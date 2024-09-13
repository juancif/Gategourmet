<?php
include_once("config_gestor.php");
session_start(); // Asegúrate de iniciar sesión para poder usar variables de sesión

if (!isset($_SESSION['nombre_usuario'])) {
    die("Usuario no autenticado.");
}

$usuario_logueado = $_SESSION['nombre_usuario']; // El usuario que está realizando la acción

if (isset($_GET['nombre_usuario'])) {
    $nombre_usuario = $_GET['nombre_usuario'];

    try {
        // Iniciar transacción
        $dbConn->beginTransaction();

        // Buscar el usuario en la tabla de administradores
        $sqlSelectAdmin = "SELECT * FROM administradores WHERE nombre_usuario = :nombre_usuario";
        $stmtSelectAdmin = $dbConn->prepare($sqlSelectAdmin);
        $stmtSelectAdmin->bindParam(':nombre_usuario', $nombre_usuario);
        $stmtSelectAdmin->execute();
        $user = $stmtSelectAdmin->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Si no se encuentra en administradores, buscar en usuarios
            $sqlSelectUser = "SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario";
            $stmtSelectUser = $dbConn->prepare($sqlSelectUser);
            $stmtSelectUser->bindParam(':nombre_usuario', $nombre_usuario);
            $stmtSelectUser->execute();
            $user = $stmtSelectUser->fetch(PDO::FETCH_ASSOC);
        }

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
            $stmtInsert->bindParam(':rol', $user['rol']);  // Asegúrate de que se está pasando el rol
            $stmtInsert->execute();

            // Eliminar de la tabla correspondiente
            if ($user['rol'] === 'Administrador') {
                $sqlDelete = "DELETE FROM administradores WHERE nombre_usuario = :nombre_usuario";
            } else {
                $sqlDelete = "DELETE FROM usuarios WHERE nombre_usuario = :nombre_usuario";
            }
            $stmtDelete = $dbConn->prepare($sqlDelete);
            $stmtDelete->bindParam(':nombre_usuario', $nombre_usuario);
            $stmtDelete->execute();

            // Registrar la acción en la tabla movimientos
            $accion = '';
            if ($user['rol'] === 'Administrador') {
                $accion = "Desactivación de administrador: $nombre_usuario";
            } elseif (in_array($user['rol'], ['Aprobador', 'Digitador', 'Observador'])) {
                $accion = "Desactivación de usuario con rol {$user['rol']}: $nombre_usuario";
            } else {
                $accion = "Desactivación de usuario con rol desconocido: $nombre_usuario";
            }
            
            $sql_movimiento = "INSERT INTO movimientos (nombre_usuario, rol, accion, fecha) VALUES (:nombre_usuario, :rol, :accion, NOW())";
            $stmt_movimiento = $dbConn->prepare($sql_movimiento);
            $stmt_movimiento->bindParam(':nombre_usuario', $usuario_logueado); // Nombre de usuario que realizó el cambio
            $stmt_movimiento->bindParam(':rol', $user['rol']);  // Insertar el rol en la tabla movimientos
            $stmt_movimiento->bindParam(':accion', $accion);
            $stmt_movimiento->execute();

            // Cometer transacción
            $dbConn->commit();

            // Redirigir o mostrar un mensaje de éxito
            header("Location: index_gestor_admin.php?msg=Usuario desactivado correctamente");
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
