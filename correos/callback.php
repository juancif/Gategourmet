<?php
require '../vendor/autoload.php'; // Asegúrate de que este archivo exista

use Google\Client;
use Google\Service\Gmail;

// Configuración de la API de Gmail
$client = new Client();
$client->setAuthConfig('../config/credentials.json');
$client->addScope(Gmail::GMAIL_READONLY);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Leer el token de acceso desde el archivo
$accessToken = json_decode(file_get_contents('config/token.json'), true);
if ($accessToken) {
    $client->setAccessToken($accessToken);

    // Verificar si el token ha expirado y refrescarlo si es necesario
    if ($client->isAccessTokenExpired()) {
        $refreshToken = $accessToken['refresh_token'];
        $client->fetchAccessTokenWithRefreshToken($refreshToken);
        file_put_contents('config/token.json', json_encode($client->getAccessToken()));
    }
} else {
    // Manejo de error si el token no se puede leer
    die('Token de acceso no encontrado.');
}

$service = new Gmail($client);

// Intentar obtener los mensajes
try {
    $user = 'me';
    $response = $service->users_messages->listUsersMessages($user, ['maxResults' => 10]);

    // Procesar los correos como en el código anterior...
} catch (Exception $e) {
    echo 'Excepción: ',  $e->getMessage(), "\n";
}
?>
