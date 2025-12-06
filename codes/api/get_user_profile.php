<?php
/**
 * Get User Profile API Endpoint
 * Returns current user profile from the database
 */

header('Content-Type: application/json');
require_once '../config.php';

try {
    $conn = getDBConnection();
    
    // For now, get the first user (admin user)
    // In a real application, you would get the logged-in user from session
    // First, check if avatar column exists
    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    $hasAvatarColumn = $columnCheck && $columnCheck->num_rows > 0;
    
    $query = "SELECT user_id, name, email, role, member_since, created_at" . 
             ($hasAvatarColumn ? ", avatar" : "") . 
             " FROM users 
              ORDER BY user_id ASC 
              LIMIT 1";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    if ($result->num_rows === 0) {
        throw new Exception("No user found");
    }
    
    $user = $result->fetch_assoc();
    
    // Format member_since date
    $memberSince = '';
    if ($user['member_since']) {
        $date = new DateTime($user['member_since']);
        $memberSince = $date->format('F Y');
    }
    
    // Get avatar path if it exists
    $avatar = null;
    if ($hasAvatarColumn && isset($user['avatar']) && $user['avatar'] && trim($user['avatar']) !== '') {
        $avatarPath = trim($user['avatar']);
        // Always return the path from database - let the browser handle missing files
        // This ensures the avatar persists even if there's a temporary file system issue
        $avatar = $avatarPath;
    }
    
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => [
            'user_id' => (int)$user['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'member_since' => $memberSince,
            'member_since_raw' => $user['member_since'],
            'avatar' => $avatar
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching user profile: ' . $e->getMessage()
    ]);
}

?>

