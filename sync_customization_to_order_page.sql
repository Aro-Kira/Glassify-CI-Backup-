-- Sync data from customization table to order_page table
-- This creates order page entries from customization data

INSERT INTO `order_page` (
    `OrderID`,
    `ProductName`,
    `Address`,
    `OrderDate`,
    `Shape`,
    `Dimension`,
    `Type`,
    `Thickness`,
    `EdgeWork`,
    `FrameType`,
    `Engraving`,
    `FileAttached`,
    `TotalQuotation`,
    `Status`,
    `Customer_ID`,
    `SalesRep_ID`
)
SELECT 
    CONCAT('GI', LPAD(ROW_NUMBER() OVER (ORDER BY c.Created_Date), 3, '0')) as OrderID,
    c.ProductName,
    c.DeliveryAddress as Address,
    COALESCE(c.OrderDate, c.Created_Date) as OrderDate,
    c.GlassShape as Shape,
    CASE 
        WHEN c.Dimensions LIKE '[%' OR c.Dimensions LIKE '{%' THEN
            CONCAT(
                JSON_UNQUOTE(JSON_EXTRACT(c.Dimensions, '$[0]')), 'in x ',
                JSON_UNQUOTE(JSON_EXTRACT(c.Dimensions, '$[2]')), 'in'
            )
        ELSE c.Dimensions
    END as Dimension,
    c.GlassType as Type,
    c.GlassThickness as Thickness,
    c.EdgeWork,
    c.FrameType,
    COALESCE(c.Engraving, 'N/A') as Engraving,
    COALESCE(c.DesignRef, 'N/A') as FileAttached,
    COALESCE(c.TotalQuotation, c.EstimatePrice, 0) as TotalQuotation,
    'Pending Review' as Status,
    c.Customer_ID,
    (SELECT UserID FROM user WHERE Role = 'Sales Representative' LIMIT 1) as SalesRep_ID
FROM customization c
WHERE c.ProductName IS NOT NULL
ON DUPLICATE KEY UPDATE
    ProductName = VALUES(ProductName),
    Address = VALUES(Address),
    OrderDate = VALUES(OrderDate),
    Shape = VALUES(Shape),
    Dimension = VALUES(Dimension),
    Type = VALUES(Type),
    Thickness = VALUES(Thickness),
    EdgeWork = VALUES(EdgeWork),
    FrameType = VALUES(FrameType),
    Engraving = VALUES(Engraving),
    FileAttached = VALUES(FileAttached),
    TotalQuotation = VALUES(TotalQuotation);

