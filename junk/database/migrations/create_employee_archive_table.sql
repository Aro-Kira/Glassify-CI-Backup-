-- Migration: Create employee_archive table
-- This table stores archived employees (Admin, Sales Representative) separately from active users

CREATE TABLE IF NOT EXISTS `employee_archive` (
  `ArchiveID` int(11) NOT NULL AUTO_INCREMENT,
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
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When this employee was archived',
  PRIMARY KEY (`ArchiveID`),
  KEY `UserID` (`UserID`),
  KEY `Email` (`Email`),
  KEY `ArchivedAt` (`ArchivedAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
