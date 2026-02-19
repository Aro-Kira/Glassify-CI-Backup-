<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/order_complete.css'); ?>">

<script>
    const BASE_URL = "<?= base_url(); ?>";
</script>

<div class="page-wrapper">

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
        <div class="step completed">Cart</div>
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
                <div class="info-value"><?= isset($order) && $order ? date('F d, Y', strtotime($order->OrderDate)) : date('F d, Y') ?></div>
            </div>

            <div class="info-item">
                <div class="info-label"><strong>Ocular Visit Date:</strong></div>
                <div class="info-value">
                    <?php 
                    if (isset($order) && !empty($order->PreferredInstallationDate)) {
                        echo date('F d, Y', strtotime($order->PreferredInstallationDate));
                    } elseif (isset($order) && !empty($order->OcularVisitDate)) {
                        echo date('F d, Y', strtotime($order->OcularVisitDate));
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

    <!-- Products Table (if any) -->
    <?php if (isset($order) && $order): ?>
        <?php
        $this->load->model('Order_model');
        $order_items = $this->Order_model->get_order_customizations($order->OrderID);
        ?>
        <section class="order-products">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Customization</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($order_items)): ?>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <?php 
                                        $placeholder_svg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlJSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjOWNhM2FmIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+';
                                        $product_img = $placeholder_svg;
                                        
                                        $image_raw = $item->ImageUrl ?? '';
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
                                        <img src="<?= $product_img ?>" alt="<?= htmlspecialchars($item->ProductName ?? 'Product') ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; flex-shrink: 0;">
                                        <span style="font-weight: 600; color: #0f2b46;"><?= htmlspecialchars($item->ProductName ?? 'Product') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    // Build customization breakdown from item data
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
                                        if (!empty($item->GlassType)) $breakdown_fields[] = ['label' => 'Type', 'value' => $item->GlassType];
                                        if (!empty($item->Engraving) && $item->Engraving !== 'None') $breakdown_fields[] = ['label' => 'Engraving', 'value' => $item->Engraving];
                                    }
                                    
                                    $has_breakdown = count($breakdown_fields) > 0;
                                    ?>
                                    <?php if ($has_breakdown): ?>
                                        <?php
                                        // Show first 2 specs
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
                                    <?php else: ?>
                                        <span>Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $item->Quantity ?></td>
                                <td>₱<?= number_format($item->EstimatePrice ?? $item->UnitPrice ?? 0, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px;">No items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?= base_url('track_order?order=' . (isset($order) && $order ? $order->OrderID : '')) ?>" class="btn-track-order" style="display: inline-block; padding: 12px 30px; background: #02455F; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; margin-right: 15px;">
            Track Booking
        </a>
        <a href="<?= base_url('products') ?>" class="btn-continue-shopping" style="display: inline-block; padding: 12px 30px; background: #f3f4f6; color: #02455F; text-decoration: none; border-radius: 6px; font-weight: 600;">
            Continue Shopping
        </a>
    </div>
</main>
    </main>

</div>

<script>
// Customization breakdown modal handler
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('view-breakdown-btn')) {
        e.preventDefault();
        const btn = e.target;
        const breakdownData = btn.getAttribute('data-breakdown');
        if (!breakdownData) return;
        
        let breakdownFields = [];
        try {
            breakdownFields = JSON.parse(breakdownData);
        } catch (err) {
            console.error('Failed to parse breakdown data:', err);
            return;
        }
        
        let contentHtml = '<div class="breakdown-list" style="padding:0;">';
        breakdownFields.forEach(function(field) {
            const label = field.label || '';
            const value = field.value || field.val || '';
            if (!value || value === '' || value === 'None') {
                contentHtml += '<div style="margin-bottom:16px; padding:12px; background:#f9fafb; border-left:4px solid #d1d5db; border-radius:4px;"><strong style="display:block;color:#1f2937; margin-bottom:6px; font-size:14px;">' + label + '</strong><div style="color:#9ca3af; font-style:italic; font-size:13px;">Not specified</div></div>';
            } else {
                contentHtml += '<div style="margin-bottom:16px; padding:12px; background:#f0f9ff; border-left:4px solid:#3b82f6; border-radius:4px;"><strong style="display:block;color:#1e40af; margin-bottom:6px; font-size:14px;">' + label + '</strong><div style="color:#1f2937; font-size:14px; font-weight:500;">' + value + '</div></div>';
            }
        });
        contentHtml += '</div>';
        
        // Create or update modal
        let modal = document.getElementById('breakdownModal');
        if (!modal) {
            const modalHtml = '<div id="breakdownModal" class="modal-backdrop" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:10000;"><div class="modal-content" style="max-width:720px;width:90%;max-height:85vh;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.3);"><div class="modal-header" style="background:#1e3a8a;color:#fff;padding:16px 20px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;"><h3 style="margin:0;font-size:20px;font-weight:700;">2D Customization Breakdown</h3><button class="modal-close" id="breakdownModalClose" style="background:rgba(255,255,255,0.2);border:none;color:#fff;font-size:28px;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background=\'rgba(255,255,255,0.3)\';" onmouseout="this.style.background=\'rgba(255,255,255,0.2)\';">×</button></div><div class="modal-body" id="breakdownModalBody" style="padding:24px;background:#fff;border-radius:0 0 12px 12px;"></div></div></div>';
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            modal = document.getElementById('breakdownModal');
            document.getElementById('breakdownModalClose').addEventListener('click', function() {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            });
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        }
        
        document.getElementById('breakdownModalBody').innerHTML = contentHtml;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('breakdownModal');
        if (modal && modal.style.display === 'flex') {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
});
</script>
