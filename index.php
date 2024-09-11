<?php
require 'vendor/autoload.php';

session_start();

// Configuración del cliente de Google
$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Gmail::GMAIL_READONLY);
$client->setRedirectUri('http://localhost/GateGourmet/index.php'); // Cambiar a la URL correcta si es necesario
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Manejar el código de autorización
if (isset($_GET['code'])) {
    try {
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $accessToken;
        header('Location: index.php'); // Redirige a la misma página para evitar reenvíos del formulario
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
foreach ($messages as $message) {
    $msg = $service->users_messages->get('me', $message->getId());
    $payload = $msg->getPayload();
    $headers = $payload->getHeaders();
    $parts = $payload->getParts();

    $emailDetails = [
        'subject' => '',
        'from' => '',
        'to' => '',
        'cc' => '',
        'bcc' => '',
        'date' => '',
        'body' => '',
    ];

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

    foreach ($parts as $part) {
        if ($part->getMimeType() == 'text/plain') {
            $emailDetails['body'] = base64url_decode($part->getBody()->getData());
        } elseif ($part->getMimeType() == 'text/html') {
            $emailDetails['body'] = base64url_decode($part->getBody()->getData());
        }
    }

    $emailData[] = $emailDetails;
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correos Electrónicos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .email-list {
            margin-top: 20px;
        }

        .email-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .email-item h2 {
            margin: 0;
            color: #333;
        }

        .email-item p {
            margin: 5px 0;
            color: #666;
        }

        .email-item .date {
            font-size: 0.9em;
            color: #999;
        }

        .email-item .body {
            white-space: pre-wrap;
            word-wrap: break-word;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }

        .logout {
            text-align: center;
            margin-top: 30px;
        }

        .logout a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Correos Electrónicos</h1>
        <div class="email-list">
            <?php
            if (empty($emailData)) {
                echo '<p>No hay correos electrónicos disponibles.</p>';
            } else {
                foreach ($emailData as $email) {
                    echo '<div class="email-item">';
                    echo '<h2>Asunto: ' . htmlspecialchars($email['subject']) . '</h2>';
                    echo '<p><strong>De:</strong> ' . htmlspecialchars($email['from']) . '</p>';
                    if (!empty($email['to'])) {
                        echo '<p><strong>Para:</strong> ' . htmlspecialchars($email['to']) . '</p>';
                    }
                    if (!empty($email['cc'])) {
                        echo '<p><strong>Cc:</strong> ' . htmlspecialchars($email['cc']) . '</p>';
                    }
                    echo '<p class="date"><strong>Fecha:</strong> ' . htmlspecialchars($email['date']) . '</p>';
                    echo '<div class="body">';
                    echo '<strong>Contenido:</strong><br>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        <div class="logout">
            <p><a href="logout.php">Desconectar</a></p>
        </div>
    </div>
</body>
</html>
