<?php
require_once 'vendor/autoload.php';
require_once 'src/config/database.php';

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "Admin user exists:\n";
        echo "Username: " . $admin['username'] . "\n";
        echo "Name: " . ($admin['name'] ?? 'not set') . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Password: " . $admin['password'] . "\n";
        echo "Role: " . ($admin['role'] ?? 'not set') . "\n";
    } else {
        echo "Admin user does not exist. Creating...\n";
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, name, email, password, role) VALUES ('admin', 'Administrator', 'admin@example.com', :password, 'admin')");
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->execute();
        echo "Admin user created successfully!\n";
        echo "Username: admin\n";
        echo "Name: Administrator\n";
        echo "Email: admin@example.com\n";
        echo "Password: admin123 (hashed)\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
