-- Update payment table to support more payment methods
ALTER TABLE `payment` 
MODIFY COLUMN `PaymentMethod` ENUM('E-Wallet', 'Cash on Delivery', 'Cash', 'Bank Transfer', 'Check', 'Credit Card', 'Debit Card') DEFAULT NULL;
