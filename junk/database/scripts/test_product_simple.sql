-- =====================================================
-- Simple Test Product Insert
-- Run this after add_customization_fields_tables.sql
-- =====================================================

-- Step 1: Insert the test product
INSERT INTO `product` (
    `ProductName`,
    `Category`,
    `Subcategory`,
    `Material`,
    `OrderType`,
    `Price`,
    `PriceMin`,
    `PriceMax`,
    `ImageUrl`,
    `Status`,
    `DateAdded`
) VALUES (
    'Test Sliding Window',
    'Windows',
    'Sliding',
    'Glass',
    'direct',
    1500.00,
    1200.00,
    1800.00,
    '["test-window-1.jpg", "test-window-2.jpg", "test-window-3.jpg"]',
    'In Stock',
    NOW()
);

-- Step 2: Get the Product_ID (replace 999 with the actual Product_ID from step 1)
-- You can find it by running: SELECT MAX(Product_ID) FROM product;
SET @test_product_id = (SELECT MAX(Product_ID) FROM product);

-- Step 3: Insert tag prices
INSERT INTO `product_tag_prices` (`Product_ID`, `FieldID`, `TagName`, `Price`, `Created_Date`) VALUES
(@test_product_id, 'glassType', 'Clear', 0.00, NOW()),
(@test_product_id, 'glassType', 'Tinted', 150.00, NOW()),
(@test_product_id, 'glassType', 'Laminated', 300.00, NOW()),
(@test_product_id, 'frameColor', 'White', 0.00, NOW()),
(@test_product_id, 'frameColor', 'Black', 200.00, NOW()),
(@test_product_id, 'frameColor', 'Silver', 250.00, NOW()),
(@test_product_id, 'frameColor', 'Bronze', 300.00, NOW()),
(@test_product_id, 'frameColor', 'Wood', 500.00, NOW()),
(@test_product_id, 'frameColor', 'Aluminum', 400.00, NOW()),
(@test_product_id, 'thickness', '3mm', -100.00, NOW()),
(@test_product_id, 'thickness', '5mm', 0.00, NOW()),
(@test_product_id, 'thickness', '6mm', 150.00, NOW()),
(@test_product_id, 'thickness', '8mm', 300.00, NOW()),
(@test_product_id, 'screen', 'Yes', 200.00, NOW());

-- Step 4: Insert standard series (one at a time to ensure IDs are correct)
INSERT INTO `product_series` (`Product_ID`, `SeriesName`, `Created_Date`) VALUES
(@test_product_id, 'Standard Series', NOW());

SET @standard_series_id = LAST_INSERT_ID();

INSERT INTO `product_series` (`Product_ID`, `SeriesName`, `Created_Date`) VALUES
(@test_product_id, 'Premium Series', NOW());

SET @premium_series_id = LAST_INSERT_ID();

-- Alternative method if LAST_INSERT_ID() doesn't work:
-- SET @standard_series_id = (SELECT Series_ID FROM product_series WHERE Product_ID = @test_product_id AND SeriesName = 'Standard Series' ORDER BY Series_ID DESC LIMIT 1);
-- SET @premium_series_id = (SELECT Series_ID FROM product_series WHERE Product_ID = @test_product_id AND SeriesName = 'Premium Series' ORDER BY Series_ID DESC LIMIT 1);

-- Step 6: Insert standard sizes for Standard Series
INSERT INTO `product_standard_sizes` (`Series_ID`, `Width`, `Height`, `Price`, `Created_Date`) VALUES
(@standard_series_id, 80.00, 100.00, 1200.00, NOW()),
(@standard_series_id, 100.00, 120.00, 1500.00, NOW()),
(@standard_series_id, 120.00, 150.00, 1800.00, NOW()),
(@standard_series_id, 150.00, 180.00, 2200.00, NOW());

-- Step 7: Insert standard sizes for Premium Series
INSERT INTO `product_standard_sizes` (`Series_ID`, `Width`, `Height`, `Price`, `Created_Date`) VALUES
(@premium_series_id, 80.00, 100.00, 1500.00, NOW()),
(@premium_series_id, 100.00, 120.00, 1800.00, NOW()),
(@premium_series_id, 120.00, 150.00, 2200.00, NOW()),
(@premium_series_id, 150.00, 180.00, 2700.00, NOW());

-- Step 8: Insert field configuration (if not exists)
INSERT INTO `customization_field_configs` (
    `Category`,
    `Subcategory`,
    `FieldKey`,
    `FieldConfig`,
    `Created_Date`
) VALUES (
    'Windows',
    'Sliding',
    'Windows_Sliding',
    '[
        {
            "type": "tags",
            "label": "Glass Type",
            "id": "glassType",
            "options": ["Clear", "Tinted", "Laminated"]
        },
        {
            "type": "tags",
            "label": "Frame Color/Material",
            "id": "frameColor",
            "options": ["White", "Black", "Silver", "Bronze", "Wood", "Aluminum"]
        },
        {
            "type": "number",
            "label": "Thickness (mm)",
            "id": "thickness",
            "min": 1,
            "step": 0.1
        },
        {
            "type": "checkbox",
            "label": "Screen",
            "id": "screen"
        }
    ]',
    NOW()
) ON DUPLICATE KEY UPDATE
    `FieldConfig` = VALUES(`FieldConfig`),
    `Updated_Date` = NOW();

-- Step 9: Verify the test product
SELECT 
    p.Product_ID,
    p.ProductName,
    p.Category,
    p.Subcategory,
    p.OrderType,
    p.PriceMin,
    p.PriceMax,
    COUNT(DISTINCT ptp.TagPriceID) as TagPricesCount,
    COUNT(DISTINCT ps.Series_ID) as SeriesCount,
    COUNT(DISTINCT pss.SizeID) as StandardSizesCount
FROM product p
LEFT JOIN product_tag_prices ptp ON p.Product_ID = ptp.Product_ID
LEFT JOIN product_series ps ON p.Product_ID = ps.Product_ID
LEFT JOIN product_standard_sizes pss ON ps.Series_ID = pss.Series_ID
WHERE p.ProductName = 'Test Sliding Window'
GROUP BY p.Product_ID;
