<?php
/**
 * Check if the reports table has all required columns
 * Run this to verify migration 010 has been applied
 */

require_once __DIR__ . '/src/config/database.php';

try {
    echo "Checking columns in reports table...\n\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM reports");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredColumns = ['bosun_assessment', 'part_required', 'part_status', 'completion_date'];
    $foundColumns = array_column($columns, 'Field');
    
    echo "All columns in reports table:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    
    echo "\n\nChecking required columns:\n";
    foreach ($requiredColumns as $col) {
        if (in_array($col, $foundColumns)) {
            echo "  ✓ $col - EXISTS\n";
        } else {
            echo "  ✗ $col - MISSING!\n";
        }
    }
    
    $allFound = true;
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $foundColumns)) {
            $allFound = false;
            break;
        }
    }
    
    if (!$allFound) {
        echo "\n⚠️  MIGRATION NEEDED!\n";
        echo "Run migration 010_add_bosun_assessment_fields.sql on your Hostinger database.\n";
        echo "SQL to run:\n\n";
        echo file_get_contents(__DIR__ . '/migrations/010_add_bosun_assessment_fields.sql');
    } else {
        echo "\n✓ All required columns exist!\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
