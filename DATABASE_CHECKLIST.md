# Database Verification Checklist

Use this checklist to verify what tables and fields exist in your phpMyAdmin database.

## Quick Verification Queries

Run these queries in phpMyAdmin to check your database:

### 1. Check All Tables
```sql
SHOW TABLES;
```
**Expected:** 26 tables (see list below)

### 2. Check Order Table Fields
```sql
SHOW COLUMNS FROM `order`;
```
**Look for these fields:**
- ✅ `OrderNumber` (varchar)
- ✅ `PreferredInstallationDate` (date)
- ✅ `OcularDate` (date)
- ✅ `FabricationDate` (date)
- ✅ `InstallationDate` (date)
- ✅ `EstimatedDelivery` (date)

### 3. Check Appointments Table Fields
```sql
SHOW COLUMNS FROM `appointments`;
```
**Look for:**
- ✅ `AssignedStaff_ID` (int)

### 4. Check Legacy Order Tables
```sql
SHOW TABLES LIKE '%_orders';
```
**Expected tables:**
- ✅ `pending_review_orders`
- ✅ `awaiting_admin_orders`
- ✅ `ready_to_approve_orders`
- ✅ `approved_orders`
- ✅ `disapproved_orders`

---

## Complete Table List (26 tables)

### Core Tables (Check ✓)
- [ ] `user`
- [ ] `customer`
- [ ] `user_address`

### Product & Inventory (Check ✓)
- [ ] `product`
- [ ] `inventory_items`
- [ ] `product_materials`
- [ ] `stock_transactions`
- [ ] `activities`
- [ ] `inventory_notifications`

### Customization (Check ✓)
- [ ] `customization`

### Shopping (Check ✓)
- [ ] `cart`
- [ ] `wishlist`

### Order Management (Check ✓)
- [ ] `order`
- [ ] `order_items`
- [ ] `payment`
- [ ] `quotation`

### Legacy Order Status Tables (Check ✓)
- [ ] `pending_review_orders`
- [ ] `awaiting_admin_orders`
- [ ] `ready_to_approve_orders`
- [ ] `approved_orders`
- [ ] `disapproved_orders`

### Appointments & Scheduling (Check ✓)
- [ ] `appointments`
- [ ] `projectschedule`

### Issue Management (Check ✓)
- [ ] `issuereport`

### Notifications & Logs (Check ✓)
- [ ] `sales_notif`
- [ ] `system_activity_log`

---

## Missing Items Summary

If any items above are unchecked, you need to:

1. **Missing Tables:** Run the `update_database_to_latest.sql` script
2. **Missing Fields:** Run the ALTER TABLE statements from the update script
3. **Missing Foreign Keys:** Check constraints and add if needed

---

## Next Steps

1. ✅ Run verification queries above
2. ✅ Check off items in the checklist
3. ✅ Run `update_database_to_latest.sql` for missing items
4. ✅ Re-verify after updates
5. ✅ Test your application

---

## Files Reference

- **Update Script:** `update_database_to_latest.sql`
- **Detailed Report:** `DATABASE_COMPARISON_REPORT.md`
- **Latest Database:** `latest_glassifydb.sql`

