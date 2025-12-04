document.addEventListener('DOMContentLoaded', function () {
    const actionMenu = document.getElementById('actionMenu');
    const actionCells = document.querySelectorAll('.action-cell');
    const popup = document.getElementById('productPopup');
    const closePopup = document.getElementById('closePopup');
    const receiptButtons = document.querySelectorAll('.receipt-btn');
    const viewReceiptLink = document.querySelector('#actionMenu li:first-child a'); // "View Receipt"

    let activeRow = null; // keep track of the selected row

    // Store the active cell reference
    let activeCell = null;
    
    // Store current order ID for "Mark as Paid" functionality
    let currentOrderId = null;

    // Function to update action menu position
    function updateActionMenuPosition(cell) {
        if (!actionMenu || !cell) return;
        
        const rect = cell.getBoundingClientRect();
        // Get the actual menu width after it's displayed
        actionMenu.style.display = 'block';
        const menuWidth = actionMenu.offsetWidth || 150;
        
        // Position menu directly below the action button, aligned to the right edge of the cell
        actionMenu.style.position = 'fixed';
        actionMenu.style.top = `${rect.bottom + 2}px`; // 2px gap below button
        // Align menu to the right edge of the action cell
        actionMenu.style.left = `${rect.right - menuWidth}px`;
        actionMenu.style.zIndex = '1000';
    }

    // Action menu logic
    actionCells.forEach(cell => {
        cell.addEventListener('click', function (e) {
            e.stopPropagation();
            activeRow = cell.closest('tr'); // save the clicked row
            activeCell = cell; // save the clicked cell

            updateActionMenuPosition(cell);
            actionMenu.style.display = 'block';
            actionMenu.classList.remove('hidden');
        });
    });

    // Update menu position on scroll - real-time updates
    let isScrolling = false;
    let scrollTimeout = null;
    
    function handleScroll() {
        if (actionMenu && actionMenu.style.display === 'block' && activeCell) {
            if (!isScrolling) {
                isScrolling = true;
                requestAnimationFrame(function updatePosition() {
                    if (activeCell && actionMenu.style.display === 'block') {
                        const rect = activeCell.getBoundingClientRect();
                        
                        // Check if cell is still visible
                        if (rect.top < window.innerHeight && rect.bottom > 0) {
                            // Get the actual menu width
                            const menuWidth = actionMenu.offsetWidth || 150;
                            // Position menu directly below the action button, aligned to the right edge
                            actionMenu.style.top = `${rect.bottom + 2}px`;
                            actionMenu.style.left = `${rect.right - menuWidth}px`;
                        } else {
                            // Hide menu if cell is out of view
                            actionMenu.style.display = 'none';
                            actionMenu.classList.add('hidden');
                        }
                    }
                    isScrolling = false;
                });
            }
        }
    }
    
    // Handle window scroll
    window.addEventListener('scroll', handleScroll, { passive: true });
    
    // Also handle scroll within any scrollable containers (like table containers)
    const scrollableContainers = document.querySelectorAll('.table-container, .payment-table-container, .content-area, main');
    scrollableContainers.forEach(container => {
        container.addEventListener('scroll', handleScroll, { passive: true });
    });

    // Also update on window resize
    window.addEventListener('resize', function() {
        if (actionMenu && actionMenu.style.display === 'block' && activeCell) {
            updateActionMenuPosition(activeCell);
        }
    });

    // Hide action menu when clicking outside or on any menu item
    document.addEventListener('click', function (e) {
        // Hide menu if clicking outside
        if (!actionMenu.contains(e.target) && !e.target.closest('.action-cell')) {
            actionMenu.style.display = 'none';
            actionMenu.classList.add('hidden');
        }
        // Hide menu if clicking on any menu item
        if (actionMenu.contains(e.target) && e.target.tagName === 'A') {
            actionMenu.style.display = 'none';
            actionMenu.classList.add('hidden');
        }
    });

    // 👉 Shared function: open popup with row data - fetches from database
    function openReceiptPopup(row) {
        if (!row) return;

        const orderId = row.cells[1].textContent.trim();
        
        // Store order ID for "Mark as Paid" functionality
        currentOrderId = orderId;
        
        // Show popup immediately with order ID
        if (!popup) {
            console.error('Popup element not found');
            return;
        }
        
        const popupOrderIdEl = document.getElementById("popupOrderId");
        if (popupOrderIdEl) {
            popupOrderIdEl.textContent = orderId;
        }
        popup.style.display = 'flex';
        actionMenu.style.display = 'none'; // hide menu
        
        // Fetch payment details from database
        fetch(base_url + 'SalesCon/get_payment_details', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'order_id=' + encodeURIComponent(orderId)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                const paymentData = data.data;
                
                // Wait a tiny bit to ensure popup is fully rendered
                setTimeout(() => {
                    // Fill popup fields with database data - from payment table
                    const popupCustomerEl = document.getElementById("popupCustomer");
                    if (popupCustomerEl) {
                        popupCustomerEl.textContent = paymentData.customer_name || 'N/A';
                    } else {
                        console.error('popupCustomer element not found');
                    }
                    
                    const popupPriceEl = document.getElementById("popupPrice");
                    if (popupPriceEl) {
                        // Set price from database payment table
                        popupPriceEl.value = parseFloat(paymentData.amount || 0).toFixed(2);
                    } else {
                        console.error('popupPrice element not found');
                    }
                    
                    // Set payment method (Gcash or Cash) - from database payment table
                    let methodDisplay = 'Not Selected';
                    if (paymentData.payment_method === 'E-Wallet') {
                        methodDisplay = 'Gcash';
                    } else if (paymentData.payment_method === 'Cash on Delivery') {
                        methodDisplay = 'Cash';
                    }
                    
                    const methodFieldEl = document.querySelector(".method-field");
                    if (methodFieldEl) {
                        // Update method field with data from payment table
                        methodFieldEl.innerHTML = `<label>Method: <span id="popupMethod">${methodDisplay}</span></label>`;
                    } else {
                        console.error('method-field element not found');
                    }

                    // Set product image
                    const productImg = document.getElementById("popupProductImage");
                    if (productImg) {
                        if (paymentData.product_image) {
                            // Check if it's a full URL or relative path
                            let imageUrl = paymentData.product_image;
                            if (!paymentData.product_image.startsWith('http://') && !paymentData.product_image.startsWith('https://')) {
                                // It's a relative path, check if it needs base_url
                                if (paymentData.product_image.startsWith('uploads/') || paymentData.product_image.startsWith('assets/')) {
                                    imageUrl = base_url + paymentData.product_image;
                                } else {
                                    imageUrl = base_url + 'uploads/' + paymentData.product_image;
                                }
                            }
                            productImg.src = imageUrl;
                            productImg.style.display = 'block';
                            productImg.onerror = function() {
                                // If image fails to load, hide it
                                this.style.display = 'none';
                            };
                        } else {
                            productImg.style.display = 'none';
                        }
                    }
                }, 10); // Small delay to ensure popup is rendered
            } else {
                alert('Failed to load payment details: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error fetching payment details:', error);
            console.error('Order ID:', orderId);
            console.error('Base URL:', base_url);
            alert('An error occurred while loading payment details: ' + error.message + '. Please check the console for details.');
        });
    }

    // Receipt button → open popup with row data
    receiptButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const row = btn.closest('tr');
            openReceiptPopup(row);
        });
    });

    // "View Receipt" option → open popup with active row
    viewReceiptLink.addEventListener('click', function (e) {
        e.preventDefault();
        if (activeRow) {
            openReceiptPopup(activeRow);
            // Hide action menu after clicking
            actionMenu.style.display = 'none';
            actionMenu.classList.add('hidden');
        }
    });

    // Close popup (X)
    closePopup.addEventListener('click', function () {
        popup.style.display = 'none';
    });

    // Close popup if background clicked
    popup.addEventListener('click', function (e) {
        if (e.target === popup) {
            popup.style.display = 'none';
        }
    });
    
    // "Mark as Paid" button handler
    const markAsPaidBtn = popup.querySelector('.save-btn');
    const cancelBtn = popup.querySelector('.cancel-btn');
    
    if (markAsPaidBtn) {
        markAsPaidBtn.addEventListener('click', function() {
            // Get order ID from stored variable or from DOM element
            let orderId = currentOrderId;
            
            if (!orderId) {
                // Fallback: try to get from DOM
                const orderIdEl = document.getElementById('popupOrderId');
                if (orderIdEl) {
                    orderId = orderIdEl.textContent.trim();
                }
            }
            
            if (!orderId || orderId === '#') {
                alert('Order ID not found. Please try closing and reopening the popup.');
                console.error('Order ID not found. currentOrderId:', currentOrderId);
                return;
            }
            
            // Confirm action
            if (!confirm('Are you sure you want to mark this payment as paid?')) {
                return;
            }
            
            // Disable button to prevent double-clicking
            markAsPaidBtn.disabled = true;
            markAsPaidBtn.textContent = 'Processing...';
            
            // Send AJAX request to mark payment as paid
            fetch(base_url + 'SalesCon/mark_payment_paid', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'order_id=' + encodeURIComponent(orderId)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Payment marked as paid successfully!');
                    // Close popup
                    popup.style.display = 'none';
                    // Reload page to reflect changes
                    window.location.reload();
                } else {
                    alert('Failed to mark payment as paid: ' + (data.message || 'Unknown error'));
                    markAsPaidBtn.disabled = false;
                    markAsPaidBtn.textContent = 'Mark as Paid';
                }
            })
            .catch(error => {
                console.error('Error marking payment as paid:', error);
                alert('An error occurred while marking payment as paid: ' + error.message);
                markAsPaidBtn.disabled = false;
                markAsPaidBtn.textContent = 'Mark as Paid';
            });
        });
    }
    
    // Cancel button handler
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            popup.style.display = 'none';
        });
    }
});
