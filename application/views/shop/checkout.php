<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/checkout_style.css'); ?>">

<script>
    const BASE_URL = "<?= base_url(); ?>";
    
    // Get selected cart IDs from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const SELECTED_CART_IDS = urlParams.get('selected') || '';
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


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
        <div class="step active">Payment</div>
        <div class="divider"></div>
        <div class="step">Complete</div>
    </div>
</div>


<main>

    <!-- Title outside sections -->
    <div class="info-title">
        <h2>Shipping information</h2>
        <div class="title-divider"></div>
    </div>

    <!-- Content row -->
    <div class="info-container">
        <section class="info-section">
            <form id="profileForm" method="POST" action="<?= base_url('usercon/update_profile'); ?>">
                <!-- User Info -->
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstname" value="<?= htmlspecialchars($user->First_Name ?? '') ?>"
                            placeholder="Enter your first name" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middlename" value="<?= htmlspecialchars($user->Middle_Name ?? '') ?>"
                            placeholder="Enter your middle name (optional)">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lastname" value="<?= htmlspecialchars($user->Last_Name ?? '') ?>"
                            placeholder="Enter your last name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user->Email) ?>"
                            placeholder="Enter your email address" required>
                    </div>
                    <div class="form-group">
                        <label>Phone number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($user->PhoneNum) ?>" maxlength="11"
                            placeholder="Enter your phone number" required>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="shipping-address-title">
                    <h3>Shipping Address</h3>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Unit/House Number</label>
                        <input type="text" name="unit_house_number"
                            value="<?= htmlspecialchars($addresses['Shipping']->UnitHouseNumber ?? '') ?>"
                            placeholder="Unit/House Number (optional)">
                    </div>
                    <div class="form-group">
                        <label>Street</label>
                        <input type="text" name="street"
                            value="<?= htmlspecialchars($addresses['Shipping']->Street ?? '') ?>"
                            placeholder="Street (optional)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Subdivision/Building</label>
                        <input type="text" name="subdivision"
                            value="<?= htmlspecialchars($addresses['Shipping']->Subdivision ?? '') ?>"
                            placeholder="Subdivision/Building (optional)">
                    </div>
                    <div class="form-group">
                        <label>Barangay <span style="color: red;">*</span></label>
                        <input type="text" name="barangay"
                            value="<?= htmlspecialchars($addresses['Shipping']->Barangay ?? '') ?>"
                            placeholder="Enter Barangay" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Region <span style="color: red;">*</span></label>
                        <input type="text" name="region"
                            value="<?= htmlspecialchars($addresses['Shipping']->Region ?? '') ?>"
                            placeholder="Enter Region" required>
                    </div>
                    <div class="form-group">
                        <label>Province <span style="color: red;">*</span></label>
                        <input type="text" name="province"
                            value="<?= htmlspecialchars($addresses['Shipping']->Province ?? '') ?>"
                            placeholder="Enter Province" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City/Municipality <span style="color: red;">*</span></label>
                        <input type="text" name="city" value="<?= htmlspecialchars($addresses['Shipping']->City ?? '') ?>"
                            placeholder="Enter City/Municipality" required>
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country"
                            value="<?= htmlspecialchars($addresses['Shipping']->Country ?? 'Philippines') ?>"
                            placeholder="Country" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Zip Code <span style="color: red;">*</span></label>
                        <input type="text" name="zipcode"
                            value="<?= htmlspecialchars($addresses['Shipping']->ZipCode ?? '') ?>"
                            placeholder="Enter Zip Code" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Special Instructions / Note</label>
                        <textarea name="note" rows="3" placeholder="Add special instructions or notes for delivery (optional)"><?= htmlspecialchars($addresses['Shipping']->Note ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Preferred Ocular Visit Date -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Preferred Ocular Visit Date <span style="color: red;">*</span></label>
                        <input type="date" name="preferred_installation_date" id="preferred_installation_date" 
                            min="<?= date('Y-m-d', strtotime('+7 days')) ?>" 
                            placeholder="Select your preferred ocular visit date" required>
                        <small style="color: #666; font-size: 0.9em; display: block; margin-top: 5px;">
                            Please select a date at least 7 days from today. We'll do our best to accommodate your preference.
                        </small>
                        <!-- Inline error message for installation date -->
                        <div id="installation-date-error" class="inline-error" style="display: none; margin-top: 5px; padding: 8px 12px; background: #fff3cd; border-left: 3px solid #dc3545; border-radius: 4px;">
                            <span style="color: #dc3545; font-size: 0.9em;">⚠ Please select a Preferred Ocular Visit Date. This field is required.</span>
                        </div>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="terms"> <input type="checkbox" id="same-billing"> <label for="same-billing"> Make billing address same as shipping
                       
                    </label> </div>

            </form>
        </section>


        <!-- Order Summary Section -->
        <section class="order-summary">
            <div class="order-summary-content">
                <h3>Order Summary</h3>
                <p><span>Items:</span> <span id="summary-items">0</span></p>
                <p><span>Subtotal:</span> ₱<span id="summary-subtotal">0.00</span></p>
                <p><span>Shipping Fee:</span> ₱<span id="summary-shipping">0.00</span></p>
                <p><span>Handling Fee:</span> ₱<span id="summary-handling">0.00</span></p>
                <div class="summary-divider"></div>
                <p class="total"><span>Total:</span> ₱<span id="summary-total">0.00</span></p>
                <div class="btn-container">
                    <button class="generate-btn" id="openModal">Generate Quotation</button>
                </div>

            </div>
            <div class="payment-section">
                <div class="payment-method-content">
                    <h3>Payment Methods</h3>
                    <p>
                        <img src="<?php echo base_url('assets/images/img-page/dollar.png'); ?>" alt="dollaricon">
                        <label for="ewallet-radio">E-Wallet</label>
                        <input type="radio" id="ewallet-radio" name="payment-method"
                            title="Select E-Wallet as payment method">
                    </p>
                    <p>
                        <img src="<?php echo base_url('assets/images/img-page/wallet.png'); ?>" alt="COD-icon">
                        <label for="COD-radio">Cash on Delivery</label>
                        <input type="radio" id="COD-radio" name="payment-method" title="Select COD as payment method">
                    </p>
                </div>

                <!-- Inline error message for payment method -->
                <div id="payment-method-error" class="inline-error" style="display: none; margin-top: 10px; padding: 8px 12px; background: #fff3cd; border-left: 3px solid #dc3545; border-radius: 4px;">
                    <span style="color: #dc3545; font-size: 0.9em;">⚠ Please select a payment method before placing order.</span>
                </div>

                <!-- Removed <a> and kept only button -->
                <button class="placeOrder-btn" id="placeOrderBtn">Place Order</button>
            </div>

            <div class="terms">
                <input type="checkbox" id="accept-terms">
                <label for="accept-terms">
                    I have read and agree to Glassify's
                    <a href="<?php echo base_url('terms_order'); ?>">Terms and Conditions of Purchase</a>
                </label>
            </div>
        </section>
    </div>

</main>




<!-- Order Confirmation Modal -->
<div id="orderConfirmModal" class="modal">
  <div class="modal-overlay"></div>
  <div class="modal-content">
    <button class="modal-close" id="closeConfirmModal">&times;</button>

    <div class="modal-header">
      <h2>📋 Order Summary</h2>
      <span class="modal-subtitle">Please review your order before confirming</span>
    </div>

    <div class="modal-body">
      <!-- Customer & Shipping Info -->
      <div class="confirm-section">
        <h4 class="confirm-section-title">
          <span class="icon">📍</span> Shipping Details
        </h4>
        <div class="confirm-info-grid">
          <div class="confirm-info-item">
            <span class="info-label">Name</span>
            <span class="info-value" id="confirm-name"></span>
          </div>
          <div class="confirm-info-item">
            <span class="info-label">Email</span>
            <span class="info-value" id="confirm-email"></span>
          </div>
          <div class="confirm-info-item">
            <span class="info-label">Phone</span>
            <span class="info-value" id="confirm-phone"></span>
          </div>
          <div class="confirm-info-item full-width">
            <span class="info-label">Shipping Address</span>
            <span class="info-value" id="confirm-address"></span>
          </div>
        </div>
      </div>

      <!-- Payment Method -->
      <div class="confirm-section">
        <h4 class="confirm-section-title">
          <span class="icon">💳</span> Payment Method
        </h4>
        <div class="payment-badge" id="confirm-payment-method">
          <span class="payment-icon"></span>
          <span class="payment-text"></span>
        </div>
      </div>

      <!-- Preferred Ocular Visit Date -->
      <div class="confirm-section" id="confirm-installation-date-section" style="display: none;">
        <h4 class="confirm-section-title">
          <span class="icon">📅</span> Preferred Ocular Visit Date
        </h4>
        <div class="confirm-info-grid">
          <div class="confirm-info-item full-width">
            <span class="info-label">Date</span>
            <span class="info-value" id="confirm-installation-date"></span>
          </div>
        </div>
      </div>

      <!-- Order Items -->
      <div class="confirm-section">
        <h4 class="confirm-section-title">
          <span class="icon">🛒</span> Order Items
        </h4>
        <div class="confirm-items-container">
          <table class="confirm-items-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Details</th>
                <th>Qty</th>
                <th>Price</th>
              </tr>
            </thead>
            <tbody id="confirm-items-body">
              <!-- Items will be dynamically populated -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Order Total Summary -->
      <div class="confirm-totals">
        <div class="confirm-total-row">
          <span>Subtotal</span>
          <span id="confirm-subtotal">₱0.00</span>
        </div>
        <div class="confirm-total-row">
          <span>Shipping Fee</span>
          <span id="confirm-shipping">₱0.00</span>
        </div>
        <div class="confirm-total-row">
          <span>Handling Fee</span>
          <span id="confirm-handling">₱0.00</span>
        </div>
        <div class="confirm-total-row grand-total">
          <span>Total Amount</span>
          <span id="confirm-total">₱0.00</span>
        </div>
      </div>
    </div>

    <div class="modal-footer confirm-footer">
      <button class="btn-cancel" id="cancelOrderBtn">Cancel</button>
      <button class="btn-confirm-order" id="confirmOrderBtn">
        <span class="btn-icon">✓</span> Confirm & Place Order
      </button>
    </div>
  </div>
</div>


<!-- Simple Modern Quotation Modal -->
<div id="quotationModal" class="modal">
  <div class="modal-overlay"></div>
  <div class="modal-content">
    <button class="modal-close" id="closeModal">&times;</button>

    <div class="modal-header">
      <h2>Quotation</h2>
      <span class="quotation-date" id="quotation-date"></span>
    </div>

    <div class="modal-body">
      <!-- Customer Info - Inline Style -->
      <div class="customer-info-bar">
        <div class="customer-detail">
          <span class="label">Customer</span>
          <span class="value" id="quote-customer-name"><?php 
            if (isset($user)) {
              $name = trim(($user->First_Name ?? '') . ' ' . ($user->Middle_Name ?? '') . ' ' . ($user->Last_Name ?? ''));
              echo htmlspecialchars($name ?: 'N/A');
            } else {
              echo 'N/A';
            }
          ?></span>
        </div>
        <div class="customer-detail">
          <span class="label">Email</span>
          <span class="value" id="quote-customer-email"><?= isset($user->Email) ? $user->Email : 'N/A' ?></span>
        </div>
        <div class="customer-detail">
          <span class="label">Phone</span>
          <span class="value" id="quote-customer-phone"><?= isset($user->PhoneNum) ? $user->PhoneNum : 'N/A' ?></span>
        </div>
        <div class="customer-detail full-width">
          <span class="label">Shipping Address</span>
          <span class="value" id="quote-customer-address"><?php 
            if (isset($addresses['Shipping'])) {
              $addr = $addresses['Shipping'];
              $addressParts = array_filter([
                $addr->UnitHouseNumber ?? '',
                $addr->Street ?? '',
                $addr->Subdivision ?? '',
                $addr->Barangay ?? '',
                $addr->City ?? '',
                $addr->Province ?? '',
                $addr->Region ?? '',
                $addr->Country ?? 'Philippines',
                $addr->ZipCode ?? ''
              ]);
              $full_address = !empty($addressParts) ? implode(', ', $addressParts) : ($addr->AddressLine ?? 'N/A');
              echo htmlspecialchars($full_address);
            } else {
              echo 'N/A';
            }
          ?></span>
        </div>
      </div>

      <!-- Items Table -->
      <div class="table-wrapper">
        <table class="quotation-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Customization</th>
              <th>Qty</th>
              <th>Unit Price</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody id="quotation-items">
            <!-- Rows will be dynamically generated -->
          </tbody>
        </table>
      </div>

      <!-- Custom Design Layouts Section -->
      <div class="designs-section" id="designs-section" style="display: none;">
        <h4 class="section-title">Custom Design Layouts</h4>
        <p class="designs-note">Included designs for reference</p>
        <div class="designs-grid" id="quotation-designs">
          <!-- Design images will be dynamically generated -->
        </div>
      </div>

      <!-- Totals -->
      <div class="totals-box">
        <div class="total-line">
          <span>Subtotal</span>
          <span id="quote-subtotal">₱0.00</span>
        </div>
        <div class="total-line">
          <span>Shipping Fee</span>
          <span id="quote-shipping">₱0.00</span>
        </div>
        <div class="total-line">
          <span>Handling Fee</span>
          <span id="quote-handling">₱0.00</span>
        </div>
        <div class="total-line grand">
          <span>Grand Total</span>
          <span id="quote-grandtotal">₱0.00</span>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn-close" id="closeModalBtn">Close</button>
      <button class="btn-print" id="printQuotation">Print Quotation</button>
    </div>
  </div>
</div>

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

// Helper functions for field highlighting
function highlightField(fieldId, errorContainerId, message) {
    const field = document.getElementById(fieldId);
    const errorContainer = document.getElementById(errorContainerId);
    
    if (field) {
        field.style.borderColor = '#dc3545';
        field.style.borderWidth = '2px';
        field.focus();
    }
    
    if (errorContainer) {
        errorContainer.style.display = 'block';
    }
}

function clearFieldError(fieldId, errorContainerId) {
    const field = document.getElementById(fieldId);
    const errorContainer = document.getElementById(errorContainerId);
    
    if (field) {
        field.style.borderColor = '';
        field.style.borderWidth = '';
    }
    
    if (errorContainer) {
        errorContainer.style.display = 'none';
    }
}

$(document).ready(function() {
    // =============================
    // LOAD SELECTED ITEMS SUMMARY
    // =============================
    function loadSelectedSummary() {
        // Check if we have selected items
        if (!SELECTED_CART_IDS) {
            showToast('No items selected. Redirecting to cart...', 'warning', 2000);
            setTimeout(() => {
                window.location.href = BASE_URL + 'addtocart';
            }, 2000);
            return;
        }

        $.ajax({
            url: BASE_URL + "CartCon/get_selected_cart_ajax",
            method: "GET",
            data: { selected: SELECTED_CART_IDS },
            dataType: "json",
            success: function(res) {
                if (res.status === 'success') {
                    const summary = res.summary;

                    // Update order summary
                    $('#summary-items').text(summary.items);
                    $('#summary-subtotal').text(summary.subtotal.toFixed(2));
                    $('#summary-shipping').text(summary.shipping.toFixed(2));
                    $('#summary-handling').text(summary.handling.toFixed(2));
                    $('#summary-total').text(summary.total.toFixed(2));

                    // Check if cart is empty
                    if (res.items.length === 0) {
                        showToast('No valid items found. Redirecting to cart...', 'warning', 2000);
                        setTimeout(() => {
                            window.location.href = BASE_URL + 'addtocart';
                        }, 2000);
                    }
                }
            },
            error: function() {
                console.error('Failed to load cart summary');
            }
        });
    }

    // Initial load
    loadSelectedSummary();

    // =============================
    // QUOTATION MODAL FOR SELECTED ITEMS
    // =============================
    function openModal() {
        $('#quotationModal').addClass('show');
        $('body').css('overflow', 'hidden');
    }

    function closeModal() {
        $('#quotationModal').removeClass('show');
        $('body').css('overflow', '');
    }

    $('#openModal').click(function() {
        $.getJSON(BASE_URL + "CartCon/get_selected_cart_ajax?selected=" + SELECTED_CART_IDS, function(res) {
            if (res.status === 'success') {
                const tbody = $('#quotation-items');
                const designsContainer = $('#quotation-designs');
                const designsSection = $('#designs-section');
                
                tbody.empty();
                designsContainer.empty();

                let subtotal = 0;
                let hasDesigns = false;
                let designIndex = 1;

                res.items.forEach((item, index) => {
                    const unit_price = Number(item.unit_price) || 0;
                    const total = Number(item.total) || 0;
                    const customization = item.customization || 'Standard';

                    const row = `<tr style="animation-delay: ${index * 0.05}s">
                        <td>${item.description}</td>
                        <td class="customization-cell">${customization}</td>
                        <td>${item.quantity}</td>
                        <td>₱${unit_price.toFixed(2)}</td>
                        <td>₱${total.toFixed(2)}</td>
                    </tr>`;
                    tbody.append(row);
                    subtotal += total;

                    // Add design image if available
                    if (item.has_design && item.design_ref) {
                        hasDesigns = true;
                        const designCard = `
                            <div class="design-card">
                                <div class="design-card-header">
                                    <span class="design-number">Design #${designIndex}</span>
                                    <span class="design-product">${item.description}</span>
                                </div>
                                <div class="design-card-image">
                                    <img src="${item.design_ref}" alt="Custom Design ${designIndex}">
                                </div>
                                <div class="design-card-specs">
                                    ${customization}
                                </div>
                            </div>
                        `;
                        designsContainer.append(designCard);
                        designIndex++;
                    }
                });

                // Show/hide designs section
                if (hasDesigns) {
                    designsSection.show();
                } else {
                    designsSection.hide();
                }

                const summary = res.summary;
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                const formattedDate = new Date().toLocaleDateString('en-US', options);
                
                $('#quotation-date').text(formattedDate);
                $('#quote-subtotal').text(`₱${summary.subtotal.toFixed(2)}`);
                $('#quote-shipping').text(`₱${summary.shipping.toFixed(2)}`);
                $('#quote-handling').text(`₱${summary.handling.toFixed(2)}`);
                $('#quote-grandtotal').text(`₱${summary.total.toFixed(2)}`);

                openModal();
            }
        });
    });

    // Close modal handlers
    $('#closeModal, #closeModalBtn').click(closeModal);
    $(document).on('click', '.modal-overlay', closeModal);
    $(document).keydown(function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // Print quotation
    $('#printQuotation').click(function() {
        window.print();
    });
});

    // === Phone number validation (digits only, max 11) ===
    const phoneInput = document.querySelector("input[name='phone']");
    if (phoneInput) {
        phoneInput.addEventListener("input", () => {
            phoneInput.value = phoneInput.value.replace(/\D/g, ""); // keep only digits
            if (phoneInput.value.length > 11) {
                phoneInput.value = phoneInput.value.slice(0, 11); // limit to 11
            }
        });
    }

    // === Email validation - show warning if missing @ symbol ===
    // Note: Removed auto-append "@gmail.com" feature as it corrupts user input
    // (e.g., user typing "john.doe@company.com" would get "john.doe@gmail.com" on blur)
    const emailInput = document.querySelector("input[name='email']");
    if (emailInput) {
        emailInput.addEventListener("blur", () => {
            const val = emailInput.value.trim();
            // Only show a visual warning, don't modify the input
            if (val && !val.includes("@")) {
                emailInput.style.borderColor = "#e74c3c";
                emailInput.setAttribute("title", "Please enter a valid email address with @");
            } else {
                emailInput.style.borderColor = "";
                emailInput.removeAttribute("title");
            }
        });
    }

    // Clear errors when user interacts with fields
    document.getElementById('ewallet-radio')?.addEventListener('change', function() {
        const errorDiv = document.getElementById('payment-method-error');
        if (errorDiv) errorDiv.style.display = 'none';
    });

    document.getElementById('COD-radio')?.addEventListener('change', function() {
        const errorDiv = document.getElementById('payment-method-error');
        if (errorDiv) errorDiv.style.display = 'none';
    });

    document.getElementById('accept-terms')?.addEventListener('change', function() {
        const errorDiv = document.getElementById('terms-error');
        if (errorDiv) errorDiv.style.display = 'none';
    });

    document.getElementById('preferred_installation_date')?.addEventListener('change', function() {
        clearFieldError('preferred_installation_date', 'installation-date-error');
    });

    // === Order Confirmation Modal Functions ===
    const confirmModal = document.getElementById('orderConfirmModal');
    const closeConfirmBtn = document.getElementById('closeConfirmModal');
    const cancelOrderBtn = document.getElementById('cancelOrderBtn');
    const confirmOrderBtn = document.getElementById('confirmOrderBtn');

    // Close modal functions
    function closeConfirmModal() {
        confirmModal.classList.remove('show');
        document.body.style.overflow = '';
    }

    function openConfirmModal() {
        confirmModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    closeConfirmBtn.addEventListener('click', closeConfirmModal);
    cancelOrderBtn.addEventListener('click', closeConfirmModal);

    // Close modal when clicking outside
    confirmModal.querySelector('.modal-overlay').addEventListener('click', closeConfirmModal);

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && confirmModal.classList.contains('show')) {
            closeConfirmModal();
        }
    });

    // Populate confirmation modal with order details
    function populateConfirmModal() {
        // Get form values
        const form = document.getElementById('profileForm');
        const firstname = form.querySelector("input[name='firstname']").value;
        const middlename = form.querySelector("input[name='middlename']")?.value || '';
        const lastname = form.querySelector("input[name='lastname']").value;
        const email = form.querySelector("input[name='email']").value;
        const phone = form.querySelector("input[name='phone']").value;
        
        // Get all address fields
        const unitHouseNumber = form.querySelector("input[name='unit_house_number']")?.value || '';
        const street = form.querySelector("input[name='street']")?.value || '';
        const subdivision = form.querySelector("input[name='subdivision']")?.value || '';
        const barangay = form.querySelector("input[name='barangay']").value;
        const city = form.querySelector("input[name='city']").value;
        const province = form.querySelector("input[name='province']").value;
        const region = form.querySelector("input[name='region']")?.value || '';
        const zipcode = form.querySelector("input[name='zipcode']").value;
        const country = form.querySelector("input[name='country']").value;
        const preferredInstallationDate = form.querySelector("input[name='preferred_installation_date']")?.value || '';

        // Build complete address
        const addressParts = [
            unitHouseNumber,
            street,
            subdivision,
            barangay,
            city,
            province,
            region,
            country,
            zipcode
        ].filter(Boolean);
        const fullAddress = addressParts.join(', ');

        // Populate shipping details
        const fullName = middlename ? `${firstname} ${middlename} ${lastname}` : `${firstname} ${lastname}`;
        document.getElementById('confirm-name').textContent = fullName;
        document.getElementById('confirm-email').textContent = email;
        document.getElementById('confirm-phone').textContent = phone;
        document.getElementById('confirm-address').textContent = fullAddress;

        // Payment method
        const ewallet = document.getElementById("ewallet-radio").checked;
        const paymentBadge = document.getElementById('confirm-payment-method');
        if (ewallet) {
            paymentBadge.innerHTML = '<span class="payment-icon">💰</span><span class="payment-text">E-Wallet</span>';
            paymentBadge.className = 'payment-badge ewallet';
        } else {
            paymentBadge.innerHTML = '<span class="payment-icon">📦</span><span class="payment-text">Cash on Delivery</span>';
            paymentBadge.className = 'payment-badge cod';
        }

        // Preferred Ocular Visit Date
        const installationDateSection = document.getElementById('confirm-installation-date-section');
        const installationDateValue = document.getElementById('confirm-installation-date');
        if (preferredInstallationDate) {
            const formattedDate = new Date(preferredInstallationDate).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            installationDateValue.textContent = formattedDate;
            installationDateSection.style.display = 'block';
        } else {
            installationDateSection.style.display = 'none';
        }

        // Fetch SELECTED cart items from server via AJAX
        const itemsBody = document.getElementById('confirm-items-body');
        itemsBody.innerHTML = '<tr><td colspan="4" class="no-items">Loading items...</td></tr>';

        $.getJSON(BASE_URL + "CartCon/get_selected_cart_ajax?selected=" + SELECTED_CART_IDS, function(res) {
            if (res.status === 'success') {
                itemsBody.innerHTML = '';
                
                res.items.forEach(item => {
                    const row = document.createElement('tr');
                    const customDetails = item.customization || 'Standard';
                    const unitPrice = Number(item.unit_price) || 0;
                    const itemTotal = Number(item.total) || 0;
                    const productImage = item.image || BASE_URL + 'assets/images/default-product.png';
                    
                    row.innerHTML = `
                        <td class="product-cell">
                            <div class="product-info">
                                <img src="${productImage}" alt="${item.description}" class="product-thumb">
                                <span class="product-name">${item.description}</span>
                            </div>
                        </td>
                        <td class="details-cell">${customDetails}</td>
                        <td class="qty-cell">${item.quantity}</td>
                        <td class="price-cell">₱${itemTotal.toFixed(2)}</td>
                    `;
                    itemsBody.appendChild(row);
                });

                // Update totals from server response
                const summary = res.summary;
                document.getElementById('confirm-subtotal').textContent = `₱${summary.subtotal.toFixed(2)}`;
                document.getElementById('confirm-shipping').textContent = `₱${summary.shipping.toFixed(2)}`;
                document.getElementById('confirm-handling').textContent = `₱${summary.handling.toFixed(2)}`;
                document.getElementById('confirm-total').textContent = `₱${summary.total.toFixed(2)}`;
            } else {
                // Fallback: Get totals from page summary
                const subtotal = document.getElementById('summary-subtotal').textContent;
                const shipping = document.getElementById('summary-shipping').textContent;
                const handling = document.getElementById('summary-handling').textContent;
                const total = document.getElementById('summary-total').textContent;
                const itemCount = document.getElementById('summary-items').textContent;

                document.getElementById('confirm-subtotal').textContent = `₱${subtotal}`;
                document.getElementById('confirm-shipping').textContent = `₱${shipping}`;
                document.getElementById('confirm-handling').textContent = `₱${handling}`;
                document.getElementById('confirm-total').textContent = `₱${total}`;
                
                itemsBody.innerHTML = `<tr><td colspan="4" class="no-items">${itemCount} item(s) in your cart</td></tr>`;
            }
        }).fail(function() {
            // Fallback on AJAX failure
            const subtotal = document.getElementById('summary-subtotal').textContent;
            const shipping = document.getElementById('summary-shipping').textContent;
            const handling = document.getElementById('summary-handling').textContent;
            const total = document.getElementById('summary-total').textContent;
            const itemCount = document.getElementById('summary-items').textContent;

            document.getElementById('confirm-subtotal').textContent = `₱${subtotal}`;
            document.getElementById('confirm-shipping').textContent = `₱${shipping}`;
            document.getElementById('confirm-handling').textContent = `₱${handling}`;
            document.getElementById('confirm-total').textContent = `₱${total}`;
            
            itemsBody.innerHTML = `<tr><td colspan="4" class="no-items">${itemCount} item(s) in your cart</td></tr>`;
        });
    }

    // === Place Order button - Show confirmation modal ===
    document.getElementById("placeOrderBtn").addEventListener("click", function () {
        const ewallet = document.getElementById("ewallet-radio").checked;
        const cod = document.getElementById("COD-radio").checked;
        const termsCheckbox = document.getElementById('accept-terms');
        const termsAccepted = termsCheckbox ? termsCheckbox.checked : false;
        const preferredDateInput = document.querySelector("input[name='preferred_installation_date']");
        const preferredDate = preferredDateInput ? preferredDateInput.value : '';

        // Validate payment method
        if (!ewallet && !cod) {
            const paymentSection = document.querySelector('.payment-section');
            const errorDiv = document.getElementById('payment-method-error');
            if (errorDiv) {
                errorDiv.style.display = 'block';
            }
            showToast('Please select a payment method before placing order.', 'warning');
            paymentSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Validate terms acceptance
        if (!termsAccepted) {
            const termsContainer = document.querySelector('.terms');
            const errorDiv = document.getElementById('terms-error');
            if (errorDiv) {
                errorDiv.style.display = 'block';
            }
            showToast('Please accept the Terms and Conditions to proceed.', 'warning');
            termsCheckbox.focus();
            termsContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Validate Preferred Ocular Visit Date
        if (!preferredDate) {
            const errorDiv = document.getElementById('installation-date-error');
            if (errorDiv) {
                errorDiv.style.display = 'block';
            }
            highlightField('preferred_installation_date', 'installation-date-error', 'Please select a Preferred Ocular Visit Date.');
            showToast('Please select a Preferred Ocular Visit Date. This field is required.', 'warning');
            if (preferredDateInput) {
                preferredDateInput.focus();
                preferredDateInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        // Populate and show confirmation modal
        populateConfirmModal();
        openConfirmModal();
    });

    // === Confirm Order button - Actually place the order ===
    confirmOrderBtn.addEventListener("click", function () {
        const btn = this;
        const ewallet = document.getElementById("ewallet-radio").checked;
        const termsCheckbox = document.getElementById('accept-terms');
        const termsAccepted = termsCheckbox ? termsCheckbox.checked : false;

        // Get form data
        const form = document.getElementById('profileForm');
        const formData = new FormData(form);

        // Add payment method, terms, and SELECTED CART IDS
        const paymentMethod = ewallet ? 'E-Wallet' : 'Cash on Delivery';
        formData.append('payment_method', paymentMethod);
        formData.append('PaymentMethod', paymentMethod); // Also add as PaymentMethod for consistency
        formData.append('terms_accepted', termsAccepted ? 'true' : 'false');
        formData.append('selected_cart_ids', SELECTED_CART_IDS);
        
        // Ensure all required address fields are included
        const addressFields = [
            'unit_house_number', 'street', 'subdivision', 'barangay',
            'city', 'province', 'region', 'country', 'zipcode', 'note'
        ];
        
        addressFields.forEach(fieldName => {
            const input = form.querySelector(`input[name='${fieldName}'], textarea[name='${fieldName}']`);
            if (input && !formData.has(fieldName)) {
                formData.append(fieldName, input.value || '');
            }
        });
        
        // Build AddressLine from components for backward compatibility
        const unitHouse = form.querySelector("input[name='unit_house_number']")?.value || '';
        const street = form.querySelector("input[name='street']")?.value || '';
        const subdivision = form.querySelector("input[name='subdivision']")?.value || '';
        const addressParts = [unitHouse, street, subdivision].filter(Boolean);
        if (addressParts.length > 0) {
            formData.append('address', addressParts.join(', '));
            formData.append('AddressLine', addressParts.join(', '));
        }
        
        // Add preferred ocular visit date (required)
        const preferredDateInput = form.querySelector("input[name='preferred_installation_date']");
        if (preferredDateInput) {
            if (!preferredDateInput.value) {
                const errorDiv = document.getElementById('installation-date-error');
                if (errorDiv) {
                    errorDiv.style.display = 'block';
                }
                highlightField('preferred_installation_date', 'installation-date-error', 'Preferred Ocular Visit Date is required.');
                showToast('Preferred Ocular Visit Date is required. Please select a date.', 'warning');
                preferredDateInput.focus();
                preferredDateInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-icon">✓</span> Confirm & Place Order';
                closeConfirmModal();
                return;
            }
            formData.append('preferred_installation_date', preferredDateInput.value);
        }

        // Disable button and show loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="btn-icon">⏳</span> Processing...';

        // Store order summary in session before sending request
        // This ensures ewallet page has access to the summary
        const summaryItems = document.getElementById('summary-items').textContent;
        const summarySubtotal = document.getElementById('summary-subtotal').textContent;
        const summaryShipping = document.getElementById('summary-shipping').textContent;
        const summaryHandling = document.getElementById('summary-handling').textContent;
        const summaryTotal = document.getElementById('summary-total').textContent;
        
        // Store summary in sessionStorage as backup
        sessionStorage.setItem('order_summary', JSON.stringify({
            items: summaryItems,
            subtotal: summarySubtotal,
            shipping: summaryShipping,
            handling: summaryHandling,
            total: summaryTotal
        }));
        sessionStorage.setItem('selected_cart_ids', SELECTED_CART_IDS);

        // Send AJAX request
        fetch(BASE_URL + 'shopcon/place_order', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If response is not JSON, it's likely an error page
                const text = await response.text();
                console.error('Non-JSON response received:', text.substring(0, 500));
                throw new Error('Server returned an error page instead of JSON. Check console for details.');
            }
        })
        .then(data => {
            // Log debug info to console
            console.log('=== Place Order Response ===');
            console.log('Status:', data.status);
            console.log('Message:', data.message);
            if (data.debug) {
                console.log('=== DEBUG INFO ===');
                console.log('Customer ID:', data.debug.customer_id);
                console.log('Selected Cart IDs:', data.debug.selected_cart_ids);
                console.log('Cart Items Before Filter:', data.debug.cart_items_count_before_filter);
                console.log('Cart Items After Filter:', data.debug.cart_items_count_after_filter);
                console.log('Cart Items Raw:', data.debug.cart_items_raw);
                console.log('Selected IDs Parsed:', data.debug.selected_ids_parsed);
                console.log('Item Prices:', data.debug.item_prices);
                console.log('Calculated Totals:', data.debug.calculated_totals);
                console.log('Summary to Store:', data.debug.summary_to_store);
                console.log('Session Verification:', data.debug.session_verification);
                console.log('===================');
            }
            
            if (data.status === 'success') {
                // Show success message briefly before redirect
                console.log('Redirecting to:', data.redirect_url);
                window.location.href = data.redirect_url;
            } else {
                // Show error message with debug info
                let errorMsg = data.message || 'An error occurred. Please try again.';
                showToast(errorMsg, 'error', 5000);
                if (data.debug) {
                    console.error('Debug Info:', data.debug);
                    showToast('Check browser console (F12) for debug details.', 'info', 3000);
                }
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-icon">✓</span> Confirm & Place Order';
                closeConfirmModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again. Check console for details.', 'error', 5000);
            btn.disabled = false;
            btn.innerHTML = '<span class="btn-icon">✓</span> Confirm & Place Order';
            closeConfirmModal();
        });
    });

    
</script>