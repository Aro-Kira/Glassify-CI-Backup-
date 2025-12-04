<?php
/**
 * Create inventory_items table for raw materials
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating inventory_items table...\n\n";
    
    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS `inventory_items` (
        `InventoryItemID` int(11) NOT NULL AUTO_INCREMENT,
        `ItemID` varchar(50) NOT NULL UNIQUE COMMENT 'e.g., GL-001, AL-022',
        `Name` varchar(255) NOT NULL,
        `Category` varchar(100) NOT NULL,
        `InStock` int(11) NOT NULL DEFAULT 0,
        `Unit` varchar(50) NOT NULL COMMENT 'sqm, pcs, tubes, meter, sets, etc.',
        `Status` enum('In Stock','Low Stock','Out of Stock','New') DEFAULT 'In Stock',
        `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
        `DateUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`InventoryItemID`),
        UNIQUE KEY `ItemID_unique` (`ItemID`),
        KEY `Category` (`Category`),
        KEY `Status` (`Status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql);
    echo "✓ Created table: inventory_items\n";
    
    // Insert sample data based on the image
    echo "\nInserting sample inventory items...\n";
    
    $items = [
        ['ItemID' => 'GL-001', 'Name' => 'Tempered Glass', 'Category' => 'Glass', 'InStock' => 150, 'Unit' => 'sqm', 'Status' => 'In Stock'],
        ['ItemID' => 'AL-022', 'Name' => 'Aluminum Frame', 'Category' => 'Aluminum', 'InStock' => 10, 'Unit' => 'pcs', 'Status' => 'Low Stock'],
        ['ItemID' => 'GL-002', 'Name' => 'Laminated Glass', 'Category' => 'Glass', 'InStock' => 120, 'Unit' => 'sqm', 'Status' => 'In Stock'],
        ['ItemID' => 'AC-003', 'Name' => 'Silicone Sealant', 'Category' => 'Accessories', 'InStock' => 200, 'Unit' => 'tubes', 'Status' => 'In Stock'],
        ['ItemID' => 'AL-045', 'Name' => 'Sliding Track', 'Category' => 'Aluminum', 'InStock' => 80, 'Unit' => 'meter', 'Status' => 'In Stock'],
        ['ItemID' => 'HD-007', 'Name' => 'Handle Set', 'Category' => 'Hardware', 'InStock' => 2, 'Unit' => 'sets', 'Status' => 'New']
    ];
    
    foreach ($items as $item) {
        // Check if item already exists
        $check = $pdo->prepare("SELECT InventoryItemID FROM inventory_items WHERE ItemID = ?");
        $check->execute([$item['ItemID']]);
        
        if ($check->fetch()) {
            echo "   - Item {$item['ItemID']} already exists, skipping...\n";
            continue;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO inventory_items (ItemID, Name, Category, InStock, Unit, Status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $item['ItemID'],
            $item['Name'],
            $item['Category'],
            $item['InStock'],
            $item['Unit'],
            $item['Status']
        ]);
        
        echo "   ✓ Inserted: {$item['ItemID']} - {$item['Name']}\n";
    }
    
    // Update status based on stock levels
    echo "\nUpdating status based on stock levels...\n";
    $pdo->exec("UPDATE inventory_items SET Status = 'Out of Stock' WHERE InStock = 0");
    $pdo->exec("UPDATE inventory_items SET Status = 'Low Stock' WHERE InStock > 0 AND InStock < 10");
    $pdo->exec("UPDATE inventory_items SET Status = 'In Stock' WHERE InStock >= 10");
    
    echo "✓ Status updated based on stock levels\n";
    
    // Verify
    echo "\n📊 Verification:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_items");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   Total inventory items: $count\n";
    
    $stmt = $pdo->query("SELECT Status, COUNT(*) as count FROM inventory_items GROUP BY Status");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($statuses as $status) {
        echo "   - {$status['Status']}: {$status['count']} items\n";
    }
    
    echo "\n✅ Inventory items table created and populated successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

