<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Gmail;

// Configura el cliente de Google
$client = new Client();
$client->setAuthConfig('config/credentials.json');
$client->addScope(Gmail::GMAIL_READONLY);
$client->setRedirectUri('http://localhost/GateGourmet/token.php');
$client->setAccessType('offline');
$client->setPrompt('consent');

// Genera la URL de autorización y redirige al usuario
$authUrl = $client->createAuthUrl();
header('Location: ' . $authUrl);
exit;
?>
