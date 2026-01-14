# Testing Guide: New Order Approval Flow (Sales Rep Archived)

This document provides a step-by-step testing guide for the new order approval flow where Sales Representatives are archived and orders go directly to Admin for approval.

## Test Environment Setup

1. **Database Setup**
   - Ensure you have at least one Sales Representative in the `user` table
   - Ensure you have at least one Customer in the `customer` table
   - Ensure you have at least one Admin user

2. **Test Scenarios**

## Test Scenario 1: Order Creation with NULL SalesRep_ID

### Steps:
1. Create a new order with `SalesRep_ID = NULL`
2. Verify the order status is automatically set to `'Awaiting Admin'` (not `'Pending Review'`)
3. Check that no sales notification is created

### Expected Results:
- Order Status: `'Awaiting Admin'`
- Order appears in Admin's order list with status filter "Awaiting Admin"
- No sales notification created

### SQL Verification:
```sql
SELECT OrderID, OrderNumber, Status, SalesRep_ID, OrderDate 
FROM `order` 
WHERE SalesRep_ID IS NULL 
ORDER BY OrderDate DESC 
LIMIT 1;
```

## Test Scenario 2: Order Creation with Archived Sales Rep

### Steps:
1. Archive a Sales Representative:
   ```sql
   UPDATE user 
   SET Status = 'Inactive' 
   WHERE Role = 'Sales Representative' 
   AND UserID = [SALES_REP_ID];
   ```

2. Create a new order assigned to the archived Sales Rep
3. Verify the order status is automatically set to `'Awaiting Admin'`

### Expected Results:
- Order Status: `'Awaiting Admin'` (not `'Pending Review'`)
- Order appears in Admin's order list
- No sales notification created for archived Sales Rep

### SQL Verification:
```sql
-- Check Sales Rep status
SELECT UserID, First_Name, Last_Name, Status 
FROM user 
WHERE UserID = [SALES_REP_ID];

-- Check order status
SELECT o.OrderID, o.OrderNumber, o.Status, o.SalesRep_ID, u.Status as SalesRep_Status
FROM `order` o
LEFT JOIN user u ON u.UserID = o.SalesRep_ID
WHERE o.OrderID = [ORDER_ID];
```

## Test Scenario 3: Admin Approval Flow

### Steps:
1. Navigate to Admin Orders page: `/admin-orders?type=direct`
2. Filter orders by status: "Awaiting Admin"
3. Click on an order with status "Awaiting Admin"
4. Verify the order details modal shows:
   - All required information (Order Info, Customer Info, Sales Rep Info, Products, Pricing, etc.)
   - Sales Rep Status badge (showing "Active" or "Archived (Inactive)")
   - Approve/Disapprove buttons are visible
   - Special Instructions section (if applicable)

5. **Test Approval:**
   - Click "Approve Order" button
   - Optionally add admin notes
   - Confirm approval

6. **Verify Approval:**
   - Order status should change to `'Approved'`
   - Payment record should be created with Status = 'Pending'
   - Activity log should record the approval

### Expected Results:
- Order Status: `'Approved'` (directly, no intermediate "Ready to Approve" status)
- Payment record exists: `SELECT * FROM payment WHERE OrderID = [ORDER_ID]`
- Activity log entry: `SELECT * FROM system_activity_log WHERE RelatedID = [ORDER_ID] AND Action = 'Order Approved by Admin'`

### SQL Verification:
```sql
-- Check order status
SELECT OrderID, OrderNumber, Status, ApprovedBy_Admin_ID, Approved_Date
FROM `order`
WHERE OrderID = [ORDER_ID];

-- Check payment record
SELECT * FROM payment WHERE OrderID = [ORDER_ID];

-- Check activity log
SELECT * FROM system_activity_log 
WHERE RelatedID = [ORDER_ID] 
AND Action = 'Order Approved by Admin'
ORDER BY Timestamp DESC;
```

## Test Scenario 4: Admin Disapproval Flow

### Steps:
1. Navigate to Admin Orders page
2. Filter orders by status: "Awaiting Admin"
3. Click on an order with status "Awaiting Admin"
4. Click "Disapprove Order" button
5. **Without reason:** Try to disapprove without entering a reason
   - Should show error: "Please provide a reason for disapproval"
6. **With reason:** Enter disapproval reason and confirm

### Expected Results:
- Order Status: `'Disapproved'` (directly, no intermediate "Ready to Approve" status)
- DisapprovalReason field is populated
- DisapprovedBy_ID and Disapproved_Date are set
- Activity log entry created

### SQL Verification:
```sql
-- Check order status and disapproval info
SELECT OrderID, OrderNumber, Status, DisapprovedBy, DisapprovedBy_ID, 
       DisapprovalReason, Disapproved_Date
FROM `order`
WHERE OrderID = [ORDER_ID];

-- Check activity log
SELECT * FROM system_activity_log 
WHERE RelatedID = [ORDER_ID] 
AND Action = 'Order Disapproved by Admin'
ORDER BY Timestamp DESC;
```

## Test Scenario 5: Sales Rep Status Display

### Steps:
1. View an order with an archived Sales Rep
2. Check the Sales Representative section in order details
3. Verify the status badge shows "Archived (Inactive)" with warning badge style
4. Verify Sales Rep name, email, and phone are still displayed (historical reference)

### Expected Results:
- Sales Rep Status badge: "Archived (Inactive)" with yellow/warning color
- Sales Rep information still visible for historical reference
- Visual indicator that Sales Rep is archived

## Test Scenario 6: Order List Filtering

### Steps:
1. Navigate to Admin Orders page
2. Use status filter dropdown
3. Verify "Awaiting Admin" option is available
4. Verify "Disapproved" option is available
5. Filter by "Awaiting Admin" and verify orders are displayed

### Expected Results:
- Status filter includes "Awaiting Admin" and "Disapproved"
- Orders with Status = 'Awaiting Admin' are displayed when filtered
- Orders show correct status badges

## Test Scenario 7: Existing Orders Transition

### Steps:
1. Find an existing order with Status = 'Pending Review' and SalesRep_ID = NULL or archived Sales Rep
2. Manually update the order to test auto-transition (or create a test order)
3. Verify the order appears in "Awaiting Admin" filter

### SQL Test:
```sql
-- Update an existing order to test
UPDATE `order` 
SET Status = 'Pending Review', SalesRep_ID = NULL
WHERE OrderID = [TEST_ORDER_ID];

-- Then check if it should be in Awaiting Admin
SELECT OrderID, OrderNumber, Status, SalesRep_ID
FROM `order`
WHERE Status = 'Awaiting Admin';
```

## Test Scenario 8: JavaScript Functionality

### Steps:
1. Open browser console (F12)
2. Navigate to Admin Orders page
3. Check for JavaScript errors
4. Click on an order with status "Awaiting Admin"
5. Verify:
   - Modal opens correctly
   - Sales Rep status badge displays correctly
   - Approve/Disapprove buttons are visible
   - Special Instructions section shows/hides correctly

### Expected Results:
- No JavaScript errors in console
- Modal displays all information correctly
- Buttons are functional
- Status badges have correct colors

## Test Checklist

- [ ] Order with NULL SalesRep_ID auto-transitions to 'Awaiting Admin'
- [ ] Order with archived Sales Rep auto-transitions to 'Awaiting Admin'
- [ ] No sales notification created for NULL/archived Sales Rep
- [ ] Admin can view orders with status "Awaiting Admin"
- [ ] Order details modal shows all required information
- [ ] Sales Rep status badge displays correctly (Active/Archived)
- [ ] Approve button works and directly sets status to 'Approved'
- [ ] Payment record created on approval
- [ ] Disapprove button requires reason
- [ ] Disapprove button directly sets status to 'Disapproved'
- [ ] Activity logs are created correctly
- [ ] Status filter includes "Awaiting Admin" and "Disapproved"
- [ ] No intermediate "Ready to Approve" status is used

## Common Issues to Check

1. **JavaScript Variables:**
   - Verify `approveOrderUrl` and `disapproveOrderUrl` are defined in `admin_orders.php`
   - Check browser console for undefined variable errors

2. **Database:**
   - Verify `payment` table exists
   - Verify `system_activity_log` table exists
   - Check that order status ENUM includes 'Awaiting Admin' and 'Disapproved'

3. **Permissions:**
   - Ensure Admin user has proper permissions
   - Check session is active

4. **AJAX Endpoints:**
   - Verify `AdminCon/approve_order_admin` endpoint exists
   - Verify `AdminCon/disapprove_order_admin` endpoint exists
   - Check response format is JSON

## SQL Queries for Verification

### Check all orders awaiting admin approval:
```sql
SELECT o.OrderID, o.OrderNumber, o.Status, o.OrderDate,
       u.First_Name as Customer_First_Name, u.Last_Name as Customer_Last_Name,
       sr.First_Name as SalesRep_First_Name, sr.Last_Name as SalesRep_Last_Name,
       sr.Status as SalesRep_Status
FROM `order` o
LEFT JOIN customer c ON c.Customer_ID = o.Customer_ID
LEFT JOIN user u ON u.UserID = c.UserID
LEFT JOIN user sr ON sr.UserID = o.SalesRep_ID
WHERE o.Status = 'Awaiting Admin'
ORDER BY o.OrderDate DESC;
```

### Check archived Sales Reps:
```sql
SELECT UserID, First_Name, Last_Name, Email, Status
FROM user
WHERE Role = 'Sales Representative'
AND Status = 'Inactive';
```

### Check recent order approvals:
```sql
SELECT o.OrderID, o.OrderNumber, o.Status, o.ApprovedBy_Admin_ID, o.Approved_Date,
       u.First_Name as Admin_First_Name, u.Last_Name as Admin_Last_Name
FROM `order` o
LEFT JOIN user u ON u.UserID = o.ApprovedBy_Admin_ID
WHERE o.Status = 'Approved'
AND o.Approved_Date >= DATE_SUB(NOW(), INTERVAL 1 DAY)
ORDER BY o.Approved_Date DESC;
```

## Notes

- The new flow bypasses Sales Rep approval entirely
- Orders go directly from 'Awaiting Admin' to 'Approved' or 'Disapproved'
- Historical orders with Sales Rep information are still displayed for reference
- Sales Rep status is shown to indicate if they are archived
