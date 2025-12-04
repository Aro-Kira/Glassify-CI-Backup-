-- Create separate customization tables for each product category

-- 1. Mirror Customization Table
CREATE TABLE IF NOT EXISTS `mirror_customization` (
  `CustomizationID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL COMMENT 'Height x Width (or Diameter for Circle)',
  `EdgeWork` varchar(50) DEFAULT NULL COMMENT 'polished, beveled, same lang',
  `GlassShape` varchar(50) DEFAULT NULL COMMENT 'Rectangle, Circle, Oval, Arch, Capsule',
  `LEDBacklight` varchar(50) DEFAULT NULL COMMENT 'Optional',
  `Engraving` varchar(255) DEFAULT NULL COMMENT 'Optional',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`CustomizationID`),
  KEY `Customer_ID` (`Customer_ID`),
  KEY `Product_ID` (`Product_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Shower Enclosure / Partition Customization Table
CREATE TABLE IF NOT EXISTS `shower_enclosure_customization` (
  `CustomizationID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL COMMENT 'Height x Width',
  `GlassType` varchar(50) DEFAULT NULL COMMENT 'same as default',
  `GlassThickness` varchar(50) DEFAULT NULL COMMENT '6mm, 8mm, 10mm, 12mm',
  `FrameType` varchar(50) DEFAULT NULL COMMENT 'Framed, Semi-Frameless, Frameless',
  `Engraving` varchar(255) DEFAULT NULL COMMENT 'optional',
  `DoorOperation` varchar(50) DEFAULT NULL COMMENT 'Swing, Sliding, Fixed',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`CustomizationID`),
  KEY `Customer_ID` (`Customer_ID`),
  KEY `Product_ID` (`Product_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Aluminum Doors Customization Table
CREATE TABLE IF NOT EXISTS `aluminum_doors_customization` (
  `CustomizationID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL,
  `GlassType` varchar(50) DEFAULT NULL COMMENT 'same as default',
  `GlassThickness` varchar(50) DEFAULT NULL COMMENT '6mm, 10mm',
  `Configuration` varchar(50) DEFAULT NULL COMMENT '2-panel slider, 3-panel slider, 4-panel slider',
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`CustomizationID`),
  KEY `Customer_ID` (`Customer_ID`),
  KEY `Product_ID` (`Product_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Aluminum and Bathroom Doors Customization Table
CREATE TABLE IF NOT EXISTS `aluminum_bathroom_doors_customization` (
  `CustomizationID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Dimensions` varchar(255) DEFAULT NULL,
  `FrameType` varchar(50) DEFAULT NULL,
  `EstimatePrice` decimal(10,2) DEFAULT 0.00,
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`CustomizationID`),
  KEY `Customer_ID` (`Customer_ID`),
  KEY `Product_ID` (`Product_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

