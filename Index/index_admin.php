<?php
require '../vendor/autoload.php';
session_start();

// Configuración del cliente de Google
$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Gmail::GMAIL_READONLY);
$client->setRedirectUri('http://localhost/GateGourmet/Index/index_admin.php');
$client->setAccessType('offline');
$client->setPrompt('consent'); // Cambiado a 'consent'

// Manejar el código de autorización
if (isset($_GET['code'])) {
    try {
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $accessToken;
        header('Location: index_admin.php');
        exit();
    } catch (Exception $e) {
        echo 'Error fetching access token: ' . $e->getMessage();
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
    echo 'Error retrieving emails: ' . $e->getMessage();
    exit();
}

// Obtener detalles de los correos electrónicos
$emailData = [];

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
            // Recorremos las partes para obtener el texto plano o HTML
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

foreach ($messages as $message) {
    $messageDetail = $service->users_messages->get('me', $message->getId());
    $headers = $messageDetail->getPayload()->getHeaders();
    $emailDetails = [];

    foreach ($headers as $header) {
        switch ($header->getName()) {
            case 'Subject':
                $emailDetails['subject'] = $header->getValue();
                break;
            case 'From':
                $emailDetails['from'] = $header->getValue();
                break;
            case 'To':
                $emailDetails['to'] = $header->getValue();
                break;
            case 'Cc':
                $emailDetails['cc'] = $header->getValue();
                break;
            case 'Bcc':
                $emailDetails['bcc'] = $header->getValue();
                break;
            case 'Date':
                $emailDetails['date'] = date('d M Y H:i:s', strtotime($header->getValue()));
                break;
        }
    }


    // Obtener el cuerpo del mensaje
    $emailDetails['body'] = getBody($messageDetail);

    // Agregar el correo electrónico al array de correos
    $emailData[] = $emailDetails;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GateGourmet</title>
    <link rel="stylesheet" href="style_index_admin.css">
</head>
<body>
    <nav class="nav__principal">
        <ul class="nav__list">
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/listado_maestro/listado_maestro.php" class="nav__link"><img src="../imagenes/security.png" alt="Listado_maestro" class="imgs__menu">Listado maestro</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/Crear_documento/crear.php" class="nav__link"><img src="../imagenes/security.png" alt="Crear datetime" class="imgs__menu">Crear documento</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/Gestor_usuarios/php/user/index_gestor.php" class="nav__link"><img src="../imagenes/security.png" alt="Gestor_usuarios" class="imgs__menu">Gestor usuarios</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/log_eventos/log_eventos.php" class="nav__link"><img src="../imagenes/security.png" alt="log_eventos" class="imgs__menu">Log de eventos</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/Indicadores/indicadores.php" class="nav__link"><img src="../imagenes/config.png" alt="Indicadores" class="imgs__menu">Indicadores</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/procesos/procesos.php" class="nav__link"><img src="../Imagenes/macroprocesos2.png" alt="Procesos" class="imgs__menu">Procesos</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/index.php" class="nav__link"><img src="../imagenes/config.png" alt="Indicadores" class="imgs__menu">Reportes</a>
            </li>
            <li class="nav__item__user">
                <a href="http://localhost/GateGourmet/Movimientos/logout.php" class="cerrar__sesion__link"><img src="../Imagenes/image.png" alt="Usuario" class="img__usuario"><div class="cerrar__sesion">Cerrar Sesión</div></a>
            </li>
        </ul>
    </nav>
 <!-- Botón para mostrar notificaciones -->
<button id="alarmas">Mostrar notificaciones</button>

<!-- Menú desplegable -->
<div id="abirMenu" class="desplegar">
    <span id="cerrarMenu">X</span>

    <!-- Contenedor principal -->
    <div class="container_not">
        <h1>Correos Electrónicos</h1>

        <!-- Sección Aprobaciones -->
        <div class="opcion" id="opcion-alarmas">
    <h2 class="nombre-opcion" onclick="toggleContenido('contenido-alarmas')">Alarmas
        <span class="contador" id="contador-alarmas"></span> <!-- Contador de elementos -->
    </h2>
    <div class="contenido alarmas" id="contenido-alarmas">
        <?php if (empty($emailData)) { ?>
            <p>No hay correos electrónicos disponibles.</p>
        <?php } else { ?>
            <?php foreach ($emailData as $email) { ?>
                <div class="email-item">
                    <h2>Asunto: <?php echo $email['subject']; ?></h2><br>
                    <p><strong>De:</strong> <?php echo $email['from']; ?></p><br>
                    <p><strong>Para:</strong> <?php echo $email['to']; ?></p><br><br>
                    <p class="date"><strong>Fecha:</strong> <?php echo $email['date']; ?></p><br><br>
                    <div class="body"><?php echo $email['body']; ?></div>
                    <div class="email-actions">
                        <button class="mover-boton" onclick="moverCorreo(this, 'contenido-revisiones', 'contador-revisiones', 'contador-alarmas')">Mover a Revisiones</button>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>

<!-- Sección Revisiones -->
<div class="opcion" id="opcion-revisiones">
    <h2 class="nombre-opcion" onclick="toggleContenido('contenido-revisiones')">Revisiones
        <span class="contador" id="contador-revisiones"></span> <!-- Contador de elementos -->
    </h2>
    <div class="contenido revisiones" id="contenido-revisiones"></div>
</div>

<!-- Sección Aprobaciones -->
<div class="opcion" id="opcion-aprobaciones">
    <h2 class="nombre-opcion" onclick="toggleContenido('contenido-aprobaciones')">Aprobaciones
        <span class="contador" id="contador-aprobaciones"></span> <!-- Contador de elementos -->
    </h2>
    <div class="contenido aprobaciones" id="contenido-aprobaciones"></div>
</div>
</div>
</div>

<script>
// Función para mover correos entre contenedores y actualizar contadores
function moverCorreo(button, contenedorIdDestino, contadorDestinoId, contadorOrigenId) {
    // Obtener los contenedores
    var contenedorDestino = document.getElementById(contenedorIdDestino);
    var contadorDestino = document.getElementById(contadorDestinoId);
    var contadorOrigen = document.getElementById(contadorOrigenId);
    
    // Obtener el email-item
    var emailItem = button.closest('.email-item');
    
    // Mover el email al contenedor destino
    contenedorDestino.appendChild(emailItem);
    
    // Cambiar el texto y acción del botón según el contenedor destino
    if (contenedorIdDestino === 'contenido-revisiones') {
        button.textContent = "Aprobar";
        button.setAttribute('onclick', "moverCorreo(this, 'contenido-aprobaciones', 'contador-aprobaciones', 'contador-revisiones')");
    } else if (contenedorIdDestino === 'contenido-aprobaciones') {
        button.textContent = "Aprobado";
        button.disabled = true; // Deshabilitar el botón cuando llegue a "Aprobaciones"
    }
    
    // Actualizar los contadores
    actualizarContador(contadorDestinoId, contenedorDestino);
    actualizarContador(contadorOrigenId, emailItem.parentElement);
}

function actualizarContador(contadorId, contenedor) {
    var contador = document.getElementById(contadorId);
    contador.textContent = contenedor.children.length;
}

// Inicializar los contadores al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    actualizarContador('contador-alarmas', document.getElementById('contenido-alarmas'));
    actualizarContador('contador-revisiones', document.getElementById('contenido-revisiones'));
    actualizarContador('contador-aprobaciones', document.getElementById('contenido-aprobaciones'));
});
</script>

</script>
    <script src="script.js"></script>
    <div class="recuadroimagen"><img src="../Imagenes/Logo_oficial_B-N.png" class="logoindex">
        <img src="../Imagenes/logo__recuadro__gategourmet.png" alt="img4" class="triangulo">
    </div>
        <div class="cuadro1">
            <div class="recuadro1">
            <div><h3 class="title title__estrag">Estrategicos</h3></div>
            <p class="parrafo_estrategicos">Procesos destinados a definir <br>y controlar las metas de la <br>organizacion, sus politicas y <br>estrategias.</p>
        </div>
            <div class="circulo circulo1"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL%2FCI%2Ezip&parent=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL" class="link1"><h3 class="h3__2">Gestión corporativa</h3></a></div>
            <div class="circulo circulo2"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL%2FSEGURIDAD%2Ezip&parent=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL" class="link1"><h3>Compliance</h3></a></div>
        </div>
        <div class="cuadro2">
        <div class="recuadro2">
            <div><h3 class="title_misionales">Misionales</h3></div>
            <p class="parrafo_misionales">Procesos que permiten generar el producto/servicio que se entregan al cliente. <br>Agregan valor al cliente.</p>
        </div>
            <div class="circulo circulo3"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL%2FABASTECIMIENTOS%2Ezip&parent=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL" class="link1"><h3 class="h3__2">Supply chain</h3></a></div>
            <div class="circulo circulo4"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL%2FCULINARY%2Ezip&parent=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL" class="link1"><h3>Culinary</h3></a></div>
            <div class="circulo circulo5"><a href="https://workdrive.zoho.com/file/hpbd780390dcd8e964441a13bc568d214fb30" class="link1"><h3>Assembly</h3></a></div>
            <div class="circulo circulo6"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL%2FSERVICE%20DELIVERY%2Ezip&parent=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL" class="link1"><h3 class="h3__2">Service delivery</h3></a></div>
            <div class="circulo circulo7"><a href="https://1drv.ms/f/s!Aijf4XW5EsnbmQqAF01L3lqjcNLc?e=2rZY8S" class="link1"><h3 class="h3__2">Servicios institucionales</h3></a></div>
        </div>
        <div class="cuadro3">
        <div class="recuadro3">
            <div><h3 class="title_soporte">Soporte</h3></div>
            <p class="parrafo_soporte">Procesos que abarcan las actividades necesarias para el correcto funcionamiento de los procesos operativos.</p>
        </div>
            <div class="circulo circulo8"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL%2FFINANCIERA%2Ezip&parent=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL" class="link1"><h3>Financiera</h3></a></div>
            <div class="circulo circulo9"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL%2FCOSTOS%2Ezip&parent=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL" class="link1"><h3>Costos</h3></a></div>
            <div class="circulo circulo10"><a href="http://localhost/GateGourmet/Index/vista_usuarios/comunicaciones.php" class="link1"><h3 class="h3__3">Comunicaciónes</h3></a></div>
            <div class="circulo circulo11"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL%2FSISTEMAS%2Ezip&parent=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL" class="link1"><h3>IT</h3></a></div>
            <div class="circulo circulo12"><a href="http://localhost/GateGourmet/Index/vista_usuarios/security.php" class="link1"><h3>Security</h3></a></div>
            <div class="circulo circulo13"><a href="https://workdrive.zoho.com/file/hpbd780390dcd8e964441a13bc568d214fb30" class="link1"><h3 class="h3__2">Servicio al cliente</h3></a></div>
            <div class="circulo circulo14"><a href="https://show.zoho.com/show/open/hpbd733318fd8b4834fcebcbc508ace6b9c64" class="link1"><h3 class="h3__2">Facilty service</h3></a></div>
            <div class="circulo circulo15"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL%2FTALENTO%20HUMANO%2Ezip&parent=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FGESTION%20DOCUMENTAL" class="link1"><h3 class="h3__2">Talento humano</h3></a></div>
        </div>
</body>
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
<script src="menu.js"></script>
<script src="script_alarmas.js"></script>
</html>