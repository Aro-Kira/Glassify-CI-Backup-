// Buy Now Handler - Saves complete order details to customization table
$(document).on('click', '#buy-now-btn', function(e) {
    e.preventDefault();
    
    const productId = $(this).data('product-id');
    const customerId = document.body.getAttribute('data-customer-id');
    
    if (!customerId || customerId === '' || customerId === '0') {
        alert('Please log in to continue with your purchase.');
        window.location.href = base_url + 'login';
        return;
    }
    
    // Get all customization data from the form
    const height = $('#input-height').val();
    const width = $('#input-width').val();
    const heightUnit = $('#btn-unit-height').data('current-unit') || 'in';
    const widthUnit = $('#btn-unit-width').data('current-unit') || 'in';
    
    // Format dimensions as Height x Width
    const dimensions = `${height}${heightUnit} x ${width}${widthUnit}`;
    const dimensionsJson = JSON.stringify([height, "0", width, "0"]); // Store as JSON array
    
    const shape = $('.option-card[data-shape].active').data('shape');
    const glassType = $('.option-card[data-glass-type].active').data('glass-type');
    const thickness = $('.option-card[data-thickness].active').data('thickness');
    const edgeWork = $('.option-card[data-edge-work].active').data('edge-work');
    const frameType = $('.option-card[data-frame-type].active').data('frame-type');
    const engraving = $('#step-3 input[type="text"]').val() || 'None';
    const totalQuotation = $('#sum-total').text().replace(/[₱,]/g, '') || '0.00';
    
    // Get product name from selectedProduct (set in 2DModeling.php)
    const productName = selectedProduct ? selectedProduct.name : 'N/A';
    
    // Get file attachment if any (from upload modal)
    // Check if uploadedFiles array exists (from 2d_customization.js)
    let fileAttached = 'N/A';
    if (typeof uploadedFiles !== 'undefined' && uploadedFiles.length > 0) {
        fileAttached = uploadedFiles[0].name || uploadedFiles[0].file?.name || 'N/A';
    } else {
        // Fallback: check DOM for uploaded files
        const uploadedFilesList = document.querySelectorAll('#uploaded-files-container .uploaded-file');
        if (uploadedFilesList.length > 0) {
            const firstFile = uploadedFilesList[0];
            const fileName = firstFile.querySelector('.file-name');
            if (fileName) {
                fileAttached = fileName.textContent.trim();
            }
        }
    }
    
    // Prepare data to save
    const orderData = {
        customer_id: customerId,
        product_id: productId,
        product_name: productName,
        dimensions: dimensionsJson,
        dimensions_display: dimensions, // Height x Width format
        shape: shape,
        type: glassType,
        thickness: thickness,
        edge_work: edgeWork,
        frame_type: frameType,
        engraving: engraving,
        file_attached: fileAttached,
        total_quotation: totalQuotation
    };
    
    // Save to customization table (will clear old data and create new record)
    // Ensure base_url has trailing slash for AJAX URL
    let ajaxUrl = base_url;
    if (!ajaxUrl.endsWith('/')) {
        ajaxUrl += '/';
    }
    ajaxUrl += 'CartCon/save_buy_now_customization';
    
    console.log('AJAX URL:', ajaxUrl);
    console.log('Order Data:', orderData);
    
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: orderData,
        dataType: 'text', // Expect text response to parse manually
        success: function(response) {
            try {
                const res = typeof response === 'string' ? JSON.parse(response) : response;
                if (res.status === 'success') {
                    // Store customization ID in session/localStorage for later order creation
                    if (res.customization_id) {
                        sessionStorage.setItem('buy_now_customization_id', res.customization_id);
                    }
                    // Redirect to checkout (payment route)
                    // Ensure base_url has trailing slash
                    let paymentUrl = base_url;
                    if (!paymentUrl.endsWith('/')) {
                        paymentUrl += '/';
                    }
                    paymentUrl += 'payment';
                    console.log('Redirecting to:', paymentUrl);
                    window.location.href = paymentUrl;
                } else {
                    alert('Error: ' + (res.message || 'Failed to save order details'));
                }
            } catch (e) {
                console.error('Error parsing response:', e, response);
                alert('Error processing response. Please try again.');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            console.error('Response:', xhr.responseText);
            console.error('Status code:', xhr.status);
            alert('Error saving order details. Please check your connection and try again.');
        }
    });
});

