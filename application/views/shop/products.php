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
        <label><input type="checkbox" value="Mirrors"> Mirrors</label>
        <label><input type="checkbox" value="Shower Enclosure/Partition"> Shower Enclosure/Partition</label>
        <label><input type="checkbox" value="Stair Railings"> Stair Railings</label>
        <label><input type="checkbox" value="Windows"> Windows</label>
        <label><input type="checkbox" value="Glass Partition"> Glass Partition</label>
        <label><input type="checkbox" value="Doors"> Doors</label>
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

          <?php foreach ($products as $p): 
            $status = isset($p->Status) ? $p->Status : 'Out of Stock';
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
            
            // Handle images - can be JSON array or single string
            $images = [];
            if (!empty($p->ImageUrl)) {
              $decoded = json_decode($p->ImageUrl, true);
              if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $images = $decoded;
              } else {
                $images = [$p->ImageUrl];
              }
            }
            if (empty($images)) {
              $images = ['default.jpg'];
            }
            
            // Get order type
            $orderType = isset($p->OrderType) ? $p->OrderType : 'direct';
            $orderTypeDisplay = ($orderType === 'site-assessed' || $orderType === 'Site-Assessed') ? 'Site-Assessed' : 'Direct';
            
            // Get series (if exists)
            $series = isset($p->series) && !empty($p->series) ? $p->series : [];
            
            // Get tags (limit to 3-4, then show "others")
            $tags = isset($p->tags) && !empty($p->tags) ? $p->tags : [];
            $displayTags = array_slice($tags, 0, 3);
            $remainingTags = count($tags) > 3 ? count($tags) - 3 : 0;
            
            // Get price range
            $priceMin = isset($p->PriceMin) && $p->PriceMin > 0 ? floatval($p->PriceMin) : null;
            $priceMax = isset($p->PriceMax) && $p->PriceMax > 0 ? floatval($p->PriceMax) : null;
            $price = isset($p->Price) && $p->Price > 0 ? floatval($p->Price) : null;
          ?>
            <div class="product" data-category="<?= $p->Category ?>" data-material="<?= $p->Material ?>"
              data-availability="<?= $status ?>">

              <!-- Image Slideshow -->
              <div class="product-image-slideshow" data-product-id="<?= $p->Product_ID ?>">
                <?php foreach ($images as $index => $image): ?>
                  <img src="<?= base_url('uploads/products/' . $image) ?>" 
                       alt="<?= htmlspecialchars($p->ProductName) ?>" 
                       class="product-slide <?= $index === 0 ? 'active' : '' ?>"
                       onerror="this.onerror=null; this.style.display='none'; var placeholder = this.nextElementSibling; if(placeholder && placeholder.classList.contains('product-image-placeholder')) { placeholder.style.display='flex'; }">
                  <div class="product-image-placeholder" style="display: none; width: 100%; height: 100%; background: #f0f0f0; align-items: center; justify-content: center; color: #999; font-size: 14px;">
                    No Image Available
                  </div>
                <?php endforeach; ?>
                <?php if (count($images) > 1): ?>
                  <div class="slideshow-indicators">
                    <?php for ($i = 0; $i < count($images); $i++): ?>
                      <span class="indicator <?= $i === 0 ? 'active' : '' ?>" data-slide="<?= $i ?>"></span>
                    <?php endfor; ?>
                  </div>
                <?php endif; ?>
              </div>

              <p class="product-name"><?= htmlspecialchars($p->ProductName) ?></p>
              
              <!-- Order Type -->
              <div class="product-order-type">
                <span class="order-type-label">Type:</span>
                <span class="order-type-value"><?= htmlspecialchars($orderTypeDisplay) ?></span>
              </div>
              
              <!-- Series (if exists) -->
              <?php if (!empty($series)): ?>
                <div class="product-series">
                  <span class="series-label">Series:</span>
                  <span class="series-value"><?= htmlspecialchars(implode(', ', $series)) ?></span>
                </div>
              <?php endif; ?>
              
              <!-- Tags -->
              <?php if (!empty($displayTags)): ?>
                <div class="product-tags">
                  <?php foreach ($displayTags as $tag): ?>
                    <span class="product-tag"><?= htmlspecialchars($tag) ?></span>
                  <?php endforeach; ?>
                  <?php if ($remainingTags > 0): ?>
                    <span class="product-tag tag-others">+<?= $remainingTags ?> others</span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              
              <!-- Price Range -->
              <div class="product-price-range">
                <?php if ($priceMin !== null && $priceMax !== null): ?>
                  <span class="price-label">Price:</span>
                  <span class="price-value">₱<?= number_format($priceMin, 2) ?> - ₱<?= number_format($priceMax, 2) ?></span>
                <?php elseif ($price !== null): ?>
                  <span class="price-label">Price:</span>
                  <span class="price-value">₱<?= number_format($price, 2) ?></span>
                <?php else: ?>
                  <span class="price-label">Price:</span>
                  <span class="price-value">Contact for pricing</span>
                <?php endif; ?>
              </div>
              
              <span class="product-status-badge <?= $status_class; ?>" style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; color: white; background-color: <?= $status_color; ?>; margin: 8px 0;">
                <?= htmlspecialchars($status); ?>
              </span>

              <button class="build-buy-btn" onclick="window.location.href='<?= base_url('2DModeling?id=' . $p->Product_ID) ?>'">
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
<script>
// Product Image Slideshow
document.addEventListener('DOMContentLoaded', function() {
  const slideshows = document.querySelectorAll('.product-image-slideshow');
  
  slideshows.forEach(function(slideshow) {
    const slides = slideshow.querySelectorAll('.product-slide');
    const indicators = slideshow.querySelectorAll('.indicator');
    
    if (slides.length <= 1) return; // No slideshow needed for single image
    
    let currentSlide = 0;
    const totalSlides = slides.length;
    
    function showSlide(index) {
      // Remove active class from all slides and indicators
      slides.forEach(slide => slide.classList.remove('active'));
      indicators.forEach(indicator => indicator.classList.remove('active'));
      
      // Add active class to current slide and indicator
      slides[index].classList.add('active');
      if (indicators[index]) {
        indicators[index].classList.add('active');
      }
    }
    
    function nextSlide() {
      currentSlide = (currentSlide + 1) % totalSlides;
      showSlide(currentSlide);
    }
    
    // Auto-advance slideshow every 3 seconds
    setInterval(nextSlide, 3000);
    
    // Add click handlers to indicators
    indicators.forEach(function(indicator, index) {
      indicator.addEventListener('click', function() {
        currentSlide = index;
        showSlide(currentSlide);
      });
    });
  });
});
</script>