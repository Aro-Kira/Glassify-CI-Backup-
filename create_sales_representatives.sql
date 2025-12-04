-- =====================================================
-- Create All Sales Representative Accounts
-- =====================================================
-- Database: glassify-test
-- Table: user
-- =====================================================
-- This single SQL file contains all Sales Representative account creations
-- Run this entire file to create all accounts at once
-- =====================================================

-- Make sure you're using the correct database
USE `glassify-test`;

-- =====================================================
-- INSERT ALL SALES REPRESENTATIVE ACCOUNTS
-- =====================================================
-- Single INSERT statement with multiple accounts
-- =====================================================

INSERT INTO `user` (`UserID`, `First_Name`, `Last_Name`, `Middle_Name`, `Email`, `Password`, `PhoneNum`, `Role`, `Status`, `Date_Created`, `Date_Updated`) VALUES
(NULL, 'Sarah', 'Johnson', 'Marie', 'sarah.johnson.sales@glassify.com', '$2y$10$FL7B8m8K/UlYedd91xTDnO6t86KZI5.7zeIHDxzBZLPLPL6gq1hVG', '09187654321', 'Sales Representative', 'Active', NOW(), NOW()),
(NULL, 'John', 'Sales', 'D.', 'salesrep@glassify.com', '$2y$10$FL7B8m8K/UlYedd91xTDnO6t86KZI5.7zeIHDxzBZLPLPL6gq1hVG', '09123456789', 'Sales Representative', 'Active', NOW(), NOW());

-- =====================================================
-- ACCOUNT CREDENTIALS:
-- =====================================================
-- Account 1:
--   Name: Sarah Marie Johnson
--   Email: sarah.johnson.sales@glassify.com
--   Password: SalesRep123!
--   Phone: 09187654321
--   Role: Sales Representative
--   Status: Active
--
-- Account 2:
--   Name: John D. Sales
--   Email: salesrep@glassify.com
--   Password: SalesRep123!
--   Phone: 09123456789
--   Role: Sales Representative
--   Status: Active
-- =====================================================

-- =====================================================
-- IMPORTANT NOTES:
-- =====================================================
-- 1. All passwords are hashed using Bcrypt
-- 2. Password for all accounts: SalesRep123!
-- 3. Emails must be REAL, WORKING email addresses
-- 4. The email domain must have valid DNS records (MX or A records)
-- 5. Fake emails (test@, example.com, etc.) will be rejected at login
-- 6. UserID is set to NULL for auto-increment
-- 7. All accounts are created with 'Active' status
-- =====================================================

-- =====================================================
-- VERIFICATION QUERIES:
-- =====================================================
-- To verify all Sales Representative accounts were created:
-- SELECT * FROM `user` WHERE `Role` = 'Sales Representative';
--
-- To check a specific account:
-- SELECT * FROM `user` WHERE `Email` = 'sarah.johnson.sales@glassify.com';
-- SELECT * FROM `user` WHERE `Email` = 'salesrep@glassify.com';
-- =====================================================

