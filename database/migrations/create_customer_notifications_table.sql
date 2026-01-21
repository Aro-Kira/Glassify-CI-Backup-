-- Migration: Create customer_notifications table
-- Description: Table to store notifications sent to customers
-- Date: 2026-01-20

CREATE TABLE IF NOT EXISTS `customer_notifications` (
  `NotificationID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) NOT NULL COMMENT 'Customer ID who receives the notification',
  `Icon` varchar(50) NOT NULL DEFAULT 'fa-info-circle' COMMENT 'Font Awesome icon class (e.g., fa-box-open, fa-check-circle, fa-times-circle)',
  `Type` varchar(50) NOT NULL DEFAULT 'General' COMMENT 'Type of notification: Order, Payment, Delivery, General, System',
  `Title` varchar(255) NOT NULL COMMENT 'Notification title/heading',
  `Message` text NOT NULL COMMENT 'Notification message/description',
  `Status` enum('Unread','Read') DEFAULT 'Unread' COMMENT 'Notification read status',
  `Created_Date` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'When notification was created',
  `Read_Date` datetime DEFAULT NULL COMMENT 'When notification was marked as read',
  `RelatedID` int(11) DEFAULT NULL COMMENT 'Related OrderID, PaymentID, etc.',
  `RelatedType` varchar(50) DEFAULT NULL COMMENT 'Order, Payment, Delivery, etc.',
  `CreatedBy` int(11) DEFAULT NULL COMMENT 'UserID of admin/staff who created the notification',
  PRIMARY KEY (`NotificationID`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_status` (`Status`),
  KEY `idx_type` (`Type`),
  KEY `idx_created_date` (`Created_Date`),
  KEY `idx_related` (`RelatedID`, `RelatedType`),
  CONSTRAINT `fk_customer_notifications_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_customer_notifications_creator` FOREIGN KEY (`CreatedBy`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
