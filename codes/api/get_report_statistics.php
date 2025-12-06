<?php
/**
 * Get Report Statistics API Endpoint
 * Returns summary statistics for reports dashboard
 */

header('Content-Type: application/json');
require_once '../config.php';

try {
    $conn = getDBConnection();
    
    // Get filter parameters (optional)
    $typeFilter = isset($_GET['type']) ? $_GET['type'] : 'all';
    $dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : null;
    $dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : null;
    
    // Build WHERE conditions for date filtering
    $dateConditions = [];
    if ($dateFrom) {
        $dateConditions[] = "date >= '" . $conn->real_escape_string($dateFrom) . "'";
    }
    if ($dateTo) {
        $dateConditions[] = "date <= '" . $conn->real_escape_string($dateTo) . "'";
    }
    $dateWhereClause = !empty($dateConditions) ? ' AND ' . implode(' AND ', $dateConditions) : '';
    
    // Get Total Sales (sum of amounts from sales reports)
    $totalSales = 0;
    if ($typeFilter === 'all' || $typeFilter === 'sales') {
        $totalSalesQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM reports WHERE type = 'sales'" . $dateWhereClause;
        $totalSalesResult = $conn->query($totalSalesQuery);
        if ($totalSalesResult) {
            $totalSales = floatval($totalSalesResult->fetch_assoc()['total']);
        }
    }
    
    // Get Items Sold (estimate based on average item price - can be customized)
    // For now, we'll estimate based on sales amount / average price
    $averageItemPrice = 200; // Average item price in PHP
    $itemsSold = $totalSales > 0 ? floor($totalSales / $averageItemPrice) : 0;
    
    // Get New Customers (estimate based on sales reports - can be customized)
    $salesReportsCount = 0;
    $newCustomers = 0;
    if ($typeFilter === 'all' || $typeFilter === 'sales') {
        $salesReportsQuery = "SELECT COUNT(*) as total FROM reports WHERE type = 'sales'" . $dateWhereClause;
        $salesReportsResult = $conn->query($salesReportsQuery);
        if ($salesReportsResult) {
            $salesReportsCount = intval($salesReportsResult->fetch_assoc()['total']);
            $newCustomers = floor($salesReportsCount * 3.2); // Estimate: ~3.2 customers per sales report
        }
    }
    
    // Get Total Orders (count of sales reports)
    $totalOrders = $salesReportsCount;
    
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => [
            'totalSales' => floatval($totalSales),
            'itemsSold' => (int)$itemsSold,
            'newCustomers' => (int)$newCustomers,
            'totalOrders' => (int)$totalOrders
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching report statistics: ' . $e->getMessage()
    ]);
}

?>

