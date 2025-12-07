<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/ewallet_style.css'); ?>">

<?php
// Get pending order summary from controller (passed as $pending_summary)
// Default values if no pending order
$items_count = isset($pending_summary['items']) ? $pending_summary['items'] : 0;
$subtotal = isset($pending_summary['subtotal']) ? $pending_summary['subtotal'] : 0;
$shipping = isset($pending_summary['shipping']) ? $pending_summary['shipping'] : 0;
$handling = isset($pending_summary['handling']) ? $pending_summary['handling'] : 0;
$total = isset($pending_summary['total']) ? $pending_summary['total'] : 0;

// Build back URL with selected cart IDs to preserve checkout state
$back_url = site_url('payment');
if (!empty($pending_cart_ids)) {
    $back_url .= '?selected=' . $pending_cart_ids;
}

// Debug removed - issue was cart.js overwriting values
?>

<script>
    const BASE_URL = "<?= base_url(); ?>";
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- cart.js removed - it was overwriting the order summary values with 0 -->


<!-- Back + Progress -->
<div class="payOrder-header">
    <div class="back-btn">
        <a href="<?php echo $back_url; ?>">
            <img src="<?php echo base_url('assets/images/img-page/back_button.png'); ?>" alt="Back Icon">
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
                <div class="summary-row"><span>Items:</span> <span id="summary-items"><?= $items_count ?></span></div>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span class="price">₱<span id="summary-subtotal"><?= number_format($subtotal, 2) ?></span></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee:</span>
                    <span class="price">₱<span id="summary-shipping"><?= number_format($shipping, 2) ?></span></span>
                </div>

                <div class="summary-row">
                    <span>Handling Fee:</span>
                    <span class="price">₱<span id="summary-handling"><?= number_format($handling, 2) ?></span></span>
                </div>

                <div class="summary-row total">
                    <span>Total:</span>
                    <span class="price">₱<span id="summary-total"><?= number_format($total, 2) ?></span></span>
                </div>

            </div>

            <!-- Upload form: posts to ShopCon::submit_ewallet_payment -->
            <form id="ewalletForm" action="<?php echo site_url('shopcon/submit_ewallet_payment'); ?>" method="post"
                enctype="multipart/form-data">
                <?php if ($this->config->item('csrf_protection')): ?>
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                        value="<?php echo $this->security->get_csrf_hash(); ?>">
                <?php endif; ?>

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
                        I have read and agree to Glassify's
                        <a href="<?php echo site_url('terms_order'); ?>">Terms and Conditions of Purchase</a>
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

        // Disable button and show loading state
        const btn = document.querySelector('.payment-btn');
        btn.disabled = true;
        btn.textContent = 'Processing...';

        // Submit the form
        document.getElementById('ewalletForm').submit();
    }
</script>
