// Notification function for add to cart (similar to wishlist notification)
function showCartNotification(message, type) {
    // Remove existing notification
    $('.cart-notification').remove();

    const bgColor = type === 'success' ? '#28a745' : 
                   type === 'error' ? '#dc3545' : 
                   type === 'info' ? '#17a2b8' : '#333';
    
    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';

    const notification = $(`
        <div class="cart-notification" style="
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
            animation: cartNotifSlideIn 0.3s ease;
        ">
            <span style="font-size: 18px;">${icon}</span>
            ${message}
        </div>
    `);

    // Add notification animation style if not exists
    if (!$('#cart-notification-style').length) {
        $('head').append(`
            <style id="cart-notification-style">
                @keyframes cartNotifSlideIn {
                    from {
                        opacity: 0;
                        transform: translateX(100px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }
                @keyframes cartNotifSlideOut {
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
        notification.css('animation', 'cartNotifSlideOut 0.3s ease');
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
}

$(document).on('click', '#add-to-cart-btn', function () {
    const btn = $(this);
    const originalText = btn.html();
    
    // Show loading state
    btn.prop('disabled', true).html('<span class="spinner"></span> Adding...');

    let product_id = btn.data('product-id');
    
    // Get the design image from Konva
    let designImageData = '';
    if (typeof window.getDesignImageData === 'function') {
        designImageData = window.getDesignImageData();
    }

    // Get price breakdown if available
    let priceBreakdownData = {};
    if (typeof window.getCustomizationState === 'function') {
        const state = window.getCustomizationState();
        priceBreakdownData = state.priceBreakdown || {};
    }

    // Clean price string (remove ₱ and commas)
    let priceText = $('#sum-total').text().replace('₱', '').replace(/,/g, '').trim();

    let data = {
        product_id: product_id,
        dimensions: $('#input-height').val() + ' x ' + $('#input-width').val(),
        shape: $('.option-card[data-shape].active').data('shape'),
        type: $('.option-card[data-glass-type].active').data('glass-type'),
        thickness: $('.option-card[data-thickness].active').data('thickness'),
        edge: $('.option-card[data-edge-work].active').data('edge-work'),
        frame: $('.option-card[data-frame-type].active').data('frame-type'),
        engraving: $('#step-3 input').val() || 'None',
        price: priceText,
        quantity: 1,
        design_image: designImageData,
        price_breakdown: JSON.stringify(priceBreakdownData)
    };

    // Debug: Log the data being sent
    console.log('Sending data to server:', {
        product_id: data.product_id,
        dimensions: data.dimensions,
        price: data.price,
        has_design_image: !!data.design_image,
        price_breakdown: data.price_breakdown
    });

    $.ajax({
        url: base_url + "CartCon/add_customized_ajax",
        type: "POST",
        data: data,
        success: function (res) {
            try {
                let response = typeof res === 'string' ? JSON.parse(res) : res;

                if (response.status === 'success') {
                    // Show success notification (similar to wishlist)
                    showCartNotification('Added to Cart!', 'success');

                    // Update cart counter
                    if ($('#cart-count').length) {
                        $('#cart-count').text(response.cart_count);
                        $('#cart-count').toggle(response.cart_count > 0);
                    }
                } else {
                    showCartNotification("Error: " + (response.message || 'Unknown error'), 'error');
                }
            } catch (e) {
                console.error('Parse error:', e);
                showCartNotification('Added to Cart!', 'success');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            
            let errorMessage = "Server error. Please try again.";
            
            // Try to parse error response if it's JSON
            try {
                if (xhr.responseText) {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                }
            } catch (e) {
                // If not JSON, show the raw response or status
                if (xhr.status === 500) {
                    errorMessage = "Internal server error. Please check the console for details.";
                } else if (xhr.status === 404) {
                    errorMessage = "Page not found. Please refresh and try again.";
                }
            }
            
            showCartNotification(errorMessage, 'error');
        },
        complete: function() {
            // Restore button state
            btn.prop('disabled', false).html(originalText);
        }
    });

});

// Buy Now button handler
$(document).on('click', '#buy-now-btn', function () {
    const btn = $(this);
    const originalText = btn.html();
    
    btn.prop('disabled', true).html('Processing...');

    let product_id = btn.data('product-id');
    
    // Get the design image from Konva
    let designImageData = '';
    if (typeof window.getDesignImageData === 'function') {
        designImageData = window.getDesignImageData();
    }

    // Clean price string
    let priceText = $('#sum-total').text().replace('₱', '').replace(/,/g, '').trim();

    let data = {
        product_id: product_id,
        dimensions: $('#input-height').val() + ' x ' + $('#input-width').val(),
        shape: $('.option-card[data-shape].active').data('shape'),
        type: $('.option-card[data-glass-type].active').data('glass-type'),
        thickness: $('.option-card[data-thickness].active').data('thickness'),
        edge: $('.option-card[data-edge-work].active').data('edge-work'),
        frame: $('.option-card[data-frame-type].active').data('frame-type'),
        engraving: $('#step-3 input').val() || 'None',
        price: priceText,
        quantity: 1,
        design_image: designImageData,
        buy_now: true
    };

    $.ajax({
        url: base_url + "CartCon/add_customized_ajax",
        type: "POST",
        data: data,
        success: function (res) {
            try {
                let response = typeof res === 'string' ? JSON.parse(res) : res;

                if (response.status === 'success') {
                    // Redirect to checkout with the cart item selected
                    window.location.href = base_url + 'payment?selected=' + response.cart_id;
                } else {
                    showCartNotification("Error: " + (response.message || 'Unknown error'), 'error');
                    btn.prop('disabled', false).html(originalText);
                }
            } catch (e) {
                console.error('Parse error:', e);
                showCartNotification("Error processing response. Please try again.", 'error');
                btn.prop('disabled', false).html(originalText);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            
            let errorMessage = "Server error. Please try again.";
            
            // Try to parse error response if it's JSON
            try {
                if (xhr.responseText) {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                }
            } catch (e) {
                // If not JSON, show the raw response or status
                if (xhr.status === 500) {
                    errorMessage = "Internal server error. Please check the console for details.";
                } else if (xhr.status === 404) {
                    errorMessage = "Page not found. Please refresh and try again.";
                }
            }
            
            showCartNotification(errorMessage, 'error');
            btn.prop('disabled', false).html(originalText);
        }
    });
});
