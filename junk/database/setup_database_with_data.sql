-- ============================================================================
-- GLASSIFY DATABASE SETUP WITH SAMPLE DATA
-- ============================================================================
-- Complete database setup script for Glassify E-Commerce System
-- This script creates the database schema and populates it with sample data
-- 
-- Database: latest_glassifydb (or glassify_db)
-- Version: 1.0
-- Created: 2026-01-20
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================================
-- DATABASE CREATION
-- ============================================================================
CREATE DATABASE IF NOT EXISTS `latest_glassifydb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `latest_glassifydb`;

-- ============================================================================
-- 1. USER MANAGEMENT TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: user
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL COMMENT 'Password reset token',
  `reset_token_expiry` datetime DEFAULT NULL COMMENT 'Token expiration date',
  `PhoneNum` varchar(13) NOT NULL,
  `ImageUrl` varchar(255) DEFAULT NULL COMMENT 'Profile picture path',
  `Role` enum('Admin','Sales Representative','Inventory Officer','Customer') NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `Date_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Last_Active` timestamp NULL DEFAULT NULL COMMENT 'Last active timestamp',
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Email` (`Email`),
  KEY `idx_reset_token` (`reset_token`),
  KEY `idx_role` (`Role`),
  KEY `idx_status` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: customer
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customer` (
  `Customer_ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL COMMENT 'Reference to user.UserID',
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Customer_ID`),
  UNIQUE KEY `UserID` (`UserID`),
  CONSTRAINT `fk_customer_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: user_address
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_address` (
  `AddressID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `AddressType` enum('Shipping','Billing') NOT NULL DEFAULT 'Shipping',
  `AddressLine` varchar(255) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Province` varchar(100) DEFAULT NULL,
  `Country` varchar(100) DEFAULT 'Philippines',
  `ZipCode` varchar(20) DEFAULT NULL,
  `Note` text DEFAULT NULL,
  `IsDefault` tinyint(1) DEFAULT 0,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`AddressID`),
  KEY `idx_userid` (`UserID`),
  KEY `idx_addresstype` (`AddressType`),
  CONSTRAINT `fk_user_address_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 2. PRODUCT MANAGEMENT TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: product
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product` (
  `Product_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProductName` varchar(100) NOT NULL,
  `Category` varchar(50) NOT NULL COMMENT 'e.g., Windows, Shower Enclosure, etc.',
  `Subcategory` varchar(100) DEFAULT NULL,
  `Material` enum('Glass','Aluminum') NOT NULL,
  `Price` decimal(10,2) NOT NULL COMMENT 'Base price per unit',
  `PriceMin` decimal(10,2) DEFAULT NULL,
  `PriceMax` decimal(10,2) DEFAULT NULL,
  `ImageUrl` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status` enum('In Stock','Out of Stock','Low Stock') DEFAULT 'Out of Stock',
  `OrderType` enum('direct','site-assessment') DEFAULT 'direct',
  `Customization` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: Customer customization selections',
  PRIMARY KEY (`Product_ID`),
  KEY `idx_category` (`Category`),
  KEY `idx_status` (`Status`),
  KEY `idx_material` (`Material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: customization
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customization` (
  `CustomizationID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL COMMENT 'e.g., 45 x 35',
  `GlassShape` varchar(50) DEFAULT NULL COMMENT 'rectangle, circle, etc.',
  `GlassType` varchar(50) DEFAULT NULL COMMENT 'tempered, laminated, etc.',
  `GlassThickness` varchar(50) DEFAULT NULL COMMENT '5mm, 8mm, etc.',
  `EdgeWork` varchar(50) DEFAULT NULL COMMENT 'flat-polish, beveled, etc.',
  `FrameType` varchar(50) DEFAULT NULL COMMENT 'vinyl, aluminum, etc.',
  `Engraving` varchar(255) DEFAULT NULL,
  `DesignRef` varchar(255) DEFAULT NULL COMMENT 'File path to design image',
  `LEDBacklight` varchar(50) DEFAULT NULL COMMENT 'For mirrors',
  `DoorOperation` varchar(50) DEFAULT NULL COMMENT 'For shower enclosures',
  `Configuration` varchar(50) DEFAULT NULL COMMENT 'For aluminum doors',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `PriceBreakdown` text DEFAULT NULL COMMENT 'JSON string containing price breakdown details',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`CustomizationID`),
  KEY `idx_customer_id` (`Customer_ID`),
  KEY `idx_product_id` (`Product_ID`),
  KEY `idx_created_at` (`CreatedAt`),
  CONSTRAINT `fk_customization_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_customization_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: customization_field_configs
-- ----------------------------------------------------------------------------
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
  KEY `idx_category_subcategory` (`Category`, `Subcategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: product_tag_prices
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_tag_prices` (
  `TagPriceID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_ID` int(11) NOT NULL,
  `FieldID` varchar(100) NOT NULL COMMENT 'Field identifier (e.g., glassType, frameColor)',
  `TagName` varchar(255) NOT NULL COMMENT 'Tag/option name',
  `Price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price for this tag/option',
  `ImageUrl` varchar(255) DEFAULT NULL,
  `VisualConfig` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON visual configuration',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`TagPriceID`),
  KEY `idx_product_id` (`Product_ID`),
  KEY `idx_field_id` (`FieldID`),
  CONSTRAINT `fk_tag_prices_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 3. INVENTORY MANAGEMENT TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: inventory_items
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inventory_items` (
  `InventoryItemID` int(11) NOT NULL AUTO_INCREMENT,
  `ItemID` varchar(50) NOT NULL COMMENT 'e.g., GL-001, AL-022',
  `Name` varchar(255) NOT NULL,
  `Category` varchar(100) NOT NULL,
  `InStock` int(11) NOT NULL DEFAULT 0,
  `min_threshold` int(11) DEFAULT 10 COMMENT 'Minimum stock threshold',
  `Unit` varchar(50) NOT NULL COMMENT 'sqm, pcs, tubes, meter, sets, etc.',
  `Status` enum('In Stock','Low Stock','Out of Stock','New') DEFAULT 'In Stock',
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `DateUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`InventoryItemID`),
  UNIQUE KEY `ItemID` (`ItemID`),
  KEY `idx_category` (`Category`),
  KEY `idx_status` (`Status`),
  KEY `idx_instock` (`InStock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: product_materials
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_materials` (
  `ProductMaterialID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_ID` int(11) NOT NULL,
  `InventoryItemID` int(11) NOT NULL,
  `QuantityRequired` decimal(10,2) NOT NULL COMMENT 'Amount of material needed per product unit',
  `Unit` varchar(50) DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ProductMaterialID`),
  KEY `idx_product` (`Product_ID`),
  KEY `idx_inventory` (`InventoryItemID`),
  CONSTRAINT `fk_product_materials_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_materials_inventory` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: stock_transactions
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stock_transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `InventoryItemID` int(11) NOT NULL,
  `transaction_type` enum('add','remove','adjust') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `previous_stock` int(11) DEFAULT NULL,
  `new_stock` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `idx_inventory_item` (`InventoryItemID`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_transaction_type` (`transaction_type`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_stock_transactions_inventory` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE,
  CONSTRAINT `fk_stock_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: inventory_notifications
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inventory_notifications` (
  `NotificationID` int(11) NOT NULL AUTO_INCREMENT,
  `InventoryItemID` int(11) NOT NULL,
  `ItemID` varchar(50) NOT NULL,
  `ItemName` varchar(255) NOT NULL,
  `Message` text NOT NULL,
  `Status` enum('Unread','Read','Resolved') DEFAULT 'Unread',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`NotificationID`),
  KEY `idx_inventory_item` (`InventoryItemID`),
  KEY `idx_status` (`Status`),
  CONSTRAINT `fk_inventory_notifications_item` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: activities
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activities` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(100) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `change_description` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `InventoryItemID` int(11) DEFAULT NULL,
  PRIMARY KEY (`activity_id`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_action` (`action`),
  KEY `idx_inventory_item` (`InventoryItemID`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_activities_inventory` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE SET NULL,
  CONSTRAINT `fk_activities_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 4. SHOPPING CART & WISHLIST TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: cart
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cart` (
  `Cart_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `CustomizationID` int(11) DEFAULT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `Added_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Cart_ID`),
  UNIQUE KEY `unique_cart_item` (`Customer_ID`,`Product_ID`,`CustomizationID`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_product` (`Product_ID`),
  KEY `idx_customization` (`CustomizationID`),
  CONSTRAINT `fk_cart_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_customization` FOREIGN KEY (`CustomizationID`) REFERENCES `customization` (`CustomizationID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: wishlist
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wishlist` (
  `Wishlist_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `CustomizationID` int(11) DEFAULT NULL,
  `DateAdded` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`Wishlist_ID`),
  UNIQUE KEY `unique_wishlist_item` (`Customer_ID`,`Product_ID`,`CustomizationID`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_product` (`Product_ID`),
  KEY `idx_customization` (`CustomizationID`),
  CONSTRAINT `fk_wishlist_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_customization` FOREIGN KEY (`CustomizationID`) REFERENCES `customization` (`CustomizationID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 5. ORDER MANAGEMENT TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: order
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order` (
  `OrderID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderNumber` varchar(50) NOT NULL COMMENT 'Formatted: GI001, GI002, etc.',
  `Customer_ID` int(11) NOT NULL,
  `SalesRep_ID` int(11) NOT NULL,
  `OrderDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `TotalAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `Status` enum('Pending Review','Awaiting Admin','Ready to Approve','Approved','Disapproved','In Fabrication','Ready for Installation','Completed','Cancelled','Returned') DEFAULT 'Pending Review',
  `PaymentStatus` enum('Pending','Paid','Partial','Refunded') DEFAULT 'Pending',
  `PaymentMethod` enum('E-Wallet','Cash on Delivery') DEFAULT NULL,
  `DeliveryAddress` varchar(255) DEFAULT NULL,
  `SpecialInstructions` text DEFAULT NULL,
  `QuotationPDFUrl` varchar(255) DEFAULT NULL,
  `ContractPDFUrl` varchar(255) DEFAULT NULL,
  
  -- Approval Fields
  `ApprovedBy_SalesRep_ID` int(11) DEFAULT NULL,
  `ApprovedBy_Admin_ID` int(11) DEFAULT NULL,
  `Approved_Date` datetime DEFAULT NULL,
  `DisapprovedBy` enum('Sales Rep','Admin') DEFAULT NULL,
  `DisapprovedBy_ID` int(11) DEFAULT NULL,
  `DisapprovalReason` text DEFAULT NULL,
  `Disapproved_Date` datetime DEFAULT NULL,
  `CustomerNotified` tinyint(1) DEFAULT 0,
  `CustomerNotified_Date` datetime DEFAULT NULL,
  
  -- Order Type and Ocular Fields
  `OrderType` enum('Direct','Site-Assessed') DEFAULT 'Direct' COMMENT 'Order type: Direct or Site-Assessed',
  `OcularCompleted` tinyint(1) DEFAULT 0 COMMENT 'Flag indicating if ocular/site assessment is completed',
  `OcularNotes` text DEFAULT NULL COMMENT 'Site assessment notes and measurements',
  `OcularDate` date DEFAULT NULL COMMENT 'Scheduled date for ocular visit',
  `OcularCompletedBy_ID` int(11) DEFAULT NULL COMMENT 'Staff who completed ocular assessment',
  
  -- Date Fields
  `PreferredInstallationDate` date DEFAULT NULL COMMENT 'Customer preferred installation date',
  `FabricationDate` date DEFAULT NULL COMMENT 'Scheduled date for fabrication',
  `InstallationDate` date DEFAULT NULL COMMENT 'Scheduled date for installation',
  `EstimatedDelivery` date DEFAULT NULL COMMENT 'Estimated delivery/completion date',
  
  -- Staff Assignment Fields
  `FabricationStaff_ID` int(11) DEFAULT NULL COMMENT 'Assigned fabrication staff member',
  `InstallationStaff_ID` int(11) DEFAULT NULL COMMENT 'Assigned installation staff member',
  
  -- Fabrication/Production Fields
  `FabricationStartDate` date DEFAULT NULL COMMENT 'Expected start date of fabrication',
  `FabricationEndDate` date DEFAULT NULL COMMENT 'Expected end date of fabrication',
  `ActualFabricationStartDate` date DEFAULT NULL COMMENT 'Actual start date when fabrication began',
  `ActualFabricationEndDate` date DEFAULT NULL COMMENT 'Actual end date when fabrication completed',
  `FabricationProgress` int(11) DEFAULT 0 COMMENT 'Fabrication progress percentage (0-100)',
  `FabricationStatus` enum('Queued','In Progress','Quality Check','Ready','Completed') DEFAULT 'Queued' COMMENT 'Fabrication queue status',
  `FabricationNotes` text DEFAULT NULL COMMENT 'Production and fabrication notes',
  `QualityCheckNotes` text DEFAULT NULL COMMENT 'Quality check notes',
  
  -- Notes Fields
  `AdminNotes` text DEFAULT NULL COMMENT 'Internal admin notes',
  `CustomerNotes` text DEFAULT NULL COMMENT 'Customer-facing notes',
  `StaffNotes` text DEFAULT NULL COMMENT 'Staff-specific notes',
  
  -- Barcode/QR Code
  `Barcode` varchar(100) DEFAULT NULL COMMENT 'Order barcode/QR code',
  `BarcodeImagePath` varchar(255) DEFAULT NULL COMMENT 'Path to barcode image',
  
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  
  PRIMARY KEY (`OrderID`),
  UNIQUE KEY `OrderNumber` (`OrderNumber`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_salesrep` (`SalesRep_ID`),
  KEY `idx_status` (`Status`),
  KEY `idx_payment_status` (`PaymentStatus`),
  KEY `idx_order_date` (`OrderDate`),
  KEY `idx_order_type` (`OrderType`),
  KEY `idx_ocular_completed` (`OcularCompleted`),
  KEY `idx_fabrication_staff` (`FabricationStaff_ID`),
  KEY `idx_installation_staff` (`InstallationStaff_ID`),
  KEY `idx_fabrication_status` (`FabricationStatus`),
  KEY `idx_fabrication_dates` (`FabricationStartDate`,`FabricationEndDate`),
  KEY `fk_order_approved_salesrep` (`ApprovedBy_SalesRep_ID`),
  KEY `fk_order_approved_admin` (`ApprovedBy_Admin_ID`),
  KEY `fk_order_disapproved_by` (`DisapprovedBy_ID`),
  KEY `fk_order_ocular_completed_by` (`OcularCompletedBy_ID`),
  
  CONSTRAINT `fk_order_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`),
  CONSTRAINT `fk_order_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`),
  CONSTRAINT `fk_order_approved_salesrep` FOREIGN KEY (`ApprovedBy_SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  CONSTRAINT `fk_order_approved_admin` FOREIGN KEY (`ApprovedBy_Admin_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  CONSTRAINT `fk_order_disapproved_by` FOREIGN KEY (`DisapprovedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  CONSTRAINT `fk_order_fabrication_staff` FOREIGN KEY (`FabricationStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  CONSTRAINT `fk_order_installation_staff` FOREIGN KEY (`InstallationStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  CONSTRAINT `fk_order_ocular_completed_by` FOREIGN KEY (`OcularCompletedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: order_items
-- ----------------------------------------------------------------------------
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
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`),
  CONSTRAINT `fk_order_items_customization` FOREIGN KEY (`CustomizationID`) REFERENCES `customization` (`CustomizationID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: pending_review_orders
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pending_review_orders` (
  `PendingOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL COMMENT 'References order.OrderID',
  `OrderNumber` varchar(50) DEFAULT NULL COMMENT 'References order.OrderNumber',
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `TotalQuotation` decimal(12,2) DEFAULT 0.00,
  `Notes` text DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`PendingOrderID`),
  KEY `idx_orderid` (`OrderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: awaiting_admin_orders
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `awaiting_admin_orders` (
  `AwaitingOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL COMMENT 'References order.OrderID',
  `OrderNumber` varchar(50) DEFAULT NULL COMMENT 'References order.OrderNumber',
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `TotalQuotation` decimal(12,2) DEFAULT 0.00,
  `SalesRepNotes` text DEFAULT NULL COMMENT 'Notes from sales rep when requesting approval',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`AwaitingOrderID`),
  KEY `idx_orderid` (`OrderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: ready_to_approve_orders
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ready_to_approve_orders` (
  `ReadyOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` varchar(50) DEFAULT NULL COMMENT 'References order.OrderNumber',
  `ProductName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `Shape` varchar(50) DEFAULT NULL,
  `Dimension` varchar(100) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Thickness` varchar(50) DEFAULT NULL,
  `EdgeWork` varchar(50) DEFAULT NULL,
  `FrameType` varchar(50) DEFAULT NULL,
  `Engraving` varchar(255) DEFAULT NULL,
  `FileAttached` varchar(255) DEFAULT NULL,
  `TotalQuotation` decimal(12,2) DEFAULT 0.00,
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `AdminStatus` enum('Approved','Disapproved') DEFAULT NULL,
  `AdminNotes` text DEFAULT NULL,
  `AdminReviewed_Date` datetime DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`ReadyOrderID`),
  KEY `idx_orderid` (`OrderID`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_salesrep` (`SalesRep_ID`),
  KEY `idx_admin_status` (`AdminStatus`),
  CONSTRAINT `fk_ready_orders_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL,
  CONSTRAINT `fk_ready_orders_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: approved_orders
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `approved_orders` (
  `ApprovedOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL COMMENT 'References order.OrderID',
  `OrderNumber` varchar(50) DEFAULT NULL COMMENT 'References order.OrderNumber',
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `TotalQuotation` decimal(12,2) DEFAULT 0.00,
  `CustomerNotified` tinyint(1) DEFAULT 0,
  `CustomerNotified_Date` datetime DEFAULT NULL,
  `ApprovedBy_SalesRep_ID` int(11) DEFAULT NULL,
  `Approved_Date` datetime DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`ApprovedOrderID`),
  KEY `idx_orderid` (`OrderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: disapproved_orders
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `disapproved_orders` (
  `DisapprovedOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL COMMENT 'References order.OrderID',
  `OrderNumber` varchar(50) DEFAULT NULL COMMENT 'References order.OrderNumber',
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `TotalQuotation` decimal(12,2) DEFAULT 0.00,
  `DisapprovedBy` enum('Sales Rep','Admin') DEFAULT NULL,
  `DisapprovedBy_ID` int(11) DEFAULT NULL,
  `DisapprovalReason` text DEFAULT NULL,
  `Disapproved_Date` datetime DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`DisapprovedOrderID`),
  KEY `idx_orderid` (`OrderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 6. PAYMENT TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: payment
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payment` (
  `Payment_ID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `CustomerName` varchar(255) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `PaymentMethod` enum('E-Wallet','Cash on Delivery') DEFAULT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Payment_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Transaction_ID` varchar(100) DEFAULT NULL,
  `ReceiptPath` varchar(255) DEFAULT NULL,
  `Status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  PRIMARY KEY (`Payment_ID`),
  KEY `idx_order` (`OrderID`),
  KEY `idx_status` (`Status`),
  KEY `idx_payment_date` (`Payment_Date`),
  CONSTRAINT `fk_payment_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 7. APPOINTMENT TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: appointments
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `appointments` (
  `AppointmentID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `ClientName` varchar(255) DEFAULT NULL,
  `Service` enum('Order Placed','Ocular Visit','In Fabrication','Installed','Completed') DEFAULT 'Order Placed',
  `AppointmentDate` date DEFAULT NULL,
  `AppointmentTime` time DEFAULT NULL,
  `AssignedStaff` varchar(255) DEFAULT NULL COMMENT 'Deprecated - use AssignedStaff_ID',
  `AssignedStaff_ID` int(11) DEFAULT NULL COMMENT 'Assigned staff member ID',
  `Status` enum('In Progress','Complete','Cancelled') DEFAULT 'In Progress',
  `Notes` text DEFAULT NULL,
  `AppointmentType` enum('Ocular','Installation') DEFAULT NULL COMMENT 'Type of appointment: Ocular or Installation',
  
  -- Ocular-Specific Fields
  `OcularNotes` text DEFAULT NULL COMMENT 'Site assessment notes and measurements',
  `OcularReportPath` varchar(255) DEFAULT NULL COMMENT 'Path to full ocular report PDF',
  
  -- Installation-Specific Fields
  `InstallationNotes` text DEFAULT NULL COMMENT 'Installation-specific notes',
  `InstallationChecklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Installation checklist items (JSON format)',
  
  -- Site Photos
  `SitePhotos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of site photo paths (JSON format)',
  
  -- Additional Notes
  `InternalNotes` text DEFAULT NULL COMMENT 'Internal admin notes',
  `CustomerVisibleNotes` text DEFAULT NULL COMMENT 'Notes visible to customer',
  
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  
  PRIMARY KEY (`AppointmentID`),
  KEY `idx_order` (`OrderID`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_service` (`Service`),
  KEY `idx_status` (`Status`),
  KEY `idx_date` (`AppointmentDate`),
  KEY `idx_staff` (`AssignedStaff_ID`),
  KEY `idx_appointment_type` (`AppointmentType`),
  KEY `idx_assigned_staff_id` (`AssignedStaff_ID`),
  
  CONSTRAINT `fk_appointments_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_appointments_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_appointments_assigned_staff` FOREIGN KEY (`AssignedStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 8. QUOTATION TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: quotation
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `quotation` (
  `QuotationID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `Quotation_num` varchar(20) NOT NULL,
  `Total_amount` decimal(10,2) DEFAULT NULL,
  `Tax_amount` decimal(10,2) DEFAULT NULL,
  `Terms_conditions` varchar(255) DEFAULT NULL,
  `Pdf_url` varchar(255) DEFAULT NULL,
  `Created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`QuotationID`),
  UNIQUE KEY `Quotation_num` (`Quotation_num`),
  KEY `idx_order` (`OrderID`),
  CONSTRAINT `fk_quotation_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 9. RETURN ORDER TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: return_order
-- ----------------------------------------------------------------------------
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
  KEY `idx_orderid` (`OrderID`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_return_status` (`ReturnStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 10. PROJECT SCHEDULE TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: projectschedule
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projectschedule` (
  `Schedule_ID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `Admin_ID` int(11) NOT NULL,
  `Project_Name` varchar(100) NOT NULL,
  `Start_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Status` enum('Scheduled','In progress','Completed','Delayed') DEFAULT 'Scheduled',
  PRIMARY KEY (`Schedule_ID`),
  KEY `idx_order` (`OrderID`),
  KEY `idx_admin` (`Admin_ID`),
  CONSTRAINT `fk_projectschedule_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_projectschedule_admin` FOREIGN KEY (`Admin_ID`) REFERENCES `user` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 11. ISSUE REPORTING TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: issuereport
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `issuereport` (
  `Issue_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) DEFAULT NULL,
  `Order_ID` int(11) DEFAULT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `PhoneNum` varchar(13) NOT NULL,
  `Category` enum('Order Issue','Payment Issue','Delivery Issue','General Inquiry','Installation Problems','Product Defect/Damage','Measurement/Design Problems','Billing/Payment Questions','Other') DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Report_Date` datetime DEFAULT current_timestamp(),
  `Status` enum('Open','Resolved') DEFAULT 'Open',
  `Priority` enum('Low','Medium','High') DEFAULT 'Low',
  PRIMARY KEY (`Issue_ID`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_order` (`Order_ID`),
  KEY `idx_status` (`Status`),
  KEY `idx_priority` (`Priority`),
  CONSTRAINT `fk_issuereport_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_issuereport_order` FOREIGN KEY (`Order_ID`) REFERENCES `order` (`OrderID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 12. NOTIFICATION TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: sales_notif
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sales_notif` (
  `NotificationID` int(11) NOT NULL AUTO_INCREMENT,
  `Icon` varchar(50) NOT NULL,
  `Role` varchar(50) NOT NULL,
  `Description` text NOT NULL,
  `Status` enum('Unread','Read') DEFAULT 'Unread',
  `Created_Date` datetime NOT NULL DEFAULT current_timestamp(),
  `Read_Date` datetime DEFAULT NULL,
  `RelatedID` int(11) DEFAULT NULL,
  `RelatedType` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`NotificationID`),
  KEY `idx_status` (`Status`),
  KEY `idx_role` (`Role`),
  KEY `idx_created_date` (`Created_Date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 13. SYSTEM LOGGING TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: system_activity_log
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_activity_log` (
  `ActivityID` int(11) NOT NULL AUTO_INCREMENT,
  `Action` varchar(50) NOT NULL,
  `Description` text NOT NULL,
  `Role` varchar(50) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `UserName` varchar(100) DEFAULT NULL,
  `Timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `RelatedID` int(11) DEFAULT NULL,
  `RelatedType` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ActivityID`),
  KEY `idx_timestamp` (`Timestamp`),
  KEY `idx_action` (`Action`),
  KEY `idx_user` (`UserID`),
  CONSTRAINT `fk_activity_log_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Table: status_history
-- ----------------------------------------------------------------------------
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
  KEY `idx_created_date` (`Created_Date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- SAMPLE DATA INSERTION
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Insert Users
-- ----------------------------------------------------------------------------
-- Password for all users: password123 (hashed with bcrypt)
-- You can change passwords after setup
INSERT INTO `user` (`UserID`, `First_Name`, `Last_Name`, `Middle_Name`, `Email`, `Password`, `PhoneNum`, `Role`, `Status`, `Date_Created`) VALUES
-- Admin Users
(1, 'Admin', 'User', NULL, 'admin@glassify.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456789', 'Admin', 'Active', NOW()),
(2, 'Marianne', 'Placides', 'Lizanne', 'marianne@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456790', 'Admin', 'Active', NOW()),

-- Sales Representatives
(3, 'Irish', 'Vasquez', 'Queen', 'queen@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456791', 'Sales Representative', 'Active', NOW()),
(4, 'Farrah', 'Jimenez', NULL, 'monah@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456792', 'Sales Representative', 'Active', NOW()),

-- Inventory Officer
(5, 'Kennedy', 'Gomez', NULL, 'kenken@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456793', 'Inventory Officer', 'Active', NOW()),

-- Customers
(6, 'John', 'Doe', NULL, 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456794', 'Customer', 'Active', NOW()),
(7, 'Jane', 'Smith', NULL, 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456795', 'Customer', 'Active', NOW()),
(8, 'Michael', 'Johnson', NULL, 'michael.j@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456796', 'Customer', 'Active', NOW());

-- ----------------------------------------------------------------------------
-- Insert Customers
-- ----------------------------------------------------------------------------
INSERT INTO `customer` (`Customer_ID`, `UserID`, `Date_Created`) VALUES
(1, 6, NOW()),
(2, 7, NOW()),
(3, 8, NOW());

-- ----------------------------------------------------------------------------
-- Insert User Addresses
-- ----------------------------------------------------------------------------
INSERT INTO `user_address` (`AddressID`, `UserID`, `AddressType`, `AddressLine`, `City`, `Province`, `Country`, `ZipCode`, `IsDefault`) VALUES
(1, 6, 'Shipping', '123 Main Street', 'Manila', 'Metro Manila', 'Philippines', '1000', 1),
(2, 6, 'Billing', '123 Main Street', 'Manila', 'Metro Manila', 'Philippines', '1000', 1),
(3, 7, 'Shipping', '456 Oak Avenue', 'Quezon City', 'Metro Manila', 'Philippines', '1100', 1),
(4, 8, 'Shipping', '789 Pine Road', 'Makati', 'Metro Manila', 'Philippines', '1200', 1);

-- ----------------------------------------------------------------------------
-- Insert Products
-- ----------------------------------------------------------------------------
INSERT INTO `product` (`Product_ID`, `ProductName`, `Category`, `Subcategory`, `Material`, `Price`, `PriceMin`, `PriceMax`, `ImageUrl`, `Description`, `Status`, `OrderType`) VALUES
-- Direct Order Products - Windows
(1, '900 Series Sliding Window', 'Windows', 'Sliding', 'Aluminum', 15000.00, 12000.00, 20000.00, NULL, 'Two-panel horizontal sliding window for direct orders', 'In Stock', 'direct'),
(2, '798 Series Sliding Window', 'Windows', 'Sliding', 'Aluminum', 14000.00, 11000.00, 18000.00, NULL, 'Two-panel horizontal sliding window with sleek design', 'In Stock', 'direct'),
(3, '38 Series Awning Window', 'Windows', 'Awning', 'Aluminum', 12000.00, 10000.00, 15000.00, NULL, 'Top-hinged window that opens outwards from bottom', 'In Stock', 'direct'),
(4, '38 Series Casement', 'Windows', 'Casement', 'Aluminum', 13000.00, 11000.00, 16000.00, NULL, 'Single-panel vertical window that opens outwards from side', 'In Stock', 'direct'),
(5, 'Fixed Glass Window', 'Windows', 'Fixed Glass', 'Glass', 10000.00, 8000.00, 13000.00, NULL, 'Fixed glass window panel', 'In Stock', 'direct'),

-- Direct Order Products - Mirrors
(6, 'Frameless Round Mirror', 'Mirrors & Specialty Glass', 'Mirrors', 'Glass', 5000.00, 4000.00, 7000.00, NULL, 'Classic round mirror without frame', 'In Stock', 'direct'),
(7, 'Gold Framed Round Mirror', 'Mirrors & Specialty Glass', 'Mirrors', 'Glass', 8000.00, 6000.00, 10000.00, NULL, 'Round mirror with gold frame', 'In Stock', 'direct'),
(8, 'Black Framed Round Mirror', 'Mirrors & Specialty Glass', 'Mirrors', 'Glass', 7500.00, 5500.00, 9500.00, NULL, 'Round mirror with black frame', 'In Stock', 'direct'),

-- Direct Order Products - Glass Partitions
(9, 'Frameless Glass Partition', 'Glass Partitions & Enclosures', 'Frameless Glass', 'Glass', 18000.00, 15000.00, 25000.00, NULL, 'Frameless glass partition for office spaces', 'In Stock', 'direct'),
(10, 'L-Shape Shower Enclosure', 'Glass Partitions & Enclosures', 'Shower Enclosure', 'Glass', 20000.00, 16000.00, 28000.00, NULL, 'L-shaped corner shower enclosure', 'In Stock', 'direct'),

-- Direct Order Products - Glass Doors
(11, 'Swing Glass Door', 'Glass Doors', 'Swing Door', 'Glass', 22000.00, 18000.00, 30000.00, NULL, 'Swing door with fixed side panel and transom above', 'In Stock', 'direct'),
(12, '4 Panel Sliding Door', 'Glass Doors', 'Sliding Door', 'Aluminum', 25000.00, 20000.00, 35000.00, NULL, 'Multi-panel sliding door system', 'In Stock', 'direct'),

-- Site Assessment Products
(13, 'Custom Glass Windows - Site Assessment', 'Windows', 'Custom', 'Glass', 0.00, NULL, NULL, NULL, 'Custom glass windows requiring site assessment and measurements', 'In Stock', 'site-assessment'),
(14, 'Custom Aluminum Windows - Site Assessment', 'Windows', 'Custom', 'Aluminum', 0.00, NULL, NULL, NULL, 'Custom aluminum windows requiring site assessment and measurements', 'In Stock', 'site-assessment'),
(15, 'Custom Mirror Installation - Site Assessment', 'Mirrors & Specialty Glass', 'Mirrors', 'Glass', 0.00, NULL, NULL, NULL, 'Custom mirror installation requiring site assessment', 'In Stock', 'site-assessment'),
(16, 'Custom Glass Partition - Site Assessment', 'Glass Partitions & Enclosures', 'Frameless Glass', 'Glass', 0.00, NULL, NULL, NULL, 'Custom glass partition requiring site assessment and measurements', 'In Stock', 'site-assessment'),
(17, 'Custom Shower Enclosure - Site Assessment', 'Glass Partitions & Enclosures', 'Shower Enclosure', 'Glass', 0.00, NULL, NULL, NULL, 'Custom shower enclosure requiring site assessment and measurements', 'In Stock', 'site-assessment'),
(18, 'Custom Glass Door - Site Assessment', 'Glass Doors', 'Custom', 'Glass', 0.00, NULL, NULL, NULL, 'Custom glass door requiring site assessment and measurements', 'In Stock', 'site-assessment');

-- ----------------------------------------------------------------------------
-- Insert Inventory Items
-- ----------------------------------------------------------------------------
INSERT INTO `inventory_items` (`InventoryItemID`, `ItemID`, `Name`, `Category`, `InStock`, `min_threshold`, `Unit`, `Status`) VALUES
(1, 'GL-001', 'Clear Glass 5mm', 'Glass', 500, 50, 'sqm', 'In Stock'),
(2, 'GL-002', 'Clear Glass 8mm', 'Glass', 300, 30, 'sqm', 'In Stock'),
(3, 'GL-003', 'Tempered Glass 5mm', 'Glass', 400, 40, 'sqm', 'In Stock'),
(4, 'GL-004', 'Laminated Glass 6mm', 'Glass', 250, 25, 'sqm', 'In Stock'),
(5, 'AL-001', 'Aluminum Frame White', 'Aluminum', 200, 20, 'meter', 'In Stock'),
(6, 'AL-002', 'Aluminum Frame Black', 'Aluminum', 180, 18, 'meter', 'In Stock'),
(7, 'AL-003', 'Aluminum Frame Silver', 'Aluminum', 150, 15, 'meter', 'In Stock'),
(8, 'AL-004', 'Aluminum Sliding Track', 'Aluminum', 100, 10, 'sets', 'In Stock'),
(9, 'AC-001', 'Glass Adhesive', 'Accessories', 500, 50, 'tubes', 'In Stock'),
(10, 'AC-002', 'Silicone Sealant', 'Accessories', 300, 30, 'tubes', 'In Stock'),
(11, 'AC-003', 'Mirror Mounting Clips', 'Accessories', 200, 20, 'pcs', 'In Stock'),
(12, 'AC-004', 'Door Hinges', 'Accessories', 150, 15, 'pcs', 'In Stock');

-- ----------------------------------------------------------------------------
-- Insert Product Materials (Sample relationships)
-- ----------------------------------------------------------------------------
INSERT INTO `product_materials` (`ProductMaterialID`, `Product_ID`, `InventoryItemID`, `QuantityRequired`, `Unit`) VALUES
(1, 1, 1, 2.5, 'sqm'),
(2, 1, 5, 4.0, 'meter'),
(3, 2, 1, 2.5, 'sqm'),
(4, 2, 5, 4.0, 'meter'),
(5, 6, 1, 1.0, 'sqm'),
(6, 6, 11, 4, 'pcs');

-- ----------------------------------------------------------------------------
-- Insert Stock Transactions (Sample)
-- ----------------------------------------------------------------------------
INSERT INTO `stock_transactions` (`transaction_id`, `InventoryItemID`, `transaction_type`, `quantity`, `reason`, `previous_stock`, `new_stock`, `user_id`) VALUES
(1, 1, 'add', 500, 'Initial stock', 0, 500, 5),
(2, 2, 'add', 300, 'Initial stock', 0, 300, 5),
(3, 3, 'add', 400, 'Initial stock', 0, 400, 5);

-- ----------------------------------------------------------------------------
-- Insert Orders (Sample)
-- ----------------------------------------------------------------------------
INSERT INTO `order` (`OrderID`, `OrderNumber`, `Customer_ID`, `SalesRep_ID`, `OrderDate`, `TotalAmount`, `Status`, `PaymentStatus`, `PaymentMethod`, `DeliveryAddress`, `OrderType`) VALUES
(1, 'GI001', 1, 3, NOW(), 15000.00, 'Pending Review', 'Pending', NULL, '123 Main Street, Manila, Metro Manila', 'Direct'),
(2, 'GI002', 2, 4, NOW(), 25000.00, 'Approved', 'Paid', 'E-Wallet', '456 Oak Avenue, Quezon City, Metro Manila', 'Direct'),
(3, 'GI003', 1, 3, NOW(), 0.00, 'Pending Review', 'Pending', NULL, '123 Main Street, Manila, Metro Manila', 'Site-Assessed');

-- ----------------------------------------------------------------------------
-- Insert Order Items
-- ----------------------------------------------------------------------------
INSERT INTO `order_items` (`OrderItemID`, `OrderID`, `Product_ID`, `Quantity`, `UnitPrice`, `EstimatePrice`, `Dimensions`, `GlassType`) VALUES
(1, 1, 1, 1, 15000.00, 15000.00, '120 x 150 cm', 'Tempered'),
(2, 2, 12, 1, 25000.00, 25000.00, '200 x 220 cm', 'Tempered'),
(3, 3, 13, 1, 0.00, 0.00, NULL, NULL);

-- ----------------------------------------------------------------------------
-- Insert Appointments (Sample)
-- ----------------------------------------------------------------------------
INSERT INTO `appointments` (`AppointmentID`, `OrderID`, `Customer_ID`, `ProductName`, `ClientName`, `Service`, `AppointmentDate`, `AppointmentTime`, `AssignedStaff_ID`, `Status`, `AppointmentType`, `Notes`) VALUES
(1, 3, 1, 'Custom Glass Windows - Site Assessment', 'John Doe', 'Ocular Visit', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', 3, 'In Progress', 'Ocular', 'Initial site assessment for custom windows'),
(2, 2, 2, '4 Panel Sliding Door', 'Jane Smith', 'Installed', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '14:00:00', 4, 'Complete', 'Installation', 'Installation completed successfully');

-- ----------------------------------------------------------------------------
-- Insert Quotations (Sample)
-- ----------------------------------------------------------------------------
INSERT INTO `quotation` (`QuotationID`, `OrderID`, `Quotation_num`, `Total_amount`, `Tax_amount`, `Terms_conditions`, `Created_date`) VALUES
(1, 1, 'QT001', 15000.00, 1800.00, 'Payment due within 30 days', NOW()),
(2, 2, 'QT002', 25000.00, 3000.00, 'Payment due within 30 days', NOW());

-- ----------------------------------------------------------------------------
-- Insert Payments (Sample)
-- ----------------------------------------------------------------------------
INSERT INTO `payment` (`Payment_ID`, `OrderID`, `CustomerName`, `ProductName`, `PaymentMethod`, `Amount`, `Transaction_ID`, `Status`) VALUES
(1, 2, 'Jane Smith', '4 Panel Sliding Door', 'E-Wallet', 25000.00, 'TXN123456789', 'Paid');

-- ----------------------------------------------------------------------------
-- Insert System Activity Log (Sample)
-- ----------------------------------------------------------------------------
INSERT INTO `system_activity_log` (`ActivityID`, `Action`, `Description`, `Role`, `UserID`, `UserName`, `RelatedID`, `RelatedType`) VALUES
(1, 'Order Created', 'New order GI001 created', 'Sales Representative', 3, 'Irish Vasquez', 1, 'Order'),
(2, 'Order Approved', 'Order GI002 approved by admin', 'Admin', 1, 'Admin User', 2, 'Order'),
(3, 'Payment Received', 'Payment received for order GI002', 'Customer', 7, 'Jane Smith', 2, 'Order');

-- ----------------------------------------------------------------------------
-- Insert Status History (Sample)
-- ----------------------------------------------------------------------------
INSERT INTO `status_history` (`StatusHistoryID`, `OrderID`, `EntityType`, `OldStatus`, `NewStatus`, `ChangedBy_ID`, `ChangedBy_Role`, `Created_Date`) VALUES
(1, 2, 'Order', 'Pending Review', 'Approved', 1, 'Admin', NOW()),
(2, 2, 'Order', 'Pending', 'Paid', 7, 'Customer', NOW());

-- ============================================================================
-- SET AUTO_INCREMENT VALUES
-- ============================================================================
ALTER TABLE `user` AUTO_INCREMENT = 9;
ALTER TABLE `customer` AUTO_INCREMENT = 4;
ALTER TABLE `product` AUTO_INCREMENT = 19;
ALTER TABLE `inventory_items` AUTO_INCREMENT = 13;
ALTER TABLE `order` AUTO_INCREMENT = 4;
ALTER TABLE `order_items` AUTO_INCREMENT = 4;
ALTER TABLE `appointments` AUTO_INCREMENT = 3;
ALTER TABLE `quotation` AUTO_INCREMENT = 3;
ALTER TABLE `payment` AUTO_INCREMENT = 2;
ALTER TABLE `system_activity_log` AUTO_INCREMENT = 4;
ALTER TABLE `status_history` AUTO_INCREMENT = 3;
ALTER TABLE `product_materials` AUTO_INCREMENT = 7;
ALTER TABLE `stock_transactions` AUTO_INCREMENT = 4;

-- ============================================================================
-- END OF SETUP
-- ============================================================================
-- 
-- Default Login Credentials:
-- Admin: admin@glassify.com / password123
-- Sales Rep: queen@gmail.com / password123
-- Customer: john.doe@example.com / password123
-- 
-- Note: All passwords are hashed. Default password is: password123
-- Please change passwords after first login for security.
-- ============================================================================
