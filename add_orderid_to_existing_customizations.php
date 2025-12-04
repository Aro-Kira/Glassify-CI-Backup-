<?php
/**
 * Add OrderID to existing customization records for testing
 * This simulates orders that have been placed
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Adding OrderID to existing customization records...\n\n";
    
    // Get SalesRep_ID (first available sales rep)
    $sales_rep_stmt = $pdo->query("SELECT UserID FROM user WHERE Role = 'Sales Representative' LIMIT 1");
    $sales_rep = $sales_rep_stmt->fetch(PDO::FETCH_ASSOC);
    $sales_rep_id = $sales_rep ? $sales_rep['UserID'] : 1;
    
    $customization_tables = [
        'mirror_customization',
        'shower_enclosure_customization',
        'aluminum_doors_customization',
        'aluminum_bathroom_doors_customization'
    ];
    
    $order_counter = 1;
    $total_updated = 0;
    
    foreach ($customization_tables as $table) {
        echo "Processing $table...\n";
        
        // Get all customizations without OrderID
        $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE OrderID IS NULL LIMIT 5");
        $stmt->execute();
        $customizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($customizations as $custom) {
            // Format OrderID (e.g., GI001)
            $order_id_formatted = 'GI' . str_pad($order_counter, 3, '0', STR_PAD_LEFT);
            
            // Get product info
            $product_stmt = $pdo->prepare("SELECT ProductName, Category FROM product WHERE Product_ID = ?");
            $product_stmt->execute([$custom['Product_ID']]);
            $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if customer exists in customer table, if not create it
            $user_id = $custom['Customer_ID']; // This is actually UserID in customization tables
            $check_customer = $pdo->prepare("SELECT Customer_ID FROM customer WHERE UserID = ?");
            $check_customer->execute([$user_id]);
            $customer_row = $check_customer->fetch(PDO::FETCH_ASSOC);
            
            if (!$customer_row) {
                // Create customer record using UserID
                $create_customer = $pdo->prepare("
                    INSERT INTO customer (UserID) VALUES (?)
                ");
                $create_customer->execute([$user_id]);
                $customer_id = $pdo->lastInsertId();
            } else {
                $customer_id = $customer_row['Customer_ID'];
            }
            
            // Create order record
            $order_data = [
                'Customer_ID' => $customer_id,
                'SalesRep_ID' => $sales_rep_id,
                'TotalAmount' => $custom['EstimatePrice'] ?? 0,
                'DeliveryAddress' => '123 Test Street, Manila'
            ];
            
            $insert_order = $pdo->prepare("
                INSERT INTO `order` (Customer_ID, SalesRep_ID, TotalAmount, DeliveryAddress, Status, PaymentStatus, OrderDate)
                VALUES (?, ?, ?, ?, 'Pending', 'Pending', NOW())
            ");
            $insert_order->execute([
                $order_data['Customer_ID'],
                $order_data['SalesRep_ID'],
                $order_data['TotalAmount'],
                $order_data['DeliveryAddress']
            ]);
            
            $order_id_numeric = $pdo->lastInsertId();
            
            // Update customization with OrderID
            $update_stmt = $pdo->prepare("UPDATE `$table` SET OrderID = ? WHERE CustomizationID = ?");
            $update_stmt->execute([$order_id_formatted, $custom['CustomizationID']]);
            
            // Insert into pending_review_orders (no Status column in this table)
            $insert_pending = $pdo->prepare("
                INSERT INTO pending_review_orders 
                (OrderID, ProductName, Address, OrderDate, Customer_ID, SalesRep_ID, TotalQuotation, 
                 Dimension, Shape, Type, Thickness, EdgeWork, FrameType, Engraving, LEDBacklight, DoorOperation, Configuration, FileAttached)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $insert_pending->execute([
                $order_id_formatted,
                $product['ProductName'] ?? 'N/A',
                $order_data['DeliveryAddress'],
                date('Y-m-d H:i:s'),
                $custom['Customer_ID'],
                $sales_rep_id,
                $custom['EstimatePrice'] ?? 0,
                $custom['Dimensions'] ?? 'N/A',
                $custom['GlassShape'] ?? 'N/A',
                $custom['GlassType'] ?? 'N/A',
                $custom['GlassThickness'] ?? 'N/A',
                $custom['EdgeWork'] ?? 'N/A',
                $custom['FrameType'] ?? 'N/A',
                $custom['Engraving'] ?? 'N/A',
                $custom['LEDBacklight'] ?? 'N/A',
                $custom['DoorOperation'] ?? 'N/A',
                $custom['Configuration'] ?? 'N/A',
                'N/A'
            ]);
            
            $total_updated++;
            $order_counter++;
            echo "   ✓ Created order $order_id_formatted for customization #{$custom['CustomizationID']}\n";
        }
    }
    
    echo "\n✅ Completed! Total orders created: $total_updated\n";
    echo "All orders are now in pending_review_orders and will appear in Sales Order page.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

