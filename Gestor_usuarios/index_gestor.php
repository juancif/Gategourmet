<?php
include_once("config_gestor.php");
$result = $dbConn->query("SELECT * FROM usuarios ORDER BY documento ASC");
?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestor de usuarios</title>
<link rel="stylesheet" href="style_gestor.css">
</head>
<body>
    <div class="cuadro_logo">
        <img src="../Imagenes/logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </div>

<div>
<table class="tabla_principal">
<tr class="tabla_secundaria">
<td>Nombre de usuario</td>
<td>Contraseña</td>
<td>Correo Electronico</td>
<td>Nombres</td>
<td>Documento</td>
<td>Area</td>
<td>Cargo</td>
<td>Acción</td>
</tr>
<?php
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
echo "<tr>";
echo "<td>".$row['nombre_usuario']."</td>";
echo "<td>".$row['contrasena']."</td>";
echo "<td>".$row['correo']."</td>";
echo "<td>".$row['nombres_apellidos']."</td>";
echo "<td>".$row['documento']."</td>";
echo "<td>".$row['area']."</td>";
echo "<td>".$row['cargo']."</td>";
echo "<td><a href=\"edit_gestor.php?documento=$row[documento]\">Editar</a> | <a href=\"delete_gestor.php?documento=$row[documento]\"
onClick=\"return confirm('Esta seguro de elimar el registro?')\">Eliminar</a></td>";
}
?>
</table>
</div>
<a href="http://localhost/GateGourmet/Gestor_usuarios/add_gestor.php" class="boton_adicionar">Adicionar usuario</a><br/><br/>
<a href="http://localhost/Pagina_Prueba/index/Prueba_index/login_register/login/login_admin.php" class="boton_volver">Volver</a>
<footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
    </footer>
</body>
</html>