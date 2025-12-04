<!-- Products Section -->
<section class="products-section-main">
    <div class="section-header">
        <h1 class="page-title">Products</h1>
    </div>

    <!-- Filters -->

    <div class="controls-container">
        <div class="search-bar">
            <input type="text" placeholder="Filter by name or category..." class="search-input">
            <button class="search-button">Search</button>
        </div>
        <div class="controls-right">
            <select class="filter-category">
                <option value="">All Category</option>
                <?php foreach ($categories as $category): 
                    // Display category name exactly as stored in database - no modifications
                    $display_name = $category;
                ?>
                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($display_name); ?></option>
                <?php endforeach; ?>
            </select>

        </div>
    </div>


    <!-- Products Table -->
    <div class="table-container">
        <div class="product-grid">
            <?php if (!empty($products)): 
                foreach ($products as $product): 
                    // Build image path
                    $image_path = '';
                    if ($product->ImageUrl) {
                        if (strpos($product->ImageUrl, 'http://') === 0 || strpos($product->ImageUrl, 'https://') === 0) {
                            // Full URL
                            $image_path = $product->ImageUrl;
                        } elseif (strpos($product->ImageUrl, 'uploads/') === 0 || strpos($product->ImageUrl, '/uploads/') === 0) {
                            // Already has uploads path
                            $image_path = base_url($product->ImageUrl);
                        } elseif (strpos($product->ImageUrl, 'assets/') === 0 || strpos($product->ImageUrl, '/assets/') === 0) {
                            // Assets path
                            $image_path = base_url($product->ImageUrl);
                        } else {
                            // Just filename - assume it's in uploads/products/
                            $image_path = base_url('uploads/products/' . $product->ImageUrl);
                        }
                    } else {
                        // Use a simple data URI as placeholder to avoid 404 errors
                        $image_path = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                    }
            ?>
            <div class="product-card" data-category="<?php echo htmlspecialchars($product->Category); ?>">
                <div class="product-image">
                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product->ProductName); ?>" onerror="if(this.src.indexOf('data:image') === -1) { this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4='; }">
                </div>
                <p class="product-name"><?php echo htmlspecialchars($product->ProductName); ?></p>
                <p class="product-price">₱<?php echo number_format($product->Price, 2); ?></p>
            </div>
            <?php 
                endforeach; 
            else: 
            ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <p>No products found</p>
            </div>
            <?php endif; ?>
        </div>
    </div>


    <!-- Pagination -->
    <div class="pagination">
        <span></span>
        <div class="pagination-controls">
            <button><i class="fas fa-chevron-left"></i></button>
            <button class="active"></button>
            <button><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</section>


<script src="<?php echo base_url('assets/js/sales-js/product-filter.js'); ?>"></script>
