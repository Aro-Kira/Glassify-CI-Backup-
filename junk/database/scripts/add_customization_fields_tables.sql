-- =====================================================
-- Customization Field Configurations Table
-- Stores field definitions for each category/subcategory
-- =====================================================

CREATE TABLE IF NOT EXISTS `customization_field_configs` (
  `ConfigID` int(11) NOT NULL AUTO_INCREMENT,
  `Category` varchar(100) NOT NULL,
  `Subcategory` varchar(100) NOT NULL,
  `FieldKey` varchar(200) NOT NULL COMMENT 'Unique key: Category_Subcategory',
  `FieldConfig` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON array of field definitions',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ConfigID`),
  UNIQUE KEY `unique_field_key` (`FieldKey`),
  KEY `idx_category_subcategory` (`Category`, `Subcategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- Product Tag Prices Table
-- Stores prices for each tag/option in customization fields
-- =====================================================

CREATE TABLE IF NOT EXISTS `product_tag_prices` (
  `TagPriceID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_ID` int(11) NOT NULL,
  `FieldID` varchar(100) NOT NULL COMMENT 'Field identifier (e.g., glassType, frameColor)',
  `TagName` varchar(255) NOT NULL COMMENT 'Tag/option name',
  `Price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price for this tag/option',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`TagPriceID`),
  KEY `idx_product_id` (`Product_ID`),
  KEY `idx_field_id` (`FieldID`),
  CONSTRAINT `fk_tag_prices_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- Product Series Table
-- Stores series names for standard sizes
-- =====================================================

CREATE TABLE IF NOT EXISTS `product_series` (
  `Series_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Product_ID` int(11) NOT NULL,
  `SeriesName` varchar(255) NOT NULL COMMENT 'Name of the series (e.g., "Standard Series", "Premium Series")',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Series_ID`),
  KEY `idx_product_id` (`Product_ID`),
  CONSTRAINT `fk_series_product` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- Product Standard Sizes Table
-- Stores standard size measurements for each series
-- =====================================================

CREATE TABLE IF NOT EXISTS `product_standard_sizes` (
  `SizeID` int(11) NOT NULL AUTO_INCREMENT,
  `Series_ID` int(11) NOT NULL,
  `Width` decimal(10,2) NOT NULL COMMENT 'Width in cm',
  `Height` decimal(10,2) NOT NULL COMMENT 'Height in cm',
  `Price` decimal(10,2) NOT NULL COMMENT 'Price for this specific size',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`SizeID`),
  KEY `idx_series_id` (`Series_ID`),
  CONSTRAINT `fk_sizes_series` FOREIGN KEY (`Series_ID`) REFERENCES `product_series` (`Series_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- Update Product Table
-- Add missing columns if they don't exist
-- =====================================================

-- Add Subcategory column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = 'product';
SET @columnname = 'Subcategory';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add OrderType column if it doesn't exist
SET @columnname = 'OrderType';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' ENUM(\'direct\', \'site-assessment\') DEFAULT \'direct\'')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add PriceMin column if it doesn't exist
SET @columnname = 'PriceMin';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(10,2) DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add PriceMax column if it doesn't exist
SET @columnname = 'PriceMax';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(10,2) DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add Customization column if it doesn't exist (for storing customer selections, not field configs)
SET @columnname = 'Customization';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT \'JSON: Customer customization selections\'')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
