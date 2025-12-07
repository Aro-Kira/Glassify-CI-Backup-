-- Migration: Add missing date columns to order table
-- These columns are needed for appointment-to-order tracking sync
-- Run this SQL to update your existing database

-- Add OcularDate column if it doesn't exist
ALTER TABLE `order` 
ADD COLUMN IF NOT EXISTS `OcularDate` date DEFAULT NULL COMMENT 'Scheduled date for ocular visit' AFTER `PreferredInstallationDate`;

-- Add FabricationDate column if it doesn't exist
ALTER TABLE `order` 
ADD COLUMN IF NOT EXISTS `FabricationDate` date DEFAULT NULL COMMENT 'Scheduled date for fabrication' AFTER `OcularDate`;

-- Add InstallationDate column if it doesn't exist
ALTER TABLE `order` 
ADD COLUMN IF NOT EXISTS `InstallationDate` date DEFAULT NULL COMMENT 'Scheduled date for installation' AFTER `FabricationDate`;

-- Add EstimatedDelivery column if it doesn't exist (if not already present)
ALTER TABLE `order` 
ADD COLUMN IF NOT EXISTS `EstimatedDelivery` date DEFAULT NULL COMMENT 'Estimated delivery/completion date' AFTER `InstallationDate`;

-- Note: If your MySQL version doesn't support IF NOT EXISTS, use this version instead:
-- 
-- ALTER TABLE `order` 
-- ADD COLUMN `OcularDate` date DEFAULT NULL COMMENT 'Scheduled date for ocular visit' AFTER `PreferredInstallationDate`;
-- 
-- ALTER TABLE `order` 
-- ADD COLUMN `FabricationDate` date DEFAULT NULL COMMENT 'Scheduled date for fabrication' AFTER `OcularDate`;
-- 
-- ALTER TABLE `order` 
-- ADD COLUMN `InstallationDate` date DEFAULT NULL COMMENT 'Scheduled date for installation' AFTER `FabricationDate`;
-- 
-- ALTER TABLE `order` 
-- ADD COLUMN `EstimatedDelivery` date DEFAULT NULL COMMENT 'Estimated delivery/completion date' AFTER `InstallationDate`;
