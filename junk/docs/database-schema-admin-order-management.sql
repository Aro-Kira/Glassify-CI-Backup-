-- ============================================================================
-- DATABASE SCHEMA FOR ADMIN ORDER MANAGEMENT SYSTEM
-- Based on: admin-order-management-structure.md
-- ============================================================================
-- This schema includes all tables, fields, and relationships needed for the
-- Admin Order Management system including Direct Orders, Site-Assessed Orders,
-- Appointments, Calendar, Production Queue, Quotations, and Return Orders.
-- ============================================================================

-- ============================================================================
-- 1. UPDATES TO EXISTING TABLES
-- ============================================================================

-- Update `order` table to include all fields from documentation
-- Note: Some fields may already exist, adjust accordingly

ALTER TABLE `order` 
  -- Order Type and Ocular Fields
  ADD COLUMN IF NOT EXISTS `OrderType` enum('Direct','Site-Assessed') DEFAULT 'Direct' COMMENT 'Order type: Direct or Site-Assessed',
  ADD COLUMN IF NOT EXISTS `OcularCompleted` tinyint(1) DEFAULT 0 COMMENT 'Flag indicating if ocular/site assessment is completed',
  ADD COLUMN IF NOT EXISTS `OcularNotes` text DEFAULT NULL COMMENT 'Site assessment notes and measurements',
  ADD COLUMN IF NOT EXISTS `OcularDate` date DEFAULT NULL COMMENT 'Scheduled date for ocular visit',
  ADD COLUMN IF NOT EXISTS `OcularCompletedBy_ID` int(11) DEFAULT NULL COMMENT 'Staff who completed ocular assessment',
  
  -- Staff Assignment Fields
  ADD COLUMN IF NOT EXISTS `FabricationStaff_ID` int(11) DEFAULT NULL COMMENT 'Assigned fabrication staff member',
  ADD COLUMN IF NOT EXISTS `InstallationStaff_ID` int(11) DEFAULT NULL COMMENT 'Assigned installation staff member',
  
  -- Fabrication/Production Fields
  ADD COLUMN IF NOT EXISTS `FabricationStartDate` date DEFAULT NULL COMMENT 'Expected start date of fabrication',
  ADD COLUMN IF NOT EXISTS `FabricationEndDate` date DEFAULT NULL COMMENT 'Expected end date of fabrication',
  ADD COLUMN IF NOT EXISTS `ActualFabricationStartDate` date DEFAULT NULL COMMENT 'Actual start date when fabrication began',
  ADD COLUMN IF NOT EXISTS `ActualFabricationEndDate` date DEFAULT NULL COMMENT 'Actual end date when fabrication completed',
  ADD COLUMN IF NOT EXISTS `FabricationProgress` int(11) DEFAULT 0 COMMENT 'Fabrication progress percentage (0-100)',
  ADD COLUMN IF NOT EXISTS `FabricationStatus` enum('Queued','In Progress','Quality Check','Ready','Completed') DEFAULT 'Queued' COMMENT 'Fabrication queue status',
  ADD COLUMN IF NOT EXISTS `FabricationNotes` text DEFAULT NULL COMMENT 'Production and fabrication notes',
  ADD COLUMN IF NOT EXISTS `QualityCheckNotes` text DEFAULT NULL COMMENT 'Quality check notes',
  
  -- Date Fields
  ADD COLUMN IF NOT EXISTS `PreferredInstallationDate` date DEFAULT NULL COMMENT 'Customer preferred installation date',
  ADD COLUMN IF NOT EXISTS `FabricationDate` date DEFAULT NULL COMMENT 'Scheduled date for fabrication',
  ADD COLUMN IF NOT EXISTS `InstallationDate` date DEFAULT NULL COMMENT 'Scheduled date for installation',
  ADD COLUMN IF NOT EXISTS `EstimatedDelivery` date DEFAULT NULL COMMENT 'Estimated delivery/completion date',
  
  -- Notes and Admin Fields
  ADD COLUMN IF NOT EXISTS `AdminNotes` text DEFAULT NULL COMMENT 'Internal admin notes',
  ADD COLUMN IF NOT EXISTS `CustomerNotes` text DEFAULT NULL COMMENT 'Customer-facing notes',
  ADD COLUMN IF NOT EXISTS `StaffNotes` text DEFAULT NULL COMMENT 'Staff-specific notes',
  
  -- Barcode/QR Code
  ADD COLUMN IF NOT EXISTS `Barcode` varchar(100) DEFAULT NULL COMMENT 'Order barcode/QR code',
  ADD COLUMN IF NOT EXISTS `BarcodeImagePath` varchar(255) DEFAULT NULL COMMENT 'Path to barcode image';

-- Add indexes for new fields
ALTER TABLE `order`
  ADD INDEX IF NOT EXISTS `idx_order_type` (`OrderType`),
  ADD INDEX IF NOT EXISTS `idx_ocular_completed` (`OcularCompleted`),
  ADD INDEX IF NOT EXISTS `idx_fabrication_staff` (`FabricationStaff_ID`),
  ADD INDEX IF NOT EXISTS `idx_installation_staff` (`InstallationStaff_ID`),
  ADD INDEX IF NOT EXISTS `idx_fabrication_status` (`FabricationStatus`),
  ADD INDEX IF NOT EXISTS `idx_fabrication_dates` (`FabricationStartDate`, `FabricationEndDate`);

-- Add foreign keys for staff assignments
ALTER TABLE `order`
  ADD CONSTRAINT `fk_order_fabrication_staff` FOREIGN KEY (`FabricationStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_installation_staff` FOREIGN KEY (`InstallationStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_ocular_completed_by` FOREIGN KEY (`OcularCompletedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

-- Update `appointments` table to include all fields from documentation
ALTER TABLE `appointments`
  -- Enhanced Service Type
  MODIFY COLUMN `Service` enum('Order Placed','Ocular Visit','In Fabrication','Installed','Completed') DEFAULT 'Order Placed',
  
  -- Enhanced Appointment Fields
  ADD COLUMN IF NOT EXISTS `AppointmentType` enum('Ocular','Installation') DEFAULT NULL COMMENT 'Type of appointment: Ocular or Installation',
  ADD COLUMN IF NOT EXISTS `AssignedStaff_ID` int(11) DEFAULT NULL COMMENT 'Assigned staff member ID (replaces AssignedStaff varchar)',
  
  -- Ocular-Specific Fields
  ADD COLUMN IF NOT EXISTS `OcularNotes` text DEFAULT NULL COMMENT 'Site assessment notes and measurements',
  ADD COLUMN IF NOT EXISTS `OcularReportPath` varchar(255) DEFAULT NULL COMMENT 'Path to full ocular report PDF',
  
  -- Installation-Specific Fields
  ADD COLUMN IF NOT EXISTS `InstallationNotes` text DEFAULT NULL COMMENT 'Installation-specific notes',
  ADD COLUMN IF NOT EXISTS `InstallationChecklist` json DEFAULT NULL COMMENT 'Installation checklist items (JSON format)',
  
  -- Site Photos
  ADD COLUMN IF NOT EXISTS `SitePhotos` json DEFAULT NULL COMMENT 'Array of site photo paths (JSON format)',
  
  -- Additional Notes
  ADD COLUMN IF NOT EXISTS `InternalNotes` text DEFAULT NULL COMMENT 'Internal admin notes',
  ADD COLUMN IF NOT EXISTS `CustomerVisibleNotes` text DEFAULT NULL COMMENT 'Notes visible to customer';

-- Add indexes for appointments
ALTER TABLE `appointments`
  ADD INDEX IF NOT EXISTS `idx_appointment_type` (`AppointmentType`),
  ADD INDEX IF NOT EXISTS `idx_assigned_staff_id` (`AssignedStaff_ID`);

-- Add foreign key for assigned staff
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_assigned_staff` FOREIGN KEY (`AssignedStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;

-- ============================================================================
-- 2. NEW TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 2.1 QUOTATIONS TABLE
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `quotation` (
  `QuotationID` int(11) NOT NULL AUTO_INCREMENT,
  `QuotationNumber` varchar(50) NOT NULL COMMENT 'Formatted: QT001, QT002, etc.',
  `Customer_ID` int(11) NOT NULL,
  `SalesRep_ID` int(11) NOT NULL,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `ExpiryDate` date DEFAULT NULL COMMENT 'Quotation valid until date',
  `Status` enum('Pending','Approved','Rejected','Converted to Order') DEFAULT 'Pending',
  `TotalAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `Notes` text DEFAULT NULL COMMENT 'Admin notes',
  `RejectionReason` text DEFAULT NULL COMMENT 'Reason if rejected',
  `ConvertedToOrder_ID` int(11) DEFAULT NULL COMMENT 'Order ID if converted to order',
  `ConvertedDate` datetime DEFAULT NULL COMMENT 'Date when converted to order',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`QuotationID`),
  UNIQUE KEY `QuotationNumber` (`QuotationNumber`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_salesrep` (`SalesRep_ID`),
  KEY `idx_status` (`Status`),
  KEY `idx_created_date` (`CreatedDate`),
  KEY `idx_expiry_date` (`ExpiryDate`),
  KEY `idx_converted_order` (`ConvertedToOrder_ID`),
  CONSTRAINT `fk_quotation_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_quotation_salesrep` FOREIGN KEY (`SalesRep_ID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `fk_quotation_converted_order` FOREIGN KEY (`ConvertedToOrder_ID`) REFERENCES `order` (`OrderID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- 2.2 QUOTATION ITEMS TABLE
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `quotation_items` (
  `QuotationItemID` int(11) NOT NULL AUTO_INCREMENT,
  `QuotationID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `UnitPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `Shape` varchar(50) DEFAULT NULL,
  `Dimension` varchar(100) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Thickness` varchar(50) DEFAULT NULL,
  `EdgeWork` varchar(50) DEFAULT NULL,
  `FrameType` varchar(50) DEFAULT NULL,
  `Engraving` varchar(255) DEFAULT NULL,
  `DesignFile` varchar(255) DEFAULT NULL COMMENT 'Path to design file',
  `Specifications` text DEFAULT NULL COMMENT 'Additional specifications (JSON)',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`QuotationItemID`),
  KEY `idx_quotation` (`QuotationID`),
  KEY `idx_product` (`ProductID`),
  CONSTRAINT `fk_quotation_items_quotation` FOREIGN KEY (`QuotationID`) REFERENCES `quotation` (`QuotationID`) ON DELETE CASCADE,
  CONSTRAINT `fk_quotation_items_product` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- 2.3 RETURN ORDERS TABLE
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `return_order` (
  `ReturnID` int(11) NOT NULL AUTO_INCREMENT,
  `ReturnNumber` varchar(50) NOT NULL COMMENT 'Formatted: RT001, RT002, etc.',
  `OriginalOrder_ID` int(11) NOT NULL COMMENT 'Reference to original order',
  `OrderNumber` varchar(50) DEFAULT NULL COMMENT 'Original order number for quick reference',
  `Customer_ID` int(11) NOT NULL,
  `ReturnDate` datetime NOT NULL DEFAULT current_timestamp(),
  `ReturnType` enum('Defect','Wrong Item','Customer Request','Other') DEFAULT 'Other',
  `ReturnReason` text NOT NULL COMMENT 'Reason for return',
  `ReturnDescription` text DEFAULT NULL COMMENT 'Detailed description',
  `ReturnStatus` enum('Pending','Approved','Rejected','Processing','Completed') DEFAULT 'Pending',
  `RejectionReason` text DEFAULT NULL COMMENT 'Reason if rejected',
  
  -- Replacement Information
  `ReplacementRequired` tinyint(1) DEFAULT 0 COMMENT 'Whether replacement is needed',
  `ReplacementProductID` int(11) DEFAULT NULL COMMENT 'Replacement product if applicable',
  `ReplacementOrder_ID` int(11) DEFAULT NULL COMMENT 'Replacement order if created',
  `ReplacementAppointment_ID` int(11) DEFAULT NULL COMMENT 'Replacement installation appointment',
  
  -- Refund Information
  `RefundAmount` decimal(12,2) DEFAULT 0.00 COMMENT 'Refund amount',
  `RefundMethod` enum('Original Payment','Store Credit','Other') DEFAULT NULL,
  `RefundStatus` enum('Pending','Processed','Completed') DEFAULT 'Pending',
  `RefundDate` datetime DEFAULT NULL COMMENT 'Date when refund was processed',
  `RefundTransactionID` varchar(100) DEFAULT NULL COMMENT 'Refund transaction reference',
  
  -- Photos and Documents
  `ReturnPhotos` json DEFAULT NULL COMMENT 'Array of returned item photo paths (JSON)',
  `AdminNotes` text DEFAULT NULL COMMENT 'Internal admin notes',
  
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_Date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`ReturnID`),
  UNIQUE KEY `ReturnNumber` (`ReturnNumber`),
  KEY `idx_original_order` (`OriginalOrder_ID`),
  KEY `idx_order_number` (`OrderNumber`),
  KEY `idx_customer` (`Customer_ID`),
  KEY `idx_return_status` (`ReturnStatus`),
  KEY `idx_return_date` (`ReturnDate`),
  KEY `idx_replacement_order` (`ReplacementOrder_ID`),
  KEY `idx_replacement_appointment` (`ReplacementAppointment_ID`),
  CONSTRAINT `fk_return_order_original` FOREIGN KEY (`OriginalOrder_ID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_return_order_customer` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_return_order_replacement_order` FOREIGN KEY (`ReplacementOrder_ID`) REFERENCES `order` (`OrderID`) ON DELETE SET NULL,
  CONSTRAINT `fk_return_order_replacement_appointment` FOREIGN KEY (`ReplacementAppointment_ID`) REFERENCES `appointments` (`AppointmentID`) ON DELETE SET NULL,
  CONSTRAINT `fk_return_order_replacement_product` FOREIGN KEY (`ReplacementProductID`) REFERENCES `product` (`ProductID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- 2.4 RETURN ORDER ITEMS TABLE
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `return_order_items` (
  `ReturnItemID` int(11) NOT NULL AUTO_INCREMENT,
  `ReturnID` int(11) NOT NULL,
  `OrderItemID` int(11) NOT NULL COMMENT 'Reference to original order item',
  `ProductID` int(11) NOT NULL,
  `QuantityReturned` int(11) NOT NULL DEFAULT 1,
  `ProductName` varchar(255) DEFAULT NULL COMMENT 'Product name at time of return',
  `Specifications` text DEFAULT NULL COMMENT 'Product specifications (JSON)',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ReturnItemID`),
  KEY `idx_return` (`ReturnID`),
  KEY `idx_order_item` (`OrderItemID`),
  KEY `idx_product` (`ProductID`),
  CONSTRAINT `fk_return_items_return` FOREIGN KEY (`ReturnID`) REFERENCES `return_order` (`ReturnID`) ON DELETE CASCADE,
  CONSTRAINT `fk_return_items_order_item` FOREIGN KEY (`OrderItemID`) REFERENCES `order_items` (`OrderItemID`) ON DELETE CASCADE,
  CONSTRAINT `fk_return_items_product` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- 2.5 ORDER STATUS HISTORY TABLE
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `StatusHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `OrderNumber` varchar(50) DEFAULT NULL COMMENT 'Order number for quick reference',
  `PreviousStatus` varchar(50) DEFAULT NULL COMMENT 'Previous status',
  `NewStatus` varchar(50) NOT NULL COMMENT 'New status',
  `ChangedBy_ID` int(11) DEFAULT NULL COMMENT 'User who changed the status',
  `ChangedBy_Type` enum('Admin','Sales Rep','System') DEFAULT 'System' COMMENT 'Type of user who changed status',
  `ChangeReason` text DEFAULT NULL COMMENT 'Reason for status change',
  `Notes` text DEFAULT NULL COMMENT 'Additional notes',
  `Changed_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`StatusHistoryID`),
  KEY `idx_order` (`OrderID`),
  KEY `idx_order_number` (`OrderNumber`),
  KEY `idx_new_status` (`NewStatus`),
  KEY `idx_changed_date` (`Changed_Date`),
  KEY `idx_changed_by` (`ChangedBy_ID`),
  CONSTRAINT `fk_status_history_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_status_history_changed_by` FOREIGN KEY (`ChangedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- 2.6 FABRICATION MATERIALS TABLE
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fabrication_materials` (
  `MaterialID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `MaterialName` varchar(255) NOT NULL COMMENT 'Name of material used',
  `MaterialType` varchar(100) DEFAULT NULL COMMENT 'Type of material (Glass, Frame, Hardware, etc.)',
  `Quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Quantity used',
  `Unit` varchar(50) DEFAULT NULL COMMENT 'Unit of measurement (sqm, pcs, etc.)',
  `UnitCost` decimal(10,2) DEFAULT 0.00 COMMENT 'Cost per unit',
  `TotalCost` decimal(10,2) DEFAULT 0.00 COMMENT 'Total cost for this material',
  `Notes` text DEFAULT NULL COMMENT 'Additional notes about material usage',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MaterialID`),
  KEY `idx_order` (`OrderID`),
  CONSTRAINT `fk_fabrication_materials_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- 2.7 FABRICATION PROGRESS PHOTOS TABLE
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fabrication_progress_photos` (
  `PhotoID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `PhotoPath` varchar(255) NOT NULL COMMENT 'Path to photo file',
  `PhotoDescription` text DEFAULT NULL COMMENT 'Description of photo',
  `PhotoDate` datetime DEFAULT NULL COMMENT 'Date when photo was taken',
  `UploadedBy_ID` int(11) DEFAULT NULL COMMENT 'User who uploaded the photo',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`PhotoID`),
  KEY `idx_order` (`OrderID`),
  KEY `idx_photo_date` (`PhotoDate`),
  KEY `idx_uploaded_by` (`UploadedBy_ID`),
  CONSTRAINT `fk_progress_photos_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_progress_photos_uploaded_by` FOREIGN KEY (`UploadedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- 2.8 ORDER NOTES TABLE (For timestamped notes)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_notes` (
  `NoteID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `NoteType` enum('Admin','Customer','Staff','System') DEFAULT 'Admin' COMMENT 'Type of note',
  `NoteContent` text NOT NULL COMMENT 'Note content',
  `CreatedBy_ID` int(11) DEFAULT NULL COMMENT 'User who created the note',
  `IsVisibleToCustomer` tinyint(1) DEFAULT 0 COMMENT 'Whether note is visible to customer',
  `AttachmentPath` varchar(255) DEFAULT NULL COMMENT 'Path to attached file if any',
  `Created_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`NoteID`),
  KEY `idx_order` (`OrderID`),
  KEY `idx_note_type` (`NoteType`),
  KEY `idx_created_date` (`Created_Date`),
  KEY `idx_created_by` (`CreatedBy_ID`),
  CONSTRAINT `fk_order_notes_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_notes_created_by` FOREIGN KEY (`CreatedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- 2.9 STAFF ASSIGNMENT HISTORY TABLE
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `staff_assignment_history` (
  `AssignmentID` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `StaffType` enum('Fabrication','Installation','Ocular') NOT NULL COMMENT 'Type of staff assignment',
  `Staff_ID` int(11) NOT NULL COMMENT 'Assigned staff member',
  `AssignedBy_ID` int(11) DEFAULT NULL COMMENT 'User who made the assignment',
  `AssignedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `UnassignedDate` datetime DEFAULT NULL COMMENT 'Date when unassigned (if applicable)',
  `IsActive` tinyint(1) DEFAULT 1 COMMENT 'Whether assignment is currently active',
  `Notes` text DEFAULT NULL COMMENT 'Assignment notes',
  PRIMARY KEY (`AssignmentID`),
  KEY `idx_order` (`OrderID`),
  KEY `idx_staff` (`Staff_ID`),
  KEY `idx_staff_type` (`StaffType`),
  KEY `idx_active` (`IsActive`),
  KEY `idx_assigned_date` (`AssignedDate`),
  CONSTRAINT `fk_assignment_history_order` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_assignment_history_staff` FOREIGN KEY (`Staff_ID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `fk_assignment_history_assigned_by` FOREIGN KEY (`AssignedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- 2.10 APPOINTMENT STATUS HISTORY TABLE
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `appointment_status_history` (
  `AppointmentHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `AppointmentID` int(11) NOT NULL,
  `PreviousStatus` varchar(50) DEFAULT NULL,
  `NewStatus` varchar(50) NOT NULL,
  `ChangedBy_ID` int(11) DEFAULT NULL,
  `ChangeReason` text DEFAULT NULL,
  `Changed_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`AppointmentHistoryID`),
  KEY `idx_appointment` (`AppointmentID`),
  KEY `idx_new_status` (`NewStatus`),
  KEY `idx_changed_date` (`Changed_Date`),
  CONSTRAINT `fk_appointment_status_history` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`) ON DELETE CASCADE,
  CONSTRAINT `fk_appointment_status_changed_by` FOREIGN KEY (`ChangedBy_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 3. VIEWS FOR COMMON QUERIES
-- ============================================================================

-- View for Direct Orders
CREATE OR REPLACE VIEW `v_direct_orders` AS
SELECT 
  o.*,
  c.First_Name AS CustomerFirstName,
  c.Last_Name AS CustomerLastName,
  c.Email AS CustomerEmail,
  c.PhoneNum AS CustomerPhone,
  CONCAT(c.First_Name, ' ', c.Last_Name) AS CustomerFullName,
  sr.First_Name AS SalesRepFirstName,
  sr.Last_Name AS SalesRepLastName,
  CONCAT(sr.First_Name, ' ', sr.Last_Name) AS SalesRepFullName,
  fs.First_Name AS FabricationStaffFirstName,
  fs.Last_Name AS FabricationStaffLastName,
  CONCAT(fs.First_Name, ' ', fs.Last_Name) AS FabricationStaffFullName,
  ins.First_Name AS InstallationStaffFirstName,
  ins.Last_Name AS InstallationStaffLastName,
  CONCAT(ins.First_Name, ' ', ins.Last_Name) AS InstallationStaffFullName
FROM `order` o
LEFT JOIN `customer` c ON o.Customer_ID = c.Customer_ID
LEFT JOIN `user` sr ON o.SalesRep_ID = sr.UserID
LEFT JOIN `user` fs ON o.FabricationStaff_ID = fs.UserID
LEFT JOIN `user` ins ON o.InstallationStaff_ID = ins.UserID
WHERE o.OrderType = 'Direct';

-- View for Site-Assessed Orders
CREATE OR REPLACE VIEW `v_site_assessed_orders` AS
SELECT 
  o.*,
  c.First_Name AS CustomerFirstName,
  c.Last_Name AS CustomerLastName,
  c.Email AS CustomerEmail,
  c.PhoneNum AS CustomerPhone,
  CONCAT(c.First_Name, ' ', c.Last_Name) AS CustomerFullName,
  sr.First_Name AS SalesRepFirstName,
  sr.Last_Name AS SalesRepLastName,
  CONCAT(sr.First_Name, ' ', sr.Last_Name) AS SalesRepFullName,
  fs.First_Name AS FabricationStaffFirstName,
  fs.Last_Name AS FabricationStaffLastName,
  CONCAT(fs.First_Name, ' ', fs.Last_Name) AS FabricationStaffFullName,
  ins.First_Name AS InstallationStaffFirstName,
  ins.Last_Name AS InstallationStaffLastName,
  CONCAT(ins.First_Name, ' ', ins.Last_Name) AS InstallationStaffFullName,
  oc.First_Name AS OcularCompletedByFirstName,
  oc.Last_Name AS OcularCompletedByLastName,
  CONCAT(oc.First_Name, ' ', oc.Last_Name) AS OcularCompletedByFullName
FROM `order` o
LEFT JOIN `customer` c ON o.Customer_ID = c.Customer_ID
LEFT JOIN `user` sr ON o.SalesRep_ID = sr.UserID
LEFT JOIN `user` fs ON o.FabricationStaff_ID = fs.UserID
LEFT JOIN `user` ins ON o.InstallationStaff_ID = ins.UserID
LEFT JOIN `user` oc ON o.OcularCompletedBy_ID = oc.UserID
WHERE o.OrderType = 'Site-Assessed';

-- View for Orders with Appointments
CREATE OR REPLACE VIEW `v_orders_with_appointments` AS
SELECT 
  o.*,
  a_ocular.AppointmentID AS OcularAppointmentID,
  a_ocular.AppointmentDate AS OcularAppointmentDate,
  a_ocular.AppointmentTime AS OcularAppointmentTime,
  a_ocular.Status AS OcularAppointmentStatus,
  a_ocular.AssignedStaff_ID AS OcularAssignedStaffID,
  a_installation.AppointmentID AS InstallationAppointmentID,
  a_installation.AppointmentDate AS InstallationAppointmentDate,
  a_installation.AppointmentTime AS InstallationAppointmentTime,
  a_installation.Status AS InstallationAppointmentStatus,
  a_installation.AssignedStaff_ID AS InstallationAssignedStaffID
FROM `order` o
LEFT JOIN `appointments` a_ocular ON o.OrderID = a_ocular.OrderID AND a_ocular.AppointmentType = 'Ocular'
LEFT JOIN `appointments` a_installation ON o.OrderID = a_installation.OrderID AND a_installation.AppointmentType = 'Installation';

-- View for Production Queue
CREATE OR REPLACE VIEW `v_production_queue` AS
SELECT 
  o.OrderID,
  o.OrderNumber,
  o.OrderType,
  o.Status AS OrderStatus,
  o.FabricationStatus,
  o.FabricationProgress,
  o.FabricationStartDate,
  o.FabricationEndDate,
  o.ActualFabricationStartDate,
  o.ActualFabricationEndDate,
  o.FabricationStaff_ID,
  o.InstallationStaff_ID,
  c.First_Name AS CustomerFirstName,
  c.Last_Name AS CustomerLastName,
  CONCAT(c.First_Name, ' ', c.Last_Name) AS CustomerFullName,
  fs.First_Name AS FabricationStaffFirstName,
  fs.Last_Name AS FabricationStaffLastName,
  CONCAT(fs.First_Name, ' ', fs.Last_Name) AS FabricationStaffFullName,
  (SELECT GROUP_CONCAT(p.ProductName SEPARATOR ', ') 
   FROM order_items oi 
   JOIN product p ON oi.ProductID = p.ProductID 
   WHERE oi.OrderID = o.OrderID) AS ProductNames,
  (SELECT SUM(oi.Quantity) 
   FROM order_items oi 
   WHERE oi.OrderID = o.OrderID) AS TotalQuantity
FROM `order` o
LEFT JOIN `customer` c ON o.Customer_ID = c.Customer_ID
LEFT JOIN `user` fs ON o.FabricationStaff_ID = fs.UserID
WHERE o.Status IN ('Approved', 'In Fabrication', 'Ready for Installation')
ORDER BY 
  CASE o.FabricationStatus
    WHEN 'Queued' THEN 1
    WHEN 'In Progress' THEN 2
    WHEN 'Quality Check' THEN 3
    WHEN 'Ready' THEN 4
    WHEN 'Completed' THEN 5
  END,
  o.FabricationStartDate ASC;

-- ============================================================================
-- 4. INDEXES FOR PERFORMANCE
-- ============================================================================

-- Additional composite indexes for common query patterns
CREATE INDEX IF NOT EXISTS `idx_order_type_status` ON `order` (`OrderType`, `Status`);
CREATE INDEX IF NOT EXISTS `idx_order_status_dates` ON `order` (`Status`, `OrderDate`, `FabricationStartDate`);
CREATE INDEX IF NOT EXISTS `idx_appointment_order_type` ON `appointments` (`OrderID`, `AppointmentType`);
CREATE INDEX IF NOT EXISTS `idx_appointment_date_status` ON `appointments` (`AppointmentDate`, `Status`);

-- ============================================================================
-- 5. TRIGGERS FOR AUTOMATIC UPDATES
-- ============================================================================

-- Trigger to log status changes
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS `trg_order_status_change` 
AFTER UPDATE ON `order`
FOR EACH ROW
BEGIN
  IF OLD.Status != NEW.Status THEN
    INSERT INTO `order_status_history` (
      `OrderID`, 
      `OrderNumber`, 
      `PreviousStatus`, 
      `NewStatus`, 
      `ChangedBy_ID`,
      `Changed_Date`
    ) VALUES (
      NEW.OrderID,
      NEW.OrderNumber,
      OLD.Status,
      NEW.Status,
      NEW.ApprovedBy_Admin_ID, -- Assuming admin made the change
      NOW()
    );
  END IF;
END$$

DELIMITER ;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
