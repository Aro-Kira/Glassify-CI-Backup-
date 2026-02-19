-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 03:26 PM
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
(8, 1, 2, 7, 1, '2026-01-13 01:30:09');

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
(2, 4, '2025-12-29 13:06:35');

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
(1, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1768049417_69624b09edce7.png', NULL, NULL, NULL, '15750.00', '{\"baseArea\":15750,\"shapeAddon\":0,\"typeAddon\":0,\"thicknessAddon\":0,\"frameAddon\":0,\"edgeAddon\":0,\"total\":15750}', '2026-01-10 12:50:17', '2026-01-10 12:50:17'),
(2, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1768053168_696259b082359.png', NULL, NULL, NULL, '15750.00', NULL, '2026-01-10 13:52:48', '2026-01-10 13:52:48'),
(4, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1768057528_69626ab8af816.png', NULL, NULL, NULL, '15750.00', NULL, '2026-01-10 15:05:28', '2026-01-10 15:05:28'),
(5, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1768227832_696503f8371bd.png', NULL, NULL, NULL, '15750.00', NULL, '2026-01-12 14:23:52', '2026-01-12 14:23:52'),
(7, 1, 2, '45 x 35', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl', 'None', 'uploads/designs/design_1_1768267809_6965a021bcefa.png', NULL, NULL, NULL, '15750.00', '{\"baseArea\":15750,\"shapeAddon\":0,\"typeAddon\":0,\"thicknessAddon\":0,\"frameAddon\":0,\"edgeAddon\":0,\"total\":15750}', '2026-01-13 01:30:09', '2026-01-13 01:30:09');

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
(1, 'Windows', 'Sliding', 'Windows_Sliding', '[\r\n        {\r\n            \"type\": \"tags\",\r\n            \"label\": \"Glass Type\",\r\n            \"id\": \"glassType\",\r\n            \"options\": [\"Clear\", \"Tinted\", \"Laminated\"]\r\n        },\r\n        {\r\n            \"type\": \"tags\",\r\n            \"label\": \"Frame Color/Material\",\r\n            \"id\": \"frameColor\",\r\n            \"options\": [\"White\", \"Black\", \"Silver\", \"Bronze\", \"Wood\", \"Aluminum\"]\r\n        },\r\n        {\r\n            \"type\": \"number\",\r\n            \"label\": \"Thickness (mm)\",\r\n            \"id\": \"thickness\",\r\n            \"min\": 1,\r\n            \"step\": 0.1\r\n        },\r\n        {\r\n            \"type\": \"checkbox\",\r\n            \"label\": \"Screen\",\r\n            \"id\": \"screen\"\r\n        }\r\n    ]', '2026-01-14 12:14:23', '2026-01-14 12:17:11');

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
  `Status` enum('Pending Payment','Paid','Payment Verified','Approved','In Fabrication','Scheduling','For Installation / Shipping','Completed','Cancelled','Returned') DEFAULT 'Pending Payment',
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
(5, 'GI005', 1, 3, '2026-01-12 17:07:40', '18500.00', '', 'Pending', 'E-Wallet', '123 Main Street, Makati City, Metro Manila, Philippines, 1200', 'Please deliver during business hours. Contact customer before delivery.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-02-15', NULL, NULL, NULL, '2026-02-20', '2026-01-12 17:07:40', '2026-01-12 17:07:40', 'Direct', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'GI006', 1, 3, '2026-01-12 17:07:40', '27500.00', '', 'Pending', 'Cash on Delivery', '456 Oak Avenue, Taguig City, Metro Manila, Philippines, 1630', 'Site assessment required. Customer prefers installation in the morning. Access via elevator only.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-02-25', '2026-01-20', NULL, NULL, '2026-03-05', '2026-01-12 17:07:40', '2026-01-12 17:17:28', 'Site-Assessed', 0, 'Site assessment completed on 2026-01-15. Window opening measures 150cm x 200cm. Standard installation procedure applicable. Building has elevator access. No special mounting requirements. Customer prefers morning installation (8AM-12PM). Site is ready for installation.', 3, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Queued', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
(1, 5, 2, NULL, 1, '1500.00', '18500.00', '120cm x 80cm', 'rectangle', 'tempered', '6mm', 'beveled', 'aluminum', 'None', NULL, '2026-01-12 17:07:40'),
(2, 6, 2, NULL, 1, '2500.00', '27500.00', '150cm x 200cm', 'rectangle', 'tempered', '8mm', 'polished', 'aluminum', 'None', NULL, '2026-01-12 17:07:40');

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
  `Status` enum('In Stock','Out of Stock','Low Stock') DEFAULT 'Out of Stock',
  `Subcategory` varchar(100) DEFAULT NULL,
  `OrderType` enum('direct','site-assessment') DEFAULT 'direct',
  `PriceMin` decimal(10,2) DEFAULT NULL,
  `PriceMax` decimal(10,2) DEFAULT NULL,
  `Customization` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: Customer customization selections'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`Product_ID`, `ProductName`, `Category`, `Material`, `Price`, `ImageUrl`, `Description`, `DateAdded`, `Status`, `Subcategory`, `OrderType`, `PriceMin`, `PriceMax`, `Customization`) VALUES
(1, 'Sliding Window', 'Windows', 'Glass', '1500.00', 'e91c3c9737a1ce6f9fe0bab47c5bd6de.jpg', NULL, '2025-12-07 09:34:07', 'Out of Stock', NULL, 'direct', NULL, NULL, NULL),
(2, 'Shower Enclosure', 'Shower Enclosure / Partition', 'Glass', '1000.00', 'fe18e12d50fe63f07ce3b3d97b61c69d.png', NULL, '2025-12-07 10:00:16', 'Out of Stock', NULL, 'direct', NULL, NULL, NULL),
(3, 'Test Sliding Window', 'Windows', 'Glass', '1500.00', '[\"test-window-1.jpg\", \"test-window-2.jpg\", \"test-window-3.jpg\"]', NULL, '2026-01-14 12:13:00', 'Out of Stock', 'Sliding', 'direct', '1200.00', '1800.00', NULL),
(4, 'Test Sliding Window', 'Windows', 'Glass', '1500.00', '[\"test-window-1.jpg\", \"test-window-2.jpg\", \"test-window-3.jpg\"]', NULL, '2026-01-14 12:14:23', 'Out of Stock', 'Sliding', 'direct', '1200.00', '1800.00', NULL),
(5, 'Test Sliding Window', 'Windows', 'Glass', '1500.00', '[\"test-window-1.jpg\", \"test-window-2.jpg\", \"test-window-3.jpg\"]', NULL, '2026-01-14 12:16:35', 'Out of Stock', 'Sliding', 'direct', '1200.00', '1800.00', NULL),
(6, 'Test Sliding Window', 'Windows', 'Glass', '1500.00', '[\"test-window-1.jpg\", \"test-window-2.jpg\", \"test-window-3.jpg\"]', NULL, '2026-01-14 12:17:11', 'Out of Stock', 'Sliding', 'direct', '1200.00', '1800.00', NULL),
(7, 'Mirrors', 'Mirrors & Specialty Glass', 'Glass', '3750.00', '[\"acf9aa699641b9462542b0483b362d6c.png\",\"de23a6f14618010e9cd39466e1e71ba0.png\",\"09ab7b9de6915254b06643ae720145f5.png\",\"d7929b6208bb0ea8a799069631cb2239.png\"]', NULL, '2026-01-14 05:28:09', 'Out of Stock', 'Mirrors', 'direct', '2500.00', '5000.00', '{\"shape\":[\"Round\",\"Rectangle\",\"Oval\"],\"edgeFinish\":[\"Beveled\",\"Polished\",\"Raw\"],\"mountingMethod\":[\"Wall-mounted\",\"Stand\",\"Adhesive\"]}'),
(8, 'Mirrors', 'Mirrors & Specialty Glass', 'Glass', '3750.00', '[\"acf9aa699641b9462542b0483b362d6c.png\",\"de23a6f14618010e9cd39466e1e71ba0.png\",\"09ab7b9de6915254b06643ae720145f5.png\",\"d7929b6208bb0ea8a799069631cb2239.png\"]', NULL, '2026-01-14 05:28:09', 'Out of Stock', 'Mirrors', 'direct', '2500.00', '5000.00', '{\"shape\":[\"Round\",\"Rectangle\",\"Oval\"],\"edgeFinish\":[\"Beveled\",\"Polished\",\"Raw\"],\"mountingMethod\":[\"Wall-mounted\",\"Stand\",\"Adhesive\"]}'),
(9, 'Sliding Doors', 'Doors', 'Glass', '12500.00', '[\"00708009a4af98b68af970f73ff75ff8.png\",\"631e05e5b9b413437c70aebebe730423.png\",\"f2d9267a4b9e31913f07297262f9119a.png\",\"7faa007a111fcc593fd60589322561d4.png\"]', NULL, '2026-01-14 05:43:55', 'Out of Stock', 'Sliding', 'site-assessment', '10000.00', '15000.00', '{\"size\":\"\",\"glassType\":[],\"handleType\":[],\"lockType\":[],\"softClose\":true}');

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
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_series`
--

INSERT INTO `product_series` (`Series_ID`, `Product_ID`, `SeriesName`, `Created_Date`) VALUES
(1, 3, 'Standard Series', '2026-01-14 12:13:00'),
(2, 3, 'Premium Series', '2026-01-14 12:13:00'),
(3, 4, 'Standard Series', '2026-01-14 12:14:23'),
(4, 4, 'Premium Series', '2026-01-14 12:14:23'),
(5, 5, 'Standard Series', '2026-01-14 12:16:35'),
(6, 5, 'Premium Series', '2026-01-14 12:16:35'),
(7, 6, 'Standard Series', '2026-01-14 12:17:11'),
(8, 6, 'Premium Series', '2026-01-14 12:17:11'),
(9, 7, '150 Series', '2026-01-14 05:28:09'),
(10, 9, '700 Series', '2026-01-14 05:43:55');

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
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_standard_sizes`
--

INSERT INTO `product_standard_sizes` (`SizeID`, `Series_ID`, `Width`, `Height`, `Price`, `Created_Date`) VALUES
(5, 3, '80.00', '100.00', '1200.00', '2026-01-14 12:14:23'),
(6, 3, '100.00', '120.00', '1500.00', '2026-01-14 12:14:23'),
(7, 3, '120.00', '150.00', '1800.00', '2026-01-14 12:14:23'),
(8, 3, '150.00', '180.00', '2200.00', '2026-01-14 12:14:23'),
(9, 4, '80.00', '100.00', '1500.00', '2026-01-14 12:14:23'),
(10, 4, '100.00', '120.00', '1800.00', '2026-01-14 12:14:23'),
(11, 4, '120.00', '150.00', '2200.00', '2026-01-14 12:14:23'),
(12, 4, '150.00', '180.00', '2700.00', '2026-01-14 12:14:23'),
(13, 5, '80.00', '100.00', '1200.00', '2026-01-14 12:16:35'),
(14, 5, '100.00', '120.00', '1500.00', '2026-01-14 12:16:35'),
(15, 5, '120.00', '150.00', '1800.00', '2026-01-14 12:16:35'),
(16, 5, '150.00', '180.00', '2200.00', '2026-01-14 12:16:35'),
(17, 6, '80.00', '100.00', '1500.00', '2026-01-14 12:16:35'),
(18, 6, '100.00', '120.00', '1800.00', '2026-01-14 12:16:35'),
(19, 6, '120.00', '150.00', '2200.00', '2026-01-14 12:16:35'),
(20, 6, '150.00', '180.00', '2700.00', '2026-01-14 12:16:35'),
(21, 7, '80.00', '100.00', '1200.00', '2026-01-14 12:17:11'),
(22, 7, '100.00', '120.00', '1500.00', '2026-01-14 12:17:11'),
(23, 7, '120.00', '150.00', '1800.00', '2026-01-14 12:17:11'),
(24, 7, '150.00', '180.00', '2200.00', '2026-01-14 12:17:11'),
(25, 8, '80.00', '100.00', '1500.00', '2026-01-14 12:17:11'),
(26, 8, '100.00', '120.00', '1800.00', '2026-01-14 12:17:11'),
(27, 8, '120.00', '150.00', '2200.00', '2026-01-14 12:17:11'),
(28, 8, '150.00', '180.00', '2700.00', '2026-01-14 12:17:11'),
(29, 9, '150.00', '150.00', '2500.00', '2026-01-14 05:28:09');

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
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_tag_prices`
--

INSERT INTO `product_tag_prices` (`TagPriceID`, `Product_ID`, `FieldID`, `TagName`, `Price`, `Created_Date`) VALUES
(1, 3, 'glassType', 'Clear', '0.00', '2026-01-14 12:13:00'),
(2, 3, 'glassType', 'Tinted', '150.00', '2026-01-14 12:13:00'),
(3, 3, 'glassType', 'Laminated', '300.00', '2026-01-14 12:13:00'),
(4, 3, 'frameColor', 'White', '0.00', '2026-01-14 12:13:00'),
(5, 3, 'frameColor', 'Black', '200.00', '2026-01-14 12:13:00'),
(6, 3, 'frameColor', 'Silver', '250.00', '2026-01-14 12:13:00'),
(7, 3, 'frameColor', 'Bronze', '300.00', '2026-01-14 12:13:00'),
(8, 3, 'frameColor', 'Wood', '500.00', '2026-01-14 12:13:00'),
(9, 3, 'frameColor', 'Aluminum', '400.00', '2026-01-14 12:13:00'),
(10, 3, 'thickness', '3mm', '-100.00', '2026-01-14 12:13:00'),
(11, 3, 'thickness', '5mm', '0.00', '2026-01-14 12:13:00'),
(12, 3, 'thickness', '6mm', '150.00', '2026-01-14 12:13:00'),
(13, 3, 'thickness', '8mm', '300.00', '2026-01-14 12:13:00'),
(14, 3, 'screen', 'Yes', '200.00', '2026-01-14 12:13:00'),
(15, 4, 'glassType', 'Clear', '0.00', '2026-01-14 12:14:23'),
(16, 4, 'glassType', 'Tinted', '150.00', '2026-01-14 12:14:23'),
(17, 4, 'glassType', 'Laminated', '300.00', '2026-01-14 12:14:23'),
(18, 4, 'frameColor', 'White', '0.00', '2026-01-14 12:14:23'),
(19, 4, 'frameColor', 'Black', '200.00', '2026-01-14 12:14:23'),
(20, 4, 'frameColor', 'Silver', '250.00', '2026-01-14 12:14:23'),
(21, 4, 'frameColor', 'Bronze', '300.00', '2026-01-14 12:14:23'),
(22, 4, 'frameColor', 'Wood', '500.00', '2026-01-14 12:14:23'),
(23, 4, 'frameColor', 'Aluminum', '400.00', '2026-01-14 12:14:23'),
(24, 4, 'thickness', '3mm', '-100.00', '2026-01-14 12:14:23'),
(25, 4, 'thickness', '5mm', '0.00', '2026-01-14 12:14:23'),
(26, 4, 'thickness', '6mm', '150.00', '2026-01-14 12:14:23'),
(27, 4, 'thickness', '8mm', '300.00', '2026-01-14 12:14:23'),
(28, 4, 'screen', 'Yes', '200.00', '2026-01-14 12:14:23'),
(29, 5, 'glassType', 'Clear', '0.00', '2026-01-14 12:16:35'),
(30, 5, 'glassType', 'Tinted', '150.00', '2026-01-14 12:16:35'),
(31, 5, 'glassType', 'Laminated', '300.00', '2026-01-14 12:16:35'),
(32, 5, 'frameColor', 'White', '0.00', '2026-01-14 12:16:35'),
(33, 5, 'frameColor', 'Black', '200.00', '2026-01-14 12:16:35'),
(34, 5, 'frameColor', 'Silver', '250.00', '2026-01-14 12:16:35'),
(35, 5, 'frameColor', 'Bronze', '300.00', '2026-01-14 12:16:35'),
(36, 5, 'frameColor', 'Wood', '500.00', '2026-01-14 12:16:35'),
(37, 5, 'frameColor', 'Aluminum', '400.00', '2026-01-14 12:16:35'),
(38, 5, 'thickness', '3mm', '-100.00', '2026-01-14 12:16:35'),
(39, 5, 'thickness', '5mm', '0.00', '2026-01-14 12:16:35'),
(40, 5, 'thickness', '6mm', '150.00', '2026-01-14 12:16:35'),
(41, 5, 'thickness', '8mm', '300.00', '2026-01-14 12:16:35'),
(42, 5, 'screen', 'Yes', '200.00', '2026-01-14 12:16:35'),
(43, 6, 'glassType', 'Clear', '0.00', '2026-01-14 12:17:11'),
(44, 6, 'glassType', 'Tinted', '150.00', '2026-01-14 12:17:11'),
(45, 6, 'glassType', 'Laminated', '300.00', '2026-01-14 12:17:11'),
(46, 6, 'frameColor', 'White', '0.00', '2026-01-14 12:17:11'),
(47, 6, 'frameColor', 'Black', '200.00', '2026-01-14 12:17:11'),
(48, 6, 'frameColor', 'Silver', '250.00', '2026-01-14 12:17:11'),
(49, 6, 'frameColor', 'Bronze', '300.00', '2026-01-14 12:17:11'),
(50, 6, 'frameColor', 'Wood', '500.00', '2026-01-14 12:17:11'),
(51, 6, 'frameColor', 'Aluminum', '400.00', '2026-01-14 12:17:11'),
(52, 6, 'thickness', '3mm', '-100.00', '2026-01-14 12:17:11'),
(53, 6, 'thickness', '5mm', '0.00', '2026-01-14 12:17:11'),
(54, 6, 'thickness', '6mm', '150.00', '2026-01-14 12:17:11'),
(55, 6, 'thickness', '8mm', '300.00', '2026-01-14 12:17:11'),
(56, 6, 'screen', 'Yes', '200.00', '2026-01-14 12:17:11');

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
(5, 'fa-shopping-cart', 'Client/Customer', 'New Order: GI004 has been created and requires your review', 'Unread', '2026-01-12 15:24:05', NULL, 4, 'Order');

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
(4, 'Admin', 'Super', '', 'testing.admin@gmail.com', '$2y$10$ahTPP9RAI9s/hfSnNuRKyuT6Ik2WQjRI.u1sj0/PWUkWbVuj4VIJe', NULL, NULL, '09937568011', NULL, 'Admin', 'Active', '2025-12-29 13:06:35', '2025-12-29 13:09:19', NULL);

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
(1, 1, 'Shipping', '35 Malasimbo', 'Quezon City', 'Metro Manila', 'Philippines', '1102', NULL, 0, '2025-12-07 16:24:30', '2025-12-07 16:24:30');

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
  ADD KEY `idx_staff` (`AssignedStaff_ID`),
  ADD KEY `idx_appointment_type` (`AppointmentType`),
  ADD KEY `idx_assigned_staff_id` (`AssignedStaff_ID`);

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
-- Indexes for table `customization_field_configs`
--
ALTER TABLE `customization_field_configs`
  ADD PRIMARY KEY (`ConfigID`),
  ADD UNIQUE KEY `unique_field_key` (`FieldKey`),
  ADD KEY `idx_category_subcategory` (`Category`,`Subcategory`);

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
  ADD KEY `idx_field_id` (`FieldID`);

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
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `Cart_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `Customer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customization`
--
ALTER TABLE `customization`
  MODIFY `CustomizationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customization_field_configs`
--
ALTER TABLE `customization_field_configs`
  MODIFY `ConfigID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `Issue_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `TagPriceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

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
  MODIFY `ReadyOrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales_notif`
--
ALTER TABLE `sales_notif`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_activity_log`
--
ALTER TABLE `system_activity_log`
  MODIFY `ActivityID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `AddressID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `Wishlist_ID` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `fk_order_fabrication_staff` FOREIGN KEY (`FabricationStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_installation_staff` FOREIGN KEY (`InstallationStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_ocular_completed_by` FOREIGN KEY (`OcularCompletedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
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
-- Constraints for table `product_materials`
--
ALTER TABLE `product_materials`
  ADD CONSTRAINT `fk_product_materials_inventory` FOREIGN KEY (`InventoryItemID`) REFERENCES `inventory_items` (`InventoryItemID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_materials_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE;

--
-- Constraints for table `product_series`
--
ALTER TABLE `product_series`
  ADD CONSTRAINT `fk_series_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE;

--
-- Constraints for table `product_standard_sizes`
--
ALTER TABLE `product_standard_sizes`
  ADD CONSTRAINT `fk_sizes_series` FOREIGN KEY (`Series_ID`) REFERENCES `product_series` (`Series_ID`) ON DELETE CASCADE;

--
-- Constraints for table `product_tag_prices`
--
ALTER TABLE `product_tag_prices`
  ADD CONSTRAINT `fk_tag_prices_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE;

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
