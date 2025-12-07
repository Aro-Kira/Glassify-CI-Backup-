// Calendar functionality for appointments page
let currentDate = new Date();
let appointmentsData = [];

// Initialize calendar on page load
document.addEventListener('DOMContentLoaded', function() {
    renderCalendar();
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
        if (!apt.appointment_date) return false;
        const aptDate = apt.appointment_date.split(' ')[0]; // Get date part only
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
                renderCalendar(); // Re-render calendar with appointments
            } else {
                console.error('Error loading appointments:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
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


