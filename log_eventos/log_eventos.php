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

// Variables para la búsqueda avanzada
$search_usuario = isset($_GET['search_usuario']) ? $connect->real_escape_string($_GET['search_usuario']) : '';
$search_accion = isset($_GET['search_accion']) ? $connect->real_escape_string($_GET['search_accion']) : '';
$search_fecha = isset($_GET['search_fecha']) ? $connect->real_escape_string($_GET['search_fecha']) : '';

// Consulta con filtro de búsqueda avanzada
$sql = "SELECT * FROM movimientos WHERE 1=1";

if ($search_usuario) {
    $sql .= " AND nombre_usuario LIKE '%$search_usuario%'";
}

if ($search_accion) {
    $sql .= " AND accion LIKE '%$search_accion%'";
}

if ($search_fecha) {
    $sql .= " AND DATE(fecha) = '$search_fecha'";
}

$sql .= " ORDER BY fecha DESC";
$result = $connect->query($sql);
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
        <a href="http://localhost/GateGourmet/Index/index_admin.html" class="cerrar__sesion__link">
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

<?php $connect->close(); ?>
