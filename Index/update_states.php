<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gategourmet";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("La conexión falló: " . $conn->connect_error);
}

// Obtener la fecha actual
$fecha_actual = date("Y-m-d");

// Consulta para actualizar los estados de los documentos según la fecha de vigencia
$sql = "UPDATE listado_maestro 
        SET estado = CASE 
            WHEN fecha_de_vigencia >= ? THEN 'vigente'
            WHEN fecha_de_vigencia < ? THEN 'desactualizado'
            ELSE 'obsoleto'
        END";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $fecha_actual, $fecha_actual);
$stmt->execute();

$stmt->close();
$conn->close();
?>
