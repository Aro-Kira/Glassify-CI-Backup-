-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2025 at 05:04 PM
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
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL COMMENT 'Action type: Stock added, Stock reduced, Item created, etc.',
  `item_name` varchar(255) DEFAULT NULL COMMENT 'Name of the item affected',
  `change_description` varchar(255) DEFAULT NULL COMMENT 'Brief description of change (e.g., +20 pieces, -5 sheets)',
  `description` text DEFAULT NULL COMMENT 'Detailed description or reason',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL COMMENT 'User who performed the action',
  `InventoryItemID` int(11) DEFAULT NULL COMMENT 'Reference to inventory_items table'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aluminum_bathroom_doors_customization`
--

CREATE TABLE `aluminum_bathroom_doors_customization` (
  `CustomizationID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL,
  `FrameType` varchar(50) DEFAULT NULL,
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aluminum_doors_customization`
--

CREATE TABLE `aluminum_doors_customization` (
  `CustomizationID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL,
  `GlassType` varchar(50) DEFAULT NULL COMMENT 'same as default',
  `GlassThickness` varchar(50) DEFAULT NULL COMMENT '6mm, 10mm',
  `Configuration` varchar(50) DEFAULT NULL COMMENT '2-panel slider, 3-panel slider, 4-panel slider',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `AppointmentID` int(11) NOT NULL,
  `OrderID` varchar(50) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `ClientName` varchar(255) DEFAULT NULL,
  `Service` enum('Order Placed','Ocular Visit','In Fabrication','Installed','Completed') DEFAULT 'Order Placed',
  `AppointmentDate` date DEFAULT NULL,
  `AppointmentTime` time DEFAULT NULL,
  `AssignedStaff` varchar(255) DEFAULT NULL,
  `Status` enum('In Progress','Complete','Cancelled') DEFAULT 'In Progress',
  `Notes` text DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `OrderID`, `Customer_ID`, `ProductName`, `ClientName`, `Service`, `AppointmentDate`, `AppointmentTime`, `AssignedStaff`, `Status`, `Notes`, `Created_Date`, `Updated_Date`) VALUES
(1, 'GI009', 1, 'N/A', 'Genesis Olalo Dimaculangan', 'Order Placed', '2025-12-05', '10:00:00', 'Engr. Sushmita Sen', 'Cancelled', '', '2025-12-04 23:58:39', '2025-12-04 19:00:58'),
(2, 'GI010', 1, 'N/A', 'Genesis Olalo Dimaculangan', 'In Fabrication', '2025-12-10', '10:00:00', 'Engr. Maria Samgon', 'In Progress', '', '2025-12-05 00:07:40', '2025-12-04 17:08:45'),
(3, 'GI011', 1, 'N/A', 'Genesis Olalo Dimaculangan', 'Order Placed', '2025-12-26', '10:00:00', '', 'In Progress', '', '2025-12-05 00:31:33', '2025-12-04 17:36:49'),
(4, 'GI012', 2, 'N/A', 'Jehana C. Dandelion', 'Ocular Visit', '2025-12-05', '10:00:00', '', 'In Progress', '', '2025-12-05 01:52:49', '2025-12-04 18:52:59'),
(5, 'GI014', 2, 'N/A', 'Jehana C. Dandelion', 'Ocular Visit', '2025-12-05', '10:00:00', '', 'In Progress', '', '2025-12-05 02:24:26', '2025-12-04 19:24:47');

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

--
-- Dumping data for table `approved_orders`
--

INSERT INTO `approved_orders` (`ApprovedOrderID`, `OrderID`, `ProductName`, `Address`, `OrderDate`, `Shape`, `Dimension`, `Type`, `Thickness`, `EdgeWork`, `FrameType`, `Engraving`, `FileAttached`, `TotalQuotation`, `Customer_ID`, `SalesRep_ID`, `ApprovedBy_SalesRep_ID`, `Approved_Date`, `CustomerNotified`, `CustomerNotified_Date`, `PaymentMethod`, `PaymentStatus`, `Created_Date`, `Updated_Date`) VALUES
(1, 'GI009', 'N/A', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', '2025-12-05 00:35:04', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '0.00', 1, 5, 5, '2025-12-05 00:38:56', 1, '2025-12-05 00:38:56', NULL, 'Pending', '2025-12-04 23:38:56', '2025-12-04 23:38:56'),
(2, 'GI010', 'N/A', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', '2025-12-05 01:03:34', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 1, 5, 5, '2025-12-05 01:07:09', 1, '2025-12-05 01:07:09', NULL, 'Pending', '2025-12-05 00:07:09', '2025-12-05 00:07:09'),
(3, 'GI011', 'N/A', '7 Sahalamas St., Genaville Subd., Brgy. Marlika, Quezon City, Nueva Ecija', '2025-12-05 01:18:55', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 1, 5, 5, '2025-12-05 01:31:15', 1, '2025-12-05 01:31:15', NULL, 'Pending', '2025-12-05 00:31:15', '2025-12-05 00:31:15'),
(4, 'GI012', 'N/A', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', '2025-12-05 02:49:07', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '35.00', 2, 5, 5, '2025-12-05 02:52:22', 0, NULL, NULL, 'Pending', '2025-12-05 01:52:22', NULL),
(5, 'GI014', 'N/A', '7 Santa Ana St.,, Quezon City, Manila', '2025-12-05 03:19:39', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '35.00', 2, 5, 5, '2025-12-05 03:24:00', 0, NULL, NULL, 'Pending', '2025-12-05 02:24:00', NULL);

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

--
-- Dumping data for table `awaiting_admin_orders`
--

INSERT INTO `awaiting_admin_orders` (`AwaitingOrderID`, `OrderID`, `ProductName`, `Address`, `OrderDate`, `Shape`, `Dimension`, `Type`, `Thickness`, `EdgeWork`, `FrameType`, `Engraving`, `FileAttached`, `TotalQuotation`, `Customer_ID`, `SalesRep_ID`, `RequestedBy_SalesRep_ID`, `Requested_Date`, `Created_Date`, `Updated_Date`) VALUES
(3, 'GI008', 'N/A', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', '2025-12-05 00:29:57', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '0.00', 1, 5, 5, '2025-12-05 01:06:58', '2025-12-05 00:06:58', NULL);

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
(0, 10, 1, 9, 1, '2025-12-05 00:03:13'),
(0, 11, 2, 5, 1, '2025-12-05 01:46:24'),
(0, 1, 1, 30, 1, '2025-12-07 15:57:10'),
(0, 1, 2, 13, 1, '2025-12-07 15:57:42'),
(0, 1, 4, 31, 1, '2025-12-07 16:01:39');

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
(1, 10),
(2, 11);

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
  `PriceBreakdown` text DEFAULT NULL COMMENT 'JSON string containing price breakdown details',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customization`
--

INSERT INTO `customization` (`CustomizationID`, `Customer_ID`, `Product_ID`, `Dimensions`, `GlassShape`, `GlassType`, `GlassThickness`, `EdgeWork`, `FrameType`, `Engraving`, `DesignRef`, `EstimatePrice`, `PriceBreakdown`, `CreatedAt`, `UpdatedAt`, `Created_Date`) VALUES
(29, 1, 1, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/wishlist_design_1_1765123020_6935a3ccd80bd.png', '0.00', NULL, '2025-12-07 15:57:00', '2025-12-07 15:57:00', '2025-12-07 15:57:00'),
(30, 1, 1, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/wishlist_design_1_1765123020_6935a3ccd80bd.png', '0.00', NULL, '2025-12-07 15:57:10', '2025-12-07 15:57:10', '2025-12-07 15:57:10'),
(31, 1, 4, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1765123299_6935a4e35a24a.png', '23625.00', NULL, '2025-12-07 16:01:39', '2025-12-07 16:01:39', '2025-12-07 16:01:39');

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
(1, 1, 'Aro', 'Manantan', 'M.', 'manantan.aro@gmail.com', '09937568015', 'Active', '2025-11-26 04:39:15', '2025-12-06 19:33:29', NULL),
(2, 3, 'Margarette', 'Soberano', 'Batumbakal', 'hernameismarga@gmail.com', '09123456789', 'Active', '2025-11-30 07:41:30', '2025-12-06 19:33:29', NULL),
(3, 6, 'Shalltear', 'Bloodfallen', '', 'putodinuguan@gmail.com', '09954756382', 'Active', '2025-12-03 07:47:47', '2025-12-06 19:33:29', NULL),
(4, 10, 'Genesis', 'Dimaculangan', 'Olalo', 'lerumgops@gmail.com', '09687645377', 'Active', '2025-12-04 21:21:08', '2025-12-06 19:33:29', NULL),
(5, 11, 'Jehana', 'Dandelion', 'C.', 'jehana@gmail.com', '09876543210', 'Active', '2025-12-05 01:43:42', '2025-12-05 01:43:42', NULL);

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
  `min_threshold` int(11) DEFAULT 10 COMMENT 'Minimum stock level for low stock alert',
  `Unit` varchar(50) NOT NULL COMMENT 'sqm, pcs, tubes, meter, sets, etc.',
  `Status` enum('In Stock','Low Stock','Out of Stock','New') DEFAULT 'In Stock',
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `DateUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`InventoryItemID`, `ItemID`, `Name`, `Category`, `InStock`, `min_threshold`, `Unit`, `Status`, `DateAdded`, `DateUpdated`) VALUES
(1, 'GL-001', 'Tempered Glass', 'Glass', 150, 10, 'sqm', 'In Stock', '2025-12-03 22:28:12', '2025-12-07 02:32:35'),
(2, 'AL-022', 'Aluminum Frame', 'Aluminum', 10, 10, 'pcs', 'In Stock', '2025-12-03 22:28:12', '2025-12-07 02:32:35'),
(3, 'GL-002', 'Laminated Glass', 'Glass', 120, 10, 'sqm', 'In Stock', '2025-12-03 22:28:12', '2025-12-07 02:32:35'),
(4, 'AC-003', 'Silicone Sealant', 'Accessories', 200, 10, 'tubes', 'In Stock', '2025-12-03 22:28:12', '2025-12-07 02:32:35'),
(5, 'AL-045', 'Sliding Track', 'Aluminum', 80, 10, 'meter', 'In Stock', '2025-12-03 22:28:12', '2025-12-07 02:32:35'),
(6, 'HD-007', 'Handle Set', 'Hardware', 2, 10, 'sets', 'Low Stock', '2025-12-03 22:28:12', '2025-12-07 02:32:35');

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
  `Report_Date` datetime DEFAULT NULL,
  `Status` enum('Open','Resolved') DEFAULT 'Open',
  `Priority` enum('Low','Medium','High') DEFAULT 'Low'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issuereport`
--

INSERT INTO `issuereport` (`Issue_ID`, `Customer_ID`, `Order_ID`, `First_Name`, `Last_Name`, `Email`, `PhoneNum`, `Category`, `Description`, `Report_Date`, `Status`, `Priority`) VALUES
(2, NULL, NULL, 'Ahtisa', 'Manalo', 'lerumgops@gmail.com', '091234567891', 'Delivery Issue', 'Hanggang ngayon di pa rin na-deliver yung mirror namin. Please update ASAP.', '2025-12-03 08:35:49', 'Resolved', 'Low'),
(3, NULL, NULL, 'Lawliet', 'L', 'lawliet@gmail.com', '09328765983', 'Order Issue', 'Di ako makaorder sa website niyong static pages pa lang', '2025-12-03 08:45:21', 'Resolved', 'Low'),
(4, NULL, NULL, 'Shalltear', 'Bloodfallen', 'putodinuguan@gmail.com', '09328765983', 'Installation Problems', 'Koya natanggal Koya natanggal siya hala oich!', '2025-12-03 09:13:11', 'Open', 'High'),
(5, NULL, NULL, 'Catriona', 'Pink', 'lavawalk@gmail.com', '09985475396', 'General Inquiry', 'I work a lot in the slums of Tondo, Manila', '2025-12-03 22:32:58', 'Open', 'Low'),
(6, NULL, NULL, 'Usertesting', 'testinguser', 'testingngani@gmail.com', '09746386447', 'Payment Issue', 'sana all sana all sana all sana all', '2025-12-03 23:14:45', 'Open', 'Low'),
(7, NULL, NULL, 'Merlyn', 'Samario', 'merlynsamario@gmail.com', '09875347654', 'Product Defect/Damage', 'asdsadsadsadsadsadsadsad', '2025-12-04 00:56:43', 'Open', 'Low'),
(8, NULL, NULL, 'adsad', 'sadsad', 'hernameismarga@gmail.com', '09127452342', 'Delivery Issue', 'asdsadsadlkngkgnelwknknlnooosddf', '2025-12-04 02:52:42', 'Resolved', 'High'),
(9, 1, NULL, 'Genesis', 'Dimaculangan', 'lerumgops@gmail.com', '09328765983', 'Order Issue', 'asadsadsadsadsadsadasasfsafsa', '2025-12-04 23:49:59', 'Open', 'Low');

-- --------------------------------------------------------

--
-- Table structure for table `mirror_customization`
--

CREATE TABLE `mirror_customization` (
  `CustomizationID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL COMMENT 'Height x Width (or Diameter for Circle)',
  `EdgeWork` varchar(50) DEFAULT NULL COMMENT 'polished, beveled, same lang',
  `GlassShape` varchar(50) DEFAULT NULL COMMENT 'Rectangle, Circle, Oval, Arch, Capsule',
  `LEDBacklight` varchar(50) DEFAULT NULL COMMENT 'Optional',
  `Engraving` varchar(255) DEFAULT NULL COMMENT 'Optional',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(5, 1, 5, '2025-12-04 22:31:36', '548.00', 'Pending', 'Pending', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', NULL, 'uploads/payments/Aluminum_Kitchen_Home1.png', NULL),
(6, 1, 5, '2025-12-04 22:47:53', '548.00', 'Pending', 'Pending', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', NULL, 'uploads/payments/Aluminum_Kitchen_Home2.png', NULL),
(7, 1, 5, '2025-12-04 23:22:07', '548.00', 'Pending', 'Pending', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', NULL, 'uploads/payments/Aluminum_Kitchen_Home3.png', NULL),
(8, 1, 5, '2025-12-04 23:29:57', '0.00', 'Pending', 'Pending', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', NULL, 'uploads/payments/Windows_Home.png', NULL),
(9, 1, 5, '2025-12-04 23:35:04', '0.00', 'Pending', 'Pending', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', NULL, 'uploads/payments/Windows_Home1.png', NULL),
(10, 1, 5, '2025-12-05 00:03:34', '42.00', 'Pending', 'Pending', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', NULL, NULL, NULL),
(11, 1, 5, '2025-12-05 00:18:55', '42.00', 'Pending', 'Pending', '7 Sahalamas St., Genaville Subd., Brgy. Marlika, Quezon City, Nueva Ecija', 'Preferred Installation Date: December 26, 2025', NULL, NULL),
(12, 2, 5, '2025-12-05 01:49:07', '35.00', 'Pending', 'Pending', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', NULL, 'uploads/payments/Windows_Home2.png', NULL),
(13, 2, 5, '2025-12-05 02:19:11', '35.00', 'Pending', 'Pending', '7 Santa Ana St.,, Quezon City, Manila', NULL, 'uploads/payments/Windows_Home3.png', NULL),
(14, 2, 5, '2025-12-05 02:19:39', '35.00', 'Pending', 'Pending', '7 Santa Ana St.,, Quezon City, Manila', NULL, 'uploads/payments/Windows_Home4.png', NULL),
(15, 1, 5, '2025-12-07 10:47:30', '42.00', 'Pending', 'Pending', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', NULL, 'uploads/payments/Windows_Home5.png', NULL),
(16, 1, 5, '2025-12-07 10:50:52', '42.00', 'Pending', 'Pending', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', NULL, 'uploads/payments/Glass_Aluminum_Home.png', NULL),
(17, 1, 5, '2025-12-07 10:53:04', '42.00', 'Pending', 'Pending', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', NULL, 'uploads/payments/Glass_Aluminum_Home1.png', NULL),
(18, 1, 5, '2025-12-07 10:53:05', '42.00', 'Pending', 'Pending', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', NULL, 'uploads/payments/Glass_Aluminum_Home2.png', NULL),
(19, 1, 5, '2025-12-07 10:53:05', '42.00', 'Pending', 'Pending', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', NULL, 'uploads/payments/Glass_Aluminum_Home3.png', NULL),
(20, 1, 5, '2025-12-07 10:53:09', '42.00', 'Pending', 'Pending', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', NULL, 'uploads/payments/Glass_Aluminum_Home4.png', NULL),
(21, 1, 5, '2025-12-07 11:38:00', '42.00', 'Pending', 'Pending', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', NULL, 'uploads/payments/Glass_Aluminum_Home5.png', NULL);

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

--
-- Dumping data for table `order_page`
--

INSERT INTO `order_page` (`OrderPageID`, `OrderID`, `ProductName`, `Address`, `OrderDate`, `Shape`, `Dimension`, `Type`, `Thickness`, `EdgeWork`, `FrameType`, `Engraving`, `FileAttached`, `TotalQuotation`, `Status`, `Customer_ID`, `SalesRep_ID`, `Created_Date`, `Updated_Date`) VALUES
(1, 'GI008', 'N/A', '3 John St., Sehana Subd., Brgy. Lito, Quezon City, Manila', '2025-12-05 00:29:57', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '0.00', 'Awaiting Admin', 1, 5, '2025-12-04 23:29:57', '2025-12-05 00:06:58'),
(6, 'GI013', 'N/A', '7 Santa Ana St.,, Quezon City, Manila', '2025-12-05 03:19:11', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '35.00', 'Pending Review', 2, 5, '2025-12-05 02:19:11', NULL),
(8, 'GI015', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:47:30', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 'Pending Review', 1, 5, '2025-12-07 10:47:30', NULL),
(9, 'GI016', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:50:52', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 'Pending Review', 1, 5, '2025-12-07 10:50:52', NULL),
(10, 'GI017', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:53:04', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 'Pending Review', 1, 5, '2025-12-07 10:53:04', NULL),
(11, 'GI018', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:53:05', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 'Pending Review', 1, 5, '2025-12-07 10:53:05', NULL),
(12, 'GI019', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:53:05', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 'Pending Review', 1, 5, '2025-12-07 10:53:05', NULL),
(13, 'GI020', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:53:09', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 'Pending Review', 1, 5, '2025-12-07 10:53:09', NULL),
(14, 'GI021', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 12:38:00', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 'Pending Review', 1, 5, '2025-12-07 11:38:00', NULL);

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
  `Status` enum('Pending','Paid','Failed','Refunded') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Payment_ID`, `OrderID`, `CustomerName`, `ProductName`, `PaymentMethod`, `Amount`, `Payment_Date`, `Transaction_ID`, `ReceiptPath`, `Status`) VALUES
(1, 9, 'Aro Manantan', 'N/A', NULL, '0.00', '2025-12-04 16:38:56', NULL, NULL, 'Pending'),
(2, 10, 'Aro Manantan', 'N/A', NULL, '42.00', '2025-12-04 17:07:09', NULL, NULL, 'Pending'),
(3, 11, 'Aro Manantan', 'N/A', NULL, '42.00', '2025-12-04 17:31:15', NULL, NULL, 'Pending'),
(4, 12, '', 'N/A', NULL, '35.00', '2025-12-04 18:52:22', NULL, NULL, 'Pending'),
(5, 14, '', 'N/A', NULL, '35.00', '2025-12-04 19:24:00', NULL, NULL, 'Pending');

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

--
-- Dumping data for table `pending_review_orders`
--

INSERT INTO `pending_review_orders` (`PendingOrderID`, `OrderID`, `ProductName`, `Address`, `OrderDate`, `Shape`, `Dimension`, `Type`, `Thickness`, `EdgeWork`, `FrameType`, `Engraving`, `FileAttached`, `TotalQuotation`, `Customer_ID`, `SalesRep_ID`, `Created_Date`, `Updated_Date`) VALUES
(6, 'GI013', 'N/A', '7 Santa Ana St.,, Quezon City, Manila', '2025-12-05 03:19:11', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '35.00', 2, 5, '2025-12-05 02:19:11', NULL),
(8, 'GI015', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:47:30', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 1, 5, '2025-12-07 10:47:30', NULL),
(9, 'GI016', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:50:52', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 1, 5, '2025-12-07 10:50:52', NULL),
(10, 'GI017', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:53:04', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 1, 5, '2025-12-07 10:53:04', NULL),
(11, 'GI018', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:53:05', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 1, 5, '2025-12-07 10:53:05', NULL),
(12, 'GI019', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:53:05', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 1, 5, '2025-12-07 10:53:05', NULL),
(13, 'GI020', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 11:53:09', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 1, 5, '2025-12-07 10:53:09', NULL),
(14, 'GI021', 'N/A', '3 Santa Ana St., Senku Subd., Brgy. Naloto, Quezon City, Manila', '2025-12-07 12:38:00', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '42.00', 1, 5, '2025-12-07 11:38:00', NULL);

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
(2, 'Round Mirror', 'mirrors', 'Glass', '100.00', '82fd096bc469ff4d4ee82fb915dc9948.jpg', '2025-11-20 18:25:01', ''),
(4, 'Shower Enclosure', 'shower-enclosure', 'Aluminum', '1500.00', 'b9dc8bbcd270b502ed30c9d4bc7e503d.png', '2025-12-06 20:19:06', '');

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
(1, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI009 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Unread', '2025-12-05 00:36:57', NULL, 9, 'Order'),
(2, 'fa-shopping-cart', 'Sales Representative', 'Order Approved: Order GI009 has been approved by Mhe Samontesa. Customer can now proceed with payment.', 'Unread', '2025-12-05 00:38:56', NULL, 9, 'Order'),
(3, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI010 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Unread', '2025-12-05 01:04:49', NULL, 10, 'Order'),
(4, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI008 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Unread', '2025-12-05 01:06:58', NULL, 8, 'Order'),
(5, 'fa-shopping-cart', 'Sales Representative', 'Order Approved: Order GI010 has been approved by Mhe Samontesa. Customer can now proceed with payment.', 'Unread', '2025-12-05 01:07:09', NULL, 10, 'Order'),
(6, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI011 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Unread', '2025-12-05 01:30:01', NULL, 11, 'Order'),
(7, 'fa-shopping-cart', 'Sales Representative', 'Order Approved: Order GI011 has been approved by Mhe Samontesa. Customer can now proceed with payment.', 'Unread', '2025-12-05 01:31:15', NULL, 11, 'Order'),
(8, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI012 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Unread', '2025-12-05 02:50:32', NULL, 12, 'Order'),
(9, 'fa-shopping-cart', 'Sales Representative', 'Order Approved: Order GI012 has been approved by Mhe Samontesa. Customer can now proceed with payment.', 'Unread', '2025-12-05 02:52:22', NULL, 12, 'Order'),
(10, 'fa-user-tie', 'Sales Representative', 'Approval Requested: Order GI014 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Unread', '2025-12-05 03:21:04', NULL, 14, 'Order'),
(11, 'fa-shopping-cart', 'Sales Representative', 'Order Approved: Order GI014 has been approved by Mhe Samontesa. Customer can now proceed with payment.', 'Unread', '2025-12-05 03:24:00', NULL, 14, 'Order');

-- --------------------------------------------------------

--
-- Table structure for table `shower_enclosure_customization`
--

CREATE TABLE `shower_enclosure_customization` (
  `CustomizationID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL COMMENT 'Height x Width',
  `GlassType` varchar(50) DEFAULT NULL COMMENT 'same as default',
  `GlassThickness` varchar(50) DEFAULT NULL COMMENT '6mm, 8mm, 10mm, 12mm',
  `FrameType` varchar(50) DEFAULT NULL COMMENT 'Framed, Semi-Frameless, Frameless',
  `Engraving` varchar(255) DEFAULT NULL COMMENT 'optional',
  `DoorOperation` varchar(50) DEFAULT NULL COMMENT 'Swing, Sliding, Fixed',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `transaction_id` int(11) NOT NULL,
  `InventoryItemID` int(11) NOT NULL,
  `transaction_type` enum('add','remove','adjust') NOT NULL COMMENT 'Type of transaction',
  `quantity` int(11) NOT NULL COMMENT 'Amount added/removed',
  `reason` text DEFAULT NULL COMMENT 'Reason for stock change',
  `previous_stock` int(11) DEFAULT NULL COMMENT 'Stock level before transaction',
  `new_stock` int(11) DEFAULT NULL COMMENT 'Stock level after transaction',
  `user_id` int(11) DEFAULT NULL COMMENT 'User who made the transaction',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Approval Requested', 'Order GI009 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 00:36:57', 9, 'Order'),
(2, 'Order Approved by Admin', 'Order GI009 has been approved by Albedo Saitama', 'Admin', 7, 'Albedo Saitama', '2025-12-05 00:38:08', 0, 'Order'),
(3, 'Order Approved', 'Order GI009 has been approved by Mhe Samontesa. Customer can now proceed with payment.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 00:38:56', 9, 'Order'),
(4, 'Approval Requested', 'Order GI010 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 01:04:49', 10, 'Order'),
(5, 'Order Approved by Admin', 'Order GI010 has been approved by Albedo Saitama', 'Admin', 7, 'Albedo Saitama', '2025-12-05 01:05:36', 0, 'Order'),
(6, 'Approval Requested', 'Order GI008 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 01:06:58', 8, 'Order'),
(7, 'Order Approved', 'Order GI010 has been approved by Mhe Samontesa. Customer can now proceed with payment.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 01:07:09', 10, 'Order'),
(8, 'Approval Requested', 'Order GI011 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 01:30:01', 11, 'Order'),
(9, 'Order Approved by Admin', 'Order GI011 has been approved by Albedo Saitama', 'Admin', 7, 'Albedo Saitama', '2025-12-05 01:30:45', 0, 'Order'),
(10, 'Order Approved', 'Order GI011 has been approved by Mhe Samontesa. Customer can now proceed with payment.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 01:31:15', 11, 'Order'),
(11, 'Approval Requested', 'Order GI012 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 02:50:32', 12, 'Order'),
(12, 'Order Approved by Admin', 'Order GI012 has been approved by Albedo Saitama', 'Admin', 7, 'Albedo Saitama', '2025-12-05 02:51:31', 0, 'Order'),
(13, 'Order Approved', 'Order GI012 has been approved by Mhe Samontesa. Customer can now proceed with payment.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 02:52:22', 12, 'Order'),
(14, 'Approval Requested', 'Order GI014 approval has been requested by Mhe Samontesa. Order is now awaiting admin review.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 03:21:04', 14, 'Order'),
(15, 'Order Approved by Admin', 'Order GI014 has been approved by Albedo Saitama', 'Admin', 7, 'Albedo Saitama', '2025-12-05 03:22:34', 0, 'Order'),
(16, 'Order Approved', 'Order GI014 has been approved by Mhe Samontesa. Customer can now proceed with payment.', 'Sales Representative', 5, 'Mhe Samontesa', '2025-12-05 03:24:00', 14, 'Order');

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
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `PhoneNum` varchar(13) NOT NULL,
  `Role` enum('Admin','Sales Representative','Inventory Officer','Customer') NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `Date_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `First_Name`, `Last_Name`, `Middle_Name`, `Email`, `Password`, `reset_token`, `reset_token_expiry`, `PhoneNum`, `Role`, `Status`, `Date_Created`, `Date_Updated`) VALUES
(1, 'Aro', 'Manantan', 'M.', 'manantan.aro@gmail.com', '$2y$10$dgpdMNnIdhWCa.z9iJ3bF.pvZcvPmv10/JoY3Uwiboqwb2Y906nMy', NULL, NULL, '09937568015', 'Customer', 'Active', '2025-11-26 04:39:15', '2025-11-26 04:39:15'),
(3, 'Margarette', 'Soberano', 'Batumbakal', 'hernameismarga@gmail.com', '$2y$10$nxOhBu/40S8NhkQoulGeGOWPUQk/2J7xX9QOk6YbKXne8osL0WrnK', NULL, NULL, '09123456789', 'Customer', 'Active', '2025-11-30 07:41:30', '2025-11-30 07:41:30'),
(5, 'Mhe', 'Samontesa', 'Ann Ancheta', 'gopslerum@gmail.com', '$2y$10$n3dVLPa3PXB92GhicoT6QuOixhOO.oqmKMcTzhGW9VHc75AGJ.Fm6', NULL, NULL, '09123595488', 'Sales Representative', 'Active', '2025-12-02 13:41:56', '2025-12-03 18:37:17'),
(6, 'Shalltear', 'Bloodfallen', '', 'putodinuguan@gmail.com', '$2y$10$32.xOCTyBDTvBDknABRtvu5Ch96PQ/g3ugVHOuX/aGpOTv1Lv46Ni', NULL, NULL, '09954756382', 'Customer', 'Active', '2025-12-03 07:47:47', '2025-12-03 07:47:47'),
(7, 'Albedo', 'Saitama', 'Mob', 'lerum.rommeljohnjeric.robles@gmail.com', '$2y$10$efVS850BJ90h5SfoKwpNHe58zohrIrfDXKlCU.YFhTC1Qw7IBVnh6', NULL, NULL, '09768594887', 'Admin', 'Active', '2025-12-04 00:09:25', '2025-12-04 00:09:25'),
(8, 'Hermione', 'Esguerra', '', 'hermione@gmail.com', '$2y$10$SF.GTASkB5Ub2vIEF0s4xOWJiV2h3KBxklLSKtyr5xo73VWzK6JUW', NULL, NULL, '09378657843', 'Sales Representative', 'Active', '2025-12-04 00:28:04', '2025-12-04 00:28:04'),
(9, 'Inigo', 'Sy', 'Calyx', 'calyxstro@gmail.com', '$2y$10$l.cCJFxR3FCpSRNPqlQmf.SXF3vmAhE75MxcIBSfenbdDh.e5zSEy', NULL, NULL, '09867192756', 'Inventory Officer', 'Active', '2025-12-04 00:29:48', '2025-12-04 00:29:48'),
(10, 'Genesis', 'Dimaculangan', 'Olalo', 'lerumgops@gmail.com', '$2y$10$rX8mQUyin.XNyVjmHYlnne5gfiwAwm.FJNMhESKrntkq.PeRLUIf2', NULL, NULL, '09687645377', 'Customer', 'Active', '2025-12-04 21:21:08', '2025-12-04 14:22:54'),
(11, 'Jehana', 'Dandelion', 'C.', 'jehana@gmail.com', '$2y$10$QRV.MrjtgRJQ.Qg.AUbTVufzNTkPtikzp.UYepCySV2N3H0CiCwy.', NULL, NULL, '09876543210', 'Customer', 'Active', '2025-12-05 01:43:42', '2025-12-05 01:43:42');

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
(1, 1, 'Shipping', '35 Malasimbo', 'Quezon City', 'Metro Manila', 'Philippines', '1102', NULL, 0, '2025-12-07 15:44:18', '2025-12-07 15:44:18');

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
  ADD KEY `idx_InventoryItemID` (`InventoryItemID`),
  ADD KEY `idx_user_id` (`user_id`);

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
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`AppointmentID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_service` (`Service`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_date` (`AppointmentDate`);

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
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`Customer_ID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `customization`
--
ALTER TABLE `customization`
  ADD PRIMARY KEY (`CustomizationID`),
  ADD KEY `idx_customer_id` (`Customer_ID`),
  ADD KEY `idx_product_id` (`Product_ID`);

--
-- Indexes for table `disapproved_orders`
--
ALTER TABLE `disapproved_orders`
  ADD PRIMARY KEY (`DisapprovedOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`);

--
-- Indexes for table `enduser`
--
ALTER TABLE `enduser`
  ADD PRIMARY KEY (`EndUser_ID`),
  ADD UNIQUE KEY `UserID` (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

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
  ADD KEY `Status` (`Status`),
  ADD KEY `idx_InStock` (`InStock`),
  ADD KEY `idx_Status` (`Status`),
  ADD KEY `idx_Category` (`Category`);

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
  ADD KEY `Order_ID` (`Order_ID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_priority` (`Priority`);

--
-- Indexes for table `mirror_customization`
--
ALTER TABLE `mirror_customization`
  ADD PRIMARY KEY (`CustomizationID`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

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
-- Indexes for table `product_materials`
--
ALTER TABLE `product_materials`
  ADD PRIMARY KEY (`ProductMaterialID`),
  ADD KEY `Product_ID` (`Product_ID`),
  ADD KEY `InventoryItemID` (`InventoryItemID`);

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
-- Indexes for table `ready_to_approve_orders`
--
ALTER TABLE `ready_to_approve_orders`
  ADD PRIMARY KEY (`ReadyOrderID`),
  ADD KEY `idx_orderid` (`OrderID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_salesrep` (`SalesRep_ID`);

--
-- Indexes for table `sales_notif`
--
ALTER TABLE `sales_notif`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `Status` (`Status`),
  ADD KEY `Role` (`Role`),
  ADD KEY `Created_Date` (`Created_Date`);

--
-- Indexes for table `shower_enclosure_customization`
--
ALTER TABLE `shower_enclosure_customization`
  ADD PRIMARY KEY (`CustomizationID`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_InventoryItemID` (`InventoryItemID`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `system_activity_log`
--
ALTER TABLE `system_activity_log`
  ADD PRIMARY KEY (`ActivityID`),
  ADD KEY `Timestamp` (`Timestamp`),
  ADD KEY `Action` (`Action`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`AddressID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `AddressType` (`AddressType`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`Wishlist_ID`),
  ADD KEY `fk_wishlist_customer` (`Customer_ID`),
  ADD KEY `fk_wishlist_product` (`Product_ID`),
  ADD KEY `fk_wishlist_customization` (`CustomizationID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aluminum_bathroom_doors_customization`
--
ALTER TABLE `aluminum_bathroom_doors_customization`
  MODIFY `CustomizationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aluminum_doors_customization`
--
ALTER TABLE `aluminum_doors_customization`
  MODIFY `CustomizationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `Appointment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `approved_orders`
--
ALTER TABLE `approved_orders`
  MODIFY `ApprovedOrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `awaiting_admin_orders`
--
ALTER TABLE `awaiting_admin_orders`
  MODIFY `AwaitingOrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `Customer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customization`
--
ALTER TABLE `customization`
  MODIFY `CustomizationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `disapproved_orders`
--
ALTER TABLE `disapproved_orders`
  MODIFY `DisapprovedOrderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enduser`
--
ALTER TABLE `enduser`
  MODIFY `EndUser_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `Inventory_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `InventoryItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory_notifications`
--
ALTER TABLE `inventory_notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issuereport`
--
ALTER TABLE `issuereport`
  MODIFY `Issue_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mirror_customization`
--
ALTER TABLE `mirror_customization`
  MODIFY `CustomizationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_page`
--
ALTER TABLE `order_page`
  MODIFY `OrderPageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pending_review_orders`
--
ALTER TABLE `pending_review_orders`
  MODIFY `PendingOrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `ReadyOrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sales_notif`
--
ALTER TABLE `sales_notif`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `shower_enclosure_customization`
--
ALTER TABLE `shower_enclosure_customization`
  MODIFY `CustomizationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_activity_log`
--
ALTER TABLE `system_activity_log`
  MODIFY `ActivityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `AddressID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `Wishlist_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE SET NULL;

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
-- Constraints for table `enduser`
--
ALTER TABLE `enduser`
  ADD CONSTRAINT `enduser_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `issuereport_ibfk_1` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `issuereport_ibfk_2` FOREIGN KEY (`Order_ID`) REFERENCES `order` (`OrderID`) ON DELETE SET NULL ON UPDATE CASCADE;

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
-- Constraints for table `product_materials`
--
ALTER TABLE `product_materials`
  ADD CONSTRAINT `product_materials_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_materials_ibfk_2` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE;

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
-- Constraints for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE;

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `user_address_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
