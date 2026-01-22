<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/list_product.css'); ?>">

<script>
    const BASE_URL = "<?= base_url(); ?>";
</script>

<section class="your-order">
    <h3>My Purchases</h3>

    <!-- Tabs Navigation -->
    <div class="purchase-tabs">
        <a href="<?= base_url('my_purchases?filter=all') ?>" 
           class="tab-link <?= (isset($current_filter) && $current_filter === 'all') ? 'active' : '' ?>" 
           data-filter="all">
            All
        </a>
        <a href="<?= base_url('my_purchases?filter=to_receive') ?>" 
           class="tab-link <?= (isset($current_filter) && $current_filter === 'to_receive') ? 'active' : '' ?>" 
           data-filter="to_receive">
            To Receive
        </a>
        <a href="<?= base_url('my_purchases?filter=completed') ?>" 
           class="tab-link <?= (isset($current_filter) && $current_filter === 'completed') ? 'active' : '' ?>" 
           data-filter="completed">
            Complete
        </a>
        <a href="<?= base_url('my_purchases?filter=cancelled') ?>" 
           class="tab-link <?= (isset($current_filter) && $current_filter === 'cancelled') ? 'active' : '' ?>" 
           data-filter="cancelled">
            Cancelled
        </a>
    </div>

    <div class="order-content">
        <table class="purchase-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Delivery</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($order_items)): ?>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
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
                                <img src="<?= $product_img ?>"
                                    alt="<?= htmlspecialchars($item->ProductName ?? 'Product') ?>" class="prod-img">
                            </td>

                            <td class="prod-name">
                                <?= htmlspecialchars($item->ProductName ?? 'Unknown Product') ?>
                            </td>

                            <td class="price-col">₱<?= number_format($item->EstimatePrice ?? 0, 2) ?></td>

                            <td class="qty-col"><?= $item->Quantity ?? 1 ?></td>

                            <td class="subtotal-col">₱<?= number_format(($item->EstimatePrice ?? 0) * ($item->Quantity ?? 1), 2) ?></td>

                            <td>
                                <?php
                                $order_status = strtolower($item->OrderStatus ?? '');
                                $badge_class = 'delivered-badge';
                                $badge_text = 'Delivered';
                                
                                // Determine badge style and text based on order status
                                if (in_array($order_status, ['completed'])) {
                                    $badge_class = 'delivered-badge approved-badge';
                                    $badge_text = 'Delivered on ' . date("M j", strtotime($item->DeliveryDate ?? $item->OrderDate ?? 'now'));
                                } elseif (in_array($order_status, ['cancelled', 'disapproved', 'returned'])) {
                                    $badge_class = 'cancelled-badge';
                                    $badge_text = ucfirst($order_status);
                                } elseif (in_array($order_status, ['pending review', 'awaiting admin', 'ready to approve', 'pending payment', 'paid', 'payment verified'])) {
                                    $badge_class = 'pending-badge';
                                    $badge_text = 'In Process';
                                } elseif (in_array($order_status, ['approved', 'ocular pending'])) {
                                    $badge_class = 'approved-badge';
                                    $badge_text = 'Approved';
                                } elseif (in_array($order_status, ['in fabrication'])) {
                                    $badge_class = 'fabrication-badge';
                                    $badge_text = 'In Fabrication';
                                } elseif (in_array($order_status, ['ready for installation'])) {
                                    $badge_class = 'ready-badge';
                                    $badge_text = 'Ready for Delivery';
                                } else {
                                    $badge_text = 'Delivered on ' . date("M j", strtotime($item->DeliveryDate ?? $item->OrderDate ?? 'now'));
                                }
                                ?>
                                <a href="<?= base_url('track_order?order=' . ($item->OrderID ?? '')) ?>" class="<?= $badge_class ?>">
                                    <?= $badge_text ?>
                                    <span class="arrow">▸</span>
                                </a>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 40px;">
                            <div class="empty-purchases">
                                <i class="fas fa-shopping-bag" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                                <p>No purchases found.</p>
                                <a href="<?= base_url('products') ?>" class="btn-shop">Start Shopping</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</section>
