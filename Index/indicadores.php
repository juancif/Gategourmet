<?php
// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "gategourmet");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para estado de documentación por área
$sql_estado = "
    SELECT 
        SUM(CASE WHEN en_actualizacion = 1 THEN 1 ELSE 0 END) AS en_actualizacion,
        SUM(CASE WHEN obsoleto = 1 THEN 1 ELSE 0 END) AS obsoleto,
        SUM(CASE WHEN anulado = 1 THEN 1 ELSE 0 END) AS anulado,
        SUM(CASE WHEN en_actualizacion = 0 AND obsoleto = 0 AND anulado = 0 THEN 1 ELSE 0 END) AS desactualizado
    FROM listado_maestro";
$resultado_estado = $conn->query($sql_estado);
$estado = $resultado_estado->fetch_assoc();

// Consulta para porcentaje de actualización por área
$sql_porcentaje = "
    SELECT 
        areas, 
        COUNT(*) AS total_documentos, 
        SUM(CASE WHEN en_actualizacion = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100 AS porcentaje_actualizacion 
    FROM listado_maestro 
    GROUP BY areas";
$resultado_porcentaje = $conn->query($sql_porcentaje);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicadores de Documentación</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: 20px auto;
        }
        .chart {
            background-color: #e9e9e9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .chart h2 {
            text-align: center;
            color: #333;
        }
        .bar-container {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .bar {
            width: 20%;
            text-align: center;
            position: relative;
        }
        .bar div {
            background-color: #007bff;
            height: 0;
            width: 100%;
            position: absolute;
            bottom: 0;
        }
        .bar-label {
            position: absolute;
            top: -30px;
            width: 100%;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .progress {
            background-color: #f3f3f3;
            border-radius: 10px;
            height: 20px;
            width: 100%;
        }
        .progress-bar {
            height: 20px;
            border-radius: 10px;
            text-align: right;
            padding-right: 10px;
            color: white;
        }
        .green { background-color: #28a745; }
        .red { background-color: #dc3545; }
    </style>
</head>
<body>

<div class="container">
    <div class="chart">
        <h2>Estado de Documentación por Área</h2>
        <div class="bar-container">
            <div class="bar">
                <div style="height: <?php echo $estado['en_actualizacion'] * 5; ?>px;"></div>
                <div class="bar-label">En Actualización</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo $estado['desactualizado'] * 5; ?>px;"></div>
                <div class="bar-label">Desactualizado</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo $estado['obsoleto'] * 5; ?>px;"></div>
                <div class="bar-label">Obsoleto</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo $estado['anulado'] * 5; ?>px;"></div>
                <div class="bar-label">Anulado</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th>Área</th>
            <th>Porcentaje de Actualización</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($fila = $resultado_porcentaje->fetch_assoc()): ?>
            <tr>
                <td><?php echo $fila['areas']; ?></td>
                <td>
                    <div class="progress">
                        <div class="progress-bar <?php echo ($fila['porcentaje_actualizacion'] < 50) ? 'red' : 'green'; ?>"
                             style="width: <?php echo $fila['porcentaje_actualizacion']; ?>%">
                            <?php echo round($fila['porcentaje_actualizacion'], 1); ?>%
                        </div>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
$conn->close();
?>
