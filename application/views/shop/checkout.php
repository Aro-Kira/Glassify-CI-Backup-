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
                <!-- Shipping Address -->
                <div class="shipping-address-title">
                    <h3>Shipping Address</h3>
                </div>
                
                <!-- User Info -->
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name <span style="color: red;">*</span></label>
                        <input type="text" name="firstname" value="<?= htmlspecialchars($user->First_Name ?? '') ?>"
                            placeholder="Enter your first name" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middlename" value="<?= htmlspecialchars($user->Middle_Name ?? '') ?>"
                            placeholder="Enter your middle name (optional)">
                    </div>
                    <div class="form-group">
                        <label>Last Name <span style="color: red;">*</span></label>
                        <input type="text" name="lastname" value="<?= htmlspecialchars($user->Last_Name ?? '') ?>"
                            placeholder="Enter your last name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email address <span style="color: red;">*</span></label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user->Email) ?>"
                            placeholder="Enter your email address" required>
                    </div>
                    <div class="form-group">
                        <label>Phone number <span style="color: red;">*</span></label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($user->PhoneNum) ?>" maxlength="11"
                            placeholder="Enter your phone number" required>
                    </div>
                </div>
                
                <!-- Saved Address Selector -->
                <?php if (isset($all_addresses) && !empty($all_addresses)): ?>
                <div class="saved-address-selector" style="margin-bottom: 20px;">
                    <label for="saved-address-dropdown" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0f2b46;">
                        Select from Saved Addresses
                    </label>
                    <select id="saved-address-dropdown" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; background: white; cursor: pointer; margin-bottom: 20px;">
                        <option value="">-- Select a saved address --</option>
                        <?php foreach ($all_addresses as $addr): ?>
                            <?php 
                            $addressLabel = '';
                            $parts = array_filter([
                                $addr->UnitHouseNumber ?? '',
                                $addr->Street ?? '',
                                $addr->Subdivision ?? '',
                                $addr->Barangay ?? '',
                                $addr->City ?? '',
                                $addr->Province ?? ''
                            ]);
                            if (!empty($parts)) {
                                $addressLabel = implode(', ', $parts);
                            } else {
                                $addressLabel = $addr->AddressLine ?? 'Address #' . $addr->AddressID;
                            }
                            ?>
                            <option value="<?= $addr->AddressID ?>" 
                                    data-address='<?= json_encode($addr) ?>'
                                    <?= (isset($addresses['Shipping']) && $addresses['Shipping']->AddressID == $addr->AddressID) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($addressLabel) ?><?= ($addr->IsDefault == 1) ? ' (Default)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="terms" style="margin-bottom: 20px;">
                        <input type="checkbox" id="use-different-shipping-address"> 
                        <label for="use-different-shipping-address">Use a different address</label>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Shipping Address Form Fields (hidden by default if saved addresses exist) -->
                <div id="shipping-address-fields" style="<?= (isset($all_addresses) && !empty($all_addresses)) ? 'display: none;' : '' ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Country <span style="color: red;">*</span></label>
                        <input type="text" name="country" id="shipping-country"
                            value="Philippines"
                            placeholder="Country" required readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Region <span style="color: red;">*</span></label>
                        <select name="region" id="shipping-region" required>
                            <option value="" <?= (!isset($addresses['Shipping']->Region) || empty($addresses['Shipping']->Region)) ? 'selected' : '' ?>>Select Region</option>
                            <option value="NCR" <?= (isset($addresses['Shipping']->Region) && $addresses['Shipping']->Region === 'NCR') ? 'selected' : '' ?>>NCR (National Capital Region)</option>
                            <option value="Region III" <?= (isset($addresses['Shipping']->Region) && $addresses['Shipping']->Region === 'Region III') ? 'selected' : '' ?>>Region III (Central Luzon)</option>
                            <option value="Region IV-A" <?= (isset($addresses['Shipping']->Region) && $addresses['Shipping']->Region === 'Region IV-A') ? 'selected' : '' ?>>Region IV-A (CALABARZON)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Province <span style="color: red;">*</span></label>
                        <select name="province" id="shipping-province" required>
                            <option value="">Select Province</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City/Municipality <span style="color: red;">*</span></label>
                        <select name="city" id="shipping-city" required>
                            <option value="">Select City/Municipality</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Barangay <span style="color: red;">*</span></label>
                        <input type="text" name="barangay"
                            value="<?= htmlspecialchars($addresses['Shipping']->Barangay ?? '') ?>"
                            placeholder="Enter Barangay" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Subdivision/Building</label>
                        <input type="text" name="subdivision"
                            value="<?= htmlspecialchars($addresses['Shipping']->Subdivision ?? '') ?>"
                            placeholder="Subdivision/Building (optional)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Street</label>
                        <input type="text" name="street"
                            value="<?= htmlspecialchars($addresses['Shipping']->Street ?? '') ?>"
                            placeholder="Street (optional)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Unit/House Number <span style="color: red;">*</span></label>
                        <input type="text" name="unit_house_number"
                            value="<?= htmlspecialchars($addresses['Shipping']->UnitHouseNumber ?? '') ?>"
                            placeholder="Unit/House Number" required>
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

                </div>
                <!-- End Shipping Address Form Fields -->
                
                <!-- Special Instructions / Note (Always visible, not tied to saved address) -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Special Instructions / Note</label>
                        <textarea name="note" rows="3" placeholder="Add special instructions or notes for delivery (optional)"><?= htmlspecialchars($addresses['Shipping']->Note ?? '') ?></textarea>
                    </div>
                </div>

            </form>
            
            <!-- Billing Address Section (Separate Box, but inside same section) -->
            <form id="billingForm" style="margin-top: 30px; border-top: 2px solid #e0e0e0; padding-top: 30px;">
                <!-- Billing Address Title -->
                <div class="shipping-address-title">
                    <h3>Billing Address</h3>
                </div>
                
                <!-- Billing Form Container -->
                <div id="billingFormContainer">
                
                <!-- Same as Shipping Checkbox -->
                <div class="terms" style="margin-bottom: 20px;">
                    <input type="checkbox" id="same-billing"> 
                    <label for="same-billing">Make billing address same as shipping</label>
                </div>
                
                <!-- Billing Address Form Fields -->
                <div id="billing-address-fields">
                    <!-- Billing Contact Information -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name <span style="color: red;">*</span></label>
                            <input type="text" name="billing_firstname" id="billing_firstname"
                                value=""
                                placeholder="Enter your first name" required>
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="billing_middlename" id="billing_middlename"
                                value=""
                                placeholder="Enter your middle name (optional)">
                        </div>
                        <div class="form-group">
                            <label>Last Name <span style="color: red;">*</span></label>
                            <input type="text" name="billing_lastname" id="billing_lastname"
                                value=""
                                placeholder="Enter your last name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email address <span style="color: red;">*</span></label>
                            <input type="email" name="billing_email" id="billing_email"
                                value=""
                                placeholder="Enter your email address" required>
                        </div>
                        <div class="form-group">
                            <label>Phone number <span style="color: red;">*</span></label>
                            <input type="tel" name="billing_phone" id="billing_phone"
                                value="" maxlength="11"
                                placeholder="Enter your phone number" required>
                        </div>
                    </div>

                    <!-- Billing Address Fields -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Country <span style="color: red;">*</span></label>
                            <input type="text" name="billing_country" id="billing-country"
                                value="Philippines"
                                placeholder="Country" required readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Region <span style="color: red;">*</span></label>
                            <select name="billing_region" id="billing-region" required>
                                <option value="" <?= (!isset($addresses['Billing']->Region) || empty($addresses['Billing']->Region)) ? 'selected' : '' ?>>Select Region</option>
                                <option value="NCR" <?= (isset($addresses['Billing']->Region) && $addresses['Billing']->Region === 'NCR') ? 'selected' : '' ?>>NCR (National Capital Region)</option>
                                <option value="Region III" <?= (isset($addresses['Billing']->Region) && $addresses['Billing']->Region === 'Region III') ? 'selected' : '' ?>>Region III (Central Luzon)</option>
                                <option value="Region IV-A" <?= (isset($addresses['Billing']->Region) && $addresses['Billing']->Region === 'Region IV-A') ? 'selected' : '' ?>>Region IV-A (CALABARZON)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Province <span style="color: red;">*</span></label>
                            <select name="billing_province" id="billing-province" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>City/Municipality <span style="color: red;">*</span></label>
                            <select name="billing_city" id="billing-city" required>
                                <option value="">Select City/Municipality</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Barangay <span style="color: red;">*</span></label>
                            <input type="text" name="billing_barangay" id="billing_barangay"
                                value=""
                                placeholder="Enter Barangay" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Subdivision/Building</label>
                            <input type="text" name="billing_subdivision" id="billing_subdivision"
                                value=""
                                placeholder="Subdivision/Building (optional)">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Street</label>
                            <input type="text" name="billing_street" id="billing_street"
                                value=""
                                placeholder="Street (optional)">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Unit/House Number <span style="color: red;">*</span></label>
                            <input type="text" name="billing_unit_house_number" id="billing_unit_house_number"
                                value=""
                                placeholder="Unit/House Number" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Zip Code <span style="color: red;">*</span></label>
                            <input type="text" name="billing_zipcode" id="billing_zipcode"
                                value=""
                                placeholder="Enter Zip Code" required>
                        </div>
                    </div>
                </div>
                <!-- End Billing Address Form Fields -->
                </div>
                <!-- End Billing Form Container -->
            </form>
        </section>


        <!-- Order Summary Section -->
        <section class="order-summary">
            <div class="order-summary-content">
                <h3>Order Summary</h3>
                
                <!-- Itemized List -->
                <div id="summary-items-list" style="max-height: 350px; overflow-y: auto; margin-bottom: 15px; padding-bottom: 10px;">
                    <!-- Items will be dynamically populated -->
                    <div style="text-align: center; color: #888; padding: 10px;">Loading items...</div>
                </div>

                <div class="summary-totals-box" style="padding-top: 15px;">
                    <p><span>Subtotal:</span> <span id="summary-subtotal">₱0.00</span></p>
                    <p><span>Shipping Fee:</span> <span id="summary-shipping">₱0.00</span></p>
                    <p><span>Handling Fee:</span> <span id="summary-handling">₱0.00</span></p>
                    <p class="total"><span>Total:</span> <span id="summary-total">₱0.00</span></p>
                </div>
            </div>
            <div class="payment-section">
                <div class="payment-method-content">
                    <h3>Payment Methods</h3>
                    <p>
                        <img src="<?php echo base_url('assets/images/img-page/atm-card.png'); ?>" alt="card-icon">
                        <label for="card-radio">Credit / Debit Card</label>
                        <input type="radio" id="card-radio" name="payment-method" value="card" title="Select Credit or Debit Card as payment method">
                    </p>
                    <p>
                        <img src="<?php echo base_url('assets/images/img-page/dollar.png'); ?>" alt="gcash-icon">
                        <label for="gcash-radio">GCash</label>
                        <input type="radio" id="gcash-radio" name="payment-method" value="gcash" title="Select GCash as payment method">
                    </p>
                    <p>
                        <img src="<?php echo base_url('assets/images/img-page/dollar.png'); ?>" alt="maya-icon">
                        <label for="maya-radio">Maya</label>
                        <input type="radio" id="maya-radio" name="payment-method" value="maya" title="Select Maya as payment method">
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

    <script>
        // Reset scroll position on page load
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        window.scrollTo(0, 0);

        // Reset scroll on any navigation links or buttons that might change content/steps
        document.addEventListener('click', function(e) {
            const target = e.target.closest('a, button, .step, .nav-btn');
            if (target) {
                // Small delay to ensure any dynamic content/page change has started
                setTimeout(() => window.scrollTo({ top: 0, behavior: 'smooth' }), 50);
            }
        });
    </script>
</main>




<!-- Calendar Modal -->
<div id="calendarModal" class="modal">
  <div class="modal-overlay" onclick="closeCalendarModal()"></div>
  <div class="modal-content" style="max-width: 400px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
    <button class="modal-close" onclick="closeCalendarModal()" style="top: 10px; right: 10px; color: white;">&times;</button>
    <div class="modal-header" style="background: #0f2b46; color: white; padding: 20px; border-bottom: none; display: flex; flex-direction: column; align-items: flex-start;">
      <h3 style="margin: 0; font-size: 1.25rem;">📅 Ocular Visit Schedule</h3>
      <p style="margin: 8px 0 0 0; font-size: 0.85rem; opacity: 0.9; color: white;">Select your preferred visit date</p>
    </div>
    <div class="modal-body" style="padding: 0;">
        <div id="custom-checkout-calendar" style="border: none; border-radius: 0; background: white; width: 100%;">
            <div class="calendar-header" style="background: #1a3a5a; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" id="cal-prev-month" style="background: rgba(255,255,255,0.1); border: none; color: white; cursor: pointer; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">&lt;</button>
                <h4 id="cal-month-year" style="margin: 0; font-size: 1.1rem; font-weight: 600;">Month Year</h4>
                <button type="button" id="cal-next-month" style="background: rgba(255,255,255,0.1); border: none; color: white; cursor: pointer; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">&gt;</button>
            </div>
            <table style="width: 100%; border-collapse: collapse; text-align: center; table-layout: fixed;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px 0; color: #888; font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">Su</th>
                        <th style="padding: 12px 0; color: #888; font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">Mo</th>
                        <th style="padding: 12px 0; color: #888; font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">Tu</th>
                        <th style="padding: 12px 0; color: #888; font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">We</th>
                        <th style="padding: 12px 0; color: #888; font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">Th</th>
                        <th style="padding: 12px 0; color: #888; font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">Fr</th>
                        <th style="padding: 12px 0; color: #888; font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">Sa</th>
                    </tr>
                </thead>
                <tbody id="cal-body">
                    <!-- Days populated by JS -->
                </tbody>
            </table>
            <div class="calendar-legend" style="padding: 15px 20px; border-top: 1px solid #eee; display: flex; justify-content: space-around; font-size: 0.7rem; color: #666; background: #fafafa;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="width: 12px; height: 12px; border-radius: 50%; background: #d9534f; display: inline-block;"></span> Today
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="width: 12px; height: 12px; border-radius: 50%; background: #0f2b46; display: inline-block;"></span> Selected
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="width: 12px; height: 12px; border-radius: 50%; background: #eee; border: 1px solid #ddd; display: inline-block;"></span> Unavailable
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer" style="padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 10px;">
      <button type="button" class="secondary-btn" onclick="closeCalendarModal()" style="padding: 8px 20px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; cursor: pointer;">Cancel</button>
      <button type="button" class="primary-btn" id="confirm-date-btn" style="padding: 8px 25px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; cursor: pointer; background: #0f2b46; color: white; border: none; transition: background 0.2s;" disabled>Save Selection</button>
    </div>
  </div>
</div>


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
                <th>Customization</th>
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
      <button class="btn-confirm-order" id="confirmOrderBtn">Confirm & Place Order</button>
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
                    const items = res.items;

                    // Update order summary - ensure elements exist
                    const itemsEl = document.getElementById('summary-items');
                    const subtotalEl = document.getElementById('summary-subtotal');
                    const shippingEl = document.getElementById('summary-shipping');
                    const handlingEl = document.getElementById('summary-handling');
                    const totalEl = document.getElementById('summary-total');
                    const itemsListEl = document.getElementById('summary-items-list');
                    
                    if (itemsEl) itemsEl.textContent = summary.items || 0;
                    if (subtotalEl) subtotalEl.textContent = '₱' + (summary.subtotal || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (shippingEl) shippingEl.textContent = '₱' + (summary.shipping || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (handlingEl) handlingEl.textContent = '₱' + (summary.handling || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (totalEl) totalEl.textContent = '₱' + (summary.total || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

                    // Populate itemized list
                    if (itemsListEl) {
                        itemsListEl.innerHTML = '';
                        if (items && items.length > 0) {
                            items.forEach(item => {
                                const itemDiv = document.createElement('div');
                                itemDiv.className = 'summary-item-row';
                                itemDiv.style.cssText = 'display: flex; gap: 15px; padding: 15px; border: 1px solid #f0f0f0; border-radius: 10px; margin-bottom: 12px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.02); align-items: center; position: relative;';
                                
                                itemDiv.innerHTML = `
                                    <img src="${item.image}" alt="${item.description}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; flex-shrink: 0;">
                                    <div class="summary-item-info">
                                        <h4>${item.description}</h4>
                                        <span class="summary-item-qty">Qty: ${item.quantity}</span>
                                    </div>
                                `;
                                itemsListEl.appendChild(itemDiv);
                            });
                        } else {
                            itemsListEl.innerHTML = '<div style="text-align: center; color: #888; padding: 10px;">No items found.</div>';
                        }
                    }

                    // Check if cart is empty
                    if (res.items.length === 0) {
                        showToast('No valid items found. Redirecting to cart...', 'warning', 2000);
                        setTimeout(() => {
                            window.location.href = BASE_URL + 'addtocart';
                        }, 2000);
                    }
                } else {
                    console.error('Failed to load summary:', res.message || 'Unknown error');
                    // Set default values if API fails
                    const itemsEl = document.getElementById('summary-items');
                    const subtotalEl = document.getElementById('summary-subtotal');
                    const shippingEl = document.getElementById('summary-shipping');
                    const handlingEl = document.getElementById('summary-handling');
                    const totalEl = document.getElementById('summary-total');
                    
                    if (itemsEl) itemsEl.textContent = '0';
                    if (subtotalEl) subtotalEl.textContent = '₱0.00';
                    if (shippingEl) shippingEl.textContent = '₱0.00';
                    if (handlingEl) handlingEl.textContent = '₱0.00';
                    if (totalEl) totalEl.textContent = '₱0.00';
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load cart summary:', error);
                // Set default values on error
                const itemsEl = document.getElementById('summary-items');
                const subtotalEl = document.getElementById('summary-subtotal');
                const shippingEl = document.getElementById('summary-shipping');
                const handlingEl = document.getElementById('summary-handling');
                const totalEl = document.getElementById('summary-total');
                
                if (itemsEl) itemsEl.textContent = '0';
                if (subtotalEl) subtotalEl.textContent = '0.00';
                if (shippingEl) shippingEl.textContent = '0.00';
                if (handlingEl) handlingEl.textContent = '0.00';
                if (totalEl) totalEl.textContent = '0.00';
            }
        });
    }

    // Initial load - wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadSelectedSummary);
    } else {
        loadSelectedSummary();
    }

    // === Saved Address Selector ===
    const savedAddressDropdown = document.getElementById('saved-address-dropdown');
    const useDifferentShippingCheckbox = document.getElementById('use-different-shipping-address');
    const shippingAddressFields = document.getElementById('shipping-address-fields');
    
    // Function to toggle shipping form fields and dropdown
    function toggleShippingForm(show) {
        console.log('toggleShippingForm called with:', show);
        if (shippingAddressFields) {
            if (show) {
                shippingAddressFields.style.display = 'block';
                console.log('Shipping form fields shown');
                // Clear all form fields except country when showing form
                clearShippingForm();
                // Ensure dropdowns are initialized when form is shown
                setupShippingDropdowns();
            } else {
                shippingAddressFields.style.display = 'none';
                console.log('Shipping form fields hidden');
            }
        } else {
            console.error('shippingAddressFields element not found!');
        }
        if (savedAddressDropdown) {
            savedAddressDropdown.disabled = show;
        } else {
            console.log('savedAddressDropdown not found (might not have saved addresses)');
        }
    }
    
    // Function to clear shipping form fields (except country)
    function clearShippingForm() {
        const form = document.getElementById('profileForm');
        if (!form) return;
        
        // Clear text inputs
        const fieldsToClear = ['barangay', 'subdivision', 'street', 'unit_house_number', 'zipcode', 'note'];
        fieldsToClear.forEach(fieldName => {
            const input = form.querySelector(`input[name='${fieldName}'], textarea[name='${fieldName}']`);
            if (input) input.value = '';
        });
        
        // Reset dropdowns to default
        const regionSelect = document.getElementById('shipping-region');
        const provinceSelect = document.getElementById('shipping-province');
        const citySelect = document.getElementById('shipping-city');
        
        if (regionSelect) {
            regionSelect.value = '';
            // Trigger change to reset province and city
            regionSelect.dispatchEvent(new Event('change'));
        }
        if (provinceSelect) {
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            provinceSelect.value = '';
        }
        if (citySelect) {
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            citySelect.value = '';
        }
        
        // Ensure country is set to Philippines
        const countryInput = document.getElementById('shipping-country');
        if (countryInput) {
            countryInput.value = 'Philippines';
        }
    }
    
    // Checkbox handler: "Use a different address"
    if (useDifferentShippingCheckbox) {
        console.log('Checkbox found, attaching event listener');
        useDifferentShippingCheckbox.addEventListener('change', function() {
            console.log('Checkbox changed! Checked:', this.checked);
            if (this.checked) {
                // Show form fields and disable dropdown
                toggleShippingForm(true);
            } else {
                // Hide form fields and enable dropdown
                toggleShippingForm(false);
            }
        });
    } else {
        console.log('useDifferentShippingCheckbox not found (might not have saved addresses)');
    }

    // Dropdown handler: When a saved address is selected
    if (savedAddressDropdown) {
        savedAddressDropdown.addEventListener('change', function() {
            // Don't process if dropdown is disabled (checkbox is checked)
            if (this.disabled) {
                return;
            }
            
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value === '') {
                // No selection - hide form
                toggleShippingForm(false);
                if (useDifferentShippingCheckbox) {
                    useDifferentShippingCheckbox.checked = false;
                }
                return;
            }
            
            // Get address data from data attribute
            const addressData = selectedOption.getAttribute('data-address');
            if (addressData) {
                try {
                    const address = JSON.parse(addressData);
                    
                    // Uncheck the "Use a different address" checkbox
                    if (useDifferentShippingCheckbox) {
                        useDifferentShippingCheckbox.checked = false;
                    }
                    
                    // Hide form fields after selecting saved address
                    toggleShippingForm(false);
                    
                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast('Address loaded successfully!', 'success', 2000);
                    }
                } catch (e) {
                    console.error('Error parsing address data:', e);
                }
            }
        });
    }
    
    // === Same Billing Address Checkbox Handler ===
    const sBillingCheckbox = document.getElementById('same-billing');
    const bAddressFields = document.getElementById('billing-address-fields');
    
    if (sBillingCheckbox && bAddressFields) {
        sBillingCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Hide billing address fields
                bAddressFields.style.display = 'none';
                
                // Copy shipping address data to billing fields
                const form = document.getElementById('profileForm');
                const billingForm = document.getElementById('billingForm');
                
                if (form && billingForm) {
                    // Copy contact info
                    const shippingFirstname = form.querySelector("input[name='firstname']")?.value || '';
                    const shippingMiddlename = form.querySelector("input[name='middlename']")?.value || '';
                    const shippingLastname = form.querySelector("input[name='lastname']")?.value || '';
                    const shippingEmail = form.querySelector("input[name='email']")?.value || '';
                    const shippingPhone = form.querySelector("input[name='phone']")?.value || '';
                    
                    const billingFirstname = billingForm.querySelector("input[name='billing_firstname']");
                    const billingMiddlename = billingForm.querySelector("input[name='billing_middlename']");
                    const billingLastname = billingForm.querySelector("input[name='billing_lastname']");
                    const billingEmail = billingForm.querySelector("input[name='billing_email']");
                    const billingPhone = billingForm.querySelector("input[name='billing_phone']");
                    
                    if (billingFirstname) billingFirstname.value = shippingFirstname;
                    if (billingMiddlename) billingMiddlename.value = shippingMiddlename;
                    if (billingLastname) billingLastname.value = shippingLastname;
                    if (billingEmail) billingEmail.value = shippingEmail;
                    if (billingPhone) billingPhone.value = shippingPhone;
                    
                    // Copy address fields
                    const shippingFields = ['unit_house_number', 'street', 'subdivision', 'barangay', 'country', 'zipcode'];
                    shippingFields.forEach(field => {
                        const shippingInput = form.querySelector(`input[name='${field}']`);
                        const billingInput = billingForm.querySelector(`input[name='billing_${field}']`);
                        if (shippingInput && billingInput) {
                            billingInput.value = shippingInput.value || '';
                        }
                    });
                    
                    // Copy region, province, city (select dropdowns)
                    const shippingRegion = form.querySelector("select[name='region']");
                    const billingRegion = billingForm.querySelector("select[name='billing_region']");
                    if (shippingRegion && billingRegion && shippingRegion.value) {
                        billingRegion.value = shippingRegion.value;
                        billingRegion.dispatchEvent(new Event('change'));
                        setTimeout(() => {
                            const shippingProvince = form.querySelector("select[name='province']");
                            const billingProvince = billingForm.querySelector("select[name='billing_province']");
                            if (shippingProvince && billingProvince && shippingProvince.value) {
                                billingProvince.value = shippingProvince.value;
                                billingProvince.dispatchEvent(new Event('change'));
                                setTimeout(() => {
                                    const shippingCity = form.querySelector("select[name='city']");
                                    const billingCity = billingForm.querySelector("select[name='billing_city']");
                                    if (shippingCity && billingCity && shippingCity.value) {
                                        billingCity.value = shippingCity.value;
                                    }
                                }, 100);
                            }
                        }, 100);
                    }
                }
            } else {
                // Show billing address fields (dropdowns are already enabled, no disabled attribute in HTML)
                bAddressFields.style.display = 'block';
            }
        });
        
        // Initialize on page load
        if (sBillingCheckbox.checked) {
            bAddressFields.style.display = 'none';
        }
    }

    // === PHILIPPINE REGIONS AND CITIES DATA ===
    const metroManilaCities = [
        'Caloocan', 'Las Piñas', 'Makati', 'Malabon', 'Mandaluyong',
        'Manila', 'Marikina', 'Muntinlupa', 'Navotas', 'Parañaque',
        'Pasay', 'Pasig', 'Quezon City', 'San Juan', 'Taguig', 'Valenzuela'
    ];
    
    // Region III (Central Luzon) - Provinces and Cities
    const region3Provinces = {
        'Aurora': ['Baler', 'Casiguran', 'Dilasag', 'Dinalungan', 'Dingalan', 'Dipaculao', 'Maria Aurora', 'San Luis'],
        'Bataan': ['Abucay', 'Bagac', 'Balanga', 'Dinalupihan', 'Hermosa', 'Limay', 'Mariveles', 'Morong', 'Orani', 'Orion', 'Pilar', 'Samal'],
        'Bulacan': ['Angat', 'Balagtas', 'Baliuag', 'Bocaue', 'Bulakan', 'Bustos', 'Calumpit', 'Doña Remedios Trinidad', 'Guiguinto', 'Hagonoy', 'Malolos', 'Marilao', 'Meycauayan', 'Norzagaray', 'Obando', 'Pandi', 'Paombong', 'Plaridel', 'Pulilan', 'San Ildefonso', 'San Jose del Monte', 'San Miguel', 'San Rafael', 'Santa Maria', 'Valenzuela'],
        'Nueva Ecija': ['Aliaga', 'Bongabon', 'Cabanatuan', 'Cabiao', 'Carranglan', 'Cuyapo', 'Gabaldon', 'Gapan', 'General Mamerto Natividad', 'General Tinio', 'Guimba', 'Jaen', 'Laur', 'Licab', 'Llanera', 'Lupao', 'Muñoz', 'Nampicuan', 'Palayan', 'Pantabangan', 'Peñaranda', 'Quezon', 'Rizal', 'San Antonio', 'San Isidro', 'San Jose', 'San Leonardo', 'Santa Rosa', 'Santo Domingo', 'Talavera', 'Talugtug', 'Zaragoza'],
        'Pampanga': ['Angeles', 'Apalit', 'Arayat', 'Bacolor', 'Candaba', 'Floridablanca', 'Guagua', 'Lubao', 'Mabalacat', 'Macabebe', 'Magalang', 'Masantol', 'Mexico', 'Minalin', 'Porac', 'San Fernando', 'San Luis', 'San Simon', 'Santa Ana', 'Santa Rita', 'Santo Tomas', 'Sasmuan'],
        'Tarlac': ['Anao', 'Bamban', 'Camiling', 'Capas', 'Concepcion', 'Gerona', 'La Paz', 'Mayantoc', 'Moncada', 'Paniqui', 'Pura', 'Ramos', 'San Clemente', 'San Jose', 'San Manuel', 'Santa Ignacia', 'Tarlac City', 'Victoria'],
        'Zambales': ['Botolan', 'Cabangan', 'Candelaria', 'Castillejos', 'Iba', 'Masinloc', 'Olongapo', 'Palauig', 'San Antonio', 'San Felipe', 'San Marcelino', 'San Narciso', 'Santa Cruz', 'Subic']
    };
    
    // Region IV-A (CALABARZON) - Provinces and Cities
    const region4AProvinces = {
        'Batangas': ['Agoncillo', 'Alitagtag', 'Balayan', 'Balete', 'Bauan', 'Calaca', 'Calatagan', 'Cuenca', 'Ibaan', 'Laurel', 'Lemery', 'Lian', 'Lipa', 'Lobo', 'Mabini', 'Malvar', 'Mataasnakahoy', 'Nasugbu', 'Padre Garcia', 'Rosario', 'San Jose', 'San Juan', 'San Luis', 'San Nicolas', 'San Pascual', 'Santa Teresita', 'Santo Tomas', 'Taal', 'Talisay', 'Tanauan', 'Taysan', 'Tingloy', 'Tuy'],
        'Cavite': ['Alfonso', 'Amadeo', 'Bacoor', 'Carmona', 'Cavite City', 'Dasmariñas', 'General Emilio Aguinaldo', 'General Mariano Alvarez', 'General Trias', 'Imus', 'Indang', 'Kawit', 'Magallanes', 'Maragondon', 'Mendez', 'Naic', 'Noveleta', 'Rosario', 'Silang', 'Tagaytay', 'Tanza', 'Ternate', 'Trece Martires', 'Tagaytay'],
        'Laguna': ['Alaminos', 'Bay', 'Biñan', 'Cabuyao', 'Calamba', 'Calauan', 'Cavinti', 'Famy', 'Kalayaan', 'Liliw', 'Los Baños', 'Luisiana', 'Lumban', 'Mabitac', 'Magdalena', 'Majayjay', 'Nagcarlan', 'Paete', 'Pagsanjan', 'Pakil', 'Pangil', 'Pila', 'Rizal', 'San Pablo', 'San Pedro', 'Santa Cruz', 'Santa Maria', 'Santa Rosa', 'Siniloan', 'Victoria'],
        'Quezon': ['Agdangan', 'Alabat', 'Atimonan', 'Buenavista', 'Burdeos', 'Calauag', 'Candelaria', 'Catanauan', 'Dolores', 'General Luna', 'General Nakar', 'Guinayangan', 'Gumaca', 'Infanta', 'Jomalig', 'Lopez', 'Lucban', 'Lucena', 'Macalelon', 'Mauban', 'Mulanay', 'Padre Burgos', 'Pagbilao', 'Panukulan', 'Patnanungan', 'Perez', 'Pitogo', 'Plaridel', 'Polillo', 'Quezon', 'Real', 'Sampaloc', 'San Andres', 'San Antonio', 'San Francisco', 'San Narciso', 'Sariaya', 'Tagkawayan', 'Tayabas', 'Tiaong', 'Unisan'],
        'Rizal': ['Angono', 'Antipolo', 'Baras', 'Binangonan', 'Cainta', 'Cardona', 'Jalajala', 'Morong', 'Pililla', 'Rodriguez', 'San Mateo', 'Tanay', 'Taytay', 'Teresa']
    };
    
    // Function to populate provinces and cities for shipping
    function setupShippingDropdowns() {
        const shippingRegion = document.getElementById('shipping-region');
        const shippingProvince = document.getElementById('shipping-province');
        const shippingCity = document.getElementById('shipping-city');
        
        if (!shippingRegion || !shippingProvince || !shippingCity) {
            console.log('Shipping dropdowns not found, skipping initialization');
            return;
        }
        
        // Remove existing event listeners if they exist (by checking if already initialized)
        if (shippingRegion.dataset.initialized === 'true') {
            console.log('Shipping dropdowns already initialized');
            return;
        }
        
        console.log('Initializing shipping dropdowns');
        // Mark as initialized
        shippingRegion.dataset.initialized = 'true';
        shippingProvince.dataset.initialized = 'true';
        shippingCity.dataset.initialized = 'true';
        
        shippingRegion.addEventListener('change', function() {
            const selectedRegion = this.value;
            console.log('Shipping region changed to:', selectedRegion);
            shippingProvince.innerHTML = '<option value="">Select Province</option>';
            shippingCity.innerHTML = '<option value="">Select City/Municipality</option>';
            
            if (!selectedRegion) return;
            
            if (selectedRegion === "NCR") {
                shippingProvince.innerHTML = '<option value="Metro Manila">Metro Manila</option>';
                shippingProvince.value = "Metro Manila";
                // Trigger change to populate cities
                setTimeout(() => {
                    shippingProvince.dispatchEvent(new Event('change'));
                }, 50);
            } else if (selectedRegion === "Region III") {
                // Only show Bulacan for Region III
                const option = document.createElement('option');
                option.value = 'Bulacan';
                option.textContent = 'Bulacan';
                shippingProvince.appendChild(option);
            } else if (selectedRegion === "Region IV-A") {
                Object.keys(region4AProvinces).forEach(province => {
                    const option = document.createElement('option');
                    option.value = province;
                    option.textContent = province;
                    shippingProvince.appendChild(option);
                });
            }
        });
        
        shippingProvince.addEventListener('change', function() {
            const selectedProvince = this.value;
            const selectedRegion = shippingRegion.value;
            console.log('Shipping province changed to:', selectedProvince, 'in region:', selectedRegion);
            shippingCity.innerHTML = '<option value="">Select City/Municipality</option>';
            
            if (selectedProvince === "Metro Manila") {
                metroManilaCities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    shippingCity.appendChild(option);
                });
            } else if (selectedRegion === "Region III" && region3Provinces[selectedProvince]) {
                region3Provinces[selectedProvince].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    shippingCity.appendChild(option);
                });
            } else if (selectedRegion === "Region IV-A" && region4AProvinces[selectedProvince]) {
                region4AProvinces[selectedProvince].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    shippingCity.appendChild(option);
                });
            }
        });
    }
    
    // Function to populate provinces and cities for billing
    function setupBillingDropdowns() {
        const billingRegion = document.getElementById('billing-region');
        const billingProvince = document.getElementById('billing-province');
        const billingCity = document.getElementById('billing-city');
        
        if (!billingRegion || !billingProvince || !billingCity) return;
        
        // Remove existing event listeners if they exist (by checking if already initialized)
        if (billingRegion.dataset.initialized === 'true') return;
        
        // Mark as initialized
        billingRegion.dataset.initialized = 'true';
        billingProvince.dataset.initialized = 'true';
        billingCity.dataset.initialized = 'true';
        
        billingRegion.addEventListener('change', function() {
            const selectedRegion = this.value;
            billingProvince.innerHTML = '<option value="">Select Province</option>';
            billingCity.innerHTML = '<option value="">Select City/Municipality</option>';
            
            if (!selectedRegion) return;
            
            if (selectedRegion === "NCR") {
                billingProvince.innerHTML = '<option value="Metro Manila">Metro Manila</option>';
                billingProvince.value = "Metro Manila";
                // Trigger change to populate cities
                setTimeout(() => {
                    billingProvince.dispatchEvent(new Event('change'));
                }, 50);
            } else if (selectedRegion === "Region III") {
                // Only show Bulacan for Region III
                const option = document.createElement('option');
                option.value = 'Bulacan';
                option.textContent = 'Bulacan';
                billingProvince.appendChild(option);
            } else if (selectedRegion === "Region IV-A") {
                Object.keys(region4AProvinces).forEach(province => {
                    const option = document.createElement('option');
                    option.value = province;
                    option.textContent = province;
                    billingProvince.appendChild(option);
                });
            }
        });
        
        billingProvince.addEventListener('change', function() {
            const selectedProvince = this.value;
            const selectedRegion = billingRegion.value;
            billingCity.innerHTML = '<option value="">Select City/Municipality</option>';
            
            if (selectedProvince === "Metro Manila") {
                metroManilaCities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    billingCity.appendChild(option);
                });
            } else if (selectedRegion === "Region III" && region3Provinces[selectedProvince]) {
                region3Provinces[selectedProvince].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    billingCity.appendChild(option);
                });
            } else if (selectedRegion === "Region IV-A" && region4AProvinces[selectedProvince]) {
                region4AProvinces[selectedProvince].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    billingCity.appendChild(option);
                });
            }
        });
    }
    
    // Initialize dropdown handlers - wait for DOM to be ready
    setTimeout(function() {
        // Initialize shipping dropdowns if form is visible
        const shippingAddressFieldsInit = document.getElementById('shipping-address-fields');
        if (shippingAddressFieldsInit) {
            if (shippingAddressFieldsInit.style.display !== 'none') {
                setupShippingDropdowns();
                // If region has a value on page load, trigger change to populate provinces
                const regionSelect = document.getElementById('shipping-region');
                const provinceSelect = document.getElementById('shipping-province');
                if (regionSelect && regionSelect.value) {
                    regionSelect.dispatchEvent(new Event('change'));
                    setTimeout(() => {
                        if (provinceSelect && provinceSelect.value) {
                            provinceSelect.dispatchEvent(new Event('change'));
                        }
                    }, 100);
                }
            } else {
                // Form is hidden, but initialize dropdowns anyway so they're ready when shown
                setupShippingDropdowns();
            }
        }
        
        // Initialize billing dropdowns
        setupBillingDropdowns();
        
        // If billing address fields are visible and have values, trigger changes
        const billingAddressFields = document.getElementById('billing-address-fields');
        const billingRegionSelect = document.getElementById('billing-region');
        if (billingRegionSelect && billingRegionSelect.value && billingAddressFields && billingAddressFields.style.display !== 'none') {
            billingRegionSelect.dispatchEvent(new Event('change'));
            setTimeout(() => {
                const billingProvinceSelect = document.getElementById('billing-province');
                if (billingProvinceSelect && billingProvinceSelect.value) {
                    billingProvinceSelect.dispatchEvent(new Event('change'));
                }
            }, 100);
        }
    }, 100);
    
    // === Billing Phone Number Validation ===
    const billingPhoneInput = document.querySelector("input[name='billing_phone']");
    if (billingPhoneInput) {
        billingPhoneInput.addEventListener("input", () => {
            billingPhoneInput.value = billingPhoneInput.value.replace(/\D/g, "");
            if (billingPhoneInput.value.length > 11) {
                billingPhoneInput.value = billingPhoneInput.value.slice(0, 11);
            }
        });
    }

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
    ['card-radio', 'gcash-radio', 'maya-radio'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', function() {
            const errorDiv = document.getElementById('payment-method-error');
            if (errorDiv) errorDiv.style.display = 'none';
        });
    });

    document.getElementById('accept-terms')?.addEventListener('change', function() {
        const errorDiv = document.getElementById('terms-error');
        if (errorDiv) errorDiv.style.display = 'none';
    });

    // Preferred Ocular Visit Date removed from payment page - only for booking page

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
        const barangay = form.querySelector("input[name='barangay']")?.value || '';
        const city = form.querySelector("input[name='city']")?.value || '';
        const province = form.querySelector("input[name='province']")?.value || '';
        const region = form.querySelector("input[name='region']")?.value || '';
        const zipcode = form.querySelector("input[name='zipcode']")?.value || '';
        const country = form.querySelector("input[name='country']")?.value || 'Philippines';
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
        const card = document.getElementById("card-radio")?.checked || false;
        const gcash = document.getElementById("gcash-radio")?.checked || false;
        const maya = document.getElementById("maya-radio")?.checked || false;
        const paymentBadge = document.getElementById('confirm-payment-method');
        
        if (card) {
            paymentBadge.innerHTML = '<span class="payment-icon">💳</span><span class="payment-text">Credit / Debit Card</span>';
            paymentBadge.className = 'payment-badge card';
        } else if (gcash) {
            paymentBadge.innerHTML = '<span class="payment-icon">💰</span><span class="payment-text">GCash</span>';
            paymentBadge.className = 'payment-badge ewallet';
        } else if (maya) {
            paymentBadge.innerHTML = '<span class="payment-icon">💰</span><span class="payment-text">Maya</span>';
            paymentBadge.className = 'payment-badge ewallet';
        }

        // Preferred Ocular Visit Date removed - only for booking page
        const installationDateSection = document.getElementById('confirm-installation-date-section');
        if (installationDateSection) {
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
                    const customizationString = item.customization || 'Standard';
                    const productImage = item.image || BASE_URL + 'assets/images/default-product.png';

                    // Format customization - show 2D preview if available, otherwise show tags
                    let customHtml = '';

                    if (item.has_design && item.design_ref) {
                        // Show 2D preview
                        customHtml = `
                            <div class="custom-layout" style="display: flex; align-items: center; gap: 8px;">
                                <div class="design-thumbnail-wrapper" style="flex-shrink: 0;">
                                    <img src="${item.design_ref}"
                                         alt="Custom Design"
                                         class="design-thumbnail"
                                         style="width: 50px; height: 50px; object-fit: contain; border: 2px solid #0d3d4d; border-radius: 4px; cursor: pointer; transition: all 0.2s ease; background: #f8f8f8; padding: 2px;"
                                         onclick="showDesignModal('${item.design_ref}')"
                                         onerror="this.style.display='none'; this.parentElement.querySelector('.view-design-text').textContent='Image not found';">
                                    <span class="view-design-text" style="display: block; font-size: 8px; color: #0d3d4d; margin-top: 2px; font-weight: 500; text-align: center;">Click to view</span>
                                </div>
                                <div class="custom-details" style="display: flex; flex-wrap: wrap; gap: 4px; flex: 1;">
                        `;

                        if (customizationString !== 'Standard') {
                            const parts = customizationString.split(' | ');
                            parts.forEach(part => {
                                customHtml += `<span class="custom-tag" style="display: inline-block; background: #e8f4f8; color: #0c2c3a; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-family: 'DM Sans', sans-serif; border: 1px solid #b8d4e3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${part}</span>`;
                            });
                        }

                        customHtml += `
                                </div>
                            </div>
                        `;
                    } else {
                        // Show tags only
                        customHtml = `
                            <div class="confirm-tags-box" style="display: flex; flex-wrap: wrap; gap: 8px;">
                        `;

                        if (customizationString !== 'Standard') {
                            const parts = customizationString.split(' | ');
                            parts.forEach(part => {
                                customHtml += `<span class="confirm-custom-tag" style="display: inline-block; background: #e3f2fd; color: #0f2b46; padding: 4px 12px; border-radius: 6px; font-size: 12px; border: 1px solid #bbdefb; font-weight: 500;">${part}</span>`;
                            });
                        } else {
                            customHtml += '<span style="color: #888; font-size: 12px;">Standard</span>';
                        }

                        customHtml += `</div>`;
                    }

                    const itemTotal = Number(item.total) || 0;
                    
                    // Placeholder SVG for missing images
                    const placeholderSvg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                    
                    row.innerHTML = `
                        <td class="product-cell">
                            <div class="product-info">
                                <img src="${productImage}" alt="${item.description}" class="product-thumb" onerror="this.onerror=null; this.src='${placeholderSvg}';">
                                <span class="product-name">${item.description}</span>
                            </div>
                        </td>
                        <td class="customization-cell">${customHtml}</td>
                        <td class="qty-cell">${item.quantity}</td>
                        <td class="price-cell">₱${itemTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    `;
                    itemsBody.appendChild(row);
                });

                // Update totals from server response
                const summary = res.summary;
                document.getElementById('confirm-subtotal').textContent = '₱' + (summary.subtotal || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('confirm-shipping').textContent = '₱' + (summary.shipping || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('confirm-handling').textContent = '₱' + (summary.handling || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('confirm-total').textContent = '₱' + (summary.total || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            } else {
                // Fallback: Get totals from page summary (already includes peso sign)
                const subtotal = document.getElementById('summary-subtotal').textContent;
                const shipping = document.getElementById('summary-shipping').textContent;
                const handling = document.getElementById('summary-handling').textContent;
                const total = document.getElementById('summary-total').textContent;
                const itemCount = document.querySelectorAll('.summary-item-row').length;

                document.getElementById('confirm-subtotal').textContent = subtotal;
                document.getElementById('confirm-shipping').textContent = shipping;
                document.getElementById('confirm-handling').textContent = handling;
                document.getElementById('confirm-total').textContent = total;
                
                itemsBody.innerHTML = `<tr><td colspan="4" class="no-items">${itemCount} item(s) in your cart</td></tr>`;
            }
        }).fail(function() {
            // Fallback on AJAX failure (values already include peso sign)
            const subtotal = document.getElementById('summary-subtotal').textContent;
            const shipping = document.getElementById('summary-shipping').textContent;
            const handling = document.getElementById('summary-handling').textContent;
            const total = document.getElementById('summary-total').textContent;
            const itemCount = document.querySelectorAll('.summary-item-row').length;

            document.getElementById('confirm-subtotal').textContent = subtotal;
            document.getElementById('confirm-shipping').textContent = shipping;
            document.getElementById('confirm-handling').textContent = handling;
            document.getElementById('confirm-total').textContent = total;
            
            itemsBody.innerHTML = `<tr><td colspan="4" class="no-items">${itemCount} item(s) in your cart</td></tr>`;
        });
    }

    // === Custom Calendar Logic ===
    let calCurrentDate = new Date();
    let selectedDate = null;
    let bookedDates = [];

    window.openCalendarModal = function() {
        document.getElementById('calendarModal').classList.add('show');
        renderCheckoutCalendar();
    };

    window.closeCalendarModal = function() {
        document.getElementById('calendarModal').classList.remove('show');
    };

    function renderCheckoutCalendar() {
        const year = calCurrentDate.getFullYear();
        const month = calCurrentDate.getMonth();
        
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        
        const monthYearEl = document.getElementById('cal-month-year');
        if (monthYearEl) monthYearEl.textContent = `${monthNames[month]} ${year}`;
        
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const minSelectableDate = new Date(today);
        minSelectableDate.setDate(today.getDate() + 4);
        
        const calBody = document.getElementById('cal-body');
        if (!calBody) return;
        calBody.innerHTML = '';
        
        let date = 1;
        for (let i = 0; i < 6; i++) {
            const row = document.createElement('tr');
            
            for (let j = 0; j < 7; j++) {
                const cell = document.createElement('td');
                cell.style.padding = '8px 0';
                cell.style.position = 'relative';
                
                if (i === 0 && j < firstDay) {
                    // Empty cell
                } else if (date > daysInMonth) {
                    // Empty cell
                } else {
                    const cellDate = new Date(year, month, date);
                    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                    
                    const dayNum = document.createElement('div');
                    dayNum.textContent = date;
                    dayNum.style.cssText = 'width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 50%; cursor: pointer; transition: all 0.2s; font-size: 0.9rem;';
                    
                    // Highlight Today
                    if (cellDate.getTime() === today.getTime()) {
                        dayNum.style.backgroundColor = '#d9534f';
                        dayNum.style.color = 'white';
                        dayNum.style.fontWeight = 'bold';
                        dayNum.title = 'Today';
                    }
                    
                    // Check if selectable
                    const isPast = cellDate < today;
                    const isTooSoon = cellDate < minSelectableDate;
                    const isBooked = bookedDates.includes(dateStr);
                    
                    if (isPast || isTooSoon || isBooked) {
                        dayNum.style.color = '#ccc';
                        dayNum.style.cursor = 'not-allowed';
                        if (isBooked) {
                            dayNum.style.backgroundColor = '#f5f5f5';
                            dayNum.title = 'Fully Booked';
                        } else if (isTooSoon && !isPast) {
                            dayNum.title = 'Select a date at least 4 days from today';
                        }
                    } else {
                        // Selectable
                        dayNum.addEventListener('mouseover', function() {
                            if (selectedDate !== dateStr) this.style.backgroundColor = '#eaf3f7';
                        });
                        dayNum.addEventListener('mouseout', function() {
                            if (selectedDate !== dateStr) this.style.backgroundColor = 'transparent';
                        });
                        dayNum.addEventListener('click', function() {
                            selectDate(dateStr);
                        });
                        
                        // Highlight Selected
                        if (selectedDate === dateStr) {
                            dayNum.style.backgroundColor = '#0f2b46';
                            dayNum.style.color = 'white';
                            dayNum.style.fontWeight = 'bold';
                        }
                    }
                    
                    cell.appendChild(dayNum);
                    date++;
                }
                row.appendChild(cell);
            }
            calBody.appendChild(row);
            if (date > daysInMonth) break;
        }
    }

    function selectDate(dateStr) {
        selectedDate = dateStr;
        document.getElementById('preferred_installation_date').value = dateStr;
        
        const formattedDate = new Date(dateStr).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        document.getElementById('selected-date-text').textContent = formattedDate;
        document.getElementById('open-calendar-btn').style.borderColor = '#0f2b46';
        document.getElementById('open-calendar-btn').style.background = '#f0f7ff';
        
        renderCheckoutCalendar();
        document.getElementById('confirm-date-btn').disabled = false;
        clearFieldError('preferred_installation_date', 'installation-date-error');
    }

    document.getElementById('confirm-date-btn')?.addEventListener('click', () => {
        closeCalendarModal();
        validatePlaceOrderButton();
    });

    document.getElementById('open-calendar-btn')?.addEventListener('click', openCalendarModal);

    document.getElementById('cal-prev-month')?.addEventListener('click', (e) => {
        e.stopPropagation();
        calCurrentDate.setMonth(calCurrentDate.getMonth() - 1);
        renderCheckoutCalendar();
    });

    document.getElementById('cal-next-month')?.addEventListener('click', (e) => {
        e.stopPropagation();
        calCurrentDate.setMonth(calCurrentDate.getMonth() + 1);
        renderCheckoutCalendar();
    });

    // Fetch booked dates and initial render
    $.getJSON(BASE_URL + "get_booked_dates", function(res) {
        if (res.status === 'success') {
            bookedDates = res.booked_dates || [];
        }
        renderCheckoutCalendar();
    });

    const pOrderBtn = document.getElementById('placeOrderBtn');
    const sBillCheckbox = document.getElementById('same-billing');
    const bFormContainer = document.getElementById('billingForm');
    const bReqInputs = bFormContainer ? bFormContainer.querySelectorAll('input[required], select[required]') : [];

    function validatePlaceOrderButton() {
        if (!pOrderBtn) return;
        
        let isValid = true;
        
        // If "Same as shipping" is NOT checked, validate billing fields
        if (sBillCheckbox && !sBillCheckbox.checked) {
            bReqInputs.forEach(input => {
                if (!input.value.trim()) isValid = false;
            });
        }
        
        // Ensure a payment method is selected
        const card = document.getElementById("card-radio")?.checked || false;
        const gcash = document.getElementById("gcash-radio")?.checked || false;
        const maya = document.getElementById("maya-radio")?.checked || false;
        if (!card && !gcash && !maya) isValid = false;

        // Ensure terms are accepted
        const terms = document.getElementById('accept-terms')?.checked;
        if (!terms) isValid = false;

        // Button is always clickable as per request, but we track validity
        pOrderBtn.style.opacity = '1';
        pOrderBtn.style.cursor = 'pointer';
        return isValid;
    }

    // Attach listeners
    if (sBillCheckbox) sBillCheckbox.addEventListener('change', validatePlaceOrderButton);
    
    bReqInputs.forEach(input => {
        input.addEventListener('input', validatePlaceOrderButton);
        input.addEventListener('change', validatePlaceOrderButton);
    });

    ['card-radio', 'gcash-radio', 'maya-radio', 'accept-terms'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', validatePlaceOrderButton);
        document.getElementById(id)?.addEventListener('input', validatePlaceOrderButton);
    });

    // Initialize button state
    validatePlaceOrderButton();

    // === Place Order button - Show confirmation modal ===
    document.getElementById("placeOrderBtn").addEventListener("click", function () {
        const card = document.getElementById("card-radio")?.checked || false;
        const gcash = document.getElementById("gcash-radio")?.checked || false;
        const maya = document.getElementById("maya-radio")?.checked || false;
        const selectedPaymentMethod = document.querySelector('input[name="payment-method"]:checked')?.value || '';
        
        console.log('Place Order clicked - Card:', card, 'GCash:', gcash, 'Maya:', maya, 'Selected:', selectedPaymentMethod);
        const termsCheckbox = document.getElementById('accept-terms');
        const termsAccepted = termsCheckbox ? termsCheckbox.checked : false;
        let firstErrorElement = null;
        let errorMessage = "Please complete all required fields.";

        // Reset errors and warnings
        document.querySelectorAll('.form-group input, .form-group select').forEach(el => el.style.borderColor = '#ccc');
        document.querySelectorAll('.inline-error').forEach(el => el.style.display = 'none');
        
        // Hide validation notice initially
        let validationNotice = document.getElementById('payment-validation-notice');
        if (validationNotice) {
            validationNotice.style.display = 'none';
        }

        // Validate shipping info
        const shippingInputs = document.querySelectorAll('#profileForm input[required], #profileForm select[required]');
        for (const input of shippingInputs) {
            // Check if field is visible or not part of hidden address fields
            const isHiddenAddressField = input.closest('#shipping-address-fields')?.style.display === 'none';
            if (isHiddenAddressField) continue;

            if (!input.value.trim()) {
                input.style.borderColor = 'red';
                if (!firstErrorElement) {
                    firstErrorElement = input;
                    const label = input.closest('.form-group')?.querySelector('label')?.textContent.replace('*', '').trim() || "required field";
                    errorMessage = `Please complete the ${label} field.`;
                }
                break;
            }
        }

        // Validate billing info if not same
        if (!firstErrorElement && sBillCheckbox && !sBillCheckbox.checked) {
            for (const input of bReqInputs) {
                // Check if billing fields container is visible
                const isHiddenBilling = document.getElementById('billing-address-fields')?.style.display === 'none';
                if (isHiddenBilling) continue;

                if (!input.value.trim()) {
                    input.style.borderColor = 'red';
                    if (!firstErrorElement) {
                        firstErrorElement = input;
                        const label = input.closest('.form-group')?.querySelector('label')?.textContent.replace('*', '').trim() || "required billing field";
                        errorMessage = `Please complete the billing ${label} field.`;
                    }
                    break;
                }
            }
        }

        // Validate payment method
        if (!firstErrorElement && !card && !gcash && !maya) {
            console.log('Payment method validation failed - Card:', card, 'GCash:', gcash, 'Maya:', maya);
            const errorDiv = document.getElementById('payment-method-error');
            if (errorDiv) errorDiv.style.display = 'block';
            if (!firstErrorElement) {
                firstErrorElement = document.querySelector('.payment-section');
                errorMessage = "Please select a payment method.";
            }
        }

        // Validate terms acceptance
        if (!firstErrorElement && !termsAccepted) {
            const errorDiv = document.getElementById('terms-error');
            if (errorDiv) errorDiv.style.display = 'block';
            if (!firstErrorElement) {
                firstErrorElement = document.querySelector('.terms');
                errorMessage = "Please accept the Terms and Conditions.";
            }
        }

        if (firstErrorElement) {
            // Get all missing fields (collect all at once to avoid duplicates)
            const missingFields = new Set();
            
            // Check shipping fields
            const shippingInputs = document.querySelectorAll('#profileForm input[required], #profileForm select[required]');
            shippingInputs.forEach(input => {
                const isHiddenAddressField = input.closest('#shipping-address-fields')?.style.display === 'none';
                if (isHiddenAddressField) return;
                
                if (!input.value.trim()) {
                    const label = input.closest('.form-group')?.querySelector('label')?.textContent.replace(/\*/g, '').trim() || 'field';
                    if (label) {
                        missingFields.add(label);
                    }
                }
            });
            
            // Check billing fields if not same as shipping
            if (sBillCheckbox && !sBillCheckbox.checked) {
                let hasMissingBilling = false;
                bReqInputs.forEach(input => {
                    const isHiddenBilling = document.getElementById('billing-address-fields')?.style.display === 'none';
                    if (isHiddenBilling) return;
                    
                    if (!input.value.trim()) {
                        hasMissingBilling = true;
                    }
                });
                // Add "Billing Address" as a single field instead of individual field names
                if (hasMissingBilling) {
                    missingFields.add('Billing Address');
                }
            }
            
            // Check payment method
            if (!card && !gcash && !maya) {
                missingFields.add('Payment Method');
            }
            
            // Check terms
            if (!termsAccepted) {
                missingFields.add('Terms and Conditions acceptance');
            }
            
            // Show validation notice
            if (!validationNotice) {
                // Create validation notice element if it doesn't exist
                validationNotice = document.createElement('div');
                validationNotice.id = 'payment-validation-notice';
                validationNotice.style.cssText = 'margin-top: 15px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; color: #856404;';
                const buttonParent = document.getElementById('placeOrderBtn').parentElement;
                if (buttonParent) {
                    buttonParent.insertBefore(validationNotice, document.getElementById('placeOrderBtn'));
                }
            }
            
            // Update notice with unique fields
            const missingFieldsArray = Array.from(missingFields);
            if (missingFieldsArray.length > 0) {
                validationNotice.innerHTML = '<strong>⚠ Please complete the following required fields before placing order:</strong><ul style="margin: 10px 0 0 20px; padding-left: 20px;">' + 
                                          missingFieldsArray.map(field => `<li>${field}</li>`).join('') + '</ul>';
                validationNotice.style.display = 'block';
            } else {
                validationNotice.style.display = 'none';
            }
            
            showToast(errorMessage, 'warning');
            firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        
        // Hide validation notice if all fields are valid
        if (validationNotice) {
            validationNotice.style.display = 'none';
        }

        // Populate and show confirmation modal
        populateConfirmModal();
        openConfirmModal();
    });

    // === Confirm Order button - Actually place the order ===
    confirmOrderBtn.addEventListener("click", function () {
        const btn = this;
        const defaultConfirmLabel = 'Confirm & Place Order';
        // Get selected payment method
        const card = document.getElementById("card-radio")?.checked || false;
        const gcash = document.getElementById("gcash-radio")?.checked || false;
        const maya = document.getElementById("maya-radio")?.checked || false;
        const selectedPaymentMethod = document.querySelector('input[name="payment-method"]:checked')?.value || '';
        
        // Debug: Log payment method state
        console.log('Payment method validation - Card:', card, 'GCash:', gcash, 'Maya:', maya, 'Selected:', selectedPaymentMethod);
        const termsCheckbox = document.getElementById('accept-terms');
        const termsAccepted = termsCheckbox ? termsCheckbox.checked : false;

        // Get form data
        const form = document.getElementById('profileForm');
        const formData = new FormData(form);

        // If using a saved address, overwrite address fields with saved data
        const savedAddressDropdown = document.getElementById('saved-address-dropdown');
        const useDifferentAddress = document.getElementById('use-different-shipping-address')?.checked || false;
        
        if (savedAddressDropdown && savedAddressDropdown.value && !useDifferentAddress) {
            const selectedOption = savedAddressDropdown.options[savedAddressDropdown.selectedIndex];
            const addressData = JSON.parse(selectedOption.getAttribute('data-address') || '{}');
            
            formData.set('unit_house_number', addressData.UnitHouseNumber || '');
            formData.set('street', addressData.Street || '');
            formData.set('subdivision', addressData.Subdivision || '');
            formData.set('barangay', addressData.Barangay || '');
            formData.set('city', addressData.City || '');
            formData.set('province', addressData.Province || '');
            formData.set('region', addressData.Region || '');
            formData.set('zipcode', addressData.ZipCode || '');
            formData.set('country', addressData.Country || 'Philippines');
            
            // Rebuild address/AddressLine for backward compatibility
            const addressParts = [
                addressData.UnitHouseNumber,
                addressData.Street,
                addressData.Subdivision
            ].filter(Boolean);
            if (addressParts.length > 0) {
                formData.set('address', addressParts.join(', '));
                formData.set('AddressLine', addressParts.join(', '));
            }
        } else {
            // Build AddressLine from components for backward compatibility
            const unitHouse = form.querySelector("input[name='unit_house_number']")?.value || '';
            const street = form.querySelector("input[name='street']")?.value || '';
            const subdivision = form.querySelector("input[name='subdivision']")?.value || '';
            const addressParts = [unitHouse, street, subdivision].filter(Boolean);
            if (addressParts.length > 0) {
                formData.set('address', addressParts.join(', '));
                formData.set('AddressLine', addressParts.join(', '));
            }
        }

        // Add payment method to formData
        if (selectedPaymentMethod) {
            formData.append('payment_method', selectedPaymentMethod);
        }
        
        // Add terms acceptance
        formData.append('terms_accepted', termsAccepted ? 'true' : 'false');
        
        // Add selected cart IDs
        formData.append('selected_cart_ids', SELECTED_CART_IDS);
        
        // Disable button and show loading state
        btn.disabled = true;
        btn.textContent = 'Processing...';

        // Store order summary in session before sending request
        // This ensures ewallet page has access to the summary
        const summaryItemsEl = document.getElementById('summary-items');
        const summarySubtotalEl = document.getElementById('summary-subtotal');
        const summaryShippingEl = document.getElementById('summary-shipping');
        const summaryHandlingEl = document.getElementById('summary-handling');
        const summaryTotalEl = document.getElementById('summary-total');

        const summaryItems = summaryItemsEl ? summaryItemsEl.textContent : '0';
        const summarySubtotal = summarySubtotalEl ? summarySubtotalEl.textContent : '₱0.00';
        const summaryShipping = summaryShippingEl ? summaryShippingEl.textContent : '₱0.00';
        const summaryHandling = summaryHandlingEl ? summaryHandlingEl.textContent : '₱0.00';
        const summaryTotal = summaryTotalEl ? summaryTotalEl.textContent : '₱0.00';
        
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
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 20000);

        fetch(BASE_URL + 'shopcon/place_order', {
            method: 'POST',
            body: formData,
            signal: controller.signal
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
            clearTimeout(timeoutId);
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
                // Check if we need to create payment intent (PayMongo flow)
                if (data.next_step === 'create_payment_intent') {
                    // Start PayMongo payment flow
                    console.log('Starting PayMongo payment flow...');
                    initiatePayMongoPayment(data.order_id, data.payment_method, data.total_amount, btn, defaultConfirmLabel);
                } else {
                    // Old flow - redirect immediately
                    console.log('Redirecting to:', data.redirect_url);
                    window.location.href = data.redirect_url;
                }
            } else {
                // Show error message with debug info
                let errorMsg = data.message || 'An error occurred. Please try again.';
                showToast(errorMsg, 'error', 5000);
                if (data.debug) {
                    console.error('Debug Info:', data.debug);
                    showToast('Check browser console (F12) for debug details.', 'info', 3000);
                }
                btn.disabled = false;
                btn.textContent = defaultConfirmLabel;
                closeConfirmModal();
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            if (error.name === 'AbortError') {
                showToast('Request timed out. Please try again.', 'error', 5000);
            } else {
                console.error('Error:', error);
                showToast('An error occurred. Please try again. Check console for details.', 'error', 5000);
            }
            btn.disabled = false;
            btn.textContent = defaultConfirmLabel;
            closeConfirmModal();
        });
    });

    /**
     * PayMongo Payment Flow
     * Handles the complete PayMongo payment process
     */
    async function initiatePayMongoPayment(orderId, paymentMethod, totalAmount, btn, defaultConfirmLabel) {
        try {
            btn.disabled = true;
            btn.textContent = 'Initializing payment...';
            closeConfirmModal();
            
            // STEP 1: Create Payment Intent (Backend)
            console.log('STEP 1: Creating payment intent...');
            const createIntentResponse = await fetch(BASE_URL + 'payment/create-payment-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    order_id: orderId,
                    payment_method: paymentMethod
                })
            });
            
            const intentData = await createIntentResponse.json();
            
            if (!intentData.status || intentData.status !== 'success') {
                throw new Error(intentData.message || 'Failed to initialize payment');
            }
            
            const { payment_intent_id, client_key, public_key } = intentData;
            console.log('Payment Intent Created:', payment_intent_id);
            
            // STEP 2 & 3: Create Payment Method (Frontend using PayMongo REST API)
            let paymentMethodId;
            
            if (paymentMethod === 'card') {
                // Card payment - collect card details
                console.log('STEP 2: Collecting card details...');
                
                const cardDetails = await collectCardDetails();
                
                if (!cardDetails) {
                    throw new Error('Card details collection cancelled');
                }
                
                // Create card payment method using PayMongo REST API (frontend)
                btn.textContent = 'Processing card payment...';
                
                const paymentMethodResponse = await fetch('https://api.paymongo.com/v1/payment_methods', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Basic ' + btoa(public_key + ':')
                    },
                    body: JSON.stringify({
                        data: {
                            attributes: {
                                type: 'card',
                                details: {
                                    card_number: cardDetails.cardNumber,
                                    exp_month: parseInt(cardDetails.expMonth),
                                    exp_year: parseInt(cardDetails.expYear),
                                    cvc: cardDetails.cvc
                                },
                                billing: {
                                    name: cardDetails.customerName || 'Customer',
                                    email: cardDetails.email || '',
                                    phone: cardDetails.phone || ''
                                }
                            }
                        }
                    })
                });
                
                const paymentMethodData = await paymentMethodResponse.json();
                
                if (paymentMethodResponse.ok && paymentMethodData.data && paymentMethodData.data.id) {
                    paymentMethodId = paymentMethodData.data.id;
                } else {
                    const errorMsg = paymentMethodData.errors?.[0]?.detail || 'Failed to create payment method';
                    throw new Error(errorMsg);
                }
                console.log('Payment Method Created (Card):', paymentMethodId);
                
            } else if (paymentMethod === 'gcash' || paymentMethod === 'maya' || paymentMethod === 'ewallet') {
                // E-Wallet payment
                console.log('STEP 2: Creating e-wallet payment method...');
                
                const ewalletType = paymentMethod === 'maya' ? 'paymaya' : 'gcash';
                btn.textContent = 'Processing e-wallet payment...';
                
                // Create e-wallet payment method using PayMongo REST API (frontend)
                const paymentMethodResponse = await fetch('https://api.paymongo.com/v1/payment_methods', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Basic ' + btoa(public_key + ':')
                    },
                    body: JSON.stringify({
                        data: {
                            attributes: {
                                type: ewalletType
                            }
                        }
                    })
                });
                
                const paymentMethodData = await paymentMethodResponse.json();
                
                if (paymentMethodResponse.ok && paymentMethodData.data && paymentMethodData.data.id) {
                    paymentMethodId = paymentMethodData.data.id;
                } else {
                    const errorMsg = paymentMethodData.errors?.[0]?.detail || 'Failed to create payment method';
                    throw new Error(errorMsg);
                }
                console.log('Payment Method Created (E-Wallet):', paymentMethodId);
            } else {
                throw new Error('Invalid payment method');
            }
            
            // STEP 4: Attach Payment Method to Payment Intent (Backend)
            console.log('STEP 3: Attaching payment method...');
            const attachResponse = await fetch(BASE_URL + 'payment/attach-payment-method', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    payment_intent_id: payment_intent_id,
                    payment_method_id: paymentMethodId,
                    order_id: orderId
                })
            });
            
            const attachData = await attachResponse.json();
            
            if (!attachData.status || attachData.status !== 'success') {
                throw new Error(attachData.message || 'Failed to process payment');
            }
            
            // STEP 5: Handle Response
            if (attachData.payment_status === 'succeeded') {
                // Card payment succeeded immediately
                console.log('Payment succeeded!');
                showToast('Payment successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = attachData.redirect_url || (BASE_URL + 'payment/complete?order_id=' + orderId);
                }, 1500);
            } else if (attachData.payment_status === 'awaiting_next_action') {
                // E-Wallet - redirect to PayMongo
                console.log('Redirecting to PayMongo for e-wallet payment...');
                
                // Show instruction message before redirect
                showToast('Redirecting to PayMongo test payment page. Click "Authorize Test Payment" to complete.', 'info', 4000);
                
                // Small delay to show message before redirect
                setTimeout(() => {
                    window.location.href = attachData.redirect_url;
                }, 500);
            } else {
                throw new Error('Payment processing failed. Please try again.');
            }
            
        } catch (error) {
            console.error('PayMongo Payment Error:', error);
            showToast(error.message || 'Payment processing failed. Please try again.', 'error', 5000);
            btn.disabled = false;
            btn.textContent = defaultConfirmLabel;
        }
    }
    
    /**
     * Detect card brand from card number
     */
    function detectCardBrand(cardNumber) {
        const cleaned = cardNumber.replace(/\s/g, '');
        
        // Visa: starts with 4
        if (/^4/.test(cleaned)) return 'Visa';
        
        // Mastercard: starts with 5
        if (/^5[1-5]/.test(cleaned)) return 'Mastercard';
        
        // American Express: starts with 34 or 37
        if (/^3[47]/.test(cleaned)) return 'American Express';
        
        // Discover: starts with 6
        if (/^6/.test(cleaned)) return 'Discover';
        
        // JCB: starts with 35
        if (/^35/.test(cleaned)) return 'JCB';
        
        return 'Unknown';
    }

    /**
     * Collect Card Details
     * Shows a modal to collect card information
     */
    function collectCardDetails() {
        return new Promise((resolve) => {
            // Create modal for card details
            const modal = document.createElement('div');
            modal.id = 'card-details-modal';
            modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;';
            
            modal.innerHTML = `
                <div style="background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%;">
                    <h3 style="margin: 0 0 20px 0;">Enter Card Details</h3>
                    <form id="card-details-form">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Card Number</label>
                            <input type="text" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" required 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(.{4})/g, '$1 ').trim()">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Brand</label>
                            <input type="text" id="card-brand" readonly 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background-color: #f5f5f5; cursor: not-allowed; color: #666;">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Expiration Date</label>
                                <input type="text" id="exp-date" placeholder="MM/YY" maxlength="5" required 
                                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^(\\d{2})(\\d)/, '$1/$2').substring(0, 5)">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: 600;">CVC</label>
                                <input type="text" id="cvc" placeholder="123" maxlength="4" required 
                                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" id="cancel-card" style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Cancel</button>
                            <button type="submit" style="padding: 10px 20px; background: #02455F; color: white; border: none; border-radius: 4px; cursor: pointer;">Submit</button>
                        </div>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            const form = modal.querySelector('#card-details-form');
            const cancelBtn = modal.querySelector('#cancel-card');
            const cardNumberInput = document.getElementById('card-number');
            const cardBrandInput = document.getElementById('card-brand');
            
            // Detect card brand as user types
            cardNumberInput.addEventListener('input', function() {
                const cardNumber = this.value.replace(/\s/g, '');
                if (cardNumber.length >= 4) {
                    const brand = detectCardBrand(cardNumber);
                    if (brand !== 'Unknown') {
                        cardBrandInput.value = brand;
                        cardBrandInput.style.color = '#02455F';
                    } else {
                        cardBrandInput.value = '';
                    }
                } else {
                    cardBrandInput.value = '';
                }
            });
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
                const expDate = document.getElementById('exp-date').value;
                const cvc = document.getElementById('cvc').value;
                
                // Parse expiration date (MM/YY)
                const expParts = expDate.split('/');
                if (expParts.length !== 2 || expParts[0].length !== 2 || expParts[1].length !== 2) {
                    alert('Please enter expiration date in MM/YY format');
                    return;
                }
                
                const expMonth = parseInt(expParts[0]);
                const expYear2Digit = parseInt(expParts[1]);
                
                // Validate month (1-12)
                if (expMonth < 1 || expMonth > 12) {
                    alert('Please enter a valid month (01-12)');
                    return;
                }
                
                // Convert 2-digit year to 4-digit year
                // Years 00-30 are 2000-2030, years 31-99 are 1931-1999
                const currentYear = new Date().getFullYear();
                const currentYear2Digit = currentYear % 100;
                let expYear = 2000 + expYear2Digit;
                
                // If the 2-digit year is less than current 2-digit year, assume next century
                // But if it's far in the past (like 99), assume previous century
                if (expYear2Digit < currentYear2Digit && expYear2Digit > 30) {
                    expYear = 1900 + expYear2Digit;
                }
                
                // Validate year (not in the past)
                if (expYear < currentYear) {
                    alert('Please enter a valid expiration year');
                    return;
                }
                
                // Also check if the card is expired (month and year in the past)
                const currentMonth = new Date().getMonth() + 1; // getMonth() returns 0-11
                if (expYear === currentYear && expMonth < currentMonth) {
                    alert('This card has expired');
                    return;
                }
                
                const cardDetails = {
                    cardNumber: cardNumber,
                    expMonth: expMonth.toString().padStart(2, '0'),
                    expYear: expYear.toString(), // Convert to 4-digit for PayMongo API
                    cvc: cvc,
                    brand: detectCardBrand(cardNumber)
                };
                
                // Get customer name from main form
                const firstNameInput = document.querySelector('input[name="firstname"]');
                const lastNameInput = document.querySelector('input[name="lastname"]');
                const customerName = (firstNameInput?.value || '') + ' ' + (lastNameInput?.value || '');
                cardDetails.customerName = customerName.trim() || 'Customer';
                
                // Get email/phone from main form if available
                const emailInput = document.querySelector('input[name="email"]');
                const phoneInput = document.querySelector('input[name="phone"]');
                if (emailInput) cardDetails.email = emailInput.value;
                if (phoneInput) cardDetails.phone = phoneInput.value;
                
                document.body.removeChild(modal);
                resolve(cardDetails);
            });
            
            cancelBtn.addEventListener('click', () => {
                document.body.removeChild(modal);
                resolve(null);
            });
        });
    }

}); // End of $(document).ready

</script>