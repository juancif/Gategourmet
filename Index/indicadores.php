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
               COUNT(*) AS total_documentos
        FROM listado_maestro
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
$meses = range(1, 12);
$cantidadActualizacionMensual = [];

while($row = $result->fetch_assoc()) {
    $areas4[] = $row['areas'];
    $cantidadActualizacionMensual[$row['areas']][$row['mes']] = $row['cantidad'];
}

// Rellenar datos faltantes con ceros para Actualización Mensual por Área
foreach ($areas4 as $area) {
    foreach ($meses as $mes) {
        if (!isset($cantidadActualizacionMensual[$area][$mes])) {
            $cantidadActualizacionMensual[$area][$mes] = 0;
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="GateGourmet Logo" class="logo">
    </header>
    <div class="container">
        <div class="chart-container">
            <h2>Porcentaje de Actualización por Área</h2>
            <canvas id="porcentajeActualizacionChart"></canvas>
        </div>

        <div class="chart-container">
            <h2>Estado de Documentación por Área</h2>
            <canvas id="estadoDocumentacionChart"></canvas>
        </div>

        <div class="chart-container">
            <h2>Tipo de Documentación Desactualizada</h2>
            <canvas id="tipoDocumentacionDesactualizadaChart"></canvas>
        </div>

        <div class="chart-container">
            <h2>Actualización Mensual por Área</h2>
            <canvas id="actualizacionMensualChart"></canvas>
        </div>

        <div class="chart-container">
            <h2>Cantidad de Documentación Desactualizada por Área</h2>
            <canvas id="cantidadDesactualizadaChart"></canvas>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 GateGourmet. Todos los derechos reservados.</p>
    </footer>

    <script>
        // Gráfico 1: Porcentaje de Actualización por Área
        var ctx1 = document.getElementById('porcentajeActualizacionChart').getContext('2d');
        var data1 = {
            labels: <?php echo json_encode(array_keys($data1)); ?>,
            datasets: [{
                label: 'Porcentaje de Actualización',
                data: <?php echo json_encode(array_values($data1)); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2
            }]
        };
        var myChart1 = new Chart(ctx1, {
            type: 'bar',
            data: data1,
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) { return value + '%'; }
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(0, 0, 0, 0.8)'
                        }
                    }
                }
            }
        });

        // Gráfico 2: Estado de Documentación por Área
        var ctx2 = document.getElementById('estadoDocumentacionChart').getContext('2d');
        var data2 = {
            labels: <?php echo json_encode($areas2); ?>,
            datasets: [{
                label: 'Vigente',
                data: <?php echo json_encode($vigente); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2
            }, {
                label: 'Desactualizado',
                data: <?php echo json_encode($desactualizado); ?>,
                backgroundColor: 'rgba(255, 206, 86, 0.6)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 2
            }, {
                label: 'Obsoleto',
                data: <?php echo json_encode($obsoleto); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2
            }, {
                label: 'Anulado',
                data: <?php echo json_encode($anulado); ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 2
            }]
        };
        var myChart2 = new Chart(ctx2, {
            type: 'bar',
            data: data2,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(0, 0, 0, 0.8)'
                        }
                    }
                }
            }
        });

        // Gráfico 3: Tipo de Documentación Desactualizada
        var ctx3 = document.getElementById('tipoDocumentacionDesactualizadaChart').getContext('2d');
        var data3 = {
            labels: <?php echo json_encode($tipos); ?>,
            datasets: [{
                label: 'Cantidad',
                data: <?php echo json_encode($cantidadTipos); ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 2
            }]
        };
        var myChart3 = new Chart(ctx3, {
            type: 'bar',
            data: data3,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(0, 0, 0, 0.8)'
                        }
                    }
                }
            }
        });

        // Gráfico 4: Actualización Mensual por Área
        var ctx4 = document.getElementById('actualizacionMensualChart').getContext('2d');
        var datasets4 = [];
        <?php foreach ($areas4 as $area): ?>
        datasets4.push({
            label: '<?php echo $area; ?>',
            data: <?php echo json_encode(array_values($cantidadActualizacionMensual[$area])); ?>,
            fill: false,
            borderColor: getRandomColor(),
            tension: 0.1
        });
        <?php endforeach; ?>

        var data4 = {
            labels: <?php echo json_encode($meses); ?>,
            datasets: datasets4
        };

        var myChart4 = new Chart(ctx4, {
            type: 'line',
            data: data4,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(0, 0, 0, 0.8)'
                        }
                    }
                }
            }
        });

        function getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        // Gráfico 5: Cantidad de Documentación Desactualizada por Área
        var ctx5 = document.getElementById('cantidadDesactualizadaChart').getContext('2d');
        var data5 = {
            labels: <?php echo json_encode($areas5); ?>,
            datasets: [{
                label: 'Cantidad',
                data: <?php echo json_encode($cantidadDesactualizada); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2
            }]
        };
        var myChart5 = new Chart(ctx5, {
            type: 'bar',
            data: data5,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(0, 0, 0, 0.8)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
