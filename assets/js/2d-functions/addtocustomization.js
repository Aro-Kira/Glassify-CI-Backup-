$(document).on('click', '#add-to-cart-btn', function () {

    let product_id = $(this).data('product-id');

    let data = {
        product_id: product_id,
        dimensions: $('#input-height').val() + ' x ' + $('#input-width').val(),
        shape: $('.option-card[data-shape].active').data('shape'),
        type: $('.option-card[data-glass-type].active').data('glass-type'),
        thickness: $('.option-card[data-thickness].active').data('thickness'),
        edge: $('.option-card[data-edge-work].active').data('edge-work'),
        frame: $('.option-card[data-frame-type].active').data('frame-type'),
        engraving: $('#step-3 input').val() || 'None',
        price: $('#sum-total').text().replace('₱', ''),
        quantity: 1,
        design_ref: ''
    };

    $.ajax({
        url: base_url + "CartCon/add_customized_ajax",
        type: "POST",
        data: data,
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                alert("Added to Cart!");

                // Update cart counter
                $('#cart-count').text(response.cart_count);
            } else {
                alert("Error: " + (response.message || 'Unknown error occurred'));
            }
        },
        error: function (xhr, status, error) {
            // Try to parse error response
            let errorMessage = "Server error. Try again.";
            
            if (xhr.responseText) {
                try {
                    let errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = "Error: " + errorResponse.message;
                    }
                } catch (e) {
                    // If not JSON, show status code
                    errorMessage = "Server error (Status: " + xhr.status + "). Please check browser console for details.";
                    console.error("AJAX Error:", {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                }
            }
            
            alert(errorMessage);
        }
    });

});
