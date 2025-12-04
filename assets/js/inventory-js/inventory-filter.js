document.addEventListener("DOMContentLoaded", () => {
  const rowsPerPageSelect = document.getElementById("rowsPerPageSelect");
  const paginationControls = document.querySelector(".pagination-controls");
  const tableBody = document.querySelector("table tbody");
  const showingInfo = document.getElementById("paginationInfo");
  const searchInput = document.getElementById("inventorySearch");

  if (!tableBody) return;

  // Get all existing rows from PHP-rendered table
  let allRows = Array.from(tableBody.querySelectorAll("tr"));
  let filteredRows = [...allRows];
  let currentPage = 1;
  let rowsPerPage = parseInt(rowsPerPageSelect?.value) || 5;

  function updateTable() {
    // Hide all rows first
    allRows.forEach(row => {
      row.style.display = "none";
    });

    // Show filtered rows for current page
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const visibleRows = filteredRows.slice(start, end);

    // Show visible rows
    visibleRows.forEach(row => {
      row.style.display = "";
    });

    // Update row numbers based on filtered results
    visibleRows.forEach((row, index) => {
      const firstCell = row.querySelector("td:first-child");
      if (firstCell) {
        firstCell.textContent = start + index + 1;
      }
    });

    updateShowingInfo(start, end);
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    renderPaginationControls(totalPages);
  }

  function updateShowingInfo(start, end) {
    if (!showingInfo) return;
    const total = filteredRows.length;
    const startNum = total > 0 ? start + 1 : 0;
    const endNum = Math.min(end, total);
    showingInfo.textContent = `Showing ${startNum}-${endNum} of ${total} items`;
  }

  function renderPaginationControls(totalPages) {
    if (!paginationControls) return;
    
    paginationControls.innerHTML = "";

    if (totalPages === 0) {
      return;
    }

    // Prev button
    const prevBtn = document.createElement("button");
    prevBtn.id = "prevPage";
    prevBtn.innerHTML = `<i class="fas fa-chevron-left"></i>`;
    prevBtn.disabled = currentPage === 1;
    prevBtn.addEventListener("click", () => {
      if (currentPage > 1) {
        currentPage--;
        updateTable();
      }
    });
    paginationControls.appendChild(prevBtn);

    // Page numbers (show up to 3 pages with sliding window)
    const maxVisiblePages = 3;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage < maxVisiblePages - 1) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    // First page
    if (startPage > 1) {
      const firstBtn = document.createElement("button");
      firstBtn.textContent = "1";
      firstBtn.addEventListener("click", () => {
        currentPage = 1;
        updateTable();
      });
      paginationControls.appendChild(firstBtn);
      
      if (startPage > 2) {
        const ellipsis = document.createElement("span");
        ellipsis.textContent = "...";
        ellipsis.style.padding = "0 5px";
        paginationControls.appendChild(ellipsis);
      }
    }

    // Page number buttons
    for (let i = startPage; i <= endPage; i++) {
      const pageBtn = document.createElement("button");
      pageBtn.textContent = i;
      if (i === currentPage) {
        pageBtn.classList.add("active");
      }
      pageBtn.addEventListener("click", () => {
        currentPage = i;
        updateTable();
      });
      paginationControls.appendChild(pageBtn);
    }

    // Last page
    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        const ellipsis = document.createElement("span");
        ellipsis.textContent = "...";
        ellipsis.style.padding = "0 5px";
        paginationControls.appendChild(ellipsis);
      }
      
      const lastBtn = document.createElement("button");
      lastBtn.textContent = totalPages;
      lastBtn.addEventListener("click", () => {
        currentPage = totalPages;
        updateTable();
      });
      paginationControls.appendChild(lastBtn);
    }

    // Next button
    const nextBtn = document.createElement("button");
    nextBtn.id = "nextPage";
    nextBtn.innerHTML = `<i class="fas fa-chevron-right"></i>`;
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.addEventListener("click", () => {
      if (currentPage < totalPages) {
        currentPage++;
        updateTable();
      }
    });
    paginationControls.appendChild(nextBtn);
  }

  // Filter function - filter by item name only
  function filterItems() {
    const searchTerm = searchInput?.value.toLowerCase().trim() || "";
    
    if (!searchTerm) {
      filteredRows = [...allRows];
    } else {
      filteredRows = allRows.filter(row => {
        const cells = row.querySelectorAll("td");
        if (cells.length < 3) return false;
        
        // Get item name from the 3rd column (Name column)
        const nameCell = cells[2];
        if (!nameCell) return false;
        
        // Get text content, but exclude badge text
        let nameText = nameCell.textContent || "";
        // Remove badge text if present (badges are separate elements)
        const badge = nameCell.querySelector(".status-badge");
        if (badge) {
          nameText = nameText.replace(badge.textContent, "").trim();
        }
        
        return nameText.toLowerCase().includes(searchTerm);
      });
    }
    
    currentPage = 1; // Reset to first page after filtering
    updateTable();
  }

  // Handle search input
  if (searchInput) {
    searchInput.addEventListener("input", filterItems);
  }

  // Handle rows per page change
  if (rowsPerPageSelect) {
    // Set default to 5 if not already set
    if (!rowsPerPageSelect.value) {
      rowsPerPageSelect.value = "5";
      rowsPerPage = 5;
    }
    
    rowsPerPageSelect.addEventListener("change", (e) => {
      rowsPerPage = parseInt(e.target.value, 10) || 5;
      currentPage = 1; // reset to first page
      updateTable();
    });
  }

  // Initial render - show only 5 rows per page by default
  updateTable();
});
