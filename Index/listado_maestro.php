<?php
// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "gategourmet");

// Verificar la conexión
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Consultas
$sql_estado_area = "SELECT areas, estado, COUNT(*) AS cantidad FROM listado_maestro GROUP BY areas, estado";
$result_estado_area = $mysqli->query($sql_estado_area);

$sql_tipo_desactualizada = "SELECT tipo, COUNT(*) AS cantidad_desactualizada FROM listado_maestro WHERE estado = 'Desactualizado' GROUP BY tipo";
$result_tipo_desactualizada = $mysqli->query($sql_tipo_desactualizada);

$sql_actualizacion_mensual = "SELECT areas, COUNT(*) AS cantidad_actualizada FROM listado_maestro WHERE fecha_aprobacion >= CURDATE() - INTERVAL 1 MONTH GROUP BY areas";
$result_actualizacion_mensual = $mysqli->query($sql_actualizacion_mensual);

$sql_cantidad_desactualizada = "SELECT areas, COUNT(*) AS cantidad_desactualizada FROM listado_maestro WHERE estado = 'Desactualizado' GROUP BY areas";
$result_cantidad_desactualizada = $mysqli->query($sql_cantidad_desactualizada);

$sql_total = "SELECT areas, COUNT(*) AS total_documentos FROM listado_maestro GROUP BY areas";
$result_total = $mysqli->query($sql_total);

$sql_actualizados = "SELECT areas, COUNT(*) AS documentos_actualizados FROM listado_maestro WHERE estado = 'Actualizado' GROUP BY areas";
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
<<<<<<< HEAD
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado Maestro</title>
    <link rel="stylesheet" href="listado_maestro.css">
=======
    <title>Indicadores de Documentación</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="indicadores.css"> <!-- Enlace a tu archivo CSS -->
    <style>
        /* Estilos generales */
>>>>>>> 1198f90b28b50dcd87c80e3dcff7333047585f63

    </style>
</head>
<body>
    <div class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Logo" class="logo">
    </div>

<<<<<<< HEAD
    <?php if ($result->num_rows > 0): ?>
        <div class="container">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Proceso</th>
                            <th>Código</th>
                            <th>Título Documento</th>
                            <th>Tipo</th>
                            <th>Versión</th>
                            <th>Estado</th>
                            <th>Fecha Aprobación</th>
                            <th>Áreas</th>
                            <th>Motivo del Cambio</th>
                            <th>Tiempo de Retención</th>
                            <th>Responsable de Retención</th>
                            <th>Lugar de Almacenamiento Físico</th>
                            <th>Lugar de Almacenamiento Magnético</th>
                            <th>Conservación</th>
                            <th>Disposición Final</th>
                            <th>Copias Controladas</th>
                            <th>Fecha de Vigencia</th>
                            <th>Días</th>
                            <th>Señal Alerta</th>
                            <th>Obsoleto</th>
                            <th>Anulado</th>
                            <th>En Actualización</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): 
                            // Determinar la clase CSS basada en el estado del documento
                            $rowClass = '';
                            if (strtolower($row['estado']) == 'vigente') {
                                $rowClass = 'vigente';
                            } elseif (strtolower($row['estado']) == 'desactualizado') {
                                $rowClass = 'desactualizado';
                            } elseif (strtolower($row['estado']) == 'obsoleto') {
                                $rowClass = 'obsoleto';
                            }
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td><?php echo htmlspecialchars($row['proceso']); ?></td>
                            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($row['titulo_documento']); ?></td>
                            <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                            <td><?php echo htmlspecialchars($row['version']); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_aprobacion']); ?></td>
                            <td><?php echo htmlspecialchars($row['areas']); ?></td>
                            <td><?php echo htmlspecialchars($row['motivo_del_cambio']); ?></td>
                            <td><?php echo htmlspecialchars($row['tiempo_de_retencion']); ?></td>
                            <td><?php echo htmlspecialchars($row['responsable_de_retencion']); ?></td>
                            <td><?php echo htmlspecialchars($row['lugar_de_almacenamiento_fisico']); ?></td>
                            <td><?php echo htmlspecialchars($row['lugar_de_almacenamiento_magnetico']); ?></td>
                            <td><?php echo htmlspecialchars($row['conservacion']); ?></td>
                            <td><?php echo htmlspecialchars($row['disposicion_final']); ?></td>
                            <td><?php echo htmlspecialchars($row['copias_controladas']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_de_vigencia']); ?></td>
                            <td><?php echo htmlspecialchars($row['dias']); ?></td>
                            <td><?php echo htmlspecialchars($row['senal_alerta']); ?></td>
                            <td><?php echo htmlspecialchars($row['obsoleto']); ?></td>
                            <td><?php echo htmlspecialchars($row['anulado']); ?></td>
                            <td><?php echo htmlspecialchars($row['en_actualizacion']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
=======
    <div class="main-content">
        <div class="chart-container">
            <h2>Estado de Documentación por Área</h2>
            <canvas id="estadoAreaChart"></canvas>
>>>>>>> 1198f90b28b50dcd87c80e3dcff7333047585f63
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

        <div class="chart-container">
            <h2>Porcentaje de Actualización por Área</h2>
            <canvas id="porcentajeActualizacionChart"></canvas>
        </div>
    </div>

    <div class="footer">
        <p>© 2024 Nombre de la Empresa. Todos los derechos reservados.</p>
        <a href="#">Política de privacidad</a> | <a href="#">Términos de servicio</a>
    </div>

    <script>
        // Gráfico 1: Estado de Documentación por Área
        var ctx1 = document.getElementById('estadoAreaChart').getContext('2d');
        var estadoAreaChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($result_estado_area->fetch_all(MYSQLI_ASSOC), 'areas')); ?>,
                datasets: [{
                    label: 'Cantidad',
                    data: <?php echo json_encode(array_column($result_estado_area->fetch_all(MYSQLI_ASSOC), 'cantidad')); ?>,
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
                labels: <?php echo json_encode(array_column($result_tipo_desactualizada->fetch_all(MYSQLI_ASSOC), 'tipo')); ?>,
                datasets: [{
                    label: 'Cantidad Desactualizada',
                    data: <?php echo json_encode(array_column($result_tipo_desactualizada->fetch_all(MYSQLI_ASSOC), 'cantidad_desactualizada')); ?>,
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
                labels: <?php echo json_encode(array_column($result_actualizacion_mensual->fetch_all(MYSQLI_ASSOC), 'areas')); ?>,
                datasets: [{
                    label: 'Cantidad Actualizada',
                    data: <?php echo json_encode(array_column($result_actualizacion_mensual->fetch_all(MYSQLI_ASSOC), 'cantidad_actualizada')); ?>,
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    borderColor: 'rgba(255, 206, 86, 1)',
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
                labels: <?php echo json_encode(array_column($result_cantidad_desactualizada->fetch_all(MYSQLI_ASSOC), 'areas')); ?>,
                datasets: [{
                    label: 'Cantidad Desactualizada',
                    data: <?php echo json_encode(array_column($result_cantidad_desactualizada->fetch_all(MYSQLI_ASSOC), 'cantidad_desactualizada')); ?>,
                    backgroundColor:'rgba(255, 99, 132, 0.2)',
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

        // Gráfico 5: Porcentaje de Actualización por Área
        var ctx5 = document.getElementById('porcentajeActualizacionChart').getContext('2d');
        var porcentajeActualizacionChart = new Chart(ctx5, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($total_documentos)); ?>,
                datasets: [{
                    label: 'Porcentaje de Actualización',
                    data: <?php
                        $porcentajes = [];
                        foreach ($total_documentos as $area => $total) {
                            $actualizados = isset($documentos_actualizados[$area]) ? $documentos_actualizados[$area] : 0;
                            $porcentajes[] = $total > 0 ? ($actualizados / $total) * 100 : 0;
                        }
                        echo json_encode($porcentajes);
                    ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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