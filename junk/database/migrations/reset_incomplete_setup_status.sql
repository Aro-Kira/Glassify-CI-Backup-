-- ============================================================================
-- Migration: Reset Setup Status for Customers Without Complete Experience Data
-- Date: 2026-02-04
-- Description: Resets setup_status to 'pending' for all customers who haven't
--              actually completed the user experience setup form.
-- ============================================================================

-- Reset all customers to 'pending' status if they don't have experience_data filled
-- This ensures only customers who have actually completed the setup are marked as 'completed'
UPDATE `customer` 
SET `setup_status` = 'pending' 
WHERE `experience_data` IS NULL 
   OR `experience_data` = '' 
   OR `experience_data` = '{}' 
   OR `experience_data` = 'null'
   OR JSON_VALID(`experience_data`) = 0
   OR JSON_LENGTH(`experience_data`) = 0;

-- Also reset customers who have role set to NULL (haven't selected beginner/professional)
UPDATE `customer` 
SET `setup_status` = 'pending' 
WHERE `role` IS NULL;

-- Verify: Show counts after update
SELECT 
    `setup_status`,
    COUNT(*) as count
FROM `customer`
GROUP BY `setup_status`;

-- Show customers with incomplete setup
SELECT 
    Customer_ID,
    UserID,
    role,
    setup_status,
    CASE 
        WHEN experience_data IS NULL THEN 'NULL'
        WHEN experience_data = '' THEN 'EMPTY'
        ELSE LEFT(experience_data, 50)
    END as experience_data_preview
FROM `customer`
WHERE `setup_status` = 'pending'
LIMIT 20;
