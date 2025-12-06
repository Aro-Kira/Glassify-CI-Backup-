<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlassWorth BUILDERS - Products</title>
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
                <a href="inventory.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                        <path d="M9 9h6v6H9z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Inventory</span>
                </a>
                <a href="products.php" class="nav-item active">
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
            <h1 class="page-title">Products</h1>

            <!-- Search and Filter Bar -->
            <div class="products-filter-bar">
                <div class="products-search-wrapper">
                    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input type="text" class="products-search-input" id="productSearchInput" placeholder="Search products" oninput="filterProducts()">
                </div>
                <div class="products-filter-dropdowns">
                    <select class="products-filter-select" id="categoryFilter" onchange="filterProducts()">
                        <option value="all">Category</option>
                        <option value="Building Materials">Building Materials</option>
                        <option value="Finishing">Finishing</option>
                        <option value="Lumber">Lumber</option>
                        <option value="Hardware">Hardware</option>
                        <option value="Glass">Glass</option>
                        <option value="Aluminum">Aluminum</option>
                    </select>
                    <select class="products-filter-select" id="lastAddedFilter" onchange="filterProducts()">
                        <option value="all">Last Added</option>
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid" id="productsGrid">
                <!-- Product cards will be generated here -->
            </div>

            <!-- Pagination -->
            <div class="products-pagination" id="productsPagination">
                <button class="pagination-btn" id="prevPageBtn" onclick="goToPreviousProductPage()" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="pagination-pages" id="paginationPages">
                    <!-- Page numbers will be generated here -->
                </div>
                <button class="pagination-btn" id="nextPageBtn" onclick="goToNextProductPage()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
    <script>
        // Products data loaded from database
        let productsData = [];
        let filteredProducts = [];
        let currentProductPage = 1;
        const productsPerPage = 8;

        // Load products from database
        function loadProductsFromDatabase() {
            fetch('api/get_products.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        productsData = data.data;
                        
                        // Re-apply current filters after loading
                        const searchInput = document.getElementById('productSearchInput');
                        const categoryFilter = document.getElementById('categoryFilter');
                        const lastAddedFilter = document.getElementById('lastAddedFilter');
                        
                        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
                        const category = categoryFilter ? categoryFilter.value : 'all';
                        const sortOrder = lastAddedFilter ? lastAddedFilter.value : 'all';
                        
                        // Filter by search and category
                        filteredProducts = productsData.filter(product => {
                            const matchesSearch = !searchTerm || product.name.toLowerCase().includes(searchTerm);
                            const matchesCategory = category === 'all' || product.category === category;
                            return matchesSearch && matchesCategory;
                        });
                        
                        // Sort by date if needed
                        if (sortOrder === 'newest') {
                            filteredProducts.sort((a, b) => new Date(b.addedDate) - new Date(a.addedDate));
                        } else if (sortOrder === 'oldest') {
                            filteredProducts.sort((a, b) => new Date(a.addedDate) - new Date(b.addedDate));
                        }
                        
                        // Reset to first page if current page is out of bounds
                        const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
                        if (currentProductPage > totalPages && totalPages > 0) {
                            currentProductPage = 1;
                        }
                        
                        renderProducts();
                        updatePagination();
                    } else {
                        console.error('Error loading products:', data.message);
                        const grid = document.getElementById('productsGrid');
                        if (grid) {
                            grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #f44336;">Error loading products. Please check your connection.</div>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching products:', error);
                    const grid = document.getElementById('productsGrid');
                    if (grid) {
                        grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #f44336;">Error loading products. Please check your connection.</div>';
                    }
                });
        }

        // Initialize products display
        document.addEventListener('DOMContentLoaded', function() {
            // Load products from database first
            loadProductsFromDatabase();
            
            // Auto-refresh products every 30 seconds for real-time updates
            setInterval(function() {
                loadProductsFromDatabase();
            }, 30000); // Refresh every 30 seconds
        });

        function renderProducts() {
            const grid = document.getElementById('productsGrid');
            if (!grid) return;

            if (filteredProducts.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">No products found</div>';
                return;
            }

            const startIndex = (currentProductPage - 1) * productsPerPage;
            const endIndex = startIndex + productsPerPage;
            const productsToShow = filteredProducts.slice(startIndex, endIndex);

            grid.innerHTML = '';

            productsToShow.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-card';
                
                // Use product image if available, otherwise show placeholder
                let imageHTML = '';
                if (product.image && product.image.trim() !== '') {
                    imageHTML = `<img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">`;
                } else {
                    imageHTML = `
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="#999" stroke-width="2"/>
                            <path d="M8 12h8M12 8v8" stroke="#999" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    `;
                }
                
                productCard.innerHTML = `
                    <div class="product-image">
                        ${imageHTML}
                    </div>
                    <div class="product-name">${escapeHtml(product.name)}</div>
                    <div class="product-price">₱${product.price.toFixed(2)}</div>
                `;

                grid.appendChild(productCard);
            });
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function filterProducts() {
            const searchInput = document.getElementById('productSearchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const lastAddedFilter = document.getElementById('lastAddedFilter');

            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const category = categoryFilter ? categoryFilter.value : 'all';
            const sortOrder = lastAddedFilter ? lastAddedFilter.value : 'all';

            // Filter by search and category
            filteredProducts = productsData.filter(product => {
                const matchesSearch = !searchTerm || product.name.toLowerCase().includes(searchTerm);
                const matchesCategory = category === 'all' || product.category === category;
                return matchesSearch && matchesCategory;
            });

            // Sort by date if needed
            if (sortOrder === 'newest') {
                filteredProducts.sort((a, b) => new Date(b.addedDate) - new Date(a.addedDate));
            } else if (sortOrder === 'oldest') {
                filteredProducts.sort((a, b) => new Date(a.addedDate) - new Date(b.addedDate));
            }

            // Reset to first page
            currentProductPage = 1;
            renderProducts();
            updatePagination();
        }

        function updatePagination() {
            const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
            const paginationPages = document.getElementById('paginationPages');
            const prevBtn = document.getElementById('prevPageBtn');
            const nextBtn = document.getElementById('nextPageBtn');

            if (!paginationPages) return;

            paginationPages.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = 'pagination-page-btn';
                if (i === currentProductPage) {
                    pageBtn.className += ' active';
                }
                pageBtn.textContent = i;
                pageBtn.onclick = () => goToProductPage(i);
                paginationPages.appendChild(pageBtn);
            }

            // Update prev/next buttons
            if (prevBtn) {
                prevBtn.disabled = currentProductPage === 1;
            }
            if (nextBtn) {
                nextBtn.disabled = currentProductPage === totalPages || totalPages === 0;
            }
        }

        function goToProductPage(page) {
            currentProductPage = page;
            renderProducts();
            updatePagination();
        }

        function goToPreviousProductPage() {
            if (currentProductPage > 1) {
                goToProductPage(currentProductPage - 1);
            }
        }

        function goToNextProductPage() {
            const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
            if (currentProductPage < totalPages) {
                goToProductPage(currentProductPage + 1);
            }
        }

        // Make functions globally accessible
        window.filterProducts = filterProducts;
        window.goToProductPage = goToProductPage;
        window.goToPreviousProductPage = goToPreviousProductPage;
        window.goToNextProductPage = goToNextProductPage;
    </script>
</body>
</html>
