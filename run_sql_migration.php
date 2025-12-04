<?php
/**
 * Run SQL migration to add order fields to customization table
 * Execute this file once: php run_sql_migration.php
 */

// Load CodeIgniter
define('BASEPATH', true);
require_once 'system/core/CodeIgniter.php';

$CI = &get_instance();
$CI->load->database();

echo "Starting database migration...\n\n";

// Read SQL file
$sql_file = 'add_order_fields_to_customization.sql';
if (!file_exists($sql_file)) {
    die("Error: SQL file not found: $sql_file\n");
}

$sql_content = file_get_contents($sql_file);

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue; // Skip empty lines and comments
    }
    
    try {
        $CI->db->query($statement);
        echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
    } catch (Exception $e) {
        // Check if error is because column already exists
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "⚠ Column already exists, skipping...\n";
        } else {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✓ Migration completed!\n";
echo "Fields added to customization table:\n";
echo "  - OrderID\n";
echo "  - ProductName\n";
echo "  - DeliveryAddress\n";
echo "  - OrderDate\n";
echo "  - TotalQuotation\n";

