<?php
/**
 * Create separate customization tables for each product category
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating category-specific customization tables...\n\n";
    
    // Read and execute SQL file
    $sql_file = __DIR__ . '/create_category_customization_tables.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: $sql_file");
    }
    
    $sql = file_get_contents($sql_file);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            // Extract table name from CREATE TABLE statement
            if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $statement, $matches)) {
                echo "✓ Created table: {$matches[1]}\n";
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                // Extract table name
                if (preg_match('/Table.*?`(\w+)`.*?already exists/i', $e->getMessage(), $matches)) {
                    echo "- Table {$matches[1]} already exists, skipping...\n";
                } else {
                    echo "- Table already exists, skipping...\n";
                }
            } else {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Verify tables were created
    echo "\n📊 Verifying tables...\n";
    $tables = [
        'mirror_customization',
        'shower_enclosure_customization',
        'aluminum_doors_customization',
        'aluminum_bathroom_doors_customization'
    ];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            // Get column count
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "   ✓ $table exists (" . count($columns) . " columns)\n";
        } else {
            echo "   ✗ $table does not exist\n";
        }
    }
    
    echo "\n✅ Category-specific customization tables created successfully!\n";
    echo "\nNext: Update code to use these tables based on product category.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

