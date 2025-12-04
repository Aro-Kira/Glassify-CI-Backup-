<?php
/**
 * Verify that product data is correctly stored in the database
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Verifying product data...\n\n";
    
    // Check product table
    echo "📦 PRODUCT TABLE:\n";
    $stmt = $pdo->query("SELECT Product_ID, ProductName, Category, Material, Price, Status FROM product ORDER BY Category, ProductName");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($products)) {
        echo "   ❌ No products found!\n";
    } else {
        echo "   ✓ Found " . count($products) . " products\n\n";
        
        $current_category = '';
        foreach ($products as $product) {
            if ($current_category !== $product['Category']) {
                $current_category = $product['Category'];
                echo "   Category: {$current_category}\n";
            }
            echo "      - {$product['ProductName']} (ID: {$product['Product_ID']}, Price: ₱" . number_format($product['Price'], 2) . ")\n";
        }
    }
    
    // Verify categories
    echo "\n📊 CATEGORY VERIFICATION:\n";
    $stmt = $pdo->query("SELECT DISTINCT Category FROM product ORDER BY Category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $expected_categories = [
        'Mirrors',
        'Shower Enclosure / Partition',
        'Aluminum Doors',
        'Aluminum and Bathroom Doors'
    ];
    
    foreach ($expected_categories as $expected) {
        if (in_array($expected, $categories)) {
            echo "   ✓ Category exists: '$expected'\n";
        } else {
            echo "   ❌ Category missing: '$expected'\n";
        }
    }
    
    // Check for unexpected categories
    foreach ($categories as $cat) {
        if (!in_array($cat, $expected_categories)) {
            echo "   ⚠️  Unexpected category found: '$cat'\n";
        }
    }
    
    // Verify customization table structure
    echo "\n🔧 CUSTOMIZATION TABLE STRUCTURE:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM customization");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $required_fields = [
        'CustomizationID',
        'Customer_ID',
        'Product_ID',
        'Dimensions',
        'GlassShape',
        'GlassType',
        'GlassThickness',
        'EdgeWork',
        'FrameType',
        'Engraving',
        'LEDBacklight',
        'DoorOperation',
        'Configuration'
    ];
    
    foreach ($required_fields as $field) {
        if (in_array($field, $columns)) {
            echo "   ✓ Field exists: $field\n";
        } else {
            echo "   ❌ Field missing: $field\n";
        }
    }
    
    // Check customization data count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customization");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "\n   Customization records: $count\n";
    
    echo "\n✅ Verification completed!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

