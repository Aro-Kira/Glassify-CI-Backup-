# Database Comparison Report
## Latest Database Analysis - Missing Tables & Fields

**Generated:** December 2025  
**Latest Database File:** `latest_glassifydb.sql` (Dec 07, 2025 at 07:55 PM)  
**Optimized Schema:** `super-latest-optimized.sql`

---

## 📊 Summary

The **`latest_glassifydb.sql`** appears to be the most recent database dump. This report identifies tables and fields that may be missing in your phpMyAdmin database.

---

## ✅ All Tables in Latest Database (26 tables)

### Core User Management
1. ✅ `user` - Core user accounts
2. ✅ `customer` - Links UserID to Customer_ID
3. ✅ `user_address` - User address management

### Product & Inventory
4. ✅ `product` - Product catalog
5. ✅ `inventory_items` - Raw materials inventory
6. ✅ `product_materials` - Product-to-material mapping
7. ✅ `stock_transactions` - Inventory transaction log
8. ✅ `activities` - Inventory activity log
9. ✅ `inventory_notifications` - Low stock alerts

### Customization
10. ✅ `customization` - Unified customization table

### Shopping
11. ✅ `cart` - Shopping cart
12. ✅ `wishlist` - Customer wishlist

### Order Management
13. ✅ `order` - Main order table (unified)
14. ✅ `order_items` - Order line items
15. ✅ `payment` - Payment records
16. ✅ `quotation` - Quotation records

### Legacy Order Status Tables (for backward compatibility)
17. ✅ `pending_review_orders` - Orders with Status = 'Pending Review'
18. ✅ `awaiting_admin_orders` - Orders with Status = 'Awaiting Admin'
19. ✅ `ready_to_approve_orders` - Orders awaiting sales rep final approval
20. ✅ `approved_orders` - Orders with Status = 'Approved'
21. ✅ `disapproved_orders` - Orders with Status = 'Disapproved'

### Appointments & Scheduling
22. ✅ `appointments` - Installation/service scheduling
23. ✅ `projectschedule` - Project scheduling

### Issue Management
24. ✅ `issuereport` - Customer issue reports

### Notifications & Logs
25. ✅ `sales_notif` - Sales representative notifications
26. ✅ `system_activity_log` - System-wide activity log

---

## 🔍 Key Differences & Missing Fields

### 1. `order` Table - Additional Fields in Latest Version

The `latest_glassifydb.sql` has these additional fields that may be missing:

```sql
-- These fields are in latest_glassifydb.sql but may be missing in your database:
`PreferredInstallationDate` date DEFAULT NULL COMMENT 'Customer preferred installation date (captured at checkout)',
`OcularDate` date DEFAULT NULL COMMENT 'Scheduled date for ocular visit',
`FabricationDate` date DEFAULT NULL COMMENT 'Scheduled date for fabrication',
`InstallationDate` date DEFAULT NULL COMMENT 'Scheduled date for installation',
`EstimatedDelivery` date DEFAULT NULL COMMENT 'Estimated delivery/completion date',
```

**SQL to Add Missing Fields:**
```sql
ALTER TABLE `order` 
ADD COLUMN `PreferredInstallationDate` date DEFAULT NULL COMMENT 'Customer preferred installation date (captured at checkout)' AFTER `CustomerNotified_Date`,
ADD COLUMN `OcularDate` date DEFAULT NULL COMMENT 'Scheduled date for ocular visit' AFTER `PreferredInstallationDate`,
ADD COLUMN `FabricationDate` date DEFAULT NULL COMMENT 'Scheduled date for fabrication' AFTER `OcularDate`,
ADD COLUMN `InstallationDate` date DEFAULT NULL COMMENT 'Scheduled date for installation' AFTER `FabricationDate`,
ADD COLUMN `EstimatedDelivery` date DEFAULT NULL COMMENT 'Estimated delivery/completion date' AFTER `InstallationDate`;
```

### 2. `appointments` Table - Additional Field

The latest version has `AssignedStaff_ID` field:

```sql
-- This field may be missing:
`AssignedStaff_ID` int(11) DEFAULT NULL,
```

**SQL to Add Missing Field:**
```sql
ALTER TABLE `appointments` 
ADD COLUMN `AssignedStaff_ID` int(11) DEFAULT NULL AFTER `AssignedStaff`,
ADD KEY `idx_staff` (`AssignedStaff_ID`),
ADD CONSTRAINT `fk_appointments_staff` FOREIGN KEY (`AssignedStaff_ID`) REFERENCES `user` (`UserID`) ON DELETE SET NULL;
```

### 3. `order` Table - OrderNumber Field

Ensure the `order` table has the `OrderNumber` field:

```sql
-- Verify this field exists:
`OrderNumber` varchar(50) NOT NULL COMMENT 'Formatted: GI001, GI002, etc.',
```

**SQL to Add if Missing:**
```sql
ALTER TABLE `order` 
ADD COLUMN `OrderNumber` varchar(50) NOT NULL COMMENT 'Formatted: GI001, GI002, etc.' AFTER `OrderID`,
ADD UNIQUE KEY `OrderNumber` (`OrderNumber`);
```

### 4. Legacy Order Status Tables

These tables are kept for backward compatibility. If missing, they should be created:

- `pending_review_orders`
- `awaiting_admin_orders`
- `approved_orders`
- `disapproved_orders`

---

## 📝 Complete SQL Script to Update Your Database

Create a file with this SQL to update your database:

```sql
-- =====================================================
-- Database Update Script
-- Add missing fields and tables from latest_glassifydb.sql
-- =====================================================

-- 1. Add missing fields to order table
ALTER TABLE `order` 
ADD COLUMN IF NOT EXISTS `PreferredInstallationDate` date DEFAULT NULL COMMENT 'Customer preferred installation date (captured at checkout)' AFTER `CustomerNotified_Date`,
ADD COLUMN IF NOT EXISTS `OcularDate` date DEFAULT NULL COMMENT 'Scheduled date for ocular visit' AFTER `PreferredInstallationDate`,
ADD COLUMN IF NOT EXISTS `FabricationDate` date DEFAULT NULL COMMENT 'Scheduled date for fabrication' AFTER `OcularDate`,
ADD COLUMN IF NOT EXISTS `InstallationDate` date DEFAULT NULL COMMENT 'Scheduled date for installation' AFTER `FabricationDate`,
ADD COLUMN IF NOT EXISTS `EstimatedDelivery` date DEFAULT NULL COMMENT 'Estimated delivery/completion date' AFTER `InstallationDate`;

-- 2. Add missing field to appointments table
ALTER TABLE `appointments` 
ADD COLUMN IF NOT EXISTS `AssignedStaff_ID` int(11) DEFAULT NULL AFTER `AssignedStaff`;

-- Add index and foreign key for AssignedStaff_ID
ALTER TABLE `appointments`
ADD KEY IF NOT EXISTS `idx_staff` (`AssignedStaff_ID`);

-- Note: Foreign key constraint will be added if it doesn't exist
-- You may need to check if the constraint already exists before adding

-- 3. Verify OrderNumber field exists in order table
-- (Check manually - if missing, uncomment below)
-- ALTER TABLE `order` 
-- ADD COLUMN IF NOT EXISTS `OrderNumber` varchar(50) NOT NULL COMMENT 'Formatted: GI001, GI002, etc.' AFTER `OrderID`,
-- ADD UNIQUE KEY IF NOT EXISTS `OrderNumber` (`OrderNumber`);
```

---

## 🔧 How to Check Your Current Database

Run these queries in phpMyAdmin to check what's missing:

```sql
-- Check if order table has the new date fields
SHOW COLUMNS FROM `order` LIKE '%Date%';

-- Check if appointments table has AssignedStaff_ID
SHOW COLUMNS FROM `appointments` LIKE 'AssignedStaff_ID';

-- List all tables in your database
SHOW TABLES;

-- Check if legacy order status tables exist
SHOW TABLES LIKE '%_orders';
```

---

## 📋 Recommended Action Plan

1. **Backup your current database** before making any changes
2. **Compare your tables** with the list above
3. **Run the SQL update script** to add missing fields
4. **Verify foreign key constraints** are properly set up
5. **Test your application** after making changes

---

## 📁 File References

- **Latest Database Dump:** `latest_glassifydb.sql` (Dec 07, 2025 at 07:55 PM)
- **Optimized Schema:** `super-latest-optimized.sql`
- **Migration Files:** `database_migrations/` folder

---

## ⚠️ Important Notes

1. The legacy order status tables (`pending_review_orders`, `awaiting_admin_orders`, etc.) are kept for backward compatibility but the main `order` table should be the primary source of truth.

2. The `order` table now includes all order statuses in a single `Status` enum field, eliminating the need for multiple status tables.

3. Always backup your database before running any ALTER TABLE statements.

4. Some MySQL/MariaDB versions may not support `IF NOT EXISTS` in ALTER TABLE. In that case, check for column existence first or use conditional logic in your application.

