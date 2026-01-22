<?php
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db = $_ENV['DB_NAME'] ?? 'sailing_club';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    global $pdo; // Make it global for now
} catch (PDOException $e) {
    if (getenv('APP_ENV') === 'production') {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection error. Please contact administrator.");
    } else {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>