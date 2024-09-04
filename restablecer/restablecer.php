<?php
// Establecer la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "gategourmet";

$connect = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($connect->connect_error) {
    die("Error de conexión: " . $connect->connect_error);
}

// Verificar si se ha enviado el formulario de restablecimiento de contraseña
if (isset($_POST['token']) && isset($_POST['password'])) {
    $token = $_POST['token'];
    $nueva_contrasena = $_POST['password'];

    // Validar el token
    $stmt = $connect->prepare("SELECT email FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        
        // Actualizar la contraseña en la base de datos
        $hashed_password = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
        $stmt = $connect->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        $stmt->execute();
        
        // Eliminar el token usado
        $stmt = $connect->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        echo "Contraseña actualizada exitosamente.";
    } else {
        echo "Token de restablecimiento inválido.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Aquí debes agregar la lógica para generar el token de restablecimiento y enviarlo por correo
    $token = bin2hex(random_bytes(50)); // Generar un token aleatorio

    // Guardar el token y la dirección de correo en la base de datos
    $stmt = $connect->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();

    // Enviar el correo electrónico con el enlace de restablecimiento
    $resetLink = "http://localhost/GateGourmet/restablecer_contrasena.php?token=" . $token;
    $subject = "Restablecimiento de Contraseña";
    $message = "Haz clic en el siguiente enlace para restablecer tu contraseña: " . $resetLink;
    $headers = "From: no-reply@gategourmet.com";

    if (mail($email, $subject, $message, $headers)) {
        echo "Se ha enviado un enlace de restablecimiento de contraseña a tu correo electrónico.";
    } else {
        echo "Error al enviar el correo electrónico.";
    }
} else {
    // Mostrar formulario de solicitud de restablecimiento
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Restablecer Contraseña</title>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;600&display=swap">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <link rel="stylesheet" href="restablecer.css">
    </head>
    <body>
        <header class="header">
            <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
        </header>
        <main class="main-content">
            <div class="reset-container">
                <h2>Restablecer Contraseña</h2>
                <form method="post" action="">
                    <div class="input-group">
                        <label for="email" class="texto_correo">Correo Electrónico</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" required placeholder="Correo Electrónico"/>
                        </div>
                    </div>
                    <div class="buttons">
                        <input type="submit" value="Enviar Enlace de Restablecimiento">
                        <a href="http://localhost/GateGourmet/login/login3.php" class="link_volver"><button class="boton_volver">Volver</a></button>
                    </div>
                </form>
            </div>
        </main>
        <footer class="footer">
            <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
        </footer>
    </body>
    </html>
    <?php
}

$connect->close();
?>