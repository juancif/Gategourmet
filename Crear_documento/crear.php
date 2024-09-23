<?php
// Conexión a la base de datos MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gategourmet";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Inicializa el mensaje de error
$error_message = "";

// Si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $proceso = $_POST['proceso'];
    $codigo = $_POST['codigo'];
    $titulo_documento = $_POST['titulo_documento'];
    $tipo = $_POST['tipo'];
    $fecha_aprobacion = $_POST['fecha_aprobacion'];
    $areas = $_POST['areas'];

    // Verificación para evitar documentos duplicados (por proceso, código o título)
    $sql_check = "SELECT * FROM listado_maestro WHERE proceso = ? OR codigo = ? OR titulo_documento = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("sss", $proceso, $codigo, $titulo_documento);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Error: Ya existe un documento con este proceso, código o título.";
    } else {
        // Estado "Vigente" y fecha actual como fecha de creación
        $estado = 'Vigente';
        $fecha_vigencia = date('Y-m-d'); // Fecha de vigencia es la fecha actual

        // Insertar el nuevo documento en la base de datos
        $sql_insert = "INSERT INTO listado_maestro (proceso, codigo, titulo_documento, tipo, estado, fecha_aprobacion, fecha_de_vigencia, areas)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssssssss", $proceso, $codigo, $titulo_documento, $tipo, $estado, $fecha_aprobacion, $fecha_vigencia, $areas);

        if ($stmt_insert->execute()) {
            echo "<script>alert('Documento creado exitosamente.');</script>";
        } else {
            $error_message = "Error: " . $stmt_insert->error;
        }
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Documentos</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error-message {
            background-color: #000000;
            color: white;
            padding: 15px;
            border-radius: 5px;
            font-weight: 500;
            text-align: center;
            position: absolute;
            top:10vh;
            right: 30vw;
            z-index: 1000;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            animation: fadeOut 5s forwards;
            display: none; /* Ocultado por defecto */
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            80% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                display: none;
            }
        }
    </style>
</head>
<body>

    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
        <link rel="stylesheet" href="crear.css">
    </header>
    <li class="nav__item__user">
        <a href="http://localhost/GateGourmet/Index/index_admin.html" class="cerrar__sesion__link">
            <img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario">
            <div class="cerrar__sesion">Volver al inicio</div>
        </a>
    </li>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Documentos</h2>
                <form method="post" action="">
                    <!-- Campos del formulario -->
                    <div class="input-group">
                        <label for="proceso">Proceso</label>
                        <input type="text" id="proceso" name="proceso" required>
                    </div>
                    <div class="input-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" required>
                    </div>
                    <div class="input-group">
                        <label for="titulo_documento">Título del Documento</label>
                        <input type="text" id="titulo_documento" name="titulo_documento" required>
                    </div>
                    <div class="input-group">
                        <label for="tipo">Tipo</label>
                        <input type="text" id="tipo" name="tipo" required>
                    </div>
                    <div class="input-group">
                        <label for="fecha_aprobacion">Fecha de Aprobación</label>
                        <input type="date" id="fecha_aprobacion" name="fecha_aprobacion" required>
                    </div>
                    <div class="input-group">
                        <label for="areas">Áreas</label>
                        <select name="areas" id="areas" required>
                            <option value="">Seleccione una opción</option>
                            <option value="Gestion_corporativa">Gestión corporativa</option>
                            <option value="Compliance">Compliance</option>
                            <option value="Supply_chain">Supply Chain</option>
                            <option value="Culinary_Excellence">Culinary Excellence</option>
                            <option value="Supervisor">Service Delivery</option>
                            <option value="Assembly">Assembly</option>
                            <option value="Servicios_institucionales">Servicios institucionales</option>
                            <option value="Financiera">Financiera</option>
                            <option value="Costos">Costos</option>
                            <option value="Comunicaciones">Comunicaciones</option>
                            <option value="Tecnologia_de_la_información">Tecnología de la información</option>
                            <option value="Talento_humano">Talento Humano</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Servicio_al_cliente">Servicio al cliente</option>
                            <option value="Security">Security</option>
                        </select>
                    </div>
                    <div class="buttons">
                        <input type="submit" name="Submit" value="Agregar" class="Registrarse">
                        <a href="http://localhost/GateGourmet/Index/index_admin.php" class="regresar">Regresar</a>
                    </div>
                </form>
            </div>
        </div>
        <div id="error-message" class="error-message"><?php echo $error_message; ?></div>
    </main>
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
        <script src="/script_prueba/script.js"></script>
    </footer>

    <script>
        // Mostrar el mensaje de error si hay uno
        <?php if ($error_message): ?>
            document.getElementById('error-message').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>
