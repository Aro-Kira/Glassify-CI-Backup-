-- Create Order Page table to store order details from customization table
CREATE TABLE IF NOT EXISTS `order_page` (
  `OrderPageID` int(11) NOT NULL AUTO_INCREMENT,
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
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`OrderPageID`),
  KEY `idx_orderid` (`OrderID`),
  KEY `idx_status` (`Status`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_salesrep` (`SalesRep_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

