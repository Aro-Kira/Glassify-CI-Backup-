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
