document.addEventListener('DOMContentLoaded', function () {
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

    const actionMenu = document.getElementById('actionMenu');
    const actionCells = document.querySelectorAll('.action-cell');
    const popup = document.getElementById('productPopup');
    const closePopup = document.getElementById('closePopup');
    const receiptButtons = document.querySelectorAll('.receipt-btn');
    const viewReceiptLink = document.querySelector('#actionMenu li:first-child a'); // "View Receipt"

    let activeRow = null; // keep track of the selected row

    // Store the active cell reference
    let activeCell = null;
    
    // Store current order ID for "Mark as Paid" functionality
    let currentOrderId = null;

    // Function to update action menu position
    function updateActionMenuPosition(cell) {
        if (!actionMenu || !cell) return;
        
        const rect = cell.getBoundingClientRect();
        // Get the actual menu width after it's displayed
        actionMenu.style.display = 'block';
        const menuWidth = actionMenu.offsetWidth || 150;
        
        // Position menu directly below the action button, aligned to the right edge of the cell
        actionMenu.style.position = 'fixed';
        actionMenu.style.top = `${rect.bottom + 2}px`; // 2px gap below button
        // Align menu to the right edge of the action cell
        actionMenu.style.left = `${rect.right - menuWidth}px`;
        actionMenu.style.zIndex = '1000';
    }

    // Action menu logic
    actionCells.forEach(cell => {
        cell.addEventListener('click', function (e) {
            e.stopPropagation();
            activeRow = cell.closest('tr'); // save the clicked row
            activeCell = cell; // save the clicked cell

            updateActionMenuPosition(cell);
            actionMenu.style.display = 'block';
            actionMenu.classList.remove('hidden');
        });
    });

    // Update menu position on scroll - real-time updates
    let isScrolling = false;
    let scrollTimeout = null;
    
    function handleScroll() {
        if (actionMenu && actionMenu.style.display === 'block' && activeCell) {
            if (!isScrolling) {
                isScrolling = true;
                requestAnimationFrame(function updatePosition() {
                    if (activeCell && actionMenu.style.display === 'block') {
                        const rect = activeCell.getBoundingClientRect();
                        
                        // Check if cell is still visible
                        if (rect.top < window.innerHeight && rect.bottom > 0) {
                            // Get the actual menu width
                            const menuWidth = actionMenu.offsetWidth || 150;
                            // Position menu directly below the action button, aligned to the right edge
                            actionMenu.style.top = `${rect.bottom + 2}px`;
                            actionMenu.style.left = `${rect.right - menuWidth}px`;
                        } else {
                            // Hide menu if cell is out of view
                            actionMenu.style.display = 'none';
                            actionMenu.classList.add('hidden');
                        }
                    }
                    isScrolling = false;
                });
            }
        }
    }
    
    // Handle window scroll
    window.addEventListener('scroll', handleScroll, { passive: true });
    
    // Also handle scroll within any scrollable containers (like table containers)
    const scrollableContainers = document.querySelectorAll('.table-container, .payment-table-container, .content-area, main');
    scrollableContainers.forEach(container => {
        container.addEventListener('scroll', handleScroll, { passive: true });
    });

    // Also update on window resize
    window.addEventListener('resize', function() {
        if (actionMenu && actionMenu.style.display === 'block' && activeCell) {
            updateActionMenuPosition(activeCell);
        }
    });

    // Hide action menu when clicking outside or on any menu item
    document.addEventListener('click', function (e) {
        // Hide menu if clicking outside
        if (!actionMenu.contains(e.target) && !e.target.closest('.action-cell')) {
            actionMenu.style.display = 'none';
            actionMenu.classList.add('hidden');
        }
        // Hide menu if clicking on any menu item
        if (actionMenu.contains(e.target) && e.target.tagName === 'A') {
            actionMenu.style.display = 'none';
            actionMenu.classList.add('hidden');
        }
    });

    // 👉 Shared function: open popup with row data - fetches from database
    function openReceiptPopup(row) {
        if (!row) return;

        const orderId = row.cells[1].textContent.trim();
        
        // Store order ID for "Mark as Paid" functionality
        currentOrderId = orderId;
        
        // Show popup immediately with order ID
        if (!popup) {
            console.error('Popup element not found');
            return;
        }
        
        const popupOrderIdEl = document.getElementById("popupOrderId");
        if (popupOrderIdEl) {
            popupOrderIdEl.textContent = orderId;
        }
        popup.style.display = 'flex';
        actionMenu.style.display = 'none'; // hide menu
        
        // Fetch payment details from database (Admin version)
        fetch(base_url + 'AdminCon/get_payment_details', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'order_id=' + encodeURIComponent(orderId)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                const paymentData = data.data;
                
                // Wait a tiny bit to ensure popup is fully rendered
                setTimeout(() => {
                    // Fill popup fields with database data - from payment table
                    const popupCustomerEl = document.getElementById("popupCustomer");
                    if (popupCustomerEl) {
                        popupCustomerEl.textContent = paymentData.customer_name || 'N/A';
                    } else {
                        console.error('popupCustomer element not found');
                    }
                    
                    const popupPriceEl = document.getElementById("popupPrice");
                    if (popupPriceEl) {
                        // Set price from database payment table
                        popupPriceEl.value = parseFloat(paymentData.amount || 0).toFixed(2);
                    } else {
                        console.error('popupPrice element not found');
                    }
                    
                    // Set payment method (Gcash or Cash) - from database payment table
                    let methodDisplay = 'Not Selected';
                    if (paymentData.payment_method === 'E-Wallet') {
                        methodDisplay = 'Gcash';
                    } else if (paymentData.payment_method === 'Cash on Delivery') {
                        methodDisplay = 'Cash';
                    }
                    
                    const methodFieldEl = document.querySelector(".method-field");
                    if (methodFieldEl) {
                        // Update method field with data from payment table
                        methodFieldEl.innerHTML = `<label>Method: <span id="popupMethod">${methodDisplay}</span></label>`;
                    } else {
                        console.error('method-field element not found');
                    }

                    // Set receipt image (priority - show receipt if available)
                    const receiptImg = document.getElementById("popupReceiptImage");
                    if (receiptImg) {
                        if (paymentData.receipt_path) {
                            // Check if it's a full URL or relative path
                            let receiptUrl = paymentData.receipt_path;
                            if (!paymentData.receipt_path.startsWith('http://') && !paymentData.receipt_path.startsWith('https://')) {
                                // It's a relative path, check if it needs base_url
                                if (paymentData.receipt_path.startsWith('uploads/') || paymentData.receipt_path.startsWith('assets/')) {
                                    receiptUrl = base_url + paymentData.receipt_path;
                                } else {
                                    receiptUrl = base_url + 'uploads/' + paymentData.receipt_path;
                                }
                            }
                            receiptImg.src = receiptUrl;
                            receiptImg.style.display = 'block';
                            receiptImg.onerror = function() {
                                // If receipt image fails to load, hide it and show product image instead
                                this.style.display = 'none';
                                showProductImage(paymentData.product_image);
                            };
                        } else {
                            receiptImg.style.display = 'none';
                            // If no receipt, show product image
                            showProductImage(paymentData.product_image);
                        }
                    } else {
                        // Fallback: show product image if receipt image element doesn't exist
                        showProductImage(paymentData.product_image);
                    }
                    
                    // Helper function to show product image
                    function showProductImage(productImage) {
                        const productImg = document.getElementById("popupProductImage");
                        if (productImg && productImage) {
                            // Check if it's a full URL or relative path
                            let imageUrl = productImage;
                            if (!productImage.startsWith('http://') && !productImage.startsWith('https://')) {
                                // It's a relative path, check if it needs base_url
                                if (productImage.startsWith('uploads/') || productImage.startsWith('assets/')) {
                                    imageUrl = base_url + productImage;
                                } else {
                                    imageUrl = base_url + 'uploads/' + productImage;
                                }
                            }
                            productImg.src = imageUrl;
                            productImg.style.display = 'block';
                            productImg.onerror = function() {
                                // If image fails to load, hide it
                                this.style.display = 'none';
                            };
                        } else if (productImg) {
                            productImg.style.display = 'none';
                        }
                    }
                }, 10); // Small delay to ensure popup is rendered
            } else {
                showToast('Failed to load payment details: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching payment details:', error);
            console.error('Order ID:', orderId);
            console.error('Base URL:', base_url);
            showToast('An error occurred while loading payment details: ' + error.message + '. Please check the console for details.', 'error');
        });
    }

    // Receipt button → open popup with row data
    receiptButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const row = btn.closest('tr');
            openReceiptPopup(row);
        });
    });

    // "View Receipt" option → open popup with active row
    if (viewReceiptLink) {
        viewReceiptLink.addEventListener('click', function (e) {
            e.preventDefault();
            if (activeRow) {
                openReceiptPopup(activeRow);
                // Hide action menu after clicking
                actionMenu.style.display = 'none';
                actionMenu.classList.add('hidden');
            }
        });
    }

    // Close popup (X)
    if (closePopup) {
        closePopup.addEventListener('click', function () {
            popup.style.display = 'none';
        });
    }

    // Close popup if background clicked
    if (popup) {
        popup.addEventListener('click', function (e) {
            if (e.target === popup) {
                popup.style.display = 'none';
            }
        });
    }
    
    // "Mark as Paid" button handler
    const markAsPaidBtn = popup ? popup.querySelector('.save-btn') : null;
    const cancelBtn = popup ? popup.querySelector('.cancel-btn') : null;
    
    if (markAsPaidBtn) {
        markAsPaidBtn.addEventListener('click', function() {
            // Get order ID from stored variable or from DOM element
            let orderId = currentOrderId;
            
            if (!orderId) {
                // Fallback: try to get from DOM
                const orderIdEl = document.getElementById('popupOrderId');
                if (orderIdEl) {
                    orderId = orderIdEl.textContent.trim();
                }
            }
            
            if (!orderId || orderId === '#') {
                showToast('Order ID not found. Please try closing and reopening the popup.', 'warning');
                console.error('Order ID not found. currentOrderId:', currentOrderId);
                return;
            }
            
            // Confirm action
            showConfirmModal('Are you sure you want to mark this payment as paid?', () => {
                // Disable button to prevent double-clicking
                markAsPaidBtn.disabled = true;
                markAsPaidBtn.textContent = 'Processing...';
                
                // Send AJAX request to mark payment as paid (Admin version)
                fetch(base_url + 'AdminCon/mark_payment_paid', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'order_id=' + encodeURIComponent(orderId)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update the table row without reloading the page
                        updatePaymentStatusInTable(orderId, 'Paid');
                        
                        showToast('Payment marked as paid successfully!', 'success');
                        // Close popup
                        popup.style.display = 'none';
                    } else {
                        let errorMsg = 'Failed to mark payment as paid: ' + (data.message || 'Unknown error');
                        if (data.error_details) {
                            errorMsg += ' Details: ' + JSON.stringify(data.error_details);
                        }
                        showToast(errorMsg, 'error');
                        console.error('Payment update failed:', data);
                        markAsPaidBtn.disabled = false;
                        markAsPaidBtn.textContent = 'Mark as Paid';
                    }
                })
                .catch(error => {
                    console.error('Error marking payment as paid:', error);
                    showToast('An error occurred while marking payment as paid: ' + error.message + '. Please check the browser console for more details.', 'error');
                    markAsPaidBtn.disabled = false;
                    markAsPaidBtn.textContent = 'Mark as Paid';
                });
            });
        });
    }
    
    // Cancel button handler
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            popup.style.display = 'none';
        });
    }
    
    // Function to update payment status in the table
    function updatePaymentStatusInTable(orderId, status) {
        // Find the table row with matching order ID
        const tableRows = document.querySelectorAll('.payment-table tbody tr[data-order-id]');
        
        // Helper function to extract numeric order ID from formatted string (e.g., "#GI020" -> 20)
        const extractNumericOrderId = (id) => {
            if (!id) return null;
            let clean = id.toString().replace('#', '').trim().toUpperCase();
            // Remove GI prefix if present
            if (clean.startsWith('GI')) {
                clean = clean.substring(2);
            }
            // Remove leading zeros and convert to number
            const numeric = parseInt(clean, 10);
            return isNaN(numeric) ? null : numeric;
        };
        
        // Try to get numeric order ID from the provided orderId
        const numericOrderId = extractNumericOrderId(orderId);
        
        tableRows.forEach(row => {
            // Get numeric order ID from data-order-id attribute (most reliable)
            const rowDataOrderId = row.getAttribute('data-order-id');
            const rowNumericId = rowDataOrderId ? parseInt(rowDataOrderId, 10) : null;
            
            // Also get formatted order ID from the cell for fallback matching
            const rowOrderIdCell = row.cells[1];
            const rowOrderId = rowOrderIdCell ? rowOrderIdCell.textContent.trim() : '';
            
            // Check if this row matches the order ID
            // Priority: match by numeric ID (most reliable), then by formatted string
            let isMatch = false;
            
            if (numericOrderId && rowNumericId && numericOrderId === rowNumericId) {
                isMatch = true;
            } else if (rowOrderId && orderId) {
                // Fallback: compare formatted strings (normalize by removing # and case)
                const normalizedOrderId = orderId.replace('#', '').trim().toUpperCase();
                const normalizedRowOrderId = rowOrderId.replace('#', '').trim().toUpperCase();
                isMatch = normalizedOrderId === normalizedRowOrderId;
            }
            
            if (isMatch) {
                    // Update the data-payment-status attribute
                    row.setAttribute('data-payment-status', status.toLowerCase());
                    
                    // Find the status cell (6th column, index 5)
                    const statusCell = row.cells[5];
                    if (statusCell) {
                        // Update the status badge
                        const statusLower = status.toLowerCase();
                        let badgeClass = 'pending';
                        let badgeText = 'Pending';
                        
                        if (statusLower === 'paid') {
                            badgeClass = 'paid';
                            badgeText = 'Paid';
                        } else if (statusLower === 'overdue') {
                            badgeClass = 'overdue';
                            badgeText = 'Overdue';
                        } else if (statusLower === 'under review') {
                            badgeClass = 'review';
                            badgeText = 'Under Review';
                        } else if (statusLower === 'failed') {
                            badgeClass = 'overdue';
                            badgeText = 'Failed';
                        }
                        
                        statusCell.innerHTML = `<span class="status-badge ${badgeClass}">${badgeText}</span>`;
                    }
                    
                    // Update stats counters
                    updatePaymentStats();
                }
        });
    }
    
    // Function to update payment statistics
    function updatePaymentStats() {
        const tableRows = document.querySelectorAll('.payment-table tbody tr[data-order-id]');
        let pendingCount = 0;
        let overdueCount = 0;
        
        tableRows.forEach(row => {
            const status = row.getAttribute('data-payment-status') || 'pending';
            
            if (status === 'pending') {
                pendingCount++;
            } else if (status === 'overdue') {
                overdueCount++;
            }
        });
        
        // Update the stat values
        const pendingStatEl = document.getElementById('statPendingValue');
        const overdueStatEl = document.getElementById('statOverdueValue');
        
        if (pendingStatEl) {
            pendingStatEl.textContent = pendingCount;
        }
        if (overdueStatEl) {
            overdueStatEl.textContent = overdueCount;
        }
    }
});
