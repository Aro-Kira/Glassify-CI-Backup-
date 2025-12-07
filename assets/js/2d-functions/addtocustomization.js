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
                    // Show success message with SweetAlert if available, else use alert
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Added to Cart!',
                            text: 'Your customized item has been added to cart.',
                            confirmButtonColor: '#003b4d'
                        });
                    } else {
                        alert("Added to Cart! Your custom design has been saved.");
                    }

                    // Update cart counter
                    $('#cart-count').text(response.cart_count);
                } else {
                    alert("Error: " + (response.message || 'Unknown error'));
                }
            } catch (e) {
                console.error('Parse error:', e);
                alert("Added to Cart!");
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
            
            alert(errorMessage);
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
                    alert("Error: " + (response.message || 'Unknown error'));
                    btn.prop('disabled', false).html(originalText);
                }
            } catch (e) {
                console.error('Parse error:', e);
                alert("Error processing response. Please try again.");
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
            
            alert(errorMessage);
            btn.prop('disabled', false).html(originalText);
        }
    });
});
