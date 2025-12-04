<?php
/**
 * Create system_activity_log table to track all system activities
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating system_activity_log table...\n\n";
    
    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS `system_activity_log` (
        `ActivityID` int(11) NOT NULL AUTO_INCREMENT,
        `Action` varchar(50) NOT NULL COMMENT 'Info, Success, Error, Warning',
        `Description` text NOT NULL,
        `Role` varchar(50) DEFAULT NULL COMMENT 'Client, Staff, Admin, System',
        `UserID` int(11) DEFAULT NULL COMMENT 'User who performed the action',
        `UserName` varchar(100) DEFAULT NULL COMMENT 'Name of the user',
        `Timestamp` datetime NOT NULL DEFAULT current_timestamp(),
        `RelatedID` int(11) DEFAULT NULL COMMENT 'Related OrderID, IssueID, etc.',
        `RelatedType` varchar(50) DEFAULT NULL COMMENT 'Order, Issue, Inventory, Payment, etc.',
        PRIMARY KEY (`ActivityID`),
        KEY `Timestamp` (`Timestamp`),
        KEY `Action` (`Action`),
        KEY `UserID` (`UserID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql);
    echo "✓ Created table: system_activity_log\n";
    
    // Add Priority column to issuereport if it doesn't exist
    echo "\nChecking issuereport table for Priority column...\n";
    $check_priority = $pdo->query("SHOW COLUMNS FROM `issuereport` LIKE 'Priority'");
    if ($check_priority->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `issuereport` ADD COLUMN `Priority` enum('Low','Medium','High') DEFAULT 'Medium' AFTER `Category`");
        echo "✓ Added Priority column to issuereport table\n";
    } else {
        echo "✓ Priority column already exists\n";
    }
    
    echo "\n✅ Setup completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

