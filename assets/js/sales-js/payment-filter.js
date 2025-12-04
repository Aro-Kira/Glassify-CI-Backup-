document.addEventListener("DOMContentLoaded", () => {
    // --- Selectors ---
    const filterTabs = document.querySelectorAll(".filter-tab");
    const tableBody = document.querySelector(".payment-table tbody");
    const rows = tableBody ? Array.from(tableBody.querySelectorAll("tr")) : [];

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


    // --- Core Helper Functions ---

    function getStatus(row) {
        // Safely extract the status class from the status-badge element
        const statusElement = row.querySelector(".status-badge");
        if (!statusElement) return '';
        
        // Find the status class: 'pending', 'paid', 'overdue', 'review', etc.
        const classes = Array.from(statusElement.classList);
        const status = classes.find(cls => ['pending', 'paid', 'overdue', 'review'].includes(cls));
        return status || statusElement.textContent.trim().toLowerCase();
    }

    function updateInventoryStats() {
        const counts = {
            all: allRows.length,
            pending: 0,
            paid: 0,
            review: 0, // 'Under Review' status
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
            // Use data-status if you updated the HTML as recommended, otherwise use textContent
            filter = activeTab.getAttribute('data-status') || activeTab.textContent.trim().toLowerCase().replace('under review', 'review');
        }

        // Filter rows
        filteredRows = allRows.filter(row => {
            const status = getStatus(row);
            return filter === "all" || status === filter;
        });

        currentPage = 1; // Reset to page 1 on filter
        renderRows();
    }

    function renderRows() {
        // Hide all rows first
        allRows.forEach(row => row.style.display = "none");

        // Check if there are any filtered rows
        if (filteredRows.length === 0) {
            // Remove existing "no data" row if it exists
            const existingNoDataRow = tableBody.querySelector('tr.no-data-row');
            if (existingNoDataRow) {
                existingNoDataRow.remove();
            }
            
            // Create and show "no data" message
            const noDataRow = document.createElement('tr');
            noDataRow.className = 'no-data-row';
            noDataRow.innerHTML = '<td colspan="9" style="text-align: center; padding: 40px; color: #666; font-size: 16px;">No data available</td>';
            tableBody.appendChild(noDataRow);
        } else {
            // Remove "no data" row if it exists
            const existingNoDataRow = tableBody.querySelector('tr.no-data-row');
            if (existingNoDataRow) {
                existingNoDataRow.remove();
            }
            
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

    // --- Initial Load ---
    updateInventoryStats(); // Calculate and display stats immediately
    renderRows(); // Initial table display
});