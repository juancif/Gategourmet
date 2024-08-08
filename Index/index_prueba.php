<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gategourmet";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("La conexión falló: " . $conn->connect_error);
}

// Consultar datos
$sql = "SELECT * FROM procesos";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Crear tabla HTML
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>macroproceso</th>
            <th>proceso</th>
            <th>usuarios</th>
            <th>cargos</th>
            <th>email</th>
            <th>rol</th>
          </tr>";

    // Mostrar datos en la tabla
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["id"] . "</td>
                <td>" . $row["macroproceso"] . "</td>
                <td>" . $row["proceso"] . "</td>
                <td>" . $row["usuarios"] . "</td>
                <td>" . $row["cargos"] . "</td>
                <td>" . $row["email"] . "</td>
                <td>" . $row["rol"] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}

// Cerrar conexión
$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Datos</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Datos de la Tabla</h1>
    <!-- Aquí se incluirá el código PHP si es necesario -->
</body>
</html>
