-- Create enduser table to store customer information
-- This table will reference the user table for customer details
CREATE TABLE IF NOT EXISTS `enduser` (
  `EndUser_ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `PhoneNum` varchar(13) NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `Date_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Last_Active` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`EndUser_ID`),
  UNIQUE KEY `UserID` (`UserID`),
  UNIQUE KEY `Email` (`Email`),
  CONSTRAINT `enduser_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Populate enduser table with existing customers from user table
INSERT INTO `enduser` (`UserID`, `First_Name`, `Last_Name`, `Middle_Name`, `Email`, `PhoneNum`, `Status`, `Date_Created`, `Date_Updated`)
SELECT 
    `UserID`,
    `First_Name`,
    `Last_Name`,
    `Middle_Name`,
    `Email`,
    `PhoneNum`,
    `Status`,
    `Date_Created`,
    `Date_Updated`
FROM `user`
WHERE `Role` = 'Customer'
ON DUPLICATE KEY UPDATE
    `First_Name` = VALUES(`First_Name`),
    `Last_Name` = VALUES(`Last_Name`),
    `Middle_Name` = VALUES(`Middle_Name`),
    `Email` = VALUES(`Email`),
    `PhoneNum` = VALUES(`PhoneNum`),
    `Status` = VALUES(`Status`),
    `Date_Updated` = CURRENT_TIMESTAMP;

