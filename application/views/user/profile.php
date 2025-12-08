<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/user/profile.css'); ?>">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



<body>
    <main class="account-settings">
        <h2 class="settings-title">Account Settings</h2>
        <section class="settings-container">

            <!-- Left: Form -->
            <section class="settings-form">
                <form id="accountForm">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname"
                        value="<?= isset($user) ? $user->First_Name : '' ?>">

                    <label for="middlename">Middle Name</label>
                    <input type="text" id="middlename" name="middlename"
                        value="<?= isset($user) ? $user->Middle_Name : '' ?>">

                    <label for="lastname">Surname</label>
                    <input type="text" id="lastname" name="lastname"
                        value="<?= isset($user) ? $user->Last_Name : '' ?>">

                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= isset($user) ? $user->Email : '' ?>">

                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?= isset($user) ? $user->PhoneNum : '' ?>">

                    <label for="address">Address</label>
                    <div class="input-group">
                        <?php
                        $fullAddress = '';
                        if (isset($addresses['Shipping']) && $addresses['Shipping']) {
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
                            $fullAddress = !empty($addressParts) ? implode(', ', $addressParts) : ($addr->AddressLine ?? '');
                        }
                        ?>
                        <textarea id="address" name="address" readonly rows="3" 
                            placeholder="Select an address" style="resize: vertical; min-height: 60px;"><?= htmlspecialchars($fullAddress) ?></textarea>
                        <button type="button" id="chooseAddressBtn" title="Select address">
                            <!-- Location Pin Icon SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M8 0a5.53 5.53 0 0 0-5.5 5.5c0 4.625 5.5 10.5 5.5 10.5s5.5-5.875 5.5-10.5A5.53 5.53 0 0 0 8 0zm0 7.5a2 2 0 1 1 0-4 2 2 0 0 1 0 4z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Password Change Section -->
                    <div class="password-section">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Enter your current password">
                        
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                        
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                        <small id="passwordError" style="color: #dc3545; display: none; margin-top: 5px;"></small>
                    </div>

                    <div class="form-buttons">
                        <button type="button" class="btn-cancel">Cancel</button>
                        <button type="submit" class="btn-save" id="saveBtn" disabled>Save Changes</button>
                    </div>
                </form>
            </section>

            <!-- Right: Profile Image -->
            <section class="settings-photo">
                <img src="<?= isset($user->ImageUrl) && !empty($user->ImageUrl) ? base_url($user->ImageUrl) : base_url('assets/images/img-page/pfp.png') ?>"
                    class="profile-img" id="profilePreview">

                <div class="photo-buttons">
                    <button type="button" id="changePhotoBtn">Change Photo</button>
                    <input type="file" id="uploadPhoto" accept="image/*" style="display:none;">
                    <button type="button" id="deletePhotoBtn">Delete Photo</button>
                </div>
            </section>

        </section>

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
                                <label for="unitHouseNumber">Unit/House Number</label>
                                <input type="text" name="UnitHouseNumber" id="unitHouseNumber" placeholder="Enter Unit/House Number (optional)">
                            </div>
                            
                            <div class="form-field-group">
                                <label for="street">Street</label>
                                <input type="text" name="Street" id="street" placeholder="Enter Street (optional)">
                            </div>
                            
                            <div class="form-field-group">
                                <label for="subdivision">Subdivision</label>
                                <input type="text" name="Subdivision" id="subdivision" placeholder="Enter Subdivision/Village (optional)">
                            </div>
                            
                            <div class="form-field-group">
                                <label for="barangay">Barangay <span class="required-asterisk">*</span></label>
                                <input type="text" name="Barangay" id="barangay" placeholder="Enter Barangay" required>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="city">City/Municipality <span class="required-asterisk">*</span></label>
                                <select name="City" id="city" required>
                                    <option value="">Select City/Municipality</option>
                                </select>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="province">Province <span class="required-asterisk">*</span></label>
                                <select name="Province" id="province" required>
                                    <option value="">Select Province</option>
                                </select>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="region">Region <span class="required-asterisk">*</span></label>
                                <select name="Region" id="region" required>
                                    <option value="">Select Region</option>
                                    <option value="NCR">NCR (National Capital Region)</option>
                                    <option value="Region I">Region I (Ilocos Region)</option>
                                    <option value="Region II">Region II (Cagayan Valley)</option>
                                    <option value="Region III">Region III (Central Luzon)</option>
                                    <option value="Region IV-A">Region IV-A (CALABARZON)</option>
                                    <option value="Region IV-B">Region IV-B (MIMAROPA)</option>
                                    <option value="Region V">Region V (Bicol Region)</option>
                                    <option value="Region VI">Region VI (Western Visayas)</option>
                                    <option value="Region VII">Region VII (Central Visayas)</option>
                                    <option value="Region VIII">Region VIII (Eastern Visayas)</option>
                                    <option value="Region IX">Region IX (Zamboanga Peninsula)</option>
                                    <option value="Region X">Region X (Northern Mindanao)</option>
                                    <option value="Region XI">Region XI (Davao Region)</option>
                                    <option value="Region XII">Region XII (SOCCSKSARGEN)</option>
                                    <option value="Region XIII">Region XIII (Caraga)</option>
                                    <option value="BARMM">BARMM (Bangsamoro Autonomous Region)</option>
                                    <option value="CAR">CAR (Cordillera Administrative Region)</option>
                                </select>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="country">Country</label>
                                <input type="text" name="Country" id="country" value="Philippines" readonly>
                            </div>
                            
                            <div class="form-field-group">
                                <label for="zipCode">Zip Code <span class="required-asterisk">*</span></label>
                                <input type="text" name="ZipCode" id="zipCode" placeholder="Enter Zip Code" required>
                            </div>
                            
                            <div class="form-field-group checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="IsDefault" id="isDefault" value="1">
                                    <span>Set as default address</span>
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
                } else if (selectedRegion) {
                    // For other regions, leave empty for now (to be populated later)
                    provinceSelect.html('<option value="">Select Province</option>');
                }
            });

            // ========= PROVINCE CHANGE HANDLER =========
            $("#province").on("change", function() {
                const selectedProvince = $(this).val();
                const citySelect = $("#city");
                
                if (selectedProvince === "Metro Manila") {
                    // Populate Metro Manila cities
                    citySelect.html('<option value="">Select City/Municipality</option>');
                    metroManilaCities.forEach(city => {
                        citySelect.append(`<option value="${city}">${city}</option>`);
                    });
                } else if (selectedProvince) {
                    // For other provinces, leave empty for now
                    citySelect.html('<option value="">Select City/Municipality</option>');
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

            $("#address").on("input change", function () {
                saveBtn.prop("disabled", !checkFormChanged());
            });

            $(".btn-cancel").click(function () {
                accountForm[0].reset();
                passwordError.hide();
                saveBtn.prop("disabled", true);
                // Reset original values
                accountForm.find("input").each(function () {
                    const name = $(this).attr("name");
                    if (name && !name.includes("password")) {
                        originalValues[name] = $(this).val();
                    }
                });
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

            // ========= PROFILE PHOTO =========
            $("#changePhotoBtn").click(() => $("#uploadPhoto").click());

            $("#uploadPhoto").change(function () {
                if (!this.files || !this.files[0]) {
                    return;
                }

                const fd = new FormData();
                fd.append("photo", this.files[0]);

                $.ajax({
                    url: "<?= base_url('UserCon/upload_photo') ?>",
                    type: "POST",
                    data: fd,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (res) {
                        if (res.status === "success") {
                            $("#profilePreview").attr("src", res.image);
                            alert("Photo updated!");
                        } else {
                            alert(res.message || "Failed to upload photo");
                        }
                    },
                    error: function (xhr, status, error) {
                        let errorMsg = "Error uploading photo. ";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg += xhr.responseJSON.message;
                        } else {
                            errorMsg += error || "Please try again.";
                        }
                        alert(errorMsg);
                        console.error("Upload error:", xhr.responseText);
                    }
                });
            });

            $("#deletePhotoBtn").click(function () {
                if (confirm("Delete profile photo?")) {
                    $.post("<?= base_url('UserCon/delete_photo') ?>", {}, function (res) {
                        if (res.status === "success") {
                            $("#profilePreview").attr("src", res.image);
                            alert("Photo deleted!");
                        } else {
                            alert(res.message);
                        }
                    }, "json");
                }
            });

        });
    </script>

</body>