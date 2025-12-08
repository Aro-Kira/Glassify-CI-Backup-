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
                            const awaitingTotalEl = document.getElementById('awaiting-total');
                            if (awaitingTotalEl) {
                                const totalAmount = parseFloat(order.TotalAmount || order.TotalQuotation || 0);
                                awaitingTotalEl.textContent = '₱' + totalAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                            
                            // Conditionally show/hide fields based on product category
                            if (typeof showHideFieldsByCategory === 'function') {
                                showHideFieldsByCategory('awaiting', order.ProductCategory || '', order);
                            }

                            // Handle file attachment with thumbnail
                            const fileThumbnail = document.getElementById('awaiting-file-thumbnail');
                            const fileThumbnailImg = document.getElementById('awaiting-file-thumbnail-img');
                            const fileLink = document.getElementById('awaiting-file-link');
                            const fileText = document.getElementById('awaiting-file-text');
                            
                            if (order.FileAttached && order.FileAttached !== 'N/A') {
                                // Build file URL - try FileUrl first, then construct from FileAttached
                                let fileUrl = order.FileUrl;
                                if (!fileUrl && order.FileAttached) {
                                    // Construct URL from file name
                                    if (order.FileAttached.startsWith('uploads/')) {
                                        fileUrl = base_url + order.FileAttached;
                                    } else {
                                        fileUrl = base_url + 'uploads/' + order.FileAttached;
                                    }
                                }
                                
                                // Get filename for display
                                const fileName = (order.FileAttached.includes('/') ? order.FileAttached.split('/').pop() : order.FileAttached);
                                
                                // Check if file is an image
                                const imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                const fileExtension = fileName.split('.').pop().toLowerCase();
                                const isImage = imageExtensions.includes(fileExtension);
                                
                                if (isImage && fileUrl && fileThumbnail && fileThumbnailImg) {
                                    // Show thumbnail for images
                                    fileThumbnail.style.display = 'block';
                                    fileThumbnailImg.src = fileUrl;
                                    fileThumbnailImg.alt = fileName;
                                    fileLink.href = fileUrl;
                                    fileLink.textContent = fileName;
                                    fileLink.style.display = 'inline';
                                    fileText.style.display = 'none';
                                } else {
                                    // Show link only for non-images
                                    if (fileThumbnail) fileThumbnail.style.display = 'none';
                                    fileLink.href = fileUrl;
                                    fileLink.textContent = fileName;
                                    fileLink.style.display = 'inline';
                                    fileText.style.display = 'none';
                                }
                            } else {
                                if (fileThumbnail) fileThumbnail.style.display = 'none';
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
    const closeBtn = document.getElementById('awaiting-close-btn');

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
    
    // Attach click listener to the 'Close' button
    if (closeBtn) {
        closeBtn.addEventListener('click', hidePopup);
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