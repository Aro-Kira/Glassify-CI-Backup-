<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/order_complete.css'); ?>">

<script>
    const BASE_URL = "<?= base_url(); ?>";
</script>

<div class="checkout-header">
    <!-- Back button -->
    <div class="back-btn">
        <a href="<?= base_url('products'); ?>">
            <img src="<?= base_url('assets/images/img-page/back_button.png');?>" alt="Back Icon">
            <span>Continue Shopping</span>
        </a>
    </div>

    <!-- Progress nav -->
    <div class="progress-nav">
        <div class="step completed">Review</div>
        <div class="divider"></div>
        <div class="step completed">Booking</div>
        <div class="divider"></div>
        <div class="step active">Complete</div>
    </div>
</div>

<main>
    <!-- Confirmation -->
    <div class="confirmation">
        <div class="checkmark"><img src="<?= base_url('assets/images/img-page/checked-complete.png');?>" alt="check-icon"></div>
        <h2>Booking is complete.</h2>
        <p>Your site assessment booking has been placed and is awaiting confirmation.</p>
    </div>

    <!-- Booking Info -->
    <section class="order-info">
        <div class="info-box">
            <div class="info-item">
                <div class="info-label"><strong>Order ID:</strong></div>
                <div class="info-value"><?= isset($order) && $order ? htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT)) : 'N/A' ?></div>
            </div>

            <div class="info-item">
                <div class="info-label"><strong>Booking Date:</strong></div>
                <div class="info-value"><?= isset($order) && $order ? (isset($order->AppointmentDate) && $order->AppointmentDate ? date('F d, Y', strtotime($order->AppointmentDate)) : date('F d, Y', strtotime($order->OrderDate))) : date('F d, Y') ?></div>
            </div>

            <div class="info-item">
                <div class="info-label"><strong>Ocular Visit Date:</strong></div>
                <div class="info-value">
                    <?php 
                    $date_str = '';
                    $time_str = '';
                    
                    // Get the date
                    if (isset($order) && !empty($order->PreferredInstallationDate)) {
                        $date_str = date('F d, Y', strtotime($order->PreferredInstallationDate));
                    } elseif (isset($order) && !empty($order->OcularVisitDate)) {
                        $date_str = date('F d, Y', strtotime($order->OcularVisitDate));
                    }
                    
                    // Get the time
                    if (isset($appointment_time) && !empty($appointment_time)) {
                        $time_str = date('g:i A', strtotime($appointment_time));
                    }
                    
                    // Display date with time
                    if ($date_str && $time_str) {
                        echo $date_str . ' - ' . $time_str;
                    } elseif ($date_str) {
                        echo $date_str;
                    } else {
                        echo 'Not set';
                    }
                    ?>
                </div>
            </div>
        </div>

        <style>
            .info-box { display:flex; gap:24px; flex-wrap:wrap; align-items:flex-start; }
            .info-item { min-width:160px; }
            .info-label { font-weight:400; color:#374151; font-size:0.9rem; }
            .info-value { margin-top:6px; color:#0f2b46; font-weight:400; font-size:0.95rem; }
            @media (max-width:640px) { .info-box { flex-direction:column; } }
        </style>
    </section>

    <!-- Products / Services Table -->
    <section class="order-products">
        <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <?php if (isset($customer_role) && $customer_role === 'beginner'): ?>
                        <th style="text-align: left;">Price Range</th>
                        <?php else: ?>
                        <th>Customization</th>
                        <th>Qty</th>
                        <th>Price Range</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // If controller didn't provide items, attempt to load them here as a fallback
                    if (empty($order_items) && isset($order) && $order) {
                        $this->load->model('Order_model');
                        $order_items = $this->Order_model->get_order_customizations($order->OrderID ?? $order->order_id ?? 0);
                    }

                    if (!empty($order_items)): ?>
                        <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php 
                                $image_raw = $item->ImageUrl ?? '';
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
                                <img src="<?= $product_img ?>" alt="<?= htmlspecialchars($item->ProductName ?? 'Product') ?>" class="cart-product-img" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; flex-shrink: 0;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span style="font-weight: 600; color: #0f2b46;"><?= htmlspecialchars($item->ProductName ?? 'Product') ?></span>
                                    <?php if (!empty($item->Category) || !empty($item->Subcategory)): ?>
                                        <span style="font-size: 0.85rem; color: #6b7280;">
                                            <?= htmlspecialchars($item->Category ?? '') ?>
                                            <?php if (!empty($item->Category) && !empty($item->Subcategory)): ?> - <?php endif; ?>
                                            <?= htmlspecialchars($item->Subcategory ?? '') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <?php if (isset($customer_role) && $customer_role === 'beginner'): ?>
                        <td style="text-align: left; font-weight: 600; color: #0f2b46;">
                            <?php
                            // Display price range for beginner
                            $price_min = $item->PriceMin ?? $item->Price ?? null;
                            $price_max = $item->PriceMax ?? null;
                            if ($price_min && $price_max) {
                                echo '₱' . number_format($price_min) . ' - ₱' . number_format($price_max);
                            } elseif ($price_min) {
                                echo 'Starting at ₱' . number_format($price_min);
                            } else {
                                echo 'Price TBD after assessment';
                            }
                            ?>
                        </td>
                        <?php else: ?>
                        <td class="customization-info">
                            <?php 
                            // Build customization breakdown from 2D JSON or legacy fields
                            $breakdown_fields = [];
                            
                            // Try to parse Customization JSON first (from 2D customization)
                            if (!empty($item->Customization)) {
                                $parsed = json_decode($item->Customization, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                                    // Add Dimension first if available
                                    if (!empty($item->Dimensions)) {
                                        $breakdown_fields[] = ['label' => 'Dimension', 'value' => $item->Dimensions];
                                    }
                                    // Add other fields from JSON
                                    foreach ($parsed as $key => $value) {
                                        if (!empty($value) && !in_array($key, ['Dimension', 'Dimensions'])) {
                                            // Convert camelCase keys to proper labels (e.g. glassType -> Glass Type)
                                            $label = ucwords(preg_replace('/(?<!^)[A-Z]/', ' $0', str_replace('_', ' ', $key)));
                                            $breakdown_fields[] = ['label' => $label, 'value' => $value];
                                        }
                                    }
                                }
                            }
                            
                            // Fallback to legacy fields if no JSON
                            if (empty($breakdown_fields)) {
                                if (!empty($item->Dimensions)) $breakdown_fields[] = ['label' => 'Size', 'value' => $item->Dimensions];
                                if (!empty($item->GlassShape)) $breakdown_fields[] = ['label' => 'Shape', 'value' => ucfirst($item->GlassShape)];
                                if (!empty($item->GlassType)) $breakdown_fields[] = ['label' => 'Type', 'value' => ucfirst($item->GlassType)];
                                if (!empty($item->GlassThickness)) $breakdown_fields[] = ['label' => 'Thickness', 'value' => $item->GlassThickness];
                                if (!empty($item->EdgeWork)) $breakdown_fields[] = ['label' => 'Edge', 'value' => ucfirst(str_replace('-', ' ', $item->EdgeWork))];
                                if (!empty($item->FrameType)) $breakdown_fields[] = ['label' => 'Frame', 'value' => ucfirst($item->FrameType)];
                                if (!empty($item->Engraving) && $item->Engraving !== 'None') $breakdown_fields[] = ['label' => 'Engraving', 'value' => $item->Engraving];
                            }
                            
                            $has_breakdown = count($breakdown_fields) > 0;
                            ?>
                            <?php if ($has_breakdown): ?>
                                <div class="custom-layout">
                                    <?php if (!empty($item->DesignRef)): ?>
                                        <div class="design-thumbnail-wrapper">
                                            <img src="<?= base_url($item->DesignRef) ?>" 
                                                 alt="Custom Design" 
                                                 class="design-thumbnail"
                                                 onclick="showDesignModal('<?= base_url($item->DesignRef) ?>')"
                                                 onerror="this.style.display='none'; this.parentElement.querySelector('.view-design-text').textContent='Image not found';">
                                            <span class="view-design-text">Click to view</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php
                                    // Show first 2 specs as button
                                    $display_parts = array_slice($breakdown_fields, 0, 2);
                                    $remaining_count = count($breakdown_fields) - 2;
                                    $display_text = implode(' • ', array_map(function($f) { return $f['label'] . ': ' . $f['value']; }, $display_parts));
                                    $breakdown_json = htmlspecialchars(json_encode($breakdown_fields), ENT_QUOTES);
                                    ?>
                                    <button type="button" class="view-breakdown-btn" data-breakdown="<?= $breakdown_json ?>" style="display:inline-block; text-align:left; padding:10px 14px; border-radius:6px; border:2px solid #3b82f6; background:#eff6ff; color:#1e40af; cursor:pointer; font-size:13px; line-height:1.6; max-width:100%; word-wrap:break-word; white-space:normal; transition:all 0.2s ease; font-weight:600; box-shadow:0 2px 4px rgba(59,130,246,0.1);" onmouseover="this.style.backgroundColor='#dbeafe'; this.style.borderColor='#2563eb'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.2)';" onmouseout="this.style.backgroundColor='#eff6ff'; this.style.borderColor='#3b82f6'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(59,130,246,0.1)';">
                                        <?= $display_text ?>
                                        <?php if ($remaining_count > 0): ?>
                                            <br><span style="font-size:12px; color:#4b5563;">and <?= $remaining_count ?> more</span>
                                        <?php endif; ?>
                                        <br><span style="font-size:11px; opacity:0.7;">▼ Click to expand</span>
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="no-custom">Standard</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $item->Quantity ?? 1 ?></td>
                        <?php
                            // Item-level price range for professional display
                            if (!(isset($customer_role) && $customer_role === 'beginner')) {
                                $price_min = $item->PriceMin ?? $item->Price ?? null;
                                $price_max = $item->PriceMax ?? null;
                                if (!empty($price_min) && !empty($price_max)) {
                                    echo '<td style="text-align: left; font-weight: 600; color: #0f2b46;">₱' . number_format((float)$price_min, 2) . ' - ₱' . number_format((float)$price_max, 2) . '</td>';
                                } elseif (!empty($price_min)) {
                                    echo '<td style="text-align: left; font-weight: 600; color: #0f2b46;">Starting at ₱' . number_format((float)$price_min, 2) . '</td>';
                                } else {
                                    echo '<td></td>';
                                }
                            }
                        ?>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= (isset($customer_role) && $customer_role === 'beginner') ? '2' : '4' ?>" style="text-align: center; padding:18px;">
                            <div class="booking-details">
                                <p><strong>No product items for this booking.</strong></p>
                                <?php if (isset($order) && $order): ?>
                                    <p><strong>Client:</strong> <?= htmlspecialchars(($order->First_Name ?? $user->First_Name ?? '') . ' ' . ($order->Last_Name ?? $user->Last_Name ?? '')) ?></p>
                                    <p><strong>Phone:</strong> <?= htmlspecialchars($order->PhoneNum ?? $user->PhoneNum ?? '') ?></p>
                                    <p><strong>Site Address:</strong> <?= nl2br(htmlspecialchars($order->DeliveryAddress ?? 'Not provided')) ?></p>
                                    <?php if (!empty($order->AppointmentDate) || !empty($order->AppointmentTime)): ?>
                                        <p><strong>Appointment:</strong>
                                            <?= !empty($order->AppointmentDate) ? date('F d, Y', strtotime($order->AppointmentDate)) : '' ?>
                                            <?= !empty($order->AppointmentTime) ? ' at ' . htmlspecialchars($order->AppointmentTime) : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($order->DesignRef)): ?>
                                        <div style="margin-top:12px;">
                                            <p><strong>Design Preview:</strong></p>
                                            <img src="<?= base_url($order->DesignRef) ?>" alt="Design Preview" style="max-width:220px; max-height:160px; border:1px solid #ddd; border-radius:6px;">
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- Addresses / Site Info -->
    <section class="order-products">
        <?php
            // Parse contact info from SpecialInstructions if JSON
            $contact_name = '';
            $contact_phone = '';
            if (isset($order) && !empty($order->SpecialInstructions)) {
                $special_data = json_decode($order->SpecialInstructions, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($special_data)) {
                    $contact_name = $special_data['contact_name'] ?? '';
                    $contact_phone = $special_data['contact_phone'] ?? '';
                }
            }
            
            // Fallback to user data if not in SpecialInstructions
            if (empty($contact_name) && isset($user) && $user) {
                $contact_name = htmlspecialchars($user->First_Name . ' ' . $user->Last_Name);
            }
            if (empty($contact_phone) && isset($user) && $user && !empty($user->PhoneNum)) {
                $contact_phone = htmlspecialchars($user->PhoneNum);
            }

            // Determine site address (prefer DeliveryAddress, then billing variants)
            $site_addr = 'Site address not provided';
            if (isset($order) && $order) {
                if (!empty($order->DeliveryAddress)) {
                    $site_addr = $order->DeliveryAddress;
                } elseif (!empty($order->BillingAddress)) {
                    $site_addr = $order->BillingAddress;
                } elseif (!empty($order->billing_address)) {
                    $site_addr = $order->billing_address;
                }
            }

            // Determine notes (prefer Notes, then other variants)
            $order_notes = '';
            if (isset($order) && $order) {
                if (!empty($order->Notes)) $order_notes = $order->Notes;
                elseif (!empty($order->CustomerNotes)) $order_notes = $order->CustomerNotes;
                elseif (!empty($order->Note)) $order_notes = $order->Note;
            }
        ?>

        <div class="addresses" style="display:flex !important; flex-direction:row !important; gap:20px; align-items:flex-start; width:100%;">
            <div class="address-box" style="flex:1 1 50%; min-width:0; max-width:50%;">
                <h4>Site Address</h4>
                <?php if (!empty($contact_name)): ?>
                    <p style="margin:0 0 6px 0;"><b><?= $contact_name ?></b></p>
                <?php endif; ?>
                <?php if (!empty($contact_phone)): ?>
                    <p style="margin:0 0 6px 0;">(+63) <?= $contact_phone ?></p>
                <?php endif; ?>
                <p style="margin-top:6px;"><?= nl2br(htmlspecialchars($site_addr)) ?></p>
            </div>

            <div class="address-box" style="flex:1 1 50%; min-width:0; max-width:50%;">
                <h4>Special Instructions / Note</h4>
                <?php if (!empty($order_notes)): ?>
                    <p><?= nl2br(htmlspecialchars($order_notes)) ?></p>
                <?php else: ?>
                    <p style="color: #999; font-style: italic;">No notes or special instructions</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Action Buttons -->
    <div style="display:flex; justify-content:center; gap:16px; margin-top:40px; margin-bottom:40px;">
        <a href="<?= base_url('track_order?order=' . (isset($order) && $order ? ($order->OrderID ?? '') : '')) ?>" style="text-decoration:none;">
            <button style="padding:12px 32px; border-radius:6px; background:#02455F; color:#fff; border:none; font-weight:600; font-size:15px; cursor:pointer; min-width:180px;">Track Order</button>
        </a>
        <a href="<?= base_url('products') ?>" style="text-decoration:none;">
            <button style="padding:12px 32px; border-radius:6px; background:#fff; color:#02455F; border:2px solid #02455F; font-weight:600; font-size:15px; cursor:pointer; min-width:180px;">Continue Shopping</button>
        </a>
    </div>
</main>

<!-- Design Preview Modal -->
<div id="designModal" class="design-modal">
    <div class="design-modal-overlay" onclick="closeDesignModal()"></div>
    <div class="design-modal-content">
        <button class="design-modal-close" onclick="closeDesignModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="design-modal-header" style="text-align:center;">
            <h3>2D Design Preview</h3>
            <p>This design is included in your order.</p>
        </div>
        <div class="design-modal-body">
            <img id="designModalImage" src="" alt="Custom Design">
        </div>
        <div class="design-modal-footer">
            <button class="btn-download-design" onclick="downloadDesignImage()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Download Design
            </button>
        </div>
    </div>
</div>

<!-- Specs Modal -->
<div id="specsModal" class="design-modal">
    <div class="design-modal-overlay" onclick="closeSpecsModal()"></div>
    <div class="design-modal-content">
        <button class="design-modal-close" onclick="closeSpecsModal()">✕</button>
        <div class="design-modal-header">
            <h3>Product Specifications</h3>
        </div>
        <div class="design-modal-body" id="specsModalBody">
        </div>
        <div class="design-modal-footer">
            <button class="btn-download-design" onclick="closeSpecsModal()">Close</button>
        </div>
    </div>
</div>

<script>
// Design Modal Functions
function showDesignModal(imageSrc) {
    document.getElementById('designModalImage').src = imageSrc;
    document.getElementById('designModal').classList.add('active');
}

function closeDesignModal() {
    document.getElementById('designModal').classList.remove('active');
}

function downloadDesignImage() {
    const img = document.getElementById('designModalImage');
    const link = document.createElement('a');
    link.href = img.src;
    link.download = 'custom-design-' + Date.now() + '.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Specs Modal Functions
function openSpecsFromId(elementId) {
    var source = document.getElementById(elementId);
    if (!source) return;
    var body = document.getElementById('specsModalBody');
    body.innerHTML = source.innerHTML;
    document.getElementById('specsModal').classList.add('active');
}

function closeSpecsModal() {
    document.getElementById('specsModal').classList.remove('active');
}

// Customization breakdown modal handler
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

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDesignModal();
        closeSpecsModal();
        var modal = document.getElementById('breakdownModal');
        if (modal && modal.style.display === 'flex') {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
});
</script>

<!-- Import jsPDF & AutoTable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

<script>
// Booking data from PHP (used for invoice generation)
const orderData = {
    orderId: "<?= isset($order) && $order ? htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT)) : 'N/A' ?>",
    transactionId: "TXN<?= isset($order) && $order ? date('Ymd', strtotime($order->OrderDate)) . str_pad($order->OrderID, 6, '0', STR_PAD_LEFT) : date('Ymd') . '000000' ?>",
    orderDate: "<?= isset($order) && $order ? date('F d, Y', strtotime($order->OrderDate)) : date('F d, Y') ?>",
    paymentMethod: "Site Assessment",
    status: "<?= isset($order) && $order ? $order->Status : 'Pending' ?>",
    customer: {
        name: "<?= isset($user) ? htmlspecialchars($user->First_Name . ' ' . $user->Last_Name) : (isset($order) && isset($order->First_Name) ? htmlspecialchars($order->First_Name . ' ' . $order->Last_Name) : 'Customer') ?>",
        email: "<?= isset($user) && !empty($user->Email) ? htmlspecialchars($user->Email) : (isset($order) && !empty($order->Email) ? htmlspecialchars($order->Email) : '') ?>",
        phone: "<?= isset($user) ? htmlspecialchars($user->PhoneNum ?? '') : (isset($order) ? htmlspecialchars($order->PhoneNum ?? '') : '') ?>",
        address: "<?= isset($shipping_address) && $shipping_address ? htmlspecialchars($shipping_address->AddressLine . ', ' . $shipping_address->City . ', ' . $shipping_address->Province . ', ' . $shipping_address->Country . ' ' . $shipping_address->ZipCode) : (isset($order) && $order ? htmlspecialchars($order->DeliveryAddress) : '') ?>"
    },
    items: [
        <?php if (!empty($order_items)): ?>
            <?php foreach ($order_items as $index => $item): ?>
            {
                name: "<?= htmlspecialchars($item->ProductName ?? 'Product') ?>",
                dimensions: "<?= htmlspecialchars($item->Dimensions ?? '-') ?>",
                glassType: "<?= htmlspecialchars($item->GlassType ?? '-') ?>",
                thickness: "<?= htmlspecialchars($item->GlassThickness ?? '-') ?>",
                shape: "<?= htmlspecialchars($item->GlassShape ?? '-') ?>",
                edgeWork: "<?= htmlspecialchars($item->EdgeWork ?? '-') ?>",
                frameType: "<?= htmlspecialchars($item->FrameType ?? '-') ?>",
                engraving: "<?= htmlspecialchars($item->Engraving ?? '-') ?>",
                designRef: "<?= !empty($item->DesignRef) ? base_url($item->DesignRef) : '' ?>",
                quantity: <?= $item->Quantity ?>,
                unitPrice: <?= $item->EstimatePrice ?>,
                total: <?= $item->EstimatePrice * $item->Quantity ?>
            }<?= $index < count($order_items) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        <?php endif; ?>
    ],
    summary: {
        items: <?= $summary['items'] ?? 0 ?>,
        subtotal: <?= $summary['subtotal'] ?? 0 ?>,
        shipping: <?= $summary['shipping'] ?? 0 ?>,
        total: <?= $summary['total'] ?? 0 ?>
    }
};

// Helper and invoice generation code (same as order_complete)
function loadImageAsBase64(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = function() {
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            resolve(canvas.toDataURL('image/png'));
        };
        img.onerror = reject;
        img.src = url;
    });
}

var invoiceBtn = document.getElementById('downloadInvoiceBtn');
if (invoiceBtn) {
invoiceBtn.addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Generating...';
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const primaryColor = [15, 43, 70];
        const darkColor = [44, 62, 80];
        const lightGray = [236, 240, 241];
        doc.setFillColor(...primaryColor);
        doc.rect(0, 0, 210, 40, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(28);
        doc.setFont('helvetica', 'bold');
        doc.text('GLASSIFY', 20, 25);
        doc.setFontSize(12);
        doc.setFont('helvetica', 'normal');
        doc.text('INVOICE', 165, 20);
        doc.text('#' + orderData.transactionId, 150, 28);
        doc.setTextColor(...darkColor);
        doc.setFontSize(10);
        doc.text('Order ID: ' + orderData.orderId, 20, 50);
        doc.text('Date: ' + orderData.orderDate, 20, 56);
        doc.text('Order Type: ' + orderData.paymentMethod, 120, 50);
        doc.setFillColor(...lightGray);
        doc.roundedRect(20, 65, 170, 30, 3, 3, 'F');
        doc.setFontSize(11);
        doc.setFont('helvetica', 'bold');
        doc.text('Bill To:', 25, 75);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.text(orderData.customer.name, 25, 83);
        doc.text(orderData.customer.email + ' | ' + orderData.customer.phone, 25, 90);
        const tableData = orderData.items.map(item => [
            item.name,
            `Size: ${item.dimensions}\nShape: ${item.shape}\nType: ${item.glassType}\nThickness: ${item.thickness}\nEdge: ${item.edgeWork}\nFrame: ${item.frameType}`,
            item.quantity,
            'PHP' + item.unitPrice.toLocaleString('en-PH', {minimumFractionDigits: 2}),
            'PHP' + item.total.toLocaleString('en-PH', {minimumFractionDigits: 2})
        ]);
        doc.autoTable({
            startY: 105,
            head: [['Product', 'Customization Details', 'Qty', 'Unit Price', 'Amount']],
            body: tableData,
            theme: 'striped',
            headStyles: { fillColor: primaryColor },
        });
        doc.save('booking-' + orderData.orderId + '.pdf');
    } catch (err) {
        console.error(err);
        showToast('Failed to generate invoice.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = '⬇ Download Invoice';
    }
});
}
</script>