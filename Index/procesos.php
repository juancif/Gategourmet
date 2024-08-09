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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Procesos</title>
    <link rel="stylesheet" href="procesos.css">
</head>
<body>
    <header>
        <h1>MAPEO DE PROCESOS</h1>
    </header>

    <main>
        <section class="container">
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
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["macroproceso"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["proceso"]) . "</td>";
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
        </section>
    </main>

    <?php
    // Cerrar conexión
    $conn->close();
    ?>

</body>
</html>
