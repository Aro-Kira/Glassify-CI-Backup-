<?php
/**
 * Update Product Schema for 4 Categories
 * This script updates the database to support the exact product categories and their fields
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting database schema update...\n\n";
    
    // 1. Update customization table
    echo "1. Updating customization table...\n";
    try {
        $pdo->exec("ALTER TABLE `customization` ADD COLUMN `LEDBacklight` VARCHAR(50) DEFAULT NULL COMMENT 'LED Backlight option for Mirrors'");
        echo "   - Added LEDBacklight column\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            echo "   - Error adding LEDBacklight: " . $e->getMessage() . "\n";
        } else {
            echo "   - LEDBacklight column already exists\n";
        }
    }
    
    try {
        $pdo->exec("ALTER TABLE `customization` ADD COLUMN `DoorOperation` VARCHAR(50) DEFAULT NULL COMMENT 'Door Operation: Swing, Sliding, Fixed for Shower Enclosure/Partition'");
        echo "   - Added DoorOperation column\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            echo "   - Error adding DoorOperation: " . $e->getMessage() . "\n";
        } else {
            echo "   - DoorOperation column already exists\n";
        }
    }
    
    try {
        $pdo->exec("ALTER TABLE `customization` ADD COLUMN `Configuration` VARCHAR(50) DEFAULT NULL COMMENT 'Configuration: 2-panel slider, 3-panel slider, 4-panel slider for Aluminum Doors'");
        echo "   - Added Configuration column\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            echo "   - Error adding Configuration: " . $e->getMessage() . "\n";
        } else {
            echo "   - Configuration column already exists\n";
        }
    }
    
    // 2. Update product table Category field
    echo "\n2. Updating product table...\n";
    try {
        $pdo->exec("ALTER TABLE `product` MODIFY COLUMN `Category` VARCHAR(100) NOT NULL COMMENT 'Product Category: Mirrors, Shower Enclosure / Partition, Aluminum Doors, Aluminum and Bathroom Doors'");
        echo "   - Updated Category column to support longer category names\n";
    } catch (PDOException $e) {
        echo "   - Error updating Category column: " . $e->getMessage() . "\n";
    }
    
    // 3. Update order_page table
    $tables_to_update = [
        'order_page',
        'pending_review_orders',
        'awaiting_admin_orders',
        'ready_to_approve_orders',
        'approved_orders',
        'disapproved_orders'
    ];
    
    echo "\n3. Updating order status tables...\n";
    foreach ($tables_to_update as $table) {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "   - Updating $table...\n";
            
            $columns = ['LEDBacklight', 'DoorOperation', 'Configuration'];
            foreach ($columns as $column) {
                try {
                    $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` VARCHAR(50) DEFAULT NULL");
                    echo "     - Added $column column\n";
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                        echo "     - Error adding $column: " . $e->getMessage() . "\n";
                    } else {
                        echo "     - $column column already exists\n";
                    }
                }
            }
        } else {
            echo "   - Table $table does not exist, skipping...\n";
        }
    }
    
    echo "\n✅ Database schema update completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Update product categories in the product table to match exactly:\n";
    echo "   - Mirrors\n";
    echo "   - Shower Enclosure / Partition\n";
    echo "   - Aluminum Doors\n";
    echo "   - Aluminum and Bathroom Doors\n";
    echo "2. Update all sales pages to display product data from database\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

