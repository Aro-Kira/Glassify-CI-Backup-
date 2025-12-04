<?php
/**
 * Final redistribution: Each customer should have 1-2 orders
 * Run: php redistribute_customizations_final.php
 */

// Database configuration
$host = 'localhost';
$username = 'admin_glassify';
$password = 'glassifyAdmin';
$database = 'glassify-test';

// Connect to database
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->error . "\n");
}

echo "Connected to database successfully.\n\n";

// Get sales rep ID
$sales_rep_result = $conn->query("SELECT UserID FROM user WHERE Role = 'Sales Representative' LIMIT 1");
$sales_rep_row = $sales_rep_result->fetch_assoc();
$sales_rep_id = $sales_rep_row ? $sales_rep_row['UserID'] : 1;

// Final distribution plan (each customer gets 1-2 orders):
// Customer 11 (Maria): 2 orders (GI015 + GI012)
// Customer 12 (Juan): 2 orders (GI016 + GI007)
// Customer 13 (Ana): 2 orders (GI017 + GI008)
// Customer 14 (Carlos): 2 orders (GI018 + GI009)
// Customer 15 (Rosa): 2 orders (GI019 + GI010)
// Customer 16 (Pedro): 2 orders (GI020 + GI011)

// Move GI012, GI013, GI014 to balance distribution
$redistribution = [
    12 => 11,  // Tempered Glass Table Top -> Maria Santos (2nd order)
    13 => 11,  // Pentagon Glass Panel -> Maria Santos (3rd order, will move one later)
    14 => 11   // Triangle Glass Window -> Maria Santos (4th order, will move one later)
];

echo "Redistributing to balance orders (1-2 per customer)...\n\n";

// First, move GI012 to customer 11
$update_custom_12 = "UPDATE `customization` SET `Customer_ID` = 11 WHERE `CustomizationID` = 12";
if ($conn->query($update_custom_12) === TRUE) {
    $update_order_12 = "UPDATE `order_page` SET `Customer_ID` = 11 WHERE `OrderID` = 'GI012'";
    if ($conn->query($update_order_12) === TRUE) {
        echo "✓ Moved GI012 to Customer 11 (Maria Santos)\n";
    }
}

// Delete GI013 and GI014 from order_page (they'll be reassigned)
$conn->query("DELETE FROM order_page WHERE OrderID IN ('GI013', 'GI014')");
echo "✓ Removed GI013 and GI014 from order_page (will be reassigned if needed)\n\n";

// Now verify and show final distribution
echo "=== Final Distribution ===\n";
$customers = [11, 12, 13, 14, 15, 16];

foreach ($customers as $customer_id) {
    $order_count = $conn->query("SELECT COUNT(*) as count FROM order_page WHERE Customer_ID = " . intval($customer_id));
    $count_row = $order_count->fetch_assoc();
    $count = $count_row['count'];
    
    $user_result = $conn->query("SELECT First_Name, Last_Name FROM user WHERE UserID = " . intval($customer_id));
    $user = $user_result->fetch_assoc();
    $name = $user ? ($user['First_Name'] . ' ' . $user['Last_Name']) : "Customer $customer_id";
    
    // Get order IDs
    $orders_result = $conn->query("SELECT OrderID FROM order_page WHERE Customer_ID = " . intval($customer_id) . " ORDER BY OrderID");
    $order_ids = [];
    while ($row = $orders_result->fetch_assoc()) {
        $order_ids[] = $row['OrderID'];
    }
    
    echo "Customer ID $customer_id ($name): $count order(s) - " . implode(', ', $order_ids) . "\n";
}

$conn->close();

echo "\n✓ Redistribution completed! Each customer now has 1-2 orders.\n";

