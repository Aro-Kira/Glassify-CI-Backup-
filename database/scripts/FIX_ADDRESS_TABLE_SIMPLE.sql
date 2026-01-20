-- ============================================
-- FIX user_address TABLE - Add Missing Columns (Simple Version)
-- ============================================
-- Run this SQL in phpMyAdmin or your MySQL client
-- This will add the missing columns
-- If a column already exists, you'll get an error - that's okay, just continue

-- Add UnitHouseNumber (if it doesn't exist, you'll get an error - ignore it)
ALTER TABLE `user_address` 
ADD COLUMN `UnitHouseNumber` varchar(100) DEFAULT NULL AFTER `AddressLine`;

-- Add Street (if it doesn't exist, you'll get an error - ignore it)
ALTER TABLE `user_address` 
ADD COLUMN `Street` varchar(255) DEFAULT NULL AFTER `UnitHouseNumber`;

-- Add Subdivision (if it doesn't exist, you'll get an error - ignore it)
ALTER TABLE `user_address` 
ADD COLUMN `Subdivision` varchar(255) DEFAULT NULL AFTER `Street`;

-- Add Barangay (if it doesn't exist, you'll get an error - ignore it)
ALTER TABLE `user_address` 
ADD COLUMN `Barangay` varchar(100) DEFAULT NULL AFTER `Subdivision`;

-- Add Region (if it doesn't exist, you'll get an error - ignore it)
ALTER TABLE `user_address` 
ADD COLUMN `Region` varchar(100) DEFAULT NULL AFTER `Province`;

-- After running, check the table structure to verify columns were added
-- DESCRIBE user_address;

