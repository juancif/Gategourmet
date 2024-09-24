<?php
// Conexión a la base de datos MySQL
$servername = "localhost";
$username = "root";
$password = "";
$database = "gategourmet";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Inicializar variables de búsqueda
$busqueda_codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$busqueda_proceso = isset($_GET['proceso']) ? $_GET['proceso'] : '';
$busqueda_usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$busqueda_cargo = isset($_GET['cargo']) ? $_GET['cargo'] : '';

// Consulta SQL para obtener los datos filtrados
$sql = "SELECT id, macroproceso, proceso, usuario, cargo, email, rol 
        FROM procesos 
        WHERE (codigo LIKE ? OR proceso LIKE ? OR usuario LIKE ?) 
        AND cargo LIKE ?";

// Preparar la consulta
$stmt = $conn->prepare($sql);

// Usar '%' para las búsquedas parciales
$busqueda_codigo = "%$busqueda_codigo%";
$busqueda_proceso = "%$busqueda_proceso%";
$busqueda_usuario = "%$busqueda_usuario%";
$busqueda_cargo = "%$busqueda_cargo%";

// Vincular parámetros
$stmt->bind_param('ssss', $busqueda_codigo, $busqueda_proceso, $busqueda_usuario, $busqueda_cargo);

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

// Obtener el color según el macroproceso
function obtenerColor($macroproceso) {
    switch ($macroproceso) {
        case 'GESTION CORPORATIVA':
        case 'COMPLIANCE':
            return 'yellow-background'; // Clase CSS para amarillo
        case 'SUPPLY CHAIN':
        case 'CULINARY EXCELLENCE':
        case 'SERVICE DELIVERY':
        case 'ASSEMBLY':
        case 'SERVICIOS INSTITUCIONALES':
            return 'red-background'; // Clase CSS para rojo
        case 'FINANCIERA':
        case 'COSTOS':
        case 'COMUNICACIONES':
        case 'TECNOLOGÍA DE LA INFORMACIÓN':
        case 'TALENTO HUMANO':
        case 'MANTENIMIENTO':
        case 'SERVICIO AL CLIENTE':
        case 'SECURITY':
            return 'green-background'; // Clase CSS para verde
        default:
            return ''; // Sin color
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Procesos</title>
    <link rel="stylesheet" href="procesos.css">
    <link rel="icon" href="/ruta/al/favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <h1>MAPEO DE PROCESOS</h1>
    </header>

    <main>
        <section class="container">
            <!-- Formulario de búsqueda -->
            <form method="GET" action="">
                <label for="codigo">Buscar por Código:</label>
                <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($busqueda_codigo); ?>">

                <label for="proceso">Buscar por Proceso:</label>
                <input type="text" id="proceso" name="proceso" value="<?php echo htmlspecialchars($busqueda_proceso); ?>">

                <label for="usuario">Buscar por Usuario:</label>
                <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($busqueda_usuario); ?>">

                <label for="cargo">Buscar por Cargo:</label>
                <input type="text" id="cargo" name="cargo" value="<?php echo htmlspecialchars($busqueda_cargo); ?>">

                <button type="submit">Buscar</button>
            </form>

            <!-- Tabla de procesos -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Macroproceso</th>
                            <th>Proceso</th>
                            <th>Usuario</th>
                            <th>Cargo</th>
                            <th>Email</th>
                            <th>Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            // Iterar a través de los resultados de la consulta
                            while ($row = $result->fetch_assoc()) {
                                // Obtener la clase de color según el macroproceso
                                $colorClass = obtenerColor($row['macroproceso']);
                                
                                // Generar filas de la tabla
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                echo "<td class='$colorClass'>" . htmlspecialchars($row["macroproceso"]) . "</td>";
                                echo "<td class='$colorClass'>" . htmlspecialchars($row["proceso"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["usuario"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["cargo"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["rol"]) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No hay datos disponibles</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

<?php
// Cerrar conexión
$stmt->close();
$conn->close();
?>
</body>
</html>
