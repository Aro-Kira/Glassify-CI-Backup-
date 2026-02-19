-- Migration: backfill empty/NULL user.Role and make column NOT NULL with default 'Customer'

-- 1) Backfill existing empty or NULL roles to a safe default
UPDATE `user` SET `Role` = 'Customer' WHERE `Role` IS NULL OR TRIM(`Role`) = '';

-- 2) Make Role column NOT NULL with a default
ALTER TABLE `user`
    MODIFY `Role` VARCHAR(64) NOT NULL DEFAULT 'Customer';

-- Note: Run this on a copy/after DB backup. If your deployment uses a different column name or casing,
-- adjust accordingly. You can instead run only the UPDATE if you prefer not to change schema.
