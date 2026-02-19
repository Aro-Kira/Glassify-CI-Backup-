<?php
$mysqli = new mysqli('localhost', 'root', '', 'latest_glassifydb');

echo "=== ORDER TABLE COLUMNS (Fabrication/Payment) ===\n";
$result = $mysqli->query('DESCRIBE `order`');
while($row = $result->fetch_assoc()) {
    if(strpos($row['Field'], 'Fabrication') !== false || strpos($row['Field'], 'Payment') !== false) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
}

echo "\n=== APPOINTMENTS TABLE COLUMNS (Fabrication/Payment) ===\n";
$result = $mysqli->query('DESCRIBE appointments');
while($row = $result->fetch_assoc()) {
    if(strpos($row['Field'], 'Fabrication') !== false || strpos($row['Field'], 'Payment') !== false) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
}

echo "\n=== CHECKING ORDER 4 FABRICATION DATA ===\n";
$result = $mysqli->query('SELECT OrderID, FabricationStatus, FabricationPaymentAmount, FabricationPaymentMethod, FabricationPaymentStatus, FabricationReceiptPath FROM `order` WHERE OrderID = 4');
$row = $result->fetch_assoc();
print_r($row);

echo "\n=== CHECKING FABRICATION APPOINTMENT FOR ORDER 4 ===\n";
$result = $mysqli->query("SELECT AppointmentID, OrderID, Service, FabricationPaymentAmount, FabricationPaymentMethod, FabricationPaymentStatus, FabricationReceiptPath FROM appointments WHERE OrderID = 4 AND Service = 'In Fabrication'");
$row = $result->fetch_assoc();
print_r($row);
