document.addEventListener('DOMContentLoaded', async function () {

    // ======================
    // 1️⃣ Variables
    // ======================
    const tbody = document.querySelector('#ordersTableBody');
    const foundText = document.querySelector('.found-text');
    const tabButtons = document.querySelectorAll('.order-tabs .tab-button');
    const searchInput = document.querySelector('.search-container input');
    const searchBtn = document.querySelector('.search-container .search-button');

    const actionMenu = document.getElementById('actionMenu');
    const popup = document.getElementById('orderPopup');
    const closeBtn = document.getElementById('closePopup');

    const btn = document.getElementById('calendar-btn');
    const dropdown = document.getElementById('calendar-dropdown');
    const monthEl = document.getElementById('month');
    const yearEl = document.getElementById('year');
    const dropdownMonth = document.getElementById('dropdown-month');
    const dropdownYear = document.getElementById('dropdown-year');
    const resetCalendarBtn = document.getElementById('reset-calendar');

    // Approval section elements
    const approvalTbody = document.querySelector('#approvalTableBody');
    const approvalPopup = document.getElementById('approvalPopup');
    const closeApprovalPopup = document.getElementById('closeApprovalPopup');

    let searchQuery = '';
    let activeFilter = 'all orders';
    let selectedMonth = null;
    let selectedYear = null;

    let currentPage = 1;
    let totalPages = 1;
    const itemsPerPage = 10;

    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();

    let currentOrderId = null; // For popup actions
    let currentApprovalOrderId = null; // For approval popup

    // ======================
    // 2️⃣ Load Orders from Database
    // ======================
    async function loadOrders() {
        try {
            const params = new URLSearchParams({
                status: activeFilter,
                search: searchQuery,
                month: selectedMonth !== null ? selectedMonth : '',
                year: selectedYear !== null ? selectedYear : '',
                page: currentPage,
                limit: itemsPerPage
            });

            const response = await fetch(getOrdersUrl + '?' + params.toString());
            if (!response.ok) throw new Error("Network response was not ok");
            
            const data = await response.json();
            renderOrdersTable(data.orders);
            totalPages = data.total_pages;
            updatePaginationControls(data.total);
            foundText.textContent = `${data.total} Orders found`;
        } catch (error) {
            console.error("Error loading orders:", error);
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">Error loading orders. Please refresh the page.</td></tr>';
        }
    }

    // ======================
    // 3️⃣ Render Orders Table
    // ======================
    function renderOrdersTable(orders) {
        if (!tbody) return;
        
        tbody.innerHTML = '';

        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">No orders found</td></tr>';
            attachActionMenu();
            return;
        }

        orders.forEach((order, index) => {
            const tr = document.createElement('tr');
            // Store status in data attribute for action menu filtering
            tr.innerHTML = `
                <td>${(currentPage - 1) * itemsPerPage + index + 1}</td>
                <td>${order.order_id}</td>
                <td>${order.product_name}</td>
                <td>${order.address}</td>
                <td>${order.date}</td>
                <td>₱${parseFloat(order.price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td><span class="status-badge ${order.status_class}">${order.status}</span></td>
                <td class="action-cell" data-order-id="${order.order_id}" data-order-status="${order.status}">⋮</td>
            `;
            tbody.appendChild(tr);
        });

        attachActionMenu();
    }

    // ======================
    // 4️⃣ Pagination Controls
    // ======================
    function updatePaginationControls(total) {
        // Find the pagination in the orders section (first one)
        const orderSection = document.querySelector('.order-list-section');
        if (!orderSection) return;
        
        const pagination = orderSection.querySelector('.pagination-controls');
        if (!pagination) return;
        
        pagination.innerHTML = '';
        const paginationInfo = orderSection.querySelector('.pagination span');
        if (paginationInfo) {
            const start = total > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
            const end = Math.min(currentPage * itemsPerPage, total);
            paginationInfo.textContent = total > 0 ? `Showing ${start}-${end} of ${total} items` : 'No items';
        }

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', () => { 
            if (currentPage > 1) {
                currentPage--; 
                loadOrders(); 
            }
        });
        pagination.appendChild(prevBtn);

        // Page buttons (show max 5 pages)
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            if (i === currentPage) btn.classList.add('active');
            btn.addEventListener('click', () => { 
                currentPage = i; 
                loadOrders(); 
            });
            pagination.appendChild(btn);
        }

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.addEventListener('click', () => { 
            if (currentPage < totalPages) {
                currentPage++; 
                loadOrders(); 
            }
        });
        pagination.appendChild(nextBtn);
    }

    // ======================
    // 5️⃣ Tab Filters
    // ======================
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.textContent.trim().toLowerCase();
            currentPage = 1;
            loadOrders();
        });
    });

    // ======================
    // 6️⃣ Search Filters
    // ======================
    searchBtn.addEventListener('click', () => {
        searchQuery = searchInput.value.trim();
        currentPage = 1;
        loadOrders();
    });

    searchInput.addEventListener('keyup', e => {
        if (e.key === 'Enter') {
            searchQuery = searchInput.value.trim();
            currentPage = 1;
            loadOrders();
        }
    });

    // ======================
    // 7️⃣ Calendar Filters
    // ======================
    function updateCalendarDisplay() {
        if (monthEl) monthEl.textContent = selectedMonth !== null ? monthNames[selectedMonth] : 'All';
        if (dropdownMonth) dropdownMonth.textContent = selectedMonth !== null ? monthNames[selectedMonth] : 'All';
        if (yearEl) yearEl.textContent = selectedYear !== null ? selectedYear : '';
        if (dropdownYear) dropdownYear.textContent = selectedYear !== null ? selectedYear : '';
    }

    function changeMonth(offset) {
        currentMonth += offset;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        selectedMonth = currentMonth;
        selectedYear = currentYear;
        updateCalendarDisplay();
        currentPage = 1;
        loadOrders();
    }

    function changeYear(offset) {
        currentYear += offset;
        selectedMonth = currentMonth;
        selectedYear = currentYear;
        updateCalendarDisplay();
        currentPage = 1;
        loadOrders();
    }

    if (btn) {
        btn.addEventListener('click', () => {
            if (dropdown) {
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            }
        });
    }

    if (resetCalendarBtn) {
        resetCalendarBtn.addEventListener('click', () => {
            selectedMonth = null;
            selectedYear = null;
            updateCalendarDisplay();
            currentPage = 1;
            loadOrders();
            if (dropdown) dropdown.style.display = 'none';
        });
    }

    window.prevMonth = () => changeMonth(-1);
    window.nextMonth = () => changeMonth(1);
    window.prevYear = () => changeYear(-1);
    window.nextYear = () => changeYear(1);

    updateCalendarDisplay();

    // ======================
    // 8️⃣ Action Menu
    // ======================
    function attachActionMenu() {
        if (!actionMenu) return;
        
        const actionCells = document.querySelectorAll('.action-cell');
        actionCells.forEach(cell => {
            // Remove existing listeners
            const newCell = cell.cloneNode(true);
            cell.parentNode.replaceChild(newCell, cell);
            
            newCell.addEventListener('click', e => {
                e.stopPropagation();
                currentOrderId = newCell.getAttribute('data-order-id');
                const orderStatus = newCell.getAttribute('data-order-status') || '';
                
                // Show/hide menu items based on order status
                updateActionMenuForStatus(orderStatus);
                
                const rect = newCell.getBoundingClientRect();
                actionMenu.style.top = `${window.scrollY + rect.bottom}px`;
                actionMenu.style.left = `${window.scrollX + rect.left}px`;
                actionMenu.style.display = 'block';
            });
        });
    }
    
    // Update action menu items based on order status
    function updateActionMenuForStatus(status) {
        if (!actionMenu) return;
        
        const menuItems = actionMenu.querySelectorAll('li');
        menuItems.forEach(item => {
            const actionText = item.textContent.trim();
            
            // Always show View
            if (actionText === 'View') {
                item.style.display = 'block';
                return;
            }
            
            // Show Complete only for orders that can be completed
            if (actionText === 'Complete') {
                const canComplete = ['Approved', 'In Fabrication', 'Ready for Installation'].some(s => 
                    status.toLowerCase().includes(s.toLowerCase())
                );
                item.style.display = canComplete ? 'block' : 'none';
                
                // If hidden and status is "Awaiting Admin", add a tooltip message
                if (!canComplete && status.toLowerCase().includes('awaiting admin')) {
                    item.title = 'This order must be approved in the "Order Schedule Approval" section first';
                }
                return;
            }
            
            // Show Cancel for orders that can be cancelled
            if (actionText === 'Cancel') {
                const canCancel = !['Completed', 'Cancelled'].some(s => 
                    status.toLowerCase().includes(s.toLowerCase())
                );
                item.style.display = canCancel ? 'block' : 'none';
                return;
            }
            
            // Show Delete (if needed)
            if (actionText === 'Delete') {
                // Only show delete for cancelled or completed orders (or implement as needed)
                item.style.display = 'block';
                return;
            }
        });
    }

    document.addEventListener('click', e => {
        if (actionMenu && !actionMenu.contains(e.target)) {
            actionMenu.style.display = 'none';
        }
    });

    // Handle action menu clicks
    if (actionMenu) {
        actionMenu.addEventListener('click', (e) => {
            const action = e.target.textContent.trim();
            if (action === 'View' && currentOrderId) {
                loadOrderDetails(currentOrderId);
            } else if (action === 'Complete' && currentOrderId) {
                completeOrder(currentOrderId);
            }
            actionMenu.style.display = 'none';
        });
    }

    // ======================
    // 9️⃣ Order Details Popup
    // ======================
    async function loadOrderDetails(orderId) {
        try {
            const response = await fetch(getOrderDetailsUrl + '?order_id=' + encodeURIComponent(orderId));
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Order details response:', data); // Debug log
            
            if (data.success && data.order) {
                displayOrderDetails(data.order);
                if (popup) popup.style.display = 'flex';
            } else {
                const errorMsg = data.message || 'Failed to load order details';
                console.error('Order details error:', errorMsg, data);
                alert(errorMsg);
            }
        } catch (error) {
            console.error("Error loading order details:", error);
            alert('Failed to load order details. Please check the console for details and try again.');
        }
    }

    function displayOrderDetails(order) {
        document.getElementById('popup-order-id').textContent = order.order_id;
        document.getElementById('popup-product-name').textContent = order.product_name;
        document.getElementById('popup-address').textContent = order.address;
        document.getElementById('popup-date').textContent = order.date;
        document.getElementById('popup-status').textContent = order.status;
        document.getElementById('popup-shape').textContent = order.shape;
        document.getElementById('popup-dimension').textContent = order.dimension;
        document.getElementById('popup-type').textContent = order.type;
        document.getElementById('popup-thickness').textContent = order.thickness;
        document.getElementById('popup-edge-work').textContent = order.edge_work;
        document.getElementById('popup-frame-type').textContent = order.frame_type;
        document.getElementById('popup-engraving').textContent = order.engraving;
        
        // File attached
        const fileAttached = document.getElementById('popup-file-attached');
        if (order.file_attached && order.file_attached !== 'N/A') {
            // Use file_url from backend if available (already includes full path), otherwise construct from file_attached
            let fileUrl = order.file_url;
            if (!fileUrl && order.file_attached) {
                // Backend didn't provide file_url, construct it
                if (order.file_attached.startsWith('uploads/')) {
                    fileUrl = baseUrl + order.file_attached;
                } else {
                    fileUrl = baseUrl + 'uploads/' + order.file_attached;
                }
            }
            // Get just the filename for display
            const fileName = (order.file_attached.includes('/') ? order.file_attached.split('/').pop() : order.file_attached);
            fileAttached.innerHTML = `<a href="${fileUrl}" target="_blank">${fileName}</a>`;
        } else {
            fileAttached.textContent = 'N/A';
        }
        
        document.getElementById('popup-total-quotation').textContent = order.total_quotation;
        
        // Preferred Installation Date
        const preferredDateEl = document.getElementById('popup-preferred-installation-date');
        if (preferredDateEl) {
            if (order.preferred_installation_date && order.preferred_installation_date !== 'N/A') {
                // Format the date if it's in Y-m-d format
                if (order.preferred_installation_date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    const dateObj = new Date(order.preferred_installation_date);
                    preferredDateEl.textContent = dateObj.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                } else {
                    preferredDateEl.textContent = order.preferred_installation_date;
                }
            } else {
                preferredDateEl.textContent = 'N/A';
            }
        }
        
        // Barcode
        const barcodeImg = document.getElementById('barcode-img');
        if (barcodeImg) {
            const orderIdForBarcode = order.order_id.replace('#', '');
            barcodeImg.src = `https://barcode.tec-it.com/barcode.ashx?data=${orderIdForBarcode}&code=Code128&translate-esc=false`;
        }
        
        // Hide approve/disapprove buttons for regular order details
        const approveBtn = document.getElementById('popup-approve-btn');
        const disapproveBtn = document.getElementById('popup-disapprove-btn');
        if (approveBtn) approveBtn.style.display = 'none';
        if (disapproveBtn) disapproveBtn.style.display = 'none';
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            if (popup) popup.style.display = 'none';
        });
    }

    window.addEventListener('click', e => {
        if (popup && e.target === popup) {
            popup.style.display = 'none';
        }
    });

    // ======================
    // 🔟 Load Approval Orders
    // ======================
    async function loadApprovalOrders() {
        try {
            const response = await fetch(getAwaitingApprovalUrl);
            if (!response.ok) throw new Error("Network response was not ok");
            
            const orders = await response.json();
            renderApprovalTable(orders);
            updateApprovalPagination(orders.length);
        } catch (error) {
            console.error("Error loading approval orders:", error);
            if (approvalTbody) {
                approvalTbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">Error loading approval orders. Please refresh the page.</td></tr>';
            }
            updateApprovalPagination(0, true);
        }
    }

    function renderApprovalTable(orders) {
        if (!approvalTbody) return;
        
        approvalTbody.innerHTML = '';

        if (orders.length === 0) {
            approvalTbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No orders awaiting approval</td></tr>';
            return;
        }

        orders.forEach((order, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${order.order_id}</td>
                <td>${order.scheduled_date}</td>
                <td>₱${parseFloat(order.price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td><button class="btn-review" data-order-id="${order.order_id}">Review</button></td>
            `;
            approvalTbody.appendChild(tr);
        });

        // Attach review button listeners
        const reviewBtns = approvalTbody.querySelectorAll('.btn-review');
        reviewBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const orderId = btn.getAttribute('data-order-id');
                loadApprovalOrderDetails(orderId);
            });
        });
    }

    function updateApprovalPagination(total, isError = false) {
        // Find the pagination in the approval section
        const approvalSection = document.querySelector('.order-schedule-section');
        if (!approvalSection) return;
        
        const pagination = approvalSection.querySelector('.pagination');
        if (!pagination) return;
        
        const paginationInfo = pagination.querySelector('span');
        if (paginationInfo) {
            if (isError) {
                paginationInfo.textContent = 'Error loading orders';
            } else if (total === 0) {
                paginationInfo.textContent = 'No orders awaiting approval';
            } else {
                paginationInfo.textContent = `Showing ${total} order${total !== 1 ? 's' : ''} awaiting approval`;
            }
        }
        
        // Clear pagination controls since approval orders don't use pagination
        const paginationControls = pagination.querySelector('.pagination-controls');
        if (paginationControls) {
            paginationControls.innerHTML = '';
        }
    }

    // ======================
    // 1️⃣1️⃣ Approval Order Details
    // ======================
    async function loadApprovalOrderDetails(orderId) {
        try {
            // Validate orderId
            if (!orderId) {
                console.error('loadApprovalOrderDetails: orderId is required');
                alert('Error: Order ID is missing. Please try again.');
                return;
            }

            // Validate URL is defined
            if (typeof getApprovalOrderDetailsUrl === 'undefined') {
                console.error('loadApprovalOrderDetails: getApprovalOrderDetailsUrl is not defined');
                alert('Error: API endpoint configuration is missing. Please refresh the page.');
                return;
            }

            const url = getApprovalOrderDetailsUrl + '?order_id=' + encodeURIComponent(orderId);
            console.log('Fetching approval order details from:', url);

            const response = await fetch(url);
            
            // Log response details for debugging
            console.log('Response status:', response.status, response.statusText);
            console.log('Response headers:', Object.fromEntries(response.headers.entries()));

            // Check if response is ok
            if (!response.ok) {
                // Clone the response so we can read it multiple times if needed
                const responseClone = response.clone();
                
                // Try to get error message from response
                let errorMessage = `Server error (${response.status}): ${response.statusText}`;
                let errorData = null;
                
                try {
                    // Try to parse as JSON first
                    try {
                        errorData = await response.json();
                        if (errorData && errorData.message) {
                            errorMessage = errorData.message;
                        } else if (errorData && errorData.error) {
                            errorMessage = errorData.error;
                        }
                        console.log('Error response JSON:', errorData);
                    } catch (jsonError) {
                        // If JSON parsing fails, try text
                        console.log('JSON parse failed, trying text...');
                        const responseText = await responseClone.text();
                        console.log('Error response text:', responseText.substring(0, 500));
                        
                        // Try to parse as JSON from text
                        try {
                            errorData = JSON.parse(responseText);
                            if (errorData && errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (parseError) {
                            // If still not JSON, use the text directly (truncated)
                            if (responseText && responseText.length > 0) {
                                errorMessage += '\n\nResponse: ' + responseText.substring(0, 500);
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error reading error response:', e);
                }
                
                console.error('loadApprovalOrderDetails - Server error:', {
                    status: response.status,
                    statusText: response.statusText,
                    message: errorMessage,
                    errorData: errorData,
                    url: url
                });
                
                // Show user-friendly error message
                const userMessage = errorData && errorData.message 
                    ? errorData.message 
                    : `Failed to load order details.\n\nError: ${errorMessage}\n\nPlease check the browser console for more details.`;
                
                alert(userMessage);
                return;
            }
            
            // Parse JSON response
            let data;
            try {
                const responseText = await response.text();
                console.log('Response text:', responseText.substring(0, 500)); // Log first 500 chars
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('loadApprovalOrderDetails - JSON parse error:', parseError);
                console.error('Response was not valid JSON');
                alert('Error: Server returned invalid data. Please check the console for details.');
                return;
            }
            
            // Validate response structure
            if (!data) {
                console.error('loadApprovalOrderDetails - Empty response data');
                alert('Error: Server returned empty response. Please try again.');
                return;
            }

            console.log('Order details response:', data);

            if (data.success && data.order) {
                currentApprovalOrderId = orderId;
                displayApprovalOrderDetails(data.order);
                if (approvalPopup) {
                    approvalPopup.style.display = 'flex';
                } else {
                    console.error('Approval popup element not found');
                    alert('Error: Approval popup element is missing from the page.');
                }
            } else {
                const errorMsg = data.message || 'Failed to load order details. The order may not exist or you may not have permission to view it.';
                console.error('loadApprovalOrderDetails - API returned error:', errorMsg);
                alert(errorMsg);
            }
        } catch (error) {
            // Handle network errors and other exceptions
            console.error("Error loading approval order details:", error);
            console.error("Error stack:", error.stack);
            
            let userMessage = 'Failed to load order details. ';
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                userMessage += 'Network error: Could not connect to the server. Please check your internet connection.';
            } else if (error.message) {
                userMessage += error.message;
            } else {
                userMessage += 'An unexpected error occurred. Please try again.';
            }
            
            alert(userMessage + '\n\nCheck the browser console for more details.');
        }
    }

    function displayApprovalOrderDetails(order) {
        document.getElementById('approval-order-id').textContent = order.order_id;
        document.getElementById('approval-product-name').textContent = order.product_name;
        document.getElementById('approval-customer-name').textContent = order.customer_name;
        document.getElementById('approval-sales-rep-name').textContent = order.sales_rep_name;
        document.getElementById('approval-address').textContent = order.address;
        document.getElementById('approval-order-date').textContent = order.date;
        document.getElementById('approval-scheduled-date').textContent = order.scheduled_date;
        document.getElementById('approval-requested-date').textContent = order.requested_date;
        document.getElementById('approval-shape').textContent = order.shape;
        document.getElementById('approval-dimension').textContent = order.dimension;
        document.getElementById('approval-type').textContent = order.type;
        document.getElementById('approval-thickness').textContent = order.thickness;
        document.getElementById('approval-edge-work').textContent = order.edge_work;
        document.getElementById('approval-frame-type').textContent = order.frame_type;
        document.getElementById('approval-engraving').textContent = order.engraving;
        
        // File attached
        const fileAttached = document.getElementById('approval-file-attached');
        if (order.file_attached && order.file_attached !== 'N/A') {
            // Use file_url from backend if available (already includes full path), otherwise construct from file_attached
            let fileUrl = order.file_url;
            if (!fileUrl && order.file_attached) {
                // Backend didn't provide file_url, construct it
                if (order.file_attached.startsWith('uploads/')) {
                    fileUrl = baseUrl + order.file_attached;
                } else {
                    fileUrl = baseUrl + 'uploads/' + order.file_attached;
                }
            }
            // Get just the filename for display
            const fileName = (order.file_attached.includes('/') ? order.file_attached.split('/').pop() : order.file_attached);
            fileAttached.innerHTML = `<a href="${fileUrl}" target="_blank">${fileName}</a>`;
        } else {
            fileAttached.textContent = 'N/A';
        }
        
        document.getElementById('approval-total-quotation').textContent = order.total_quotation;
        
        // Preferred Installation Date
        const approvalPrefDateEl = document.getElementById('approval-preferred-installation-date');
        if (approvalPrefDateEl) {
            approvalPrefDateEl.textContent = order.preferred_installation_date || 'N/A';
        }
        
        // Barcode
        const barcodeImg = document.getElementById('approval-barcode-img');
        if (barcodeImg) {
            const orderIdForBarcode = order.order_id.replace('#', '');
            barcodeImg.src = `https://barcode.tec-it.com/barcode.ashx?data=${orderIdForBarcode}&code=Code128&translate-esc=false`;
        }
        
        // Clear form fields
        document.getElementById('admin-notes').value = '';
        document.getElementById('disapproval-reason').value = '';
    }

    if (closeApprovalPopup) {
        closeApprovalPopup.addEventListener('click', () => {
            if (approvalPopup) approvalPopup.style.display = 'none';
        });
    }

    const approvalCancelBtn = document.getElementById('approval-cancel-btn');
    if (approvalCancelBtn) {
        approvalCancelBtn.addEventListener('click', () => {
            if (approvalPopup) approvalPopup.style.display = 'none';
        });
    }

    window.addEventListener('click', e => {
        if (approvalPopup && e.target === approvalPopup) {
            approvalPopup.style.display = 'none';
        }
    });

    // ======================
    // 1️⃣2️⃣ Approve Order
    // ======================
    const approvalApproveBtn = document.getElementById('approval-approve-btn');
    if (approvalApproveBtn) {
        approvalApproveBtn.addEventListener('click', async () => {
            if (!currentApprovalOrderId) {
                alert('No order selected');
                return;
            }

            const adminNotes = document.getElementById('admin-notes').value.trim();

            try {
                const formData = new FormData();
                formData.append('order_id', currentApprovalOrderId);
                if (adminNotes) {
                    formData.append('admin_notes', adminNotes);
                }

                const response = await fetch(approveOrderUrl, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message || 'Order approved successfully!');
                    if (approvalPopup) approvalPopup.style.display = 'none';
                    loadApprovalOrders(); // Reload approval orders
                } else {
                    alert(data.message || 'Failed to approve order');
                }
            } catch (error) {
                console.error("Error approving order:", error);
                alert('Failed to approve order. Please try again.');
            }
        });
    }

    // ======================
    // 1️⃣3️⃣ Disapprove Order
    // ======================
    const approvalDisapproveBtn = document.getElementById('approval-disapprove-btn');
    if (approvalDisapproveBtn) {
        approvalDisapproveBtn.addEventListener('click', async () => {
            if (!currentApprovalOrderId) {
                alert('No order selected');
                return;
            }

            const disapprovalReason = document.getElementById('disapproval-reason').value.trim();
            if (!disapprovalReason) {
                alert('Please provide a reason for disapproval');
                return;
            }

            if (!confirm('Are you sure you want to disapprove this order?')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('order_id', currentApprovalOrderId);
                formData.append('disapproval_reason', disapprovalReason);

                const response = await fetch(disapproveOrderUrl, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message || 'Order disapproved successfully!');
                    if (approvalPopup) approvalPopup.style.display = 'none';
                    loadApprovalOrders(); // Reload approval orders
                } else {
                    alert(data.message || 'Failed to disapprove order');
                }
            } catch (error) {
                console.error("Error disapproving order:", error);
                alert('Failed to disapprove order. Please try again.');
            }
        });
    }

    // ======================
    // 1️⃣4️⃣ Complete Order
    // ======================
    async function completeOrder(orderId) {
        if (!orderId) {
            alert('No order selected');
            return;
        }

        // Get the order status from the action cell
        const actionCell = document.querySelector(`.action-cell[data-order-id="${orderId}"]`);
        const orderStatus = actionCell ? actionCell.getAttribute('data-order-status') : '';
        
        // Check if order is in "Awaiting Admin" status
        if (orderStatus && orderStatus.toLowerCase().includes('awaiting admin')) {
            alert('This order is awaiting admin approval. Please approve it in the "Order Schedule Approval" section below first.');
            return;
        }

        if (!confirm('Are you sure you want to mark this order as completed?')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('order_id', orderId);

            const response = await fetch(completeOrderUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Order marked as completed successfully!');
                loadOrders(); // Reload orders to reflect the status change
            } else {
                // Show helpful message if order can't be completed
                if (data.message && data.message.includes('cannot be completed')) {
                    alert(data.message + '\n\nNote: Orders in "Awaiting Admin" status must be approved in the "Order Schedule Approval" section first.');
                } else {
                    alert(data.message || 'Failed to complete order');
                }
            }
        } catch (error) {
            console.error("Error completing order:", error);
            alert('Failed to complete order. Please try again.');
        }
    }

    // ======================
    // 1️⃣5️⃣ Initial Load
    // ======================
    loadOrders();
    loadApprovalOrders();

});
