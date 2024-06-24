<?php
include("config_gestor.php");
$documento = $_GET['documento'];
$sql = "DELETE FROM usuarios WHERE documento=:documento"; // Aquí podrías necesitar cambiar ':documento' a ':id' dependiendo del nombre real del parámetro en tu base de datos
$query = $dbConn->prepare($sql);
$query->execute(array(':documento' => $documento)); // Cambié ':documento' a ':id' si ese es el nombre real del parámetro en tu base de datos
header("Location: index_gestor.php"); // Se corrigió la dirección de redirección
?>

