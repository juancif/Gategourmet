<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gategourmet";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("La conexión falló: " . $conn->connect_error);
}

// 1. Porcentaje de Actualización por Área
$sql = "SELECT areas, 
               SUM(CASE WHEN estado = 'vigente' THEN 1 ELSE 0 END) AS documentos_vigentes,
               SUM(CASE WHEN estado IN ('vigente', 'desactualizado') THEN 1 ELSE 0 END) AS total_documentos
        FROM listado_maestro
        WHERE estado IN ('vigente', 'desactualizado')
        GROUP BY areas";
$result = $conn->query($sql);

$data1 = [];
while($row = $result->fetch_assoc()) {
    $area = $row['areas'];
    $porcentaje_vigentes = ($row['total_documentos'] > 0) ? ($row['documentos_vigentes'] / $row['total_documentos']) * 100 : 0;
    $data1[$area] = $porcentaje_vigentes;
}

// 2. Estado de Documentación por Área
$sql = "SELECT areas,
               SUM(CASE WHEN estado = 'vigente' THEN 1 ELSE 0 END) AS vigente,
               SUM(CASE WHEN estado = 'desactualizado' THEN 1 ELSE 0 END) AS desactualizado,
               SUM(CASE WHEN estado = 'obsoleto' THEN 1 ELSE 0 END) AS obsoleto,
               SUM(CASE WHEN estado = 'anulado' THEN 1 ELSE 0 END) AS anulado
        FROM listado_maestro
        GROUP BY areas";
$result = $conn->query($sql);

$areas2 = [];
$vigente = [];
$desactualizado = [];
$obsoleto = [];
$anulado = [];

while($row = $result->fetch_assoc()) {
    $areas2[] = $row['areas'];
    $vigente[] = $row['vigente'];
    $desactualizado[] = $row['desactualizado'];
    $obsoleto[] = $row['obsoleto'];
    $anulado[] = $row['anulado'];
}

// 3. Tipo de Documentación Desactualizada
$sql = "SELECT tipo, COUNT(*) AS cantidad
        FROM listado_maestro
        WHERE estado = 'desactualizado'
        GROUP BY tipo";
$result = $conn->query($sql);

$tipos = [];
$cantidadTipos = [];

while($row = $result->fetch_assoc()) {
    $tipos[] = $row['tipo'];
    $cantidadTipos[] = $row['cantidad'];
}

// 4. Actualización Mensual por Área
$sql = "SELECT areas, MONTH(fecha_aprobacion) AS mes, COUNT(*) AS cantidad
        FROM listado_maestro
        WHERE estado = 'vigente'
        GROUP BY areas, mes";
$result = $conn->query($sql);

$areas4 = [];
$meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$cantidadActualizacionMensual = [];

while($row = $result->fetch_assoc()) {
    $areas4[] = $row['areas'];
    $cantidadActualizacionMensual[$row['areas']][$row['mes']] = $row['cantidad'];
}

// Rellenar datos faltantes con ceros para Actualización Mensual por Área
foreach ($areas4 as $area) {
    foreach ($meses as $index => $mes) {
        if (!isset($cantidadActualizacionMensual[$area][$index + 1])) {
            $cantidadActualizacionMensual[$area][$index + 1] = 0;
        }
    }
}

// 5. Cantidad de Documentación Desactualizada por Área
$sql = "SELECT areas, COUNT(*) AS cantidad
        FROM listado_maestro
        WHERE estado = 'desactualizado'
        GROUP BY areas";
$result = $conn->query($sql);

$areas5 = [];
$cantidadDesactualizada = [];

while($row = $result->fetch_assoc()) {
    $areas5[] = $row['areas'];
    $cantidadDesactualizada[] = $row['cantidad'];
}

// 6. Documentos Obsoletos por Área
$sql = "SELECT areas, COUNT(*) AS cantidad
        FROM listado_maestro
        WHERE estado = 'obsoleto'
        GROUP BY areas";
$result = $conn->query($sql);

$areas6 = [];
$cantidadObsoleta = [];

while($row = $result->fetch_assoc()) {
    $areas6[] = $row['areas'];
    $cantidadObsoleta[] = $row['cantidad'];
}

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicadores de Documentación</title>
    <link rel="stylesheet" href="indicadores.css">
</head>
<body>
    <header>
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="GateGourmet Logo" class="logo">
    </header>
    <div class="container">
        <!-- Indicador de Porcentaje de Actualización por Área -->
        <div class="chart-container">
            <center><h2>Porcentaje de Actualización por Área</h2></center>
            <table>
                <thead>
                    <tr>
                        <th>Área</th>
                        <th>Porcentaje de Actualización</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($data1 as $area => $porcentaje_vigentes) {
                        $class = $porcentaje_vigentes < 50 ? 'low' : ($porcentaje_vigentes < 75 ? 'medium' : 'high');
                        echo "<tr>
                                <td>$area</td>
                                <td>
                                    <div class='progress-bar'>
                                        <div class='progress $class' style='width: $porcentaje_vigentes%;'>
                                            " . round($porcentaje_vigentes, 2) . "%
                                        </div>
                                    </div>
                                </td>
                              </tr>";
                    }
                    ?> 
                </tbody>
            </table>

        <!-- Gráfico 2: Estado de Documentación por Área -->
        <div class="chart-container">
            <center><h2>Estado de Documentación por Área</h2></center>
            <canvas id="estadoDocumentacionChart"></canvas>
            <button onclick="downloadPDF('estadoDocumentacionChart')">Descargar PDF</button>
        </div>

        <!-- Gráfico 3: Tipo de Documentación Desactualizada -->
        <div class="chart-container">
           <center><h2>Tipo de Documentación Desactualizada</h2></center> 
            <canvas id="tipoDocumentacionDesactualizadaChart"></canvas>
            <button onclick="downloadPDF('tipoDocumentacionDesactualizadaChart')">Descargar PDF</button>
        </div>

        <!-- Gráfico 4: Actualización Mensual por Área -->
        <div class="chart-container">
            <center><h2>Actualización Mensual por Área</h2></center>
            <canvas id="actualizacionMensualChart"></canvas>
            <button onclick="downloadPDF('actualizacionMensualChart')">Descargar PDF</button>
        </div>

        <!-- Gráfico 5: Cantidad de Documentación Desactualizada por Área -->
        <div class="chart-container">
            <center><h2>Cantidad de Documentación Desactualizada por Área</h2></center>
            <canvas id="cantidadDesactualizadaChart"></canvas>
            <button onclick="downloadPDF('cantidadDesactualizadaChart')">Descargar PDF</button>
        </div>

        <!-- Gráfico 6: Documentos Obsoletos por Área -->
        <div class="chart-container">
            <center><h2>Documentos Obsoletos por Área</h2></center>
            <canvas id="documentosObsoletosChart"></canvas>
            <button onclick="downloadPDF('documentosObsoletosChart')">Descargar PDF</button>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 GateGourmet. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.min.js"></script>

    <script>
        // Función para descargar un gráfico específico en PDF
        function downloadPDF(chartId) {
            const canvas = document.getElementById(chartId);
            const canvasImage = canvas.toDataURL("image/png", 1.0);
            const pdf = new jsPDF('landscape');
            pdf.addImage(canvasImage, 'PNG', 10, 10, 280, 150);
            pdf.save(`${chartId}.pdf`);
        }

        // Gráfico 2: Estado de Documentación por Área
        new Chart(document.getElementById('estadoDocumentacionChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($areas2); ?>,
                datasets: [
                    {
                        label: 'Vigente',
                        data: <?php echo json_encode($vigente); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    },
                    {
                        label: 'Desactualizado',
                        data: <?php echo json_encode($desactualizado); ?>,
                        backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    },
                    {
                        label: 'Obsoleto',
                        data: <?php echo json_encode($obsoleto); ?>,
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    },
                    {
                        label: 'Anulado',
                        data: <?php echo json_encode($anulado); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Estado de Documentación por Área'
                    }
                }
            }
        });

        var ctx3 = document.getElementById('tipoDocumentacionDesactualizadaChart').getContext('2d');
var data3 = {
    labels: [
        'INSTRUCTIVO',
        'PROGRAMA',
        'FORMATO',
        'LEYOUT',
        'MANUAL',
        'PROCEDIMIENTO',
        'MAPA DE CADENA VOLUTIVA',
        'SUBPROGRAMA'
    ],
    datasets: [{
        label: 'Cantidad',
        data: <?php echo json_encode($cantidadTipos); ?>,
        backgroundColor: <?php echo json_encode(array_map(function() {
            return 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 0.6)';
        }, $cantidadTipos)); ?>,
        borderColor: <?php echo json_encode(array_map(function() {
            return 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 1)';
        }, $cantidadTipos)); ?>,
        borderWidth: 2
    }]
};
var myChart3 = new Chart(ctx3, {
    type: 'bar',
    data: data3,
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Tipos de Documentación'
                },
                ticks: {
                    autoSkip: false,
                    maxRotation: 90,
                    minRotation: 45
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Cantidad'
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: 'rgba(0, 0, 0, 0.8)'
                }
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                    }
                }
            },
            datalabels: {
                display: true,
                color: 'black',
                anchor: 'end',
                align: 'top',
                formatter: function(value) {
                    return value;
                }
            }
        }
    }
});


        // Gráfico 4: Actualizacion Mensaul por Area

        new Chart(document.getElementById('actualizacionMensualChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($meses); ?>,
        datasets: <?php echo json_encode(array_map(function($area, $data) {
            // Generar un color aleatorio para cada área
            $color = sprintf('rgba(%d, %d, %d, 1)', rand(0, 255), rand(0, 255), rand(0, 255));
            return [
                'label' => $area,
                'data' => array_values($data),
                'fill' => false,
                'borderColor' => $color,
                'tension' => 0.1
            ];
        }, array_keys($cantidadActualizacionMensual), $cantidadActualizacionMensual)); ?>
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Actualización Mensual por Área'
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Meses'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Actualización'
                },
                beginAtZero: true
            }
        }
    }
});


        // Gráfico 5: Cantidad de Documentación Desactualizada por Área
        new Chart(document.getElementById('cantidadDesactualizadaChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($areas5); ?>,
                datasets: [{
                    label: 'Cantidad Desactualizada',
                    data: <?php echo json_encode($cantidadDesactualizada); ?>,
                    backgroundColor: 'rgba(255, 206, 86, 0.6)',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Cantidad de Documentación Desactualizada por Área'
                    }
                }
            }
        });

        // Gráfico 6: Documentos Obsoletos por Área
        new Chart(document.getElementById('documentosObsoletosChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($areas6); ?>,
                datasets: [{
                    label: 'Cantidad Obsoleta',
                    data: <?php echo json_encode($cantidadObsoleta); ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Documentos Obsoletos por Área'
                    }
                }
            }
        });
    </script>
</body>
</html>

