// Appointment Management Module2D Preview Column
// Handles Ocular and Installation Appointments

// Global function to update installation payment badge
function updateInstPaymentBadge() {
    const status = document.getElementById('inst-installation-status')?.value || 'Pending';
    const badge = document.getElementById('inst-payment-badge');
    if (badge) {
        badge.textContent = status;
        if (status === 'Paid') {
            badge.style.backgroundColor = '#28a745';
            badge.style.color = '#fff';
        } else {
            badge.style.backgroundColor = '#ffc107';
            badge.style.color = '#000';
        }
    }
}

// ======================
// INSTALLATION PAYMENT WORKFLOW FUNCTIONS
// ======================

// Global countdown interval reference
let paymentCountdownInterval = null;

/**
 * Handle installation status change - show/hide payment deadline banners
 */
function handleInstallationStatusChange() {
    const statusSelect = document.getElementById('detail-status');
    if (!statusSelect) return;
    
    const status = statusSelect.value;
    const deadlineBanner = document.getElementById('inst-payment-deadline-banner');
    const overdueBanner = document.getElementById('inst-payment-overdue-banner');
    
    // Hide both banners by default
    if (deadlineBanner) deadlineBanner.style.display = 'none';
    if (overdueBanner) overdueBanner.style.display = 'none';
    
    // Stop any existing countdown
    if (paymentCountdownInterval) {
        clearInterval(paymentCountdownInterval);
        paymentCountdownInterval = null;
    }
    
    // Show appropriate banner based on status
    if (status === 'Installed') {
        if (deadlineBanner) deadlineBanner.style.display = 'block';
        updatePaymentDeadlineDisplay();
    } else if (status === 'Payment Overdue') {
        if (overdueBanner) overdueBanner.style.display = 'block';
        updatePaymentOverdueDisplay();
    }
}

/**
 * Update payment deadline countdown display
 */
function updatePaymentDeadlineDisplay() {
    const apt = window.currentAppointment;
    if (!apt) return;
    
    const countdownEl = document.getElementById('inst-countdown');
    
    if (!apt.installation_completed_date) {
        if (countdownEl) countdownEl.textContent = '⏱️ Pending installation completion';
        return;
    }
    
    const completedDate = new Date(apt.installation_completed_date);
    const dueDate = apt.payment_due_date ? new Date(apt.payment_due_date) : new Date(completedDate.getTime() + (5 * 24 * 60 * 60 * 1000));
    
    // Start countdown timer
    function updateCountdown() {
        const now = new Date();
        const timeLeft = dueDate - now;
        
        if (timeLeft <= 0) {
            if (countdownEl) countdownEl.innerHTML = '<span style="color: #dc3545;">⏰ PAYMENT DUE NOW!</span>';
            clearInterval(paymentCountdownInterval);
            return;
        }
        
        const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        
        let countdownText = '';
        if (days > 0) {
            countdownText = `⏰ ${days} day${days > 1 ? 's' : ''}, ${hours} hour${hours > 1 ? 's' : ''} remaining`;
        } else if (hours > 0) {
            countdownText = `⏰ ${hours} hour${hours > 1 ? 's' : ''} remaining`;
        } else {
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            countdownText = `⏰ ${minutes} minute${minutes > 1 ? 's' : ''} remaining`;
        }
        
        // Change color if less than 24 hours
        const color = timeLeft < (24 * 60 * 60 * 1000) ? '#dc3545' : '#d97706';
        if (countdownEl) countdownEl.innerHTML = `<span style="color: ${color};">${countdownText}</span>`;
    }
    
    updateCountdown();
    paymentCountdownInterval = setInterval(updateCountdown, 60000); // Update every minute
}

/**
 * Update payment overdue display
 */
function updatePaymentOverdueDisplay() {
    const apt = window.currentAppointment;
    if (!apt) return;
    
    const overdueDaysEl = document.getElementById('inst-overdue-days');
    
    if (!apt.installation_completed_date || !apt.payment_due_date) {
        if (overdueDaysEl) overdueDaysEl.textContent = '⚠️ Date information missing';
        return;
    }
    
    const dueDate = new Date(apt.payment_due_date);
    const now = new Date();
    
    const daysOverdue = Math.floor((now - dueDate) / (1000 * 60 * 60 * 24));
    if (overdueDaysEl) {
        overdueDaysEl.innerHTML = `⚠️ <strong>${daysOverdue} day${daysOverdue > 1 ? 's' : ''} overdue</strong>`;
    }
}

/**
 * Mark 10% installation payment as received
 */
async function markInstallationPaymentReceived() {
    const statusSelect = document.getElementById('detail-status');
    const instStatusSelect = document.getElementById('inst-installation-status');
    
    const confirmed = await showConfirmationAsync('Confirm that the 10% installation payment has been received?');
    if (!confirmed) return;
    
    // Update appointment status to Complete
    if (statusSelect) statusSelect.value = 'Complete';
    
    // Update payment status to Paid
    if (instStatusSelect) {
        instStatusSelect.value = 'Paid';
        updateInstPaymentBadge();
    }
    
    // Trigger status change to hide banners
    handleInstallationStatusChange();
    
    showToast('Status updated to Complete. Please click "Save Changes" to save.', 'info');
}

/**
 * Mark installation as returned (product removed)
 */
async function markAsReturned() {
    const confirmed = await showConfirmationAsync(
        'WARNING: This will mark the product as RETURNED (removed due to non-payment).\\n\\n' +
        'This action indicates that Glassworth has exercised the right to remove the installation.\\n\\n' +
        'Continue?',
        'Confirm Return',
        'Yes, Return',
        'Cancel'
    );
    
    if (!confirmed) return;
    
    const statusSelect = document.getElementById('detail-status');
    const instStatusSelect = document.getElementById('inst-installation-status');
        
    // Update status to Returned
    if (statusSelect) statusSelect.value = 'Returned';
    
    // Keep payment status as Pending (not paid)
    if (instStatusSelect) {
        instStatusSelect.value = 'Pending';
        updateInstPaymentBadge();
    }
    
    // Trigger status change to hide banners
    handleInstallationStatusChange();
    
    showToast('Status updated to Returned. Please add details in Installation Notes and click "Save Changes".', 'info');
}

document.addEventListener('DOMContentLoaded', function() {
    // ======================
    // TOAST HELPER
    // ======================
    function ensureToastContainer() {
        if (!document.getElementById('global-toast-container')) {
            const container = document.createElement('div');
            container.id = 'global-toast-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '99999';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '8px';
            document.body.appendChild(container);
        }
        return document.getElementById('global-toast-container');
    }

    function showToast(message, type = 'info', duration = 4000) {
        try {
            const container = ensureToastContainer();
            const toast = document.createElement('div');
            toast.className = 'app-toast app-toast-' + type;
            toast.style.minWidth = '200px';
            toast.style.maxWidth = '360px';
            toast.style.padding = '10px 14px';
            toast.style.borderRadius = '6px';
            toast.style.boxShadow = '0 2px 8px rgba(0,0,0,0.12)';
            toast.style.color = '#fff';
            toast.style.fontSize = '14px';
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 200ms ease, transform 200ms ease';
            toast.style.transform = 'translateY(-6px)';

            if (type === 'success') toast.style.background = '#28a745';
            else if (type === 'error') toast.style.background = '#dc3545';
            else if (type === 'warning') toast.style.background = '#ffc107';
            else toast.style.background = '#17a2b8';

            toast.textContent = message;
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-6px)';
                setTimeout(() => container.removeChild(toast), 220);
            }, duration);
        } catch (e) {
            console.error(message);
        }
    }

    // ======================
    // VARIABLES
    // ======================
    const tbody = document.querySelector('#appointmentsTableBody');
    const foundText = document.querySelector('.found-text');
    const appointmentDetailsModal = document.getElementById('appointmentDetailsModal');
    const closeAppointmentDetails = document.getElementById('closeAppointmentDetails');
    
    // Filter elements
    const statusFilter = document.getElementById('status-filter');
    const dateFilter = document.getElementById('date-filter');
    const clientSearch = document.getElementById('client-search');
    const staffFilter = document.getElementById('staff-filter');
    const ocularCompletedFilter = document.getElementById('ocular-completed-filter');
    const applyFiltersBtn = document.getElementById('apply-filters');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    // View toggle
    const listViewBtn = document.querySelector('[data-view="list"]');
    const calendarViewBtn = document.querySelector('[data-view="calendar"]');
    const listView = document.getElementById('list-view');
    const calendarView = document.getElementById('calendar-view');
    
    // Pagination
    let currentPage = 1;
    let totalPages = 1;
    const itemsPerPage = 10;
    
    // Current appointment for details modal
    let currentAppointment = null;
    let staffList = [];
    let currentCalendarMonth = new Date();
    
    // ======================
    // INITIALIZATION
    // ======================
    loadStaffList();
    loadAppointments();
    // If viewing installation appointments, also load date-change requests into the list view
    if (appointmentType === 'installation') {
        loadInstallationRequests();
    }
    initializeCalendar();
    setupQuotationHandlers();
    
    // Event listeners
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            currentPage = 1;
            loadAppointments();
            if (calendarView && calendarView.style.display !== 'none') {
                renderCalendarView();
            }
        });
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            clearFilters();
            currentPage = 1;
            loadAppointments();
            if (calendarView && calendarView.style.display !== 'none') {
                renderCalendarView();
            }
        });
    }
    
    if (closeAppointmentDetails) {
        closeAppointmentDetails.addEventListener('click', () => {
            appointmentDetailsModal.classList.remove('active');
        });
    }
    
    // Close modal on overlay click
    if (appointmentDetailsModal) {
        appointmentDetailsModal.addEventListener('click', (e) => {
            if (e.target === appointmentDetailsModal) {
                appointmentDetailsModal.classList.remove('active');
            }
        });
    }
    
    // View toggle
    if (listViewBtn) {
        listViewBtn.addEventListener('click', () => {
            switchView('list');
        });
    }
    
    if (calendarViewBtn) {
        calendarViewBtn.addEventListener('click', () => {
            switchView('calendar');
        });
    }
    // Requests view: present for installation pages; enable toggle
    const requestsViewBtn = document.querySelector('[data-view="requests"]');
    const requestsView = document.getElementById('requests-view');
    if (requestsViewBtn) {
        requestsViewBtn.addEventListener('click', () => {
            switchView('requests');
        });
    }
    
    // Calendar controls
    const calendarToday = document.getElementById('calendar-today');
    const calendarPrev = document.getElementById('calendar-prev');
    const calendarNext = document.getElementById('calendar-next');
    
    if (calendarToday) {
        calendarToday.addEventListener('click', () => {
            currentCalendarMonth = new Date();
            renderCalendarView();
        });
    }
    
    if (calendarPrev) {
        calendarPrev.addEventListener('click', () => {
            currentCalendarMonth.setMonth(currentCalendarMonth.getMonth() - 1);
            renderCalendarView();
        });
    }
    
    if (calendarNext) {
        calendarNext.addEventListener('click', () => {
            currentCalendarMonth.setMonth(currentCalendarMonth.getMonth() + 1);
            renderCalendarView();
        });
    }
    
    // Appointment modal action buttons
    const saveAppointmentBtn = document.getElementById('save-appointment-btn');
    const rescheduleBtn = document.getElementById('reschedule-btn');
    const linkToOrderBtn = document.getElementById('link-to-order-btn');
    const markOcularCompleteBtn = document.getElementById('mark-ocular-complete-btn');
    const cancelAppointmentBtn = document.getElementById('cancel-appointment-btn');
    const uploadPhotosBtn = document.getElementById('upload-photos-btn');
    const sitePhotosInput = document.getElementById('site-photos-input');
    
    if (saveAppointmentBtn) {
        saveAppointmentBtn.addEventListener('click', () => saveAppointment());
    }
    
    if (rescheduleBtn) {
        rescheduleBtn.addEventListener('click', () => showRescheduleDialog());
    }
    
    if (linkToOrderBtn) {
        linkToOrderBtn.addEventListener('click', () => {
            if (currentAppointment && currentAppointment.order_id) {
                window.location.href = baseUrl + 'admin-orders?order_id=' + currentAppointment.order_id;
            }
        });
    }
    
    if (markOcularCompleteBtn) {
        markOcularCompleteBtn.addEventListener('click', () => markOcularComplete());
    }
    
    if (cancelAppointmentBtn) {
        cancelAppointmentBtn.addEventListener('click', () => cancelAppointment());
    }
    
    if (uploadPhotosBtn && sitePhotosInput) {
        uploadPhotosBtn.addEventListener('click', () => {
            sitePhotosInput.click();
        });
        
        sitePhotosInput.addEventListener('change', (e) => {
            handlePhotoUpload(e.target.files);
        });
    }
    
    // ======================
    // LOAD APPOINTMENTS
    // ======================
    async function loadAppointments() {
        if (!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">Loading appointments...</td></tr>';
        
        try {
            const params = new URLSearchParams({
                appointment_type: appointmentType || 'ocular',
                status: statusFilter ? statusFilter.value : 'all',
                page: currentPage,
                limit: itemsPerPage
            });
            
            // Add optional filters
            if (dateFilter && dateFilter.value) {
                params.append('date', dateFilter.value);
            }
            if (clientSearch && clientSearch.value.trim()) {
                params.append('client_search', clientSearch.value.trim());
            }
            if (staffFilter && staffFilter.value !== 'all') {
                params.append('staff', staffFilter.value);
            }
            if (ocularCompletedFilter && ocularCompletedFilter.value !== 'all') {
                params.append('ocular_completed', ocularCompletedFilter.value);
            }
            
            const response = await fetch(getAppointmentsUrl + '?' + params.toString());
            const data = await response.json();
            
            if (data.success) {
                renderAppointmentsTable(data.appointments || []);
                updatePagination(data.total || 0, data.page || 1, data.total_pages || 1);
                updateFoundText(data.total || 0);
            } else {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: red;">Error: ' + (data.message || 'Failed to load appointments') + '</td></tr>';
            }
        } catch (error) {
            console.error('Error loading appointments:', error);
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: red;">Error loading appointments</td></tr>';
        }
    }
    
    // ======================
    // RENDER APPOINTMENTS TABLE
    // ======================
    function renderAppointmentsTable(appointments) {
        if (!tbody) return;
        
        if (appointments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 20px;">No appointments found</td></tr>';
            return;
        }
        
        tbody.innerHTML = appointments.map((apt, index) => {
            const rowNum = (currentPage - 1) * itemsPerPage + index + 1;
            const scheduledDate = apt.appointment_date ? new Date(apt.appointment_date).toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' }) : '-';
            const scheduledTime = apt.appointment_time ? new Date('2000-01-01T' + apt.appointment_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : '-';
            const orderDate = apt.order_date || '-';
            const statusBadge = getStatusBadge(apt.status);
            const productName = apt.product || 'N/A';
            
            return `
                <tr>
                    <td>${rowNum}</td>
                    <td>${apt.client_name || apt.client || '-'}</td>
                    <td>${apt.role || '-'}</td>
                    <td><a href="${baseUrl}admin-orders?order_id=${apt.order_id}" class="order-link">${apt.order_number || apt.order_id || '-'}</a></td>
                    <td>${orderDate}</td>
                    <td>${scheduledDate} ${scheduledTime}</td>
                    <td>${productName}</td>
                    <td>${apt.assigned_staff || 'Unassigned'}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn-edit" onclick="openAppointmentDetails(${apt.id})">Edit Progress</button>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    // ======================
    // STATUS BADGE
    // ======================
    function getStatusBadge(status) {
        const badges = {
            'In Progress': '<span class="badge badge-warning">In Progress</span>',
            'Installed': '<span class="badge badge-info">Installed</span>',
            'Complete': '<span class="badge badge-success">Complete</span>',
            'Payment Overdue': '<span class="badge badge-danger">Payment Overdue</span>',
            'Cancelled': '<span class="badge badge-secondary">Cancelled</span>',
            'Returned': '<span class="badge badge-dark">Returned</span>'
        };
        return badges[status] || '<span class="badge badge-light">' + status + '</span>';
    }
    
    // ======================
    // OPEN APPOINTMENT DETAILS
    // ======================
    window.openAppointmentDetails = async function(appointmentId) {
        try {
            const response = await fetch(getAppointmentDetailsUrl + '?appointment_id=' + appointmentId);
            const data = await response.json();
            
            if (data.success && data.appointment) {
                currentAppointment = data.appointment;
                populateAppointmentModal(data.appointment);
                appointmentDetailsModal.classList.add('active');
            } else {
                showToast('Error loading appointment details: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error loading appointment details:', error);
            showToast('Error loading appointment details', 'error');
        }
    };
    
    // ======================
    // POPULATE PRODUCT PREVIEW
    // ======================
    function populateProductPreview(apt) {
        // Product name & category
        const productName = document.getElementById('detail-product-name');
        if (productName) {
            productName.textContent = apt.product_name || apt.product || '-';
        }
        const productCategory = document.getElementById('detail-product-category');
        if (productCategory) {
            productCategory.textContent = apt.product_category || '';
        }
        
        // 2D Design Preview image
        const designContainer = document.getElementById('design-preview-container');
        const designImg = document.getElementById('detail-design-preview');
        if (designContainer && designImg) {
            let designSrc = apt.design_ref || '';
            // Handle JSON array format for design_ref
            if (designSrc && typeof designSrc === 'string' && designSrc.trim().startsWith('[')) {
                try {
                    const parsed = JSON.parse(designSrc);
                    if (Array.isArray(parsed) && parsed.length > 0) designSrc = parsed[0];
                } catch(e) { /* ignore */ }
            }
            // Ensure full URL
            if (designSrc && !designSrc.startsWith('http')) {
                designSrc = baseUrl + designSrc;
            }
            if (designSrc) {
                designImg.src = designSrc;
                designImg.onerror = function() { designContainer.style.display = 'none'; };
                designContainer.style.display = 'block';
            } else if (apt.product_image) {
                // Fallback to product image if no design ref
                designImg.src = apt.product_image;
                designImg.onerror = function() { designContainer.style.display = 'none'; };
                designContainer.style.display = 'block';
            } else {
                designContainer.style.display = 'none';
            }
        }
        
        // Customization breakdown table from JSON
        const breakdownContainer = document.getElementById('customization-breakdown-container');
        const breakdownTable = document.getElementById('customization-breakdown-table');
        const noCustomMsg = document.getElementById('no-customization-msg');
        
        if (breakdownContainer && breakdownTable) {
            const tbody = breakdownTable.querySelector('tbody');
            tbody.innerHTML = '';
            
            let hasBreakdown = false;
            
            // Try to parse customization JSON
            if (apt.customization_json) {
                try {
                    const custData = typeof apt.customization_json === 'string' 
                        ? JSON.parse(apt.customization_json) 
                        : apt.customization_json;
                    
                    if (custData && typeof custData === 'object') {
                        // Add dimension first if available
                        if (apt.dimensions) {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `<td>Dimension</td><td>${escapeHtmlStr(apt.dimensions)}</td>`;
                            tbody.appendChild(tr);
                            hasBreakdown = true;
                        }
                        // Add fields from JSON (skip Dimension-like keys)
                        for (const [key, value] of Object.entries(custData)) {
                            if (value && !['Dimension', 'Dimensions', 'dimension', 'dimensions'].includes(key)) {
                                const label = key.replace(/([A-Z])/g, ' $1').replace(/_/g, ' ').trim();
                                const displayLabel = label.charAt(0).toUpperCase() + label.slice(1);
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<td>${escapeHtmlStr(displayLabel)}</td><td>${escapeHtmlStr(String(value))}</td>`;
                                tbody.appendChild(tr);
                                hasBreakdown = true;
                            }
                        }
                    }
                } catch (e) {
                    console.warn('Error parsing customization JSON:', e);
                }
            }
            
            // If no JSON breakdown, build from individual fields
            if (!hasBreakdown) {
                const specFields = [
                    { label: 'Dimension', value: apt.dimensions },
                    { label: 'Glass Shape', value: apt.glass_shape },
                    { label: 'Glass Type', value: apt.glass_type },
                    { label: 'Glass Thickness', value: apt.glass_thickness },
                    { label: 'Edge Work', value: apt.edge_work },
                    { label: 'Frame Type', value: apt.frame_type },
                    { label: 'Engraving', value: apt.engraving },
                    { label: 'Quantity', value: apt.quantity }
                ];
                
                specFields.forEach(f => {
                    if (f.value && String(f.value).trim()) {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${escapeHtmlStr(f.label)}</td><td>${escapeHtmlStr(String(f.value))}</td>`;
                        tbody.appendChild(tr);
                        hasBreakdown = true;
                    }
                });
            }
            
            if (hasBreakdown) {
                breakdownContainer.style.display = 'block';
                if (noCustomMsg) noCustomMsg.style.display = 'none';
            } else {
                breakdownContainer.style.display = 'none';
                if (noCustomMsg) noCustomMsg.style.display = 'block';
            }
        }
    }
    
    function escapeHtmlStr(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    
    // ======================
    // ADMIN 2D PREVIEW & DYNAMIC SPECS
    // ======================
    let adminKonvaStage = null;
    let adminKonvaLayer = null;
    let adminProductData = null;  // Stores current product's customization data
    let adminCustomValues = {};   // Current selected customization values

    // Installation Dynamic Specs (Read-Only)
    let instProductData = null;   // Stores current product's customization data (read-only)
    let instCustomValues = {};    // Current selected customization values (read-only)

    // Populate window.glassStyles & window.frameStyles from windowsVisualConfigs
    // so the comprehensive 2D renderer has styles even outside the 2D modeling page.
    (function bootstrapVisualStyles() {
        const wvc = window.windowsVisualConfigs;
        if (!wvc) return;
        if (!window.glassStyles || Object.keys(window.glassStyles).length === 0) {
            window.glassStyles = {};
            const gt = wvc.glassType || {};
            const gc = wvc.glassColor || {};
            for (const [k, v] of Object.entries(gt)) { window.glassStyles[k.toLowerCase()] = v; }
            for (const [k, v] of Object.entries(gc)) { window.glassStyles[k.toLowerCase()] = v; }
        }
        if (!window.frameStyles || Object.keys(window.frameStyles).length === 0) {
            window.frameStyles = {};
            const fc = wvc.frameColor || {};
            for (const [k, v] of Object.entries(fc)) { window.frameStyles[k.toLowerCase()] = v; }
        }
    })();
    
    /**
     * Load product customization data and render dynamic fields + 2D preview
     */
    async function loadProductCustomizationForAppointment(apt) {
        const dynamicContainer = document.getElementById('admin-dynamic-specs-container');
        const staticSpecs = document.getElementById('admin-static-specs');
        const konvaWrapper = document.getElementById('admin-konva-wrapper');
        const konvaContainer = document.getElementById('admin-konva-container');
        const staticPreview = document.getElementById('admin-static-preview');
        
        if (!dynamicContainer) return;
        
        // Show loading state
        dynamicContainer.innerHTML = '<div style="text-align: center; padding: 20px; color: #9ca3af;"><i class="fas fa-spinner fa-spin"></i> Loading product specifications...</div>';
        
        try {
            const response = await fetch(getProductCustomizationUrl + '?order_id=' + apt.order_id);
            const data = await response.json();
            
            if (!data.success || !data.product) {
                // Fallback to static fields
                showStaticSpecsFallback(dynamicContainer, staticSpecs);
                showStaticPreviewFallback(apt, konvaWrapper, staticPreview);
                return;
            }
            
            adminProductData = data;
            adminCustomValues = {};
            
            // Pre-populate customization values from customer selections
            if (data.customerSelections && typeof data.customerSelections === 'object') {
                Object.assign(adminCustomValues, data.customerSelections);
            }
            
            // Load saved customization data from order_item_customization
            if (apt.order_item_customization) {
                try {
                    const savedCustomization = JSON.parse(apt.order_item_customization);
                    if (savedCustomization && typeof savedCustomization === 'object') {
                        Object.assign(adminCustomValues, savedCustomization);
                        console.log('Loaded saved order customization data:', savedCustomization);
                    }
                } catch (e) {
                    console.warn('Failed to parse order_item_customization:', e);
                }
            }
            
            // Also set dimension values from the appointment
            if (apt.dimensions) {
                const match = apt.dimensions.toString().match(/([\d.]+)\s*(in|cm|mm)?\s*x\s*([\d.]+)\s*(in|cm|mm)?/i);
                if (match) {
                    adminCustomValues._width = parseFloat(match[1]);
                    adminCustomValues._height = parseFloat(match[3]);
                    adminCustomValues._unit = (match[2] || match[4] || 'in').toLowerCase();
                }
            }
            
            // Render dynamic specification fields
            renderAdminDynamicFields(data, dynamicContainer, staticSpecs);
            
            // Initialize 2D Konva preview
            initAdminKonvaPreview(data, apt, konvaWrapper, konvaContainer, staticPreview);
            
        } catch (error) {
            console.error('Error loading product customization data:', error);
            showStaticSpecsFallback(dynamicContainer, staticSpecs);
            showStaticPreviewFallback(apt, konvaWrapper, staticPreview);
        }
    }
    
    function showStaticSpecsFallback(dynamicContainer, staticSpecs) {
        dynamicContainer.innerHTML = '';
        dynamicContainer.style.display = 'none';
        if (staticSpecs) staticSpecs.style.display = 'block';
    }
    
    function showStaticPreviewFallback(apt, konvaWrapper, staticPreview) {
        if (konvaWrapper) konvaWrapper.style.display = 'none';
        if (staticPreview && apt.design_ref) {
            const img = document.getElementById('admin-static-preview-img');
            if (img) {
                let designSrc = apt.design_ref;
                // Handle JSON array format for design_ref
                if (designSrc && typeof designSrc === 'string' && designSrc.trim().startsWith('[')) {
                    try {
                        const parsed = JSON.parse(designSrc);
                        if (Array.isArray(parsed) && parsed.length > 0) designSrc = parsed[0];
                    } catch(e) { /* ignore */ }
                }
                // Ensure full URL
                if (designSrc && !designSrc.startsWith('http')) {
                    designSrc = baseUrl + designSrc;
                }
                img.src = designSrc;
            }
            staticPreview.style.display = 'block';
        }
    }
    
    /**
     * Load product customization for installation appointment (read-only version)
     */
    async function loadProductCustomizationForInstallation(apt) {
        console.log('[Installation] Loading product customization for order:', apt.order_id);
        
        const dynamicContainer = document.getElementById('inst-dynamic-specs-container');
        const staticSpecs = document.getElementById('inst-static-specs');
        
        if (!dynamicContainer) {
            console.warn('[Installation] Dynamic container not found');
            return;
        }
        
        // Show loading state
        dynamicContainer.innerHTML = '<div style="text-align: center; padding: 20px; color: #9ca3af;"><i class="fas fa-spinner fa-spin"></i> Loading product specifications...</div>';
        
        try {
            console.log('[Installation] Fetching from:', getProductCustomizationUrl + '?order_id=' + apt.order_id);
            const response = await fetch(getProductCustomizationUrl + '?order_id=' + apt.order_id);
            const data = await response.json();
            console.log('[Installation] Received data:', data);
            
            if (!data.success || !data.product) {
                // Fallback to static fields
                showInstStaticSpecsFallback(dynamicContainer, staticSpecs);
                return;
            }
            
            instProductData = data;
            instCustomValues = {};
            
            // Pre-populate customization values from customer selections
            if (data.customerSelections && typeof data.customerSelections === 'object') {
                Object.assign(instCustomValues, data.customerSelections);
            }
            
            // Load saved customization data from order_item_customization
            if (apt.order_item_customization) {
                try {
                    const savedCustomization = JSON.parse(apt.order_item_customization);
                    if (savedCustomization && typeof savedCustomization === 'object') {
                        Object.assign(instCustomValues, savedCustomization);
                    }
                } catch (e) {
                    console.warn('Failed to parse order_item_customization:', e);
                }
            }
            
            // Also set dimension values from the appointment
            if (apt.dimensions) {
                const match = apt.dimensions.toString().match(/([\d.]+)\s*(in|cm|mm|ft)?\s*x\s*([\d.]+)\s*(in|cm|mm|ft)?/i);
                if (match) {
                    instCustomValues._width = parseFloat(match[1]);
                    instCustomValues._height = parseFloat(match[3]);
                    instCustomValues._unit = (match[2] || match[4] || 'in').toLowerCase();
                }
            }
            
            // Set quantity and price
            if (apt.quantity) instCustomValues._quantity = apt.quantity;
            if (apt.estimate_price) instCustomValues._estimatePrice = apt.estimate_price;
            
            // Populate dimensions fields (read-only)
            const widthInput = document.getElementById('inst-spec-width');
            const heightInput = document.getElementById('inst-spec-height');
            const unitInput = document.getElementById('inst-spec-unit-display');
            const quantityInput = document.getElementById('inst-spec-quantity');
            
            if (widthInput) widthInput.value = instCustomValues._width || '';
            if (heightInput) heightInput.value = instCustomValues._height || '';
            if (unitInput) unitInput.value = instCustomValues._unit || 'cm';
            if (quantityInput) quantityInput.value = instCustomValues._quantity || apt.quantity || '1';
            
            // Render dynamic specification fields (read-only)
            console.log('[Installation] Rendering dynamic fields...');
            renderInstDynamicFields(data, dynamicContainer, staticSpecs);
            
            console.log('[Installation] Product customization loaded successfully');
            
        } catch (error) {
            console.error('Error loading product customization data for installation:', error);
            showInstStaticSpecsFallback(dynamicContainer, staticSpecs);
        }
    }
    
    function showInstStaticSpecsFallback(dynamicContainer, staticSpecs) {
        dynamicContainer.innerHTML = '';
        dynamicContainer.style.display = 'none';
        if (staticSpecs) staticSpecs.style.display = 'block';
    }
    
    
    /**
     * Render product-specific specification fields for installation (read-only)
     */
    function renderInstDynamicFields(data, dynamicContainer, staticSpecs) {
        const fields = data.fieldConfig || [];
        const customerSelections = data.customerSelections || {};
        
        if (fields.length === 0) {
            showInstStaticSpecsFallback(dynamicContainer, staticSpecs);
            return;
        }
        
        dynamicContainer.style.display = 'block';
        if (staticSpecs) staticSpecs.style.display = 'none';
        
        dynamicContainer.innerHTML = '';
        
        const gridDiv = document.createElement('div');
        gridDiv.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr; gap: 12px;';
        
        fields.forEach(field => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'info-item';
            
            const label = document.createElement('span');
            label.className = 'info-label';
            label.textContent = field.label || field.id;
            
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.id = `inst-field-${field.id}`;
            input.disabled = true;
            input.style.background = '#f3f4f6';
            
            // Set value from customization
            const value = instCustomValues[field.id] || customerSelections[field.id] || '';
            input.value = value || '-';
            
            itemDiv.appendChild(label);
            itemDiv.appendChild(input);
            gridDiv.appendChild(itemDiv);
        });
        
        dynamicContainer.appendChild(gridDiv);
    }
    
    /**
     * Render product-specific specification fields as dropdowns
     */
    function renderAdminDynamicFields(data, dynamicContainer, staticSpecs) {
        const fields = data.fieldConfig || [];
        const tagPrices = data.tagPrices || {};
        const selectedOptions = data.selectedOptions || {};
        const customerSelections = data.customerSelections || {};
        
        if (fields.length === 0) {
            // No dynamic fields, fall back to static
            showStaticSpecsFallback(dynamicContainer, staticSpecs);
            return;
        }
        
        // Hide static specs, show dynamic
        dynamicContainer.style.display = 'block';
        if (staticSpecs) staticSpecs.style.display = 'none';
        
        dynamicContainer.innerHTML = '';
        
        // Group fields into rows of 2
        const gridDiv = document.createElement('div');
        gridDiv.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr; gap: 12px;';
        
        fields.forEach(field => {
            if (field.type === 'dimensions') return; // We handle dimensions separately
            
            const group = document.createElement('div');
            group.className = 'spec-field-group';
            group.setAttribute('data-field-id', field.id);
            
            const label = document.createElement('label');
            label.textContent = field.label || field.id;
            group.appendChild(label);
            
            if (field.type === 'tags' && field.options && field.options.length > 0) {
                // Filter options by product's selected tags if available
                let options = field.options;
                if (selectedOptions[field.id] && Array.isArray(selectedOptions[field.id]) && selectedOptions[field.id].length > 0) {
                    options = field.options.filter(opt => selectedOptions[field.id].includes(opt));
                }
                
                const select = document.createElement('select');
                select.className = 'form-control editable admin-dynamic-field';
                select.setAttribute('data-field-id', field.id);
                select.id = 'admin-spec-' + field.id;
                
                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = 'Select ' + (field.label || field.id);
                select.appendChild(defaultOpt);
                
                options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt;
                    option.textContent = opt;
                    // Show price if available
                    const price = tagPrices[field.id] && tagPrices[field.id][opt];
                    if (price && price > 0) {
                        option.textContent += ` (+₱${price.toLocaleString()})`;
                    }
                    // Pre-select from adminCustomValues (which includes both customer selections and saved admin edits)
                    if (adminCustomValues[field.id] === opt) {
                        option.selected = true;
                        console.log(`Pre-selected ${field.id}: ${opt}`);
                    }
                    select.appendChild(option);
                });
                
                // Update preview on change
                select.addEventListener('change', function() {
                    const fieldId = this.getAttribute('data-field-id');
                    const value = this.value;
                    adminCustomValues[fieldId] = value;
                    console.log(`Dropdown updated: adminCustomValues[${fieldId}] = "${value}" (selectedIndex: ${this.selectedIndex})`);
                    updateAdminKonvaPreview();
                });
                
                // Also listen for input events to catch programmatic changes
                select.addEventListener('input', function() {
                    const fieldId = this.getAttribute('data-field-id');
                    const value = this.value;
                    adminCustomValues[fieldId] = value;
                    console.log(`Dropdown input event: adminCustomValues[${fieldId}] = "${value}"`);
                });
                
                group.appendChild(select);
            } else if (field.type === 'number') {
                const input = document.createElement('input');
                input.type = 'number';
                input.className = 'form-control editable admin-dynamic-field';
                input.setAttribute('data-field-id', field.id);
                input.id = 'admin-spec-' + field.id;
                input.min = field.min || 0;
                input.step = field.step || 1;
                input.placeholder = field.label || '';
                // Pre-populate from adminCustomValues (merged customer + saved admin data)
                if (adminCustomValues[field.id]) {
                    input.value = adminCustomValues[field.id];
                }
                input.addEventListener('change', function() {
                    adminCustomValues[this.getAttribute('data-field-id')] = this.value;
                    updateAdminKonvaPreview();
                });
                group.appendChild(input);
            } else if (field.type === 'checkbox') {
                const checkDiv = document.createElement('div');
                checkDiv.style.cssText = 'display: flex; align-items: center; gap: 8px; padding: 8px 0;';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'admin-dynamic-field';
                checkbox.setAttribute('data-field-id', field.id);
                checkbox.id = 'admin-spec-' + field.id;
                // Pre-populate from adminCustomValues (merged customer + saved admin data)
                if (adminCustomValues[field.id]) {
                    checkbox.checked = (adminCustomValues[field.id] === 'Yes' || adminCustomValues[field.id] === true);
                }
                checkbox.addEventListener('change', function() {
                    const fieldId = this.getAttribute('data-field-id');
                    const value = this.checked ? 'Yes' : '';
                    adminCustomValues[fieldId] = value;
                    console.log(`Updated adminCustomValues[${fieldId}] = ${value}`);
                    updateAdminKonvaPreview();
                });
                const checkLabel = document.createElement('span');
                checkLabel.textContent = field.label || field.id;
                checkDiv.appendChild(checkbox);
                checkDiv.appendChild(checkLabel);
                group.appendChild(checkDiv);
            } else {
                // Default: text input
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control editable admin-dynamic-field';
                input.setAttribute('data-field-id', field.id);
                input.id = 'admin-spec-' + field.id;
                input.placeholder = field.label || '';
                // Pre-populate from adminCustomValues (merged customer + saved admin data)
                if (adminCustomValues[field.id]) {
                    input.value = adminCustomValues[field.id];
                }
                input.addEventListener('change', function() {
                    adminCustomValues[this.getAttribute('data-field-id')] = this.value;
                    updateAdminKonvaPreview();
                });
                group.appendChild(input);
            }
            
            gridDiv.appendChild(group);
        });
        
        dynamicContainer.appendChild(gridDiv);
    }
    
    /**
     * Initialize Konva stage for admin 2D preview
     */
    function initAdminKonvaPreview(data, apt, konvaWrapper, konvaContainer, staticPreview) {
        if (!konvaContainer || typeof Konva === 'undefined') {
            // Konva not available, show static preview
            showStaticPreviewFallback(apt, konvaWrapper, staticPreview);
            return;
        }
        
        // Destroy previous stage
        if (adminKonvaStage) {
            adminKonvaStage.destroy();
            adminKonvaStage = null;
            adminKonvaLayer = null;
        }
        
        // Show Konva wrapper, hide static
        if (konvaWrapper) konvaWrapper.style.display = 'block';
        if (staticPreview) staticPreview.style.display = 'none';
        
        // Get responsive stage size from container (don't add padding, keep within bounds)
        const containerWidth = konvaContainer.offsetWidth || 280;
        const containerHeight = konvaContainer.offsetHeight || 280;
        const stageSize = Math.min(containerWidth, containerHeight, 280);
        
        try {
            adminKonvaStage = new Konva.Stage({
                container: 'admin-konva-container',
                width: stageSize,
                height: stageSize
            });
            
            adminKonvaLayer = new Konva.Layer();
            adminKonvaStage.add(adminKonvaLayer);
            
            // Store on window for global access
            window.adminKonvaLayer = adminKonvaLayer;
            
            // Store stage size globally for renderer
            window.ADMIN_STAGE_SIZE = stageSize;
            window.ADMIN_DRAWING_SIZE = stageSize - 80; // Subtract padding
            window.ADMIN_PADDING = 30;
            
            // Set global styles for the renderer
            window.adminGlassStyles = window.glassStyles || {};
            window.adminFrameStyles = window.frameStyles || {};
            
            // Load visual configs if available
            if (data.tagVisualConfigs && typeof data.tagVisualConfigs === 'object') {
                loadAdminVisualConfigs(data.tagVisualConfigs);
            }
            
            // Render initial preview
            updateAdminKonvaPreview();
            
            // Also wire up dimension changes to trigger preview update
            const widthInput = document.getElementById('detail-spec-width');
            const heightInput = document.getElementById('detail-spec-height');
            const unitSelect = document.getElementById('detail-spec-unit');
            
            if (widthInput) {
                widthInput.removeEventListener('input', onAdminDimensionChange);
                widthInput.addEventListener('input', onAdminDimensionChange);
            }
            if (heightInput) {
                heightInput.removeEventListener('input', onAdminDimensionChange);
                heightInput.addEventListener('input', onAdminDimensionChange);
            }
            if (unitSelect) {
                unitSelect.removeEventListener('change', onAdminDimensionChange);
                unitSelect.addEventListener('change', onAdminDimensionChange);
            }
            
        } catch (error) {
            console.error('Error initializing Konva preview:', error);
            showStaticPreviewFallback(apt, konvaWrapper, staticPreview);
        }
    }
    
    function onAdminDimensionChange() {
        updateAdminKonvaPreview();
    }
    
    /**
     * Load visual configs for glass/frame styles
     */
    function loadAdminVisualConfigs(tagVisualConfigs) {
        if (!tagVisualConfigs) return;
        
        for (const [fieldId, configs] of Object.entries(tagVisualConfigs)) {
            for (const [tagName, config] of Object.entries(configs)) {
                const lcFieldId = fieldId.toLowerCase();
                if (lcFieldId.includes('glass') && lcFieldId.includes('color') || lcFieldId.includes('glasstype') || lcFieldId.includes('glass_type')) {
                    if (!window.glassStyles) window.glassStyles = {};
                    window.glassStyles[tagName] = config;
                } else if (lcFieldId.includes('frame') && lcFieldId.includes('color') || lcFieldId.includes('frametype') || lcFieldId.includes('frame_type')) {
                    if (!window.frameStyles) window.frameStyles = {};
                    window.frameStyles[tagName] = config;
                }
            }
        }
    }
    
    /**
     * Update the admin Konva 2D preview based on current specs
     */
    function updateAdminKonvaPreview() {
        if (!adminKonvaLayer || !adminProductData) return;
        
        const product = adminProductData.product || {};
        const widthInput = document.getElementById('detail-spec-width');
        const heightInput = document.getElementById('detail-spec-height');
        const unitSelect = document.getElementById('detail-spec-unit');
        
        const width = parseFloat(widthInput?.value) || 45;
        const height = parseFloat(heightInput?.value) || 35;
        const unit = unitSelect?.value || 'in';
        
        const dimensions = { width, height, unit };
        
        // Build full customization values from dynamic fields
        const allValues = { ...adminCustomValues };
        
        // Also collect from any dynamic field inputs
        document.querySelectorAll('.admin-dynamic-field').forEach(el => {
            const fieldId = el.getAttribute('data-field-id');
            if (fieldId) {
                if (el.type === 'checkbox') {
                    allValues[fieldId] = el.checked ? 'Yes' : '';
                } else if (el.value) {
                    allValues[fieldId] = el.value;
                }
            }
        });
        
        // Prepare product data for the renderer
        // Note: Backend returns Category/Subcategory with capital letters
        const productCategory = product.Category || product.category || '';
        const productSubcategory = product.Subcategory || product.subcategory || '';
        const productName = product.ProductName || product.name || '';
        
        const productInfo = {
            productType: productSubcategory,
            category: productCategory,
            name: productName,
            customizationValues: allValues
        };
        
        // Use responsive stage size
        const stageSize = window.ADMIN_STAGE_SIZE || 320;
        const drawingSize = window.ADMIN_DRAWING_SIZE || 260;
        const padding = window.ADMIN_PADDING || 30;
        
        // Set temporary global values for renderer (matching customer approach)
        const origStageSize = window.STAGE_SIZE;
        const origDrawingSize = window.DRAWING_SIZE;
        const origPadding = window.PADDING;
        window.STAGE_SIZE = stageSize;
        window.DRAWING_SIZE = drawingSize;
        window.PADDING = padding;
        
        // Sync customization values to window scope for renderer compatibility
        window.selectedCustomizationValues = allValues;
        window.selectedProduct = productInfo;
        // Prepare admin-specific visual overrides so shapes are visible
        // when rendered on a light background in admin modal.
        let __admin_orig_glass_styles = null;   
        try {
            if (typeof window.glassStyles !== 'undefined') {
                // clone existing styles to restore later
                try { __admin_orig_glass_styles = JSON.parse(JSON.stringify(window.glassStyles)); } catch(e) { __admin_orig_glass_styles = Object.assign({}, window.glassStyles); }
            } else {
                __admin_orig_glass_styles = null;
            }
            // apply gentle blue/teal fill to common glass style keys used by renderer
            window.glassStyles = window.glassStyles || {};
            const adminFill = { fill: 'rgba(30, 144, 255, 0.3)', opacity: 0.7 };
            ['clear','tempered','laminated','double','low-e','frosted','tinted','smoked'].forEach(k => {
                const key = (k || '').toString().toLowerCase();
                window.glassStyles[key] = window.glassStyles[key] || {};
                // only override fill/opacity to preserve other props
                window.glassStyles[key].fill = adminFill.fill;
                window.glassStyles[key].opacity = adminFill.opacity;
            });
        } catch (e) {
            console.warn('[Admin 2D Preview] Failed to apply admin glass style overrides', e);
        }
        
        try {
            // Log product info for debugging
            console.log('[Admin 2D Preview] Rendering product:', {
                category: productInfo.category,
                productType: productInfo.productType,
                customizationKeys: Object.keys(allValues),
                customizationValues: allValues,
                dimensions: dimensions,
                stageSize: stageSize,
                drawingSize: drawingSize
            });
            
            // Clear layer before rendering
            adminKonvaLayer.destroyChildren();
            
            // Use comprehensive renderer (same as customer 2D)
            if (typeof Comprehensive2DRenderer !== 'undefined' && Comprehensive2DRenderer.renderProduct2D) {
                console.log('[Admin 2D Preview] Using Comprehensive2DRenderer');
                Comprehensive2DRenderer.renderProduct2D(productInfo, dimensions, adminKonvaLayer);
                adminKonvaLayer.draw();
            } else if (typeof renderWithComprehensiveRenderer === 'function') {
                console.log('[Admin 2D Preview] Using renderWithComprehensiveRenderer wrapper');
                renderWithComprehensiveRenderer({
                    dimensions,
                    productData: productInfo,
                    customizationValues: allValues,
                    layer: adminKonvaLayer
                });
                adminKonvaLayer.draw();
            } else {
                console.warn('[Admin 2D Preview] Comprehensive renderer not available, using fallback');
                // Fallback: draw a basic rectangle
                drawBasicPreviewFallback(width, height, unit, height / width || 1, stageSize, drawingSize, padding);
            }
        } catch (error) {
            console.error('[Admin 2D Preview] Error rendering 2D preview:', error, 'productInfo:', productInfo);
            // Draw basic fallback rectangle on renderer failure
            try {
                drawBasicPreviewFallback(width, height, unit, height / width || 1, stageSize, drawingSize, padding);
            } catch (_) { /* ignore fallback error */ }
        } finally {
            // Restore globals
            window.STAGE_SIZE = origStageSize;
            window.DRAWING_SIZE = origDrawingSize;
            window.PADDING = origPadding;
            // Restore original glassStyles if we modified them
            try {
                if (typeof __admin_orig_glass_styles !== 'undefined' && __admin_orig_glass_styles !== null) {
                    window.glassStyles = __admin_orig_glass_styles;
                }
            } catch (e) {
                // ignore restore errors
            }
        }
        
        // Helper function to draw basic fallback with responsive sizing
        function drawBasicPreviewFallback(w, h, u, ratio, stageSize, drawingSize, padding) {
            adminKonvaLayer.destroyChildren();
            
            // Create a scaled version that fits within stage size
            // with consistent margins on all sides for dimension labels
            const labelSpace = Math.max(20, stageSize * 0.12);
            const margin = Math.max(8, stageSize * 0.04);
            const availableWidth = stageSize - (labelSpace + margin * 2);
            const availableHeight = stageSize - (labelSpace + margin * 2);
            
            // Calculate shape dimensions preserving aspect ratio
            let shapeWidth = availableWidth;
            let shapeHeight = shapeWidth * ratio;
            
            // Scale down if height exceeds available space
            if (shapeHeight > availableHeight) {
                shapeHeight = availableHeight;
                shapeWidth = shapeHeight / ratio;
            }
            
            // Position shape with space for labels
            const shapeX = labelSpace + (availableWidth - shapeWidth) / 2 + margin;
            const shapeY = labelSpace + (availableHeight - shapeHeight) / 2 + margin;
            
            // Draw the main rectangle shape
            const rect = new Konva.Rect({
                x: shapeX,
                y: shapeY,
                width: shapeWidth,
                height: shapeHeight,
                fill: '#D0E8F2',
                stroke: '#0284c7',
                strokeWidth: Math.max(2, stageSize * 0.01),
                cornerRadius: 0
            });
            adminKonvaLayer.add(rect);
            
            // Add dimension lines (like in the reference image)
            const lineStroke = '#999999';
            const lineStrokeWidth = Math.max(1, stageSize * 0.008);
            const dashLength = Math.max(3, stageSize * 0.02);
            
            // Top horizontal dimension line
            const topLineY = shapeY - margin / 2;
            const topLine = new Konva.Line({
                points: [shapeX, topLineY, shapeX + shapeWidth, topLineY],
                stroke: lineStroke,
                strokeWidth: lineStrokeWidth,
                dash: [dashLength, dashLength]
            });
            adminKonvaLayer.add(topLine);
            
            // Left vertical dimension line
            const leftLineX = shapeX - margin / 2;
            const leftLine = new Konva.Line({
                points: [leftLineX, shapeY, leftLineX, shapeY + shapeHeight],
                stroke: lineStroke,
                strokeWidth: lineStrokeWidth,
                dash: [dashLength, dashLength]
            });
            adminKonvaLayer.add(leftLine);
            
            // Right vertical dimension line
            const rightLineX = shapeX + shapeWidth + margin / 2;
            const rightLine = new Konva.Line({
                points: [rightLineX, shapeY, rightLineX, shapeY + shapeHeight],
                stroke: lineStroke,
                strokeWidth: lineStrokeWidth,
                dash: [dashLength, dashLength]
            });
            adminKonvaLayer.add(rightLine);
            
            // Corner markers (small lines at corners)
            const markerSize = Math.max(6, stageSize * 0.035);
            const corners = [
                { x: shapeX, y: shapeY },
                { x: shapeX + shapeWidth, y: shapeY },
                { x: shapeX, y: shapeY + shapeHeight },
                { x: shapeX + shapeWidth, y: shapeY + shapeHeight }
            ];
            
            corners.forEach(corner => {
                // Horizontal corner marker
                const hMarker = new Konva.Line({
                    points: [corner.x - markerSize/2, corner.y, corner.x + markerSize/2, corner.y],
                    stroke: lineStroke,
                    strokeWidth: lineStrokeWidth * 2.5
                });
                adminKonvaLayer.add(hMarker);
                
                // Vertical corner marker
                const vMarker = new Konva.Line({
                    points: [corner.x, corner.y - markerSize/2, corner.x, corner.y + markerSize/2],
                    stroke: lineStroke,
                    strokeWidth: lineStrokeWidth * 2.5
                });
                adminKonvaLayer.add(vMarker);
            });
            
            // Add width dimension label at top
            const labelFontSize = Math.max(10, Math.min(14, stageSize * 0.085));
            const topLabel = new Konva.Text({
                x: shapeX,
                y: 2,
                width: shapeWidth,
                text: `${w}${u}`,
                fontSize: labelFontSize,
                fontStyle: 'bold',
                fill: '#0284c7',
                align: 'center',
                verticalAlign: 'top'
            });
            adminKonvaLayer.add(topLabel);
            
            // Add height dimension label at right
            const rightLabel = new Konva.Text({
                x: rightLineX + 3,
                y: shapeY + (shapeHeight / 2) - (labelFontSize / 2),
                text: `${h}${u}`,
                fontSize: labelFontSize,
                fontStyle: 'bold',
                fill: '#0284c7',
                align: 'left',
                verticalAlign: 'middle'
            });
            adminKonvaLayer.add(rightLabel);
            
            // Add center dimension text
            const centerTextSize = Math.max(9, Math.min(13, stageSize * 0.08));
            const centerText = new Konva.Text({
                x: shapeX,
                y: shapeY + (shapeHeight / 2) - (centerTextSize / 2),
                width: shapeWidth,
                text: `${w}${u} × ${h}${u}`,
                fontSize: centerTextSize,
                fontStyle: 'bold',
                fill: '#0c4a6e',
                align: 'center',
                verticalAlign: 'middle',
                ellipsis: true
            });
            adminKonvaLayer.add(centerText);
            
            adminKonvaLayer.draw();
        }
    }
    
    // ======================
    // POPULATE APPOINTMENT MODAL
    // ======================
    function populateAppointmentModal(apt) {
        // Clear pending photos when opening modal
        pendingSitePhotos = [];
        
        // Store appointment globally for payment workflow functions
        window.currentAppointment = apt;
        
        // Order information
        const orderLink = document.getElementById('detail-order-link');
        if (orderLink) {
            orderLink.href = baseUrl + 'admin-orders?order_id=' + apt.order_id;
            orderLink.textContent = apt.order_id || '-';
        }
        
        // Order date
        const orderDate = document.getElementById('detail-order-date');
        if (orderDate) {
            orderDate.textContent = apt.order_date || '-';
        }
        
        // Client information
        const clientName = document.getElementById('detail-client-name');
        if (clientName) {
            clientName.value = apt.client || apt.client_name || '';
        }
        
        // Client phone
        const clientPhone = document.getElementById('detail-client-phone');
        if (clientPhone) {
            clientPhone.textContent = apt.client_phone || '-';
        }
        
        // Client address
        const clientAddress = document.getElementById('detail-client-address');
        if (clientAddress) {
            clientAddress.textContent = apt.client_address || '-';
        }
        
        // Appointment information
        const serviceType = document.getElementById('detail-service-type');
        if (serviceType) {
            serviceType.textContent = apt.service || (appointmentType === 'ocular' ? 'Ocular Visit' : 'Installed');
        }
        
        const appointmentDate = document.getElementById('detail-appointment-date');
        if (appointmentDate) {
            appointmentDate.value = apt.date || '';
        }
        
        const appointmentTime = document.getElementById('detail-appointment-time');
        if (appointmentTime) {
            appointmentTime.value = apt.time || '';
        }
        
        // Assigned staff - match by ID first, then by name as fallback
        const assignedStaff = document.getElementById('detail-assigned-staff');
        if (assignedStaff) {
            // Get staff ID - check if it's explicitly set (even if 0) or use null
            const assignedStaffId = apt.assigned_staff_id !== undefined && apt.assigned_staff_id !== null ? apt.assigned_staff_id : null;
            const assignedStaffName = apt.assigned_staff || '';
            assignedStaff.innerHTML = '<option value="">Select Staff...</option>' + 
                staffList.map(staff => {
                    // Prioritize ID match, then fallback to name match
                    const isSelected = (assignedStaffId !== null && staff.id == assignedStaffId) || 
                                      (assignedStaffId === null && assignedStaffName && staff.name.trim() === assignedStaffName.trim());
                    return `<option value="${staff.id}" ${isSelected ? 'selected' : ''}>${staff.name}</option>`;
                }).join('');
        }
        
        // Status
        const status = document.getElementById('detail-status');
        if (status) {
            status.value = apt.status || 'In Progress';
        }

        // Order specifications (ocular only)
        const orderItemId = document.getElementById('detail-order-item-id');
        if (orderItemId) {
            orderItemId.value = apt.order_item_id || '';
        }
        const specWidth = document.getElementById('detail-spec-width');
        const specHeight = document.getElementById('detail-spec-height');
        const specUnit = document.getElementById('detail-spec-unit');
        if (specWidth || specHeight || specUnit) {
            const dims = (apt.dimensions || '').toString();
            const match = dims.match(/([\d.]+)\s*(in|cm|mm)?\s*x\s*([\d.]+)\s*(in|cm|mm)?/i);
            if (match) {
                if (specWidth) specWidth.value = match[1];
                if (specHeight) specHeight.value = match[3];
                const unit = match[2] || match[4] || 'in';
                if (specUnit) specUnit.value = unit.toLowerCase();
            } else {
                if (specWidth) specWidth.value = '';
                if (specHeight) specHeight.value = '';
            }
        }
        const specQuantity = document.getElementById('detail-spec-quantity');
        if (specQuantity) {
            specQuantity.value = apt.quantity || '';
        }
        
        // Populate customization fields - always show fields with customer's order values
        if (appointmentType === 'ocular') {
            // Populate static fallback fields
            const customizationFields = [
                { id: 'detail-spec-shape', value: apt.glass_shape, row: 'spec-shape-row' },
                { id: 'detail-spec-type', value: apt.glass_type, row: 'spec-type-row' },
                { id: 'detail-spec-thickness', value: apt.glass_thickness, row: 'spec-thickness-row' },
                { id: 'detail-spec-edge', value: apt.edge_work, row: 'spec-edge-row' },
                { id: 'detail-spec-frame', value: apt.frame_type, row: 'spec-frame-row' },
                { id: 'detail-spec-engraving', value: apt.engraving, row: 'spec-engraving-row' }
            ];
            
            customizationFields.forEach(field => {
                const element = document.getElementById(field.id);
                const rowElement = document.getElementById(field.row);
                
                if (element) {
                    // Always show the row
                    if (rowElement) {
                        rowElement.style.display = '';
                    }
                    // Populate the value from customer's order
                    if (field.value && field.value.trim() !== '') {
                        element.value = field.value;
                    } else {
                        // Reset to default if no customer value
                        element.value = '';
                    }
                }
            });
            
            // Populate Product Preview section
            populateProductPreview(apt);
            
            // Load dynamic product-specific specs and 2D preview
            loadProductCustomizationForAppointment(apt);
        }
        
        // Notes
        const notes = document.getElementById('detail-notes');
        if (notes) {
            notes.value = apt.notes || '';
        }
        
        // Ocular notes (if ocular appointment)
        if (appointmentType === 'ocular') {
            const ocularNotes = document.getElementById('detail-ocular-notes');
            if (ocularNotes) {
                ocularNotes.value = apt.ocular_notes || '';
            }
            
            // Populate payment breakdown (downpayment only for ocular)
            const downpaymentAmount = document.getElementById('detail-downpayment-amount');
            const downpaymentMethod = document.getElementById('detail-downpayment-method');
            const downpaymentStatus = document.getElementById('detail-downpayment-status');
            const downpaymentBadge = document.getElementById('downpayment-status-badge');
            
            if (apt.payment_data) {
                // If payment data exists, populate it
                if (downpaymentAmount) downpaymentAmount.value = apt.payment_data.downpayment_amount || '';
                if (downpaymentMethod) downpaymentMethod.value = apt.payment_data.downpayment_method || '';
                if (downpaymentStatus) downpaymentStatus.value = apt.payment_data.downpayment_status || 'Pending';
                
                // Update badge color based on status
                if (downpaymentBadge) {
                    const status = apt.payment_data.downpayment_status || 'Pending';
                    if (status === 'Paid') {
                        downpaymentBadge.textContent = 'Paid';
                        downpaymentBadge.style.backgroundColor = '#28a745';
                        downpaymentBadge.style.color = '#fff';
                    } else {
                        downpaymentBadge.textContent = 'Pending';
                        downpaymentBadge.style.backgroundColor = '#ffc107';
                        downpaymentBadge.style.color = '#000';
                    }
                }
            } else {
                // Don't auto-calculate from estimate price - let admin enter total amount manually
                // This ensures the downpayment field starts empty
                /*
                // Calculate 50% from estimate price if available
                if (apt.estimate_price && downpaymentAmount) {
                    const fiftyPercent = (parseFloat(apt.estimate_price) * 0.5).toFixed(2);
                    downpaymentAmount.value = fiftyPercent;
                }
                */
            }

            // Total amount input (admin-entered) - if present, use it to auto-calc payments
            const totalAmountInput = document.getElementById('detail-total-amount');
            const fabricationAmountInput = document.getElementById('detail-fabrication-amount');
            const installationAmountInput = document.getElementById('detail-installation-amount');

            // Helper to recalculate values
            function recalcPaymentsFromTotal(totalVal) {
                const total = parseFloat(totalVal) || 0;
                const dp = (total * 0.5).toFixed(2);
                const fab = (total * 0.4).toFixed(2);
                const inst = (total * 0.1).toFixed(2);
                if (downpaymentAmount) downpaymentAmount.value = dp;
                if (fabricationAmountInput) {
                    fabricationAmountInput.value = fab;
                    fabricationAmountInput.placeholder = '';
                }
                if (installationAmountInput) {
                    installationAmountInput.value = inst;
                    installationAmountInput.placeholder = '';
                }
            }

            // Helper to calculate and show amounts even when locked
            function showCalculatedAmounts() {
                // Use total amount from payment data or estimate price as fallback
                let totalAmount = 0;
                if (apt.payment_data && apt.payment_data.total_amount) {
                    totalAmount = parseFloat(apt.payment_data.total_amount);
                } else if (apt.unit_price || apt.estimate_price) {
                    totalAmount = parseFloat(apt.unit_price || apt.estimate_price);
                }
                
                if (totalAmount > 0) {
                    const fab = (totalAmount * 0.4).toFixed(2);
                    const inst = (totalAmount * 0.1).toFixed(2);
                    
                    if (fabricationAmountInput) {
                        fabricationAmountInput.placeholder = `₱${Number(fab).toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        fabricationAmountInput.value = fab;
                    }
                    if (installationAmountInput) {
                        installationAmountInput.placeholder = `₱${Number(inst).toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        installationAmountInput.value = inst;
                    }
                }
            }

            // Initialize total from existing data (payment_data only - not estimate_price)
            if (totalAmountInput) {
                // Load from saved payment data if it exists (this preserves manually entered amounts)
                if (apt.payment_data && apt.payment_data.total_amount) {
                    const savedTotal = parseFloat(apt.payment_data.total_amount);
                    if (savedTotal > 0) {
                        totalAmountInput.value = savedTotal.toFixed(2);
                        recalcPaymentsFromTotal(apt.payment_data.total_amount);
                        console.log('Loaded saved total amount:', savedTotal);
                    }
                }
                // NOTE: No longer auto-populate from estimate_price to keep field empty for new entries

                // Live update on input and change
                const totalInputHandler = function(e) {
                    recalcPaymentsFromTotal(e.target.value);
                };
                totalAmountInput.addEventListener('input', totalInputHandler);
                totalAmountInput.addEventListener('change', totalInputHandler);

                // Also update on blur to ensure formatting
                totalAmountInput.addEventListener('blur', function(e) {
                    const val = parseFloat(e.target.value) || 0;
                    e.target.value = val ? val.toFixed(2) : '';
                    recalcPaymentsFromTotal(e.target.value);
                });
                
                // Always show calculated amounts for fabrication and installation payments
                showCalculatedAmounts();
            } else {
                // Show calculated amounts even without total amount input
                showCalculatedAmounts();
            }
            
            // Update badge when status changes
            if (downpaymentStatus) {
                downpaymentStatus.addEventListener('change', function() {
                    if (downpaymentBadge) {
                        if (this.value === 'Paid') {
                            downpaymentBadge.textContent = 'Paid';
                            downpaymentBadge.style.backgroundColor = '#28a745';
                            downpaymentBadge.style.color = '#fff';
                        } else {
                            downpaymentBadge.textContent = 'Pending';
                            downpaymentBadge.style.backgroundColor = '#ffc107';
                            downpaymentBadge.style.color = '#000';
                        }
                    }
                });
            }
            
            // Load site photos if available
            loadSitePhotos(apt.id);

            const receiptLink = document.getElementById('detail-payment-receipt-link');
            const receiptInput = document.getElementById('detail-payment-receipt');
            
            // Remove any existing status messages or helper text first
            if (receiptLink) {
                const existingStatus = receiptLink.querySelector('.receipt-status');
                if (existingStatus) {
                    existingStatus.remove();
                }
            }
            if (receiptInput) {
                const existingHelper = receiptInput.nextElementSibling;
                if (existingHelper && existingHelper.classList.contains('receipt-helper')) {
                    existingHelper.remove();
                }
                // Always clear file input when opening modal (file inputs can't be pre-filled)
                receiptInput.value = '';
            }
            
            if (receiptLink) {
                if (apt.receipt_url && apt.receipt_url.trim() !== '') {
                    receiptLink.style.display = 'block';
                    const anchor = receiptLink.querySelector('a');
                    if (anchor) {
                        // Ensure URL is properly formatted
                        let receiptUrl = apt.receipt_url;
                        if (!receiptUrl.startsWith('http://') && !receiptUrl.startsWith('https://') && !receiptUrl.startsWith('/')) {
                            // If it's a relative path, ensure it has base URL
                            if (receiptUrl.startsWith('uploads/')) {
                                receiptUrl = baseUrl + receiptUrl;
                            } else {
                                receiptUrl = baseUrl + 'uploads/' + receiptUrl;
                            }
                        }
                        anchor.href = receiptUrl;
                        // Extract filename from URL for display
                        const urlParts = receiptUrl.split('/');
                        const fileName = urlParts[urlParts.length - 1].split('?')[0]; // Remove query params if any
                        anchor.innerHTML = `<i class="fas fa-file-pdf" style="margin-right: 5px;"></i>View uploaded receipt: ${fileName}`;
                        anchor.title = fileName;
                    }
                    // Add a message that receipt already exists
                    const statusMsg = document.createElement('p');
                    statusMsg.className = 'receipt-status';
                    statusMsg.style.marginTop = '8px';
                    statusMsg.style.color = '#28a745';
                    statusMsg.style.fontSize = '13px';
                    statusMsg.innerHTML = '<i class="fas fa-check-circle" style="margin-right: 5px;"></i>Receipt already uploaded';
                    receiptLink.appendChild(statusMsg);
                    
                    // Add helper text to file input
                    if (receiptInput) {
                        const helper = document.createElement('small');
                        helper.className = 'receipt-helper';
                        helper.style.display = 'block';
                        helper.style.marginTop = '4px';
                        helper.style.color = '#6c757d';
                        helper.style.fontStyle = 'italic';
                        helper.textContent = 'Upload a new file to replace the existing receipt';
                        receiptInput.parentNode.insertBefore(helper, receiptInput.nextSibling);
                    }
                } else {
                    receiptLink.style.display = 'none';
                }
            }
        }
        
        // Installation notes (if installation appointment)
        if (appointmentType === 'installation') {
            const installationNotes = document.getElementById('detail-installation-notes');
            if (installationNotes) {
                installationNotes.value = apt.installation_notes || '';
            }
            
            // Load dynamic product-specific specs and 2D preview for installation (read-only)
            loadProductCustomizationForInstallation(apt);
            
            // Populate Payment Breakdown for Installation
            // Calculate total from downpayment (which is 50%)
            let totalAmount = parseFloat(apt.estimate_price || apt.unit_price || 0);
            
            // If we have payment_data with downpayment, use that to calculate accurate total
            if (apt.payment_data && apt.payment_data.downpayment_amount) {
                totalAmount = parseFloat(apt.payment_data.downpayment_amount) * 2; // downpayment is 50%, so total = downpayment * 2
            }
            
            // Downpayment (50%) - read-only
            const instDownpaymentAmount = document.getElementById('inst-downpayment-amount');
            const instDownpaymentMethod = document.getElementById('inst-downpayment-method');
            if (apt.payment_data) {
                if (instDownpaymentAmount) instDownpaymentAmount.value = apt.payment_data.downpayment_amount || (totalAmount * 0.5).toFixed(2);
                if (instDownpaymentMethod) instDownpaymentMethod.value = apt.payment_data.downpayment_method || '—';
            } else {
                if (instDownpaymentAmount) instDownpaymentAmount.value = (totalAmount * 0.5).toFixed(2);
                if (instDownpaymentMethod) instDownpaymentMethod.value = '—';
            }
            
            // Fabrication Payment (40%) - read-only
            const instFabricationAmount = document.getElementById('inst-fabrication-amount');
            const instFabricationMethod = document.getElementById('inst-fabrication-method');
            const instFabricationBadge = document.getElementById('inst-fabrication-badge');
            
            // Always calculate fabrication amount from total (40%)
            if (instFabricationAmount) instFabricationAmount.value = (totalAmount * 0.4).toFixed(2);
            
            if (apt.payment_data) {
                if (instFabricationMethod) instFabricationMethod.value = apt.payment_data.fabrication_method || '—';
                if (instFabricationBadge) {
                    const fabStatus = apt.payment_data.fabrication_status || 'Pending';
                    instFabricationBadge.textContent = fabStatus === 'Paid' ? 'Completed' : fabStatus;
                    instFabricationBadge.style.backgroundColor = fabStatus === 'Paid' ? '#28a745' : '#ffc107';
                    instFabricationBadge.style.color = fabStatus === 'Paid' ? '#fff' : '#000';
                }
            } else {
                if (instFabricationMethod) instFabricationMethod.value = '—';
            }
            
            // Installation Payment (10%) - editable
            const instInstallationAmount = document.getElementById('inst-installation-amount');
            const instInstallationMethod = document.getElementById('inst-installation-method');
            const instInstallationStatus = document.getElementById('inst-installation-status');
            const instPaymentBadge = document.getElementById('inst-payment-badge');
            const instPaymentWarning = document.getElementById('inst-payment-warning');
            const instPaymentDueDate = document.getElementById('inst-payment-due-date');
            
            // Always show 10% of total amount (read-only)
            if (instInstallationAmount) instInstallationAmount.value = (totalAmount * 0.1).toFixed(2);
            
            if (apt.payment_data) {
                if (instInstallationMethod) instInstallationMethod.value = apt.payment_data.installation_method || '';
                if (instInstallationStatus) instInstallationStatus.value = apt.payment_data.installation_status || 'Pending';
                
                // Update badge
                if (instPaymentBadge) {
                    const instStatus = apt.payment_data.installation_status || 'Pending';
                    instPaymentBadge.textContent = instStatus;
                    instPaymentBadge.style.backgroundColor = instStatus === 'Paid' ? '#28a745' : '#ffc107';
                    instPaymentBadge.style.color = instStatus === 'Paid' ? '#fff' : '#000';
                }
                
                // Show payment due warning if installation is completed but not paid
                if (apt.payment_data.installation_completed_date && apt.payment_data.installation_status !== 'Paid') {
                    if (instPaymentWarning) instPaymentWarning.style.display = 'block';
                    if (instPaymentDueDate && apt.payment_data.installation_payment_due_date) {
                        const dueDate = new Date(apt.payment_data.installation_payment_due_date);
                        instPaymentDueDate.textContent = dueDate.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
                        
                        // Check if overdue
                        if (new Date() > dueDate) {
                            instPaymentWarning.style.background = '#f8d7da';
                            instPaymentWarning.style.borderColor = '#dc3545';
                            instPaymentWarning.querySelector('p').style.color = '#721c24';
                        }
                    }
                } else {
                    if (instPaymentWarning) instPaymentWarning.style.display = 'none';
                }
                
                // Receipt link
                const receiptLink = document.getElementById('inst-installation-receipt-link');
                if (receiptLink) {
                    if (apt.payment_data.installation_receipt_url) {
                        receiptLink.style.display = 'block';
                        receiptLink.querySelector('a').href = apt.payment_data.installation_receipt_url;
                    } else {
                        receiptLink.style.display = 'none';
                    }
                }
            } else {
                if (instInstallationMethod) instInstallationMethod.value = '';
                if (instInstallationStatus) instInstallationStatus.value = 'Pending';
                if (instPaymentWarning) instPaymentWarning.style.display = 'none';
            }
            
            // Load checklist if available
            loadInstallationChecklist(apt.id);
            
            // Trigger payment deadline banner display based on current status
            setTimeout(() => handleInstallationStatusChange(), 100);
        }
        
        // Fabrication notes and payment (if fabrication appointment)
        if (appointmentType === 'fabrication') {
            const fabricationNotes = document.getElementById('detail-fabrication-notes');
            if (fabricationNotes) {
                fabricationNotes.value = apt.fabrication_notes || '';
            }
            
            // Populate order specifications for fabrication (read-only display)
            const fabDimensions = document.getElementById('fab-dimensions');
            const fabQuantity = document.getElementById('fab-quantity');
            const fabGlassShape = document.getElementById('fab-glass-shape');
            const fabGlassType = document.getElementById('fab-glass-type');
            const fabGlassThickness = document.getElementById('fab-glass-thickness');
            const fabEdgeWork = document.getElementById('fab-edge-work');
            const fabFrameType = document.getElementById('fab-frame-type');
            const fabEngraving = document.getElementById('fab-engraving');
            
            if (fabDimensions) fabDimensions.textContent = apt.dimensions || '-';
            if (fabQuantity) fabQuantity.textContent = apt.quantity || '1';
            if (fabGlassShape) fabGlassShape.textContent = apt.glass_shape || '-';
            if (fabGlassType) fabGlassType.textContent = apt.glass_type || '-';
            if (fabGlassThickness) fabGlassThickness.textContent = apt.glass_thickness || '-';
            if (fabEdgeWork) fabEdgeWork.textContent = apt.edge_work || '-';
            if (fabFrameType) fabFrameType.textContent = apt.frame_type || '-';
            if (fabEngraving) fabEngraving.textContent = apt.engraving || '-';
            
            // Populate payment breakdown for fabrication
            // Calculate total from downpayment (which is 50%)
            let totalAmount = parseFloat(apt.estimate_price || apt.unit_price || 0);
            
            // If we have payment_data with downpayment, use that to calculate accurate total
            if (apt.payment_data && apt.payment_data.downpayment_amount) {
                totalAmount = parseFloat(apt.payment_data.downpayment_amount) * 2; // downpayment is 50%, so total = downpayment * 2
            }
            
            // Downpayment (50%) - read-only, already completed
            const fabDownpaymentAmount = document.getElementById('fab-downpayment-amount');
            const fabDownpaymentMethod = document.getElementById('fab-downpayment-method');
            const fabDownpaymentBadge = document.getElementById('fab-downpayment-status-badge');
            
            if (apt.payment_data) {
                if (fabDownpaymentAmount) fabDownpaymentAmount.value = apt.payment_data.downpayment_amount || (totalAmount * 0.5).toFixed(2);
                if (fabDownpaymentMethod) fabDownpaymentMethod.value = apt.payment_data.downpayment_method || '—';
                if (fabDownpaymentBadge) {
                    fabDownpaymentBadge.textContent = apt.payment_data.downpayment_status === 'Paid' ? 'Paid' : 'Completed';
                    fabDownpaymentBadge.style.backgroundColor = '#28a745';
                    fabDownpaymentBadge.style.color = '#fff';
                }
            }
            
            // Fabrication payment (40%) - editable
            const fabFabricationAmount = document.getElementById('fab-fabrication-amount');
            const fabFabricationMethod = document.getElementById('fab-fabrication-method');
            const fabFabricationStatus = document.getElementById('fab-fabrication-status');
            const fabFabricationBadge = document.getElementById('fab-fabrication-status-badge');
            const fabFabricationReceiptLink = document.getElementById('fab-fabrication-receipt-link');
            
            // Always calculate fabrication amount from total (40%)
            if (fabFabricationAmount) fabFabricationAmount.value = (totalAmount * 0.4).toFixed(2);
            
            if (apt.payment_data) {
                if (fabFabricationMethod) fabFabricationMethod.value = apt.payment_data.fabrication_method || '';
                if (fabFabricationStatus) fabFabricationStatus.value = apt.payment_data.fabrication_status || 'Pending';
                
                if (fabFabricationBadge) {
                    const status = apt.payment_data.fabrication_status || 'Pending';
                    fabFabricationBadge.textContent = status;
                    if (status === 'Paid') {
                        fabFabricationBadge.style.backgroundColor = '#28a745';
                        fabFabricationBadge.style.color = '#fff';
                    } else {
                        fabFabricationBadge.style.backgroundColor = '#ffc107';
                        fabFabricationBadge.style.color = '#000';
                    }
                }
                
                // Show receipt link if available
                if (apt.payment_data.fabrication_receipt_url && fabFabricationReceiptLink) {
                    fabFabricationReceiptLink.style.display = 'block';
                    const link = fabFabricationReceiptLink.querySelector('a');
                    if (link) link.href = apt.payment_data.fabrication_receipt_url;
                }
            }
            
            // Update badge when status changes
            if (fabFabricationStatus) {
                fabFabricationStatus.addEventListener('change', function() {
                    if (fabFabricationBadge) {
                        if (this.value === 'Paid') {
                            fabFabricationBadge.textContent = 'Paid';
                            fabFabricationBadge.style.backgroundColor = '#28a745';
                            fabFabricationBadge.style.color = '#fff';
                        } else {
                            fabFabricationBadge.textContent = 'Pending';
                            fabFabricationBadge.style.backgroundColor = '#ffc107';
                            fabFabricationBadge.style.color = '#000';
                        }
                    }
                });
            }
        }
    }
    
    // ======================
    // SAVE APPOINTMENT
    // ======================
    async function saveAppointment() {
        if (!currentAppointment) {
            console.error('saveAppointment: currentAppointment is null or undefined');
            showToast('Error: No appointment selected', 'error');
            return;
        }
        
        const appointmentId = currentAppointment.id || currentAppointment.appointment_id || currentAppointment.AppointmentID;
        if (!appointmentId) {
            console.error('saveAppointment: appointment ID not found in currentAppointment:', currentAppointment);
            showToast('Error: Appointment ID not found', 'error');
            return;
        }
        
        console.log('Saving appointment ID:', appointmentId);
        
        const formData = new FormData();
        formData.append('appointment_id', appointmentId);
        formData.append('client_name', document.getElementById('detail-client-name')?.value || '');
        formData.append('date', document.getElementById('detail-appointment-date')?.value || '');
        formData.append('time', document.getElementById('detail-appointment-time')?.value || '');
        formData.append('assigned_staff', document.getElementById('detail-assigned-staff')?.value || '');
        formData.append('status', document.getElementById('detail-status')?.value || '');
        formData.append('notes', document.getElementById('detail-notes')?.value || '');
        formData.append('service', currentAppointment.service || (appointmentType === 'ocular' ? 'Ocular Visit' : 'Installed'));
        formData.append('order_item_id', document.getElementById('detail-order-item-id')?.value || '');
        
        // Add ocular notes if ocular appointment
        if (appointmentType === 'ocular') {
            formData.append('ocular_notes', document.getElementById('detail-ocular-notes')?.value || '');
            formData.append('spec_width', document.getElementById('detail-spec-width')?.value || '');
            formData.append('spec_height', document.getElementById('detail-spec-height')?.value || '');
            formData.append('spec_unit', document.getElementById('detail-spec-unit')?.value || '');
            formData.append('spec_quantity', document.getElementById('detail-spec-quantity')?.value || '');
            
            // Add customization specifications (static fallback fields)
            formData.append('spec_shape', document.getElementById('detail-spec-shape')?.value || '');
            formData.append('spec_type', document.getElementById('detail-spec-type')?.value || '');
            formData.append('spec_thickness', document.getElementById('detail-spec-thickness')?.value || '');
            formData.append('spec_edge', document.getElementById('detail-spec-edge')?.value || '');
            formData.append('spec_frame', document.getElementById('detail-spec-frame')?.value || '');
            formData.append('spec_engraving', document.getElementById('detail-spec-engraving')?.value || '');
            
            // Add dynamic product-specific customization values
            const dynamicSpecs = {};
            
            console.log('=== COLLECTING CUSTOMIZATION DATA ===');
            console.log('Current adminCustomValues:', adminCustomValues);
            
            // First, copy all values from adminCustomValues (includes programmatically set values)
            Object.assign(dynamicSpecs, adminCustomValues);
            console.log('After copying adminCustomValues:', dynamicSpecs);
            
            // Then, override with current DOM values to ensure latest changes are captured
            document.querySelectorAll('.admin-dynamic-field').forEach(el => {
                const fieldId = el.getAttribute('data-field-id');
                if (fieldId) {
                    let value;
                    if (el.type === 'checkbox') {
                        value = el.checked ? 'Yes' : '';
                    } else if (el.tagName === 'SELECT') {
                        // Special handling for select dropdowns
                        value = el.value;
                        console.log(`Select dropdown ${fieldId}: selected index=${el.selectedIndex}, value="${value}", options count=${el.options.length}`);
                        // Ensure we have the actual selected value, not empty string
                        if (el.selectedIndex > 0 && el.options[el.selectedIndex]) {
                            value = el.options[el.selectedIndex].value;
                        }
                    } else {
                        value = el.value && el.value.trim() !== '' ? el.value : '';
                    }
                    
                    if (value !== '' && value !== null && value !== undefined) {
                        dynamicSpecs[fieldId] = value;
                    }
                    
                    console.log(`Processing field ${fieldId} (${el.tagName}${el.type ? '[' + el.type + ']' : ''}): "${value}"`);
                }
            });
            
            // Also capture dimensions separately to ensure they're included
            const widthValue = document.getElementById('detail-spec-width')?.value;
            const heightValue = document.getElementById('detail-spec-height')?.value;
            const unitValue = document.getElementById('detail-spec-unit')?.value;
            
            if (widthValue) {
                dynamicSpecs['width'] = widthValue;
                console.log('Added width:', widthValue);
            }
            if (heightValue) {
                dynamicSpecs['height'] = heightValue;
                console.log('Added height:', heightValue);
            }
            if (unitValue) {
                dynamicSpecs['unit'] = unitValue;
                console.log('Added unit:', unitValue);
            }
            
            console.log('Final dynamicSpecs to be sent:', dynamicSpecs);
            console.log('=== END CUSTOMIZATION DATA COLLECTION ===');
            
            if (Object.keys(dynamicSpecs).length > 0) {
                formData.append('dynamic_customization', JSON.stringify(dynamicSpecs));
                console.log('Appended dynamic_customization to FormData');
            } else {
                console.log('No dynamic customization data to send');
            }
            
            // Add payment breakdown data
            formData.append('downpayment_amount', document.getElementById('detail-downpayment-amount')?.value || '');
            formData.append('downpayment_method', document.getElementById('detail-downpayment-method')?.value || '');
            formData.append('downpayment_status', document.getElementById('detail-downpayment-status')?.value || 'Pending');
            
            // Add total amount to payment data
            const totalAmountValue = document.getElementById('detail-total-amount')?.value;
            if (totalAmountValue) {
                formData.append('total_amount', totalAmountValue);
                console.log('Sending total amount to backend:', totalAmountValue);
            }
            
            const receiptInput = document.getElementById('detail-payment-receipt');
            if (receiptInput && receiptInput.files && receiptInput.files[0]) {
                formData.append('payment_receipt', receiptInput.files[0]);
            }
            // Add site photos
            pendingSitePhotos.forEach((file, index) => {
                formData.append(`site_photos[${index}]`, file);
            });
        }
        
        // Add installation notes if installation appointment
        if (appointmentType === 'installation') {
            formData.append('installation_notes', document.getElementById('detail-installation-notes')?.value || '');
            
            // Get checklist values
            const checklist = {
                materials: document.getElementById('checklist-materials')?.checked || false,
                site_prepared: document.getElementById('checklist-site-prepared')?.checked || false,
                installation_completed: document.getElementById('checklist-installation-completed')?.checked || false,
                quality_check: document.getElementById('checklist-quality-check')?.checked || false
            };
            formData.append('checklist', JSON.stringify(checklist));
            
            // Add installation payment data (10% final payment)
            formData.append('installation_amount', document.getElementById('inst-installation-amount')?.value || '');
            formData.append('installation_method', document.getElementById('inst-installation-method')?.value || '');
            formData.append('installation_status', document.getElementById('inst-installation-status')?.value || 'Pending');
            
            const instReceiptInput = document.getElementById('inst-installation-receipt');
            if (instReceiptInput && instReceiptInput.files && instReceiptInput.files[0]) {
                formData.append('installation_receipt', instReceiptInput.files[0]);
            }
        }
        
        // Add fabrication notes and payment if fabrication appointment
        if (appointmentType === 'fabrication') {
            formData.append('fabrication_notes', document.getElementById('detail-fabrication-notes')?.value || '');
            
            // Add fabrication payment data
            formData.append('fabrication_amount', document.getElementById('fab-fabrication-amount')?.value || '');
            formData.append('fabrication_method', document.getElementById('fab-fabrication-method')?.value || '');
            formData.append('fabrication_status', document.getElementById('fab-fabrication-status')?.value || 'Pending');
            
            const fabReceiptInput = document.getElementById('fab-fabrication-receipt');
            if (fabReceiptInput && fabReceiptInput.files && fabReceiptInput.files[0]) {
                formData.append('fabrication_receipt', fabReceiptInput.files[0]);
            }
        }
        
        // Debug: Log FormData contents
        console.log('=== FormData being sent ===');
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                console.log(key + ': [File] ' + value.name);
            } else {
                console.log(key + ':', value);
            }
        }
        console.log('=== End FormData ===');
        
        try {
            const response = await fetch(updateAppointmentUrl, {
                method: 'POST',
                body: formData
            });
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            // Get the raw text first to see what's being returned
            const responseText = await response.text();
            console.log('Raw response:', responseText.substring(0, 500)); // First 500 chars
            
            // Try to parse as JSON
            let data;
            try {
                data = JSON.parse(responseText);
                console.log('Response data:', data);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Full response text:', responseText);
                showToast('Server error - check console for details', 'error');
                return;
            }
            
            if (data.success) {
                // Clear pending photos after successful save
                pendingSitePhotos = [];
                showToast('Appointment updated successfully', 'success');
                appointmentDetailsModal.classList.remove('active');
                loadAppointments();
                if (calendarView && calendarView.style.display !== 'none') {
                    renderCalendarView();
                }
            } else {
                showToast('Error updating appointment: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error saving appointment:', error);
            console.error('Error stack:', error.stack);
            showToast('Error saving appointment: ' + error.message, 'error');
        }
    }
    
    // ======================
    // MARK OCULAR COMPLETE
    // ======================
    async function markOcularComplete() {
        if (!currentAppointment || appointmentType !== 'ocular') return;
        
        const appointmentId = currentAppointment.id || currentAppointment.appointment_id || currentAppointment.AppointmentID;
        if (!appointmentId) {
            console.error('markOcularComplete: appointment ID not found');
            showToast('Error: Appointment ID not found', 'error');
            return;
        }
        
        const confirmed = await showConfirmationAsync('Mark this ocular appointment as complete?');
        if (!confirmed) return;
        
        try {
            const response = await fetch(updateAppointmentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    appointment_id: appointmentId,
                    service: currentAppointment.service || 'Ocular Visit',
                    status: 'Complete',
                    ocular_completed: true
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('Ocular appointment marked as complete', 'success');
                appointmentDetailsModal.classList.remove('active');
                loadAppointments();
                if (calendarView && calendarView.style.display !== 'none') {
                    renderCalendarView();
                }
            } else {
                showToast('Error: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error marking ocular complete:', error);
            showToast('Error marking ocular as complete', 'error');
        }
    }
    
    // ======================
    // CANCEL APPOINTMENT
    // ======================
    async function cancelAppointment() {
        if (!currentAppointment) return;
        
        const appointmentId = currentAppointment.id || currentAppointment.appointment_id || currentAppointment.AppointmentID;
        if (!appointmentId) {
            console.error('cancelAppointment: appointment ID not found');
            showToast('Error: Appointment ID not found', 'error');
            return;
        }
        
        const confirmed = await showConfirmationAsync('Are you sure you want to cancel this appointment?');
        if (!confirmed) return;
        
        try {
            const response = await fetch(deleteAppointmentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    appointment_id: appointmentId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('Appointment cancelled', 'success');
                appointmentDetailsModal.classList.remove('active');
                loadAppointments();
                if (calendarView && calendarView.style.display !== 'none') {
                    renderCalendarView();
                }
            } else {
                showToast('Error: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error cancelling appointment:', error);
            showToast('Error cancelling appointment', 'error');
        }
    }
    
    // ======================
    // RESCHEDULE DIALOG
    // ======================
    function showRescheduleDialog() {
        const newDate = prompt('Enter new date (YYYY-MM-DD):');
        const newTime = prompt('Enter new time (HH:MM):');
        
        if (newDate && newTime) {
            const dateInput = document.getElementById('detail-appointment-date');
            const timeInput = document.getElementById('detail-appointment-time');
            
            if (dateInput) dateInput.value = newDate;
            if (timeInput) timeInput.value = newTime;
            
            saveAppointment();
        }
    }
    
    // ======================
    // LOAD STAFF LIST
    // ======================
    async function loadStaffList() {
        try {
            const response = await fetch(getStaffListUrl);
            const data = await response.json();
            
            if (data.success && data.staff) {
                staffList = data.staff;
                
                // Populate staff filter dropdown
                if (staffFilter) {
                    staffFilter.innerHTML = '<option value="all">All Staff</option>' + 
                        staffList.map(staff => 
                            `<option value="${staff.id}">${staff.name}</option>`
                        ).join('');
                }
            }
        } catch (error) {
            console.error('Error loading staff list:', error);
        }
    }
    
    // ======================
    // CALENDAR VIEW
    // ======================
    function initializeCalendar() {
        // Calendar will be rendered when switching to calendar view
    }
    
    function switchView(view) {
        // Update toggle buttons
        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === view) {
                btn.classList.add('active');
            }
        });
        
        // Show/hide views
        if (view === 'list') {
            if (listView) listView.style.display = 'block';
            if (calendarView) calendarView.style.display = 'none';
            if (requestsView) requestsView.style.display = 'none';
        } else if (view === 'calendar') {
            if (listView) listView.style.display = 'none';
            if (calendarView) calendarView.style.display = 'block';
            if (requestsView) requestsView.style.display = 'none';
            renderCalendarView();
        } else if (view === 'requests') {
            if (listView) listView.style.display = 'none';
            if (calendarView) calendarView.style.display = 'none';
            if (requestsView) requestsView.style.display = 'block';
            loadInstallationRequests();
        }
    }

    // Load installation date-change requests and render them in the requests table
    async function loadInstallationRequests() {
        // Support both legacy requests container and the embedded installation requests container
        const tbodyReq = document.getElementById('installation-requests-table-body') || document.getElementById('requests-table-body');
        if (!tbodyReq) return;
        tbodyReq.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:12px;">Loading...</td></tr>';

        try {
            const resp = await fetch(getDateChangeRequestsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const text = await resp.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Non-JSON response from get_installation_date_change_requests:', text);
                throw new Error('Server returned non-JSON response');
            }
            if (!data.success) {
                tbodyReq.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:12px;">'+(data.message||'Failed to load requests')+'</td></tr>';
                return;
            }

            const requests = data.requests || [];
            if (requests.length === 0) {
                tbodyReq.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:12px;">No requests found</td></tr>';
                return;
            }

            // Render rows: Requested Date, Client, Order, Date & Time, Specs, Assigned Staff, Status, Actions
            tbodyReq.innerHTML = '';
            requests.forEach((r, idx) => {
                const tr = document.createElement('tr');

                const reqDate = r.requested_date || '-';

                // Client name: prefer enriched client_name, fallback to UserName or dash
                const client = r.client_name || r.UserName || '-';

                // Order display & link: prefer numeric order_id, then order_number
                let orderText = '-';
                let orderLink = baseUrl + 'admin-orders';
                if (r.order_id) {
                    orderText = r.order_number || ('GI' + String(r.order_id).padStart(3, '0'));
                    orderLink += '?order_id=' + encodeURIComponent(r.order_id);
                } else if (r.order_number) {
                    orderText = r.order_number;
                    orderLink += '?order_number=' + encodeURIComponent(r.order_number);
                }

                // Appointment date & time
                let dateTime = '-';
                if (r.appointment_date) {
                    try {
                        const d = new Date(r.appointment_date);
                        const dateStr = d.toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
                        const timeStr = r.appointment_time ? (new Date('1970-01-01T' + r.appointment_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })) : '';
                        dateTime = dateStr + (timeStr ? ' ' + timeStr : '');
                    } catch (e) {
                        dateTime = r.appointment_date + (r.appointment_time ? ' ' + r.appointment_time : '');
                    }
                }

                const productName = r.specs || '-'; // Backend returns product name in 'specs' field
                const assigned = r.assigned_staff || '-';
                const status = r.appointment_status || r.order_status || '-';

                const actions = `
                    <button class="btn-small open-request-btn" data-request-id="${r.id}">Open</button>
                `;

                tr.innerHTML = `
                    <td>${reqDate}</td>
                    <td>${client}</td>
                    <td><a href="${orderLink}" class="order-link">${orderText}</a></td>
                    <td>${dateTime}</td>
                    <td>${productName}</td>
                    <td>${assigned}</td>
                    <td>${status}</td>
                    <td>${actions}</td>
                `;

                tbodyReq.appendChild(tr);
            });

                // Attach Open button handlers to show request details modal
                document.querySelectorAll('.open-request-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const requestId = this.dataset.requestId;
                        if (!requestId) return;
                        openRequestModal(requestId);
                    });
                });
        } catch (err) {
            console.error('Error loading installation requests:', err);
            tbodyReq.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:12px;">Error loading requests</td></tr>';
        }
    }

        // Open request details modal and populate with data
        async function openRequestModal(requestId) {
            const modal = document.getElementById('requestDetailsModal');
            const modalContent = document.getElementById('request-details-content');
            const approveBtn = document.getElementById('request-approve-btn');
            const disapproveBtn = document.getElementById('request-disapprove-btn');
            const closeBtn = document.getElementById('closeRequestDetails');
            if (!modal || !modalContent) return;

            modalContent.innerHTML = '<p style="padding:12px;">Loading...</p>';
            modal.classList.add('active');

            try {
                const resp = await fetch(getDateChangeRequestsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const text = await resp.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Non-JSON response from get_installation_date_change_requests (modal):', text);
                    modalContent.innerHTML = '<p style="padding:12px;color:red;">Server error while loading request details.</p>';
                    return;
                }
                if (!data.success) {
                    modalContent.innerHTML = '<p style="padding:12px;color:red;">Failed to load request</p>';
                    return;
                }
                const requests = data.requests || [];
                const r = requests.find(x => String(x.id) === String(requestId));
                if (!r) {
                    modalContent.innerHTML = '<p style="padding:12px;">Request not found</p>';
                    return;
                }

                // Build details HTML: order, client, actual installation date, preferred date, specs, assigned staff, notes/status
                const actualDate = r.appointment_date ? (r.appointment_date + (r.appointment_time ? ' ' + r.appointment_time : '')) : (r.installation_date || '-');
                const preferred = r.requested_date || '-';
                const orderText = r.order_number || (r.order_id ? ('GI' + String(r.order_id).padStart(3, '0')) : '-');
                const client = r.client_name || r.UserName || '-';
                const specs = r.specs || '-';
                const assigned = r.assigned_staff || '-';
                const status = r.appointment_status || r.order_status || '-';

                modalContent.innerHTML = `
                    <div style="padding:12px;">
                        <p><strong>Order:</strong> ${orderText}</p>
                        <p><strong>Client:</strong> ${client}</p>
                        <p><strong>Actual installation date:</strong> ${actualDate}</p>
                        <p><strong>Preferred installation date:</strong> ${preferred}</p>
                        <p><strong>Specs:</strong> ${specs}</p>
                        <p><strong>Assigned Staff:</strong> ${assigned}</p>
                        <p><strong>Status:</strong> ${status}</p>
                    </div>
                `;

                // Wire approve/disapprove buttons
                if (approveBtn) {
                    approveBtn.onclick = async function() {
                        if (!confirm('Approve this installation date change request?')) return;
                        try {
                            const res = await fetch(baseUrl + 'AdminCon/process_installation_date_request', {
                                method: 'POST',
                                headers: { 
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: 'request_id=' + encodeURIComponent(requestId) + '&action=approve'
                            });
                            const j = await res.json();
                            if (j.success) {
                                showToast(j.message || 'Request approved', 'success');
                                modal.classList.remove('active');
                                // Refresh all views to show updated date
                                loadInstallationRequests();
                                loadAppointments();
                                if (calendarView && calendarView.style.display !== 'none') {
                                    renderCalendarView();
                                }
                            } else {
                                showToast(j.message || 'Failed to approve request', 'error');
                            }
                        } catch (e) {
                            console.error(e);
                            showToast('Error approving request', 'error');
                        }
                    };
                } else {
                    console.error('Approve button not found');
                }

                if (disapproveBtn) {
                    disapproveBtn.onclick = async function() {
                        // Show disapproval reason modal
                        showDisapprovalReasonModal(requestId, modal);
                    };
                } else {
                    console.error('Disapprove button not found');
                }

                closeBtn.onclick = function() {
                    modal.classList.remove('active');
                };

            } catch (err) {
                console.error('Error loading request details:', err);
                modalContent.innerHTML = '<p style="padding:12px;color:red;">Error loading request details</p>';
            }
        }

        // Show disapproval reason modal
        function showDisapprovalReasonModal(requestId, parentModal) {
            // Create or get disapproval modal
            let disapprovalModal = document.getElementById('disapproval-reason-modal');
            if (!disapprovalModal) {
                disapprovalModal = document.createElement('div');
                disapprovalModal.id = 'disapproval-reason-modal';
                disapprovalModal.className = 'popup-overlay';
                disapprovalModal.innerHTML = `
                    <div class="popup popup-medium" style="max-width: 500px;">
                        <span class="close-btn" id="close-disapproval-modal">&times;</span>
                        <h3 class="popup-title">Disapprove Installation Date Request</h3>
                        <div style="padding: 20px;">
                            <p style="margin-bottom: 15px;">Please provide a reason for disapproving this request. This will be sent to the customer.</p>
                            <label for="disapproval-reason" style="display: block; margin-bottom: 8px; font-weight: 600;">Reason for Disapproval:</label>
                            <textarea id="disapproval-reason" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; resize: vertical; font-family: inherit; box-sizing: border-box;" placeholder="Enter the reason for disapproval..."></textarea>
                        </div>
                        <div class="popup-actions" style="padding: 15px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee;">
                            <button id="cancel-disapproval-btn" class="btn-secondary" style="padding: 10px 20px; border-radius: 6px; cursor: pointer;">Cancel</button>
                            <button id="confirm-disapproval-btn" class="btn-danger" style="padding: 10px 20px; border-radius: 6px; cursor: pointer;">Confirm Disapproval</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(disapprovalModal);
            }

            // Clear previous reason
            const reasonTextarea = disapprovalModal.querySelector('#disapproval-reason');
            reasonTextarea.value = '';

            // Show modal
            disapprovalModal.classList.add('active');

            // Wire up buttons
            const closeBtn = disapprovalModal.querySelector('#close-disapproval-modal');
            const cancelBtn = disapprovalModal.querySelector('#cancel-disapproval-btn');
            const confirmBtn = disapprovalModal.querySelector('#confirm-disapproval-btn');

            const closeDisapprovalModal = () => {
                disapprovalModal.classList.remove('active');
            };

            closeBtn.onclick = closeDisapprovalModal;
            cancelBtn.onclick = closeDisapprovalModal;

            confirmBtn.onclick = async function() {
                const reason = reasonTextarea.value.trim();
                if (!reason) {
                    showToast('Please provide a reason for disapproval.', 'warning');
                    reasonTextarea.focus();
                    return;
                }

                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Processing...';

                try {
                    const res = await fetch(baseUrl + 'AdminCon/process_installation_date_request', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'request_id=' + encodeURIComponent(requestId) + '&action=disapprove&reason=' + encodeURIComponent(reason)
                    });
                    const j = await res.json();
                    showToast(j.message || 'Request disapproved', j.success ? 'success' : 'error');
                    closeDisapprovalModal();
                    if (parentModal) parentModal.classList.remove('active');
                    // Refresh all views
                    loadInstallationRequests();
                    loadAppointments();
                    if (calendarView && calendarView.style.display !== 'none') {
                        renderCalendarView();
                    }
                } catch (e) {
                    console.error(e);
                    showToast('Error disapproving request', 'error');
                } finally {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Confirm Disapproval';
                }
            };
        }
    
    async function renderCalendarView() {
        const calendarBody = document.getElementById('calendar-body');
        const calendarMonthYear = document.getElementById('calendar-month-year');
        
        if (!calendarBody) return;
        
        // Update month/year header
        if (calendarMonthYear) {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                              'July', 'August', 'September', 'October', 'November', 'December'];
            calendarMonthYear.textContent = monthNames[currentCalendarMonth.getMonth()] + ' ' + currentCalendarMonth.getFullYear();
        }
        
        // Load appointments for the month
        try {
            const params = new URLSearchParams({
                appointment_type: appointmentType || 'ocular',
                month: currentCalendarMonth.getMonth() + 1,
                year: currentCalendarMonth.getFullYear()
            });
            
            const response = await fetch(getAppointmentsUrl + '?' + params.toString());
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Ensure appointments is always an array
            let appointments = [];
            if (data && data.success && Array.isArray(data.appointments)) {
                appointments = data.appointments;
            } else if (data && Array.isArray(data)) {
                // Handle case where API returns array directly
                appointments = data;
            }
            
            // Limit appointments to prevent memory issues
            if (appointments.length > 10000) {
                console.warn('Too many appointments, limiting to 10000');
                appointments = appointments.slice(0, 10000);
            }
            
            renderCalendarGrid(calendarBody, appointments);
        } catch (error) {
            console.error('Error loading calendar appointments:', error);
            if (calendarBody) {
                calendarBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Error loading calendar: ' + (error.message || 'Unknown error') + '</td></tr>';
            }
        }
    }
    
    function renderCalendarGrid(calendarBody, appointments) {
        if (!calendarBody) {
            console.error('Calendar body element not found');
            return;
        }
        
        const year = currentCalendarMonth.getFullYear();
        const month = currentCalendarMonth.getMonth();
        
        // Validate month and year
        if (isNaN(year) || isNaN(month) || month < 0 || month > 11) {
            console.error('Invalid month or year:', year, month);
            calendarBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Invalid date</td></tr>';
            return;
        }
        
        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        // Validate days calculation
        if (isNaN(firstDay) || isNaN(daysInMonth) || daysInMonth < 28 || daysInMonth > 31) {
            console.error('Invalid calendar calculation:', firstDay, daysInMonth);
            calendarBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Error calculating calendar</td></tr>';
            return;
        }
        
        // Group appointments by date
        const appointmentsByDate = {};
        if (Array.isArray(appointments)) {
            appointments.forEach(apt => {
                if (apt && apt.appointment_date) {
                    try {
                        const date = new Date(apt.appointment_date);
                        // Check if date is valid
                        if (!isNaN(date.getTime())) {
                            // Only include appointments from the current month being displayed
                            if (date.getMonth() === month && date.getFullYear() === year) {
                                const dateKey = date.getDate();
                                if (dateKey >= 1 && dateKey <= 31) {
                                    if (!appointmentsByDate[dateKey]) {
                                        appointmentsByDate[dateKey] = [];
                                    }
                                    appointmentsByDate[dateKey].push(apt);
                                }
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing appointment date:', apt.appointment_date, e);
                    }
                }
            });
        }
        
        // Build calendar grid
        const htmlParts = [];
        let day = 1;
        
        // Calculate total cells needed (first day offset + days in month)
        const totalCells = firstDay + daysInMonth;
        const weeksNeeded = Math.ceil(totalCells / 7);
        
        for (let week = 0; week < weeksNeeded && week < 6; week++) {
            htmlParts.push('<tr>');
            for (let dayOfWeek = 0; dayOfWeek < 7; dayOfWeek++) {
                if (week === 0 && dayOfWeek < firstDay) {
                    // Empty cells before first day of month
                    htmlParts.push('<td class="calendar-day empty"></td>');
                } else if (day > daysInMonth) {
                    // Empty cells after last day of month
                    htmlParts.push('<td class="calendar-day empty"></td>');
                } else {
                    // Day cell with appointments
                    const dayAppointments = appointmentsByDate[day] || [];
                    const appointmentCount = Math.min(dayAppointments.length, 999); // Limit to prevent overflow
                    const appointmentBadge = appointmentCount > 0 ? `<span class="appointment-badge">${appointmentCount > 99 ? '99+' : appointmentCount}</span>` : '';
                    
                    htmlParts.push(
                        `<td class="calendar-day ${appointmentCount > 0 ? 'has-appointments' : ''}" data-day="${day}">`,
                        `<div class="day-number">${day}</div>`,
                        appointmentBadge,
                        '</td>'
                    );
                    day++;
                }
            }
            htmlParts.push('</tr>');
            
            // Safety check: if we've processed all days, break
            if (day > daysInMonth) break;
        }
        
        // Use join instead of concatenation to avoid string length issues
        try {
            const html = htmlParts.join('');
            // Safety check: limit HTML size to prevent "Invalid string length" error
            if (html.length > 1000000) { // 1MB limit
                console.error('Calendar HTML too large:', html.length, 'characters');
                calendarBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Too many appointments to display. Please use filters.</td></tr>';
                return;
            }
            calendarBody.innerHTML = html;
        } catch (e) {
            console.error('Error setting calendar HTML:', e);
            calendarBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Error rendering calendar</td></tr>';
        }
        
        // Add click handlers to calendar days
        calendarBody.querySelectorAll('.calendar-day.has-appointments').forEach(cell => {
            cell.addEventListener('click', function() {
                const dayNum = parseInt(this.dataset.day);
                showDayAppointments(dayNum, appointmentsByDate[dayNum] || []);
            });
        });
    }
    
    function showDayAppointments(day, appointments) {
        // Show a small table of appointments for the selected day
        if (!appointments || appointments.length === 0) return;

        // Build table rows with sensible fallbacks for common field names
        const esc = s => String(s || '').replace(/[&<>\"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

        const rows = appointments.map(apt => {
            const time = apt.appointment_time ? new Date('2000-01-01T' + apt.appointment_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : '';
            const orderId = apt.order_number || apt.OrderNumber || apt.orderId || apt.OrderID || apt.order_id || '';
            const client = apt.client_name || apt.clientName || apt.customer_name || apt.customer || 'N/A';
            const service = apt.service_type || apt.service || '';
            const specsParts = [];
            if (apt.width) specsParts.push(apt.width + (apt.unit ? apt.unit : ''));
            if (apt.height) specsParts.push(apt.height + (apt.unit ? apt.unit : ''));
            if (apt.quantity) specsParts.push('Qty: ' + apt.quantity);
            const specs = specsParts.join(' • ');

            return `<tr>` +
                `<td class="dap-time">${esc(time)}</td>` +
                `<td class="dap-order">${esc(orderId)}</td>` +
                `<td class="dap-client">${esc(client)}</td>` +
                `<td class="dap-service">${esc(service)}</td>` +
                `<td class="dap-specs">${esc(specs)}</td>` +
            `</tr>`;
        }).join('');

        const table = `
            <table class="dap-table">
                <thead><tr><th>Time</th><th>Order</th><th>Client</th><th>Service</th><th>Specs</th></tr></thead>
                <tbody>${rows}</tbody>
            </table>
        `;

        const popupId = 'day-appointments-popup';
        const existing = document.getElementById(popupId);
        if (existing) existing.remove();

        const popup = document.createElement('div');
        popup.id = popupId;
        popup.className = 'day-appointments-popup';
        popup.innerHTML = `
            <div class="dap-header">Appointments for ${day}</div>
            <div class="dap-body">${table}</div>
            <div class="dap-actions"><button class="btn-secondary dap-close">Close</button></div>
        `;

        document.body.appendChild(popup);
        setTimeout(() => popup.classList.add('visible'), 10);
        popup.querySelectorAll('.dap-close').forEach(btn => btn.addEventListener('click', () => popup.remove()));
        // Auto-dismiss after 10s
        setTimeout(() => { if (document.body.contains(popup)) popup.remove(); }, 10000);
    }
    
    // ======================
    // PHOTO UPLOAD
    // ======================
    let pendingSitePhotos = []; // Store files to be uploaded when saving
    
    function handlePhotoUpload(files) {
        if (!files || files.length === 0) return;
        
        const gallery = document.getElementById('site-photos-gallery');
        if (!gallery) return;
        
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                // Add to pending photos array
                pendingSitePhotos.push(file);
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const photoContainer = document.createElement('div');
                    photoContainer.style.position = 'relative';
                    photoContainer.style.display = 'inline-block';
                    photoContainer.style.margin = '5px';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '4px';
                    img.style.border = '2px solid #ddd';
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '×';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '-5px';
                    removeBtn.style.right = '-5px';
                    removeBtn.style.background = 'red';
                    removeBtn.style.color = 'white';
                    removeBtn.style.border = 'none';
                    removeBtn.style.borderRadius = '50%';
                    removeBtn.style.width = '20px';
                    removeBtn.style.height = '20px';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.fontSize = '14px';
                    removeBtn.style.lineHeight = '1';
                    removeBtn.onclick = function() {
                        // Remove from pending photos
                        const index = pendingSitePhotos.indexOf(file);
                        if (index > -1) {
                            pendingSitePhotos.splice(index, 1);
                        }
                        photoContainer.remove();
                    };
                    
                    photoContainer.appendChild(img);
                    photoContainer.appendChild(removeBtn);
                    gallery.appendChild(photoContainer);
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // ======================
    // LOAD SITE PHOTOS
    // ======================
    async function loadSitePhotos(appointmentId) {
        const gallery = document.getElementById('site-photos-gallery');
        if (!gallery) return;
        
        gallery.innerHTML = ''; // Clear existing photos
        
        try {
            const response = await fetch(`${baseUrl}AdminCon/get_site_photos_ajax?appointment_id=${appointmentId}`);
            const data = await response.json();
            
            if (data.success && data.photos && data.photos.length > 0) {
                data.photos.forEach(photo => {
                    const photoContainer = document.createElement('div');
                    photoContainer.style.position = 'relative';
                    photoContainer.style.display = 'inline-block';
                    photoContainer.style.margin = '5px';
                    
                    const img = document.createElement('img');
                    img.src = photo.url;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '4px';
                    img.style.border = '2px solid #ddd';
                    img.onclick = function() {
                        window.open(photo.url, '_blank');
                    };
                    img.style.cursor = 'pointer';
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '×';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '-5px';
                    removeBtn.style.right = '-5px';
                    removeBtn.style.background = 'red';
                    removeBtn.style.color = 'white';
                    removeBtn.style.border = 'none';
                    removeBtn.style.borderRadius = '50%';
                    removeBtn.style.width = '20px';
                    removeBtn.style.height = '20px';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.fontSize = '14px';
                    removeBtn.style.lineHeight = '1';
                    removeBtn.onclick = async function(e) {
                        e.stopPropagation();
                        if (confirm('Delete this photo?')) {
                            try {
                                const deleteResponse = await fetch(`${baseUrl}AdminCon/delete_site_photo`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        appointment_id: appointmentId,
                                        photo_path: photo.path
                                    })
                                });
                                const deleteData = await deleteResponse.json();
                                if (deleteData.success) {
                                    photoContainer.remove();
                                } else {
                                    showToast('Error deleting photo: ' + (deleteData.message || 'Unknown error'), 'error');
                                }
                            } catch (error) {
                                console.error('Error deleting photo:', error);
                                showToast('Error deleting photo', 'error');
                            }
                        }
                    };
                    
                    photoContainer.appendChild(img);
                    photoContainer.appendChild(removeBtn);
                    gallery.appendChild(photoContainer);
                });
            }
        } catch (error) {
            console.error('Error loading site photos:', error);
        }
    }
    
    // ======================
    // LOAD INSTALLATION CHECKLIST
    // ======================
    async function loadInstallationChecklist(appointmentId) {
        // This would load checklist from server
        // Placeholder for now
    }
    
    // ======================
    // CLEAR FILTERS
    // ======================
    function clearFilters() {
        if (statusFilter) statusFilter.value = 'all';
        if (dateFilter) dateFilter.value = '';
        if (clientSearch) clientSearch.value = '';
        if (staffFilter) staffFilter.value = 'all';
        if (ocularCompletedFilter) ocularCompletedFilter.value = 'all';
    }
    
    // ======================
    // UPDATE PAGINATION
    // ======================
    function updatePagination(total, page, totalPagesParam) {
        // ensure outer-scoped totalPages is updated so goToPage() validates correctly
        totalPages = totalPagesParam || 1;
        const paginationInfo = document.getElementById('pagination-info');
        const paginationControls = document.getElementById('pagination-controls');
        
        if (paginationInfo) {
            paginationInfo.textContent = `Showing ${((page - 1) * itemsPerPage) + 1} to ${Math.min(page * itemsPerPage, total)} of ${total} appointments`;
        }
        
        if (paginationControls) {
            let html = '';
            
            // Previous button
            html += `<button class="pagination-btn" ${page <= 1 ? 'disabled' : ''} onclick="goToPage(${page - 1})">Previous</button>`;
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= page - 2 && i <= page + 2)) {
                    html += `<button class="pagination-btn ${i === page ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
                } else if (i === page - 3 || i === page + 3) {
                    html += `<span class="pagination-ellipsis">...</span>`;
                }
            }
            
            // Next button
            html += `<button class="pagination-btn" ${page >= totalPages ? 'disabled' : ''} onclick="goToPage(${page + 1})">Next</button>`;
            
            paginationControls.innerHTML = html;
        }
    }
    
    // ======================
    // GO TO PAGE
    // ======================
    window.goToPage = function(page) {
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            loadAppointments();
        }
    };
    
    // ======================
    // UPDATE FOUND TEXT
    // ======================
    function updateFoundText(count) {
        if (foundText) {
            foundText.textContent = `(${count} found)`;
        }
    }
    
    // ======================
    // QUOTATION HANDLERS
    // ======================
    function setupQuotationHandlers() {
        const createQuotationBtn = document.getElementById('create-quotation-btn');
        const sendQuotationBtn = document.getElementById('send-quotation-btn');
        const proceedFabricationBtn = document.getElementById('proceed-fabrication-btn');
        
        if (createQuotationBtn) {
            createQuotationBtn.addEventListener('click', handleCreateQuotation);
        }
        
        if (sendQuotationBtn) {
            sendQuotationBtn.addEventListener('click', handleSendQuotation);
        }
        
        if (proceedFabricationBtn) {
            proceedFabricationBtn.addEventListener('click', handleProceedFabrication);
        }
    }
    
    async function handleCreateQuotation() {
        if (!currentAppointment) {
            showToast('No appointment selected', 'warning');
            return;
        }
        
        const totalAmount = document.getElementById('quotation-total-amount')?.value;
        const notes = document.getElementById('quotation-notes')?.value || '';
        const expiryDate = document.getElementById('quotation-expiry-date')?.value || null;
        
        if (!totalAmount || parseFloat(totalAmount) <= 0) {
            showToast('Please enter a valid total amount', 'warning');
            return;
        }
        
        const appointmentId = currentAppointment.id || currentAppointment.appointment_id || currentAppointment.AppointmentID;
        if (!appointmentId) {
            console.error('createQuotation: appointment ID not found');
            showToast('Error: Appointment ID not found', 'error');
            return;
        }
        
        const createBtn = document.getElementById('create-quotation-btn');
        createBtn.disabled = true;
        createBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        
        try {
            const formData = new FormData();
            formData.append('appointment_id', appointmentId);
            formData.append('total_amount', totalAmount);
            formData.append('notes', notes);
            if (expiryDate) {
                formData.append('expiry_date', expiryDate);
            }
            
            const response = await fetch(createQuotationUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showQuotationMessage('Quotation created successfully!', 'success');
                updateQuotationStatus('Quotation #' + data.quotation_number + ' created', data.quotation_id);
                document.getElementById('send-quotation-btn').style.display = 'block';
                createBtn.style.display = 'none';
            } else {
                showQuotationMessage('Error: ' + (data.message || 'Failed to create quotation'), 'error');
                createBtn.disabled = false;
                createBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Create Quotation';
            }
        } catch (error) {
            console.error('Error creating quotation:', error);
            showQuotationMessage('Error: ' + error.message, 'error');
            createBtn.disabled = false;
            createBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Create Quotation';
        }
    }
    
    async function handleSendQuotation() {
        if (!currentAppointment) {
            showToast('No appointment selected', 'warning');
            return;
        }
        
        const quotationId = document.getElementById('quotation-status')?.dataset?.quotationId;
        if (!quotationId) {
            showToast('Quotation not found. Please create quotation first.', 'warning');
            return;
        }
        
        if (!confirm('Send quotation via email to customer?')) {
            return;
        }
        
        const appointmentId = currentAppointment.id || currentAppointment.appointment_id || currentAppointment.AppointmentID;
        if (!appointmentId) {
            console.error('sendQuotation: appointment ID not found');
            showToast('Error: Appointment ID not found', 'error');
            return;
        }
        
        const sendBtn = document.getElementById('send-quotation-btn');
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        try {
            const formData = new FormData();
            formData.append('quotation_id', quotationId);
            formData.append('appointment_id', appointmentId);
            
            const response = await fetch(sendQuotationUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showQuotationMessage('Quotation sent successfully to customer!', 'success');
                document.getElementById('proceed-fabrication-btn').style.display = 'block';
                sendBtn.style.display = 'none';
            } else {
                showQuotationMessage('Error: ' + (data.message || 'Failed to send quotation'), 'error');
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Quotation via Email';
            }
        } catch (error) {
            console.error('Error sending quotation:', error);
            showQuotationMessage('Error: ' + error.message, 'error');
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Quotation via Email';
        }
    }
    
    async function handleProceedFabrication() {
        if (!currentAppointment) {
            showToast('No appointment selected', 'warning');
            return;
        }
        
        const quotationId = document.getElementById('quotation-status')?.dataset?.quotationId;
        const orderId = currentAppointment.order_id;
        
        if (!confirm('Proceed order to fabrication? This will move the order to the fabrication queue.')) {
            return;
        }
        
        const proceedBtn = document.getElementById('proceed-fabrication-btn');
        proceedBtn.disabled = true;
        proceedBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const formData = new FormData();
            formData.append('order_id', orderId);
            if (quotationId) {
                formData.append('quotation_id', quotationId);
            }
            
            const response = await fetch(proceedFabricationUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showQuotationMessage('Order moved to fabrication successfully!', 'success');
                proceedBtn.style.display = 'none';
                setTimeout(() => {
                    loadAppointments();
                }, 1000);
            } else {
                showQuotationMessage('Error: ' + (data.message || 'Failed to proceed to fabrication'), 'error');
                proceedBtn.disabled = false;
                proceedBtn.innerHTML = '<i class="fas fa-cog"></i> Proceed to Fabrication';
            }
        } catch (error) {
            console.error('Error proceeding to fabrication:', error);
            showQuotationMessage('Error: ' + error.message, 'error');
            proceedBtn.disabled = false;
            proceedBtn.innerHTML = '<i class="fas fa-cog"></i> Proceed to Fabrication';
        }
    }
    
    function updateQuotationStatus(message, quotationId) {
        const statusDiv = document.getElementById('quotation-status');
        const statusText = document.getElementById('quotation-status-text');
        if (statusDiv && statusText) {
            statusText.textContent = message;
            if (quotationId) {
                statusDiv.dataset.quotationId = quotationId;
            }
            statusDiv.style.background = '#d4edda';
            statusDiv.style.color = '#155724';
        }
    }
    
    function showQuotationMessage(message, type) {
        const messageDiv = document.getElementById('quotation-message');
        if (messageDiv) {
            messageDiv.style.display = 'block';
            messageDiv.style.padding = '10px';
            messageDiv.style.borderRadius = '5px';
            messageDiv.style.marginTop = '10px';
            
            if (type === 'success') {
                messageDiv.style.background = '#d4edda';
                messageDiv.style.color = '#155724';
                messageDiv.style.border = '1px solid #c3e6cb';
            } else {
                messageDiv.style.background = '#f8d7da';
                messageDiv.style.color = '#721c24';
                messageDiv.style.border = '1px solid #f5c6cb';
            }
            
            messageDiv.textContent = message;
            
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
    }
    
});
