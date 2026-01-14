// Calendar / Project Timeline Module
// Handles Monthly, Weekly, Daily, and Timeline views

let currentView = 'monthly';
let currentDate = new Date();
let calendarEvents = [];
let filters = {
    order_type: 'all',
    status: 'all',
    date_start: '',
    date_end: '',
    search: ''
};

// Initialize calendar on page load
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    initializeCalendar();
});

// Initialize calendar
function initializeCalendar() {
    // Render initial view, then load events (which will re-render)
    renderMonthlyView();
    updateFoundText();
    // Load events for current month
    loadCalendarEvents();
}

// Setup event listeners
function setupEventListeners() {
    // View option buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            switchView(this.dataset.view);
        });
    });
    
    // Filter controls
    const orderTypeFilter = document.getElementById('order-type-filter');
    const statusFilter = document.getElementById('status-filter');
    const dateStartFilter = document.getElementById('date-start-filter');
    const dateEndFilter = document.getElementById('date-end-filter');
    const searchFilter = document.getElementById('search-filter');
    const applyFiltersBtn = document.getElementById('apply-calendar-filters');
    const clearFiltersBtn = document.getElementById('clear-calendar-filters');
    
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            filters.order_type = orderTypeFilter ? orderTypeFilter.value : 'all';
            filters.status = statusFilter ? statusFilter.value : 'all';
            filters.date_start = dateStartFilter ? dateStartFilter.value : '';
            filters.date_end = dateEndFilter ? dateEndFilter.value : '';
            filters.search = searchFilter ? searchFilter.value : '';
            loadCalendarEvents();
        });
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            if (orderTypeFilter) orderTypeFilter.value = 'all';
            if (statusFilter) statusFilter.value = 'all';
            if (dateStartFilter) dateStartFilter.value = '';
            if (dateEndFilter) dateEndFilter.value = '';
            if (searchFilter) searchFilter.value = '';
            filters = {
                order_type: 'all',
                status: 'all',
                date_start: '',
                date_end: '',
                search: ''
            };
            loadCalendarEvents();
        });
    }
    
    // Close sidebar
    const closeSidebar = document.getElementById('close-sidebar');
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            closeDayDetailsSidebar();
        });
    }
}

// Switch between views
function switchView(view) {
    currentView = view;
    
    // Update active button
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === view) {
            btn.classList.add('active');
        }
    });
    
    // Hide all views
    document.querySelectorAll('.calendar-view').forEach(viewEl => {
        viewEl.classList.remove('active');
    });
    
    // Show selected view
    const viewElement = document.getElementById(`calendar-view-${view}`);
    if (viewElement) {
        viewElement.classList.add('active');
    }
    
    // Render the selected view
    switch(view) {
        case 'monthly':
            renderMonthlyView();
            break;
        case 'weekly':
            renderWeeklyView();
            break;
        case 'daily':
            renderDailyView();
            break;
        case 'timeline':
            renderTimelineView();
            break;
    }
}

// Load calendar events from server
function loadCalendarEvents() {
    const params = new URLSearchParams();
    
    // If no date filters are set, load events for current month
    if (!filters.date_start || !filters.date_end) {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        
        params.append('start', formatDate(firstDay));
        params.append('end', formatDate(lastDay));
    } else {
        if (filters.date_start) params.append('start', filters.date_start);
        if (filters.date_end) params.append('end', filters.date_end);
    }
    
    if (filters.order_type && filters.order_type !== 'all') params.append('order_type', filters.order_type);
    if (filters.status && filters.status !== 'all') params.append('status', filters.status);
    if (filters.search) params.append('search', filters.search);
    
    fetch(`${getCalendarEventsUrl}?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            calendarEvents = Array.isArray(data) ? data : [];
            console.log('Loaded calendar events:', calendarEvents.length, 'events');
            if (calendarEvents.length > 0) {
                console.log('Sample event:', calendarEvents[0]);
            }
            renderCurrentView();
            updateFoundText();
        })
        .catch(error => {
            console.error('Error loading calendar events:', error);
            calendarEvents = [];
            renderCurrentView();
            updateFoundText();
        });
}

// Render current view
function renderCurrentView() {
    switch(currentView) {
        case 'monthly':
            renderMonthlyView();
            break;
        case 'weekly':
            renderWeeklyView();
            break;
        case 'daily':
            renderDailyView();
            break;
        case 'timeline':
            renderTimelineView();
            break;
    }
}

// ======================
// MONTHLY VIEW
// ======================
function renderMonthlyView() {
    const container = document.getElementById('monthly-calendar');
    if (!container) return;
    
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    
    const today = new Date();
    const isCurrentMonth = year === today.getFullYear() && month === today.getMonth();
    const todayDate = today.getDate();
    
    let html = `
        <div class="monthly-calendar">
            <div class="monthly-header">
                <button class="month-nav-btn" onclick="navigateMonth(-1)">‹ Prev</button>
                <div class="month-year-display">${monthNames[month]} ${year}</div>
                <button class="month-nav-btn" onclick="navigateMonth(1)">Next ›</button>
            </div>
            <table class="calendar-grid">
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
                <tbody>
    `;
    
    let dayCounter = 1;
    let prevMonthDay = daysInPrevMonth - firstDay + 1;
    const totalCells = firstDay + daysInMonth;
    const weeksNeeded = Math.ceil(totalCells / 7);
    
    for (let week = 0; week < weeksNeeded; week++) {
        html += '<tr>';
        
        for (let dayOfWeek = 0; dayOfWeek < 7; dayOfWeek++) {
            let dayNumber = '';
            let isOtherMonth = false;
            let isToday = false;
            let cellDate;
            
            if (week === 0 && dayOfWeek < firstDay) {
                dayNumber = prevMonthDay;
                prevMonthDay++;
                isOtherMonth = true;
                const prevMonth = month === 0 ? 11 : month - 1;
                const prevYear = month === 0 ? year - 1 : year;
                cellDate = `${prevYear}-${String(prevMonth + 1).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
            } else if (dayCounter <= daysInMonth) {
                dayNumber = dayCounter;
                isToday = isCurrentMonth && dayCounter === todayDate;
                cellDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
                dayCounter++;
            } else {
                dayNumber = dayCounter - daysInMonth;
                dayCounter++;
                isOtherMonth = true;
                const nextMonth = month === 11 ? 0 : month + 1;
                const nextYear = month === 11 ? year + 1 : year;
                cellDate = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
            }
            
            const dayEvents = getEventsForDate(cellDate);
            const eventCount = dayEvents.length;
            
            html += `<td class="calendar-day ${isOtherMonth ? 'other-month' : ''} ${isToday ? 'today' : ''}" 
                        onclick="showDayDetails('${cellDate}')">
                        <div class="day-number">${dayNumber}</div>
                        <div class="day-events">`;
            
            // Show detailed event cards inside the calendar cell
            if (dayEvents.length > 0) {
                const maxEventsToShow = 2; // Show max 2 events, then "more" link
                const eventsToDisplay = dayEvents.slice(0, maxEventsToShow);
                const remainingCount = dayEvents.length - maxEventsToShow;
                
                eventsToDisplay.forEach(event => {
                    const eventClass = getEventColorClass(event);
                    const isAppointment = event.type === 'appointment';
                    
                    // For appointments, show service type; for orders, show order number
                    let eventIdentifier;
                    let eventType;
                    let secondaryInfo = '';
                    
                    if (isAppointment) {
                        eventType = getShortServiceType(event.service);
                        eventIdentifier = event.order_number && event.order_number !== 'N/A' ? event.order_number : '';
                        secondaryInfo = event.client_name || '';
                    } else {
                        eventType = 'Order';
                        eventIdentifier = event.order_number || getEventCompactIdentifier(event);
                        secondaryInfo = event.customer || '';
                    }
                    
                    const shortSecondary = secondaryInfo.length > 15 ? secondaryInfo.substring(0, 15) + '...' : secondaryInfo;
                    const statusClass = getStatusBadgeClass(event.status);
                    const shortStatus = getShortStatus(event.status);
                    const eventId = isAppointment ? event.appointment_id : event.order_id;
                    
                    html += `<div class="calendar-event-card ${eventClass}" 
                                  onclick="event.stopPropagation(); showEventDetails(${eventId}, '${event.type}')"
                                  title="${event.title || eventIdentifier}">
                                <div class="event-card-header">
                                    <span class="event-type-badge ${eventClass}">${eventType}</span>
                                    <span class="event-status-badge ${statusClass}">${shortStatus}</span>
                                </div>
                                ${eventIdentifier ? `<div class="event-card-id">${eventIdentifier}</div>` : ''}
                                ${shortSecondary ? `<div class="event-card-customer">${shortSecondary}</div>` : ''}
                             </div>`;
                });
                
                // Show "more" indicator if there are additional events
                if (remainingCount > 0) {
                    html += `<div class="event-more-link">+${remainingCount} more</div>`;
                }
            }
            
            html += `</div></td>`;
        }
        
        html += '</tr>';
    }
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

// ======================
// WEEKLY VIEW
// ======================
function renderWeeklyView() {
    const container = document.getElementById('weekly-calendar');
    if (!container) return;
    
    const weekStart = getWeekStart(currentDate);
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 6);
    
    const weekDays = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date(weekStart);
        date.setDate(weekStart.getDate() + i);
        weekDays.push(date);
    }
    
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayNamesShort = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const timeSlots = [];
    for (let hour = 6; hour <= 20; hour++) {
        timeSlots.push(hour);
    }
    
    const today = new Date();
    const todayStr = formatDate(today);
    const currentHour = today.getHours();
    const currentMinutes = today.getMinutes();
    
    // Get week range display
    const weekRangeDisplay = `${monthNames[weekStart.getMonth()]} ${weekStart.getDate()} - ${monthNames[weekEnd.getMonth()]} ${weekEnd.getDate()}, ${weekEnd.getFullYear()}`;
    
    let html = `
        <div class="weekly-calendar">
            <div class="weekly-nav-header">
                <button class="week-nav-btn" onclick="navigateWeek(-1)">‹ Previous Week</button>
                <div class="week-range-display">${weekRangeDisplay}</div>
                <button class="week-nav-btn" onclick="goToCurrentWeek()">This Week</button>
                <button class="week-nav-btn" onclick="navigateWeek(1)">Next Week ›</button>
            </div>
            <div class="weekly-header">
                <div class="weekly-time-column">Time</div>
                ${weekDays.map((day, idx) => {
                    const dateStr = formatDate(day);
                    const isToday = dateStr === todayStr;
                    const dayEvents = getEventsForDate(dateStr);
                    return `
                        <div class="weekly-day-header ${isToday ? 'today' : ''}" onclick="showDayDetails('${dateStr}')">
                            <div class="weekly-day-name">${dayNamesShort[day.getDay()]}</div>
                            <div class="weekly-day-date ${isToday ? 'today-circle' : ''}">${day.getDate()}</div>
                            ${dayEvents.length > 0 ? `<div class="weekly-event-count">${dayEvents.length} event${dayEvents.length > 1 ? 's' : ''}</div>` : ''}
                        </div>
                    `;
                }).join('')}
            </div>
            <div class="weekly-body-wrapper">
                <div class="weekly-body">
                    <div class="weekly-time-slots">
                        ${timeSlots.map(hour => `
                            <div class="time-slot">${formatHour(hour)}</div>
                        `).join('')}
                    </div>
                    ${weekDays.map((day, dayIdx) => {
                        const dateStr = formatDate(day);
                        const isToday = dateStr === todayStr;
                        const dayEvents = getEventsForDate(dateStr);
                        
                        return `
                            <div class="weekly-day-column ${isToday ? 'today-column' : ''}">
                                ${isToday ? `<div class="current-time-indicator" style="top: ${((currentHour - 6) * 60 + currentMinutes)}px;"></div>` : ''}
                                ${timeSlots.map(hour => {
                                    return `<div class="weekly-day-slot"></div>`;
                                }).join('')}
                                <div class="weekly-events-container">
                                    ${dayEvents.map((event, eventIdx) => {
                                        const eventClass = getEventColorClass(event);
                                        const eventId = event.order_id || event.appointment_id;
                                        
                                        // Calculate position based on event time or distribute evenly
                                        let topPosition = 0;
                                        let eventHeight = 50;
                                        
                                        if (event.start && event.start.includes(':')) {
                                            const eventDate = new Date(event.start);
                                            const eventHour = eventDate.getHours();
                                            const eventMinutes = eventDate.getMinutes();
                                            topPosition = (eventHour - 6) * 60 + eventMinutes;
                                        } else {
                                            // For all-day events, stack them at the top
                                            topPosition = eventIdx * 55;
                                        }
                                        
                                        const isAppointment = event.type === 'appointment';
                                        const eventTitle = isAppointment 
                                            ? (event.service || 'Appointment')
                                            : `Order: ${event.order_number || 'N/A'}`;
                                        const eventSubtitle = isAppointment
                                            ? (event.client_name || '')
                                            : (event.customer || '');
                                        
                                        return `
                                            <div class="weekly-event-card ${eventClass}" 
                                                 style="top: ${topPosition}px;"
                                                 onclick="showEventDetails(${eventId}, '${event.type}')"
                                                 title="${eventTitle} - ${eventSubtitle}">
                                                <div class="weekly-event-title">${eventTitle}</div>
                                                <div class="weekly-event-subtitle">${eventSubtitle}</div>
                                                <div class="weekly-event-status">${getShortStatus(event.status)}</div>
                                            </div>
                                        `;
                                    }).join('')}
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Auto-scroll to current time if viewing current week
    const isCurrentWeek = weekDays.some(day => formatDate(day) === todayStr);
    if (isCurrentWeek) {
        const bodyWrapper = container.querySelector('.weekly-body-wrapper');
        if (bodyWrapper) {
            const scrollTop = Math.max(0, (currentHour - 6) * 60 - 100);
            bodyWrapper.scrollTop = scrollTop;
        }
    }
}

// Navigate to next/previous week
function navigateWeek(direction) {
    currentDate.setDate(currentDate.getDate() + (direction * 7));
    loadCalendarEvents();
}

// Go to current week
function goToCurrentWeek() {
    currentDate = new Date();
    loadCalendarEvents();
}

// ======================
// DAILY VIEW
// ======================
function renderDailyView() {
    const container = document.getElementById('daily-calendar');
    if (!container) return;
    
    const dateStr = formatDate(currentDate);
    const dayEvents = getEventsForDate(dateStr);
    const timeSlots = [];
    for (let hour = 6; hour <= 21; hour++) {
        timeSlots.push(hour);
    }
    
    const today = new Date();
    const todayStr = formatDate(today);
    const isToday = dateStr === todayStr;
    const currentHour = today.getHours();
    const currentMinutes = today.getMinutes();
    
    // Separate all-day events from timed events
    const allDayEvents = dayEvents.filter(e => !e.start || !e.start.includes(':') || e.start.includes('00:00:00'));
    const timedEvents = dayEvents.filter(e => e.start && e.start.includes(':') && !e.start.includes('00:00:00'));
    
    // Group events by type
    const orders = dayEvents.filter(e => e.type === 'order');
    const appointments = dayEvents.filter(e => e.type === 'appointment');
    
    // Get day info
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    let html = `
        <div class="daily-calendar">
            <div class="daily-header">
                <button class="day-nav-btn" onclick="navigateDay(-1)">
                    <span class="nav-arrow">‹</span>
                    <span class="nav-text">Previous Day</span>
                </button>
                <div class="daily-date-info">
                    <div class="daily-day-name">${dayNames[currentDate.getDay()]}</div>
                    <div class="daily-date-display">${monthNames[currentDate.getMonth()]} ${currentDate.getDate()}, ${currentDate.getFullYear()}</div>
                    ${isToday ? '<div class="today-badge">Today</div>' : ''}
                </div>
                <div class="daily-nav-right">
                    <button class="day-nav-btn today-btn" onclick="goToToday()">Go to Today</button>
                    <button class="day-nav-btn" onclick="navigateDay(1)">
                        <span class="nav-text">Next Day</span>
                        <span class="nav-arrow">›</span>
                    </button>
                </div>
            </div>
            
            <div class="daily-content-wrapper">
                <!-- Left: Event Summary Panel -->
                <div class="daily-summary-panel">
                    <div class="daily-summary-header">
                        <h3>Events Summary</h3>
                        <span class="event-total-count">${dayEvents.length} Event${dayEvents.length !== 1 ? 's' : ''}</span>
                    </div>
                    
                    <div class="daily-stats">
                        <div class="stat-item">
                            <span class="stat-icon stat-orders">📦</span>
                            <span class="stat-value">${orders.length}</span>
                            <span class="stat-label">Orders</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-icon stat-appointments">📅</span>
                            <span class="stat-value">${appointments.length}</span>
                            <span class="stat-label">Appointments</span>
                        </div>
                    </div>
                    
                    ${dayEvents.length > 0 ? `
                        <div class="daily-events-list">
                            <h4>All Events</h4>
                            ${dayEvents.map(event => {
                                const eventClass = getEventColorClass(event);
                                const isAppointment = event.type === 'appointment';
                                const eventId = event.order_id || event.appointment_id;
                                const eventTitle = isAppointment 
                                    ? (event.service || 'Appointment')
                                    : `Order: ${event.order_number || 'N/A'}`;
                                const eventSubtitle = isAppointment
                                    ? (event.client_name || 'N/A')
                                    : (event.customer || 'N/A');
                                const eventTime = event.time ? formatTime(event.time) : 'All Day';
                                
                                return `
                                    <div class="daily-event-card ${eventClass}" 
                                         onclick="showEventDetails(${eventId}, '${event.type}')">
                                        <div class="event-card-left">
                                            <div class="event-type-icon ${eventClass}">
                                                ${isAppointment ? '📅' : '📦'}
                                            </div>
                                        </div>
                                        <div class="event-card-right">
                                            <div class="event-card-title">${eventTitle}</div>
                                            <div class="event-card-subtitle">${eventSubtitle}</div>
                                            <div class="event-card-meta">
                                                <span class="event-time-badge">${eventTime}</span>
                                                <span class="event-status-badge ${getStatusBadgeClass(event.status)}">${getShortStatus(event.status)}</span>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    ` : `
                        <div class="no-events-message">
                            <div class="no-events-icon">📭</div>
                            <div class="no-events-text">No events scheduled for this day</div>
                        </div>
                    `}
                </div>
                
                <!-- Right: Timeline View -->
                <div class="daily-timeline-panel">
                    <div class="daily-timeline-header">
                        <h3>Timeline</h3>
                        <div class="timeline-view-toggle">
                            <button class="timeline-toggle-btn active" data-view="schedule" onclick="toggleDailyTimelineView('schedule')">Schedule</button>
                            <button class="timeline-toggle-btn" data-view="list" onclick="toggleDailyTimelineView('list')">List</button>
                        </div>
                    </div>
                    
                    <!-- All Day Events Section -->
                    ${allDayEvents.length > 0 ? `
                        <div class="daily-all-day-section">
                            <div class="all-day-label">All Day Events</div>
                            <div class="all-day-events">
                                ${allDayEvents.map(event => {
                                    const eventClass = getEventColorClass(event);
                                    const isAppointment = event.type === 'appointment';
                                    const eventId = event.order_id || event.appointment_id;
                                    const eventTitle = isAppointment 
                                        ? (event.service || 'Appointment')
                                        : `Order: ${event.order_number || 'N/A'}`;
                                    
                                    return `
                                        <div class="all-day-event-chip ${eventClass}" 
                                             onclick="showEventDetails(${eventId}, '${event.type}')"
                                             title="${eventTitle}">
                                            ${isAppointment ? '📅' : '📦'} ${eventTitle}
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Time Slots Timeline -->
                    <div class="daily-timeline-body" id="daily-timeline-schedule">
                        ${isToday ? `<div class="current-time-line" style="top: ${((currentHour - 6) * 60 + currentMinutes)}px;">
                            <span class="current-time-label">${formatHour(currentHour).replace(':00', ':' + String(currentMinutes).padStart(2, '0'))}</span>
                        </div>` : ''}
                        <div class="daily-time-slots">
                            ${timeSlots.map(hour => {
                                const hourEvents = timedEvents.filter(e => {
                                    if (!e.start) return false;
                                    const eventTime = new Date(e.start);
                                    return eventTime.getHours() === hour;
                                });
                                
                                const isCurrentHour = isToday && hour === currentHour;
                                
                                return `
                                    <div class="daily-time-row ${isCurrentHour ? 'current-hour' : ''}">
                                        <div class="time-label">${formatHour(hour)}</div>
                                        <div class="time-content">
                                            ${hourEvents.map(event => {
                                                const eventClass = getEventColorClass(event);
                                                const isAppointment = event.type === 'appointment';
                                                const eventId = event.order_id || event.appointment_id;
                                                const eventTitle = isAppointment 
                                                    ? (event.service || 'Appointment')
                                                    : `Order: ${event.order_number || 'N/A'}`;
                                                const eventSubtitle = isAppointment
                                                    ? (event.client_name || '')
                                                    : (event.customer || '');
                                                    
                                                return `
                                                    <div class="timeline-event ${eventClass}"
                                                         onclick="showEventDetails(${eventId}, '${event.type}')">
                                                        <div class="timeline-event-time">${formatTime(event.time || event.start)}</div>
                                                        <div class="timeline-event-content">
                                                            <div class="timeline-event-title">${eventTitle}</div>
                                                            <div class="timeline-event-subtitle">${eventSubtitle}</div>
                                                        </div>
                                                        <div class="timeline-event-status ${getStatusBadgeClass(event.status)}">${getShortStatus(event.status)}</div>
                                                    </div>
                                                `;
                                            }).join('')}
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Auto-scroll to current hour if viewing today
    if (isToday) {
        const timelineBody = container.querySelector('.daily-timeline-body');
        if (timelineBody) {
            const scrollTop = Math.max(0, (currentHour - 6) * 60 - 100);
            timelineBody.scrollTop = scrollTop;
        }
    }
}

// Toggle daily timeline view mode
function toggleDailyTimelineView(viewMode) {
    const buttons = document.querySelectorAll('.timeline-toggle-btn');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === viewMode) {
            btn.classList.add('active');
        }
    });
    
    // Could implement different view modes here (schedule vs list)
    // For now, we just toggle the button states
}

// ======================
// TIMELINE VIEW (GANTT)
// ======================

// Timeline state variables
let timelineZoom = 'month'; // 'week', 'month', '3months'
let timelineStartDate = null;

function renderTimelineView() {
    const container = document.getElementById('timeline-calendar');
    if (!container) return;
    
    // Initialize timeline date if not set
    if (!timelineStartDate) {
        timelineStartDate = new Date(currentDate);
        timelineStartDate.setDate(1); // Start from first of the month
    }
    
    // Get all events (orders and appointments)
    const orders = calendarEvents.filter(e => e.type === 'order');
    const appointments = calendarEvents.filter(e => e.type === 'appointment');
    
    // Calculate date range based on zoom level
    const dates = [];
    let rangeStart = new Date(timelineStartDate);
    let rangeEnd = new Date(timelineStartDate);
    let dayIncrement = 1;
    
    switch(timelineZoom) {
        case 'week':
            rangeStart = getWeekStart(timelineStartDate);
            rangeEnd = new Date(rangeStart);
            rangeEnd.setDate(rangeEnd.getDate() + 6);
            break;
        case 'month':
            rangeStart = new Date(timelineStartDate.getFullYear(), timelineStartDate.getMonth(), 1);
            rangeEnd = new Date(timelineStartDate.getFullYear(), timelineStartDate.getMonth() + 1, 0);
            break;
        case '3months':
            rangeStart = new Date(timelineStartDate.getFullYear(), timelineStartDate.getMonth(), 1);
            rangeEnd = new Date(timelineStartDate.getFullYear(), timelineStartDate.getMonth() + 3, 0);
            dayIncrement = 2; // Show every other day for 3 month view
            break;
    }
    
    for (let d = new Date(rangeStart); d <= rangeEnd; d.setDate(d.getDate() + dayIncrement)) {
        dates.push(new Date(d));
    }
    
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const monthNamesShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    // Get range display text
    let rangeDisplay = '';
    if (timelineZoom === 'week') {
        rangeDisplay = `${monthNamesShort[rangeStart.getMonth()]} ${rangeStart.getDate()} - ${monthNamesShort[rangeEnd.getMonth()]} ${rangeEnd.getDate()}, ${rangeEnd.getFullYear()}`;
    } else if (timelineZoom === 'month') {
        rangeDisplay = `${monthNames[rangeStart.getMonth()]} ${rangeStart.getFullYear()}`;
    } else {
        rangeDisplay = `${monthNamesShort[rangeStart.getMonth()]} - ${monthNamesShort[rangeEnd.getMonth()]} ${rangeEnd.getFullYear()}`;
    }
    
    const today = new Date();
    const todayStr = formatDate(today);
    
    // Group dates by week for the header
    const weeks = [];
    let currentWeek = [];
    dates.forEach((date, idx) => {
        currentWeek.push(date);
        if (date.getDay() === 6 || idx === dates.length - 1) {
            weeks.push([...currentWeek]);
            currentWeek = [];
        }
    });
    
    let html = `
        <div class="timeline-calendar-container">
            <!-- Timeline Controls -->
            <div class="timeline-controls">
                <div class="timeline-nav">
                    <button class="timeline-nav-btn" onclick="navigateTimeline(-1)">‹ Previous</button>
                    <span class="timeline-range-display">${rangeDisplay}</span>
                    <button class="timeline-nav-btn" onclick="goToCurrentTimeline()">Today</button>
                    <button class="timeline-nav-btn" onclick="navigateTimeline(1)">Next ›</button>
                </div>
                <div class="timeline-zoom">
                    <span class="zoom-label">View:</span>
                    <button class="zoom-btn ${timelineZoom === 'week' ? 'active' : ''}" onclick="setTimelineZoom('week')">Week</button>
                    <button class="zoom-btn ${timelineZoom === 'month' ? 'active' : ''}" onclick="setTimelineZoom('month')">Month</button>
                    <button class="zoom-btn ${timelineZoom === '3months' ? 'active' : ''}" onclick="setTimelineZoom('3months')">3 Months</button>
                </div>
            </div>
            
            <!-- Timeline Stats -->
            <div class="timeline-stats">
                <div class="timeline-stat">
                    <span class="stat-number">${orders.length}</span>
                    <span class="stat-text">Orders</span>
                </div>
                <div class="timeline-stat">
                    <span class="stat-number">${appointments.length}</span>
                    <span class="stat-text">Appointments</span>
                </div>
                <div class="timeline-stat">
                    <span class="stat-number">${orders.filter(o => o.status === 'In Fabrication').length}</span>
                    <span class="stat-text">In Production</span>
                </div>
                <div class="timeline-stat">
                    <span class="stat-number">${orders.filter(o => o.status === 'Completed').length}</span>
                    <span class="stat-text">Completed</span>
                </div>
            </div>
            
            <!-- Timeline Legend -->
            <div class="timeline-legend">
                <div class="legend-item"><span class="legend-color event-direct"></span> Direct Order</div>
                <div class="legend-item"><span class="legend-color event-site-assessed"></span> Site-Assessed</div>
                <div class="legend-item"><span class="legend-color event-fabrication"></span> In Fabrication</div>
                <div class="legend-item"><span class="legend-color event-installation"></span> Installation</div>
                <div class="legend-item"><span class="legend-color event-completed"></span> Completed</div>
                <div class="legend-item"><span class="legend-color event-cancelled"></span> Cancelled</div>
            </div>
            
            <!-- Timeline Grid -->
            <div class="timeline-scroll-container">
                <div class="timeline-grid">
                    <!-- Header Row with Dates -->
                    <div class="timeline-header-row">
                        <div class="timeline-project-header">
                            <div class="project-header-title">Project / Order</div>
                            <div class="project-header-status">Status</div>
                        </div>
                        <div class="timeline-dates-header">
                            ${dates.map((date, idx) => {
                                const dateStr = formatDate(date);
                                const isToday = dateStr === todayStr;
                                const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                                const isFirstOfMonth = date.getDate() === 1;
                                
                                return `
                                    <div class="timeline-date-cell ${isToday ? 'today' : ''} ${isWeekend ? 'weekend' : ''} ${isFirstOfMonth ? 'month-start' : ''}">
                                        <div class="date-day">${dayNames[date.getDay()]}</div>
                                        <div class="date-num ${isToday ? 'today-circle' : ''}">${date.getDate()}</div>
                                        ${isFirstOfMonth ? `<div class="date-month">${monthNamesShort[date.getMonth()]}</div>` : ''}
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                    
                    <!-- Today Line Indicator -->
                    <div class="today-line-container">
                        ${dates.findIndex(d => formatDate(d) === todayStr) >= 0 ? `
                            <div class="today-line" style="left: calc(280px + ${(dates.findIndex(d => formatDate(d) === todayStr) / dates.length) * 100}%);"></div>
                        ` : ''}
                    </div>
                    
                    <!-- Timeline Body -->
                    <div class="timeline-body-rows">
                        ${orders.length === 0 && appointments.length === 0 ? `
                            <div class="no-timeline-data">
                                <div class="no-data-icon">📅</div>
                                <div class="no-data-text">No events found in this time range</div>
                                <button class="no-data-btn" onclick="goToCurrentTimeline()">Go to Today</button>
                            </div>
                        ` : ''}
                        
                        ${orders.map(order => {
                            const orderDate = new Date(order.start);
                            const eventClass = getEventColorClass(order);
                            const progress = order.progress || calculateOrderProgress(order);
                            
                            // Calculate bar position
                            const orderDateIdx = dates.findIndex(d => formatDate(d) === formatDate(orderDate));
                            let barStartPercent = orderDateIdx >= 0 ? (orderDateIdx / dates.length) * 100 : (orderDate < rangeStart ? 0 : 100);
                            
                            // Calculate expected end date (estimate 14 days for order completion if no end date)
                            let orderEndDate = order.installation_date ? new Date(order.installation_date) :
                                              order.fabrication_end ? new Date(order.fabrication_end) :
                                              new Date(orderDate.getTime() + (14 * 24 * 60 * 60 * 1000));
                            
                            if (order.status === 'Completed') {
                                orderEndDate = orderDate; // Show as single point for completed
                            }
                            
                            const orderEndIdx = dates.findIndex(d => formatDate(d) === formatDate(orderEndDate));
                            let barEndPercent = orderEndIdx >= 0 ? ((orderEndIdx + 1) / dates.length) * 100 : 100;
                            
                            let barWidth = Math.max(3, barEndPercent - barStartPercent);
                            
                            // Clamp values
                            if (barStartPercent < 0) barStartPercent = 0;
                            if (barStartPercent + barWidth > 100) barWidth = 100 - barStartPercent;
                            
                            // Status color class for the status badge
                            const statusClass = getStatusBadgeClass(order.status);
                            
                            return `
                                <div class="timeline-row" onclick="showEventDetails(${order.order_id}, 'order')">
                                    <div class="timeline-project-info">
                                        <div class="project-main-info">
                                            <div class="project-type-badge ${eventClass}">📦</div>
                                            <div class="project-details">
                                                <div class="project-number">${order.order_number || 'N/A'}</div>
                                                <div class="project-customer">${order.customer || 'N/A'}</div>
                                            </div>
                                        </div>
                                        <div class="project-status">
                                            <span class="status-badge ${statusClass}">${getShortStatus(order.status)}</span>
                                        </div>
                                    </div>
                                    <div class="timeline-bar-track">
                                        ${dates.map((date, idx) => {
                                            const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                                            return `<div class="timeline-day-cell ${isWeekend ? 'weekend' : ''}"></div>`;
                                        }).join('')}
                                        <div class="timeline-bar ${eventClass}" 
                                             style="left: ${barStartPercent}%; width: ${barWidth}%;"
                                             title="${order.order_number} - ${order.customer || 'N/A'} | ${order.status}">
                                            <div class="bar-progress" style="width: ${progress}%;"></div>
                                            <span class="bar-label">${order.order_number}</span>
                                        </div>
                                        ${order.installation_date ? `
                                            <div class="timeline-milestone installation" 
                                                 style="left: ${barEndPercent}%;"
                                                 title="Installation: ${formatDateShort(new Date(order.installation_date))}">
                                                🔧
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        }).join('')}
                        
                        ${appointments.length > 0 ? `
                            <div class="timeline-section-divider">
                                <span>Appointments</span>
                            </div>
                        ` : ''}
                        
                        ${appointments.map(appt => {
                            const apptDate = new Date(appt.start);
                            const eventClass = getEventColorClass(appt);
                            
                            // Calculate position
                            const apptDateIdx = dates.findIndex(d => formatDate(d) === formatDate(apptDate));
                            let barStartPercent = apptDateIdx >= 0 ? (apptDateIdx / dates.length) * 100 : -1;
                            
                            // Skip if outside range
                            if (barStartPercent < 0 || barStartPercent >= 100) return '';
                            
                            const statusClass = getStatusBadgeClass(appt.status);
                            
                            return `
                                <div class="timeline-row appointment" onclick="showEventDetails(${appt.appointment_id}, 'appointment')">
                                    <div class="timeline-project-info">
                                        <div class="project-main-info">
                                            <div class="project-type-badge ${eventClass}">📅</div>
                                            <div class="project-details">
                                                <div class="project-number">${appt.service || 'Appointment'}</div>
                                                <div class="project-customer">${appt.client_name || 'N/A'}</div>
                                            </div>
                                        </div>
                                        <div class="project-status">
                                            <span class="status-badge ${statusClass}">${getShortStatus(appt.status)}</span>
                                        </div>
                                    </div>
                                    <div class="timeline-bar-track">
                                        ${dates.map((date, idx) => {
                                            const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                                            return `<div class="timeline-day-cell ${isWeekend ? 'weekend' : ''}"></div>`;
                                        }).join('')}
                                        <div class="timeline-marker ${eventClass}" 
                                             style="left: ${barStartPercent}%;"
                                             title="${appt.service} - ${appt.client_name || 'N/A'} | ${appt.status}">
                                            <span class="marker-label">${getShortServiceType(appt.service)}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

// Calculate order progress based on status
function calculateOrderProgress(order) {
    const statusProgress = {
        'Pending Review': 10,
        'Awaiting Admin Approval': 15,
        'Approved': 25,
        'Ocular Pending': 30,
        'Ocular Completed': 40,
        'In Fabrication': 60,
        'Ready for Installation': 80,
        'Installed': 90,
        'Completed': 100,
        'Cancelled': 0
    };
    return statusProgress[order.status] || 0;
}

// Navigate timeline
function navigateTimeline(direction) {
    if (!timelineStartDate) {
        timelineStartDate = new Date(currentDate);
    }
    
    switch(timelineZoom) {
        case 'week':
            timelineStartDate.setDate(timelineStartDate.getDate() + (direction * 7));
            break;
        case 'month':
            timelineStartDate.setMonth(timelineStartDate.getMonth() + direction);
            break;
        case '3months':
            timelineStartDate.setMonth(timelineStartDate.getMonth() + (direction * 3));
            break;
    }
    
    renderTimelineView();
}

// Go to current date in timeline
function goToCurrentTimeline() {
    timelineStartDate = new Date();
    renderTimelineView();
}

// Set timeline zoom level
function setTimelineZoom(zoom) {
    timelineZoom = zoom;
    renderTimelineView();
}

// Format date short (for tooltips)
function formatDateShort(date) {
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${monthNames[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
}

// ======================
// HELPER FUNCTIONS
// ======================

function getEventsForDate(dateStr) {
    if (!calendarEvents || calendarEvents.length === 0) {
        return [];
    }
    
    // Normalize the cell date
    const normalizedCellDate = dateStr.substring(0, 10); // YYYY-MM-DD format
    
    return calendarEvents.filter(event => {
        if (!event.start) {
            return false;
        }
        
        // Handle both date-only and datetime formats
        let eventDateStr;
        if (typeof event.start === 'string') {
            // Remove time part if present (format: YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
            eventDateStr = event.start.split(' ')[0].substring(0, 10);
        } else if (event.start instanceof Date) {
            eventDateStr = formatDate(event.start);
        } else {
            return false;
        }
        
        // Compare normalized dates
        return eventDateStr === normalizedCellDate;
    });
}

function getEventsForTimeSlot(dateStr, hour) {
    return getEventsForDate(dateStr).filter(event => {
        if (!event.start) return false;
        const eventTime = new Date(event.start);
        return eventTime.getHours() === hour;
    });
}

function getEventColorClass(event) {
    if (event.type === 'order') {
        if (event.status === 'Cancelled') return 'event-cancelled';
        if (event.status === 'Completed') return 'event-completed';
        if (event.order_type === 'Site-Assessed') return 'event-site-assessed';
        return 'event-direct';
    } else if (event.type === 'appointment') {
        const service = event.service || '';
        
        // Ocular Visit = Red
        if (service.includes('Ocular Visit') || service.includes('Ocular')) {
            return 'event-cancelled'; // Using red for ocular visits
        }
        
        // Site Visit = Orange
        if (service.includes('Site Visit') || service.includes('Site Assessment')) {
            return 'event-ocular'; // Orange
        }
        
        // Measurement = Magenta/Pink
        if (service.includes('Measurement')) {
            return 'event-installation'; // Magenta/Pink
        }
        
        // Installation = Magenta/Pink
        if (service.includes('Installation') || service.includes('Installed')) {
            return 'event-installation'; // Magenta/Pink
        }
        
        // Default to purple for fabrication
        return 'event-fabrication';
    }
    return 'event-direct';
}

// Get status badge class for styling
function getStatusBadgeClass(status) {
    if (!status) return 'status-pending';
    
    const statusLower = status.toLowerCase();
    
    if (statusLower.includes('pending') || statusLower.includes('awaiting')) {
        return 'status-pending';
    }
    if (statusLower.includes('approved')) {
        return 'status-approved';
    }
    if (statusLower === 'complete' || statusLower.includes('completed') || statusLower.includes('installed')) {
        return 'status-completed';
    }
    if (statusLower.includes('cancelled') || statusLower.includes('rejected')) {
        return 'status-cancelled';
    }
    if (statusLower.includes('fabrication') || statusLower.includes('production')) {
        return 'status-fabrication';
    }
    if (statusLower.includes('ready')) {
        return 'status-ready';
    }
    if (statusLower.includes('in progress')) {
        return 'status-in-progress';
    }
    if (statusLower.includes('scheduled')) {
        return 'status-scheduled';
    }
    
    return 'status-default';
}

// Get short status text for display in calendar cell
function getShortStatus(status) {
    if (!status) return 'Pending';
    
    const statusLower = status.toLowerCase();
    
    // Return shortened versions of common statuses
    if (statusLower.includes('awaiting admin')) return 'Awaiting';
    if (statusLower.includes('pending review')) return 'Pending';
    if (statusLower.includes('approved')) return 'Approved';
    if (statusLower.includes('completed') || statusLower === 'complete') return 'Done';
    if (statusLower.includes('cancelled')) return 'Cancelled';
    if (statusLower.includes('in fabrication')) return 'In Fab';
    if (statusLower.includes('ready for installation')) return 'Ready';
    if (statusLower.includes('installed')) return 'Installed';
    if (statusLower.includes('ocular pending')) return 'Ocular';
    if (statusLower.includes('ocular completed')) return 'Ocular OK';
    if (statusLower.includes('in progress')) return 'In Progress';
    
    // Return first word if status is long
    if (status.length > 12) {
        return status.split(' ')[0];
    }
    
    return status;
}

// Get short service type for appointment display in calendar cell
function getShortServiceType(service) {
    if (!service) return 'Appt';
    
    const serviceLower = service.toLowerCase();
    
    // Return shortened service types
    if (serviceLower.includes('ocular visit') || serviceLower.includes('ocular')) {
        return 'Ocular';
    }
    if (serviceLower.includes('site visit') || serviceLower.includes('site assessment')) {
        return 'Site Visit';
    }
    if (serviceLower.includes('measurement')) {
        return 'Measure';
    }
    if (serviceLower.includes('installation') || serviceLower.includes('installed')) {
        return 'Install';
    }
    if (serviceLower.includes('in fabrication') || serviceLower.includes('fabrication')) {
        return 'Fab';
    }
    if (serviceLower.includes('order placed')) {
        return 'Order';
    }
    if (serviceLower.includes('completed')) {
        return 'Complete';
    }
    
    // Return first word if service is long
    if (service.length > 10) {
        return service.split(' ')[0].substring(0, 8);
    }
    
    return service;
}

function getEventShortTitle(event) {
    if (event.type === 'order') {
        return `Order: ${event.order_number || 'N/A'}`;
    } else {
        return event.service || event.title;
    }
}

// Get time label in format like "10a", "1p"
function getEventTimeLabel(event) {
    if (!event.start) return '';
    
    try {
        const eventDate = new Date(event.start);
        const hours = eventDate.getHours();
        const minutes = eventDate.getMinutes();
        
        // Format as "10a", "1p", etc.
        if (hours === 0 && minutes === 0) {
            return ''; // No time specified
        }
        
        const hour12 = hours % 12 || 12;
        const ampm = hours >= 12 ? 'p' : 'a';
        
        // If minutes are 0, just show hour, otherwise show hour:minutes
        if (minutes === 0) {
            return `${hour12}${ampm} `;
        } else {
            return `${hour12}:${String(minutes).padStart(2, '0')}${ampm} `;
        }
    } catch (e) {
        return '';
    }
}

// Get display title for event
function getEventDisplayTitle(event) {
    if (event.type === 'appointment') {
        // For appointments, show service and client name
        const service = event.service || '';
        const client = event.client_name || '';
        
        if (service && client) {
            // Format like "Site Visit - Client A"
            return `${service} - ${client}`;
        } else if (service) {
            return service;
        } else {
            return event.title || 'Appointment';
        }
    } else {
        // For orders, show order number
        return `Order: ${event.order_number || 'N/A'}`;
    }
}

// Get compact identifier for event (shown inside calendar cell)
function getEventCompactIdentifier(event) {
    if (event.type === 'order') {
        // For orders, show just the order number (e.g., "GI003")
        return event.order_number || 'GI' + String(event.order_id || '').padStart(3, '0');
    } else if (event.type === 'appointment') {
        // For appointments, show a short identifier
        // Could be order number if available, or first few letters of service
        if (event.order_number) {
            return event.order_number;
        } else if (event.service) {
            // Get first letters of service type (e.g., "SV" for Site Visit)
            const words = event.service.split(' ');
            if (words.length >= 2) {
                return words[0].substring(0, 2).toUpperCase() + words[1].substring(0, 1).toUpperCase();
            }
            return event.service.substring(0, 4).toUpperCase();
        }
        return 'APT';
    }
    return 'EVT';
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDateDisplay(date) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function formatHour(hour) {
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:00 ${ampm}`;
}

function formatTime(timeStr) {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}

function getWeekStart(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day;
    return new Date(d.setDate(diff));
}

// ======================
// NAVIGATION
// ======================

function navigateMonth(direction) {
    currentDate.setMonth(currentDate.getMonth() + direction);
    // Reload events for the new month
    loadCalendarEvents();
}

function navigateDay(direction) {
    currentDate.setDate(currentDate.getDate() + direction);
    renderCurrentView();
}

function goToToday() {
    currentDate = new Date();
    renderCurrentView();
}

// ======================
// DAY DETAILS SIDEBAR
// ======================

function showDayDetails(dateStr) {
    fetch(`${getDayDetailsUrl}?date=${dateStr}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayDayDetails(dateStr, data.events);
            }
        })
        .catch(error => {
            console.error('Error loading day details:', error);
        });
}

function displayDayDetails(dateStr, events) {
    const sidebar = document.getElementById('day-details-sidebar');
    const sidebarDate = document.getElementById('sidebar-date');
    const sidebarContent = document.getElementById('sidebar-content');
    
    if (!sidebar || !sidebarDate || !sidebarContent) return;
    
    const date = new Date(dateStr);
    sidebarDate.textContent = formatDateDisplay(date);
    
    if (events.length === 0) {
        sidebarContent.innerHTML = '<p>No events scheduled for this day.</p>';
    } else {
        sidebarContent.innerHTML = events.map(event => {
            const eventClass = getEventColorClass(event);
            return `
                <div class="day-event-item" onclick="showEventDetails(${event.order_id || event.appointment_id}, '${event.type}')">
                    <div class="event-item-header">
                        <span class="event-item-type ${eventClass}">${event.type === 'order' ? 'Order' : 'Appointment'}</span>
                        <span class="event-item-status">${event.status || 'N/A'}</span>
                    </div>
                    <div class="event-item-details">
                        ${event.type === 'order' ? `
                            <strong>${event.order_number || 'N/A'}</strong><br>
                            Customer: ${event.customer || 'N/A'}<br>
                            Type: ${event.order_type || 'Direct'}
                        ` : `
                            <strong>${event.service || 'N/A'}</strong><br>
                            Client: ${event.client_name || 'N/A'}<br>
                            Staff: ${event.assigned_staff || 'N/A'}
                        `}
                    </div>
                    ${event.time ? `<div class="event-item-time">Time: ${formatTime(event.time)}</div>` : ''}
                    <div class="event-item-actions">
                        <button class="event-action-btn" onclick="event.stopPropagation(); viewEvent(${event.order_id || event.appointment_id}, '${event.type}')">View</button>
                        <button class="event-action-btn" onclick="event.stopPropagation(); editEvent(${event.order_id || event.appointment_id}, '${event.type}')">Edit</button>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    sidebar.classList.add('open');
}

function closeDayDetailsSidebar() {
    const sidebar = document.getElementById('day-details-sidebar');
    if (sidebar) {
        sidebar.classList.remove('open');
    }
}

function showEventDetails(id, type) {
    if (type === 'order') {
        window.location.href = `${baseUrl}admin-orders?order_id=${id}`;
    } else {
        window.location.href = `${baseUrl}admin-appointment?appointment_id=${id}`;
    }
}

function viewEvent(id, type) {
    showEventDetails(id, type);
}

function editEvent(id, type) {
    showEventDetails(id, type);
}

function updateFoundText() {
    const foundText = document.querySelector('.found-text');
    if (foundText) {
        const count = calendarEvents.length;
        foundText.textContent = `(${count} ${count === 1 ? 'event' : 'events'})`;
    }
}
