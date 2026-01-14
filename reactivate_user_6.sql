-- =====================================================
-- Reactivate User with UserID = 6
-- Run this in phpMyAdmin SQL tab
-- =====================================================

-- Check current status
SELECT UserID, First_Name, Last_Name, Email, Role, Status 
FROM `user` 
WHERE UserID = 6;

-- Reactivate the user (set Status to 'Active')
UPDATE `user` 
SET Status = 'Active' 
WHERE UserID = 6;

-- Verify the update
SELECT UserID, First_Name, Last_Name, Email, Role, Status 
FROM `user` 
WHERE UserID = 6;
