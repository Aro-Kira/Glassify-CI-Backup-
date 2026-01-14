document.addEventListener('DOMContentLoaded', function() {
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
    const ocularFilter = document.getElementById('ocular-filter');
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
        closeOrderDetails.addEventListener('click', () => {
            orderDetailsModal.classList.remove('active');
        });
    }
    
    // Close modal on overlay click
    if (orderDetailsModal) {
        orderDetailsModal.addEventListener('click', (e) => {
            if (e.target === orderDetailsModal) {
                orderDetailsModal.classList.remove('active');
            }
        });
    }
    
    // ======================
    // LOAD ORDERS
    // ======================
    async function loadOrders() {
        if (!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 20px;">Loading orders...</td></tr>';
        
        try {
            const params = new URLSearchParams({
                order_type: orderType || 'direct',
                status: statusFilter ? statusFilter.value : 'all',
                page: currentPage,
                limit: itemsPerPage
            });
            
            // Add optional filters
            if (ocularFilter && ocularFilter.value !== 'all') {
                params.append('ocular_status', ocularFilter.value);
            }
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
            
            const response = await fetch(getOrdersUrl + '?' + params.toString());
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            renderOrdersTable(data.orders || []);
            totalPages = data.total_pages || 1;
            updatePagination(data.total || 0);
            
            if (foundText) {
                foundText.textContent = `${data.total || 0} Orders found`;
            }
        } catch (error) {
            console.error("Error loading orders:", error);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 20px; color: red;">Error loading orders. Please refresh the page.</td></tr>';
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
            
            // Ocular status (for site-assessed orders)
            let ocularStatusCell = '';
            if (orderType === 'site-assessed') {
                const ocularStatus = order.ocular_status || 'Pending';
                const ocularClass = ocularStatus === 'Completed' ? 'success' : 'warning';
                ocularStatusCell = `<td><span class="badge badge-${ocularClass}">${ocularStatus}</span></td>`;
            }
            
            tr.innerHTML = `
                <td>${rowNum}</td>
                <td><a href="#" class="order-link" data-order-id="${order.order_id_raw || order.order_id}">${order.order_id}</a></td>
                <td>${order.customer_name || 'N/A'}</td>
                <td>${order.product_name || 'N/A'}</td>
                <td title="${address}">${addressDisplay}</td>
                <td>${order.date || 'N/A'}</td>
                ${ocularStatusCell}
                <td>₱${parseFloat(order.price || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
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
            alert('Error: Order ID is required');
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
                populateOrderDetailsModal(data.order);
                orderDetailsModal.classList.add('active');
            } else {
                const errorMsg = data.message || 'Unknown error';
                console.error('Error in response:', errorMsg);
                alert('Error loading order details: ' + errorMsg);
            }
        } catch (error) {
            console.error("Error loading order details:", error);
            console.error("Order ID was:", orderId);
            alert('Error loading order details. Please check the console for details.');
        }
    }
    
    function populateOrderDetailsModal(order) {
        // Order Information
        setElementText('detail-order-id', order.order_id || 'N/A');
        setElementText('detail-order-date', order.date || 'N/A');
        setElementText('detail-status-badge', order.status || 'N/A');
        document.getElementById('detail-status-badge').className = 'badge badge-' + getStatusClass(order.status);
        
        // Customer Information
        setElementText('detail-customer-name', order.customer_name || 'N/A');
        setElementText('detail-customer-email', order.customer_email || 'N/A');
        setElementText('detail-customer-phone', order.customer_phone || 'N/A');
        setElementText('detail-customer-address', order.address || order.full_address || 'N/A');
        
        // Special Instructions
        if (order.special_instructions) {
            const specialInstructionsSection = document.getElementById('special-instructions-section');
            const specialInstructionsText = document.getElementById('detail-special-instructions');
            const preferredDateText = document.getElementById('detail-preferred-installation-date');
            
            if (specialInstructionsSection) {
                specialInstructionsSection.style.display = 'block';
            }
            if (specialInstructionsText) {
                specialInstructionsText.textContent = order.special_instructions || 'N/A';
            }
            if (preferredDateText) {
                preferredDateText.textContent = order.preferred_installation_date || 'N/A';
            }
        } else {
            const specialInstructionsSection = document.getElementById('special-instructions-section');
            if (specialInstructionsSection) {
                specialInstructionsSection.style.display = 'none';
            }
        }
        
        // Show/hide approval actions based on order status
        const approvalActionsSection = document.getElementById('approval-actions-section');
        const adminNotesGroup = document.getElementById('admin-notes-group');
        
        if (order.status === 'Awaiting Admin') {
            if (approvalActionsSection) {
                approvalActionsSection.style.display = 'block';
            }
            if (adminNotesGroup) {
                adminNotesGroup.style.display = 'none';
            }
        } else {
            if (approvalActionsSection) {
                approvalActionsSection.style.display = 'none';
            }
            if (adminNotesGroup) {
                adminNotesGroup.style.display = 'block';
            }
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
        
        // Populate ocular notes (for site-assessed orders)
        if (orderType === 'site-assessed') {
            const ocularNotesTextarea = document.getElementById('detail-ocular-notes');
            if (ocularNotesTextarea) {
                ocularNotesTextarea.value = order.ocular_notes || '';
                ocularNotesTextarea.setAttribute('readonly', 'readonly');
                ocularNotesTextarea.style.border = '1px solid #ddd';
            }
            
            // Set ocular status
            const ocularStatus = order.ocular_completed ? 'Completed' : 'Pending';
            setElementText('detail-ocular-status', ocularStatus);
            document.getElementById('detail-ocular-status').className = 'badge badge-' + (ocularStatus === 'Completed' ? 'success' : 'warning');
            
            // Set ocular date and staff
            setElementText('detail-ocular-date', order.ocular_date || 'N/A');
            setElementText('detail-ocular-staff', order.ocular_staff_name || 'N/A');
        }
        
        // Show/hide cancel button based on status
        const cancelOrderBtn = document.getElementById('cancel-order-btn');
        if (cancelOrderBtn) {
            const canCancel = order.status && 
                !['Completed', 'Cancelled'].includes(order.status);
            cancelOrderBtn.style.display = canCancel ? 'inline-block' : 'none';
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
        const fabricationSelect = document.getElementById('select-fabrication-staff');
        const installationSelect = document.getElementById('select-installation-staff');
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
    }
    
    function populateItemsTable(items) {
        const tbody = document.getElementById('detail-items-tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No items found</td></tr>';
            return;
        }
        
        items.forEach(item => {
            const tr = document.createElement('tr');
            const subtotal = (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)).toFixed(2);
            const specs = [
                item.shape || 'N/A',
                item.dimension || 'N/A',
                item.type || 'N/A',
                item.thickness || 'N/A',
                item.edge_work || 'N/A',
                item.frame_type || 'N/A',
                item.engraving || 'N/A'
            ].filter(s => s !== 'N/A').join(', ');
            
            tr.innerHTML = `
                <td>${item.product_name || 'N/A'}</td>
                <td>${item.quantity || 0}</td>
                <td>₱${parseFloat(item.unit_price || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td>₱${parseFloat(subtotal).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td>${specs || 'N/A'}</td>
                <td>${item.design_file ? '<a href="' + item.design_file + '" target="_blank">View Design</a>' : 'N/A'}</td>
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
        
        // Define valid transitions (must match raw database status values)
        const transitions = {
            'Pending Review': ['Approved', 'Cancelled'],
            'Awaiting Admin': ['Approved', 'Disapproved', 'Cancelled'],
            'Approved': ['In Fabrication', 'Cancelled'],
            'Ocular Pending': ['Approved', 'Cancelled'],
            'In Fabrication': ['Ready for Installation', 'Cancelled'],
            'Ready for Installation': ['Completed', 'Cancelled'],
            'Completed': [], // Final state
            'Cancelled': [], // Final state
            'Disapproved': [], // Final state
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
        
        select.innerHTML = '<option value="">Select Staff...</option>';
        staff.forEach(s => {
            const option = document.createElement('option');
            option.value = s.id;
            option.textContent = s.name;
            select.appendChild(option);
        });
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
                    alert('Error: Order ID not found');
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
            alert('Error: Order ID not found');
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
        
        // Show/hide ocular notes for site-assessed orders
        const ocularNotesAction = actionMenu.querySelector('.action-ocular-notes');
        if (ocularNotesAction) {
            ocularNotesAction.style.display = (orderType === 'site-assessed') ? 'block' : 'none';
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
                    alert('Error: Order ID not found');
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
                    alert('Error: Order ID not found');
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
                    alert('Error: Order ID not found');
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
                    alert('Error: Order ID not found');
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
                    alert('Error: Order ID not found');
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
                    alert('Error: Order ID not found');
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
                    alert('Error: Order ID not found');
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
                alert('No order selected');
                return;
            }
            
            // Get the order ID
            const orderId = currentOrder.order_id_raw || currentOrder.order_id;
            
            // Close the order details modal
            orderDetailsModal.classList.remove('active');
            orderDetailsModal.style.display = 'none';
            
            // Open the update status modal
            await openUpdateStatusModal(orderId);
        });
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
                alert('No order selected');
                return;
            }
            
            if (!confirm('Are you sure you want to approve this order?')) {
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
                    alert('Order approved successfully!');
                    orderDetailsModal.classList.remove('active');
                    loadOrders();
                } else {
                    alert('Error: ' + (data.message || 'Failed to approve order'));
                }
            } catch (error) {
                console.error('Error approving order:', error);
                alert('Error approving order. Please try again.');
            }
        });
    }
    
    const disapproveOrderBtn = document.getElementById('disapprove-order-btn');
    if (disapproveOrderBtn) {
        disapproveOrderBtn.addEventListener('click', async () => {
            if (!currentOrder) {
                alert('No order selected');
                return;
            }
            
            const disapprovalReason = document.getElementById('disapproval-reason-textarea')?.value || '';
            
            if (!disapprovalReason.trim()) {
                alert('Please provide a reason for disapproval');
                document.getElementById('disapproval-reason-textarea')?.focus();
                return;
            }
            
            if (!confirm('Are you sure you want to disapprove this order?')) {
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
                    alert('Order disapproved successfully!');
                    orderDetailsModal.classList.remove('active');
                    loadOrders();
                } else {
                    alert('Error: ' + (data.message || 'Failed to disapprove order'));
                }
            } catch (error) {
                console.error('Error disapproving order:', error);
                alert('Error disapproving order. Please try again.');
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
                alert('Export functionality is being prepared. Please use the Export button in the order details modal.');
            }
        } catch (error) {
            console.error('Error exporting order:', error);
            alert('Error exporting order. Please try again.');
        }
    }
    
    // ======================
    // CANCEL ORDER
    // ======================
    async function cancelOrder(orderId) {
        if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
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
                alert('Order cancelled successfully');
                loadOrders();
            } else {
                alert('Error: ' + (data.message || 'Failed to cancel order'));
            }
        } catch (error) {
            console.error('Error cancelling order:', error);
            alert('Error cancelling order. Please try again.');
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
    
    async function saveStaffAssignment(staffType, staffId) {
        if (!currentOrder) {
            alert('No order selected');
            return;
        }
        
        const orderId = currentOrder.order_id_raw || currentOrder.order_id;
        
        try {
            const response = await fetch(assignStaffUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    order_id: orderId,
                    staff_type: staffType,
                    staff_id: staffId || ''
                })
            });
            
            const data = await response.json();
            if (data.success) {
                alert('Staff assigned successfully');
                // Reload order details to refresh staff display
                loadOrderDetails(orderId);
            } else {
                alert('Error: ' + (data.message || 'Failed to assign staff'));
            }
        } catch (error) {
            console.error('Error assigning staff:', error);
            alert('Error assigning staff. Please try again.');
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
            alert('No order selected');
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
                alert('Ocular notes saved successfully');
                // Reload order details
                loadOrderDetails(orderId);
            } else {
                alert('Error: ' + (data.message || 'Failed to save ocular notes'));
            }
        } catch (error) {
            console.error('Error saving ocular notes:', error);
            alert('Error saving ocular notes. Please try again.');
        }
    }
    
    // ======================
    // LINK TO CALENDAR BUTTON
    // ======================
    const linkCalendarBtn = document.getElementById('link-calendar-btn');
    if (linkCalendarBtn) {
        linkCalendarBtn.addEventListener('click', () => {
            if (!currentOrder) {
                alert('No order selected');
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
                alert('No order selected');
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
                alert('No order selected');
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
                alert('Error loading order details');
            }
        } catch (error) {
            console.error('Error opening update status modal:', error);
            alert('Error loading order details');
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
                alert('Please select a status');
                return;
            }
            
            if (!orderId) {
                alert('Order ID not found');
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
                
                const data = await response.json();
                if (data.success) {
                    alert('Order status updated successfully');
                    const modal = document.getElementById('updateStatusModal');
                    if (modal) {
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                    }
                    loadOrders();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update status'));
                }
            } catch (error) {
                console.error('Error updating status:', error);
                alert('Error updating order status');
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
                
                // Load staff lists
                await loadStaffListsForModal();
                
                // Show modal
                if (modal) {
                    modal.classList.add('active');
                    modal.style.display = 'flex';
                }
            } else {
                alert('Error loading order details');
            }
        } catch (error) {
            console.error('Error opening assign staff modal:', error);
            alert('Error loading order details');
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
            const fabricationSelect = document.getElementById('modal-assign-fabrication-staff');
            const installationSelect = document.getElementById('modal-assign-installation-staff');
            
            if (!orderId) {
                alert('Order ID not found');
                return;
            }
            
            try {
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
                        alert('Error assigning fabrication staff: ' + (data.message || 'Failed'));
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
                        alert('Error assigning installation staff: ' + (data.message || 'Failed'));
                        return;
                    }
                }
                
                alert('Staff assigned successfully');
                const modal = document.getElementById('assignStaffModal');
                if (modal) {
                    modal.classList.remove('active');
                    modal.style.display = 'none';
                }
                loadOrders();
            } catch (error) {
                console.error('Error assigning staff:', error);
                alert('Error assigning staff. Please try again.');
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
                alert('Error loading order details');
            }
        } catch (error) {
            console.error('Error opening link calendar modal:', error);
            alert('Error loading order details');
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
                alert('Error loading order details');
            }
        } catch (error) {
            console.error('Error opening export order modal:', error);
            alert('Error loading order details');
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
                    alert('Order ID not found');
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
                        window.location.href = data.download_url;
                    } else if (data.success && data.file_path) {
                        window.location.href = baseUrl + data.file_path;
                    } else {
                        alert('Export functionality is being prepared. Please try again later.');
                    }
                    
                    const modal = document.getElementById('exportOrderModal');
                    if (modal) {
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error exporting order:', error);
                    alert('Error exporting order. Please try again.');
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
                alert('Error loading order details');
            }
        } catch (error) {
            console.error('Error opening cancel order modal:', error);
            alert('Error loading order details');
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
                alert('Order ID not found');
                return;
            }
            
            if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
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
                    alert('Order cancelled successfully');
                    const modal = document.getElementById('cancelOrderModal');
                    if (modal) {
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                    }
                    loadOrders();
                } else {
                    alert('Error: ' + (data.message || 'Failed to cancel order'));
                }
            } catch (error) {
                console.error('Error cancelling order:', error);
                alert('Error cancelling order. Please try again.');
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
            'Pending Review': ['Approved', 'Cancelled'],
            'Awaiting Admin': ['Approved', 'Disapproved', 'Cancelled'],
            'Approved': ['In Fabrication', 'Cancelled'],
            'Ocular Pending': ['Approved', 'Cancelled'],
            'In Fabrication': ['Ready for Installation', 'Cancelled'],
            'Ready for Installation': ['Completed', 'Cancelled'],
            'Completed': [], // Final state
            'Cancelled': [], // Final state
            'Disapproved': [], // Final state
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
