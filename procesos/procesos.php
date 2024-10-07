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
$busqueda_proceso = isset($_GET['proceso']) ? $_GET['proceso'] : '';
$busqueda_usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$busqueda_cargo = isset($_GET['cargo']) ? $_GET['cargo'] : '';

// Consulta SQL usando consultas preparadas para evitar inyección SQL
$stmt = $conn->prepare("SELECT id, macroproceso, proceso, usuario, cargo, email, rol FROM procesos WHERE proceso LIKE ? AND usuario LIKE ? AND cargo LIKE ?");
$proceso_like = "%$busqueda_proceso%";
$usuario_like = "%$busqueda_usuario%";
$cargo_like = "%$busqueda_cargo%";
$stmt->bind_param("sss", $proceso_like, $usuario_like, $cargo_like);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <h1>MAPEO DE PROCESOS</h1>
    </header>

    <main>
        <section class="container">
            <!-- Formulario para agregar nuevo proceso -->
            <form method="POST" action="agregar_proceso.php" class="form-agregar">
                
                <div class="form-group">
                    <label for="macroproceso">Macroproceso:</label>
                    <input type="text" id="macroproceso" name="macroproceso" required>
                </div>
                <div class="form-group">
                    <label for="proceso">Proceso:</label>
                    <input type="text" id="proceso" name="proceso" required>
                </div>
                <div class="form-group">
                    <label for="usuario">Usuario:</label>
                    <input type="text" id="usuario" name="usuario" required>
                </div>
                <div class="form-group">
                    <label for="cargo">Cargo:</label>
                    <input type="text" id="cargo" name="cargo" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="rol">Rol:</label>
                    <input type="text" id="rol" name="rol" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn"><i class="fas fa-plus"></i> Agregar Proceso</button>
                </div>
            </form>

            <!-- Formulario de búsqueda -->
            <form method="GET" action="" class="form-agregar">
                <div class="form-group">
                    <label for="proceso">Buscar por Proceso:</label>
                    <input type="text" id="proceso" name="proceso" value="<?php echo htmlspecialchars($busqueda_proceso); ?>">
                </div>
                <div class="form-group">
                    <label for="usuario">Buscar por Usuario:</label>
                    <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($busqueda_usuario); ?>">
                </div>
                <div class="form-group">
                    <label for="cargo">Buscar por Cargo:</label>
                    <input type="text" id="cargo" name="cargo" value="<?php echo htmlspecialchars($busqueda_cargo); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn"><i class="fas fa-search"></i> Buscar</button>
                </div>
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
                                // Obtener el color para la fila de la tabla
                                $colorClass = obtenerColor($row['macroproceso']);
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
    $conn->close();
    ?>
</body>
</html>
