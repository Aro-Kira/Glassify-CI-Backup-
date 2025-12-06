<?php
/**
 * Get Activities API Endpoint
 * Returns recent activities from the database
 */

header('Content-Type: application/json');
require_once '../config.php';

try {
    $conn = getDBConnection();
    
    // Get recent activities, limit to 20 most recent
    $query = "SELECT 
                activity_id,
                action,
                item_name,
                change_description,
                description,
                timestamp,
                DATE_FORMAT(timestamp, '%m/%d/%Y - %h:%i %p') AS formatted_timestamp
              FROM activities 
              ORDER BY timestamp DESC 
              LIMIT 20";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'activity_id' => $row['activity_id'],
            'action' => $row['action'],
            'item_name' => $row['item_name'] ?? '',
            'change_description' => $row['change_description'] ?? '',
            'description' => $row['description'] ?? '',
            'timestamp' => $row['timestamp'],
            'formatted_timestamp' => $row['formatted_timestamp']
        ];
    }
    
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $activities
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching activities: ' . $e->getMessage()
    ]);
}

?>

