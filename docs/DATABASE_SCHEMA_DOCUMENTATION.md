# Glassify Database Schema Documentation

## Overview

This document provides a comprehensive overview of the Glassify database schema. Glassify is an e-commerce system for managing glass, aluminum, and steel works including orders, appointments, inventory, and customer management.

## Database Structure

The database is organized into 13 main sections:

1. **User Management** - User accounts, customers, addresses
2. **Product Management** - Products, customizations
3. **Inventory Management** - Stock items, materials, transactions
4. **Shopping Cart & Wishlist** - Cart and wishlist functionality
5. **Order Management** - Orders, order items, order status tracking
6. **Payment** - Payment transactions
7. **Appointments** - Ocular visits and installation appointments
8. **Quotations** - Quote management
9. **Return Orders** - Return order processing
10. **Project Schedule** - Project scheduling
11. **Issue Reporting** - Customer issue tracking
12. **Notifications** - System notifications
13. **System Logging** - Activity and status history logs

---

## Entity Relationship Diagram (Text Representation)

```
┌─────────────────┐
│      user       │
│─────────────────│
│ UserID (PK)     │
│ First_Name      │
│ Last_Name       │
│ Email (UNIQUE)  │
│ Password        │
│ Role            │
│ Status          │
└────────┬────────┘
         │
         │ 1:1
         │
┌────────▼────────┐
│    customer     │
│─────────────────│
│ Customer_ID(PK)│
│ UserID (FK)     │
└────────┬────────┘
         │
         │ 1:N
         │
    ┌────┴────┬──────────────┬──────────────┬──────────────┐
    │         │              │              │              │
┌───▼───┐ ┌──▼────┐ ┌───────▼──────┐ ┌──────▼──────┐ ┌───▼──────┐
│ cart  │ │wishlist│ │ customization│ │   order     │ │issuereport│
└───────┘ └────────┘ └──────────────┘ └──────┬──────┘ └──────────┘
                                               │
                                               │ 1:N
                                               │
                                          ┌────▼──────┐
                                          │order_items│
                                          └───────────┘
                                               │
                                               │ N:1
                                               │
                                          ┌────▼──────┐
                                          │  product  │
                                          └───────────┘
                                               │
                                               │ 1:N
                                               │
                                          ┌────▼──────────────┐
                                          │product_materials  │
                                          └────┬──────────────┘
                                               │
                                               │ N:1
                                               │
                                          ┌────▼──────────────┐
                                          │inventory_items    │
                                          └───────────────────┘
```

---

## Table Descriptions

### 1. User Management Tables

#### `user`
Core user table for all system users.
- **Roles**: Admin, Sales Representative, Inventory Officer, Customer
- **Key Fields**: UserID, Email (unique), Role, Status
- **Relationships**: 
  - 1:1 with `customer`
  - 1:N with `user_address`
  - Referenced by many tables for staff assignments

#### `customer`
Customer-specific information linked to user table.
- **Key Fields**: Customer_ID, UserID (FK to user)
- **Relationships**: 
  - N:1 with `user`
  - 1:N with orders, cart, wishlist, customization

#### `user_address`
User shipping and billing addresses.
- **Key Fields**: AddressID, UserID, AddressType
- **Relationships**: N:1 with `user`

---

### 2. Product Management Tables

#### `product`
Product catalog (Glass, Aluminum products).
- **Key Fields**: Product_ID, ProductName, Category, Material, Price
- **Relationships**: 
  - 1:N with `customization`, `order_items`, `cart`, `wishlist`
  - 1:N with `product_materials`

#### `customization`
Product customization options (dimensions, glass type, etc.).
- **Key Fields**: CustomizationID, Customer_ID, Product_ID, Dimensions, GlassType, EstimatePrice
- **Relationships**: 
  - N:1 with `customer`, `product`
  - 1:N with `cart`, `wishlist`, `order_items`

---

### 3. Inventory Management Tables

#### `inventory_items`
Raw materials and inventory items.
- **Key Fields**: InventoryItemID, ItemID (unique), Name, InStock, min_threshold
- **Relationships**: 
  - 1:N with `product_materials`, `stock_transactions`, `inventory_notifications`, `activities`

#### `product_materials`
Materials required for each product.
- **Key Fields**: ProductMaterialID, Product_ID, InventoryItemID, QuantityRequired
- **Relationships**: 
  - N:1 with `product`, `inventory_items`

#### `stock_transactions`
Inventory stock movement history.
- **Key Fields**: transaction_id, InventoryItemID, transaction_type, quantity
- **Relationships**: N:1 with `inventory_items`, `user`

#### `inventory_notifications`
Low stock and inventory alerts.
- **Key Fields**: NotificationID, InventoryItemID, Message, Status
- **Relationships**: N:1 with `inventory_items`

#### `activities`
Inventory activity log.
- **Key Fields**: activity_id, action, InventoryItemID, user_id
- **Relationships**: N:1 with `inventory_items`, `user`

---

### 4. Shopping Cart & Wishlist Tables

#### `cart`
Shopping cart items.
- **Key Fields**: Cart_ID, Customer_ID, Product_ID, CustomizationID, Quantity
- **Unique Constraint**: (Customer_ID, Product_ID, CustomizationID)
- **Relationships**: 
  - N:1 with `customer`, `product`, `customization`

#### `wishlist`
Customer wishlist items.
- **Key Fields**: Wishlist_ID, Customer_ID, Product_ID, CustomizationID
- **Unique Constraint**: (Customer_ID, Product_ID, CustomizationID)
- **Relationships**: 
  - N:1 with `customer`, `product`, `customization`

---

### 5. Order Management Tables

#### `order`
Main order table with comprehensive order management.
- **Key Fields**: 
  - OrderID, OrderNumber (unique), Customer_ID, SalesRep_ID
  - Status, PaymentStatus, OrderType (Direct/Site-Assessed)
  - FabricationStatus, FabricationProgress
  - Various date fields (OcularDate, FabricationDate, InstallationDate)
- **Status Flow**: 
  - Pending Review → Awaiting Admin → Ready to Approve → Approved → In Fabrication → Ready for Installation → Completed
- **Relationships**: 
  - N:1 with `customer`, `user` (SalesRep, Admin, Staff)
  - 1:N with `order_items`, `payment`, `appointments`, `quotation`, `return_order`

#### `order_items`
Items within each order.
- **Key Fields**: OrderItemID, OrderID, Product_ID, CustomizationID, Quantity, UnitPrice, EstimatePrice
- **Relationships**: 
  - N:1 with `order`, `product`, `customization`

#### Order Status Tracking Tables
- `pending_review_orders` - Orders pending sales rep review
- `awaiting_admin_orders` - Orders awaiting admin approval
- `ready_to_approve_orders` - Orders ready for sales rep approval
- `approved_orders` - Approved orders tracking
- `disapproved_orders` - Disapproved orders tracking

---

### 6. Payment Tables

#### `payment`
Payment transactions.
- **Key Fields**: Payment_ID, OrderID, PaymentMethod, Amount, Status, ReceiptPath
- **Relationships**: N:1 with `order`

---

### 7. Appointment Tables

#### `appointments`
Ocular visits and installation appointments.
- **Key Fields**: 
  - AppointmentID, OrderID, Customer_ID
  - AppointmentType (Ocular/Installation)
  - AppointmentDate, AppointmentTime, AssignedStaff_ID
  - OcularNotes, InstallationNotes, SitePhotos (JSON)
- **Relationships**: 
  - N:1 with `order`, `customer`, `user` (AssignedStaff)

---

### 8. Quotation Tables

#### `quotation`
Quotation/quote management.
- **Key Fields**: QuotationID, OrderID, Quotation_num (unique), Total_amount, Pdf_url
- **Relationships**: N:1 with `order`

---

### 9. Return Order Tables

#### `return_order`
Return order management.
- **Key Fields**: 
  - ReturnID, ReturnNumber (unique), OrderID, Customer_ID
  - ReturnType, ReturnStatus, RefundStatus
  - ReplacementOrderID, RefundAmount
- **Relationships**: 
  - N:1 with `order`, `customer`

---

### 10. Project Schedule Tables

#### `projectschedule`
Project scheduling.
- **Key Fields**: Schedule_ID, OrderID, Admin_ID, Project_Name, Start_Date, End_Date, Status
- **Relationships**: 
  - N:1 with `order`, `user` (Admin)

---

### 11. Issue Reporting Tables

#### `issuereport`
Customer issue reports.
- **Key Fields**: Issue_ID, Customer_ID, Order_ID, Category, Description, Status, Priority
- **Relationships**: 
  - N:1 with `customer`, `order` (optional)

---

### 12. Notification Tables

#### `sales_notif`
Sales representative notifications.
- **Key Fields**: NotificationID, Icon, Role, Description, Status, RelatedID, RelatedType
- **Purpose**: Track notifications for sales representatives

---

### 13. System Logging Tables

#### `system_activity_log`
System-wide activity logging.
- **Key Fields**: ActivityID, Action, Description, UserID, RelatedID, RelatedType
- **Relationships**: N:1 with `user`

#### `status_history`
Status change history for orders, appointments, returns, quotations.
- **Key Fields**: StatusHistoryID, EntityType, OldStatus, NewStatus, ChangedBy_ID
- **Purpose**: Audit trail for status changes

---

## Key Relationships Summary

### User Hierarchy
- `user` → `customer` (1:1) - Only customers have customer records
- `user` → `user_address` (1:N) - Users can have multiple addresses

### Order Flow
1. Customer adds items to `cart` or `wishlist`
2. Creates `customization` for products
3. Places `order` with `order_items`
4. Order goes through status tracking tables
5. `payment` is processed
6. `appointments` are scheduled (Ocular/Installation)
7. Order moves to fabrication (`FabricationStatus`)
8. Order is completed or `return_order` is created if needed

### Inventory Flow
1. `inventory_items` track raw materials
2. `product_materials` link products to required materials
3. `stock_transactions` log all stock movements
4. `inventory_notifications` alert on low stock
5. `activities` log inventory actions

### Staff Assignments
- Orders can have:
  - `SalesRep_ID` - Assigned sales representative
  - `FabricationStaff_ID` - Assigned fabrication staff
  - `InstallationStaff_ID` - Assigned installation staff
  - `OcularCompletedBy_ID` - Staff who completed ocular

---

## Indexes

The schema includes comprehensive indexing for:
- Foreign keys
- Frequently queried fields (Status, OrderDate, etc.)
- Composite indexes for common query patterns
- Unique constraints where needed

---

## Data Types and Constraints

### Common Patterns
- **IDs**: `int(11) AUTO_INCREMENT PRIMARY KEY`
- **Status Fields**: `enum()` with predefined values
- **Dates**: `date`, `datetime`, `timestamp`
- **Prices**: `decimal(10,2)` or `decimal(12,2)`
- **Text**: `varchar()` for short text, `text` for longer content
- **JSON**: `longtext` with JSON validation for structured data

### Foreign Key Constraints
- Most foreign keys use `ON DELETE CASCADE` for child records
- User references use `ON DELETE SET NULL` to preserve historical data
- All foreign keys are indexed for performance

---

## Order Status Workflow

```
Pending Review
    ↓
Awaiting Admin (if no Sales Rep or Sales Rep inactive)
    ↓
Ready to Approve (after admin approval)
    ↓
Approved (after sales rep approval)
    ↓
In Fabrication (when fabrication starts)
    ↓
Ready for Installation
    ↓
Completed
```

### Alternative Paths
- **Disapproved** - Can occur at any approval stage
- **Cancelled** - Can occur at any stage
- **Returned** - After completion

---

## Fabrication Status Workflow

```
Queued
    ↓
In Progress
    ↓
Quality Check
    ↓
Ready
    ↓
Completed
```

---

## Best Practices

1. **Always use transactions** for multi-table operations
2. **Check inventory availability** before order creation
3. **Log all status changes** in `status_history`
4. **Validate JSON fields** before storing
5. **Use indexes** for frequently queried fields
6. **Maintain referential integrity** through foreign keys
7. **Track all changes** in activity logs

---

## Notes

- The schema supports both **Direct Orders** (standard orders) and **Site-Assessed Orders** (requiring ocular visits)
- **Customization** data is stored both in `customization` table and denormalized in `order_items` for historical accuracy
- **Status tracking tables** (`pending_review_orders`, etc.) provide quick access to orders by status
- **JSON fields** are used for flexible data storage (checklists, photos, price breakdowns)
- **Barcode/QR code** support is included for order tracking

---

## Version History

- **v1.0** (2026-01-12) - Initial complete schema documentation

---

## Contact

For questions or updates to this schema, please contact the development team.
