<section id="notifications" class="page">
    <div class="dash-tabs">
        <h2>Notifications</h2> 
        <h3>Stay Updated with your latest notifications</h3>
    </div>

    <!-- Filter tabs removed - showing all notifications by default -->
    
    <div class="notifications-list" id="notifications-list">
        <?php if (empty($notifications)): ?>
            <div class="notification-item empty-message">
                <div class="notification-details">
                    <p class="notification-text">No notifications available.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <?php
                // Get icon from database (already stored)
                $icon = $notif->Icon ?? 'fa-info-circle';
                
                // Determine status
                $status = isset($notif->Status) ? strtolower($notif->Status) : 'read';
                if ($status === 'unread') {
                    $status = 'unread';
                } else {
                    $status = 'read';
                }
                
                // Format title and message
                $title = $notif->Action ?? 'Notification';
                $message = $notif->Description ?? '';
                ?>
                <div class="notification-item <?php echo $status === 'unread' ? 'unread-item' : ''; ?>" data-status="<?php echo $status; ?>">
                    <i class="fas <?php echo htmlspecialchars($icon); ?> notification-icon"></i>
                    <div class="notification-details">
                        <p class="notification-text">
                            <strong><?php echo htmlspecialchars($title); ?>:</strong> 
                            <?php echo htmlspecialchars($message); ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script>
// Mark all notifications as viewed when sales rep opens the notification page
document.addEventListener('DOMContentLoaded', function() {
    // Get base URL
    let baseUrl = window.BASE_URL || '';
    if (!baseUrl.endsWith('/')) {
        baseUrl += '/';
    }
    
    // Mark all notifications as viewed
    fetch(baseUrl + 'sales-mark-notifications-viewed', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update the badge immediately after marking as viewed
            if (typeof updateNotificationBadge === 'function') {
                updateNotificationBadge();
            }
        }
    })
    .catch(error => {
        console.error('Error marking notifications as viewed:', error);
    });
});
</script>
