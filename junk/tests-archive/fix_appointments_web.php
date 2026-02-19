<?php
/**
 * Fix mismatched appointment dates to use customer's preferred dates
 * Web-accessible version
 */

header('Content-Type: text/plain');

// Bootstrap CodeIgniter
$system_path = 'system';
$application_folder = 'application';

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

// Find mismatched appointments and fix them
$query = "
    SELECT a.AppointmentID, a.OrderID, a.AppointmentDate, a.AppointmentTime, o.PreferredInstallationDate
    FROM appointments a
    JOIN `order` o ON a.OrderID = o.OrderID
    WHERE (a.Service = 'Ocular Visit' OR a.AppointmentType = 'Ocular')
    AND o.PreferredInstallationDate IS NOT NULL
    AND (DATE(a.AppointmentDate) != DATE(o.PreferredInstallationDate) OR a.AppointmentTime IS NULL OR a.AppointmentTime = '' OR a.AppointmentTime = '00:00:00')
";

$result = $mysqli->query($query);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}

$count = 0;

echo "=== FIXING APPOINTMENT DATES AND TIMES ===\n";
echo str_repeat('-', 80) . "\n";

while ($row = $result->fetch_object()) {
    $new_date = date('Y-m-d', strtotime($row->PreferredInstallationDate));
    $old_date = date('Y-m-d', strtotime($row->AppointmentDate));
    $old_time = $row->AppointmentTime ?: 'NULL';
    $new_time = '10:00:00'; // Default to 10:00 AM
    
    $update = "UPDATE appointments SET AppointmentDate = '" . $mysqli->real_escape_string($new_date) . "', AppointmentTime = '" . $new_time . "' WHERE AppointmentID = " . (int)$row->AppointmentID;
    
    if ($mysqli->query($update)) {
        echo "Appointment #" . $row->AppointmentID . " (Order #" . $row->OrderID . "):\n";
        echo "  Date: " . $old_date . " -> " . $new_date . "\n";
        echo "  Time: " . $old_time . " -> " . $new_time . " [UPDATED]\n";
        $count++;
    } else {
        echo "Appointment #" . $row->AppointmentID . ": FAILED - " . $mysqli->error . "\n";
    }
}

echo str_repeat('-', 80) . "\n";
echo "\nTotal updated: $count appointments\n\n";

if ($count > 0) {
    echo "SUCCESS: All appointments now use customer's preferred dates with 10:00 AM default time.\n";
} else {
    echo "All appointments already have correct dates and times.\n";
}

$mysqli->close();
