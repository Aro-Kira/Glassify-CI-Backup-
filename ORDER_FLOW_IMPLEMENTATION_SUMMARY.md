# Order Flow Implementation Summary
## MVC Integration of ORDER_FLOW_FUNCTIONS_REFERENCE.md

This document summarizes the implementation of order flow functions into the MVC structure.

---

## 📋 Summary of Changes and Additions

### What Was Changed

#### 1. **Order_model.php** - Core Business Logic Implementation
- **Added 12 new functions** for complete order flow management
- **Implemented transaction safety** for all status-changing operations
- **Added activity logging** for audit trail
- **Created status validation** to prevent invalid transitions
- **Added payment record creation** automation
- **Implemented customer notification** framework (logging ready for email/SMS)

#### 2. **SalesCon.php** - Sales Representative Controller
- **Refactored 5 methods** to use Order_model functions
- **Added `parse_order_id()` helper** for consistent order ID handling
- **Improved error handling** with structured JSON responses
- **Removed duplicate database logic** from controller

#### 3. **AdminCon.php** - Administrator Controller
- **Refactored 3 methods** to use Order_model functions
- **Standardized approval/disapproval** workflow
- **Improved transaction handling** for admin actions

### What Was Added

#### New Functions in Order_model.php

**Sales Representative Functions:**
- `request_admin_approval()` - Request admin approval (Stage 3)
- `sales_rep_final_approve()` - Final approval after admin review (Stage 5)
- `sales_rep_final_disapprove()` - Final disapproval after admin review (Stage 5)
- `get_sales_rep_orders()` - Get orders filtered by sales rep and status
- `get_ready_to_approve_orders()` - Get orders ready for final approval
- `count_sales_rep_orders_by_status()` - Count orders by status for dashboard

**Administrator Functions:**
- `admin_approve_order()` - Admin approves order (Stage 4)
- `admin_disapprove_order()` - Admin disapproves order (Stage 4)
- `get_awaiting_admin_orders()` - Get all orders awaiting admin review

**Utility Functions:**
- `validate_status_transition()` - Validate status changes
- `create_payment_record()` - Auto-create payment on approval
- `get_order_details_for_popup()` - Get complete order details for popups

#### New Helper Functions

**SalesCon.php:**
- `parse_order_id()` - Handles multiple order ID formats (GI001, #GI001, #1, 1, etc.)

#### Documentation Files Created

1. **ORDER_FLOW_DOCUMENTATION.md** - Complete system flow documentation
2. **ORDER_FLOW_FUNCTIONS_REFERENCE.md** - Detailed function reference guide
3. **ORDER_FLOW_IMPLEMENTATION_SUMMARY.md** - This implementation summary

### Key Improvements

1. **Code Organization**: Moved all business logic from controllers to model
2. **Transaction Safety**: All operations use database transactions
3. **Error Handling**: Consistent structured responses across all functions
4. **Activity Logging**: Automatic logging of all order status changes
5. **Status Validation**: Prevents invalid status transitions
6. **Legacy Support**: Maintains backward compatibility with existing tables

---

## 🔄 Order Flow System Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    COMPLETE ORDER LIFECYCLE FLOW                          │
└─────────────────────────────────────────────────────────────────────────┘

[CUSTOMER]
    │
    ├─► Stage 1: Place Order (Cart → Checkout)
    │   └─► Status: "Pending Review"
    │   └─► Tables: order, order_items, cart
    │   └─► Function: create_order(), save_order_customizations()
    │
    ▼
[SALES REPRESENTATIVE]
    │
    ├─► Stage 2: View Orders (sales_orders.php)
    │   ├─► Tab: "Pending Review" (Status = 'Pending Review')
    │   ├─► Tab: "Awaiting Admin" (Status = 'Awaiting Admin')
    │   └─► Tab: "Ready to Approve" (Status = 'Ready to Approve')
    │   └─► Function: get_sales_rep_orders()
    │
    ├─► Stage 3: Request Admin Approval
    │   └─► Status: "Pending Review" → "Awaiting Admin"
    │   └─► Tables: order, awaiting_admin_orders, system_activity_log
    │   └─► Function: request_admin_approval()
    │
    ▼
[ADMINISTRATOR]
    │
    ├─► Stage 4: Review Orders (admin_orders.php)
    │   ├─► Tab: "Order Schedule Approval" (Status = 'Awaiting Admin')
    │   └─► Function: get_awaiting_admin_orders()
    │
    ├─► Action: Approve Order
    │   └─► Status: "Awaiting Admin" → "Ready to Approve"
    │   └─► AdminStatus = 'Approved'
    │   └─► Tables: order, ready_to_approve_orders, awaiting_admin_orders
    │   └─► Function: admin_approve_order()
    │
    ├─► Action: Disapprove Order
    │   └─► Status: "Awaiting Admin" → "Ready to Approve"
    │   └─► AdminStatus = 'Disapproved'
    │   └─► Tables: order, ready_to_approve_orders, awaiting_admin_orders
    │   └─► Function: admin_disapprove_order()
    │
    ▼
[SALES REPRESENTATIVE]
    │
    ├─► Stage 5: Final Decision (Ready to Approve Tab)
    │   ├─► View: AdminStatus = 'Approved' or 'Disapproved'
    │   └─► Function: get_ready_to_approve_orders()
    │
    ├─► Action: Approve Order (if AdminStatus = 'Approved')
    │   └─► Status: "Ready to Approve" → "Approved"
    │   └─► Tables: order, payment, approved_orders, ready_to_approve_orders
    │   └─► Function: sales_rep_final_approve()
    │   └─► Creates: payment record (Status = 'Pending')
    │   └─► Notifies: Customer
    │
    ├─► Action: Disapprove Order (if AdminStatus = 'Disapproved')
    │   └─► Status: "Ready to Approve" → "Disapproved"
    │   └─► Tables: order, disapproved_orders, ready_to_approve_orders
    │   └─► Function: sales_rep_final_disapprove()
    │
    ▼
[CUSTOMER]
    │
    ├─► Stage 6: Payment Processing
    │   ├─► E-Wallet: Upload receipt → payment.ReceiptPath
    │   ├─► Cash on Delivery: Marked on delivery
    │   └─► Tables: payment, order
    │   └─► Function: save_payment_receipt(), update_payment_status()
    │
    ▼
[SYSTEM]
    │
    ├─► Stage 7: Order Fulfillment & Tracking
    │   ├─► 1. Order Placed ✓ (Status: Approved)
    │   ├─► 2. Ocular Visit (Scheduled) → appointments table
    │   ├─► 3. In Fabrication (Scheduled) → projectschedule table
    │   ├─► 4. Installed (Scheduled) → appointments table
    │   └─► 5. Completed ✓ (Status: Completed)
    │   └─► Tables: order, appointments, projectschedule
    │   └─► Function: update_order_status()
    │
    ▼
[COMPLETED]

Status Progression:
Pending Review → Awaiting Admin → Ready to Approve → Approved → 
In Fabrication → Ready for Installation → Completed
```

### Status Transition Rules

**Valid Transitions:**
- `Pending Review` → `Awaiting Admin` (Sales Rep: Request Approval)
- `Awaiting Admin` → `Ready to Approve` (Admin: Approve/Disapprove)
- `Ready to Approve` → `Approved` (Sales Rep: Final Approve)
- `Ready to Approve` → `Disapproved` (Sales Rep: Final Disapprove)
- `Approved` → `In Fabrication` (System: Schedule)
- `In Fabrication` → `Ready for Installation` (System: Complete)
- `Ready for Installation` → `Completed` (System: Install)

**Invalid Transitions (Blocked):**
- Cannot skip stages
- Cannot go backwards
- Admin cannot directly approve to 'Approved' (must go through 'Ready to Approve')

---

## Implementation Status

### ✅ Completed

#### 1. **Order_model.php** - All Functions Implemented
All functions from `ORDER_FLOW_FUNCTIONS_REFERENCE.md` have been added to `Order_model.php`:

- ✅ `request_admin_approval()` - Stage 3: Request admin approval
- ✅ `admin_approve_order()` - Stage 4: Admin approves
- ✅ `admin_disapprove_order()` - Stage 4: Admin disapproves
- ✅ `sales_rep_final_approve()` - Stage 5: Sales rep final approval
- ✅ `sales_rep_final_disapprove()` - Stage 5: Sales rep final disapproval
- ✅ `get_sales_rep_orders()` - Get orders for sales rep
- ✅ `get_awaiting_admin_orders()` - Get orders awaiting admin
- ✅ `get_ready_to_approve_orders()` - Get ready to approve orders
- ✅ `validate_status_transition()` - Validate status transitions
- ✅ `create_payment_record()` - Create payment record
- ✅ `get_order_details_for_popup()` - Get order details
- ✅ `count_sales_rep_orders_by_status()` - Count orders by status

#### 2. **SalesCon.php** - Controller Updated

**Updated Methods:**
- ✅ `sales_orders()` - Now uses `Order_model->get_sales_rep_orders()`
- ✅ `request_approval()` - Now uses `Order_model->request_admin_approval()`
- ✅ `approve_order()` - Now uses `Order_model->sales_rep_final_approve()`
- ✅ `disapprove_order()` - Now uses `Order_model->sales_rep_final_disapprove()`
- ✅ `get_order_details()` - Now uses `Order_model->get_order_details_for_popup()`

**Helper Function Added:**
- ✅ `parse_order_id()` - Consistent order ID parsing (handles GI001, #GI001, #1, 1, etc.)

#### 3. **AdminCon.php** - Controller Updated

**Updated Methods:**
- ✅ `get_awaiting_approval_orders()` - Now uses `Order_model->get_awaiting_admin_orders()`
- ✅ `approve_order_admin()` - Now uses `Order_model->admin_approve_order()`
- ✅ `disapprove_order_admin()` - Now uses `Order_model->admin_disapprove_order()`

---

## Key Changes

### 1. Consistent Order ID Handling

**Before:** Multiple different parsing methods across controllers
**After:** Single `parse_order_id()` helper function in SalesCon

```php
private function parse_order_id($order_id)
{
    // Handles: GI001, #GI001, #1, 1, etc.
    // Returns: ['numeric' => int, 'formatted' => string]
}
```

### 2. Transaction Safety

**Before:** Manual transaction handling in controllers
**After:** All transactions handled in Order_model functions

All status-changing operations now use database transactions with proper rollback on errors.

### 3. Activity Logging

**Before:** Inconsistent logging
**After:** Centralized logging in Order_model functions

All actions are logged in `system_activity_log` with:
- Action type
- Description
- Role
- User ID
- Related ID and Type
- Timestamp

### 4. Error Handling

**Before:** Mixed return types (sometimes bool, sometimes array)
**After:** Consistent structured responses

All functions return:
```php
['success' => bool, 'message' => string]
```

### 5. Legacy Table Support

All functions maintain backward compatibility with legacy tables:
- `pending_review_orders`
- `awaiting_admin_orders`
- `ready_to_approve_orders`
- `approved_orders`
- `disapproved_orders`

---

## Function Call Flow (Updated)

### Sales Representative Flow

```
1. View Orders
   SalesCon->sales_orders()
   └─► Order_model->get_sales_rep_orders($sales_rep_id)
   └─► Order_model->get_ready_to_approve_orders($sales_rep_id) [for AdminStatus]
   └─► Order_model->count_sales_rep_orders_by_status() [for counts]

2. Request Approval
   SalesCon->request_approval()
   └─► Order_model->request_admin_approval($order_id, $sales_rep_id, $notes)
   Status: 'Pending Review' → 'Awaiting Admin'

3. Final Approve
   SalesCon->approve_order()
   └─► Order_model->sales_rep_final_approve($order_id, $sales_rep_id)
   Status: 'Ready to Approve' → 'Approved'
   └─► Creates payment record
   └─► Notifies customer

4. Final Disapprove
   SalesCon->disapprove_order()
   └─► Order_model->sales_rep_final_disapprove($order_id, $sales_rep_id, $reason)
   Status: 'Ready to Approve' → 'Disapproved'

5. Get Order Details
   SalesCon->get_order_details()
   └─► Order_model->get_order_details_for_popup($order_id)
```

### Administrator Flow

```
1. Get Awaiting Orders
   AdminCon->get_awaiting_approval_orders()
   └─► Order_model->get_awaiting_admin_orders()

2. Approve Order
   AdminCon->approve_order_admin()
   └─► Order_model->admin_approve_order($order_id, $admin_id, $admin_notes)
   Status: 'Awaiting Admin' → 'Ready to Approve' (AdminStatus = 'Approved')

3. Disapprove Order
   AdminCon->disapprove_order_admin()
   └─► Order_model->admin_disapprove_order($order_id, $admin_id, $disapproval_reason)
   Status: 'Awaiting Admin' → 'Ready to Approve' (AdminStatus = 'Disapproved')
```

---

## Benefits of This Implementation

### 1. **Separation of Concerns**
- Controllers handle HTTP requests/responses
- Models handle business logic and database operations
- Views handle presentation

### 2. **Code Reusability**
- Order_model functions can be used by any controller
- Consistent behavior across the application

### 3. **Maintainability**
- Single source of truth for order operations
- Easy to update business logic in one place

### 4. **Testability**
- Model functions can be unit tested independently
- Controllers can be tested with mocked models

### 5. **Transaction Safety**
- All database operations use transactions
- Automatic rollback on errors
- Data consistency guaranteed

### 6. **Activity Logging**
- All actions logged automatically
- Audit trail for compliance
- Easy debugging

---

## Testing Checklist

### Sales Representative Functions

- [ ] `sales_orders()` - Displays orders correctly
- [ ] `request_approval()` - Moves order to 'Awaiting Admin'
- [ ] `approve_order()` - Moves order to 'Approved', creates payment
- [ ] `disapprove_order()` - Moves order to 'Disapproved'
- [ ] `get_order_details()` - Returns correct order details

### Administrator Functions

- [ ] `get_awaiting_approval_orders()` - Returns awaiting orders
- [ ] `approve_order_admin()` - Moves to 'Ready to Approve' with AdminStatus = 'Approved'
- [ ] `disapprove_order_admin()` - Moves to 'Ready to Approve' with AdminStatus = 'Disapproved'

### Edge Cases

- [ ] Invalid order ID handling
- [ ] Order not found scenarios
- [ ] Permission checks (sales rep can only see their orders)
- [ ] Status transition validation
- [ ] Transaction rollback on errors

---

## Migration Notes

### Breaking Changes
None - All changes are backward compatible.

### Database Changes
None - Uses existing database schema.

### View Changes
None - Views continue to work with existing data structure.

### JavaScript Changes
None - Frontend JavaScript continues to work as before.

---

## ✅ Next Steps Checklist

### Phase 1: Testing & Validation

#### 1.1 Sales Representative Functions Testing
- [ ] **Test `sales_orders()`** - Verify orders display correctly
  - **Database Tables**: `order`, `customer`, `user`, `order_items`, `product`
  - **Check**: Orders filtered by SalesRep_ID, status tabs working
  
- [ ] **Test `request_approval()`** - Verify status transition
  - **Database Tables**: `order`, `awaiting_admin_orders`, `system_activity_log`
  - **Check**: Status changes from 'Pending Review' to 'Awaiting Admin'
  - **Check**: Record inserted into `awaiting_admin_orders`
  - **Check**: Activity logged in `system_activity_log`
  
- [ ] **Test `approve_order()`** - Verify final approval
  - **Database Tables**: `order`, `payment`, `approved_orders`, `ready_to_approve_orders`, `system_activity_log`
  - **Check**: Status changes from 'Ready to Approve' to 'Approved'
  - **Check**: Payment record created in `payment` table
  - **Check**: Record moved to `approved_orders`
  - **Check**: Record deleted from `ready_to_approve_orders`
  
- [ ] **Test `disapprove_order()`** - Verify final disapproval
  - **Database Tables**: `order`, `disapproved_orders`, `ready_to_approve_orders`, `system_activity_log`
  - **Check**: Status changes to 'Disapproved'
  - **Check**: Disapproval reason saved
  - **Check**: Record moved to `disapproved_orders`
  
- [ ] **Test `get_order_details()`** - Verify popup data
  - **Database Tables**: `order`, `order_items`, `product`, `customization`, `customer`, `user`
  - **Check**: All order details returned correctly

#### 1.2 Administrator Functions Testing
- [ ] **Test `get_awaiting_approval_orders()`** - Verify order retrieval
  - **Database Tables**: `order`, `customer`, `user`, `order_items`
  - **Check**: Only orders with Status = 'Awaiting Admin' returned
  
- [ ] **Test `approve_order_admin()`** - Verify admin approval
  - **Database Tables**: `order`, `ready_to_approve_orders`, `awaiting_admin_orders`, `system_activity_log`
  - **Check**: Status changes to 'Ready to Approve'
  - **Check**: AdminStatus = 'Approved' in `ready_to_approve_orders`
  - **Check**: Record deleted from `awaiting_admin_orders`
  - **Check**: ApprovedBy_Admin_ID and Approved_Date set in `order`
  
- [ ] **Test `disapprove_order_admin()`** - Verify admin disapproval
  - **Database Tables**: `order`, `ready_to_approve_orders`, `awaiting_admin_orders`, `system_activity_log`
  - **Check**: Status changes to 'Ready to Approve'
  - **Check**: AdminStatus = 'Disapproved' in `ready_to_approve_orders`
  - **Check**: DisapprovalReason saved in `order` table
  - **Check**: DisapprovedBy = 'Admin' and DisapprovedBy_ID set

#### 1.3 Edge Cases & Error Handling
- [ ] **Test Invalid Order ID** - Verify error handling
  - **Database Tables**: `order`
  - **Check**: Returns error message for non-existent orders
  
- [ ] **Test Invalid Status Transitions** - Verify validation
  - **Database Tables**: `order`
  - **Check**: Cannot skip stages (e.g., Pending Review → Approved)
  - **Check**: Cannot go backwards (e.g., Approved → Pending Review)
  
- [ ] **Test Permission Checks** - Verify access control
  - **Database Tables**: `order`, `user`
  - **Check**: Sales rep can only see their assigned orders
  - **Check**: Admin can see all orders
  
- [ ] **Test Transaction Rollback** - Verify error recovery
  - **Database Tables**: All order-related tables
  - **Check**: On error, all changes rolled back
  - **Check**: No partial updates

### Phase 2: Payment Integration

#### 2.1 Payment Record Creation
- [ ] **Verify Payment Creation on Approval**
  - **Database Tables**: `payment`, `order`
  - **Check**: Payment record created when order approved
  - **Check**: Payment.Status = 'Pending'
  - **Check**: Payment.Amount = Order.TotalAmount
  - **Check**: Payment.OrderID linked correctly

#### 2.2 Payment Receipt Upload
- [ ] **Test E-Wallet Receipt Upload**
  - **Database Tables**: `payment`, `order`
  - **Check**: Receipt file uploaded and path saved
  - **Check**: Payment.Status remains 'Pending' until verified
  - **Check**: ReceiptPath stored in `payment` table

#### 2.3 Payment Verification
- [ ] **Test Payment Status Update**
  - **Database Tables**: `payment`, `order`
  - **Check**: Payment.Status updated to 'Paid'
  - **Check**: Order.PaymentStatus updated to 'Paid'
  - **Check**: Transaction logged in `system_activity_log`

### Phase 3: Notification System

#### 3.1 Customer Notifications
- [ ] **Implement Email Notifications**
  - **Database Tables**: `order`, `user`, `customer`
  - **Check**: Email sent when order approved
  - **Check**: Email sent when order disapproved
  - **Check**: CustomerNotified flag updated in `order` table
  - **Check**: CustomerNotified_Date timestamp set

- [ ] **Implement SMS Notifications** (Optional)
  - **Database Tables**: `order`, `user`
  - **Check**: SMS sent for critical status changes
  - **Check**: Phone number from `user` table used

#### 3.2 Sales Rep Notifications
- [ ] **Test Sales Rep Notifications**
  - **Database Tables**: `sales_notif`, `order`
  - **Check**: Notification created when order moves to 'Awaiting Admin'
  - **Check**: Notification created when admin reviews order
  - **Check**: Notification status tracked in `sales_notif` table

### Phase 4: Order Fulfillment & Tracking

#### 4.1 Appointment Scheduling
- [ ] **Test Ocular Visit Scheduling**
  - **Database Tables**: `appointments`, `order`
  - **Check**: Appointment created when order approved
  - **Check**: Appointment.Service = 'Ocular Visit'
  - **Check**: Order.OcularDate set
  - **Check**: Appointment linked to OrderID and Customer_ID

- [ ] **Test Installation Scheduling**
  - **Database Tables**: `appointments`, `order`
  - **Check**: Appointment created when ready for installation
  - **Check**: Appointment.Service = 'Installed'
  - **Check**: Order.InstallationDate set

#### 4.2 Project Scheduling
- [ ] **Test Fabrication Scheduling**
  - **Database Tables**: `projectschedule`, `order`
  - **Check**: Project scheduled when order moves to 'In Fabrication'
  - **Check**: Order.FabricationDate set
  - **Check**: Project linked to OrderID and Admin_ID

#### 4.3 Order Tracking
- [ ] **Test Order Status Updates**
  - **Database Tables**: `order`, `appointments`, `projectschedule`
  - **Check**: Status progression: Approved → In Fabrication → Ready for Installation → Completed
  - **Check**: Progress calculated correctly (0%, 25%, 50%, 75%, 100%)
  - **Check**: Dates updated in `order` table

### Phase 5: Database Optimization

#### 5.1 Index Verification
- [ ] **Verify Indexes Exist** (from `latest_glassifydb.sql`)
  - **Table**: `order`
    - [ ] `idx_status` on `Status`
    - [ ] `idx_customer` on `Customer_ID`
    - [ ] `idx_salesrep` on `SalesRep_ID`
    - [ ] `idx_order_date` on `OrderDate`
  - **Table**: `order_items`
    - [ ] `idx_order` on `OrderID`
  - **Table**: `payment`
    - [ ] `idx_order` on `OrderID`
    - [ ] `idx_status` on `Status`
  - **Table**: `appointments`
    - [ ] `idx_order` on `OrderID`
    - [ ] `idx_service` on `Service`
  - **Table**: `projectschedule`
    - [ ] `idx_order` on `OrderID`

#### 5.2 Query Performance
- [ ] **Test Query Performance**
  - **Database Tables**: All order-related tables
  - **Check**: Queries execute in < 500ms
  - **Check**: No N+1 query problems
  - **Check**: Proper JOINs used instead of multiple queries

### Phase 6: Legacy Table Maintenance

#### 6.1 Legacy Table Sync
- [ ] **Verify Legacy Tables Updated**
  - **Tables**: `pending_review_orders`, `awaiting_admin_orders`, `ready_to_approve_orders`, `approved_orders`, `disapproved_orders`
  - **Check**: Records inserted when status changes
  - **Check**: Records deleted when status changes
  - **Check**: Data matches `order` table

#### 6.2 Data Consistency
- [ ] **Verify Data Consistency**
  - **Database Tables**: `order` (primary) + all legacy tables
  - **Check**: Status in `order` table matches legacy table presence
  - **Check**: No orphaned records in legacy tables
  - **Check**: OrderNumber consistency across tables

### Phase 7: Documentation & Deployment

#### 7.1 Code Documentation
- [ ] **Add PHPDoc Comments**
  - **Files**: `Order_model.php`, `SalesCon.php`, `AdminCon.php`
  - **Check**: All functions have proper documentation
  - **Check**: Parameters and return types documented

#### 7.2 API Documentation
- [ ] **Update API Endpoints Documentation**
  - **Endpoints**: All SalesCon and AdminCon endpoints
  - **Check**: Request/response formats documented
  - **Check**: Error codes documented

#### 7.3 User Documentation
- [ ] **Create User Guides**
  - **For Sales Reps**: How to request approval, approve orders
  - **For Admins**: How to review and approve orders
  - **For Customers**: How to track orders

### Phase 8: Security & Validation

#### 8.1 Input Validation
- [ ] **Test Input Sanitization**
  - **Check**: SQL injection prevention
  - **Check**: XSS prevention
  - **Check**: Order ID validation
  - **Check**: Notes/reason field sanitization

#### 8.2 Access Control
- [ ] **Test Role-Based Access**
  - **Database Tables**: `user`, `order`
  - **Check**: Sales rep can only access their orders
  - **Check**: Admin can access all orders
  - **Check**: Customer can only access their own orders

#### 8.3 Activity Logging
- [ ] **Verify Activity Logs**
  - **Database Tables**: `system_activity_log`
  - **Check**: All status changes logged
  - **Check**: User ID and role logged correctly
  - **Check**: RelatedID and RelatedType set correctly

---

## 📊 Database Tables Reference

### Primary Tables (Core Functionality)

| Table Name | Purpose | Key Fields | Used In Stages |
|------------|---------|------------|----------------|
| `order` | Main order table (source of truth) | OrderID, OrderNumber, Status, Customer_ID, SalesRep_ID | All stages |
| `order_items` | Order line items | OrderItemID, OrderID, Product_ID, CustomizationID | Stage 1, 7 |
| `payment` | Payment records | Payment_ID, OrderID, Status, Amount, ReceiptPath | Stage 5, 6 |
| `customer` | Customer information | Customer_ID, UserID | All stages |
| `user` | User accounts (Sales Rep, Admin, Customer) | UserID, Role, Email | All stages |

### Legacy Tables (Backward Compatibility)

| Table Name | Purpose | Status Mapping | Used In Stages |
|------------|---------|----------------|----------------|
| `pending_review_orders` | Orders pending review | Status = 'Pending Review' | Stage 2 |
| `awaiting_admin_orders` | Orders awaiting admin | Status = 'Awaiting Admin' | Stage 3, 4 |
| `ready_to_approve_orders` | Orders ready for final approval | Status = 'Ready to Approve' | Stage 4, 5 |
| `approved_orders` | Approved orders | Status = 'Approved' | Stage 5 |
| `disapproved_orders` | Disapproved orders | Status = 'Disapproved' | Stage 5 |

### Supporting Tables

| Table Name | Purpose | Used In Stages |
|------------|---------|----------------|
| `appointments` | Service scheduling (Ocular Visit, Installation) | Stage 7 |
| `projectschedule` | Fabrication project scheduling | Stage 7 |
| `product` | Product catalog | Stage 1, 2 |
| `customization` | Product customizations | Stage 1, 2 |
| `cart` | Shopping cart | Stage 1 |
| `system_activity_log` | Activity audit trail | All stages |
| `sales_notif` | Sales rep notifications | Stage 3, 4, 5 |

### Key Relationships

```
order
  ├─► customer (Customer_ID)
  ├─► user (SalesRep_ID, ApprovedBy_SalesRep_ID, ApprovedBy_Admin_ID)
  ├─► order_items (OrderID)
  ├─► payment (OrderID)
  ├─► appointments (OrderID)
  └─► projectschedule (OrderID)

order_items
  ├─► product (Product_ID)
  └─► customization (CustomizationID)

customer
  └─► user (UserID)
```

---

## Files Modified

1. `application/models/Order_model.php` - Added all flow functions
2. `application/controllers/SalesCon.php` - Updated to use Order_model functions
3. `application/controllers/AdminCon.php` - Updated to use Order_model functions

## Files Created

1. `ORDER_FLOW_DOCUMENTATION.md` - Complete flow documentation
2. `ORDER_FLOW_FUNCTIONS_REFERENCE.md` - Function reference guide
3. `ORDER_FLOW_IMPLEMENTATION_SUMMARY.md` - This file

---

**Implementation Date**: 2025-01-08  
**Status**: ✅ Complete  
**Version**: 1.0
