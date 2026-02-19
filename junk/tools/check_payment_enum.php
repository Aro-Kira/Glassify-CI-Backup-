<?php
$db = new mysqli('localhost', 'root', '', 'latest_glassifydb');
if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

$result = $db->query('SHOW COLUMNS FROM payment WHERE Field = "PaymentMethod"');
if ($result && $row = $result->fetch_assoc()) {
    echo "PaymentMethod Type: " . $row['Type'] . "\n";
} else {
    echo "Field not found\n";
}

// Also check recent payment records
echo "\nRecent payment records:\n";
$payments = $db->query('SELECT OrderID, PaymentMethod, Amount, Status FROM payment ORDER BY Payment_ID DESC LIMIT 5');
if ($payments) {
    while ($payment = $payments->fetch_assoc()) {
        echo "OrderID: {$payment['OrderID']}, Method: {$payment['PaymentMethod']}, Amount: {$payment['Amount']}, Status: {$payment['Status']}\n";
    }
}
