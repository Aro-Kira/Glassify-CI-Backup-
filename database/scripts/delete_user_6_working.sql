-- =====================================================
-- Working SQL to Delete User with UserID = 6
-- Run this in phpMyAdmin SQL tab
-- =====================================================

-- Update references to prevent foreign key errors
UPDATE `order` SET SalesRep_ID = NULL WHERE SalesRep_ID = 6;
UPDATE `order` SET ApprovedBy_SalesRep_ID = NULL WHERE ApprovedBy_SalesRep_ID = 6;
UPDATE `order` SET ApprovedBy_Admin_ID = NULL WHERE ApprovedBy_Admin_ID = 6;
UPDATE `order` SET DisapprovedBy_ID = NULL WHERE DisapprovedBy_ID = 6;

-- Update appointments table - AssignedStaff_ID allows SET NULL
UPDATE `appointments` SET AssignedStaff_ID = NULL WHERE AssignedStaff_ID = 6;

-- Update inventory
UPDATE `inventory` SET UpdatedBy = NULL WHERE UpdatedBy = 6;

-- Delete the user (will automatically delete customer and addresses due to CASCADE)
DELETE FROM `user` WHERE UserID = 6;

-- Verify deletion
SELECT 'User with UserID 6 has been deleted' AS Result;
