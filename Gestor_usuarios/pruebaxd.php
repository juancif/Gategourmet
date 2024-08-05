<?php
include_once("config_gestor.php");

// Consulta a la base de datos
$query = "SELECT * FROM procesos";
$result = $dbConn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mostrar Procesos</title>
    <style>
        .tabla_principal {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla_principal th, .tabla_principal td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .tabla_principal th {
            background-color: #f2f2f2;
        }
        .tabla_secundaria {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <table class="tabla_principal">
        <tr class="tabla_secundaria">
            <!-- Encabezados de la tabla -->

        </tr>
        <?php
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $column) {
                echo "<td>" . htmlspecialchars($column) . "</td>";
            }
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>
