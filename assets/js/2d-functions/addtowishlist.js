/**
 * ============================================================================
 * ADD TO WISHLIST - 2D CUSTOMIZATION PAGE
 * ============================================================================
 * 
 * Handles the "Add to Wishlist" button functionality on the 2DModeling page.
 * Allows users to save customized glass products to their wishlist.
 * 
 * FUNCTIONALITY:
 * - Check if product is already in wishlist on page load
 * - Add product with all customization options to wishlist
 * - Save design canvas image (from Konva) as PNG
 * - Visual feedback with heart animations and particles
 * - Toast notifications for success/error/info states
 * - Prevent duplicate additions
 * 
 * CUSTOMIZATION DATA CAPTURED:
 * - Dimensions (height x width)
 * - Glass shape (rectangle, circle, arch, etc.)
 * - Glass type (tempered, laminated, etc.)
 * - Thickness (5mm, 8mm, 10mm, etc.)
 * - Edge work (flat polish, beveled, etc.)
 * - Frame type (vinyl, aluminum, etc.)
 * - Engraving text
 * - Design image (Konva canvas export)
 * - Calculated price
 * 
 * AJAX ENDPOINTS USED:
 * - WishlistCon/check_ajax : Check if already in wishlist
 * - WishlistCon/add_ajax   : Add item to wishlist
 * 
 * REQUIRES:
 * - jQuery 3.6.0+
 * - base_url constant (set in view)
 * - Optional: window.getDesignImageData() for canvas export
 * 
 * @author      Glassify Development Team
 * @version     1.0.0
 * @created     December 2025
 * ============================================================================
 */
$(document).ready(function() {
    
    // Add animation styles on page load
    addWishlistStyles();
    
    // Check if product is already in wishlist on page load
    checkWishlistStatus();

    function addWishlistStyles() {
        if (!$('#wishlist-btn-styles').length) {
            $('head').append(`
                <style id="wishlist-btn-styles">
                    .wishlist-btn {
                        transition: transform 0.2s ease;
                        position: relative;
                        overflow: visible;
                    }
                    .wishlist-btn:hover {
                        transform: scale(1.1);
                    }
                    .wishlist-btn.pop {
                        animation: popAnimation 0.6s ease;
                    }
                    .wishlist-btn.active svg {
                        fill: #e74c3c !important;
                        stroke: #e74c3c !important;
                    }
                    .wishlist-btn.active {
                        cursor: default !important;
                    }
                    .wishlist-btn.active:hover {
                        transform: scale(1) !important;
                    }
                    .wishlist-btn.processing {
                        pointer-events: none;
                        opacity: 0.7;
                    }
                    @keyframes popAnimation {
                        0% { transform: scale(1); }
                        25% { transform: scale(1.4); }
                        50% { transform: scale(0.9); }
                        75% { transform: scale(1.2); }
                        100% { transform: scale(1); }
                    }
                    @keyframes heartBeat {
                        0% { transform: scale(1); }
                        14% { transform: scale(1.3); }
                        28% { transform: scale(1); }
                        42% { transform: scale(1.3); }
                        70% { transform: scale(1); }
                    }
                    @keyframes burstParticle {
                        0% { 
                            transform: translate(-50%, -50%) scale(0);
                            opacity: 1;
                        }
                        100% { 
                            transform: translate(-50%, -50%) scale(1);
                            opacity: 0;
                        }
                    }
                    .heart-particle {
                        position: absolute;
                        pointer-events: none;
                        font-size: 16px;
                        animation: floatUp 1s ease-out forwards;
                        z-index: 1000;
                    }
                    @keyframes floatUp {
                        0% {
                            opacity: 1;
                            transform: translateY(0) scale(1);
                        }
                        100% {
                            opacity: 0;
                            transform: translateY(-40px) scale(0.5);
                        }
                    }
                    .burst-ring {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        width: 60px;
                        height: 60px;
                        border: 3px solid #e74c3c;
                        border-radius: 50%;
                        transform: translate(-50%, -50%) scale(0);
                        animation: burstParticle 0.5s ease-out forwards;
                        pointer-events: none;
                    }
                </style>
            `);
        }
    }

    function checkWishlistStatus() {
        const btn = $('#add-to-wishlist-btn');
        const product_id = btn.data('product-id');
        
        if (!product_id) return;

        $.ajax({
            url: base_url + "WishlistCon/check_ajax",
            method: "POST",
            data: { product_id: product_id },
            dataType: "json",
            success: function(res) {
                if (res.status === 'success' && res.in_wishlist) {
                    setWishlistActive(false); // no animation on load
                    // Disable button since item is already in wishlist
                    btn.prop('disabled', true);
                    btn.css('cursor', 'default');
                }
            }
        });
    }

    function setWishlistActive(animate = true) {
        const btn = $('#add-to-wishlist-btn');
        btn.addClass('active');
        btn.find('svg').attr('fill', '#e74c3c').attr('stroke', '#e74c3c');
        btn.attr('title', 'Already in Wishlist');
        
        if (animate) {
            // Add pop animation
            btn.addClass('pop');
            
            // Create burst effect
            createBurstEffect(btn);
            
            // Create floating hearts
            createFloatingHearts(btn);
            
            // Remove animation class after it completes
            setTimeout(function() {
                btn.removeClass('pop');
            }, 600);
        }
    }

    function setWishlistInactive() {
        const btn = $('#add-to-wishlist-btn');
        btn.removeClass('active');
        btn.find('svg').attr('fill', 'none').attr('stroke', '#333');
        btn.attr('title', 'Add to Wishlist');
    }
    
    function createBurstEffect(btn) {
        const ring = $('<div class="burst-ring"></div>');
        btn.append(ring);
        
        setTimeout(function() {
            ring.remove();
        }, 500);
    }
    
    function createFloatingHearts(btn) {
        const hearts = ['❤️', '💕', '💗'];
        const positions = [
            { x: -15, y: -10 },
            { x: 15, y: -15 },
            { x: 0, y: -20 },
            { x: -20, y: 5 },
            { x: 20, y: 0 }
        ];
        
        positions.forEach((pos, i) => {
            setTimeout(function() {
                const heart = $(`<span class="heart-particle">${hearts[i % hearts.length]}</span>`);
                heart.css({
                    left: `calc(50% + ${pos.x}px)`,
                    top: `calc(50% + ${pos.y}px)`
                });
                btn.append(heart);
                
                setTimeout(function() {
                    heart.remove();
                }, 1000);
            }, i * 80);
        });
    }

    // Add to Wishlist button click handler
    $(document).on('click', '#add-to-wishlist-btn', function() {
        const btn = $(this);
        const product_id = btn.data('product-id');

        // Prevent clicking if already in wishlist
        if (btn.hasClass('active')) {
            showNotification('Already in Wishlist', 'info');
            return;
        }

        // Prevent double-clicking while processing
        if (btn.hasClass('processing')) {
            return;
        }

        if (!product_id) {
            alert('Product ID not found');
            return;
        }

        // Gather customization data if we're on step 3 or summary
        let data = {
            product_id: product_id
        };

        // Check if user has customized the product (check if step selectors exist and have values)
        const hasCustomization = $('.option-card[data-shape].active').length > 0;

        if (hasCustomization) {
            data.dimensions = $('#input-height').val() + ' x ' + $('#input-width').val();
            data.shape = $('.option-card[data-shape].active').data('shape') || '';
            data.type = $('.option-card[data-glass-type].active').data('glass-type') || '';
            data.thickness = $('.option-card[data-thickness].active').data('thickness') || '';
            data.edge = $('.option-card[data-edge-work].active').data('edge-work') || '';
            data.frame = $('.option-card[data-frame-type].active').data('frame-type') || '';
            data.engraving = $('#step-3 input').val() || 'None';
            
            // Get price from summary if available, otherwise from price box
            const sumTotal = $('#sum-total').text().replace('₱', '').replace(/,/g, '').trim();
            data.price = sumTotal || productBasePrice || 0;
            
            // Get the design image from Konva (same as add to cart)
            let designImageData = '';
            if (typeof window.getDesignImageData === 'function') {
                designImageData = window.getDesignImageData();
            }
            data.design_image = designImageData;
        }

        // Mark as processing and disable button
        btn.addClass('processing');
        btn.prop('disabled', true);
        btn.find('svg').css('opacity', '0.5');

        $.ajax({
            url: base_url + "WishlistCon/add_ajax",
            method: "POST",
            data: data,
            dataType: "json",
            success: function(res) {
                btn.removeClass('processing');
                btn.find('svg').css('opacity', '1');

                if (res.status === 'success') {
                    setWishlistActive(true);
                    
                    // Keep button disabled after successful add
                    btn.prop('disabled', true);
                    btn.css('cursor', 'default');
                    
                    // Show success notification
                    showNotification('Added to Wishlist!', 'success');
                    
                    // Update wishlist counter if exists
                    if ($('#wishlist-count').length) {
                        $('#wishlist-count').text(res.wishlist_count);
                    }
                } else if (res.status === 'exists') {
                    // Item already exists - keep it disabled
                    setWishlistActive(false);
                    btn.prop('disabled', true);
                    btn.css('cursor', 'default');
                    showNotification('Already in Wishlist', 'info');
                } else {
                    // Error - re-enable button
                    btn.prop('disabled', false);
                    showNotification(res.message || 'Error adding to wishlist', 'error');
                }
            },
            error: function(xhr) {
                btn.removeClass('processing');
                btn.prop('disabled', false);
                btn.find('svg').css('opacity', '1');
                
                // Check if user is not logged in
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    showNotification(xhr.responseJSON.message, 'error');
                } else {
                    showNotification('Please log in to add to wishlist', 'error');
                }
            }
        });
    });

    // Simple notification function
    function showNotification(message, type) {
        // Remove existing notification
        $('.wishlist-notification').remove();

        const bgColor = type === 'success' ? '#28a745' : 
                       type === 'error' ? '#dc3545' : 
                       type === 'info' ? '#17a2b8' : '#333';
        
        const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : '♥';

        const notification = $(`
            <div class="wishlist-notification" style="
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${bgColor};
                color: white;
                padding: 15px 25px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                font-family: 'Montserrat', sans-serif;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 10px;
                animation: notifSlideIn 0.3s ease;
            ">
                <span style="font-size: 18px;">${icon}</span>
                ${message}
            </div>
        `);

        // Add notification animation style if not exists
        if (!$('#wishlist-notification-style').length) {
            $('head').append(`
                <style id="wishlist-notification-style">
                    @keyframes notifSlideIn {
                        from {
                            opacity: 0;
                            transform: translateX(100px);
                        }
                        to {
                            opacity: 1;
                            transform: translateX(0);
                        }
                    }
                    @keyframes notifSlideOut {
                        from {
                            opacity: 1;
                            transform: translateX(0);
                        }
                        to {
                            opacity: 0;
                            transform: translateX(100px);
                        }
                    }
                </style>
            `);
        }

        $('body').append(notification);

        // Auto remove after 3 seconds
        setTimeout(function() {
            notification.css('animation', 'notifSlideOut 0.3s ease');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
});
