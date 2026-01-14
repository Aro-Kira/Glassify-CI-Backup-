-- Migration Script: Update Order Status Enum
-- Description: Updates the order Status enum to the new status values
-- Date: 2026-01-12
-- 
-- New Status Values:
-- - Pending Payment
-- - Paid
-- - Payment Verified
-- - Approved
-- - In Fabrication
-- - Scheduling
-- - For Installation / Shipping
-- - Completed
-- - Cancelled (kept for backward compatibility)
-- - Returned (kept for backward compatibility)

-- Step 1: Map existing orders to new statuses before changing enum
-- This ensures data integrity during migration

-- Map 'Pending Review' -> 'Pending Payment'
UPDATE `order` SET `Status` = 'Pending Payment' WHERE `Status` = 'Pending Review';

-- Map 'Awaiting Admin' -> 'Pending Payment' (orders awaiting admin review need payment)
UPDATE `order` SET `Status` = 'Pending Payment' WHERE `Status` = 'Awaiting Admin';

-- Map 'Ready to Approve' -> 'Pending Payment' (orders ready to approve need payment)
UPDATE `order` SET `Status` = 'Pending Payment' WHERE `Status` = 'Ready to Approve';

-- Map 'Approved' -> 'Approved' (keep as is, but will need payment verification)
-- Note: If PaymentStatus = 'Paid', we might want to set Status = 'Payment Verified' instead
-- This will be handled in a separate update after enum change

-- Map 'In Fabrication' -> 'In Fabrication' (keep as is)

-- Map 'Ready for Installation' -> 'For Installation / Shipping'
UPDATE `order` SET `Status` = 'For Installation / Shipping' WHERE `Status` = 'Ready for Installation';

-- Map 'Completed' -> 'Completed' (keep as is)

-- Map 'Disapproved' -> 'Cancelled' (disapproved orders are cancelled)
UPDATE `order` SET `Status` = 'Cancelled' WHERE `Status` = 'Disapproved';

-- Keep 'Cancelled' and 'Returned' as is

-- Step 2: Update orders with PaymentStatus = 'Paid' to appropriate status
-- If order is 'Approved' and payment is 'Paid', set to 'Paid'
UPDATE `order` 
SET `Status` = 'Paid' 
WHERE `Status` = 'Approved' 
AND `PaymentStatus` = 'Paid';

-- If order is 'Approved' and payment is 'Paid' and has been verified, set to 'Payment Verified'
-- (This assumes there's a payment verification process - adjust based on your business logic)
-- For now, we'll set 'Paid' status and let the system update to 'Payment Verified' when verified

-- Step 3: Modify the enum column to include new values
-- Note: MySQL/MariaDB requires ALTER TABLE to modify enum values
ALTER TABLE `order` 
MODIFY COLUMN `Status` ENUM(
    'Pending Payment',
    'Paid',
    'Payment Verified',
    'Approved',
    'In Fabrication',
    'Scheduling',
    'For Installation / Shipping',
    'Completed',
    'Cancelled',
    'Returned'
) DEFAULT 'Pending Payment';

-- Step 4: Update default status for new orders
-- The DEFAULT is already set in the ALTER TABLE above

-- Step 5: Verify the migration
-- Run these queries to verify the migration:
-- SELECT Status, COUNT(*) as count FROM `order` GROUP BY Status;
-- SELECT * FROM `order` WHERE Status NOT IN ('Pending Payment', 'Paid', 'Payment Verified', 'Approved', 'In Fabrication', 'Scheduling', 'For Installation / Shipping', 'Completed', 'Cancelled', 'Returned');

-- Migration Complete
-- Note: After running this migration, update the application code to use the new status values
