<?php
/**
 * Safe Order Deletion Script
 * This script backs up orders before deleting them
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli') {
    // Allow browser access but require confirmation
    if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
        echo "<!DOCTYPE html><html><head><title>Delete Orders</title></head><body>";
        echo "<h1>⚠️ Delete Orders - Confirmation Required</h1>";
        echo "<p>This will:</p>";
        echo "<ul>";
        echo "<li>Backup all orders to a SQL file</li>";
        echo "<li>Delete all records from the 'order' table</li>";
        echo "<li>Delete all related order_items records</li>";
        echo "</ul>";
        echo "<p><strong>Are you sure you want to proceed?</strong></p>";
        echo "<p><a href='?confirm=yes' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Yes, Delete Orders</a> ";
        echo "<a href='admin-orders' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Cancel</a></p>";
        echo "</body></html>";
        exit;
    }
}

// Database configuration (from application/config/database.php)
$db_config = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'latest_glassifydb'
];

// Connect to database
$conn = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$backup_dir = __DIR__ . '/database/backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

$timestamp = date('Y-m-d_His');
$backup_file = $backup_dir . 'orders_backup_' . $timestamp . '.sql';

echo "<!DOCTYPE html><html><head><title>Delete Orders</title></head><body>";
echo "<h2>Starting Safe Order Deletion Process...</h2>\n";
echo "<pre>\n";

// Step 1: Backup orders table
echo "Step 1: Backing up orders table...\n";
$result = $conn->query("SELECT * FROM `order`");
$order_count = $result->num_rows;
echo "Found {$order_count} orders to backup\n";

$sql_content = "-- Orders Backup Created: " . date('Y-m-d H:i:s') . "\n";
$sql_content .= "-- Total Orders: {$order_count}\n\n";

if ($order_count > 0) {
    $sql_content .= "-- Backup of 'order' table\n";
    while ($order = $result->fetch_assoc()) {
        $columns = array_keys($order);
        $values = array_map(function($val) use ($conn) {
            return $val === null ? 'NULL' : "'" . $conn->real_escape_string($val) . "'";
        }, array_values($order));
        
        $sql_content .= "INSERT INTO `order` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
    }
}

// Step 2: Backup order_items if table exists
$check_table = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($check_table->num_rows > 0) {
    echo "\nStep 2: Backing up order_items table...\n";
    $result = $conn->query("SELECT * FROM `order_items`");
    $items_count = $result->num_rows;
    echo "Found {$items_count} order items to backup\n";
    
    if ($items_count > 0) {
        $sql_content .= "\n-- Backup of 'order_items' table\n";
        while ($item = $result->fetch_assoc()) {
            $columns = array_keys($item);
            $values = array_map(function($val) use ($conn) {
                return $val === null ? 'NULL' : "'" . $conn->real_escape_string($val) . "'";
            }, array_values($item));
            
            $sql_content .= "INSERT INTO `order_items` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
        }
    }
} else {
    $items_count = 0;
}

// Write backup file
file_put_contents($backup_file, $sql_content);
echo "\n✓ Backup saved to: {$backup_file}\n";
echo "Backup file size: " . number_format(filesize($backup_file)) . " bytes\n";

// Step 3: Delete order_items first (foreign key constraint)
if ($check_table->num_rows > 0) {
    echo "\nStep 3: Deleting order_items...\n";
    $conn->query("TRUNCATE TABLE `order_items`");
    echo "✓ Order items deleted\n";
}

// Step 4: Delete orders
echo "\nStep 4: Deleting orders...\n";
$conn->query("TRUNCATE TABLE `order`");
echo "✓ Orders deleted\n";

// Step 5: Verify deletion
$remaining_result = $conn->query("SELECT COUNT(*) as count FROM `order`");
$remaining_orders = $remaining_result->fetch_assoc()['count'];

$remaining_items = 0;
if ($check_table->num_rows > 0) {
    $remaining_items_result = $conn->query("SELECT COUNT(*) as count FROM `order_items`");
    $remaining_items = $remaining_items_result->fetch_assoc()['count'];
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "DELETION COMPLETE\n";
echo str_repeat("=", 50) . "\n";
echo "Orders deleted: {$order_count}\n";
if ($check_table->num_rows > 0) {
    echo "Order items deleted: {$items_count}\n";
}
echo "Remaining orders: {$remaining_orders}\n";
echo "Remaining order items: {$remaining_items}\n";
echo "\nBackup location: {$backup_file}\n";
echo "\n✓ All orders have been safely deleted!\n";
echo "✓ Backup file created for restoration if needed\n";
echo "</pre>\n";

echo "<p><a href='admin-orders' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Go to Orders Page</a></p>";
echo "</body></html>";

$conn->close();
