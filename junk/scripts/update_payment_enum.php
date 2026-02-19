<?php
$db = new mysqli('localhost', 'root', '', 'latest_glassifydb');
if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

echo "Updating payment table PaymentMethod enum...\n";

$sql = "ALTER TABLE `payment` 
        MODIFY COLUMN `PaymentMethod` ENUM('E-Wallet', 'Cash on Delivery', 'Cash', 'Bank Transfer', 'Check', 'Credit Card', 'Debit Card') DEFAULT NULL";

if ($db->query($sql)) {
    echo "Successfully updated PaymentMethod enum!\n";
    
    // Verify the change
    $result = $db->query('SHOW COLUMNS FROM payment WHERE Field = "PaymentMethod"');
    if ($result && $row = $result->fetch_assoc()) {
        echo "New PaymentMethod Type: " . $row['Type'] . "\n";
    }
} else {
    echo "Error updating table: " . $db->error . "\n";
}

$db->close();
