<?php
/**
 * Migration script to move existing orders from order_page to status-specific tables
 * Run this once after creating the new status tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting migration...\n";
    
    // Get all orders from order_page
    $stmt = $pdo->query("SELECT * FROM order_page");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $pending_count = 0;
    $awaiting_count = 0;
    $ready_count = 0;
    
    foreach ($orders as $order) {
        $status = $order['Status'];
        
        // Prepare order data (remove OrderPageID and Status)
        $order_data = [
            'OrderID' => $order['OrderID'],
            'ProductName' => $order['ProductName'],
            'Address' => $order['Address'],
            'OrderDate' => $order['OrderDate'],
            'Shape' => $order['Shape'],
            'Dimension' => $order['Dimension'],
            'Type' => $order['Type'],
            'Thickness' => $order['Thickness'],
            'EdgeWork' => $order['EdgeWork'],
            'FrameType' => $order['FrameType'],
            'Engraving' => $order['Engraving'],
            'FileAttached' => $order['FileAttached'],
            'TotalQuotation' => $order['TotalQuotation'],
            'Customer_ID' => $order['Customer_ID'],
            'SalesRep_ID' => $order['SalesRep_ID']
        ];
        
        try {
            if ($status === 'Pending Review') {
                // Insert into pending_review_orders
                $sql = "INSERT INTO pending_review_orders (" . implode(', ', array_keys($order_data)) . ") VALUES (:" . implode(', :', array_keys($order_data)) . ")";
                $stmt = $pdo->prepare($sql);
                foreach ($order_data as $key => $value) {
                    $stmt->bindValue(':' . $key, $value);
                }
                $stmt->execute();
                $pending_count++;
                
            } elseif ($status === 'Awaiting Admin') {
                // Insert into awaiting_admin_orders
                $order_data['RequestedBy_SalesRep_ID'] = $order['SalesRep_ID'];
                $order_data['Requested_Date'] = date('Y-m-d H:i:s');
                $sql = "INSERT INTO awaiting_admin_orders (" . implode(', ', array_keys($order_data)) . ") VALUES (:" . implode(', :', array_keys($order_data)) . ")";
                $stmt = $pdo->prepare($sql);
                foreach ($order_data as $key => $value) {
                    $stmt->bindValue(':' . $key, $value);
                }
                $stmt->execute();
                $awaiting_count++;
                
            } elseif ($status === 'Ready to Approve') {
                // Insert into ready_to_approve_orders
                // Assume admin has already reviewed (you may need to adjust this)
                $order_data['AdminStatus'] = 'Approved'; // Default to Approved, adjust as needed
                $order_data['AdminReviewed_Date'] = date('Y-m-d H:i:s');
                $sql = "INSERT INTO ready_to_approve_orders (" . implode(', ', array_keys($order_data)) . ") VALUES (:" . implode(', :', array_keys($order_data)) . ")";
                $stmt = $pdo->prepare($sql);
                foreach ($order_data as $key => $value) {
                    $stmt->bindValue(':' . $key, $value);
                }
                $stmt->execute();
                $ready_count++;
            }
        } catch (PDOException $e) {
            echo "Error migrating order {$order['OrderID']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "Migration completed!\n";
    echo "Pending Review: $pending_count orders\n";
    echo "Awaiting Admin: $awaiting_count orders\n";
    echo "Ready to Approve: $ready_count orders\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}

