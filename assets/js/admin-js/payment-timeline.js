/**
 * Payment Timeline Modal Handler
 * Displays payment progress for orders with milestone-based payments
 */

// View payment timeline for an order
function viewPaymentTimeline(orderId) {
    console.log('viewPaymentTimeline called with orderId:', orderId);
    
    const modal = document.getElementById('paymentTimelineModal');
    const container = document.getElementById('timelineContainer');
    
    if (!modal) {
        console.error('paymentTimelineModal not found');
        showToast('Error: Payment timeline modal not found. Please refresh the page.', 'error');
        return;
    }
    
    if (!container) {
        console.error('timelineContainer not found');
        return;
    }
    
    // Show loading state
    container.innerHTML = '<div class="timeline-loading"><i class="fas fa-spinner fa-spin"></i> Loading payment timeline...</div>';
    modal.classList.add('active');
    
    console.log('Fetching timeline from:', base_url + 'AdminCon/get_payment_timeline');
    
    // Fetch timeline data
    fetch(base_url + 'AdminCon/get_payment_timeline', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'order_id=' + encodeURIComponent(orderId)
    })
    .then(response => {
        console.log('Response received:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Timeline data:', data);
        
        if (!data.success) {
            container.innerHTML = '<div class="timeline-error">Error: ' + (data.message || 'Failed to load timeline') + '</div>';
            return;
        }
        
        // Populate header info
        document.getElementById('timelineOrderNumber').textContent = data.order_number;
        document.getElementById('timelineCustomer').textContent = data.customer_name;
        document.getElementById('timelineRole').textContent = data.customer_role;
        document.getElementById('timelineTotalAmount').textContent = numberFormat(data.total_amount);
        document.getElementById('timelinePaidAmount').textContent = numberFormat(data.paid_amount);
        document.getElementById('timelineRemainingAmount').textContent = numberFormat(data.remaining_amount);
        document.getElementById('timelineProgressPercent').textContent = data.progress_percentage;
        document.getElementById('timelineProgressBar').style.width = data.progress_percentage + '%';
        
        // Build timeline
        let timelineHTML = '';
        const milestones = ['ocular_50', 'fabrication_40', 'installation_10'];
        const timeline = data.timeline;
        
        milestones.forEach((milestone, index) => {
            const item = timeline[milestone];
            const statusClass = getTimelineStatusClass(item.status);
            const statusIcon = getTimelineStatusIcon(item.status);
            const isLast = index === milestones.length - 1;
            
            timelineHTML += `
                <div class="timeline-item ${statusClass}">
                    <div class="timeline-marker">
                        <div class="timeline-icon">${statusIcon}</div>
                        ${!isLast ? '<div class="timeline-line"></div>' : ''}
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-milestone-header">
                            <strong>${item.label}</strong>
                            <span class="timeline-amount">₱${numberFormat(item.amount)}</span>
                        </div>
                        ${renderTimelineStatus(item)}
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = timelineHTML;
    })
    .catch(error => {
        console.error('Error fetching payment timeline:', error);
        container.innerHTML = '<div class="timeline-error">Error loading timeline. Please try again.</div>';
    });
}

// Close timeline modal
function closeTimelineModal() {
    const modal = document.getElementById('paymentTimelineModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('paymentTimelineModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            // Close if clicking directly on the overlay (not on the popup content)
            if (e.target === modal) {
                closeTimelineModal();
            }
        });
    }
});

// Render timeline status content
function renderTimelineStatus(item) {
    if (item.status === 'verified') {
        const payment = item.payment;
        const paymentDate = new Date(payment.date);
        const formattedDate = paymentDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        return `
            <div class="timeline-details">
                <p><strong>Status:</strong> <span class="status-badge-inline verified">✓ VERIFIED</span></p>
                <p><strong>Paid:</strong> ${formattedDate}</p>
                <p><strong>Method:</strong> ${payment.method || 'N/A'}</p>
                ${payment.transaction_id ? `<p><strong>Transaction ID:</strong> ${payment.transaction_id}</p>` : ''}
                ${payment.receipt_path ? `<p><a href="${base_url}${payment.receipt_path}" target="_blank" class="receipt-link"><i class="fas fa-file-image"></i> View Receipt</a></p>` : ''}
            </div>
        `;
    } else if (item.status === 'pending') {
        const payment = item.payment;
        const paymentDate = payment.date ? new Date(payment.date).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }) : 'N/A';
        
        return `
            <div class="timeline-details">
                <p><strong>Status:</strong> <span class="status-badge-inline pending">⏳ PENDING VERIFICATION</span></p>
                <p><strong>Submitted:</strong> ${paymentDate}</p>
                <p><strong>Method:</strong> ${payment.method || 'N/A'}</p>
                ${payment.receipt_path ? `<p><a href="${base_url}${payment.receipt_path}" target="_blank" class="receipt-link"><i class="fas fa-file-image"></i> View Receipt</a></p>` : ''}
                <div class="timeline-actions">
                    <button class="btn-verify" onclick="verifyPayment(${payment.payment_id})">
                        <i class="fas fa-check"></i> Verify Payment
                    </button>
                    <button class="btn-reject" onclick="rejectPayment(${payment.payment_id})">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
        `;
    } else if (item.status === 'failed') {
        return `
            <div class="timeline-details">
                <p><strong>Status:</strong> <span class="status-badge-inline failed">❌ FAILED</span></p>
                <p>Payment was rejected or failed.</p>
            </div>
        `;
    } else {
        // not_yet
        return `
            <div class="timeline-details">
                <p><strong>Status:</strong> <span class="status-badge-inline not-yet">○ NOT YET DUE</span></p>
                <p class="text-muted">Payment will be required after this stage is reached.</p>
            </div>
        `;
    }
}

// Get timeline status class for styling
function getTimelineStatusClass(status) {
    switch(status) {
        case 'verified': return 'timeline-verified';
        case 'pending': return 'timeline-pending';
        case 'failed': return 'timeline-failed';
        default: return 'timeline-not-yet';
    }
}

// Get timeline status icon
function getTimelineStatusIcon(status) {
    switch(status) {
        case 'verified': return '●';
        case 'pending': return '◐';
        case 'failed': return '✕';
        default: return '○';
    }
}

// Verify payment (stub - connects to existing verification system)
async function verifyPayment(paymentId) {
    const confirmed = await showConfirmationAsync('Are you sure you want to verify this payment?');
    if (!confirmed) return;
    
    // This would connect to your existing mark_payment_paid endpoint
    console.log('Verifying payment:', paymentId);
    showToast('Payment verification would be processed here. Connect to mark_payment_paid endpoint.', 'info');
    
    // Reload timeline after verification
    // closeTimelineModal();
}

// Reject payment
async function rejectPayment(paymentId) {
    const confirmed = await showConfirmationAsync('Are you sure you want to reject this payment?');
    if (!confirmed) return;
    
    console.log('Rejecting payment:', paymentId);
    showToast('Payment rejection would be processed here.', 'info');
}

// Format number with commas
function numberFormat(num) {
    return parseFloat(num).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Export payment report
function exportPaymentReport() {
    const reportData = {
        weekly_sales: document.querySelector('.stat-green .stat-value').textContent,
        monthly_sales: document.querySelector('.stat-blue .stat-value').textContent,
        pending_count: document.getElementById('statPendingValue').textContent,
        overdue_count: document.getElementById('statOverdueValue').textContent,
        milestone_breakdown: milestone_breakdown,
        export_date: new Date().toISOString()
    };
    
    // Generate CSV
    let csv = 'Payment Report\n\n';
    csv += 'Generated: ' + new Date().toLocaleString() + '\n\n';
    csv += 'Summary\n';
    csv += 'Weekly Revenue,' + reportData.weekly_sales + '\n';
    csv += 'Monthly Revenue,' + reportData.monthly_sales + '\n';
    csv += 'Pending Payments,' + reportData.pending_count + '\n';
    csv += 'Overdue Payments,' + reportData.overdue_count + '\n\n';
    csv += 'Milestone Breakdown (This Month)\n';
    csv += '50% Ocular Payments,₱' + numberFormat(milestone_breakdown.ocular_50) + '\n';
    csv += '40% Fabrication Payments,₱' + numberFormat(milestone_breakdown.fabrication_40) + '\n';
    csv += '10% Installation Payments,₱' + numberFormat(milestone_breakdown.installation_10) + '\n';
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'payment_report_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('paymentTimelineModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            // Close if clicking directly on the overlay (not on the popup content)
            if (e.target === modal) {
                closeTimelineModal();
            }
        });
    }
});
