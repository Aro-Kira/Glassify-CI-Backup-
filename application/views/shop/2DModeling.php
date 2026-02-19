<?php
// Check if user is logged in
$isGuest = !$this->session->userdata('customer_id');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/2DModeling_styles.css'); ?>">

<style>
    /* Quantity control styling for 2D Modeling summary */
    :root { --qty-size-height: 44px; --qty-size-width: 56px; }
    #summary-qty-input {
        width: var(--qty-size-width);
        height: var(--qty-size-height);
        box-sizing: border-box;
        text-align: center;
        padding: 0 8px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 15px;
        line-height: var(--qty-size-height);
        /* Remove native number input arrows */
        -moz-appearance: textfield;
        appearance: textfield;
        vertical-align: middle;
    }
    /* Remove webkit spin buttons */
    #summary-qty-input::-webkit-outer-spin-button,
    #summary-qty-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .qty-btn {
        background: #ffffff;
        border: 1px solid #d1d5db;
        padding: 0;
        border-radius: 8px;
        cursor: pointer;
        width: var(--qty-size-width);
        height: var(--qty-size-height);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #1f2937;
        box-sizing: border-box;
    }
    .qty-btn:hover { background: #f8fafc; }
    .summary-qty { align-items: center; }

    /* Ensure the inner qty control has equal spacing and centered alignment */
    .summary-qty .qty-control {
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>

<script src="<?php echo base_url('assets/js/konva.min.js'); ?>"></script>
<!-- Comprehensive 2D Renderer -->
<script src="<?php echo base_url('assets/js/2d-functions/comprehensive_2d_renderer.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/2d-functions/comprehensive_renderer_integration.js'); ?>"></script>

<body <?= (isset($customer_role) && $customer_role === 'beginner') ? 'class="role-beginner"' : '' ?> data-customer-id="<?= $this->session->userdata('customer_id') ?: '' ?>">

    <?php if (!isset($customer_role) || $customer_role !== 'beginner'): ?>
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
                    <div id="uploaded-files-container" class="uploaded-files-gallery">
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
    <?php endif; ?>

    <!-- Guest Authentication Modal -->
    <div id="guest-auth-modal" class="modal-backdrop hidden-step" style="z-index: 10000;">
        <div class="modal-content" style="max-width: 460px; max-height: fit-content; border-radius: 12px; padding: 0; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #02455f 0%, #0a6b7d 100%); padding: 16px 20px; border-bottom: none;">
                <h2 style="color: white; margin: 0; font-size: 1.4rem; font-weight: 600;">Sign in to continue</h2>
                <button class="modal-close" id="guest-modal-close-btn" style="color: white; opacity: 0.9;">&times;</button>
            </div>
            
            <div style="padding: 20px 24px 12px; text-align: center;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#02455f" stroke-width="2" style="margin-bottom: 12px;">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <h3 style="color: #333; font-size: 1.1rem; margin-bottom: 8px; font-weight: 600;">Ready to Book Your Order?</h3>
                <p style="color: #666; font-size: 0.9rem; line-height: 1.4; margin-bottom: 16px;">
                    Create an account or log in to save your customization and proceed with booking an ocular visit.
                </p>
                
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 8px;">
                    <a href="<?= base_url('register') ?>" class="btn btn-primary" style="background: linear-gradient(135deg, #02455f 0%, #0a6b7d 100%); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; border: none; cursor: pointer; box-shadow: 0 3px 10px rgba(2, 69, 95, 0.3); transition: all 0.2s ease;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                        Create Account
                    </a>
                    <a href="<?= base_url('login') ?>" class="btn btn-secondary" style="background: white; color: #02455f; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; border: 2px solid #02455f; cursor: pointer; transition: all 0.2s ease;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        Log In
                    </a>
                </div>
                
                <p style="color: #999; font-size: 0.8rem; margin: 0;">
                    Your customization will be saved after you sign in.
                </p>
            </div>
        </div>
    </div>


    <?php if (!isset($customer_role) || $customer_role !== 'beginner'): ?>
    <div class="breadcrumb-strip">

        <div class="page-title">Products & Services</div>
        <div class="breadcrumbs" id="breadcrumbs-container">
            <span>Products</span>
            <span class="chevron-right"></span>
            <span id="crumb-step1">Step 1</span>
            <span class="chevron-right"></span>
            <span id="crumb-step2">Step 2</span>
            <span class="chevron-right"></span>
            <span class="active" id="crumb-review">Review Order</span>
        </div>
    </div>
    <?php endif; ?>

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
                                    class="main-product-image <?= $index === 0 ? 'active' : '' ?>" data-image-index="<?= $index ?>"
                                    style="<?= $index === 0 ? '' : 'display: none;' ?>"
                                    onerror="this.onerror=null; this.src='<?= $placeholderSvg ?>';">
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (empty($productImages)): ?>
                            <div
                                style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
                                No Image Available
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div
                        style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
                        No Image Available
                    </div>
                <?php endif; ?>

                <?php if (isset($totalImages) && $totalImages > 1): ?>
                    <div class="gallery-nav">
                        <button class="nav-arrow" id="gallery-prev-btn">&lt;</button>
                        <button class="nav-arrow" id="gallery-next-btn">&gt;</button>
                    </div>
                    <div class="image-counter" id="image-counter">1/<?= $totalImages ?></div>
                <?php else: ?>
                    <div class="image-counter" id="image-counter" style="display: none;">1/1</div>
                <?php endif; ?>
            </div>

            <?php if (isset($customer_role) && $customer_role === 'beginner'): ?>
            <!-- Beginner users don't see 2D customization -->
            <?php elseif (!$isGuest && isset($setup_status) && $setup_status !== 'completed'): ?>
            <!-- Locked 2D Preview for Incomplete Setup Users Only -->
            <div class="diagram-container" style="position: relative;">
                <div id="konva-container" class="konva-wrapper" style="filter: blur(5px); opacity: 0.5; pointer-events: none;"></div>
                <div class="preview-label" style="cursor: not-allowed; color: #856404;">2D Preview (Locked)
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2" style="vertical-align: middle; margin-left: 4px;">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
            </div>
            <!-- Disabled Upload Button for Incomplete Setup Users -->
            <button class="upload-btn" style="background: #ffc107; cursor: not-allowed; opacity: 0.8; color: #856404;" disabled title="Complete setup to upload files">
                Upload a File (Locked)
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </button>
            <?php else: ?>
            <div class="diagram-container">
                <div id="konva-container" class="konva-wrapper"></div>
                <div class="preview-label" style="cursor: pointer;">2D Preview <span style="font-size: 0.8em;">(Click to
                        enlarge)</span></div>
            </div>
            <?php if ($isGuest): ?>
            <!-- Upload Button Locked for Guests -->
            <button class="upload-btn" style="background: #ffc107; cursor: not-allowed; opacity: 0.8; color: #856404;" disabled title="Sign in to upload files">
                Upload a File (Locked)
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </button>
            <?php else: ?>
            <button class="upload-btn" id="open-modal-btn">
                Upload a File
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48">
                    </path>
                </svg>
            </button>
            <?php endif; ?>
            <?php endif; ?>

            <!-- External Uploaded Files Display (outside modal) -->
            <div class="external-uploaded-files-list" id="external-uploaded-files-list"
                style="display: none; margin-top: 15px;">
                <h3 class="external-uploaded-files-title"
                    style="font-size: 0.9rem; font-weight: 600; margin-bottom: 10px; color: #02455F;">Uploaded Files
                </h3>
                <div id="external-uploaded-files-container"
                    style="display: flex; gap: 10px; overflow-x: auto; padding: 10px 0; max-height: 120px;">
                    <p class="placeholder-text"
                        style="font-style: italic; color: #666; text-align: center; padding: 10px;">No files uploaded
                        yet.</p>
                </div>
                <div class="external-files-scroll-nav"
                    style="display: flex; gap: 10px; justify-content: center; margin-top: 10px;">
                    <button class="scroll-arrow left hidden"
                        style="background: #02455F; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; display: none;">&lt;</button>
                    <button class="scroll-arrow right hidden"
                        style="background: #02455F; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; display: none;">&gt;</button>
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
                <button class="wishlist-btn" id="add-to-wishlist-btn"
                    data-product-id="<?= isset($product) && $product ? $product->Product_ID : '' ?>"
                    title="Add to Wishlist">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                </button>
            </div>

            <?php if ($isGuest): ?>
            <!-- Guest Notice - Amber Warning Style -->
            <div id="guest-notice-banner" class="guest-notice-banner" style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 16px; margin: 8px 0 16px 0; border-radius: 4px; max-width: 600px;">
                <span style="color: #856404; font-size: 14px; font-weight: 500; line-height: 1.4;">
                    <strong>Notice:</strong> You can explore customization, but you'll need an account to continue with booking.
                </span>
            </div>
            <?php endif; ?>

            <?php if (isset($customer_role) && $customer_role === 'beginner'): ?>
            <!-- Beginner users: hide customization entirely, show book ocular visit CTA above -->
            <script>
            // Hide all customization sections for beginners
            document.addEventListener('DOMContentLoaded', function() {
                // Hide customization sections
                const sectionsToHide = ['#dynamic-customization-container', '.customization-summary', '.summary-actions', '.build-toggle', '.price-box'];
                sectionsToHide.forEach(function(selector) {
                    const elems = document.querySelectorAll(selector);
                    elems.forEach(function(elem) {
                        if (elem) elem.style.display = 'none';
                    });
                });
            });
            </script>
            <?php endif; ?>

            <div class="build-toggle hidden-step" aria-hidden="true" hidden>
                <button class="toggle-btn active" id="btn-customize">Customize Build</button>
                <div class="divider-v"></div>
                <button class="toggle-btn inactive" id="btn-standard">Standard</button>
            </div>

            <div class="price-box" id="price-box">
                <div class="price-main">
                    <span class="price-label">Estimated Price</span>
                    <span class="price-value" id="total-price">
                        <?php 
                        // Use same price logic as products page
                        $priceMin = isset($product->PriceMin) && $product->PriceMin > 0 ? floatval($product->PriceMin) : null;
                        $priceMax = isset($product->PriceMax) && $product->PriceMax > 0 ? floatval($product->PriceMax) : null;
                        $price = isset($product->Price) && $product->Price > 0 ? floatval($product->Price) : null;
                        
                        if ($priceMin !== null && $priceMax !== null): ?>
                            ₱<?= number_format($priceMin, 2) ?> - ₱<?= number_format($priceMax, 2) ?>
                        <?php elseif ($price !== null): ?>
                            ₱<?= number_format($price, 2) ?>
                        <?php else: ?>
                            Contact for pricing
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <?php if (!$isGuest && isset($setup_status) && $setup_status !== 'completed'): ?>
            <!-- Setup Incomplete Notice -->
            <div class="setup-incomplete-notice" style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;">
                <img src="<?= base_url('assets/images/img-page/exclamation-mark.png') ?>" alt="Setup Required" style="width: 48px; height: 48px; margin-bottom: 12px;">
                <h3 style="color: #856404; font-size: 1.2rem; margin-bottom: 8px; font-weight: 600;">Setup Required</h3>
                <p style="color: #856404; font-size: 0.95rem; margin-bottom: 20px; line-height: 1.5;">Please complete "Set Up Your Experience" to continue with customization.</p>
                <a href="<?= base_url('Profile#user-experience') ?>" class="btn btn-primary" style="background: #856404; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; margin: 0 auto;">
                    Complete Setup
                </a>
            </div>
            <?php elseif (isset($customer_role) && $customer_role === 'beginner'): ?>
            <!-- Beginner Role Notice -->
            <div class="beginner-notice" style="background: #e7f3ff; border: 2px solid #2196F3; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;">
                <img src="<?= base_url('assets/images/img-page/exclamation-mark-blue.png') ?>" alt="Beginner Notice" style="width: 48px; height: 48px; margin-bottom: 12px;">
                <h3 style="color: #1976D2; font-size: 1.2rem; margin-bottom: 8px; font-weight: 600;">Interested in this product?</h3>
                <p style="color: #1976D2; font-size: 0.95rem; margin-bottom: 12px; line-height: 1.5;">As a <strong>Beginner</strong>, our experts will help you with measurements and customization during your ocular visit.</p>
                <p style="color: #1976D2; font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5;"><strong>📋 Product customization will be prepared by our team after your ocular visit.</strong></p>
                <div style="background: #fff; border: 1px solid #2196F3; border-radius: 6px; padding: 16px; margin-bottom: 20px; text-align: left;">
                    <h4 style="color: #1976D2; font-size: 0.95rem; margin: 0 0 10px 0; font-weight: 600;">What happens next:</h4>
                    <ol style="color: #1976D2; font-size: 0.9rem; line-height: 1.6; margin: 0; padding-left: 20px;">
                        <li>Book an ocular visit for this product</li>
                        <li>Our team visits your site to take measurements</li>
                        <li>We prepare the customization based on measurements</li>
                        <li>You review and approve the final design</li>
                        <li>We fabricate and deliver your product</li>
                    </ol>
                </div>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                    <?php 
                    $productId = isset($product) ? $product->Product_ID : '';
                    $productName = isset($product) ? urlencode($product->ProductName) : '';
                    $bookingUrl = base_url("booking?product_id={$productId}&product_name={$productName}&source=beginner_booking");
                    ?>
                    <a href="<?= $bookingUrl ?>" class="btn btn-primary" style="background: #28a745; color: white; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; font-size: 1rem; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        Book Ocular Visit for This Product
                    </a>
                    <button onclick="window.history.back()" class="btn btn-secondary" style="background: transparent; color: #2196F3; padding: 14px 28px; border-radius: 8px; border: 2px solid #2196F3; font-weight: 500; cursor: pointer;">
                        Go Back
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- AJAX Status Indicator -->
            <div id="ajax-status-indicator"
                style="display: none; position: fixed; top: 20px; right: 20px; z-index: 10000; padding: 10px 15px; border-radius: 4px; font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: all 0.3s ease;">
                <span id="ajax-status-text">Saving...</span>
            </div>

            <style>
                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes fadeOut {
                    from {
                        opacity: 1;
                        transform: translateY(0);
                    }

                    to {
                        opacity: 0;
                        transform: translateY(-10px);
                    }
                }

                #ajax-status-indicator {
                    animation: fadeIn 0.3s ease;
                }
                
                /* Locked state for guests and incomplete setup users */
                .custom-wrapper-locked {
                    position: relative;
                    filter: blur(3px);
                    opacity: 0.5;
                    pointer-events: none;
                    user-select: none;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                }
                
                .locked-overlay {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    text-align: center;
                    z-index: 10;
                    pointer-events: none;
                }
                
                .locked-overlay svg {
                    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
                }
                
                .locked-overlay p {
                    user-select: none;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                }
                
                .locked-overlay a {
                    pointer-events: all;
                    user-select: text;
                }
            </style>

            <?php if (!isset($customer_role) || $customer_role !== 'beginner'): ?>
            <?php if (!$isGuest && isset($setup_status) && $setup_status !== 'completed'): ?>
            <!-- Locked Custom Wrapper with Overlay (Incomplete Setup Only) -->
            <div style="position: relative;">
                <div id="custom-wrapper" class="custom-wrapper-locked">
            <?php else: ?>
            <div id="custom-wrapper" <?php if ($isGuest): ?>data-guest="true"<?php endif; ?>>
            <?php endif; ?>
                <!-- Default Size Fields (Height & Width) - Only visible on Step 1 -->
                <div class="dimensions-container" id="dimensions-container">
                    <div class="input-group">
                        <label class="section-label">Height</label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-height" name="height" value="45" min="0" step="0.1"
                                    placeholder="45">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-height" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 12l4 4 4-4"></path>
                                    </svg>
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
                        <button type="button" id="dimension-lock-btn" class="dimension-lock-btn"
                            title="Lock dimensions to keep height and width equal">
                            <svg id="lock-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <svg id="unlock-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                style="display: none;">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="input-group">
                        <label class="section-label">Width</label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-width" name="width" value="35" min="0" step="0.1"
                                    placeholder="35">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-width" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 12l4 4 4-4"></path>
                                    </svg>
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
                        <label class="section-label">Inner Height (h1) <span
                                style="color: #0066CC; font-weight: bold;">●</span></label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-h1" name="h1" value="" min="0" step="0.1"
                                    placeholder="0.0">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-h1" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 12l4 4 4-4"></path>
                                    </svg>
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
                        <label class="section-label">Fixed Transom Height (h2) <span
                                style="color: #00AA00; font-weight: bold;">●</span></label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-h2" name="h2" value="" min="0" step="0.1"
                                    placeholder="0.0">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-h2" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 12l4 4 4-4"></path>
                                    </svg>
                                </button>
                                <div class="unit-dropdown hidden-step" id="dropdown-h2">
                                    <div class="unit-option" data-value="in">Inches</div>
                                    <div class="unit-option" data-value="cm">Centimeters</div>
                                    <div class="unit-option" data-value="mm">Millimeters</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="input-group hidden-step" id="input-group-w1">
                        <label class="section-label">Door Width (w1) <span
                                style="color: #00aeff; font-weight: bold;">●</span></label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-w1" name="w1" value="" min="0" step="0.1"
                                    placeholder="0.0">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-w1" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 12l4 4 4-4"></path>
                                    </svg>
                                </button>
                                <div class="unit-dropdown hidden-step" id="dropdown-w1">
                                    <div class="unit-option" data-value="in">Inches</div>
                                    <div class="unit-option" data-value="cm">Centimeters</div>
                                    <div class="unit-option" data-value="mm">Millimeters</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group hidden-step" id="input-group-w2">
                        <label class="section-label">Left Panel Width (w2) <span
                                style="color: #ffa600; font-weight: bold;">●</span></label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-w2" name="w2" value="" min="0" step="0.1"
                                    placeholder="0.0">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-w2" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 12l4 4 4-4"></path>
                                    </svg>
                                </button>
                                <div class="unit-dropdown hidden-step" id="dropdown-w2">
                                    <div class="unit-option" data-value="in">Inches</div>
                                    <div class="unit-option" data-value="cm">Centimeters</div>
                                    <div class="unit-option" data-value="mm">Millimeters</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group hidden-step" id="input-group-w3">
                        <label class="section-label">Right Panel Width (w3) <span
                                style="color: #fbff00; font-weight: bold;">●</span></label>
                        <div class="unit-wrapper">
                            <div class="input-wrapper">
                                <input type="number" id="input-w3" name="w3" value="" min="0" step="0.1"
                                    placeholder="0.0">
                            </div>
                            <div class="unit-control">
                                <button type="button" class="unit-select" id="btn-unit-w3" data-current-unit="in">
                                    Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 12l4 4 4-4"></path>
                                    </svg>
                                </button>
                                <div class="unit-dropdown hidden-step" id="dropdown-w3">
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
            
            <?php if (!$isGuest && isset($setup_status) && $setup_status !== 'completed'): ?>
                <!-- Lock Overlay for Custom Wrapper (Incomplete Setup Only) -->
                <div class="locked-overlay">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <p style="color: #856404; font-weight: 600; margin-top: 12px; font-size: 1.1rem;">
                        Complete setup to check eligibility for customization and book an ocular visit.
                    </p>
                </div>
            </div> <!-- Close position: relative wrapper -->
            <?php endif; ?>
            <?php endif; ?>

            <?php if (!isset($customer_role) || $customer_role !== 'beginner'): ?>
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
                    <button class="nav-btn next-btn" id="finalize-order-btn">
                        Finalize Order
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="summary-wrapper" class="hidden-step">
                <h2 class="summary-title">Review your order</h2>

                <!-- Warning Message - All orders go through ocular visit now -->
                <div class="price-warning" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 12px 16px; margin: 20px 0; color: #856404;">
                    <strong style="display: block; margin-bottom: 4px;">⚠️ Important Notice:</strong>
                    <?php if ($isGuest): ?>
                        <span>You can explore customization, but you'll need an account to continue with booking.</span>
                        <br><br>
                    <?php endif; ?>
                    <span>The estimated price shown is subject to change after the ocular visit. Final pricing will be confirmed following site assessment and verification of specifications.</span>
                </div>

                <!-- Design Preview Section -->
                <div class="design-preview-section">
                    <h3 class="design-preview-title">Your Custom Design</h3>
                    <div class="design-preview-container">
                        <img id="design-preview-img" src="" alt="Custom Design Preview">
                    </div>
                    <p class="design-preview-note">
                        This design layout will be saved with your order for quotation and invoice purposes.
                    </p>
                </div>

                <div class="summary-table-container">
                    <?php
                    // ALL products now follow the site-assessed process (unified flow)
                    // Header always shows "2D Customization Breakdown"
                    ?>
                    <div class="summary-header">
                        2D Customization Breakdown
                    </div>
                    <div class="summary-content" id="summary-content">
                        <!-- Dimension row (always shown) -->
                        <div class="summary-row">
                            <span class="spec-label">Dimension:</span>
                            <span class="spec-value">
                                <span id="sum-dim">45" x 35"</span>
                            </span>
                        </div>
                        <!-- Dynamic fields will be inserted here -->
                        <div id="dynamic-summary-rows"></div>
                        <!-- Engraving row (if applicable) -->
                        <div class="summary-row" id="summary-engraving-row" style="display: none;">
                            <span class="spec-label">Engraving:</span>
                            <span class="spec-value" id="sum-engrave">None</span>
                        </div>
                    </div>
                </div>

                <!-- Quantity selector moved here (below breakdown, above actions) -->
                <div class="summary-qty" style="display:flex; justify-content:flex-end; margin:12px 0 8px 0;">
                    <label style="align-self:center; margin-right:10px; color:#374151; font-weight:500;">Quantity:</label>
                    <div class="qty-control">
                        <button type="button" class="qty-btn" id="qty-decrease">−</button>
                        <input type="number" id="summary-qty-input" value="1" min="1" />
                        <button type="button" class="qty-btn" id="qty-increase">+</button>
                    </div>
                </div>

                <?php if (!isset($customer_role) || $customer_role !== 'beginner'): ?>
                <div class="summary-actions">
                    <?php
                    // ALL orders now follow the site-assessed process (unified flow)
                    // Add to Cart and Buy Now removed - only Book Now is available
                    ?>
                    
                    <!-- Edit Configuration button - on the left -->
                    <button class="edit-order-btn" id="edit-order-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                        Edit Configuration
                    </button>

                    <!-- Book Now button - All products follow site-assessment process -->
                    <button class="buy-btn" id="book-now-btn"
                        data-product-id="<?= isset($product) && $product ? $product->Product_ID : '' ?>"
                        data-is-guest="<?= $isGuest ? 'true' : 'false' ?>">
                        Book Now
                    </button>
                </div>
                <?php endif; ?>


            </div>
            <?php endif; ?>

            <?php if (!isset($customer_role) || $customer_role !== 'beginner'): ?>
            <!-- Preview Modal for enlarged Konva canvas -->
            <div id="preview-modal" class="modal-backdrop hidden-step">
                <div class="preview-modal-content" style="background: none; border: none; box-shadow: none; border-radius: 8px; overflow: hidden; max-width: 90vw; max-height: 90vh; position: relative;">
                    <div class="preview-modal-header" style="background: #02455f; color: white; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; position: relative;">
                        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">2D Design Preview</h3>
                        <button class="preview-close-btn" id="preview-close-btn" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 4px 8px; line-height: 1; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); z-index: 10;">&times;</button>
                    </div>
                    <div style="padding: 20px; background: white;">
                        <img id="zoomed-preview-img" src="" alt="Design Preview" style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
                        <div class="preview-modal-actions" style="margin-top: 20px; text-align: center;">
                            <button class="download-design-btn" id="download-design-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Download Design
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <section id="product-description-section" class="full-width-section product-description-section"
        style="transition: display 0.3s;">
        <div class="inner-content">
            <!-- Tab Navigation -->
            <div class="product-tabs">
                <button class="tab-btn active">DESCRIPTION</button>
            </div>

            <!-- Tab Content -->
            <div id="description-tab" class="tab-content active">
                <div class="product-description-content">
                    <?php if (isset($product) && !empty($product->Description)): ?>
                        <?= nl2br(htmlspecialchars($product->Description)) ?>
                    <?php else: ?>
                        <p>No description available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <style>
        .product-tabs {
            display: flex;
            gap: 40px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 10px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 15px 0;
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            position: relative;
            transition: color 0.3s;
        }

        .tab-btn:hover {
            color: #111827;
        }

        .tab-btn.active {
            color: #111827;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #3b82f6;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .product-description-content {
            padding: 5px 0 20px 0;
            line-height: 1.6;
            color: #374151;
            border: none;
            outline: none;
        }

        .product-description-content p {
            margin-top: 0;
        }
    </style>

    <section id="related-products-section" class="full-width-section dark-bg">
        <div class="inner-content">
            <h2 class="section-title-white">You May Also Like</h2>
            <div class="products-grid">
                <?php if (isset($recommendations) && !empty($recommendations)): ?>
                    <?php
                    // Limit to exactly 4 cards and randomize
                    $recommendations_array = is_array($recommendations) ? $recommendations : (array) $recommendations;

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
                                $placeholderSvg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1zbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';

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
                                <img src="<?= htmlspecialchars($image_url) ?>"
                                    alt="<?= htmlspecialchars($rec_product->ProductName) ?>"
                                    onerror="this.onerror=null; this.src='<?= $placeholderSvg ?>';">
                            </div>
                            <div class="p-info" style="text-align: left;">
                                <p
                                    style="font-weight: bold; color: white; margin-bottom: 8px; text-align: center; justify-content: center !important;">
                                    <?= htmlspecialchars($rec_product->ProductName) ?>
                                </p>
                             <!--    <p style="color: white; text-align: left !important; margin: 4px 0; font-size: 14px;">Type:
                                    <span style="font-weight: bold;"><?php
                                    // Determine order type based on category
                                    $category = strtolower($rec_product->Category ?? '');
                                    if (in_array($category, ['shower enclosure', 'windows', 'railings', 'canopy'])) {
                                        echo 'Site Assessment';
                                    } else {
                                        echo 'Direct Order';
                                    }
                                    ?></span>
                                </p> -->
                                <p style="color: white; text-align: left !important; margin: 4px 0; font-size: 14px;">Price:
                                    <span style="font-weight: bold;">₱<?= number_format($rec_product->Price, 2) ?></span>
                                </p>
                                <button class="yellow-btn"
                                    onclick="window.location.href='<?= base_url('2DModeling?id=' . $rec_product->Product_ID) ?>'">Build
                                    and Buy</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback if no recommendations available -->
                    <p style="color: #fff; text-align: center; padding: 20px; grid-column: 1 / -1;">No products available at
                        the moment.</p>
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
                    <p>Highly recommending this shop! Very smooth and fast transaction. Despite unfortunate events, they
                        were
                        still able to deliver. Owner and staff are committed at great service. Exceeds expectations.
                        Will definitely
                        be our go-to-shop for glass and aluminum.</p>
                    <div class="stars">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                    </div>
                    <h3 class="author">Kris-Ann Munda-Rebullana</h3>
                </div>

                <div class="testimonial-text">
                    <p>Highly recommended ⭐⭐⭐⭐⭐ Very accommodating staff. Responded immediately to queries and concerns.
                        Quality
                        materials and great workmanship. We'll ask them DEFINITELY to do collab again in our next
                        project 👍👍</p>
                    <div class="stars">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                    </div>
                    <h3 class="author">Anne Cruz</h3>
                </div>

                <div class="testimonial-text">
                    <p>Highly recommended! GlassWorth Builders service was excellent, and the quality of materials was
                        top-notch.
                        Their installers were kind and demonstrated good workmanship. I'm thoroughly impressed!</p>
                    <div class="stars">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
                        <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>"
                            alt="ratings">
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
    // Price display is now static (shows price range from PHP), not dynamically calculated
    // No need to initialize productBasePrice as it would override the static display
    window.productBasePrice = 0; // Deprecated - price is now static
    const productBasePrice = 0; // Deprecated - price is now static
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
    // Indicate beginner role to client scripts so they can suppress customization behaviors
    window.isBeginner = <?= (isset($customer_role) && $customer_role === 'beginner') ? 'true' : 'false' ?>;
</script>

<script>
    // Ensure product images are keyboard-focusable and interactive for beginner customers
    if (window.isBeginner) {
        document.addEventListener('DOMContentLoaded', function () {
            const imgs = document.querySelectorAll('.main-product-image');
            imgs.forEach(img => {
                img.setAttribute('tabindex', '0');
                img.setAttribute('role', 'img');
                img.style.cursor = 'pointer';
                img.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        img.click();
                    }
                });
            });
        });
    }
</script>
</script>

<script src="<?= base_url('assets/js/2d-functions/dynamic_customization.js'); ?>"></script>
<script src="<?= base_url('assets/js/2d-functions/2d_customization.js'); ?>"></script>
<script src="<?= base_url('assets/js/windows_visual_configs.js'); ?>"></script>
<script src="<?= base_url('assets/js/2d-functions/customization_ajax.js'); ?>"></script>
<script src="<?= base_url('assets/js/2d-functions/addtocustomization.js'); ?>"></script>
<script src="<?= base_url('assets/js/2d-functions/addtowishlist.js'); ?>"></script>
<script>
        // Product Image Gallery Navigation
        (function () {
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
                        if (i === index) {
                            img.style.display = 'block';
                            img.classList.add('active');
                        } else {
                            img.style.display = 'none';
                            img.classList.remove('active');
                        }
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

        // Backwards-compatibility: create proxy elements with legacy IDs so other scripts
        // that look for `prev-image` / `next-image` continue to work.
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                const prev = document.getElementById('gallery-prev-btn');
                const next = document.getElementById('gallery-next-btn');
                if (prev && !document.getElementById('prev-image')) {
                    const proxyPrev = document.createElement('button');
                    proxyPrev.type = 'button';
                    proxyPrev.id = 'prev-image';
                    proxyPrev.style.display = 'none';
                    proxyPrev.addEventListener('click', () => prev.click());
                    document.body.appendChild(proxyPrev);
                }
                if (next && !document.getElementById('next-image')) {
                    const proxyNext = document.createElement('button');
                    proxyNext.type = 'button';
                    proxyNext.id = 'next-image';
                    proxyNext.style.display = 'none';
                    proxyNext.addEventListener('click', () => next.click());
                    document.body.appendChild(proxyNext);
                }
            });
        })();

    // Testimonial script (inline to avoid nextBtn conflict)
    (function () {
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
        $imageSrc = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1zbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';

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
            selectedOptions: <?= json_encode($productSelectedOptions ?? []) ?>, // Admin-selected tags to filter options
            orderType: <?= json_encode($product->OrderType ?? 'direct') ?>
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
                    } catch (e) {
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
</script>