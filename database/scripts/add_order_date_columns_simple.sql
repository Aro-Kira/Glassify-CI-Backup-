-- Migration: Add missing date columns to order table (Simple version)
-- Run this SQL in phpMyAdmin or your MySQL client
-- If you get "Duplicate column" errors, those columns already exist - that's fine!

ALTER TABLE `order` 
ADD COLUMN `OcularDate` date DEFAULT NULL COMMENT 'Scheduled date for ocular visit' AFTER `PreferredInstallationDate`;

ALTER TABLE `order` 
ADD COLUMN `FabricationDate` date DEFAULT NULL COMMENT 'Scheduled date for fabrication' AFTER `OcularDate`;

ALTER TABLE `order` 
ADD COLUMN `InstallationDate` date DEFAULT NULL COMMENT 'Scheduled date for installation' AFTER `FabricationDate`;

ALTER TABLE `order` 
ADD COLUMN `EstimatedDelivery` date DEFAULT NULL COMMENT 'Estimated delivery/completion date' AFTER `InstallationDate`;
