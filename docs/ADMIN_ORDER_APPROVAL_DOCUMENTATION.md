# Admin Order Approval Documentation
## Glassify-CI - Order Approval Flow (Sales Representative Pages Archived)

---

## Table of Contents
1. [Overview](#overview)
2. [Archived Sales Representative Approval Process](#archived-sales-representative-approval-process)
3. [Current Order Approval Flow](#current-order-approval-flow)
4. [Admin Approval Process](#admin-approval-process)
5. [Information Displayed to Admin](#information-displayed-to-admin)
6. [Admin Approval Decision Criteria](#admin-approval-decision-criteria)
7. [Technical Implementation Details](#technical-implementation-details)
8. [Database Changes](#database-changes)
9. [API Endpoints](#api-endpoints)

---

## Overview

This document describes the order approval flow after archiving Sales Representative approval pages. **All order approvals are now handled directly by Administrators**, eliminating the need for Sales Representatives to approve orders.

### Key Changes
- ✅ **Sales Representative approval pages are archived** - Sales Reps can no longer approve orders
- ✅ **Direct Admin approval** - Orders go from "Awaiting Admin" directly to "Approved" status
- ✅ **Simplified workflow** - Reduced approval steps from 2 (Admin → Sales Rep) to 1 (Admin only)

---

## Archived Sales Representative Approval Process

### Previous Flow (Archived)
The following Sales Representative approval steps have been **archived** and are no longer active:

1. **Sales Rep Request Approval** (Archived)
   - Sales Reps could request admin approval for orders with status "Pending Review"
   - Status changed: `Pending Review` → `Awaiting Admin`
   - Route: `SalesCon/request_approval` (Archived)

2. **Sales Rep Final Approval** (Archived)
   - After Admin approval, Sales Reps had to give final approval
   - Status changed: `Ready to Approve` → `Approved`
   - Route: `SalesCon/approve_order` (Archived)
   - Route: `SalesCon/disapprove_order` (Archived)

### Archived Files (Reference Only)
The following files contain archived Sales Representative approval functionality:
- `application/controllers/SalesCon.php` - `approve_order()`, `disapprove_order()`, `request_approval()` methods
- `application/views/sales_page/sales_orders.php` - Approval tabs and buttons (if still present)
- `assets/js/sales-js/sales-order-approval-btn.js` - Approval button handlers
- `assets/js/sales-js/sales-order-approve-handler.js` - Approval handlers
- `assets/js/sales-js/sales-request-approval-handler.js` - Request approval handlers

---

## Current Order Approval Flow

### New Simplified Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    CURRENT ORDER APPROVAL FLOW                            │
│                     (Sales Rep Approval Archived)                         │
└─────────────────────────────────────────────────────────────────────────┘

[CUSTOMER]
    │
    ├─► Place Order (Cart → Checkout)
    │   └─► Status: "Pending Review"
    │       └─► Order created in `order` table
    │
    ▼
[ADMIN]
    │
    ├─► View Orders (admin_orders.php)
    │   ├─► Filter by Status: "Awaiting Admin"
    │   └─► View order details in modal/popup
    │
    ├─► Review Order Information
    │   ├─► Order Details
    │   ├─► Customer Information
    │   ├─► Product Specifications
    │   ├─► Pricing & Payment Method
    │   ├─► Delivery Address
    │   ├─► Design Files (if applicable)
    │   └─► Sales Rep Notes (if any)
    │
    ├─► Action: "Approve Order"
    │   └─► Status: "Awaiting Admin" → "Approved"
    │       └─► Payment record created (Status: 'Pending')
    │       └─► Customer notified
    │       └─► Order available for payment processing
    │
    ├─► Action: "Disapprove Order"
    │   └─► Status: "Awaiting Admin" → "Disapproved"
    │       └─► Disapproval reason required
    │       └─► Customer notified
    │       └─► Order cancelled
    │
    ▼
[CUSTOMER]
    │
    ├─► Receive Approval/Disapproval Notification
    │   └─► Order Status: "Approved" or "Disapproved"
    │
    ├─► Payment Processing (if Approved)
    │   ├─► E-Wallet: Upload receipt
    │   └─► Cash on Delivery: Marked on delivery
    │
    ▼
[SYSTEM]
    │
    ├─► Order Fulfillment Stages
    │   ├─► 1. Order Placed ✓
    │   ├─► 2. Approved ✓ (by Admin)
    │   ├─► 3. Payment Confirmed
    │   ├─► 4. In Fabrication
    │   ├─► 5. Ready for Installation
    │   └─► 6. Completed
```

### Order Status Flow

| Status | Description | Who Can Change | Next Status(es) |
|--------|-------------|----------------|-----------------|
| **Pending Review** | Order placed by customer, awaiting initial review | System (on order creation) | `Awaiting Admin` (manual) |
| **Awaiting Admin** | Order sent to admin for approval decision | Admin | `Approved` or `Disapproved` |
| **Approved** | Order approved by admin, ready for payment | **Admin only** | `In Fabrication` (after payment) |
| **Disapproved** | Order rejected by admin | **Admin only** | `Cancelled` |
| **In Fabrication** | Order is being manufactured | Admin | `Ready for Installation` |
| **Ready for Installation** | Order ready for installation | Admin | `Completed` |
| **Completed** | Order fulfilled and delivered | Admin | N/A |
| **Cancelled** | Order cancelled | Admin | N/A |

---

## Admin Approval Process

### Step-by-Step Process

1. **Access Orders Page**
   - Navigate to: Admin Dashboard → Orders
   - URL: `/AdminCon/orders` or `/AdminCon/orders?type=direct`
   - View: `application/views/admin_page/admin_orders.php`

2. **Filter for Orders Awaiting Approval**
   - Use Status filter: Select "Awaiting Admin"
   - Apply filters (optional):
     - Order Type (Direct/Site-Assessed)
     - Date Range
     - Client Search
     - Order Number Search

3. **View Order List**
   - Table displays:
     - Order ID
     - Client Name
     - Product Name
     - Address
     - Order Date
     - Total Amount
     - Status
     - Actions

4. **Open Order Details**
   - Click "View" or "Actions" button on the order row
   - Order Details Modal/Popup opens
   - Review all order information (see [Information Displayed to Admin](#information-displayed-to-admin))

5. **Make Approval Decision**

   **Option A: Approve Order**
   - Click "Approve Order" button
   - (Optional) Add Admin Notes
   - Confirm approval
   - Status changes: `Awaiting Admin` → `Approved`
   - Payment record created automatically
   - Customer receives notification

   **Option B: Disapprove Order**
   - Click "Disapprove Order" button
   - **Required**: Enter Disapproval Reason (mandatory field)
   - Confirm disapproval
   - Status changes: `Awaiting Admin` → `Disapproved`
   - Customer receives notification
   - Order is cancelled

6. **Post-Approval Actions**
   - Approved orders move to "Approved" status
   - Orders become available in Payments section
   - Customer can proceed with payment
   - Order progresses through fulfillment stages

---

## Information Displayed to Admin

The Admin Order Details Modal/Popup displays the following information sections for approval decisions:

### 1. Order Information Section
- **Order ID**: Order number (e.g., GI001, GI002)
- **Order Date**: Date when order was placed
- **Order Type**: Direct Order or Site-Assessed Order
- **Status**: Current order status (should be "Awaiting Admin" for approval)
- **Special Instructions**: Any special notes from customer

### 2. Customer Information Section
- **Customer Name**: Full name (First, Middle, Last)
- **Email**: Customer email address
- **Phone Number**: Customer contact number
- **Customer ID**: Internal customer identifier

### 3. Sales Representative Information Section
- **Sales Rep Name**: Name of assigned sales representative
- **Status**: Sales Rep status/availability
- **Email**: Sales Rep email
- **Phone**: Sales Rep phone number
- **Sales Rep Notes**: Any notes added by the sales representative (if applicable)

### 4. Products/Items Section
Table showing all order items:
- **Product Name**: Name of the product ordered
- **Quantity**: Number of items
- **Unit Price**: Price per unit
- **Subtotal**: Quantity × Unit Price
- **Specifications**:
  - Glass Shape
  - Dimensions
  - Glass Type
  - Glass Thickness
  - Edge Work
  - Frame Type
  - Engraving
- **Design File**: Link/thumbnail to uploaded design file (if applicable)

### 5. Pricing & Payment Section
- **Subtotal**: Sum of all item subtotals
- **Tax**: Applicable taxes
- **Total Quotation**: Final total amount (subtotal + tax)
- **Payment Status**: Current payment status
- **Payment Method**: E-Wallet (Gcash) or Cash on Delivery
- **Payment Date**: Date of payment (if paid)
- **Payment Receipt**: Receipt image/file (if uploaded)

### 6. Delivery Information Section
- **Delivery Address**: Full delivery address
- **Preferred Installation Date**: Preferred date for installation (if applicable)
- **Scheduled Date**: Scheduled delivery/installation date

### 7. Ocular/Site Assessment Section (Site-Assessed Orders Only)
- **Ocular Status**: Completed/Pending
- **Ocular Date**: Date of site assessment
- **Ocular Completed By**: Staff member who performed assessment
- **Ocular Notes**: Notes from site assessment

### 8. Approval Actions Section
- **Approve Order Button**: Approve the order
- **Disapprove Order Button**: Reject the order
- **Admin Notes Textarea**: Optional internal notes for approval
- **Disapproval Reason Textarea**: **Required** field when disapproving

---

## Admin Approval Decision Criteria

When reviewing orders for approval, admins should consider the following:

### ✅ Approval Criteria (Approve if):
1. **Order Completeness**
   - All required order information is present
   - Customer details are valid and complete
   - Delivery address is valid

2. **Product Specifications**
   - Product specifications are clear and feasible
   - Dimensions are appropriate
   - Design files are acceptable (if required)

3. **Pricing**
   - Total amount is correct
   - Pricing aligns with product specifications
   - Payment method is specified

4. **Site Assessment** (for Site-Assessed Orders)
   - Ocular visit completed (if required)
   - Site assessment notes are acceptable
   - Installation is feasible

5. **Business Rules**
   - Order aligns with company policies
   - Customer credit/account is in good standing
   - Inventory/material availability (if applicable)

### ❌ Disapproval Criteria (Disapprove if):
1. **Invalid Information**
   - Missing critical order information
   - Invalid customer details
   - Incomplete delivery address

2. **Product Issues**
   - Product specifications are unclear or unfeasible
   - Design files are missing or unacceptable (when required)
   - Dimensions are invalid

3. **Business Constraints**
   - Customer account issues
   - Material/inventory unavailability
   - Order violates company policies
   - Site assessment reveals installation issues (for Site-Assessed Orders)

4. **Pricing Issues**
   - Pricing discrepancies
   - Payment method issues

### Required Actions
- **Approval**: Admin notes are optional but recommended for record-keeping
- **Disapproval**: Disapproval reason is **mandatory** - cannot disapprove without providing a reason

---

## Technical Implementation Details

### Controller Methods

**File**: `application/controllers/AdminCon.php`

#### `approve_order_admin()`
- **Route**: `POST /AdminCon/approve_order_admin`
- **Purpose**: Approves an order awaiting admin review
- **Parameters**:
  - `order_id` (required): Order ID (numeric or GI format)
  - `admin_notes` (optional): Admin notes for approval
- **Returns**: JSON response with success status and message
- **Status Change**: `Awaiting Admin` → `Approved`
- **Additional Actions**:
  - Creates payment record (Status: 'Pending')
  - Deletes from `awaiting_admin_orders` table (legacy)
  - Logs activity to `system_activity_log`
  - Notifies customer (if notification system is active)

#### `disapprove_order_admin()`
- **Route**: `POST /AdminCon/disapprove_order_admin`
- **Purpose**: Disapproves an order awaiting admin review
- **Parameters**:
  - `order_id` (required): Order ID (numeric or GI format)
  - `disapproval_reason` (required): Reason for disapproval
- **Returns**: JSON response with success status and message
- **Status Change**: `Awaiting Admin` → `Disapproved`
- **Additional Actions**:
  - Updates `DisapprovedBy`, `DisapprovedBy_ID`, `DisapprovalReason`, `Disapproved_Date`
  - Inserts into `disapproved_orders` table (legacy)
  - Logs activity to `system_activity_log`
  - Notifies customer (if notification system is active)

#### `get_order_details_ajax()`
- **Route**: `GET /AdminCon/get_order_details_ajax`
- **Purpose**: Retrieves order details for the order details modal
- **Parameters**:
  - `order_id` (required): Order ID (numeric or GI format)
- **Returns**: JSON response with complete order details

#### `get_orders_ajax()`
- **Route**: `GET /AdminCon/get_orders_ajax`
- **Purpose**: Retrieves list of orders with filtering and pagination
- **Parameters**:
  - `status` (optional): Filter by status (use "Awaiting Admin" for approval queue)
  - `order_type` (optional): 'direct' or 'site-assessed'
  - `page` (optional): Page number for pagination
  - `limit` (optional): Items per page
  - Additional filter parameters (date_range, client_search, etc.)
- **Returns**: JSON response with orders array, pagination info

### Model Methods

**File**: `application/models/Order_model.php`

#### `admin_approve_order($order_id, $admin_id, $admin_notes = '')`
- **Purpose**: Model method to approve order (used by controller)
- **Status Change**: `Awaiting Admin` → `Approved`
- **Database Updates**:
  - Updates `order` table: `Status`, `ApprovedBy_Admin_ID`, `Approved_Date`
  - Creates payment record via `create_payment_record()`
  - Deletes from `awaiting_admin_orders` (legacy table)
  - Inserts into `system_activity_log`

#### `admin_disapprove_order($order_id, $admin_id, $disapproval_reason)`
- **Purpose**: Model method to disapprove order (used by controller)
- **Status Change**: `Awaiting Admin` → `Disapproved`
- **Database Updates**:
  - Updates `order` table: `Status`, `DisapprovedBy`, `DisapprovedBy_ID`, `DisapprovalReason`, `Disapproved_Date`
  - Inserts into `disapproved_orders` (legacy table)
  - Inserts into `system_activity_log`

#### `get_approval_order_details($order_id)`
- **Purpose**: Retrieves complete order details for admin approval review
- **Returns**: Order object with all related data (customer, sales rep, items, etc.)

#### `get_awaiting_admin_orders()`
- **Purpose**: Retrieves list of orders with status "Awaiting Admin"
- **Returns**: Array of order objects

### View Files

**File**: `application/views/admin_page/admin_orders.php`
- Main orders page with table and filters
- Order Details Modal/Popup
- Approval/Disapproval action buttons

**File**: `assets/js/admin-js/order-management.js`
- JavaScript for order list management
- Order details modal handling
- Approval/disapproval AJAX calls
- Filter and pagination logic

---

## Database Changes

### Order Status Flow (Current)

```
Pending Review → Awaiting Admin → Approved → In Fabrication → Ready for Installation → Completed
                                    │
                                    └─→ Disapproved → Cancelled
```

### Key Database Tables

#### `order` Table
- **Status Field**: Main status tracking
  - `Awaiting Admin`: Orders pending admin approval
  - `Approved`: Orders approved by admin
  - `Disapproved`: Orders rejected by admin
- **Approval Fields**:
  - `ApprovedBy_Admin_ID`: ID of admin who approved
  - `Approved_Date`: Date/time of approval
  - `DisapprovedBy`: 'Admin' (since Sales Rep approval is archived)
  - `DisapprovedBy_ID`: ID of admin who disapproved
  - `DisapprovalReason`: Reason for disapproval
  - `Disapproved_Date`: Date/time of disapproval

#### `awaiting_admin_orders` Table (Legacy)
- Legacy table for backward compatibility
- Contains orders awaiting admin review
- Entries are deleted when order is approved/disapproved
- **Note**: This table may be deprecated in future versions

#### `system_activity_log` Table
- Logs all approval/disapproval actions
- Records:
  - Action type ('Order Approved by Admin', 'Order Disapproved by Admin')
  - Description with notes/reason
  - Admin ID
  - Order ID
  - Timestamp

### Database Queries (Reference)

**Get Orders Awaiting Approval:**
```sql
SELECT * FROM `order` 
WHERE Status = 'Awaiting Admin'
ORDER BY OrderDate DESC;
```

**Approve Order:**
```sql
UPDATE `order` 
SET Status = 'Approved',
    ApprovedBy_Admin_ID = ?,
    Approved_Date = NOW()
WHERE OrderID = ? AND Status = 'Awaiting Admin';
```

**Disapprove Order:**
```sql
UPDATE `order` 
SET Status = 'Disapproved',
    DisapprovedBy = 'Admin',
    DisapprovedBy_ID = ?,
    DisapprovalReason = ?,
    Disapproved_Date = NOW()
WHERE OrderID = ? AND Status = 'Awaiting Admin';
```

---

## API Endpoints

### Order Approval Endpoints

#### Approve Order
```
POST /AdminCon/approve_order_admin
Content-Type: application/x-www-form-urlencoded

Parameters:
- order_id (required): Order ID (e.g., "GI001" or "1")
- admin_notes (optional): Admin notes

Response:
{
  "success": true,
  "message": "Order approved successfully.",
  "order_id": "GI001"
}
```

#### Disapprove Order
```
POST /AdminCon/disapprove_order_admin
Content-Type: application/x-www-form-urlencoded

Parameters:
- order_id (required): Order ID (e.g., "GI001" or "1")
- disapproval_reason (required): Reason for disapproval

Response:
{
  "success": true,
  "message": "Order disapproved. Customer will be notified.",
  "order_id": "GI001"
}
```

#### Get Order Details
```
GET /AdminCon/get_order_details_ajax?order_id=GI001
Content-Type: application/json

Response:
{
  "success": true,
  "order": {
    "OrderID": 1,
    "OrderNumber": "GI001",
    "Status": "Awaiting Admin",
    "Customer_First_Name": "John",
    "Customer_Last_Name": "Doe",
    "ProductName": "Custom Glass Panel",
    "TotalAmount": 5000.00,
    // ... additional order fields
  }
}
```

#### Get Orders List
```
GET /AdminCon/get_orders_ajax?status=Awaiting Admin&page=1&limit=10
Content-Type: application/json

Response:
{
  "orders": [
    {
      "order_id": "GI001",
      "customer_name": "John Doe",
      "product_name": "Custom Glass Panel",
      "total_amount": "5000.00",
      "status": "Awaiting Admin",
      // ... additional fields
    }
  ],
  "total": 25,
  "page": 1,
  "limit": 10,
  "total_pages": 3
}
```

---

## Summary

### Key Points
1. ✅ **Sales Representative approval pages are archived** - All approvals are handled by Admin
2. ✅ **Simplified workflow** - Orders go directly from "Awaiting Admin" to "Approved" or "Disapproved"
3. ✅ **Admin has full control** - Admins make all approval decisions
4. ✅ **Complete information display** - Admins see all necessary information for approval decisions
5. ✅ **Mandatory disapproval reason** - Disapproval requires a reason to be provided
6. ✅ **Automatic payment record creation** - Approved orders automatically get payment records
7. ✅ **Customer notifications** - Customers are notified of approval/disapproval decisions

### Approval Checklist for Admins
- [ ] Review order information completeness
- [ ] Verify customer details
- [ ] Check product specifications
- [ ] Validate pricing and payment method
- [ ] Review delivery address
- [ ] Check design files (if applicable)
- [ ] Review sales rep notes (if any)
- [ ] Verify site assessment (for Site-Assessed Orders)
- [ ] Make approval decision
- [ ] Add admin notes (recommended for approval, required for disapproval)
- [ ] Confirm action

---

**Document Version**: 1.0  
**Last Updated**: January 2026  
**Status**: Active (Sales Rep Approval Archived)
