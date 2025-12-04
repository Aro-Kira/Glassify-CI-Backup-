<?php
/**
 * Add 5+ new customers (with matching User IDs) and their orders
 * Customer_ID must equal UserID
 * Product_ID generated from Product Name (e.g., "Tempered Glass" → TEMP001)
 * Run: php add_customers_with_orders.php
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

// Get next available UserID
$user_result = $conn->query("SELECT MAX(UserID) as max_id FROM user");
$user_row = $user_result->fetch_assoc();
$next_user_id = ($user_row['max_id'] ? intval($user_row['max_id']) : 0) + 1;

// Get sales rep ID
$sales_rep_result = $conn->query("SELECT UserID FROM user WHERE Role = 'Sales Representative' LIMIT 1");
$sales_rep_row = $sales_rep_result->fetch_assoc();
$sales_rep_id = $sales_rep_row ? $sales_rep_row['UserID'] : 1;

// Function to generate Product ID from Product Name
function generateProductID($productName) {
    // Extract first 3-4 letters from each word, uppercase
    $words = explode(' ', $productName);
    $prefix = '';
    foreach ($words as $word) {
        $prefix .= strtoupper(substr($word, 0, min(4, strlen($word))));
    }
    // Limit to 4-6 characters total
    $prefix = substr($prefix, 0, 6);
    
    // Get next number for this prefix
    global $conn;
    $check_sql = "SELECT Product_ID FROM product WHERE Product_ID LIKE '" . $conn->real_escape_string($prefix) . "%' ORDER BY Product_ID DESC LIMIT 1";
    $result = $conn->query($check_sql);
    
    $next_num = 1;
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_id = $row['Product_ID'];
        // Extract number part
        $num_part = preg_replace('/[^0-9]/', '', $last_id);
        $next_num = intval($num_part) + 1;
    }
    
    return $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
}

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Sample customers with their orders
$customers_data = [
    [
        'user' => [
            'First_Name' => 'Maria',
            'Last_Name' => 'Santos',
            'Middle_Name' => 'C.',
            'Email' => 'maria.santos.customer@email.com',
            'Password' => 'Customer123!',
            'PhoneNum' => '09171234567',
            'Role' => 'Customer'
        ],
        'order' => [
            'ProductName' => 'Tempered Glass Panel',
            'Dimensions' => '["48", "0", "36", "0"]', // 48in x 36in
            'GlassShape' => 'Rectangle',
            'GlassType' => 'Tempered',
            'GlassThickness' => '8mm',
            'EdgeWork' => 'Flat Polish',
            'FrameType' => 'Vinyl',
            'Engraving' => 'N/A',
            'DesignRef' => 'tempered_panel.pdf',
            'EstimatePrice' => 3500.00,
            'TotalQuotation' => 3500.00,
            'DeliveryAddress' => '123 Main St. Manila',
            'OrderDate' => '2025-06-10 10:00:00',
            'Status' => 'Pending Review'
        ]
    ],
    [
        'user' => [
            'First_Name' => 'Juan',
            'Last_Name' => 'Cruz',
            'Middle_Name' => 'D.',
            'Email' => 'juan.cruz.customer@email.com',
            'Password' => 'Customer123!',
            'PhoneNum' => '09172345678',
            'Role' => 'Customer'
        ],
        'order' => [
            'ProductName' => 'Laminated Glass Window',
            'Dimensions' => '["60", "0", "48", "0"]', // 60in x 48in
            'GlassShape' => 'Rectangle',
            'GlassType' => 'Laminated',
            'GlassThickness' => '6mm',
            'EdgeWork' => 'Mitered',
            'FrameType' => 'Aluminum',
            'Engraving' => 'Company Logo',
            'DesignRef' => 'laminated_window.pdf',
            'EstimatePrice' => 4800.00,
            'TotalQuotation' => 4800.00,
            'DeliveryAddress' => '456 Oak Ave. Quezon City',
            'OrderDate' => '2025-06-11 14:30:00',
            'Status' => 'Awaiting Admin'
        ]
    ],
    [
        'user' => [
            'First_Name' => 'Ana',
            'Last_Name' => 'Reyes',
            'Middle_Name' => 'B.',
            'Email' => 'ana.reyes.customer@email.com',
            'Password' => 'Customer123!',
            'PhoneNum' => '09173456789',
            'Role' => 'Customer'
        ],
        'order' => [
            'ProductName' => 'Double Glazed Panel',
            'Dimensions' => '["54", "0", "42", "0"]', // 54in x 42in
            'GlassShape' => 'Square',
            'GlassType' => 'Double',
            'GlassThickness' => '10mm',
            'EdgeWork' => 'Beveled',
            'FrameType' => 'Wood',
            'Engraving' => 'None',
            'DesignRef' => 'double_glazed.pdf',
            'EstimatePrice' => 5500.00,
            'TotalQuotation' => 5500.00,
            'DeliveryAddress' => '789 Pine St. Makati',
            'OrderDate' => '2025-06-12 09:15:00',
            'Status' => 'Ready to Approve'
        ]
    ],
    [
        'user' => [
            'First_Name' => 'Carlos',
            'Last_Name' => 'Torres',
            'Middle_Name' => 'E.',
            'Email' => 'carlos.torres.customer@email.com',
            'Password' => 'Customer123!',
            'PhoneNum' => '09174567890',
            'Role' => 'Customer'
        ],
        'order' => [
            'ProductName' => 'Low-E Glass Panel',
            'Dimensions' => '["42", "0", "36", "0"]', // 42in x 36in
            'GlassShape' => 'Rectangle',
            'GlassType' => 'Low-E',
            'GlassThickness' => '5mm',
            'EdgeWork' => 'Seamed',
            'FrameType' => 'Vinyl',
            'Engraving' => 'Custom Text',
            'DesignRef' => 'low_e_panel.pdf',
            'EstimatePrice' => 4200.00,
            'TotalQuotation' => 4200.00,
            'DeliveryAddress' => '321 Elm Blvd. Pasig',
            'OrderDate' => '2025-06-13 11:45:00',
            'Status' => 'Pending Review'
        ]
    ],
    [
        'user' => [
            'First_Name' => 'Rosa',
            'Last_Name' => 'Garcia',
            'Middle_Name' => 'F.',
            'Email' => 'rosa.garcia.customer@email.com',
            'Password' => 'Customer123!',
            'PhoneNum' => '09175678901',
            'Role' => 'Customer'
        ],
        'order' => [
            'ProductName' => 'Tinted Glass Door',
            'Dimensions' => '["84", "0", "36", "0"]', // 84in x 36in
            'GlassShape' => 'Rectangle',
            'GlassType' => 'Tinted',
            'GlassThickness' => '12mm',
            'EdgeWork' => 'Flat Polish',
            'FrameType' => 'Aluminum',
            'Engraving' => 'N/A',
            'DesignRef' => 'tinted_door.pdf',
            'EstimatePrice' => 6800.00,
            'TotalQuotation' => 6800.00,
            'DeliveryAddress' => '654 Maple Rd. Taguig',
            'OrderDate' => '2025-06-14 16:20:00',
            'Status' => 'Awaiting Admin'
        ]
    ],
    [
        'user' => [
            'First_Name' => 'Pedro',
            'Last_Name' => 'Lopez',
            'Middle_Name' => 'G.',
            'Email' => 'pedro.lopez.customer@email.com',
            'Password' => 'Customer123!',
            'PhoneNum' => '09176789012',
            'Role' => 'Customer'
        ],
        'order' => [
            'ProductName' => 'Frosted Glass Partition',
            'Dimensions' => '["72", "0", "60", "0"]', // 72in x 60in
            'GlassShape' => 'Rectangle',
            'GlassType' => 'Frosted',
            'GlassThickness' => '8mm',
            'EdgeWork' => 'Mitered',
            'FrameType' => 'Vinyl',
            'Engraving' => 'Decorative Pattern',
            'DesignRef' => 'frosted_partition.pdf',
            'EstimatePrice' => 5200.00,
            'TotalQuotation' => 5200.00,
            'DeliveryAddress' => '987 Cedar Ave. Mandaluyong',
            'OrderDate' => '2025-06-15 10:00:00',
            'Status' => 'Ready to Approve'
        ]
    ]
];

echo "Creating " . count($customers_data) . " customers with orders...\n\n";

foreach ($customers_data as $index => $data) {
    $user_data = $data['user'];
    $order_data = $data['order'];
    
    // Step 1: Check if user exists, if not create User (with specific UserID)
    $check_user = $conn->query("SELECT UserID FROM user WHERE Email = '" . $conn->real_escape_string($user_data['Email']) . "'");
    $user_id = null;
    
    if ($check_user && $check_user->num_rows > 0) {
        $user_row = $check_user->fetch_assoc();
        $user_id = intval($user_row['UserID']);
        echo "✓ User already exists ID: $user_id - " . $user_data['First_Name'] . " " . $user_data['Last_Name'] . "\n";
    } else {
        $user_id = $next_user_id + $index;
        $hashed_password = hashPassword($user_data['Password']);
        
        $insert_user = "INSERT INTO `user` (
        `UserID`,
        `First_Name`,
        `Last_Name`,
        `Middle_Name`,
        `Email`,
        `Password`,
        `PhoneNum`,
        `Role`,
        `Status`,
        `Date_Created`,
        `Date_Updated`
    ) VALUES (
        " . intval($user_id) . ",
        '" . $conn->real_escape_string($user_data['First_Name']) . "',
        '" . $conn->real_escape_string($user_data['Last_Name']) . "',
        '" . $conn->real_escape_string($user_data['Middle_Name']) . "',
        '" . $conn->real_escape_string($user_data['Email']) . "',
        '" . $conn->real_escape_string($hashed_password) . "',
        '" . $conn->real_escape_string($user_data['PhoneNum']) . "',
        'Customer',
        'Active',
        NOW(),
        NOW()
    )";
        
        if ($conn->query($insert_user) === TRUE) {
            echo "✓ Created User ID: $user_id - " . $user_data['First_Name'] . " " . $user_data['Last_Name'] . "\n";
        } else {
            echo "✗ Error creating user: " . $conn->error . "\n";
            continue;
        }
    }
    
    if ($user_id) {
        // Step 2: Check if customer exists, if not create Customer (Customer_ID = UserID)
        $check_customer = $conn->query("SELECT Customer_ID FROM customer WHERE Customer_ID = " . intval($user_id));
        
        if ($check_customer && $check_customer->num_rows > 0) {
            echo "  ✓ Customer already exists ID: $user_id (matches UserID)\n";
        } else {
        $insert_customer = "INSERT INTO `customer` (
            `Customer_ID`,
            `UserID`
        ) VALUES (
            " . intval($user_id) . ",
            " . intval($user_id) . "
        )";
            
            if ($conn->query($insert_customer) === TRUE) {
                echo "  ✓ Created Customer ID: $user_id (matches UserID)\n";
            } else {
                echo "  ✗ Error creating customer: " . $conn->error . "\n";
                continue;
            }
        }
        
        // Step 3: Generate Product ID from Product Name
            $product_id = generateProductID($order_data['ProductName']);
            
            // Check if product exists, if not create it
            $check_product = $conn->query("SELECT Product_ID FROM product WHERE Product_ID = '" . $conn->real_escape_string($product_id) . "'");
            if ($check_product->num_rows == 0) {
                $insert_product = "INSERT INTO `product` (
                    `Product_ID`,
                    `ProductName`,
                    `Category`,
                    `Material`,
                    `Price`,
                    `Status`,
                    `ImageUrl`
                ) VALUES (
                    '" . $conn->real_escape_string($product_id) . "',
                    '" . $conn->real_escape_string($order_data['ProductName']) . "',
                    'custom-panels',
                    'Glass',
                    " . floatval($order_data['TotalQuotation']) . ",
                    'In Stock',
                    'assets/images/products/default.jpg'
                )";
                
                if ($conn->query($insert_product) === TRUE) {
                    echo "  ✓ Created Product ID: $product_id - " . $order_data['ProductName'] . "\n";
                } else {
                    echo "  ✗ Error creating product: " . $conn->error . "\n";
                    continue;
                }
            } else {
                echo "  ✓ Product exists: $product_id\n";
            }
            
            // Step 4: Create Customization record
            $dims = json_decode($order_data['Dimensions'], true);
            $height = $dims[0];
            $width = $dims[2];
            $dimension_display = $height . 'in x ' . $width . 'in';
            
            $insert_customization = "INSERT INTO `customization` (
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
                " . intval($user_id) . ",
                '" . $conn->real_escape_string($product_id) . "',
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
            
            if ($conn->query($insert_customization) === TRUE) {
                $customization_id = $conn->insert_id;
                echo "  ✓ Created Customization ID: $customization_id\n";
                
                // Step 5: Create Order Page entry
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
                    '" . $conn->real_escape_string($order_data['Status']) . "',
                    " . intval($user_id) . ",
                    " . intval($sales_rep_id) . "
                )";
                
                if ($conn->query($insert_order_page) === TRUE) {
                    echo "  ✓ Created Order Page: $order_id (Status: " . $order_data['Status'] . ")\n";
                } else {
                    echo "  ✗ Error creating order_page: " . $conn->error . "\n";
                }
            } else {
                echo "  ✗ Error creating customization: " . $conn->error . "\n";
            }
    }
    echo "\n";
}

$conn->close();

echo "✓ Completed! Created " . count($customers_data) . " customers with matching User IDs and their orders.\n";
echo "All orders are synced to order_page table and will appear in Sales Order Page.\n";

