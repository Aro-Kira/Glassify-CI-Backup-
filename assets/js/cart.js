$(document).ready(function () {

    // =============================
    // TOAST NOTIFICATION SYSTEM
    // =============================
    function showToast(message, type = 'info', duration = 3000) {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast-notification');
        existingToasts.forEach(toast => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        });

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        
        // Set icon and colors based on type
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
        
        // Add styles
        toast.style.cssText = `
            position: fixed;
            top: 100px;
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
        
        // Add animation styles if not already added
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                @keyframes toastSlideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes toastFadeOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
                .toast-notification {
                    transition: all 0.3s ease;
                }
                .toast-fade-out {
                    animation: toastFadeOut 0.3s ease forwards;
                }
                .toast-icon {
                    font-size: 20px;
                    font-weight: bold;
                    flex-shrink: 0;
                }
                .toast-message {
                    flex: 1;
                    font-size: 14px;
                    line-height: 1.4;
                }
                .toast-close {
                    background: none;
                    border: none;
                    color: white;
                    font-size: 24px;
                    cursor: pointer;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0.8;
                    transition: opacity 0.2s;
                    flex-shrink: 0;
                }
                .toast-close:hover {
                    opacity: 1;
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(toast);
        
        // Auto remove after duration
        setTimeout(() => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
        
        return toast;
    }

    // =============================
    // TOAST WITH UNDO BUTTON
    // =============================
    function showToastWithUndo(message, onUndo, duration = 2500) {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast-notification');
        existingToasts.forEach(toast => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        });

        // Create toast element with undo button
        const toast = document.createElement('div');
        toast.className = 'toast-notification toast-success';
        
        toast.innerHTML = `
            <div class="toast-icon">✓</div>
            <div class="toast-message">${message}</div>
            <button class="toast-undo-btn" id="toast-undo-btn">Undo</button>
        `;
        
        // Add styles
        toast.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: #28a745;
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
            border-left: 4px solid #1e7e34;
        `;
        
        // Style the undo button
        const undoBtn = toast.querySelector('.toast-undo-btn');
        undoBtn.style.cssText = `
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
            flex-shrink: 0;
        `;
        
        undoBtn.addEventListener('mouseenter', () => {
            undoBtn.style.background = 'rgba(255, 255, 255, 0.3)';
        });
        
        undoBtn.addEventListener('mouseleave', () => {
            undoBtn.style.background = 'rgba(255, 255, 255, 0.2)';
        });
        
        // Handle undo click
        undoBtn.addEventListener('click', () => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
            if (onUndo) onUndo();
        });
        
        // Add animation styles if not already added (should already be there from showToast)
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                @keyframes toastSlideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes toastFadeOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
                .toast-notification {
                    transition: all 0.3s ease;
                }
                .toast-fade-out {
                    animation: toastFadeOut 0.3s ease forwards;
                }
                .toast-icon {
                    font-size: 20px;
                    font-weight: bold;
                    flex-shrink: 0;
                }
                .toast-message {
                    flex: 1;
                    font-size: 14px;
                    line-height: 1.4;
                }
                .toast-close {
                    background: none;
                    border: none;
                    color: white;
                    font-size: 24px;
                    cursor: pointer;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0.8;
                    transition: opacity 0.2s;
                    flex-shrink: 0;
                }
                .toast-close:hover {
                    opacity: 1;
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(toast);
        
        // Auto remove after duration
        setTimeout(() => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
        
        return toast;
    }

    // =============================
    // CUSTOM CONFIRMATION MODAL
    // =============================
    function showConfirmModal(message, onConfirm, onCancel = null) {
        // Remove existing modal if any
        const existingModal = document.getElementById('confirm-modal-overlay');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.id = 'confirm-modal-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s ease;
        `;
        
        // Create modal content
        const modal = document.createElement('div');
        modal.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease;
        `;
        
        modal.innerHTML = `
            <div style="margin-bottom: 20px;">
                <div style="font-size: 48px; color: #ffc107; text-align: center; margin-bottom: 15px;">⚠</div>
                <h3 style="margin: 0 0 10px 0; color: #333; font-size: 20px; text-align: center;">Confirm Action</h3>
                <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.6; text-align: center;">${message}</p>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button id="confirm-cancel-btn" style="
                    padding: 10px 20px;
                    border: 1px solid #ddd;
                    background: white;
                    color: #333;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    transition: all 0.2s;
                ">Cancel</button>
                <button id="confirm-ok-btn" style="
                    padding: 10px 20px;
                    border: none;
                    background: #dc3545;
                    color: white;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    transition: all 0.2s;
                ">Confirm</button>
            </div>
        `;
        
        // Add hover effects
        const cancelBtn = modal.querySelector('#confirm-cancel-btn');
        const confirmBtn = modal.querySelector('#confirm-ok-btn');
        
        cancelBtn.addEventListener('mouseenter', () => {
            cancelBtn.style.background = '#f5f5f5';
        });
        cancelBtn.addEventListener('mouseleave', () => {
            cancelBtn.style.background = 'white';
        });
        
        confirmBtn.addEventListener('mouseenter', () => {
            confirmBtn.style.background = '#c82333';
        });
        confirmBtn.addEventListener('mouseleave', () => {
            confirmBtn.style.background = '#dc3545';
        });
        
        // Add animations if not already added
        if (!document.getElementById('confirm-modal-styles')) {
            const style = document.createElement('style');
            style.id = 'confirm-modal-styles';
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes slideUp {
                    from {
                        transform: translateY(20px);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        // Handle confirm
        confirmBtn.addEventListener('click', () => {
            overlay.style.animation = 'fadeIn 0.2s ease reverse';
            setTimeout(() => overlay.remove(), 200);
            if (onConfirm) onConfirm();
        });
        
        // Handle cancel
        cancelBtn.addEventListener('click', () => {
            overlay.style.animation = 'fadeIn 0.2s ease reverse';
            setTimeout(() => overlay.remove(), 200);
            if (onCancel) onCancel();
        });
        
        // Close on overlay click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.animation = 'fadeIn 0.2s ease reverse';
                setTimeout(() => overlay.remove(), 200);
                if (onCancel) onCancel();
            }
        });
        
        // Close on Escape key
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

    // =============================
    // CALCULATE SELECTED ITEMS SUMMARY
    // =============================
    function calculateSelectedSummary() {
        let selectedItems = 0;
        let subtotal = 0;

        $('.item-checkbox:checked').each(function() {
            const row = $(this).closest('tr');
            const price = parseFloat($(this).data('price')) || 0;
            const quantity = parseInt(row.find('.qty-input').val()) || 0;
            
            selectedItems += quantity;
            subtotal += price * quantity;
        });

        // Calculate shipping and handling to match server-side calculation
        // Shipping: 25 per item, Handling: 10 per item
        const shipping = selectedItems * 25;
        const handling = selectedItems * 10;
        const total = subtotal + shipping + handling;

        // Update summary display
        $('#summary-items').text(selectedItems);
        $('#summary-subtotal').text(subtotal.toFixed(2));
        $('#summary-shipping').text(shipping.toFixed(2));
        $('#summary-handling').text(handling.toFixed(2));
        $('#summary-total').text(total.toFixed(2));

        // Update mobile summary display
        $('#summary-items-mobile').text(selectedItems);
        $('#summary-shipping-mobile').text(shipping.toFixed(2));
        $('#summary-handling-mobile').text(handling.toFixed(2));
        $('#summary-total-mobile').text(total.toFixed(2));

        // Update selected count in checkout button
        const selectedCount = $('.item-checkbox:checked').length;
        $('#selected-count').text(selectedCount);
        $('#selected-count-mobile').text(selectedCount);

        // Enable/disable checkout button based on selection
        if (selectedCount === 0) {
            $('#checkout-selected-btn').prop('disabled', true).addClass('disabled');
            $('#checkout-selected-btn-mobile').prop('disabled', true).addClass('disabled');
        } else {
            $('#checkout-selected-btn').prop('disabled', false).removeClass('disabled');
            $('#checkout-selected-btn-mobile').prop('disabled', false).removeClass('disabled');
        }

        return { selectedItems, subtotal, shipping, handling, total };
    }

    // =============================
    // LOAD SUMMARY FROM SERVER
    // =============================
    function loadSummary() {
        $.ajax({
            url: BASE_URL + "CartCon/get_cart_ajax",
            method: "GET",
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    // Update each row total in cart table and checkbox data
                    res.items.forEach(item => {
                        const row = $(`.qty-input[data-id='${item.cart_id}']`).closest('tr');
                        row.find('.item-total').text(`₱${item.total.toFixed(2)}`);
                        row.find('.qty-input').val(item.quantity);
                        
                        // Update checkbox data attributes
                        const checkbox = row.find('.item-checkbox');
                        checkbox.data('quantity', item.quantity);
                    });

                    // Recalculate based on selected items
                    calculateSelectedSummary();
                }
            }
        });
    }

    // Initial load
    loadSummary();

    // =============================
    // SELECT ALL CHECKBOX
    // =============================
    $('#select-all-items, #select-all-items-mobile').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.item-checkbox').prop('checked', isChecked);
        
        // Sync both select all checkboxes
        $('#select-all-items').prop('checked', isChecked);
        $('#select-all-items-mobile').prop('checked', isChecked);
        
        // Update row styling
        $('.cart-row').each(function() {
            if (isChecked) {
                $(this).removeClass('unselected');
            } else {
                $(this).addClass('unselected');
            }
        });

        calculateSelectedSummary();
    });

    // =============================
    // INDIVIDUAL ITEM CHECKBOX
    // =============================
    $(document).on('change', '.item-checkbox', function() {
        const row = $(this).closest('tr');
        
        if ($(this).prop('checked')) {
            row.removeClass('unselected');
        } else {
            row.addClass('unselected');
        }

        // Update "select all" checkbox state
        const totalItems = $('.item-checkbox').length;
        const checkedItems = $('.item-checkbox:checked').length;
        
        if (checkedItems === 0) {
            $('#select-all-items').prop('checked', false).prop('indeterminate', false);
            $('#select-all-items-mobile').prop('checked', false).prop('indeterminate', false);
        } else if (checkedItems === totalItems) {
            $('#select-all-items').prop('checked', true).prop('indeterminate', false);
            $('#select-all-items-mobile').prop('checked', true).prop('indeterminate', false);
        } else {
            $('#select-all-items').prop('checked', false).prop('indeterminate', true);
            $('#select-all-items-mobile').prop('checked', false).prop('indeterminate', true);
        }

        calculateSelectedSummary();
    });

    // =============================
    // CHECKOUT SELECTED ITEMS
    // =============================
    $('#checkout-selected-btn, #checkout-selected-btn-mobile').on('click', function() {
        const selectedIds = [];
        $('.item-checkbox:checked').each(function() {
            selectedIds.push($(this).data('id'));
        });

        if (selectedIds.length === 0) {
            showToast('Please select at least one item to checkout.', 'warning');
            return;
        }

        // Navigate to payment with selected cart IDs
        window.location.href = BASE_URL + 'payment?selected=' + selectedIds.join(',');
    });

    // =============================
    // DELETE ITEM (With Undo - Delete immediately if user navigates away)
    // =============================
    // Track pending undo operations
    let pendingUndoOperations = [];
    
    // Delete all pending items immediately when user navigates away
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // User navigated away - delete all pending items immediately
            pendingUndoOperations.forEach(function(operation) {
                if (operation.timeout) {
                    clearTimeout(operation.timeout);
                }
                if (operation.toast) {
                    operation.toast.remove();
                }
                
                // Delete from database immediately
                if (!operation.isUndone && !operation.deleted) {
                    operation.deleted = true;
                    $.ajax({
                        url: BASE_URL + "CartCon/remove_ajax",
                        method: "POST",
                        data: operation.ajaxData,
                        dataType: "json",
                        success: function (res) {
                            console.log('Item deleted on navigation:', res);
                        },
                        error: function (xhr, status, error) {
                            console.error('Error deleting item on navigation:', error);
                        }
                    });
                }
            });
            pendingUndoOperations = [];
        }
    });
    
    // Also delete on beforeunload (page refresh/close)
    window.addEventListener('beforeunload', function() {
        pendingUndoOperations.forEach(function(operation) {
            if (operation.timeout) {
                clearTimeout(operation.timeout);
            }
            
            // Delete from database immediately
            if (!operation.isUndone && !operation.deleted) {
                operation.deleted = true;
                // Use sendBeacon for reliability during page unload
                const formData = new FormData();
                formData.append('cart_id', operation.ajaxData.cart_id || 0);
                if (operation.ajaxData.product_id) {
                    formData.append('product_id', operation.ajaxData.product_id);
                }
                if (operation.ajaxData.customization_id) {
                    formData.append('customization_id', operation.ajaxData.customization_id);
                }
                navigator.sendBeacon(BASE_URL + "CartCon/remove_ajax", formData);
            }
        });
        pendingUndoOperations = [];
    });
    
    $(document).on('click', '.remove-btn', function () {
        const btn = $(this);
        const row = btn.closest('tr');
        const cart_id = btn.data('id');
        const product_id = btn.data('product-id');
        const customization_id = btn.data('customization-id');
        const productName = row.find('td').eq(2).text();
        const quantity = parseInt(row.find('.qty-input').val()) || 1;

        // Store row HTML and data for undo
        const rowHTML = row[0].outerHTML;
        const prevRowCartId = row.prev('tr.cart-row').find('.item-checkbox').data('id');
        const wasChecked = row.find('.item-checkbox').prop('checked');
        
        // Prepare data to send - always include product_id and customization_id as backup
        const ajaxData = {
            cart_id: cart_id || 0
        };
        
        if (product_id) {
            ajaxData.product_id = product_id;
        }
        
        ajaxData.customization_id = (customization_id !== undefined && customization_id !== null && customization_id !== '') 
            ? customization_id 
            : '';

        // Store item data for potential undo
        const itemData = {
            cart_id: cart_id,
            product_id: product_id,
            customization_id: customization_id,
            quantity: quantity,
            rowHTML: rowHTML,
            prevRowCartId: prevRowCartId,
            wasChecked: wasChecked,
            productName: productName
        };

        // Optimistically remove from UI immediately
        row.css({
            'transition': 'all 0.3s ease',
            'opacity': '0',
            'transform': 'translateX(-20px)'
        });
        
        setTimeout(function() {
            row.remove();
            
            // Check if cart is now empty
            if ($('#cart-body tr.cart-row').length === 0) {
                $('#cart-body').html('<tr><td colspan="8">Your cart is empty.</td></tr>');
            }
            
            // Update summary and recalculate selected
            calculateSelectedSummary();
            
            // Update select all checkbox state
            const totalItems = $('.item-checkbox').length;
            const checkedItems = $('.item-checkbox:checked').length;
            if (totalItems === 0 || checkedItems === totalItems) {
                $('#select-all-items').prop('indeterminate', false);
            }
        }, 300);

        // Show toast with undo button
        let deletionTimeout;
        
        // Store undo operation first
        const undoOperation = {
            itemData: itemData,
            ajaxData: ajaxData,
            toast: null,
            timeout: null,
            isUndone: false,
            deleted: false
        };
        
        const toast = showToastWithUndo(
            `${productName} has been removed from your cart`,
            function() {
                // Undo clicked - restore the item
                undoOperation.isUndone = true;
                if (deletionTimeout) {
                    clearTimeout(deletionTimeout);
                }
                
                // Remove from pending operations
                const index = pendingUndoOperations.findIndex(op => op.itemData.cart_id === itemData.cart_id);
                if (index > -1) {
                    pendingUndoOperations.splice(index, 1);
                }
                
                // Restore the row
                const cartBody = $('#cart-body');
                if (cartBody.find('tr').length === 1 && cartBody.find('tr').text().includes('empty')) {
                    cartBody.empty();
                }
                
                // Insert row back at original position
                const newRow = $(rowHTML);
                if (prevRowCartId) {
                    // Try to find the previous row by cart_id
                    const prevRow = cartBody.find(`tr .item-checkbox[data-id="${prevRowCartId}"]`).closest('tr');
                    if (prevRow.length > 0) {
                        // Insert after the previous row
                        prevRow.after(newRow);
                    } else {
                        // Previous row was also removed, append to end
                        cartBody.append(newRow);
                    }
                } else {
                    // No previous row, insert at the beginning
                    const firstRow = cartBody.find('tr.cart-row').first();
                    if (firstRow.length > 0) {
                        firstRow.before(newRow);
                    } else {
                        cartBody.prepend(newRow);
                    }
                }
                
                // Restore checkbox state
                if (wasChecked) {
                    newRow.find('.item-checkbox').prop('checked', true);
                    newRow.removeClass('unselected');
                }
                
                // Recalculate summary
                calculateSelectedSummary();
                
                // Update select all checkbox
                const totalItems = $('.item-checkbox').length;
                const checkedItems = $('.item-checkbox:checked').length;
                if (checkedItems === 0) {
                    $('#select-all-items').prop('checked', false).prop('indeterminate', false);
                } else if (checkedItems === totalItems) {
                    $('#select-all-items').prop('checked', true).prop('indeterminate', false);
                } else {
                    $('#select-all-items').prop('checked', false).prop('indeterminate', true);
                }
                
                showToast('Item restored', 'success', 2000);
            }
        );
        
        // Store toast reference
        undoOperation.toast = toast;
        
        // Delete from server after 2.5 seconds (if not undone and user is still on page)
        deletionTimeout = setTimeout(function() {
            if (!undoOperation.isUndone && !undoOperation.deleted) {
                undoOperation.deleted = true;
                
                // Remove from pending operations
                const index = pendingUndoOperations.findIndex(op => op.itemData.cart_id === itemData.cart_id);
                if (index > -1) {
                    pendingUndoOperations.splice(index, 1);
                }
                
                console.log('Attempting to remove cart item from server:', {
                    cart_id: cart_id,
                    product_id: product_id,
                    customization_id: customization_id,
                    product_name: productName,
                    url: BASE_URL + "CartCon/remove_ajax",
                    data: ajaxData
                });

                $.ajax({
                    url: BASE_URL + "CartCon/remove_ajax",
                    method: "POST",
                    data: ajaxData,
                    dataType: "json",
                    success: function (res) {
                        console.log('Remove item response:', res);
                        if (res.status === 'success') {
                            console.log('Item removed from server successfully');
                            // Update summary from server response
                            if (res.summary) {
                                calculateSelectedSummary();
                            }
                        } else {
                            console.error('Failed to remove item from server:', res);
                            // Item was already removed from UI, so just log the error
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error removing cart item from server:', {
                            status: status,
                            error: error,
                            statusCode: xhr.status
                        });
                        // Item was already removed from UI, so just log the error
                    }
                });
            }
        }, 2500);
        
        undoOperation.timeout = deletionTimeout;
        pendingUndoOperations.push(undoOperation);
    });

    // =============================
    // UPDATE QUANTITY
    // =============================
$(document).on('change', '.qty-input', function () { 
    const input = $(this);
    const cart_id = input.data('id');
    let quantity = parseInt(input.val());

    if (quantity < 1) input.val(1);

    $.post(BASE_URL + "CartCon/update_qty_ajax", 
        { cart_id: cart_id, quantity: input.val() }, 
        function (res) {

        if (res.status === 'success') {
            const row = input.closest('tr');

            // Get unit price from checkbox data attribute
            const checkbox = row.find('.item-checkbox');
            const unitPrice = parseFloat(checkbox.data('price')) || 0;
            const newTotal = (unitPrice * quantity).toFixed(2);

            row.find('.item-total').text('₱' + newTotal);
            
            // Update checkbox quantity data
            checkbox.data('quantity', quantity);

            // Recalculate selected summary
            calculateSelectedSummary();
        }
    }, 'json');
});


    // =============================
    // CLEAR CART (Real-time AJAX)
    // =============================
    $('#clear-cart').click(function () {
        const cartRows = $('tr.cart-row');
        
        if (cartRows.length === 0) {
            showToast('Your cart is already empty.', 'info');
            return;
        }

        const btn = $(this);
        
        // Custom confirmation modal
        showConfirmModal(
            'Are you sure you want to clear your entire cart?',
            function() {
                // User confirmed - proceed with clearing
                btn.prop('disabled', true).text('Clearing...');

                $.ajax({
                    url: BASE_URL + "CartCon/clear_ajax",
                    method: "POST",
                    dataType: "json",
                    success: function (res) {
                        if (res.status === 'success') {
                            showToast('Cart cleared successfully', 'success');
                            // Animate all rows removal
                            cartRows.css({
                                'transition': 'all 0.3s ease',
                                'opacity': '0',
                                'transform': 'translateX(-20px)'
                            });

                            setTimeout(function() {
                                cartRows.remove();
                                $('#cart-body').html('<tr><td colspan="8">Your cart is empty.</td></tr>');
                                calculateSelectedSummary();
                                btn.prop('disabled', false).text('Clear Shopping Cart');
                            }, 300);
                        } else {
                            showToast('Failed to clear cart. Please try again.', 'error');
                            btn.prop('disabled', false).text('Clear Shopping Cart');
                        }
                    },
                    error: function () {
                        showToast('An error occurred. Please try again.', 'error');
                        btn.prop('disabled', false).text('Clear Shopping Cart');
                    }
                });
            }
        );
    });

    // =============================
    // QUANTITY +/- BUTTONS
    // =============================
    $(document).on('click', '.qty-minus', function () {
        const cart_id = $(this).data('id');
        const input = $(`.qty-input[data-id='${cart_id}']`);
        let currentVal = parseInt(input.val());
        
        if (currentVal > 1) {
            input.val(currentVal - 1).trigger('change');
        }
    });

    $(document).on('click', '.qty-plus', function () {
        const cart_id = $(this).data('id');
        const input = $(`.qty-input[data-id='${cart_id}']`);
        let currentVal = parseInt(input.val());
        
        input.val(currentVal + 1).trigger('change');
    });

    // =============================
    // QUOTATION MODAL
    // =============================
    function openModal() {
        $('#quotationModal').addClass('show');
        $('body').css('overflow', 'hidden');
    }

    function closeModal() {
        $('#quotationModal').removeClass('show');
        $('body').css('overflow', '');
    }

    $('#openModal').click(function () {
        // Get selected cart IDs - convert to integers for consistent comparison
        const selectedIds = [];
        $('.item-checkbox:checked').each(function() {
            selectedIds.push(parseInt($(this).data('id')));
        });

        if (selectedIds.length === 0) {
            showToast('Please select at least one item to generate quotation.', 'warning');
            return;
        }

        $.getJSON(BASE_URL + "CartCon/get_cart_ajax", function (res) {
            if (res.status === 'success') {
                const tbody = $('#quotation-items');
                const designsContainer = $('#quotation-designs');
                const designsSection = $('#designs-section');
                
                tbody.empty();
                designsContainer.empty();

                let subtotal = 0;
                let hasDesigns = false;
                let designIndex = 1;
                let itemCount = 0;

                // Only show selected items
                res.items.forEach((item, index) => {
                    // Check if this item is selected - convert cart_id to int for comparison
                    if (!selectedIds.includes(parseInt(item.cart_id))) {
                        return; // Skip unselected items
                    }

                    const unit_price = Number(item.unit_price) || 0;
                    const total = Number(item.total) || 0;
                    const customization = item.customization || 'Standard';

                    const row = `<tr style="animation-delay: ${itemCount * 0.05}s">
                        <td>${item.description}</td>
                        <td class="customization-cell">${customization}</td>
                        <td>${item.quantity}</td>
                        <td>₱${unit_price.toFixed(2)}</td>
                        <td>₱${total.toFixed(2)}</td>
                    </tr>`;
                    tbody.append(row);
                    subtotal += total;
                    itemCount++;

                    // Add design image if available
                    if (item.has_design && item.design_ref) {
                        hasDesigns = true;
                        const designCard = `
                            <div class="design-card">
                                <div class="design-card-header">
                                    <span class="design-number">Design #${designIndex}</span>
                                    <span class="design-product">${item.description}</span>
                                </div>
                                <div class="design-card-image">
                                    <img src="${item.design_ref}" alt="Custom Design ${designIndex}">
                                </div>
                                <div class="design-card-specs">
                                    ${customization}
                                </div>
                            </div>
                        `;
                        designsContainer.append(designCard);
                        designIndex++;
                    }
                });

                // Show/hide designs section based on whether there are designs
                if (hasDesigns) {
                    designsSection.show();
                } else {
                    designsSection.hide();
                }

                // Calculate fees based on selected items only (matching server-side calculation)
                // Shipping: 25 per item, Handling: 10 per item
                const shippingFee = itemCount * 25;
                const handlingFee = itemCount * 10;
                const grandTotal = subtotal + shippingFee + handlingFee;

                // Format date nicely
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                const formattedDate = new Date().toLocaleDateString('en-US', options);
                
                $('#quotation-date').text(formattedDate);
                $('#quote-subtotal').text(`₱${subtotal.toFixed(2)}`);
                $('#quote-shipping').text(`₱${shippingFee.toFixed(2)}`);
                $('#quote-handling').text(`₱${handlingFee.toFixed(2)}`);
                $('#quote-grandtotal').text(`₱${grandTotal.toFixed(2)}`);

                openModal();
            }
        });
    });

    // Close modal handlers
    $('#closeModal, #closeModalBtn').click(function () {
        closeModal();
    });

    // Close on overlay click
    $(document).on('click', '.modal-overlay', function () {
        closeModal();
    });

    // Close on ESC key
    $(document).keydown(function (e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    // Print quotation
    $('#printQuotation').click(function () {
        window.print();
    });
});
