<?php
require '../vendor/autoload.php';
require('fpdf.php');
session_start();
$mysqli = new mysqli("localhost", "root", "", "gategourmet"); // Conexión a la base de datos

if ($mysqli->connect_errno) {
    echo "Error al conectar a la base de datos: " . $mysqli->connect_error;
    exit();
}

// Verifica si el usuario está logueado
if (!isset($_SESSION['nombre_usuario'])) {
    // Redirigir a la página de login si no está logueado
    header('Location: http://localhost/GateGourmet/login/login3.php');
    exit();
}

$nombre_usuario = $_SESSION['nombre_usuario']; // El nombre de usuario guardado en la sesión

// Configuración del cliente de Google
$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Gmail::GMAIL_READONLY);
$client->setRedirectUri('http://localhost/GateGourmet/Index/index_admin.php');
$client->setAccessType('offline');
$client->setPrompt('consent');

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
    $response = $service->users_messages->listUsersMessages('me', ['maxResults' => 100]);
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

// Recuperar el estado previo de los correos guardados por el usuario
$query = "SELECT * FROM acciones_usuarios WHERE nombre_usuario = '$nombre_usuario'";
$result = $mysqli->query($query);

$correos_guardados = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $correos_guardados[$row['id_correo']] = $row['estado']; // Guardar el estado de cada correo
    }
}

foreach ($messages as $message) {
    $messageDetail = $service->users_messages->get('me', $message->getId());
    $headers = $messageDetail->getPayload()->getHeaders();
    $emailDetails = [];
    $emailDetails['id'] = $message->getId();

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
            case 'Date':
                $emailDetails['date'] = date('d M Y H:i:s', strtotime($header->getValue()));
                break;
        }
    }

    $emailDetails['body'] = getBody($messageDetail);

    // Verificar si el correo ya tiene un estado guardado
    if (isset($correos_guardados[$emailDetails['id']])) {
        $emailDetails['estado'] = $correos_guardados[$emailDetails['id']];
    } else {
        $emailDetails['estado'] = 'alarmas'; // Estado por defecto
    }

    $emailData[] = $emailDetails;
}

// Guardar cambios en la base de datos
function guardarAccion($nombre_usuario, $id_correo, $estado) {
    global $mysqli;
    // Verificar si ya existe una acción previa para este correo
    $query = "SELECT * FROM acciones_usuarios WHERE nombre_usuario = '$nombre_usuario' AND id_correo = '$id_correo'";
    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        // Si ya existe, actualizar el estado
        $query = "UPDATE acciones_usuarios SET estado = '$estado' WHERE nombre_usuario = '$nombre_usuario' AND id_correo = '$id_correo'";
    } else {
        // Si no existe, insertar una nueva acción
        $query = "INSERT INTO acciones_usuarios (nombre_usuario, id_correo, estado) VALUES ('$nombre_usuario', '$id_correo', '$estado')";
    }

    $mysqli->query($query);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GateGourmet</title>
    <link rel="stylesheet" href="style_index_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
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

<!-- Sección Alarmas -->
<div class="opcion" id="opcion-alarmas">
    <h2 class="nombre-opcion" onclick="toggleContenido('contenido-alarmas')">Alarmas
        <span class="contador" id="contador-alarmas"></span>
    </h2>
    <div class="contenido alarmas" id="contenido-alarmas">
        <?php if (empty($emailData)) { ?>
            <p>No hay correos electrónicos disponibles.</p>
        <?php } else { ?>
            <?php foreach ($emailData as $email) { if ($email['estado'] == 'alarmas') { ?>
                <div class="email-item" data-id-correo="<?php echo $email['id']; ?>" data-estado="<?php echo $email['estado']; ?>">
                    <h2>Asunto: <?php echo $email['subject']; ?></h2><br>
                    <p><strong>De:</strong> <?php echo $email['from']; ?></p><br>
                    <p><strong>Para:</strong> <?php echo $email['to']; ?></p><br><br>
                    <p class="date"><strong>Fecha:</strong> <?php echo $email['date']; ?></p><br>
                    <div class="body"><?php echo $email['body']; ?></div>
                    <div class="email-actions">
                        <button class="mover-boton" onclick="moverCorreo(this, 'revisiones', 'contador-revisiones', 'contador-alarmas')">Enviar</button>
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
                <h2>Asunto: <?php echo $email['subject']; ?></h2><br>
                <p><strong>De:</strong> <?php echo $email['from']; ?></p><br>
                <p><strong>Para:</strong> <?php echo $email['to']; ?></p><br><br>
                <p class="date"><strong>Fecha:</strong> <?php echo $email['date']; ?></p><br>
                <div class="body"><?php echo $email['body']; ?></div>
                <div class="email-actions">
                    <button class="mover-boton" onclick="moverCorreo(this, 'aprobaciones', 'contador-aprobaciones', 'contador-revisiones')">Enviar</button>
                    <button class="ignorar-boton" onclick="ignorarCorreo(this)">Ignorar</button>
                    <button class="devolver-boton" onclick="devolverCorreo(this, 'alarmas', 'contador-alarmas', 'contador-revisiones')">Devolver</button>
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
                <h2>Asunto: <?php echo $email['subject']; ?></h2><br>
                <p><strong>De:</strong> <?php echo $email['from']; ?></p><br>
                <p><strong>Para:</strong> <?php echo $email['to']; ?></p><br><br>
                <p class="date"><strong>Fecha:</strong> <?php echo $email['date']; ?></p><br>
                <div class="body"><?php echo $email['body']; ?></div>
                <div class="email-actions">
                    <button class="aprobar-boton" onclick="aprobarCorreo(this)">Aprobar</button>
                    <button class="ignorar-boton" onclick="ignorarCorreo(this)">Ignorar</button>
                    <button class="devolver-boton" onclick="devolverCorreo(this, 'revisiones', 'contador-revisiones', 'contador-aprobaciones')">Devolver</button>
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
    var subject = emailItem.querySelector('h2').innerText; // Obtener el asunto del correo

    // Mensaje de confirmación con SweetAlert
    swal({
        title: "Confirmar Acción",
        text: `¿Estás seguro de enviar el correo: "${subject}"?`,
        icon: "warning",
        buttons: {
            cancel: {
                text: "Cancelar",
                value: null,
                visible: true,
                className: "cancel-button",
                closeModal: false, // No cerrar el modal al cancelar
            },
            confirm: {
                text: "Confirmar",
                value: true,
                visible: true,
                className: "confirm-button",
                closeModal: false // No cerrar el modal al confirmar
            }
        }
    }).then((willMove) => {
        if (willMove) {
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

                // Actualizar los botones según el nuevo estado
                actualizarBotones(emailItem, nuevoEstado);

                // Actualizar los contadores del origen y destino
                actualizarContador(contadorOrigenId);
                actualizarContador(contadorDestinoId);
                swal("¡Éxito!", `Correo enviado a ${nuevoEstado}.`, "success"); // Notificación de éxito
            }).catch(error => {
                console.error('Error al mover el correo:', error);
                swal("Error", "No se pudo mover el correo. Inténtalo de nuevo.", "error");
            });
        } else {
            swal("Acción cancelada", "No se ha realizado ninguna acción.", "info"); // Mensaje de cancelación
        }
    });
}

// Función para ignorar correos
function ignorarCorreo(button) {
    var emailItem = button.closest('.email-item');
    var idCorreo = emailItem.getAttribute('data-id-correo');
    var subject = emailItem.querySelector('h2').innerText; // Obtener el asunto del correo

    // Mensaje de confirmación con SweetAlert
    swal({
        title: "Confirmar Acción",
        text: `¿Estás seguro de ignorar el correo: "${subject}"?`,
        icon: "warning",
        buttons: {
            cancel: {
                text: "Cancelar",
                value: null,
                visible: true,
                className: "cancel-button",
                closeModal: false, // No cerrar el modal al cancelar
            },
            confirm: {
                text: "Confirmar",
                value: true,
                visible: true,
                className: "confirm-button",
                closeModal: false // No cerrar el modal al confirmar
            }
        }
    }).then((willIgnore) => {
        if (willIgnore) {
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
                swal("¡Éxito!", "El correo ha sido ignorado.", "success"); // Notificación de éxito
            }).catch(error => {
                console.error('Error al ignorar el correo:', error);
                swal("Error", "No se pudo ignorar el correo. Inténtalo de nuevo.", "error");
            });
        } else {
            swal("Acción cancelada", "No se ha realizado ninguna acción.", "info"); // Mensaje de cancelación
        }
    });
}

// Función para devolver correos
function devolverCorreo(button, estadoAnterior, contadorDestinoId, contadorOrigenId) {
    var emailItem = button.closest('.email-item');
    var idCorreo = emailItem.getAttribute('data-id-correo');
    var subject = emailItem.querySelector('h2').innerText; // Obtener el asunto del correo

    // Mensaje de confirmación con SweetAlert
    swal({
        title: "Confirmar Acción",
        text: `¿Estás seguro de devolver el correo: "${subject}"?`,
        icon: "warning",
        buttons: {
            cancel: {
                text: "Cancelar",
                value: null,
                visible: true,
                className: "cancel-button",
                closeModal: false, // No cerrar el modal al cancelar
            },
            confirm: {
                text: "Confirmar",
                value: true,
                visible: true,
                className: "confirm-button",
                closeModal: false // No cerrar el modal al confirmar
            }
        }
    }).then((willReturn) => {
        if (willReturn) {
            // Llamada AJAX para actualizar el estado del correo
            var formData = new FormData();
            formData.append('id_correo', idCorreo);
            formData.append('estado', estadoAnterior);

            fetch('guardar_cambio.php', {
                method: 'POST',
                body: formData
            }).then(response => response.text())
              .then(data => {
                // Mover el correo de vuelta al contenedor anterior
                var contenedorDestino = document.getElementById('contenido-' + estadoAnterior);
                contenedorDestino.appendChild(emailItem);

                // Actualizar los botones según el estado al que ha sido devuelto
                actualizarBotones(emailItem, estadoAnterior);

                // Actualizar los contadores
                actualizarContador(contadorOrigenId);
                actualizarContador(contadorDestinoId);
                swal("¡Éxito!", `Correo devuelto a ${estadoAnterior}.`, "success"); // Notificación de éxito
            }).catch(error => {
                console.error('Error al devolver el correo:', error);
                swal("Error", "No se pudo devolver el correo. Inténtalo de nuevo.", "error");
            });
        } else {
            swal("Acción cancelada", "No se ha realizado ninguna acción.", "info"); // Mensaje de cancelación
        }
    });
}

// Función para aprobar correos
function aprobarCorreo(button) {
    var emailItem = button.closest('.email-item');
    var idCorreo = emailItem.getAttribute('data-id-correo');
    var subject = emailItem.querySelector('h2').innerText; // Obtener el asunto del correo

    // Mensaje de confirmación con SweetAlert
    swal({
        title: "Confirmar Acción",
        text: `¿Estás seguro de aprobar el correo: "${subject}"?`,
        icon: "warning",
        buttons: {
            cancel: {
                text: "Cancelar",
                value: null,
                visible: true,
                className: "cancel-button",
                closeModal: false, // No cerrar el modal al cancelar
            },
            confirm: {
                text: "Confirmar",
                value: true,
                visible: true,
                className: "confirm-button",
                closeModal: false // No cerrar el modal al confirmar
            }
        }
    }).then((willApprove) => {
        if (willApprove) {
            // Llamada AJAX para marcar el correo como aprobado
            var formData = new FormData();
            formData.append('id_correo', idCorreo);
            formData.append('estado', 'aprobado');

            fetch('guardar_cambio.php', {
                method: 'POST',
                body: formData
            }).then(response => response.text())
              .then(() => {
                emailItem.remove(); // Elimina el correo de la vista
                actualizarContador('contador-aprobaciones'); // Actualiza el contador de aprobaciones
                swal("¡Éxito!", "El correo ha sido aprobado.", "success"); // Notificación de éxito
            })
            .catch(error => {
                console.error('Error al aprobar el correo:', error);
                swal("Error", "No se pudo aprobar el correo. Inténtalo de nuevo.", "error");
            });
        } else {
            swal("Acción cancelada", "No se ha realizado ninguna acción.", "info"); // Mensaje de cancelación
        }
    });
}

// Función para actualizar los botones según el estado del correo
function actualizarBotones(emailItem, nuevoEstado) {
    var botones = emailItem.querySelectorAll('.boton-accion'); // Seleccionar todos los botones de acción

    botones.forEach(function(boton) {
        // Deshabilitar todos los botones inicialmente
        boton.disabled = true;
    });

    // Habilitar solo los botones correspondientes al nuevo estado
    switch (nuevoEstado) {
        case 'revisiones':
            emailItem.querySelector('.boton-ignorar').disabled = false;
            emailItem.querySelector('.boton-devolver').disabled = false;
            break;
        case 'aprobaciones':
            emailItem.querySelector('.boton-aprobar').disabled = false;
            break;
        case 'alarmas':
            emailItem.querySelector('.boton-mover').disabled = false;
            break;
        default:
            break;
    }
}

// Función para actualizar contadores
function actualizarContador(contadorId) {
    var contador = document.getElementById(contadorId);
    var numItems = document.getElementById('contenido-' + contadorId).children.length;
    contador.innerText = numItems; // Actualiza el texto del contador
}
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