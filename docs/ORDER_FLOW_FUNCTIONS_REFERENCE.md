# Order Flow Functions Reference
## Complete Function List Based on ORDER_FLOW_DOCUMENTATION.md

This document provides a comprehensive reference for all order flow functions implemented in `Order_model.php`.

---

## Function Categories

### 1. Order Creation Functions

#### `create_order($order_data)`
- **Purpose**: Create a new order (Stage 1)
- **Status**: `Pending Review`
- **Parameters**: 
  - `$order_data` (array): Order data including Customer_ID, SalesRep_ID, TotalAmount, etc.
- **Returns**: `int|false` - OrderID on success, false on failure
- **Transaction**: Yes
- **Usage**: Called from `ShopCon->place_order()`, `ShopCon->submit_ewallet_payment()`

#### `save_order_customizations($order_id, $cart_items)`
- **Purpose**: Save order items from cart
- **Parameters**:
  - `$order_id` (int): Order ID
  - `$cart_items` (array): Cart items to convert to order items
- **Returns**: `bool` - Success status
- **Transaction**: Yes

---

### 2. Sales Representative Functions

#### `request_admin_approval($order_id, $sales_rep_id, $notes = '')`
- **Purpose**: Request admin approval (Stage 3)
- **Status Transition**: `Pending Review` → `Awaiting Admin`
- **Parameters**:
  - `$order_id` (int): Order ID
  - `$sales_rep_id` (int): Sales Representative ID
  - `$notes` (string, optional): Notes from sales rep
- **Returns**: `array` - `['success' => bool, 'message' => string]`
- **Transaction**: Yes
- **Updates**:
  - `order.Status` = 'Awaiting Admin'
  - Inserts into `awaiting_admin_orders` (legacy)
  - Logs activity in `system_activity_log`
- **Usage**: Called from `SalesCon->request_approval()`

#### `sales_rep_final_approve($order_id, $sales_rep_id)`
- **Purpose**: Final approval after admin review (Stage 5)
- **Status Transition**: `Ready to Approve` → `Approved`
- **Parameters**:
  - `$order_id` (int): Order ID
  - `$sales_rep_id` (int): Sales Representative ID
- **Returns**: `array` - `['success' => bool, 'message' => string]`
- **Transaction**: Yes
- **Updates**:
  - `order.Status` = 'Approved'
  - `order.ApprovedBy_SalesRep_ID` = Sales Rep ID
  - `order.Approved_Date` = Current timestamp
  - Creates payment record in `payment` table
  - Inserts into `approved_orders` (legacy)
  - Deletes from `ready_to_approve_orders`
  - Notifies customer
- **Usage**: Called from `SalesCon->approve_order()`

#### `sales_rep_final_disapprove($order_id, $sales_rep_id, $reason = '')`
- **Purpose**: Final disapproval after admin review (Stage 5)
- **Status Transition**: `Ready to Approve` → `Disapproved`
- **Parameters**:
  - `$order_id` (int): Order ID
  - `$sales_rep_id` (int): Sales Representative ID
  - `$reason` (string, optional): Disapproval reason
- **Returns**: `array` - `['success' => bool, 'message' => string]`
- **Transaction**: Yes
- **Updates**:
  - `order.Status` = 'Disapproved'
  - `order.DisapprovedBy` = 'Sales Rep'
  - `order.DisapprovedBy_ID` = Sales Rep ID
  - `order.DisapprovalReason` = Combined admin + sales rep reason
  - Inserts into `disapproved_orders` (legacy)
  - Deletes from `ready_to_approve_orders`
- **Usage**: Called from `SalesCon->disapprove_order()`

#### `get_sales_rep_orders($sales_rep_id, $status = null)`
- **Purpose**: Get orders for a sales rep, optionally filtered by status
- **Parameters**:
  - `$sales_rep_id` (int): Sales Representative ID
  - `$status` (string, optional): Order status filter
- **Returns**: `array` - Array of order objects
- **Usage**: Called from `SalesCon->sales_orders()`

#### `count_sales_rep_orders_by_status($sales_rep_id, $status)`
- **Purpose**: Count orders by status for a sales rep
- **Parameters**:
  - `$sales_rep_id` (int): Sales Representative ID
  - `$status` (string): Order status
- **Returns**: `int` - Count
- **Usage**: Dashboard statistics

---

### 3. Administrator Functions

#### `admin_approve_order($order_id, $admin_id, $admin_notes = '')`
- **Purpose**: Admin approves order (Stage 4)
- **Status Transition**: `Awaiting Admin` → `Ready to Approve` (with AdminStatus = 'Approved')
- **Parameters**:
  - `$order_id` (int): Order ID
  - `$admin_id` (int): Admin ID
  - `$admin_notes` (string, optional): Admin notes
- **Returns**: `array` - `['success' => bool, 'message' => string]`
- **Transaction**: Yes
- **Updates**:
  - `order.Status` = 'Ready to Approve'
  - `order.ApprovedBy_Admin_ID` = Admin ID
  - `order.Approved_Date` = Current timestamp
  - Inserts into `ready_to_approve_orders` with AdminStatus = 'Approved'
  - Deletes from `awaiting_admin_orders`
  - Logs activity
- **Usage**: Called from `AdminCon->approve_order_admin()`

#### `admin_disapprove_order($order_id, $admin_id, $disapproval_reason)`
- **Purpose**: Admin disapproves order (Stage 4)
- **Status Transition**: `Awaiting Admin` → `Ready to Approve` (with AdminStatus = 'Disapproved')
- **Parameters**:
  - `$order_id` (int): Order ID
  - `$admin_id` (int): Admin ID
  - `$disapproval_reason` (string, required): Reason for disapproval
- **Returns**: `array` - `['success' => bool, 'message' => string]`
- **Transaction**: Yes
- **Updates**:
  - `order.Status` = 'Ready to Approve'
  - `order.DisapprovedBy` = 'Admin'
  - `order.DisapprovedBy_ID` = Admin ID
  - `order.DisapprovalReason` = Disapproval reason
  - `order.Disapproved_Date` = Current timestamp
  - Inserts into `ready_to_approve_orders` with AdminStatus = 'Disapproved'
  - Deletes from `awaiting_admin_orders`
  - Logs activity
- **Usage**: Called from `AdminCon->disapprove_order_admin()`

#### `get_awaiting_admin_orders()`
- **Purpose**: Get all orders awaiting admin approval
- **Returns**: `array` - Array of order objects with customer and sales rep details
- **Usage**: Called from `AdminCon->get_awaiting_approval_orders()`

---

### 4. Order Status Management Functions

#### `update_order_status($order_id, $status, $approved_by = null, $approved_by_id = null)`
- **Purpose**: Update order status with transaction handling
- **Parameters**:
  - `$order_id` (int): Order ID
  - `$status` (string): New status
  - `$approved_by` (string, optional): 'Sales Rep' or 'Admin'
  - `$approved_by_id` (int, optional): User ID of approver
- **Returns**: `bool` - Success status
- **Transaction**: Yes
- **Logs**: Activity in `system_activity_log`
- **Usage**: General purpose status update function

#### `validate_status_transition($current_status, $new_status, $role = 'System')`
- **Purpose**: Validate if a status transition is allowed
- **Parameters**:
  - `$current_status` (string): Current order status
  - `$new_status` (string): Desired new status
  - `$role` (string, optional): User role performing transition
- **Returns**: `array` - `['valid' => bool, 'message' => string]`
- **Valid Transitions**:
  - `Pending Review` → `Awaiting Admin`, `Disapproved`
  - `Awaiting Admin` → `Ready to Approve`, `Disapproved`
  - `Ready to Approve` → `Approved`, `Disapproved`
  - `Approved` → `In Fabrication`, `Cancelled`
  - `In Fabrication` → `Ready for Installation`, `Cancelled`
  - `Ready for Installation` → `Completed`, `Cancelled`
- **Usage**: Called before status updates to ensure valid transitions

---

### 5. Payment Functions

#### `create_payment_record($order_id)`
- **Purpose**: Create payment record for approved order
- **Parameters**:
  - `$order_id` (int): Order ID
- **Returns**: `bool` - Success status
- **Creates**: Payment record in `payment` table with Status = 'Pending'
- **Usage**: Called automatically when order is approved

#### `update_payment_status($order_id, $status)`
- **Purpose**: Update payment status
- **Parameters**:
  - `$order_id` (int): Order ID
  - `$status` (string): New payment status ('Pending', 'Paid', 'Failed', 'Refunded')
- **Returns**: `bool` - Success status
- **Transaction**: Yes
- **Updates**: Both `order.PaymentStatus` and `payment.Status`
- **Usage**: Called from `SalesCon->mark_payment_paid()`

#### `save_payment_receipt($order_id, $receipt_path, $amount = 0)`
- **Purpose**: Save payment receipt for E-Wallet orders
- **Parameters**:
  - `$order_id` (int): Order ID
  - `$receipt_path` (string): Path to receipt file
  - `$amount` (float, optional): Payment amount
- **Returns**: `bool` - Success status
- **Transaction**: Yes
- **Updates**: `payment.ReceiptPath`, `payment.Status` = 'Pending'
- **Usage**: Called from `ShopCon->submit_ewallet_payment()`

---

### 6. Order Retrieval Functions

#### `get_order($order_id)`
- **Purpose**: Get order by ID
- **Returns**: `object|null` - Order object

#### `get_order_with_customer($order_id)`
- **Purpose**: Get order with customer details
- **Returns**: `object|null` - Order object with customer info

#### `get_order_details_for_popup($order_id)`
- **Purpose**: Get full order details for popup display
- **Returns**: `object|null` - Order object with all related data
- **Usage**: Called from `SalesCon->get_order_details()`

#### `get_ready_to_approve_orders($sales_rep_id = null)`
- **Purpose**: Get orders ready to approve (with AdminStatus)
- **Parameters**:
  - `$sales_rep_id` (int, optional): Filter by sales rep
- **Returns**: `array` - Array of order objects with AdminStatus
- **Usage**: Called from `SalesCon->sales_orders()` for "Ready to Approve" tab

#### `get_customer_orders($customer_id)`
- **Purpose**: Get all orders for a customer
- **Returns**: `array` - Array of order objects

#### `get_order_customizations($order_id)`
- **Purpose**: Get order items with product details
- **Returns**: `array` - Array of order item objects

---

### 7. Order Tracking Functions

#### `get_order_tracking_details($order_id)`
- **Purpose**: Get order details for tracking page
- **Returns**: `object|null` - Order object with calculated dates
- **Calculates**: OcularDate, FabricationDate, InstallationDate, EstimatedDelivery

#### `get_order_progress($status)`
- **Purpose**: Get progress steps based on order status
- **Parameters**:
  - `$status` (string): Order status
- **Returns**: `array` - Progress steps array
- **Steps**: order_placed, ocular_visit, in_fabrication, installed, completed

---

### 8. Notification Functions

#### `notify_customer($customer_id, $order_id, $status_type, $total_amount = 0)`
- **Purpose**: Notify customer of order status change (private function)
- **Parameters**:
  - `$customer_id` (int): Customer ID
  - `$order_id` (int): Order ID
  - `$status_type` (string): Status type ('approved', 'disapproved', etc.)
  - `$total_amount` (float, optional): Order total
- **Returns**: `bool` - Success status
- **Updates**: `order.CustomerNotified`, `order.CustomerNotified_Date`
- **Note**: Email sending is TODO - currently only logs

---

### 9. Utility Functions

#### `generate_order_number()`
- **Purpose**: Generate unique order number (GI001, GI002, etc.)
- **Returns**: `string` - Order number
- **Private**: Yes

#### `get_default_sales_rep()`
- **Purpose**: Get default sales rep ID
- **Returns**: `int` - Sales rep UserID

#### `calculate_order_summary($order_id)`
- **Purpose**: Calculate order summary (items, subtotal, shipping, handling, total)
- **Returns**: `array` - Summary array

#### `can_create_order($product_id, $quantity = 1)`
- **Purpose**: Check if order can be created (inventory check)
- **Returns**: `array` - `['can_create' => bool, 'missing_materials' => array]`

---

## Function Call Flow

### Complete Order Flow Example

```
1. Customer places order
   └─► create_order()
   └─► save_order_customizations()
   Status: 'Pending Review'

2. Sales Rep reviews order
   └─► get_sales_rep_orders($sales_rep_id, 'Pending Review')

3. Sales Rep requests approval
   └─► request_admin_approval($order_id, $sales_rep_id, $notes)
   Status: 'Awaiting Admin'

4. Admin reviews order
   └─► get_awaiting_admin_orders()
   
5a. Admin approves
   └─► admin_approve_order($order_id, $admin_id, $admin_notes)
   Status: 'Ready to Approve' (AdminStatus = 'Approved')

5b. Admin disapproves
   └─► admin_disapprove_order($order_id, $admin_id, $disapproval_reason)
   Status: 'Ready to Approve' (AdminStatus = 'Disapproved')

6. Sales Rep final decision
   └─► get_ready_to_approve_orders($sales_rep_id)
   
6a. Sales Rep approves
   └─► sales_rep_final_approve($order_id, $sales_rep_id)
   └─► create_payment_record($order_id)
   └─► notify_customer($customer_id, $order_id, 'approved', $total_amount)
   Status: 'Approved'

6b. Sales Rep disapproves
   └─► sales_rep_final_disapprove($order_id, $sales_rep_id, $reason)
   Status: 'Disapproved'

7. Payment processing
   └─► save_payment_receipt($order_id, $receipt_path, $amount) [E-Wallet]
   └─► update_payment_status($order_id, 'Paid')

8. Order fulfillment
   └─► update_order_status($order_id, 'In Fabrication')
   └─► update_order_status($order_id, 'Ready for Installation')
   └─► update_order_status($order_id, 'Completed')
```

---

## Error Handling

All functions that perform database operations use transactions and return structured responses:

- **Success**: `['success' => true, 'message' => '...']` or `true`/`int`
- **Failure**: `['success' => false, 'message' => 'Error description']` or `false`

Functions validate:
- Order existence
- Order status
- User permissions
- Status transitions
- Required parameters

---

## Transaction Safety

All functions that modify order status use database transactions:
- `create_order()`
- `request_admin_approval()`
- `admin_approve_order()`
- `admin_disapprove_order()`
- `sales_rep_final_approve()`
- `sales_rep_final_disapprove()`
- `update_order_status()`
- `update_payment_status()`
- `save_payment_receipt()`

Transactions ensure:
- Data consistency
- Atomic operations
- Rollback on errors
- Activity logging

---

## Legacy Table Support

Functions maintain backward compatibility with legacy tables:
- `pending_review_orders`
- `awaiting_admin_orders`
- `ready_to_approve_orders`
- `approved_orders`
- `disapproved_orders`

These tables are populated/updated alongside the unified `order` table for display purposes.

---

## Activity Logging

All status changes are logged in `system_activity_log` with:
- Action type
- Description
- Role
- User ID
- Related ID and Type
- Timestamp

---

**Document Version**: 1.0  
**Last Updated**: 2025-01-08  
**Model File**: `application/models/Order_model.php`
