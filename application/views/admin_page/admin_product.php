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
              $imagePath = '';
              
              if (!empty($imageUrl)) {
                // Check if it's a JSON array
                $decoded = json_decode($imageUrl, true);
                if (is_array($decoded) && !empty($decoded)) {
                  $firstImage = $decoded[0];
                } else {
                  // Single image (backward compatibility)
                  $firstImage = $imageUrl;
                }
                
                // Check if image path already includes a full path or is just a filename
                if (strpos($firstImage, 'broken-image-icon') !== false) {
                  // Use placeholder SVG data URI for broken image icons
                  $imagePath = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                } else if (strpos($firstImage, 'assets/') === 0 || strpos($firstImage, '/assets/') === 0) {
                  // It's an assets path, use it as-is
                  $imagePath = base_url($firstImage);
                } else if (strpos($firstImage, 'http://') === 0 || strpos($firstImage, 'https://') === 0) {
                  // It's a full URL, use it as-is
                  $imagePath = $firstImage;
                } else {
                  // It's just a filename, prepend uploads/products/
                  $imagePath = base_url('uploads/products/' . $firstImage);
                }
              } else {
                $imagePath = base_url('uploads/products/default.png');
              }
            ?>
            <img src="<?= $imagePath; ?>"
              alt="<?= $product->ProductName; ?>"
              onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';">
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
        <!-- Order Type Selection (moved to top) -->
        <div class="form-group">
          <label>Order Type</label>
          <div class="order-type-buttons">
            <button type="button" class="order-type-btn active" id="directOrderBtn" data-order-type="direct">
              Direct Order
            </button>
            <button type="button" class="order-type-btn" id="siteAssessmentBtn" data-order-type="site-assessment">
              Site Assessment Order
            </button>
          </div>
          <input type="hidden" id="productOrderType" name="orderType" value="direct">
        </div>

        <!-- Main Category Selection -->
        <div class="form-group">
          <label for="productCategory">Category</label>
          <select id="productCategory" class="input-text">
            <option value="" disabled selected>Select category</option>
            <!-- Categories will be filtered based on order type -->
          </select>
        </div>

        <!-- Subcategory Selection (appears after category is selected) -->
        <div class="form-group" id="subcategoryGroup" style="display: none;">
          <label for="productSubcategory">Subcategory</label>
          <select id="productSubcategory" class="input-text">
            <option value="" disabled selected>Select subcategory</option>
          </select>
        </div>

        <!-- Series Selection (appears after subcategory is selected, only for Windows subcategories) -->
        <div class="form-group" id="seriesGroup" style="display: none;">
          <label for="productSeries">Series <span style="color: #999; font-weight: normal;">(Optional)</span></label>
          <select id="productSeries" class="input-text">
            <option value="" selected>None</option>
            <option value="798 Series">798 Series</option>
            <option value="868-DMX Series">868-DMX Series</option>
            <option value="900 Series">900 Series</option>
            <option value="130-DMX Series">130-DMX Series</option>
          </select>
          <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Select a series to auto-fill customization fields, or choose None to configure manually</small>
        </div>

        <!-- Customization and Standard Tabs -->
        <div class="form-group">
          <div class="customization-tabs">
            <button type="button" class="tab-btn active" id="customizeTab" data-tab="customize">
              Customize Build
            </button>
            <button type="button" class="tab-btn" id="standardTab" data-tab="standard">
              Standard
            </button>
          </div>
        </div>

        <!-- Customization Tab Content -->
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

        <!-- Standard Tab Content -->
        <div id="standardTabContent" class="tab-content">
          <div class="form-group">
            <label>Standard Sizes (Series)</label>
            <small style="color: #666; font-size: 12px; display: block; margin-bottom: 8px;">Add multiple series, each with their own measurements</small>
            <div class="standard-series-container" id="standardSeriesContainer">
              <!-- Series will be added here -->
              <p style="color: #999; font-size: 13px; text-align: center; padding: 10px;">No series added yet. Click "Add Series" to start.</p>
            </div>
            <button type="button" class="add-series-btn" id="addSeriesBtn" style="margin-top: 10px;">
              <i class="fas fa-plus"></i> Add Series
            </button>
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
        <!-- Order Type Selection (moved to top) -->
        <div class="form-group">
          <label>Order Type</label>
          <div class="order-type-buttons">
            <button type="button" class="order-type-btn active" id="editDirectOrderBtn" data-order-type="direct">
              Direct Order
            </button>
            <button type="button" class="order-type-btn" id="editSiteAssessmentBtn" data-order-type="site-assessment">
              Site Assessment Order
            </button>
          </div>
          <input type="hidden" id="editProductOrderType" name="orderType" value="direct">
        </div>

        <!-- Main Category Selection (Read-only) -->
        <div class="form-group">
          <label for="editProductCategory">Category</label>
          <select id="editProductCategory" class="input-text" disabled style="opacity: 0.6; cursor: not-allowed;">
            <option value="" disabled selected>Select category</option>
            <!-- Categories will be filtered based on order type -->
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

        <!-- Series Selection (appears after subcategory is selected, only for Windows subcategories) -->
        <div class="form-group" id="editSeriesGroup" style="display: none;">
          <label for="editProductSeries">Series <span style="color: #999; font-weight: normal;">(Optional)</span></label>
          <select id="editProductSeries" class="input-text">
            <option value="" selected>None</option>
            <option value="798 Series">798 Series</option>
            <option value="868-DMX Series">868-DMX Series</option>
            <option value="900 Series">900 Series</option>
            <option value="130-DMX Series">130-DMX Series</option>
          </select>
          <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Select a series to auto-fill customization fields, or choose None to configure manually</small>
        </div>

        <!-- Customization and Standard Tabs -->
        <div class="form-group">
          <div class="customization-tabs">
            <button type="button" class="tab-btn active" id="editCustomizeTab" data-tab="customize">
              Customize Build
            </button>
            <button type="button" class="tab-btn" id="editStandardTab" data-tab="standard">
              Standard
            </button>
          </div>
        </div>

        <!-- Customization Tab Content -->
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

        <!-- Standard Tab Content -->
        <div id="editStandardTabContent" class="tab-content">
          <div class="form-group">
            <label>Standard Sizes (Series)</label>
            <small style="color: #666; font-size: 12px; display: block; margin-bottom: 8px;">Add multiple series, each with their own measurements</small>
            <div class="standard-series-container" id="editStandardSeriesContainer">
              <!-- Series will be added here -->
              <p style="color: #999; font-size: 13px; text-align: center; padding: 10px;">No series added yet. Click "Add Series" to start.</p>
            </div>
            <button type="button" class="add-series-btn" id="editAddSeriesBtn" style="margin-top: 10px;">
              <i class="fas fa-plus"></i> Add Series
            </button>
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
    <div class="form-group">
      <label for="tagPriceInput">Price per Tag (₱)</label>
      <div class="price-input">
        <span>₱</span>
        <input type="number" id="tagPriceInput" class="input-text" placeholder="0.00" step="0.01" min="0">
      </div>
    </div>
    <div class="form-group">
      <label for="tagImageInput">Tag Image (Optional)</label>
      <input type="file" id="tagImageInput" accept="image/*" style="display: none;">
      <div class="tag-image-upload-container">
        <button type="button" class="tag-image-upload-btn" id="tagImageUploadBtn">
          <i class="fas fa-image"></i> Choose Image
        </button>
        <div class="tag-image-preview" id="tagImagePreview" style="display: none;">
          <img id="tagImagePreviewImg" src="" alt="Tag preview">
          <button type="button" class="tag-image-remove" id="tagImageRemoveBtn">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </div>
    <!-- Konva Visual Configuration for 2D Preview - FULLY DYNAMIC -->
    <div class="form-group konva-visual-config" id="konvaVisualConfigGroup">
      <!-- Toggle Switch for 2D Preview -->
      <div class="visual-preview-toggle-row" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; margin-bottom: 12px; border: 1px solid #dee2e6;">
        <div style="display: flex; align-items: center; gap: 10px;">
          <i class="fas fa-palette" style="color: #6c757d; font-size: 16px;"></i>
          <div>
            <label for="enableVisualPreview" style="font-weight: 600; color: #495057; margin: 0; cursor: pointer; font-size: 13px;">Enable 2D Preview Style</label>
            <small style="display: block; color: #868e96; font-size: 11px; margin-top: 2px;">Configure how this option appears in customer's Konva.js preview</small>
          </div>
        </div>
        <label class="toggle-switch" style="position: relative; display: inline-block; width: 48px; height: 26px; flex-shrink: 0;">
          <input type="checkbox" id="enableVisualPreview" style="opacity: 0; width: 0; height: 0;">
          <span class="toggle-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .3s; border-radius: 26px;"></span>
            <style>
              .toggle-switch input:checked + .toggle-slider { background: linear-gradient(135deg, #005b82 0%, #0077a8 100%); box-shadow: 0 0 8px rgba(0,91,130,0.3); }
              .toggle-switch .toggle-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
              .toggle-switch input:checked + .toggle-slider:before { transform: translateX(22px); }
              #visualConfigContent { animation: slideDown 0.3s ease-out; }
              @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
            </style>
        </label>
      </div>
      
      <!-- Visual Config Content (hidden by default) -->
      <div id="visualConfigContent" style="display: none;">
      
      <!-- Konva Effect Type Selection -->
      <div class="konva-config-row" style="margin-bottom: 10px;">
        <label for="tagKonvaEffect" style="font-size: 12px; color: #555;">Visual Effect Type</label>
        <select id="tagKonvaEffect" class="text-input" style="width: 100%;">
          <option value="fill">Glass Fill (color + transparency)</option>
          <option value="frame">Frame/Border Style</option>
          <option value="pattern">Pattern/Texture Effect</option>
          <option value="gradient">Gradient Effect</option>
          <option value="shadow">Shadow/Glow Effect</option>
          <option value="edge">Edge Style (dashed, beveled)</option>
          <option value="overlay">Overlay/Tint Layer</option>
          <option value="custom">Custom (specify all properties)</option>
        </select>
      </div>
      
      <!-- Basic Colors Row -->
      <div class="konva-config-row" style="display: flex; gap: 12px; margin-bottom: 10px;">
        <div style="flex: 1;">
          <label for="tagFillColor" style="font-size: 12px; color: #555;">Primary Color</label>
          <div style="display: flex; align-items: center; gap: 8px;">
            <input type="color" id="tagFillColor" value="#E0F2F1" style="width: 50px; height: 32px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">
            <input type="text" id="tagFillColorHex" class="text-input" value="#E0F2F1" style="flex: 1; font-size: 12px;" placeholder="#RRGGBB">
          </div>
        </div>
        <div style="flex: 1;">
          <label for="tagStrokeColor" style="font-size: 12px; color: #555;">Secondary/Stroke Color</label>
          <div style="display: flex; align-items: center; gap: 8px;">
            <input type="color" id="tagStrokeColor" value="#333333" style="width: 50px; height: 32px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">
            <input type="text" id="tagStrokeColorHex" class="text-input" value="#333333" style="flex: 1; font-size: 12px;" placeholder="#RRGGBB">
          </div>
        </div>
      </div>
      
      <!-- Opacity and Width Row -->
      <div class="konva-config-row" style="display: flex; gap: 12px; margin-bottom: 10px;">
        <div style="flex: 1;">
          <label for="tagOpacity" style="font-size: 12px; color: #555;">Opacity: <span id="tagOpacityValue">0.9</span></label>
          <input type="range" id="tagOpacity" min="0.1" max="1" step="0.05" value="0.9" style="width: 100%;">
        </div>
        <div style="flex: 1;">
          <label for="tagStrokeWidth" style="font-size: 12px; color: #555;">Stroke Width: <span id="tagStrokeWidthValue">4</span>px</label>
          <input type="range" id="tagStrokeWidth" min="0" max="15" step="1" value="4" style="width: 100%;">
        </div>
      </div>
      
      <!-- Advanced Options (shown based on effect type) -->
      <div id="advancedKonvaOptions" style="display: none; border-top: 1px dashed #ddd; padding-top: 10px; margin-top: 10px;">
        <!-- Gradient Options -->
        <div id="gradientOptions" style="display: none; margin-bottom: 10px;">
          <label style="font-size: 12px; color: #555; font-weight: 600;">Gradient Settings</label>
          <div style="display: flex; gap: 12px; margin-top: 6px;">
            <div style="flex: 1;">
              <label for="tagGradientEnd" style="font-size: 11px; color: #777;">End Color</label>
              <input type="color" id="tagGradientEnd" value="#FFFFFF" style="width: 100%; height: 28px;">
            </div>
            <div style="flex: 1;">
              <label for="tagGradientDirection" style="font-size: 11px; color: #777;">Direction</label>
              <select id="tagGradientDirection" class="text-input" style="font-size: 11px;">
                <option value="vertical">Top to Bottom</option>
                <option value="horizontal">Left to Right</option>
                <option value="diagonal">Diagonal</option>
                <option value="radial">Radial (center)</option>
              </select>
            </div>
          </div>
        </div>
        
        <!-- Shadow Options -->
        <div id="shadowOptions" style="display: none; margin-bottom: 10px;">
          <label style="font-size: 12px; color: #555; font-weight: 600;">Shadow/Glow Settings</label>
          <div style="display: flex; gap: 12px; margin-top: 6px;">
            <div style="flex: 1;">
              <label for="tagShadowBlur" style="font-size: 11px; color: #777;">Blur: <span id="tagShadowBlurValue">10</span>px</label>
              <input type="range" id="tagShadowBlur" min="0" max="50" step="2" value="10" style="width: 100%;">
            </div>
            <div style="flex: 1;">
              <label for="tagShadowOffset" style="font-size: 11px; color: #777;">Offset: <span id="tagShadowOffsetValue">5</span>px</label>
              <input type="range" id="tagShadowOffset" min="0" max="30" step="1" value="5" style="width: 100%;">
            </div>
          </div>
          <div style="display: flex; gap: 12px; margin-top: 6px;">
            <div style="flex: 1;">
              <label for="tagShadowColor" style="font-size: 11px; color: #777;">Shadow Color</label>
              <input type="color" id="tagShadowColor" value="#000000" style="width: 100%; height: 28px;">
            </div>
            <div style="flex: 1;">
              <label for="tagShadowOpacity" style="font-size: 11px; color: #777;">Shadow Opacity: <span id="tagShadowOpacityValue">0.3</span></label>
              <input type="range" id="tagShadowOpacity" min="0" max="1" step="0.1" value="0.3" style="width: 100%;">
            </div>
          </div>
        </div>
        
        <!-- Pattern Options -->
        <div id="patternOptions" style="display: none; margin-bottom: 10px;">
          <label style="font-size: 12px; color: #555; font-weight: 600;">Pattern Settings</label>
          <div style="display: flex; gap: 12px; margin-top: 6px;">
            <div style="flex: 1;">
              <label for="tagPatternType" style="font-size: 11px; color: #777;">Pattern Type</label>
              <select id="tagPatternType" class="text-input" style="font-size: 11px;">
                <option value="none">None (Solid)</option>
                <option value="lines">Lines</option>
                <option value="grid">Grid</option>
                <option value="dots">Dots</option>
                <option value="crosshatch">Crosshatch</option>
                <option value="frosted">Frosted Glass</option>
                <option value="rain">Rain/Water Drops</option>
              </select>
            </div>
            <div style="flex: 1;">
              <label for="tagPatternDensity" style="font-size: 11px; color: #777;">Density: <span id="tagPatternDensityValue">5</span></label>
              <input type="range" id="tagPatternDensity" min="1" max="20" step="1" value="5" style="width: 100%;">
            </div>
          </div>
        </div>
        
        <!-- Edge Style Options -->
        <div id="edgeOptions" style="display: none; margin-bottom: 10px;">
          <label style="font-size: 12px; color: #555; font-weight: 600;">Edge Style Settings</label>
          <div style="display: flex; gap: 12px; margin-top: 6px;">
            <div style="flex: 1;">
              <label for="tagEdgeStyle" style="font-size: 11px; color: #777;">Edge Type</label>
              <select id="tagEdgeStyle" class="text-input" style="font-size: 11px;">
                <option value="solid">Solid</option>
                <option value="dashed">Dashed</option>
                <option value="dotted">Dotted</option>
                <option value="double">Double Line</option>
                <option value="beveled">Beveled (3D effect)</option>
                <option value="rounded">Rounded/Smooth</option>
              </select>
            </div>
          </div>
          
          <!-- Corner Radius Controls -->
          <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 6px; border: 1px solid #e9ecef;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
              <label style="font-size: 11px; color: #555; font-weight: 600;"><i class="fas fa-border-style"></i> Corner Radius</label>
              <label style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #666; cursor: pointer;">
                <input type="checkbox" id="linkCornerRadius" checked style="width: 14px; height: 14px; cursor: pointer;">
                <span>Link All</span>
              </label>
            </div>
            
            <!-- Visual Corner Grid -->
            <div id="cornerRadiusGrid" style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 4px; align-items: center;">
              <!-- Top Row -->
              <div style="text-align: center;">
                <label style="font-size: 10px; color: #888; display: block;">Top Left</label>
                <input type="number" id="tagCornerRadiusTL" min="0" max="50" value="0" style="width: 50px; text-align: center; font-size: 11px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
              </div>
              <div style="width: 40px;"></div>
              <div style="text-align: center;">
                <label style="font-size: 10px; color: #888; display: block;">Top Right</label>
                <input type="number" id="tagCornerRadiusTR" min="0" max="50" value="0" style="width: 50px; text-align: center; font-size: 11px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
              </div>
              
              <!-- Middle Visual Preview -->
              <div style="grid-column: 1 / -1; display: flex; justify-content: center; padding: 6px 0;">
                <div id="cornerPreviewBox" style="width: 60px; height: 40px; border: 2px solid #6c5ce7; background: linear-gradient(135deg, #e8e4ff 0%, #f8f9fa 100%); border-radius: 0px;"></div>
              </div>
              
              <!-- Bottom Row -->
              <div style="text-align: center;">
                <label style="font-size: 10px; color: #888; display: block;">Bottom Left</label>
                <input type="number" id="tagCornerRadiusBL" min="0" max="50" value="0" style="width: 50px; text-align: center; font-size: 11px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
              </div>
              <div style="width: 40px;"></div>
              <div style="text-align: center;">
                <label style="font-size: 10px; color: #888; display: block;">Bottom Right</label>
                <input type="number" id="tagCornerRadiusBR" min="0" max="50" value="0" style="width: 50px; text-align: center; font-size: 11px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
              </div>
            </div>
            
            <!-- Quick All Corners Slider -->
            <div id="allCornersSlider" style="margin-top: 10px;">
              <label style="font-size: 10px; color: #777;">All Corners: <span id="tagCornerRadiusValue">0</span>px</label>
              <input type="range" id="tagCornerRadius" min="0" max="50" step="1" value="0" style="width: 100%;">
            </div>
          </div>
        </div>
      </div>
      
      <!-- Live Preview Canvas -->
      <div style="margin-top: 12px;">
        <label style="font-size: 12px; color: #555;">Live Preview:</label>
        <div id="tagKonvaPreview" style="width: 100%; height: 100px; background: linear-gradient(45deg, #f0f0f0 25%, transparent 25%, transparent 75%, #f0f0f0 75%), linear-gradient(45deg, #f0f0f0 25%, transparent 25%, transparent 75%, #f0f0f0 75%); background-size: 20px 20px; background-position: 0 0, 10px 10px; border: 1px solid #ddd; border-radius: 4px; margin-top: 4px;"></div>
        <small style="color: #888; font-size: 10px;">Checkered background helps visualize transparency</small>
      </div>
      
      </div><!-- End visualConfigContent -->
    </div>
    <div class="popup-actions">
      <button class="save-btn" id="confirmAddTag">Add Tag</button>
      <button class="cancel-btn" id="cancelAddTag">Cancel</button>
    </div>
  </div>
</div>


<script src="https://unpkg.com/konva@9.3.6/konva.min.js"></script>
<script>
  const base_url = "<?= base_url(); ?>";
</script>

<!-- Konva Visual Presets - Smart auto-suggestions for tag visual configs -->
<script src="<?php echo base_url('assets/js/admin-js/konva_visual_presets.js'); ?>"></script>