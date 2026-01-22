/**
 * Update notification badge count for employee pages
 * Supports Admin, Sales Representative, and Inventory Officer
 */
function updateNotificationBadge() {
    // Determine which role we're on based on the badge ID
    const adminBadge = document.getElementById('admin-notification-count');
    const salesBadge = document.getElementById('sales-notification-count');
    const inventoryBadge = document.getElementById('inventory-notification-count');
    
    let badge = null;
    let endpoint = '';
    
    if (adminBadge) {
        badge = adminBadge;
        endpoint = 'admin-get-notification-count';
    } else if (salesBadge) {
        badge = salesBadge;
        endpoint = 'sales-get-notification-count';
    } else if (inventoryBadge) {
        badge = inventoryBadge;
        endpoint = 'inventory-get-notification-count';
    }
    
    if (!badge || !endpoint) {
        return; // No badge found, exit
    }
    
    // Get base URL from window or construct it
    let baseUrl = window.BASE_URL || '';
    if (!baseUrl.endsWith('/')) {
        baseUrl += '/';
    }
    
    // Fetch notification count
    fetch(baseUrl + endpoint)
        .then(response => {
            // Check if response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Response is not JSON, likely HTML error page
                return response.text().then(text => {
                    console.warn('Notification endpoint returned non-JSON response:', text.substring(0, 200));
                    throw new Error('Server returned non-JSON response');
                });
            }
            
            return response.json();
        })
        .then(data => {
            if (data && data.status === 'success') {
                const count = data.count;
                const display = data.display;
                
                if (count > 0) {
                    badge.textContent = display;
                    badge.style.display = 'flex';
                    
                    // Add class for "99+" to adjust styling
                    if (display === '99+') {
                        badge.classList.add('badge-overflow');
                    } else {
                        badge.classList.remove('badge-overflow');
                    }
                } else {
                    badge.style.display = 'none';
                    badge.classList.remove('badge-overflow');
                }
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(error => {
            // Only log error if it's not a network error or expected error
            if (error.message && !error.message.includes('Failed to fetch') && !error.message.includes('NetworkError')) {
                console.error('Error updating notification badge:', error);
            }
            badge.style.display = 'none';
        });
}

// Update badge on page load
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationBadge();
    
    // Update badge every 30 seconds
    setInterval(updateNotificationBadge, 30000);
});

// Make function globally available for manual updates
window.updateNotificationBadge = updateNotificationBadge;

