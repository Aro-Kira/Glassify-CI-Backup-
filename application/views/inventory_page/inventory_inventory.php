<!-- Orders -->
<section class="order-list-section">
  <div class="section-header">
    <h2>Inventory</h2>

    <div class="inventory-stats">
      <div class="stat-card">
        <p class="stat-title">Total Items</p>
        <p class="stat-value" id="totalItemsCount">0</p>
      </div>
      <div class="stat-card">
        <p class="stat-title">Low Stocks Alerts</p>
        <p class="stat-value" id="lowStockCount">0</p>
      </div>
      <div class="stat-card">
        <p class="stat-title">New Items</p>
        <p class="stat-value" id="newItemsCount">0</p>
      </div>
      <div class="stat-card">
        <p class="stat-title">Recent Requests</p>
        <p class="stat-value" id="recentRequestsCount">0</p>
      </div>
    </div>
  </div>

  <div class="order-tabs">
    <h2>List of Items</h2>
  </div>

  <div class="controls-container">
    <div class="filters">
      <!-- Search -->
      <input type="text" id="searchInput" placeholder="Filter by name or item id" class="search-input">

      <!-- Category -->
      <label class="filter-label">Category:</label>
      <select id="categoryFilter" class="filter-select">
        <option value="all">All</option>
        <option value="door">Door</option>
        <option value="mirror">Mirror</option>
        <option value="partition">Partition</option>
        <option value="railings">Railings</option>
        <option value="window">Window</option>
      </select>

      <!-- Status -->
      <select id="statusFilter" class="filter-select">
        <option value="">Status</option>
        <option value="in-stock">In Stock</option>
        <option value="low-stock">Low Stock</option>
        <option value="out-of-stock">Out of Stock</option>
      </select>
    </div>

    <!-- Add Button -->
    <button class="add-btn" id="addItemBtn">+ Add New Item</button>
  </div>

    <?php
    // Inventory Page removed — deprecated. Redirect to admin dashboard.
    header('Location: ' . base_url('admin-dashboard'));
    exit;
    ?>
            <option value="railings">Railings</option>
            <option value="window">Window</option>
          </select>
        </div>

        <div class="form-group">
          <label>Unit of Measure:</label>
          <select id="itemUnit" class="input-text" required>
            <option value="" disabled selected>Select Unit</option>
            <option value="sqm">sqm</option>
            <option value="pcs">pcs</option>
            <option value="sets">sets</option>
            <option value="meter">meter</option>
            <option value="sheets">sheets</option>
            <option value="pieces">pieces</option>
          </select>
        </div>

        <div class="form-group">
          <label>Initial Stock:</label>
          <input type="number" id="initialStock" class="input-text" placeholder="Quantity to add initially" value="0" required />
        </div>

        <div class="form-group">
          <label>Min. Threshold:</label>
          <input type="number" id="minThreshold" class="input-text" placeholder="Minimum stock level for alert" value="10" />
        </div>

        <div class="popup-actions">
          <button type="submit" class="save-btn">Create Item</button>
          <button type="button" class="cancel-btn" id="cancelAddBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Item ID</th>
          <th>Name</th>
          <th>Category</th>
          <th>In Stock</th>
          <th>Unit</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <tr>
          <td colspan="7" style="text-align: center; padding: 20px;">Loading items...</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="pagination">
    <span id="paginationInfo">Showing 0 items</span>
    <div class="rows-per-page">
      <label for="rowsPerPageSelect">Rows per page:</label>
      <select id="rowsPerPageSelect">
        <option value="5" selected>5</option>
        <option value="10">10</option>
        <option value="25">25</option>
      </select>
    </div>
    <div class="pagination-controls">
      <button id="prevPage"><i class="fas fa-chevron-left"></i></button>
      <button class="active" id="page1">1</button>
      <button id="nextPage"><i class="fas fa-chevron-right"></i></button>
    </div>
  </div>

  <!-- Action menu (reusable) -->
  <div id="actionMenu" class="action-menu hidden">
    <ul>
      <li><a href="#" data-action="manage">Manage Stock</a></li>
      <li><a href="#" data-action="edit">Edit Item</a></li>
      <li><a href="#" data-action="delete">Delete Item</a></li>
    </ul>
  </div>

  <!-- Manage Stock Popup -->
  <div id="managePopup" class="popup-overlay">
    <div class="popup">
      <button class="close-btn" id="closeManageBtn">&times;</button>
      <h3 id="manageItemName">Item Name</h3>

      <form id="manageStockForm">
        <input type="hidden" id="manageItemId" />
        <div class="form-group">
          <label>Current stock:</label>
          <input type="text" id="currentStock" class="input-text" readonly />
        </div>

        <div class="form-group">
          <label>Add Stock:</label>
          <input type="number" id="addStock" class="input-text" placeholder="Quantity" min="0" />
        </div>

        <div class="form-group">
          <label>Remove Stock:</label>
          <input type="number" id="removeStock" class="input-text" placeholder="Quantity" min="0" />
        </div>

        <div class="form-group">
          <label>Min. Threshold:</label>
          <input type="number" id="manageMinThreshold" class="input-text" />
        </div>

        <div class="form-group">
          <label class="section-label">Reason</label>
          <textarea id="stockReason" class="input-text textarea" placeholder="Reason for stock change"></textarea>
        </div>

        <div class="popup-actions">
          <button type="submit" class="save-btn">Save Changes</button>
          <button type="button" class="cancel-btn" id="cancelManageBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Item Popup -->
  <div id="editPopup" class="popup-overlay">
    <div class="popup">
      <button class="close-btn" id="closeEditBtn">&times;</button>
      <h3>Edit Item</h3>

      <form id="editItemForm">
        <input type="hidden" id="editItemId" />
        <div class="form-group">
          <label>Item Name:</label>
          <input type="text" id="editItemName" class="input-text" required />
        </div>

        <div class="form-group">
          <label>Category:</label>
          <select id="editItemCategory" class="input-text">
            <option value="door">Door</option>
            <option value="mirror">Mirror</option>
            <option value="partition">Partition</option>
            <option value="railings">Railings</option>
            <option value="window">Window</option>
          </select>
        </div>

        <div class="form-group">
          <label>Unit of Measure:</label>
          <select id="editItemUnit" class="input-text">
            <option value="sqm">sqm</option>
            <option value="pcs">pcs</option>
            <option value="sets">sets</option>
            <option value="meter">meter</option>
            <option value="sheets">sheets</option>
            <option value="pieces">pieces</option>
          </select>
        </div>

        <div class="form-group">
          <label>Item ID:</label>
          <input type="text" id="editItemCode" class="input-text" readonly />
        </div>

        <div class="popup-actions">
          <button type="submit" class="save-btn">Save Changes</button>
          <button type="button" class="cancel-btn" id="cancelEditBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Item Popup -->
  <div id="deletePopup" class="popup-overlay">
    <div class="popup delete-popup">
      <button class="close-btn" id="closeDeleteBtn">&times;</button>
      <h3><span class="error-icon">!</span> Delete Item?</h3>

      <p><strong>Item ID:</strong> <span id="deleteItemCode"></span></p>
      <p><strong>Item name:</strong> <span id="deleteItemName"></span></p>
      <p class="delete-msg" id="deleteMessage"></p>

      <div class="popup-actions">
        <button class="delete-btn" id="confirmDeleteBtn">Delete Item</button>
        <button class="cancel-btn" id="cancelDeleteBtn">Cancel</button>
      </div>
    </div>
  </div>
</section>

<!-- Only load scripts that exist and are needed -->
<script src="<?php echo base_url('assets/js/order-status.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/includes/sidebar.js'); ?>"></script>
<!-- Main inventory functionality is in the script below -->

<script>
// ============================================
// INVENTORY MANAGEMENT JAVASCRIPT
// ============================================

(function() {
    'use strict';
    
    // Get base URL from CodeIgniter
    const baseUrl = '<?php echo base_url(); ?>';
    let currentItemId = null;
    let allItems = [];
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Format date
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: '2-digit', 
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Load statistics from API
    function loadStatistics() {
        fetch(baseUrl + 'api/inventory/get_statistics')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    document.getElementById('totalItemsCount').textContent = data.data.totalItems || 0;
                    document.getElementById('lowStockCount').textContent = data.data.lowStockAlerts || 0;
                    document.getElementById('newItemsCount').textContent = data.data.newItems || 0;
                    document.getElementById('recentRequestsCount').textContent = data.data.recentRequests || 0;
                }
            })
            .catch(error => {
                console.error('Error loading statistics:', error);
            });
    }
    
    // Load items from API
    function loadItems() {
        fetch(baseUrl + 'api/inventory/get_items')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('tableBody');
                if (!tbody) return;
                
                if (data.success && data.data && data.data.length > 0) {
                    allItems = data.data;
                    tbody.innerHTML = '';
                    
                    data.data.forEach((item, index) => {
                        const row = document.createElement('tr');
                        row.setAttribute('data-item-id', item.InventoryItemID || item.item_id);
                        
                        // Determine stock display
                        const stock = item.InStock || item.stock_quantity || 0;
                        const minThreshold = item.min_threshold || 10;
                        let stockDisplay = '<span class="stock-number">' + stock + '</span>';
                        
                        if (stock === 0) {
                            stockDisplay += '<span class="badge badge-out-stock">Out of Stock</span>';
                        } else if (stock < minThreshold) {
                            stockDisplay += '<span class="low-stock-indicator">Low Stock</span>';
                        }
                        
                        // Item name
                        const itemName = item.ItemName || item.name || 'N/A';
                        const itemCode = item.ItemCode || item.item_code || 'N/A';
                        const category = item.Category || item.category || 'N/A';
                        const unit = item.Unit || item.unit || 'N/A';
                        
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${escapeHtml(itemCode)}</td>
                            <td>${escapeHtml(itemName)}</td>
                            <td>${escapeHtml(category)}</td>
                            <td>${stockDisplay}</td>
                            <td>${escapeHtml(unit)}</td>
                            <td>
                                <div class="actions-menu">
                                    <button class="actions-btn" data-item-id="${item.InventoryItemID || item.item_id}">⋮</button>
                                    <div class="actions-dropdown">
                                        <a href="#" data-action="manage" data-item-id="${item.InventoryItemID || item.item_id}">
                                            <i class="fas fa-chart-bar"></i>
                                            <span>Manage Stock</span>
                                        </a>
                                        <a href="#" data-action="edit" data-item-id="${item.InventoryItemID || item.item_id}">
                                            <i class="fas fa-pencil-alt"></i>
                                            <span>Edit Item</span>
                                        </a>
                                        <a href="#" data-action="delete" data-item-id="${item.InventoryItemID || item.item_id}">
                                            <i class="fas fa-trash"></i>
                                            <span>Delete Item</span>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        `;
                        
                        tbody.appendChild(row);
                    });
                    
                    // Update pagination info
                    document.getElementById('paginationInfo').textContent = `Showing 1-${data.data.length} of ${data.data.length} items`;
                    
                    // Attach event listeners
                    attachActionListeners();
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No items found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading items:', error);
                const tbody = document.getElementById('tableBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #f44336;">Error loading items</td></tr>';
                }
            });
    }
    
    // Attach action listeners
    function attachActionListeners() {
        // Action buttons
        document.querySelectorAll('.actions-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const menu = this.closest('.actions-menu');
                const dropdown = menu.querySelector('.actions-dropdown');
                
                // Close other menus
                document.querySelectorAll('.actions-menu').forEach(m => {
                    if (m !== menu) {
                        m.classList.remove('active');
                        const otherDropdown = m.querySelector('.actions-dropdown');
                        if (otherDropdown) {
                            otherDropdown.classList.remove('show');
                        }
                    }
                });
                
                // Toggle current menu
                menu.classList.toggle('active');
                dropdown.classList.toggle('show');
            });
        });
        
        // Action links
        document.querySelectorAll('.actions-dropdown a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.getAttribute('data-action');
                const itemId = this.getAttribute('data-item-id');
                const row = this.closest('tr');
                const menu = this.closest('.actions-menu');
                const dropdown = this.closest('.actions-dropdown');
                
                // Close menu
                menu.classList.remove('active');
                dropdown.classList.remove('show');
                
                if (action === 'manage') {
                    openManageStock(itemId, row);
                } else if (action === 'edit') {
                    openEditItem(itemId, row);
                } else if (action === 'delete') {
                    openDeleteItem(itemId, row);
                }
            });
        });
    }
    
    // Open manage stock popup
    function openManageStock(itemId, row) {
        const item = allItems.find(i => (i.InventoryItemID || i.item_id) == itemId);
        if (!item) return;
        
        currentItemId = itemId;
        document.getElementById('manageItemId').value = itemId;
        document.getElementById('manageItemName').textContent = item.ItemName || item.name;
        document.getElementById('currentStock').value = `${item.InStock || item.stock_quantity || 0} ${item.Unit || item.unit || ''}`;
        document.getElementById('manageMinThreshold').value = item.min_threshold || 10;
        document.getElementById('addStock').value = '';
        document.getElementById('removeStock').value = '';
        document.getElementById('stockReason').value = '';
        
        document.getElementById('managePopup').classList.add('active');
    }
    
    // Open edit item popup
    function openEditItem(itemId, row) {
        const item = allItems.find(i => (i.InventoryItemID || i.item_id) == itemId);
        if (!item) return;
        
        currentItemId = itemId;
        document.getElementById('editItemId').value = itemId;
        document.getElementById('editItemName').value = item.ItemName || item.name;
        document.getElementById('editItemCategory').value = item.Category || item.category;
        document.getElementById('editItemUnit').value = item.Unit || item.unit;
        document.getElementById('editItemCode').value = item.ItemCode || item.item_code;
        
        document.getElementById('editPopup').classList.add('active');
    }
    
    // Open delete item popup
    function openDeleteItem(itemId, row) {
        const item = allItems.find(i => (i.InventoryItemID || i.item_id) == itemId);
        if (!item) return;
        
        currentItemId = itemId;
        document.getElementById('deleteItemCode').textContent = item.ItemCode || item.item_code;
        document.getElementById('deleteItemName').textContent = item.ItemName || item.name;
        document.getElementById('deleteMessage').textContent = `Are you sure you want to delete ${item.ItemName || item.name} from the inventory? This action cannot be undone.`;
        
        document.getElementById('deletePopup').classList.add('active');
    }
    
    // Close popups
    function closePopups() {
        document.getElementById('addItemPopup').classList.remove('active');
        document.getElementById('managePopup').classList.remove('active');
        document.getElementById('editPopup').classList.remove('active');
        document.getElementById('deletePopup').classList.remove('active');
    }
    
    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Load data
        loadStatistics();
        loadItems();
        
        // Add item button
        document.getElementById('addItemBtn').addEventListener('click', function() {
            document.getElementById('addItemPopup').classList.add('active');
        });
        
        // Close buttons
        document.getElementById('closeAddBtn').addEventListener('click', closePopups);
        document.getElementById('cancelAddBtn').addEventListener('click', closePopups);
        document.getElementById('closeManageBtn').addEventListener('click', closePopups);
        document.getElementById('cancelManageBtn').addEventListener('click', closePopups);
        document.getElementById('closeEditBtn').addEventListener('click', closePopups);
        document.getElementById('cancelEditBtn').addEventListener('click', closePopups);
        document.getElementById('closeDeleteBtn').addEventListener('click', closePopups);
        document.getElementById('cancelDeleteBtn').addEventListener('click', closePopups);
        
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
        
        // Form submissions
        document.getElementById('addItemForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                itemName: document.getElementById('itemName').value,
                itemCategory: document.getElementById('itemCategory').value,
                itemUnit: document.getElementById('itemUnit').value,
                initialStock: parseInt(document.getElementById('initialStock').value) || 0,
                minThreshold: parseInt(document.getElementById('minThreshold').value) || 10
            };
            
            fetch(baseUrl + 'api/inventory/add_item', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Item added successfully!', 'success');
                    closePopups();
                    loadItems();
                    loadStatistics();
                    document.getElementById('addItemForm').reset();
                } else {
                    showToast((data.message || 'Failed to add item'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding item. Please try again.', 'error');
            });
        });
        
        // Manage stock form
        document.getElementById('manageStockForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                addStock: parseInt(document.getElementById('addStock').value) || 0,
                removeStock: parseInt(document.getElementById('removeStock').value) || 0,
                minThreshold: parseInt(document.getElementById('manageMinThreshold').value) || 10,
                reason: document.getElementById('stockReason').value
            };
            
            fetch(baseUrl + 'api/inventory/manage_stock/' + currentItemId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Stock updated successfully!', 'success');
                    closePopups();
                    loadItems();
                    loadStatistics();
                } else {
                    showToast((data.message || 'Failed to update stock'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating stock. Please try again.', 'error');
            });
        });
        
        // Edit item form
        document.getElementById('editItemForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                itemName: document.getElementById('editItemName').value,
                itemCategory: document.getElementById('editItemCategory').value,
                itemUnit: document.getElementById('editItemUnit').value
            };
            
            fetch(baseUrl + 'api/inventory/update_item/' + currentItemId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Item updated successfully!', 'success');
                    closePopups();
                    loadItems();
                    loadStatistics();
                } else {
                    showToast((data.message || 'Failed to update item'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating item. Please try again.', 'error');
            });
        });
        
        // Delete item
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (!currentItemId) return;
            
            showConfirmModal('Are you sure you want to delete this item? This action cannot be undone.', () => {
                fetch(baseUrl + 'api/inventory/delete_item/' + currentItemId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Item deleted successfully!', 'success');
                    closePopups();
                    loadItems();
                    loadStatistics();
                } else {
                    showToast((data.message || 'Failed to delete item'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error deleting item. Please try again.', 'error');
            });
            });
        });
        
        // Click outside to close popups
        document.querySelectorAll('.popup-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    closePopups();
                }
            });
        });
        
        // Click outside to close action dropdowns
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.actions-menu')) {
                document.querySelectorAll('.actions-menu').forEach(menu => {
                    menu.classList.remove('active');
                    const dropdown = menu.querySelector('.actions-dropdown');
                    if (dropdown) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        });
    });
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        loadStatistics();
    }, 30000);
    
})();
</script>
