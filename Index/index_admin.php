<?php
require '../vendor/autoload.php';
session_start();

// Conexión a la base de datos con manejo de excepciones
try {
    $mysqli = new mysqli("localhost", "root", "", "gategourmet");
    if ($mysqli->connect_error) {
        throw new Exception("Error al conectar a la base de datos: " . $mysqli->connect_error);
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: http://localhost/GateGourmet/login/login3.php');
    exit();
}

$nombre_usuario = $_SESSION['nombre_usuario']; // Nombre de usuario logueado
$id_correo = filter_input(INPUT_POST, 'id_correo', FILTER_SANITIZE_STRING);
$nuevo_estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

// Obtener detalles del usuario
$query_user = $mysqli->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
$query_user->bind_param("s", $nombre_usuario);
$query_user->execute();
$result_user = $query_user->get_result();

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
    $area = $user['area'];
    $cargo = $user['cargo'];
    $rol = $user['rol'];

    // Verificar flujo de trabajo según el rol
    if ($rol == 'digitador' && $nuevo_estado == 'revisiones') {
        // Buscar aprobador en la misma área
        $query_responsable = $mysqli->prepare("SELECT nombre_usuario FROM usuarios WHERE area = ? AND rol = 'aprobador' LIMIT 1");
        $query_responsable->bind_param("s", $area);
        $query_responsable->execute();
        $result_responsable = $query_responsable->get_result();

        if ($result_responsable->num_rows > 0) {
            $responsable = $result_responsable->fetch_assoc();
            $nuevo_destinatario = $responsable['nombre_usuario'];
        } else {
            $nuevo_destinatario = 'dramirez'; // A falta de aprobador, se envía al administrador
        }
    } elseif ($rol == 'aprobador' && $nuevo_estado == 'aprobaciones') {
        $nuevo_destinatario = 'dramirez'; // Aprobador envía al administrador
    } else {
        $nuevo_destinatario = $nombre_usuario; // Otros casos mantienen el mismo usuario
    }

    // Guardar acción del usuario
    $query = $mysqli->prepare("UPDATE acciones_usuarios SET estado = ?, destinatario = ? WHERE id_correo = ?");
    $query->bind_param("sss", $nuevo_estado, $nuevo_destinatario, $id_correo);
    $query->execute();
}

// Configuración del cliente de Google
$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Gmail::GMAIL_READONLY);
$client->setRedirectUri('http://localhost/GateGourmet/Index/index_admin.php');
$client->setAccessType('offline');
$client->setPrompt('consent');

// Manejar código de autorización
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

// Configurar servicio de Gmail
$client->setAccessToken($_SESSION['access_token']);
$service = new Google_Service_Gmail($client);

// Obtener correos
try {
    $response = $service->users_messages->listUsersMessages('me', ['maxResults' => 10]);
    $messages = $response->getMessages();
} catch (Exception $e) {
    echo 'Error retrieving emails: ' . $e->getMessage();
    exit();
}

// Decodificación de base64url
function base64url_decode($data) {
    $data = str_replace(['-', '_'], ['+', '/'], $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}

// Función para obtener el cuerpo del mensaje
function getBody($message) {
    global $service;
    $message = $service->users_messages->get('me', $message->getId());
    $payload = $message->getPayload();
    $parts = $payload->getParts();
    $body = '';

    if ($payload->getMimeType() == 'text/plain' || $payload->getMimeType() == 'text/html') {
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

// Obtener estado de correos guardados por el usuario
$query = $mysqli->prepare("SELECT * FROM acciones_usuarios WHERE nombre_usuario = ?");
$query->bind_param("s", $nombre_usuario);
$query->execute();
$result = $query->get_result();

$correos_guardados = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $correos_guardados[$row['id_correo']] = $row['estado']; // Guardar estado por id
    }
}

// Preparar datos de correos electrónicos
$emailData = [];
foreach ($messages as $message) {
    $messageDetail = $service->users_messages->get('me', $message->getId());
    $headers = $messageDetail->getPayload()->getHeaders();
    $emailDetails = ['id' => $message->getId()];

    foreach ($headers as $header) {
        if ($header->getName() == 'Subject') {
            $emailDetails['subject'] = $header->getValue();
        } elseif ($header->getName() == 'Date') {
            $emailDetails['date'] = date('d M Y H:i:s', strtotime($header->getValue()));
        }
    }

    $emailDetails['body'] = getBody($messageDetail);
    $emailDetails['estado'] = isset($correos_guardados[$emailDetails['id']]) ? $correos_guardados[$emailDetails['id']] : 'alarmas';

    $emailData[] = $emailDetails;
}

// Guardar acciones de correos electrónicos
function guardarAccion($nombre_usuario, $id_correo, $estado) {
    global $mysqli;
    $query = $mysqli->prepare("SELECT * FROM acciones_usuarios WHERE nombre_usuario = ? AND id_correo = ?");
    $query->bind_param("ss", $nombre_usuario, $id_correo);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $query = $mysqli->prepare("UPDATE acciones_usuarios SET estado = ? WHERE nombre_usuario = ? AND id_correo = ?");
        $query->bind_param("sss", $estado, $nombre_usuario, $id_correo);
    } else {
        $query = $mysqli->prepare("INSERT INTO acciones_usuarios (nombre_usuario, id_correo, estado) VALUES (?, ?, ?)");
        $query->bind_param("sss", $nombre_usuario, $id_correo, $estado);
    }
    $query->execute();
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
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/crear_areas_y_cargos/crear_cargo.php" class="nav__link"><img src="../Imagenes/macroprocesos2.png" alt="Procesos" class="imgs__menu">Crear cargo</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/crear_areas_y_cargos/crear_area.php" class="nav__link"><img src="../imagenes/config.png" alt="Indicadores" class="imgs__menu">Crear área</a>
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
        <h1>Notificaciones</h1>


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


    <script src="script.js"></script>
<!-- Primera vista -->
<div id="vista1" class="vista-activa">
    <div class="recuadroimagen">
        <img src="../Imagenes/Logo_oficial_B-N.png" class="logoindex">
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
    <!-- Flecha para cambiar a la segunda vista -->
    <div class="flecha" onclick="mostrarVista('vista2')">➡️ Cambiar de vista</div>
</div>

<!-- Segunda vista -->
<div id="vista2" class="vista">
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
    <!-- Flecha para volver a la primera vista -->
    <div class="flecha_volver" onclick="mostrarVista('vista1')">⬅️ Volver</div>
</div>

<script>
    // Función para alternar entre vistas
    function mostrarVista(vistaId) {
        // Ocultar todas las vistas
        document.getElementById('vista1').style.display = 'none';
        document.getElementById('vista2').style.display = 'none';
        // Mostrar la vista seleccionada
        document.getElementById(vistaId).style.display = 'block';
    }
    // Mostrar la primera vista por defecto
    mostrarVista('vista1');
</script>

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