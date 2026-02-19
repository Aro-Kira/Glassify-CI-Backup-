# Database Migration Scripts - Order Type and Booking Support

This directory contains SQL migration scripts to add support for Direct Orders and Site Assessment Orders based on the implementation summary.

## Available Scripts

### 1. `add_order_type_and_booking_support.sql` (Safe Version)
- **Recommended for**: Production environments or when you're unsure if columns already exist
- **Features**:
  - Checks if columns exist before adding them
  - Prevents errors if columns already exist
  - Includes verification queries
  - More complex but safer

### 2. `add_order_type_and_booking_support_simple.sql` (Simple Version)
- **Recommended for**: Fresh installations or when you know columns don't exist
- **Features**:
  - Straightforward SQL statements
  - Easy to read and modify
  - Faster to execute
  - Will fail if columns already exist (but easy to fix)

## How to Run

### Option 1: Using phpMyAdmin
1. Open phpMyAdmin
2. Select your database (e.g., `glassify_db`)
3. Click on "SQL" tab
4. Copy and paste the contents of one of the migration scripts
5. Click "Go" to execute

### Option 2: Using MySQL Command Line
```bash
mysql -u your_username -p glassify_db < add_order_type_and_booking_support_simple.sql
```

### Option 3: Using XAMPP MySQL
1. Open XAMPP Control Panel
2. Start MySQL
3. Open Command Prompt
4. Navigate to MySQL bin directory:
   ```
   cd C:\xampp\mysql\bin
   ```
5. Run:
   ```
   mysql.exe -u root -p glassify_db < "C:\xampp 7.4\htdocs\Glassify-CI\database\migrations\add_order_type_and_booking_support_simple.sql"
   ```

### Option 4: Using MySQL Workbench
1. Open MySQL Workbench
2. Connect to your database
3. Open the migration SQL file
4. Execute the script (Ctrl+Shift+Enter)

## What These Scripts Do

### Order Table (`order`)
- ✅ Adds `OrderType` column (ENUM: 'Direct', 'Site-Assessed')
- ✅ Adds `PreferredInstallationDate` column (DATE)
- ✅ Adds index `idx_order_type` on `OrderType`
- ✅ Updates `Status` enum to include new statuses

### Product Table (`product`)
- ✅ Adds `OrderType` column (ENUM: 'direct', 'site-assessment')
- ✅ Adds `PriceMin` column (DECIMAL)
- ✅ Adds `PriceMax` column (DECIMAL)

### Payment Table (`payment`)
- ✅ Verifies `Transaction_ID` column exists (used for PayMongo)

## Before Running

⚠️ **IMPORTANT**: Always backup your database before running migrations!

```sql
-- Create backup
mysqldump -u root -p glassify_db > backup_before_migration.sql
```

## After Running

1. **Verify the changes** by running the verification queries at the bottom of the script
2. **Update your products** to set appropriate values:
   ```sql
   -- Example: Set a product as Site Assessment with price range
   UPDATE `product` 
   SET `OrderType` = 'site-assessment', 
       `PriceMin` = 10000.00, 
       `PriceMax` = 50000.00 
   WHERE `Product_ID` = 1;
   ```
3. **Test the application** to ensure everything works correctly

## Troubleshooting

### Error: "Duplicate column name 'OrderType'"
- **Solution**: The column already exists. Use the safe version script or remove the ADD COLUMN statement for that column.

### Error: "Table doesn't exist"
- **Solution**: Make sure you're connected to the correct database and the table names are correct (case-sensitive in some systems).

### Error: "Unknown column 'Subcategory'"
- **Solution**: In the simple script, change `AFTER `Subcategory`` to `AFTER `Status`` in the product table ALTER statements.

### Error: "Duplicate key name 'idx_order_type'"
- **Solution**: The index already exists. Remove the ADD KEY statement for that index.

## Rollback (If Needed)

If you need to rollback these changes:

```sql
-- Remove columns from order table
ALTER TABLE `order` DROP COLUMN `OrderType`;
ALTER TABLE `order` DROP COLUMN `PreferredInstallationDate`;
ALTER TABLE `order` DROP KEY `idx_order_type`;

-- Remove columns from product table
ALTER TABLE `product` DROP COLUMN `OrderType`;
ALTER TABLE `product` DROP COLUMN `PriceMin`;
ALTER TABLE `product` DROP COLUMN `PriceMax`;

-- Note: You may need to restore the original Status enum values
-- Check your backup for the original enum definition
```

## Verification Checklist

After running the migration, verify:

- [ ] `OrderType` column exists in `order` table
- [ ] `PreferredInstallationDate` column exists in `order` table
- [ ] `idx_order_type` index exists
- [ ] `OrderType` column exists in `product` table
- [ ] `PriceMin` column exists in `product` table
- [ ] `PriceMax` column exists in `product` table
- [ ] `Transaction_ID` column exists in `payment` table
- [ ] Status enum includes all new statuses
- [ ] Existing orders have `OrderType = 'Direct'` by default
- [ ] Existing products have `OrderType = 'direct'` by default

## Support

For more information, see:
- `docs/DATABASE_CHANGES_SUMMARY.md` - Detailed documentation of all changes
- `docs/IMPLEMENTATION_SUMMARY.md` - Complete implementation details
