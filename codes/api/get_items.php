<?php
/**
 * Get Items API Endpoint
 * Returns all inventory items from the database
 */

header('Content-Type: application/json');
require_once '../config.php';

try {
    $conn = getDBConnection();
    
    // Get all items from database
    $query = "SELECT item_id, item_code, name, category, stock_quantity, unit, min_threshold, is_new_item 
              FROM items 
              ORDER BY item_id DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $items
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching items: ' . $e->getMessage()
    ]);
}

?>

