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

// Consulta SQL para obtener los datos
$sql = "SELECT id, macroproceso, proceso, usuario, cargo, email, rol FROM procesos";
$result = $conn->query($sql);

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
    <li class="nav__item__user">
                <a href="http://localhost/GateGourmet/Index/index_admin.php" class="cerrar__sesion__link"><img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario"><div class="cerrar__sesion">Volver al inicio</div></a>
            </li>
    <main>
        <section class="container">
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
                            while ($row = $result->fetch_assoc()) {
                                // Obtener la clase de color según el macroproceso
                                $colorClass = obtenerColor($row['macroproceso']);
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                // Solo colorear las celdas de Macroproceso y Proceso usando la clase CSS
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