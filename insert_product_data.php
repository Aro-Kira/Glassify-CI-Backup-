<?php
/**
 * Insert the 4 product categories exactly as specified
 * Categories:
 * 1. Mirrors
 * 2. Shower Enclosure / Partition
 * 3. Aluminum Doors
 * 4. Aluminum and Bathroom Doors
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Inserting product categories...\n\n";
    
    // Define the 4 product categories with sample products
    $products = [
        // Category 1: Mirrors
        [
            'ProductName' => 'Standard Mirror',
            'Category' => 'Mirrors',
            'Material' => 'Glass',
            'Price' => 1500.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        [
            'ProductName' => 'LED Backlit Mirror',
            'Category' => 'Mirrors',
            'Material' => 'Glass',
            'Price' => 2500.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        [
            'ProductName' => 'Custom Shape Mirror',
            'Category' => 'Mirrors',
            'Material' => 'Glass',
            'Price' => 2000.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        
        // Category 2: Shower Enclosure / Partition
        [
            'ProductName' => 'Framed Shower Enclosure',
            'Category' => 'Shower Enclosure / Partition',
            'Material' => 'Glass',
            'Price' => 8000.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        [
            'ProductName' => 'Semi-Frameless Shower Partition',
            'Category' => 'Shower Enclosure / Partition',
            'Material' => 'Glass',
            'Price' => 10000.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        [
            'ProductName' => 'Frameless Shower Enclosure',
            'Category' => 'Shower Enclosure / Partition',
            'Material' => 'Glass',
            'Price' => 12000.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        
        // Category 3: Aluminum Doors
        [
            'ProductName' => '2-Panel Aluminum Sliding Door',
            'Category' => 'Aluminum Doors',
            'Material' => 'Aluminum',
            'Price' => 15000.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        [
            'ProductName' => '3-Panel Aluminum Sliding Door',
            'Category' => 'Aluminum Doors',
            'Material' => 'Aluminum',
            'Price' => 20000.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        [
            'ProductName' => '4-Panel Aluminum Sliding Door',
            'Category' => 'Aluminum Doors',
            'Material' => 'Aluminum',
            'Price' => 25000.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        
        // Category 4: Aluminum and Bathroom Doors
        [
            'ProductName' => 'Aluminum Bathroom Door - Framed',
            'Category' => 'Aluminum and Bathroom Doors',
            'Material' => 'Aluminum',
            'Price' => 6000.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        [
            'ProductName' => 'Aluminum Bathroom Door - Semi-Frameless',
            'Category' => 'Aluminum and Bathroom Doors',
            'Material' => 'Aluminum',
            'Price' => 7500.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ],
        [
            'ProductName' => 'Aluminum Bathroom Door - Frameless',
            'Category' => 'Aluminum and Bathroom Doors',
            'Material' => 'Aluminum',
            'Price' => 9000.00,
            'ImageUrl' => null,
            'Status' => 'In Stock'
        ]
    ];
    
    // Insert products
    $inserted_count = 0;
    foreach ($products as $product) {
        $stmt = $pdo->prepare("
            INSERT INTO `product` 
            (ProductName, Category, Material, Price, ImageUrl, DateAdded, Status) 
            VALUES (?, ?, ?, ?, ?, NOW(), ?)
        ");
        
        $stmt->execute([
            $product['ProductName'],
            $product['Category'], // Exact category name as specified
            $product['Material'],
            $product['Price'],
            $product['ImageUrl'],
            $product['Status']
        ]);
        
        $inserted_count++;
        echo "✓ Inserted: {$product['ProductName']} ({$product['Category']})\n";
    }
    
    echo "\n✅ Successfully inserted $inserted_count products across 4 categories:\n";
    echo "   - Mirrors\n";
    echo "   - Shower Enclosure / Partition\n";
    echo "   - Aluminum Doors\n";
    echo "   - Aluminum and Bathroom Doors\n";
    
    // Verify categories
    echo "\n📊 Category Verification:\n";
    $stmt = $pdo->query("SELECT Category, COUNT(*) as count FROM product GROUP BY Category ORDER BY Category");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($categories as $cat) {
        echo "   - {$cat['Category']}: {$cat['count']} product(s)\n";
    }
    
    echo "\n✅ Product data insertion completed!\n";
    echo "\nNote: The customization table will be populated when customers create customizations.\n";
    echo "All product fields (LEDBacklight, DoorOperation, Configuration) are ready in the schema.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

