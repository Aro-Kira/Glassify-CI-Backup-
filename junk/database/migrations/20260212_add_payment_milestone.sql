-- ================================================================
-- Add payment milestone tracking to payment table
-- Date: 2026-02-12
-- Purpose: Track which stage each payment belongs to (50% Ocular, 40% Fabrication, 10% Installation)
-- ================================================================

-- Step 1: Add payment_milestone column
ALTER TABLE payment 
ADD COLUMN payment_milestone ENUM('ocular_50', 'fabrication_40', 'installation_10') NULL 
AFTER PaymentMethod,
ADD INDEX idx_payment_milestone (payment_milestone);

-- Step 2: Backfill existing payments with milestone based on order status
-- Ocular payments (50%) - orders in early stages
UPDATE payment p
INNER JOIN `order` o ON p.OrderID = o.OrderID
SET p.payment_milestone = 'ocular_50'
WHERE p.payment_milestone IS NULL
AND o.Status IN ('Booking Submitted', 'Ocular Pending', 'Ocular Visit', 'Approved');

-- Fabrication payments (40%) - orders in fabrication
UPDATE payment p
INNER JOIN `order` o ON p.OrderID = o.OrderID
SET p.payment_milestone = 'fabrication_40'
WHERE p.payment_milestone IS NULL
AND o.Status IN ('In Fabrication', 'Ready for Installation');

-- Installation payments (10%) - orders in installation/completed
UPDATE payment p
INNER JOIN `order` o ON p.OrderID = o.OrderID
SET p.payment_milestone = 'installation_10'
WHERE p.payment_milestone IS NULL
AND o.Status IN ('Installation/Delivery', 'Completed');

-- Step 3: Default remaining payments to ocular (safest assumption)
UPDATE payment
SET payment_milestone = 'ocular_50'
WHERE payment_milestone IS NULL;

-- Step 4: Verify results
SELECT 
    payment_milestone,
    COUNT(*) as count,
    SUM(Amount) as total_amount
FROM payment
GROUP BY payment_milestone
ORDER BY payment_milestone;

-- Step 5: Show sample records by milestone
SELECT 
    p.Payment_ID,
    p.OrderID,
    p.payment_milestone,
    o.Status as order_status,
    p.Amount,
    p.Status as payment_status,
    p.Payment_Date
FROM payment p
LEFT JOIN `order` o ON p.OrderID = o.OrderID
ORDER BY p.payment_milestone, p.Payment_Date DESC
LIMIT 20;
