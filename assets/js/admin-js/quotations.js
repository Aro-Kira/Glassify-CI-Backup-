document.addEventListener('DOMContentLoaded', function() {
    // ======================
    // VARIABLES
    // ======================
    const tbody = document.querySelector('#quotationsTableBody');
    const foundText = document.querySelector('.found-text');
    const quotationDetailsModal = document.getElementById('quotationDetailsModal');
    const closeQuotationDetails = document.getElementById('closeQuotationDetails');
    const actionMenu = document.getElementById('actionMenu');
    
    // Filter elements
    const statusFilter = document.getElementById('status-filter');
    const dateStart = document.getElementById('date-range-start');
    const dateEnd = document.getElementById('date-range-end');
    const clientSearch = document.getElementById('client-search');
    const salesRepFilter = document.getElementById('sales-rep-filter');
    const amountMin = document.getElementById('amount-min');
    const amountMax = document.getElementById('amount-max');
    const applyFiltersBtn = document.getElementById('apply-filters');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    // Pagination
    let currentPage = 1;
    let totalPages = 1;
    const itemsPerPage = 10;
    
    // Current quotation for details modal
    let currentQuotation = null;
    
    // ======================
    // INITIALIZATION
    // ======================
    loadQuotations();
    loadSalesReps();
    
    // Event listeners
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            currentPage = 1;
            loadQuotations();
        });
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            clearFilters();
            currentPage = 1;
            loadQuotations();
        });
    }
    
    if (closeQuotationDetails) {
        closeQuotationDetails.addEventListener('click', () => {
            quotationDetailsModal.classList.remove('active');
        });
    }
    
    // Close modal on overlay click
    if (quotationDetailsModal) {
        quotationDetailsModal.addEventListener('click', (e) => {
            if (e.target === quotationDetailsModal) {
                quotationDetailsModal.classList.remove('active');
            }
        });
    }
    
    // Action buttons
    const approveBtn = document.getElementById('approve-quotation-btn');
    const rejectBtn = document.getElementById('reject-quotation-btn');
    const convertBtn = document.getElementById('convert-to-order-btn');
    const confirmRejectBtn = document.getElementById('confirm-reject-btn');
    const saveNotesBtn = document.getElementById('save-notes-btn');
    
    if (approveBtn) {
        approveBtn.addEventListener('click', () => approveQuotation());
    }
    
    if (rejectBtn) {
        rejectBtn.addEventListener('click', () => {
            document.getElementById('rejection-reason-group').style.display = 'block';
        });
    }
    
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', () => rejectQuotation());
    }
    
    if (convertBtn) {
        convertBtn.addEventListener('click', () => convertToOrder());
    }
    
    if (saveNotesBtn) {
        saveNotesBtn.addEventListener('click', () => saveAdminNotes());
    }
    
    // ======================
    // LOAD QUOTATIONS
    // ======================
    async function loadQuotations() {
        if (!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px;">Loading quotations...</td></tr>';
        
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
            if (salesRepFilter && salesRepFilter.value !== 'all') {
                params.append('sales_rep', salesRepFilter.value);
            }
            if (amountMin && amountMin.value) {
                params.append('amount_min', amountMin.value);
            }
            if (amountMax && amountMax.value) {
                params.append('amount_max', amountMax.value);
            }
            
            const response = await fetch(getQuotationsUrl + '?' + params.toString());
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            if (data.success === false && data.message) {
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px; color: #856404;">' + (data.message || 'Error loading quotations.') + '</td></tr>';
                }
                if (foundText) foundText.textContent = '0 Quotations found';
                return;
            }
            renderQuotationsTable(data.quotations || []);
            totalPages = data.total_pages || 1;
            updatePagination(data.total || 0);
            
            if (foundText) {
                foundText.textContent = `${data.total || 0} Quotations found`;
            }
        } catch (error) {
            console.error("Error loading quotations:", error);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px; color: red;">Error loading quotations. Please refresh the page.</td></tr>';
            }
        }
    }
    
    // ======================
    // LOAD SALES REPS
    // ======================
    async function loadSalesReps() {
        try {
            const response = await fetch(getSalesRepsUrl);
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            if (data.success === false && data.message) {
                console.warn('Sales reps:', data.message);
            }
            if (data.success && salesRepFilter) {
                salesRepFilter.innerHTML = '<option value="all">All</option>';
                data.sales_reps.forEach(rep => {
                    const option = document.createElement('option');
                    option.value = rep.user_id;
                    option.textContent = `${rep.first_name} ${rep.last_name}`;
                    salesRepFilter.appendChild(option);
                });
            }
        } catch (error) {
            console.error("Error loading sales reps:", error);
        }
    }
    
    // ======================
    // RENDER QUOTATIONS TABLE
    // ======================
    function renderQuotationsTable(quotations) {
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (quotations.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px;">No quotations found</td></tr>';
            return;
        }
        
        quotations.forEach((quotation, index) => {
            const tr = document.createElement('tr');
            const rowNum = (currentPage - 1) * itemsPerPage + index + 1;
            
            // Status badge
            const statusClass = getStatusClass(quotation.status);
            const statusBadge = `<span class="badge badge-${statusClass}">${quotation.status || 'Pending'}</span>`;
            
            tr.innerHTML = `
                <td>${rowNum}</td>
                <td><a href="#" class="order-link" data-quotation-id="${quotation.quotation_id}">${quotation.quotation_id || quotation.quotation_number}</a></td>
                <td>${quotation.client_name || 'N/A'}</td>
                <td>${quotation.sales_rep_name || 'N/A'}</td>
                <td>${quotation.product_name || 'N/A'}</td>
                <td>₱${parseFloat(quotation.total_amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td>${quotation.created_date || 'N/A'}</td>
                <td>${statusBadge}</td>
                <td class="action-cell">
                    <button class="action-btn" data-quotation-id="${quotation.quotation_id}" title="Actions">⋮</button>
                </td>
            `;
            
            tbody.appendChild(tr);
        });
        
        // Attach event listeners
        attachQuotationLinkListeners();
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
                loadQuotations();
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
                loadQuotations();
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
                loadQuotations();
            }
        });
        paginationControls.appendChild(nextBtn);
    }
    
    // ======================
    // QUOTATION DETAILS MODAL
    // ======================
    async function loadQuotationDetails(quotationId) {
        try {
            const response = await fetch(getQuotationDetailsUrl + '?quotation_id=' + encodeURIComponent(quotationId));
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            if (data.success && data.quotation) {
                currentQuotation = data.quotation;
                populateQuotationDetailsModal(data.quotation);
                quotationDetailsModal.classList.add('active');
            } else {
                showToast('Error loading quotation details: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error("Error loading quotation details:", error);
            showToast('Error loading quotation details. Please try again.', 'error');
        }
    }
    
    function populateQuotationDetailsModal(quotation) {
        // Quotation Information
        setElementText('detail-quotation-id', quotation.quotation_id || quotation.quotation_number || 'N/A');
        setElementText('detail-created-date', quotation.created_date || 'N/A');
        setElementText('detail-expiry-date', quotation.expiry_date || 'N/A');
        setElementText('detail-sales-rep', quotation.sales_rep_name || 'N/A');
        setElementText('detail-status-badge', quotation.status || 'N/A');
        document.getElementById('detail-status-badge').className = 'badge badge-' + getStatusClass(quotation.status);
        
        // Customer Information
        setElementText('detail-customer-name', quotation.customer_name || 'N/A');
        setElementText('detail-customer-email', quotation.customer_email || 'N/A');
        setElementText('detail-customer-phone', quotation.customer_phone || 'N/A');
        setElementText('detail-customer-address', quotation.customer_address || 'N/A');
        
        // Items table
        populateItemsTable(quotation.items || []);
        
        // Total amount
        setElementText('detail-total-amount', '₱' + parseFloat(quotation.total_amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }));
        
        // Admin notes
        const notesTextarea = document.getElementById('admin-notes-textarea');
        if (notesTextarea) {
            notesTextarea.value = quotation.admin_notes || '';
        }
        
        // Linked order info
        const linkedOrderInfo = document.getElementById('linked-order-info');
        const linkedOrderLink = document.getElementById('linked-order-link');
        if (quotation.linked_order_id) {
            if (linkedOrderInfo) linkedOrderInfo.style.display = 'block';
            if (linkedOrderLink) {
                linkedOrderLink.textContent = quotation.linked_order_id;
                linkedOrderLink.href = baseUrl + 'admin-orders?type=direct&order_id=' + quotation.linked_order_id;
            }
        } else {
            if (linkedOrderInfo) linkedOrderInfo.style.display = 'none';
        }
        
        // Show/hide action buttons based on status
        updateActionButtonsVisibility(quotation.status);
    }
    
    function populateItemsTable(items) {
        const tbody = document.getElementById('detail-items-tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No items</td></tr>';
            return;
        }
        
        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.product_name || 'N/A'}</td>
                <td>${item.specifications || 'N/A'}</td>
                <td>${item.quantity || 1}</td>
                <td>₱${parseFloat(item.unit_price || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td>₱${parseFloat(item.subtotal || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
            `;
            tbody.appendChild(tr);
        });
    }
    
    function updateActionButtonsVisibility(status) {
        const approveBtn = document.getElementById('approve-quotation-btn');
        const rejectBtn = document.getElementById('reject-quotation-btn');
        const convertBtn = document.getElementById('convert-to-order-btn');
        
        if (status === 'Approved') {
            if (approveBtn) approveBtn.style.display = 'none';
            if (rejectBtn) rejectBtn.style.display = 'none';
        } else if (status === 'Rejected') {
            if (approveBtn) approveBtn.style.display = 'none';
            if (rejectBtn) rejectBtn.style.display = 'none';
            if (convertBtn) convertBtn.style.display = 'none';
        } else if (status === 'Converted to Order') {
            if (approveBtn) approveBtn.style.display = 'none';
            if (rejectBtn) rejectBtn.style.display = 'none';
            if (convertBtn) convertBtn.style.display = 'none';
        }
    }
    
    // ======================
    // ACTION HANDLERS
    // ======================
    async function approveQuotation() {
        if (!currentQuotation) return;
        
        const confirmed = await showConfirmationAsync('Are you sure you want to approve this quotation?');
        if (!confirmed) return;
        
        try {
            const response = await fetch(approveQuotationUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    quotation_id: currentQuotation.quotation_id
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Quotation approved successfully', 'success');
                quotationDetailsModal.classList.remove('active');
                loadQuotations();
            } else {
                showToast('Error: ' + (data.message || 'Failed to approve quotation'), 'error');
            }
        } catch (error) {
            console.error('Error approving quotation:', error);
            showToast('Error approving quotation', 'error');
        }
    }
    
    async function rejectQuotation() {
        if (!currentQuotation) return;
        
        const reason = document.getElementById('rejection-reason-textarea')?.value;
        if (!reason || reason.trim() === '') {
            showToast('Please provide a rejection reason', 'warning');
            return;
        }
        
        try {
            const response = await fetch(rejectQuotationUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    quotation_id: currentQuotation.quotation_id,
                    reason: reason
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Quotation rejected successfully', 'success');
                quotationDetailsModal.classList.remove('active');
                document.getElementById('rejection-reason-group').style.display = 'none';
                loadQuotations();
            } else {
                showToast('Error: ' + (data.message || 'Failed to reject quotation'), 'error');
            }
        } catch (error) {
            console.error('Error rejecting quotation:', error);
            showToast('Error rejecting quotation', 'error');
        }
    }
    
    async function convertToOrder() {
        if (!currentQuotation) return;
        
        const confirmed = await showConfirmationAsync('Are you sure you want to convert this quotation to an order?');
        if (!confirmed) return;
        
        try {
            const response = await fetch(convertToOrderUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    quotation_id: currentQuotation.quotation_id
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Quotation converted to order successfully', 'success');
                quotationDetailsModal.classList.remove('active');
                loadQuotations();
            } else {
                showToast('Error: ' + (data.message || 'Failed to convert quotation'), 'error');
            }
        } catch (error) {
            console.error('Error converting quotation:', error);
            showToast('Error converting quotation to order', 'error');
        }
    }
    
    async function saveAdminNotes() {
        if (!currentQuotation) return;
        
        const notes = document.getElementById('admin-notes-textarea')?.value || '';
        
        try {
            const response = await fetch(baseUrl + 'AdminCon/save_quotation_notes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    quotation_id: currentQuotation.quotation_id,
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
    function attachQuotationLinkListeners() {
        document.querySelectorAll('.order-link[data-quotation-id]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const quotationId = e.target.closest('.order-link').dataset.quotationId;
                loadQuotationDetails(quotationId);
            });
        });
    }
    
    function attachActionMenuListeners() {
        document.querySelectorAll('.action-btn[data-quotation-id]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const quotationId = e.target.closest('.action-btn').dataset.quotationId;
                showActionMenu(e.target, quotationId);
            });
        });
    }
    
    function showActionMenu(button, quotationId) {
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
                loadQuotationDetails(quotationId);
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
            'Converted to Order': 'converted'
        };
        return statusMap[status] || 'secondary';
    }
    
    function clearFilters() {
        if (statusFilter) statusFilter.value = 'all';
        if (dateStart) dateStart.value = '';
        if (dateEnd) dateEnd.value = '';
        if (clientSearch) clientSearch.value = '';
        if (salesRepFilter) salesRepFilter.value = 'all';
        if (amountMin) amountMin.value = '';
        if (amountMax) amountMax.value = '';
    }
});
