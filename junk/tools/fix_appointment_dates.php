<?php
/**
 * Fix mismatched appointment dates to use customer's preferred dates
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

// Find mismatched appointments
$query = "
    SELECT a.AppointmentID, a.OrderID, a.AppointmentDate, o.PreferredInstallationDate
    FROM appointments a
    JOIN `order` o ON a.OrderID = o.OrderID
    WHERE (a.Service = 'Ocular Visit' OR a.AppointmentType = 'Ocular')
    AND o.PreferredInstallationDate IS NOT NULL
    AND DATE(a.AppointmentDate) != DATE(o.PreferredInstallationDate)
";

$result = $mysqli->query($query);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}

$count = 0;

echo "\n";
echo "=== FIXING MISMATCHED APPOINTMENT DATES ===\n";
echo str_repeat('-', 70) . "\n";

while ($row = $result->fetch_object()) {
    $new_date = date('Y-m-d', strtotime($row->PreferredInstallationDate));
    $old_date = date('Y-m-d', strtotime($row->AppointmentDate));
    
    $update = "UPDATE appointments SET AppointmentDate = '" . $mysqli->real_escape_string($new_date) . "' WHERE AppointmentID = " . (int)$row->AppointmentID;
    
    if ($mysqli->query($update)) {
        echo "Appointment #" . $row->AppointmentID . " (Order #" . $row->OrderID . "): " . $old_date . " -> " . $new_date . " [UPDATED]\n";
        $count++;
    } else {
        echo "Appointment #" . $row->AppointmentID . ": FAILED - " . $mysqli->error . "\n";
    }
}

echo str_repeat('-', 70) . "\n";
echo "\nTotal updated: $count appointments\n\n";

if ($count > 0) {
    echo "SUCCESS: All mismatched appointments now use the customer's preferred dates.\n";
} else {
    echo "No mismatched appointments found.\n";
}

$mysqli->close();
