<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "gategourmet";

$connect = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($connect->connect_error) {
    die("Error de conexión: " . $connect->connect_error);
}

// Establecer el charset a utf8mb4 para manejar caracteres especiales
$connect->set_charset("utf8mb4");

// Variables para la búsqueda avanzada
$search_usuario = isset($_GET['search_usuario']) ? $connect->real_escape_string($_GET['search_usuario']) : '';
$search_accion = isset($_GET['search_accion']) ? $connect->real_escape_string($_GET['search_accion']) : '';
$search_fecha = isset($_GET['search_fecha']) ? $connect->real_escape_string($_GET['search_fecha']) : '';

// Consulta con filtro de búsqueda avanzada utilizando consultas preparadas
$sql = "SELECT * FROM movimientos WHERE 1=1";
$params = [];

if ($search_usuario) {
    $sql .= " AND nombre_usuario LIKE ?";
    $params[] = '%' . $search_usuario . '%';
}

if ($search_accion) {
    $sql .= " AND accion LIKE ?";
    $params[] = '%' . $search_accion . '%';
}

if ($search_fecha) {
    $sql .= " AND DATE(fecha) = ?";
    $params[] = $search_fecha;
}

$sql .= " ORDER BY fecha DESC";

$stmt = $connect->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params)); // Todos los parámetros son cadenas (strings)
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
    <title>Log de Eventos - GateGourmet</title>
    <link rel="stylesheet" href="style_log_eventos.css"> <!-- Enlace al archivo CSS -->
</head>
<body>
    <header>
        <h1>Log de Eventos</h1>
    </header>
    <li class="nav__item__user">
        <a href="http://localhost/GateGourmet/Index/index_admin.php" class="cerrar__sesion__link">
            <img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario">
            <div class="cerrar__sesion">Volver al inicio</div>
        </a>
    </li>
    <main>
        <!-- Filtro de búsqueda avanzada -->
        <form method="GET" action="">
            <div class="filter-container">
                <input type="text" name="search_usuario" placeholder="Buscar por usuario" value="<?php echo htmlspecialchars($search_usuario); ?>">
                <input type="text" name="search_accion" placeholder="Buscar por acción" value="<?php echo htmlspecialchars($search_accion); ?>">
                <input type="date" name="search_fecha" value="<?php echo htmlspecialchars($search_fecha); ?>">
                <button type="submit" class="btn-search">Buscar</button>
            </div>
        </form>

        <!-- Tabla de eventos -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($row['accion']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No se encontraron eventos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Botón para recargar eventos -->
        <div class="btn-container">
            <button onclick="window.location.reload();" class="btn-reload">Recargar Eventos</button>
        </div>
    </main>
</body>
</html>

<?php 
$stmt->close();
$connect->close();
?>
