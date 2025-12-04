<?php
/**
 * Sync all customization records with OrderID to pending_review_orders
 * This ensures all orders from customization tables appear in the sales order page
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Syncing customization records to pending_review_orders...\n\n";
    
    $customization_tables = [
        'mirror_customization',
        'shower_enclosure_customization',
        'aluminum_doors_customization',
        'aluminum_bathroom_doors_customization'
    ];
    
    $total_synced = 0;
    
    foreach ($customization_tables as $table) {
        echo "Processing $table...\n";
        
        // Get all customizations with OrderID that are not yet in pending_review_orders
        $stmt = $pdo->prepare("
            SELECT c.*, p.ProductName, p.Category, o.SalesRep_ID, o.DeliveryAddress, o.OrderDate as OrderDateFromOrder, o.TotalAmount
            FROM `$table` c
            LEFT JOIN product p ON p.Product_ID = c.Product_ID
            LEFT JOIN `order` o ON o.OrderID = CAST(SUBSTRING(c.OrderID, 3) AS UNSIGNED)
            WHERE c.OrderID IS NOT NULL
            AND NOT EXISTS (
                SELECT 1 FROM pending_review_orders pro 
                WHERE pro.OrderID = c.OrderID
            )
        ");
        $stmt->execute();
        $customizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($customizations as $custom) {
            // Get order details
            $order_stmt = $pdo->prepare("
                SELECT SalesRep_ID, DeliveryAddress, OrderDate, TotalAmount
                FROM `order`
                WHERE OrderID = CAST(SUBSTRING(?, 3) AS UNSIGNED)
            ");
            $order_stmt->execute([$custom['OrderID']]);
            $order = $order_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                echo "   ⚠️  Order not found for OrderID: {$custom['OrderID']}\n";
                continue;
            }
            
            // Prepare data for pending_review_orders
            $order_data = [
                'OrderID' => $custom['OrderID'],
                'ProductName' => $custom['ProductName'] ?? 'N/A',
                'Address' => $order['DeliveryAddress'] ?? 'N/A',
                'OrderDate' => $order['OrderDate'] ?? date('Y-m-d H:i:s'),
                'Customer_ID' => $custom['Customer_ID'],
                'SalesRep_ID' => $order['SalesRep_ID'],
                'Status' => 'Pending Review',
                'TotalQuotation' => $order['TotalAmount'] ?? ($custom['EstimatePrice'] ?? 0),
                'Dimension' => $custom['Dimensions'] ?? 'N/A',
                'Shape' => $custom['GlassShape'] ?? 'N/A',
                'Type' => $custom['GlassType'] ?? 'N/A',
                'Thickness' => $custom['GlassThickness'] ?? 'N/A',
                'EdgeWork' => $custom['EdgeWork'] ?? 'N/A',
                'FrameType' => $custom['FrameType'] ?? 'N/A',
                'Engraving' => $custom['Engraving'] ?? 'N/A',
                'LEDBacklight' => $custom['LEDBacklight'] ?? 'N/A',
                'DoorOperation' => $custom['DoorOperation'] ?? 'N/A',
                'Configuration' => $custom['Configuration'] ?? 'N/A',
                'FileAttached' => 'N/A', // DesignRef not in all tables
                'Category' => $custom['Category'] ?? 'N/A'
            ];
            
            // Insert into pending_review_orders
            $insert_stmt = $pdo->prepare("
                INSERT INTO pending_review_orders 
                (OrderID, ProductName, Address, OrderDate, Customer_ID, SalesRep_ID, Status, TotalQuotation, 
                 Dimension, Shape, Type, Thickness, EdgeWork, FrameType, Engraving, LEDBacklight, DoorOperation, Configuration, FileAttached, Category)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $insert_stmt->execute([
                $order_data['OrderID'],
                $order_data['ProductName'],
                $order_data['Address'],
                $order_data['OrderDate'],
                $order_data['Customer_ID'],
                $order_data['SalesRep_ID'],
                $order_data['Status'],
                $order_data['TotalQuotation'],
                $order_data['Dimension'],
                $order_data['Shape'],
                $order_data['Type'],
                $order_data['Thickness'],
                $order_data['EdgeWork'],
                $order_data['FrameType'],
                $order_data['Engraving'],
                $order_data['LEDBacklight'],
                $order_data['DoorOperation'],
                $order_data['Configuration'],
                $order_data['FileAttached'],
                $order_data['Category']
            ]);
            
            $total_synced++;
            echo "   ✓ Synced OrderID: {$custom['OrderID']}\n";
        }
    }
    
    echo "\n✅ Sync completed! Total orders synced: $total_synced\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

