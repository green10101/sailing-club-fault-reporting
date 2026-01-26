<?php
// This script creates an admin user
// Upload to PUBLIC folder and run it once, then delete it

require_once '../vendor/autoload.php';
require_once '../src/config/database.php';

$email = 'admin@sailingclub.com';
$password = 'admin123';
$name = 'Administrator';
$role = 'admin';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    
    if ($stmt->fetch()) {
        // Update existing user
        $stmt = $pdo->prepare("UPDATE users SET password = :password, name = :name, role = :role WHERE email = :email");
        $stmt->execute([
            ':password' => $hashedPassword,
            ':name' => $name,
            ':role' => $role,
            ':email' => $email
        ]);
        echo "Admin user updated successfully!<br>";
    } else {
        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name, role) VALUES (:email, :password, :name, :role)");
        $stmt->execute([
            ':email' => $email,
            ':password' => $hashedPassword,
            ':name' => $name,
            ':role' => $role
        ]);
        echo "Admin user created successfully!<br>";
    }
    
    echo "Email: admin@sailingclub.com<br>";
    echo "Password: admin123<br>";
    echo "<br>Please delete this file now for security!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
