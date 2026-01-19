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
                    // Build image path - handle JSON array or single string
                    $image_raw = $product->ImageUrl ?? '';
                    $placeholder_svg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                    $image_path = $placeholder_svg;
                    
                    if (!empty($image_raw)) {
                        $decoded = json_decode($image_raw, true);
                        $first_image = '';
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                            $first_image = $decoded[0];
                        } else {
                            $first_image = $image_raw;
                        }
                        
                        if (!empty($first_image) && strpos($first_image, 'broken-image-icon') === false) {
                            if (strpos($first_image, 'http') === 0) {
                                $image_path = $first_image;
                            } else if (strpos($first_image, 'assets/') === 0 || strpos($first_image, 'uploads/') === 0) {
                                $image_path = base_url($first_image);
                            } else {
                                $image_path = base_url('uploads/products/' . basename($first_image));
                            }
                        }
                    }
            ?>
            <div class="product-card" data-category="<?php echo htmlspecialchars($product->Category); ?>" data-status="<?php echo htmlspecialchars($product->Status ?? 'Out of Stock'); ?>">
                <div class="product-image">
                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product->ProductName); ?>" onerror="if(this.src.indexOf('data:image') === -1) { this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4='; }">
                </div>
                <p class="product-name"><?php echo htmlspecialchars($product->ProductName); ?></p>
                <p class="product-price">₱<?php echo number_format($product->Price, 2); ?></p>
                <?php
                  $status = isset($product->Status) ? $product->Status : 'Out of Stock';
                  $status_class = '';
                  $status_color = '';
                  if ($status === 'In Stock') {
                    $status_class = 'badge-in-stock';
                    $status_color = '#4CAF50';
                  } elseif ($status === 'Low Stock') {
                    $status_class = 'badge-low-stock';
                    $status_color = '#FF9800';
                  } else {
                    $status_class = 'badge-out-stock';
                    $status_color = '#f44336';
                  }
                ?>
                <span class="product-status-badge <?= $status_class; ?>" style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; color: white; background-color: <?= $status_color; ?>; margin-top: 8px;">
                  <?= htmlspecialchars($status); ?>
                </span>
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
