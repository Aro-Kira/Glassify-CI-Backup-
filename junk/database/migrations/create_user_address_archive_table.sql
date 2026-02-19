-- Migration: Create user_address_archive table
-- This table stores archived addresses separately from active addresses

CREATE TABLE IF NOT EXISTS `user_address_archive` (
  `ArchiveID` int(11) NOT NULL AUTO_INCREMENT,
  `AddressID` int(11) NOT NULL COMMENT 'Original AddressID from user_address',
  `UserID` int(11) NOT NULL,
  `AddressType` enum('Shipping','Billing') NOT NULL DEFAULT 'Shipping',
  `AddressLine` varchar(255) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Province` varchar(100) DEFAULT NULL,
  `Country` varchar(100) DEFAULT 'Philippines',
  `ZipCode` varchar(20) DEFAULT NULL,
  `Note` text DEFAULT NULL,
  `IsDefault` tinyint(1) DEFAULT 0,
  `Created_Date` timestamp NULL DEFAULT NULL COMMENT 'Original creation date',
  `Updated_Date` timestamp NULL DEFAULT NULL COMMENT 'Last update before archiving',
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When this address was archived',
  PRIMARY KEY (`ArchiveID`),
  KEY `UserID` (`UserID`),
  KEY `AddressID` (`AddressID`),
  KEY `ArchivedAt` (`ArchivedAt`),
  CONSTRAINT `user_address_archive_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
