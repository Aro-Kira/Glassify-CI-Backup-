-- ============================================================================
-- Admin Order Approval System - Database Schema (MySQL 5.7 Compatible)
-- Glassify-CI - Order Approval Flow (Sales Representative Pages Archived)
-- ============================================================================
-- This schema adds the necessary database tables and columns for the 
-- Admin Order Approval System as documented in ADMIN_ORDER_APPROVAL_DOCUMENTATION.md
--
-- MySQL 5.7 Compatible Version (does not use IF NOT EXISTS for ALTER TABLE)
-- Created: January 2026
-- ============================================================================

-- ============================================================================
-- 1. ALTER `order` TABLE - Add Approval/Disapproval Fields
-- ============================================================================
-- Note: MySQL 5.7 doesn't support ADD COLUMN IF NOT EXISTS
-- Remove the columns first if they exist, or check manually before running

-- Check if columns exist before adding (manual check required)
-- Run these ALTER statements only if columns don't exist

ALTER TABLE `order`
ADD COLUMN `ApprovedBy_Admin_ID` INT(11) NULL DEFAULT NULL COMMENT 'ID of admin who approved the order',
ADD COLUMN `Approved_Date` DATETIME NULL DEFAULT NULL COMMENT 'Date and time when order was approved by admin',
ADD COLUMN `DisapprovedBy` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Who disapproved the order (Admin or Sales Rep)',
ADD COLUMN `DisapprovedBy_ID` INT(11) NULL DEFAULT NULL COMMENT 'ID of admin/sales rep who disapproved the order',
ADD COLUMN `DisapprovalReason` TEXT NULL DEFAULT NULL COMMENT 'Reason for disapproval (required when disapproving)',
ADD COLUMN `Disapproved_Date` DATETIME NULL DEFAULT NULL COMMENT 'Date and time when order was disapproved';

-- Add indexes for performance
CREATE INDEX `idx_order_status` ON `order` (`Status`);
CREATE INDEX `idx_order_approved_by_admin` ON `order` (`ApprovedBy_Admin_ID`);
CREATE INDEX `idx_order_disapproved_by` ON `order` (`DisapprovedBy_ID`);

-- ============================================================================
-- 2. CREATE `system_activity_log` TABLE (if not exists)
-- ============================================================================
-- Logs all approval/disapproval actions and system activities

CREATE TABLE IF NOT EXISTS `system_activity_log` (
  `Log_ID` INT(11) NOT NULL AUTO_INCREMENT,
  `Action` VARCHAR(100) NOT NULL COMMENT 'Action type (e.g., "Order Approved by Admin", "Order Disapproved by Admin")',
  `Description` TEXT NULL DEFAULT NULL COMMENT 'Detailed description of the action',
  `Role` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Role of user who performed action (Admin, Sales Representative, Customer, System)',
  `UserID` INT(11) NULL DEFAULT NULL COMMENT 'ID of user who performed the action',
  `UserName` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Name of user who performed the action',
  `RelatedID` INT(11) NULL DEFAULT NULL COMMENT 'ID of related record (e.g., OrderID)',
  `RelatedType` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Type of related record (e.g., "Order", "Payment")',
  `Timestamp` DATETIME NOT NULL COMMENT 'Date and time when action occurred',
  PRIMARY KEY (`Log_ID`),
  INDEX `idx_timestamp` (`Timestamp`),
  INDEX `idx_role` (`Role`),
  INDEX `idx_userid` (`UserID`),
  INDEX `idx_related` (`RelatedType`, `RelatedID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System activity log for tracking all system actions';

-- ============================================================================
-- 3. CREATE `awaiting_admin_orders` TABLE (Legacy - Optional)
-- ============================================================================
-- Legacy table for backward compatibility
-- Contains orders awaiting admin review
-- Entries are deleted when order is approved/disapproved

CREATE TABLE IF NOT EXISTS `awaiting_admin_orders` (
  `ID` INT(11) NOT NULL AUTO_INCREMENT,
  `OrderID` INT(11) NOT NULL COMMENT 'Numeric Order ID',
  `OrderNumber` VARCHAR(50) NOT NULL COMMENT 'Order Number (e.g., GI001)',
  `ProductName` VARCHAR(255) NULL DEFAULT NULL,
  `Address` TEXT NULL DEFAULT NULL COMMENT 'Delivery address',
  `OrderDate` DATETIME NULL DEFAULT NULL,
  `TotalQuotation` DECIMAL(10,2) NULL DEFAULT NULL,
  `Customer_ID` INT(11) NULL DEFAULT NULL,
  `SalesRep_ID` INT(11) NULL DEFAULT NULL,
  `SalesRepNotes` TEXT NULL DEFAULT NULL COMMENT 'Notes from sales rep when requesting approval',
  `Created_Date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  INDEX `idx_orderid` (`OrderID`),
  INDEX `idx_ordernumber` (`OrderNumber`),
  INDEX `idx_orderdate` (`OrderDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Legacy table: Orders awaiting admin approval (for backward compatibility)';

-- ============================================================================
-- 4. CREATE `disapproved_orders` TABLE (Legacy - Optional)
-- ============================================================================
-- Legacy table for backward compatibility
-- Contains orders that were disapproved

CREATE TABLE IF NOT EXISTS `disapproved_orders` (
  `ID` INT(11) NOT NULL AUTO_INCREMENT,
  `OrderID` INT(11) NOT NULL COMMENT 'Numeric Order ID',
  `OrderNumber` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Order Number (e.g., GI001)',
  `ProductName` VARCHAR(255) NULL DEFAULT NULL,
  `Address` TEXT NULL DEFAULT NULL COMMENT 'Delivery address',
  `OrderDate` DATETIME NULL DEFAULT NULL,
  `TotalQuotation` DECIMAL(10,2) NULL DEFAULT NULL,
  `Customer_ID` INT(11) NULL DEFAULT NULL,
  `SalesRep_ID` INT(11) NULL DEFAULT NULL,
  `DisapprovedBy` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Who disapproved (Admin or Sales Rep)',
  `DisapprovedBy_ID` INT(11) NULL DEFAULT NULL COMMENT 'ID of admin/sales rep who disapproved',
  `DisapprovalReason` TEXT NULL DEFAULT NULL COMMENT 'Reason for disapproval',
  `Disapproved_Date` DATETIME NULL DEFAULT NULL COMMENT 'Date and time of disapproval',
  `CustomerNotified` TINYINT(1) NULL DEFAULT 0 COMMENT 'Whether customer was notified',
  -- Optional product specification fields (may not exist in all installations)
  `Shape` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Glass shape',
  `Dimension` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Dimensions',
  `Type` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Glass type',
  `Thickness` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Glass thickness',
  `EdgeWork` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Edge work type',
  `FrameType` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Frame type',
  `Engraving` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Engraving details',
  `FileAttached` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Design file path',
  `Created_Date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  INDEX `idx_orderid` (`OrderID`),
  INDEX `idx_ordernumber` (`OrderNumber`),
  INDEX `idx_disapproved_by` (`DisapprovedBy_ID`),
  INDEX `idx_disapproved_date` (`Disapproved_Date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Legacy table: Disapproved orders (for backward compatibility)';

-- ============================================================================
-- 5. ENSURE `payment` TABLE EXISTS (Required for approved orders)
-- ============================================================================
-- Payment records are automatically created when orders are approved
-- This table should already exist, but we'll create it if it doesn't

CREATE TABLE IF NOT EXISTS `payment` (
  `Payment_ID` INT(11) NOT NULL AUTO_INCREMENT,
  `OrderID` INT(11) NOT NULL COMMENT 'Order ID',
  `CustomerName` VARCHAR(255) NULL DEFAULT NULL,
  `ProductName` VARCHAR(255) NULL DEFAULT NULL,
  `PaymentMethod` VARCHAR(50) NULL DEFAULT NULL COMMENT 'E-Wallet, Cash on Delivery, etc.',
  `Amount` DECIMAL(10,2) NOT NULL,
  `ReceiptPath` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Path to uploaded receipt',
  `Status` VARCHAR(50) NOT NULL DEFAULT 'Pending' COMMENT 'Pending, Paid, Refunded',
  `Payment_Date` DATETIME NULL DEFAULT NULL COMMENT 'Date of payment',
  `Created_Date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Payment_ID`),
  INDEX `idx_orderid` (`OrderID`),
  INDEX `idx_status` (`Status`),
  INDEX `idx_payment_date` (`Payment_Date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payment records for orders';

-- ============================================================================
-- NOTES:
-- ============================================================================
-- 1. The `order` table must support the following Status values:
--    - 'Pending Review'
--    - 'Awaiting Admin'
--    - 'Approved'
--    - 'Disapproved'
--    - 'In Fabrication'
--    - 'Ready for Installation'
--    - 'Completed'
--    - 'Cancelled'
--
-- 2. Legacy tables (`awaiting_admin_orders`, `disapproved_orders`) are optional
--    but recommended for backward compatibility. The system checks if they
--    exist before using them.
--
-- 3. The `system_activity_log` table is required for logging all approval
--    and disapproval actions.
--
-- 4. The `payment` table is required as payment records are automatically
--    created when orders are approved.
--
-- 5. All timestamp fields use DATETIME type for consistency.
--
-- 6. Indexes are added for performance on frequently queried fields.
--
-- 7. FOR MYSQL 5.7: The ALTER TABLE statements don't support IF NOT EXISTS.
--    If columns already exist, you'll get an error. To safely add columns,
--    check if they exist first or use a stored procedure.
-- ============================================================================
