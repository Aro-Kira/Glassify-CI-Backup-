document.addEventListener('DOMContentLoaded', function() {
    // ===================== DATE PICKER =====================
    // Date filtering disabled as per requirements
    /*
    const dateFilter = document.getElementById('date-filter');
    const dateDisplayMonth = document.getElementById('date-display-month');
    const dateDisplayYear = document.getElementById('date-display-year');
    let selectedDate = null;
    let currentTab = 'pending';

    // Initialize date picker
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            selectedDate = this.value;
            if (selectedDate) {
                const date = new Date(selectedDate);
                dateDisplayMonth.textContent = date.toLocaleString('default', { month: 'short' });
                dateDisplayYear.textContent = date.getFullYear();
                filterOrdersByDate(selectedDate);
            } else {
                dateDisplayMonth.textContent = new Date().toLocaleString('default', { month: 'short' });
                dateDisplayYear.textContent = new Date().getFullYear();
                loadAllOrders();
            }
        });
    }
    */
    let currentTab = 'pending';

    // ===================== SEARCH FILTER =====================
    const searchBox = document.getElementById('product-search');
    if (searchBox) {
        searchBox.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            filterByProductName(searchTerm);
            updatePagination();
        });
    }

    // Filter orders by product name
    function filterByProductName(searchTerm) {
        const activeSection = document.querySelector('.order-section.active');
        if (!activeSection) return;

        const tbody = activeSection.querySelector('tbody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr[data-order-id]');
        let visibleCount = 0;

        rows.forEach(row => {
            const productName = row.getAttribute('data-product-name') || '';
            if (productName.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show "no results" message if needed
        let noResultsRow = tbody.querySelector('tr.no-results');
        if (visibleCount === 0 && searchTerm) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results';
                noResultsRow.innerHTML = '<td colspan="7" style="text-align: center; padding: 20px;">No orders found matching "' + searchTerm + '"</td>';
                tbody.appendChild(noResultsRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    }

    // ===================== DATE FILTERING =====================
    // Date filtering disabled as per requirements
    /*
    function filterOrdersByDate(date) {
        const activeTab = document.querySelector('.tab-link.active');
        if (activeTab) {
            currentTab = activeTab.getAttribute('data-tab');
        }

        fetch(base_url + 'SalesCon/filter_orders_by_date', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'date=' + encodeURIComponent(date) + '&status=' + encodeURIComponent(currentTab)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateTableWithFilteredOrders(data.orders, currentTab);
                if (data.count === 0) {
                    alert('No orders found for the selected date.');
                }
            } else {
                alert('Error filtering orders: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error filtering orders. Please try again.');
        });
    }
    */

    function updateTableWithFilteredOrders(orders, tab) {
        const sectionId = 'tab-' + tab;
        const section = document.getElementById(sectionId);
        if (!section) return;

        const tbody = section.querySelector('tbody');
        if (!tbody) return;

        // Clear existing rows (except header)
        const existingRows = tbody.querySelectorAll('tr[data-order-id]');
        existingRows.forEach(row => row.remove());

        // Add new rows
        if (orders.length === 0) {
            const noDataRow = document.createElement('tr');
            noDataRow.innerHTML = '<td colspan="7" style="text-align: center; padding: 20px;">No orders found for the selected date</td>';
            tbody.appendChild(noDataRow);
        } else {
            orders.forEach((order, index) => {
                const row = createOrderRow(order, tab, index + 1);
                tbody.appendChild(row);
            });
            
            // Re-attach event listeners for new buttons
            attachButtonListeners();
        }

        // Re-apply search filter if active
        const searchTerm = searchBox ? searchBox.value.toLowerCase().trim() : '';
        if (searchTerm) {
            filterByProductName(searchTerm);
        }

        updatePagination();
    }

    function createOrderRow(order, tab, rowNum) {
        const row = document.createElement('tr');
        const orderIdNum = order.OrderIDFormatted ? order.OrderIDFormatted.replace('#GI', '') : (order.OrderID ? order.OrderID.toString().replace('#GI', '') : '');
        const orderIdDisplay = order.OrderIDFormatted || order.OrderID || '#GI000';
        row.setAttribute('data-order-id', orderIdNum);
        row.setAttribute('data-product-name', (order.ProductName || '').toLowerCase());

        if (tab === 'pending') {
            row.innerHTML = `
                <td>${rowNum}</td>
                <td>${orderIdDisplay}</td>
                <td>${order.ProductName || 'N/A'}</td>
                <td>${order.Address || 'N/A'}</td>
                <td>${order.Date || 'N/A'}</td>
                <td>${order.Price || '₱0.00'}</td>
                <td><button class="btn-approve" data-order-id="${orderIdNum}">Request Approval</button></td>
            `;
        } else if (tab === 'awaiting') {
            row.innerHTML = `
                <td>${rowNum}</td>
                <td>${orderIdDisplay}</td>
                <td>${order.ProductName || 'N/A'}</td>
                <td>${order.Address || 'N/A'}</td>
                <td>${order.Date || 'N/A'}</td>
                <td>${order.Price || '₱0.00'}</td>
                <td>
                    <button class="btn-view" data-order-id="${orderIdNum}">
                        <img src="${base_url}assets/images/img_admin/search-icon.svg" alt="View Icon" class="button-icon">
                        View
                    </button>
                </td>
            `;
        } else if (tab === 'ready') {
            const statusClass = order.Status === 'Approved' ? 'approved' : (order.Status === 'In Fabrication' ? 'in-progress' : 'ready');
            row.innerHTML = `
                <td>${rowNum}</td>
                <td>${orderIdDisplay}</td>
                <td>${order.ProductName || 'N/A'}</td>
                <td>${order.Address || 'N/A'}</td>
                <td>${order.Date || 'N/A'}</td>
                <td><span class="status ${statusClass}">${order.Status || 'Pending'}</span></td>
                <td><button class="btn-check" data-order-id="${orderIdNum}">Check</button></td>
            `;
        }

        return row;
    }
    
    function attachButtonListeners() {
        // Event listeners are handled by event delegation in the main files
        // This function is a placeholder for any additional setup needed
    }

    function loadAllOrders() {
        // Reload page to show all orders
        window.location.reload();
    }

    // ===================== PAGINATION =====================
    let currentPage = 1;
    const itemsPerPage = 10;

    function updatePagination() {
        const activeSection = document.querySelector('.order-section.active');
        if (!activeSection) return;

        const tbody = activeSection.querySelector('tbody');
        if (!tbody) return;

        const visibleRows = Array.from(tbody.querySelectorAll('tr[data-order-id]')).filter(row => row.style.display !== 'none');
        const totalItems = visibleRows.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        // Update pagination info
        const start = totalItems === 0 ? 0 : ((currentPage - 1) * itemsPerPage) + 1;
        const end = Math.min(currentPage * itemsPerPage, totalItems);

        document.getElementById('pagination-start').textContent = start;
        document.getElementById('pagination-end').textContent = end;
        document.getElementById('pagination-total').textContent = totalItems;

        // Show/hide pagination controls
        const paginationControls = document.querySelector('.pagination-controls');
        if (totalPages <= 1) {
            paginationControls.style.display = 'none';
        } else {
            paginationControls.style.display = 'flex';
        }

        // Update page buttons
        const currentPageBtn = document.getElementById('current-page');
        if (currentPageBtn) {
            currentPageBtn.textContent = currentPage;
            currentPageBtn.disabled = true;
        }

        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

        // Show/hide rows based on current page
        visibleRows.forEach((row, index) => {
            const rowPage = Math.floor(index / itemsPerPage) + 1;
            if (rowPage === currentPage) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Pagination button handlers
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                updatePagination();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            const activeSection = document.querySelector('.order-section.active');
            if (activeSection) {
                const tbody = activeSection.querySelector('tbody');
                if (tbody) {
                    const visibleRows = Array.from(tbody.querySelectorAll('tr[data-order-id]')).filter(row => row.style.display !== 'none');
                    const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
                    if (currentPage < totalPages) {
                        currentPage++;
                        updatePagination();
                    }
                }
            }
        });
    }

    // Reset pagination when tab changes
    const tabLinks = document.querySelectorAll('.tab-link');
    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            currentPage = 1;
            currentTab = this.getAttribute('data-tab');
            setTimeout(() => {
                updatePagination();
            }, 100);
        });
    });

    // Initialize pagination
    updatePagination();

    // ===================== ORDER DETAILS POPUP =====================
    // This function ALWAYS fetches fresh data from the database - no caching
    window.loadOrderDetails = function(orderId, popupType) {
        // Show loading state
        const popupOverlay = document.getElementById(popupType + 'Popup') || document.getElementById('approvalPopup');
        if (popupOverlay) {
            // Clear previous data while loading
            const detailValues = popupOverlay.querySelectorAll('.detail-value');
            detailValues.forEach(el => {
                if (!el.classList.contains('total-price')) {
                    el.textContent = 'Loading...';
                }
            });
        }
        
        // Fetch FRESH data from database with cache-busting timestamp
        const timestamp = new Date().getTime();
        fetch(base_url + 'SalesCon/get_order_details?t=' + timestamp, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            },
            cache: 'no-store',
            body: 'order_id=' + encodeURIComponent(orderId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.order) {
                const order = data.order;
                const prefix = popupType || 'popup';

                // Update all popup fields with data from database (exactly as stored - no modifications)
                updatePopupField(prefix + '-order-id', order.OrderID);
                updatePopupField(prefix + '-product', order.ProductName);
                updatePopupField(prefix + '-address', order.Address);
                updatePopupField(prefix + '-date', order.Date);
                updatePopupField(prefix + '-shape', order.Shape);
                updatePopupField(prefix + '-dimension', order.Dimensions); // Already formatted as Height x Width
                updatePopupField(prefix + '-type', order.Type);
                updatePopupField(prefix + '-thickness', order.Thickness);
                updatePopupField(prefix + '-edgework', order.EdgeWork);
                updatePopupField(prefix + '-frametype', order.FrameType || 'N/A');
                updatePopupField(prefix + '-engraving', order.Engraving);
                // New category-specific fields
                updatePopupField(prefix + '-ledbacklight', order.LEDBacklight || 'N/A');
                updatePopupField(prefix + '-dooroperation', order.DoorOperation || 'N/A');
                updatePopupField(prefix + '-configuration', order.Configuration || 'N/A');
                updatePopupField(prefix + '-total', order.TotalAmount);
                
                // Conditionally show/hide fields based on product category
                const category = order.ProductCategory || '';
                showHideFieldsByCategory(prefix, category, order);

                // Handle file attachment - always make clickable if file exists
                const fileLink = document.getElementById(prefix + '-file-link');
                const fileText = document.getElementById(prefix + '-file-text');
                if (order.FileAttached && order.FileAttached !== 'N/A') {
                    // Build file URL - try FileUrl first, then construct from FileAttached
                    let fileUrl = order.FileUrl;
                    if (!fileUrl && order.FileAttached) {
                        // Construct URL from file name
                        fileUrl = base_url + 'uploads/' + order.FileAttached;
                    }
                    if (fileLink && fileText) {
                        fileLink.href = fileUrl;
                        fileLink.textContent = order.FileAttached;
                        fileLink.style.display = 'inline';
                        fileText.style.display = 'none';
                    }
                } else {
                    if (fileLink && fileText) {
                        fileLink.style.display = 'none';
                        fileText.textContent = 'N/A';
                        fileText.style.display = 'inline';
                    }
                }
                
                // Handle AdminNotes for disapproved orders
                const notesElement = document.getElementById(prefix + '-notes');
                if (notesElement && order.AdminNotes) {
                    notesElement.value = order.AdminNotes;
                } else if (notesElement && !order.AdminNotes) {
                    notesElement.value = '';
                }
            } else {
                alert('Error loading order details: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order details. Please try again.');
        });
    };

    function updatePopupField(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || '-';
        }
    }
    
    /**
     * Show/hide fields based on product category
     * Categories:
     * - Mirrors: Dimensions, Edge Work, Glass Shape, LED Backlight (optional), Engraving (optional)
     * - Shower Enclosure / Partition: Dimensions, Glass Type, Glass Thickness, Frame Type, Engraving (optional), Door Operation
     * - Aluminum Doors: Dimensions, Glass Type, Glass Thickness, Configuration
     * - Aluminum and Bathroom Doors: Dimensions, Frame Type
     */
    window.showHideFieldsByCategory = function(prefix, category, order) {
        // Normalize category name (case-insensitive)
        const cat = (category || '').toLowerCase().trim();
        
        // Get all field rows
        const shapeRow = document.getElementById(prefix + '-shape').closest('.detail-row');
        const dimensionRow = document.getElementById(prefix + '-dimension').closest('.detail-row');
        const typeRow = document.getElementById(prefix + '-type').closest('.detail-row');
        const thicknessRow = document.getElementById(prefix + '-thickness').closest('.detail-row');
        const edgeworkRow = document.getElementById(prefix + '-edgework').closest('.detail-row');
        const frametypeRow = document.getElementById(prefix + '-frametype').closest('.detail-row');
        const engravingRow = document.getElementById(prefix + '-engraving').closest('.detail-row');
        const ledbacklightRow = document.getElementById(prefix + '-ledbacklight-row');
        const dooroperationRow = document.getElementById(prefix + '-dooroperation-row');
        const configurationRow = document.getElementById(prefix + '-configuration-row');
        
        // Hide all category-specific fields first
        if (ledbacklightRow) ledbacklightRow.style.display = 'none';
        if (dooroperationRow) dooroperationRow.style.display = 'none';
        if (configurationRow) configurationRow.style.display = 'none';
        
        // Show/hide fields based on category
        if (cat === 'mirrors') {
            // Mirrors: Dimensions, Edge Work, Glass Shape, LED Backlight (optional), Engraving (optional)
            if (shapeRow) shapeRow.style.display = '';
            if (dimensionRow) dimensionRow.style.display = '';
            if (edgeworkRow) edgeworkRow.style.display = '';
            if (engravingRow) engravingRow.style.display = '';
            if (ledbacklightRow && order.LEDBacklight && order.LEDBacklight !== 'N/A') {
                ledbacklightRow.style.display = '';
            }
            // Hide fields not used by Mirrors
            if (typeRow) typeRow.style.display = 'none';
            if (thicknessRow) thicknessRow.style.display = 'none';
            if (frametypeRow) frametypeRow.style.display = 'none';
            if (dooroperationRow) dooroperationRow.style.display = 'none';
            if (configurationRow) configurationRow.style.display = 'none';
        } else if (cat === 'shower enclosure / partition' || cat.includes('shower')) {
            // Shower Enclosure / Partition: Dimensions, Glass Type, Glass Thickness, Frame Type, Engraving (optional), Door Operation
            if (dimensionRow) dimensionRow.style.display = '';
            if (typeRow) typeRow.style.display = '';
            if (thicknessRow) thicknessRow.style.display = '';
            if (frametypeRow) frametypeRow.style.display = '';
            if (engravingRow) engravingRow.style.display = '';
            if (dooroperationRow && order.DoorOperation && order.DoorOperation !== 'N/A') {
                dooroperationRow.style.display = '';
            }
            // Hide fields not used by Shower Enclosure / Partition
            if (shapeRow) shapeRow.style.display = 'none';
            if (edgeworkRow) edgeworkRow.style.display = 'none';
            if (ledbacklightRow) ledbacklightRow.style.display = 'none';
            if (configurationRow) configurationRow.style.display = 'none';
        } else if (cat === 'aluminum doors' || (cat.includes('aluminum') && !cat.includes('bathroom'))) {
            // Aluminum Doors: Dimensions, Glass Type, Glass Thickness, Configuration
            if (dimensionRow) dimensionRow.style.display = '';
            if (typeRow) typeRow.style.display = '';
            if (thicknessRow) thicknessRow.style.display = '';
            if (configurationRow && order.Configuration && order.Configuration !== 'N/A') {
                configurationRow.style.display = '';
            }
            // Hide fields not used by Aluminum Doors
            if (shapeRow) shapeRow.style.display = 'none';
            if (edgeworkRow) edgeworkRow.style.display = 'none';
            if (frametypeRow) frametypeRow.style.display = 'none';
            if (engravingRow) engravingRow.style.display = 'none';
            if (ledbacklightRow) ledbacklightRow.style.display = 'none';
            if (dooroperationRow) dooroperationRow.style.display = 'none';
        } else if (cat === 'aluminum and bathroom doors' || (cat.includes('aluminum') && cat.includes('bathroom'))) {
            // Aluminum and Bathroom Doors: Dimensions, Frame Type
            if (dimensionRow) dimensionRow.style.display = '';
            if (frametypeRow) frametypeRow.style.display = '';
            // Hide fields not used by Aluminum and Bathroom Doors
            if (shapeRow) shapeRow.style.display = 'none';
            if (typeRow) typeRow.style.display = 'none';
            if (thicknessRow) thicknessRow.style.display = 'none';
            if (edgeworkRow) edgeworkRow.style.display = 'none';
            if (engravingRow) engravingRow.style.display = 'none';
            if (ledbacklightRow) ledbacklightRow.style.display = 'none';
            if (dooroperationRow) dooroperationRow.style.display = 'none';
            if (configurationRow) configurationRow.style.display = 'none';
        } else {
            // Unknown category - show all fields (fallback)
            if (shapeRow) shapeRow.style.display = '';
            if (dimensionRow) dimensionRow.style.display = '';
            if (typeRow) typeRow.style.display = '';
            if (thicknessRow) thicknessRow.style.display = '';
            if (edgeworkRow) edgeworkRow.style.display = '';
            if (frametypeRow) frametypeRow.style.display = '';
            if (engravingRow) engravingRow.style.display = '';
            if (ledbacklightRow && order.LEDBacklight && order.LEDBacklight !== 'N/A') {
                ledbacklightRow.style.display = '';
            }
            if (dooroperationRow && order.DoorOperation && order.DoorOperation !== 'N/A') {
                dooroperationRow.style.display = '';
            }
            if (configurationRow && order.Configuration && order.Configuration !== 'N/A') {
                configurationRow.style.display = '';
            }
        }
    };
});

