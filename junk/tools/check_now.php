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

$query = "
    SELECT a.AppointmentID, a.OrderID, a.AppointmentDate, a.AppointmentTime, o.PreferredInstallationDate
    FROM appointments a
    LEFT JOIN `order` o ON a.OrderID = o.OrderID
    WHERE a.Service = 'Ocular Visit' OR a.AppointmentType = 'Ocular'
    ORDER BY a.AppointmentID DESC
";

$result = $mysqli->query($query);

echo "=== CURRENT APPOINTMENTS STATUS ===\n";
echo str_repeat('-', 90) . "\n";
echo sprintf("%-6s | %-8s | %-12s | %-10s | %-12s | %s\n", 'AptID', 'OrderID', 'Apt Date', 'Apt Time', 'Pref Date', 'Match?');
echo str_repeat('-', 90) . "\n";

while ($row = $result->fetch_object()) {
    $apt_date = $row->AppointmentDate ? date('Y-m-d', strtotime($row->AppointmentDate)) : 'NULL';
    $apt_time = $row->AppointmentTime ?: 'NULL';
    $pref_date = $row->PreferredInstallationDate ? date('Y-m-d', strtotime($row->PreferredInstallationDate)) : 'NULL';
    $match = ($pref_date === 'NULL') ? 'N/A' : (($apt_date === $pref_date) ? 'YES' : 'NO');
    
    echo sprintf("%-6s | %-8s | %-12s | %-10s | %-12s | %s\n", 
        $row->AppointmentID, $row->OrderID, $apt_date, $apt_time, $pref_date, $match);
}

$mysqli->close();
