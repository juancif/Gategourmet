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

// Consulta SQL para obtener todos los campos de la tabla 'procesos'
$sql = "SELECT macroproceso, proceso, usuario, cargo, email, rol FROM procesos";
$result = $conn->query($sql);

// Comprobar si la consulta fue exitosa
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

// Obtener el color según el índice de la fila
function obtenerColorFila($index) {
    if ($index >= 1 && $index <= 13) {
        return 'yellow-background'; // Clase CSS para amarillo
    } elseif ($index >= 14 && $index <= 44) {
        return 'red-background'; // Clase CSS para rojo
    } elseif ($index >= 45 && $index <= 76) {
        return 'green-background'; // Clase CSS para verde
    } else {
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
    <header class="header">
        MAPEO DE PROCESOS
    </header>
    <li class="nav__item__user">
<<<<<<< HEAD
        <a href="http://localhost/GateGourmet/Index/index_admin.html" class="cerrar__sesion__link">
            <img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario">
            <div class="cerrar__sesion">Volver al inicio</div>
        </a>
=======
        <a href="http://localhost/GateGourmet/Index/index_admin.php" class="cerrar__sesion__link"><img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario"><div class="cerrar__sesion">Volver al inicio</div></a>
>>>>>>> 830c756f34f76d60d68e72cb1566acccfe4c2b48
    </li>
    <main>
        <section class="container">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
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
                            $rowIndex = 1; // Contador de filas
                            while ($row = $result->fetch_assoc()) {
                                // Obtener la clase de color según el índice de la fila
                                $colorClass = obtenerColorFila($rowIndex);

                                // Asignar cada valor a su columna correspondiente
                                echo "<tr>";
                                echo "<td class='$colorClass'>" . htmlspecialchars($row["macroproceso"]) . "</td>";  // Macroproceso
                                echo "<td class='$colorClass'>" . htmlspecialchars($row["proceso"]) . "</td>";  // Proceso
                                echo "<td>" . htmlspecialchars($row["usuario"]) . "</td>";  // Usuario
                                echo "<td>" . htmlspecialchars($row["cargo"]) . "</td>";  // Cargo
                                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";  // Email
                                echo "<td>" . htmlspecialchars($row["rol"]) . "</td>";  // Rol
                                echo "</tr>";

                                $rowIndex++; // Incrementar el contador de filas
                            }
                        } else {
                            echo "<tr><td colspan='6'>No hay datos disponibles</td></tr>";
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