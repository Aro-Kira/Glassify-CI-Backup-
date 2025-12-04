-- Update Product Schema for 4 Categories
-- This script updates the database to support the exact product categories and their fields

-- 1. Update customization table to add new fields
ALTER TABLE `customization`
ADD COLUMN IF NOT EXISTS `LEDBacklight` VARCHAR(50) DEFAULT NULL COMMENT 'LED Backlight option for Mirrors',
ADD COLUMN IF NOT EXISTS `DoorOperation` VARCHAR(50) DEFAULT NULL COMMENT 'Door Operation: Swing, Sliding, Fixed for Shower Enclosure/Partition',
ADD COLUMN IF NOT EXISTS `Configuration` VARCHAR(50) DEFAULT NULL COMMENT 'Configuration: 2-panel slider, 3-panel slider, 4-panel slider for Aluminum Doors';

-- 2. Update product table to ensure Category field can store exact category names
-- Categories must be exactly:
-- - Mirrors
-- - Shower Enclosure / Partition
-- - Aluminum Doors
-- - Aluminum and Bathroom Doors
ALTER TABLE `product`
MODIFY COLUMN `Category` VARCHAR(100) NOT NULL COMMENT 'Product Category: Mirrors, Shower Enclosure / Partition, Aluminum Doors, Aluminum and Bathroom Doors';

-- 3. Update order_page table if it exists (add missing fields)
-- Check if order_page table exists and update it
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = 'glassify-test' AND table_name = 'order_page');

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE `order_page`
    ADD COLUMN IF NOT EXISTS `LEDBacklight` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `DoorOperation` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `Configuration` VARCHAR(50) DEFAULT NULL;',
    'SELECT "order_page table does not exist" AS message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Update pending_review_orders table
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = 'glassify-test' AND table_name = 'pending_review_orders');

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE `pending_review_orders`
    ADD COLUMN IF NOT EXISTS `LEDBacklight` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `DoorOperation` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `Configuration` VARCHAR(50) DEFAULT NULL;',
    'SELECT "pending_review_orders table does not exist" AS message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Update awaiting_admin_orders table
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = 'glassify-test' AND table_name = 'awaiting_admin_orders');

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE `awaiting_admin_orders`
    ADD COLUMN IF NOT EXISTS `LEDBacklight` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `DoorOperation` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `Configuration` VARCHAR(50) DEFAULT NULL;',
    'SELECT "awaiting_admin_orders table does not exist" AS message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Update ready_to_approve_orders table
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = 'glassify-test' AND table_name = 'ready_to_approve_orders');

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE `ready_to_approve_orders`
    ADD COLUMN IF NOT EXISTS `LEDBacklight` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `DoorOperation` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `Configuration` VARCHAR(50) DEFAULT NULL;',
    'SELECT "ready_to_approve_orders table does not exist" AS message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. Update approved_orders table
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = 'glassify-test' AND table_name = 'approved_orders');

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE `approved_orders`
    ADD COLUMN IF NOT EXISTS `LEDBacklight` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `DoorOperation` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `Configuration` VARCHAR(50) DEFAULT NULL;',
    'SELECT "approved_orders table does not exist" AS message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 8. Update disapproved_orders table
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = 'glassify-test' AND table_name = 'disapproved_orders');

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE `disapproved_orders`
    ADD COLUMN IF NOT EXISTS `LEDBacklight` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `DoorOperation` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `Configuration` VARCHAR(50) DEFAULT NULL;',
    'SELECT "disapproved_orders table does not exist" AS message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

