<?php
/**
 * Redistribute customization records 7-14 to different customers
 * Each customer should have 1-2 orders
 * Run: php redistribute_customizations.php
 */

// Database configuration
$host = 'localhost';
$username = 'admin_glassify';
$password = 'glassifyAdmin';
$database = 'glassify-test';

// Connect to database
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Connected to database successfully.\n\n";

// Get sales rep ID
$sales_rep_result = $conn->query("SELECT UserID FROM user WHERE Role = 'Sales Representative' LIMIT 1");
$sales_rep_row = $sales_rep_result->fetch_assoc();
$sales_rep_id = $sales_rep_row ? $sales_rep_row['UserID'] : 1;

// Get available customers (User IDs 11-16 that we created)
$customers_result = $conn->query("SELECT Customer_ID, UserID FROM customer WHERE Customer_ID IN (11, 12, 13, 14, 15, 16) ORDER BY Customer_ID");
$customers = [];
while ($row = $customers_result->fetch_assoc()) {
    $customers[] = $row['Customer_ID'];
}

if (empty($customers)) {
    die("No customers found. Please run add_customers_with_orders.php first.\n");
}

echo "Found " . count($customers) . " customers to distribute orders to.\n\n";

// Redistribution plan: Each customer gets 1-2 orders
// Customer 11 (Maria): 1 order (keep existing GI015)
// Customer 12 (Juan): 2 orders (keep existing GI016 + assign customization 7)
// Customer 13 (Ana): 2 orders (keep existing GI017 + assign customization 8)
// Customer 14 (Carlos): 2 orders (keep existing GI018 + assign customization 9)
// Customer 15 (Rosa): 2 orders (keep existing GI019 + assign customization 10)
// Customer 16 (Pedro): 2 orders (keep existing GI020 + assign customization 11)

$redistribution = [
    7 => 12,  // Laminated Glass Panel -> Juan Cruz
    8 => 13,  // Double Glazed Window -> Ana Reyes
    9 => 14,  // Low-E Glass Panel -> Carlos Torres
    10 => 15, // Tinted Glass Door -> Rosa Garcia
    11 => 16, // Frosted Glass Partition -> Pedro Lopez
    12 => 12, // Tempered Glass Table Top -> Juan Cruz (2nd order)
    13 => 13, // Pentagon Glass Panel -> Ana Reyes (2nd order)
    14 => 14  // Triangle Glass Window -> Carlos Torres (2nd order)
];

echo "Redistributing customization records...\n\n";

foreach ($redistribution as $customization_id => $new_customer_id) {
    // Get the customization record
    $custom_result = $conn->query("SELECT * FROM customization WHERE CustomizationID = " . intval($customization_id));
    
    if (!$custom_result || $custom_result->num_rows == 0) {
        echo "⚠ Customization ID $customization_id not found, skipping...\n";
        continue;
    }
    
    $custom = $custom_result->fetch_assoc();
    
    // Get customer info
    $customer_result = $conn->query("SELECT UserID FROM customer WHERE Customer_ID = " . intval($new_customer_id));
    if (!$customer_result || $customer_result->num_rows == 0) {
        echo "⚠ Customer ID $new_customer_id not found, skipping customization $customization_id...\n";
        continue;
    }
    
    $customer = $customer_result->fetch_assoc();
    
    // Update customization record
    $update_custom = "UPDATE `customization` SET 
        `Customer_ID` = " . intval($new_customer_id) . "
    WHERE `CustomizationID` = " . intval($customization_id);
    
    if ($conn->query($update_custom) === TRUE) {
        echo "✓ Updated Customization ID $customization_id -> Customer ID $new_customer_id\n";
        
        // Parse dimensions
        $dims = json_decode($custom['Dimensions'], true);
        $height = $dims[0];
        $width = $dims[2];
        $dimension_display = $height . 'in x ' . $width . 'in';
        
        // Generate Order ID
        $order_id = 'GI' . str_pad($customization_id, 3, '0', STR_PAD_LEFT);
        
        // Check if order_page entry exists, if not create it, if yes update it
        $check_order_page = $conn->query("SELECT OrderPageID FROM order_page WHERE OrderID = '" . $conn->real_escape_string($order_id) . "'");
        
        if ($check_order_page && $check_order_page->num_rows > 0) {
            // Update existing order_page entry
            $update_order_page = "UPDATE `order_page` SET 
                `Customer_ID` = " . intval($new_customer_id) . ",
                `SalesRep_ID` = " . intval($sales_rep_id) . ",
                `Status` = 'Pending Review'
            WHERE `OrderID` = '" . $conn->real_escape_string($order_id) . "'";
            
            if ($conn->query($update_order_page) === TRUE) {
                echo "  ✓ Updated Order Page: $order_id (Customer: $new_customer_id)\n";
            } else {
                echo "  ✗ Error updating order_page: " . $conn->error . "\n";
            }
        } else {
            // Create new order_page entry
            $insert_order_page = "INSERT INTO `order_page` (
                `OrderID`,
                `ProductName`,
                `Address`,
                `OrderDate`,
                `Shape`,
                `Dimension`,
                `Type`,
                `Thickness`,
                `EdgeWork`,
                `FrameType`,
                `Engraving`,
                `FileAttached`,
                `TotalQuotation`,
                `Status`,
                `Customer_ID`,
                `SalesRep_ID`
            ) VALUES (
                '" . $conn->real_escape_string($order_id) . "',
                '" . $conn->real_escape_string($custom['ProductName']) . "',
                '" . $conn->real_escape_string($custom['DeliveryAddress'] ?: '123 Main St. Manila') . "',
                '" . $conn->real_escape_string($custom['OrderDate'] ?: $custom['Created_Date']) . "',
                '" . $conn->real_escape_string($custom['GlassShape']) . "',
                '" . $conn->real_escape_string($dimension_display) . "',
                '" . $conn->real_escape_string($custom['GlassType']) . "',
                '" . $conn->real_escape_string($custom['GlassThickness']) . "',
                '" . $conn->real_escape_string($custom['EdgeWork']) . "',
                '" . $conn->real_escape_string($custom['FrameType']) . "',
                '" . $conn->real_escape_string($custom['Engraving'] ?: 'N/A') . "',
                '" . $conn->real_escape_string($custom['DesignRef'] ?: 'N/A') . "',
                " . floatval($custom['TotalQuotation'] ?: $custom['EstimatePrice'] ?: 0) . ",
                'Pending Review',
                " . intval($new_customer_id) . ",
                " . intval($sales_rep_id) . "
            )";
            
            if ($conn->query($insert_order_page) === TRUE) {
                echo "  ✓ Created Order Page: $order_id (Customer: $new_customer_id)\n";
            } else {
                echo "  ✗ Error creating order_page: " . $conn->error . "\n";
            }
        }
    } else {
        echo "✗ Error updating customization $customization_id: " . $conn->error . "\n";
    }
    echo "\n";
}

// Summary
echo "=== Distribution Summary ===\n";
foreach ($customers as $customer_id) {
    $order_count = $conn->query("SELECT COUNT(*) as count FROM order_page WHERE Customer_ID = " . intval($customer_id));
    $count_row = $order_count->fetch_assoc();
    $count = $count_row['count'];
    
    $user_result = $conn->query("SELECT First_Name, Last_Name FROM user WHERE UserID = " . intval($customer_id));
    $user = $user_result->fetch_assoc();
    $name = $user ? ($user['First_Name'] . ' ' . $user['Last_Name']) : "Customer $customer_id";
    
    echo "Customer ID $customer_id ($name): $count order(s)\n";
}

$conn->close();

echo "\n✓ Redistribution completed!\n";

