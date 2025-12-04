// Handle Request Approval button click in the approval popup
document.addEventListener('DOMContentLoaded', function() {
    const approvalPopup = document.getElementById('approvalPopup');
    if (approvalPopup) {
        const requestApprovalBtn = approvalPopup.querySelector('.submit-btn');
        if (requestApprovalBtn) {
            requestApprovalBtn.addEventListener('click', function() {
                const orderIdElement = document.getElementById('popup-order-id');
                if (!orderIdElement) {
                    alert('Order ID not found');
                    return;
                }
                
                const orderId = orderIdElement.textContent.trim();
                if (!orderId) {
                    alert('Order ID is required');
                    return;
                }
                
                // Confirm action
                if (!confirm('Are you sure you want to request approval for this order? It will be sent to the admin for review.')) {
                    return;
                }
                
                // Disable button during request
                requestApprovalBtn.disabled = true;
                requestApprovalBtn.textContent = 'Requesting...';
                
                // Send AJAX request
                fetch(base_url + 'SalesCon/request_approval', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'order_id=' + encodeURIComponent(orderId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Approval requested successfully!');
                        // Close popup
                        approvalPopup.style.display = 'none';
                        // Store in sessionStorage that we need to switch to awaiting tab
                        sessionStorage.setItem('switchToTab', 'awaiting');
                        // Reload page to refresh order list
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to request approval. Please try again.');
                        requestApprovalBtn.disabled = false;
                        requestApprovalBtn.textContent = 'Submit to Admin';
                    }
                })
                .catch(error => {
                    console.error('Error requesting approval:', error);
                    alert('An error occurred while requesting approval. Please try again.');
                    requestApprovalBtn.disabled = false;
                    requestApprovalBtn.textContent = 'Submit to Admin';
                });
            });
        }
    }
});

