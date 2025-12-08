-- Migration: Add new address fields to user_address table
-- Run this SQL to add the new columns: UnitHouseNumber, Street, Subdivision, Barangay, Region

ALTER TABLE `user_address`
ADD COLUMN `UnitHouseNumber` varchar(100) DEFAULT NULL AFTER `AddressLine`,
ADD COLUMN `Street` varchar(255) DEFAULT NULL AFTER `UnitHouseNumber`,
ADD COLUMN `Subdivision` varchar(255) DEFAULT NULL AFTER `Street`,
ADD COLUMN `Barangay` varchar(100) DEFAULT NULL AFTER `Subdivision`,
ADD COLUMN `Region` varchar(100) DEFAULT NULL AFTER `Province`;

