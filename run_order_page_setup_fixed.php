<?php
/**
 * Setup Order Page table and sync customization data
 * Run: php run_order_page_setup_fixed.php
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

// Step 1: Create order_page table
echo "Step 1: Creating order_page table...\n";
$create_sql = "CREATE TABLE IF NOT EXISTS `order_page` (
  `OrderPageID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` varchar(50) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `Shape` varchar(50) DEFAULT NULL,
  `Dimension` varchar(100) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Thickness` varchar(50) DEFAULT NULL,
  `EdgeWork` varchar(50) DEFAULT NULL,
  `FrameType` varchar(50) DEFAULT NULL,
  `Engraving` varchar(255) DEFAULT NULL,
  `FileAttached` varchar(255) DEFAULT NULL,
  `TotalQuotation` decimal(12,2) DEFAULT 0.00,
  `Status` enum('Pending Review','Awaiting Admin','Ready to Approve') DEFAULT 'Pending Review',
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`OrderPageID`),
  KEY `idx_orderid` (`OrderID`),
  KEY `idx_status` (`Status`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_salesrep` (`SalesRep_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($create_sql) === TRUE) {
    echo "✓ Table created successfully\n";
} else {
    if (strpos($conn->error, 'already exists') !== false) {
        echo "⚠ Table already exists, continuing...\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
        exit;
    }
}

// Step 2: Clear customization table and insert new record
echo "\nStep 2: Clearing customization table and inserting new record...\n";
$conn->query("DELETE FROM `customization`");

$insert_sql = "INSERT INTO `customization` (
    `Customer_ID`,
    `Product_ID`,
    `ProductName`,
    `Dimensions`,
    `GlassShape`,
    `GlassType`,
    `GlassThickness`,
    `EdgeWork`,
    `FrameType`,
    `Engraving`,
    `DesignRef`,
    `EstimatePrice`,
    `TotalQuotation`,
    `DeliveryAddress`,
    `OrderDate`,
    `OrderID`,
    `Created_Date`
) VALUES (
    1,
    1,
    'Tempered Glass Panel',
    '[\"45\", \"0\", \"35\", \"0\"]',
    'Rectangle',
    'Tempered',
    '8mm',
    'Flat Polish',
    'Vinyl',
    'N/A',
    'design.pdf',
    3100.00,
    3100.00,
    '123 Glass St. Manila',
    '2025-05-30 10:00:00',
    NULL,
    NOW()
)";

if ($conn->query($insert_sql) === TRUE) {
    echo "✓ Customization table updated\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Step 3: Clear order_page and sync from customization
echo "\nStep 3: Clearing order_page and syncing from customization...\n";
$conn->query("DELETE FROM `order_page`");

// Get sales rep ID
$sales_rep_result = $conn->query("SELECT UserID FROM user WHERE Role = 'Sales Representative' LIMIT 1");
$sales_rep_row = $sales_rep_result->fetch_assoc();
$sales_rep_id = $sales_rep_row ? $sales_rep_row['UserID'] : 1;

// Get customization data
$custom_result = $conn->query("SELECT * FROM customization WHERE ProductName IS NOT NULL");

if ($custom_result && $custom_result->num_rows > 0) {
    while ($custom = $custom_result->fetch_assoc()) {
        // Parse dimensions
        $dimension = 'N/A';
        if ($custom['Dimensions']) {
            $dims = json_decode($custom['Dimensions'], true);
            if (is_array($dims) && count($dims) >= 3) {
                $dimension = $dims[0] . 'in x ' . $dims[2] . 'in';
            }
        }
        
        // Generate OrderID
        $order_id = 'GI' . str_pad($custom['CustomizationID'], 3, '0', STR_PAD_LEFT);
        
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
            '" . $conn->real_escape_string($custom['DeliveryAddress'] ?: '123 Glass St. Manila') . "',
            '" . $conn->real_escape_string($custom['OrderDate'] ?: $custom['Created_Date']) . "',
            '" . $conn->real_escape_string($custom['GlassShape']) . "',
            '" . $conn->real_escape_string($dimension) . "',
            '" . $conn->real_escape_string($custom['GlassType']) . "',
            '" . $conn->real_escape_string($custom['GlassThickness']) . "',
            '" . $conn->real_escape_string($custom['EdgeWork']) . "',
            '" . $conn->real_escape_string($custom['FrameType']) . "',
            '" . $conn->real_escape_string($custom['Engraving'] ?: 'N/A') . "',
            '" . $conn->real_escape_string($custom['DesignRef'] ?: 'N/A') . "',
            " . floatval($custom['TotalQuotation'] ?: $custom['EstimatePrice'] ?: 0) . ",
            'Pending Review',
            " . intval($custom['Customer_ID']) . ",
            " . intval($sales_rep_id) . "
        )";
        
        if ($conn->query($insert_order_page) === TRUE) {
            echo "✓ Inserted order: $order_id\n";
        } else {
            echo "✗ Error inserting: " . $conn->error . "\n";
        }
    }
} else {
    echo "⚠ No customization data found to sync\n";
}

$conn->close();

echo "\n✓ Setup completed successfully!\n";
echo "Order Page table created and populated with customization data.\n";

