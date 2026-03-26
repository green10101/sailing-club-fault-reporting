<?php
require_once __DIR__ . '/vendor_bootstrap.php';
loadVendorAutoload();

// Load environment variables
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    } catch (Exception $e) {
        error_log("Failed to load .env file: " . $e->getMessage());
    }
}

// Try both $_ENV and getenv() for compatibility
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$db = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'sailing_club';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

// Debug: Log what we're using (remove after testing)
error_log("DB Connection attempt: host=$host, db=$db, user=$user");

$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    global $pdo; // Make it global for now
} catch (PDOException $e) {
    $isProduction = ($_ENV['APP_ENV'] ?? getenv('APP_ENV')) === 'production';
    error_log("Database connection failed: " . $e->getMessage());
    if ($isProduction) {
        die("Database connection error. Please contact administrator.");
    } else {
        die("Connection failed: " . $e->getMessage() . "<br>Using: host=$host, db=$db, user=$user");
    }
}
?>