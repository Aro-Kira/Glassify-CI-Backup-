<section id="notifications" class="page">
    <div class="dash-tabs">
        <h2>Notifications</h2> 
        <h3>Stay Updated with your latest notifications</h3>
    </div>

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

