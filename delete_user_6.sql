-- =====================================================
-- SQL Script to Delete User with UserID = 6
-- Run this in phpMyAdmin SQL tab
-- =====================================================

-- IMPORTANT: This script will delete user with UserID 6
-- and handle related records according to foreign key constraints

SET FOREIGN_KEY_CHECKS = 0;

-- Step 1: Check if user exists (view only - won't delete)
SELECT UserID, First_Name, Last_Name, Email, Role 
FROM `user` 
WHERE UserID = 6;

-- Step 2: Update references to NULL where foreign keys allow SET NULL
-- (This prevents foreign key constraint errors)

-- Update orders where user is SalesRep
UPDATE `order` 
SET SalesRep_ID = NULL 
WHERE SalesRep_ID = 6;

-- Update orders where user approved/disapproved
UPDATE `order` 
SET ApprovedBy_SalesRep_ID = NULL 
WHERE ApprovedBy_SalesRep_ID = 6;

UPDATE `order` 
SET ApprovedBy_Admin_ID = NULL 
WHERE ApprovedBy_Admin_ID = 6;

UPDATE `order` 
SET DisapprovedBy_ID = NULL 
WHERE DisapprovedBy_ID = 6;

-- Update appointments
UPDATE `appointment` 
SET Admin_ID = NULL 
WHERE Admin_ID = 6;

UPDATE `appointment` 
SET AssignedStaff_ID = NULL 
WHERE AssignedStaff_ID = 6;

-- Update inventory
UPDATE `inventory` 
SET UpdatedBy = NULL 
WHERE UpdatedBy = 6;

-- Update stock_transactions (if table exists)
UPDATE `stock_transactions` 
SET user_id = NULL 
WHERE user_id = 6;

-- Update activities (if table exists)
UPDATE `activities` 
SET user_id = NULL 
WHERE user_id = 6;

-- Step 3: Check for RESTRICT constraints that might prevent deletion
-- If user is referenced in projectschedule with RESTRICT, you must update/delete those first
-- Uncomment and run this to check:
-- SELECT * FROM `projectschedule` WHERE Admin_ID = 6;

-- If projectschedule records exist, update them first:
-- UPDATE `projectschedule` SET Admin_ID = NULL WHERE Admin_ID = 6;
-- OR delete them if appropriate:
-- DELETE FROM `projectschedule` WHERE Admin_ID = 6;

-- Step 4: Check if user is a customer with orders
-- If customer has orders, you may need to delete or reassign orders first
-- Uncomment to check:
-- SELECT o.OrderID, o.OrderNumber, o.Status 
-- FROM `order` o
-- INNER JOIN `customer` c ON o.Customer_ID = c.Customer_ID
-- WHERE c.UserID = 6;

-- If orders exist for this customer, you may need to delete them first:
-- DELETE FROM `order` WHERE Customer_ID IN (SELECT Customer_ID FROM `customer` WHERE UserID = 6);

-- Step 5: Delete the user
-- This will automatically delete related records due to CASCADE:
-- - customer record (CASCADE)
-- - user_address records (CASCADE)
DELETE FROM `user` 
WHERE UserID = 6;

SET FOREIGN_KEY_CHECKS = 1;

-- Step 6: Verify deletion
SELECT 'User deleted successfully' AS Status;
SELECT COUNT(*) AS RemainingUsers FROM `user` WHERE UserID = 6;
