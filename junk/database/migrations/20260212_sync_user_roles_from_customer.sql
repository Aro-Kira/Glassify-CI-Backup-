-- ================================================================
-- Sync user.Role with customer.role for existing users
-- Date: 2026-02-12
-- Issue: Users who completed experience setup have customer.role 
--        (beginner/professional) but user.Role stayed as 'Customer'
-- ================================================================

-- Update user.Role based on customer.role for Beginner users
UPDATE user u
INNER JOIN customer c ON c.UserID = u.UserID
SET u.Role = 'Beginner'
WHERE c.role = 'beginner'
AND u.Role = 'Customer'
AND c.setup_status = 'completed';

-- Update user.Role based on customer.role for Professional users  
UPDATE user u
INNER JOIN customer c ON c.UserID = u.UserID
SET u.Role = 'Professional'
WHERE c.role = 'professional'
AND u.Role = 'Customer'
AND c.setup_status = 'completed';

-- Display summary of changes
SELECT 
    'Users updated' as Status,
    COUNT(*) as Count
FROM user u
INNER JOIN customer c ON c.UserID = u.UserID
WHERE (
    (c.role = 'beginner' AND u.Role = 'Beginner') 
    OR 
    (c.role = 'professional' AND u.Role = 'Professional')
)
AND c.setup_status = 'completed';
