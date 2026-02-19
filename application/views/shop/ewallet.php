<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/ewallet_style.css'); ?>">

<?php
// Get pending order summary from controller (passed as $pending_summary)
// Default values if no pending order
$items_count = isset($pending_summary['items']) ? $pending_summary['items'] : 0;
$subtotal = isset($pending_summary['subtotal']) ? $pending_summary['subtotal'] : 0;
$shipping = isset($pending_summary['shipping']) ? $pending_summary['shipping'] : 0;
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
    const SELECTED_CART_IDS = "<?= $pending_cart_ids ?: (isset($_GET['selected']) ? $_GET['selected'] : '') ?>";
    
    // Get order summary from sessionStorage as fallback if server data is missing
    const sessionSummary = sessionStorage.getItem('order_summary');
    const sessionCartIds = sessionStorage.getItem('selected_cart_ids');
    
    <?php if (empty($pending_summary) || $pending_summary['total'] == 0): ?>
    // If server didn't provide summary, try to get from sessionStorage or fetch via AJAX
    if (sessionSummary) {
        const summary = JSON.parse(sessionSummary);
        document.getElementById('summary-items').textContent = summary.items || 0;
        document.getElementById('summary-subtotal').textContent = summary.subtotal || '0.00';
        document.getElementById('summary-shipping').textContent = summary.shipping || '0.00';
        document.getElementById('summary-total').textContent = summary.total || '0.00';
    } else if (SELECTED_CART_IDS) {
        // Fetch summary via AJAX using selected cart IDs
        $.ajax({
            url: BASE_URL + "CartCon/get_selected_cart_ajax",
            method: "GET",
            data: { selected: SELECTED_CART_IDS },
            dataType: "json",
            success: function(res) {
                if (res.status === 'success' && res.summary) {
                    const summary = res.summary;
                    document.getElementById('summary-items').textContent = summary.items || 0;
                    document.getElementById('summary-subtotal').textContent = summary.subtotal.toFixed(2);
                    document.getElementById('summary-shipping').textContent = summary.shipping.toFixed(2);
                    document.getElementById('summary-total').textContent = summary.total.toFixed(2);
                }
            },
            error: function() {
                console.error('Failed to load cart summary');
            }
        });
    }
    <?php endif; ?>
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- cart.js removed - it was overwriting the order summary values with 0 -->


<!-- Back + Progress -->
<div class="payOrder-header">
    <div class="back-btn">
        <a href="javascript:history.back()">
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

                <!-- Handling fee removed -->

                <div class="summary-row total">
                    <span>Total:</span>
                    <span class="price">₱<span id="summary-total"><?= number_format($total, 2) ?></span></span>
                </div>

            </div>

            <!-- Upload form: posts to ShopCon::submit_ewallet_payment -->
            <form id="ewalletForm" action="<?php echo base_url('shopcon/submit_ewallet_payment'); ?>" method="post"
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
                    <input type="checkbox" id="terms" name="terms">
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
    // =============================
    // TOAST NOTIFICATION SYSTEM
    // =============================
    function showToast(message, type = 'info', duration = 3000) {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast-notification');
        existingToasts.forEach(toast => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        });

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        
        // Set icon and colors based on type
        const config = {
            success: { icon: '✓', bg: '#28a745', border: '#1e7e34' },
            error: { icon: '✕', bg: '#dc3545', border: '#c82333' },
            warning: { icon: '⚠', bg: '#ffc107', border: '#e0a800' },
            info: { icon: 'ℹ', bg: '#17a2b8', border: '#138496' }
        };
        
        const toastConfig = config[type] || config.info;
        
        toast.innerHTML = `
            <div class="toast-icon">${toastConfig.icon}</div>
            <div class="toast-message">${message}</div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        // Add styles
        toast.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: ${toastConfig.bg};
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            max-width: 500px;
            animation: toastSlideIn 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            border-left: 4px solid ${toastConfig.border};
        `;
        
        // Add animation styles if not already added
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                @keyframes toastSlideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes toastFadeOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
                .toast-notification {
                    transition: all 0.3s ease;
                }
                .toast-fade-out {
                    animation: toastFadeOut 0.3s ease forwards;
                }
                .toast-icon {
                    font-size: 20px;
                    font-weight: bold;
                    flex-shrink: 0;
                }
                .toast-message {
                    flex: 1;
                    font-size: 14px;
                    line-height: 1.4;
                }
                .toast-close {
                    background: none;
                    border: none;
                    color: white;
                    font-size: 24px;
                    cursor: pointer;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0.8;
                    transition: opacity 0.2s;
                    flex-shrink: 0;
                }
                .toast-close:hover {
                    opacity: 1;
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(toast);
        
        // Auto remove after duration
        setTimeout(() => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
        
        return toast;
    }

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
            showToast("Please attach a payment receipt.", 'warning');
            return;
        }
        if (!document.getElementById("terms").checked) {
            showToast("Please agree to the Terms and Conditions.", 'warning');
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
