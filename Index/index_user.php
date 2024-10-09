<?php
require '../vendor/autoload.php';
session_start();
$usuario_actual = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '';
$area = isset($_SESSION['area']) ? $_SESSION['area'] : '';
$cargo = isset($_SESSION['cargo']) ? $_SESSION['cargo'] : '';
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';

// Conexión a la base de datos
require 'config.php';

// Función para obtener el usuario aprobador del área del usuario actual
function obtenerAprobador($area) {
    global $conn;
    $query = "SELECT nombre_usuario FROM usuarios WHERE area = ? AND rol = 'aprobador' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $area);
    $stmt->execute();
    $stmt->bind_result($aprobador);
    $stmt->fetch();
    $stmt->close();
    return $aprobador;
}

// Función para obtener el administrador general
function obtenerAdministrador() {
    global $conn;
    $query = "SELECT nombre_usuario FROM usuarios WHERE rol = 'administrador' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stmt->bind_result($administrador);
    $stmt->fetch();
    $stmt->close();
    return $administrador;
}

// Configuración del cliente de Google
$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Gmail::GMAIL_READONLY);
$client->setRedirectUri('http://localhost/GateGourmet/Index/index_user.php');
$client->setAccessType('offline');
$client->setPrompt('consent');

// Manejar el código de autorización
if (isset($_GET['code'])) {
    try {
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $accessToken;
        header('Location: index_user.php');
        exit();
    } catch (Exception $e) {
        echo 'Error fetching access token: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        exit();
    }
}

// Verificar si el token de acceso está disponible
if (!isset($_SESSION['access_token']) || $_SESSION['access_token'] === null) {
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit();
}

// Configurar el servicio de Gmail con el token de acceso
$client->setAccessToken($_SESSION['access_token']);
$service = new Google_Service_Gmail($client);

// Recuperar correos electrónicos
try {
    $response = $service->users_messages->listUsersMessages('me', ['maxResults' => 10]);
    $messages = $response->getMessages();
} catch (Exception $e) {
    echo 'Error retrieving emails: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit();
}

// Obtener detalles de los correos electrónicos
$emailData = [];

// Función para obtener el cuerpo del mensaje
function getBody($message) {
    global $service;
    $message = $service->users_messages->get('me', $message->getId());
    $payload = $message->getPayload();
    $parts = $payload->getParts();

    $body = '';

    // Decodificar el cuerpo del mensaje según su tipo MIME
    if ($payload->getMimeType() == 'text/plain') {
        $body = base64url_decode($payload->getBody()->getData());
    } elseif ($payload->getMimeType() == 'text/html') {
        $body = base64url_decode($payload->getBody()->getData());
    } elseif ($parts) {
        foreach ($parts as $part) {
            if ($part->getMimeType() == 'text/html') {
                $body .= base64url_decode($part->getBody()->getData());
            }
        }
    }

    return $body;
}

// Función para decodificar base64url
function base64url_decode($data) {
    $data = str_replace(['-', '_'], ['+', '/'], $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}

// Función para enviar correo de alarmas a revisiones
function enviarCorreoRevisiones($correo_id, $usuario_origen, $usuario_destino) {
    global $conn;
    $query = "INSERT INTO correos_revisiones (correo_id, usuario_origen, usuario_destino, estado) VALUES (?, ?, ?, 'enviado')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iss', $correo_id, $usuario_origen, $usuario_destino);
    $stmt->execute();
    $stmt->close();
}

// Procesar los correos y asignar dinámicamente
foreach ($messages as $message) {
    $messageDetail = $service->users_messages->get('me', $message->getId());
    $headers = $messageDetail->getPayload()->getHeaders();
    $emailDetails = [];

    foreach ($headers as $header) {
        switch ($header->getName()) {
            case 'Subject':
                $emailDetails['subject'] = htmlspecialchars($header->getValue(), ENT_QUOTES, 'UTF-8');
                break;
            case 'From':
                $emailDetails['from'] = htmlspecialchars($header->getValue(), ENT_QUOTES, 'UTF-8');
                break;
            case 'To':
                $emailDetails['to'] = htmlspecialchars($header->getValue(), ENT_QUOTES, 'UTF-8');
                break;
            case 'Date':
                $emailDetails['date'] = htmlspecialchars(date('d M Y H:i:s', strtotime($header->getValue())), ENT_QUOTES, 'UTF-8');
                break;
        }
    }

    $emailDetails['body'] = htmlspecialchars(getBody($messageDetail), ENT_QUOTES, 'UTF-8');
    $emailData[] = $emailDetails;

    // Asignar correo según rol y área
    if ($rol === 'digitador' && $area === 'costos') {
        $aprobador = obtenerAprobador($area);
        if ($aprobador) {
            enviarCorreoRevisiones($message->getId(), $usuario_actual, $aprobador);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GateGourmet</title>
    <link rel="stylesheet" href="style_index_user.css">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <nav class="nav__principal">
        <ul class="nav__list">
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/listado_maestro/listado_maestro_user.php" class="nav__link"><img src="../imagenes/security.png" alt="Seguridad" class="imgs__menu">Listado maestro</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/Crear_documento/crear.php" class="nav__link"><img src="../imagenes/security.png" alt="Seguridad" class="imgs__menu">Crear documento</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/Indicadores/indicadores_user.php" class="nav__link"><img src="../imagenes/config.png" alt="Configuracióm" class="imgs__menu">Indicadores</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/procesos/procesos.php" class="nav__link"><img src="../Imagenes/macroprocesos2.png" alt="macroprocesos" class="imgs__menu">Procesos</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/Index/generar_pdf.php" class="nav__link">
                    <img src="../Imagenes/macroprocesos2.png" alt="macroprocesos" class="imgs__menu">Reportes
                </a>
            </li>

            <li class="nav__item__user">
                <a href="http://localhost/GateGourmet/Movimientos/logout.php" class="cerrar__sesion__link"><img src="../Imagenes/image.png" alt="Usuario" class="img__usuario"><div class="cerrar__sesion">Cerrar sesión</div></a>
            </li>
        </ul>
    </nav>
    <button id="alarmas">Mostrar notificaciones</button>

<!-- Menú desplegable -->
<div id="abirMenu" class="desplegar">
    <span id="cerrarMenu">X</span>

    <!-- Contenedor principal -->
    <div class="container_not">
        <h1>Correos Electrónicos</h1>

<!-- Sección Alarmas -->
<div class="opcion" id="opcion-alarmas">
    <h2 class="nombre-opcion" onclick="toggleContenido('contenido-alarmas')">Alarmas
        <span class="contador" id="contador-alarmas"></span>
    </h2>
    <div class="contenido alarmas" id="contenido-alarmas">
        <?php if (empty($emailData)) { ?>
            <p>No hay correos electrónicos disponibles.</p>
        <?php } else { ?>
            <?php foreach ($emailData as $email) { 
    if (isset($email['estado']) && $email['estado'] == 'alarmas') { ?>
        <div class="email-item" data-id-correo="<?php echo $email['id']; ?>">
            <h2>Asunto: <?php echo $email['subject']; ?></h2><br><br>
            <p class="date"><strong>Fecha:</strong> <?php echo $email['date']; ?></p><br>
            <div class="body"><?php echo $email['body']; ?></div>
            <div class="email-actions">
                <button class="mover-boton" onclick="moverCorreo(this, 'revisiones', 'contador-revisiones', 'contador-alarmas')">Enviar a Revisiones</button>
                <button class="ignorar-boton" onclick="ignorarCorreo(this)">Ignorar</button>
            </div>
        </div>
<?php } } ?>

        <?php } ?>
    </div>
</div>

<!-- Sección Revisiones -->
<div class="opcion" id="opcion-revisiones">
    <h2 class="nombre-opcion" onclick="toggleContenido('contenido-revisiones')">Revisiones
        <span class="contador" id="contador-revisiones"></span>
    </h2>
    <div class="contenido revisiones" id="contenido-revisiones">
        <?php foreach ($emailData as $email) { if ($email['estado'] == 'revisiones') { ?>
            <div class="email-item" data-id-correo="<?php echo $email['id']; ?>">
                <h2>Asunto: <?php echo $email['subject']; ?></h2><br><br>
                <p class="date"><strong>Fecha:</strong> <?php echo $email['date']; ?></p><br>
                <div class="body"><?php echo $email['body']; ?></div>
                <div class="email-actions">
                    <button class="mover-boton" onclick="moverCorreo(this, 'aprobaciones', 'contador-aprobaciones', 'contador-revisiones')">Enviar a Aprobaciones</button>
                    <button class="mover-boton" onclick="moverCorreo(this, 'alarmas', 'contador-alarmas', 'contador-revisiones')">Devolver a Alarmas</button>
                </div>
            </div>
        <?php } } ?>
    </div>
</div>

<!-- Sección Aprobaciones -->
<div class="opcion" id="opcion-aprobaciones">
    <h2 class="nombre-opcion" onclick="toggleContenido('contenido-aprobaciones')">Aprobaciones
        <span class="contador" id="contador-aprobaciones"></span>
    </h2>
    <div class="contenido aprobaciones" id="contenido-aprobaciones">
        <?php foreach ($emailData as $email) { if ($email['estado'] == 'aprobaciones') { ?>
            <div class="email-item" data-id-correo="<?php echo $email['id']; ?>">
                <h2>Asunto: <?php echo $email['subject']; ?></h2><br><br>
                <p class="date"><strong>Fecha:</strong> <?php echo $email['date']; ?></p><br>
                <div class="body"><?php echo $email['body']; ?></div>
                <div class="email-actions">
                    <button class="mover-boton" onclick="moverCorreo(this, 'revisiones', 'contador-revisiones', 'contador-aprobaciones')">Devolver a Revisiones</button>
                </div>
            </div>
        <?php } } ?>
    </div>
</div>
</div>
</div>
<script>
// Función para mover correos entre contenedores y actualizar el estado en tiempo real
function moverCorreo(button, nuevoEstado, contadorDestinoId, contadorOrigenId) {
    var emailItem = button.closest('.email-item');
    var idCorreo = emailItem.getAttribute('data-id-correo');

    // Llamada AJAX para actualizar el estado del correo en el servidor
    var formData = new FormData();
    formData.append('id_correo', idCorreo);
    formData.append('estado', nuevoEstado);

    fetch('guardar_cambio.php', {
        method: 'POST',
        body: formData
    }).then(response => response.text())
      .then(data => {
        // Mover el correo al nuevo contenedor
        var contenedorDestino = document.getElementById('contenido-' + nuevoEstado);
        contenedorDestino.appendChild(emailItem);

        // Actualizar los contadores del origen y destino
        actualizarContador(contadorOrigenId);
        actualizarContador(contadorDestinoId);

        // Actualizar los botones según el nuevo estado
        mostrarBotonesSegunEstado(emailItem, nuevoEstado);
    }).catch(error => {
        console.error('Error al mover el correo:', error);
    });
}

// Función para ignorar correos
function ignorarCorreo(button) {
    var emailItem = button.closest('.email-item');
    var idCorreo = emailItem.getAttribute('data-id-correo');

    // Llamada AJAX para marcar como ignorado
    var formData = new FormData();
    formData.append('id_correo', idCorreo);
    formData.append('estado', 'ignorado');

    fetch('guardar_cambio.php', {
        method: 'POST',
        body: formData
    }).then(response => response.text())
      .then(data => {
        // Eliminar el correo visualmente
        emailItem.remove();

        // Actualizar los contadores
        actualizarContador('contador-alarmas');
        actualizarContador('contador-revisiones');
        actualizarContador('contador-aprobaciones');
    }).catch(error => {
        console.error('Error al ignorar el correo:', error);
    });
}

// Función para actualizar el contador de correos en una sección
function actualizarContador(contadorId) {
    var contenedor = document.getElementById(contadorId.replace('contador-', 'contenido-'));
    var contador = document.getElementById(contadorId);
    var itemsVisibles = contenedor.querySelectorAll('.email-item').length;
    contador.textContent = itemsVisibles;
}

// Función para mostrar los botones según el estado actual del correo
function mostrarBotonesSegunEstado(emailItem, nuevoEstado) {
    var acciones = emailItem.querySelector('.email-actions');

    if (nuevoEstado === 'alarmas') {
        // En 'alarmas', mostrar "Enviar a Revisiones" e "Ignorar"
        acciones.innerHTML = `
            <button class="mover-boton" onclick="moverCorreo(this, 'revisiones', 'contador-revisiones', 'contador-alarmas')">Enviar a Revisiones</button>
            <button class="ignorar-boton" onclick="ignorarCorreo(this)">Ignorar</button>
        `;
    } else if (nuevoEstado === 'revisiones') {
        // En 'revisiones', mostrar "Enviar a Aprobaciones" y "Devolver a Alarmas"
        acciones.innerHTML = `
            <button class="mover-boton" onclick="moverCorreo(this, 'aprobaciones', 'contador-aprobaciones', 'contador-revisiones')">Enviar a Aprobaciones</button>
            <button class="mover-boton" onclick="moverCorreo(this, 'alarmas', 'contador-alarmas', 'contador-revisiones')">Devolver a Alarmas</button>
        `;
    } else if (nuevoEstado === 'aprobaciones') {
        // En 'aprobaciones', solo mostrar "Devolver a Revisiones"
        acciones.innerHTML = `
            <button class="mover-boton" onclick="moverCorreo(this, 'revisiones', 'contador-revisiones', 'contador-aprobaciones')">Devolver a Revisiones</button>
        `;
    }
}

// Inicializar los contadores al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    actualizarContador('contador-alarmas');
    actualizarContador('contador-revisiones');
    actualizarContador('contador-aprobaciones');
});

// Función para mostrar u ocultar el contenido de cada sección
function toggleContenido(contenidoId) {
    var contenido = document.getElementById(contenidoId);
    contenido.style.display = contenido.style.display === 'none' ? 'block' : 'none';
}
</script>


<div class="recuadroimagen">
        <img src="../Imagenes/Logo_oficial_B-N.png" class="logoindex">
        <img src="../Imagenes/logo__recuadro__gategourmet.png" alt="img4" class="triangulo">
    </div>
    <div class="column1">
        <div class="opciones opcion1"><a href="#" class="link1"><h3 class="h3__2">ABASTECIMIENTOS</h3></a></div>
        <div class="opciones opcion2"><a href="#" class="link1"><h3 class="h3__2">CI</h3></a></div>
        <div class="opciones opcion3"><a href="#" class="link1"><h3 class="h3__2">COMPLIANCE</h3></a></div>
        <div class="opciones opcion4"><a href="#" class="link1"><h3 class="h3__2">COMPRAS</h3></a></div>
        <div class="opciones opcion5"><a href="#" class="link1"><h3 class="h3__2">COSTOS</h3></a></div>
        <div class="opciones opcion6"><a href="#" class="link1"><h3 class="h3__2">CULINARY</h3></a></div>
        <div class="opciones opcion7"><a href="#" class="link1"><h3 class="h3__2">DESARROLLO</h3></a></div>
        <div class="opciones opcion8"><a href="#" class="link1"><h3 class="h3__2">FACILITY</h3></a></div>
        <div class="opciones opcion9"><a href="#" class="link1"><h3 class="h3__2">FINANCIERA</h3></a></div>
        <div class="opciones opcion10"><a href="#" class="link1"><h3 class="h3__2">IDS</h3></a></div>
        <div class="opciones opcion11"><a href="#" class="link1"><h3 class="h3__2">KEY ACCOUNT</h3></a></div>
        <div class="opciones opcion12"><a href="#" class="link1"><h3 class="h3__2">LAUNDRY</h3></a></div>
        <div class="opciones opcion13"><a href="#" class="link1"><h3 class="h3__2">MAKE & PACK</h3></a></div>
        <div class="opciones opcion14"><a href="#" class="link1"><h3 class="h3__2">PICK & PACK</h3></a></div>
        <div class="opciones opcion15"><a href="#" class="link1"><h3 class="h3__2">SALAS</h3></a></div>
        <div class="opciones opcion16"><a href="#" class="link1"><h3 class="h3__2">SEGURIDAD</h3></a></div>
        <div class="opciones opcion17"><a href="#" class="link1"><h3 class="h3__2">SERVICE DELIVERY</h3></a></div>
        <div class="opciones opcion18"><a href="#" class="link1"><h3 class="h3__2">SISTEMAS</h3></a></div>
        <div class="opciones opcion19"><a href="#" class="link1"><h3 class="h3__2">TALENTO HUMANO</h3></a></div>
        <div class="opciones opcion20"><a href="#" class="link1"><h3 class="h3__2">WASH & PACK</h3></a></div>
        <div class="opciones opcion21"><a href="#" class="link1"><h3 class="h3__2">OBSOLETOS</h3></a></div>
    </div>
    <script>
// Función para alternar el contenido y actualizar el contador
function toggleContenido(id) {
    const contenido = document.getElementById(id);
    const opcion = document.querySelector(`[onclick="toggleContenido('${id}')"]`);

    // Alternar entre mostrar/ocultar el contenido y agregar la clase show
    if (contenido.classList.contains('show')) {
        contenido.classList.remove('show');
    } else {
        contenido.classList.add('show');
    }
}

// Función para contar los correos dentro de cada sección
function actualizarContadores() {
    const secciones = ['contenido-aprobaciones', 'contenido-revisiones', 'contenido-alarmass'];
    
    secciones.forEach(seccionId => {
        const seccion = document.getElementById(seccionId);
        const contador = seccion.querySelectorAll('.email-item').length;
        const nombreOpcion = document.querySelector(`[onclick="toggleContenido('${seccionId}')"]`);
        
        // Mostrar el total de correos en el encabezado
        if (nombreOpcion) {
            const contadorElement = nombreOpcion.querySelector('.contador');
            contadorElement.textContent = contador;
        }
    });
}

// Ejecutamos la función de contar correos al cargar la página
document.addEventListener('DOMContentLoaded', actualizarContadores);

</script>
<script>
    function confirmAction(action, fileName) {
        let message = "";
        switch(action) {
            case 'aprobar':
                message = `¿Estás seguro de que quieres aprobar el documento: ${fileName}?`;
                break;
            case 'revisar':
                message = `¿Estás seguro de que quieres revisar el documento: ${fileName}?`;
                break;
            case 'no aprobar':
                message = `¿Estás seguro de que NO quieres aprobar el documento: ${fileName}?`;
                break;
        }

        if (confirm(message)) {
            // Si el usuario confirma, puedes ejecutar la lógica correspondiente para la acción
            alert(`Acción ${action} confirmada para: ${fileName}`);
            // Aquí puedes hacer una llamada AJAX o cualquier otro procesamiento
        } else {
            // Si el usuario cancela, muestra un mensaje de cancelación
            alert(`Acción ${action} cancelada para: ${fileName}`);
        }
    }
</script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const areaUsuario = "<?php echo $area; ?>"; // Aquí tomamos el área del usuario desde PHP

            console.log("Área del usuario:", areaUsuario); // Para verificar que está llegando correctamente

            const circulos = document.querySelectorAll(".circulo");

            circulos.forEach(circulo => {
                const h3Text = circulo.querySelector("h3").textContent.trim();

                console.log("Texto del h3:", h3Text); // Para verificar el texto de cada h3

                if (h3Text !== areaUsuario) {
                    // Deshabilitar la funcionalidad de click
                    const link = circulo.querySelector("a");
                    link.removeAttribute("href");
                    link.style.pointerEvents = "none";
                    link.style.opacity = "0.5"; // Visualmente deshabilitar
                }
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const areaUsuario = "<?php echo $area; ?>"; // Aquí tomamos el área del usuario desde PHP

            console.log("Área del usuario:", areaUsuario); // Para verificar que está llegando correctamente

            const circulos = document.querySelectorAll(".circulo");

            circulos.forEach(circulo => {
                const h3Text = circulo.querySelector("h3").textContent.trim();

                console.log("Texto del h3:", h3Text); // Para verificar el texto de cada h3

                if (h3Text !== areaUsuario) {
                    // Deshabilitar la funcionalidad de click
                    const link = circulo.querySelector("a");
                    link.removeAttribute("href");
                    link.style.pointerEvents = "none";
                    link.style.opacity = "0.5"; // Visualmente deshabilitar
                }
            });
        });
    </script>
    <script src="menu.js"></script>
    <script src="script_alarmas.js"></script>
</body>
</html>