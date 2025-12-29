
<link rel="stylesheet" href="<?php echo base_url('assets/css/main_style.css'); ?>">
<script src="<?php echo base_url('assets/js/feature-slideshow.js'); ?>"></script>

<main id="content"></main>

<div class="welcome-section">

  <style>
    .welcome-section {
      background:
        linear-gradient(rgba(10, 42, 58, 0.6), rgba(10, 42, 58, 0.6)),
        url("<?php echo base_url('assets/images/img-page/home_bg.png'); ?>");
      background-size: cover;
      background-position: center;
      height: 80vh;
      width: 100%;
      display: flex;
      flex-direction: column;
      z-index: 0;
      justify-content: center;
      align-items: center;
      position: relative;
      padding-top: 0;
    }

    .welcome-section > * {
      position: relative;
      z-index: 1;
    }

    .welcome-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 0;
    }
  </style>

  <h1>Glassify</h1>
  <p><span>Design</span> Your Glass Project. Get
    <span>Instant</span> <br>Quotes.
    <span>Order</span> Online.
  </p>

  <a href="<?php echo base_url('products'); ?>" class="buildtd-btn">Build Today</a>
  <a href="#contact-footer" class="contus">Contact Us?</a>
</div>

<section class="what-we-offer">
  <div class="container">
    <div class="left">
      <h1>What We Offer</h1>
      <p><span>Expertly crafted</span> glass, aluminum, <br>and steel works.</p>
      <a href="<?php echo base_url('products'); ?>" class="btn">Browse Products</a>
    </div>

    <div class="right">
      <div class="feature">
        <div class="icon">
          <img src="<?php echo base_url('assets/images/img-page/windows.svg'); ?>" alt="Glass & Aluminum">
        </div>
        <div class="text">
          <h2>Glass & Aluminum Works</h2>
          <p>Sliding doors and windows, frameless panels, shower enclosures, storefronts, and more—designed for both
            homes and businesses.</p>
        </div>
      </div>

      <div class="feature">
        <div class="icon">
          <img src="<?php echo base_url('assets/images/img-page/angle.svg'); ?>" alt="Stainless & Steel">
        </div>
        <div class="text">
          <h2>Stainless & Steel Fabrication</h2>
          <p>Custom stair railings, balconies, gates, grills, and welded steelwork built for strength and style.</p>
        </div>
      </div>

      <div class="feature">
        <div class="icon">
          <img src="<?php echo base_url('assets/images/img-page/staircase.svg'); ?>" alt="Interior Enhancements">
        </div>
        <div class="text">
          <h2>Interior Enhancements</h2>
          <p>Kitchen cabinets, wardrobes, mirrors, glass boards, and sleek finishes to elevate your interiors.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="featured-projects">
  <div class="container-featured">
    <div class="carousel">
      <h2 class="section-title">Featured Projects</h2>
      <p class="section-subtitle">See Our Craft in Action</p>
      <div class="carousel-track">
        <div class="carousel-slide active">
          <div class="slide-content">
            <div class="slide-image" style="background-image: url('<?php echo base_url('assets/images/img-page/Glass_Aluminum_Home.png'); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
            <h3>Glass and Aluminum</h3>
          </div>
        </div>
        <div class="carousel-slide">
          <div class="slide-content">
            <div class="slide-image" style="background-image: url('<?php echo base_url('assets/images/img-page/Windows_Home.png'); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
            <h3>Windows</h3>
          </div>
        </div>
        <div class="carousel-slide">
          <div class="slide-content">
            <div class="slide-image" style="background-image: url('<?php echo base_url('assets/images/img-page/Aluminum_Kitchen_Home.png'); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
            <h3>Aluminum Kitchen</h3>
          </div>
        </div>
        <div class="carousel-slide">
          <div class="slide-content">
            <div class="slide-image" style="background-image: url('<?php echo base_url('assets/images/img-page/Shower_Enclosure_Home.png'); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
            <h3>Shower Enclosure</h3>
          </div>
        </div>
      </div>

      <button class="carousel-btn carousel-btn-prev" aria-label="Previous slide">&#8249;</button>
      <button class="carousel-btn carousel-btn-next" aria-label="Next slide">&#8250;</button>
    </div>
  </div>
</section>

<section class="testimonials">
  <h2>Customer Testimonials</h2>
  <p class="subtitle">
    Built on <span class="highlight">trust</span>. Proven by <span class="highlight">experience.</span>
  </p>

  <div class="cards">
    <div class="card">
      <div class="avatar">
        <img src="<?php echo base_url('assets/images/img-page/user-icon-testimonials.png'); ?>" alt="User avatar">
      </div>
      <h3>Jandoc Jun</h3>
      <p class="date">May 06, 2025</p>
      <p class="stars">⭐⭐⭐⭐⭐</p>
      <p class="review">
        Highly recommended! GlassWorth Builders service was excellent, and the quality of materials was top-notch.
        Their installers were kind and demonstrated good workmanship. I'm thoroughly impressed!
      </p>
    </div>

    <div class="card">
      <div class="avatar">
        <img src="<?php echo base_url('assets/images/img-page/user-icon-testimonials.png'); ?>" alt="User avatar">
      </div>
      <h3>Anne Cruz</h3>
      <p class="date">October 30, 2022</p>
      <p class="stars">⭐⭐⭐⭐⭐</p>
      <p class="review">
        Highly recommended. Very accommodating staff. Responded immediately to queries and concerns.
        Quality materials and great workmanship. We'll ask them DEFINITELY to do collab again in our next project.
      </p>
    </div>

    <div class="card">
      <div class="avatar">
        <img src="<?php echo base_url('assets/images/img-page/user-icon-testimonials.png'); ?>" alt="User avatar">
      </div>
      <h3>Francis Medina</h3>
      <p class="date">February 09, 2022</p>
      <p class="stars">⭐⭐⭐⭐⭐</p>
      <p class="review">
        Great product and super fast installation. Installed 6hrs after on-site estimation.
      </p>
    </div>
  </div>

  <div class="btn-container">
    <a href="<?php echo base_url('products'); ?>" class="btn">BUILD TODAY</a>
  </div>
</section>

