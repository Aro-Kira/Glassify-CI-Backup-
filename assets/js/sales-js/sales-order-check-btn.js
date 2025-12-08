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
                            const totalEl = document.getElementById(prefix + '-total');
                            if (totalEl) {
                                const totalAmount = parseFloat(order.TotalAmount || order.TotalQuotation || 0);
                                totalEl.textContent = '₱' + totalAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                            
                            // Conditionally show/hide fields based on product category
                            if (typeof showHideFieldsByCategory === 'function') {
                                showHideFieldsByCategory(prefix, order.ProductCategory || '', order);
                            }

                            // Handle file attachment with thumbnail
                            const fileThumbnail = document.getElementById(prefix + '-file-thumbnail');
                            const fileThumbnailImg = document.getElementById(prefix + '-file-thumbnail-img');
                            const fileLink = document.getElementById(prefix + '-file-link');
                            const fileText = document.getElementById(prefix + '-file-text');
                            
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

                // Show appropriate popup
                if (isApproved) {
                    document.getElementById('approvedPopup').style.display = 'flex';
                } else {
                    document.getElementById('disapprovedPopup').style.display = 'flex';
                }
            }
        }
    });

    // Close buttons (X button in header)
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

    // Close/Cancel buttons (footer buttons)
    const approvedPopup = document.getElementById('approvedPopup');
    const disapprovedPopup = document.getElementById('disapprovedPopup');
    
    if (approvedPopup) {
        const closeApprovedButton = document.getElementById('approved-close-btn');
        if (closeApprovedButton) {
            closeApprovedButton.addEventListener('click', function() {
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
        const closeDisapprovedButton = document.getElementById('disapproved-close-btn');
        if (closeDisapprovedButton) {
            closeDisapprovedButton.addEventListener('click', function() {
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