<?php
include_once("config_gestor.php");
if(isset($_POST['Submit'])) {
$nombre_usuario = $_POST['nombre_usuario'];
$contrasena = $_POST['contrasena'];
$correo = $_POST['correo'];
$nombres_apellidos = $_POST['nombres_apellidos'];
$documento = $_POST['documento'];
$area = $_POST['area'];
$cargo = $_POST['cargo'];
if( empty($nombre_usuario) ||  empty($contrasena) || empty($correo) || empty($nombres_apellidos) || 
 empty($documento) || empty($area) || empty($cargo)) {

if(empty($nombre_usuario)) {
echo "<font color='red'>Campo: nombre_usuario esta vacio.</font><br/>";
}
if(empty($contrasena)) {
    echo "<font color='red'>Campo: contrasena esta vacio.</font><br/>";
}
    if(empty($correo)) {
echo "<font color='red'>Campo: correo esta vacio.</font><br/>";
}
if(empty($nombres_apellidos)) {
echo "<font color='red'>Campo: nombres_apellidos esta vacio.</font><br/>";
}
    if(empty($documento)) {
echo "<font color='red'>Campo: documento esta vacio.</font><br/>";
}
if(empty($area)) {
echo "<font color='red'>Campo: area esta vacio.</font><br/>";
}
if(empty($cargo)) {
echo "<font color='red'>Campo: cargo esta vacio.</font><br/>";
}
echo "<br/><a href='javascript:self.history.back();'>Volver</a>";
} else {
$sql = "INSERT INTO usuarios (nombre_usuario, contrasena, correo, nombres_apellidos, documento, area, cargo) 
VALUES(:nombre_usuario, :contrasena, :correo, :nombres_apellidos, :documento, :area, :cargo)";
$query = $dbConn->prepare($sql);
$query->bindparam(':nombre_usuario', $nombre_usuario);
$query->bindparam(':contrasena', $contrasena);
$query->bindparam(':correo', $correo);
$query->bindparam(':nombres_apellidos', $nombres_apellidos);
$query->bindparam(':documento', $documento);
$query->bindparam(':area', $area);
$query->bindparam(':cargo', $cargo);
$query->execute();
$query->rowCount()."";
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
    <link rel="stylesheet" href="style_add_gestor.css">
</head>
<body>
    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Usuarios</h2>
                <form method="post" action="http://localhost/GateGourmet/Gestor_usuarios/index_gestor.php">
                    <div class="input-group">
                        <label for="nombre_usuario">Nombre de Usuario</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                    </div>
                    <div class="input-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="contrasena" id="contrasena" name="contrasena" required>
                    </div>
                    <div class="input-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" required>
                    </div>
                    <div class="input-group">
                        <label for="first_name">Nombr y apellidos</label>
                        <input type="text" id="nombres_apellidos" name="nombres_apellidos" required>
                    </div>
                    <div class="input-group">
                        <label for="document">Documento</label>
                        <input type="text" id="documento" name="documento" required>
                    </div>
                    <div class="input-group">
                        <label for="nombres_apellidos">Área Pertenece</label>
                        <input type="text" id="area" name="area">
                    </div>
                    <div class="input-group">
                        <label for="user_type">Cargo</label>
                        <input type="text" id="cargo" name="cargo">
                    </div>
                    <div class="buttons">
                    <input type="Submit" name="Submit" value="Registrarse" class="Registrarse"></input>
                        <a href="http://localhost/GateGourmet/Gestor_usuarios/index_gestor.php" class="button">Regresar</a>
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