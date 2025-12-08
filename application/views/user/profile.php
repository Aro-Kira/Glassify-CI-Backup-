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
                        <input type="text" id="address" name="address" readonly
                            value="<?= isset($addresses['Shipping']) ? $addresses['Shipping']->AddressLine : '' ?>"
                            placeholder="Select an address">
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
                                        <th>Address</th>
                                        <th>City</th>
                                        <th>Province</th>
                                        <th>Country</th>
                                        <th>Zip</th>
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

                    <!-- Add new address -->
                    <div class="block-section">
                        <h4 class="section-title">Add New Address</h4>

                        <form id="newAddressForm" class="address-form">
                            <div class="grid-2">
                                <input type="text" name="AddressLine" placeholder="Address Line" required>
                                <input type="text" name="City" placeholder="City" required>
                            </div>
                            <div class="grid-2">
                                <input type="text" name="Province" placeholder="Province" required>
                                <input type="text" name="Country" placeholder="Country" required>
                            </div>
                            <div class="grid-2">
                                <input type="text" name="ZipCode" placeholder="Zip Code" required>
                            </div>

                            <button type="submit" class="btn-add">+ Add Address</button>
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
                            tbody.html("<tr><td colspan='6' style='text-align:center'>No addresses found.</td></tr>");
                            return;
                        }

                        res.data.forEach(a => {
                            tbody.append(`
                        <tr>
                            <td>${a.AddressLine}</td>
                            <td>${a.City}</td>
                            <td>${a.Province}</td>
                            <td>${a.Country}</td>
                            <td>${a.ZipCode}</td>
                            <td>
                                <button class="btn-select select-address"
                                    data-address="${a.AddressLine}, ${a.City}, ${a.Province}, ${a.Country}, ${a.ZipCode}">
                                    Select
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
                $("#address").val($(this).data("address"));
                modal.hide();
            });

            // ========= ADD NEW ADDRESS (AJAX + AUTO REFRESH) =========
            $("#newAddressForm").submit(function (e) {
                e.preventDefault();

                const fd = new FormData(this);

                fetch("<?= base_url('UserCon/add_address') ?>", {
                    method: "POST",
                    body: fd
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            $("#address").val(data.full_address);
                            this.reset();
                            loadAddresses(); // refresh list
                        } else {
                            alert(data.message || "Failed to add address.");
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