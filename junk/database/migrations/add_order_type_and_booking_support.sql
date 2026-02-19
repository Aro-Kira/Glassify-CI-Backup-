-- ============================================================================
-- Migration Script: Add Order Type and Booking Support
-- Description: Adds support for Direct Orders and Site Assessment Orders
-- Based on: docs/DATABASE_CHANGES_SUMMARY.md and docs/IMPLEMENTATION_SUMMARY.md
-- Date: 2024
-- ============================================================================
-- 
-- This script safely adds all necessary columns and updates for:
-- 1. Order Type system (Direct vs Site-Assessed)
-- 2. Booking system for Site Assessment Orders
-- 3. PayMongo payment integration support
--
-- The script checks for existing columns before adding them to prevent errors.
-- ============================================================================

SET @db_name = DATABASE();
SET @table_exists = 0;

-- ============================================================================
-- PART 1: ORDER TABLE CHANGES
-- ============================================================================

-- Check if order table exists
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = @db_name 
AND table_name = 'order';

-- Only proceed if order table exists
SET @sql = IF(@table_exists > 0, 
    '-- Order table exists, proceeding with migration', 
    'SELECT "ERROR: order table does not exist!" as error');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 1: Add OrderType column to order table (if it doesn't exist)
SET @column_exists = 0;
SELECT COUNT(*) INTO @column_exists 
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'order' 
AND column_name = 'OrderType';

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `order` ADD COLUMN `OrderType` enum(''Direct'',''Site-Assessed'') DEFAULT ''Direct'' COMMENT ''Order type: Direct or Site-Assessed'' AFTER `CustomerNotified_Date`',
    'SELECT ''OrderType column already exists, skipping...'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 2: Add PreferredInstallationDate column to order table (if it doesn't exist)
SET @column_exists = 0;
SELECT COUNT(*) INTO @column_exists 
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'order' 
AND column_name = 'PreferredInstallationDate';

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `order` ADD COLUMN `PreferredInstallationDate` date DEFAULT NULL COMMENT ''Customer preferred installation date (captured at checkout)'' AFTER `CustomerNotified_Date`',
    'SELECT ''PreferredInstallationDate column already exists, skipping...'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 3: Add index for OrderType (if it doesn't exist)
SET @index_exists = 0;
SELECT COUNT(*) INTO @index_exists 
FROM information_schema.statistics 
WHERE table_schema = @db_name 
AND table_name = 'order' 
AND index_name = 'idx_order_type';

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE `order` ADD KEY `idx_order_type` (`OrderType`)',
    'SELECT ''idx_order_type index already exists, skipping...'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 4: Update order Status enum to include new statuses
-- This preserves existing statuses and adds new ones
SET @sql = 'ALTER TABLE `order` MODIFY COLUMN `Status` ENUM(
    ''Pending Review'',
    ''Awaiting Admin'',
    ''Ready to Approve'',
    ''Approved'',
    ''Disapproved'',
    ''Pending Payment'',
    ''Pending Booking Confirmation'',
    ''Booking Confirmed'',
    ''Ocular Visit Completed'',
    ''Quotation Available'',
    ''Awaiting Payment'',
    ''Paid'',
    ''In Fabrication'',
    ''Ready for Installation'',
    ''Installation Completed'',
    ''Completed'',
    ''Cancelled'',
    ''Returned''
) DEFAULT ''Pending Review''';

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- PART 2: PRODUCT TABLE CHANGES
-- ============================================================================

-- Check if product table exists
SET @table_exists = 0;
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = @db_name 
AND table_name = 'product';

-- Only proceed if product table exists
SET @sql = IF(@table_exists > 0, 
    '-- Product table exists, proceeding with migration', 
    'SELECT "ERROR: product table does not exist!" as error');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 5: Add OrderType column to product table (if it doesn't exist)
SET @column_exists = 0;
SELECT COUNT(*) INTO @column_exists 
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'product' 
AND column_name = 'OrderType';

-- Check if Subcategory column exists to determine position
SET @subcategory_exists = 0;
SELECT COUNT(*) INTO @subcategory_exists 
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'product' 
AND column_name = 'Subcategory';

SET @position = IF(@subcategory_exists > 0, 'AFTER `Subcategory`', 'AFTER `Status`');

SET @sql = IF(@column_exists = 0,
    CONCAT('ALTER TABLE `product` ADD COLUMN `OrderType` enum(''direct'',''site-assessment'') DEFAULT ''direct'' ', @position),
    'SELECT ''OrderType column already exists in product table, skipping...'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 6: Add PriceMin column to product table (if it doesn't exist)
SET @column_exists = 0;
SELECT COUNT(*) INTO @column_exists 
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'product' 
AND column_name = 'PriceMin';

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `product` ADD COLUMN `PriceMin` decimal(10,2) DEFAULT NULL AFTER `OrderType`',
    'SELECT ''PriceMin column already exists, skipping...'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 7: Add PriceMax column to product table (if it doesn't exist)
SET @column_exists = 0;
SELECT COUNT(*) INTO @column_exists 
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'product' 
AND column_name = 'PriceMax';

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `product` ADD COLUMN `PriceMax` decimal(10,2) DEFAULT NULL AFTER `PriceMin`',
    'SELECT ''PriceMax column already exists, skipping...'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- PART 3: PAYMENT TABLE VERIFICATION
-- ============================================================================

-- Check if payment table exists and Transaction_ID column exists
SET @table_exists = 0;
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = @db_name 
AND table_name = 'payment';

SET @column_exists = 0;
IF @table_exists > 0 THEN
    SELECT COUNT(*) INTO @column_exists 
    FROM information_schema.columns 
    WHERE table_schema = @db_name 
    AND table_name = 'payment' 
    AND column_name = 'Transaction_ID';
END IF;

-- Step 8: Add Transaction_ID column if it doesn't exist
SET @sql = IF(@table_exists > 0 AND @column_exists = 0,
    'ALTER TABLE `payment` ADD COLUMN `Transaction_ID` varchar(100) DEFAULT NULL',
    IF(@table_exists = 0, 
        'SELECT "WARNING: payment table does not exist, skipping Transaction_ID check" as message',
        'SELECT "Transaction_ID column already exists in payment table" as message'));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- PART 4: DATA MIGRATION (Optional - Set defaults for existing data)
-- ============================================================================

-- Set default OrderType for existing orders that might be NULL
SET @sql = 'UPDATE `order` SET `OrderType` = ''Direct'' WHERE `OrderType` IS NULL';
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Set default OrderType for existing products that might be NULL
SET @sql = 'UPDATE `product` SET `OrderType` = ''direct'' WHERE `OrderType` IS NULL';
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- PART 5: VERIFICATION QUERIES
-- ============================================================================

-- Display summary of changes
SELECT 'Migration completed successfully!' as status;
SELECT 'Verification queries:' as info;

-- Verify OrderType column in order table
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN CONCAT('✓ OrderType column exists in order table')
        ELSE '✗ OrderType column NOT found in order table'
    END as verification
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'order' 
AND column_name = 'OrderType';

-- Verify PreferredInstallationDate column in order table
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN CONCAT('✓ PreferredInstallationDate column exists in order table')
        ELSE '✗ PreferredInstallationDate column NOT found in order table'
    END as verification
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'order' 
AND column_name = 'PreferredInstallationDate';

-- Verify OrderType column in product table
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN CONCAT('✓ OrderType column exists in product table')
        ELSE '✗ OrderType column NOT found in product table'
    END as verification
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'product' 
AND column_name = 'OrderType';

-- Verify PriceMin column in product table
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN CONCAT('✓ PriceMin column exists in product table')
        ELSE '✗ PriceMin column NOT found in product table'
    END as verification
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'product' 
AND column_name = 'PriceMin';

-- Verify PriceMax column in product table
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN CONCAT('✓ PriceMax column exists in product table')
        ELSE '✗ PriceMax column NOT found in product table'
    END as verification
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'product' 
AND column_name = 'PriceMax';

-- Verify Transaction_ID column in payment table
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN CONCAT('✓ Transaction_ID column exists in payment table')
        ELSE '✗ Transaction_ID column NOT found in payment table'
    END as verification
FROM information_schema.columns 
WHERE table_schema = @db_name 
AND table_name = 'payment' 
AND column_name = 'Transaction_ID';

-- Verify idx_order_type index
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN CONCAT('✓ idx_order_type index exists')
        ELSE '✗ idx_order_type index NOT found'
    END as verification
FROM information_schema.statistics 
WHERE table_schema = @db_name 
AND table_name = 'order' 
AND index_name = 'idx_order_type';

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================
-- 
-- Next Steps:
-- 1. Review the verification queries above to ensure all changes were applied
-- 2. Update your products to set appropriate OrderType, PriceMin, and PriceMax values
-- 3. Test the Direct Order and Site Assessment Order flows
-- 4. Verify PayMongo payment integration works correctly
--
-- For more details, see:
-- - docs/DATABASE_CHANGES_SUMMARY.md
-- - docs/IMPLEMENTATION_SUMMARY.md
-- ============================================================================
