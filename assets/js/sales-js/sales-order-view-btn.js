document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for dynamically added rows
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-view') || e.target.closest('.btn-view')) {
            const button = e.target.classList.contains('btn-view') ? e.target : e.target.closest('.btn-view');
            const orderId = button.getAttribute('data-order-id');
            
            if (orderId) {
                // Load order details from database (ALWAYS fetch fresh data)
                if (typeof loadOrderDetails === 'function') {
                    loadOrderDetails(orderId, 'awaiting');
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
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.order) {
                            const order = data.order;
                            document.getElementById('awaiting-order-id').textContent = order.OrderID;
                            document.getElementById('awaiting-product').textContent = order.ProductName;
                            document.getElementById('awaiting-address').textContent = order.Address;
                            document.getElementById('awaiting-date').textContent = order.Date;
                            document.getElementById('awaiting-shape').textContent = order.Shape;
                            document.getElementById('awaiting-dimension').textContent = order.Dimensions;
                            document.getElementById('awaiting-type').textContent = order.Type;
                            document.getElementById('awaiting-thickness').textContent = order.Thickness;
                            document.getElementById('awaiting-edgework').textContent = order.EdgeWork;
                            document.getElementById('awaiting-frametype').textContent = order.FrameType || 'N/A';
                            document.getElementById('awaiting-engraving').textContent = order.Engraving;
                            // New category-specific fields
                            const ledbacklightEl = document.getElementById('awaiting-ledbacklight');
                            const dooroperationEl = document.getElementById('awaiting-dooroperation');
                            const configurationEl = document.getElementById('awaiting-configuration');
                            if (ledbacklightEl) ledbacklightEl.textContent = order.LEDBacklight || 'N/A';
                            if (dooroperationEl) dooroperationEl.textContent = order.DoorOperation || 'N/A';
                            if (configurationEl) configurationEl.textContent = order.Configuration || 'N/A';
                            document.getElementById('awaiting-total').textContent = order.TotalAmount;
                            
                            // Conditionally show/hide fields based on product category
                            if (typeof showHideFieldsByCategory === 'function') {
                                showHideFieldsByCategory('awaiting', order.ProductCategory || '', order);
                            }

                            // Handle file attachment - always make clickable if file exists
                            const fileLink = document.getElementById('awaiting-file-link');
                            const fileText = document.getElementById('awaiting-file-text');
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
                        alert('Error loading order details. Please try again.');
                    });
                }
                
                // Show popup
                const popupOverlay = document.getElementById('awaitingPopup');
                if (popupOverlay) {
                    popupOverlay.style.display = 'flex';
                }
            }
        }
    });

    // Select the popup overlay
    const popupOverlay = document.getElementById('awaitingPopup');
    const closeButton = document.getElementById('closeAwaitingPopup');
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