-- ============================================================================
-- MERGE MISSING TABLES FROM latest_glassifydb aro.sql
-- ============================================================================
-- This script adds missing tables from the aro database dump to your local database
-- Date: 2026-01-21
-- 
-- Missing tables identified:
-- 1. customer_customizations
-- 2. customer_notifications (may already exist from recent changes)
-- 3. customization_field_configs
-- 4. employee_archive
-- 5. enduser_archive
-- 6. order_items
-- 7. product_series
-- 8. product_standard_sizes
-- 9. product_tag_prices
-- 10. return_order
-- 11. status_history
-- ============================================================================

SET FOREIGN_KEY_CHECKS=0;

-- ============================================================================
-- 1. CUSTOMER_CUSTOMIZATIONS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `customer_customizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `selections` longtext NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_customer_product` (`customer_id`,`product_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 2. CUSTOMER_NOTIFICATIONS TABLE (May already exist)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `customer_notifications` (
  `NotificationID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) NOT NULL COMMENT 'Customer ID who receives the notification',
  `Icon` varchar(50) NOT NULL DEFAULT 'fa-info-circle' COMMENT 'Font Awesome icon class',
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
  KEY `idx_related` (`RelatedID`,`RelatedType`),
  KEY `idx_created_by` (`CreatedBy`),
  CONSTRAINT `fk_customer_notifications_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_customer_notifications_creator` FOREIGN KEY (`CreatedBy`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 3. CUSTOMIZATION_FIELD_CONFIGS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `customization_field_configs` (
  `ConfigID` int(11) NOT NULL AUTO_INCREMENT,
  `Category` varchar(100) NOT NULL,
  `Subcategory` varchar(100) NOT NULL,
  `FieldKey` varchar(200) NOT NULL COMMENT 'Unique key: Category_Subcategory',
  `FieldConfig` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON array of field definitions',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ConfigID`),
  UNIQUE KEY `unique_field_key` (`FieldKey`),
  KEY `idx_category_subcategory` (`Category`,`Subcategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 4. EMPLOYEE_ARCHIVE TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `employee_archive` (
  `ArchiveID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL COMMENT 'Original UserID from user table',
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `PhoneNum` varchar(13) NOT NULL,
  `ImageUrl` varchar(255) DEFAULT NULL,
  `Role` enum('Admin','Sales Representative','Inventory Officer','Customer') NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `Date_Created` timestamp NULL DEFAULT NULL COMMENT 'Original creation date',
  `Date_Updated` timestamp NULL DEFAULT NULL COMMENT 'Last update before archiving',
  `Last_Active` timestamp NULL DEFAULT NULL,
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When this employee was archived',
  PRIMARY KEY (`ArchiveID`),
  KEY `UserID` (`UserID`),
  KEY `Email` (`Email`),
  KEY `ArchivedAt` (`ArchivedAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 5. ENDUSER_ARCHIVE TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `enduser_archive` (
  `ArchiveID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL COMMENT 'Original UserID from user table',
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `PhoneNum` varchar(13) NOT NULL,
  `ImageUrl` varchar(255) DEFAULT NULL,
  `Role` enum('Admin','Sales Representative','Inventory Officer','Customer') NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `Date_Created` timestamp NULL DEFAULT NULL COMMENT 'Original creation date',
  `Date_Updated` timestamp NULL DEFAULT NULL COMMENT 'Last update before archiving',
  `Last_Active` timestamp NULL DEFAULT NULL,
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When this user was archived',
  PRIMARY KEY (`ArchiveID`),
  KEY `UserID` (`UserID`),
  KEY `Email` (`Email`),
  KEY `ArchivedAt` (`ArchivedAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 6. ORDER_ITEMS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `OrderItemID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `CustomizationID` int(11) DEFAULT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `UnitPrice` decimal(10,2) NOT NULL,
  `EstimatePrice` decimal(10,2) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL,
  `GlassShape` varchar(50) DEFAULT NULL,
  `GlassType` varchar(50) DEFAULT NULL,
  `GlassThickness` varchar(50) DEFAULT NULL,
  `EdgeWork` varchar(50) DEFAULT NULL,
  `FrameType` varchar(50) DEFAULT NULL,
  `Engraving` varchar(255) DEFAULT NULL,
  `DesignRef` varchar(255) DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`OrderItemID`),
  KEY `idx_order` (`OrderID`),
  KEY `idx_product` (`Product_ID`),
  KEY `idx_customization` (`CustomizationID`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_customization` FOREIGN KEY (`CustomizationID`) REFERENCES `customization` (`CustomizationID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 7. PRODUCT_SERIES TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `product_series` (
  `Series_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_ID` int(11) NOT NULL,
  `SeriesName` varchar(255) NOT NULL COMMENT 'Name of the series (e.g., "Standard Series", "Premium Series")',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Width` decimal(10,2) DEFAULT NULL,
  `Height` decimal(10,2) DEFAULT NULL,
  `WidthUnit` varchar(10) DEFAULT 'in',
  `HeightUnit` varchar(10) DEFAULT 'in',
  `OtherOptions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`OtherOptions`)),
  PRIMARY KEY (`Series_ID`),
  KEY `idx_product_id` (`Product_ID`),
  CONSTRAINT `fk_product_series_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 8. PRODUCT_STANDARD_SIZES TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `product_standard_sizes` (
  `SizeID` int(11) NOT NULL AUTO_INCREMENT,
  `Series_ID` int(11) NOT NULL,
  `Width` decimal(10,2) NOT NULL COMMENT 'Width in cm',
  `Height` decimal(10,2) NOT NULL COMMENT 'Height in cm',
  `Price` decimal(10,2) NOT NULL COMMENT 'Price for this specific size',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Product_ID` int(11) NOT NULL,
  `SizeName` varchar(100) NOT NULL,
  `WidthUnit` varchar(10) DEFAULT 'in',
  `HeightUnit` varchar(10) DEFAULT 'in',
  `Shape` varchar(50) DEFAULT NULL,
  `OtherOptions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`OtherOptions`)),
  PRIMARY KEY (`SizeID`),
  KEY `idx_series_id` (`Series_ID`),
  CONSTRAINT `fk_product_standard_sizes_series` FOREIGN KEY (`Series_ID`) REFERENCES `product_series` (`Series_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 9. PRODUCT_TAG_PRICES TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `product_tag_prices` (
  `TagPriceID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_ID` int(11) NOT NULL,
  `FieldID` varchar(100) NOT NULL COMMENT 'Field identifier (e.g., glassType, frameColor)',
  `TagName` varchar(255) NOT NULL COMMENT 'Tag/option name',
  `Price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price for this tag/option',
  `ImageUrl` varchar(255) DEFAULT NULL COMMENT 'Optional image URL for the tag',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `TagKey` varchar(100) NOT NULL,
  `TagValue` varchar(255) NOT NULL,
  `VisualConfig` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`VisualConfig`)),
  `Updated_Date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`TagPriceID`),
  KEY `idx_product_id` (`Product_ID`),
  KEY `idx_field_id` (`FieldID`),
  KEY `idx_image_url` (`ImageUrl`),
  CONSTRAINT `fk_product_tag_prices_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 10. RETURN_ORDER TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `return_order` (
  `ReturnID` int(11) NOT NULL AUTO_INCREMENT,
  `ReturnNumber` varchar(50) NOT NULL COMMENT 'Formatted: RT001, RT002, etc.',
  `OrderID` int(11) NOT NULL COMMENT 'Reference to original order',
  `Customer_ID` int(11) NOT NULL,
  `ReturnDate` date NOT NULL,
  `ReturnType` enum('Defect','Wrong Item','Customer Request','Other') DEFAULT 'Other',
  `ReturnReason` varchar(255) DEFAULT NULL,
  `ReturnDescription` text DEFAULT NULL,
  `ReturnStatus` enum('Pending','Approved','Rejected','Processing','Completed') DEFAULT 'Pending',
  `Product_ID` int(11) DEFAULT NULL COMMENT 'Product being returned',
  `QuantityReturned` int(11) DEFAULT 1,
  `ReturnPhotos` text DEFAULT NULL COMMENT 'JSON array of returned item photo paths',
  `ReplacementRequired` tinyint(1) DEFAULT 0,
  `ReplacementOrderID` int(11) DEFAULT NULL COMMENT 'Link to replacement order if created',
  `RefundAmount` decimal(12,2) DEFAULT 0.00,
  `RefundMethod` enum('Original Payment','Store Credit','Other') DEFAULT NULL,
  `RefundStatus` enum('Pending','Processed','Failed') DEFAULT 'Pending',
  `RefundDate` date DEFAULT NULL,
  `AdminNotes` text DEFAULT NULL,
  `RejectionReason` text DEFAULT NULL,
  `ProcessedBy_ID` int(11) DEFAULT NULL COMMENT 'Admin who processed the return',
  `ProcessedDate` datetime DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ReturnID`),
  UNIQUE KEY `ReturnNumber` (`ReturnNumber`),
  KEY `idx_order` (`OrderID`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_status` (`ReturnStatus`),
  CONSTRAINT `fk_return_order_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_return_order_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_return_order_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE SET NULL,
  CONSTRAINT `fk_return_order_processed_by` FOREIGN KEY (`ProcessedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 11. STATUS_HISTORY TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `status_history` (
  `StatusHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) DEFAULT NULL COMMENT 'Reference to order (if order status change)',
  `AppointmentID` int(11) DEFAULT NULL COMMENT 'Reference to appointment (if appointment status change)',
  `ReturnID` int(11) DEFAULT NULL COMMENT 'Reference to return order (if return status change)',
  `QuotationID` int(11) DEFAULT NULL COMMENT 'Reference to quotation (if quotation status change)',
  `EntityType` enum('Order','Appointment','Return','Quotation') NOT NULL,
  `OldStatus` varchar(100) DEFAULT NULL,
  `NewStatus` varchar(100) NOT NULL,
  `ChangedBy_ID` int(11) DEFAULT NULL COMMENT 'User who made the change',
  `ChangedBy_Role` varchar(50) DEFAULT NULL,
  `ChangeReason` text DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`StatusHistoryID`),
  KEY `idx_order` (`OrderID`),
  KEY `idx_appointment` (`AppointmentID`),
  KEY `idx_return` (`ReturnID`),
  KEY `idx_quotation` (`QuotationID`),
  KEY `idx_entity_type` (`EntityType`),
  KEY `idx_created_date` (`Created_Date`),
  CONSTRAINT `fk_status_history_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_status_history_appointment` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`) ON DELETE CASCADE,
  CONSTRAINT `fk_status_history_return` FOREIGN KEY (`ReturnID`) REFERENCES `return_order` (`ReturnID`) ON DELETE CASCADE,
  CONSTRAINT `fk_status_history_quotation` FOREIGN KEY (`QuotationID`) REFERENCES `quotation` (`QuotationID`) ON DELETE CASCADE,
  CONSTRAINT `fk_status_history_changed_by` FOREIGN KEY (`ChangedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;

-- ============================================================================
-- VERIFICATION
-- ============================================================================
-- Run these queries to verify all tables were created:
-- 
-- SELECT COUNT(*) as table_count FROM information_schema.tables 
-- WHERE table_schema = DATABASE() 
-- AND table_name IN (
--   'customer_customizations',
--   'customer_notifications',
--   'customization_field_configs',
--   'employee_archive',
--   'enduser_archive',
--   'order_items',
--   'product_series',
--   'product_standard_sizes',
--   'product_tag_prices',
--   'return_order',
--   'status_history'
-- );
-- 
-- Expected result: 11 tables
-- ============================================================================
