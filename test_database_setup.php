<?php
/**
 * Database Verification Test Script
 * Run this to verify your database is set up correctly
 * 
 * Usage: Access via browser:
 * http://localhost/Glassify-CI/test_database_setup.php
 */

// Database configuration - adjust if needed
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'latest_glassifydb';

// Connect to database
$db = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db->connect_error) {
    die("❌ Connection failed: " . $db->connect_error);
}

echo "<h2>🔍 Database Verification Test</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
</style>";

echo "<h3 class='success'>✅ Database Connected Successfully</h3>";

// Test 1: Check required tables exist
$required_tables = [
    'order',
    'order_items',
    'payment',
    'awaiting_admin_orders',
    'ready_to_approve_orders',
    'approved_orders',
    'disapproved_orders',
    'pending_review_orders',
    'system_activity_log',
    'customer',
    'user'
];

echo "<h3>📋 Table Existence Check:</h3><ul>";
$missing_tables = [];
foreach ($required_tables as $table) {
    $result = $db->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<li class='success'>✅ Table '$table' exists</li>";
    } else {
        echo "<li class='error'>❌ Table '$table' MISSING</li>";
        $missing_tables[] = $table;
    }
}
echo "</ul>";

if (!empty($missing_tables)) {
    echo "<p class='error'><strong>⚠️ Missing Tables:</strong> " . implode(', ', $missing_tables) . "</p>";
}

// Test 2: Check order table structure
echo "<h3>📊 Order Table Structure:</h3>";
$result = $db->query("DESCRIBE `order`");
echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
$required_fields = ['OrderID', 'OrderNumber', 'Status', 'Customer_ID', 'SalesRep_ID', 'ApprovedBy_SalesRep_ID', 'ApprovedBy_Admin_ID'];
$found_fields = [];
while ($row = $result->fetch_assoc()) {
    $found_fields[] = $row['Field'];
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
}
echo "</table>";

// Check for missing required fields
$missing_fields = array_diff($required_fields, $found_fields);
if (!empty($missing_fields)) {
    echo "<p class='error'><strong>⚠️ Missing Required Fields:</strong> " . implode(', ', $missing_fields) . "</p>";
} else {
    echo "<p class='success'>✅ All required fields present</p>";
}

// Test 3: Check for test users
echo "<h3>👥 Test Users:</h3>";
$result = $db->query("SELECT UserID, First_Name, Last_Name, Email, Role FROM user WHERE Role IN ('Customer', 'Sales Representative', 'Admin')");
echo "<table><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
    echo "<tr><td>{$row['UserID']}</td><td>{$row['First_Name']} {$row['Last_Name']}</td><td>{$row['Email']}</td><td>{$row['Role']}</td></tr>";
}
echo "</table>";

if (empty($users)) {
    echo "<p class='warning'>⚠️ No test users found. You may need to create test users.</p>";
} else {
    echo "<p class='success'>✅ Found " . count($users) . " test users</p>";
}

// Test 4: Check order status distribution
echo "<h3>📈 Order Status Distribution:</h3>";
$result = $db->query("SELECT Status, COUNT(*) as Count FROM `order` GROUP BY Status ORDER BY Count DESC");
echo "<table><tr><th>Status</th><th>Count</th></tr>";
$total_orders = 0;
while ($row = $result->fetch_assoc()) {
    $total_orders += $row['Count'];
    echo "<tr><td>{$row['Status']}</td><td>{$row['Count']}</td></tr>";
}
echo "</table>";
echo "<p><strong>Total Orders:</strong> $total_orders</p>";

// Test 5: Check for orphaned records
echo "<h3>🔍 Data Integrity Check:</h3>";
$issues = [];

// Check orders in 'Awaiting Admin' but not in awaiting_admin_orders
$result = $db->query("
    SELECT o.OrderID, o.OrderNumber, o.Status
    FROM `order` o
    LEFT JOIN awaiting_admin_orders aao ON o.OrderID = aao.OrderID
    WHERE o.Status = 'Awaiting Admin' 
    AND aao.OrderID IS NULL
");
if ($result->num_rows > 0) {
    $issues[] = "Found " . $result->num_rows . " orders in 'Awaiting Admin' but not in awaiting_admin_orders table";
}

// Check for inconsistent status
$result = $db->query("
    SELECT o.OrderID, o.OrderNumber, o.Status, rtao.AdminStatus
    FROM `order` o
    JOIN ready_to_approve_orders rtao ON o.OrderID = rtao.OrderID
    WHERE o.Status != 'Ready to Approve'
");
if ($result->num_rows > 0) {
    $issues[] = "Found " . $result->num_rows . " orders in ready_to_approve_orders but status is not 'Ready to Approve'";
}

if (empty($issues)) {
    echo "<p class='success'>✅ No data integrity issues found</p>";
} else {
    echo "<p class='error'><strong>⚠️ Data Integrity Issues:</strong></p><ul>";
    foreach ($issues as $issue) {
        echo "<li class='error'>$issue</li>";
    }
    echo "</ul>";
}

// Test 6: Check recent activity log
echo "<h3>📝 Recent Activity Log (Last 5):</h3>";
$result = $db->query("SELECT Action, Description, Role, UserID, Timestamp FROM system_activity_log ORDER BY Timestamp DESC LIMIT 5");
echo "<table><tr><th>Action</th><th>Description</th><th>Role</th><th>UserID</th><th>Timestamp</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Action']}</td><td>{$row['Description']}</td><td>{$row['Role']}</td><td>{$row['UserID']}</td><td>{$row['Timestamp']}</td></tr>";
}
echo "</table>";

// Summary
echo "<hr>";
echo "<h3>📊 Summary</h3>";
echo "<ul>";
echo "<li>Tables: " . (count($required_tables) - count($missing_tables)) . "/" . count($required_tables) . " present</li>";
echo "<li>Required Fields: " . (count($required_fields) - count($missing_fields)) . "/" . count($required_fields) . " present</li>";
echo "<li>Test Users: " . count($users) . " found</li>";
echo "<li>Total Orders: $total_orders</li>";
echo "<li>Data Issues: " . count($issues) . " found</li>";
echo "</ul>";

if (empty($missing_tables) && empty($missing_fields) && !empty($users) && empty($issues)) {
    echo "<h3 class='success'>✅ All checks passed! Database is ready for testing.</h3>";
} else {
    echo "<h3 class='warning'>⚠️ Some issues found. Please review and fix before testing.</h3>";
}

$db->close();
?>
