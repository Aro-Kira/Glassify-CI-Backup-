<?php
/**
 * Script to verify and create order status tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking and creating order status tables...\n\n";
    
    // Read SQL file
    $sql_file = 'create_order_status_tables.sql';
    if (!file_exists($sql_file)) {
        echo "❌ SQL file not found: $sql_file\n";
        exit(1);
    }
    
    $sql = file_get_contents($sql_file);
    
    // Execute the entire SQL file
    try {
        // Split by semicolon but keep CREATE TABLE statements together
        $statements = preg_split('/;\s*(?=CREATE TABLE)/', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || preg_match('/^--/', $statement)) {
                continue;
            }
            
            // Add semicolon if missing
            if (substr($statement, -1) !== ';') {
                $statement .= ';';
            }
            
            try {
                $pdo->exec($statement);
                // Extract table name from CREATE TABLE statement
                if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                    echo "✓ Created table: {$matches[1]}\n";
                } else {
                    echo "✓ Executed statement\n";
                }
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo "✓ Table already exists (skipped)\n";
                } else {
                    echo "⚠ Error: " . $e->getMessage() . "\n";
                }
            }
        }
    } catch (PDOException $e) {
        echo "❌ Error executing SQL: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Verify tables exist
    echo "\nVerifying tables...\n";
    $tables = ['pending_review_orders', 'awaiting_admin_orders', 'ready_to_approve_orders', 'disapproved_orders', 'approved_orders'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "❌ Table '$table' does NOT exist\n";
        }
    }
    
    echo "\n✅ Setup complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

