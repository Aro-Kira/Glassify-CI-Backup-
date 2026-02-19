-- =====================================================
-- Integration: Codes Folder + Inventory Branch
-- Database Updates for glassify-test database
-- =====================================================

USE `glassify-test`;

-- =====================================================
-- 1. Add min_threshold field to inventory_items
-- =====================================================
ALTER TABLE `inventory_items` 
ADD COLUMN IF NOT EXISTS `min_threshold` INT(11) DEFAULT 10 COMMENT 'Minimum stock level for low stock alert' AFTER `InStock`;

-- =====================================================
-- 2. Ensure Status field supports all values
-- =====================================================
ALTER TABLE `inventory_items` 
MODIFY COLUMN `Status` ENUM('In Stock','Low Stock','Out of Stock','New') DEFAULT 'In Stock';

-- =====================================================
-- 3. Create activities table for activity log
-- =====================================================
CREATE TABLE IF NOT EXISTS `activities` (
    `activity_id` INT(11) NOT NULL AUTO_INCREMENT,
    `action` VARCHAR(100) NOT NULL COMMENT 'Action type: Stock added, Stock reduced, Item created, etc.',
    `item_name` VARCHAR(255) DEFAULT NULL COMMENT 'Name of the item affected',
    `change_description` VARCHAR(255) DEFAULT NULL COMMENT 'Brief description of change (e.g., +20 pieces, -5 sheets)',
    `description` TEXT DEFAULT NULL COMMENT 'Detailed description or reason',
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `user_id` INT(11) DEFAULT NULL COMMENT 'User who performed the action',
    `InventoryItemID` INT(11) DEFAULT NULL COMMENT 'Reference to inventory_items table',
    PRIMARY KEY (`activity_id`),
    KEY `idx_timestamp` (`timestamp`),
    KEY `idx_action` (`action`),
    KEY `idx_InventoryItemID` (`InventoryItemID`),
    KEY `idx_user_id` (`user_id`),
    FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 4. Create stock_transactions table for stock history
-- =====================================================
CREATE TABLE IF NOT EXISTS `stock_transactions` (
    `transaction_id` INT(11) NOT NULL AUTO_INCREMENT,
    `InventoryItemID` INT(11) NOT NULL,
    `transaction_type` ENUM('add', 'remove', 'adjust') NOT NULL COMMENT 'Type of transaction',
    `quantity` INT(11) NOT NULL COMMENT 'Amount added/removed',
    `reason` TEXT DEFAULT NULL COMMENT 'Reason for stock change',
    `previous_stock` INT(11) DEFAULT NULL COMMENT 'Stock level before transaction',
    `new_stock` INT(11) DEFAULT NULL COMMENT 'Stock level after transaction',
    `user_id` INT(11) DEFAULT NULL COMMENT 'User who made the transaction',
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`transaction_id`),
    KEY `idx_InventoryItemID` (`InventoryItemID`),
    KEY `idx_timestamp` (`timestamp`),
    KEY `idx_transaction_type` (`transaction_type`),
    KEY `idx_user_id` (`user_id`),
    FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 5. Update existing inventory_items to have min_threshold
-- =====================================================
UPDATE `inventory_items` 
SET `min_threshold` = 10 
WHERE `min_threshold` IS NULL OR `min_threshold` = 0;

-- =====================================================
-- 6. Create index for better performance
-- =====================================================
ALTER TABLE `inventory_items` 
ADD INDEX IF NOT EXISTS `idx_InStock` (`InStock`),
ADD INDEX IF NOT EXISTS `idx_Status` (`Status`),
ADD INDEX IF NOT EXISTS `idx_Category` (`Category`);

-- =====================================================
-- Success Message
-- =====================================================
SELECT 'Database integration completed successfully!' AS Message;


