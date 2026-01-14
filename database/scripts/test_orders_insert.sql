-- Test Data Insert Script for Direct and Site-Assessed Orders
-- Description: Inserts test data for both order types to test admin approval flow
-- Date: 2026-01-12
--
-- This script creates:
-- 1. A Direct Order with status 'Awaiting Admin' (for testing approval)
-- 2. A Site-Assessed Order with status 'Awaiting Admin' (for testing approval)
--
-- IMPORTANT NOTES:
-- ================
-- 1. Before running, check your current Status enum:
--    - If you've run update_order_status_enum.sql, the script uses 'Pending Payment' (correct)
--    - If NOT run, change 'Pending Payment' to 'Awaiting Admin' in the INSERT statements
--    - Run: SHOW COLUMNS FROM `order` LIKE 'Status'; to check current enum values
--
-- 2. Verify the next available OrderID:
--    Run: SELECT MAX(OrderID) FROM `order`;
--    Update OrderID values (5, 6) in this script if needed
--
-- 3. Verify the next available OrderNumber:
--    Run: SELECT MAX(CAST(SUBSTRING(OrderNumber, 3) AS UNSIGNED)) FROM `order`;
--    Update OrderNumber values (GI005, GI006) in this script if needed
--
-- 4. Verify existing data:
--    - Customer_ID = 1: SELECT * FROM `customer` WHERE Customer_ID = 1;
--    - SalesRep_ID = 3: SELECT * FROM `user` WHERE UserID = 3 AND Role = 'Sales Representative';
--    - Product_ID = 2: SELECT * FROM `product` WHERE Product_ID = 2;
--    - OcularCompletedBy_ID = 3: SELECT * FROM `user` WHERE UserID = 3;
--
-- 5. Adjust values as needed for your environment

-- ============================================
-- Direct Order (GI005)
-- ============================================
INSERT INTO `order` (
    `OrderID`,
    `OrderNumber`,
    `Customer_ID`,
    `SalesRep_ID`,
    `OrderDate`,
    `TotalAmount`,
    `Status`,
    `PaymentStatus`,
    `PaymentMethod`,
    `DeliveryAddress`,
    `SpecialInstructions`,
    `QuotationPDFUrl`,
    `ContractPDFUrl`,
    `ApprovedBy_SalesRep_ID`,
    `ApprovedBy_Admin_ID`,
    `Approved_Date`,
    `DisapprovedBy`,
    `DisapprovedBy_ID`,
    `DisapprovalReason`,
    `Disapproved_Date`,
    `CustomerNotified`,
    `CustomerNotified_Date`,
    `PreferredInstallationDate`,
    `OcularDate`,
    `FabricationDate`,
    `InstallationDate`,
    `EstimatedDelivery`,
    `OrderType`,
    `OcularCompleted`,
    `OcularNotes`,
    `OcularCompletedBy_ID`,
    `FabricationStaff_ID`,
    `InstallationStaff_ID`,
    `FabricationStartDate`,
    `FabricationEndDate`,
    `ActualFabricationStartDate`,
    `ActualFabricationEndDate`,
    `FabricationProgress`,
    `FabricationStatus`,
    `FabricationNotes`,
    `QualityCheckNotes`,
    `AdminNotes`,
    `CustomerNotes`,
    `StaffNotes`,
    `Barcode`,
    `BarcodeImagePath`,
    `Created_Date`,
    `Updated_Date`
) VALUES (
    5,                                    -- OrderID
    'GI005',                              -- OrderNumber
    1,                                    -- Customer_ID (assuming customer 1 exists)
    3,                                    -- SalesRep_ID (assuming sales rep 3 exists)
    NOW(),                                -- OrderDate
    18500.00,                             -- TotalAmount
    'Pending Payment',                    -- Status (for testing admin approval - use 'Pending Payment' if enum was updated, or 'Awaiting Admin' if not)
    'Pending',                            -- PaymentStatus
    'E-Wallet',                           -- PaymentMethod
    '123 Main Street, Makati City, Metro Manila, Philippines, 1200',  -- DeliveryAddress
    'Please deliver during business hours. Contact customer before delivery.',  -- SpecialInstructions
    NULL,                                 -- QuotationPDFUrl
    NULL,                                 -- ContractPDFUrl
    NULL,                                 -- ApprovedBy_SalesRep_ID
    NULL,                                 -- ApprovedBy_Admin_ID
    NULL,                                 -- Approved_Date
    NULL,                                 -- DisapprovedBy
    NULL,                                 -- DisapprovedBy_ID
    NULL,                                 -- DisapprovalReason
    NULL,                                 -- Disapproved_Date
    0,                                    -- CustomerNotified
    NULL,                                 -- CustomerNotified_Date
    '2026-02-15',                         -- PreferredInstallationDate
    NULL,                                 -- OcularDate (not needed for Direct orders)
    NULL,                                 -- FabricationDate
    NULL,                                 -- InstallationDate
    '2026-02-20',                         -- EstimatedDelivery
    'Direct',                             -- OrderType
    0,                                    -- OcularCompleted (not applicable for Direct)
    NULL,                                 -- OcularNotes (not applicable for Direct)
    NULL,                                 -- OcularCompletedBy_ID (not applicable for Direct)
    NULL,                                 -- FabricationStaff_ID
    NULL,                                 -- InstallationStaff_ID
    NULL,                                 -- FabricationStartDate
    NULL,                                 -- FabricationEndDate
    NULL,                                 -- ActualFabricationStartDate
    NULL,                                 -- ActualFabricationEndDate
    0,                                    -- FabricationProgress
    'Queued',                             -- FabricationStatus
    NULL,                                 -- FabricationNotes
    NULL,                                 -- QualityCheckNotes
    NULL,                                 -- AdminNotes
    NULL,                                 -- CustomerNotes
    NULL,                                 -- StaffNotes
    NULL,                                 -- Barcode
    NULL,                                 -- BarcodeImagePath
    NOW(),                                -- Created_Date
    NOW()                                 -- Updated_Date
);

-- Order Items for Direct Order (GI005)
INSERT INTO `order_items` (
    `OrderID`,
    `Product_ID`,
    `CustomizationID`,
    `Quantity`,
    `UnitPrice`,
    `EstimatePrice`,
    `Dimensions`,
    `GlassShape`,
    `GlassType`,
    `GlassThickness`,
    `EdgeWork`,
    `FrameType`,
    `Engraving`,
    `DesignRef`,
    `Created_Date`
) VALUES (
    5,                                    -- OrderID
    2,                                    -- Product_ID (assuming Product 2 exists - Round Mirror)
    NULL,                                 -- CustomizationID
    1,                                    -- Quantity
    1500.00,                              -- UnitPrice
    18500.00,                             -- EstimatePrice
    '120cm x 80cm',                       -- Dimensions
    'rectangle',                          -- GlassShape
    'tempered',                           -- GlassType
    '6mm',                                -- GlassThickness
    'beveled',                            -- EdgeWork
    'aluminum',                           -- FrameType
    'None',                               -- Engraving
    NULL,                                 -- DesignRef (can be NULL for direct orders)
    NOW()                                 -- Created_Date
);

-- ============================================
-- Site-Assessed Order (GI006)
-- ============================================
INSERT INTO `order` (
    `OrderID`,
    `OrderNumber`,
    `Customer_ID`,
    `SalesRep_ID`,
    `OrderDate`,
    `TotalAmount`,
    `Status`,
    `PaymentStatus`,
    `PaymentMethod`,
    `DeliveryAddress`,
    `SpecialInstructions`,
    `QuotationPDFUrl`,
    `ContractPDFUrl`,
    `ApprovedBy_SalesRep_ID`,
    `ApprovedBy_Admin_ID`,
    `Approved_Date`,
    `DisapprovedBy`,
    `DisapprovedBy_ID`,
    `DisapprovalReason`,
    `Disapproved_Date`,
    `CustomerNotified`,
    `CustomerNotified_Date`,
    `PreferredInstallationDate`,
    `OcularDate`,
    `FabricationDate`,
    `InstallationDate`,
    `EstimatedDelivery`,
    `OrderType`,
    `OcularCompleted`,
    `OcularNotes`,
    `OcularCompletedBy_ID`,
    `FabricationStaff_ID`,
    `InstallationStaff_ID`,
    `FabricationStartDate`,
    `FabricationEndDate`,
    `ActualFabricationStartDate`,
    `ActualFabricationEndDate`,
    `FabricationProgress`,
    `FabricationStatus`,
    `FabricationNotes`,
    `QualityCheckNotes`,
    `AdminNotes`,
    `CustomerNotes`,
    `StaffNotes`,
    `Barcode`,
    `BarcodeImagePath`,
    `Created_Date`,
    `Updated_Date`
) VALUES (
    6,                                    -- OrderID
    'GI006',                              -- OrderNumber
    1,                                    -- Customer_ID (assuming customer 1 exists)
    3,                                    -- SalesRep_ID (assuming sales rep 3 exists)
    NOW(),                                -- OrderDate
    27500.00,                             -- TotalAmount
    'Pending Payment',                    -- Status (for testing admin approval - use 'Pending Payment' if enum was updated, or 'Awaiting Admin' if not)
    'Pending',                            -- PaymentStatus
    'Cash on Delivery',                   -- PaymentMethod
    '456 Oak Avenue, Taguig City, Metro Manila, Philippines, 1630',  -- DeliveryAddress
    'Site assessment required. Customer prefers installation in the morning. Access via elevator only.',  -- SpecialInstructions
    NULL,                                 -- QuotationPDFUrl
    NULL,                                 -- ContractPDFUrl
    NULL,                                 -- ApprovedBy_SalesRep_ID
    NULL,                                 -- ApprovedBy_Admin_ID
    NULL,                                 -- Approved_Date
    NULL,                                 -- DisapprovedBy
    NULL,                                 -- DisapprovedBy_ID
    NULL,                                 -- DisapprovalReason
    NULL,                                 -- Disapproved_Date
    0,                                    -- CustomerNotified
    NULL,                                 -- CustomerNotified_Date
    '2026-02-25',                         -- PreferredInstallationDate
    '2026-01-15',                         -- OcularDate (ocular visit completed date)
    NULL,                                 -- FabricationDate
    NULL,                                 -- InstallationDate
    '2026-03-05',                         -- EstimatedDelivery
    'Site-Assessed',                      -- OrderType
    1,                                    -- OcularCompleted (1 = completed)
    'Site assessment completed on 2026-01-15. Window opening measures 150cm x 200cm. Standard installation procedure applicable. Building has elevator access. No special mounting requirements. Customer prefers morning installation (8AM-12PM). Site is ready for installation.',  -- OcularNotes
    3,                                    -- OcularCompletedBy_ID (assuming staff ID 3 exists, adjust if needed)
    NULL,                                 -- FabricationStaff_ID
    NULL,                                 -- InstallationStaff_ID
    NULL,                                 -- FabricationStartDate
    NULL,                                 -- FabricationEndDate
    NULL,                                 -- ActualFabricationStartDate
    NULL,                                 -- ActualFabricationEndDate
    0,                                    -- FabricationProgress
    'Queued',                             -- FabricationStatus
    NULL,                                 -- FabricationNotes
    NULL,                                 -- QualityCheckNotes
    NULL,                                 -- AdminNotes
    NULL,                                 -- CustomerNotes
    NULL,                                 -- StaffNotes
    NULL,                                 -- Barcode
    NULL,                                 -- BarcodeImagePath
    NOW(),                                -- Created_Date
    NOW()                                 -- Updated_Date
);

-- Order Items for Site-Assessed Order (GI006)
INSERT INTO `order_items` (
    `OrderID`,
    `Product_ID`,
    `CustomizationID`,
    `Quantity`,
    `UnitPrice`,
    `EstimatePrice`,
    `Dimensions`,
    `GlassShape`,
    `GlassType`,
    `GlassThickness`,
    `EdgeWork`,
    `FrameType`,
    `Engraving`,
    `DesignRef`,
    `Created_Date`
) VALUES (
    6,                                    -- OrderID
    2,                                    -- Product_ID (assuming Product 2 exists - Round Mirror)
    NULL,                                 -- CustomizationID
    1,                                    -- Quantity
    2500.00,                              -- UnitPrice
    27500.00,                             -- EstimatePrice
    '150cm x 200cm',                      -- Dimensions (matches ocular notes)
    'rectangle',                          -- GlassShape
    'tempered',                           -- GlassType
    '8mm',                                -- GlassThickness
    'polished',                           -- EdgeWork
    'aluminum',                           -- FrameType
    'None',                               -- Engraving
    NULL,                                 -- DesignRef
    NOW()                                 -- Created_Date
);

-- ============================================
-- Optional: Insert into awaiting_admin_orders table (if needed for legacy support)
-- ============================================
-- Note: Only insert if the system still uses this legacy table
-- Uncomment if needed:

/*
INSERT INTO `awaiting_admin_orders` (
    `OrderID`,
    `OrderNumber`,
    `Customer_ID`,
    `SalesRep_ID`,
    `ProductName`,
    `Address`,
    `OrderDate`,
    `TotalQuotation`,
    `SalesRepNotes`,
    `Created_Date`,
    `Updated_Date`
) VALUES
(
    5,
    'GI005',
    1,
    3,
    'Round Mirror',  -- Adjust based on actual product name
    '123 Main Street, Makati City, Metro Manila, Philippines, 1200',
    NOW(),
    18500.00,
    NULL,
    NOW(),
    NOW()
),
(
    6,
    'GI006',
    1,
    3,
    'Round Mirror',  -- Adjust based on actual product name
    '456 Oak Avenue, Taguig City, Metro Manila, Philippines, 1630',
    NOW(),
    27500.00,
    'Site assessment completed. Ready for approval.',
    NOW(),
    NOW()
);
*/

-- ============================================
-- Verification Queries
-- ============================================
-- Run these queries to verify the test data was inserted correctly:

-- Check Direct Order
-- SELECT * FROM `order` WHERE `OrderNumber` = 'GI005';

-- Check Site-Assessed Order
-- SELECT * FROM `order` WHERE `OrderNumber` = 'GI006';

-- Check Order Items
-- SELECT * FROM `order_items` WHERE `OrderID` IN (5, 6);

-- Check both orders
-- SELECT `OrderID`, `OrderNumber`, `OrderType`, `Status`, `TotalAmount`, `OcularCompleted`, `OcularNotes` 
-- FROM `order` 
-- WHERE `OrderNumber` IN ('GI005', 'GI006');

-- ============================================
-- Cleanup (if needed)
-- ============================================
-- To remove the test data, uncomment and run:
/*
DELETE FROM `order_items` WHERE `OrderID` IN (5, 6);
DELETE FROM `awaiting_admin_orders` WHERE `OrderID` IN (5, 6);
DELETE FROM `order` WHERE `OrderID` IN (5, 6);
*/