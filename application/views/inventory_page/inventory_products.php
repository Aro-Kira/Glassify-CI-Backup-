

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
          <option value="Mirrors">Mirrors</option>
          <option value="Shower Enclosure / Partition">Shower Enclosure / Partition</option>
          <option value="Aluminum Doors">Aluminum Doors</option>
          <option value="Stair Railings">Stair Railings</option>
          <option value="Windows">Windows</option>
          <option value="Glass Partition">Glass Partition</option>
        </select>

      </div>
    </div>


    <!-- Products Table -->
    <div class="table-container">
      <div class="product-grid">
        <div class="product-card" data-category="cabinet">
          <div class="product-image"><img src="<?php echo base_url('assets/images/img_admin/cabinet.jpg'); ?>" alt="Cabinet"></div>
          <p class="product-name">Glass Cabinet</p>
          <p class="product-price">₱1,100.00</p>

        </div>
        <div class="product-card" data-category="shower-enclosure">
          <div class="product-image"><img src="<?php echo base_url('assets/images/img_admin/french-shower-enclosure.jpg'); ?>"
              alt="Shower Enclosure"></div>
          <p class="product-name">French Type Fixed Shower Enclosure</p>
          <p class="product-price">₱5,200.00</p>

        </div>
        <div class="product-card" data-category="doors">
          <div class="product-image"><img src="<?php echo base_url('assets/images/img_admin/aluminum-screen.jpg'); ?>" alt="Aluminum Screen">
          </div>
          <p class="product-name">Aluminum Screen Door</p>
          <p class="product-price">₱4,100.00</p>

        </div>
        <div class="product-card" data-category="cabinet">
          <div class="product-image"><img src="<?php echo base_url('assets/images/img_admin/aluminum-cabinet.jpg'); ?>" alt="Kitchen Cabinet">
          </div>
          <p class="product-name">Aluminum Kitchen Cabinet</p>
          <p class="product-price">₱3,100.00</p>

        </div>
        <div class="product-card" data-category="windows">
          <div class="product-image"><img src="<?php echo base_url('assets/images/img_admin/sliding-window.jpg'); ?>" alt="Sliding Window">
          </div>
          <p class="product-name">Sliding Window</p>
          <p class="product-price">₱2,000.00</p>

        </div>
        <div class="product-card" data-category="mirrors">
          <div class="product-image"><img src="<?php echo base_url('assets/images/img_admin/arched frameless.jpg'); ?>" alt="Arched Frameless">
          </div>
          <p class="product-name">Arched Frameless</p>
          <p class="product-price">₱1,200.00</p>

        </div>
        <div class="product-card" data-category="windows">
          <div class="product-image"><img src="<?php echo base_url('assets/images/img_admin/corner-glass.jpg'); ?>" alt="Corner Fixed Glass">
          </div>
          <p class="product-name">Corner Fixed Glass</p>
          <p class="product-price">₱10,000.00</p>

        </div>
        <div class="product-card" data-category="doors">
          <div class="product-image"><img src="<?php echo base_url('assets/images/img_admin/french-type-door.jpg'); ?>" alt="Sliding Door">
          </div>
          <p class="product-name">900 Series French Type Sliding Door</p>
          <p class="product-price">₱5,000.00</p>

        </div>
      </div>
    </div>


    <!-- Pagination -->
    <div class="pagination">
      <span>Showing 1-4 of 255 items</span>
      <div class="pagination-controls">
        <button><i class="fas fa-chevron-left"></i></button>
        <button class="active">1</button>
        <button><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  </section>






  <!-- Only load scripts that exist -->
  <script src="<?php echo base_url('assets/js/includes/sidebar.js'); ?>"></script>
  <script src="<?php echo base_url('assets/js/admin-js/products.js'); ?>"></script>
  <script src="<?php echo base_url('assets/js/sales-js/product-filter.js'); ?>"></script>