<section id="orders" class="page">
    <div class="dash-tabs">
        <h2>Order <span class="subtitle"><span id="total-orders-count"><?php echo $total_orders; ?></span> Orders found</span></h2>
    </div>

    <div class="order-tabs">
        <div class="tab-links-container">
            <a href="#" class="tab-link active" id="pending-id" data-tab="pending">
                Pending Review <span class="tab-count" id="pending-count"><?php echo $pending_count; ?></span>
            </a>

            <a href="#" class="tab-link" id="awaiting-id" data-tab="awaiting">
                Awaiting Admin <span class="tab-count" id="awaiting-count"><?php echo $awaiting_count; ?></span>
            </a>

            <a href="#" class="tab-link" id="ready-id" data-tab="ready">
                Ready to Approve <span class="tab-count" id="ready-count"><?php echo $ready_count; ?></span>
            </a>
        </div>
        <!-- Date filter disabled as per requirements -->
        <!-- <div class="order-date" id="order-date-picker" style="cursor: pointer; position: relative; z-index: 1; display: none;">
            <input type="date" id="date-filter" style="position: absolute; top: 0; left: 0; opacity: 0; width: 100%; height: 100%; cursor: pointer; z-index: 2;" />
            <span id="date-display-month" style="position: relative; z-index: 1; pointer-events: none;"><?php echo date('M'); ?></span>
            <span id="date-display-year" style="position: relative; z-index: 1; pointer-events: none;"><?php echo date('Y'); ?></span>
        </div> -->
    </div>

    <input type="text" placeholder="Search products..." class="search-box" id="product-search">

    <section id="tab-pending" class="order-section active">
        <table class="order-table" id="pending-table">
            <thead>
                <tr class="order-header">
                    <th>#</th>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="pending-tbody">
                <?php 
                $pending_orders = array_filter($orders, function($o) { return $o->Status === 'Pending Review'; });
                $row_num = 1;
                foreach ($pending_orders as $order): 
                    $order_id_formatted = '#' . $order->OrderID;
                    $address = $order->Address ? (strlen($order->Address) > 20 ? substr($order->Address, 0, 20) . '...' : $order->Address) : 'N/A';
                    $product_name = $order->ProductName ?: 'N/A';
                ?>
                <tr data-order-id="<?php echo $order->OrderID; ?>" data-product-name="<?php echo strtolower($product_name); ?>">
                    <td><?php echo $row_num++; ?></td>
                    <td><?php echo $order_id_formatted; ?></td>
                    <td><?php echo $product_name; ?></td>
                    <td><?php echo $address; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($order->OrderDate)); ?></td>
                    <td>₱<?php echo number_format($order->TotalQuotation, 2); ?></td>
                    <td><button class="btn-approve" data-order-id="<?php echo $order->OrderID; ?>">Request Approval</button></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($pending_orders)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No pending orders found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section id="tab-awaiting" class="order-section">
        <table class="order-table awaiting" id="awaiting-table">
            <thead>
                <tr class="order-header">
                    <th>#</th>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="awaiting-tbody">
                <?php 
                $awaiting_orders = array_filter($orders, function($o) { return $o->Status === 'Awaiting Admin'; });
                $row_num = 1;
                foreach ($awaiting_orders as $order): 
                    $order_id_formatted = '#' . $order->OrderID;
                    $address = $order->Address ? (strlen($order->Address) > 20 ? substr($order->Address, 0, 20) . '...' : $order->Address) : 'N/A';
                    $product_name = $order->ProductName ?: 'N/A';
                ?>
                <tr data-order-id="<?php echo $order->OrderID; ?>" data-product-name="<?php echo strtolower($product_name); ?>">
                    <td><?php echo $row_num++; ?></td>
                    <td><?php echo $order_id_formatted; ?></td>
                    <td><?php echo $product_name; ?></td>
                    <td><?php echo $address; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($order->OrderDate)); ?></td>
                    <td>₱<?php echo number_format($order->TotalQuotation, 2); ?></td>
                    <td>
                        <button class="btn-view" data-order-id="<?php echo $order->OrderID; ?>">
                            <img src="<?php echo base_url('assets/images/img_admin/search-icon.svg'); ?>" alt="View Icon" class="button-icon">
                            View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($awaiting_orders)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No awaiting orders found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section id="tab-ready" class="order-section">
        <table class="order-table ready" id="ready-table">
            <thead>
                <tr class="order-header">
                    <th>#</th>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Admin Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="ready-tbody">
                <?php 
                $ready_orders = array_filter($orders, function($o) { 
                    return $o->Status === 'Ready to Approve'; 
                });
                $row_num = 1;
                foreach ($ready_orders as $order): 
                    $order_id_formatted = '#' . $order->OrderID;
                    $address = $order->Address ? (strlen($order->Address) > 20 ? substr($order->Address, 0, 20) . '...' : $order->Address) : 'N/A';
                    $product_name = $order->ProductName ?: 'N/A';
                    $status_class = 'ready';
                ?>
                <tr data-order-id="<?php echo $order->OrderID; ?>" data-product-name="<?php echo strtolower($product_name); ?>">
                    <td><?php echo $row_num++; ?></td>
                    <td><?php echo $order_id_formatted; ?></td>
                    <td><?php echo $product_name; ?></td>
                    <td><?php echo $address; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($order->OrderDate)); ?></td>
                    <td>
                        <?php 
                        // Display "Approved" or "Disapproved" based on AdminStatus from ready_to_approve_orders table
                        $display_status = 'Pending';
                        if (isset($order->AdminStatus)) {
                            // For ready_to_approve_orders, use AdminStatus field
                            $display_status = $order->AdminStatus === 'Approved' ? 'Approved' : 'Disapproved';
                        } elseif ($order->Status === 'Ready to Approve' || $order->Status === 'Approved') {
                            $display_status = 'Approved';
                        } elseif ($order->Status === 'Disapproved' || $order->Status === 'Rejected') {
                            $display_status = 'Disapproved';
                        }
                        ?>
                        <span class="status <?php echo strtolower($display_status); ?>"><?php echo $display_status; ?></span>
                    </td>
                    <td><button class="btn-check" data-order-id="<?php echo $order->OrderID; ?>">Check</button></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($ready_orders)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No ready orders found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
    <div class="pagination">
        <span id="pagination-info">Showing <span id="pagination-start">1</span>-<span id="pagination-end">10</span> of <span id="pagination-total">0</span> items</span>
        <div class="pagination-controls">
            <button id="prev-page"><i class="fas fa-chevron-left"></i></button>
            <button class="active" id="current-page">1</button>
            <button id="next-page"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</section>
</main>
</div>

<div class="popup-overlay" id="approvalPopup">
    <div class="popup">
        <span class="close-btn" id="closePopup">&times;</span>

        <h3 class="popup-title">Request Approval</h3>

        <div class="popup-content-grid">

            <div class="order-details">
                <h4 class="details-title">Order Details</h4>
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value" id="popup-order-id">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Product:</span>
                    <span class="detail-value" id="popup-product">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value" id="popup-address">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value" id="popup-date">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Shape:</span>
                    <span class="detail-value" id="popup-shape">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Dimension:</span>
                    <span class="detail-value" id="popup-dimension">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Type:</span>
                    <span class="detail-value" id="popup-type">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Thickness:</span>
                    <span class="detail-value" id="popup-thickness">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Edge Work:</span>
                    <span class="detail-value" id="popup-edgework">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Frame Type:</span>
                    <span class="detail-value" id="popup-frametype">-</span>
                </div>
                <div class="detail-row" id="popup-ledbacklight-row" style="display: none;">
                    <span class="detail-label">LED Backlight:</span>
                    <span class="detail-value" id="popup-ledbacklight">-</span>
                </div>
                <div class="detail-row" id="popup-dooroperation-row" style="display: none;">
                    <span class="detail-label">Door Operation:</span>
                    <span class="detail-value" id="popup-dooroperation">-</span>
                </div>
                <div class="detail-row" id="popup-configuration-row" style="display: none;">
                    <span class="detail-label">Configuration:</span>
                    <span class="detail-value" id="popup-configuration">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Engraving:</span>
                    <span class="detail-value" id="popup-engraving">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">File Attached:</span>
                    <a href="#" class="detail-value file-link" id="popup-file-link" target="_blank" style="display: none;">-</a>
                    <span class="detail-value" id="popup-file-text" style="display: none;">N/A</span>
                </div>
                <div class="detail-row total-row">
                    <span class="detail-label">Total Quotation (₱):</span>
                    <span class="detail-value total-price" id="popup-total">-</span>
                </div>
            </div>

            <div class="notes-barcode">
                <h4 class="notes-title">Notes</h4>
                <textarea class="notes-textarea" placeholder="empty..." id="popup-notes"></textarea>
            </div>

        </div>

        <div class="popup-actions approval-actions">
            <button class="save-btn submit-btn">Submit to Admin</button>
            <button class="cancel-btn">Cancel</button>
        </div>
    </div>
</div>

<div class="popup-overlay" id="awaitingPopup">
    <div class="popup">
        <span class="close-btn" id="closeAwaitingPopup">&times;</span>

        <h3 class="popup-title">Awaiting Approval</h3>

        <div class="popup-content-grid">

            <div class="order-details">
                <h4 class="details-title">Order Details</h4>
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value" id="awaiting-order-id">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Product:</span>
                    <span class="detail-value" id="awaiting-product">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value" id="awaiting-address">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value" id="awaiting-date">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Shape:</span>
                    <span class="detail-value" id="awaiting-shape">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Dimension:</span>
                    <span class="detail-value" id="awaiting-dimension">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Type:</span>
                    <span class="detail-value" id="awaiting-type">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Thickness:</span>
                    <span class="detail-value" id="awaiting-thickness">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Edge Work:</span>
                    <span class="detail-value" id="awaiting-edgework">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Frame Type:</span>
                    <span class="detail-value" id="awaiting-frametype">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Engraving:</span>
                    <span class="detail-value" id="awaiting-engraving">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">File Attached:</span>
                    <a href="#" class="detail-value file-link" id="awaiting-file-link" target="_blank" style="display: none;">-</a>
                    <span class="detail-value" id="awaiting-file-text" style="display: none;">N/A</span>
                </div>
                <div class="detail-row total-row">
                    <span class="detail-label">Total Quotation (₱):</span>
                    <span class="detail-value total-price" id="awaiting-total">-</span>
                </div>
            </div>

            <div class="notes-barcode">
                <h4 class="notes-title">Notes</h4>
                <textarea class="notes-textarea" placeholder="empty..." id="awaiting-notes" readonly></textarea>
            </div>

        </div>

        <div class="popup-actions approval-actions">
            <button class="cancel-btn">Close</button>
        </div>
    </div>
</div>

<div class="popup-overlay" id="approvedPopup">
    <div class="popup">
        <span class="close-btn" id="closeApprovedPopup">&times;</span>

        <h3 class="popup-title">Request Approval - <span class="status-approved">Approved</span></h3>

        <div class="popup-content-grid">

            <div class="order-details">
                <h4 class="details-title">Order Details</h4>
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value" id="approved-order-id">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Product:</span>
                    <span class="detail-value" id="approved-product">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value" id="approved-address">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value" id="approved-date">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Shape:</span>
                    <span class="detail-value" id="approved-shape">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Dimension:</span>
                    <span class="detail-value" id="approved-dimension">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Type:</span>
                    <span class="detail-value" id="approved-type">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Thickness:</span>
                    <span class="detail-value" id="approved-thickness">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Edge Work:</span>
                    <span class="detail-value" id="approved-edgework">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Frame Type:</span>
                    <span class="detail-value" id="approved-frametype">-</span>
                </div>
                <div class="detail-row" id="approved-ledbacklight-row" style="display: none;">
                    <span class="detail-label">LED Backlight:</span>
                    <span class="detail-value" id="approved-ledbacklight">-</span>
                </div>
                <div class="detail-row" id="approved-dooroperation-row" style="display: none;">
                    <span class="detail-label">Door Operation:</span>
                    <span class="detail-value" id="approved-dooroperation">-</span>
                </div>
                <div class="detail-row" id="approved-configuration-row" style="display: none;">
                    <span class="detail-label">Configuration:</span>
                    <span class="detail-value" id="approved-configuration">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Engraving:</span>
                    <span class="detail-value" id="approved-engraving">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">File Attached:</span>
                    <a href="#" class="detail-value file-link" id="approved-file-link" target="_blank" style="display: none;">-</a>
                    <span class="detail-value" id="approved-file-text" style="display: none;">N/A</span>
                </div>
                <div class="detail-row total-row">
                    <span class="detail-label">Total Quotation (₱):</span>
                    <span class="detail-value total-price" id="approved-total">-</span>
                </div>
            </div>

            <div class="notes-barcode">
                <h4 class="notes-title">Notes</h4>
                <textarea class="notes-textarea" placeholder="empty..." id="approved-notes" readonly></textarea>
            </div>

        </div>

        <div class="popup-actions approval-actions">
            <button class="approved-btn">Approve Order</button>
            <button class="cancel-btn">Cancel</button>
        </div>
    </div>
</div>

<div class="popup-overlay" id="disapprovedPopup">
    <div class="popup">
        <span class="close-btn" id="closeDisapprovedPopup">&times;</span>

        <h3 class="popup-title">Request Approval - <span class="status-declined">Declined</span></h3>

        <div class="popup-content-grid">

            <div class="order-details">
                <h4 class="details-title">Order Details</h4>
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value" id="disapproved-order-id">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Product:</span>
                    <span class="detail-value" id="disapproved-product">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value" id="disapproved-address">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value" id="disapproved-date">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Shape:</span>
                    <span class="detail-value" id="disapproved-shape">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Dimension:</span>
                    <span class="detail-value" id="disapproved-dimension">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Type:</span>
                    <span class="detail-value" id="disapproved-type">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Thickness:</span>
                    <span class="detail-value" id="disapproved-thickness">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Edge Work:</span>
                    <span class="detail-value" id="disapproved-edgework">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Frame Type:</span>
                    <span class="detail-value" id="disapproved-frametype">-</span>
                </div>
                <div class="detail-row" id="disapproved-ledbacklight-row" style="display: none;">
                    <span class="detail-label">LED Backlight:</span>
                    <span class="detail-value" id="disapproved-ledbacklight">-</span>
                </div>
                <div class="detail-row" id="disapproved-dooroperation-row" style="display: none;">
                    <span class="detail-label">Door Operation:</span>
                    <span class="detail-value" id="disapproved-dooroperation">-</span>
                </div>
                <div class="detail-row" id="disapproved-configuration-row" style="display: none;">
                    <span class="detail-label">Configuration:</span>
                    <span class="detail-value" id="disapproved-configuration">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Engraving:</span>
                    <span class="detail-value" id="disapproved-engraving">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">File Attached:</span>
                    <a href="#" class="detail-value file-link" id="disapproved-file-link" target="_blank" style="display: none;">-</a>
                    <span class="detail-value" id="disapproved-file-text" style="display: none;">N/A</span>
                </div>
                <div class="detail-row total-row">
                    <span class="detail-label">Total Quotation (₱):</span>
                    <span class="detail-value total-price" id="disapproved-total">-</span>
                </div>
            </div>

            <div class="notes-barcode">
                <h4 class="notes-title">Notes</h4>
                <textarea class="notes-textarea" placeholder="empty..." id="disapproved-notes" readonly></textarea>
            </div>

        </div>

        <div class="popup-actions approval-actions">
            <button class="disapproved-btn">Disapprove Order</button>
            <button class="cancel-btn">Cancel</button>
        </div>
    </div>
</div>

<script>
    const base_url = '<?php echo base_url(); ?>';
    const ordersData = <?php echo json_encode($orders); ?>;
</script>
<script src="<?php echo base_url('assets/js/sales-js/sales-order-tabs.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/sales-js/sales-order-approval-btn.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/sales-js/sales-order-check-btn.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/sales-js/sales-order-view-btn.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/sales-js/sales-request-approval-handler.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/sales-js/sales-order-approve-handler.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/sales-js/sales-orders-main.js'); ?>"></script>
