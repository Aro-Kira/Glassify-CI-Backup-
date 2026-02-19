# Junk Files Inventory
**Date Generated:** February 19, 2026

This document lists all identified testing, debugging, and temporary files that are not part of the core system.

---

## 📁 Testing Files (`/tests` folder)

### PHP Test Files
- `db_connect_test.php` - Database connection testing
- `db_query_appointments.php` - Database query testing for appointments
- `db_query_order.php` - Database query testing for orders
- `db_query_order_items.php` - Database query testing for order items
- `db_show_columns.php` - Database column inspection
- `debug_appointments.php` - Appointment debugging script
- `debug_orders.php` - Order debugging script
- `debug_urls.php` - URL debugging
- `fix_all_appointments.php` - Appointment fixing script
- `fix_appointments_web.php` - Web-based appointment fixing
- `fix_appointment_dates_web.php` - Appointment date fixing
- `restore_orders_backup.php` - Order backup restoration
- `run_sql.php` - SQL execution utility
- `safe_delete_orders.php` - Safe order deletion script
- `ShopConBillingTest.php` - Billing system testing
- `test_ajax_call.html` - AJAX call testing
- `test_appointment_update.php` - Appointment update testing
- `test_database_setup.php` - Database setup verification
- `test_mirrors_api.php` - Mirror API testing
- `test_network_access.php` - Network access testing
- `test_orders_api.php` - Orders API testing

### Text Files (Test Output)
- `debug_output.txt` - Debug output logs
- `production_served_numbered.txt` - Production served data (numbered format)
- `production_served_after_numbered.txt` - Production served data (after processing)
- `temp_lines.txt` - Temporary line data

### Markdown Documentation (Tests)
- `IMPLEMENTATION_SUMMARY.md` - Implementation summary notes
- `LOCAL_CHANGES_SUMMARY.md` - Local changes documentation
- `PAGE_CHANGES_SUMMARY.md` - Page-level changes summary
- `CURRENT_CHANGES_SUMMARY_2026_01_22.md` - Changes summary from 2026-01-22
- `run_populate_script.bat` - Batch script for population

### Other Test Files
- `start` - Script file (unclear purpose)

---

## 🔧 Scripts & Tools (`/scripts` folder)

### JavaScript Debug/Test Files
- `production_served.js` - Production debugging script
- `production_served_v.js` - Production debugging script (version)
- `production_served_after_fix.js` - Production debugging after fix

### PHP Utility & Testing Scripts
- `backfill_order_7_backup.sql` - SQL backup for order backfill
- `backfill_order_items.php` - Order items backfill
- `debug_get_order.php` - Order retrieval debugging
- `populate_customization_defaults.php` - Customization defaults setup
- `populate_tag_prices.php` - Tag prices population
- `simulate_update_fab.php` - FAB update simulation
- `test_ajax_customization.php` - AJAX customization testing
- `test_api.php` - API testing
- `test_api_windows_defaults.php` - Windows defaults API testing
- `test_customer_ajax_2dmodel.php` - Customer AJAX 2D model testing
- `test_windows_defaults.php` - Windows defaults testing
- `test_windows_visual_preview.php` - Windows visual preview testing
- `update_payment_enum.php` - Payment enum update

### Python Debug Scripts
- `check_js_balance.py` - JavaScript balance checking
- `inspect_line83.py` - Line 83 inspection

### Batch Files
- `export_database.bat` - Database export
- `get_my_ip.bat` - IP address retrieval
- `quick_merge.bat` - Quick merge utility

### PowerShell Scripts
- `merge_inventory_product_features.ps1` - Inventory/product feature merging

### SQL Files
- `populate_tag_prices.sql` - SQL for tag prices

### Subdirectories
- `php/` - PHP utilities folder

---

## 🛠️ Tools Folder (`/tools`)

### PHP Tools
- `check_appointment_dates.php` - Appointment date checking
- `check_customization_column.php` - Customization column checking
- `check_fab_payment_columns.php` - FAB payment columns checking
- `check_now.php` - General checking utility
- `check_payment_enum.php` - Payment enum checking
- `check_tables.php` - Table structure checking
- `clean_orders.php` - Order cleaning utility
- `clear_orders.php` - Order clearing utility
- `fix_appointment_dates.php` - Appointment date fixing
- `run_updates.php` - Update execution utility
- `temp_2DModeling.php` - Temporary 2D modeling file

### JavaScript Files
- `temp_2d_customization.js` - Temporary 2D customization script
- `temp_dynamic_customization.js` - Temporary dynamic customization script

### CSS Files
- `temp_2DModeling_styles.css` - Temporary 2D modeling styles

### Batch Files
- `run_clear_with_backup.bat` - Backup and clear utility

### Documentation
- `README_CLEAN_ORDERS.md` - Documentation for order cleaning

---

## 📂 Junk Folder (`/junk`)

### Subdirectories
- `archive-logs/` - Archived logs
- `temp-images/` - Temporary images
- `test-files/` - Test files directory

---

## 📊 Summary Statistics

| Category | Count |
|----------|-------|
| PHP Test Files | 20 |
| Text Debug Files | 4 |
| Test Markdown Docs | 4 |
| JavaScript Debug Files | 3 |
| PHP Utility Scripts | 11 |
| Python Scripts | 2 |
| Batch Scripts | 3 |
| PowerShell Scripts | 1 |
| SQL Scripts | 2 |
| CSS Files | 1 |
| Subdirectories | 7 |
| **Total Items** | **58** |

---

## ⚠️ Recommendation

These files should be considered for:
- **Archive**: Moving to a proper backup/archive location
- **Deletion**: Removing if no longer needed (after code review)
- **Organization**: Consolidating into a dedicated debug/test directory if needed again
- **Version Control**: Excluding from production deployments

Before deletion, verify:
1. No active references to these scripts
2. Critical data backed up
3. Approval from development team
