

<!-- Payments -->
<section class="order-list-section">
  <div class="section-header">
    <h2>Payments</h2>

    <div class="inventory-stats">
    <div class="stat-card stat-green">
        <div class="stat-value">₱<?php echo number_format($weekly_sales ?? 0, 2); ?></div>
        <div class="stat-title">Weekly Sales</div>
    </div>

    <div class="stat-card stat-orange">
        <div class="stat-value" id="statPendingValue"><?php echo $pending_count ?? 0; ?></div> 
        <div class="stat-title">Pending Payments</div>
    </div>

    <div class="stat-card stat-red">
        <div class="stat-value" id="statOverdueValue"><?php echo $overdue_count ?? 0; ?></div> 
        <div class="stat-title">Overdue Payments</div>
    </div>
</div>

  </div>

  <div class="order-tabs">
    <h2>Payment Tables</h2>
  </div>

    <div class="payment-filters">
    <span class="filter-tab active" data-status="all">All</span>
    <span class="filter-tab" data-status="paid">Paid</span>
    <span class="filter-tab" data-status="pending">Pending</span>
    <span class="filter-tab" data-status="review">Under Review</span>
    <span class="filter-tab" data-status="overdue">Overdue</span>
    </div>


  <div class="table-container">
    <table class="payment-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Product</th>
          <th>Method</th>
          <th>Status</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $row_num = 1;
        if (!empty($orders)): 
          foreach ($orders as $order): 
            $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
            $customer_name = $customer_name ?: 'N/A';
            $order_id_formatted = '#' . $order->OrderID;
            // Get payment status and method from payment table if available, otherwise from approved_orders
            $payment_status = isset($order->PaymentStatus) && !empty($order->PaymentStatus) ? $order->PaymentStatus : ($order->PaymentStatus ?? 'Pending');
            $payment_method = isset($order->PaymentMethod) && !empty($order->PaymentMethod) ? $order->PaymentMethod : ($order->PaymentMethod ?? 'Not Selected');
            $approved_date = $order->Approved_Date ? date('d/m/Y', strtotime($order->Approved_Date)) : date('d/m/Y', strtotime($order->OrderDate));
        ?>
        <tr data-order-id="<?php echo $order->OrderID; ?>" 
            data-price="<?php echo isset($order->PaymentAmount) ? $order->PaymentAmount : $order->TotalQuotation; ?>" 
            data-payment-method="<?php echo htmlspecialchars(isset($order->PaymentMethod) ? $order->PaymentMethod : $payment_method); ?>"
            data-product-image="<?php echo htmlspecialchars($order->ProductImage ?? ''); ?>"
            data-payment-id="<?php echo isset($order->Payment_ID) ? $order->Payment_ID : ''; ?>">
          <td><?php echo $row_num++; ?></td>
          <td><?php echo $order_id_formatted; ?></td>
          <td><?php echo $customer_name; ?></td>
          <td><?php echo $order->ProductName ?: 'N/A'; ?></td>
          <td>
            <?php if ($payment_method === 'E-Wallet'): ?>
              <span class="method-gcash">Gcash</span>
            <?php elseif ($payment_method === 'Cash on Delivery'): ?>
              <span>Cash</span>
            <?php else: ?>
              <span>Not Selected</span>
            <?php endif; ?>
          </td>
          <td>
            <?php 
            $status_class = strtolower($payment_status);
            if ($status_class === 'paid') {
              echo '<span class="status-badge paid">Paid</span>';
            } elseif ($status_class === 'failed') {
              echo '<span class="status-badge overdue">Failed</span>';
            } else {
              echo '<span class="status-badge pending">Pending</span>';
            }
            ?>
          </td>
          <td><?php echo $approved_date; ?></td>
          <td class="action-cell">⋮</td>
        </tr>
        <?php 
          endforeach; 
        else: 
        ?>
        <tr>
          <td colspan="8" style="text-align: center; padding: 20px;">No approved orders found</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
        <div class="pagination">
          <span>Showing 1-<?php echo min(10, count($orders ?? [])); ?> of <?php echo count($orders ?? []); ?> items</span>
          <div class="pagination-controls">
            <button><i class="fas fa-chevron-left"></i></button>
            <button class="active">1</button>
            <button><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
        <div id="actionMenu" class="action-menu hidden">
        <ul>
            <li><a href="#">View Receipt</a></li>
            <li><a href="#">Cancel</a></li>
        </ul>
        </div>

<!-- Popup Overlay -->
<div class="popup-overlay" id="productPopup">
  <div class="popup">
    <span class="close-btn" id="closePopup">&times;</span>
    <h3>Order ID: <span id="popupOrderId">#</span></h3>

    <!-- Image Preview -->
    <div class="form-group">
      <div class="image-preview">
        <img id="popupProductImage" src="" alt="Product Image" style="display: none;">
      </div>
    </div>

    <!-- Form Fields -->
    <div class="form-group">
      <label>Customer: <span id="popupCustomer"></span></label>
    </div>

    <div class="form-group">
      <label>Price:</label>
      <div class="price-input">
        <span>₱</span>
        <input type="number" id="popupPrice" class="input-text" readonly>
      </div>
    </div>

    <div class="form-group method-field">
        <label>Method:</label>
    </div>


    <!-- Action Buttons -->
    <div class="popup-actions">
      <button class="save-btn">Mark as Paid</button>
      <button class="cancel-btn">Cancel</button>
    </div>
  </div>
</div>



      </section>
    </main>
    </div>
    <script>
        const base_url = '<?php echo base_url(); ?>';
    </script>
    <script src="<?php echo base_url('assets/js/sales-js/payments-action.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/sales-js/payment-filter.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/sales-js/view-receipt-payments.js'); ?>"></script>




