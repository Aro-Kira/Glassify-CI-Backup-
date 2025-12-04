<?php
/**
 * Populate category-specific customization tables with sample data
 * Adds at least 5 entries to each table
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Populating category-specific customization tables...\n\n";
    
    // Get product IDs for each category (we'll reuse products if needed to get 5+ entries)
    $mirror_products = $pdo->query("SELECT Product_ID FROM product WHERE Category = 'Mirrors'")->fetchAll(PDO::FETCH_COLUMN);
    $shower_products = $pdo->query("SELECT Product_ID FROM product WHERE Category = 'Shower Enclosure / Partition'")->fetchAll(PDO::FETCH_COLUMN);
    $aluminum_doors_products = $pdo->query("SELECT Product_ID FROM product WHERE Category = 'Aluminum Doors'")->fetchAll(PDO::FETCH_COLUMN);
    $bathroom_doors_products = $pdo->query("SELECT Product_ID FROM product WHERE Category = 'Aluminum and Bathroom Doors'")->fetchAll(PDO::FETCH_COLUMN);
    
    // Ensure we have at least one product per category
    if (empty($mirror_products) || empty($shower_products) || empty($aluminum_doors_products) || empty($bathroom_doors_products)) {
        echo "❌ Missing products in one or more categories. Please ensure all 4 categories have products.\n";
        exit(1);
    }
    
    // Get customer IDs (use existing customers or create test customers)
    $customer_ids = $pdo->query("SELECT UserID FROM user WHERE Role = 'Customer' LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($customer_ids)) {
        // If no customers, use any user IDs
        $customer_ids = $pdo->query("SELECT UserID FROM user LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
    }
    
    if (empty($customer_ids)) {
        echo "❌ No customers found. Please create customer accounts first.\n";
        exit(1);
    }
    
    // 1. Populate mirror_customization (at least 5 entries)
    echo "📦 Populating mirror_customization...\n";
    $mirror_data = [
        ['Dimensions' => '24" x 18"', 'EdgeWork' => 'polished', 'GlassShape' => 'Rectangle', 'LEDBacklight' => 'Yes', 'Engraving' => 'N/A', 'EstimatePrice' => 2500.00],
        ['Dimensions' => '30" (Diameter)', 'EdgeWork' => 'beveled', 'GlassShape' => 'Circle', 'LEDBacklight' => 'No', 'Engraving' => 'Custom Text', 'EstimatePrice' => 3000.00],
        ['Dimensions' => '36" x 24"', 'EdgeWork' => 'same lang', 'GlassShape' => 'Oval', 'LEDBacklight' => 'Yes', 'Engraving' => 'N/A', 'EstimatePrice' => 2800.00],
        ['Dimensions' => '20" x 16"', 'EdgeWork' => 'polished', 'GlassShape' => 'Rectangle', 'LEDBacklight' => 'No', 'Engraving' => 'N/A', 'EstimatePrice' => 1800.00],
        ['Dimensions' => '28" (Diameter)', 'EdgeWork' => 'beveled', 'GlassShape' => 'Circle', 'LEDBacklight' => 'Yes', 'Engraving' => 'Logo', 'EstimatePrice' => 3200.00],
        ['Dimensions' => '32" x 20"', 'EdgeWork' => 'polished', 'GlassShape' => 'Arch', 'LEDBacklight' => 'No', 'Engraving' => 'N/A', 'EstimatePrice' => 2200.00]
    ];
    
    $mirror_count = 0;
    foreach ($mirror_data as $index => $data) {
        $stmt = $pdo->prepare("
            INSERT INTO mirror_customization 
            (Customer_ID, Product_ID, Dimensions, EdgeWork, GlassShape, LEDBacklight, Engraving, EstimatePrice, Created_Date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $customer_id = $customer_ids[$index % count($customer_ids)];
        $product_id = $mirror_products[$index % count($mirror_products)]; // Reuse products if needed
        
        $stmt->execute([
            $customer_id,
            $product_id,
            $data['Dimensions'],
            $data['EdgeWork'],
            $data['GlassShape'],
            $data['LEDBacklight'],
            $data['Engraving'],
            $data['EstimatePrice']
        ]);
        
        $mirror_count++;
        echo "   ✓ Inserted mirror customization #$mirror_count\n";
    }
    
    // 2. Populate shower_enclosure_customization (at least 5 entries)
    echo "\n📦 Populating shower_enclosure_customization...\n";
    $shower_data = [
        ['Dimensions' => '72" x 36"', 'GlassType' => 'Tempered', 'GlassThickness' => '8mm', 'FrameType' => 'Framed', 'Engraving' => 'N/A', 'DoorOperation' => 'Swing', 'EstimatePrice' => 8500.00],
        ['Dimensions' => '78" x 42"', 'GlassType' => 'Tempered', 'GlassThickness' => '10mm', 'FrameType' => 'Semi-Frameless', 'Engraving' => 'N/A', 'DoorOperation' => 'Sliding', 'EstimatePrice' => 12000.00],
        ['Dimensions' => '80" x 48"', 'GlassType' => 'Tempered', 'GlassThickness' => '12mm', 'FrameType' => 'Frameless', 'Engraving' => 'Custom Pattern', 'DoorOperation' => 'Fixed', 'EstimatePrice' => 15000.00],
        ['Dimensions' => '70" x 32"', 'GlassType' => 'Tempered', 'GlassThickness' => '6mm', 'FrameType' => 'Framed', 'Engraving' => 'N/A', 'DoorOperation' => 'Swing', 'EstimatePrice' => 7500.00],
        ['Dimensions' => '76" x 40"', 'GlassType' => 'Tempered', 'GlassThickness' => '8mm', 'FrameType' => 'Semi-Frameless', 'Engraving' => 'N/A', 'DoorOperation' => 'Sliding', 'EstimatePrice' => 11000.00],
        ['Dimensions' => '84" x 50"', 'GlassType' => 'Tempered', 'GlassThickness' => '10mm', 'FrameType' => 'Frameless', 'Engraving' => 'N/A', 'DoorOperation' => 'Fixed', 'EstimatePrice' => 16000.00]
    ];
    
    $shower_count = 0;
    foreach ($shower_data as $index => $data) {
        $stmt = $pdo->prepare("
            INSERT INTO shower_enclosure_customization 
            (Customer_ID, Product_ID, Dimensions, GlassType, GlassThickness, FrameType, Engraving, DoorOperation, EstimatePrice, Created_Date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $customer_id = $customer_ids[($index + 2) % count($customer_ids)];
        $product_id = $shower_products[$index % count($shower_products)]; // Reuse products if needed
        
        $stmt->execute([
            $customer_id,
            $product_id,
            $data['Dimensions'],
            $data['GlassType'],
            $data['GlassThickness'],
            $data['FrameType'],
            $data['Engraving'],
            $data['DoorOperation'],
            $data['EstimatePrice']
        ]);
        
        $shower_count++;
        echo "   ✓ Inserted shower enclosure customization #$shower_count\n";
    }
    
    // 3. Populate aluminum_doors_customization (at least 5 entries)
    echo "\n📦 Populating aluminum_doors_customization...\n";
    $aluminum_doors_data = [
        ['Dimensions' => '96" x 84"', 'GlassType' => 'Tempered', 'GlassThickness' => '6mm', 'Configuration' => '2-panel slider', 'EstimatePrice' => 15000.00],
        ['Dimensions' => '120" x 90"', 'GlassType' => 'Tempered', 'GlassThickness' => '10mm', 'Configuration' => '3-panel slider', 'EstimatePrice' => 22000.00],
        ['Dimensions' => '144" x 96"', 'GlassType' => 'Tempered', 'GlassThickness' => '10mm', 'Configuration' => '4-panel slider', 'EstimatePrice' => 28000.00],
        ['Dimensions' => '84" x 78"', 'GlassType' => 'Tempered', 'GlassThickness' => '6mm', 'Configuration' => '2-panel slider', 'EstimatePrice' => 14000.00],
        ['Dimensions' => '108" x 84"', 'GlassType' => 'Tempered', 'GlassThickness' => '10mm', 'Configuration' => '3-panel slider', 'EstimatePrice' => 20000.00],
        ['Dimensions' => '132" x 90"', 'GlassType' => 'Tempered', 'GlassThickness' => '10mm', 'Configuration' => '4-panel slider', 'EstimatePrice' => 26000.00]
    ];
    
    $aluminum_doors_count = 0;
    foreach ($aluminum_doors_data as $index => $data) {
        $stmt = $pdo->prepare("
            INSERT INTO aluminum_doors_customization 
            (Customer_ID, Product_ID, Dimensions, GlassType, GlassThickness, Configuration, EstimatePrice, Created_Date)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $customer_id = $customer_ids[($index + 4) % count($customer_ids)];
        $product_id = $aluminum_doors_products[$index % count($aluminum_doors_products)]; // Reuse products if needed
        
        $stmt->execute([
            $customer_id,
            $product_id,
            $data['Dimensions'],
            $data['GlassType'],
            $data['GlassThickness'],
            $data['Configuration'],
            $data['EstimatePrice']
        ]);
        
        $aluminum_doors_count++;
        echo "   ✓ Inserted aluminum doors customization #$aluminum_doors_count\n";
    }
    
    // 4. Populate aluminum_bathroom_doors_customization (at least 5 entries)
    echo "\n📦 Populating aluminum_bathroom_doors_customization...\n";
    $bathroom_doors_data = [
        ['Dimensions' => '80" x 30"', 'FrameType' => 'Framed', 'EstimatePrice' => 6500.00],
        ['Dimensions' => '84" x 32"', 'FrameType' => 'Semi-Frameless', 'EstimatePrice' => 8000.00],
        ['Dimensions' => '88" x 34"', 'FrameType' => 'Frameless', 'EstimatePrice' => 9500.00],
        ['Dimensions' => '78" x 28"', 'FrameType' => 'Framed', 'EstimatePrice' => 6000.00],
        ['Dimensions' => '82" x 30"', 'FrameType' => 'Semi-Frameless', 'EstimatePrice' => 7500.00],
        ['Dimensions' => '86" x 32"', 'FrameType' => 'Frameless', 'EstimatePrice' => 9000.00]
    ];
    
    $bathroom_doors_count = 0;
    foreach ($bathroom_doors_data as $index => $data) {
        $stmt = $pdo->prepare("
            INSERT INTO aluminum_bathroom_doors_customization 
            (Customer_ID, Product_ID, Dimensions, FrameType, EstimatePrice, Created_Date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $customer_id = $customer_ids[($index + 6) % count($customer_ids)];
        $product_id = $bathroom_doors_products[$index % count($bathroom_doors_products)]; // Reuse products if needed
        
        $stmt->execute([
            $customer_id,
            $product_id,
            $data['Dimensions'],
            $data['FrameType'],
            $data['EstimatePrice']
        ]);
        
        $bathroom_doors_count++;
        echo "   ✓ Inserted aluminum bathroom doors customization #$bathroom_doors_count\n";
    }
    
    // Summary
    echo "\n📊 Summary:\n";
    echo "   - mirror_customization: $mirror_count entries\n";
    echo "   - shower_enclosure_customization: $shower_count entries\n";
    echo "   - aluminum_doors_customization: $aluminum_doors_count entries\n";
    echo "   - aluminum_bathroom_doors_customization: $bathroom_doors_count entries\n";
    
    echo "\n✅ Category-specific customization tables populated successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

