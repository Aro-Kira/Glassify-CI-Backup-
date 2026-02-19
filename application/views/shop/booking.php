<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/checkout_style.css'); ?>">
<script>
    const BASE_URL = "<?= base_url(); ?>";
    const CUSTOMER_ROLE = "<?= isset($customer_role) ? $customer_role : '' ?>";
    const IS_BEGINNER = CUSTOMER_ROLE === 'beginner';
    
    // PHP-provided beginner booking data (most reliable)
    const PHP_BEGINNER_BOOKING = <?= isset($beginner_booking) && $beginner_booking ? 'true' : 'false' ?>;
    const PHP_BEGINNER_PRODUCT_ID = "<?= isset($beginner_product_id) ? $beginner_product_id : '' ?>";
    const PHP_BEGINNER_PRODUCT_NAME = "<?= isset($beginner_product_name) ? addslashes($beginner_product_name) : '' ?>";
    
    console.log('🔧 PHP Variables:');
    console.log('   PHP_BEGINNER_BOOKING:', PHP_BEGINNER_BOOKING);
    console.log('   PHP_BEGINNER_PRODUCT_ID:', PHP_BEGINNER_PRODUCT_ID);
    console.log('   PHP_BEGINNER_PRODUCT_NAME:', PHP_BEGINNER_PRODUCT_NAME);
    console.log('   CUSTOMER_ROLE:', CUSTOMER_ROLE);
    
    // Get selected cart IDs from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const SELECTED_CART_IDS = urlParams.get('selected') || '';
    
    // Beginner mode: Get product info - prefer PHP data, fallback to URL/localStorage
    let BEGINNER_PRODUCT_ID = PHP_BEGINNER_PRODUCT_ID || urlParams.get('product_id') || '';
    let BEGINNER_PRODUCT_NAME = PHP_BEGINNER_PRODUCT_NAME || urlParams.get('product_name') || '';
    const BEGINNER_SOURCE = urlParams.get('source') || '';
    
    console.log('🔍 After PHP fallback:');
    console.log('   BEGINNER_PRODUCT_ID:', BEGINNER_PRODUCT_ID);
    console.log('   BEGINNER_PRODUCT_NAME:', BEGINNER_PRODUCT_NAME);
    
    // Fallback: Check localStorage if still missing
    if (!BEGINNER_PRODUCT_ID) {
        try {
            const storedProduct = localStorage.getItem('bookingProduct');
            if (storedProduct) {
                const parsed = JSON.parse(storedProduct);
                if (parsed && parsed.id) {
                    BEGINNER_PRODUCT_ID = parsed.id;
                    BEGINNER_PRODUCT_NAME = parsed.name || '';
                    console.log('✅ Loaded product from localStorage:', parsed);
                }
            }
        } catch (e) {
            console.log('Error reading localStorage:', e);
        }
    }
    
    // Final beginner booking detection - simplified and more robust
    const IS_BEGINNER_BOOKING = PHP_BEGINNER_BOOKING || !!BEGINNER_PRODUCT_ID || (IS_BEGINNER && BEGINNER_SOURCE === 'beginner_booking');
    
    console.log('🎯 FINAL DETECTION:');
    console.log('   IS_BEGINNER_BOOKING:', IS_BEGINNER_BOOKING);
    console.log('   BEGINNER_PRODUCT_ID:', BEGINNER_PRODUCT_ID);
    console.log('   BEGINNER_PRODUCT_NAME:', BEGINNER_PRODUCT_NAME);
    console.log('   URL:', window.location.href);
    console.log('💾 localStorage beginner_selected_product:', localStorage.getItem('beginner_selected_product'));
    console.log('💾 localStorage beginner_booking_mode:', localStorage.getItem('beginner_booking_mode'));
    console.log('💾 localStorage bookingProduct:', localStorage.getItem('bookingProduct'));
    
    // Check if we're coming from 2D modeling page
    console.log('🔍 Document referrer:', document.referrer);
    console.log('=====================================');
    
    // Force detection if we have localStorage data but URL params are missing
    if (!IS_BEGINNER_BOOKING && localStorage.getItem('beginner_booking_mode') === 'true') {
        console.log('🚨 FORCING BEGINNER BOOKING MODE - found localStorage flag but not detected via other methods');
        window.FORCE_BEGINNER_BOOKING = true;
    }
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<div class="checkout-header">
    <!-- Back button -->
    <div class="back-btn">
        <a href="javascript:history.back()">
            <img src="<?php echo base_url('assets/images/img-page/back_button.png'); ?>" alt="Back Icon">
            <span>Back</span>
        </a>
    </div>

    <!-- Progress nav -->
    <div class="progress-nav" style="display:flex; justify-content:center; gap:12px; align-items:center;">
        <div class="step completed">Review</div>
        <div class="divider"></div>
        <div class="step active">Booking</div>
        <div class="divider"></div>
        <div class="step">Complete</div>
    </div>
</div>


<main>

    <!-- Title outside sections -->
    <div class="info-title">
        <h2>Booking Details</h2>
        <div class="title-divider"></div>
    </div>

    <!-- Content row -->
    <div class="info-container">
        <section class="info-section">
            <form id="profileForm" method="POST" action="<?= base_url('usercon/update_profile'); ?>">
                <!-- Site Address -->
                <div class="shipping-address-title">
                    <h3>Site Address</h3>
                </div>
                
                <!-- User Info -->
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstname" value="<?= htmlspecialchars($user->First_Name ?? '') ?>"
                            placeholder="Enter your first name" readonly style="background-color: #f5f5f5; cursor: not-allowed;" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middlename" value="<?= htmlspecialchars($user->Middle_Name ?? '') ?>"
                            readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lastname" value="<?= htmlspecialchars($user->Last_Name ?? '') ?>"
                            placeholder="Enter your last name" readonly style="background-color: #f5f5f5; cursor: not-allowed;" required>
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
                
                <!-- Site Address Form Fields (hidden by default if saved addresses exist) -->
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
                <!-- End Site Address Form Fields -->
                
                <!-- Special Instructions / Note (Always visible, not tied to saved address) -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Special Instructions / Note</label>
                        <textarea name="note" rows="3" placeholder="Add notes or special instructions (e.g., access details, preferences)"><?= htmlspecialchars($addresses['Shipping']->Note ?? '') ?></textarea>
                    </div>
                </div>
                
                <!-- Preferred Ocular Visit Date -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Preferred Ocular Visit Date <span style="color: red;">*</span></label>
                        <div class="calendar-trigger-wrapper">
                            <button type="button" id="open-calendar-btn" style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 12px 15px; border: 1px solid #ccc; border-radius: 6px; background: white; cursor: pointer; color: #0f2b46; font-weight: 500; transition: all 0.2s;">
                                <span id="selected-date-text">Select your preferred ocular visit date</span>
                                <i class="fas fa-calendar-alt" style="color: #0f2b46; font-size: 1.1rem;"></i>
                            </button>
                            <input type="hidden" name="preferred_installation_date" id="preferred_installation_date" required>
                            
                            <div id="installation-date-error" class="inline-error" style="display: none; margin-top: 5px; padding: 8px 12px; background: #fff3cd; border-left: 3px solid #dc3545; border-radius: 4px;">
                                <span style="color: #dc3545; font-size: 0.9em;">⚠ Please select a Preferred Ocular Visit Date. This field is required.</span>
                            </div>
                        </div>
                        <small style="color: #666; font-size: 0.9em; display: block; margin-top: 8px; line-height: 1.4;">
                            <i class="fas fa-info-circle" style="color: #0f2b46;"></i> 
                            Please select a date at least <b>4 days from today</b>.
                        </small>
                    </div>
                </div>
                
                <!-- Preferred Time for Ocular Visit -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Preferred Time <span style="color: red;">*</span></label>
                        <select name="preferred_time" id="preferred_time" style="width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 6px; background: white; color: #0f2b46; font-weight: 500;" required>
                            <option value="">Select preferred time</option>
                            <option value="08:00">8:00 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="17:00">5:00 PM</option>
                        </select>
                        <small style="color: #666; font-size: 0.9em; display: block; margin-top: 8px; line-height: 1.4;">
                            <i class="fas fa-clock" style="color: #0f2b46;"></i> 
                            Available hours: <b>8:00 AM to 5:00 PM</b>
                        </small>
                    </div>
                </div>

            </form>
        </section>


        <!-- Order Summary Section -->
        <section class="order-summary">
            <div class="order-summary-content">
                <h3>Product Details</h3>
                
                <!-- Itemized List -->
                <div id="summary-items-list" style="max-height: 350px; overflow-y: auto; margin-bottom: 4px; padding-bottom: 4px;">
                    <!-- Items will be dynamically populated -->
                    <?php if (isset($customer_role) && $customer_role === 'beginner'): ?>
                    <div style="text-align: center; color: #888; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #dee2e6;">
                        <i class="fas fa-spinner fa-spin" style="margin-bottom: 10px; font-size: 1.2rem; color: #007bff;"></i>
                        <div>Loading your selected product...</div>
                        <div style="font-size: 0.8rem; color: #666; margin-top: 5px;">Please wait while we fetch your product details</div>
                    </div>
                    <?php else: ?>
                    <div style="background: #ffffff; border-radius: 10px; padding: 12px; display:flex; gap:12px; align-items:center; border:1px solid #eef6fb; box-shadow: 0 2px 6px rgba(15,43,70,0.03);">
                        <img src="<?php echo base_url('uploads/products/0070987d24e7c87bd317ac520a9872f5.jpg'); ?>" alt="Glass Board" style="width:72px; height:72px; object-fit:cover; border-radius:6px; border:1px solid #e6eef7;">
                        <div style="flex:1;">
                            <div style="font-weight:700; color:#1b6fb3; font-size:1.05rem;">Glass Board</div>
                            <div style="color:#2e7d32; font-weight:600; margin-top:4px;">₱4,000 - ₱6,000</div>
                            <div style="color:#666; margin-top:6px;">Mirrors & Specialty Glass</div>
                            <div style="color:#888; font-style:italic; margin-top:4px;">Glass Board</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!isset($customer_role) || $customer_role !== 'beginner'): ?>
                <div class="summary-totals-box cart-only" style="padding-top: 4px;">
                    <p style="display:none;"><span>Items:</span> <span id="summary-items" style="font-weight: 600; color: #0f2b46;">0</span></p>
                    <p style="display:none;"><span>Price Range:</span> <span id="summary-price-range" style="font-weight: 600; color: #0f2b46;"></span></p>
                    <div id="estimated-price-note" style="margin-top:4px; padding:10px; background:#fff3cd; color:#856404; border-left:4px solid #ffc107; border-radius:6px; display:flex; gap:8px; align-items:flex-start;">
                        <div style="font-size:0.95em;">
                            <em><strong>Note:</strong> The price shown above is an estimate. The final quotation will be provided after the site assessment.</em>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="beginner-only" style="margin-top:1rem; padding:15px; background:#e8f5e8; color:#2e7d2e; border-left:4px solid #28a745; border-radius:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-calendar-check" style="margin-top: 2px; color: #28a745;"></i>
                        <div style="font-size:0.95em; line-height: 1.5;">
                            <strong>What to expect:</strong><br>
                            Our team will visit your location to assess your needs, take measurements, and provide personalized recommendations for your selected product. No payment is required at this time.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
                <div class="payment-section">
                <!-- Validation notice will be injected here when needed -->
                <div class="terms" style="margin:0 0 8px;">
                    <input type="checkbox" id="accept-terms">
                    <label for="accept-terms">
                        I have read and agree to Glassify's
                        <a href="<?php echo base_url('terms_order'); ?>">Terms and Conditions of Purchase</a>
                    </label>
                </div>
                <!-- Payment methods removed - Site Assessment Orders do not require payment at booking -->
                <!-- Static price range displayed in order summary above -->
                <?php if (isset($customer_role) && $customer_role === 'beginner'): ?>
                <button class="placeOrder-btn" id="confirmBookingBtn">Review Booking Details</button>
                <?php else: ?>
                <button class="placeOrder-btn" id="confirmBookingBtn">Review Booking Details</button>
                <?php endif; ?>
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
            <h2>📋 Review Booking Details</h2>
            <span class="modal-subtitle">Please review your booking details before confirming</span>
        </div>

                <div class="modal-body">
            <!-- Site Address Info -->
            <div class="confirm-section">
                <h4 class="confirm-section-title">
                    <span class="icon">📍</span> Site Address Details
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
                        <span class="info-label">Site Address</span>
                        <span class="info-value" id="confirm-address"></span>
                    </div>
                </div>
            </div>

                    <!-- Special Instructions / Note (show under Site Address Details) -->
                    <div class="confirm-section">
                        <h4 class="confirm-section-title">
                            <span class="icon">📝</span> Special Instructions / Note
                        </h4>
                        <div class="confirm-info-grid">
                            <div class="confirm-info-item full-width">
                                <span class="info-value" id="confirm-note"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Design Preview Modal -->
                    <div id="designPreviewModal" class="modal" style="z-index:12000;">
                        <div class="modal-overlay" onclick="closeDesignModal()"></div>
                        <div class="modal-content" style="position:relative; max-width: 900px; width: 90%; border-radius: 8px; overflow: hidden; box-shadow: 0 12px 30px rgba(0,0,0,0.3);">
                            <div class="design-modal-header" style="background: #0f2b46; color: #fff; padding: 12px 14px; display:flex; align-items:center; justify-content:space-between;">
                                <h3 style="margin:0; font-size:1rem; font-weight:600;">Design Preview</h3>
                                <button onclick="closeDesignModal()" aria-label="Close design preview" style="background: rgba(255,255,255,0.12); border: none; color: #fff; width: 36px; height: 36px; border-radius: 50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:18px; box-shadow: 0 6px 14px rgba(15,43,70,0.35);">×</button>
                            </div>
                            <div class="modal-body" style="background: #fff; padding: 12px; display:flex; justify-content:center; align-items:center;">
                                <img id="designPreviewImage" src="" alt="Design Preview" style="max-width: 100%; max-height: 75vh; border-radius:6px; box-shadow: 0 6px 18px rgba(0,0,0,0.15);">
                            </div>
                            <!-- Footer intentionally removed: header provides close control and no footer is required for design preview -->
                        </div>
                    </div>

            <!-- Payment Method removed - Site Assessment Orders do not require payment at booking -->
      
            <!-- Preferred Ocular Visit Date -->
            <div class="confirm-section" id="confirm-installation-date-section" style="display: none;">
                <h4 class="confirm-section-title">
                    <span class="icon">📅</span> Preferred Ocular Visit Date
                </h4>
                <div class="confirm-info-grid">
                    <div class="confirm-info-item">
                        <span class="info-label">Date</span>
                        <span class="info-value" id="confirm-installation-date"></span>
                    </div>
                    <div class="confirm-info-item">
                        <span class="info-label">Time</span>
                        <span class="info-value" id="confirm-installation-time"></span>
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
                                <?php if (isset($customer_role) && $customer_role === 'beginner'): ?>
                                <th style="text-align: left;">Price Range</th>
                                <?php else: ?>
                                <th>Customization</th>
                                <th>Qty</th>
                                <th style="text-align: left;">Price Range</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
            <tbody id="confirm-items-body">
              <!-- Items will be dynamically populated -->
            </tbody>
          </table>
        </div>
      </div>

            <!-- Order Total Summary removed for professional bookings (Price Range not shown here) -->
    </div>

        <div class="modal-footer confirm-footer">
            <button class="btn-cancel" id="cancelOrderBtn">Cancel</button>
            <button class="btn-confirm-order" id="confirmOrderBtn">Confirm Booking</button>
        </div>
</div>
</div>

<!-- Tweak sizes in the Review Booking Details modal (increase readable text and image sizes; leave buttons as-is) -->
<style>
    /* Make the Review Booking Details modal wider */
    #orderConfirmModal .modal-content {
        max-width: 1100px !important;
        width: 95% !important;
    }
    /* Target the order confirmation modal only */
    #orderConfirmModal .confirm-info-grid .info-label {
        font-size: 0.92rem;
        color: #374151;
    }

    #orderConfirmModal .confirm-info-grid .info-value {
        font-size: 0.99rem;
        color: #0f2b46;
        font-weight: 600;
        line-height: 1.26;
    }

    #orderConfirmModal .confirm-section-title {
        font-size: 1.02rem;
    }

    /* Slightly smaller product thumbnail for balance */
    #orderConfirmModal .product-thumb {
        width: 60px !important;
        height: 60px !important;
        object-fit: cover !important;
        border-radius: 6px !important;
    }

    #orderConfirmModal .product-name {
        display: inline-block;
        font-size: 0.98rem;
        font-weight: 600;
        margin-left: 10px;
        color: #0f2b46;
    }

    /* Make customization text slightly bigger for readability */
    #orderConfirmModal .custom-tag,
    #orderConfirmModal .confirm-custom-tag,
    #orderConfirmModal .view-design-text {
        font-size: 11px !important;
    }

    /* Ensure custom details and tags container use a readable base size */
    #orderConfirmModal .custom-details,
    #orderConfirmModal .confirm-tags-box,
    #orderConfirmModal .confirm-custom-tag {
        font-size: 0.98rem !important;
    }

    /* Make table cell text slightly smaller but readable */
    #orderConfirmModal .confirm-items-table td,
    #orderConfirmModal .confirm-items-table th {
        font-size: 0.94rem;
        vertical-align: middle;
    }

    /* Remove outer lining (borders/box-shadow) around order items section */
    #orderConfirmModal .confirm-items-container,
    #orderConfirmModal .confirm-items-table {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    /* Remove thumbnail/thumbnail-wrapper borders to avoid inner outlines */
    #orderConfirmModal .design-thumbnail-wrapper,
    #orderConfirmModal .design-thumbnail-wrapper img,
    #orderConfirmModal .product-thumb {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        padding: 0 !important;
    }

    /* Keep modal buttons unchanged */
    #orderConfirmModal .modal-footer .btn-cancel,
    #orderConfirmModal .modal-footer .btn-confirm-order {
        font-size: inherit;
    }
</style>

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
        console.log('=== loadSelectedSummary START ===');
        console.log('IS_BEGINNER_BOOKING:', IS_BEGINNER_BOOKING);
        console.log('BEGINNER_PRODUCT_ID:', BEGINNER_PRODUCT_ID);
        console.log('SELECTED_CART_IDS:', SELECTED_CART_IDS);
        
        // BEGINNER MODE: Handle direct product booking (no cart required)
        // Use PHP-detected beginner booking as primary source (most reliable)
        // Fallback to JavaScript detection if PHP didn't catch it
        const isBeginnerBookingMode = PHP_BEGINNER_BOOKING || IS_BEGINNER_BOOKING || !!BEGINNER_PRODUCT_ID || localStorage.getItem('bookingProduct') || window.FORCE_BEGINNER_BOOKING;
        
        console.log('🔍 loadSelectedSummary - isBeginnerBookingMode:', isBeginnerBookingMode);
        console.log('   PHP_BEGINNER_BOOKING:', PHP_BEGINNER_BOOKING);
        console.log('   IS_BEGINNER_BOOKING:', IS_BEGINNER_BOOKING);
        console.log('   BEGINNER_PRODUCT_ID:', BEGINNER_PRODUCT_ID);
        
        if (isBeginnerBookingMode) {
            console.log('✅ Beginner booking mode CONFIRMED: Loading product directly');
            
            // Hide cart-related elements for beginners
            const cartElements = document.querySelectorAll('.cart-only, .summary-totals-box');
            cartElements.forEach(el => el.style.display = 'none');
            
            // Show beginner-specific elements
            const beginnerElements = document.querySelectorAll('.beginner-only');
            beginnerElements.forEach(el => el.style.display = 'block');
            
            loadBeginnerProductSummary();
            return;
        }
        
        console.log('❌ Regular booking mode: Checking cart items');
        
        // Check if we have selected items (for non-beginner or cart-based booking)
        if (!SELECTED_CART_IDS) {
            console.log('❌ No cart items selected');
            
            // Show more informative message for different scenarios
            if (IS_BEGINNER) {
                showToast('Please select a product first.', 'warning', 3000);
                setTimeout(() => {
                    window.location.href = BASE_URL + 'productpage';
                }, 3000);
            } else {
                showToast('No items selected. Redirecting to cart...', 'warning', 2000);
                setTimeout(() => {
                    window.location.href = BASE_URL + 'addtocart';
                }, 2000);
            }
            return;
        }

        $.ajax({
            url: BASE_URL + "CartCon/get_selected_cart_ajax",
            method: "GET",
            data: { selected: SELECTED_CART_IDS },
            dataType: "json",
            xhrFields: { withCredentials: true }, // Ensure session cookies are sent
            success: function(res) {
                if (res.status === 'success') {
                    const summary = (res && res.summary) ? res.summary : null;
                    const items = res && res.items ? res.items : [];

                    // Update order summary - ensure elements exist
                    const itemsEl = document.getElementById('summary-items');
                    const priceRangeEl = document.getElementById('summary-price-range');
                    const itemsListEl = document.getElementById('summary-items-list');
                    
                    if (itemsEl) itemsEl.textContent = summary.items || 0;
                    
                    // Display price range for Site Assessment Orders - prefer admin-provided values
                    if (priceRangeEl && summary) {
                        if (summary.price_range_min !== undefined && summary.price_range_max !== undefined) {
                            const priceMinRaw = parseFloat(summary.price_range_min);
                            const priceMaxRaw = parseFloat(summary.price_range_max);
                            const priceMin = isNaN(priceMinRaw) ? 0 : priceMinRaw;
                            const priceMax = isNaN(priceMaxRaw) ? 0 : priceMaxRaw;
                            priceRangeEl.textContent = `₱${priceMin.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} - ₱${priceMax.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                        } else if (summary.price_range) {
                            priceRangeEl.textContent = summary.price_range;
                        } else {
                            priceRangeEl.textContent = document.getElementById('summary-price-range')?.textContent || '';
                        }
                    }

                    // Populate itemized list
                    if (itemsListEl) {
                        itemsListEl.innerHTML = '';
                        if (items && items.length > 0) {
                            items.forEach(item => {
                                const itemDiv = document.createElement('div');
                                itemDiv.className = 'summary-item-row';
                                itemDiv.style.cssText = 'display: flex; gap: 15px; padding: 15px; border: 1px solid #f0f0f0; border-radius: 10px; margin-bottom: 12px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.02); align-items: center; position: relative;';
                                
                                if (IS_BEGINNER) {
                                    // Simplified view for beginners - only product name and image
                                    itemDiv.innerHTML = `
                                        <img src="${item.image}" alt="${item.description}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; flex-shrink: 0;">
                                        <div class="summary-item-info">
                                            <h4 style="margin: 0 0 6px 0; color: #333;">${item.description}</h4>
                                            ${item.category ? `<p style="margin: 0; color: #666; font-size: 0.9rem;">${item.category}</p>` : ''}
                                        </div>
                                    `;
                                } else {
                                    // Full view for professional users — use beginner-style product card with fallbacks
                                    const rawImage = item.image || item.ImageUrl || item.Image || item.ImageURL || '';
                                    const profImageUrl = rawImage ? (rawImage.startsWith('http') ? rawImage : BASE_URL + rawImage) : (BASE_URL + 'assets/images/placeholder.png');

                                    const priceMinVal = (typeof item.PriceMin !== 'undefined') ? item.PriceMin : (typeof item.price_min !== 'undefined' ? item.price_min : null);
                                    const priceMaxVal = (typeof item.PriceMax !== 'undefined') ? item.PriceMax : (typeof item.price_max !== 'undefined' ? item.price_max : null);
                                    const priceVal = (typeof item.Price !== 'undefined') ? item.Price : (typeof item.price !== 'undefined' ? item.price : null);
                                    const priceRangeField = (typeof item.price_range !== 'undefined') ? item.price_range : (typeof item.PriceRange !== 'undefined' ? item.PriceRange : (typeof item.priceRange !== 'undefined' ? item.priceRange : null));

                                    let priceDisplay = 'Price TBD after assessment';
                                    // Prefer summary price range (site-assessment range) when available
                                    if (typeof summary !== 'undefined' && summary && typeof summary.price_range_min !== 'undefined' && typeof summary.price_range_max !== 'undefined' && summary.price_range_min !== null && summary.price_range_max !== null) {
                                        const pMin = parseFloat(summary.price_range_min);
                                        const pMax = parseFloat(summary.price_range_max);
                                        if (!isNaN(pMin) && !isNaN(pMax)) {
                                            priceDisplay = `₱${pMin.toLocaleString()} - ₱${pMax.toLocaleString()}`;
                                        }
                                    } else if (priceMinVal !== null && priceMaxVal !== null) {
                                        priceDisplay = `₱${parseFloat(priceMinVal).toLocaleString()} - ₱${parseFloat(priceMaxVal).toLocaleString()}`;
                                    } else if (priceRangeField) {
                                        priceDisplay = priceRangeField;
                                    } else if (priceVal) {
                                        priceDisplay = `Starting at ₱${parseFloat(priceVal).toLocaleString()}`;
                                    }

                                    const categoryText = item.category || item.Category || item.CategoryName || '';
                                    const subcategoryText = item.subcategory || item.Subcategory || item.SubcategoryName || item.subCategory || '';

                                    itemDiv.innerHTML = `
                                        <img src="${profImageUrl}" alt="${item.description}" style="width: 90px; height: 90px; object-fit: cover; border-radius: 10px; border: 2px solid #e3f2fd; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" onerror="this.src='${BASE_URL}assets/images/placeholder.png'">
                                        <div class="summary-item-info" style="flex-grow: 1;">
                                            <h4 style="margin: 0 0 8px 0; color: #1976d2; font-size: 1.1rem; font-weight: 600;">${item.description}</h4>
                                            <p style="margin: 0 0 6px 0; color: #28a745; font-size: 1rem; font-weight: 600;">${priceDisplay}</p>
                                            ${categoryText ? `<p style="margin: 0 0 2px 0; color: #666; font-size: 0.9rem;">${categoryText}</p>` : ''}
                                            ${subcategoryText ? `<p style="margin: 0; color: #888; font-size: 0.85rem; font-style: italic;">${subcategoryText}</p>` : ''}
                                        </div>
                                    `;
                                }
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
                    const priceRangeEl = document.getElementById('summary-price-range');
                    
                    if (itemsEl) itemsEl.textContent = '0';
                    if (priceRangeEl) priceRangeEl.textContent = '';
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load cart summary:', error);
                // Set default values on error
                const itemsEl = document.getElementById('summary-items');
                const priceRangeEl = document.getElementById('summary-price-range');
                
                if (itemsEl) itemsEl.textContent = '0';
                if (priceRangeEl) priceRangeEl.textContent = '';
            }
        });
    }
    
    // =============================
    // BEGINNER MODE: Load product directly (no cart)
    // =============================
    function loadBeginnerProductSummary() {
        console.log('=== loadBeginnerProductSummary START ===');
        
        // Priority order for product ID:
        // 1. PHP-provided (most reliable)
        // 2. JavaScript BEGINNER_PRODUCT_ID (from URL)
        // 3. localStorage
        let productId = PHP_BEGINNER_PRODUCT_ID || BEGINNER_PRODUCT_ID || '';
        let productName = PHP_BEGINNER_PRODUCT_NAME || BEGINNER_PRODUCT_NAME || '';
        
        console.log('Initial productId:', productId, 'from:', 
            PHP_BEGINNER_PRODUCT_ID ? 'PHP' : 
            BEGINNER_PRODUCT_ID ? 'URL' : 'none');
        
        if (!productId) {
            try {
                const storedProduct = localStorage.getItem('bookingProduct');
                if (storedProduct) {
                    const parsed = JSON.parse(storedProduct);
                    if (parsed && parsed.id) {
                        productId = parsed.id;
                        productName = parsed.name || '';
                        console.log('✅ Using product from localStorage:', parsed);
                    }
                }
            } catch (e) {
                console.error('Error reading localStorage:', e);
            }
        }
        
        console.log('Final product ID to load:', productId);
        console.log('Final product name:', productName);
        
        if (!productId) {
            console.error('❌ No product ID available for beginner booking');
            // Fallback: show generic product entry
            const itemsEl = document.getElementById('summary-items');
            const priceRangeEl = document.getElementById('summary-price-range');
            const itemsListEl = document.getElementById('summary-items-list');
            
            if (itemsEl) itemsEl.textContent = '1';
            if (priceRangeEl) priceRangeEl.textContent = 'Price TBD after assessment';
            
            if (itemsListEl) {
                itemsListEl.innerHTML = `
                    <div class="summary-item-row beginner-product-item" style="display: flex; gap: 15px; padding: 20px; border: 1px solid #e3f2fd; border-radius: 12px; margin-bottom: 15px; background: linear-gradient(135deg, #fff 0%, #f8faff 100%); box-shadow: 0 4px 12px rgba(0,123,255,0.08); align-items: center;">
                        <div class="placeholder-icon" style="width: 90px; height: 90px; background: #f8f9fa; border-radius: 10px; border: 2px solid #e3f2fd; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-cube" style="font-size: 2rem; color: #ccc;"></i>
                        </div>
                        <div class="summary-item-info">
                            <h4 style="margin: 0 0 6px 0; color: #333;">Selected Product</h4>
                            <p style="margin: 0; color: #666; font-size: 0.9rem;">Product details will be confirmed during ocular visit</p>
                        </div>
                    </div>
                `;
            }
            
            // Set up minimal product info for booking
            window.beginnerBookingProduct = {
                id: 'unknown',
                name: 'Selected Product',
                category: '',
                subcategory: '',
                imageUrl: '',
                priceRange: 'Price TBD after assessment'
            };
            return;
        }
        
        console.log('Loading beginner product summary for product ID:', productId);
        
        // Fetch product details from API
        $.ajax({
            url: BASE_URL + "ProductCon/get_product_details",
            method: "GET",
            data: { product_id: productId },
            dataType: "json",
            success: function(res) {
                console.log('✅ Product details response:', res);
                
                const itemsEl = document.getElementById('summary-items');
                const priceRangeEl = document.getElementById('summary-price-range');
                const itemsListEl = document.getElementById('summary-items-list');
                
                if (res.status === 'success' && res.product) {
                    const product = res.product;
                    
                    // Update summary count
                    if (itemsEl) itemsEl.textContent = '1';
                    
                    // Update price range (use product's price range if available)
                    if (priceRangeEl) {
                        if (product.PriceMin && product.PriceMax) {
                            priceRangeEl.textContent = `₱${parseFloat(product.PriceMin).toLocaleString()} - ₱${parseFloat(product.PriceMax).toLocaleString()}`;
                        } else if (product.Price) {
                            priceRangeEl.textContent = `Starting at ₱${parseFloat(product.Price).toLocaleString()}`;
                        } else {
                            priceRangeEl.textContent = 'Price TBD after assessment';
                        }
                    }
                    
                    // Populate product in list
                    if (itemsListEl) {
                        const imageUrl = product.ImageUrl ? 
                            (product.ImageUrl.startsWith('http') ? product.ImageUrl : BASE_URL + product.ImageUrl) :
                            BASE_URL + 'assets/images/placeholder.png';
                        
                        // Build price range display
                        let priceDisplay = 'Price TBD after assessment';
                        if (product.PriceMin && product.PriceMax) {
                            priceDisplay = `₱${parseFloat(product.PriceMin).toLocaleString()} - ₱${parseFloat(product.PriceMax).toLocaleString()}`;
                        } else if (product.Price) {
                            priceDisplay = `Starting at ₱${parseFloat(product.Price).toLocaleString()}`;
                        }
                        
                        itemsListEl.innerHTML = `
                            <div class="summary-item-row beginner-product-item" style="display: flex; gap: 15px; padding: 20px; border: 1px solid #e3f2fd; border-radius: 12px; margin-bottom: 15px; background: linear-gradient(135deg, #fff 0%, #f8faff 100%); box-shadow: 0 4px 12px rgba(0,123,255,0.08); align-items: center; transition: transform 0.2s;">
                                <img src="${imageUrl}" alt="${product.ProductName}" style="width: 90px; height: 90px; object-fit: cover; border-radius: 10px; border: 2px solid #e3f2fd; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" onerror="this.src='${BASE_URL}assets/images/placeholder.png'">
                                <div class="summary-item-info" style="flex-grow: 1;">
                                    <h4 style="margin: 0 0 8px 0; color: #1976d2; font-size: 1.1rem; font-weight: 600;">${product.ProductName}</h4>
                                    <p style="margin: 0 0 6px 0; color: #28a745; font-size: 1rem; font-weight: 600;">${priceDisplay}</p>
                                    ${product.Category ? `<p style="margin: 0 0 2px 0; color: #666; font-size: 0.9rem;">${product.Category}</p>` : ''}
                                    ${product.Subcategory ? `<p style="margin: 0; color: #888; font-size: 0.85rem; font-style: italic;">${product.Subcategory}</p>` : ''}
                                </div>
                            </div>
                        `;
                    }
                    
                    // Store product info for booking submission
                    const productImageUrl = product.ImageUrl ? 
                        (product.ImageUrl.startsWith('http') ? product.ImageUrl : BASE_URL + product.ImageUrl) :
                        BASE_URL + 'assets/images/placeholder.png';
                    
                    let productPriceRange = 'Price TBD after assessment';
                    if (product.PriceMin && product.PriceMax) {
                        productPriceRange = `₱${parseFloat(product.PriceMin).toLocaleString()} - ₱${parseFloat(product.PriceMax).toLocaleString()}`;
                    } else if (product.Price) {
                        productPriceRange = `Starting at ₱${parseFloat(product.Price).toLocaleString()}`;
                    }
                    
                    window.beginnerBookingProduct = {
                        id: product.Product_ID,
                        name: product.ProductName,
                        category: product.Category || '',
                        subcategory: product.Subcategory || '',
                        imageUrl: productImageUrl,
                        priceRange: productPriceRange
                    };
                    
                } else {
                    // Product not found - use URL parameters as fallback
                    console.log('⚠️ Product not found in API, using fallback data');
                    
                    if (itemsEl) itemsEl.textContent = '1';
                    if (priceRangeEl) priceRangeEl.textContent = 'Price TBD after assessment';
                    
                    if (itemsListEl) {
                        itemsListEl.innerHTML = `
                            <div class="summary-item-row beginner-product-item" style="display: flex; gap: 15px; padding: 20px; border: 1px solid #e3f2fd; border-radius: 12px; margin-bottom: 15px; background: linear-gradient(135deg, #fff 0%, #f8faff 100%); box-shadow: 0 4px 12px rgba(0,123,255,0.08); align-items: center;">
                                <div class="placeholder-icon" style="width: 90px; height: 90px; background: #f8f9fa; border-radius: 10px; border: 2px solid #e3f2fd; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fas fa-cube" style="font-size: 2rem; color: #1976d2;"></i>
                                </div>
                                <div class="summary-item-info">
                                    <h4 style="margin: 0 0 6px 0; color: #333;">${decodeURIComponent(productName) || 'Selected Product'}</h4>
                                    <p style="margin: 0; color: #666; font-size: 0.9rem;">Product ID: ${productId}</p>
                                    <div style="margin-top: 8px; display: flex; align-items: center; color: #28a745;">
                                        <i class="fas fa-check-circle" style="margin-right: 6px;"></i>
                                        <span style="font-size: 0.85rem; font-weight: 500;">Ready for ocular visit</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Store product info for booking submission
                    window.beginnerBookingProduct = {
                        id: productId,
                        name: decodeURIComponent(productName) || 'Selected Product',
                        category: '',
                        subcategory: '',
                        imageUrl: '',
                        priceRange: 'Price TBD after assessment'
                    };
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Failed to load product details:', error);
                console.error('XHR Status:', xhr.status, 'Response:', xhr.responseText);
                
                // Fallback: use available data
                const itemsEl = document.getElementById('summary-items');
                const priceRangeEl = document.getElementById('summary-price-range');
                const itemsListEl = document.getElementById('summary-items-list');
                
                if (itemsEl) itemsEl.textContent = '1';
                if (priceRangeEl) priceRangeEl.textContent = 'Price TBD after assessment';
                
                if (itemsListEl) {
                    itemsListEl.innerHTML = `
                        <div class="summary-item-row beginner-product-item" style="display: flex; gap: 15px; padding: 20px; border: 1px solid #fff3cd; border-radius: 12px; margin-bottom: 15px; background: linear-gradient(135deg, #fff 0%, #fffbf0 100%); box-shadow: 0 4px 12px rgba(255,193,7,0.08); align-items: center;">
                            <div class="placeholder-icon" style="width: 90px; height: 90px; background: #fff3cd; border-radius: 10px; border: 2px solid #ffc107; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #856404;"></i>
                            </div>
                            <div class="summary-item-info">
                                <h4 style="margin: 0 0 6px 0; color: #333;">${decodeURIComponent(productName) || 'Selected Product'}</h4>
                                <p style="margin: 0; color: #666; font-size: 0.9rem;">Product details will be confirmed during ocular visit</p>
                            </div>
                        </div>
                    `;
                }
                
                window.beginnerBookingProduct = {
                    id: productId || 'unknown',
                    name: decodeURIComponent(productName) || 'Selected Product',
                    category: '',
                    subcategory: '',
                    imageUrl: '',
                    priceRange: 'Price TBD after assessment'
                };
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
    document.getElementById('ewallet-radio')?.addEventListener('change', function() {
        const errorDiv = document.getElementById('payment-method-error');
        if (errorDiv) errorDiv.style.display = 'none';
    });

    document.getElementById('card-radio')?.addEventListener('change', function() {
        const errorDiv = document.getElementById('payment-method-error');
        if (errorDiv) errorDiv.style.display = 'none';
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

        // Get all address fields (use let for fields we may override from saved-address data)
        let unitHouseNumber = form.querySelector("input[name='unit_house_number']")?.value || '';
        let street = form.querySelector("input[name='street']")?.value || '';
        let subdivision = form.querySelector("input[name='subdivision']")?.value || '';
        let barangay = form.querySelector("input[name='barangay']")?.value || '';
        let city = form.querySelector("[name='city']")?.value || '';
        let province = form.querySelector("[name='province']")?.value || '';
        let region = form.querySelector("[name='region']")?.value || '';
        let zipcode = form.querySelector("input[name='zipcode']")?.value || '';
        let country = form.querySelector("input[name='country']")?.value || 'Philippines';
        const preferredInstallationDate = form.querySelector("input[name='preferred_installation_date']")?.value || '';

        // If user selected a saved address and shipping form is hidden, prefer saved-address data
        const savedAddressDropdownEl = document.getElementById('saved-address-dropdown');
        const shippingFieldsEl = document.getElementById('shipping-address-fields');
        let selectedSavedAddressLabel = null;
        if (savedAddressDropdownEl && savedAddressDropdownEl.value && shippingFieldsEl && shippingFieldsEl.style.display === 'none') {
            const selectedOption = savedAddressDropdownEl.options[savedAddressDropdownEl.selectedIndex];
            selectedSavedAddressLabel = selectedOption?.textContent?.trim() || null;
            const addressDataRaw = selectedOption?.getAttribute('data-address') || null;
            if (addressDataRaw) {
                try {
                    const addressData = JSON.parse(addressDataRaw);
                    console.log('Booking confirm modal - parsed saved address:', addressData);

                    // Helper to read multiple possible key names (case-insensitive and common variants)
                    function readField(obj, ...keys) {
                        if (!obj) return undefined;
                        // direct keys first
                        for (const k of keys) {
                            if (obj[k] !== undefined && obj[k] !== null && String(obj[k]).trim() !== '') return obj[k];
                        }
                        // try lowercase variants
                        const lowerMap = {};
                        Object.keys(obj).forEach(k => { lowerMap[k.toLowerCase()] = obj[k]; });
                        for (const k of keys) {
                            const lk = k.toLowerCase();
                            if (lowerMap[lk] !== undefined && lowerMap[lk] !== null && String(lowerMap[lk]).trim() !== '') return lowerMap[lk];
                        }
                        return undefined;
                    }

                    unitHouseNumber = readField(addressData, 'UnitHouseNumber', 'Unit', 'unit_house_number') || unitHouseNumber;
                    street = readField(addressData, 'Street', 'street') || street;
                    subdivision = readField(addressData, 'Subdivision', 'subdivision') || subdivision;
                    barangay = readField(addressData, 'Barangay', 'barangay') || barangay;
                    city = readField(addressData, 'City', 'city', 'Municipality') || city;
                    province = readField(addressData, 'Province', 'province', 'State', 'state') || province;
                    region = readField(addressData, 'Region', 'region') || region;
                    zipcode = readField(addressData, 'ZipCode', 'zipcode', 'PostalCode', 'postal_code') || zipcode;
                    country = readField(addressData, 'Country', 'country') || country;
                } catch (err) {
                    console.error('Failed to parse saved address JSON for confirm modal:', err);
                }
            }
        }

        // Build formatted address lines according to required system format:
        // Line1: Unit/House Number, Street, Subdivision
        // Line2: Barangay, City/Municipality, State/Province, Region, Postal Code, Country (Full Name)
        function escapeHtml(str) {
            if (!str && str !== 0) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        const line1Parts = [unitHouseNumber, street, subdivision].filter(Boolean);
        const line2Parts = [barangay, city, province, region, zipcode, country].filter(Boolean);
        const line1 = line1Parts.join(', ');
        // If city/province missing, try to fallback to saved option label which often includes them
        let line2 = line2Parts.join(', ');
        if (( !city || !province ) && selectedSavedAddressLabel) {
            // Use the saved option label as a best-effort fallback
            line2 = selectedSavedAddressLabel + (zipcode ? (', ' + zipcode) : '') + (country ? (', ' + country) : '');
        }

        // Populate shipping details
        const fullName = middlename ? `${firstname} ${middlename} ${lastname}` : `${firstname} ${lastname}`;
        document.getElementById('confirm-name').textContent = fullName;
        document.getElementById('confirm-email').textContent = email;
        document.getElementById('confirm-phone').textContent = phone;

        const addressEl = document.getElementById('confirm-address');
        if (addressEl) {
            if (line1 && line2) {
                addressEl.innerHTML = escapeHtml(line1) + '<br>' + escapeHtml(line2);
            } else if (line1) {
                addressEl.textContent = line1;
            } else if (line2) {
                addressEl.textContent = line2;
            } else {
                addressEl.textContent = '';
            }
        }

        // Populate special instructions / note
        const noteEl = document.getElementById('confirm-note');
        const noteValue = form.querySelector("textarea[name='note']")?.value || '';
        if (noteEl) {
            if (noteValue && String(noteValue).trim() !== '') {
                // Preserve line breaks and escape HTML
                noteEl.innerHTML = escapeHtml(noteValue).replace(/\n/g, '<br>');
            } else {
                // Show placeholder when there's no note
                noteEl.innerHTML = '<span style="color: #999; font-style: italic;">No notes or special instructions</span>';
            }
        }

        // Payment method not needed for Site Assessment Orders
        const paymentSection = document.getElementById('confirm-payment-section');
        if (paymentSection) {
            paymentSection.style.display = 'none';
        }

        // Preferred Ocular Visit Date (for booking page)
        const installationDateSection = document.getElementById('confirm-installation-date-section');
        const installationDateValue = document.getElementById('confirm-installation-date');
        const preferredTimeValue = document.getElementById('confirm-installation-time');
        const preferredTimeSelect = form.querySelector("select[name='preferred_time']");
        const preferredTime = preferredTimeSelect ? preferredTimeSelect.options[preferredTimeSelect.selectedIndex]?.text : '';
        
        if (preferredInstallationDate) {
            const formattedDate = new Date(preferredInstallationDate).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            if (installationDateValue) {
                installationDateValue.textContent = formattedDate;
            }
            if (preferredTimeValue && preferredTime) {
                preferredTimeValue.textContent = preferredTime;
            }
            if (installationDateSection) {
                installationDateSection.style.display = 'block';
            }
        } else {
            if (installationDateSection) {
                installationDateSection.style.display = 'none';
            }
        }

        // Fetch SELECTED cart items from server via AJAX
        const itemsBody = document.getElementById('confirm-items-body');
        
        // Check if this is a beginner booking
        const isBeginnerMode = PHP_BEGINNER_BOOKING || IS_BEGINNER_BOOKING || !!BEGINNER_PRODUCT_ID;
        
        if (isBeginnerMode && window.beginnerBookingProduct) {
            // Beginner mode: Show product with 2 columns (Product, Price Range)
            console.log('📋 Modal: Beginner mode - showing product with price range');
            const product = window.beginnerBookingProduct;
            
            const placeholderSvg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
            
            const productImage = product.imageUrl || placeholderSvg;
            const priceRange = product.priceRange || 'Price TBD after assessment';
            
            itemsBody.innerHTML = `
                <tr>
                    <td class="product-cell">
                        <div class="product-info" style="display: flex; align-items: center; gap: 12px;">
                            <img src="${productImage}" alt="${product.name}" class="product-thumb" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e0e0e0;" onerror="this.onerror=null; this.src='${placeholderSvg}';">
                            <div>
                                <span class="product-name" style="font-weight: 600; color: #333; display: block;">${product.name}</span>
                                ${product.category ? `<span style="font-size: 0.85rem; color: #666;">${product.category}${product.subcategory ? ' - ' + product.subcategory : ''}</span>` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="price-range-cell" style="text-align: left; font-weight: 600; color: #0f2b46;">
                        ${priceRange}
                    </td>
                </tr>
            `;
            
            // Hide the price range footer for beginners (already shown in table)
            const confirmTotalsEl = document.querySelector('.confirm-totals');
            if (confirmTotalsEl) {
                confirmTotalsEl.style.display = 'none';
            }
        } else {
            // Regular cart-based booking
            itemsBody.innerHTML = '<tr><td colspan="4" class="no-items">Loading items...</td></tr>';

        $.getJSON(BASE_URL + "CartCon/get_selected_cart_ajax?selected=" + SELECTED_CART_IDS, function(res) {
            if (res.status === 'success') {
                itemsBody.innerHTML = '';
                
                // Helper: prefer structured `final-specs` JSON when available, else fallback to customization string
                function getSpecsFromFinalOrString(customizationString, breakdownFields) {
                    console.log('getSpecsFromFinalOrString called with:', {
                        customizationString: customizationString,
                        breakdownFields: breakdownFields,
                        isArray: Array.isArray(breakdownFields),
                        length: breakdownFields ? breakdownFields.length : 0
                    });
                    
                    // First priority: use server-provided breakdown fields (from Customization JSON in database)
                    if (Array.isArray(breakdownFields) && breakdownFields.length) {
                        console.log('🔍 Breakdown fields details:', JSON.stringify(breakdownFields, null, 2));
                        const result = breakdownFields.map(f => {
                            const label = f.label || '';
                            const value = f.value || f.val || '';
                            const formatted = `${label}: ${value}`;
                            console.log(`  Field: ${label} = "${value}" → "${formatted}"`);
                            return formatted;
                        });
                        console.log('Using breakdown fields, result:', result);
                        return result;
                    }

                    // Fallback: parse provided customization string (legacy)
                    if (!customizationString) return [];
                    const result = customizationString.split(' | ').map(p => p && p.trim()).filter(Boolean);
                    console.log('Using customization string fallback, result:', result);
                    return result;
                }

                res.items.forEach((item, itemIndex) => {
                    console.log(`Processing item ${itemIndex}:`, {
                        cart_id: item.cart_id,
                        customization: item.customization,
                        customization_breakdown: item.customization_breakdown,
                        has_breakdown: !!item.customization_breakdown,
                        breakdown_length: item.customization_breakdown ? item.customization_breakdown.length : 0,
                        breakdown_detail: item.customization_breakdown
                    });
                    console.log('🔍 Full breakdown array:', JSON.stringify(item.customization_breakdown, null, 2));
                    
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
                            const parts = getSpecsFromFinalOrString(customizationString, item.customization_breakdown || []);
                            // Display only first 2 specs to avoid overcrowding
                            const displayParts = parts.slice(0, 2);
                            const remainingCount = parts.length - 2;
                            let displayText = displayParts.length ? displayParts.join(' • ') : 'View customization';
                            if (remainingCount > 0) {
                                displayText += `<br><span style="font-size:12px; color:#4b5563;">and ${remainingCount} more</span>`;
                            }
                            displayText += `<br><span style="font-size:11px; opacity:0.7;">▼ Click to expand</span>`;
                            
                            // Add indicator if using legacy fallback data vs actual 2D data
                            const hasActual2DData = Array.isArray(item.customization_breakdown) && item.customization_breakdown.length > 0;
                            if (!hasActual2DData && parts.length > 0) {
                                console.warn('Using legacy field fallback for Cart_ID:', item.cart_id);
                                displayText = '⚠️ ' + displayText + ' (legacy data)';
                            }
                            
                            const breakdownPayload = hasActual2DData ? encodeURIComponent(JSON.stringify(item.customization_breakdown)) : encodeURIComponent(customizationString);
                            customHtml += `<button type="button" class="view-more-specs open-breakdown" data-breakdown="${breakdownPayload}" data-customization="${encodeURIComponent(customizationString)}" data-item-index="${itemIndex}" style="display:inline-block; text-align:left; padding:10px 14px; border-radius:6px; border:2px solid #3b82f6; background:#eff6ff; color:#1e40af; cursor:pointer; font-size:13px; line-height:1.6; max-width:100%; word-wrap:break-word; white-space:normal; transition:all 0.2s ease; font-weight:600; box-shadow:0 2px 4px rgba(59,130,246,0.1);" onmouseover="this.style.backgroundColor='#dbeafe'; this.style.borderColor='#2563eb'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.2)';" onmouseout="this.style.backgroundColor='#eff6ff'; this.style.borderColor='#3b82f6'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(59,130,246,0.1)';" onclick="console.log('🖱️ Customization button clicked!', {breakdown: '${breakdownPayload}'.substring(0,100)});">${displayText}</button>`;
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
                            const parts = getSpecsFromFinalOrString(customizationString, item.customization_breakdown || []);
                            // Display only first 2 specs to avoid overcrowding
                            const displayParts = parts.slice(0, 2);
                            const remainingCount = parts.length - 2;
                            let displayText = displayParts.length ? displayParts.join(' • ') : 'View customization';
                            if (remainingCount > 0) {
                                displayText += `<br><span style="font-size:12px; color:#4b5563;">and ${remainingCount} more</span>`;
                            }
                            displayText += `<br><span style="font-size:11px; opacity:0.7;">▼ Click to expand</span>`;
                            
                            // Add indicator if using legacy fallback data vs actual 2D data
                            const hasActual2DData = Array.isArray(item.customization_breakdown) && item.customization_breakdown.length > 0;
                            if (!hasActual2DData && parts.length > 0) {
                                console.warn('Using legacy field fallback for Cart_ID:', item.cart_id);
                                displayText = '⚠️ ' + displayText + ' (legacy data)';
                            }
                            
                            const breakdownPayload = hasActual2DData ? encodeURIComponent(JSON.stringify(item.customization_breakdown)) : encodeURIComponent(customizationString);
                            customHtml += `<button type="button" class="view-more-specs open-breakdown" data-breakdown="${breakdownPayload}" data-customization="${encodeURIComponent(customizationString)}" data-item-index="${itemIndex}" style="display:inline-block; text-align:left; padding:10px 14px; border-radius:6px; border:2px solid #3b82f6; background:#eff6ff; color:#1e40af; cursor:pointer; font-size:13px; line-height:1.6; max-width:100%; word-wrap:break-word; white-space:normal; transition:all 0.2s ease; font-weight:600; box-shadow:0 2px 4px rgba(59,130,246,0.1);" onmouseover="this.style.backgroundColor='#dbeafe'; this.style.borderColor='#2563eb'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.2)';" onmouseout="this.style.backgroundColor='#eff6ff'; this.style.borderColor='#3b82f6'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(59,130,246,0.1)';" onclick="console.log('🖱️ Customization button clicked!', {breakdown: '${breakdownPayload}'.substring(0,100)});">${displayText}</button>`;
                        } else {
                            customHtml += '<span style="color: #888; font-size: 12px;">Standard</span>';
                        }

                        customHtml += `</div>`;
                    }

                    const itemTotal = Number(item.total) || 0;
                    
                    // Placeholder SVG for missing images
                    const placeholderSvg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                    
                    // Determine item-level price range
                    let priceRangeText = '';
                    try {
                        const pMin = (item.PriceMin !== undefined) ? parseFloat(item.PriceMin) : (item.price_min !== undefined ? parseFloat(item.price_min) : null);
                        const pMax = (item.PriceMax !== undefined) ? parseFloat(item.PriceMax) : (item.price_max !== undefined ? parseFloat(item.price_max) : null);
                        if (pMin != null && pMax != null && !isNaN(pMin) && !isNaN(pMax)) {
                            priceRangeText = `₱${pMin.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})} - ₱${pMax.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
                        } else if (pMin != null && !isNaN(pMin)) {
                            priceRangeText = `Starting at ₱${pMin.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
                        } else if (item.Price !== undefined || item.price !== undefined || item.total !== undefined) {
                            const single = item.Price !== undefined ? parseFloat(item.Price) : (item.price !== undefined ? parseFloat(item.price) : parseFloat(item.total || 0));
                            if (!isNaN(single)) priceRangeText = `₱${single.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
                        }
                    } catch (e) { console.warn('Price range parse error', e); }

                    row.innerHTML = `
                        <td class="product-cell">
                            <div class="product-info">
                                <img src="${productImage}" alt="${item.description}" class="product-thumb" onerror="this.onerror=null; this.src='${placeholderSvg}';">
                                <div style="display:flex; flex-direction:column;">
                                    <span class="product-name">${item.description}</span>
                                    ${item.category ? `<span style="font-size: 0.85rem; color: #666; margin-top:4px;">${item.category}${item.subcategory ? ' - ' + item.subcategory : ''}</span>` : ''}
                                </div>
                            </div>
                        </td>
                        <td class="customization-cell">${customHtml}</td>
                        <td class="qty-cell">${item.quantity}</td>
                        <td class="price-range-cell" style="text-align: left; font-weight:600; color:#0f2b46;">${priceRangeText}</td>
                    `;
                    itemsBody.appendChild(row);
                });

                // Update price range from server response (defensive) - always prefer admin-provided values
                const summary = (res && res.summary) ? res.summary : null;
                const confirmPriceEl = document.getElementById('confirm-price-range');
                if (confirmPriceEl) {
                    if (summary) {
                        if (summary.price_range_min !== undefined && summary.price_range_max !== undefined) {
                            const priceMinRaw = parseFloat(summary.price_range_min);
                            const priceMaxRaw = parseFloat(summary.price_range_max);
                            const priceMin = isNaN(priceMinRaw) ? 0 : priceMinRaw;
                            const priceMax = isNaN(priceMaxRaw) ? 0 : priceMaxRaw;
                            confirmPriceEl.textContent = `₱${priceMin.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} - ₱${priceMax.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                        } else if (summary.price_range) {
                            confirmPriceEl.textContent = summary.price_range;
                        } else {
                            confirmPriceEl.textContent = document.getElementById('summary-price-range')?.textContent || '';
                        }
                    } else {
                        confirmPriceEl.textContent = document.getElementById('summary-price-range')?.textContent || '';
                    }
                }
            } else {
                // Fallback: Get totals from page summary (already includes peso sign)
                const summaryPriceRange = document.getElementById('summary-price-range')?.textContent || '';
                const itemCount = document.querySelectorAll('.summary-item-row').length;
                const confirmPriceEl = document.getElementById('confirm-price-range');
                if (confirmPriceEl) confirmPriceEl.textContent = summaryPriceRange;
                
                itemsBody.innerHTML = `<tr><td colspan="4" class="no-items">${itemCount} item(s) in your cart</td></tr>`;
            }
        }).fail(function() {
            // Fallback on AJAX failure (values already include peso sign)
            const summaryPriceRange = document.getElementById('summary-price-range')?.textContent || '';
            const itemCount = document.querySelectorAll('.summary-item-row').length;
            const confirmPriceEl = document.getElementById('confirm-price-range');
            if (confirmPriceEl) confirmPriceEl.textContent = summaryPriceRange;
            itemsBody.innerHTML = `<tr><td colspan="4" class="no-items">${itemCount} item(s) in your cart</td></tr>`;
        });
        } // End of else block for regular cart booking
    }

    // Design preview helper
    window.showDesignModal = function(url) {
        try {
            const modal = document.getElementById('designPreviewModal');
            const img = document.getElementById('designPreviewImage');
            if (!modal || !img) return;
            img.src = url || '';
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        } catch (e) {
            console.error('Failed to open design preview modal', e);
        }
    };

    window.closeDesignModal = function() {
        const modal = document.getElementById('designPreviewModal');
        const img = document.getElementById('designPreviewImage');
        if (!modal) return;
        modal.classList.remove('show');
        if (img) img.src = '';
        document.body.style.overflow = '';
    };

    // Close design modal when clicking overlay
    document.getElementById('designPreviewModal')?.querySelector('.modal-overlay')?.addEventListener('click', closeDesignModal);

    // Close design modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('designPreviewModal')?.classList.contains('show')) {
            closeDesignModal();
        }
    });

    // View more specs handler - opens breakdown modal showing full 2D customization breakdown
    $(document).on('click', '.view-more-specs', function (e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('🔍 Modal trigger clicked!');
        const btn = $(this);
        const encodedBreakdown = btn.data('breakdown') || btn.attr('data-breakdown') || '';
        const encoded = btn.data('customization') || btn.attr('data-customization') || '';
        const customizationString = decodeURIComponent(String(encoded || ''));
        console.log('📦 Breakdown payload:', encodedBreakdown.substring(0, 200));

        // Attempt to use structured breakdown (JSON) first, fallback to string parsing
        let breakdownObj = null;
        if (encodedBreakdown) {
            try {
                const decoded = decodeURIComponent(String(encodedBreakdown || ''));
                breakdownObj = JSON.parse(decoded);
            } catch (e) {
                breakdownObj = null;
            }
        }

        let contentHtml = '';

        if (Array.isArray(breakdownObj) && breakdownObj.length) {
            contentHtml += '<div class="breakdown-list" style="padding:0;">';
            breakdownObj.forEach(entry => {
                const label = entry.label || '';
                const value = entry.value || entry.val || '';
                // Skip entries with no value
                if (!value || value === '' || value === 'None') {
                    contentHtml += `<div style="margin-bottom:16px; padding:12px; background:#f9fafb; border-left:4px solid #d1d5db; border-radius:4px;"><strong style="display:block;color:#1f2937; margin-bottom:6px; font-size:14px;">${label}</strong><div style="color:#9ca3af; font-style:italic; font-size:13px;">Not specified</div></div>`;
                } else {
                    contentHtml += `<div style="margin-bottom:16px; padding:12px; background:#f0f9ff; border-left:4px solid:#3b82f6; border-radius:4px;"><strong style="display:block;color:#1e40af; margin-bottom:6px; font-size:14px;">${label}</strong><div style="color:#1f2937; font-size:14px; font-weight:500;">${value}</div></div>`;
                }
            });
            contentHtml += '</div>';
        } else if (breakdownObj && typeof breakdownObj === 'object' && Object.keys(breakdownObj).length) {
            contentHtml += '<div class="breakdown-list" style="padding:0;">';
            Object.keys(breakdownObj).forEach(k => {
                const label = k;
                const value = breakdownObj[k];
                contentHtml += `<div style="margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #e5e7eb;"><strong style="display:block;color:#0f2b46; margin-bottom:4px;">${label}</strong><div style="color:#374151;">${value}</div></div>`;
            });
            contentHtml += '</div>';
        } else {
            // Fallback: parse customization string parts
            const parts = customizationString ? customizationString.split(' | ').map(p => p && p.trim()).filter(Boolean) : [];
            if (parts.length > 0) {
                contentHtml += '<div class="breakdown-list" style="padding:0;">';
                parts.forEach(p => {
                    const colonIdx = p.indexOf(':');
                    if (colonIdx > 0) {
                        const label = p.substring(0, colonIdx).trim();
                        const value = p.substring(colonIdx + 1).trim();
                        contentHtml += `<div style="margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #e5e7eb;"><strong style="display:block;color:#0f2b46; margin-bottom:4px;">${label}</strong><div style="color:#374151;">${value}</div></div>`;
                    } else {
                        contentHtml += `<div style="margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #e5e7eb;"><div style="color:#374151;">${p}</div></div>`;
                    }
                });
                contentHtml += '</div>';
            } else {
                contentHtml = '<p style="color:#6b7280;">No customization details available.</p>';
            }
        }

        // Insert into modal body and show
        let modal = document.getElementById('breakdownModal');
        if (!modal) {
            // create modal markup if not present
            const modalHtml = `
                <div id="breakdownModal" class="modal-backdrop" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:10000;">
                    <div class="modal-content" style="max-width:720px;width:90%;max-height:85vh;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.3);">
                        <div class="modal-header" style="background:#1e3a8a;color:#fff;padding:16px 20px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;">
                            <h3 style="margin:0;font-size:20px;font-weight:700;">2D Customization Breakdown</h3>
                            <button class="modal-close" id="breakdownModalClose" style="background:rgba(255,255,255,0.2);border:none;color:#fff;font-size:28px;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)';" onmouseout="this.style.background='rgba(255,255,255,0.2)';">&times;</button>
                        </div>
                        <div class="modal-body" id="breakdownModalBody" style="padding:24px;background:#fff;border-radius:0 0 12px 12px;">${contentHtml}</div>
                    </div>
                </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            modal = document.getElementById('breakdownModal');
            // attach close
            document.getElementById('breakdownModalClose')?.addEventListener('click', function() { modal.remove(); document.body.style.overflow=''; });
        } else {
            document.getElementById('breakdownModalBody').innerHTML = contentHtml;
        }
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    });

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

    const confirmBookingBtn = document.getElementById('confirmBookingBtn');

    // Removed real-time validation - warning will only show on button click

    // === Confirm Booking button - Show confirmation modal ===
    document.getElementById("confirmBookingBtn").addEventListener("click", function () {
        const termsCheckbox = document.getElementById('accept-terms');
        const termsAccepted = termsCheckbox ? termsCheckbox.checked : false;
        const preferredDateInput = document.getElementById('preferred_installation_date');
        const preferredDate = preferredDateInput ? preferredDateInput.value : '';
        
        let firstErrorElement = null;
        let errorMessage = "Please complete all required fields.";

        // Reset errors and warnings
        document.querySelectorAll('.form-group input, .form-group select').forEach(el => el.style.borderColor = '#ccc');
        document.querySelectorAll('.inline-error').forEach(el => el.style.display = 'none');
        
        // Get or create validation notice element (declare once at function level)
        let validationNotice = document.getElementById('booking-validation-notice');
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

        // Validate Preferred Ocular Visit Date (required for booking)
        if (!firstErrorElement && !preferredDate) {
            const errorDiv = document.getElementById('installation-date-error');
            if (errorDiv) errorDiv.style.display = 'block';
            document.getElementById('open-calendar-btn').style.borderColor = 'red';
            if (!firstErrorElement) {
                firstErrorElement = document.getElementById('open-calendar-btn');
                errorMessage = "Please select your preferred ocular visit date.";
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
            
            // Check shipping fields (excluding Preferred Ocular Visit Date field)
            const shippingInputs = document.querySelectorAll('#profileForm input[required], #profileForm select[required]');
            shippingInputs.forEach(input => {
                const isHiddenAddressField = input.closest('#shipping-address-fields')?.style.display === 'none';
                // Skip if hidden or if it's the Preferred Ocular Visit Date field
                if (isHiddenAddressField || input.id === 'preferred_installation_date' || input.name === 'preferred_installation_date') {
                    return;
                }
                
                if (!input.value.trim()) {
                    const label = input.closest('.form-group')?.querySelector('label')?.textContent.replace(/\*/g, '').trim() || 'field';
                    // Filter out any label that contains "Preferred Ocular Visit Date"
                    if (label && !label.toLowerCase().includes('preferred ocular visit date')) {
                        missingFields.add(label);
                    }
                }
            });
            
            // Check Preferred Ocular Visit Date (only once, explicitly)
            if (!preferredDate) {
                missingFields.add('Preferred Ocular Visit Date');
            }
            
            // Check terms
            if (!termsAccepted) {
                missingFields.add('Terms and Conditions acceptance');
            }
            
            // Show validation notice (reuse existing variable)
            if (!validationNotice) {
                validationNotice = document.getElementById('booking-validation-notice');
            }
            if (!validationNotice) {
                // Create validation notice element if it doesn't exist
                validationNotice = document.createElement('div');
                validationNotice.id = 'booking-validation-notice';
                validationNotice.style.cssText = 'margin-top: 15px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; color: #856404;';
                const buttonParent = confirmBookingBtn.parentElement;
                if (buttonParent) {
                    // Prefer inserting the validation notice immediately after the Terms
                    // checkbox so the order is: [Terms checkbox] -> [validation notice] -> [Review Booking button]
                    const termsEl = buttonParent.querySelector('.terms');
                    try {
                        if (termsEl && typeof termsEl.insertAdjacentElement === 'function') {
                            termsEl.insertAdjacentElement('afterend', validationNotice);
                        } else if (termsEl) {
                            // fallback: append after terms
                            buttonParent.insertBefore(validationNotice, termsEl.nextSibling);
                        } else {
                            // final fallback: put at top
                            buttonParent.insertBefore(validationNotice, buttonParent.firstChild);
                        }
                    } catch (ex) {
                        // In case insertAdjacentElement fails in older environments, fallback
                        if (termsEl) buttonParent.insertBefore(validationNotice, termsEl.nextSibling);
                        else buttonParent.insertBefore(validationNotice, buttonParent.firstChild);
                    }
                }
            }
            
            // Update notice with unique fields
            const missingFieldsArray = Array.from(missingFields);
            if (missingFieldsArray.length > 0) {
                validationNotice.innerHTML = '<strong>⚠ Please complete the following required fields before confirming booking:</strong><ul style="margin: 10px 0 0 20px; padding-left: 20px;">' + 
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

        // === Confirm Booking button - Actually confirm the booking ===
        confirmOrderBtn.addEventListener("click", function () {
            const btn = this;
            const defaultConfirmLabel = 'Confirm Booking';
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

        // Add terms and SELECTED CART IDS
        const preferredDateInput = form.querySelector("input[name='preferred_installation_date']");
        if (preferredDateInput && preferredDateInput.value) {
            formData.append('preferred_installation_date', preferredDateInput.value);
        }
        
        // Add preferred time
        const preferredTimeSelect = form.querySelector("select[name='preferred_time']");
        if (preferredTimeSelect && preferredTimeSelect.value) {
            formData.append('preferred_time', preferredTimeSelect.value);
        }
        
        // Add selected cart IDs or beginner product info
        if (IS_BEGINNER_BOOKING && window.beginnerBookingProduct) {
            // Beginner booking: send product info instead of cart IDs
            formData.append('is_beginner_booking', 'true');
            formData.append('beginner_product_id', window.beginnerBookingProduct.id);
            formData.append('beginner_product_name', window.beginnerBookingProduct.name);
            formData.append('beginner_product_category', window.beginnerBookingProduct.category);
            formData.append('beginner_product_subcategory', window.beginnerBookingProduct.subcategory);
            // Don't need cart IDs for beginner booking
            formData.append('selected_cart_ids', '');
            console.log('Beginner booking - Product:', window.beginnerBookingProduct);
        } else {
            formData.append('selected_cart_ids', SELECTED_CART_IDS);
        }
        formData.append('terms_accepted', termsAccepted ? 'true' : 'false');
        
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

        fetch(BASE_URL + 'shopcon/confirm_booking', {
            method: 'POST',
            body: formData,
            signal: controller.signal,
            credentials: 'include' // Ensure session cookies are sent
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
                // Show success message briefly before redirect
                console.log('Redirecting to:', data.redirect_url);
                // Fallback: if server did not provide a redirect, send to complete booking page for site-assessment
                let redirectUrl = data.redirect_url;
                if (!redirectUrl) {
                    const isSite = data.is_site_assessment || data.order_type === 'site-assessment' || data.order_type === 'site_assessment';
                    const orderId = data.order_id || data.OrderID || data.orderID || '';
                    if (isSite) {
                        redirectUrl = BASE_URL + 'complete_booking' + (orderId ? ('?order=' + orderId) : '');
                    } else {
                        redirectUrl = BASE_URL + 'complete' + (orderId ? ('?order=' + orderId) : '');
                    }
                }
                window.location.href = redirectUrl;
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

}); // End of $(document).ready

</script>