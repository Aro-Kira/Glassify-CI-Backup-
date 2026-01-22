<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/2DModeling_styles.css'); ?>">

<script src="<?php echo base_url('assets/js/konva.min.js'); ?>"></script>
<!-- Comprehensive 2D Renderer -->
<script src="<?php echo base_url('assets/js/2d-functions/comprehensive_2d_renderer.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/2d-functions/comprehensive_renderer_integration.js'); ?>"></script>

<body data-customer-id="<?= $this->session->userdata('customer_id') ?: '' ?>">

    <div id="upload-modal" class="modal-backdrop hidden-step">
        <div class="modal-content">
            <div class="modal-header">
                <h2>File Upload</h2>
                <button class="modal-close" id="modal-close-btn">&times;</button>
            </div>

            <div class="upload-area">
                <div class="dropzone" id="dropzone">
                    <div class="dropzone-icon">
                        <svg viewBox="0 0 24 24" width="60" height="60" fill="none" stroke="#003b4d" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <path d="M12 17v-4"></path>
                            <path d="M10 15l2-2 2 2"></path>
                        </svg>
                    </div>

                    <p class="upload-title">Choose a file or drag & drop it here</p>
                    <p class="upload-support-info">
                        Supported file types: JPG, PNG, PDF<br>
                        Maximum size: 25MB
                    </p>

                    <input type="file" id="file-input" multiple accept=".jpg,.jpeg,.png,.pdf" class="hidden-step">

                    <button class="browse-btn" id="browse-files-btn">Browse Files</button>
                </div>

                <div class="uploaded-files-list">
                    <h3 class="uploaded-files-title">Uploaded Files</h3>
                    <div id="uploaded-files-container">
                        <p class="placeholder-text">No files uploaded yet.</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="secondary-btn" id="modal-cancel-btn">Cancel</button>
                <button class="primary-btn" id="modal-done-btn">Done</button>
            </div>
        </div>
    </div>


    <div class="breadcrumb-strip">
        <div class="page-title">Products & Services</div>
        <div class="breadcrumbs" id="breadcrumbs-container">
            <span>Products</span>
            <span class="chevron-right"></span>
            <span class="active" id="crumb-main">Glass Shape</span>
        </div>
    </div>

    <main class="container">

        <section class="product-gallery">
            <div class="main-image-container">
                <?php if (isset($product) && $product): ?>
                    <?php 
                    // Handle ImageUrl - it might be JSON array or single string
                    $imageUrl = $product->ImageUrl ?? '';
                    $productImages = [];
                    $imagePaths = [];
                    $placeholderSvg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                    
                    if (!empty($imageUrl)) {
                        $decoded = json_decode($imageUrl, true);
                        if (is_array($decoded) && !empty($decoded)) {
                            $productImages = $decoded;
                        } else {
                            $productImages = [$imageUrl];
                        }
                    }
                    
                    // Build proper image paths
                    foreach ($productImages as $image) {
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
                    
                    if (empty($imagePaths)) {
                        $imagePaths = [$placeholderSvg];
                    }
                    
                    $totalImages = count($imagePaths);
                    ?>
                    <div class="product-info" id="product-image-container">
                        <?php if (!empty($imagePaths)): ?>
                            <?php foreach ($imagePaths as $index => $imgPath): ?>
                                <img src="<?= htmlspecialchars($imgPath) ?>"
                                    alt="<?= htmlspecialchars($product->ProductName ?? 'Product') ?>" 
                                    class="main-product-image <?= $index === 0 ? 'active' : '' ?>"
                                    data-image-index="<?= $index ?>"
                                    style="<?= $index === 0 ? '' : 'display: none;' ?>"
                                    onerror="this.onerror=null; this.src='<?= $placeholderSvg ?>';">
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (empty($productImages)): ?>
                            <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
                                No Image Available
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
                        No Image Available
                    </div>
                <?php endif; ?>

                <?php if (isset($totalImages) && $totalImages > 1): ?>
                    <div class="gallery-nav">
                        <button class="nav-arrow" id="prev-image">&lt;</button>
                        <button class="nav-arrow" id="next-image">&gt;</button>
                    </div>
                    <div class="image-counter" id="image-counter">1/<?= $totalImages ?></div>
                <?php else: ?>
                    <div class="image-counter" id="image-counter" style="display: none;">1/1</div>
                <?php endif; ?>
            </div>

            <div class="diagram-container">
                <div id="konva-container" class="konva-wrapper"></div>
                <div class="preview-label" style="cursor: pointer;">2D Preview <span style="font-size: 0.8em;">(Click to enlarge)</span></div>
            </div>
            <button class="upload-btn" id="open-modal-btn">
                Upload a File
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48">
                    </path>
                </svg>
            </button>
            
            <!-- External Uploaded Files Display (outside modal) -->
            <div class="external-uploaded-files-list" id="external-uploaded-files-list" style="display: none; margin-top: 15px;">
                <h3 class="external-uploaded-files-title" style="font-size: 0.9rem; font-weight: 600; margin-bottom: 10px; color: #02455F;">Uploaded Files</h3>
                <div id="external-uploaded-files-container" style="display: flex; gap: 10px; overflow-x: auto; padding: 10px 0; max-height: 120px;">
                    <p class="placeholder-text" style="font-style: italic; color: #666; text-align: center; padding: 10px;">No files uploaded yet.</p>
                </div>
                <div class="external-files-scroll-nav" style="display: flex; gap: 10px; justify-content: center; margin-top: 10px;">
                    <button class="scroll-arrow left hidden" style="background: #02455F; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; display: none;">&lt;</button>
                    <button class="scroll-arrow right hidden" style="background: #02455F; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; display: none;">&gt;</button>
                </div>
            </div>
        </section>

        <section class="product-details">
            <div class="title-row">
                <div>
                    <?php if (isset($product) && $product): ?>
                        <div class="product-info">
                            <h2><?= htmlspecialchars($product->ProductName) ?></h2>
                        </div>
                    <?php endif; ?>

                    <p id="standard-subtitle" class="subtitle hidden-step">Start building today!</p>
                </div>
                <button class="wishlist-btn" id="add-to-wishlist-btn" data-product-id="<?= isset($product) && $product ? $product->Product_ID : '' ?>" title="Add to Wishlist">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                </button>
            </div>

            <div class="build-toggle">
                <button class="toggle-btn active" id="btn-customize">Customize Build</button>
                <div class="divider-v"></div>
                <button class="toggle-btn inactive" id="btn-standard">Standard</button>
            </div>

            <div class="price-box" id="price-box">
                <div class="price-main">
                    <span class="price-label">Estimated Price</span>
                    <span class="price-value" id="total-price">₱0.00</span>
                </div>
                <!-- Estimated Price Breakdown (always visible) -->
                <div class="estimated-price-breakdown" id="estimated-price-breakdown">
                    <!-- Dimension row (always shown) -->
                    <div class="breakdown-row" data-field-id="dimension">
                        <span>Dimension:</span>
                        <span id="estimated-dimension">-</span>
                    </div>
                    <!-- Base Area Cost (always shown) -->
                    <div class="breakdown-row" data-field-id="baseArea">
                        <span>Base Area Cost:</span>
                        <span id="estimated-cost-area">₱0.00</span>
                    </div>
                    <!-- Dynamic fields will be inserted here -->
                    <div id="estimated-dynamic-breakdown-rows"></div>
                </div>
                <div class="price-breakdown" id="price-breakdown">
                    <div class="breakdown-toggle" id="breakdown-toggle">
                        <span>View Price Breakdown</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                    <div class="breakdown-details hidden-step" id="breakdown-details">
                        <!-- Base Area Cost (always shown) -->
                        <div class="breakdown-row" data-field-id="baseArea">
                            <span>Base Area Cost:</span>
                            <span id="cost-area">₱0.00</span>
                        </div>
                        <!-- Dynamic fields will be inserted here -->
                        <div id="dynamic-breakdown-rows"></div>
                        <!-- Total (always shown at bottom) -->
                        <div class="breakdown-row breakdown-total" id="breakdown-total-row" style="display: none;">
                            <span style="font-weight: bold;">Total:</span>
                            <span id="cost-total" style="font-weight: bold; color: #ee4d2d;">₱0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AJAX Status Indicator -->
            <div id="ajax-status-indicator" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 10000; padding: 10px 15px; border-radius: 4px; font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: all 0.3s ease;">
                <span id="ajax-status-text">Saving...</span>
            </div>

            <style>
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                @keyframes fadeOut {
                    from { opacity: 1; transform: translateY(0); }
                    to { opacity: 0; transform: translateY(-10px); }
                }
                #ajax-status-indicator {
                    animation: fadeIn 0.3s ease;
                }
            </style>

            <div id="custom-wrapper">
                <!-- Default Size Fields (Height & Width) - Only visible on Step 1 -->
                <div class="dimensions-container" id="dimensions-container">
                    <div class="input-group">
                        <label class="section-label">Height</label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-height" name="height" value="45" min="0" step="0.1" placeholder="45">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-height" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>
                                </button>
                                <div class="unit-dropdown hidden-step" id="dropdown-height">
                                    <div class="unit-option" data-value="in">Inches</div>
                                    <div class="unit-option" data-value="cm">Centimeters</div>
                                    <div class="unit-option" data-value="mm">Millimeters</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Lock/Unlock Button -->
                    <div class="dimension-lock-container">
                        <button type="button" id="dimension-lock-btn" class="dimension-lock-btn" title="Lock dimensions to keep height and width equal">
                            <svg id="lock-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <svg id="unlock-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="input-group">
                        <label class="section-label">Width</label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-width" name="width" value="35" min="0" step="0.1" placeholder="35">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-width" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>
                                </button>
                                <div class="unit-dropdown hidden-step" id="dropdown-width">
                                    <div class="unit-option" data-value="in">Inches</div>
                                    <div class="unit-option" data-value="cm">Centimeters</div>
                                    <div class="unit-option" data-value="mm">Millimeters</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Inner Height (h1) - Sliding section - Only visible when transom is selected -->
                    <div class="input-group hidden-step" id="input-group-h1">
                        <label class="section-label">Inner Height (h1) - Sliding <span style="color: #0066CC; font-weight: bold;">●</span></label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-h1" name="h1" value="" min="0" step="0.1" placeholder="0.0">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-h1" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>
                                </button>
                                <div class="unit-dropdown hidden-step" id="dropdown-h1">
                                    <div class="unit-option" data-value="in">Inches</div>
                                    <div class="unit-option" data-value="cm">Centimeters</div>
                                    <div class="unit-option" data-value="mm">Millimeters</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fixed Transom Height (h2) - Fixed section - Only visible when transom is selected -->
                    <div class="input-group hidden-step" id="input-group-h2">
                        <label class="section-label">Fixed Transom Height (h2) <span style="color: #00AA00; font-weight: bold;">●</span></label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-h2" name="h2" value="" min="0" step="0.1" placeholder="0.0">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-h2" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>
                                </button>
                                <div class="unit-dropdown hidden-step" id="dropdown-h2">
                                    <div class="unit-option" data-value="in">Inches</div>
                                    <div class="unit-option" data-value="cm">Centimeters</div>
                                    <div class="unit-option" data-value="mm">Millimeters</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic customization fields will be rendered here -->
                <div id="dynamic-customization-container">
                    <!-- Fields will be dynamically generated based on product configuration -->
                </div>

                <div class="action-area">
                    <div class="action-group left hidden-step" id="back-group">
                        <button class="nav-btn back-btn" id="back-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            Back
                        </button>
                        <p class="footer-note" id="back-note">Glass Shape</p>
                    </div>
                    <div class="action-group right">
                        <button class="nav-btn next-btn" id="next-btn">
                            Next
                            <svg viewBox="0 0 24 24">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                        <p class="footer-note" id="next-note">Glass Type & Thickness</p>
                    </div>
                </div>
            </div>

            <div id="standard-wrapper" class="hidden-step">
                <!-- Dynamic standard sizes will be rendered here -->
                <div id="dynamic-standard-container">
                    <!-- Standard series and sizes will be dynamically generated based on product configuration -->
                </div>

                <div class="engraving-section" style="margin-top: 30px; margin-bottom: 60px;">
                    <label class="section-label">Engraving (Optional)</label>
                    <div class="input-wrapper">
                        <input type="text" placeholder=""
                            style="border: none; width: 100%; padding: 12px; font-family: inherit; background: #f8f9fa;">
                    </div>
                </div>

                <div class="action-area" style="justify-content: center;">
                    <button class="nav-btn next-btn">
                        Finalize Order
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="summary-wrapper" class="hidden-step">
                <h2 class="summary-title">Review your order</h2>
                
                <!-- Warning Message -->
                <div class="price-warning" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 12px 16px; margin: 20px 0; color: #856404;">
                    <strong style="display: block; margin-bottom: 4px;">⚠️ Important Notice:</strong>
                    <span>The estimated price shown is subject to change after the ocular visit. Final pricing will be confirmed following site assessment and verification of specifications.</span>
                </div>

                <!-- Design Preview Section -->
                <div class="design-preview-section">
                    <h3 class="design-preview-title">Your Custom Design</h3>
                    <div class="design-preview-container">
                        <img id="design-preview-img" src="" alt="Custom Design Preview">
                    </div>
                    <p class="design-preview-note">This design layout will be saved with your order for quotation and invoice purposes.</p>
                </div>

                <div class="summary-table-container">
                    <div class="summary-header">
                        Price Breakdown
                    </div>
                    <div class="summary-content" id="summary-content">
                        <!-- Dimension row (always shown) -->
                        <div class="summary-row">
                            <span class="spec-label">Dimension:</span>
                            <span class="spec-value">
                                <span id="sum-dim">45" x 35"</span>
                                <span class="price-addon" id="sum-dim-price">Base: ₱0.00</span>
                            </span>
                        </div>
                        <!-- Dynamic fields will be inserted here -->
                        <div id="dynamic-summary-rows"></div>
                        <!-- Engraving row (if applicable) -->
                        <div class="summary-row" id="summary-engraving-row" style="display: none;">
                            <span class="spec-label">Engraving:</span>
                            <span class="spec-value" id="sum-engrave">None</span>
                        </div>

                        <div class="summary-row total-row">
                            <span class="spec-label">Total</span>
                            <span class="spec-value price-final" id="sum-total">₱0.00</span>
                        </div>
                    </div>
                </div>

                <div class="summary-actions">
                    <button class="cart-btn" id="add-to-cart-btn" data-product-id="<?= isset($product) && $product ? $product->Product_ID : '' ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Add to Cart
                    </button>

                    <?php
                    // Determine order type
                    $orderType = isset($product->OrderType) ? strtolower($product->OrderType) : 'direct';
                    $isSiteAssessment = ($orderType === 'site-assessment' || $orderType === 'site-assessed');
                    ?>
                    
                    <?php if ($isSiteAssessment): ?>
                        <!-- Site Assessment Order: Show Book Now button -->
                        <button class="buy-btn" id="book-now-btn" data-product-id="<?= isset($product) && $product ? $product->Product_ID : '' ?>">
                            Book Now
                        </button>
                    <?php else: ?>
                        <!-- Direct Order: Show Buy Now button -->
                        <button class="buy-btn" id="buy-now-btn" data-product-id="<?= isset($product) && $product ? $product->Product_ID : '' ?>">
                            Buy Now
                        </button>
                    <?php endif; ?>

                    <button class="edit-order-btn" id="edit-order-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                        Edit Configuration
                    </button>
                </div>


            </div>

            <!-- Preview Modal for enlarged Konva canvas -->
            <div id="preview-modal" class="modal-backdrop hidden-step">
                <div class="preview-modal-content">
                    <button class="preview-close-btn" id="preview-close-btn">&times;</button>
                    <img id="zoomed-preview-img" src="" alt="Design Preview">
                    <div class="preview-modal-actions">
                        <button class="download-design-btn" id="download-design-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Download Design
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php if (isset($product) && !empty($product->Description)): ?>
    <section id="product-description-section" class="full-width-section product-description-section">
        <div class="inner-content">
            <h2 class="section-title">Product Descriptions</h2>
            <div class="product-description-content">
                <?= nl2br(htmlspecialchars($product->Description)) ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section id="related-products-section" class="full-width-section dark-bg">
        <div class="inner-content">
            <h2 class="section-title-white">You May Also Like</h2>
            <div class="products-grid">
                <?php if (isset($recommendations) && !empty($recommendations)): ?>
                    <?php 
                    // Limit to exactly 4 cards and randomize
                    $recommendations_array = is_array($recommendations) ? $recommendations : (array)$recommendations;
                    
                    // Shuffle array to randomize
                    shuffle($recommendations_array);
                    
                    // Limit to 4 items, excluding current product
                    $limited_recommendations = [];
                    foreach ($recommendations_array as $rec_product) {
                        // Skip the current product being viewed
                        if (isset($product) && $rec_product->Product_ID == $product->Product_ID) {
                            continue;
                        }
                        $limited_recommendations[] = $rec_product;
                        if (count($limited_recommendations) >= 4) {
                            break;
                        }
                    }
                    
                    foreach ($limited_recommendations as $rec_product): 
                    ?>
                        <div class="product-card">
                            <div class="p-image">
                                <?php 
                                $rec_images = [];
                                $placeholderSvg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                                
                                if (!empty($rec_product->ImageUrl)) {
                                    $decoded = json_decode($rec_product->ImageUrl, true);
                                    if (is_array($decoded)) {
                                        $rec_images = $decoded;
                                    } else {
                                        $rec_images = [$rec_product->ImageUrl];
                                    }
                                }
                                
                                $image_url = $placeholderSvg;
                                if (!empty($rec_images)) {
                                    $firstImg = trim($rec_images[0]);
                                    if (!empty($firstImg) && strpos($firstImg, 'broken-image-icon') === false) {
                                        $firstImg = ltrim($firstImg, '/');
                                        if (strpos($firstImg, 'http://') === 0 || strpos($firstImg, 'https://') === 0) {
                                            $image_url = $firstImg;
                                        } else if (strpos($firstImg, 'assets/') === 0) {
                                            $image_url = base_url($firstImg);
                                        } else if (strpos($firstImg, 'uploads/') === 0) {
                                            $image_url = base_url($firstImg);
                                        } else {
                                            $filename = basename($firstImg);
                                            $image_url = base_url('uploads/products/' . $filename);
                                        }
                                    }
                                }
                                ?>
                                <img src="<?= htmlspecialchars($image_url) ?>" alt="<?= htmlspecialchars($rec_product->ProductName) ?>" onerror="this.onerror=null; this.src='<?= $placeholderSvg ?>';">
                            </div>
                            <div class="p-info">
                                <p><?= htmlspecialchars($rec_product->ProductName) ?></p>
                                <button class="yellow-btn" onclick="window.location.href='<?= base_url('2DModeling?id=' . $rec_product->Product_ID) ?>'">Build and Buy</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback if no recommendations available -->
                    <p style="color: #fff; text-align: center; padding: 20px; grid-column: 1 / -1;">No products available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

  <!-- Testimonials -->
  <section id="testimonials-section" class="testimonials">
    <h2>Customer Testimonials</h2>
    <div class="testimonial-content">
      <button class="testimonial-arrow left">
        <img src="<?php echo base_url(''); ?>assets/images/img-page/testimonials-arrow.png" alt="Previous">
      </button>

      <div class="testimonial-wrapper">
        <div class="testimonial-text active">
          <p>Highly recommending this shop! Very smooth and fast transaction. Despite unfortunate events, they were
            still able to deliver. Owner and staff are committed at great service. Exceeds expectations. Will definitely
            be our go-to-shop for glass and aluminum.</p>
          <div class="stars">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
          </div>
          <h3 class="author">Kris-Ann Munda-Rebullana</h3>
        </div>

        <div class="testimonial-text">
          <p>Highly recommended ⭐⭐⭐⭐⭐ Very accommodating staff. Responded immediately to queries and concerns. Quality
            materials and great workmanship. We'll ask them DEFINITELY to do collab again in our next project 👍👍</p>
          <div class="stars">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
          </div>
          <h3 class="author">Anne Cruz</h3>
        </div>

        <div class="testimonial-text">
          <p>Highly recommended! GlassWorth Builders service was excellent, and the quality of materials was top-notch.
            Their installers were kind and demonstrated good workmanship. I'm thoroughly impressed!</p>
          <div class="stars">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
          </div>
          <h3 class="author">Jandoc Jun</h3>
        </div>
      </div>

      <button class="testimonial-arrow right">
        <img src="<?php echo base_url('assets/images/img-page/testimonials-arrow.png'); ?>" alt="Next">
      </button>
    </div>
  </section>
</body>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // PHP → JS: Pass selected product data
    <?php
    // Get price from database - use Price if available, otherwise PriceMin, otherwise 0
    $productPrice = 0;
    if (isset($product) && $product) {
        if (isset($product->Price) && $product->Price !== null && $product->Price !== '') {
            $productPrice = floatval($product->Price);
        } elseif (isset($product->PriceMin) && $product->PriceMin !== null && $product->PriceMin !== '') {
            $productPrice = floatval($product->PriceMin);
        }
    }
    ?>
    window.productBasePrice = <?= $productPrice ?>;
    const productBasePrice = window.productBasePrice;
    var base_url = '<?= base_url(); ?>';
    var BASE_URL = '<?= base_url(); ?>';
    var PAYMENT_URL = '<?= rtrim(base_url(), "/") . "/payment"; ?>';
    
    // Product images for gallery navigation
    <?php if (isset($product) && $product && isset($totalImages) && $totalImages > 1): ?>
    window.productImages = <?= json_encode($productImages ?? []) ?>;
    window.totalProductImages = <?= $totalImages ?>;
    <?php else: ?>
    window.productImages = [];
    window.totalProductImages = 0;
    <?php endif; ?>
</script>

<script src="<?= base_url('assets/js/2d-functions/dynamic_customization.js'); ?>"></script>
<script src="<?= base_url('assets/js/2d-functions/2d_customization.js'); ?>"></script>
<script src="<?= base_url('assets/js/windows_visual_configs.js'); ?>"></script>
<script src="<?= base_url('assets/js/2d-functions/customization_ajax.js'); ?>"></script>
<script src="<?= base_url('assets/js/2d-functions/addtocustomization.js'); ?>"></script>
<script src="<?= base_url('assets/js/2d-functions/addtowishlist.js'); ?>"></script>
<script>
// Product Image Gallery Navigation
(function() {
    const productImages = window.productImages || [];
    const totalImages = window.totalProductImages || 0;
    
    if (totalImages > 1) {
        let currentImageIndex = 0;
        const imageContainer = document.getElementById('product-image-container');
        const images = imageContainer ? imageContainer.querySelectorAll('.main-product-image') : [];
        const prevBtn = document.getElementById('gallery-prev-btn');
        const nextBtn = document.getElementById('gallery-next-btn');
        const counter = document.getElementById('image-counter');
        
        function showImage(index) {
            images.forEach((img, i) => {
                img.classList.toggle('active', i === index);
            });
            if (counter) {
                counter.textContent = `${index + 1}/${totalImages}`;
            }
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
                showImage(currentImageIndex);
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentImageIndex = (currentImageIndex + 1) % totalImages;
                showImage(currentImageIndex);
            });
        }
        
        // Initialize
        showImage(0);
    }
})();

// Testimonial script (inline to avoid nextBtn conflict)
(function() {
    const testimonials = document.querySelectorAll('.testimonial-text');
    const prevBtn = document.querySelector('.testimonial-arrow.left');
    const testimonialNextBtn = document.querySelector('.testimonial-arrow.right');
    let currentIndex = 0;

    function showTestimonial(index) {
        testimonials.forEach((t, i) => {
            t.classList.toggle('active', i === index);
        });
    }

    if (testimonialNextBtn) {
        testimonialNextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % testimonials.length;
            showTestimonial(currentIndex);
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + testimonials.length) % testimonials.length;
            showTestimonial(currentIndex);
        });
    }
})();
</script>



<?php if ($product): ?>
    <script>
        // Pass Product Info From PHP → JavaScript
        // Handle ImageUrl - it might be JSON array or single string
        <?php 
        $imageUrl = $product->ImageUrl ?? '';
        $imageSrc = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
        
        if (!empty($imageUrl)) {
            $decoded = json_decode($imageUrl, true);
            $firstImage = '';
            if (is_array($decoded) && !empty($decoded[0])) {
                $firstImage = trim($decoded[0]);
            } else {
                $firstImage = trim($imageUrl);
            }
            
            if (!empty($firstImage) && strpos($firstImage, 'broken-image-icon') === false) {
                $firstImage = ltrim($firstImage, '/');
                if (strpos($firstImage, 'http://') === 0 || strpos($firstImage, 'https://') === 0) {
                    $imageSrc = $firstImage;
                } else if (strpos($firstImage, 'assets/') === 0) {
                    $imageSrc = base_url($firstImage);
                } else if (strpos($firstImage, 'uploads/') === 0) {
                    $imageSrc = base_url($firstImage);
                } else {
                    $filename = basename($firstImage);
                    $imageSrc = base_url('uploads/products/' . $filename);
                }
            }
        }
        ?>
        
        <?php
        // Get price from database - use Price if available, otherwise PriceMin, otherwise 0
        $productPriceForJS = 0;
        if (isset($product) && $product) {
            if (isset($product->Price) && $product->Price !== null && $product->Price !== '') {
                $productPriceForJS = floatval($product->Price);
            } elseif (isset($product->PriceMin) && $product->PriceMin !== null && $product->PriceMin !== '') {
                $productPriceForJS = floatval($product->PriceMin);
            }
        }
        ?>
        const selectedProduct = {
            id: "<?= isset($product) && $product ? $product->Product_ID : '' ?>",
            name: <?= json_encode($product->ProductName ?? '') ?>,
            price: <?= $productPriceForJS ?>,
            priceMin: <?= isset($product->PriceMin) && $product->PriceMin !== null && $product->PriceMin !== '' ? floatval($product->PriceMin) : 'null' ?>,
            priceMax: <?= isset($product->PriceMax) && $product->PriceMax !== null && $product->PriceMax !== '' ? floatval($product->PriceMax) : 'null' ?>,
            category: <?= json_encode($product->Category ?? '') ?>,
            subcategory: <?= json_encode($product->Subcategory ?? '') ?>,
            material: <?= json_encode($product->Material ?? '') ?>,
            image: <?= json_encode($imageSrc) ?>,
            customizationFieldKey: <?= json_encode($customizationFieldKey ?? null) ?>,
            tagPrices: <?= json_encode(empty($tagPrices) ? new stdClass() : $tagPrices) ?>,
            tagImages: <?= json_encode(empty($tagImages) ? new stdClass() : $tagImages) ?>,
            tagVisualConfigs: <?= json_encode(empty($tagVisualConfigs) ? new stdClass() : $tagVisualConfigs) ?>,
            standardSeries: <?= json_encode($standardSeries ?? []) ?>,
            selectedOptions: <?= json_encode($productSelectedOptions ?? []) ?> // Admin-selected tags to filter options
        };

        console.log("=== PRODUCT DATA DEBUG ===");
        console.log("Loaded Product From PHP:", selectedProduct);
        console.log("Product ID:", selectedProduct.id);
        console.log("Category:", selectedProduct.category);
        console.log("Subcategory:", selectedProduct.subcategory);
        console.log("Customization Field Key:", selectedProduct.customizationFieldKey);
        console.log("Tag Prices:", selectedProduct.tagPrices);
        console.log("Tag Prices Count:", Object.keys(selectedProduct.tagPrices || {}).length);
        console.log("=== TAG VISUAL CONFIGS (2D Preview Styles) ===");
        console.log("Tag Visual Configs:", selectedProduct.tagVisualConfigs);
        console.log("Tag Visual Configs Count:", Object.keys(selectedProduct.tagVisualConfigs || {}).length);
        if (selectedProduct.tagVisualConfigs) {
            Object.keys(selectedProduct.tagVisualConfigs).forEach(fieldId => {
                console.log(`  Field "${fieldId}":`, selectedProduct.tagVisualConfigs[fieldId]);
            });
        }
        console.log("Standard Series:", selectedProduct.standardSeries);
        console.log("Standard Series Count:", (selectedProduct.standardSeries || []).length);
        console.log("Base URL:", base_url);

        // Initialize dynamic customization when DOM is ready
        document.addEventListener('DOMContentLoaded', async () => {
            // Set global reference for dynamic_customization.js
            if (typeof window !== 'undefined') {
                window.selectedProduct = selectedProduct;
            }

            // Wait a bit for 2d_customization.js to load
            setTimeout(async () => {
                console.log("=== LOADING CUSTOMIZATION FIELDS ===");
                
                // Load customization fields from API (from customization_field_configs table)
                let customizationFields = [];
                let stepNamesFromAPI = null;
                if (selectedProduct.customizationFieldKey) {
                    const apiUrl = base_url + 'customizationFields/get?fieldKey=' + encodeURIComponent(selectedProduct.customizationFieldKey);
                    console.log("Fetching from API:", apiUrl);
                    
                    try {
                        const response = await fetch(apiUrl);
                        console.log("API Response Status:", response.status);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const result = await response.json();
                        console.log("API Response:", result);
                        console.log("API Response fields:", result.fields);
                        console.log("API Response fields type:", typeof result.fields);
                        console.log("API Response fields length:", result.fields ? result.fields.length : 'N/A');
                        
                        if (result.status === 'success') {
                            // Check if fields is an array and has items
                            if (Array.isArray(result.fields) && result.fields.length > 0) {
                                customizationFields = result.fields;
                                
                                // Extract step names from fields array if they exist
                                // Step names might be stored as a separate field in the config
                                const stepNamesKey = selectedProduct.customizationFieldKey + '_stepNames';
                                if (result[stepNamesKey]) {
                                    stepNamesFromAPI = result[stepNamesKey];
                                } else if (result.stepNames) {
                                    stepNamesFromAPI = result.stepNames;
                                }
                                
                                console.log('✅ Loaded customization fields from database:', customizationFields);
                                console.log('✅ Fields count:', customizationFields.length);
                                if (stepNamesFromAPI) {
                                    console.log('✅ Loaded step names from database:', stepNamesFromAPI);
                                }
                            } else {
                                console.warn('⚠️ API returned success but fields array is empty or invalid:', result.fields);
                            }
                        } else {
                            console.warn('⚠️ API returned error status:', result);
                        }
                    } catch(e) {
                        console.error('❌ Error loading customization fields from API:', e);
                        console.error('Error details:', e.message);
                    }
                } else {
                    console.warn('⚠️ No customizationFieldKey available');
                }
                
                // If no fields came back, we intentionally do NOT fall back to localStorage.
                // localStorage can be stale per-browser and will break "sync" with admin DB updates.
                // The API already returns JSON-backed defaults when the DB has no entry.
                if (customizationFields.length === 0) {
                    console.warn('⚠️ No customization fields returned. Ensure the fieldKey exists or defaults are configured.');
                }
                
                        // Store step names globally for navigation
                if (stepNamesFromAPI) {
                    window.customizationStepNames = stepNamesFromAPI;
                }

                // Load Windows-specific visual configurations if this is a Windows product
                if (selectedProduct.category === 'Windows' && window.windowsVisualConfigs) {
                    console.log('Loading Windows visual configurations for 2D preview');
                    if (typeof window.loadDynamicVisualConfigs === 'function') {
                        // Convert Windows visual configs to the format expected by loadDynamicVisualConfigs
                        const tagVisualConfigs = {};
                        Object.keys(window.windowsVisualConfigs).forEach(fieldId => {
                            tagVisualConfigs[fieldId] = window.windowsVisualConfigs[fieldId];
                        });
                        window.loadDynamicVisualConfigs(tagVisualConfigs);
                        console.log('✅ Windows visual configurations loaded');
                    }
                }

                // =====================================================
                // CRITICAL: Load visual configs BEFORE rendering fields
                // This ensures 2D preview colors sync from admin to customer
                // =====================================================
                console.log("=== LOADING VISUAL CONFIGS FOR 2D PREVIEW ===");
                if (selectedProduct.tagVisualConfigs && Object.keys(selectedProduct.tagVisualConfigs).length > 0) {
                    if (typeof window.loadDynamicVisualConfigs === 'function') {
                        window.loadDynamicVisualConfigs(selectedProduct.tagVisualConfigs);
                        console.log('✅ Visual configs loaded from admin settings');
                    } else {
                        console.warn('⚠️ loadDynamicVisualConfigs not available yet, will retry after render');
                        // Store for later loading
                        window.pendingVisualConfigs = selectedProduct.tagVisualConfigs;
                    }
                } else {
                    console.log('ℹ️ No custom visual configs defined for this product');
                }
                
                // Render customization fields if available
                console.log("=== RENDERING CUSTOMIZATION FIELDS ===");
                console.log("Fields to render:", customizationFields);
                console.log("Fields count:", customizationFields.length);
                
                if (customizationFields.length > 0) {
                    const customContainer = document.getElementById('dynamic-customization-container');
                    console.log("Custom container found:", !!customContainer);
                    console.log("renderDynamicCustomizationFields function exists:", typeof renderDynamicCustomizationFields === 'function');
                    console.log("Selected options for filtering:", selectedProduct.selectedOptions);
                    
                    if (customContainer && typeof renderDynamicCustomizationFields === 'function') {
                        // Use step names from API or default
                        const stepNamesToUse = window.customizationStepNames || null;
                        
                        // Pass selectedOptions to filter which tags are shown to customer
                        // Only tags selected by admin will be displayed
                        renderDynamicCustomizationFields(
                            customizationFields,
                            selectedProduct.tagPrices,
                            customContainer,
                            selectedProduct.tagImages,
                            stepNamesToUse,
                            selectedProduct.selectedOptions // Admin-selected tags only
                        );
                        console.log('✅ Rendered customization fields with selected options filter');
                    } else {
                        console.error('❌ Container or function not found');
                        console.error('Container:', customContainer);
                        console.error('Function:', typeof renderDynamicCustomizationFields);
                    }
                } else {
                    console.warn('⚠️ No customization fields to render');
                    const customContainer = document.getElementById('dynamic-customization-container');
                    if (customContainer) {
                        customContainer.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">No customization options available. Please configure fields in admin panel.</p>';
                    }
                }

                // Render standard sizes if available
                console.log("=== RENDERING STANDARD SIZES ===");
                console.log("Standard series to render:", selectedProduct.standardSeries);
                console.log("Standard series count:", (selectedProduct.standardSeries || []).length);
                
                if (selectedProduct.standardSeries && selectedProduct.standardSeries.length > 0) {
                    const standardContainer = document.getElementById('dynamic-standard-container');
                    console.log("Standard container found:", !!standardContainer);
                    console.log("renderStandardSizes function exists:", typeof renderStandardSizes === 'function');
                    
                    if (standardContainer && typeof renderStandardSizes === 'function') {
                        renderStandardSizes(
                            selectedProduct.standardSeries,
                            standardContainer
                        );
                        console.log('✅ Rendered standard sizes');
                    } else {
                        console.error('❌ Standard container or function not found');
                        console.error('Container:', standardContainer);
                        console.error('Function:', typeof renderStandardSizes);
                    }
                } else {
                    console.warn('⚠️ No standard series to render');
                    const standardContainer = document.getElementById('dynamic-standard-container');
                    if (standardContainer) {
                        standardContainer.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">No standard sizes available for this product.</p>';
                    }
                }
                
                // =====================================================
                // FINAL: Retry loading visual configs if they weren't loaded earlier
                // and ensure the 2D preview is re-rendered with correct colors
                // =====================================================
                setTimeout(() => {
                    // Retry loading visual configs if they were pending
                    if (window.pendingVisualConfigs && typeof window.loadDynamicVisualConfigs === 'function') {
                        console.log('🔄 Retrying visual config load...');
                        window.loadDynamicVisualConfigs(window.pendingVisualConfigs);
                        delete window.pendingVisualConfigs;
                    }
                    
                    // Force re-render of Konva to apply visual configs
                    if (typeof window.renderCustomState === 'function') {
                        console.log('🔄 Re-rendering 2D preview with visual configs...');
                        window.renderCustomState();
                    } else if (typeof renderCustomState === 'function') {
                        renderCustomState();
                    }
                    
                        console.log('✅ 2D Preview sync complete - admin visual configs applied');
                }, 500);

                // Initialize AJAX functionality for customizations after everything is loaded
                if (typeof window.customizationAjax !== 'undefined' && typeof window.customizationAjax.init === 'function') {
                    console.log('Initializing AJAX customization functionality...');
                    window.customizationAjax.init();
                } else {
                    // Retry after a delay if not yet available
                    setTimeout(() => {
                        if (typeof window.customizationAjax !== 'undefined' && typeof window.customizationAjax.init === 'function') {
                            window.customizationAjax.init();
                        }
                    }, 1000);
                }
            }, 200);
        });

        // Helper function to get default fields (matches admin side structure)
        function getDefaultFieldsForSubcategory(category, subcategory) {
            // Map category to prefix (matches admin side)
            const prefixMap = {
                'Windows': 'Windows',
                'Doors': 'Doors',
                'Glass Partitions & Enclosures': 'Partitions',
                'Mirrors & Specialty Glass': 'Specialty',
                'Commercial & Exterior': 'Commercial'
            };
            
            const prefix = prefixMap[category] || '';
            const fieldKey = prefix ? `${prefix}_${subcategory}` : subcategory;
            
            // Default field configurations (matches admin side products.js)
            // These are comprehensive defaults that match the admin configuration
            const defaultFields = {
                'Windows_Sliding': [
                    { type: 'tags', label: 'Number of Panels', id: 'numberOfPanels', options: ['2 Panels', '4 Panels'], stepNumber: 1 },
                    { type: 'tags', label: 'Transom Type (Top / Bottom Fixed Panel)', id: 'transomType', options: ['None', 'Fixed Transom Head (Fixed glass at top)', 'Fixed Transom Sill (Fixed glass at bottom)'], stepNumber: 1 },
                    { type: 'tags', label: 'Track System (Sliding Rail Count)', id: 'trackSystem', options: ['2 Tracks', '3 Tracks'], stepNumber: 2 },
                    { type: 'tags', label: 'Panel Configuration', id: 'panelConfiguration', options: ['S | S (Sliding | Sliding)', 'F | S (Fixed | Sliding)', 'S | S | S | S (All Sliding)', 'F | S | S | F (Fixed | Sliding | Sliding | Fixed)'], stepNumber: 2 },
                    { type: 'tags', label: 'Frame Color', id: 'frameColor', options: ['Hanalok', 'White', 'Black', 'Gray', 'Wood Finish'], stepNumber: 3 },
                    { type: 'tags', label: 'Glass Type', id: 'glassType', options: ['Clear', 'Ultra Clear', 'Bronze', 'Light Green', 'Dark Gray', 'Copperfree Mirror', 'Euro Gray', 'Ford Blue', 'Reflective: Clear', 'Reflective: Gray', 'Reflective: Light Blue', 'Reflective: Dark Blue', 'Reflective: Light Green', 'Reflective: Dark Green', 'Reflective: Light Bronze', 'Tempered: Clear', 'Tempered: Bronze'], stepNumber: 3 },
                    { type: 'tags', label: 'Glass Thickness', id: 'glassThickness', options: ['6mm'], stepNumber: 3 },
                    { type: 'tags', label: 'Lock Type', id: 'lockType', options: ['Center Lok 904 Big', 'Flushlok #12', 'Durable Flushlok', 'New Auto Flushlock'], stepNumber: 4 },
                    { type: 'tags', label: 'Roller Type', id: 'rollerType', options: ['Single Panel Roller', 'Blue Single Roller', 'Blue Double Roller'], stepNumber: 4 },
                    { type: 'tags', label: 'Screen', id: 'screen', options: ['With Screen', 'Without Screen'], stepNumber: 4 }
                ],
                'Windows_Sliding_stepNames': {
                    '1': 'Window Type',
                    '2': 'Sliding System & Size',
                    '3': 'Frame & Glass',
                    '4': 'Hardware & Accessories'
                },
                'Doors_Sliding': [
                    { type: 'tags', label: 'Glass Type', id: 'glassType', options: ['Clear', 'Tinted', 'Frosted', 'Low-E', 'Tempered', 'Laminated', 'Laminated safety glass'], stepNumber: 1 },
                    { type: 'tags', label: 'Frame Material/Color', id: 'frameColor', options: ['Aluminum', 'Black', 'White', 'Bronze', 'Brown (wood-look)', 'Silver', 'Custom colors'], stepNumber: 1 },
                    { type: 'tags', label: 'Panel Count', id: 'panelCount', options: ['2-panel', '3-panel', '4-panel', 'More panels'], stepNumber: 1 },
                    { type: 'tags', label: 'Operation', id: 'operation', options: ['Sliding (single)', 'Sliding (double)', 'Sliding (multi-track)'], stepNumber: 2 },
                    { type: 'tags', label: 'Panel Configuration', id: 'panelConfiguration', options: ['Central sliding panels with fixed outer panels', 'All sliding', '2 sliding + 2 fixed', '2 sliding only', '3 sliding', 'Custom'], stepNumber: 2 },
                    { type: 'tags', label: 'Handle Type', id: 'handleType', options: ['Various pull handles', 'Knob handles', 'Square handles', 'Bar-style', 'Round', 'Square matte black'], stepNumber: 3 },
                    { type: 'tags', label: 'Hardware Finish', id: 'hardwareFinish', options: ['Chrome/Stainless Steel', 'Polished Chrome/Stainless Steel', 'Black Matte', 'Gold', 'Brushed Nickel', 'Bronze'], stepNumber: 3 },
                    { type: 'checkbox', label: 'Soft-close', id: 'softClose', stepNumber: 3 }
                ],
                'Doors_Sliding_stepNames': {
                    '1': 'Basic Options',
                    '2': 'Operation & Configuration',
                    '3': 'Hardware & Features'
                },
                'Partitions_Frameless Glass': [
                    { type: 'tags', label: 'Layout', id: 'layout', options: ['L-shape', 'Straight', 'U-shape', 'L-type', 'Neo-angle', 'Square', 'Bay', 'Other corner layouts'], stepNumber: 1 },
                    { type: 'tags', label: 'Glass Type', id: 'glassType', options: ['Clear', 'Frosted', 'Tinted', 'Frosted (full or partial)', 'Clear with frosted sticker', 'Fully frosted'], stepNumber: 1 },
                    { type: 'tags', label: 'Finish', id: 'finish', options: ['Clear', 'Frosted', 'Patterned'], stepNumber: 1 },
                    { type: 'tags', label: 'Configuration', id: 'configuration', options: ['Single partition', 'Multiple partitions', '2 fixed panels', '3 fixed panels', 'Custom configurations'], stepNumber: 2 },
                    { type: 'tags', label: 'Hardware Color', id: 'hardwareColor', options: ['Black', 'Silver', 'Gold', 'White', 'Bronze', 'Chrome/Stainless Steel', 'Black Matte', 'Brushed Nickel', 'Stainless Steel'], stepNumber: 2 },
                    { type: 'tags', label: 'Mounting Hardware', id: 'mountingHardware', options: ['Stainless Fixed Bracket', 'Gold U-Channel', 'Analok U-Channel (anodized aluminum)', 'Stainless U-Channel', 'Other bracket types', 'Standard mounting'], stepNumber: 2 },
                    { type: 'number', label: 'Glass Thickness (mm)', id: 'glassThickness', min: 1, step: 0.1, stepNumber: 2 }
                ],
                'Partitions_Frameless Glass_stepNames': {
                    '1': 'Basic Options',
                    '2': 'Configuration & Hardware'
                },
                'Specialty_Mirrors': [
                    { type: 'tags', label: 'Shape', id: 'shape', options: ['Round', 'Rectangle', 'Oval', 'Circle', 'Square', 'Rectangular with rounded edges', 'Rectangular with arched top', 'Custom shapes'], stepNumber: 1 },
                    { type: 'tags', label: 'Frame Type', id: 'frameType', options: ['Frameless', 'Framed', 'Gold frame', 'Black frame', 'White frame', 'Framed (thin, metallic)', 'Framed (dark, possibly black, grid frame)', 'Framed (gold frame shown)', 'Framed (thin matching frame possible)'], stepNumber: 1 },
                    { type: 'tags', label: 'Frame Material/Color', id: 'frameColor', options: ['Gold frame', 'Silver', 'Rose Gold', 'Other metallic finishes', 'Wood', 'Colored frames', 'Black frame', 'Other metallic or matte colors', 'White frame', 'Other colors', 'Metal', 'Silver/Metallic', 'Other options', 'Dark/Black', 'Other frame colors available'], stepNumber: 1 },
                    { type: 'tags', label: 'Edge Finish', id: 'edgeFinish', options: ['Beveled', 'Polished', 'Raw', 'Beveled edge', 'Flat polished edge', 'Pencil edge', 'Standard polished edge', 'Standard (behind frame)', 'Rounded edges'], stepNumber: 2 },
                    { type: 'tags', label: 'Tint/Finish', id: 'tintFinish', options: ['Bronze tint/color', 'Grey tint (smoked)', 'Colored glass'], stepNumber: 2 },
                    { type: 'tags', label: 'Orientation', id: 'orientation', options: ['Vertical', 'Horizontal', 'Vertical/Full-body'], stepNumber: 2 },
                    { type: 'tags', label: 'Mounting Method', id: 'mountingMethod', options: ['Wall-mounted', 'Stand', 'Adhesive', 'Leaning', 'Wall-mounted (often fixed above vanity)', 'Fixed wall mount', 'Integrated hanger', 'Rope hanger', 'Chain'], stepNumber: 3 },
                    { type: 'tags', label: 'Size', id: 'size', options: ['Small', 'Medium', 'Large diameter (custom)', 'Custom height and width (oval dimensions)', 'Custom height and width', 'Custom width and height (often for vanity sizes)', 'Very large dimensions for whole-body viewing (customizable)', 'Custom Size (flexible dimensions)', 'Large scale, possibly custom-fit for walls', 'Standard large size', 'Custom sizes', 'Various sizes (tall vertical, wider horizontal)', 'Custom dimensions'], stepNumber: 3 },
                    { type: 'number', label: 'Corner Radius (in)', id: 'cornerRadius', min: 0, step: 0.1, stepNumber: 3 }
                ],
                'Specialty_Mirrors_stepNames': {
                    '1': 'Basic Shape & Frame',
                    '2': 'Finish & Details',
                    '3': 'Mounting & Installation'
                }
            };
            
            // Return fields and step names
            const fields = defaultFields[fieldKey] || [];
            const stepNames = defaultFields[fieldKey + '_stepNames'] || null;
            
            return { fields, stepNames };
        }
    </script>
<?php endif; ?>