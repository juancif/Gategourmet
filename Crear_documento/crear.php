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

// Variables para mensajes de error y éxito
$error_message = '';
$success_message = '';

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
        $error_message = "Error El documento que estás intentando crear ya existe con este código o título.";
    } else {
        // Estado "Vigente" y fecha de vigencia de un año a partir de hoy
        $estado = 'VIGENTE';
        $fecha_vigencia = date('Y-m-d'); // Fecha de vigencia es la fecha actual
        $fecha_caducidad = date('Y-m-d', strtotime('+1 year', strtotime($fecha_vigencia))); // Un año después de la creación

        // Insertar el nuevo documento en la base de datos
        $sql_insert = "INSERT INTO listado_maestro (proceso, codigo, titulo_documento, tipo, estado, fecha_aprobacion, fecha_de_vigencia, areas)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssssssss", $proceso, $codigo, $titulo_documento, $tipo, $estado, $fecha_aprobacion, $fecha_caducidad, $areas);

        if ($stmt_insert->execute()) {
            $success_message = "Documento creado exitosamente.";
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
    <link rel="stylesheet" href="crear.css">
    <style>
        .error-message {
            background-color: #000000;
            color: white;
            padding: 15px;
            border-radius: 5px;
            font-weight: 500;
            text-align: center;
            position: absolute;
            top: 10vh;
            right: 30vw;
            z-index: 1000;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            animation: fadeOut 5s forwards;
            display: none;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
    </style>
</head>
<body>

<header class="header">
    <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
</header>

<li class="nav__item__user">
    <a href="http://localhost/GateGourmet/Index/index_admin.php" class="cerrar__sesion__link">
        <img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario">
        <div class="cerrar__sesion">Volver al inicio</div>
    </a>
</li>

<main class="main-content">
    <div class="register-container">
        <div class="register-box">
            <h2>Registro de Documentos</h2>

            <!-- Mostrar mensajes de error o éxito -->
            <div class="message">
                <?php if (!empty($error_message)) { ?>
                    <div id="error-message" class="error-message"><?php echo $error_message; ?></div>
                <?php } ?>
                <?php if (!empty($success_message)) { ?>
                    <p class='success-message' style='color:green; font-weight:bold;'><?php echo $success_message; ?></p>
                <?php } ?>
            </div>

            <form id="documentForm" method="post" action="">
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
                    <select id="tipo" name="tipo" required>
                        <option value="">Seleccione un tipo</option>
                        <option value="M">M - Manual</option>
                        <option value="F">F - Formato</option>
                        <option value="I">I - Instructivo</option>
                        <option value="G">G - Programa</option>
                        <option value="L">L - Layout</option>
                        <option value="MCV">MCV - Mapa De Cadena Evolutiva</option>
                        <option value="P">P - Procedimiento</option>
                        <option value="S">S - Subprograma</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="fecha_aprobacion">Fecha de Aprobación</label>
                    <input type="date" id="fecha_aprobacion" name="fecha_aprobacion" required>
                </div>
                <div class="input-group">
    <label for="areas">Áreas</label>
    <select name="areas" id="areas" required>
        <option value="">Seleccione una opción</option>
        <option value="SERVICE_DELIVERY">SERVICE DELIVERY</option>
        <option value="KEY_ACCOUNT">KEY ACCOUNT</option>
        <option value="COMPLIANCE_RAMP_SQUAD">COMPLIANCE - RAMP SQUAD</option>
        <option value="PICK_PACK">PICK & PACK</option>
        <option value="ABASTECIMIENTOS">ABASTECIMIENTOS</option>
        <option value="ABASTECIMIENTOS_BODEGA_EXTERNA">ABASTECIMIENTOS - BODEGA EXTERNA</option>
        <option value="CUBERTERIA">CUBERTERIA</option>
        <option value="COMPLIANCE_SEGURIDAD_ALIMENTARIA">COMPLIANCE - SEGURIDAD ALIMENTARIA</option>
        <option value="COMPLIANCE_MEDIO_AMBIENTE">COMPLIANCE - MEDIO AMBIENTE</option>
        <option value="TALENTO_HUMANO_SST">TALENTO HUMANO - SST</option>
        <option value="TALENTO_HUMANO">TALENTO HUMANO</option>
        <option value="GERENCIA_RECEPCION">GERENCIA - RECEPCIÓN</option>
        <option value="MANTENIMIENTO_HIGIENE">MANTENIMIENTO - HIGIENE</option>
        <option value="AUDIFONOS">AUDIFONOS</option>
        <option value="CASINO">CASINO</option>
        <option value="ROPERIA">ROPERIA</option>
        <option value="CULINARY_CAP">CULINARY - CAP</option>
        <option value="FINANCIERA">FINANCIERA</option>
        <option value="COMPLIANCE_CASINOS">COMPLIANCE - CASINOS</option>
        <option value="SALAS_VIP">SALAS VIP</option>
        <option value="CULINARY_COCINA_CALIENTE">CULINARY - COCINA CALIENTE</option>
        <option value="CULINARY_COCINA_FRIA">CULINARY - COCINA FRÍA</option>
        <option value="TALENTO_HUMANO_ENTRENAMIENTO">TALENTO HUMANO - ENTRENAMIENTO</option>
        <option value="COMPRAS">COMPRAS</option>
        <option value="COSTOS">COSTOS</option>
        <option value="CULINARY_CAR">CULINARY - CAR</option>
        <option value="SECURITY">SECURITY</option>
        <option value="DESARROLLO_DE_PRODUCTOS">DESARROLLO DE PRODUCTOS</option>
        <option value="GERENCIA">GERENCIA</option>
        <option value="MEJORAMIENTO_CONTINUO">MEJORAMIENTO CONTINUO</option>
        <option value="SUPLLY_CHAIN_IDS">SUPLLY CHAIN - IDS</option>
        <option value="MEJORAMIENTO_CONTINUO_FST">MEJORAMIENTO CONTINUO - FST</option>
        <option value="MEJORAMIENTO_CONTINUO_MCV">MEJORAMIENTO CONTINUO - MCV</option>
        <option value="WASH_PACK">WASH & PACK</option>
        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
        <option value="SISTEMAS">SISTEMAS</option>
        <option value="LAUNDRY">LAUNDRY</option>
        <option value="MAKE_PACK">MAKE & PACK</option>
        <option value="CULINARY_CARNICERIA">CULINARY - CARNICERIA</option>
        <option value="CULINARY_FRUTAS_Y_VERDURAS">CULINARY - FRUTAS Y VERDURAS</option>
        <option value="PRODUCCION">PRODUCCION</option>
        <option value="CULINARY_PANADERIA_Y_PASTELERIA">CULINARY - PANADERIA Y PASTELERIA</option>
        <option value="PLANTA_EQUIPOS">PLANTA - EQUIPOS</option>
        <option value="COMPLIANCE_SAGRILAFT">COMPLIANCE - SAGRILAFT</option>
        <option value="COMPLIANCE_POLITICA_Y_ETICA_EMPRESARIAL">COMPLIANCE - POLÍTICA Y ÉTICA EMPRESARIAL</option>
    </select>
</div>


                <!-- Botones de acción -->
                <div class="buttons">
                    <input type="button" id="addDocumentBtn" value="Agregar" class="button">
            </form>

            <!-- Modal de confirmación -->
            <div id="confirmationModal" class="modal">
                <div class="modal-content">
                    <span class="close" id="closeModal">&times;</span>
                    <h2>¿Estás segur@ de crear este documento?</h2>
                    <div class="modal-buttons">
                        <button id="confirmCreate" class="modal-button">Crear</button>
                        <button id="cancelCreate" class="modal-button">Volver</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="footer">
    <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
</footer>

<script>
// Mostrar y ocultar mensaje de error automáticamente
document.addEventListener("DOMContentLoaded", function() {
    var errorMessage = document.getElementById('error-message');
    if (errorMessage) {
        errorMessage.style.display = 'block';
        setTimeout(function() {
            errorMessage.style.display = 'none';
        }, 5000); // Desaparece después de 5 segundos
    }
});

// Validación del formulario y modal
var modal = document.getElementById("confirmationModal");
var addDocumentBtn = document.getElementById("addDocumentBtn");
var confirmCreate = document.getElementById("confirmCreate");
var cancelCreate = document.getElementById("cancelCreate");
var closeModal = document.getElementById("closeModal");

// Mostrar el modal cuando el usuario hace click en "Agregar"
addDocumentBtn.addEventListener('click', function() {
    var valid = document.getElementById("documentForm").checkValidity();
    if (valid) {
        modal.style.display = "block";  // Solo muestra el modal si el formulario es válido
    } else {
        alert("Por favor, complete todos los campos.");
    }
});

// Confirmación de envío
confirmCreate.addEventListener('click', function() {
    document.getElementById("documentForm").submit(); // Enviar el formulario
    modal.style.display = "none"; // Cerrar el modal
});

// Cancelación
cancelCreate.addEventListener('click', function() {
    modal.style.display = "none"; // Cerrar el modal
});

// Cerrar el modal cuando se hace click en la "X"
closeModal.addEventListener('click', function() {
    modal.style.display = "none";
});

// Cerrar el modal si el usuario hace click fuera del modal
window.addEventListener('click', function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
});
</script>

</body>
</html>