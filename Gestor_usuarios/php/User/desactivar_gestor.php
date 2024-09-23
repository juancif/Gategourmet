<?php
include_once("config_gestor.php");
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

        // Obtener datos del usuario a desactivar
        $sqlSelect = "SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario";
        $stmtSelect = $dbConn->prepare($sqlSelect);
        $stmtSelect->bindParam(':nombre_usuario', $nombre_usuario);
        $stmtSelect->execute();
        $user = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Insertar en la tabla inactivos
            $sqlInsert = "INSERT INTO inactivos (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol, estado) 
                          VALUES (:correo, :nombres_apellidos, :nombre_usuario, :contrasena, :area, :cargo, :rol, 'Inactivo')";
            $stmtInsert = $dbConn->prepare($sqlInsert);
            $stmtInsert->bindParam(':correo', $user['correo']);
            $stmtInsert->bindParam(':nombres_apellidos', $user['nombres_apellidos']);
            $stmtInsert->bindParam(':nombre_usuario', $user['nombre_usuario']);
            $stmtInsert->bindParam(':contrasena', $user['contrasena']);
            $stmtInsert->bindParam(':area', $user['area']);
            $stmtInsert->bindParam(':cargo', $user['cargo']);
            $stmtInsert->bindParam(':rol', $user['rol']);
            $stmtInsert->execute();

            // Eliminar de la tabla usuarios
            $sqlDelete = "DELETE FROM usuarios WHERE nombre_usuario = :nombre_usuario";
            $stmtDelete = $dbConn->prepare($sqlDelete);
            $stmtDelete->bindParam(':nombre_usuario', $nombre_usuario);
            $stmtDelete->execute();

            // Determinar el tipo de acción a registrar en la tabla movimientos
            $accion = "Desactivación de usuario con rol {$user['rol']}: $nombre_usuario";

            // Registrar la acción en la tabla movimientos
            $sql_movimiento = "INSERT INTO movimientos (nombre_usuario, rol, accion, fecha) VALUES (:usuario_logueado, :rol, :accion, NOW())";
            $stmt_movimiento = $dbConn->prepare($sql_movimiento);
            $stmt_movimiento->bindParam(':usuario_logueado', $usuario_logueado); // El usuario que realizó el cambio
            $stmt_movimiento->bindParam(':rol', $user['rol']);  // Rol del usuario desactivado
            $stmt_movimiento->bindParam(':accion', $accion);
            $stmt_movimiento->execute();

            // Cometer transacción
            $dbConn->commit();

            // Redirigir o mostrar un mensaje de éxito
            header("Location: http://10.24.217.62/GateGourmet/Gestor_usuarios/php/user/index_gestor.php?msg=Usuario desactivado correctamente");
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
