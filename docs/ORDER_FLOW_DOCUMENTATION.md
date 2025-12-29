# Order Management Flow Documentation
## Glassify-CI Order Processing System

---

## Table of Contents
1. [Overview](#overview)
2. [Order Status Flow Diagram](#order-status-flow-diagram)
3. [Detailed Flow Steps](#detailed-flow-steps)
4. [User Roles & Responsibilities](#user-roles--responsibilities)
5. [Database Tables & Status Mapping](#database-tables--status-mapping)
6. [API Endpoints & Actions](#api-endpoints--actions)
7. [Order Tracking Stages](#order-tracking-stages)

---

## Overview

The Glassify-CI order management system follows a multi-stage approval workflow involving Customers, Sales Representatives, and Administrators. Orders progress through various statuses from initial placement to final completion.

### Key Components:
- **Customer Interface**: Order placement, payment, and tracking
- **Sales Representative Interface**: Order review, approval requests, and final confirmation
- **Admin Interface**: Final approval, scheduling, and order management
- **Unified Order Table**: Single source of truth for all order statuses

---

## Order Status Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         ORDER LIFECYCLE FLOW                             │
└─────────────────────────────────────────────────────────────────────────┘

[CUSTOMER]
    │
    ├─► Place Order (Cart → Checkout)
    │   └─► Status: "Pending Review"
    │       └─► Order created in `order` table
    │
    ▼
[SALES REP]
    │
    ├─► View Orders (sales_orders.php)
    │   ├─► Tab: "Pending Review" (Status = 'Pending Review')
    │   ├─► Tab: "Awaiting Admin" (Status = 'Awaiting Admin')
    │   └─► Tab: "Ready to Approve" (Status = 'Ready to Approve')
    │
    ├─► Action: "Request Approval" (Pending Review orders)
    │   └─► Status: "Pending Review" → "Awaiting Admin"
    │       └─► Order moved to admin queue
    │
    ▼
[ADMIN]
    │
    ├─► View Orders (admin_orders.php)
    │   ├─► Tab: "All orders"
    │   ├─► Tab: "Order Schedule Approval" (Status = 'Awaiting Admin')
    │   └─► View order details
    │
    ├─► Action: "Approve Order" (Awaiting Admin orders)
    │   └─► Status: "Awaiting Admin" → "Ready to Approve"
    │       └─► AdminStatus = 'Approved'
    │
    ├─► Action: "Disapprove Order" (Awaiting Admin orders)
    │   └─► Status: "Awaiting Admin" → "Ready to Approve"
    │       └─► AdminStatus = 'Disapproved'
    │
    ▼
[SALES REP]
    │
    ├─► View "Ready to Approve" Tab
    │   ├─► Shows AdminStatus: "Approved" or "Disapproved"
    │   └─► Action: "Check" button
    │
    ├─► Action: "Approve Order" (if AdminStatus = 'Approved')
    │   └─► Status: "Ready to Approve" → "Approved"
    │       └─► Customer notified
    │       └─► Payment record created
    │
    ├─► Action: "Disapprove Order" (if AdminStatus = 'Disapproved')
    │   └─► Status: "Ready to Approve" → "Disapproved"
    │       └─► Order cancelled
    │
    ▼
[CUSTOMER]
    │
    ├─► Receive Approval Notification
    │   └─► Order Status: "Approved"
    │
    ├─► Payment Processing
    │   ├─► E-Wallet: Upload receipt
    │   └─► Cash on Delivery: Marked on delivery
    │
    ▼
[SYSTEM]
    │
    ├─► Order Fulfillment Stages
    │   ├─► 1. Order Placed ✓
    │   ├─► 2. Ocular Visit (Scheduled)
    │   ├─► 3. In Fabrication (Scheduled)
    │   ├─► 4. Installed (Scheduled)
    │   └─► 5. Completed ✓
    │
    ▼
[COMPLETED]
```

---

## Detailed Flow Steps

### Stage 1: Order Creation (Customer)

**View**: `application/views/shop/checkout.php`  
**Controller**: `CartCon`, `ShopCon`  
**Database**: `order`, `order_items`, `cart`

**Process:**
1. Customer adds items to cart (`cart` table)
2. Customer proceeds to checkout
3. Order created in `order` table with:
   - `Status` = `'Pending Review'`
   - `SalesRep_ID` = Assigned sales representative
   - `Customer_ID` = Customer ID
   - `OrderNumber` = Auto-generated (e.g., "GI001")
4. Order items created in `order_items` table
5. Cart items cleared

**Status After**: `Pending Review`

---

### Stage 2: Sales Rep Review (Pending Review)

**View**: `application/views/sales_page/sales_orders.php`  
**Controller**: `SalesCon->sales_orders()`  
**Database**: `order` (Status = 'Pending Review')

**Process:**
1. Sales Rep views "Pending Review" tab
2. Sees list of orders with Status = 'Pending Review'
3. Can click "Request Approval" button on any order
4. Popup shows order details:
   - Product information
   - Customization details
   - Customer address
   - Total quotation
   - Notes field (optional)

**Actions Available:**
- **Request Approval**: Moves order to admin queue
- **View Details**: See full order information

**Status After**: `Awaiting Admin` (if approved) or remains `Pending Review`

---

### Stage 3: Request Admin Approval

**View**: `application/views/sales_page/sales_orders.php` (Popup)  
**Controller**: `SalesCon->request_approval()`  
**Database**: `order`, `awaiting_admin_orders` (legacy), `system_activity_log`

**Process:**
1. Sales Rep clicks "Request Approval" button
2. Fills in optional notes
3. Clicks "Submit to Admin"
4. System updates:
   - `order.Status` = `'Awaiting Admin'`
   - Inserts into `awaiting_admin_orders` (legacy table)
   - Logs activity in `system_activity_log`
5. Order disappears from "Pending Review" tab
6. Order appears in "Awaiting Admin" tab (read-only for Sales Rep)

**Transaction Steps:**
```sql
START TRANSACTION;
UPDATE `order` SET Status = 'Awaiting Admin' WHERE OrderID = ?;
INSERT INTO awaiting_admin_orders (...);
INSERT INTO system_activity_log (Action: 'Approval Requested');
COMMIT;
```

**Status After**: `Awaiting Admin`

---

### Stage 4: Admin Review

**View**: `application/views/admin_page/admin_orders.php`  
**Controller**: `AdminCon->get_awaiting_approval_orders()`, `AdminCon->approve_order_admin()`, `AdminCon->disapprove_order_admin()`  
**Database**: `order`, `ready_to_approve_orders` (legacy), `awaiting_admin_orders` (legacy)

**Process:**
1. Admin views "Order Schedule Approval" section
2. Sees orders with Status = 'Awaiting Admin'
3. Clicks "View" or "Review" on an order
4. Popup shows:
   - Full order details
   - Customer information
   - Sales Rep information
   - Scheduled dates
   - Customization details
5. Admin can:
   - **Approve**: Moves to "Ready to Approve" with AdminStatus = 'Approved'
   - **Disapprove**: Moves to "Ready to Approve" with AdminStatus = 'Disapproved' (requires reason)

**Transaction Steps (Approve):**
```sql
START TRANSACTION;
UPDATE `order` SET Status = 'Ready to Approve', ApprovedBy_Admin_ID = ?, Approved_Date = ? WHERE OrderID = ?;
INSERT INTO ready_to_approve_orders (AdminStatus = 'Approved', AdminNotes = ?);
DELETE FROM awaiting_admin_orders WHERE OrderID = ?;
INSERT INTO system_activity_log (Action: 'Order Approved by Admin');
COMMIT;
```

**Transaction Steps (Disapprove):**
```sql
START TRANSACTION;
UPDATE `order` SET Status = 'Ready to Approve', DisapprovedBy = 'Admin', DisapprovedBy_ID = ?, DisapprovalReason = ?, Disapproved_Date = ? WHERE OrderID = ?;
INSERT INTO ready_to_approve_orders (AdminStatus = 'Disapproved', AdminNotes = ?);
DELETE FROM awaiting_admin_orders WHERE OrderID = ?;
INSERT INTO system_activity_log (Action: 'Order Disapproved by Admin');
COMMIT;
```

**Status After**: `Ready to Approve` (with AdminStatus = 'Approved' or 'Disapproved')

---

### Stage 5: Sales Rep Final Approval

**View**: `application/views/sales_page/sales_orders.php` ("Ready to Approve" tab)  
**Controller**: `SalesCon->approve_order()`, `SalesCon->disapprove_order()`  
**Database**: `order`, `payment`, `approved_orders` (legacy), `ready_to_approve_orders` (legacy)

**Process:**
1. Sales Rep views "Ready to Approve" tab
2. Sees orders with:
   - Status = 'Ready to Approve'
   - AdminStatus column showing "Approved" or "Disapproved"
3. Clicks "Check" button on an order
4. Popup shows:
   - Order details
   - Admin notes (if any)
   - AdminStatus indicator

**If AdminStatus = 'Approved':**
- Sales Rep clicks "Approve Order"
- System updates:
  - `order.Status` = `'Approved'`
  - `order.ApprovedBy_SalesRep_ID` = Sales Rep ID
  - `order.Approved_Date` = Current timestamp
  - Creates payment record in `payment` table (Status = 'Pending')
  - Inserts into `approved_orders` (legacy)
  - Deletes from `ready_to_approve_orders`
  - Notifies customer
  - Logs activity

**If AdminStatus = 'Disapproved':**
- Sales Rep clicks "Disapprove Order"
- System updates:
  - `order.Status` = `'Disapproved'`
  - `order.DisapprovedBy` = 'Sales Rep'
  - `order.DisapprovedBy_ID` = Sales Rep ID
  - `order.DisapprovalReason` = Combined admin + sales rep reason
  - Inserts into `disapproved_orders` (legacy)
  - Deletes from `ready_to_approve_orders`
  - Logs activity

**Transaction Steps (Approve):**
```sql
START TRANSACTION;
UPDATE `order` SET Status = 'Approved', ApprovedBy_SalesRep_ID = ?, Approved_Date = ? WHERE OrderID = ?;
INSERT INTO payment (OrderID, Status = 'Pending', Amount, ...);
INSERT INTO approved_orders (...);
DELETE FROM ready_to_approve_orders WHERE OrderID = ?;
INSERT INTO system_activity_log (Action: 'Order Approved');
COMMIT;
```

**Status After**: `Approved` or `Disapproved`

---

### Stage 6: Payment Processing

**View**: `application/views/shop/ewallet.php`, `application/views/sales_page/sales_payments.php`  
**Controller**: `ShopCon`, `SalesCon->mark_payment_paid()`  
**Database**: `payment`, `order`

**Process:**

**E-Wallet Payment:**
1. Customer receives approval notification
2. Customer uploads payment receipt
3. Receipt stored in `payment.ReceiptPath`
4. `payment.Status` = 'Pending' (awaiting verification)
5. Sales Rep verifies receipt
6. Sales Rep marks payment as paid
7. `payment.Status` = 'Paid'
8. `order.PaymentStatus` = 'Paid'

**Cash on Delivery:**
1. Customer selects "Cash on Delivery" at checkout
2. `order.PaymentMethod` = 'Cash on Delivery'
3. Payment marked as paid upon delivery completion
4. `order.PaymentStatus` = 'Paid'

**Status After**: Payment completed, order ready for fulfillment

---

### Stage 7: Order Fulfillment & Tracking

**View**: `application/views/shop/order_tracking.php`  
**Controller**: `ShopCon->order_tracking()`  
**Database**: `order`, `appointments`, `projectschedule`

**Process:**
Orders progress through these stages (tracked in `order_tracking.php`):

1. **Order Placed** ✓
   - Status: `Approved`
   - Date: `order.OrderDate`
   - Progress: 0%

2. **Ocular Visit** (Scheduled)
   - Status: `In Fabrication` (after visit)
   - Date: `order.OcularDate`
   - Progress: 25%
   - Appointment created in `appointments` table

3. **In Fabrication** (Scheduled)
   - Status: `In Fabrication`
   - Date: `order.FabricationDate`
   - Progress: 50%
   - Project scheduled in `projectschedule` table

4. **Installed** (Scheduled)
   - Status: `Ready for Installation` → `Completed`
   - Date: `order.InstallationDate`
   - Progress: 75%
   - Installation appointment scheduled

5. **Completed** ✓
   - Status: `Completed`
   - Date: `order.EstimatedDelivery`
   - Progress: 100%
   - Order fully delivered

**Status Progression:**
```
Approved → In Fabrication → Ready for Installation → Completed
```

---

## User Roles & Responsibilities

### Customer
- **Actions:**
  - Place orders
  - Upload payment receipts (E-Wallet)
  - Track order status
  - View order history
- **Views:**
  - `shop/checkout.php`
  - `shop/order_tracking.php`
  - `shop/list_product.php` (order history)

### Sales Representative
- **Actions:**
  - Review pending orders
  - Request admin approval
  - Final approve/disapprove orders
  - Verify payments
  - Manage customer communications
- **Views:**
  - `sales_page/sales_orders.php`
  - `sales_page/sales_payments.php`
  - `sales_page/sales_dashboard.php`

### Administrator
- **Actions:**
  - Review orders awaiting approval
  - Approve/disapprove orders (with notes)
  - Schedule appointments
  - Manage project schedules
  - View all orders
- **Views:**
  - `admin_page/admin_orders.php`
  - `admin_page/admin_dashboard.php`

---

## Database Tables & Status Mapping

### Primary Order Table: `order`

**Status Enum Values:**
```sql
'Pending Review'        -- Initial state, awaiting sales rep review
'Awaiting Admin'        -- Sales rep requested admin approval
'Ready to Approve'      -- Admin reviewed, awaiting sales rep final decision
'Approved'              -- Final approval, ready for payment
'Disapproved'          -- Order rejected
'In Fabrication'        -- Order being manufactured
'Ready for Installation' -- Ready to install
'Completed'             -- Order delivered and completed
'Cancelled'             -- Order cancelled
'Returned'              -- Order returned
```

**Key Fields:**
- `OrderID` (Primary Key)
- `OrderNumber` (e.g., "GI001")
- `Customer_ID` (FK to `customer`)
- `SalesRep_ID` (FK to `user`)
- `Status` (Enum - see above)
- `PaymentStatus` (Enum: 'Pending', 'Paid', 'Partial', 'Refunded')
- `PaymentMethod` (Enum: 'E-Wallet', 'Cash on Delivery')
- `ApprovedBy_SalesRep_ID` (FK to `user`, nullable)
- `ApprovedBy_Admin_ID` (FK to `user`, nullable)
- `Approved_Date` (datetime, nullable)
- `DisapprovedBy` (Enum: 'Sales Rep', 'Admin', nullable)
- `DisapprovedBy_ID` (FK to `user`, nullable)
- `DisapprovalReason` (text, nullable)
- `Disapproved_Date` (datetime, nullable)

### Legacy Tables (Backward Compatibility)

**`pending_review_orders`**
- Used for orders with Status = 'Pending Review'
- Populated when order is created

**`awaiting_admin_orders`**
- Used for orders with Status = 'Awaiting Admin'
- Populated when sales rep requests approval

**`ready_to_approve_orders`**
- Used for orders with Status = 'Ready to Approve'
- Contains `AdminStatus` field ('Approved' or 'Disapproved')
- Contains `AdminNotes` field

**`approved_orders`**
- Used for orders with Status = 'Approved'
- Contains customer notification status

**`disapproved_orders`**
- Used for orders with Status = 'Disapproved'
- Contains disapproval reasons

### Related Tables

**`order_items`**
- Line items for each order
- Links to `product` and `customization`

**`payment`**
- Payment records
- Status: 'Pending', 'Paid', 'Failed', 'Refunded'

**`appointments`**
- Service scheduling (Ocular Visit, Installation)
- Links to `order` and `customer`

**`projectschedule`**
- Project scheduling for fabrication
- Links to `order` and admin user

---

## API Endpoints & Actions

### Sales Representative Endpoints

**Get Orders:**
- `SalesCon->sales_orders()` - Returns orders for current sales rep
- Filters by status: 'Pending Review', 'Awaiting Admin', 'Ready to Approve'

**Request Approval:**
- `POST /SalesCon/request_approval`
- Parameters: `order_id`, `notes` (optional)
- Updates: Status = 'Awaiting Admin'

**Approve Order:**
- `POST /SalesCon/approve_order`
- Parameters: `order_id`
- Updates: Status = 'Approved', creates payment record

**Disapprove Order:**
- `POST /SalesCon/disapprove_order`
- Parameters: `order_id`, `reason` (optional)
- Updates: Status = 'Disapproved'

**Get Order Details:**
- `POST /SalesCon/get_order_details`
- Returns: Full order information for popup display

### Administrator Endpoints

**Get Awaiting Approval Orders:**
- `GET /AdminCon/get_awaiting_approval_orders`
- Returns: Orders with Status = 'Awaiting Admin'

**Approve Order (Admin):**
- `POST /AdminCon/approve_order_admin`
- Parameters: `order_id`, `admin_notes` (optional)
- Updates: Status = 'Ready to Approve', AdminStatus = 'Approved'

**Disapprove Order (Admin):**
- `POST /AdminCon/disapprove_order_admin`
- Parameters: `order_id`, `disapproval_reason` (required)
- Updates: Status = 'Ready to Approve', AdminStatus = 'Disapproved'

**Get Approval Order Details:**
- `POST /AdminCon/get_approval_order_details`
- Returns: Full order information for admin review popup

### Customer Endpoints

**Order Tracking:**
- `GET /ShopCon/order_tracking/{order_id}`
- Returns: Order status, progress stages, order items

**Payment Upload:**
- `POST /ShopCon/upload_payment_receipt`
- Parameters: `order_id`, `receipt_file`
- Updates: `payment.ReceiptPath`, `payment.Status` = 'Pending'

---

## Order Tracking Stages

### Visual Progress Indicator

The customer-facing order tracking page (`order_tracking.php`) displays a progress bar with 5 stages:

```
[✓] Order Placed (0%)
    ↓
[ ] Ocular Visit (25%)
    ↓
[ ] In Fabrication (50%)
    ↓
[ ] Installed (75%)
    ↓
[ ] Completed (100%)
```

### Stage Details

**1. Order Placed**
- **Status**: `Approved`
- **Date Field**: `order.OrderDate`
- **Completed When**: Order is approved by sales rep
- **Progress**: 0%

**2. Ocular Visit**
- **Status**: `In Fabrication` (after visit)
- **Date Field**: `order.OcularDate`
- **Scheduled In**: `appointments` table (Service = 'Ocular Visit')
- **Progress**: 25%
- **Completed When**: Visit appointment marked as 'Complete'

**3. In Fabrication**
- **Status**: `In Fabrication`
- **Date Field**: `order.FabricationDate`
- **Scheduled In**: `projectschedule` table
- **Progress**: 50%
- **Completed When**: Project status = 'Completed'

**4. Installed**
- **Status**: `Ready for Installation` → `Completed`
- **Date Field**: `order.InstallationDate`
- **Scheduled In**: `appointments` table (Service = 'Installed')
- **Progress**: 75%
- **Completed When**: Installation appointment marked as 'Complete'

**5. Completed**
- **Status**: `Completed`
- **Date Field**: `order.EstimatedDelivery`
- **Progress**: 100%
- **Completed When**: All stages finished, order delivered

---

## Status Transition Rules

### Valid Transitions

```
Pending Review
    ↓ (Sales Rep: Request Approval)
Awaiting Admin
    ↓ (Admin: Approve)
Ready to Approve (AdminStatus = 'Approved')
    ↓ (Sales Rep: Approve Order)
Approved
    ↓ (System: Schedule)
In Fabrication
    ↓ (System: Complete)
Ready for Installation
    ↓ (System: Install)
Completed

Pending Review
    ↓ (Sales Rep: Request Approval)
Awaiting Admin
    ↓ (Admin: Disapprove)
Ready to Approve (AdminStatus = 'Disapproved')
    ↓ (Sales Rep: Disapprove Order)
Disapproved
```

### Invalid Transitions (Blocked)

- Cannot skip stages (e.g., Pending Review → Approved)
- Cannot go backwards (e.g., Approved → Pending Review)
- Admin cannot directly approve to 'Approved' (must go through 'Ready to Approve')

---

## Notes & Best Practices

1. **Transaction Safety**: All status changes use database transactions to ensure data consistency
2. **Activity Logging**: All actions are logged in `system_activity_log` for audit trail
3. **Notifications**: Customer notifications are triggered when order is approved
4. **Legacy Tables**: System maintains backward compatibility with legacy status tables
5. **Status Consistency**: Primary source of truth is the `order` table; legacy tables are for display only
6. **Error Handling**: All endpoints return JSON responses with success/error status
7. **Role-Based Access**: Each role can only perform actions appropriate to their permissions

---

## Future Enhancements

1. **Email Notifications**: Implement actual email sending for order status changes
2. **SMS Notifications**: Add SMS alerts for critical status changes
3. **Real-time Updates**: WebSocket integration for live order status updates
4. **Automated Scheduling**: Auto-schedule appointments based on order approval
5. **Payment Gateway Integration**: Direct payment processing instead of receipt upload
6. **Order Cancellation**: Allow customers to cancel orders (with restrictions)
7. **Return/Refund Workflow**: Complete return and refund processing flow

---

**Document Version**: 1.0  
**Last Updated**: 2025-01-08  
**Maintained By**: Development Team
