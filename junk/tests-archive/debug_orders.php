<?php
header('Content-Type: text/plain');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$system_path = 'system';
$application_folder = 'application';
define('BASEPATH', str_replace('\\', '/', $system_path) . '/');
define('APPPATH', $application_folder . '/');
define('ENVIRONMENT', 'development');
require_once APPPATH . 'config/database.php';

$mysqli = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

echo "=== CHECKING ORDER TABLE STRUCTURE AND DATA ===\n\n";

// Check if PreferredInstallationDate column exists
$result = $mysqli->query("SHOW COLUMNS FROM `order` LIKE 'PreferredInstallationDate'");
if ($result->num_rows > 0) {
    echo "✓ PreferredInstallationDate column EXISTS in order table\n\n";
} else {
    echo "✗ PreferredInstallationDate column DOES NOT EXIST in order table\n\n";
}

// Show all orders with their PreferredInstallationDate
echo "=== ORDERS WITH PREFERRED DATE ===\n";
echo str_repeat('-', 80) . "\n";

$result = $mysqli->query("SELECT OrderID, OrderNumber, Status, PreferredInstallationDate, SpecialInstructions FROM `order` ORDER BY OrderID DESC LIMIT 10");

while ($row = $result->fetch_object()) {
    echo "Order #" . $row->OrderID . " (" . $row->OrderNumber . ")\n";
    echo "  Status: " . $row->Status . "\n";
    echo "  PreferredInstallationDate: " . ($row->PreferredInstallationDate ?: 'NULL') . "\n";
    
    // Check SpecialInstructions for preferred_ocular_date
    if ($row->SpecialInstructions) {
        $json = json_decode($row->SpecialInstructions, true);
        if ($json && isset($json['preferred_ocular_date'])) {
            echo "  SpecialInstructions->preferred_ocular_date: " . $json['preferred_ocular_date'] . "\n";
        }
    }
    echo "\n";
}

echo str_repeat('-', 80) . "\n";

$mysqli->close();
