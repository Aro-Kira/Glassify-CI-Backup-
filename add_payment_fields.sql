-- Add CustomerName, ProductName, and PaymentMethod columns to payment table
ALTER TABLE `payment` 
ADD COLUMN `CustomerName` VARCHAR(255) NULL AFTER `OrderID`,
ADD COLUMN `ProductName` VARCHAR(255) NULL AFTER `CustomerName`,
ADD COLUMN `PaymentMethod` ENUM('E-Wallet', 'Cash on Delivery') NULL AFTER `ProductName`;

