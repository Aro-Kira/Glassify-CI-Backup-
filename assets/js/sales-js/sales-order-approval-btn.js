document.addEventListener('DOMContentLoaded', function() {
    // Select all 'Request Approval' buttons (use event delegation for dynamically added rows)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-approve') || e.target.closest('.btn-approve')) {
            const button = e.target.classList.contains('btn-approve') ? e.target : e.target.closest('.btn-approve');
            const orderId = button.getAttribute('data-order-id');
            
            if (orderId) {
                // Load order details from database (ALWAYS fetch fresh data)
                if (typeof loadOrderDetails === 'function') {
                    loadOrderDetails(orderId, 'popup');
                } else {
                    // Fallback: fetch order details with cache-busting
                    const timestamp = new Date().getTime();
                    fetch(base_url + 'SalesCon/get_order_details?t=' + timestamp, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Cache-Control': 'no-cache, no-store, must-revalidate',
                            'Pragma': 'no-cache',
                            'Expires': '0'
                        },
                        cache: 'no-store',
                        body: 'order_id=' + encodeURIComponent(orderId)
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Failed to load order details');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Order not found');
                        }
                        if (data.order) {
                            const order = data.order;
                            document.getElementById('popup-order-id').textContent = order.OrderID;
                            document.getElementById('popup-product').textContent = order.ProductName;
                            document.getElementById('popup-address').textContent = order.Address;
                            document.getElementById('popup-date').textContent = order.Date;
                            document.getElementById('popup-shape').textContent = order.Shape;
                            document.getElementById('popup-dimension').textContent = order.Dimensions;
                            document.getElementById('popup-type').textContent = order.Type;
                            document.getElementById('popup-thickness').textContent = order.Thickness;
                            document.getElementById('popup-edgework').textContent = order.EdgeWork;
                            document.getElementById('popup-frametype').textContent = order.FrameType || 'N/A';
                            document.getElementById('popup-engraving').textContent = order.Engraving;
                            // New category-specific fields
                            const ledbacklightEl = document.getElementById('popup-ledbacklight');
                            const dooroperationEl = document.getElementById('popup-dooroperation');
                            const configurationEl = document.getElementById('popup-configuration');
                            if (ledbacklightEl) ledbacklightEl.textContent = order.LEDBacklight || 'N/A';
                            if (dooroperationEl) dooroperationEl.textContent = order.DoorOperation || 'N/A';
                            if (configurationEl) configurationEl.textContent = order.Configuration || 'N/A';
                            document.getElementById('popup-total').textContent = order.TotalAmount;
                            
                            // Conditionally show/hide fields based on product category
                            if (typeof showHideFieldsByCategory === 'function') {
                                showHideFieldsByCategory('popup', order.ProductCategory || '', order);
                            }

                            // Handle file attachment - always make clickable if file exists
                            const fileLink = document.getElementById('popup-file-link');
                            const fileText = document.getElementById('popup-file-text');
                            if (order.FileAttached && order.FileAttached !== 'N/A') {
                                // Build file URL - try FileUrl first, then construct from FileAttached
                                let fileUrl = order.FileUrl;
                                if (!fileUrl && order.FileAttached) {
                                    // Construct URL from file name
                                    fileUrl = base_url + 'uploads/' + order.FileAttached;
                                }
                                fileLink.href = fileUrl;
                                fileLink.textContent = order.FileAttached;
                                fileLink.style.display = 'inline';
                                fileText.style.display = 'none';
                            } else {
                                fileLink.style.display = 'none';
                                fileText.textContent = 'N/A';
                                fileText.style.display = 'inline';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading order details:', error);
                        alert('Error loading order details: ' + error.message);
                    });
                }
                
                // Show popup
                const popupOverlay = document.getElementById('approvalPopup');
                if (popupOverlay) {
                    popupOverlay.style.display = 'flex';
                }
            }
        }
    });

    // Select the popup overlay
    const popupOverlay = document.getElementById('approvalPopup');
    const closeButton = document.getElementById('closePopup');
    const cancelButton = popupOverlay ? popupOverlay.querySelector('.cancel-btn') : null;

    // Function to hide the popup
    function hidePopup() {
        if (popupOverlay) {
            popupOverlay.style.display = 'none';
        }
    }

    // Attach click listener to the close (X) button
    if (closeButton) {
        closeButton.addEventListener('click', hidePopup);
    }
    
    // Attach click listener to the 'Cancel' button
    if (cancelButton) {
        cancelButton.addEventListener('click', hidePopup);
    }
    
    // Close popup when clicking outside the main popup box
    if (popupOverlay) {
        popupOverlay.addEventListener('click', function(e) {
            if (e.target === popupOverlay) {
                hidePopup();
            }
        });
    }
});
