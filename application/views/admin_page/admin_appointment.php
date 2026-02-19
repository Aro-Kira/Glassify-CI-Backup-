<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Determine appointment type from URL parameter
$appointment_type = isset($_GET['type']) ? $_GET['type'] : 'ocular';
$is_ocular = ($appointment_type === 'ocular');
$is_fabrication = ($appointment_type === 'fabrication');
$is_installation = ($appointment_type === 'installation');
$page_title = $is_ocular ? 'Ocular / Site Assessment Appointments' : ($is_fabrication ? 'Fabrication Appointments' : 'Installation Appointments');
?>

<script>
  // Pass the URLs from PHP to JS
  const baseUrl = "<?php echo base_url(); ?>";
  const getAppointmentsUrl = "<?php echo base_url('AdminCon/get_appointments_ajax'); ?>";
  const getAppointmentDetailsUrl = "<?php echo base_url('AdminCon/get_appointment_details_ajax'); ?>";
  const updateAppointmentUrl = "<?php echo base_url('AdminCon/update_appointment_ajax'); ?>";
  const deleteAppointmentUrl = "<?php echo base_url('AdminCon/delete_appointment_ajax'); ?>";
  const getStaffListUrl = "<?php echo base_url('AdminCon/get_staff_list'); ?>";
  const getDateChangeRequestsUrl = "<?php echo base_url('AdminCon/get_installation_date_change_requests'); ?>";
  const createQuotationUrl = "<?php echo base_url('AdminCon/create_quotation_from_appointment'); ?>";
  const sendQuotationUrl = "<?php echo base_url('AdminCon/send_quotation_email'); ?>";
  const proceedFabricationUrl = "<?php echo base_url('AdminCon/proceed_to_fabrication'); ?>";
  const getProductCustomizationUrl = "<?php echo base_url('AdminCon/get_product_customization_data'); ?>";
  const getCustomizationFieldsUrl = "<?php echo base_url('customizationFields/get'); ?>";
  const appointmentType = "<?php echo $appointment_type; ?>";
</script>

<?php if ($is_ocular): ?>
<!-- Konva.js for 2D preview rendering -->
<script src="<?php echo base_url('assets/js/konva.min.js'); ?>"></script>
<!-- 2D Rendering Engine -->
<script src="<?php echo base_url('assets/js/2d-functions/comprehensive_2d_renderer.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/2d-functions/comprehensive_renderer_integration.js'); ?>"></script>
<!-- Windows visual configs -->
<script src="<?php echo base_url('assets/js/windows_visual_configs.js'); ?>"></script>
<?php endif; ?>

<script src="<?php echo base_url('assets/js/admin-js/appointment-management.js'); ?>"></script>

<!-- Appointments Section -->
<section class="appointment-management-section">
  <div class="section-header">
    <h2><?php echo $page_title; ?> <span class="found-text">Loading...</span></h2>
  </div>

  <!-- Progress Steps Indicator -->
  <div class="progress-steps-indicator">
    <div class="step-item <?php echo $is_ocular ? 'active' : ''; ?>">
      <div class="step-icon step-blue">
        <i class="fas fa-shopping-cart"></i>
      </div>
      <div class="step-label">Booking Submitted</div>
      <div class="step-connector"></div>
    </div>
    <div class="step-item <?php echo $is_ocular ? 'active' : ''; ?>">
      <div class="step-icon step-orange">
        <i class="fas fa-eye"></i>
      </div>
      <div class="step-label">Ocular Visit</div>
      <div class="step-connector"></div>
    </div>
    <div class="step-item">
      <div class="step-icon step-purple">
        <i class="fas fa-cog"></i>
      </div>
      <div class="step-label">In Fabrication</div>
      <div class="step-connector"></div>
    </div>
    <div class="step-item <?php echo $is_installation ? 'active' : ''; ?>">
      <div class="step-icon step-yellow">
        <i class="fas fa-tools"></i>
      </div>
      <div class="step-label">Installation/Delivery</div>
      <div class="step-connector"></div>
    </div>
    <div class="step-item">
      <div class="step-icon step-green">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="step-label">Completed</div>
    </div>
  </div>

  <!-- Filters Section -->
  <div class="filters-container">
    <div class="filter-group">
      <label for="status-filter">Status:</label>
      <select id="status-filter" class="filter-select">
        <option value="all">All</option>
        <option value="In Progress">In Progress</option>
        <option value="Complete">Complete</option>
        <option value="Cancelled">Cancelled</option>
      </select>
    </div>

    <div class="filter-group">
      <label for="date-filter">Date:</label>
      <input type="date" id="date-filter" class="filter-input">
    </div>

    <div class="filter-group">
      <label for="client-search">Client:</label>
      <input type="text" id="client-search" class="filter-input" placeholder="Search by client name">
    </div>

    <div class="filter-group">
      <label for="staff-filter">Assigned Staff:</label>
      <select id="staff-filter" class="filter-select">
        <option value="all">All Staff</option>
        <!-- Options will be loaded via AJAX -->
      </select>
    </div>

    <?php if ($is_ocular): ?>
    <div class="filter-group">
      <label for="ocular-completed-filter">Ocular Completed:</label>
      <select id="ocular-completed-filter" class="filter-select">
        <option value="all">All</option>
        <option value="yes">Yes</option>
        <option value="no">No</option>
      </select>
    </div>
    <?php endif; ?>

    <button class="filter-btn" id="apply-filters">Apply Filters</button>
    <button class="filter-btn filter-btn-secondary" id="clear-filters">Clear</button>
  </div>

  <!-- View Toggle -->
    <div class="view-toggle" style="display:flex; align-items:center; gap:8px;">
      <button class="toggle-btn active" data-view="list">List View</button>
      <button class="toggle-btn" data-view="calendar">Calendar View</button>
      <?php if ($is_installation): ?>
      <span class="view-toggle-divider" style="color:#cfcfcf; margin:0 6px;">|</span>
      <button class="toggle-btn" data-view="requests">Request</button>
      <?php endif; ?>
    </div>

  <!-- List View -->
  <div id="list-view" class="list-view-container">
    <div class="table-container">
      <table class="appointments-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Client</th>
            <th>Role</th>
            <th>Order ID</th>
            <th>Order Date</th>
            <th>Scheduled Date & Time</th>
            <th>Product Name</th>
            <th>Assigned Staff</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="appointmentsTableBody">
          <tr>
            <td colspan="10" style="text-align: center; padding: 20px;">Loading appointments...</td>
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
  </div>
  <?php if ($is_installation): ?>
  <!-- Requests View (installation only) -->
  <div id="requests-view" class="requests-container" style="display:none; margin-top:16px;">
    <h3>Installation Date Change Requests</h3>
    <div class="requests-table-wrapper" style="overflow-x:auto;">
      <table class="requests-table" style="width:100%; border-collapse: collapse;">
        <thead>
          <tr>
            <th>Requested Date</th>
            <th>Client</th>
            <th>Order</th>
            <th>Date &amp; Time</th>
            <th>Product Name</th>
            <th>Assigned Staff</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="requests-table-body">
          <tr><td colspan="8" style="text-align:center; padding:12px;">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Request Details Modal -->
  <div class="popup-overlay" id="requestDetailsModal">
    <div class="popup popup-medium">
      <span class="close-btn" id="closeRequestDetails">&times;</span>
      <h3 class="popup-title">Request Details</h3>
      <div id="request-details-content" style="min-height:120px;">
        <!-- Populated by JS -->
      </div>
      <div class="popup-actions" style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end;">
        <button class="btn-success" id="request-approve-btn">Approve</button>
        <button class="btn-danger" id="request-disapprove-btn">Disapprove</button>
        <button class="btn-secondary" onclick="document.getElementById('requestDetailsModal').classList.remove('active')">Close</button>
      </div>
    </div>
  </div>

  <!-- Calendar View -->
  <div id="calendar-view" class="calendar-view-container" style="display: none;">
    <div class="calendar-header">
      <h3 id="calendar-month-year"></h3>
      <div class="calendar-controls">
        <button class="calendar-btn" id="calendar-today">Today</button>
        <button class="calendar-btn" id="calendar-prev">❮</button>
        <button class="calendar-btn" id="calendar-next">❯</button>
      </div>
    </div>
    <table class="calendar-table">
      <thead>
        <tr>
          <th>Sun</th>
          <th>Mon</th>
          <th>Tue</th>
          <th>Wed</th>
          <th>Thu</th>
          <th>Fri</th>
          <th>Sat</th>
        </tr>
      </thead>
      <tbody id="calendar-body">
        <!-- Calendar will be rendered by JavaScript -->
      </tbody>
    </table>
  </div>
</section>

<!-- Appointment Details Modal -->
<div class="popup-overlay" id="appointmentDetailsModal">
  <div class="popup popup-large">
    <span class="close-btn" id="closeAppointmentDetails">&times;</span>
    <h3 class="popup-title">Appointment Details</h3>

    <div class="appointment-details-content">
      <!-- Order Information Section -->
      <div class="details-section">
        <h4 class="section-title">Order Information</h4>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Order ID:</span>
            <span class="info-value">
              <a href="#" id="detail-order-link" class="order-link">-</a>
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Order Date:</span>
            <span class="info-value" id="detail-order-date">-</span>
          </div>
        </div>
      </div>

      <?php if ($is_ocular): ?>
      <!-- Product & 2D Preview Section -->
      <div class="details-section" id="product-preview-section">
        <h4 class="section-title">Product Preview</h4>
        <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
          <!-- 2D Design Preview -->
          <div id="design-preview-container" style="display: none; flex-shrink: 0;">
            <div style="border: 2px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #f9fafb; cursor: pointer;" onclick="showDesignModalAdmin(this.querySelector('img')?.src)">
              <img id="detail-design-preview" src="" alt="2D Preview" 
                   style="max-width: 220px; max-height: 220px; display: block; object-fit: contain;">
            </div>
            <div style="text-align: center; margin-top: 6px;">
              <small style="color: #6b7280; font-style: italic;">Click to enlarge</small>
            </div>
          </div>
          <!-- Product Info + Specs Summary -->
          <div style="flex: 1; min-width: 200px;">
            <div style="margin-bottom: 12px;">
              <span style="font-weight: 600; color: #0f2b46; font-size: 1.05rem;" id="detail-product-name">-</span>
              <br>
              <span style="color: #6b7280; font-size: 0.9rem;" id="detail-product-category"></span>
            </div>
            <!-- Customization Breakdown (from JSON) -->
            <div id="customization-breakdown-container" style="display: none;">
              <table id="customization-breakdown-table" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <tbody></tbody>
              </table>
            </div>
            <div id="no-customization-msg" style="display: none; color: #9ca3af; font-style: italic; font-size: 0.9rem;">
              No customization data available
            </div>
          </div>
        </div>
      </div>

      <!-- Order Specifications Section (Dynamic with 2D Preview) -->
      <div class="details-section" id="order-specs-section">
        <h4 class="section-title">Order Specifications <small style="color: #666; font-weight: 400;">(Editable during ocular visit "In Progress" status)</small></h4>
        <input type="hidden" id="detail-order-item-id">
        <input type="hidden" id="detail-customization-id">
        
        <div style="display: flex; gap: 24px; flex-wrap: wrap;">
          <!-- 2D Preview Column -->
          <div style="flex: 0 0 280px; max-width: 320px;">
            <div id="admin-konva-wrapper" style="width: 280px; height: 280px; border: 2px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #f9fafb;">
              <div id="admin-konva-container" style="width: 100%; height: 100%;"></div>
            </div>
            <div style="text-align: center; margin-top: 6px;">
              <small style="color: #6b7280; font-style: italic;">Live 2D Preview</small>
            </div>
            <!-- Fallback static image when no Konva rendering -->
            <div id="admin-static-preview" style="display: none;">
              <img id="admin-static-preview-img" src="" alt="Design Preview" 
                   style="max-width: 280px; max-height: 280px; display: block; object-fit: contain; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;"
                   onclick="showDesignModalAdmin(this.src)">
            </div>
          </div>
          
          <!-- Specifications Column -->
          <div style="flex: 1; min-width: 300px;">
            <!-- Dimensions (always shown) -->
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Width</span>
                <input type="number" id="detail-spec-width" class="form-control editable" min="0" step="0.01" placeholder="Enter width">
              </div>
              <div class="info-item">
                <span class="info-label">Height</span>
                <input type="number" id="detail-spec-height" class="form-control editable" min="0" step="0.01" placeholder="Enter height">
              </div>
              <div class="info-item">
                <span class="info-label">Unit</span>
                <select id="detail-spec-unit" class="form-control editable">
                  <option value="in">inches (in)</option>
                  <option value="cm">centimeters (cm)</option>
                  <option value="mm">millimeters (mm)</option>
                  <option value="ft">feet (ft)</option>
                </select>
              </div>
            </div>
            
            <!-- Dynamic Product-Specific Fields (loaded via AJAX) -->
            <div id="admin-dynamic-specs-container" style="margin-top: 15px;">
              <div style="text-align: center; padding: 20px; color: #9ca3af;">
                <i class="fas fa-spinner fa-spin"></i> Loading product specifications...
              </div>
            </div>
            
            <!-- Fallback static fields (shown if no dynamic fields available) -->
            <div id="admin-static-specs" style="display: none;">
              <!-- Glass Specifications -->
              <div class="info-grid" style="margin-top: 15px;">
                <div class="info-item" id="spec-shape-row">
                  <span class="info-label">Glass Shape</span>
                  <select id="detail-spec-shape" class="form-control editable">
                    <option value="">Select shape</option>
                    <option value="Rectangle">Rectangle</option>
                    <option value="Square">Square</option>
                    <option value="Round">Round / Circle</option>
                    <option value="Oval">Oval</option>
                    <option value="Arch">Arch</option>
                    <option value="Custom">Custom</option>
                  </select>
                </div>
                <div class="info-item" id="spec-type-row">
                  <span class="info-label">Glass Type</span>
                  <select id="detail-spec-type" class="form-control editable">
                    <option value="">Select type</option>
                    <option value="Clear">Clear</option>
                    <option value="Tinted">Tinted</option>
                    <option value="Frosted">Frosted</option>
                    <option value="Tempered">Tempered</option>
                    <option value="Laminated">Laminated</option>
                    <option value="Mirror">Mirror</option>
                  </select>
                </div>
                <div class="info-item" id="spec-thickness-row">
                  <span class="info-label">Glass Thickness</span>
                  <select id="detail-spec-thickness" class="form-control editable">
                    <option value="">Select thickness</option>
                    <option value="3mm">3mm</option>
                    <option value="4mm">4mm</option>
                    <option value="5mm">5mm</option>
                    <option value="6mm">6mm</option>
                    <option value="8mm">8mm</option>
                    <option value="10mm">10mm</option>
                    <option value="12mm">12mm</option>
                  </select>
                </div>
              </div>
              
              <!-- Additional Options -->
              <div class="info-grid" style="margin-top: 15px;">
                <div class="info-item" id="spec-edge-row">
                  <span class="info-label">Edge Work</span>
                  <select id="detail-spec-edge" class="form-control editable">
                    <option value="">Select edge work</option>
                    <option value="Flat polished edge">Flat polished edge</option>
                    <option value="Beveled">Beveled</option>
                    <option value="Pencil polished edge">Pencil polished edge</option>
                    <option value="Seamed edge">Seamed edge</option>
                  </select>
                </div>
                <div class="info-item" id="spec-frame-row">
                  <span class="info-label">Frame Type</span>
                  <select id="detail-spec-frame" class="form-control editable">
                    <option value="">Select frame</option>
                    <option value="Frameless">Frameless</option>
                    <option value="Hanalok">Hanalok</option>
                    <option value="Aluminum">Aluminum</option>
                    <option value="Wooden">Wooden</option>
                    <option value="Steel">Steel</option>
                  </select>
                </div>
                <div class="info-item" id="spec-engraving-row">
                  <span class="info-label">Engraving</span>
                  <input type="text" id="detail-spec-engraving" class="form-control editable" placeholder="Enter engraving text (optional)">
                </div>
              </div>
            </div>
            
            <!-- Quantity (always shown) -->
            <div class="info-grid" style="margin-top: 15px;">
              <div class="info-item">
                <span class="info-label">Quantity</span>
                <input type="number" id="detail-spec-quantity" class="form-control editable" min="1" step="1" value="1">
              </div>
            </div>
          </div>
        </div>
        
        <div style="margin-top: 10px; padding: 10px; background: #f0f9ff; border-left: 4px solid #0284c7; border-radius: 4px;">
          <small style="color: #0369a1;">
            <i class="fas fa-info-circle"></i> <strong>Note:</strong> These specifications can be updated based on the ocular visit assessment report. Changes will be reflected in the order.
          </small>
        </div>
      </div>
      <?php endif; ?>

      <!-- Client Information Section -->
      <div class="details-section">
        <h4 class="section-title">Client Information</h4>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Client Name:</span>
            <input type="text" id="detail-client-name" class="form-control editable" readonly>
          </div>
          <div class="info-item">
            <span class="info-label">Contact Number:</span>
            <span class="info-value" id="detail-client-phone">-</span>
          </div>
          <div class="info-item full-width">
            <span class="info-label">Address:</span>
            <span class="info-value" id="detail-client-address">-</span>
          </div>
        </div>
      </div>

      <!-- Appointment Information Section -->
      <div class="details-section">
        <h4 class="section-title">Appointment Information</h4>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Service Type:</span>
            <span class="info-value" id="detail-service-type">
              <?php echo $is_ocular ? 'Ocular Visit' : 'Installation/Delivery'; ?>
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Date:</span>
            <input type="date" id="detail-appointment-date" class="form-control editable">
          </div>
          <div class="info-item">
            <span class="info-label">Time:</span>
            <input type="time" id="detail-appointment-time" class="form-control editable">
          </div>
          <div class="info-item">
            <span class="info-label">Assigned Staff:</span>
            <select id="detail-assigned-staff" class="form-control editable staff-select">
              <option value="">Select Staff...</option>
              <!-- Options will be loaded via AJAX -->
            </select>
          </div>
          <div class="info-item">
            <span class="info-label">Status:</span>
            <select id="detail-status" class="form-control editable" onchange="handleInstallationStatusChange()">
              <option value="In Progress">In Progress</option>
              <option value="Installed">Installed (Work Done - Payment Pending)</option>
              <option value="Complete">Complete (Work + Payment Done)</option>
              <option value="Payment Overdue">Payment Overdue</option>
              <option value="Returned">Returned (Product Removed)</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>
          <div class="info-item full-width">
            <span class="info-label">Notes:</span>
            <textarea id="detail-notes" class="form-control editable" rows="3"></textarea>
          </div>
        </div>
      </div>

      <!-- Ocular Notes Section (Ocular Appointments Only) -->
      <?php if ($is_ocular): ?>
      <!-- Payment Breakdown Section -->
      <div class="details-section">
        <h4 class="section-title">Payment Breakdown</h4>
        <div style="margin-bottom: 20px; padding: 12px; background: #f8f9fa; border-left: 4px solid #02455F; border-radius: 4px;">
          <small style="color: #495057;">
            <i class="fas fa-info-circle"></i> <strong>Payment Schedule:</strong> 50% downpayment at ocular visit, 40% after fabrication complete, 10% after installation complete.
          </small>
        </div>
        
        <!-- Total Amount (admin-entered) -->
        <div style="border: 2px solid #e9ecef; border-radius: 8px; padding: 12px; margin-bottom: 15px; background: #ffffff;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <h5 style="margin:0; color:#02455F;"><i class="fas fa-calculator"></i> Product Total Amount</h5>
            <small style="color:#6c757d; font-size:13px;">Enter total amount to auto-calculate payments</small>
          </div>
          <div class="info-grid" style="margin-top:6px;">
            <div class="info-item" style="flex:1;">
              <span class="info-label">Total Amount (₱)</span>
              <input type="number" id="detail-total-amount" class="form-control editable" min="0" step="0.01" placeholder="Enter total product amount">
            </div>
          </div>
        </div>

        <!-- Payment Stage 1: Downpayment (50%) -->
        <div style="border: 2px solid #02455F; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #ffffff;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0; color: #02455F;">
              <i class="fas fa-money-bill-wave"></i> Downpayment (50%)
            </h5>
            <span id="downpayment-status-badge" class="badge" style="background-color: #ffc107; color: #000;">Pending</span>
          </div>
          <div class="info-grid" style="margin-top: 10px;">
            <div class="info-item">
              <span class="info-label">Amount (₱)</span>
              <input type="number" id="detail-downpayment-amount" class="form-control" min="0" step="0.01" placeholder="Auto-calculated" disabled>
            </div>
            <div class="info-item">
              <span class="info-label">Payment Method</span>
              <select id="detail-downpayment-method" class="form-control editable">
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
              <select id="detail-downpayment-status" class="form-control editable">
                <option value="Pending">Pending</option>
                <option value="Paid">Paid</option>
              </select>
            </div>
          </div>
            <div class="info-item full-width" style="margin-top: 10px;">
              <span class="info-label">Payment Receipt:</span>
              <input type="file" id="detail-payment-receipt" accept="image/*,application/pdf" class="form-control editable">
              <div id="detail-payment-receipt-link" style="margin-top: 8px; display: none;">
                <a href="#" target="_blank" style="color: #02455F; text-decoration: underline;">
                  <i class="fas fa-file-pdf" style="margin-right: 5px;"></i>View uploaded receipt
                </a>
              </div>
            </div>
        </div>
        
        <!-- Payment Stage 2: Fabrication Payment (40%) - LOCKED -->
        <div style="border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; opacity: 0.6;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0; color: #6c757d;">
              <i class="fas fa-lock"></i> Fabrication Payment (40%)
            </h5>
            <span class="badge" style="background-color: #6c757d; color: #fff;">Locked</span>
          </div>
          <div class="info-grid" style="margin-top: 10px;">
            <div class="info-item">
              <span class="info-label">Amount (₱)</span>
              <input type="number" id="detail-fabrication-amount" class="form-control" value="" disabled placeholder="Available after fabrication">
            </div>
            <div class="info-item">
              <span class="info-label">Payment Method</span>
              <input type="text" class="form-control" value="—" disabled>
            </div>
            <div class="info-item">
              <span class="info-label">Status</span>
              <input type="text" class="form-control" value="Locked" disabled>
            </div>
          </div>
          <small style="color: #6c757d; font-style: italic;">
            <i class="fas fa-info-circle"></i> This payment stage will be available after fabrication is complete.
          </small>
        </div>
        
        <!-- Payment Stage 3: Installation Payment (10%) - LOCKED -->
        <div style="border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; opacity: 0.6;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0; color: #6c757d;">
              <i class="fas fa-lock"></i> Installation Payment (10%)
            </h5>
            <span class="badge" style="background-color: #6c757d; color: #fff;">Locked</span>
          </div>
          <div class="info-grid" style="margin-top: 10px;">
            <div class="info-item">
              <span class="info-label">Amount (₱)</span>
              <input type="number" id="detail-installation-amount" class="form-control" value="" disabled placeholder="Available after installation">
            </div>
            <div class="info-item">
              <span class="info-label">Payment Method</span>
              <input type="text" class="form-control" value="—" disabled>
            </div>
            <div class="info-item">
              <span class="info-label">Status</span>
              <input type="text" class="form-control" value="Locked" disabled>
            </div>
          </div>
          <small style="color: #6c757d; font-style: italic;">
            <i class="fas fa-info-circle"></i> This payment stage will be available after installation is complete.
          </small>
        </div>
      </div>
      
      <div class="details-section">
        <h4 class="section-title">Ocular / Site Assessment Notes</h4>
        <div class="info-grid">
          <div class="info-item full-width">
            <span class="info-label">Ocular Notes:</span>
            <textarea id="detail-ocular-notes" class="form-control editable" rows="8" placeholder="Enter site assessment details, measurements, special requirements, access considerations, material recommendations..."></textarea>
          </div>
          <div class="info-item full-width">
            <span class="info-label">Site Photos:</span>
            <div class="photo-upload-area" id="photo-upload-area">
              <input type="file" id="site-photos-input" multiple accept="image/*" style="display: none;">
              <button class="btn-secondary" id="upload-photos-btn">Upload Photos</button>
              <div class="photo-gallery" id="site-photos-gallery">
                <!-- Photos will be displayed here -->
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Quotation Section removed per request -->
      <?php endif; ?>

      <!-- Fabrication Appointment - Order Specifications (Read-Only) -->
      <?php if ($is_fabrication): ?>
      <div class="details-section">
        <h4 class="section-title">Order Specifications <small style="color: #666; font-weight: 400;">(From customer order)</small></h4>
        
        <!-- Dimensions -->
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Dimensions</span>
            <span class="info-value" id="fab-dimensions">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Quantity</span>
            <span class="info-value" id="fab-quantity">-</span>
          </div>
        </div>
        
        <!-- Glass Specifications -->
        <div class="info-grid" style="margin-top: 15px;">
          <div class="info-item" id="fab-shape-row">
            <span class="info-label">Glass Shape</span>
            <span class="info-value" id="fab-glass-shape">-</span>
          </div>
          <div class="info-item" id="fab-type-row">
            <span class="info-label">Glass Type</span>
            <span class="info-value" id="fab-glass-type">-</span>
          </div>
          <div class="info-item" id="fab-thickness-row">
            <span class="info-label">Glass Thickness</span>
            <span class="info-value" id="fab-glass-thickness">-</span>
          </div>
        </div>
        
        <!-- Additional Options -->
        <div class="info-grid" style="margin-top: 15px;">
          <div class="info-item" id="fab-edge-row">
            <span class="info-label">Edge Work</span>
            <span class="info-value" id="fab-edge-work">-</span>
          </div>
          <div class="info-item" id="fab-frame-row">
            <span class="info-label">Frame Type</span>
            <span class="info-value" id="fab-frame-type">-</span>
          </div>
          <div class="info-item" id="fab-engraving-row">
            <span class="info-label">Engraving</span>
            <span class="info-value" id="fab-engraving">-</span>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Fabrication Appointment Payment Breakdown -->
      <?php if ($is_fabrication): ?>
      <!-- Payment Breakdown Section -->
      <div class="details-section">
        <h4 class="section-title">Payment Breakdown</h4>
        <div style="margin-bottom: 20px; padding: 12px; background: #f8f9fa; border-left: 4px solid #02455F; border-radius: 4px;">
          <small style="color: #495057;">
            <i class="fas fa-info-circle"></i> <strong>Payment Schedule:</strong> 50% downpayment at ocular visit, 40% after fabrication complete, 10% after installation complete.
          </small>
        </div>
        
        <!-- Payment Stage 1: Downpayment (50%) - READ ONLY -->
        <div style="border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; opacity: 0.7;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0; color: #6c757d;">
              <i class="fas fa-check-circle"></i> Downpayment (50%)
            </h5>
            <span id="fab-downpayment-status-badge" class="badge" style="background-color: #6c757d; color: #fff;">Completed</span>
          </div>
          <div class="info-grid" style="margin-top: 10px;">
            <div class="info-item">
              <span class="info-label">Amount (₱)</span>
              <input type="number" id="fab-downpayment-amount" class="form-control" disabled>
            </div>
            <div class="info-item">
              <span class="info-label">Payment Method</span>
              <input type="text" id="fab-downpayment-method" class="form-control" disabled>
            </div>
            <div class="info-item">
              <span class="info-label">Status</span>
              <input type="text" id="fab-downpayment-status-text" class="form-control" value="Paid" disabled>
            </div>
          </div>
          <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
            <i class="fas fa-info-circle"></i> Downpayment was completed during the ocular visit.
          </small>
        </div>
        
        <!-- Payment Stage 2: Fabrication Payment (40%) - EDITABLE -->
        <div style="border: 2px solid #02455F; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #ffffff;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0; color: #02455F;">
              <i class="fas fa-money-bill-wave"></i> Fabrication Payment (40%)
            </h5>
            <span id="fab-fabrication-status-badge" class="badge" style="background-color: #ffc107; color: #000;">Pending</span>
          </div>
          <div class="info-grid" style="margin-top: 10px;">
            <div class="info-item">
              <span class="info-label">Amount (₱)</span>
              <input type="number" id="fab-fabrication-amount" class="form-control" min="0" step="0.01" placeholder="Auto-calculated" disabled>
            </div>
            <div class="info-item">
              <span class="info-label">Payment Method</span>
              <select id="fab-fabrication-method" class="form-control editable">
                <option value="">Select method</option>
                <option value="GCash">GCash</option>
                <option value="Maya">Maya</option>
                <option value="Card">Credit/Debit Card</option>
                <option value="Cash">Cash</option>
                <option value="Check">Check</option>
              </select>
            </div>
            <div class="info-item full-width" style="margin-top: 6px;">
              <span class="info-label">Payment Receipt:</span>
              <input type="file" id="fab-fabrication-receipt" accept="image/*,application/pdf" class="form-control editable">
              <div id="fab-fabrication-receipt-link" style="margin-top: 8px; display: none;">
                <a href="#" target="_blank" style="color: #02455F; text-decoration: underline;">
                  <i class="fas fa-file-pdf" style="margin-right: 5px;"></i>View uploaded receipt
                </a>
              </div>
            </div>
          </div>
          <div class="info-item" style="margin-top: 10px;">
            <span class="info-label">Status</span>
            <select id="fab-fabrication-status" class="form-control editable">
              <option value="Pending">Pending</option>
              <option value="Paid">Paid</option>
            </select>
          </div>
        </div>
        
        <!-- Payment Stage 3: Installation Payment (10%) - LOCKED -->
        <div style="border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; opacity: 0.6;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0; color: #6c757d;">
              <i class="fas fa-lock"></i> Installation Payment (10%)
            </h5>
            <span class="badge" style="background-color: #6c757d; color: #fff;">Locked</span>
          </div>
          <div class="info-grid" style="margin-top: 10px;">
            <div class="info-item">
              <span class="info-label">Amount (₱)</span>
              <input type="number" class="form-control" value="" disabled placeholder="Available after installation">
            </div>
            <div class="info-item">
              <span class="info-label">Payment Method</span>
              <input type="text" class="form-control" value="—" disabled>
            </div>
            <div class="info-item">
              <span class="info-label">Status</span>
              <input type="text" class="form-control" value="Locked" disabled>
            </div>
          </div>
          <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
            <i class="fas fa-info-circle"></i> This payment stage will be available after installation is complete.
          </small>
        </div>
      </div>
      
      <div class="details-section">
        <h4 class="section-title">Fabrication Notes</h4>
        <div class="info-grid">
          <div class="info-item full-width">
            <span class="info-label">Fabrication Progress Notes:</span>
            <textarea id="detail-fabrication-notes" class="form-control editable" rows="8" placeholder="Enter fabrication progress, quality checks, material updates, completion status..."></textarea>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Installation Appointment - Order Specifications (Read-Only Dynamic) -->
      <?php if ($is_installation): ?>
      <div class="details-section" id="inst-order-specs-section">
        <h4 class="section-title">Order Specifications <small style="color: #666; font-weight: 400;">(From customer order - Read only)</small></h4>
        <input type="hidden" id="inst-order-item-id">
        <input type="hidden" id="inst-customization-id">
        
        <!-- Specifications (Read-Only) -->
        <div>
            <!-- Dimensions (read-only) -->
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Width</span>
                <input type="number" id="inst-spec-width" class="form-control" disabled style="background: #f3f4f6;">
              </div>
              <div class="info-item">
                <span class="info-label">Height</span>
                <input type="number" id="inst-spec-height" class="form-control" disabled style="background: #f3f4f6;">
              </div>
              <div class="info-item">
                <span class="info-label">Unit</span>
                <input type="text" id="inst-spec-unit-display" class="form-control" disabled style="background: #f3f4f6;">
              </div>
            </div>
            
            <!-- Dynamic Product-Specific Fields (loaded via AJAX, read-only) -->
            <div id="inst-dynamic-specs-container" style="margin-top: 15px;">
              <div style="text-align: center; padding: 20px; color: #9ca3af;">
                <i class="fas fa-spinner fa-spin"></i> Loading product specifications...
              </div>
            </div>
            
            <!-- Fallback static fields (shown if no dynamic fields available, read-only) -->
            <div id="inst-static-specs" style="display: none;">
              <!-- Glass Specifications -->
              <div class="info-grid" style="margin-top: 15px;">
                <div class="info-item" id="inst-spec-shape-row">
                  <span class="info-label">Glass Shape</span>
                  <input type="text" id="inst-spec-shape" class="form-control" disabled style="background: #f3f4f6;">
                </div>
                <div class="info-item" id="inst-spec-type-row">
                  <span class="info-label">Glass Type</span>
                  <input type="text" id="inst-spec-type" class="form-control" disabled style="background: #f3f4f6;">
                </div>
                <div class="info-item" id="inst-spec-thickness-row">
                  <span class="info-label">Glass Thickness</span>
                  <input type="text" id="inst-spec-thickness" class="form-control" disabled style="background: #f3f4f6;">
                </div>
              </div>
              
              <!-- Additional Options -->
              <div class="info-grid" style="margin-top: 15px;">
                <div class="info-item" id="inst-spec-edge-row">
                  <span class="info-label">Edge Work</span>
                  <input type="text" id="inst-spec-edge" class="form-control" disabled style="background: #f3f4f6;">
                </div>
                <div class="info-item" id="inst-spec-frame-row">
                  <span class="info-label">Frame Type</span>
                  <input type="text" id="inst-spec-frame" class="form-control" disabled style="background: #f3f4f6;">
                </div>
                <div class="info-item" id="inst-spec-engraving-row">
                  <span class="info-label">Engraving</span>
                  <input type="text" id="inst-spec-engraving" class="form-control" disabled style="background: #f3f4f6;">
                </div>
              </div>
            </div>
            
            <!-- Quantity (read-only) -->
            <div class="info-grid" style="margin-top: 15px;">
              <div class="info-item">
                <span class="info-label">Quantity</span>
                <input type="number" id="inst-spec-quantity" class="form-control" disabled style="background: #f3f4f6;">
              </div>
            </div>
        
        <div style="margin-top: 10px; padding: 10px; background: #f0f9ff; border-left: 4px solid #0284c7; border-radius: 4px;">
          <small style="color: #0369a1;">
            <i class="fas fa-info-circle"></i> <strong>Note:</strong> These specifications were confirmed during the ocular visit and cannot be modified during installation.
          </small>
        </div>
      </div>
      
      <!-- Installation Payment Breakdown Section -->
      <div class="details-section">
        <h4 class="section-title">Payment Breakdown</h4>
        
        <!-- Payment Stage 1: Downpayment (50%) - READ ONLY -->
        <div style="border: 2px solid #28a745; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8fff8;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0; color: #28a745;">
              <i class="fas fa-check-circle"></i> Downpayment (50%)
            </h5>
            <span id="inst-downpayment-badge" class="badge" style="background-color: #28a745; color: #fff;">Completed</span>
          </div>
          <div class="info-grid" style="margin-top: 10px;">
            <div class="info-item">
              <span class="info-label">Amount (₱)</span>
              <input type="number" id="inst-downpayment-amount" class="form-control" disabled style="background: #e9ecef;">
            </div>
            <div class="info-item">
              <span class="info-label">Payment Method</span>
              <input type="text" id="inst-downpayment-method" class="form-control" disabled style="background: #e9ecef;">
            </div>
            <div class="info-item">
              <span class="info-label">Status</span>
              <input type="text" class="form-control" value="Completed" disabled style="background: #e9ecef;">
            </div>
          </div>
          <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
            <i class="fas fa-info-circle"></i> Downpayment was completed during the ocular visit.
          </small>
        </div>
        
        <!-- Payment Stage 2: Fabrication Payment (40%) - READ ONLY -->
        <div style="border: 2px solid #28a745; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8fff8;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0; color: #28a745;">
              <i class="fas fa-check-circle"></i> Fabrication Payment (40%)
            </h5>
            <span id="inst-fabrication-badge" class="badge" style="background-color: #28a745; color: #fff;">Completed</span>
          </div>
          <div class="info-grid" style="margin-top: 10px;">
            <div class="info-item">
              <span class="info-label">Amount (₱)</span>
              <input type="number" id="inst-fabrication-amount" class="form-control" disabled style="background: #e9ecef;">
            </div>
            <div class="info-item">
              <span class="info-label">Payment Method</span>
              <input type="text" id="inst-fabrication-method" class="form-control" disabled style="background: #e9ecef;">
            </div>
            <div class="info-item">
              <span class="info-label">Status</span>
              <input type="text" class="form-control" value="Completed" disabled style="background: #e9ecef;">
            </div>
          </div>
          <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
            <i class="fas fa-info-circle"></i> Fabrication payment was completed before installation.
          </small>
        </div>
        
        <!-- Payment Stage 3: Installation Payment (10%) - EDITABLE -->
        <div id="inst-payment-section" style="border: 2px solid #02455F; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #ffffff;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0; color: #02455F;">
              <i class="fas fa-money-bill-wave"></i> Installation Payment (10%)
            </h5>
            <span id="inst-payment-badge" class="badge" style="background-color: #ffc107; color: #000;">Pending</span>
          </div>
          
          <!-- Payment Due Warning -->
          <div id="inst-payment-warning" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 12px; margin-bottom: 15px; display: none;">
            <p style="margin: 0; color: #856404; font-size: 14px;">
              <i class="fas fa-exclamation-triangle"></i> <strong>Payment Due:</strong> 
              <span id="inst-payment-due-text">Customer has 3-5 days to pay the remaining 10%.</span>
            </p>
            <p style="margin: 8px 0 0 0; color: #856404; font-size: 13px;">
              <i class="fas fa-calendar-alt"></i> Due Date: <strong id="inst-payment-due-date">—</strong>
            </p>
          </div>
          
          <div class="info-grid" style="margin-top: 10px;">
            <div class="info-item">
              <span class="info-label">Amount (₱)</span>
              <input type="number" id="inst-installation-amount" class="form-control" disabled style="background: #e9ecef;">
            </div>
            <div class="info-item">
              <span class="info-label">Payment Method</span>
              <select id="inst-installation-method" class="form-control editable">
                <option value="">Select method</option>
                <option value="GCash">GCash</option>
                <option value="Maya">Maya</option>
                <option value="Card">Credit/Debit Card</option>
                <option value="Cash">Cash</option>
                <option value="Check">Check</option>
              </select>
            </div>
            <div class="info-item full-width" style="margin-top: 6px;">
              <span class="info-label">Payment Receipt:</span>
              <input type="file" id="inst-installation-receipt" accept="image/*,application/pdf" class="form-control editable">
              <div id="inst-installation-receipt-link" style="margin-top: 8px; display: none;">
                <a href="#" target="_blank" style="color: #02455F; text-decoration: underline;">
                  <i class="fas fa-file-pdf" style="margin-right: 5px;"></i>View uploaded receipt
                </a>
              </div>
            </div>
          </div>
          <div class="info-item" style="margin-top: 10px;">
            <span class="info-label">Status</span>
            <select id="inst-installation-status" class="form-control editable" onchange="updateInstPaymentBadge()">
              <option value="Pending">Pending</option>
              <option value="Paid">Paid</option>
            </select>
          </div>
          <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
            <i class="fas fa-info-circle"></i> Customer has 3-5 days after installation completion to pay the remaining 10%. If not paid, the installed product may be removed.
          </small>
        </div>
      </div>
      <?php endif; ?>

      <!-- Payment Deadline Tracker (Installation Only) -->
      <?php if ($is_installation): ?>
      <div id="inst-payment-deadline-banner" class="details-section" style="display: none; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 32px;">⏰</div>
            <div>
              <span style="font-size: 1.1rem; font-weight: 600; color: #856404;">Payment Due: </span>
              <span style="font-size: 1.2rem; font-weight: 700; color: #d97706;" id="inst-countdown">⏱️ Calculating...</span>
            </div>
          </div>
          <button type="button" class="btn btn-success" onclick="markInstallationPaymentReceived()">
            <i class="fas fa-check-circle"></i> Mark as Paid
          </button>
        </div>
      </div>
      
      <!-- Payment Overdue Warning -->
      <div id="inst-payment-overdue-banner" class="details-section" style="display: none; background: #f8d7da; border: 2px solid #dc3545; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 32px;">🚨</div>
            <div>
              <span style="font-size: 1.1rem; font-weight: 600; color: #721c24;">PAYMENT OVERDUE: </span>
              <span style="font-size: 1.2rem; font-weight: 700; color: #dc3545;" id="inst-overdue-days">⚠️ Calculating...</span>
            </div>
          </div>
          <div style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-success btn-sm" onclick="markInstallationPaymentReceived()">
              <i class="fas fa-check-circle"></i> Payment Received
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="markAsReturned()">
              <i class="fas fa-undo"></i> Mark as Returned
            </button>
          </div>
        </div>
      </div>
      
      <!-- Installation Notes Section (Installation Appointments Only) -->
      <div class="details-section">
        <h4 class="section-title">Installation Notes</h4>
        <div class="info-grid">
          <div class="info-item full-width">
            <span class="info-label">Installation Notes:</span>
            <textarea id="detail-installation-notes" class="form-control editable" rows="5" placeholder="Enter installation-specific notes..."></textarea>
          </div>
          <div class="info-item full-width">
            <span class="info-label">Installation Checklist:</span>
            <div class="checklist-container">
              <label class="checkbox-label">
                <input type="checkbox" id="checklist-materials" class="checklist-item">
                <span>Materials delivered</span>
              </label>
              <label class="checkbox-label">
                <input type="checkbox" id="checklist-site-prepared" class="checklist-item">
                <span>Site prepared</span>
              </label>
              <label class="checkbox-label">
                <input type="checkbox" id="checklist-installation-completed" class="checklist-item">
                <span>Installation completed</span>
              </label>
              <label class="checkbox-label">
                <input type="checkbox" id="checklist-quality-check" class="checklist-item">
                <span>Quality check passed</span>
              </label>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="popup-actions">
      <button class="btn-primary" id="save-appointment-btn">Save Changes</button>
      <button class="btn-secondary" id="reschedule-btn">Reschedule</button>
      <?php if (! $is_ocular): ?>
      <button class="btn-secondary" id="link-to-order-btn">Link to Order</button>
      <?php endif; ?>
      <?php if ($is_ocular): ?>
      <button class="btn-success" id="mark-ocular-complete-btn">Mark as Complete</button>
      <?php endif; ?>
      <button class="btn-danger" id="cancel-appointment-btn">Cancel Appointment</button>
    </div>
  </div>
</div>

<!-- Design Preview Modal (fullscreen) -->
<div id="designPreviewModalAdmin" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:10001; cursor:pointer; justify-content:center; align-items:center;" onclick="this.style.display='none'">
  <img id="designPreviewModalAdminImg" src="" alt="Design Preview" style="max-width:90%; max-height:90%; border-radius:8px; box-shadow:0 4px 24px rgba(0,0,0,0.5);">
  <span style="position:absolute; top:20px; right:30px; color:#fff; font-size:36px; cursor:pointer; font-weight:bold;">&times;</span>
</div>

<style>
  /* Product preview styles */
  #customization-breakdown-table td {
    padding: 5px 10px 5px 0;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: top;
  }
  #customization-breakdown-table td:first-child {
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
    width: 140px;
  }
  #customization-breakdown-table td:last-child {
    color: #1f2937;
  }
  /* Dynamic customization fields in admin appointment */
  #admin-dynamic-specs-container .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
  }
  #admin-dynamic-specs-container .spec-field-group {
    margin-bottom: 12px;
  }
  #admin-dynamic-specs-container .spec-field-group label {
    display: block;
    font-weight: 600;
    color: #0f2b46;
    margin-bottom: 4px;
    font-size: 0.9rem;
  }
  #admin-dynamic-specs-container .spec-field-group select,
  #admin-dynamic-specs-container .spec-field-group input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.9rem;
    background: #fff;
    transition: border-color 0.2s;
  }
  #admin-dynamic-specs-container .spec-field-group select:focus,
  #admin-dynamic-specs-container .spec-field-group input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37,99,235,0.1);
  }
  #admin-konva-wrapper {
    position: relative;
  }
</style>

<script>
function showDesignModalAdmin(src) {
    if (!src) return;
    const modal = document.getElementById('designPreviewModalAdmin');
    const img = document.getElementById('designPreviewModalAdminImg');
    if (modal && img) {
        img.src = src;
        modal.style.display = 'flex';
    }
}
</script>

