/**
 * ============================================================================
 * WISHLIST PAGE JAVASCRIPT
 * ============================================================================
 * 
 * Handles all interactions on the wishlist page (/wishlist)
 * 
 * FUNCTIONALITY:
 * - Remove individual items from wishlist (with confirmation)
 * - Move items to shopping cart
 * - Clear entire wishlist (with confirmation)
 * - Update wishlist count in header
 * - Handle empty wishlist state UI
 * 
 * AJAX ENDPOINTS USED:
 * - WishlistCon/remove_ajax     : Remove single item
 * - WishlistCon/move_to_cart_ajax : Transfer item to cart
 * - WishlistCon/clear_ajax      : Clear all items
 * 
 * REQUIRES:
 * - jQuery 3.6.0+
 * - BASE_URL constant (set in view)
 * 
 * @author      Glassify Development Team
 * @version     1.0.0
 * @created     December 2025
 * ============================================================================
 */
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
            top: 80px;
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
            max-width: 450px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease;
        `;
        
        modal.innerHTML = `
            <h3 style="margin: 0 0 15px 0; font-size: 20px; color: #333; font-family: 'Montserrat', sans-serif;">Confirm Action</h3>
            <p style="margin: 0 0 25px 0; color: #666; font-size: 15px; line-height: 1.5;">${message}</p>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button id="confirm-cancel-btn" style="
                    padding: 10px 20px;
                    border: 1px solid #ddd;
                    background: white;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    color: #666;
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
                    font-weight: 600;
                    transition: all 0.2s;
                ">Confirm</button>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        // Add animations if not already added
        if (!document.getElementById('modal-styles')) {
            const style = document.createElement('style');
            style.id = 'modal-styles';
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
                #confirm-cancel-btn:hover {
                    background: #f5f5f5;
                }
                #confirm-ok-btn:hover {
                    background: #c82333;
                }
            `;
            document.head.appendChild(style);
        }
        
        // Handle button clicks
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
    // REMOVE ITEM FROM WISHLIST
    // =============================
    $(document).on('click', '.remove-btn', function () {
        const btn = $(this);
        const row = btn.closest('tr');
        const wishlist_id = btn.data('id');
        const productName = row.find('.product-name').text();
        const addCartBtn = row.find('.add-cart-btn');

        // Store row HTML and data for undo
        const rowHTML = row[0].outerHTML;
        const prevRowWishlistId = row.prev('tr.wishlist-row').find('[data-id]').data('id');
        const tbody = row.closest('tbody');
        
        // Revert "Added" button to "Add to Cart" if it was in added state
        if (addCartBtn.hasClass('added-state')) {
            setButtonToAddToCart(addCartBtn);
        }

        // Optimistically remove from UI immediately
        row.css({
            'transition': 'all 0.3s ease',
            'opacity': '0',
            'transform': 'translateX(-20px)'
        });
        
        setTimeout(function() {
            row.remove();
            
            // Check if wishlist is now empty
            if ($('#wishlist-body tr.wishlist-row').length === 0) {
                $('#wishlist-body').html(`
                    <tr class="empty-row">
                        <td colspan="6" class="empty-wishlist">
                            <div class="empty-message">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                </svg>
                                <p>Your wishlist is empty</p>
                                <a href="${BASE_URL}products" class="browse-btn">Browse Products</a>
                            </div>
                        </td>
                    </tr>
                `);
                $('.wishlist-actions').hide();
            }
        }, 300);

        // Show toast with undo button
        let deletionTimeout;
        let isUndone = false;
        
        const toast = showToastWithUndo(
            `${productName} has been removed from your wishlist`,
            function() {
                // Undo clicked - restore the item
                isUndone = true;
                clearTimeout(deletionTimeout);
                
                // Restore the row
                const wishlistBody = $('#wishlist-body');
                if (wishlistBody.find('tr').length === 1 && wishlistBody.find('tr').text().includes('empty')) {
                    wishlistBody.empty();
                    $('.wishlist-actions').show();
                }
                
                // Insert row back at original position
                const newRow = $(rowHTML);
                if (prevRowWishlistId) {
                    // Try to find the previous row by wishlist_id
                    const prevRow = wishlistBody.find(`tr [data-id="${prevRowWishlistId}"]`).closest('tr');
                    if (prevRow.length > 0) {
                        // Insert after the previous row
                        prevRow.after(newRow);
                    } else {
                        // Previous row was also removed, append to end
                        wishlistBody.append(newRow);
                    }
                } else {
                    // No previous row, insert at the beginning
                    const firstRow = wishlistBody.find('tr.wishlist-row').first();
                    if (firstRow.length > 0) {
                        firstRow.before(newRow);
                    } else {
                        wishlistBody.prepend(newRow);
                    }
                }
                
                // After restoring, check if this item is in cart and update button state
                setTimeout(function() {
                    checkCartStatus();
                }, 100);
                
                showToast('Item restored', 'success', 2000);
            }
        );
        
        // Delete from server after 2.5 seconds (if not undone)
        deletionTimeout = setTimeout(function() {
            if (!isUndone) {
                $.ajax({
                    url: BASE_URL + "WishlistCon/remove_ajax",
                    method: "POST",
                    data: { wishlist_id: wishlist_id },
                    dataType: "json",
                    success: function (res) {
                        if (res.status === 'success') {
                            // Update wishlist counter if exists
                            if ($('#wishlist-count').length) {
                                $('#wishlist-count').text(res.wishlist_count);
                                $('#wishlist-count').toggle(res.wishlist_count > 0);
                            }
                        } else {
                            console.error('Failed to remove item from server:', res);
                        }
                    },
                    error: function () {
                        console.error('Error removing item from server');
                    }
                });
            }
        }, 2500);
    });

    // =============================
    // CHECK CART STATUS ON PAGE LOAD
    // =============================
    function checkCartStatus() {
        $.ajax({
            url: BASE_URL + "WishlistCon/check_cart_status_ajax",
            method: "POST",
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    // Convert all IDs to numbers for proper comparison
                    const inCartIds = (res.in_cart_wishlist_ids || []).map(id => parseInt(id));
                    
                    // Get all wishlist buttons
                    $('.add-cart-btn').each(function() {
                        const btn = $(this);
                        const wishlist_id = parseInt(btn.data('id')); // Convert to number
                        const isInCart = inCartIds.includes(wishlist_id);
                        const hasAddedClass = btn.hasClass('added-state');
                        
                        // Only update if state doesn't match
                        if (isInCart && !hasAddedClass) {
                            // Item is in cart but button is not in added state - update it
                            setButtonToAdded(btn);
                        } else if (!isInCart && hasAddedClass) {
                            // Item is NOT in cart but button is in added state - revert it
                            // This happens when order is completed and cart is cleared
                            setButtonToAddToCart(btn);
                        }
                        // If state already matches (both in cart and has added class, or both not), do nothing
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('Error checking cart status:', error);
                // Don't change button states on error - preserve current state
            }
        });
    }

    // =============================
    // SET BUTTON TO ADDED STATE
    // =============================
    function setButtonToAdded(btn) {
        btn.addClass('added-state');
        btn.html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Added');
        btn.css({
            'background': '#28a745',
            'color': 'white',
            'cursor': 'default'
        });
        btn.prop('disabled', false); // Keep enabled but change appearance
    }

    // =============================
    // SET BUTTON TO ADD TO CART STATE
    // =============================
    function setButtonToAddToCart(btn) {
        btn.removeClass('added-state');
        btn.html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg> Add to Cart');
        // Remove inline styles to let CSS take over, then set proper styles
        btn.removeAttr('style');
        btn.css({
            'background': '#02455F',
            'color': '#fff',
            'cursor': 'pointer'
        });
    }

    // Track last cart status check to avoid excessive checks
    let lastCartCheck = 0;
    const CART_CHECK_INTERVAL = 2000; // Minimum 2 seconds between checks

    // Check cart status on page load (after DOM is ready)
    // Use a delay to ensure all buttons are rendered and PHP-set states are preserved
    // The initial state is already set by PHP, so this just syncs any changes
    setTimeout(function() {
        checkCartStatus();
        lastCartCheck = Date.now();
    }, 500); // Increased delay to ensure PHP-rendered states are fully loaded

    // Check cart status when page becomes visible (e.g., after returning from checkout/order completion)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            // Only check if enough time has passed since last check
            const now = Date.now();
            if (now - lastCartCheck > CART_CHECK_INTERVAL) {
                setTimeout(function() {
                    checkCartStatus();
                    lastCartCheck = Date.now();
                }, 200);
            }
        }
    });

    // Check cart status when window gains focus (user switches back to tab)
    window.addEventListener('focus', function() {
        const now = Date.now();
        if (now - lastCartCheck > CART_CHECK_INTERVAL) {
            setTimeout(function() {
                checkCartStatus();
                lastCartCheck = Date.now();
            }, 200);
        }
    });

    // Also check cart status periodically (every 30 seconds) to catch order completions
    setInterval(function() {
        checkCartStatus();
        lastCartCheck = Date.now();
    }, 30000);

    // =============================
    // ADD TO CART FROM WISHLIST
    // =============================
    $(document).on('click', '.add-cart-btn:not(.added-state)', function () {
        const btn = $(this);
        const row = btn.closest('tr');
        const wishlist_id = btn.data('id');
        const originalText = btn.html();

        // Don't proceed if already in added state
        if (btn.hasClass('added-state')) {
            return;
        }

        btn.prop('disabled', true).html('Adding...');

        $.ajax({
            url: BASE_URL + "WishlistCon/move_to_cart_ajax",
            method: "POST",
            data: { wishlist_id: wishlist_id },
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    // Change button to "Added" state - DO NOT remove row
                    setButtonToAdded(btn);
                    
                    // Update counters
                    if ($('#cart-count').length) {
                        $('#cart-count').text(res.cart_count);
                        $('#cart-count').toggle(res.cart_count > 0);
                    }
                    
                    showToast('Item added to cart!', 'success', 2000);
                } else {
                    showToast(res.message || 'Failed to add to cart. Please try again.', 'error');
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function () {
                showToast('An error occurred. Please try again.', 'error');
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // =============================
    // CLEAR WISHLIST
    // =============================
    $('#clear-wishlist').click(function (e) {
        e.preventDefault();
        
        const wishlistRows = $('tr.wishlist-row');
        
        if (wishlistRows.length === 0) {
            showToast('Your wishlist is already empty.', 'info');
            return;
        }

        // Custom confirmation modal
        showConfirmModal(
            'Are you sure you want to clear your entire wishlist?',
            function() {
                // User confirmed - proceed with clearing
                const link = $('#clear-wishlist');
                link.css('opacity', '0.5').text('Clearing...');

                $.ajax({
                    url: BASE_URL + "WishlistCon/clear_ajax",
                    method: "POST",
                    dataType: "json",
                    success: function (res) {
                        if (res.status === 'success') {
                            wishlistRows.css({
                                'transition': 'all 0.3s ease',
                                'opacity': '0',
                                'transform': 'translateX(-20px)'
                            });

                            setTimeout(function() {
                                wishlistRows.remove();
                                $('#wishlist-body').html(`
                                    <tr class="empty-row">
                                        <td colspan="6" class="empty-wishlist">
                                            <div class="empty-message">
                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                                </svg>
                                                <p>Your wishlist is empty</p>
                                                <a href="${BASE_URL}products" class="browse-btn">Browse Products</a>
                                            </div>
                                        </td>
                                    </tr>
                                `);
                                $('.wishlist-actions').hide();
                                
                                if ($('#wishlist-count').length) {
                                    $('#wishlist-count').text(0);
                                    $('#wishlist-count').hide();
                                }
                                
                                showToast('Wishlist cleared successfully', 'success');
                            }, 300);
                        } else {
                            showToast('Failed to clear wishlist. Please try again.', 'error');
                            link.css('opacity', '1').text('Clear Wishlist');
                        }
                    },
                    error: function () {
                        showToast('An error occurred. Please try again.', 'error');
                        link.css('opacity', '1').text('Clear Wishlist');
                    }
                });
            }
        );
    });

});
