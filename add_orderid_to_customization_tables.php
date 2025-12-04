<?php
/**
 * Add OrderID column to all category-specific customization tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Adding OrderID column to category-specific customization tables...\n\n";
    
    $tables = [
        'mirror_customization',
        'shower_enclosure_customization',
        'aluminum_doors_customization',
        'aluminum_bathroom_doors_customization'
    ];
    
    foreach ($tables as $table) {
        // Check if OrderID column already exists
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'OrderID'");
        if ($stmt->rowCount() > 0) {
            echo "- OrderID column already exists in $table\n";
        } else {
            // Add OrderID column
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `OrderID` VARCHAR(50) DEFAULT NULL AFTER `Product_ID`");
            echo "✓ Added OrderID column to $table\n";
        }
    }
    
    echo "\n✅ OrderID columns added successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

