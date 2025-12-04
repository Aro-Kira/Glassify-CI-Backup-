<?php
/**
 * Create Sales Representative Account
 * This script creates the account directly in the database
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

// Prepare the INSERT statement
$first_name = 'Sarah';
$middle_name = 'Marie';
$last_name = 'Johnson';
$email = 'sarah.johnson.sales@glassify.com';
$password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // Password: SalesRep123!
$phone = '09187654321';
$role = 'Sales Representative';
$status = 'Active';

// Check if email already exists
$check_stmt = $conn->prepare("SELECT UserID FROM `user` WHERE Email = ?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo "Error: Email '$email' already exists in the database.\n";
    $check_stmt->close();
    $conn->close();
    exit;
}
$check_stmt->close();

// Insert the account
$stmt = $conn->prepare("INSERT INTO `user` (`UserID`, `First_Name`, `Last_Name`, `Middle_Name`, `Email`, `Password`, `PhoneNum`, `Role`, `Status`, `Date_Created`, `Date_Updated`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
$stmt->bind_param("ssssssss", $first_name, $last_name, $middle_name, $email, $password_hash, $phone, $role, $status);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    echo "SUCCESS: Sales Representative account created!\n\n";
    echo "Account Details:\n";
    echo "================\n";
    echo "UserID: $user_id\n";
    echo "Name: $first_name $middle_name $last_name\n";
    echo "Email: $email\n";
    echo "Password: SalesRep123!\n";
    echo "Phone: $phone\n";
    echo "Role: $role\n";
    echo "Status: $status\n\n";
    echo "The account has been stored in the 'user' table of the 'glassify-test' database.\n";
} else {
    echo "Error: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?>

