-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 26, 2026 at 01:31 PM
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

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`activity_id`, `action`, `item_name`, `change_description`, `description`, `timestamp`, `user_id`, `InventoryItemID`) VALUES
(1, 'Item created', 'Tempered Glass', '13 meter initial', 'System', '2025-12-08 12:29:54', NULL, NULL),
(2, 'Item created', 'Silicone Sealant', '15 pcs initial', 'System', '2025-12-08 12:32:32', NULL, NULL),
(3, 'Item deleted', 'Tempered Glass', 'Item removed from inventory', 'System', '2025-12-08 12:32:44', NULL, NULL),
(4, 'Item created', 'Tempered Glass', '20 meter initial', 'System', '2025-12-08 12:34:01', NULL, NULL),
(5, 'Stock added', 'Silicone Sealant', '+14 pcs', 'Stock management', '2025-12-08 12:39:21', 5, NULL),
(6, 'Stock reduced', 'Silicone Sealant', '-21 pcs', 'Stock management', '2025-12-08 12:47:45', 5, NULL),
(7, 'Item created', 'Alluminum Alloy', '16 meter initial', 'System', '2025-12-08 13:20:56', NULL, NULL),
(8, 'Item created', 'Insulation Bars', '14 pcs initial', 'System', '2025-12-08 14:04:36', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `AppointmentID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `QuotationID` int(11) DEFAULT NULL COMMENT 'Linked quotation ID',
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
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `AppointmentType` enum('Ocular','Installation') DEFAULT NULL COMMENT 'Type of appointment: Ocular or Installation',
  `OcularNotes` text DEFAULT NULL COMMENT 'Site assessment notes and measurements',
  `OcularReportPath` varchar(255) DEFAULT NULL COMMENT 'Path to full ocular report PDF',
  `InstallationNotes` text DEFAULT NULL COMMENT 'Installation-specific notes',
  `InstallationChecklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Installation checklist items (JSON format)' CHECK (json_valid(`InstallationChecklist`)),
  `SitePhotos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of site photo paths (JSON format)' CHECK (json_valid(`SitePhotos`)),
  `InternalNotes` text DEFAULT NULL COMMENT 'Internal admin notes',
  `CustomerVisibleNotes` text DEFAULT NULL COMMENT 'Notes visible to customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `OrderID`, `QuotationID`, `Customer_ID`, `ProductName`, `ClientName`, `Service`, `AppointmentDate`, `AppointmentTime`, `AssignedStaff`, `AssignedStaff_ID`, `Status`, `Notes`, `Created_Date`, `Updated_Date`, `AppointmentType`, `OcularNotes`, `OcularReportPath`, `InstallationNotes`, `InstallationChecklist`, `SitePhotos`, `InternalNotes`, `CustomerVisibleNotes`) VALUES
(1, 9, NULL, 2, 'Shower Enclosure', 'Meryl S. Colby', 'Installed', '2025-12-17', '10:00:00', 'Engr. Sushmita Sen', NULL, 'In Progress', '', '2025-12-08 13:32:27', '2025-12-08 06:34:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 12, NULL, 2, 'Sliding Window', 'Meryl S. Colby', 'In Fabrication', '2025-12-25', '10:00:00', '', NULL, 'In Progress', '', '2025-12-08 21:39:51', '2025-12-08 14:40:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 19, NULL, 1, 'Sliding Window', 'Aaron Gabriel M. Manantan', 'Order Placed', '2025-12-30', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-15 23:54:06', '2026-01-15 23:54:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 21, NULL, 2, 'Classic Mirror', 'Meryl S. Colby', 'Order Placed', '2026-01-31', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-15 23:54:06', '2026-01-15 23:54:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 7, NULL, 3, '900 Series Sliding Window', 'Ag  Pauig', 'Order Placed', '2026-01-20', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-20 01:39:25', '2026-01-20 01:39:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 7, NULL, 3, '900 Series Sliding Window', 'Ag  Pauig', 'Ocular Visit', '2026-01-21', '10:00:00', NULL, NULL, 'Complete', NULL, '2026-01-21 06:11:05', '2026-01-21 07:19:03', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 16, NULL, 7, NULL, 'Angela  Pauig', 'Ocular Visit', '2026-01-21', '10:00:00', NULL, NULL, 'Complete', NULL, '2026-01-21 08:19:09', '2026-01-22 17:26:46', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 24, NULL, 7, NULL, 'Angela  Pauig', 'Ocular Visit', '2026-01-21', '10:00:00', 'Sales Test', 3, 'Complete', NULL, '2026-01-21 13:54:04', '2026-01-23 03:13:28', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 7, NULL, 3, '900 Series Sliding Window', 'Ag  Pauig', 'In Fabrication', '2026-01-21', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-21 14:19:03', '2026-01-21 14:19:03', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 23, NULL, 7, 'Mirror', 'Angela  Pauig', 'In Fabrication', '2026-01-21', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-21 14:19:15', '2026-01-21 14:19:15', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 23, NULL, 7, 'Mirror', 'Angela  Pauig', 'Installed', '2026-01-23', '15:20:10', NULL, NULL, 'In Progress', NULL, '2026-01-21 14:20:10', '2026-01-21 14:20:10', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 16, NULL, 7, '900 sampp', 'Angela  Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 00:26:46', '2026-01-23 00:26:46', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 25, NULL, 7, '75 seriesss', 'Angela  Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 00:27:09', '2026-01-23 00:27:09', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 25, NULL, 7, '75 seriesss', 'Angela  Pauig', 'Installed', '2026-01-25', '01:27:00', NULL, NULL, 'Complete', NULL, '2026-01-23 00:27:20', '2026-01-22 17:29:03', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 26, NULL, 8, NULL, 'Rommel John Jeric R. Lerum', 'Ocular Visit', '2026-01-23', '10:00:00', 'Admin Super', 4, 'Complete', NULL, '2026-01-23 01:52:39', '2026-01-22 18:53:21', 'Ocular', NULL, NULL, NULL, NULL, '[\"uploads\\/appointments\\/site_photos\\/site_20_1769133192_0.jpg\"]', NULL, NULL),
(21, 26, NULL, 8, '900 sampp', 'Rommel John Jeric R. Lerum', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 01:53:22', '2026-01-23 01:53:22', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 26, NULL, 8, '900 sampp', 'Rommel John Jeric R. Lerum', 'Installed', '2026-01-25', '02:54:00', 'Admin Super', 4, 'Complete', NULL, '2026-01-23 01:54:34', '2026-01-22 18:55:24', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 1, NULL, 3, '900 Series Sliding Window', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 2, NULL, 3, 'Mirror', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 3, NULL, 3, 'Mirror', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 4, NULL, 3, 'Mirror', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 5, NULL, 3, 'Mirror', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 9, NULL, 3, 'Mirror', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 10, NULL, 3, 'Mirror', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 11, NULL, 3, 'Mirror', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 13, NULL, 3, 'Mirror', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 14, NULL, 3, 'Mirror', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 15, NULL, 3, 'Mirror', 'Ag Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 17, NULL, 7, 'Mirror', 'Angela Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, 18, NULL, 7, 'Mirror', 'Angela Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 19, NULL, 7, 'Mirror', 'Angela Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 20, NULL, 7, 'Mirror', 'Angela Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 21, NULL, 7, 'Mirror', 'Angela Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 22, NULL, 7, 'Mirror', 'Angela Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 27, NULL, 8, '85 series', 'Rommel John Jeric Lerum', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:27:34', '2026-01-23 05:27:34', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 8, NULL, 3, NULL, 'Ag  Pauig', 'Ocular Visit', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 05:28:14', '2026-01-23 05:28:14', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 24, NULL, 7, NULL, 'Angela  Pauig', 'In Fabrication', '2026-01-23', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-23 10:13:28', '2026-01-23 10:13:28', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(58, 37, NULL, 7, NULL, 'Angela Pauig', 'Ocular Visit', '2026-01-24', NULL, 'Admin Super', 4, 'In Progress', NULL, '2026-01-24 02:32:38', '2026-01-24 02:33:07', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(59, 35, NULL, 7, NULL, 'Angela Pauig', 'Ocular Visit', NULL, NULL, 'Sales Test', 3, 'In Progress', NULL, '2026-01-24 02:47:11', '2026-01-24 03:10:54', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 33, NULL, 7, NULL, 'Angela Pauig', 'Ocular Visit', NULL, NULL, 'Admin Super', 4, 'In Progress', NULL, '2026-01-24 02:53:45', '2026-01-24 02:53:45', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(61, 1, NULL, 3, '900 Series Sliding Window', 'Ag  Pauig', 'Installed', '2026-01-26', '04:24:18', NULL, NULL, 'In Progress', NULL, '2026-01-24 03:24:18', '2026-01-24 03:24:18', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(62, 40, NULL, 8, 'Mirror', 'Rommel John Jeric R. Lerum', 'Installed', '2026-01-26', '05:41:00', 'Admin Super', 4, 'Complete', NULL, '2026-01-24 04:41:13', '2026-01-23 21:42:30', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, 41, NULL, 8, 'Mirror', 'Rommel John Jeric R. Lerum', 'Installed', '2026-01-26', '06:02:00', NULL, NULL, 'Complete', NULL, '2026-01-24 05:02:11', '2026-01-23 22:03:53', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, 42, NULL, 8, NULL, 'Rommel John Jeric Lerum', 'Ocular Visit', '2026-01-24', NULL, 'Admin Super', 4, 'Complete', NULL, '2026-01-24 05:05:11', '2026-01-23 22:05:53', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(65, 42, NULL, 8, NULL, 'Rommel John Jeric R. Lerum', 'In Fabrication', '2026-01-24', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-24 05:05:53', '2026-01-24 05:05:53', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(66, 42, NULL, 8, '900 Series', 'Rommel John Jeric R. Lerum', 'Installed', '2026-01-26', '11:45:29', NULL, NULL, 'In Progress', NULL, '2026-01-24 10:45:29', '2026-01-24 04:13:56', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, 75, NULL, 10, NULL, 'Jinwoo  Sun', 'Ocular Visit', '2026-01-26', '10:00:00', NULL, NULL, 'Complete', NULL, '2026-01-26 03:09:11', '2026-01-25 20:09:32', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(68, 75, NULL, 10, NULL, 'Jinwoo  Sun', 'In Fabrication', '2026-01-26', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-01-26 03:09:32', '2026-01-26 03:09:32', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(69, 75, NULL, 10, '900 Series Window', 'Jinwoo  Sun', 'Installed', '2026-01-28', '04:09:45', NULL, NULL, 'In Progress', NULL, '2026-01-26 03:09:45', '2026-01-26 03:09:45', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(70, 90, NULL, 8, 'Awning Window', 'Rommel John Jeric R. Lerum', 'Installed', '2026-02-01', '10:47:28', NULL, NULL, 'In Progress', NULL, '2026-01-26 09:47:28', '2026-01-26 04:16:14', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(71, 91, NULL, 8, 'Sliding Window', 'Rommel John Jeric R. Lerum', 'Installed', '2026-01-31', '10:55:14', NULL, NULL, 'In Progress', NULL, '2026-01-26 09:55:14', '2026-01-26 03:04:13', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`Cart_ID`, `Customer_ID`, `Product_ID`, `CustomizationID`, `Quantity`, `Added_Date`) VALUES
(52, 3, 33, 151, 1, '2026-01-20 04:46:08'),
(146, 10, 32, 245, 1, '2026-01-26 07:14:54');

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
(2, 4, '2025-12-29 13:06:35'),
(3, 5, '2026-01-17 19:37:17'),
(7, 9, '2026-01-21 08:14:35'),
(8, 10, '2026-01-23 01:25:08'),
(9, 11, '2026-01-23 10:02:28'),
(10, 12, '2026-01-25 18:33:28');

-- --------------------------------------------------------

--
-- Table structure for table `customer_customizations`
--

CREATE TABLE `customer_customizations` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `selections` longtext NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_customizations`
--

INSERT INTO `customer_customizations` (`id`, `customer_id`, `product_id`, `selections`, `timestamp`, `updated_at`) VALUES
(1, 3, 33, '{\"shape\":\"Rectangular with rounded edges\",\"frameType\":\"Framed (gold frame shown)\",\"lighting\":\"Integrated LED lighting\",\"ledColorTemperature\":\"Warm white\",\"mountingMethod\":\"Wall-mounted\",\"control\":\"Touch sensor button\",\"additionalFeatures\":\"Defogger\",\"tintFinish\":\"Bronze tint\\/color\",\"orientation\":\"Vertical\",\"style\":\"French Type (grid\\/paneled design)\",\"gridPattern\":\"French window style grid\",\"quantity\":\"Available in sets (3 sets, or individually)\",\"arrangement\":\"Can be displayed as triptych\",\"cornerRadius\":[0],\"cornerRadius_unit\":\"in\"}', '2026-01-20 07:10:50', '2026-01-20 07:10:50'),
(2, 3, 32, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Hanalok\",\"glassType\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-01-20 01:51:48', '2026-01-20 01:51:48'),
(3, 3, 31, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Hanalok\",\"glassType\":\"Reflective: Light Bronze\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-01-20 02:13:36', '2026-01-20 02:13:36'),
(4, 3, 11, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Hanalok\",\"glassType\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-01-20 01:13:37', '2026-01-20 01:13:37'),
(5, 7, 11, '{\"numberOfPanels\":\"4 Panels\",\"transomType\":\"Fixed Transom Sill (Fixed glass at bottom)\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S | S | S (All Sliding)\",\"frameColor\":\"Wood Finish\",\"glassType\":\"Euro Gray\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"Without Screen\"}', '2026-01-21 09:16:38', '2026-01-21 09:16:38'),
(6, 7, 32, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Hanalok\",\"glassType\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-01-23 14:15:26', '2026-01-23 14:15:26'),
(7, 7, 33, '{\"shape\":\"Round\",\"frameType\":\"Frameless\",\"edgeFinish\":\"Beveled\",\"lighting\":\"Integrated LED lighting\",\"ledColorTemperature\":\"Warm white\",\"mountingMethod\":\"Wall-mounted\",\"control\":\"Touch sensor button\",\"additionalFeatures\":\"Defogger\",\"tintFinish\":\"Bronze tint\\/color\",\"orientation\":\"Vertical\",\"style\":\"French Type (grid\\/paneled design)\",\"gridPattern\":\"French window style grid\",\"quantity\":\"Available in sets (3 sets, or individually)\",\"arrangement\":\"Can be displayed as triptych\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\",\"frameColor\":\"Dark\\/Black\"}', '2026-01-23 08:00:38', '2026-01-23 08:00:38'),
(8, 7, 35, '{\"glassType\":\"Clear\",\"frameColor\":\"White\",\"operation\":\"Casement (hinge side configurable)\",\"numberOfPanels\":\"Single panel\",\"hingeSide\":\"Left-hinged\",\"configuration\":\"Two casement windows with fixed transom\",\"transomOptions\":\"Different transom sizes\",\"thickness\":\"6mm\",\"screen\":\"With Screen\"}', '2026-01-22 02:20:51', '2026-01-22 02:20:51'),
(9, 7, 34, '{\"glassType\":\"Low-E\"}', '2026-01-22 03:10:18', '2026-01-22 03:10:18'),
(10, 7, 31, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Hanalok\",\"glassType\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-01-23 00:45:03', '2026-01-23 00:45:03'),
(11, 7, 36, '{\"frameColor\":\"Black\",\"thickness\":\"6mm\",\"glassType\":\"Clear\",\"operation\":\"Casement (hinge side configurable)\",\"numberOfPanels\":\"Single panel\",\"hingeSide\":\"Left-hinged\",\"configuration\":\"Custom configurations\",\"transomOptions\":\"Shapes\",\"screen\":\"Without Screen\"}', '2026-01-22 09:43:48', '2026-01-22 09:43:48'),
(12, 7, 39, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-23 12:54:16', '2026-01-23 12:54:16'),
(13, 7, 40, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-22 13:02:25', '2026-01-22 13:02:25'),
(14, 7, 44, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-23 08:45:41', '2026-01-23 08:45:41'),
(15, 7, 42, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-23 08:05:27', '2026-01-23 08:05:27'),
(16, 7, 43, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-23 14:05:33', '2026-01-23 14:05:33'),
(17, 8, 44, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-23 06:33:58', '2026-01-23 06:33:58'),
(18, 8, 32, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Hanalok\",\"glassType\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-01-24 06:04:17', '2026-01-24 06:04:17'),
(19, 8, 40, '{\"transomType\":\"Casement w\\/FTS\",\"panelConfiguration\":\"5\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-23 04:05:20', '2026-01-23 04:05:20'),
(20, 8, 43, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-23 06:39:46', '2026-01-23 06:39:46'),
(21, 8, 38, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-24 05:32:39', '2026-01-24 05:32:39'),
(22, 8, 33, '{\"shape\":\"Square\",\"frameType\":\"Framed\",\"lighting\":\"Integrated LED lighting\",\"ledColorTemperature\":\"Warm white\",\"mountingMethod\":\"Stand\",\"control\":\"Touch sensor button\",\"additionalFeatures\":\"Defogger\",\"tintFinish\":\"Bronze tint\\/color\",\"orientation\":\"Vertical\",\"style\":\"French Type (grid\\/paneled design)\",\"gridPattern\":\"French window style grid\",\"quantity\":\"Available in sets (3 sets, or individually)\",\"arrangement\":\"Can be displayed as triptych\",\"cornerRadius\":4.2,\"cornerRadius_unit\":\"in\",\"frameColor\":\"Black frame\"}', '2026-01-24 05:48:56', '2026-01-24 05:48:56'),
(23, 8, 31, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"Fixed Transom Head (Fixed glass at top)\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Hanalok\",\"glassType\":\"Reflective: Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-01-24 10:39:06', '2026-01-24 10:39:06'),
(24, 10, 43, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-25 19:58:44', '2026-01-25 19:58:44'),
(25, 10, 40, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-25 21:34:31', '2026-01-25 21:34:31'),
(26, 10, 32, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Hanalok\",\"glassType\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-01-26 08:16:51', '2026-01-26 08:16:51'),
(27, 10, 44, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"1\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-26 06:10:12', '2026-01-26 06:10:12'),
(28, 10, 37, '{\"transomType\":\"None\",\"panelConfiguration\":\"2\",\"frameColor\":\"Wood Finish\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-01-26 04:06:39', '2026-01-26 04:06:39'),
(29, 8, 5, '{\"shape\":\"Rectangle\",\"frameType\":\"Framed\",\"lighting\":\"Integrated LED lighting\",\"ledColorTemperature\":\"Warm white\",\"mountingMethod\":\"Wall-mounted\",\"control\":\"Touch sensor button\",\"tintFinish\":\"Bronze tint\\/color\",\"cornerRadius\":4.6,\"cornerRadius_unit\":\"in\"}', '2026-01-26 09:41:20', '2026-01-26 09:41:20'),
(30, 8, 6, '{\"glassType\":\"Frosted\",\"doorType\":\"Double swing\",\"doorSwing\":\"Left swing\",\"fixedPanels\":\"With fixed side\\/transom panels\",\"configuration\":\"With fixed side panel (left or right)\",\"handleType\":\"Various pull handle designs\",\"hardwareFinish\":\"Polished Chrome\\/Stainless Steel\",\"gridPattern\":\"Internal grids\",\"glassTreatment\":\"Frosted stripes (horizontal\\/vertical)\",\"installation\":\"Patch fittings (minimalist hardware)\",\"hardware\":\"Push\\/pull handles\"}', '2026-01-26 09:41:43', '2026-01-26 09:41:43'),
(31, 8, 7, '{\"shape\":\"Custom shapes\",\"edgeFinish\":\"Raw\",\"mountingMethod\":\"Stand\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\"}', '2026-01-26 09:54:07', '2026-01-26 09:54:07'),
(32, 8, 2, '{\"glassType\":\"Tempered\",\"glassColor\":\"Bronze\",\"frameColor\":\"Analok\",\"operation\":\"Awning (push-out)\",\"openingDirection\":\"Top-hinged\",\"thickness\":\"6mm\",\"screen\":\"With Screen\"}', '2026-01-26 10:45:37', '2026-01-26 10:45:37'),
(33, 8, 1, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-01-26 10:54:20', '2026-01-26 10:54:20');

-- --------------------------------------------------------

--
-- Table structure for table `customer_notifications`
--

CREATE TABLE `customer_notifications` (
  `NotificationID` int(11) NOT NULL,
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
  `CreatedBy` int(11) DEFAULT NULL COMMENT 'UserID of admin/staff who created the notification'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_notifications`
--

INSERT INTO `customer_notifications` (`NotificationID`, `Customer_ID`, `Icon`, `Type`, `Title`, `Message`, `Status`, `Created_Date`, `Read_Date`, `RelatedID`, `RelatedType`, `CreatedBy`) VALUES
(1, 3, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI007 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Unread', '2026-01-21 15:19:03', NULL, 7, 'Order', 2),
(2, 7, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI023 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-01-21 15:19:15', '2026-01-21 16:49:30', 23, 'Order', 2),
(3, 7, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI023 fabrication is complete! Installation is scheduled for January 23, 2026 at 3:20 PM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-21 15:20:10', '2026-01-21 16:49:30', 23, 'Order', 2),
(4, 7, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI023 fabrication is complete! Installation is scheduled for January 23, 2026 at 3:20 PM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-21 15:21:35', '2026-01-21 16:49:30', 23, 'Order', 2),
(5, 7, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI016 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-01-23 01:26:46', '2026-01-23 01:27:57', 16, 'Order', 2),
(6, 7, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI025 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-01-23 01:27:09', '2026-01-23 01:27:57', 25, 'Order', 2),
(7, 7, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI025 fabrication is complete! Installation is scheduled for January 25, 2026 at 1:27 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-23 01:27:20', '2026-01-23 01:27:57', 25, 'Order', 2),
(8, 8, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI026 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-01-23 02:53:22', '2026-01-23 02:55:49', 26, 'Order', 2),
(9, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI026 fabrication is complete! Installation is scheduled for January 25, 2026 at 2:54 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-23 02:54:34', '2026-01-23 02:55:49', 26, 'Order', 2),
(10, 7, 'fa-cog', 'Order', 'Order in Fabrication', 'Your order #GI019 is now being fabricated. We\'ll notify you once it\'s ready for installation.', 'Unread', '2026-01-23 06:24:13', NULL, 19, 'Order', 2),
(11, 7, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI024 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Unread', '2026-01-23 11:13:28', NULL, 24, 'Order', 2),
(12, 7, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI037 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Unread', '2026-01-24 03:33:03', NULL, 37, 'Order', 2),
(13, 7, 'fa-cog', 'Order', 'Order in Fabrication', 'Your order #GI038 is now being fabricated. We\'ll notify you once it\'s ready for installation.', 'Unread', '2026-01-24 03:46:55', NULL, 38, 'Order', 2),
(14, 3, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI001 fabrication is complete! Installation is scheduled for January 26, 2026 at 4:24 AM. You can request to change the date within the next 7 days if needed.', 'Unread', '2026-01-24 04:24:18', NULL, 1, 'Order', 2),
(15, 8, 'fa-cog', 'Order', 'Order in Fabrication', 'Your order #GI040 is now being fabricated. We\'ll notify you once it\'s ready for installation.', 'Read', '2026-01-24 05:39:55', '2026-01-24 05:41:31', 40, 'Order', 2),
(16, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI040 fabrication is complete! Installation is scheduled for January 26, 2026 at 5:41 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-24 05:41:13', '2026-01-24 05:41:31', 40, 'Order', 2),
(17, 8, 'fa-cog', 'Order', 'Order in Fabrication', 'Your order #GI041 is now being fabricated. We\'ll notify you once it\'s ready for installation.', 'Read', '2026-01-24 06:00:19', '2026-01-24 06:01:40', 41, 'Order', 2),
(18, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI041 fabrication is complete! Installation is scheduled for January 26, 2026 at 6:02 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-24 06:02:11', '2026-01-24 06:02:32', 41, 'Order', 2),
(19, 8, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI042 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-01-24 06:05:16', '2026-01-24 06:32:31', 42, 'Order', 2),
(20, 8, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI042 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-01-24 06:05:53', '2026-01-24 06:32:31', 42, 'Order', 2),
(21, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI042 fabrication is complete! Installation is scheduled for January 26, 2026 at 11:45 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-24 11:45:29', '2026-01-24 11:47:25', 42, 'Order', 2),
(22, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI042 fabrication is complete! Installation is scheduled for January 26, 2026 at 11:45 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-24 11:46:02', '2026-01-24 11:47:25', 42, 'Order', 2),
(23, 10, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI075 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Unread', '2026-01-26 04:09:07', NULL, 75, 'Order', 2),
(24, 10, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI075 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Unread', '2026-01-26 04:09:32', NULL, 75, 'Order', 2),
(25, 10, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI075 fabrication is complete! Installation is scheduled for January 28, 2026 at 4:09 AM. You can request to change the date within the next 7 days if needed.', 'Unread', '2026-01-26 04:09:45', NULL, 75, 'Order', 2),
(26, 8, 'fa-cog', 'Order', 'Order in Fabrication', 'Your order #GI090 is now being fabricated. We\'ll notify you once it\'s ready for installation.', 'Read', '2026-01-26 10:46:50', '2026-01-26 10:47:18', 90, 'Order', 2),
(27, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI090 fabrication is complete! Installation is scheduled for January 28, 2026 at 10:47 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-26 10:47:28', '2026-01-26 10:47:59', 90, 'Order', 2),
(28, 8, 'fa-cog', 'Order', 'Order in Fabrication', 'Your order #GI091 is now being fabricated. We\'ll notify you once it\'s ready for installation.', 'Read', '2026-01-26 10:54:59', '2026-01-26 10:55:56', 91, 'Order', 2),
(29, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI091 fabrication is complete! Installation is scheduled for January 28, 2026 at 10:55 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-26 10:55:14', '2026-01-26 10:55:56', 91, 'Order', 2);

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
(27, 2, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_2_1765199854_6936cfeea4d4b.png', NULL, NULL, NULL, '15750.00', NULL, '2025-12-08 13:17:34', '2025-12-08 13:17:34'),
(28, 2, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_2_1765206172_6936e89c77fe1.png', NULL, NULL, NULL, '15750.00', NULL, '2025-12-08 15:02:52', '2025-12-08 15:02:52'),
(29, 2, 2, '60 x 70', 'rectangle', 'double', '8mm', 'beveled', 'vinyl', 'None', 'uploads/designs/design_2_1765206208_6936e8c056ae1.png', NULL, NULL, NULL, '79300.00', NULL, '2025-12-08 15:03:28', '2025-12-08 15:03:28'),
(30, 2, 1, '45 x 35', 'square', 'tinted', '5mm', 'flat-polish', 'aluminum', 'None', 'uploads/designs/design_2_1765228695_6937409784354.png', NULL, NULL, NULL, '34582.63', NULL, '2025-12-08 21:18:15', '2025-12-08 21:18:15'),
(31, 2, 1, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'aluminum', 'None', 'uploads/designs/design_2_1765228950_69374196c9bb0.png', NULL, NULL, NULL, '27518.75', NULL, '2025-12-08 21:22:30', '2025-12-08 21:22:30'),
(33, 2, 1, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_2_1765229698_69374482e3ce9.png', NULL, NULL, NULL, '23625.00', '{\"baseArea\":23625,\"shapeAddon\":0,\"typeAddon\":0,\"thicknessAddon\":0,\"frameAddon\":0,\"edgeAddon\":0,\"total\":23625}', '2025-12-08 21:34:58', '2025-12-08 21:34:58'),
(34, 2, 1, '45 x 35', 'triangle', 'tempered', '12mm', 'metered', 'vinyl', 'None', 'uploads/designs/design_2_1765229716_6937449438627.png', NULL, NULL, NULL, '43720.00', '{\"baseArea\":23625,\"shapeAddon\":3543.7499999999977,\"typeAddon\":0,\"thicknessAddon\":14175.000000000002,\"frameAddon\":0,\"edgeAddon\":250,\"total\":43720}', '2025-12-08 21:35:16', '2025-12-08 21:35:16'),
(36, 2, 2, '45 x 35', 'pentagon', 'tinted', '5mm', 'beveled', 'wood', 'None', 'uploads/designs/design_2_1765234165_693755f5ebd7b.png', NULL, NULL, NULL, '33243.75', NULL, '2025-12-08 22:49:25', '2025-12-08 22:49:25'),
(37, 2, 2, '45 x 35', 'pentagon', 'tinted', '8mm', 'metered', 'wood', 'None', 'uploads/designs/design_2_1765234399_693756dfbd8a1.png', NULL, NULL, NULL, '40917.19', NULL, '2025-12-08 22:53:19', '2025-12-08 22:53:19'),
(38, 2, 1, '45 x 35', 'square', 'tempered', '10mm', 'flat-polish', 'aluminum', 'None', 'uploads/designs/design_2_1765235479_69375b178f1f7.png', NULL, NULL, NULL, '40288.06', NULL, '2025-12-08 23:11:19', '2025-12-08 23:11:19'),
(39, 2, 2, '45 x 35', 'rectangle', 'tinted', '8mm', 'beveled', 'aluminum', 'None', 'uploads/designs/design_2_1765235727_69375c0fa47b2.png', NULL, NULL, NULL, '28068.75', NULL, '2025-12-08 23:15:27', '2025-12-08 23:15:27'),
(40, 2, 1, '45 x 35', 'square', 'tinted', '5mm', 'flat-polish', 'aluminum', 'None', 'uploads/designs/design_2_1765235957_69375cf5778db.png', NULL, NULL, NULL, '34582.63', NULL, '2025-12-08 23:19:17', '2025-12-08 23:19:17'),
(41, 2, 1, '45 x 35', 'rectangle', 'frosted', '12mm', 'beveled', 'aluminum', 'None', 'uploads/designs/design_2_1765236555_69375f4b95e78.png', NULL, NULL, NULL, '50890.50', NULL, '2025-12-08 23:29:15', '2025-12-08 23:29:15'),
(42, 1, 3, '45 x 35', 'triangle', 'tempered', '12mm', 'flat-polish', 'wood', 'None', 'uploads/designs/design_1_1765240383_69376e3fdd280.png', NULL, NULL, NULL, '4712.30', NULL, '2025-12-09 00:33:03', '2025-12-09 00:33:03'),
(43, 1, 3, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1765240421_69376e6586d15.png', NULL, NULL, NULL, '1575.00', NULL, '2025-12-09 00:33:41', '2025-12-09 00:33:41'),
(44, 1, 1, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1765240506_69376eba2b159.png', NULL, NULL, NULL, '23625.00', '{\"baseArea\":23625,\"shapeAddon\":0,\"typeAddon\":0,\"thicknessAddon\":0,\"frameAddon\":0,\"edgeAddon\":0,\"total\":23625}', '2025-12-09 00:35:06', '2025-12-09 00:35:06'),
(45, 3, 4, '23 x 10', 'rectangle', 'tempered', '10mm', 'flat-polish', 'wood', 'None', 'uploads/designs/wishlist_design_3_1765241536_693772c0c50ea.png', NULL, NULL, NULL, '1886.75', NULL, '2025-12-09 00:52:16', '2025-12-09 00:52:16'),
(46, 3, 4, '23 x 10', 'rectangle', 'tempered', '10mm', 'flat-polish', 'wood', 'None', 'uploads/designs/design_3_1765241552_693772d0e99a1.png', NULL, NULL, NULL, '1886.75', '{\"baseArea\":575,\"shapeAddon\":0,\"typeAddon\":0,\"thicknessAddon\":229.99999999999994,\"frameAddon\":1001.25,\"edgeAddon\":0,\"total\":1886.75}', '2025-12-09 00:52:32', '2025-12-09 00:52:32'),
(47, 3, 1, '45 x 35', 'rectangle', 'tempered', '3mm', 'flat-polish', 'aluminum', 'None', 'uploads/designs/design_3_1765241638_693773268b273.png', NULL, NULL, NULL, '23443.44', '{\"baseArea\":23625,\"shapeAddon\":0,\"typeAddon\":0,\"thicknessAddon\":-3543.7500000000005,\"frameAddon\":3893.7499999999977,\"edgeAddon\":0,\"total\":23443.4375}', '2025-12-09 00:53:58', '2025-12-09 00:53:58'),
(67, 2, 3, '45 x 35', 'square', 'tinted', '5mm', 'flat-polish', 'aluminum', 'None', 'uploads/designs/design_2_1768005811_6961a0b3f1c61.png', NULL, NULL, NULL, '2632.17', '{\"baseArea\":1575,\"shapeAddon\":78.75000000000007,\"typeAddon\":314.99999999999994,\"thicknessAddon\":0,\"frameAddon\":586.2499999999999,\"edgeAddon\":0,\"total\":2632.1749999999997}', '2026-01-10 00:43:31', '2026-01-10 00:43:31'),
(68, 2, 2, '45 x 35', 'square', 'low-e', '8mm', 'beveled', 'aluminum', 'None', 'uploads/designs/design_2_1768005825_6961a0c125f88.png', NULL, NULL, NULL, '34181.72', '{\"baseArea\":15750,\"shapeAddon\":787.5000000000007,\"typeAddon\":6299.999999999998,\"thicknessAddon\":3937.5,\"frameAddon\":2712.4999999999986,\"edgeAddon\":550,\"total\":34181.71875}', '2026-01-10 00:43:45', '2026-01-10 00:43:45'),
(69, 2, 1, '45 x 35', 'pentagon', 'laminated', '8mm', 'metered', 'wood', 'None', 'uploads/designs/design_2_1768005841_6961a0d15a2d1.png', NULL, NULL, NULL, '68325.88', '{\"baseArea\":23625,\"shapeAddon\":5906.25,\"typeAddon\":8268.750000000002,\"thicknessAddon\":5906.25,\"frameAddon\":9068.750000000002,\"edgeAddon\":250,\"total\":68325.87890625}', '2026-01-10 00:44:01', '2026-01-10 00:44:01'),
(70, 2, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/wishlist_design_2_1768007076_6961a5a44d88f.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-10 01:04:36', '2026-01-10 01:04:36'),
(71, 2, 3, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/wishlist_design_2_1768007081_6961a5a95849a.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-10 01:04:41', '2026-01-10 01:04:41'),
(73, 2, 3, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/wishlist_design_2_1768007081_6961a5a95849a.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-10 01:06:51', '2026-01-10 01:06:51'),
(74, 2, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/wishlist_design_2_1768007076_6961a5a44d88f.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-10 01:07:00', '2026-01-10 01:07:00'),
(77, 2, 4, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_2_1768251533_6965608dd771c.png', NULL, NULL, NULL, '3937.50', NULL, '2026-01-12 20:58:53', '2026-01-12 20:58:53'),
(78, 2, 3, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/wishlist_design_2_1768251721_6965614996159.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-12 21:02:01', '2026-01-12 21:02:01'),
(98, 2, 3, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/wishlist_design_2_1768267869_6965a05d95fd3.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-13 01:31:09', '2026-01-13 01:31:09'),
(99, 2, 3, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/wishlist_design_2_1768267869_6965a05d95fd3.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-13 01:31:15', '2026-01-13 01:31:15'),
(104, 3, 31, '104in x 123in', NULL, 'Tempered: Bronze', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_3_1768761252_696d27a41e7d1.png', NULL, NULL, NULL, '2046720.00', '{\"baseArea\":2046720,\"fieldPrices\":{\"transomType\":{\"option\":\"Fixed Transom Head (Fixed glass at top)\",\"price\":0},\"numberOfPanels\":{\"option\":\"2 Panels\",\"price\":0},\"trackSystem\":{\"option\":\"3 Tracks\",\"price\":0},\"panelConfiguration\":{\"option\":\"F | S (Fixed | Sliding)\",\"price\":0},\"frameColor\":{\"option\":\"Wood Finish\",\"price\":0},\"glassType\":{\"option\":\"Tempered: Bronze\",\"price\":0},\"glassThickness\":{\"option\":\"6mm\",\"price\":0},\"lockType\":{\"option\":\"Flushlok #12\",\"price\":0},\"rollerType\":{\"option\":\"Single Panel Roller\",\"price\":0},\"screen\":{\"option\":\"Without Screen\",\"price\":0}},\"total\":2046720,\"isMinimumPriceApplied\":true,\"minimumPrice\":16000}', '2026-01-18 18:34:12', '2026-01-18 18:34:12'),
(105, 3, 31, '421in x 123in', NULL, 'Tempered: Bronze', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_3_1768763458_696d30429f6fb.png', NULL, NULL, NULL, '8285280.00', '{\"baseArea\":8285280,\"fieldPrices\":{\"transomType\":{\"option\":\"Fixed Transom Sill (Fixed glass at bottom)\",\"price\":0},\"numberOfPanels\":{\"option\":\"4 Panels\",\"price\":0},\"trackSystem\":{\"option\":\"2 Tracks\",\"price\":0},\"panelConfiguration\":{\"option\":\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\",\"price\":0},\"frameColor\":{\"option\":\"Wood Finish\",\"price\":0},\"glassType\":{\"option\":\"Tempered: Bronze\",\"price\":0},\"glassThickness\":{\"option\":\"6mm\",\"price\":0},\"lockType\":{\"option\":\"Flushlok #12\",\"price\":0},\"rollerType\":{\"option\":\"Blue Single Roller\",\"price\":0},\"screen\":{\"option\":\"Without Screen\",\"price\":0}},\"total\":8285280,\"isMinimumPriceApplied\":true,\"minimumPrice\":16000}', '2026-01-18 19:10:58', '2026-01-18 19:10:58'),
(106, 3, 31, '123in x 123in', NULL, 'Clear', NULL, NULL, 'Gray', 'None', 'uploads/designs/design_3_1768765276_696d375ce887f.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-18 19:41:16', '2026-01-18 19:41:16'),
(108, 3, 32, '145in x 123in', NULL, 'Reflective: Light Blue', NULL, NULL, 'Black', 'None', 'uploads/designs/design_3_1768768020_696d421486d1d.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-18 20:27:00', '2026-01-18 20:27:00'),
(109, 3, 32, '526in x 321in', NULL, 'Ford Blue', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_3_1768768144_696d4290ceaeb.png', NULL, NULL, NULL, '0.00', '{\"baseArea\":28063049.430000003,\"fieldPrices\":{\"numberOfPanels\":{\"option\":\"2 Panels\",\"price\":0},\"transomType\":{\"option\":\"Fixed Transom Sill (Fixed glass at bottom)\",\"price\":0},\"trackSystem\":{\"option\":\"2 Tracks\",\"price\":0},\"panelConfiguration\":{\"option\":\"F | S (Fixed | Sliding)\",\"price\":0},\"frameColor\":{\"option\":\"Wood Finish\",\"price\":0},\"glassType\":{\"option\":\"Ford Blue\",\"price\":0},\"glassThickness\":{\"option\":\"6mm\",\"price\":0},\"lockType\":{\"option\":\"Flushlok #12\",\"price\":0},\"rollerType\":{\"option\":\"Blue Single Roller\",\"price\":0},\"screen\":{\"option\":\"Without Screen\",\"price\":0}},\"total\":28063049.430000003,\"isMinimumPriceApplied\":true,\"minimumPrice\":16620.5}', '2026-01-18 20:29:04', '2026-01-18 20:29:04'),
(110, 3, 32, '526in x 321in', NULL, 'Ford Blue', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_3_1768768146_696d429214edc.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-18 20:29:06', '2026-01-18 20:29:06'),
(111, 3, 31, '321in x 123in', NULL, 'Ultra Clear', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_3_1768774007_696d59778fd1d.png', NULL, NULL, NULL, '6317280.00', NULL, '2026-01-18 22:06:47', '2026-01-18 22:06:47'),
(113, 3, 31, '32cm x 12cm', NULL, 'Ultra Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_3_1768785797_696d87859f359.png', NULL, NULL, NULL, '16000.00', NULL, '2026-01-19 01:23:17', '2026-01-19 01:23:17'),
(114, 3, 31, '63cm x 31cm', NULL, 'Light Green', NULL, NULL, 'Gray', 'None', 'uploads/designs/design_3_1768786303_696d897faab31.png', NULL, NULL, NULL, '48434.50', NULL, '2026-01-19 01:31:43', '2026-01-19 01:31:43'),
(115, 3, 31, '42cm x 21cm', NULL, 'Ultra Clear', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_3_1768786647_696d8ad74d442.png', NULL, NULL, NULL, '21873.64', NULL, '2026-01-19 01:37:27', '2026-01-19 01:37:27'),
(116, 3, 31, '53cm x 23cm', NULL, 'Ford Blue', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_3_1768787355_696d8d9b48bd5.png', NULL, NULL, NULL, '30231.26', NULL, '2026-01-19 01:49:15', '2026-01-19 01:49:15'),
(117, 3, 31, '44cm x 24cm', NULL, 'Reflective: Dark Green', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_3_1768789848_696d975870d9e.png', NULL, NULL, NULL, '26188.85', NULL, '2026-01-19 02:30:48', '2026-01-19 02:30:48'),
(118, 3, 31, '32cm x 12cm', NULL, 'Dark Gray', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_3_1768812775_696df0e7d549e.png', NULL, NULL, NULL, '16000.00', NULL, '2026-01-19 08:52:55', '2026-01-19 08:52:55'),
(119, 3, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768850463_696e841f1c265.png', NULL, NULL, NULL, '0.00', NULL, '2026-01-19 19:21:03', '2026-01-19 19:21:03'),
(120, 3, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768851596_696e888c6c5cf.png', NULL, NULL, NULL, '151875.00', NULL, '2026-01-19 19:39:56', '2026-01-19 19:39:56'),
(121, 3, 33, '45in x 45in', 'Rectangular with rounded edges', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768851803_696e895b11c4f.png', NULL, NULL, NULL, '159989.00', NULL, '2026-01-19 19:43:23', '2026-01-19 19:43:23'),
(122, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768852007_696e8a2749dcf.png', NULL, NULL, NULL, '159034.00', NULL, '2026-01-19 19:46:47', '2026-01-19 19:46:47'),
(123, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768853417_696e8fa9d9623.png', NULL, NULL, NULL, '158879.00', '{}', '2026-01-19 20:10:17', '2026-01-19 20:10:17'),
(124, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768853420_696e8fac50c19.png', NULL, NULL, NULL, '158879.00', NULL, '2026-01-19 20:10:20', '2026-01-19 20:10:20'),
(125, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768860362_696eaaca93f77.png', NULL, NULL, NULL, '158868.00', NULL, '2026-01-19 22:06:02', '2026-01-19 22:06:02'),
(126, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768860449_696eab216f68b.png', NULL, NULL, NULL, '158960.00', '{}', '2026-01-19 22:07:29', '2026-01-19 22:07:29'),
(127, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768860452_696eab24c6588.png', NULL, NULL, NULL, '158960.00', NULL, '2026-01-19 22:07:32', '2026-01-19 22:07:32'),
(128, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768861076_696ead947b04c.png', NULL, NULL, NULL, '158960.00', NULL, '2026-01-19 22:17:56', '2026-01-19 22:17:56'),
(129, 3, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768863775_696eb81f5378d.png', NULL, NULL, NULL, '159088.00', NULL, '2026-01-19 23:02:55', '2026-01-19 23:02:55'),
(130, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, 'Black frame', 'None', 'uploads/designs/design_3_1768868603_696ecafb44fee.png', NULL, NULL, NULL, '159086.00', '{}', '2026-01-20 00:23:23', '2026-01-20 00:23:23'),
(131, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, 'Black frame', 'None', 'uploads/designs/design_3_1768868605_696ecafdbd489.png', NULL, NULL, NULL, '159086.00', NULL, '2026-01-20 00:23:25', '2026-01-20 00:23:25'),
(132, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768869514_696ece8aaaa2b.png', NULL, NULL, NULL, '158960.00', '{}', '2026-01-20 00:38:34', '2026-01-20 00:38:34'),
(133, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768869516_696ece8c9d583.png', NULL, NULL, NULL, '158960.00', NULL, '2026-01-20 00:38:36', '2026-01-20 00:38:36'),
(134, 3, 32, '45in x 35in', NULL, 'Reflective: Light Blue', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_3_1768869565_696ecebd45465.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-20 00:39:25', '2026-01-20 00:39:25'),
(135, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, 'Rose Gold', 'None', 'uploads/designs/design_3_1768870239_696ed15f92ca9.png', NULL, NULL, NULL, '159190.00', '{}', '2026-01-20 00:50:39', '2026-01-20 00:50:39'),
(136, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, 'Rose Gold', 'None', 'uploads/designs/design_3_1768870240_696ed160896a1.png', NULL, NULL, NULL, '159190.00', NULL, '2026-01-20 00:50:40', '2026-01-20 00:50:40'),
(137, 3, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_3_1768870312_696ed1a8ee026.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-20 00:51:52', '2026-01-20 00:51:52'),
(138, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768871521_696ed66190d8a.png', NULL, NULL, NULL, '158960.00', '{}', '2026-01-20 01:12:01', '2026-01-20 01:12:01'),
(139, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768871524_696ed664d271b.png', NULL, NULL, NULL, '158960.00', NULL, '2026-01-20 01:12:04', '2026-01-20 01:12:04'),
(140, 3, 31, '45in x 35in', NULL, 'Reflective: Light Bronze', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_3_1768871623_696ed6c744214.png', NULL, NULL, NULL, '252000.00', NULL, '2026-01-20 01:13:43', '2026-01-20 01:13:43'),
(141, 3, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768872568_696eda78e4918.png', NULL, NULL, NULL, '159088.00', NULL, '2026-01-20 01:29:28', '2026-01-20 01:29:28'),
(142, 3, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_3_1768873018_696edc3ab60ed.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-20 01:36:58', '2026-01-20 01:36:58'),
(143, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, 'Dark/Black', 'None', 'uploads/designs/design_3_1768876637_696eea5d0437f.png', NULL, NULL, NULL, '159186.00', '{}', '2026-01-20 02:37:17', '2026-01-20 02:37:17'),
(144, 3, 33, '45in x 45in', 'Circle', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768878390_696ef13665901.png', NULL, NULL, NULL, '153980.00', NULL, '2026-01-20 03:06:30', '2026-01-20 03:06:30'),
(145, 3, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768878434_696ef162386d7.png', NULL, NULL, NULL, '159108.00', NULL, '2026-01-20 03:07:14', '2026-01-20 03:07:14'),
(146, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, 'Gold frame', 'None', 'uploads/designs/design_3_1768878831_696ef2efb90de.png', NULL, NULL, NULL, '159096.00', NULL, '2026-01-20 03:13:51', '2026-01-20 03:13:51'),
(147, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768879161_696ef439b996a.png', NULL, NULL, NULL, '158814.00', '{}', '2026-01-20 03:19:21', '2026-01-20 03:19:21'),
(148, 3, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768879162_696ef43a25044.png', NULL, NULL, NULL, '158814.00', '{}', '2026-01-20 03:19:22', '2026-01-20 03:19:22'),
(149, 3, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768879262_696ef49e8a6f1.png', NULL, NULL, NULL, '159088.00', '{}', '2026-01-20 03:21:02', '2026-01-20 03:21:02'),
(150, 3, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768879267_696ef4a355892.png', NULL, NULL, NULL, '159088.00', NULL, '2026-01-20 03:21:07', '2026-01-20 03:21:07'),
(151, 3, 33, '45in x 45in', 'Circle', NULL, NULL, NULL, 'Silver', 'None', 'uploads/designs/design_3_1768884368_696f08903177e.png', NULL, NULL, NULL, '153942.00', '{}', '2026-01-20 04:46:08', '2026-01-20 04:46:08'),
(153, 7, 32, '45in x 35in', NULL, 'Dark Gray', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1768983450_69708b9a304e5.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-21 08:17:30', '2026-01-21 08:17:30'),
(154, 7, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_7_1768984141_69708e4da758e.png', NULL, NULL, NULL, '159088.00', '{}', '2026-01-21 08:29:01', '2026-01-21 08:29:01'),
(155, 7, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_7_1768984142_69708e4e82ef9.png', NULL, NULL, NULL, '159088.00', NULL, '2026-01-21 08:29:02', '2026-01-21 08:29:02'),
(156, 7, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_7_1769001409_6970d1c170915.png', NULL, NULL, NULL, '158868.00', NULL, '2026-01-21 13:16:49', '2026-01-21 13:16:49'),
(157, 7, 33, '45in x 45in', 'Square', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_7_1769002465_6970d5e15f6ec.png', NULL, NULL, NULL, '158568.00', NULL, '2026-01-21 13:34:25', '2026-01-21 13:34:25'),
(158, 7, 33, '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_7_1769002799_6970d72fd08ef.png', NULL, NULL, NULL, '158960.00', NULL, '2026-01-21 13:39:59', '2026-01-21 13:39:59'),
(159, 7, 32, '45in x 35in', NULL, 'Ultra Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769003618_6970da62304d6.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-21 13:53:38', '2026-01-21 13:53:38'),
(160, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769040856_69716bd84bd32.png', NULL, NULL, NULL, '261772.88', '{}', '2026-01-22 00:14:16', '2026-01-22 00:14:16'),
(161, 7, 33, '45in x 45in', 'Custom shapes', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_7_1769041455_69716e2f6ac0d.png', NULL, NULL, NULL, '154142.00', NULL, '2026-01-22 00:24:15', '2026-01-22 00:24:15'),
(162, 7, 44, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769126321_6972b9b13e754.png', NULL, NULL, NULL, '261623.25', '{}', '2026-01-22 23:58:41', '2026-01-22 23:58:41'),
(163, 7, 42, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769127876_6972bfc4d6614.png', NULL, NULL, NULL, '211743.00', NULL, '2026-01-23 00:24:36', '2026-01-23 00:24:36'),
(164, 8, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769131652_6972ce849496d.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 01:27:32', '2026-01-23 01:27:32'),
(165, 8, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769131743_6972cedfc95a4.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 01:29:03', '2026-01-23 01:29:03'),
(166, 8, 40, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769137522_6972e57250bd8.png', NULL, NULL, NULL, '271073.25', NULL, '2026-01-23 03:05:22', '2026-01-23 03:05:22'),
(167, 8, 44, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769146441_697308498664e.png', NULL, NULL, NULL, '261623.25', NULL, '2026-01-23 05:34:01', '2026-01-23 05:34:01'),
(168, 8, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769146793_697309a9d89db.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-23 05:39:53', '2026-01-23 05:39:53'),
(169, 8, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769148634_697310da72d33.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 06:10:34', '2026-01-23 06:10:34'),
(170, 8, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769148661_697310f5783f1.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-23 06:11:01', '2026-01-23 06:11:01'),
(171, 7, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Dark/Black', 'None', 'uploads/designs/design_7_1769153163_6973228bcaee4.png', NULL, NULL, NULL, '166082.00', NULL, '2026-01-23 07:26:03', '2026-01-23 07:26:03'),
(172, 7, 33, '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Dark/Black', 'None', 'uploads/designs/design_7_1769153183_6973229f67ad8.png', NULL, NULL, NULL, '166082.00', NULL, '2026-01-23 07:26:23', '2026-01-23 07:26:23'),
(173, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769153204_697322b47cbc7.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 07:26:44', '2026-01-23 07:26:44'),
(174, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769163109_697349651ee38.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 10:11:49', '2026-01-23 10:11:49'),
(175, 7, 44, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769163113_69734969507d3.png', NULL, NULL, NULL, '261623.25', NULL, '2026-01-23 10:11:53', '2026-01-23 10:11:53'),
(176, 7, 39, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769169259_6973616b99c20.png', NULL, NULL, NULL, '237911.63', NULL, '2026-01-23 11:54:19', '2026-01-23 11:54:19'),
(177, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769169415_69736207efd18.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 11:56:55', '2026-01-23 11:56:55'),
(178, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769171341_6973698d18b2d.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 12:29:01', '2026-01-23 12:29:01'),
(179, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769171625_69736aa935f9e.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 12:33:45', '2026-01-23 12:33:45'),
(180, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769172515_69736e2320c23.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 12:48:35', '2026-01-23 12:48:35'),
(181, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769172943_69736fcfbb6b1.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 12:55:43', '2026-01-23 12:55:43'),
(182, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769173137_697370911bd92.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 12:58:57', '2026-01-23 12:58:57'),
(183, 7, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769173534_6973721e6f43c.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-23 13:05:34', '2026-01-23 13:05:34'),
(184, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769173747_697372f37b40c.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 13:09:07', '2026-01-23 13:09:07'),
(185, 7, 44, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769173877_697373757818a.png', NULL, NULL, NULL, '261623.25', NULL, '2026-01-23 13:11:17', '2026-01-23 13:11:17'),
(186, 7, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769174129_697374719c782.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-23 13:15:29', '2026-01-23 13:15:29'),
(187, 7, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769174332_6973753cc4507.png', NULL, NULL, NULL, '286114.50', '{}', '2026-01-23 13:18:52', '2026-01-23 13:18:52'),
(188, 8, 33, '50in x 50in', 'Square', NULL, NULL, NULL, 'Black frame', 'None', 'uploads/designs/design_8_1769229363_69744c335c78b.png', NULL, NULL, NULL, '201199.00', NULL, '2026-01-24 04:36:03', '2026-01-24 04:36:03'),
(189, 8, 33, '45in x 45in', 'Square', NULL, NULL, NULL, 'Black frame', 'None', 'uploads/designs/design_8_1769230144_69744f40e2331.png', NULL, NULL, NULL, '165532.00', NULL, '2026-01-24 04:49:04', '2026-01-24 04:49:04'),
(190, 8, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769231062_697452d6b3066.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-24 05:04:22', '2026-01-24 05:04:22'),
(191, 8, 32, '45in x 35in', NULL, NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_8_1769247005_6974911d516d8.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-24 09:30:05', '2026-01-24 09:30:05'),
(192, 8, 32, '45in x 35in', NULL, NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_8_1769247226_697491facbc1c.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-24 09:33:46', '2026-01-24 09:33:46'),
(193, 8, 31, '45in x 35in', NULL, 'Reflective: Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769247549_6974933d4719d.png', NULL, NULL, NULL, '252000.00', NULL, '2026-01-24 09:39:09', '2026-01-24 09:39:09'),
(194, 8, 32, '45in x 35in', NULL, NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_8_1769247829_6974945508230.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-24 09:43:49', '2026-01-24 09:43:49'),
(195, 8, 32, '45in x 35in', NULL, NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_8_1769249352_69749a481a8f1.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-24 10:09:12', '2026-01-24 10:09:12'),
(196, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769366114_69766262eafce.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-25 18:35:14', '2026-01-25 18:35:14'),
(197, 10, 40, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769366286_6976630e152ae.png', NULL, NULL, NULL, '271073.25', NULL, '2026-01-25 18:38:06', '2026-01-25 18:38:06'),
(198, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769366447_697663afd43ae.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-25 18:40:47', '2026-01-25 18:40:47'),
(199, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769367570_697668128cbd4.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-25 18:59:30', '2026-01-25 18:59:30'),
(200, 10, 40, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769369458_69766f7235fc6.png', NULL, NULL, NULL, '271073.25', NULL, '2026-01-25 19:30:58', '2026-01-25 19:30:58'),
(201, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769371406_6976770ede3ba.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-25 20:03:26', '2026-01-25 20:03:26'),
(202, 10, 40, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769373274_69767e5a0ae12.png', NULL, NULL, NULL, '271073.25', NULL, '2026-01-25 20:34:34', '2026-01-25 20:34:34'),
(203, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769375292_6976863c4d3a9.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-25 21:08:12', '2026-01-25 21:08:12'),
(204, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769375452_697686dc43cf2.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-25 21:10:52', '2026-01-25 21:10:52'),
(205, 10, 44, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769375470_697686eea6982.png', NULL, NULL, NULL, '261623.25', NULL, '2026-01-25 21:11:10', '2026-01-25 21:11:10'),
(206, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769375901_6976889d08587.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-25 21:18:21', '2026-01-25 21:18:21'),
(207, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769376154_6976899abc9c9.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-25 21:22:34', '2026-01-25 21:22:34'),
(208, 10, 42, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769376402_69768a9226b09.png', NULL, NULL, NULL, '211743.00', NULL, '2026-01-25 21:26:42', '2026-01-25 21:26:42'),
(209, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769376549_69768b259f56e.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-25 21:29:09', '2026-01-25 21:29:09'),
(210, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769376628_69768b747a00b.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-25 21:30:28', '2026-01-25 21:30:28'),
(211, 10, 40, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769377026_69768d02abec4.png', NULL, NULL, NULL, '271073.25', NULL, '2026-01-25 21:37:06', '2026-01-25 21:37:06'),
(212, 10, 41, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769377334_69768e3611c41.png', NULL, NULL, NULL, '301872.38', NULL, '2026-01-25 21:42:14', '2026-01-25 21:42:14'),
(213, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769378097_6976913140fb6.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-25 21:54:57', '2026-01-25 21:54:57'),
(214, 10, 41, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769378154_6976916a7eb7c.png', NULL, NULL, NULL, '301872.38', NULL, '2026-01-25 21:55:54', '2026-01-25 21:55:54'),
(215, 10, 42, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769379451_6976967b86922.png', NULL, NULL, NULL, '211743.00', NULL, '2026-01-25 22:17:31', '2026-01-25 22:17:31'),
(216, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769379524_697696c4c2844.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-25 22:18:44', '2026-01-25 22:18:44'),
(217, 10, 31, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769379583_697696ff949e7.png', NULL, NULL, NULL, '252000.00', NULL, '2026-01-25 22:19:43', '2026-01-25 22:19:43'),
(218, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769379944_69769868e6f3a.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-25 22:25:44', '2026-01-25 22:25:44'),
(219, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769385248_6976ad2027da4.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-25 23:54:08', '2026-01-25 23:54:08'),
(220, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769386133_6976b095a6cba.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-26 00:08:53', '2026-01-26 00:08:53'),
(221, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769386143_6976b09faa0e4.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-26 00:09:03', '2026-01-26 00:09:03'),
(222, 10, 31, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769392928_6976cb204912d.png', NULL, NULL, NULL, '252000.00', NULL, '2026-01-26 02:02:08', '2026-01-26 02:02:08'),
(223, 10, 31, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769393014_6976cb7648008.png', NULL, NULL, NULL, '252000.00', NULL, '2026-01-26 02:03:34', '2026-01-26 02:03:34'),
(224, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769393680_6976ce1088fa0.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-26 02:14:40', '2026-01-26 02:14:40'),
(225, 10, 31, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769393878_6976ced623140.png', NULL, NULL, NULL, '252000.00', NULL, '2026-01-26 02:17:58', '2026-01-26 02:17:58'),
(226, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769394223_6976d02f5431e.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-26 02:23:43', '2026-01-26 02:23:43'),
(227, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769394563_6976d1837c899.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-26 02:29:23', '2026-01-26 02:29:23'),
(228, 10, 31, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769394756_6976d244a9c11.png', NULL, NULL, NULL, '252000.00', NULL, '2026-01-26 02:32:36', '2026-01-26 02:32:36'),
(229, 10, 44, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769396167_6976d7c7401f4.png', NULL, NULL, NULL, '261623.25', NULL, '2026-01-26 02:56:07', '2026-01-26 02:56:07'),
(230, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769398055_6976df2730fea.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-26 03:27:35', '2026-01-26 03:27:35'),
(231, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769398226_6976dfd2b07c3.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-26 03:30:26', '2026-01-26 03:30:26'),
(232, 10, 41, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769398288_6976e010c50e4.png', NULL, NULL, NULL, '301872.38', NULL, '2026-01-26 03:31:28', '2026-01-26 03:31:28'),
(233, 10, 41, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769398798_6976e20e8ec4e.png', NULL, NULL, NULL, '301872.38', NULL, '2026-01-26 03:39:58', '2026-01-26 03:39:58'),
(234, 10, 44, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769399099_6976e33b14492.png', NULL, NULL, NULL, '261623.25', NULL, '2026-01-26 03:44:59', '2026-01-26 03:44:59'),
(235, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769399394_6976e462b4e05.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-26 03:49:54', '2026-01-26 03:49:54'),
(236, 10, 41, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769399886_6976e64ed39b3.png', NULL, NULL, NULL, '301872.38', NULL, '2026-01-26 03:58:06', '2026-01-26 03:58:06'),
(237, 10, 42, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769400474_6976e89ac39ef.png', NULL, NULL, NULL, '211743.00', NULL, '2026-01-26 04:07:54', '2026-01-26 04:07:54'),
(238, 10, 40, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769400983_6976ea97e0cb8.png', NULL, NULL, NULL, '271073.25', NULL, '2026-01-26 04:16:23', '2026-01-26 04:16:23'),
(239, 10, 41, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769401593_6976ecf9be8d3.png', NULL, NULL, NULL, '301872.38', NULL, '2026-01-26 04:26:33', '2026-01-26 04:26:33'),
(240, 10, 43, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769401780_6976edb422e94.png', NULL, NULL, NULL, '286114.50', NULL, '2026-01-26 04:29:40', '2026-01-26 04:29:40'),
(241, 10, 44, '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769404214_6976f7365d190.png', NULL, NULL, NULL, '261623.25', NULL, '2026-01-26 05:10:14', '2026-01-26 05:10:14'),
(242, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769404786_6976f9722caf2.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-26 05:19:46', '2026-01-26 05:19:46'),
(243, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769405405_6976fbddf2238.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-26 05:30:05', '2026-01-26 05:30:05'),
(244, 10, 31, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769405422_6976fbeeed36d.png', NULL, NULL, NULL, '252000.00', NULL, '2026-01-26 05:30:22', '2026-01-26 05:30:22'),
(245, 10, 32, '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769411694_6977146e417d6.png', NULL, NULL, NULL, '261772.88', NULL, '2026-01-26 07:14:54', '2026-01-26 07:14:54'),
(246, 8, 7, '45in x 45in', 'Custom shapes', NULL, NULL, 'Raw', NULL, 'None', 'uploads/designs/design_8_1769417648_69772bb0a833c.png', NULL, NULL, NULL, '30375.00', NULL, '2026-01-26 08:54:08', '2026-01-26 08:54:08'),
(247, 8, 2, '45in x 35in', NULL, 'Tempered', '6mm', NULL, 'Analok', 'None', 'uploads/designs/design_8_1769420742_697737c645763.png', NULL, NULL, NULL, '63000.00', NULL, '2026-01-26 09:45:42', '2026-01-26 09:45:42'),
(248, 8, 1, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_8_1769421265_697739d15bbc8.png', NULL, NULL, NULL, '67725.00', NULL, '2026-01-26 09:54:25', '2026-01-26 09:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `customization_field_configs`
--

CREATE TABLE `customization_field_configs` (
  `ConfigID` int(11) NOT NULL,
  `Category` varchar(100) NOT NULL,
  `Subcategory` varchar(100) NOT NULL,
  `FieldKey` varchar(200) NOT NULL COMMENT 'Unique key: Category_Subcategory',
  `FieldConfig` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON array of field definitions',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customization_field_configs`
--

INSERT INTO `customization_field_configs` (`ConfigID`, `Category`, `Subcategory`, `FieldKey`, `FieldConfig`, `Created_Date`, `Updated_Date`) VALUES
(1, 'Windows', 'Sliding', 'Windows_Sliding', '[{\"type\":\"tags\",\"label\":\"Number of Panels\",\"id\":\"numberOfPanels\",\"options\":[\"2 Panels\",\"4 Panels\"],\"stepNumber\":1},\r\n    {\"type\":\"tags\",\"label\":\"Transom Type (Top / Bottom Fixed Panel)\",\"id\":\"transomType\",\"options\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\"],\"stepNumber\":1},\r\n    {\"type\":\"tags\",\"label\":\"Track System (Sliding Rail Count)\",\"id\":\"trackSystem\",\"options\":[\"2 Tracks\",\"3 Tracks\"],\"stepNumber\":2},\r\n    {\"type\":\"tags\",\"label\":\"Panel Configuration\",\"id\":\"panelConfiguration\",\"options\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"stepNumber\":2},\r\n    {\"type\":\"tags\",\"label\":\"Frame Color\",\"id\":\"frameColor\",\"options\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"stepNumber\":3},\r\n    {\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"stepNumber\":3},\r\n    {\"type\":\"tags\",\"label\":\"Glass Color\",\"id\":\"glassColor\",\"options\":[\"Clear\",\"Bronze\",\"Frosted\",\"Smoked\"],\"stepNumber\":3},\r\n    {\"type\":\"tags\",\"label\":\"Glass Thickness\",\"id\":\"glassThickness\",\"options\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"stepNumber\":3},\r\n    {\"type\":\"tags\",\"label\":\"Lock Type\",\"id\":\"lockType\",\"options\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"stepNumber\":4},\r\n    {\"type\":\"tags\",\"label\":\"Roller Type\",\"id\":\"rollerType\",\"options\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"],\"stepNumber\":4},\r\n    {\"type\":\"tags\",\"label\":\"Screen\",\"id\":\"screen\",\"options\":[\"With Screen\",\"Without Screen\"],\"stepNumber\":4}]', '2026-01-14 12:14:23', '2026-01-20 22:48:37'),
(2, 'Windows', 'Awning', 'Windows_Awning', '[{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"stepNumber\":1},\r\n    {\"type\":\"tags\",\"label\":\"Glass Color\",\"id\":\"glassColor\",\"options\":[\"Clear\",\"Bronze\",\"Frosted\",\"Smoked\"],\"stepNumber\":1},\r\n    {\"type\":\"tags\",\"label\":\"Frame Color/Material\",\"id\":\"frameColor\",\"options\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"stepNumber\":1},\r\n    {\"type\":\"tags\",\"label\":\"Operation\",\"id\":\"operation\",\"options\":[\"Awning (crank-out)\",\"Awning (push-out)\"],\"stepNumber\":1},\r\n    {\"type\":\"tags\",\"label\":\"Opening Direction\",\"id\":\"openingDirection\",\"options\":[\"Top-hinged\"],\"stepNumber\":2},\r\n    {\"type\":\"tags\",\"label\":\"Thickness (mm)\",\"id\":\"thickness\",\"options\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"stepNumber\":2},\r\n    {\"type\":\"tags\",\"label\":\"Screen\",\"id\":\"screen\",\"options\":[\"With Screen\",\"Without Screen\"],\"stepNumber\":2}]', '2026-01-21 02:11:42', '2026-01-21 17:04:48'),
(4, 'Mirrors & Specialty Glass', 'Glass Board', 'Specialty_Glass Board', '[{\"type\":\"tags\",\"label\":\"Shape\",\"id\":\"shape\",\"options\":[\"Round\",\"Rectangle\",\"Oval\"]},{\"type\":\"tags\",\"label\":\"Edge Finish\",\"id\":\"edgeFinish\",\"options\":[\"Beveled\",\"Polished\",\"Raw\"]},{\"type\":\"tags\",\"label\":\"Mounting Method\",\"id\":\"mountingMethod\",\"options\":[\"Wall-mounted\",\"Stand\",\"Adhesive\"]}]', '2026-01-14 15:30:55', '2026-01-14 15:30:55'),
(5, 'Mirrors & Specialty Glass', 'Mirrors', 'Specialty_Mirrors', '[{\"type\":\"tags\",\"label\":\"Shape\",\"id\":\"shape\",\"options\":[\"Round\",\"Rectangle\",\"Oval\",\"Circle\",\"Square\",\"Rectangular with rounded edges\",\"Rectangular with arched top\",\"Custom shapes\"]},{\"type\":\"number\",\"label\":\"Corner Radius (in)\",\"id\":\"cornerRadius\",\"min\":0,\"step\":0.1},{\"type\":\"tags\",\"label\":\"Frame Type\",\"id\":\"frameType\",\"options\":[\"Frameless\",\"Framed\",\"Gold frame\",\"Black frame\",\"White frame\",\"Framed (thin, metallic)\",\"Framed (dark, possibly black, grid frame)\",\"Framed (gold frame shown)\",\"Framed (thin matching frame possible)\"]},{\"type\":\"tags\",\"label\":\"Frame Material\\/Color\",\"id\":\"frameColor\",\"options\":[\"Gold frame\",\"Silver\",\"Rose Gold\",\"Other metallic finishes\",\"Wood\",\"Colored frames\",\"Black frame\",\"Other metallic or matte colors\",\"White frame\",\"Other colors\",\"Metal\",\"Silver\\/Metallic\",\"Other options\",\"Dark\\/Black\",\"Other frame colors available\"],\"stepNumber\":1,\"step\":1},{\"type\":\"tags\",\"label\":\"Edge Finish\",\"id\":\"edgeFinish\",\"stepNumber\":2,\"options\":[\"Beveled\",\"Polished\",\"Raw\",\"Beveled edge\",\"Flat polished edge\",\"Pencil edge\",\"Standard polished edge\",\"Standard (behind frame)\",\"Rounded edges\"]},{\"type\":\"tags\",\"label\":\"Lighting\",\"id\":\"lighting\",\"stepNumber\":4,\"options\":[\"Integrated LED lighting\",\"Backlighting\",\"Front lighting\",\"Integrated LED options\"]},{\"type\":\"tags\",\"label\":\"LED Color\\/Temperature\",\"id\":\"ledColorTemperature\",\"stepNumber\":4,\"options\":[\"Warm white\",\"Cool white\",\"Tunable white\",\"RGB\"]},{\"type\":\"tags\",\"label\":\"Mounting Method\",\"id\":\"mountingMethod\",\"stepNumber\":3,\"options\":[\"Wall-mounted\",\"Stand\",\"Adhesive\",\"Leaning\",\"Wall-mounted (often fixed above vanity)\",\"Fixed wall mount\",\"Integrated hanger\",\"Rope hanger\",\"Chain\"]},{\"type\":\"tags\",\"label\":\"Control\",\"id\":\"control\",\"stepNumber\":3,\"options\":[\"Touch sensor button\",\"Dimmer\",\"Defogger\"]},{\"type\":\"tags\",\"label\":\"Additional Features\",\"id\":\"additionalFeatures\",\"stepNumber\":3,\"options\":[\"Defogger\",\"Dimmer\"]},{\"type\":\"tags\",\"label\":\"Tint\\/Finish\",\"id\":\"tintFinish\",\"stepNumber\":2,\"options\":[\"Bronze tint\\/color\",\"Grey tint (smoked)\",\"Colored glass\"]},{\"type\":\"tags\",\"label\":\"Orientation\",\"id\":\"orientation\",\"stepNumber\":2,\"options\":[\"Vertical\",\"Horizontal\",\"Vertical\\/Full-body\"]},{\"type\":\"tags\",\"label\":\"Style\",\"id\":\"style\",\"stepNumber\":2,\"options\":[\"French Type (grid\\/paneled design)\"]},{\"type\":\"tags\",\"label\":\"Grid Pattern\",\"id\":\"gridPattern\",\"stepNumber\":4,\"options\":[\"French window style grid\"]},{\"type\":\"tags\",\"label\":\"Quantity\",\"id\":\"quantity\",\"stepNumber\":4,\"options\":[\"Available in sets (3 sets, or individually)\"]},{\"type\":\"tags\",\"label\":\"Arrangement\",\"id\":\"arrangement\",\"stepNumber\":3,\"options\":[\"Can be displayed as triptych\",\"Individually\"]}]', '2026-01-14 15:52:15', '2026-01-19 12:10:25'),
(6, 'Glass Partitions & Enclosures', 'Frameless Glass', 'Partitions_Frameless Glass', '[{\"type\":\"tags\",\"label\":\"Layout\",\"id\":\"layout\",\"options\":[\"L-shape\",\"Straight\",\"U-shape\",\"L-type\",\"Neo-angle\",\"Square\",\"Bay\",\"Other corner layouts\"],\"stepNumber\":1,\"step\":1},{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Clear\",\"Frosted\",\"Tinted\",\"Frosted (full or partial)\",\"Clear with frosted sticker\",\"Fully frosted\"],\"stepNumber\":1,\"step\":1},{\"type\":\"tags\",\"label\":\"Finish\",\"id\":\"finish\",\"options\":[\"Clear\",\"Frosted\",\"Patterned\"],\"stepNumber\":1,\"step\":1},{\"type\":\"number\",\"label\":\"Glass Thickness (mm)\",\"id\":\"glassThickness\",\"min\":1,\"step\":1,\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Hardware Color\",\"id\":\"hardwareColor\",\"stepNumber\":2,\"options\":[\"Black\",\"Silver\",\"Gold\",\"White\",\"Bronze\",\"Chrome\\/Stainless Steel\",\"Black Matte\",\"Brushed Nickel\",\"Stainless Steel\"],\"step\":2},{\"type\":\"tags\",\"label\":\"Mounting Hardware\",\"id\":\"mountingHardware\",\"stepNumber\":2,\"options\":[\"Stainless Fixed Bracket\",\"Gold U-Channel\",\"Analok U-Channel (anodized aluminum)\",\"Stainless U-Channel\",\"Other bracket types\",\"Standard mounting\"],\"step\":2},{\"type\":\"tags\",\"label\":\"Configuration\",\"id\":\"configuration\",\"options\":[\"Single partition\",\"Multiple partitions\",\"2 fixed panels\",\"3 fixed panels\",\"Custom configurations\"],\"stepNumber\":2,\"step\":2}]', '2026-01-15 06:26:57', '2026-01-15 06:26:57'),
(7, 'Glass Partitions & Enclosures', 'Shower Enclosure', 'Partitions_Shower Enclosure', '[{\"type\":\"tags\",\"label\":\"Layout\",\"id\":\"layout\",\"options\":[\"L-shape\",\"Straight\",\"U-shape\",\"L-type\",\"Neo-angle\",\"Square\",\"Bay\",\"Other corner layouts\"],\"stepNumber\":1,\"step\":1},{\"type\":\"tags\",\"label\":\"Configuration\",\"id\":\"configuration\",\"options\":[\"Fixed and swing\",\"Swing with small fixed glass\",\"Single sliding door\",\"Double sliding doors\",\"Sliding with fixed panels\",\"Single sliding\",\"Double sliding\",\"With fixed panels\",\"2 fixed panels\",\"3 fixed panels\",\"Custom configurations\"],\"stepNumber\":2,\"step\":2},{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Clear\",\"Frosted\",\"Tinted\",\"Frosted (full or partial)\",\"Clear with frosted sticker\",\"Fully frosted\",\"Custom frosting patterns\",\"Frosted (full or partial with custom patterns\\/heights)\"],\"stepNumber\":1,\"step\":1},{\"type\":\"tags\",\"label\":\"Glass Treatment\",\"id\":\"glassTreatment\",\"options\":[\"Frosted sticker (customizable patterns, opacity, colors)\",\"Clear\",\"Custom patterns\",\"Heights (top clear, bottom frosted)\",\"Colors\"],\"stepNumber\":1,\"step\":1},{\"type\":\"number\",\"label\":\"Glass Thickness (mm)\",\"id\":\"glassThickness\",\"min\":1,\"step\":1,\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Hardware Finish\",\"id\":\"hardwareColor\",\"stepNumber\":2,\"options\":[\"Chrome\\/Stainless Steel\",\"Black Matte\",\"Gold\",\"Brushed Nickel\",\"Polished Chrome\\/Stainless Steel\",\"Matte Black (handles, hinges, connectors)\",\"Matte Black (rail, rollers, handles)\",\"Matte Black (hinges, handle, top bracing bar)\",\"Stainless Steel\",\"Black\",\"Silver\",\"Bronze\"],\"step\":2},{\"type\":\"tags\",\"label\":\"Door Swing\",\"id\":\"doorSwing\",\"options\":[\"Left-hinged\",\"Right-hinged\",\"Left swing\",\"Right swing\"],\"stepNumber\":2,\"step\":2},{\"type\":\"tags\",\"label\":\"Mounting\",\"id\":\"mounting\",\"options\":[\"Standard mounting\",\"Custom mounting methods\"],\"stepNumber\":2,\"step\":2},{\"type\":\"tags\",\"label\":\"Handle Style\",\"id\":\"handleStyle\",\"options\":[\"Various pull handle designs\",\"Various pull handles\",\"Knob handles\",\"Square handles\",\"Square matte black\",\"Round\",\"Bar-style\"],\"stepNumber\":2,\"step\":2}]', '2026-01-15 06:27:58', '2026-01-14 23:28:13'),
(12, 'Windows', 'Casement', 'Windows_Casement', '[{\"type\":\"tags\",\"label\":\"Transom Type\",\"id\":\"transomType\",\"options\":[\"Casement w\\/FTH\",\"Casement w\\/FTS\",\"None\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Panel Configuration\",\"id\":\"panelConfiguration\",\"options\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"stepNumber\":1},{\"type\":\"dimensions\",\"label\":\"Dimensions\",\"id\":\"dimensions\",\"stepNumber\":1,\"fields\":[\"width\",\"height\",\"h1\"],\"h1Conditional\":{\"dependsOn\":\"transomType\",\"showWhen\":[\"Casement w\\/FTH\",\"Casement w\\/FTS\"]}},{\"type\":\"tags\",\"label\":\"Frame Color\",\"id\":\"frameColor\",\"options\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Glass Color\",\"id\":\"glassColor\",\"options\":[\"Clear\",\"Bronze\",\"Frosted\\/Smoked\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Thickness\",\"id\":\"thickness\",\"options\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"stepNumber\":2}]', '2026-01-21 23:46:55', '2026-01-26 05:29:08'),
(13, 'Mirrors & Specialty Glass', 'Top Glass', 'Specialty_Top Glass', '[{\"type\":\"tags\",\"label\":\"Shape\",\"id\":\"shape\",\"options\":[\"Round\",\"Rectangle\",\"Oval\",\"Square\",\"Custom shapes\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Edge Finish\",\"id\":\"edgeFinish\",\"options\":[\"Beveled\",\"Polished\",\"Raw\",\"Beveled edge\",\"Flat polished edge\",\"Pencil edge\"],\"stepNumber\":1},{\"type\":\"number\",\"label\":\"Corner Radius (in)\",\"id\":\"cornerRadius\",\"min\":0,\"step\":0.1,\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Mounting Method\",\"id\":\"mountingMethod\",\"options\":[\"Wall-mounted\",\"Stand\",\"Adhesive\"],\"stepNumber\":2}]', '2026-01-22 07:43:57', '2026-01-22 07:43:57');

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
-- Table structure for table `employee_archive`
--

CREATE TABLE `employee_archive` (
  `ArchiveID` int(11) NOT NULL,
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
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When this employee was archived'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_archive`
--

INSERT INTO `employee_archive` (`ArchiveID`, `UserID`, `First_Name`, `Last_Name`, `Middle_Name`, `Email`, `Password`, `PhoneNum`, `ImageUrl`, `Role`, `Status`, `Date_Created`, `Date_Updated`, `Last_Active`, `ArchivedAt`) VALUES
(6, 10, 'Mel', 'Asuna', NULL, 'hdia131ha@gmail.om', '$2y$10$hpUIPjRkL4inx79B4WCF5.Ovluzs95v9ZOXrpXwfK6J.s8tV1bQgm', '09111111111', NULL, 'Sales Representative', 'Active', '2026-01-15 22:22:52', '2026-01-15 15:23:13', NULL, '2026-01-15 15:34:43');

-- --------------------------------------------------------

--
-- Table structure for table `enduser_archive`
--

CREATE TABLE `enduser_archive` (
  `ArchiveID` int(11) NOT NULL,
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
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When this user was archived'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enduser_archive`
--

INSERT INTO `enduser_archive` (`ArchiveID`, `UserID`, `First_Name`, `Last_Name`, `Middle_Name`, `Email`, `Password`, `PhoneNum`, `ImageUrl`, `Role`, `Status`, `Date_Created`, `Date_Updated`, `Last_Active`, `ArchivedAt`) VALUES
(4, 8, 'Aggg', 'Pau', '', 'cheezygrizzoverload@gmail.com', '$2y$10$Tv4yP5VV/uM7TeMIstAj3.1dwJ2xmfwCr7CUM4DBgOrGwoTf6G2Du', '09111111123', NULL, 'Customer', 'Active', '2026-01-15 19:36:07', '2026-01-15 15:02:35', NULL, '2026-01-15 15:23:53'),
(5, 11, 'Ag', 'Pauig', NULL, 'cheezygrizzoverload@gmail.com', '$2y$10$03udYierB8KDQ6q7Cpo/zuehzMZdbYkuPl752a.VA73duBmG/KT9G', '0915214421114', NULL, 'Customer', 'Inactive', '2026-01-15 22:42:00', '2026-01-15 22:42:00', NULL, '2026-01-15 15:44:12'),
(6, 6, 'Agg', 'Pauig', NULL, 'paaopaj121@gmail.com', '$2y$10$amR.7FIA0c7GzkeqYAF5HO2VoGdHuTBH5f0I8qtZZRZo1rX8OAySG', '09111113222', NULL, 'Customer', 'Inactive', '2026-01-21 08:11:15', '2026-01-21 08:11:15', NULL, '2026-01-21 01:11:44'),
(7, 7, 'Agg', 'Pauig', NULL, 'paaopaj121@gmail.com', '$2y$10$1ey.HyL37ec7qcTxKuXxSey0mVrvNoCK8IzH1wCWN5ZCzjcVdZok6', '09614788448', NULL, 'Customer', 'Inactive', '2026-01-21 08:11:58', '2026-01-21 08:11:58', NULL, '2026-01-21 01:12:35'),
(8, 8, 'Agg', 'Pauig', NULL, 'aghii127@gmail.com', '$2y$10$lAvN.Di7dZPBSVV2QtGx9Oj9QKoLp35LSUh4UDik86C0ZC.dSjcka', '09614788448', NULL, 'Customer', 'Inactive', '2026-01-21 08:13:07', '2026-01-21 08:13:07', NULL, '2026-01-21 01:14:14');

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
  `Priority` enum('Low','Medium','High') DEFAULT 'Low',
  `FileAttached` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issuereport`
--

INSERT INTO `issuereport` (`Issue_ID`, `Customer_ID`, `Order_ID`, `First_Name`, `Last_Name`, `Email`, `PhoneNum`, `Category`, `Description`, `Report_Date`, `Status`, `Priority`, `FileAttached`) VALUES
(1, 2, NULL, 'Samantha', 'Panilio', 'lerumgops@gmail.com', '09328765983', 'Product Defect/Damage', 'agsgdgfhfdgdfsdfsfewfwefwefwef', '2025-12-08 11:36:02', 'Open', 'Low', NULL),
(2, 2, NULL, 'Meryl', 'Colby', 'lerumgops@gmail.com', '09120844695', 'Delivery Issue', 'testing testing lang', '2026-01-10 00:18:02', 'Open', 'Low', NULL),
(3, NULL, NULL, 'AG', 'Pauig', 'cheezygrizzoverload@gmail.com', '09111111111', 'Payment Issue', 'I woulld like to inquire about my payment. Still not going in yet.', '2026-01-15 22:20:22', 'Open', 'Low', 'uploads/issues/f1dcbe4fdc53a181b95f99f13c4b8c31.png');

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
  `Status` enum('Pending Review','Awaiting Admin','Ready to Approve','Approved','Ocular Pending','Disapproved','In Fabrication','Ready for Installation','Completed','Cancelled','Returned') DEFAULT 'Pending Review',
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
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `OrderType` enum('Direct','Site-Assessed') DEFAULT 'Direct' COMMENT 'Order type: Direct or Site-Assessed',
  `OcularCompleted` tinyint(1) DEFAULT 0 COMMENT 'Flag indicating if ocular/site assessment is completed',
  `OcularNotes` text DEFAULT NULL COMMENT 'Site assessment notes and measurements',
  `OcularCompletedBy_ID` int(11) DEFAULT NULL COMMENT 'Staff who completed ocular assessment',
  `FabricationStaff_ID` int(11) DEFAULT NULL COMMENT 'Assigned fabrication staff member',
  `InstallationStaff_ID` int(11) DEFAULT NULL COMMENT 'Assigned installation staff member',
  `FabricationStartDate` date DEFAULT NULL COMMENT 'Expected start date of fabrication',
  `FabricationEndDate` date DEFAULT NULL COMMENT 'Expected end date of fabrication',
  `ActualFabricationStartDate` date DEFAULT NULL COMMENT 'Actual start date when fabrication began',
  `ActualFabricationEndDate` date DEFAULT NULL COMMENT 'Actual end date when fabrication completed',
  `FabricationProgress` int(11) DEFAULT 0 COMMENT 'Fabrication progress percentage (0-100)',
  `FabricationStatus` enum('Queued','In Progress','Quality Check','Ready','Completed') DEFAULT 'Queued' COMMENT 'Fabrication queue status',
  `FabricationNotes` text DEFAULT NULL COMMENT 'Production and fabrication notes',
  `QualityCheckNotes` text DEFAULT NULL COMMENT 'Quality check notes',
  `AdminNotes` text DEFAULT NULL COMMENT 'Internal admin notes',
  `CustomerNotes` text DEFAULT NULL COMMENT 'Customer-facing notes',
  `StaffNotes` text DEFAULT NULL COMMENT 'Staff-specific notes',
  `Barcode` varchar(100) DEFAULT NULL COMMENT 'Order barcode/QR code',
  `BarcodeImagePath` varchar(255) DEFAULT NULL COMMENT 'Path to barcode image'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`OrderID`, `OrderNumber`, `Customer_ID`, `SalesRep_ID`, `OrderDate`, `TotalAmount`, `Status`, `PaymentStatus`, `PaymentMethod`, `DeliveryAddress`, `SpecialInstructions`, `QuotationPDFUrl`, `ContractPDFUrl`, `ApprovedBy_SalesRep_ID`, `ApprovedBy_Admin_ID`, `Approved_Date`, `DisapprovedBy`, `DisapprovedBy_ID`, `DisapprovalReason`, `Disapproved_Date`, `CustomerNotified`, `CustomerNotified_Date`, `PreferredInstallationDate`, `OcularDate`, `FabricationDate`, `InstallationDate`, `EstimatedDelivery`, `Created_Date`, `Updated_Date`, `OrderType`, `OcularCompleted`, `OcularNotes`, `OcularCompletedBy_ID`, `FabricationStaff_ID`, `InstallationStaff_ID`, `FabricationStartDate`, `FabricationEndDate`, `ActualFabricationStartDate`, `ActualFabricationEndDate`, `FabricationProgress`, `FabricationStatus`, `FabricationNotes`, `QualityCheckNotes`, `AdminNotes`, `CustomerNotes`, `StaffNotes`, `Barcode`, `BarcodeImagePath`) VALUES
(1, 'GI001', 3, 3, '2026-01-18 20:07:38', '10332105.00', 'Ready for Installation', 'Pending', NULL, '4145, Philippines, 1111', 'Preferred Installation Date: January 29, 2026', NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-26', NULL, '2026-01-18 20:07:38', '2026-01-23 20:24:19', 'Direct', 0, NULL, NULL, 2, NULL, NULL, NULL, NULL, '2026-01-24', 100, 'Completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'GI002', 3, 3, '2026-01-19 22:06:54', '158903.00', 'In Fabrication', 'Paid', NULL, '4145, Caloocan, Metro Manila, Philippines, 1111', 'Preferred Installation Date: January 27, 2026', NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-19 22:06:54', '2026-01-24 03:36:12', 'Direct', 0, NULL, NULL, NULL, NULL, '2026-01-24', '2026-01-29', NULL, NULL, 25, 'In Progress', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'GI003', 3, 3, '2026-01-19 23:14:30', '158995.00', 'Approved', 'Pending', NULL, '4145, Caloocan, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-19 23:14:30', '2026-01-24 03:36:07', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'GI004', 3, 3, '2026-01-19 23:31:49', '35.00', 'In Fabrication', 'Pending', NULL, '4145, Caloocan, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-19 23:31:49', '2026-01-24 03:30:17', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50, 'Quality Check', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'GI005', 3, 3, '2026-01-20 00:14:50', '35.00', 'In Fabrication', 'Pending', NULL, '4145, Caloocan, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 00:14:50', '2026-01-24 03:30:18', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50, 'Quality Check', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'GI006', 3, 3, '2026-01-20 01:04:50', '261807.88', 'Cancelled', 'Pending', NULL, '4145, Caloocan, Metro Manila, Philippines, 1111', 'Preferred Ocular Visit Date: January 26, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 01:04:50', '2026-01-20 01:35:08', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'GI007', 3, 3, '2026-01-20 01:31:31', '252035.00', 'In Fabrication', 'Pending', NULL, '4145, Caloocan, Metro Manila, Philippines, 1111', 'Preferred Ocular Visit Date: January 24, 2026', NULL, NULL, NULL, NULL, '2026-01-20 02:39:16', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-01-21', NULL, NULL, NULL, '2026-01-20 01:31:31', '2026-01-24 03:39:41', 'Site-Assessed', 1, NULL, 2, NULL, NULL, '2026-01-24', '2026-02-08', NULL, NULL, 25, 'In Progress', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'GI008', 3, 3, '2026-01-20 01:37:10', '261807.88', 'Ocular Pending', 'Pending', NULL, '4145, Caloocan, Metro Manila, Philippines, 1111', 'Preferred Ocular Visit Date: January 26, 2026', NULL, NULL, NULL, 2, '2026-01-23 06:28:13', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 01:37:10', '2026-01-23 05:28:13', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'GI009', 3, 3, '2026-01-20 02:40:14', '159221.00', 'In Fabrication', 'Paid', '', '4145, Caloocan, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 02:40:14', '2026-01-23 05:27:34', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'GI010', 3, 3, '2026-01-20 03:06:43', '154015.00', 'Approved', 'Paid', '', '4145, Caloocan, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 03:06:43', '2026-01-24 03:36:07', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'GI011', 3, 3, '2026-01-20 03:07:25', '159143.00', 'Approved', 'Pending', '', '4145, Caloocan, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 03:07:25', '2026-01-24 03:36:08', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'GI012', 3, 3, '2026-01-20 03:14:14', '159131.00', 'In Fabrication', 'Pending', '', '4145, Caloocan, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 03:14:14', '2026-01-24 03:30:16', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50, 'Quality Check', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'GI013', 3, 3, '2026-01-20 03:21:16', '159123.00', 'In Fabrication', 'Pending', '', '4145, Caloocan, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 03:21:16', '2026-01-23 05:27:34', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'GI014', 3, 3, '2026-01-20 03:23:56', '159123.00', 'Approved', 'Pending', '', '4145, Caloocan, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 03:23:56', '2026-01-24 03:36:09', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'GI015', 3, 3, '2026-01-20 03:24:57', '159123.00', 'In Fabrication', 'Paid', '', '4145, Caloocan, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 03:24:57', '2026-01-23 05:27:34', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'GI016', 7, 3, '2026-01-21 08:18:34', '261807.88', 'Approved', 'Pending', NULL, '5311, Manila, Metro Manila, Philippines, 1123', 'Preferred Ocular Visit Date: January 26, 2026', NULL, NULL, NULL, NULL, '2026-01-21 09:19:09', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-01-23', NULL, NULL, NULL, '2026-01-21 08:18:34', '2026-01-24 03:39:39', 'Site-Assessed', 1, NULL, 2, NULL, NULL, '2026-01-24', '2026-01-31', NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'GI017', 7, 3, '2026-01-21 08:56:54', '318246.00', 'In Fabrication', 'Pending', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-21 08:56:54', '2026-01-24 03:30:28', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50, 'Quality Check', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'GI018', 7, 3, '2026-01-21 08:57:26', '318246.00', 'In Fabrication', 'Pending', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:27:34', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-21 08:57:26', '2026-01-24 03:30:24', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50, 'Quality Check', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'GI019', 7, 3, '2026-01-21 08:58:39', '318246.00', 'Approved', 'Paid', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-21 08:58:39', '2026-01-24 03:36:10', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'GI020', 7, 3, '2026-01-21 13:36:17', '158603.00', 'In Fabrication', 'Pending', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, 2, '2026-01-23 06:16:53', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-21 13:36:17', '2026-01-24 03:30:27', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50, 'Quality Check', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'GI021', 7, 3, '2026-01-21 13:40:16', '158995.00', 'In Fabrication', 'Pending', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, 2, '2026-01-23 06:00:37', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-21 13:40:16', '2026-01-23 05:27:34', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'GI022', 7, 3, '2026-01-21 13:41:25', '158995.00', 'In Fabrication', 'Pending', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, 2, '2026-01-21 15:14:41', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-21 13:41:25', '2026-01-24 03:36:00', 'Direct', 0, NULL, NULL, NULL, NULL, '2026-01-24', '2026-01-29', NULL, NULL, 25, 'In Progress', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'GI023', 7, 3, '2026-01-21 13:42:55', '158995.00', 'Ready for Installation', 'Paid', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, 2, '2026-01-21 14:48:52', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-01-21', NULL, '2026-01-23', NULL, '2026-01-21 13:42:55', '2026-01-21 07:21:35', 'Direct', 1, NULL, 2, 2, NULL, NULL, NULL, NULL, '2026-01-21', 100, 'Completed', 'Issues: ', NULL, NULL, NULL, NULL, NULL, NULL),
(24, 'GI024', 7, 3, '2026-01-21 13:53:48', '261807.88', 'In Fabrication', 'Pending', NULL, '2222, Quezon City, Metro Manila, Philippines, 4444', 'Preferred Ocular Visit Date: January 26, 2026', NULL, NULL, NULL, 2, '2026-01-21 14:54:04', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-01-23', NULL, NULL, NULL, '2026-01-21 13:53:48', '2026-01-23 10:13:28', 'Site-Assessed', 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 'GI025', 7, 3, '2026-01-23 00:24:45', '211778.00', 'Completed', 'Paid', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, 2, '2026-01-23 01:25:12', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-01-23', NULL, '2026-01-25', NULL, '2026-01-23 00:24:45', '2026-01-23 00:29:03', 'Direct', 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, '2026-01-23', 100, 'Completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 'GI026', 8, 3, '2026-01-23 01:29:59', '261807.88', 'Completed', 'Paid', '', '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', 'Preferred Ocular Visit Date: January 28, 2026', NULL, NULL, NULL, 2, '2026-01-23 02:52:39', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-01-23', NULL, '2026-01-25', NULL, '2026-01-23 01:29:59', '2026-01-23 01:55:24', 'Site-Assessed', 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, '2026-01-23', 100, 'Completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 'GI027', 8, 3, '2026-01-23 03:05:29', '271108.25', 'Ready for Installation', 'Paid', '', '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', NULL, NULL, NULL, NULL, 2, '2026-01-23 05:54:39', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 03:05:29', '2026-01-24 03:46:00', 'Direct', 0, NULL, NULL, 4, NULL, '2026-01-24', '2026-01-29', NULL, NULL, 75, 'Ready', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'GI028', 8, 3, '2026-01-23 06:10:48', '261807.88', '', 'Pending', NULL, '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', 'Preferred Ocular Visit Date: January 30, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 06:10:48', '2026-01-23 06:10:48', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 'GI029', 8, 3, '2026-01-23 06:11:11', '286149.50', '', 'Paid', '', '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 06:11:11', '2026-01-23 06:11:15', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 'GI030', 7, 3, '2026-01-23 11:53:52', '261807.88', '', 'Pending', NULL, '2222, Quezon City, Metro Manila, Philippines, 4444', 'Preferred Ocular Visit Date: January 28, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 11:53:52', '2026-01-23 11:53:52', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 'GI031', 7, 3, '2026-01-23 11:54:45', '237946.63', '', 'Paid', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 11:54:45', '2026-01-23 11:54:50', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 'GI032', 7, 3, '2026-01-23 12:10:14', '261807.88', '', 'Pending', NULL, '2222, Quezon City, Metro Manila, Philippines, 4444', 'Preferred Ocular Visit Date: January 30, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 12:10:14', '2026-01-23 12:10:14', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 'GI033', 7, 3, '2026-01-23 12:29:11', '261807.88', '', 'Pending', NULL, '2222, Quezon City, Metro Manila, Philippines, 4444', 'Preferred Ocular Visit Date: January 28, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 12:29:11', '2026-01-23 12:29:11', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 'GI034', 7, 3, '2026-01-23 13:05:52', '286149.50', '', 'Paid', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:05:52', '2026-01-23 13:05:56', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 'GI035', 7, 3, '2026-01-23 13:09:20', '261807.88', '', 'Pending', NULL, '2222, Quezon City, Metro Manila, Philippines, 4444', 'Preferred Ocular Visit Date: January 28, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:09:20', '2026-01-23 13:09:20', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 'GI036', 7, 3, '2026-01-23 13:11:59', '261658.25', '', 'Paid', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:11:59', '2026-01-23 13:12:03', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, 'GI037', 7, 3, '2026-01-23 13:15:45', '261807.88', 'Ocular Pending', 'Pending', NULL, '2222, Quezon City, Metro Manila, Philippines, 4444', 'Preferred Ocular Visit Date: January 28, 2026', NULL, NULL, NULL, 2, '2026-01-24 03:33:03', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:15:45', '2026-01-24 02:33:03', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'GI038', 7, 3, '2026-01-23 13:19:12', '286149.50', 'In Fabrication', 'Paid', '', '2222, Quezon City, Metro Manila, Philippines, 4444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:19:12', '2026-01-24 02:46:55', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 'GI039', 8, 3, '2026-01-24 04:37:32', '201234.00', '', 'Pending', '', '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-24 04:37:32', '2026-01-24 04:37:32', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 'GI040', 8, 3, '2026-01-24 04:38:08', '201234.00', 'Completed', 'Paid', '', '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-26', NULL, '2026-01-24 04:38:08', '2026-01-24 04:42:30', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-24', 100, 'Completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 'GI041', 8, 3, '2026-01-24 04:51:19', '165567.00', 'Completed', 'Paid', '', '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-26', NULL, '2026-01-24 04:51:19', '2026-01-24 05:03:53', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-24', 100, 'Completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 'GI042', 8, 3, '2026-01-24 05:04:43', '261807.88', 'Ready for Installation', 'Pending', NULL, '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', 'Preferred Ocular Visit Date: January 28, 2026', NULL, NULL, NULL, 2, '2026-01-24 06:05:16', NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-01-24', NULL, '2026-01-26', NULL, '2026-01-24 05:04:43', '2026-01-24 03:46:05', 'Site-Assessed', 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, '2026-01-24', 100, 'Completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(43, 'GI043', 8, 3, '2026-01-24 09:30:12', '261807.88', '', 'Pending', NULL, '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', 'Preferred Ocular Visit Date: January 28, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-24 09:30:12', '2026-01-24 09:30:12', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, 'GI044', 8, 3, '2026-01-24 09:33:55', '261807.88', '', 'Pending', NULL, '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', 'Preferred Ocular Visit Date: January 30, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-24 09:33:55', '2026-01-24 09:33:55', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(45, 'GI045', 8, 3, '2026-01-24 09:39:17', '252035.00', '', 'Pending', NULL, '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', 'Preferred Ocular Visit Date: January 28, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-24 09:39:17', '2026-01-24 09:39:17', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, 'GI046', 8, 3, '2026-01-24 09:44:01', '261807.88', '', 'Pending', NULL, '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', 'Preferred Ocular Visit Date: January 28, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-24 09:44:01', '2026-01-24 09:44:01', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(47, 'GI047', 8, 3, '2026-01-24 10:09:20', '261807.88', '', 'Pending', NULL, '6, Sesame St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1125', 'Preferred Ocular Visit Date: January 28, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-24 10:09:20', '2026-01-24 10:09:20', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 'GI048', 10, 3, '2026-01-25 18:38:31', '271108.25', '', 'Paid', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 18:38:31', '2026-01-25 18:38:40', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'GI049', 10, 3, '2026-01-25 18:42:39', '286149.50', '', 'Paid', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 18:42:39', '2026-01-25 18:42:45', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 'GI050', 10, 3, '2026-01-25 18:59:39', '286149.50', '', 'Pending', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 18:59:39', '2026-01-25 18:59:39', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(51, 'GI051', 10, 3, '2026-01-25 18:59:49', '286149.50', '', 'Pending', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 18:59:49', '2026-01-25 18:59:49', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 'GI052', 10, 3, '2026-01-25 19:02:46', '286149.50', '', 'Pending', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 19:02:46', '2026-01-25 19:02:46', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, 'GI053', 10, 3, '2026-01-25 19:05:14', '286149.50', '', 'Pending', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 19:05:14', '2026-01-25 19:05:14', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, 'GI054', 10, 3, '2026-01-25 19:06:38', '286149.50', '', 'Paid', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 19:06:38', '2026-01-25 19:06:45', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, 'GI055', 10, 3, '2026-01-25 19:41:59', '271108.25', '', 'Paid', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 19:41:59', '2026-01-25 19:42:04', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 'GI056', 10, 3, '2026-01-25 20:03:36', '286149.50', '', 'Pending', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 20:03:36', '2026-01-25 20:03:36', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 'GI057', 10, 3, '2026-01-25 20:20:07', '286149.50', '', 'Paid', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 20:20:07', '2026-01-25 20:20:12', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(58, 'GI058', 10, 3, '2026-01-25 21:07:09', '271108.25', '', 'Paid', '', '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 21:07:09', '2026-01-25 21:07:13', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(59, 'GI059', 10, 3, '2026-01-25 21:08:38', '261807.88', '', 'Pending', NULL, '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', 'Note: This is a test only | Preferred Ocular Visit Date: January 30, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 21:08:38', '2026-01-25 21:08:38', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 'GI060', 10, 3, '2026-01-25 21:27:14', '211778.00', '', 'Paid', '', '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 21:27:14', '2026-01-25 21:27:19', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(61, 'GI061', 10, 3, '2026-01-25 21:35:56', '286149.50', '', 'Paid', '', '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 21:35:56', '2026-01-25 21:36:01', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(62, 'GI062', 10, 3, '2026-01-25 21:37:12', '271108.25', '', 'Paid', '', '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 21:37:12', '2026-01-25 21:37:17', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, 'GI063', 10, 3, '2026-01-25 21:42:24', '301907.38', '', 'Paid', '', '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 21:42:24', '2026-01-25 21:42:30', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, 'GI064', 10, 3, '2026-01-25 21:55:05', '286149.50', '', 'Paid', '', '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 21:55:05', '2026-01-25 21:55:12', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(65, 'GI065', 10, 3, '2026-01-25 21:56:00', '301907.38', '', 'Paid', '', '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 21:56:00', '2026-01-25 21:56:04', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(66, 'GI066', 10, 3, '2026-01-25 22:17:59', '211778.00', '', 'Paid', '', '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 22:17:59', '2026-01-25 22:18:04', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, 'GI067', 10, 3, '2026-01-25 22:19:17', '261807.88', '', 'Pending', NULL, '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', 'Preferred Ocular Visit Date: January 30, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-25 22:19:17', '2026-01-25 22:19:17', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(68, 'GI068', 10, 3, '2026-01-26 01:48:11', '252035.00', '', 'Pending', NULL, '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', 'Preferred Ocular Visit Date: January 30, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 01:48:11', '2026-01-26 01:48:11', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(69, 'GI069', 10, 3, '2026-01-26 02:02:19', '252035.00', '', 'Pending', NULL, '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', 'Preferred Ocular Visit Date: January 30, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 02:02:19', '2026-01-26 02:02:19', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(70, 'GI070', 10, 3, '2026-01-26 02:03:52', '252035.00', '', 'Pending', NULL, '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', 'Note: THis is a test note | Preferred Ocular Visit Date: January 30, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 02:03:52', '2026-01-26 02:03:52', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(71, 'GI071', 10, 3, '2026-01-26 02:14:58', '261807.88', '', 'Pending', NULL, '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', 'Preferred Ocular Visit Date: January 31, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 02:14:58', '2026-01-26 02:14:58', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(72, 'GI072', 10, 3, '2026-01-26 02:19:42', '252035.00', '', 'Pending', NULL, '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', 'Note: This is a test only. | Preferred Ocular Visit Date: February 10, 2026', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-02-10', NULL, NULL, NULL, NULL, '2026-01-26 02:19:42', '2026-01-26 02:19:42', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'This is a test only.', NULL, NULL, NULL),
(73, 'GI073', 10, 3, '2026-01-26 02:23:51', '261807.88', '', 'Pending', NULL, '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', '{\"contact_name\":null,\"contact_phone\":null,\"contact_email\":null,\"note\":\"\",\"preferred_ocular_date\":\"2026-01-31\"}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-01-31', NULL, NULL, NULL, NULL, '2026-01-26 02:23:51', '2026-01-26 02:23:51', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(74, 'GI074', 10, 3, '2026-01-26 02:29:32', '261807.88', '', 'Pending', NULL, '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', '{\"contact_name\":\"Jinwoo Sun\",\"contact_phone\":\"09111111111\",\"contact_email\":\"angelapauig05@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-01-30\"}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-01-30', NULL, NULL, NULL, NULL, '2026-01-26 02:29:32', '2026-01-26 02:29:32', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(75, 'GI075', 10, 3, '2026-01-26 02:32:43', '252035.00', 'Ready for Installation', 'Pending', NULL, '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', '{\"contact_name\":\"Jinwoo Sun\",\"contact_phone\":\"09111111111\",\"contact_email\":\"angelapauig05@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-01-30\"}', NULL, NULL, NULL, 2, '2026-01-26 04:09:07', NULL, NULL, NULL, NULL, 0, NULL, '2026-01-30', '2026-01-26', NULL, '2026-01-28', NULL, '2026-01-26 02:32:43', '2026-01-25 20:09:48', 'Site-Assessed', 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, '2026-01-26', 100, 'Completed', NULL, NULL, NULL, '', NULL, NULL, NULL),
(76, 'GI076', 10, 3, '2026-01-26 03:21:31', '261658.25', '', 'Paid', '', '331, Tessstt, Test, Malolos, Bulacan, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 03:21:31', '2026-01-26 03:21:36', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(77, 'GI077', 10, 3, '2026-01-26 03:31:44', '301907.38', '', 'Pending', '', '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 03:31:44', '2026-01-26 03:31:44', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(78, 'GI078', 10, 3, '2026-01-26 03:31:48', '301907.38', '', 'Pending', '', '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 03:31:48', '2026-01-26 03:31:48', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(79, 'GI079', 10, 3, '2026-01-26 03:43:59', '301907.38', '', 'Paid', '', '5111, Casa Valencia, San Benissa Garden Villas, Kaligayahan, Quezon City, Metro Manila, 1124, Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 03:43:59', '2026-01-26 03:44:06', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(80, 'GI080', 10, 3, '2026-01-26 03:55:46', '261658.25', '', 'Paid', '', '1111, Chestnut, Piña-Santol, Sampaloc, Manila, Metro Manila, 1111, Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 03:55:46', '2026-01-26 03:55:50', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(81, 'GI081', 10, 3, '2026-01-26 04:01:34', '301907.38', '', 'Paid', '', '5111, Casa Valencia, San Benissa Garden Villas, Kaligayahan, Quezon City, Metro Manila, 1124, Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 04:01:34', '2026-01-26 04:01:38', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(82, 'GI082', 10, 3, '2026-01-26 04:11:33', '211778.00', '', 'Paid', '', '5111, Casa Valencia, San Benissa Garden Villas, Kaligayahan, Quezon City, Metro Manila, 1124, Philippines', 'Note: HI. This is test.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 04:11:33', '2026-01-26 04:11:37', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'HI. This is test.', NULL, NULL, NULL),
(83, 'GI083', 10, 3, '2026-01-26 04:17:42', '271108.25', '', 'Paid', '', '1111, Chestnut, Piña-Santol, Sampaloc, Manila, Metro Manila, 1111, Philippines', 'Note: Hi again.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 04:17:42', '2026-01-26 04:17:47', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'Hi again.', NULL, NULL, NULL),
(84, 'GI084', 10, 3, '2026-01-26 04:26:52', '301907.38', '', 'Paid', '', '5111, Casa Valencia, San Benissa Garden Villas, Kaligayahan, Quezon City, Metro Manila, 1124, Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 04:26:52', '2026-01-26 04:26:56', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(85, 'GI085', 10, 3, '2026-01-26 05:10:22', '261658.25', '', 'Paid', '', '1111, Chestnut, Piña-Santol, Sampaloc, Manila, Metro Manila, 1111, Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 05:10:22', '2026-01-26 05:10:27', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(86, 'GI086', 10, 3, '2026-01-26 05:20:17', '261807.88', '', 'Pending', NULL, '5111, Casa Valencia, San Benissa Garden Villas, Quezon City, Metro Manila, Philippines, 1124', '{\"contact_name\":\"pop Nein\",\"contact_phone\":\"09664444444\",\"contact_email\":\"angelapauig05@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-01-31\"}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-01-31', NULL, NULL, NULL, NULL, '2026-01-26 05:20:17', '2026-01-26 05:20:17', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(87, 'GI087', 10, 3, '2026-01-26 05:30:11', '261807.88', '', 'Pending', NULL, '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', '{\"contact_name\":\"Jinwoo Sun\",\"contact_phone\":\"09111111111\",\"contact_email\":\"angelapauig05@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-01-30\"}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-01-30', NULL, NULL, NULL, NULL, '2026-01-26 05:30:11', '2026-01-26 05:30:11', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(88, 'GI088', 10, 3, '2026-01-26 05:30:27', '252035.00', '', 'Pending', NULL, '1111, Chestnut, Piña-Santol, Manila, Metro Manila, Philippines, 1111', '{\"contact_name\":\"Jinwoo Sun\",\"contact_phone\":\"09111111111\",\"contact_email\":\"angelapauig05@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-01-31\"}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-01-31', NULL, NULL, NULL, NULL, '2026-01-26 05:30:27', '2026-01-26 05:30:27', 'Site-Assessed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(89, 'GI089', 8, 3, '2026-01-26 08:54:15', '30410.00', '', 'Paid', '', '6, Sesame St., San Antonio Subd., Brgy. Nagkaisang Nayon, Quezon City, Metro Manila, 1125, Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 08:54:15', '2026-01-26 08:54:21', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, '', NULL, NULL, NULL),
(90, 'GI090', 8, 3, '2026-01-26 09:45:48', '63035.00', 'Ready for Installation', 'Paid', '', '6, Sesame St., San Antonio Subd., Brgy. Nagkaisang Nayon, Quezon City, Metro Manila, 1125, Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-02-01', NULL, '2026-01-26 09:45:48', '2026-01-26 11:16:14', 'Direct', 0, NULL, NULL, NULL, NULL, '2026-01-26', '2026-01-31', NULL, '2026-01-26', 100, 'Completed', NULL, NULL, NULL, '', NULL, NULL, NULL),
(91, 'GI091', 8, 3, '2026-01-26 09:54:30', '67760.00', 'Ready for Installation', 'Paid', '', '6, Sesame St., San Antonio Subd., Brgy. Nagkaisang Nayon, Quezon City, Metro Manila, 1125, Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-01-31', NULL, '2026-01-26 09:54:30', '2026-01-26 10:04:13', 'Direct', 0, NULL, NULL, NULL, NULL, '2026-01-26', '2026-01-31', NULL, '2026-01-26', 100, 'Completed', NULL, NULL, NULL, '', NULL, NULL, NULL);

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
(1, 1, 31, 104, 1, '16000.00', '2046720.00', '104in x 123in', NULL, 'Tempered: Bronze', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_3_1768761252_696d27a41e7d1.png', '2026-01-18 20:07:38'),
(2, 1, 31, 105, 1, '16000.00', '8285280.00', '421in x 123in', NULL, 'Tempered: Bronze', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_3_1768763458_696d30429f6fb.png', '2026-01-18 20:07:38'),
(3, 1, 31, 106, 1, '16000.00', '0.00', '123in x 123in', NULL, 'Clear', NULL, NULL, 'Gray', 'None', 'uploads/designs/design_3_1768765276_696d375ce887f.png', '2026-01-18 20:07:38'),
(4, 2, 33, 125, 1, '7500.00', '158868.00', '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768860362_696eaaca93f77.png', '2026-01-19 22:06:54'),
(5, 3, 33, 126, 1, '7500.00', '158960.00', '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768860449_696eab216f68b.png', '2026-01-19 23:14:30'),
(6, 4, 33, NULL, 1, '7500.00', '0.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-19 23:31:49'),
(7, 5, 33, NULL, 1, '7500.00', '0.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-20 00:14:50'),
(8, 6, 32, 137, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_3_1768870312_696ed1a8ee026.png', '2026-01-20 01:04:50'),
(9, 7, 31, 140, 0, '0.00', '0.00', 'in x in', NULL, 'Reflective: Light Bronze', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_3_1768871623_696ed6c744214.png', '2026-01-20 01:31:31'),
(10, 8, 32, 142, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_3_1768873018_696edc3ab60ed.png', '2026-01-20 01:37:10'),
(11, 9, 33, 143, 1, '7500.00', '159186.00', '45in x 45in', 'Round', NULL, NULL, NULL, 'Dark/Black', 'None', 'uploads/designs/design_3_1768876637_696eea5d0437f.png', '2026-01-20 02:40:14'),
(12, 10, 33, 144, 1, '7500.00', '153980.00', '45in x 45in', 'Circle', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_3_1768878390_696ef13665901.png', '2026-01-20 03:06:43'),
(13, 11, 33, 145, 1, '7500.00', '159108.00', '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768878434_696ef162386d7.png', '2026-01-20 03:07:25'),
(14, 12, 33, 146, 1, '7500.00', '159096.00', '45in x 45in', 'Round', NULL, NULL, NULL, 'Gold frame', 'None', 'uploads/designs/design_3_1768878831_696ef2efb90de.png', '2026-01-20 03:14:14'),
(15, 13, 33, 150, 1, '7500.00', '159088.00', '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768879267_696ef4a355892.png', '2026-01-20 03:21:16'),
(16, 14, 33, 150, 1, '7500.00', '159088.00', '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768879267_696ef4a355892.png', '2026-01-20 03:23:56'),
(17, 15, 33, 150, 1, '7500.00', '159088.00', '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_3_1768879267_696ef4a355892.png', '2026-01-20 03:24:57'),
(18, 16, 32, 153, 0, '0.00', '0.00', 'in x in', NULL, 'Dark Gray', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1768983450_69708b9a304e5.png', '2026-01-21 08:18:34'),
(19, 17, 33, 154, 1, '7500.00', '159088.00', '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_7_1768984141_69708e4da758e.png', '2026-01-21 08:56:54'),
(20, 17, 33, 155, 1, '7500.00', '159088.00', '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_7_1768984142_69708e4e82ef9.png', '2026-01-21 08:56:54'),
(21, 18, 33, 154, 1, '7500.00', '159088.00', '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_7_1768984141_69708e4da758e.png', '2026-01-21 08:57:26'),
(22, 18, 33, 155, 1, '7500.00', '159088.00', '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_7_1768984142_69708e4e82ef9.png', '2026-01-21 08:57:26'),
(23, 19, 33, 154, 1, '7500.00', '159088.00', '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_7_1768984141_69708e4da758e.png', '2026-01-21 08:58:39'),
(24, 19, 33, 155, 1, '7500.00', '159088.00', '45in x 45in', 'Round', NULL, NULL, 'Beveled', 'Gold frame', 'None', 'uploads/designs/design_7_1768984142_69708e4e82ef9.png', '2026-01-21 08:58:39'),
(25, 20, 33, 157, 1, '7500.00', '158568.00', '45in x 45in', 'Square', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_7_1769002465_6970d5e15f6ec.png', '2026-01-21 13:36:17'),
(26, 21, 33, 158, 1, '7500.00', '158960.00', '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_7_1769002799_6970d72fd08ef.png', '2026-01-21 13:40:16'),
(27, 22, 33, 158, 1, '7500.00', '158960.00', '45in x 45in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_7_1769002799_6970d72fd08ef.png', '2026-01-21 13:41:25'),
(28, 23, 33, 158, 0, '0.00', '0.00', 'in x in', 'Round', NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_7_1769002799_6970d72fd08ef.png', '2026-01-21 13:42:55'),
(29, 24, 32, 159, 0, '0.00', '0.00', 'in x in', NULL, 'Ultra Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769003618_6970da62304d6.png', '2026-01-21 13:53:48'),
(30, 25, 42, 163, 0, '0.00', '0.00', 'in x in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769127876_6972bfc4d6614.png', '2026-01-23 00:24:45'),
(31, 26, 32, 165, 0, '0.00', '0.00', 'in x in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769131743_6972cedfc95a4.png', '2026-01-23 01:29:59'),
(32, 27, 40, 166, 1, '17211.00', '271073.25', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769137522_6972e57250bd8.png', '2026-01-23 03:05:29'),
(33, 28, 32, 169, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769148634_697310da72d33.png', '2026-01-23 06:10:48'),
(34, 29, 43, 170, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769148661_697310f5783f1.png', '2026-01-23 06:11:11'),
(35, 30, 32, 174, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769163109_697349651ee38.png', '2026-01-23 11:53:52'),
(36, 31, 39, 176, 1, '15105.50', '237911.63', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769169259_6973616b99c20.png', '2026-01-23 11:54:45'),
(37, 32, 32, 177, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769169415_69736207efd18.png', '2026-01-23 12:10:14'),
(38, 33, 32, 178, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769171341_6973698d18b2d.png', '2026-01-23 12:29:11'),
(39, 34, 43, 183, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769173534_6973721e6f43c.png', '2026-01-23 13:05:52'),
(40, 35, 32, 184, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769173747_697372f37b40c.png', '2026-01-23 13:09:20'),
(41, 36, 44, 185, 1, '16611.00', '261623.25', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769173877_697373757818a.png', '2026-01-23 13:11:59'),
(42, 37, 32, 186, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769174129_697374719c782.png', '2026-01-23 13:15:45'),
(43, 38, 43, 187, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_7_1769174332_6973753cc4507.png', '2026-01-23 13:19:12'),
(44, 39, 33, 188, 1, '7500.00', '201199.00', '50in x 50in', 'Square', NULL, NULL, NULL, 'Black frame', 'None', 'uploads/designs/design_8_1769229363_69744c335c78b.png', '2026-01-24 04:37:32'),
(45, 40, 33, 188, 0, '0.00', '0.00', 'in x in', 'Square', NULL, NULL, NULL, 'Black frame', 'None', 'uploads/designs/design_8_1769229363_69744c335c78b.png', '2026-01-24 04:38:08'),
(46, 41, 33, 189, 0, '0.00', '0.00', 'in x in', 'Square', NULL, NULL, NULL, 'Black frame', 'None', 'uploads/designs/design_8_1769230144_69744f40e2331.png', '2026-01-24 04:51:19'),
(47, 42, 32, 190, 0, '0.00', '0.00', 'in x in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769231062_697452d6b3066.png', '2026-01-24 05:04:43'),
(48, 43, 32, 191, 1, '16620.50', '261772.88', '45in x 35in', NULL, NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_8_1769247005_6974911d516d8.png', '2026-01-24 09:30:12'),
(49, 44, 32, 192, 1, '16620.50', '261772.88', '45in x 35in', NULL, NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_8_1769247226_697491facbc1c.png', '2026-01-24 09:33:55'),
(50, 45, 31, 193, 1, '16000.00', '252000.00', '45in x 35in', NULL, 'Reflective: Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_8_1769247549_6974933d4719d.png', '2026-01-24 09:39:17'),
(51, 46, 32, 194, 1, '16620.50', '261772.88', '45in x 35in', NULL, NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_8_1769247829_6974945508230.png', '2026-01-24 09:44:01'),
(52, 47, 32, 195, 1, '16620.50', '261772.88', '45in x 35in', NULL, NULL, NULL, NULL, NULL, 'None', 'uploads/designs/design_8_1769249352_69749a481a8f1.png', '2026-01-24 10:09:20'),
(53, 48, 40, 197, 1, '17211.00', '271073.25', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769366286_6976630e152ae.png', '2026-01-25 18:38:31'),
(54, 49, 43, 198, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769366447_697663afd43ae.png', '2026-01-25 18:42:39'),
(55, 50, 43, 199, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769367570_697668128cbd4.png', '2026-01-25 18:59:39'),
(56, 51, 43, 199, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769367570_697668128cbd4.png', '2026-01-25 18:59:49'),
(57, 52, 43, 199, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769367570_697668128cbd4.png', '2026-01-25 19:02:46'),
(58, 53, 43, 199, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769367570_697668128cbd4.png', '2026-01-25 19:05:14'),
(59, 54, 43, 199, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769367570_697668128cbd4.png', '2026-01-25 19:06:38'),
(60, 55, 40, 200, 1, '17211.00', '271073.25', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769369458_69766f7235fc6.png', '2026-01-25 19:41:59'),
(61, 56, 43, 201, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769371406_6976770ede3ba.png', '2026-01-25 20:03:36'),
(62, 57, 43, 201, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769371406_6976770ede3ba.png', '2026-01-25 20:20:07'),
(63, 58, 40, 202, 1, '17211.00', '271073.25', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769373274_69767e5a0ae12.png', '2026-01-25 21:07:09'),
(64, 59, 32, 203, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769375292_6976863c4d3a9.png', '2026-01-25 21:08:38'),
(65, 60, 42, 208, 1, '13444.00', '211743.00', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769376402_69768a9226b09.png', '2026-01-25 21:27:14'),
(66, 61, 43, 209, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769376549_69768b259f56e.png', '2026-01-25 21:35:56'),
(67, 62, 40, 211, 1, '17211.00', '271073.25', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769377026_69768d02abec4.png', '2026-01-25 21:37:12'),
(68, 63, 41, 212, 1, '19166.50', '301872.38', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769377334_69768e3611c41.png', '2026-01-25 21:42:24'),
(69, 64, 43, 213, 1, '18166.00', '286114.50', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769378097_6976913140fb6.png', '2026-01-25 21:55:05'),
(70, 65, 41, 214, 1, '19166.50', '301872.38', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769378154_6976916a7eb7c.png', '2026-01-25 21:56:00'),
(71, 66, 42, 215, 1, '13444.00', '211743.00', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769379451_6976967b86922.png', '2026-01-25 22:17:59'),
(72, 67, 32, 216, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769379524_697696c4c2844.png', '2026-01-25 22:19:17'),
(73, 68, 31, 217, 1, '16000.00', '252000.00', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769379583_697696ff949e7.png', '2026-01-26 01:48:11'),
(74, 69, 31, 222, 1, '16000.00', '252000.00', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769392928_6976cb204912d.png', '2026-01-26 02:02:19'),
(75, 70, 31, 223, 1, '16000.00', '252000.00', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769393014_6976cb7648008.png', '2026-01-26 02:03:52'),
(76, 71, 32, 224, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769393680_6976ce1088fa0.png', '2026-01-26 02:14:58'),
(77, 72, 31, 225, 1, '16000.00', '252000.00', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769393878_6976ced623140.png', '2026-01-26 02:19:42'),
(78, 73, 32, 226, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769394223_6976d02f5431e.png', '2026-01-26 02:23:51'),
(79, 74, 32, 227, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769394563_6976d1837c899.png', '2026-01-26 02:29:32'),
(80, 75, 31, 228, 1, '16000.00', '16000.00', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769394756_6976d244a9c11.png', '2026-01-26 02:32:43'),
(81, 76, 44, 229, 1, '16611.00', '261623.25', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769396167_6976d7c7401f4.png', '2026-01-26 03:21:31'),
(82, 77, 41, 232, 1, '19166.50', '301872.38', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769398288_6976e010c50e4.png', '2026-01-26 03:31:44'),
(83, 78, 41, 232, 1, '19166.50', '301872.38', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769398288_6976e010c50e4.png', '2026-01-26 03:31:48'),
(84, 79, 41, 233, 1, '19166.50', '301872.38', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769398798_6976e20e8ec4e.png', '2026-01-26 03:43:59'),
(85, 80, 44, 234, 1, '16611.00', '261623.25', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769399099_6976e33b14492.png', '2026-01-26 03:55:46'),
(86, 81, 41, 236, 1, '19166.50', '301872.38', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769399886_6976e64ed39b3.png', '2026-01-26 04:01:34'),
(87, 82, 42, 237, 1, '13444.00', '211743.00', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769400474_6976e89ac39ef.png', '2026-01-26 04:11:33'),
(88, 83, 40, 238, 1, '17211.00', '271073.25', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769400983_6976ea97e0cb8.png', '2026-01-26 04:17:42'),
(89, 84, 41, 239, 1, '19166.50', '301872.38', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769401593_6976ecf9be8d3.png', '2026-01-26 04:26:52'),
(90, 85, 44, 241, 1, '16611.00', '261623.25', '45in x 35in', NULL, 'Ordinary', '6mm', NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769404214_6976f7365d190.png', '2026-01-26 05:10:22'),
(91, 86, 32, 242, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769404786_6976f9722caf2.png', '2026-01-26 05:20:17'),
(92, 87, 32, 243, 1, '16620.50', '261772.88', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769405405_6976fbddf2238.png', '2026-01-26 05:30:11'),
(93, 88, 31, 244, 1, '16000.00', '252000.00', '45in x 35in', NULL, 'Clear', NULL, NULL, 'Hanalok', 'None', 'uploads/designs/design_10_1769405422_6976fbeeed36d.png', '2026-01-26 05:30:27'),
(94, 89, 7, 246, 1, '1500.00', '30375.00', '45in x 45in', 'Custom shapes', NULL, NULL, 'Raw', NULL, 'None', 'uploads/designs/design_8_1769417648_69772bb0a833c.png', '2026-01-26 08:54:15'),
(95, 90, 2, 247, 1, '4000.00', '63000.00', '45in x 35in', NULL, 'Tempered', '6mm', NULL, 'Analok', 'None', 'uploads/designs/design_8_1769420742_697737c645763.png', '2026-01-26 09:45:48'),
(96, 91, 1, 248, 1, '4300.00', '67725.00', '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_8_1769421265_697739d15bbc8.png', '2026-01-26 09:54:30');

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
  `PaymentIntentID` varchar(128) DEFAULT NULL,
  `ReceiptPath` varchar(255) DEFAULT NULL,
  `Status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  `billing_name` varchar(255) DEFAULT '',
  `billing_email` varchar(255) DEFAULT '',
  `billing_phone` varchar(50) DEFAULT '',
  `billing_unit` varchar(255) DEFAULT '',
  `billing_street` varchar(255) DEFAULT '',
  `billing_subdivision` varchar(255) DEFAULT '',
  `billing_barangay` varchar(255) DEFAULT '',
  `billing_city` varchar(255) DEFAULT '',
  `billing_province` varchar(255) DEFAULT '',
  `billing_region` varchar(255) DEFAULT '',
  `billing_postal_code` varchar(50) DEFAULT '',
  `billing_country` varchar(255) DEFAULT '',
  `shipping_name` varchar(255) DEFAULT NULL,
  `shipping_email` varchar(255) DEFAULT NULL,
  `shipping_phone` varchar(20) DEFAULT NULL,
  `shipping_unit` varchar(100) DEFAULT NULL,
  `shipping_street` varchar(255) DEFAULT NULL,
  `shipping_subdivision` varchar(255) DEFAULT NULL,
  `shipping_barangay` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_province` varchar(100) DEFAULT NULL,
  `shipping_region` varchar(100) DEFAULT NULL,
  `shipping_postal_code` varchar(20) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT NULL,
  `billing_country_iso` varchar(8) DEFAULT '',
  `billing_payload_json` text DEFAULT '',
  `billing_firstname` varchar(120) DEFAULT NULL,
  `billing_lastname` varchar(120) DEFAULT NULL,
  `billing_unit_house_number` varchar(255) DEFAULT NULL,
  `billing_zipcode` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Payment_ID`, `OrderID`, `CustomerName`, `ProductName`, `PaymentMethod`, `Amount`, `Payment_Date`, `Transaction_ID`, `PaymentIntentID`, `ReceiptPath`, `Status`, `billing_name`, `billing_email`, `billing_phone`, `billing_unit`, `billing_street`, `billing_subdivision`, `billing_barangay`, `billing_city`, `billing_province`, `billing_region`, `billing_postal_code`, `billing_country`, `shipping_name`, `shipping_email`, `shipping_phone`, `shipping_unit`, `shipping_street`, `shipping_subdivision`, `shipping_barangay`, `shipping_city`, `shipping_province`, `shipping_region`, `shipping_postal_code`, `shipping_country`, `billing_country_iso`, `billing_payload_json`, `billing_firstname`, `billing_lastname`, `billing_unit_house_number`, `billing_zipcode`) VALUES
(1, 9, NULL, NULL, '', '159221.00', '2026-01-19 19:43:55', 'pi_BT6ZhbGnN8owC6o5RVcFC6nN', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(2, 10, NULL, NULL, '', '154015.00', '2026-01-19 20:06:51', 'pi_PQSh9mNVRBqsMBdsF79NM8oS', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(3, 11, NULL, NULL, '', '159143.00', '2026-01-19 20:07:25', 'pi_xsnE8s3K4oT1nyYaRhviskMB', NULL, NULL, 'Pending', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(4, 12, NULL, NULL, '', '159131.00', '2026-01-19 20:14:15', 'pi_YVkRMSUtcxfbutugEvPpvSfv', NULL, NULL, 'Pending', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(5, 13, NULL, NULL, '', '159123.00', '2026-01-19 20:21:16', 'pi_mqyfZRsWrEZkaPoRUkCd3aP6', NULL, NULL, 'Pending', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(6, 14, NULL, NULL, '', '159123.00', '2026-01-19 20:23:56', 'pi_eHfDVnqqtAYAPAZ2o8rFR2jq', NULL, NULL, 'Pending', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(7, 15, NULL, NULL, '', '159123.00', '2026-01-19 20:25:26', 'pi_jXWLZTjS8ktYDUV36Tf4N4SH', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(8, 19, NULL, NULL, '', '318246.00', '2026-01-21 01:58:55', 'pi_aAqXQ88cD5o1SbY2LrQSC9Zn', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(9, 23, NULL, NULL, '', '158995.00', '2026-01-21 06:46:00', 'pi_DxV2c21uHRk36ZEWDMrdx4c6', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(10, 25, NULL, NULL, '', '211778.00', '2026-01-22 17:24:52', 'pi_GgkWGhCVSgPFNzUdvq8PY8ou', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(11, 26, NULL, NULL, '', '0.00', '2026-01-22 18:53:12', NULL, NULL, 'uploads/payments/ocular/custom-design-1768914404853.png', 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(12, 27, NULL, NULL, '', '271108.25', '2026-01-22 20:05:34', 'pi_62MW8JeY4UiVVErhA5idWLAL', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(13, 29, NULL, NULL, '', '286149.50', '2026-01-22 23:11:15', 'pi_1LTi8YJsNqqGi5MmWsoBeedP', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(14, 31, NULL, NULL, '', '237946.63', '2026-01-23 06:03:54', 'pi_jL9yq7SDbhjmZaFCdo2aPy4B', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(15, 34, NULL, NULL, '', '286149.50', '2026-01-23 06:05:56', 'pi_UqcC48CLmuivpksxW6g9SPKm', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(16, 36, NULL, NULL, '', '261658.25', '2026-01-23 06:12:03', 'pi_71BpPXBoSw5xB9YYWnHCBdVa', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(17, 38, NULL, NULL, '', '286149.50', '2026-01-23 06:19:16', 'pi_NHdenT7n5QxGek8W852S3ogJ', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(18, 39, NULL, NULL, '', '201234.00', '2026-01-23 21:37:33', 'pi_mqUKSL58zEDqgZEmXkci87AA', NULL, NULL, 'Pending', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(19, 40, NULL, NULL, '', '201234.00', '2026-01-23 21:38:39', 'pi_fmVb2dnQS7VLxzcmbJ21oug3', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(20, 41, NULL, NULL, '', '165567.00', '2026-01-23 21:59:26', 'pi_hQ6tkfuFxnXAapQ8VCYeZLPG', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(21, 48, NULL, NULL, '', '271108.25', '2026-01-25 11:38:40', 'pi_nbH5nQaaS5FCXKT8UHZDRLaJ', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(22, 49, NULL, NULL, '', '286149.50', '2026-01-25 11:42:45', 'pi_e8DvTrbdLrAg7twkXs3FaGce', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(23, 52, NULL, NULL, '', '286149.50', '2026-01-25 12:02:46', 'pi_qMUh8LHhTibZk6cyP4gvUDzV', NULL, NULL, 'Pending', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(24, 53, NULL, NULL, '', '286149.50', '2026-01-25 12:05:14', 'pi_jLh5PpsdWvKrNLVEYQC1TzvY', NULL, NULL, 'Pending', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(25, 54, NULL, NULL, '', '286149.50', '2026-01-25 12:06:45', 'pi_yRkm6GTuwvBHEtdUpM12jrV2', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(26, 55, NULL, NULL, '', '271108.25', '2026-01-25 12:42:04', 'pi_RFoAiWogpXdoQTrRPo6ySafe', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(27, 56, NULL, NULL, '', '286149.50', '2026-01-25 13:03:37', 'pi_7XxVTfJS8cba7wiefDLXXyC3', NULL, NULL, 'Pending', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(28, 57, NULL, NULL, '', '286149.50', '2026-01-25 15:17:19', 'pi_7mzzVscQ1mN5DiMUap8MUuEo', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"\",\"email\":\"\",\"phone\":\"\",\"address\":{\"line1\":\"\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(29, 58, NULL, NULL, '', '271108.25', '2026-01-25 15:17:08', 'pi_ngqG9ryw33XK87GruNoz19Hs', NULL, NULL, 'Paid', '', '', '', '5111', 'Casa Valencia', 'San Benissa Garden Villas', 'Kaligayahan', 'Quezon City', 'Metro Manila', 'NCR', '1124', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"\",\"email\":\"\",\"phone\":\"\",\"address\":{\"line1\":\"5111, Casa Valencia, San Benissa Garden Villas, Kaligayahan\",\"city\":\"Quezon City\",\"state\":\"Metro Manila\",\"postal_code\":\"1124\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(30, 60, NULL, NULL, '', '211778.00', '2026-01-25 14:27:19', 'pi_Q5BMwYGFs6NFyxVZnuMe1n9K', NULL, NULL, 'Paid', '', '', '', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"\",\"email\":\"\",\"phone\":\"\",\"address\":{\"line1\":\"1111, Chestnut, Pi\\u00f1a-Santol, Sampaloc\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"1111\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(31, 61, NULL, NULL, '', '286149.50', '2026-01-25 14:36:57', NULL, NULL, NULL, 'Paid', '', '', '', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"\",\"email\":\"\",\"phone\":\"\",\"address\":{\"line1\":\"1111, Chestnut, Pi\\u00f1a-Santol, Sampaloc\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"1111\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(32, 62, NULL, NULL, '', '271108.25', '2026-01-25 14:37:17', NULL, NULL, NULL, 'Paid', '', '', '', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"\",\"email\":\"\",\"phone\":\"\",\"address\":{\"line1\":\"1111, Chestnut, Pi\\u00f1a-Santol, Sampaloc\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"1111\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(33, 63, NULL, NULL, '', '301907.38', '2026-01-25 15:02:44', NULL, 'pi_Zik3q14ZMNyMEDaQF8bCu4UG', NULL, 'Paid', '', '', '', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"\",\"email\":\"\",\"phone\":\"\",\"address\":{\"line1\":\"1111, Chestnut, Pi\\u00f1a-Santol, Sampaloc\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"1111\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(34, 64, NULL, NULL, '', '286149.50', '2026-01-25 14:55:48', NULL, 'pi_8rtkk5hRgT3ZqHZyyDt32hcH', NULL, 'Paid', '', '', '', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"\",\"email\":\"\",\"phone\":\"\",\"address\":{\"line1\":\"1111, Chestnut, Pi\\u00f1a-Santol, Sampaloc\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"1111\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(35, 65, NULL, NULL, '', '301907.38', '2026-01-25 21:26:22', 'pi_vGsVBvJdNhbznWjCXE2ALZfe', NULL, NULL, 'Paid', '', '', '', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"\",\"email\":\"\",\"phone\":\"\",\"address\":{\"line1\":\"1111, Chestnut, Pi\\u00f1a-Santol, Sampaloc\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"1111\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(36, 66, NULL, NULL, '', '211778.00', '2026-01-25 20:30:38', 'pi_g1Bqehx4gTrQQioki5T4CX9u', NULL, NULL, 'Paid', 'Baus  Rufo', 'angelapauig05@gmail.com', '09111111111', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"Baus  Rufo\",\"email\":\"angelapauig05@gmail.com\",\"phone\":\"09111111111\",\"address\":{\"line1\":\"1111, Chestnut, Pi\\u00f1a-Santol\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"1111\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(37, 76, NULL, NULL, '', '261658.25', '2026-01-25 20:36:17', 'pi_TrkRNsTo42kYQGkHeoN4DnJj', NULL, NULL, 'Paid', 'Hani  Kim', 'angelapauig05@gmail.com', '09239222222', '422', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '0000', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"Hani  Kim\",\"email\":\"angelapauig05@gmail.com\",\"phone\":\"09239222222\",\"address\":{\"line1\":\"422, Chestnut, Pi\\u00f1a-Santol\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"0000\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(38, 79, NULL, NULL, '', '301907.38', '2026-01-25 20:44:15', 'pi_m7DxrwGiUSCan7dbSayEjnMP', NULL, NULL, 'Paid', 'Jennie  Kim', 'angelapauig05@gmail.com', '09112233331', '451', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '0000', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"Jennie  Kim\",\"email\":\"angelapauig05@gmail.com\",\"phone\":\"09112233331\",\"address\":{\"line1\":\"451, Chestnut, Pi\\u00f1a-Santol\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"0000\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(39, 80, NULL, NULL, '', '261658.25', '2026-01-25 21:15:30', 'pi_xsKWzS4oqyuxb8oQQdoin8iH', NULL, NULL, 'Paid', 'Jenny  Kim', 'angelapauig05@gmail.com', '09121222333', '321', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '0000', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"Jenny  Kim\",\"email\":\"angelapauig05@gmail.com\",\"phone\":\"09121222333\",\"address\":{\"line1\":\"321, Chestnut, Pi\\u00f1a-Santol\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"0000\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(40, 81, NULL, NULL, '', '301907.38', '2026-01-25 21:07:47', 'pi_zvUqMm3RGgwABp5KCLzHsGFc', NULL, NULL, 'Paid', 'Yeri  Kim', 'angelapauig05@gmail.com', '09144444444', '93', '', '', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '0111', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"Yeri  Kim\",\"email\":\"angelapauig05@gmail.com\",\"phone\":\"09144444444\",\"address\":{\"line1\":\"93\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"0111\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(41, 82, NULL, NULL, '', '211778.00', '2026-01-25 21:23:35', 'pi_LAKGQykmZB4C5dNHs8B415rD', NULL, NULL, 'Paid', 'Jennie  Kim', 'angelapauig05@gmail.com', '09000001111', '322', '', '', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '0001', 'Philippines', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '{\"name\":\"Jennie  Kim\",\"email\":\"angelapauig05@gmail.com\",\"phone\":\"09000001111\",\"address\":{\"line1\":\"322\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"0001\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(42, 83, NULL, NULL, '', '271108.25', '2026-01-25 21:23:39', 'pi_T7YUMUJBMDibVGVgZ3MkDrNd', NULL, NULL, 'Paid', 'Jenny  Kim', 'angelapauig05@gmail.com', '09121222333', '777', '', '', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '0001', 'Philippines', 'Liz  Kriz', 'angelapauig05@gmail.com', '09999922222', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', 'PH', '{\"name\":\"Jenny  Kim\",\"email\":\"angelapauig05@gmail.com\",\"phone\":\"09121222333\",\"address\":{\"line1\":\"777\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"0001\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(43, 84, NULL, NULL, '', '301907.38', '2026-01-25 22:24:52', 'pi_qMyhrBVLXL3EepQUwgbsioGn', NULL, NULL, 'Paid', 'Jinwoo  Sun', 'angelapauig05@gmail.com', '09111111111', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', 'Jinwoo  Sun', 'angelapauig05@gmail.com', '0911424242', '5111', 'Casa Valencia', 'San Benissa Garden Villas', 'Kaligayahan', 'Quezon City', 'Metro Manila', 'NCR', '1124', 'Philippines', 'PH', '{\"name\":\"Jinwoo  Sun\",\"email\":\"angelapauig05@gmail.com\",\"phone\":\"09111111111\",\"address\":{\"line1\":\"1111, Chestnut, Pi\\u00f1a-Santol\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"1111\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(44, 85, NULL, NULL, '', '261658.25', '2026-01-25 22:10:27', 'pi_u8JKmkgjoFT2Hr1pPUx2oTB7', NULL, NULL, 'Paid', 'Jinwoo  Sun', 'angelapauig05@gmail.com', '09111111111', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', 'Jinwoo  Sun', 'angelapauig05@gmail.com', '09111111111', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'Manila', 'Metro Manila', 'NCR', '1111', 'Philippines', 'PH', '{\"name\":\"Jinwoo  Sun\",\"email\":\"angelapauig05@gmail.com\",\"phone\":\"09111111111\",\"address\":{\"line1\":\"1111, Chestnut, Pi\\u00f1a-Santol\",\"city\":\"Manila\",\"state\":\"Metro Manila\",\"postal_code\":\"1111\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(45, 89, NULL, NULL, '', '30410.00', '2026-01-26 01:54:21', 'pi_Fy8nPNo6c5Pf1P2tzgWFq65A', NULL, NULL, 'Paid', 'Rommel John Jeric R. Lerum', 'lerumgops@gmail.com', '09120844695', '6', 'Sesame St.', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'Quezon City', 'Metro Manila', 'NCR', '1125', 'Philippines', 'Rommel John Jeric R. Lerum', 'lerumgops@gmail.com', '09120844695', '6', 'Sesame St.', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'Quezon City', 'Metro Manila', 'NCR', '1125', 'Philippines', 'PH', '{\"name\":\"Rommel John Jeric R. Lerum\",\"email\":\"lerumgops@gmail.com\",\"phone\":\"09120844695\",\"address\":{\"line1\":\"6, Sesame St., San Antonio Subd.\",\"city\":\"Quezon City\",\"state\":\"Metro Manila\",\"postal_code\":\"1125\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(46, 90, NULL, NULL, '', '63035.00', '2026-01-26 02:45:54', 'pi_gtcMNkdExS1zecmockQ3ycNX', NULL, NULL, 'Paid', 'Rommel John Jeric R. Lerum', 'lerumgops@gmail.com', '09120844695', '6', 'Sesame St.', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'Quezon City', 'Metro Manila', 'NCR', '1125', 'Philippines', 'Rommel John Jeric R. Lerum', 'lerumgops@gmail.com', '09120844695', '6', 'Sesame St.', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'Quezon City', 'Metro Manila', 'NCR', '1125', 'Philippines', 'PH', '{\"name\":\"Rommel John Jeric R. Lerum\",\"email\":\"lerumgops@gmail.com\",\"phone\":\"09120844695\",\"address\":{\"line1\":\"6, Sesame St., San Antonio Subd.\",\"city\":\"Quezon City\",\"state\":\"Metro Manila\",\"postal_code\":\"1125\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL),
(47, 91, NULL, NULL, '', '67760.00', '2026-01-26 02:54:36', 'pi_WS5RZEr1px8EQkquJYRcXVSd', NULL, NULL, 'Paid', 'Rommel John Jeric R. Lerum', 'lerumgops@gmail.com', '09120844695', '6', 'Sesame St.', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'Quezon City', 'Metro Manila', 'NCR', '1125', 'Philippines', 'Rommel John Jeric R. Lerum', 'lerumgops@gmail.com', '09120844695', '6', 'Sesame St.', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'Quezon City', 'Metro Manila', 'NCR', '1125', 'Philippines', 'PH', '{\"name\":\"Rommel John Jeric R. Lerum\",\"email\":\"lerumgops@gmail.com\",\"phone\":\"09120844695\",\"address\":{\"line1\":\"6, Sesame St., San Antonio Subd.\",\"city\":\"Quezon City\",\"state\":\"Metro Manila\",\"postal_code\":\"1125\",\"country\":\"PH\"}}', NULL, NULL, NULL, NULL);

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
  `Status` enum('Unavailable','Available') DEFAULT 'Unavailable',
  `Subcategory` varchar(100) DEFAULT NULL,
  `OrderType` enum('direct','site-assessment') DEFAULT 'direct',
  `PriceMin` decimal(10,2) DEFAULT NULL,
  `PriceMax` decimal(10,2) DEFAULT NULL,
  `Customization` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: Customer customization selections',
  `SelectedCustomizationSeries` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`Product_ID`, `ProductName`, `Category`, `Material`, `Price`, `ImageUrl`, `Description`, `DateAdded`, `Status`, `Subcategory`, `OrderType`, `PriceMin`, `PriceMax`, `Customization`, `SelectedCustomizationSeries`) VALUES
(1, 'Sliding Window', 'Windows', 'Glass', '4300.00', '[\"08973984eef0bdc173063b3debb2df7f.jpg\",\"a9719fddcbc0baecacb9d6c4ec61ae3e.jpg\",\"b0911580251a5a6d8d20741598a5179a.jpg\"]', NULL, '2026-01-20 18:55:09', 'Available', 'Sliding', 'direct', '1600.00', '7000.00', '{\"numberOfPanels\":[\"2 Panels\",\"4 Panels\"],\"transomType\":[\"Fixed Transom Sill (Fixed glass at bottom)\",\"Fixed Transom Head (Fixed glass at top)\",\"None\"],\"trackSystem\":[\"2 Tracks\",\"3 Tracks\"],\"panelConfiguration\":[\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\",\"S | S | S | S (All Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S (Sliding | Sliding)\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Black\",\"Matte Gray\",\"Wood Finish\"],\"glassType\":[\"Ordinary\",\"Reflective\",\"Tempered\"],\"glassColor\":[\"Bronze\",\"Clear\",\"Smoked\",\"Frosted\"],\"glassThickness\":[\"6mm\",\"10mm\",\"8mm\",\"12mm\"],\"lockType\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"New Auto Flushlock\",\"Durable Flushlok\"],\"rollerType\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', NULL),
(2, 'Awning Window', 'Windows', 'Glass', '4000.00', '[\"8891bbe9173e296dabda55ccd6e132e1.jpg\"]', NULL, '2026-01-20 19:19:09', 'Available', 'Awning', 'direct', '2000.00', '6000.00', '{\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"glassColor\":[\"Clear\",\"Frosted\",\"Bronze\",\"Smoked\"],\"frameColor\":[\"Powder Coated White\",\"Matte Gray\",\"Wood Finish\",\"Analok\",\"Matte Black\"],\"operation\":[\"Awning (crank-out)\",\"Awning (push-out)\"],\"sizeConfiguration\":[\"Single panel\",\"Multiple panels\"],\"openingDirection\":[\"Top-hinged\"],\"thickness\":[\"6mm\",\"10mm\",\"8mm\",\"12mm\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', NULL),
(3, 'Casement Window', 'Windows', 'Glass', '1500.00', '[\"4a04a1a313222ae5f585308c10aa4908.jpg\"]', NULL, '2026-01-20 20:03:04', 'Available', 'Casement', 'direct', '1000.00', '2000.00', '{\"transomType\":[\"None\",\"Casement w/FTS\",\"Casement w/FTH\"],\"panelConfiguration\":[\"1\",\"4\",\"2\",\"5\",\"3\",\"6\"],\"width\":\"\",\"height\":\"\",\"h1\":\"\",\"frameColor\":[\"Wood Finish\",\"Matte Gray\",\"Powder Coated White\",\"Analok\",\"Matte Black\"],\"glassColor\":[\"Bronze\",\"Smoked\",\"Clear\",\"Frosted\"],\"glassType\":[\"Reflective\",\"Ordinary\",\"Tempered\"],\"thickness\":[\"10mm\",\"6mm\",\"8mm\",\"12mm\"]}', NULL),
(4, 'Sliding Door', 'Doors', 'Glass', '2000.00', '[\"6946d23f9ab77a37b945e908744736cf.jpg\"]', NULL, '2026-01-20 20:04:18', 'Available', 'Sliding', 'site-assessment', '1000.00', '3000.00', '{\"glassType\":[\"Clear\",\"Tinted\",\"Low-E\",\"Frosted\",\"Tempered\",\"Laminated\",\"Laminated safety glass\"],\"frameColor\":[\"Aluminum\",\"White\",\"Black\",\"Bronze\",\"Brown (wood-look)\",\"Silver\",\"Custom colors\"],\"panelCount\":[\"2-panel\",\"4-panel\",\"3-panel\"],\"operation\":[\"Sliding (single)\",\"Sliding (multi-track)\",\"Sliding (double)\"],\"panelConfiguration\":[\"Central sliding panels with fixed outer panels\",\"All sliding\",\"2 sliding + 2 fixed\",\"3 sliding\",\"2 sliding only\"],\"handleType\":[\"Various pull handles\",\"Knob handles\",\"Bar-style\",\"Square handles\",\"Round\",\"Square matte black\"],\"hardwareFinish\":[\"Chrome/Stainless Steel\",\"Polished Chrome/Stainless Steel\",\"Black Matte\",\"Brushed Nickel\",\"Gold\",\"Bronze\"],\"softClose\":false}', NULL),
(5, 'Mirrors', 'Mirrors & Specialty Glass', 'Glass', '2500.00', '[\"1c00867eac693e151f3663db39b397a2.jpg\",\"4e5f72c1809586c8f265ba2fb39318b2.jpg\"]', NULL, '2026-01-20 22:52:35', 'Available', 'Mirrors', 'direct', '2000.00', '3000.00', '{\"shape\":[\"Round\",\"Rectangle\",\"Oval\",\"Square\"],\"cornerRadius\":\"\",\"frameType\":[\"Frameless\",\"Framed\"],\"frameColor\":[\"White\",\"Gold\",\"Black\",\"Machine Polished Edges\",\"Beveled Edge\"],\"glassType\":[\"Copper Free and Lead Free Mirror\"],\"thickness\":[\"6mm\"],\"tintFinish\":[\"Bronze tint/color\",\"Grey tint (smoked)\",\"Colored glass\"],\"lighting\":[\"Integrated LED lighting\",\"Backlighting\",\"Front lighting\"],\"ledColorTemperature\":[\"Warm white\",\"Tunable white\",\"Cool white\"],\"control\":[\"Touch sensor button\",\"Defogger\",\"Dimmer\"],\"mountingMethod\":[\"Wall-mounted\",\"Adhesive\",\"Leaning\",\"Stand\"]}', NULL),
(6, 'Frameless Door', 'Doors', 'Glass', '2000.00', '[\"c4e5e34e31fc2e0c4b32c1a1b49dbc3c.jpg\",\"feb6e890524380c9d97400a6d4069138.jpg\"]', NULL, '2026-01-21 21:13:19', 'Available', 'Frameless', 'site-assessment', '1000.00', '3000.00', '{\"glassType\":[\"Clear\",\"Tinted\",\"Frosted\",\"Laminated\",\"Laminated safety glass\"],\"doorType\":[\"Single swing\",\"Single French door\",\"Double swing\",\"Double French doors\"],\"doorSwing\":[\"Left swing\",\"Left-hinged\",\"Right swing\",\"Right-hinged\"],\"fixedPanels\":[\"With fixed side/transom panels\",\"0 fixed panels\",\"Without fixed panels\",\"2 fixed panels\",\"More fixed panels\",\"With fixed side panel (left or right)\",\"With fixed transom\",\"Both\",\"1 fixed panel\"],\"configuration\":[\"With fixed side panel (left or right)\",\"With fixed transom\",\"Both\",\"Single swing door\",\"Double swing door\"],\"handleType\":[\"Various pull handle designs\",\"Decorative handles\",\"Various pull handles\"],\"hardwareFinish\":[\"Polished Chrome/Stainless Steel\",\"Matte Black\",\"Gold\",\"Brushed Nickel\",\"Chrome/Stainless Steel\"],\"gridPattern\":[\"External grids\",\"Prairie\",\"Custom grid designs\",\"French type grid\",\"Colonial\",\"Internal grids\"],\"glassTreatment\":[\"Frosted stripes (horizontal/vertical)\",\"Custom patterns\",\"Colors\"],\"installation\":[\"Patch fittings (minimalist hardware)\",\"Standard\"],\"hardware\":[\"Push/pull handles\",\"Closers\",\"Multi-point locks\",\"Locks\"],\"softClose\":false}', NULL),
(7, 'Top Glass', 'Mirrors & Specialty Glass', 'Glass', '1500.00', '[\"031d6e56c8aa1a4fd61dba00dffa39ef.jpg\",\"f3d0149161d614c19f397f4900858b35.jpg\",\"64324927ebc9db8e5052fff72cffee59.jpg\",\"4d359966411c2dcd1808c1c0dec4f4cb.jpg\"]', NULL, '2026-01-21 21:23:46', 'Available', 'Top Glass', 'direct', '1000.00', '2000.00', '{\"shape\":[\"Round\",\"Rectangle\",\"Oval\",\"Square\",\"Custom shapes\"],\"edgeFinish\":[\"Beveled\",\"Polished\",\"Raw\",\"Beveled edge\",\"Flat polished edge\",\"Pencil edge\"],\"mountingMethod\":[\"Wall-mounted\",\"Stand\",\"Adhesive\"]}', NULL),
(8, 'Glass Board', 'Mirrors & Specialty Glass', 'Glass', '1500.00', '[\"eedb3f9128501cd227f191559d54e885.jpg\",\"d69ee9aeee60b6033a116ef1f523551d.jpg\",\"fa0fce32b771d0718ec27eab8ffdb965.jpg\"]', 'Testing', '2026-01-21 22:02:08', 'Available', 'Glass Board', 'direct', '1000.00', '2000.00', '{\"shape\":[\"Round\",\"Rectangle\",\"Oval\",\"Square\"],\"edgeFinish\":[\"Beveled\",\"Polished\",\"Raw\",\"Beveled edge\",\"Flat polished edge\",\"Pencil edge\"],\"mountingMethod\":[\"Wall-mounted\",\"Stand\",\"Adhesive\"]}', NULL),
(31, '900 Series Window', 'Windows', 'Glass', '16000.00', '[\"469b1a719c020e50ee419d54dacd3ac4.jpg\"]', NULL, '2026-01-17 16:18:21', 'Available', 'Sliding', 'site-assessment', '12000.00', '20000.00', '{\"numberOfPanels\":[\"2 Panels\",\"4 Panels\"],\"transomType\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\",\"SAmpleeee\"],\"trackSystem\":[\"2 Tracks\",\"3 Tracks\"],\"panelConfiguration\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"frameColor\":[\"Hanalok\",\"White\",\"Black\",\"Gray\",\"Wood Finish\"],\"glassType\":[\"Clear\",\"Ultra Clear\",\"Bronze\",\"Light Green\",\"Dark Gray\",\"Copperfree Mirror\",\"Euro Gray\",\"Ford Blue\",\"Reflective: Clear\",\"Reflective: Gray\",\"Reflective: Light Blue\",\"Reflective: Dark Blue\",\"Reflective: Light Green\",\"Reflective: Dark Green\",\"Reflective: Light Bronze\",\"Tempered: Clear\",\"Tempered: Bronze\"],\"glassThickness\":[\"6mm\"],\"lockType\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"rollerType\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', NULL),
(32, '900 Series', 'Windows', 'Glass', '16620.50', '[\"c993e952ec11fe8df524f944ab53a851.png\"]', NULL, '2026-01-17 16:44:05', 'Available', 'Sliding', 'site-assessment', '13241.00', '20000.00', '{\"numberOfPanels\":[],\"transomType\":[],\"trackSystem\":[],\"panelConfiguration\":[],\"frameColor\":[],\"glassType\":[],\"glassThickness\":[],\"lockType\":[],\"rollerType\":[],\"screen\":[]}', NULL),
(33, 'Mirror', 'Mirrors & Specialty Glass', 'Glass', '7500.00', '[\"dfd48cb5304db5916bbfa1dd4af7ae25.png\"]', NULL, '2026-01-19 12:10:27', 'Available', 'Mirrors', 'direct', '5000.00', '10000.00', '{\"shape\":[\"Round\",\"Rectangle\",\"Oval\",\"Circle\",\"Square\",\"Rectangular with rounded edges\",\"Rectangular with arched top\",\"Custom shapes\"],\"cornerRadius\":\"\",\"frameType\":[\"Frameless\",\"Framed\",\"Gold frame\",\"Black frame\",\"White frame\",\"Framed (thin, metallic)\",\"Framed (dark, possibly black, grid frame)\",\"Framed (gold frame shown)\",\"Framed (thin matching frame possible)\"],\"frameColor\":[\"Gold frame\",\"Silver\",\"Rose Gold\",\"Other metallic finishes\",\"Wood\",\"Colored frames\",\"Black frame\",\"Other metallic or matte colors\",\"White frame\",\"Other colors\",\"Metal\",\"Silver/Metallic\",\"Other options\",\"Dark/Black\",\"Other frame colors available\"],\"edgeFinish\":[\"Beveled\",\"Polished\",\"Raw\",\"Beveled edge\",\"Flat polished edge\",\"Pencil edge\",\"Standard polished edge\",\"Standard (behind frame)\",\"Rounded edges\"],\"tintFinish\":[\"Bronze tint/color\",\"Grey tint (smoked)\",\"Colored glass\"],\"orientation\":[\"Vertical\",\"Horizontal\",\"Vertical/Full-body\"],\"style\":[\"French Type (grid/paneled design)\"],\"mountingMethod\":[\"Wall-mounted\",\"Stand\",\"Adhesive\",\"Leaning\",\"Wall-mounted (often fixed above vanity)\",\"Fixed wall mount\",\"Integrated hanger\",\"Rope hanger\",\"Chain\"],\"control\":[\"Touch sensor button\",\"Dimmer\",\"Defogger\"],\"additionalFeatures\":[\"Defogger\",\"Dimmer\"],\"arrangement\":[\"Can be displayed as triptych\",\"Individually\"],\"lighting\":[\"Integrated LED lighting\",\"Backlighting\",\"Front lighting\",\"Integrated LED options\"],\"ledColorTemperature\":[\"Warm white\",\"Cool white\",\"Tunable white\",\"RGB\"],\"gridPattern\":[\"French window style grid\"],\"quantity\":[\"Available in sets (3 sets, or individually)\"]}', NULL),
(34, 'Shower Enclosure', 'Windows', 'Glass', '5561111.00', '[\"bcc6887d1eee26fb76baddee96b5592e.png\"]', NULL, '2026-01-21 17:16:19', 'Available', 'Casement', 'direct', '11111.00', '11111111.00', '{\"transomType\":[],\"panelConfiguration\":[],\"width\":\"\",\"height\":\"\",\"h1\":\"\",\"frameColor\":[\"Brown (wood-grain)\",\"Bronze\",\"White\"],\"glassColor\":[],\"glassType\":[\"Frosted\",\"Laminated\",\"Low-E\",\"Tinted\",\"Clear\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"]}', NULL),
(37, 'YC-38 series window', 'Windows', 'Glass', '4210.00', '[\"ba4d7262f46bd9e543e7bc3f90b1ac1d.png\"]', NULL, '2026-01-22 03:09:16', 'Available', 'Casement', 'direct', '4210.00', NULL, '{\"transomType\":[\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"width\":\"\",\"height\":\"\",\"h1\":\"\",\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Frosted/Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"thickness\":[\"6mm\"]}', NULL),
(38, '75-series', 'Windows', 'Glass', '15061.00', '[\"3cdcb4c4a7de944de0d321bdec2c4c36.png\"]', NULL, '2026-01-22 04:22:16', 'Available', 'Casement', 'direct', '4122.00', '26000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"width\":\"\",\"height\":\"\",\"h1\":\"\",\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Frosted/Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"]}', NULL),
(39, '60-DMX', 'Windows', 'Glass', '15105.50', '[\"05c2cbf46a3d6ab09076dc4aebc05a88.jpg\"]', NULL, '2026-01-22 04:26:24', 'Available', 'Casement', 'direct', '4211.00', '26000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"width\":\"\",\"height\":\"\",\"h1\":\"\",\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Frosted/Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"]}', NULL),
(40, '85 series', 'Windows', 'Glass', '17211.00', '[\"41343452d03fa0a97b9293da851a921c.png\"]', NULL, '2026-01-22 04:44:16', 'Available', 'Casement', 'direct', '5422.00', '29000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"width\":\"\",\"height\":\"\",\"h1\":\"\",\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Frosted/Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"]}', NULL),
(41, 'YC-50 Series', 'Windows', 'Glass', '19166.50', '[\"d1f5f5cd550169b6731909e9c051cb0c.png\"]', NULL, '2026-01-22 14:57:45', 'Available', 'Casement', 'direct', '6333.00', '32000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"width\":\"\",\"height\":\"\",\"h1\":\"\",\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Frosted/Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"thickness\":[\"6mm\",\"8mm\"]}', 'YC-50 Series'),
(42, '755 series', 'Windows', 'Glass', '13444.00', '[\"82da1693b9d228110631813729e7c649.png\"]', NULL, '2026-01-22 15:33:19', 'Available', 'Casement', 'direct', '2222.00', '24666.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"width\":\"\",\"height\":\"\",\"h1\":\"\",\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Frosted/Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"]}', NULL),
(43, '60 DMX Series', 'Windows', 'Glass', '18166.00', '[\"476776b899cb5378b2ab18b5554ff39c.png\"]', NULL, '2026-01-22 15:39:19', 'Available', 'Casement', 'direct', '5332.00', '31000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"width\":\"\",\"height\":\"\",\"h1\":\"\",\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Frosted/Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"]}', '60-DMX Series'),
(44, '85 seriess', 'Windows', 'Glass', '16611.00', '[\"5ec0d503ea33d0cb1c3af9907d965968.png\"]', NULL, '2026-01-22 15:56:17', 'Available', 'Casement', 'direct', '2222.00', '31000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"width\":\"\",\"height\":\"\",\"h1\":\"\",\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Frosted/Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"]}', '85 Series');

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
-- Table structure for table `product_series`
--

CREATE TABLE `product_series` (
  `Series_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `SeriesName` varchar(255) NOT NULL COMMENT 'Name of the series (e.g., "Standard Series", "Premium Series")',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Width` decimal(10,2) DEFAULT NULL,
  `Height` decimal(10,2) DEFAULT NULL,
  `WidthUnit` varchar(10) DEFAULT 'in',
  `HeightUnit` varchar(10) DEFAULT 'in',
  `OtherOptions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`OtherOptions`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_series`
--

INSERT INTO `product_series` (`Series_ID`, `Product_ID`, `SeriesName`, `Created_Date`, `Width`, `Height`, `WidthUnit`, `HeightUnit`, `OtherOptions`) VALUES
(1, 3, 'Standard Series', '2026-01-14 12:13:00', NULL, NULL, 'in', 'in', NULL),
(2, 3, 'Premium Series', '2026-01-14 12:13:00', NULL, NULL, 'in', 'in', NULL),
(3, 4, 'Standard Series', '2026-01-14 12:14:23', NULL, NULL, 'in', 'in', NULL),
(4, 4, 'Premium Series', '2026-01-14 12:14:23', NULL, NULL, 'in', 'in', NULL),
(5, 5, 'Standard Series', '2026-01-14 12:16:35', NULL, NULL, 'in', 'in', NULL),
(6, 5, 'Premium Series', '2026-01-14 12:16:35', NULL, NULL, 'in', 'in', NULL),
(7, 6, 'Standard Series', '2026-01-14 12:17:11', NULL, NULL, 'in', 'in', NULL),
(8, 6, 'Premium Series', '2026-01-14 12:17:11', NULL, NULL, 'in', 'in', NULL),
(9, 7, '150 Series', '2026-01-14 05:28:09', NULL, NULL, 'in', 'in', NULL),
(10, 9, '700 Series', '2026-01-14 05:43:55', NULL, NULL, 'in', 'in', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_standard_sizes`
--

CREATE TABLE `product_standard_sizes` (
  `SizeID` int(11) NOT NULL,
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
  `OtherOptions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`OtherOptions`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_standard_sizes`
--

INSERT INTO `product_standard_sizes` (`SizeID`, `Series_ID`, `Width`, `Height`, `Price`, `Created_Date`, `Product_ID`, `SizeName`, `WidthUnit`, `HeightUnit`, `Shape`, `OtherOptions`) VALUES
(5, 3, '80.00', '100.00', '1200.00', '2026-01-14 12:14:23', 0, '', 'in', 'in', NULL, NULL),
(6, 3, '100.00', '120.00', '1500.00', '2026-01-14 12:14:23', 0, '', 'in', 'in', NULL, NULL),
(7, 3, '120.00', '150.00', '1800.00', '2026-01-14 12:14:23', 0, '', 'in', 'in', NULL, NULL),
(8, 3, '150.00', '180.00', '2200.00', '2026-01-14 12:14:23', 0, '', 'in', 'in', NULL, NULL),
(9, 4, '80.00', '100.00', '1500.00', '2026-01-14 12:14:23', 0, '', 'in', 'in', NULL, NULL),
(10, 4, '100.00', '120.00', '1800.00', '2026-01-14 12:14:23', 0, '', 'in', 'in', NULL, NULL),
(11, 4, '120.00', '150.00', '2200.00', '2026-01-14 12:14:23', 0, '', 'in', 'in', NULL, NULL),
(12, 4, '150.00', '180.00', '2700.00', '2026-01-14 12:14:23', 0, '', 'in', 'in', NULL, NULL),
(13, 5, '80.00', '100.00', '1200.00', '2026-01-14 12:16:35', 0, '', 'in', 'in', NULL, NULL),
(14, 5, '100.00', '120.00', '1500.00', '2026-01-14 12:16:35', 0, '', 'in', 'in', NULL, NULL),
(15, 5, '120.00', '150.00', '1800.00', '2026-01-14 12:16:35', 0, '', 'in', 'in', NULL, NULL),
(16, 5, '150.00', '180.00', '2200.00', '2026-01-14 12:16:35', 0, '', 'in', 'in', NULL, NULL),
(17, 6, '80.00', '100.00', '1500.00', '2026-01-14 12:16:35', 0, '', 'in', 'in', NULL, NULL),
(18, 6, '100.00', '120.00', '1800.00', '2026-01-14 12:16:35', 0, '', 'in', 'in', NULL, NULL),
(19, 6, '120.00', '150.00', '2200.00', '2026-01-14 12:16:35', 0, '', 'in', 'in', NULL, NULL),
(20, 6, '150.00', '180.00', '2700.00', '2026-01-14 12:16:35', 0, '', 'in', 'in', NULL, NULL),
(21, 7, '80.00', '100.00', '1200.00', '2026-01-14 12:17:11', 0, '', 'in', 'in', NULL, NULL),
(22, 7, '100.00', '120.00', '1500.00', '2026-01-14 12:17:11', 0, '', 'in', 'in', NULL, NULL),
(23, 7, '120.00', '150.00', '1800.00', '2026-01-14 12:17:11', 0, '', 'in', 'in', NULL, NULL),
(24, 7, '150.00', '180.00', '2200.00', '2026-01-14 12:17:11', 0, '', 'in', 'in', NULL, NULL),
(25, 8, '80.00', '100.00', '1500.00', '2026-01-14 12:17:11', 0, '', 'in', 'in', NULL, NULL),
(26, 8, '100.00', '120.00', '1800.00', '2026-01-14 12:17:11', 0, '', 'in', 'in', NULL, NULL),
(27, 8, '120.00', '150.00', '2200.00', '2026-01-14 12:17:11', 0, '', 'in', 'in', NULL, NULL),
(28, 8, '150.00', '180.00', '2700.00', '2026-01-14 12:17:11', 0, '', 'in', 'in', NULL, NULL),
(29, 9, '150.00', '150.00', '2500.00', '2026-01-14 05:28:09', 0, '', 'in', 'in', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_tag_prices`
--

CREATE TABLE `product_tag_prices` (
  `TagPriceID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `FieldID` varchar(100) NOT NULL COMMENT 'Field identifier (e.g., glassType, frameColor)',
  `TagName` varchar(255) NOT NULL COMMENT 'Tag/option name',
  `Price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price for this tag/option',
  `ImageUrl` varchar(255) DEFAULT NULL COMMENT 'Optional image URL for the tag',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `TagKey` varchar(100) NOT NULL,
  `TagValue` varchar(255) NOT NULL,
  `VisualConfig` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`VisualConfig`)),
  `Updated_Date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_tag_prices`
--

INSERT INTO `product_tag_prices` (`TagPriceID`, `Product_ID`, `FieldID`, `TagName`, `Price`, `ImageUrl`, `Created_Date`, `TagKey`, `TagValue`, `VisualConfig`, `Updated_Date`) VALUES
(1, 3, 'glassType', 'Clear', '0.00', 'uploads/tags/clear-glass.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(2, 3, 'glassType', 'Tinted', '150.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(3, 3, 'glassType', 'Laminated', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(4, 3, 'frameColor', 'White', '0.00', 'uploads/tags/white-frame.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(5, 3, 'frameColor', 'Black', '200.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(6, 3, 'frameColor', 'Silver', '250.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(7, 3, 'frameColor', 'Bronze', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(8, 3, 'frameColor', 'Wood', '500.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(9, 3, 'frameColor', 'Aluminum', '400.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(10, 3, 'thickness', '3mm', '-100.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(11, 3, 'thickness', '5mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(12, 3, 'thickness', '6mm', '150.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(13, 3, 'thickness', '8mm', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(14, 3, 'screen', 'Yes', '200.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(15, 4, 'glassType', 'Clear', '0.00', 'uploads/tags/clear-glass.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(16, 4, 'glassType', 'Tinted', '150.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(17, 4, 'glassType', 'Laminated', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(18, 4, 'frameColor', 'White', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(19, 4, 'frameColor', 'Black', '200.00', 'uploads/tags/black-frame.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(20, 4, 'frameColor', 'Silver', '250.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(21, 4, 'frameColor', 'Bronze', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(22, 4, 'frameColor', 'Wood', '500.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(23, 4, 'frameColor', 'Aluminum', '400.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(24, 4, 'thickness', '3mm', '-100.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(25, 4, 'thickness', '5mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(26, 4, 'thickness', '6mm', '150.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(27, 4, 'thickness', '8mm', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(28, 4, 'screen', 'Yes', '200.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:14:23', '', '', NULL, '2026-01-18 02:56:46'),
(29, 5, 'glassType', 'Clear', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(30, 5, 'glassType', 'Tinted', '150.00', 'uploads/tags/tinted-glass.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(31, 5, 'glassType', 'Laminated', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(32, 5, 'frameColor', 'White', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(33, 5, 'frameColor', 'Black', '200.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(34, 5, 'frameColor', 'Silver', '250.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(35, 5, 'frameColor', 'Bronze', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(36, 5, 'frameColor', 'Wood', '500.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(37, 5, 'frameColor', 'Aluminum', '400.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(38, 5, 'thickness', '3mm', '-100.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(39, 5, 'thickness', '5mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(40, 5, 'thickness', '6mm', '150.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(41, 5, 'thickness', '8mm', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(42, 5, 'screen', 'Yes', '200.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(43, 6, 'glassType', 'Clear', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(44, 6, 'glassType', 'Tinted', '150.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(45, 6, 'glassType', 'Laminated', '300.00', 'uploads/tags/laminated-glass.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(46, 6, 'frameColor', 'White', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(47, 6, 'frameColor', 'Black', '200.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(48, 6, 'frameColor', 'Silver', '250.00', 'uploads/tags/silver-frame.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(49, 6, 'frameColor', 'Bronze', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(50, 6, 'frameColor', 'Wood', '500.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(51, 6, 'frameColor', 'Aluminum', '400.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(52, 6, 'thickness', '3mm', '-100.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(53, 6, 'thickness', '5mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(54, 6, 'thickness', '6mm', '150.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(55, 6, 'thickness', '8mm', '300.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(56, 6, 'screen', 'Yes', '200.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(239, 33, 'shape', 'Round', '5000.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(240, 33, 'shape', 'Rectangle', '6000.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(241, 33, 'shape', 'Oval', '5500.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(242, 33, 'shape', 'Square', '4700.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(243, 33, 'shape', 'Rectangular with rounded edges', '6200.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(244, 33, 'shape', 'Rectangular with arched top', '6600.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(245, 33, 'frameType', 'Frameless', '244.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"transparent\",\"strokeWidth\":0,\"enabled\":true}', '2026-01-24 11:06:57'),
(246, 33, 'frameType', 'Framed', '241.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#333333\",\"strokeWidth\":6,\"enabled\":true}', '2026-01-24 11:06:57'),
(247, 33, 'frameType', 'Gold frame', '358.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#FFD700\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-24 11:06:57'),
(248, 33, 'frameType', 'Black frame', '311.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#000000\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-24 11:06:57'),
(249, 33, 'frameType', 'White frame', '290.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#FFFFFF\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-24 11:06:57'),
(250, 33, 'frameType', 'Framed (thin, metallic)', '345.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#333333\",\"strokeWidth\":6,\"enabled\":true}', '2026-01-24 11:06:57'),
(251, 33, 'frameType', 'Framed (dark, possibly black, grid frame)', '361.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#000000\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-24 11:06:57'),
(252, 33, 'frameType', 'Framed (gold frame shown)', '436.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#FFD700\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-24 11:06:57'),
(253, 33, 'frameType', 'Framed (thin matching frame possible)', '399.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#333333\",\"strokeWidth\":6,\"enabled\":true}', '2026-01-24 11:06:57'),
(254, 33, 'frameColor', 'Gold frame', '320.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#FFD700\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-24 11:06:57'),
(255, 33, 'frameColor', 'Silver', '123.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#C0C0C0\",\"strokeWidth\":3,\"enabled\":true}', '2026-01-24 11:06:57'),
(256, 33, 'frameColor', 'Rose Gold', '425.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#FFD700\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-24 11:06:57'),
(257, 33, 'frameColor', 'Wood', '241.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#795548\",\"strokeWidth\":6,\"enabled\":true}', '2026-01-24 11:06:57'),
(258, 33, 'frameColor', 'Black frame', '321.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#000000\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-24 11:06:57'),
(259, 33, 'frameColor', 'White frame', '297.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#FFFFFF\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-24 11:06:57'),
(260, 33, 'frameColor', 'Metal', '432.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(261, 33, 'frameColor', 'Silver/Metallic', '479.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#C0C0C0\",\"strokeWidth\":3,\"enabled\":true}', '2026-01-24 11:06:57'),
(262, 33, 'frameColor', 'Dark/Black', '421.00', NULL, '2026-01-23 20:06:57', '', '', '{\"stroke\":\"#000000\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-24 11:06:57'),
(263, 33, 'edgeFinish', 'Beveled', '123.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(264, 33, 'edgeFinish', 'Polished', '109.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(265, 33, 'edgeFinish', 'Raw', '103.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(266, 33, 'edgeFinish', 'Beveled edge', '102.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(267, 33, 'edgeFinish', 'Flat polished edge', '122.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(268, 33, 'edgeFinish', 'Pencil edge', '123.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(269, 33, 'edgeFinish', 'Standard polished edge', '131.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(270, 33, 'edgeFinish', 'Standard (behind frame)', '133.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(271, 33, 'edgeFinish', 'Rounded edges', '132.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(272, 33, 'tintFinish', 'Bronze tint/color', '222.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(273, 33, 'tintFinish', 'Grey tint (smoked)', '231.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(274, 33, 'tintFinish', 'Colored glass', '234.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(275, 33, 'orientation', 'Vertical', '111.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(276, 33, 'orientation', 'Horizontal', '122.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(277, 33, 'orientation', 'Vertical/Full-body', '131.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(278, 33, 'style', 'French Type (grid/paneled design)', '241.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(279, 33, 'mountingMethod', 'Wall-mounted', '111.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(280, 33, 'mountingMethod', 'Stand', '90.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(281, 33, 'mountingMethod', 'Adhesive', '123.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(282, 33, 'mountingMethod', 'Leaning', '152.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(283, 33, 'mountingMethod', 'Wall-mounted (often fixed above vanity)', '132.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(284, 33, 'mountingMethod', 'Fixed wall mount', '152.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(285, 33, 'mountingMethod', 'Integrated hanger', '111.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(286, 33, 'mountingMethod', 'Rope hanger', '111.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(287, 33, 'mountingMethod', 'Chain', '104.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(288, 33, 'control', 'Touch sensor button', '245.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(289, 33, 'control', 'Dimmer', '299.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(290, 33, 'control', 'Defogger', '209.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(291, 33, 'additionalFeatures', 'Defogger', '211.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(292, 33, 'additionalFeatures', 'Dimmer', '211.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(293, 33, 'ledColorTemperature', 'Warm white', '222.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(294, 33, 'ledColorTemperature', 'Cool white', '222.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(295, 33, 'ledColorTemperature', 'Tunable white', '222.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(296, 33, 'ledColorTemperature', 'RGB', '222.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(297, 33, 'gridPattern', 'French window style grid', '163.00', NULL, '2026-01-23 20:06:57', '', '', NULL, '2026-01-24 11:06:57'),
(298, 31, 'transomType', 'SAmpleeee', '0.00', NULL, '2026-01-23 21:25:00', '', '', NULL, '2026-01-24 12:25:00');

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

--
-- Dumping data for table `projectschedule`
--

INSERT INTO `projectschedule` (`Schedule_ID`, `OrderID`, `Admin_ID`, `Project_Name`, `Start_Date`, `End_Date`, `Status`) VALUES
(1, 7, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(2, 23, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(3, 16, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(4, 25, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(5, 26, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(6, 24, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(7, 42, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(8, 75, 2, '', '0000-00-00', '0000-00-00', 'In progress');

-- --------------------------------------------------------

--
-- Table structure for table `quotation`
--

CREATE TABLE `quotation` (
  `QuotationID` int(11) NOT NULL,
  `QuotationNumber` varchar(50) NOT NULL COMMENT 'Formatted: QT001, QT002, etc.',
  `Customer_ID` int(11) NOT NULL,
  `SalesRep_ID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `Quotation_num` varchar(20) NOT NULL,
  `Total_amount` decimal(10,2) DEFAULT NULL,
  `Notes` text DEFAULT NULL COMMENT 'Admin notes',
  `Status` enum('Pending','Approved','Rejected','Converted to Order') DEFAULT 'Pending',
  `ExpiryDate` date DEFAULT NULL COMMENT 'Quotation validity expiry date',
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `ConvertedToOrder_ID` int(11) DEFAULT NULL COMMENT 'Order ID if converted to order',
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
-- Table structure for table `return_order`
--

CREATE TABLE `return_order` (
  `ReturnID` int(11) NOT NULL,
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
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
(1, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI001 has been created and requires your review', 'Read', '2026-01-10 13:50:34', '2026-01-12 14:24:20', 1, 'Order'),
(2, 'fa-check-circle', 'Sales Representative', 'Order Approved: GI001 has been approved', 'Read', '2026-01-10 13:53:04', '2026-01-12 14:24:20', 1, 'Order'),
(3, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI002 has been created and requires your review', 'Read', '2026-01-10 14:53:04', '2026-01-12 14:24:20', 2, 'Order'),
(4, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI003 has been created and requires your review', 'Unread', '2026-01-12 14:40:51', NULL, 3, 'Order'),
(5, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI004 has been created and requires your review', 'Unread', '2026-01-12 15:24:05', NULL, 4, 'Order'),
(6, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI006 (Amount: ₱26,671.72) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:28:48', NULL, 6, 'Payment'),
(7, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:29:21', NULL, 5, 'Payment'),
(8, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:29:21', NULL, 5, 'Payment'),
(9, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:39:15', NULL, 4, 'Payment'),
(10, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Unread', '2025-12-08 14:39:17', NULL, 4, 'Payment'),
(11, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI010 has been created and requires your review', 'Unread', '2025-12-08 23:52:00', NULL, 13, 'Order'),
(12, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI011 has been created and requires your review', 'Unread', '2025-12-08 23:54:26', NULL, 14, 'Order'),
(13, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI012 has been created and requires your review', 'Unread', '2025-12-09 00:11:37', NULL, 15, 'Order'),
(14, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI013 has been created and requires your review', 'Unread', '2025-12-09 00:21:46', NULL, 16, 'Order'),
(15, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI014 has been created and requires your review', 'Unread', '2025-12-09 00:29:29', NULL, 17, 'Order'),
(16, 'fa-check-circle', 'Sales Representative', 'Order Approved: GI014 has been approved', 'Unread', '2025-12-09 00:34:53', NULL, 17, 'Order'),
(17, 'fa-times-circle', 'Sales Representative', 'Order Disapproved: GI013 has been disapproved (Reason: Order disapproved by Admin and finalized by Sales Representative)', 'Unread', '2025-12-09 00:37:36', NULL, 16, 'Order'),
(18, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI015 has been created and requires your review', 'Unread', '2025-12-09 01:34:07', NULL, 18, 'Order'),
(19, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI016 has been created and requires your review', 'Unread', '2025-12-09 01:35:21', NULL, 19, 'Order'),
(20, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI017 has been created and requires your review', 'Unread', '2025-12-09 01:56:30', NULL, 20, 'Order'),
(21, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI017 (Amount: ₱50,925.50) has been marked as paid by Sales Test.', 'Unread', '2025-12-09 02:00:13', NULL, 17, 'Payment'),
(22, 'fa-money-bill-wave', 'Sales Representative', 'Payment Updated: Payment for Order GI017 has been marked as paid', 'Unread', '2025-12-09 02:00:13', NULL, 17, 'Payment'),
(23, 'fa-shopping-cart', 'Sales Representative', 'Payment Received: Payment for Order GI017 (Amount: ₱50,925.50) has been marked as paid by Sales Test.', 'Unread', '2025-12-09 02:00:13', NULL, 17, 'Payment'),
(24, 'fa-money-bill-wave', 'Sales Representative', 'Payment Updated: Payment for Order GI017 has been marked as paid', 'Unread', '2025-12-09 02:00:13', NULL, 17, 'Payment'),
(25, 'fa-check-circle', 'Sales Representative', 'Order Approved: GI017 has been approved', 'Unread', '2025-12-09 02:09:52', NULL, 20, 'Order'),
(26, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI018 has been created and requires your review', 'Unread', '2026-01-10 02:31:29', NULL, 21, 'Order'),
(27, 'fa-check-circle', 'Sales Representative', 'Order Approved: GI018 has been approved', 'Unread', '2026-01-12 20:49:33', NULL, 21, 'Order'),
(28, 'fa-check-circle', 'Sales Representative', 'Order Approved: GI016 has been approved', 'Unread', '2026-01-12 20:49:47', NULL, 19, 'Order'),
(29, 'fa-times-circle', 'Sales Representative', 'Order Disapproved: GI001 has been disapproved (Reason: Order disapproved by Admin and finalized by Sales Representative)', 'Unread', '2026-01-12 20:50:04', NULL, 4, 'Order'),
(30, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI019 has been created and requires your review', 'Unread', '2026-01-12 21:59:20', NULL, 22, 'Order'),
(31, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI001 has been created and requires your review', 'Unread', '2026-01-18 21:07:38', NULL, 1, 'Order'),
(32, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI002 has been created and requires your review', 'Unread', '2026-01-19 23:06:54', NULL, 2, 'Order'),
(33, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI003 has been created and requires your review', 'Unread', '2026-01-20 00:14:30', NULL, 3, 'Order'),
(34, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI004 has been created and requires your review', 'Unread', '2026-01-20 00:31:49', NULL, 4, 'Order'),
(35, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI005 has been created and requires your review', 'Unread', '2026-01-20 01:14:50', NULL, 5, 'Order'),
(36, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI006 has been created and requires your review', 'Unread', '2026-01-20 02:04:50', NULL, 6, 'Order'),
(37, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI007 has been created and requires your review', 'Unread', '2026-01-20 02:31:31', NULL, 7, 'Order'),
(38, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI008 has been created and requires your review', 'Unread', '2026-01-20 02:37:10', NULL, 8, 'Order'),
(39, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI009 has been created and requires your review', 'Unread', '2026-01-20 03:40:14', NULL, 9, 'Order'),
(40, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI010 has been created and requires your review', 'Unread', '2026-01-20 04:06:43', NULL, 10, 'Order'),
(41, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI011 has been created and requires your review', 'Unread', '2026-01-20 04:07:25', NULL, 11, 'Order'),
(42, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI012 has been created and requires your review', 'Unread', '2026-01-20 04:14:14', NULL, 12, 'Order'),
(43, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI013 has been created and requires your review', 'Unread', '2026-01-20 04:21:16', NULL, 13, 'Order'),
(44, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI014 has been created and requires your review', 'Unread', '2026-01-20 04:23:56', NULL, 14, 'Order'),
(45, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI015 has been created and requires your review', 'Unread', '2026-01-20 04:24:57', NULL, 15, 'Order'),
(46, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI016 has been created and requires your review', 'Unread', '2026-01-21 09:18:34', NULL, 16, 'Order'),
(47, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI017 has been created and requires your review', 'Unread', '2026-01-21 09:56:54', NULL, 17, 'Order'),
(48, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI018 has been created and requires your review', 'Unread', '2026-01-21 09:57:26', NULL, 18, 'Order'),
(49, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI019 has been created and requires your review', 'Unread', '2026-01-21 09:58:39', NULL, 19, 'Order'),
(50, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI020 has been created and requires your review', 'Unread', '2026-01-21 14:36:17', NULL, 20, 'Order'),
(51, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI021 has been created and requires your review', 'Unread', '2026-01-21 14:40:16', NULL, 21, 'Order'),
(52, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI022 has been created and requires your review', 'Unread', '2026-01-21 14:41:25', NULL, 22, 'Order'),
(53, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI023 has been created and requires your review', 'Unread', '2026-01-21 14:42:55', NULL, 23, 'Order'),
(54, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI024 has been created and requires your review', 'Unread', '2026-01-21 14:53:48', NULL, 24, 'Order'),
(55, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI025 has been created and requires your review', 'Unread', '2026-01-23 01:24:45', NULL, 25, 'Order'),
(56, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI026 has been created and requires your review', 'Unread', '2026-01-23 02:29:59', NULL, 26, 'Order'),
(57, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI027 has been created and requires your review', 'Unread', '2026-01-23 04:05:29', NULL, 27, 'Order'),
(58, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI028 has been created and requires your review', 'Unread', '2026-01-23 07:10:48', NULL, 28, 'Order'),
(59, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI029 has been created and requires your review', 'Unread', '2026-01-23 07:11:11', NULL, 29, 'Order'),
(60, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI030 has been created and requires your review', 'Unread', '2026-01-23 12:53:52', NULL, 30, 'Order'),
(61, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI031 has been created and requires your review', 'Unread', '2026-01-23 12:54:45', NULL, 31, 'Order'),
(62, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI032 has been created and requires your review', 'Unread', '2026-01-23 13:10:14', NULL, 32, 'Order'),
(63, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI033 has been created and requires your review', 'Unread', '2026-01-23 13:29:11', NULL, 33, 'Order'),
(64, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI034 has been created and requires your review', 'Unread', '2026-01-23 14:05:52', NULL, 34, 'Order'),
(65, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI035 has been created and requires your review', 'Unread', '2026-01-23 14:09:20', NULL, 35, 'Order'),
(66, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI036 has been created and requires your review', 'Unread', '2026-01-23 14:11:59', NULL, 36, 'Order'),
(67, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI037 has been created and requires your review', 'Unread', '2026-01-23 14:15:45', NULL, 37, 'Order'),
(68, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI038 has been created and requires your review', 'Unread', '2026-01-23 14:19:12', NULL, 38, 'Order'),
(69, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI039 has been created and requires your review', 'Unread', '2026-01-24 05:37:32', NULL, 39, 'Order'),
(70, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI040 has been created and requires your review', 'Unread', '2026-01-24 05:38:08', NULL, 40, 'Order'),
(71, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI041 has been created and requires your review', 'Unread', '2026-01-24 05:51:19', NULL, 41, 'Order'),
(72, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI042 has been created and requires your review', 'Unread', '2026-01-24 06:04:43', NULL, 42, 'Order'),
(73, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI043 has been created and requires your review', 'Unread', '2026-01-24 10:30:12', NULL, 43, 'Order'),
(74, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI044 has been created and requires your review', 'Unread', '2026-01-24 10:33:55', NULL, 44, 'Order'),
(75, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI045 has been created and requires your review', 'Unread', '2026-01-24 10:39:17', NULL, 45, 'Order'),
(76, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI046 has been created and requires your review', 'Unread', '2026-01-24 10:44:01', NULL, 46, 'Order'),
(77, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI047 has been created and requires your review', 'Unread', '2026-01-24 11:09:20', NULL, 47, 'Order'),
(78, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI048 has been created and requires your review', 'Unread', '2026-01-25 19:38:31', NULL, 48, 'Order'),
(79, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI049 has been created and requires your review', 'Unread', '2026-01-25 19:42:39', NULL, 49, 'Order'),
(80, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI050 has been created and requires your review', 'Unread', '2026-01-25 19:59:39', NULL, 50, 'Order'),
(81, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI051 has been created and requires your review', 'Unread', '2026-01-25 19:59:49', NULL, 51, 'Order'),
(82, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI052 has been created and requires your review', 'Unread', '2026-01-25 20:02:46', NULL, 52, 'Order'),
(83, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI053 has been created and requires your review', 'Unread', '2026-01-25 20:05:14', NULL, 53, 'Order'),
(84, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI054 has been created and requires your review', 'Unread', '2026-01-25 20:06:38', NULL, 54, 'Order'),
(85, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI055 has been created and requires your review', 'Unread', '2026-01-25 20:41:59', NULL, 55, 'Order'),
(86, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI056 has been created and requires your review', 'Unread', '2026-01-25 21:03:36', NULL, 56, 'Order'),
(87, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI057 has been created and requires your review', 'Unread', '2026-01-25 21:20:07', NULL, 57, 'Order'),
(88, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI058 has been created and requires your review', 'Unread', '2026-01-25 22:07:09', NULL, 58, 'Order'),
(89, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI059 has been created and requires your review', 'Unread', '2026-01-25 22:08:38', NULL, 59, 'Order'),
(90, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI060 has been created and requires your review', 'Unread', '2026-01-25 22:27:14', NULL, 60, 'Order'),
(91, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI061 has been created and requires your review', 'Unread', '2026-01-25 22:35:56', NULL, 61, 'Order'),
(92, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI062 has been created and requires your review', 'Unread', '2026-01-25 22:37:12', NULL, 62, 'Order'),
(93, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI063 has been created and requires your review', 'Unread', '2026-01-25 22:42:24', NULL, 63, 'Order'),
(94, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI064 has been created and requires your review', 'Unread', '2026-01-25 22:55:05', NULL, 64, 'Order'),
(95, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI065 has been created and requires your review', 'Unread', '2026-01-25 22:56:00', NULL, 65, 'Order'),
(96, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI066 has been created and requires your review', 'Unread', '2026-01-25 23:17:59', NULL, 66, 'Order'),
(97, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI067 has been created and requires your review', 'Unread', '2026-01-25 23:19:17', NULL, 67, 'Order'),
(98, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI068 has been created and requires your review', 'Unread', '2026-01-26 02:48:11', NULL, 68, 'Order'),
(99, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI069 has been created and requires your review', 'Unread', '2026-01-26 03:02:19', NULL, 69, 'Order'),
(100, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI070 has been created and requires your review', 'Unread', '2026-01-26 03:03:52', NULL, 70, 'Order'),
(101, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI071 has been created and requires your review', 'Unread', '2026-01-26 03:14:58', NULL, 71, 'Order'),
(102, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI072 has been created and requires your review', 'Unread', '2026-01-26 03:19:42', NULL, 72, 'Order'),
(103, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI073 has been created and requires your review', 'Unread', '2026-01-26 03:23:51', NULL, 73, 'Order'),
(104, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI074 has been created and requires your review', 'Unread', '2026-01-26 03:29:32', NULL, 74, 'Order'),
(105, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI075 has been created and requires your review', 'Unread', '2026-01-26 03:32:43', NULL, 75, 'Order'),
(106, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI076 has been created and requires your review', 'Unread', '2026-01-26 04:21:31', NULL, 76, 'Order'),
(107, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI077 has been created and requires your review', 'Unread', '2026-01-26 04:31:44', NULL, 77, 'Order'),
(108, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI078 has been created and requires your review', 'Unread', '2026-01-26 04:31:48', NULL, 78, 'Order'),
(109, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI079 has been created and requires your review', 'Unread', '2026-01-26 04:43:59', NULL, 79, 'Order'),
(110, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI080 has been created and requires your review', 'Unread', '2026-01-26 04:55:46', NULL, 80, 'Order'),
(111, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI081 has been created and requires your review', 'Unread', '2026-01-26 05:01:34', NULL, 81, 'Order'),
(112, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI082 has been created and requires your review', 'Unread', '2026-01-26 05:11:33', NULL, 82, 'Order'),
(113, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI083 has been created and requires your review', 'Unread', '2026-01-26 05:17:42', NULL, 83, 'Order'),
(114, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI084 has been created and requires your review', 'Unread', '2026-01-26 05:26:52', NULL, 84, 'Order'),
(115, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI085 has been created and requires your review', 'Unread', '2026-01-26 06:10:22', NULL, 85, 'Order'),
(116, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI086 has been created and requires your review', 'Unread', '2026-01-26 06:20:17', NULL, 86, 'Order'),
(117, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI087 has been created and requires your review', 'Unread', '2026-01-26 06:30:11', NULL, 87, 'Order'),
(118, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI088 has been created and requires your review', 'Unread', '2026-01-26 06:30:27', NULL, 88, 'Order'),
(119, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI089 has been created and requires your review', 'Unread', '2026-01-26 09:54:15', NULL, 89, 'Order'),
(120, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI090 has been created and requires your review', 'Unread', '2026-01-26 10:45:48', NULL, 90, 'Order'),
(121, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI091 has been created and requires your review', 'Unread', '2026-01-26 10:54:30', NULL, 91, 'Order');

-- --------------------------------------------------------

--
-- Table structure for table `status_history`
--

CREATE TABLE `status_history` (
  `StatusHistoryID` int(11) NOT NULL,
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
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `stock_transactions`
--

INSERT INTO `stock_transactions` (`transaction_id`, `InventoryItemID`, `transaction_type`, `quantity`, `reason`, `previous_stock`, `new_stock`, `user_id`, `timestamp`) VALUES
(2, 2, 'add', 15, 'Initial stock', 0, 15, NULL, '2025-12-08 12:32:32'),
(3, 3, 'add', 20, 'Initial stock', 0, 20, NULL, '2025-12-08 12:34:01'),
(4, 2, 'add', 14, '', 15, 29, 5, '2025-12-08 12:39:21'),
(5, 2, 'remove', 21, '', 29, 8, 5, '2025-12-08 12:47:45'),
(6, 4, 'add', 16, 'Initial stock', 0, 16, NULL, '2025-12-08 13:20:56'),
(7, 5, 'add', 14, 'Initial stock', 0, 14, NULL, '2025-12-08 14:04:36');

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
(26, 'Payment Received', 'Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:39:17', 4, 'Payment'),
(27, 'Order Disapproved by Admin', 'Order GI001 disapproved by Admin. Reason: Wala gusto ko lang', 'Admin', 2, NULL, '2025-12-08 15:39:33', 4, 'Order'),
(28, 'Order Created', 'New order created: GI007 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-08 16:04:00', 10, 'Order'),
(29, 'Order Created', 'New order created: GI008 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-08 22:18:33', 11, 'Order'),
(30, 'Order Created', 'New order created: GI009 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-08 22:23:02', 12, 'Order'),
(31, 'Order Status Updated', 'Order GI009 status updated to: Awaiting Admin', 'System', NULL, NULL, '2025-12-08 22:29:02', 12, 'Order'),
(32, 'Approval Requested', 'Order GI009 approval requested by Sales Rep. Notes: None', 'Sales Representative', 3, NULL, '2025-12-08 22:29:02', 12, 'Order'),
(33, 'Order Approved by Admin', 'Order GI009 approved by Admin. Notes: None', 'Admin', 2, NULL, '2025-12-08 22:30:02', 12, 'Order'),
(34, 'Order Approved', 'Order GI009 has been approved by Sales Rep. Customer can now proceed with payment.', 'Sales Representative', 3, NULL, '2025-12-08 22:30:42', 12, 'Order'),
(35, 'Order Created', 'New order created: GI010 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-08 23:52:00', 13, 'Order'),
(36, 'Order Created', 'New order created: GI011 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-08 23:54:26', 14, 'Order'),
(37, 'Order Created', 'New order created: GI012 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-09 00:11:37', 15, 'Order'),
(38, 'Order Created', 'New order created: GI013 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-09 00:21:46', 16, 'Order'),
(39, 'Order Created', 'New order created: GI014 (Customer ID: 2)', 'Customer', NULL, NULL, '2025-12-09 00:29:29', 17, 'Order'),
(40, 'Order Status Updated', 'Order GI014 status updated to: Awaiting Admin', 'System', NULL, NULL, '2025-12-09 00:32:55', 17, 'Order'),
(41, 'Approval Requested', 'Order GI014 approval requested by Sales Rep. Notes: None', 'Sales Representative', 3, NULL, '2025-12-09 00:32:55', 17, 'Order'),
(42, 'Order Approved by Admin', 'Order GI014 approved by Admin. Notes: None', 'Admin', 2, NULL, '2025-12-09 00:34:17', 17, 'Order'),
(43, 'Order Status Updated', 'Order GI013 status updated to: Awaiting Admin', 'System', NULL, NULL, '2025-12-09 00:34:33', 16, 'Order'),
(44, 'Approval Requested', 'Order GI013 approval requested by Sales Rep. Notes: None', 'Sales Representative', 3, NULL, '2025-12-09 00:34:33', 16, 'Order'),
(45, 'Order Approved', 'Order GI014 has been approved by Sales Rep. Customer can now proceed with payment.', 'Sales Representative', 3, NULL, '2025-12-09 00:34:53', 17, 'Order'),
(46, 'Order Disapproved by Admin', 'Order GI013 disapproved by Admin. Reason: Holiday season', 'Admin', 2, NULL, '2025-12-09 00:36:59', 16, 'Order'),
(47, 'Order Disapproved', 'Order GI013 disapproved by Sales Rep. Reason: Admin Reason: Holiday season | Sales Rep Finalization: Order disapproved by Admin and finalized by Sales Representative', 'Sales Representative', 3, NULL, '2025-12-09 00:37:36', 16, 'Order'),
(48, 'Order Status Updated', 'Order GI012 status updated to: Awaiting Admin', 'System', NULL, NULL, '2025-12-09 00:46:16', 15, 'Order'),
(49, 'Approval Requested', 'Order GI012 approval requested by Sales Rep. Notes: None', 'Sales Representative', 3, NULL, '2025-12-09 00:46:16', 15, 'Order'),
(50, 'Order Created', 'New order created: GI015 (Customer ID: 1)', 'Customer', NULL, NULL, '2025-12-09 01:34:07', 18, 'Order'),
(51, 'Order Created', 'New order created: GI016 (Customer ID: 1)', 'Customer', NULL, NULL, '2025-12-09 01:35:21', 19, 'Order'),
(52, 'Order Created', 'New order created: GI017 (Customer ID: 3)', 'Customer', NULL, NULL, '2025-12-09 01:56:30', 20, 'Order'),
(53, 'Payment Received', 'Payment for Order GI017 (Amount: ₱50,925.50) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-09 02:00:13', 17, 'Payment'),
(54, 'Payment Received', 'Payment for Order GI017 (Amount: ₱50,925.50) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-09 02:00:13', 17, 'Payment'),
(55, 'Order Status Updated', 'Order GI017 status updated to: Awaiting Admin', 'System', NULL, NULL, '2025-12-09 02:00:38', 20, 'Order'),
(56, 'Approval Requested', 'Order GI017 approval requested by Sales Rep. Notes: None', 'Sales Representative', 3, NULL, '2025-12-09 02:00:38', 20, 'Order'),
(57, 'Order Approved by Admin', 'Order GI017 approved by Admin. Notes: None', 'Admin', 2, NULL, '2025-12-09 02:06:17', 20, 'Order'),
(58, 'Order Approved', 'Order GI017 has been approved by Sales Rep. Customer can now proceed with payment.', 'Sales Representative', 3, NULL, '2025-12-09 02:09:52', 20, 'Order'),
(59, 'Order Created', 'New order created: GI018 (Customer ID: 2)', 'Customer', NULL, NULL, '2026-01-10 02:31:29', 21, 'Order'),
(60, 'Order Status Updated', 'Order GI018 status updated to: Awaiting Admin', 'System', NULL, NULL, '2026-01-12 19:42:21', 21, 'Order'),
(61, 'Approval Requested', 'Order GI018 approval requested by Sales Rep. Notes: None', 'Sales Representative', 3, NULL, '2026-01-12 19:42:21', 21, 'Order'),
(62, 'Order Status Updated', 'Order GI016 status updated to: Awaiting Admin', 'System', NULL, NULL, '2026-01-12 19:43:04', 19, 'Order'),
(63, 'Approval Requested', 'Order GI016 approval requested by Sales Rep. Notes: None', 'Sales Representative', 3, NULL, '2026-01-12 19:43:04', 19, 'Order'),
(64, 'Order Status Updated', 'Order GI015 status updated to: Awaiting Admin', 'System', NULL, NULL, '2026-01-12 19:43:11', 18, 'Order'),
(65, 'Approval Requested', 'Order GI015 approval requested by Sales Rep. Notes: None', 'Sales Representative', 3, NULL, '2026-01-12 19:43:11', 18, 'Order'),
(66, 'Order Approved by Admin', 'Order GI018 approved by Admin. Notes: None', 'Admin', 2, NULL, '2026-01-12 19:54:19', 21, 'Order'),
(67, 'Order Approved by Admin', 'Order GI016 approved by Admin. Notes: None', 'Admin', 2, NULL, '2026-01-12 19:54:21', 19, 'Order'),
(68, 'Order Approved', 'Order GI018 has been approved by Sales Rep. Customer can now proceed with payment.', 'Sales Representative', 3, NULL, '2026-01-12 20:49:33', 21, 'Order'),
(69, 'Order Approved', 'Order GI016 has been approved by Sales Rep. Customer can now proceed with payment.', 'Sales Representative', 3, NULL, '2026-01-12 20:49:46', 19, 'Order'),
(70, 'Order Disapproved', 'Order GI001 disapproved by Sales Rep. Reason: Admin Reason: Wala gusto ko lang | Sales Rep Finalization: Order disapproved by Admin and finalized by Sales Representative', 'Sales Representative', 3, NULL, '2026-01-12 20:50:04', 4, 'Order'),
(71, 'Order Created', 'New order created: GI019 (Customer ID: 2)', 'Customer', NULL, NULL, '2026-01-12 21:59:20', 22, 'Order'),
(72, 'Order Created', 'New order created: GI001 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-18 21:07:38', 1, 'Order'),
(73, 'Staff Assigned', 'fabrication staff assigned to order GI001: Admin Test', 'Admin', 2, 'Admin Test', '2026-01-19 10:02:40', 1, 'Order'),
(74, 'Order Created', 'New order created: GI002 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-19 23:06:54', 2, 'Order'),
(75, 'Order Created', 'New order created: GI003 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 00:14:30', 3, 'Order'),
(76, 'Order Created', 'New order created: GI004 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 00:31:49', 4, 'Order'),
(77, 'Order Created', 'New order created: GI005 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 01:14:50', 5, 'Order'),
(78, 'Order Created', 'New order created: GI006 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 02:04:50', 6, 'Order'),
(79, 'Order Created', 'New order created: GI007 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 02:31:31', 7, 'Order'),
(80, 'Order Status Updated', 'Order GI006 status changed from  to Cancelled', 'Admin', 2, 'Admin Test', '2026-01-20 02:35:08', 6, 'Order'),
(81, 'Order Created', 'New order created: GI008 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 02:37:10', 8, 'Order'),
(82, 'Order Status Updated', 'Order GI007 status changed from Pending Review to Approved', 'Admin', 2, 'Admin Test', '2026-01-20 02:39:16', 7, 'Order'),
(83, 'Order Created', 'New order created: GI009 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 03:40:14', 9, 'Order'),
(84, 'Order Created', 'New order created: GI010 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 04:06:43', 10, 'Order'),
(85, 'Order Created', 'New order created: GI011 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 04:07:25', 11, 'Order'),
(86, 'Order Created', 'New order created: GI012 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 04:14:14', 12, 'Order'),
(87, 'Order Created', 'New order created: GI013 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 04:21:16', 13, 'Order'),
(88, 'Order Created', 'New order created: GI014 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 04:23:56', 14, 'Order'),
(89, 'Order Created', 'New order created: GI015 (Customer ID: 3)', 'Customer', NULL, NULL, '2026-01-20 04:24:57', 15, 'Order'),
(90, 'Order Created', 'New order created: GI016 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-21 09:18:34', 16, 'Order'),
(91, 'Order Status Updated', 'Order GI016 status changed from  to Approved', 'Admin', 2, 'Admin Test', '2026-01-21 09:19:09', 16, 'Order'),
(92, 'Order Created', 'New order created: GI017 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-21 09:56:54', 17, 'Order'),
(93, 'Order Created', 'New order created: GI018 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-21 09:57:26', 18, 'Order'),
(94, 'Order Created', 'New order created: GI019 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-21 09:58:39', 19, 'Order'),
(95, 'Order Created', 'New order created: GI020 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-21 14:36:17', 20, 'Order'),
(96, 'Order Created', 'New order created: GI021 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-21 14:40:16', 21, 'Order'),
(97, 'Order Created', 'New order created: GI022 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-21 14:41:25', 22, 'Order'),
(98, 'Order Created', 'New order created: GI023 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-21 14:42:55', 23, 'Order'),
(99, 'Order Status Updated', 'Order GI023 status changed from Pending Payment to Paid', 'Admin', 2, 'Admin Test', '2026-01-21 14:47:41', 23, 'Order'),
(100, 'Order Status Updated', 'Order GI023 status changed from Paid to Payment Verified', 'Admin', 2, 'Admin Test', '2026-01-21 14:48:14', 23, 'Order'),
(101, 'Order Status Updated', 'Order GI023 status changed from Payment Verified to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 14:48:52', 23, 'Order'),
(102, 'Order Status Updated', 'Order GI023 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 14:49:06', 23, 'Order'),
(103, 'Order Status Updated', 'Order GI023 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 14:50:07', 23, 'Order'),
(104, 'Order Status Updated', 'Order GI023 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 14:51:15', 23, 'Order'),
(105, 'Order Status Updated', 'Order GI023 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 14:52:51', 23, 'Order'),
(106, 'Order Created', 'New order created: GI024 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-21 14:53:48', 24, 'Order'),
(107, 'Order Status Updated', 'Order GI024 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 14:54:04', 24, 'Order'),
(108, 'Order Status Updated', 'Order GI023 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 15:11:02', 23, 'Order'),
(109, 'Order Status Updated', 'Order GI023 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 15:11:18', 23, 'Order'),
(110, 'Order Status Updated', 'Order GI022 status changed from Pending Payment to Paid', 'Admin', 2, 'Admin Test', '2026-01-21 15:14:30', 22, 'Order'),
(111, 'Order Status Updated', 'Order GI022 status changed from Paid to Payment Verified', 'Admin', 2, 'Admin Test', '2026-01-21 15:14:36', 22, 'Order'),
(112, 'Order Status Updated', 'Order GI022 status changed from Payment Verified to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 15:14:41', 22, 'Order'),
(113, 'Order Status Updated', 'Order GI023 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 15:15:07', 23, 'Order'),
(114, 'Order Status Updated', 'Order GI023 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 15:15:28', 23, 'Order'),
(115, 'Order Status Updated', 'Order GI023 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 15:17:43', 23, 'Order'),
(116, 'Order Status Updated', 'Order GI023 status changed from Ocular Pending to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-21 15:18:11', 23, 'Order'),
(117, 'Order Created', 'New order created: GI025 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-23 01:24:45', 25, 'Order'),
(118, 'Order Status Updated', 'Order GI025 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-23 01:25:12', 25, 'Order'),
(119, 'Order Created', 'New order created: GI026 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-23 02:29:59', 26, 'Order'),
(120, 'Order Status Updated', 'Order GI026 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-23 02:52:39', 26, 'Order'),
(121, 'Order Created', 'New order created: GI027 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-23 04:05:29', 27, 'Order'),
(122, 'Staff Assigned', 'fabrication staff assigned to order GI027: Admin Super', 'Admin', 2, 'Admin Test', '2026-01-23 05:12:39', 27, 'Order'),
(123, 'Staff Assigned', 'ocular staff assigned to order GI024: Sales Test', 'Admin', 2, 'Admin Test', '2026-01-23 05:53:53', 24, 'Order'),
(124, 'Order Status Updated', 'Order GI024 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-23 05:54:00', 24, 'Order'),
(125, 'Order Status Updated', 'Order GI027 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-23 05:54:39', 27, 'Order'),
(126, 'Order Status Updated', 'Order GI022 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-23 05:59:51', 22, 'Order'),
(127, 'Order Status Updated', 'Order GI021 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-23 06:00:37', 21, 'Order'),
(128, 'Order Status Updated', 'Order GI020 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-23 06:16:53', 20, 'Order'),
(129, 'Order Status Updated', 'Order GI019 status changed from  to In Fabrication', 'Admin', 2, 'Admin Test', '2026-01-23 06:24:13', 19, 'Order'),
(130, 'Order Status Updated', 'Order GI008 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-23 06:28:13', 8, 'Order'),
(131, 'Order Created', 'New order created: GI028 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-23 07:10:48', 28, 'Order'),
(132, 'Order Created', 'New order created: GI029 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-23 07:11:11', 29, 'Order'),
(133, 'Order Status Updated', 'Order GI024 status changed from Ocular Pending to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-23 11:13:06', 24, 'Order'),
(134, 'Order Created', 'New order created: GI030 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-23 12:53:52', 30, 'Order'),
(135, 'Order Created', 'New order created: GI031 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-23 12:54:45', 31, 'Order'),
(136, 'Order Created', 'New order created: GI032 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-23 13:10:14', 32, 'Order'),
(137, 'Order Created', 'New order created: GI033 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-23 13:29:11', 33, 'Order'),
(138, 'Order Created', 'New order created: GI034 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-23 14:05:52', 34, 'Order'),
(139, 'Order Created', 'New order created: GI035 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-23 14:09:20', 35, 'Order'),
(140, 'Order Created', 'New order created: GI036 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-23 14:11:59', 36, 'Order'),
(141, 'Order Created', 'New order created: GI037 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-23 14:15:45', 37, 'Order'),
(142, 'Order Created', 'New order created: GI038 (Customer ID: 7)', 'Customer', NULL, NULL, '2026-01-23 14:19:12', 38, 'Order'),
(143, 'Staff Assigned', 'ocular staff assigned to order GI037: Admin Super', 'Admin', 2, 'Admin Test', '2026-01-24 03:32:38', 37, 'Order'),
(144, 'Order Status Updated', 'Order GI037 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-24 03:33:03', 37, 'Order'),
(145, 'Order Status Updated', 'Order GI038 status changed from  to In Fabrication', 'Admin', 2, 'Admin Test', '2026-01-24 03:46:55', 38, 'Order'),
(146, 'Staff Assigned', 'ocular staff assigned to order GI035: Admin Test', 'Admin', 2, 'Admin Test', '2026-01-24 03:47:11', 35, 'Order'),
(147, 'Staff Assigned', 'ocular staff assigned to order GI033: Admin Super', 'Admin', 2, 'Admin Test', '2026-01-24 03:53:45', 33, 'Order'),
(148, 'Staff Assigned', 'ocular staff assigned to order GI035: Sales Test', 'Admin', 2, 'Admin Test', '2026-01-24 04:10:54', 35, 'Order'),
(149, 'Order Created', 'New order created: GI039 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-24 05:37:32', 39, 'Order'),
(150, 'Order Created', 'New order created: GI040 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-24 05:38:08', 40, 'Order'),
(151, 'Order Status Updated', 'Order GI040 status changed from  to In Fabrication', 'Admin', 2, 'Admin Test', '2026-01-24 05:39:55', 40, 'Order'),
(152, 'Order Created', 'New order created: GI041 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-24 05:51:19', 41, 'Order'),
(153, 'Order Status Updated', 'Order GI041 status changed from  to In Fabrication', 'Admin', 2, 'Admin Test', '2026-01-24 06:00:19', 41, 'Order'),
(154, 'Order Created', 'New order created: GI042 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-24 06:04:43', 42, 'Order'),
(155, 'Staff Assigned', 'ocular staff assigned to order GI042: Admin Super', 'Admin', 2, 'Admin Test', '2026-01-24 06:05:11', 42, 'Order'),
(156, 'Order Status Updated', 'Order GI042 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-24 06:05:16', 42, 'Order'),
(157, 'Order Created', 'New order created: GI043 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-24 10:30:12', 43, 'Order'),
(158, 'Order Created', 'New order created: GI044 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-24 10:33:55', 44, 'Order'),
(159, 'Order Created', 'New order created: GI045 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-24 10:39:17', 45, 'Order'),
(160, 'Order Created', 'New order created: GI046 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-24 10:44:01', 46, 'Order'),
(161, 'Order Created', 'New order created: GI047 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-24 11:09:20', 47, 'Order'),
(162, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI042 to 2026-01-26', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-24 12:13:56', NULL, NULL),
(163, 'Order Created', 'New order created: GI048 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 19:38:31', 48, 'Order'),
(164, 'Order Created', 'New order created: GI049 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 19:42:39', 49, 'Order'),
(165, 'Order Created', 'New order created: GI050 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 19:59:39', 50, 'Order'),
(166, 'Order Created', 'New order created: GI051 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 19:59:49', 51, 'Order'),
(167, 'Order Created', 'New order created: GI052 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 20:02:46', 52, 'Order'),
(168, 'Order Created', 'New order created: GI053 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 20:05:14', 53, 'Order'),
(169, 'Order Created', 'New order created: GI054 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 20:06:38', 54, 'Order'),
(170, 'Order Created', 'New order created: GI055 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 20:41:59', 55, 'Order'),
(171, 'Order Created', 'New order created: GI056 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 21:03:36', 56, 'Order'),
(172, 'Order Created', 'New order created: GI057 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 21:20:07', 57, 'Order'),
(173, 'Order Created', 'New order created: GI058 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 22:07:09', 58, 'Order'),
(174, 'Order Created', 'New order created: GI059 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 22:08:38', 59, 'Order'),
(175, 'Order Created', 'New order created: GI060 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 22:27:14', 60, 'Order'),
(176, 'Order Created', 'New order created: GI061 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 22:35:56', 61, 'Order'),
(177, 'Order Created', 'New order created: GI062 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 22:37:12', 62, 'Order'),
(178, 'Order Created', 'New order created: GI063 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 22:42:24', 63, 'Order'),
(179, 'Order Created', 'New order created: GI064 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 22:55:05', 64, 'Order'),
(180, 'Order Created', 'New order created: GI065 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 22:56:00', 65, 'Order'),
(181, 'Order Created', 'New order created: GI066 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 23:17:59', 66, 'Order'),
(182, 'Order Created', 'New order created: GI067 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-25 23:19:17', 67, 'Order'),
(183, 'Order Created', 'New order created: GI068 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 02:48:11', 68, 'Order'),
(184, 'Order Created', 'New order created: GI069 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 03:02:19', 69, 'Order'),
(185, 'Order Created', 'New order created: GI070 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 03:03:52', 70, 'Order'),
(186, 'Order Created', 'New order created: GI071 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 03:14:58', 71, 'Order'),
(187, 'Order Created', 'New order created: GI072 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 03:19:42', 72, 'Order'),
(188, 'Order Created', 'New order created: GI073 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 03:23:51', 73, 'Order'),
(189, 'Order Created', 'New order created: GI074 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 03:29:32', 74, 'Order'),
(190, 'Order Created', 'New order created: GI075 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 03:32:43', 75, 'Order'),
(191, 'Order Status Updated', 'Order GI075 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-26 04:09:07', 75, 'Order'),
(192, 'Order Status Updated', 'Order GI075 status changed from Ocular Pending to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-26 04:09:11', 75, 'Order'),
(193, 'Order Status Updated', 'Order GI075 status changed from Ocular Pending to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-26 04:09:11', 75, 'Order'),
(194, 'Order Status Updated', 'Order GI075 status changed from Ocular Pending to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-01-26 04:09:11', 75, 'Order'),
(195, 'Order Created', 'New order created: GI076 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 04:21:31', 76, 'Order'),
(196, 'Order Created', 'New order created: GI077 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 04:31:44', 77, 'Order'),
(197, 'Order Created', 'New order created: GI078 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 04:31:48', 78, 'Order'),
(198, 'Order Created', 'New order created: GI079 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 04:43:59', 79, 'Order'),
(199, 'Order Created', 'New order created: GI080 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 04:55:46', 80, 'Order'),
(200, 'Order Created', 'New order created: GI081 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 05:01:34', 81, 'Order'),
(201, 'Order Created', 'New order created: GI082 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 05:11:33', 82, 'Order'),
(202, 'Order Created', 'New order created: GI083 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 05:17:42', 83, 'Order'),
(203, 'Order Created', 'New order created: GI084 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 05:26:52', 84, 'Order'),
(204, 'Order Created', 'New order created: GI085 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 06:10:22', 85, 'Order'),
(205, 'Order Created', 'New order created: GI086 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 06:20:17', 86, 'Order'),
(206, 'Order Created', 'New order created: GI087 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 06:30:11', 87, 'Order'),
(207, 'Order Created', 'New order created: GI088 (Customer ID: 10)', 'Customer', NULL, NULL, '2026-01-26 06:30:27', 88, 'Order'),
(208, 'Order Created', 'New order created: GI089 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-26 09:54:15', 89, 'Order'),
(209, 'Order Created', 'New order created: GI090 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-26 10:45:48', 90, 'Order'),
(210, 'Order Status Updated', 'Order GI090 status changed from  to In Fabrication', 'Admin', 2, 'Admin Test', '2026-01-26 10:46:50', 90, 'Order'),
(211, 'Order Status Updated', 'Order GI090 status changed from In Fabrication to In Fabrication', 'Admin', 2, 'Admin Test', '2026-01-26 10:46:54', 90, 'Order'),
(212, 'Order Status Updated', 'Order GI090 status changed from In Fabrication to In Fabrication', 'Admin', 2, 'Admin Test', '2026-01-26 10:46:54', 90, 'Order'),
(213, 'Order Status Updated', 'Order GI090 status changed from In Fabrication to In Fabrication', 'Admin', 2, 'Admin Test', '2026-01-26 10:46:54', 90, 'Order'),
(214, 'Order Status Updated', 'Order GI090 status changed from In Fabrication to In Fabrication', 'Admin', 2, 'Admin Test', '2026-01-26 10:46:54', 90, 'Order'),
(215, 'Order Created', 'New order created: GI091 (Customer ID: 8)', 'Customer', NULL, NULL, '2026-01-26 10:54:30', 91, 'Order'),
(216, 'Order Status Updated', 'Order GI091 status changed from  to In Fabrication', 'Admin', 2, 'Admin Test', '2026-01-26 10:54:59', 91, 'Order'),
(217, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI091 to 2026-01-28', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 11:00:38', NULL, NULL),
(218, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI091 to 2026-01-31', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 11:04:13', NULL, NULL),
(219, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI091 to 2026-01-30', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 11:14:43', NULL, NULL),
(220, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI091 to 2026-01-30', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 11:45:22', NULL, NULL),
(221, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI090 to 2026-01-29', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 11:45:56', NULL, NULL),
(222, 'Installation Date Change Approved', 'Admin Admin Test approved installation date change for order #GI090 to 2026-01-29', 'Admin', NULL, 'Admin Test', '2026-01-26 12:15:06', NULL, NULL),
(223, 'Installation Date Change Approved', 'Admin Admin Test approved installation date change for order #GI090 to 2026-01-29', 'Admin', NULL, 'Admin Test', '2026-01-26 12:15:12', NULL, NULL),
(224, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI090 to 2026-02-01', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 12:16:00', NULL, NULL),
(225, 'Installation Date Change Approved', 'Admin Admin Test approved installation date change for order #GI090 to 2026-02-01', 'Admin', NULL, 'Admin Test', '2026-01-26 12:16:14', NULL, NULL);

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
(4, 'Admin', 'Super', '', 'testing.admin@gmail.com', '$2y$10$ahTPP9RAI9s/hfSnNuRKyuT6Ik2WQjRI.u1sj0/PWUkWbVuj4VIJe', NULL, NULL, '09937568011', NULL, 'Admin', 'Active', '2025-12-29 13:06:35', '2025-12-29 13:09:19', NULL),
(5, 'Ag', 'Pauig', '', 'cheezygrizzoverload@gmail.com', '$2y$10$H6kuL/RdhXcTVOjNagG/gefQ8tWIPG02MgngC7xSUl1LiJcYGomEO', NULL, NULL, '09887779123', NULL, 'Customer', 'Active', '2026-01-17 19:37:17', '2026-01-17 19:38:04', NULL),
(9, 'Angela', 'Pauig', '', 'agchii127@gmail.com', '$2y$10$Q6XA7MHEPkAZsn3erKLDpOiU0AIAtdfNZ3R1Wm2bgLYlzYQq7Kzk2', NULL, NULL, '09614788448', NULL, 'Customer', 'Active', '2026-01-21 08:14:35', '2026-01-21 01:15:38', NULL),
(10, 'Rommel John Jeric', 'Lerum', 'R.', 'lerumgops@gmail.com', '$2y$10$9SA.5/.c6HsmTeRPh26Bnu8kLvpuuMA45DsUKEPjo4dN8ONlzLS4.', NULL, NULL, '09120844695', NULL, 'Customer', 'Active', '2026-01-23 01:25:08', '2026-01-23 01:26:11', NULL),
(11, 'Dani', 'Hein', '', 'agchii128@gmail.com', '$2y$10$nbcy4nZjQ0Gzh9sJd7gC.e6f3QCFIgOvcOEy5I.Ypv2B8Vu3Hi/sC', NULL, NULL, '09111111111', NULL, 'Customer', 'Active', '2026-01-23 10:02:28', '2026-01-23 10:04:20', NULL),
(12, 'Jinwoo', 'Sun', '', 'angelapauig05@gmail.com', '$2y$10$WCrETrab8UD2Yvr1RdnxM.Izvf/cUpXu1BPLJSiYcZRWT/0XHg2/G', NULL, NULL, '09111111111', NULL, 'Customer', 'Active', '2026-01-25 18:33:28', '2026-01-25 18:34:01', NULL);

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
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `UnitHouseNumber` varchar(100) DEFAULT NULL,
  `Street` varchar(255) DEFAULT NULL,
  `Subdivision` varchar(255) DEFAULT NULL,
  `Barangay` varchar(100) DEFAULT NULL,
  `Region` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`AddressID`, `UserID`, `AddressType`, `AddressLine`, `City`, `Province`, `Country`, `ZipCode`, `Note`, `IsDefault`, `Created_Date`, `Updated_Date`, `UnitHouseNumber`, `Street`, `Subdivision`, `Barangay`, `Region`) VALUES
(1, 1, 'Shipping', '35 Malasimbo', 'Quezon City', 'Metro Manila', 'Philippines', '1102', NULL, 0, '2025-12-07 16:24:30', '2025-12-07 16:24:30', NULL, NULL, NULL, NULL, NULL),
(2, 5, 'Shipping', '4145', 'Caloocan', 'Metro Manila', 'Philippines', '1111', NULL, 1, '2026-01-18 02:09:26', '2026-01-18 02:09:26', '4145', '', '', 'Kaligayahan', 'NCR'),
(3, 9, 'Shipping', '2222', 'Quezon City', 'Metro Manila', 'Philippines', '4444', NULL, 1, '2026-01-21 08:32:09', '2026-01-21 08:32:09', '2222', '', '', 'West F', 'NCR'),
(4, 10, 'Shipping', '6, Sesame St., San Antonio Subd.', 'Quezon City', 'Metro Manila', 'Philippines', '1125', NULL, 1, '2026-01-23 01:27:57', '2026-01-23 01:27:57', '6', 'Sesame St.', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'NCR'),
(5, 12, 'Shipping', '5111, Casa Valencia, San Benissa Garden Villas', 'Quezon City', 'Metro Manila', 'Philippines', '1124', NULL, 0, '2026-01-25 18:37:16', '2026-01-25 21:25:01', '5111', 'Casa Valencia', 'San Benissa Garden Villas', 'Kaligayahan', 'NCR'),
(6, 10, 'Billing', '1111, Chestnut, Piña-Santol', 'Manila', 'Metro Manila', 'Philippines', '1111', NULL, 0, '2026-01-25 19:41:59', '2026-01-26 04:26:52', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'NCR'),
(7, 12, 'Shipping', '1111, Chestnut, Piña-Santol', 'Manila', 'Metro Manila', 'Philippines', '1111', NULL, 1, '2026-01-25 21:25:01', '2026-01-25 21:25:01', '1111', 'Chestnut', 'Piña-Santol', 'Sampaloc', 'NCR'),
(8, 8, 'Billing', '6, Sesame St., San Antonio Subd.', 'Quezon City', 'Metro Manila', 'Philippines', '1125', NULL, 0, '2026-01-26 08:54:15', '2026-01-26 08:54:15', '6', 'Sesame St.', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'NCR');

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
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`Wishlist_ID`, `Customer_ID`, `Product_ID`, `CustomizationID`, `DateAdded`) VALUES
(3, 3, 31, NULL, '2026-01-19 03:15:48'),
(4, 3, 32, NULL, '2026-01-19 06:51:14'),
(5, 3, 33, NULL, '2026-01-20 03:28:21'),
(6, 7, 44, NULL, '2026-01-23 19:15:06');

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
  ADD KEY `idx_staff` (`AssignedStaff_ID`),
  ADD KEY `idx_appointment_type` (`AppointmentType`),
  ADD KEY `idx_assigned_staff_id` (`AssignedStaff_ID`),
  ADD KEY `idx_quotation` (`QuotationID`);

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
-- Indexes for table `customer_customizations`
--
ALTER TABLE `customer_customizations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_product` (`customer_id`,`product_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `customer_notifications`
--
ALTER TABLE `customer_notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `idx_customer` (`Customer_ID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_type` (`Type`),
  ADD KEY `idx_created_date` (`Created_Date`),
  ADD KEY `idx_related` (`RelatedID`,`RelatedType`),
  ADD KEY `fk_customer_notifications_creator` (`CreatedBy`);

--
-- Indexes for table `customization`
--
ALTER TABLE `customization`
  ADD PRIMARY KEY (`CustomizationID`),
  ADD KEY `idx_customer_id` (`Customer_ID`),
  ADD KEY `idx_product_id` (`Product_ID`),
  ADD KEY `idx_created_at` (`CreatedAt`);

--
-- Indexes for table `customization_field_configs`
--
ALTER TABLE `customization_field_configs`
  ADD PRIMARY KEY (`ConfigID`),
  ADD UNIQUE KEY `unique_field_key` (`FieldKey`),
  ADD KEY `idx_category_subcategory` (`Category`,`Subcategory`);

--
-- Indexes for table `employee_archive`
--
ALTER TABLE `employee_archive`
  ADD PRIMARY KEY (`ArchiveID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `Email` (`Email`),
  ADD KEY `ArchivedAt` (`ArchivedAt`);

--
-- Indexes for table `enduser_archive`
--
ALTER TABLE `enduser_archive`
  ADD PRIMARY KEY (`ArchiveID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `Email` (`Email`),
  ADD KEY `ArchivedAt` (`ArchivedAt`);

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
  ADD KEY `fk_order_disapproved_by` (`DisapprovedBy_ID`),
  ADD KEY `idx_order_type` (`OrderType`),
  ADD KEY `idx_ocular_completed` (`OcularCompleted`),
  ADD KEY `idx_fabrication_staff` (`FabricationStaff_ID`),
  ADD KEY `idx_installation_staff` (`InstallationStaff_ID`),
  ADD KEY `idx_fabrication_status` (`FabricationStatus`),
  ADD KEY `idx_fabrication_dates` (`FabricationStartDate`,`FabricationEndDate`),
  ADD KEY `fk_order_ocular_completed_by` (`OcularCompletedBy_ID`);

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
-- Indexes for table `product_series`
--
ALTER TABLE `product_series`
  ADD PRIMARY KEY (`Series_ID`),
  ADD KEY `idx_product_id` (`Product_ID`);

--
-- Indexes for table `product_standard_sizes`
--
ALTER TABLE `product_standard_sizes`
  ADD PRIMARY KEY (`SizeID`),
  ADD KEY `idx_series_id` (`Series_ID`);

--
-- Indexes for table `product_tag_prices`
--
ALTER TABLE `product_tag_prices`
  ADD PRIMARY KEY (`TagPriceID`),
  ADD KEY `idx_product_id` (`Product_ID`),
  ADD KEY `idx_field_id` (`FieldID`),
  ADD KEY `idx_image_url` (`ImageUrl`);

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
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `Cart_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `Customer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customer_customizations`
--
ALTER TABLE `customer_customizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `customer_notifications`
--
ALTER TABLE `customer_notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `customization`
--
ALTER TABLE `customization`
  MODIFY `CustomizationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=249;

--
-- AUTO_INCREMENT for table `customization_field_configs`
--
ALTER TABLE `customization_field_configs`
  MODIFY `ConfigID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `employee_archive`
--
ALTER TABLE `employee_archive`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `enduser_archive`
--
ALTER TABLE `enduser_archive`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `Issue_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `ProductMaterialID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_series`
--
ALTER TABLE `product_series`
  MODIFY `Series_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_standard_sizes`
--
ALTER TABLE `product_standard_sizes`
  MODIFY `SizeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `product_tag_prices`
--
ALTER TABLE `product_tag_prices`
  MODIFY `TagPriceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=299;

--
-- AUTO_INCREMENT for table `projectschedule`
--
ALTER TABLE `projectschedule`
  MODIFY `Schedule_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quotation`
--
ALTER TABLE `quotation`
  MODIFY `QuotationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ready_to_approve_orders`
--
ALTER TABLE `ready_to_approve_orders`
  MODIFY `ReadyOrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales_notif`
--
ALTER TABLE `sales_notif`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_activity_log`
--
ALTER TABLE `system_activity_log`
  MODIFY `ActivityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=226;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `AddressID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  ADD CONSTRAINT `fk_activities_inventory` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_activities_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_assigned_staff` FOREIGN KEY (`AssignedStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appointments_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointments_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointments_staff` FOREIGN KEY (`AssignedStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
