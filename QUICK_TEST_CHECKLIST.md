# Quick Testing Checklist
## Fast Reference for Order Flow Testing

---

## 🚀 Quick Start Testing

### Step 1: Setup (5 minutes)
1. ✅ Start XAMPP (Apache + MySQL)
2. ✅ Open browser: `http://localhost/Glassify-CI`
3. ✅ Open Developer Tools (F12) → Network tab
4. ✅ Open phpMyAdmin for database checks

### Step 2: Test Data Setup
```sql
-- Check you have test users
SELECT UserID, Email, Role FROM user WHERE Role IN ('Customer', 'Sales Representative', 'Admin');

-- Create a test order if needed (or use existing)
-- Make sure it has Status = 'Pending Review'
SELECT OrderID, OrderNumber, Status, SalesRep_ID FROM `order` LIMIT 5;
```

---

## ✅ Testing Checklist

### Sales Rep Functions (15 minutes)

#### Test 1: View Orders
- [ ] Login as Sales Rep
- [ ] See "Pending Review" tab with orders
- [ ] See "Awaiting Admin" tab
- [ ] See "Ready to Approve" tab
- [ ] Order counts match database

**Quick DB Check:**
```sql
SELECT Status, COUNT(*) FROM `order` WHERE SalesRep_ID = [Your_ID] GROUP BY Status;
```

---

#### Test 2: Request Approval
- [ ] Click "Request Approval" on a "Pending Review" order
- [ ] Fill notes (optional)
- [ ] Submit
- [ ] Check browser response: `{"success": true}`
- [ ] Order disappears from "Pending Review" tab
- [ ] Order appears in "Awaiting Admin" tab

**Quick DB Check:**
```sql
-- Before: Status should be 'Pending Review'
-- After: Status should be 'Awaiting Admin'
SELECT OrderID, Status FROM `order` WHERE OrderID = [Test_Order_ID];

-- Check record created
SELECT * FROM awaiting_admin_orders WHERE OrderID = [Test_Order_ID];
```

---

#### Test 3: View Order Details
- [ ] Click "View" or "Details" button
- [ ] Popup shows: Order number, Customer info, Products, Total
- [ ] All data looks correct

---

#### Test 4: Final Approve (After Admin Approval)
**Prerequisites:** Order must be in "Ready to Approve" with AdminStatus = 'Approved'

- [ ] Go to "Ready to Approve" tab
- [ ] Click "Check" button
- [ ] See AdminStatus = "Approved"
- [ ] Click "Approve Order"
- [ ] Check response: `{"success": true}`

**Quick DB Check:**
```sql
-- Status should be 'Approved'
SELECT Status, ApprovedBy_SalesRep_ID, Approved_Date 
FROM `order` WHERE OrderID = [Test_Order_ID];

-- Payment should be created
SELECT * FROM payment WHERE OrderID = [Test_Order_ID];

-- Should be in approved_orders
SELECT * FROM approved_orders WHERE OrderID = [Test_Order_ID];
```

---

### Admin Functions (10 minutes)

#### Test 5: View Awaiting Orders
- [ ] Login as Admin
- [ ] Go to admin orders page
- [ ] See "Order Schedule Approval" section
- [ ] Only orders with Status = 'Awaiting Admin' shown

**Quick DB Check:**
```sql
SELECT OrderID, Status FROM `order` WHERE Status = 'Awaiting Admin';
```

---

#### Test 6: Admin Approve
- [ ] Click "Approve" on an awaiting order
- [ ] Enter admin notes (optional)
- [ ] Submit
- [ ] Check response: `{"success": true}`

**Quick DB Check:**
```sql
-- Status should be 'Ready to Approve'
SELECT Status, ApprovedBy_Admin_ID FROM `order` WHERE OrderID = [Test_Order_ID];

-- Check ready_to_approve_orders
SELECT AdminStatus, AdminNotes FROM ready_to_approve_orders 
WHERE OrderID = [Test_Order_ID];
-- Expected: AdminStatus = 'Approved'
```

---

#### Test 7: Admin Disapprove
- [ ] Click "Disapprove" on an awaiting order
- [ ] **Enter disapproval reason (required)**
- [ ] Submit
- [ ] Check response: `{"success": true}`

**Quick DB Check:**
```sql
-- Status should be 'Ready to Approve'
SELECT Status, DisapprovedBy, DisapprovalReason 
FROM `order` WHERE OrderID = [Test_Order_ID];
-- Expected: DisapprovedBy = 'Admin', reason saved

-- Check ready_to_approve_orders
SELECT AdminStatus FROM ready_to_approve_orders 
WHERE OrderID = [Test_Order_ID];
-- Expected: AdminStatus = 'Disapproved'
```

---

### Error Testing (10 minutes)

#### Test 8: Invalid Order ID
- [ ] Try to approve order ID 99999
- [ ] Expected: Error message returned
- [ ] Check response: `{"success": false, "message": "..."}`

#### Test 9: Invalid Status Transition
- [ ] Try to approve order directly from 'Pending Review'
- [ ] Expected: Validation error
- [ ] Check response: `{"success": false, "message": "Invalid status transition"}`

#### Test 10: Permission Check
- [ ] Sales rep tries to see another sales rep's orders
- [ ] Expected: Only their own orders visible
- [ ] Admin should see all orders

---

### Payment Testing (10 minutes)

#### Test 11: Payment Creation
**Prerequisites:** Order must be 'Approved'

- [ ] Check payment record exists
- [ ] Verify: Payment.Status = 'Pending'
- [ ] Verify: Payment.Amount = Order.TotalAmount

**Quick DB Check:**
```sql
SELECT p.*, o.TotalAmount 
FROM payment p
JOIN `order` o ON p.OrderID = o.OrderID
WHERE p.OrderID = [Test_Order_ID];
```

#### Test 12: Receipt Upload
- [ ] Customer uploads payment receipt
- [ ] Verify file saved
- [ ] Check database: ReceiptPath set

**Quick DB Check:**
```sql
SELECT ReceiptPath, Status FROM payment WHERE OrderID = [Test_Order_ID];
```

#### Test 13: Payment Verification
- [ ] Sales rep marks payment as paid
- [ ] Verify: Payment.Status = 'Paid'
- [ ] Verify: Order.PaymentStatus = 'Paid'

**Quick DB Check:**
```sql
SELECT p.Status, o.PaymentStatus 
FROM payment p
JOIN `order` o ON p.OrderID = o.OrderID
WHERE p.OrderID = [Test_Order_ID];
```

---

### Integration Test (15 minutes)

#### Test 14: Complete Flow
1. [ ] Customer places order → Status: 'Pending Review'
2. [ ] Sales rep requests approval → Status: 'Awaiting Admin'
3. [ ] Admin approves → Status: 'Ready to Approve', AdminStatus: 'Approved'
4. [ ] Sales rep final approves → Status: 'Approved', Payment created
5. [ ] Customer uploads receipt → ReceiptPath saved
6. [ ] Sales rep verifies payment → PaymentStatus: 'Paid'

**Quick DB Check After Each Step:**
```sql
-- Check current status
SELECT OrderID, OrderNumber, Status FROM `order` WHERE OrderID = [Test_Order_ID];

-- Check activity log
SELECT Action, Description, Timestamp 
FROM system_activity_log 
WHERE RelatedID = [Test_Order_ID] 
ORDER BY Timestamp DESC;
```

---

## 🐛 Quick Troubleshooting

### Orders Not Showing?
```sql
-- Check sales rep assignment
SELECT OrderID, SalesRep_ID FROM `order` WHERE OrderID = [Order_ID];
SELECT UserID FROM user WHERE Email = '[Your_Email]';
-- Make sure they match!
```

### Status Not Changing?
- Check browser console (F12) for JavaScript errors
- Check Network tab for AJAX response
- Check PHP error logs: `application/logs/`

### Payment Not Created?
```sql
-- Make sure order is approved first
SELECT Status FROM `order` WHERE OrderID = [Order_ID];
-- Should be 'Approved'
```

---

## 📊 Test Results Summary

**Date:** ___________

| Test | Status | Notes |
|------|--------|-------|
| View Orders | [ ] Pass [ ] Fail | |
| Request Approval | [ ] Pass [ ] Fail | |
| View Details | [ ] Pass [ ] Fail | |
| Final Approve | [ ] Pass [ ] Fail | |
| Admin View | [ ] Pass [ ] Fail | |
| Admin Approve | [ ] Pass [ ] Fail | |
| Admin Disapprove | [ ] Pass [ ] Fail | |
| Error Handling | [ ] Pass [ ] Fail | |
| Payment Creation | [ ] Pass [ ] Fail | |
| Receipt Upload | [ ] Pass [ ] Fail | |
| Payment Verify | [ ] Pass [ ] Fail | |
| Complete Flow | [ ] Pass [ ] Fail | |

**Overall:** [ ] All Pass [ ] Some Fail [ ] Major Issues

**Issues Found:**
1. ___________
2. ___________
3. ___________

---

## ⚡ Quick SQL Queries for Testing

```sql
-- Check order status
SELECT OrderID, OrderNumber, Status, SalesRep_ID FROM `order` WHERE OrderID = ?;

-- Check all related records
SELECT 'order' as table_name, COUNT(*) as count FROM `order` WHERE OrderID = ?
UNION ALL
SELECT 'awaiting_admin', COUNT(*) FROM awaiting_admin_orders WHERE OrderID = ?
UNION ALL
SELECT 'ready_to_approve', COUNT(*) FROM ready_to_approve_orders WHERE OrderID = ?
UNION ALL
SELECT 'approved', COUNT(*) FROM approved_orders WHERE OrderID = ?
UNION ALL
SELECT 'payment', COUNT(*) FROM payment WHERE OrderID = ?;

-- Check recent activity
SELECT Action, Description, Timestamp 
FROM system_activity_log 
WHERE RelatedID = ? 
ORDER BY Timestamp DESC 
LIMIT 5;
```

---

**Total Testing Time:** ~60 minutes for complete test suite

*For detailed procedures, see ORDER_FLOW_TESTING_GUIDE.md*
