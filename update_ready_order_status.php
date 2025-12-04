<?php
/**
 * Update one order in ready_to_approve_orders to have AdminStatus = 'Disapproved'
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking orders in ready_to_approve_orders...\n\n";
    
    // Get all orders in ready_to_approve_orders
    $stmt = $pdo->query("SELECT ReadyOrderID, OrderID, AdminStatus FROM ready_to_approve_orders ORDER BY ReadyOrderID");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orders)) {
        echo "No orders found in ready_to_approve_orders table.\n";
        exit(0);
    }
    
    echo "Found " . count($orders) . " order(s):\n";
    foreach ($orders as $order) {
        echo "- Order ID: {$order['OrderID']}, AdminStatus: " . ($order['AdminStatus'] ?? 'NULL') . "\n";
    }
    
    // Find an order that doesn't have AdminStatus = 'Disapproved' yet
    $orderToUpdate = null;
    foreach ($orders as $order) {
        if ($order['AdminStatus'] !== 'Disapproved') {
            $orderToUpdate = $order;
            break;
        }
    }
    
    // If all are already disapproved, update the first one
    if (!$orderToUpdate) {
        $orderToUpdate = $orders[0];
    }
    
    // Update the order to have AdminStatus = 'Disapproved'
    $stmt = $pdo->prepare("UPDATE ready_to_approve_orders SET AdminStatus = 'Disapproved', AdminReviewed_Date = NOW() WHERE ReadyOrderID = ?");
    $stmt->execute([$orderToUpdate['ReadyOrderID']]);
    
    echo "\n✓ Updated Order ID: {$orderToUpdate['OrderID']} to have AdminStatus = 'Disapproved'\n";
    
    // Verify the update
    $stmt = $pdo->prepare("SELECT OrderID, AdminStatus FROM ready_to_approve_orders WHERE ReadyOrderID = ?");
    $stmt->execute([$orderToUpdate['ReadyOrderID']]);
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✓ Verified: Order {$updated['OrderID']} now has AdminStatus = '{$updated['AdminStatus']}'\n";
    
    echo "\n✅ Update complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

