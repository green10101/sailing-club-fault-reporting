<?php
require_once 'vendor/autoload.php';

$host = 'localhost';
$db = 'sailing_club';
$user = 'root';
$pass = '';

try {
    // Connect without specifying database first
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Select the database
    $pdo->exec("USE `$db`");

    // Run migrations only if boats table does not exist
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'boats'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        $migrations = [
            'migrations/004_create_boats_table.sql',
            'migrations/005_update_reports_add_boat_id.sql',
            'migrations/006_add_unique_boat_name.sql'
        ];

        foreach ($migrations as $migration) {
            if (file_exists($migration)) {
                $sql = file_get_contents($migration);
                $pdo->exec($sql);
                echo "Executed $migration\n";
            } else {
                echo "Migration file $migration not found\n";
            }
        }
    }

    // Skip duplicate cleanup to avoid foreign key constraint issues

    // Insert some sample boats
    $sampleBoats = [
        ['boat_name' => 'Sailboat Alpha', 'boat_type' => 'Sailboat', 'serial_number' => 'SB001'],
        ['boat_name' => 'Motorboat Beta', 'boat_type' => 'Motorboat', 'serial_number' => 'MB001'],
        ['boat_name' => 'Dinghy Gamma', 'boat_type' => 'Dinghy', 'serial_number' => 'DG001'],
    ];

    foreach ($sampleBoats as $boat) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO boats (boat_name, boat_type, serial_number) VALUES (:name, :type, :serial)");
        $stmt->execute([
            ':name' => $boat['boat_name'],
            ':type' => $boat['boat_type'],
            ':serial' => $boat['serial_number']
        ]);
    }

    // Ensure 'Retired' is part of the status ENUM
    $colStmt = $pdo->query("SHOW COLUMNS FROM boats LIKE 'status'");
    $col = $colStmt->fetch(PDO::FETCH_ASSOC);
    if ($col && strpos($col['Type'], "Retired") === false) {
        $pdo->exec("ALTER TABLE boats MODIFY COLUMN status ENUM('OK','Minor Faults','Out of Operation','Retired') DEFAULT 'OK'");
        echo "Updated boats.status ENUM to include 'Retired'\n";
    }

    // Add role column to users table if it doesn't exist
    $colStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($colStmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'bosun') DEFAULT 'bosun' AFTER email");
        echo "Added role column to users table\n";
    }

    // Add name and email columns to users table if they don't exist
    $nameColStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'name'");
    if ($nameColStmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN name VARCHAR(255) NOT NULL DEFAULT '' AFTER username");
        echo "Added name column to users table\n";
    }
    
    $emailColStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    $emailCol = $emailColStmt->fetch(PDO::FETCH_ASSOC);
    // Check if email column exists, if not add it
    if (!$emailCol) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(255) NOT NULL DEFAULT '' AFTER name");
        echo "Added email column to users table\n";
    }

    // Add reporter_name and reporter_email columns to reports table if they don't exist
    $reporterNameColStmt = $pdo->query("SHOW COLUMNS FROM reports LIKE 'reporter_name'");
    if ($reporterNameColStmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE reports ADD COLUMN reporter_name VARCHAR(255) NOT NULL DEFAULT '' AFTER fault_description");
        echo "Added reporter_name column to reports table\n";
    }
    
    $reporterEmailColStmt = $pdo->query("SHOW COLUMNS FROM reports LIKE 'reporter_email'");
    if ($reporterEmailColStmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE reports ADD COLUMN reporter_email VARCHAR(255) NOT NULL DEFAULT '' AFTER reporter_name");
        echo "Added reporter_email column to reports table\n";
    }

    echo "Sample boats inserted.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>