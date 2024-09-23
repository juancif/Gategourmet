<?php
session_start();
$areaUsuario = $_SESSION['area']; // Suponiendo que el área se guarda en la sesión

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gategourmet";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("La conexión falló: " . $conn->connect_error);
}

// 1. Porcentaje de Actualización por Área (Circular)
$sql = "SELECT areas, 
               SUM(CASE WHEN estado = 'vigente' THEN 1 ELSE 0 END) AS documentos_vigentes,
               SUM(CASE WHEN estado IN ('vigente', 'desactualizado') THEN 1 ELSE 0 END) AS total_documentos
        FROM listado_maestro
        WHERE estado IN ('vigente', 'desactualizado') 
        AND areas = '$areaUsuario'
        GROUP BY areas";

$result = $conn->query($sql);

$data1 = [];
while($row = $result->fetch_assoc()) {
    $area = $row['areas'];
    $porcentaje_vigentes = ($row['total_documentos'] > 0) ? ($row['documentos_vigentes'] / $row['total_documentos']) * 100 : 0;
    $data1[$area] = $porcentaje_vigentes;
}

// 2. Estado de Documentación por Área (Barras)
$sql = "SELECT areas,
               SUM(CASE WHEN estado = 'vigente' THEN 1 ELSE 0 END) AS vigente,
               SUM(CASE WHEN estado = 'desactualizado' THEN 1 ELSE 0 END) AS desactualizado,
               SUM(CASE WHEN estado = 'obsoleto' THEN 1 ELSE 0 END) AS obsoleto,
               SUM(CASE WHEN estado = 'anulado' THEN 1 ELSE 0 END) AS anulado
        FROM listado_maestro
        WHERE areas = '$areaUsuario'
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

// 3. Tipo de Documentación Desactualizada (Barras)
$sql = "SELECT tipo, COUNT(*) AS cantidad
        FROM listado_maestro
        WHERE estado = 'desactualizado' 
        AND areas = '$areaUsuario'
        GROUP BY tipo";

$result = $conn->query($sql);

$tipos = [];
$cantidadTipos = [];
$tiposCompleto = [
    'F' => 'Formato',
    'G' => 'Programa',
    'I' => 'Instructivo',
    'L' => 'Leyout',
    'M' => 'Manual',
    'MCV' => 'Mapa de Cadena Evolutiva',
    'P' => 'Procedimiento',
    'S' => 'Subprograma'
];

while($row = $result->fetch_assoc()) {
    $tipo = $row['tipo'];
    $tipos[] = $tiposCompleto[$tipo] ?? $tipo; // Usar nombre completo
    $cantidadTipos[] = $row['cantidad'];
}

// 4. Actualización Mensual por Área (Barras)
$sql = "SELECT areas, MONTH(fecha_aprobacion) AS mes, COUNT(*) AS cantidad
        FROM listado_maestro
        WHERE estado = 'vigente' 
        AND areas = '$areaUsuario'
        GROUP BY areas, mes
        ORDER BY mes";

$result = $conn->query($sql);

$actualizacionMensualData = [];
$meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

while ($row = $result->fetch_assoc()) {
    $area = $row['areas'];
    $mes = $row['mes'];
    $cantidad = $row['cantidad'];
    $actualizacionMensualData[$area][$mes] = $cantidad;
}

// Rellenar meses faltantes con ceros
foreach ($actualizacionMensualData as $area => &$data) {
    foreach (range(1, 12) as $mes) {
        if (!isset($data[$mes])) {
            $data[$mes] = 0;
        }
    }
}

// 5. Cantidad de Documentación Desactualizada por Área (Barras)
$sql = "SELECT areas, COUNT(*) AS cantidad
        FROM listado_maestro
        WHERE estado = 'desactualizado' 
        AND areas = '$areaUsuario'
        GROUP BY areas";

$result = $conn->query($sql);

$areas5 = [];
$cantidadDesactualizada = [];

while($row = $result->fetch_assoc()) {
    $areas5[] = $row['areas'];
    $cantidadDesactualizada[] = $row['cantidad'];
}

// 6. Documentos Obsoletos por Área (Pie)
$sql = "SELECT areas, COUNT(*) AS cantidad
        FROM listado_maestro
        WHERE estado = 'obsoleto' 
        AND areas = '$areaUsuario'
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
    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <li class="nav__item__user">
        <a href="http://localhost/GateGourmet/Index/index_user.php" class="cerrar__sesion__link">
            <img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario">
            <div class="cerrar__sesion">Volver al inicio</div>
        </a>
    </li>
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
        </div>

        <!-- Gráfico 2: Estado de Documentación por Área -->
        <div class="chart-container">
            <center><h2>Estado de Documentación por Área</h2></center>
            <canvas id="estadoDocumentacionChart"></canvas>
            <button onclick="downloadPDF('estadoDocumentacionChart')">Descargar PDF</button>
        </div>

        <!-- Gráfico 3: Tipo de Documentación Desactualizada (Circular) -->
        <div class="chart-container">
           <center><h2>Tipo de Documentación Desactualizada</h2></center> 
            <canvas id="tipoDocumentacionDesactualizadaChart"></canvas>
            <button onclick="downloadPDF('tipoDocumentacionDesactualizadaChart')">Descargar PDF</button>
        </div>

        <!-- Gráfico 4: Actualización Mensual por Área (Línea) -->
        <div class="chart-container">
            <center><h2>Actualización Mensual por Área</h2></center>
            <label for="area-select">Seleccionar Área:</label>
            <select id="area-select" onchange="actualizarGrafica(this.value)">
                <option value="Todas">Todas las Áreas</option>
                <?php
                foreach ($areas2 as $area) {
                    echo "<option value='$area'>$area</option>";
                }
                ?>
            </select>
            <canvas id="actualizacionMensualChart"></canvas>
            <button onclick="downloadPDF('actualizacionMensualChart')">Descargar PDF</button>
        </div>
        <!-- Gráfico 5: Cantidad de Documentación Desactualizada por Área (Circular) -->
        <div class="chart-container">
            <center><h2>Cantidad de Documentación Desactualizada por Área</h2></center>
            <canvas id="cantidadDesactualizadaChart"></canvas>
            <button onclick="downloadPDF('cantidadDesactualizadaChart')">Descargar PDF</button>
        </div>

        <!-- Gráfico 6: Documentos Obsoletos por Área (PolarArea) -->
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

        // Gráfico 3: Tipo de Documentación Desactualizada (Circular)
        new Chart(document.getElementById('tipoDocumentacionDesactualizadaChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($tipos); ?>, // Nombres completos
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
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Tipo de Documentación Desactualizada'
                    }
                }
            }
        });

        // Gráfico 4: Actualización Mensual por Área
        function downloadPDF(chartId) {
            const canvas = document.getElementById(chartId);
            const canvasImage = canvas.toDataURL("image/png", 1.0);
            const pdf = new jsPDF('landscape');
            pdf.addImage(canvasImage, 'PNG', 10, 10, 280, 150);
            pdf.save(`${chartId}.pdf`);
        }

        // Función para obtener un color aleatorio
        function getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        // Datos de actualización mensual por área desde PHP
        const actualizacionMensualData = <?php echo json_encode($actualizacionMensualData); ?>;
        const meses = <?php echo json_encode($meses); ?>;

        // Inicializar gráfico de Actualización Mensual
        let ctx = document.getElementById('actualizacionMensualChart').getContext('2d');
        let chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses,
                datasets: []
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Actualización Mensual por Área'
                    },
                    legend: {
                        display: true
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Mes'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Cantidad'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // Función para actualizar el gráfico cuando se seleccione un área
        function actualizarGrafica(areaSeleccionada) {
            let dataset = [];
            if (areaSeleccionada === "Todas") {
                for (let area in actualizacionMensualData) {
                    dataset.push({
                        label: area,
                        data: Object.values(actualizacionMensualData[area]),
                        fill: false,
                        borderColor: getRandomColor(),
                        tension: 0.1
                    });
                }
            } else {
                dataset.push({
                    label: areaSeleccionada,
                    data: Object.values(actualizacionMensualData[areaSeleccionada]),
                    fill: false,
                    borderColor: getRandomColor(),
                    tension: 0.1
                });
            }

            chart.data.datasets = dataset;
            chart.update();
        }

        // Cargar gráfico con todas las áreas al inicio
        actualizarGrafica("Todas");
        // Cambiar el gráfico cuando el usuario selecciona un área
        document.getElementById('area-select').addEventListener('change', function() {
            const areaSeleccionada = this.value;
            actualizarGrafica(areaSeleccionada);
        });

        // Función para generar colores aleatorios
        function getRandomColor() {
            const r = Math.floor(Math.random() * 255);
            const g = Math.floor(Math.random() * 255);
            const b = Math.floor(Math.random() * 255);
            return `rgba(${r}, ${g}, ${b}, 0.6)`;
        }

        // Inicializar la gráfica con todas las áreas seleccionadas
        actualizarGrafica("Todas");

        // Gráfico 5: Cantidad de Documentación Desactualizada por Área (Circular)
        new Chart(document.getElementById('cantidadDesactualizadaChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($areas5); ?>,
                datasets: [{
                    label: 'Cantidad Desactualizada',
                    data: <?php echo json_encode($cantidadDesactualizada); ?>,
                    backgroundColor: <?php echo json_encode(array_map(function() {
                        return 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 0.6)';
                    }, $cantidadDesactualizada)); ?>
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Cantidad de Documentación Desactualizada por Área'
                    },
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        // Gráfico 6: Documentos Obsoletos por Área (Polar Area)
        new Chart(document.getElementById('documentosObsoletosChart'), {
            type: 'polarArea',
            data: {
                labels: <?php echo json_encode($areas6); ?>,
                datasets: [{
                    label: 'Cantidad Obsoleta',
                    data: <?php echo json_encode($cantidadObsoleta); ?>,
                    backgroundColor: <?php echo json_encode(array_map(function() {
                        return 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 0.6)';
                    }, $cantidadObsoleta)); ?>
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Documentos Obsoletos por Área'
                    },
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    </script>
    </div>
</body>
</html>
