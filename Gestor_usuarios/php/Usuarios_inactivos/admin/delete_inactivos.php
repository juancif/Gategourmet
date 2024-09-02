<?php
include("config_gestor.php");
$nombre_usuario = $_GET['nombre_usuario'];
$sql = "DELETE FROM administradores WHERE nombre_usuario=:nombre_usuario"; // Aquí podrías necesitar cambiar ':nombre_usuario' a ':id' dependiendo del nombre real del parámetro en tu base de datos
$query = $dbConn->prepare($sql);
$query->execute(array(':nombre_usuario' => $nombre_usuario)); // Cambié ':nombre_usuario' a ':id' si ese es el nombre real del parámetro en tu base de datos
header("Location: index_gestor_admin.php"); // Se corrigió la dirección de redirección
?>

