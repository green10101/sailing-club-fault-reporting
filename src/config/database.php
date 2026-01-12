<?php
require_once __DIR__ . '/../../vendor/autoload.php';

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
// $dotenv->load();

$host = 'localhost'; // $_ENV['DB_HOST'] ?? 'localhost';
$db = 'sailing_club'; // $_ENV['DB_DATABASE'] ?? 'sailing_club';
$user = 'root'; // $_ENV['DB_USERNAME'] ?? 'root';
$pass = ''; // $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    global $pdo; // Make it global for now
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>