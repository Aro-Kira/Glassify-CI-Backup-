document.addEventListener('DOMContentLoaded', function() {
    // ======================
    // VARIABLES
    // ======================
    const tbody = document.querySelector('#returnOrdersTableBody');
    const foundText = document.querySelector('.found-text');
    const returnOrderDetailsModal = document.getElementById('returnOrderDetailsModal');
    const closeReturnDetails = document.getElementById('closeReturnDetails');
    const actionMenu = document.getElementById('actionMenu');
    
    // Filter elements
    const statusFilter = document.getElementById('status-filter');
    const dateStart = document.getElementById('date-range-start');
    const dateEnd = document.getElementById('date-range-end');
    const clientSearch = document.getElementById('client-search');
    const orderSearch = document.getElementById('order-search');
    const returnTypeFilter = document.getElementById('return-type-filter');
    const applyFiltersBtn = document.getElementById('apply-filters');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    // Pagination
    let currentPage = 1;
    let totalPages = 1;
    const itemsPerPage = 10;
    
    // Current return order for details modal
    let currentReturnOrder = null;
    
    // ======================
    // INITIALIZATION
    // ======================
    loadReturnOrders();
    
    // Event listeners
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            currentPage = 1;
            loadReturnOrders();
        });
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            clearFilters();
            currentPage = 1;
            loadReturnOrders();
        });
    }
    
    if (closeReturnDetails) {
        closeReturnDetails.addEventListener('click', () => {
            returnOrderDetailsModal.classList.remove('active');
        });
    }
    
    // Close modal on overlay click
    if (returnOrderDetailsModal) {
        returnOrderDetailsModal.addEventListener('click', (e) => {
            if (e.target === returnOrderDetailsModal) {
                returnOrderDetailsModal.classList.remove('active');
            }
        });
    }
    
    // Action buttons
    const approveBtn = document.getElementById('approve-return-btn');
    const rejectBtn = document.getElementById('reject-return-btn');
    const processRefundBtn = document.getElementById('process-refund-btn');
    const createReplacementBtn = document.getElementById('create-replacement-btn');
    const scheduleReplacementBtn = document.getElementById('schedule-replacement-btn');
    const updateStatusBtn = document.getElementById('update-status-btn');
    const confirmRejectBtn = document.getElementById('confirm-reject-btn');
    const saveNotesBtn = document.getElementById('save-notes-btn');
    
    if (approveBtn) {
        approveBtn.addEventListener('click', () => approveReturn());
    }
    
    if (rejectBtn) {
        rejectBtn.addEventListener('click', () => {
            document.getElementById('rejection-reason-group').style.display = 'block';
        });
    }
    
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', () => rejectReturn());
    }
    
    if (processRefundBtn) {
        processRefundBtn.addEventListener('click', () => processRefund());
    }
    
    if (createReplacementBtn) {
        createReplacementBtn.addEventListener('click', () => createReplacementOrder());
    }
    
    if (scheduleReplacementBtn) {
        scheduleReplacementBtn.addEventListener('click', () => scheduleReplacementAppointment());
    }
    
    if (updateStatusBtn) {
        updateStatusBtn.addEventListener('click', () => updateReturnStatus());
    }
    
    if (saveNotesBtn) {
        saveNotesBtn.addEventListener('click', () => saveAdminNotes());
    }
    
    // ======================
    // LOAD RETURN ORDERS
    // ======================
    async function loadReturnOrders() {
        if (!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px;">Loading return orders...</td></tr>';
        
        try {
            const params = new URLSearchParams({
                status: statusFilter ? statusFilter.value : 'all',
                page: currentPage,
                limit: itemsPerPage
            });
            
            if (dateStart && dateStart.value) {
                params.append('date_start', dateStart.value);
            }
            if (dateEnd && dateEnd.value) {
                params.append('date_end', dateEnd.value);
            }
            if (clientSearch && clientSearch.value.trim()) {
                params.append('client_search', clientSearch.value.trim());
            }
            if (orderSearch && orderSearch.value.trim()) {
                params.append('order_search', orderSearch.value.trim());
            }
            if (returnTypeFilter && returnTypeFilter.value !== 'all') {
                params.append('return_type', returnTypeFilter.value);
            }
            
            const response = await fetch(getReturnOrdersUrl + '?' + params.toString());
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            renderReturnOrdersTable(data.return_orders || []);
            totalPages = data.total_pages || 1;
            updatePagination(data.total || 0);
            
            if (foundText) {
                foundText.textContent = `${data.total || 0} Return Orders found`;
            }
        } catch (error) {
            console.error("Error loading return orders:", error);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px; color: red;">Error loading return orders. Please refresh the page.</td></tr>';
            }
        }
    }
    
    // ======================
    // RENDER RETURN ORDERS TABLE
    // ======================
    function renderReturnOrdersTable(returnOrders) {
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (returnOrders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px;">No return orders found</td></tr>';
            return;
        }
        
        returnOrders.forEach((returnOrder, index) => {
            const tr = document.createElement('tr');
            const rowNum = (currentPage - 1) * itemsPerPage + index + 1;
            
            // Status badge
            const statusClass = getStatusClass(returnOrder.status);
            const statusBadge = `<span class="badge badge-${statusClass}">${returnOrder.status || 'Pending'}</span>`;
            
            // Truncate reason
            const reason = returnOrder.return_reason || 'N/A';
            const reasonDisplay = reason.length > 30 ? reason.substring(0, 27) + '...' : reason;
            
            tr.innerHTML = `
                <td>${rowNum}</td>
                <td><a href="#" class="order-link" data-return-id="${returnOrder.return_id}">${returnOrder.return_id || returnOrder.return_number}</a></td>
                <td><a href="#" class="order-link" data-order-id="${returnOrder.original_order_id}" target="_blank">${returnOrder.original_order_id || 'N/A'}</a></td>
                <td>${returnOrder.client_name || 'N/A'}</td>
                <td>${returnOrder.product_name || 'N/A'}</td>
                <td>${returnOrder.return_date || 'N/A'}</td>
                <td title="${reason}">${reasonDisplay}</td>
                <td>${statusBadge}</td>
                <td class="action-cell">
                    <button class="action-btn" data-return-id="${returnOrder.return_id}" title="Actions">⋮</button>
                </td>
            `;
            
            tbody.appendChild(tr);
        });
        
        // Attach event listeners
        attachReturnLinkListeners();
        attachActionMenuListeners();
    }
    
    // ======================
    // PAGINATION
    // ======================
    function updatePagination(total) {
        const paginationInfo = document.getElementById('pagination-info');
        const paginationControls = document.getElementById('pagination-controls');
        
        if (paginationInfo) {
            const start = total > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
            const end = Math.min(currentPage * itemsPerPage, total);
            paginationInfo.textContent = total > 0 ? `Showing ${start}-${end} of ${total} items` : 'No items';
        }
        
        if (!paginationControls) return;
        
        paginationControls.innerHTML = '';
        
        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn';
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadReturnOrders();
            }
        });
        paginationControls.appendChild(prevBtn);
        
        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.className = 'pagination-btn' + (i === currentPage ? ' active' : '');
            btn.textContent = i;
            btn.addEventListener('click', () => {
                currentPage = i;
                loadReturnOrders();
            });
            paginationControls.appendChild(btn);
        }
        
        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn';
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.disabled = currentPage >= totalPages;
        nextBtn.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                loadReturnOrders();
            }
        });
        paginationControls.appendChild(nextBtn);
    }
    
    // ======================
    // RETURN ORDER DETAILS MODAL
    // ======================
    async function loadReturnDetails(returnId) {
        try {
            const response = await fetch(getReturnDetailsUrl + '?return_id=' + encodeURIComponent(returnId));
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            if (data.success && data.return_order) {
                currentReturnOrder = data.return_order;
                populateReturnDetailsModal(data.return_order);
                returnOrderDetailsModal.classList.add('active');
            } else {
                showToast('Error loading return order details: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error("Error loading return order details:", error);
            showToast('Error loading return order details. Please try again.', 'error');
        }
    }
    
    function populateReturnDetailsModal(returnOrder) {
        // Return Information
        setElementText('detail-return-id', returnOrder.return_id || returnOrder.return_number || 'N/A');
        setElementText('detail-return-date', returnOrder.return_date || 'N/A');
        setElementText('detail-return-type', returnOrder.return_type || 'N/A');
        setElementText('detail-status-badge', returnOrder.status || 'N/A');
        document.getElementById('detail-status-badge').className = 'badge badge-' + getStatusClass(returnOrder.status);
        
        // Original Order Information
        const originalOrderLink = document.getElementById('detail-original-order-link');
        if (originalOrderLink && returnOrder.original_order_id) {
            originalOrderLink.textContent = returnOrder.original_order_id;
            originalOrderLink.href = baseUrl + 'admin-orders?type=direct&order_id=' + returnOrder.original_order_id;
        }
        setElementText('detail-original-order-date', returnOrder.original_order_date || 'N/A');
        setElementText('detail-original-product', returnOrder.original_product_name || 'N/A');
        setElementText('detail-original-amount', '₱' + parseFloat(returnOrder.original_amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }));
        
        // Return Details
        setElementText('detail-returned-product', returnOrder.product_name || 'N/A');
        setElementText('detail-returned-quantity', returnOrder.quantity_returned || 'N/A');
        setElementText('detail-return-reason', returnOrder.return_reason || 'N/A');
        setElementText('detail-return-description', returnOrder.return_description || 'N/A');
        
        // Return Photos
        populateReturnPhotos(returnOrder.photos || []);
        
        // Replacement Information
        const replacementRequired = document.getElementById('replacement-required-checkbox');
        if (replacementRequired) {
            replacementRequired.checked = returnOrder.replacement_required || false;
        }
        setElementText('detail-replacement-product', returnOrder.replacement_product_name || 'N/A');
        
        const replacementOrderLink = document.getElementById('detail-replacement-order-link');
        const replacementOrderNone = document.getElementById('detail-replacement-order-none');
        if (returnOrder.replacement_order_id) {
            if (replacementOrderLink) {
                replacementOrderLink.textContent = returnOrder.replacement_order_id;
                replacementOrderLink.href = baseUrl + 'admin-orders?type=direct&order_id=' + returnOrder.replacement_order_id;
                replacementOrderLink.style.display = 'inline';
            }
            if (replacementOrderNone) replacementOrderNone.style.display = 'none';
        } else {
            if (replacementOrderLink) replacementOrderLink.style.display = 'none';
            if (replacementOrderNone) replacementOrderNone.style.display = 'inline';
        }
        
        const replacementAppointmentLink = document.getElementById('detail-replacement-appointment-link');
        const replacementAppointmentNone = document.getElementById('detail-replacement-appointment-none');
        if (returnOrder.replacement_appointment_id) {
            if (replacementAppointmentLink) {
                replacementAppointmentLink.textContent = returnOrder.replacement_appointment_id;
                replacementAppointmentLink.href = baseUrl + 'admin-appointment?type=installation&appointment_id=' + returnOrder.replacement_appointment_id;
                replacementAppointmentLink.style.display = 'inline';
            }
            if (replacementAppointmentNone) replacementAppointmentNone.style.display = 'none';
        } else {
            if (replacementAppointmentLink) replacementAppointmentLink.style.display = 'none';
            if (replacementAppointmentNone) replacementAppointmentNone.style.display = 'inline';
        }
        
        // Refund Information
        const refundAmountInput = document.getElementById('refund-amount-input');
        const refundMethodSelect = document.getElementById('refund-method-select');
        if (refundAmountInput) {
            refundAmountInput.value = returnOrder.refund_amount || '';
        }
        if (refundMethodSelect) {
            refundMethodSelect.value = returnOrder.refund_method || '';
        }
        setElementText('detail-refund-status', returnOrder.refund_status || 'N/A');
        if (returnOrder.refund_status) {
            const refundStatusEl = document.getElementById('detail-refund-status');
            if (refundStatusEl) {
                refundStatusEl.className = 'badge badge-' + getRefundStatusClass(returnOrder.refund_status);
            }
        }
        setElementText('detail-refund-date', returnOrder.refund_date || 'N/A');
        
        // Admin notes
        const notesTextarea = document.getElementById('admin-notes-textarea');
        if (notesTextarea) {
            notesTextarea.value = returnOrder.admin_notes || '';
        }
        
        // Update status dropdown
        const statusSelect = document.getElementById('update-status-select');
        if (statusSelect) {
            statusSelect.value = returnOrder.status || '';
        }
        
        // Show/hide action buttons based on status
        updateActionButtonsVisibility(returnOrder.status);
    }
    
    function populateReturnPhotos(photos) {
        const gallery = document.getElementById('detail-return-photos');
        if (!gallery) return;
        
        gallery.innerHTML = '';
        
        if (photos.length === 0) {
            gallery.innerHTML = '<p style="color: #999; font-style: italic;">No photos uploaded</p>';
            return;
        }
        
        photos.forEach(photo => {
            const img = document.createElement('img');
            img.src = baseUrl + (photo.startsWith('uploads/') ? photo : 'uploads/' + photo);
            img.alt = 'Return item photo';
            img.addEventListener('click', () => {
                window.open(img.src, '_blank');
            });
            gallery.appendChild(img);
        });
    }
    
    function updateActionButtonsVisibility(status) {
        const approveBtn = document.getElementById('approve-return-btn');
        const rejectBtn = document.getElementById('reject-return-btn');
        const processRefundBtn = document.getElementById('process-refund-btn');
        const createReplacementBtn = document.getElementById('create-replacement-btn');
        
        if (status === 'Approved') {
            if (approveBtn) approveBtn.style.display = 'none';
            if (rejectBtn) rejectBtn.style.display = 'none';
        } else if (status === 'Rejected') {
            if (approveBtn) approveBtn.style.display = 'none';
            if (rejectBtn) rejectBtn.style.display = 'none';
            if (processRefundBtn) processRefundBtn.style.display = 'none';
            if (createReplacementBtn) createReplacementBtn.style.display = 'none';
        } else if (status === 'Completed') {
            if (approveBtn) approveBtn.style.display = 'none';
            if (rejectBtn) rejectBtn.style.display = 'none';
        }
    }
    
    // ======================
    // ACTION HANDLERS
    // ======================
    async function approveReturn() {
        if (!currentReturnOrder) return;
        
        const confirmed = await showConfirmationAsync('Are you sure you want to approve this return?');
        if (!confirmed) return;
        
        try {
            const response = await fetch(approveReturnUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    return_id: currentReturnOrder.return_id
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Return approved successfully', 'success');
                returnOrderDetailsModal.classList.remove('active');
                loadReturnOrders();
            } else {
                showToast('Error: ' + (data.message || 'Failed to approve return'), 'error');
            }
        } catch (error) {
            console.error('Error approving return:', error);
            showToast('Error approving return', 'error');
        }
    }
    
    async function rejectReturn() {
        if (!currentReturnOrder) return;
        
        const reason = document.getElementById('rejection-reason-textarea')?.value;
        if (!reason || reason.trim() === '') {
            showToast('Please provide a rejection reason', 'warning');
            return;
        }
        
        try {
            const response = await fetch(rejectReturnUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    return_id: currentReturnOrder.return_id,
                    reason: reason
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Return rejected successfully', 'success');
                returnOrderDetailsModal.classList.remove('active');
                document.getElementById('rejection-reason-group').style.display = 'none';
                loadReturnOrders();
            } else {
                showToast('Error: ' + (data.message || 'Failed to reject return'), 'error');
            }
        } catch (error) {
            console.error('Error rejecting return:', error);
            showToast('Error rejecting return', 'error');
        }
    }
    
    async function processRefund() {
        if (!currentReturnOrder) return;
        
        const refundAmount = document.getElementById('refund-amount-input')?.value;
        const refundMethod = document.getElementById('refund-method-select')?.value;
        
        if (!refundAmount || parseFloat(refundAmount) <= 0) {
            showToast('Please enter a valid refund amount', 'warning');
            return;
        }
        
        if (!refundMethod) {
            showToast('Please select a refund method', 'warning');
            return;
        }
        
        const confirmed = await showConfirmationAsync(`Are you sure you want to process a refund of ₱${parseFloat(refundAmount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}?`);
        if (!confirmed) return;
        
        try {
            const response = await fetch(processRefundUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    return_id: currentReturnOrder.return_id,
                    refund_amount: refundAmount,
                    refund_method: refundMethod
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Refund processed successfully', 'success');
                returnOrderDetailsModal.classList.remove('active');
                loadReturnOrders();
            } else {
                showToast('Error: ' + (data.message || 'Failed to process refund'), 'error');
            }
        } catch (error) {
            console.error('Error processing refund:', error);
            showToast('Error processing refund', 'error');
        }
    }
    
    async function createReplacementOrder() {
        if (!currentReturnOrder) return;
        
        const confirmed = await showConfirmationAsync('Are you sure you want to create a replacement order for this return?');
        if (!confirmed) return;
        
        try {
            const response = await fetch(createReplacementOrderUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    return_id: currentReturnOrder.return_id
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Replacement order created successfully', 'success');
                returnOrderDetailsModal.classList.remove('active');
                loadReturnOrders();
            } else {
                showToast('Error: ' + (data.message || 'Failed to create replacement order'), 'error');
            }
        } catch (error) {
            console.error('Error creating replacement order:', error);
            showToast('Error creating replacement order', 'error');
        }
    }
    
    async function scheduleReplacementAppointment() {
        if (!currentReturnOrder) return;
        
        const confirmed = await showConfirmationAsync('Are you sure you want to schedule a replacement installation appointment?');
        if (!confirmed) return;
        
        try {
            const response = await fetch(scheduleReplacementUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    return_id: currentReturnOrder.return_id
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Replacement appointment scheduled successfully', 'success');
                returnOrderDetailsModal.classList.remove('active');
                loadReturnOrders();
            } else {
                showToast('Error: ' + (data.message || 'Failed to schedule appointment'), 'error');
            }
        } catch (error) {
            console.error('Error scheduling appointment:', error);
            showToast('Error scheduling replacement appointment', 'error');
        }
    }
    
    async function updateReturnStatus() {
        if (!currentReturnOrder) return;
        
        const status = document.getElementById('update-status-select')?.value;
        if (!status) {
            showToast('Please select a status', 'warning');
            return;
        }
        
        try {
            const response = await fetch(updateReturnStatusUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    return_id: currentReturnOrder.return_id,
                    status: status
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Return status updated successfully', 'success');
                returnOrderDetailsModal.classList.remove('active');
                loadReturnOrders();
            } else {
                showToast('Error: ' + (data.message || 'Failed to update status'), 'error');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            showToast('Error updating return status', 'error');
        }
    }
    
    async function saveAdminNotes() {
        if (!currentReturnOrder) return;
        
        const notes = document.getElementById('admin-notes-textarea')?.value || '';
        
        try {
            const response = await fetch(baseUrl + 'AdminCon/save_return_notes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    return_id: currentReturnOrder.return_id,
                    notes: notes
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Notes saved successfully', 'success');
            } else {
                showToast('Error: ' + (data.message || 'Failed to save notes'), 'error');
            }
        } catch (error) {
            console.error('Error saving notes:', error);
            showToast('Error saving notes', 'error');
        }
    }
    
    // ======================
    // EVENT LISTENERS
    // ======================
    function attachReturnLinkListeners() {
        document.querySelectorAll('.order-link[data-return-id]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const returnId = e.target.closest('.order-link').dataset.returnId;
                loadReturnDetails(returnId);
            });
        });
    }
    
    function attachActionMenuListeners() {
        document.querySelectorAll('.action-btn[data-return-id]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const returnId = e.target.closest('.action-btn').dataset.returnId;
                showActionMenu(e.target, returnId);
            });
        });
    }
    
    function showActionMenu(button, returnId) {
        if (!actionMenu) return;
        
        const rect = button.getBoundingClientRect();
        actionMenu.style.top = rect.bottom + 'px';
        actionMenu.style.left = rect.left + 'px';
        actionMenu.classList.remove('hidden');
        
        // Update action menu links
        document.querySelectorAll('.action-view').forEach(link => {
            link.onclick = (e) => {
                e.preventDefault();
                actionMenu.classList.add('hidden');
                loadReturnDetails(returnId);
            };
        });
        
        // Close menu when clicking outside
        setTimeout(() => {
            document.addEventListener('click', function closeMenu() {
                actionMenu.classList.add('hidden');
                document.removeEventListener('click', closeMenu);
            });
        }, 100);
    }
    
    // ======================
    // UTILITY FUNCTIONS
    // ======================
    function setElementText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    }
    
    function getStatusClass(status) {
        const statusMap = {
            'Pending': 'pending',
            'Approved': 'approved',
            'Rejected': 'rejected',
            'Processing': 'processing',
            'Completed': 'completed'
        };
        return statusMap[status] || 'secondary';
    }
    
    function getRefundStatusClass(status) {
        const statusMap = {
            'Pending': 'warning',
            'Processed': 'success',
            'Completed': 'success'
        };
        return statusMap[status] || 'secondary';
    }
    
    function clearFilters() {
        if (statusFilter) statusFilter.value = 'all';
        if (dateStart) dateStart.value = '';
        if (dateEnd) dateEnd.value = '';
        if (clientSearch) clientSearch.value = '';
        if (orderSearch) orderSearch.value = '';
        if (returnTypeFilter) returnTypeFilter.value = 'all';
    }
});
