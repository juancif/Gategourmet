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
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Listado de Procesos y Usuarios</h2>

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
            // Output de cada fila
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["macroproceso"] . "</td>";
                echo "<td>" . $row["proceso"] . "</td>";
                echo "<td>" . $row["usuario"] . "</td>";
                echo "<td>" . $row["cargo"] . "</td>";
                echo "<td>" . $row["email"] . "</td>";
                echo "<td>" . $row["rol"] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No hay datos disponibles</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>

<?php
// Cerrar conexión
$conn->close();
?>
