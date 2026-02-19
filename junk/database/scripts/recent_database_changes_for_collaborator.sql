-- ============================================================================
-- GLASSIFY-CI: RECENT DATABASE CHANGES FOR COLLABORATOR
-- ============================================================================
-- This file contains all recent database changes/additions
-- Date: 2026-01-21
-- Purpose: Share with collaborator to sync databases
-- 
-- INSTRUCTIONS:
-- 1. BACKUP YOUR DATABASE FIRST!
-- 2. Open phpMyAdmin
-- 3. Select your database (latest_glassifydb)
-- 4. Go to SQL tab
-- 5. Copy and paste this entire file
-- 6. Click "Go"
-- 
-- Note: Some statements may show warnings if columns/tables already exist.
-- This is normal and safe - the script checks before adding.
-- ============================================================================

SET FOREIGN_KEY_CHECKS=0;

-- ============================================================================
-- 1. CUSTOMER NOTIFICATIONS TABLE (NEW)
-- ============================================================================
-- Purpose: Store notifications sent to customers
-- Added: 2026-01-20

CREATE TABLE IF NOT EXISTS `customer_notifications` (
  `NotificationID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) NOT NULL COMMENT 'Customer ID who receives the notification',
  `Icon` varchar(50) NOT NULL DEFAULT 'fa-info-circle' COMMENT 'Font Awesome icon class',
  `Type` varchar(50) NOT NULL DEFAULT 'General' COMMENT 'Type of notification: Order, Payment, Delivery, General, System',
  `Title` varchar(255) NOT NULL COMMENT 'Notification title/heading',
  `Message` text NOT NULL COMMENT 'Notification message/description',
  `Status` enum('Unread','Read') DEFAULT 'Unread' COMMENT 'Notification read status',
  `Created_Date` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'When notification was created',
  `Read_Date` datetime DEFAULT NULL COMMENT 'When notification was marked as read',
  `RelatedID` int(11) DEFAULT NULL COMMENT 'Related OrderID, PaymentID, etc.',
  `RelatedType` varchar(50) DEFAULT NULL COMMENT 'Order, Payment, Delivery, etc.',
  `CreatedBy` int(11) DEFAULT NULL COMMENT 'UserID of admin/staff who created the notification',
  PRIMARY KEY (`NotificationID`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_status` (`Status`),
  KEY `idx_type` (`Type`),
  KEY `idx_created_date` (`Created_Date`),
  KEY `idx_related` (`RelatedID`, `RelatedType`),
  CONSTRAINT `fk_customer_notifications_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_customer_notifications_creator` FOREIGN KEY (`CreatedBy`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 2. QUOTATION TABLE - Add Missing Fields
-- ============================================================================
-- Purpose: Ensure quotation table has all fields needed for new workflow

-- Check and add QuotationNumber
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'QuotationNumber');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `quotation` ADD COLUMN `QuotationNumber` varchar(50) NOT NULL COMMENT ''Formatted: QT001, QT002, etc.'' AFTER QuotationID;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add Customer_ID
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'Customer_ID');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `quotation` ADD COLUMN `Customer_ID` int(11) NOT NULL AFTER QuotationNumber;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add SalesRep_ID
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'SalesRep_ID');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `quotation` ADD COLUMN `SalesRep_ID` int(11) NOT NULL AFTER Customer_ID;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add TotalAmount (if Total_amount doesn't exist, add TotalAmount)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'TotalAmount');
SET @col_exists_alt = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'Total_amount');
SET @sql = IF(@col_exists = 0 AND @col_exists_alt = 0, 
  'ALTER TABLE `quotation` ADD COLUMN `TotalAmount` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER SalesRep_ID;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add Notes (position it after the total amount column that exists)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'Notes');
SET @col_exists_total = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'TotalAmount');
SET @col_exists_total_alt = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'Total_amount');
-- Determine which total column to use as reference
SET @sql = IF(@col_exists = 0, 
  IF(@col_exists_total > 0, 
    'ALTER TABLE `quotation` ADD COLUMN `Notes` text DEFAULT NULL COMMENT ''Admin notes'' AFTER TotalAmount;',
    IF(@col_exists_total_alt > 0,
      'ALTER TABLE `quotation` ADD COLUMN `Notes` text DEFAULT NULL COMMENT ''Admin notes'' AFTER Total_amount;',
      'ALTER TABLE `quotation` ADD COLUMN `Notes` text DEFAULT NULL COMMENT ''Admin notes'' AFTER SalesRep_ID;'
    )
  ),
  'SELECT 1;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add Status enum
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'Status');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `quotation` ADD COLUMN `Status` enum(''Pending'',''Approved'',''Rejected'',''Converted to Order'') DEFAULT ''Pending'' AFTER Notes;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add ExpiryDate
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'ExpiryDate');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `quotation` ADD COLUMN `ExpiryDate` date DEFAULT NULL COMMENT ''Quotation validity expiry date'' AFTER Status;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add CreatedDate
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'CreatedDate');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `quotation` ADD COLUMN `CreatedDate` datetime NOT NULL DEFAULT current_timestamp() AFTER ExpiryDate;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add ConvertedToOrder_ID
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'quotation' 
  AND COLUMN_NAME = 'ConvertedToOrder_ID');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `quotation` ADD COLUMN `ConvertedToOrder_ID` int(11) DEFAULT NULL COMMENT ''Order ID if converted to order'' AFTER CreatedDate;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 3. APPOINTMENTS TABLE - Add QuotationID Field
-- ============================================================================
-- Purpose: Link appointments with quotations

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'appointments' 
  AND COLUMN_NAME = 'QuotationID');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `appointments` ADD COLUMN `QuotationID` int(11) DEFAULT NULL COMMENT ''Linked quotation ID'' AFTER OrderID;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for QuotationID
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'appointments' 
  AND INDEX_NAME = 'idx_quotation');
SET @sql = IF(@idx_exists = 0, 
  'ALTER TABLE `appointments` ADD INDEX `idx_quotation` (`QuotationID`);',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 4. ISSUEREPORT TABLE - Add FileAttached Field
-- ============================================================================
-- Purpose: Store file attachments for issue reports

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'issuereport' 
  AND COLUMN_NAME = 'FileAttached');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `issuereport` ADD COLUMN `FileAttached` varchar(255) DEFAULT NULL AFTER Description;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 5. ORDER TABLE - Ensure OrderNumber Field Exists
-- ============================================================================
-- Purpose: Ensure order table has OrderNumber field

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'order' 
  AND COLUMN_NAME = 'OrderNumber');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `order` ADD COLUMN `OrderNumber` varchar(50) UNIQUE COMMENT ''Formatted: GI001, GI002, etc.'' AFTER OrderID;',
  'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 6. ORDER TABLE - Add 'Ocular Pending' Status to Status Enum
-- ============================================================================
-- Purpose: Add 'Ocular Pending' status for orders awaiting ocular visit
-- Added: 2026-01-21
-- 
-- This status is used when an order has been approved and is waiting
-- for an ocular visit (site assessment) to be scheduled and completed.

-- Check if 'Ocular Pending' already exists in the enum
SET @enum_has_ocular = (
  SELECT COUNT(*) 
  FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'order' 
    AND COLUMN_NAME = 'Status'
    AND COLUMN_TYPE LIKE '%Ocular Pending%'
);

-- Only update if 'Ocular Pending' doesn't exist
SET @sql = IF(@enum_has_ocular = 0,
  'ALTER TABLE `order` MODIFY COLUMN `Status` ENUM(
    ''Pending Review'',
    ''Awaiting Admin'',
    ''Ready to Approve'',
    ''Approved'',
    ''Ocular Pending'',
    ''Disapproved'',
    ''In Fabrication'',
    ''Ready for Installation'',
    ''Completed'',
    ''Cancelled'',
    ''Returned''
  ) DEFAULT ''Pending Review'';',
  'SELECT 1 AS ''Ocular Pending already exists in enum'';'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS=1;

-- ============================================================================
-- VERIFICATION (Optional - Run these to check)
-- ============================================================================

-- Check customer_notifications table
-- SELECT COUNT(*) as table_exists FROM information_schema.tables 
-- WHERE table_schema = DATABASE() AND table_name = 'customer_notifications';

-- Check quotation table structure
-- DESCRIBE `quotation`;

-- Check appointments table for QuotationID
-- DESCRIBE `appointments`;

-- Check issuereport table for FileAttached
-- DESCRIBE `issuereport`;

-- Verify 'Ocular Pending' status exists in order Status enum
-- SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order' AND COLUMN_NAME = 'Status';

-- ============================================================================
-- 4. ADD SelectedCustomizationSeries COLUMN TO PRODUCT TABLE
-- ============================================================================
-- Purpose: Store the series selected in Manage Customization Fields for each product
-- Added: 2026-01-23

ALTER TABLE `product`
ADD COLUMN `SelectedCustomizationSeries` VARCHAR(255) DEFAULT NULL COMMENT 'Series selected in Manage Customization Fields modal' AFTER `Customization`;

-- ============================================================================
-- END
-- ============================================================================
