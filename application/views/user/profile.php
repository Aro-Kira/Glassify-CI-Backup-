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
                    <a href="#account-details" class="nav-item <?php echo $current_section === 'account-details' ? 'active' : ''; ?>" onclick="event.preventDefault(); switchSection('account-details', this)">
                        <i class="fas fa-user"></i>
                        <span>Account details</span>
                    </a>
                    <div class="nav-group <?php echo $current_section === 'orders' ? 'expanded' : ''; ?>" id="orders-nav-group">
                        <a href="#orders" class="nav-item <?php echo $current_section === 'orders' ? 'active' : ''; ?>" onclick="event.preventDefault(); toggleOrdersDropdown(event, this)">
                            <i class="fas fa-envelope"></i>
                            <span>Orders</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <div class="nav-sub-items">
                            <a href="#ongoing-orders" class="nav-sub-item" onclick="event.preventDefault(); showOrderCategory('ongoing-orders', this)">Ongoing Orders</a>
                            <a href="#completed-orders" class="nav-sub-item" onclick="event.preventDefault(); showOrderCategory('completed-orders', this)">Completed Orders</a>
                            <a href="#cancelled-orders" class="nav-sub-item" onclick="event.preventDefault(); showOrderCategory('cancelled-orders', this)">Cancelled Orders</a>
                        </div>
                    </div>
                    <a href="#addresses" class="nav-item <?php echo $current_section === 'addresses' ? 'active' : ''; ?>" onclick="event.preventDefault(); switchSection('addresses', this)">
                        <i class="fas fa-home"></i>
                        <span>Addresses</span>
                    </a>
                    <a href="#user-experience" class="nav-item <?php echo $current_section === 'user-experience' ? 'active' : ''; ?>" onclick="event.preventDefault(); switchSection('user-experience', this)">
                        <i class="fas fa-user-cog"></i>
                        <span>Skill Level</span>
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
                    <div class="orders-header">
                        <h3>Your Orders</h3>
                    </div>

                    <?php
                    // Group orders by status categories
                    $ongoing_orders = [];
                    $completed_orders = [];
                    $cancelled_orders = [];

                    if (!empty($orders_with_products)) {
                        foreach ($orders_with_products as $order) {
                            $status_lower = strtolower(trim($order->Status));
                            if ($status_lower === 'completed' || $status_lower === 'delivered') {
                                $completed_orders[] = $order;
                            } elseif ($status_lower === 'cancelled' || $status_lower === 'returned') {
                                $cancelled_orders[] = $order;
                            } else {
                                $ongoing_orders[] = $order;
                            }
                        }
                    }

                    // Helper to render order table
                    function render_order_table($orders, $category_id) {
                        if (empty($orders)) {
                            echo '<p class="empty-msg">No ' . strtolower(str_replace('-', ' ', $category_id)) . ' found.</p>';
                            return;
                        }
                        ?>
                        <div class="table-wrapper">
                            <table class="styled-table">
                                <thead>
                                    <tr>
                                        <th>Order Number</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Total Amount</th>
                                        <th>Order Status</th>
                                        <th>Order Date</th>
                                        <th>View Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $display_order = '';
                                                if (!empty($order->OrderNumber)) {
                                                    $on = $order->OrderNumber;
                                                    if (preg_match('/^\d+$/', $on)) {
                                                        $display_order = 'GI' . str_pad($on, 3, '0', STR_PAD_LEFT);
                                                    } else {
                                                        $display_order = htmlspecialchars($on);
                                                    }
                                                } elseif (!empty($order->OrderID)) {
                                                    $display_order = htmlspecialchars($order->OrderID);
                                                } else {
                                                    $display_order = '-';
                                                }
                                                echo $display_order;
                                                ?>
                                            </td>
                                            <td style="display: flex; align-items: center; gap: 10px;">
                                                <?php 
                                                $image_raw = $order->ImageUrl ?? '';
                                                $placeholder_svg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                                                $product_img = $placeholder_svg;
                                                if (!empty($image_raw)) {
                                                    $decoded = json_decode($image_raw, true);
                                                    $first_image = '';
                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                                                        $first_image = $decoded[0];
                                                    } else {
                                                        $first_image = $image_raw;
                                                    }
                                                    if (!empty($first_image) && strpos($first_image, 'broken-image-icon') === false) {
                                                        if (strpos($first_image, 'http') === 0) {
                                                            $product_img = $first_image;
                                                        } else if (strpos($first_image, 'assets/') === 0 || strpos($first_image, 'uploads/') === 0) {
                                                            $product_img = base_url($first_image);
                                                        } else {
                                                            $product_img = base_url('uploads/products/' . basename($first_image));
                                                        }
                                                    }
                                                }
                                                ?>
                                                <img src="<?= $product_img ?>" alt="Product" class="product-thumb" style="width: 48px; height: 48px; object-fit: cover; border-radius: 4px;">
                                                <span><?= htmlspecialchars($order->ProductName ?? 'Custom Order') ?></span>
                                            </td>
                                            <?php
                                            // Ensure quantity has a sensible fallback
                                            $qty = (!empty($order->Quantity) && intval($order->Quantity) > 0) ? intval($order->Quantity) : 1;
                                            // Compute total if missing or zero
                                            $total = (!empty($order->TotalAmount) && floatval($order->TotalAmount) > 0) ? floatval($order->TotalAmount) : (floatval($order->EstimatePrice ?? 0) * $qty);
                                            ?>
                                            <td><?= $qty ?></td>
                                            <td style="font-weight: 600; color: #0f2b46;">₱<?= number_format($total, 2) ?></td>
                                            <td>
                                                <?php
                                                // Unified order status display - all orders follow same process
                                                $status = strtolower(trim($order->Status));
                                                if ($status === 'booking requested') {
                                                    echo 'Booking Requested';
                                                } elseif ($status === 'ocular visit' || $status === 'ocular pending') {
                                                    echo 'Ocular Visit';
                                                } elseif ($status === 'in fabrication') {
                                                    echo 'In Fabrication';
                                                } elseif ($status === 'ready for installation' || $status === 'installation') {
                                                    echo 'Installation';
                                                } elseif ($status === 'completed') {
                                                    echo 'Completed';
                                                } elseif ($status === 'approved') {
                                                    echo 'Approved';
                                                } elseif ($status === 'pending review') {
                                                    echo 'Pending Review';
                                                } else {
                                                    echo ucfirst($status);
                                                }
                                                ?>
                                            </td>
                                            <td><?= !empty($order->OrderDate) ? date('Y-m-d', strtotime($order->OrderDate)) : '' ?></td>
                                            <td>
                                                <a href="<?= base_url('track_order?order=' . $order->OrderID) ?>" class="btn-view-details">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    }
                    ?>

                    <!-- Order Content Area - No left menu, just content -->
                    <div class="order-content-wrapper">
                        <div id="ongoing-orders" class="category-content active">
                            <h4 style="margin: 0 0 20px 0; color: #0f2b46;">Ongoing Orders (<?= count($ongoing_orders) ?>)</h4>
                            <?php render_order_table($ongoing_orders, 'ongoing-orders'); ?>
                        </div>
                        <div id="completed-orders" class="category-content">
                            <h4 style="margin: 0 0 20px 0; color: #0f2b46;">Completed Orders (<?= count($completed_orders) ?>)</h4>
                            <?php render_order_table($completed_orders, 'completed-orders'); ?>
                        </div>
                        <div id="cancelled-orders" class="category-content">
                            <h4 style="margin: 0 0 20px 0; color: #0f2b46;">Cancelled Orders (<?= count($cancelled_orders) ?>)</h4>
                            <?php render_order_table($cancelled_orders, 'cancelled-orders'); ?>
                        </div>
                    </div>

                    <script>
                        function selectOrderCategory(categoryId) {
                            // Hide all category contents
                            document.querySelectorAll('.category-content').forEach(content => {
                                content.classList.remove('active');
                            });
                            
                            // Show the selected category content
                            const targetContent = document.getElementById(categoryId);
                            if (targetContent) {
                                targetContent.classList.add('active');
                            }
                        }

                        function toggleOrdersDropdown(event, element) {
                            event.preventDefault();
                            event.stopPropagation();
                            
                            const group = element.closest('.nav-group');
                            
                            // Toggle dropdown expanded state
                            if (group.classList.contains('expanded')) {
                                // Already expanded, just show orders section
                                switchSection('orders', element);
                            } else {
                                // Expand the dropdown
                                group.classList.add('expanded');
                                
                                // Show the orders section
                                switchSection('orders', element);
                            }
                        }

                        function switchSection(sectionId, element) {
                            // Hide all sections
                            document.querySelectorAll('.content-section').forEach(sec => {
                                sec.style.display = 'none';
                            });
                            
                            // Show target section
                            const target = document.getElementById(sectionId);
                            if (target) target.style.display = 'block';
                            
                            // Update active state in sidebar
                            document.querySelectorAll('.nav-item').forEach(item => {
                                item.classList.remove('active');
                            });
                            
                            // Remove active from any nav-sub-item
                            document.querySelectorAll('.nav-sub-item').forEach(item => {
                                item.classList.remove('active');
                            });

                            element.classList.add('active');

                            // Close orders dropdown if switching away from orders
                            if (sectionId !== 'orders') {
                                document.getElementById('orders-nav-group').classList.remove('expanded');
                            }
                        }

                        function showOrderCategory(categoryId, element) {
                            // This function is called from sidebar sub-items
                            // Ensure Orders section is visible
                            document.querySelectorAll('.content-section').forEach(sec => {
                                sec.style.display = 'none';
                            });
                            document.getElementById('orders').style.display = 'block';

                            // Update category selection using the selectOrderCategory function
                            selectOrderCategory(categoryId);
                            
                            // Find and activate the corresponding tab in the left menu
                            const tabItem = document.querySelector(`.order-category-item[data-category="${categoryId}"]`);
                            if (tabItem) {
                                document.querySelectorAll('.order-category-item').forEach(item => {
                                    item.classList.remove('active');
                                });
                                tabItem.classList.add('active');
                            }
                            
                            // Deactivate ALL main nav items
                            document.querySelectorAll('.nav-item').forEach(item => {
                                item.classList.remove('active');
                            });
                            
                            // Deactivate all sub-items
                            document.querySelectorAll('.nav-sub-item').forEach(item => {
                                item.classList.remove('active');
                            });
                            
                            // Expand the orders dropdown
                            const ordersGroup = document.getElementById('orders-nav-group');
                            if (ordersGroup) {
                                ordersGroup.classList.add('expanded');
                            }
                            
                            // Activate ONLY the selected sub-item (this will be blue)
                            element.classList.add('active');
                            
                            // Scroll to top
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    </script>
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

                <!-- Skill Level Section -->
                <div id="user-experience" class="content-section">
                    <h3>Skill Level</h3>
                    <p class="section-description">Tell us about your skill level so we can guide you properly.</p>
                    
                    <?php if (isset($setup_status) && $setup_status !== 'completed'): ?>
                        <div class="incomplete-setup-notice" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; margin-bottom: 1.5rem;">
                            <i class="fas fa-exclamation-circle" style="color: #856404;"></i>
                            <span style="color: #856404;">Your experience setup is incomplete. Please complete all required fields.</span>
                        </div>
                    <?php endif; ?>

                    <form id="experienceForm" class="experience-form">
                        <input type="hidden" name="role" id="experience-role-input" value="<?php echo isset($customer_role) ? htmlspecialchars($customer_role) : 'beginner'; ?>">
                        <!-- Role (read-only with request change) -->
                        <div class="experience-field">
                            <label class="experience-label">Role <span class="required">*</span></label>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span id="user-role-badge" class="role-badge"><?php echo isset($customer_role) ? htmlspecialchars(ucfirst($customer_role)) : 'Beginner'; ?></span>
                                <button type="button" id="btn-request-role" class="btn-link">Request change</button>
                            </div>
                        </div>

                        <?php
                        // Extract professional type from experience_data
                        $professional_type = '';
                        if (isset($experience_data) && isset($experience_data['professional_type'])) {
                            $prof_type = $experience_data['professional_type'];
                            // Check if it's one of the predefined types
                            if (in_array(strtolower($prof_type), ['architect', 'engineer', 'contractor'])) {
                                $professional_type = strtolower($prof_type);
                            } else {
                                $professional_type = 'other';
                            }
                        }
                        ?>

                        <!-- Professional Type (shown only if Professional) -->
                        <div class="experience-field professional-only" style="<?php echo (isset($customer_role) && $customer_role === 'professional') ? '' : 'display: none;'; ?>">
                            <label class="experience-label">Professional Type <span class="required">*</span></label>
                            <div class="experience-options">
                                <label class="experience-option">
                                    <input type="radio" name="professional_type" value="architect" 
                                        <?php echo ($professional_type === 'architect') ? 'checked' : ''; ?>>
                                    <span>Architect</span>
                                </label>
                                <label class="experience-option">
                                    <input type="radio" name="professional_type" value="engineer" 
                                        <?php echo ($professional_type === 'engineer') ? 'checked' : ''; ?>>
                                    <span>Engineer</span>
                                </label>
                                <label class="experience-option">
                                    <input type="radio" name="professional_type" value="contractor" 
                                        <?php echo ($professional_type === 'contractor') ? 'checked' : ''; ?>>
                                    <span>Contractor</span>
                                </label>
                                <label class="experience-option">
                                    <input type="radio" name="professional_type" value="other" id="professional-type-other"
                                        <?php echo ($professional_type === 'other') ? 'checked' : ''; ?>>
                                    <span>Other</span>
                                </label>
                            </div>
                            <div id="other-professional-type-input" style="<?php echo ($professional_type === 'other' && isset($customer_role) && $customer_role === 'professional') ? '' : 'display: none;'; ?> margin-top: 10px;">
                                <input type="text" name="other_professional_type" id="other_professional_type" 
                                    placeholder="Please specify your professional type" 
                                    value="<?php echo (isset($experience_data) && isset($experience_data['professional_type']) && !in_array(strtolower($experience_data['professional_type']), ['architect', 'engineer', 'contractor'])) ? htmlspecialchars($experience_data['professional_type']) : ''; ?>"
                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            </div>
                        </div>

                        <!-- BEGINNER FIELDS -->
                        <div class="beginner-fields" style="<?php echo (isset($customer_role) && $customer_role === 'beginner') ? '' : 'display: none;'; ?>">
                            <!-- Question 2: Ever ordered product with specifications -->
                            <div class="experience-field">
                                <label class="experience-label">Have you ever ordered a product that required specifications?</label>
                                <div class="experience-options">
                                    <label class="experience-option">
                                        <input type="radio" name="beginner_experience" value="first_time" 
                                            <?php echo (isset($experience_data) && isset($experience_data['experience']) && $experience_data['experience'] === 'first_time') ? 'checked' : ''; ?>>
                                        <span>No, this is my first time</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="beginner_experience" value="once_twice" 
                                            <?php echo (isset($experience_data) && isset($experience_data['experience']) && $experience_data['experience'] === 'once_twice') ? 'checked' : ''; ?>>
                                        <span>Yes, once or twice</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="beginner_experience" value="several_times" 
                                            <?php echo (isset($experience_data) && isset($experience_data['experience']) && $experience_data['experience'] === 'several_times') ? 'checked' : ''; ?>>
                                        <span>Yes, several times</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 3: Familiar with specifications -->
                            <div class="experience-field">
                                <label class="experience-label">Are you familiar with reading or providing product specifications (sizes, profiles, materials)?</label>
                                <div class="experience-options">
                                    <label class="experience-option">
                                        <input type="radio" name="beginner_specifications" value="not_at_all" 
                                            <?php echo (isset($experience_data) && isset($experience_data['specifications_knowledge']) && $experience_data['specifications_knowledge'] === 'not_at_all') ? 'checked' : ''; ?>>
                                        <span>Not at all</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="beginner_specifications" value="a_little" 
                                            <?php echo (isset($experience_data) && isset($experience_data['specifications_knowledge']) && $experience_data['specifications_knowledge'] === 'a_little') ? 'checked' : ''; ?>>
                                        <span>A little</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="beginner_specifications" value="yes_need_guidance" 
                                            <?php echo (isset($experience_data) && isset($experience_data['specifications_knowledge']) && $experience_data['specifications_knowledge'] === 'yes_need_guidance') ? 'checked' : ''; ?>>
                                        <span>Yes, but I still need guidance</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 4: Customization handling -->
                            <div class="experience-field">
                                <label class="experience-label">How would you like your product customization to be handled after the ocular visit?</label>
                                <p style="font-size: 0.9em; color: #666; margin-top: 0.25rem; margin-bottom: 0.75rem;">Note: Beginner users cannot create customization themselves. This affects review/approval flow only.</p>
                                <div class="experience-options">
                                    <label class="experience-option">
                                        <input type="radio" name="beginner_customization_handling" value="prepare_for_me" 
                                            <?php echo (isset($experience_data) && isset($experience_data['customization_handling']) && $experience_data['customization_handling'] === 'prepare_for_me') ? 'checked' : ''; ?>>
                                        <span>I prefer GlassWorth Builders to prepare the customization for me</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="beginner_customization_handling" value="review_and_approve" 
                                            <?php echo (isset($experience_data) && isset($experience_data['customization_handling']) && $experience_data['customization_handling'] === 'review_and_approve') ? 'checked' : ''; ?>>
                                        <span>I want to review and approve the customization prepared for me</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Read-only notice for Beginners -->
                            <div class="info-box" style="background: #e7f3ff; border-left: 4px solid #2196F3; padding: 1rem; margin-top: 1rem;">
                                <i class="fas fa-info-circle" style="color: #2196F3;"></i>
                                <span style="color: #1976D2; margin-left: 0.5rem;">Product customization will be prepared after your ocular visit.</span>
                            </div>
                        </div>

                        <!-- Role Request Modal -->
                        <div id="roleRequestModal" class="modal" style="display:none; position:fixed; inset:0; align-items:center; justify-content:center; z-index:10000;">
                            <div style="background:#fff; width:520px; max-width:95%; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.2); overflow:hidden;">
                                <div style="padding:14px 18px; background:#02455F; color:#fff; display:flex; justify-content:space-between; align-items:center;">
                                    <strong>Apply to change role</strong>
                                    <button type="button" id="roleModalClose" style="background:transparent; border:none; color:#fff; font-size:20px; cursor:pointer;">×</button>
                                </div>
                                <div style="padding:18px;">
                                    <p>Please answer the following with <strong>Yes</strong> or <strong>No</strong> (these are self-declared):</p>
                                    <div style="margin-bottom:12px;">
                                        <label for="requestedRoleSelect">Change role to</label>
                                        <div style="margin-top:6px;">
                                            <select id="requestedRoleSelect" style="padding:6px;border:1px solid #ddd;border-radius:4px;">
                                                <option value="Professional">Professional</option>
                                                <option value="Beginner">Beginner</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div style="margin-bottom:12px;">
                                        <label>1. I can read product specifications and measurements</label>
                                        <div style="margin-top:6px;">
                                            <label style="margin-right:12px;"><input type="radio" name="r1" value="Yes" checked> Yes</label>
                                            <label><input type="radio" name="r1" value="No"> No</label>
                                        </div>
                                    </div>
                                    <div style="margin-bottom:12px;">
                                        <label>2. I have the necessary tools to perform customizations</label>
                                        <div style="margin-top:6px;">
                                            <label style="margin-right:12px;"><input type="radio" name="r2" value="Yes" checked> Yes</label>
                                            <label><input type="radio" name="r2" value="No"> No</label>
                                        </div>
                                    </div>
                                    <div style="margin-bottom:12px;">
                                        <label>3. I have performed product customizations before</label>
                                        <div style="margin-top:6px;">
                                            <label style="margin-right:12px;"><input type="radio" name="r3" value="Yes" checked> Yes</label>
                                            <label><input type="radio" name="r3" value="No"> No</label>
                                        </div>
                                    </div>

                                    <div style="margin-top:8px;">
                                        <label for="roleComment">Optional comment (max 40 chars)</label>
                                        <input id="roleComment" type="text" maxlength="40" placeholder="Optional reason (40 chars)" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; margin-top:6px;">
                                        <div id="commentCount" style="font-size:12px; color:#6b7280; margin-top:6px;">0 / 40</div>
                                    </div>
                                    <div style="margin-top:8px;">
                                        <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" id="confirmAccuracy"> I confirm these answers are accurate.</label>
                                    </div>
                                    <p class="text-muted" style="font-size:13px; margin-top:10px;">Requests allowed once every 90 days. Requests are auto-approved and applied immediately.</p>
                                </div>
                                <div style="padding:12px 18px; display:flex; justify-content:flex-end; gap:8px; background:#f7f7f7;">
                                    <button type="button" id="roleCancel" class="btn-secondary">Cancel</button>
                                    <button type="button" id="submitRoleRequest" class="btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>

                        <!-- PROFESSIONAL FIELDS -->
                        <div class="professional-fields" style="<?php echo (isset($customer_role) && $customer_role === 'professional') ? '' : 'display: none;'; ?>">
                            <!-- Question 2: Previous experience with specifications -->
                            <div class="experience-field">
                                <label class="experience-label">Have you previously worked with products that required detailed specifications?</label>
                                <div class="experience-options">
                                    <label class="experience-option">
                                        <input type="radio" name="professional_prev_experience" value="yes_regularly" 
                                            <?php echo (isset($experience_data) && isset($experience_data['previous_experience']) && $experience_data['previous_experience'] === 'yes_regularly') ? 'checked' : ''; ?>>
                                        <span>Yes, regularly</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="professional_prev_experience" value="yes_occasionally" 
                                            <?php echo (isset($experience_data) && isset($experience_data['previous_experience']) && $experience_data['previous_experience'] === 'yes_occasionally') ? 'checked' : ''; ?>>
                                        <span>Yes, occasionally</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="professional_prev_experience" value="no_understand_drawings" 
                                            <?php echo (isset($experience_data) && isset($experience_data['previous_experience']) && $experience_data['previous_experience'] === 'no_understand_drawings') ? 'checked' : ''; ?>>
                                        <span>No, but I understand technical drawings</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 3: Specification preparation -->
                            <div class="experience-field">
                                <label class="experience-label">How do you usually prepare product specifications?</label>
                                <div class="experience-options">
                                    <label class="experience-option">
                                        <input type="radio" name="professional_spec_prep" value="prepare_myself" 
                                            <?php echo (isset($experience_data) && isset($experience_data['specification_preparation']) && $experience_data['specification_preparation'] === 'prepare_myself') ? 'checked' : ''; ?>>
                                        <span>I prepare measurements and specifications myself</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="professional_spec_prep" value="collaborate_after_assessment" 
                                            <?php echo (isset($experience_data) && isset($experience_data['specification_preparation']) && $experience_data['specification_preparation'] === 'collaborate_after_assessment') ? 'checked' : ''; ?>>
                                        <span>I collaborate after a site assessment</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="professional_spec_prep" value="adjust_supplier_specs" 
                                            <?php echo (isset($experience_data) && isset($experience_data['specification_preparation']) && $experience_data['specification_preparation'] === 'adjust_supplier_specs') ? 'checked' : ''; ?>>
                                        <span>I adjust specifications provided by suppliers</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 4: 2D tool comfort -->
                            <div class="experience-field">
                                <label class="experience-label">How comfortable are you with customizing products using a 2D configuration tool?</label>
                                <div class="experience-options">
                                    <label class="experience-option">
                                        <input type="radio" name="professional_2d_comfort" value="very_comfortable" 
                                            <?php echo (isset($experience_data) && isset($experience_data['2d_tool_comfort']) && $experience_data['2d_tool_comfort'] === 'very_comfortable') ? 'checked' : ''; ?>>
                                        <span>Very comfortable</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="professional_2d_comfort" value="somewhat_comfortable" 
                                            <?php echo (isset($experience_data) && isset($experience_data['2d_tool_comfort']) && $experience_data['2d_tool_comfort'] === 'somewhat_comfortable') ? 'checked' : ''; ?>>
                                        <span>Somewhat comfortable</span>
                                    </label>
                                    <label class="experience-option">
                                        <input type="radio" name="professional_2d_comfort" value="prefer_minimal" 
                                            <?php echo (isset($experience_data) && isset($experience_data['2d_tool_comfort']) && $experience_data['2d_tool_comfort'] === 'prefer_minimal') ? 'checked' : ''; ?>>
                                        <span>I prefer minimal adjustments</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-buttons" style="margin-top: 2rem;">
                            <button type="submit" class="btn-save" id="experienceSaveBtn">
                                Save Changes
                            </button>
                        </div>
                    </form>

                    <script>
                        // Role request modal handlers
                        (function(){
                            var btn = document.getElementById('btn-request-role');
                            var modal = document.getElementById('roleRequestModal');
                            var closeBtn = document.getElementById('roleModalClose');
                            var cancelBtn = document.getElementById('roleCancel');
                            var submitBtn = document.getElementById('submitRoleRequest');

                            function openModal(){ modal.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
                            function closeModal(){ modal.style.display = 'none'; document.body.style.overflow = ''; }

                            if (btn) btn.addEventListener('click', openModal);
                            if (closeBtn) closeBtn.addEventListener('click', closeModal);
                            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

                            if (submitBtn) submitBtn.addEventListener('click', function(){
                                var getChecked = function(name){
                                    var el = document.querySelector('input[name="'+name+'"]:checked');
                                    return el ? el.value : 'No';
                                };
                                var answers = [getChecked('r1'), getChecked('r2'), getChecked('r3')];
                                var confirmation = document.getElementById('confirmAccuracy').checked;
                                if (!confirmation) { showToast('Please confirm the accuracy of your answers.', 'warning'); return; }

                                var commentEl = document.getElementById('roleComment');
                                var comment = commentEl ? commentEl.value.trim() : '';
                                if (comment.length > 40) { showToast('Comment must be 40 characters or fewer.', 'warning'); return; }

                                var bypass = (location.search.indexOf('bypass_cooldown=1') !== -1);
                                var targetRole = document.getElementById('requestedRoleSelect') ? document.getElementById('requestedRoleSelect').value : 'Professional';
                                fetch('<?php echo base_url('Role_requests/create'); ?>', {
                                    method: 'POST',
                                    headers: {'Content-Type':'application/json'},
                                    body: JSON.stringify({ requested_role: targetRole, answers: answers, confirmation: confirmation, comment: comment, bypass_cooldown: bypass })
                                }).then(function(r){ return r.json(); }).then(function(res){
                                    if (!res || !res.success) {
                                        showToast(res && res.message ? res.message : 'Failed to submit request', 'error');
                                        return;
                                    }
                                    // All accepted requests are applied immediately server-side
                                    // Update badge to show the actual requested role
                                    var badge = document.getElementById('user-role-badge');
                                    if (badge) badge.textContent = targetRole; // Use the actual role that was requested
                                    // Update hidden role input so the experience form posts the correct role (lowercase for consistency)
                                    var hid = document.getElementById('experience-role-input');
                                    if (hid) hid.value = targetRole.toLowerCase();
                                    // Refresh experience UI visibility
                                    if (typeof updateExperienceFields === 'function') updateExperienceFields();
                                    showToast('Role updated successfully to ' + targetRole + '. The page will reload to apply changes.', 'success');
                                    closeModal();
                                    // Reload page to ensure all UI reflects new role (especially product customization pages)
                                    setTimeout(function(){ location.reload(); }, 500);
                                }).catch(function(){ showToast('Submission failed', 'error'); });
                            });
                            // update comment counter
                            var commentEl = document.getElementById('roleComment');
                            var countEl = document.getElementById('commentCount');
                            if (commentEl && countEl) {
                                commentEl.addEventListener('input', function(){
                                    var len = Math.min(40, this.value.length);
                                    countEl.textContent = len + ' / 40';
                                });
                                // initialize
                                countEl.textContent = (commentEl.value || '').length + ' / 40';
                            }
                        })();
                    </script>
                </div>

                <!-- Account Details Section (Current Profile Form) -->
                <div id="account-details" class="content-section active" style="display: block;">
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

        <!-- Experience Success Modal -->
        <div id="experienceSuccessModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center;">
            <div style="background: white; border-radius: 12px; padding: 2rem; max-width: 400px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.2); position: relative;">
                <div style="width: 60px; height: 60px; background: #28a745; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h3 style="color: #333; font-size: 1.3rem; margin-bottom: 0.5rem; font-weight: 600;">Success!</h3>
                <p id="experienceSuccessMessage" style="color: #666; font-size: 1rem; margin-bottom: 1.5rem; line-height: 1.5;">Your experience settings have been updated successfully.</p>
                <button onclick="closeExperienceSuccessModal()" style="background: #28a745; color: white; border: none; padding: 0.75rem 2rem; border-radius: 6px; font-size: 1rem; cursor: pointer; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#218838'" onmouseout="this.style.background='#28a745'">OK</button>
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
                                showToast("Address updated successfully!", "success");
                            } else {
                                showToast("Address added successfully!", "success");
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
                            showToast(data.message || "Failed to save address.", "error");
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
                            showToast("Profile updated!", "success");
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
                            showToast(res.message || "Failed to update profile.", "error");
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
                        showToast(errorMsg, "error");
                    }
                });
            });

            // ========= ANCHOR LINK HANDLING =========
            // Handle hash links for navigation
            function showSection(sectionId) {
                // Hide all content sections
                document.querySelectorAll('.content-section').forEach(sec => {
                    sec.style.display = 'none';
                });
                
                // Remove active from all nav items and sub-items
                document.querySelectorAll('.nav-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelectorAll('.nav-sub-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Close orders dropdown
                const ordersGroup = document.getElementById('orders-nav-group');
                if (ordersGroup) {
                    ordersGroup.classList.remove('expanded');
                }
                
                const sectionMap = {
                    'orders': 'orders',
                    'addresses': 'addresses',
                    'account-details': 'account-details',
                    'user-experience': 'user-experience'
                };
                
                const targetSectionId = sectionMap[sectionId] || 'account-details';
                
                // Show the target section
                const targetSection = document.getElementById(targetSectionId);
                if (targetSection) {
                    targetSection.style.display = 'block';
                }
                
                // Set active nav item
                const navSelector = `a[href="#${targetSectionId}"]`;
                const activeNavItem = document.querySelector(navSelector);
                if (activeNavItem && activeNavItem.classList.contains('nav-item')) {
                    activeNavItem.classList.add('active');
                }
            }

            // Initialize page - show account-details section by default
            document.addEventListener('DOMContentLoaded', function() {
                // Handle hash on page load
                if (window.location.hash) {
                    const hash = window.location.hash.substring(1);
                    showSection(hash);
                } else {
                    // Default to account-details if no hash - ensure it's visible
                    showSection('account-details');
                }
            });

            // Handle hash on page load (fallback for older browsers)
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
                            showToast("Address archived successfully!", "success");
                            closeAddressFormModal();
                            location.reload();
                        } else {
                            showToast(res.message || "Failed to archive address.", "error");
                        }
                    },
                    error: function() {
                        showToast("Error deleting address. Please try again.", "error");
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
                                showToast("Address updated successfully!", "success");
                        } else {
                                showToast("Address added successfully!", "success");
                            }
                            closeAddressFormModal();
                            location.reload();
                        } else {
                            showToast(data.message || "Failed to save address.", "error");
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
                
                // When an option is selected, reset size and close dropdown
                $select.on("change", function() {
                    resetSize();
                    // Blur the select to close the dropdown
                    $(this).blur();
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

            // ========= SKILL LEVEL TAB LOGIC =========
            const roleInputs = document.querySelectorAll('input[name="role"]');
            const beginnerFields = document.querySelector('.beginner-fields');
            const professionalFields = document.querySelector('.professional-fields');
            const professionalOnly = document.querySelector('.professional-only');

            function updateExperienceFields() {
                // Prefer explicit radio inputs if present, otherwise fall back to the hidden role input
                let selectedRole = document.querySelector('input[name="role"]:checked')?.value;
                if (!selectedRole) {
                    const hidden = document.getElementById('experience-role-input');
                    selectedRole = hidden ? hidden.value : null;
                }

                if (selectedRole === 'professional') {
                    if (beginnerFields) beginnerFields.style.display = 'none';
                    if (professionalFields) professionalFields.style.display = 'block';
                    if (professionalOnly) professionalOnly.style.display = 'block';
                } else if (selectedRole === 'beginner') {
                    if (beginnerFields) beginnerFields.style.display = 'block';
                    if (professionalFields) professionalFields.style.display = 'none';
                    if (professionalOnly) professionalOnly.style.display = 'none';
                } else {
                    if (beginnerFields) beginnerFields.style.display = 'none';
                    if (professionalFields) professionalFields.style.display = 'none';
                    if (professionalOnly) professionalOnly.style.display = 'none';
                }
            }

            // Initialize on load
            updateExperienceFields();

            // Update on role change
            roleInputs.forEach(input => {
                input.addEventListener('change', updateExperienceFields);
            });

            // Handle professional type "Other" toggle
            const professionalTypeInputs = document.querySelectorAll('input[name="professional_type"]');
            const otherProfessionalInput = document.getElementById('other-professional-type-input');
            const otherProfessionalTypeField = document.getElementById('other_professional_type');

            function toggleOtherProfessionalInput() {
                const otherSelected = document.getElementById('professional-type-other')?.checked;
                let roleIsProfessional = document.querySelector('input[name="role"][value="professional"]')?.checked;
                if (!roleIsProfessional) {
                    const hid = document.getElementById('experience-role-input');
                    roleIsProfessional = hid ? hid.value === 'professional' : false;
                }
                if (otherProfessionalInput) {
                    // Only show if both conditions are met: role is professional AND other is selected
                    const shouldShow = otherSelected && roleIsProfessional;
                    otherProfessionalInput.style.display = shouldShow ? 'block' : 'none';
                    if (otherProfessionalTypeField) {
                        if (shouldShow) {
                            otherProfessionalTypeField.setAttribute('required', 'required');
                        } else {
                            otherProfessionalTypeField.removeAttribute('required');
                        }
                    }
                }
            }

            // Initialize on load
            toggleOtherProfessionalInput();

            // Update on professional type change
            professionalTypeInputs.forEach(input => {
                input.addEventListener('change', toggleOtherProfessionalInput);
            });

            // Also update when role changes
            document.querySelectorAll('input[name="role"]').forEach(input => {
                input.addEventListener('change', toggleOtherProfessionalInput);
            });

            // Handle experience form submission
            $('#experienceForm').on('submit', function(e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.ajax({
                    url: '<?= base_url("UserCon/update_experience") ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showExperienceSuccessModal(response.message || 'Your experience settings have been updated successfully.');
                            // Reload after modal is closed
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            showExperienceSuccessModal(response.message, true);
                        }
                    },
                    error: function() {
                        showExperienceSuccessModal('An error occurred while saving your experience settings.', true);
                    }
                });
            });
        });

        // Experience Success Modal Functions
        function showExperienceSuccessModal(message, isError) {
            const modal = document.getElementById('experienceSuccessModal');
            const messageEl = document.getElementById('experienceSuccessMessage');
            const icon = modal.querySelector('div[style*="background: #28a745"]');
            const button = modal.querySelector('button');
            
            if (isError) {
                icon.style.background = '#dc3545';
                button.style.background = '#dc3545';
                button.onmouseover = function() { this.style.background = '#c82333'; };
                button.onmouseout = function() { this.style.background = '#dc3545'; };
            } else {
                icon.style.background = '#28a745';
                button.style.background = '#28a745';
                button.onmouseover = function() { this.style.background = '#218838'; };
                button.onmouseout = function() { this.style.background = '#28a745'; };
            }
            
            messageEl.textContent = message;
            modal.style.display = 'flex';
        }

        function closeExperienceSuccessModal() {
            const modal = document.getElementById('experienceSuccessModal');
            modal.style.display = 'none';
        }

        // Close modal on overlay click
        document.getElementById('experienceSuccessModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeExperienceSuccessModal();
            }
        });
    </script>

</body>
