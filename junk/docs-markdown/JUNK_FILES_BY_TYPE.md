# Junk Files by Type
**Date Generated:** February 19, 2026

Detailed categorization of all testing, debugging, and temporary files organized by file extension.

---

## 📄 SQL Files (Testing & Migration)

### Location: `/database` folder
- `scripts/add_order_date_columns_simple.sql` - Simple order date column addition
- `scripts/add_order_date_columns_mysql.sql` - MySQL-specific order date columns
- `scripts/FIX_ADDRESS_TABLE_SIMPLE.sql` - Simple address table fix
- `scripts/FIX_ADDRESS_TABLE.sql` - Address table fix
- `scripts/delete_user_6_working.sql` - User deletion working script
- `scripts/delete_user_6_simple.sql` - Simple user deletion
- `scripts/delete_user_6_fixed.sql` - Fixed user deletion
- `scripts/delete_user_6.sql` - Basic user deletion
- `scripts/create_ready_to_approve_table.sql` - Table creation for approval workflow
- `scripts/check_order_data.sql` - Order data validation
- `scripts/admin_order_approval_schema_mysql57.sql` - Admin approval schema for MySQL 5.7
- `scripts/fix_empty_order_status.sql` - Order status correction

### Location: `/database/migrations` folder
- `20260212_add_comment_to_role_requests.sql` - Migration for role request comments
- `20260211_fix_user_role_constraints.sql` - Migration for user role constraints
- `20260211_create_role_requests.sql` - Migration creating role requests table

### Location: `/scripts` folder
- `backfill_order_7_backup.sql` - Backup SQL for order backfill
- `populate_tag_prices.sql` - Tag price population

### Location: `/docs` folder
- `database-schema-admin-order-management.sql` - Admin order management schema documentation

**Total SQL Files: 21**

---

## 📝 Text Files (Debug Output)

### Location: `/tests` folder
- `debug_output.txt` - General debugging output
- `production_served_numbered.txt` - Numbered production output log
- `production_served_after_numbered.txt` - Processed production output
- `temp_lines.txt` - Temporary line data

### Location: Root
- `license.txt` - License file (keep)

**Total TXT Files: 5** (1 production file, 4 test/debug)

---

## 📖 Markdown Files (Documentation & Test Logs)

### Location: `/docs` folder (57 total - mostly production docs)
**Sample files:**
- `CHANGES_SUMMARY_2026_02_08.md` - Change summary
- `CHANGES_SUMMARY_2026_01_22.md` - Change summary (older)
- `ARCHITECTURE_SUMMARY.md` - Architecture documentation
- `COMPREHENSIVE_2D_RENDERER_SUMMARY.md` - 2D renderer guide
- `DATABASE_SCHEMA_DOCUMENTATION.md` - Database documentation
- `INSTALLATION_PAYMENT_WORKFLOW.md` - Payment workflow docs
- *and 51 other documentation files*

### Location: `/tests` folder (Test Documentation)
- `IMPLEMENTATION_SUMMARY.md` - Testing implementation notes
- `LOCAL_CHANGES_SUMMARY.md` - Local environment changes
- `PAGE_CHANGES_SUMMARY.md` - Page-specific changes
- `CURRENT_CHANGES_SUMMARY_2026_01_22.md` - Dated change summary

### Location: `/tools` folder
- `README_CLEAN_ORDERS.md` - Order cleaning documentation

**Total MD Files: 75** (4 test, 71 production/docs)

---

## 🔨 JavaScript Files (Debug & Testing)

### Location: `/scripts` folder (Debug Scripts)
- `production_served.js` - Production debugging output
- `production_served_v.js` - Production debugging (version variant)
- `production_served_after_fix.js` - Production debugging after fix

### Location: `/tools` folder (Temporary)
- `temp_2d_customization.js` - Temporary 2D customization
- `temp_dynamic_customization.js` - Temporary dynamic customization

### Location: `/assets/js` folder (79 total - mostly production)
**Sample production files:**
- `auth.js` - Authentication
- `cart.js` - Shopping cart
- `calendar.js` - Calendar functionality
- `konva.min.js` - Konva graphics library (minified)
- `2d-functions/buy-now-handler.js` - Purchase handler
- *and 74 other production JS files*

**Total JS Files: 79** (5 test/debug, 74 production)

---

## 🐍 Python Files (Debug/Analysis)

### Location: `/scripts` folder
- `check_js_balance.py` - JavaScript balance verification
- `inspect_line83.py` - Line-specific code inspection

**Total Python Files: 2**

---

## ⚙️ PHP Files (Testing & Utilities)

### Location: `/tests` folder (Testing)
- `db_connect_test.php` - Database connection test
- `db_query_appointments.php` - Appointment query test
- `db_query_order.php` - Order query test
- `db_query_order_items.php` - Order items query test
- `db_show_columns.php` - Column inspection
- `debug_appointments.php` - Appointment debugging
- `debug_orders.php` - Order debugging
- `debug_urls.php` - URL debugging
- `fix_all_appointments.php` - Appointment fixing
- `fix_appointments_web.php` - Web appointment fixing
- `fix_appointment_dates_web.php` - Appointment date web fixing
- `restore_orders_backup.php` - Restore backup
- `run_sql.php` - SQL execution
- `safe_delete_orders.php` - Safe deletion utility
- `ShopConBillingTest.php` - Billing test
- `test_ajax_call.html` - AJAX test (HTML)
- `test_appointment_update.php` - Update test
- `test_database_setup.php` - Setup verification
- `test_mirrors_api.php` - API test
- `test_network_access.php` - Network test
- `test_orders_api.php` - Orders API test

### Location: `/scripts` folder (Utilities)
- `backfill_order_items.php` - Backfill utility
- `debug_get_order.php` - Order retrieval debug
- `populate_customization_defaults.php` - Defaults setup
- `populate_tag_prices.php` - Tag prices setup
- `simulate_update_fab.php` - Update simulation
- `test_ajax_customization.php` - AJAX customization test
- `test_api.php` - API test
- `test_api_windows_defaults.php` - Windows API test
- `test_customer_ajax_2dmodel.php` - Customer 2D model test
- `test_windows_defaults.php` - Windows defaults test
- `test_windows_visual_preview.php` - Visual preview test
- `update_payment_enum.php` - Enum update

### Location: `/tools` folder (Tools)
- `check_appointment_dates.php` - Date checking
- `check_customization_column.php` - Column checking
- `check_fab_payment_columns.php` - FAB columns checking
- `check_now.php` - General checking
- `check_payment_enum.php` - Enum checking
- `check_tables.php` - Table structure checking
- `clean_orders.php` - Order cleaning
- `clear_orders.php` - Order clearing
- `fix_appointment_dates.php` - Date fixing
- `run_updates.php` - Update execution
- `temp_2DModeling.php` - Temporary 2D modeling

**Total PHP Files: 52**

---

## 🎨 CSS Files (Temporary)

### Location: `/tools` folder
- `temp_2DModeling_styles.css` - Temporary styling for 2D modeling

**Total CSS Files: 1**

---

## 🚀 Batch/Shell Files (Automation)

### Location: `/scripts` folder
- `export_database.bat` - Database export automation
- `get_my_ip.bat` - IP retrieval
- `quick_merge.bat` - Merge automation

### Location: `/tests` folder
- `run_populate_script.bat` - Population script execution

### Location: `/tools` folder
- `run_clear_with_backup.bat` - Backup and clear automation

**Total Batch Files: 5**

---

## 💻 PowerShell Files (Automation)

### Location: `/scripts` folder
- `merge_inventory_product_features.ps1` - Inventory feature merging

**Total PowerShell Files: 1**

---

## 📊 File Type Summary

| File Type | Debug/Test | Production | Total |
|-----------|-----------|-----------|-------|
| .sql | 21 | 0 | 21 |
| .txt | 4 | 1 | 5 |
| .md | 4 | 71 | 75 |
| .js | 5 | 74 | 79 |
| .php | 52 | 0 | 52 |
| .py | 2 | 0 | 2 |
| .bat | 5 | 0 | 5 |
| .ps1 | 1 | 0 | 1 |
| .css | 1 | 0 | 1 |
| **TOTAL** | **95** | **146** | **241** |

---

## 🗂️ Files by Folder

| Folder | Debug Files | Purpose |
|--------|------------|---------|
| `/tests` | 25+ | Unit & integration testing |
| `/scripts` | 20+ | Database & data migrations |
| `/tools` | 15+ | Maintenance & checking utilities |
| `/junk` | 3 subdirs | Archived & temporary items |
| `/database` | 12+ | Migration & schema fixes |
| `/docs` | 4 | Test documentation |

---

## 🎯 Recommended Actions

### High Priority (Remove/Archive)
- All files in `/junk` folder
- Debug output text files in `/tests`
- Test PHP files in `/tests` (20+ files)

### Medium Priority (Review)
- Database scripts in `/database/scripts` (migration/fix files)
- Debug JavaScript files in `/scripts`
- Temporary files (temp_*.*)

### Low Priority (Archive if space concerns)
- Documentation in `/docs` (thorough - may be needed for reference)
- Python inspection scripts (rarely used but useful for debugging)
