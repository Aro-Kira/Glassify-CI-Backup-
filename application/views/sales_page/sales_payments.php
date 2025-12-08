

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
            $order_id_formatted = '#' . ($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT));
            // Get payment status from payment table if available, otherwise from order table
            // Priority: payment.Status > order.PaymentStatus > 'Pending'
            $payment_status = 'Pending';
            if (isset($order->PaymentStatus) && !empty($order->PaymentStatus)) {
                $payment_status = $order->PaymentStatus;
            }
            
            // Only override status if it's not already 'Paid' or 'Complete'
            // Determine if status should be "Under Review" (has receipt but not paid)
            if (($payment_status === 'Pending' || $payment_status === '') && !empty($order->ReceiptPath)) {
                $payment_status = 'Under Review';
            }
            
            // Determine if overdue (more than 7 days since approval and still pending/under review)
            // Only check overdue if status is not already 'Paid' or 'Complete'
            $is_overdue = false;
            if (($payment_status === 'Pending' || $payment_status === 'Under Review' || $payment_status === '') && $order->Approved_Date) {
                $approved_date = strtotime($order->Approved_Date);
                $days_since = (time() - $approved_date) / (60 * 60 * 24);
                if ($days_since > 7) {
                    $is_overdue = true;
                    $payment_status = 'Overdue';
                }
            }
            
            // Get payment method from payment table if available, otherwise from order table
            $payment_method = isset($order->PaymentMethod) && !empty($order->PaymentMethod) ? $order->PaymentMethod : ($order->PaymentMethod ?? 'Not Selected');
            
            // If payment method is not set but receipt exists, it's E-Wallet
            if (empty($payment_method) || $payment_method === 'Not Selected') {
                if (!empty($order->ReceiptPath)) {
                    $payment_method = 'E-Wallet';
                }
            }
            
            // Use Approved_Date if available, otherwise OrderDate, or payment date
            $display_date = $order->Approved_Date ?? $order->OrderDate;
            if (empty($display_date) && !empty($order->Payment_Date)) {
                $display_date = $order->Payment_Date;
            }
            $approved_date = $display_date ? date('d/m/Y', strtotime($display_date)) : date('d/m/Y');
        ?>
        <tr data-order-id="<?php echo $order->OrderID; ?>" 
            data-price="<?php echo isset($order->PaymentAmount) ? $order->PaymentAmount : $order->TotalQuotation; ?>" 
            data-payment-method="<?php echo htmlspecialchars($payment_method); ?>"
            data-product-image="<?php echo htmlspecialchars($order->ProductImage ?? ''); ?>"
            data-payment-id="<?php echo isset($order->Payment_ID) ? $order->Payment_ID : ''; ?>"
            data-payment-status="<?php echo strtolower($payment_status); ?>"
            data-receipt-path="<?php echo htmlspecialchars($order->ReceiptPath ?? ''); ?>">
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
            } elseif ($status_class === 'overdue') {
              echo '<span class="status-badge overdue">Overdue</span>';
            } elseif ($status_class === 'under review') {
              echo '<span class="status-badge review">Under Review</span>';
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

    <!-- Receipt Image Preview -->
    <div class="form-group">
      <div class="image-preview">
        <img id="popupReceiptImage" src="" alt="Payment Receipt" style="display: none; max-width: 100%; max-height: 400px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px;">
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




