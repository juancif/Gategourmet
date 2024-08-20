<?php
// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "gategourmet");

// Verificar la conexión
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Consultas para obtener datos
$sql_estado_area = "SELECT areas, estado, COUNT(*) AS cantidad FROM listado_maestro GROUP BY areas, estado";
$sql_tipo_desactualizada = "SELECT tipo, COUNT(*) AS cantidad_desactualizada FROM listado_maestro WHERE estado = 'Desactualizado' GROUP BY tipo";
$sql_actualizacion_mensual = "SELECT areas, COUNT(*) AS cantidad_actualizada FROM listado_maestro WHERE fecha_aprobacion >= CURDATE() - INTERVAL 1 MONTH GROUP BY areas";
$sql_cantidad_desactualizada = "SELECT areas, COUNT(*) AS cantidad_desactualizada FROM listado_maestro WHERE estado = 'Desactualizado' GROUP BY areas";
$sql_total = "SELECT areas, COUNT(*) AS total_documentos FROM listado_maestro GROUP BY areas";
$sql_actualizados = "SELECT areas, COUNT(*) AS documentos_actualizados FROM listado_maestro WHERE estado = 'Actualizado' GROUP BY areas";

$result_estado_area = $mysqli->query($sql_estado_area);
$result_tipo_desactualizada = $mysqli->query($sql_tipo_desactualizada);
$result_actualizacion_mensual = $mysqli->query($sql_actualizacion_mensual);
$result_cantidad_desactualizada = $mysqli->query($sql_cantidad_desactualizada);
$result_total = $mysqli->query($sql_total);
$result_actualizados = $mysqli->query($sql_actualizados);

$total_documentos = [];
$documentos_actualizados = [];

// Guardar resultados de total de documentos por área
while ($row = $result_total->fetch_assoc()) {
    $total_documentos[$row['areas']] = $row['total_documentos'];
}

// Guardar resultados de documentos actualizados por área
while ($row = $result_actualizados->fetch_assoc()) {
    $documentos_actualizados[$row['areas']] = $row['documentos_actualizados'];
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Indicadores de Documentación</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <link rel="stylesheet" href="indicadores.css">
    <style>
        .table-container {
            margin: 20px 0;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container th, .table-container td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .progress-bar {
            width: 100%;
            background-color: #f3f3f3;
            border-radius: 5px;
            overflow: hidden;
        }
        .progress {
            height: 20px;
            line-height: 20px;
            color: white;
            text-align: right;
            padding-right: 5px;
            border-radius: 5px;
        }
        .low {
            background-color: red; /* Menos de 50% */
        }
        .high {
            background-color: green; /* 50% o más */
        }
        .chart-container {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Logo" class="logo">
    </div>

    <div class="main-content">
        <!-- Gráficos -->
        <div class="chart-container">
            <h2>Estado de Documentación por Área</h2>
            <canvas id="estadoAreaChart"></canvas>
        </div>

        <div class="chart-container">
            <h2>Tipo de Documentación Desactualizada</h2>
            <canvas id="tipoDesactualizadaChart"></canvas>
        </div>

        <div class="chart-container">
            <h2>Actualización Mensual por Área</h2>
            <canvas id="actualizacionMensualChart"></canvas>
        </div>

        <div class="chart-container">
            <h2>Cantidad de Documentación Desactualizada por Área</h2>
            <canvas id="cantidadDesactualizadaChart"></canvas>
        </div>

        <!-- Tabla con Barras de Progreso -->
        <div class="table-container">
            <h2>Porcentaje de Actualización por Área</h2>
            <table>
                <thead>
                    <tr>
                        <th>Área</th>
                        <th>Porcentaje de Actualización</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($total_documentos as $area => $total): 
                        $actualizados = isset($documentos_actualizados[$area]) ? $documentos_actualizados[$area] : 0;
                        $porcentaje = $total > 0 ? ($actualizados / $total) * 100 : 0;
                        $progress_class = $porcentaje < 50 ? 'low' : 'high';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($area); ?></td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress <?php echo $progress_class; ?>" style="width: <?php echo $porcentaje; ?>%;">
                                    <?php echo number_format($porcentaje, 1); ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>© 2024 Gategourmet-Todos los derechos reservados.</p>
        <a href="#">Política de privacidad</a> | <a href="#">Términos de servicio</a>
    </div>

    <script>
        // Gráfico 1: Estado de Documentación por Área
        var ctx1 = document.getElementById('estadoAreaChart').getContext('2d');
        var estadoAreaChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: <?php
                    $result_estado_area->data_seek(0); // Reiniciar el puntero del resultado
                    echo json_encode(array_column($result_estado_area->fetch_all(MYSQLI_ASSOC), 'areas'));
                ?>,
                datasets: [{
                    label: 'Cantidad',
                    data: <?php
                        $result_estado_area->data_seek(0); // Reiniciar el puntero del resultado
                        echo json_encode(array_column($result_estado_area->fetch_all(MYSQLI_ASSOC), 'cantidad'));
                    ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Gráfico 2: Tipo de Documentación Desactualizada
        var ctx2 = document.getElementById('tipoDesactualizadaChart').getContext('2d');
        var tipoDesactualizadaChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php
                    $result_tipo_desactualizada->data_seek(0); // Reiniciar el puntero del resultado
                    echo json_encode(array_column($result_tipo_desactualizada->fetch_all(MYSQLI_ASSOC), 'tipo'));
                ?>,
                datasets: [{
                    label: 'Cantidad Desactualizada',
                    data: <?php
                        $result_tipo_desactualizada->data_seek(0); // Reiniciar el puntero del resultado
                        echo json_encode(array_column($result_tipo_desactualizada->fetch_all(MYSQLI_ASSOC), 'cantidad_desactualizada'));
                    ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Gráfico 3: Actualización Mensual por Área
        var ctx3 = document.getElementById('actualizacionMensualChart').getContext('2d');
        var actualizacionMensualChart = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: <?php
                    $result_actualizacion_mensual->data_seek(0); // Reiniciar el puntero del resultado
                    echo json_encode(array_column($result_actualizacion_mensual->fetch_all(MYSQLI_ASSOC), 'areas'));
                ?>,
                datasets: [{
                    label: 'Cantidad Actualizada',
                    data: <?php
                        $result_actualizacion_mensual->data_seek(0); // Reiniciar el puntero del resultado
                        echo json_encode(array_column($result_actualizacion_mensual->fetch_all(MYSQLI_ASSOC), 'cantidad_actualizada'));
                    ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Gráfico 4: Cantidad de Documentación Desactualizada por Área
        var ctx4 = document.getElementById('cantidadDesactualizadaChart').getContext('2d');
        var cantidadDesactualizadaChart = new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: <?php
                    $result_cantidad_desactualizada->data_seek(0); // Reiniciar el puntero del resultado
                    echo json_encode(array_column($result_cantidad_desactualizada->fetch_all(MYSQLI_ASSOC), 'areas'));
                ?>,
                datasets: [{
                    label: 'Cantidad Desactualizada',
                    data: <?php
                        $result_cantidad_desactualizada->data_seek(0); // Reiniciar el puntero del resultado
                        echo json_encode(array_column($result_cantidad_desactualizada->fetch_all(MYSQLI_ASSOC), 'cantidad_desactualizada'));
                    ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
