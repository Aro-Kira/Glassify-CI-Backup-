<?php
/**
 * Create inventory_notifications table to track out of stock alerts for sales
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating inventory_notifications table...\n\n";
    
    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS `inventory_notifications` (
        `NotificationID` int(11) NOT NULL AUTO_INCREMENT,
        `InventoryItemID` int(11) NOT NULL,
        `ItemID` varchar(50) NOT NULL,
        `ItemName` varchar(255) NOT NULL,
        `Message` text NOT NULL,
        `Status` enum('Unread','Read','Resolved') DEFAULT 'Unread',
        `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`NotificationID`),
        KEY `InventoryItemID` (`InventoryItemID`),
        KEY `Status` (`Status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql);
    echo "✓ Created table: inventory_notifications\n";
    
    echo "\n✅ Inventory notifications table created successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

