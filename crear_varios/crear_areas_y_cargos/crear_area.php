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
    $area = $_POST['area'];

    // Validamos que los campos no estén vacíos
    if (!empty($nombre_usuario) && !empty($area)) {
        // Preparamos la consulta SQL para actualizar el campo 'area'
        $sql_area = "UPDATE administradores SET area = ? WHERE nombre_usuario = ?";
        $stmt_area = $conexion->prepare($sql_area);

        if ($stmt_area) {
            // Vinculamos los parámetros
            $stmt_area->bind_param("ss", $area, $nombre_usuario);

            // Ejecutamos la consulta y mostramos el resultado
            if ($stmt_area->execute()) {
                echo "<p>Área actualizada con éxito.</p>";
            } else {
                echo "<p>Error al actualizar el área: " . $stmt_area->error . "</p>";
            }

            // Cerramos la consulta
            $stmt_area->close();
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
    <title>Añadir Área</title>
    <link rel="stylesheet" href="crear_area.css"> <!-- Estilo CSS -->
</head>
<body>
    <div class="main-content">
        <div class="register-container">
            <h2>Añadir Área</h2>
            <!-- Formulario para añadir área -->
            <form id="addAreaForm" action="" method="POST">
                <div class="input-group">
                    <label for="nombre_usuario">Nombre de Usuario del Administrador:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                </div>
                <div class="input-group">
                    <label for="area">Área:</label>
                    <input type="text" id="area" name="area" maxlength="50" required>
                </div>
                <div class="buttons">
                    <input type="submit" value="Guardar Área">
                    <a href="index.html" class="regresar">Regresar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
