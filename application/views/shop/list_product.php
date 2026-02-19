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
        <a href="<?= base_url('my_purchases?filter=ongoing') ?>" 
           class="tab-link <?= (isset($current_filter) && ($current_filter === 'on_going' || $current_filter === 'ongoing')) ? 'active' : '' ?>" 
           data-filter="ongoing">
            Ongoing
        </a>
        <a href="<?= base_url('my_purchases?filter=completed') ?>" 
           class="tab-link <?= (isset($current_filter) && $current_filter === 'completed') ? 'active' : '' ?>" 
           data-filter="completed">
            Completed
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
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>View Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($order_items)): ?>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <!-- Order ID (formatted: prefer OrderNumber as GI###) -->
                            <td>
                                <?php
                                if (!empty($item->OrderNumber)) {
                                    $on = trim($item->OrderNumber);
                                    if (preg_match('/^\d+$/', $on)) {
                                        echo 'GI' . str_pad($on, 3, '0', STR_PAD_LEFT);
                                    } else {
                                        echo htmlspecialchars($on);
                                    }
                                } elseif (!empty($item->OrderID)) {
                                    // Fallback to formatted OrderID (GI###)
                                    echo 'GI' . str_pad($item->OrderID, 3, '0', STR_PAD_LEFT);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>

                            <!-- Product (Image + Name) -->
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
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="<?= $product_img ?>" alt="<?= htmlspecialchars($item->ProductName ?? 'Product') ?>" class="prod-img" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">
                                    <span><?= htmlspecialchars($item->ProductName ?? 'Unknown Product') ?></span>
                                </div>
                            </td>

                            <?php
                            $qty = (isset($item->Quantity) && intval($item->Quantity) > 0) ? intval($item->Quantity) : 1;
                            $unit = floatval($item->TotalAmount ?? 0);
                            if ($unit <= 0) {
                                $unit_price = floatval($item->EstimatePrice ?? ($item->UnitPrice ?? 0));
                                $unit = $unit_price * $qty;
                            }
                            $total = $unit;
                            ?>
                            <td><?= $qty ?></td>
                            <td>
                                <?php
                                // Unified order flow status display
                                $order_status = strtolower($item->OrderStatus ?? '');
                                $status_text = '';
                                
                                // Map statuses to customer-friendly display
                                $status_map = [
                                    'awaiting admin' => 'Order Placed',
                                    'pending review' => 'Order Placed',
                                    'ready to approve' => 'Order Placed',
                                    'order placed' => 'Order Placed',
                                    'approved' => 'Approved',
                                    'ocular pending' => 'Ocular Visit Pending',
                                    'ocular visit' => 'Ocular Visit',
                                    'booking requested' => 'Booking Requested',
                                    'booking confirmed' => 'Booking Confirmed',
                                    'in fabrication' => 'In Fabrication',
                                    'ready for installation' => 'Ready for Installation',
                                    'installation' => 'Installation',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                    'disapproved' => 'Disapproved',
                                    'returned' => 'Returned'
                                ];
                                
                                $status_text = $status_map[$order_status] ?? ucwords(str_replace('_', ' ', $order_status));
                                echo htmlspecialchars($status_text);
                                ?>
                            </td>
                            <td><?= !empty($item->OrderDate) ? date('M d, Y', strtotime($item->OrderDate)) : '-' ?></td>
                            <td>
                                <a href="<?= base_url('track_order?order=' . ($item->OrderID ?? '')) ?>" class="btn btn-details">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 40px;">
                            <div class="empty-purchases" style="text-align:center;">
                                <?php if (isset($current_filter) && ($current_filter === 'all' || $current_filter === null || $current_filter === '')): ?>
                                    <p style="font-size: 1.05em; margin-bottom: 20px;">You have no orders yet.</p>
                                    <a href="<?= base_url('products') ?>" class="btn-shop" style="margin-top: 6px;">Browse Products</a>
                                <?php elseif (isset($current_filter) && ($current_filter === 'ongoing' || $current_filter === 'to_receive')): ?>
                                    <p style="font-size: 1.05em; margin-bottom: 12px;">You have no ongoing orders.</p>
                                <?php elseif (isset($current_filter) && $current_filter === 'completed'): ?>
                                    <p style="font-size: 1.05em; margin-bottom: 12px;">You have no completed orders.</p>
                                <?php elseif (isset($current_filter) && $current_filter === 'cancelled'): ?>
                                    <p style="font-size: 1.05em; margin-bottom: 12px;">You have no cancelled orders.</p>
                                <?php else: ?>
                                    <p style="font-size: 1.05em; margin-bottom: 12px;">No purchases found.</p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (!empty($order_items) && isset($total_items)): ?>
    <!-- Pagination -->
    <div class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; padding: 20px; border-top: 1px solid #e5e7eb;">
        <div class="pagination-info" style="color: #6b7280; font-size: 14px;">
            Showing <?= (($current_page - 1) * $per_page) + 1 ?> to <?= min($current_page * $per_page, $total_items) ?> of <?= $total_items ?> results
        </div>
        <?php if (isset($total_pages) && $total_pages > 1): ?>
        <div class="pagination-controls" style="display: flex; gap: 8px;">
            <?php
            $filter_param = isset($current_filter) && $current_filter !== 'all' ? '&filter=' . $current_filter : '';
            ?>
            
            <?php if ($current_page > 1): ?>
                <a href="<?= base_url('my_purchases?page=1' . $filter_param) ?>" 
                   class="pagination-btn" 
                   style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; text-decoration: none; background: #fff; transition: all 0.2s;">
                    &laquo; First
                </a>
                <a href="<?= base_url('my_purchases?page=' . ($current_page - 1) . $filter_param) ?>" 
                   class="pagination-btn" 
                   style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; text-decoration: none; background: #fff; transition: all 0.2s;">
                    &lsaquo; Prev
                </a>
            <?php endif; ?>
            
            <?php
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <a href="<?= base_url('my_purchases?page=' . $i . $filter_param) ?>" 
                   class="pagination-btn <?= $i === $current_page ? 'active' : '' ?>" 
                   style="padding: 8px 14px; border: 1px solid <?= $i === $current_page ? '#0d3d4d' : '#d1d5db' ?>; border-radius: 6px; color: <?= $i === $current_page ? '#fff' : '#374151' ?>; background: <?= $i === $current_page ? '#0d3d4d' : '#fff' ?>; text-decoration: none; font-weight: <?= $i === $current_page ? '600' : '400' ?>; transition: all 0.2s;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="<?= base_url('my_purchases?page=' . ($current_page + 1) . $filter_param) ?>" 
                   class="pagination-btn" 
                   style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; text-decoration: none; background: #fff; transition: all 0.2s;">
                    Next &rsaquo;
                </a>
                <a href="<?= base_url('my_purchases?page=' . $total_pages . $filter_param) ?>" 
                   class="pagination-btn" 
                   style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; text-decoration: none; background: #fff; transition: all 0.2s;">
                    Last &raquo;
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>

<style>
.pagination-btn:hover:not(.active) {
    background: #f3f4f6 !important;
    border-color: #9ca3af !important;
}
</style>