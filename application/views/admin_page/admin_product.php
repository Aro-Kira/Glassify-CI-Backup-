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
        <?php
        // Get unique categories from products
        $categories = [];
        foreach ($products as $product) {
          if (!empty($product->Category) && !in_array($product->Category, $categories)) {
            $categories[] = $product->Category;
          }
        }
        sort($categories);
        foreach ($categories as $category): ?>
          <option value="<?= htmlspecialchars($category); ?>"><?= htmlspecialchars($category); ?></option>
        <?php endforeach; ?>
      </select>
      <select class="filter-availability" id="filterAvailability" style="margin-left:8px;">
        <option value="">All Availability</option>
        <option value="available">Available</option>
        <option value="unavailable">Unavailable</option>
      </select>
      <select class="sort-products" id="sortProducts">
        <option value="recent">Recently Added</option>
        <option value="last">Last Added</option>
      </select>
    </div>

  </div>

  <!-- Active Filters Section -->
  <div class="active-filters-section">
    <h4 class="active-filters-title">Active Filters:</h4>
    <div class="active-filters-tags" id="activeFiltersTags">
      <!-- Active filter tags will be added here dynamically -->
    </div>
    <a href="#" class="clear-filters" id="clearAllFilters" style="display: none;">Clear All</a>
  </div>

  <!-- Products Table -->
  <div class="table-container">
    <div class="product-grid">
      <?php foreach ($products as $product): 
        // Handle images - can be JSON array or single string
        $images = [];
        $imagePaths = [];
        $placeholderSvg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
        
        if (!empty($product->ImageUrl)) {
          $decoded = json_decode($product->ImageUrl, true);
          if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
            $images = $decoded;
          } else if (!empty($product->ImageUrl)) {
            $images = [$product->ImageUrl];
          }
        }
        
        // Build proper image paths
        if (!empty($images)) {
          foreach ($images as $image) {
            $image = trim($image);
            if (empty($image) || strpos($image, 'broken-image-icon') !== false) {
              $imagePaths[] = $placeholderSvg;
              continue;
            }
            $image = ltrim($image, '/');
            if (strpos($image, 'http://') === 0 || strpos($image, 'https://') === 0) {
              $imagePaths[] = $image;
            } else if (strpos($image, 'assets/') === 0) {
              $imagePaths[] = base_url($image);
            } else if (strpos($image, 'uploads/') === 0) {
              $imagePaths[] = base_url($image);
            } else {
              $filename = basename($image);
              $imagePaths[] = base_url('uploads/products/' . $filename);
            }
          }
        }
        
        if (empty($imagePaths)) {
          $imagePaths = [$placeholderSvg];
        }
        
        // Get order type
        $orderType = isset($product->OrderType) ? $product->OrderType : 'direct';
        $orderTypeDisplay = ($orderType === 'site-assessed' || $orderType === 'Site-Assessed' || $orderType === 'site-assessment') ? 'Site Assessment' : 'Direct';
        
        // Get status
        $status = isset($product->Status) ? $product->Status : 'Available';
        $statusClass = '';
        if ($status === 'available' || $status === 'Available') {
          $statusClass = 'status-available';
        } else {
          $statusClass = 'status-unavailable';
        }
        
        // Get price range
        $priceMin = isset($product->PriceMin) && $product->PriceMin > 0 ? floatval($product->PriceMin) : null;
        $priceMax = isset($product->PriceMax) && $product->PriceMax > 0 ? floatval($product->PriceMax) : null;
        $price = isset($product->Price) && $product->Price > 0 ? floatval($product->Price) : null;
      ?>
        <div class="product-card" data-id="<?= $product->Product_ID; ?>" data-category="<?= $product->Category; ?>"
          data-material="<?= $product->Material; ?>" data-status="<?= $status; ?>">
          
          <!-- Product Image with Carousel -->
          <div class="product-image-container">
            <div class="product-image-slideshow" data-product-id="<?= $product->Product_ID ?>">
              <?php if (!empty($imagePaths)): ?>
                <?php foreach ($imagePaths as $index => $imagePath): ?>
                  <img src="<?= htmlspecialchars($imagePath) ?>" 
                       alt="<?= htmlspecialchars($product->ProductName) ?>" 
                       class="product-slide <?= $index === 0 ? 'active' : '' ?>"
                       onerror="this.onerror=null; this.src='<?= $placeholderSvg ?>';">
                <?php endforeach; ?>
              <?php else: ?>
                <div class="product-image-placeholder">No Image Available</div>
              <?php endif; ?>
              
              <!-- Carousel Dots -->
              <?php if (count($imagePaths) > 1): ?>
                <div class="slideshow-indicators">
                  <?php for ($i = 0; $i < count($imagePaths); $i++): ?>
                    <span class="indicator-dot <?= $i === 0 ? 'active' : '' ?>" data-slide="<?= $i ?>"></span>
                  <?php endfor; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
          
          <!-- Product Details -->
          <div class="product-details">
            <p class="product-name"><?= htmlspecialchars($product->ProductName); ?></p>
            
            <!-- Order Type -->
            <!-- <p class="product-type">Type: <span><?= htmlspecialchars($orderTypeDisplay); ?></span></p>
             -->
            <!-- Price Range -->
            <p class="product-price">
              <?php if ($priceMin !== null && $priceMax !== null): ?>
                ₱<?= number_format($priceMin, 2) ?> - ₱<?= number_format($priceMax, 2) ?>
              <?php elseif ($price !== null): ?>
                ₱<?= number_format($price, 2) ?>
              <?php else: ?>
                Contact for pricing
              <?php endif; ?>
            </p>
            
            <!-- Stock Status -->
            <div class="product-status-badge <?= $statusClass; ?>">
              <?= htmlspecialchars($status); ?>
            </div>
            
            <!-- Action Buttons -->
            <div class="product-actions">
              <button class="product-edit-btn">
                <i class="fas fa-pen"></i> Edit
              </button>
              <button class="product-remove-btn" type="button">
                <i class="fas fa-trash"></i> Remove
              </button>
            </div>
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
  <div class="popup popup-two-column">
    <span class="close-btn" id="closePopup">&times;</span>
    <h3>Add New Product</h3>

    <div class="form-columns-container">
      <!-- LEFT COLUMN: Images, Product Name, Price Range, Preview -->
      <div class="form-column-left">
        <!-- Multiple Image Upload -->
        <div class="form-group">
          <label>Product Images <span class="required-indicator">*</span></label>
          <small class="image-requirement">Minimum 1 image required</small>
          
          <!-- Image Upload Area -->
          <div class="multiple-image-upload-container">
            <input type="file" id="productImageInput" accept="image/*" multiple style="display:none">
            
            <!-- Upload Dropzone -->
            <div class="image-upload-dropzone" id="imageUploadDropzone">
              <div class="dropzone-content">
                <i class="fas fa-cloud-upload-alt"></i>
                <p class="dropzone-text">Drag & drop images here or <span class="browse-link">browse</span></p>
                <p class="dropzone-subtext">Upload at least 1 image (JPG, PNG, GIF) - Maximum 10 images</p>
              </div>
            </div>
            
            <!-- Image Preview Grid -->
            <div class="image-preview-grid" id="imagePreviewGrid">
              <!-- Preview items will be added dynamically -->
            </div>
            
            <!-- Image Count Indicator -->
            <div class="image-count-indicator">
              <span id="imageCount">0</span> images uploaded
            </div>
          </div>
        </div>

        <!-- Product Name -->
        <div class="form-group">
          <label for="productName">Product Name</label>
          <input type="text" id="productName" class="text-input" placeholder="Enter product name">
        </div>

        <!-- Description -->
        <div class="form-group">
          <label for="productDescription">Description</label>
          <textarea id="productDescription" class="text-input" rows="4" placeholder="Enter product specifications and description"></textarea>
          <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Add product specifications, features, and details</small>
        </div>

        <!-- Price Range (Min - Max) -->
        <div class="form-group">
          <label>Price Range (₱)</label>
          <div class="price-range-container">
            <div class="price-range-item">
              <label for="productPriceMin">Min</label>
              <div class="price-input">
                <span>₱</span>
                <input type="number" id="productPriceMin" class="input-text" placeholder="0.00" step="0.01" min="0">
              </div>
            </div>
            <span class="price-range-separator">-</span>
            <div class="price-range-item">
              <label for="productPriceMax">Max</label>
              <div class="price-input">
                <span>₱</span>
                <input type="number" id="productPriceMax" class="input-text" placeholder="0.00" step="0.01" min="0">
              </div>
            </div>
          </div>
          <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Price range will be adjusted based on customer measurements</small>
        </div>
      </div>

      <!-- DIVIDER -->
      <div class="form-column-divider"></div>

      <!-- RIGHT COLUMN: Order Type, Category, Customization, Standard Sizes -->
      <div class="form-column-right">
        <!-- Order type selection removed — categories are unified -->

        <!-- Main Category Selection -->
        <div class="form-group">
          <label for="productCategory">Category</label>
          <select id="productCategory" class="input-text">
            <option value="" disabled selected>Select category</option>
            <!-- Categories (unified) -->
          </select>
        </div>

        <!-- Subcategory Selection (appears after category is selected) -->
        <div class="form-group" id="subcategoryGroup" style="display: none;">
          <label for="productSubcategory">Subcategory</label>
          <select id="productSubcategory" class="input-text">
            <option value="" disabled selected>Select subcategory</option>
          </select>
        </div>

        <!-- Customization Content (Removed tab) -->
        <div id="customizeTabContent" class="tab-content active">
          <!-- Manage Customization Fields Button (shown when subcategory is selected) -->
          <div class="form-group" id="manageCustomizationGroup" style="display: none;">
            <button type="button" class="manage-fields-btn" id="manageCustomizationBtn">
              <i class="fas fa-cog"></i> Manage Customization Fields
            </button>
          </div>
          <!-- Dynamic Customization Fields Container -->
          <div id="customizationFields" class="customization-fields-container">
            <!-- Fields will be dynamically generated here based on category/subcategory selection -->
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="popup-actions">
      <button type="button" class="save-btn" id="addProductSaveBtn">Save</button>
      <button type="button" class="cancel-btn" id="addProductCancelBtn">Cancel</button>
    </div>
  </div>
</div>

<!-- Edit Product Popup -->
<div class="popup-overlay" id="editPopup">
  <div class="popup popup-two-column">
    <span class="close-btn" id="closeEditPopup">&times;</span>
    <h3>Edit Product</h3>

    <div class="form-columns-container">
      <!-- LEFT COLUMN: Images, Product Name, Price Range, Preview -->
      <div class="form-column-left">
        <!-- Multiple Image Upload -->
        <div class="form-group">
          <label>Product Images <span class="required-indicator">*</span></label>
          <small class="image-requirement">Minimum 1 image required</small>
          
          <!-- Image Upload Area -->
          <div class="multiple-image-upload-container">
            <input type="file" id="editProductImageInput" accept="image/*" multiple style="display:none">
            
            <!-- Upload Dropzone -->
            <div class="image-upload-dropzone" id="editImageUploadDropzone">
              <div class="dropzone-content">
                <i class="fas fa-cloud-upload-alt"></i>
                <p class="dropzone-text">Drag & drop images here or <span class="browse-link">browse</span></p>
                <p class="dropzone-subtext">Upload at least 1 image (JPG, PNG, GIF) - Maximum 10 images</p>
              </div>
            </div>
            
            <!-- Image Preview Grid -->
            <div class="image-preview-grid" id="editImagePreviewGrid">
              <!-- Preview items will be added dynamically -->
            </div>
            
            <!-- Image Count Indicator -->
            <div class="image-count-indicator">
              <span id="editImageCount">0</span> images uploaded
            </div>
          </div>
        </div>

        <!-- Product Name -->
        <div class="form-group">
          <label for="editProductName">Product Name</label>
          <input type="text" id="editProductName" class="text-input" placeholder="Enter product name">
        </div>

          <div class="form-group">
            <label for="editProductAvailability">Availability</label>
            <label style="display:flex; align-items:center; gap:8px;">
              <input type="checkbox" id="editProductAvailability" style="width:18px; height:18px;" />
              <span>Unavailable</span>
            </label>
          </div>

        <!-- Description -->
        <div class="form-group">
          <label for="editProductDescription">Description</label>
          <textarea id="editProductDescription" class="text-input" rows="4" placeholder="Enter product specifications and description"></textarea>
          <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Add product specifications, features, and details</small>
        </div>

        <!-- Price Range (Min - Max) -->
        <div class="form-group">
          <label>Price Range (₱)</label>
          <div class="price-range-container">
            <div class="price-range-item">
              <label for="editProductPriceMin">Min</label>
              <div class="price-input">
                <span>₱</span>
                <input type="number" id="editProductPriceMin" class="input-text" placeholder="0.00" step="0.01" min="0">
              </div>
            </div>
            <span class="price-range-separator">-</span>
            <div class="price-range-item">
              <label for="editProductPriceMax">Max</label>
              <div class="price-input">
                <span>₱</span>
                <input type="number" id="editProductPriceMax" class="input-text" placeholder="0.00" step="0.01" min="0">
              </div>
            </div>
          </div>
          <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Price range will be adjusted based on customer measurements</small>
        </div>
      </div>

      <!-- DIVIDER -->
      <div class="form-column-divider"></div>

      <!-- RIGHT COLUMN: Order Type, Category, Customization, Standard Sizes -->
      <div class="form-column-right">
        <!-- Order type selection removed for edit form — categories are unified -->

        <!-- Main Category Selection (Read-only) -->
        <div class="form-group">
          <label for="editProductCategory">Category</label>
          <select id="editProductCategory" class="input-text" disabled style="opacity: 0.6; cursor: not-allowed;">
            <option value="" disabled selected>Select category</option>
            <!-- Categories (unified) -->
          </select>
          <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Category cannot be edited</small>
        </div>

        <!-- Subcategory Selection (Read-only) -->
        <div class="form-group" id="editSubcategoryGroup" style="display: none;">
          <label for="editProductSubcategory">Subcategory</label>
          <select id="editProductSubcategory" class="input-text" disabled style="opacity: 0.6; cursor: not-allowed;">
            <option value="" disabled selected>Select subcategory</option>
          </select>
          <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Subcategory cannot be edited</small>
        </div>

        <!-- Customization Content (Removed tab) -->
        <div id="editCustomizeTabContent" class="tab-content active">
          <!-- Manage Customization Fields Button (shown when subcategory is selected) -->
          <div class="form-group" id="editManageCustomizationGroup" style="display: none;">
            <button type="button" class="manage-fields-btn" id="editManageCustomizationBtn">
              <i class="fas fa-cog"></i> Manage Customization Fields
            </button>
          </div>
          <!-- Dynamic Customization Fields Container -->
          <div id="editCustomizationFields" class="customization-fields-container">
            <!-- Fields will be dynamically generated here based on category/subcategory selection -->
          </div>
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

<!-- Add Tag Modal -->
<div class="popup-overlay" id="addTagModal" style="z-index: 10001;">
  <div class="popup" style="width: 400px;">
    <span class="close-btn" id="closeTagModal">&times;</span>
    <h3>Add New Tag</h3>
    
    <div style="color: #856404; font-size: 12px; margin-bottom: 15px; padding: 10px 12px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px;">
      <i class="fas fa-exclamation-circle" style="margin-right: 5px;"></i> <strong>⚠️ Notice:</strong> This tag will <strong>NOT</strong> update the 2D Preview.
    </div>
    
    <div class="form-group">
      <label for="tagNameInput">Tag Name</label>
      <!-- Text input for regular tags -->
      <input type="text" id="tagNameInput" class="text-input" placeholder="Enter tag name">
      <!-- Dropdown for shape fields -->
      <select id="tagNameSelect" class="text-input" style="display: none;">
        <option value="">Select a shape</option>
        <option value="Rectangle">Rectangle</option>
        <option value="Round">Round</option>
        <option value="Circle">Circle</option>
        <option value="Oval">Oval</option>
        <option value="Triangle">Triangle</option>
        <option value="Pentagon">Pentagon</option>
        <option value="Hexagon">Hexagon</option>
        <option value="Octagon">Octagon</option>
        <option value="Star">Star</option>
        <option value="Diamond">Diamond</option>
        <option value="Square">Square</option>
        <option value="Others">Others</option>
      </select>
      <!-- Text input for custom shape when "Others" is selected -->
      <input type="text" id="tagNameCustomInput" class="text-input" placeholder="Enter custom shape name" style="display: none; margin-top: 8px;">
    </div>
    
    <div class="popup-actions">
      <button class="save-btn" id="confirmAddTag">Add Tag</button>
      <button class="cancel-btn" id="cancelAddTag">Cancel</button>
    </div>
  </div>
</div>


<script src="<?php echo base_url('assets/js/konva.min.js'); ?>"></script>
<script>
  const base_url = "<?= base_url(); ?>";
</script>

<!-- Konva Visual Presets - Smart auto-suggestions for tag visual configs -->
<script src="<?php echo base_url('assets/js/admin-js/konva_visual_presets.js'); ?>"></script>