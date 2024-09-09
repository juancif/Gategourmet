<?php
require 'vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Gmail::GMAIL_READONLY);
$client->setRedirectUri('http://localhost/GateGourmet/oauth2callback.php');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

if (!isset($_SESSION['access_token']) || $_SESSION['access_token'] == '') {
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit();
}

$client->setAccessToken($_SESSION['access_token']);
$service = new Google_Service_Gmail($client);

try {
    $response = $service->users_messages->listUsersMessages('me');
    foreach ($response->getMessages() as $message) {
        $msg = $service->users_messages->get('me', $message->getId());
        $payload = $msg->getPayload();
        $headers = $payload->getHeaders();
        
        $subject = '';
        $from = '';
        $date = '';
        $body = '';

        foreach ($headers as $header) {
            if ($header->getName() == 'Subject') {
                $subject = $header->getValue();
            } elseif ($header->getName() == 'From') {
                $from = $header->getValue();
            } elseif ($header->getName() == 'Date') {
                $date = $header->getValue();
            }
        }

        $parts = $payload->getParts();
        if ($parts) {
            foreach ($parts as $part) {
                if ($part->getMimeType() == 'text/plain') {
                    $body = base64_decode(str_replace(array('-', '_'), array('+', '/'), $part->getBody()->getData()));
                } elseif ($part->getMimeType() == 'text/html') {
                    $body = base64_decode(str_replace(array('-', '_'), array('+', '/'), $part->getBody()->getData()));
                }
            }
        }

        saveToDatabase($subject, $from, $date, $body);
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

function saveToDatabase($subject, $from, $date, $body) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=gategourmet', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("INSERT INTO correos (asunto, remitente, fecha, cuerpo) VALUES (:asunto, :remitente, :fecha, :cuerpo)");
        $stmt->bindParam(':asunto', $subject);
        $stmt->bindParam(':remitente', $from);
        $stmt->bindParam(':fecha', $date);
        $stmt->bindParam(':cuerpo', $body);
        $stmt->execute();

    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gmail API Integration</title>
</head>
<body>
    <h1>Gmail API Integration</h1>
    <p><a href="logout.php">Desconectar</a></p>
    <!-- Aquí puedes agregar más contenido o formularios si es necesario -->
</body>
</html>
