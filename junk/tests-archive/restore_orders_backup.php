<?php
/**
 * Restore Orders from Backup
 * This script allows you to restore orders from a backup file
 */

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

// List available backups
$backups = glob($backup_dir . 'orders_backup_*.sql');
rsort($backups); // Most recent first

if (empty($backups)) {
    echo "<h2>No backup files found</h2>";
    echo "<p>No order backups available in: {$backup_dir}</p>";
    exit;
}

// If a backup is selected for restoration
if (isset($_GET['restore'])) {
    $backup_file = $_GET['restore'];
    
    if (!file_exists($backup_file)) {
        die("Backup file not found: {$backup_file}");
    }
    
    if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
        echo "<!DOCTYPE html><html><head><title>Restore Orders</title></head><body>";
        echo "<h1>⚠️ Restore Orders - Confirmation Required</h1>";
        echo "<p>This will restore orders from: <strong>" . basename($backup_file) . "</strong></p>";
        echo "<p><strong>Warning:</strong> This will delete current orders and replace them with the backup.</p>";
        echo "<p><a href='?restore=" . urlencode($backup_file) . "&confirm=yes' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Yes, Restore</a> ";
        echo "<a href='restore_orders_backup.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Cancel</a></p>";
        echo "</body></html>";
        exit;
    }
    
    echo "<h2>Restoring Orders...</h2>\n";
    echo "<pre>\n";
    
    // Clear existing data
    $check_table = $conn->query("SHOW TABLES LIKE 'order_items'");
    if ($check_table->num_rows > 0) {
        $conn->query("TRUNCATE TABLE `order_items`");
        echo "✓ Cleared existing order_items\n";
    }
    $conn->query("TRUNCATE TABLE `order`");
    echo "✓ Cleared existing orders\n";
    
    // Read and execute SQL file
    $sql = file_get_contents($backup_file);
    $statements = explode(";\n", $sql);
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) continue;
        
        if ($conn->query($statement)) {
            $success++;
        } else {
            $errors++;
            echo "Error executing: " . substr($statement, 0, 100) . "...\n";
        }
    }
    
    // Get counts
    $orders_result = $conn->query("SELECT COUNT(*) as count FROM `order`");
    $orders_count = $orders_result->fetch_assoc()['count'];
    
    $items_count = 0;
    if ($check_table->num_rows > 0) {
        $items_result = $conn->query("SELECT COUNT(*) as count FROM `order_items`");
        $items_count = $items_result->fetch_assoc()['count'];
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "RESTORATION COMPLETE\n";
    echo str_repeat("=", 50) . "\n";
    echo "Statements executed: {$success}\n";
    echo "Errors: {$errors}\n";
    echo "Orders restored: {$orders_count}\n";
    if ($check_table->num_rows > 0) {
        echo "Order items restored: {$items_count}\n";
    }
    echo "</pre>\n";
    echo "<p><a href='admin-orders' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Go to Orders Page</a></p>";
    $conn->close();
    exit;
}

// List backups
echo "<!DOCTYPE html><html><head><title>Restore Orders</title>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:12px;text-align:left;} th{background:#02455F;color:white;}</style>";
echo "</head><body>";
echo "<h1>Restore Orders from Backup</h1>";
echo "<p>Select a backup to restore:</p>";
echo "<table>";
echo "<tr><th>Backup File</th><th>Date</th><th>Size</th><th>Action</th></tr>";

foreach ($backups as $backup) {
    $filename = basename($backup);
    $size = filesize($backup);
    $date = date('Y-m-d H:i:s', filemtime($backup));
    
    echo "<tr>";
    echo "<td>{$filename}</td>";
    echo "<td>{$date}</td>";
    echo "<td>" . number_format($size) . " bytes</td>";
    echo "<td><a href='?restore=" . urlencode($backup) . "' style='background:#007bff;color:white;padding:6px 12px;text-decoration:none;border-radius:4px;'>Restore</a></td>";
    echo "</tr>";
}

echo "</table>";
echo "<p><a href='admin-orders' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Back to Orders</a></p>";
echo "</body></html>";
