-- Add fields to customization table to store complete order details
ALTER TABLE `customization` 
ADD COLUMN `OrderID` int(11) DEFAULT NULL AFTER `CustomizationID`,
ADD COLUMN `ProductName` varchar(255) DEFAULT NULL AFTER `Product_ID`,
ADD COLUMN `DeliveryAddress` varchar(255) DEFAULT NULL AFTER `Engraving`,
ADD COLUMN `OrderDate` datetime DEFAULT NULL AFTER `DeliveryAddress`,
ADD COLUMN `TotalQuotation` decimal(12,2) DEFAULT 0.00 AFTER `OrderDate`,
ADD INDEX `idx_orderid` (`OrderID`),
ADD INDEX `idx_customer_order` (`Customer_ID`, `OrderID`);

