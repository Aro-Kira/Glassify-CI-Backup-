-- Migration: add CustomerRoleAtOrder snapshot column to order table and backfill from customer
ALTER TABLE `order`
  ADD COLUMN `CustomerRoleAtOrder` VARCHAR(64) NULL AFTER `Customer_ID`;

-- Backfill CustomerRoleAtOrder from customer.role where available
UPDATE `order` o
JOIN `customer` c ON c.Customer_ID = o.Customer_ID
SET o.CustomerRoleAtOrder = c.role
WHERE o.CustomerRoleAtOrder IS NULL;
