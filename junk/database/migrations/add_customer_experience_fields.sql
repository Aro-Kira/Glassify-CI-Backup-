-- ============================================================================
-- Migration: Add Customer Experience Setup Fields
-- Date: 2026-02-04
-- Description: Adds columns to customer table for tracking user experience 
--              setup and role-specific preferences
-- ============================================================================

-- Add role column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = 'customer';
SET @columnname = 'role';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE customer ADD COLUMN `role` ENUM('beginner', 'professional') DEFAULT NULL COMMENT 'User role: beginner or professional'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add setup_status column if it doesn't exist
SET @columnname = 'setup_status';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE customer ADD COLUMN `setup_status` ENUM('pending', 'completed') DEFAULT 'pending' COMMENT 'Whether user has completed setup experience'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add experience_data column if it doesn't exist
SET @columnname = 'experience_data';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE customer ADD COLUMN `experience_data` JSON DEFAULT NULL COMMENT 'JSON object containing all experience setup answers'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add Date_Updated column if it doesn't exist
SET @columnname = 'Date_Updated';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE customer ADD COLUMN `Date_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update timestamp'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add indexes if they don't exist
SET @indexname = 'idx_role';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (index_name = @indexname)) > 0,
  "SELECT 1",
  "ALTER TABLE customer ADD INDEX `idx_role` (`role`)"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_setup_status';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (index_name = @indexname)) > 0,
  "SELECT 1",
  "ALTER TABLE customer ADD INDEX `idx_setup_status` (`setup_status`)"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- Example experience_data JSON structure:
--
-- For Beginner:
-- {
--   "role": "beginner",
--   "experience": "first_time|once_twice|several_times",
--   "confidence": "not_confident|somewhat_confident|confident",
--   "customization_preference": "diy|admin_handled"
-- }
--
-- For Professional:
-- {
--   "role": "professional",
--   "professional_type": "architect|engineer|contractor|other",
--   "experience": "once_twice|several_times|first_time",
--   "confidence": "not_confident|somewhat_confident|confident",
--   "guidance_preference": "guidance_preferred|handle_independently"
-- }
-- ============================================================================
