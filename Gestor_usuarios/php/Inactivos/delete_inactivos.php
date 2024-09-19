<?php
include_once("config_inactivos.php");
session_start(); // Asegúrate de iniciar sesión para poder usar variables de sesión

// Verifica si el usuario está autenticado
if (!isset($_SESSION['nombre_usuario'])) {
    die("Usuario no autenticado.");
}

$usuario_logueado = $_SESSION['nombre_usuario']; // El usuario que está realizando la acción

if (isset($_GET['nombre_usuario'])) {
    $nombre_usuario = $_GET['nombre_usuario'];

    try {
        // Iniciar transacción
        $dbConn->beginTransaction();

        // Obtener datos del usuario a eliminar
        $sqlSelect = "SELECT * FROM inactivos WHERE nombre_usuario = :nombre_usuario";
        $stmtSelect = $dbConn->prepare($sqlSelect);
        $stmtSelect->bindParam(':nombre_usuario', $nombre_usuario);
        $stmtSelect->execute();
        $user = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Eliminar de la tabla inactivos
            $sqlDelete = "DELETE FROM inactivos WHERE nombre_usuario = :nombre_usuario";
            $stmtDelete = $dbConn->prepare($sqlDelete);
            $stmtDelete->bindParam(':nombre_usuario', $nombre_usuario);
            $stmtDelete->execute();

            $accion = '';
            if ($user['rol'] === 'Administrador') {
                $accion = "Eliminación de administrador: $nombre_usuario";
            } elseif (in_array($user['rol'], ['Aprobador', 'Digitador', 'Observador'])) {
                $accion = "Eliminación de usuario con rol {$user['rol']}: $nombre_usuario";
            } else {
                $accion = "Eliminación de usuario con rol desconocido: $nombre_usuario";
            }
            
            // Registrar la acción en la tabla movimientos con el usuario que realizó la eliminación
            $sqlMovimiento = "INSERT INTO movimientos (nombre_usuario, accion) VALUES (:usuario_logueado, :accion)";
            $stmtMovimiento = $dbConn->prepare($sqlMovimiento);
            $stmtMovimiento->bindParam(':usuario_logueado', $usuario_logueado); // Usuario que realizó la eliminación
            $stmtMovimiento->bindParam(':accion', $accion);
            $stmtMovimiento->execute();

            // Cometer transacción
            $dbConn->commit();

            // Redirigir o mostrar un mensaje de éxito
            header("Location: http://localhost/GateGourmet/Gestor_usuarios/php/Inactivos/index_inactivos.php?msg=Usuario eliminado correctamente");
            exit();
        } else {
            throw new Exception("Usuario no encontrado");
        }
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if ($dbConn->inTransaction()) {
            $dbConn->rollBack();
        }
        echo "<font color='red'>Error: " . $e->getMessage() . "</font><br/>";
    }
} else {
    echo "<font color='red'>No se ha especificado el nombre de usuario.</font><br/>";
}
?>
