-- Migration: add comment column to role_requests
ALTER TABLE `role_requests`
ADD COLUMN `comment` VARCHAR(128) NULL AFTER `status`;
