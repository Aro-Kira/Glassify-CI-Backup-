<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/order_complete.css'); ?>">

<script>
    const BASE_URL = "<?= base_url(); ?>";
</script>

<div class="checkout-header">
    <!-- Back button -->
    <div class="back-btn">
        <a href="javascript:history.back()">
            <img src="<?= base_url('assets/images/img-page/back_button.png');?>" alt="Back Icon">
            <span>Continue Shopping</span>
        </a>
    </div>

    <!-- Progress nav -->
    <div class="progress-nav">
        <?php $origin = isset($payment_origin) ? $payment_origin : 'cart'; ?>
        <div class="step completed"><?= ($origin === 'review') ? 'Review' : 'Cart' ?></div>
        <div class="divider"></div>
        <div class="step completed">Order & Payment</div>
        <div class="divider"></div>
        <div class="step active">Complete</div>
    </div>
</div>

<main>
    <!-- Confirmation -->
    <div class="confirmation">
        <?php
            // Determine successful payment state. Prefer explicit $payment_status, then inspect $payment record.
            $is_payment_success = false;
            if (isset($payment_status) && strtolower($payment_status) === 'succeeded') {
                $is_payment_success = true;
            } elseif (isset($payment) && $payment) {
                $pstat = '';
                if (isset($payment->PaymentStatus)) $pstat = strtolower($payment->PaymentStatus);
                if (empty($pstat) && isset($payment->Status)) $pstat = strtolower($payment->Status);
                if (in_array($pstat, ['succeeded', 'paid', 'completed'])) {
                    $is_payment_success = true;
                }
                if (!$is_payment_success && !empty($payment->Transaction_ID) && strpos($payment->Transaction_ID, 'pay_') === 0) {
                    $is_payment_success = true;
                }
            }
        ?>

        <?php if ($is_payment_success): ?>
            <div class="checkmark"><img src="<?= base_url('assets/images/img-page/checked-complete.png');?>" alt="check-icon"></div>
            <h2>Payment Successful!</h2>
            <p>Your payment has been processed successfully. Your order is now being reviewed.</p>
        <?php else: ?>
            <div class="checkmark"><img src="<?= base_url('assets/images/img-page/checked-complete.png');?>" alt="check-icon"></div>
            <h2>Order Placed</h2>
            <p>Your order has been placed and is waiting for payment verification.</p>
        <?php endif; ?>
    </div>

    <!-- Order Info -->
    <section class="order-info">
        <?php
            // Use exact selected payment method when available: prefer payment record, then order
            $pm_raw = '';
            if (isset($payment) && $payment && !empty($payment->PaymentMethod)) {
                $pm_raw = $payment->PaymentMethod;
            } elseif (isset($order) && $order && !empty($order->PaymentMethod)) {
                $pm_raw = $order->PaymentMethod;
            }
            
            // Map payment method to user-friendly display names
            $pm_display = $pm_raw;
            $pm_lower = strtolower($pm_raw);
            if (strpos($pm_lower, 'gcash') !== false) {
                $pm_display = 'GCash';
            } elseif (strpos($pm_lower, 'maya') !== false) {
                $pm_display = 'Maya';
            } elseif (strpos($pm_lower, 'card') !== false) {
                $pm_display = 'Credit / Debit Card';
            } elseif (!empty($pm_raw)) {
                $pm_display = ucfirst($pm_raw);
            } else {
                $pm_display = 'N/A';
            }
        ?>

        <div class="info-box" style="display:flex; gap:24px; flex-wrap:wrap;">
            <div class="info-item">
                <div class="info-label"><strong>Order ID:</strong></div>
                <div class="info-value"><?= isset($order) && $order ? htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT)) : 'N/A' ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><strong>Payment Method:</strong></div>
                <div class="info-value"><?= htmlspecialchars($pm_display ?: 'N/A') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><strong>Payment ID:</strong></div>
                <div class="info-value" style="overflow-wrap:anywhere;"><?= isset($order) && $order && isset($payment) && $payment->Transaction_ID ? htmlspecialchars($payment->Transaction_ID) : 'TXN' . (isset($order) && $order ? date('Ymd', strtotime($order->OrderDate)) . str_pad($order->OrderID, 6, '0', STR_PAD_LEFT) : 'N/A') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><strong>Order Date:</strong></div>
                <div class="info-value"><?= isset($order) && $order ? date('F d, Y', strtotime($order->OrderDate)) : date('F d, Y') ?></div>
            </div>
        </div>
        <style>
            .info-box { display:flex; gap:24px; flex-wrap:wrap; align-items:flex-start; }
            .info-item { min-width:160px; }
            .info-label { font-weight:400; color:#374151; font-size:0.9rem; }
            .info-value { margin-top:6px; color:#0f2b46; font-weight:400; font-size:0.95rem; }
        </style>
    </section>

    <!-- Products Table -->
    <?php if (isset($order) && $order): ?>
        <?php
        $this->load->model('Order_model');
        $order_items = $this->Order_model->get_order_customizations($order->OrderID);
        ?>
        <section class="order-products">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th style="text-align: left; padding-left: 18px;">Product</th>
                        <th>Customization</th>
                        <th style="width: 90px;">Quantity</th>
                        <th style="width: 140px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($order_items)): ?>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <?php 
                                        // Get product image (not 2D customization image)
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
                                        <span style="font-weight: 600; color: #0f2b46;"><?= htmlspecialchars($item->ProductName ?? 'Unknown Product') ?></span>
                                    </div>
                                </td>
                                <td class="customization-info">
                                    <?php 
                                    // Check if item has any customization data
                                    $has_customization = !empty($item->Dimensions) || !empty($item->GlassType) || 
                                                        !empty($item->GlassThickness) || !empty($item->GlassShape) || 
                                                        !empty($item->EdgeWork) || !empty($item->FrameType) || 
                                                        !empty($item->DesignRef);
                                    ?>
                                    <?php if ($has_customization): ?>
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
                                            <div class="custom-details">
                                                <?php
                                                // Build simple summary (first two specs)
                                                $specs = [];
                                                if (!empty($item->Dimensions)) $specs['Size'] = htmlspecialchars($item->Dimensions);
                                                if (!empty($item->GlassShape)) $specs['Shape'] = ucfirst(htmlspecialchars($item->GlassShape));
                                                if (!empty($item->GlassType)) $specs['Type'] = ucfirst(htmlspecialchars($item->GlassType));
                                                if (!empty($item->GlassThickness)) $specs['Thickness'] = htmlspecialchars($item->GlassThickness);
                                                if (!empty($item->EdgeWork)) $specs['Edge'] = ucfirst(str_replace('-', ' ', htmlspecialchars($item->EdgeWork)));
                                                if (!empty($item->FrameType)) $specs['Frame'] = ucfirst(htmlspecialchars($item->FrameType));
                                                if (!empty($item->Engraving) && $item->Engraving !== 'None') $specs['Engraving'] = htmlspecialchars($item->Engraving);
                                                $preview_limit = 3;
                                                $spec_preview = array_slice($specs, 0, $preview_limit, true);
                                                foreach ($spec_preview as $k => $v) {
                                                    echo '<span class="custom-tag">' . $k . ': ' . $v . '</span>';
                                                }
                                                $total_specs = count($specs);
                                                if ($total_specs > $preview_limit) {
                                                    echo '<a href="#" class="custom-tag" onclick="openSpecsFromId(\'specs-' . $item->OrderItemID . '\'); return false;">View More</a>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div id="specs-<?= $item->OrderItemID ?>" style="display:none;">
                                            <div class="summary-box">
                                                <h4>Product Specifications</h4>
                                                <div style="padding: 10px;">
                                                    <ul style="margin:0; padding-left:18px;">
                                                        <?php foreach ($specs as $k => $v): ?>
                                                            <li style="margin-bottom:6px;"><strong><?= $k ?>:</strong> <?= $v ?></li>
                                                        <?php endforeach; ?>
                                                        <?php if (!empty($item->DesignRef)): ?>
                                                            <li style="margin-top:8px;"><strong>2D Preview:</strong> <a href="#" onclick="showDesignModal('<?= base_url($item->DesignRef) ?>'); return false;">Open Preview</a></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-custom">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $item->Quantity ?? 1 ?></td>
                                <td class="price">₱<?= number_format(($item->EstimatePrice ?? 0) * ($item->Quantity ?? 1), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No items found.</td>
                        </tr>
                    <?php endif; ?>
                    <tr style="border-top:2px solid #ddd;">
                        <td colspan="3" style="text-align:left; padding:18px;"></td>
                        <td style="font-weight:700; font-size:1.05rem; color:#0f2b46; white-space:nowrap;">Total: ₱<?= isset($summary['total']) ? number_format($summary['total'], 2) : number_format($order->TotalAmount ?? 0, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </section>

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
                <div class="design-modal-header">
                    <h3>Custom Design Layout</h3>
                    <p>This design is included in your order</p>
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

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDesignModal();
            }
        });
        </script>
        
        <section class="order-products">
            <div class="addresses" style="display:flex !important; flex-direction:row !important; gap:20px; align-items:flex-start; width:100%;">
                <div class="address-box" style="flex:1 1 50%; min-width:0; max-width:50%;">
                    <h4>Shipping Address</h4>
                    <?php $this->load->helper('address'); ?>
                    <?php
                        // Get shipping info from payment record ONLY (reflects Payment page input)
                        $ship_name = '';
                        $ship_email = '';
                        $ship_phone = '';
                        $ship_addr = null;
                        
                        if (isset($payment) && $payment) {
                            // Always use payment record data if available, never fall back to user profile
                            $ship_name = $payment->shipping_name ?? '';
                            $ship_email = $payment->shipping_email ?? '';
                            $ship_phone = $payment->shipping_phone ?? '';
                            
                            // Build address array from payment shipping fields
                            $ship_addr = [
                                'UnitHouseNumber' => $payment->shipping_unit ?? '',
                                'Street' => $payment->shipping_street ?? '',
                                'Subdivision' => $payment->shipping_subdivision ?? '',
                                'Barangay' => $payment->shipping_barangay ?? '',
                                'City' => $payment->shipping_city ?? '',
                                'Province' => $payment->shipping_province ?? '',
                                'Region' => $payment->shipping_region ?? '',
                                'ZipCode' => $payment->shipping_postal_code ?? '',
                                'Country' => $payment->shipping_country ?? ''
                            ];
                            
                            // Check if address is completely empty
                            $has_address = false;
                            foreach ($ship_addr as $field) {
                                if (!empty($field)) {
                                    $has_address = true;
                                    break;
                                }
                            }
                            
                            // If address is empty, set to null so we can show appropriate message
                            if (!$has_address) {
                                $ship_addr = null;
                            }
                        }
                    ?>
                    <?php if ($ship_name): ?><p><b><?= htmlspecialchars($ship_name) ?></b></p><?php endif; ?>
                    <?php if ($ship_email): ?><p><?= htmlspecialchars($ship_email) ?></p><?php endif; ?>
                    <?php if ($ship_phone): ?><p><?= format_display_phone($ship_phone) ?></p><?php endif; ?>

                    <?php if (!is_null($ship_addr)): ?>
                        <p><?= format_address_three_html($ship_addr) ?></p>
                    <?php else: ?>
                        <p style="color: #999; font-style: italic;">No shipping address captured from payment form.</p>
                    <?php endif; ?>
                </div>

                <?php if (!isset($is_site_assessment) || !$is_site_assessment): ?>
                <div class="address-box" style="flex:1 1 50%; min-width:0; max-width:50%;">
                    <h4>Billing Address</h4>
                    <?php
                    // Prefer persisted billing fields on the payment record, then user billing address, then order DeliveryAddress
                    $has_payment_billing = isset($payment) && ($payment->billing_name ?? $payment->billing_firstname ?? $payment->billing_street ?? false);
                    if ($has_payment_billing):
                        // Build from payment columns
                        $b_name = trim(($payment->billing_name ?? trim(($payment->billing_firstname ?? '') . ' ' . ($payment->billing_lastname ?? ''))));
                        $b_email = $payment->billing_email ?? '';
                        $b_phone = $payment->billing_phone ?? '';
                        $b_addr = [
                            'UnitHouseNumber' => $payment->billing_unit ?? $payment->billing_unit_house_number ?? '',
                            'Street' => $payment->billing_street ?? '',
                            'Subdivision' => $payment->billing_subdivision ?? '',
                            'Barangay' => $payment->billing_barangay ?? '',
                            'City' => $payment->billing_city ?? '',
                            'Province' => $payment->billing_province ?? '',
                            'Region' => $payment->billing_region ?? '',
                            'ZipCode' => $payment->billing_postal_code ?? $payment->billing_zipcode ?? '',
                            'Country' => $payment->billing_country ?? ''
                        ];
                    ?>
                        <?php if ($b_name): ?><p><b><?= htmlspecialchars($b_name) ?></b></p><?php endif; ?>
                        <?php if ($b_email): ?><p><?= htmlspecialchars($b_email) ?></p><?php endif; ?>
                        <?php if ($b_phone): ?><p><?= format_display_phone($b_phone) ?></p><?php endif; ?>
                        <p><?= format_address_three_html($b_addr) ?></p>
                    <?php elseif (isset($billing_address) && $billing_address): ?>
                        <p><?= format_address_three_html($billing_address) ?></p>
                    <?php elseif (isset($shipping_address) && $shipping_address): ?>
                        <p><?= format_address_three_html($shipping_address) ?></p>
                    <?php elseif (isset($order) && !empty($order->DeliveryAddress)): ?>
                        <?php
                            $tmpAddr = [
                                'Street' => $order->DeliveryAddress,
                                'City' => $order->City ?? '',
                                'Province' => $order->Province ?? '',
                                'Region' => $order->Region ?? '',
                                'ZipCode' => $order->ZipCode ?? ''
                            ];
                        ?>
                        <p><?= format_address_three_html($tmpAddr) ?></p>
                    <?php else: ?>
                        <p>No billing address provided.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="address-box" style="margin-top:20px; width:100%;">
                <h4>Special Instructions / Note</h4>
                <?php
                    $order_notes = '';
                    if (isset($order) && $order) {
                        if (!empty($order->CustomerNotes)) $order_notes = $order->CustomerNotes;
                        elseif (!empty($order->Notes)) $order_notes = $order->Notes;
                        elseif (!empty($order->Note)) $order_notes = $order->Note;
                    }
                ?>
                <?php if (!empty($order_notes)): ?>
                    <p><?= nl2br(htmlspecialchars($order_notes)) ?></p>
                <?php else: ?>
                    <p>No special instructions.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div style="text-align: center; margin-top: 30px; margin-bottom: 40px;">
        <a href="<?= base_url('track_order?order=' . (isset($order) && $order ? $order->OrderID : '')) ?>" class="btn-track-order" style="display: inline-block; padding: 12px 30px; background: #02455F; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; margin-right: 15px;">
            Track Order
        </a>
        <a href="<?= base_url('products') ?>" class="btn-continue-shopping" style="display: inline-block; padding: 12px 30px; background: #fff; color: #02455F; text-decoration: none; border-radius: 6px; font-weight: 600; border: 2px solid #02455F;">
            Continue Shopping
        </a>
    </div>
</main>
