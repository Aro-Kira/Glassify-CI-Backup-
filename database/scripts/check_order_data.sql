-- Check what's missing for order GI001
-- Run this in phpMyAdmin to see what data exists

-- 1. Check if order exists
SELECT * FROM `order` WHERE OrderNumber = 'GI001' OR OrderID = 4;

-- 2. Check if order_items exist for this order
SELECT * FROM order_items WHERE OrderID = 4;

-- 3. Check if customer exists
SELECT c.*, u.* 
FROM customer c 
LEFT JOIN user u ON c.UserID = u.UserID 
WHERE c.Customer_ID = 1;

-- 4. Check if sales rep exists
SELECT * FROM user WHERE UserID = 3;

-- 5. Check if product exists (if order_items exist)
SELECT p.* FROM product p 
INNER JOIN order_items oi ON p.Product_ID = oi.Product_ID 
WHERE oi.OrderID = 4;

-- 6. Check if awaiting_admin_orders table exists and has data
SELECT * FROM awaiting_admin_orders WHERE OrderID = 'GI001';
