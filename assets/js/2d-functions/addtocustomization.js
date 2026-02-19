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

    const totalQuotationVal = $('#sum-total').text().replace(/[₱,]/g, '') || $('#sum-total-breakdown').text().replace(/[₱,]/g, '') || $('#final-price').val() || '0.00';
    const pbDataObjFinal = JSON.parse($('#final-specs').val() || '{}').priceBreakdown || {};

    // Collect all customization values dynamically from selectedCustomizationValues
    // This ensures we capture all fields configured in admin (numberOfPanels, operation, configuration, etc.)
    const customSelections = window.selectedCustomizationValues || {};
    
    console.log('=== ADD TO CART CUSTOMIZATION DEBUG ===');
    console.log('window.selectedCustomizationValues:', window.selectedCustomizationValues);
    console.log('customSelections:', customSelections);
    console.log('customSelections keys:', Object.keys(customSelections));
    console.log('=================================');
    
    // Get dimensions with units
    const heightValue = $('#input-height').val() || '';
    const widthValue = $('#input-width').val() || '';
    const heightUnit = $('#btn-unit-height').data('current-unit') || 'in';
    const widthUnit = $('#btn-unit-width').data('current-unit') || 'in';
    const dims = `${heightValue}${heightUnit} x ${widthValue}${widthUnit}`;
    
    // Get legacy field values (for backward compatibility)
    // These are still used if dynamic fields aren't available
    const legacyShape = $('.option-card[data-shape].active').data('shape') || customSelections.shape || '';
    const legacyType = $('.option-card[data-glass-type].active').data('glass-type') || customSelections.glassType || '';
    const legacyThickness = $('.option-card[data-thickness].active').data('thickness') || customSelections.thickness || '';
    const legacyEdge = $('.option-card[data-edge-work].active').data('edge-work') || customSelections.edgeFinish || '';
    const legacyFrame = $('.option-card[data-frame-type].active').data('frame-type') || customSelections.frameColor || '';
    
    // Get current quantity from summary if available
    let quantity = 1;
    const summaryQtyInput = $('#summary-qty-input');
    if (summaryQtyInput.length) {
        quantity = parseInt(summaryQtyInput.val()) || 1;
    }

    // Build data object with all customization values
    // Read current quantity (if the summary input exists)
    let currentQuantity = 1;
    const qtyInput = $('#summary-qty-input');
    if (qtyInput.length) {
        currentQuantity = parseInt(qtyInput.val()) || 1;
    }

    // Read quantity selected in the summary (if present)
    let bookQuantity = 1;
    const bookQtyInput = $('#summary-qty-input');
    if (bookQtyInput.length) {
        bookQuantity = parseInt(bookQtyInput.val()) || 1;
    }

    let data = {
        product_id: product_id,
        dimensions: dims,
        // Legacy fields (for backward compatibility)
        shape: legacyShape,
        type: legacyType,
        thickness: legacyThickness,
        edge: legacyEdge,
        frame: legacyFrame,
        engraving: $('#step-3 input').val() || customSelections.engraving || 'None',
        price: totalQuotationVal,
        quantity: quantity,
        design_image: designImageData,
        price_breakdown: JSON.stringify(pbDataObjFinal),
        // Include all dynamic customization values (synced with admin side)
        customization: JSON.stringify(customSelections)
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
        xhrFields: { withCredentials: true }, // Ensure session cookies are sent
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
                    showCartNotification((response.message || 'Unknown error'), 'error');
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

// Quantity increment/decrement handlers for the summary quantity input
$(document).on('click', '#qty-increase', function () {
    const input = $('#summary-qty-input');
    if (!input.length) return;
    const val = parseInt(input.val()) || 1;
    input.val(val + 1).trigger('change');
});

$(document).on('click', '#qty-decrease', function () {
    const input = $('#summary-qty-input');
    if (!input.length) return;
    const val = parseInt(input.val()) || 1;
    input.val(Math.max(1, val - 1)).trigger('change');
});

// Enforce minimum quantity of 1 when user types a value
$(document).on('change', '#summary-qty-input', function () {
    const input = $(this);
    let val = parseInt(input.val()) || 1;
    if (val < 1) val = 1;
    input.val(val);
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

    // Collect all customization values dynamically from selectedCustomizationValues
    const customizationValues = window.selectedCustomizationValues || {};
    
    // DEBUG: Log what we're capturing from 2D modeling
    console.log('=== BUY NOW - CUSTOMIZATION DEBUG ===');
    console.log('window.selectedCustomizationValues:', window.selectedCustomizationValues);
    console.log('customizationValues object:', customizationValues);
    console.log('customizationValues keys:', Object.keys(customizationValues));
    console.log('customizationValues JSON:', JSON.stringify(customizationValues));
    
    // Get dimensions with units
    const heightValue = $('#input-height').val() || '';
    const widthValue = $('#input-width').val() || '';
    const heightUnit = $('#btn-unit-height').data('current-unit') || 'in';
    const widthUnit = $('#btn-unit-width').data('current-unit') || 'in';
    const dimensions = `${heightValue}${heightUnit} x ${widthValue}${widthUnit}`;
    
    // Get legacy field values (for backward compatibility)
    const legacyShape = $('.option-card[data-shape].active').data('shape') || customizationValues.shape || '';
    const legacyType = $('.option-card[data-glass-type].active').data('glass-type') || customizationValues.glassType || '';
    const legacyThickness = $('.option-card[data-thickness].active').data('thickness') || customizationValues.thickness || '';
    const legacyEdge = $('.option-card[data-edge-work].active').data('edge-work') || customizationValues.edgeFinish || '';
    const legacyFrame = $('.option-card[data-frame-type].active').data('frame-type') || customizationValues.frameColor || '';
    
    // Read quantity selected in the summary (if present)
    let buyQuantity = 1;
    const buyQtyInput = $('#summary-qty-input');
    if (buyQtyInput.length) {
        buyQuantity = parseInt(buyQtyInput.val()) || 1;
    }

    // Read quantity selected in the summary (if present)
    let bookQuantity = 1;
    const bookQtyInput = $('#summary-qty-input');
    if (bookQtyInput.length) {
        bookQuantity = parseInt(bookQtyInput.val()) || 1;
    }

    let data = {
        product_id: product_id,
        dimensions: dimensions,
        // Legacy fields (for backward compatibility)
        shape: legacyShape,
        type: legacyType,
        thickness: legacyThickness,
        edge: legacyEdge,
        frame: legacyFrame,
        engraving: $('#step-3 input').val() || customizationValues.engraving || 'None',
        price: priceText,
        quantity: buyQuantity,
        design_image: designImageData,
        buy_now: true,
        // Include all dynamic customization values (synced with admin side)
        customization: JSON.stringify(customizationValues)
    };

    $.ajax({
        url: base_url + "CartCon/add_customized_ajax",
        type: "POST",
        data: data,
        success: function (res) {
            try {
                let response = typeof res === 'string' ? JSON.parse(res) : res;

                if (response.status === 'success') {
                    // Redirect to payment page (shipping, order summary, payment method) with the cart item selected
                    const payUrl = (typeof PAYMENT_URL === 'string' && PAYMENT_URL) ? PAYMENT_URL : ((typeof base_url === 'string' && base_url) ? base_url.replace(/\/?$/, '') + '/payment' : '/payment');
                    window.location.href = payUrl + (payUrl.indexOf('?') >= 0 ? '&' : '?') + 'selected=' + response.cart_id;
                } else {
                    showCartNotification((response.message || 'Unknown error'), 'error');
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

// Book Now button handler (for Site Assessment Orders)
$(document).on('click', '#book-now-btn', function () {
    const btn = $(this);
    const originalText = btn.html();
    const isGuest = btn.data('is-guest') === 'true' || btn.data('is-guest') === true;

    // If guest user, show authentication modal instead of proceeding
    if (isGuest) {
        $('#guest-auth-modal').removeClass('hidden-step').css('display', 'flex');
        return;
    }

    btn.prop('disabled', true).html('Processing...');

    let product_id = btn.data('product-id');
    
    // Get the design image from Konva
    let designImageData = '';
    if (typeof window.getDesignImageData === 'function') {
        designImageData = window.getDesignImageData();
    }

    // Clean price string
    let priceText = $('#sum-total').text().replace('₱', '').replace(/,/g, '').trim();

    // Collect all customization values dynamically from selectedCustomizationValues
    const customizationValues = window.selectedCustomizationValues || {};
    
    // DEBUG: Log what we're capturing from 2D modeling
    console.log('=== BOOK NOW - CUSTOMIZATION DEBUG ===');
    console.log('window.selectedCustomizationValues:', window.selectedCustomizationValues);
    console.log('customizationValues object:', customizationValues);
    console.log('customizationValues keys:', Object.keys(customizationValues));
    console.log('customizationValues JSON:', JSON.stringify(customizationValues));
    console.log('customizationValues JSON length:', JSON.stringify(customizationValues).length);
    
    // Get dimensions with units
    const heightValue = $('#input-height').val() || '';
    const widthValue = $('#input-width').val() || '';
    const heightUnit = $('#btn-unit-height').data('current-unit') || 'in';
    const widthUnit = $('#btn-unit-width').data('current-unit') || 'in';
    const dimensions = `${heightValue}${heightUnit} x ${widthValue}${widthUnit}`;
    
    // Get legacy field values (for backward compatibility)
    const legacyShape = $('.option-card[data-shape].active').data('shape') || customizationValues.shape || '';
    const legacyType = $('.option-card[data-glass-type].active').data('glass-type') || customizationValues.glassType || '';
    const legacyThickness = $('.option-card[data-thickness].active').data('thickness') || customizationValues.thickness || '';
    const legacyEdge = $('.option-card[data-edge-work].active').data('edge-work') || customizationValues.edgeFinish || '';
    const legacyFrame = $('.option-card[data-frame-type].active').data('frame-type') || customizationValues.frameColor || '';
    
    // Read quantity selected in the summary (if present)
    let bookQuantity = 1;
    const bookQtyInput = $('#summary-qty-input');
    if (bookQtyInput.length) {
        bookQuantity = parseInt(bookQtyInput.val(), 10) || 1;
    }

    let data = {
        product_id: product_id,
        dimensions: dimensions,
        // Legacy fields (for backward compatibility)
        shape: legacyShape,
        type: legacyType,
        thickness: legacyThickness,
        edge: legacyEdge,
        frame: legacyFrame,
        engraving: $('#step-3 input').val() || customizationValues.engraving || 'None',
        price: priceText,
        quantity: bookQuantity,
        design_image: designImageData,
        book_now: true, // Flag for booking instead of buying
        // Include all dynamic customization values (synced with admin side)
        customization: JSON.stringify(customizationValues)
    };

    $.ajax({
        url: base_url + "CartCon/add_customized_ajax",
        type: "POST",
        data: data,
        dataType: "json", // Ensure proper JSON parsing
        xhrFields: { withCredentials: true }, // Ensure session cookies are sent
        beforeSend: function() {
            console.log('Book Now AJAX - Sending request to:', base_url + "CartCon/add_customized_ajax");
        },
        success: function (res) {
            console.log('Book Now AJAX - Response received:', res);
            try {
                let response = typeof res === 'string' ? JSON.parse(res) : res;

                if (response.status === 'success') {
                    // Redirect to booking page (shipping, preferred visit date, order summary) with the cart item selected
                    const bookUrl = (typeof BASE_URL === 'string' && BASE_URL) ? BASE_URL.replace(/\/?$/, '') + '/booking' : '/booking';
                    let redirectUrl = bookUrl + (bookUrl.indexOf('?') >= 0 ? '&' : '?') + 'selected=' + response.cart_id;
                    // If this was a 'Book Now' action from the 2D review, mark origin so booking shows 'Review'
                    if (data && data.book_now) {
                        redirectUrl += '&from=review';
                    }
                    window.location.href = redirectUrl;
                } else {
                    showCartNotification((response.message || 'Unknown error'), 'error');
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

// Download Quotation button handler
$(document).on('click', '#download-quotation-btn', function () {
    const btn = $(this);
    const originalText = btn.html();

    btn.prop('disabled', true).html('Generating PDF...');

    // Get quotation content
    const quotationContainer = $('.quotation-preview-container').clone();
    
    // Create a printable window with the quotation
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    
    if (!printWindow) {
        showCartNotification('Please allow popups to download the quotation', 'error');
        btn.prop('disabled', false).html(originalText);
        return;
    }

    const quotationNumber = $('#quotation-number').text();
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Quotation - QT-${quotationNumber}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: 'Segoe UI', Arial, sans-serif; 
                    padding: 40px;
                    color: #333;
                }
                .quotation-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 3px solid #1a3a1a;
                }
                .quotation-company {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }
                .quotation-logo {
                    width: 60px;
                    height: 60px;
                    object-fit: contain;
                }
                .quotation-company-info strong {
                    display: block;
                    font-size: 1.4rem;
                    color: #1a3a1a;
                }
                .quotation-company-info span {
                    font-size: 0.85rem;
                    color: #666;
                }
                .quotation-meta {
                    text-align: right;
                }
                .quotation-label {
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: #1a3a1a;
                    letter-spacing: 2px;
                }
                .quotation-number, .quotation-date {
                    font-size: 0.9rem;
                    color: #666;
                    margin-top: 5px;
                }
                .quotation-customer-info {
                    margin: 20px 0;
                    padding: 15px;
                    background: #f5f5f5;
                    border-radius: 5px;
                }
                .quotation-product-info {
                    margin-bottom: 20px;
                }
                .quotation-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                .quotation-table th {
                    background: #1a3a1a;
                    color: white;
                    padding: 12px 15px;
                    text-align: left;
                }
                .quotation-table th.text-right {
                    text-align: right;
                }
                .quotation-table td {
                    padding: 12px 15px;
                    border-bottom: 1px solid #eee;
                }
                .quotation-table td.text-right {
                    text-align: right;
                }
                .quotation-table tfoot tr {
                    background: #f9f9f9;
                }
                .quotation-table .quotation-total td {
                    border-top: 2px solid #1a3a1a;
                    font-size: 1.1rem;
                    font-weight: bold;
                    color: #1a3a1a;
                }
                .quotation-notes {
                    margin-top: 25px;
                    padding: 15px;
                    background: #fff9e6;
                    border-left: 4px solid #f0ad4e;
                    font-size: 0.9rem;
                    color: #666;
                }
                .quotation-validity {
                    margin-top: 15px;
                    font-size: 0.9rem;
                    color: #888;
                    text-align: right;
                }
                @media print {
                    body { padding: 20px; }
                }
            </style>
        </head>
        <body>
            ${quotationContainer.html()}
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(function() { window.close(); }, 500);
                };
            </script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    
    setTimeout(function() {
        btn.prop('disabled', false).html(originalText);
    }, 1000);
});