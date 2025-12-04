<?php
/**
 * Remove the old customization table
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Removing old customization table...\n\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'customization'");
    if ($stmt->rowCount() > 0) {
        // Drop the table
        $pdo->exec("DROP TABLE `customization`");
        echo "✓ Dropped old customization table\n";
    } else {
        echo "- Old customization table does not exist\n";
    }
    
    echo "\n✅ Old customization table removal completed!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

