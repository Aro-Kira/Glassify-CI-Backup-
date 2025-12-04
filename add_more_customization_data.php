<?php
/**
 * Add more sample data to customization table and sync to order_page
 * Run: php add_more_customization_data.php
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

// Get customer IDs (use existing or create default)
$customer_result = $conn->query("SELECT Customer_ID FROM customer LIMIT 1");
$customer_row = $customer_result->fetch_assoc();
$customer_id = $customer_row ? $customer_row['Customer_ID'] : 1;

// Sample data for 5+ orders
$sample_orders = [
    [
        'ProductName' => 'Laminated Glass Panel',
        'Dimensions' => '["50", "0", "40", "0"]', // 50in x 40in
        'GlassShape' => 'Square',
        'GlassType' => 'Laminated',
        'GlassThickness' => '6mm',
        'EdgeWork' => 'Mitered',
        'FrameType' => 'Aluminum',
        'Engraving' => 'Company Logo',
        'DesignRef' => 'logo_design.pdf',
        'EstimatePrice' => 4500.00,
        'TotalQuotation' => 4500.00,
        'DeliveryAddress' => '456 Glass Ave. Quezon City',
        'OrderDate' => '2025-06-01 14:30:00'
    ],
    [
        'ProductName' => 'Double Glazed Window',
        'Dimensions' => '["60", "0", "48", "0"]', // 60in x 48in
        'GlassShape' => 'Rectangle',
        'GlassType' => 'Double',
        'GlassThickness' => '10mm',
        'EdgeWork' => 'Beveled',
        'FrameType' => 'Wood',
        'Engraving' => 'None',
        'DesignRef' => 'window_spec.pdf',
        'EstimatePrice' => 5200.00,
        'TotalQuotation' => 5200.00,
        'DeliveryAddress' => '789 Window St. Makati',
        'OrderDate' => '2025-06-02 09:15:00'
    ],
    [
        'ProductName' => 'Low-E Glass Panel',
        'Dimensions' => '["42", "0", "36", "0"]', // 42in x 36in
        'GlassShape' => 'Rectangle',
        'GlassType' => 'Low-E',
        'GlassThickness' => '5mm',
        'EdgeWork' => 'Seamed',
        'FrameType' => 'Vinyl',
        'Engraving' => 'Custom Text',
        'DesignRef' => 'low_e_design.pdf',
        'EstimatePrice' => 3800.00,
        'TotalQuotation' => 3800.00,
        'DeliveryAddress' => '321 Energy Blvd. Pasig',
        'OrderDate' => '2025-06-03 11:45:00'
    ],
    [
        'ProductName' => 'Tinted Glass Door',
        'Dimensions' => '["84", "0", "36", "0"]', // 84in x 36in
        'GlassShape' => 'Rectangle',
        'GlassType' => 'Tinted',
        'GlassThickness' => '12mm',
        'EdgeWork' => 'Flat Polish',
        'FrameType' => 'Aluminum',
        'Engraving' => 'N/A',
        'DesignRef' => 'door_design.pdf',
        'EstimatePrice' => 6800.00,
        'TotalQuotation' => 6800.00,
        'DeliveryAddress' => '654 Door Rd. Taguig',
        'OrderDate' => '2025-06-04 16:20:00'
    ],
    [
        'ProductName' => 'Frosted Glass Partition',
        'Dimensions' => '["72", "0", "60", "0"]', // 72in x 60in
        'GlassShape' => 'Rectangle',
        'GlassType' => 'Frosted',
        'GlassThickness' => '8mm',
        'EdgeWork' => 'Mitered',
        'FrameType' => 'Vinyl',
        'Engraving' => 'Decorative Pattern',
        'DesignRef' => 'partition_design.pdf',
        'EstimatePrice' => 5500.00,
        'TotalQuotation' => 5500.00,
        'DeliveryAddress' => '987 Partition Ave. Mandaluyong',
        'OrderDate' => '2025-06-05 10:00:00'
    ],
    [
        'ProductName' => 'Tempered Glass Table Top',
        'Dimensions' => '["48", "0", "30", "0"]', // 48in x 30in
        'GlassShape' => 'Rectangle',
        'GlassType' => 'Tempered',
        'GlassThickness' => '8mm',
        'EdgeWork' => 'Beveled',
        'FrameType' => 'N/A',
        'Engraving' => 'N/A',
        'DesignRef' => 'table_design.pdf',
        'EstimatePrice' => 2900.00,
        'TotalQuotation' => 2900.00,
        'DeliveryAddress' => '147 Table St. San Juan',
        'OrderDate' => '2025-06-06 13:30:00'
    ],
    [
        'ProductName' => 'Pentagon Glass Panel',
        'Dimensions' => '["40", "0", "40", "0"]', // 40in x 40in
        'GlassShape' => 'Pentagon',
        'GlassType' => 'Tempered',
        'GlassThickness' => '6mm',
        'EdgeWork' => 'Seamed',
        'FrameType' => 'Aluminum',
        'Engraving' => 'Geometric Pattern',
        'DesignRef' => 'pentagon_design.pdf',
        'EstimatePrice' => 4200.00,
        'TotalQuotation' => 4200.00,
        'DeliveryAddress' => '258 Shape Blvd. Paranaque',
        'OrderDate' => '2025-06-07 15:45:00'
    ],
    [
        'ProductName' => 'Triangle Glass Window',
        'Dimensions' => '["36", "0", "36", "0"]', // 36in x 36in
        'GlassShape' => 'Triangle',
        'GlassType' => 'Laminated',
        'GlassThickness' => '5mm',
        'EdgeWork' => 'Flat Polish',
        'FrameType' => 'Wood',
        'Engraving' => 'N/A',
        'DesignRef' => 'triangle_design.pdf',
        'EstimatePrice' => 3300.00,
        'TotalQuotation' => 3300.00,
        'DeliveryAddress' => '369 Triangle Rd. Las Pinas',
        'OrderDate' => '2025-06-08 12:00:00'
    ]
];

echo "Inserting " . count($sample_orders) . " records into customization table...\n\n";

// Insert each order into customization table
foreach ($sample_orders as $index => $order_data) {
    // Parse dimensions to create proper format
    $dims = json_decode($order_data['Dimensions'], true);
    $height = $dims[0];
    $width = $dims[2];
    $dimension_display = $height . 'in x ' . $width . 'in';
    
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
        " . intval($customer_id) . ",
        1,
        '" . $conn->real_escape_string($order_data['ProductName']) . "',
        '" . $conn->real_escape_string($order_data['Dimensions']) . "',
        '" . $conn->real_escape_string($order_data['GlassShape']) . "',
        '" . $conn->real_escape_string($order_data['GlassType']) . "',
        '" . $conn->real_escape_string($order_data['GlassThickness']) . "',
        '" . $conn->real_escape_string($order_data['EdgeWork']) . "',
        '" . $conn->real_escape_string($order_data['FrameType']) . "',
        '" . $conn->real_escape_string($order_data['Engraving']) . "',
        '" . $conn->real_escape_string($order_data['DesignRef']) . "',
        " . floatval($order_data['EstimatePrice']) . ",
        " . floatval($order_data['TotalQuotation']) . ",
        '" . $conn->real_escape_string($order_data['DeliveryAddress']) . "',
        '" . $conn->real_escape_string($order_data['OrderDate']) . "',
        NULL,
        NOW()
    )";
    
    if ($conn->query($insert_sql) === TRUE) {
        $customization_id = $conn->insert_id;
        echo "✓ Inserted customization ID: $customization_id - " . $order_data['ProductName'] . "\n";
        
        // Now insert into order_page table
        $order_id = 'GI' . str_pad($customization_id, 3, '0', STR_PAD_LEFT);
        
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
            '" . $conn->real_escape_string($order_data['ProductName']) . "',
            '" . $conn->real_escape_string($order_data['DeliveryAddress']) . "',
            '" . $conn->real_escape_string($order_data['OrderDate']) . "',
            '" . $conn->real_escape_string($order_data['GlassShape']) . "',
            '" . $conn->real_escape_string($dimension_display) . "',
            '" . $conn->real_escape_string($order_data['GlassType']) . "',
            '" . $conn->real_escape_string($order_data['GlassThickness']) . "',
            '" . $conn->real_escape_string($order_data['EdgeWork']) . "',
            '" . $conn->real_escape_string($order_data['FrameType']) . "',
            '" . $conn->real_escape_string($order_data['Engraving']) . "',
            '" . $conn->real_escape_string($order_data['DesignRef']) . "',
            " . floatval($order_data['TotalQuotation']) . ",
            'Pending Review',
            " . intval($customer_id) . ",
            " . intval($sales_rep_id) . "
        )";
        
        if ($conn->query($insert_order_page) === TRUE) {
            echo "  ✓ Synced to order_page: $order_id\n";
        } else {
            echo "  ✗ Error syncing to order_page: " . $conn->error . "\n";
        }
    } else {
        echo "✗ Error inserting customization: " . $conn->error . "\n";
    }
    echo "\n";
}

$conn->close();

echo "✓ Completed! Added " . count($sample_orders) . " new orders to both customization and order_page tables.\n";

