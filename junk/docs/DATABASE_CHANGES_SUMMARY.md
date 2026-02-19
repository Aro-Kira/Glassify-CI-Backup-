# Database Changes Summary - Implementation Summary

Based on the IMPLEMENTATION_SUMMARY.md, here are all the database changes made to support the Direct Order and Site Assessment Order flow, booking system, and PayMongo payment integration.

---

## Table of Contents
1. [Order Table Changes](#order-table-changes)
2. [Payment Table Changes](#payment-table-changes)
3. [Product Table Changes](#product-table-changes)
4. [Status Enum Updates](#status-enum-updates)
5. [Indexes Added](#indexes-added)
6. [Migration Scripts](#migration-scripts)

---

## Order Table Changes

### New Columns Added

#### 1. `OrderType` Column
- **Type**: `ENUM('Direct','Site-Assessed')`
- **Default**: `'Direct'`
- **Comment**: `'Order type: Direct or Site-Assessed'`
- **Purpose**: Distinguishes between Direct Orders (standard orders that proceed directly to payment) and Site Assessment Orders (orders that require an ocular visit before final pricing)
- **Location**: Added after `CustomerNotified_Date` field

**SQL Example:**
```sql
ALTER TABLE `order` 
ADD COLUMN `OrderType` enum('Direct','Site-Assessed') DEFAULT 'Direct' 
COMMENT 'Order type: Direct or Site-Assessed' 
AFTER `CustomerNotified_Date`;
```

#### 2. `PreferredInstallationDate` Column
- **Type**: `DATE`
- **Default**: `NULL`
- **Comment**: `'Customer preferred installation date (captured at checkout)'`
- **Purpose**: Stores the preferred ocular visit date for Site Assessment orders (captured during booking)
- **Location**: Added after `CustomerNotified_Date` field

**SQL Example:**
```sql
ALTER TABLE `order` 
ADD COLUMN `PreferredInstallationDate` date DEFAULT NULL 
COMMENT 'Customer preferred installation date (captured at checkout)' 
AFTER `CustomerNotified_Date`;
```

### Existing Columns Usage

#### `Status` Column
- **Existing Type**: `ENUM(...)`
- **New Status Values Added** (based on implementation):
  - `'Pending Booking Confirmation'` - For Site Assessment orders awaiting admin confirmation
  - `'Quotation Available'` - When quotation is ready for customer review
  - `'Awaiting Payment'` - After quotation acceptance, waiting for payment
  - `'Booking Confirmed'` - Site Assessment booking confirmed by admin
  - `'Ocular Visit Completed'` - After ocular visit is done
  - `'Installation Completed'` - Installation finished (may have balance due)

**Note**: The actual enum values may vary. Check your current schema to see which statuses are actually implemented.

---

## Payment Table Changes

### Existing Column Usage

#### `Transaction_ID` Column
- **Type**: `VARCHAR(100)`
- **Default**: `NULL`
- **Purpose**: **REPURPOSED** to store PayMongo `payment_intent_id`
- **Previous Use**: Likely stored transaction IDs from other payment methods
- **New Use**: Stores PayMongo payment intent ID for payment verification and tracking

**No schema change required** - the column already exists and is being used for a new purpose.

**Usage in Code:**
- When creating payment intent via PayMongo API, the `payment_intent_id` is saved to `payment.Transaction_ID`
- Used to retrieve payment status from PayMongo API
- Used for payment verification on return from PayMongo redirect

---

## Product Table Changes

### New Columns Added

#### 1. `OrderType` Column
- **Type**: `ENUM('direct','site-assessment')`
- **Default**: `'direct'`
- **Purpose**: Defines whether a product requires Direct Order or Site Assessment Order flow
- **Location**: Added after `Subcategory` field

**SQL Example:**
```sql
ALTER TABLE `product` 
ADD COLUMN `OrderType` enum('direct','site-assessment') DEFAULT 'direct' 
AFTER `Subcategory`;
```

#### 2. `PriceMin` Column
- **Type**: `DECIMAL(10,2)`
- **Default**: `NULL`
- **Purpose**: Minimum price for Site Assessment products (used to display price range on booking page)
- **Location**: Added after `OrderType` field

**SQL Example:**
```sql
ALTER TABLE `product` 
ADD COLUMN `PriceMin` decimal(10,2) DEFAULT NULL 
AFTER `OrderType`;
```

#### 3. `PriceMax` Column
- **Type**: `DECIMAL(10,2)`
- **Default**: `NULL`
- **Purpose**: Maximum price for Site Assessment products (used to display price range on booking page)
- **Location**: Added after `PriceMin` field

**SQL Example:**
```sql
ALTER TABLE `product` 
ADD COLUMN `PriceMax` decimal(10,2) DEFAULT NULL 
AFTER `PriceMin`;
```

---

## Status Enum Updates

### Order Status Enum

The `order.Status` enum needs to support the following statuses for both order types:

#### Direct Order Statuses:
- `'Pending Payment'` - Order created, awaiting payment
- `'Paid'` - Payment received and verified
- `'In Fabrication'` - Order is being manufactured
- `'Completed'` - Order fully processed and delivered
- `'Cancelled'` - Order cancelled
- `'Returned'` - Order returned

#### Site Assessment Order Statuses:
- `'Pending Booking Confirmation'` - Booking submitted, awaiting admin confirmation
- `'Booking Confirmed'` or `'Approved'` - Booking confirmed, waiting for ocular visit
- `'Ocular Visit Completed'` - Ocular visit done, preparing quotation
- `'Quotation Available'` - Quotation ready for customer review
- `'Awaiting Payment'` - Quotation accepted, waiting for payment
- `'In Fabrication'` - Payment received, order in fabrication
- `'Ready for Installation'` or `'Installation Completed'` - Installation done (may have balance)
- `'Completed'` - Order fully completed
- `'Cancelled'` - Order cancelled

**SQL Example to Update Enum:**
```sql
ALTER TABLE `order` 
MODIFY COLUMN `Status` ENUM(
    'Pending Payment',
    'Pending Booking Confirmation',
    'Booking Confirmed',
    'Approved',
    'Ocular Visit Completed',
    'Quotation Available',
    'Awaiting Payment',
    'Paid',
    'In Fabrication',
    'Ready for Installation',
    'Installation Completed',
    'Completed',
    'Cancelled',
    'Returned'
) DEFAULT 'Pending Payment';
```

**Note**: The exact enum values may differ in your implementation. Check your actual schema and application code for the exact status names used.

---

## Indexes Added

### Order Table Indexes

#### `idx_order_type` Index
- **Column**: `OrderType`
- **Purpose**: Improve query performance when filtering orders by type (Direct vs Site-Assessed)
- **Type**: Non-unique index

**SQL Example:**
```sql
ALTER TABLE `order` 
ADD KEY `idx_order_type` (`OrderType`);
```

---

## Migration Scripts

### Complete Migration Script

Here's a complete migration script that adds all the necessary columns:

```sql
-- ============================================================================
-- Migration: Add Order Type and Booking Support
-- Description: Adds support for Direct Orders and Site Assessment Orders
-- Date: Based on IMPLEMENTATION_SUMMARY.md
-- ============================================================================

-- Step 1: Add OrderType column to order table
ALTER TABLE `order` 
ADD COLUMN `OrderType` enum('Direct','Site-Assessed') DEFAULT 'Direct' 
COMMENT 'Order type: Direct or Site-Assessed' 
AFTER `CustomerNotified_Date`;

-- Step 2: Add PreferredInstallationDate column to order table
ALTER TABLE `order` 
ADD COLUMN `PreferredInstallationDate` date DEFAULT NULL 
COMMENT 'Customer preferred installation date (captured at checkout)' 
AFTER `CustomerNotified_Date`;

-- Step 3: Add index for OrderType
ALTER TABLE `order` 
ADD KEY `idx_order_type` (`OrderType`);

-- Step 4: Add OrderType column to product table
ALTER TABLE `product` 
ADD COLUMN `OrderType` enum('direct','site-assessment') DEFAULT 'direct' 
AFTER `Subcategory`;

-- Step 5: Add PriceMin column to product table
ALTER TABLE `product` 
ADD COLUMN `PriceMin` decimal(10,2) DEFAULT NULL 
AFTER `OrderType`;

-- Step 6: Add PriceMax column to product table
ALTER TABLE `product` 
ADD COLUMN `PriceMax` decimal(10,2) DEFAULT NULL 
AFTER `PriceMin`;

-- Step 7: Update order Status enum to include new statuses
-- NOTE: Adjust the enum values based on your actual implementation
ALTER TABLE `order` 
MODIFY COLUMN `Status` ENUM(
    'Pending Payment',
    'Pending Booking Confirmation',
    'Booking Confirmed',
    'Approved',
    'Ocular Visit Completed',
    'Quotation Available',
    'Awaiting Payment',
    'Paid',
    'In Fabrication',
    'Ready for Installation',
    'Installation Completed',
    'Completed',
    'Cancelled',
    'Returned',
    'Pending Review',
    'Awaiting Admin',
    'Ready to Approve',
    'Disapproved'
) DEFAULT 'Pending Payment';

-- Step 8: Verify Transaction_ID column exists in payment table
-- (This column should already exist, but verify it's VARCHAR(100))
-- If it doesn't exist, add it:
-- ALTER TABLE `payment` 
-- ADD COLUMN `Transaction_ID` varchar(100) DEFAULT NULL;

-- Migration Complete
```

---

## Data Migration Notes

### Existing Data Handling

1. **Existing Orders**:
   - All existing orders will default to `OrderType = 'Direct'`
   - Existing orders with `Status = 'Pending Review'` or similar should be mapped appropriately

2. **Existing Products**:
   - All existing products will default to `OrderType = 'direct'`
   - `PriceMin` and `PriceMax` will be `NULL` for existing products
   - You may need to update products manually to set appropriate `OrderType`, `PriceMin`, and `PriceMax` values

3. **Payment Transaction_ID**:
   - Existing `Transaction_ID` values (if any) will remain unchanged
   - New PayMongo payments will populate this field with `payment_intent_id`

---

## Verification Queries

After running the migration, use these queries to verify:

```sql
-- Check order table structure
DESCRIBE `order`;

-- Check product table structure
DESCRIBE `product`;

-- Check payment table structure
DESCRIBE `payment`;

-- Verify OrderType values in orders
SELECT `OrderType`, COUNT(*) as count 
FROM `order` 
GROUP BY `OrderType`;

-- Verify OrderType values in products
SELECT `OrderType`, COUNT(*) as count 
FROM `product` 
GROUP BY `OrderType`;

-- Check orders with PreferredInstallationDate
SELECT COUNT(*) as orders_with_preferred_date
FROM `order` 
WHERE `PreferredInstallationDate` IS NOT NULL;

-- Check products with price ranges
SELECT COUNT(*) as products_with_price_range
FROM `product` 
WHERE `PriceMin` IS NOT NULL AND `PriceMax` IS NOT NULL;

-- Check payment records with Transaction_ID (PayMongo)
SELECT COUNT(*) as payments_with_transaction_id
FROM `payment` 
WHERE `Transaction_ID` IS NOT NULL;
```

---

## Summary

### Tables Modified:
1. **`order`** - Added 2 columns (`OrderType`, `PreferredInstallationDate`), updated `Status` enum, added 1 index
2. **`payment`** - Repurposed existing `Transaction_ID` column (no schema change)
3. **`product`** - Added 3 columns (`OrderType`, `PriceMin`, `PriceMax`)

### Total Changes:
- **3 new columns** in `order` table
- **3 new columns** in `product` table
- **1 index** added to `order` table
- **1 column repurposed** in `payment` table (no schema change)
- **Status enum updated** to support new order statuses

---

## Important Notes

1. **Backward Compatibility**: All new columns have default values, so existing data will continue to work
2. **Status Enum**: The exact status enum values may differ from what's documented here. Always check your actual schema and application code
3. **Transaction_ID**: This column is being repurposed for PayMongo. If you have existing data in this column, ensure it doesn't conflict with PayMongo payment intent IDs
4. **Product OrderType**: Products need to be manually configured with the correct `OrderType`, `PriceMin`, and `PriceMax` values
5. **Testing**: After migration, thoroughly test both Direct Order and Site Assessment Order flows

---

## References

- **Implementation Summary**: `docs/IMPLEMENTATION_SUMMARY.md`
- **Database Schema**: `database/glassify_complete_schema.sql`
- **Latest Database Dump**: `database/scripts/latest_glassifydb (6).sql`
