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
if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error);
}

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
</head>
<body>
    <header class="header">
        <h1>LISTADO MAESTRO</h1>
    </header>

    <div class="container">
        <div class="search-bar">
            <form method="post">
                <div class="search-fields">
                    <?php foreach ($campos as $campo): ?>
                        <label for="<?php echo htmlspecialchars($campo); ?>"><?php echo ucwords(str_replace('_', ' ', $campo)); ?></label>
                        <input type="text" id="<?php echo htmlspecialchars($campo); ?>" name="<?php echo htmlspecialchars($campo); ?>" autocomplete="off" 
                            oninput="filterOptions(this, '<?php echo htmlspecialchars($campo); ?>')">
                        <div class="search-dropdown" id="<?php echo htmlspecialchars($campo); ?>-options">
                            <?php if (!empty($options[$campo])): ?>
                                <?php foreach ($options[$campo] as $option): ?>
                                    <div data-value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit">Buscar</button>
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
        function filterOptions(input, field) {
            const query = input.value.toLowerCase();
            const optionsList = document.getElementById(field + '-options');
            const options = optionsList.querySelectorAll('div');

            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                if (text.includes(query)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });

            optionsList.style.display = query ? 'block' : 'none';
        }

        document.addEventListener('click', function(event) {
            const target = event.target;
            if (target && target.hasAttribute('data-value')) {
                const field = target.parentElement.id.replace('-options', '');
                const inputField = document.getElementById(field);
                inputField.value = target.getAttribute('data-value');
                target.parentElement.style.display = 'none';
            } else if (!event.target.closest('.search-bar')) {
                document.querySelectorAll('.search-dropdown').forEach(list => list.style.display = 'none');
            }
        });
    </script>
</body>
</html>
