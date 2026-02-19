<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/checkout_style.css'); ?>">
<script>
    const BASE_URL = "<?= base_url(); ?>";
    
    // Get selected cart IDs from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const SELECTED_CART_IDS = urlParams.get('selected') || '';

    // Stage payment mode flag (from Track Order Pay Now)
    const IS_STAGE_PAYMENT = <?= !empty($stage_payment) ? 'true' : 'false' ?>;
    
    // Global variable to store loaded cart items for modal reuse
    window.loadedCartItems = null;
    window.loadedCartSummary = null;
    
    <?php if (!empty($stage_payment)): ?>
    // Stage payment data
    window.stagePaymentData = {
        order_id: "<?= $stage_payment['order_id'] ?>",
        order_number: "<?= htmlspecialchars($stage_payment['order_number']) ?>",
        stage: "<?= $stage_payment['stage'] ?>",
        stage_label: "<?= htmlspecialchars($stage_payment['stage_label']) ?>",
        amount: <?= $stage_payment['amount'] ?>,
        items: <?= json_encode(array_map(function($item) {
            return [
                'ProductName' => $item->ProductName ?? '',
                'ImageUrl' => $item->ImageUrl ?? '',
                'Category' => $item->Category ?? '',
                'Subcategory' => $item->Subcategory ?? '',
                'PriceMin' => $item->PriceMin ?? null,
                'PriceMax' => $item->PriceMax ?? null,
                'Quantity' => intval($item->Quantity ?? 1),
                'Customization' => $item->Customization ?? '',
                'DesignRef' => $item->DesignRef ?? '',
                'Dimensions' => $item->Dimensions ?? ''
            ];
        }, $stage_payment['items'])) ?>
    };
    <?php endif; ?>
</script>

<!-- Make the Order Summary modal match Review Booking Details modal sizing and typography -->
<style>
    /* Make the Order Summary / Confirm modal wider */
    #orderConfirmModal .modal-content {
        max-width: 1100px !important;
        width: 95% !important;
    }

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

    #orderConfirmModal .product-thumb {
        width: 60px !important;
        height: 60px !important;
        object-fit: cover !important;
        border-radius: 6px !important;
    }

    #orderConfirmModal .product-info {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    #orderConfirmModal .product-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    #orderConfirmModal .product-name {
        font-size: 0.98rem;
        font-weight: 600;
        color: #1976d2;
        line-height: 1.3;
    }

    #orderConfirmModal .custom-tag,
    #orderConfirmModal .confirm-custom-tag,
    #orderConfirmModal .view-design-text {
        font-size: 11px !important;
    }

    #orderConfirmModal .custom-details,
    #orderConfirmModal .confirm-tags-box,
    #orderConfirmModal .confirm-custom-tag {
        font-size: 0.98rem !important;
    }

    #orderConfirmModal .confirm-items-table td,
    #orderConfirmModal .confirm-items-table th {
        font-size: 0.94rem;
        vertical-align: middle;
    }

    #orderConfirmModal .confirm-items-container,
    #orderConfirmModal .confirm-items-table {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    #orderConfirmModal .design-thumbnail-wrapper,
    #orderConfirmModal .design-thumbnail-wrapper img,
    #orderConfirmModal .product-thumb {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        padding: 0 !important;
    }

    #orderConfirmModal .modal-footer .btn-cancel,
    #orderConfirmModal .modal-footer .btn-confirm-order {
        font-size: inherit;
    }
</style>


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
    <div class="progress-nav">
        <?php $origin = isset($payment_origin) ? $payment_origin : 'cart'; ?>
        <?php if ($origin === 'stage_payment'): ?>
            <div class="step completed">Track Order</div>
            <div class="divider"></div>
            <div class="step active">Payment</div>
            <div class="divider"></div>
            <div class="step">Complete</div>
        <?php else: ?>
            <div class="step completed"><?= ($origin === 'review') ? 'Review' : 'Cart' ?></div>
            <div class="divider"></div>
            <div class="step active">Order & Payment</div>
            <div class="divider"></div>
            <div class="step">Complete</div>
        <?php endif; ?>
    </div>
</div>


<main>

    <!-- Title outside sections -->
    <div class="info-title">
        <h2>Order & Payment Details</h2>
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
                        <input type="text" name="firstname" value="<?= htmlspecialchars(!empty($stage_payment) ? ($stage_payment['customer']['first_name'] ?? $user->First_Name ?? '') : ($user->First_Name ?? '')) ?>"
                            placeholder="Enter your first name" required <?= !empty($stage_payment) ? 'readonly style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middlename" value="<?= htmlspecialchars($user->Middle_Name ?? '') ?>"
                            placeholder="Enter your middle name (optional)" <?= !empty($stage_payment) ? 'readonly style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label>Last Name <span style="color: red;">*</span></label>
                        <input type="text" name="lastname" value="<?= htmlspecialchars(!empty($stage_payment) ? ($stage_payment['customer']['last_name'] ?? $user->Last_Name ?? '') : ($user->Last_Name ?? '')) ?>"
                            placeholder="Enter your last name" required <?= !empty($stage_payment) ? 'readonly style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email address <span style="color: red;">*</span></label>
                        <input type="email" name="email" value="<?= htmlspecialchars(!empty($stage_payment) ? ($stage_payment['customer']['email'] ?? $user->Email) : $user->Email) ?>"
                            placeholder="Enter your email address" required <?= !empty($stage_payment) ? 'readonly style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label>Phone number <span style="color: red;">*</span></label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars(!empty($stage_payment) ? ($stage_payment['customer']['phone'] ?? $user->PhoneNum) : $user->PhoneNum) ?>" maxlength="11"
                            placeholder="Enter your phone number" required <?= !empty($stage_payment) ? 'readonly style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                    </div>
                </div>
                
                <!-- Saved Address Selector (Hidden for stage payment) -->
                <?php if (!empty($stage_payment)): ?>
                    <!-- Stage Payment - Show full address as readonly text input -->
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Address <span style="color: red;">*</span></label>
                            <input type="text" name="shipping_address" value="<?= htmlspecialchars($stage_payment['address_parts']['full_address'] ?? '') ?>" readonly style="background: #f5f5f5; cursor: not-allowed;">
                        </div>
                    </div>
                    <!-- Hidden fields for barangay, city and province so they appear in the order review modal -->
                    <input type="hidden" name="barangay" value="<?= htmlspecialchars($stage_payment['address_parts']['barangay'] ?? '') ?>">
                    <input type="hidden" name="city" value="<?= htmlspecialchars($stage_payment['address_parts']['city'] ?? '') ?>">
                    <input type="hidden" name="province" value="<?= htmlspecialchars($stage_payment['address_parts']['province'] ?? '') ?>">
                    <input type="hidden" name="zipcode" value="<?= htmlspecialchars($stage_payment['address_parts']['zipcode'] ?? '') ?>">
                    <input type="hidden" name="country" value="<?= htmlspecialchars($stage_payment['address_parts']['country'] ?? 'Philippines') ?>">
                <?php elseif (isset($all_addresses) && !empty($all_addresses)): ?>
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
                
                <!-- Shipping Address Form Fields (hidden for stage payment and when saved addresses exist) -->
                <div id="shipping-address-fields" style="<?= !empty($stage_payment) ? 'display: none;' : ((isset($all_addresses) && !empty($all_addresses)) ? 'display: none;' : '') ?>">
                
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
                            <select name="billing_country" id="billing-country" required>
                                <option value="">Select Country</option>
                                <option value="Afghanistan">Afghanistan</option>
                                <option value="Albania">Albania</option>
                                <option value="Algeria">Algeria</option>
                                <option value="Andorra">Andorra</option>
                                <option value="Angola">Angola</option>
                                <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Armenia">Armenia</option>
                                <option value="Australia">Australia</option>
                                <option value="Austria">Austria</option>
                                <option value="Azerbaijan">Azerbaijan</option>
                                <option value="Bahamas">Bahamas</option>
                                <option value="Bahrain">Bahrain</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="Barbados">Barbados</option>
                                <option value="Belarus">Belarus</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Belize">Belize</option>
                                <option value="Benin">Benin</option>
                                <option value="Bhutan">Bhutan</option>
                                <option value="Bolivia">Bolivia</option>
                                <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                <option value="Botswana">Botswana</option>
                                <option value="Brazil">Brazil</option>
                                <option value="Brunei">Brunei</option>
                                <option value="Bulgaria">Bulgaria</option>
                                <option value="Burkina Faso">Burkina Faso</option>
                                <option value="Burundi">Burundi</option>
                                <option value="Cabo Verde">Cabo Verde</option>
                                <option value="Cambodia">Cambodia</option>
                                <option value="Cameroon">Cameroon</option>
                                <option value="Canada">Canada</option>
                                <option value="Central African Republic">Central African Republic</option>
                                <option value="Chad">Chad</option>
                                <option value="Chile">Chile</option>
                                <option value="China">China</option>
                                <option value="Colombia">Colombia</option>
                                <option value="Comoros">Comoros</option>
                                <option value="Congo, Democratic Republic of the">Congo, Democratic Republic of the</option>
                                <option value="Congo, Republic of the">Congo, Republic of the</option>
                                <option value="Costa Rica">Costa Rica</option>
                                <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                                <option value="Croatia">Croatia</option>
                                <option value="Cuba">Cuba</option>
                                <option value="Cyprus">Cyprus</option>
                                <option value="Czech Republic">Czech Republic</option>
                                <option value="Denmark">Denmark</option>
                                <option value="Djibouti">Djibouti</option>
                                <option value="Dominica">Dominica</option>
                                <option value="Dominican Republic">Dominican Republic</option>
                                <option value="Ecuador">Ecuador</option>
                                <option value="Egypt">Egypt</option>
                                <option value="El Salvador">El Salvador</option>
                                <option value="Equatorial Guinea">Equatorial Guinea</option>
                                <option value="Eritrea">Eritrea</option>
                                <option value="Estonia">Estonia</option>
                                <option value="Eswatini">Eswatini</option>
                                <option value="Ethiopia">Ethiopia</option>
                                <option value="Fiji">Fiji</option>
                                <option value="Finland">Finland</option>
                                <option value="France">France</option>
                                <option value="Gabon">Gabon</option>
                                <option value="Gambia">Gambia</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Germany">Germany</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Greece">Greece</option>
                                <option value="Grenada">Grenada</option>
                                <option value="Guatemala">Guatemala</option>
                                <option value="Guinea">Guinea</option>
                                <option value="Guinea-Bissau">Guinea-Bissau</option>
                                <option value="Guyana">Guyana</option>
                                <option value="Haiti">Haiti</option>
                                <option value="Honduras">Honduras</option>
                                <option value="Hungary">Hungary</option>
                                <option value="Iceland">Iceland</option>
                                <option value="India">India</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Iran">Iran</option>
                                <option value="Iraq">Iraq</option>
                                <option value="Ireland">Ireland</option>
                                <option value="Israel">Israel</option>
                                <option value="Italy">Italy</option>
                                <option value="Jamaica">Jamaica</option>
                                <option value="Japan">Japan</option>
                                <option value="Jordan">Jordan</option>
                                <option value="Kazakhstan">Kazakhstan</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Kiribati">Kiribati</option>
                                <option value="Korea, North">Korea, North</option>
                                <option value="Korea, South">Korea, South</option>
                                <option value="Kuwait">Kuwait</option>
                                <option value="Kyrgyzstan">Kyrgyzstan</option>
                                <option value="Laos">Laos</option>
                                <option value="Latvia">Latvia</option>
                                <option value="Lebanon">Lebanon</option>
                                <option value="Lesotho">Lesotho</option>
                                <option value="Liberia">Liberia</option>
                                <option value="Libya">Libya</option>
                                <option value="Liechtenstein">Liechtenstein</option>
                                <option value="Lithuania">Lithuania</option>
                                <option value="Luxembourg">Luxembourg</option>
                                <option value="Madagascar">Madagascar</option>
                                <option value="Malawi">Malawi</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Maldives">Maldives</option>
                                <option value="Mali">Mali</option>
                                <option value="Malta">Malta</option>
                                <option value="Marshall Islands">Marshall Islands</option>
                                <option value="Mauritania">Mauritania</option>
                                <option value="Mauritius">Mauritius</option>
                                <option value="Mexico">Mexico</option>
                                <option value="Micronesia">Micronesia</option>
                                <option value="Moldova">Moldova</option>
                                <option value="Monaco">Monaco</option>
                                <option value="Mongolia">Mongolia</option>
                                <option value="Montenegro">Montenegro</option>
                                <option value="Morocco">Morocco</option>
                                <option value="Mozambique">Mozambique</option>
                                <option value="Myanmar">Myanmar</option>
                                <option value="Namibia">Namibia</option>
                                <option value="Nauru">Nauru</option>
                                <option value="Nepal">Nepal</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="Nicaragua">Nicaragua</option>
                                <option value="Niger">Niger</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="North Macedonia">North Macedonia</option>
                                <option value="Norway">Norway</option>
                                <option value="Oman">Oman</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Palau">Palau</option>
                                <option value="Panama">Panama</option>
                                <option value="Papua New Guinea">Papua New Guinea</option>
                                <option value="Paraguay">Paraguay</option>
                                <option value="Peru">Peru</option>
                                <option value="Philippines" selected>Philippines</option>
                                <option value="Poland">Poland</option>
                                <option value="Portugal">Portugal</option>
                                <option value="Qatar">Qatar</option>
                                <option value="Romania">Romania</option>
                                <option value="Russia">Russia</option>
                                <option value="Rwanda">Rwanda</option>
                                <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                <option value="Saint Lucia">Saint Lucia</option>
                                <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                                <option value="Samoa">Samoa</option>
                                <option value="San Marino">San Marino</option>
                                <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                <option value="Saudi Arabia">Saudi Arabia</option>
                                <option value="Senegal">Senegal</option>
                                <option value="Serbia">Serbia</option>
                                <option value="Seychelles">Seychelles</option>
                                <option value="Sierra Leone">Sierra Leone</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Slovakia">Slovakia</option>
                                <option value="Slovenia">Slovenia</option>
                                <option value="Solomon Islands">Solomon Islands</option>
                                <option value="Somalia">Somalia</option>
                                <option value="South Africa">South Africa</option>
                                <option value="South Sudan">South Sudan</option>
                                <option value="Spain">Spain</option>
                                <option value="Sri Lanka">Sri Lanka</option>
                                <option value="Sudan">Sudan</option>
                                <option value="Suriname">Suriname</option>
                                <option value="Sweden">Sweden</option>
                                <option value="Switzerland">Switzerland</option>
                                <option value="Syria">Syria</option>
                                <option value="Taiwan">Taiwan</option>
                                <option value="Tajikistan">Tajikistan</option>
                                <option value="Tanzania">Tanzania</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Timor-Leste">Timor-Leste</option>
                                <option value="Togo">Togo</option>
                                <option value="Tonga">Tonga</option>
                                <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                <option value="Tunisia">Tunisia</option>
                                <option value="Turkey">Turkey</option>
                                <option value="Turkmenistan">Turkmenistan</option>
                                <option value="Tuvalu">Tuvalu</option>
                                <option value="Uganda">Uganda</option>
                                <option value="Ukraine">Ukraine</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Uruguay">Uruguay</option>
                                <option value="Uzbekistan">Uzbekistan</option>
                                <option value="Vanuatu">Vanuatu</option>
                                <option value="Vatican City">Vatican City</option>
                                <option value="Venezuela">Venezuela</option>
                                <option value="Vietnam">Vietnam</option>
                                <option value="Yemen">Yemen</option>
                                <option value="Zambia">Zambia</option>
                                <option value="Zimbabwe">Zimbabwe</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Region <span style="color: red;">*</span></label>
                            <input type="text" name="billing_region" id="billing-region" value="" placeholder="Enter region" required>
                        </div>
                        <div class="form-group">
                            <label>Province <span style="color: red;">*</span></label>
                            <input type="text" name="billing_province" id="billing-province" value="" placeholder="Enter province" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>City/Municipality <span style="color: red;">*</span></label>
                            <input type="text" name="billing_city" id="billing-city" value="" placeholder="Enter city/municipality" required>
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

                <?php if (!empty($stage_payment)): ?>
                <!-- ========== STAGE PAYMENT SUMMARY (from Track Order Pay Now) ========== -->
                <div style="margin-bottom: 16px; padding: 14px; background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 4px;">
                    <div style="font-size: 0.82rem; color: #6b7280; margin-bottom: 4px;">Order #<?= htmlspecialchars($stage_payment['order_number']) ?></div>
                    <div style="font-size: 1rem; font-weight: 700; color: #1e3a8a;"><?= htmlspecialchars($stage_payment['stage_label']) ?></div>
                </div>

                <!-- Order Items -->
                <div id="summary-items-list" style="max-height: 350px; overflow-y: auto; margin-bottom: 15px; padding-bottom: 10px;">
                    <?php if (!empty($stage_payment['items'])): ?>
                        <?php foreach ($stage_payment['items'] as $item): ?>
                            <?php
                                // Parse image URL (may be JSON array)
                                $imageUrl = $item->ImageUrl ?? '';
                                if (!empty($imageUrl) && strpos($imageUrl, '[') === 0) {
                                    $parsed = json_decode($imageUrl, true);
                                    if (is_array($parsed) && !empty($parsed)) $imageUrl = $parsed[0];
                                }
                                // Build full URL: check if absolute or starts with uploads/assets
                                if (!empty($imageUrl)) {
                                    if (strpos($imageUrl, 'http') === 0) {
                                        // Already absolute URL
                                        $imageUrl = $imageUrl;
                                    } elseif (strpos($imageUrl, 'uploads/') === 0 || strpos($imageUrl, 'assets/') === 0) {
                                        // Has path prefix
                                        $imageUrl = base_url($imageUrl);
                                    } else {
                                        // Just filename - assume uploads/products/
                                        $imageUrl = base_url('uploads/products/' . basename($imageUrl));
                                    }
                                } else {
                                    $imageUrl = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y1ZjVmNSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwsc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==';
                                }
                                
                                // Get price range from item - prefer product table data over order_items
                                $priceMin = $item->PriceMin ?? null;
                                $priceMax = $item->PriceMax ?? null;
                                $priceDisplay = 'Price TBD after assessment';
                                if ($priceMin && $priceMax) {
                                    $priceDisplay = '₱' . number_format((float)$priceMin, 0) . ' - ₱' . number_format((float)$priceMax, 0);
                                } elseif ($priceMin) {
                                    $priceDisplay = 'Starting at ₱' . number_format((float)$priceMin, 0);
                                }
                                
                                $quantity = intval($item->Quantity ?? 1);
                            ?>
                        <div class="summary-item-row" style="display: flex; gap: 15px; padding: 15px; border: 1px solid #f0f0f0; border-radius: 10px; margin-bottom: 12px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.02); align-items: center;">
                            <img src="<?= $imageUrl ?>" alt="<?= htmlspecialchars($item->ProductName ?? 'Product') ?>" style="width: 90px; height: 90px; object-fit: cover; border-radius: 10px; border: 2px solid #e3f2fd; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y1ZjVmNSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwsc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='">
                            <div class="summary-item-info" style="flex-grow: 1;">
                                <h4 style="margin: 0 0 8px 0; color: #1976d2; font-size: 1.1rem; font-weight: 600;"><?= htmlspecialchars($item->ProductName ?? 'Custom Glass Product') ?></h4>
                                <p style="margin: 0 0 6px 0; color: #28a745; font-size: 1rem; font-weight: 600;"><?= $priceDisplay ?></p>
                                <?php if (!empty($item->Category)): ?>
                                    <p style="margin: 0 0 2px 0; color: #666; font-size: 0.9rem;"><?= htmlspecialchars($item->Category) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item->Subcategory)): ?>
                                    <p style="margin: 0 0 4px 0; color: #888; font-size: 0.85rem; font-style: italic;"><?= htmlspecialchars($item->Subcategory) ?></p>
                                <?php endif; ?>
                                <p style="margin: 0; color: #666; font-size: 0.9rem;">Qty: <?= $quantity ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; color: #888; padding: 10px;">Order items</div>
                    <?php endif; ?>
                </div>

                <div class="summary-totals-box" style="padding-top: 15px;">
                    <p class="total" style="font-size: 1.15rem;"><span>Amount:</span> <span id="summary-total">₱<?= number_format($stage_payment['amount'], 2) ?></span></p>
                </div>

                <!-- Hidden fields for stage payment processing -->
                <input type="hidden" id="stage-payment-order-id" value="<?= $stage_payment['order_id'] ?>">
                <input type="hidden" id="stage-payment-stage" value="<?= $stage_payment['stage'] ?>">
                <input type="hidden" id="stage-payment-amount" value="<?= $stage_payment['amount'] ?>">
                <!-- END STAGE PAYMENT SUMMARY -->

                <?php else: ?>
                <!-- ========== NORMAL CART SUMMARY ========== -->
                <!-- Itemized List -->
                <div id="summary-items-list" style="max-height: 350px; overflow-y: auto; margin-bottom: 15px; padding-bottom: 10px;">
                    <!-- Items will be dynamically populated -->
                    <div style="text-align: center; color: #888; padding: 10px;">Loading items...</div>
                </div>

                <div class="summary-totals-box" style="padding-top: 15px;">
                    <p><span>Subtotal:</span> <span id="summary-subtotal">₱0.00</span></p>
                    <!-- Shipping fee removed per requirements -->
                    <p class="total"><span>Total:</span> <span id="summary-total">₱0.00</span></p>
                </div>
                <!-- END NORMAL CART SUMMARY -->
                <?php endif; ?>
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

                <div class="terms" style="margin-top: 20px; margin-bottom: 15px;">
                    <input type="checkbox" id="accept-terms">
                    <label for="accept-terms">
                        I have read and agree to Glassify's
                        <a href="<?php echo base_url('terms_order'); ?>">Terms and Conditions of Purchase</a>
                    </label>
                </div>

                <!-- Removed <a> and kept only button -->
                <button class="placeOrder-btn" id="placeOrderBtn">Review Order</button>
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
        // Show shipping row only when shipping > 0.00
        document.addEventListener('DOMContentLoaded', function() {
            const row = document.getElementById('summary-shipping-row');
            const shipEl = document.getElementById('summary-shipping');
            if (row && shipEl) {
                let val = shipEl.textContent.replace(/[^0-9.\-]+/g,'');
                val = parseFloat(val) || 0;
                if (val > 0) row.style.display = 'block';
                else row.style.display = 'none';
            }
            // Observe changes to shipping value and toggle visibility
            if (row && shipEl && window.MutationObserver) {
                const obs = new MutationObserver(() => {
                    let v = shipEl.textContent.replace(/[^0-9.\-]+/g,'');
                    v = parseFloat(v) || 0;
                    row.style.display = (v > 0) ? 'block' : 'none';
                });
                obs.observe(shipEl, { childList: true, characterData: true, subtree: true });
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
      <h2>📋 Review Order Details</h2>
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

      <!-- Billing Details -->
      <div class="confirm-section">
        <h4 class="confirm-section-title">
          <span class="icon">💳</span> Billing Details
        </h4>
        <div class="confirm-info-grid">
          <div class="confirm-info-item">
            <span class="info-label">Name</span>
            <span class="info-value" id="confirm-billing-name"></span>
          </div>
          <div class="confirm-info-item">
            <span class="info-label">Email</span>
            <span class="info-value" id="confirm-billing-email"></span>
          </div>
          <div class="confirm-info-item">
            <span class="info-label">Phone</span>
            <span class="info-value" id="confirm-billing-phone"></span>
          </div>
          <div class="confirm-info-item full-width">
            <span class="info-label">Billing Address</span>
            <span class="info-value" id="confirm-billing-address"></span>
          </div>
        </div>
      </div>

      <!-- Special Instructions / Note -->
      <div class="confirm-section">
        <h4 class="confirm-section-title">
          <span class="icon">📝</span> Special Instructions / Note
        </h4>
        <div class="confirm-info-grid">
          <div class="confirm-info-item full-width">
            <span class="info-value" id="confirm-special-note" style="padding: 12px; background: #f8f9fa; border-radius: 6px; display: block; min-height: 40px; color: #666; font-style: italic;">No special instructions provided</span>
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
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody id="confirm-items-body">
              <!-- Items will be dynamically populated -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal-footer confirm-footer">
      <button class="btn-cancel" id="cancelOrderBtn">Cancel</button>
      <button class="btn-confirm-order" id="confirmOrderBtn">Place Order</button>
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
    </div>
</div>

<script>
// =============================
// DESIGN PREVIEW MODAL
// =============================
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

// Close design modal when clicking overlay or pressing Escape
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('designPreviewModal')?.querySelector('.modal-overlay')?.addEventListener('click', closeDesignModal);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('designPreviewModal')?.classList.contains('show')) {
            closeDesignModal();
        }
    });
});

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
        // In stage payment mode, summary is already server-rendered — skip AJAX loading
        if (IS_STAGE_PAYMENT) {
            return;
        }
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
                    
                    // Store globally for modal reuse
                    window.loadedCartItems = items;
                    window.loadedCartSummary = summary;

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
                                
                                // Parse image if it's a JSON array
                                let itemImage = item.image || '';
                                try {
                                    if (typeof itemImage === 'string' && itemImage.trim().startsWith('[')) {
                                        const parsed = JSON.parse(itemImage);
                                        if (Array.isArray(parsed) && parsed.length > 0) itemImage = parsed[0];
                                    }
                                } catch (e) { /* ignore */ }
                                
                                // Build full image URL
                                if (itemImage) {
                                    if (itemImage.startsWith('http')) {
                                        // Already absolute
                                    } else if (itemImage.startsWith('uploads/') || itemImage.startsWith('assets/')) {
                                        itemImage = BASE_URL + itemImage;
                                    } else {
                                        // Just filename - assume uploads/products/
                                        itemImage = BASE_URL + 'uploads/products/' + itemImage.split('/').pop();
                                    }
                                } else {
                                    itemImage = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y1ZjVmNSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwsc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==';
                                }
                                
                                // Build price display
                                let priceDisplay = 'Price TBD after assessment';
                                if (item.price_min && item.price_max) {
                                    priceDisplay = `₱${parseFloat(item.price_min).toLocaleString()} - ₱${parseFloat(item.price_max).toLocaleString()}`;
                                } else if (item.price) {
                                    priceDisplay = `Starting at ₱${parseFloat(item.price).toLocaleString()}`;
                                }
                                
                                const categoryText = item.category || item.Category || '';
                                const subcategoryText = item.subcategory || item.Subcategory || '';
                                
                                itemDiv.innerHTML = `
                                    <img src="${itemImage}" alt="${item.description}" style="width: 90px; height: 90px; object-fit: cover; border-radius: 10px; border: 2px solid #e3f2fd; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y1ZjVmNSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwsc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='">
                                    <div class="summary-item-info" style="flex-grow: 1;">
                                        <h4 style="margin: 0 0 8px 0; color: #1976d2; font-size: 1.1rem; font-weight: 600;">${item.description}</h4>
                                        <p style="margin: 0 0 6px 0; color: #28a745; font-size: 1rem; font-weight: 600;">${priceDisplay}</p>
                                        ${categoryText ? `<p style="margin: 0 0 2px 0; color: #666; font-size: 0.9rem;">${categoryText}</p>` : ''}
                                        ${subcategoryText ? `<p style="margin: 0; color: #888; font-size: 0.85rem; font-style: italic;">${subcategoryText}</p>` : ''}
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
                    
                    // All orders now follow unified flow - show notice about ocular visit
                    const paymentSectionEl = document.querySelector('.payment-section');
                    if (paymentSectionEl) {
                        let notice = document.getElementById('unified-order-notice');
                        if (!notice) {
                            notice = document.createElement('div');
                            notice.id = 'unified-order-notice';
                            notice.style.cssText = 'padding:12px; background:#e3f2fd; border:1px solid #90caf9; border-radius:6px; color:#1565c0; margin-bottom:12px;';
                            notice.innerHTML = '<strong>📋 Order Process:</strong> All orders require an ocular visit for site assessment before production. Final pricing will be confirmed after the site visit.';
                            paymentSectionEl.insertBefore(notice, paymentSectionEl.firstChild);
                        }
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
                        const shippingInput = form.querySelector(`[name='${field}']`);
                        const billingInput = billingForm.querySelector(`[name='billing_${field}']`);
                        if (shippingInput && billingInput) {
                            billingInput.value = shippingInput.value || '';
                        }
                    });

                    // Prefer saved-address dropdown data if present (saved addresses may keep the region/province/city)
                    const savedDropdown = document.getElementById('saved-address-dropdown');
                    const useDifferentShipping = document.getElementById('use-different-shipping-address')?.checked || false;
                    let sd_region = '';
                    let sd_province = '';
                    let sd_city = '';
                    if (savedDropdown && savedDropdown.value && !useDifferentShipping) {
                        try {
                            const selectedOpt = savedDropdown.options[savedDropdown.selectedIndex];
                            const addr = JSON.parse(selectedOpt.getAttribute('data-address') || '{}');
                            sd_region = addr.Region || addr.region || '';
                            sd_province = addr.Province || addr.province || '';
                            sd_city = addr.City || addr.city || '';
                            // Also copy unit/street/subdivision/barangay/zipcode if available
                            if (addr.UnitHouseNumber || addr.Unit) {
                                const billingUnit = billingForm.querySelector("[name='billing_unit_house_number']");
                                if (billingUnit) billingUnit.value = addr.UnitHouseNumber || addr.Unit || '';
                            }
                            if (addr.Street) {
                                const billingStreet = billingForm.querySelector("[name='billing_street']");
                                if (billingStreet) billingStreet.value = addr.Street || '';
                            }
                            if (addr.Subdivision) {
                                const billingSub = billingForm.querySelector("[name='billing_subdivision']");
                                if (billingSub) billingSub.value = addr.Subdivision || '';
                            }
                            if (addr.Barangay) {
                                const billingBarangay = billingForm.querySelector("[name='billing_barangay']");
                                if (billingBarangay) billingBarangay.value = addr.Barangay || '';
                            }
                            if (addr.ZipCode || addr.Zipcode) {
                                const billingZip = billingForm.querySelector("[name='billing_zipcode']");
                                if (billingZip) billingZip.value = addr.ZipCode || addr.Zipcode || '';
                            }
                        } catch (e) {
                            console.error('Error parsing saved address for same-billing copy', e);
                        }
                    }

                    // Copy region, province, city from shipping to billing (work with inputs or selects)
                    const shippingRegion = form.querySelector("[name='region']");
                    const shippingProvince = form.querySelector("[name='province']");
                    const shippingCity = form.querySelector("[name='city']");
                    const billingRegion = billingForm.querySelector("[name='billing_region']");
                    const billingProvince = billingForm.querySelector("[name='billing_province']");
                    const billingCity = billingForm.querySelector("[name='billing_city']");

                    if (billingRegion) {
                        billingRegion.value = sd_region || (shippingRegion ? shippingRegion.value || '' : '');
                    }
                    if (billingProvince) {
                        billingProvince.value = sd_province || (shippingProvince ? shippingProvince.value || '' : '');
                    }
                    if (billingCity) {
                        billingCity.value = sd_city || (shippingCity ? shippingCity.value || '' : '');
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
        const phone = form.querySelector("input[name='phone']")?.value || '';

        // If a saved address is selected and 'use-different-shipping-address' is not checked,
        // prefer values from the selected option's data-address payload so City/Province are accurate.
        const savedDropdown = document.getElementById('saved-address-dropdown');
        const useDifferent = document.getElementById('use-different-shipping-address')?.checked || false;

        let unitHouseNumber = '';
        let street = '';
        let subdivision = '';
        let barangay = '';
        let city = '';
        let province = '';
        let region = '';
        let zipcode = '';
        let country = 'Philippines';

        if (savedDropdown && savedDropdown.value && !useDifferent) {
            try {
                const selectedOpt = savedDropdown.options[savedDropdown.selectedIndex];
                const addr = JSON.parse(selectedOpt.getAttribute('data-address') || '{}');
                unitHouseNumber = addr.UnitHouseNumber || addr.Unit || '';
                street = addr.Street || '';
                subdivision = addr.Subdivision || '';
                barangay = addr.Barangay || '';
                city = addr.City || '';
                province = addr.Province || '';
                region = addr.Region || '';
                zipcode = addr.ZipCode || addr.Zipcode || '';
                country = addr.Country || 'Philippines';
            } catch (e) {
                // fallback to form fields below
            }
        }

        // If not populated from saved address, read from form fields (handles selects and inputs)
        if (!unitHouseNumber) unitHouseNumber = form.querySelector("[name='unit_house_number']")?.value || '';
        if (!street) street = form.querySelector("[name='street']")?.value || '';
        if (!subdivision) subdivision = form.querySelector("[name='subdivision']")?.value || '';
        if (!barangay) barangay = form.querySelector("[name='barangay']")?.value || '';
        if (!city) city = form.querySelector("[name='city']")?.value || '';
        if (!province) province = form.querySelector("[name='province']")?.value || '';
        if (!region) region = form.querySelector("[name='region']")?.value || '';
        if (!zipcode) zipcode = form.querySelector("[name='zipcode']")?.value || '';
        if (!country) country = form.querySelector("[name='country']")?.value || 'Philippines';
        const preferredInstallationDate = form.querySelector("input[name='preferred_installation_date']")?.value || '';

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
        const line2 = line2Parts.join(', ');

        // Populate shipping details
        const fullName = middlename ? `${firstname} ${middlename} ${lastname}` : `${firstname} ${lastname}`;
        document.getElementById('confirm-name').textContent = fullName;
        document.getElementById('confirm-email').textContent = email;
        document.getElementById('confirm-phone').textContent = phone;

        // Set address with explicit two-line format. Use innerHTML with escaped content and <br> between lines.
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

        // Billing address
        const sameBilling = document.getElementById('same-billing')?.checked || false;
        const billingAddressEl = document.getElementById('confirm-billing-address');
        const billingNameEl = document.getElementById('confirm-billing-name');
        const billingEmailEl = document.getElementById('confirm-billing-email');
        const billingPhoneEl = document.getElementById('confirm-billing-phone');
        
        if (sameBilling) {
            // Use same data as shipping
            if (billingNameEl) billingNameEl.textContent = fullName;
            if (billingEmailEl) billingEmailEl.textContent = email;
            if (billingPhoneEl) billingPhoneEl.textContent = phone;
            if (billingAddressEl) {
                if (line1 && line2) {
                    billingAddressEl.innerHTML = escapeHtml(line1) + '<br>' + escapeHtml(line2);
                } else if (line1) {
                    billingAddressEl.textContent = line1;
                } else if (line2) {
                    billingAddressEl.textContent = line2;
                } else {
                    billingAddressEl.textContent = 'Same as shipping address';
                }
            }
        } else {
            // Get billing info fields
            const billingFirstname = document.getElementById('billing_firstname')?.value || '';
            const billingMiddlename = document.getElementById('billing_middlename')?.value || '';
            const billingLastname = document.getElementById('billing_lastname')?.value || '';
            const billingEmail = document.getElementById('billing_email')?.value || '';
            const billingPhone = document.getElementById('billing_phone')?.value || '';
            
            const billingFullName = billingMiddlename 
                ? `${billingFirstname} ${billingMiddlename} ${billingLastname}` 
                : `${billingFirstname} ${billingLastname}`;
            
            if (billingNameEl) billingNameEl.textContent = billingFullName.trim() || 'Not provided';
            if (billingEmailEl) billingEmailEl.textContent = billingEmail || 'Not provided';
            if (billingPhoneEl) billingPhoneEl.textContent = billingPhone || 'Not provided';
            
            // Get billing address fields
            const billingUnit = document.getElementById('billing_unit_house_number')?.value || '';
            const billingStreet = document.getElementById('billing_street')?.value || '';
            const billingSubdivision = document.getElementById('billing_subdivision')?.value || '';
            const billingBarangay = document.getElementById('billing_barangay')?.value || '';
            const billingCity = document.getElementById('billing-city')?.value || '';
            const billingProvince = document.getElementById('billing-province')?.value || '';
            const billingRegion = document.getElementById('billing-region')?.value || '';
            const billingZipcode = document.getElementById('billing_zipcode')?.value || '';
            const billingCountry = document.getElementById('billing-country')?.value || 'Philippines';
            
            const billingLine1Parts = [billingUnit, billingStreet, billingSubdivision].filter(Boolean);
            const billingLine2Parts = [billingBarangay, billingCity, billingProvince, billingRegion, billingZipcode, billingCountry].filter(Boolean);
            const billingLine1 = billingLine1Parts.join(', ');
            const billingLine2 = billingLine2Parts.join(', ');
            
            if (billingAddressEl) {
                if (billingLine1 && billingLine2) {
                    billingAddressEl.innerHTML = escapeHtml(billingLine1) + '<br>' + escapeHtml(billingLine2);
                } else if (billingLine1) {
                    billingAddressEl.textContent = billingLine1;
                } else if (billingLine2) {
                    billingAddressEl.textContent = billingLine2;
                } else {
                    billingAddressEl.textContent = 'No billing address provided';
                }
            }
        }

        // Special Instructions / Note
        const specialNote = form.querySelector("textarea[name='note']")?.value || '';
        const specialNoteEl = document.getElementById('confirm-special-note');
        if (specialNoteEl) {
            if (specialNote.trim()) {
                specialNoteEl.textContent = specialNote;
                specialNoteEl.style.fontStyle = 'normal';
                specialNoteEl.style.color = '#333';
            } else {
                specialNoteEl.textContent = 'No special instructions provided';
                specialNoteEl.style.fontStyle = 'italic';
                specialNoteEl.style.color = '#666';
            }
        }

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

        // Fetch SELECTED cart items from server via AJAX or use cached data
        const itemsBody = document.getElementById('confirm-items-body');
        itemsBody.innerHTML = '<tr><td colspan="4" class="no-items">Loading items...</td></tr>';
        
        // Check if this is stage payment mode - use stage payment data
        if (IS_STAGE_PAYMENT && window.stagePaymentData) {
            console.log('Using stage payment data for modal:', window.stagePaymentData);
            populateModalItemsFromStagePayment(window.stagePaymentData);
            return;
        }
        
        // Check if we already have loaded cart items (from page load)
        if (window.loadedCartItems && window.loadedCartItems.length > 0) {
            console.log('Using cached cart items for modal:', window.loadedCartItems);
            populateModalItems(window.loadedCartItems, window.loadedCartSummary);
            return;
        }

        $.getJSON(BASE_URL + "CartCon/get_selected_cart_ajax?selected=" + SELECTED_CART_IDS, function(res) {
            if (res.status === 'success') {
                // Store for future use
                window.loadedCartItems = res.items;
                window.loadedCartSummary = res.summary;
                populateModalItems(res.items, res.summary);
            } else {
                // Fallback: Get total from page summary
                const total = document.getElementById('summary-total')?.textContent || '₱0.00';
                const itemCount = document.querySelectorAll('.summary-item-row').length;
                
                itemsBody.innerHTML = `<tr><td colspan="4" class="no-items">${itemCount} item(s) in your cart</td></tr>`;
                
                // Add Total row
                const totalRow = document.createElement('tr');
                totalRow.style.borderTop = '2px solid #ddd';
                totalRow.innerHTML = `
                    <td colspan="3" style="text-align: right; padding: 18px; font-weight: 700; font-size: 1.05rem; color: #0f2b46;">Total:</td>
                    <td style="text-align: right; padding: 18px; font-weight: 700; font-size: 1.05rem; color: #0f2b46;">${total}</td>
                `;
                itemsBody.appendChild(totalRow);
            }
        }).fail(function() {
            // Fallback on AJAX failure
            const total = document.getElementById('summary-total')?.textContent || '₱0.00';
            const itemCount = document.querySelectorAll('.summary-item-row').length;
            
            itemsBody.innerHTML = `<tr><td colspan="4" class="no-items">${itemCount} item(s) in your cart</td></tr>`;
            
            // Add Total row
            const totalRow = document.createElement('tr');
            totalRow.style.borderTop = '2px solid #ddd';
            totalRow.innerHTML = `
                <td colspan="3" style="text-align: right; padding: 18px; font-weight: 700; font-size: 1.05rem; color: #0f2b46;">Total:</td>
                <td style="text-align: right; padding: 18px; font-weight: 700; font-size: 1.05rem; color: #0f2b46;">${total}</td>
            `;
            itemsBody.appendChild(totalRow);
        });
    }
    
    // Helper function to populate modal items from stage payment data
    function populateModalItemsFromStagePayment(stageData) {
        const itemsBody = document.getElementById('confirm-items-body');
        if (!itemsBody) return;
        
        itemsBody.innerHTML = '';
        
        if (!stageData.items || stageData.items.length === 0) {
            itemsBody.innerHTML = '<tr><td colspan="4" class="no-items">No items found</td></tr>';
            return;
        }
        
        stageData.items.forEach(item => {
            const row = document.createElement('tr');
            
            // Parse image URL (may be JSON array)
            let imageUrl = item.ImageUrl || '';
            try {
                if (typeof imageUrl === 'string' && imageUrl.trim().startsWith('[')) {
                    const parsed = JSON.parse(imageUrl);
                    if (Array.isArray(parsed) && parsed.length > 0) imageUrl = parsed[0];
                }
            } catch (e) { /* ignore */ }
            
            // Build full URL
            if (imageUrl) {
                if (imageUrl.startsWith('http')) {
                    // Already absolute
                } else if (imageUrl.startsWith('uploads/') || imageUrl.startsWith('assets/')) {
                    imageUrl = BASE_URL + imageUrl;
                } else {
                    imageUrl = BASE_URL + 'uploads/products/' + imageUrl.split('/').pop();
                }
            } else {
                imageUrl = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
            }
            
            // Build price range string
            let priceRangeHtml = '';
            if (item.PriceMin && item.PriceMax) {
                priceRangeHtml = `<div style="color: #28a745; font-size: 0.9rem; font-weight: 600; margin: 4px 0;">₱${Number(item.PriceMin).toLocaleString()} - ₱${Number(item.PriceMax).toLocaleString()}</div>`;
            } else if (item.PriceMin) {
                priceRangeHtml = `<div style="color: #28a745; font-size: 0.9rem; font-weight: 600; margin: 4px 0;">Starting at ₱${Number(item.PriceMin).toLocaleString()}</div>`;
            } else {
                priceRangeHtml = `<div style="color: #888; font-size: 0.85rem; font-style: italic; margin: 4px 0;">Price TBD after assessment</div>`;
            }
            
            // Build category/subcategory string
            let categoryHtml = '';
            if (item.Category) {
                categoryHtml += `<div style="color: #666; font-size: 0.85rem; margin: 2px 0;">${item.Category}</div>`;
            }
            if (item.Subcategory) {
                categoryHtml += `<div style="color: #888; font-size: 0.8rem; font-style: italic; margin: 2px 0;">${item.Subcategory}</div>`;
            }
            
            // Build customization HTML - mimic track order page with 2D preview
            let customHtml = '<span style="color: #888; font-size: 12px;">Standard</span>';
            
            // Build breakdown fields array from Customization JSON
            const breakdownFields = [];
            if (item.Customization) {
                try {
                    const customData = JSON.parse(item.Customization);
                    if (customData && typeof customData === 'object') {
                        // Add Dimension first if available
                        if (item.Dimensions) {
                            breakdownFields.push({label: 'Dimension', value: item.Dimensions});
                        }
                        // Add other fields from JSON
                        for (const [key, value] of Object.entries(customData)) {
                            if (!value || value === 'None' || value === '' || ['Dimension', 'Dimensions', 'product_id', 'product_name', 'total_quotation', 'quantity', 'price_breakdown', 'customization'].includes(key)) continue;
                            
                            // Convert camelCase to proper labels
                            const label = key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ').replace(/([A-Z])/g, ' $1').trim();
                            breakdownFields.push({label: label, value: value});
                        }
                    }
                } catch (e) {
                    console.warn('Failed to parse customization JSON:', e);
                }
            }
            
            // If we have customization data, show it like track order page
            if (breakdownFields.length > 0) {
                customHtml = '<div class="custom-layout" style="display: flex; align-items: center; gap: 8px;">';
                
                // Add 2D design thumbnail if available
                if (item.DesignRef) {
                    const designUrl = item.DesignRef.startsWith('http') ? item.DesignRef : BASE_URL + item.DesignRef;
                    customHtml += `
                        <div class="design-thumbnail-wrapper" style="flex-shrink: 0;">
                            <img src="${designUrl}"
                                 alt="Custom Design"
                                 class="design-thumbnail"
                                 style="width: 50px; height: 50px; object-fit: contain; border: 2px solid #0d3d4d; border-radius: 4px; cursor: pointer; transition: all 0.2s ease; background: #f8f8f8; padding: 2px;"
                                 onclick="showDesignModal('${designUrl}')"
                                 onerror="this.style.display='none';">
                            <span class="view-design-text" style="display: block; font-size: 8px; color: #0d3d4d; margin-top: 2px; font-weight: 500; text-align: center;">Click to view</span>
                        </div>
                    `;
                }
                
                // Show first 2 specs in compact format
                const displayParts = breakdownFields.slice(0, 2);
                const remainingCount = breakdownFields.length - 2;
                const displayText = displayParts.map(f => `${f.label}: ${f.value}`).join(' • ');
                const breakdownJson = JSON.stringify(breakdownFields).replace(/'/g, '&#39;').replace(/"/g, '&quot;');
                
                customHtml += `
                    <button type="button" class="view-breakdown-btn" data-breakdown="${breakdownJson}" style="display:inline-block; text-align:left; padding:10px 14px; border-radius:6px; border:2px solid #3b82f6; background:#eff6ff; color:#1e40af; cursor:pointer; font-size:13px; line-height:1.6; word-wrap:break-word; white-space:normal; transition:all 0.2s ease; font-weight:600; box-shadow:0 2px 4px rgba(59,130,246,0.1);" onmouseover="this.style.backgroundColor='#dbeafe'; this.style.borderColor='#2563eb';" onmouseout="this.style.backgroundColor='#eff6ff'; this.style.borderColor='#3b82f6';">
                        ${displayText}
                        ${remainingCount > 0 ? `<br><span style="font-size:12px; color:#4b5563;">and ${remainingCount} more</span>` : ''}
                        <br><span style="font-size:11px; opacity:0.7;">▼ Click to expand</span>
                    </button>
                `;
                customHtml += '</div>';
            }
            
            const placeholderSvg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
            
            row.innerHTML = `
                <td class="product-cell">
                    <div class="product-info">
                        <img src="${imageUrl}" alt="${item.ProductName || 'Product'}" class="product-thumb" onerror="this.onerror=null; this.src='${placeholderSvg}';">
                        <div class="product-details">
                            <div class="product-name" style="color: #1976d2; font-weight: 600; margin-bottom: 4px;">${item.ProductName || 'Product'}</div>
                            ${priceRangeHtml}
                            ${categoryHtml}
                        </div>
                    </div>
                </td>
                <td class="customization-cell">${customHtml}</td>
                <td class="qty-cell">${item.Quantity || 1}</td>
                <td class="price-cell">-</td>
            `;
            itemsBody.appendChild(row);
        });
        
        // Add Total row
        const totalRow = document.createElement('tr');
        totalRow.style.borderTop = '2px solid #ddd';
        totalRow.innerHTML = `
            <td colspan="3" style="text-align: right; padding: 18px; font-weight: 700; font-size: 1.05rem; color: #0f2b46;">Total:</td>
            <td style="text-align: right; padding: 18px; font-weight: 700; font-size: 1.05rem; color: #0f2b46;">₱${Number(stageData.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
        `;
        itemsBody.appendChild(totalRow);
    }
    
    // Helper function to populate modal items
    function populateModalItems(items, summary) {
        const itemsBody = document.getElementById('confirm-items-body');
        if (!itemsBody) return;
        
        itemsBody.innerHTML = '';
                
        items.forEach(item => {
                    // Sanitize image/design refs which may be stored as JSON arrays in DB
                    try {
                        if (item.design_ref && typeof item.design_ref === 'string' && item.design_ref.trim().startsWith('[')) {
                            const parsed = JSON.parse(item.design_ref);
                            if (Array.isArray(parsed) && parsed.length > 0) item.design_ref = parsed[0];
                        }
                    } catch (e) { /* leave as-is if parsing fails */ }
                    try {
                        if (item.image && typeof item.image === 'string' && item.image.trim().startsWith('[')) {
                            const parsedImg = JSON.parse(item.image);
                            if (Array.isArray(parsedImg) && parsedImg.length > 0) item.image = parsedImg[0];
                        }
                    } catch (e) { /* ignore */ }

                    const row = document.createElement('tr');
                    const customizationString = item.customization || 'Standard';
                    const productImage = item.image || (BASE_URL + 'assets/images/default-product.png');

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
                    
                    // Build price range string
                    let priceRangeHtml = '';
                    if (item.price_min && item.price_max) {
                        priceRangeHtml = `<div style="color: #28a745; font-size: 0.9rem; font-weight: 600; margin: 4px 0;">₱${Number(item.price_min).toLocaleString()} - ₱${Number(item.price_max).toLocaleString()}</div>`;
                    } else if (item.price_min) {
                        priceRangeHtml = `<div style="color: #28a745; font-size: 0.9rem; font-weight: 600; margin: 4px 0;">Starting at ₱${Number(item.price_min).toLocaleString()}</div>`;
                    } else {
                        priceRangeHtml = `<div style="color: #888; font-size: 0.85rem; font-style: italic; margin: 4px 0;">Price TBD after assessment</div>`;
                    }
                    
                    // Build category/subcategory string
                    let categoryHtml = '';
                    if (item.category) {
                        categoryHtml += `<div style="color: #666; font-size: 0.85rem; margin: 2px 0;">${item.category}</div>`;
                    }
                    if (item.subcategory) {
                        categoryHtml += `<div style="color: #888; font-size: 0.8rem; font-style: italic; margin: 2px 0;">${item.subcategory}</div>`;
                    }
                    
                    // Placeholder SVG for missing images
                    const placeholderSvg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                    
                    row.innerHTML = `
                        <td class="product-cell">
                            <div class="product-info">
                                <img src="${productImage}" alt="${item.description}" class="product-thumb" onerror="this.onerror=null; this.src='${placeholderSvg}';">
                                <div class="product-details">
                                    <div class="product-name" style="color: #1976d2; font-weight: 600; margin-bottom: 4px;">${item.description}</div>
                                    ${priceRangeHtml}
                                    ${categoryHtml}
                                </div>
                            </div>
                        </td>
                        <td class="customization-cell">${customHtml}</td>
                        <td class="qty-cell">${item.quantity}</td>
                        <td class="price-cell">₱${itemTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    `;
                    itemsBody.appendChild(row);
                });

        // Add Total row at the end of the table
        const totalRow = document.createElement('tr');
        totalRow.style.borderTop = '2px solid #ddd';
        totalRow.innerHTML = `
            <td colspan="3" style="text-align: right; padding: 18px; font-weight: 700; font-size: 1.05rem; color: #0f2b46;">Total:</td>
            <td style="text-align: right; padding: 18px; font-weight: 700; font-size: 1.05rem; color: #0f2b46;">₱${((summary && summary.total) || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
        `;
        itemsBody.appendChild(totalRow);
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
        const defaultConfirmLabel = 'Place Order';
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
        
        // =========================================
        // STAGE PAYMENT MODE — use submit_stage_payment instead
        // =========================================
        if (IS_STAGE_PAYMENT) {
            const stageOrderId = document.getElementById('stage-payment-order-id')?.value;
            const stageName = document.getElementById('stage-payment-stage')?.value;
            const stageAmount = document.getElementById('stage-payment-amount')?.value;

            if (!selectedPaymentMethod) {
                showToast('Please select a payment method.', 'warning');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Processing...';

            const stageFormData = new FormData();
            stageFormData.append('order_id', stageOrderId);
            stageFormData.append('stage', stageName);
            stageFormData.append('payment_method', selectedPaymentMethod);

            // Route stage payments through PayMongo
            initiateStagePayMongoPayment(stageOrderId, stageName, selectedPaymentMethod, stageAmount, btn, defaultConfirmLabel);
            return; // Skip normal place_order flow
        }
        // =========================================

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
            signal: controller.signal,
            credentials: 'same-origin' // Required to send session cookies with AJAX requests
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
            // Collect billing values to send to server so backend persists the exact billing data
            const sameBillingChecked = document.getElementById('same-billing')?.checked || false;

            // Helper to read shipping input values (used when same-billing is checked)
            function readShippingField(name) {
                // saved-address dropdown may override
                const savedAddressDropdown = document.getElementById('saved-address-dropdown');
                const useDifferentAddress = document.getElementById('use-different-shipping-address')?.checked || false;
                if (savedAddressDropdown && savedAddressDropdown.value && !useDifferentAddress) {
                    const selectedOption = savedAddressDropdown.options[savedAddressDropdown.selectedIndex];
                    const addressData = JSON.parse(selectedOption.getAttribute('data-address') || '{}');
                    return addressData[name] || '';
                }
                // fall back to form fields
                return document.querySelector("[name='" + name + "']")?.value || '';
            }

            const billingPayload = {
                order_id: orderId,
                payment_method: paymentMethod,
                // Shipping fields
                firstname: (document.querySelector("input[name='firstname']")?.value || '').trim(),
                middlename: (document.querySelector("input[name='middlename']")?.value || '').trim(),
                lastname: (document.querySelector("input[name='lastname']")?.value || '').trim(),
                email: (document.querySelector("input[name='email']")?.value || '').trim(),
                phone: (document.querySelector("input[name='phone']")?.value || '').trim(),
                unit_house_number: readShippingField('UnitHouseNumber') || (document.querySelector("input[name='unit_house_number']")?.value || '').trim(),
                street: readShippingField('Street') || (document.querySelector("input[name='street']")?.value || '').trim(),
                subdivision: readShippingField('Subdivision') || (document.querySelector("input[name='subdivision']")?.value || '').trim(),
                barangay: readShippingField('Barangay') || (document.querySelector("input[name='barangay']")?.value || '').trim(),
                city: readShippingField('City') || (document.querySelector("select[name='city']")?.value || document.querySelector("input[name='city']")?.value || '').trim(),
                province: readShippingField('Province') || (document.querySelector("select[name='province']")?.value || document.querySelector("input[name='province']")?.value || '').trim(),
                region: readShippingField('Region') || (document.querySelector("select[name='region']")?.value || document.querySelector("input[name='region']")?.value || '').trim(),
                zipcode: readShippingField('ZipCode') || (document.querySelector("input[name='zipcode']")?.value || '').trim(),
                country: readShippingField('Country') || (document.querySelector("select[name='country']")?.value || 'Philippines').trim(),
                // Billing fields
                billing_firstname: (document.getElementById('billing_firstname')?.value || '').trim(),
                billing_middlename: (document.getElementById('billing_middlename')?.value || '').trim(),
                billing_lastname: (document.getElementById('billing_lastname')?.value || '').trim(),
                billing_email: (document.getElementById('billing_email')?.value || '').trim(),
                billing_phone: (document.getElementById('billing_phone')?.value || '').trim(),
                billing_unit_house_number: (document.getElementById('billing_unit_house_number')?.value || '').trim(),
                billing_street: (document.getElementById('billing_street')?.value || '').trim(),
                billing_subdivision: (document.getElementById('billing_subdivision')?.value || '').trim(),
                billing_barangay: (document.getElementById('billing_barangay')?.value || '').trim(),
                billing_city: (document.getElementById('billing-city')?.value || '').trim(),
                billing_province: (document.getElementById('billing-province')?.value || '').trim(),
                billing_region: (document.getElementById('billing-region')?.value || '').trim(),
                billing_zipcode: (document.getElementById('billing_zipcode')?.value || '').trim(),
                billing_country: (document.getElementById('billing-country')?.value || '').trim(),
                same_billing: sameBillingChecked ? 'true' : 'false'
            };

            // If "same as shipping" is checked, override empty billing fields with shipping values
            if (sameBillingChecked) {
                // map shipping component names to billing keys
                const map = {
                    'billing_unit_house_number': 'UnitHouseNumber',
                    'billing_street': 'Street',
                    'billing_subdivision': 'Subdivision',
                    'billing_barangay': 'Barangay',
                    'billing_city': 'City',
                    'billing_province': 'Province',
                    'billing_region': 'Region',
                    'billing_zipcode': 'ZipCode',
                    'billing_country': 'Country'
                };
                for (const bk in map) {
                    if (!billingPayload[bk]) {
                        billingPayload[bk] = readShippingField(map[bk]) || billingPayload[bk];
                    }
                }
                // If billing name/email/phone empty, fallback to main form's fields
                if (!billingPayload.billing_firstname) billingPayload.billing_firstname = (document.querySelector("input[name='firstname']")?.value || '').trim();
                if (!billingPayload.billing_lastname) billingPayload.billing_lastname = (document.querySelector("input[name='lastname']")?.value || '').trim();
                if (!billingPayload.billing_email) billingPayload.billing_email = (document.querySelector("input[name='email']")?.value || '').trim();
                if (!billingPayload.billing_phone) billingPayload.billing_phone = (document.querySelector("input[name='phone']")?.value || '').trim();
            }

            const createIntentResponse = await fetch(BASE_URL + 'payment/create-payment-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(billingPayload),
                credentials: 'same-origin'
            });
            
            const intentData = await createIntentResponse.json();
            
            if (!intentData.status || intentData.status !== 'success') {
                throw new Error(intentData.message || 'Failed to initialize payment');
            }
            
            const { payment_intent_id, client_key, public_key } = intentData;
            console.log('Payment Intent Created:', payment_intent_id);
            // Map common country names to ISO codes; fallback to uppercased 2-letter value or 'PH'
            const countryMap = {
                'Philippines': 'PH',
                'United States': 'US',
                'United Kingdom': 'GB',
                'United Arab Emirates': 'AE',
                'Canada': 'CA',
                'Australia': 'AU'
            };
            function toIsoCountry(v) {
                if (!v) return 'PH';
                v = v.trim();
                if (v.length === 2) return v.toUpperCase();
                if (countryMap[v]) return countryMap[v];
                // try common variants
                const normalized = v.replace(/\s+/g, ' ').replace(/&/g, 'and');
                if (countryMap[normalized]) return countryMap[normalized];
                return 'PH';
            }
            
            // STEP 2 & 3: Create Payment Method (Frontend using PayMongo REST API)
            let paymentMethodId;
            
            if (paymentMethod === 'card') {
                // Card payment - collect card details
                console.log('STEP 2: Collecting card details...');
                
                const cardDetails = await collectCardDetails();
                
                if (!cardDetails) {
                    throw new Error('Card details collection cancelled');
                }
                
                // Gather billing address from checkout form (if present)
                const billingName = (document.getElementById('billing_firstname')?.value || '') + ' ' + (document.getElementById('billing_lastname')?.value || '');

                // Map common country names to ISO codes; fallback to uppercased 2-letter value or 'PH'
                const countryMap = {
                    'Philippines': 'PH',
                    'United States': 'US',
                    'United Kingdom': 'GB',
                    'United Arab Emirates': 'AE',
                    'Canada': 'CA',
                    'Australia': 'AU'
                };
                function toIsoCountry(v) {
                    if (!v) return 'PH';
                    v = v.trim();
                    if (v.length === 2) return v.toUpperCase();
                    if (countryMap[v]) return countryMap[v];
                    // try common variants
                    const normalized = v.replace(/\s+/g, ' ').replace(/&/g, 'and');
                    if (countryMap[normalized]) return countryMap[normalized];
                    return 'PH';
                }

                // Compose PayMongo address line1 as: Unit/House Number, Street, Subdivision, Barangay
                const billingUnit = document.getElementById('billing_unit_house_number')?.value || '';
                const billingStreet = document.getElementById('billing_street')?.value || '';
                const billingSubdivision = document.getElementById('billing_subdivision')?.value || '';
                const billingBarangay = document.getElementById('billing_barangay')?.value || '';
                const billingLine1Parts = [billingUnit, billingStreet, billingSubdivision, billingBarangay].filter(Boolean);
                const billingLine1 = billingLine1Parts.join(', ');

                // Prefer explicit billing fields; fall back to shipping inputs or saved address values if billing fields are hidden/empty
                const billingCity = (document.getElementById('billing-city')?.value || '').trim() || (document.querySelector("input[name='city']")?.value || '').trim();
                const billingProvince = (document.getElementById('billing-province')?.value || '').trim() || (document.querySelector("input[name='province']")?.value || '').trim() || (document.getElementById('billing-region')?.value || '').trim();
                const billingPostal = (document.getElementById('billing_zipcode')?.value || '').trim() || (document.querySelector("input[name='zipcode']")?.value || '').trim();
                const billingCountryIso = toIsoCountry(document.getElementById('billing-country')?.value || document.querySelector("input[name='country']")?.value || '');

                const billingAddress = {
                    line1: billingLine1,
                    city: billingCity,
                    state: billingProvince,
                    postal_code: billingPostal,
                    country: billingCountryIso
                };

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
                                    name: billingName.trim() || cardDetails.customerName || 'Customer',
                                    email: document.getElementById('billing_email')?.value || cardDetails.email || '',
                                    phone: document.getElementById('billing_phone')?.value || cardDetails.phone || '',
                                    address: billingAddress
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
                
                // Gather billing address from checkout form (if present)
                const ewalletBillingName = (document.getElementById('billing_firstname')?.value || '') + ' ' + (document.getElementById('billing_lastname')?.value || '');
                const ewalletUnit = document.getElementById('billing_unit_house_number')?.value || '';
                const ewalletStreet = document.getElementById('billing_street')?.value || '';
                const ewalletSubdivision = document.getElementById('billing_subdivision')?.value || '';
                const ewalletBarangay = document.getElementById('billing_barangay')?.value || '';
                const ewalletLine1Parts = [ewalletUnit, ewalletStreet, ewalletSubdivision, ewalletBarangay].filter(Boolean);
                const ewalletLine1 = ewalletLine1Parts.join(', ');
                // Use billing fields if present, otherwise fall back to shipping values
                const ewalletCity = (document.getElementById('billing-city')?.value || '').trim() || (document.querySelector("input[name='city']")?.value || '').trim();
                const ewalletProvince = (document.getElementById('billing-province')?.value || '').trim() || (document.querySelector("input[name='province']")?.value || '').trim() || (document.getElementById('billing-region')?.value || '').trim();
                const ewalletPostal = (document.getElementById('billing_zipcode')?.value || '').trim() || (document.querySelector("input[name='zipcode']")?.value || '').trim();
                const ewalletCountryIso = toIsoCountry(document.getElementById('billing-country')?.value || document.querySelector("input[name='country']")?.value || '');

                const ewalletBillingAddress = {
                    line1: ewalletLine1,
                    city: ewalletCity,
                    state: ewalletProvince,
                    postal_code: ewalletPostal,
                    country: ewalletCountryIso
                };

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
                                type: ewalletType,
                                billing: {
                                    name: ewalletBillingName.trim() || '',
                                    email: document.getElementById('billing_email')?.value || '',
                                    phone: document.getElementById('billing_phone')?.value || '',
                                    address: ewalletBillingAddress
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
                }),
                credentials: 'same-origin'
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
     * PayMongo Stage Payment Flow
     * Handles downpayment, fabrication, and installation stage payments via PayMongo
     */
    async function initiateStagePayMongoPayment(orderId, stage, paymentMethod, stageAmount, btn, defaultConfirmLabel) {
        try {
            btn.disabled = true;
            btn.textContent = 'Initializing payment...';
            closeConfirmModal();

            // STEP 1: Create Stage Payment Intent (Backend)
            console.log('Stage Payment STEP 1: Creating payment intent for', stage, '...');
            const intentPayload = {
                order_id: orderId,
                stage: stage,
                payment_method: paymentMethod
            };

            const createIntentResponse = await fetch(BASE_URL + 'payment/create-stage-payment-intent', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(intentPayload),
                credentials: 'same-origin'
            });

            const intentData = await createIntentResponse.json();

            if (!intentData.status || intentData.status !== 'success') {
                throw new Error(intentData.message || 'Failed to initialize stage payment');
            }

            const { payment_intent_id, client_key, public_key } = intentData;
            console.log('Stage Payment Intent Created:', payment_intent_id);

            // STEP 2: Create Payment Method (Frontend using PayMongo REST API)
            let paymentMethodId;

            if (paymentMethod === 'card') {
                console.log('Stage Payment STEP 2: Collecting card details...');
                const cardDetails = await collectCardDetails();
                if (!cardDetails) {
                    throw new Error('Card details collection cancelled');
                }

                btn.textContent = 'Processing card payment...';

                const pmResponse = await fetch('https://api.paymongo.com/v1/payment_methods', {
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
                                }
                            }
                        }
                    })
                });

                const pmData = await pmResponse.json();
                if (pmResponse.ok && pmData.data && pmData.data.id) {
                    paymentMethodId = pmData.data.id;
                } else {
                    throw new Error(pmData.errors?.[0]?.detail || 'Failed to create payment method');
                }
            } else if (paymentMethod === 'gcash' || paymentMethod === 'maya' || paymentMethod === 'ewallet') {
                console.log('Stage Payment STEP 2: Creating e-wallet payment method...');
                btn.textContent = 'Processing e-wallet payment...';
                const ewalletType = paymentMethod === 'maya' ? 'paymaya' : 'gcash';

                const pmResponse = await fetch('https://api.paymongo.com/v1/payment_methods', {
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

                const pmData = await pmResponse.json();
                if (pmResponse.ok && pmData.data && pmData.data.id) {
                    paymentMethodId = pmData.data.id;
                } else {
                    throw new Error(pmData.errors?.[0]?.detail || 'Failed to create payment method');
                }
            } else {
                throw new Error('Invalid payment method');
            }

            console.log('Stage Payment Method Created:', paymentMethodId);

            // STEP 3: Attach Payment Method (Backend)
            console.log('Stage Payment STEP 3: Attaching payment method...');
            const attachResponse = await fetch(BASE_URL + 'payment/attach-payment-method', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    payment_intent_id: payment_intent_id,
                    payment_method_id: paymentMethodId,
                    order_id: orderId,
                    stage: stage
                }),
                credentials: 'same-origin'
            });

            const attachData = await attachResponse.json();

            if (!attachData.status || attachData.status !== 'success') {
                throw new Error(attachData.message || 'Failed to process stage payment');
            }

            // STEP 4: Handle Response
            if (attachData.payment_status === 'succeeded') {
                console.log('Stage payment succeeded!');
                showToast(attachData.message || 'Payment successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = attachData.redirect_url || (BASE_URL + 'order-tracking?order=' + orderId);
                }, 1500);
            } else if (attachData.payment_status === 'awaiting_next_action') {
                console.log('Redirecting to PayMongo for e-wallet stage payment...');
                showToast('Redirecting to PayMongo payment page...', 'info', 4000);
                setTimeout(() => {
                    window.location.href = attachData.redirect_url;
                }, 500);
            } else {
                throw new Error('Stage payment processing failed. Please try again.');
            }

        } catch (error) {
            console.error('Stage PayMongo Payment Error:', error);
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
                    showToast('Please enter expiration date in MM/YY format', 'warning');
                    return;
                }
                
                const expMonth = parseInt(expParts[0]);
                const expYear2Digit = parseInt(expParts[1]);
                
                // Validate month (1-12)
                if (expMonth < 1 || expMonth > 12) {
                    showToast('Please enter a valid month (01-12)', 'warning');
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
                    showToast('Please enter a valid expiration year', 'warning');
                    return;
                }
                
                // Also check if the card is expired (month and year in the past)
                const currentMonth = new Date().getMonth() + 1; // getMonth() returns 0-11
                if (expYear === currentYear && expMonth < currentMonth) {
                    showToast('This card has expired', 'error');
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

// Customization breakdown modal handler (same as track order page)
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.view-breakdown-btn');
    if (!btn) return;
    e.preventDefault();
    var breakdownData = btn.getAttribute('data-breakdown');
    if (!breakdownData) return;
    
    var breakdownFields = [];
    try {
        breakdownFields = JSON.parse(breakdownData);
    } catch (err) {
        console.error('Failed to parse breakdown data:', err);
        return;
    }
    
    var contentHtml = '<div class="breakdown-list" style="padding:0;">';
    breakdownFields.forEach(function(field) {
        var label = field.label || '';
        var value = field.value || field.val || '';
        if (!value || value === '' || value === 'None') {
            contentHtml += '<div style="margin-bottom:16px; padding:12px; background:#f9fafb; border-left:4px solid #d1d5db; border-radius:4px;"><strong style="display:block;color:#1f2937; margin-bottom:6px; font-size:14px;">' + label + '</strong><div style="color:#9ca3af; font-style:italic; font-size:13px;">Not specified</div></div>';
        } else {
            contentHtml += '<div style="margin-bottom:16px; padding:12px; background:#f0f9ff; border-left:4px solid #3b82f6; border-radius:4px;"><strong style="display:block;color:#1e40af; margin-bottom:6px; font-size:14px;">' + label + '</strong><div style="color:#1f2937; font-size:14px; font-weight:500;">' + value + '</div></div>';
        }
    });
    contentHtml += '</div>';
    
    // Create or update modal
    var modal = document.getElementById('breakdownModal');
    if (!modal) {
        var modalHtml = '<div id="breakdownModal" class="modal-backdrop" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:10000;"><div class="modal-content" style="max-width:720px;width:90%;max-height:85vh;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.3);"><div class="modal-header" style="background:#1e3a8a;color:#fff;padding:16px 20px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;"><h3 style="margin:0;font-size:20px;font-weight:700;">2D Customization Breakdown</h3><button class="modal-close" id="breakdownModalClose" style="background:rgba(255,255,255,0.2);border:none;color:#fff;font-size:28px;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background=\'rgba(255,255,255,0.3)\';" onmouseout="this.style.background=\'rgba(255,255,255,0.2)\';">×</button></div><div class="modal-body" id="breakdownModalBody" style="padding:24px;background:#fff;border-radius:0 0 12px 12px;"></div></div></div>';
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('breakdownModal');
        document.getElementById('breakdownModalClose').addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        });
        modal.addEventListener('click', function(ev) {
            if (ev.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
    
    document.getElementById('breakdownModalBody').innerHTML = contentHtml;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
});

</script>