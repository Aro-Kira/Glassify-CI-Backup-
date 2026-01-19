<link rel="stylesheet" href="<?= base_url('assets/css/admin_css/admin_product.css'); ?>">
<script src="<?= base_url('assets/js/inventory-js/products.js'); ?>"></script>

<!-- Products Section -->
<section class="products-section-main">
  <div class="section-header">
    <h1 class="page-title">Products</h1>
  </div>

  <!-- Filters -->
  <div class="controls-container">
    <div class="search-bar">
      <input type="text" placeholder="Filter by name or category..." class="search-input">
      <button class="search-button">Search</button>
    </div>
    <div class="controls-right">
      <select class="filter-category">
        <option value="">All Category</option>
        <?php foreach ($categories as $category): ?>
        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Products Table -->
  <div class="table-container">
    <div class="product-grid">
        <?php if (!empty($products)): 
        foreach ($products as $product): 
          // Build image path - handle JSON array or single string
          $image_raw = $product->ImageUrl ?? '';
          $placeholder_svg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
          $image_path = $placeholder_svg;
          
          if (!empty($image_raw)) {
              $decoded = json_decode($image_raw, true);
              $first_image = '';
              if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                  $first_image = $decoded[0];
              } else {
                  $first_image = $image_raw;
              }
              
              if (!empty($first_image) && strpos($first_image, 'broken-image-icon') === false) {
                  if (strpos($first_image, 'http') === 0) {
                      $image_path = $first_image;
                  } else if (strpos($first_image, 'assets/') === 0 || strpos($first_image, 'uploads/') === 0) {
                      $image_path = base_url($first_image);
                  } else {
                      $image_path = base_url('uploads/products/' . basename($first_image));
                  }
              }
          }
      ?>
      <div class="product-card" data-id="<?= $product->Product_ID; ?>" data-category="<?= htmlspecialchars($product->Category); ?>"
        data-material-id="<?= isset($product->current_material_id) ? $product->current_material_id : ''; ?>"
        data-material="<?= htmlspecialchars($product->Material ?? ''); ?>">
        <div class="product-image">
          <img src="<?= $image_path; ?>" alt="<?= htmlspecialchars($product->ProductName); ?>" 
            onerror="if(this.src.indexOf('data:image') === -1) { this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4='; }">
        </div>
        <p class="product-name"><?= htmlspecialchars($product->ProductName); ?></p>
        <p class="product-price">₱<?= isset($product->Price) ? number_format($product->Price, 2) : '0.00'; ?></p>
        <?php
          $status = isset($product->Status) ? $product->Status : 'Out of Stock';
          $status_class = '';
          $status_color = '';
          if ($status === 'In Stock') {
            $status_class = 'badge-in-stock';
            $status_color = '#4CAF50';
          } elseif ($status === 'Low Stock') {
            $status_class = 'badge-low-stock';
            $status_color = '#FF9800';
          } else {
            $status_class = 'badge-out-stock';
            $status_color = '#f44336';
          }
        ?>
        <span class="product-status-badge <?= $status_class; ?>" style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; color: white; background-color: <?= $status_color; ?>; margin-bottom: 8px;">
          <?= htmlspecialchars($status); ?>
        </span>
        <div class="product-actions">
          <button class="edit-btn"><i class="fas fa-pen"></i> Edit</button>
          <button class="remove-btn" type="button"><i class="fas fa-trash"></i> Remove</button>
        </div>
      </div>
      <?php 
        endforeach; 
      else: 
      ?>
      <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
        <p>No products found</p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Pagination -->
  <div class="pagination">
    <span>Showing 1-4 of <?= count($products); ?> items</span>
    <div class="pagination-controls">
      <button><i class="fas fa-chevron-left"></i></button>
      <button class="active">1</button>
      <button><i class="fas fa-chevron-right"></i></button>
    </div>
  </div>
</section>

<!-- Edit Product Popup -->
<div class="popup-overlay" id="editPopup">
  <div class="popup">
    <span class="close-btn" id="closeEditPopup">&times;</span>
    <h3>Edit Product</h3>

    <!-- Image Preview -->
    <div class="form-group">
      <label>Product Image</label>
      <div class="image-preview">
        <img src="" alt="Preview" id="editProductImagePreview">
      </div>
      <input type="file" id="editProductImageInput" accept="image/*" style="display:none" disabled>
      <label for="editProductImageInput" class="upload-btn" id="editProductImageLabel" style="opacity: 0.5; cursor: not-allowed;">
        <i class="fas fa-upload"></i>
        <span>Upload Image</span>
      </label>
    </div>

    <!-- Product Name -->
    <div class="form-group">
      <label for="editProductName">Product Name</label>
      <input type="text" id="editProductName" class="input-text" placeholder="Enter product name" readonly>
    </div>

    <!-- Category -->
    <div class="form-group">
      <label for="editProductCategory">Category</label>
      <select id="editProductCategory" class="input-text" disabled>
        <option value="" disabled>Select category</option>
        <option value="Mirrors">Mirrors</option>
        <option value="Shower Enclosure / Partition">Shower Enclosure / Partition</option>
        <option value="Aluminum Doors">Aluminum Doors</option>
        <option value="Stair Railings">Stair Railings</option>
        <option value="Windows">Windows</option>
        <option value="Glass Partition">Glass Partition</option>
      </select>
    </div>

    <!-- Material (Raw Materials from Inventory) -->
    <div class="form-group">
      <label for="editProductMaterial" style="display: flex; align-items: center; justify-content: space-between;">
        <span>Materials (Raw Materials)</span>
        <button type="button" id="addMaterialBtn" class="add-material-btn" style="background: #006494; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">
          <i class="fas fa-plus"></i> Add New Material
        </button>
      </label>
      
      <!-- Current Materials List -->
      <div id="materialsList" style="margin-bottom: 10px; min-height: 40px;">
        <!-- Materials will be dynamically added here -->
      </div>
      
      <!-- Material Selector (hidden by default, shown when adding new material) -->
      <div id="materialSelector" style="display: none;">
        <select id="editProductMaterial" class="input-text">
          <option value="" disabled selected>Select raw material from inventory</option>
          <?php foreach ($inventory_items as $item): ?>
          <option value="<?= $item->InventoryItemID; ?>" 
                  data-item-id="<?= htmlspecialchars($item->ItemID); ?>"
                  data-item-name="<?= htmlspecialchars($item->Name); ?>"
                  data-item-stock="<?= $item->InStock; ?>"
                  data-item-unit="<?= htmlspecialchars($item->Unit); ?>">
            <?= htmlspecialchars($item->ItemID . ' - ' . $item->Name); ?> (Stock: <?= $item->InStock; ?> <?= htmlspecialchars($item->Unit); ?>)
          </option>
          <?php endforeach; ?>
        </select>
        <div style="margin-top: 8px; display: flex; gap: 8px;">
          <button type="button" id="saveMaterialBtn" class="save-material-btn" style="background: #4CAF50; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
            <i class="fas fa-check"></i> Add
          </button>
          <button type="button" id="cancelMaterialBtn" class="cancel-material-btn" style="background: #f44336; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
            <i class="fas fa-times"></i> Cancel
          </button>
        </div>
        <small id="stockInfo" style="color: #666; font-size: 12px; margin-top: 4px; display: block;"></small>
      </div>
      
      <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Add raw materials from inventory to this product</small>
    </div>

    <!-- Price -->
    <div class="form-group">
      <label for="editProductPrice">Price</label>
      <div class="price-input">
        <span>₱</span>
        <input type="number" id="editProductPrice" class="input-text" placeholder="00.00" readonly>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="popup-actions">
      <button class="save-btn" id="editSaveBtn">Save</button>
      <button class="cancel-btn" id="cancelEdit">Cancel</button>
    </div>
  </div>
</div>

<!-- Delete Product Popup -->
<div class="popup-delete-overlay" id="popup-delete">
  <div class="popup-delete-box">
    <div class="popup-delete-header">
      Delete Item?
      <span class="popup-delete-close">&times;</span>
    </div>
    <div class="popup-delete-icon">
      <i class="fas fa-trash"></i>
    </div>
    <p id="delete-message">Are you sure you want to delete this item?</p>
    <div class="popup-delete-actions">
      <button class="popup-delete-cancel">Cancel</button>
      <button class="popup-delete-confirm">Delete</button>
    </div>
  </div>
</div>

<script>
  const base_url = "<?= base_url(); ?>";
  const user_role = "Inventory Officer"; // Set role for inventory officer
</script>

<script src="<?php echo base_url('assets/js/includes/sidebar.js'); ?>"></script>
