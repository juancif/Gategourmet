<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "gategourmet");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consultas a la base de datos
$sql_estado = "
    SELECT 
        SUM(CASE WHEN en_actualizacion = '1' THEN 1 ELSE 0 END) AS en_actualizacion,
        SUM(CASE WHEN obsoleto = '1' THEN 1 ELSE 0 END) AS obsoleto,
        SUM(CASE WHEN anulado = '1' THEN 1 ELSE 0 END) AS anulado,
        SUM(CASE WHEN en_actualizacion = '0' AND obsoleto = '0' AND anulado = '0' THEN 1 ELSE 0 END) AS desactualizado
    FROM listado_maestro";
$resultado_estado = $conn->query($sql_estado);
$estado = $resultado_estado ? $resultado_estado->fetch_assoc() : [];

$estado = array_map(function ($value) {
    return $value !== null ? (int)$value : 0;
}, $estado);

$max_valor = max($estado);
$factor_escala = ($max_valor > 0) ? 200 / $max_valor : 1;

$sql_porcentaje = "
    SELECT 
        areas, 
        COUNT(*) AS total_documentos, 
        SUM(CASE WHEN en_actualizacion = '1' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) * 100 AS porcentaje_actualizacion 
    FROM listado_maestro 
    GROUP BY areas";
$resultado_porcentaje = $conn->query($sql_porcentaje);

$sql_desactualizada = "
    SELECT 
        areas, 
        COUNT(*) AS total_desactualizados 
    FROM listado_maestro 
    WHERE obsoleto = '1' OR anulado = '1'
    GROUP BY areas";
$resultado_desactualizada = $conn->query($sql_desactualizada);

$sql_tiempos = "
    SELECT 
        SUM(CASE WHEN a_tiempo = 1 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) * 100 AS porcentaje_a_tiempo,
        SUM(CASE WHEN fuera_de_tiempo = 1 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) * 100 AS porcentaje_fuera_de_tiempo,
        SUM(CASE WHEN no_entregado = 1 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) * 100 AS porcentaje_no_entregado
    FROM listado_maestro";
$resultado_tiempos = $conn->query($sql_tiempos);
$tiempos = $resultado_tiempos->fetch_assoc();

$conn->close();

// Devolver datos en formato JSON
echo json_encode([
    'estado' => $estado,
    'porcentaje' => $resultado_porcentaje->fetch_all(MYSQLI_ASSOC),
    'desactualizada' => $resultado_desactualizada->fetch_all(MYSQLI_ASSOC),
    'tiempos' => $tiempos
]);
