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
                <div class="info-title">
                    <h3>Shipping Address</h3>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Address line</label>
                        <input type="text" name="address"
                            value="<?= htmlspecialchars($addresses['Shipping']->AddressLine ?? '') ?>"
                            placeholder="Enter your address" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country"
                            value="<?= htmlspecialchars($addresses['Shipping']->Country ?? 'Philippines') ?>"
                            placeholder="Enter your country" required>
                    </div>
                    <div class="form-group">
                        <label>Zip code</label>
                        <input type="text" name="zipcode"
                            value="<?= htmlspecialchars($addresses['Shipping']->ZipCode ?? '') ?>"
                            placeholder="Enter your zip code" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Province</label>
                        <input type="text" name="province"
                            value="<?= htmlspecialchars($addresses['Shipping']->Province ?? '') ?>"
                            placeholder="Enter your province" required>
                    </div>
                    <div class="form-group">
                        <label>City/Municipality</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($addresses['Shipping']->City ?? '') ?>"
                            placeholder="Enter your city or municipality" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Special Instructions / Note</label>
                        <textarea name="note" rows="3" placeholder="Add special instructions or notes for delivery (optional)"><?= htmlspecialchars($addresses['Shipping']->Note ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Preferred Installation Date -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Preferred Installation Date</label>
                        <input type="date" name="preferred_installation_date" id="preferred_installation_date" 
                            min="<?= date('Y-m-d', strtotime('+7 days')) ?>" 
                            placeholder="Select your preferred installation date (optional)">
                        <small style="color: #666; font-size: 0.9em; display: block; margin-top: 5px;">
                            Please select a date at least 7 days from today. We'll do our best to accommodate your preference.
                        </small>
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

      <!-- Preferred Installation Date -->
      <div class="confirm-section" id="confirm-installation-date-section" style="display: none;">
        <h4 class="confirm-section-title">
          <span class="icon">📅</span> Preferred Installation Date
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
              $full_address = trim(($addr->AddressLine ?? '') . ', ' . ($addr->City ?? '') . ', ' . ($addr->Province ?? '') . ' ' . ($addr->ZipCode ?? ''));
              echo $full_address ?: 'N/A';
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
$(document).ready(function() {
    // =============================
    // LOAD SELECTED ITEMS SUMMARY
    // =============================
    function loadSelectedSummary() {
        // Check if we have selected items
        if (!SELECTED_CART_IDS) {
            alert('No items selected. Redirecting to cart...');
            window.location.href = BASE_URL + 'addtocart';
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
                        alert('No valid items found. Redirecting to cart...');
                        window.location.href = BASE_URL + 'addtocart';
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
        const address = form.querySelector("input[name='address']").value;
        const city = form.querySelector("input[name='city']").value;
        const province = form.querySelector("input[name='province']").value;
        const zipcode = form.querySelector("input[name='zipcode']").value;
        const country = form.querySelector("input[name='country']").value;
        const preferredInstallationDate = form.querySelector("input[name='preferred_installation_date']")?.value || '';

        // Populate shipping details
        const fullName = middlename ? `${firstname} ${middlename} ${lastname}` : `${firstname} ${lastname}`;
        document.getElementById('confirm-name').textContent = fullName;
        document.getElementById('confirm-email').textContent = email;
        document.getElementById('confirm-phone').textContent = phone;
        document.getElementById('confirm-address').textContent = `${address}, ${city}, ${province} ${zipcode}, ${country}`;

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

        // Preferred Installation Date
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

        // Validate payment method
        if (!ewallet && !cod) {
            alert("Please select a payment method before placing order.");
            return;
        }

        // Validate terms acceptance
        if (!termsAccepted) {
            alert("Please accept the Terms and Conditions to proceed.");
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
        if (!formData.has('address') || !formData.get('address')) {
            const addressInput = form.querySelector("input[name='address']");
            if (addressInput) formData.append('address', addressInput.value || '');
        }
        if (!formData.has('city') || !formData.get('city')) {
            const cityInput = form.querySelector("input[name='city']");
            if (cityInput) formData.append('city', cityInput.value || '');
        }
        if (!formData.has('province') || !formData.get('province')) {
            const provinceInput = form.querySelector("input[name='province']");
            if (provinceInput) formData.append('province', provinceInput.value || '');
        }
        if (!formData.has('country') || !formData.get('country')) {
            const countryInput = form.querySelector("input[name='country']");
            if (countryInput) formData.append('country', countryInput.value || '');
        }
        if (!formData.has('zipcode') || !formData.get('zipcode')) {
            const zipcodeInput = form.querySelector("input[name='zipcode']");
            if (zipcodeInput) formData.append('zipcode', zipcodeInput.value || '');
        }
        if (!formData.has('note') || !formData.get('note')) {
            const noteInput = form.querySelector("textarea[name='note']");
            if (noteInput) formData.append('note', noteInput.value || '');
        }
        // Add preferred installation date
        if (!formData.has('preferred_installation_date')) {
            const preferredDateInput = form.querySelector("input[name='preferred_installation_date']");
            if (preferredDateInput && preferredDateInput.value) {
                formData.append('preferred_installation_date', preferredDateInput.value);
            }
        }

        // Disable button and show loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="btn-icon">⏳</span> Processing...';

        // Send AJAX request
        fetch(BASE_URL + 'shopcon/place_order', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
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
                if (data.debug) {
                    errorMsg += '\n\nDebug: Check browser console (F12) for details.';
                }
                alert(errorMsg);
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-icon">✓</span> Confirm & Place Order';
                closeConfirmModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<span class="btn-icon">✓</span> Confirm & Place Order';
            closeConfirmModal();
        });
    });

    
</script>