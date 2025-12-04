<?php
/**
 * Add more raw material items needed to build products
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Adding more raw material items...\n\n";
    
    // Additional raw materials needed for glass products
    $items = [
        // Glass Materials
        ['ItemID' => 'GL-003', 'Name' => 'Float Glass', 'Category' => 'Glass', 'InStock' => 200, 'Unit' => 'sqm', 'Status' => 'In Stock'],
        ['ItemID' => 'GL-004', 'Name' => 'Tempered Glass Sheet', 'Category' => 'Glass', 'InStock' => 150, 'Unit' => 'sqm', 'Status' => 'In Stock'],
        ['ItemID' => 'GL-005', 'Name' => 'Laminated Glass Sheet', 'Category' => 'Glass', 'InStock' => 100, 'Unit' => 'sqm', 'Status' => 'In Stock'],
        ['ItemID' => 'GL-006', 'Name' => 'Mirror Glass', 'Category' => 'Glass', 'InStock' => 80, 'Unit' => 'sqm', 'Status' => 'In Stock'],
        ['ItemID' => 'GL-007', 'Name' => 'Clear Glass Panel', 'Category' => 'Glass', 'InStock' => 250, 'Unit' => 'sqm', 'Status' => 'In Stock'],
        
        // Aluminum Materials
        ['ItemID' => 'AL-001', 'Name' => 'Aluminum Extrusion', 'Category' => 'Aluminum', 'InStock' => 500, 'Unit' => 'meter', 'Status' => 'In Stock'],
        ['ItemID' => 'AL-023', 'Name' => 'Aluminum Profile', 'Category' => 'Aluminum', 'InStock' => 300, 'Unit' => 'meter', 'Status' => 'In Stock'],
        ['ItemID' => 'AL-024', 'Name' => 'Aluminum Corner Bracket', 'Category' => 'Aluminum', 'InStock' => 150, 'Unit' => 'pcs', 'Status' => 'In Stock'],
        ['ItemID' => 'AL-025', 'Name' => 'Aluminum Hinge', 'Category' => 'Aluminum', 'InStock' => 200, 'Unit' => 'pcs', 'Status' => 'In Stock'],
        ['ItemID' => 'AL-046', 'Name' => 'Sliding Door Track', 'Category' => 'Aluminum', 'InStock' => 120, 'Unit' => 'meter', 'Status' => 'In Stock'],
        
        // Hardware Materials
        ['ItemID' => 'HD-001', 'Name' => 'Door Lock Set', 'Category' => 'Hardware', 'InStock' => 50, 'Unit' => 'sets', 'Status' => 'In Stock'],
        ['ItemID' => 'HD-002', 'Name' => 'Door Handle', 'Category' => 'Hardware', 'InStock' => 80, 'Unit' => 'pcs', 'Status' => 'In Stock'],
        ['ItemID' => 'HD-003', 'Name' => 'Hinge Set', 'Category' => 'Hardware', 'InStock' => 100, 'Unit' => 'sets', 'Status' => 'In Stock'],
        ['ItemID' => 'HD-004', 'Name' => 'Door Closer', 'Category' => 'Hardware', 'InStock' => 30, 'Unit' => 'pcs', 'Status' => 'In Stock'],
        ['ItemID' => 'HD-005', 'Name' => 'Roller Guide', 'Category' => 'Hardware', 'InStock' => 200, 'Unit' => 'pcs', 'Status' => 'In Stock'],
        ['ItemID' => 'HD-006', 'Name' => 'Door Stop', 'Category' => 'Hardware', 'InStock' => 150, 'Unit' => 'pcs', 'Status' => 'In Stock'],
        ['ItemID' => 'HD-008', 'Name' => 'Screw Set', 'Category' => 'Hardware', 'InStock' => 500, 'Unit' => 'pcs', 'Status' => 'In Stock'],
        
        // Accessories Materials
        ['ItemID' => 'AC-001', 'Name' => 'Rubber Gasket', 'Category' => 'Accessories', 'InStock' => 300, 'Unit' => 'meter', 'Status' => 'In Stock'],
        ['ItemID' => 'AC-002', 'Name' => 'Foam Tape', 'Category' => 'Accessories', 'InStock' => 250, 'Unit' => 'rolls', 'Status' => 'In Stock'],
        ['ItemID' => 'AC-004', 'Name' => 'Glass Cleaner', 'Category' => 'Accessories', 'InStock' => 100, 'Unit' => 'bottles', 'Status' => 'In Stock'],
        ['ItemID' => 'AC-005', 'Name' => 'Protective Film', 'Category' => 'Accessories', 'InStock' => 180, 'Unit' => 'rolls', 'Status' => 'In Stock'],
        ['ItemID' => 'AC-006', 'Name' => 'Edge Protector', 'Category' => 'Accessories', 'InStock' => 220, 'Unit' => 'pcs', 'Status' => 'In Stock'],
        
        // LED Components (for LED Backlit Mirrors)
        ['ItemID' => 'LED-001', 'Name' => 'LED Strip', 'Category' => 'LED Components', 'InStock' => 150, 'Unit' => 'meter', 'Status' => 'In Stock'],
        ['ItemID' => 'LED-002', 'Name' => 'LED Driver', 'Category' => 'LED Components', 'InStock' => 80, 'Unit' => 'pcs', 'Status' => 'In Stock'],
        ['ItemID' => 'LED-003', 'Name' => 'LED Connector', 'Category' => 'LED Components', 'InStock' => 200, 'Unit' => 'pcs', 'Status' => 'In Stock'],
        
        // Some items with low stock for testing
        ['ItemID' => 'GL-008', 'Name' => 'Frosted Glass', 'Category' => 'Glass', 'InStock' => 8, 'Unit' => 'sqm', 'Status' => 'Low Stock'],
        ['ItemID' => 'AL-026', 'Name' => 'Aluminum Screw', 'Category' => 'Aluminum', 'InStock' => 5, 'Unit' => 'pcs', 'Status' => 'Low Stock'],
    ];
    
    $inserted = 0;
    $skipped = 0;
    
    foreach ($items as $item) {
        // Check if item already exists
        $check = $pdo->prepare("SELECT InventoryItemID FROM inventory_items WHERE ItemID = ?");
        $check->execute([$item['ItemID']]);
        
        if ($check->fetch()) {
            echo "   - Item {$item['ItemID']} already exists, skipping...\n";
            $skipped++;
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
        
        $inserted++;
        echo "   ✓ Inserted: {$item['ItemID']} - {$item['Name']} ({$item['InStock']} {$item['Unit']})\n";
    }
    
    // Update status based on stock levels
    echo "\nUpdating status based on stock levels...\n";
    $pdo->exec("UPDATE inventory_items SET Status = 'Out of Stock' WHERE InStock = 0");
    $pdo->exec("UPDATE inventory_items SET Status = 'Low Stock' WHERE InStock > 0 AND InStock < 10");
    $pdo->exec("UPDATE inventory_items SET Status = 'In Stock' WHERE InStock >= 10");
    
    echo "\n📊 Summary:\n";
    echo "   - Inserted: $inserted items\n";
    echo "   - Skipped: $skipped items\n";
    
    // Show total count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_items");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   - Total inventory items: $total\n";
    
    echo "\n✅ Additional inventory items added successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

