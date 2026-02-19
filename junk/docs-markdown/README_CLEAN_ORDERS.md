# Cleaning Orders - Safe Procedure

This document explains how to safely back up and clean orders for a fresh start.

WARNING: The cleanup is destructive and irreversible. Back up before running anything.

Recommended steps (Windows / local XAMPP):

1) Backup the entire database (recommended):

```powershell
REM Run in Command Prompt or PowerShell (adjust paths and credentials)
mysqldump -h localhost -u YOUR_DB_USER -p YOUR_DB_NAME > backup_full_db.sql
```

Or to dump only the database (recommended):

```powershell
mysqldump -h localhost -u YOUR_DB_USER -p YOUR_DB_NAME > "%CD%\\tools\\backup_full_db.sql"
```

2) Option A — Run the provided PHP cleanup script (safe & opinionated):

- From CLI (preferred):

```powershell
cd tools
php clear_orders.php --confirm
```

- From your browser (if running the app locally):

```
http://localhost/Glassify-CI/tools/clear_orders.php?confirm=yes
```

This script will attempt to delete/truncate known order-related tables and reset the `order` auto-increment. It will also disable/enable foreign key checks during the operation.

3) Option B — Run a custom SQL script (advanced):

If you prefer SQL-only cleanup, create a dump first, then run your SQL to TRUNCATE/DELETE specific tables. Be sure to disable foreign key checks temporarily:

```sql
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `order`;
TRUNCATE TABLE orderitem;
TRUNCATE TABLE order_items;
TRUNCATE TABLE payment;
TRUNCATE TABLE appointments;
TRUNCATE TABLE cart;
TRUNCATE TABLE installation_date_change_requests;
TRUNCATE TABLE awaiting_admin_orders;
-- remove or modify other tables as needed
SET FOREIGN_KEY_CHECKS = 1;
```

4) Verify the site after cleanup: log in as admin, check sample customers/orders, and run smoke tests.

If you want, I can:
- Create a small Windows batch helper that runs `mysqldump` (you supply credentials) then executes `php clear_orders.php --confirm`.
- Modify `clear_orders.php` to list exact SQL table names found in your DB before executing (extra confirmation).

Which of the above would you like me to add or run (I can only create scripts and instructions here; I cannot run them on your machine)?
