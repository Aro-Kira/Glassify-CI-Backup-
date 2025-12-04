-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 07:27 AM
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
-- Database: `glassify-test`
--

-- --------------------------------------------------------

--
-- Table structure for table `aluminum_bathroom_doors_customization`
--

CREATE TABLE `aluminum_bathroom_doors_customization` (
  `CustomizationID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `OrderID` varchar(50) DEFAULT NULL,
  `Dimensions` varchar(255) DEFAULT NULL,
  `FrameType` varchar(50) DEFAULT NULL,
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aluminum_bathroom_doors_customization`
--

INSERT INTO `aluminum_bathroom_doors_customization` (`CustomizationID`, `Customer_ID`, `Product_ID`, `OrderID`, `Dimensions`, `FrameType`, `EstimatePrice`, `Created_Date`) VALUES
(1, 9, 10, 'GI016', '80\" x 30\"', 'Framed', '6500.00', '2025-12-03 18:45:39'),
(2, 10, 11, 'GI017', '84\" x 32\"', 'Semi-Frameless', '8000.00', '2025-12-03 18:45:39'),
(3, 11, 12, 'GI018', '88\" x 34\"', 'Frameless', '9500.00', '2025-12-03 18:45:39'),
(4, 9, 10, 'GI019', '80\" x 30\"', 'Framed', '6500.00', '2025-12-03 18:47:25'),
(5, 10, 11, 'GI020', '84\" x 32\"', 'Semi-Frameless', '8000.00', '2025-12-03 18:47:25'),
(6, 11, 12, NULL, '88\" x 34\"', 'Frameless', '9500.00', '2025-12-03 18:47:25'),
(7, 12, 10, NULL, '78\" x 28\"', 'Framed', '6000.00', '2025-12-03 18:47:25'),
(8, 1, 11, NULL, '82\" x 30\"', 'Semi-Frameless', '7500.00', '2025-12-03 18:47:25'),
(9, 3, 12, NULL, '86\" x 32\"', 'Frameless', '9000.00', '2025-12-03 18:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `aluminum_doors_customization`
--

CREATE TABLE `aluminum_doors_customization` (
  `CustomizationID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `OrderID` varchar(50) DEFAULT NULL,
  `Dimensions` varchar(255) DEFAULT NULL,
  `GlassType` varchar(50) DEFAULT NULL COMMENT 'same as default',
  `GlassThickness` varchar(50) DEFAULT NULL COMMENT '6mm, 10mm',
  `Configuration` varchar(50) DEFAULT NULL COMMENT '2-panel slider, 3-panel slider, 4-panel slider',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aluminum_doors_customization`
--

INSERT INTO `aluminum_doors_customization` (`CustomizationID`, `Customer_ID`, `Product_ID`, `OrderID`, `Dimensions`, `GlassType`, `GlassThickness`, `Configuration`, `EstimatePrice`, `Created_Date`) VALUES
(1, 7, 7, 'GI011', '96\" x 84\"', 'Tempered', '6mm', '2-panel slider', '15000.00', '2025-12-03 18:45:39'),
(2, 8, 8, 'GI012', '120\" x 90\"', 'Tempered', '10mm', '3-panel slider', '22000.00', '2025-12-03 18:45:39'),
(3, 9, 9, 'GI013', '144\" x 96\"', 'Tempered', '10mm', '4-panel slider', '28000.00', '2025-12-03 18:45:39'),
(4, 7, 7, 'GI014', '96\" x 84\"', 'Tempered', '6mm', '2-panel slider', '15000.00', '2025-12-03 18:47:25'),
(5, 8, 8, 'GI015', '120\" x 90\"', 'Tempered', '10mm', '3-panel slider', '22000.00', '2025-12-03 18:47:25'),
(6, 9, 9, NULL, '144\" x 96\"', 'Tempered', '10mm', '4-panel slider', '28000.00', '2025-12-03 18:47:25'),
(7, 10, 7, NULL, '84\" x 78\"', 'Tempered', '6mm', '2-panel slider', '14000.00', '2025-12-03 18:47:25'),
(8, 11, 8, NULL, '108\" x 84\"', 'Tempered', '10mm', '3-panel slider', '20000.00', '2025-12-03 18:47:25'),
(9, 12, 9, NULL, '132\" x 90\"', 'Tempered', '10mm', '4-panel slider', '26000.00', '2025-12-03 18:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `Appointment_ID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `Admin_ID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Scheduled_Date` date NOT NULL,
  `Start_time` datetime DEFAULT NULL,
  `End_time` datetime DEFAULT NULL,
  `Notes` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approved_orders`
--

CREATE TABLE `approved_orders` (
  `ApprovedOrderID` int(11) NOT NULL,
  `OrderID` varchar(50) DEFAULT NULL,
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
  `ApprovedBy_SalesRep_ID` int(11) DEFAULT NULL,
  `Approved_Date` datetime DEFAULT NULL,
  `CustomerNotified` tinyint(1) DEFAULT 0,
  `CustomerNotified_Date` datetime DEFAULT NULL,
  `PaymentMethod` enum('E-Wallet','Cash on Delivery') DEFAULT NULL,
  `PaymentStatus` enum('Pending','Paid','Failed') DEFAULT 'Pending',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `awaiting_admin_orders`
--

CREATE TABLE `awaiting_admin_orders` (
  `AwaitingOrderID` int(11) NOT NULL,
  `OrderID` varchar(50) DEFAULT NULL,
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
  `RequestedBy_SalesRep_ID` int(11) DEFAULT NULL,
  `Requested_Date` datetime DEFAULT NULL,
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
  `Quantity` int(11) NOT NULL,
  `Added_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`Cart_ID`, `Customer_ID`, `Product_ID`, `CustomizationID`, `Quantity`, `Added_Date`) VALUES
(0, 6, 1, 5, 1, '2025-12-03 21:18:43'),
(10, 1, 2, 15, 1, '2025-11-27 05:11:20'),
(11, 1, 1, 16, 1, '2025-11-27 06:25:50'),
(12, 1, 2, 17, 1, '2025-12-01 12:30:13'),
(13, 1, 2, 18, 1, '2025-12-04 03:48:02');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `Customer_ID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`Customer_ID`, `UserID`) VALUES
(1, 1),
(17, 3),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(9, 9),
(10, 10),
(11, 11),
(12, 12),
(13, 13),
(14, 14),
(15, 15),
(16, 16);

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
  `DesignRef` varchar(255) DEFAULT NULL,
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customization`
--

INSERT INTO `customization` (`CustomizationID`, `Customer_ID`, `Product_ID`, `Dimensions`, `GlassShape`, `GlassType`, `GlassThickness`, `EdgeWork`, `FrameType`, `Engraving`, `DesignRef`, `EstimatePrice`, `Created_Date`, `Quantity`) VALUES
(5, 6, 1, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', '', '3.00', '2025-12-03 21:18:43', 1),
(15, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', '', '189.00', '2025-11-27 05:11:20', 1),
(16, 1, 1, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', '', '3.00', '2025-11-27 06:25:50', 1),
(17, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', '', '189.00', '2025-12-01 12:30:13', 1),
(18, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', '', '189.00', '2025-12-04 03:48:02', 1);

-- --------------------------------------------------------

--
-- Table structure for table `disapproved_orders`
--

CREATE TABLE `disapproved_orders` (
  `DisapprovedOrderID` int(11) NOT NULL,
  `OrderID` varchar(50) DEFAULT NULL,
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
  `DisapprovedBy` enum('Sales Rep','Admin') DEFAULT NULL,
  `DisapprovedBy_ID` int(11) DEFAULT NULL,
  `DisapprovalReason` text DEFAULT NULL,
  `Disapproved_Date` datetime DEFAULT NULL,
  `CustomerNotified` tinyint(1) DEFAULT 0,
  `CustomerNotified_Date` datetime DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enduser`
--

CREATE TABLE `enduser` (
  `EndUser_ID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `PhoneNum` varchar(13) NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `Date_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Last_Active` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enduser`
--

INSERT INTO `enduser` (`EndUser_ID`, `UserID`, `First_Name`, `Last_Name`, `Middle_Name`, `Email`, `PhoneNum`, `Status`, `Date_Created`, `Date_Updated`, `Last_Active`) VALUES
(1, 1, 'Aro', 'Manantan', 'M.', 'manantan.aro@gmail.com', '09937568015', 'Active', '2025-11-25 20:39:15', '2025-11-25 20:39:15', NULL),
(2, 3, 'Ag', 'Pau', '', 'pau1@gmail.com', '09614788448', 'Active', '2025-12-03 07:23:31', '2025-12-03 07:23:31', NULL),
(3, 5, 'Maria', 'Santos', 'C.', 'maria.santos@email.com', '09171234567', 'Active', '2025-12-03 13:39:34', '2025-12-03 13:39:34', NULL),
(4, 6, 'Juan', 'Cruz', 'D.', 'juan.cruz@email.com', '09172345678', 'Active', '2025-12-03 13:39:34', '2025-12-03 13:39:34', NULL),
(5, 7, 'Ana', 'Reyes', 'B.', 'ana.reyes@email.com', '09173456789', 'Active', '2025-12-03 13:39:34', '2025-12-03 13:39:34', NULL),
(6, 8, 'Carlos', 'Torres', 'E.', 'carlos.torres@email.com', '09174567890', 'Active', '2025-12-03 13:39:34', '2025-12-03 13:39:34', NULL),
(7, 9, 'Rosa', 'Garcia', 'F.', 'rosa.garcia@email.com', '09175678901', 'Active', '2025-12-03 13:39:34', '2025-12-03 13:39:34', NULL),
(8, 10, 'Pedro', 'Lopez', 'G.', 'pedro.lopez@email.com', '09176789012', 'Active', '2025-12-03 13:39:34', '2025-12-03 13:39:34', NULL),
(9, 11, 'Maria', 'Santos', 'C.', 'maria.santos.customer@email.com', '09171234567', 'Active', '2025-12-03 13:41:10', '2025-12-03 13:41:10', NULL),
(10, 12, 'Juan', 'Cruz', 'D.', 'juan.cruz.customer@email.com', '09172345678', 'Active', '2025-12-03 13:41:10', '2025-12-03 13:41:10', NULL),
(11, 13, 'Ana', 'Reyes', 'B.', 'ana.reyes.customer@email.com', '09173456789', 'Active', '2025-12-03 13:41:10', '2025-12-03 13:41:10', NULL),
(12, 14, 'Carlos', 'Torres', 'E.', 'carlos.torres.customer@email.com', '09174567890', 'Active', '2025-12-03 13:41:10', '2025-12-03 13:41:10', NULL),
(13, 15, 'Rosa', 'Garcia', 'F.', 'rosa.garcia.customer@email.com', '09175678901', 'Active', '2025-12-03 13:41:10', '2025-12-03 13:41:10', NULL),
(14, 16, 'Pedro', 'Lopez', 'G.', 'pedro.lopez.customer@email.com', '09176789012', 'Active', '2025-12-03 13:41:10', '2025-12-03 13:41:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `Inventory_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `SalesRep_ID` int(11) NOT NULL,
  `QuantityInStock` int(11) NOT NULL DEFAULT 0,
  `MinimumStockLevel` int(11) DEFAULT 0,
  `MaximumStockLevel` int(11) DEFAULT 1000,
  `LastUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `UpdatedBy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `inventory`
--
DELIMITER $$
CREATE TRIGGER `update_product_status` AFTER INSERT ON `inventory` FOR EACH ROW BEGIN
    IF NEW.QuantityInStock = 0 THEN
        UPDATE Product SET Status = 'Out of Stock'
        WHERE Product_ID = NEW.Product_ID;

    ELSEIF NEW.QuantityInStock <= NEW.MinimumStockLevel THEN
        UPDATE Product SET Status = 'Low Stock'
        WHERE Product_ID = NEW.Product_ID;

    ELSE
        UPDATE Product SET Status = 'In Stock'
        WHERE Product_ID = NEW.Product_ID;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_product_status_on_update` AFTER UPDATE ON `inventory` FOR EACH ROW BEGIN
    IF NEW.QuantityInStock = 0 THEN
        UPDATE Product SET Status = 'Out of Stock'
        WHERE Product_ID = NEW.Product_ID;

    ELSEIF NEW.QuantityInStock <= NEW.MinimumStockLevel THEN
        UPDATE Product SET Status = 'Low Stock'
        WHERE Product_ID = NEW.Product_ID;

    ELSE
        UPDATE Product SET Status = 'In Stock'
        WHERE Product_ID = NEW.Product_ID;
    END IF;
END
$$
DELIMITER ;

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
  `Unit` varchar(50) NOT NULL COMMENT 'sqm, pcs, tubes, meter, sets, etc.',
  `Status` enum('In Stock','Low Stock','Out of Stock','New') DEFAULT 'In Stock',
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `DateUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`InventoryItemID`, `ItemID`, `Name`, `Category`, `InStock`, `Unit`, `Status`, `DateAdded`, `DateUpdated`) VALUES
(1, 'GL-001', 'Tempered Glass', 'Glass', 150, 'sqm', 'New', '2025-12-03 22:28:12', '2025-12-03 23:03:17'),
(2, 'AL-022', 'Aluminum Frame', 'Aluminum', 10, 'pcs', 'New', '2025-12-03 22:28:12', '2025-12-03 23:03:17'),
(3, 'GL-002', 'Laminated Glass', 'Glass', 120, 'sqm', 'New', '2025-12-03 22:28:12', '2025-12-03 23:03:17'),
(4, 'AC-003', 'Silicone Sealant', 'Accessories', 200, 'tubes', 'New', '2025-12-03 22:28:12', '2025-12-03 23:03:17'),
(5, 'AL-045', 'Sliding Track', 'Aluminum', 80, 'meter', 'New', '2025-12-03 22:28:12', '2025-12-03 23:03:17'),
(6, 'HD-007', 'Handle Set', 'Hardware', 2, 'sets', 'New', '2025-12-03 22:28:12', '2025-12-03 22:28:12');

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
  `Customer_ID` int(11) NOT NULL,
  `Order_ID` int(11) NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `PhoneNum` varchar(13) NOT NULL,
  `Category` enum('Order Issue','Billing Issue','Delivery Issue') DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Report_Date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mirror_customization`
--

CREATE TABLE `mirror_customization` (
  `CustomizationID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `OrderID` varchar(50) DEFAULT NULL,
  `Dimensions` varchar(255) DEFAULT NULL COMMENT 'Height x Width (or Diameter for Circle)',
  `EdgeWork` varchar(50) DEFAULT NULL COMMENT 'polished, beveled, same lang',
  `GlassShape` varchar(50) DEFAULT NULL COMMENT 'Rectangle, Circle, Oval, Arch, Capsule',
  `LEDBacklight` varchar(50) DEFAULT NULL COMMENT 'Optional',
  `Engraving` varchar(255) DEFAULT NULL COMMENT 'Optional',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mirror_customization`
--

INSERT INTO `mirror_customization` (`CustomizationID`, `Customer_ID`, `Product_ID`, `OrderID`, `Dimensions`, `EdgeWork`, `GlassShape`, `LEDBacklight`, `Engraving`, `EstimatePrice`, `Created_Date`) VALUES
(1, 1, 1, 'GI001', '24\" x 18\"', 'polished', 'Rectangle', 'Yes', 'N/A', '2500.00', '2025-12-03 18:45:39'),
(2, 3, 2, 'GI001', '30\" (Diameter)', 'beveled', 'Circle', 'No', 'Custom Text', '3000.00', '2025-12-03 18:45:39'),
(3, 5, 3, 'GI001', '36\" x 24\"', 'same lang', 'Oval', 'Yes', 'N/A', '2800.00', '2025-12-03 18:45:39'),
(4, 1, 1, 'GI001', '24\" x 18\"', 'polished', 'Rectangle', 'Yes', 'N/A', '2500.00', '2025-12-03 18:47:25'),
(5, 3, 2, 'GI002', '30\" (Diameter)', 'beveled', 'Circle', 'No', 'Custom Text', '3000.00', '2025-12-03 18:47:25'),
(6, 5, 3, 'GI003', '36\" x 24\"', 'same lang', 'Oval', 'Yes', 'N/A', '2800.00', '2025-12-03 18:47:25'),
(7, 6, 1, 'GI004', '20\" x 16\"', 'polished', 'Rectangle', 'No', 'N/A', '1800.00', '2025-12-03 18:47:25'),
(8, 7, 2, 'GI005', '28\" (Diameter)', 'beveled', 'Circle', 'Yes', 'Logo', '3200.00', '2025-12-03 18:47:25'),
(9, 8, 3, NULL, '32\" x 20\"', 'polished', 'Arch', 'No', 'N/A', '2200.00', '2025-12-03 18:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `OrderID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `SalesRep_ID` int(11) NOT NULL,
  `OrderDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `TotalAmount` decimal(12,2) NOT NULL,
  `Status` enum('Pending','Approved','In Fabrication','Ready for Installation','Completed','Cancelled','Returned') DEFAULT 'Pending',
  `PaymentStatus` enum('Pending','Paid','Partial','Refunded') DEFAULT 'Pending',
  `DeliveryAddress` varchar(255) DEFAULT NULL,
  `SpecialInstructions` varchar(255) DEFAULT NULL,
  `QuotationPDFUrl` varchar(255) DEFAULT NULL,
  `ContractPDFUrl` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`OrderID`, `Customer_ID`, `SalesRep_ID`, `OrderDate`, `TotalAmount`, `Status`, `PaymentStatus`, `DeliveryAddress`, `SpecialInstructions`, `QuotationPDFUrl`, `ContractPDFUrl`) VALUES
(1, 1, 2, '2025-12-03 18:54:33', '2500.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(3, 17, 2, '2025-12-03 18:56:59', '3000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(4, 5, 2, '2025-12-03 18:58:44', '2800.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(5, 1, 2, '2025-12-03 18:59:57', '2500.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(6, 17, 2, '2025-12-03 18:59:57', '3000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(7, 5, 2, '2025-12-03 18:59:57', '2800.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(8, 6, 2, '2025-12-03 18:59:57', '1800.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(9, 7, 2, '2025-12-03 18:59:57', '3200.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(10, 5, 2, '2025-12-03 18:59:57', '8500.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(11, 6, 2, '2025-12-03 18:59:57', '12000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(12, 7, 2, '2025-12-03 18:59:57', '15000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(13, 5, 2, '2025-12-03 18:59:57', '8500.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(14, 6, 2, '2025-12-03 18:59:57', '12000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(15, 7, 2, '2025-12-03 18:59:57', '15000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(16, 8, 2, '2025-12-03 18:59:57', '22000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(17, 9, 2, '2025-12-03 18:59:57', '28000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(18, 7, 2, '2025-12-03 18:59:57', '15000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(19, 8, 2, '2025-12-03 18:59:57', '22000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(20, 9, 2, '2025-12-03 18:59:57', '6500.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(21, 10, 2, '2025-12-03 18:59:57', '8000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(22, 11, 2, '2025-12-03 18:59:57', '9500.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(23, 9, 2, '2025-12-03 18:59:57', '6500.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL),
(24, 10, 2, '2025-12-03 18:59:57', '8000.00', 'Pending', 'Pending', '123 Test Street, Manila', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_page`
--

CREATE TABLE `order_page` (
  `OrderPageID` int(11) NOT NULL,
  `OrderID` varchar(50) DEFAULT NULL,
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
  `Status` enum('Pending Review','Awaiting Admin','Ready to Approve') DEFAULT 'Pending Review',
  `Customer_ID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_ID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Payment_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Transaction_ID` varchar(100) DEFAULT NULL,
  `Status` enum('Pending','Paid','Failed','Refunded') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pending_review_orders`
--

CREATE TABLE `pending_review_orders` (
  `PendingOrderID` int(11) NOT NULL,
  `OrderID` varchar(50) DEFAULT NULL,
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
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status` enum('In Stock','Out of Stock','Low Stock') DEFAULT 'Out of Stock'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`Product_ID`, `ProductName`, `Category`, `Material`, `Price`, `ImageUrl`, `DateAdded`, `Status`) VALUES
(1, 'test', 'sliding-windows', 'Aluminum', '2000.00', '94238694b5d7a113af9ad379b1ef0fc2.jpg', '2025-11-19 17:15:34', ''),
(2, 'Round Mirror', 'mirrors', 'Glass', '100.00', '82fd096bc469ff4d4ee82fb915dc9948.jpg', '2025-11-20 18:25:01', '');

-- --------------------------------------------------------

--
-- Table structure for table `product_materials`
--

CREATE TABLE `product_materials` (
  `ProductMaterialID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `InventoryItemID` int(11) NOT NULL,
  `QuantityRequired` decimal(10,2) NOT NULL COMMENT 'Amount of material needed per product unit',
  `Unit` varchar(50) DEFAULT NULL COMMENT 'Unit of measurement',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_materials`
--

INSERT INTO `product_materials` (`ProductMaterialID`, `Product_ID`, `InventoryItemID`, `QuantityRequired`, `Unit`, `Created_Date`) VALUES
(1, 1, 10, '1.00', 'sqm', '2025-12-03 19:36:04'),
(2, 1, 24, '2.00', 'meter', '2025-12-03 19:36:04'),
(3, 1, 25, '0.50', 'rolls', '2025-12-03 19:36:04'),
(4, 2, 10, '1.00', 'sqm', '2025-12-03 19:36:04'),
(5, 2, 24, '2.00', 'meter', '2025-12-03 19:36:04'),
(6, 2, 25, '0.50', 'rolls', '2025-12-03 19:36:04'),
(7, 3, 10, '1.00', 'sqm', '2025-12-03 19:36:04'),
(8, 3, 24, '2.00', 'meter', '2025-12-03 19:36:04'),
(9, 3, 25, '0.50', 'rolls', '2025-12-03 19:36:04'),
(10, 4, 8, '2.50', 'sqm', '2025-12-03 19:36:04'),
(11, 4, 2, '4.00', 'pcs', '2025-12-03 19:36:04'),
(12, 4, 4, '2.00', 'tubes', '2025-12-03 19:36:04'),
(13, 4, 19, '1.00', 'sets', '2025-12-03 19:36:04'),
(14, 4, 5, '1.50', 'meter', '2025-12-03 19:36:04'),
(15, 5, 8, '2.50', 'sqm', '2025-12-03 19:36:04'),
(16, 5, 2, '4.00', 'pcs', '2025-12-03 19:36:04'),
(17, 5, 4, '2.00', 'tubes', '2025-12-03 19:36:04'),
(18, 5, 19, '1.00', 'sets', '2025-12-03 19:36:04'),
(19, 5, 5, '1.50', 'meter', '2025-12-03 19:36:04'),
(20, 6, 8, '2.50', 'sqm', '2025-12-03 19:36:04'),
(21, 6, 2, '4.00', 'pcs', '2025-12-03 19:36:04'),
(22, 6, 4, '2.00', 'tubes', '2025-12-03 19:36:04'),
(23, 6, 19, '1.00', 'sets', '2025-12-03 19:36:04'),
(24, 6, 5, '1.50', 'meter', '2025-12-03 19:36:04'),
(25, 7, 8, '3.00', 'sqm', '2025-12-03 19:36:04'),
(26, 7, 12, '6.00', 'meter', '2025-12-03 19:36:04'),
(27, 7, 16, '2.00', 'meter', '2025-12-03 19:36:04'),
(28, 7, 21, '4.00', 'pcs', '2025-12-03 19:36:04'),
(29, 7, 4, '1.00', 'tubes', '2025-12-03 19:36:04'),
(30, 8, 8, '3.00', 'sqm', '2025-12-03 19:36:04'),
(31, 8, 12, '6.00', 'meter', '2025-12-03 19:36:04'),
(32, 8, 16, '2.00', 'meter', '2025-12-03 19:36:04'),
(33, 8, 21, '4.00', 'pcs', '2025-12-03 19:36:04'),
(34, 8, 4, '1.00', 'tubes', '2025-12-03 19:36:04'),
(35, 9, 8, '3.00', 'sqm', '2025-12-03 19:36:04'),
(36, 9, 12, '6.00', 'meter', '2025-12-03 19:36:04'),
(37, 9, 16, '2.00', 'meter', '2025-12-03 19:36:04'),
(38, 9, 21, '4.00', 'pcs', '2025-12-03 19:36:04'),
(39, 9, 4, '1.00', 'tubes', '2025-12-03 19:36:04'),
(40, 10, 8, '2.00', 'sqm', '2025-12-03 19:36:04'),
(41, 10, 2, '3.00', 'pcs', '2025-12-03 19:36:04'),
(42, 10, 17, '1.00', 'sets', '2025-12-03 19:36:04'),
(43, 10, 18, '1.00', 'pcs', '2025-12-03 19:36:04'),
(44, 10, 4, '1.00', 'tubes', '2025-12-03 19:36:04'),
(45, 11, 8, '2.00', 'sqm', '2025-12-03 19:36:04'),
(46, 11, 2, '3.00', 'pcs', '2025-12-03 19:36:04'),
(47, 11, 17, '1.00', 'sets', '2025-12-03 19:36:04'),
(48, 11, 18, '1.00', 'pcs', '2025-12-03 19:36:04'),
(49, 11, 4, '1.00', 'tubes', '2025-12-03 19:36:04'),
(50, 12, 8, '2.00', 'sqm', '2025-12-03 19:36:04'),
(51, 12, 2, '3.00', 'pcs', '2025-12-03 19:36:04'),
(52, 12, 17, '1.00', 'sets', '2025-12-03 19:36:04'),
(53, 12, 18, '1.00', 'pcs', '2025-12-03 19:36:04'),
(54, 12, 4, '1.00', 'tubes', '2025-12-03 19:36:04');

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
  `Status` enum('Scheduled','In progress','Completed','Delayed') DEFAULT NULL
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
  `OrderID` varchar(50) DEFAULT NULL,
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
  `Icon` varchar(50) NOT NULL COMMENT 'Font Awesome icon class (e.g., fa-box-open, fa-user-tie, fa-shopping-cart)',
  `Role` varchar(50) NOT NULL COMMENT 'System, Client/Customer, Admin, Inventory Officer, Sales Representative',
  `Description` text NOT NULL COMMENT 'Notification message/description',
  `Status` enum('Unread','Read') DEFAULT 'Unread' COMMENT 'Notification read status',
  `Created_Date` datetime NOT NULL DEFAULT current_timestamp(),
  `Read_Date` datetime DEFAULT NULL COMMENT 'When notification was marked as read',
  `RelatedID` int(11) DEFAULT NULL COMMENT 'Related OrderID, IssueID, InventoryItemID, etc.',
  `RelatedType` varchar(50) DEFAULT NULL COMMENT 'Order, Issue, Inventory, Payment, etc.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_notif`
--

INSERT INTO `sales_notif` (`NotificationID`, `Icon`, `Role`, `Description`, `Status`, `Created_Date`, `Read_Date`, `RelatedID`, `RelatedType`) VALUES
(1, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI001 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Unread', '2025-12-03 22:44:20', NULL, NULL, 'Order'),
(2, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI002 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Unread', '2025-12-03 22:44:34', NULL, NULL, 'Order'),
(3, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI006 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Unread', '2025-12-03 22:44:51', NULL, NULL, 'Order'),
(4, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI003 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Unread', '2025-12-03 22:45:28', NULL, NULL, 'Order'),
(5, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI005 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Unread', '2025-12-03 22:50:32', NULL, 5, 'Order'),
(6, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI007 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Unread', '2025-12-03 22:54:12', NULL, 7, 'Order');

-- --------------------------------------------------------

--
-- Table structure for table `shower_enclosure_customization`
--

CREATE TABLE `shower_enclosure_customization` (
  `CustomizationID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `OrderID` varchar(50) DEFAULT NULL,
  `Dimensions` varchar(255) DEFAULT NULL COMMENT 'Height x Width',
  `GlassType` varchar(50) DEFAULT NULL COMMENT 'same as default',
  `GlassThickness` varchar(50) DEFAULT NULL COMMENT '6mm, 8mm, 10mm, 12mm',
  `FrameType` varchar(50) DEFAULT NULL COMMENT 'Framed, Semi-Frameless, Frameless',
  `Engraving` varchar(255) DEFAULT NULL COMMENT 'optional',
  `DoorOperation` varchar(50) DEFAULT NULL COMMENT 'Swing, Sliding, Fixed',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shower_enclosure_customization`
--

INSERT INTO `shower_enclosure_customization` (`CustomizationID`, `Customer_ID`, `Product_ID`, `OrderID`, `Dimensions`, `GlassType`, `GlassThickness`, `FrameType`, `Engraving`, `DoorOperation`, `EstimatePrice`, `Created_Date`) VALUES
(1, 5, 4, 'GI006', '72\" x 36\"', 'Tempered', '8mm', 'Framed', 'N/A', 'Swing', '8500.00', '2025-12-03 18:45:39'),
(2, 6, 5, 'GI007', '78\" x 42\"', 'Tempered', '10mm', 'Semi-Frameless', 'N/A', 'Sliding', '12000.00', '2025-12-03 18:45:39'),
(3, 7, 6, 'GI008', '80\" x 48\"', 'Tempered', '12mm', 'Frameless', 'Custom Pattern', 'Fixed', '15000.00', '2025-12-03 18:45:39'),
(4, 5, 4, 'GI009', '72\" x 36\"', 'Tempered', '8mm', 'Framed', 'N/A', 'Swing', '8500.00', '2025-12-03 18:47:25'),
(5, 6, 5, 'GI010', '78\" x 42\"', 'Tempered', '10mm', 'Semi-Frameless', 'N/A', 'Sliding', '12000.00', '2025-12-03 18:47:25'),
(6, 7, 6, NULL, '80\" x 48\"', 'Tempered', '12mm', 'Frameless', 'Custom Pattern', 'Fixed', '15000.00', '2025-12-03 18:47:25'),
(7, 8, 4, NULL, '70\" x 32\"', 'Tempered', '6mm', 'Framed', 'N/A', 'Swing', '7500.00', '2025-12-03 18:47:25'),
(8, 9, 5, NULL, '76\" x 40\"', 'Tempered', '8mm', 'Semi-Frameless', 'N/A', 'Sliding', '11000.00', '2025-12-03 18:47:25'),
(9, 10, 6, NULL, '84\" x 50\"', 'Tempered', '10mm', 'Frameless', 'N/A', 'Fixed', '16000.00', '2025-12-03 18:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `system_activity_log`
--

CREATE TABLE `system_activity_log` (
  `ActivityID` int(11) NOT NULL,
  `Action` varchar(50) NOT NULL COMMENT 'Info, Success, Error, Warning',
  `Description` text NOT NULL,
  `Role` varchar(50) DEFAULT NULL COMMENT 'Client, Staff, Admin, System',
  `UserID` int(11) DEFAULT NULL COMMENT 'User who performed the action',
  `UserName` varchar(100) DEFAULT NULL COMMENT 'Name of the user',
  `Timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `RelatedID` int(11) DEFAULT NULL COMMENT 'Related OrderID, IssueID, etc.',
  `RelatedType` varchar(50) DEFAULT NULL COMMENT 'Order, Issue, Inventory, Payment, etc.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_activity_log`
--

INSERT INTO `system_activity_log` (`ActivityID`, `Action`, `Description`, `Role`, `UserID`, `UserName`, `Timestamp`, `RelatedID`, `RelatedType`) VALUES
(1, 'Approval Requested', 'Order GI001 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Sales Representative', 2, 'Asdd Sales', '2025-12-03 22:44:20', NULL, 'Order'),
(2, 'Approval Requested', 'Order GI002 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Sales Representative', 2, 'Asdd Sales', '2025-12-03 22:44:34', NULL, 'Order'),
(3, 'Approval Requested', 'Order GI006 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Sales Representative', 2, 'Asdd Sales', '2025-12-03 22:44:51', NULL, 'Order'),
(4, 'Approval Requested', 'Order GI003 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Sales Representative', 2, 'Asdd Sales', '2025-12-03 22:45:28', NULL, 'Order'),
(5, 'Approval Requested', 'Order GI005 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Sales Representative', 2, 'Asdd Sales', '2025-12-03 22:50:32', 5, 'Order'),
(6, 'Approval Requested', 'Order GI007 approval has been requested by Asdd Sales. Order is now awaiting admin review.', 'Sales Representative', 2, 'Asdd Sales', '2025-12-03 22:54:12', 7, 'Order');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Middle_Name` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `PhoneNum` varchar(13) NOT NULL,
  `Role` enum('Admin','Sales Representative','Inventory Officer','Customer') NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `ImageUrl` varchar(255) DEFAULT NULL,
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `Date_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `First_Name`, `Last_Name`, `Middle_Name`, `Email`, `Password`, `PhoneNum`, `Role`, `Status`, `ImageUrl`, `Date_Created`, `Date_Updated`) VALUES
(1, 'Aro', 'Manantan', 'M.', 'manantan.aro@gmail.com', '$2y$10$dgpdMNnIdhWCa.z9iJ3bF.pvZcvPmv10/JoY3Uwiboqwb2Y906nMy', '09937568015', 'Customer', 'Active', 'uploads/profile/profile_1.jpg', '2025-11-26 04:39:15', '2025-12-04 04:54:13'),
(2, 'Sales', 'Representative', '', 'sales.rep@gmail.com', '$2y$10$53q803WjTa4Ru7Tgnc3RD.gdDjLiT0Ff5CMg8uEI1vzSenBKsBEU.', '09937568015', 'Sales Representative', 'Active', NULL, '2025-12-01 10:47:15', '2025-12-01 10:51:10'),
(3, 'Admin', 'Log', '', 'admin.log@gmail.com', '$2y$10$lqgRqt8dQyMO23xzfXC7JuONanYPVgmpyxWTFrQ.yfglQFHWVHk1e', '09937568015', 'Admin', 'Active', NULL, '2025-12-01 10:49:13', '2025-12-01 10:50:38'),
(4, 'Inventory', 'Manager', '', 'inv.manager@gmail.com', '$2y$10$E4qL5JBKyCzMucAhF9Sxm.7aYiSam693epAEKLua4JO7Kjn.cb/LC', '09937568015', 'Inventory Officer', 'Active', NULL, '2025-12-01 10:50:13', '2025-12-01 10:51:20');

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `AddressID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `AddressLine` varchar(255) NOT NULL,
  `City` varchar(100) NOT NULL,
  `Province` varchar(100) NOT NULL,
  `Country` varchar(100) NOT NULL,
  `ZipCode` varchar(20) NOT NULL,
  `Note` varchar(255) DEFAULT NULL,
  `AddressType` enum('Shipping','Billing') NOT NULL DEFAULT 'Shipping',
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `Date_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aluminum_bathroom_doors_customization`
--
ALTER TABLE `aluminum_bathroom_doors_customization`
  ADD PRIMARY KEY (`CustomizationID`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `aluminum_doors_customization`
--
ALTER TABLE `aluminum_doors_customization`
  ADD PRIMARY KEY (`CustomizationID`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`Appointment_ID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `Admin_ID` (`Admin_ID`),
  ADD KEY `Customer_ID` (`Customer_ID`);

--
-- Indexes for table `approved_orders`
--
ALTER TABLE `approved_orders`
  ADD PRIMARY KEY (`ApprovedOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`),
  ADD KEY `idx_payment_status` (`PaymentStatus`);

--
-- Indexes for table `awaiting_admin_orders`
--
ALTER TABLE `awaiting_admin_orders`
  ADD PRIMARY KEY (`AwaitingOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`Cart_ID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`Customer_ID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `customization`
--
ALTER TABLE `customization`
  ADD PRIMARY KEY (`CustomizationID`);

--
-- Indexes for table `disapproved_orders`
--
ALTER TABLE `disapproved_orders`
  ADD PRIMARY KEY (`DisapprovedOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`Inventory_ID`),
  ADD KEY `Product_ID` (`Product_ID`),
  ADD KEY `UpdatedBy` (`UpdatedBy`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`InventoryItemID`),
  ADD UNIQUE KEY `ItemID` (`ItemID`),
  ADD UNIQUE KEY `ItemID_unique` (`ItemID`),
  ADD KEY `Category` (`Category`),
  ADD KEY `Status` (`Status`);

--
-- Indexes for table `inventory_notifications`
--
ALTER TABLE `inventory_notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `InventoryItemID` (`InventoryItemID`),
  ADD KEY `Status` (`Status`);

--
-- Indexes for table `issuereport`
--
ALTER TABLE `issuereport`
  ADD PRIMARY KEY (`Issue_ID`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `Order_ID` (`Order_ID`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `SalesRep_ID` (`SalesRep_ID`);

--
-- Indexes for table `order_page`
--
ALTER TABLE `order_page`
  ADD PRIMARY KEY (`OrderPageID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Payment_ID`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Indexes for table `pending_review_orders`
--
ALTER TABLE `pending_review_orders`
  ADD PRIMARY KEY (`PendingOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`Product_ID`);

--
-- Indexes for table `projectschedule`
--
ALTER TABLE `projectschedule`
  ADD PRIMARY KEY (`Schedule_ID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `Admin_ID` (`Admin_ID`);

--
-- Indexes for table `quotation`
--
ALTER TABLE `quotation`
  ADD PRIMARY KEY (`QuotationID`),
  ADD UNIQUE KEY `Quotation_num` (`Quotation_num`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`AddressID`),
  ADD KEY `UserID` (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `Appointment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `Cart_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `Customer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `customization`
--
ALTER TABLE `customization`
  MODIFY `CustomizationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `Inventory_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issuereport`
--
ALTER TABLE `issuereport`
  MODIFY `Issue_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `AddressID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`),
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`Admin_ID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `appointment_ibfk_3` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`);

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`UpdatedBy`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `issuereport`
--
ALTER TABLE `issuereport`
  ADD CONSTRAINT `issuereport_ibfk_1` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`),
  ADD CONSTRAINT `issuereport_ibfk_2` FOREIGN KEY (`Order_ID`) REFERENCES `order` (`OrderID`);

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`),
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`);

--
-- Constraints for table `projectschedule`
--
ALTER TABLE `projectschedule`
  ADD CONSTRAINT `projectschedule_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`),
  ADD CONSTRAINT `projectschedule_ibfk_2` FOREIGN KEY (`Admin_ID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `quotation`
--
ALTER TABLE `quotation`
  ADD CONSTRAINT `quotation_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`);

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `user_address_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
