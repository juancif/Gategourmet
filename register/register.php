
<?php
include_once("config.php");
if(isset($_POST['Submit'])) {
$nombre_completo = $_POST['nombre_completo'];
$area = $_POST['area'];
$correo = $_POST['correo'];;
$contrasena = $_POST['contrasena'];
if( empty($nombre_completo) || empty($area) || empty($correo) ||  empty($contrasena)) {

if(empty($nombre_completo)) {
echo "<font color='red'>Campo: nombre_completo esta vacio.</font><br/>";
}
if(empty($area)) {
echo "<font color='red'>Campo: Areá esta vacio.</font><br/>";
}
if(empty($correo)) {
echo "<font color='red'>Campo: correo esta vacio.</font><br/>";
}
if(empty($contrasena)) {
    echo "<font color='red'>Campo: contrasena esta vacio.</font><br/>";
    }
echo "<br/><a href='javascript:self.history.back();'>Volver</a>";
} else {
$sql = "INSERT INTO users (nombre_completo,area, correo, contrasena ) VALUES(:nombre_completo,:area, :correo, :contrasena)";
$query = $dbConn->prepare($sql);

$query->bindparam(':nombre_completo', $nombre_completo);
$query->bindparam(':area', $area);
$query->bindparam(':correo', $correo);
$query->bindparam(':contrasena', $contrasena);
$query->execute();
echo "<font color='green'>Registro Agregado Correctamente.";
echo "Cantidad de Registros Agregados: ".$query->rowCount()."<br>";
echo "<br/><a href='login.php'>Ver Todos los Registros</a>";
}
}
?>

<!DOCTYPE html>
<html lang="e">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_register1.css">
    <title>Registro</title>
</head>
<body>
<form action="register.php" method="post" name="form1">
    <div class="container_logo"></div>
      <div class="container_principal">
        <form>
          <img src="logo_oficial_color.png" class="logo">
            <center><h2>REGISTRO</h2></center>
              <input type="text" id="nombre_completo" name="nombre_completo" required placeholder="Nombre Completo" class="campo_nombre_completo">
            <br>
              <input type="text" id="area" name="area" required placeholder="Areá" class="campo_area">
            <br>
            <br>
              <input type="text" id="correo" name="correo" required placeholder="correo" class="campo_correo">
            <br>  
              <input type="password" id="contrasena" name="contrasena" required placeholder="Contraseña" class="campo_contrasena">
            <br>
            <br>
              <img src="https://th.bing.com/th/id/OIP.XddA3we5txwZAP4fAJtYRQHaHa?w=201&h=201&c=7&r=0&o=5&pid=1.7" class="qr">
            <br>
            <br>
              <input type="Submit" name="Submit" value="Registrar" class="link_registro"></input>
            </form>
              <script src="register.js"></script>
            <div><a href="http://localhost/Pagina_Prueba/index/Prueba_index/login_register/login/login.php" class="link_login">Volver al inicio</a></div>
        </div>
      </tr>
    </form>
</body>
</html>
