<!-- Appointment Section -->
<section class="appointment-section-main">
    <h1 class="page-title">Appointment</h1>

    <!-- Filters -->
    <div class="controls-container">
        <input type="date" class="filter-date" id="filter-date">
        <select class="filter-status" id="filter-status">
            <option value="all">All Statuses</option>
            <option value="In Progress">In Progress</option>
            <option value="Complete">Complete</option>
            <option value="Cancelled">Cancelled</option>
        </select>
        <select class="filter-service" id="filter-service">
            <option value="all">All Services</option>
            <option value="Order Placed">Order Placed</option>
            <option value="Ocular Visit">Ocular Visit</option>
            <option value="In Fabrication">In Fabrication</option>
            <option value="Installed">Installed</option>
            <option value="Completed">Completed</option>
        </select>
        <input type="text" placeholder="Search by client name..." class="filter-search" id="filter-search">
        <button class="apply-btn" onclick="applyFilters()">Apply</button>
        <button class="clear-btn" onclick="clearFilters()">Clear</button>
    </div>

    <!-- Progress Steps -->
<div class="progress-steps">
    <div class="step">
        <img src="<?php echo base_url('assets/images/img_admin/checkout.png'); ?>" alt="Order Placed">
        <p>Order Placed</p>
        <span class="square blue"></span>
    </div>
    <img src="<?php echo base_url('assets/images/img_admin/double-arrow.svg'); ?>" alt="arrow" class="arrow">
    <div class="step">
        <img src="<?php echo base_url('assets/images/img_admin/ocular-visit.png'); ?>" alt="Ocular Visit">
        <p>Ocular Visit</p>
        <span class="square orange"></span>
    </div>
    <img src="<?php echo base_url('assets/images/img_admin/double-arrow.svg'); ?>" alt="arrow" class="arrow">
    <div class="step">
        <img src="<?php echo base_url('assets/images/img_admin/in-fabrication.png'); ?>" alt="In Fabrication">
        <p>In Fabrication</p>
        <span class="square purple"></span>
    </div>
    <img src="<?php echo base_url('assets/images/img_admin/double-arrow.svg'); ?>" alt="arrow" class="arrow">
    <div class="step">
        <img src="<?php echo base_url('assets/images/img_admin/installed.png'); ?>" alt="Installed">
        <p>Installed</p>
        <span class="square yellow"></span>
    </div>
    <img src="<?php echo base_url('assets/images/img_admin/double-arrow.svg'); ?>" alt="arrow" class="arrow">
    <div class="step">
        <img src="<?php echo base_url('assets/images/img_admin/completed.png'); ?>" alt="Completed">
        <p>Completed</p>
        <span class="square green"></span>
    </div>
</div>



    <!-- Appointments Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Date & Time</th>
                    <th>Assigned Staff</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="appointments-tbody">
                <!-- Dynamic content will be loaded here -->
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">Loading appointments...</td>
                </tr>
            </tbody>

        </table>
    </div> <!-- closes table-container -->
    <!-- Pagination -->
    <div class="pagination">
        <span id="pagination-info">Loading...</span>
        <div class="pagination-controls">
            <button class="page-btn-pagination prev">&lt;</button>
            <span class="page-number active">1</span>
            <button class="page-btn-pagination next">&gt;</button>
        </div>
    </div>
</section> <!-- closes appointment-section-main -->

<!-- Calendar Section -->
<section class="calendar-container">
    <div class="calendar-header">
        <h3 id="calendar-month-year"></h3>
        <div class="calendar-controls">
            <button class="today-btn" onclick="goToToday()">Today</button>
            <button class="page-btn" onclick="prevMonth()">❮</button>
            <button class="page-btn" onclick="nextMonth()">❯</button>
        </div>
    </div>

    <table class="calendar">
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
            <!-- Days will be injected by JavaScript -->
        </tbody>
    </table>
</section>
</main>
</div> <!-- closes container -->

<!-- Overlay & Popup -->
<div class="overlay" id="editProgressPopupOverlay">
    <div class="popup">
        <div class="popup-header">
            <h2>Project Progress</h2>
            <span class="close-btn" onclick="closePopup()">&times;</span>
        </div>
        <div class="popup-content">
            <h3>Project: <input type="text" id="edit-project-name" readonly></h3>
            <form id="edit-appointment-form">
                <input type="hidden" id="edit-appointment-id">
                
                <label>Client</label>
                <input type="text" id="edit-client-name" required>

                <label>Service</label>
                <select id="edit-service" required>
                    <option value="Order Placed">Order Placed</option>
                    <option value="Ocular Visit">Ocular Visit</option>
                    <option value="In Fabrication">In Fabrication</option>
                    <option value="Installed">Installed</option>
                    <option value="Completed">Completed</option>
                </select>

                <label>Date</label>
                <input type="date" id="edit-date" required>

                <label>Time</label>
                <input type="time" id="edit-time">

                <label>Assigned Staff</label>
                <input type="text" id="edit-assigned-staff" placeholder="Enter staff name">

                <label>Status</label>
                <select id="edit-status" required>
                    <option value="In Progress">In Progress</option>
                    <option value="Complete">Complete</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

                <label>Notes</label>
                <textarea id="edit-notes" placeholder="empty..."></textarea>
            </form>

            <div class="btn-group">
                <button type="button" class="save-btn" onclick="saveAppointmentChanges()">Save Changes</button>
                <button type="button" class="delete-btn" onclick="deleteAppointment()">Delete Project</button>
            </div>

            <!-- Cancel button below -->
            <div class="cancel-container">
                <button type="button" class="cancel-btn" onclick="closePopup()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Overlay & Popup -->
<div class="overlay" id="addAppointmentPopupOverlay">
    <div class="popup">
        <div class="popup-header">
            <h2>Add Project Progress</h2>
            <span class="close-btn" onclick="closePopup()">&times;</span>
        </div>
        <div class="popup-content">
            <h3>Project: <input type="text" placeholder="Enter project name"></h3>
            <form>
                <label>Client</label>
                <input type="text" placeholder="Enter client name">

                <label>Service</label>
                <select>
                    <option selected disabled>Select</option>
                    <option>Order Placed</option>
                    <option>Ocular Visit</option>
                    <option>In Fabrication</option>
                    <option>Installed</option>
                    <option>Completed</option>
                </select>

                <label>Date</label>
                <input type="date">

                <label>Assigned Staff</label>
                <input type="text" placeholder="Enter staff name">

                <label>Status</label>
                <select>
                    <option selected disabled>Select</option>
                    <option>Completed</option>
                    <option>In Progress</option>
                    <option>Cancelled</option>
                </select>

                <label>Notes</label>
                <textarea placeholder="empty..."></textarea>

                <div class="btn-group">
                    <button type="button" class="save-btn">Add Progress</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Calendar functionality for appointments page
let currentDate = new Date();
let appointmentsData = [];

// Initialize calendar on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load appointments first, then render calendar
    loadAppointmentsForCalendar();
});

// Render calendar for current month
function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Update month/year header
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('calendar-month-year').textContent = `${monthNames[month]} ${year}`;
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    
    // Get today's date for highlighting
    const today = new Date();
    const isCurrentMonth = year === today.getFullYear() && month === today.getMonth();
    const todayDate = today.getDate();
    
    // Build calendar HTML
    let calendarHTML = '';
    let dayCounter = 1;
    let prevMonthDay = daysInPrevMonth - firstDay + 1;
    
    // Calculate number of weeks needed
    const totalCells = firstDay + daysInMonth;
    const weeksNeeded = Math.ceil(totalCells / 7);
    
    for (let week = 0; week < weeksNeeded; week++) {
        calendarHTML += '<tr>';
        
        for (let dayOfWeek = 0; dayOfWeek < 7; dayOfWeek++) {
            let cellHTML = '';
            let dayNumber = '';
            let isOtherMonth = false;
            let isToday = false;
            
            if (week === 0 && dayOfWeek < firstDay) {
                // Previous month days
                dayNumber = prevMonthDay;
                prevMonthDay++;
                isOtherMonth = true;
            } else if (dayCounter <= daysInMonth) {
                // Current month days
                dayNumber = dayCounter;
                isToday = isCurrentMonth && dayCounter === todayDate;
                dayCounter++;
            } else {
                // Next month days
                dayNumber = dayCounter - daysInMonth;
                dayCounter++;
                isOtherMonth = true;
            }
            
            // Format date for this cell (YYYY-MM-DD)
            let cellDate;
            if (isOtherMonth && week === 0) {
                // Previous month
                const prevMonth = month === 0 ? 11 : month - 1;
                const prevYear = month === 0 ? year - 1 : year;
                cellDate = `${prevYear}-${String(prevMonth + 1).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
            } else if (isOtherMonth && dayCounter > daysInMonth) {
                // Next month
                const nextMonth = month === 11 ? 0 : month + 1;
                const nextYear = month === 11 ? year + 1 : year;
                cellDate = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
            } else {
                // Current month
                cellDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
            }
            
            // Get appointments for this date
            const dayAppointments = getAppointmentsForDate(cellDate);
            
            cellHTML = `<td class="${isOtherMonth ? 'other-month' : ''} ${isToday ? 'today' : ''}">`;
            cellHTML += `<div class="day-number">${dayNumber}</div>`;
            
            // Add appointments for this day
            if (dayAppointments.length > 0) {
                dayAppointments.forEach(apt => {
                    const serviceClass = getServiceColorClass(apt.service);
                    const timeDisplay = apt.appointment_time ? formatTime(apt.appointment_time) + ' - ' : '';
                    cellHTML += `<div class="event ${serviceClass}" title="${apt.service} - ${apt.client}">${timeDisplay}${apt.service} - ${apt.client}</div>`;
                });
            }
            
            cellHTML += '</td>';
            calendarHTML += cellHTML;
        }
        
        calendarHTML += '</tr>';
    }
    
    document.getElementById('calendar-body').innerHTML = calendarHTML;
}

// Get appointments for a specific date
function getAppointmentsForDate(dateString) {
    return appointmentsData.filter(apt => {
        if (!apt.appointment_date) {
            console.log('Appointment missing date:', apt);
            return false;
        }
        // Handle both date string and date object formats
        let aptDate;
        if (typeof apt.appointment_date === 'string') {
            aptDate = apt.appointment_date.split(' ')[0]; // Get date part only (YYYY-MM-DD)
        } else if (apt.appointment_date instanceof Date) {
            aptDate = apt.appointment_date.toISOString().split('T')[0];
        } else {
            return false;
        }
        return aptDate === dateString;
    });
}

// Get color class for service type
function getServiceColorClass(service) {
    const colorMap = {
        'Order Placed': 'blue',
        'Ocular Visit': 'orange',
        'In Fabrication': 'purple',
        'Installed': 'yellow',
        'Completed': 'green'
    };
    return colorMap[service] || 'blue';
}

// Format time from HH:MM:SS to readable format
function formatTime(timeString) {
    if (!timeString) return '';
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}

// Load appointments for calendar
function loadAppointmentsForCalendar() {
    fetch('<?php echo base_url('AdminCon/get_appointments_ajax'); ?>?status=all')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                appointmentsData = data.appointments;
                console.log('Loaded appointments for calendar:', appointmentsData.length, 'appointments');
                if (appointmentsData.length > 0) {
                    console.log('Sample appointment:', appointmentsData[0]);
                    console.log('Appointment date format:', appointmentsData[0].appointment_date);
                }
                // Always render calendar (even if no appointments, to show empty calendar)
                renderCalendar();
            } else {
                console.error('Error loading appointments:', data.message);
                // Still render calendar even on error
                renderCalendar();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Still render calendar even on error
            renderCalendar();
        });
}

// Navigate to previous month
function prevMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
    loadAppointmentsForCalendar();
}

// Navigate to next month
function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
    loadAppointmentsForCalendar();
}

// Go to today's date
function goToToday() {
    currentDate = new Date();
    renderCalendar();
    loadAppointmentsForCalendar();
}
</script>
<script src="<?php echo base_url('assets/js/side-popup-appointment.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/filter-status.js'); ?>"></script>
<script>
// Load appointments on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAppointments();
    
    // Setup filter handlers
    const filterSearch = document.getElementById('filter-search');
    
    if (filterSearch) {
        filterSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    }
});

function loadAppointments() {
    applyFilters();
}

function applyFilters() {
    const statusFilter = document.getElementById('filter-status')?.value || 'all';
    const serviceFilter = document.getElementById('filter-service')?.value || 'all';
    const search = document.getElementById('filter-search')?.value || '';
    const dateFilter = document.getElementById('filter-date')?.value || '';
    
    // Build query string
    const params = new URLSearchParams();
    if (statusFilter && statusFilter !== 'all') {
        params.append('status', statusFilter);
    }
    if (serviceFilter && serviceFilter !== 'all') {
        params.append('service', serviceFilter);
    }
    if (search) {
        params.append('search', search);
    }
    if (dateFilter) {
        params.append('date', dateFilter);
    }
    
    const queryString = params.toString();
    const url = `<?php echo base_url('AdminCon/get_appointments_ajax'); ?>${queryString ? '?' + queryString : ''}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderAppointments(data.appointments);
                updatePaginationInfo(data.total);
                // Also update calendar with all appointments (not filtered)
                loadAppointmentsForCalendar();
            } else {
                console.error('Error loading appointments:', data.message);
                document.getElementById('appointments-tbody').innerHTML = 
                    '<tr><td colspan="7" style="text-align: center; padding: 20px; color: red;">Error loading appointments</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('appointments-tbody').innerHTML = 
                '<tr><td colspan="7" style="text-align: center; padding: 20px; color: red;">Error loading appointments</td></tr>';
        });
}

function clearFilters() {
    document.getElementById('filter-status').value = 'all';
    document.getElementById('filter-service').value = 'all';
    document.getElementById('filter-search').value = '';
    document.getElementById('filter-date').value = '';
    loadAppointments();
}

function renderAppointments(appointments) {
    const tbody = document.getElementById('appointments-tbody');
    
    if (appointments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No appointments found</td></tr>';
        return;
    }
    
    tbody.innerHTML = appointments.map((apt, index) => {
        return `
            <tr>
                <td>${index + 1}</td>
                <td class="client-cell">${apt.client}</td>
                <td class="status-cell"><span class="tag ${apt.service_class}">${apt.service}</span></td>
                <td class="date-cell">${apt.date_time}</td>
                <td>${apt.assigned_staff}</td>
                <td class="progress-cell"><span class="status ${apt.status_class}"></span> ${apt.status}</td>
                <td><button class="edit-progress-btn" onclick="openEditModal(${apt.id})">Edit Progress</button></td>
            </tr>
        `;
    }).join('');
}

function updatePaginationInfo(total) {
    const paginationInfo = document.getElementById('pagination-info');
    if (paginationInfo) {
        paginationInfo.textContent = `Showing ${total} item${total !== 1 ? 's' : ''}`;
    }
}

function openEditModal(appointmentId) {
    fetch(`<?php echo base_url('AdminCon/get_appointment_details_ajax'); ?>?appointment_id=${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const apt = data.appointment;
                document.getElementById('edit-appointment-id').value = apt.id;
                document.getElementById('edit-project-name').value = apt.product;
                document.getElementById('edit-client-name').value = apt.client;
                document.getElementById('edit-service').value = apt.service;
                document.getElementById('edit-date').value = apt.date;
                document.getElementById('edit-time').value = apt.time;
                document.getElementById('edit-assigned-staff').value = apt.assigned_staff;
                document.getElementById('edit-status').value = apt.status;
                document.getElementById('edit-notes').value = apt.notes;
                
                document.getElementById('editProgressPopupOverlay').style.display = 'flex';
            } else {
                alert('Error loading appointment details: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading appointment details');
        });
}

function saveAppointmentChanges() {
    const appointmentId = document.getElementById('edit-appointment-id').value;
    const formData = new FormData();
    
    formData.append('appointment_id', appointmentId);
    formData.append('client_name', document.getElementById('edit-client-name').value);
    formData.append('service', document.getElementById('edit-service').value);
    formData.append('date', document.getElementById('edit-date').value);
    formData.append('time', document.getElementById('edit-time').value);
    formData.append('assigned_staff', document.getElementById('edit-assigned-staff').value);
    formData.append('status', document.getElementById('edit-status').value);
    formData.append('notes', document.getElementById('edit-notes').value);
    
    fetch('<?php echo base_url('AdminCon/update_appointment_ajax'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Appointment updated successfully!');
            closePopup();
            loadAppointments(); // This will also refresh the calendar
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating appointment');
    });
}

function deleteAppointment() {
    if (!confirm('Are you sure you want to delete this appointment?')) {
        return;
    }
    
    const appointmentId = document.getElementById('edit-appointment-id').value;
    // TODO: Implement delete functionality if needed
    alert('Delete functionality not yet implemented');
}

function closePopup() {
    document.getElementById('editProgressPopupOverlay').style.display = 'none';
}
</script>
