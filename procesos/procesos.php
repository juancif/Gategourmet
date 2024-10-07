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

// Inicializar variable para el mensaje de error
$error_message = "";

// Validar el correo electrónico antes de insertar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $macroproceso = $_POST['macroproceso'];

    // Verificar el dominio del correo electrónico
    if (!preg_match("/@gategroup\.com$/", $email)) {
        $error_message = "Error: Solo se permiten correos electrónicos de gategroup.com";
    } else {
        // Comprobar si el usuario existe en la tabla de usuarios
        $sqlCheckUser = "SELECT * FROM usuarios WHERE correo = '$email'";
        $resultCheckUser = $conn->query($sqlCheckUser);

        if ($resultCheckUser->num_rows == 0) {
            $error_message = "Error: El usuario no está registrado en la tabla de usuarios. No se puede agregar el macroproceso.";
        } else {
            // Comprobar si el usuario existe en la tabla de administradores
            $sqlCheckAdmin = "SELECT * FROM administradores WHERE correo = '$email'";
            $resultCheckAdmin = $conn->query($sqlCheckAdmin);

            if ($resultCheckAdmin->num_rows == 0) {
                $error_message = "Error: El usuario no está registrado en la tabla de administradores. No se puede agregar el macroproceso.";
            } else {
                // Obtener el color correspondiente al macroproceso
                $color = "";
                if ($macroproceso == "GESTION CORPORATIVA") {
                    $color = "Amarillo"; // Por ejemplo, amarillo para gestión corporativa
                } 
                // Aquí puedes añadir más condiciones para otros macroprocesos y colores

                // Insertar macroproceso si el usuario está registrado en ambas tablas
                $sql = "INSERT INTO procesos (macroproceso, email, color) VALUES ('$macroproceso', '$email', '$color')";
                
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Nuevo proceso agregado correctamente');</script>";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        }
    }
}

// Consulta SQL para obtener todos los campos de la tabla 'procesos' ordenados por macroproceso y color
$sql = "SELECT macroproceso, proceso, usuario, cargo, email, rol, color FROM procesos 
        ORDER BY 
            macroproceso ASC, 
            CASE
                WHEN color = 'Amarillo' THEN 1
                WHEN color = 'Rojo' THEN 2
                WHEN color = 'Verde' THEN 3
                ELSE 4
            END";

$result = $conn->query($sql);

// Comprobar si la consulta fue exitosa
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

// Obtener el color según el índice de la fila
function obtenerColorFila($color) {
    switch ($color) {
        case 'Amarillo':
            return 'yellow-background'; // Clase CSS para amarillo
        case 'Rojo':
            return 'red-background'; // Clase CSS para rojo
        case 'Verde':
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
    <header class="header">
        MAPEO DE PROCESOS
    </header>
    <li class="nav__item__user">
        <a href="http://localhost/GateGourmet/Index/index_admin.html" class="cerrar__sesion__link">
            <img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario">
            <div class="cerrar__sesion">Volver al inicio</div>
        </a>
    </li>
    <main>
        <section class="container">
            <!-- Formulario para agregar macroproceso -->
            <form id="formularioMacroproceso" action="" method="POST">
                <label for="macroproceso">Macroproceso:</label>
                <input type="text" id="macroproceso" name="macroproceso" required>

                <label for="email">Email (usuario registrado):</label>
                <input type="email" id="email" name="email" required>
                <div id="emailError" style="color: red;"><?php echo $error_message; ?></div>

                <button type="submit">Agregar Macroproceso</button>
            </form>

            <!-- Tabla de procesos existentes -->
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
                            while ($row = $result->fetch_assoc()) {
                                // Obtener la clase de color según el valor del campo 'color'
                                $colorClass = obtenerColorFila($row["color"]);

                                // Asignar cada valor a su columna correspondiente
                                echo "<tr>";
                                echo "<td class='$colorClass'>" . htmlspecialchars($row["macroproceso"]) . "</td>";
                                echo "<td class='$colorClass'>" . htmlspecialchars($row["proceso"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["usuario"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["cargo"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["rol"]) . "</td>";
                                echo "</tr>";
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
<script>
    function validarEmail() {
        const emailInput = document.getElementById('email').value;
        const errorDiv = document.getElementById('emailError');

        // Comprobar si el email tiene el dominio correcto
        if (!emailInput.endsWith('@gategroup.com')) {
            errorDiv.textContent = 'Error: Solo se permiten correos electrónicos de gategroup.com';
            return false;
        } else {
            errorDiv.textContent = ''; // Limpiar el mensaje de error si es válido
        }

        return true; // Si todo está correcto, permitir el envío del formulario
    }
</script>
</body>
</html>
