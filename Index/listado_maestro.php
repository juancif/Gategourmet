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
    'areas', 'motivo_del_cambio', 'tiempo_de_retencion', 'responsable_de_retencion', 
    'lugar_de_almacenamiento_fisico', 'lugar_de_almacenamiento_magnetico', 'conservacion', 
    'disposicion_final', 'copias_controladas', 'fecha_de_vigencia', 'dias', 'senal_alerta', 
    'obsoleto', 'anulado', 'en_actualizacion'
];

// Obtener opciones únicas para cada campo
$options = [];
foreach ($campos as $campo) {
    $query = "SELECT DISTINCT $campo FROM listado_maestro WHERE $campo IS NOT NULL AND $campo != ''";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $options[$campo] = [];
        while ($row = $result->fetch_assoc()) {
            $options[$campo][] = $row[$campo];
        }
    }
}

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
        obsoleto, anulado, en_actualizacion FROM listado_maestro WHERE 1=1";

// Añadir filtros a la consulta de manera dinámica
foreach ($searchValues as $campo => $valor) {
    $sql .= " AND $campo LIKE ?";
}

// Preparar la consulta
$stmt = $conn->prepare($sql);

// Vincular los parámetros si hay filtros
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Ejecutar la consulta y obtener los resultados
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado Maestro</title>
    <link rel="stylesheet" href="listado_maestro.css">
    <style>
       
    </style>
</head>
<body>
    <header class="header">
        <h1>LISTADO MAESTRO</h1>
    </header>

    <div class="container">
        <div class="search-bar">
            <form method="post">
                <span class="dropdown-toggle" onclick="toggleDropdown()">Buscar por Filtros</span>
                <div class="search-dropdown">
                    <?php foreach ($campos as $campo): ?>
                        <label for="<?php echo $campo; ?>"><?php echo ucwords(str_replace('_', ' ', $campo)); ?></label>
                        <select name="<?php echo $campo; ?>" id="<?php echo $campo; ?>">
                            <option value="">Selecciona una opción...</option>
                            <?php if (!empty($options[$campo])): ?>
                                <?php foreach ($options[$campo] as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($$campo == $option) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($option); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    <?php endforeach; ?>
                    <button type="submit">Buscar</button>
                </div>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($campos as $campo): ?>
                                <th><?php echo ucwords(str_replace('_', ' ', $campo)); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): 
                            $rowClass = '';

                            // Definir la clase de la fila basada en el estado
                            if (strtolower($row['estado']) == 'vigente') {
                                $rowClass = 'vigente';
                            } elseif (strtolower($row['estado']) == 'desactualizado') {
                                $rowClass = 'desactualizado';
                            } elseif (strtolower($row['estado']) == 'obsoleto') {
                                $rowClass = 'obsoleto';
                            }
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <?php foreach ($campos as $campo): ?>
                                <td><?php echo htmlspecialchars($row[$campo]); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No se encontraron documentos con los criterios de búsqueda proporcionados.</p>
        <?php endif; ?>

        <?php $conn->close(); ?>
    </div>

    <script>
        function toggleDropdown() {
            document.querySelector('.search-bar').classList.toggle('active');
        }
    </script>
</body>
</html>
