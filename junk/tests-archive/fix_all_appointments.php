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

// Find and fix ALL mismatched appointments
$query = "
    SELECT a.AppointmentID, a.OrderID, a.AppointmentDate, a.AppointmentTime, o.PreferredInstallationDate
    FROM appointments a
    JOIN `order` o ON a.OrderID = o.OrderID
    WHERE (a.Service = 'Ocular Visit' OR a.AppointmentType = 'Ocular')
    AND o.PreferredInstallationDate IS NOT NULL
    AND (DATE(a.AppointmentDate) != DATE(o.PreferredInstallationDate) OR a.AppointmentTime IS NULL OR a.AppointmentTime = '' OR a.AppointmentTime = '00:00:00')
";

$result = $mysqli->query($query);

echo "=== FIXING ALL MISMATCHED APPOINTMENT DATES ===\n";
echo str_repeat('-', 80) . "\n";

$count = 0;
while ($row = $result->fetch_object()) {
    $new_date = date('Y-m-d', strtotime($row->PreferredInstallationDate));
    $old_date = date('Y-m-d', strtotime($row->AppointmentDate));
    $new_time = '10:00:00';
    
    $update = "UPDATE appointments SET AppointmentDate = '" . $mysqli->real_escape_string($new_date) . "', AppointmentTime = '" . $new_time . "' WHERE AppointmentID = " . (int)$row->AppointmentID;
    
    if ($mysqli->query($update)) {
        echo "Appointment #" . $row->AppointmentID . " (Order #" . $row->OrderID . "): " . $old_date . " -> " . $new_date . " @ " . $new_time . " [FIXED]\n";
        $count++;
    } else {
        echo "Appointment #" . $row->AppointmentID . ": FAILED - " . $mysqli->error . "\n";
    }
}

echo str_repeat('-', 80) . "\n";
echo "\nFixed: $count appointments\n\n";

// Verify all appointments now
echo "=== VERIFICATION - ALL APPOINTMENTS ===\n";
echo str_repeat('-', 90) . "\n";
echo sprintf("%-6s | %-8s | %-12s | %-10s | %-12s | %s\n", 'AptID', 'OrderID', 'Apt Date', 'Apt Time', 'Pref Date', 'Match?');
echo str_repeat('-', 90) . "\n";

$result = $mysqli->query("
    SELECT a.AppointmentID, a.OrderID, a.AppointmentDate, a.AppointmentTime, o.PreferredInstallationDate, o.OrderNumber
    FROM appointments a
    LEFT JOIN `order` o ON a.OrderID = o.OrderID
    WHERE a.Service = 'Ocular Visit' OR a.AppointmentType = 'Ocular'
    ORDER BY a.AppointmentID
");

while ($row = $result->fetch_object()) {
    $apt_date = $row->AppointmentDate ? date('Y-m-d', strtotime($row->AppointmentDate)) : 'NULL';
    $apt_time = $row->AppointmentTime ?: 'NULL';
    $pref_date = $row->PreferredInstallationDate ? date('Y-m-d', strtotime($row->PreferredInstallationDate)) : 'NULL';
    $match = ($pref_date === 'NULL') ? 'N/A' : (($apt_date === $pref_date) ? 'YES' : 'NO');
    
    echo sprintf("%-6s | %-8s | %-12s | %-10s | %-12s | %s\n", 
        $row->AppointmentID, $row->OrderID, $apt_date, $apt_time, $pref_date, $match);
}

echo str_repeat('-', 90) . "\n";

$mysqli->close();
