<?php
/**
 * Direct script to create order status tables
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
    
    // Create pending_review_orders
    $sql1 = "CREATE TABLE IF NOT EXISTS `pending_review_orders` (
      `PendingOrderID` int(11) NOT NULL AUTO_INCREMENT,
      `OrderID` varchar(50) DEFAULT NULL,
      `ProductName` varchar(255) DEFAULT NULL,
      `Address` varchar(255) DEFAULT NULL,
      `OrderDate` datetime DEFAULT NULL,
      `Shape` varchar(50) DEFAULT NULL,
      `Dimension` varchar(100) DEFAULT NULL,
      `Type` varchar(50) DEFAULT NULL,
      `Thickness` varchar(50) DEFAULT NULL,
      `EdgeWork` varchar(50) DEFAULT NULL,
      `FrameType` varchar(50) DEFAULT NULL,
      `Engraving` varchar(255) DEFAULT NULL,
      `FileAttached` varchar(255) DEFAULT NULL,
      `TotalQuotation` decimal(12,2) DEFAULT 0.00,
      `Customer_ID` int(11) DEFAULT NULL,
      `SalesRep_ID` int(11) DEFAULT NULL,
      `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
      `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
      PRIMARY KEY (`PendingOrderID`),
      KEY `idx_orderid` (`OrderID`),
      KEY `idx_customer` (`Customer_ID`),
      KEY `idx_salesrep` (`SalesRep_ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql1);
    echo "✓ Created pending_review_orders\n";
    
    // Create awaiting_admin_orders
    $sql2 = "CREATE TABLE IF NOT EXISTS `awaiting_admin_orders` (
      `AwaitingOrderID` int(11) NOT NULL AUTO_INCREMENT,
      `OrderID` varchar(50) DEFAULT NULL,
      `ProductName` varchar(255) DEFAULT NULL,
      `Address` varchar(255) DEFAULT NULL,
      `OrderDate` datetime DEFAULT NULL,
      `Shape` varchar(50) DEFAULT NULL,
      `Dimension` varchar(100) DEFAULT NULL,
      `Type` varchar(50) DEFAULT NULL,
      `Thickness` varchar(50) DEFAULT NULL,
      `EdgeWork` varchar(50) DEFAULT NULL,
      `FrameType` varchar(50) DEFAULT NULL,
      `Engraving` varchar(255) DEFAULT NULL,
      `FileAttached` varchar(255) DEFAULT NULL,
      `TotalQuotation` decimal(12,2) DEFAULT 0.00,
      `Customer_ID` int(11) DEFAULT NULL,
      `SalesRep_ID` int(11) DEFAULT NULL,
      `RequestedBy_SalesRep_ID` int(11) DEFAULT NULL,
      `Requested_Date` datetime DEFAULT NULL,
      `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
      `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
      PRIMARY KEY (`AwaitingOrderID`),
      KEY `idx_orderid` (`OrderID`),
      KEY `idx_customer` (`Customer_ID`),
      KEY `idx_salesrep` (`SalesRep_ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql2);
    echo "✓ Created awaiting_admin_orders\n";
    
    // Create ready_to_approve_orders
    $sql3 = "CREATE TABLE IF NOT EXISTS `ready_to_approve_orders` (
      `ReadyOrderID` int(11) NOT NULL AUTO_INCREMENT,
      `OrderID` varchar(50) DEFAULT NULL,
      `ProductName` varchar(255) DEFAULT NULL,
      `Address` varchar(255) DEFAULT NULL,
      `OrderDate` datetime DEFAULT NULL,
      `Shape` varchar(50) DEFAULT NULL,
      `Dimension` varchar(100) DEFAULT NULL,
      `Type` varchar(50) DEFAULT NULL,
      `Thickness` varchar(50) DEFAULT NULL,
      `EdgeWork` varchar(50) DEFAULT NULL,
      `FrameType` varchar(50) DEFAULT NULL,
      `Engraving` varchar(255) DEFAULT NULL,
      `FileAttached` varchar(255) DEFAULT NULL,
      `TotalQuotation` decimal(12,2) DEFAULT 0.00,
      `Customer_ID` int(11) DEFAULT NULL,
      `SalesRep_ID` int(11) DEFAULT NULL,
      `AdminStatus` enum('Approved','Disapproved') DEFAULT NULL,
      `AdminNotes` text DEFAULT NULL,
      `AdminReviewed_Date` datetime DEFAULT NULL,
      `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
      `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
      PRIMARY KEY (`ReadyOrderID`),
      KEY `idx_orderid` (`OrderID`),
      KEY `idx_customer` (`Customer_ID`),
      KEY `idx_salesrep` (`SalesRep_ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql3);
    echo "✓ Created ready_to_approve_orders\n";
    
    // Create disapproved_orders
    $sql4 = "CREATE TABLE IF NOT EXISTS `disapproved_orders` (
      `DisapprovedOrderID` int(11) NOT NULL AUTO_INCREMENT,
      `OrderID` varchar(50) DEFAULT NULL,
      `ProductName` varchar(255) DEFAULT NULL,
      `Address` varchar(255) DEFAULT NULL,
      `OrderDate` datetime DEFAULT NULL,
      `Shape` varchar(50) DEFAULT NULL,
      `Dimension` varchar(100) DEFAULT NULL,
      `Type` varchar(50) DEFAULT NULL,
      `Thickness` varchar(50) DEFAULT NULL,
      `EdgeWork` varchar(50) DEFAULT NULL,
      `FrameType` varchar(50) DEFAULT NULL,
      `Engraving` varchar(255) DEFAULT NULL,
      `FileAttached` varchar(255) DEFAULT NULL,
      `TotalQuotation` decimal(12,2) DEFAULT 0.00,
      `Customer_ID` int(11) DEFAULT NULL,
      `SalesRep_ID` int(11) DEFAULT NULL,
      `DisapprovedBy` enum('Sales Rep','Admin') DEFAULT NULL,
      `DisapprovedBy_ID` int(11) DEFAULT NULL,
      `DisapprovalReason` text DEFAULT NULL,
      `Disapproved_Date` datetime DEFAULT NULL,
      `CustomerNotified` tinyint(1) DEFAULT 0,
      `CustomerNotified_Date` datetime DEFAULT NULL,
      `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
      `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
      PRIMARY KEY (`DisapprovedOrderID`),
      KEY `idx_orderid` (`OrderID`),
      KEY `idx_customer` (`Customer_ID`),
      KEY `idx_salesrep` (`SalesRep_ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql4);
    echo "✓ Created disapproved_orders\n";
    
    // Create approved_orders
    $sql5 = "CREATE TABLE IF NOT EXISTS `approved_orders` (
      `ApprovedOrderID` int(11) NOT NULL AUTO_INCREMENT,
      `OrderID` varchar(50) DEFAULT NULL,
      `ProductName` varchar(255) DEFAULT NULL,
      `Address` varchar(255) DEFAULT NULL,
      `OrderDate` datetime DEFAULT NULL,
      `Shape` varchar(50) DEFAULT NULL,
      `Dimension` varchar(100) DEFAULT NULL,
      `Type` varchar(50) DEFAULT NULL,
      `Thickness` varchar(50) DEFAULT NULL,
      `EdgeWork` varchar(50) DEFAULT NULL,
      `FrameType` varchar(50) DEFAULT NULL,
      `Engraving` varchar(255) DEFAULT NULL,
      `FileAttached` varchar(255) DEFAULT NULL,
      `TotalQuotation` decimal(12,2) DEFAULT 0.00,
      `Customer_ID` int(11) DEFAULT NULL,
      `SalesRep_ID` int(11) DEFAULT NULL,
      `ApprovedBy_SalesRep_ID` int(11) DEFAULT NULL,
      `Approved_Date` datetime DEFAULT NULL,
      `CustomerNotified` tinyint(1) DEFAULT 0,
      `CustomerNotified_Date` datetime DEFAULT NULL,
      `PaymentMethod` enum('E-Wallet','Cash on Delivery') DEFAULT NULL,
      `PaymentStatus` enum('Pending','Paid','Failed') DEFAULT 'Pending',
      `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
      `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
      PRIMARY KEY (`ApprovedOrderID`),
      KEY `idx_orderid` (`OrderID`),
      KEY `idx_customer` (`Customer_ID`),
      KEY `idx_salesrep` (`SalesRep_ID`),
      KEY `idx_payment_status` (`PaymentStatus`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql5);
    echo "✓ Created approved_orders\n";
    
    echo "\n✅ All tables created successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

