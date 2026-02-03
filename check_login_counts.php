<?php
require 'src/config/database.php';

echo "Checking login counts for all users...\n\n";

$stmt = $pdo->query("SELECT id, name, email, login_count FROM users ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Login Count: {$user['login_count']}\n";
}

echo "\n\nSpecifically for Chris Hodge (ID 5) and Nick Colbourne (ID 8):\n";
$stmt = $pdo->prepare("SELECT id, name, email, login_count FROM users WHERE id IN (5, 8)");
$stmt->execute();
$targetUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($targetUsers as $user) {
    echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Login Count: {$user['login_count']}\n";
}
?>
