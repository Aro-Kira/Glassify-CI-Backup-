-- ============================================================================
-- Migration Script: Add Order Type and Booking Support (Simple Version)
-- Description: Adds support for Direct Orders and Site Assessment Orders
-- Based on: docs/DATABASE_CHANGES_SUMMARY.md and docs/IMPLEMENTATION_SUMMARY.md
-- Date: 2024
-- ============================================================================
-- 
-- IMPORTANT: Before running this script:
-- 1. Backup your database
-- 2. Check if columns already exist (see verification section at bottom)
-- 3. Run this script in a test environment first
--
-- This script adds:
-- 1. OrderType column to order table
-- 2. PreferredInstallationDate column to order table
-- 3. OrderType, PriceMin, PriceMax columns to product table
-- 4. Updates Status enum in order table
-- 5. Adds index on OrderType
-- ============================================================================

-- ============================================================================
-- PART 1: ORDER TABLE CHANGES
-- ============================================================================

-- Add OrderType column to order table
-- Note: If column already exists, this will fail. Remove this section if column exists.
ALTER TABLE `order` 
ADD COLUMN `OrderType` enum('Direct','Site-Assessed') DEFAULT 'Direct' 
COMMENT 'Order type: Direct or Site-Assessed' 
AFTER `CustomerNotified_Date`;

-- Add PreferredInstallationDate column to order table
-- Note: If column already exists, this will fail. Remove this section if column exists.
ALTER TABLE `order` 
ADD COLUMN `PreferredInstallationDate` date DEFAULT NULL 
COMMENT 'Customer preferred installation date (captured at checkout)' 
AFTER `CustomerNotified_Date`;

-- Add index for OrderType
-- Note: If index already exists, this will fail. Remove this section if index exists.
ALTER TABLE `order` 
ADD KEY `idx_order_type` (`OrderType`);

-- Update order Status enum to include new statuses
-- This preserves existing statuses and adds new ones
ALTER TABLE `order` 
MODIFY COLUMN `Status` ENUM(
    'Pending Review',
    'Awaiting Admin',
    'Ready to Approve',
    'Approved',
    'Disapproved',
    'Pending Payment',
    'Pending Booking Confirmation',
    'Booking Confirmed',
    'Ocular Visit Completed',
    'Quotation Available',
    'Awaiting Payment',
    'Paid',
    'In Fabrication',
    'Ready for Installation',
    'Installation Completed',
    'Completed',
    'Cancelled',
    'Returned'
) DEFAULT 'Pending Review';

-- ============================================================================
-- PART 2: PRODUCT TABLE CHANGES
-- ============================================================================

-- Add OrderType column to product table
-- Note: Adjust the AFTER clause based on your table structure
-- If Subcategory column doesn't exist, change to AFTER `Status`
ALTER TABLE `product` 
ADD COLUMN `OrderType` enum('direct','site-assessment') DEFAULT 'direct' 
AFTER `Subcategory`;

-- Add PriceMin column to product table
ALTER TABLE `product` 
ADD COLUMN `PriceMin` decimal(10,2) DEFAULT NULL 
AFTER `OrderType`;

-- Add PriceMax column to product table
ALTER TABLE `product` 
ADD COLUMN `PriceMax` decimal(10,2) DEFAULT NULL 
AFTER `PriceMin`;

-- ============================================================================
-- PART 3: PAYMENT TABLE VERIFICATION
-- ============================================================================

-- Verify Transaction_ID column exists in payment table
-- If it doesn't exist, uncomment the line below:
-- ALTER TABLE `payment` ADD COLUMN `Transaction_ID` varchar(100) DEFAULT NULL;

-- ============================================================================
-- PART 4: DATA MIGRATION (Set defaults for existing data)
-- ============================================================================

-- Set default OrderType for existing orders that might be NULL
UPDATE `order` 
SET `OrderType` = 'Direct' 
WHERE `OrderType` IS NULL;

-- Set default OrderType for existing products that might be NULL
UPDATE `product` 
SET `OrderType` = 'direct' 
WHERE `OrderType` IS NULL;

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================
-- Run these queries after migration to verify everything was added correctly:

-- Check order table structure
-- DESCRIBE `order`;

-- Check product table structure
-- DESCRIBE `product`;

-- Verify OrderType values in orders
-- SELECT `OrderType`, COUNT(*) as count FROM `order` GROUP BY `OrderType`;

-- Verify OrderType values in products
-- SELECT `OrderType`, COUNT(*) as count FROM `product` GROUP BY `OrderType`;

-- Check orders with PreferredInstallationDate
-- SELECT COUNT(*) as orders_with_preferred_date FROM `order` WHERE `PreferredInstallationDate` IS NOT NULL;

-- Check products with price ranges
-- SELECT COUNT(*) as products_with_price_range FROM `product` WHERE `PriceMin` IS NOT NULL AND `PriceMax` IS NOT NULL;

-- Check payment records with Transaction_ID (PayMongo)
-- SELECT COUNT(*) as payments_with_transaction_id FROM `payment` WHERE `Transaction_ID` IS NOT NULL;

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================
-- 
-- Next Steps:
-- 1. Run the verification queries above (uncomment them)
-- 2. Update your products to set appropriate OrderType, PriceMin, and PriceMax values
-- 3. Test the Direct Order and Site Assessment Order flows
-- 4. Verify PayMongo payment integration works correctly
--
-- For more details, see:
-- - docs/DATABASE_CHANGES_SUMMARY.md
-- - docs/IMPLEMENTATION_SUMMARY.md
-- ============================================================================
