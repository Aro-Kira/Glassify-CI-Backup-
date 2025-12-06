<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlassWorth BUILDERS - Inventory</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="hamburger-menu" id="hamburgerMenu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="top-bar-center">
            <h1 class="top-bar-title">Inventory</h1>
        </div>
        <div class="top-bar-right">
            <div class="icon-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="icon-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <div class="logo-container">
                    <svg class="logo-window" width="140" height="90" viewBox="0 0 140 90" xmlns="http://www.w3.org/2000/svg">
                        <!-- Window frame with 3D perspective (viewed from lower-left angle) -->
                        <!-- Top horizontal bar -->
                        <line x1="8" y1="18" x2="132" y2="22" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        <!-- Bottom horizontal bar -->
                        <line x1="12" y1="72" x2="136" y2="76" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        <!-- Left vertical bar -->
                        <line x1="8" y1="18" x2="12" y2="72" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        <!-- Right vertical bar -->
                        <line x1="132" y1="22" x2="136" y2="76" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        <!-- Middle vertical divider -->
                        <line x1="70" y1="20" x2="74" y2="74" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                    <div class="logo-text-overlay">
                        <div class="logo-main">GlassWorth</div>
                        <div class="logo-sub">BUILDERS</div>
                    </div>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <div class="nav-text-item">General</div>
                <a href="inventory.php" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                        <path d="M9 9h6v6H9z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Inventory</span>
                </a>
                <a href="products.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Products</span>
                </a>
                <a href="reports.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <line x1="3" y1="21" x2="3" y2="9" stroke="currentColor" stroke-width="2"/>
                        <line x1="9" y1="21" x2="9" y2="13" stroke="currentColor" stroke-width="2"/>
                        <line x1="15" y1="21" x2="15" y2="5" stroke="currentColor" stroke-width="2"/>
                        <line x1="21" y1="21" x2="21" y2="17" stroke="currentColor" stroke-width="2"/>
                        <line x1="3" y1="9" x2="21" y2="9" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Reports</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1 class="page-title">Inventory</h1>
            
            <!-- Summary Cards -->
            <div class="dashboard-cards">
                <div class="card card-total-items">
                    <div class="card-number" id="statTotalItems">0</div>
                    <div class="card-label">Total Items</div>
                </div>
                <div class="card card-low-stock-alert">
                    <div class="card-number" id="statLowStock">0</div>
                    <div class="card-label">Low Stocks Alerts</div>
                </div>
                <div class="card card-new-items">
                    <div class="card-number" id="statNewItems">0</div>
                    <div class="card-label">New Items</div>
                </div>
                <div class="card card-recent-requests">
                    <div class="card-number" id="statRecentRequests">0</div>
                    <div class="card-label">Recent Requests</div>
                </div>
            </div>

            <!-- List of Items Section -->
            <div class="section-header">
                <h2 class="section-title">List of Items</h2>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-left">
                    <input type="text" class="filter-input" id="searchFilter" placeholder="Search by item ID, name..." oninput="applyFilters()">
                    <select class="filter-select" id="categoryFilter" onchange="applyFilters()">
                        <option value="All Categories">All Categories</option>
                        <option value="Building Materials">Building Materials</option>
                        <option value="Finishing">Finishing</option>
                        <option value="Lumber">Lumber</option>
                        <option value="Hardware">Hardware</option>
                        <option value="Glass">Glass</option>
                        <option value="Aluminum">Aluminum</option>
                    </select>
                    <select class="filter-select" id="statusFilter" onchange="applyFilters()">
                        <option value="All Status">All Status</option>
                        <option value="In Stock">In Stock</option>
                        <option value="Low Stock">Low Stock</option>
                        <option value="Out of Stock">Out of Stock</option>
                    </select>
                    <button type="button" class="btn-clear-filter" id="clearFilterBtn" onclick="clearFilters()" style="display:none;">Clear Filters</button>
                </div>
                <button type="button" class="btn-add-item" onclick="openAddItemModal()">+ Add New Item</button>
            </div>

            <!-- Items Table -->
            <div class="items-table-container">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>In Stock</th>
                            <th>Unit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <!-- Items will be loaded from database -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer / Pagination -->
            <div class="table-footer">
                <div class="table-info" id="tableInfo">Showing 1-10 of 255 items</div>
                <div class="pagination">
                    <select class="rows-select" id="rowsPerPage">
                        <option value="5">Rows per page: 5</option>
                        <option value="10" selected>Rows per page: 10</option>
                        <option value="20">Rows per page: 20</option>
                        <option value="50">Rows per page: 50</option>
                        <option value="100">Rows per page: 100</option>
                        <option value="999999">Show All</option>
                    </select>
                    <div class="pagination-controls" id="paginationControls">
                        <button class="pagination-btn" id="prevBtn">Previous</button>
                        <button class="pagination-btn active" data-page="1">1</button>
                        <button class="pagination-btn" data-page="2">2</button>
                        <button class="pagination-btn" data-page="3">3</button>
                        <span class="pagination-ellipsis">...</span>
                        <button class="pagination-btn" id="lastPageBtn" data-page="26">26</button>
                        <button class="pagination-btn" id="nextBtn">Next</button>
                    </div>
                </div>
            </div>

            <!-- Recent Activities Section -->
            <div class="section-header">
                <h2 class="section-title">Recent Activities</h2>
            </div>

            <div class="activities-table">
                <table>
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Item</th>
                            <th>Change</th>
                            <th>Description</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="activitiesTableBody">
                        <!-- Activities will be loaded from database -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Manage Stock Modal -->
    <div class="modal-overlay" id="manageStockModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="manageStockTitle">Tempered Glass 6mm</h2>
                <button class="modal-close" id="closeManageStockModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="manage-stock-form">
                    <div class="form-group">
                        <label for="currentStock">Current stock</label>
                        <input type="text" id="currentStock" readonly class="readonly-input" value="3 sheets">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="addStock">Add Stock</label>
                            <input type="number" id="addStock" name="addStock" min="0" placeholder="Quantity">
                        </div>
                        <div class="form-group">
                            <label for="removeStock">Remove Stock</label>
                            <input type="number" id="removeStock" name="removeStock" min="0" placeholder="Quantity">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="minThreshold">Min. Threshhold</label>
                        <input type="number" id="minThreshold" name="minThreshold" min="0" placeholder="10 sheets" value="10">
                    </div>
                    
                    <div class="form-group">
                        <label for="stockReason">Reason</label>
                        <textarea id="stockReason" name="stockReason" rows="3" placeholder="Enter reason for stock change...">Increased amount due to demand</textarea>
                    </div>
                    
                    <h3 class="manage-stock-heading">Manage Stock</h3>
                    
                    <div class="modal-actions manage-stock-actions">
                        <button type="button" class="btn-save" id="saveStockChanges">Save Changes</button>
                        <button type="button" class="btn-cancel" id="cancelStockChanges">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Item Modal -->
    <div class="modal-overlay" id="deleteItemModal">
        <div class="modal-content delete-modal-content">
            <div class="modal-header delete-modal-header">
                <div class="delete-header-left">
                    <div class="warning-icon">
                        <span>!</span>
                    </div>
                    <h2 id="deleteItemTitle">Delete Item?</h2>
                </div>
                <button class="modal-close" id="closeDeleteItemModal">&times;</button>
            </div>
            <div class="modal-body delete-modal-body">
                <div class="delete-item-info">
                    <div class="info-row">
                        <span class="info-label">Item ID:</span>
                        <span class="info-value" id="deleteItemId">GL-001</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Item name:</span>
                        <span class="info-value" id="deleteItemName">Tempered Glass 6mm</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Message:</span>
                    </div>
                    <div class="delete-message">
                        <em id="deleteMessageText">Are you sure you want to delete Tempered Glass 6mm from the inventory? This action cannot be undone.</em>
                    </div>
                </div>
                <div class="modal-actions delete-modal-actions">
                    <button type="button" class="btn-delete" id="confirmDeleteBtn">Delete Item</button>
                    <button type="button" class="btn-cancel" id="cancelDeleteBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal-overlay" id="editItemModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="editItemTitle">Edit Item</h2>
                <button class="modal-close" id="closeEditItemModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editItemForm">
                    <div class="form-group">
                        <label for="editItemName">Item Name *</label>
                        <input type="text" id="editItemName" name="editItemName" required placeholder="Enter item name">
                    </div>
                    <div class="form-group">
                        <label for="editItemCategory">Category *</label>
                        <select id="editItemCategory" name="editItemCategory" required>
                            <option value="">Select Category</option>
                            <option value="Building Materials">Building Materials</option>
                            <option value="Finishing">Finishing</option>
                            <option value="Lumber">Lumber</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Glass">Glass</option>
                            <option value="Aluminum">Aluminum</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editStockQuantity">Stock Quantity *</label>
                        <input type="number" id="editStockQuantity" name="editStockQuantity" min="0" required placeholder="Enter stock quantity">
                    </div>
                    <div class="form-group">
                        <label for="editItemUnit">Unit *</label>
                        <select id="editItemUnit" name="editItemUnit" required>
                            <option value="">Select Unit</option>
                            <option value="Bags">Bags</option>
                            <option value="Pieces">Pieces</option>
                            <option value="Cans">Cans</option>
                            <option value="Boxes">Boxes</option>
                            <option value="Sheets">Sheets</option>
                            <option value="Sets">Sets</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="editIsNewItem" name="editIsNewItem">
                            Mark as New Item
                        </label>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" id="cancelEditBtn">Cancel</button>
                        <button type="submit" class="btn-submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add New Item Modal -->
    <div class="modal-overlay" id="addItemModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Item</h2>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addItemForm" onsubmit="handleFormSubmit(event)">
                    <div class="form-group">
                        <label for="itemName">Item Name *</label>
                        <input type="text" id="itemName" name="itemName" required placeholder="Enter item name">
                    </div>
                    <div class="form-group">
                        <label for="itemCategory">Category *</label>
                        <select id="itemCategory" name="itemCategory" required>
                            <option value="">Select Category</option>
                            <option value="Building Materials">Building Materials</option>
                            <option value="Finishing">Finishing</option>
                            <option value="Lumber">Lumber</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Glass">Glass</option>
                            <option value="Aluminum">Aluminum</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="initialStock">Initial Stock Quantity *</label>
                        <input type="number" id="initialStock" name="initialStock" min="0" required placeholder="Enter stock quantity">
                    </div>
                    <div class="form-group">
                        <label for="itemUnit">Unit *</label>
                        <select id="itemUnit" name="itemUnit" required>
                            <option value="">Select Unit</option>
                            <option value="Bags">Bags</option>
                            <option value="Pieces">Pieces</option>
                            <option value="Cans">Cans</option>
                            <option value="Boxes">Boxes</option>
                            <option value="Sheets">Sheets</option>
                            <option value="Sets">Sets</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="isNewItem" name="isNewItem">
                            Mark as New Item
                        </label>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn-submit">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Global filter functions - must be defined before script.js uses them
        // Global function to apply filters
        function applyFilters() {
            console.log('applyFilters called');
            
            const categorySelect = document.getElementById('categoryFilter');
            const statusSelect = document.getElementById('statusFilter');
            const filterInput = document.querySelector('.filter-input');
            const itemsTable = document.querySelector('.items-table tbody');
            
            if (!itemsTable) {
                console.error('Items table not found!');
                return;
            }
            
            const searchTerm = filterInput ? filterInput.value.toLowerCase().trim() : '';
            const category = categorySelect ? categorySelect.value : 'All Categories';
            const status = statusSelect ? statusSelect.value : 'All Status';
            
            console.log('Filter values:', { searchTerm, category, status });
            
            const rows = itemsTable.querySelectorAll('tr');
            let visibleCount = 0;
            
            rows.forEach(function(row) {
                // Get cell values
                const itemIdCell = row.querySelector('td:nth-child(2)');
                const itemNameCell = row.querySelector('td:nth-child(3)');
                const itemCategoryCell = row.querySelector('td:nth-child(4)');
                const stockCell = row.querySelector('td:nth-child(5)');
                
                if (!itemIdCell || !itemNameCell || !itemCategoryCell || !stockCell) {
                    row.style.display = 'none';
                    return;
                }
                
                // Get text content, removing badges
                const itemId = itemIdCell.textContent.toLowerCase().trim();
                const itemNameSpan = itemNameCell.querySelector('span');
                const itemName = itemNameSpan ? itemNameSpan.textContent.toLowerCase().trim() : itemNameCell.textContent.toLowerCase().trim();
                const itemCategory = itemCategoryCell.textContent.trim();
                const stockText = stockCell.textContent.toLowerCase();
                
                // Check search match
                const matchesSearch = !searchTerm || itemId.includes(searchTerm) || itemName.includes(searchTerm);
                
                // Check category match
                const matchesCategory = category === 'All Categories' || category === '' || itemCategory === category;
                
                // Check status match
                let matchesStatus = true;
                if (status !== 'All Status' && status !== '') {
                    if (status === 'In Stock') {
                        // In Stock means no low stock or out of stock badges
                        matchesStatus = !stockText.includes('low stock') && !stockText.includes('out of stock');
                    } else if (status === 'Low Stock') {
                        matchesStatus = stockText.includes('low stock');
                    } else if (status === 'Out of Stock') {
                        matchesStatus = stockText.includes('out of stock');
                    }
                }
                
                // Show or hide row
                if (matchesSearch && matchesCategory && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update table info
            const tableInfo = document.querySelector('.table-info');
            if (tableInfo) {
                tableInfo.textContent = `Showing 1-${Math.min(visibleCount, 10)} of ${visibleCount} items`;
            }
            
            // Renumber rows
            let visibleIndex = 1;
            rows.forEach(function(row) {
                if (row.style.display !== 'none') {
                    const firstCell = row.querySelector('td:first-child');
                    if (firstCell) {
                        firstCell.textContent = visibleIndex;
                    }
                    visibleIndex++;
                }
            });
            
            // Update clear button
            const clearBtn = document.getElementById('clearFilterBtn');
            if (clearBtn) {
                const hasFilters = category !== 'All Categories' || status !== 'All Status' || searchTerm !== '';
                clearBtn.style.display = hasFilters ? 'inline-block' : 'none';
            }
            
            // Visual feedback for dropdowns
            if (categorySelect) {
                if (category !== 'All Categories') {
                    categorySelect.style.backgroundColor = '#e3f2fd';
                    categorySelect.style.borderColor = '#2196f3';
                } else {
                    categorySelect.style.backgroundColor = 'white';
                    categorySelect.style.borderColor = '#ddd';
                }
            }
            
            if (statusSelect) {
                if (status !== 'All Status') {
                    statusSelect.style.backgroundColor = '#e3f2fd';
                    statusSelect.style.borderColor = '#2196f3';
                } else {
                    statusSelect.style.backgroundColor = 'white';
                    statusSelect.style.borderColor = '#ddd';
                }
            }
            
            console.log('Filter applied. Visible items:', visibleCount);
        }

        // Clear all filters
        function clearFilters() {
            const categorySelect = document.getElementById('categoryFilter');
            const statusSelect = document.getElementById('statusFilter');
            const filterInput = document.querySelector('.filter-input');
            const clearBtn = document.getElementById('clearFilterBtn');
            
            if (categorySelect) categorySelect.value = 'All Categories';
            if (statusSelect) statusSelect.value = 'All Status';
            if (filterInput) filterInput.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            
            // Reset visual styles
            if (categorySelect) {
                categorySelect.style.backgroundColor = 'white';
                categorySelect.style.borderColor = '#ddd';
            }
            if (statusSelect) {
                statusSelect.style.backgroundColor = 'white';
                statusSelect.style.borderColor = '#ddd';
            }
            
            // Re-apply filters (which will show all items now)
            applyFilters();
        }

        // Pagination functionality
        let currentPage = 1;
        let rowsPerPage = 10;
        let paginationInitialized = false;

        function initPagination() {
            // Prevent duplicate initialization
            if (paginationInitialized) {
                updatePaginationDisplay();
                return;
            }
            
            // Remove any existing event listeners from script.js by cloning elements
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const rowsSelect = document.getElementById('rowsPerPage');
            const paginationControls = document.getElementById('paginationControls');
            
            // Clone and replace buttons to remove old listeners
            if (prevBtn) {
                const newPrevBtn = prevBtn.cloneNode(true);
                prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
            }
            if (nextBtn) {
                const newNextBtn = nextBtn.cloneNode(true);
                nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
            }
            // Store current rows per page value before cloning
            let currentRowsPerPageValue = rowsPerPage;
            if (rowsSelect) {
                const currentValue = rowsSelect.value;
                if (currentValue) {
                    currentRowsPerPageValue = parseInt(currentValue) || rowsPerPage;
                }
                const newRowsSelect = rowsSelect.cloneNode(true);
                rowsSelect.parentNode.replaceChild(newRowsSelect, rowsSelect);
            }
            
            // Get fresh references after cloning
            const freshPrevBtn = document.getElementById('prevBtn');
            const freshNextBtn = document.getElementById('nextBtn');
            const freshRowsSelect = document.getElementById('rowsPerPage');
            const freshPaginationControls = document.getElementById('paginationControls');
            
            // Restore rows per page value
            if (freshRowsSelect) {
                // Set the value, or use default if not found
                const optionExists = Array.from(freshRowsSelect.options).some(opt => parseInt(opt.value) === currentRowsPerPageValue);
                if (optionExists) {
                    freshRowsSelect.value = currentRowsPerPageValue;
                } else {
                    freshRowsSelect.value = 10; // Default to 10
                    currentRowsPerPageValue = 10;
                }
                rowsPerPage = currentRowsPerPageValue;
                
                // Rows per page handler
                freshRowsSelect.addEventListener('change', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const newRowsPerPage = parseInt(this.value);
                    if (!isNaN(newRowsPerPage) && newRowsPerPage > 0) {
                        rowsPerPage = newRowsPerPage;
                        currentPage = 1; // Reset to first page when changing rows per page
                    updatePaginationDisplay();
                    }
                });
            }

            // Previous button
            if (freshPrevBtn) {
                freshPrevBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (currentPage > 1) {
                        currentPage--;
                        updatePaginationDisplay();
                    }
                });
            }

            // Next button
            if (freshNextBtn) {
                freshNextBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const itemsTable = document.querySelector('.items-table tbody');
                    if (itemsTable) {
                        // Get ALL rows (not filtered by display style, since pagination controls visibility)
                        const allRows = Array.from(itemsTable.querySelectorAll('tr'));
                        // Filter out any error/empty rows
                        const validRows = allRows.filter(row => {
                            const firstCell = row.querySelector('td:first-child');
                            if (!firstCell) return false;
                            const cellText = firstCell.textContent.trim();
                            // Skip error messages or empty rows
                            if (cellText === '' || cellText.includes('Error') || row.querySelector('td[colspan]')) {
                                return false;
                            }
                            return true;
                        });
                        
                        const totalPages = Math.max(1, Math.ceil(validRows.length / rowsPerPage));
                        
                        if (currentPage < totalPages) {
                            currentPage++;
                            updatePaginationDisplay();
                        }
                    }
                });
            }

            // Page number buttons - use event delegation
            if (freshPaginationControls) {
                freshPaginationControls.addEventListener('click', function(e) {
                    if (e.target && e.target.classList.contains('pagination-btn') && e.target.dataset.page) {
                        e.preventDefault();
                        e.stopPropagation();
                        const pageNum = parseInt(e.target.dataset.page);
                        if (!isNaN(pageNum)) {
                            currentPage = pageNum;
                            updatePaginationDisplay();
                        }
                    }
                });
            }

            paginationInitialized = true;

            // Initial pagination update
            updatePaginationDisplay();
        }

        function updatePaginationDisplay() {
            const itemsTable = document.querySelector('.items-table tbody');
            if (!itemsTable) return;

            // Get all rows in the table
            const allRows = Array.from(itemsTable.querySelectorAll('tr'));
            
            // Filter out error/empty message rows - get ALL valid rows regardless of current display state
            const validRows = allRows.filter(row => {
                const firstCell = row.querySelector('td:first-child');
                // Check if it's a valid data row (has content in first cell and not an error message)
                if (!firstCell) return false;
                const cellText = firstCell.textContent.trim();
                // Skip error messages or empty rows
                if (cellText === '' || cellText.includes('Error') || row.querySelector('td[colspan]')) {
                    return false;
                }
                return true;
            });
            
            // For pagination, we need to consider ALL valid rows, not just currently visible ones
            // But we also need to respect filters (search, category, status)
            // Get rows that pass the current filters
            const searchInput = document.getElementById('searchFilter');
            const categorySelect = document.getElementById('categoryFilter');
            const statusSelect = document.getElementById('statusFilter');
            
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const category = categorySelect ? categorySelect.value : 'All Categories';
            const status = statusSelect ? statusSelect.value : 'All Status';
            
            // Filter rows based on search, category, and status filters
            const filteredRows = validRows.filter(row => {
                const itemIdCell = row.querySelector('td:nth-child(2)');
                const itemNameCell = row.querySelector('td:nth-child(3)');
                const itemCategoryCell = row.querySelector('td:nth-child(4)');
                const stockCell = row.querySelector('td:nth-child(5)');
                
                if (!itemIdCell || !itemNameCell || !itemCategoryCell || !stockCell) {
                    return false;
                }
                
                // Check search match
                const itemId = itemIdCell.textContent.toLowerCase().trim();
                const itemNameSpan = itemNameCell.querySelector('span');
                const itemName = itemNameSpan ? itemNameSpan.textContent.toLowerCase().trim() : itemNameCell.textContent.toLowerCase().trim();
                const matchesSearch = !searchTerm || itemId.includes(searchTerm) || itemName.includes(searchTerm);
                
                // Check category match
                const itemCategory = itemCategoryCell.textContent.trim();
                const matchesCategory = category === 'All Categories' || category === '' || itemCategory === category;
                
                // Check status match
                const stockText = stockCell.textContent.toLowerCase();
                let matchesStatus = true;
                if (status !== 'All Status' && status !== '') {
                    if (status === 'In Stock') {
                        matchesStatus = !stockText.includes('low stock') && !stockText.includes('out of stock');
                    } else if (status === 'Low Stock') {
                        matchesStatus = stockText.includes('low stock');
                    } else if (status === 'Out of Stock') {
                        matchesStatus = stockText.includes('out of stock');
                    }
                }
                
                return matchesSearch && matchesCategory && matchesStatus;
            });
            
            const totalItems = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(totalItems / rowsPerPage));

            // Ensure currentPage is within bounds
            if (currentPage > totalPages) {
                currentPage = totalPages;
            }
            if (currentPage < 1) {
                currentPage = 1;
            }

            // Calculate range for current page
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = Math.min(startIndex + rowsPerPage, totalItems);

            // Hide all rows first (both valid and invalid)
            allRows.forEach(row => {
                row.style.display = 'none';
            });

            // Show only rows for current page (from filtered rows)
            if (filteredRows.length > 0) {
                filteredRows.slice(startIndex, endIndex).forEach(row => {
                    row.style.display = 'table-row';
                });
            }

            // Update table info
            const tableInfo = document.getElementById('tableInfo');
            if (tableInfo) {
                if (totalItems === 0) {
                    tableInfo.textContent = 'Showing 0 items';
                } else {
                    tableInfo.textContent = `Showing ${startIndex + 1}-${endIndex} of ${totalItems} items`;
                }
            }

            // Update pagination buttons
            updatePaginationButtons(totalPages);

            // Renumber visible rows
            renumberPaginationRows();
        }

        function updatePaginationButtons(totalPages) {
            const controls = document.getElementById('paginationControls');
            if (!controls) return;

            // Get fresh references to Previous and Next buttons
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            
            // If buttons don't exist, pagination isn't ready yet
            if (!prevBtn || !nextBtn) {
                console.log('Pagination buttons not found, skipping update');
                return;
            }

            // Remove existing page number buttons (keep Previous and Next)
            const existingPageBtns = controls.querySelectorAll('.pagination-btn[data-page], .pagination-ellipsis');
            existingPageBtns.forEach(btn => btn.remove());

            // Create page buttons
            let pageButtonsHTML = '';

            if (totalPages <= 7) {
                // Show all pages if 7 or fewer
                for (let i = 1; i <= totalPages; i++) {
                    const activeClass = i === currentPage ? 'active' : '';
                    pageButtonsHTML += `<button class="pagination-btn ${activeClass}" data-page="${i}">${i}</button>`;
                }
            } else {
                // Show first page
                const firstActive = currentPage === 1 ? 'active' : '';
                pageButtonsHTML += `<button class="pagination-btn ${firstActive}" data-page="1">1</button>`;

                if (currentPage <= 4) {
                    // Show pages 2, 3, 4, 5
                    for (let i = 2; i <= Math.min(5, totalPages - 1); i++) {
                        const activeClass = i === currentPage ? 'active' : '';
                        pageButtonsHTML += `<button class="pagination-btn ${activeClass}" data-page="${i}">${i}</button>`;
                    }
                    if (totalPages > 5) {
                        pageButtonsHTML += '<span class="pagination-ellipsis">...</span>';
                    }
                    // Last page
                    if (totalPages > 1) {
                        const lastActive = currentPage === totalPages ? 'active' : '';
                        pageButtonsHTML += `<button class="pagination-btn ${lastActive}" data-page="${totalPages}">${totalPages}</button>`;
                    }
                } else if (currentPage >= totalPages - 3) {
                    // Show pages near the end
                    pageButtonsHTML += '<span class="pagination-ellipsis">...</span>';
                    for (let i = Math.max(totalPages - 4, 2); i <= totalPages; i++) {
                        const activeClass = i === currentPage ? 'active' : '';
                        pageButtonsHTML += `<button class="pagination-btn ${activeClass}" data-page="${i}">${i}</button>`;
                    }
                } else {
                    // Show pages around current page
                    pageButtonsHTML += '<span class="pagination-ellipsis">...</span>';
                    for (let i = currentPage - 1; i <= currentPage + 1; i++) {
                        const activeClass = i === currentPage ? 'active' : '';
                        pageButtonsHTML += `<button class="pagination-btn ${activeClass}" data-page="${i}">${i}</button>`;
                    }
                    pageButtonsHTML += '<span class="pagination-ellipsis">...</span>';
                    // Last page
                    const lastActive = currentPage === totalPages ? 'active' : '';
                    pageButtonsHTML += `<button class="pagination-btn ${lastActive}" data-page="${totalPages}">${totalPages}</button>`;
                }
            }

            // Insert page buttons before Next button
            if (nextBtn && nextBtn.parentNode) {
                nextBtn.insertAdjacentHTML('beforebegin', pageButtonsHTML);
            }

            // Update Previous/Next button states
            if (prevBtn) {
                prevBtn.disabled = currentPage === 1;
                prevBtn.style.opacity = currentPage === 1 ? '0.5' : '1';
                prevBtn.style.cursor = currentPage === 1 ? 'not-allowed' : 'pointer';
            }

            if (nextBtn) {
                nextBtn.disabled = currentPage >= totalPages;
                nextBtn.style.opacity = currentPage >= totalPages ? '0.5' : '1';
                nextBtn.style.cursor = currentPage >= totalPages ? 'not-allowed' : 'pointer';
            }
        }

        function renumberPaginationRows() {
            const itemsTable = document.querySelector('.items-table tbody');
            if (!itemsTable) return;

            const visibleRows = Array.from(itemsTable.querySelectorAll('tr')).filter(
                row => row.style.display !== 'none' && row.style.display !== ''
            );

            visibleRows.forEach((row, index) => {
                const firstCell = row.querySelector('td:first-child');
                if (firstCell) {
                    firstCell.textContent = (currentPage - 1) * rowsPerPage + index + 1;
                }
            });
        }

        // Override applyFilters to update pagination
        const originalApplyFilters = window.applyFilters;
        if (originalApplyFilters) {
            window.applyFilters = function() {
                originalApplyFilters();
                setTimeout(function() {
                    currentPage = 1; // Reset to first page when filters change
                    updatePaginationDisplay();
                }, 50);
            };
        }

        // Make pagination functions globally accessible
        window.updatePaginationDisplay = updatePaginationDisplay;
        Object.defineProperty(window, 'currentPage', {
            get: function() { return currentPage; },
            set: function(value) { currentPage = value; }
        });
        Object.defineProperty(window, 'rowsPerPage', {
            get: function() { return rowsPerPage; },
            set: function(value) { rowsPerPage = value; }
        });

        // Load statistics from database
        window.loadStatistics = function() {
            fetch('api/get_statistics.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Update Total Items
                        const totalItemsEl = document.getElementById('statTotalItems');
                        if (totalItemsEl) {
                            totalItemsEl.textContent = data.data.totalItems;
                        }
                        
                        // Update Low Stock Alerts
                        const lowStockEl = document.getElementById('statLowStock');
                        if (lowStockEl) {
                            lowStockEl.textContent = data.data.lowStockAlerts;
                        }
                        
                        // Update New Items
                        const newItemsEl = document.getElementById('statNewItems');
                        if (newItemsEl) {
                            newItemsEl.textContent = data.data.newItems;
                        }
                        
                        // Update Recent Requests
                        const recentRequestsEl = document.getElementById('statRecentRequests');
                        if (recentRequestsEl) {
                            recentRequestsEl.textContent = data.data.recentRequests;
                        }
                    } else {
                        console.error('Error loading statistics:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching statistics:', error);
                });
        }

        // Load activities from database
        window.loadActivities = function() {
            fetch('api/get_activities.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const activitiesTable = document.getElementById('activitiesTableBody');
                        if (!activitiesTable) return;
                        
                        // Clear existing rows
                        activitiesTable.innerHTML = '';
                        
                        if (data.data.length === 0) {
                            activitiesTable.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #666;">No activities found</td></tr>';
                            return;
                        }
                        
                        // Populate activities
                        data.data.forEach(activity => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${escapeHtml(activity.action || '')}</td>
                                <td>${escapeHtml(activity.item_name || '')}</td>
                                <td>${escapeHtml(activity.change_description || '')}</td>
                                <td>${escapeHtml(activity.description || '')}</td>
                                <td>${escapeHtml(activity.formatted_timestamp || activity.timestamp || '')}</td>
                            `;
                            activitiesTable.appendChild(row);
                        });
                    } else {
                        console.error('Error loading activities:', data.message);
                        const activitiesTable = document.getElementById('activitiesTableBody');
                        if (activitiesTable) {
                            activitiesTable.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #f44336;">Error loading activities</td></tr>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching activities:', error);
                    const activitiesTable = document.getElementById('activitiesTableBody');
                    if (activitiesTable) {
                        activitiesTable.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #f44336;">Error loading activities. Please check your connection.</td></tr>';
                    }
                });
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load items from database on page load
        window.loadItemsFromDatabase = function() {
            fetch('api/get_items.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                const itemsTable = document.querySelector('.items-table tbody');
                        if (!itemsTable) return;
                        
                        itemsTable.innerHTML = '';
                        
                        data.data.forEach((item, index) => {
                            const row = document.createElement('tr');
                            row.setAttribute('data-item-id', item.item_id);
                            
                            // Determine stock display and badges
                            let stockDisplay = '<span>' + item.stock_quantity + '</span>';
                            if (item.stock_quantity === 0) {
                                stockDisplay += '<span class="badge badge-out-stock">Out of Stock</span>';
                            } else if (item.stock_quantity < item.min_threshold) {
                                stockDisplay += '<span class="badge badge-low-stock">Low Stock</span>';
                            }
                            
                            // Determine name display with New badge if checked
                            let nameDisplay = '<span>' + item.name + '</span>';
                            if (item.is_new_item) {
                                nameDisplay += '<span class="badge badge-new">New</span>';
                            }
                            
                            row.innerHTML = `
                                <td>${index + 1}</td>
                                <td>${item.item_code}</td>
                                <td>${nameDisplay}</td>
                                <td>${item.category}</td>
                                <td>${stockDisplay}</td>
                                <td>${item.unit}</td>
                                <td>
                                    <div class="actions-menu">
                                        <button class="actions-btn">⋮</button>
                                        <div class="actions-dropdown">
                                            <a href="#">Manage Stock</a>
                                            <a href="#">Edit Item</a>
                                            <a href="#">Delete Item</a>
                                        </div>
                                    </div>
                                </td>
                            `;
                            
                            itemsTable.appendChild(row);
                            attachActionHandlers(row);
                        });
                        
                        // Reload statistics after loading items
                        loadStatistics();
                        
                        // Update counts (for backward compatibility with script.js functions)
                        if (typeof updateTotalItemsCount === 'function') {
                            updateTotalItemsCount();
                        }
                        if (typeof updateLowStockCount === 'function') {
                            updateLowStockCount();
                        }
                        if (typeof updateNewItemsCount === 'function') {
                            updateNewItemsCount();
                        }
                        if (typeof updateTableInfo === 'function') {
                            updateTableInfo();
                        }
                        
                        // Initialize pagination after loading items
                        // Wait a bit longer to ensure script.js has finished
                        setTimeout(function() {
                            // Reset initialization flag to allow re-initialization after loading from DB
                            paginationInitialized = false;
                initPagination();
                        }, 200);
                    } else {
                        console.error('Error loading items:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                    // Show error message but don't break the page
                    const itemsTable = document.querySelector('.items-table tbody');
                    if (itemsTable) {
                        itemsTable.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #f44336;">Error loading items from database. Please check your connection.</td></tr>';
                    }
                });
        }

        // Initialize pagination when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Load statistics first
            loadStatistics();
            
            // Load activities from database
            loadActivities();
            
            // Load items from database
            loadItemsFromDatabase();
            
            // Auto-refresh statistics every 30 seconds for real-time updates
            setInterval(function() {
                if (typeof window.loadStatistics === 'function') {
                    window.loadStatistics();
                }
            }, 30000); // Refresh every 30 seconds
            
            // Auto-refresh activities every 30 seconds for real-time updates
            setInterval(function() {
                if (typeof window.loadActivities === 'function') {
                    window.loadActivities();
                }
            }, 30000); // Refresh every 30 seconds
            
            // Check for URL parameters to apply filters
            const urlParams = new URLSearchParams(window.location.search);
            const filterType = urlParams.get('filter');
            
            // Wait a bit to ensure DOM is fully loaded and items are loaded
            setTimeout(function() {
                // Apply filters based on URL parameters
                if (filterType === 'lowstock') {
                    setTimeout(function() {
                        const statusFilter = document.getElementById('statusFilter');
                        if (statusFilter) {
                            statusFilter.value = 'Low Stock';
                            applyFilters();
                        }
                    }, 500);
                } else if (filterType === 'returned') {
                    // For returned items, search for items with "return" in name or description
                    setTimeout(function() {
                        const searchInput = document.getElementById('searchFilter');
                        if (searchInput) {
                            searchInput.value = 'return';
                            applyFilters();
                        }
                        }, 500);
                }
            }, 500);
        });
    </script>
    <script src="script.js"></script>
    <script>
        // Make openAddItemModal available globally
        function openAddItemModal() {
            const modal = document.getElementById('addItemModal');
            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
                const form = document.getElementById('addItemForm');
                if (form) form.reset();
            }
        }

        function closeAddItemModal() {
            const modal = document.getElementById('addItemModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
                const form = document.getElementById('addItemForm');
                if (form) form.reset();
            }
        }

        // Global function to apply filters
        function applyFilters() {
            console.log('applyFilters called');
            
            // Wait a bit for DOM to be ready if needed
            setTimeout(function() {
                const categorySelect = document.getElementById('categoryFilter');
                const statusSelect = document.getElementById('statusFilter');
                const filterInput = document.querySelector('.filter-input');
                const itemsTable = document.querySelector('.items-table tbody');
                
                if (!itemsTable) {
                    console.error('Items table not found!');
                    return;
                }
                
                const searchTerm = filterInput ? filterInput.value.toLowerCase().trim() : '';
                const category = categorySelect ? categorySelect.value : 'All Categories';
                const status = statusSelect ? statusSelect.value : 'All Status';
                
                console.log('Filter values:', { searchTerm, category, status });
                
                const rows = itemsTable.querySelectorAll('tr');
                let visibleCount = 0;
                
                rows.forEach(function(row) {
                    // Get cell values
                    const itemIdCell = row.querySelector('td:nth-child(2)');
                    const itemNameCell = row.querySelector('td:nth-child(3)');
                    const itemCategoryCell = row.querySelector('td:nth-child(4)');
                    const stockCell = row.querySelector('td:nth-child(5)');
                    
                    if (!itemIdCell || !itemNameCell || !itemCategoryCell || !stockCell) {
                        row.style.display = 'none';
                        return;
                    }
                    
                    // Get text content, removing badges
                    const itemId = itemIdCell.textContent.toLowerCase().trim();
                    const itemNameSpan = itemNameCell.querySelector('span');
                    const itemName = itemNameSpan ? itemNameSpan.textContent.toLowerCase().trim() : itemNameCell.textContent.toLowerCase().trim();
                    const itemCategory = itemCategoryCell.textContent.trim();
                    const stockText = stockCell.textContent.toLowerCase();
                    
                    // Check search match
                    const matchesSearch = !searchTerm || itemId.includes(searchTerm) || itemName.includes(searchTerm);
                    
                    // Check category match
                    const matchesCategory = category === 'All Categories' || category === '' || itemCategory === category;
                    
                    // Check status match
                    let matchesStatus = true;
                    if (status !== 'All Status' && status !== '') {
                        if (status === 'In Stock') {
                            // In Stock means no low stock or out of stock badges
                            matchesStatus = !stockText.includes('low stock') && !stockText.includes('out of stock');
                        } else if (status === 'Low Stock') {
                            matchesStatus = stockText.includes('low stock');
                        } else if (status === 'Out of Stock') {
                            matchesStatus = stockText.includes('out of stock');
                        }
                    }
                    
                    // Show or hide row
                    if (matchesSearch && matchesCategory && matchesStatus) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Update table info
                const tableInfo = document.querySelector('.table-info');
                if (tableInfo) {
                    const totalRows = itemsTable.querySelectorAll('tr').length;
                    tableInfo.textContent = `Showing 1-${Math.min(visibleCount, 10)} of ${visibleCount} items`;
                }
                
                // Renumber rows
                let visibleIndex = 1;
                rows.forEach(function(row) {
                    if (row.style.display !== 'none') {
                        const firstCell = row.querySelector('td:first-child');
                        if (firstCell) {
                            firstCell.textContent = visibleIndex;
                        }
                        visibleIndex++;
                    }
                });
                
                // Update clear button
                const clearBtn = document.getElementById('clearFilterBtn');
                if (clearBtn) {
                    const hasFilters = category !== 'All Categories' || status !== 'All Status' || searchTerm !== '';
                    clearBtn.style.display = hasFilters ? 'inline-block' : 'none';
                }
                
                // Visual feedback for search input
                const searchInput = document.getElementById('searchFilter');
                if (searchInput) {
                    if (searchTerm !== '') {
                        searchInput.style.borderColor = '#2196f3';
                        searchInput.style.backgroundColor = '#f5f5f5';
                    } else {
                        searchInput.style.borderColor = '#ddd';
                        searchInput.style.backgroundColor = 'white';
                    }
                }
                
                // Visual feedback for dropdowns
                if (categorySelect) {
                    if (category !== 'All Categories') {
                        categorySelect.style.backgroundColor = '#e3f2fd';
                        categorySelect.style.borderColor = '#2196f3';
                    } else {
                        categorySelect.style.backgroundColor = 'white';
                        categorySelect.style.borderColor = '#ddd';
                    }
                }
                
                if (statusSelect) {
                    if (status !== 'All Status') {
                        statusSelect.style.backgroundColor = '#e3f2fd';
                        statusSelect.style.borderColor = '#2196f3';
                    } else {
                        statusSelect.style.backgroundColor = 'white';
                        statusSelect.style.borderColor = '#ddd';
                    }
                }
                
                console.log('Filter applied. Visible items:', visibleCount);
                
                // Update pagination after filtering
                if (typeof updatePaginationDisplay === 'function') {
                    setTimeout(function() {
                        if (typeof currentPage !== 'undefined') {
                            currentPage = 1; // Reset to first page when filters change
                        }
                        updatePaginationDisplay();
                    }, 50);
                }
            }, 10);
        }

        // Clear all filters
        function clearFilters() {
            const categorySelect = document.getElementById('categoryFilter');
            const statusSelect = document.getElementById('statusFilter');
            const filterInput = document.querySelector('.filter-input');
            const clearBtn = document.getElementById('clearFilterBtn');
            
            if (categorySelect) categorySelect.value = 'All Categories';
            if (statusSelect) statusSelect.value = 'All Status';
            if (filterInput) filterInput.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            
            // Re-apply filters (which will show all items now)
            applyFilters();
        }

        // Global function to handle form submission
        function handleFormSubmit(event) {
            event.preventDefault();
            
            // Get form values
            const itemNameInput = document.getElementById('itemName');
            const itemCategoryInput = document.getElementById('itemCategory');
            const initialStockInput = document.getElementById('initialStock');
            const itemUnitInput = document.getElementById('itemUnit');
            const isNewItemInput = document.getElementById('isNewItem');

            if (!itemNameInput || !itemCategoryInput || !initialStockInput || !itemUnitInput) {
                alert('Form fields not found!');
                return false;
            }

            const itemName = itemNameInput.value.trim();
            const itemCategory = itemCategoryInput.value;
            const initialStock = initialStockInput.value;
            const itemUnit = itemUnitInput.value;
            const isNewItem = isNewItemInput ? isNewItemInput.checked : false;

            // Validate form
            if (!itemName || !itemCategory || !initialStock || !itemUnit) {
                alert('Please fill in all required fields');
                return false;
            }

            const stockNum = parseInt(initialStock) || 0;
            
            // Disable submit button to prevent double submission
            const submitBtn = event.target.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
            }
            
            // Prepare data to send to server
            const formData = {
                itemName: itemName,
                itemCategory: itemCategory,
                initialStock: stockNum,
                itemUnit: itemUnit,
                isNewItem: isNewItem
            };
            
            // Send AJAX request to save to database
            fetch('api/add_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Item';
                }
                
                if (data.success) {
            // Get the items table
            const itemsTable = document.querySelector('.items-table tbody');
            if (!itemsTable) {
                alert('Table not found!');
                return false;
            }
            
            // Get the next item number
            const existingRows = itemsTable.querySelectorAll('tr');
            const nextItemNum = existingRows.length + 1;
            
                    // Use the item_code from database response
                    const itemCode = data.data.item_code || 'ITM' + String(nextItemNum).padStart(3, '0');
            
            // Create new table row
            const newRow = document.createElement('tr');
                    newRow.setAttribute('data-item-id', data.data.item_id);
            
            // Determine stock display and badges
            let stockDisplay = '<span>' + stockNum + '</span>';
            if (stockNum === 0) {
                stockDisplay += '<span class="badge badge-out-stock">Out of Stock</span>';
            } else if (stockNum < 15) {
                stockDisplay += '<span class="badge badge-low-stock">Low Stock</span>';
            }

            // Determine name display with New badge if checked
            let nameDisplay = '<span>' + itemName + '</span>';
            if (isNewItem) {
                nameDisplay += '<span class="badge badge-new">New</span>';
            }
            
            newRow.innerHTML = `
                <td>${nextItemNum}</td>
                        <td>${itemCode}</td>
                <td>${nameDisplay}</td>
                <td>${itemCategory}</td>
                <td>${stockDisplay}</td>
                <td>${itemUnit}</td>
                <td>
                    <div class="actions-menu">
                        <button class="actions-btn">⋮</button>
                        <div class="actions-dropdown">
                            <a href="#">Manage Stock</a>
                            <a href="#">Edit Item</a>
                            <a href="#">Delete Item</a>
                        </div>
                    </div>
                </td>
            `;
            
            // Add row to table
            itemsTable.appendChild(newRow);
            
            // Attach action handlers to new row
            attachActionHandlers(newRow);
            
                    // Reload items from database to ensure consistency
                    // This ensures the new item appears with correct formatting
                    setTimeout(function() {
                        if (typeof window.loadItemsFromDatabase === 'function') {
                            window.loadItemsFromDatabase();
                        }
                        // Reload statistics after adding item
                        if (typeof window.loadStatistics === 'function') {
                            window.loadStatistics();
                        }
                        // Reload activities after adding item
                        if (typeof window.loadActivities === 'function') {
                            window.loadActivities();
                        }
                    }, 300);
            
            // Show notification
            if (typeof showNotification === 'function') {
                showNotification(`New item "${itemName}" added successfully!`, 'success');
            } else {
                alert(`New item "${itemName}" added successfully!`);
            }
            
            // Close modal
            closeAddItemModal();
                } else {
                    // Show error message
                    alert('Error: ' + (data.message || 'Failed to add item'));
                    if (typeof showNotification === 'function') {
                        showNotification('Error: ' + (data.message || 'Failed to add item'), 'error');
                    }
                }
            })
            .catch(error => {
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Item';
                }
                
                console.error('Error:', error);
                alert('Error saving item. Please check your database connection.');
                if (typeof showNotification === 'function') {
                    showNotification('Error saving item. Please check your database connection.', 'error');
                }
            });
            
            return false;
        }

        // Function to attach action handlers to a row
        function attachActionHandlers(row) {
            const actionsBtn = row.querySelector('.actions-btn');
            const actionsItems = row.querySelectorAll('.actions-dropdown a');
            
            if (actionsBtn) {
                actionsBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const menu = this.closest('.actions-menu');
                    const dropdown = menu.querySelector('.actions-dropdown');
                    const isActive = menu.classList.contains('active');
                    
                    document.querySelectorAll('.actions-menu').forEach(function(m) {
                        if (m !== menu) {
                            m.classList.remove('active');
                            const d = m.querySelector('.actions-dropdown');
                            if (d) d.classList.remove('show');
                        }
                    });
                    
                    if (isActive) {
                        menu.classList.remove('active');
                        dropdown.classList.remove('show');
                    } else {
                        menu.classList.add('active');
                        dropdown.classList.add('show');
                    }
                });
            }
            
            actionsItems.forEach(function(item) {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const menu = this.closest('.actions-menu');
                    const tableRow = menu.closest('tr');
                    const itemId = tableRow.querySelector('td:nth-child(2)').textContent.trim();
                    const nameCell = tableRow.querySelector('td:nth-child(3)');
                    const nameSpan = nameCell ? nameCell.querySelector('span') : null;
                    const itemName = nameSpan ? nameSpan.textContent.trim() : (nameCell ? nameCell.textContent.trim().replace(/New/g, '').trim() : '');
                    const action = this.textContent.trim();
                    
                    menu.classList.remove('active');
                    const dropdown = menu.querySelector('.actions-dropdown');
                    if (dropdown) dropdown.classList.remove('show');
                    
                    if (typeof handleManageStock === 'function' && action === 'Manage Stock') {
                        handleManageStock(itemId, itemName, tableRow);
                    } else if (typeof handleEditItem === 'function' && action === 'Edit Item') {
                        handleEditItem(itemId, itemName, tableRow);
                    } else if (typeof handleDeleteItem === 'function' && action === 'Delete Item') {
                        handleDeleteItem(itemId, itemName, tableRow);
                    }
                });
            });
        }

        // Manage Stock Modal Functions - make globally accessible
        window.openManageStockModal = function(itemName, currentStock, unit) {
            const modal = document.getElementById('manageStockModal');
            const title = document.getElementById('manageStockTitle');
            const currentStockInput = document.getElementById('currentStock');
            const addStockInput = document.getElementById('addStock');
            const removeStockInput = document.getElementById('removeStock');
            const minThresholdInput = document.getElementById('minThreshold');
            const reasonTextarea = document.getElementById('stockReason');
            
            if (modal && title) {
                // Update modal title with item name
                title.textContent = itemName;
                
                // Update current stock display
                if (currentStockInput) {
                    currentStockInput.value = currentStock + ' ' + unit;
                }
                
                // Get current threshold from row if available
                let currentThreshold = 15; // Default
                if (window.currentManageStockRow) {
                    // Try to get threshold from stock cell or default to 15
                    const stockCell = window.currentManageStockRow.querySelector('td:nth-child(5)');
                    const stockValue = stockCell ? parseInt(stockCell.querySelector('span')?.textContent || 0) : 0;
                    // If stock is low, threshold might be around 15
                    if (stockValue > 0 && stockValue < 15) {
                        currentThreshold = 15;
                    }
                }
                
                // Reset form
                if (addStockInput) addStockInput.value = '';
                if (removeStockInput) removeStockInput.value = '';
                if (minThresholdInput) {
                    minThresholdInput.value = currentThreshold;
                    minThresholdInput.placeholder = currentThreshold + ' ' + unit;
                }
                if (reasonTextarea) reasonTextarea.value = 'Increased amount due to demand';
                
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeManageStockModal = function() {
            const modal = document.getElementById('manageStockModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        };

        // Edit Item Modal Functions - make globally accessible
        window.openEditItemModal = function(itemId, itemName, category, stock, unit, isNewItem) {
            const modal = document.getElementById('editItemModal');
            const title = document.getElementById('editItemTitle');
            const nameInput = document.getElementById('editItemName');
            const categoryInput = document.getElementById('editItemCategory');
            const stockInput = document.getElementById('editStockQuantity');
            const unitInput = document.getElementById('editItemUnit');
            const isNewItemInput = document.getElementById('editIsNewItem');
            
            if (modal && title) {
                // Update modal title
                title.textContent = 'Edit Item';
                
                // Populate form fields with current values
                if (nameInput) nameInput.value = itemName;
                if (categoryInput) categoryInput.value = category;
                if (stockInput) stockInput.value = stock;
                if (unitInput) unitInput.value = unit;
                if (isNewItemInput) isNewItemInput.checked = isNewItem || false;
                
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeEditItemModal = function() {
            const modal = document.getElementById('editItemModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        };

        window.handleEditItemSubmit = function(event) {
            event.preventDefault();
            
            if (!window.currentEditItemRow) {
                alert('Row reference not found!');
                return false;
            }
            
            const nameInput = document.getElementById('editItemName');
            const categoryInput = document.getElementById('editItemCategory');
            const stockInput = document.getElementById('editStockQuantity');
            const unitInput = document.getElementById('editItemUnit');
            const isNewItemInput = document.getElementById('editIsNewItem');
            
            if (!nameInput || !categoryInput || !stockInput || !unitInput) {
                alert('Form fields not found!');
                return false;
            }
            
            const newName = nameInput.value.trim();
            const newCategory = categoryInput.value;
            const newStock = parseInt(stockInput.value) || 0;
            const newUnit = unitInput.value;
            const isNewItem = isNewItemInput ? isNewItemInput.checked : false;
            
            // Validate form
            if (!newName || !newCategory || !newUnit) {
                alert('Please fill in all required fields');
                return false;
            }
            
            const row = window.currentEditItemRow;
            
            // Update name cell
            const nameCell = row.querySelector('td:nth-child(3)');
            let nameDisplay = '<span>' + newName + '</span>';
            if (isNewItem) {
                nameDisplay += '<span class="badge badge-new">New</span>';
            }
            nameCell.innerHTML = nameDisplay;
            
            // Update category
            const categoryCell = row.querySelector('td:nth-child(4)');
            if (categoryCell) categoryCell.textContent = newCategory;
            
            // Update stock
            const stockCell = row.querySelector('td:nth-child(5)');
            let stockDisplay = '<span>' + newStock + '</span>';
            
            // Remove old stock badges
            const oldStockBadges = stockCell.querySelectorAll('.badge');
            oldStockBadges.forEach(function(badge) {
                badge.remove();
            });
            
            // Add appropriate stock badges
            if (newStock === 0) {
                stockDisplay += '<span class="badge badge-out-stock">Out of Stock</span>';
            } else if (newStock < 15) {
                stockDisplay += '<span class="badge badge-low-stock">Low Stock</span>';
            }
            stockCell.innerHTML = stockDisplay;
            
            // Update unit
            const unitCell = row.querySelector('td:nth-child(6)');
            if (unitCell) unitCell.textContent = newUnit;
            
            // Update counts
            if (typeof updateLowStockCount === 'function') {
                updateLowStockCount();
            }
            if (typeof updateNewItemsCount === 'function') {
                updateNewItemsCount();
            }
            
            // Close modal
            window.closeEditItemModal();
            
            // Reload statistics after editing item
            if (typeof window.loadStatistics === 'function') {
                window.loadStatistics();
            }
            
            // Reload activities after editing item
            if (typeof window.loadActivities === 'function') {
                window.loadActivities();
            }
            
            // Show success message
            if (typeof showNotification === 'function') {
                showNotification(`Item "${window.currentEditItemOriginalName}" updated successfully!`, 'success');
            } else {
                alert(`Item "${window.currentEditItemOriginalName}" updated successfully!`);
            }
            
            console.log(`Item edited: ${window.currentEditItemId} - ${window.currentEditItemOriginalName} → ${newName}`);
            
            return false;
        };

        // Delete Item Modal Functions - make globally accessible
        window.openDeleteItemModal = function(itemId, itemName) {
            const modal = document.getElementById('deleteItemModal');
            const itemIdSpan = document.getElementById('deleteItemId');
            const itemNameSpan = document.getElementById('deleteItemName');
            const messageText = document.getElementById('deleteMessageText');
            
            if (modal) {
                // Update modal content with item details
                if (itemIdSpan) itemIdSpan.textContent = itemId;
                if (itemNameSpan) itemNameSpan.textContent = itemName;
                if (messageText) {
                    messageText.textContent = `Are you sure you want to delete ${itemName} from the inventory? This action cannot be undone.`;
                }
                
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeDeleteItemModal = function() {
            const modal = document.getElementById('deleteItemModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        };

        window.confirmDeleteItem = function() {
            if (!window.currentDeleteItemRow) {
                alert('Row reference not found!');
                return;
            }
            
            const row = window.currentDeleteItemRow;
            const itemName = window.currentDeleteItemName || 'Item';
            const itemId = window.currentDeleteItemId || '';
            
            // Add fade out animation
            row.style.transition = 'opacity 0.3s ease';
            row.style.opacity = '0';
            
            // Close modal immediately
            window.closeDeleteItemModal();
            
            setTimeout(function() {
                row.remove();
                
                // Show success notification
                if (typeof showNotification === 'function') {
                    showNotification(`Item "${itemName}" has been deleted`, 'success');
                } else {
                    alert(`Item "${itemName}" has been deleted`);
                }
                
                // Update counts
                if (typeof updateTotalItemsCount === 'function') {
                    updateTotalItemsCount();
                }
                if (typeof updateLowStockCount === 'function') {
                    updateLowStockCount();
                }
                if (typeof updateNewItemsCount === 'function') {
                    updateNewItemsCount();
                }
                if (typeof updateTableInfo === 'function') {
                    updateTableInfo();
                }
                
                // Renumber rows
                if (typeof renumberRows === 'function') {
                    renumberRows();
                }
                
                // Update pagination if needed
                if (typeof updatePaginationDisplay === 'function') {
                    setTimeout(function() {
                        updatePaginationDisplay();
                    }, 50);
                }
                
                // Reload statistics after deleting item
                if (typeof window.loadStatistics === 'function') {
                    window.loadStatistics();
                }
                
                // Reload activities after deleting item
                if (typeof window.loadActivities === 'function') {
                    window.loadActivities();
                }
                
                console.log(`Item deleted: ${itemId} - ${itemName}`);
            }, 300);
        };

        window.saveStockChanges = function() {
            if (!window.currentManageStockRow) return;
            
            const addStockInput = document.getElementById('addStock');
            const removeStockInput = document.getElementById('removeStock');
            const minThresholdInput = document.getElementById('minThreshold');
            const reasonTextarea = document.getElementById('stockReason');
            
            const addAmount = addStockInput ? parseInt(addStockInput.value) || 0 : 0;
            const removeAmount = removeStockInput ? parseInt(removeStockInput.value) || 0 : 0;
            const minThreshold = minThresholdInput ? parseInt(minThresholdInput.value) || 0 : 0;
            const reason = reasonTextarea ? reasonTextarea.value.trim() : '';
            
            // Get current stock
            const stockCell = window.currentManageStockRow.querySelector('td:nth-child(5)');
            const stockSpan = stockCell.querySelector('span');
            const currentStockNum = stockSpan ? parseInt(stockSpan.textContent.trim()) || 0 : parseInt(stockCell.textContent.replace(/[^0-9]/g, '')) || 0;
            const unit = window.currentManageStockRow.querySelector('td:nth-child(6)').textContent.trim();
            
            // Calculate new stock
            let newStock = currentStockNum + addAmount - removeAmount;
            if (newStock < 0) newStock = 0;
            
            // Update stock display
            const badges = stockCell.querySelectorAll('.badge');
            let badgeHTML = '';
            badges.forEach(function(badge) {
                badgeHTML += badge.outerHTML;
            });
            
            stockCell.innerHTML = '<span>' + newStock + '</span>' + badgeHTML;
            
            // Update badges based on stock level and min threshold
            const stockSpanNew = stockCell.querySelector('span');
            const existingBadges = stockCell.querySelectorAll('.badge-low-stock, .badge-out-stock');
            existingBadges.forEach(function(badge) {
                badge.remove();
            });
            
            if (newStock === 0) {
                stockSpanNew.insertAdjacentHTML('afterend', '<span class="badge badge-out-stock">Out of Stock</span>');
            } else if (minThreshold > 0 && newStock < minThreshold) {
                stockSpanNew.insertAdjacentHTML('afterend', '<span class="badge badge-low-stock">Low Stock</span>');
            } else if (minThreshold === 0 && newStock < 15) {
                stockSpanNew.insertAdjacentHTML('afterend', '<span class="badge badge-low-stock">Low Stock</span>');
            }
            
            // Update counts
            if (typeof updateLowStockCount === 'function') {
                updateLowStockCount();
            }
            
            // Close modal
            window.closeManageStockModal();
            
            // Reload statistics after stock changes
            if (typeof window.loadStatistics === 'function') {
                window.loadStatistics();
            }
            
            // Reload activities after stock changes
            if (typeof window.loadActivities === 'function') {
                window.loadActivities();
            }
            
            // Show success message
            let message = `Stock updated for ${window.currentManageStockItemName}: ${newStock} ${unit}`;
            if (addAmount > 0) message += ` (+${addAmount})`;
            if (removeAmount > 0) message += ` (-${removeAmount})`;
            if (typeof showNotification === 'function') {
                showNotification(message, 'success');
            } else {
                alert(message);
            }
        };

        // Also attach event listeners when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Add Item Modal
            const closeBtn = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const addItemModal = document.getElementById('addItemModal');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', closeAddItemModal);
            }
            
            if (cancelBtn) {
                cancelBtn.addEventListener('click', closeAddItemModal);
            }
            
            if (addItemModal) {
                addItemModal.addEventListener('click', function(e) {
                    if (e.target === addItemModal) {
                        closeAddItemModal();
                    }
                });
            }

            // Manage Stock Modal
            const closeManageStockBtn = document.getElementById('closeManageStockModal');
            const cancelStockBtn = document.getElementById('cancelStockChanges');
            const saveStockBtn = document.getElementById('saveStockChanges');
            const manageStockModal = document.getElementById('manageStockModal');
            
            if (closeManageStockBtn) {
                closeManageStockBtn.addEventListener('click', window.closeManageStockModal);
            }
            
            if (cancelStockBtn) {
                cancelStockBtn.addEventListener('click', window.closeManageStockModal);
            }
            
            if (saveStockBtn) {
                saveStockBtn.addEventListener('click', window.saveStockChanges);
            }
            
            if (manageStockModal) {
                manageStockModal.addEventListener('click', function(e) {
                    if (e.target === manageStockModal) {
                        window.closeManageStockModal();
                    }
                });
            }

            // Edit Item Modal
            const editItemForm = document.getElementById('editItemForm');
            if (editItemForm) {
                editItemForm.addEventListener('submit', window.handleEditItemSubmit);
            }
            
            const closeEditItemBtn = document.getElementById('closeEditItemModal');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            const editItemModal = document.getElementById('editItemModal');
            
            if (closeEditItemBtn) {
                closeEditItemBtn.addEventListener('click', window.closeEditItemModal);
            }
            
            if (cancelEditBtn) {
                cancelEditBtn.addEventListener('click', window.closeEditItemModal);
            }
            
            if (editItemModal) {
                editItemModal.addEventListener('click', function(e) {
                    if (e.target === editItemModal) {
                        window.closeEditItemModal();
                    }
                });
            }

            // Delete Item Modal
            const closeDeleteItemBtn = document.getElementById('closeDeleteItemModal');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const deleteItemModal = document.getElementById('deleteItemModal');
            
            if (closeDeleteItemBtn) {
                closeDeleteItemBtn.addEventListener('click', window.closeDeleteItemModal);
            }
            
            if (cancelDeleteBtn) {
                cancelDeleteBtn.addEventListener('click', window.closeDeleteItemModal);
            }
            
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', window.confirmDeleteItem);
            }
            
            if (deleteItemModal) {
                deleteItemModal.addEventListener('click', function(e) {
                    if (e.target === deleteItemModal) {
                        window.closeDeleteItemModal();
                    }
                });
            }
        });
    </script>
</body>
</html>

