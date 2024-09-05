<?php
require 'vendor/autoload.php';

use Google\Client;

// Configura el cliente de Google
$client = new Client();
$client->setAuthConfig('config/credentials.json');
$client->setRedirectUri('http://localhost/GateGourmet/correos/callback.php');
$client->setAccessType('offline');

// Intercambia el código de autorización por un token de acceso
if (isset($_GET['code'])) {
    $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $accessToken = $client->getAccessToken();
    file_put_contents('config/token.json', json_encode($accessToken));
    echo 'Token guardado con éxito.';
} else {
    echo 'No se recibió el código de autorización.';
}
?>
