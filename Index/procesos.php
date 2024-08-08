<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gategourmet";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener los datos de la tabla "procesos"
$sql = "SELECT id, macroproceso, proceso, usuarios, cargos, email, rol FROM procesos";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos de Procesos</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Datos de la Tabla Procesos</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Macroproceso</th>
            <th>Proceso</th>
            <th>Usuarios</th>
            <th>Cargos</th>
            <th>Email</th>
            <th>Rol</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            // Salida de datos para cada fila
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["macroproceso"] . "</td>";
                echo "<td>" . $row["proceso"] . "</td>";
                echo "<td>" . $row["usuarios"] . "</td>";
                echo "<td>" . $row["cargos"] . "</td>";
                echo "<td>" . $row["email"] . "</td>";
                echo "<td>" . $row["rol"] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No se encontraron resultados</td></tr>";
        }
        $conn->close();
        ?>

    </table>
</body>
</html>
