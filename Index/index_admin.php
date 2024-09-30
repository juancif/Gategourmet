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
            if ($part->getMimeType() == 'text/plain') {
                $body .= base64url_decode($part->getBody()->getData());
            } elseif ($part->getMimeType() == 'text/html') {
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
    <button id="alarmas">Mostrar notificaciones</button>

    <div id="abirMenu" class="desplegar">
      <span id="cerrarMenu">X</span>
      <ddiv class="container">
    <h1>Correos Electrónicos</h1>
    <div class="aprobaciones"><h2>Aprobaciones</h2>
    <div class="email-list">
            <?php if (empty($emailData)) { ?>
                <p>No hay correos electrónicos disponibles.</p>
            <?php } else { ?>
                <?php foreach ($emailData as $email) { ?>
                    <div class="email-item">
                        <h2>Asunto: <?php echo htmlspecialchars($email['subject']); ?></h2><br>
                        <p><strong>De:</strong> <?php echo htmlspecialchars($email['from']); ?></p><br>
                        <?php if (!empty($email['to'])) { ?><br>
                            <p><strong>Para:</strong> <?php echo htmlspecialchars($email['to']); ?></p><br>
                        <?php } ?>
                        <?php if (!empty($email['cc'])) { ?>
                            <p><strong>Cc:</strong> <?php echo htmlspecialchars($email['cc']); ?></p><br>
                        <?php } ?><br>
                        <p class="date"><strong>Fecha:</strong> <?php echo htmlspecialchars($email['date']); ?></p><br>
                        <div class="body">
                        <strong>Contenido:</strong><br>
                        <?php if (strpos($email['body'], '<html') !== false) { ?>
                            <!-- Si el cuerpo del mensaje es HTML -->
                            <div><?php echo $email['body']; ?></div><br>
                        <?php } else { ?>
                            <!-- Si es texto plano -->
                            <p><?php echo nl2br(htmlspecialchars($email['body'])); ?></p>
                        <?php } ?>
                        </div>
                        <div class="email-actions">
                            <button>Verificar</button>
                            <button>Devolver</button>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
<div class="revisiones"><h2>Revisiones</h2>
    <div class="email-list">
            <?php if (empty($emailData)) { ?>
                <p>No hay correos electrónicos disponibles.</p>
            <?php } else { ?>
                <?php foreach ($emailData as $email) { ?>
                    <div class="email-item">
                        <h2>Asunto: <?php echo htmlspecialchars($email['subject']); ?></h2><br>
                        <p><strong>De:</strong> <?php echo htmlspecialchars($email['from']); ?></p>
                        <?php if (!empty($email['to'])) { ?><br>
                            <p><strong>Para:</strong> <?php echo htmlspecialchars($email['to']); ?></p>
                        <?php } ?><br>
                        <?php if (!empty($email['cc'])) { ?><br>
                            <p><strong>Cc:</strong> <?php echo htmlspecialchars($email['cc']); ?></p>
                        <?php } ?><br><br>
                        <p class="date"><strong>Fecha:</strong> <?php echo htmlspecialchars($email['date']); ?></p>
                        <div class="body">
                        <strong>Contenido:</strong><br>
                        <?php if (strpos($email['body'], '<html') !== false) { ?>
                            <!-- Si el cuerpo del mensaje es HTML -->
                            <div><?php echo $email['body']; ?></div><br>
                        <?php } else { ?>
                            <!-- Si es texto plano -->
                            <p><?php echo nl2br(htmlspecialchars($email['body'])); ?></p>
                        <?php } ?>
                        </div>
                        <div class="email-actions">
                            <button>Verificar</button>
                            <button>Devolver</button>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
<div class="alarmass"><h2>Alarmas</h2>
    <div class="email-list">
            <?php if (empty($emailData)) { ?>
                <p>No hay correos electrónicos disponibles.</p>
            <?php } else { ?>
                <?php foreach ($emailData as $email) { ?>
                    <div class="email-item">
                        <h2>Asunto: <?php echo htmlspecialchars($email['subject']); ?></h2><br>
                        <p><strong>De:</strong> <?php echo htmlspecialchars($email['from']); ?></p><br>
                        <?php if (!empty($email['to'])) { ?><br>
                            <p><strong>Para:</strong> <?php echo htmlspecialchars($email['to']); ?></p><br>
                        <?php } ?>
                        <?php if (!empty($email['cc'])) { ?>
                            <p><strong>Cc:</strong> <?php echo htmlspecialchars($email['cc']); ?></p><br>
                        <?php } ?><br>
                        <p class="date"><strong>Fecha:</strong> <?php echo htmlspecialchars($email['date']); ?></p><br>
                        <div class="body">
                        <strong>Contenido:</strong><br>
                        <?php if (strpos($email['body'], '<html') !== false) { ?>
                            <!-- Si el cuerpo del mensaje es HTML -->
                            <div><?php echo $email['body']; ?></div><br>
                        <?php } else { ?>
                            <!-- Si es texto plano -->
                            <p><?php echo nl2br(htmlspecialchars($email['body'])); ?></p>
                        <?php } ?>
                        </div>
                        <div class="email-actions">
                            <button>Verificar</button>
                            <button>Devolver</button>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
</div>
</div>
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
<script src="menu.js"></script>
<script src="script_alarmas.js"></script>
</html>