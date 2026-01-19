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

// Book Now Handler - Saves complete order details to customization table
$(document).on('click', '#buy-now-btn', function(e) {
    e.preventDefault();
    
    const productId = $(this).data('product-id');
    const customerId = document.body.getAttribute('data-customer-id');
    
    if (!customerId || customerId === '' || customerId === '0') {
        showToast('Please log in to continue with your purchase.', 'warning');
        setTimeout(() => {
            window.location.href = base_url + 'login';
        }, 2000);
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
    const totalQuotation = $('#sum-total').text().replace(/[₱,]/g, '') || $('#sum-total-breakdown').text().replace(/[₱,]/g, '') || '0.00';
    
    // Get current quantity
    const quantity = parseInt($('#summary-qty-input').val()) || 1;

    // Get dynamic customization
    const customizationValues = window.selectedCustomizationValues || {};
    
    // Get product name from selectedProduct (set in 2DModeling.php)
    const productName = selectedProduct ? selectedProduct.name : 'N/A';
    
    // ...
    
    const totalQuValue = $('#sum-total').text().replace(/[₱,]/g, '') || $('#sum-total-breakdown').text().replace(/[₱,]/g, '') || $('#final-price').val() || '0.00';
    const pbDataObj = JSON.parse($('#final-specs').val() || '{}').priceBreakdown || {};
    
    // Get current quantity
    const quantityVal = parseInt($('#summary-qty-input').val()) || 1;

    // Get dynamic customization
    const customValues = window.selectedCustomizationValues || {};
    
    // Get product name from selectedProduct (set in 2DModeling.php)
    const pName = selectedProduct ? selectedProduct.name : 'N/A';
    
    // ...
    
    // Prepare data to save
    const orderData = {
        customer_id: customerId,
        product_id: productId,
        product_name: pName,
        dimensions: dimensionsJson,
        dimensions_display: dimensions, // Height x Width format
        shape: shape,
        type: glassType,
        thickness: thickness,
        edge_work: edgeWork,
        frame_type: frameType,
        engraving: engraving,
        file_attached: fileAttached,
        file_paths: filePaths.length > 0 ? JSON.stringify(filePaths) : null,
        total_quotation: totalQuValue,
        quantity: quantityVal,
        customization: JSON.stringify(customValues),
        price_breakdown: JSON.stringify(pbDataObj)
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
                    showToast((res.message || 'Failed to save order details'), 'error');
                }
            } catch (e) {
                console.error('Error parsing response:', e, response);
                showToast('Error processing response. Please try again.', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            console.error('Response:', xhr.responseText);
            console.error('Status code:', xhr.status);
            showToast('Error saving order details. Please check your connection and try again.', 'error');
        }
    });
});

