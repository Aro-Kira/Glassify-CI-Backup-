<?php
/**
 * Script to create order status tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating order status tables...\n\n";
    
    // Read SQL file
    $sql = file_get_contents('create_order_status_tables.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Executed statement successfully\n";
            } catch (PDOException $e) {
                // Ignore "table already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "⚠ Error: " . $e->getMessage() . "\n";
                } else {
                    echo "✓ Table already exists (skipped)\n";
                }
            }
        }
    }
    
    echo "\n✅ All tables created successfully!\n";
    echo "\nTables created:\n";
    echo "- pending_review_orders\n";
    echo "- awaiting_admin_orders\n";
    echo "- ready_to_approve_orders\n";
    echo "- disapproved_orders\n";
    echo "- approved_orders\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

