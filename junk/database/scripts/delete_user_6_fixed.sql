-- =====================================================
-- Fixed SQL to Delete User with UserID = 6
-- This handles the appointment table constraint issue
-- =====================================================

-- Step 1: Check what we're dealing with
SELECT 'Checking user and related records...' AS Status;
SELECT UserID, First_Name, Last_Name, Email, Role FROM `user` WHERE UserID = 6;

-- Step 2: Check for appointments that reference this user
SELECT 'Checking appointments...' AS Status;
SELECT COUNT(*) AS AppointmentCount FROM `appointment` WHERE Admin_ID = 6;
SELECT COUNT(*) AS AppointmentsCount FROM `appointments` WHERE AssignedStaff_ID = 6;

-- Step 3: Update references that allow SET NULL
UPDATE `order` SET SalesRep_ID = NULL WHERE SalesRep_ID = 6;
UPDATE `order` SET ApprovedBy_SalesRep_ID = NULL WHERE ApprovedBy_SalesRep_ID = 6;
UPDATE `order` SET ApprovedBy_Admin_ID = NULL WHERE ApprovedBy_Admin_ID = 6;
UPDATE `order` SET DisapprovedBy_ID = NULL WHERE DisapprovedBy_ID = 6;

-- Step 4: Handle appointments table (plural) - this allows SET NULL
UPDATE `appointments` SET AssignedStaff_ID = NULL WHERE AssignedStaff_ID = 6;

-- Step 5: Handle appointment table (singular) - Admin_ID has RESTRICT constraint
-- You have two options:

-- OPTION A: Delete appointments that reference this user (recommended if you want to remove all traces)
DELETE FROM `appointment` WHERE Admin_ID = 6;

-- OPTION B: If you want to keep appointments, you need to reassign them to another admin first
-- Uncomment the line below and replace 1 with another admin's UserID:
-- UPDATE `appointment` SET Admin_ID = 1 WHERE Admin_ID = 6;

-- Step 6: Update other references
UPDATE `inventory` SET UpdatedBy = NULL WHERE UpdatedBy = 6;

-- Step 7: Handle projectschedule if needed (has RESTRICT constraint)
-- Check first:
SELECT COUNT(*) AS ScheduleCount FROM `projectschedule` WHERE Admin_ID = 6;
-- If schedules exist, you need to delete or reassign them:
-- DELETE FROM `projectschedule` WHERE Admin_ID = 6;
-- OR: UPDATE `projectschedule` SET Admin_ID = 1 WHERE Admin_ID = 6;

-- Step 8: Now delete the user
DELETE FROM `user` WHERE UserID = 6;

-- Step 9: Verify deletion
SELECT 'User deleted successfully' AS Result;
SELECT COUNT(*) AS RemainingUsers FROM `user` WHERE UserID = 6;
