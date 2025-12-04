document.addEventListener("DOMContentLoaded", () => {
    const searchBox = document.querySelector(".search-input");
    const searchBtn = document.querySelector(".search-button");
    const categoryFilter = document.querySelector(".filter-category");
    const productCards = document.querySelectorAll(".product-card");
    const showingInfo = document.querySelector(".pagination span");
    const paginationControls = document.querySelector(".pagination-controls");
    const prevBtn = paginationControls.querySelector("button:first-child");
    const nextBtn = paginationControls.querySelector("button:last-child");

    let currentPage = 1;
    const rowsPerPage = 8; // Show 8 products per page
    let filteredCards = Array.from(productCards); // Start with all cards

    function filterProducts() {
        const searchTerm = searchBox.value.toLowerCase();
        const selectedCategory = categoryFilter.value.toLowerCase();

        filteredCards = Array.from(productCards).filter(card => {
            const productName = card.querySelector(".product-name").textContent.toLowerCase();
            const productCategory = card.dataset.category.toLowerCase();

            const matchesSearch = productName.includes(searchTerm);
            const matchesCategory = selectedCategory === "" || productCategory === selectedCategory;

            return matchesSearch && matchesCategory;
        });

        currentPage = 1; // Reset to page 1 on filter
        renderProducts();
    }

    function renderProducts() {
        // Hide all cards first
        productCards.forEach(card => card.style.display = "none");

        // Show only the paginated filtered cards
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedCards = filteredCards.slice(start, end);

        paginatedCards.forEach(card => card.style.display = ""); // Show visible cards

        updateShowingInfo(start, end);
        updatePaginationControls();
    }

    function updateShowingInfo(start, end) {
        const totalItems = filteredCards.length;
        if (totalItems === 0) {
            showingInfo.textContent = `Showing 0-0 of 0 items`;
        } else {
            showingInfo.textContent = `Showing ${start + 1}-${Math.min(end, totalItems)} of ${totalItems} items`;
        }
    }

    function updatePaginationControls() {
        const totalPages = Math.ceil(filteredCards.length / rowsPerPage);

        // Update the active button text to show the current page number
        const activeBtn = paginationControls.querySelector("button.active");
        activeBtn.textContent = currentPage;

        // Enable/disable prev/next buttons
        const prevBtn = paginationControls.querySelector("button:first-child");
        const nextBtn = paginationControls.querySelector("button:last-child");
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;

        // Controls always show (removed hiding for 1 page)
    }

    // Event listeners for pagination
    prevBtn.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            renderProducts();
        }
    });

    nextBtn.addEventListener("click", () => {
        const totalPages = Math.ceil(filteredCards.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderProducts();
        }
    });

    // Filter when clicking the search button
    searchBtn.addEventListener("click", filterProducts);

    // Filter when pressing Enter in the search box
    searchBox.addEventListener("keyup", (e) => {
        if (e.key === "Enter") filterProducts();
    });

    // Filter when changing category
    categoryFilter.addEventListener("change", filterProducts);

    // Auto-clear: Show all products when search input is cleared
    searchBox.addEventListener("input", () => {
        if (searchBox.value.trim() === "") {
            filterProducts(); // Show all when input is empty
        }
    });

    // Initial render
    renderProducts();
});
