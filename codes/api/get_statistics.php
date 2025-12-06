<?php
/**
 * Get Statistics API Endpoint
 * Returns real-time statistics for the inventory dashboard
 */

header('Content-Type: application/json');
require_once '../config.php';

try {
    $conn = getDBConnection();
    
    // Get Total Items count
    $totalItemsQuery = "SELECT COUNT(*) as total FROM items";
    $totalItemsResult = $conn->query($totalItemsQuery);
    $totalItems = $totalItemsResult->fetch_assoc()['total'];
    
    // Get Low Stock Alerts count (items where stock_quantity < min_threshold and stock_quantity > 0)
    $lowStockQuery = "SELECT COUNT(*) as total FROM items WHERE stock_quantity > 0 AND stock_quantity < min_threshold";
    $lowStockResult = $conn->query($lowStockQuery);
    $lowStockCount = $lowStockResult->fetch_assoc()['total'];
    
    // Get New Items count (items marked as new)
    $newItemsQuery = "SELECT COUNT(*) as total FROM items WHERE is_new_item = TRUE";
    $newItemsResult = $conn->query($newItemsQuery);
    $newItemsCount = $newItemsResult->fetch_assoc()['total'];
    
    // Get Recent Requests count (pending reports or recent activities)
    // Using reports with status 'pending' or recent activities from last 7 days
    $recentRequestsQuery = "SELECT COUNT(*) as total FROM reports WHERE status = 'pending' AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $recentRequestsResult = $conn->query($recentRequestsQuery);
    $recentRequestsCount = $recentRequestsResult->fetch_assoc()['total'];
    
    // If no recent requests from reports, count recent activities instead
    if ($recentRequestsCount == 0) {
        $activitiesQuery = "SELECT COUNT(*) as total FROM activities WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $activitiesResult = $conn->query($activitiesQuery);
        $recentRequestsCount = $activitiesResult->fetch_assoc()['total'];
    }
    
    // Get Total Products count
    $totalProductsQuery = "SELECT COUNT(*) as total FROM products";
    $totalProductsResult = $conn->query($totalProductsQuery);
    $totalProducts = $totalProductsResult->fetch_assoc()['total'];
    
    // Get Returned Items count (using recent requests as returned items for now)
    // This can be customized based on your business logic
    $returnedItems = $recentRequestsCount;
    
    $conn->close();
    
    // Return success response with all statistics
    echo json_encode([
        'success' => true,
        'data' => [
            'totalItems' => (int)$totalItems,
            'lowStockAlerts' => (int)$lowStockCount,
            'newItems' => (int)$newItemsCount,
            'recentRequests' => (int)$recentRequestsCount,
            'totalProducts' => (int)$totalProducts,
            'returnedItems' => (int)$returnedItems
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching statistics: ' . $e->getMessage()
    ]);
}

?>

