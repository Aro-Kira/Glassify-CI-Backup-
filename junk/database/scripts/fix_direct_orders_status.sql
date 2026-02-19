-- ============================================================================
-- Fix Direct Orders Status - Update Empty Status Values
-- Glassify-CI - Admin Order Management
-- ============================================================================
-- This script fixes orders with empty Status values that cause action menu
-- issues in the Direct Orders admin interface.
--
-- Fixed Orders:
-- - Order 2 (GI002): Changed empty Status ('') to 'Pending Review'
-- - Order 4 (GI004): Changed empty Status ('') to 'Pending Review'
--
-- Created: January 2026
-- ============================================================================

-- Start transaction for safety
START TRANSACTION;

-- Fix Order 2 (GI002): Set Status to 'Pending Review' if empty
UPDATE `order` 
SET `Status` = 'Pending Review',
    `Updated_Date` = NOW()
WHERE `OrderNumber` = 'GI002' 
  AND (`Status` = '' OR `Status` IS NULL);

-- Fix Order 4 (GI004): Set Status to 'Pending Review' if empty
UPDATE `order` 
SET `Status` = 'Pending Review',
    `Updated_Date` = NOW()
WHERE `OrderNumber` = 'GI004' 
  AND (`Status` = '' OR `Status` IS NULL);

-- Verify the fix
SELECT 
    OrderID,
    OrderNumber,
    Status,
    OrderType,
    PaymentStatus,
    OrderDate,
    Updated_Date
FROM `order` 
WHERE OrderNumber IN ('GI002', 'GI004')
ORDER BY OrderID;

-- Commit transaction
COMMIT;

-- ============================================================================
-- Alternative: Fix all Direct Orders with empty status
-- ============================================================================
-- Uncomment below if you want to fix ALL Direct Orders with empty status
-- (not just GI002 and GI004)

/*
UPDATE `order` 
SET `Status` = 'Pending Review',
    `Updated_Date` = NOW()
WHERE (`Status` = '' OR `Status` IS NULL)
  AND (`OrderType` = 'Direct' OR `OrderType` IS NULL);
*/
