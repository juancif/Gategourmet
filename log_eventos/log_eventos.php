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

// Obtener los registros de movimientos
$sql = "SELECT * FROM movimientos ORDER BY fecha DESC";
$result = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log de Eventos - GateGourmet</title>
    <link rel="stylesheet" href="style_log_eventos.css"> <!-- Crea un archivo CSS para esta página -->
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
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_usuario']); ?></td>
                        <td><?php echo htmlspecialchars($row['accion']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
    <button onclick="window.location.reload();">Recargar Eventos</button>
</body>
</html>

<?php $connect->close(); ?>
