<?php
require_once __DIR__ . '/../vendor/autoload.php'; // <- eine Ebene hoch

use Dotenv\Dotenv;

// Projektordner, in dem die .env liegt
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$dbhost = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$dbuser = $_ENV['DB_USERNAME'];
$dbpass = $_ENV['DB_PASSWORD'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Verbindung prüfen
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Verbindung fehlgeschlagen: ' . $conn->connect_error]);
    exit;
}

?>