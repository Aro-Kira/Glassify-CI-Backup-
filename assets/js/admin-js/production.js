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

// Load fabrication queue from server
function loadFabricationQueue() {
    const statusFilter = document.getElementById('status-filter')?.value || 'all';
    const orderTypeFilter = document.getElementById('order-type-filter')?.value || 'all';
    const staffFilter = document.getElementById('staff-filter')?.value || 'all';
    const dateStartFilter = document.getElementById('date-start-filter')?.value || '';
    const dateEndFilter = document.getElementById('date-end-filter')?.value || '';
    const searchFilter = document.getElementById('search-filter')?.value || '';
    
    const params = new URLSearchParams();
    if (statusFilter !== 'all') params.append('status', statusFilter);
    if (orderTypeFilter !== 'all') params.append('order_type', orderTypeFilter);
    if (staffFilter !== 'all') params.append('staff', staffFilter);
    if (dateStartFilter) params.append('date_start', dateStartFilter);
    if (dateEndFilter) params.append('date_end', dateEndFilter);
    if (searchFilter) params.append('search', searchFilter);
    
    fetch(`${getFabricationQueueUrl}?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ordersData = data.orders;
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
    
    const orderTypeClass = order.order_type === 'Site-Assessed' ? 'badge-site-assessed' : 'badge-direct';
    const orderTypeText = order.order_type === 'Site-Assessed' ? 'Site-Assessed' : 'Direct';
    
    card.innerHTML = `
        <div class="card-header">
            <a href="${baseUrl}admin-orders?order_id=${order.order_id}" class="card-order-number">${order.order_number}</a>
            <span class="order-type-badge ${orderTypeClass}">${orderTypeText}</span>
        </div>
        <div class="card-customer">${order.customer_name || 'N/A'}</div>
        <div class="card-product">${order.product_name || 'N/A'}</div>
        <div class="card-quantity">Qty: ${order.quantity || 1}</div>
        ${order.fabrication_start ? `<div class="card-dates">Start: ${formatDate(order.fabrication_start)}</div>` : ''}
        ${order.fabrication_end ? `<div class="card-dates">End: ${formatDate(order.fabrication_end)}</div>` : ''}
        <div class="card-staff ${order.fabrication_staff_name === 'Unassigned' ? 'unassigned' : ''}">
            Staff: ${order.fabrication_staff_name || 'Unassigned'}
        </div>
        <div class="progress-container">
            <div class="progress-label">
                <span>Progress</span>
                <span>${order.progress || 0}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: ${order.progress || 0}%"></div>
            </div>
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
    
    const orderTypeClass = order.order_type === 'Site-Assessed' ? 'badge-site-assessed' : 'badge-direct';
    const orderTypeText = order.order_type === 'Site-Assessed' ? 'Site-Assessed' : 'Direct';
    const statusClass = `status-${order.queue_status || 'queued'}`;
    const statusText = formatStatusText(order.queue_status || 'queued');
    
    row.innerHTML = `
        <td>${index}</td>
        <td><a href="${baseUrl}admin-orders?order_id=${order.order_id}" class="table-order-link">${order.order_number}</a></td>
        <td>${order.customer_name || 'N/A'}</td>
        <td>${order.product_name || 'N/A'}</td>
        <td>${order.quantity || 1}</td>
        <td><span class="order-type-badge ${orderTypeClass}">${orderTypeText}</span></td>
        <td>${order.fabrication_start ? formatDate(order.fabrication_start) : 'N/A'}</td>
        <td>${order.fabrication_end ? formatDate(order.fabrication_end) : 'N/A'}</td>
        <td>${order.fabrication_staff_name || 'Unassigned'}</td>
        <td>
            <div class="table-progress">
                <div class="table-progress-bar">
                    <div class="table-progress-fill" style="width: ${order.progress || 0}%"></div>
                </div>
                <span class="table-progress-text">${order.progress || 0}%</span>
            </div>
        </td>
        <td><span class="table-status-badge ${statusClass}">${statusText}</span></td>
        <td>
            <div class="action-dropdown">
                <button class="action-btn" onclick="toggleActionMenu(this)">Actions</button>
                <div class="action-menu">
                    <div class="action-menu-item" onclick="showOrderDetails(${order.order_id})">View Details</div>
                    <div class="action-menu-item" onclick="editOrderProgress(${order.order_id})">Edit Progress</div>
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
                renderOrderDetailsModal();
                openOrderDetailsModal();
            } else {
                alert('Error loading order details: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order details');
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
                        <div class="info-label">Order Type</div>
                        <div class="info-value">${order.order_type}</div>
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
                        <div class="item-row">
                            <div>${item.product_name} x ${item.quantity}</div>
                            <div>₱${parseFloat(item.subtotal).toFixed(2)}</div>
                        </div>
                    `).join('')}
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
                    <div class="form-group">
                        <label>Actual Start Date</label>
                        <input type="date" id="actual-start-date" value="${order.fabrication.actual_start_date || ''}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Actual End Date</label>
                        <input type="date" id="actual-end-date" value="${order.fabrication.actual_end_date || ''}">
                    </div>
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
                    <label>Progress: <span id="progress-value">${order.fabrication.progress || 0}</span>%</label>
                    <div class="progress-slider-container">
                        <input type="range" min="0" max="100" value="${order.fabrication.progress || 0}" 
                               class="progress-slider" id="fabrication-progress" 
                               disabled
                               oninput="document.getElementById('progress-value').textContent = this.value">
                    </div>
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
            
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="saveOrderDetails()">Save Changes</button>
                <button class="modal-btn modal-btn-secondary" onclick="closeOrderDetailsModal()">Cancel</button>
                <button class="modal-btn modal-btn-success" onclick="markComplete(${order.order_id})">Mark Complete</button>
            </div>
        `;
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
    
    // Map fabrication status to order status
    const statusMap = {
        'Queued': 'Approved',
        'In Progress': 'In Fabrication',
        'Quality Check': 'In Fabrication',
        'Ready': 'Ready for Installation',
        'Completed': 'Completed'
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
    formData.append('actual_end_date', document.getElementById('actual-end-date')?.value || '');
    formData.append('staff_id', document.getElementById('fabrication-staff')?.value || '');
    formData.append('notes', document.getElementById('fabrication-notes')?.value || '');
    formData.append('quality_check_notes', document.getElementById('quality-check-notes')?.value || '');
    formData.append('issues', document.getElementById('fabrication-issues')?.value || '');
    
    fetch(`${baseUrl}AdminCon/update_fabrication_progress`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order updated successfully');
            closeOrderDetailsModal();
            loadFabricationQueue();
        } else {
            alert('Error updating order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order');
    });
}

// ======================
// HELPER FUNCTIONS
// ======================

function updateOrderStatus(orderId, newStatus) {
    // Map queue status to order status and fabrication status with progress
    const statusMap = {
        'queued': { status: 'Approved', fabrication_status: 'Queued', progress: 0 },
        'in-progress': { status: 'In Fabrication', fabrication_status: 'In Progress', progress: 25 },
        'quality-check': { status: 'In Fabrication', fabrication_status: 'Quality Check', progress: 50 },
        'ready': { status: 'Ready for Installation', fabrication_status: 'Ready', progress: 75 },
        'completed': { status: 'Completed', fabrication_status: 'Completed', progress: 100 }
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
            alert('Order moved to quality check');
            loadFabricationQueue();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error moving order');
    });
}

function markComplete(orderId) {
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
            alert('Order marked as complete');
            closeOrderDetailsModal();
            loadFabricationQueue();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error marking order as complete');
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
    }
}

function updateFoundText() {
    const foundText = document.querySelector('.found-text');
    if (foundText) {
        const count = ordersData.length;
        foundText.textContent = `(${count} ${count === 1 ? 'order' : 'orders'})`;
    }
}
