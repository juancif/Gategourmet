<?php
session_start();
$area = isset($_SESSION['area']) ? $_SESSION['area'] : '';

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

// Campos visibles hasta 'areas'
$campos_visibles = [
    'proceso', 'codigo', 'titulo_documento', 'tipo', 'version', 'estado', 'fecha_aprobacion', 
    'areas'
];

$campos_internos = [
    'motivo_del_cambio', 'tiempo_de_retencion', 'responsable_de_retencion', 
    'lugar_de_almacenamiento_fisico', 'lugar_de_almacenamiento_magnetico', 'conservacion', 
    'disposicion_final', 'copias_controladas', 'fecha_de_vigencia', 'dias', 'senal_alerta', 
    'obsoleto', 'anulado', 'en_actualizacion'
];

// Campos completos para la consulta
$campos = array_merge($campos_visibles, $campos_internos);

$params = [];
$types = '';
$searchValues = [];

// Recopilar los valores de los filtros enviados por POST
foreach ($campos_visibles as $campo) {
    $$campo = $_POST[$campo] ?? '';
    if (!empty($$campo) && $campo !== 'areas') {
        $searchValues[$campo] = $$campo;
        $params[] = "%" . $$campo . "%";
        $types .= 's';
    }
}

// Agregar filtro para el área del usuario
$searchValues['areas'] = $area; // Establece el área del usuario como filtro
$params[] = "%" . $area . "%";
$types .= 's';

// Construir la consulta SQL base hasta 'areas'
$sql = "SELECT " . implode(", ", $campos) . " FROM listado_maestro WHERE areas LIKE ?";

// Añadir filtros adicionales a la consulta de manera dinámica
foreach ($searchValues as $campo => $valor) {
    if ($campo !== 'areas') {
        $sql .= " AND $campo LIKE ?";
    }
}

// Preparar la consulta para buscar documentos
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error);
}

// Vincular los parámetros si hay filtros
if (!empty($params)) {
    // Convertir los parámetros en un array de referencias
    $ref_params = [];
    foreach ($params as $key => $value) {
        $ref_params[$key] = &$params[$key]; // Usar referencia
    }

    // Usar call_user_func_array para pasar los parámetros por referencia
    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $ref_params));
}

// Ejecutar la consulta y obtener los resultados
if (!$stmt->execute()) {
    die("Error al ejecutar la consulta: " . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die("Error al obtener los resultados: " . $stmt->error);
}

// Preparar la consulta para obtener un registro de ejemplo
$sql_for_example = "SELECT " . implode(", ", $campos) . " FROM listado_maestro WHERE areas LIKE ? LIMIT 1";

$stmt_example = $conn->prepare($sql_for_example);
if ($stmt_example === false) {
    die("Error al preparar la consulta de ejemplo: " . $conn->error);
}

// Vincular el parámetro para la consulta de ejemplo
$stmt_example->bind_param('s', $params[count($params) - 1]); // Último parámetro es el filtro de área

if (!$stmt_example->execute()) {
    die("Error al ejecutar la consulta de ejemplo: " . $stmt_example->error);
}

$result_example = $stmt_example->get_result();
$defaultValues = [];
if ($result_example && $result_example->num_rows > 0) {
    $defaultValues = $result_example->fetch_assoc();
}

// Ahora puedes usar $defaultValues para autocompletar los campos
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado Maestro</title>
    <link rel="stylesheet" href="listado_maestro.css">
    <style>
        label {
            display: block;
            margin-top: 10px;
        }
        input[type="text"] {
            width: 300px;
            padding: 5px;
        }
        input[type="submit"] {
            margin-top: 20px;
            padding: 10px 20px;
        }
        .search-field {
            margin-bottom: 10px;
            position: relative;
        }
        .search-dropdown {
            display: none;
            border: 1px solid #ddd;
            position: absolute;
            background-color: #fff;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        }
        .dropdown-item {
            padding: 10px;
            cursor: pointer;
        }
        .dropdown-item:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
<header class="header">
    <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
</header>
<li class="nav__item__user">
    <a href="http://localhost/GateGourmet/Index/index_admin.php" class="cerrar__sesion__link">
        <img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario">
        <div class="cerrar__sesion">Volver al inicio</div>
    </a>
</li>
<div class="container">
    <!-- Barra de búsqueda -->
    <div class="search-bar">
        <form method="post">
            <div class="search-fields">
                <!-- Generación dinámica de los campos de búsqueda -->
                <?php foreach ($campos_visibles as $campo): ?>
                    <div class="search-field">
                        <label for="<?php echo htmlspecialchars($campo); ?>"><?php echo ucwords(str_replace('_', ' ', $campo)); ?></label>
                        <input type="text" class="search-input" id="<?php echo htmlspecialchars($campo); ?>" 
                               name="<?php echo htmlspecialchars($campo); ?>" 
                               value="<?php echo htmlspecialchars($searchValues[$campo] ?? ($defaultValues[$campo] ?? '')); ?>" 
                               autocomplete="off" <?php echo $campo === 'areas' ? 'readonly' : ''; ?>>
                        <div class="search-dropdown" id="<?php echo htmlspecialchars($campo); ?>-options"></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit">Buscar</button>
        </form>
    </div>

    <!-- Resultados de la búsqueda -->
    <?php if (isset($result) && $result->num_rows > 0): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($campos_visibles as $campo): ?>
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
                        <?php foreach ($campos_visibles as $campo): ?>
                            <td><?php echo htmlspecialchars($row[$campo]); ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No se encontraron resultados.</p>
    <?php endif; ?>

    <!-- Autocompletar -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.search-input').on('input', function() {
            let filtro = $(this).attr('name');
            let query = $(this).val();

            if (query.length > 2 && filtro !== 'areas') { // Evitar el autocompletado para el campo 'areas'
                $.ajax({
                    url: 'autocomplete.php',
                    method: 'POST',
                    data: { filtro: filtro, query: query },
                    success: function(data) {
                        let options = JSON.parse(data);
                        let dropdown = $('#' + filtro + '-options');
                        dropdown.empty();
                        options.forEach(option => {
                            dropdown.append('<div class="dropdown-item">' + option + '</div>');
                        });
                        dropdown.show();
                    }
                });
            } else {
                $('#' + filtro + '-options').hide();
            }
        });

        $(document).on('click', '.dropdown-item', function() {
            let selectedText = $(this).text();
            let input = $(this).closest('.search-field').find('.search-input');
            input.val(selectedText);
            $('#' + input.attr('name') + '-options').hide();
        });
    });
    </script>
</div>
</body>
</html>
