-- SQL script to add password reset fields to user table
-- Run this in phpMyAdmin or MySQL client

ALTER TABLE `user` 
ADD COLUMN `reset_token` VARCHAR(255) NULL DEFAULT NULL AFTER `Password`,
ADD COLUMN `reset_token_expiry` DATETIME NULL DEFAULT NULL AFTER `reset_token`,
ADD INDEX `idx_reset_token` (`reset_token`);

