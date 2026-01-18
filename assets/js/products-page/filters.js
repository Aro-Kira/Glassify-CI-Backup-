document.addEventListener("DOMContentLoaded", () => {
  const allProducts = Array.from(document.querySelectorAll(".product"));
  const paginationContainer = document.querySelector(".pagination");
  const checkboxes = document.querySelectorAll(".filters input[type='checkbox']");
  const activeFiltersContainer = document.querySelector(".active-filters");
  const searchInput = document.querySelector(".search");
  const resultsText = document.querySelector(".search-results p");

  const itemsPerPage = 6;
  let currentPage = 1;
  let filteredProducts = [...allProducts]; // start with all

  // 🔎 Apply filters + search
  function applyFilters() {
    const selected = {
      Category: [],
      Availability: [],
    };

    // collect selected checkboxes - allow multiple selections per group
    checkboxes.forEach(cb => {
      if (cb.checked) {
        const group = cb.closest(".filter-group").querySelector("h4").textContent;
        if (group === "Category") {
          selected.Category.push(cb.value);
        } else if (group === "Availability") {
          selected.Availability.push(cb.value);
        }
      }
    });

    const searchTerm = searchInput.value.trim().toLowerCase();

    // filter products
    filteredProducts = allProducts.filter(product => {
      let show = true;

      // Category filter - check if product matches any selected category
      if (selected.Category.length > 0) {
        const productCategory = (product.dataset.category || "").trim();
        const matchesCategory = selected.Category.some(selectedCat => {
          const normalizedProduct = productCategory.toLowerCase();
          const normalizedSelected = selectedCat.toLowerCase();
          // Exact match or contains match for flexibility
          return normalizedProduct === normalizedSelected || 
                 normalizedProduct.includes(normalizedSelected) || 
                 normalizedSelected.includes(normalizedProduct);
        });
        if (!matchesCategory) {
          show = false;
        }
      }
      
      // Availability filter - check if product matches any selected availability
      if (selected.Availability.length > 0) {
        const productAvailability = (product.dataset.availability || "").trim();
        const matchesAvailability = selected.Availability.includes(productAvailability);
        if (!matchesAvailability) {
          show = false;
        }
      }

      // Search filter
      if (searchTerm) {
        const productText = product.textContent.toLowerCase();
        const productName = product.querySelector('.product-name')?.textContent.toLowerCase() || '';
        if (!productText.includes(searchTerm) && !productName.includes(searchTerm)) {
          show = false;
        }
      }

      return show;
    });

    currentPage = 1;
    showPage(currentPage);
    updateActiveFilters();
  }

  // 📄 Show products for given page
  function showPage(page) {
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;

    allProducts.forEach(product => (product.style.display = "none")); // hide all

    filteredProducts.forEach((product, index) => {
      if (index >= start && index < end) {
        product.style.display = "flex";
      }
    });

    updatePagination(page);
    updateResultsCount(start, end);
  }

  // 🔢 Update pagination
  function updatePagination(page) {
    paginationContainer.innerHTML = "";
    const totalPages = Math.ceil(filteredProducts.length / itemsPerPage) || 1;

    // Prev
    const prev = document.createElement("a");
    prev.href = "#";
    prev.textContent = "<";
    if (page === 1) prev.classList.add("disabled");
    prev.addEventListener("click", e => {
      e.preventDefault();
      if (currentPage > 1) {
        currentPage--;
        showPage(currentPage);
      }
    });
    paginationContainer.appendChild(prev);

    // Numbers
    for (let i = 1; i <= totalPages; i++) {
      const link = document.createElement("a");
      link.href = "#";
      link.textContent = i;
      if (i === page) link.classList.add("active");
      link.addEventListener("click", e => {
        e.preventDefault();
        currentPage = i;
        showPage(currentPage);
      });
      paginationContainer.appendChild(link);
    }

    // Next
    const next = document.createElement("a");
    next.href = "#";
    next.textContent = ">";
    if (page === totalPages) next.classList.add("disabled");
    next.addEventListener("click", e => {
      e.preventDefault();
      if (currentPage < totalPages) {
        currentPage++;
        showPage(currentPage);
      }
    });
    paginationContainer.appendChild(next);
  }

  // 📊 Update results text
  function updateResultsCount(start, end) {
    const total = filteredProducts.length;
    if (total === 0) {
      resultsText.textContent = "Showing 0 results";
    } else {
      const from = start + 1;
      const to = Math.min(end, total);
      resultsText.textContent = `Showing ${from}-${to} of ${total} results`;
    }
  }

  // 🏷 Active filter tags
  function updateActiveFilters() {
    activeFiltersContainer.innerHTML = "<h4>Active Filters:</h4>";
    let hasFilter = false;

    checkboxes.forEach(checkbox => {
      if (checkbox.checked) {
        hasFilter = true;
        const tag = document.createElement("span");
        tag.className = "filter-tag";
        tag.textContent = checkbox.parentNode.textContent.trim();

        tag.addEventListener("click", () => {
          checkbox.checked = false;
          applyFilters();
        });

        activeFiltersContainer.appendChild(tag);
      }
    });

    if (hasFilter) {
      const clearAll = document.createElement("span");
      clearAll.className = "clear";
      clearAll.textContent = "Clear All";

      clearAll.addEventListener("click", () => {
        checkboxes.forEach(cb => (cb.checked = false));
        searchInput.value = "";
        filteredProducts = [...allProducts];
        currentPage = 1;
        showPage(currentPage);
        updateActiveFilters();
      });

      activeFiltersContainer.appendChild(clearAll);
    }
  }

  // ✅ Allow multiple selections per group (removed single-selection restriction)
  checkboxes.forEach(checkbox => {
    checkbox.addEventListener("change", e => {
      applyFilters();
    });
  });

  // 🔍 Search input
  searchInput.addEventListener("input", () => {
    applyFilters();
  });

  // Init
  applyFilters();
});
