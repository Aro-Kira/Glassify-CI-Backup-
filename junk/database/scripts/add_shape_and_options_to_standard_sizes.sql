-- Add OtherOptions and Unit columns to product_standard_sizes table
-- This allows storing customization options and original units for each measurement

-- Add OtherOptions column for customization data (JSON)
ALTER TABLE `product_standard_sizes` 
ADD COLUMN `OtherOptions` TEXT NULL DEFAULT NULL COMMENT 'Customization options stored as JSON' AFTER `Price`;

-- Add unit columns to store original measurement units
ALTER TABLE `product_standard_sizes` 
ADD COLUMN `WidthUnit` VARCHAR(10) NULL DEFAULT 'in' COMMENT 'Unit for width (in, cm, mm)' AFTER `Width`,
ADD COLUMN `HeightUnit` VARCHAR(10) NULL DEFAULT 'in' COMMENT 'Unit for height (in, cm, mm)' AFTER `Height`;
