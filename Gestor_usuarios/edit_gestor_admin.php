<?php
include_once("config_gestor.php");

if(isset($_POST['update']))
{
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];
    $correo = $_POST['correo'];
    $nombres_apellidos = $_POST['nombres_apellidos'];;
    $documento = $_POST['documento'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];

    if(empty($nombre_usuario) || empty($contrasena) || empty($correo) || empty($nombres_apellidos) || 
    empty($documento) || empty($area) || empty($cargo)) {
        if(empty($nombre_usuario)) {
            echo "<font color='red'>Campo: nombre_usuario está vacío.</font><br/>";
        }
        if(empty($contrasena)) {
            echo "<font color='red'>Campo: contrasena está vacío.</font><br/>";
        }
        if(empty($correo)) {
            echo "<font color='red'>Campo: correo está vacío.</font><br/>";
        }
        if(empty($nombres_apellidos)) {
            echo "<font color='red'>Campo: nombres_apellidos está vacío.</font><br/>";
        }
        if(empty($documento)) {
            echo "<font color='red'>Campo: documento está vacío.</font><br/>";
        }
        if(empty($area)) {
            echo "<font color='red'>Campo: area está vacío.</font><br/>";
        }
        if(empty($cargo)) {
            echo "<font color='red'>Campo: cargo está vacío.</font><br/>";
        }
    } else {
        $sql = "UPDATE administradores SET nombre_usuario=:nombre_usuario, contrasena=:contrasena, correo=:correo, nombres_apellidos=:nombres_apellidos, 
       documento=:documento, area=:area, cargo=:cargo 
        WHERE documento=:documento";
        $query = $dbConn->prepare($sql);
        $query->bindParam(':nombre_usuario', $nombre_usuario);
        $query->bindParam(':contrasena', $contrasena);
        $query->bindParam(':correo', $correo);
        $query->bindParam(':nombres_apellidos', $nombres_apellidos);
        $query->bindParam(':documento', $documento);
        $query->bindParam(':area', $area);
        $query->bindParam(':cargo', $cargo);
        $query->execute();
        header("Location: index_gestor_admin.php");
    }
}
?>

<?php
$documento = $_GET['documento'];
$sql = "SELECT * FROM administradores WHERE documento=:documento";
$query = $dbConn->prepare($sql);
$query->execute(array(':documento' => $documento));
$row = $query->fetch(PDO::FETCH_ASSOC);
$nombre_usuario = $row['nombre_usuario'];
$contrasena = $row['contrasena'];
$correo = $row['correo'];
$nombres_apellidos = $row['nombres_apellidos'];
$documento = $row['documento'];
$area = $row['area'];
$cargo = $row['cargo'];
?>
<html>
<head>
    <title>Editar Datos</title>
    <link rel="stylesheet" href="style_edit_gestor.css">
</head>
<body>
<a href="index_gestor_admin.php">Inicio</a>
<br/><br/>
<form name="form1" method="post" action="edit_gestor_admin.php">
    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Usuarios</h2>
                <form method="post" action="index_gestor_admin.php">
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
                        <label for="first_name">nombres_apellidos</label>
                        <input type="text" id="nombres_apellidos" name="nombres_apellidos" required value="<?php echo $nombres_apellidos;?>">
                    </div>
                    <div class="input-group">
                        <label for="document">Documento</label>
                        <input type="text" id="documento" name="documento" required value="<?php echo $documento;?>">
                    </div>
                    <div class="input-group">
                        <label for="nombres_apellidos">Área Pertenece</label>
                        <input type="text" id="area" name="area" value="<?php echo $area;?>">
                    </div>
                    <div class="input-group">
                    <select name="cargo" id="cargo">
                        <label for="cargo">Cargo</label>
                            <option value="">Seleccione una opción</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Empleado">Empleado</option>
                        </select> 
                    </div>
                    <div class="buttons">
                    <input type="Submit" name="update" value="Editar" class="Registrarse"></input>
                        <a href="http://localhost/GateGourmet/Gestor_usuarios/index_gestor_admin.php" class="button">Volver</a>
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
