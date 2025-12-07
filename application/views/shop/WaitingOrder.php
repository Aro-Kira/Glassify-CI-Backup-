<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/2DModeling_styles.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/checkout_style.css'); ?>">
<script src="<?php echo base_url('assets/js/order_status.js'); ?>"></script>
<script src="https://unpkg.com/konva@9.3.6/konva.min.js"></script>

<div class="checkout-header">
    <!-- Back button -->
    <div class="back-btn">
        <a href="<?php echo base_url('addtocart'); ?>">
            <img src="<?php echo base_url('assets/images/img-page/back_button.png'); ?>" alt="Back Icon">
            <span>Back</span>
        </a>
    </div>

    <!-- Progress nav -->
    <div class="progress-nav">
        <div class="step completed">Cart</div>
        <div class="divider"></div>
        <div class="step completed">Payment</div>
        <div class="divider"></div>
        <div class="step active">Approval</div>
        <div class="divider"></div>
        <div class="step">Complete</div>
    </div>
</div>

<main>
    <section class="status-banner">
        <div class="status-icon">
            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"
                stroke-linejoin="round" color="#eeb609">
                <path d="M5 22h14"></path>
                <path d="M5 2h14"></path>
                <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"></path>
                <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"></path>
            </svg>
        </div>
        <h1 class="status-title">Waiting for your order approval</h1>
        <p class="status-desc">We've received your order and are currently reviewing the details. We'll notify you soon.
        </p>
    </section>

    <div class="order-status-layout">

        <div class="product-gallery">
            <div class="main-image-container">
                <img src="/Glassify/assets/img/glass-window.jpg" alt="Glass Aluminum Window" class="main-image">
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
        </div>

        <div class="order-details-static">
            <div class="order-info-header">
                <h2>Glass & Aluminum Window</h2>
                <p>Review your order</p>
            </div>

            <div class="summary-table-container">
                <div class="summary-header">
                    Estimated Price
                </div>
                <div class="summary-content">
                    <div class="summary-row">
                        <span class="spec-label">Shape:</span>
                        <span class="spec-value" id="view-shape">...</span>
                    </div>
                    <div class="summary-row">
                        <span class="spec-label">Dimension:</span>
                        <span class="spec-value" id="view-dim">...</span>
                    </div>
                    <div class="summary-row">
                        <span class="spec-label">Type:</span>
                        <span class="spec-value" id="view-type">...</span>
                    </div>
                    <div class="summary-row">
                        <span class="spec-label">Thickness:</span>
                        <span class="spec-value" id="view-thick">...</span>
                    </div>
                    <div class="summary-row">
                        <span class="spec-label">Edge Work:</span>
                        <span class="spec-value" id="view-edge">...</span>
                    </div>
                    <div class="summary-row">
                        <span class="spec-label">Frame Type:</span>
                        <span class="spec-value" id="view-frame">...</span>
                    </div>
                    <div class="summary-row">
                        <span class="spec-label">Engraving:</span>
                        <span class="spec-value" id="view-engrave">...</span>
                    </div>

                    <div class="summary-row total-row">
                        <span class="spec-label">Total</span>
                        <span class="spec-value price-final" id="view-total">₱0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

