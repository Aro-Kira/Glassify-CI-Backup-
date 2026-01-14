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
            <?php
              // Handle both JSON array and single string formats
              $imageUrl = $product->ImageUrl ?? '';
              $firstImage = 'default.png';
              
              if (!empty($imageUrl)) {
                // Check if it's a JSON array
                $decoded = json_decode($imageUrl, true);
                if (is_array($decoded) && !empty($decoded)) {
                  $firstImage = $decoded[0];
                } else {
                  // Single image (backward compatibility)
                  $firstImage = $imageUrl;
                }
              }
            ?>
            <img src="<?= base_url('uploads/products/' . $firstImage); ?>"
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

    <!-- Multiple Image Upload -->
    <div class="form-group">
      <label>Product Images <span class="required-indicator">*</span></label>
      <small class="image-requirement">Minimum 3-4 images required</small>
      
      <!-- Image Upload Area -->
      <div class="multiple-image-upload-container">
        <input type="file" id="productImageInput" accept="image/*" multiple style="display:none">
        
        <!-- Upload Dropzone -->
        <div class="image-upload-dropzone" id="imageUploadDropzone">
          <div class="dropzone-content">
            <i class="fas fa-cloud-upload-alt"></i>
            <p class="dropzone-text">Drag & drop images here or <span class="browse-link">browse</span></p>
            <p class="dropzone-subtext">Upload at least 3-4 images (JPG, PNG, GIF)</p>
          </div>
        </div>
        
        <!-- Image Preview Grid -->
        <div class="image-preview-grid" id="imagePreviewGrid">
          <!-- Preview items will be added dynamically -->
        </div>
        
        <!-- Image Count Indicator -->
        <div class="image-count-indicator">
          <span id="imageCount">0</span> / 4+ images uploaded
        </div>
      </div>
    </div>


    <!-- Form Fields -->
    <div class="form-group">
      <label for="productName">Product Name</label>
      <input type="text" id="productName" class="text-input" placeholder="Enter product name">
    </div>

    <!-- Main Category Selection -->
    <div class="form-group">
      <label for="productCategory">Category</label>
      <select id="productCategory" class="input-text">
        <option value="" disabled selected>Select category</option>
        <option value="Windows">Windows</option>
        <option value="Doors">Doors</option>
        <option value="Glass Partitions & Enclosures">Glass Partitions & Enclosures</option>
        <option value="Mirrors & Specialty Glass">Mirrors & Specialty Glass</option>
        <option value="Cabinets & Furniture">Cabinets & Furniture</option>
        <option value="Commercial & Exterior">Commercial & Exterior</option>
      </select>
    </div>

    <!-- Subcategory Selection (appears after category is selected) -->
    <div class="form-group" id="subcategoryGroup" style="display: none;">
      <label for="productSubcategory">Subcategory</label>
      <select id="productSubcategory" class="input-text">
        <option value="" disabled selected>Select subcategory</option>
      </select>
    </div>

    <!-- Dynamic Customization Fields Container -->
    <div id="customizationFields" class="customization-fields-container">
      <!-- Fields will be dynamically generated here based on category/subcategory selection -->
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

    <!-- Multiple Image Upload (Edit) -->
    <div class="form-group">
      <label>Product Images</label>
      <small class="image-requirement">Upload 3-4 images minimum</small>
      
      <!-- Image Upload Area -->
      <div class="multiple-image-upload-container">
        <input type="file" id="editProductImageInput" accept="image/*" multiple style="display:none">
        
        <!-- Upload Dropzone -->
        <div class="image-upload-dropzone" id="editImageUploadDropzone">
          <div class="dropzone-content">
            <i class="fas fa-cloud-upload-alt"></i>
            <p class="dropzone-text">Drag & drop images here or <span class="browse-link">browse</span></p>
            <p class="dropzone-subtext">Upload at least 3-4 images (JPG, PNG, GIF)</p>
          </div>
        </div>
        
        <!-- Image Preview Grid -->
        <div class="image-preview-grid" id="editImagePreviewGrid">
          <!-- Preview items will be added dynamically -->
        </div>
        
        <!-- Image Count Indicator -->
        <div class="image-count-indicator">
          <span id="editImageCount">0</span> / 4+ images uploaded
        </div>
      </div>
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
        <option value="Windows">Windows</option>
        <option value="Doors">Doors</option>
        <option value="Glass Partitions & Enclosures">Glass Partitions & Enclosures</option>
        <option value="Mirrors & Specialty Glass">Mirrors & Specialty Glass</option>
        <option value="Cabinets & Furniture">Cabinets & Furniture</option>
        <option value="Commercial & Exterior">Commercial & Exterior</option>
      </select>
    </div>

    <!-- Subcategory Selection (appears after category is selected) -->
    <div class="form-group" id="editSubcategoryGroup" style="display: none;">
      <label for="editProductSubcategory">Subcategory</label>
      <select id="editProductSubcategory" class="input-text">
        <option value="" disabled selected>Select subcategory</option>
      </select>
    </div>

    <!-- Dynamic Customization Fields Container -->
    <div id="editCustomizationFields" class="customization-fields-container">
      <!-- Fields will be dynamically generated here based on category/subcategory selection -->
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