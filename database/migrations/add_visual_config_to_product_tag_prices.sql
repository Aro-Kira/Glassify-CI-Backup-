-- =====================================================
-- Migration: Add VisualConfig column to product_tag_prices
-- Purpose: Store Konva.js visual configuration for tags
-- Date: 2026-01-17
-- =====================================================
-- 
-- IMPORTANT: Run this migration to enable 2D Preview sync from Admin to Customer!
-- This adds the VisualConfig column that stores admin-configured colors/styles.
--
-- =====================================================

-- Add VisualConfig column to store JSON visual configuration
-- This will fail silently if column already exists (which is fine)
SET @dbname = DATABASE();
SET @tablename = "product_tag_prices";
SET @columnname = "VisualConfig";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 'Column already exists.'",
  CONCAT("ALTER TABLE `", @tablename, "` ADD COLUMN `", @columnname, "` JSON DEFAULT NULL COMMENT 'Konva.js visual config JSON' AFTER `ImageUrl`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Alternative simple version (comment out above and use this if prepared statement doesn't work):
-- ALTER TABLE `product_tag_prices` 
-- ADD COLUMN IF NOT EXISTS `VisualConfig` JSON DEFAULT NULL 
-- COMMENT 'Konva.js visual config JSON with advanced effects support' 
-- AFTER `ImageUrl`;

-- =====================================================
-- VISUAL CONFIG JSON STRUCTURE (Full Feature Set)
-- =====================================================
-- {
--   "effectType": "fill|gradient|pattern|shadow|edge|overlay|custom",
--   
--   // Basic properties (always available)
--   "fill": "#E0F2F1",           // Primary fill color
--   "opacity": 0.9,              // Fill opacity (0.1 - 1.0)
--   "stroke": "#333333",         // Stroke/border color
--   "strokeWidth": 4,            // Stroke width in pixels
--   
--   // Gradient properties (effectType: gradient)
--   "gradientEnd": "#FFFFFF",    // Gradient end color
--   "gradientDirection": "vertical|horizontal|diagonal|radial",
--   
--   // Shadow properties (effectType: shadow)
--   "shadowBlur": 10,            // Shadow blur radius
--   "shadowOffset": 5,           // Shadow offset in pixels
--   "shadowColor": "#000000",    // Shadow color
--   "shadowOpacity": 0.3,        // Shadow opacity
--   
--   // Pattern properties (effectType: pattern)
--   "patternType": "none|lines|grid|dots|crosshatch|frosted|rain",
--   "patternDensity": 5,         // Pattern density (1-20)
--   
--   // Edge properties (effectType: edge)
--   "edgeStyle": "solid|dashed|dotted|double|beveled|rounded",
--   "cornerRadius": 0            // Corner radius in pixels
-- }
-- =====================================================

-- Example: Tinted Black Glass with shadow effect
-- UPDATE `product_tag_prices` 
-- SET `VisualConfig` = '{
--   "effectType": "shadow",
--   "fill": "#37474F",
--   "opacity": 0.7,
--   "stroke": "#263238",
--   "strokeWidth": 3,
--   "shadowBlur": 15,
--   "shadowOffset": 5,
--   "shadowColor": "#000000",
--   "shadowOpacity": 0.4
-- }' 
-- WHERE `FieldID` = 'glassType' AND `TagName` = 'Tinted Black';

-- Example: Frosted Glass with pattern
-- UPDATE `product_tag_prices` 
-- SET `VisualConfig` = '{
--   "effectType": "pattern",
--   "fill": "#FFFFFF",
--   "opacity": 0.95,
--   "stroke": "#CCCCCC",
--   "strokeWidth": 2,
--   "patternType": "frosted",
--   "patternDensity": 8
-- }' 
-- WHERE `FieldID` = 'glassType' AND `TagName` = 'Frosted';

-- Example: Gradient Glass (sunset effect)
-- UPDATE `product_tag_prices` 
-- SET `VisualConfig` = '{
--   "effectType": "gradient",
--   "fill": "#FF9800",
--   "opacity": 0.8,
--   "stroke": "#E65100",
--   "strokeWidth": 3,
--   "gradientEnd": "#FFE082",
--   "gradientDirection": "vertical"
-- }' 
-- WHERE `FieldID` = 'glassType' AND `TagName` = 'Sunset Tint';

-- =====================================================
-- NOTE: Run this migration once to add the column
-- The admin can then configure visual styles when adding new tags
-- All fields are optional - Konva.js will use sensible defaults
-- =====================================================
