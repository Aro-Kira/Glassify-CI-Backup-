document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for dynamically added rows
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-check') || e.target.closest('.btn-check')) {
            const button = e.target.classList.contains('btn-check') ? e.target : e.target.closest('.btn-check');
            const orderId = button.getAttribute('data-order-id');
            
            if (orderId) {
                // Find the status in the same table row
                const row = button.closest('tr');
                const statusSpan = row ? row.querySelector('.status') : null;
                const status = statusSpan ? statusSpan.textContent.trim() : 'Pending';

                // Determine if approved or disapproved
                const isApproved = status === 'Approved' || status === 'Ready to Approve';

                // Load order details from database (ALWAYS fetch fresh data)
                const popupType = isApproved ? 'approved' : 'disapproved';
                if (typeof loadOrderDetails === 'function') {
                    loadOrderDetails(orderId, popupType);
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
                            const prefix = popupType;
                            
                            document.getElementById(prefix + '-order-id').textContent = order.OrderID;
                            document.getElementById(prefix + '-product').textContent = order.ProductName;
                            document.getElementById(prefix + '-address').textContent = order.Address;
                            document.getElementById(prefix + '-date').textContent = order.Date;
                            document.getElementById(prefix + '-shape').textContent = order.Shape;
                            document.getElementById(prefix + '-dimension').textContent = order.Dimensions;
                            document.getElementById(prefix + '-type').textContent = order.Type;
                            document.getElementById(prefix + '-thickness').textContent = order.Thickness;
                            document.getElementById(prefix + '-edgework').textContent = order.EdgeWork;
                            document.getElementById(prefix + '-frametype').textContent = order.FrameType || 'N/A';
                            document.getElementById(prefix + '-engraving').textContent = order.Engraving;
                            // New category-specific fields
                            const ledbacklightEl = document.getElementById(prefix + '-ledbacklight');
                            const dooroperationEl = document.getElementById(prefix + '-dooroperation');
                            const configurationEl = document.getElementById(prefix + '-configuration');
                            if (ledbacklightEl) ledbacklightEl.textContent = order.LEDBacklight || 'N/A';
                            if (dooroperationEl) dooroperationEl.textContent = order.DoorOperation || 'N/A';
                            if (configurationEl) configurationEl.textContent = order.Configuration || 'N/A';
                            document.getElementById(prefix + '-total').textContent = order.TotalAmount;
                            
                            // Conditionally show/hide fields based on product category
                            if (typeof showHideFieldsByCategory === 'function') {
                                showHideFieldsByCategory(prefix, order.ProductCategory || '', order);
                            }

                            // Handle file attachment - always make clickable if file exists
                            const fileLink = document.getElementById(prefix + '-file-link');
                            const fileText = document.getElementById(prefix + '-file-text');
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

                // Show appropriate popup
                if (isApproved) {
                    document.getElementById('approvedPopup').style.display = 'flex';
                } else {
                    document.getElementById('disapprovedPopup').style.display = 'flex';
                }
            }
        }
    });

    // Close buttons
    const closeApprovedBtn = document.getElementById('closeApprovedPopup');
    const closeDisapprovedBtn = document.getElementById('closeDisapprovedPopup');
    
    if (closeApprovedBtn) {
        closeApprovedBtn.addEventListener('click', function() {
            document.getElementById('approvedPopup').style.display = 'none';
        });
    }
    
    if (closeDisapprovedBtn) {
        closeDisapprovedBtn.addEventListener('click', function() {
            document.getElementById('disapprovedPopup').style.display = 'none';
        });
    }

    // Cancel buttons
    const approvedPopup = document.getElementById('approvedPopup');
    const disapprovedPopup = document.getElementById('disapprovedPopup');
    
    if (approvedPopup) {
        const cancelApprovedButton = approvedPopup.querySelector('.cancel-btn');
        if (cancelApprovedButton) {
            cancelApprovedButton.addEventListener('click', function() {
                approvedPopup.style.display = 'none';
            });
        }
        
        approvedPopup.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }
    
    if (disapprovedPopup) {
        const cancelDisapprovedButton = disapprovedPopup.querySelector('.cancel-btn');
        if (cancelDisapprovedButton) {
            cancelDisapprovedButton.addEventListener('click', function() {
                disapprovedPopup.style.display = 'none';
            });
        }
        
        disapprovedPopup.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }
});