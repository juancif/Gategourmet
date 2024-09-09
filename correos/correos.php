<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correos Electrónicos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .email-list {
            margin-top: 20px;
        }

        .email-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .email-item h2 {
            margin: 0;
            color: #333;
        }

        .email-item p {
            margin: 5px 0;
            color: #666;
        }

        .email-item hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 10px 0;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Correos Electrónicos Almacenados</h1>
        <div class="email-list">
            <?php
                // Aquí va el código PHP para mostrar los correos
                // Conectarse a la base de datos
                $conexion = new mysqli('localhost', 'root', '', 'gategourmet');

                if ($conexion->connect_error) {
                    die("Error de conexión a la base de datos: " . $conexion->connect_error);
                }

                // Consultar los correos almacenados
                $sql = "SELECT asunto, remitente, fecha, cuerpo FROM correos ORDER BY fecha DESC";
                $resultado = $conexion->query($sql);

                if ($resultado->num_rows > 0) {
                    while ($fila = $resultado->fetch_assoc()) {
                        echo '<div class="email-item">';
                        echo '<h2>Asunto: ' . $fila['asunto'] . '</h2>';
                        echo '<p><strong>De:</strong> ' . $fila['remitente'] . '</p>';
                        echo '<p><strong>Fecha:</strong> ' . $fila['fecha'] . '</p>';
                        echo '<hr>';
                        echo '<p>' . nl2br($fila['cuerpo']) . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No hay correos almacenados.</p>';
                }

                // Cerrar la conexión
                $conexion->close();
            ?>
        </div>
    </div>
    <p><a href="logout.php" >Desconectar</a></p>
</body>
</html>

