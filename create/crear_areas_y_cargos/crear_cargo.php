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
    $documento = $_POST['documento'];
    $cargo = $_POST['cargo'];

    // Validamos que los campos no estén vacíos
    if (!empty($documento) && !empty($cargo)) {
        // Preparamos la consulta SQL para actualizar el campo 'cargo'
        $sql_cargo = "UPDATE administradores SET cargo = ? WHERE documento = ?";
        $stmt_cargo = $conexion->prepare($sql_cargo);
        $stmt_cargo->bind_param("si", $cargo, $documento);

        // Ejecutamos la consulta y mostramos el resultado
        if ($stmt_cargo->execute()) {
            echo "<p>Cargo actualizado con éxito.</p>";
        } else {
            echo "<p>Error al actualizar el cargo: " . $conexion->error . "</p>";
        }

        // Cerramos la consulta
        $stmt_cargo->close();
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
    <link rel="stylesheet" href="styles.css"> <!-- Estilo CSS -->
</head>
<body>
    <div class="main-content">
        <div class="register-container">
            <h2>Añadir Cargo</h2>
            <!-- Formulario para añadir cargo -->
            <form id="addCargoForm" action="" method="POST">
                <div class="input-group">
                    <label for="documento">Documento del Administrador:</label>
                    <input type="number" id="documento" name="documento" required>
                </div>
                <div class="input-group">
                    <label for="cargo">Cargo:</label>
                    <input type="text" id="cargo" name="cargo" maxlength="25" required>
                </div>
                <div class="buttons">
                    <input type="submit" value="Guardar Cargo">
                    <a href="index.html" class="regresar">Regresar</a>
                </div>
            </form>
        </div>
    </div>