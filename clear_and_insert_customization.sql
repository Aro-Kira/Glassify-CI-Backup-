-- Clear all existing data in customization table
DELETE FROM `customization`;

-- Insert new sample record with all required fields
INSERT INTO `customization` (
    `Customer_ID`,
    `Product_ID`,
    `ProductName`,
    `Dimensions`,
    `GlassShape`,
    `GlassType`,
    `GlassThickness`,
    `EdgeWork`,
    `FrameType`,
    `Engraving`,
    `DesignRef`,
    `EstimatePrice`,
    `TotalQuotation`,
    `DeliveryAddress`,
    `OrderDate`,
    `OrderID`,
    `Created_Date`
) VALUES (
    1, -- Customer_ID (adjust as needed)
    1, -- Product_ID (adjust as needed)
    'Tempered Glass Panel', -- ProductName
    '["45", "0", "35", "0"]', -- Dimensions as JSON: [height, ?, width, ?]
    'Rectangle', -- Shape
    'Tempered', -- Type
    '8mm', -- Thickness
    'Flat Polish', -- Edge Work
    'Vinyl', -- Frame Type
    'N/A', -- Engraving
    'design.pdf', -- File Attached
    3100.00, -- EstimatePrice
    3100.00, -- TotalQuotation
    '123 Glass St. Manila', -- Address (random)
    '2025-05-30 10:00:00', -- Date (random)
    NULL, -- OrderID (will be set when order is created)
    NOW() -- Created_Date
);

