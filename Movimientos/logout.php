<?php
session_start();
include_once("config_mov.php");

// Verificar si hay una sesión activa
if (isset($_SESSION['nombre_usuario'])) {
    $nombre_usuario = $_SESSION['nombre_usuario'];

    // Inicializar variable para el rol
    $rol = null;

    // Verificar el rol en la tabla de usuarios
    $sql_check_user = "SELECT rol FROM usuarios WHERE nombre_usuario = :nombre_usuario";
    $query_check_user = $dbConn->prepare($sql_check_user);
    $query_check_user->execute([':nombre_usuario' => $nombre_usuario]);
    $row_user = $query_check_user->fetch(PDO::FETCH_ASSOC);

    if ($row_user) {
        $rol = $row_user['rol'];
    } else {
        // Si no se encuentra en usuarios, verificar en la tabla de administradores
        $sql_check_admin = "SELECT rol FROM administradores WHERE nombre_usuario = :nombre_usuario";
        $query_check_admin = $dbConn->prepare($sql_check_admin);
        $query_check_admin->execute([':nombre_usuario' => $nombre_usuario]);
        $row_admin = $query_check_admin->fetch(PDO::FETCH_ASSOC);
        
        if ($row_admin) {
            $rol = $row_admin['rol'];
        }
    }

    if ($rol) {
        // Registrar el cierre de sesión en la tabla de movimientos
        $accion = "Cierre de sesión: Usuario $nombre_usuario con rol $rol";
        $sql_movimiento = "INSERT INTO movimientos (nombre_usuario, accion) VALUES (:nombre_usuario, :accion)";
        $stmt_movimiento = $dbConn->prepare($sql_movimiento);
        $stmt_movimiento->bindParam(':nombre_usuario', $nombre_usuario);
        $stmt_movimiento->bindParam(':accion', $accion);
        $stmt_movimiento->execute();
    }

    // Destruir la sesión
    session_destroy();
}

// Redirigir al usuario a la página de inicio de sesión
header("Location: http://localhost/GateGourmet/login/login3.php");
exit();
?>
