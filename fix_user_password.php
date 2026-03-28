<?php
// Script to fix plain text passwords in the database by hashing them
require_once 'vendor/autoload.php';
require_once 'src/config/database.php';

$email = 'training@cyc.co.uk';

echo "Fixing password for user: $email\n";

try {
    // Get the user
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "Error: User not found with email: $email\n";
        exit(1);
    }
    
    $currentPassword = $user['password'];
    echo "Current password stored: " . $currentPassword . "\n";
    
    // Check if already hashed (bcrypt hashes start with $2)
    if (strpos($currentPassword, '$2') === 0) {
        echo "Password is already hashed. No action needed.\n";
        exit(0);
    }
    
    // Hash the plain text password
    $hashedPassword = password_hash($currentPassword, PASSWORD_DEFAULT);
    echo "Hashed password: " . $hashedPassword . "\n";
    
    // Update the database
    $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $updateStmt->bindValue(':id', $user['id']);
    $updateStmt->bindValue(':password', $hashedPassword);
    $updateStmt->execute();
    
    echo "✓ Password successfully hashed and updated in database!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
