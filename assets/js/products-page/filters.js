document.addEventListener("DOMContentLoaded", () => {
  const allProducts = Array.from(document.querySelectorAll(".product"));
  const paginationContainer = document.querySelector(".pagination");
  const checkboxes = document.querySelectorAll(".filters input[type='checkbox']");
  const categoryRadios = document.querySelectorAll(".filters input[name='category']");
  const subcategoryCheckboxes = document.querySelectorAll(".filters input[name='subcategory']");
  const activeFiltersContainer = document.querySelector(".active-filters");
  const searchInput = document.querySelector(".search");
  const resultsText = document.querySelector(".search-results p");
  const subcategoriesGroup = document.getElementById("subcategories-group");
  const subcategoriesContainer = document.getElementById("subcategories-container");

  const itemsPerPage = 6;
  let currentPage = 1;
  let filteredProducts = [...allProducts]; // start with all

  // Subcategories data (will be populated from PHP)
  const subcategoriesData = window.subcategoriesByCategory || {};

  // 🔎 Apply filters + search
  function applyFilters() {
    const selected = {
      Category: [],
      Subcategory: [],
      Availability: [],
    };

    // collect selected category radio button
    const selectedCategoryRadio = document.querySelector(".filters input[name='category']:checked");
    if (selectedCategoryRadio) {
      selected.Category.push(selectedCategoryRadio.value);
    }

    // collect selected subcategory checkboxes
    subcategoryCheckboxes.forEach(cb => {
      if (cb.checked) {
        selected.Subcategory.push(cb.value);
      }
    });

    // collect selected availability checkboxes
    checkboxes.forEach(cb => {
      if (cb.checked) {
        const group = cb.closest(".filter-group").querySelector("h4").textContent;
        if (group === "Availability") {
          selected.Availability.push(cb.value);
        }
      }
    });

    const searchTerm = searchInput.value.trim().toLowerCase();

    // filter products
    filteredProducts = allProducts.filter(product => {
      let show = true;

      // Category filter - check if product matches selected category
      if (selected.Category.length > 0) {
        const productCategory = (product.dataset.category || "").trim();
        const selectedCategory = selected.Category[0]; // Only one category can be selected
        const normalizedProduct = productCategory.toLowerCase();
        const normalizedSelected = selectedCategory.toLowerCase();
        const matchesCategory = normalizedProduct === normalizedSelected ||
                               normalizedProduct.includes(normalizedSelected) ||
                               normalizedSelected.includes(normalizedProduct);
        if (!matchesCategory) {
          show = false;
        }
      }

      // Subcategory filter - check if product matches any selected subcategory
      if (selected.Subcategory.length > 0) {
        const productSubcategory = (product.dataset.subcategory || "").trim();
        const matchesSubcategory = selected.Subcategory.includes(productSubcategory);
        if (!matchesSubcategory) {
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

    // Handle category radio button
    const selectedCategoryRadio = document.querySelector(".filters input[name='category']:checked");
    if (selectedCategoryRadio) {
      hasFilter = true;
      const tag = document.createElement("span");
      tag.className = "filter-tag";
      tag.textContent = selectedCategoryRadio.parentNode.textContent.trim();

      tag.addEventListener("click", () => {
        selectedCategoryRadio.checked = false;
        handleCategoryChange();
      });

      activeFiltersContainer.appendChild(tag);
    }

    // Handle subcategory checkboxes
    const subcategoryCheckboxes = document.querySelectorAll(".filters input[name='subcategory']");
    subcategoryCheckboxes.forEach(checkbox => {
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

    // Handle availability checkboxes
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
        // Clear category radio
        if (selectedCategoryRadio) {
          selectedCategoryRadio.checked = false;
        }
        // Clear subcategory checkboxes
        subcategoryCheckboxes.forEach(cb => (cb.checked = false));
        // Clear availability checkboxes
        checkboxes.forEach(cb => (cb.checked = false));
        searchInput.value = "";
        filteredProducts = [...allProducts];
        currentPage = 1;
        showPage(currentPage);
        handleCategoryChange(); // This will hide subcategories and update filters
      });

      activeFiltersContainer.appendChild(clearAll);
    }
  }

  // 🔘 Handle category radio button changes
  function handleCategoryChange() {
    const selectedCategory = document.querySelector(".filters input[name='category']:checked");
    if (selectedCategory) {
      const categoryValue = selectedCategory.value;

      // Show subcategories group
      subcategoriesGroup.style.display = "block";

      // Populate subcategories
      const subcategories = subcategoriesData[categoryValue] || [];
      subcategoriesContainer.innerHTML = "";

      if (subcategories.length > 0) {
        subcategories.forEach(subcategory => {
          const label = document.createElement("label");
          label.innerHTML = `<input type="checkbox" name="subcategory" value="${subcategory}"> ${subcategory}`;
          subcategoriesContainer.appendChild(label);
        });

        // Update subcategory checkboxes reference
        const newSubcategoryCheckboxes = document.querySelectorAll(".filters input[name='subcategory']");

        // Add event listeners to new subcategory checkboxes
        newSubcategoryCheckboxes.forEach(cb => {
          cb.addEventListener("change", () => {
            applyFilters();
          });
        });
      } else {
        subcategoriesContainer.innerHTML = "<p style='color: #666; font-style: italic;'>No subcategories available</p>";
      }
    } else {
      // Hide subcategories when no category is selected
      subcategoriesGroup.style.display = "none";
    }

    applyFilters();
  }

  // Add event listeners to category radio buttons
  categoryRadios.forEach(radio => {
    radio.addEventListener("change", handleCategoryChange);
  });

  // ✅ Allow multiple selections for availability checkboxes
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
