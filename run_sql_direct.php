<?php
/**
 * Direct SQL execution script
 * Run: php run_sql_direct.php
 */

// Database configuration (from application/config/database.php)
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
echo "Executing SQL migration...\n\n";

// SQL statements to execute
$sql_statements = [
    "ALTER TABLE `customization` ADD COLUMN IF NOT EXISTS `OrderID` int(11) DEFAULT NULL AFTER `CustomizationID`",
    "ALTER TABLE `customization` ADD COLUMN IF NOT EXISTS `ProductName` varchar(255) DEFAULT NULL AFTER `Product_ID`",
    "ALTER TABLE `customization` ADD COLUMN IF NOT EXISTS `DeliveryAddress` varchar(255) DEFAULT NULL AFTER `Engraving`",
    "ALTER TABLE `customization` ADD COLUMN IF NOT EXISTS `OrderDate` datetime DEFAULT NULL AFTER `DeliveryAddress`",
    "ALTER TABLE `customization` ADD COLUMN IF NOT EXISTS `TotalQuotation` decimal(12,2) DEFAULT 0.00 AFTER `OrderDate`"
];

// Execute each statement
foreach ($sql_statements as $sql) {
    // MySQL doesn't support IF NOT EXISTS for ALTER TABLE ADD COLUMN
    // So we'll check if column exists first
    $column_name = '';
    if (preg_match('/ADD COLUMN.*?`([^`]+)`/', $sql, $matches)) {
        $column_name = $matches[1];
        
        // Check if column exists
        $check_sql = "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
                      WHERE TABLE_SCHEMA = '$database' 
                      AND TABLE_NAME = 'customization' 
                      AND COLUMN_NAME = '$column_name'";
        $result = $conn->query($check_sql);
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo "⚠ Column '$column_name' already exists, skipping...\n";
            continue;
        }
    }
    
    // Remove IF NOT EXISTS if present (MySQL doesn't support it)
    $sql = str_replace(' IF NOT EXISTS', '', $sql);
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Added column: $column_name\n";
    } else {
        // Check if error is "Duplicate column name"
        if (strpos($conn->error, 'Duplicate column name') !== false) {
            echo "⚠ Column '$column_name' already exists, skipping...\n";
        } else {
            echo "✗ Error: " . $conn->error . "\n";
        }
    }
}

// Add indexes
$indexes = [
    "CREATE INDEX IF NOT EXISTS `idx_orderid` ON `customization` (`OrderID`)",
    "CREATE INDEX IF NOT EXISTS `idx_customer_order` ON `customization` (`Customer_ID`, `OrderID`)"
];

foreach ($indexes as $sql) {
    // Remove IF NOT EXISTS (MySQL syntax)
    $sql = str_replace(' IF NOT EXISTS', '', $sql);
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Created index\n";
    } else {
        if (strpos($conn->error, 'Duplicate key name') !== false) {
            echo "⚠ Index already exists, skipping...\n";
        } else {
            echo "✗ Error: " . $conn->error . "\n";
        }
    }
}

$conn->close();

echo "\n✓ Migration completed successfully!\n";
echo "Fields added to customization table:\n";
echo "  - OrderID\n";
echo "  - ProductName\n";
echo "  - DeliveryAddress\n";
echo "  - OrderDate\n";
echo "  - TotalQuotation\n";

