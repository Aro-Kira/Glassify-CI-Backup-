document.addEventListener("DOMContentLoaded", () => {
  const popup = document.getElementById("productPopup");
  const closeBtn = document.getElementById("closePopup");
  const cancelBtn = popup ? popup.querySelector(".cancel-btn") : null;
  const actionMenu = document.getElementById("actionMenu");

  let activeRow = null;

  // When clicking the action cell (⋮), mark the row as active
  let activeCell = null;
  
  document.querySelectorAll(".action-cell").forEach(cell => {
    cell.addEventListener("click", (e) => {
      e.stopPropagation();
      const row = e.target.closest("tr");

      // Remove previous active-row
      document.querySelectorAll(".payment-table tbody tr").forEach(r => {
        r.classList.remove("active-row");
      });

      // Set this row as active
      row.classList.add("active-row");
      activeRow = row;
      activeCell = cell;

      // Position the action menu using fixed positioning
      const rect = cell.getBoundingClientRect();
      if (actionMenu) {
        actionMenu.style.position = 'fixed';
        actionMenu.style.top = `${rect.bottom}px`;
        actionMenu.style.left = `${rect.left}px`;
        actionMenu.style.zIndex = '1000';
        actionMenu.classList.remove("hidden");
        actionMenu.style.display = 'block';
      }
    });
  });

  // Update menu position on scroll
  let isScrolling = false;
  window.addEventListener('scroll', function() {
    if (actionMenu && actionMenu.style.display === 'block' && activeCell) {
      if (!isScrolling) {
        isScrolling = true;
        requestAnimationFrame(function updatePosition() {
          if (activeCell && actionMenu && actionMenu.style.display === 'block') {
            const rect = activeCell.getBoundingClientRect();
            
            // Check if cell is still visible
            if (rect.top < window.innerHeight && rect.bottom > 0) {
              actionMenu.style.top = `${rect.bottom}px`;
              actionMenu.style.left = `${rect.left}px`;
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
  }, { passive: true });

  // Handle View Receipt click
  if (actionMenu) {
    document.querySelectorAll("#actionMenu a").forEach(link => {
      if (link.textContent.trim() === "View Receipt") {
        link.addEventListener("click", (e) => {
          e.preventDefault();

          if (!activeRow) return;

        // Extract order ID from active row
        const orderId = activeRow.cells[1].textContent;
        
        // Show popup immediately with order ID
        if (popup) {
          const h3 = popup.querySelector("h3");
          if (h3) {
            h3.textContent = `Order ID: ${orderId}`;
          }
        }
        
        // Hide action menu after clicking
        if (actionMenu) {
            actionMenu.style.display = 'none';
            actionMenu.classList.add('hidden');
        }
        
        // Show popup
        if (popup) {
          popup.style.display = "flex";
        }
        
        // Fetch payment details from database (Admin version)
        fetch(base_url + 'AdminCon/get_payment_details', {
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
                
                if (!popup) return;
                
                // Fill popup fields with database data - from payment table
                const customerLabel = popup.querySelector(".form-group label");
                if (customerLabel) {
                    const customerSpan = customerLabel.querySelector("#popupCustomer");
                    if (customerSpan) {
                        customerSpan.textContent = paymentData.customer_name || 'N/A';
                    } else {
                        customerLabel.textContent = `Customer: ${paymentData.customer_name || 'N/A'}`;
                    }
                }
                
                const priceInput = popup.querySelector("#popupPrice");
                if (priceInput) {
                    priceInput.value = parseFloat(paymentData.amount || 0).toFixed(2);
                }
                
                // Set payment method (Gcash or Cash) - from database payment table
                let methodDisplay = 'Not Selected';
                if (paymentData.payment_method === 'E-Wallet') {
                    methodDisplay = 'Gcash';
                } else if (paymentData.payment_method === 'Cash on Delivery') {
                    methodDisplay = 'Cash';
                }
                
                const methodField = popup.querySelector(".method-field");
                if (methodField) {
                    methodField.innerHTML = `<label>Method: <span id="popupMethod">${methodDisplay}</span></label>`;
                }

                // Set receipt image (priority - show receipt if available)
                const receiptImg = document.getElementById("popupReceiptImage");
                if (receiptImg) {
                    if (paymentData.receipt_path) {
                        // Check if it's a full URL or relative path
                        let receiptUrl = paymentData.receipt_path;
                        if (!paymentData.receipt_path.startsWith('http://') && !paymentData.receipt_path.startsWith('https://')) {
                            // It's a relative path, check if it needs base_url
                            if (paymentData.receipt_path.startsWith('uploads/') || paymentData.receipt_path.startsWith('assets/')) {
                                receiptUrl = base_url + paymentData.receipt_path;
                            } else {
                                receiptUrl = base_url + 'uploads/' + paymentData.receipt_path;
                            }
                        }
                        receiptImg.src = receiptUrl;
                        receiptImg.style.display = 'block';
                        receiptImg.onerror = function() {
                            // If receipt image fails to load, hide it and show product image instead
                            this.style.display = 'none';
                            showProductImage(paymentData.product_image);
                        };
                    } else {
                        receiptImg.style.display = 'none';
                        // If no receipt, show product image
                        showProductImage(paymentData.product_image);
                    }
                } else {
                    // Fallback: show product image if receipt image element doesn't exist
                    showProductImage(paymentData.product_image);
                }
                
                // Helper function to show product image
                function showProductImage(productImage) {
                    const productImg = document.getElementById("popupProductImage");
                    if (productImg && productImage) {
                        // Check if it's a full URL or relative path
                        let imageUrl = productImage;
                        if (!productImage.startsWith('http://') && !productImage.startsWith('https://')) {
                            // It's a relative path, check if it needs base_url
                            if (productImage.startsWith('uploads/') || productImage.startsWith('assets/')) {
                                imageUrl = base_url + productImage;
                            } else {
                                imageUrl = base_url + 'uploads/' + productImage;
                            }
                        }
                        productImg.src = imageUrl;
                        productImg.style.display = 'block';
                        productImg.onerror = function() {
                            // If image fails to load, hide it
                            this.style.display = 'none';
                        };
                    } else if (productImg) {
                        productImg.style.display = 'none';
                    }
                }
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
        });
      }
    });
  }

         // Close popup
         if (closeBtn) {
           closeBtn.addEventListener("click", () => {
             if (popup) popup.style.display = "none";
           });
         }
         
         // Cancel button handler
         if (cancelBtn) {
             cancelBtn.addEventListener("click", () => {
                 if (popup) popup.style.display = "none";
             });
         }
         
         // "Mark as Paid" button handler
         if (popup) {
           const markAsPaidBtn = popup.querySelector(".save-btn");
           if (markAsPaidBtn) {
               markAsPaidBtn.addEventListener("click", function() {
                   // Try multiple ways to get the order ID
                   let orderId = null;
                   
                   // Method 1: Get from span with id
                   const orderIdEl = document.getElementById('popupOrderId');
                   if (orderIdEl) {
                       orderId = orderIdEl.textContent.trim();
                   }
                   
                   // Method 2: Get from h3 span
                   if (!orderId || orderId === '#') {
                       const h3Span = popup.querySelector("h3 span");
                       if (h3Span) {
                           orderId = h3Span.textContent.trim();
                       }
                   }
                   
                   // Method 3: Get from h3 text content
                   if (!orderId || orderId === '#') {
                       const h3 = popup.querySelector("h3");
                       if (h3) {
                           const h3Text = h3.textContent.trim();
                           // Extract order ID from "Order ID: #GI020" format
                           const match = h3Text.match(/Order ID:\s*(.+)/i);
                           if (match) {
                               orderId = match[1].trim();
                           }
                       }
                   }
                   
                   if (!orderId || orderId === '#') {
                       alert('Order ID not found. Please try closing and reopening the popup.');
                       console.error('Order ID not found. Popup HTML:', popup.innerHTML);
                       return;
                   }
                   
                   // Confirm action
                   if (!confirm('Are you sure you want to mark this payment as paid?')) {
                       return;
                   }
                   
                   // Disable button to prevent double-clicking
                   markAsPaidBtn.disabled = true;
                   markAsPaidBtn.textContent = 'Processing...';
                   
                   // Send AJAX request to mark payment as paid (Admin version)
                   fetch(base_url + 'AdminCon/mark_payment_paid', {
                       method: 'POST',
                       headers: {
                           'Content-Type': 'application/x-www-form-urlencoded'
                       },
                       body: 'order_id=' + encodeURIComponent(orderId)
                   })
                   .then(response => {
                       // First check if response is ok
                       if (!response.ok) {
                           // Try to get error message from response
                           return response.text().then(text => {
                               let errorData;
                               try {
                                   errorData = JSON.parse(text);
                               } catch (e) {
                                   errorData = { message: text || 'Server error: ' + response.status };
                               }
                               throw new Error(errorData.message || 'Server error: ' + response.status);
                           });
                       }
                       return response.json();
                   })
                   .then(data => {
                       if (data.success) {
                           // Update the table row without reloading the page
                           updatePaymentStatusInTable(orderId, 'Paid');
                           
                           alert('Payment marked as paid successfully!');
                           // Close popup
                           if (popup) popup.style.display = "none";
                       } else {
                           let errorMsg = 'Failed to mark payment as paid: ' + (data.message || 'Unknown error');
                           if (data.error_details) {
                               errorMsg += '\n\nDetails:\n' + JSON.stringify(data.error_details, null, 2);
                           }
                           alert(errorMsg);
                           console.error('Payment update failed:', data);
                           markAsPaidBtn.disabled = false;
                           markAsPaidBtn.textContent = 'Mark as Paid';
                       }
                   })
                   .catch(error => {
                       console.error('Error marking payment as paid:', error);
                       alert('An error occurred while marking payment as paid:\n\n' + error.message + '\n\nPlease check the browser console for more details.');
                       markAsPaidBtn.disabled = false;
                       markAsPaidBtn.textContent = 'Mark as Paid';
                   });
               });
           }
         }
         
         if (popup) {
           window.addEventListener("click", (e) => {
             if (e.target === popup) {
               popup.style.display = "none";
             }
           });
         }
         
         // Function to update payment status in the table
         function updatePaymentStatusInTable(orderId, status) {
             // Find the table row with matching order ID
             const tableRows = document.querySelectorAll('.payment-table tbody tr[data-order-id]');
             
             // Helper function to extract numeric order ID from formatted string (e.g., "#GI020" -> 20)
             const extractNumericOrderId = (id) => {
                 if (!id) return null;
                 let clean = id.toString().replace('#', '').trim().toUpperCase();
                 // Remove GI prefix if present
                 if (clean.startsWith('GI')) {
                     clean = clean.substring(2);
                 }
                 // Remove leading zeros and convert to number
                 const numeric = parseInt(clean, 10);
                 return isNaN(numeric) ? null : numeric;
             };
             
             // Try to get numeric order ID from the provided orderId
             const numericOrderId = extractNumericOrderId(orderId);
             
             tableRows.forEach(row => {
                 // Get numeric order ID from data-order-id attribute (most reliable)
                 const rowDataOrderId = row.getAttribute('data-order-id');
                 const rowNumericId = rowDataOrderId ? parseInt(rowDataOrderId, 10) : null;
                 
                 // Also get formatted order ID from the cell for fallback matching
                 const rowOrderIdCell = row.cells[1];
                 const rowOrderId = rowOrderIdCell ? rowOrderIdCell.textContent.trim() : '';
                 
                 // Check if this row matches the order ID
                 // Priority: match by numeric ID (most reliable), then by formatted string
                 let isMatch = false;
                 
                 if (numericOrderId && rowNumericId && numericOrderId === rowNumericId) {
                     isMatch = true;
                 } else if (rowOrderId && orderId) {
                     // Fallback: compare formatted strings (normalize by removing # and case)
                     const normalizedOrderId = orderId.replace('#', '').trim().toUpperCase();
                     const normalizedRowOrderId = rowOrderId.replace('#', '').trim().toUpperCase();
                     isMatch = normalizedOrderId === normalizedRowOrderId;
                 }
                 
                 if (isMatch) {
                         // Update the data-payment-status attribute
                         row.setAttribute('data-payment-status', status.toLowerCase());
                         
                         // Find the status cell (6th column, index 5)
                         const statusCell = row.cells[5];
                         if (statusCell) {
                             // Update the status badge
                             const statusLower = status.toLowerCase();
                             let badgeClass = 'pending';
                             let badgeText = 'Pending';
                             
                             if (statusLower === 'paid') {
                                 badgeClass = 'paid';
                                 badgeText = 'Paid';
                             } else if (statusLower === 'overdue') {
                                 badgeClass = 'overdue';
                                 badgeText = 'Overdue';
                             } else if (statusLower === 'under review') {
                                 badgeClass = 'review';
                                 badgeText = 'Under Review';
                             } else if (statusLower === 'failed') {
                                 badgeClass = 'overdue';
                                 badgeText = 'Failed';
                             }
                             
                             statusCell.innerHTML = `<span class="status-badge ${badgeClass}">${badgeText}</span>`;
                         }
                         
                         // Update stats counters
                         updatePaymentStats();
                     }
             });
         }
         
         // Function to update payment statistics
         function updatePaymentStats() {
             const tableRows = document.querySelectorAll('.payment-table tbody tr[data-order-id]');
             let pendingCount = 0;
             let overdueCount = 0;
             
             tableRows.forEach(row => {
                 const status = row.getAttribute('data-payment-status') || 'pending';
                 
                 if (status === 'pending') {
                     pendingCount++;
                 } else if (status === 'overdue') {
                     overdueCount++;
                 }
             });
             
             // Update the stat values
             const pendingStatEl = document.getElementById('statPendingValue');
             const overdueStatEl = document.getElementById('statOverdueValue');
             
             if (pendingStatEl) {
                 pendingStatEl.textContent = pendingCount;
             }
             if (overdueStatEl) {
                 overdueStatEl.textContent = overdueCount;
             }
         }
       });
