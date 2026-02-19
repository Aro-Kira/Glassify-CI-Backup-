-- Migration: create role_requests table
CREATE TABLE IF NOT EXISTS `role_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `requested_role` VARCHAR(64) NOT NULL,
  `answers` JSON NOT NULL,
  `confirmation` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('pending','auto_approved','flagged','approved','denied') NOT NULL DEFAULT 'pending',
  `admin_id` INT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` DATETIME NULL,
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
