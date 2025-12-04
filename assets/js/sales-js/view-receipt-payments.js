document.addEventListener("DOMContentLoaded", () => {
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
                           popup.style.display = "none";
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
           
           window.addEventListener("click", (e) => {
             if (e.target === popup) {
               popup.style.display = "none";
             }
           });
         });
