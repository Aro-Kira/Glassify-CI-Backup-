<?php
/**
 * Get Notifications API Endpoint
 * Returns notifications from the database and generates system notifications
 */

header('Content-Type: application/json');
require_once '../config.php';

try {
    $conn = getDBConnection();
    
    $notifications = [];
    
    // Get notifications from notifications table if it exists
    // First, check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $query = "SELECT notification_id, message, type, is_read, created_at 
                  FROM notifications 
                  ORDER BY created_at DESC 
                  LIMIT 20";
        $result = $conn->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $notifications[] = [
                    'id' => (int)$row['notification_id'],
                    'message' => $row['message'],
                    'type' => $row['type'],
                    'read' => (bool)$row['is_read'],
                    'time' => getTimeAgo($row['created_at']),
                    'timestamp' => $row['created_at']
                ];
            }
        }
    }
    
    // Generate system notifications from activities, reports, and items
    // Get recent completed reports
    $reportsQuery = "SELECT report_id, description, type, date, created_at 
                     FROM reports 
                     WHERE status = 'completed' 
                     AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                     ORDER BY created_at DESC 
                     LIMIT 5";
    $reportsResult = $conn->query($reportsQuery);
    if ($reportsResult) {
        while ($row = $reportsResult->fetch_assoc()) {
            $notifications[] = [
                'id' => 'report_' . $row['report_id'],
                'message' => 'New report generated: ' . $row['description'],
                'type' => 'report',
                'read' => false,
                'time' => getTimeAgo($row['created_at']),
                'timestamp' => $row['created_at']
            ];
        }
    }
    
    // Get low stock alerts
    $lowStockQuery = "SELECT item_id, name, stock_quantity, min_threshold 
                      FROM items 
                      WHERE stock_quantity > 0 
                      AND stock_quantity < min_threshold
                      ORDER BY (min_threshold - stock_quantity) DESC
                      LIMIT 5";
    $lowStockResult = $conn->query($lowStockQuery);
    if ($lowStockResult) {
        while ($row = $lowStockResult->fetch_assoc()) {
            $notifications[] = [
                'id' => 'lowstock_' . $row['item_id'],
                'message' => 'Low stock alert: ' . $row['name'] . ' is below threshold',
                'type' => 'inventory',
                'read' => false,
                'time' => 'Just now',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    // Get recently added products
    $productsQuery = "SELECT product_id, name, created_at 
                      FROM products 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                      ORDER BY created_at DESC 
                      LIMIT 3";
    $productsResult = $conn->query($productsQuery);
    if ($productsResult) {
        while ($row = $productsResult->fetch_assoc()) {
            $notifications[] = [
                'id' => 'product_' . $row['product_id'],
                'message' => 'New product added: ' . $row['name'],
                'type' => 'product',
                'read' => false,
                'time' => getTimeAgo($row['created_at']),
                'timestamp' => $row['created_at']
            ];
        }
    }
    
    // Get recent activities that might be notifications
    $activitiesQuery = "SELECT activity_id, action, item_name, description, timestamp 
                        FROM activities 
                        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                        AND action IN ('Item created', 'Stock added', 'Threshold updated')
                        ORDER BY timestamp DESC 
                        LIMIT 3";
    $activitiesResult = $conn->query($activitiesQuery);
    if ($activitiesResult) {
        while ($row = $activitiesResult->fetch_assoc()) {
            $message = $row['action'];
            if ($row['item_name']) {
                $message .= ': ' . $row['item_name'];
            }
            if ($row['description'] && $row['description'] !== 'System') {
                $message .= ' - ' . $row['description'];
            }
            
            $notifications[] = [
                'id' => 'activity_' . $row['activity_id'],
                'message' => $message,
                'type' => 'inventory',
                'read' => false,
                'time' => getTimeAgo($row['timestamp']),
                'timestamp' => $row['timestamp']
            ];
        }
    }
    
    // Sort all notifications by timestamp (newest first)
    usort($notifications, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    // Remove duplicates and limit to 20
    $uniqueNotifications = [];
    $seenIds = [];
    foreach ($notifications as $notification) {
        if (!in_array($notification['id'], $seenIds)) {
            $uniqueNotifications[] = $notification;
            $seenIds[] = $notification['id'];
            if (count($uniqueNotifications) >= 20) {
                break;
            }
        }
    }
    
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $uniqueNotifications
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching notifications: ' . $e->getMessage()
    ]);
}

// Helper function to get time ago
function getTimeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}

?>

