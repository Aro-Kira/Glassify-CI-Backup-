<?php
/**
 * Create sales_notif table to store all notifications for sales representatives
 */

// Database configuration
$host = 'localhost';
$dbname = 'glassify-test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating sales_notif table...\n\n";
    
    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS `sales_notif` (
        `NotificationID` int(11) NOT NULL AUTO_INCREMENT,
        `Icon` varchar(50) NOT NULL COMMENT 'Font Awesome icon class (e.g., fa-box-open, fa-user-tie, fa-shopping-cart)',
        `Role` varchar(50) NOT NULL COMMENT 'System, Client/Customer, Admin, Inventory Officer, Sales Representative',
        `Description` text NOT NULL COMMENT 'Notification message/description',
        `Status` enum('Unread','Read') DEFAULT 'Unread' COMMENT 'Notification read status',
        `Created_Date` datetime NOT NULL DEFAULT current_timestamp(),
        `Read_Date` datetime DEFAULT NULL COMMENT 'When notification was marked as read',
        `RelatedID` int(11) DEFAULT NULL COMMENT 'Related OrderID, IssueID, InventoryItemID, etc.',
        `RelatedType` varchar(50) DEFAULT NULL COMMENT 'Order, Issue, Inventory, Payment, etc.',
        PRIMARY KEY (`NotificationID`),
        KEY `Status` (`Status`),
        KEY `Role` (`Role`),
        KEY `Created_Date` (`Created_Date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql);
    echo "✓ Created table: sales_notif\n";
    
    // Migrate existing inventory notifications to sales_notif
    echo "\nMigrating existing inventory notifications...\n";
    $inventory_notifs = $pdo->query("SELECT * FROM inventory_notifications")->fetchAll(PDO::FETCH_ASSOC);
    $migrated_count = 0;
    
    foreach ($inventory_notifs as $notif) {
        $stmt = $pdo->prepare("INSERT INTO sales_notif (Icon, Role, Description, Status, Created_Date, RelatedID, RelatedType) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'fa-box-open', // Icon for inventory alerts
            'System', // Role
            $notif['Message'], // Description
            $notif['Status'], // Status (Unread/Read)
            $notif['Created_Date'], // Created date
            $notif['InventoryItemID'], // Related ID
            'Inventory' // Related type
        ]);
        $migrated_count++;
    }
    
    echo "✓ Migrated {$migrated_count} inventory notifications\n";
    
    // Migrate system activity log entries to sales_notif
    echo "\nMigrating system activity log entries...\n";
    $activities = $pdo->query("SELECT * FROM system_activity_log ORDER BY Timestamp DESC")->fetchAll(PDO::FETCH_ASSOC);
    $migrated_activities = 0;
    
    foreach ($activities as $activity) {
        // Determine icon based on action/description
        $icon = 'fa-info-circle';
        $action_lower = strtolower($activity['Action'] ?? '');
        $desc_lower = strtolower($activity['Description'] ?? '');
        
        if (strpos($desc_lower, 'inventory') !== false || strpos($desc_lower, 'stock') !== false || $action_lower === 'warning') {
            $icon = 'fa-box-open';
        } elseif (strpos($desc_lower, 'employee') !== false || strpos($desc_lower, 'request') !== false || strpos($desc_lower, 'logout') !== false) {
            $icon = 'fa-user-tie';
        } elseif (strpos($desc_lower, 'order') !== false || strpos($action_lower, 'order') !== false) {
            $icon = 'fa-shopping-cart';
        } elseif (strpos($desc_lower, 'payment') !== false || strpos($action_lower, 'payment') !== false) {
            $icon = 'fa-money-bill-wave';
        } elseif (strpos($desc_lower, 'issue') !== false || strpos($action_lower, 'issue') !== false) {
            $icon = 'fa-exclamation-circle';
        }
        
        // Format description with action as title
        $description = $activity['Action'] . ': ' . $activity['Description'];
        
        $stmt = $pdo->prepare("INSERT INTO sales_notif (Icon, Role, Description, Status, Created_Date, RelatedID, RelatedType) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $icon,
            $activity['Role'] ?? 'System',
            $description,
            'Read', // System activities are considered read
            $activity['Timestamp'],
            $activity['RelatedID'],
            $activity['RelatedType']
        ]);
        $migrated_activities++;
    }
    
    echo "✓ Migrated {$migrated_activities} system activities\n";
    
    echo "\n✅ Setup completed successfully!\n";
    echo "Total notifications in sales_notif: " . ($migrated_count + $migrated_activities) . "\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

