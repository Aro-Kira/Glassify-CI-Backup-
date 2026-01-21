// Appointment Management Module
// Handles Ocular and Installation Appointments

document.addEventListener('DOMContentLoaded', function() {
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
    initializeCalendar();
    
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
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">No appointments found</td></tr>';
            return;
        }
        
        tbody.innerHTML = appointments.map((apt, index) => {
            const rowNum = (currentPage - 1) * itemsPerPage + index + 1;
            const date = apt.appointment_date ? new Date(apt.appointment_date).toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' }) : '-';
            const time = apt.appointment_time ? new Date('2000-01-01T' + apt.appointment_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : '-';
            const statusBadge = getStatusBadge(apt.status);
            const priceValue = apt.unit_price ?? apt.estimate_price ?? '';
            const specs = [
                apt.dimensions ? `Size: ${apt.dimensions}` : null,
                apt.quantity ? `Qty: ${apt.quantity}` : null,
                priceValue !== '' ? `Price: ₱${Number(priceValue).toLocaleString('en-PH', { minimumFractionDigits: 2 })}` : null
            ].filter(Boolean).join('<br>');
            
            return `
                <tr>
                    <td>${rowNum}</td>
                    <td>${apt.client_name || apt.client || '-'}</td>
                    <td><a href="${baseUrl}admin-orders?order_id=${apt.order_id}" class="order-link">${apt.order_number || apt.order_id || '-'}</a></td>
                    <td>${date} ${time}</td>
                    <td>${specs || '-'}</td>
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
            'Complete': '<span class="badge badge-success">Complete</span>',
            'Cancelled': '<span class="badge badge-danger">Cancelled</span>'
        };
        return badges[status] || '<span class="badge">' + status + '</span>';
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
                alert('Error loading appointment details: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading appointment details:', error);
            alert('Error loading appointment details');
        }
    };
    
    // ======================
    // POPULATE APPOINTMENT MODAL
    // ======================
    function populateAppointmentModal(apt) {
        // Clear pending photos when opening modal
        pendingSitePhotos = [];
        
        // Order information
        const orderLink = document.getElementById('detail-order-link');
        if (orderLink) {
            orderLink.href = baseUrl + 'admin-orders?order_id=' + apt.order_id;
            orderLink.textContent = apt.order_id || '-';
        }
        
        // Client information
        const clientName = document.getElementById('detail-client-name');
        if (clientName) {
            clientName.value = apt.client || apt.client_name || '';
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
        const specPrice = document.getElementById('detail-spec-price');
        if (specPrice) {
            const priceValue = apt.unit_price ?? apt.estimate_price ?? '';
            specPrice.value = priceValue !== null ? priceValue : '';
        }
        const specQuantity = document.getElementById('detail-spec-quantity');
        if (specQuantity) {
            specQuantity.value = apt.quantity || '';
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
            
            // Load checklist if available
            loadInstallationChecklist(apt.id);
        }
    }
    
    // ======================
    // SAVE APPOINTMENT
    // ======================
    async function saveAppointment() {
        if (!currentAppointment) return;
        const formData = new FormData();
        formData.append('appointment_id', currentAppointment.id);
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
            formData.append('spec_price', document.getElementById('detail-spec-price')?.value || '');
            formData.append('spec_quantity', document.getElementById('detail-spec-quantity')?.value || '');
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
        }
        
        try {
            const response = await fetch(updateAppointmentUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Clear pending photos after successful save
                pendingSitePhotos = [];
                alert('Appointment updated successfully');
                appointmentDetailsModal.classList.remove('active');
                loadAppointments();
                if (calendarView && calendarView.style.display !== 'none') {
                    renderCalendarView();
                }
            } else {
                alert('Error updating appointment: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error saving appointment:', error);
            alert('Error saving appointment');
        }
    }
    
    // ======================
    // MARK OCULAR COMPLETE
    // ======================
    async function markOcularComplete() {
        if (!currentAppointment || appointmentType !== 'ocular') return;
        
        if (!confirm('Mark this ocular appointment as complete?')) return;
        
        try {
            const response = await fetch(updateAppointmentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    appointment_id: currentAppointment.id,
                    service: currentAppointment.service || 'Ocular Visit',
                    status: 'Complete',
                    ocular_completed: true
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Ocular appointment marked as complete');
                appointmentDetailsModal.classList.remove('active');
                loadAppointments();
                if (calendarView && calendarView.style.display !== 'none') {
                    renderCalendarView();
                }
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error marking ocular complete:', error);
            alert('Error marking ocular as complete');
        }
    }
    
    // ======================
    // CANCEL APPOINTMENT
    // ======================
    async function cancelAppointment() {
        if (!currentAppointment) return;
        
        if (!confirm('Are you sure you want to cancel this appointment?')) return;
        
        try {
            const response = await fetch(deleteAppointmentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    appointment_id: currentAppointment.id
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Appointment cancelled');
                appointmentDetailsModal.classList.remove('active');
                loadAppointments();
                if (calendarView && calendarView.style.display !== 'none') {
                    renderCalendarView();
                }
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error cancelling appointment:', error);
            alert('Error cancelling appointment');
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
        } else {
            if (listView) listView.style.display = 'none';
            if (calendarView) calendarView.style.display = 'block';
            renderCalendarView();
        }
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
        // This could open a modal or sidebar showing appointments for that day
        if (appointments.length > 0) {
            const appointmentList = appointments.map(apt => {
                const time = apt.appointment_time ? new Date('2000-01-01T' + apt.appointment_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : '';
                return `${time} - ${apt.client_name || 'N/A'}`;
            }).join('\n');
            
            alert(`Appointments for ${day}:\n\n${appointmentList}`);
        }
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
                                    alert('Error deleting photo: ' + (deleteData.message || 'Unknown error'));
                                }
                            } catch (error) {
                                console.error('Error deleting photo:', error);
                                alert('Error deleting photo');
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
    function updatePagination(total, page, totalPages) {
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
});
