<?php
/**
 * Mark All Notifications as Read API Endpoint
 * Marks all notifications as read in the database
 */

header('Content-Type: application/json');
require_once '../config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Check if notifications table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    }
    
    // For system-generated notifications, we track read status in session/localStorage
    // This is handled on the client side
    
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'All notifications marked as read'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error marking notifications as read: ' . $e->getMessage()
    ]);
}

?>

