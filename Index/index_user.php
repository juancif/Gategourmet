<?php
require '../vendor/autoload.php';
session_start();
$area = isset($_SESSION['area']) ? $_SESSION['area'] : '';


// Configuración del cliente de Google
$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Gmail::GMAIL_READONLY);
$client->setRedirectUri('http://localhost/GateGourmet/Index/index_user.php');
$client->setAccessType('offline');
$client->setPrompt('consent'); // Cambiado a 'consent'

// Manejar el código de autorización
if (isset($_GET['code'])) {
    try {
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $accessToken;
        header('Location: index_user.php');
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

        <!-- Sección Aprobaciones -->
        <div class="opcion" id="opcion-aprobaciones">
    <h2 class="nombre-opcion" onclick="toggleContenido('contenido-aprobaciones')">Aprobaciones
        <span class="contador"></span> <!-- Contador de elementos -->
    </h2>
    <div class="contenido aprobaciones" id="contenido-aprobaciones">
                <?php if (empty($emailData)) { ?>
                    <p>No hay correos electrónicos disponibles.</p>
                <?php } else { ?>
                    <?php foreach ($emailData as $email) { ?>
                        <div class="email-item">
                            <h2>Asunto: <?php echo htmlspecialchars($email['subject']); ?></h2>

                            <!-- Extraer solo el nombre del campo "De:" -->
                            <p><strong>De:</strong> 
                                <?php
                                if (preg_match('/"(.*?)"/', $email['from'], $matches)) {
                                    echo htmlspecialchars($matches[1]); // Muestra solo el nombre
                                } else {
                                    echo htmlspecialchars($email['from']); // En caso de que no se encuentre el nombre
                                }
                                ?>
                            </p>

                            <!-- Extraer solo el nombre del campo "Para:" -->
                            <?php if (!empty($email['to'])) { ?><br>
                            <p><strong>Para:</strong> 
                                <?php
                                if (preg_match('/"(.*?)"/', $email['to'], $matches)) {
                                    echo htmlspecialchars($matches[1]); // Muestra solo el nombre
                                } else {
                                    echo htmlspecialchars($email['to']); // En caso de que no se encuentre el nombre
                                }
                                ?>
                            </p>
                            <?php } ?><br>

                            <?php if (!empty($email['cc'])) { ?><br>
                                <!-- Si quieres aplicar lo mismo para Cc, puedes usar el mismo patrón -->
                                <p><strong>Cc:</strong> 
                                    <?php
                                    if (preg_match('/"(.*?)"/', $email['cc'], $matches)) {
                                        echo htmlspecialchars($matches[1]); // Muestra solo el nombre
                                    } else {
                                        echo htmlspecialchars($email['cc']); // En caso de que no se encuentre el nombre
                                    }
                                    ?>
                                </p>
                            <?php } ?><br>

                            <p class="date"><strong>Fecha:</strong> <?php echo htmlspecialchars($email['date']); ?></p><br>

                            <!-- Mostrar solo el contenido en HTML dentro del recuadro, no como texto plano -->
                            <div class="body">
                                <?php if (strpos($email['body'], '<html') !== false) { ?>
                                    <!-- Mostrar el cuerpo si contiene HTML -->
                                    <div><?php echo $email['body']; ?></div>
                                <?php } ?>
                            </div>
                            <div class="email-actions">
                                <button onclick="confirmAction('aprobar', '<?php echo htmlspecialchars($email['subject']); ?>')">Aprobar</button>
                                <button onclick="confirmAction('revisar', '<?php echo htmlspecialchars($email['subject']); ?>')">Revisar</button>
                                <button onclick="confirmAction('no aprobar', '<?php echo htmlspecialchars($email['subject']); ?>')">No aprobar</button>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>


        <!-- Sección Revisiones -->
        <div class="opcion" id="opcion-revisiones">
    <h2 class="nombre-opcion" onclick="toggleContenido('contenido-revisiones')">Revisiones
        <span class="contador"></span> <!-- Contador de elementos -->
    </h2>
    <div class="contenido revisiones" id="contenido-revisiones">
                <?php if (empty($emailData)) { ?>
                    <p>No hay correos electrónicos disponibles.</p>
                <?php } else { ?>
                    <?php foreach ($emailData as $email) { ?>
                        <div class="email-item">
                            <h2>Asunto: <?php echo htmlspecialchars($email['subject']); ?></h2>
                            <p><strong>De:</strong> 
                        <?php
                        if (preg_match('/"(.*?)"/', $email['from'], $matches)) {
                            echo htmlspecialchars($matches[1]); // Muestra solo el nombre
                        } else {
                            echo htmlspecialchars($email['from']); // En caso de que no se encuentre el nombre
                        }
                        ?>
                    </p>

                    <!-- Extraer solo el nombre del campo "Para:" -->
                    <?php if (!empty($email['to'])) { ?><br>
                    <p><strong>Para:</strong> 
                        <?php
                        if (preg_match('/"(.*?)"/', $email['to'], $matches)) {
                            echo htmlspecialchars($matches[1]); // Muestra solo el nombre
                        } else {
                            echo htmlspecialchars($email['to']); // En caso de que no se encuentre el nombre
                        }
                        ?>
                    </p>
                    <?php } ?><br>

                    <?php if (!empty($email['cc'])) { ?><br>
                        <!-- Si quieres aplicar lo mismo para Cc, puedes usar el mismo patrón -->
                        <p><strong>Cc:</strong> 
                            <?php
                            if (preg_match('/"(.*?)"/', $email['cc'], $matches)) {
                                echo htmlspecialchars($matches[1]); // Muestra solo el nombre
                            } else {
                                echo htmlspecialchars($email['cc']); // En caso de que no se encuentre el nombre
                            }
                            ?>
                        </p>
                    <?php } ?><br>
                            <p class="date"><strong>Fecha:</strong> <?php echo htmlspecialchars($email['date']); ?></p><br>
                            <div class="body">
                                <?php if (strpos($email['body'], '<html') !== false) { ?>
                                    <div><?php echo $email['body']; ?></div>
                                <?php } else { ?>
                                    <p><?php echo nl2br(htmlspecialchars($email['body'])); ?></p>
                                <?php } ?>
                            </div>
                                <div class="email-actions">
                                <button onclick="confirmAction('aprobar', '<?php echo htmlspecialchars($email['subject']); ?>')">Aprobar</button>
                                <button onclick="confirmAction('revisar', '<?php echo htmlspecialchars($email['subject']); ?>')">Revisar</button>
                                <button onclick="confirmAction('no aprobar', '<?php echo htmlspecialchars($email['subject']); ?>')">No aprobar</button>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>

        <!-- Sección Alarmas -->
        <div class="opcion" id="opcion-alarmass">
    <h2 class="nombre-opcion" onclick="toggleContenido('contenido-alarmass')">Alarmas
        <span class="contador"></span> <!-- Contador de elementos -->
    </h2>
    <div class="contenido alarmass" id="contenido-alarmass">
                <?php if (empty($emailData)) { ?><br>
                    <p>No hay correos electrónicos disponibles.</p>
                <?php } else { ?>
                    <?php foreach ($emailData as $email) { ?>
                        <div class="email-item">
                            <h2>Asunto: <?php echo htmlspecialchars($email['subject']); ?></h2>
                            <p><strong>De:</strong> 
                        <?php
                        if (preg_match('/"(.*?)"/', $email['from'], $matches)) {
                            echo htmlspecialchars($matches[1]); // Muestra solo el nombre
                        } else {
                            echo htmlspecialchars($email['from']); // En caso de que no se encuentre el nombre
                        }
                        ?>
                    </p>

                    <!-- Extraer solo el nombre del campo "Para:" -->
                    <?php if (!empty($email['to'])) { ?><br>
                    <p><strong>Para:</strong> 
                        <?php
                        if (preg_match('/"(.*?)"/', $email['to'], $matches)) {
                            echo htmlspecialchars($matches[1]); // Muestra solo el nombre
                        } else {
                            echo htmlspecialchars($email['to']); // En caso de que no se encuentre el nombre
                        }
                        ?>
                    </p>
                    <?php } ?><br>

                    <?php if (!empty($email['cc'])) { ?><br>
                        <!-- Si quieres aplicar lo mismo para Cc, puedes usar el mismo patrón -->
                        <p><strong>Cc:</strong> 
                            <?php
                            if (preg_match('/"(.*?)"/', $email['cc'], $matches)) {
                                echo htmlspecialchars($matches[1]); // Muestra solo el nombre
                            } else {
                                echo htmlspecialchars($email['cc']); // En caso de que no se encuentre el nombre
                            }
                            ?>
                        </p>
                    <?php } ?><br>
                            <p class="date"><strong>Fecha:</strong> <?php echo htmlspecialchars($email['date']); ?></p><br>
                            <div class="body">
                                <?php if (strpos($email['body'], '<html') !== false) { ?>
                                    <div><?php echo $email['body']; ?></div>
                                <?php } else { ?>
                                    <p><?php echo nl2br(htmlspecialchars($email['body'])); ?></p>
                                <?php } ?>
                            </div>
                            <div class="email-actions">
                                <button onclick="confirmAction('aprobar', '<?php echo htmlspecialchars($email['subject']); ?>')">Aprobar</button>
                                <button onclick="confirmAction('revisar', '<?php echo htmlspecialchars($email['subject']); ?>')">Revisar</button>
                                <button onclick="confirmAction('no aprobar', '<?php echo htmlspecialchars($email['subject']); ?>')">No aprobar</button>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
    <div class="recuadroimagen"><img src="../Imagenes/Logo_oficial_B-N.png" class="logoindex">
        <img src="../Imagenes/logo__recuadro__gategourmet.png" alt="img4" class="triangulo">
    </div>
    <div class="cuadro1" id="cuadro1">
        <div class="recuadro1">
            <div><h3 class="title title__estrag">Estrategicos</h3></div>
            <p class="parrafo_estrategicos">Procesos destinados a definir <br>y controlar las metas de la <br>organizacion, sus politicas y <br>estrategias.</p>
        </div>
        <div class="circulo circulo1"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?newTargetListUrl=%2Fsites%2FPrueba%2Egg%2FShared%20Documents&viewpath=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FForms%2FAllItems%2Easpx&id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FDocumentos%5F%5Fprueba%2FGesti%C3%B3n%20Corporativa&viewid=7e698b00%2D50a8%2D4a9f%2Daf64%2D4414983a1399" class="link1"><h3 class="h3__2">Gestión corporativa</h3></a></div>
        <div class="circulo circulo2"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?newTargetListUrl=%2Fsites%2FPrueba%2Egg%2FShared%20Documents&viewpath=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FForms%2FAllItems%2Easpx&id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FDocumentos%5F%5Fprueba%2FCompliance&viewid=7e698b00%2D50a8%2D4a9f%2Daf64%2D4414983a1399" class="link1"><h3>Compliance</h3></a></div>
    </div>
    <div class="cuadro2" id="cuadro2">
        <div class="recuadro2">
            <div><h3 class="title_misionales">Misionales</h3></div>
            <p class="parrafo_misionales">Procesos que permiten generar el producto/servicio que se entregan al cliente. <br>Agregan valor al cliente.</p>
        </div>
        <div class="circulo circulo3"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?newTargetListUrl=%2Fsites%2FPrueba%2Egg%2FShared%20Documents&viewpath=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FForms%2FAllItems%2Easpx&id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FDocumentos%5F%5Fprueba%2FSupply%20Chain&viewid=7e698b00%2D50a8%2D4a9f%2Daf64%2D4414983a1399" class="link1"><h3 class="h3__2">Supply chain</h3></a></div>
        <div class="circulo circulo4"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?newTargetListUrl=%2Fsites%2FPrueba%2Egg%2FShared%20Documents&viewpath=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FForms%2FAllItems%2Easpx&id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FDocumentos%5F%5Fprueba%2FCulinary&viewid=7e698b00%2D50a8%2D4a9f%2Daf64%2D4414983a1399" class="link1"><h3>Culinary</h3></a></div>
        <div class="circulo circulo5"><a href="https://workdrive.zoho.com/file/hpbd780390dcd8e964441a13bc568d214fb30" class="link1"><h3>Assembly</h3></a></div>
        <div class="circulo circulo6"><a href="http://localhost/GateGourmet/Index/vista_usuarios/security.php" class="link1"><h3 class="h3__2">Service delivery</h3></a></div>
        <div class="circulo circulo7"><a href="https://1drv.ms/f/s!Aijf4XW5EsnbmQqAF01L3lqjcNLc?e=2rZY8S" class="link1"><h3 class="h3__2">Servicios institucionales</h3></a></div>
    </div>
    <div class="cuadro3" id="cuadro3">
        <div class="recuadro3">
            <div><h3 class="title_soporte">Soporte</h3></div>
            <p class="parrafo_soporte">Procesos que abarcan las actividades necesarias para el correcto funcionamiento de los procesos operativos.</p>
        </div>
        <div class="circulo circulo8"><a href="https://1drv.ms/w/s!Aijf4XW5EsnbmQ0kfSuUy3leGNi0?e=M6FAKA" class="link1"><h3>Financiera</h3></a></div>
        <div class="circulo circulo9"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/GESTION%20DOCUMENTAL?csf=1&web=1&e=BSXQQx" class="link1"><h3>Costos</h3></a></div>
        <div class="circulo circulo10"><a href="http://localhost/GateGourmet/Index/index_comunicaciones.php" class="link1"><h3 class="h3__3">Comunicaciones</h3></a></div>
        <div class="circulo circulo11"><a href="https://show.zoho.com/show/open/hpbd733318fd8b4834fcebcbc508ace6b9c64" class="link1"><h3>IT</h3></a></div>
        <div class="circulo circulo12"><a href="http://localhost/GateGourmet/Index/vista_usuarios/security.php" class="link1"><h3>Security</h3></a></div>
        <div class="circulo circulo13"><a href="https://workdrive.zoho.com/file/hpbd780390dcd8e964441a13bc568d214fb30" class="link1"><h3 class="h3__2">Servicio al cliente</h3></a></div>
        <div class="circulo circulo14"><a href="https://show.zoho.com/show/open/hpbd733318fd8b4834fcebcbc508ace6b9c64" class="link1"><h3 class="h3__2">Facilty service</h3></a></div>
        <div class="circulo circulo15"><a href="https://show.zoho.com/show/open/hpbd733318fd8b4834fcebcbc508ace6b9c64" class="link1"><h3 class="h3__2">Talento humano</h3></a></div>
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
</body>
</html>