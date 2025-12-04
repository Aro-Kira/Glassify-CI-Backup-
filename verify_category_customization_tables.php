<?php
/**
 * Verify category-specific customization tables and their data
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Verifying category-specific customization tables...\n\n";
    
    // Check if old customization table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'customization'");
    if ($stmt->rowCount() > 0) {
        echo "⚠️  WARNING: Old customization table still exists!\n";
    } else {
        echo "✓ Old customization table has been removed\n";
    }
    
    // Verify category-specific tables
    $tables = [
        'mirror_customization',
        'shower_enclosure_customization',
        'aluminum_doors_customization',
        'aluminum_bathroom_doors_customization'
    ];
    
    echo "\n📊 Table Verification:\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            // Count entries
            $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo "   ✓ $table exists ($count entries)\n";
            
            // Show sample entries
            if ($count > 0) {
                $sample_stmt = $pdo->query("SELECT * FROM `$table` LIMIT 2");
                $samples = $sample_stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "      Sample entries:\n";
                foreach ($samples as $sample) {
                    $details = [];
                    foreach ($sample as $key => $value) {
                        if ($key !== 'CustomizationID' && $key !== 'Created_Date' && $value !== null) {
                            $details[] = "$key: $value";
                        }
                    }
                    echo "         - " . implode(', ', array_slice($details, 0, 3)) . "...\n";
                }
            }
        } else {
            echo "   ✗ $table does not exist\n";
        }
    }
    
    // Summary
    echo "\n📈 Summary:\n";
    $total_entries = 0;
    foreach ($tables as $table) {
        $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $total_entries += $count;
        echo "   - $table: $count entries\n";
    }
    echo "   Total entries across all tables: $total_entries\n";
    
    echo "\n✅ Verification completed!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

