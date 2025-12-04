<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/2DModeling_styles.css'); ?>">

<script src="https://unpkg.com/konva@9.3.6/konva.min.js"></script>

<body data-customer-id="<?= $this->session->userdata('Customer_ID') ?: '' ?>">

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
                <?php if ($product): ?>
                    <div class="product-info">
                        <img src="<?= base_url('uploads/products/' . $product->ImageUrl) ?>"
                            alt="<?= $product->ProductName ?>">
                    </div>
                <?php endif; ?>


                <div class="gallery-nav">
                    <button class="nav-arrow">&lt;</button>
                    <button class="nav-arrow">&gt;</button>
                </div>
                <div class="image-counter">1/3</div>
            </div>

            <div class="diagram-container">
                <div id="konva-container" class="konva-wrapper"></div>
                <div class="preview-label">2D Preview</div>
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
        </section>

        <section class="product-details">
            <div class="title-row">
                <div>
                    <?php if ($product): ?>
                        <div class="product-info">
                            <h2><?= $product->ProductName ?></h2>

                        </div>
                    <?php endif; ?>

                    <p id="standard-subtitle" class="subtitle hidden-step">Start building today!</p>
                </div>
                <button class="wishlist-btn">
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
                <span class="price-label">Estimated Price</span>


                <?php if ($product): ?>
                    <span class="price-value">Price: ₱<?= number_format($product->Price, 2) ?></span>
                <?php endif; ?>

            </div>

            <div id="custom-wrapper">
                <div id="step-1">
                    <div class="shape-section">
                        <label class="section-label">Glass Shape</label>
                        <div class="grid-3-cols">
                            <div class="option-card active" data-shape="rectangle">Rectangle</div>
                            <div class="option-card" data-shape="square">Square</div>
                            <div class="option-card" data-shape="triangle">Triangle</div>
                        </div>
                        <div class="shape-grid-row-2">
                            <div class="option-card" data-shape="pentagon">Pentagon</div>
                        </div>
                    </div>

                    <div class="dimensions-container">
                        <div class="input-group">
                            <label>Height</label>
                            <div class="unit-wrapper">
                                <div class="input-wrapper">
                                    <input type="text" id="input-height" value="45">
                                </div>
                                <div class="unit-control">
                                    <button class="unit-select" id="btn-unit-height" data-current-unit="in">
                                        Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M8 12l4 4 4-4"></path>
                                        </svg>
                                    </button>
                                    <div class="unit-dropdown hidden-step" id="dropdown-height">
                                        <div class="unit-option" data-value="in" data-target="height">Inches</div>
                                        <div class="unit-option" data-value="cm" data-target="height">Centimeters</div>
                                        <div class="unit-option" data-value="mm" data-target="height">Millimeters</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Width</label>
                            <div class="unit-wrapper">
                                <div class="input-wrapper">
                                    <input type="text" id="input-width" value="35">
                                </div>
                                <div class="unit-control">
                                    <button class="unit-select" id="btn-unit-width" data-current-unit="in">
                                        Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M8 12l4 4 4-4"></path>
                                        </svg>
                                    </button>
                                    <div class="unit-dropdown hidden-step" id="dropdown-width">
                                        <div class="unit-option" data-value="in" data-target="width">Inches</div>
                                        <div class="unit-option" data-value="cm" data-target="width">Centimeters</div>
                                        <div class="unit-option" data-value="mm" data-target="width">Millimeters</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="step-2" class="hidden-step">
                    <div class="type-section">
                        <label class="section-label">Glass Type</label>
                        <div class="grid-3-cols">
                            <div class="option-card active" data-glass-type="tempered">Tempered</div>
                            <div class="option-card" data-glass-type="laminated">Laminated</div>
                            <div class="option-card" data-glass-type="double">Double</div>
                            <div class="option-card" data-glass-type="low-e">Low-E</div>
                            <div class="option-card" data-glass-type="tinted">Tinted</div>
                            <div class="option-card" data-glass-type="frosted">Frosted</div>
                        </div>
                    </div>
                    <div class="thickness-section" style="margin-top: 30px; margin-bottom: 60px;">
                        <label class="section-label">Glass Thickness</label>
                        <div class="grid-3-cols">
                            <div class="option-card" data-thickness="3mm">3mm</div>
                            <div class="option-card active" data-thickness="5mm">5mm</div>
                            <div class="option-card" data-thickness="6mm">6mm</div>
                            <div class="option-card" data-thickness="8mm">8mm</div>
                            <div class="option-card" data-thickness="10mm">10mm</div>
                            <div class="option-card" data-thickness="12mm">12mm</div>
                        </div>
                    </div>
                </div>

                <div id="step-3" class="hidden-step">
                    <div class="edge-section">
                        <label class="section-label">Edge Work</label>
                        <div class="grid-3-cols">
                            <div class="option-card active" data-edge-work="flat-polish">Flat Polish</div>
                            <div class="option-card" data-edge-work="metered">Metered</div>
                            <div class="option-card" data-edge-work="beveled">Beveled</div>
                        </div>
                        <div class="shape-grid-row-2">
                            <div class="option-card" data-edge-work="seamed">Seamed</div>
                        </div>
                    </div>
                    <div class="frame-section" style="margin-top: 30px;">
                        <label class="section-label">Frame Type</label>
                        <div class="grid-3-cols">
                            <div class="option-card active" data-frame-type="vinyl">Vinyl</div>
                            <div class="option-card" data-frame-type="aluminum">Aluminum</div>
                            <div class="option-card" data-frame-type="wood">Wood</div>
                        </div>
                    </div>
                    <div class="engraving-section" style="margin-top: 30px; margin-bottom: 60px;">
                        <label class="section-label">Engraving (Optional)</label>
                        <div class="input-wrapper">
                            <input type="text" placeholder=""
                                style="border: none; width: 100%; padding: 12px; font-family: inherit; background: #f8f9fa;">
                        </div>
                    </div>
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
                <div class="standard-size-section">
                    <label class="section-label">Glass Size</label>
                    <div class="grid-3-cols">
                        <div class="option-card active" data-height="45" data-width="35">45" x 35"</div>
                        <div class="option-card" data-height="55" data-width="45">55" x 45"</div>
                        <div class="option-card" data-height="70" data-width="55">70" x 55"</div>
                    </div>
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

                <div class="summary-table-container">
                    <div class="summary-header">
                        Estimated Price
                    </div>
                    <div class="summary-content">
                        <div class="summary-row">
                            <span class="spec-label">Shape:</span>
                            <span class="spec-value" id="sum-shape">Rectangle</span>
                        </div>
                        <div class="summary-row">
                            <span class="spec-label">Dimension:</span>
                            <span class="spec-value" id="sum-dim">35in x 45in</span>
                        </div>
                        <div class="summary-row">
                            <span class="spec-label">Type:</span>
                            <span class="spec-value" id="sum-type">Tempered</span>
                        </div>
                        <div class="summary-row">
                            <span class="spec-label">Thickness:</span>
                            <span class="spec-value" id="sum-thick">5mm</span>
                        </div>
                        <div class="summary-row">
                            <span class="spec-label">Edge Work:</span>
                            <span class="spec-value" id="sum-edge">Flat Polish</span>
                        </div>
                        <div class="summary-row">
                            <span class="spec-label">Frame Type:</span>
                            <span class="spec-value" id="sum-frame">Vinyl</span>
                        </div>
                        <div class="summary-row">
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
                    <button class="cart-btn" id="add-to-cart-btn" data-product-id="<?= $product->Product_ID ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Add to Cart
                    </button>

                    <button class="buy-btn" id="buy-now-btn" data-product-id='<?= $product_id ?>'>
                        Buy Now
                    </button>

                    <button class="edit-order-btn" id="edit-order-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                        Edit Configuration
                    </button>
                </div>


            </div>
        </section>
    </main>

    <section id="related-products-section" class="full-width-section dark-bg">
        <div class="inner-content">
            <h2 class="section-title-white">Related Products</h2>
            <div class="products-grid">
                <div class="product-card">
                    <div class="p-image">
                        <img src="/Glassify/assets/img/series_slidingwindow.png" alt="900 Series">
                    </div>
                    <div class="p-info">
                        <p>900 Series Sliding Window</p>
                        <button class="yellow-btn">Build and Buy</button>
                    </div>

                </div>
                <div class="product-card">
                    <div class="p-image">
                        <img src="/Glassify/assets/img/798_window.png" alt="798 Series">
                    </div>
                    <div class="p-info">
                        <p>798 Series Sliding Window</p>
                        <button class="yellow-btn">Build and Buy</button>
                    </div>
                </div>
                <div class="product-card">
                    <div class="p-image">
                        <img src="/Glassify/assets/img/french-sliding-door.png" alt="French Window">
                    </div>
                    <div class="p-info">
                        <p>900 Series French Type Sliding Window</p>
                        <button class="yellow-btn">Build and Buy</button>
                    </div>
                </div>
                <div class="product-card">
                    <div class="p-image">
                        <img src="/Glassify/assets/img/awningwindow.png" alt="Awning Window">
                    </div>
                    <div class="p-info">
                        <p>38 Series Awning Window</p>
                        <button class="yellow-btn">Build and Buy</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials-section" class="full-width-section white-bg">
        <div class="inner-content">
            <h2 class="section-title-dark">Customer Testimonials</h2>
            <div class="testimonial-slider">
                <button class="slider-arrow left">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>

                <div class="testimonial-text-box">
                    <p class="quote">"Highly recommending this shop! Very smooth and fast transaction. Despite
                        unfortunate
                        events, they were still able to deliver. Owner and staff are committed to great service. Exceeds
                        expectations. Will definitely be our go-to-shop for glass and aluminum."</p>
                    <div class="stars">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="author">Kris-Ann Munda-Rebullana</p>
                </div>

                <button class="slider-arrow right">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
        </div>
    </section>
</body>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // PHP → JS: Pass selected product data
    const productBasePrice = <?= $product ? $product->Price : 0 ?>;
    var base_url = '<?= base_url(); ?>';
</script>

<script src="<?= base_url('assets/js/2d-functions/2d_customization.js'); ?>"></script>
<script src="<?= base_url('assets/js/2d-functions/addtocustomization.js'); ?>"></script>
<script src="<?= base_url('assets/js/2d-functions/buy-now-handler.js'); ?>"></script>
<!-- <script src="<?= base_url('assets/js/2d-functions/2d_functions.js'); ?>"></script> -->



<?php if ($product): ?>
    <script>
        // Pass Product Info From PHP → JavaScript
        const selectedProduct = {
            id: "<?= $product->Product_ID ?>",
            name: "<?= $product->ProductName ?>",
            price: "<?= $product->Price ?>",
            category: "<?= $product->Category ?>",
            material: "<?= $product->Material ?>",
            image: "<?= base_url('uploads/products/' . $product->ImageUrl) ?>"
        };

        console.log("Loaded Product From PHP:", selectedProduct);
    </script>
<?php endif; ?>