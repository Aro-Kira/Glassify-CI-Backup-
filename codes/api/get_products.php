<?php
/**
 * Get Products API Endpoint
 * Returns all products from the database
 */

header('Content-Type: application/json');
require_once '../config.php';

try {
    $conn = getDBConnection();
    
    // Get all products from database
    $query = "SELECT product_id, name, category, price, image, added_date, created_at 
              FROM products 
              ORDER BY added_date DESC, created_at DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => (int)$row['product_id'],
            'name' => $row['name'],
            'category' => $row['category'],
            'price' => floatval($row['price']),
            'image' => $row['image'] ?? '',
            'addedDate' => $row['added_date']
        ];
    }
    
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $products
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching products: ' . $e->getMessage()
    ]);
}

?>

