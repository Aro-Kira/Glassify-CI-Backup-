CREATE TABLE IF NOT EXISTS `product` (
  `Product_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProductName` varchar(100) NOT NULL,
  `Category` varchar(50) NOT NULL,
  `Material` enum('Glass','Aluminum') NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `ImageUrl` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status` enum('In Stock','Out of Stock','Low Stock') DEFAULT 'Out of Stock',
  `Subcategory` varchar(100) DEFAULT NULL,
  `OrderType` enum('direct','site-assessment') DEFAULT 'direct',
  `PriceMin` decimal(10,2) DEFAULT NULL,
  `PriceMax` decimal(10,2) DEFAULT NULL,
  `Customization` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: Customer customization selections',
  PRIMARY KEY (`Product_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `product` (`Product_ID`, `ProductName`, `Category`, `Material`, `Price`, `ImageUrl`, `Description`, `DateAdded`, `Status`, `Subcategory`, `OrderType`, `PriceMin`, `PriceMax`, `Customization`) VALUES
(INSERT INTO `product` (`Product_ID`, `ProductName`, `Category`, `Material`, `Price`, `ImageUrl`, `Description`, `DateAdded`, `Status`, `Subcategory`, `OrderType`, `PriceMin`, `PriceMax`, `Customization`) VALUES
(1, 'Sliding Window', 'Windows', 'Glass', '4300.00', '["08973984eef0bdc173063b3debb2df7f.jpg","a9719fddcbc0baecacb9d6c4ec61ae3e.jpg","b0911580251a5a6d8d20741598a5179a.jpg"]', NULL, '2026-01-21 02:55:09', 'In Stock', 'Sliding', 'direct', '1600.00', '7000.00', '{"numberOfPanels":["2 Panels","4 Panels"],"transomType":["Fixed Transom Sill (Fixed glass at bottom)","Fixed Transom Head (Fixed glass at top)","None"],"trackSystem":["2 Tracks","3 Tracks"],"panelConfiguration":["F | S | S | F (Fixed | Sliding | Sliding | Fixed)","S | S | S | S (All Sliding)","F | S (Fixed | Sliding)","S | S (Sliding | Sliding)"],"frameColor":["Powder Coated White","Analok","Matte Black","Matte Gray","Wood Finish"],"glassType":["Ordinary","Reflective","Tempered"],"glassColor":["Bronze","Clear","Smoked","Frosted"],"glassThickness":["6mm","10mm","8mm","12mm"],"lockType":["Center Lok 904 Big","Flushlok #12","New Auto Flushlock","Durable Flushlok"],"rollerType":["Single Panel Roller","Blue Single Roller","Blue Double Roller"],"screen":["With Screen","Without Screen"]}'),
(2, 'Awning Window', 'Windows', 'Glass', '4000.00', '["8891bbe9173e296dabda55ccd6e132e1.jpg"]', NULL, '2026-01-21 03:19:09', 'In Stock', 'Awning', 'direct', '2000.00', '6000.00', '{"glassType":["Ordinary","Tempered","Reflective"],"glassColor":["Clear","Frosted","Bronze","Smoked"],"frameColor":["Powder Coated White","Matte Gray","Wood Finish","Analok","Matte Black"],"operation":["Awning (crank-out)","Awning (push-out)"],"sizeConfiguration":["Single panel","Multiple panels"],"openingDirection":["Top-hinged"],"thickness":["6mm","10mm","8mm","12mm"],"screen":["With Screen","Without Screen"]}'),
(3, 'Casement Window', 'Windows', 'Glass', '1500.00', '["4a04a1a313222ae5f585308c10aa4908.jpg"]', NULL, '2026-01-21 04:03:04', 'In Stock', 'Casement', 'direct', '1000.00', '2000.00', '{"transomType":["None","Casement w/FTS","Casement w/FTH"],"panelConfiguration":["1","4","2","5","3","6"],"width":"","height":"","h1":"","frameColor":["Wood Finish","Matte Gray","Powder Coated White","Analok","Matte Black"],"glassColor":["Bronze","Smoked","Clear","Frosted"],"glassType":["Reflective","Ordinary","Tempered"],"thickness":["10mm","6mm","8mm","12mm"]}'),
(4, 'Sliding Door', 'Doors', 'Glass', '2000.00', '["6946d23f9ab77a37b945e908744736cf.jpg"]', NULL, '2026-01-21 04:04:18', 'In Stock', 'Sliding', 'site-assessment', '1000.00', '3000.00', '{"glassType":["Clear","Tinted","Low-E","Frosted","Tempered","Laminated","Laminated safety glass"],"frameColor":["Aluminum","White","Black","Bronze","Brown (wood-look)","Silver","Custom colors"],"panelCount":["2-panel","4-panel","3-panel"],"operation":["Sliding (single)","Sliding (multi-track)","Sliding (double)"],"panelConfiguration":["Central sliding panels with fixed outer panels","All sliding","2 sliding + 2 fixed","3 sliding","2 sliding only"],"handleType":["Various pull handles","Knob handles","Bar-style","Square handles","Round","Square matte black"],"hardwareFinish":["Chrome/Stainless Steel","Polished Chrome/Stainless Steel","Black Matte","Brushed Nickel","Gold","Bronze"],"softClose":false}'),
(5, 'Mirrors', 'Mirrors & Specialty Glass', 'Glass', '2500.00', '["1c00867eac693e151f3663db39b397a2.jpg","4e5f72c1809586c8f265ba2fb39318b2.jpg"]', NULL, '2026-01-21 06:52:35', 'In Stock', 'Mirrors', 'direct', '2000.00', '3000.00', '{"shape":["Round","Rectangle","Oval","Square"],"cornerRadius":"","frameType":["Frameless","Framed"],"frameColor":["White","Gold","Black","Machine Polished Edges","Beveled Edge"],"glassType":["Copper Free and Lead Free Mirror"],"thickness":["6mm"],"tintFinish":["Bronze tint/color","Grey tint (smoked)","Colored glass"],"lighting":["Integrated LED lighting","Backlighting","Front lighting"],"ledColorTemperature":["Warm white","Tunable white","Cool white"],"control":["Touch sensor button","Defogger","Dimmer"],"mountingMethod":["Wall-mounted","Adhesive","Leaning","Stand"]}'),
(6, 'Frameless Door', 'Doors', 'Glass', '2000.00', '["c4e5e34e31fc2e0c4b32c1a1b49dbc3c.jpg","feb6e890524380c9d97400a6d4069138.jpg"]', NULL, '2026-01-22 05:13:19', 'In Stock', 'Frameless', 'site-assessment', '1000.00', '3000.00', '{"glassType":["Clear","Tinted","Frosted","Laminated","Laminated safety glass"],"doorType":["Single swing","Single French door","Double swing","Double French doors"],"doorSwing":["Left swing","Left-hinged","Right swing","Right-hinged"],"fixedPanels":["With fixed side/transom panels","0 fixed panels","Without fixed panels","2 fixed panels","More fixed panels","With fixed side panel (left or right)","With fixed transom","Both","1 fixed panel"],"configuration":["With fixed side panel (left or right)","With fixed transom","Both","Single swing door","Double swing door"],"handleType":["Various pull handle designs","Decorative handles","Various pull handles"],"hardwareFinish":["Polished Chrome/Stainless Steel","Matte Black","Gold","Brushed Nickel","Chrome/Stainless Steel"],"gridPattern":["External grids","Prairie","Custom grid designs","French type grid","Colonial","Internal grids"],"glassTreatment":["Frosted stripes (horizontal/vertical)","Custom patterns","Colors"],"installation":["Patch fittings (minimalist hardware)","Standard"],"hardware":["Push/pull handles","Closers","Multi-point locks","Locks"],"softClose":false}'),
(7, 'Top Glass', 'Mirrors & Specialty Glass', 'Glass', '1500.00', '["031d6e56c8aa1a4fd61dba00dffa39ef.jpg","f3d0149161d614c19f397f4900858b35.jpg","64324927ebc9db8e5052fff72cffee59.jpg","4d359966411c2dcd1808c1c0dec4f4cb.jpg"]', NULL, '2026-01-22 05:23:46', 'In Stock', 'Top Glass', 'direct', '1000.00', '2000.00', '{"shape":["Round","Rectangle","Oval","Square","Custom shapes"],"edgeFinish":["Beveled","Polished","Raw","Beveled edge","Flat polished edge","Pencil edge"],"mountingMethod":["Wall-mounted","Stand","Adhesive"]}'),
(8, 'Glass Board', 'Mirrors & Specialty Glass', 'Glass', '1500.00', '["eedb3f9128501cd227f191559d54e885.jpg","d69ee9aeee60b6033a116ef1f523551d.jpg","fa0fce32b771d0718ec27eab8ffdb965.jpg"]', 'Testing', '2026-01-22 06:02:08', 'In Stock', 'Glass Board', 'direct', '1000.00', '2000.00', '{"shape":["Round","Rectangle","Oval","Square"],"edgeFinish":["Beveled","Polished","Raw","Beveled edge","Flat polished edge","Pencil edge"],"mountingMethod":["Wall-mounted","Stand","Adhesive"]}')
ON DUPLICATE KEY UPDATE
  `ProductName` = VALUES(`ProductName`),
  `Category` = VALUES(`Category`),
  `Material` = VALUES(`Material`),
  `Price` = VALUES(`Price`),
  `ImageUrl` = VALUES(`ImageUrl`),
  `Description` = VALUES(`Description`),
  `DateAdded` = VALUES(`DateAdded`),
  `Status` = VALUES(`Status`),
  `Subcategory` = VALUES(`Subcategory`),
  `OrderType` = VALUES(`OrderType`),
  `PriceMin` = VALUES(`PriceMin`),
  `PriceMax` = VALUES(`PriceMax`),
  `Customization` = VALUES(`Customization`);

-- --------------------------------------------------------



-- SAFE UPDATE: Replace existing file contents with a cleaned up safe SQL that uses
-- IF NOT EXISTS for tables and INSERT ... ON DUPLICATE KEY UPDATE for rows.

CREATE TABLE IF NOT EXISTS `product` (
  `Product_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ProductName` varchar(100) NOT NULL,
  `Category` varchar(50) NOT NULL,
  `Material` enum('Glass','Aluminum') NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `ImageUrl` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status` enum('In Stock','Out of Stock','Low Stock') DEFAULT 'Out of Stock',
  `Subcategory` varchar(100) DEFAULT NULL,
  `OrderType` enum('direct','site-assessment') DEFAULT 'direct',
  `PriceMin` decimal(10,2) DEFAULT NULL,
  `PriceMax` decimal(10,2) DEFAULT NULL,
  `Customization` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: Customer customization selections',
  PRIMARY KEY (`Product_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert/update product rows
INSERT INTO `product` (`Product_ID`, `ProductName`, `Category`, `Material`, `Price`, `ImageUrl`, `Description`, `DateAdded`, `Status`, `Subcategory`, `OrderType`, `PriceMin`, `PriceMax`, `Customization`) VALUES
(1, 'Sliding Window', 'Windows', 'Glass', 4300.00, '["08973984eef0bdc173063b3debb2df7f.jpg","a9719fddcbc0baecacb9d6c4ec61ae3e.jpg","b0911580251a5a6d8d20741598a5179a.jpg"]', NULL, '2026-01-21 02:55:09', 'In Stock', 'Sliding', 'direct', 1600.00, 7000.00, '{"numberOfPanels":["2 Panels","4 Panels"],"transomType":["Fixed Transom Sill (Fixed glass at bottom)","Fixed Transom Head (Fixed glass at top)","None"],"trackSystem":["2 Tracks","3 Tracks"],"panelConfiguration":["F | S | S | F (Fixed | Sliding | Sliding | Fixed)","S | S | S | S (All Sliding)","F | S (Fixed | Sliding)","S | S (Sliding | Sliding)"],"frameColor":["Powder Coated White","Analok","Matte Black","Matte Gray","Wood Finish"],"glassType":["Ordinary","Reflective","Tempered"],"glassColor":["Bronze","Clear","Smoked","Frosted"],"glassThickness":["6mm","10mm","8mm","12mm"],"lockType":["Center Lok 904 Big","Flushlok #12","New Auto Flushlock","Durable Flushlok"],"rollerType":["Single Panel Roller","Blue Single Roller","Blue Double Roller"],"screen":["With Screen","Without Screen"]}'),
(2, 'Awning Window', 'Windows', 'Glass', 4000.00, '["8891bbe9173e296dabda55ccd6e132e1.jpg"]', NULL, '2026-01-21 03:19:09', 'In Stock', 'Awning', 'direct', 2000.00, 6000.00, '{"glassType":["Ordinary","Tempered","Reflective"],"glassColor":["Clear","Frosted","Bronze","Smoked"],"frameColor":["Powder Coated White","Matte Gray","Wood Finish","Analok","Matte Black"],"operation":["Awning (crank-out)","Awning (push-out)"],"sizeConfiguration":["Single panel","Multiple panels"],"openingDirection":["Top-hinged"],"thickness":["6mm","10mm","8mm","12mm"],"screen":["With Screen","Without Screen"]}'),
(3, 'Casement Window', 'Windows', 'Glass', 1500.00, '["4a04a1a313222ae5f585308c10aa4908.jpg"]', NULL, '2026-01-21 04:03:04', 'In Stock', 'Casement', 'direct', 1000.00, 2000.00, '{"transomType":["None","Casement w/FTS","Casement w/FTH"],"panelConfiguration":["1","4","2","5","3","6"],"width":"","height":"","h1":"","frameColor":["Wood Finish","Matte Gray","Powder Coated White","Analok","Matte Black"],"glassColor":["Bronze","Smoked","Clear","Frosted"],"glassType":["Reflective","Ordinary","Tempered"],"thickness":["10mm","6mm","8mm","12mm"]}'),
(4, 'Sliding Door', 'Doors', 'Glass', 2000.00, '["6946d23f9ab77a37b945e908744736cf.jpg"]', NULL, '2026-01-21 04:04:18', 'In Stock', 'Sliding', 'site-assessment', 1000.00, 3000.00, '{"glassType":["Clear","Tinted","Low-E","Frosted","Tempered","Laminated","Laminated safety glass"],"frameColor":["Aluminum","White","Black","Bronze","Brown (wood-look)","Silver","Custom colors"],"panelCount":["2-panel","4-panel","3-panel"],"operation":["Sliding (single)","Sliding (multi-track)","Sliding (double)"],"panelConfiguration":["Central sliding panels with fixed outer panels","All sliding","2 sliding + 2 fixed","3 sliding","2 sliding only"],"handleType":["Various pull handles","Knob handles","Bar-style","Square handles","Round","Square matte black"],"hardwareFinish":["Chrome/Stainless Steel","Polished Chrome/Stainless Steel","Black Matte","Brushed Nickel","Gold","Bronze"],"softClose":false}'),
(5, 'Mirrors', 'Mirrors & Specialty Glass', 'Glass', 2500.00, '["1c00867eac693e151f3663db39b397a2.jpg","4e5f72c1809586c8f265ba2fb39318b2.jpg"]', NULL, '2026-01-21 06:52:35', 'In Stock', 'Mirrors', 'direct', 2000.00, 3000.00, '{"shape":["Round","Rectangle","Oval","Square"],"cornerRadius":"","frameType":["Frameless","Framed"],"frameColor":["White","Gold","Black","Machine Polished Edges","Beveled Edge"],"glassType":["Copper Free and Lead Free Mirror"],"thickness":["6mm"],"tintFinish":["Bronze tint/color","Grey tint (smoked)","Colored glass"],"lighting":["Integrated LED lighting","Backlighting","Front lighting"],"ledColorTemperature":["Warm white","Tunable white","Cool white"],"control":["Touch sensor button","Defogger","Dimmer"],"mountingMethod":["Wall-mounted","Adhesive","Leaning","Stand"]}'),
(6, 'Frameless Door', 'Doors', 'Glass', 2000.00, '["c4e5e34e31fc2e0c4b32c1a1b49dbc3c.jpg","feb6e890524380c9d97400a6d4069138.jpg"]', NULL, '2026-01-22 05:13:19', 'In Stock', 'Frameless', 'site-assessment', 1000.00, 3000.00, '{"glassType":["Clear","Tinted","Frosted","Laminated","Laminated safety glass"],"doorType":["Single swing","Single French door","Double swing","Double French doors"],"doorSwing":["Left swing","Left-hinged","Right swing","Right-hinged"],"fixedPanels":["With fixed side/transom panels","0 fixed panels","Without fixed panels","2 fixed panels","More fixed panels","With fixed side panel (left or right)","With fixed transom","Both","1 fixed panel"],"configuration":["With fixed side panel (left or right)","With fixed transom","Both","Single swing door","Double swing door"],"handleType":["Various pull handle designs","Decorative handles","Various pull handles"],"hardwareFinish":["Polished Chrome/Stainless Steel","Matte Black","Gold","Brushed Nickel","Chrome/Stainless Steel"],"gridPattern":["External grids","Prairie","Custom grid designs","French type grid","Colonial","Internal grids"],"glassTreatment":["Frosted stripes (horizontal\/vertical)","Custom patterns","Colors"],"installation":["Patch fittings (minimalist hardware)","Standard"],"hardware":["Push/pull handles","Closers","Multi-point locks","Locks"],"softClose":false}'),
(7, 'Top Glass', 'Mirrors & Specialty Glass', 'Glass', 1500.00, '["031d6e56c8aa1a4fd61dba00dffa39ef.jpg","f3d0149161d614c19f397f4900858b35.jpg","64324927ebc9db8e5052fff72cffee59.jpg","4d359966411c2dcd1808c1c0dec4f4cb.jpg"]', NULL, '2026-01-22 05:23:46', 'In Stock', 'Top Glass', 'direct', 1000.00, 2000.00, '{"shape":["Round","Rectangle","Oval","Square","Custom shapes"],"edgeFinish":["Beveled","Polished","Raw","Beveled edge","Flat polished edge","Pencil edge"],"mountingMethod":["Wall-mounted","Stand","Adhesive"]}'),
(8, 'Glass Board', 'Mirrors & Specialty Glass', 'Glass', 1500.00, '["eedb3f9128501cd227f191559d54e885.jpg","d69ee9aeee60b6033a116ef1f523551d.jpg","fa0fce32b771d0718ec27eab8ffdb965.jpg"]', 'Testing', '2026-01-22 06:02:08', 'In Stock', 'Glass Board', 'direct', 1000.00, 2000.00, '{"shape":["Round","Rectangle","Oval","Square"],"edgeFinish":["Beveled","Polished","Raw","Beveled edge","Flat polished edge","Pencil edge"],"mountingMethod":["Wall-mounted","Stand","Adhesive"]}')
ON DUPLICATE KEY UPDATE
  `ProductName` = VALUES(`ProductName`),
  `Category` = VALUES(`Category`),
  `Material` = VALUES(`Material`),
  `Price` = VALUES(`Price`),
  `ImageUrl` = VALUES(`ImageUrl`),
  `Description` = VALUES(`Description`),
  `DateAdded` = VALUES(`DateAdded`),
  `Status` = VALUES(`Status`),
  `Subcategory` = VALUES(`Subcategory`),
  `OrderType` = VALUES(`OrderType`),
  `PriceMin` = VALUES(`PriceMin`),
  `PriceMax` = VALUES(`PriceMax`),
  `Customization` = VALUES(`Customization`);


-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `customization_field_configs` (
  `ConfigID` int(11) NOT NULL AUTO_INCREMENT,
  `Category` varchar(100) NOT NULL,
  `Subcategory` varchar(100) NOT NULL,
  `FieldKey` varchar(200) NOT NULL COMMENT 'Unique key: Category_Subcategory',
  `FieldConfig` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON array of field definitions',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ConfigID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert/update customization_field_configs rows
INSERT INTO `customization_field_configs` (`ConfigID`, `Category`, `Subcategory`, `FieldKey`, `FieldConfig`, `Created_Date`, `Updated_Date`) VALUES
(1, 'Windows', 'Sliding', 'Windows_Sliding', '[{"type":"tags","label":"Number of Panels","id":"numberOfPanels","options":["2 Panels","4 Panels"],"stepNumber":1},{"type":"tags","label":"Transom Type (Top \/ Bottom Fixed Panel)","id":"transomType","options":["None","Fixed Transom Head (Fixed glass at top)","Fixed Transom Sill (Fixed glass at bottom)"],"stepNumber":1},{"type":"tags","label":"Track System (Sliding Rail Count)","id":"trackSystem","options":["2 Tracks","3 Tracks"],"stepNumber":2},{"type":"tags","label":"Panel Configuration","id":"panelConfiguration","options":["S | S (Sliding | Sliding)","F | S (Fixed | Sliding)","S | S | S | S (All Sliding)","F | S | S | F (Fixed | Sliding | Sliding | Fixed)"],"stepNumber":2},{"type":"tags","label":"Frame Color","id":"frameColor","options":["Powder Coated White","Analok","Matte Gray","Matte Black","Wood Finish"],"stepNumber":3},{"type":"tags","label":"Glass Type","id":"glassType","options":["Ordinary","Tempered","Reflective"],"stepNumber":3},{"type":"tags","label":"Glass Color","id":"glassColor","options":["Clear","Bronze","Frosted","Smoked"],"stepNumber":3},{"type":"tags","label":"Glass Thickness","id":"glassThickness","options":["6mm","8mm","10mm","12mm"],"stepNumber":3},{"type":"tags","label":"Lock Type","id":"lockType","options":["Center Lok 904 Big","Flushlok #12","Durable Flushlok","New Auto Flushlock"],"stepNumber":4},{"type":"tags","label":"Roller Type","id":"rollerType","options":["Single Panel Roller","Blue Single Roller","Blue Double Roller"],"stepNumber":4},{"type":"tags","label":"Screen","id":"screen","options":["With Screen","Without Screen"],"stepNumber":4}]', '2026-01-21 09:54:15', '2026-01-21 06:48:37'),
(2, 'Windows', 'Awning', 'Windows_Awning', '[{"type":"tags","label":"Glass Type","id":"glassType","options":["Ordinary","Tempered","Reflective"],"stepNumber":1},{"type":"tags","label":"Glass Color","id":"glassColor","options":["Clear","Bronze","Frosted","Smoked"],"stepNumber":1},{"type":"tags","label":"Frame Color\\/Material","id":"frameColor","options":["Powder Coated White","Analok","Matte Gray","Matte Black","Wood Finish"],"stepNumber":1},{"type":"tags","label":"Operation","id":"operation","options":["Awning (crank-out)","Awning (push-out)"],"stepNumber":1},{"type":"tags","label":"Opening Direction","id":"openingDirection","options":["Top-hinged"],"stepNumber":2},{"type":"tags","label":"Thickness (mm)","id":"thickness","options":["6mm","8mm","10mm","12mm"],"stepNumber":2},{"type":"tags","label":"Screen","id":"screen","options":["With Screen","Without Screen"],"stepNumber":2}]', '2026-01-21 10:11:42', '2026-01-22 01:04:48'),
  `Created_Date` = VALUES(`Created_Date`),
  `FieldID` = VALUES(`FieldID`),
  `TagName` = VALUES(`TagName`),
  `Price` = VALUES(`Price`),
  `ImageUrl` = VALUES(`ImageUrl`),
  `VisualConfig` = VALUES(`VisualConfig`);

