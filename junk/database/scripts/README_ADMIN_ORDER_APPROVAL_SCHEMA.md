# Admin Order Approval System - Database Schema Installation Guide

This document explains how to install the database schema for the Admin Order Approval System.

## Files Included

1. **`admin_order_approval_schema.sql`** - For MySQL 8.0+ (supports `ADD COLUMN IF NOT EXISTS`)
2. **`admin_order_approval_schema_mysql57.sql`** - For MySQL 5.7 (does not support `ADD COLUMN IF NOT EXISTS`)

## Installation Instructions

### For MySQL 8.0 or MariaDB 10.3.4+

```bash
mysql -u your_username -p your_database_name < admin_order_approval_schema.sql
```

### For MySQL 5.7 or older versions

**Option 1: Manual Column Check (Recommended)**
1. First, check if the columns already exist in the `order` table:
   ```sql
   DESCRIBE `order`;
   ```
2. If the columns don't exist, run:
   ```bash
   mysql -u your_username -p your_database_name < admin_order_approval_schema_mysql57.sql
   ```

**Option 2: Safe Installation Script**
If columns might already exist, use this approach:
```sql
-- Check if column exists before adding
SET @dbname = DATABASE();
SET @tablename = "order";
SET @columnname = "ApprovedBy_Admin_ID";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column already exists.'",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT(11) NULL DEFAULT NULL COMMENT 'ID of admin who approved the order';")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
```

Repeat this for each column that needs to be added.

## What Gets Created/Modified

### 1. Modified Tables

#### `order` table
Adds the following columns:
- `ApprovedBy_Admin_ID` (INT) - ID of admin who approved
- `Approved_Date` (DATETIME) - Approval timestamp
- `DisapprovedBy` (VARCHAR) - Who disapproved (Admin/Sales Rep)
- `DisapprovedBy_ID` (INT) - ID of person who disapproved
- `DisapprovalReason` (TEXT) - Reason for disapproval
- `Disapproved_Date` (DATETIME) - Disapproval timestamp

Adds indexes for performance:
- `idx_order_status`
- `idx_order_approved_by_admin`
- `idx_order_disapproved_by`

### 2. New Tables Created

#### `system_activity_log` (Required)
Logs all system activities including approvals and disapprovals.

#### `awaiting_admin_orders` (Legacy - Optional)
Legacy table for backward compatibility. Contains orders awaiting admin review.

#### `disapproved_orders` (Legacy - Optional)
Legacy table for backward compatibility. Contains disapproved orders.

#### `payment` (Required if not exists)
Payment records table. Created automatically when orders are approved.

## Required Order Status Values

Ensure your `order` table's `Status` column supports these values:
- `Pending Review`
- `Awaiting Admin`
- `Approved`
- `Disapproved`
- `In Fabrication`
- `Ready for Installation`
- `Completed`
- `Cancelled`

## Verification

After installation, verify the schema:

```sql
-- Check order table columns
DESCRIBE `order`;

-- Check if new tables exist
SHOW TABLES LIKE 'system_activity_log';
SHOW TABLES LIKE 'awaiting_admin_orders';
SHOW TABLES LIKE 'disapproved_orders';
SHOW TABLES LIKE 'payment';

-- Verify indexes
SHOW INDEX FROM `order` WHERE Key_name LIKE 'idx_order_%';
```

## Troubleshooting

### Error: "Duplicate column name"
This means the column already exists. You can either:
1. Skip that column (if using MySQL 5.7, you'll need to manually remove duplicate ALTER statements)
2. Use the safe installation script provided above

### Error: "Table already exists"
This is fine - the CREATE TABLE IF NOT EXISTS statements will not recreate existing tables.

### Error: "Unknown column 'Status' in 'field list'"
Make sure your `order` table has a `Status` column. If not, you may need to add it first.

## Rollback (If Needed)

If you need to rollback the changes:

```sql
-- Remove columns from order table
ALTER TABLE `order`
DROP COLUMN IF EXISTS `ApprovedBy_Admin_ID`,
DROP COLUMN IF EXISTS `Approved_Date`,
DROP COLUMN IF EXISTS `DisapprovedBy`,
DROP COLUMN IF EXISTS `DisapprovedBy_ID`,
DROP COLUMN IF EXISTS `DisapprovalReason`,
DROP COLUMN IF EXISTS `Disapproved_Date`;

-- Drop indexes
DROP INDEX IF EXISTS `idx_order_status` ON `order`;
DROP INDEX IF EXISTS `idx_order_approved_by_admin` ON `order`;
DROP INDEX IF EXISTS `idx_order_disapproved_by` ON `order`;

-- Drop tables (only if you want to remove them completely)
-- DROP TABLE IF EXISTS `system_activity_log`;
-- DROP TABLE IF EXISTS `awaiting_admin_orders`;
-- DROP TABLE IF EXISTS `disapproved_orders`;
-- Note: Don't drop payment table if it's being used by the system
```

## Support

For questions or issues, refer to:
- `docs/ADMIN_ORDER_APPROVAL_DOCUMENTATION.md` - Full documentation
- Check application logs for any database-related errors
