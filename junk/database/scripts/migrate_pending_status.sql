-- Migration script to update old 'Pending' status to 'Pending Review'
-- Run this to update existing orders with the old status format

UPDATE `order` 
SET `Status` = 'Pending Review' 
WHERE `Status` = 'Pending';

-- Verify the update
SELECT `Status`, COUNT(*) as count 
FROM `order` 
GROUP BY `Status`;
