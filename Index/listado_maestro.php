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

// Inicializar variables para cada campo de filtro (excluyendo 'motivo_del_cambio' hasta 'en_actualizacion')
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

// Construir la consulta SQL base (mantener los campos eliminados en el SELECT)
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
</head>
<body>
    <header class="header">
        <h1>Listado Maestro</h1>
    </header>

    <div class="container">
        <!-- Barra de búsqueda -->
        <div class="search-bar">
            <form method="post">
                <div class="search-fields">
                    <!-- Generación dinámica de los campos de búsqueda -->
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

        <!-- Resultados de la búsqueda -->
        <?php if (isset($result) && $result->num_rows > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($campos as $campo): ?>
                                <th><?php echo ucwords(str_replace('_', ' ', $campo)); ?></th>
                            <?php endforeach; ?>
                            <!-- Añadir columnas para los campos eliminados de los filtros -->
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
                            <!-- Mostrar los campos eliminados -->
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
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.search-input').on('input', function() {
            let input = $(this);
            let query = input.val().trim();
            let filtro = input.attr('name'); // Usamos el nombre del campo como filtro

            if (query.length > 1) {
                $.ajax({
                    url: 'autocomplete.php',
                    method: 'GET',
                    data: { query: query, filtro: filtro },
                    success: function(data) {
                        try {
                            let suggestions = JSON.parse(data);
                            let suggestionsList = input.next('.search-dropdown');

                            suggestionsList.empty(); // Limpiar las sugerencias anteriores

                            if (Array.isArray(suggestions) && suggestions.length > 0) {
                                suggestions.forEach(function(suggestion) {
                                    suggestionsList.append(`<div class="suggestion-item" data-value="${suggestion}">${suggestion}</div>`);
                                });
                                suggestionsList.show();
                            } else {
                                suggestionsList.hide();
                            }

                            // Mostrar y manejar el clic en las sugerencias
                            suggestionsList.find('.suggestion-item').on('click', function() {
                                input.val($(this).data('value'));
                                suggestionsList.hide();
                            });
                        } catch (e) {
                            console.error('Error al procesar la respuesta JSON:', e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', status, error);
                    }
                });
            } else {
                input.next('.search-dropdown').hide();
            }
        });

        // Ocultar sugerencias si se hace clic fuera del campo
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-input, .search-dropdown').length) {
                $('.search-dropdown').hide();
            }
        });
    });
    </script>
</body>
</html>
