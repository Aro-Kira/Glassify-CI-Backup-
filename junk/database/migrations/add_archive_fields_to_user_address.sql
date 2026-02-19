-- Migration: Add archive fields to user_address table
-- This allows addresses to be archived instead of deleted

ALTER TABLE `user_address` 
ADD COLUMN `IsArchived` tinyint(1) DEFAULT 0 AFTER `IsDefault`,
ADD COLUMN `ArchivedAt` timestamp NULL DEFAULT NULL AFTER `IsArchived`;

-- Add index for better query performance
CREATE INDEX `idx_is_archived` ON `user_address` (`IsArchived`);
