# Order Flow System - Testing Guide
## Complete Step-by-Step Testing Procedures

**Document Version**: 1.0  
**Last Updated**: 2025-01-08  
**Purpose**: Practical guide for testing all order flow functions

---

## 📋 Pre-Testing Setup

### 1. Database Preparation

**Check Required Data:**
```sql
-- Verify you have test users
SELECT UserID, First_Name, Last_Name, Email, Role 
FROM user 
WHERE Role IN ('Customer', 'Sales Representative', 'Admin');

-- Verify you have a customer
SELECT Customer_ID, UserID 
FROM customer;

-- Check existing orders (optional - clear if needed)
SELECT OrderID, OrderNumber, Status, Customer_ID, SalesRep_ID 
FROM `order`;
```

**Create Test Data (if needed):**
```sql
-- Create test customer (if not exists)
INSERT INTO user (First_Name, Last_Name, Email, Password, PhoneNum, Role, Status)
VALUES ('Test', 'Customer', 'test.customer@test.com', '$2y$10$...', '09999999999', 'Customer', 'Active');

-- Get the UserID from above, then:
INSERT INTO customer (UserID) VALUES (LAST_INSERT_ID());

-- Create test sales rep (if not exists)
INSERT INTO user (First_Name, Last_Name, Email, Password, PhoneNum, Role, Status)
VALUES ('Test', 'SalesRep', 'test.sales@test.com', '$2y$10$...', '09999999998', 'Sales Representative', 'Active');

-- Create test admin (if not exists)
INSERT INTO user (First_Name, Last_Name, Email, Password, PhoneNum, Role, Status)
VALUES ('Test', 'Admin', 'test.admin@test.com', '$2y$10$...', '09999999997', 'Admin', 'Active');
```

### 2. Test Environment Setup

**Required:**
- ✅ XAMPP/WAMP running (Apache + MySQL)
- ✅ CodeIgniter application accessible
- ✅ Database connection configured
- ✅ Browser with developer tools (F12)
- ✅ Database management tool (phpMyAdmin/MySQL Workbench)

**Browser Tools:**
- Open Developer Console (F12)
- Go to Network tab to monitor AJAX requests
- Go to Console tab to see JavaScript errors

---

## 🧪 Testing Procedures

### Phase 1: Sales Representative Functions Testing

#### Test 1.1: View Orders (`sales_orders()`)

**Objective**: Verify orders display correctly in tabs

**Steps:**
1. **Login as Sales Representative**
   - URL: `http://localhost/Glassify-CI/SalesCon/sales_orders`
   - Use sales rep credentials

2. **Check Tabs Display**
   - Look for tabs: "Pending Review", "Awaiting Admin", "Ready to Approve"
   - Verify each tab shows correct orders

3. **Verify Order Filtering**
   ```sql
   -- In database, check orders assigned to this sales rep
   SELECT OrderID, OrderNumber, Status, SalesRep_ID 
   FROM `order` 
   WHERE SalesRep_ID = [Your_SalesRep_ID];
   ```
   - Compare with what's shown in the interface
   - Verify only orders for this sales rep are visible

4. **Check Order Counts**
   - Verify counts match actual orders in database
   - Check if counts update when orders change status

**Expected Results:**
- ✅ All three tabs visible
- ✅ Orders filtered by SalesRep_ID
- ✅ Order counts accurate
- ✅ Order details displayed correctly

**Database Verification:**
```sql
-- Count orders by status for this sales rep
SELECT Status, COUNT(*) as Count
FROM `order`
WHERE SalesRep_ID = [Your_SalesRep_ID]
GROUP BY Status;
```

---

#### Test 1.2: Request Approval (`request_approval()`)

**Objective**: Verify status transition from 'Pending Review' to 'Awaiting Admin'

**Prerequisites:**
- Have an order with Status = 'Pending Review'
- Order assigned to the sales rep you're testing with

**Steps:**
1. **Note Current Order State**
   ```sql
   -- Before test, record the order state
   SELECT OrderID, OrderNumber, Status, SalesRep_ID 
   FROM `order` 
   WHERE OrderID = [Test_Order_ID];
   
   SELECT COUNT(*) as awaiting_count 
   FROM awaiting_admin_orders 
   WHERE OrderID = [Test_Order_ID];
   ```

2. **Perform Action in UI**
   - Go to "Pending Review" tab
   - Click "Request Approval" button on a test order
   - Fill in notes (optional)
   - Click "Submit to Admin"

3. **Check Browser Response**
   - Open Network tab (F12)
   - Look for AJAX request to `SalesCon/request_approval`
   - Check response: Should be `{"success": true, "message": "..."}`

4. **Verify Database Changes**
   ```sql
   -- Check order status changed
   SELECT OrderID, OrderNumber, Status 
   FROM `order` 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: Status = 'Awaiting Admin'
   
   -- Check record in awaiting_admin_orders
   SELECT * FROM awaiting_admin_orders 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: Record exists
   
   -- Check activity log
   SELECT * FROM system_activity_log 
   WHERE RelatedID = [Test_Order_ID] 
   AND RelatedType = 'Order'
   ORDER BY Timestamp DESC 
   LIMIT 1;
   -- Expected: Action = 'Approval Requested'
   ```

5. **Verify UI Update**
   - Order should disappear from "Pending Review" tab
   - Order should appear in "Awaiting Admin" tab (read-only)

**Expected Results:**
- ✅ Status changed to 'Awaiting Admin'
- ✅ Record inserted into `awaiting_admin_orders`
- ✅ Activity logged in `system_activity_log`
- ✅ UI updated correctly

**Error Testing:**
- Try with invalid OrderID → Should return error
- Try with order not assigned to this sales rep → Should return error
- Try with order already in 'Awaiting Admin' → Should return error

---

#### Test 1.3: Get Order Details (`get_order_details()`)

**Objective**: Verify popup shows complete order information

**Steps:**
1. **Click "View" or "Details" button** on any order
2. **Check Popup Content:**
   - Order number
   - Customer information
   - Product details
   - Customization details
   - Total amount
   - Delivery address
   - Order date

3. **Verify Data Accuracy**
   ```sql
   -- Compare with database
   SELECT o.*, c.Customer_ID, u.First_Name, u.Last_Name, u.Email
   FROM `order` o
   JOIN customer c ON o.Customer_ID = c.Customer_ID
   JOIN user u ON c.UserID = u.UserID
   WHERE o.OrderID = [Test_Order_ID];
   ```

**Expected Results:**
- ✅ All order details displayed
- ✅ Product information correct
- ✅ Customization details shown
- ✅ Customer information accurate

---

#### Test 1.4: Final Approve Order (`approve_order()`)

**Prerequisites:**
- Have an order with Status = 'Ready to Approve'
- Order must have AdminStatus = 'Approved' in `ready_to_approve_orders`

**Steps:**
1. **Note Current State**
   ```sql
   SELECT OrderID, Status, PaymentStatus 
   FROM `order` 
   WHERE OrderID = [Test_Order_ID];
   
   SELECT * FROM ready_to_approve_orders 
   WHERE OrderID = [Test_Order_ID];
   
   SELECT COUNT(*) as payment_count 
   FROM payment 
   WHERE OrderID = [Test_Order_ID];
   ```

2. **Perform Action**
   - Go to "Ready to Approve" tab
   - Click "Check" button
   - Verify AdminStatus shows "Approved"
   - Click "Approve Order"

3. **Verify Database Changes**
   ```sql
   -- Check status changed
   SELECT OrderID, Status, ApprovedBy_SalesRep_ID, Approved_Date 
   FROM `order` 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: Status = 'Approved', ApprovedBy_SalesRep_ID set, Approved_Date set
   
   -- Check payment record created
   SELECT * FROM payment 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: Payment record exists, Status = 'Pending', Amount = Order.TotalAmount
   
   -- Check moved to approved_orders
   SELECT * FROM approved_orders 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: Record exists
   
   -- Check removed from ready_to_approve_orders
   SELECT * FROM ready_to_approve_orders 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: No record (deleted)
   
   -- Check activity log
   SELECT * FROM system_activity_log 
   WHERE RelatedID = [Test_Order_ID] 
   ORDER BY Timestamp DESC 
   LIMIT 1;
   -- Expected: Action = 'Order Approved'
   ```

**Expected Results:**
- ✅ Status changed to 'Approved'
- ✅ Payment record created
- ✅ Record moved to `approved_orders`
- ✅ Record deleted from `ready_to_approve_orders`
- ✅ CustomerNotified flag updated (if implemented)

---

#### Test 1.5: Final Disapprove Order (`disapprove_order()`)

**Prerequisites:**
- Have an order with Status = 'Ready to Approve'
- Order must have AdminStatus = 'Disapproved' in `ready_to_approve_orders`

**Steps:**
1. **Perform Action**
   - Go to "Ready to Approve" tab
   - Click "Check" button
   - Verify AdminStatus shows "Disapproved"
   - Click "Disapprove Order"
   - Enter disapproval reason (optional)

2. **Verify Database Changes**
   ```sql
   -- Check status changed
   SELECT OrderID, Status, DisapprovedBy, DisapprovedBy_ID, DisapprovalReason 
   FROM `order` 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: Status = 'Disapproved', DisapprovedBy = 'Sales Rep', reason saved
   
   -- Check moved to disapproved_orders
   SELECT * FROM disapproved_orders 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: Record exists
   
   -- Check removed from ready_to_approve_orders
   SELECT * FROM ready_to_approve_orders 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: No record
   ```

**Expected Results:**
- ✅ Status changed to 'Disapproved'
- ✅ Disapproval reason saved
- ✅ Record moved to `disapproved_orders`

---

### Phase 2: Administrator Functions Testing

#### Test 2.1: Get Awaiting Approval Orders (`get_awaiting_approval_orders()`)

**Steps:**
1. **Login as Admin**
   - URL: `http://localhost/Glassify-CI/AdminCon/get_awaiting_approval_orders`
   - Or access through admin orders page

2. **Verify Orders Displayed**
   ```sql
   -- Check what should be displayed
   SELECT OrderID, OrderNumber, Status 
   FROM `order` 
   WHERE Status = 'Awaiting Admin';
   ```
   - Compare with UI

**Expected Results:**
- ✅ Only orders with Status = 'Awaiting Admin' shown
- ✅ Customer details included
- ✅ Sales rep details included

---

#### Test 2.2: Admin Approve Order (`approve_order_admin()`)

**Prerequisites:**
- Have an order with Status = 'Awaiting Admin'

**Steps:**
1. **Note Current State**
   ```sql
   SELECT OrderID, Status 
   FROM `order` 
   WHERE OrderID = [Test_Order_ID];
   
   SELECT * FROM awaiting_admin_orders 
   WHERE OrderID = [Test_Order_ID];
   ```

2. **Perform Action**
   - View order details
   - Click "Approve" button
   - Enter admin notes (optional)
   - Submit

3. **Verify Database Changes**
   ```sql
   -- Check status changed
   SELECT OrderID, Status, ApprovedBy_Admin_ID, Approved_Date 
   FROM `order` 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: Status = 'Ready to Approve', ApprovedBy_Admin_ID set
   
   -- Check ready_to_approve_orders
   SELECT * FROM ready_to_approve_orders 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: AdminStatus = 'Approved', AdminNotes saved
   
   -- Check removed from awaiting_admin_orders
   SELECT * FROM awaiting_admin_orders 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: No record
   ```

**Expected Results:**
- ✅ Status changed to 'Ready to Approve'
- ✅ AdminStatus = 'Approved' in `ready_to_approve_orders`
- ✅ Record deleted from `awaiting_admin_orders`

---

#### Test 2.3: Admin Disapprove Order (`disapprove_order_admin()`)

**Prerequisites:**
- Have an order with Status = 'Awaiting Admin'

**Steps:**
1. **Perform Action**
   - View order details
   - Click "Disapprove" button
   - **Enter disapproval reason (required)**
   - Submit

2. **Verify Database Changes**
   ```sql
   -- Check status changed
   SELECT OrderID, Status, DisapprovedBy, DisapprovedBy_ID, DisapprovalReason 
   FROM `order` 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: Status = 'Ready to Approve', DisapprovedBy = 'Admin', reason saved
   
   -- Check ready_to_approve_orders
   SELECT * FROM ready_to_approve_orders 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: AdminStatus = 'Disapproved'
   ```

**Expected Results:**
- ✅ Status changed to 'Ready to Approve'
- ✅ AdminStatus = 'Disapproved'
- ✅ Disapproval reason saved

---

### Phase 3: Edge Cases & Error Handling

#### Test 3.1: Invalid Order ID

**Test Cases:**
1. **Non-existent Order ID**
   - Try to approve/disapprove order ID 99999
   - Expected: Error message returned

2. **Invalid Format**
   - Try with "ABC123", "123ABC", etc.
   - Expected: Error handling

3. **Null/Empty Order ID**
   - Try with empty string or null
   - Expected: Validation error

**How to Test:**
```javascript
// In browser console, test AJAX call
$.ajax({
    url: '/Glassify-CI/SalesCon/request_approval',
    method: 'POST',
    data: { order_id: '99999' },
    success: function(response) {
        console.log('Response:', response);
        // Should show error
    }
});
```

---

#### Test 3.2: Invalid Status Transitions

**Test Cases:**
1. **Skip Stages**
   - Try to approve order directly from 'Pending Review'
   - Expected: Validation error

2. **Backwards Transition**
   - Try to change 'Approved' back to 'Pending Review'
   - Expected: Validation error

3. **Wrong Role Action**
   - Sales rep tries to approve from 'Awaiting Admin'
   - Expected: Error (only admin can do this)

**Database Check:**
```sql
-- Try to manually update status (should fail validation in code)
UPDATE `order` 
SET Status = 'Approved' 
WHERE OrderID = [Test_Order_ID] AND Status = 'Pending Review';
-- Then try to use the function - should detect invalid transition
```

---

#### Test 3.3: Permission Checks

**Test Cases:**
1. **Sales Rep Access**
   - Sales rep tries to see orders assigned to another sales rep
   - Expected: Only their own orders visible

2. **Admin Access**
   - Admin should see all orders
   - Expected: All orders visible

3. **Customer Access**
   - Customer tries to see other customers' orders
   - Expected: Only their own orders visible

**How to Test:**
```sql
-- Check what orders sales rep should see
SELECT OrderID, SalesRep_ID 
FROM `order` 
WHERE SalesRep_ID = [SalesRep_ID];

-- Compare with what's shown in UI
```

---

#### Test 3.4: Transaction Rollback

**Objective**: Verify that errors cause complete rollback

**How to Test:**
1. **Simulate Error**
   - Temporarily break database connection
   - Or cause a constraint violation
   - Try to perform an action

2. **Verify Rollback**
   ```sql
   -- Check that no partial updates occurred
   SELECT * FROM `order` WHERE OrderID = [Test_Order_ID];
   SELECT * FROM awaiting_admin_orders WHERE OrderID = [Test_Order_ID];
   SELECT * FROM system_activity_log WHERE RelatedID = [Test_Order_ID];
   -- Expected: No changes made
   ```

**Expected Results:**
- ✅ No partial updates
- ✅ Database consistency maintained
- ✅ Error message returned

---

### Phase 4: Payment Integration Testing

#### Test 4.1: Payment Record Creation

**Prerequisites:**
- Order with Status = 'Approved'

**Steps:**
1. **Verify Payment Created**
   ```sql
   SELECT * FROM payment 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: Payment record exists
   ```

2. **Check Payment Details**
   ```sql
   SELECT 
       p.*,
       o.TotalAmount,
       o.PaymentMethod
   FROM payment p
   JOIN `order` o ON p.OrderID = o.OrderID
   WHERE p.OrderID = [Test_Order_ID];
   -- Expected: 
   -- p.Amount = o.TotalAmount
   -- p.PaymentMethod = o.PaymentMethod
   -- p.Status = 'Pending'
   ```

**Expected Results:**
- ✅ Payment record created automatically
- ✅ Amount matches order total
- ✅ Payment method matches
- ✅ Status = 'Pending'

---

#### Test 4.2: Payment Receipt Upload

**Steps:**
1. **Upload Receipt**
   - Go to payment page
   - Select receipt file (image/PDF)
   - Upload

2. **Verify Upload**
   ```sql
   SELECT ReceiptPath, Status 
   FROM payment 
   WHERE OrderID = [Test_Order_ID];
   -- Expected: ReceiptPath set, Status = 'Pending'
   ```

3. **Check File Exists**
   - Verify file exists in uploads directory
   - Check file permissions

**Expected Results:**
- ✅ File uploaded successfully
- ✅ ReceiptPath saved in database
- ✅ File accessible

---

#### Test 4.3: Payment Verification

**Steps:**
1. **Mark Payment as Paid**
   - Sales rep verifies receipt
   - Marks payment as paid

2. **Verify Status Update**
   ```sql
   SELECT p.Status, o.PaymentStatus 
   FROM payment p
   JOIN `order` o ON p.OrderID = o.OrderID
   WHERE p.OrderID = [Test_Order_ID];
   -- Expected: p.Status = 'Paid', o.PaymentStatus = 'Paid'
   ```

**Expected Results:**
- ✅ Payment status updated
- ✅ Order payment status updated
- ✅ Activity logged

---

### Phase 5: Integration Testing

#### Test 5.1: Complete Order Flow

**Objective**: Test entire order flow from creation to completion

**Steps:**
1. **Create Order** (as Customer)
   - Add items to cart
   - Proceed to checkout
   - Place order
   - Verify: Status = 'Pending Review'

2. **Request Approval** (as Sales Rep)
   - View order in "Pending Review" tab
   - Request approval
   - Verify: Status = 'Awaiting Admin'

3. **Admin Review** (as Admin)
   - View order in admin panel
   - Approve order
   - Verify: Status = 'Ready to Approve', AdminStatus = 'Approved'

4. **Final Approval** (as Sales Rep)
   - View in "Ready to Approve" tab
   - Approve order
   - Verify: Status = 'Approved', Payment created

5. **Payment** (as Customer)
   - Upload payment receipt
   - Verify: Receipt saved

6. **Payment Verification** (as Sales Rep)
   - Mark payment as paid
   - Verify: PaymentStatus = 'Paid'

**Expected Results:**
- ✅ Complete flow works end-to-end
- ✅ All status transitions valid
- ✅ All records created correctly
- ✅ Activity logged at each step

---

## 📝 Testing Checklist

### Sales Representative Functions
- [ ] `sales_orders()` - Orders display correctly
- [ ] `request_approval()` - Status transition works
- [ ] `get_order_details()` - Popup shows correct data
- [ ] `approve_order()` - Final approval works
- [ ] `disapprove_order()` - Final disapproval works

### Administrator Functions
- [ ] `get_awaiting_approval_orders()` - Orders retrieved correctly
- [ ] `approve_order_admin()` - Admin approval works
- [ ] `disapprove_order_admin()` - Admin disapproval works

### Error Handling
- [ ] Invalid Order ID handling
- [ ] Invalid status transitions blocked
- [ ] Permission checks work
- [ ] Transaction rollback on errors

### Payment Integration
- [ ] Payment record created on approval
- [ ] Receipt upload works
- [ ] Payment verification works

### Integration
- [ ] Complete order flow works
- [ ] All status transitions valid
- [ ] Activity logging complete

---

## 🐛 Common Issues & Solutions

### Issue 1: Orders Not Displaying

**Check:**
```sql
-- Verify sales rep ID matches
SELECT SalesRep_ID FROM `order` WHERE OrderID = [Order_ID];
SELECT UserID FROM user WHERE Email = '[Sales_Rep_Email]';
```

**Solution:** Ensure SalesRep_ID in order matches the logged-in user's UserID.

---

### Issue 2: Status Not Changing

**Check:**
```sql
-- Check current status
SELECT Status FROM `order` WHERE OrderID = [Order_ID];

-- Check for errors in activity log
SELECT * FROM system_activity_log 
WHERE RelatedID = [Order_ID] 
ORDER BY Timestamp DESC 
LIMIT 5;
```

**Solution:** Check browser console for JavaScript errors, check PHP error logs.

---

### Issue 3: Payment Not Created

**Check:**
```sql
-- Verify order is approved
SELECT Status FROM `order` WHERE OrderID = [Order_ID];

-- Check if payment exists
SELECT * FROM payment WHERE OrderID = [Order_ID];
```

**Solution:** Ensure order status is 'Approved' before checking for payment.

---

## 📊 Test Results Template

```
Test Date: ___________
Tester: ___________

Test Case: ___________
Status: [ ] Pass [ ] Fail [ ] Partial
Notes: ___________

Database Verification:
- Order Status: ___________
- Related Records: ___________
- Activity Log: ___________

Issues Found: ___________
```

---

## 🎯 Quick Test Script

**Run this SQL to check system health:**

```sql
-- Check order status distribution
SELECT Status, COUNT(*) as Count 
FROM `order` 
GROUP BY Status;

-- Check for orphaned records
SELECT o.OrderID, o.Status, 
       (SELECT COUNT(*) FROM awaiting_admin_orders WHERE OrderID = o.OrderID) as awaiting_count,
       (SELECT COUNT(*) FROM ready_to_approve_orders WHERE OrderID = o.OrderID) as ready_count
FROM `order` o
WHERE o.Status = 'Awaiting Admin' 
AND (SELECT COUNT(*) FROM awaiting_admin_orders WHERE OrderID = o.OrderID) = 0;

-- Check recent activity
SELECT * FROM system_activity_log 
ORDER BY Timestamp DESC 
LIMIT 10;
```

---

*For detailed function documentation, refer to ORDER_FLOW_FUNCTIONS_REFERENCE.md*
