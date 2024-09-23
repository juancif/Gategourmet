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
} else {
    if (isset($_GET['token'])) {
        $token = $_GET['token'];
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Restablecer Contraseña</title>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
        </head>
        <body>
            <h2>Restablecer Contraseña</h2>
            <form method="post" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div>
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div>
                    <input type="submit" value="Actualizar Contraseña">
                </div>
            </form>
        </body>
        </html>
        <?php
    } else {
        echo "Token no proporcionado.";
    }
}

$connect->close();
?>
