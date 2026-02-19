document.addEventListener("DOMContentLoaded", () => {
  // =============================
  // TOAST NOTIFICATION SYSTEM
  // =============================
  function showToast(message, type = 'info', duration = 3000) {
      const existingToasts = document.querySelectorAll('.toast-notification');
      existingToasts.forEach(toast => {
          toast.classList.add('toast-fade-out');
          setTimeout(() => toast.remove(), 300);
      });

      const toast = document.createElement('div');
      toast.className = `toast-notification toast-${type}`;
      
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
      
      if (!document.getElementById('toast-styles')) {
          const style = document.createElement('style');
          style.id = 'toast-styles';
          style.textContent = `
              @keyframes toastSlideIn {
                  from { transform: translateX(400px); opacity: 0; }
                  to { transform: translateX(0); opacity: 1; }
              }
              @keyframes toastFadeOut {
                  from { transform: translateX(0); opacity: 1; }
                  to { transform: translateX(400px); opacity: 0; }
              }
              .toast-notification { transition: all 0.3s ease; }
              .toast-fade-out { animation: toastFadeOut 0.3s ease forwards; }
              .toast-icon { font-size: 20px; font-weight: bold; flex-shrink: 0; }
              .toast-message { flex: 1; font-size: 14px; line-height: 1.4; }
              .toast-close { background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; opacity: 0.8; transition: opacity 0.2s; flex-shrink: 0; }
              .toast-close:hover { opacity: 1; }
          `;
          document.head.appendChild(style);
      }
      
      document.body.appendChild(toast);
      setTimeout(() => {
          toast.classList.add('toast-fade-out');
          setTimeout(() => toast.remove(), 300);
      }, duration);
      
      return toast;
  }

  function showConfirmModal(message, onConfirm, onCancel = null) {
      const existingModal = document.getElementById('confirm-modal-overlay');
      if (existingModal) existingModal.remove();
      
      const overlay = document.createElement('div');
      overlay.id = 'confirm-modal-overlay';
      overlay.style.cssText = `
          position: fixed; top: 0; left: 0; width: 100%; height: 100%;
          background: rgba(0, 0, 0, 0.5); z-index: 10001;
          display: flex; align-items: center; justify-content: center;
          animation: fadeIn 0.2s ease;
      `;
      
      const modal = document.createElement('div');
      modal.style.cssText = `
          background: white; border-radius: 12px; padding: 30px;
          max-width: 450px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
          animation: slideUp 0.3s ease;
      `;
      
      modal.innerHTML = `
          <h3 style="margin: 0 0 15px 0; font-size: 20px; color: #333; font-family: 'Montserrat', sans-serif;">Confirm Action</h3>
          <p style="margin: 0 0 25px 0; color: #666; font-size: 15px; line-height: 1.5;">${message}</p>
          <div style="display: flex; gap: 10px; justify-content: flex-end;">
              <button id="confirm-cancel-btn" style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer; font-size: 14px; color: #666; transition: all 0.2s;">Cancel</button>
              <button id="confirm-ok-btn" style="padding: 10px 20px; border: none; background: #dc3545; color: white; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">Confirm</button>
          </div>
      `;
      
      overlay.appendChild(modal);
      document.body.appendChild(overlay);
      
      if (!document.getElementById('modal-styles')) {
          const style = document.createElement('style');
          style.id = 'modal-styles';
          style.textContent = `
              @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
              @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
              #confirm-cancel-btn:hover { background: #f5f5f5; }
              #confirm-ok-btn:hover { background: #c82333; }
          `;
          document.head.appendChild(style);
      }
      
      const cancelBtn = overlay.querySelector('#confirm-cancel-btn');
      const okBtn = overlay.querySelector('#confirm-ok-btn');
      
      cancelBtn.addEventListener('click', () => {
          overlay.style.animation = 'fadeIn 0.2s ease reverse';
          setTimeout(() => overlay.remove(), 200);
          if (onCancel) onCancel();
      });
      
      okBtn.addEventListener('click', () => {
          overlay.style.animation = 'fadeIn 0.2s ease reverse';
          setTimeout(() => overlay.remove(), 200);
          if (onConfirm) onConfirm();
      });
      
      overlay.addEventListener('click', (e) => {
          if (e.target === overlay) {
              overlay.style.animation = 'fadeIn 0.2s ease reverse';
              setTimeout(() => overlay.remove(), 200);
              if (onCancel) onCancel();
          }
      });
      
      const escapeHandler = (e) => {
          if (e.key === 'Escape') {
              overlay.style.animation = 'fadeIn 0.2s ease reverse';
              setTimeout(() => overlay.remove(), 200);
              if (onCancel) onCancel();
              document.removeEventListener('keydown', escapeHandler);
          }
      };
      document.addEventListener('keydown', escapeHandler);
  }

  const popup = document.getElementById("productPopup");
  const closeBtn = document.getElementById("closePopup");
  const cancelBtn = popup.querySelector(".cancel-btn");
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
      actionMenu.style.position = 'fixed';
      actionMenu.style.top = `${rect.bottom}px`;
      actionMenu.style.left = `${rect.left}px`;
      actionMenu.style.zIndex = '1000';
      actionMenu.classList.remove("hidden");
      actionMenu.style.display = 'block';
    });
  });

  // Update menu position on scroll
  let isScrolling = false;
  window.addEventListener('scroll', function() {
    if (actionMenu && actionMenu.style.display === 'block' && activeCell) {
      if (!isScrolling) {
        isScrolling = true;
        requestAnimationFrame(function updatePosition() {
          if (activeCell && actionMenu.style.display === 'block') {
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
  document.querySelectorAll("#actionMenu a").forEach(link => {
    if (link.textContent.trim() === "View Receipt") {
      link.addEventListener("click", (e) => {
        e.preventDefault();

        if (!activeRow) return;

      // Extract order ID from active row
      const orderId = activeRow.cells[1].textContent;
      
      // Show popup immediately with order ID
      popup.querySelector("h3").textContent = `Order ID: ${orderId}`;
      
      // Hide action menu after clicking
      if (actionMenu) {
          actionMenu.style.display = 'none';
          actionMenu.classList.add('hidden');
      }
      
      // Show popup
      popup.style.display = "flex";
      
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
              
              // Set payment method - from database payment table
              let methodDisplay = 'Not Selected';
              const pm = paymentData.payment_method;
              if (pm === 'GCash' || pm === 'E-Wallet') {
                  methodDisplay = 'GCash';
              } else if (pm === 'Maya') {
                  methodDisplay = 'Maya';
              } else if (pm === 'Card' || pm === 'Bank Transfer') {
                  methodDisplay = 'Credit/Debit Card';
              } else if (pm === 'Cash' || pm === 'Cash on Delivery') {
                  methodDisplay = 'Cash';
              } else if (pm === 'Check') {
                  methodDisplay = 'Check';
              } else if (pm) {
                  methodDisplay = pm;
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
              showToast('Failed to load payment details: ' + (data.message || 'Unknown error'), 'error');
          }
      })
      .catch(error => {
          console.error('Error fetching payment details:', error);
          console.error('Order ID:', orderId);
          console.error('Base URL:', base_url);
          showToast('An error occurred while loading payment details: ' + error.message + '. Please check the console for details.', 'error');
      });
      });
    }
  });

           // Close popup
           closeBtn.addEventListener("click", () => {
             popup.style.display = "none";
           });
           
           // Cancel button handler
           if (cancelBtn) {
               cancelBtn.addEventListener("click", () => {
                   popup.style.display = "none";
               });
           }
           
           // "Mark as Paid" button handler
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
                       showToast('Order ID not found. Please try closing and reopening the popup.', 'warning');
                       console.error('Order ID not found. Popup HTML:', popup.innerHTML);
                       return;
                   }
                   
                   // Confirm action
                   showConfirmModal('Are you sure you want to mark this payment as paid?', () => {
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
                           
                           showToast('Payment marked as paid successfully!', 'success');
                           // Close popup
                           popup.style.display = "none";
                       } else {
                           let errorMsg = 'Failed to mark payment as paid: ' + (data.message || 'Unknown error');
                           if (data.error_details) {
                               errorMsg += '\n\nDetails:\n' + JSON.stringify(data.error_details, null, 2);
                           }
                           showToast(errorMsg, 'error');
                           console.error('Payment update failed:', data);
                           markAsPaidBtn.disabled = false;
                           markAsPaidBtn.textContent = 'Mark as Paid';
                       }
                   })
                   .catch(error => {
                       console.error('Error marking payment as paid:', error);
                       showToast('An error occurred while marking payment as paid:\n\n' + error.message + '\n\nPlease check the browser console for more details.', 'error');
                       markAsPaidBtn.disabled = false;
                       markAsPaidBtn.textContent = 'Mark as Paid';
                   });
                   });
               });
           }
           
           window.addEventListener("click", (e) => {
             if (e.target === popup) {
               popup.style.display = "none";
             }
           });
           
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
