-- ============================================================================
-- Migration: Add Installation Payment Workflow Statuses
-- Date: 2026-02-10
-- Purpose: Support 5-day payment grace period after installation completion
-- ============================================================================

-- Step 1: Modify appointments.Status ENUM to support installation payment workflow
ALTER TABLE `appointments`
MODIFY COLUMN `Status` ENUM(
    'In Progress',           -- Work is actively happening
    'Installed',             -- Physical installation complete, awaiting 10% payment (within 5-day grace period)
    'Complete',              -- Installation AND payment both done ✓
    'Payment Overdue',       -- Installation done but payment past 5-day deadline
    'Cancelled',             -- Appointment cancelled
    'Returned'               -- Product removed due to non-payment
) DEFAULT 'In Progress'
COMMENT 'Appointment status - Installed = work done but payment pending, Complete = fully done with payment';

-- Step 2: Add fields to track payment deadline
ALTER TABLE `appointments`
ADD COLUMN IF NOT EXISTS `InstallationCompletedDate` DATETIME DEFAULT NULL 
    COMMENT 'Date/time when physical installation was completed',
ADD COLUMN IF NOT EXISTS `PaymentDueDate` DATETIME DEFAULT NULL 
    COMMENT 'Deadline for 10% payment (InstallationCompletedDate + 5 days)',
ADD COLUMN IF NOT EXISTS `PaymentGracePeriodDays` INT DEFAULT 5 
    COMMENT 'Number of days customer has to pay after installation (default 5)';

-- Step 3: Add indexes for performance
ALTER TABLE `appointments`
ADD INDEX IF NOT EXISTS `idx_payment_due` (`PaymentDueDate`),
ADD INDEX IF NOT EXISTS `idx_installation_completed` (`InstallationCompletedDate`);

-- Step 4: Add trigger to automatically set payment due date when status changes to 'Installed'
DELIMITER $$

DROP TRIGGER IF EXISTS `set_installation_payment_due_date`$$

CREATE TRIGGER `set_installation_payment_due_date`
BEFORE UPDATE ON `appointments`
FOR EACH ROW
BEGIN
    -- When status changes to 'Installed', set the installation completion date and payment due date
    IF NEW.Status = 'Installed' AND OLD.Status != 'Installed' THEN
        IF NEW.InstallationCompletedDate IS NULL THEN
            SET NEW.InstallationCompletedDate = NOW();
        END IF;
        
        -- Calculate payment due date (5 days from completion)
        IF NEW.PaymentDueDate IS NULL AND NEW.InstallationCompletedDate IS NOT NULL THEN
            SET NEW.PaymentDueDate = DATE_ADD(NEW.InstallationCompletedDate, 
                INTERVAL COALESCE(NEW.PaymentGracePeriodDays, 5) DAY);
        END IF;
    END IF;
    
    -- When payment is received (status changes to 'Complete'), clear the payment due date
    IF NEW.Status = 'Complete' AND OLD.Status != 'Complete' THEN
        SET NEW.PaymentDueDate = NULL;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- Notes for Admins:
-- ============================================================================
-- 
-- STATUS WORKFLOW:
-- 1. "In Progress" - Installation team is working
-- 2. "Installed" - Physical work done, 5-day payment window starts
-- 3. "Complete" - Payment received, order fully done
-- 4. "Payment Overdue" - Past 5 days, payment not received (manual intervention needed)
-- 5. "Returned" - Product removed due to non-payment
-- 
-- AUTOMATIC BEHAVIOR:
-- - When you mark status as "Installed", the system automatically:
--   * Records InstallationCompletedDate (current date/time)
--   * Calculates PaymentDueDate (5 days later)
--   * Shows countdown in UI
-- 
-- - Admin can manually check overdue payments and mark as "Payment Overdue"
-- - If product needs to be removed, mark as "Returned"
-- 
-- ============================================================================
