<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'gategourmet');

// Comprobamos si hay un error en la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Si se recibe una solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtenemos los valores del formulario
    $nombre_usuario = $_POST['nombre_usuario'];
    $cargo = $_POST['cargo'];

    // Validamos que los campos no estén vacíos
    if (!empty($nombre_usuario) && !empty($cargo)) {
        // Preparamos la consulta SQL para actualizar el campo 'cargo'
        $sql_cargo = "UPDATE administradores SET cargo = ? WHERE nombre_usuario = ?";
        $stmt_cargo = $conexion->prepare($sql_cargo);

        if ($stmt_cargo) {
            // Vinculamos los parámetros
            $stmt_cargo->bind_param("ss", $cargo, $nombre_usuario);

            // Ejecutamos la consulta y mostramos el resultado
            if ($stmt_cargo->execute()) {
                echo "<p>Cargo actualizado con éxito.</p>";
            } else {
                echo "<p>Error al actualizar el cargo: " . $stmt_cargo->error . "</p>";
            }
            // Cerramos la consulta
            $stmt_cargo->close();
        } else {
            echo "<p>Error al preparar la consulta: " . $conexion->error . "</p>";
        }
    } else {
        echo "<p>Debes rellenar todos los campos.</p>";
    }
}

// Cerramos la conexión a la base de datos
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Cargo</title>
    <link rel="stylesheet" href="crear_cargo.css"> <!-- Estilo CSS -->
</head>
<body>
    <div class="main-content">
        <div class="register-container">
            <h2>Añadir Cargo</h2>
            <!-- Formulario para añadir cargo -->
            <form id="addCargoForm" action="" method="POST">
                <div class="input-group">
                    <label for="nombre_usuario">Nombre de Usuario del Administrador:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                </div>
                <div class="input-group">
                    <label for="cargo">Cargo:</label>
                    <input type="text" id="cargo" name="cargo" maxlength="50" required>
                </div>
                <div class="buttons">
                    <input type="submit" value="Guardar Cargo">
                    <a href="index.html" class="regresar">Regresar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
