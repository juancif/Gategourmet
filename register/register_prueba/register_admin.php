<?php
include_once("config_register.php");

if (isset($_POST['Submit'])) {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];
    $correo = $_POST['correo'];
    $nombres_apellidos = $_POST['nombres_apellidos'];
    $documento = $_POST['documento'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];

    // Verificar si algún campo está vacío
    if (empty($nombre_usuario) || empty($contrasena) || empty($correo) || empty($nombres_apellidos) || empty($documento) || empty($area) || empty($cargo)) {
        if (empty($nombre_usuario)) {
            echo "<font color='red'>Campo: nombre_usuario está vacío.</font><br/>";
        }
        if (empty($contrasena)) {
            echo "<font color='red'>Campo: contrasena está vacío.</font><br/>";
        }
        if (empty($correo)) {
            echo "<font color='red'>Campo: correo está vacío.</font><br/>";
        }
        if (empty($nombres_apellidos)) {
            echo "<font color='red'>Campo: nombres_apellidos está vacío.</font><br/>";
        }
        if (empty($documento)) {
            echo "<font color='red'>Campo: documento está vacío.</font><br/>";
        }
        if (empty($area)) {
            echo "<font color='red'>Campo: área está vacío.</font><br/>";
        }
        if (empty($cargo)) {
            echo "<font color='red'>Campo: cargo está vacío.</font><br/>";
        }
        echo "<br/><a href='javascript:self.history.back();'>Volver</a>";
    } else {
        // Insertar datos en la base de datos
        $sql = "INSERT INTO administradores (nombre_usuario, contrasena, correo, nombres_apellidos, documento, area, cargo) 
                VALUES (:nombre_usuario, :contrasena, :correo, :nombres_apellidos, :documento, :area, :cargo)";
        $query = $dbConn->prepare($sql);

        $query->bindparam(':nombre_usuario', $nombre_usuario);
        $query->bindparam(':contrasena', $contrasena);
        $query->bindparam(':correo', $correo);
        $query->bindparam(':nombres_apellidos', $nombres_apellidos);
        $query->bindparam(':documento', $documento);
        $query->bindparam(':area', $area);
        $query->bindparam(':cargo', $cargo);
        $query->execute();

        if ($query->rowCount() > 0) {
            // Redirigir a la página deseada después del registro exitoso
            header("Location: http://localhost/GateGourmet/register/registro_exitoso_admin.php");
            exit();
        } else {
            echo "<font color='red'>Error al registrar el usuario.</font><br/>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Usuarios</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles_register_admin.css">
</head>
<body>
    <header class="header">
        <img src="../Imagenes/logo_oficial_color.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Usuarios</h2>
                <form method="post" action="">
                    <div class="input-group">
                        <label for="nombre_usuario">Nombre de Usuario</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                    </div>
                    <div class="input-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" required>
                    </div>
                    <div class="input-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" required>
                    </div>
                    <div class="input-group">
                        <label for="nombres_apellidos">Nombres y Apellidos</label>
                        <input type="text" id="nombres_apellidos" name="nombres_apellidos" required>
                    </div>
                    <div class="input-group">
                        <label for="documento">Documento</label>
                        <input type="text" id="documento" name="documento" required>
                    </div>
                    <div class="input-group">
                        <label for="area">Área</label>
                        <input type="text" id="area" name="area" required>
                    </div>
                    <div class="input-group">
                        <label for="cargo">Cargo</label>
                        <input type="text" id="cargo" name="cargo" required>
                    </div>
                    <div class="buttons">
                        <input type="submit" name="Submit" value="Registrarse" class="Registrarse">
                        <a href="http://localhost/GateGourmet/login/login3.php" class="button">Regresar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
    </footer>
</body>
</html>
