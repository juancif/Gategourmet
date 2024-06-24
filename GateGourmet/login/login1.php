<?php

// Establecer la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "prueba";

$connect = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($connect->connect_error) {
    die("Error de conexión: " . $connect->connect_error);
}

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si los campos del formulario fueron enviados
    if (isset($_POST['nombre_completo']) && isset($_POST['contrasena'])) {
        // Recuperar los datos del formulario
        $nombre_completo = $_POST['nombre_completo'];
        $contrasena = $_POST['contrasena'];

        // Preparar la consulta para verificar las credenciales
        $stmt = $connect->prepare("SELECT * FROM users WHERE nombre_completo = ? AND contrasena = ?");
        $stmt->bind_param("ss", $nombre_completo, $contrasena);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si se encontraron resultados
        if ($result->num_rows > 0) {
            // Las credenciales son correctas, redirigir al usuario a la página principal
            header("Location: http://localhost/Pagina_Prueba/index/Prueba_Index/index1.html");
            exit(); // Terminar el script después de la redirección
        } 
    } else {
        // Si no se enviaron los campos del formulario, mostrar un mensaje de error
        echo "Por favor, ingrese nombre de usuario y contrasena.";
    }
}

// Cerrar la conexión
$connect->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_login1.css">
    <title>Login</title>
</head>
<body>
    <div class="container_logo"></div>
        <div class="container_principal">
            <form>
                <img src="../login/logo_oficial_B-N.png" class="logo">
              <center><h2>INGRESO</h2></center>
</form>
            <form action="" method="post">
              <input type="text" name="nombre_completo" required placeholder="Nombres y apellidos" value="<?php if(isset($_POST['nombre_completo'])) echo $_POST['nombre_completo'] ?>" class="campo_nombre"/><br /><br />
              <input type="password" name="contrasena" required placeholder="Contraseña" value="<?php if(isset($_POST['contrasena'])) echo $_POST['contrasena'] ?>" class="campo_contrasena" /><br/><br/>
              <input type="submit" name='login' value="Ingresar" class='inicio'>
          </form>
          <a href="../register/register1.php" class="link__registro"><input type="submit" name='registro' value="Registrarse" class='registro'></a> 
          <script src="script.js"></script>
</body>
</html>