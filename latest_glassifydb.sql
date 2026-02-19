-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 19, 2026 at 03:37 PM
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
  `Status` enum('In Progress','Installed','Complete','Payment Overdue','Cancelled','Returned') DEFAULT 'In Progress' COMMENT 'Appointment status - Installed = work done but payment pending, Complete = fully done with payment',
  `Notes` text DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `AppointmentType` enum('Ocular','Installation') DEFAULT NULL COMMENT 'Type of appointment: Ocular or Installation',
  `OcularNotes` text DEFAULT NULL COMMENT 'Site assessment notes and measurements',
  `OcularReportPath` varchar(255) DEFAULT NULL COMMENT 'Path to full ocular report PDF',
  `InstallationNotes` text DEFAULT NULL COMMENT 'Installation-specific notes',
  `FabricationNotes` text DEFAULT NULL,
  `InstallationChecklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Installation checklist items (JSON format)' CHECK (json_valid(`InstallationChecklist`)),
  `SitePhotos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of site photo paths (JSON format)' CHECK (json_valid(`SitePhotos`)),
  `InternalNotes` text DEFAULT NULL COMMENT 'Internal admin notes',
  `CustomerVisibleNotes` text DEFAULT NULL COMMENT 'Notes visible to customer',
  `FabricationPaymentAmount` decimal(10,2) DEFAULT NULL,
  `FabricationPaymentMethod` varchar(50) DEFAULT NULL,
  `FabricationPaymentStatus` enum('Pending','Paid') DEFAULT 'Pending',
  `FabricationReceiptPath` varchar(255) DEFAULT NULL,
  `InstallationCompletedDate` datetime DEFAULT NULL COMMENT 'Date/time when physical installation was completed',
  `PaymentDueDate` datetime DEFAULT NULL COMMENT 'Deadline for 10% payment (InstallationCompletedDate + 5 days)',
  `PaymentGracePeriodDays` int(11) DEFAULT 5 COMMENT 'Number of days customer has to pay after installation (default 5)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `OrderID`, `QuotationID`, `Customer_ID`, `ProductName`, `ClientName`, `Service`, `AppointmentDate`, `AppointmentTime`, `AssignedStaff`, `AssignedStaff_ID`, `Status`, `Notes`, `Created_Date`, `Updated_Date`, `AppointmentType`, `OcularNotes`, `OcularReportPath`, `InstallationNotes`, `FabricationNotes`, `InstallationChecklist`, `SitePhotos`, `InternalNotes`, `CustomerVisibleNotes`, `FabricationPaymentAmount`, `FabricationPaymentMethod`, `FabricationPaymentStatus`, `FabricationReceiptPath`, `InstallationCompletedDate`, `PaymentDueDate`, `PaymentGracePeriodDays`) VALUES
(1, 5, NULL, 15, NULL, 'Leonidas Opus Santos', 'Ocular Visit', '2026-02-13', '10:00:00', 'Sales Test', 3, 'Complete', NULL, '2026-02-09 06:01:54', '2026-02-08 23:58:18', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(2, 7, NULL, 15, NULL, 'Leonidas Opus Santos', 'Ocular Visit', '2026-02-17', '10:00:00', NULL, NULL, 'Complete', NULL, '2026-02-09 08:23:41', '2026-02-09 07:47:56', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(3, 8, NULL, 15, NULL, 'Leonidas Opus Santos', 'Ocular Visit', '2026-02-18', '10:00:00', 'Admin Super', 4, 'Complete', NULL, '2026-02-09 17:33:05', '2026-02-09 10:35:51', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(4, 8, NULL, 15, NULL, 'Leonidas Opus Santos', 'In Fabrication', '2026-02-09', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-09 17:35:51', '2026-02-09 17:44:38', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '', 'Pending', NULL, NULL, NULL, 5),
(5, 9, NULL, 15, NULL, 'Leonidas Opus Santos', 'Ocular Visit', '2026-02-24', '10:00:00', 'Admin Test', 2, 'Complete', NULL, '2026-02-09 18:39:47', '2026-02-09 11:40:50', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(6, 9, NULL, 15, NULL, 'Leonidas Opus Santos', 'In Fabrication', '2026-02-09', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-09 18:40:50', '2026-02-09 21:47:17', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2.00', 'Gcash', 'Paid', NULL, NULL, NULL, 5),
(7, 9, NULL, 15, 'Top Glass', 'Leonidas Opus Santos', 'Installed', '2026-02-13', '22:47:00', 'Admin Test', 2, 'Complete', NULL, '2026-02-09 21:47:47', '2026-02-09 17:19:00', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-02-10 00:26:32', NULL, 5),
(8, 10, NULL, 15, NULL, 'Leonidas Opus Santos', 'Ocular Visit', '2026-02-15', '10:00:00', 'Admin Super', 4, 'Complete', NULL, '2026-02-10 01:36:40', '2026-02-09 18:41:36', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(9, 10, NULL, 15, NULL, 'Leonidas Opus Santos', 'In Fabrication', '2026-02-10', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-10 01:41:36', '2026-02-10 01:42:45', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '14.00', 'Credit/Debit Card', 'Paid', NULL, NULL, NULL, 5),
(10, 10, NULL, 15, '868/130 Series Sliding Door', 'Leonidas Opus Santos', 'Installed', '2026-02-12', '02:43:00', NULL, NULL, 'Complete', NULL, '2026-02-10 01:43:07', '2026-02-09 18:45:28', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-02-10 02:44:14', NULL, 5),
(11, 11, NULL, 17, NULL, 'Arogela Robles Lerum', 'Ocular Visit', '2026-02-14', '10:00:00', 'Admin Super', 4, 'Complete', NULL, '2026-02-10 01:57:33', '2026-02-09 19:02:33', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(12, 11, NULL, 17, NULL, 'Arogela Robles Lerum', 'In Fabrication', '2026-02-10', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-10 02:02:34', '2026-02-10 02:04:15', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '14.00', 'Credit/Debit Card', 'Paid', NULL, NULL, NULL, 5),
(13, 11, NULL, 17, '868/130 Series Sliding Door', 'Arogela Robles Lerum', 'Installed', '2026-02-12', '03:04:00', NULL, NULL, 'Complete', NULL, '2026-02-10 02:04:38', '2026-02-09 19:06:14', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-02-10 03:05:23', NULL, 5),
(14, 6, NULL, 15, NULL, 'Leonidas Opus Santos', 'Ocular Visit', '2026-02-13', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-11 09:44:20', '2026-02-11 09:44:26', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(15, 12, NULL, 18, NULL, 'Kelly Jadaone Delos Santos', 'Ocular Visit', '2026-02-16', '10:00:00', 'Admin Super', 4, 'Complete', NULL, '2026-02-11 14:48:40', '2026-02-11 07:51:37', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(16, 12, NULL, 18, NULL, 'Kelly Jadaone Delos Santos', 'In Fabrication', '2026-02-11', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-11 14:51:37', '2026-02-11 14:53:40', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1.00', 'GCash', 'Paid', NULL, NULL, NULL, 5),
(17, 12, NULL, 18, '60 Series Awning Window', 'Kelly Jadaone Delos Santos', 'Installed', '2026-02-13', '15:54:00', 'Admin Test', 2, 'Complete', NULL, '2026-02-11 14:54:12', '2026-02-11 08:03:42', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(18, 13, NULL, 18, NULL, 'Kelly Jadaone Delos Santos', 'Ocular Visit', '2026-02-16', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-11 21:26:12', '2026-02-11 21:26:17', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(19, 14, NULL, 1, NULL, 'Aaron Gabriel M. Manantan', 'Ocular Visit', '2026-02-19', '10:00:00', NULL, NULL, 'Complete', NULL, '2026-02-12 03:52:43', '2026-02-11 20:55:06', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(20, 14, NULL, 1, NULL, 'Aaron Gabriel M. Manantan', 'In Fabrication', '2026-02-12', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-12 03:55:06', '2026-02-12 04:00:33', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4.00', 'GCash', 'Paid', NULL, NULL, NULL, 5),
(21, 14, NULL, 1, '868/130 Series Sliding Door', 'Aaron Gabriel M. Manantan', 'Installed', '2026-02-14', '04:59:00', NULL, NULL, 'Installed', NULL, '2026-02-12 03:59:13', '2026-02-11 21:01:58', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-02-12 04:59:54', '2026-02-17 04:59:54', 5),
(22, 15, NULL, 1, NULL, 'Aaron Gabriel M. Manantan', 'Ocular Visit', '2026-02-16', '10:00:00', NULL, NULL, 'Complete', NULL, '2026-02-12 04:04:14', '2026-02-11 21:08:18', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(23, 15, NULL, 1, NULL, 'Aaron Gabriel M. Manantan', 'In Fabrication', '2026-02-12', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-12 04:08:18', '2026-02-12 04:12:58', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4.00', 'GCash', 'Paid', NULL, NULL, NULL, 5),
(24, 15, NULL, 1, 'Top Glass', 'Aaron Gabriel M. Manantan', 'Installed', '2026-02-14', '05:14:08', NULL, NULL, 'In Progress', NULL, '2026-02-12 04:14:08', '2026-02-12 04:14:08', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(25, 16, NULL, 19, NULL, 'Aro Gab Manantan', 'Ocular Visit', '2026-02-16', '10:00:00', NULL, NULL, 'Complete', NULL, '2026-02-12 04:20:37', '2026-02-11 21:21:42', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(26, 16, NULL, 19, 'Top Glass', 'Aro Gab Manantan', 'Installed', '2026-02-14', '05:27:00', 'Admin Test', 2, 'Installed', NULL, '2026-02-12 04:27:27', '2026-02-11 21:28:36', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-02-12 05:28:36', '2026-02-17 05:28:36', 5),
(27, 17, NULL, 19, NULL, 'Aro Gab Manantan', 'Ocular Visit', '2026-02-17', '10:00:00', NULL, NULL, 'Complete', NULL, '2026-02-12 05:06:09', '2026-02-11 22:15:12', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(28, 17, NULL, 19, NULL, 'Aro Gab Manantan', 'In Fabrication', '2026-02-12', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-12 05:15:12', '2026-02-12 05:16:49', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '80.00', 'GCash', 'Paid', NULL, NULL, NULL, 5),
(29, 17, NULL, 19, '868/130 Series Sliding Door', 'Aro Gab Manantan', 'Installed', '2026-02-14', '06:17:00', NULL, NULL, 'Complete', NULL, '2026-02-12 05:17:09', '2026-02-11 22:20:18', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(30, 18, NULL, 19, NULL, 'Aro Gab Manantan', 'Ocular Visit', '2026-02-16', '10:00:00', NULL, NULL, 'Complete', NULL, '2026-02-12 05:21:53', '2026-02-11 22:25:12', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(31, 18, NULL, 19, NULL, 'Aro Gab Manantan', 'In Fabrication', '2026-02-12', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-12 05:25:13', '2026-02-12 05:26:10', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '14.00', 'GCash', 'Paid', NULL, NULL, NULL, 5),
(32, 18, NULL, 19, '868/130 Series Sliding Door', 'Aro Gab Manantan', 'Installed', '2026-02-14', '06:26:00', 'Admin Test', 2, 'Installed', NULL, '2026-02-12 05:26:58', '2026-02-11 22:30:24', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-02-12 06:27:43', '2026-02-17 06:27:43', 5),
(33, 19, NULL, 19, NULL, 'Aro Gab Manantan', 'Ocular Visit', '2026-02-16', '10:00:00', NULL, NULL, 'Complete', NULL, '2026-02-12 05:33:21', '2026-02-11 22:34:07', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(34, 19, NULL, 19, NULL, 'Aro Gab Manantan', 'In Fabrication', '2026-02-12', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-12 05:34:08', '2026-02-12 05:36:03', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '14.00', 'GCash', 'Paid', NULL, NULL, NULL, 5),
(35, 19, NULL, 19, '868/130 Series Sliding Door', 'Aro Gab Manantan', 'Installed', '2026-02-14', '06:36:00', 'Admin Test', 2, 'Complete', NULL, '2026-02-12 05:36:29', '2026-02-11 22:37:38', 'Installation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-02-12 06:37:21', NULL, 5),
(36, 20, NULL, 19, NULL, 'Aro Gab Manantan', 'Ocular Visit', '2026-02-16', '10:00:00', 'Admin Test', 2, 'In Progress', NULL, '2026-02-12 06:31:35', '2026-02-11 23:32:04', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5),
(37, 21, NULL, 19, NULL, 'Aro Gab Manantan', 'Ocular Visit', '2026-02-16', '10:00:00', NULL, NULL, 'In Progress', NULL, '2026-02-12 07:07:29', '2026-02-12 07:18:39', 'Ocular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, 5);

--
-- Triggers `appointments`
--
DELIMITER $$
CREATE TRIGGER `set_installation_payment_due_date` BEFORE UPDATE ON `appointments` FOR EACH ROW BEGIN
    IF NEW.Status = 'Installed' AND OLD.Status != 'Installed' THEN
        IF NEW.InstallationCompletedDate IS NULL THEN
            SET NEW.InstallationCompletedDate = NOW();
        END IF;
        IF NEW.PaymentDueDate IS NULL AND NEW.InstallationCompletedDate IS NOT NULL THEN
            SET NEW.PaymentDueDate = DATE_ADD(NEW.InstallationCompletedDate, INTERVAL COALESCE(NEW.PaymentGracePeriodDays, 5) DAY);
        END IF;
    END IF;
    IF NEW.Status = 'Complete' AND OLD.Status != 'Complete' THEN
        SET NEW.PaymentDueDate = NULL;
    END IF;
END
$$
DELIMITER ;

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

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `Customer_ID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `setup_status` enum('pending','completed') DEFAULT 'pending',
  `experience_data` text DEFAULT NULL,
  `role` enum('beginner','professional') DEFAULT NULL COMMENT 'User role: beginner or professional',
  `Date_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`Customer_ID`, `UserID`, `Date_Created`, `setup_status`, `experience_data`, `role`, `Date_Updated`) VALUES
(1, 1, '2025-12-07 17:04:19', 'completed', '{\"role\":\"beginner\",\"experience\":\"once_twice\",\"specifications_knowledge\":\"a_little\",\"customization_handling\":\"prepare_for_me\"}', 'beginner', '2026-02-12 03:48:54'),
(2, 4, '2025-12-29 13:06:35', 'pending', NULL, NULL, '2026-02-04 10:11:32'),
(3, 5, '2026-01-17 19:37:17', 'pending', NULL, NULL, '2026-02-04 10:11:32'),
(7, 9, '2026-01-21 08:14:35', 'pending', NULL, NULL, '2026-02-04 10:11:32'),
(8, 10, '2026-01-23 01:25:08', 'pending', NULL, NULL, '2026-02-04 10:11:32'),
(9, 11, '2026-01-23 10:02:28', 'completed', '{\"role\":\"beginner\",\"experience\":\"first_time\",\"specifications_knowledge\":\"not_at_all\",\"customization_handling\":\"prepare_for_me\"}', 'beginner', '2026-02-04 11:13:38'),
(10, 12, '2026-01-25 18:33:28', 'completed', '{\"role\":\"professional\",\"professional_type\":\"engineer\",\"previous_experience\":\"yes_occasionally\",\"specification_preparation\":\"collaborate_after_assessment\",\"2d_tool_comfort\":\"prefer_minimal\"}', 'professional', '2026-02-07 04:04:11'),
(11, 13, '2026-02-03 04:46:55', 'pending', NULL, NULL, '2026-02-04 10:11:32'),
(12, 14, '2026-02-03 14:32:57', 'pending', '{\"role\":\"beginner\",\"experience\":null,\"confidence\":null,\"customization_preference\":null}', NULL, '2026-02-04 10:11:32'),
(13, 15, '2026-02-03 14:54:40', 'pending', NULL, NULL, '2026-02-04 05:21:31'),
(14, 16, '2026-02-04 10:02:38', 'completed', '{\"role\":\"professional\",\"previous_experience\":\"yes_regularly\",\"specification_preparation\":\"prepare_myself\",\"2d_tool_comfort\":\"very_comfortable\"}', 'professional', '2026-02-04 10:57:11'),
(15, 17, '2026-02-09 02:44:30', 'completed', '{\"role\":\"beginner\",\"experience\":\"once_twice\",\"specifications_knowledge\":\"a_little\",\"customization_handling\":\"review_and_approve\"}', 'beginner', '2026-02-09 02:46:59'),
(16, 18, '2026-02-10 01:51:36', 'pending', NULL, NULL, '2026-02-10 01:51:36'),
(17, 19, '2026-02-10 01:52:50', 'completed', '{\"role\":\"beginner\",\"experience\":null,\"specifications_knowledge\":null,\"customization_handling\":null}', 'beginner', '2026-02-11 14:00:01'),
(18, 20, '2026-02-11 14:33:44', 'completed', '{\"role\":\"beginner\",\"experience\":null,\"specifications_knowledge\":null,\"customization_handling\":null}', 'beginner', '2026-02-11 15:07:36'),
(19, 21, '2026-02-12 04:17:34', 'completed', '{\"role\":\"professional\",\"previous_experience\":\"yes_regularly\",\"specification_preparation\":\"collaborate_after_assessment\",\"2d_tool_comfort\":\"somewhat_comfortable\"}', 'professional', '2026-02-12 07:03:58'),
(20, 22, '2026-02-12 07:38:57', 'pending', NULL, NULL, '2026-02-12 07:38:57');

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
(29, 8, 5, '{\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"frameColor\":\"Powder Coated White\",\"operation\":\"Awning (crank-out)\",\"openingDirection\":\"Top-hinged\",\"thickness\":\"6mm\",\"screen\":\"With Screen\",\"shape\":\"Round\",\"frameType\":\"Framed\",\"lighting\":\"Integrated LED lighting\",\"ledColorTemperature\":\"Warm white\",\"control\":\"Touch sensor button\",\"mountingMethod\":\"Wall-mounted\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\"}', '2026-01-27 19:37:41', '2026-01-27 19:37:41'),
(30, 8, 6, '{\"glassType\":\"Frosted\",\"doorType\":\"Double swing\",\"doorSwing\":\"Left swing\",\"fixedPanels\":\"With fixed side\\/transom panels\",\"configuration\":\"With fixed side panel (left or right)\",\"handleType\":\"Various pull handle designs\",\"hardwareFinish\":\"Polished Chrome\\/Stainless Steel\",\"gridPattern\":\"Internal grids\",\"glassTreatment\":\"Frosted stripes (horizontal\\/vertical)\",\"installation\":\"Patch fittings (minimalist hardware)\",\"hardware\":\"Push\\/pull handles\"}', '2026-01-26 09:41:43', '2026-01-26 09:41:43'),
(31, 8, 7, '{\"shape\":\"Custom shapes\",\"edgeFinish\":\"Raw\",\"mountingMethod\":\"Stand\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\"}', '2026-01-26 09:54:07', '2026-01-26 09:54:07'),
(32, 8, 2, '{\"glassType\":\"Tempered\",\"glassColor\":\"Bronze\",\"frameColor\":\"Analok\",\"operation\":\"Awning (push-out)\",\"openingDirection\":\"Top-hinged\",\"thickness\":\"6mm\",\"screen\":\"With Screen\"}', '2026-01-26 10:45:37', '2026-01-26 10:45:37'),
(33, 8, 1, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-01-26 10:54:20', '2026-01-26 10:54:20'),
(34, 8, 22, '{\"numberOfPanels\":\"4 Panels\",\"transomType\":\"Fixed Transom Head (Fixed glass at top)\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-01-27 20:13:30', '2026-01-27 20:13:30'),
(35, 8, 19, '{\"shape\":\"Square\",\"edgeFinish\":\"Flat polished edge\",\"mountingMethod\":\"Wall-mounted\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\"}', '2026-01-27 20:16:04', '2026-01-27 20:16:04'),
(36, 8, 18, '{\"shape\":\"Round\",\"edgeFinish\":\"Beveled\",\"mountingMethod\":\"Wall-mounted\",\"thicknessmm\":\"6mm\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\",\"numberOfPanels\":\"2 Panels\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Hanalok\",\"glassType\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Durable Flushlok\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-02-03 01:03:03', '2026-02-03 01:03:03'),
(37, 8, 23, '{\"glassType\":\"Clear\",\"doorType\":\"Single swing\",\"doorSwing\":\"Left swing\",\"fixedPanels\":\"None\",\"handleType\":\"Various pull handle designs\",\"hardwareFinish\":\"Polished Chrome\\/Stainless Steel\",\"installation\":\"Patch fittings (minimalist hardware)\",\"hardware\":\"Push\\/pull handles\"}', '2026-02-03 01:32:52', '2026-02-03 01:32:52'),
(38, 10, 23, '{\"glassType\":\"Clear\",\"doorType\":\"Single swing\",\"doorSwing\":\"Left swing\",\"fixedPanels\":\"None\",\"handleType\":\"Various pull handle designs\",\"hardwareFinish\":\"Polished Chrome\\/Stainless Steel\",\"installation\":\"Patch fittings (minimalist hardware)\",\"hardware\":\"Push\\/pull handles\",\"softClose\":true}', '2026-02-08 06:23:37', '2026-02-08 06:23:37'),
(39, 10, 18, '{\"shape\":\"Round\",\"edgeFinish\":\"Beveled\",\"mountingMethod\":\"Wall-mounted\",\"thicknessmm\":\"6mm\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\"}', '2026-02-03 13:52:15', '2026-02-03 13:52:15'),
(40, 13, 23, '{\"glassType\":\"Clear\",\"doorType\":\"Single swing\",\"doorSwing\":\"Left swing\",\"fixedPanels\":\"None\",\"handleType\":\"Various pull handle designs\",\"hardwareFinish\":\"Polished Chrome\\/Stainless Steel\",\"installation\":\"Patch fittings (minimalist hardware)\",\"hardware\":\"Push\\/pull handles\"}', '2026-02-04 09:20:58', '2026-02-04 09:20:58'),
(41, 14, 23, '{\"glassType\":\"Clear\",\"doorType\":\"Double swing\",\"doorSwing\":\"Left swing\",\"fixedPanels\":\"None\",\"handleType\":\"Various pull handle designs\",\"hardwareFinish\":\"Polished Chrome\\/Stainless Steel\",\"installation\":\"Patch fittings (minimalist hardware)\",\"hardware\":\"Push\\/pull handles\"}', '2026-02-04 11:43:18', '2026-02-04 11:43:18'),
(42, 14, 21, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-04 11:49:49', '2026-02-04 11:49:49'),
(43, 9, 18, '{\"shape\":\"Round\",\"edgeFinish\":\"Beveled\",\"mountingMethod\":\"Wall-mounted\",\"thicknessmm\":\"6mm\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\"}', '2026-02-07 17:29:11', '2026-02-07 17:29:11'),
(44, 14, 19, '{\"shape\":\"Rectangle\",\"edgeFinish\":\"Flat polished edge\",\"mountingMethod\":\"Stand\"}', '2026-02-04 11:58:32', '2026-02-04 11:58:32'),
(45, 9, 20, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-07 08:33:58', '2026-02-07 08:33:58'),
(46, 9, 22, '{\"numberOfPanels\":\"4 Panels\",\"transomType\":\"Fixed Transom Sill (Fixed glass at bottom)\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\",\"frameColor\":\"Matte Gray\",\"glassType\":\"Reflective\",\"glassColor\":\"Bronze\",\"glassThickness\":\"10mm\",\"lockType\":\"Flushlok #12\",\"rollerType\":\"Blue Single Roller\"}', '2026-02-07 07:45:05', '2026-02-07 07:45:05'),
(47, 9, 21, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-05 13:19:14', '2026-02-05 13:19:14'),
(48, 9, 23, '{\"glassType\":\"Clear\",\"doorType\":\"Single swing\",\"doorSwing\":\"Left swing\",\"fixedPanels\":\"None\",\"handleType\":\"Various pull handle designs\",\"hardwareFinish\":\"Polished Chrome\\/Stainless Steel\",\"installation\":\"Patch fittings (minimalist hardware)\",\"hardware\":\"Push\\/pull handles\"}', '2026-02-07 05:21:20', '2026-02-07 05:21:20'),
(49, 10, 22, '{\"numberOfPanels\":\"4 Panels\",\"transomType\":\"Fixed Transom Sill (Fixed glass at bottom)\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\",\"frameColor\":\"Matte Black\",\"glassType\":\"Tempered\",\"glassColor\":\"Bronze\",\"glassThickness\":\"8mm\",\"lockType\":\"Flushlok #12\",\"rollerType\":\"Blue Single Roller\"}', '2026-02-08 04:01:50', '2026-02-08 04:01:50'),
(50, 10, 21, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-07 16:04:41', '2026-02-07 16:04:41'),
(51, 10, 20, '{\"numberOfPanels\":\"4 Panels\",\"transomType\":\"Fixed Transom Head (Fixed glass at top)\",\"trackSystem\":\"3 Tracks\",\"panelConfiguration\":\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\",\"frameColor\":\"Analok\",\"glassType\":\"Tempered\",\"glassColor\":\"Bronze\",\"glassThickness\":\"8mm\",\"lockType\":\"New Auto Flushlock\",\"rollerType\":\"Blue Double Roller\"}', '2026-02-08 03:51:24', '2026-02-08 03:51:24'),
(52, 10, 19, '{\"shape\":\"Rectangle\",\"edgeFinish\":\"Beveled\",\"mountingMethod\":\"Wall-mounted\"}', '2026-02-08 03:44:09', '2026-02-08 03:44:09'),
(53, 10, 9, '{\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"frameColor\":\"Powder Coated White\",\"operation\":\"Awning (crank-out)\",\"openingDirection\":\"Top-hinged\",\"thickness\":\"6mm\",\"screen\":\"With Screen\"}', '2026-02-08 09:22:24', '2026-02-08 09:22:24'),
(54, 10, 7, '{\"glassType\":\"Reflective\",\"glassColor\":\"Frosted\",\"frameColor\":\"Analok\",\"operation\":\"Awning (push-out)\",\"openingDirection\":\"Top-hinged\",\"thickness\":\"8mm\",\"screen\":\"Without Screen\"}', '2026-02-08 04:47:22', '2026-02-08 04:47:22'),
(55, 15, 19, '{\"shape\":\"Rectangle\",\"edgeFinish\":\"Beveled\",\"mountingMethod\":\"Wall-mounted\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\"}', '2026-02-09 04:58:12', '2026-02-09 04:58:12'),
(56, 15, 22, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-11 15:29:45', '2026-02-11 15:29:45'),
(57, 17, 22, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-11 14:12:33', '2026-02-11 14:12:33'),
(58, 17, 10, '{\"transomType\":\"Casement w\\/FTH\",\"panelConfiguration\":\"2\",\"frameColor\":\"Hanalok\",\"glassColor\":\"Clear\",\"glassType\":\"Ordinary\",\"thickness\":\"6mm\"}', '2026-02-11 13:24:27', '2026-02-11 13:24:27'),
(59, 17, 23, '{\"glassType\":\"Clear\",\"doorType\":\"Single swing\",\"doorSwing\":\"Left swing\",\"fixedPanels\":\"None\",\"handleType\":\"Various pull handle designs\",\"hardwareFinish\":\"Polished Chrome\\/Stainless Steel\",\"installation\":\"Patch fittings (minimalist hardware)\",\"hardware\":\"Push\\/pull handles\"}', '2026-02-11 13:28:39', '2026-02-11 13:28:39'),
(60, 18, 22, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-11 16:18:03', '2026-02-11 16:18:03'),
(61, 18, 7, '{\"glassType\":\"Tempered\",\"glassColor\":\"Clear\",\"frameColor\":\"Analok\",\"operation\":\"Awning (push-out)\",\"openingDirection\":\"Top-hinged\",\"thickness\":\"12mm\",\"screen\":\"Without Screen\",\"shape\":\"Custom shapes\",\"edgeFinish\":\"Raw\",\"mountingMethod\":\"Stand\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\"}', '2026-02-11 15:41:17', '2026-02-11 15:41:17'),
(62, 18, 1, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"screen\":\"With Screen\"}', '2026-02-11 22:25:31', '2026-02-11 22:25:32'),
(63, 1, 22, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Frosted\",\"glassColor\":\"Frosted\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-12 04:49:57', '2026-02-12 04:49:57'),
(64, 1, 18, '{\"shape\":\"Round\",\"edgeFinish\":\"Beveled\",\"mountingMethod\":\"Wall-mounted\",\"thicknessmm\":\"6mm\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\"}', '2026-02-12 05:03:38', '2026-02-12 05:03:38'),
(65, 19, 21, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Smoked\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-12 08:04:31', '2026-02-12 08:04:31'),
(66, 19, 22, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Reflective\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-12 08:04:15', '2026-02-12 08:04:16');

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
(29, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI091 fabrication is complete! Installation is scheduled for January 28, 2026 at 10:55 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-01-26 10:55:14', '2026-01-26 10:55:56', 91, 'Order', 2),
(30, 8, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI001 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-01-27 20:53:54', '2026-02-03 00:59:41', 1, 'Order', 2),
(31, 8, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI002 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-03 01:03:46', '2026-02-03 04:03:10', 2, 'Order', 2),
(32, 8, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI001 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-03 01:06:46', '2026-02-03 04:03:10', 1, 'Order', 2),
(33, 8, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI002 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-03 01:22:16', '2026-02-03 04:03:10', 2, 'Order', 2),
(34, 8, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI003 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-03 04:19:00', '2026-02-03 05:11:47', 3, 'Order', 2),
(35, 8, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI003 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-03 04:39:20', '2026-02-03 05:11:47', 3, 'Order', 2),
(36, 8, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI002 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱52,664.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-03 05:07:00', '2026-02-03 05:11:47', 2, 'Order', 2),
(37, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI003 fabrication is complete! Installation is scheduled for February 5, 2026 at 5:07 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-03 05:07:17', '2026-02-03 05:11:47', 3, 'Order', 2),
(38, 8, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI003 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱59,864.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-03 05:08:31', '2026-02-03 05:11:47', 3, 'Order', 2),
(39, 8, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI002 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱52,664.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-03 05:08:39', '2026-02-03 05:11:47', 2, 'Order', 2),
(40, 8, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI001 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱40,514.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-03 05:08:46', '2026-02-03 05:11:47', 1, 'Order', 2),
(41, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI003 fabrication is complete! Installation is scheduled for February 5, 2026 at 5:07 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-03 05:10:33', '2026-02-03 05:11:47', 3, 'Order', 2),
(42, 8, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI002 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱52,664.00 (40% of total) to proceed with installation scheduling.', 'Unread', '2026-02-03 05:13:14', NULL, 2, 'Order', 2),
(43, 8, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI002 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱52,664.00 (40% of total) to proceed with installation scheduling.', 'Unread', '2026-02-03 05:13:22', NULL, 2, 'Order', 2),
(44, 8, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI001 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱40,514.00 (40% of total) to proceed with installation scheduling.', 'Unread', '2026-02-03 05:39:06', NULL, 1, 'Order', 2),
(45, 8, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI001 fabrication is complete! Installation is scheduled for February 5, 2026 at 5:39 AM. You can request to change the date within the next 7 days if needed.', 'Unread', '2026-02-03 05:39:19', NULL, 1, 'Order', 2),
(46, 11, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI004 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-03 05:50:44', '2026-02-03 05:52:48', 4, 'Order', 2),
(47, 11, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI004 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-03 05:52:27', '2026-02-03 05:52:48', 4, 'Order', 2),
(48, 11, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI004 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱63,014.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-03 05:53:35', '2026-02-03 05:54:04', 4, 'Order', 2),
(49, 11, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI004 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱63,014.00 (40% of total) to proceed with installation scheduling.', 'Unread', '2026-02-03 05:54:33', NULL, 4, 'Order', 2),
(50, 11, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI004 fabrication is complete! Installation is scheduled for February 5, 2026 at 5:54 AM. You can request to change the date within the next 7 days if needed.', 'Unread', '2026-02-03 05:54:47', NULL, 4, 'Order', 2),
(51, 11, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI005 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Unread', '2026-02-03 06:08:23', NULL, 5, 'Order', 2),
(52, 11, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI005 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Unread', '2026-02-03 06:09:08', NULL, 5, 'Order', 2),
(53, 11, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI005 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱15,764.00 (40% of total) to proceed with installation scheduling.', 'Unread', '2026-02-03 06:09:25', NULL, 5, 'Order', 2),
(54, 11, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI005 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱15,764.00 (40% of total) to proceed with installation scheduling.', 'Unread', '2026-02-03 06:09:35', NULL, 5, 'Order', 2),
(55, 11, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI005 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱15,764.00 (40% of total) to proceed with installation scheduling.', 'Unread', '2026-02-03 06:09:49', NULL, 5, 'Order', 2),
(56, 11, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI005 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱15,764.00 (40% of total) to proceed with installation scheduling.', 'Unread', '2026-02-03 06:10:12', NULL, 5, 'Order', 2),
(57, 11, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI005 fabrication is complete! Installation is scheduled for February 5, 2026 at 6:10 AM. You can request to change the date within the next 7 days if needed.', 'Unread', '2026-02-03 06:10:30', NULL, 5, 'Order', 2),
(58, 11, 'fa-check-circle', 'Order', 'Installation Complete - Final Payment Due', 'Your order #GI005 has been successfully installed! Please submit the final payment of ₱3,941.00 (10%) by February 8, 2026. Failure to pay within 5 days may result in product removal.', 'Unread', '2026-02-03 06:11:45', NULL, 5, 'Order', 2),
(59, 10, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI012 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Unread', '2026-02-07 09:14:14', NULL, 12, 'Order', 2),
(60, 10, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI018 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Unread', '2026-02-08 05:54:05', NULL, 18, 'Order', 2),
(61, 15, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI005 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-09 07:01:49', '2026-02-09 10:41:14', 5, 'Order', 2),
(62, 15, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI005 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-09 07:58:18', '2026-02-09 10:41:14', 5, 'Order', 2),
(63, 15, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI007 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-09 09:23:36', '2026-02-09 10:41:14', 7, 'Order', 2),
(64, 15, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI007 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-09 12:57:21', '2026-02-10 00:40:54', 7, 'Order', 2),
(65, 15, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI007 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱5,014.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-09 15:46:21', '2026-02-10 00:40:54', 7, 'Order', 2),
(66, 15, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI007 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-09 15:47:56', '2026-02-10 00:40:54', 7, 'Order', 2),
(67, 15, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI007 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱5,014.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-09 15:48:31', '2026-02-10 00:40:54', 7, 'Order', 2),
(68, 15, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI005 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱5,014.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-09 17:54:27', '2026-02-10 00:40:54', 5, 'Order', 2),
(69, 15, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI008 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-09 18:33:00', '2026-02-10 00:40:54', 8, 'Order', 2),
(70, 15, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI008 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-09 18:35:51', '2026-02-10 00:40:54', 8, 'Order', 2),
(71, 15, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI008 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱5,014.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-09 18:44:50', '2026-02-10 00:40:54', 8, 'Order', 2),
(72, 15, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI009 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-09 19:39:42', '2026-02-10 00:40:54', 9, 'Order', 2),
(73, 15, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI009 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-09 19:40:50', '2026-02-10 00:40:54', 9, 'Order', 2),
(74, 15, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI009 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱2,000.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-09 19:41:28', '2026-02-10 00:40:54', 9, 'Order', 2),
(75, 15, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI009 fabrication is complete! Installation is scheduled for February 11, 2026 at 10:47 PM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-09 22:47:47', '2026-02-10 00:40:54', 9, 'Order', 2),
(76, 15, 'fa-check-circle', 'Order', 'Payment Complete - Order Finished!', '🎉 Thank you! Your final payment of ₱500.00 for order #GI009 has been received. Your order is now complete!', 'Unread', '2026-02-10 00:46:28', NULL, 9, 'Order', 17),
(77, 15, 'fa-check-circle', 'Order', 'Installation Complete - Final Payment Due', '🎉 Your order #GI009 has been successfully installed! Please submit the final payment of ₱500.00 (10%) by February 15, 2026. You can pay online via GCash, Maya, or Credit/Debit Card in your Track Order page. Failure to pay within 5 days may result in product removal.', 'Unread', '2026-02-10 01:18:52', NULL, 9, 'Order', 2),
(78, 15, 'fa-check-circle', 'Order', 'Installation Complete - Final Payment Due', '🎉 Your order #GI009 has been successfully installed! Please submit the final payment of ₱500.00 (10%) by February 15, 2026. You can pay online via GCash, Maya, or Credit/Debit Card in your Track Order page. Failure to pay within 5 days may result in product removal.', 'Unread', '2026-02-10 01:19:00', NULL, 9, 'Order', 2),
(79, 15, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI010 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Unread', '2026-02-10 02:36:35', NULL, 10, 'Order', 2),
(80, 15, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI010 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Unread', '2026-02-10 02:41:36', NULL, 10, 'Order', 2),
(81, 15, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI010 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱2,400.00 (40% of total) to proceed with installation scheduling.', 'Unread', '2026-02-10 02:42:05', NULL, 10, 'Order', 2),
(82, 15, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI010 fabrication is complete! Installation is scheduled for February 12, 2026 at 2:43 AM. You can request to change the date within the next 7 days if needed.', 'Unread', '2026-02-10 02:43:07', NULL, 10, 'Order', 2),
(83, 15, 'fa-check-circle', 'Order', 'Payment Complete - Order Finished!', '🎉 Thank you! Your final payment of ₱600.00 for order #GI010 has been received. Your order is now complete!', 'Unread', '2026-02-10 02:45:22', NULL, 10, 'Order', 17),
(84, 15, 'fa-check-circle', 'Order', 'Payment Complete - Order Finished!', '🎉 Thank you! Your final payment of ₱600.00 for order #GI010 has been received. Your order is now complete!', 'Unread', '2026-02-10 02:45:28', NULL, 10, 'Order', 17),
(85, 17, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI011 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-10 02:57:28', '2026-02-11 12:30:08', 11, 'Order', 2),
(86, 17, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI011 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-10 03:02:34', '2026-02-11 12:30:08', 11, 'Order', 2),
(87, 17, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI011 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱4,800.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-10 03:03:04', '2026-02-11 12:30:08', 11, 'Order', 2),
(88, 17, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI011 fabrication is complete! Installation is scheduled for February 12, 2026 at 3:04 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-10 03:04:38', '2026-02-11 12:30:08', 11, 'Order', 2),
(89, 17, 'fa-check-circle', 'Order', 'Payment Complete - Order Finished!', '🎉 Thank you! Your final payment of ₱1,200.00 for order #GI011 has been received. Your order is now complete!', 'Read', '2026-02-10 03:06:14', '2026-02-11 12:30:08', 11, 'Order', 19),
(90, 15, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI006 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Unread', '2026-02-11 10:44:16', NULL, 6, 'Order', 2),
(91, 18, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI012 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-11 15:48:35', '2026-02-11 16:04:37', 12, 'Order', 2),
(92, 18, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI012 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-11 15:51:37', '2026-02-11 16:04:37', 12, 'Order', 2),
(93, 18, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI012 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱1,600.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-11 15:52:55', '2026-02-11 16:04:37', 12, 'Order', 2),
(94, 18, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI012 fabrication is complete! Installation is scheduled for February 13, 2026 at 3:54 PM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-11 15:54:12', '2026-02-11 16:04:37', 12, 'Order', 2),
(95, 18, 'fa-check-circle', 'Order', 'Payment Complete - Order Finished!', '🎉 Thank you! Your final payment of ₱503.50 for order #GI012 has been received. Your order is now complete!', 'Read', '2026-02-11 16:02:11', '2026-02-11 16:04:37', 12, 'Order', 20),
(96, 18, 'fa-check-circle', 'Order', 'Payment Complete - Order Finished!', '🎉 Thank you! Your final payment of ₱503.50 for order #GI012 has been received. Your order is now complete!', 'Read', '2026-02-11 16:02:16', '2026-02-11 16:04:37', 12, 'Order', 20),
(97, 18, 'fa-check-circle', 'Order', 'Installation Complete - Final Payment Due', '🎉 Your order #GI012 has been successfully installed! Please submit the final payment of ₱400.00 (10%) by February 16, 2026. You can pay online via GCash, Maya, or Credit/Debit Card in your Track Order page. Failure to pay within 5 days may result in product removal.', 'Read', '2026-02-11 16:03:36', '2026-02-11 16:04:37', 12, 'Order', 2),
(98, 18, 'fa-check-circle', 'Order', 'Installation Complete - Final Payment Due', '🎉 Your order #GI012 has been successfully installed! Please submit the final payment of ₱400.00 (10%) by February 16, 2026. You can pay online via GCash, Maya, or Credit/Debit Card in your Track Order page. Failure to pay within 5 days may result in product removal.', 'Read', '2026-02-11 16:03:42', '2026-02-11 16:04:37', 12, 'Order', 2),
(99, 18, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI013 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Unread', '2026-02-11 22:26:07', NULL, 13, 'Order', 2),
(100, 1, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI014 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-12 04:52:37', '2026-02-12 05:01:18', 14, 'Order', 4),
(101, 1, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI014 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-12 04:55:06', '2026-02-12 05:01:18', 14, 'Order', 4),
(102, 1, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI014 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱4,000.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 04:58:29', '2026-02-12 05:01:18', 14, 'Order', 4),
(103, 1, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI014 fabrication is complete! Installation is scheduled for February 14, 2026 at 4:59 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-12 04:59:13', '2026-02-12 05:01:18', 14, 'Order', 4),
(104, 1, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI014 fabrication is complete! Installation is scheduled for February 14, 2026 at 4:59 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-12 05:00:28', '2026-02-12 05:01:18', 14, 'Order', 4),
(105, 1, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI014 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱4,000.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 05:02:39', '2026-02-12 05:16:45', 14, 'Order', 4),
(106, 1, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI014 fabrication is complete! Installation is scheduled for February 14, 2026 at 4:59 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-12 05:03:14', '2026-02-12 05:16:45', 14, 'Order', 4),
(107, 1, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI014 fabrication is complete! Installation is scheduled for February 14, 2026 at 4:59 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-12 05:03:27', '2026-02-12 05:16:45', 14, 'Order', 4),
(108, 1, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI014 fabrication is complete! Installation is scheduled for February 14, 2026 at 4:59 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-12 05:03:31', '2026-02-12 05:16:45', 14, 'Order', 4),
(109, 1, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI015 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-12 05:04:09', '2026-02-12 05:16:45', 15, 'Order', 4),
(110, 1, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI015 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-12 05:08:18', '2026-02-12 05:16:45', 15, 'Order', 4),
(111, 1, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI015 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱4,000.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 05:09:38', '2026-02-12 05:16:45', 15, 'Order', 4),
(112, 1, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI015 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱4,000.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 05:12:48', '2026-02-12 05:16:45', 15, 'Order', 4),
(113, 1, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI015 fabrication is complete! Installation is scheduled for February 14, 2026 at 5:14 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-12 05:14:08', '2026-02-12 05:16:45', 15, 'Order', 4),
(114, 1, 'fa-star', 'Order', 'Order Completed', 'Your order #GI015 has been completed and installed. Thank you for choosing Glassify!', 'Read', '2026-02-12 05:15:15', '2026-02-12 05:16:45', 15, 'Order', 4),
(115, 19, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI016 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-12 05:20:29', '2026-02-12 07:17:46', 16, 'Order', 4),
(116, 19, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI016 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-12 05:21:42', '2026-02-12 07:17:46', 16, 'Order', 4),
(117, 19, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI016 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱2,000.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 05:23:44', '2026-02-12 07:17:46', 16, 'Order', 4),
(118, 19, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI016 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱2,000.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 05:24:07', '2026-02-12 07:17:46', 16, 'Order', 4),
(119, 19, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI016 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱2,000.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 05:24:18', '2026-02-12 07:17:46', 16, 'Order', 4),
(120, 19, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI016 fabrication is complete! Installation is scheduled for February 14, 2026 at 5:27 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-12 05:27:27', '2026-02-12 07:17:46', 16, 'Order', 4),
(121, 19, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI017 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-12 06:05:58', '2026-02-12 07:17:46', 17, 'Order', 4),
(122, 19, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI017 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-12 06:15:12', '2026-02-12 07:17:46', 17, 'Order', 4),
(123, 19, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI017 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱80.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 06:15:42', '2026-02-12 07:17:46', 17, 'Order', 4),
(124, 19, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI017 fabrication is complete! Installation is scheduled for February 14, 2026 at 6:17 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-12 06:17:09', '2026-02-12 07:17:46', 17, 'Order', 4),
(125, 19, 'fa-check-circle', 'Order', 'Payment Complete - Order Finished!', '🎉 Thank you! Your final payment of ₱3.50 for order #GI017 has been received. Your order is now complete!', 'Read', '2026-02-12 06:19:43', '2026-02-12 07:17:46', 17, 'Order', 21),
(126, 19, 'fa-check-circle', 'Order', 'Installation Complete - Final Payment Due', '🎉 Your order #GI017 has been successfully installed! Please submit the final payment of ₱20.00 (10%) by February 17, 2026. You can pay online via GCash, Maya, or Credit/Debit Card in your Track Order page. Failure to pay within 5 days may result in product removal.', 'Read', '2026-02-12 06:20:08', '2026-02-12 07:17:46', 17, 'Order', 4),
(127, 19, 'fa-check-circle', 'Order', 'Installation Complete - Final Payment Due', '🎉 Your order #GI017 has been successfully installed! Please submit the final payment of ₱20.00 (10%) by February 17, 2026. You can pay online via GCash, Maya, or Credit/Debit Card in your Track Order page. Failure to pay within 5 days may result in product removal.', 'Read', '2026-02-12 06:20:19', '2026-02-12 07:17:46', 17, 'Order', 4),
(128, 19, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI018 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-12 06:21:48', '2026-02-12 07:17:46', 18, 'Order', 4),
(129, 19, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI018 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-12 06:25:13', '2026-02-12 07:17:46', 18, 'Order', 4),
(130, 19, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI018 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱800.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 06:25:37', '2026-02-12 07:17:46', 18, 'Order', 4),
(131, 19, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI018 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱800.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 06:26:48', '2026-02-12 07:17:46', 18, 'Order', 4),
(132, 19, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI018 fabrication is complete! Installation is scheduled for February 14, 2026 at 6:26 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-12 06:26:58', '2026-02-12 07:17:46', 18, 'Order', 4),
(133, 19, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI019 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Read', '2026-02-12 06:33:16', '2026-02-12 07:17:46', 19, 'Order', 4),
(134, 19, 'fa-cog', 'Order', 'Order in Fabrication', 'Great news! Your order #GI019 has completed the ocular visit and is now being fabricated. We\'ll notify you once fabrication is complete.', 'Read', '2026-02-12 06:34:08', '2026-02-12 07:17:46', 19, 'Order', 4),
(135, 19, 'fa-money-bill-wave', 'Payment', 'Fabrication Payment Required', 'Great news! Your order #GI019 is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱14.00 (40% of total) to proceed with installation scheduling.', 'Read', '2026-02-12 06:34:30', '2026-02-12 07:17:46', 19, 'Order', 4),
(136, 19, 'fa-calendar-check', 'Delivery', 'Installation Scheduled', 'Your order #GI019 fabrication is complete! Installation is scheduled for February 14, 2026 at 6:36 AM. You can request to change the date within the next 7 days if needed.', 'Read', '2026-02-12 06:36:29', '2026-02-12 07:17:46', 19, 'Order', 4),
(137, 19, 'fa-check-circle', 'Order', 'Payment Complete - Order Finished!', '🎉 Thank you! Your final payment of ₱3.50 for order #GI019 has been received. Your order is now complete!', 'Read', '2026-02-12 06:37:38', '2026-02-12 07:17:46', 19, 'Order', 21),
(138, 19, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI020 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Unread', '2026-02-12 07:31:30', NULL, 20, 'Order', 4),
(139, 19, 'fa-calendar-alt', 'Order', 'Booking Confirmed', 'Your booking for order #GI021 is confirmed. We will schedule an ocular visit soon (estimated: TBD - We will contact you to schedule).', 'Unread', '2026-02-12 08:07:24', NULL, 21, 'Order', 4);

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
  `Customization` text DEFAULT NULL COMMENT 'JSON string containing all dynamic customization fields',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customization`
--

INSERT INTO `customization` (`CustomizationID`, `Customer_ID`, `Product_ID`, `Dimensions`, `GlassShape`, `GlassType`, `GlassThickness`, `EdgeWork`, `FrameType`, `Engraving`, `DesignRef`, `LEDBacklight`, `DoorOperation`, `Configuration`, `EstimatePrice`, `PriceBreakdown`, `Customization`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 8, 19, '45in x 45in', 'Square', NULL, NULL, 'Flat polished edge', NULL, 'None', 'uploads/designs/design_8_1769542268_6979127cf2ce9.png', NULL, NULL, NULL, '101250.00', NULL, NULL, '2026-01-27 19:31:09', '2026-01-27 19:31:09'),
(2, 8, 18, '45in x 45in', 'Round', 'Clear', NULL, 'Beveled', 'Hanalok', 'None', 'uploads/designs/design_8_1770076991_69813b3fe7814.png', NULL, NULL, NULL, '131625.00', NULL, NULL, '2026-02-03 00:03:11', '2026-02-03 00:03:11'),
(3, 8, 23, '45in x 35in', NULL, 'Clear', NULL, NULL, NULL, 'on', 'uploads/designs/design_8_1770078777_698142391bdae.png', NULL, NULL, NULL, '149625.00', NULL, NULL, '2026-02-03 00:32:57', '2026-02-03 00:32:57'),
(4, 8, 13, '45in x 35in', 'Round', 'Ordinary', '6mm', 'Beveled', 'Hanalok', 'None', 'uploads/designs/design_8_1770093676_69817c6c4a5f3.png', NULL, NULL, NULL, '110250.00', NULL, NULL, '2026-02-03 04:41:16', '2026-02-03 04:41:16'),
(5, 11, 21, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_11_1770094096_69817e1031ee2.png', NULL, NULL, NULL, '157500.00', NULL, NULL, '2026-02-03 04:48:16', '2026-02-03 04:48:16'),
(6, 11, 21, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_11_1770094131_69817e33a4ea7.png', NULL, NULL, NULL, '157500.00', NULL, NULL, '2026-02-03 04:48:51', '2026-02-03 04:48:51'),
(7, 11, 1, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_11_1770095270_698182a628e01.png', NULL, NULL, NULL, '39375.00', NULL, NULL, '2026-02-03 05:07:50', '2026-02-03 05:07:50'),
(8, 14, 19, '45in x 35in', 'Rectangle', NULL, NULL, 'Flat polished edge', NULL, 'None', 'uploads/designs/design_14_1770202716_6983265cd9860.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-04 10:58:36', '2026-02-04 10:58:36'),
(9, 10, 23, '45in x 35in', NULL, 'Clear', NULL, NULL, NULL, 'on', 'uploads/designs/design_10_1770449603_6986eac32a20a.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-07 07:33:23', '2026-02-07 07:33:23'),
(10, 10, 22, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_10_1770457006_698707aecf2f4.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-07 09:36:46', '2026-02-07 09:36:46'),
(11, 10, 20, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_10_1770461544_6987196831b5b.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-07 10:52:24', '2026-02-07 10:52:24'),
(12, 10, 23, '45in x 35in', NULL, 'Clear', NULL, NULL, NULL, 'on', 'uploads/designs/design_10_1770469946_69873a3a5772b.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-07 13:12:26', '2026-02-07 13:12:26'),
(13, 10, 21, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_10_1770476740_698754c4caf4a.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-07 15:05:40', '2026-02-07 15:05:40'),
(14, 10, 23, '45in x 35in', NULL, 'Clear', NULL, NULL, NULL, 'on', 'uploads/designs/design_10_1770482427_69876afb7aa6f.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-07 16:40:27', '2026-02-07 16:40:27'),
(15, 10, 20, '45in x 35in', NULL, 'Tempered', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_10_1770514210_6987e722da8aa.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 01:30:10', '2026-02-08 01:30:10'),
(16, 10, 22, '45in x 35in', NULL, 'Reflective', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_10_1770516771_6987f12361c4f.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 02:12:51', '2026-02-08 02:12:51'),
(17, 10, 22, '45in x 35in', NULL, 'Reflective', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_10_1770517158_6987f2a688bf7.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 02:19:18', '2026-02-08 02:19:18'),
(18, 10, 22, '45in x 35in', NULL, 'Tempered', NULL, NULL, 'Matte Black', 'None', 'uploads/designs/design_10_1770517289_6987f329bc72a.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 02:21:29', '2026-02-08 02:21:29'),
(19, 10, 22, '83in x 74in', NULL, 'Ordinary', NULL, NULL, 'Analok', 'None', 'uploads/designs/design_10_1770517347_6987f36366e6a.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 02:22:27', '2026-02-08 02:22:27'),
(20, 10, 22, '45in x 35in', NULL, 'Tempered', NULL, NULL, 'Matte Gray', 'None', 'uploads/designs/design_10_1770517815_6987f5370cbe1.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 02:30:15', '2026-02-08 02:30:15'),
(21, 10, 22, '45in x 35in', NULL, 'Tempered', NULL, NULL, 'Analok', 'None', 'uploads/designs/design_10_1770518180_6987f6a40c0d4.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 02:36:20', '2026-02-08 02:36:20'),
(22, 10, 22, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Analok', 'None', 'uploads/designs/design_10_1770518297_6987f71989c07.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 02:38:17', '2026-02-08 02:38:17'),
(23, 10, 22, '45in x 35in', NULL, 'Tempered', NULL, NULL, 'Analok', 'None', 'uploads/designs/design_10_1770518719_6987f8bf5416f.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 02:45:19', '2026-02-08 02:45:19'),
(24, 10, 20, '75in x 87in', NULL, 'Tempered', NULL, NULL, 'Analok', 'None', 'uploads/designs/design_10_1770519072_6987fa20046ae.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 02:51:12', '2026-02-08 02:51:12'),
(25, 10, 20, '45in x 35in', NULL, 'Tempered', NULL, NULL, 'Analok', 'None', 'uploads/designs/design_10_1770519097_6987fa39d1add.png', NULL, NULL, NULL, '0.00', NULL, NULL, '2026-02-08 02:51:37', '2026-02-08 02:51:37'),
(26, 10, 22, '122in x 143in', NULL, 'Tempered', NULL, NULL, 'Matte Black', 'None', 'uploads/designs/design_10_1770519732_6987fcb4db359.png', NULL, NULL, NULL, '0.00', NULL, '{\"numberOfPanels\":\"4 Panels\",\"transomType\":\"Fixed Transom Sill (Fixed glass at bottom)\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\",\"frameColor\":\"Matte Black\",\"glassType\":\"Tempered\",\"glassColor\":\"Bronze\",\"glassThickness\":\"8mm\",\"lockType\":\"Flushlok #12\",\"rollerType\":\"Blue Single Roller\"}', '2026-02-08 03:02:12', '2026-02-08 03:02:12'),
(27, 10, 9, '45in x 35in', NULL, 'Tempered', '8mm', NULL, 'Analok', 'None', 'uploads/designs/design_10_1770521251_698802a3bcd1e.png', NULL, NULL, NULL, '0.00', NULL, '{\"glassType\":\"Tempered\",\"glassColor\":\"Bronze\",\"frameColor\":\"Analok\",\"operation\":\"Awning (push-out)\",\"openingDirection\":\"Top-hinged\",\"thickness\":\"8mm\",\"screen\":\"Without Screen\"}', '2026-02-08 03:27:31', '2026-02-08 03:27:31'),
(28, 10, 7, '45in x 35in', NULL, 'Reflective', '8mm', NULL, 'Analok', 'None', 'uploads/designs/design_10_1770522444_6988074c8ac4b.png', NULL, NULL, NULL, '0.00', NULL, '{\"glassType\":\"Reflective\",\"glassColor\":\"Frosted\",\"frameColor\":\"Analok\",\"operation\":\"Awning (push-out)\",\"openingDirection\":\"Top-hinged\",\"thickness\":\"8mm\",\"screen\":\"Without Screen\"}', '2026-02-08 03:47:24', '2026-02-08 03:47:24'),
(29, 10, 23, '45in x 35in', NULL, 'Clear', NULL, NULL, NULL, 'on', 'uploads/designs/design_10_1770528221_69881ddd64dc3.png', NULL, NULL, NULL, '0.00', NULL, '{\"glassType\":\"Clear\",\"doorType\":\"Single swing\",\"doorSwing\":\"Left swing\",\"fixedPanels\":\"None\",\"handleType\":\"Various pull handle designs\",\"hardwareFinish\":\"Polished Chrome/Stainless Steel\",\"installation\":\"Patch fittings (minimalist hardware)\",\"hardware\":\"Push/pull handles\",\"softClose\":true}', '2026-02-08 05:23:41', '2026-02-08 05:23:41'),
(30, 18, 22, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_18_1770820783_698c94af8d406.png', NULL, NULL, NULL, '0.00', NULL, '{\"numberOfPanels\":\"4 Panels\",\"transomType\":\"Fixed Transom Head (Fixed glass at top)\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"10mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Blue Single Roller\"}', '2026-02-11 14:39:43', '2026-02-11 14:39:43'),
(31, 18, 7, '45in x 35in', 'Custom shapes', 'Tempered', '12mm', 'Raw', 'Analok', 'None', 'uploads/designs/design_18_1770820884_698c951451a58.png', NULL, NULL, NULL, '0.00', NULL, '{\"glassType\":\"Tempered\",\"glassColor\":\"Clear\",\"frameColor\":\"Analok\",\"operation\":\"Awning (push-out)\",\"openingDirection\":\"Top-hinged\",\"thickness\":\"12mm\",\"screen\":\"Without Screen\",\"shape\":\"Custom shapes\",\"edgeFinish\":\"Raw\",\"mountingMethod\":\"Stand\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\"}', '2026-02-11 14:41:24', '2026-02-11 14:41:24'),
(32, 19, 22, '45in x 35in', NULL, 'Frosted', NULL, NULL, 'Wood Finish', 'None', 'uploads/designs/design_19_1770872567_698d5ef752bf4.png', NULL, NULL, NULL, '0.00', NULL, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Wood Finish\",\"glassType\":\"Frosted\",\"glassColor\":\"Smoked\",\"glassThickness\":\"8mm\",\"lockType\":\"Durable Flushlok\",\"rollerType\":\"Blue Single Roller\"}', '2026-02-12 05:02:47', '2026-02-12 05:02:47'),
(33, 19, 22, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_19_1770873651_698d633360728.png', NULL, NULL, NULL, '0.00', NULL, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-12 05:20:51', '2026-02-12 05:20:51'),
(34, 19, 22, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_19_1770874346_698d65ea2ea7b.png', NULL, NULL, NULL, '0.00', NULL, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-12 05:32:26', '2026-02-12 05:32:26'),
(35, 19, 21, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_19_1770877746_698d73324fc68.png', NULL, NULL, NULL, '0.00', NULL, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-12 06:29:06', '2026-02-12 06:29:06'),
(36, 19, 22, '45in x 35in', NULL, 'Reflective', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_19_1770879857_698d7b71bf5a7.png', NULL, NULL, NULL, '0.00', NULL, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Reflective\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-12 07:04:17', '2026-02-12 07:04:17'),
(37, 19, 21, '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', 'uploads/designs/design_19_1770879873_698d7b8128b4f.png', NULL, NULL, NULL, '0.00', NULL, '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Smoked\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\"}', '2026-02-12 07:04:33', '2026-02-12 07:04:33');

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
(1, 'Windows', 'Sliding', 'Windows_Sliding', '[{\"type\":\"tags\",\"label\":\"Number of Panels\",\"id\":\"numberOfPanels\",\"options\":[\"2 Panels\",\"4 Panels\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Transom Type (Top \\/ Bottom Fixed Panel)\",\"id\":\"transomType\",\"options\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Track System (Sliding Rail Count)\",\"id\":\"trackSystem\",\"options\":[\"2 Tracks\",\"3 Tracks\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Panel Configuration\",\"id\":\"panelConfiguration\",\"options\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Frame Color\",\"id\":\"frameColor\",\"options\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Glass Color\",\"id\":\"glassColor\",\"options\":[\"Clear\",\"Bronze\",\"Smoked\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Glass Thickness\",\"id\":\"glassThickness\",\"options\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Lock Type\",\"id\":\"lockType\",\"options\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"stepNumber\":4},{\"type\":\"tags\",\"label\":\"Roller Type\",\"id\":\"rollerType\",\"options\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"],\"stepNumber\":4},{\"type\":\"tags\",\"label\":\"Screen\",\"id\":\"screen\",\"options\":[\"With Screen\",\"Without Screen\"],\"stepNumber\":4}]', '2026-01-14 12:14:23', '2026-02-11 18:20:06'),
(2, 'Windows', 'Awning', 'Windows_Awning', '[{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Glass Color\",\"id\":\"glassColor\",\"options\":[\"Clear\",\"Bronze\",\"Smoked\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Frame Color\\/Material\",\"id\":\"frameColor\",\"options\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Operation\",\"id\":\"operation\",\"options\":[\"Awning (crank-out)\",\"Awning (push-out)\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Opening Direction\",\"id\":\"openingDirection\",\"options\":[\"Top-hinged\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Thickness (mm)\",\"id\":\"thickness\",\"options\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Screen\",\"id\":\"screen\",\"options\":[\"With Screen\",\"Without Screen\"],\"stepNumber\":2}]', '2026-01-21 02:11:42', '2026-02-11 12:14:35'),
(4, 'Mirrors & Specialty Glass', 'Glass Board', 'Specialty_Glass Board', '[{\"type\":\"tags\",\"label\":\"Shape\",\"id\":\"shape\",\"options\":[\"Round\",\"Rectangle\",\"Oval\",\"Square\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Edge Finish\",\"id\":\"edgeFinish\",\"options\":[\"Beveled\",\"Polished\",\"Raw\",\"Beveled edge\",\"Flat polished edge\",\"Pencil edge\"],\"stepNumber\":1},{\"type\":\"number\",\"label\":\"Corner Radius (in)\",\"id\":\"cornerRadius\",\"min\":0,\"step\":0.1,\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Mounting Method\",\"id\":\"mountingMethod\",\"options\":[\"Wall-mounted\",\"Stand\",\"Adhesive\"],\"stepNumber\":2}]', '2026-01-14 15:30:55', '2026-01-27 11:12:41'),
(5, 'Mirrors & Specialty Glass', 'Mirrors', 'Specialty_Mirrors', '[{\"type\":\"tags\",\"label\":\"Shape\",\"id\":\"shape\",\"options\":[\"Round\",\"Rectangle\",\"Oval\",\"Square\"],\"stepNumber\":1},{\"type\":\"number\",\"label\":\"Corner Radius (in)\",\"id\":\"cornerRadius\",\"min\":0,\"step\":0.1,\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Frame Type\",\"id\":\"frameType\",\"options\":[\"Frameless\",\"Framed\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Frame Material\\/Color\",\"id\":\"frameColor\",\"options\":[\"White\",\"Black\",\"Gold\",\"Machine Polished Edges\",\"Beveled Edge\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Copper Free & Lead Free Mirror\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Thickness (mm)\",\"id\":\"thickness\",\"options\":[\"6mm\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Lighting\",\"id\":\"lighting\",\"options\":[\"Integrated LED lighting\",\"Backlighting\",\"Front lighting\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"LED Color\\/Temperature\",\"id\":\"ledColorTemperature\",\"options\":[\"Warm white\",\"Cool white\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Control\",\"id\":\"control\",\"options\":[\"Touch sensor button\",\"Dimmer\",\"Defogger\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Mounting Method\",\"id\":\"mountingMethod\",\"options\":[\"Wall-mounted\",\"Stand\",\"Adhesive\",\"Leaning\"],\"stepNumber\":4},{\"type\":\"tags\",\"label\":\"Quantity\",\"id\":\"quantity\",\"options\":[\"Available in sets (3 sets, or individually)\"],\"stepNumber\":4}]', '2026-01-14 15:52:15', '2026-02-11 15:20:34'),
(6, 'Glass Partitions & Enclosures', 'Frameless Glass', 'Partitions_Frameless Glass', '[{\"type\":\"tags\",\"label\":\"Layout\",\"id\":\"layout\",\"options\":[\"L-shape\",\"Straight\",\"U-shape\",\"L-type\",\"Neo-angle\",\"Square\",\"Bay\",\"Other corner layouts\"],\"stepNumber\":1,\"step\":1},{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Clear\",\"Frosted\",\"Tinted\",\"Frosted (full or partial)\",\"Clear with frosted sticker\",\"Fully frosted\"],\"stepNumber\":1,\"step\":1},{\"type\":\"tags\",\"label\":\"Finish\",\"id\":\"finish\",\"options\":[\"Clear\",\"Frosted\",\"Patterned\"],\"stepNumber\":1,\"step\":1},{\"type\":\"number\",\"label\":\"Glass Thickness (mm)\",\"id\":\"glassThickness\",\"min\":1,\"step\":1,\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Hardware Color\",\"id\":\"hardwareColor\",\"stepNumber\":2,\"options\":[\"Black\",\"Silver\",\"Gold\",\"White\",\"Bronze\",\"Chrome\\/Stainless Steel\",\"Black Matte\",\"Brushed Nickel\",\"Stainless Steel\"],\"step\":2},{\"type\":\"tags\",\"label\":\"Mounting Hardware\",\"id\":\"mountingHardware\",\"stepNumber\":2,\"options\":[\"Stainless Fixed Bracket\",\"Gold U-Channel\",\"Analok U-Channel (anodized aluminum)\",\"Stainless U-Channel\",\"Other bracket types\",\"Standard mounting\"],\"step\":2},{\"type\":\"tags\",\"label\":\"Configuration\",\"id\":\"configuration\",\"options\":[\"Single partition\",\"Multiple partitions\",\"2 fixed panels\",\"3 fixed panels\",\"Custom configurations\"],\"stepNumber\":2,\"step\":2}]', '2026-01-15 06:26:57', '2026-01-15 06:26:57'),
(7, 'Glass Partitions & Enclosures', 'Shower Enclosure', 'Partitions_Shower Enclosure', '[{\"type\":\"tags\",\"label\":\"Layout\",\"id\":\"layout\",\"options\":[\"L-shape\",\"Straight\",\"U-shape\",\"L-type\",\"Neo-angle\",\"Square\",\"Bay\",\"Other corner layouts\"],\"stepNumber\":1,\"step\":1},{\"type\":\"tags\",\"label\":\"Configuration\",\"id\":\"configuration\",\"options\":[\"Fixed and swing\",\"Swing with small fixed glass\",\"Single sliding door\",\"Double sliding doors\",\"Sliding with fixed panels\",\"Single sliding\",\"Double sliding\",\"With fixed panels\",\"2 fixed panels\",\"3 fixed panels\",\"Custom configurations\"],\"stepNumber\":2,\"step\":2},{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Clear\",\"Frosted\",\"Tinted\",\"Frosted (full or partial)\",\"Clear with frosted sticker\",\"Fully frosted\",\"Custom frosting patterns\",\"Frosted (full or partial with custom patterns\\/heights)\"],\"stepNumber\":1,\"step\":1},{\"type\":\"tags\",\"label\":\"Glass Treatment\",\"id\":\"glassTreatment\",\"options\":[\"Frosted sticker (customizable patterns, opacity, colors)\",\"Clear\",\"Custom patterns\",\"Heights (top clear, bottom frosted)\",\"Colors\"],\"stepNumber\":1,\"step\":1},{\"type\":\"number\",\"label\":\"Glass Thickness (mm)\",\"id\":\"glassThickness\",\"min\":1,\"step\":1,\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Hardware Finish\",\"id\":\"hardwareColor\",\"stepNumber\":2,\"options\":[\"Chrome\\/Stainless Steel\",\"Black Matte\",\"Gold\",\"Brushed Nickel\",\"Polished Chrome\\/Stainless Steel\",\"Matte Black (handles, hinges, connectors)\",\"Matte Black (rail, rollers, handles)\",\"Matte Black (hinges, handle, top bracing bar)\",\"Stainless Steel\",\"Black\",\"Silver\",\"Bronze\"],\"step\":2},{\"type\":\"tags\",\"label\":\"Door Swing\",\"id\":\"doorSwing\",\"options\":[\"Left-hinged\",\"Right-hinged\",\"Left swing\",\"Right swing\"],\"stepNumber\":2,\"step\":2},{\"type\":\"tags\",\"label\":\"Mounting\",\"id\":\"mounting\",\"options\":[\"Standard mounting\",\"Custom mounting methods\"],\"stepNumber\":2,\"step\":2},{\"type\":\"tags\",\"label\":\"Handle Style\",\"id\":\"handleStyle\",\"options\":[\"Various pull handle designs\",\"Various pull handles\",\"Knob handles\",\"Square handles\",\"Square matte black\",\"Round\",\"Bar-style\"],\"stepNumber\":2,\"step\":2}]', '2026-01-15 06:27:58', '2026-01-14 23:28:13'),
(12, 'Windows', 'Casement', 'Windows_Casement', '[{\"type\":\"tags\",\"label\":\"Transom Type\",\"id\":\"transomType\",\"options\":[\"Casement w\\/FTH\",\"Casement w\\/FTS\",\"None\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Panel Configuration\",\"id\":\"panelConfiguration\",\"options\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Frame Color\",\"id\":\"frameColor\",\"options\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Glass Color\",\"id\":\"glassColor\",\"options\":[\"Clear\",\"Bronze\",\"Smoked\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Thickness\",\"id\":\"thickness\",\"options\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"stepNumber\":2}]', '2026-01-21 23:46:55', '2026-02-11 05:12:02'),
(13, 'Mirrors & Specialty Glass', 'Top Glass', 'Specialty_Top Glass', '[{\"type\":\"tags\",\"label\":\"Shape\",\"id\":\"shape\",\"options\":[\"Round\",\"Rectangle\",\"Oval\",\"Square\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Edge Finish\",\"id\":\"edgeFinish\",\"options\":[\"Beveled\",\"Polished\",\"Raw\",\"Beveled edge\",\"Flat polished edge\",\"Pencil edge\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Mounting Method\",\"id\":\"mountingMethod\",\"options\":[\"Wall-mounted\",\"Stand\",\"Adhesive\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Thickness (mm)\",\"id\":\"thicknessmm\",\"stepNumber\":2,\"options\":[\"6mm\",\"8mm\",\"10mm\"]}]', '2026-01-22 07:43:57', '2026-01-27 11:04:28'),
(14, 'Doors', 'Sliding', 'Doors_Sliding', '[{\"type\":\"tags\",\"label\":\"Number of Panels\",\"id\":\"numberOfPanels\",\"options\":[\"2 Panels\",\"4 Panels\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Transom Type (Top \\/ Bottom Fixed Panel)\",\"id\":\"transomType\",\"options\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Track System (Sliding Rail Count)\",\"id\":\"trackSystem\",\"options\":[\"2 Tracks\",\"3 Tracks\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Panel Configuration\",\"id\":\"panelConfiguration\",\"options\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Frame Color\",\"id\":\"frameColor\",\"options\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Glass Color\",\"id\":\"glassColor\",\"options\":[\"Clear\",\"Bronze\",\"Smoked\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Glass Thickness\",\"id\":\"glassThickness\",\"options\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Lock Type\",\"id\":\"lockType\",\"options\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"stepNumber\":4},{\"type\":\"tags\",\"label\":\"Roller Type\",\"id\":\"rollerType\",\"options\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"],\"stepNumber\":4}]', '2026-01-26 15:03:23', '2026-02-11 12:15:17'),
(15, 'Doors', 'Frameless', 'Doors_Frameless', '[{\"type\":\"tags\",\"label\":\"Glass Type\",\"id\":\"glassType\",\"options\":[\"Clear\",\"Tinted\",\"Frosted\",\"Laminated\",\"Laminated safety glass\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Door Type\",\"id\":\"doorType\",\"options\":[\"Single swing\",\"Double swing\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Door Swing\",\"id\":\"doorSwing\",\"options\":[\"Left swing\",\"Right swing\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Fixed Panels\",\"id\":\"fixedPanels\",\"options\":[\"None\",\"Fixed Side (Left)\",\"Fixed Side (Right)\",\"2 Panels\",\"Transom Only\",\"Both\"],\"stepNumber\":1},{\"type\":\"tags\",\"label\":\"Handle Style\",\"id\":\"handleType\",\"options\":[\"Various pull handle designs\",\"Various pull handles\",\"Decorative handles\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Hardware Finish\",\"id\":\"hardwareFinish\",\"options\":[\"Polished Chrome\\/Stainless Steel\",\"Matte Black\",\"Brushed Nickel\",\"Gold\",\"Chrome\\/Stainless Steel\"],\"stepNumber\":2},{\"type\":\"tags\",\"label\":\"Installation\",\"id\":\"installation\",\"options\":[\"Patch fittings (minimalist hardware)\",\"Standard\"],\"stepNumber\":3},{\"type\":\"tags\",\"label\":\"Hardware\",\"id\":\"hardware\",\"options\":[\"Push\\/pull handles\",\"Locks\",\"Closers\",\"Multi-point locks\"],\"stepNumber\":3},{\"type\":\"checkbox\",\"label\":\"Soft-close\",\"id\":\"softClose\",\"stepNumber\":3}]', '2026-01-26 17:17:43', '2026-01-27 11:17:53');

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
(8, 8, 'Agg', 'Pauig', NULL, 'aghii127@gmail.com', '$2y$10$lAvN.Di7dZPBSVV2QtGx9Oj9QKoLp35LSUh4UDik86C0ZC.dSjcka', '09614788448', NULL, 'Customer', 'Inactive', '2026-01-21 08:13:07', '2026-01-21 08:13:07', NULL, '2026-01-21 01:14:14'),
(9, 14, 'Glaire', 'Pauig', NULL, 'gitsquad2026@gmail.com', '$2y$10$I5baTAgNPAQDGdAsnC0LFuAZYaNZS7/U/c.ew03IMWaHv8U/Tp6Q6', '09111111111', NULL, 'Customer', 'Active', '2026-02-03 14:32:57', '2026-02-03 14:34:23', NULL, '2026-02-03 07:53:58'),
(10, 15, 'Glaire', 'Pauig', NULL, 'gitsquad2026@gmail.com', '$2y$10$bl0H9s28PT9luWSx9qJ..OSE/.B5Mlp6XKvhXj9vfGL4XtRgCDbea', '09111111111', NULL, 'Customer', 'Active', '2026-02-03 14:54:40', '2026-02-03 14:55:13', NULL, '2026-02-04 02:58:57');

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
  `CustomerRoleAtOrder` varchar(64) DEFAULT NULL,
  `CustomizationID` int(11) DEFAULT NULL,
  `SalesRep_ID` int(11) NOT NULL,
  `OrderDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `TotalAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `Status` enum('Pending Review','Awaiting Admin','Ready to Approve','Approved','Booking Confirmed','Ocular Pending','Quotation Available','Awaiting Payment','Disapproved','In Fabrication','Ready for Installation','Installed','Completed','Cancelled','Returned') DEFAULT 'Pending Review',
  `PaymentStatus` enum('Pending','Paid','Partial','Refunded') DEFAULT 'Pending',
  `PaymentMethod` varchar(50) DEFAULT NULL,
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
  `FabricationPaymentAmount` decimal(10,2) DEFAULT NULL,
  `FabricationPaymentMethod` varchar(50) DEFAULT NULL,
  `FabricationPaymentStatus` enum('Pending','Paid') DEFAULT 'Pending',
  `FabricationReceiptPath` varchar(255) DEFAULT NULL,
  `FabricationTransactionID` varchar(255) DEFAULT NULL,
  `InstallationPaymentAmount` decimal(10,2) DEFAULT NULL,
  `InstallationPaymentMethod` varchar(50) DEFAULT NULL,
  `InstallationPaymentStatus` enum('Pending','Paid') DEFAULT 'Pending',
  `InstallationReceiptPath` varchar(255) DEFAULT NULL,
  `InstallationTransactionID` varchar(255) DEFAULT NULL,
  `InstallationCompletedDate` date DEFAULT NULL,
  `InstallationPaymentDueDate` date DEFAULT NULL,
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

INSERT INTO `order` (`OrderID`, `OrderNumber`, `Customer_ID`, `CustomerRoleAtOrder`, `CustomizationID`, `SalesRep_ID`, `OrderDate`, `TotalAmount`, `Status`, `PaymentStatus`, `PaymentMethod`, `DeliveryAddress`, `SpecialInstructions`, `QuotationPDFUrl`, `ContractPDFUrl`, `ApprovedBy_SalesRep_ID`, `ApprovedBy_Admin_ID`, `Approved_Date`, `DisapprovedBy`, `DisapprovedBy_ID`, `DisapprovalReason`, `Disapproved_Date`, `CustomerNotified`, `CustomerNotified_Date`, `PreferredInstallationDate`, `OcularDate`, `FabricationDate`, `InstallationDate`, `EstimatedDelivery`, `Created_Date`, `Updated_Date`, `OrderType`, `OcularCompleted`, `OcularNotes`, `OcularCompletedBy_ID`, `FabricationStaff_ID`, `InstallationStaff_ID`, `FabricationStartDate`, `FabricationEndDate`, `ActualFabricationStartDate`, `ActualFabricationEndDate`, `FabricationProgress`, `FabricationStatus`, `FabricationNotes`, `FabricationPaymentAmount`, `FabricationPaymentMethod`, `FabricationPaymentStatus`, `FabricationReceiptPath`, `FabricationTransactionID`, `InstallationPaymentAmount`, `InstallationPaymentMethod`, `InstallationPaymentStatus`, `InstallationReceiptPath`, `InstallationTransactionID`, `InstallationCompletedDate`, `InstallationPaymentDueDate`, `QualityCheckNotes`, `AdminNotes`, `CustomerNotes`, `StaffNotes`, `Barcode`, `BarcodeImagePath`) VALUES
(1, 'GI001', 15, 'beginner', NULL, 3, '2026-02-09 04:09:11', '12535.00', 'Cancelled', 'Pending', NULL, '6, Noli Quen St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1105', '{\"contact_name\":\"Leonidas Opus Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"lerum.rommeljohnjeric.robles@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-13\",\"preferred_ocular_time\":\"09:00\"}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-02-13', NULL, NULL, NULL, NULL, '2026-02-09 04:09:11', '2026-02-11 12:03:51', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, 'That time of the day is not available', '', NULL, NULL, NULL),
(2, 'GI002', 15, 'beginner', NULL, 3, '2026-02-09 04:17:39', '6535.00', 'Cancelled', 'Pending', NULL, '6, Noli Quen St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1105', '{\"contact_name\":\"Leonidas Opus Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"lerum.rommeljohnjeric.robles@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-14\",\"preferred_ocular_time\":\"10:00\"}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-02-14', NULL, NULL, NULL, NULL, '2026-02-09 04:17:39', '2026-02-11 12:03:51', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Time of the day not available, only 1pm to 4pm.', '', NULL, NULL, NULL),
(3, 'GI003', 15, 'beginner', NULL, 3, '2026-02-09 04:36:13', '12535.00', 'Cancelled', 'Pending', NULL, '6, Noli Quen St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1105', '{\"contact_name\":\"Leonidas Opus Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"lerum.rommeljohnjeric.robles@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-13\",\"preferred_ocular_time\":\"12:00\"}', NULL, NULL, NULL, NULL, NULL, 'Admin', 2, 'Testing', '2026-02-09 05:36:28', 0, NULL, '2026-02-13', NULL, NULL, NULL, NULL, '2026-02-09 04:36:13', '2026-02-11 12:03:51', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Testing', '', NULL, NULL, NULL),
(4, 'GI004', 15, 'beginner', NULL, 3, '2026-02-09 04:36:50', '12535.00', 'Cancelled', 'Pending', NULL, '6, Noli Quen St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1105', '{\"contact_name\":\"Leonidas Opus Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"lerum.rommeljohnjeric.robles@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-16\",\"preferred_ocular_time\":\"10:00\"}', NULL, NULL, NULL, NULL, NULL, 'Admin', 2, '2 Testing', '2026-02-09 05:37:15', 0, NULL, '2026-02-16', NULL, NULL, NULL, NULL, '2026-02-09 04:36:50', '2026-02-11 12:03:51', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2 Testing', '', NULL, NULL, NULL),
(5, 'GI005', 15, 'beginner', NULL, 3, '2026-02-09 05:43:06', '12535.00', 'Ready for Installation', 'Pending', NULL, '6, Noli Quen St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1105', '{\"contact_name\":\"Leonidas Opus Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"lerum.rommeljohnjeric.robles@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-13\",\"preferred_ocular_time\":\"11:00\"}', NULL, NULL, NULL, 2, '2026-02-09 07:01:49', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-13', '2026-02-09', NULL, NULL, NULL, '2026-02-09 05:43:06', '2026-02-11 12:03:51', 'Direct', 1, NULL, 2, 4, NULL, '2026-02-09', '2026-02-14', NULL, NULL, 75, 'Ready', 'Issues: ', '0.00', '', 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL),
(6, 'GI006', 15, 'beginner', NULL, 3, '2026-02-09 07:00:49', '9535.00', 'Ocular Pending', 'Pending', NULL, '6, Noli Quen St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1105', '{\"contact_name\":\"Leonidas Opus Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"lerum.rommeljohnjeric.robles@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-13\",\"preferred_ocular_time\":\"12:00\"}', NULL, NULL, NULL, 2, '2026-02-11 10:44:15', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-13', NULL, NULL, NULL, NULL, '2026-02-09 07:00:49', '2026-02-11 12:03:51', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL),
(7, 'GI007', 15, 'beginner', NULL, 3, '2026-02-09 07:05:58', '12535.00', 'Ready for Installation', 'Partial', 'Card', '6, Noli Quen St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1105', '{\"contact_name\":\"Leonidas Opus Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"lerum.rommeljohnjeric.robles@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-17\",\"preferred_ocular_time\":\"13:00\"}', NULL, NULL, NULL, 2, '2026-02-09 09:23:36', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-17', '2026-02-17', NULL, NULL, NULL, '2026-02-09 07:05:58', '2026-02-11 12:03:51', 'Direct', 1, NULL, 2, NULL, NULL, '2026-02-09', '2026-02-14', NULL, NULL, 75, 'Ready', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL),
(8, 'GI008', 15, 'beginner', NULL, 3, '2026-02-09 17:32:42', '12535.00', 'In Fabrication', 'Pending', NULL, '6, Noli Quen St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1105', '{\"contact_name\":\"Leonidas Opus Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"lerum.rommeljohnjeric.robles@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-18\",\"preferred_ocular_time\":\"14:00\"}', NULL, NULL, NULL, 2, '2026-02-09 18:33:00', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-18', '2026-02-09', NULL, NULL, NULL, '2026-02-09 17:32:42', '2026-02-11 12:03:51', 'Direct', 1, NULL, 2, 4, NULL, '2026-02-09', '2026-02-14', '2026-02-09', NULL, 75, 'Ready', 'Issues: ', '0.00', '', 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL),
(9, 'GI009', 15, 'beginner', NULL, 3, '2026-02-09 18:39:22', '5000.00', 'Completed', 'Paid', NULL, '6, Noli Quen St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1105', '{\"contact_name\":\"Leonidas Opus Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"lerum.rommeljohnjeric.robles@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-24\",\"preferred_ocular_time\":\"14:00\"}', NULL, NULL, NULL, 2, '2026-02-09 19:39:42', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-24', '2026-02-09', NULL, '2026-02-13', NULL, '2026-02-09 18:39:22', '2026-02-11 12:03:51', 'Direct', 1, NULL, 2, 2, NULL, '2026-02-09', '2026-02-14', NULL, '2026-02-09', 100, 'Completed', 'Issues: ', '2.00', 'Gcash', 'Paid', NULL, 'pi_iYXuQWpYyTAyh4e7yBnKieBX', '500.00', 'Gcash', 'Paid', NULL, 'pi_ytuhFFoC5K2y518Z6Kv2XL4x', '2026-02-10', '2026-02-15', NULL, NULL, '', NULL, NULL, NULL),
(10, 'GI010', 15, 'beginner', NULL, 3, '2026-02-10 01:33:23', '6000.00', 'Completed', 'Paid', 'GCash', '6, Noli Quen St., San Antonio Subd., Quezon City, Metro Manila, Philippines, 1105', '{\"contact_name\":\"Leonidas Opus Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"lerum.rommeljohnjeric.robles@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-15\",\"preferred_ocular_time\":\"13:00\"}', NULL, NULL, NULL, 2, '2026-02-10 02:36:35', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-15', '2026-02-10', NULL, '2026-02-12', NULL, '2026-02-10 01:33:23', '2026-02-11 12:03:51', 'Direct', 1, NULL, 2, NULL, NULL, '2026-02-10', '2026-02-15', NULL, '2026-02-10', 100, 'Completed', NULL, '14.00', 'Credit/Debit Card', 'Paid', NULL, 'pi_2Kre42ksgzThqkzqLePe7Ztk', '600.00', 'Credit/Debit Card', 'Paid', NULL, 'pi_13pC8jPQYPV7LCtF9LkPmRy6', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL),
(11, 'GI011', 17, 'beginner', NULL, 3, '2026-02-10 01:56:34', '12000.00', 'Completed', 'Paid', 'GCash', '12, Sesame, San Antonio Subd., Caloocan, Metro Manila, Philippines, 1125', '{\"contact_name\":\"Arogela Robles Lerum\",\"contact_phone\":\"09120844695\",\"contact_email\":\"gitsquad5@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-14\",\"preferred_ocular_time\":\"10:00\"}', NULL, NULL, NULL, 2, '2026-02-10 02:57:28', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-14', '2026-02-10', NULL, '2026-02-12', NULL, '2026-02-10 01:56:34', '2026-02-11 12:03:51', 'Direct', 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, '2026-02-10', 100, 'Completed', NULL, '14.00', 'Credit/Debit Card', 'Paid', NULL, 'pi_W1v5qn6X4q4LX6aHpKd2WdNv', '1200.00', 'Maya', 'Paid', NULL, 'pi_LshAY1bCCRDsjPQ9eeCq4vg6', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL),
(12, 'GI012', 18, 'professional', NULL, 3, '2026-02-11 14:42:05', '4000.00', 'Completed', 'Paid', NULL, 'Tower 5, Trees Residence, Las Piñas, Metro Manila, Philippines, 1118', '{\"contact_name\":\"Kelly Jadaone Delos Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"davidmariakhellyc@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-16\",\"preferred_ocular_time\":\"11:00\"}', NULL, NULL, NULL, 2, '2026-02-11 15:48:35', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-16', '2026-02-11', NULL, '2026-02-13', NULL, '2026-02-11 14:42:05', '2026-02-11 15:03:42', 'Direct', 1, NULL, 2, 2, NULL, '2026-02-11', '2026-02-16', '2026-02-11', '2026-02-11', 100, 'Completed', 'Issues: ', '1.00', 'GCash', 'Paid', NULL, 'pi_pnBeNH2YpiqV7dz6QtQvj3zp', '400.00', 'Credit/Debit Card', 'Paid', NULL, 'pi_v7NhYc6obienNmkeckR9jgyi', '2026-02-11', '2026-02-16', NULL, NULL, '', NULL, NULL, NULL),
(13, 'GI013', 18, 'beginner', NULL, 3, '2026-02-11 21:25:47', '2535.00', 'Ocular Pending', 'Partial', 'Credit/Debit Card', 'Tower 5, Trees Residence, Las Piñas, Metro Manila, Philippines, 1118', '{\"contact_name\":\"Kelly Jadaone Delos Santos\",\"contact_phone\":\"09120844695\",\"contact_email\":\"davidmariakhellyc@gmail.com\",\"note\":\"\",\"preferred_ocular_date\":\"2026-02-16\",\"preferred_ocular_time\":\"12:00\"}', NULL, NULL, NULL, 2, '2026-02-11 22:26:07', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-16', NULL, NULL, NULL, NULL, '2026-02-11 21:25:47', '2026-02-11 22:50:39', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL),
(14, 'GI014', 1, 'beginner', NULL, 3, '2026-02-12 03:50:22', '10000.00', 'Ready for Installation', 'Partial', 'GCash', '35 Malasimbo, Quezon City, Metro Manila, Philippines, 1102', '{\"contact_name\":\"Aaron Gabriel M. Manantan\",\"contact_phone\":\"09937568011\",\"contact_email\":\"manantan.aro@gmail.com\",\"note\":\"test\",\"preferred_ocular_date\":\"2026-02-19\",\"preferred_ocular_time\":\"12:00\"}', NULL, NULL, NULL, 4, '2026-02-12 04:52:37', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-19', '2026-02-12', NULL, '2026-02-14', NULL, '2026-02-12 03:50:22', '2026-02-11 21:03:37', 'Direct', 1, NULL, 4, NULL, NULL, '2026-02-12', '2026-02-17', '2026-02-12', '2026-02-12', 100, 'Completed', 'Issues: \n\nIssues: ', '4.00', 'GCash', 'Paid', NULL, 'pi_uifwKykB87tkc2xwfqSozur8', '1000.00', 'GCash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, 'test', NULL, NULL, NULL),
(15, 'GI015', 1, 'beginner', NULL, 3, '2026-02-12 04:03:53', '10000.00', 'Completed', 'Partial', 'GCash', '35 Malasimbo, Quezon City, Metro Manila, Philippines, 1102', '{\"contact_name\":\"Aaron Gabriel M. Manantan\",\"contact_phone\":\"09937568011\",\"contact_email\":\"manantan.aro@gmail.com\",\"note\":\"test\",\"preferred_ocular_date\":\"2026-02-16\",\"preferred_ocular_time\":\"08:00\"}', NULL, NULL, NULL, 4, '2026-02-12 05:04:09', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-16', '2026-02-12', NULL, '2026-02-14', NULL, '2026-02-12 04:03:53', '2026-02-12 04:15:15', 'Direct', 1, NULL, 4, NULL, NULL, '2026-02-12', '2026-02-17', '2026-02-12', '2026-02-12', 100, 'Completed', 'Issues: ', '4.00', 'GCash', 'Paid', NULL, 'pi_751zzrEgJ6CBaKvL5PFV88MV', NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, 'test', NULL, NULL, NULL),
(16, 'GI016', 19, 'beginner', NULL, 3, '2026-02-12 04:19:56', '5000.00', 'Ready for Installation', 'Partial', 'GCash', 'Blk12 Lot23, Matthew, Estrella Homes, San Jose del Monte, Bulacan, Philippines, 3024', '{\"contact_name\":\"Aro Gab Manantan\",\"contact_phone\":\"09937569024\",\"contact_email\":\"aro.manantan@gmail.com\",\"note\":\"test\",\"preferred_ocular_date\":\"2026-02-16\",\"preferred_ocular_time\":\"12:00\"}', NULL, NULL, NULL, 4, '2026-02-12 05:20:29', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-16', '2026-02-12', NULL, '2026-02-14', NULL, '2026-02-12 04:19:56', '2026-02-12 04:28:14', 'Direct', 1, NULL, 4, 2, NULL, '2026-02-12', '2026-02-17', '2026-02-12', '2026-02-12', 100, 'Completed', 'Issues: ', '14.00', 'GCash', 'Paid', NULL, 'pi_R7EziDTPnvfV3XQTTpG7cfBH', '500.00', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, 'test', NULL, NULL, NULL),
(17, 'GI017', 19, 'professional', NULL, 3, '2026-02-12 05:03:09', '200.00', 'Completed', 'Paid', 'GCash', 'Blk12 Lot23, Matthew, Estrella Homes, Angat, Bulacan, Philippines, 3024', '{\"contact_name\":\"Aro Gab Manantan\",\"contact_phone\":\"09937569024\",\"contact_email\":\"aro.manantan@gmail.com\",\"note\":\"test\",\"preferred_ocular_date\":\"2026-02-17\",\"preferred_ocular_time\":\"11:00\"}', NULL, NULL, NULL, 4, '2026-02-12 06:05:58', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-17', '2026-02-12', NULL, '2026-02-14', NULL, '2026-02-12 05:03:09', '2026-02-12 05:20:18', 'Direct', 1, NULL, 4, 2, NULL, '2026-02-12', '2026-02-17', NULL, '2026-02-12', 100, 'Completed', 'Issues: ', '80.00', 'GCash', 'Paid', NULL, 'pi_ZXq9vHyTQf5hXF8wdB8Nx4mD', '20.00', 'GCash', 'Paid', NULL, 'pi_RVeTGzE4SNR5BYQnpKUUmC16', '2026-02-12', '2026-02-17', NULL, NULL, 'test', NULL, NULL, NULL),
(18, 'GI018', 19, 'professional', NULL, 3, '2026-02-12 05:21:13', '2000.00', 'Ready for Installation', 'Partial', 'GCash', 'Blk12 Lot23, Matthew, Estrella Homes, San Jose del Monte, Bulacan, Philippines, 3024', '{\"contact_name\":\"Aro Gab Manantan\",\"contact_phone\":\"09937569024\",\"contact_email\":\"aro.manantan@gmail.com\",\"note\":\"test\",\"preferred_ocular_date\":\"2026-02-16\",\"preferred_ocular_time\":\"12:00\"}', NULL, NULL, NULL, 4, '2026-02-12 06:21:48', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-16', '2026-02-12', NULL, '2026-02-14', NULL, '2026-02-12 05:21:13', '2026-02-12 05:27:43', 'Direct', 1, NULL, 4, NULL, NULL, '2026-02-12', '2026-02-17', NULL, '2026-02-12', 100, 'Completed', NULL, '14.00', 'GCash', 'Paid', NULL, 'pi_awq6zDfwag9gtXtm1XwpVJDo', '200.00', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, 'test', NULL, NULL, NULL),
(19, 'GI019', 19, 'professional', NULL, 3, '2026-02-12 05:32:57', '35.00', 'Completed', 'Paid', 'GCash', 'Blk12 Lot23, Matthew, Estrella Homes, San Jose del Monte, Bulacan, Philippines, 3024', '{\"contact_name\":\"Aro Gab Manantan\",\"contact_phone\":\"09937569024\",\"contact_email\":\"aro.manantan@gmail.com\",\"note\":\"test\",\"preferred_ocular_date\":\"2026-02-16\",\"preferred_ocular_time\":\"11:00\"}', NULL, NULL, NULL, 4, '2026-02-12 06:33:16', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-16', '2026-02-12', NULL, '2026-02-14', NULL, '2026-02-12 05:32:57', '2026-02-12 05:37:43', 'Direct', 1, NULL, 4, 2, NULL, '2026-02-12', '2026-02-17', NULL, '2026-02-12', 100, 'Completed', 'Issues: ', '14.00', 'GCash', 'Paid', NULL, 'pi_UuqvMAHDMJBtJZotUx8dG8Py', '3.50', 'GCash', 'Paid', NULL, 'pi_y9XQBsVD8MrbXSLQNKSuevD8', NULL, NULL, NULL, NULL, 'test', NULL, NULL, NULL),
(20, 'GI020', 19, 'professional', NULL, 3, '2026-02-12 06:30:14', '12000.00', 'Ocular Pending', 'Pending', NULL, 'Blk12 Lot23, Matthew, Estrella Homes, San Jose del Monte, Bulacan, Philippines, 3024', '{\"contact_name\":\"Aro Gab Manantan\",\"contact_phone\":\"09937569024\",\"contact_email\":\"aro.manantan@gmail.com\",\"note\":\"test\",\"preferred_ocular_date\":\"2026-02-16\",\"preferred_ocular_time\":\"13:00\"}', NULL, NULL, NULL, 4, '2026-02-12 07:31:30', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-16', '2026-02-16', NULL, NULL, NULL, '2026-02-12 06:30:14', '2026-02-12 06:32:04', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, 'test', NULL, NULL, NULL),
(21, 'GI021', 19, 'professional', NULL, 3, '2026-02-12 07:04:50', '35.00', 'Ocular Pending', 'Pending', NULL, 'Blk12 Lot23, Matthew, Estrella Homes, San Jose del Monte, Bulacan, Philippines, 3024', '{\"contact_name\":\"Aro Gab Manantan\",\"contact_phone\":\"09937569024\",\"contact_email\":\"aro.manantan@gmail.com\",\"note\":\"test\",\"preferred_ocular_date\":\"2026-02-16\",\"preferred_ocular_time\":\"11:00\"}', NULL, NULL, NULL, 4, '2026-02-12 08:07:24', NULL, NULL, NULL, NULL, 0, NULL, '2026-02-16', NULL, NULL, NULL, NULL, '2026-02-12 07:04:50', '2026-02-12 07:07:24', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, 'test', NULL, NULL, NULL);

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
  `Customization` longtext DEFAULT NULL COMMENT 'JSON field for dynamic product customization',
  `DesignRef` varchar(255) DEFAULT NULL,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`OrderItemID`, `OrderID`, `Product_ID`, `CustomizationID`, `Quantity`, `UnitPrice`, `EstimatePrice`, `Dimensions`, `GlassShape`, `GlassType`, `GlassThickness`, `EdgeWork`, `FrameType`, `Engraving`, `Customization`, `DesignRef`, `Created_Date`) VALUES
(1, 1, 22, NULL, 1, '0.00', '12500.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-09 04:09:11'),
(2, 2, 18, NULL, 1, '0.00', '6500.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-09 04:17:39'),
(3, 3, 22, NULL, 1, '0.00', '12500.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-09 04:36:13'),
(4, 4, 22, NULL, 1, '0.00', '12500.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-09 04:36:50'),
(5, 5, 22, NULL, 0, '0.00', '0.00', 'in x in', '', '', '', '', '', '', NULL, NULL, '2026-02-09 05:43:06'),
(6, 6, 23, NULL, 1, '0.00', '9500.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-09 07:00:49'),
(7, 7, 22, NULL, 3, '0.00', '0.00', '51cm x 51cm', '', '', '', '', '', '', '{\"_width\":51,\"_height\":51,\"_unit\":\"cm\",\"numberOfPanels\":\"2 Panels\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"trackSystem\":\"2 Tracks\",\"transomType\":\"Fixed Transom Head (Fixed glass at top)\",\"frameColor\":\"Analok\",\"glassType\":\"Tempered\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Flushlok #12\",\"rollerType\":\"Blue Single Roller\",\"width\":\"51\",\"height\":\"51\",\"unit\":\"cm\"}', NULL, '2026-02-09 07:05:58'),
(8, 8, 22, NULL, 1, '0.00', '0.00', '70cm x 80cm', '', '', '', '', '', '', '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"Fixed Transom Head (Fixed glass at top)\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"F | S (Fixed | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Tempered\",\"glassColor\":\"Clear\",\"glassThickness\":\"8mm\",\"lockType\":\"Durable Flushlok\",\"rollerType\":\"Single Panel Roller\",\"width\":\"70\",\"height\":\"80\",\"unit\":\"cm\"}', NULL, '2026-02-09 17:32:42'),
(9, 9, 18, NULL, 1, '0.00', '0.00', '45cm x 46cm', '', '', '', '', '', '', '{\"shape\":\"Round\",\"edgeFinish\":\"Beveled\",\"mountingMethod\":\"Wall-mounted\",\"thicknessmm\":\"8mm\",\"width\":\"45\",\"height\":\"46\",\"unit\":\"cm\",\"_width\":45,\"_height\":46,\"_unit\":\"cm\"}', NULL, '2026-02-09 18:39:23'),
(10, 10, 22, NULL, 1, '0.00', '0.00', '50cm x 50cm', '', '', '', '', '', '', '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"Fixed Transom Head (Fixed glass at top)\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"F | S (Fixed | Sliding)\",\"frameColor\":\"Analok\",\"glassType\":\"Ordinary\",\"glassColor\":\"Frosted\",\"glassThickness\":\"10mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"width\":\"50\",\"height\":\"50\",\"unit\":\"cm\",\"_width\":50,\"_height\":50,\"_unit\":\"cm\"}', NULL, '2026-02-10 01:33:23'),
(11, 11, 22, NULL, 1, '0.00', '0.00', '60cm x 60cm', '', '', '', '', '', '', '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"Fixed Transom Head (Fixed glass at top)\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"8mm\",\"lockType\":\"Durable Flushlok\",\"rollerType\":\"Blue Single Roller\",\"width\":\"60\",\"height\":\"60\",\"unit\":\"cm\",\"_width\":60,\"_height\":60,\"_unit\":\"cm\"}', NULL, '2026-02-10 01:56:34'),
(12, 12, 7, 31, 1, '5000.00', '5000.00', '45in x 35in', '', 'Tempered', '12mm', '', '', 'None', '{\"glassType\":\"Tempered\",\"glassColor\":\"Clear\",\"frameColor\":\"Analok\",\"operation\":\"Awning (push-out)\",\"openingDirection\":\"Top-hinged\",\"thickness\":\"12mm\",\"screen\":\"Without Screen\",\"shape\":\"Custom shapes\",\"edgeFinish\":\"Raw\",\"mountingMethod\":\"Stand\",\"cornerRadius\":0,\"cornerRadius_unit\":\"in\",\"_width\":45,\"_height\":35,\"_unit\":\"in\",\"width\":\"45\",\"height\":\"35\",\"unit\":\"in\"}', 'uploads/designs/design_18_1770820884_698c951451a58.png', '2026-02-11 14:42:05'),
(13, 13, 1, NULL, 1, '0.00', '2500.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-11 21:25:47'),
(14, 14, 22, NULL, 1, '0.00', '0.00', '100in x 100in', '', '', '', '', '', '', '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"Fixed Transom Head (Fixed glass at top)\",\"trackSystem\":\"2 Tracks\",\"frameColor\":\"Wood Finish\",\"glassType\":\"Tempered\",\"glassColor\":\"Smoked\",\"glassThickness\":\"10mm\",\"rollerType\":\"Single Panel Roller\",\"lockType\":\"Center Lok 904 Big\",\"width\":\"100\",\"height\":\"100\",\"unit\":\"in\"}', NULL, '2026-02-12 03:50:22'),
(15, 15, 18, NULL, 1, '0.00', '0.00', '45in x 35in', '', '', '', '', '', '', '{\"shape\":\"Rectangle\",\"edgeFinish\":\"Beveled\",\"mountingMethod\":\"Wall-mounted\",\"thicknessmm\":\"6mm\",\"width\":\"45\",\"height\":\"35\",\"unit\":\"in\",\"_width\":45,\"_height\":35,\"_unit\":\"in\"}', NULL, '2026-02-12 04:03:53'),
(16, 16, 18, NULL, 1, '0.00', '0.00', '25in x 25in', '', '', '', '', '', '', '{\"shape\":\"Rectangle\",\"edgeFinish\":\"Beveled\",\"mountingMethod\":\"Wall-mounted\",\"thicknessmm\":\"6mm\",\"width\":\"25\",\"height\":\"25\",\"unit\":\"in\"}', NULL, '2026-02-12 04:19:56'),
(17, 17, 22, 32, 1, '0.00', '0.00', '45in x 35in', '', 'Frosted', '', '', '', 'None', '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Wood Finish\",\"glassType\":\"Frosted\",\"glassColor\":\"Smoked\",\"glassThickness\":\"8mm\",\"lockType\":\"Durable Flushlok\",\"rollerType\":\"Blue Single Roller\",\"_width\":45,\"_height\":35,\"_unit\":\"in\",\"width\":\"45\",\"height\":\"35\",\"unit\":\"in\"}', 'uploads/designs/design_19_1770872567_698d5ef752bf4.png', '2026-02-12 05:03:09'),
(18, 18, 22, 33, 1, '0.00', '0.00', '45in x 35in', '', '', '', '', '', 'None', '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"_width\":45,\"_height\":35,\"_unit\":\"in\",\"width\":\"45\",\"height\":\"35\",\"unit\":\"in\"}', 'uploads/designs/design_19_1770873651_698d633360728.png', '2026-02-12 05:21:13'),
(19, 19, 22, 34, 1, '0.00', '0.00', '45in x 35in', '', '', '', '', '', 'None', '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"_width\":45,\"_height\":35,\"_unit\":\"in\",\"width\":\"45\",\"height\":\"35\",\"unit\":\"in\"}', 'uploads/designs/design_19_1770874346_698d65ea2ea7b.png', '2026-02-12 05:32:57'),
(20, 20, 21, 35, 1, '0.00', '0.00', '45in x 35in', '', '', '', '', '', 'None', '{\"numberOfPanels\":\"2 Panels\",\"transomType\":\"None\",\"trackSystem\":\"2 Tracks\",\"panelConfiguration\":\"S | S (Sliding | Sliding)\",\"frameColor\":\"Powder Coated White\",\"glassType\":\"Ordinary\",\"glassColor\":\"Clear\",\"glassThickness\":\"6mm\",\"lockType\":\"Center Lok 904 Big\",\"rollerType\":\"Single Panel Roller\",\"_width\":45,\"_height\":35,\"_unit\":\"in\",\"width\":\"45\",\"height\":\"35\",\"unit\":\"in\"}', 'uploads/designs/design_19_1770877746_698d73324fc68.png', '2026-02-12 06:30:14'),
(21, 21, 21, 37, 1, '10000.00', '0.00', '45in x 35in', NULL, 'Ordinary', NULL, NULL, 'Powder Coated White', 'None', NULL, 'uploads/designs/design_19_1770879873_698d7b8128b4f.png', '2026-02-12 07:04:50');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_ID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `CustomerName` varchar(255) DEFAULT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL,
  `payment_milestone` enum('ocular_50','fabrication_40','installation_10') DEFAULT NULL,
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

INSERT INTO `payment` (`Payment_ID`, `OrderID`, `CustomerName`, `ProductName`, `PaymentMethod`, `payment_milestone`, `Amount`, `Payment_Date`, `Transaction_ID`, `PaymentIntentID`, `ReceiptPath`, `Status`, `billing_name`, `billing_email`, `billing_phone`, `billing_unit`, `billing_street`, `billing_subdivision`, `billing_barangay`, `billing_city`, `billing_province`, `billing_region`, `billing_postal_code`, `billing_country`, `shipping_name`, `shipping_email`, `shipping_phone`, `shipping_unit`, `shipping_street`, `shipping_subdivision`, `shipping_barangay`, `shipping_city`, `shipping_province`, `shipping_region`, `shipping_postal_code`, `shipping_country`, `billing_country_iso`, `billing_payload_json`, `billing_firstname`, `billing_lastname`, `billing_unit_house_number`, `billing_zipcode`) VALUES
(1, 5, NULL, NULL, 'Cash', 'fabrication_40', '0.00', '2026-02-11 22:43:28', NULL, NULL, 'uploads/payments/ocular/status1.png', 'Pending', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(2, 7, NULL, NULL, 'Card', 'fabrication_40', '6000.00', '2026-02-11 22:43:28', 'pi_rjeA4c9RaGZQikPKTAPr63CL', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(3, 8, NULL, NULL, 'Cash', 'fabrication_40', '5500.00', '2026-02-11 22:43:28', NULL, NULL, 'uploads/payments/ocular/Gitsquad_SSYADD1_MSYADD1-rubric-template-Sept2023_(1)5.pdf', 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(4, 9, NULL, NULL, 'Cash', 'installation_10', '2500.00', '2026-02-11 22:43:28', NULL, NULL, 'uploads/payments/ocular/ChatGPT_Image_Jan_27,_2026,_01_18_42_PM.png', 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(5, 10, NULL, NULL, 'GCash', 'installation_10', '3000.00', '2026-02-11 22:43:28', 'pi_VM2fRqfRzG1iHYXjHLcp9Bcn', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(6, 11, NULL, NULL, 'GCash', 'installation_10', '6000.00', '2026-02-11 22:43:28', 'pi_mg8GA18PUPRB4PUGDEVNN6c5', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(7, 12, NULL, NULL, 'Cash', 'installation_10', '2000.00', '2026-02-11 22:43:28', NULL, NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(8, 13, ' ', NULL, 'Credit/Debit Card', NULL, '1267.50', '2026-02-11 15:50:39', 'pi_1ZSBagQwQGEa1pXTFN3XEUfm', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(9, 14, NULL, NULL, 'GCash', NULL, '5000.00', '2026-02-11 20:57:41', 'pi_3p7ztzYEGdwfXCSmwt77haLK', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(10, 15, NULL, NULL, 'GCash', NULL, '5000.00', '2026-02-11 21:06:38', 'pi_Lp6c5SJK8ubua3975pW84kCU', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(11, 16, NULL, NULL, 'GCash', NULL, '2500.00', '2026-02-11 21:23:22', 'pi_AzTDBWRQxokApNf7joCck6jy', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(12, 17, NULL, NULL, 'GCash', NULL, '100.00', '2026-02-11 22:16:16', 'pi_Gf1dyoHmjsGnJmPNCE7rZTgD', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(13, 18, NULL, NULL, 'GCash', NULL, '1000.00', '2026-02-11 22:24:49', 'pi_Nrc6CNWrTZmFarDkdFgJA12b', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(14, 19, NULL, NULL, 'GCash', NULL, '17.50', '2026-02-11 22:35:30', 'pi_CxYtGGJQgiAKFEgn5pR9f1mz', NULL, NULL, 'Paid', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL),
(15, 20, NULL, NULL, NULL, NULL, '6000.00', '2026-02-12 06:32:04', NULL, NULL, NULL, 'Pending', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL);

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
(1, '798 Series Sliding Window', 'Windows', 'Glass', '2500.00', '[\"3dc4bbffed4a4d049b2e642d152a3356.jpg\",\"12cf08a1163f7bec672e9706f1c4108d.jpg\",\"1571d6c0f7c4506e106af9a303bf6209.jpg\",\"2faccbfe12ef665850eba581b7e6821e.jpg\"]', 'Product Description\r\nSliding windows are designed for smooth operation, space efficiency, and modern aesthetics. Built with durable aluminum frames and customizable glass options, they provide excellent ventilation while maintaining a clean and elegant look for residential and commercial spaces.\r\n\r\nKey Features\r\nSmooth sliding operation with heavy-duty rollers\r\nSpace-saving design\r\nCustom panel configurations\r\nOptional fixed transom (top or bottom)\r\nOptional insect screen\r\nStrong aluminum frame with multiple finish options\r\nSpecifications\r\n\r\nNumber of Panels: 2 or 4 panels\r\nTrack System: 2 or 3 tracks\r\nPanel Configuration: Sliding / Fixed combinations\r\nFrame Color: White, Analok, Matte Gray, Matte Black, Wood Finish\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted, Smoked\r\nGlass Thickness: 6mm\r\nLock Type: Multiple lock options available\r\nRoller Type: Single or double roller system', '2026-01-27 10:28:22', 'Available', 'Sliding', 'direct', '2000.00', '3000.00', '{\"numberOfPanels\":[\"2 Panels\",\"4 Panels\"],\"transomType\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\"],\"trackSystem\":[\"2 Tracks\",\"3 Tracks\"],\"panelConfiguration\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"glassThickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"lockType\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"rollerType\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', '798 Series'),
(2, '900 Series Sliding Window', 'Windows', 'Glass', '4000.00', '[\"d52e91561fb8b93f4fd65f338833685a.jpg\",\"3aa2b3620fc3fce26a6a1554db62ef56.jpg\",\"b6d6c2be7054eba505cc28f0de01c435.jpg\",\"e1ab5c1daf98ff7a614519ca55df529b.jpg\"]', 'Product Description\r\nSliding windows are designed for smooth operation, space efficiency, and modern aesthetics. Built with durable aluminum frames and customizable glass options, they provide excellent ventilation while maintaining a clean and elegant look for residential and commercial spaces.\r\n\r\nKey Features\r\nSmooth sliding operation with heavy-duty rollers\r\nSpace-saving design\r\nCustom panel configurations\r\nOptional fixed transom (top or bottom)\r\nOptional insect screen\r\nStrong aluminum frame with multiple finish options\r\nSpecifications\r\n\r\nNumber of Panels: 2 or 4 panels\r\nTrack System: 2 or 3 tracks\r\nPanel Configuration: Sliding / Fixed combinations\r\nFrame Color: White, Analok, Matte Gray, Matte Black, Wood Finish\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted, Smoked\r\nGlass Thickness: 6mm – 8mm\r\nLock Type: Multiple lock options available\r\nRoller Type: Single or double roller system', '2026-01-27 10:29:51', 'Available', 'Sliding', 'direct', '3000.00', '5000.00', '{\"numberOfPanels\":[\"2 Panels\",\"4 Panels\"],\"transomType\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\"],\"trackSystem\":[\"2 Tracks\",\"3 Tracks\"],\"panelConfiguration\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"glassThickness\":[\"6mm\",\"8mm\"],\"lockType\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"rollerType\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', '900 Series'),
(3, '868 Series Sliding Window', 'Windows', 'Glass', '5000.00', '[\"fbe233ef331b7a274912a509f48238bc.jpg\",\"b532cc35ba056891a9ad8d497f842e5a.jpg\",\"9847336752e0d2bf1010e3b58840c2d7.jpg\",\"aa9f9eba005cee29f3c3eeded896fb6e.jpg\"]', 'Product Description\r\nSliding windows are designed for smooth operation, space efficiency, and modern aesthetics. Built with durable aluminum frames and customizable glass options, they provide excellent ventilation while maintaining a clean and elegant look for residential and commercial spaces.\r\n\r\nKey Features\r\nSmooth sliding operation with heavy-duty rollers\r\nSpace-saving design\r\nCustom panel configurations\r\nOptional fixed transom (top or bottom)\r\nOptional insect screen\r\nStrong aluminum frame with multiple finish options\r\nSpecifications\r\n\r\nNumber of Panels: 2 or 4 panels\r\nTrack System: 2 or 3 tracks\r\nPanel Configuration: Sliding / Fixed combinations\r\nFrame Color: White, Analok, Matte Gray, Matte Black, Wood Finish\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted, Smoked\r\nGlass Thickness: 6mm – 12mm\r\nLock Type: Multiple lock options available\r\nRoller Type: Single or double roller system', '2026-01-27 10:31:44', 'Available', 'Sliding', 'direct', '4000.00', '6000.00', '{\"numberOfPanels\":[\"2 Panels\",\"4 Panels\"],\"transomType\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\"],\"trackSystem\":[\"2 Tracks\",\"3 Tracks\"],\"panelConfiguration\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"glassThickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"lockType\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"rollerType\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', '900 Series'),
(4, '130 Series Sliding Window', 'Windows', 'Glass', '6000.00', '[\"c75d04a5c5da230cec802108ac4c771a.jpg\",\"0070987d24e7c87bd317ac520a9872f5.jpg\"]', 'Product Description\r\nSliding windows are designed for smooth operation, space efficiency, and modern aesthetics. Built with durable aluminum frames and customizable glass options, they provide excellent ventilation while maintaining a clean and elegant look for residential and commercial spaces.\r\n\r\nKey Features\r\nSmooth sliding operation with heavy-duty rollers\r\nSpace-saving design\r\nCustom panel configurations\r\nOptional fixed transom (top or bottom)\r\nOptional insect screen\r\nStrong aluminum frame with multiple finish options\r\nSpecifications\r\n\r\nNumber of Panels: 2 or 4 panels\r\nTrack System: 2 or 3 tracks\r\nPanel Configuration: Sliding / Fixed combinations\r\nFrame Color: White, Analok, Matte Gray, Matte Black, Wood Finish\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted, Smoked\r\nGlass Thickness: 6mm – 12mm\r\nLock Type: Multiple lock options available\r\nRoller Type: Single or double roller system', '2026-01-27 10:34:31', 'Available', 'Sliding', 'direct', '5000.00', '7000.00', '{\"numberOfPanels\":[\"2 Panels\",\"4 Panels\"],\"transomType\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\"],\"trackSystem\":[\"2 Tracks\",\"3 Tracks\"],\"panelConfiguration\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"glassThickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"lockType\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"rollerType\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', '798 Series'),
(5, '38 Series Awning Window', 'Windows', 'Glass', '2500.00', '[\"2c2d328799986af5c48a4f5ff6fecefe.jpg\",\"1feeec44c1bb6709c75ef55d9e589aff.jpg\",\"d992eabb0dfde42c2ed27871fc45d03b.jpg\",\"da71a6247b685b61f0b97ff4af449f34.jpg\"]', 'Product Description\r\nAwning windows are top-hinged windows that open outward, allowing airflow even during light rain. Ideal for bathrooms, kitchens, and ventilation areas, they offer both functionality and durability.\r\n\r\nKey Features\r\n\r\nTop-hinged opening design\r\nExcellent ventilation and weather protection\r\nManual push-out or crank-out operation\r\nSuitable for single or multi-panel layouts\r\nOptional insect screen\r\n\r\nSpecifications\r\n\r\nSeries: 38, 50, 60, 75, 85 Series\r\nOperation: Push-out or crank-out\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted, Smoked\r\nFrame Finish: White, Analok, Matte Gray, Matte Black, Wood Finish\r\nThickness: 6mm\r\nConfiguration: Single or multiple panels', '2026-01-27 10:36:55', 'Available', 'Awning', 'direct', '2000.00', '3000.00', '{\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Frosted\",\"Smoked\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"operation\":[\"Awning (crank-out)\",\"Awning (push-out)\"],\"openingDirection\":[\"Top-hinged\"],\"thickness\":[\"6mm\"],\"screen\":[\"Without Screen\",\"With Screen\"]}', '798 Series'),
(6, '50 Series Awning Window', 'Windows', 'Glass', '3500.00', '[\"c719a0e70a438231abadc821af50b582.jpg\",\"790c4ed257f16f598aef2b5eda4824e6.jpg\"]', 'Product Description\r\nAwning windows are top-hinged windows that open outward, allowing airflow even during light rain. Ideal for bathrooms, kitchens, and ventilation areas, they offer both functionality and durability.\r\n\r\nKey Features\r\n\r\nTop-hinged opening design\r\nExcellent ventilation and weather protection\r\nManual push-out or crank-out operation\r\nSuitable for single or multi-panel layouts\r\nOptional insect screen\r\n\r\nSpecifications\r\n\r\nSeries: 38, 50, 60, 75, 85 Series\r\nOperation: Push-out or crank-out\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted, Smoked\r\nFrame Finish: White, Analok, Matte Gray, Matte Black, Wood Finish\r\nThickness: 6mm – 8mm\r\nConfiguration: Single or multiple panels', '2026-01-27 10:39:33', 'Available', 'Awning', 'direct', '3000.00', '4000.00', '{\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"operation\":[\"Awning (crank-out)\",\"Awning (push-out)\"],\"openingDirection\":[\"Top-hinged\"],\"thickness\":[\"6mm\",\"8mm\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', '798 Series'),
(7, '60 Series Awning Window', 'Windows', 'Glass', '5000.00', '[\"0b7ed7e3a2b7ecd28eae4dc9a293edec.jpg\",\"ad552079987350e544c4d99dbfce10eb.jpg\"]', 'Product Description\r\nAwning windows are top-hinged windows that open outward, allowing airflow even during light rain. Ideal for bathrooms, kitchens, and ventilation areas, they offer both functionality and durability.\r\n\r\nKey Features\r\n\r\nTop-hinged opening design\r\nExcellent ventilation and weather protection\r\nManual push-out or crank-out operation\r\nSuitable for single or multi-panel layouts\r\nOptional insect screen\r\n\r\nSpecifications\r\n\r\nSeries: 38, 50, 60, 75, 85 Series\r\nOperation: Push-out or crank-out\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted, Smoked\r\nFrame Finish: White, Analok, Matte Gray, Matte Black, Wood Finish\r\nThickness: 6mm – 12mm\r\nConfiguration: Single or multiple panels', '2026-01-27 10:40:41', 'Available', 'Awning', 'direct', '4000.00', '6000.00', '{\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"operation\":[\"Awning (crank-out)\",\"Awning (push-out)\"],\"openingDirection\":[\"Top-hinged\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', '900 Series'),
(8, '85 Series Awning Window', 'Windows', 'Glass', '7000.00', '[\"16d9bf39020c10d16ce5f685722b1429.jpg\",\"247c12f7b0da2c81c2b99cbedd169f5d.jpg\"]', 'Product Description\r\nAwning windows are top-hinged windows that open outward, allowing airflow even during light rain. Ideal for bathrooms, kitchens, and ventilation areas, they offer both functionality and durability.\r\n\r\nKey Features\r\n\r\nTop-hinged opening design\r\nExcellent ventilation and weather protection\r\nManual push-out or crank-out operation\r\nSuitable for single or multi-panel layouts\r\nOptional insect screen\r\n\r\nSpecifications\r\n\r\nSeries: 38, 50, 60, 75, 85 Series\r\nOperation: Push-out or crank-out\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted, Smoked\r\nFrame Finish: White, Analok, Matte Gray, Matte Black, Wood Finish\r\nThickness: 6mm – 12mm\r\nConfiguration: Single or multiple panels', '2026-01-27 10:42:07', 'Available', 'Awning', 'direct', '6000.00', '8000.00', '{\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"operation\":[\"Awning (crank-out)\",\"Awning (push-out)\"],\"openingDirection\":[\"Top-hinged\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', '798 Series'),
(9, '75 Series Awning Window', 'Windows', 'Glass', '8500.00', '[\"da913a11f7d8f8791dbeeae035df81b8.jpg\",\"df3ca58fb766c359e5dbcad5d3582e5d.jpg\"]', 'Product Description\r\nAwning windows are top-hinged windows that open outward, allowing airflow even during light rain. Ideal for bathrooms, kitchens, and ventilation areas, they offer both functionality and durability.\r\n\r\nKey Features\r\n\r\nTop-hinged opening design\r\nExcellent ventilation and weather protection\r\nManual push-out or crank-out operation\r\nSuitable for single or multi-panel layouts\r\nOptional insect screen\r\n\r\nSpecifications\r\n\r\nSeries: 38, 50, 60, 75, 85 Series\r\nOperation: Push-out or crank-out\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted, Smoked\r\nFrame Finish: White, Analok, Matte Gray, Matte Black, Wood Finish\r\nThickness: 6mm – 12mm\r\nConfiguration: Single or multiple panels', '2026-01-27 10:43:22', 'Available', 'Awning', 'direct', '7000.00', '10000.00', '{\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"operation\":[\"Awning (crank-out)\",\"Awning (push-out)\"],\"openingDirection\":[\"Top-hinged\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"screen\":[\"With Screen\",\"Without Screen\"]}', '798 Series'),
(10, '38 Series Casement Window', 'Windows', 'Glass', '2500.00', '[\"de8ad9b07648f4eab2b6be0a0a32a760.jpg\",\"f21ca6031784fc8d0c177fa429c49d5c.jpg\"]', 'Product Description\r\nCasement windows open outward using side hinges, offering maximum ventilation and a tight seal when closed. Ideal for modern homes requiring both airflow and energy efficiency.\r\n\r\nKey Features\r\n\r\nSide-hinged opening\r\nStrong locking system\r\nWide opening for better airflow\r\nOptional transom configurations\r\nDurable aluminum framing\r\n\r\nSpecifications\r\n\r\nPanel Configuration: 1–6 panels\r\nTransom Type: None, FTH, FTS\r\nFrame Color: Hanalok, Black, White, Gray, Wood Finish\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted/Smoked\r\nThickness: 6mm', '2026-01-27 10:46:02', 'Available', 'Casement', 'direct', '2000.00', '3000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"thickness\":[\"6mm\"]}', 'YC-38 Series'),
(11, '50 Series Casement Window', 'Windows', 'Glass', '4000.00', '[\"f37eeda74bb85c923665eba00c87d75a.jpg\",\"9984f61d7f4e84be74dcad01a07ac925.jpg\"]', 'Product Description\r\nCasement windows open outward using side hinges, offering maximum ventilation and a tight seal when closed. Ideal for modern homes requiring both airflow and energy efficiency.\r\n\r\nKey Features\r\n\r\nSide-hinged opening\r\nStrong locking system\r\nWide opening for better airflow\r\nOptional transom configurations\r\nDurable aluminum framing\r\n\r\nSpecifications\r\n\r\nPanel Configuration: 1–6 panels\r\nTransom Type: None, FTH, FTS\r\nFrame Color: Hanalok, Black, White, Gray, Wood Finish\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted/Smoked\r\nThickness: 6mm – 8mm', '2026-01-27 10:48:52', 'Available', 'Casement', 'direct', '3000.00', '5000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\", \"Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"thickness\":[\"6mm\",\"8mm\"]}', 'YC-50 Series'),
(12, '60 Series Casement Window', 'Windows', 'Glass', '6000.00', '[\"47001e557aef43d954520148cfc01fdd.jpg\",\"9eedab325cdd2b39ef5c456db1793698.jpg\"]', 'Product Description\r\nCasement windows open outward using side hinges, offering maximum ventilation and a tight seal when closed. Ideal for modern homes requiring both airflow and energy efficiency.\r\n\r\nKey Features\r\n\r\nSide-hinged opening\r\nStrong locking system\r\nWide opening for better airflow\r\nOptional transom configurations\r\nDurable aluminum framing\r\n\r\nSpecifications\r\n\r\nPanel Configuration: 1–6 panels\r\nTransom Type: None, FTH, FTS\r\nFrame Color: Hanalok, Black, White, Gray, Wood Finish\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted/Smoked\r\nThickness: 6mm – 12mm', '2026-01-27 10:50:12', 'Available', 'Casement', 'direct', '5000.00', '7000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"]}', '60-DMX Series'),
(13, '85 Series Casement Window', 'Windows', 'Glass', '7000.00', '[\"16a9b1d380a1ee06d4b4b3fde9ad5622.jpg\"]', 'Product Description\r\nCasement windows open outward using side hinges, offering maximum ventilation and a tight seal when closed. Ideal for modern homes requiring both airflow and energy efficiency.\r\n\r\nKey Features\r\n\r\nSide-hinged opening\r\nStrong locking system\r\nWide opening for better airflow\r\nOptional transom configurations\r\nDurable aluminum framing\r\n\r\nSpecifications\r\n\r\nPanel Configuration: 1–6 panels\r\nTransom Type: None, FTH, FTS\r\nFrame Color: Hanalok, Black, White, Gray, Wood Finish\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted/Smoked\r\nThickness: 6mm – 12mm', '2026-01-27 10:51:11', 'Available', 'Casement', 'direct', '6000.00', '8000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"]}', '85 Series'),
(14, '75 Series Casement Window', 'Windows', 'Glass', '10500.00', '[\"e563312ee466629a27022951f94976fa.jpg\",\"a40b495870dc06c56f2a5cfe37201e93.jpg\"]', 'Product Description\r\nCasement windows open outward using side hinges, offering maximum ventilation and a tight seal when closed. Ideal for modern homes requiring both airflow and energy efficiency.\r\n\r\nKey Features\r\n\r\nSide-hinged opening\r\nStrong locking system\r\nWide opening for better airflow\r\nOptional transom configurations\r\nDurable aluminum framing\r\n\r\nSpecifications\r\n\r\nPanel Configuration: 1–6 panels\r\nTransom Type: None, FTH, FTS\r\nFrame Color: Hanalok, Black, White, Gray, Wood Finish\r\nGlass Type: Ordinary, Tempered, Reflective\r\nGlass Color: Clear, Bronze, Frosted/Smoked\r\nThickness: 6mm – 12mm', '2026-01-27 10:52:11', 'Available', 'Casement', 'direct', '9000.00', '12000.00', '{\"transomType\":[\"Casement w/FTH\",\"Casement w/FTS\",\"None\"],\"panelConfiguration\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\"],\"frameColor\":[\"Hanalok\",\"Black\",\"White\",\"Gray\",\"Wood Finish\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"thickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"]}', '75 Series'),
(15, 'Rectangle/ Square Framed Mirror', 'Mirrors & Specialty Glass', 'Glass', '7500.00', '[\"6ffafdf605b686c1a793df32e816c460.jpg\",\"1b1fcd649f003a53c5434abc8f7e90af.jpg\"]', NULL, '2026-01-27 10:57:09', 'Available', 'Mirrors', 'direct', '5000.00', '10000.00', '{\"shape\":[\"Rectangle\",\"Square\"],\"cornerRadius\":\"\",\"frameType\":[\"Framed\"],\"frameColor\":[\"White\",\"Black\",\"Gold\",\"Machine Polished Edges\",\"Beveled Edge\"],\"glassType\":[\"Copper Free & Lead Free Mirror\"],\"thickness\":[\"6mm\"],\"mountingMethod\":[\"Wall-mounted\",\"Stand\",\"Adhesive\",\"Leaning\"]}', 'Mirror'),
(16, 'Rectangle/ Square Frameless Mirror', 'Mirrors & Specialty Glass', 'Glass', '5000.00', '[\"6ce0196b2aad5033211bf942f1003ec3.jpg\"]', NULL, '2026-01-27 11:00:34', 'Available', 'Mirrors', 'direct', '4000.00', '6000.00', '{\"shape\":[\"Rectangle\",\"Square\"],\"cornerRadius\":\"\",\"frameType\":[\"Frameless\"],\"frameColor\":[\"Machine Polished Edges\",\"Beveled Edge\"],\"glassType\":[\"Copper Free & Lead Free Mirror\"],\"thickness\":[\"6mm\"],\"mountingMethod\":[\"Wall-mounted\",\"Stand\",\"Adhesive\",\"Leaning\"]}', 'Mirror'),
(17, 'Oval Frame/Frameless Mirror', 'Mirrors & Specialty Glass', 'Glass', '7500.00', '[\"99f1367f6f19e39bbbd46b08a3d1bf07.jpg\",\"7ea59aea8370286870dedc8c6cd08a74.jpg\",\"7f755b139a8545c5cc683ce6987f6ee4.jpg\",\"8b98dc7a6e33fb9b14542f985573d4a1.jpg\"]', NULL, '2026-01-27 11:02:44', 'Available', 'Mirrors', 'direct', '6000.00', '9000.00', '{\"shape\":[\"Rectangle\",\"Oval\",\"Square\",\"Round\"],\"cornerRadius\":\"\",\"frameType\":[\"Frameless\",\"Framed\"],\"frameColor\":[\"White\",\"Black\",\"Gold\",\"Machine Polished Edges\",\"Beveled Edge\"],\"glassType\":[\"Copper Free & Lead Free Mirror\"],\"thickness\":[\"6mm\"],\"lighting\":[\"Integrated LED lighting\",\"Backlighting\",\"Front lighting\"],\"ledColorTemperature\":[\"Warm white\",\"Cool white\"],\"control\":[\"Touch sensor button\",\"Dimmer\",\"Defogger\"],\"mountingMethod\":[\"Wall-mounted\",\"Stand\",\"Adhesive\",\"Leaning\"]}', 'Mirror'),
(18, 'Top Glass', 'Mirrors & Specialty Glass', 'Glass', '6500.00', '[\"17a4a68475cdc122dfd6274080e423db.jpg\",\"90dbcce4ebb157d673be171de2194b61.jpg\"]', NULL, '2026-01-27 11:10:28', 'Available', 'Top Glass', 'direct', '5000.00', '8000.00', '{\"shape\":[\"Round\",\"Rectangle\",\"Oval\",\"Square\"],\"edgeFinish\":[\"Beveled\",\"Polished\",\"Raw\",\"Beveled edge\"],\"mountingMethod\":[\"Stand\",\"Wall-mounted\"],\"thicknessmm\":[\"6mm\",\"8mm\",\"10mm\"]}', 'Top Glass'),
(19, 'Glass Board', 'Mirrors & Specialty Glass', 'Glass', '5000.00', '[\"fc1edca9a812d7c3fb6dae38fb2c7aaa.jpg\",\"14549d2596d9b9b0a2b1d298578bccef.jpg\",\"39d0e7789074b0a22c73f9c580a1d55a.jpg\",\"94e0facafe494fa5ae280fa329c7a048.jpg\"]', NULL, '2026-01-27 11:12:52', 'Available', 'Glass Board', 'direct', '4000.00', '6000.00', '{\"shape\":[\"Rectangle\",\"Square\"],\"edgeFinish\":[\"Beveled\",\"Polished\",\"Raw\",\"Beveled edge\",\"Flat polished edge\",\"Pencil edge\"],\"cornerRadius\":\"\",\"mountingMethod\":[\"Wall-mounted\",\"Stand\"]}', 'Glass Board'),
(20, '798 Series Sliding Door', 'Doors', 'Glass', '8500.00', '[\"bceed3e272633bbcbceb786b3f3e6951.jpg\",\"1ed4e16c50a8fa1b82713e1c095cdfc5.jpg\"]', NULL, '2026-01-27 11:14:47', 'Available', 'Sliding', 'site-assessment', '7000.00', '10000.00', '{\"numberOfPanels\":[\"2 Panels\",\"4 Panels\"],\"transomType\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\"],\"trackSystem\":[\"2 Tracks\",\"3 Tracks\"],\"panelConfiguration\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"glassThickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"lockType\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"rollerType\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"]}', '798 Series'),
(21, '900 Series Sliding Door', 'Doors', 'Glass', '10000.00', '[\"5a4ccb9e6892ee4dbb694ba46fde74c9.jpg\",\"7dae5e8ea92a9838fae00426fa650df1.jpg\"]', NULL, '2026-01-27 11:15:57', 'Available', 'Sliding', 'site-assessment', '9000.00', '11000.00', '{\"numberOfPanels\":[\"2 Panels\",\"4 Panels\"],\"transomType\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\"],\"trackSystem\":[\"2 Tracks\",\"3 Tracks\"],\"panelConfiguration\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Gray\",\"Matte Black\",\"Wood Finish\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"glassThickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"lockType\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"rollerType\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"]}', '900 Series'),
(22, '868/130 Series Sliding Door', 'Doors', 'Glass', '12500.00', '[\"49feb857016eafe1c62b53d145cc53d0.jpg\",\"a3e167a88c354af040d6f0954613e807.jpg\"]', NULL, '2026-01-27 11:17:28', 'Available', 'Sliding', 'site-assessment', '10000.00', '15000.00', '{\"numberOfPanels\":[\"2 Panels\",\"4 Panels\"],\"transomType\":[\"None\",\"Fixed Transom Head (Fixed glass at top)\",\"Fixed Transom Sill (Fixed glass at bottom)\"],\"trackSystem\":[\"2 Tracks\",\"3 Tracks\"],\"panelConfiguration\":[\"S | S (Sliding | Sliding)\",\"F | S (Fixed | Sliding)\",\"S | S | S | S (All Sliding)\",\"F | S | S | F (Fixed | Sliding | Sliding | Fixed)\"],\"frameColor\":[\"Powder Coated White\",\"Analok\",\"Matte Black\",\"Wood Finish\",\"Matte Gray\"],\"glassType\":[\"Ordinary\",\"Tempered\",\"Reflective\",\"Frosted\"],\"glassColor\":[\"Clear\",\"Bronze\",\"Smoked\"],\"glassThickness\":[\"6mm\",\"8mm\",\"10mm\",\"12mm\"],\"lockType\":[\"Center Lok 904 Big\",\"Flushlok #12\",\"Durable Flushlok\",\"New Auto Flushlock\"],\"rollerType\":[\"Single Panel Roller\",\"Blue Single Roller\",\"Blue Double Roller\"]}', '900 Series');

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
(5, 5, 'Standard Series', '2026-01-14 12:16:35', NULL, NULL, 'in', 'in', NULL),
(6, 5, 'Premium Series', '2026-01-14 12:16:35', NULL, NULL, 'in', 'in', NULL),
(7, 6, 'Standard Series', '2026-01-14 12:17:11', NULL, NULL, 'in', 'in', NULL),
(8, 6, 'Premium Series', '2026-01-14 12:17:11', NULL, NULL, 'in', 'in', NULL),
(9, 7, '150 Series', '2026-01-14 05:28:09', NULL, NULL, 'in', 'in', NULL),
(10, 9, '700 Series', '2026-01-14 05:43:55', NULL, NULL, 'in', 'in', NULL),
(17, 4, 'Standard Series', '2026-01-26 19:53:45', NULL, NULL, 'in', 'in', NULL),
(18, 4, 'Premium Series', '2026-01-26 19:53:45', NULL, NULL, 'in', 'in', NULL);

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
(29, 9, '150.00', '150.00', '2500.00', '2026-01-14 05:28:09', 0, '', 'in', 'in', NULL, NULL),
(54, 17, '80.00', '100.00', '1200.00', '2026-01-26 19:53:45', 0, '', 'in', 'in', NULL, NULL),
(55, 17, '100.00', '120.00', '1500.00', '2026-01-26 19:53:45', 0, '', 'in', 'in', NULL, NULL),
(56, 17, '120.00', '150.00', '1800.00', '2026-01-26 19:53:45', 0, '', 'in', 'in', NULL, NULL),
(57, 17, '150.00', '180.00', '2200.00', '2026-01-26 19:53:45', 0, '', 'in', 'in', NULL, NULL),
(58, 18, '80.00', '100.00', '1500.00', '2026-01-26 19:53:45', 0, '', 'in', 'in', NULL, NULL),
(59, 18, '100.00', '120.00', '1800.00', '2026-01-26 19:53:45', 0, '', 'in', 'in', NULL, NULL),
(60, 18, '120.00', '150.00', '2200.00', '2026-01-26 19:53:45', 0, '', 'in', 'in', NULL, NULL),
(61, 18, '150.00', '180.00', '2700.00', '2026-01-26 19:53:45', 0, '', 'in', 'in', NULL, NULL);

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
(2, 3, 'glassType', 'Tinted', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(3, 3, 'glassType', 'Laminated', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(4, 3, 'frameColor', 'White', '0.00', 'uploads/tags/white-frame.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(5, 3, 'frameColor', 'Black', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(6, 3, 'frameColor', 'Silver', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(7, 3, 'frameColor', 'Bronze', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(8, 3, 'frameColor', 'Wood', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(9, 3, 'frameColor', 'Aluminum', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(10, 3, 'thickness', '3mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(11, 3, 'thickness', '5mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-18 02:56:46'),
(12, 3, 'thickness', '6mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(13, 3, 'thickness', '8mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(14, 3, 'screen', 'Yes', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:13:00', '', '', NULL, '2026-01-27 15:34:01'),
(29, 5, 'glassType', 'Clear', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(30, 5, 'glassType', 'Tinted', '0.00', 'uploads/tags/tinted-glass.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(31, 5, 'glassType', 'Laminated', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(32, 5, 'frameColor', 'White', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(33, 5, 'frameColor', 'Black', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(34, 5, 'frameColor', 'Silver', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(35, 5, 'frameColor', 'Bronze', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(36, 5, 'frameColor', 'Wood', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(37, 5, 'frameColor', 'Aluminum', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(38, 5, 'thickness', '3mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(39, 5, 'thickness', '5mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-18 02:56:46'),
(40, 5, 'thickness', '6mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(41, 5, 'thickness', '8mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(42, 5, 'screen', 'Yes', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:16:35', '', '', NULL, '2026-01-27 15:34:01'),
(43, 6, 'glassType', 'Clear', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(44, 6, 'glassType', 'Tinted', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(45, 6, 'glassType', 'Laminated', '0.00', 'uploads/tags/laminated-glass.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(46, 6, 'frameColor', 'White', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(47, 6, 'frameColor', 'Black', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(48, 6, 'frameColor', 'Silver', '0.00', 'uploads/tags/silver-frame.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(49, 6, 'frameColor', 'Bronze', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(50, 6, 'frameColor', 'Wood', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(51, 6, 'frameColor', 'Aluminum', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(52, 6, 'thickness', '3mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(53, 6, 'thickness', '5mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-18 02:56:46'),
(54, 6, 'thickness', '6mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(55, 6, 'thickness', '8mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(56, 6, 'screen', 'Yes', '0.00', 'assets/images/broken-image-icon.png', '2026-01-14 12:17:11', '', '', NULL, '2026-01-27 15:34:01'),
(298, 31, 'transomType', 'SAmpleeee', '0.00', NULL, '2026-01-23 21:25:00', '', '', NULL, '2026-01-24 12:25:00'),
(341, 4, 'glassType', 'Clear', '0.00', 'uploads/tags/clear-glass.png', '2026-01-26 19:53:45', '', '', '{\"fill\":\"#E0F2F1\",\"opacity\":0.9,\"enabled\":true}', '2026-01-27 10:53:45'),
(342, 4, 'glassType', 'Tinted', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', '{\"fill\":\"#546E7A\",\"opacity\":0.7,\"enabled\":true}', '2026-01-27 15:34:01'),
(343, 4, 'glassType', 'Laminated', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', '{\"fill\":\"#CFD8DC\",\"opacity\":0.95,\"enabled\":true}', '2026-01-27 15:34:01'),
(344, 4, 'frameColor', 'White', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', '{\"stroke\":\"#FFFFFF\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 10:53:45'),
(345, 4, 'frameColor', 'Black', '0.00', 'uploads/tags/black-frame.png', '2026-01-26 19:53:45', '', '', '{\"stroke\":\"#000000\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 15:34:01'),
(346, 4, 'frameColor', 'Silver', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', '{\"stroke\":\"#C0C0C0\",\"strokeWidth\":3,\"enabled\":true}', '2026-01-27 15:34:01'),
(347, 4, 'frameColor', 'Bronze', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', '{\"stroke\":\"#CD7F32\",\"strokeWidth\":3,\"enabled\":true}', '2026-01-27 15:34:01'),
(348, 4, 'frameColor', 'Wood', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', '{\"stroke\":\"#795548\",\"strokeWidth\":6,\"enabled\":true}', '2026-01-27 15:34:01'),
(349, 4, 'frameColor', 'Aluminum', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', '{\"stroke\":\"#90A4AE\",\"strokeWidth\":3,\"enabled\":true}', '2026-01-27 15:34:01'),
(350, 4, 'thickness', '3mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', NULL, '2026-01-27 15:34:01'),
(351, 4, 'thickness', '5mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', NULL, '2026-01-27 10:53:45'),
(352, 4, 'thickness', '6mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', NULL, '2026-01-27 15:34:01'),
(353, 4, 'thickness', '8mm', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', NULL, '2026-01-27 15:34:01'),
(354, 4, 'screen', 'Yes', '0.00', 'assets/images/broken-image-icon.png', '2026-01-26 19:53:45', '', '', NULL, '2026-01-27 15:34:01'),
(532, 33, 'shape', 'Round', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(533, 33, 'shape', 'Rectangle', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(534, 33, 'shape', 'Oval', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(535, 33, 'shape', 'Square', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(536, 33, 'shape', 'Rectangular with rounded edges', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(537, 33, 'shape', 'Rectangular with arched top', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(538, 33, 'frameType', 'Frameless', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"transparent\",\"strokeWidth\":0,\"enabled\":true}', '2026-01-27 16:05:23'),
(539, 33, 'frameType', 'Framed', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#333333\",\"strokeWidth\":6,\"enabled\":true}', '2026-01-27 16:05:23'),
(540, 33, 'frameType', 'Gold frame', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#FFD700\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 16:05:23'),
(541, 33, 'frameType', 'Black frame', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#000000\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 16:05:23'),
(542, 33, 'frameType', 'White frame', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#FFFFFF\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 16:05:23'),
(543, 33, 'frameType', 'Framed (thin, metallic)', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#333333\",\"strokeWidth\":6,\"enabled\":true}', '2026-01-27 16:05:23'),
(544, 33, 'frameType', 'Framed (dark, possibly black, grid frame)', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#000000\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 16:05:23'),
(545, 33, 'frameType', 'Framed (gold frame shown)', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#FFD700\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 16:05:23'),
(546, 33, 'frameType', 'Framed (thin matching frame possible)', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#333333\",\"strokeWidth\":6,\"enabled\":true}', '2026-01-27 16:05:23'),
(547, 33, 'frameColor', 'Gold frame', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#FFD700\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 16:05:23'),
(548, 33, 'frameColor', 'Silver', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#C0C0C0\",\"strokeWidth\":3,\"enabled\":true}', '2026-01-27 16:05:23'),
(549, 33, 'frameColor', 'Rose Gold', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#FFD700\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 16:05:23'),
(550, 33, 'frameColor', 'Wood', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#795548\",\"strokeWidth\":6,\"enabled\":true}', '2026-01-27 16:05:23'),
(551, 33, 'frameColor', 'Black frame', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#000000\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 16:05:23'),
(552, 33, 'frameColor', 'White frame', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#FFFFFF\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 16:05:23'),
(553, 33, 'frameColor', 'Metal', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(554, 33, 'frameColor', 'Silver/Metallic', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#C0C0C0\",\"strokeWidth\":3,\"enabled\":true}', '2026-01-27 16:05:23'),
(555, 33, 'frameColor', 'Dark/Black', '0.00', NULL, '2026-01-27 01:05:23', '', '', '{\"stroke\":\"#000000\",\"strokeWidth\":4,\"enabled\":true}', '2026-01-27 16:05:23'),
(556, 33, 'edgeFinish', 'Beveled', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(557, 33, 'edgeFinish', 'Polished', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(558, 33, 'edgeFinish', 'Raw', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(559, 33, 'edgeFinish', 'Beveled edge', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(560, 33, 'edgeFinish', 'Flat polished edge', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(561, 33, 'edgeFinish', 'Pencil edge', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(562, 33, 'edgeFinish', 'Standard polished edge', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(563, 33, 'edgeFinish', 'Standard (behind frame)', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(564, 33, 'edgeFinish', 'Rounded edges', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(565, 33, 'tintFinish', 'Bronze tint/color', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(566, 33, 'tintFinish', 'Grey tint (smoked)', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(567, 33, 'tintFinish', 'Colored glass', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(568, 33, 'orientation', 'Vertical', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(569, 33, 'orientation', 'Horizontal', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(570, 33, 'orientation', 'Vertical/Full-body', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(571, 33, 'style', 'French Type (grid/paneled design)', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(572, 33, 'mountingMethod', 'Wall-mounted', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(573, 33, 'mountingMethod', 'Stand', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(574, 33, 'mountingMethod', 'Adhesive', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(575, 33, 'mountingMethod', 'Leaning', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(576, 33, 'mountingMethod', 'Wall-mounted (often fixed above vanity)', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(577, 33, 'mountingMethod', 'Fixed wall mount', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(578, 33, 'mountingMethod', 'Integrated hanger', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(579, 33, 'mountingMethod', 'Rope hanger', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(580, 33, 'mountingMethod', 'Chain', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(581, 33, 'control', 'Touch sensor button', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(582, 33, 'control', 'Dimmer', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(583, 33, 'control', 'Defogger', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(584, 33, 'additionalFeatures', 'Defogger', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(585, 33, 'additionalFeatures', 'Dimmer', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(586, 33, 'ledColorTemperature', 'Warm white', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(587, 33, 'ledColorTemperature', 'Cool white', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(588, 33, 'ledColorTemperature', 'Tunable white', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(589, 33, 'ledColorTemperature', 'RGB', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(590, 33, 'gridPattern', 'French window style grid', '0.00', NULL, '2026-01-27 01:05:23', '', '', NULL, '2026-01-27 16:05:23'),
(592, 10, 'glassColor', 'Frosted', '0.00', NULL, '2026-01-27 10:46:36', '', '', NULL, '2026-01-28 01:46:36');

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
(8, 75, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(9, 1, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(10, 2, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(11, 3, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(12, 4, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(13, 5, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(14, 8, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(15, 9, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(16, 10, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(17, 11, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(18, 12, 2, '', '0000-00-00', '0000-00-00', 'In progress'),
(19, 14, 4, '', '0000-00-00', '0000-00-00', 'In progress'),
(20, 15, 4, '', '0000-00-00', '0000-00-00', 'In progress'),
(21, 17, 4, '', '0000-00-00', '0000-00-00', 'In progress'),
(22, 18, 4, '', '0000-00-00', '0000-00-00', 'In progress'),
(23, 19, 4, '', '0000-00-00', '0000-00-00', 'In progress');

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
-- Table structure for table `role_change_log`
--

CREATE TABLE `role_change_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_role` varchar(64) DEFAULT NULL,
  `new_role` varchar(64) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_change_log`
--

INSERT INTO `role_change_log` (`id`, `user_id`, `old_role`, `new_role`, `changed_by`, `reason`, `created_at`) VALUES
(1, 19, 'Customer', 'Skilled Installer', NULL, '', '2026-02-11 20:42:53'),
(2, 19, '', 'Professional', NULL, '', '2026-02-11 21:12:25'),
(3, 19, '', 'Professional', NULL, '', '2026-02-11 21:15:05'),
(4, 19, '', 'Professional', NULL, '', '2026-02-11 21:26:36'),
(5, 19, '', 'Professional', NULL, '', '2026-02-11 21:27:51'),
(6, 19, '', 'Professional', NULL, '', '2026-02-11 21:38:00'),
(7, 19, '', 'Professional', NULL, '', '2026-02-11 21:38:35'),
(8, 19, '', 'Professional', NULL, '', '2026-02-11 21:41:24'),
(9, 19, '', 'Professional', NULL, '', '2026-02-11 21:45:49'),
(10, 19, '', 'Professional', NULL, '', '2026-02-11 21:46:05'),
(11, 19, '', 'Beginner', NULL, '', '2026-02-11 21:46:30'),
(12, 19, '', 'Beginner', NULL, '', '2026-02-11 21:50:33'),
(13, 19, '', 'Beginner', NULL, '', '2026-02-11 21:50:51'),
(14, 19, '', 'Beginner', NULL, '', '2026-02-11 21:51:04'),
(15, 19, '', 'Professional', NULL, '', '2026-02-11 21:59:49'),
(16, 19, '', 'Beginner', NULL, '', '2026-02-11 21:59:57'),
(17, 20, 'Customer', 'Beginner', NULL, '', '2026-02-11 23:07:29'),
(18, 20, '', 'Beginner', NULL, '', '2026-02-11 23:17:36'),
(19, 1, '', 'Beginner', NULL, 'test', '2026-02-12 11:46:55'),
(20, 21, '', 'Professional', NULL, 'yeh', '2026-02-12 12:37:10'),
(21, 21, '', 'Beginner', NULL, 'test', '2026-02-12 14:55:54'),
(22, 21, '', 'Professional', NULL, 'test', '2026-02-12 15:03:58');

-- --------------------------------------------------------

--
-- Table structure for table `role_requests`
--

CREATE TABLE `role_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `requested_role` varchar(64) NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`answers`)),
  `confirmation` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','auto_approved','flagged','approved','denied') NOT NULL DEFAULT 'pending',
  `comment` varchar(128) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_requests`
--

INSERT INTO `role_requests` (`id`, `user_id`, `requested_role`, `answers`, `confirmation`, `status`, `comment`, `admin_id`, `created_at`, `reviewed_at`) VALUES
(1, 19, 'Skilled Installer', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 20:24:45', NULL),
(2, 19, 'Skilled Installer', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 20:35:02', NULL),
(3, 19, 'Skilled Installer', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 20:40:18', NULL),
(4, 19, 'Skilled Installer', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 20:42:53', NULL),
(5, 19, 'Professional', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:12:25', NULL),
(6, 19, 'Professional', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:15:05', NULL),
(7, 19, 'Professional', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:26:36', NULL),
(8, 19, 'Professional', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:27:51', NULL),
(9, 19, 'Professional', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:38:00', NULL),
(10, 19, 'Professional', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:38:35', NULL),
(11, 19, 'Professional', '[\"No\",\"Yes\",\"Yes\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:41:24', NULL),
(12, 19, 'Professional', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:45:49', NULL),
(13, 19, 'Professional', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:46:05', NULL),
(14, 19, 'Beginner', '[\"No\",\"No\",\"Yes\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:46:30', NULL),
(15, 19, 'Beginner', '[\"Yes\",\"No\",\"Yes\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:50:33', NULL),
(16, 19, 'Beginner', '[\"No\",\"No\",\"Yes\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:50:51', NULL),
(17, 19, 'Beginner', '[\"No\",\"No\",\"Yes\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:51:04', NULL),
(18, 19, 'Professional', '[\"Yes\",\"No\",\"Yes\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:59:49', NULL),
(19, 19, 'Beginner', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 21:59:57', NULL),
(20, 20, 'Beginner', '[\"Yes\",\"No\",\"No\"]', 1, 'auto_approved', '', NULL, '2026-02-11 23:07:29', NULL),
(21, 20, 'Beginner', '[\"No\",\"No\",\"Yes\"]', 1, 'auto_approved', '', NULL, '2026-02-11 23:17:36', NULL),
(22, 1, 'Beginner', '[\"No\",\"No\",\"No\"]', 1, 'auto_approved', 'test', NULL, '2026-02-12 11:46:55', NULL),
(23, 21, 'Professional', '[\"Yes\",\"Yes\",\"Yes\"]', 1, 'auto_approved', 'yeh', NULL, '2026-02-12 12:37:10', NULL),
(24, 21, 'Beginner', '[\"No\",\"No\",\"No\"]', 1, 'auto_approved', 'test', NULL, '2026-02-12 14:55:54', NULL),
(25, 21, 'Professional', '[\"Yes\",\"Yes\",\"Yes\"]', 1, 'auto_approved', 'test', NULL, '2026-02-12 15:03:57', NULL);

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
(121, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI091 has been created and requires your review', 'Unread', '2026-01-26 10:54:30', NULL, 91, 'Order'),
(122, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI001 has been created and requires your review', 'Unread', '2026-01-27 20:31:27', NULL, 1, 'Order'),
(123, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI002 has been created and requires your review', 'Unread', '2026-02-03 01:03:27', NULL, 2, 'Order'),
(124, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI003 has been created and requires your review', 'Unread', '2026-02-03 01:33:08', NULL, 3, 'Order'),
(125, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI004 has been created and requires your review', 'Unread', '2026-02-03 05:49:02', NULL, 4, 'Order'),
(126, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI005 has been created and requires your review', 'Unread', '2026-02-03 06:08:00', NULL, 5, 'Order'),
(127, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI006 has been created and requires your review', 'Unread', '2026-02-05 12:54:41', NULL, 6, 'Order'),
(128, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI007 has been created and requires your review', 'Unread', '2026-02-05 13:03:45', NULL, 7, 'Order'),
(129, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI008 has been created and requires your review', 'Unread', '2026-02-05 13:11:43', NULL, 8, 'Order'),
(130, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI009 has been created and requires your review', 'Unread', '2026-02-05 13:19:38', NULL, 9, 'Order'),
(131, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI010 has been created and requires your review', 'Unread', '2026-02-07 07:49:32', NULL, 10, 'Order'),
(132, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI011 has been created and requires your review', 'Unread', '2026-02-07 08:00:48', NULL, 11, 'Order'),
(133, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI012 has been created and requires your review', 'Unread', '2026-02-07 09:01:06', NULL, 12, 'Order'),
(134, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI013 has been created and requires your review', 'Unread', '2026-02-07 12:02:25', NULL, 13, 'Order'),
(135, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI014 has been created and requires your review', 'Unread', '2026-02-07 16:04:52', NULL, 14, 'Order'),
(136, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI015 has been created and requires your review', 'Unread', '2026-02-07 16:05:57', NULL, 15, 'Order'),
(137, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI016 has been created and requires your review', 'Unread', '2026-02-08 04:17:21', NULL, 16, 'Order'),
(138, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI017 has been created and requires your review', 'Unread', '2026-02-08 04:20:39', NULL, 17, 'Order'),
(139, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI018 has been created and requires your review', 'Unread', '2026-02-08 04:46:00', NULL, 18, 'Order'),
(140, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI019 has been created and requires your review', 'Unread', '2026-02-08 04:52:46', NULL, 19, 'Order'),
(141, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI001 has been created and requires your review', 'Unread', '2026-02-09 05:09:11', NULL, 1, 'Order'),
(142, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI002 has been created and requires your review', 'Unread', '2026-02-09 05:17:39', NULL, 2, 'Order'),
(143, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI003 has been created and requires your review', 'Unread', '2026-02-09 05:36:13', NULL, 3, 'Order'),
(144, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI004 has been created and requires your review', 'Unread', '2026-02-09 05:36:50', NULL, 4, 'Order'),
(145, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI005 has been created and requires your review', 'Unread', '2026-02-09 06:43:06', NULL, 5, 'Order'),
(146, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI006 has been created and requires your review', 'Unread', '2026-02-09 08:00:49', NULL, 6, 'Order'),
(147, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI007 has been created and requires your review', 'Unread', '2026-02-09 08:05:58', NULL, 7, 'Order'),
(148, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI008 has been created and requires your review', 'Unread', '2026-02-09 18:32:42', NULL, 8, 'Order'),
(149, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI009 has been created and requires your review', 'Unread', '2026-02-09 19:39:23', NULL, 9, 'Order'),
(150, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI010 has been created and requires your review', 'Unread', '2026-02-10 02:33:23', NULL, 10, 'Order'),
(151, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI011 has been created and requires your review', 'Unread', '2026-02-10 02:56:34', NULL, 11, 'Order'),
(152, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI012 has been created and requires your review', 'Unread', '2026-02-11 15:42:05', NULL, 12, 'Order'),
(153, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI013 has been created and requires your review', 'Unread', '2026-02-11 22:25:47', NULL, 13, 'Order'),
(154, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI014 has been created and requires your review', 'Unread', '2026-02-12 04:50:22', NULL, 14, 'Order'),
(155, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI015 has been created and requires your review', 'Unread', '2026-02-12 05:03:53', NULL, 15, 'Order'),
(156, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI016 has been created and requires your review', 'Unread', '2026-02-12 05:19:56', NULL, 16, 'Order'),
(157, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI017 has been created and requires your review', 'Unread', '2026-02-12 06:03:09', NULL, 17, 'Order'),
(158, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI018 has been created and requires your review', 'Unread', '2026-02-12 06:21:13', NULL, 18, 'Order'),
(159, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI019 has been created and requires your review', 'Unread', '2026-02-12 06:32:57', NULL, 19, 'Order'),
(160, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI020 has been created and requires your review', 'Unread', '2026-02-12 07:30:14', NULL, 20, 'Order'),
(161, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI021 has been created and requires your review', 'Unread', '2026-02-12 08:04:50', NULL, 21, 'Order');

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
(12, 'Payment Received', 'Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:11:00', 5, 'Payment'),
(13, 'Payment Received', 'Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:11:00', 5, 'Payment'),
(14, 'Payment Received', 'Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:11:23', 4, 'Payment'),
(15, 'Payment Received', 'Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:11:23', 4, 'Payment'),
(20, 'Payment Received', 'Payment for Order GI006 (Amount: ₱26,671.72) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:28:47', 6, 'Payment'),
(21, 'Payment Received', 'Payment for Order GI006 (Amount: ₱26,671.72) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:28:48', 6, 'Payment'),
(22, 'Payment Received', 'Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:29:21', 5, 'Payment'),
(23, 'Payment Received', 'Payment for Order GI005 (Amount: ₱27,803.75) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:29:21', 5, 'Payment'),
(25, 'Payment Received', 'Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:39:15', 4, 'Payment'),
(26, 'Payment Received', 'Payment for Order GI004 (Amount: ₱23,660.00) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-08 14:39:17', 4, 'Payment'),
(53, 'Payment Received', 'Payment for Order GI017 (Amount: ₱50,925.50) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-09 02:00:13', 17, 'Payment'),
(54, 'Payment Received', 'Payment for Order GI017 (Amount: ₱50,925.50) has been marked as paid by Sales Test.', 'Sales Representative', 3, 'Sales Test', '2025-12-09 02:00:13', 17, 'Payment'),
(162, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI042 to 2026-01-26', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-24 12:13:56', NULL, NULL),
(217, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI091 to 2026-01-28', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 11:00:38', NULL, NULL),
(218, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI091 to 2026-01-31', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 11:04:13', NULL, NULL),
(219, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI091 to 2026-01-30', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 11:14:43', NULL, NULL),
(220, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI091 to 2026-01-30', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 11:45:22', NULL, NULL),
(221, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI090 to 2026-01-29', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 11:45:56', NULL, NULL),
(222, 'Installation Date Change Approved', 'Admin Admin Test approved installation date change for order #GI090 to 2026-01-29', 'Admin', NULL, 'Admin Test', '2026-01-26 12:15:06', NULL, NULL),
(223, 'Installation Date Change Approved', 'Admin Admin Test approved installation date change for order #GI090 to 2026-01-29', 'Admin', NULL, 'Admin Test', '2026-01-26 12:15:12', NULL, NULL),
(224, 'Installation Date Change Request', 'Customer Rommel John Jeric Lerum (ID: 8) requested installation date change for order #GI090 to 2026-02-01', 'Customer', NULL, 'Rommel John Jeric Lerum', '2026-01-26 12:16:00', NULL, NULL),
(225, 'Installation Date Change Approved', 'Admin Admin Test approved installation date change for order #GI090 to 2026-02-01', 'Admin', NULL, 'Admin Test', '2026-01-26 12:16:14', NULL, NULL),
(259, 'Order Created', 'New order created: GI001 (Customer ID: 15)', 'Customer', NULL, NULL, '2026-02-09 05:09:11', 1, 'Order'),
(260, 'Order Created', 'New order created: GI002 (Customer ID: 15)', 'Customer', NULL, NULL, '2026-02-09 05:17:39', 2, 'Order'),
(261, 'Order Status Updated', 'Order GI002 status changed from  to Cancelled', 'Admin', 2, 'Admin Test', '2026-02-09 05:18:26', 2, 'Order'),
(262, 'Order Status Updated', 'Order GI001 status changed from  to Cancelled', 'Admin', 2, 'Admin Test', '2026-02-09 05:26:43', 1, 'Order'),
(263, 'Order Created', 'New order created: GI003 (Customer ID: 15)', 'Customer', NULL, NULL, '2026-02-09 05:36:13', 3, 'Order'),
(264, 'Order Status Updated', 'Order GI003 status changed from  to Cancelled', 'Admin', 2, 'Admin Test', '2026-02-09 05:36:28', 3, 'Order'),
(265, 'Order Created', 'New order created: GI004 (Customer ID: 15)', 'Customer', NULL, NULL, '2026-02-09 05:36:50', 4, 'Order'),
(266, 'Order Status Updated', 'Order GI004 status changed from  to Cancelled', 'Admin', 2, 'Admin Test', '2026-02-09 05:37:15', 4, 'Order'),
(267, 'Order Created', 'New order created: GI005 (Customer ID: 15)', 'Customer', NULL, NULL, '2026-02-09 06:43:06', 5, 'Order'),
(268, 'Order Status Updated', 'Order GI005 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-02-09 07:01:49', 5, 'Order'),
(269, 'Order Created', 'New order created: GI006 (Customer ID: 15)', 'Customer', NULL, NULL, '2026-02-09 08:00:49', 6, 'Order'),
(270, 'Order Created', 'New order created: GI007 (Customer ID: 15)', 'Customer', NULL, NULL, '2026-02-09 08:05:58', 7, 'Order'),
(271, 'Order Status Updated', 'Order GI007 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-02-09 09:23:36', 7, 'Order'),
(272, 'Order Created', 'New order created: GI008 (Customer ID: 15)', 'Customer', NULL, NULL, '2026-02-09 18:32:42', 8, 'Order'),
(273, 'Order Status Updated', 'Order GI008 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-02-09 18:33:00', 8, 'Order'),
(274, 'Order Created', 'New order created: GI009 (Customer ID: 15)', 'Customer', NULL, NULL, '2026-02-09 19:39:23', 9, 'Order'),
(275, 'Order Status Updated', 'Order GI009 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-02-09 19:39:42', 9, 'Order'),
(276, 'Installation Date Change Request', 'Customer Leonidas Santos (ID: 15) requested installation date change for order #GI009 to 2026-02-13', 'Customer', NULL, 'Leonidas Santos', '2026-02-09 22:48:32', NULL, NULL),
(277, 'Installation Date Change Approved', 'Admin Admin Test approved installation date change for order #GI009 to 2026-02-13', 'Admin', NULL, 'Admin Test', '2026-02-09 22:48:53', NULL, NULL),
(278, 'Order Created', 'New order created: GI010 (Customer ID: 15)', 'Customer', NULL, NULL, '2026-02-10 02:33:23', 10, 'Order'),
(279, 'Order Status Updated', 'Order GI010 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-02-10 02:36:35', 10, 'Order'),
(280, 'Order Status Updated', 'Order GI010 status updated to: Completed', 'System', NULL, NULL, '2026-02-10 02:45:27', 10, 'Order'),
(281, 'Order Status Updated', 'Order GI010 status updated to: Completed', 'System', NULL, NULL, '2026-02-10 02:45:33', 10, 'Order'),
(282, 'Order Created', 'New order created: GI011 (Customer ID: 17)', 'Customer', NULL, NULL, '2026-02-10 02:56:34', 11, 'Order'),
(283, 'Order Status Updated', 'Order GI011 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-02-10 02:57:28', 11, 'Order'),
(284, 'Order Status Updated', 'Order GI011 status updated to: Completed', 'System', NULL, NULL, '2026-02-10 03:06:21', 11, 'Order'),
(285, 'Order Status Updated', 'Order GI006 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-02-11 10:44:15', 6, 'Order'),
(286, 'Order Created', 'New order created: GI012 (Customer ID: 18)', 'Customer', NULL, NULL, '2026-02-11 15:42:05', 12, 'Order'),
(287, 'Order Status Updated', 'Order GI012 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-02-11 15:48:35', 12, 'Order'),
(288, 'Order Status Updated', 'Order GI012 status updated to: Completed', 'System', NULL, NULL, '2026-02-11 16:02:15', 12, 'Order'),
(289, 'Order Status Updated', 'Order GI012 status updated to: Completed', 'System', NULL, NULL, '2026-02-11 16:02:20', 12, 'Order'),
(290, 'Order Created', 'New order created: GI013 (Customer ID: 18)', 'Customer', NULL, NULL, '2026-02-11 22:25:47', 13, 'Order'),
(291, 'Order Status Updated', 'Order GI013 status changed from  to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-02-11 22:26:07', 13, 'Order'),
(292, 'Order Status Updated', 'Order GI013 status changed from Ocular Pending to Ocular Pending', 'Admin', 2, 'Admin Test', '2026-02-12 00:48:22', 13, 'Order'),
(293, 'Order Created', 'New order created: GI014 (Customer ID: 1)', 'Customer', NULL, NULL, '2026-02-12 04:50:22', 14, 'Order'),
(294, 'Order Status Updated', 'Order GI014 status changed from  to Ocular Pending', 'Admin', 4, 'Admin Super', '2026-02-12 04:52:37', 14, 'Order'),
(295, 'Order Created', 'New order created: GI015 (Customer ID: 1)', 'Customer', NULL, NULL, '2026-02-12 05:03:53', 15, 'Order'),
(296, 'Order Status Updated', 'Order GI015 status changed from  to Ocular Pending', 'Admin', 4, 'Admin Super', '2026-02-12 05:04:09', 15, 'Order'),
(297, 'Order Status Updated', 'Order GI015 status changed from Ready for Installation to Completed', 'Admin', 4, 'Admin Super', '2026-02-12 05:15:15', 15, 'Order'),
(298, 'Order Created', 'New order created: GI016 (Customer ID: 19)', 'Customer', NULL, NULL, '2026-02-12 05:19:56', 16, 'Order'),
(299, 'Order Status Updated', 'Order GI016 status changed from  to Ocular Pending', 'Admin', 4, 'Admin Super', '2026-02-12 05:20:29', 16, 'Order'),
(300, 'Order Created', 'New order created: GI017 (Customer ID: 19)', 'Customer', NULL, NULL, '2026-02-12 06:03:09', 17, 'Order'),
(301, 'Order Status Updated', 'Order GI017 status changed from  to Ocular Pending', 'Admin', 4, 'Admin Super', '2026-02-12 06:05:58', 17, 'Order'),
(302, 'Order Status Updated', 'Order GI017 status changed from Ocular Pending to Ocular Pending', 'Admin', 4, 'Admin Super', '2026-02-12 06:06:12', 17, 'Order'),
(303, 'Order Status Updated', 'Order GI017 status updated to: Completed', 'System', NULL, NULL, '2026-02-12 06:19:48', 17, 'Order'),
(304, 'Order Created', 'New order created: GI018 (Customer ID: 19)', 'Customer', NULL, NULL, '2026-02-12 06:21:13', 18, 'Order'),
(305, 'Order Status Updated', 'Order GI018 status changed from  to Ocular Pending', 'Admin', 4, 'Admin Super', '2026-02-12 06:21:48', 18, 'Order'),
(306, 'Order Created', 'New order created: GI019 (Customer ID: 19)', 'Customer', NULL, NULL, '2026-02-12 06:32:57', 19, 'Order'),
(307, 'Order Status Updated', 'Order GI019 status changed from  to Ocular Pending', 'Admin', 4, 'Admin Super', '2026-02-12 06:33:16', 19, 'Order'),
(308, 'Order Status Updated', 'Order GI019 status updated to: Completed', 'System', NULL, NULL, '2026-02-12 06:37:43', 19, 'Order'),
(309, 'Order Created', 'New order created: GI020 (Customer ID: 19)', 'Customer', NULL, NULL, '2026-02-12 07:30:14', 20, 'Order'),
(310, 'Order Status Updated', 'Order GI020 status changed from  to Ocular Pending', 'Admin', 4, 'Admin Super', '2026-02-12 07:31:30', 20, 'Order'),
(311, 'Order Created', 'New order created: GI021 (Customer ID: 19)', 'Customer', NULL, NULL, '2026-02-12 08:04:50', 21, 'Order'),
(312, 'Order Status Updated', 'Order GI021 status changed from  to Ocular Pending', 'Admin', 4, 'Admin Super', '2026-02-12 08:07:24', 21, 'Order');

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
(1, 'Aaron Gabriel', 'Manantan', 'M.', 'manantan.aro@gmail.com', '$2y$10$q8v9lhdmbCUtn2afm/tSMe58NypAC/fmip47pgQXV/S5H80/AcpbW', NULL, NULL, '09937568011', 'uploads/profile/profile_1.png', '', 'Active', '2025-12-07 16:21:24', '2026-02-11 20:46:55', NULL),
(2, 'Admin', 'Test', '', 'admin.test@gmail.com', '$2y$10$H/RROdyDMNk9XN1JLbUYFeDfsGkotLtkzXOa5CTp5uBYFqb4Fb/gm', NULL, NULL, '09937569023', NULL, 'Admin', 'Active', '2025-12-07 16:23:22', '2025-12-07 16:23:37', NULL),
(3, 'Sales', 'Test', '', 'sales.rep@gmail.com', '$2y$10$ZTBDhCxjJi4ZZtwXa5B0rOWrd5j.zhsA6AntbN4QYOebpGn2SW/Em', NULL, NULL, '09937569024', NULL, 'Sales Representative', 'Active', '2025-12-07 17:10:33', '2025-12-07 17:10:33', NULL),
(4, 'Admin', 'Super', '', 'testing.admin@gmail.com', '$2y$10$ahTPP9RAI9s/hfSnNuRKyuT6Ik2WQjRI.u1sj0/PWUkWbVuj4VIJe', NULL, NULL, '09937568011', NULL, 'Admin', 'Active', '2025-12-29 13:06:35', '2025-12-29 13:09:19', NULL),
(5, 'Ag', 'Pauig', '', 'cheezygrizzoverload@gmail.com', '$2y$10$H6kuL/RdhXcTVOjNagG/gefQ8tWIPG02MgngC7xSUl1LiJcYGomEO', NULL, NULL, '09887779123', NULL, 'Customer', 'Active', '2026-01-17 19:37:17', '2026-01-17 19:38:04', NULL),
(9, 'Angela', 'Pauig', '', 'agchii127@gmail.com', '$2y$10$Q6XA7MHEPkAZsn3erKLDpOiU0AIAtdfNZ3R1Wm2bgLYlzYQq7Kzk2', NULL, NULL, '09614788448', NULL, 'Customer', 'Active', '2026-01-21 08:14:35', '2026-01-21 01:15:38', NULL),
(10, 'Rommel John Jeric', 'Lerum', 'R.', 'lerumgops@gmail.com', '$2y$10$9SA.5/.c6HsmTeRPh26Bnu8kLvpuuMA45DsUKEPjo4dN8ONlzLS4.', NULL, NULL, '09120844695', NULL, 'Customer', 'Active', '2026-01-23 01:25:08', '2026-01-23 01:26:11', NULL),
(11, 'Dani', 'Hein', 'Kim', 'agchii128@gmail.com', '$2y$10$nbcy4nZjQ0Gzh9sJd7gC.e6f3QCFIgOvcOEy5I.Ypv2B8Vu3Hi/sC', NULL, NULL, '09111111111', NULL, 'Customer', 'Active', '2026-01-23 10:02:28', '2026-02-11 21:21:19', NULL),
(12, 'Jinwoo', 'Sun', 'Kim', 'angelapauig05@gmail.com', '$2y$10$WCrETrab8UD2Yvr1RdnxM.Izvf/cUpXu1BPLJSiYcZRWT/0XHg2/G', NULL, NULL, '09111111111', NULL, 'Customer', 'Active', '2026-01-25 18:33:28', '2026-02-11 21:21:19', NULL),
(13, 'Shalltear', 'Smith', 'Olalo', 'gopslerum@gmail.com', '$2y$10$BPqF9AVppH91fD/FcOEt2uyBIh66XY4A.C6DH4XSkacDOa/wX/nla', NULL, NULL, '09120844695', NULL, 'Customer', 'Active', '2026-02-03 04:46:55', '2026-02-03 04:47:42', NULL),
(16, 'Glaire', 'Pauig', '', 'gitsquad2026@gmail.com', '$2y$10$YBwM.Al1xEIm7MaCuX97QO4eojoOLPOm98lMzvCL6FYlTLXMs/Isa', NULL, NULL, '09111111111', NULL, 'Customer', 'Active', '2026-02-04 10:02:38', '2026-02-11 21:21:19', NULL),
(17, 'Leonidas', 'Santos', 'Opus', 'lerum.rommeljohnjeric.robles@gmail.com', '$2y$10$LY2nWBicYnufW5gptR/BkOimKQxqW9dlg4FrW73i7jc.DfVyGtE3O', NULL, NULL, '09120844695', NULL, 'Customer', 'Active', '2026-02-09 02:44:30', '2026-02-11 21:21:19', NULL),
(18, 'Shalltear', 'Smith', 'Batumbakal', 'garciakris110@gmail.com', '$2y$10$K4pGfRSqLjGZANvXwySja.u53Ok5wVZS72cKLzvCIsd4kRreBxmyq', '921daf15a972f663c1282e0ca2a728a071fd611037f9cc79ace31b452a024ba2', '2026-02-11 02:51:36', '09120844695', NULL, 'Customer', 'Inactive', '2026-02-10 01:51:36', '2026-02-10 01:51:36', NULL),
(19, 'Arogela', 'Lerum', 'Robles', 'gitsquad5@gmail.com', '$2y$10$/sBprgU9Wu57lK/DFp3HpehCtAhjlhiSiSdMyFC538C9TDSFofKva', NULL, NULL, '09120844695', NULL, 'Customer', 'Active', '2026-02-10 01:52:50', '2026-02-11 21:21:19', NULL),
(20, 'Kelly', 'Delos Santos', 'Jadaone', 'davidmariakhellyc@gmail.com', '$2y$10$P65nQQ0dU0II1ER7AC1qNefCDs4a78EOErcGn/TZaFKTE9NFh3X.m', NULL, NULL, '09120844695', NULL, 'Customer', 'Active', '2026-02-11 14:33:44', '2026-02-11 21:21:19', NULL),
(21, 'Aro', 'Manantan', 'Gab', 'aro.manantan@gmail.com', '$2y$10$4zjBPZvIGEOYAr9QnjezpO.3si0JegaTC1KkhAdkwiXppwxXRMsVi', NULL, NULL, '09937569024', NULL, '', 'Active', '2026-02-12 04:17:34', '2026-02-12 00:03:58', NULL),
(22, 'Aro', 'Manantan', 'Gabby', 'aro.bckup@gmail.com', '$2y$10$PYywTnY87cTqAKmM8RjbwO/wAfU5T8YKPg1aYW92qOW5o0eYDuA.K', NULL, NULL, '09937569024', NULL, 'Customer', 'Active', '2026-02-12 07:38:57', '2026-02-12 07:39:38', NULL);

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
(8, 8, 'Billing', '6, Sesame St., San Antonio Subd.', 'Quezon City', 'Metro Manila', 'Philippines', '1125', NULL, 0, '2026-01-26 08:54:15', '2026-01-26 08:54:15', '6', 'Sesame St.', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'NCR'),
(9, 13, 'Shipping', '321, Noli Quen St., Samber Subd.', 'Malabon', 'Metro Manila', 'Philippines', '1105', NULL, 0, '2026-02-03 04:48:41', '2026-02-03 04:48:41', '321', 'Noli Quen St.', 'Samber Subd.', 'Brgy. Sapa', 'NCR'),
(10, 11, 'Shipping', '2133, Casa Valencia, San Benissa Garden Villas', 'Quezon City', 'Metro Manila', 'Philippines', '1123', NULL, 1, '2026-02-05 11:31:59', '2026-02-05 11:31:59', '2133', 'Casa Valencia', 'San Benissa Garden Villas', 'Kaligayahan', 'NCR'),
(11, 17, 'Shipping', '6, Noli Quen St., San Antonio Subd.', 'Quezon City', 'Metro Manila', 'Philippines', '1105', NULL, 1, '2026-02-09 03:57:24', '2026-02-09 03:57:24', '6', 'Noli Quen St.', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'NCR'),
(12, 19, 'Shipping', '12, Sesame, San Antonio Subd.', 'Caloocan', 'Metro Manila', 'Philippines', '1125', NULL, 1, '2026-02-10 01:56:04', '2026-02-10 01:56:04', '12', 'Sesame', 'San Antonio Subd.', 'Brgy. Nagkaisang Nayon', 'NCR'),
(13, 20, 'Shipping', 'Tower 5, Trees Residence', 'Las Piñas', 'Metro Manila', 'Philippines', '1118', NULL, 1, '2026-02-11 14:40:51', '2026-02-11 14:40:51', 'Tower 5', '', 'Trees Residence', 'Brgy. Pasong Putik', 'NCR');

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
(2, 9, 22, NULL, '2026-02-07 14:39:30'),
(3, 10, 23, NULL, '2026-02-07 15:31:37');

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
  ADD KEY `idx_quotation` (`QuotationID`),
  ADD KEY `idx_payment_due` (`PaymentDueDate`),
  ADD KEY `idx_installation_completed` (`InstallationCompletedDate`);

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
  ADD UNIQUE KEY `UserID` (`UserID`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_setup_status` (`setup_status`);

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
  ADD KEY `fk_order_ocular_completed_by` (`OcularCompletedBy_ID`),
  ADD KEY `idx_customization_id` (`CustomizationID`);

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
  ADD KEY `idx_payment_date` (`Payment_Date`),
  ADD KEY `idx_payment_milestone` (`payment_milestone`);

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
-- Indexes for table `role_change_log`
--
ALTER TABLE `role_change_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `role_requests`
--
ALTER TABLE `role_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `Cart_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `Customer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `customer_customizations`
--
ALTER TABLE `customer_customizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `customer_notifications`
--
ALTER TABLE `customer_notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `customization`
--
ALTER TABLE `customization`
  MODIFY `CustomizationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `customization_field_configs`
--
ALTER TABLE `customization_field_configs`
  MODIFY `ConfigID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employee_archive`
--
ALTER TABLE `employee_archive`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `enduser_archive`
--
ALTER TABLE `enduser_archive`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `ProductMaterialID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_series`
--
ALTER TABLE `product_series`
  MODIFY `Series_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product_standard_sizes`
--
ALTER TABLE `product_standard_sizes`
  MODIFY `SizeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `product_tag_prices`
--
ALTER TABLE `product_tag_prices`
  MODIFY `TagPriceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=593;

--
-- AUTO_INCREMENT for table `projectschedule`
--
ALTER TABLE `projectschedule`
  MODIFY `Schedule_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `quotation`
--
ALTER TABLE `quotation`
  MODIFY `QuotationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ready_to_approve_orders`
--
ALTER TABLE `ready_to_approve_orders`
  MODIFY `ReadyOrderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_change_log`
--
ALTER TABLE `role_change_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `role_requests`
--
ALTER TABLE `role_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `sales_notif`
--
ALTER TABLE `sales_notif`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_activity_log`
--
ALTER TABLE `system_activity_log`
  MODIFY `ActivityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=313;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `AddressID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `Wishlist_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
