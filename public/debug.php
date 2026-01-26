<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug Information</h2>";
echo "PHP Version: " . phpversion() . "<br><br>";

// Check if vendor exists
echo "Vendor folder exists: " . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'YES' : 'NO') . "<br>";

// Try to load autoload
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "Autoload loaded: YES<br><br>";
} catch (Exception $e) {
    echo "Autoload error: " . $e->getMessage() . "<br><br>";
    die();
}

// Check .env file
echo ".env file exists: " . (file_exists(__DIR__ . '/../.env') ? 'YES' : 'NO') . "<br>";

// Try to load .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
    echo "Environment loaded: YES<br><br>";
} catch (Exception $e) {
    echo "Environment load error: " . $e->getMessage() . "<br><br>";
}

// Show DB credentials (masked)
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "<br>";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'NOT SET') . "<br>";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'NOT SET') . "<br>";
echo "DB_PASS: " . (isset($_ENV['DB_PASS']) ? 'SET (length: ' . strlen($_ENV['DB_PASS']) . ')' : 'NOT SET') . "<br><br>";

// Try database connection
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $db = $_ENV['DB_NAME'] ?? 'bosun_proto';
    $user = $_ENV['DB_USER'] ?? 'Markgreengian';
    $pass = $_ENV['DB_PASS'] ?? 'tQ/38e$4oU5U';
    
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection: SUCCESS!<br>";
} catch (PDOException $e) {
    echo "Database connection: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}
?>