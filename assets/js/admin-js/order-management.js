document.addEventListener('DOMContentLoaded', function() {
    // ======================
    // DEBUG CHECKS
    // ======================
    console.log('Order management script loaded');
    console.log('getOrdersUrl:', typeof getOrdersUrl !== 'undefined' ? getOrdersUrl : 'UNDEFINED');
    
    // ======================
    // VARIABLES
    // ======================
    const tbody = document.querySelector('#ordersTableBody');
    const foundText = document.querySelector('.found-text');
    const orderDetailsModal = document.getElementById('orderDetailsModal');
    const closeOrderDetails = document.getElementById('closeOrderDetails');
    const actionMenu = document.getElementById('actionMenu');
    
    // Filter elements
    const filtersContainer = document.querySelector('.filters-container');
    const toggleFiltersBtn = document.getElementById('toggle-filters-btn');
    const statusFilter = document.getElementById('status-filter');
    // Ocular filter removed - redundant with unified order flow
    const dateStart = document.getElementById('date-range-start');
    const dateEnd = document.getElementById('date-range-end');
    const clientSearch = document.getElementById('client-search');
    const orderSearch = document.getElementById('order-search');
    const monthYearFilter = document.getElementById('month-year-filter');
    const applyFiltersBtn = document.getElementById('apply-filters');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    // Pagination
    let currentPage = 1;
    let totalPages = 1;
    const itemsPerPage = 10;
    
    // Current order for details modal
    let currentOrder = null;

    // Lightweight toast helper (non-blocking notifications)
    function ensureToastContainer() {
        if (!document.getElementById('global-toast-container')) {
            const container = document.createElement('div');
            container.id = 'global-toast-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '99999';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '8px';
            document.body.appendChild(container);
        }
        return document.getElementById('global-toast-container');
    }

    function showToast(message, type = 'info', duration = 4000) {
        try {
            const container = ensureToastContainer();
            const toast = document.createElement('div');
            toast.className = 'app-toast app-toast-' + type;
            toast.style.minWidth = '200px';
            toast.style.maxWidth = '360px';
            toast.style.padding = '10px 14px';
            toast.style.borderRadius = '6px';
            toast.style.boxShadow = '0 2px 8px rgba(0,0,0,0.12)';
            toast.style.color = '#fff';
            toast.style.fontSize = '14px';
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 200ms ease, transform 200ms ease';
            toast.style.transform = 'translateY(-6px)';

            if (type === 'success') toast.style.background = '#28a745';
            else if (type === 'error') toast.style.background = '#dc3545';
            else if (type === 'warning') toast.style.background = '#ffc107';
            else toast.style.background = '#17a2b8';

            toast.textContent = message;
            container.appendChild(toast);

            // animate in
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });

            // remove after duration
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-6px)';
                setTimeout(() => container.removeChild(toast), 220);
            }, duration);
        } catch (e) {
            // fallback to console if toast fails
            console.error(message);
        }
    }
    
    // ======================
    // INITIALIZATION
    // ======================
    loadOrders();
    
    // ======================
    // FILTER TOGGLE FUNCTION
    // ======================
    /**
     * Toggles the visibility of the filters container
     * Updates button appearance and icon based on state
     */
    function toggleFilters() {
        if (!toggleFiltersBtn || !filtersContainer) return;
        
        const isHidden = filtersContainer.classList.contains('hidden');
        const icon = toggleFiltersBtn.querySelector('i');
        const buttonText = toggleFiltersBtn.querySelector('span');
        
        // Toggle visibility with animation
        filtersContainer.classList.toggle('hidden');
        
        // Update button state
        toggleFiltersBtn.classList.toggle('active');
        
        // Update icon and text
        if (icon) {
            if (isHidden) {
                // Show filters - change to close icon
                icon.className = 'fas fa-times';
                if (buttonText) {
                    buttonText.textContent = 'Hide Filters';
                }
                toggleFiltersBtn.setAttribute('title', 'Hide Filters');
            } else {
                // Hide filters - change to filter icon
                icon.className = 'fas fa-filter';
                if (buttonText) {
                    buttonText.textContent = 'Filters';
                }
                toggleFiltersBtn.setAttribute('title', 'Show Filters');
            }
        }
        
        // Smooth scroll to filters if showing
        if (isHidden) {
            setTimeout(() => {
                filtersContainer.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest',
                    inline: 'nearest'
                });
            }, 150);
        }
    }
    
    // Initialize filter toggle
    if (toggleFiltersBtn && filtersContainer) {
        // Set initial state (hidden by default)
        filtersContainer.classList.add('hidden');
        toggleFiltersBtn.addEventListener('click', toggleFilters);
    }
    
    // Event listeners
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            currentPage = 1;
            loadOrders();
        });
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            clearFilters();
            currentPage = 1;
            loadOrders();
        });
    }
    
    if (closeOrderDetails) {
        closeOrderDetails.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Close button clicked');
            if (orderDetailsModal) {
                orderDetailsModal.classList.remove('active');
                orderDetailsModal.style.display = 'none';
            }
        });
    }
    
    // Close modal on overlay click
    if (orderDetailsModal) {
        orderDetailsModal.addEventListener('click', (e) => {
            if (e.target === orderDetailsModal) {
                orderDetailsModal.classList.remove('active');
                orderDetailsModal.style.display = 'none';
            }
        });
    }
    
    // ======================
    // LOAD ORDERS
    // ======================
    async function loadOrders() {
        console.log('loadOrders() called');
        console.log('tbody element:', tbody);
        console.log('getOrdersUrl:', getOrdersUrl);
        
        if (!tbody) {
            console.error('tbody element not found!');
            return;
        }
        
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">Loading orders...</td></tr>';
        
        try {
            const params = new URLSearchParams({
                status: statusFilter ? statusFilter.value : 'all',
                page: currentPage,
                limit: itemsPerPage
            });
            
            // Add optional filters
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
            if (monthYearFilter && monthYearFilter.value) {
                params.append('month_year', monthYearFilter.value);
            }
            
            const fetchUrl = getOrdersUrl + '?' + params.toString();
            console.log('Loading orders with URL:', fetchUrl);
            const response = await fetch(fetchUrl);
            console.log('Response status:', response.status);
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response error:', errorText);
                throw new Error("Network response was not ok: " + response.status);
            }
            
            const data = await response.json();
            console.log('Orders data received:', data);
            renderOrdersTable(data.orders || []);
            totalPages = data.total_pages || 1;
            updatePagination(data.total || 0);
            
            if (foundText) {
                foundText.textContent = `${data.total || 0} Orders found`;
            }
        } catch (error) {
            console.error("Error loading orders:", error);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: red;">Error loading orders: ' + error.message + '</td></tr>';
            }
        }
    }
    
    // ======================
    // RENDER ORDERS TABLE
    // ======================
    function renderOrdersTable(orders) {
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (orders.length === 0) {
            const colCount = document.querySelector('.orders-table thead tr').children.length;
            tbody.innerHTML = `<tr><td colspan="${colCount}" style="text-align: center; padding: 20px;">No orders found</td></tr>`;
            return;
        }
        
        orders.forEach((order, index) => {
            const tr = document.createElement('tr');
            const rowNum = (currentPage - 1) * itemsPerPage + index + 1;
            
            // Format address (truncate if long)
            const address = order.full_address || order.address || 'N/A';
            const addressDisplay = address.length > 30 ? address.substring(0, 27) + '...' : address;
            
            // Status badge
            const statusClass = getStatusClass(order.status_raw || order.status);
            const statusBadge = `<span class="badge badge-${statusClass}">${order.status || 'Pending Review'}</span>`;
            
            tr.innerHTML = `
                <td>${rowNum}</td>
                <td><a href="#" class="order-link" data-order-id="${order.order_id_raw || order.order_id}">${order.order_id}</a></td>
                <td>${order.customer_name || 'N/A'}</td>
                <td>${order.customer_role || ''}</td>
                <td>${order.product_name || 'N/A'}</td>
                <td title="${address}">${addressDisplay}</td>
                <td>${order.date || 'N/A'}</td>
                <td>${statusBadge}</td>
                <td class="action-cell">
                    <button class="action-btn" data-order-id="${order.order_id_raw || order.order_id}" title="Actions">⋮</button>
                </td>
            `;
            
            tbody.appendChild(tr);
        });
        
        // Attach event listeners
        attachOrderLinkListeners();
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
                loadOrders();
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
                loadOrders();
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
                loadOrders();
            }
        });
        paginationControls.appendChild(nextBtn);
    }
    
    // ======================
    // ORDER DETAILS MODAL
    // ======================
    async function loadOrderDetails(orderId) {
        if (!orderId) {
            console.error('loadOrderDetails: Order ID is missing');
            showToast('Error: Order ID is required', 'error');
            return;
        }
        
        try {
            console.log('Loading order details for:', orderId);
            const response = await fetch(getOrderDetailsUrl + '?order_id=' + encodeURIComponent(orderId));
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response not OK:', response.status, errorText);
                throw new Error("Network response was not ok: " + response.status);
            }
            
            const data = await response.json();
            console.log('Order details response:', data);
            
            if (data.success && data.order) {
                currentOrder = data.order;
                await populateOrderDetailsModal(data.order);
                orderDetailsModal.classList.add('active');
                orderDetailsModal.style.display = 'flex';
            } else {
                const errorMsg = data.message || 'Unknown error';
                console.error('Error in response:', errorMsg);
                showToast('Error loading order details: ' + errorMsg, 'error');
            }
        } catch (error) {
            console.error("Error loading order details:", error);
            console.error("Order ID was:", orderId);
            showToast('Error loading order details. Please check the console for details.', 'error');
        }
    }
    
    async function populateOrderDetailsModal(order) {
        // Order Information
        setElementText('detail-order-id', order.order_id || 'N/A');
        setElementText('detail-order-date', order.date || 'N/A');
        setElementText('detail-status-badge', order.status || 'N/A');
        setElementText('detail-preferred-ocular-date', order.preferred_installation_date || 'N/A');
        document.getElementById('detail-status-badge').className = 'badge badge-' + getStatusClass(order.status);
        
        // Customer Information
        setElementText('detail-customer-name', order.customer_name || 'N/A');
        setElementText('detail-customer-email', order.customer_email || 'N/A');
        setElementText('detail-customer-phone', order.customer_phone || 'N/A');
        setElementText('detail-customer-address', order.address || order.full_address || 'N/A');
        
        // Special Instructions - only show the customer's note
        const specialInstructionsSection = document.getElementById('special-instructions-section');
        const specialInstructionsText = document.getElementById('detail-special-instructions');
        
        let noteToShow = '';
        
        if (order.special_instructions) {
            // Try to parse as JSON (booking form stores data as JSON)
            try {
                const parsed = JSON.parse(order.special_instructions);
                // Only extract the note field
                noteToShow = parsed.note || '';
            } catch (e) {
                // Not JSON, use as plain text
                noteToShow = order.special_instructions;
            }
        }
        
        if (noteToShow && noteToShow.trim() !== '') {
            if (specialInstructionsSection) {
                specialInstructionsSection.style.display = 'block';
            }
            if (specialInstructionsText) {
                specialInstructionsText.textContent = noteToShow;
            }
        } else {
            if (specialInstructionsSection) {
                specialInstructionsSection.style.display = 'none';
            }
        }
        
        // Show/hide approval actions based on order status
        const approvalActionsSection = document.getElementById('approval-actions-section');
        const adminNotesGroup = document.getElementById('admin-notes-group');
        const disapprovalReasonGroup = document.getElementById('disapproval-reason-group');
        
        // Always hide the old approval actions section - we now use the dropdown approach
        if (approvalActionsSection) {
            approvalActionsSection.style.display = 'none';
        }
        
        // Always show the general admin actions with the dropdown
        if (adminNotesGroup) {
            adminNotesGroup.style.display = 'block';
        }
        
        // Hide disapproval reason initially - will show when Disapprove is selected
        if (disapprovalReasonGroup) {
            disapprovalReasonGroup.style.display = 'none';
        }
        
        // Pricing
        setElementText('detail-subtotal', '₱' + parseFloat(order.subtotal || order.total_quotation || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }));
        setElementText('detail-tax', '₱' + parseFloat(order.tax || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }));
        setElementText('detail-total-amount', '₱' + parseFloat(order.total_quotation || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }));
        setElementText('detail-payment-status', order.payment_status || 'N/A');
        setElementText('detail-payment-method', order.payment_method || 'N/A');
        setElementText('detail-payment-date', order.payment_date || 'N/A');
        
        // Items table
        populateItemsTable(order.items || []);
        
        // Update status dropdown - use status_raw for transitions
        const currentStatusForDropdown = order.status_raw || order.status || '';
        populateStatusDropdown(currentStatusForDropdown);
        
        // Load staff lists
        loadStaffLists();
        
        // Load appointments
        loadOrderAppointments(order.order_id_raw || order.order_id);
        
        // Populate staff assignments
        setElementText('detail-fabrication-staff', order.fabrication_staff_name || 'Unassigned');
        setElementText('detail-installation-staff', order.installation_staff_name || 'Unassigned');
        
        // Populate ocular notes/fields when server returned ocular data (site-assessed orders)
        if (order.ocular_notes !== undefined || order.ocular_staff_name !== undefined || order.ocular_date !== undefined) {
            const ocularNotesTextarea = document.getElementById('detail-ocular-notes');
            if (ocularNotesTextarea) {
                ocularNotesTextarea.value = order.ocular_notes || '';
                ocularNotesTextarea.setAttribute('readonly', 'readonly');
                ocularNotesTextarea.style.border = '1px solid #ddd';
            }

            // Set ocular status
            const ocularStatus = order.ocular_completed ? 'Completed' : 'Pending';
            setElementText('detail-ocular-status', ocularStatus);
            const statusEl = document.getElementById('detail-ocular-status');
            if (statusEl) statusEl.className = 'badge badge-' + (ocularStatus === 'Completed' ? 'success' : 'warning');

            // Set ocular date and staff
            setElementText('detail-ocular-date', order.ocular_date || 'N/A');
            setElementText('detail-ocular-staff', order.ocular_staff_name || 'N/A');

            // Ensure ocular inline select is populated (load staff lists) and visible state set
            try {
                await loadStaffLists();
                const ocularSelect = document.getElementById('select-ocular-staff');
                const ocularSpan = document.getElementById('detail-ocular-staff');
                if (ocularSelect && ocularSpan) {
                    ocularSelect.style.display = 'none';
                    ocularSpan.style.display = 'inline';
                }
            } catch (e) {
                console.error('Error initializing ocular inline select:', e);
            }
        }
        
        // Show/hide cancel button based on status
        // Cancel button: only show for approved/ongoing orders (not for unapproved orders)
        const cancelOrderBtn = document.getElementById('cancel-order-btn');
        if (cancelOrderBtn) {
            // Normalize status for checking
            const statusLower = (order.status || '').toLowerCase().trim();
            const statusRawLower = (order.status_raw || '').toLowerCase().trim();
            
            const isUnapproved = ['pending review', 'awaiting admin', 'ready to approve', 'pending booking confirmation'].includes(statusLower) ||
                                 ['pending review', 'awaiting admin', 'ready to approve', 'pending booking confirmation'].includes(statusRawLower);
            
            const isApprovedOrOngoing = !isUnapproved && 
                !['Completed', 'Cancelled', 'Disapproved'].some(s => 
                    statusLower.includes(s.toLowerCase()) || statusRawLower.includes(s.toLowerCase())
                );
            cancelOrderBtn.style.display = isApprovedOrOngoing ? 'inline-block' : 'none';
        }
        
        // Reset ocular notes edit button
        const editOcularNotesBtn = document.getElementById('edit-ocular-notes-btn');
        if (editOcularNotesBtn) {
            editOcularNotesBtn.innerHTML = '<i class="fas fa-edit" style="margin-right: 6px;"></i>Edit Notes';
            editOcularNotesBtn.className = 'btn-modern btn-secondary';
        }
        
        // Reset staff assignment buttons
        const changeFabricationBtn = document.getElementById('change-fabrication-staff');
        const changeInstallationBtn = document.getElementById('change-installation-staff');
        if (changeFabricationBtn) {
            changeFabricationBtn.innerHTML = '<i class="fas fa-edit" style="margin-right: 4px;"></i>Change';
            changeFabricationBtn.className = 'btn-modern btn-secondary';
        }
        if (changeInstallationBtn) {
            changeInstallationBtn.innerHTML = '<i class="fas fa-edit" style="margin-right: 4px;"></i>Change';
            changeInstallationBtn.className = 'btn-modern btn-secondary';
        }
        
        // Reset staff select visibility
        const ocularSelectInline = document.getElementById('select-ocular-staff');
        const fabricationSelect = document.getElementById('select-fabrication-staff');
        const installationSelect = document.getElementById('select-installation-staff');
        const ocularSpan = document.getElementById('detail-ocular-staff');
        const fabricationSpan = document.getElementById('detail-fabrication-staff');
        const installationSpan = document.getElementById('detail-installation-staff');

        if (fabricationSelect && fabricationSpan) {
            fabricationSelect.style.display = 'none';
            fabricationSpan.style.display = 'inline';
        }
        if (installationSelect && installationSpan) {
            installationSelect.style.display = 'none';
            installationSpan.style.display = 'inline';
        }
        
        // Populate payment breakdown
        populatePaymentBreakdown(order);
        
        // Show modal
        document.getElementById('orderDetailsModal').style.display = 'flex';

        // Defensive: ensure orders modal downpayment stays empty unless a real
        // payment amount exists. Some other renderers may try to display a
        // calculated estimate; clear it here to keep the Orders page locked
        // until the Ocular Visit records the downpayment.
        try {
            const dpInput = document.getElementById('order-downpayment-amount');
            const paymentData = (order && order.payment_data) ? order.payment_data : null;
            if (dpInput && (!paymentData || !paymentData.downpayment_amount)) {
                dpInput.value = '';
            }
        } catch (e) {
            console.warn('Unable to enforce orders modal downpayment blank state', e);
        }
    }
    
    function populatePaymentBreakdown(order) {
        const totalAmount = parseFloat(order.total_quotation || order.estimate_price || 0);
        
        // Calculate payment amounts
        const downpaymentAmount = (totalAmount * 0.5).toFixed(2);
        const fabricationAmount = (totalAmount * 0.4).toFixed(2);
        const installationAmount = (totalAmount * 0.1).toFixed(2);
        
        // Get payment data from order (if exists)
        const paymentData = order.payment_data || {};
        
        // Stage 1: Downpayment (50%) - Always visible but read-only
        const downpaymentAmountInput = document.getElementById('order-downpayment-amount');
        const downpaymentMethodInput = document.getElementById('order-downpayment-method');
        const downpaymentStatusInput = document.getElementById('order-downpayment-status-text');
        const downpaymentBadge = document.getElementById('order-downpayment-status-badge');
        const downpaymentReceiptContainer = document.getElementById('order-downpayment-receipt-container');
        const downpaymentReceiptLink = document.getElementById('order-downpayment-receipt-link');
        
        if (downpaymentAmountInput) {
            // Do not pre-fill the orders modal with a calculated downpayment
            // unless there is actual payment data recorded. This keeps the
            // Orders page showing a blank/placeholder value until the
            // Ocular Visit appointment handles the downpayment.
            if (paymentData && paymentData.downpayment_amount) {
                downpaymentAmountInput.value = paymentData.downpayment_amount;
            } else {
                downpaymentAmountInput.value = '';
            }
        }
        if (downpaymentMethodInput) {
            downpaymentMethodInput.value = paymentData.downpayment_method || '—';
        }
        if (downpaymentStatusInput) {
            const dpStatus = paymentData.downpayment_status || 'Pending';
            downpaymentStatusInput.value = dpStatus;
        }
        if (downpaymentBadge) {
            const dpStatus = paymentData.downpayment_status || 'Pending';
            downpaymentBadge.textContent = dpStatus;
            if (dpStatus === 'Paid') {
                downpaymentBadge.style.backgroundColor = '#28a745';
                downpaymentBadge.style.color = '#fff';
            } else {
                downpaymentBadge.style.backgroundColor = '#ffc107';
                downpaymentBadge.style.color = '#000';
            }
        }
        if (downpaymentReceiptContainer && downpaymentReceiptLink) {
            if (order.receipt_url) {
                downpaymentReceiptContainer.style.display = 'block';
                const link = downpaymentReceiptLink.querySelector('a');
                if (link) link.href = order.receipt_url;
            } else {
                downpaymentReceiptContainer.style.display = 'none';
            }
        }
        
        // Stage 2: Fabrication Payment (40%)
        const fabricationStage = document.getElementById('payment-stage-2');
        const fabricationAmountInput = document.getElementById('order-fabrication-amount');
        const fabricationMethodSelect = document.getElementById('order-fabrication-method');
        const fabricationStatusSelect = document.getElementById('order-fabrication-status');
        const fabricationBadge = document.getElementById('order-fabrication-status-badge');
        const fabricationReceiptContainer = document.getElementById('order-fabrication-receipt-container');
        const fabricationReceiptInput = document.getElementById('order-fabrication-receipt');
        const fabricationReceiptLink = document.getElementById('order-fabrication-receipt-link');
        
        const orderStatus = order.status || '';
        const isFabricationUnlocked = ['In Fabrication', 'Ready for Installation', 'Installed', 'Completed'].includes(orderStatus);
        
        if (isFabricationUnlocked) {
            // Unlock fabrication payment stage
            if (fabricationStage) {
                fabricationStage.style.border = '2px solid #02455F';
                fabricationStage.style.background = '#ffffff';
                fabricationStage.style.opacity = '1';
                fabricationStage.querySelector('h5').style.color = '#02455F';
                fabricationStage.querySelector('h5 i').className = 'fas fa-money-bill-wave';
            }
            if (fabricationAmountInput) {
                fabricationAmountInput.value = paymentData.fabrication_amount || fabricationAmount;
                fabricationAmountInput.disabled = false;
            }
            if (fabricationMethodSelect) {
                fabricationMethodSelect.value = paymentData.fabrication_method || '';
                fabricationMethodSelect.disabled = false;
            }
            if (fabricationStatusSelect) {
                fabricationStatusSelect.value = paymentData.fabrication_status || 'Pending';
                fabricationStatusSelect.disabled = false;
            }
            if (fabricationBadge) {
                const fabStatus = paymentData.fabrication_status || 'Pending';
                fabricationBadge.textContent = fabStatus;
                if (fabStatus === 'Paid') {
                    fabricationBadge.style.backgroundColor = '#28a745';
                    fabricationBadge.style.color = '#fff';
                } else {
                    fabricationBadge.style.backgroundColor = '#ffc107';
                    fabricationBadge.style.color = '#000';
                }
            }
            if (fabricationReceiptContainer && fabricationReceiptInput) {
                fabricationReceiptContainer.style.display = 'block';
                fabricationReceiptInput.disabled = false;
            }
            if (fabricationReceiptLink && paymentData.fabrication_receipt_url) {
                fabricationReceiptLink.style.display = 'block';
                const link = fabricationReceiptLink.querySelector('a');
                if (link) link.href = paymentData.fabrication_receipt_url;
            }
        } else {
            // Keep locked
            if (fabricationAmountInput) fabricationAmountInput.value = '';
            if (fabricationMethodSelect) fabricationMethodSelect.value = '';
            if (fabricationStatusSelect) fabricationStatusSelect.value = 'Pending';
            if (fabricationReceiptContainer) fabricationReceiptContainer.style.display = 'none';
        }
        
        // Stage 3: Installation Payment (10%)
        const installationStage = document.getElementById('payment-stage-3');
        const installationAmountInput = document.getElementById('order-installation-amount');
        const installationMethodSelect = document.getElementById('order-installation-method');
        const installationStatusSelect = document.getElementById('order-installation-status');
        const installationBadge = document.getElementById('order-installation-status-badge');
        const installationReceiptContainer = document.getElementById('order-installation-receipt-container');
        const installationReceiptInput = document.getElementById('order-installation-receipt');
        const installationReceiptLink = document.getElementById('order-installation-receipt-link');
        
        const isInstallationUnlocked = ['Installed', 'Completed'].includes(orderStatus);
        
        if (isInstallationUnlocked) {
            // Unlock installation payment stage
            if (installationStage) {
                installationStage.style.border = '2px solid #02455F';
                installationStage.style.background = '#ffffff';
                installationStage.style.opacity = '1';
                installationStage.querySelector('h5').style.color = '#02455F';
                installationStage.querySelector('h5 i').className = 'fas fa-money-bill-wave';
            }
            if (installationAmountInput) {
                installationAmountInput.value = paymentData.installation_amount || installationAmount;
                installationAmountInput.disabled = false;
            }
            if (installationMethodSelect) {
                installationMethodSelect.value = paymentData.installation_method || '';
                installationMethodSelect.disabled = false;
            }
            if (installationStatusSelect) {
                installationStatusSelect.value = paymentData.installation_status || 'Pending';
                installationStatusSelect.disabled = false;
            }
            if (installationBadge) {
                const instStatus = paymentData.installation_status || 'Pending';
                installationBadge.textContent = instStatus;
                if (instStatus === 'Paid') {
                    installationBadge.style.backgroundColor = '#28a745';
                    installationBadge.style.color = '#fff';
                } else {
                    installationBadge.style.backgroundColor = '#ffc107';
                    installationBadge.style.color = '#000';
                }
            }
            if (installationReceiptContainer && installationReceiptInput) {
                installationReceiptContainer.style.display = 'block';
                installationReceiptInput.disabled = false;
            }
            if (installationReceiptLink && paymentData.installation_receipt_url) {
                installationReceiptLink.style.display = 'block';
                const link = installationReceiptLink.querySelector('a');
                if (link) link.href = paymentData.installation_receipt_url;
            }
        } else {
            // Keep locked
            if (installationAmountInput) installationAmountInput.value = '';
            if (installationMethodSelect) installationMethodSelect.value = '';
            if (installationStatusSelect) installationStatusSelect.value = 'Pending';
            if (installationReceiptContainer) installationReceiptContainer.style.display = 'none';
        }
        
        // Lock inline selects/buttons according to order stage (Ocular -> Fabrication -> Installation)
        try {
            const rawStatus = order.status_raw || (currentOrder && currentOrder.status_raw) || '';
            const displayStatus = (order.status || '') || (currentOrder && currentOrder.status) || '';
            
            // Ocular staff can only be assigned when:
            // - Order is Approved/Booking Confirmed (ready for ocular visit scheduling)
            // - Order is in Ocular Pending status
            // NOT when order is just submitted (Pending Review, Awaiting Admin)
            let allowOcularAssign = /approved|booking confirmed|ocular pending|ocular visit/i.test(rawStatus) || 
                                    /approved|booking confirmed|ocular pending/i.test(displayStatus);
            
            // Fabrication staff can only be assigned when order is In Fabrication
            const allowFabricationAssign = /in fabrication/i.test(rawStatus);
            
            // Installation staff can only be assigned when order is Ready for Installation
            const allowInstallationAssign = /ready for installation/i.test(rawStatus);

            // Get DOM elements for inline selects
            const ocularSelectInline = document.getElementById('ocular-staff-inline');
            const ocularSpan = document.getElementById('ocular-staff-display');
            const fabricationSelect = document.getElementById('fabrication-staff-inline');
            const installationSelect = document.getElementById('installation-staff-inline');
            
            const ocularLockInline = document.getElementById('ocular-lock-inline');
            const fabLockInline = document.getElementById('fabrication-lock-inline');
            const instLockInline = document.getElementById('installation-lock-inline');

            if (ocularSelectInline) {
                ocularSelectInline.disabled = !allowOcularAssign;
                ocularSelectInline.title = allowOcularAssign ? '' : 'Locked until order is Approved';
                // Show inline select directly when ocular assignment is allowed
                if (allowOcularAssign) {
                    ocularSelectInline.style.display = 'inline-block';
                    if (ocularSpan) ocularSpan.style.display = 'none';
                } else {
                    ocularSelectInline.style.display = 'none';
                    if (ocularSpan) ocularSpan.style.display = 'inline';
                }
            }

            if (fabricationSelect) {
                fabricationSelect.disabled = !allowFabricationAssign;
                fabricationSelect.title = allowFabricationAssign ? '' : 'Locked until order is In Fabrication';
            }

            if (installationSelect) {
                installationSelect.disabled = !allowInstallationAssign;
                installationSelect.title = allowInstallationAssign ? '' : 'Locked until order is Ready for Installation';
            }

            // Toggle change button enabled/disabled state
            const changeOcularBtn = document.getElementById('change-ocular-staff');
            const changeFabBtn = document.getElementById('change-fabrication-staff');
            const changeInstBtn = document.getElementById('change-installation-staff');
            if (changeOcularBtn) {
                if (!allowOcularAssign) {
                    changeOcularBtn.classList.add('disabled');
                    changeOcularBtn.setAttribute('disabled', 'disabled');
                } else {
                    changeOcularBtn.classList.remove('disabled');
                    changeOcularBtn.removeAttribute('disabled');
                }
            }
            if (changeFabBtn) {
                if (!allowFabricationAssign) {
                    changeFabBtn.classList.add('disabled');
                    changeFabBtn.setAttribute('disabled', 'disabled');
                } else {
                    changeFabBtn.classList.remove('disabled');
                    changeFabBtn.removeAttribute('disabled');
                }
            }
            if (changeInstBtn) {
                if (!allowInstallationAssign) {
                    changeInstBtn.classList.add('disabled');
                    changeInstBtn.setAttribute('disabled', 'disabled');
                } else {
                    changeInstBtn.classList.remove('disabled');
                    changeInstBtn.removeAttribute('disabled');
                }
            }

            // Toggle lock icon visibility
            if (ocularLockInline) ocularLockInline.style.display = allowOcularAssign ? 'none' : 'inline-block';
            if (fabLockInline) fabLockInline.style.display = allowFabricationAssign ? 'none' : 'inline-block';
            if (instLockInline) instLockInline.style.display = allowInstallationAssign ? 'none' : 'inline-block';

        } catch (e) {
            console.error('Error applying inline select locks:', e);
        }
    }
    
    function populateItemsTable(items) {
        const tbody = document.getElementById('detail-items-tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No items found</td></tr>';
            return;
        }
        
        items.forEach(item => {
            const tr = document.createElement('tr');
            
            // Build customization details display
            const specsArray = [];
            if (item.dimension && item.dimension !== 'N/A') specsArray.push(`<strong>Dimensions:</strong> ${item.dimension}`);
            if (item.shape && item.shape !== 'N/A') specsArray.push(`<strong>Shape:</strong> ${item.shape}`);
            if (item.type && item.type !== 'N/A') specsArray.push(`<strong>Glass Type:</strong> ${item.type}`);
            if (item.thickness && item.thickness !== 'N/A') specsArray.push(`<strong>Thickness:</strong> ${item.thickness}`);
            if (item.edge_work && item.edge_work !== 'N/A') specsArray.push(`<strong>Edge Work:</strong> ${item.edge_work}`);
            if (item.frame_type && item.frame_type !== 'N/A') specsArray.push(`<strong>Frame:</strong> ${item.frame_type}`);
            if (item.engraving && item.engraving !== 'N/A' && item.engraving !== 'None') specsArray.push(`<strong>Engraving:</strong> ${item.engraving}`);
            
            const customizationHtml = specsArray.length > 0 
                ? '<div style="font-size: 12px; line-height: 1.6;">' + specsArray.join('<br>') + '</div>'
                : '<span style="color: #999;">Standard (No customization)</span>';
            
            // Design file link
            const designLink = item.design_file 
                ? '<a href="' + item.design_file + '" target="_blank" class="btn-modern btn-secondary" style="padding: 4px 10px; font-size: 12px;"><i class="fas fa-eye" style="margin-right: 4px;"></i>View Design</a>' 
                : '<span style="color: #999;">N/A</span>';
            
            tr.innerHTML = `
                <td><strong>${item.product_name || 'N/A'}</strong></td>
                <td>${customizationHtml}</td>
                <td style="text-align: center;">${item.quantity || 1}</td>
                <td>${designLink}</td>
            `;
            tbody.appendChild(tr);
        });
    }
    
    function populateStatusDropdown(currentStatus) {
        const select = document.getElementById('update-status-select');
        if (!select) return;
        
        // Normalize status - trim whitespace and handle null/undefined
        if (!currentStatus) {
            console.warn('Current status is missing for order details dropdown');
            currentStatus = '';
        } else {
            currentStatus = String(currentStatus).trim();
        }
        
        // Check if this is an unapproved order
        const statusLower = currentStatus.toLowerCase();
        const isUnapproved = ['pending review', 'awaiting admin', 'ready to approve', 'pending booking confirmation'].includes(statusLower);
        
        // Clear existing options
        select.innerHTML = '<option value="">Select Status...</option>';
        
        if (isUnapproved) {
            // For unapproved orders, only show Approve and Disapprove
            const approveOption = document.createElement('option');
            approveOption.value = 'Approved';
            approveOption.textContent = 'Approve';
            select.appendChild(approveOption);
            
            const disapproveOption = document.createElement('option');
            disapproveOption.value = 'Disapproved';
            disapproveOption.textContent = 'Disapprove';
            select.appendChild(disapproveOption);
        } else {
            // For approved/ongoing orders, show normal status transitions
            const transitions = {
                'Approved': ['Ocular Pending', 'Cancelled'],
                'Ocular Pending': ['In Fabrication', 'Cancelled'],
                'In Fabrication': ['Ready for Installation', 'Cancelled'],
                'Ready for Installation': ['Completed', 'Cancelled'],
                'Booking Confirmed': ['Ocular Pending', 'Cancelled'],
                'Quotation Available': ['Awaiting Payment', 'Cancelled'],
                'Awaiting Payment': ['In Fabrication', 'Cancelled'],
                'Disapproved': [],
                'Completed': [],
                'Cancelled': [],
                'Confirmed': ['Ocular Pending', 'Cancelled'],
                'In Progress': ['Ready for Installation', 'Completed', 'Cancelled']
            };
            
            let validStatuses = [];
            for (const [key, values] of Object.entries(transitions)) {
                if (key.toLowerCase() === statusLower) {
                    validStatuses = values;
                    break;
                }
            }
            
            // If no exact match, try partial match
            if (validStatuses.length === 0 && currentStatus) {
                for (const [key, values] of Object.entries(transitions)) {
                    if (key.toLowerCase().includes(statusLower) || statusLower.includes(key.toLowerCase())) {
                        validStatuses = values;
                        break;
                    }
                }
            }
            
            if (validStatuses.length === 0) {
                console.warn('No valid status transitions found for order details dropdown:', currentStatus);
            } else {
                validStatuses.forEach(status => {
                    const option = document.createElement('option');
                    option.value = status;
                    option.textContent = status;
                    select.appendChild(option);
                });
            }
        }
        
        // Add change event listener to show/hide disapproval reason
        select.removeEventListener('change', handleStatusChange); // Remove any existing listener
        select.addEventListener('change', handleStatusChange);
    }
    
    async function loadStaffLists() {
        try {
            // Load fabrication staff
            const fabResponse = await fetch(baseUrl + 'AdminCon/get_staff_list?role=Fabrication');
            if (fabResponse.ok) {
                const fabData = await fabResponse.json();
                if (fabData.success) {
                    populateStaffSelect('select-fabrication-staff', fabData.staff);
                }
            }
            
            // Load installation staff
            const instResponse = await fetch(baseUrl + 'AdminCon/get_staff_list?role=Installation');
            if (instResponse.ok) {
                const instData = await instResponse.json();
                if (instData.success) {
                    populateStaffSelect('select-installation-staff', instData.staff);
                }
            }
            
            // Load ocular staff
            const ocularResponse = await fetch(baseUrl + 'AdminCon/get_staff_list?role=Ocular');
            if (ocularResponse.ok) {
                const ocularData = await ocularResponse.json();
                if (ocularData.success) {
                    populateStaffSelect('select-ocular-staff', ocularData.staff);
                }
            }
        } catch (error) {
            console.error('Error loading staff lists:', error);
        }
    }
    
    async function loadOrderAppointments(orderId) {
        try {
            console.log('Loading appointments for order:', orderId);
            const response = await fetch(baseUrl + 'AdminCon/get_appointments_ajax?search=' + encodeURIComponent(orderId));
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Appointments response not OK:', response.status, errorText);
                throw new Error("Network response was not ok: " + response.status);
            }
            
            const data = await response.json();
            console.log('Appointments response:', data);
            
            const appointmentsContainer = document.getElementById('detail-appointments');
            if (!appointmentsContainer) return;
            
            if (data.success && data.appointments && data.appointments.length > 0) {
                let html = '';
                data.appointments.forEach(apt => {
                    // Use the correct field names from the API response
                    const appointmentDate = apt.appointment_date || apt.date || apt.AppointmentDate;
                    const appointmentTime = apt.appointment_time || apt.time || apt.AppointmentTime;
                    
                    const date = appointmentDate ? new Date(appointmentDate).toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    }) : 'N/A';
                    const time = appointmentTime ? new Date('2000-01-01 ' + appointmentTime).toLocaleTimeString('en-US', { 
                        hour: 'numeric', 
                        minute: '2-digit' 
                    }) : 'N/A';
                    const statusClass = apt.status === 'Complete' ? 'success' : (apt.status === 'Cancelled' ? 'danger' : 'warning');
                    
                    html += `
                        <div class="appointment-item">
                            <div class="appointment-header">
                                <span class="appointment-service">${apt.service || 'N/A'}</span>
                                <span class="badge badge-${statusClass}">${apt.status || 'N/A'}</span>
                            </div>
                            <div class="appointment-details">
                                <div><strong>Date:</strong> ${date}</div>
                                <div><strong>Time:</strong> ${time}</div>
                                ${apt.assigned_staff ? `<div><strong>Staff:</strong> ${apt.assigned_staff}</div>` : ''}
                                ${apt.notes ? `<div><strong>Notes:</strong> ${apt.notes}</div>` : ''}
                                ${apt.ocular_notes ? `<div><strong>Ocular Notes:</strong> ${apt.ocular_notes}</div>` : ''}
                            </div>
                            <a href="${baseUrl}admin-appointment?appointment_id=${apt.id || apt.appointment_id}" class="appointment-link">View Appointment</a>
                        </div>
                    `;
                });
                appointmentsContainer.innerHTML = html;
            } else {
                appointmentsContainer.innerHTML = '<p>No appointments found for this order.</p>';
            }
        } catch (error) {
            console.error('Error loading appointments:', error);
            const appointmentsContainer = document.getElementById('detail-appointments');
            if (appointmentsContainer) {
                appointmentsContainer.innerHTML = '<p>Error loading appointments.</p>';
            }
        }
    }
    
    function populateStaffSelect(selectId, staff) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        // Preserve current value to re-select after repopulating
        const currentVal = select.value;
        select.innerHTML = '<option value="">Select Staff...</option>';
        staff.forEach(s => {
            const option = document.createElement('option');
            option.value = s.id;
            option.textContent = s.name;
            select.appendChild(option);
        });

        // Re-select previous value if still present
        if (currentVal) {
            select.value = currentVal;
        }

        // Initialize TomSelect for better UX (searchable dropdown)
        try {
            if (window.TomSelect) {
                // If an instance exists, destroy to avoid double-init
                if (select.tomselect) {
                    try { select.tomselect.destroy(); } catch(e) { /* ignore */ }
                }
                // Create a new TomSelect instance bound to the select element
                new TomSelect(select, {
                    // Allow creating external names only for ocular select
                    create: (selectId === 'select-ocular-staff'),
                    allowEmptyOption: true,
                    maxItems: 1,
                    hideSelected: true,
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['text']
                });
            }
        } catch (e) {
            console.error('Error initializing TomSelect for', selectId, e);
        }
    }
    
    // ======================
    // EVENT LISTENERS
    // ======================
    function attachOrderLinkListeners() {
        document.querySelectorAll('.order-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const orderId = e.target.closest('.order-link').dataset.orderId;
                loadOrderDetails(orderId);
            });
        });
    }
    
    function attachActionMenuListeners() {
        document.querySelectorAll('.action-btn').forEach(btn => {
            // Remove any existing listeners to avoid duplicates
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                const button = e.currentTarget || e.target.closest('.action-btn');
                const orderId = button.getAttribute('data-order-id') || button.dataset.orderId;
                
                if (!orderId) {
                    console.error('Order ID not found in button:', button);
                    showToast('Error: Order ID not found', 'error');
                    return;
                }
                
                console.log('Action button clicked, orderId:', orderId);
                showActionMenu(button, orderId);
            });
        });
    }
    
    function showActionMenu(button, orderId) {
        if (!actionMenu) {
            console.error('Action menu element not found');
            return;
        }
        
        if (!orderId) {
            console.error('Order ID is missing from button:', button);
            showToast('Error: Order ID not found', 'error');
            return;
        }
        
        console.log('Showing action menu for order:', orderId);
        
        // Temporarily show menu to calculate dimensions
        actionMenu.style.position = 'fixed';
        actionMenu.style.display = 'block';
        actionMenu.classList.remove('hidden');
        
        const rect = button.getBoundingClientRect();
        const menuRect = actionMenu.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Calculate position with smart positioning
        let left = rect.left;
        let top = rect.bottom + 5; // 5px gap below button
        
        // Check if menu would go off-screen to the right
        if (left + menuRect.width > viewportWidth - 10) {
            // Position to the left of the button instead
            left = rect.right - menuRect.width;
        }
        
        // Ensure menu doesn't go off-screen to the left
        if (left < 10) {
            left = 10;
        }
        
        // Check if menu would go off-screen at the bottom
        if (top + menuRect.height > viewportHeight - 10) {
            // Position above the button instead
            top = rect.top - menuRect.height - 5; // 5px gap above button
        }
        
        // Ensure menu doesn't go off-screen at the top
        if (top < 10) {
            top = 10;
        }
        
        actionMenu.style.top = top + 'px';
        actionMenu.style.left = left + 'px';
        
        // Get order status from the row to determine available actions
        const row = button.closest('tr');
        const statusBadge = row ? row.querySelector('.badge') : null;
        const orderStatus = statusBadge ? statusBadge.textContent.trim() : '';
        
        // Show/hide cancel order based on status
        const cancelAction = actionMenu.querySelector('.action-cancel');
        if (cancelAction) {
            const canCancel = orderStatus && 
                !['Completed', 'Cancelled'].includes(orderStatus);
            cancelAction.style.display = canCancel ? 'block' : 'none';
        }
        
        // Show ocular notes for all orders (unified flow)
        const ocularNotesAction = actionMenu.querySelector('.action-ocular-notes');
        if (ocularNotesAction) {
            ocularNotesAction.style.display = 'block'; // Always show - all orders go through ocular
        }
        
        // Update action menu links - only attach to links within this action menu
        const actionView = actionMenu.querySelector('.action-view');
        if (actionView) {
            actionView.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                actionMenu.classList.add('hidden');
                actionMenu.style.display = 'none';
                if (orderId) {
                    loadOrderDetails(orderId);
                } else {
                    console.error('Order ID is missing');
                    showToast('Error: Order ID not found', 'error');
                }
            };
        }
        
        const actionUpdateStatus = actionMenu.querySelector('.action-update-status');
        if (actionUpdateStatus) {
            actionUpdateStatus.onclick = async (e) => {
                e.preventDefault();
                e.stopPropagation();
                actionMenu.classList.add('hidden');
                actionMenu.style.display = 'none';
                if (orderId) {
                    await openUpdateStatusModal(orderId);
                } else {
                    console.error('Order ID is missing');
                    showToast('Error: Order ID not found', 'error');
                }
            };
        }
        
        const actionAssignStaff = actionMenu.querySelector('.action-assign-staff');
        if (actionAssignStaff) {
            actionAssignStaff.onclick = async (e) => {
                e.preventDefault();
                e.stopPropagation();
                actionMenu.classList.add('hidden');
                actionMenu.style.display = 'none';
                if (orderId) {
                    await openAssignStaffModal(orderId);
                } else {
                    console.error('Order ID is missing');
                    showToast('Error: Order ID not found', 'error');
                }
            };
        }
        
        const actionLinkCalendar = actionMenu.querySelector('.action-link-calendar');
        if (actionLinkCalendar) {
            actionLinkCalendar.onclick = async (e) => {
                e.preventDefault();
                e.stopPropagation();
                actionMenu.classList.add('hidden');
                actionMenu.style.display = 'none';
                if (orderId) {
                    await openLinkCalendarModal(orderId);
                } else {
                    console.error('Order ID is missing');
                    showToast('Error: Order ID not found', 'error');
                }
            };
        }
        
        const actionExport = actionMenu.querySelector('.action-export');
        if (actionExport) {
            actionExport.onclick = async (e) => {
                e.preventDefault();
                e.stopPropagation();
                actionMenu.classList.add('hidden');
                actionMenu.style.display = 'none';
                if (orderId) {
                    await openExportOrderModal(orderId);
                } else {
                    console.error('Order ID is missing');
                    showToast('Error: Order ID not found', 'error');
                }
            };
        }
        
        const actionCancel = actionMenu.querySelector('.action-cancel');
        if (actionCancel) {
            actionCancel.onclick = async (e) => {
                e.preventDefault();
                e.stopPropagation();
                actionMenu.classList.add('hidden');
                actionMenu.style.display = 'none';
                if (orderId) {
                    await openCancelOrderModal(orderId);
                } else {
                    console.error('Order ID is missing');
                    showToast('Error: Order ID not found', 'error');
                }
            };
        }
        
        const actionOcularNotes = actionMenu.querySelector('.action-ocular-notes');
        if (actionOcularNotes) {
            actionOcularNotes.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                actionMenu.classList.add('hidden');
                actionMenu.style.display = 'none';
                if (orderId) {
                    loadOrderDetails(orderId);
                    // Scroll to ocular notes section after modal opens
                    setTimeout(() => {
                        const ocularSection = document.getElementById('detail-ocular-notes');
                        if (ocularSection) {
                            ocularSection.closest('.details-section').scrollIntoView({ behavior: 'smooth', block: 'center' });
                            // Enable editing
                            const editBtn = document.getElementById('edit-ocular-notes-btn');
                            if (editBtn) {
                                editBtn.click();
                            }
                        }
                    }, 300);
                } else {
                    console.error('Order ID is missing');
                    showToast('Error: Order ID not found', 'error');
                }
            };
        }
        
        // Close menu when clicking outside
        setTimeout(() => {
            document.addEventListener('click', function closeMenu(e) {
                // Don't close if clicking on the action button or menu itself
                if (actionMenu.contains(e.target) || button.contains(e.target)) {
                    return;
                }
                actionMenu.classList.add('hidden');
                actionMenu.style.display = 'none';
                document.removeEventListener('click', closeMenu);
            });
        }, 100);
    }
    
    // Update status button - Link to update status modal
    const updateStatusBtn = document.getElementById('update-status-btn');
    if (updateStatusBtn) {
        updateStatusBtn.addEventListener('click', async () => {
            if (!currentOrder) {
                showToast('No order selected', 'warning');
                return;
            }

            // Get selected status from inline select
            const statusSelect = document.getElementById('update-status-select');
            const notes = document.getElementById('admin-notes-textarea-general')?.value || '';
            if (!statusSelect || !statusSelect.value) {
                showToast('Please select a status', 'warning');
                return;
            }
            
            // If status is Disapproved, check for disapproval reason
            if (statusSelect.value === 'Disapproved') {
                const disapprovalReason = document.getElementById('disapproval-reason-general')?.value || '';
                if (!disapprovalReason.trim()) {
                    showToast('Please provide a reason for disapproval', 'warning');
                    document.getElementById('disapproval-reason-general')?.focus();
                    return;
                }
            }

            const orderId = currentOrder.order_id_raw || currentOrder.order_id || currentOrder.id;
            
            // Prepare request data
            let requestData = { order_id: orderId, status: statusSelect.value, notes: notes };
            if (statusSelect.value === 'Disapproved') {
                requestData.disapproval_reason = document.getElementById('disapproval-reason-general')?.value || '';
            }

            try {
                let url = updateOrderStatusUrl;
                if (statusSelect.value === 'Disapproved') {
                    url = disapproveOrderUrl; // Use disapprove endpoint for disapprovals
                    requestData = { order_id: orderId, disapproval_reason: requestData.disapproval_reason };
                }
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(requestData)
                });

                const text = await response.text();
                if (!response.ok) {
                    console.error('HTTP Error updating status:', response.status, response.statusText, text);
                    showToast('Error updating status: ' + response.status, 'error');
                    return;
                }

                let data;
                try { data = JSON.parse(text); } catch (e) { console.error('Parse error', e, text); showToast('Server returned invalid response', 'error'); return; }

                if (data.success) {
                    const action = statusSelect.value === 'Approved' ? 'approved' : (statusSelect.value === 'Disapproved' ? 'disapproved' : 'updated');
                    showToast(`Order ${action} successfully`, 'success');
                    // Update status badge in details modal
                    const badgeEl = document.getElementById('detail-status-badge');
                    if (badgeEl) {
                        badgeEl.textContent = statusSelect.value === 'Approved' ? 'Ocular Pending' : statusSelect.value;
                        badgeEl.className = 'badge badge-' + getStatusClass(statusSelect.value);
                    }
                    // Close modal and refresh orders list
                    orderDetailsModal.classList.remove('active');
                    loadOrders();
                } else {
                    showToast('Error: ' + (data.message || 'Failed to update order status'), 'error');
                }
            } catch (error) {
                console.error('Error updating order status:', error);
                showToast('Error updating order status', 'error');
            }
        });
    }
    
    // Handle status dropdown change to show/hide disapproval reason
    function handleStatusChange(e) {
        const selectedValue = e.target.value;
        const disapprovalReasonGroup = document.getElementById('disapproval-reason-group');
        
        if (selectedValue === 'Disapproved') {
            if (disapprovalReasonGroup) {
                disapprovalReasonGroup.style.display = 'block';
            }
        } else {
            if (disapprovalReasonGroup) {
                disapprovalReasonGroup.style.display = 'none';
            }
        }
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
            'Pending Review': 'warning',
            'Awaiting Admin': 'warning',
            'Approved': 'info',
            'Disapproved': 'danger',
            'Ocular Pending': 'warning',
            'In Fabrication': 'primary',
            'Ready for Installation': 'success',
            'Completed': 'success',
            'Cancelled': 'danger'
        };
        return statusMap[status] || 'secondary';
    }
    
    // ======================
    // APPROVE/DISAPPROVE ORDER
    // ======================
    const approveOrderBtn = document.getElementById('approve-order-btn');
    if (approveOrderBtn) {
        approveOrderBtn.addEventListener('click', async () => {
            if (!currentOrder) {
                showToast('No order selected', 'warning');
                return;
            }
            
            const confirmed = await showConfirmationAsync('Are you sure you want to approve this order?');
            if (!confirmed) {
                return;
            }
            
            const adminNotes = document.getElementById('admin-notes-textarea')?.value || '';
            const orderId = currentOrder.order_id_raw || currentOrder.order_id || currentOrder.id;
            
            try {
                const response = await fetch(approveOrderUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        order_id: orderId,
                        admin_notes: adminNotes
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    showToast('Order approved successfully!', 'success');
                    orderDetailsModal.classList.remove('active');
                    // Reload orders table to reflect status change
                    setTimeout(() => loadOrders(), 300);
                } else {
                    showToast('Error: ' + (data.message || 'Failed to approve order'), 'error');
                }
            } catch (error) {
                console.error('Error approving order:', error);
                showToast('Error approving order. Please try again.', 'error');
            }
        });
    }
    
    const disapproveOrderBtn = document.getElementById('disapprove-order-btn');
    if (disapproveOrderBtn) {
        disapproveOrderBtn.addEventListener('click', async () => {
            if (!currentOrder) {
                showToast('No order selected', 'warning');
                return;
            }
            
            const disapprovalReason = document.getElementById('disapproval-reason-textarea')?.value || '';
            
            if (!disapprovalReason.trim()) {
                showToast('Please provide a reason for disapproval', 'warning');
                document.getElementById('disapproval-reason-textarea')?.focus();
                return;
            }
            
            const confirmed = await showConfirmationAsync('Are you sure you want to disapprove this order?');
            if (!confirmed) {
                return;
            }
            
            const orderId = currentOrder.order_id_raw || currentOrder.order_id || currentOrder.id;
            
            try {
                const response = await fetch(disapproveOrderUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        order_id: orderId,
                        disapproval_reason: disapprovalReason
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    showToast('Order disapproved successfully!', 'success');
                    orderDetailsModal.classList.remove('active');
                    loadOrders();
                } else {
                    showToast('Error: ' + (data.message || 'Failed to disapprove order'), 'error');
                }
            } catch (error) {
                console.error('Error disapproving order:', error);
                showToast('Error disapproving order. Please try again.', 'error');
            }
        });
    }
    
    function clearFilters() {
        if (statusFilter) statusFilter.value = 'all';
        if (ocularFilter) ocularFilter.value = 'all';
        if (dateStart) dateStart.value = '';
        if (dateEnd) dateEnd.value = '';
        if (clientSearch) clientSearch.value = '';
        if (orderSearch) orderSearch.value = '';
        if (monthYearFilter) monthYearFilter.value = '';
    }
    
    // ======================
    // EXPORT ORDER
    // ======================
    async function exportOrder(orderId) {
        try {
            const response = await fetch(exportOrderUrl + '?order_id=' + encodeURIComponent(orderId));
            const data = await response.json();
            
            if (data.success && data.download_url) {
                // If server returns a download URL, use it
                window.location.href = data.download_url;
            } else if (data.success && data.file_path) {
                // If server returns a file path, download it
                window.location.href = baseUrl + data.file_path;
            } else {
                // Fallback: Generate PDF on client side if server doesn't support it
                showToast('Export functionality is being prepared. Please use the Export button in the order details modal.', 'info');
            }
        } catch (error) {
            console.error('Error exporting order:', error);
            showToast('Error exporting order. Please try again.', 'error');
        }
    }
    
    // ======================
    // CANCEL ORDER
    // ======================
    async function cancelOrder(orderId) {
        const confirmed = await showConfirmationAsync('Are you sure you want to cancel this order? This action cannot be undone.');
        if (!confirmed) {
            return;
        }
        
        const cancelReason = prompt('Please provide a reason for cancellation (optional):');
        
        try {
            const response = await fetch(updateOrderStatusUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    order_id: orderId,
                    status: 'Cancelled',
                    notes: cancelReason || ''
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Order cancelled successfully', 'success');
                loadOrders();
            } else {
                showToast('Error: ' + (data.message || 'Failed to cancel order'), 'error');
            }
        } catch (error) {
            console.error('Error cancelling order:', error);
            showToast('Error cancelling order. Please try again.', 'error');
        }
    }
    
    // ======================
    // STAFF ASSIGNMENT
    // ======================
    const changeFabricationStaffBtn = document.getElementById('change-fabrication-staff');
    if (changeFabricationStaffBtn) {
        changeFabricationStaffBtn.addEventListener('click', () => {
            const staffSpan = document.getElementById('detail-fabrication-staff');
            const staffSelect = document.getElementById('select-fabrication-staff');
            
            if (staffSpan && staffSelect) {
                staffSpan.style.display = 'none';
                staffSelect.style.display = 'inline-block';
                changeFabricationStaffBtn.innerHTML = '<i class="fas fa-save" style="margin-right: 4px;"></i>Save';
                changeFabricationStaffBtn.className = 'btn-modern btn-success';
                changeFabricationStaffBtn.onclick = async () => {
                    await saveStaffAssignment('fabrication', staffSelect.value);
                };
            }
        });
    }
    
    const changeInstallationStaffBtn = document.getElementById('change-installation-staff');
    if (changeInstallationStaffBtn) {
        changeInstallationStaffBtn.addEventListener('click', () => {
            // Prevent editing installation assignment unless order is Ready for Installation
            if (currentOrder && (currentOrder.status_raw || '') !== 'Ready for Installation') {
                showToast('Installation assignment is locked until the order status is "Ready for Installation".', 'warning');
                return;
            }
            const staffSpan = document.getElementById('detail-installation-staff');
            const staffSelect = document.getElementById('select-installation-staff');
            
            if (staffSpan && staffSelect) {
                staffSpan.style.display = 'none';
                staffSelect.style.display = 'inline-block';
                changeInstallationStaffBtn.innerHTML = '<i class="fas fa-save" style="margin-right: 4px;"></i>Save';
                changeInstallationStaffBtn.className = 'btn-modern btn-success';
                changeInstallationStaffBtn.onclick = async () => {
                    await saveStaffAssignment('installation', staffSelect.value);
                };
            }
        });
    }

    const changeOcularStaffBtn = document.getElementById('change-ocular-staff');
    if (changeOcularStaffBtn) {
        changeOcularStaffBtn.addEventListener('click', () => {
                // Determine whether ocular assignment should be allowed
                // Only allow after order is Approved/Booking Confirmed
                if (currentOrder) {
                    const statusRaw = (currentOrder.status_raw || '').toString();
                    const statusDisplay = (currentOrder.status || '').toString();
                    // Only allow ocular assignment for approved orders and ocular-stage orders
                    const allowOcularAssign = /approved|booking confirmed|ocular pending|ocular visit/i.test(statusRaw) || 
                                              /approved|booking confirmed|ocular pending/i.test(statusDisplay);
                    if (!allowOcularAssign) {
                        showToast('Ocular staff assignment is locked until the order is Approved.', 'warning');
                        return;
                    }
                }

            const staffSpan = document.getElementById('detail-ocular-staff');
            const staffSelect = document.getElementById('select-ocular-staff');

            if (staffSpan && staffSelect) {
                staffSpan.style.display = 'none';
                staffSelect.style.display = 'inline-block';
                changeOcularStaffBtn.innerHTML = '<i class="fas fa-save" style="margin-right: 4px;"></i>Save';
                changeOcularStaffBtn.className = 'btn-modern btn-success';
                changeOcularStaffBtn.onclick = async () => {
                    await saveStaffAssignment('ocular', staffSelect.value);
                };
            }
        });
    }
    
    async function saveStaffAssignment(staffType, staffId) {
        if (!currentOrder) {
            showToast('No order selected', 'warning');
            return;
        }
        
        const orderId = currentOrder.order_id_raw || currentOrder.order_id;
        // If staffId is non-numeric (created via TomSelect), send it as staff_name instead
        const isNumericId = staffId && !isNaN(Number(staffId));
        const payload = new URLSearchParams({ order_id: orderId, staff_type: staffType });
        if (isNumericId) payload.append('staff_id', staffId);
        else payload.append('staff_id', '');
        if (!isNumericId && staffId) payload.append('staff_name', staffId);

        try {
            const response = await fetch(assignStaffUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Staff assigned successfully', 'success');
                // Reload order details to refresh staff display
                loadOrderDetails(orderId);
            } else {
                showToast('Error: ' + (data.message || 'Failed to assign staff'), 'error');
            }
        } catch (error) {
            console.error('Error assigning staff:', error);
            showToast('Error assigning staff. Please try again.', 'error');
        }
    }
    
    // ======================
    // OCULAR NOTES EDITING
    // ======================
    const editOcularNotesBtn = document.getElementById('edit-ocular-notes-btn');
    if (editOcularNotesBtn) {
        let isEditing = false;
        let originalNotes = '';
        
        editOcularNotesBtn.addEventListener('click', () => {
            const notesTextarea = document.getElementById('detail-ocular-notes');
            if (!notesTextarea) return;
            
            if (!isEditing) {
                // Switch to edit mode
                isEditing = true;
                originalNotes = notesTextarea.value;
                notesTextarea.removeAttribute('readonly');
                notesTextarea.style.border = '1px solid #02455F';
                notesTextarea.classList.add('form-textarea-modern');
                editOcularNotesBtn.innerHTML = '<i class="fas fa-save" style="margin-right: 6px;"></i>Save Notes';
                editOcularNotesBtn.className = 'btn-modern btn-success';
            } else {
                // Save notes
                saveOcularNotes(notesTextarea.value);
            }
        });
    }
    
    async function saveOcularNotes(notes) {
        if (!currentOrder) {
            showToast('No order selected', 'warning');
            return;
        }
        
        const orderId = currentOrder.order_id_raw || currentOrder.order_id;
        
        try {
            // Use the update_ocular_notes endpoint
            const response = await fetch(baseUrl + 'AdminCon/update_ocular_notes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    order_id: orderId,
                    ocular_notes: notes
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showToast('Ocular notes saved successfully', 'success');
                // Reload order details
                loadOrderDetails(orderId);
            } else {
                showToast('Error: ' + (data.message || 'Failed to save ocular notes'), 'error');
            }
        } catch (error) {
            console.error('Error saving ocular notes:', error);
            showToast('Error saving ocular notes. Please try again.', 'error');
        }
    }
    
    // ======================
    // LINK TO CALENDAR BUTTON
    // ======================
    const linkCalendarBtn = document.getElementById('link-calendar-btn');
    if (linkCalendarBtn) {
        linkCalendarBtn.addEventListener('click', () => {
            if (!currentOrder) {
                showToast('No order selected', 'warning');
                return;
            }
            const orderId = currentOrder.order_id_raw || currentOrder.order_id;
            window.location.href = baseUrl + 'admin-calendar?order_id=' + encodeURIComponent(orderId);
        });
    }
    
    // ======================
    // EXPORT ORDER BUTTON
    // ======================
    const exportOrderBtn = document.getElementById('export-order-btn');
    if (exportOrderBtn) {
        exportOrderBtn.addEventListener('click', async () => {
            if (!currentOrder) {
                showToast('No order selected', 'warning');
                return;
            }
            const orderId = currentOrder.order_id_raw || currentOrder.order_id;
            await exportOrder(orderId);
        });
    }
    
    // ======================
    // CANCEL ORDER BUTTON
    // ======================
    const cancelOrderBtn = document.getElementById('cancel-order-btn');
    if (cancelOrderBtn) {
        cancelOrderBtn.addEventListener('click', async () => {
            if (!currentOrder) {
                showToast('No order selected', 'warning');
                return;
            }
            const orderId = currentOrder.order_id_raw || currentOrder.order_id;
            await cancelOrder(orderId);
        });
    }
    
    // ======================
    // MODAL FUNCTIONS
    // ======================
    
    // Update Status Modal
    async function openUpdateStatusModal(orderId) {
        try {
            const response = await fetch(getOrderDetailsUrl + '?order_id=' + encodeURIComponent(orderId));
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            if (data.success && data.order) {
                const order = data.order;
                const modal = document.getElementById('updateStatusModal');
                const orderIdSpan = document.getElementById('modal-status-order-id');
                const currentStatusSpan = document.getElementById('modal-status-current-status');
                const statusSelect = document.getElementById('modal-update-status-select');
                
                if (orderIdSpan) orderIdSpan.textContent = order.order_id || orderId;
                
                // Get the current status - MUST use status_raw for transitions (status is mapped/display version)
                const currentStatus = order.status_raw || order.status || '';
                
                if (currentStatusSpan) {
                    // Use the display status for the badge
                    const displayStatus = order.status || currentStatus;
                    const statusClass = getStatusClass(currentStatus);
                    currentStatusSpan.innerHTML = `<span class="badge badge-${statusClass}">${displayStatus || 'N/A'}</span>`;
                }
                
                // Populate status dropdown - use status_raw for transitions matching
                console.log('Opening update status modal for order:', orderId);
                console.log('Status (display):', order.status);
                console.log('Status (raw):', order.status_raw);
                console.log('Using status_raw for transitions:', currentStatus);
                populateModalStatusDropdown(currentStatus, 'modal-update-status-select');
                
                // Show modal
                if (modal) {
                    modal.classList.add('active');
                    modal.style.display = 'flex';
                }
            } else {
                showToast('Error loading order details', 'error');
            }
        } catch (error) {
            console.error('Error opening update status modal:', error);
            showToast('Error loading order details', 'error');
        }
    }
    
    // Close Update Status Modal
    const closeUpdateStatusModal = document.getElementById('closeUpdateStatusModal');
    const cancelUpdateStatus = document.getElementById('cancel-update-status');
    if (closeUpdateStatusModal) {
        closeUpdateStatusModal.addEventListener('click', () => {
            const modal = document.getElementById('updateStatusModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    }
    if (cancelUpdateStatus) {
        cancelUpdateStatus.addEventListener('click', () => {
            const modal = document.getElementById('updateStatusModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    }
    
    // Confirm Update Status
    const confirmUpdateStatus = document.getElementById('confirm-update-status');
    if (confirmUpdateStatus) {
        confirmUpdateStatus.addEventListener('click', async () => {
            const orderId = document.getElementById('modal-status-order-id')?.textContent;
            const statusSelect = document.getElementById('modal-update-status-select');
            const notes = document.getElementById('modal-update-status-notes')?.value || '';
            
            if (!statusSelect || !statusSelect.value) {
                showToast('Please select a status', 'warning');
                return;
            }
            
            if (!orderId) {
                showToast('Order ID not found', 'error');
                return;
            }
            
            try {
                const response = await fetch(updateOrderStatusUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        order_id: orderId,
                        status: statusSelect.value,
                        notes: notes
                    })
                });
                
                // Read response as text first (can only read once)
                const responseText = await response.text();
                
                // Check if response is ok
                if (!response.ok) {
                    console.error('HTTP Error:', response.status, response.statusText, responseText);
                    showToast('Error updating status: ' + response.status + ' ' + response.statusText, 'error');
                    return;
                }
                
                // Try to parse as JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response text:', responseText);
                    showToast('Error: Server returned invalid response. Please check the console for details.', 'error');
                    return;
                }
                
                if (data.success) {
                    showToast('Order status updated successfully', 'success');
                    const modal = document.getElementById('updateStatusModal');
                    if (modal) {
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                    }
                    loadOrders();
                } else {
                    showToast('Error: ' + (data.message || 'Failed to update status'), 'error');
                }
            } catch (error) {
                console.error('Error updating status:', error);
                console.error('Error details:', {
                    message: error.message,
                    stack: error.stack,
                    orderId: orderId,
                    status: statusSelect.value
                });
                showToast('Error updating order status: ' + (error.message || 'Unknown error occurred. Please check the console for details.'), 'error');
            }
        });
    }
    
    // Assign Staff Modal
    async function openAssignStaffModal(orderId) {
        try {
            const response = await fetch(getOrderDetailsUrl + '?order_id=' + encodeURIComponent(orderId));
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            if (data.success && data.order) {
                const order = data.order;
                const modal = document.getElementById('assignStaffModal');
                const orderIdSpan = document.getElementById('modal-staff-order-id');
                const currentFabrication = document.getElementById('current-fabrication-staff')?.querySelector('span');
                const currentInstallation = document.getElementById('current-installation-staff')?.querySelector('span');
                
                if (orderIdSpan) orderIdSpan.textContent = order.order_id || orderId;
                if (currentFabrication) {
                    const fabName = order.fabrication_staff_name || 'Unassigned';
                    currentFabrication.textContent = fabName;
                    currentFabrication.style.color = fabName === 'Unassigned' ? '#6c757d' : '#02455F';
                }
                if (currentInstallation) {
                    const instName = order.installation_staff_name || 'Unassigned';
                    currentInstallation.textContent = instName;
                    currentInstallation.style.color = instName === 'Unassigned' ? '#6c757d' : '#02455F';
                }
                // Ocular current
                const currentOcular = document.getElementById('current-ocular-staff')?.querySelector('span');
                if (currentOcular) {
                    const ocularName = order.ocular_staff_name || 'Unassigned';
                    currentOcular.textContent = ocularName;
                    currentOcular.style.color = ocularName === 'Unassigned' ? '#6c757d' : '#02455F';
                }
                
                // Load staff lists
                await loadStaffListsForModal();

                // Lock modal selects according to order stage (Ocular -> Fabrication -> Installation)
                try {
                    const rawStatus = order.status_raw || '';
                    // Ocular staff can only be assigned after order is Approved
                    const allowOcularAssign = /approved|booking confirmed|ocular pending|ocular visit/i.test(rawStatus) || 
                                              /approved|booking confirmed|ocular pending/i.test(order.status || '');
                    const allowFabricationAssign = /in fabrication/i.test(rawStatus);
                    const allowInstallationAssign = /ready for installation/i.test(rawStatus);

                    const ocularSelect = document.getElementById('modal-assign-ocular-staff');
                    const fabSelect = document.getElementById('modal-assign-fabrication-staff');
                    const instSelect = document.getElementById('modal-assign-installation-staff');
                    const modalInstLock = document.getElementById('modal-installation-lock');

                    if (ocularSelect) {
                        ocularSelect.disabled = !allowOcularAssign;
                        ocularSelect.title = allowOcularAssign ? '' : 'Locked until order is Approved';
                    }
                    if (fabSelect) {
                        fabSelect.disabled = !allowFabricationAssign;
                        fabSelect.title = allowFabricationAssign ? '' : 'Locked until order is In Fabrication';
                    }
                    if (instSelect) {
                        instSelect.disabled = !allowInstallationAssign;
                        instSelect.title = allowInstallationAssign ? '' : 'Locked until order is Ready for Installation';
                        if (modalInstLock) modalInstLock.style.display = allowInstallationAssign ? 'none' : 'inline-block';
                    }
                } catch (e) {
                    console.error('Error applying modal select locks:', e);
                }

                // Show modal
                if (modal) {
                    modal.classList.add('active');
                    modal.style.display = 'flex';
                }
            } else {
                showToast('Error loading order details', 'error');
            }
        } catch (error) {
            console.error('Error opening assign staff modal:', error);
            showToast('Error loading order details', 'error');
        }
    }
    
    async function loadStaffListsForModal() {
        try {
            // Load fabrication staff
            const fabResponse = await fetch(baseUrl + 'AdminCon/get_staff_list?role=Fabrication');
            if (fabResponse.ok) {
                const fabData = await fabResponse.json();
                if (fabData.success) {
                    const select = document.getElementById('modal-assign-fabrication-staff');
                    if (select) {
                        select.innerHTML = '<option value="">Select Fabrication Staff...</option>';
                        fabData.staff.forEach(s => {
                            const option = document.createElement('option');
                            option.value = s.id;
                            option.textContent = s.name;
                            select.appendChild(option);
                        });
                    }
                }
            }
            
            // Load installation staff
            const instResponse = await fetch(baseUrl + 'AdminCon/get_staff_list?role=Installation');
            if (instResponse.ok) {
                const instData = await instResponse.json();
                if (instData.success) {
                    const select = document.getElementById('modal-assign-installation-staff');
                    if (select) {
                        select.innerHTML = '<option value="">Select Installation Staff...</option>';
                        instData.staff.forEach(s => {
                            const option = document.createElement('option');
                            option.value = s.id;
                            option.textContent = s.name;
                            select.appendChild(option);
                        });
                    }
                }
            }
            // Load ocular staff
            const ocularResponse = await fetch(baseUrl + 'AdminCon/get_staff_list?role=Ocular');
            if (ocularResponse.ok) {
                const ocularData = await ocularResponse.json();
                if (ocularData.success) {
                    const select = document.getElementById('modal-assign-ocular-staff');
                    if (select) {
                        select.innerHTML = '<option value="">Select Ocular Staff...</option>';
                        ocularData.staff.forEach(s => {
                            const option = document.createElement('option');
                            option.value = s.id;
                            option.textContent = s.name;
                            select.appendChild(option);
                        });
                    }
                }
            }
        } catch (error) {
            console.error('Error loading staff lists:', error);
        }
    }
    
    // Close Assign Staff Modal
    const closeAssignStaffModal = document.getElementById('closeAssignStaffModal');
    const cancelAssignStaff = document.getElementById('cancel-assign-staff');
    if (closeAssignStaffModal) {
        closeAssignStaffModal.addEventListener('click', () => {
            const modal = document.getElementById('assignStaffModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    }
    if (cancelAssignStaff) {
        cancelAssignStaff.addEventListener('click', () => {
            const modal = document.getElementById('assignStaffModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    }
    
    // Confirm Assign Staff
    const confirmAssignStaff = document.getElementById('confirm-assign-staff');
    if (confirmAssignStaff) {
        confirmAssignStaff.addEventListener('click', async () => {
            const orderId = document.getElementById('modal-staff-order-id')?.textContent;
            const ocularSelect = document.getElementById('modal-assign-ocular-staff');
            const fabricationSelect = document.getElementById('modal-assign-fabrication-staff');
            const installationSelect = document.getElementById('modal-assign-installation-staff');
            
            if (!orderId) {
                showToast('Order ID not found', 'error');
                return;
            }
            
            try {
                // Assign ocular staff if selected
                if (ocularSelect && ocularSelect.value) {
                    const response = await fetch(assignStaffUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            order_id: orderId,
                            staff_type: 'ocular',
                            staff_id: ocularSelect.value
                        })
                    });
                    const data = await response.json();
                    if (!data.success) {
                        showToast('Error assigning ocular staff: ' + (data.message || 'Failed'), 'error');
                        return;
                    }
                }

                // Assign fabrication staff if selected
                if (fabricationSelect && fabricationSelect.value) {
                    const response = await fetch(assignStaffUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            order_id: orderId,
                            staff_type: 'fabrication',
                            staff_id: fabricationSelect.value
                        })
                    });
                    const data = await response.json();
                    if (!data.success) {
                        showToast('Error assigning fabrication staff: ' + (data.message || 'Failed'), 'error');
                        return;
                    }
                }
                
                // Assign installation staff if selected
                if (installationSelect && installationSelect.value) {
                    const response = await fetch(assignStaffUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            order_id: orderId,
                            staff_type: 'installation',
                            staff_id: installationSelect.value
                        })
                    });
                    const data = await response.json();
                    if (!data.success) {
                        showToast('Error assigning installation staff: ' + (data.message || 'Failed'), 'error');
                        return;
                    }
                }
                
                showToast('Staff assigned successfully', 'success');
                const modal = document.getElementById('assignStaffModal');
                if (modal) {
                    modal.classList.remove('active');
                    modal.style.display = 'none';
                }
                loadOrders();
            } catch (error) {
                console.error('Error assigning staff:', error);
                showToast('Error assigning staff. Please try again.', 'error');
            }
        });
    }
    
    // Link to Calendar Modal
    async function openLinkCalendarModal(orderId) {
        try {
            const response = await fetch(getOrderDetailsUrl + '?order_id=' + encodeURIComponent(orderId));
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            if (data.success && data.order) {
                const order = data.order;
                const modal = document.getElementById('linkCalendarModal');
                const orderIdSpan = document.getElementById('modal-calendar-order-id');
                const clientNameSpan = document.getElementById('modal-calendar-client-name');
                
                if (orderIdSpan) orderIdSpan.textContent = order.order_id || orderId;
                if (clientNameSpan) clientNameSpan.textContent = order.customer_name || 'N/A';
                
                // Show modal
                if (modal) {
                    modal.classList.add('active');
                    modal.style.display = 'flex';
                }
            } else {
                showToast('Error loading order details', 'error');
            }
        } catch (error) {
            console.error('Error opening link calendar modal:', error);
            showToast('Error loading order details', 'error');
        }
    }
    
    // Close Link Calendar Modal
    const closeLinkCalendarModal = document.getElementById('closeLinkCalendarModal');
    const cancelLinkCalendar = document.getElementById('cancel-link-calendar');
    if (closeLinkCalendarModal) {
        closeLinkCalendarModal.addEventListener('click', () => {
            const modal = document.getElementById('linkCalendarModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    }
    if (cancelLinkCalendar) {
        cancelLinkCalendar.addEventListener('click', () => {
            const modal = document.getElementById('linkCalendarModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    }
    
    // View Calendar Button
    const viewCalendarBtn = document.getElementById('view-calendar-btn');
    if (viewCalendarBtn) {
        viewCalendarBtn.addEventListener('click', () => {
            const orderId = document.getElementById('modal-calendar-order-id')?.textContent;
            if (orderId) {
                window.location.href = baseUrl + 'admin-calendar?order_id=' + encodeURIComponent(orderId);
            }
        });
    }
    
    // Create Appointment Button
    const createAppointmentBtn = document.getElementById('create-appointment-btn');
    if (createAppointmentBtn) {
        createAppointmentBtn.addEventListener('click', () => {
            const orderId = document.getElementById('modal-calendar-order-id')?.textContent;
            if (orderId) {
                window.location.href = baseUrl + 'admin-appointment?order_id=' + encodeURIComponent(orderId);
            }
        });
    }
    
    // Export Order Modal
    async function openExportOrderModal(orderId) {
        try {
            const response = await fetch(getOrderDetailsUrl + '?order_id=' + encodeURIComponent(orderId));
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            if (data.success && data.order) {
                const order = data.order;
                const modal = document.getElementById('exportOrderModal');
                const orderIdSpan = document.getElementById('modal-export-order-id');
                const clientNameSpan = document.getElementById('modal-export-client-name');
                const orderDateSpan = document.getElementById('modal-export-order-date');
                
                if (orderIdSpan) orderIdSpan.textContent = order.order_id || orderId;
                if (clientNameSpan) clientNameSpan.textContent = order.customer_name || 'N/A';
                if (orderDateSpan) orderDateSpan.textContent = order.date || 'N/A';
                
                // Show modal
                if (modal) {
                    modal.classList.add('active');
                    modal.style.display = 'flex';
                    // Attach export format button listeners
                    attachExportFormatListeners();
                }
            } else {
                showToast('Error loading order details', 'error');
            }
        } catch (error) {
            console.error('Error opening export order modal:', error);
            showToast('Error loading order details', 'error');
        }
    }
    
    // Close Export Order Modal
    const closeExportOrderModal = document.getElementById('closeExportOrderModal');
    const cancelExportOrder = document.getElementById('cancel-export-order');
    if (closeExportOrderModal) {
        closeExportOrderModal.addEventListener('click', () => {
            const modal = document.getElementById('exportOrderModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    }
    if (cancelExportOrder) {
        cancelExportOrder.addEventListener('click', () => {
            const modal = document.getElementById('exportOrderModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    }
    
    // Export Format Buttons - attach listeners when modal opens
    function attachExportFormatListeners() {
        document.querySelectorAll('.export-format-btn').forEach(btn => {
            // Remove existing listeners
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', async () => {
                const orderId = document.getElementById('modal-export-order-id')?.textContent;
                const format = newBtn.getAttribute('data-format');
                
                if (!orderId) {
                    showToast('Order ID not found', 'error');
                    return;
                }
                
                if (format === 'print') {
                    // Open print dialog
                    window.print();
                    return;
                }
                
                try {
                    const response = await fetch(exportOrderUrl + '?order_id=' + encodeURIComponent(orderId) + '&format=' + format);
                    const data = await response.json();
                    
                    if (data.success && data.download_url) {
                        // Prefer checking the URL first (HEAD) so we can fall back to index.php variant if needed
                        try {
                            const url = data.download_url;
                            let ok = false;
                            try {
                                const head = await fetch(url, { method: 'HEAD' });
                                ok = head && head.ok;
                            } catch (e) {
                                ok = false;
                            }

                            if (ok) {
                                window.open(url, '_blank');
                            } else {
                                // Try an index.php prefixed URL if the download_url is on the same base
                                try {
                                    if (url.startsWith(baseUrl)) {
                                        const rel = url.substring(baseUrl.length).replace(/^\/+/, '');
                                        const alt = baseUrl.replace(/\/$/, '') + '/index.php/' + rel;
                                        const headAlt = await fetch(alt, { method: 'HEAD' });
                                        if (headAlt && headAlt.ok) {
                                            window.open(alt, '_blank');
                                        } else {
                                            // Last resort: open original URL anyway
                                            window.open(url, '_blank');
                                        }
                                    } else {
                                        window.open(url, '_blank');
                                    }
                                } catch (e) {
                                    window.open(url, '_blank');
                                }
                            }
                        } catch (e) {
                            // If anything goes wrong, just open the URL
                            try { window.open(data.download_url, '_blank'); } catch (ex) { window.location.href = data.download_url; }
                        }
                    } else if (data.success && data.file_path) {
                        // If server returned a file path, navigate to it to trigger download
                        const fullPath = baseUrl + data.file_path;
                        try {
                            const head = await fetch(fullPath, { method: 'HEAD' });
                            if (head && head.ok) {
                                window.open(fullPath, '_blank');
                            } else {
                                // Try index.php variant
                                const rel = data.file_path.replace(/^\/+/, '');
                                const alt = baseUrl.replace(/\/$/, '') + '/index.php/' + rel;
                                try {
                                    const headAlt = await fetch(alt, { method: 'HEAD' });
                                    if (headAlt && headAlt.ok) window.open(alt, '_blank'); else window.open(fullPath, '_blank');
                                } catch (e) { window.open(fullPath, '_blank'); }
                            }
                        } catch (e) {
                            try { window.open(fullPath, '_blank'); } catch (ex) { window.location.href = fullPath; }
                        }
                    } else {
                        showToast('Export functionality is being prepared. Please try again later.', 'info');
                    }
                    
                    const modal = document.getElementById('exportOrderModal');
                    if (modal) {
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error exporting order:', error);
                    showToast('Error exporting order. Please try again.', 'error');
                }
            });
        });
    }
    
    // Cancel Order Modal
    async function openCancelOrderModal(orderId) {
        try {
            const response = await fetch(getOrderDetailsUrl + '?order_id=' + encodeURIComponent(orderId));
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            if (data.success && data.order) {
                const order = data.order;
                const modal = document.getElementById('cancelOrderModal');
                const orderIdSpan = document.getElementById('modal-cancel-order-id');
                const clientNameSpan = document.getElementById('modal-cancel-client-name');
                const currentStatusSpan = document.getElementById('modal-cancel-current-status');
                
                if (orderIdSpan) orderIdSpan.textContent = order.order_id || orderId;
                if (clientNameSpan) clientNameSpan.textContent = order.customer_name || 'N/A';
                if (currentStatusSpan) {
                    const statusClass = getStatusClass(order.status);
                    currentStatusSpan.innerHTML = `<span class="badge badge-${statusClass}">${order.status || 'N/A'}</span>`;
                }
                
                // Show modal
                if (modal) {
                    modal.classList.add('active');
                    modal.style.display = 'flex';
                }
            } else {
                showToast('Error loading order details', 'error');
            }
        } catch (error) {
            console.error('Error opening cancel order modal:', error);
            showToast('Error loading order details', 'error');
        }
    }
    
    // Close Cancel Order Modal
    const closeCancelOrderModal = document.getElementById('closeCancelOrderModal');
    const cancelCancelOrder = document.getElementById('cancel-cancel-order');
    if (closeCancelOrderModal) {
        closeCancelOrderModal.addEventListener('click', () => {
            const modal = document.getElementById('cancelOrderModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    }
    if (cancelCancelOrder) {
        cancelCancelOrder.addEventListener('click', () => {
            const modal = document.getElementById('cancelOrderModal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    }
    
    // Confirm Cancel Order
    const confirmCancelOrder = document.getElementById('confirm-cancel-order');
    if (confirmCancelOrder) {
        confirmCancelOrder.addEventListener('click', async () => {
            const orderId = document.getElementById('modal-cancel-order-id')?.textContent;
            const cancelReason = document.getElementById('modal-cancel-reason')?.value || '';
            
            if (!orderId) {
                showToast('Order ID not found', 'error');
                return;
            }
            
            const confirmed = await showConfirmationAsync('Are you sure you want to cancel this order? This action cannot be undone.');
            if (!confirmed) {
                return;
            }
            
            try {
                const response = await fetch(updateOrderStatusUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        order_id: orderId,
                        status: 'Cancelled',
                        notes: cancelReason
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    showToast('Order cancelled successfully', 'success');
                    const modal = document.getElementById('cancelOrderModal');
                    if (modal) {
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                    }
                    loadOrders();
                } else {
                    showToast('Error: ' + (data.message || 'Failed to cancel order'), 'error');
                }
            } catch (error) {
                console.error('Error cancelling order:', error);
                showToast('Error cancelling order. Please try again.', 'error');
            }
        });
    }
    
    // Close modals on overlay click
    document.querySelectorAll('.popup-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                overlay.style.display = 'none';
            }
        });
    });
    
    // Helper function to populate status dropdown in modal
    function populateModalStatusDropdown(currentStatus, selectId) {
        const select = document.getElementById(selectId);
        if (!select) {
            console.error('Select element not found:', selectId);
            return;
        }
        
        // Normalize status - trim whitespace and handle null/undefined
        if (!currentStatus) {
            console.warn('Current status is missing');
            currentStatus = '';
        } else {
            currentStatus = String(currentStatus).trim();
        }
        
        console.log('populateModalStatusDropdown called with status:', currentStatus);
        
        // Define valid transitions (must match raw database status values)
        const transitions = {
            // Direct Order statuses
            'Pending Payment': ['Paid', 'Cancelled'],
            'Paid': ['Payment Verified', 'Cancelled'],
            'Payment Verified': ['Approved', 'Cancelled'],
            'Approved': ['In Fabrication', 'Cancelled'],
            'In Fabrication': ['Scheduling', 'Ready for Installation', 'For Installation / Shipping', 'Cancelled'],
            'Scheduling': ['For Installation / Shipping', 'Cancelled'],
            'For Installation / Shipping': ['Completed', 'Cancelled'],
            'Ready for Installation': ['Completed', 'Cancelled'],
            
            // Site Assessment Order statuses
            'Pending Booking Confirmation': ['Approved', 'Booking Confirmed', 'Cancelled'],
            'Booking Confirmed': ['Quotation Available', 'In Fabrication', 'Cancelled'],
            'Quotation Available': ['Awaiting Payment', 'Cancelled'],
            'Awaiting Payment': ['In Fabrication', 'Paid', 'Cancelled'],
            
            // Legacy/Common statuses (backward compatibility)
            'Pending Review': ['Approved', 'Disapproved', 'Cancelled'],
            'Awaiting Admin': ['Approved', 'Disapproved', 'Cancelled'],
            'Ocular Pending': ['Approved', 'Cancelled'],
            'Disapproved': [], // Final state
            'Completed': [], // Final state
            'Cancelled': [], // Final state
            
            // Also handle mapped display values as fallback
            'Ready to Approve': ['Approved', 'Disapproved', 'Cancelled'], // Mapped from 'Awaiting Admin' or 'Pending Review'
            'Confirmed': ['In Fabrication', 'Cancelled'], // Mapped from 'Approved'
            'In Progress': ['Ready for Installation', 'Completed', 'Cancelled'] // Mapped from 'In Fabrication' or 'Ready for Installation'
        };
        
        // Clear existing options
        select.innerHTML = '<option value="">Select Status...</option>';
        
        // Get valid statuses for current status (case-insensitive exact match first)
        let validStatuses = [];
        const statusLower = currentStatus.toLowerCase();
        
        for (const [key, values] of Object.entries(transitions)) {
            if (key.toLowerCase() === statusLower) {
                validStatuses = values;
                console.log('Found exact match:', key);
                break;
            }
        }
        
        // If no exact match, try partial match
        if (validStatuses.length === 0 && currentStatus) {
            for (const [key, values] of Object.entries(transitions)) {
                if (key.toLowerCase().includes(statusLower) || statusLower.includes(key.toLowerCase())) {
                    validStatuses = values;
                    console.log('Found partial match:', key, 'for status:', currentStatus);
                    break;
                }
            }
        }
        
        if (validStatuses.length === 0) {
            console.warn('No valid status transitions found for:', currentStatus);
            console.warn('Available transition keys:', Object.keys(transitions));
            // Add a message option
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No status changes available';
            option.disabled = true;
            select.appendChild(option);
        } else {
            console.log('Adding', validStatuses.length, 'status options to dropdown');
            validStatuses.forEach(status => {
                const option = document.createElement('option');
                option.value = status;
                option.textContent = status;
                select.appendChild(option);
            });
        }
        
        console.log('Final dropdown options count:', select.options.length - 1, 'valid statuses');
    }
});
