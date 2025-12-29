# Selective Merge Instructions - Manual Steps

Since you're currently on `sean-branch` and want to merge specific features to `main-aro`, follow these steps:

## Step 1: Switch to main-aro branch

```powershell
git checkout main-aro
```

If you have uncommitted changes, stash them first:
```powershell
git stash
git checkout main-aro
```

## Step 2: Copy files from sean-branch

Run these commands one by one to copy the files:

### Inventory Management Files:
```powershell
git checkout sean-branch -- application/controllers/api/Inventory_api.php
git checkout sean-branch -- application/models/Inventory_model.php
git checkout sean-branch -- application/controllers/InventCon.php
```

### Product Management Files:
```powershell
git checkout sean-branch -- application/controllers/ShopCon.php
git checkout sean-branch -- application/models/Product_model.php
```

## Step 3: Update routes.php (if needed)

Check if `application/config/routes.php` already has these routes. If not, add them:

### Inventory Routes (add before `$route['404_override']`):
```php
/* 
======================================
=============Inventory Routes==============
======================================
 */

$route['inventory-dashboard'] = 'InventCon/inventory_dashboard';
$route['inventory-products'] = 'InventCon/inventory_products';
$route['inventory-inventory'] = 'InventCon/inventory_inventory';
$route['inventory-account'] = 'InventCon/inventory_account';
$route['inventory-reports'] = 'InventCon/inventory_reports';
$route['inventory-notif'] = 'InventCon/inventory_notif';

/* 
======================================
=============Inventory API Routes==============
======================================
 */
$route['api/inventory/get_items'] = 'api/Inventory_api/get_items';
$route['api/inventory/get_statistics'] = 'api/Inventory_api/get_statistics';
$route['api/inventory/add_item'] = 'api/Inventory_api/add_item';
$route['api/inventory/update_item/(:num)'] = 'api/Inventory_api/update_item/$1';
$route['api/inventory/delete_item/(:num)'] = 'api/Inventory_api/delete_item/$1';
$route['api/inventory/manage_stock/(:num)'] = 'api/Inventory_api/manage_stock/$1';
$route['api/inventory/get_activities'] = 'api/Inventory_api/get_activities';
```

### Product Routes (should already exist):
```php
$route['products'] = 'ShopCon/products';
$route['2DModeling'] = 'ShopCon/product_2d';
```

## Step 4: Verify and commit

```powershell
# Check what files were changed
git status

# Review the changes
git diff

# If everything looks good, commit
git add .
git commit -m "Selectively merge Inventory Management and Product Management features from sean-branch"
```

## Files Being Merged:

### Inventory Management:
- ✅ `application/controllers/api/Inventory_api.php` - Complete REST API for inventory
- ✅ `application/models/Inventory_model.php` - Full inventory model with all methods
- ✅ `application/controllers/InventCon.php` - Inventory controller

### Product Management:
- ✅ `application/controllers/ShopCon.php` - Shop controller (products & product_2d methods)
- ✅ `application/models/Product_model.php` - Product model

## What's Included:

### Inventory Features:
- Stock management (add/remove stock)
- Material tracking
- Product-material relationships
- Stock threshold monitoring
- Inventory activity logging
- Material deduction on order payment
- Stock availability checking for manufacturing
- REST API endpoints for all operations

### Product Features:
- Product catalog browsing (`/products`)
- 2D product modeling (`/2DModeling`)
- Product customization support
- Support for categories: Mirrors, Shower Enclosures, Aluminum Doors, Aluminum Bathroom Doors

## Testing Checklist:

After merging, test:
- [ ] `/products` route displays products
- [ ] `/2DModeling?id=X` shows 2D modeling page
- [ ] `/inventory-dashboard` loads (requires Inventory Officer login)
- [ ] `/api/inventory/get_items` returns JSON (requires Inventory Officer login)
- [ ] All routes work correctly

