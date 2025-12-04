/**
 * Inventory Filter and Pagination
 */

document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('tableBody');
    const searchInput = document.getElementById('inventorySearch');
    const searchButton = document.getElementById('searchButton');
    const rowsPerPageSelect = document.getElementById('rowsPerPageSelect');
    const paginationInfo = document.getElementById('paginationInfo');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    
    let allRows = [];
    let filteredRows = [];
    let currentPage = 1;
    let rowsPerPage = parseInt(rowsPerPageSelect.value) || 5;
    
    // Initialize: Get all rows from table
    function initializeRows() {
        allRows = Array.from(tableBody.querySelectorAll('tr'));
        filteredRows = [...allRows];
        renderTable();
    }
    
    // Filter rows based on search
    function filterRows() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        if (!searchTerm) {
            filteredRows = [...allRows];
        } else {
            filteredRows = allRows.filter(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length === 0) return false; // Skip empty rows
                
                const itemId = cells[1]?.textContent.toLowerCase() || '';
                const name = cells[2]?.textContent.toLowerCase() || '';
                const category = cells[3]?.textContent.toLowerCase() || '';
                
                return itemId.includes(searchTerm) || 
                       name.includes(searchTerm) || 
                       category.includes(searchTerm);
            });
        }
        
        currentPage = 1;
        renderTable();
    }
    
    // Render table with pagination
    function renderTable() {
        // Clear table
        tableBody.innerHTML = '';
        
        if (filteredRows.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="6" style="text-align: center; padding: 20px;">No items found.</td>';
            tableBody.appendChild(row);
            updatePaginationInfo(0);
            return;
        }
        
        // Calculate pagination
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        const paginatedRows = filteredRows.slice(startIndex, endIndex);
        
        // Render rows
        paginatedRows.forEach(row => {
            tableBody.appendChild(row.cloneNode(true));
        });
        
        // Update pagination info
        updatePaginationInfo(filteredRows.length);
        updatePaginationButtons();
    }
    
    // Update pagination info
    function updatePaginationInfo(total) {
        const start = total === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
        const end = Math.min(currentPage * rowsPerPage, total);
        paginationInfo.textContent = `Showing ${start}-${end} of ${total} items`;
    }
    
    // Update pagination buttons
    function updatePaginationButtons() {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        
        // Update prev/next buttons
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage >= totalPages;
        
        // Update page number buttons (simplified - just show current page)
        const pageButtons = document.querySelectorAll('.pagination-controls button:not(#prevPage):not(#nextPage)');
        pageButtons.forEach(btn => {
            if (btn.id === 'page1') {
                btn.textContent = currentPage;
                btn.classList.toggle('active', true);
            } else {
                btn.remove();
            }
        });
    }
    
    // Event listeners
    searchInput.addEventListener('input', filterRows);
    searchButton.addEventListener('click', filterRows);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            filterRows();
        }
    });
    
    rowsPerPageSelect.addEventListener('change', function() {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        renderTable();
    });
    
    prevPageBtn.addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });
    
    nextPageBtn.addEventListener('click', function() {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });
    
    // Initialize
    initializeRows();
});

