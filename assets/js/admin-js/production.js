// Production / Fabrication Queue Module
// Handles Kanban Board and List View

let currentView = 'kanban';
let ordersData = [];
let staffData = [];
let currentOrderDetails = null;
let draggedCard = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeProduction();
    setupEventListeners();
    loadStaff();
    loadFabricationQueue();
});

// Initialize production module
function initializeProduction() {
    // Set default view
    switchView('kanban');
}

// Setup event listeners
function setupEventListeners() {
    // View toggle buttons
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            switchView(this.dataset.view);
        });
    });
    
    // Apply filters button
    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            loadFabricationQueue();
        });
    }
    
    // Close modal
    const closeModal = document.querySelector('.close-modal');
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            closeOrderDetailsModal();
        });
    }
    
    // Click outside modal to close
    const modal = document.querySelector('.order-details-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeOrderDetailsModal();
            }
        });
    }
            
            
            // Click handler for customization breakdown buttons inside the order details modal
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.view-breakdown-btn');
                if (!btn) return;
                e.preventDefault();
                var breakdownData = btn.getAttribute('data-breakdown');
                if (!breakdownData) return;
                var breakdownFields = [];
                try { breakdownFields = JSON.parse(breakdownData); } catch (err) { console.error('Failed to parse breakdown data:', err); return; }
                var contentHtml = '<div class="breakdown-list" style="padding:0;">';
                    breakdownFields.forEach(function(field) {
                        var label = field.label || '';
                        var value = field.value || field.val || '';
                        if (!value || value === '' || value === 'None') {
                            contentHtml += '<div style="margin-bottom:16px; padding:12px; background:#f9fafb; border-left:4px solid #d1d5db; border-radius:4px;"><strong style="display:block;color:#1f2937; margin-bottom:6px; font-size:14px;">' + label + '</strong><div style="color:#9ca3af; font-style:italic; font-size:13px;">Not specified</div></div>';
                        } else {
                            contentHtml += '<div style="margin-bottom:16px; padding:12px; background:#f0f9ff; border-left:4px solid #3b82f6; border-radius:4px;"><strong style="display:block;color:#1e40af; margin-bottom:6px; font-size:14px;">' + label + '</strong><div style="color:#1f2937; font-size:14px; font-weight:500;">' + value + '</div></div>';
                        }
                    });
                contentHtml += '</div>';

                var modal = document.getElementById('breakdownModal');
                if (!modal) {
                    var modalHtml = '<div id="breakdownModal" class="modal-backdrop" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:10000;">' +
                                    '<div class="modal-content" style="max-width:720px;width:90%;max-height:85vh;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.3);">' +
                                        '<div class="modal-header" style="background:#1e3a8a;color:#fff;padding:16px 20px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;">' +
                                            '<h3 style="margin:0;font-size:20px;font-weight:700;">2D Customization Breakdown</h3>' +
                                            '<button class="modal-close" id="breakdownModalClose" style="background:rgba(255,255,255,0.2);border:none;color:#fff;font-size:28px;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;">×</button>' +
                                        '</div>' +
                                        '<div class="modal-body" id="breakdownModalBody" style="padding:24px;background:#fff;border-radius:0 0 12px 12px;"></div>' +
                                    '</div>' +
                                '</div>';
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                    modal = document.getElementById('breakdownModal');
                    document.getElementById('breakdownModalClose').addEventListener('click', function() { modal.style.display = 'none'; document.body.style.overflow = ''; });
                    modal.addEventListener('click', function(ev) { if (ev.target === modal) { modal.style.display = 'none'; document.body.style.overflow = ''; } });
                }
                document.getElementById('breakdownModalBody').innerHTML = contentHtml;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
            
}

// Switch between Kanban and List view
function switchView(view) {
    currentView = view;
    
    // Update toggle buttons
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === view) {
            btn.classList.add('active');
        }
    });
    
    // Show/hide views
    const kanbanView = document.getElementById('kanban-view');
    const listView = document.getElementById('list-view');
    
    if (view === 'kanban') {
        if (kanbanView) kanbanView.style.display = 'flex';
        if (listView) listView.style.display = 'none';
        renderKanbanView();
    } else {
        if (kanbanView) kanbanView.style.display = 'none';
        if (listView) listView.style.display = 'block';
        renderListView();
    }
}

// (Requests view removed from production page)

// Load fabrication queue from server
function loadFabricationQueue() {
    const statusFilter = document.getElementById('status-filter')?.value || 'all';
    const staffFilter = document.getElementById('staff-filter')?.value || 'all';
    const dateStartFilter = document.getElementById('date-start-filter')?.value || '';
    const dateEndFilter = document.getElementById('date-end-filter')?.value || '';
    const searchFilter = document.getElementById('search-filter')?.value || '';
    
    const params = new URLSearchParams();
    if (statusFilter !== 'all') params.append('status', statusFilter);
    if (staffFilter !== 'all') params.append('staff', staffFilter);
    if (dateStartFilter) params.append('date_start', dateStartFilter);
    if (dateEndFilter) params.append('date_end', dateEndFilter);
    if (searchFilter) params.append('search', searchFilter);
    
    fetch(`${getFabricationQueueUrl}?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ordersData = data.orders;
                // Debug: Log ready orders
                const readyOrders = ordersData.filter(o => o.queue_status === 'ready');
                console.log('Total orders loaded:', ordersData.length);
                console.log('Ready orders:', readyOrders.length, readyOrders);
                renderCurrentView();
                updateFoundText();
            } else {
                console.error('Error loading queue:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Load staff members
function loadStaff() {
    fetch(`${baseUrl}AdminCon/get_fabrication_staff`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                staffData = data.staff;
                populateStaffFilter();
            }
        })
        .catch(error => {
            console.error('Error loading staff:', error);
        });
}

// Populate staff filter dropdown
function populateStaffFilter() {
    const staffFilter = document.getElementById('staff-filter');
    if (!staffFilter) return;
    
    // Keep "All Staff" option
    const allOption = staffFilter.querySelector('option[value="all"]');
    staffFilter.innerHTML = '';
    if (allOption) {
        staffFilter.appendChild(allOption);
    }
    
    staffData.forEach(staff => {
        const option = document.createElement('option');
        option.value = staff.id;
        option.textContent = staff.name;
        staffFilter.appendChild(option);
    });
}

// Render current view
function renderCurrentView() {
    if (currentView === 'kanban') {
        renderKanbanView();
    } else {
        renderListView();
    }
}

// ======================
// KANBAN VIEW
// ======================
function renderKanbanView() {
    const columns = {
        'queued': document.getElementById('queued-cards'),
        'in-progress': document.getElementById('in-progress-cards'),
        'quality-check': document.getElementById('quality-check-cards'),
        'ready': document.getElementById('ready-cards'),
        'completed': document.getElementById('completed-cards')
    };
    
    // Clear all columns
    Object.values(columns).forEach(column => {
        if (column) column.innerHTML = '';
    });
    
    // Group orders by queue status
    const groupedOrders = {
        'queued': [],
        'in-progress': [],
        'quality-check': [],
        'ready': [],
        'completed': []
    };
    
    ordersData.forEach(order => {
        const status = order.queue_status || 'queued';
        if (groupedOrders[status]) {
            groupedOrders[status].push(order);
        }
    });
    
    // Render cards in each column
    Object.keys(columns).forEach(status => {
        const column = columns[status];
        const orders = groupedOrders[status] || [];
        
        // Update column count
        const countElement = column?.closest('.kanban-column')?.querySelector('.column-count');
        if (countElement) {
            countElement.textContent = orders.length;
        }
        
        // Render cards
        if (column) {
            orders.forEach(order => {
                const card = createKanbanCard(order);
                column.appendChild(card);
            });
        }
    });
    
    // Setup drag and drop
    setupDragAndDrop();
}

// Create kanban card
function createKanbanCard(order) {
    const card = document.createElement('div');
    card.className = 'kanban-card';
    card.draggable = true;
    card.dataset.orderId = order.order_id;
    card.dataset.status = order.queue_status;
    card.dataset.paymentStatus = order.payment_data?.fabrication_status || 'Pending';
    
    // Determine payment badge color and text
    const fabPaymentStatus = order.payment_data?.fabrication_status || 'Pending';
    const isReady = order.queue_status === 'ready' || order.queue_status === 'completed';
    let paymentBadgeColor, paymentBadgeText;
    
    if (fabPaymentStatus === 'Paid') {
        paymentBadgeColor = '#28a745'; // Green
        paymentBadgeText = '40% Paid';
    } else if (isReady) {
        paymentBadgeColor = '#dc3545'; // Red - payment required
        paymentBadgeText = '40% Due';
    } else {
        paymentBadgeColor = '#6c757d'; // Gray - not yet required
        paymentBadgeText = 'Payment Locked';
    }
    
    card.innerHTML = `
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="${baseUrl}admin-orders?order_id=${order.order_id}" class="card-order-number">${order.order_number}</a>
            <span class="payment-badge" style="background: ${paymentBadgeColor}; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600;">${paymentBadgeText}</span>
        </div>
        <div class="card-customer">${order.customer_name || 'N/A'}</div>
        <div class="card-product">${order.product_name || 'N/A'}</div>
        <div class="card-quantity">Qty: ${order.quantity || 1}</div>
        ${order.fabrication_start ? `<div class="card-dates">Start: ${formatDate(order.fabrication_start)}</div>` : ''}
        ${order.fabrication_end ? `<div class="card-dates">End: ${formatDate(order.fabrication_end)}</div>` : ''}
        <div class="card-staff ${order.fabrication_staff_name === 'Unassigned' ? 'unassigned' : ''}">
            Staff: ${order.fabrication_staff_name || 'Unassigned'}
        </div>
    `;
    
    // Add click event to open details
    card.addEventListener('click', function(e) {
        if (!e.target.closest('a')) {
            showOrderDetails(order.order_id);
        }
    });
    
    return card;
}

// Setup drag and drop
function setupDragAndDrop() {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-column');
    
    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });
    
    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragenter', handleDragEnter);
        column.addEventListener('dragleave', handleDragLeave);
    });
}

function handleDragStart(e) {
    draggedCard = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    document.querySelectorAll('.kanban-column').forEach(col => {
        col.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    this.classList.add('drag-over');
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    if (draggedCard !== this) {
        const column = this.closest('.kanban-column');
        const newStatus = column.dataset.status;
        const orderId = draggedCard.dataset.orderId;
        const paymentStatus = draggedCard.dataset.paymentStatus;
        
        // VALIDATION: Cannot move to 'completed' unless payment is 'Paid'
        if (newStatus === 'completed' && paymentStatus !== 'Paid') {
            showToast('Cannot move to Completed: Fabrication Payment (40%) must be PAID first.', 'warning');
            this.classList.remove('drag-over');
            return false;
        }
        // VALIDATION: Orders in 'ready' should not be moved back to earlier stages
        const oldStatus = draggedCard.dataset.status;
        const disallowedBack = ['queued', 'in-progress', 'quality-check'];
        if (oldStatus === 'ready' && disallowedBack.includes(newStatus)) {
            showToast('Cannot move order back from Ready for Installation.', 'warning');
            this.classList.remove('drag-over');
            return false;
        }
        
        // Update order status
        updateOrderStatus(orderId, newStatus);
        
        // Move card
        const cardsContainer = column.querySelector('.kanban-cards');
        if (cardsContainer) {
            cardsContainer.appendChild(draggedCard);
            draggedCard.dataset.status = newStatus;
        }
    }
    
    this.classList.remove('drag-over');
    return false;
}

// ======================
// LIST VIEW
// ======================
function renderListView() {
    const tbody = document.getElementById('production-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    ordersData.forEach((order, index) => {
        const row = createTableRow(order, index + 1);
        tbody.appendChild(row);
    });
}

// Create table row
function createTableRow(order, index) {
    const row = document.createElement('tr');
    
    const statusClass = `status-${order.queue_status || 'queued'}`;
    const statusText = formatStatusText(order.queue_status || 'queued');
    
    row.innerHTML = `
        <td>${index}</td>
        <td><a href="${baseUrl}admin-orders?order_id=${order.order_id}" class="table-order-link">${order.order_number}</a></td>
        <td>${order.customer_name || 'N/A'}</td>
        <td>${order.product_name || 'N/A'}</td>
        <td>${order.quantity || 1}</td>
        <td>${order.fabrication_start ? formatDate(order.fabrication_start) : 'N/A'}</td>
        <td>${order.fabrication_end ? formatDate(order.fabrication_end) : 'N/A'}</td>
        <td>${order.fabrication_staff_name || 'Unassigned'}</td>
        <td><span class="table-status-badge ${statusClass}">${statusText}</span></td>
        <td>
            <div class="action-dropdown">
                <button class="action-btn" onclick="toggleActionMenu(this)">Actions</button>
                <div class="action-menu">
                    <div class="action-menu-item" onclick="showOrderDetails(${order.order_id})">View Details</div>
                    <div class="action-menu-item" onclick="assignStaff(${order.order_id})">Assign Staff</div>
                    ${order.queue_status !== 'completed' ? `
                        <div class="action-menu-item" onclick="moveToQualityCheck(${order.order_id})">Move to Quality Check</div>
                        <div class="action-menu-item" onclick="markComplete(${order.order_id})">Mark Complete</div>
                    ` : ''}
                </div>
            </div>
        </td>
    `;
    
    return row;
}

// ======================
// ORDER DETAILS MODAL
// ======================
function showOrderDetails(orderId) {
    fetch(`${baseUrl}AdminCon/get_production_order_details?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentOrderDetails = data.order;
                // Debug: Log payment data
                console.log('Order payment_data:', currentOrderDetails.payment_data);
                console.log('Downpayment amount:', currentOrderDetails.payment_data?.downpayment_amount);
                console.log('Downpayment method:', currentOrderDetails.payment_data?.downpayment_method);
                renderOrderDetailsModal();
                openOrderDetailsModal();
            } else {
                showToast('Error loading order details: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading order details', 'error');
        });
}

function renderOrderDetailsModal() {
    if (!currentOrderDetails) return;
    
    const modal = document.querySelector('.order-details-modal');
    if (!modal) return;
    
    const order = currentOrderDetails;
    
    // This will be rendered by updating the modal content
    // For now, we'll create a basic structure
    const modalBody = modal.querySelector('.modal-body');
    if (modalBody) {
        modalBody.innerHTML = `
            <div class="modal-section">
                <h4 class="modal-section-title">Order Information</h4>
                <div class="order-info-grid">
                    <div class="info-item">
                        <div class="info-label">Order Number</div>
                        <div class="info-value">${order.order_number}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">${order.status}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Customer</div>
                        <div class="info-value">${order.customer.name}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Role</div>
                        <div class="info-value">${order.customer.role || 'N/A'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">${order.customer.email || 'N/A'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value">${order.customer.phone || 'N/A'}</div>
                    </div>
                </div>
                
                <div class="items-list">
                    <h5>Order Items</h5>
                    ${order.items.map(item => `
                        <div class="item-row" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 10px; background: #fafafa;">
                            <div style="margin-bottom: 10px;">
                                <strong style="font-size: 16px; color: #02455F;">${item.product_name}</strong>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 13px; color: #555;">
                                ${item.dimensions ? `<div><span style="color: #888;">Dimensions:</span> <strong>${item.dimensions}</strong></div>` : ''}
                                ${item.quantity ? `<div><span style="color: #888;">Quantity:</span> <strong>${item.quantity}</strong></div>` : ''}
                                ${item.glass_shape ? `<div><span style="color: #888;">Shape:</span> <strong>${item.glass_shape}</strong></div>` : ''}
                                ${item.glass_type ? `<div><span style="color: #888;">Glass Type:</span> <strong>${item.glass_type}</strong></div>` : ''}
                                ${item.glass_thickness ? `<div><span style="color: #888;">Thickness:</span> <strong>${item.glass_thickness}</strong></div>` : ''}
                                ${item.edge_work ? `<div><span style="color: #888;">Edge Work:</span> <strong>${item.edge_work}</strong></div>` : ''}
                                ${item.frame_type ? `<div><span style="color: #888;">Frame:</span> <strong>${item.frame_type}</strong></div>` : ''}
                                ${item.engraving && item.engraving !== 'None' && item.engraving !== 'none' ? `<div><span style="color: #888;">Engraving:</span> <strong>${item.engraving}</strong></div>` : ''}
                                ${item.customization ? (() => {
                                    try {
                                        const c = (typeof item.customization === 'object') ? item.customization : JSON.parse(item.customization);
                                        const unit = c._unit || c.unit || '';
                                        const size = (c._width && c._height) ? `${c._width}${unit} x ${c._height}${unit}` : (c.Dimensions || '');
                                        const map = [
                                            {label: 'Size', value: size},
                                            {label: 'Number Of Panels', value: c.numberOfPanels || c.numberOfPanelsValue || c.panels || ''},
                                            {label: 'Panel Configuration', value: c.panelConfiguration || c.configuration || ''},
                                            {label: 'Track System', value: c.trackSystem || ''},
                                            {label: 'Transom Type', value: c.transomType || ''},
                                            {label: 'Frame Color', value: c.frameColor || ''},
                                            {label: 'Glass Type', value: c.glassType || c.GlassType || ''},
                                            {label: 'Glass Color', value: c.glassColor || ''},
                                            {label: 'Glass Thickness', value: c.glassThickness || c.GlassThickness || ''},
                                            {label: 'Lock Type', value: c.lockType || ''},
                                            {label: 'Roller Type', value: c.rollerType || ''}
                                        ];
                                        const breakdown = map.filter(x => x.value && x.value !== '' );
                                        const breakdownJson = JSON.stringify(breakdown).replace(/'/g, "&#39;");
                                        const summary = breakdown.slice(0,2).map(b => `${b.label}: ${b.value}`).join(' • ');
                                        const moreCount = Math.max(0, breakdown.length - 2);
                                        const moreHtml = moreCount > 0 ? `<br><span style="font-size:12px; color:#4b5563;">and ${moreCount} more</span>` : '';
                                        const style = 'display:inline-block; text-align:left; padding:10px 14px; border-radius:6px; border:2px solid #3b82f6; background:#eff6ff; color:#1e40af; cursor:pointer; font-size:13px; line-height:1.6; max-width:100%; overflow-wrap:break-word; white-space:normal; transition:all 0.18s ease; font-weight:600; box-shadow:0 2px 4px rgba(59,130,246,0.1);';
                                        return `<div style="grid-column:1 / -1;">` +
                                            `<button type="button" class="view-breakdown-btn" data-breakdown='${breakdownJson}' style="${style}" onmouseover="this.style.backgroundColor='#dbeafe'; this.style.borderColor='#2563eb'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.2)';" onmouseout="this.style.backgroundColor='#eff6ff'; this.style.borderColor='#3b82f6'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(59,130,246,0.1)';">` +
                                                `${summary || 'View details'} ${moreHtml}` +
                                                `<br><span style="font-size:11px; opacity:0.7;">▼ Click to expand</span>` +
                                            `</button>` +
                                        `</div>`;
                                    } catch (e) {
                                        return '';
                                    }
                                })() : ''}
                            </div>
                        </div>
                    `).join('')}
                    ${order.items.length === 0 ? '<p style="color: #888; font-style: italic;">No order items found</p>' : ''}
                </div>
            </div>
            
            <div class="modal-section">
                <h4 class="modal-section-title">Fabrication Details</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" id="fabrication-start-date" value="${order.fabrication.start_date || ''}">
                    </div>
                    <div class="form-group">
                        <label>Expected End Date</label>
                        <input type="date" id="fabrication-end-date" value="${order.fabrication.end_date || ''}">
                    </div>
                </div>
                <div class="form-row">
                    <!-- Actual start/end removed: dates are auto-managed when status changes -->
                </div>
                <div class="form-group">
                    <label>Assigned Staff</label>
                    <select id="fabrication-staff">
                        <option value="">Unassigned</option>
                        ${staffData.map(staff => `
                            <option value="${staff.id}" ${staff.id == order.fabrication.staff_id ? 'selected' : ''}>${staff.name}</option>
                        `).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Fabrication Status</label>
                    <select id="fabrication-status" onchange="updateProgressOnStatusChange()">
                        <option value="Queued" ${order.fabrication.status === 'Queued' ? 'selected' : ''}>Queued</option>
                        <option value="In Progress" ${order.fabrication.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                        <option value="Quality Check" ${order.fabrication.status === 'Quality Check' ? 'selected' : ''}>Quality Check</option>
                        <option value="Ready" ${order.fabrication.status === 'Ready' ? 'selected' : ''}>Ready</option>
                        <option value="Completed" ${order.fabrication.status === 'Completed' ? 'selected' : ''}>Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fabrication Notes</label>
                    <textarea id="fabrication-notes" rows="4">${order.fabrication.notes || ''}</textarea>
                </div>
                <div class="form-group">
                    <label>Quality Check Notes</label>
                    <textarea id="quality-check-notes" rows="4">${order.fabrication.quality_check_notes || ''}</textarea>
                </div>
                <div class="form-group">
                    <label>Issues/Problems</label>
                    <textarea id="fabrication-issues" rows="4">${order.fabrication.issues || ''}</textarea>
                </div>
            </div>
            
            <div class="modal-section">
                <h4 class="modal-section-title">Payment Breakdown</h4>
                <div style="margin-bottom: 20px; padding: 12px; background: #f8f9fa; border-left: 4px solid #02455F; border-radius: 4px;">
                    <small style="color: #495057;">
                        <i class="fas fa-info-circle"></i> <strong>Payment Schedule:</strong> 50% downpayment at ocular visit, 40% after fabrication complete, 10% after installation complete.
                    </small>
                </div>
                
                <!-- Calculate payment amounts -->
                ${(() => {
                    const totalAmount = parseFloat(order.total_amount || order.total || 0);
                    const dpAmount = order.payment_data?.downpayment_amount || (totalAmount * 0.5);
                    const fabAmount = order.payment_data?.fabrication_amount || (totalAmount * 0.4);
                    const instAmount = totalAmount * 0.1;
                    window._currentOrderPayments = { dpAmount, fabAmount, instAmount, totalAmount };
                    return '';
                })()}
                
                <!-- Payment Stage 1: Downpayment (50%) - READ ONLY -->
                <div style="border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; opacity: 0.7;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h5 style="margin: 0; color: #6c757d;">
                            <i class="fas fa-check-circle"></i> Downpayment (50%)
                        </h5>
                        <span class="badge" style="background-color: #28a745; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 12px;">Paid</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 10px;">
                        <div>
                            <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 5px; display: block;">Amount (₱)</label>
                            <input type="number" value="${order.payment_data?.downpayment_amount || (parseFloat(order.total_amount || order.total || 0) * 0.5).toFixed(2)}" class="form-control" disabled style="background: #e9ecef;">
                        </div>
                        <div>
                            <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 5px; display: block;">Payment Method</label>
                            <input type="text" value="${order.payment_data?.downpayment_method || '—'}" class="form-control" disabled style="background: #e9ecef;">
                        </div>
                        <div>
                            <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 5px; display: block;">Status</label>
                            <input type="text" value="Paid" class="form-control" disabled style="background: #e9ecef;">
                        </div>
                    </div>
                    <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
                        <i class="fas fa-info-circle"></i> Downpayment was completed during the ocular visit.
                    </small>
                </div>
                
                <!-- Payment Stage 2: Fabrication Payment (40%) - READ ONLY (System updates via PayMongo) -->
                <div id="fab-payment-section" style="border: 2px solid ${order.payment_data?.fabrication_status === 'Paid' ? '#28a745' : (order.fabrication.status === 'Ready' || order.fabrication.status === 'Completed') ? '#02455F' : '#dee2e6'}; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: ${(order.fabrication.status === 'Ready' || order.fabrication.status === 'Completed') ? '#ffffff' : '#f8f9fa'}; opacity: ${(order.fabrication.status === 'Ready' || order.fabrication.status === 'Completed') ? '1' : '0.7'};">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h5 style="margin: 0; color: ${(order.fabrication.status === 'Ready' || order.fabrication.status === 'Completed') ? '#02455F' : '#6c757d'};">
                            <i class="fas ${order.payment_data?.fabrication_status === 'Paid' ? 'fa-check-circle' : (order.fabrication.status === 'Ready' || order.fabrication.status === 'Completed') ? 'fa-money-bill-wave' : 'fa-lock'}"></i> Fabrication Payment (40%)
                        </h5>
                        <span id="fab-payment-badge" class="badge" style="background-color: ${order.payment_data?.fabrication_status === 'Paid' ? '#28a745' : (order.fabrication.status === 'Ready' || order.fabrication.status === 'Completed') ? '#ffc107' : '#6c757d'}; color: ${order.payment_data?.fabrication_status === 'Paid' ? '#fff' : (order.fabrication.status === 'Ready' || order.fabrication.status === 'Completed') ? '#000' : '#fff'}; padding: 4px 12px; border-radius: 4px; font-size: 12px;">${(order.fabrication.status === 'Ready' || order.fabrication.status === 'Completed') ? (order.payment_data?.fabrication_status || 'Pending') : 'Locked'}</span>
                    </div>
                    ${(order.fabrication.status !== 'Ready' && order.fabrication.status !== 'Completed') ? `
                    <p style="color: #6c757d; font-size: 13px; margin: 10px 0;"><i class="fas fa-info-circle"></i> Payment will be available when fabrication status reaches "Ready". Customer pays online.</p>
                    ` : `
                    <p style="color: #17a2b8; font-size: 13px; margin: 10px 0;"><i class="fas fa-info-circle"></i> Customer pays this online via PayMongo. Status updates automatically when payment is verified.</p>
                    `}
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 10px;">
                        <div>
                            <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 5px; display: block;">Amount (₱)</label>
                            <input type="text" id="fab-payment-amount" value="${order.payment_data?.fabrication_amount ? parseFloat(order.payment_data.fabrication_amount).toLocaleString('en-PH', {minimumFractionDigits: 2}) : (parseFloat(order.total_amount || order.total || 0) * 0.4).toLocaleString('en-PH', {minimumFractionDigits: 2})}" class="form-control" disabled style="background: #e9ecef; font-weight: 600;">
                        </div>
                        <div>
                            <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 5px; display: block;">Payment Method</label>
                            <input type="text" id="fab-payment-method" value="${order.payment_data?.fabrication_method || (order.payment_data?.fabrication_status === 'Paid' ? '—' : 'Online Payment')}" class="form-control" disabled style="background: #e9ecef;">
                        </div>
                        <div>
                            <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 5px; display: block;">Status</label>
                            <input type="text" id="fab-payment-status" value="${order.payment_data?.fabrication_status || 'Pending'}" class="form-control" disabled style="background: #e9ecef; color: ${order.payment_data?.fabrication_status === 'Paid' ? '#28a745' : '#dc3545'}; font-weight: 600;">
                        </div>
                    </div>
                    ${order.payment_data?.fabrication_receipt_url ? `
                    <div style="margin-top: 10px;">
                        <a href="${order.payment_data.fabrication_receipt_url}" target="_blank" style="color: #02455F; text-decoration: underline;">
                            <i class="fas fa-file-pdf" style="margin-right: 5px;"></i>View payment receipt
                        </a>
                    </div>
                    ` : ''}
                </div>
                
                <!-- Payment Stage 3: Installation Payment (10%) - LOCKED -->
                <div style="border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; opacity: 0.6;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h5 style="margin: 0; color: #6c757d;">
                            <i class="fas fa-lock"></i> Installation Payment (10%)
                        </h5>
                        <span class="badge" style="background-color: #6c757d; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 12px;">Locked</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 10px;">
                        <div>
                            <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 5px; display: block;">Amount (₱)</label>
                            <input type="text" value="${(parseFloat(order.total_amount || order.total || 0) * 0.1).toLocaleString('en-PH', {minimumFractionDigits: 2})}" class="form-control" disabled style="background: #e9ecef; font-weight: 600;">
                        </div>
                        <div>
                            <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 5px; display: block;">Payment Method</label>
                            <input type="text" value="Cash / Check (On-site)" class="form-control" disabled style="background: #e9ecef;">
                        </div>
                        <div>
                            <label style="font-weight: 600; color: #495057; font-size: 12px; margin-bottom: 5px; display: block;">Status</label>
                            <input type="text" value="Locked" class="form-control" disabled style="background: #e9ecef;">
                        </div>
                    </div>
                    <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
                        <i class="fas fa-info-circle"></i> This payment is collected on-site after installation is complete.
                    </small>
                </div>
            </div>
            
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="saveOrderDetails()">Save Changes</button>
                <button class="modal-btn modal-btn-secondary" onclick="closeOrderDetailsModal()">Cancel</button>
                <button class="modal-btn modal-btn-success" onclick="markComplete(${order.order_id})">Mark Complete</button>
            </div>
        `;
    }
}

function updateFabPaymentBadge() {
    const status = document.getElementById('fab-payment-status')?.value || 'Pending';
    const badge = document.getElementById('fab-payment-badge');
    if (badge) {
        badge.textContent = status;
        if (status === 'Paid') {
            badge.style.backgroundColor = '#28a745';
            badge.style.color = '#fff';
        } else {
            badge.style.backgroundColor = '#ffc107';
            badge.style.color = '#000';
        }
    }
}

// Toggle fabrication payment fields based on fabrication status
function toggleFabPaymentFields() {
    const fabStatus = document.getElementById('fabrication-status')?.value;
    const isUnlocked = (fabStatus === 'Ready' || fabStatus === 'Completed');
    
    const section = document.getElementById('fab-payment-section');
    const amountField = document.getElementById('fab-payment-amount');
    const methodField = document.getElementById('fab-payment-method');
    const statusField = document.getElementById('fab-payment-status');
    const receiptField = document.getElementById('fab-payment-receipt');
    const badge = document.getElementById('fab-payment-badge');
    
    if (section) {
        section.style.borderColor = isUnlocked ? '#02455F' : '#dee2e6';
        section.style.background = isUnlocked ? '#ffffff' : '#f8f9fa';
        section.style.opacity = isUnlocked ? '1' : '0.7';
        
        // Update the header icon and color
        const header = section.querySelector('h5');
        if (header) {
            header.style.color = isUnlocked ? '#02455F' : '#6c757d';
            const icon = header.querySelector('i');
            if (icon) {
                icon.className = isUnlocked ? 'fas fa-money-bill-wave' : 'fas fa-lock';
            }
        }
    }
    
    // Toggle field states
    [amountField, methodField, statusField, receiptField].forEach(field => {
        if (field) {
            field.disabled = !isUnlocked;
            field.style.background = isUnlocked ? '' : '#e9ecef';
            field.style.cursor = isUnlocked ? '' : 'not-allowed';
        }
    });
    
    // Update badge
    if (badge && !isUnlocked) {
        badge.textContent = 'Locked';
        badge.style.backgroundColor = '#6c757d';
        badge.style.color = '#fff';
    } else if (badge && isUnlocked) {
        const paymentStatus = statusField?.value || 'Pending';
        badge.textContent = paymentStatus;
        badge.style.backgroundColor = paymentStatus === 'Paid' ? '#28a745' : '#ffc107';
        badge.style.color = paymentStatus === 'Paid' ? '#fff' : '#000';
    }
}

function openOrderDetailsModal() {
    const modal = document.querySelector('.order-details-modal');
    if (modal) {
        modal.classList.add('open');
    }
}

function closeOrderDetailsModal() {
    const modal = document.querySelector('.order-details-modal');
    if (modal) {
        modal.classList.remove('open');
    }
    currentOrderDetails = null;
}

function saveOrderDetails() {
    if (!currentOrderDetails) return;
    
    const fabricationStatus = document.getElementById('fabrication-status')?.value || '';
    const paymentStatus = document.getElementById('fab-payment-status')?.value || 'Pending';
    
    // Validate: Cannot complete without payment
    if (fabricationStatus === 'Completed' && paymentStatus !== 'Paid') {
        showToast('Cannot save with Completed status: Fabrication Payment must be PAID first.', 'warning');
        return;
    }
    
    // Map fabrication status to order status
    // Note: 'Ready' means fabrication is ready but order stays In Fabrication
    // 'Completed' means fabrication is done and order moves to Ready for Installation
    const statusMap = {
        'Queued': 'Approved',
        'In Progress': 'In Fabrication',
        'Quality Check': 'In Fabrication',
        'Ready': 'In Fabrication',
        'Completed': 'Ready for Installation'
    };
    
    // Map fabrication status to progress
    const statusProgressMap = {
        'In Progress': 25,
        'Quality Check': 50,
        'Ready': 75,
        'Completed': 100
    };
    
    const orderStatus = statusMap[fabricationStatus] || currentOrderDetails.status;
    // Get progress from status map if status has a defined progress, otherwise use slider value
    const progress = statusProgressMap[fabricationStatus] !== undefined 
        ? statusProgressMap[fabricationStatus] 
        : (document.getElementById('fabrication-progress')?.value || 0);
    
    const formData = new FormData();
    formData.append('order_id', currentOrderDetails.order_id);
    formData.append('progress', progress);
    formData.append('status', orderStatus);
    formData.append('fabrication_status', fabricationStatus);
    formData.append('start_date', document.getElementById('fabrication-start-date')?.value || '');
    formData.append('end_date', document.getElementById('fabrication-end-date')?.value || '');
    // actual start/end are no longer sent; controller auto-manages start/end when status changes
    formData.append('staff_id', document.getElementById('fabrication-staff')?.value || '');
    formData.append('notes', document.getElementById('fabrication-notes')?.value || '');
    formData.append('quality_check_notes', document.getElementById('quality-check-notes')?.value || '');
    formData.append('issues', document.getElementById('fabrication-issues')?.value || '');
    
    // Add payment breakdown data
    formData.append('fabrication_payment_amount', document.getElementById('fab-payment-amount')?.value || '');
    formData.append('fabrication_payment_method', document.getElementById('fab-payment-method')?.value || '');
    formData.append('fabrication_payment_status', document.getElementById('fab-payment-status')?.value || 'Pending');
    
    // Add receipt file if uploaded
    const receiptInput = document.getElementById('fab-payment-receipt');
    if (receiptInput && receiptInput.files && receiptInput.files[0]) {
        formData.append('fabrication_payment_receipt', receiptInput.files[0]);
    }
    
    fetch(`${baseUrl}AdminCon/update_fabrication_progress`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Check if response is actually JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('Order updated successfully', 'success');
            closeOrderDetailsModal();
            loadFabricationQueue();
        } else {
            showToast('Error updating order: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error updating order:', error);
        showToast('Error updating order: ' + (error.message || 'Please check the console for details'), 'error');
    });
}

// ======================
// HELPER FUNCTIONS
// ======================

function updateOrderStatus(orderId, newStatus) {
    // Map queue status to order status and fabrication status with progress
    // Note: 'ready' means fabrication is ready but order stays In Fabrication
    // 'completed' means fabrication is done and order moves to Ready for Installation
    const statusMap = {
        'queued': { status: 'Approved', fabrication_status: 'Queued', progress: 0 },
        'in-progress': { status: 'In Fabrication', fabrication_status: 'In Progress', progress: 25 },
        'quality-check': { status: 'In Fabrication', fabrication_status: 'Quality Check', progress: 50 },
        'ready': { status: 'In Fabrication', fabrication_status: 'Ready', progress: 75 },
        'completed': { status: 'Ready for Installation', fabrication_status: 'Completed', progress: 100 }
    };
    
    const statusInfo = statusMap[newStatus] || { status: 'Approved', fabrication_status: 'Queued', progress: null };
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', statusInfo.status);
    formData.append('fabrication_status', statusInfo.fabrication_status);
        // Set progress based on status (always set progress for all statuses)
        formData.append('progress', statusInfo.progress !== null ? statusInfo.progress : 0);
    
    fetch(`${baseUrl}AdminCon/update_fabrication_progress`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadFabricationQueue();
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
    });
}

function moveToQualityCheck(orderId) {
    const formData = new FormData();
    formData.append('order_id', orderId);
    
    fetch(`${baseUrl}AdminCon/move_to_quality_check`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Order moved to quality check', 'success');
            loadFabricationQueue();
        } else {
            showToast('Error: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error moving order', 'error');
    });
}

function markComplete(orderId) {
    // Validate payment status before completing
    const paymentStatus = document.getElementById('fab-payment-status')?.value || 'Pending';
    if (paymentStatus !== 'Paid') {
        showToast('Cannot mark as Complete: Fabrication Payment (40%) must be PAID first.', 'warning');
        return;
    }
    
    if (!confirm('Mark this order as complete?')) return;
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    
    fetch(`${baseUrl}AdminCon/mark_fabrication_complete`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Order marked as complete', 'success');
            closeOrderDetailsModal();
            loadFabricationQueue();
        } else {
            showToast('Error: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error marking order as complete', 'error');
    });
}

function assignStaff(orderId) {
    showOrderDetails(orderId);
    // Focus on staff dropdown when modal opens
    setTimeout(() => {
        const staffSelect = document.getElementById('fabrication-staff');
        if (staffSelect) {
            staffSelect.focus();
        }
    }, 100);
}

function editOrderProgress(orderId) {
    showOrderDetails(orderId);
}

function toggleActionMenu(button) {
    const menu = button.nextElementSibling;
    document.querySelectorAll('.action-menu').forEach(m => {
        if (m !== menu) m.classList.remove('show');
    });
    menu.classList.toggle('show');
}

// Close action menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown')) {
        document.querySelectorAll('.action-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
}

function formatStatusText(status) {
    const statusMap = {
        'queued': 'Queued',
        'in-progress': 'In Progress',
        'quality-check': 'Quality Check',
        'ready': 'Ready',
        'completed': 'Completed'
    };
    return statusMap[status] || status;
}

function updateProgressOnStatusChange() {
    const statusSelect = document.getElementById('fabrication-status');
    const progressSlider = document.getElementById('fabrication-progress');
    const progressValue = document.getElementById('progress-value');
    
    if (statusSelect && progressSlider && progressValue) {
        const selectedStatus = statusSelect.value;
        
        // Check if trying to move to Completed without payment
        if (selectedStatus === 'Completed') {
            const paymentStatus = document.getElementById('fab-payment-status')?.value;
            if (paymentStatus !== 'Paid') {
                showToast('Cannot mark as Completed: Fabrication Payment must be PAID first.', 'warning');
                // Reset to previous status (Ready)
                statusSelect.value = 'Ready';
                return;
            }
        }
        
        // Set progress based on status
        const statusProgressMap = {
            'Queued': 0,
            'In Progress': 25,
            'Quality Check': 50,
            'Ready': 75,
            'Completed': 100
        };
        
        if (statusProgressMap.hasOwnProperty(selectedStatus)) {
            const progress = statusProgressMap[selectedStatus];
            progressSlider.value = progress;
            progressValue.textContent = progress.toString();
        }
        
        // Toggle fabrication payment fields based on new status
        toggleFabPaymentFields();
    }
}

function updateFoundText() {
    const foundText = document.querySelector('.found-text');
    if (foundText) {
        const count = ordersData.length;
        foundText.textContent = `(${count} ${count === 1 ? 'order' : 'orders'})`;
    }
}
