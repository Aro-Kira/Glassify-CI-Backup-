-- Add transaction ID columns for fabrication and installation payments
-- These store the PayMongo payment ID (pay_...) or payment intent ID (pi_...)

ALTER TABLE `order`
ADD COLUMN IF NOT EXISTS `FabricationTransactionID` VARCHAR(255) NULL DEFAULT NULL AFTER `FabricationReceiptPath`,
ADD COLUMN IF NOT EXISTS `InstallationTransactionID` VARCHAR(255) NULL DEFAULT NULL AFTER `InstallationReceiptPath`;

-- Note: Downpayment transaction ID is already stored in the `payment` table's Transaction_ID column
