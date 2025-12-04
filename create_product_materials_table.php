<?php
/**
 * Create product_materials table to track which raw materials are used for each product
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating product_materials table...\n\n";
    
    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS `product_materials` (
        `ProductMaterialID` int(11) NOT NULL AUTO_INCREMENT,
        `Product_ID` int(11) NOT NULL,
        `InventoryItemID` int(11) NOT NULL,
        `QuantityRequired` decimal(10,2) NOT NULL COMMENT 'Amount of material needed per product unit',
        `Unit` varchar(50) DEFAULT NULL COMMENT 'Unit of measurement',
        `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`ProductMaterialID`),
        KEY `Product_ID` (`Product_ID`),
        KEY `InventoryItemID` (`InventoryItemID`),
        FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE,
        FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql);
    echo "✓ Created table: product_materials\n";
    
    // Get product and inventory IDs for mapping
    $products = $pdo->query("SELECT Product_ID, ProductName, Category FROM product")->fetchAll(PDO::FETCH_ASSOC);
    $inventory = $pdo->query("SELECT InventoryItemID, ItemID, Name, Category FROM inventory_items")->fetchAll(PDO::FETCH_ASSOC);
    
    // Create a mapping array for easier lookup
    $inventory_map = [];
    foreach ($inventory as $inv) {
        $inventory_map[$inv['ItemID']] = $inv;
    }
    
    echo "\nCreating product-material mappings...\n";
    
    $mappings_created = 0;
    
    foreach ($products as $product) {
        $product_id = $product['Product_ID'];
        $category = $product['Category'];
        
        // Define materials needed based on product category
        $materials_needed = [];
        
        if ($category === 'Mirrors') {
            // Mirrors need: Glass, Edge work materials, LED (if applicable), Engraving materials
            $materials_needed = [
                ['ItemID' => 'GL-006', 'Quantity' => 1.0, 'Unit' => 'sqm'], // Mirror Glass
                ['ItemID' => 'AC-001', 'Quantity' => 2.0, 'Unit' => 'meter'], // Rubber Gasket
                ['ItemID' => 'AC-002', 'Quantity' => 0.5, 'Unit' => 'rolls'], // Foam Tape
            ];
        } elseif ($category === 'Shower Enclosure / Partition') {
            // Shower enclosures need: Glass, Aluminum frame, Hardware, Sealant
            $materials_needed = [
                ['ItemID' => 'GL-004', 'Quantity' => 2.5, 'Unit' => 'sqm'], // Tempered Glass Sheet
                ['ItemID' => 'AL-022', 'Quantity' => 4.0, 'Unit' => 'pcs'], // Aluminum Frame
                ['ItemID' => 'AC-003', 'Quantity' => 2.0, 'Unit' => 'tubes'], // Silicone Sealant
                ['ItemID' => 'HD-003', 'Quantity' => 1.0, 'Unit' => 'sets'], // Hinge Set
                ['ItemID' => 'AL-045', 'Quantity' => 1.5, 'Unit' => 'meter'], // Sliding Track
            ];
        } elseif ($category === 'Aluminum Doors') {
            // Aluminum doors need: Glass, Aluminum profiles, Hardware, Track
            $materials_needed = [
                ['ItemID' => 'GL-004', 'Quantity' => 3.0, 'Unit' => 'sqm'], // Tempered Glass Sheet
                ['ItemID' => 'AL-001', 'Quantity' => 6.0, 'Unit' => 'meter'], // Aluminum Extrusion
                ['ItemID' => 'AL-046', 'Quantity' => 2.0, 'Unit' => 'meter'], // Sliding Door Track
                ['ItemID' => 'HD-005', 'Quantity' => 4.0, 'Unit' => 'pcs'], // Roller Guide
                ['ItemID' => 'AC-003', 'Quantity' => 1.0, 'Unit' => 'tubes'], // Silicone Sealant
            ];
        } elseif ($category === 'Aluminum and Bathroom Doors') {
            // Bathroom doors need: Glass, Aluminum frame, Hardware
            $materials_needed = [
                ['ItemID' => 'GL-004', 'Quantity' => 2.0, 'Unit' => 'sqm'], // Tempered Glass Sheet
                ['ItemID' => 'AL-022', 'Quantity' => 3.0, 'Unit' => 'pcs'], // Aluminum Frame
                ['ItemID' => 'HD-001', 'Quantity' => 1.0, 'Unit' => 'sets'], // Door Lock Set
                ['ItemID' => 'HD-002', 'Quantity' => 1.0, 'Unit' => 'pcs'], // Door Handle
                ['ItemID' => 'AC-003', 'Quantity' => 1.0, 'Unit' => 'tubes'], // Silicone Sealant
            ];
        }
        
        // Insert mappings
        foreach ($materials_needed as $material) {
            if (!isset($inventory_map[$material['ItemID']])) {
                echo "   ⚠️  Warning: Inventory item {$material['ItemID']} not found, skipping...\n";
                continue;
            }
            
            $inventory_item_id = $inventory_map[$material['ItemID']]['InventoryItemID'];
            
            // Check if mapping already exists
            $check = $pdo->prepare("SELECT ProductMaterialID FROM product_materials WHERE Product_ID = ? AND InventoryItemID = ?");
            $check->execute([$product_id, $inventory_item_id]);
            
            if ($check->fetch()) {
                continue; // Skip if already exists
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO product_materials (Product_ID, InventoryItemID, QuantityRequired, Unit)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $product_id,
                $inventory_item_id,
                $material['Quantity'],
                $material['Unit']
            ]);
            
            $mappings_created++;
        }
    }
    
    echo "\n✓ Created $mappings_created product-material mappings\n";
    
    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM product_materials");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   Total mappings: $count\n";
    
    echo "\n✅ Product materials table created successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

