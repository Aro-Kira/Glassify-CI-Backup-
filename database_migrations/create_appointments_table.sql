-- Create appointments table for tracking order progress
-- This table stores appointment/progress information for approved orders

CREATE TABLE IF NOT EXISTS `appointments` (
  `AppointmentID` int(11) NOT NULL AUTO_INCREMENT,
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
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`AppointmentID`),
  KEY `idx_orderid` (`OrderID`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_service` (`Service`),
  KEY `idx_status` (`Status`),
  KEY `idx_date` (`AppointmentDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

