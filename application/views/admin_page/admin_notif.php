<section id="notifications" class="page">
    <div class="dash-tabs">
        <h2>Notifications</h2> 
        <h3>Stay Updated with your latest notifications</h3>
    </div>

    <style>
    .notif-pagination{display:flex;gap:8px;justify-content:center;align-items:center;margin-top:18px;flex-wrap:wrap}
    .btn-pager{background:#003049;color:#fff;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;text-decoration:none;transition:all 0.3s ease}
    .btn-pager:hover:not([disabled]){background:#001d2e;transform:translateY(-2px);box-shadow:0 4px 8px rgba(0,0,0,0.2)}
    .btn-pager[disabled], .btn-pager[disabled]:hover{background:#cbd5e1;color:#4b5563;cursor:default;transform:none;box-shadow:none}
    .page-info{color:#374151;font-weight:600;padding:0 8px;min-width:150px;text-align:center}
    .notification-item{transition:all 0.2s ease}
    .notification-item:hover{background-color:#f8f9fa;border-radius:4px}
    </style>

    <!-- Filter tabs removed - showing all notifications by default -->
    
    <div class="notifications-list" id="notifications-list">
        <?php if (empty($notifications) || !isset($notifications)): ?>
            <div class="notification-item empty-message">
                <div class="notification-details">
                    <p class="notification-text">No notifications available.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <?php
                // Get icon from notification object (determined by controller)
                $icon = $notif->Icon ?? 'fa-info-circle';
                
                // Determine status (system_activity_log doesn't have Status field, so we'll treat all as read for now)
                $status = isset($notif->Status) ? strtolower($notif->Status) : 'read';
                
                // Format title and message
                $title = $notif->Action ?? 'Notification';
                $message = $notif->Description ?? '';
                $timestamp = $notif->Timestamp ?? date('Y-m-d H:i:s');
                ?>
                <div class="notification-item <?php echo $status === 'unread' ? 'unread-item' : ''; ?>" data-status="<?php echo $status; ?>">
                    <i class="fas <?php echo htmlspecialchars($icon); ?> notification-icon"></i>
                    <div class="notification-details">
                        <p class="notification-text">
                            <strong><?php echo htmlspecialchars($title); ?>:</strong> 
                            <?php echo htmlspecialchars($message); ?>
                        </p>
                        <small class="notification-timestamp" style="color:#9ca3af;font-size:0.85em;margin-top:4px;display:block">
                            <?php echo htmlspecialchars($timestamp); ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination Control -->
    <?php if (!empty($pagination) && isset($pagination['total_pages']) && $pagination['total_pages'] > 1): ?>
    <div class="notif-pagination">
        <!-- Previous Button -->
        <?php if ($pagination['current_page'] > 1): ?>
            <a class="btn-pager" href="?page=1" title="First Page">«</a>
            <a class="btn-pager" href="?page=<?php echo $pagination['current_page'] - 1; ?>" title="Previous Page">‹</a>
        <?php else: ?>
            <button class="btn-pager" disabled title="First Page">«</button>
            <button class="btn-pager" disabled title="Previous Page">‹</button>
        <?php endif; ?>

        <!-- Page Information -->
        <span class="page-info">
            Page <?php echo $pagination['current_page']; ?> of <?php echo $pagination['total_pages']; ?>
            <br>
            <small style="font-size:0.9em;color:#6b7280">
                Showing <?php echo (($pagination['current_page'] - 1) * $pagination['per_page']) + 1; ?> - 
                <?php echo min($pagination['current_page'] * $pagination['per_page'], $pagination['total_items']); ?> 
                of <?php echo $pagination['total_items']; ?> notifications
            </small>
        </span>

        <!-- Next Button -->
        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
            <a class="btn-pager" href="?page=<?php echo $pagination['current_page'] + 1; ?>" title="Next Page">›</a>
            <a class="btn-pager" href="?page=<?php echo $pagination['total_pages']; ?>" title="Last Page">»</a>
        <?php else: ?>
            <button class="btn-pager" disabled title="Next Page">›</button>
            <button class="btn-pager" disabled title="Last Page">»</button>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>

<script>
// Mark all notifications as viewed when admin opens the notification page
document.addEventListener('DOMContentLoaded', function() {
    // Get base URL
    let baseUrl = window.BASE_URL || '';
    if (!baseUrl.endsWith('/')) {
        baseUrl += '/';
    }
    
    // Mark all notifications as viewed
    fetch(baseUrl + 'admin-mark-notifications-viewed', {
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
    
    // Add keyboard navigation for pagination
    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowRight' && event.ctrlKey) {
            // Ctrl + Right Arrow - Next Page
            const nextLink = document.querySelector('.notif-pagination a[title="Next Page"]');
            if (nextLink) nextLink.click();
        } else if (event.key === 'ArrowLeft' && event.ctrlKey) {
            // Ctrl + Left Arrow - Previous Page
            const prevLink = document.querySelector('.notif-pagination a[title="Previous Page"]');
            if (prevLink) prevLink.click();
        }
    });
});
</script>

