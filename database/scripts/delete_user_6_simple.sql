-- =====================================================
-- Simple SQL to Delete User with UserID = 6
-- Copy and paste this into phpMyAdmin SQL tab
-- =====================================================

-- First, let's see what we're deleting (optional - just to verify)
SELECT UserID, First_Name, Last_Name, Email, Role FROM `user` WHERE UserID = 6;

-- Update any references that might cause foreign key errors
UPDATE `order` SET SalesRep_ID = NULL WHERE SalesRep_ID = 6;
UPDATE `order` SET ApprovedBy_SalesRep_ID = NULL WHERE ApprovedBy_SalesRep_ID = 6;
UPDATE `order` SET ApprovedBy_Admin_ID = NULL WHERE ApprovedBy_Admin_ID = 6;
UPDATE `order` SET DisapprovedBy_ID = NULL WHERE DisapprovedBy_ID = 6;

-- Update appointments table - AssignedStaff_ID allows SET NULL
UPDATE `appointments` SET AssignedStaff_ID = NULL WHERE AssignedStaff_ID = 6;

UPDATE `inventory` SET UpdatedBy = NULL WHERE UpdatedBy = 6;

-- Delete the user (this will automatically delete customer and addresses due to CASCADE)
DELETE FROM `user` WHERE UserID = 6;

-- Verify deletion
SELECT 'User with UserID 6 has been deleted' AS Result;
