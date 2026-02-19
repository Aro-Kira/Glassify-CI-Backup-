-- ================================================================
-- Fix ALL user.Role values to ensure proper classification
-- Date: 2026-02-12
-- Issue: Some users have NULL or empty Role values
-- ================================================================

-- Step 1: Set default 'Customer' role for any user with NULL/empty Role who has a customer record
UPDATE user u
INNER JOIN customer c ON c.UserID = u.UserID
SET u.Role = 'Customer'
WHERE (u.Role IS NULL OR u.Role = '')
AND c.Customer_ID IS NOT NULL;

-- Step 2: Update to 'Beginner' for users who completed setup as beginner
UPDATE user u
INNER JOIN customer c ON c.UserID = u.UserID
SET u.Role = 'Beginner'
WHERE c.role = 'beginner'
AND c.setup_status = 'completed'
AND u.Role IN ('Customer', 'customer', '');

-- Step 3: Update to 'Professional' for users who completed setup as professional
UPDATE user u
INNER JOIN customer c ON c.UserID = u.UserID
SET u.Role = 'Professional'
WHERE c.role = 'professional'
AND c.setup_status = 'completed'
AND u.Role IN ('Customer', 'customer', '');

-- Step 4: Set any remaining NULL/empty roles to 'Customer' (failsafe for orphaned users)
UPDATE user
SET Role = 'Customer'
WHERE (Role IS NULL OR Role = '')
AND UserID NOT IN (SELECT UserID FROM user WHERE Role IN ('Admin', 'Sales Representative', 'admin', 'sales representative'));

-- Step 5: Verify results
SELECT 
    Role,
    COUNT(*) as Count
FROM user
GROUP BY Role
ORDER BY Role;

-- Step 6: Show any remaining problematic records
SELECT 
    UserID, 
    First_Name, 
    Last_Name, 
    Email, 
    Role,
    Status,
    Date_Created
FROM user
WHERE Role IS NULL OR Role = ''
ORDER BY Date_Created DESC;
