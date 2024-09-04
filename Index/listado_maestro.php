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

$params = [];
$types = '';
$searchValues = [];

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

// Condicionalmente añadir filtros a la consulta
foreach ($searchValues as $campo => $valor) {
    $sql .= " AND $campo LIKE ?";
}

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);

// Vincular los parámetros dinámicamente
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

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
        body {
            font-family: 'Poppins', sans-serif;
            background: url('../Imagenes/fondogg3.webp') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin: 2rem auto;
            width: 90%;
            max-width: 1800px;
        }
        .header h1 {
            font-size: 2.5rem;
            color: #0b8b0f;
            background: rgba(255, 249, 249, 0.9);
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .search-bar {
            position: relative;
            max-width: 1000px;
            margin: 0 auto 2rem;
        }
        .search-bar input[type="text"], 
        .search-bar select {
            width: 100%;
            padding: 0.7rem;
            border: 2px solid #0b8b0f;
            border-radius: 6px;
            font-size: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .search-bar input[type="text"]:focus, 
        .search-bar select:focus {
            border-color: #085f05;
            outline: none;
        }
        .search-bar button {
            padding: 0.7rem 1.2rem;
            background-color: #0b8b0f;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .search-bar button:hover {
            background-color: #085f05;
            transform: translateY(-2px);
        }
        .search-bar button:active {
            transform: translateY(1px);
        }
        .search-dropdown {
            display: none;
            position: absolute;
            top: 50px;
            left: 0;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #0b8b0f;
            border-radius: 8px;
            padding: 10px;
            width: 100%;
            max-width: 1000px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .search-bar.active .search-dropdown {
            display: block;
        }
        .dropdown-toggle {
            cursor: pointer;
            color: #0b8b0f;
            text-decoration: underline;
            font-weight: bold;
        }
        .search-dropdown input[type="text"] {
            width: calc(100% - 20px);
            padding: 0.7rem;
            border: 2px solid #0b8b0f;
            border-radius: 6px;
            font-size: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .search-dropdown button {
            padding: 0.7rem 1.2rem;
            background-color: #0b8b0f;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .search-dropdown button:hover {
            background-color: #085f05;
            transform: translateY(-2px);
        }
        .search-dropdown button:active {
            transform: translateY(1px);
        }
        .container {
            width: 90%;
            max-width: 2400px;
            margin: 2rem auto;
            background: rgba(255, 249, 249, 0.9);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 10;
        }
        .table-wrapper {
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 8rem);
            border: 2px solid #000;
            padding-bottom: 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        table, th, td {
            border: 2px solid #000;
            background: rgba(255, 249, 249, 0.9);
        }
        th, td {
            padding: 12px;
            text-align: left;
            font-size: 14px;
            color: #000;
        }
        th {
            background-color: #0b8b0f;
            color: white;
        }
        tr:nth-child(even) {
            background: rgba(255, 249, 249, 0.9);
        }
        tr:nth-child(odd) {
            background: rgba(255, 249, 249, 0.95);
        }
        tr:hover td {
            background-color: rgba(0, 0, 0, 0.1);
        }
        tr.vigente td {
            background-color: rgba(51, 187, 255, 0.4);
            color: #000;
        }
        tr.desactualizado td {
            background-color: rgba(248, 59, 59, 0.4);
            color: #000;
        }
        tr.obsoleto td {
            background-color: rgba(141, 139, 139, 0.63);
            color: #000;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>LISTADO MAESTRO</h1>
    </header>

    <div class="search-bar">
        <form method="post">
            <input type="text" name="general_search" placeholder="Buscar en todos los campos..." onfocus="this.value='';">
            <span class="dropdown-toggle" onclick="toggleDropdown()">Más filtros</span>
            <div class="search-dropdown">
                <?php foreach ($campos as $campo): ?>
                    <input type="text" name="<?php echo $campo; ?>" placeholder="<?php echo ucwords(str_replace('_', ' ', $campo)); ?>" value="<?php echo htmlspecialchars($$campo); ?>">
                <?php endforeach; ?>
                <button type="submit">Buscar</button>
            </div>
        </form>
    </div>

    <script>
        function toggleDropdown() {
            document.querySelector('.search-bar').classList.toggle('active');
        }
    </script>

    <?php if ($result->num_rows > 0): ?>
        <div class="container">                                    
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
        </div>
    <?php else: ?>
        <p>No se encontraron documentos con los criterios de búsqueda proporcionados.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
