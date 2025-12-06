<?php
/**
 * Mark Notification as Read API Endpoint
 * Marks a notification as read in the database
 */

header('Content-Type: application/json');
require_once '../config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// If JSON input is not available, try POST data
if (!$input) {
    $input = $_POST;
}

// Validate required fields
if (!isset($input['notificationId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
    exit;
}

$notificationId = $input['notificationId'];

try {
    $conn = getDBConnection();
    
    // Check if notifications table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    
    if ($tableCheck && $tableCheck->num_rows > 0) {
        // If notification ID is numeric, it's from the notifications table
        if (is_numeric($notificationId)) {
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
            $stmt->bind_param("i", $notificationId);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // For system-generated notifications (report_, lowstock_, product_, activity_),
    // we don't need to update anything as they're generated on-the-fly
    
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Notification marked as read'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error marking notification as read: ' . $e->getMessage()
    ]);
}

?>

