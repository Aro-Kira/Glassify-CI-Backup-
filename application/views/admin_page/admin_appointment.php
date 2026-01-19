<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Determine appointment type from URL parameter
$appointment_type = isset($_GET['type']) ? $_GET['type'] : 'ocular';
$is_ocular = ($appointment_type === 'ocular');
$is_installation = ($appointment_type === 'installation');
$page_title = $is_ocular ? 'Ocular / Site Assessment Appointments' : 'Installation Appointments';
?>

<script>
  // Pass the URLs from PHP to JS
  const baseUrl = "<?php echo base_url(); ?>";
  const getAppointmentsUrl = "<?php echo base_url('AdminCon/get_appointments_ajax'); ?>";
  const getAppointmentDetailsUrl = "<?php echo base_url('AdminCon/get_appointment_details_ajax'); ?>";
  const updateAppointmentUrl = "<?php echo base_url('AdminCon/update_appointment_ajax'); ?>";
  const deleteAppointmentUrl = "<?php echo base_url('AdminCon/delete_appointment_ajax'); ?>";
  const getStaffListUrl = "<?php echo base_url('AdminCon/get_staff_list'); ?>";
  const appointmentType = "<?php echo $appointment_type; ?>";
</script>

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
      <div class="step-label">Order Placed</div>
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
      <div class="step-label">Installed</div>
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
  <div class="view-toggle">
    <button class="toggle-btn active" data-view="list">List View</button>
    <button class="toggle-btn" data-view="calendar">Calendar View</button>
  </div>

  <!-- List View -->
  <div id="list-view" class="list-view-container">
    <div class="table-container">
      <table class="appointments-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Client</th>
            <th>Order ID</th>
            <th>Date & Time</th>
            <th>Assigned Staff</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="appointmentsTableBody">
          <tr>
            <td colspan="7" style="text-align: center; padding: 20px;">Loading appointments...</td>
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
        </div>
      </div>

      <!-- Client Information Section -->
      <div class="details-section">
        <h4 class="section-title">Client Information</h4>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Client Name:</span>
            <input type="text" id="detail-client-name" class="form-control editable" readonly>
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
              <?php echo $is_ocular ? 'Ocular Visit' : 'Installed'; ?>
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
            <select id="detail-status" class="form-control editable">
              <option value="In Progress">In Progress</option>
              <option value="Complete">Complete</option>
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
      <?php endif; ?>

      <!-- Installation Notes Section (Installation Appointments Only) -->
      <?php if ($is_installation): ?>
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
      <button class="btn-secondary" id="link-to-order-btn">Link to Order</button>
      <?php if ($is_ocular): ?>
      <button class="btn-success" id="mark-ocular-complete-btn">Mark as Complete</button>
      <?php endif; ?>
      <button class="btn-danger" id="cancel-appointment-btn">Cancel Appointment</button>
    </div>
  </div>
</div>

<script>
// =============================
// TOAST NOTIFICATION SYSTEM
// =============================
function showToast(message, type = 'info', duration = 3000) {
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => {
        toast.classList.add('toast-fade-out');
        setTimeout(() => toast.remove(), 300);
    });

    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    const config = {
        success: { icon: '✓', bg: '#28a745', border: '#1e7e34' },
        error: { icon: '✕', bg: '#dc3545', border: '#c82333' },
        warning: { icon: '⚠', bg: '#ffc107', border: '#e0a800' },
        info: { icon: 'ℹ', bg: '#17a2b8', border: '#138496' }
    };
    
    const toastConfig = config[type] || config.info;
    
    toast.innerHTML = `
        <div class="toast-icon">${toastConfig.icon}</div>
        <div class="toast-message">${message}</div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    toast.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${toastConfig.bg};
        color: white;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        max-width: 500px;
        animation: toastSlideIn 0.3s ease;
        font-family: 'Montserrat', sans-serif;
        border-left: 4px solid ${toastConfig.border};
    `;
    
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            @keyframes toastSlideIn {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes toastFadeOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
            .toast-notification { transition: all 0.3s ease; }
            .toast-fade-out { animation: toastFadeOut 0.3s ease forwards; }
            .toast-icon { font-size: 20px; font-weight: bold; flex-shrink: 0; }
            .toast-message { flex: 1; font-size: 14px; line-height: 1.4; }
            .toast-close { background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; opacity: 0.8; transition: opacity 0.2s; flex-shrink: 0; }
            .toast-close:hover { opacity: 1; }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('toast-fade-out');
        setTimeout(() => toast.remove(), 300);
    }, duration);
    
    return toast;
}

function showConfirmModal(message, onConfirm, onCancel = null) {
    const existingModal = document.getElementById('confirm-modal-overlay');
    if (existingModal) existingModal.remove();
    
    const overlay = document.createElement('div');
    overlay.id = 'confirm-modal-overlay';
    overlay.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); z-index: 10001;
        display: flex; align-items: center; justify-content: center;
        animation: fadeIn 0.2s ease;
    `;
    
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: white; border-radius: 12px; padding: 30px;
        max-width: 450px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        animation: slideUp 0.3s ease;
    `;
    
    modal.innerHTML = `
        <h3 style="margin: 0 0 15px 0; font-size: 20px; color: #333; font-family: 'Montserrat', sans-serif;">Confirm Action</h3>
        <p style="margin: 0 0 25px 0; color: #666; font-size: 15px; line-height: 1.5;">${message}</p>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button id="confirm-cancel-btn" style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer; font-size: 14px; color: #666; transition: all 0.2s;">Cancel</button>
            <button id="confirm-ok-btn" style="padding: 10px 20px; border: none; background: #dc3545; color: white; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">Confirm</button>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    if (!document.getElementById('modal-styles')) {
        const style = document.createElement('style');
        style.id = 'modal-styles';
        style.textContent = `
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
            #confirm-cancel-btn:hover { background: #f5f5f5; }
            #confirm-ok-btn:hover { background: #c82333; }
        `;
        document.head.appendChild(style);
    }
    
    const cancelBtn = overlay.querySelector('#confirm-cancel-btn');
    const okBtn = overlay.querySelector('#confirm-ok-btn');
    
    cancelBtn.addEventListener('click', () => {
        overlay.style.animation = 'fadeIn 0.2s ease reverse';
        setTimeout(() => overlay.remove(), 200);
        if (onCancel) onCancel();
    });
    
    okBtn.addEventListener('click', () => {
        overlay.style.animation = 'fadeIn 0.2s ease reverse';
        setTimeout(() => overlay.remove(), 200);
        if (onConfirm) onConfirm();
    });
    
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.style.animation = 'fadeIn 0.2s ease reverse';
            setTimeout(() => overlay.remove(), 200);
            if (onCancel) onCancel();
        }
    });
    
    const escapeHandler = (e) => {
        if (e.key === 'Escape') {
            overlay.style.animation = 'fadeIn 0.2s ease reverse';
            setTimeout(() => overlay.remove(), 200);
            if (onCancel) onCancel();
            document.removeEventListener('keydown', escapeHandler);
        }
    };
    document.addEventListener('keydown', escapeHandler);
}

</script>
