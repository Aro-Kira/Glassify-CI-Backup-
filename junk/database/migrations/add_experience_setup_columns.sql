-- Add experience setup columns to customer table
-- Run this SQL in your database

ALTER TABLE `customer` 
ADD COLUMN `setup_status` ENUM('pending', 'completed') DEFAULT 'pending' AFTER `Status`,
ADD COLUMN `experience_data` TEXT NULL AFTER `setup_status`;

-- NOTE: New customers start with 'pending' status
-- They must complete the "Set Up Your Experience" form to get 'completed' status
-- DO NOT auto-update existing customers to 'completed' as they need to complete setup too
