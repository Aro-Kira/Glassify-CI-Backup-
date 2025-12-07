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
    // REMOVE ITEM FROM WISHLIST
    // =============================
    $(document).on('click', '.remove-btn', function () {
        const btn = $(this);
        const row = btn.closest('tr');
        const wishlist_id = btn.data('id');
        const productName = row.find('.product-name').text();

        if (!confirm(`Remove "${productName}" from wishlist?`)) {
            return;
        }

        btn.css('opacity', '0.5');

        $.ajax({
            url: BASE_URL + "WishlistCon/remove_ajax",
            method: "POST",
            data: { wishlist_id: wishlist_id },
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
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
                        
                        // Update wishlist counter if exists
                        if ($('#wishlist-count').length) {
                            $('#wishlist-count').text(res.wishlist_count);
                        }
                    }, 300);
                } else {
                    alert('Failed to remove item. Please try again.');
                    btn.css('opacity', '1');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
                btn.css('opacity', '1');
            }
        });
    });

    // =============================
    // ADD TO CART FROM WISHLIST
    // =============================
    $(document).on('click', '.add-cart-btn', function () {
        const btn = $(this);
        const row = btn.closest('tr');
        const wishlist_id = btn.data('id');
        const originalText = btn.html();

        btn.prop('disabled', true).html('Adding...');

        $.ajax({
            url: BASE_URL + "WishlistCon/move_to_cart_ajax",
            method: "POST",
            data: { wishlist_id: wishlist_id },
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    // Show success animation
                    btn.html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Added!');
                    btn.css('background', '#28a745');
                    
                    setTimeout(function() {
                        row.css({
                            'transition': 'all 0.3s ease',
                            'opacity': '0',
                            'transform': 'translateX(20px)'
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
                            
                            // Update counters
                            if ($('#cart-count').length) {
                                $('#cart-count').text(res.cart_count);
                            }
                            if ($('#wishlist-count').length) {
                                $('#wishlist-count').text(res.wishlist_count);
                            }
                        }, 300);
                    }, 500);
                } else {
                    alert(res.message || 'Failed to add to cart. Please try again.');
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
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
            alert('Your wishlist is already empty.');
            return;
        }

        if (!confirm('Are you sure you want to clear your entire wishlist?')) {
            return;
        }

        const link = $(this);
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
                        }
                    }, 300);
                } else {
                    alert('Failed to clear wishlist. Please try again.');
                    link.css('opacity', '1').text('Clear Wishlist');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
                link.css('opacity', '1').text('Clear Wishlist');
            }
        });
    });

});
