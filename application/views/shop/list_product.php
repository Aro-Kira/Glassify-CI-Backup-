<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/list_product.css'); ?>">

<script>
    const BASE_URL = "<?= base_url(); ?>";
</script>

<section class="your-order">
    <h3>My Purchases</h3>

    <div class="order-content">
        <table class="purchase-table">
            <thead>
                <tr>
                    <th>Products</th>
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
                                <img src="<?= base_url('uploads/products/' . ($item->ImageUrl ?? 'default.jpg')) ?>"
                                    alt="<?= htmlspecialchars($item->ProductName ?? 'Product') ?>" class="prod-img">
                            </td>

                            <td class="prod-name">
                                <?= htmlspecialchars($item->ProductName ?? 'Unknown Product') ?>
                            </td>

                            <td class="price-col">₱<?= number_format($item->EstimatePrice ?? 0, 2) ?></td>

                            <td class="qty-col"><?= $item->Quantity ?? 1 ?></td>

                            <td class="subtotal-col">₱<?= number_format(($item->EstimatePrice ?? 0) * ($item->Quantity ?? 1), 2) ?></td>

                            <td>
                                <a href="<?= base_url('track_order?order=' . ($item->OrderID ?? '')) ?>" class="delivered-badge">
                                    Delivered on <?= date("M j", strtotime($item->DeliveryDate ?? $item->OrderDate ?? 'now')) ?>
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
