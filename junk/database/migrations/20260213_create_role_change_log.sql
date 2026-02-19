-- Migration: create role_change_log table for auditing role changes
CREATE TABLE IF NOT EXISTS `role_change_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `old_role` VARCHAR(64) NULL,
  `new_role` VARCHAR(64) NULL,
  `changed_by` INT NULL,
  `reason` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
