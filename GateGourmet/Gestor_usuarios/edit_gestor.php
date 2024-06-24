<?php
include_once("config_gestor.php");

if(isset($_POST['update']))
{
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];
    $correo = $_POST['correo'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $tipo_documento = $_POST['tipo_documento'];
    $documento = $_POST['documento'];
    $area = $_POST['area'];
    $tipo_usuario = $_POST['tipo_usuario'];

    if(empty($nombre_usuario) || empty($contrasena) || empty($correo) || empty($nombres) || empty($apellidos) 
    || empty($tipo_documento) || empty($documento) || empty($area) || empty($tipo_usuario)) {
        if(empty($nombre_usuario)) {
            echo "<font color='red'>Campo: nombre_usuario está vacío.</font><br/>";
        }
        if(empty($contrasena)) {
            echo "<font color='red'>Campo: contrasena está vacío.</font><br/>";
        }
        if(empty($correo)) {
            echo "<font color='red'>Campo: correo está vacío.</font><br/>";
        }
        if(empty($nombres)) {
            echo "<font color='red'>Campo: nombres está vacío.</font><br/>";
        }
        if(empty($apellidos)) {
            echo "<font color='red'>Campo: apellidos está vacío.</font><br/>";
        }
        if(empty($tipo_documento)) {
            echo "<font color='red'>Campo: tipo_documento está vacío.</font><br/>";
        }
        if(empty($documento)) {
            echo "<font color='red'>Campo: documento está vacío.</font><br/>";
        }
        if(empty($area)) {
            echo "<font color='red'>Campo: area está vacío.</font><br/>";
        }
        if(empty($tipo_usuario)) {
            echo "<font color='red'>Campo: tipo_usuario está vacío.</font><br/>";
        }
    } else {
        $sql = "UPDATE usuarios SET nombre_usuario=:nombre_usuario, contrasena=:contrasena, correo=:correo, nombres=:nombres, 
        apellidos=:apellidos, tipo_documento=:tipo_documento, documento=:documento, area=:area, tipo_usuario=:tipo_usuario 
        WHERE documento=:documento";
        $query = $dbConn->prepare($sql);
        $query->bindParam(':nombre_usuario', $nombre_usuario);
        $query->bindParam(':contrasena', $contrasena);
        $query->bindParam(':correo', $correo);
        $query->bindParam(':nombres', $nombres);
        $query->bindParam(':apellidos', $apellidos);
        $query->bindParam(':tipo_documento', $tipo_documento);
        $query->bindParam(':documento', $documento);
        $query->bindParam(':area', $area);
        $query->bindParam(':tipo_usuario', $tipo_usuario);
        $query->execute();
        header("Location: index_gestor.php");
    }
}
?>

<?php
$documento = $_GET['documento'];
$sql = "SELECT * FROM usuarios WHERE documento=:documento";
$query = $dbConn->prepare($sql);
$query->execute(array(':documento' => $documento));
$row = $query->fetch(PDO::FETCH_ASSOC);
$nombre_usuario = $row['nombre_usuario'];
$contrasena = $row['contrasena'];
$correo = $row['correo'];
$nombres = $row['nombres'];
$apellidos = $row['apellidos'];
$tipo_documento = $row['tipo_documento'];
$documento = $row['documento'];
$area = $row['area'];
$tipo_usuario = $row['tipo_usuario'];
?>

<html>
<head>
    <title>Editar Datos</title>
    <link rel="stylesheet" href="style_add_gestor.css">
</head>
<body>
<a href="index_gestor.php">Inicio</a>
<br/><br/>
<form name="form1" method="post" action="edit_gestor.php">
    <header class="header">
        <img src="../login_register/register/logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Usuarios</h2>
                <form method="post" action="index_gestor.php">
                    <div class="input-group">
                        <label for="nombre_usuario">Nombre de Usuario</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" required value="<?php echo $nombre_usuario;?>">
                    </div>
                    <div class="input-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="contrasena" id="contrasena" name="contrasena" required value="<?php echo $contrasena;?>">
                    </div>
                    <div class="input-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" required value="<?php echo $correo;?>">
                    </div>
                    <div class="input-group">
                        <label for="first_name">Nombres</label>
                        <input type="text" id="nombres" name="nombres" required value="<?php echo $nombres;?>">
                    </div>
                    <div class="input-group">
                        <label for="last_name">Apellidos</label>
                        <input type="text" id="apellidos" name="apellidos" required value="<?php echo $apellidos;?>">
                    </div>
                    <div class="input-group">
                        <label for="document_type">Tipo de Documento</label>
                        <select id="tipo_documento" name="tipo_documento" required value="<?php echo $tipo_documento;?>"> 
                            <option value="C.C">Cédula de Ciudadanía</option>
                            <option value="C.E">Cédula de Extranjería</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="document">Documento</label>
                        <input type="text" id="documento" name="documento" required value="<?php echo $documento;?>">
                    </div>
                    <div class="input-group">
                        <label for="nombres">Área Pertenece</label>
                        <input type="text" id="area" name="area" value="<?php echo $area;?>">
                    </div>
                    <div class="input-group">
                        <label for="user_type">Tipo de Usuario</label>
                        <select id="tipo_usuario" name="tipo_usuario" required value="<?php echo $tipo_usuario?>">
                            <option value="Administrativo">Administrativo</option>
                            <option value="Operativo">Operativo</option>
                            <option value="Jefe de area">Jefe De Área</option>
                        </select>
                    </div>
                    <div class="buttons">
                    <input type="Submit" name="update" value="Editar" class="Registrarse"></input>
                        <a href="http://localhost/Pagina_Prueba/index/Prueba_index/Gestor_usuarios/index_gestor.php" class="button">Volver</a>
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



