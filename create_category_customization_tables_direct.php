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
    
    // 1. Mirror Customization Table
    $sql1 = "CREATE TABLE IF NOT EXISTS `mirror_customization` (
      `CustomizationID` int(11) NOT NULL AUTO_INCREMENT,
      `Customer_ID` int(11) NOT NULL,
      `Product_ID` int(11) NOT NULL,
      `Dimensions` varchar(255) DEFAULT NULL COMMENT 'Height x Width (or Diameter for Circle)',
      `EdgeWork` varchar(50) DEFAULT NULL COMMENT 'polished, beveled, same lang',
      `GlassShape` varchar(50) DEFAULT NULL COMMENT 'Rectangle, Circle, Oval, Arch, Capsule',
      `LEDBacklight` varchar(50) DEFAULT NULL COMMENT 'Optional',
      `Engraving` varchar(255) DEFAULT NULL COMMENT 'Optional',
      `EstimatePrice` decimal(10,2) DEFAULT 0.00,
      `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`CustomizationID`),
      KEY `Customer_ID` (`Customer_ID`),
      KEY `Product_ID` (`Product_ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    try {
        $pdo->exec($sql1);
        echo "✓ Created table: mirror_customization\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "- Table mirror_customization already exists\n";
        } else {
            echo "✗ Error creating mirror_customization: " . $e->getMessage() . "\n";
        }
    }
    
    // 2. Shower Enclosure / Partition Customization Table
    $sql2 = "CREATE TABLE IF NOT EXISTS `shower_enclosure_customization` (
      `CustomizationID` int(11) NOT NULL AUTO_INCREMENT,
      `Customer_ID` int(11) NOT NULL,
      `Product_ID` int(11) NOT NULL,
      `Dimensions` varchar(255) DEFAULT NULL COMMENT 'Height x Width',
      `GlassType` varchar(50) DEFAULT NULL COMMENT 'same as default',
      `GlassThickness` varchar(50) DEFAULT NULL COMMENT '6mm, 8mm, 10mm, 12mm',
      `FrameType` varchar(50) DEFAULT NULL COMMENT 'Framed, Semi-Frameless, Frameless',
      `Engraving` varchar(255) DEFAULT NULL COMMENT 'optional',
      `DoorOperation` varchar(50) DEFAULT NULL COMMENT 'Swing, Sliding, Fixed',
      `EstimatePrice` decimal(10,2) DEFAULT 0.00,
      `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`CustomizationID`),
      KEY `Customer_ID` (`Customer_ID`),
      KEY `Product_ID` (`Product_ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    try {
        $pdo->exec($sql2);
        echo "✓ Created table: shower_enclosure_customization\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "- Table shower_enclosure_customization already exists\n";
        } else {
            echo "✗ Error creating shower_enclosure_customization: " . $e->getMessage() . "\n";
        }
    }
    
    // 3. Aluminum Doors Customization Table
    $sql3 = "CREATE TABLE IF NOT EXISTS `aluminum_doors_customization` (
      `CustomizationID` int(11) NOT NULL AUTO_INCREMENT,
      `Customer_ID` int(11) NOT NULL,
      `Product_ID` int(11) NOT NULL,
      `Dimensions` varchar(255) DEFAULT NULL,
      `GlassType` varchar(50) DEFAULT NULL COMMENT 'same as default',
      `GlassThickness` varchar(50) DEFAULT NULL COMMENT '6mm, 10mm',
      `Configuration` varchar(50) DEFAULT NULL COMMENT '2-panel slider, 3-panel slider, 4-panel slider',
      `EstimatePrice` decimal(10,2) DEFAULT 0.00,
      `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`CustomizationID`),
      KEY `Customer_ID` (`Customer_ID`),
      KEY `Product_ID` (`Product_ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    try {
        $pdo->exec($sql3);
        echo "✓ Created table: aluminum_doors_customization\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "- Table aluminum_doors_customization already exists\n";
        } else {
            echo "✗ Error creating aluminum_doors_customization: " . $e->getMessage() . "\n";
        }
    }
    
    // 4. Aluminum and Bathroom Doors Customization Table
    $sql4 = "CREATE TABLE IF NOT EXISTS `aluminum_bathroom_doors_customization` (
      `CustomizationID` int(11) NOT NULL AUTO_INCREMENT,
      `Customer_ID` int(11) NOT NULL,
      `Product_ID` int(11) NOT NULL,
      `Dimensions` varchar(255) DEFAULT NULL,
      `FrameType` varchar(50) DEFAULT NULL,
      `EstimatePrice` decimal(10,2) DEFAULT 0.00,
      `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`CustomizationID`),
      KEY `Customer_ID` (`Customer_ID`),
      KEY `Product_ID` (`Product_ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    try {
        $pdo->exec($sql4);
        echo "✓ Created table: aluminum_bathroom_doors_customization\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "- Table aluminum_bathroom_doors_customization already exists\n";
        } else {
            echo "✗ Error creating aluminum_bathroom_doors_customization: " . $e->getMessage() . "\n";
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
            
            // List columns
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $col) {
                echo "      - {$col['Field']} ({$col['Type']})\n";
            }
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

