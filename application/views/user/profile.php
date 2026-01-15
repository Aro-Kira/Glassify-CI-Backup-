<?php
// Get user data
$user_name = isset($user) && $user ? htmlspecialchars(trim($user->First_Name . ' ' . ($user->Middle_Name ? $user->Middle_Name . ' ' : '') . $user->Last_Name)) : 'User';
$user_email = isset($user) && $user ? htmlspecialchars($user->Email) : '';
$username = isset($user) && $user ? htmlspecialchars($user->Email) : 'user'; // Using email as username
$current_section = isset($current_section) ? $current_section : 'account-details';

// Helper function to get status class
function get_status_class($status) {
    $status_lower = strtolower(trim($status));
    
    // Map specific statuses to their classes
    if ($status_lower === 'disapproved') {
        return 'disapproved';
    } elseif ($status_lower === 'ready to approve') {
        return 'ready-to-approve';
    } elseif ($status_lower === 'approved') {
        return 'approved';
    } elseif ($status_lower === 'completed') {
        return 'completed';
    } elseif ($status_lower === 'in fabrication') {
        return 'in-fabrication';
    } elseif ($status_lower === 'ready for installation') {
        return 'ready-for-installation';
    } elseif ($status_lower === 'pending review' || $status_lower === 'pending') {
        return 'pending';
    } elseif ($status_lower === 'awaiting admin') {
        return 'awaiting-admin';
    } elseif ($status_lower === 'cancelled') {
        return 'cancelled';
    } elseif ($status_lower === 'returned') {
        return 'returned';
    } elseif ($status_lower === 'confirmed') {
        return 'confirmed';
    }
    
    // Fallback for any other status
    return 'pending';
}
?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/user/profile.css'); ?>">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<body>
    <main class="account-settings">
        <div class="profile-container">
            <h2 class="settings-title">Account Setting</h2>
            <div class="profile-content-wrapper">
            <!-- Left Sidebar Navigation -->
            <aside class="profile-sidebar">
                <nav class="sidebar-nav">
                    <a href="#account-details" class="nav-item <?php echo $current_section === 'account-details' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i>
                        <span>Account details</span>
                    </a>
                    <a href="#orders" class="nav-item <?php echo $current_section === 'orders' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i>
                        <span>Orders</span>
                    </a>
                    <a href="#addresses" class="nav-item <?php echo $current_section === 'addresses' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Addresses</span>
                    </a>
                    <a href="<?php echo base_url('logout'); ?>" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Log out</span>
                    </a>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <section class="profile-content">
                <!-- Orders Section -->
                <div id="orders" class="content-section">
                    <h3>Your Orders</h3>
                    <?php if (!empty($orders_with_products)): ?>
                        <div class="table-wrapper">
                            <table class="styled-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders_with_products as $order): ?>
                                        <tr>
                                            <td class="product-cell">
                                                <img src="<?= base_url('uploads/products/' . ($order->ImageUrl ?? 'default.jpg')) ?>" alt="<?= htmlspecialchars($order->ProductName ?? 'Product') ?>" class="product-thumb">
                                                <span><?= htmlspecialchars($order->ProductName ?? 'Custom Order') ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT)) ?></td>
                                            <td><?= date('M j, Y', strtotime($order->OrderDate)) ?></td>
                                            <td><span class="status <?= get_status_class($order->Status) ?>"><?= htmlspecialchars($order->Status) ?></span></td>
                                            <td><span class="status <?= get_status_class($order->PaymentStatus) ?>"><?= htmlspecialchars($order->PaymentStatus) ?></span></td>
                                            <td><a href="<?= base_url('track_order?order=' . $order->OrderID) ?>" class="btn-view-details">View details</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>You haven't placed any orders yet. <a href="<?= base_url('products') ?>">Start shopping!</a></p>
                    <?php endif; ?>
                </div>

                <!-- Addresses Section -->
                <div id="addresses" class="content-section">
                    <h3>Saved Addresses</h3>
                    <div class="table-wrapper addresses-table-wrapper">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>Unit/House #</th>
                                    <th>Street</th>
                                    <th>Subdivision</th>
                                    <th>Barangay</th>
                                    <th>City</th>
                                    <th>Province</th>
                                    <th>Region</th>
                                    <th>Country</th>
                                    <th>Zip Code</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($all_addresses)): ?>
                                    <?php foreach ($all_addresses as $addr): ?>
                                        <tr>
                                            <td>
                                                <div class="unit-house-number-cell">
                                                    <?= htmlspecialchars($addr->UnitHouseNumber ?? '') ?>
                                                    <?php if ($addr->IsDefault == 1): ?>
                                                        <div class="default-label">Default</div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($addr->Street ?? '') ?></td>
                                            <td><?= htmlspecialchars($addr->Subdivision ?? '') ?></td>
                                            <td><?= htmlspecialchars($addr->Barangay ?? '') ?></td>
                                            <td><?= htmlspecialchars($addr->City ?? '') ?></td>
                                            <td><?= htmlspecialchars($addr->Province ?? '') ?></td>
                                            <td><?= htmlspecialchars($addr->Region ?? '') ?></td>
                                            <td><?= htmlspecialchars($addr->Country ?? 'Philippines') ?></td>
                                            <td><?= htmlspecialchars($addr->ZipCode ?? '') ?></td>
                                            <td>
                                                <button class="btn-edit-icon" data-address-id="<?= $addr->AddressID ?>" title="Edit Address">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" style="text-align: center; color: #888; padding: 20px;">
                                            No saved addresses.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <button class="btn-add-new-address" id="btnAddNewAddressMain" style="margin-top: 2rem;">+ Add New Address</button>
                    
                    <!-- Address Action Modal -->
                    <div id="addressActionModal" class="address-modal-overlay" style="display: none !important;">
                        <div class="address-modal-content">
                            <div class="address-modal-header">
                                <h3>Address Options</h3>
                                <button type="button" class="address-modal-close" onclick="closeAddressActionModal()">&times;</button>
                            </div>
                            <div class="address-modal-body">
                                <p>What would you like to do with this address?</p>
                                <div class="address-modal-actions">
                                    <button class="address-action-btn address-action-edit" id="modalEditBtn">Edit Address</button>
                                    <button class="address-action-btn address-action-delete" id="modalDeleteBtn">Delete Address</button>
                                    <button class="address-action-btn address-action-default" id="modalSetDefaultBtn">Set as Default</button>
                                </div>
                            </div>
                            <div class="address-modal-footer">
                                <button type="button" class="address-modal-btn-cancel" onclick="closeAddressActionModal()">Cancel</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add/Edit Address Modal -->
                    <div id="addressFormModal" class="address-modal-overlay" style="display: none !important;">
                        <div class="address-modal-content address-form-modal-content">
                            <div class="address-modal-header">
                                <h3 id="addressFormModalTitle">Add New Address</h3>
                                <button type="button" class="address-modal-close" onclick="closeAddressFormModal()">&times;</button>
                            </div>
                            <div class="address-modal-body">
                                <form id="addressesAddressForm" class="address-form">
                        <input type="hidden" name="AddressID" id="addressesEditAddressID" value="">
                        
                        <div class="form-field-group">
                            <label for="addressesCountry">Country</label>
                            <input type="text" name="Country" id="addressesCountry" value="Philippines" readonly>
                        </div>
                        
                        <div class="form-field-group">
                            <label for="addressesRegion">Region <span class="required-asterisk">*</span></label>
                            <select name="Region" id="addressesRegion" required>
                                <option value="">Select Region</option>
                                <option value="NCR">NCR (National Capital Region)</option>
                                <option value="Region III">Region III (Central Luzon)</option>
                                <option value="Region IV-A">Region IV-A (CALABARZON)</option>
                            </select>
                        </div>
                        
                        <div class="form-field-group">
                            <label for="addressesProvince">Province <span class="required-asterisk">*</span></label>
                            <select name="Province" id="addressesProvince" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        
                        <div class="form-field-group">
                            <label for="addressesCity">City/Municipality <span class="required-asterisk">*</span></label>
                            <select name="City" id="addressesCity" required>
                                <option value="">Select City/Municipality</option>
                            </select>
                        </div>
                        
                        <div class="form-field-group">
                            <label for="addressesBarangay">Barangay <span class="required-asterisk">*</span></label>
                            <input type="text" name="Barangay" id="addressesBarangay" placeholder="Enter Barangay" required>
                        </div>
                        
                        <div class="form-field-group">
                            <label for="addressesSubdivision">Subdivision</label>
                            <input type="text" name="Subdivision" id="addressesSubdivision" placeholder="Enter Subdivision/Village (optional)">
                        </div>
                        
                        <div class="form-field-group">
                            <label for="addressesStreet">Street</label>
                            <input type="text" name="Street" id="addressesStreet" placeholder="Enter Street (optional)">
                        </div>
                        
                        <div class="form-field-group">
                            <label for="addressesUnitHouseNumber">Unit/House Number <span class="required-asterisk">*</span></label>
                            <input type="text" name="UnitHouseNumber" id="addressesUnitHouseNumber" placeholder="Enter Unit/House Number" required>
                        </div>
                        
                        <div class="form-field-group">
                            <label for="addressesZipCode">Zip Code <span class="required-asterisk">*</span></label>
                            <input type="text" name="ZipCode" id="addressesZipCode" placeholder="Enter Zip Code" required>
                        </div>
                        
                        <div class="form-field-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="IsDefault" id="addressesIsDefault" value="1">
                                <span>Set this address as my default shipping address</span>
                            </label>
                        </div>
                                </form>
                            </div>
                            <div class="address-modal-footer">
                                <!-- Buttons shown when editing -->
                                <div id="addressFormEditButtons" class="address-form-edit-buttons" style="display: none;">
                                    <button type="submit" class="address-modal-btn-save" id="addressesAddressSubmitBtn" form="addressesAddressForm">Save Changes</button>
                                    <button type="button" class="address-action-btn address-action-delete" id="formModalDeleteBtn">Delete</button>
                                    <button type="button" class="address-modal-btn-cancel" onclick="closeAddressFormModal()">Cancel</button>
                                </div>
                                <!-- Buttons shown when adding new address -->
                                <div id="addressFormAddButtons" class="address-modal-footer-buttons">
                                    <button type="button" class="address-modal-btn-cancel" onclick="closeAddressFormModal()">Cancel</button>
                                    <button type="submit" class="address-modal-btn-save" id="addressesAddressSubmitBtnAdd" form="addressesAddressForm">Save Address</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Details Section (Current Profile Form) -->
                <div id="account-details" class="content-section">
                    <section class="settings-container">
            <section class="settings-form">
                <form id="accountForm">
                                <!-- First Name, Middle Name, and Surname in a row -->
                                <div class="form-row form-row-three">
                                    <div class="form-field">
                                        <label for="firstname">First name <span class="required">*</span></label>
                    <input type="text" id="firstname" name="firstname"
                                            value="<?= isset($user) ? htmlspecialchars($user->First_Name) : '' ?>" required>
                                    </div>
                                    <div class="form-field">
                                        <label for="middlename">Middle name</label>
                    <input type="text" id="middlename" name="middlename"
                                            value="<?= isset($user) ? htmlspecialchars($user->Middle_Name ?? '') : '' ?>">
                                    </div>
                                    <div class="form-field">
                                        <label for="lastname">Surname <span class="required">*</span></label>
                    <input type="text" id="lastname" name="lastname"
                                            value="<?= isset($user) ? htmlspecialchars($user->Last_Name) : '' ?>" required>
                                    </div>
                                </div>

                                <!-- Email Address and Phone Number in one row -->
                                <div class="form-row">
                                    <div class="form-field">
                                        <label for="email">Email address <span class="required">*</span></label>
                                        <input type="email" id="email" name="email" value="<?= isset($user) ? htmlspecialchars($user->Email) : '' ?>" required>
                                    </div>
                                    <div class="form-field">
                                        <label for="phone">Phone Number <span class="required">*</span></label>
                                        <input type="text" id="phone" name="phone" value="<?= isset($user) ? htmlspecialchars($user->PhoneNum ?? '') : '' ?>" required>
                                    </div>
                                </div>

                    <!-- Password Change Section -->
                    <div class="password-section">
                                    <h4 class="password-section-title">Password change</h4>
                                    <div class="form-field">
                                        <label for="current_password">Current password (leave blank to leave unchanged)</label>
                                        <div class="password-input-wrapper">
                        <input type="password" id="current_password" name="current_password" placeholder="Enter your current password">
                                            <button type="button" class="toggle-password" data-target="current_password">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                        
                                    <div class="form-field">
                                        <label for="new_password">New password (leave blank to leave unchanged)</label>
                                        <div class="password-input-wrapper">
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                                            <button type="button" class="toggle-password" data-target="new_password">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                        
                                    <div class="form-field">
                                        <label for="confirm_password">Confirm new password</label>
                                        <div class="password-input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                                            <button type="button" class="toggle-password" data-target="confirm_password">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                        <small id="passwordError" style="color: #dc3545; display: none; margin-top: 5px;"></small>
                    </div>

                    <div class="form-buttons">
                                    <button type="submit" class="btn-save" id="saveBtn" disabled>Save changes</button>
                    </div>
                </form>
            </section>
                    </section>
                </div>
            </section>
            </div>
        </div>

        <!-- Custom Modal -->
        <div class="modal" id="addressModal">
            <div class="upload-style-modal">

                <!-- Header Bar -->
                <div class="modal-header-bar">
                    <h3>Add or Select Address</h3>
                    <span class="modal-close" id="modalCloseBtn">&times;</span>
                </div>

                <div class="modal-body">

                    <!-- Select Address Section -->
                    <div class="block-section">

                        <h4 class="section-title">Saved Addresses</h4>

                        <div class="table-wrapper">
                            <table class="styled-table">
                                <thead>
                                    <tr>
                                        <th>Unit/House #</th>
                                        <th>Street</th>
                                        <th>Subdivision</th>
                                        <th>Barangay</th>
                                        <th>City</th>
                                        <th>Province</th>
                                        <th>Region</th>
                                        <th>Country</th>
                                        <th>Zip Code</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $hasAddresses = false;
                                    foreach ($addresses as $type => $addr): 
                                        if ($addr): // Only display if address exists (not null)
                                            $hasAddresses = true;
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($addr->AddressLine ?? '') ?></td>
                                            <td><?= htmlspecialchars($addr->City ?? '') ?></td>
                                            <td><?= htmlspecialchars($addr->Province ?? '') ?></td>
                                            <td><?= htmlspecialchars($addr->Country ?? '') ?></td>
                                            <td><?= htmlspecialchars($addr->ZipCode ?? '') ?></td>
                                            <td>
                                                <button class="btn-select select-address"
                                                    data-address="<?= htmlspecialchars(($addr->AddressLine ?? '') . ', ' . ($addr->City ?? '') . ', ' . ($addr->Province ?? '') . ', ' . ($addr->Country ?? '') . ', ' . ($addr->ZipCode ?? '')) ?>">
                                                    Select
                                                </button>
                                            </td>
                                        </tr>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    
                                    if (!$hasAddresses): 
                                    ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; color: #888; padding: 20px;">
                                                No saved addresses. Add one below.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="addressLoader" style="display:none; text-align:center; padding:10px;">
                            <div class="spinner" style="
        width:25px;
        height:25px;
        border:4px solid #ddd;
        border-top-color:#333;
        border-radius:50%;
        animation:spin 1s linear infinite;
        margin:auto;
    "></div>
                        </div>

                        <style>
                            @keyframes spin {
                                from {
                                    transform: rotate(0deg);
                                }

                                to {
                                    transform: rotate(360deg);
                                }
                            }
                        </style>

                    </div>

                    <!-- Add/Edit address -->
                    <div class="block-section">
                        <h4 class="section-title" id="addressFormTitle">Add New Address</h4>

                        <form id="newAddressForm" class="address-form">
                            <input type="hidden" name="AddressID" id="editAddressID" value="">
                            
                            <div class="form-field-group">
                                <label for="country">Country</label>
                                <input type="text" name="Country" id="country" value="Philippines" readonly>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="region">Region <span class="required-asterisk">*</span></label>
                                <select name="Region" id="region" required>
                                    <option value="">Select Region</option>
                                    <option value="NCR">NCR (National Capital Region)</option>
                                    <option value="Region III">Region III (Central Luzon)</option>
                                    <option value="Region IV-A">Region IV-A (CALABARZON)</option>
                                </select>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="province">Province <span class="required-asterisk">*</span></label>
                                <select name="Province" id="province" required>
                                    <option value="">Select Province</option>
                                </select>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="city">City/Municipality <span class="required-asterisk">*</span></label>
                                <select name="City" id="city" required>
                                    <option value="">Select City/Municipality</option>
                                </select>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="barangay">Barangay <span class="required-asterisk">*</span></label>
                                <input type="text" name="Barangay" id="barangay" placeholder="Enter Barangay" required>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="subdivision">Subdivision</label>
                                <input type="text" name="Subdivision" id="subdivision" placeholder="Enter Subdivision/Village (optional)">
                            </div>
                            
                            <div class="form-field-group">
                                <label for="unitHouseNumber">Unit/House Number <span class="required-asterisk">*</span></label>
                                <input type="text" name="UnitHouseNumber" id="unitHouseNumber" placeholder="Enter Unit/House Number" required>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="street">Street</label>
                                <input type="text" name="Street" id="street" placeholder="Enter Street (optional)">
                            </div>
                            
                            <div class="form-field-group">
                                <label for="zipCode">Zip Code <span class="required-asterisk">*</span></label>
                                <input type="text" name="ZipCode" id="zipCode" placeholder="Enter Zip Code" required>
                            </div>
                            
                            <div class="form-field-group checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="IsDefault" id="isDefault" value="1">
                                    <span>Set this address as my default shipping address</span>
                                </label>
                            </div>

                            <button type="submit" class="btn-add" id="addressSubmitBtn">+ Add Address</button>
                            <button type="button" class="btn-cancel" id="cancelEditBtn" style="display:none; margin-top:10px;">Cancel Edit</button>
                        </form>
                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn-cancel-modal" id="modalCancelBtn">Cancel</button>
                    <button class="btn-done" id="modalDoneBtn">Done</button>
                </div>


            </div>
        </div>

    </main>



    <script>
        $(document).ready(function () {

            // ========= MODAL SETUP =========
            const modal = $("#addressModal");
            const openModalBtn = $("#chooseAddressBtn");
            const modalCloseBtn = $("#modalCloseBtn");
            const modalCancelBtn = $("#modalCancelBtn");
            const modalDoneBtn = $("#modalDoneBtn");

            // SHOW MODAL ONLY WHEN BUTTON CLICKED
            openModalBtn.on("click", function () {
                modal.addClass("show");
                modal.fadeIn(200); // optional smooth animation
                loadAddresses();
            });

            // CLOSE MODAL
            modalCloseBtn.on("click", () => modal.removeClass("show").fadeOut(200));
            modalCancelBtn.on("click", () => modal.removeClass("show").fadeOut(200));
            modalDoneBtn.on("click", () => modal.removeClass("show").fadeOut(200));

            // CLICK OUTSIDE MODAL TO CLOSE
            $(window).on("click", function (e) {
                if ($(e.target).is(modal)) {
                    modal.removeClass("show");
                    modal.fadeOut(200);
                }
            });

            // ========= PHILIPPINE REGIONS AND CITIES DATA =========
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

            // ========= REGION CHANGE HANDLER =========
            $("#region").on("change", function() {
                const selectedRegion = $(this).val();
                const provinceSelect = $("#province");
                const citySelect = $("#city");
                
                // Clear existing options
                provinceSelect.html('<option value="">Select Province</option>');
                citySelect.html('<option value="">Select City/Municipality</option>');
                
                if (selectedRegion === "NCR") {
                    // For NCR, set province to Metro Manila
                    provinceSelect.html('<option value="Metro Manila">Metro Manila</option>');
                    provinceSelect.val("Metro Manila");
                    
                    // Populate cities
                    metroManilaCities.forEach(city => {
                        citySelect.append(`<option value="${city}">${city}</option>`);
                    });
                } else if (selectedRegion === "Region III") {
                    // Populate Region III provinces
                    Object.keys(region3Provinces).forEach(province => {
                        provinceSelect.append(`<option value="${province}">${province}</option>`);
                    });
                } else if (selectedRegion === "Region IV-A") {
                    // Populate Region IV-A provinces
                    Object.keys(region4AProvinces).forEach(province => {
                        provinceSelect.append(`<option value="${province}">${province}</option>`);
                    });
                }
            });

            // ========= PROVINCE CHANGE HANDLER =========
            $("#province").on("change", function() {
                const selectedProvince = $(this).val();
                const selectedRegion = $("#region").val();
                const citySelect = $("#city");
                
                // Clear cities
                citySelect.html('<option value="">Select City/Municipality</option>');
                
                if (selectedProvince === "Metro Manila") {
                    // Populate Metro Manila cities
                    metroManilaCities.forEach(city => {
                        citySelect.append(`<option value="${city}">${city}</option>`);
                    });
                } else if (selectedRegion === "Region III" && region3Provinces[selectedProvince]) {
                    // Populate Region III cities
                    region3Provinces[selectedProvince].forEach(city => {
                        citySelect.append(`<option value="${city}">${city}</option>`);
                    });
                } else if (selectedRegion === "Region IV-A" && region4AProvinces[selectedProvince]) {
                    // Populate Region IV-A cities
                    region4AProvinces[selectedProvince].forEach(city => {
                        citySelect.append(`<option value="${city}">${city}</option>`);
                    });
                }
            });

            // ========= LOAD ADDRESSES (AJAX REFRESH) =========
            function loadAddresses() {
                $("#addressLoader").show();
                const tbody = $("#addressModal tbody");
                tbody.html(""); // clear table

                $.ajax({
                    url: "<?= base_url('UserCon/get_addresses') ?>",
                    method: "GET",
                    dataType: "json",
                    success: function (res) {
                        $("#addressLoader").hide();

                        if (!res.success || res.data.length === 0) {
                            tbody.html("<tr><td colspan='7' style='text-align:center'>No addresses found.</td></tr>");
                            return;
                        }

                        res.data.forEach(a => {
                            tbody.append(`
                        <tr>
                            <td>${a.UnitHouseNumber || ''}</td>
                            <td>${a.Street || ''}</td>
                            <td>${a.Subdivision || ''}</td>
                            <td>${a.Barangay || ''}</td>
                            <td>${a.City || ''}</td>
                            <td>${a.Province || ''}</td>
                            <td>${a.Region || ''}</td>
                            <td>${a.Country || 'Philippines'}</td>
                            <td>${a.ZipCode || ''}</td>
                            <td>
                                <button class="btn-select select-address" data-address-id="${a.AddressID}"
                                    data-address="${[a.UnitHouseNumber || '', a.Street || '', a.Subdivision || '', a.Barangay || '', a.City || '', a.Province || '', a.Region || '', a.Country || 'Philippines', a.ZipCode || ''].filter(Boolean).join(', ')}">
                                    Select
                                </button>
                                <button class="btn-select edit-address" data-address-id="${a.AddressID}" style="margin-left:5px; background:#037c9e;">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    `);
                        });
                    }
                });
            }

            // ========= SELECT ADDRESS =========
            $(document).on("click", ".select-address", function () {
                const addressText = $(this).data("address");
                $("#address").val(addressText);
                modal.removeClass("show").fadeOut(200);
                
                // Trigger change event to enable save button
                $("#address").trigger('change');
            });

            // ========= EDIT ADDRESS =========
            $(document).on("click", ".edit-address", function () {
                const addressId = $(this).data("address-id");
                
                $.ajax({
                    url: "<?= base_url('UserCon/get_address') ?>",
                    method: "GET",
                    data: { address_id: addressId },
                    dataType: "json",
                    success: function (res) {
                        if (res.success && res.data) {
                            const addr = res.data;
                            
                            // Populate form fields
                            $("#editAddressID").val(addr.AddressID || '');
                            $("#unitHouseNumber").val(addr.UnitHouseNumber || '');
                            $("#street").val(addr.Street || '');
                            $("#subdivision").val(addr.Subdivision || '');
                            $("#barangay").val(addr.Barangay || '');
                            $("#region").val(addr.Region || '').trigger('change');
                            
                            // Wait a bit for province to populate, then set city
                            setTimeout(function() {
                                $("#province").val(addr.Province || '');
                                $("#province").trigger('change');
                                
                                setTimeout(function() {
                                    $("#city").val(addr.City || '');
                                }, 100);
                            }, 100);
                            
                            $("#country").val(addr.Country || 'Philippines');
                            $("#zipCode").val(addr.ZipCode || '');
                            $("#isDefault").prop('checked', addr.IsDefault == 1);
                            
                            // Change form title and button
                            $("#addressFormTitle").text("Edit Address");
                            $("#addressSubmitBtn").text("Update Address");
                            $("#cancelEditBtn").show();
                            
                            // Scroll to form
                            $('html, body').animate({
                                scrollTop: $("#newAddressForm").offset().top - 100
                            }, 500);
                        }
                    }
                });
            });

            // ========= CANCEL EDIT =========
            $("#cancelEditBtn").on("click", function() {
                $("#newAddressForm")[0].reset();
                $("#editAddressID").val('');
                $("#isDefault").prop('checked', false);
                $("#addressFormTitle").text("Add New Address");
                $("#addressSubmitBtn").text("+ Add Address");
                $(this).hide();
                $("#region").trigger('change');
            });

            // ========= ADD/UPDATE ADDRESS (AJAX + AUTO REFRESH) =========
            $("#newAddressForm").submit(function (e) {
                e.preventDefault();

                const fd = new FormData(this);
                const addressId = $("#editAddressID").val();
                const url = addressId ? "<?= base_url('UserCon/update_address') ?>" : "<?= base_url('UserCon/add_address') ?>";

                fetch(url, {
                    method: "POST",
                    body: fd
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (addressId) {
                                alert("Address updated successfully!");
                            } else {
                                alert("Address added successfully!");
                            }
                            $("#newAddressForm")[0].reset();
                            $("#editAddressID").val('');
                            $("#isDefault").prop('checked', false);
                            $("#addressFormTitle").text("Add New Address");
                            $("#addressSubmitBtn").text("+ Add Address");
                            $("#cancelEditBtn").hide();
                            $("#region").trigger('change');
                            loadAddresses(); // refresh list
                        } else {
                            alert(data.message || "Failed to save address.");
                        }
                    });
            });

            // ========= PROFILE FORM =========
            const saveBtn = $("#saveBtn");
            const accountForm = $("#accountForm");
            const passwordError = $("#passwordError");
            const originalValues = {};

            // Initialize original values (excluding password fields)
            accountForm.find("input").each(function () {
                const name = $(this).attr("name");
                if (name && !name.includes("password")) {
                    originalValues[name] = $(this).val();
                }
            });

            function validatePasswords() {
                const currentPassword = $("#current_password").val();
                const newPassword = $("#new_password").val();
                const confirmPassword = $("#confirm_password").val();

                // If any password field is filled, all must be filled
                if (currentPassword || newPassword || confirmPassword) {
                    if (!currentPassword || !newPassword || !confirmPassword) {
                        passwordError.text("All password fields must be filled to change password.").show();
                        return false;
                    }

                    // Check if new password and confirm password match
                    if (newPassword !== confirmPassword) {
                        passwordError.text("New password and confirm password do not match.").show();
                        return false;
                    }

                    // Check minimum length
                    if (newPassword.length < 6) {
                        passwordError.text("New password must be at least 6 characters long.").show();
                        return false;
                    }

                    passwordError.hide();
                    return true;
                }

                // No password change attempted
                passwordError.hide();
                return true;
            }

            function checkFormChanged() {
                let changed = false;
                
                // Check non-password fields
                accountForm.find("input").each(function () {
                    const name = $(this).attr("name");
                    if (name && !name.includes("password")) {
                        if ($(this).val() !== (originalValues[name] || "")) {
                            changed = true;
                            return false;
                        }
                    }
                });

                // Check if password fields are filled (password change attempt)
                const currentPassword = $("#current_password").val();
                const newPassword = $("#new_password").val();
                const confirmPassword = $("#confirm_password").val();
                
                if (currentPassword || newPassword || confirmPassword) {
                    changed = true;
                }

                return changed;
            }

            // Validate passwords on input
            $("#current_password, #new_password, #confirm_password").on("input", function () {
                validatePasswords();
                saveBtn.prop("disabled", !checkFormChanged() || !validatePasswords());
            });

            accountForm.find("input").not("#current_password, #new_password, #confirm_password").on("input", function () {
                saveBtn.prop("disabled", !checkFormChanged());
            });

            // ========= PASSWORD TOGGLE FUNCTIONALITY =========
            $(document).on("click", ".toggle-password", function() {
                const targetId = $(this).data("target");
                const passwordInput = $("#" + targetId);
                const icon = $(this).find("i");
                
                if (passwordInput.attr("type") === "password") {
                    passwordInput.attr("type", "text");
                    icon.removeClass("fa-eye").addClass("fa-eye-slash");
                } else {
                    passwordInput.attr("type", "password");
                    icon.removeClass("fa-eye-slash").addClass("fa-eye");
                }
            });

            accountForm.submit(function (e) {
                e.preventDefault();

                // Validate passwords before submission
                if (!validatePasswords()) {
                    return;
                }

                // Check if password change is being attempted
                const currentPassword = $("#current_password").val();
                const newPassword = $("#new_password").val();
                const confirmPassword = $("#confirm_password").val();

                // If password fields are filled, ensure all are filled
                if (currentPassword || newPassword || confirmPassword) {
                    if (!currentPassword || !newPassword || !confirmPassword) {
                        passwordError.text("All password fields must be filled to change password.").show();
                        return;
                    }
                }

                $.ajax({
                    url: "<?= base_url('UserCon/update_profile') ?>",
                    type: "POST",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (res) {
                        if (res.status === "success") {
                            alert("Profile updated!");
                            saveBtn.prop("disabled", true);
                            passwordError.hide();

                            // Clear password fields after successful update
                            $("#current_password, #new_password, #confirm_password").val("");

                            // Update original values
                            accountForm.find("input").each(function () {
                                const name = $(this).attr("name");
                                if (name && !name.includes("password")) {
                                    originalValues[name] = $(this).val();
                                }
                            });
                        } else {
                            alert(res.message || "Failed to update profile.");
                            if (res.message && res.message.toLowerCase().includes("password")) {
                                passwordError.text(res.message).show();
                            }
                        }
                    },
                    error: function (xhr) {
                        let errorMsg = "Error updating profile.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                            if (errorMsg.toLowerCase().includes("password")) {
                                passwordError.text(errorMsg).show();
                            }
                        }
                        alert(errorMsg);
                    }
                });
            });

            // ========= ANCHOR LINK HANDLING =========
            // Show account-details by default
            $('#account-details').addClass('active');
            $('.nav-item[href*="account-details"]').addClass('active');

            // Handle hash links for navigation
            function showSection(sectionId) {
                $('.content-section').removeClass('active');
                $('.nav-item').removeClass('active');
                
                const sectionMap = {
                    'orders': 'orders',
                    'addresses': 'addresses',
                    'account-details': 'account-details'
                };
                
                if (sectionMap[sectionId]) {
                    $('#' + sectionId).addClass('active');
                    $('.nav-item[href*="' + sectionId + '"]').addClass('active');
                } else {
                    // Default to account-details
                    $('#account-details').addClass('active');
                    $('.nav-item[href*="account-details"]').addClass('active');
                }
            }

            // Handle hash on page load
            if (window.location.hash) {
                const hash = window.location.hash.substring(1);
                showSection(hash);
            } else {
                // Default to account-details if no hash
                showSection('account-details');
            }

            // Handle anchor link clicks in sidebar
            $('.sidebar-nav a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                const hash = $(this).attr('href').substring(1);
                // Store current scroll position
                const scrollPos = window.pageYOffset || document.documentElement.scrollTop;
                showSection(hash);
                // Update URL without scrolling
                if (history.pushState) {
                    history.pushState(null, null, '#' + hash);
                } else {
                    window.location.hash = hash;
                }
                // Restore scroll position immediately
                window.scrollTo(0, scrollPos);
            });

            // ========= ADDRESS MODAL HANDLERS =========
            let currentAddressId = null;
            
            // Make close functions globally accessible
            window.closeAddressActionModal = function() {
                $("#addressActionModal").css("display", "none");
                currentAddressId = null;
            };
            
            window.closeAddressFormModal = function() {
                $("#addressFormModal").css("display", "none");
                $("#addressesAddressForm")[0].reset();
                $("#addressesEditAddressID").val('');
                $("#addressesIsDefault").prop('checked', false);
                $("#addressFormModalTitle").text("Add New Address");
                $("#addressFormEditButtons").hide();
                $("#addressFormAddButtons").show();
                currentAddressId = null;
            };
            
            // Edit icon click - directly open form modal with pre-filled data
            $(document).on("click", "#addresses .btn-edit-icon", function () {
                currentAddressId = $(this).data("address-id");

                $.ajax({
                    url: "<?= base_url('UserCon/get_address') ?>",
                    method: "GET",
                    data: { address_id: currentAddressId },
                    dataType: "json",
                    success: function (res) {
                        if (res.success && res.data) {
                            const addr = res.data;
                            
                            // Populate form fields
                            $("#addressesEditAddressID").val(addr.AddressID || '');
                            $("#addressesUnitHouseNumber").val(addr.UnitHouseNumber || '');
                            $("#addressesStreet").val(addr.Street || '');
                            $("#addressesSubdivision").val(addr.Subdivision || '');
                            $("#addressesBarangay").val(addr.Barangay || '');
                            $("#addressesRegion").val(addr.Region || '').trigger('change');
                            
                            setTimeout(function() {
                                $("#addressesProvince").val(addr.Province || '');
                                $("#addressesProvince").trigger('change');
                                
                                setTimeout(function() {
                                    $("#addressesCity").val(addr.City || '');
                                }, 100);
                            }, 100);
                            
                            $("#addressesCountry").val(addr.Country || 'Philippines');
                            $("#addressesZipCode").val(addr.ZipCode || '');
                            $("#addressesIsDefault").prop('checked', addr.IsDefault == 1);
                            
                            // Change form title
                            $("#addressFormModalTitle").text("Edit Address");
                            
                            // Show edit buttons, hide add buttons
                            $("#addressFormEditButtons").show();
                            $("#addressFormAddButtons").hide();
                            
                            // Store address ID for form modal actions
                            currentAddressId = addr.AddressID;
                            
                            // Open form modal
                            $("#addressFormModal").css("display", "flex");
                        }
                    }
                });
            });
            
            
            // Add New Address button
            $("#btnAddNewAddressMain").on("click", function() {
                $("#addressesAddressForm")[0].reset();
                $("#addressesEditAddressID").val('');
                $("#addressesIsDefault").prop('checked', false);
                $("#addressFormModalTitle").text("Add New Address");
                $("#addressFormEditButtons").hide();
                $("#addressFormAddButtons").show();
                $("#addressesRegion").trigger('change');
                currentAddressId = null;
                $("#addressFormModal").css("display", "flex");
            });
            
            // Form modal - Delete button (in edit mode)
            $("#formModalDeleteBtn").on("click", function() {
                if (!currentAddressId) return;
                
                if (!confirm("Are you sure you want to archive this address? It will be hidden but can be recovered later.")) {
                    return;
                }
                
                $.ajax({
                    url: "<?= base_url('UserCon/delete_address') ?>",
                    method: "POST",
                    data: { address_id: currentAddressId },
                    dataType: "json",
                    success: function (res) {
                        if (res.success) {
                            alert("Address archived successfully!");
                            closeAddressFormModal();
                            location.reload();
                        } else {
                            alert(res.message || "Failed to archive address.");
                        }
                    },
                    error: function() {
                        alert("Error deleting address. Please try again.");
                    }
                });
            });

            // Modals can only be closed by clicking buttons or X - no outside click or ESC key closing

            // Handle address form submission
            $("#addressesAddressForm").submit(function (e) {
                e.preventDefault();
                
                const fd = new FormData(this);
                const addressId = $("#addressesEditAddressID").val();
                const url = addressId ? "<?= base_url('UserCon/update_address') ?>" : "<?= base_url('UserCon/add_address') ?>";
                
                fetch(url, {
                    method: "POST",
                    body: fd
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (addressId) {
                                alert("Address updated successfully!");
                        } else {
                                alert("Address added successfully!");
                            }
                            closeAddressFormModal();
                            location.reload();
                        } else {
                            alert(data.message || "Failed to save address.");
                        }
                    });
            });

            // Region change handler for addresses section
            // Define region data for addresses section
            const addressesRegion3Provinces = {
                'Aurora': ['Baler', 'Casiguran', 'Dilasag', 'Dinalungan', 'Dingalan', 'Dipaculao', 'Maria Aurora', 'San Luis'],
                'Bataan': ['Abucay', 'Bagac', 'Balanga', 'Dinalupihan', 'Hermosa', 'Limay', 'Mariveles', 'Morong', 'Orani', 'Orion', 'Pilar', 'Samal'],
                'Bulacan': ['Angat', 'Balagtas', 'Baliuag', 'Bocaue', 'Bulakan', 'Bustos', 'Calumpit', 'Doña Remedios Trinidad', 'Guiguinto', 'Hagonoy', 'Malolos', 'Marilao', 'Meycauayan', 'Norzagaray', 'Obando', 'Pandi', 'Paombong', 'Plaridel', 'Pulilan', 'San Ildefonso', 'San Jose del Monte', 'San Miguel', 'San Rafael', 'Santa Maria', 'Valenzuela'],
                'Nueva Ecija': ['Aliaga', 'Bongabon', 'Cabanatuan', 'Cabiao', 'Carranglan', 'Cuyapo', 'Gabaldon', 'Gapan', 'General Mamerto Natividad', 'General Tinio', 'Guimba', 'Jaen', 'Laur', 'Licab', 'Llanera', 'Lupao', 'Muñoz', 'Nampicuan', 'Palayan', 'Pantabangan', 'Peñaranda', 'Quezon', 'Rizal', 'San Antonio', 'San Isidro', 'San Jose', 'San Leonardo', 'Santa Rosa', 'Santo Domingo', 'Talavera', 'Talugtug', 'Zaragoza'],
                'Pampanga': ['Angeles', 'Apalit', 'Arayat', 'Bacolor', 'Candaba', 'Floridablanca', 'Guagua', 'Lubao', 'Mabalacat', 'Macabebe', 'Magalang', 'Masantol', 'Mexico', 'Minalin', 'Porac', 'San Fernando', 'San Luis', 'San Simon', 'Santa Ana', 'Santa Rita', 'Santo Tomas', 'Sasmuan'],
                'Tarlac': ['Anao', 'Bamban', 'Camiling', 'Capas', 'Concepcion', 'Gerona', 'La Paz', 'Mayantoc', 'Moncada', 'Paniqui', 'Pura', 'Ramos', 'San Clemente', 'San Jose', 'San Manuel', 'Santa Ignacia', 'Tarlac City', 'Victoria'],
                'Zambales': ['Botolan', 'Cabangan', 'Candelaria', 'Castillejos', 'Iba', 'Masinloc', 'Olongapo', 'Palauig', 'San Antonio', 'San Felipe', 'San Marcelino', 'San Narciso', 'Santa Cruz', 'Subic']
            };

            const addressesRegion4AProvinces = {
                'Batangas': ['Agoncillo', 'Alitagtag', 'Balayan', 'Balete', 'Bauan', 'Calaca', 'Calatagan', 'Cuenca', 'Ibaan', 'Laurel', 'Lemery', 'Lian', 'Lipa', 'Lobo', 'Mabini', 'Malvar', 'Mataasnakahoy', 'Nasugbu', 'Padre Garcia', 'Rosario', 'San Jose', 'San Juan', 'San Luis', 'San Nicolas', 'San Pascual', 'Santa Teresita', 'Santo Tomas', 'Taal', 'Talisay', 'Tanauan', 'Taysan', 'Tingloy', 'Tuy'],
                'Cavite': ['Alfonso', 'Amadeo', 'Bacoor', 'Carmona', 'Cavite City', 'Dasmariñas', 'General Emilio Aguinaldo', 'General Mariano Alvarez', 'General Trias', 'Imus', 'Indang', 'Kawit', 'Magallanes', 'Maragondon', 'Mendez', 'Naic', 'Noveleta', 'Rosario', 'Silang', 'Tagaytay', 'Tanza', 'Ternate', 'Trece Martires', 'Tagaytay'],
                'Laguna': ['Alaminos', 'Bay', 'Biñan', 'Cabuyao', 'Calamba', 'Calauan', 'Cavinti', 'Famy', 'Kalayaan', 'Liliw', 'Los Baños', 'Luisiana', 'Lumban', 'Mabitac', 'Magdalena', 'Majayjay', 'Nagcarlan', 'Paete', 'Pagsanjan', 'Pakil', 'Pangil', 'Pila', 'Rizal', 'San Pablo', 'San Pedro', 'Santa Cruz', 'Santa Maria', 'Santa Rosa', 'Siniloan', 'Victoria'],
                'Quezon': ['Agdangan', 'Alabat', 'Atimonan', 'Buenavista', 'Burdeos', 'Calauag', 'Candelaria', 'Catanauan', 'Dolores', 'General Luna', 'General Nakar', 'Guinayangan', 'Gumaca', 'Infanta', 'Jomalig', 'Lopez', 'Lucban', 'Lucena', 'Macalelon', 'Mauban', 'Mulanay', 'Padre Burgos', 'Pagbilao', 'Panukulan', 'Patnanungan', 'Perez', 'Pitogo', 'Plaridel', 'Polillo', 'Quezon', 'Real', 'Sampaloc', 'San Andres', 'San Antonio', 'San Francisco', 'San Narciso', 'Sariaya', 'Tagkawayan', 'Tayabas', 'Tiaong', 'Unisan'],
                'Rizal': ['Angono', 'Antipolo', 'Baras', 'Binangonan', 'Cainta', 'Cardona', 'Jalajala', 'Morong', 'Pililla', 'Rodriguez', 'San Mateo', 'Tanay', 'Taytay', 'Teresa']
            };

            const addressesMetroManilaCities = [
                'Caloocan', 'Las Piñas', 'Makati', 'Malabon', 'Mandaluyong',
                'Manila', 'Marikina', 'Muntinlupa', 'Navotas', 'Parañaque',
                'Pasay', 'Pasig', 'Quezon City', 'San Juan', 'Taguig', 'Valenzuela'
            ];

            $("#addressesRegion").on("change", function() {
                const selectedRegion = $(this).val();
                const provinceSelect = $("#addressesProvince");
                const citySelect = $("#addressesCity");
                
                // Clear existing options
                provinceSelect.html('<option value="">Select Province</option>');
                citySelect.html('<option value="">Select City/Municipality</option>');
                
                if (selectedRegion === "NCR") {
                    // For NCR, set province to Metro Manila
                    provinceSelect.html('<option value="Metro Manila">Metro Manila</option>');
                    provinceSelect.val("Metro Manila");
                    
                    // Populate cities
                    addressesMetroManilaCities.forEach(city => {
                        citySelect.append(`<option value="${city}">${city}</option>`);
                    });
                } else if (selectedRegion === "Region III") {
                    // Only show Bulacan province for Region III
                    provinceSelect.html('<option value="Bulacan">Bulacan</option>');
                    provinceSelect.val("Bulacan").trigger('change');
                } else if (selectedRegion === "Region IV-A") {
                    // Populate Region IV-A provinces
                    Object.keys(addressesRegion4AProvinces).forEach(province => {
                        provinceSelect.append(`<option value="${province}">${province}</option>`);
                    });
                }
                
                // Re-apply dropdown limit after options are populated
                setTimeout(function() {
                    setupDropdownLimit("#addressesProvince");
                    setupDropdownLimit("#addressesCity");
                }, 50);
            });

            // Province change handler for addresses section
            $("#addressesProvince").on("change", function() {
                const selectedProvince = $(this).val();
                const selectedRegion = $("#addressesRegion").val();
                const citySelect = $("#addressesCity");
                
                // Clear cities
                citySelect.html('<option value="">Select City/Municipality</option>');
                
                if (selectedProvince === "Metro Manila") {
                    // Populate Metro Manila cities
                    addressesMetroManilaCities.forEach(city => {
                        citySelect.append(`<option value="${city}">${city}</option>`);
                    });
                } else if (selectedRegion === "Region III" && addressesRegion3Provinces[selectedProvince]) {
                    // Populate Region III cities
                    addressesRegion3Provinces[selectedProvince].forEach(city => {
                        citySelect.append(`<option value="${city}">${city}</option>`);
                    });
                } else if (selectedRegion === "Region IV-A" && addressesRegion4AProvinces[selectedProvince]) {
                    // Populate Region IV-A cities
                    addressesRegion4AProvinces[selectedProvince].forEach(city => {
                        citySelect.append(`<option value="${city}">${city}</option>`);
                    });
                }
                
                // Re-apply dropdown limit after options are populated
                setTimeout(function() {
                    setupDropdownLimit("#addressesCity");
                }, 50);
            });

            // Limit dropdowns to show 5 options at a time with scrolling (only if more than 5 options exist)
            function setupDropdownLimit(selectId) {
                const $select = $(selectId);
                let isOpen = false;
                let timeoutId = null;
                
                function checkAndSetSize() {
                    const optionCount = $select.find('option').length;
                    // Only apply size if there are more than 5 options
                    if (optionCount > 5 && !isOpen) {
                        isOpen = true;
                        $select.attr("size", "5");
                        // Scroll to selected option if any
                        const selectedIndex = $select[0].selectedIndex;
                        if (selectedIndex > 0) {
                            setTimeout(function() {
                                $select[0].scrollTop = (selectedIndex - 1) * 40; // Approximate option height
                            }, 50);
                        }
                    }
                }
                
                function resetSize() {
                    if (isOpen) {
                        isOpen = false;
                        $select.removeAttr("size");
                    }
                    if (timeoutId) {
                        clearTimeout(timeoutId);
                        timeoutId = null;
                    }
                }
                
                // On focus, set size if needed (this happens when dropdown opens)
                $select.on("focus", function() {
                    timeoutId = setTimeout(function() {
                        checkAndSetSize();
                    }, 100);
                });
                
                // On blur (when losing focus), reset size
                $select.on("blur", function() {
                    resetSize();
                });
                
                // When an option is selected, reset size
                $select.on("change", function() {
                    resetSize();
                });
                
                // Handle click to detect when dropdown is opened
                $select.on("click", function() {
                    // Use a small delay to check if dropdown opened
                    setTimeout(function() {
                        if ($select.is(":focus")) {
                            checkAndSetSize();
                        }
                    }, 50);
                });
            }

            // Apply to all three address dropdowns
            setupDropdownLimit("#addressesRegion");
            setupDropdownLimit("#addressesProvince");
            setupDropdownLimit("#addressesCity");
        });
    </script>

</body>
