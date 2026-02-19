document.addEventListener("DOMContentLoaded", () => {
    // --- Selectors ---
    const filterTabs = document.querySelectorAll(".filter-tab");
    const tableBody = document.querySelector(".payment-table tbody");
    const rows = tableBody ? Array.from(tableBody.querySelectorAll("tr")) : [];
    const sortableHeaders = document.querySelectorAll(".sortable");
    const milestoneFilter = document.getElementById('milestoneFilter');

    const showingInfo = document.querySelector(".pagination span"); 
    const paginationControls = document.querySelector(".pagination-controls"); 
    
    // Check if controls exist before querying children
    const prevBtn = paginationControls ? paginationControls.querySelector("button:first-child") : null;
    const nextBtn = paginationControls ? paginationControls.querySelector("button:last-child") : null;
    
    // Selectors for the Inventory Stat boxes (based on recommended HTML IDs)
    const statPendingValue = document.getElementById('statPendingValue'); 
    const statOverdueValue = document.getElementById('statOverdueValue'); 

    // --- State ---
    let currentPage = 1;
    const rowsPerPage = 10; 
    let allRows = rows;
    let filteredRows = allRows;
    let currentSortColumn = null;
    let currentSortDirection = 'asc'; 


    // --- Core Helper Functions ---

    function getStatus(row) {
        // First try to get from data attribute (most reliable)
        const dataStatus = row.getAttribute('data-payment-status');
        if (dataStatus) {
            return dataStatus.toLowerCase();
        }
        
        // Fallback: Safely extract the status class from the status-badge element
        const statusElement = row.querySelector(".status-badge");
        if (!statusElement) return '';
        
        // Find the status class: 'pending', 'paid', 'overdue'
        const classes = Array.from(statusElement.classList);
        const status = classes.find(cls => ['pending', 'paid', 'overdue'].includes(cls));
        
        // If not found in classes, check text content
        if (!status) {
            const textStatus = statusElement.textContent.trim().toLowerCase();
            // Map text to status codes
            if (textStatus.includes('overdue')) return 'overdue';
            if (textStatus.includes('paid')) return 'paid';
            if (textStatus.includes('pending')) return 'pending';
            return textStatus;
        }
        
        return status;
    }

    function updateInventoryStats() {
        const counts = {
            all: allRows.length,
            pending: 0,
            paid: 0,
            overdue: 0
        };

        // 1. Calculate counts by iterating over ALL rows
        allRows.forEach(row => {
            const status = getStatus(row);
            if (counts.hasOwnProperty(status)) {
                counts[status]++;
            }
        });

        // 2. Update the HTML elements (Stats Boxes)
        if (statPendingValue) statPendingValue.textContent = counts.pending.toLocaleString();
        if (statOverdueValue) statOverdueValue.textContent = counts.overdue.toLocaleString();
        
        // Note: Weekly Sales (stat-green) is usually a separate calculation (not row count based).
    }

    function filterRows() {
        // Get the active filter value (using a data attribute or text content)
        const activeTab = document.querySelector(".filter-tab.active");
        let filter = "all";
        
        if (activeTab) {
            // Use data-status attribute (most reliable)
            filter = activeTab.getAttribute('data-status') || 'all';
        }

        // Get milestone filter value
        const milestoneValue = milestoneFilter ? milestoneFilter.value : 'all';

        // Filter rows by both status and milestone
        filteredRows = allRows.filter(row => {
            const status = getStatus(row);
            const statusMatch = filter === "all" || status === filter;
            
            const rowMilestone = row.dataset.milestone || '';
            const milestoneMatch = milestoneValue === 'all' || rowMilestone === milestoneValue;
            
            return statusMatch && milestoneMatch;
        });

        // Re-apply current sort after filtering
        if (currentSortColumn) {
            applySorting();
        }

        currentPage = 1; // Reset to page 1 on filter
        renderRows();
    }

    function renderRows() {
        // Remove "no data" row if it exists
        const existingNoDataRow = tableBody.querySelector('tr.no-data-row');
        if (existingNoDataRow) {
            existingNoDataRow.remove();
        }

        // Check if there are any filtered rows
        if (filteredRows.length === 0) {
            // Hide all rows
            allRows.forEach(row => row.style.display = "none");
            
            // Create and show "no data" message
            const noDataRow = document.createElement('tr');
            noDataRow.className = 'no-data-row';
            noDataRow.innerHTML = '<td colspan="11" style="text-align: center; padding: 40px; color: #666; font-size: 16px;">No data available</td>';
            tableBody.appendChild(noDataRow);
        } else {
            // Reorder DOM elements to match the filtered (and possibly sorted) array
            filteredRows.forEach((row, index) => {
                tableBody.appendChild(row); // Move row to end (reorders in DOM)
                
                // Update row number
                const rowNumCell = row.querySelector('td:first-child');
                if (rowNumCell && !row.classList.contains('no-data-row')) {
                    rowNumCell.textContent = index + 1;
                }
            });
            
            // Hide all rows first
            allRows.forEach(row => row.style.display = "none");
            
            // Show only the paginated filtered rows
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const paginatedRows = filteredRows.slice(start, end);

            paginatedRows.forEach(row => row.style.display = ""); // Show visible rows
        }

        updateShowingInfo((currentPage - 1) * rowsPerPage, (currentPage - 1) * rowsPerPage + rowsPerPage);
        updatePaginationControls();
    }

    function updateShowingInfo(start, end) {
        const totalItems = filteredRows.length;
        if (showingInfo) {
            if (totalItems === 0) {
                showingInfo.textContent = `Showing 0-0 of 0 items`;
            } else {
                showingInfo.textContent = `Showing ${start + 1}-${Math.min(end, totalItems)} of ${totalItems} items`;
            }
        }
    }

    function updatePaginationControls() {
        if (!paginationControls || !prevBtn || !nextBtn) return;
        
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

        // Update the active button text to show the current page number
        const activeBtn = paginationControls.querySelector("button.active"); 
        if (activeBtn) activeBtn.textContent = currentPage;

        // Enable/disable prev/next buttons
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
    }

    function sortTable(column) {
        // Toggle sort direction if clicking the same column
        if (currentSortColumn === column) {
            currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            currentSortColumn = column;
            currentSortDirection = 'asc';
        }

        // Update header icons
        updateSortIcons();

        // Apply sorting to filtered rows
        applySorting();

        // Reset to page 1 after sorting
        currentPage = 1;
        renderRows();
    }

    function updateSortIcons() {
        sortableHeaders.forEach(header => {
            const icon = header.querySelector('i');
            if (header.dataset.sort === currentSortColumn) {
                icon.className = currentSortDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
                header.classList.add('active-sort');
            } else {
                icon.className = 'fas fa-sort';
                header.classList.remove('active-sort');
            }
        });
    }

    function applySorting() {
        if (!currentSortColumn) return;

        // Sort the filtered rows based on current sort column and direction
        filteredRows.sort((a, b) => {
            let aValue, bValue;

            switch(currentSortColumn) {
                case 'order-id':
                    aValue = a.dataset.orderNumber || '';
                    bValue = b.dataset.orderNumber || '';
                    // Extract numeric part for proper numeric sorting
                    const aNum = parseInt(aValue.replace(/[^0-9]/g, '')) || 0;
                    const bNum = parseInt(bValue.replace(/[^0-9]/g, '')) || 0;
                    return currentSortDirection === 'asc' ? aNum - bNum : bNum - aNum;
                
                case 'customer':
                    aValue = (a.dataset.customerName || '').toLowerCase();
                    bValue = (b.dataset.customerName || '').toLowerCase();
                    break;
                
                case 'amount':
                    aValue = parseFloat(a.dataset.price) || 0;
                    bValue = parseFloat(b.dataset.price) || 0;
                    return currentSortDirection === 'asc' ? aValue - bValue : bValue - aValue;
                
                case 'date':
                    aValue = new Date(a.dataset.date || 0).getTime();
                    bValue = new Date(b.dataset.date || 0).getTime();
                    return currentSortDirection === 'asc' ? aValue - bValue : bValue - aValue;
                
                default:
                    return 0;
            }

            // String comparison for customer name
            if (currentSortDirection === 'asc') {
                return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
            } else {
                return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
            }
        });
    }

    // --- Event Listeners ---
    
    // Pagination listeners
    if (prevBtn) {
        prevBtn.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                renderRows();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", () => {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderRows();
            }
        });
    }

    // Filter tab listeners
    filterTabs.forEach(tab => {
        tab.addEventListener("click", () => {
            // 1️⃣ Update active tab style
            filterTabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            // 2️⃣ Filter rows
            filterRows();
        });
    });

    // Sortable header listeners
    sortableHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const sortColumn = header.dataset.sort;
            sortTable(sortColumn);
        });
        // Add hover cursor
        header.style.cursor = 'pointer';
    });

    // Milestone filter listener
    if (milestoneFilter) {
        milestoneFilter.addEventListener('change', () => {
            filterRows(); // Re-filter with new milestone selection
        });
    }

    // --- Initial Load ---
    updateInventoryStats(); // Calculate and display stats immediately
    updateSortIcons(); // Initialize sort icons
    renderRows(); // Initial table display
});
