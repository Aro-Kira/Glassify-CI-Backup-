<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/products_style.css'); ?>">


<!-- MAIN -->
<main>
  <section class="page-title">
    <h1>Products & Services</h1>
  </section>

  <section class="search-results">
    <div class="search-container">
      <input type="text" placeholder="Search" class="search">
      <img src="<?php echo base_url('assets/images/img-page/search.png'); ?>" alt="Search Icon" class="search-icon">
    </div>
    <p>Showing 1-6 of 9 results</p>
  </section>


  <section class="products-section">
    <!-- Sidebar Filters -->
    <aside class="filters">
      <h3>Filter Options</h3>

      <div class="filter-group">
        <h4>Category</h4>
        <label><input type="checkbox" value="balcony"> Balcony</label>
        <label><input type="checkbox" value="board"> Board</label>
        <label><input type="checkbox" value="cabinet"> Cabinet</label>
        <label><input type="checkbox" value="doors"> Doors</label>
        <label><input type="checkbox" value="mirrors"> Mirrors</label>
        <label><input type="checkbox" value="partition"> Partition</label>
        <label><input type="checkbox" value="shower-enclosure"> Shower Enclosure</label>
        <label><input type="checkbox" value="sliding-doors"> Sliding Doors</label>
        <label><input type="checkbox" value="sliding-windows"> Sliding Windows</label>
        <label><input type="checkbox" value="stair-railings"> Stair Railings</label>
        <label><input type="checkbox" value="storefront"> Storefront</label>
        <label><input type="checkbox" value="windows"> Windows</label>
      </div>


      <div class="filter-group">
        <h4>Material</h4>
        <label><input type="checkbox" value="Glass"> Glass</label>
        <label><input type="checkbox" value="Aluminum"> Aluminum</label>
      </div>

      <div class="filter-group">
        <h4>Availability</h4>
        <label><input type="checkbox" value="In Stock"> In Stock</label>
        <label><input type="checkbox" value="Out of Stock"> Out of Stock</label>
        <label><input type="checkbox" value="Low Stock"> Low Stock</label>
      </div>
    </aside>

    <!-- Products -->
    <div class="products">
      <!-- Active Filters -->
      <div class="active-filters">
        <h4>Active filter:</h4>
        <div class="active-tags"></div>
        <a href="#" class="clear">Clear All</a>
      </div>

      <script src="<?php echo base_url('assets/js/products-page/filters.js'); ?>"></script>

        <div class="product-grid">

          <?php foreach ($products as $p): ?>
            <div class="product" data-category="<?= $p->Category ?>" data-material="<?= $p->Material ?>"
              data-availability="<?= $p->Status ?>">

              <img src="<?= base_url('uploads/products/' . $p->ImageUrl) ?>" alt="<?= $p->ProductName ?>">

              <p><?= $p->ProductName ?></p>

              <button onclick="window.location.href='<?= base_url('2DModeling?id=' . $p->Product_ID) ?>'">
                Build and Buy
              </button>
            </div>
          <?php endforeach; ?>

        </div>


      <!-- Pagination -->
      <div class="pagination">
        <a href="#">&lt;</a>
        <a href="#" class="active">1</a>
        <a href="#">2</a>
        <a href="#">…</a>
        <a href="#">&gt;</a>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="testimonials">
    <h2>Customer Testimonials</h2>
    <div class="testimonial-content">
      <button class="testimonial-arrow left">
        <img src="<?php echo base_url(''); ?>assets/images/img-page/testimonials-arrow.png" alt="Previous">
      </button>

      <div class="testimonial-wrapper">
        <div class="testimonial-text active">
          <p>Highly recommending this shop! Very smooth and fast transaction. Despite unfortunate events, they were
            still able to deliver. Owner and staff are committed at great service. Exceeds expectations. Will definitely
            be our go-to-shop for glass and aluminum.</p>
          <div class="stars">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
          </div>
          <h3 class="author">Kris-Ann Munda-Rebullana</h3>
        </div>

        <div class="testimonial-text">
          <p>Highly recommended ⭐⭐⭐⭐⭐ Very accommodating staff. Responded immediately to queries and concerns. Quality
            materials and great workmanship. We'll ask them DEFINITELY to do collab again in our next project 👍👍</p>
          <div class="stars">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
          </div>
          <h3 class="author">Anne Cruz</h3>
        </div>

        <div class="testimonial-text">
          <p>Highly recommended! GlassWorth Builders service was excellent, and the quality of materials was top-notch.
            Their installers were kind and demonstrated good workmanship. I'm thoroughly impressed!</p>
          <div class="stars">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
            <img src="<?php echo base_url('assets/images/img-page/mdi--star-circle-outline.svg'); ?>" alt="ratings">
          </div>
          <h3 class="author">Jandoc Jun</h3>
        </div>
      </div>

      <button class="testimonial-arrow right">
        <img src="<?php echo base_url('assets/images/img-page/testimonials-arrow.png'); ?>" alt="Next">
      </button>
    </div>
  </section>

</main>
<script src="<?php echo base_url('assets/js/products-page/testimonial.js'); ?>"></script>