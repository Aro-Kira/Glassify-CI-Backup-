<link rel="stylesheet" href="<?= base_url('assets/css/admin_css/admin_product.css'); ?>">
<script src="<?= base_url('assets/js/admin-js/products.js'); ?>"></script>


<!-- Products Section -->
<section class="products-section-main">
  <div class="section-header">
    <h1 class="page-title">Products</h1>
    <div class="header-buttons">
      <!-- <button class="export-btn">Export</button> -->
      <button class="add-product-btn">+ Add New Product</button>
    </div>
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
        <option value="balcony">Balcony</option>
        <option value="board">Board</option>
        <option value="cabinet">Cabinet</option>
        <option value="doors">Doors</option>
        <option value="mirrors">Mirrors</option>
        <option value="partition">Partition</option>
        <option value="shower-enclosure">Shower Enclosure</option>
        <option value="sliding-doors">Sliding Doors</option>
        <option value="sliding-windows">Sliding Windows</option>
        <option value="stair-railings">Stair Railings</option>
        <option value="storefront">Storefront</option>
        <option value="windows">Windows</option>
      </select>
      <select class="sort-products" id="sortProducts">
        <option value="recent">Recently Added</option>
        <option value="last">Last Added</option>
      </select>
    </div>

  </div>

  <!-- Products Table -->
  <div class="table-container">
    <div class="product-grid">
      <?php foreach ($products as $product): ?>
        <div class="product-card" data-id="<?= $product->Product_ID; ?>" data-category="<?= $product->Category; ?>"
          data-material="<?= $product->Material; ?>">
          <div class="product-image">
            <img src="<?= base_url('uploads/products/' . ($product->ImageUrl ?? 'default.png')); ?>"
              alt="<?= $product->ProductName; ?>">
          </div>
          <p class="product-name"><?= $product->ProductName; ?></p>
          <p class="product-price">₱<?= isset($product->Price) ? number_format($product->Price, 2) : '0.00'; ?></p>
          <div class="product-actions">
            <button class="edit-btn"><i class="fas fa-pen"></i> Edit</button>
            <button class="remove-btn" type="button"><i class="fas fa-trash"></i> Remove</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>



  <!-- Pagination -->
  <div class="pagination">
    <span>Showing 1-4 of 255 items</span>
    <div class="pagination-controls">
      <button><i class="fas fa-chevron-left"></i></button>
      <button class="active">1</button>
      <button><i class="fas fa-chevron-right"></i></button>
    </div>
  </div>
</section>
</div>

<!-- Popup Overlay -->
<div class="popup-overlay" id="productPopup">
  <div class="popup">
    <span class="close-btn" id="closePopup">&times;</span>
    <h3>Add New Product</h3>

    <!-- Image Preview -->
    <div class="form-group">

      <!-- preview box -->
      <div class="image-preview">
        <img src="#" alt="">
      </div>
      <input type="file" id="productImageInput" accept="image/*" style="display:none">

      <!-- styled label acting like a button -->
      <label for="productImageInput" class="upload-btn">
        <i class="fas fa-upload"></i>
        <span>Upload Image</span>
      </label>
    </div>


    <!-- Form Fields -->
    <div class="form-group">
      <label for="productName">Product Name</label>
      <input type="text" id="productName" class="text-input" placeholder="Enter product name">
    </div>

    <div class="form-group">
      <label for="productCategory">Category</label>
      <select id="productCategory" class="input-text">
        <option value="" disabled selected>Select category</option>
        <option value="Mirrors">Mirrors</option>
        <option value="Shower Enclosure / Partition">Shower Enclosure / Partition</option>
        <option value="Aluminum Doors">Aluminum Doors</option>
        <option value="Stair Railings">Stair Railings</option>
        <option value="Windows">Windows</option>
        <option value="Glass Partition">Glass Partition</option>
      </select>
    </div>

    <div class="form-group">
      <label for="productMaterial">Material</label>
      <select id="productMaterial" class="input-text">
        <option value="" disabled selected>Select material</option>
        <option value="Glass">Glass</option>
        <option value="Aluminum">Aluminum</option>
      </select>
    </div>

    <div class="form-group">
      <label for="productPrice">Price</label>
      <div class="price-input">
        <span>₱</span>
        <input type="number" id="productPrice" class="input-text" placeholder="00.00">
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="popup-actions">
      <button class="save-btn">Save</button>
      <button class="cancel-btn">Cancel</button>
    </div>
  </div>
</div>

<!-- Edit Product Popup -->
<div class="popup-overlay" id="editPopup">
  <div class="popup">
    <span class="close-btn" id="closeEditPopup">&times;</span>
    <h3>Edit Product</h3>

    <!-- Image Preview -->
    <div class="form-group">
      <label>Product Image</label>
      <div class="image-preview">
        <img src="" alt="Preview">
      </div>
      <input type="file" id="editProductImageInput" accept="image/*" style="display:none">
      <label for="editProductImageInput" class="upload-btn">
        <i class="fas fa-upload"></i>
        <span>Upload Image</span>
      </label>
    </div>

    <!-- Product Name -->
    <div class="form-group">
      <label for="editProductName">Product Name</label>
      <input type="text" id="editProductName" class="input-text" placeholder="Enter product name">
    </div>

    <!-- Category -->
    <div class="form-group">
      <label for="editProductCategory">Category</label>
      <select id="editProductCategory" class="input-text">
        <option value="" disabled>Select category</option>
        <option value="Mirrors">Mirrors</option>
        <option value="Shower Enclosure / Partition">Shower Enclosure / Partition</option>
        <option value="Aluminum Doors">Aluminum Doors</option>
        <option value="Stair Railings">Stair Railings</option>
        <option value="Windows">Windows</option>
        <option value="Glass Partition">Glass Partition</option>
      </select>
    </div>

    <!-- Material (Read-only for Admin) -->
    <div class="form-group">
      <label for="editProductMaterial">Material</label>
      <select id="editProductMaterial" class="input-text" disabled style="opacity: 0.6; cursor: not-allowed;">
        <option value="" disabled>Select material</option>
        <option value="Glass">Glass</option>
        <option value="Aluminum">Aluminum</option>
      </select>
      <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Material cannot be edited by Admin</small>
    </div>

    <!-- Price -->
    <div class="form-group">
      <label for="editProductPrice">Price</label>
      <div class="price-input">
        <span>₱</span>
        <input type="number" id="editProductPrice" class="input-text" placeholder="00.00">
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="popup-actions">
      <button class="save-btn" id="editSaveBtn">Save</button>
      <button class="cancel-btn" id="cancelEdit">Cancel</button>
    </div>
  </div>
</div>


<!-- Action Buttons -->
<div class="popup-actions">
  <button class="save-btn" id="editSaveBtn">Save</button>
  <button class="cancel-btn" id="cancelEdit">Cancel</button>
</div>
</div>
</div>


<!-- The popup -->
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
</script>

<script src="<?php echo base_url('assets/js/admin-js/products.js'); ?>"></script>