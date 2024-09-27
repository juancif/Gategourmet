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

// Inicializar variables para cada campo de filtro
$campos = [
    'proceso', 'codigo', 'titulo_documento', 'tipo', 'version', 'estado', 'fecha_aprobacion', 
    'areas'
];

$params = [];
$types = '';
$searchValues = [];

// Recopilar los valores de los filtros enviados por POST
foreach ($campos as $campo) {
    $$campo = $_POST[$campo] ?? '';
    if (!empty($$campo)) {
        $params[] = "%" . $$campo . "%";
        $types .= 's';
        $searchValues[$campo] = $$campo;
    }
}

// Construir la consulta SQL base
$sql = "SELECT proceso, codigo, titulo_documento, tipo, version, estado, fecha_aprobacion, 
        areas, motivo_del_cambio, tiempo_de_retencion, responsable_de_retencion, 
        lugar_de_almacenamiento_fisico, lugar_de_almacenamiento_magnetico, conservacion, 
        disposicion_final, copias_controladas, fecha_de_vigencia, dias, senal_alerta, 
        obsoleto, anulado, en_actualizacion 
        FROM listado_maestro WHERE 1=1";

// Añadir filtros a la consulta de manera dinámica
foreach ($searchValues as $campo => $valor) {
    $sql .= " AND $campo LIKE ?";
}

// Preparar la consulta
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error);
}

// Vincular los parámetros si hay filtros
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Ejecutar la consulta y obtener los resultados
if (!$stmt->execute()) {
    die("Error al ejecutar la consulta: " . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die("Error al obtener los resultados: " . $stmt->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado Maestro</title>
    <link rel="stylesheet" href="listado_maestro.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>
    <header class="header">
        <h1>Listado Maestro</h1>
    </header>

    <div class="container">
        <div class="search-bar">
            <form method="post">
                <div class="search-fields">
                    <?php foreach ($campos as $campo): ?>
                        <div class="search-field">
                            <label for="<?php echo htmlspecialchars($campo); ?>"><?php echo ucwords(str_replace('_', ' ', $campo)); ?></label>
                            <input type="text" class="search-input" id="<?php echo htmlspecialchars($campo); ?>" 
                                   name="<?php echo htmlspecialchars($campo); ?>" autocomplete="off">
                            <div class="search-dropdown" id="<?php echo htmlspecialchars($campo); ?>-options"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit">Buscar</button>
            </form>
        </div>

        <?php if (isset($result) && $result->num_rows > 0): ?>
            <div class="table-wrapper">
                <table id="listado-maestro-table">
                    <thead>
                        <tr>
                            <?php foreach ($campos as $campo): ?>
                                <th><?php echo ucwords(str_replace('_', ' ', $campo)); ?></th>
                            <?php endforeach; ?>
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
                            <th>Señal de Alerta</th>
                            <th>Obsoleto</th>
                            <th>Anulado</th>
                            <th>En Actualización</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): 
                            $rowClass = '';

                            switch (strtolower($row['estado'])) {
                                case 'vigente':
                                    $rowClass = 'vigente';
                                    break;
                                case 'desactualizado':
                                    $rowClass = 'desactualizado';
                                    break;
                                case 'obsoleto':
                                    $rowClass = 'obsoleto';
                                    break;
                            }
                        ?>
                        <tr class="<?php echo htmlspecialchars($rowClass); ?>">
                            <?php foreach ($campos as $campo): ?>
                                <td><?php echo htmlspecialchars($row[$campo]); ?></td>
                            <?php endforeach; ?>
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
        <?php else: ?>
            <p>No se encontraron documentos con los criterios de búsqueda proporcionados.</p>
        <?php endif; ?>

        <?php if (isset($conn)): ?>
            <?php $conn->close(); ?>
        <?php endif; ?>

        <!-- Botón para descargar el Excel -->
        <button id="download-excel" onclick="downloadExcel()">Descargar Excel</button>
    </div>

    <script>
    function downloadExcel() {
        // Referencia a la tabla HTML
        let table = document.getElementById("listado-maestro-table");

        // Crear un nuevo libro de trabajo
        let workbook = XLSX.utils.book_new();
        let visibleData = [];

        // Estilos para el Excel
        const headerStyle = {
            fill: {
                fgColor: { rgb: "4F81BD" } // Color de fondo azul para cabeceras
            },
            font: {
                color: { rgb: "FFFFFF" }, // Color de texto blanco
                bold: true,
                sz: 12,
                name: "Arial"
            },
            alignment: {
                horizontal: "center"
            }
        };

        const cellStyle = {
            font: {
                sz: 11,
                name: "Arial"
            },
            alignment: {
                horizontal: "left"
            }
        };

        // Recorrer las filas de la tabla y solo añadir las visibles
        let rows = table.querySelectorAll("tbody tr");
        for (let row of rows) {
            if (row.style.display !== "none") { // Solo considerar filas visibles
                let cells = row.querySelectorAll("td");
                let rowData = [];
                cells.forEach(cell => {
                    rowData.push(cell.innerText);
                });
                visibleData.push(rowData);
            }
        }

        // Añadir la cabecera de la tabla
        let header = [];
        table.querySelectorAll("thead th").forEach(th => {
            header.push(th.innerText);
        });
        visibleData.unshift(header); // Añadir la cabecera como la primera fila

        // Convertir los datos visibles a una hoja de trabajo
        let ws = XLSX.utils.aoa_to_sheet(visibleData);
        
        // Aplicar estilo a las celdas
        for (let col = 0; col < header.length; col++) {
            ws['A1'].s = headerStyle; // Estilo para cabecera
            for (let row = 2; row < visibleData.length; row++) {
                const cell = ws[XLSX.utils.encode_cell({r: row, c: col})];
                if (cell) {
                    cell.s = cellStyle; // Estilo para celdas de datos
                }
            }
        }

        // Añadir la hoja de trabajo al libro
        XLSX.utils.book_append_sheet(workbook, ws, "Listado Maestro");

        // Guardar el archivo Excel
        XLSX.writeFile(workbook, "listado_maestro.xlsx");
    }
    </script>
</body>
</html>
