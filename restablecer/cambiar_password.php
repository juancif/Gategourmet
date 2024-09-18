<?php
$servername = "localhost"; // Cambiar si es necesario
$username = "root"; // Cambiar si es necesario
$password = ""; // Cambiar si es necesario
$database = "gategourmet";

// Crear conexión
$connect = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($connect->connect_error) {
    die("Error de conexión: " . $connect->connect_error);
}

// Si el método es POST y tenemos todos los parámetros necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    $token = $_POST['token'];
    $nueva_contrasena = $_POST['password'];
    $confirmar_contrasena = $_POST['confirm_password'];

    // Validar que las contraseñas coincidan
    if ($nueva_contrasena !== $confirmar_contrasena) {
        echo "<script>alert('Las contraseñas no coinciden.');window.history.back();</script>";
        exit();
    }

    // Validar longitud de la nueva contraseña
    if (strlen($nueva_contrasena) < 8) {
        echo "<script>alert('La contraseña debe tener al menos 8 caracteres.');window.history.back();</script>";
        exit();
    }

    // Buscar el token en la base de datos
    $stmt = $connect->prepare("SELECT correo, token_expiry FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si el token es válido
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $correo = $row['correo'];
        $token_expiry = $row['token_expiry'];

        // Verificar si el token ha expirado comparando la fecha de expiración en la base de datos
        $current_time = time(); // Tiempo actual en formato UNIX
        $token_expiry_time = strtotime($token_expiry); // Convertir la fecha de expiración a formato UNIX

        if ($current_time > $token_expiry_time) {
            echo "<script>alert('El token ha expirado.');window.history.back();</script>";
            exit();
        }

        // Verificar si la nueva contraseña es diferente a la actual
        $stmt = $connect->prepare("SELECT contrasena FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (password_verify($nueva_contrasena, $row['contrasena'])) {
            echo "<script>alert('La nueva contraseña no puede ser la misma que la actual.');window.history.back();</script>";
            exit();
        }

        // Actualizar la nueva contraseña en la base de datos
        $hashed_password = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
        $stmt = $connect->prepare("UPDATE usuarios SET contrasena = ? WHERE correo = ?");
        $stmt->bind_param("ss", $hashed_password, $correo);
        $stmt->execute();

        // Eliminar el token una vez que la contraseña haya sido actualizada
        $stmt = $connect->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        // Verificar que el token fue eliminado correctamente
        if ($stmt->affected_rows > 0) {
            // Redirigir al login después de actualizar la contraseña
            echo "<script>alert('Contraseña actualizada exitosamente. El enlace ya no puede ser utilizado.');window.location.href='http://10.24.217.100/Gategourmet/login/login3.php';</script>";
        } else {
            echo "<script>alert('Error al eliminar el token de restablecimiento. Inténtalo de nuevo.');window.history.back();</script>";
        }

    } else {
        // Si el token no es válido o ya ha sido utilizado
        echo "<script>alert('Token de restablecimiento inválido o ya utilizado.');window.location.href='http://10.24.217.100/Gategourmet/login/login3.php';</script>";
    }
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Restablecer Contraseña</title>
        <link rel="stylesheet" href="restablecer.css">
        <style>
            /* Estilos adicionales para campos simétricos */
            .reset-container {
                width: 100%;
                max-width: 400px;
                margin: 0 auto;
                padding: 20px;
                border-radius: 10px;
                background-color: rgb(82, 82, 82);
                box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            }

            .input-group {
                margin-bottom: 20px;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
            }

            .input-group label {
                margin-bottom: 8px;
                font-weight: bold;
                color: white;
            }

            .input-group input {
                width: 100%;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                font-size: 16px;
            }

            .buttons input {
                width: 100%;
                padding: 12px;
                background-color: rgb(82, 82, 82);
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                font-weight: bold;
                transition: background-color 0.3s ease;
            }

            .buttons input:hover {
                background-color: #003f7f;
            }
        </style>
    </head>
    <body>
        <header class="header">
            <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
        </header>
        <main class="main-content">
            <div class="reset-container">
                <h2>Restablecer Contraseña</h2>
                <form method="post" action="">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="input-group">
                        <label for="password">Nueva Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="input-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="buttons">
                        <input type="submit" value="Actualizar Contraseña">
                    </div>
                </form>
                <p><a href="http://10.24.217.100/Gategourmet/login/login3.php">Volver a iniciar sesión</a></p>
            </div>
        </main>
        <footer class="footer">
            <p>&copy; 2024 Gate Gourmet. Todos los derechos reservados.</p>
        </footer>
    </body>
    </html>
    <?php
} else {
    echo "Token no proporcionado.";
}

// Cerrar conexión a la base de datos
$connect->close();
?>
