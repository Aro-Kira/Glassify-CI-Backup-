<?php
/**
 * Check User Password and Reset Script
 * Usage: Access via browser: http://localhost/Glassify-CI/check_user_password.php?userid=6
 */

// Get user ID from URL parameter
$user_id = isset($_GET['userid']) ? intval($_GET['userid']) : 6;

if ($user_id <= 0) {
    die("Error: Invalid UserID. Please provide a valid user ID.\n");
}

// Database configuration
$hostname = 'localhost';
$username = 'admin_glassify';
$password = 'glassifyAdmin';
$database = 'latest_glassifydb';

// Connect to database
$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "<html><head><title>User Password Info</title></head><body>";
echo "<h2>User Information for UserID: $user_id</h2>";

// Get user information
$result = $conn->query("SELECT UserID, First_Name, Last_Name, Email, Role, Password FROM `user` WHERE UserID = $user_id");

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><td><strong>UserID:</strong></td><td>" . htmlspecialchars($user['UserID']) . "</td></tr>";
    echo "<tr><td><strong>Name:</strong></td><td>" . htmlspecialchars($user['First_Name'] . ' ' . $user['Last_Name']) . "</td></tr>";
    echo "<tr><td><strong>Email:</strong></td><td>" . htmlspecialchars($user['Email']) . "</td></tr>";
    echo "<tr><td><strong>Role:</strong></td><td>" . htmlspecialchars($user['Role']) . "</td></tr>";
    echo "<tr><td><strong>Password Hash:</strong></td><td><code>" . htmlspecialchars($user['Password']) . "</code></td></tr>";
    echo "</table>";
    
    echo "<br><hr><br>";
    echo "<h3>⚠️ Important Information:</h3>";
    echo "<p>Passwords are stored as <strong>hashed values</strong> (using bcrypt/password_hash) and <strong>cannot be retrieved in plain text</strong>.</p>";
    echo "<p>This is a security feature - even database administrators cannot see the original password.</p>";
    
    echo "<br><h3>Reset Password:</h3>";
    echo "<p>If you forgot the password, you can reset it using one of these methods:</p>";
    echo "<ol>";
    echo "<li><strong>Use the Forgot Password feature</strong> on the login page (if email is set up)</li>";
    echo "<li><strong>Reset via SQL:</strong> Use the form below to set a new password</li>";
    echo "</ol>";
    
    // Password reset form
    if (isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'];
        if (empty($new_password)) {
            echo "<p style='color: red;'>Error: Password cannot be empty!</p>";
        } else {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password in database
            $update_query = "UPDATE `user` SET Password = ? WHERE UserID = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'><strong>✓ Password successfully reset!</strong></p>";
                echo "<p>New password: <strong>" . htmlspecialchars($new_password) . "</strong></p>";
                echo "<p style='color: orange;'><em>Please save this password securely and change it after logging in.</em></p>";
            } else {
                echo "<p style='color: red;'>Error resetting password: " . $conn->error . "</p>";
            }
            $stmt->close();
        }
    }
    
    echo "<form method='POST' style='margin-top: 20px; padding: 20px; border: 1px solid #ccc; background: #f9f9f9;'>";
    echo "<h4>Reset Password for UserID $user_id:</h4>";
    echo "<label>New Password: <input type='password' name='new_password' required style='padding: 5px; width: 200px;'></label><br><br>";
    echo "<button type='submit' name='reset_password' style='padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer;'>Reset Password</button>";
    echo "</form>";
    
} else {
    echo "<p style='color: red;'>User with UserID $user_id not found.</p>";
}

echo "</body></html>";
$conn->close();
