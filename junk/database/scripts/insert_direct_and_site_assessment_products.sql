-- =====================================================
-- INSERT Statements for Direct and Site-Assessment Products
-- =====================================================
-- This file contains INSERT statements for:
-- 1. Direct order products (Product IDs 10-20)
-- 2. Site-assessment order products (Product IDs 21-30)
-- 3. Updated tag prices with images (broken image icons for missing images)
--
-- Usage: Import this file into your database after the main schema is created
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- =====================================================
-- INSERT Direct Order Products
-- =====================================================
INSERT INTO `product` (`Product_ID`, `ProductName`, `Category`, `Material`, `Price`, `ImageUrl`, `Description`, `DateAdded`, `Status`, `Subcategory`, `OrderType`, `PriceMin`, `PriceMax`, `Customization`) VALUES
(10, '900 Series Sliding Window', 'Windows', 'Aluminum', 15000.00, 'assets/images/broken-image-icon.png', 'Two-panel horizontal sliding window for direct orders', '2026-01-15 08:00:00', 'In Stock', 'Sliding', 'direct', 12000.00, 20000.00, NULL),
(11, '798 Series Sliding Window', 'Windows', 'Aluminum', 14000.00, 'assets/images/broken-image-icon.png', 'Two-panel horizontal sliding window with sleek design', '2026-01-15 08:00:00', 'In Stock', 'Sliding', 'direct', 11000.00, 18000.00, NULL),
(12, '38 Series Awning Window', 'Windows', 'Aluminum', 12000.00, 'assets/images/broken-image-icon.png', 'Top-hinged window that opens outwards from bottom', '2026-01-15 08:00:00', 'In Stock', 'Awning', 'direct', 10000.00, 15000.00, NULL),
(13, '38 Series Casement', 'Windows', 'Aluminum', 13000.00, 'assets/images/broken-image-icon.png', 'Single-panel vertical window that opens outwards from side', '2026-01-15 08:00:00', 'In Stock', 'Casement', 'direct', 11000.00, 16000.00, NULL),
(14, 'Frameless Round Mirror', 'Mirrors & Specialty Glass', 'Glass', 5000.00, 'assets/images/broken-image-icon.png', 'Classic round mirror without frame', '2026-01-15 08:00:00', 'In Stock', 'Mirrors', 'direct', 4000.00, 7000.00, NULL),
(15, 'Gold Framed Round Mirror', 'Mirrors & Specialty Glass', 'Glass', 8000.00, 'assets/images/broken-image-icon.png', 'Round mirror with gold frame', '2026-01-15 08:00:00', 'In Stock', 'Mirrors', 'direct', 6000.00, 10000.00, NULL),
(16, 'Black Framed Round Mirror', 'Mirrors & Specialty Glass', 'Glass', 7500.00, 'assets/images/broken-image-icon.png', 'Round mirror with black frame', '2026-01-15 08:00:00', 'In Stock', 'Mirrors', 'direct', 5500.00, 9500.00, NULL),
(17, 'Frameless Glass Partition', 'Glass Partitions & Enclosures', 'Glass', 18000.00, 'assets/images/broken-image-icon.png', 'Frameless glass partition for office spaces', '2026-01-15 08:00:00', 'In Stock', 'Frameless Glass', 'direct', 15000.00, 25000.00, NULL),
(18, 'L-Shape Shower Enclosure', 'Glass Partitions & Enclosures', 'Glass', 20000.00, 'assets/images/broken-image-icon.png', 'L-shaped corner shower enclosure', '2026-01-15 08:00:00', 'In Stock', 'Shower Enclosure', 'direct', 16000.00, 28000.00, NULL),
(19, 'Swing Glass Door', 'Glass Doors', 'Glass', 22000.00, 'assets/images/broken-image-icon.png', 'Swing door with fixed side panel and transom above', '2026-01-15 08:00:00', 'In Stock', 'Swing Door', 'direct', 18000.00, 30000.00, NULL),
(20, '4 Panel Sliding Door', 'Glass Doors', 'Aluminum', 25000.00, 'assets/images/broken-image-icon.png', 'Multi-panel sliding door system', '2026-01-15 08:00:00', 'In Stock', 'Sliding Door', 'direct', 20000.00, 35000.00, NULL);

-- =====================================================
-- INSERT Site-Assessment Order Products
-- =====================================================
INSERT INTO `product` (`Product_ID`, `ProductName`, `Category`, `Material`, `Price`, `ImageUrl`, `Description`, `DateAdded`, `Status`, `Subcategory`, `OrderType`, `PriceMin`, `PriceMax`, `Customization`) VALUES
(21, 'Custom Glass Windows - Site Assessment', 'Windows', 'Glass', 0.00, 'assets/images/broken-image-icon.png', 'Custom glass windows requiring site assessment and measurements', '2026-01-15 08:00:00', 'In Stock', 'Custom', 'site-assessment', NULL, NULL, NULL),
(22, 'Custom Aluminum Windows - Site Assessment', 'Windows', 'Aluminum', 0.00, 'assets/images/broken-image-icon.png', 'Custom aluminum windows requiring site assessment and measurements', '2026-01-15 08:00:00', 'In Stock', 'Custom', 'site-assessment', NULL, NULL, NULL),
(23, 'Custom Mirror Installation - Site Assessment', 'Mirrors & Specialty Glass', 'Glass', 0.00, 'assets/images/broken-image-icon.png', 'Custom mirror installation requiring site assessment', '2026-01-15 08:00:00', 'In Stock', 'Mirrors', 'site-assessment', NULL, NULL, NULL),
(24, 'Custom Glass Partition - Site Assessment', 'Glass Partitions & Enclosures', 'Glass', 0.00, 'assets/images/broken-image-icon.png', 'Custom glass partition requiring site assessment and measurements', '2026-01-15 08:00:00', 'In Stock', 'Frameless Glass', 'site-assessment', NULL, NULL, NULL),
(25, 'Custom Shower Enclosure - Site Assessment', 'Glass Partitions & Enclosures', 'Glass', 0.00, 'assets/images/broken-image-icon.png', 'Custom shower enclosure requiring site assessment and measurements', '2026-01-15 08:00:00', 'In Stock', 'Shower Enclosure', 'site-assessment', NULL, NULL, NULL),
(26, 'Custom Glass Door - Site Assessment', 'Glass Doors', 'Glass', 0.00, 'assets/images/broken-image-icon.png', 'Custom glass door requiring site assessment and measurements', '2026-01-15 08:00:00', 'In Stock', 'Custom', 'site-assessment', NULL, NULL, NULL),
(27, 'Custom Aluminum Door - Site Assessment', 'Glass Doors', 'Aluminum', 0.00, 'assets/images/broken-image-icon.png', 'Custom aluminum door requiring site assessment and measurements', '2026-01-15 08:00:00', 'In Stock', 'Custom', 'site-assessment', NULL, NULL, NULL),
(28, 'Custom Stair Railings - Site Assessment', 'Stair Railings', 'Glass', 0.00, 'assets/images/broken-image-icon.png', 'Custom stair railings requiring site assessment and measurements', '2026-01-15 08:00:00', 'In Stock', 'Custom', 'site-assessment', NULL, NULL, NULL),
(29, 'Custom Glass Board - Site Assessment', 'Mirrors & Specialty Glass', 'Glass', 0.00, 'assets/images/broken-image-icon.png', 'Custom glass board requiring site assessment and measurements', '2026-01-15 08:00:00', 'In Stock', 'Glass Board', 'site-assessment', NULL, NULL, NULL),
(30, 'Custom Specialty Glass - Site Assessment', 'Mirrors & Specialty Glass', 'Glass', 0.00, 'assets/images/broken-image-icon.png', 'Custom specialty glass requiring site assessment and measurements', '2026-01-15 08:00:00', 'In Stock', 'Specialty', 'site-assessment', NULL, NULL, NULL);

-- =====================================================
-- UPDATE Tag Prices with Images
-- =====================================================
-- Note: Only a few tags have actual images, rest use broken image icon
-- Product 3 tags
UPDATE `product_tag_prices` SET `ImageUrl` = 'uploads/tags/clear-glass.png' WHERE `TagPriceID` = 1;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 2;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 3;
UPDATE `product_tag_prices` SET `ImageUrl` = 'uploads/tags/white-frame.png' WHERE `TagPriceID` = 4;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 5;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 6;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 7;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 8;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 9;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 10;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 11;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 12;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 13;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 14;

-- Product 4 tags
UPDATE `product_tag_prices` SET `ImageUrl` = 'uploads/tags/clear-glass.png' WHERE `TagPriceID` = 15;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 16;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 17;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 18;
UPDATE `product_tag_prices` SET `ImageUrl` = 'uploads/tags/black-frame.png' WHERE `TagPriceID` = 19;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 20;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 21;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 22;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 23;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 24;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 25;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 26;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 27;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 28;

-- Product 5 tags
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 29;
UPDATE `product_tag_prices` SET `ImageUrl` = 'uploads/tags/tinted-glass.png' WHERE `TagPriceID` = 30;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 31;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 32;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 33;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 34;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 35;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 36;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 37;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 38;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 39;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 40;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 41;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 42;

-- Product 6 tags
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 43;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 44;
UPDATE `product_tag_prices` SET `ImageUrl` = 'uploads/tags/laminated-glass.png' WHERE `TagPriceID` = 45;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 46;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 47;
UPDATE `product_tag_prices` SET `ImageUrl` = 'uploads/tags/silver-frame.png' WHERE `TagPriceID` = 48;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 49;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 50;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 51;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 52;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 53;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 54;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 55;
UPDATE `product_tag_prices` SET `ImageUrl` = 'assets/images/broken-image-icon.png' WHERE `TagPriceID` = 56;

-- =====================================================
-- Update AUTO_INCREMENT for product table
-- =====================================================
ALTER TABLE `product` AUTO_INCREMENT = 31;

COMMIT;
