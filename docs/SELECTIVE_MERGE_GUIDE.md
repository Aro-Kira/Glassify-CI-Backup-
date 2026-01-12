# Selective Merge Guide: Inventory & Product Management Features

This guide will help you selectively merge only the **Inventory Management** and **Product Management** features from `sean-branch` into your current branch (`main-aro`).

## Files to Merge

### 1. Inventory Management Features (from Analysis lines 111-140)

#### Core Files:
- `application/controllers/api/Inventory_api.php` - Inventory API endpoints
- `application/models/Inventory_model.php` - Inventory model with all methods
- `application/controllers/InventCon.php` - Inventory controller

#### Routes (add to `application/config/routes.php`):
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

#### Views (if needed):
- `application/views/inventory_page/inventory_dashboard.php`
- `application/views/inventory_page/inventory_products.php`
- `application/views/inventory_page/inventory_inventory.php`
- `application/views/inventory_page/inventory_reports.php`
- `application/views/inventory_page/inventory_account.php`
- `application/views/inventory_page/inventory_notif.php`

### 2. Product Management Features (from Analysis lines 41-51)

#### Core Files:
- `application/controllers/ShopCon.php` - Shop controller (products & product_2d methods)
- `application/models/Product_model.php` - Product model

#### Routes (add to `application/config/routes.php`):
```php
/* 
======================================
=============Shop Routes==============
======================================
 */

$route['products'] = 'ShopCon/products';
$route['2DModeling'] = 'ShopCon/product_2d';
```

#### Views:
- `application/views/shop/products.php` - Product catalog page
- `application/views/shop/2DModeling.php` - 2D modeling page

#### Assets (if changed):
- `assets/js/2d-functions/2d_functions.js` - 2D modeling JavaScript
- `assets/css/general-customer/shop/2DModeling_styles.css` - 2D styling

## Step-by-Step Merge Process

### Option 1: Using Git Checkout (Recommended)

1. **Ensure you're on your target branch:**
   ```bash
   git checkout main-aro
   git status  # Make sure working directory is clean
   ```

2. **Copy specific files from sean-branch:**
   ```bash
   # Inventory Management files
   git checkout sean-branch -- application/controllers/api/Inventory_api.php
   git checkout sean-branch -- application/models/Inventory_model.php
   git checkout sean-branch -- application/controllers/InventCon.php
   
   # Product Management files
   git checkout sean-branch -- application/controllers/ShopCon.php
   git checkout sean-branch -- application/models/Product_model.php
   git checkout sean-branch -- application/views/shop/products.php
   git checkout sean-branch -- application/views/shop/2DModeling.php
   ```

3. **Merge routes selectively:**
   ```bash
   # Copy routes file temporarily to see differences
   git checkout sean-branch -- application/config/routes.php
   # Manually edit routes.php to keep only the routes you need
   # Or use git checkout -p to selectively apply changes
   ```

4. **Add routes manually:**
   - Open `application/config/routes.php`
   - Add the inventory and product routes listed above
   - Keep your existing routes intact

5. **Review and test:**
   ```bash
   git status  # Review what's changed
   git diff    # Review the differences
   ```

6. **Commit the changes:**
   ```bash
   git add .
   git commit -m "Selectively merge Inventory Management and Product Management from sean-branch"
   ```

### Option 2: Manual File Copy (If Option 1 has conflicts)

1. **Create a backup:**
   ```bash
   git checkout main-aro
   git stash  # Save any uncommitted changes
   ```

2. **Switch to sean-branch to read files:**
   ```bash
   git checkout sean-branch
   ```

3. **Manually copy specific files:**
   - Copy the files listed above from sean-branch
   - Use a file comparison tool (WinMerge, Beyond Compare, etc.)
   - Or use git show to view and copy:
     ```bash
     git show sean-branch:application/controllers/api/Inventory_api.php > temp_inventory_api.php
     ```

4. **Switch back and integrate:**
   ```bash
   git checkout main-aro
   # Copy the files manually to your working directory
   # Edit routes.php to add necessary routes
   ```

### Option 3: Selective Git Cherry-Pick (For specific commits)

If the features are in specific commits:

1. **Find relevant commits:**
   ```bash
   git log sean-branch --oneline --grep="inventory\|product\|Inventory\|Product"
   ```

2. **Cherry-pick specific commits:**
   ```bash
   git checkout main-aro
   git cherry-pick <commit-hash>
   # Resolve any conflicts if they occur
   ```

## Important Considerations

### Dependencies to Check:

1. **Database Schema:**
   - Ensure `inventory_items` table exists
   - Ensure `product_materials` table exists (for inventory-product relationships)
   - Check if any new columns were added

2. **Authentication:**
   - Inventory API requires "Inventory Officer" role
   - Ensure user roles are properly set up in your database

3. **Model Dependencies:**
   - `Inventory_model.php` may depend on other models
   - `Product_model.php` should have methods: `get_products()`, `get_product($id)`

4. **View Dependencies:**
   - Check if views reference specific CSS/JS files
   - Ensure asset files exist

### Testing Checklist:

After merging, test:

- [ ] `/products` route works and displays products
- [ ] `/2DModeling` route works with product selection
- [ ] `/inventory-dashboard` loads (if you have inventory views)
- [ ] `/api/inventory/get_items` returns JSON (requires Inventory Officer login)
- [ ] Product model methods work correctly
- [ ] Inventory model methods work correctly

## Troubleshooting

### If you get conflicts:
```bash
# View conflicts
git status

# Resolve manually or use merge tool
git mergetool

# After resolving, add and commit
git add <resolved-files>
git commit
```

### If files are missing:
```bash
# Check if files exist in sean-branch
git ls-tree -r sean-branch --name-only | grep -i inventory
git ls-tree -r sean-branch --name-only | grep -i product
```

### If routes don't work:
- Check `application/config/routes.php` syntax
- Ensure routes are placed in correct order (specific routes before generic ones)
- Clear CodeIgniter cache if needed

## Alternative: Partial File Merge

If you only need specific methods from files:

1. **View differences:**
   ```bash
   git diff main-aro..sean-branch -- application/models/Inventory_model.php
   ```

2. **Use interactive patch:**
   ```bash
   git checkout -p sean-branch -- application/models/Inventory_model.php
   # Select which changes (hunks) to apply
   ```

This allows you to pick and choose specific changes within files!


