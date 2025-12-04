<?php
/**
 * Verify and Fix Password Hash
 */

// Database configuration
$db_host = 'localhost';
$db_user = 'admin_glassify';
$db_pass = 'glassifyAdmin';
$db_name = 'glassify-test';

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = 'sarah.johnson.sales@glassify.com';
$password = 'SalesRep123!';

// Get the account
$stmt = $conn->prepare("SELECT UserID, Email, Password FROM `user` WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "Account found!\n";
    echo "Email: " . $row['Email'] . "\n";
    echo "Current Hash: " . $row['Password'] . "\n\n";
    
    // Verify password
    if (password_verify($password, $row['Password'])) {
        echo "Password VERIFIED: The hash matches 'SalesRep123!'\n";
    } else {
        echo "Password VERIFICATION FAILED: Hash does not match!\n";
        echo "Generating new hash for 'SalesRep123!'...\n\n";
        
        // Generate new hash
        $new_hash = password_hash($password, PASSWORD_BCRYPT);
        echo "New Hash: " . $new_hash . "\n\n";
        
        // Update the password
        $update_stmt = $conn->prepare("UPDATE `user` SET `Password` = ? WHERE `Email` = ?");
        $update_stmt->bind_param("ss", $new_hash, $email);
        
        if ($update_stmt->execute()) {
            echo "SUCCESS: Password hash updated in database!\n";
            echo "You can now login with:\n";
            echo "Email: $email\n";
            echo "Password: $password\n";
        } else {
            echo "ERROR: Failed to update password: " . $update_stmt->error . "\n";
        }
        $update_stmt->close();
    }
} else {
    echo "Account not found!\n";
}

$stmt->close();
$conn->close();
?>

