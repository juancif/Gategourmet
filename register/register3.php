<?php
include_once("config3.php");
if(isset($_POST['Submit'])) {
$nombre_usuario = $_POST['nombre_usuario'];
$contrasena = $_POST['contrasena'];
$correo = $_POST['correo'];
$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$tipo_documento = $_POST['tipo_documento'];
$documento = $_POST['documento'];
$area = $_POST['area'];
$tipo_usuario = $_POST['tipo_usuario'];
if( empty($nombre_usuario) ||  empty($contrasena) || empty($correo) || empty($nombres) || empty($apellidos) 
||  empty($tipo_documento) || empty($documento) || empty($area) || empty($tipo_usuario)) {

if(empty($nombre_usuario)) {
echo "<font color='red'>Campo: nombre_usuario esta vacio.</font><br/>";
}
if(empty($contrasena)) {
    echo "<font color='red'>Campo: contrasena esta vacio.</font><br/>";
}
    if(empty($correo)) {
echo "<font color='red'>Campo: correo esta vacio.</font><br/>";
}
if(empty($nombres)) {
echo "<font color='red'>Campo: nombres esta vacio.</font><br/>";
}
if(empty($apellidos)) {
echo "<font color='red'>Campo: apellidos esta vacio.</font><br/>";
}
if(empty($tipo_documento)) {
    echo "<font color='red'>Campo: tipo_documento esta vacio.</font><br/>";
}
    if(empty($documento)) {
echo "<font color='red'>Campo: documento esta vacio.</font><br/>";
}
if(empty($area)) {
echo "<font color='red'>Campo: area esta vacio.</font><br/>";
}
if(empty($tipo_usuario)) {
echo "<font color='red'>Campo: tipo_usuario esta vacio.</font><br/>";
}
echo "<br/><a href='javascript:self.history.back();'>Volver</a>";
} else {
$sql = "INSERT INTO usuarios (nombre_usuario, contrasena, correo, nombres, apellidos, tipo_documento, documento, area, tipo_usuario) 
VALUES(:nombre_usuario, :contrasena, :correo, :nombres, :apellidos, :tipo_documento, :documento, :area, :tipo_usuario)";
$query = $dbConn->prepare($sql);

$query->bindparam(':nombre_usuario', $nombre_usuario);
$query->bindparam(':contrasena', $contrasena);
$query->bindparam(':correo', $correo);
$query->bindparam(':nombres', $nombres);
$query->bindparam(':apellidos', $apellidos);
$query->bindparam(':tipo_documento', $tipo_documento);
$query->bindparam(':documento', $documento);
$query->bindparam(':area', $area);
$query->bindparam(':tipo_usuario', $tipo_usuario);
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
    <link rel="stylesheet" href="style_register3.css">
</head>
<body>
    <header class="header">
        <img src="logo_oficial_color.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Usuarios</h2>
                <form method="post" action="http://localhost/Pagina_Prueba/index/Prueba_index/login_register/register/register3.php">
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
                        <label for="first_name">Nombres</label>
                        <input type="text" id="nombres" name="nombres" required>
                    </div>
                    <div class="input-group">
                        <label for="last_name">Apellidos</label>
                        <input type="text" id="apellidos" name="apellidos" required>
                    </div>
                    <div class="input-group">
                        <label for="document_type">Tipo de Documento</label>
                        <select id="tipo_documento" name="tipo_documento" required>
                            <option value="cc">Cédula de Ciudadanía</option>
                            <option value="ce">Cédula de Extranjería</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="document">Documento</label>
                        <input type="text" id="documento" name="documento" required>
                    </div>
                    <div class="input-group">
                        <label for="nombres">Área Pertenece</label>
                        <input type="text" id="area" name="area">
                    </div>
                    <div class="input-group">
                        <label for="user_type">Tipo de Usuario</label>
                        <select id="tipo_usuario" name="tipo_usuario" required>
                            <option value="administrativo">Administrativo</option>
                            <option value="operativo">Operativo</option>
                            <option value="jefe_de_nombres">Jefe De nombres</option>
                        </select>
                    </div>
                    <div class="buttons">
                    <input type="Submit" name="Submit" value="Registrarse" class="Registrarse"></input>
                        <a href="http://localhost/Pagina_Prueba/index/Prueba_index/login_register/login/login3.php" class="button">Regresar</a>
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