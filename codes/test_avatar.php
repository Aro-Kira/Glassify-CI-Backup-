<?php
/**
 * Avatar Debugging Test Script
 * Visit this page in your browser to check avatar status
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Avatar Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        img { max-width: 200px; margin-top: 10px; border: 2px solid #ddd; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Avatar Debugging Test</h1>
    
    <?php
    try {
        $conn = getDBConnection();
        
        // Check if avatar column exists
        echo "<h2>1. Database Column Check</h2>";
        $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
        if ($columnCheck && $columnCheck->num_rows > 0) {
            echo "<p class='success'>✓ Avatar column exists</p>";
        } else {
            echo "<p class='error'>✗ Avatar column does NOT exist</p>";
            echo "<p class='info'>Adding avatar column...</p>";
            if ($conn->query("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL")) {
                echo "<p class='success'>✓ Avatar column added successfully</p>";
            } else {
                echo "<p class='error'>✗ Failed to add avatar column: " . $conn->error . "</p>";
            }
        }
        
        // Get user data
        echo "<h2>2. User Data Check</h2>";
        $result = $conn->query("SELECT user_id, name, email, avatar FROM users ORDER BY user_id ASC LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<pre>";
            echo "User ID: " . htmlspecialchars($user['user_id']) . "\n";
            echo "Name: " . htmlspecialchars($user['name']) . "\n";
            echo "Email: " . htmlspecialchars($user['email']) . "\n";
            echo "Avatar: " . ($user['avatar'] ? htmlspecialchars($user['avatar']) : 'NULL') . "\n";
            echo "</pre>";
            
            if ($user['avatar']) {
                $avatarPath = __DIR__ . '/' . $user['avatar'];
                echo "<h2>3. File System Check</h2>";
                if (file_exists($avatarPath)) {
                    echo "<p class='success'>✓ Avatar file exists</p>";
                    echo "<p>File path: " . htmlspecialchars($avatarPath) . "</p>";
                    echo "<p>File size: " . filesize($avatarPath) . " bytes</p>";
                    echo "<p>File readable: " . (is_readable($avatarPath) ? "Yes" : "No") . "</p>";
                    
                    echo "<h2>4. Image Display Test</h2>";
                    $webPath = $user['avatar'];
                    echo "<p>Web path: <code>" . htmlspecialchars($webPath) . "</code></p>";
                    echo "<img src='" . htmlspecialchars($webPath) . "' alt='Avatar' onerror='this.style.border=\"3px solid red\"; this.alt=\"Image failed to load\";'>";
                } else {
                    echo "<p class='error'>✗ Avatar file does NOT exist</p>";
                    echo "<p>Expected path: " . htmlspecialchars($avatarPath) . "</p>";
                    echo "<p class='info'>The database has a path, but the file is missing. This might be why the avatar disappears.</p>";
                }
            } else {
                echo "<h2>3. Avatar Status</h2>";
                echo "<p class='error'>✗ No avatar path in database</p>";
                echo "<p class='info'>Upload an avatar first, then check again.</p>";
            }
        } else {
            echo "<p class='error'>✗ No user found in database</p>";
        }
        
        // Check uploads directory
        echo "<h2>5. Uploads Directory Check</h2>";
        $uploadsDir = __DIR__ . '/uploads/avatars/';
        if (file_exists($uploadsDir)) {
            echo "<p class='success'>✓ Uploads directory exists</p>";
            echo "<p>Path: " . htmlspecialchars($uploadsDir) . "</p>";
            echo "<p>Writable: " . (is_writable($uploadsDir) ? "Yes" : "No") . "</p>";
            
            // List files
            $files = glob($uploadsDir . '*');
            if (count($files) > 0) {
                echo "<p>Files in directory (" . count($files) . "):</p>";
                echo "<ul>";
                foreach ($files as $file) {
                    $filename = basename($file);
                    echo "<li>" . htmlspecialchars($filename) . " (" . filesize($file) . " bytes)</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='info'>No files in uploads directory</p>";
            }
        } else {
            echo "<p class='error'>✗ Uploads directory does NOT exist</p>";
            echo "<p>Expected path: " . htmlspecialchars($uploadsDir) . "</p>";
            echo "<p class='info'>The directory should be created automatically when you upload an avatar.</p>";
        }
        
        // Test API endpoint
        echo "<h2>6. API Endpoint Test</h2>";
        $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/get_user_profile.php';
        echo "<p>API URL: <code>" . htmlspecialchars($apiUrl) . "</code></p>";
        echo "<p><a href='" . htmlspecialchars($apiUrl) . "' target='_blank'>Test API (opens in new tab)</a></p>";
        
        $conn->close();
        
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
    
    <h2>7. Next Steps</h2>
    <ol>
        <li>If avatar column doesn't exist → It should be created automatically</li>
        <li>If database has NULL → Upload an avatar image</li>
        <li>If file doesn't exist → Check file permissions and uploads directory</li>
        <li>If image doesn't display → Check browser console for errors</li>
    </ol>
    
    <p><a href="index.php">← Back to Dashboard</a></p>
</body>
</html>

