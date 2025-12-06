<?php
/**
 * Get Reports API Endpoint
 * Returns all reports from the database
 */

header('Content-Type: application/json');
require_once '../config.php';

try {
    $conn = getDBConnection();
    
    // Get all reports from database
    $query = "SELECT report_id, date, type, description, amount, status, created_at 
              FROM reports 
              ORDER BY date DESC, created_at DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = [
            'id' => (int)$row['report_id'],
            'date' => $row['date'],
            'type' => $row['type'],
            'description' => $row['description'],
            'amount' => floatval($row['amount']),
            'status' => $row['status']
        ];
    }
    
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $reports
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching reports: ' . $e->getMessage()
    ]);
}

?>

