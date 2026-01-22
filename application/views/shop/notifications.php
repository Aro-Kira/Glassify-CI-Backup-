<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/list_product.css'); ?>">
<style>
.notifications-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.notifications-header {
    margin-bottom: 30px;
}

.notifications-header h2 {
    font-size: 32px;
    color: #003049;
    margin-bottom: 10px;
}

.notifications-header p {
    color: #6b7280;
    font-size: 16px;
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    align-items: flex-start;
    gap: 15px;
    transition: all 0.3s ease;
    border-left: 4px solid #e5e7eb;
}

.notification-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.notification-item.unread {
    border-left-color: #003049;
    background: #f8f9fa;
}

.notification-icon {
    font-size: 24px;
    color: #003049;
    margin-top: 5px;
    min-width: 30px;
    text-align: center;
}

/* Match icon color to notification type badge */
.notification-item[data-type="order"] .notification-icon,
.notification-item .notification-icon.order-icon {
    color: #1e40af;
}

.notification-item[data-type="payment"] .notification-icon,
.notification-item .notification-icon.payment-icon {
    color: #065f46;
}

.notification-item[data-type="delivery"] .notification-icon,
.notification-item .notification-icon.delivery-icon {
    color: #92400e;
}

.notification-item[data-type="system"] .notification-icon,
.notification-item[data-type="general"] .notification-icon,
.notification-item .notification-icon.system-icon {
    color: #4b5563;
}

.notification-details {
    flex: 1;
}

.notification-title {
    font-size: 18px;
    font-weight: 600;
    color: #003049;
    margin-bottom: 8px;
}

.notification-message {
    font-size: 15px;
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 10px;
}

.notification-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 13px;
    color: #6b7280;
}

.notification-type {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    background: #e5e7eb;
    color: #4b5563;
}

.notification-type.order {
    background: #dbeafe;
    color: #1e40af;
}

.notification-type.payment {
    background: #d1fae5;
    color: #065f46;
}

.notification-type.delivery {
    background: #fef3c7;
    color: #92400e;
}

.notification-type.system {
    background: #e5e7eb;
    color: #4b5563;
}

.notification-action {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
}

.btn-request-date-change {
    background: #003049;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-request-date-change:hover {
    background: #004d6b;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 48, 73, 0.3);
}

.btn-request-date-change:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.empty-state-icon {
    font-size: 64px;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 24px;
    color: #374151;
    margin-bottom: 10px;
}

.empty-state p {
    font-size: 16px;
    color: #6b7280;
}

@media (max-width: 768px) {
    .notifications-page {
        padding: 20px 10px;
    }
    
    .notification-item {
        padding: 15px;
    }
    
    .notification-title {
        font-size: 16px;
    }
    
    .notification-message {
        font-size: 14px;
    }
}
</style>

<section class="notifications-page">
    <div class="notifications-header">
        <h2>Notifications</h2>
        <p>Stay updated with your order status and important updates</p>
    </div>

    <div class="notifications-list" id="notifications-list">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash empty-state-icon"></i>
                <h3>No Notifications</h3>
                <p>You don't have any notifications yet. We'll notify you about order updates and important information.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <?php
                $status = strtolower($notif->Status ?? 'read');
                $icon = $notif->Icon ?? 'fa-info-circle';
                $type_class = strtolower($notif->Type ?? 'general');
                $is_unread = ($status === 'unread');
                ?>
                <div class="notification-item <?= $is_unread ? 'unread' : '' ?>" data-notification-id="<?= $notif->NotificationID ?>" data-type="<?= $type_class ?>">
                    <i class="fas <?= htmlspecialchars($icon) ?> notification-icon <?= $type_class ?>-icon"></i>
                    <div class="notification-details">
                        <div class="notification-title"><?= htmlspecialchars($notif->Title) ?></div>
                        <div class="notification-message"><?= htmlspecialchars($notif->Message) ?></div>
                        <div class="notification-meta">
                            <span class="notification-type <?= $type_class ?>"><?= htmlspecialchars($notif->Type) ?></span>
                            <span><?= date('M j, Y g:i A', strtotime($notif->Created_Date)) ?></span>
                        </div>
                        <?php if (!empty($notif->ActionData)): ?>
                            <?php 
                            $action = json_decode($notif->ActionData);
                            if ($action && $action->type === 'installation_date_change'): 
                                $allowed_until = isset($action->allowed_until) ? strtotime($action->allowed_until) : strtotime('+7 days');
                                $can_change = time() <= $allowed_until;
                            ?>
                                <div class="notification-action">
                                    <button class="btn-request-date-change" 
                                            data-order-id="<?= htmlspecialchars($action->order_id ?? '') ?>"
                                            data-current-date="<?= htmlspecialchars($action->current_date ?? '') ?>"
                                            data-allowed-until="<?= htmlspecialchars($action->allowed_until ?? '') ?>"
                                            <?= !$can_change ? 'disabled title="Date change request expired"' : '' ?>>
                                        <i class="fas fa-calendar-alt"></i> 
                                        <?= $can_change ? 'Request Date Change' : 'Date Change Expired' ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Date Change Request Modal -->
<div id="dateChangeModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background: white; margin: 5% auto; padding: 30px; border-radius: 12px; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
        <span class="close-modal" style="float: right; font-size: 28px; font-weight: bold; cursor: pointer; color: #aaa;">&times;</span>
        <h2 style="color: #003049; margin-bottom: 20px;">Request Installation Date Change</h2>
        <p style="color: #6b7280; margin-bottom: 20px;">Please select a new installation date within 7 days of the original date.</p>
        <form id="dateChangeForm">
            <input type="hidden" id="modal-order-id" name="order_id">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500;">Current Date:</label>
                <input type="text" id="modal-current-date" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9fafb;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500;">New Date:</label>
                <input type="date" id="modal-new-date" name="new_date" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                <small style="color: #6b7280; display: block; margin-top: 5px;">Must be within 7 days of original date</small>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="close-modal" style="padding: 10px 20px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 20px; background: #003049; color: white; border: none; border-radius: 6px; cursor: pointer;">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
// Mark all notifications as read when customer opens the page
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?= base_url(); ?>';
    
    // Mark all notifications as read
    fetch(baseUrl + 'ShopCon/mark_notifications_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update UI - remove unread styling
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            
            // Update notification badge if it exists
            if (typeof updateNotificationBadge === 'function') {
                updateNotificationBadge();
            }
        }
    })
    .catch(error => {
        console.error('Error marking notifications as read:', error);
    });
    
    // Handle date change request buttons
    document.querySelectorAll('.btn-request-date-change').forEach(btn => {
        if (!btn.disabled) {
            btn.addEventListener('click', function() {
                const orderId = this.dataset.orderId;
                const currentDate = this.dataset.currentDate;
                const allowedUntil = this.dataset.allowedUntil;
                
                openDateChangeModal(orderId, currentDate, allowedUntil);
            });
        }
    });
    
    // Modal close handlers
    document.querySelectorAll('.close-modal').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            document.getElementById('dateChangeModal').style.display = 'none';
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('dateChangeModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Handle form submission
    document.getElementById('dateChangeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitDateChangeRequest(baseUrl);
    });
});

function openDateChangeModal(orderId, currentDate, allowedUntil) {
    const modal = document.getElementById('dateChangeModal');
    const orderIdInput = document.getElementById('modal-order-id');
    const currentDateInput = document.getElementById('modal-current-date');
    const newDateInput = document.getElementById('modal-new-date');
    
    // Format current date for display
    if (currentDate) {
        const date = new Date(currentDate);
        currentDateInput.value = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    } else {
        currentDateInput.value = 'Not set';
    }
    
    orderIdInput.value = orderId;
    
    // Set min and max dates for date picker
    const today = new Date();
    const maxDate = new Date(allowedUntil);
    today.setHours(0, 0, 0, 0);
    maxDate.setHours(23, 59, 59, 999);
    
    newDateInput.min = today.toISOString().split('T')[0];
    newDateInput.max = maxDate.toISOString().split('T')[0];
    
    modal.style.display = 'block';
}

function submitDateChangeRequest(baseUrl) {
    const form = document.getElementById('dateChangeForm');
    const formData = new FormData(form);
    
    fetch(baseUrl + 'ShopCon/request_installation_date_change', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Date change request submitted successfully! We will contact you to confirm the new date.');
            document.getElementById('dateChangeModal').style.display = 'none';
            form.reset();
        } else {
            alert('Error: ' + (data.message || 'Failed to submit request'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>
