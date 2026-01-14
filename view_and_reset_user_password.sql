-- =====================================================
-- View and Reset Password for UserID = 6
-- Run this in phpMyAdmin SQL tab
-- =====================================================

-- Step 1: View user information and password hash
SELECT 
    UserID, 
    First_Name, 
    Last_Name, 
    Email, 
    Role,
    Password as Password_Hash,
    LENGTH(Password) as Hash_Length
FROM `user` 
WHERE UserID = 6;

-- Step 2: IMPORTANT - Passwords cannot be retrieved in plain text!
-- Passwords are hashed using bcrypt/password_hash for security.
-- Even database administrators cannot see the original password.

-- Step 3: Reset Password (uncomment and set your desired password)
-- Replace 'YourNewPassword123!' with your desired password

-- Option A: Using PHP's password_hash (run via PHP script, see check_user_password.php)
-- This is the recommended method as it uses proper hashing

-- Option B: Manual reset (only if you know the current password format)
-- WARNING: Only use this if you know the exact hash format used
-- Uncomment the line below and replace with your desired password hash
-- UPDATE `user` SET Password = '$2y$10$YourHashHere' WHERE UserID = 6;

-- Step 4: Quick Reset - Set a simple password (using bcrypt format)
-- Uncomment and run this to set password to "password123"
-- UPDATE `user` SET Password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE UserID = 6;
-- This hash corresponds to: "password123"

-- For other passwords, use the PHP script at:
-- http://localhost/Glassify-CI/check_user_password.php?userid=6
