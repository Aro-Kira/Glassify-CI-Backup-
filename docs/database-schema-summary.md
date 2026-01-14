# Database Schema Summary - Admin Order Management System

This document provides a summary of the database schema required for the Admin Order Management System based on `admin-order-management-structure.md`.

## Table of Contents
1. [Updated Existing Tables](#updated-existing-tables)
2. [New Tables](#new-tables)
3. [Views](#views)
4. [Key Relationships](#key-relationships)

---

## Updated Existing Tables

### 1. `order` Table - Additional Fields

#### Order Type & Ocular Fields
- `OrderType` (enum: 'Direct', 'Site-Assessed') - Distinguishes order types
- `OcularCompleted` (tinyint) - Flag for site assessment completion
- `OcularNotes` (text) - Site assessment notes and measurements
- `OcularDate` (date) - Scheduled ocular visit date
- `OcularCompletedBy_ID` (int, FK) - Staff who completed ocular

#### Staff Assignment Fields
- `FabricationStaff_ID` (int, FK) - Assigned fabrication staff
- `InstallationStaff_ID` (int, FK) - Assigned installation staff

#### Fabrication/Production Fields
- `FabricationStartDate` (date) - Expected fabrication start
- `FabricationEndDate` (date) - Expected fabrication end
- `ActualFabricationStartDate` (date) - Actual start date
- `ActualFabricationEndDate` (date) - Actual end date
- `FabricationProgress` (int, 0-100) - Progress percentage
- `FabricationStatus` (enum: 'Queued', 'In Progress', 'Quality Check', 'Ready', 'Completed')
- `FabricationNotes` (text) - Production notes
- `QualityCheckNotes` (text) - QC notes

#### Date Fields
- `PreferredInstallationDate` (date) - Customer preference
- `FabricationDate` (date) - Scheduled fabrication date
- `InstallationDate` (date) - Scheduled installation date
- `EstimatedDelivery` (date) - Estimated completion

#### Notes & Admin Fields
- `AdminNotes` (text) - Internal admin notes
- `CustomerNotes` (text) - Customer-facing notes
- `StaffNotes` (text) - Staff-specific notes

#### Barcode
- `Barcode` (varchar) - Order barcode/QR code
- `BarcodeImagePath` (varchar) - Path to barcode image

### 2. `appointments` Table - Additional Fields

#### Enhanced Fields
- `AppointmentType` (enum: 'Ocular', 'Installation') - Type of appointment
- `AssignedStaff_ID` (int, FK) - Assigned staff member ID

#### Ocular-Specific
- `OcularNotes` (text) - Site assessment notes
- `OcularReportPath` (varchar) - Path to ocular report PDF

#### Installation-Specific
- `InstallationNotes` (text) - Installation notes
- `InstallationChecklist` (json) - Checklist items

#### Additional
- `SitePhotos` (json) - Array of site photo paths
- `InternalNotes` (text) - Internal admin notes
- `CustomerVisibleNotes` (text) - Customer-visible notes

---

## New Tables

### 1. `quotation` - Quotations Management
**Purpose:** Store quotation information before conversion to orders

**Key Fields:**
- `QuotationID` (PK)
- `QuotationNumber` (unique)
- `Customer_ID` (FK)
- `SalesRep_ID` (FK)
- `CreatedDate`, `ExpiryDate`
- `Status` (enum: 'Pending', 'Approved', 'Rejected', 'Converted to Order')
- `TotalAmount`
- `ConvertedToOrder_ID` (FK) - Links to order if converted
- `Notes`, `RejectionReason`

### 2. `quotation_items` - Quotation Line Items
**Purpose:** Store products/items in quotations

**Key Fields:**
- `QuotationItemID` (PK)
- `QuotationID` (FK)
- `ProductID` (FK)
- `Quantity`, `UnitPrice`, `Subtotal`
- Product specifications (Shape, Dimension, Type, Thickness, EdgeWork, FrameType, Engraving)
- `DesignFile` - Path to design file
- `Specifications` (text/JSON) - Additional specs

### 3. `return_order` - Return Orders Management
**Purpose:** Handle order returns and refunds

**Key Fields:**
- `ReturnID` (PK)
- `ReturnNumber` (unique)
- `OriginalOrder_ID` (FK) - Original order reference
- `Customer_ID` (FK)
- `ReturnDate`
- `ReturnType` (enum: 'Defect', 'Wrong Item', 'Customer Request', 'Other')
- `ReturnReason`, `ReturnDescription`
- `ReturnStatus` (enum: 'Pending', 'Approved', 'Rejected', 'Processing', 'Completed')
- `ReplacementRequired` (tinyint)
- `ReplacementOrder_ID` (FK) - Replacement order if created
- `ReplacementAppointment_ID` (FK) - Replacement installation appointment
- `RefundAmount`, `RefundMethod`, `RefundStatus`, `RefundDate`
- `ReturnPhotos` (json) - Array of photo paths
- `AdminNotes`

### 4. `return_order_items` - Return Order Line Items
**Purpose:** Store items being returned

**Key Fields:**
- `ReturnItemID` (PK)
- `ReturnID` (FK)
- `OrderItemID` (FK) - Reference to original order item
- `ProductID` (FK)
- `QuantityReturned`
- `ProductName` - Product name at time of return
- `Specifications` (text/JSON)

### 5. `order_status_history` - Status Change Tracking
**Purpose:** Track all status changes for audit trail

**Key Fields:**
- `StatusHistoryID` (PK)
- `OrderID` (FK)
- `OrderNumber` - For quick reference
- `PreviousStatus`, `NewStatus`
- `ChangedBy_ID` (FK) - User who made change
- `ChangedBy_Type` (enum: 'Admin', 'Sales Rep', 'System')
- `ChangeReason`, `Notes`
- `Changed_Date`

### 6. `fabrication_materials` - Materials Used in Production
**Purpose:** Track materials used during fabrication

**Key Fields:**
- `MaterialID` (PK)
- `OrderID` (FK)
- `MaterialName`, `MaterialType`
- `Quantity`, `Unit`
- `UnitCost`, `TotalCost`
- `Notes`

### 7. `fabrication_progress_photos` - Production Progress Photos
**Purpose:** Store photos of fabrication progress

**Key Fields:**
- `PhotoID` (PK)
- `OrderID` (FK)
- `PhotoPath`
- `PhotoDescription`
- `PhotoDate`
- `UploadedBy_ID` (FK)

### 8. `order_notes` - Timestamped Notes
**Purpose:** Store timestamped notes for orders

**Key Fields:**
- `NoteID` (PK)
- `OrderID` (FK)
- `NoteType` (enum: 'Admin', 'Customer', 'Staff', 'System')
- `NoteContent`
- `CreatedBy_ID` (FK)
- `IsVisibleToCustomer` (tinyint)
- `AttachmentPath`
- `Created_Date`

### 9. `staff_assignment_history` - Staff Assignment Tracking
**Purpose:** Track staff assignment changes

**Key Fields:**
- `AssignmentID` (PK)
- `OrderID` (FK)
- `StaffType` (enum: 'Fabrication', 'Installation', 'Ocular')
- `Staff_ID` (FK)
- `AssignedBy_ID` (FK)
- `AssignedDate`, `UnassignedDate`
- `IsActive` (tinyint)
- `Notes`

### 10. `appointment_status_history` - Appointment Status Tracking
**Purpose:** Track appointment status changes

**Key Fields:**
- `AppointmentHistoryID` (PK)
- `AppointmentID` (FK)
- `PreviousStatus`, `NewStatus`
- `ChangedBy_ID` (FK)
- `ChangeReason`
- `Changed_Date`

---

## Views

### 1. `v_direct_orders`
**Purpose:** Pre-joined view for Direct Orders with customer and staff information

**Includes:**
- All order fields
- Customer name, email, phone
- Sales rep name
- Fabrication staff name
- Installation staff name

### 2. `v_site_assessed_orders`
**Purpose:** Pre-joined view for Site-Assessed Orders with all related information

**Includes:**
- All fields from `v_direct_orders`
- Ocular completed by staff name

### 3. `v_orders_with_appointments`
**Purpose:** Orders with linked appointment information

**Includes:**
- All order fields
- Ocular appointment details (ID, date, time, status, staff)
- Installation appointment details (ID, date, time, status, staff)

### 4. `v_production_queue`
**Purpose:** Production queue view with all relevant information

**Includes:**
- Order and fabrication details
- Customer information
- Fabrication staff information
- Product names (concatenated)
- Total quantity
- Sorted by fabrication status and start date

---

## Key Relationships

### Order Relationships
```
order
├── customer (Many-to-One)
├── user (SalesRep) (Many-to-One)
├── user (FabricationStaff) (Many-to-One)
├── user (InstallationStaff) (Many-to-One)
├── user (OcularCompletedBy) (Many-to-One)
├── order_items (One-to-Many)
├── appointments (One-to-Many)
├── payment (One-to-Many)
├── quotation (One-to-One, if converted from quotation)
├── return_order (One-to-Many, as original order)
├── order_status_history (One-to-Many)
├── fabrication_materials (One-to-Many)
├── fabrication_progress_photos (One-to-Many)
└── order_notes (One-to-Many)
```

### Appointment Relationships
```
appointments
├── order (Many-to-One)
├── user (AssignedStaff) (Many-to-One)
└── appointment_status_history (One-to-Many)
```

### Quotation Relationships
```
quotation
├── customer (Many-to-One)
├── user (SalesRep) (Many-to-One)
├── order (One-to-One, if converted)
└── quotation_items (One-to-Many)
```

### Return Order Relationships
```
return_order
├── order (OriginalOrder) (Many-to-One)
├── customer (Many-to-One)
├── order (ReplacementOrder) (One-to-One, if replacement created)
├── appointments (ReplacementAppointment) (One-to-One, if scheduled)
└── return_order_items (One-to-Many)
```

---

## Indexes

### Performance Indexes
- `idx_order_type` - Order type filtering
- `idx_ocular_completed` - Ocular completion filtering
- `idx_fabrication_staff` - Fabrication staff filtering
- `idx_installation_staff` - Installation staff filtering
- `idx_fabrication_status` - Fabrication status filtering
- `idx_fabrication_dates` - Date range queries
- `idx_order_type_status` - Composite: Order type + status
- `idx_order_status_dates` - Composite: Status + dates
- `idx_appointment_order_type` - Composite: Order + appointment type
- `idx_appointment_date_status` - Composite: Date + status

---

## Triggers

### `trg_order_status_change`
**Purpose:** Automatically log status changes to `order_status_history`

**Triggered:** After UPDATE on `order` table when `Status` changes

---

## Implementation Notes

1. **Order Type Separation**: Use `OrderType` field to filter Direct vs Site-Assessed orders
2. **Status Transitions**: Validate status transitions using `order_status_history`
3. **Staff Assignment**: Track all assignment changes in `staff_assignment_history`
4. **Ocular Workflow**: Site-Assessed orders require `OcularCompleted = 1` before fabrication
5. **Fabrication Queue**: Use `FabricationStatus` for Kanban board columns
6. **Calendar Integration**: Link orders and appointments via foreign keys
7. **Return Processing**: Link returns to original orders and track replacements
8. **Quotation Conversion**: When converting quotation to order, set `ConvertedToOrder_ID`

---

## File Location

The complete SQL schema file is located at:
`docs/database-schema-admin-order-management.sql`

This file contains:
- All ALTER TABLE statements for existing tables
- All CREATE TABLE statements for new tables
- All CREATE VIEW statements
- All CREATE INDEX statements
- Trigger definitions

---

## Next Steps

1. Review the SQL schema file
2. Test on development database
3. Adjust field types/sizes as needed for your specific requirements
4. Add any additional indexes based on query patterns
5. Implement data migration scripts if needed
6. Update application models to use new fields and tables
