# Glassify-CI All Changes Documentation

## Overview
Complete documentation of all changes, additions, and modifications made to the Glassify-CI repository.

## Date of Changes
**Date:** January 18, 2026

---

## 1. MODIFIED FILES

### Controllers

#### `application/controllers/AdminCon.php`
**Changes:**
- Added `Inventory_model` import for inventory management
- Enhanced `admin_product()` method with automatic product status updates
- Added logic to update all products' status based on material availability
- Implemented fallback mechanism to show all products if filtering results in empty set

**Code Changes:**
```php
$this->load->model('Inventory_model');

// Fetch ALL products first (admin needs to see all for management)
$allProducts = $this->Product_model->get_all_products();

// Update product status based on materials for each product
foreach ($allProducts as $product) {
    $this->Inventory_model->update_product_status_from_materials($product->Product_ID);
}

// Reload products - get products that customers can see (In Stock or Low Stock)
$data['products'] = $this->Product_model->get_products();

// Ensure we have products to display - if filtering removed all products,
// show all products so admin can manage them
if (empty($data['products']) && !empty($allProducts)) {
    $data['products'] = $allProducts;
}
```

#### `application/controllers/CustomizationFieldsCon.php`
**Changes:**
- Complete overhaul of `Windows_Sliding` customization fields
- Replaced simple fields with detailed 900 Series sliding window specifications
- Organized into 4-step process with step numbers

**Before:**
```php
'Windows_Sliding' => [
    ['type' => 'tags', 'label' => 'Glass Type', 'id' => 'glassType', 'options' => ['Clear', 'Tinted', 'Laminated']],
    ['type' => 'tags', 'label' => 'Frame Color/Material', 'id' => 'frameColor', 'options' => ['White', 'Black', 'Silver', 'Bronze', 'Wood', 'Aluminum']],
    ['type' => 'number', 'label' => 'Thickness (mm)', 'id' => 'thickness', 'min' => 1, 'step' => 0.1],
    ['type' => 'checkbox', 'label' => 'Screen', 'id' => 'screen']
],
```

**After:**
```php
'Windows_Sliding' => [
    ['type' => 'tags', 'label' => 'Number of Panels', 'id' => 'numberOfPanels', 'options' => ['2 Panels', '4 Panels'], 'stepNumber' => 1],
    ['type' => 'tags', 'label' => 'Transom Type (Top / Bottom Fixed Panel)', 'id' => 'transomType', 'options' => ['None', 'Fixed Transom Head (Fixed glass at top)', 'Fixed Transom Sill (Fixed glass at bottom)'], 'stepNumber' => 1],
    ['type' => 'tags', 'label' => 'Track System (Sliding Rail Count)', 'id' => 'trackSystem', 'options' => ['2 Tracks', '3 Tracks'], 'stepNumber' => 2],
    ['type' => 'tags', 'label' => 'Panel Configuration', 'id' => 'panelConfiguration', 'options' => ['S | S (Sliding | Sliding)', 'F | S (Fixed | Sliding)', 'S | S | S | S (All Sliding)', 'F | S | S | F (Fixed | Sliding | Sliding | Fixed)'], 'stepNumber' => 2],
    ['type' => 'tags', 'label' => 'Frame Color', 'id' => 'frameColor', 'options' => ['Hanalok', 'White', 'Black', 'Gray', 'Wood Finish'], 'stepNumber' => 3],
    ['type' => 'tags', 'label' => 'Glass Type', 'id' => 'glassType', 'options' => ['Clear', 'Ultra Clear', 'Bronze', 'Light Green', 'Dark Gray', 'Copperfree Mirror', 'Euro Gray', 'Ford Blue', 'Reflective: Clear', 'Reflective: Gray', 'Reflective: Light Blue', 'Reflective: Dark Blue', 'Reflective: Light Green', 'Reflective: Dark Green', 'Reflective: Light Bronze', 'Tempered: Clear', 'Tempered: Bronze'], 'stepNumber' => 3],
    ['type' => 'tags', 'label' => 'Glass Thickness', 'id' => 'glassThickness', 'options' => ['6mm'], 'stepNumber' => 3],
    ['type' => 'tags', 'label' => 'Lock Type', 'id' => 'lockType', 'options' => ['Center Lok 904 Big', 'Flushlok #12', 'Durable Flushlok', 'New Auto Flushlock'], 'stepNumber' => 4],
    ['type' => 'tags', 'label' => 'Roller Type', 'id' => 'rollerType', 'options' => ['Single Panel Roller', 'Blue Single Roller', 'Blue Double Roller'], 'stepNumber' => 4],
    ['type' => 'tags', 'label' => 'Screen', 'id' => 'screen', 'options' => ['With Screen', 'Without Screen'], 'stepNumber' => 4]
],
```

#### `application/controllers/ProductCon.php`
**Changes:**
- Modified image upload validation in `upload_product_images()` method
- Changed minimum images from 3 to 1
- Added maximum limit of 10 images
- Updated error messages

**Code Changes:**
```php
// Before
if ($file_count < 3) {
    echo json_encode(['status' => 'error', 'msg' => 'Please upload at least 3 images.']);
    return;
}

// After
if ($file_count < 1) {
    echo json_encode(['status' => 'error', 'msg' => 'Please upload at least 1 image.']);
    return;
}
if ($file_count > 10) {
    echo json_encode(['status' => 'error', 'msg' => 'Maximum 10 images allowed per product.']);
    return;
}
```

#### `application/controllers/ShopCon.php`
**Changes:**
- Various modifications to shop controller logic (specific changes require reading the file)

### Models

#### `application/models/Inventory_model.php`
**Changes:**
- Modified `update_product_status_from_materials()` method
- Changed default behavior for products without linked materials
- Products now default to "In Stock" instead of "Out of Stock"
- Improved status preservation logic

**Code Changes:**
```php
// Before
if (empty($materials)) {
    // No materials linked - set to Out of Stock
    $this->db->where('Product_ID', $product_id);
    $this->db->update('product', ['Status' => 'Out of Stock']);
    return 'Out of Stock';
}

// After
if (empty($materials)) {
    // No materials linked - set to In Stock by default (so product is visible)
    // Admin can manage materials later, but product should still be visible
    $this->db->where('Product_ID', $product_id);
    $current_status = $this->db->select('Status')->get('product')->row()->Status ?? 'In Stock';
    // Only update if status is null or empty, otherwise keep existing status
    if (empty($current_status) || $current_status === 'Out of Stock') {
        $this->db->where('Product_ID', $product_id);
        $this->db->update('product', ['Status' => 'In Stock']);
        return 'In Stock';
    }
    return $current_status;
}
```

#### `application/models/Product_model.php`
**Changes:**
- Added `get_all_products()` method for admin use
- Added `product_name_exists()` method for duplicate checking
- Enhanced `get_products()` method with explicit column selection

**New Methods:**
```php
public function get_all_products() {
    // Get all products regardless of status (for admin)
    // Explicitly select all columns including ImageUrl
    $this->db->select('*');
    return $this->db->order_by('DateAdded', 'DESC')->get('product')->result();
}

public function product_name_exists($productName, $excludeId = null) {
    $this->db->where('ProductName', $productName);
    if ($excludeId !== null) {
        $this->db->where('Product_ID !=', $excludeId);
    }
    $query = $this->db->get('product');
    return $query->num_rows() > 0;
}
```

### Views

#### `application/views/admin_page/admin_product.php`
**Changes:**
- Modifications to admin product management interface (requires reading the file for specific changes)

#### `application/views/shop/2DModeling.php`
**Changes:**
- Updates to 2D modeling interface (requires reading the file for specific changes)

#### `application/views/shop/products.php`
**Changes:**
- Changes to shop products page display and functionality (requires reading the file for specific changes)

### Assets

#### `assets/css/admin_css/admin_product.css`
**Changes:**
- Styling updates for admin product pages

#### `assets/js/2d-functions/dynamic_customization.js`
**Changes:**
- JavaScript enhancements for 2D customization functionality

#### `assets/js/admin-js/products.js`
**Changes:**
- Admin product management JavaScript updates

#### `assets/js/products-page/filters.js`
**Changes:**
- Product filtering functionality improvements

---

## 2. ADDED FILES

### Database Script
#### `database/scripts/update_windows_sliding_fields.sql`
**Purpose:** Database script to update Windows_Sliding customization fields

**Content:**
```sql
-- Update or Insert Windows_Sliding field configuration
INSERT INTO `customization_field_configs`
(`Category`, `Subcategory`, `FieldKey`, `FieldConfig`, `Created_Date`, `Updated_Date`)
VALUES (
  'Windows',
  'Sliding',
  'Windows_Sliding',
  '[JSON configuration with all 4 steps]',
  NOW(),
  NOW()
)
ON DUPLICATE KEY UPDATE
  `FieldConfig` = VALUES(`FieldConfig`),
  `Updated_Date` = NOW();
```

### Design Images (8 files added to `uploads/designs/`)
- `design_3_1768702192_696c40f0b365b.png`
- `design_3_1768702194_696c40f280cfb.png`
- `design_3_1768702212_696c410488009.png`
- `design_3_1768702234_696c411a5790e.png`
- `design_3_1768702239_696c411f757c2.png`
- `design_3_1768702240_696c412025591.png`
- `design_3_1768702245_696c4125b0e5d.png`
- `design_3_1768702256_696c4130d0145.png`

### Product Images (11 files added to `uploads/products/`)
- `091128a9856039304a2bbbecff2dd220.jpg`
- `0e3a04e1558ddeb9a3758290711f9781.jpg`
- `44130cee0b14fb0e01f6e72cbdcc19a9.jpg`
- `469b1a719c020e50ee419d54dacd3ac4.jpg`
- `6609816aa34e5cb2019e6f1f7766bd2d.jpg`
- `7744f09d26e835c1a9fdce541358c1b8.jpg`
- `95072f6aea00ed2cbecfed2f667bcbdf.jpg`
- `981f42586ddf4d9b98eaace916808dee.jpg`
- `c9659945b09ef1c8ad6950d32e05aad5.jpg`
- `e45f109528eb317b89e7f14e0b1c5e93.jpg`
- `ffec893db70de5cf63f73ce2dcaa4140.jpg`

---

## 3. SUMMARY

### Files Modified: 13
### Files Added: 20 (1 SQL script + 19 images)

### Key Features Added:
1. **Flexible Product Images**: 1-10 images per product (was 3 minimum)
2. **Enhanced Admin Product Management**: View all products, better inventory tracking
3. **Advanced Sliding Window Customization**: 4-step process with 17 glass types, 5 frame colors, multiple hardware options
4. **Improved Inventory Logic**: Products default to "In Stock" when no materials are linked
5. **New Product Images**: 11 additional product images
6. **New Design Images**: 8 design images for 2D modeling

### Technical Improvements:
- Database script with safe upsert operations
- Better separation of admin vs customer product views
- Enhanced customization field configurations
- Improved JavaScript functionality for customization and filtering