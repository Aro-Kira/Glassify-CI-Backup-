<?php
/**
 * Check if appointment dates match customer's preferred ocular visit dates
 */

// Bootstrap CodeIgniter
$system_path = '../system';
$application_folder = '../application';

define('BASEPATH', str_replace('\\', '/', $system_path) . '/');
define('APPPATH', $application_folder . '/');
define('ENVIRONMENT', 'development');

require_once APPPATH . 'config/database.php';

// Connect to database
$mysqli = new mysqli(
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'],
    $db['default']['database']
);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "
    SELECT 
        a.AppointmentID,
        a.OrderID,
        a.AppointmentDate,
        a.AppointmentTime,
        o.PreferredInstallationDate,
        o.OrderDate,
        CONCAT(u.First_Name, ' ', u.Last_Name) as CustomerName
    FROM appointments a
    LEFT JOIN `order` o ON a.OrderID = o.OrderID
    LEFT JOIN customer c ON o.Customer_ID = c.Customer_ID
    LEFT JOIN user u ON c.UserID = u.UserID
    WHERE a.Service = 'Ocular Visit' OR a.AppointmentType = 'Ocular'
    ORDER BY a.AppointmentID DESC
    LIMIT 20
";

$result = $mysqli->query($query);

echo "\n";
echo "=== APPOINTMENT DATE vs CUSTOMER PREFERRED OCULAR VISIT DATE ===\n";
echo str_repeat('-', 105) . "\n";
echo sprintf("%-6s | %-8s | %-12s | %-12s | %-25s | %s\n", 'AptID', 'OrderID', 'Apt Date', 'Pref Date', 'Customer', 'Match?');
echo str_repeat('-', 105) . "\n";

$match_count = 0;
$no_match_count = 0;
$na_count = 0;

while ($row = $result->fetch_object()) {
    $apt_date = $row->AppointmentDate ? date('Y-m-d', strtotime($row->AppointmentDate)) : 'NULL';
    $pref_date = $row->PreferredInstallationDate ? date('Y-m-d', strtotime($row->PreferredInstallationDate)) : 'NULL';
    
    if ($pref_date === 'NULL') {
        $match = 'N/A (no pref)';
        $na_count++;
    } elseif ($apt_date === $pref_date) {
        $match = 'YES';
        $match_count++;
    } else {
        $match = 'NO';
        $no_match_count++;
    }
    
    $customer_name = $row->CustomerName ?: 'N/A';
    if (strlen($customer_name) > 25) {
        $customer_name = substr($customer_name, 0, 22) . '...';
    }
    
    echo sprintf("%-6s | %-8s | %-12s | %-12s | %-25s | %s\n", 
        $row->AppointmentID,
        $row->OrderID,
        $apt_date,
        $pref_date,
        $customer_name,
        $match
    );
}

echo str_repeat('-', 105) . "\n";
echo "\nSUMMARY:\n";
echo "  - Matching dates: $match_count\n";
echo "  - Non-matching dates: $no_match_count\n";
echo "  - No preferred date set: $na_count\n";
echo "\n";

if ($no_match_count > 0) {
    echo "NOTE: Non-matching dates may be from orders approved before the fix was applied.\n";
    echo "New orders approved after the fix will use the customer's preferred date.\n";
}

$mysqli->close();
