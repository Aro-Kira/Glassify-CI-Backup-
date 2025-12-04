-- =====================================================
-- Create Sales Representative Account
-- =====================================================
-- This SQL script creates a new Sales Representative account
-- directly in the user table.
--
-- Instructions:
-- 1. Replace the placeholder values below with actual data
-- 2. Generate a password hash using PHP: password_hash('your_password', PASSWORD_BCRYPT)
-- 3. Run this script in phpMyAdmin or MySQL command line
-- =====================================================

-- Example: Create a Sales Representative account
-- Replace the values with your actual data

INSERT INTO `user` 
(`First_Name`, `Middle_Name`, `Last_Name`, `Email`, `Password`, `PhoneNum`, `Role`, `Status`, `Date_Created`, `Date_Updated`)
VALUES
(
    'John',                    -- First_Name: Replace with actual first name
    'D.',                      -- Middle_Name: Replace with actual middle name (can be empty string '')
    'Sales',                   -- Last_Name: Replace with actual last name
    'sales@glassify.com',      -- Email: Replace with REAL, WORKING email address
    '$2y$10$YourHashedPasswordHere',  -- Password: Replace with Bcrypt hash (see instructions below)
    '09123456789',             -- PhoneNum: Replace with actual phone number
    'Sales Representative',     -- Role: Must be exactly 'Sales Representative'
    'Active',                  -- Status: 'Active' or 'Inactive'
    NOW(),                     -- Date_Created: Current timestamp
    NOW()                      -- Date_Updated: Current timestamp
);

-- =====================================================
-- HOW TO GENERATE PASSWORD HASH:
-- =====================================================
-- Option 1: Using PHP (create a temporary file):
--   <?php
--   echo password_hash('your_password_here', PASSWORD_BCRYPT);
--   ?>
--   Run: php hash_password.php
--
-- Option 2: Using online Bcrypt generator (less secure):
--   Visit: https://bcrypt-generator.com/
--   Enter your password and copy the hash
--
-- Option 3: Using MySQL (if you have a function):
--   SELECT SHA2('your_password_here', 256); -- Note: This is SHA2, not Bcrypt
--   For Bcrypt, you MUST use PHP's password_hash() function
-- =====================================================

-- =====================================================
-- IMPORTANT NOTES:
-- =====================================================
-- 1. Email MUST be a real, working email address
--    - Fake emails (test@, example.com, etc.) will be rejected at login
--    - The email domain must have valid DNS records (MX or A records)
--
-- 2. Password MUST be hashed using Bcrypt
--    - Never insert plain text passwords
--    - Use PHP's password_hash() function: password_hash('password', PASSWORD_BCRYPT)
--
-- 3. Role MUST be exactly: 'Sales Representative'
--    - Case-sensitive
--    - Must match the enum value in the database
--
-- 4. After creating the account, the Sales Rep can log in at:
--    http://localhost/Glassify-CI/sales-login
-- =====================================================
