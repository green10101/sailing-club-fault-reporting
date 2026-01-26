<?php
// Debug script to check user data
require_once '../vendor/autoload.php';
require_once '../src/config/database.php';

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo "Usage: debug_user.php?email=user@example.com";
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<pre>";
        print_r($user);
        echo "</pre>";
    } else {
        echo "User not found with email: " . htmlspecialchars($email);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
