<?php
// Unified Orders Page - All orders follow the same process now
$page_title = 'Orders';
?>

<script>
  // Pass the URLs from PHP to JS
  const baseUrl = "<?php echo base_url(); ?>";
  const getOrdersUrl = "<?php echo base_url('AdminCon/get_orders_ajax'); ?>";
  const getOrderDetailsUrl = "<?php echo base_url('AdminCon/get_order_details_ajax'); ?>";
  const updateOrderStatusUrl = "<?php echo base_url('AdminCon/update_order_status'); ?>";
  const assignStaffUrl = "<?php echo base_url('AdminCon/assign_staff'); ?>";
  const exportOrderUrl = "<?php echo base_url('AdminCon/export_order'); ?>";
  const approveOrderUrl = "<?php echo base_url('AdminCon/approve_order_admin'); ?>";
  const disapproveOrderUrl = "<?php echo base_url('AdminCon/disapprove_order_admin'); ?>";
  
  // Debug: Test the orders API immediately
  console.log('=== INLINE DEBUG START ===');
  console.log('getOrdersUrl:', getOrdersUrl);
  
  // Simple fetch test
  fetch(getOrdersUrl + '?status=all&page=1&limit=10')
    .then(response => {
      console.log('Inline fetch response status:', response.status);
      return response.text();
    })
    .then(text => {
      console.log('Inline fetch response:', text);
    })
    .catch(error => {
      console.error('Inline fetch error:', error);
    });
</script>

<!-- TomSelect for searchable staff selects -->
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.default.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
<script src="<?php echo base_url('assets/js/admin-js/order-management.js?v=' . time()); ?>"></script>

<!-- Orders Section -->
<section class="order-management-section">
  <div class="section-header">
    <h2><?php echo $page_title; ?> <span class="found-text">Loading...</span></h2>
    <button class="toggle-filters-btn" id="toggle-filters-btn" title="Toggle Filters">
      <i class="fas fa-filter"></i>
      <span>Filters</span>
    </button>
  </div>

  <!-- Order Tabs -->
  <style>
    .order-tabs { display: inline-flex; align-items: center; gap: 0; margin: 14px 0; font-size: 14px; }
    .order-tabs .tab-btn { background: transparent; border: none; padding: 8px 12px; color: #212529; cursor: pointer; border-radius: 6px; display: inline-flex; align-items: center; }
    .order-tabs .tab-btn.active { background: #02455F; color: #fff; }
    .order-tabs .tab-btn:not(:last-child)::after { content: '|'; color: #6c757d; margin-left: 8px; margin-right: 8px; }
    .order-tabs .tab-btn:focus { outline: none; box-shadow: none; }
  </style>

  <div class="order-tabs" role="tablist" aria-label="Order Tabs">
    <button class="tab-btn active" id="tab-all" role="tab">All</button>
    <button class="tab-btn" id="tab-ongoing" role="tab">On Going</button>
    <button class="tab-btn" id="tab-completed" role="tab">Completed</button>
    <button class="tab-btn" id="tab-cancelled" role="tab">Cancelled</button>
  </div>

  <script>
    // Tab handlers - wire to existing filters and load action
    (function(){
      function setActive(tab){
        document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
        if(tab) tab.classList.add('active');
      }

      function applyTabFilter(statusValue, tabEl){
        var statusEl = document.getElementById('status-filter');
        var applyBtn = document.getElementById('apply-filters');
        if(statusEl) statusEl.value = statusValue;
        setActive(tabEl);
        if(applyBtn) applyBtn.click();
      }

      var tAll = document.getElementById('tab-all');
      var tOngoing = document.getElementById('tab-ongoing');
      var tCompleted = document.getElementById('tab-completed');
      var tCancelled = document.getElementById('tab-cancelled');

      if(tAll) tAll.addEventListener('click', function(){ applyTabFilter('all', tAll); });
      if(tOngoing) tOngoing.addEventListener('click', function(){ applyTabFilter('on_going', tOngoing); });
      if(tCompleted) tCompleted.addEventListener('click', function(){ applyTabFilter('Completed', tCompleted); });
      if(tCancelled) tCancelled.addEventListener('click', function(){ applyTabFilter('Cancelled', tCancelled); });
    })();
  </script>

  <!-- Filters Section -->
  <div class="filters-container">
    <div class="filter-group">
      <label for="status-filter">Status:</label>
      <select id="status-filter" class="filter-select">
        <option value="all">All</option>
        <option value="Pending Review">Pending Review</option>
        <option value="Awaiting Admin">Awaiting Admin</option>
        <option value="Approved">Approved</option>
        <option value="Disapproved">Disapproved</option>
        <option value="Ocular Pending">Ocular Pending</option>
        <option value="In Fabrication">In Fabrication</option>
        <option value="Ready for Installation">Ready for Installation</option>
        <option value="Completed">Completed</option>
        <option value="Cancelled">Cancelled</option>
      </select>
    </div>

    <div class="filter-group">
      <label for="date-range-start">Date Range:</label>
      <input type="date" id="date-range-start" class="filter-input">
      <span>to</span>
      <input type="date" id="date-range-end" class="filter-input">
    </div>

    <div class="filter-group">
      <label for="client-search">Client:</label>
      <input type="text" id="client-search" class="filter-input" placeholder="Name, email, or phone">
    </div>

    <div class="filter-group">
      <label for="order-search">Order Number:</label>
      <input type="text" id="order-search" class="filter-input" placeholder="Order ID or Number">
    </div>

    <div class="filter-group">
      <label for="month-year-filter">Month/Year:</label>
      <input type="month" id="month-year-filter" class="filter-input">
    </div>

    <button class="filter-btn" id="apply-filters">Apply Filters</button>
    <button class="filter-btn filter-btn-secondary" id="clear-filters">Clear</button>
  </div>

  <!-- Orders Table -->
  <div class="table-container">
    <table class="orders-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Order ID</th>
          <th>Client Name</th>
          <th>User Role</th>
          <th>Product Name</th>
          <th>Address</th>
          <th>Order Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="ordersTableBody">
        <tr>
          <td colspan="8" style="text-align: center; padding: 20px;">Loading orders...</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="pagination">
    <span id="pagination-info">Loading...</span>
    <div class="pagination-controls" id="pagination-controls">
      <!-- Pagination will be generated by JavaScript -->
    </div>
  </div>
</section>

<!-- Order Details Modal -->
<div class="popup-overlay" id="orderDetailsModal">
  <div class="popup popup-large modern-popup">
    <div class="popup-header-modern">
      <span class="close-btn" id="closeOrderDetails">&times;</span>
      <h3 class="popup-title-modern">
        <i class="fas fa-file-invoice" style="margin-right: 10px;"></i>Order Details
      </h3>
      <p class="popup-subtitle">Complete order information and management</p>
    </div>

    <div class="popup-content-modern order-details-content">
      <!-- Order Information Section -->
      <div class="info-card">
        <div class="info-card-header">
              <i class="fas fa-info-circle info-card-icon"></i>
                <span id="detail-order-overview" style="flex: 1; min-width: 150px; display: inline;">Order Overview</span>
        </div>
        <div class="info-card-body">
          <div class="info-grid">
            <!-- Ocular Visit Staff removed per UX request -->
            <div class="info-item">
              <span class="info-label">Order ID</span>
              <span class="info-value" id="detail-order-id">-</span>
            </div>
            <div class="info-item">
              <span class="info-label">Order Date</span>
              <span class="info-value" id="detail-order-date">-</span>
            </div>
            <div class="info-item">
              <span class="info-label">Status</span>
              <span class="info-value">
                <span class="badge" id="detail-status-badge">-</span>
              </span>
            </div>
            <div class="info-item">
              <span class="info-label">Preferred Ocular Visit Date</span>
              <span class="info-value" id="detail-preferred-ocular-date">-</span>
            </div>
          </div>
          <!-- Status History Timeline removed as not needed -->
        </div>
      </div>

      <!-- Customer Information Section -->
      <div class="info-card">
        <div class="info-card-header">
          <i class="fas fa-user info-card-icon"></i>
          <h4 class="info-card-title">Customer Information</h4>
        </div>
        <div class="info-card-body">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Customer Name</span>
              <span class="info-value" id="detail-customer-name">-</span>
            </div>
            <div class="info-item">
              <span class="info-label">Email</span>
              <span class="info-value" id="detail-customer-email">-</span>
            </div>
            <div class="info-item">
              <span class="info-label">Phone</span>
              <span class="info-value" id="detail-customer-phone">-</span>
            </div>
            <div class="info-item full-width">
              <span class="info-label">Address</span>
              <span class="info-value" id="detail-customer-address">-</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Details Section -->
      <div class="info-card">
        <div class="info-card-header">
          <i class="fas fa-box info-card-icon"></i>
          <h4 class="info-card-title">Order Details</h4>
        </div>
        <div class="info-card-body" style="padding: 0;">
          <div style="overflow-x: auto;">
            <table class="items-table">
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>Customization Details</th>
                  <th>Quantity</th>
                  <th>Design</th>
                </tr>
              </thead>
              <tbody id="detail-items-tbody">
                <!-- Items will be loaded here -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- (Ocular/Site Assessment card intentionally removed per request) -->

      <!-- Assigned Staff Section -->
      <div class="info-card">
        <div class="info-card-header">
          <i class="fas fa-users-cog info-card-icon"></i>
          <h4 class="info-card-title">Assigned Staff</h4>
        </div>
        <div class="info-card-body">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Ocular Visit Staff</span>
              <div class="info-value-with-action" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span id="detail-ocular-staff" style="flex: 1 1 0%; min-width: 150px;">N/A</span>
                <i id="ocular-lock-inline" class="fas fa-lock" style="margin-left:8px;color:#6c757d;display:none;" title="Ocular assignment locked until appropriate stage"></i>
                <select class="form-control staff-select" id="select-ocular-staff" style="flex: 1; min-width: 200px;">
                  <option value="">Select Staff...</option>
                  <!-- Options will be loaded via AJAX -->
                </select>
                <button class="btn-modern btn-secondary" id="change-ocular-staff" style="padding: 8px 16px;">
                  <i class="fas fa-edit" style="margin-right: 4px;"></i>Change
                </button>
              </div>
            </div>

            <div class="info-item">
              <span class="info-label">Fabrication Staff</span>
              <div class="info-value-with-action" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span id="detail-fabrication-staff" style="flex: 1 1 0%; min-width: 150px;">-</span>
                <i id="fabrication-lock-inline" class="fas fa-lock" style="margin-left:8px;color:#6c757d;display:none;" title="Fabrication assignment locked until order is In Fabrication"></i>
                <select class="form-control staff-select" id="select-fabrication-staff" style="display: none; flex: 1; min-width: 200px;">
                  <option value="">Select Staff...</option>
                  <!-- Options will be loaded via AJAX -->
                </select>
                <button class="btn-modern btn-secondary" id="change-fabrication-staff" style="padding: 8px 16px;">
                  <i class="fas fa-edit" style="margin-right: 4px;"></i>Change
                </button>
              </div>
            </div>

            <div class="info-item">
              <span class="info-label">Installation Staff</span>
              <div class="info-value-with-action" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span id="detail-installation-staff" style="flex: 1 1 0%; min-width: 150px;">-</span>
                <i id="installation-lock-inline" class="fas fa-lock" style="margin-left:8px;color:#6c757d;display:none;" title="Installation assignment locked until order is Ready for Installation"></i>
                <select class="form-control staff-select" id="select-installation-staff" style="display: none; flex: 1; min-width: 200px;">
                  <option value="">Select Staff...</option>
                  <!-- Options will be loaded via AJAX -->
                </select>
                <button class="btn-modern btn-secondary" id="change-installation-staff" style="padding: 8px 16px;">
                  <i class="fas fa-edit" style="margin-right: 4px;"></i>Change
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Linked Appointments Section -->
      <div class="info-card">
        <div class="info-card-header">
          <i class="fas fa-calendar-check info-card-icon"></i>
          <h4 class="info-card-title">Linked Appointments</h4>
        </div>
        <div class="info-card-body">
          <div id="detail-appointments">
            <!-- Appointments will be loaded here -->
          </div>
        </div>
      </div>

      <!-- Special Instructions Section -->
      <div class="info-card" id="special-instructions-section" style="display: none;">
        <div class="info-card-header">
          <i class="fas fa-sticky-note info-card-icon"></i>
          <h4 class="info-card-title">Special Instructions</h4>
        </div>
        <div class="info-card-body">
          <div class="info-grid">
            <div class="info-item full-width">
              <span class="info-value" id="detail-special-instructions" style="white-space: pre-wrap; line-height: 1.6;">-</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Breakdown Section -->
      <div class="info-card">
        <div class="info-card-header">
          <i class="fas fa-money-bill-wave info-card-icon"></i>
          <h4 class="info-card-title">Payment Breakdown</h4>
        </div>
        <div class="info-card-body">
          <div style="margin-bottom: 20px; padding: 12px; background: #f8f9fa; border-left: 4px solid #02455F; border-radius: 4px;">
            <small style="color: #495057;">
              <i class="fas fa-info-circle"></i> <strong>Payment Schedule:</strong> 50% downpayment at ocular visit, 40% after fabrication complete, 10% after installation complete.
            </small>
          </div>
          
          <!-- Payment Stage 1: Downpayment (50%) -->
          <div id="payment-stage-1" style="border: 2px solid #02455F; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #ffffff;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <h5 style="margin: 0; color: #02455F;">
                <i class="fas fa-money-bill-wave"></i> Downpayment (50%)
              </h5>
              <span id="order-downpayment-status-badge" class="badge" style="background-color: #ffc107; color: #000;">Pending</span>
            </div>
            <div class="info-grid" style="margin-top: 10px;">
              <div class="info-item">
                <span class="info-label">Amount (₱)</span>
                <input type="number" id="order-downpayment-amount" class="form-control" min="0" step="0.01" placeholder="Auto-calculated" disabled>
              </div>
              <div class="info-item">
                <span class="info-label">Payment Method</span>
                <input type="text" id="order-downpayment-method" class="form-control" value="—" disabled>
              </div>
              <div class="info-item">
                <span class="info-label">Status</span>
                <input type="text" id="order-downpayment-status-text" class="form-control" value="Pending" disabled>
              </div>
            </div>
            <div class="info-item full-width" id="order-downpayment-receipt-container" style="margin-top: 10px; display: none;">
              <span class="info-label">Payment Receipt:</span>
              <div id="order-downpayment-receipt-link" style="margin-top: 8px;">
                <a href="#" target="_blank" style="color: #02455F; text-decoration: underline;">
                  <i class="fas fa-file-pdf" style="margin-right: 5px;"></i>View receipt
                </a>
              </div>
            </div>
            <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
              <i class="fas fa-info-circle"></i> Downpayment is managed in the Ocular Visit appointment.
            </small>
          </div>
          
          <!-- Payment Stage 2: Fabrication Payment (40%) -->
          <div id="payment-stage-2" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; opacity: 0.6;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <h5 style="margin: 0; color: #6c757d;">
                <i class="fas fa-lock"></i> Fabrication Payment (40%)
              </h5>
              <span id="order-fabrication-status-badge" class="badge" style="background-color: #6c757d; color: #fff;">Locked</span>
            </div>
            <div class="info-grid" style="margin-top: 10px;">
              <div class="info-item">
                <span class="info-label">Amount (₱)</span>
                <input type="number" id="order-fabrication-amount" class="form-control" value="" disabled placeholder="Available after fabrication">
              </div>
              <div class="info-item">
                <span class="info-label">Payment Method</span>
                <select id="order-fabrication-method" class="form-control" disabled>
                  <option value="">Select method</option>
                  <option value="GCash">GCash</option>
                  <option value="Maya">Maya</option>
                  <option value="Card">Credit/Debit Card</option>
                  <option value="Cash">Cash</option>
                  <option value="Check">Check</option>
                </select>
              </div>
              <div class="info-item">
                <span class="info-label">Status</span>
                <select id="order-fabrication-status" class="form-control" disabled>
                  <option value="Pending">Pending</option>
                  <option value="Paid">Paid</option>
                </select>
              </div>
            </div>
            <div class="info-item full-width" id="order-fabrication-receipt-container" style="margin-top: 10px; display: none;">
              <span class="info-label">Payment Receipt:</span>
              <input type="file" id="order-fabrication-receipt" accept="image/*,application/pdf" class="form-control" disabled>
              <div id="order-fabrication-receipt-link" style="margin-top: 8px; display: none;">
                <a href="#" target="_blank" style="color: #02455F; text-decoration: underline;">
                  <i class="fas fa-file-pdf" style="margin-right: 5px;"></i>View receipt
                </a>
              </div>
            </div>
            <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
              <i class="fas fa-info-circle"></i> This payment stage will be available when order status is "In Fabrication" or later.
            </small>
          </div>
          
          <!-- Payment Stage 3: Installation Payment (10%) -->
          <div id="payment-stage-3" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; opacity: 0.6;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <h5 style="margin: 0; color: #6c757d;">
                <i class="fas fa-lock"></i> Installation Payment (10%)
              </h5>
              <span id="order-installation-status-badge" class="badge" style="background-color: #6c757d; color: #fff;">Locked</span>
            </div>
            <div class="info-grid" style="margin-top: 10px;">
              <div class="info-item">
                <span class="info-label">Amount (₱)</span>
                <input type="number" id="order-installation-amount" class="form-control" value="" disabled placeholder="Available after installation">
              </div>
              <div class="info-item">
                <span class="info-label">Payment Method</span>
                <select id="order-installation-method" class="form-control" disabled>
                  <option value="">Select method</option>
                  <option value="GCash">GCash</option>
                  <option value="Maya">Maya</option>
                  <option value="Card">Credit/Debit Card</option>
                  <option value="Cash">Cash</option>
                  <option value="Check">Check</option>
                </select>
              </div>
              <div class="info-item">
                <span class="info-label">Status</span>
                <select id="order-installation-status" class="form-control" disabled>
                  <option value="Pending">Pending</option>
                  <option value="Paid">Paid</option>
                </select>
              </div>
            </div>
            <div class="info-item full-width" id="order-installation-receipt-container" style="margin-top: 10px; display: none;">
              <span class="info-label">Payment Receipt:</span>
              <input type="file" id="order-installation-receipt" accept="image/*,application/pdf" class="form-control" disabled>
              <div id="order-installation-receipt-link" style="margin-top: 8px; display: none;">
                <a href="#" target="_blank" style="color: #02455F; text-decoration: underline;">
                  <i class="fas fa-file-pdf" style="margin-right: 5px;"></i>View receipt
                </a>
              </div>
            </div>
            <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
              <i class="fas fa-info-circle"></i> This payment stage will be available when order status is "Installation/Delivery" or later.
            </small>
          </div>
          
          <div style="margin-top: 15px; padding: 12px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
            <small style="color: #856404;">
              <i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> Payment stages unlock based on order status. Fabrication payment becomes editable during "In Fabrication" stage, and installation payment becomes editable during "Installation/Delivery" stage.
            </small>
          </div>
        </div>
      </div>

      <!-- Admin Actions Section -->
      <div class="info-card">
        <div class="info-card-header">
          <i class="fas fa-cog info-card-icon"></i>
          <h4 class="info-card-title">Admin Actions</h4>
        </div>
        <div class="info-card-body">
          <!-- Order Approval Actions (for orders with Status = 'Awaiting Admin') -->
          <div class="approval-actions" id="approval-actions-section" style="display: none;">
            <div class="form-group-modern">
              <label class="form-label-modern" for="admin-notes-textarea">Admin Notes <span style="color: #6c757d; font-weight: 400;">(Internal - Optional)</span></label>
              <textarea id="admin-notes-textarea" class="form-textarea-modern" rows="3" placeholder="Add internal notes (optional)..."></textarea>
            </div>
            <div class="form-group-modern">
              <label class="form-label-modern" for="disapproval-reason-textarea">Disapproval Reason <span class="required-asterisk">*</span> <span style="color: #6c757d; font-weight: 400;">(Required for Disapproval)</span></label>
              <textarea id="disapproval-reason-textarea" class="form-textarea-modern" rows="3" placeholder="Please provide a reason for disapproval..." required></textarea>
            </div>
            <div class="action-buttons-row" style="margin-top: 20px;">
              <button class="btn-modern btn-success" id="approve-order-btn">
                <i class="fas fa-check" style="margin-right: 6px;"></i>Approve Order
              </button>
              <button class="btn-modern btn-danger" id="disapprove-order-btn">
                <i class="fas fa-times" style="margin-right: 6px;"></i>Disapprove Order
              </button>
            </div>
          </div>

          <!-- General Admin Actions -->
          <div class="action-buttons">
            <div class="form-group-modern">
              <label class="form-label-modern" for="update-status-select">Update Status</label>
              <div style="display: flex; gap: 10px; align-items: flex-end;">
                <select id="update-status-select" class="form-control" style="flex: 1;">
                  <!-- Options will be populated based on current status -->
                </select>
                <button class="btn-modern btn-success" id="update-status-btn" style="padding: 12px 24px;">
                  <i class="fas fa-sync-alt" style="margin-right: 6px;"></i>Update
                </button>
              </div>
            </div>
            <div class="form-group-modern" id="admin-notes-group" style="display: none;">
              <label class="form-label-modern" for="admin-notes-textarea-general">Admin Notes</label>
              <div style="display: flex; gap: 10px; align-items: flex-end;">
                <textarea id="admin-notes-textarea-general" class="form-textarea-modern" rows="3" placeholder="Add internal notes..." style="flex: 1;"></textarea>
              </div>
            </div>
            <div class="form-group-modern" id="disapproval-reason-group" style="display: none;">
              <label class="form-label-modern" for="disapproval-reason-general">Disapproval Reason <span class="required-asterisk">*</span></label>
              <textarea id="disapproval-reason-general" class="form-textarea-modern" rows="3" placeholder="Please provide a reason for disapproval..." required></textarea>
            </div>
            <div class="action-buttons-row" style="margin-top: 20px; gap: 10px;">
              <button class="btn-modern btn-secondary" id="export-order-btn">
                <i class="fas fa-file-export" style="margin-right: 6px;"></i>Export Order
              </button>
              <button class="btn-modern btn-danger" id="cancel-order-btn" style="display: none;">
                <i class="fas fa-ban" style="margin-right: 6px;"></i>Cancel Order
              </button>
            </div>
          </div>
          <div class="barcode-section" id="detail-barcode-section" style="display: none; margin-top: 20px; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <label class="form-label-modern" style="margin-bottom: 10px;">Order Barcode</label>
            <img id="detail-barcode-img" src="" alt="Barcode" style="max-width: 100%; height: auto; border-radius: 6px;">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Action Menu Dropdown -->
<div id="actionMenu" class="action-menu hidden">
  <ul>
    <li><a href="#" class="action-view">View Details</a></li>
    <li><a href="#" class="action-update-status">Update Status</a></li>
    <li><a href="#" class="action-export">Export Order</a></li>
    <li><a href="#" class="action-cancel" style="display: none;">Cancel Order</a></li>
  </ul>
</div>

<!-- Update Status Modal -->
<div class="popup-overlay" id="updateStatusModal">
  <div class="popup modern-popup">
    <div class="popup-header-modern">
      <span class="close-btn" id="closeUpdateStatusModal">&times;</span>
      <h3 class="popup-title-modern">
        <i class="fas fa-sync-alt" style="margin-right: 10px;"></i>Update Order Status
      </h3>
      <p class="popup-subtitle">Change the current status of this order</p>
    </div>
    <div class="popup-content-modern">
      <div class="info-card">
        <div class="info-card-body">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Order ID</span>
              <span class="info-value" id="modal-status-order-id">-</span>
            </div>
            <div class="info-item">
              <span class="info-label">Current Status</span>
              <span class="info-value" id="modal-status-current-status">-</span>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group-modern">
        <label class="form-label-modern" for="modal-update-status-select">Select New Status</label>
        <select id="modal-update-status-select" class="form-control">
          <option value="">Select Status...</option>
        </select>
      </div>
      <div class="form-group-modern">
        <label class="form-label-modern" for="modal-update-status-notes">Admin Notes <span style="color: #6c757d; font-weight: 400;">(Optional)</span></label>
        <textarea id="modal-update-status-notes" class="form-textarea-modern" rows="4" placeholder="Add any notes about this status change..."></textarea>
      </div>
    </div>
    <div class="popup-actions-modern">
      <button class="btn-modern btn-secondary" id="cancel-update-status">Cancel</button>
      <button class="btn-modern btn-success" id="confirm-update-status">
        <i class="fas fa-check" style="margin-right: 6px;"></i>Update Status
      </button>
    </div>
  </div>
</div>

<!-- Assign Staff Modal -->
<div class="popup-overlay" id="assignStaffModal">
  <div class="popup modern-popup">
    <div class="popup-header-modern">
      <span class="close-btn" id="closeAssignStaffModal">&times;</span>
      <h3 class="popup-title-modern">
        <i class="fas fa-users" style="margin-right: 10px;"></i>Assign Staff to Order
      </h3>
      <p class="popup-subtitle">Assign fabrication and installation staff members</p>
    </div>
    <div class="popup-content-modern">
      <div class="info-card">
        <div class="info-card-body">
          <div class="info-item">
            <span class="info-label">Order ID</span>
            <span class="info-value" id="modal-staff-order-id">-</span>
          </div>
        </div>
      </div>
      <?php if ($is_site_assessed): ?>
      <div class="form-group-modern">
        <label class="form-label-modern" for="modal-assign-ocular-staff">Ocular Visit Staff</label>
        <select id="modal-assign-ocular-staff" class="form-control">
          <option value="">Select Ocular Staff...</option>
        </select>
        <p class="current-assignment" id="current-ocular-staff" style="margin-top: 8px; font-size: 13px; color: #6c757d;">
          <i class="fas fa-info-circle" style="margin-right: 5px;"></i>Current: <span style="font-weight: 500; color: #02455F;">-</span>
        </p>

      </div>
      <?php endif; ?>
      <div class="form-group-modern">
        <label class="form-label-modern" for="modal-assign-fabrication-staff">Fabrication Staff</label>
        <select id="modal-assign-fabrication-staff" class="form-control">
          <option value="">Select Fabrication Staff...</option>
        </select>
        <p class="current-assignment" id="current-fabrication-staff" style="margin-top: 8px; font-size: 13px; color: #6c757d;">
          <i class="fas fa-info-circle" style="margin-right: 5px;"></i>Current: <span style="font-weight: 500; color: #02455F;">-</span>
        </p>
      </div>
      <div class="form-group-modern">
        <label class="form-label-modern" for="modal-assign-installation-staff">Installation Staff <i id="modal-installation-lock" class="fas fa-lock" style="margin-left:8px;color:#6c757d;display:none;" title="Locked until order is Ready for Installation"></i></label>
        <select id="modal-assign-installation-staff" class="form-control">
          <option value="">Select Installation Staff...</option>
        </select>
        <p class="current-assignment" id="current-installation-staff" style="margin-top: 8px; font-size: 13px; color: #6c757d;">
          <i class="fas fa-info-circle" style="margin-right: 5px;"></i>Current: <span style="font-weight: 500; color: #02455F;">-</span>
        </p>
      </div>
    </div>
    <div class="popup-actions-modern">
      <button class="btn-modern btn-secondary" id="cancel-assign-staff">Cancel</button>
      <button class="btn-modern btn-success" id="confirm-assign-staff">
        <i class="fas fa-user-check" style="margin-right: 6px;"></i>Assign Staff
      </button>
    </div>
  </div>
</div>

<!-- Link to Calendar Modal -->
<div class="popup-overlay" id="linkCalendarModal">
  <div class="popup modern-popup">
    <div class="popup-header-modern">
      <span class="close-btn" id="closeLinkCalendarModal">&times;</span>
      <h3 class="popup-title-modern">
        <i class="fas fa-calendar-alt" style="margin-right: 10px;"></i>Link Order to Calendar
      </h3>
      <p class="popup-subtitle">Schedule appointments or track important dates</p>
    </div>
    <div class="popup-content-modern">
      <div class="info-card">
        <div class="info-card-body">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Order ID</span>
              <span class="info-value" id="modal-calendar-order-id">-</span>
            </div>
            <div class="info-item">
              <span class="info-label">Client Name</span>
              <span class="info-value" id="modal-calendar-client-name">-</span>
            </div>
          </div>
        </div>
      </div>
      <div class="calendar-link-options">
        <p style="color: #495057; margin-bottom: 20px; font-size: 14px; line-height: 1.6;">
          You can link this order to the calendar to schedule appointments or track important dates.
        </p>
        <div class="link-options">
          <button class="btn-modern btn-success" id="view-calendar-btn" style="flex: 1;">
            <i class="fas fa-calendar-alt" style="margin-right: 8px;"></i>View Calendar
          </button>
          <button class="btn-modern btn-primary" id="create-appointment-btn" style="flex: 1;">
            <i class="fas fa-plus" style="margin-right: 8px;"></i>Create Appointment
          </button>
        </div>
      </div>
    </div>
    <div class="popup-actions-modern">
      <button class="btn-modern btn-secondary" id="cancel-link-calendar">Close</button>
    </div>
  </div>
</div>

<!-- Export Order Modal -->
<div class="popup-overlay" id="exportOrderModal">
  <div class="popup modern-popup">
    <div class="popup-header-modern">
      <span class="close-btn" id="closeExportOrderModal">&times;</span>
      <h3 class="popup-title-modern">
        <i class="fas fa-file-export" style="margin-right: 10px;"></i>Export Order
      </h3>
      <p class="popup-subtitle">Download or print order information</p>
    </div>
    <div class="popup-content-modern">
      <div class="info-card">
        <div class="info-card-body">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Order ID</span>
              <span class="info-value" id="modal-export-order-id">-</span>
            </div>
            <div class="info-item">
              <span class="info-label">Client Name</span>
              <span class="info-value" id="modal-export-client-name">-</span>
            </div>
            <div class="info-item">
              <span class="info-label">Order Date</span>
              <span class="info-value" id="modal-export-order-date">-</span>
            </div>
          </div>
        </div>
      </div>
      <div class="export-options">
        <label class="form-label-modern" style="margin-bottom: 15px;">Choose Export Format</label>
        <div class="export-format-buttons">
          <button class="export-format-btn" data-format="pdf">
            <i class="fas fa-file-pdf"></i>
            <span>PDF</span>
          </button>
          <button class="export-format-btn" data-format="excel">
            <i class="fas fa-file-excel"></i>
            <span>Excel</span>
          </button>
          <button class="export-format-btn" data-format="print">
            <i class="fas fa-print"></i>
            <span>Print</span>
          </button>
        </div>
      </div>
    </div>
    <div class="popup-actions-modern">
      <button class="btn-modern btn-secondary" id="cancel-export-order">Close</button>
    </div>
  </div>
</div>

<!-- Cancel Order Modal -->
<div class="popup-overlay" id="cancelOrderModal">
  <div class="popup modern-popup">
    <div class="popup-header-modern" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
      <span class="close-btn" id="closeCancelOrderModal">&times;</span>
      <h3 class="popup-title-modern">
        <i class="fas fa-times-circle" style="margin-right: 10px;"></i>Cancel Order
      </h3>
      <p class="popup-subtitle">This action cannot be undone</p>
    </div>
    <div class="popup-content-modern">
      <div class="warning-message">
        <i class="fas fa-exclamation-triangle"></i>
        <p><strong>Warning:</strong> This action cannot be undone. The order will be marked as cancelled.</p>
      </div>
      <div class="info-card">
        <div class="info-card-body">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Order ID</span>
              <span class="info-value" id="modal-cancel-order-id">-</span>
            </div>
            <div class="info-item">
              <span class="info-label">Client Name</span>
              <span class="info-value" id="modal-cancel-client-name">-</span>
            </div>
            <div class="info-item">
              <span class="info-label">Current Status</span>
              <span class="info-value" id="modal-cancel-current-status">-</span>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group-modern">
        <label class="form-label-modern" for="modal-cancel-reason">Cancellation Reason <span style="color: #6c757d; font-weight: 400;">(Optional)</span></label>
        <textarea id="modal-cancel-reason" class="form-textarea-modern" rows="4" placeholder="Please provide a reason for cancellation..."></textarea>
      </div>
    </div>
    <div class="popup-actions-modern">
      <button class="btn-modern btn-secondary" id="cancel-cancel-order">Keep Order</button>
      <button class="btn-modern btn-danger" id="confirm-cancel-order">
        <i class="fas fa-ban" style="margin-right: 6px;"></i>Confirm Cancellation
      </button>
    </div>
  </div>
</div>
