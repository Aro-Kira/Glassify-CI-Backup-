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
        <div class="step completed">Cart</div>
        <div class="divider"></div>
        <div class="step completed">Payment</div>
        <div class="divider"></div>
        <div class="step active">Complete</div>
    </div>
</div>

<main>
    <!-- Confirmation -->
    <div class="confirmation">
        <?php if (isset($payment_status) && $payment_status === 'succeeded'): ?>
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
        <div class="info-box">
            <p><strong>Order ID:</strong> <?= isset($order) && $order ? htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT)) : 'N/A' ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($order->PaymentMethod ?? 'Card / E-Wallet') ?></p>
            <p><strong>Payment Status:</strong> <span class="status-badge" style="color: <?= (isset($payment_status) && $payment_status === 'succeeded') ? '#38a169' : '#f59e0b' ?>">
                <?= (isset($payment_status) && $payment_status === 'succeeded') ? 'Paid' : 'Pending Verification' ?>
            </span></p>
            <p><strong>Transaction ID:</strong> <?= isset($order) && $order && isset($payment) && $payment->Transaction_ID ? htmlspecialchars($payment->Transaction_ID) : 'TXN' . (isset($order) && $order ? date('Ymd', strtotime($order->OrderDate)) . str_pad($order->OrderID, 6, '0', STR_PAD_LEFT) : 'N/A') ?></p>
            <p><strong>Order Date:</strong> <?= isset($order) && $order ? date('F d, Y', strtotime($order->OrderDate)) : date('F d, Y') ?></p>
            <p><strong>Status:</strong> <span class="status-badge"><?= isset($order) && $order ? htmlspecialchars($order->Status) : 'Pending Payment' ?></span></p>
        </div>
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
                        <th>Product</th>
                        <th>Customization</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
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
                                        <img src="<?= $product_img ?>" alt="<?= $item->ProductName ?>"
                                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; flex-shrink: 0;"
                                            onerror="this.onerror=null;this.alt='Image unavailable';this.src='<?= $placeholder_svg ?>';">
                                        <span style="font-weight: 600; color: #0f2b46;"><?= htmlspecialchars($item->ProductName) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($item->DesignRef)): ?>
                                        <img src="<?= base_url($item->DesignRef) ?>" alt="Custom Design" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;"
                                             onerror="this.onerror=null;this.src='<?= $placeholder_svg ?>';">
                                    <?php else: ?>
                                        <span>Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td>₱<?= number_format($item->EstimatePrice ?? $item->UnitPrice ?? 0, 2) ?></td>
                                <td><?= $item->Quantity ?></td>
                                <td>₱<?= number_format(($item->EstimatePrice ?? $item->UnitPrice ?? 0) * $item->Quantity, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">No items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 600;">Total:</td>
                        <td style="font-weight: 700; font-size: 1.1em;">₱<?= isset($order) && $order ? number_format($order->TotalAmount, 2) : '0.00' ?></td>
                    </tr>
                </tfoot>
            </table>
        </section>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?= base_url('track_order?order=' . (isset($order) && $order ? $order->OrderID : '')) ?>" class="btn-track-order" style="display: inline-block; padding: 12px 30px; background: #02455F; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; margin-right: 15px;">
            Track Order
        </a>
        <a href="<?= base_url('products') ?>" class="btn-continue-shopping" style="display: inline-block; padding: 12px 30px; background: #f3f4f6; color: #02455F; text-decoration: none; border-radius: 6px; font-weight: 600;">
            Continue Shopping
        </a>
    </div>
</main>
