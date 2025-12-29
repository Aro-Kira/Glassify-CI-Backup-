-- =====================================================
-- Database Update Script
-- Add missing fields and tables from latest_glassifydb.sql
-- Run this in phpMyAdmin to update your database
-- =====================================================
-- IMPORTANT: Backup your database before running this script!
-- =====================================================

-- 1. Add missing date fields to order table
-- Check if columns exist first (MySQL/MariaDB doesn't support IF NOT EXISTS in ALTER TABLE)
-- If you get an error that the column already exists, that's fine - just skip that line

ALTER TABLE `order` 
ADD COLUMN `PreferredInstallationDate` date DEFAULT NULL COMMENT 'Customer preferred installation date (captured at checkout)' AFTER `CustomerNotified_Date`;

ALTER TABLE `order` 
ADD COLUMN `OcularDate` date DEFAULT NULL COMMENT 'Scheduled date for ocular visit' AFTER `PreferredInstallationDate`;

ALTER TABLE `order` 
ADD COLUMN `FabricationDate` date DEFAULT NULL COMMENT 'Scheduled date for fabrication' AFTER `OcularDate`;

ALTER TABLE `order` 
ADD COLUMN `InstallationDate` date DEFAULT NULL COMMENT 'Scheduled date for installation' AFTER `FabricationDate`;

ALTER TABLE `order` 
ADD COLUMN `EstimatedDelivery` date DEFAULT NULL COMMENT 'Estimated delivery/completion date' AFTER `InstallationDate`;

-- 2. Add missing AssignedStaff_ID field to appointments table
ALTER TABLE `appointments` 
ADD COLUMN `AssignedStaff_ID` int(11) DEFAULT NULL AFTER `AssignedStaff`;

-- Add index for AssignedStaff_ID
ALTER TABLE `appointments`
ADD KEY `idx_staff` (`AssignedStaff_ID`);

-- Add foreign key constraint for AssignedStaff_ID (if it doesn't already exist)
-- Note: If you get an error that the constraint already exists, that's fine
ALTER TABLE `appointments`
ADD CONSTRAINT `fk_appointments_staff` FOREIGN KEY (`AssignedStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

-- 3. Verify OrderNumber field exists in order table
-- Uncomment the lines below if OrderNumber field is missing
-- ALTER TABLE `order` 
-- ADD COLUMN `OrderNumber` varchar(50) NOT NULL COMMENT 'Formatted: GI001, GI002, etc.' AFTER `OrderID`;
-- ALTER TABLE `order`
-- ADD UNIQUE KEY `OrderNumber` (`OrderNumber`);

-- 4. Create legacy order status tables if they don't exist (for backward compatibility)

-- pending_review_orders table
CREATE TABLE IF NOT EXISTS `pending_review_orders` (
  `PendingOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL COMMENT 'References order.OrderID',
  `OrderNumber` varchar(50) DEFAULT NULL COMMENT 'References order.OrderNumber',
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `TotalQuotation` decimal(12,2) DEFAULT 0.00,
  `Notes` text DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`PendingOrderID`),
  KEY `idx_orderid` (`OrderID`),
  KEY `idx_ordernumber` (`OrderNumber`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_salesrep` (`SalesRep_ID`),
  CONSTRAINT `fk_pending_orders_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_pending_orders_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL,
  CONSTRAINT `fk_pending_orders_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- awaiting_admin_orders table
CREATE TABLE IF NOT EXISTS `awaiting_admin_orders` (
  `AwaitingOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL COMMENT 'References order.OrderID',
  `OrderNumber` varchar(50) DEFAULT NULL COMMENT 'References order.OrderNumber',
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `TotalQuotation` decimal(12,2) DEFAULT 0.00,
  `SalesRepNotes` text DEFAULT NULL COMMENT 'Notes from sales rep when requesting approval',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`AwaitingOrderID`),
  KEY `idx_orderid` (`OrderID`),
  KEY `idx_ordernumber` (`OrderNumber`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_salesrep` (`SalesRep_ID`),
  CONSTRAINT `fk_awaiting_orders_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_awaiting_orders_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL,
  CONSTRAINT `fk_awaiting_orders_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- approved_orders table
CREATE TABLE IF NOT EXISTS `approved_orders` (
  `ApprovedOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL COMMENT 'References order.OrderID',
  `OrderNumber` varchar(50) DEFAULT NULL COMMENT 'References order.OrderNumber',
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `TotalQuotation` decimal(12,2) DEFAULT 0.00,
  `CustomerNotified` tinyint(1) DEFAULT 0,
  `CustomerNotified_Date` datetime DEFAULT NULL,
  `ApprovedBy_SalesRep_ID` int(11) DEFAULT NULL,
  `Approved_Date` datetime DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`ApprovedOrderID`),
  KEY `idx_orderid` (`OrderID`),
  KEY `idx_ordernumber` (`OrderNumber`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_salesrep` (`SalesRep_ID`),
  CONSTRAINT `fk_approved_orders_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_approved_orders_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL,
  CONSTRAINT `fk_approved_orders_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  CONSTRAINT `fk_approved_orders_salesrep_approved` FOREIGN KEY (`ApprovedBy_SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- disapproved_orders table
CREATE TABLE IF NOT EXISTS `disapproved_orders` (
  `DisapprovedOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL COMMENT 'References order.OrderID',
  `OrderNumber` varchar(50) DEFAULT NULL COMMENT 'References order.OrderNumber',
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `TotalQuotation` decimal(12,2) DEFAULT 0.00,
  `DisapprovedBy` enum('Sales Rep','Admin') DEFAULT NULL,
  `DisapprovedBy_ID` int(11) DEFAULT NULL,
  `DisapprovalReason` text DEFAULT NULL,
  `Disapproved_Date` datetime DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`DisapprovedOrderID`),
  KEY `idx_orderid` (`OrderID`),
  KEY `idx_ordernumber` (`OrderNumber`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_salesrep` (`SalesRep_ID`),
  CONSTRAINT `fk_disapproved_orders_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_disapproved_orders_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL,
  CONSTRAINT `fk_disapproved_orders_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  CONSTRAINT `fk_disapproved_orders_disapproved_by` FOREIGN KEY (`DisapprovedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- Verification Queries (Run these to check if updates were successful)
-- =====================================================

-- Check if order table has the new date fields
-- SHOW COLUMNS FROM `order` LIKE '%Date%';

-- Check if appointments table has AssignedStaff_ID
-- SHOW COLUMNS FROM `appointments` LIKE 'AssignedStaff_ID';

-- List all tables
-- SHOW TABLES;

-- Check if legacy order status tables exist
-- SHOW TABLES LIKE '%_orders';

