-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 03:25 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `latest_glassifydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `change_description` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `InventoryItemID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `AppointmentID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `ClientName` varchar(255) DEFAULT NULL,
  `Service` enum('Order Placed','Ocular Visit','In Fabrication','Installed','Completed') DEFAULT 'Order Placed',
  `AppointmentDate` date DEFAULT NULL,
  `AppointmentTime` time DEFAULT NULL,
  `AssignedStaff` varchar(255) DEFAULT NULL,
  `AssignedStaff_ID` int(11) DEFAULT NULL,
  `Status` enum('In Progress','Complete','Cancelled') DEFAULT 'In Progress',
  `Notes` text DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `OrderID`, `Customer_ID`, `ProductName`, `ClientName`, `Service`, `AppointmentDate`, `AppointmentTime`, `AssignedStaff`, `AssignedStaff_ID`, `Status`, `Notes`, `Created_Date`, `Updated_Date`) VALUES
(1, 9, 2, 'Shower Enclosure', 'Meryl S. Colby', 'Installed', '2025-12-17', '10:00:00', 'Engr. Sushmita Sen', NULL, 'In Progress', '', '2025-12-08 13:32:27', '2025-12-08 06:34:44');

-- --------------------------------------------------------

--
-- Table structure for table `approved_orders`
--

CREATE TABLE `approved_orders` (
  `ApprovedOrderID` int(11) NOT NULL,
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
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approved_orders`
--

INSERT INTO `approved_orders` (`ApprovedOrderID`, `OrderID`, `OrderNumber`, `Customer_ID`, `SalesRep_ID`, `ProductName`, `Address`, `OrderDate`, `TotalQuotation`, `CustomerNotified`, `CustomerNotified_Date`, `ApprovedBy_SalesRep_ID`, `Approved_Date`, `Created_Date`, `Updated_Date`) VALUES
(1, 9, 'GI006', 2, 3, 'Shower Enclosure', '3 Santa Ana St., Merica Subd., Quezon City, Manila, Philippines, 1125', '2025-12-08 21:18:38', '15785.00', 0, NULL, 3, '2025-12-08 14:29:46', '2025-12-08 13:29:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `awaiting_admin_orders`
--

CREATE TABLE `awaiting_admin_orders` (
  `AwaitingOrderID` int(11) NOT NULL,
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
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `Cart_ID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `CustomizationID` int(11) DEFAULT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `Added_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `Customer_ID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`Customer_ID`, `UserID`, `Date_Created`) VALUES
(1, 1, '2025-12-07 17:04:19'),
(2, 6, '2025-12-08 04:39:36');

-- --------------------------------------------------------

--
-- Table structure for table `customization`
--

CREATE TABLE `customization` (
  `CustomizationID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL,
  `GlassShape` varchar(50) DEFAULT NULL,
  `GlassType` varchar(50) DEFAULT NULL,
  `GlassThickness` varchar(50) DEFAULT NULL,
  `EdgeWork` varchar(50) DEFAULT NULL,
  `FrameType` varchar(50) DEFAULT NULL,
  `Engraving` varchar(255) DEFAULT NULL,
  `DesignRef` varchar(255) DEFAULT NULL COMMENT 'File path to design image',
  `LEDBacklight` varchar(50) DEFAULT NULL COMMENT 'For mirrors',
  `DoorOperation` varchar(50) DEFAULT NULL COMMENT 'For shower enclosures',
  `Configuration` varchar(50) DEFAULT NULL COMMENT 'For aluminum doors',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `PriceBreakdown` text DEFAULT NULL COMMENT 'JSON string containing price breakdown details',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customization`
--

INSERT INTO `customization` (`CustomizationID`, `Customer_ID`, `Product_ID`, `Dimensions`, `GlassShape`, `GlassType`, `GlassThickness`, `EdgeWork`, `FrameType`, `Engraving`, `DesignRef`, `LEDBacklight`, `DoorOperation`, `Configuration`, `EstimatePrice`, `PriceBreakdown`, `CreatedAt`, `UpdatedAt`) VALUES
(5, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1765127075_6935b3a3b34fe.png', NULL, NULL, NULL, '15750.00', '{\"baseArea\":15750,\"shapeAddon\":0,\"typeAddon\":0,\"thicknessAddon\":0,\"frameAddon\":0,\"edgeAddon\":0,\"total\":15750}', '2025-12-07 17:04:35', '2025-12-07 17:04:35'),
(6, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1765128409_6935b8d95faa9.png', NULL, NULL, NULL, '15750.00', '{\"baseArea\":15750,\"shapeAddon\":0,\"typeAddon\":0,\"thicknessAddon\":0,\"frameAddon\":0,\"edgeAddon\":0,\"total\":15750}', '2025-12-07 17:26:49', '2025-12-07 17:26:49'),
(7, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1765129895_6935bea741843.png', NULL, NULL, NULL, '15750.00', '{\"baseArea\":15750,\"shapeAddon\":0,\"typeAddon\":0,\"thicknessAddon\":0,\"frameAddon\":0,\"edgeAddon\":0,\"total\":15750}', '2025-12-07 17:51:35', '2025-12-07 17:51:35'),
(8, 1, 1, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1765130100_6935bf74cecb6.png', NULL, NULL, NULL, '23625.00', '{\"baseArea\":23625,\"shapeAddon\":0,\"typeAddon\":0,\"thicknessAddon\":0,\"frameAddon\":0,\"edgeAddon\":0,\"total\":23625}', '2025-12-07 17:55:00', '2025-12-07 17:55:00'),
(9, 2, 2, '45 x 35', 'pentagon', 'tinted', '5mm', 'metered', 'aluminum', 'None', 'uploads/designs/wishlist_design_2_1765168828_693656bc4b21b.png', NULL, NULL, NULL, '27768.75', NULL, '2025-12-08 04:40:28', '2025-12-08 04:40:28'),
(10, 2, 2, '45 x 35', 'pentagon', 'tinted', '5mm', 'metered', 'aluminum', 'None', 'uploads/designs/wishlist_design_2_1765168828_693656bc4b21b.png', NULL, NULL, NULL, '27768.75', NULL, '2025-12-08 04:42:23', '2025-12-08 04:42:23'),
(12, 2, 2, '45 x 35', 'pentagon', 'frosted', '5mm', 'metered', 'aluminum', 'None', 'uploads/designs/design_2_1765171523_6936614320f7e.png', NULL, NULL, NULL, '26636.72', NULL, '2025-12-08 05:25:23', '2025-12-08 05:25:23'),
(23, 2, 1, '45 x 35', 'rectangle', 'tinted', '5mm', 'metered', 'aluminum', 'None', 'uploads/designs/design_2_1765187785_6936a0c9d9c7b.png', NULL, NULL, NULL, '33202.50', NULL, '2025-12-08 09:56:25', '2025-12-08 09:56:25'),
(25, 2, 1, '45 x 35', 'square', 'low-e', '6mm', 'beveled', 'wood', 'None', 'uploads/designs/design_2_1765189950_6936a93ed8a80.png', NULL, NULL, NULL, '52922.19', NULL, '2025-12-08 10:32:30', '2025-12-08 10:32:30'),
(27, 2, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_2_1765199854_6936cfeea4d4b.png', NULL, NULL, NULL, '15750.00', NULL, '2025-12-08 13:17:34', '2025-12-08 13:17:34');

-- --------------------------------------------------------

--
-- Table structure for table `disapproved_orders`
--

CREATE TABLE `disapproved_orders` (
  `DisapprovedOrderID` int(11) NOT NULL,
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
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `InventoryItemID` int(11) NOT NULL,
  `ItemID` varchar(50) NOT NULL COMMENT 'e.g., GL-001, AL-022',
  `Name` varchar(255) NOT NULL,
  `Category` varchar(100) NOT NULL,
  `InStock` int(11) NOT NULL DEFAULT 0,
  `min_threshold` int(11) DEFAULT 10,
  `Unit` varchar(50) NOT NULL COMMENT 'sqm, pcs, tubes, meter, sets, etc.',
  `Status` enum('In Stock','Low Stock','Out of Stock','New') DEFAULT 'In Stock',
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `DateUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_notifications`
--

CREATE TABLE `inventory_notifications` (
  `NotificationID` int(11) NOT NULL,
  `InventoryItemID` int(11) NOT NULL,
  `ItemID` varchar(50) NOT NULL,
  `ItemName` varchar(255) NOT NULL,
  `Message` text NOT NULL,
  `Status` enum('Unread','Read','Resolved') DEFAULT 'Unread',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issuereport`
--

CREATE TABLE `issuereport` (
  `Issue_ID` int(11) NOT NULL,
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
  `Priority` enum('Low','Medium','High') DEFAULT 'Low'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issuereport`
--

INSERT INTO `issuereport` (`Issue_ID`, `Customer_ID`, `Order_ID`, `First_Name`, `Last_Name`, `Email`, `PhoneNum`, `Category`, `Description`, `Report_Date`, `Status`, `Priority`) VALUES
(1, 2, NULL, 'Samantha', 'Panilio', 'lerumgops@gmail.com', '09328765983', 'Product Defect/Damage', 'agsgdgfhfdgdfsdfsfewfwefwefwef', '2025-12-08 11:36:02', 'Open', 'Low');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `OrderID` int(11) NOT NULL,
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
  `ApprovedBy_SalesRep_ID` int(11) DEFAULT NULL,
  `ApprovedBy_Admin_ID` int(11) DEFAULT NULL,
  `Approved_Date` datetime DEFAULT NULL,
  `DisapprovedBy` enum('Sales Rep','Admin') DEFAULT NULL,
  `DisapprovedBy_ID` int(11) DEFAULT NULL,
  `DisapprovalReason` text DEFAULT NULL,
  `Disapproved_Date` datetime DEFAULT NULL,
  `CustomerNotified` tinyint(1) DEFAULT 0,
  `CustomerNotified_Date` datetime DEFAULT NULL,
  `PreferredInstallationDate` date DEFAULT NULL COMMENT 'Customer preferred installation date (captured at checkout)',
  `OcularDate` date DEFAULT NULL COMMENT 'Scheduled date for ocular visit',
  `FabricationDate` date DEFAULT NULL COMMENT 'Scheduled date for fabrication',
  `InstallationDate` date DEFAULT NULL COMMENT 'Scheduled date for installation',
  `EstimatedDelivery` date DEFAULT NULL COMMENT 'Estimated delivery/completion date',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`OrderID`, `OrderNumber`, `Customer_ID`, `SalesRep_ID`, `OrderDate`, `TotalAmount`, `Status`, `PaymentStatus`, `PaymentMethod`, `DeliveryAddress`, `SpecialInstructions`, `QuotationPDFUrl`, `ContractPDFUrl`, `ApprovedBy_SalesRep_ID`, `ApprovedBy_Admin_ID`, `Approved_Date`, `DisapprovedBy`, `DisapprovedBy_ID`, `DisapprovalReason`, `Disapproved_Date`, `CustomerNotified`, `CustomerNotified_Date`, `PreferredInstallationDate`, `OcularDate`, `FabricationDate`, `InstallationDate`, `EstimatedDelivery`, `Created_Date`, `Updated_Date`) VALUES
(4, 'GI001', 1, 3, '2025-12-07 17:55:07', '23660.00', 'Awaiting Admin', 'Paid', NULL, '35 Malasimbo, Quezon City, Metro Manila, Philippines, 1102', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-07 17:55:07', '2025-12-08 13:11:23'),
(5, 'GI002', 2, 3, '2025-12-08 04:44:23', '27803.75', 'Pending Review', 'Paid', 'E-Wallet', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila, Philippines, 1125', 'Preferred Installation Date: December 17, 2025', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-17', NULL, NULL, NULL, NULL, '2025-12-08 04:44:23', '2025-12-08 11:13:33'),
(6, 'GI003', 2, 3, '2025-12-08 05:26:02', '26671.72', 'Pending Review', 'Paid', 'E-Wallet', '3 Santa Ana St., Merica Subd., Quezon City, Manila, Philippines, 1125', 'Preferred Installation Date: December 31, 2025', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-31', NULL, NULL, NULL, NULL, '2025-12-08 05:26:02', '2025-12-08 13:28:47'),
(7, 'GI004', 2, 3, '2025-12-08 09:57:18', '33237.50', 'Pending Review', 'Pending', 'E-Wallet', '3 Santa Ana St., Merica Subd., Quezon City, Manila, Philippines, 1125', 'Preferred Installation Date: December 18, 2025', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-18', NULL, NULL, NULL, NULL, '2025-12-08 09:57:18', '2025-12-08 09:57:18'),
(8, 'GI005', 2, 3, '2025-12-08 10:33:20', '52957.19', 'Pending Review', 'Pending', 'E-Wallet', '3 Santa Ana St., Merica Subd., Quezon City, Manila, Philippines, 1125', 'Preferred Installation Date: December 25, 2025', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-25', NULL, NULL, NULL, NULL, '2025-12-08 10:33:20', '2025-12-08 10:33:20'),
(9, 'GI006', 2, 3, '2025-12-08 13:18:38', '15785.00', 'Ready for Installation', 'Pending', 'E-Wallet', '3 Santa Ana St., Merica Subd., Quezon City, Manila, Philippines, 1125', 'Preferred Installation Date: December 17, 2025', NULL, NULL, 3, 2, '2025-12-08 14:29:46', NULL, NULL, NULL, NULL, 1, '2025-12-08 14:29:46', '2025-12-17', '2025-12-17', NULL, '2025-12-17', NULL, '2025-12-08 13:18:38', '2025-12-08 13:34:44');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `OrderItemID` int(11) NOT NULL,
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
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`OrderItemID`, `OrderID`, `Product_ID`, `CustomizationID`, `Quantity`, `UnitPrice`, `EstimatePrice`, `Dimensions`, `GlassShape`, `GlassType`, `GlassThickness`, `EdgeWork`, `FrameType`, `Engraving`, `DesignRef`, `Created_Date`) VALUES
(4, 4, 1, 8, 1, '1500.00', '23625.00', '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1765130100_6935bf74cecb6.png', '2025-12-07 17:55:07'),
(5, 5, 2, 10, 1, '1000.00', '27768.75', '45 x 35', 'pentagon', 'tinted', '5mm', 'metered', 'aluminum', 'None', 'uploads/designs/wishlist_design_2_1765168828_693656bc4b21b.png', '2025-12-08 04:44:23'),
(6, 6, 2, 12, 1, '1000.00', '26636.72', '45 x 35', 'pentagon', 'frosted', '5mm', 'metered', 'aluminum', 'None', 'uploads/designs/design_2_1765171523_6936614320f7e.png', '2025-12-08 05:26:02'),
(7, 7, 1, 23, 1, '1500.00', '33202.50', '45 x 35', 'rectangle', 'tinted', '5mm', 'metered', 'aluminum', 'None', 'uploads/designs/design_2_1765187785_6936a0c9d9c7b.png', '2025-12-08 09:57:19'),
(8, 8, 1, 25, 1, '1500.00', '52922.19', '45 x 35', 'square', 'low-e', '6mm', 'beveled', 'wood', 'None', 'uploads/designs/design_2_1765189950_6936a93ed8a80.png', '2025-12-08 10:33:20'),
(9, 9, 2, 27, 1, '1000.00', '15750.00', '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_2_1765199854_6936cfeea4d4b.png', '2025-12-08 13:18:38');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_ID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `CustomerName` varchar(255) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `PaymentMethod` enum('E-Wallet','Cash on Delivery') DEFAULT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Payment_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Transaction_ID` varchar(100) DEFAULT NULL,
  `ReceiptPath` varchar(255) DEFAULT NULL,
  `Status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Payment_ID`, `OrderID`, `CustomerName`, `ProductName`, `PaymentMethod`, `Amount`, `Payment_Date`, `Transaction_ID`, `ReceiptPath`, `Status`) VALUES
(2, 5, 'Meryl Colby', 'Shower Enclosure', 'E-Wallet', '27803.75', '2025-12-08 06:29:21', NULL, 'uploads/receipts/receipt_2_1765169063.png', 'Paid'),
(3, 6, 'Meryl Colby', 'Shower Enclosure', 'E-Wallet', '26671.72', '2025-12-08 06:28:48', NULL, 'uploads/receipts/receipt_2_1765171562.png', 'Paid'),
(4, 7, 'Meryl Colby', 'Sliding Window', 'E-Wallet', '33237.50', '2025-12-08 09:57:19', NULL, 'uploads/receipts/receipt_2_1765187838.png', 'Pending'),
(5, 8, 'Meryl Colby', 'Sliding Window', 'E-Wallet', '52957.19', '2025-12-08 10:33:20', NULL, 'uploads/receipts/receipt_2_1765190000.png', 'Pending'),
(6, 4, 'Aaron Gabriel Manantan', 'Sliding Window', 'E-Wallet', '23660.00', '2025-12-08 06:39:17', NULL, NULL, 'Paid'),
(7, 9, 'Meryl Colby', 'Shower Enclosure', 'E-Wallet', '15785.00', '2025-12-08 13:18:38', NULL, 'uploads/receipts/receipt_2_1765199917.png', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `pending_review_orders`
--

CREATE TABLE `pending_review_orders` (
  `PendingOrderID` int(11) NOT NULL,
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
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `Product_ID` int(11) NOT NULL,
  `ProductName` varchar(100) NOT NULL,
  `Category` varchar(50) NOT NULL,
  `Material` enum('Glass','Aluminum') NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `ImageUrl` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status` enum('In Stock','Out of Stock','Low Stock') DEFAULT 'Out of Stock'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`Product_ID`, `ProductName`, `Category`, `Material`, `Price`, `ImageUrl`, `Description`, `DateAdded`, `Status`) VALUES
(1, 'Sliding Window', 'Windows', 'Glass', '1500.00', 'e91c3c9737a1ce6f9fe0bab47c5bd6de.jpg', NULL, '2025-12-07 09:34:07', ''),
(2, 'Shower Enclosure', 'Shower Enclosure / Partition', 'Glass', '1000.00', 'fe18e12d50fe63f07ce3b3d97b61c69d.png', NULL, '2025-12-07 10:00:16', '');

-- --------------------------------------------------------

--
-- Table structure for table `product_materials`
--

CREATE TABLE `product_materials` (
  `ProductMaterialID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `InventoryItemID` int(11) NOT NULL,
  `QuantityRequired` decimal(10,2) NOT NULL COMMENT 'Amount of material needed per product unit',
  `Unit` varchar(50) DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projectschedule`
--

CREATE TABLE `projectschedule` (
  `Schedule_ID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `Admin_ID` int(11) NOT NULL,
  `Project_Name` varchar(100) NOT NULL,
  `Start_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Status` enum('Scheduled','In progress','Completed','Delayed') DEFAULT 'Scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation`
--

CREATE TABLE `quotation` (
  `QuotationID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `Quotation_num` varchar(20) NOT NULL,
  `Total_amount` decimal(10,2) DEFAULT NULL,
  `Tax_amount` decimal(10,2) DEFAULT NULL,
  `Terms_conditions` varchar(255) DEFAULT NULL,
  `Pdf_url` varchar(255) DEFAULT NULL,
  `Created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ready_to_approve_orders`
--

CREATE TABLE `ready_to_approve_orders` (
  `ReadyOrderID` int(11) NOT NULL,
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
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_notif`
--

CREATE TABLE `sales_notif` (
  `NotificationID` int(11) NOT NULL,
  `Icon` varchar(50) NOT NULL,
  `Role` varchar(50) NOT NULL,
  `Description` text NOT NULL,
  `Status` enum('Unread','Read') DEFAULT 'Unread',
  `Created_Date` datetime NOT NULL DEFAULT current_timestamp(),
  `Read_Date` datetime DEFAULT NULL,
  `RelatedID` int(11) DEFAULT NULL,
  `RelatedType` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_notif`
--

INSERT INTO `sales_notif` (`NotificationID`, `Icon`, `Role`, `Description`, `Status`, `Created_Date`, `Read_Date`, `RelatedID`, `RelatedType`) VALUES
(1, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:11:00', NULL, 5, 'Payment'),
(2, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:11:00', NULL, 5, 'Payment'),
(3, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:11:23', NULL, 4, 'Payment'),
(4, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:11:23', NULL, 4, 'Payment'),
(5, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI006 (Amount: ₱26,671.72) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:28:47', NULL, 6, 'Payment'),
(6, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI006 (Amount: ₱26,671.72) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:28:48', NULL, 6, 'Payment'),
(7, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:29:21', NULL, 5, 'Payment'),
(8, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:29:21', NULL, 5, 'Payment'),
(9, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:39:15', NULL, 4, 'Payment'),
(10, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:39:17', NULL, 4, 'Payment');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `transaction_id` int(11) NOT NULL,
  `InventoryItemID` int(11) NOT NULL,
  `transaction_type` enum('add','remove','adjust') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `previous_stock` int(11) DEFAULT NULL,
  `new_stock` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_activity_log`
--

CREATE TABLE `system_activity_log` (
  `ActivityID` int(11) NOT NULL,
  `Action` varchar(50) NOT NULL,
  `Description` text NOT NULL,
  `Role` varchar(50) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `UserName` varchar(100) DEFAULT NULL,
  `Timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `RelatedID` int(11) DEFAULT NULL,
  `RelatedType` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_activity_log`
--

INSERT INTO `system_activity_log` (`ActivityID`, `Action`, `Description`, `Role`, `UserID`, `UserName`, `Timestamp`, `RelatedID`, `RelatedType`) VALUES
(1, 'Product Added', 'New product added: Shower Enclosure', 'Admin', NULL, NULL, '2025-12-07 18:00:16', NULL, NULL),
(2, 'Order Created', 'New order created: GI001 (Customer ID: 1)', 'Customer', NULL, NULL, '2025-12-07 18:05:10', 1, 'Order'),
(3, 'Order Created', 'New order created: GI002 (Customer ID: 1)', 'Customer', NULL, NULL, '2025-12-07 18:26:57', 2, 'Order'),
(4, 'Order Created', 'New order created: GI003 (Customer ID: 1)', 'Customer', NULL, NULL, '2025-12-07 18:51:42', 3, 'Order'),
(5, 'Order Created', 'New order created: GI001 (Customer ID: 1)', 'Customer', NULL, NULL, '2025-12-07 18:55:07', 4, 'Order'),
(6, 'Order Status Updated', 'Order GI001 status updated to: Awaiting Admin', 'System', NULL, NULL, '2025-12-07 19:11:01', 4, 'Order'),
(7, 'Approval Requested', 'Order GI001 approval requested by Sales Rep. Notes: None', 'Sales Representative', 3, NULL, '2025-12-07 19:11:01', 4, 'Order'),
(8, 'Order Created', 'New order created: GI002 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-08 05:44:23', 5, 'Order'),
(9, 'Order Created', 'New order created: GI003 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-08 06:26:02', 6, 'Order'),
(10, 'Order Created', 'New order created: GI004 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-08 10:57:18', 7, 'Order'),
(11, 'Order Created', 'New order created: GI005 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-08 11:33:20', 8, 'Order'),
(12, 'Payment Received', 'Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:11:00', 5, 'Payment'),
(13, 'Payment Received', 'Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:11:00', 5, 'Payment'),
(14, 'Payment Received', 'Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:11:23', 4, 'Payment'),
(15, 'Payment Received', 'Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:11:23', 4, 'Payment'),
(16, 'Order Created', 'New order created: GI006 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-08 14:18:38', 9, 'Order'),
(17, 'Order Status Updated', 'Order GI006 status updated to: Awaiting Admin', 'System', NULL, NULL, '2025-12-08 14:22:01', 9, 'Order'),
(18, 'Approval Requested', 'Order GI006 approval requested by Sales Rep. Notes: None', 'Sales Representative', 3, NULL, '2025-12-08 14:22:01', 9, 'Order'),
(19, 'Order Approved by Admin', 'Order GI006 approved by Admin. Notes: None', 'Admin', 2, NULL, '2025-12-08 14:26:56', 9, 'Order'),
(20, 'Payment Received', 'Payment for Order GI006 (Amount: ₱26,671.72) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:28:47', 6, 'Payment'),
(21, 'Payment Received', 'Payment for Order GI006 (Amount: ₱26,671.72) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:28:48', 6, 'Payment'),
(22, 'Payment Received', 'Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:29:21', 5, 'Payment'),
(23, 'Payment Received', 'Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:29:21', 5, 'Payment'),
(24, 'Order Approved', 'Order GI006 has been approved by Sales Rep. Customer can now proceed with payment.', 'Sales Representative', 3, NULL, '2025-12-08 14:29:46', 9, 'Order'),
(25, 'Payment Received', 'Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:39:15', 4, 'Payment'),
(26, 'Payment Received', 'Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:39:17', 4, 'Payment');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `PhoneNum` varchar(13) NOT NULL,
  `ImageUrl` varchar(255) DEFAULT NULL COMMENT 'Profile picture path',
  `Role` enum('Admin','Sales Representative','Inventory Officer','Customer') NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `Date_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Last_Active` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `First_Name`, `Last_Name`, `Middle_Name`, `Email`, `Password`, `reset_token`, `reset_token_expiry`, `PhoneNum`, `ImageUrl`, `Role`, `Status`, `Date_Created`, `Date_Updated`, `Last_Active`) VALUES
(1, 'Aaron Gabriel', 'Manantan', 'M.', 'manantan.aro@gmail.com', '$2y$10$q8v9lhdmbCUtn2afm/tSMe58NypAC/fmip47pgQXV/S5H80/AcpbW', NULL, NULL, '09937568011', 'uploads/profile/profile_1.png', 'Customer', 'Active', '2025-12-07 16:21:24', '2025-12-07 09:32:33', NULL),
(2, 'Admin', 'Test', '', 'admin.test@gmail.com', '$2y$10$H/RROdyDMNk9XN1JLbUYFeDfsGkotLtkzXOa5CTp5uBYFqb4Fb/gm', NULL, NULL, '09937569023', NULL, 'Admin', 'Active', '2025-12-07 16:23:22', '2025-12-07 16:23:37', NULL),
(3, 'Sales', 'Test', '', 'sales.rep@gmail.com', '$2y$10$ZTBDhCxjJi4ZZtwXa5B0rOWrd5j.zhsA6AntbN4QYOebpGn2SW/Em', NULL, NULL, '09937569024', NULL, 'Sales Representative', 'Active', '2025-12-07 17:10:33', '2025-12-07 17:10:33', NULL),
(4, 'Janaya', 'Empress', '', 'janaya@gmail.com', '$2y$10$YS9R5EcOrOybN.ScTK5uyObqGP.RIC/spkAqR2Y4IdYfbZkF7Geni', NULL, NULL, '09876543219', NULL, 'Sales Representative', 'Active', '2025-12-08 04:36:20', '2025-12-08 04:36:20', NULL),
(5, 'Marco', 'Gumabao', '', 'marco@gmail.com', '$2y$10$m2fSSxRC4K0RQqg7bV6x5eflam74AsYmM1IpGz1kFsxKZeLyx49cS', NULL, NULL, '09678543216', NULL, 'Inventory Officer', 'Active', '2025-12-08 04:36:57', '2025-12-08 04:36:57', NULL),
(6, 'Meryl', 'Colby', 'S.', 'lerumgops@gmail.com', '$2y$10$3RCzS5C4IZTwPNdCehNklOG1hOR.qLz/.eq7Dqrcoy4EAkILm1f6u', NULL, NULL, '09120844695', 'uploads/profile/profile_6.png', 'Customer', 'Active', '2025-12-08 04:39:36', '2025-12-07 22:21:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `AddressID` int(11) NOT NULL,
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
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`AddressID`, `UserID`, `AddressType`, `AddressLine`, `City`, `Province`, `Country`, `ZipCode`, `Note`, `IsDefault`, `Created_Date`, `Updated_Date`) VALUES
(1, 1, 'Shipping', '35 Malasimbo', 'Quezon City', 'Metro Manila', 'Philippines', '1102', NULL, 0, '2025-12-07 16:24:30', '2025-12-07 16:24:30'),
(2, 6, 'Shipping', '3 Santa Ana St., Merica Subd.', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-08 04:50:36', '2025-12-08 05:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `Wishlist_ID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `CustomizationID` int(11) DEFAULT NULL,
  `DateAdded` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_inventory_item` (`InventoryItemID`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`AppointmentID`),
  ADD KEY `idx_order` (`OrderID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_service` (`Service`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_date` (`AppointmentDate`),
  ADD KEY `idx_staff` (`AssignedStaff_ID`);

--
-- Indexes for table `approved_orders`
--
ALTER TABLE `approved_orders`
  ADD PRIMARY KEY (`ApprovedOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_ordernumber` (`OrderNumber`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`),
  ADD KEY `fk_approved_orders_salesrep_approved` (`ApprovedBy_SalesRep_ID`);

--
-- Indexes for table `awaiting_admin_orders`
--
ALTER TABLE `awaiting_admin_orders`
  ADD PRIMARY KEY (`AwaitingOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_ordernumber` (`OrderNumber`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`Cart_ID`),
  ADD UNIQUE KEY `unique_cart_item` (`Customer_ID`,`Product_ID`,`CustomizationID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_product` (`Product_ID`),
  ADD KEY `idx_customization` (`CustomizationID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`Customer_ID`),
  ADD UNIQUE KEY `UserID` (`UserID`);

--
-- Indexes for table `customization`
--
ALTER TABLE `customization`
  ADD PRIMARY KEY (`CustomizationID`),
  ADD KEY `idx_customer_id` (`Customer_ID`),
  ADD KEY `idx_product_id` (`Product_ID`),
  ADD KEY `idx_created_at` (`CreatedAt`);

--
-- Indexes for table `disapproved_orders`
--
ALTER TABLE `disapproved_orders`
  ADD PRIMARY KEY (`DisapprovedOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_ordernumber` (`OrderNumber`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`),
  ADD KEY `fk_disapproved_orders_disapproved_by` (`DisapprovedBy_ID`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`InventoryItemID`),
  ADD UNIQUE KEY `ItemID` (`ItemID`),
  ADD KEY `idx_category` (`Category`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_instock` (`InStock`);

--
-- Indexes for table `inventory_notifications`
--
ALTER TABLE `inventory_notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `idx_inventory_item` (`InventoryItemID`),
  ADD KEY `idx_status` (`Status`);

--
-- Indexes for table `issuereport`
--
ALTER TABLE `issuereport`
  ADD PRIMARY KEY (`Issue_ID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_order` (`Order_ID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_priority` (`Priority`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`OrderID`),
  ADD UNIQUE KEY `OrderNumber` (`OrderNumber`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_payment_status` (`PaymentStatus`),
  ADD KEY `idx_order_date` (`OrderDate`),
  ADD KEY `fk_order_approved_salesrep` (`ApprovedBy_SalesRep_ID`),
  ADD KEY `fk_order_approved_admin` (`ApprovedBy_Admin_ID`),
  ADD KEY `fk_order_disapproved_by` (`DisapprovedBy_ID`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`OrderItemID`),
  ADD KEY `idx_order` (`OrderID`),
  ADD KEY `idx_product` (`Product_ID`),
  ADD KEY `idx_customization` (`CustomizationID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Payment_ID`),
  ADD KEY `idx_order` (`OrderID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_payment_date` (`Payment_Date`);

--
-- Indexes for table `pending_review_orders`
--
ALTER TABLE `pending_review_orders`
  ADD PRIMARY KEY (`PendingOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_ordernumber` (`OrderNumber`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`Product_ID`),
  ADD KEY `idx_category` (`Category`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_material` (`Material`);

--
-- Indexes for table `product_materials`
--
ALTER TABLE `product_materials`
  ADD PRIMARY KEY (`ProductMaterialID`),
  ADD KEY `idx_product` (`Product_ID`),
  ADD KEY `idx_inventory` (`InventoryItemID`);

--
-- Indexes for table `projectschedule`
--
ALTER TABLE `projectschedule`
  ADD PRIMARY KEY (`Schedule_ID`),
  ADD KEY `idx_order` (`OrderID`),
  ADD KEY `idx_admin` (`Admin_ID`);

--
-- Indexes for table `quotation`
--
ALTER TABLE `quotation`
  ADD PRIMARY KEY (`QuotationID`),
  ADD UNIQUE KEY `Quotation_num` (`Quotation_num`),
  ADD KEY `idx_order` (`OrderID`);

--
-- Indexes for table `ready_to_approve_orders`
--
ALTER TABLE `ready_to_approve_orders`
  ADD PRIMARY KEY (`ReadyOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`),
  ADD KEY `idx_admin_status` (`AdminStatus`);

--
-- Indexes for table `sales_notif`
--
ALTER TABLE `sales_notif`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_role` (`Role`),
  ADD KEY `idx_created_date` (`Created_Date`);

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_inventory_item` (`InventoryItemID`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `system_activity_log`
--
ALTER TABLE `system_activity_log`
  ADD PRIMARY KEY (`ActivityID`),
  ADD KEY `idx_timestamp` (`Timestamp`),
  ADD KEY `idx_action` (`Action`),
  ADD KEY `idx_user` (`UserID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_reset_token` (`reset_token`),
  ADD KEY `idx_role` (`Role`),
  ADD KEY `idx_status` (`Status`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`AddressID`),
  ADD KEY `idx_userid` (`UserID`),
  ADD KEY `idx_addresstype` (`AddressType`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`Wishlist_ID`),
  ADD UNIQUE KEY `unique_wishlist_item` (`Customer_ID`,`Product_ID`,`CustomizationID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_product` (`Product_ID`),
  ADD KEY `idx_customization` (`CustomizationID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `approved_orders`
--
ALTER TABLE `approved_orders`
  MODIFY `ApprovedOrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `awaiting_admin_orders`
--
ALTER TABLE `awaiting_admin_orders`
  MODIFY `AwaitingOrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `Cart_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `Customer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customization`
--
ALTER TABLE `customization`
  MODIFY `CustomizationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `disapproved_orders`
--
ALTER TABLE `disapproved_orders`
  MODIFY `DisapprovedOrderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `InventoryItemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_notifications`
--
ALTER TABLE `inventory_notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issuereport`
--
ALTER TABLE `issuereport`
  MODIFY `Issue_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pending_review_orders`
--
ALTER TABLE `pending_review_orders`
  MODIFY `PendingOrderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `ProductMaterialID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projectschedule`
--
ALTER TABLE `projectschedule`
  MODIFY `Schedule_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation`
--
ALTER TABLE `quotation`
  MODIFY `QuotationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ready_to_approve_orders`
--
ALTER TABLE `ready_to_approve_orders`
  MODIFY `ReadyOrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales_notif`
--
ALTER TABLE `sales_notif`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_activity_log`
--
ALTER TABLE `system_activity_log`
  MODIFY `ActivityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `AddressID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `Wishlist_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `fk_activities_inventory` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_activities_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointments_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointments_staff` FOREIGN KEY (`AssignedStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `approved_orders`
--
ALTER TABLE `approved_orders`
  ADD CONSTRAINT `fk_approved_orders_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_approved_orders_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_approved_orders_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_approved_orders_salesrep_approved` FOREIGN KEY (`ApprovedBy_SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `awaiting_admin_orders`
--
ALTER TABLE `awaiting_admin_orders`
  ADD CONSTRAINT `fk_awaiting_orders_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_awaiting_orders_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_awaiting_orders_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_customization` FOREIGN KEY (`CustomizationID`) REFERENCES `customization` (`CustomizationID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE;

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `fk_customer_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `customization`
--
ALTER TABLE `customization`
  ADD CONSTRAINT `fk_customization_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_customization_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE;

--
-- Constraints for table `disapproved_orders`
--
ALTER TABLE `disapproved_orders`
  ADD CONSTRAINT `fk_disapproved_orders_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_disapproved_orders_disapproved_by` FOREIGN KEY (`DisapprovedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_disapproved_orders_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_disapproved_orders_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_notifications`
--
ALTER TABLE `inventory_notifications`
  ADD CONSTRAINT `fk_inventory_notifications_item` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE;

--
-- Constraints for table `issuereport`
--
ALTER TABLE `issuereport`
  ADD CONSTRAINT `fk_issuereport_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_issuereport_order` FOREIGN KEY (`Order_ID`) REFERENCES `order` (`OrderID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `fk_order_approved_admin` FOREIGN KEY (`ApprovedBy_Admin_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_approved_salesrep` FOREIGN KEY (`ApprovedBy_SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`),
  ADD CONSTRAINT `fk_order_disapproved_by` FOREIGN KEY (`DisapprovedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_customization` FOREIGN KEY (`CustomizationID`) REFERENCES `customization` (`CustomizationID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE;

--
-- Constraints for table `pending_review_orders`
--
ALTER TABLE `pending_review_orders`
  ADD CONSTRAINT `fk_pending_orders_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pending_orders_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pending_orders_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `product_materials`
--
ALTER TABLE `product_materials`
  ADD CONSTRAINT `fk_product_materials_inventory` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_materials_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE;

--
-- Constraints for table `projectschedule`
--
ALTER TABLE `projectschedule`
  ADD CONSTRAINT `fk_projectschedule_admin` FOREIGN KEY (`Admin_ID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `fk_projectschedule_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE;

--
-- Constraints for table `quotation`
--
ALTER TABLE `quotation`
  ADD CONSTRAINT `fk_quotation_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE;

--
-- Constraints for table `ready_to_approve_orders`
--
ALTER TABLE `ready_to_approve_orders`
  ADD CONSTRAINT `fk_ready_orders_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ready_orders_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD CONSTRAINT `fk_stock_transactions_inventory` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_stock_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `system_activity_log`
--
ALTER TABLE `system_activity_log`
  ADD CONSTRAINT `fk_activity_log_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `fk_user_address_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_customization` FOREIGN KEY (`CustomizationID`) REFERENCES `customization` (`CustomizationID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
