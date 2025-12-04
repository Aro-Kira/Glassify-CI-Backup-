// Handle Approve/Disapprove Order button clicks
document.addEventListener('DOMContentLoaded', function() {
    // Helper function to get order ID from any popup
    function getOrderIdFromPopup(popup) {
        // Try different possible order ID element IDs
        const possibleIds = [
            'approved-order-id',
            'disapproved-order-id',
            'awaiting-order-id',
            'popup-order-id'
        ];
        
        for (const id of possibleIds) {
            const element = document.getElementById(id);
            if (element && element.textContent.trim()) {
                return element.textContent.trim();
            }
        }
        return null;
    }
    
    // Approve Order button handler
    const approvedPopup = document.getElementById('approvedPopup');
    if (approvedPopup) {
        const approveBtn = approvedPopup.querySelector('.approved-btn');
        if (approveBtn) {
            approveBtn.addEventListener('click', function() {
                const orderId = getOrderIdFromPopup(approvedPopup);
                if (!orderId) {
                    alert('Order ID not found');
                    return;
                }
                
                // Confirm action
                if (!confirm('Are you sure you want to approve this order? The customer will be notified and can proceed with payment.')) {
                    return;
                }
                
                // Disable button during request
                approveBtn.disabled = true;
                approveBtn.textContent = 'Approving...';
                
                // Send AJAX request
                fetch(base_url + 'SalesCon/approve_order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'order_id=' + encodeURIComponent(orderId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Order approved successfully!');
                        // Close popup
                        approvedPopup.style.display = 'none';
                        // Reload page to refresh order list
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to approve order. Please try again.');
                        approveBtn.disabled = false;
                        approveBtn.textContent = 'Approve Order';
                    }
                })
                .catch(error => {
                    console.error('Error approving order:', error);
                    alert('An error occurred while approving the order. Please try again.');
                    approveBtn.disabled = false;
                    approveBtn.textContent = 'Approve Order';
                });
            });
        }
    }
    
    // Disapprove Order button handler - works for all popups
    function setupDisapproveHandler(popupId, orderIdElementId) {
        const popup = document.getElementById(popupId);
        if (popup) {
            const disapproveBtn = popup.querySelector('.disapproved-btn');
            if (disapproveBtn) {
                disapproveBtn.addEventListener('click', function() {
                    // Try to get order ID from the specific element or use helper
                    let orderId = null;
                    if (orderIdElementId) {
                        const element = document.getElementById(orderIdElementId);
                        if (element) {
                            orderId = element.textContent.trim();
                        }
                    }
                    
                    // Fallback to helper function
                    if (!orderId) {
                        orderId = getOrderIdFromPopup(popup);
                    }
                    
                    if (!orderId) {
                        alert('Order ID not found');
                        return;
                    }
                    
                    // Prompt for reason
                    const reason = prompt('Please provide a reason for disapproving this order:');
                    if (reason === null) {
                        return; // User cancelled
                    }
                    
                    if (!reason.trim()) {
                        alert('A reason is required to disapprove an order');
                        return;
                    }
                    
                    // Confirm action
                    if (!confirm('Are you sure you want to disapprove and cancel this order? The customer will be notified immediately.')) {
                        return;
                    }
                    
                    // Disable button during request
                    disapproveBtn.disabled = true;
                    disapproveBtn.textContent = 'Disapproving...';
                    
                    // Send AJAX request
                    fetch(base_url + 'SalesCon/disapprove_order', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'order_id=' + encodeURIComponent(orderId) + '&reason=' + encodeURIComponent(reason)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message || 'Order disapproved and cancelled successfully! The customer has been notified.');
                            // Close popup
                            popup.style.display = 'none';
                            // Reload page to refresh order list
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to disapprove order. Please try again.');
                            disapproveBtn.disabled = false;
                            disapproveBtn.textContent = 'Disapprove Order';
                        }
                    })
                    .catch(error => {
                        console.error('Error disapproving order:', error);
                        alert('An error occurred while disapproving the order. Please try again.');
                        disapproveBtn.disabled = false;
                        disapproveBtn.textContent = 'Disapprove Order';
                    });
                });
            }
        }
    }
    
    // Setup disapprove handlers - only for specific popups (not approvalPopup or awaitingPopup)
    // approvalPopup: Only has "Submit to Admin" button (no disapprove)
    // awaitingPopup: View-only, no action buttons
    // approvedPopup: Only has "Approve Order" button (no disapprove)
    // disapprovedPopup: Has "Disapprove Order" button to finalize admin's disapproval
    
    // Special handler for disapproved popup - this is when admin has already disapproved
    // Sales rep can finalize the disapproval, which will notify customer and cancel order
    const disapprovedPopup = document.getElementById('disapprovedPopup');
    if (disapprovedPopup) {
        const disapproveBtn = disapprovedPopup.querySelector('.disapproved-btn');
        if (disapproveBtn) {
            disapproveBtn.addEventListener('click', function() {
                const orderIdElement = document.getElementById('disapproved-order-id');
                if (!orderIdElement) {
                    alert('Order ID not found');
                    return;
                }
                
                const orderId = orderIdElement.textContent.trim();
                if (!orderId) {
                    alert('Order ID is required');
                    return;
                }
                
                // Get admin notes/reason if available
                const notesElement = document.getElementById('disapproved-notes');
                const adminReason = notesElement ? notesElement.value.trim() : '';
                
                // Prompt for additional reason (optional, since admin already provided reason)
                const additionalReason = prompt('Admin has already disapproved this order. Add any additional reason (optional, press Cancel to use admin\'s reason):');
                let reason = 'Order disapproved by Admin and finalized by Sales Representative';
                
                if (additionalReason !== null && additionalReason.trim()) {
                    reason = adminReason ? adminReason + ' | Additional: ' + additionalReason : additionalReason;
                } else if (adminReason) {
                    reason = adminReason;
                }
                
                // Confirm action - emphasize this will notify customer and cancel order
                if (!confirm('Are you sure you want to finalize the disapproval of this order?\n\nThis will:\n- Immediately notify the customer\n- Cancel the order permanently\n- Move it to cancelled orders\n\nThis action cannot be undone.')) {
                    return;
                }
                
                // Disable button during request
                disapproveBtn.disabled = true;
                disapproveBtn.textContent = 'Disapproving...';
                
                // Send AJAX request
                fetch(base_url + 'SalesCon/disapprove_order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'order_id=' + encodeURIComponent(orderId) + '&reason=' + encodeURIComponent(reason)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Order disapproved and cancelled successfully! The customer has been notified.');
                        // Close popup
                        disapprovedPopup.style.display = 'none';
                        // Reload page to refresh order list
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to disapprove order. Please try again.');
                        disapproveBtn.disabled = false;
                        disapproveBtn.textContent = 'Disapprove Order';
                    }
                })
                .catch(error => {
                    console.error('Error disapproving order:', error);
                    alert('An error occurred while disapproving the order. Please try again.');
                    disapproveBtn.disabled = false;
                    disapproveBtn.textContent = 'Disapprove Order';
                });
            });
        }
    }
});

