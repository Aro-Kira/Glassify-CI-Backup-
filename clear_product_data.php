<?php
/**
 * Clear all product-related data from database tables
 * This script deletes all data from tables that contain product details
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting data cleanup...\n\n";
    
    // List of tables to clear (in order to respect foreign key constraints)
    $tables_to_clear = [
        'disapproved_orders',
        'approved_orders',
        'ready_to_approve_orders',
        'awaiting_admin_orders',
        'pending_review_orders',
        'order_page',
        'payment',
        'order',
        'cart',
        'customization',
        'product'
    ];
    
    // Disable foreign key checks temporarily
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    foreach ($tables_to_clear as $table) {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            try {
                $pdo->exec("TRUNCATE TABLE `$table`");
                echo "✓ Cleared table: $table\n";
            } catch (PDOException $e) {
                // If TRUNCATE fails, try DELETE
                try {
                    $pdo->exec("DELETE FROM `$table`");
                    echo "✓ Cleared table: $table (using DELETE)\n";
                } catch (PDOException $e2) {
                    echo "✗ Error clearing $table: " . $e2->getMessage() . "\n";
                }
            }
        } else {
            echo "- Table $table does not exist, skipping...\n";
        }
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Reset AUTO_INCREMENT for product table
    try {
        $pdo->exec("ALTER TABLE `product` AUTO_INCREMENT = 1");
        echo "\n✓ Reset AUTO_INCREMENT for product table\n";
    } catch (PDOException $e) {
        echo "\n- Could not reset AUTO_INCREMENT: " . $e->getMessage() . "\n";
    }
    
    // Reset AUTO_INCREMENT for customization table
    try {
        $pdo->exec("ALTER TABLE `customization` AUTO_INCREMENT = 1");
        echo "✓ Reset AUTO_INCREMENT for customization table\n";
    } catch (PDOException $e) {
        echo "- Could not reset AUTO_INCREMENT: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Data cleanup completed successfully!\n";
    echo "\nNext: Run insert_product_data.php to insert the 4 product categories.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

