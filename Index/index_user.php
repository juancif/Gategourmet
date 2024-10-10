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
    <div class="column1">
        <div class="opciones opcion1"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/ABASTECIMIENTOS?csf=1&web=1&e=8qA04K" class="link1"><h3 class="h3__2">ABASTECIMIENTOS</h3></a></div>
        <div class="opciones opcion2"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/CI?csf=1&web=1&e=yctetA" class="link1"><h3 class="h3__2">CI</h3></a></div>
        <div class="opciones opcion3"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/COMPLIANCE?csf=1&web=1&e=YHvNaD" class="link1"><h3 class="h3__2">COMPLIANCE</h3></a></div>
        <div class="opciones opcion4"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/COMPRAS?csf=1&web=1&e=3uqWmg" class="link1"><h3 class="h3__2">COMPRAS</h3></a></div>
        <div class="opciones opcion5"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/COSTOS?csf=1&web=1&e=g7Z84E" class="link1"><h3 class="h3__2">COSTOS</h3></a></div>
        <div class="opciones opcion6"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/CULINARY?csf=1&web=1&e=Frn6gA" class="link1"><h3 class="h3__2">CULINARY</h3></a></div>
        <div class="opciones opcion7"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/DESARROLLO?csf=1&web=1&e=EIfYLA" class="link1"><h3 class="h3__2">DESARROLLO</h3></a></div>
        <div class="opciones opcion8"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/FACILITY?csf=1&web=1&e=fnhRiV" class="link1"><h3 class="h3__2">FACILITY</h3></a></div>
        <div class="opciones opcion9"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/FINANCIERA?csf=1&web=1&e=4aCC1r" class="link1"><h3 class="h3__2">FINANCIERA</h3></a></div>
        <div class="opciones opcion10"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/IDS?csf=1&web=1&e=IZ4k8e" class="link1"><h3 class="h3__2">IDS</h3></a></div>
        <div class="opciones opcion11"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/KEY%20ACCOUNT?csf=1&web=1&e=sdremj" class="link1"><h3 class="h3__2">KEY ACCOUNT</h3></a></div>
        <div class="opciones opcion12"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/LAUNDRY?csf=1&web=1&e=AtbvR7" class="link1"><h3 class="h3__2">LAUNDRY</h3></a></div>
        <div class="opciones opcion13"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/MAKE%20%26%20PACK?csf=1&web=1&e=nYGJ66" class="link1"><h3 class="h3__2">MAKE & PACK</h3></a></div>
        <div class="opciones opcion14"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/PICK%20%26%20PACK?csf=1&web=1&e=WfWrMR" class="link1"><h3 class="h3__2">PICK & PACK</h3></a></div>
        <div class="opciones opcion15"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/SALAS?csf=1&web=1&e=X7LOjx" class="link1"><h3 class="h3__2">SALAS</h3></a></div>
        <div class="opciones opcion16"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/SEGURIDAD?csf=1&web=1&e=v9cqVy" class="link1"><h3 class="h3__2">SEGURIDAD</h3></a></div>
        <div class="opciones opcion17"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/SERVICE%20DELIVERY?csf=1&web=1&e=JNrYSO" class="link1"><h3 class="h3__2">SERVICE DELIVERY</h3></a></div>
        <div class="opciones opcion18"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/SISTEMAS?csf=1&web=1&e=cWR2LW" class="link1"><h3 class="h3__2">SISTEMAS</h3></a></div>
        <div class="opciones opcion19"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/TALENTO%20HUMANO?csf=1&web=1&e=xTPX2y" class="link1"><h3 class="h3__2">TALENTO HUMANO</h3></a></div>
        <div class="opciones opcion20"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/WASH%20%26%20PACK?csf=1&web=1&e=6HUFGO" class="link1"><h3 class="h3__2">WASH & PACK</h3></a></div>
        <div class="opciones opcion21"><a href="https://gategrouphq.sharepoint.com/:f:/r/sites/Prueba.gg/Shared%20Documents/Gesti%C3%B3n_Documental/OBSOLETOS?csf=1&web=1&e=kCmKM1" class="link1"><h3 class="h3__2">OBSOLETOS</h3></a></div>

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
        // Obtenemos el área del usuario desde PHP
        const areaUsuario = "<?php echo trim($area); ?>".toLowerCase(); // Convertimos a minúsculas y eliminamos espacios

        console.log("Área del usuario:", areaUsuario); // Para verificar en la consola el área del usuario

        // Seleccionamos todas las opciones que tienen la clase 'opciones'
        const opciones = document.querySelectorAll(".opciones");

        opciones.forEach(opcion => {
            // Obtenemos el texto del h3 y lo comparamos correctamente con el área del usuario
            const h3Text = opcion.querySelector("h3").textContent.trim().toLowerCase(); // Convertimos a minúsculas y eliminamos espacios

            console.log("Texto del h3:", h3Text); // Para verificar el texto en la consola

            if (h3Text !== areaUsuario) {
                // Si el área no coincide, deshabilitamos el enlace
                const link = opcion.querySelector("a");
                link.removeAttribute("href"); // Quitamos el enlace
                link.style.pointerEvents = "none"; // Deshabilitamos el clic
                link.style.opacity = "0.5"; // Cambiamos la opacidad para deshabilitar visualmente
                link.style.color = "#B0B0B0"; // Cambiamos el color para reflejar que está deshabilitado
            } else {
                // Resaltamos la opción activa (opcional)
                const link = opcion.querySelector("a");
                link.style.fontWeight = "bold"; // Resaltamos la opción activa
                link.style.color = "#ffffff"; // Usamos un color fuerte para la opción activa
            }
        });
    });
</script>


    <script src="menu.js"></script>
    <script src="script_alarmas.js"></script>
</body>
</html>