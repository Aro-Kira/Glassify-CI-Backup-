<!-- Payments -->
<section class="order-list-section">
  <div class="section-header">
    <h2>Payment Management</h2>

    <div class="inventory-stats">
    <div class="stat-card stat-green">
        <div class="stat-value">₱<?php echo number_format($weekly_sales ?? 0, 2); ?></div>
        <div class="stat-title">Weekly Revenue</div>
    </div>

    <div class="stat-card stat-blue">
        <div class="stat-value">₱<?php echo number_format($monthly_sales ?? 0, 2); ?></div>
        <div class="stat-title">Monthly Revenue</div>
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
    <h2>Payment Transactions</h2>
    <button class="export-report-btn" onclick="exportPaymentReport()">
      <i class="fas fa-download"></i> Download Report
    </button>
  </div>

    <div class="payment-filters">
    <span class="filter-tab active" data-status="all">All</span>
    <span class="filter-tab" data-status="paid">Paid</span>
    <span class="filter-tab" data-status="pending">Pending</span>
    <span class="filter-tab" data-status="overdue">Overdue</span>
    
    <!-- Milestone filter -->
    <select class="milestone-filter" id="milestoneFilter">
      <option value="all">All Milestones</option>
      <option value="ocular_50">50% Ocular</option>
      <option value="fabrication_40">40% Fabrication</option>
      <option value="installation_10">10% Installation</option>
    </select>
    </div>


  <div class="table-container">
    <table class="payment-table">
      <thead>
        <tr>
          <th>#</th>
          <th class="sortable" data-sort="order-id">
            Order ID <i class="fas fa-sort"></i>
          </th>
          <th class="sortable" data-sort="customer">
            Customer <i class="fas fa-sort"></i>
          </th>
          <th>Role</th>
          <th>Product</th>
          <th>Milestone</th>
          <th class="sortable" data-sort="amount">
            Amount <i class="fas fa-sort"></i>
          </th>
          <th>Method</th>
          <th>Status</th>
          <th class="sortable" data-sort="date">
            Date <i class="fas fa-sort"></i>
          </th>
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
            
            // Get customer role
            $customer_role = ucfirst($order->customer_role ?? $order->user_role ?? 'Customer');
            
            // Get payment status from payment table if available, otherwise from order table
            $payment_status = 'Pending';
            if (isset($order->PaymentStatus) && !empty($order->PaymentStatus)) {
                $payment_status = $order->PaymentStatus;
            }
            
            // Check overdue flag from controller
            if (isset($order->is_overdue) && $order->is_overdue) {
                $payment_status = 'Overdue';
            }
            
            // Get payment method
            $payment_method = isset($order->PaymentMethod) && !empty($order->PaymentMethod) ? $order->PaymentMethod : ($order->OrderPaymentMethod ?? 'Not Selected');
            
            // If payment method is not set but receipt exists, default to GCash (legacy data)
            if (empty($payment_method) || $payment_method === 'Not Selected') {
                if (!empty($order->ReceiptPath)) {
                    $payment_method = 'GCash';
                }
            }
            
            // Determine current milestone based on order status (not stored milestone)
            $order_status = $order->OrderStatus ?? 'Pending Review';
            $milestone = 'ocular_50'; // Default
            
            // Map order status to current milestone
            if (in_array($order_status, ['In Fabrication', 'In Production', 'Quality Check'])) {
                $milestone = 'fabrication_40';
            } elseif (in_array($order_status, ['Ready for Installation', 'Installation/Delivery', 'Installed', 'Completed'])) {
                $milestone = 'installation_10';
            } elseif (in_array($order_status, ['Approved', 'Ocular Pending', 'Ocular Visit', 'Booking Confirmed', 'Quotation Available', 'Awaiting Payment'])) {
                $milestone = 'ocular_50';
            }
            
            // If payment milestone is set and payment is paid, use that instead (for historical payments)
            if (!empty($order->payment_milestone) && $payment_status === 'Paid') {
                $milestone = $order->payment_milestone;
            }
            
            $milestone_display = '';
            $milestone_class = '';
            switch($milestone) {
                case 'ocular_50':
                    $milestone_display = '50% Ocular';
                    $milestone_class = 'milestone-ocular';
                    break;
                case 'fabrication_40':
                    $milestone_display = '40% Fabrication';
                    $milestone_class = 'milestone-fabrication';
                    break;
                case 'installation_10':
                    $milestone_display = '10% Installation';
                    $milestone_class = 'milestone-installation';
                    break;
            }
            
            // Get payment amount
            $amount = isset($order->PaymentAmount) ? $order->PaymentAmount : $order->TotalQuotation;
            
            // Use Approved_Date if available, otherwise OrderDate, or payment date
            $display_date = $order->Approved_Date ?? $order->OrderDate;
            if (empty($display_date) && !empty($order->Payment_Date)) {
                $display_date = $order->Payment_Date;
            }
            $approved_date = $display_date ? date('d/m/Y', strtotime($display_date)) : date('d/m/Y');
        ?>
        <tr data-order-id="<?php echo $order->OrderID; ?>" 
            data-order-number="<?php echo $order_id_formatted; ?>"
            data-customer-name="<?php echo htmlspecialchars($customer_name); ?>"
            data-date="<?php echo $display_date; ?>"
            data-price="<?php echo $amount; ?>" 
            data-payment-method="<?php echo htmlspecialchars($payment_method); ?>"
            data-product-image="<?php echo htmlspecialchars($order->ProductImage ?? ''); ?>"
            data-payment-id="<?php echo isset($order->Payment_ID) ? $order->Payment_ID : ''; ?>"
            data-payment-status="<?php echo strtolower($payment_status); ?>"
            data-milestone="<?php echo $milestone; ?>"
            data-receipt-path="<?php echo htmlspecialchars($order->ReceiptPath ?? ''); ?>">
          <td><?php echo $row_num++; ?></td>
          <td><?php echo $order_id_formatted; ?></td>
          <td><?php echo $customer_name; ?></td>
          <td><span class="role-badge role-<?php echo strtolower($customer_role); ?>"><?php echo $customer_role; ?></span></td>
          <td><?php echo $order->ProductName ?: 'N/A'; ?></td>
          <td><span class="milestone-badge <?php echo $milestone_class; ?>"><?php echo $milestone_display; ?></span></td>
          <td>₱<?php echo number_format($amount, 2); ?></td>
          <td>
            <?php 
              // Map payment method to display name
              $method_display = 'Not Selected';
              if ($payment_method === 'GCash' || $payment_method === 'E-Wallet') {
                $method_display = '<span class="method-gcash">GCash</span>';
              } elseif ($payment_method === 'Maya') {
                $method_display = '<span class="method-gcash">Maya</span>';
              } elseif ($payment_method === 'Card' || $payment_method === 'Bank Transfer') {
                $method_display = '<span>Credit/Debit Card</span>';
              } elseif ($payment_method === 'Cash' || $payment_method === 'Cash on Delivery') {
                $method_display = '<span>Cash</span>';
              } elseif ($payment_method === 'Check') {
                $method_display = '<span>Check</span>';
              } else {
                $method_display = '<span>Not Selected</span>';
              }
              echo $method_display;
            ?>
          </td>
          <td>
            <?php 
            $status_class = strtolower($payment_status);
            if ($status_class === 'paid' || $status_class === 'verified') {
              echo '<span class="status-badge paid">Paid</span>';
            } elseif ($status_class === 'overdue') {
              echo '<span class="status-badge overdue">Overdue</span>';
            } elseif ($status_class === 'failed') {
              echo '<span class="status-badge overdue">Failed</span>';
            } else {
              echo '<span class="status-badge pending">Pending</span>';
            }
            ?>
          </td>
          <td><?php echo $approved_date; ?></td>
          <td class="timeline-action">
            <button class="btn-view-timeline" onclick="viewPaymentTimeline(<?php echo $order->OrderID; ?>); return false;">
              <i class="fas fa-eye"></i> View Timeline
            </button>
          </td>
        </tr>
        <?php 
          endforeach; 
        else: 
        ?>
        <tr>
          <td colspan="11" style="text-align: center; padding: 20px;">No payment records found</td>
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

<!-- Popup Overlay for Receipt/Payment Details -->
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

<!-- Payment Timeline Modal -->
<div class="popup-overlay" id="paymentTimelineModal">
  <div class="popup popup-timeline">
    <span class="close-btn" onclick="closeTimelineModal()">&times;</span>
    <h3 class="popup-title">Payment Progress - <span id="timelineOrderNumber">#</span></h3>
    
    <div class="timeline-header">
      <p><strong>Customer:</strong> <span id="timelineCustomer"></span> (<span id="timelineRole"></span>)</p>
      <p><strong>Total Order Value:</strong> ₱<span id="timelineTotalAmount">0</span></p>
    </div>
    
    <div class="timeline-container" id="timelineContainer">
      <!-- Timeline will be populated by JavaScript -->
    </div>
    
    <div class="timeline-progress">
      <div class="progress-text">
        <strong>Progress:</strong> <span id="timelineProgressPercent">0</span>% paid 
        (₱<span id="timelinePaidAmount">0</span> received)
      </div>
      <div class="progress-bar-container">
        <div class="progress-bar" id="timelineProgressBar" style="width: 0%"></div>
      </div>
      <div class="progress-remaining">
        <strong>Remaining:</strong> ₱<span id="timelineRemainingAmount">0</span>
      </div>
    </div>
    
    <div class="popup-actions">
      <button class="cancel-btn" onclick="closeTimelineModal()">Close</button>
    </div>
  </div>
</div>



      </section>
    </main>
    </div>
    <script>
        const base_url = '<?php echo base_url(); ?>';
        const milestone_breakdown = <?php echo json_encode($milestone_breakdown ?? ['ocular_50' => 0, 'fabrication_40' => 0, 'installation_10' => 0]); ?>;
        
        // Debug: Verify modal exists on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Payment page loaded');
            console.log('Timeline modal exists:', document.getElementById('paymentTimelineModal') !== null);
            console.log('Base URL:', base_url);
        });
    </script>
    <script src="<?php echo base_url('assets/js/admin-js/payments-action.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/admin-js/payment-filter.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/admin-js/view-receipt-payments.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/admin-js/payment-timeline.js'); ?>"></script>



