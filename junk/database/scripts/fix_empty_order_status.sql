-- Fix orders with empty status
-- Update orders with empty or NULL status to 'Pending Review'

UPDATE `order` 
SET `Status` = 'Pending Review' 
WHERE `Status` = '' OR `Status` IS NULL;

-- Verify the fix
SELECT OrderID, OrderNumber, Status, SalesRep_ID, OrderDate 
FROM `order` 
WHERE Status = 'Pending Review' OR Status = 'Awaiting Admin' OR Status = 'Ready to Approve';
