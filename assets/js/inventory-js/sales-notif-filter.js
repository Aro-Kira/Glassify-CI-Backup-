// Sales Notifications Filter
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-link');
    const notificationItems = document.querySelectorAll('.notification-item[data-status]');
    const noUnreadMessage = document.getElementById('no-unread-message');
    const unreadCountSpan = document.getElementById('unread-count');
    
    // Function to filter notifications
    function filterNotifications(target) {
        let visibleUnreadCount = 0;
        
        notificationItems.forEach(item => {
            const status = item.getAttribute('data-status');
            
            if (target === 'all') {
                item.style.display = 'flex';
                if (status === 'unread') {
                    visibleUnreadCount++;
                }
            } else if (target === 'unread') {
                if (status === 'unread') {
                    item.style.display = 'flex';
                    visibleUnreadCount++;
                } else {
                    item.style.display = 'none';
                }
            }
        });
        
        // Show/hide "No unread notifications" message
        if (target === 'unread') {
            if (visibleUnreadCount === 0 && noUnreadMessage) {
                noUnreadMessage.style.display = 'flex';
            } else if (noUnreadMessage) {
                noUnreadMessage.style.display = 'none';
            }
        } else {
            // Hide the message when showing all notifications
            if (noUnreadMessage) {
                noUnreadMessage.style.display = 'none';
            }
        }
        
        // Update active tab
        tabLinks.forEach(link => {
            if (link.getAttribute('data-target') === target) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }
    
    // Add click event listeners to tab links
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-target');
            filterNotifications(target);
        });
    });
    
    // Initialize: show all notifications by default
    filterNotifications('all');
});

