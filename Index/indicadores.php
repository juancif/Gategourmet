<?php
// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "gategourmet");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consultas a la base de datos
$sql_estado = "
    SELECT 
        SUM(CASE WHEN en_actualizacion = 1 THEN 1 ELSE 0 END) AS en_actualizacion,
        SUM(CASE WHEN obsoleto = 1 THEN 1 ELSE 0 END) AS obsoleto,
        SUM(CASE WHEN anulado = 1 THEN 1 ELSE 0 END) AS anulado,
        SUM(CASE WHEN en_actualizacion = 0 AND obsoleto = 0 AND anulado = 0 THEN 1 ELSE 0 END) AS desactualizado
    FROM listado_maestro";
$resultado_estado = $conn->query($sql_estado);

// Validación de datos obtenidos
$estado = $resultado_estado ? $resultado_estado->fetch_assoc() : ['en_actualizacion' => 0, 'obsoleto' => 0, 'anulado' => 0, 'desactualizado' => 0];

$estado = array_map(function ($value) {
    return $value !== null ? (int)$value : 0;
}, $estado);

$max_valor = max($estado);
$factor_escala = ($max_valor > 0) ? 200 / $max_valor : 1; // Evita división por cero

$sql_porcentaje = "
    SELECT 
        areas, 
        COUNT(*) AS total_documentos, 
        SUM(CASE WHEN en_actualizacion = 1 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) * 100 AS porcentaje_actualizacion 
    FROM listado_maestro 
    GROUP BY areas";
$resultado_porcentaje = $conn->query($sql_porcentaje);

$sql_desactualizada = "
    SELECT 
        areas, 
        COUNT(*) AS total_desactualizados 
    FROM listado_maestro 
    WHERE obsoleto = 1 OR anulado = 1
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicador General de Documentación</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="indicadores.css"
<body>

<div class="header">
    <img src="../Imagenes/Logo_oficial_B-N.png" alt="Logo de la empresa" title="Logo de la empresa">
    <h1>Indicadores Generales</h1>
</div>

<div class="container">
    <div class="card">
        <h2>Estado General de Documentación</h2>
        <div class="bar-container">
            <div class="bar" role="progressbar" aria-valuenow="<?php echo $estado['en_actualizacion']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $max_valor; ?>">
                <div id="en-actualizacion" style="height: <?php echo $estado['en_actualizacion'] * $factor_escala; ?>px;">
                    <div class="bar-value"><?php echo htmlspecialchars($estado['en_actualizacion']); ?></div>
                    <div class="bar-label">En Actualización</div>
                </div>
            </div>
            <div class="bar" role="progressbar" aria-valuenow="<?php echo $estado['obsoleto']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $max_valor; ?>">
                <div id="obsoleto" style="height: <?php echo $estado['obsoleto'] * $factor_escala; ?>px;">
                    <div class="bar-value"><?php echo htmlspecialchars($estado['obsoleto']); ?></div>
                    <div class="bar-label">Obsoleto</div>
                </div>
            </div>
            <div class="bar" role="progressbar" aria-valuenow="<?php echo $estado['anulado']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $max_valor; ?>">
                <div id="anulado" style="height: <?php echo $estado['anulado'] * $factor_escala; ?>px;">
                    <div class="bar-value"><?php echo htmlspecialchars($estado['anulado']); ?></div>
                    <div class="bar-label">Anulado</div>
                </div>
            </div>
            <div class="bar" role="progressbar" aria-valuenow="<?php echo $estado['desactualizado']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $max_valor; ?>">
                <div id="desactualizado" style="height: <?php echo $estado['desactualizado'] * $factor_escala; ?>px;">
                    <div class="bar-value"><?php echo htmlspecialchars($estado['desactualizado']); ?></div>
                    <div class="bar-label">Desactualizado</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Porcentajes de Documentación</h2>
        <table>
            <thead>
            <tr>
                <th>Área</th>
                <th>Documentos Totales</th>
                <th>% En Actualización</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($fila = $resultado_porcentaje->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['areas']); ?></td>
                    <td><?php echo (int)$fila['total_documentos']; ?></td>
                    <td><?php echo ($fila['total_documentos'] > 0) ? round($fila['porcentaje_actualizacion'], 2) : 0; ?>%</td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Documentos Desactualizados</h2>
        <table>
            <thead>
            <tr>
                <th>Área</th>
                <th>Total Desactualizados</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($fila = $resultado_desactualizada->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['areas']); ?></td>
                    <td><?php echo (int)$fila['total_desactualizados']; ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Tiempos de Entrega</h2>
        <div class="progress">
            <div class="progress-bar green" style="width: <?php echo round($tiempos['porcentaje_a_tiempo'], 2); ?>%;">
                A Tiempo: <?php echo round($tiempos['porcentaje_a_tiempo'], 2); ?>%
            </div>
        </div>
        <div class="progress">
            <div class="progress-bar red" style="width: <?php echo round($tiempos['porcentaje_fuera_de_tiempo'], 2); ?>%;">
                Fuera de Tiempo: <?php echo round($tiempos['porcentaje_fuera_de_tiempo'], 2); ?>%
            </div>
        </div>
        <div class="progress">
            <div class="progress-bar gray" style="width: <?php echo round($tiempos['porcentaje_no_entregado'], 2); ?>%;">
                No Entregado: <?php echo round($tiempos['porcentaje_no_entregado'], 2); ?>%
            </div>
        </div>
    </div>
</div>

<div class="footer">
    <p>&copy; 2024 gategourmet. Todos los derechos reservados.</p>
</div>

</body>
</html>
