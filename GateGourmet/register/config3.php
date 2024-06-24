<?php
$host = 'localhost';
$dbname = 'prueba';
$username = 'root';
$password = '';
try {
// http://php.net/manual/en/pdo.connections.php
$dbConn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 // Setting Error Mode as Exception //
// More on setAttribute: http://php.net/manual/en/pdo.setattribute.php //
} catch(PDOException $e) {
die("Could not connect to the database $dbname :" . $e->getMessage());
}
?>