<?php
/**
 * Upload Avatar API Endpoint
 * Handles avatar image uploads
 */

header('Content-Type: application/json');
require_once '../config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['avatar'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images are allowed.']);
    exit;
}

// Validate file size (max 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
    exit;
}

try {
    // Create uploads directory if it doesn't exist
    $uploadDir = 'C:\Users\GWAYNE\Pictures\Camera Roll'; 
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    // Get relative path for database storage (relative to web root)
    $relativePath = 'uploads/avatars/' . $filename;
    
    // Update user profile in database
    // For now, update the first user (admin)
    // In a real application, you would get user_id from session
    $conn = getDBConnection();
    
    // First, check if avatar column exists in users table
    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if (!$columnCheck || $columnCheck->num_rows === 0) {
        // Add avatar column if it doesn't exist
        $conn->query("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
    }
    
    // Get the first user's ID and current avatar (if any)
    $userResult = $conn->query("SELECT user_id, avatar FROM users ORDER BY user_id ASC LIMIT 1");
    if (!$userResult || $userResult->num_rows === 0) {
        throw new Exception("No user found in database");
    }
    $userRow = $userResult->fetch_assoc();
    $userId = $userRow['user_id'];
    $oldAvatar = $userRow['avatar'];
    
    // Delete old avatar file if it exists
    if ($oldAvatar && trim($oldAvatar) !== '') {
        $oldAvatarPath = '../' . trim($oldAvatar);
        if (file_exists($oldAvatarPath) && is_file($oldAvatarPath)) {
            @unlink($oldAvatarPath); // Delete old file
        }
    }
    
    // Update user avatar in database
    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("si", $relativePath, $userId);
    
    if (!$stmt->execute()) {
        throw new Exception("Update failed: " . $stmt->error);
    }
    
    // Verify the update was successful by querying again
    $verifyResult = $conn->query("SELECT avatar FROM users WHERE user_id = $userId");
    if ($verifyResult) {
        $verifyRow = $verifyResult->fetch_assoc();
        $savedAvatar = trim($verifyRow['avatar']);
        if ($savedAvatar !== trim($relativePath)) {
            // If verification fails, try one more time
            $stmt2 = $conn->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
            $stmt2->bind_param("si", $relativePath, $userId);
            $stmt2->execute();
            $stmt2->close();
        }
    }
    
    $stmt->close();
    
    // Final verification
    $finalCheck = $conn->query("SELECT avatar FROM users WHERE user_id = $userId");
    $finalRow = $finalCheck->fetch_assoc();
    $finalAvatar = trim($finalRow['avatar']);
    
    $conn->close();
    
    // Double-check file exists
    if (!file_exists($filepath)) {
        throw new Exception("Uploaded file not found at expected location");
    }
    
    // Return success response with avatar URL
    echo json_encode([
        'success' => true,
        'message' => 'Avatar uploaded successfully',
        'data' => [
            'avatar_url' => $relativePath
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error uploading avatar: ' . $e->getMessage()
    ]);
}

?>

