<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/ewallet_style.css'); ?>">

<script>
    const BASE_URL = "<?= base_url(); ?>";
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= base_url('assets/js/cart.js'); ?>"></script>


<!-- Back + Progress -->
<div class="payOrder-header">
    <div class="back-btn">
        <a href="<?php echo site_url('shopcon/checkout'); ?>">
            <img src="<?php echo base_url('assets/img/back_button.png'); ?>" alt="Back Icon">
            <span>Back</span>
        </a>
    </div>

    <div class="progress-nav">
        <div class="step">Cart</div>
        <div class="divider"></div>
        <div class="step active">Payment</div>
        <div class="divider"></div>
        <div class="step">Complete</div>
    </div>
</div>

<main>
    <!-- Title -->
    <div class="info-title">
        <h2>Pay The Order</h2>
        <div class="title-divider"></div>
    </div>

    <!-- Payment Section -->
    <section class="payment-container">
        <!-- Left: GCash QR -->
        <div class="gcash-box">
            <img src="<?php echo base_url('assets/images/img-page/qr.png'); ?>" alt="GCash QR">
        </div>

        <!-- Right: Order Details -->
        <div class="order-box">
            <h3>How is the processing of order works?</h3>
            <p>
                After sending the payment through GCash, the employee will verify first the transaction in their
                system
                before confirming your order. You will be notified in this cart if your order is complete.
            </p>

            <div class="order-summary">
                <div class="summary-header">Order Summary</div>
                <!-- These values can be replaced with dynamic variables from controller: $items_count, $subtotal, etc. -->
                <div class="summary-row"><span>Items:</span> <span id="summary-items">0</span></div>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span class="price">₱<span id="summary-subtotal">0.00</span></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee:</span>
                    <span class="price">₱<span id="summary-shipping">0.00</span></span>
                </div>

                <div class="summary-row">
                    <span>Handling Fee:</span>
                    <span class="price">₱<span id="summary-handling">0.00</span></span>
                </div>

                <div class="summary-row total">
                    <span>Total:</span>
                    <span class="price">₱<span id="summary-total">0.00</span></span>
                </div>

            </div>

            <!-- Upload form: posts to ShopCon::waiting_order (which creates order) -->
            <form id="ewalletForm" action="<?php echo site_url('waiting_order'); ?>" method="post"
                enctype="multipart/form-data">
                <input type="hidden" name="payment_method" value="ewallet">
                <?php if ($this->config->item('csrf_protection')): ?>
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                        value="<?php echo $this->security->get_csrf_hash(); ?>">
                <?php endif; ?>
                
                <!-- Hidden fields to pass checkout data (will be populated from sessionStorage) -->
                <input type="hidden" name="address" id="hidden-address">
                <input type="hidden" name="city" id="hidden-city">
                <input type="hidden" name="province" id="hidden-province">
                <input type="hidden" name="note" id="hidden-note">
                <input type="hidden" name="total_amount" id="hidden-total">

                <div class="upload-box">
                    <span>*</span>
                    <span id="file-name"></span>
                    <label for="fileUpload" class="upload-btn">📎 Attach a file</label>
                    <input type="file" id="fileUpload" name="receipt" accept="image/*,application/pdf">
                </div>

                <button type="button" class="payment-btn" onclick="submitPayment()">Send Payment</button>

                <div class="terms">
                    <input type="checkbox" id="terms">
                    <label for="terms">
                        I have read and agree to Glassify’s
                        <a href="<?php echo site_url('shopcon/terms_order'); ?>">Terms and Conditions of Purchase</a>
                    </label>
                </div>
            </form>
        </div>
    </section>

</main>


<script>
    // Show selected file name
    const fileInput = document.getElementById('fileUpload');
    const fileNameDisplay = document.getElementById('file-name');

    if (fileInput) {
        fileInput.addEventListener('change', () => {
            fileNameDisplay.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : "";
        });
    }

    // Submit via form POST (simple client-side checks)
    function submitPayment() {
        if (!fileInput || !fileInput.files.length) {
            alert("Please attach a payment receipt.");
            return;
        }
        if (!document.getElementById("terms").checked) {
            alert("Please agree to the Terms and Conditions.");
            return;
        }

        // Get checkout data from sessionStorage (if available)
        const checkoutData = sessionStorage.getItem('checkout_data');
        if (checkoutData) {
            const data = JSON.parse(checkoutData);
            document.getElementById('hidden-address').value = data.address || '';
            document.getElementById('hidden-city').value = data.city || '';
            document.getElementById('hidden-province').value = data.province || '';
            document.getElementById('hidden-note').value = data.note || '';
            document.getElementById('hidden-total').value = data.total_amount || document.getElementById('summary-total').textContent.replace(/[₱,]/g, '');
        } else {
            // Fallback: get total from page
            document.getElementById('hidden-total').value = document.getElementById('summary-total').textContent.replace(/[₱,]/g, '');
        }

        // Optionally show a loading state here
        document.getElementById('ewalletForm').submit();
    }
</script>