<section id="notifications" class="page">
    <div class="dash-tabs">
        <h2>Notifications</h2> 
        <h3>Stay Updated with your latest notifications</h3>
    </div>

    <div class="order-tabs">
        <div class="tab-links-container">
            <a href="#" class="tab-link active" data-target="all">
                All
            </a>
            <a href="#" class="tab-link" data-target="unread" id="unread-tab-link">
                Unread (<span id="unread-count"><?php echo isset($unread_count) ? $unread_count : 0; ?></span>)
            </a>
        </div>
    </div>
    
    <div class="notifications-list" id="notifications-list">
        <?php if (empty($notifications)): ?>
            <div class="notification-item empty-message">
                <div class="notification-details">
                    <p class="notification-text">No notifications available.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="notification-item empty-message" id="no-unread-message" style="display: none;">
                <div class="notification-details">
                    <p class="notification-text">No unread notifications.</p>
                </div>
            </div>
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

<script src="<?php echo base_url('assets/js/inventory-js/sales-notif-filter.js'); ?>"></script>
