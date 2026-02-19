<?php
/**
 * Clear Orders Script
 * Removes all orders and related data from the database
 * 
 * Usage: 
 *   Browser: http://localhost/Glassify-CI/tools/clear_orders.php?confirm=yes
 *   CLI: php clear_orders.php --confirm
 */

// Security check - require confirmation and an explicit proceed flag
$confirmed = false;
$proceed = false;
if (php_sapi_name() === 'cli') {
    $confirmed = in_array('--confirm', $argv ?? []);
    $proceed = in_array('--proceed', $argv ?? []);
} else {
    $confirmed = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';
    $proceed = isset($_GET['proceed']) && $_GET['proceed'] === 'yes';
}

if (!$confirmed) {
    $usage = php_sapi_name() === 'cli'
        ? "Usage: php clear_orders.php --confirm --proceed"
        : "Usage: Add ?confirm=yes&proceed=yes to the URL to execute";

    echo "<pre>";
    echo "===========================================\n";
    echo "       ORDER CLEANUP SCRIPT\n";
    echo "===========================================\n\n";
    echo "This script will DELETE ALL ORDERS and related data:\n";
    echo "  - Orders\n";
    echo "  - Order Items\n";
    echo "  - Payments\n";
    echo "  - Appointments\n";
    echo "  - Notifications\n";
    echo "  - Activity Logs (order-related)\n\n";
    echo "WARNING: This action CANNOT be undone!\n\n";
    echo "$usage\n";
    echo "</pre>";
    exit;
}

// Load CodeIgniter paths to retrieve DB config
$system_path = '../system';
$application_folder = '../application';

define('BASEPATH', str_replace('\\', '/', realpath($system_path)) . '/');
define('APPPATH', str_replace('\\', '/', realpath($application_folder)) . '/');
define('ENVIRONMENT', 'development');

// Load database config
require_once APPPATH . 'config/database.php';

// Connect to database
$conn = new mysqli(
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'],
    $db['default']['database']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tables/queries we intend to clear (key => statement)
$tables_to_clear = [
    'notifications' => "DELETE FROM notifications WHERE RelatedType = 'Order' OR Message LIKE '%order%'",
    'system_activity_log' => "DELETE FROM system_activity_log WHERE RelatedType = 'Order' OR Action LIKE '%Order%'",
    'payment' => "TRUNCATE TABLE payment",
    'appointments' => "TRUNCATE TABLE appointments",
    'orderitem' => "TRUNCATE TABLE orderitem",
    'order_items' => "TRUNCATE TABLE order_items",
    'order' => "TRUNCATE TABLE `order`",
    'awaiting_admin_orders' => "TRUNCATE TABLE awaiting_admin_orders",
];

// If confirmed but not proceeded, list detected tables and counts and request final confirmation
if ($confirmed && !$proceed) {
    echo "<pre>";
    echo "===========================================\n";
    echo "       ORDER CLEANUP - PREVIEW\n";
    echo "===========================================\n\n";
    echo "Detected the following order-related tables and row counts (only showing existing tables):\n\n";

    foreach ($tables_to_clear as $table => $query) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if ($check && $check->num_rows > 0) {
            $count_result = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
            $count = $count_result ? $count_result->fetch_assoc()['cnt'] : 'unknown';
            echo sprintf("- %s : %s rows\n", $table, $count);
        }
    }

    // Additional optional tables
    $optional_tables = ['cart', 'installation_date_change_requests'];
    foreach ($optional_tables as $ot) {
        $check = $conn->query("SHOW TABLES LIKE '$ot'");
        if ($check && $check->num_rows > 0) {
            $count_result = $conn->query("SELECT COUNT(*) as cnt FROM `$ot`");
            $count = $count_result ? $count_result->fetch_assoc()['cnt'] : 'unknown';
            echo sprintf("- %s : %s rows\n", $ot, $count);
        }
    }

    echo "\nThis is a PREVIEW only. To actually perform the cleanup:\n";
    if (php_sapi_name() === 'cli') {
        echo "  Re-run: php clear_orders.php --confirm --proceed\n";
    } else {
        $self = $_SERVER['PHP_SELF'];
        $url = $self . '?confirm=yes&proceed=yes';
        echo "  Open in browser: $url\n";
    }

    echo "\nWARNING: This action CANNOT be undone. Make sure you have a database backup.\n";
    echo "</pre>";
    $conn->close();
    exit;
}

// If here, $proceed is true and we will perform the destructive operations
echo "<pre>";
echo "===========================================\n";
echo "       CLEARING ORDERS DATA\n";
echo "===========================================\n\n";

// Disable foreign key checks temporarily
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
echo "✓ Disabled foreign key checks\n\n";

// Check which tables exist and clear them
foreach ($tables_to_clear as $table => $query) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows > 0) {
        // Get count before
        $count_result = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
        $count = $count_result ? $count_result->fetch_assoc()['cnt'] : 0;
        
        // Execute delete/truncate
        if ($conn->query($query)) {
            echo "✓ Cleared '$table' ($count records)\n";
        } else {
            echo "✗ Error clearing '$table': " . $conn->error . "\n";
        }
    } else {
        echo "- Skipped '$table' (table doesn't exist)\n";
    }
}

// Also clear cart items if they exist
$check = $conn->query("SHOW TABLES LIKE 'cart'");
if ($check && $check->num_rows > 0) {
    $count_result = $conn->query("SELECT COUNT(*) as cnt FROM cart");
    $count = $count_result ? $count_result->fetch_assoc()['cnt'] : 0;
    if ($conn->query("TRUNCATE TABLE cart")) {
        echo "✓ Cleared 'cart' ($count records)\n";
    }
}

// Clear installation_date_change_requests if exists
$check = $conn->query("SHOW TABLES LIKE 'installation_date_change_requests'");
if ($check && $check->num_rows > 0) {
    $count_result = $conn->query("SELECT COUNT(*) as cnt FROM installation_date_change_requests");
    $count = $count_result ? $count_result->fetch_assoc()['cnt'] : 0;
    if ($conn->query("TRUNCATE TABLE installation_date_change_requests")) {
        echo "✓ Cleared 'installation_date_change_requests' ($count records)\n";
    }
}

// Reset auto-increment on order table
$conn->query("ALTER TABLE `order` AUTO_INCREMENT = 1");
echo "\n✓ Reset order auto-increment to 1\n";

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "✓ Re-enabled foreign key checks\n";

$conn->close();

echo "\n===========================================\n";
echo "       CLEANUP COMPLETE!\n";
echo "===========================================\n";
echo "</pre>";
?>
