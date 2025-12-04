<?php
/**
 * Setup Order Page table and sync customization data
 * Run: php run_order_page_setup.php
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
$create_table_sql = file_get_contents('create_order_page_table.sql');
$statements = array_filter(array_map('trim', explode(';', $create_table_sql)));

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) continue;
    
    if ($conn->query($statement) === TRUE) {
        echo "✓ Table created successfully\n";
    } else {
        if (strpos($conn->error, 'already exists') !== false) {
            echo "⚠ Table already exists, continuing...\n";
        } else {
            echo "✗ Error: " . $conn->error . "\n";
        }
    }
}

// Step 2: Clear customization table and insert new record
echo "\nStep 2: Clearing customization table and inserting new record...\n";
$clear_sql = file_get_contents('clear_and_insert_customization.sql');
$statements = array_filter(array_map('trim', explode(';', $clear_sql)));

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) continue;
    
    if ($conn->query($statement) === TRUE) {
        echo "✓ Customization table updated\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
    }
}

// Step 3: Sync customization to order_page
echo "\nStep 3: Syncing customization data to order_page table...\n";
$sync_sql = file_get_contents('sync_customization_to_order_page.sql');

// Handle the INSERT ... SELECT with ROW_NUMBER (MySQL 8.0+)
// For older MySQL, use a simpler approach
$simple_sync = "
INSERT INTO `order_page` (
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
)
SELECT 
    CONCAT('GI', LPAD(COALESCE(c.OrderID, c.CustomizationID), 3, '0')) as OrderID,
    c.ProductName,
    COALESCE(c.DeliveryAddress, '123 Glass St. Manila') as Address,
    COALESCE(c.OrderDate, c.Created_Date) as OrderDate,
    c.GlassShape as Shape,
    CASE 
        WHEN c.Dimensions LIKE '[%' OR c.Dimensions LIKE '{%' THEN
            CONCAT(
                TRIM(BOTH '\"' FROM JSON_UNQUOTE(JSON_EXTRACT(c.Dimensions, '$[0]'))), 'in x ',
                TRIM(BOTH '\"' FROM JSON_UNQUOTE(JSON_EXTRACT(c.Dimensions, '$[2]'))), 'in'
            )
        ELSE c.Dimensions
    END as Dimension,
    c.GlassType as Type,
    c.GlassThickness as Thickness,
    c.EdgeWork,
    c.FrameType,
    COALESCE(c.Engraving, 'N/A') as Engraving,
    COALESCE(c.DesignRef, 'N/A') as FileAttached,
    COALESCE(c.TotalQuotation, c.EstimatePrice, 0) as TotalQuotation,
    'Pending Review' as Status,
    c.Customer_ID,
    (SELECT UserID FROM user WHERE Role = 'Sales Representative' LIMIT 1) as SalesRep_ID
FROM customization c
WHERE c.ProductName IS NOT NULL
ON DUPLICATE KEY UPDATE
    ProductName = VALUES(ProductName),
    Address = VALUES(Address),
    OrderDate = VALUES(OrderDate),
    Shape = VALUES(Shape),
    Dimension = VALUES(Dimension),
    Type = VALUES(Type),
    Thickness = VALUES(Thickness),
    EdgeWork = VALUES(EdgeWork),
    FrameType = VALUES(FrameType),
    Engraving = VALUES(Engraving),
    FileAttached = VALUES(FileAttached),
    TotalQuotation = VALUES(TotalQuotation);
";

if ($conn->query($simple_sync) === TRUE) {
    echo "✓ Data synced to order_page table\n";
} else {
    echo "✗ Error syncing: " . $conn->error . "\n";
}

$conn->close();

echo "\n✓ Setup completed successfully!\n";
echo "Order Page table created and populated with customization data.\n";

