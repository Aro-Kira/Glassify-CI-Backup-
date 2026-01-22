-- Migration Script: Add 'Ocular Pending' Status to Order Status Enum
-- Description: Adds 'Ocular Pending' status value to the order Status enum
-- Date: 2026-01-XX
-- 
-- This status is used for orders that have been approved and are waiting
-- for an ocular visit (site assessment) to be completed.

-- Add 'Ocular Pending' to the Status enum
-- Note: This will preserve all existing enum values and add the new one
ALTER TABLE `order` 
MODIFY COLUMN `Status` ENUM(
    'Pending Review',
    'Awaiting Admin',
    'Ready to Approve',
    'Approved',
    'Ocular Pending',
    'Disapproved',
    'In Fabrication',
    'Ready for Installation',
    'Completed',
    'Cancelled',
    'Returned'
) DEFAULT 'Pending Review';

-- Verify the update
SELECT 
    COLUMN_TYPE 
FROM 
    INFORMATION_SCHEMA.COLUMNS 
WHERE 
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'order'
    AND COLUMN_NAME = 'Status';

-- Migration Complete
-- The 'Ocular Pending' status is now available for use in the system
