// Handle Approve/Disapprove Order button clicks
document.addEventListener('DOMContentLoaded', function() {
    // =============================
    // TOAST NOTIFICATION SYSTEM
    // =============================
    function showToast(message, type = 'info', duration = 3000) {
        const existingToasts = document.querySelectorAll('.toast-notification');
        existingToasts.forEach(toast => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        });

        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        
        const config = {
            success: { icon: '✓', bg: '#28a745', border: '#1e7e34' },
            error: { icon: '✕', bg: '#dc3545', border: '#c82333' },
            warning: { icon: '⚠', bg: '#ffc107', border: '#e0a800' },
            info: { icon: 'ℹ', bg: '#17a2b8', border: '#138496' }
        };
        
        const toastConfig = config[type] || config.info;
        
        toast.innerHTML = `
            <div class="toast-icon">${toastConfig.icon}</div>
            <div class="toast-message">${message}</div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        toast.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: ${toastConfig.bg};
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            max-width: 500px;
            animation: toastSlideIn 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            border-left: 4px solid ${toastConfig.border};
        `;
        
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                @keyframes toastSlideIn {
                    from { transform: translateX(400px); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes toastFadeOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(400px); opacity: 0; }
                }
                .toast-notification { transition: all 0.3s ease; }
                .toast-fade-out { animation: toastFadeOut 0.3s ease forwards; }
                .toast-icon { font-size: 20px; font-weight: bold; flex-shrink: 0; }
                .toast-message { flex: 1; font-size: 14px; line-height: 1.4; }
                .toast-close { background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; opacity: 0.8; transition: opacity 0.2s; flex-shrink: 0; }
                .toast-close:hover { opacity: 1; }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
        
        return toast;
    }

    function showConfirmModal(message, onConfirm, onCancel = null) {
        const existingModal = document.getElementById('confirm-modal-overlay');
        if (existingModal) existingModal.remove();
        
        const overlay = document.createElement('div');
        overlay.id = 'confirm-modal-overlay';
        overlay.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); z-index: 10001;
            display: flex; align-items: center; justify-content: center;
            animation: fadeIn 0.2s ease;
        `;
        
        const modal = document.createElement('div');
        modal.style.cssText = `
            background: white; border-radius: 12px; padding: 30px;
            max-width: 450px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease;
        `;
        
        modal.innerHTML = `
            <h3 style="margin: 0 0 15px 0; font-size: 20px; color: #333; font-family: 'Montserrat', sans-serif;">Confirm Action</h3>
            <p style="margin: 0 0 25px 0; color: #666; font-size: 15px; line-height: 1.5;">${message}</p>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button id="confirm-cancel-btn" style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer; font-size: 14px; color: #666; transition: all 0.2s;">Cancel</button>
                <button id="confirm-ok-btn" style="padding: 10px 20px; border: none; background: #dc3545; color: white; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">Confirm</button>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        if (!document.getElementById('modal-styles')) {
            const style = document.createElement('style');
            style.id = 'modal-styles';
            style.textContent = `
                @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
                @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
                #confirm-cancel-btn:hover { background: #f5f5f5; }
                #confirm-ok-btn:hover { background: #c82333; }
            `;
            document.head.appendChild(style);
        }
        
        const cancelBtn = overlay.querySelector('#confirm-cancel-btn');
        const okBtn = overlay.querySelector('#confirm-ok-btn');
        
        cancelBtn.addEventListener('click', () => {
            overlay.style.animation = 'fadeIn 0.2s ease reverse';
            setTimeout(() => overlay.remove(), 200);
            if (onCancel) onCancel();
        });
        
        okBtn.addEventListener('click', () => {
            overlay.style.animation = 'fadeIn 0.2s ease reverse';
            setTimeout(() => overlay.remove(), 200);
            if (onConfirm) onConfirm();
        });
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.animation = 'fadeIn 0.2s ease reverse';
                setTimeout(() => overlay.remove(), 200);
                if (onCancel) onCancel();
            }
        });
        
        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                overlay.style.animation = 'fadeIn 0.2s ease reverse';
                setTimeout(() => overlay.remove(), 200);
                if (onCancel) onCancel();
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
    }

    function showPromptModal(message, placeholder = '', onConfirm, onCancel = null) {
        const existingModal = document.getElementById('prompt-modal-overlay');
        if (existingModal) existingModal.remove();
        
        const overlay = document.createElement('div');
        overlay.id = 'prompt-modal-overlay';
        overlay.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); z-index: 10001;
            display: flex; align-items: center; justify-content: center;
            animation: fadeIn 0.2s ease;
        `;
        
        const modal = document.createElement('div');
        modal.style.cssText = `
            background: white; border-radius: 12px; padding: 30px;
            max-width: 450px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease;
        `;
        
        modal.innerHTML = `
            <h3 style="margin: 0 0 15px 0; font-size: 20px; color: #333; font-family: 'Montserrat', sans-serif;">Input Required</h3>
            <p style="margin: 0 0 15px 0; color: #666; font-size: 15px; line-height: 1.5;">${message}</p>
            <input type="text" id="prompt-input" placeholder="${placeholder}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; margin-bottom: 20px;">
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button id="prompt-cancel-btn" style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer; font-size: 14px; color: #666; transition: all 0.2s;">Cancel</button>
                <button id="prompt-ok-btn" style="padding: 10px 20px; border: none; background: #dc3545; color: white; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">Confirm</button>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        const input = modal.querySelector('#prompt-input');
        input.focus();
        
        const cancelBtn = overlay.querySelector('#prompt-cancel-btn');
        const okBtn = overlay.querySelector('#prompt-ok-btn');
        
        const closeModal = () => {
            overlay.style.animation = 'fadeIn 0.2s ease reverse';
            setTimeout(() => overlay.remove(), 200);
        };
        
        cancelBtn.addEventListener('click', () => {
            closeModal();
            if (onCancel) onCancel();
        });
        
        okBtn.addEventListener('click', () => {
            const value = input.value.trim();
            closeModal();
            if (onConfirm) onConfirm(value);
        });
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const value = input.value.trim();
                closeModal();
                if (onConfirm) onConfirm(value);
            }
        });
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal();
                if (onCancel) onCancel();
            }
        });
        
        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                closeModal();
                if (onCancel) onCancel();
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
    }

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
        const approveBtn = document.getElementById('approved-approve-btn') || approvedPopup.querySelector('.approved-btn');
        if (approveBtn) {
            approveBtn.addEventListener('click', function() {
                const orderId = getOrderIdFromPopup(approvedPopup);
                if (!orderId) {
                    showToast('Order ID not found', 'warning');
                    return;
                }
                
                // Confirm action
                showConfirmModal('Are you sure you want to approve this order? The customer will be notified and can proceed with payment.', () => {
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
                    .then(response => {
                        if (!response.ok) {
                            // If response is not OK, try to get error message
                            return response.text().then(text => {
                                let errorData;
                                try {
                                    errorData = JSON.parse(text);
                                } catch (e) {
                                    errorData = { success: false, message: `Server error (${response.status}): ${response.statusText}` };
                                }
                                throw new Error(errorData.message || 'Server error');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast(data.message || 'Order approved successfully!', 'success');
                            // Close popup
                            approvedPopup.style.display = 'none';
                            // Reload page to refresh order list
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showToast(data.message || 'Failed to approve order. Please try again.', 'error');
                            approveBtn.disabled = false;
                            approveBtn.textContent = 'Approve Order';
                        }
                    })
                    .catch(error => {
                        console.error('Error approving order:', error);
                        const errorMessage = error.message || 'An error occurred while approving the order. Please try again.';
                        showToast(errorMessage, 'error');
                        approveBtn.disabled = false;
                        approveBtn.textContent = 'Approve Order';
                    });
                });
            });
        }
    }
    
    // Helper function to process disapproval
    function processDisapproval(orderId, reason, disapproveBtn, popup) {
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
        .then(response => {
            if (!response.ok) {
                // If response is not OK, try to get error message
                return response.text().then(text => {
                    let errorData;
                    try {
                        errorData = JSON.parse(text);
                    } catch (e) {
                        errorData = { success: false, message: `Server error (${response.status}): ${response.statusText}` };
                    }
                    throw new Error(errorData.message || 'Server error');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Order disapproved and cancelled successfully! The customer has been notified.', 'success');
                // Close popup
                popup.style.display = 'none';
                // Reload page to refresh order list
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(data.message || 'Failed to disapprove order. Please try again.', 'error');
                disapproveBtn.disabled = false;
                disapproveBtn.textContent = 'Disapprove Order';
            }
        })
        .catch(error => {
            console.error('Error disapproving order:', error);
            const errorMessage = error.message || 'An error occurred while disapproving the order. Please try again.';
            showToast(errorMessage, 'error');
            disapproveBtn.disabled = false;
            disapproveBtn.textContent = 'Disapprove Order';
        });
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
        const disapproveBtn = document.getElementById('disapproved-disapprove-btn') || disapprovedPopup.querySelector('.disapproved-btn');
        if (disapproveBtn) {
            disapproveBtn.addEventListener('click', function() {
                const orderIdElement = document.getElementById('disapproved-order-id');
                if (!orderIdElement) {
                    showToast('Order ID not found', 'warning');
                    return;
                }
                
                const orderId = orderIdElement.textContent.trim();
                if (!orderId) {
                    showToast('Order ID is required', 'warning');
                    return;
                }
                
                // Get admin notes/reason if available
                const notesElement = document.getElementById('disapproved-notes');
                const adminReason = notesElement ? notesElement.value.trim() : '';
                
                // Prompt for additional reason (optional, since admin already provided reason)
                showPromptModal('Admin has already disapproved this order. Add any additional reason (optional, press Cancel to use admin\'s reason):', 'Enter additional reason...', (additionalReason) => {
                    let reason = 'Order disapproved by Admin and finalized by Sales Representative';
                    
                    if (additionalReason && additionalReason.trim()) {
                        reason = adminReason ? adminReason + ' | Additional: ' + additionalReason : additionalReason;
                    } else if (adminReason) {
                        reason = adminReason;
                    }
                    
                    // Confirm action - emphasize this will notify customer and cancel order
                    showConfirmModal('Are you sure you want to finalize the disapproval of this order?\n\nThis will:\n- Immediately notify the customer\n- Cancel the order permanently\n- Move it to cancelled orders\n\nThis action cannot be undone.', () => {
                        processDisapproval(orderId, reason, disapproveBtn, disapprovedPopup);
                    });
                });
            });
        }
    }
});

