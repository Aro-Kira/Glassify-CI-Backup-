
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
  <a href="<?php echo base_url('projects#contact'); ?>" class="contus">Contact Us?</a>
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

<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/pages/projects_style.css'); ?>">

<!-- Contact -->
<section id="contact" class="contact-section">
    <div class="contact-content">
        <!-- Left Side: Info -->
        <div class="contact-info">
            <h2>Contact Us!</h2>
            <p>
                We're eager to discuss your next glass or aluminum project.
                Our team is ready to assist you!
            </p>

            <div class="info-item">
                <span class="icon"><img src="<?php echo base_url('assets/images/img-page/ic_outline-facebook.svg'); ?>"
                        alt="fb-icon"></span>
                <a href="https://www.facebook.com/glassworthbuilders" target="_blank" rel="noopener noreferrer">GlassWorth Builders</a>
            </div>

            <div class="info-item">
                <span class="icon"><img src="<?php echo base_url('assets/images/img-page/weui_email-filled.svg'); ?>"
                        alt="email-icon"></span>
                <p>glassworthbuilders@gmail.com</p>
            </div>
        </div>

        <!-- Right Side: Form -->
        <form class="contact-form" method="post" action="<?php echo base_url('quote-request'); ?>">
            <div class="form-group half">
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="first-name" placeholder="Enter your first name" required>
            </div>

            <div class="form-group half">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last-name" placeholder="Enter your last name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Enter your message"></textarea>
            </div>

            <button type="submit">Submit</button>
        </form>

    </div>
</section>

<!-- Modal Popup for Messages -->
<div id="messageModal" class="message-modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <div class="modal-body">
            <div id="modalMessage"></div>
        </div>
    </div>
</div>

<style>
    /* Modal Popup Styles */
    .message-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease;
    }

    .message-modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: #fff;
        margin: auto;
        padding: 0;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        animation: slideDown 0.3s ease;
        position: relative;
    }

    .modal-close {
        position: absolute;
        right: 15px;
        top: 15px;
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
        transition: color 0.3s;
    }

    .modal-close:hover,
    .modal-close:focus {
        color: #000;
    }

    .modal-body {
        padding: 40px 30px 30px;
        text-align: center;
    }

    .modal-body.success {
        color: #155724;
    }

    .modal-body.error {
        color: #721c24;
    }

    .modal-body .icon {
        font-size: 48px;
        margin-bottom: 20px;
    }

    .modal-body.success .icon {
        color: #28a745;
    }

    .modal-body.error .icon {
        color: #dc3545;
    }

    .modal-body p {
        margin: 0;
        font-size: 16px;
        line-height: 1.6;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<script>
    // Show modal if there's a flashdata message
    <?php if ($this->session->flashdata('success')): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showModal('<?php echo addslashes($this->session->flashdata('success')); ?>', 'success');
        });
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('error')): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showModal('<?php echo addslashes($this->session->flashdata('error')); ?>', 'error');
        });
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('email_debug')): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var debugMsg = 'Email Debug Info:<br><?php echo addslashes($this->session->flashdata('email_debug')); ?><br><small>Check <a href="<?php echo base_url('test_email'); ?>" target="_blank">test_email.php</a> for detailed testing.</small>';
            showModal(debugMsg, 'error');
        });
    <?php endif; ?>

    function showModal(message, type) {
        var modal = document.getElementById('messageModal');
        var modalMessage = document.getElementById('modalMessage');
        var modalBody = modal.querySelector('.modal-body');
        
        // Add icon based on type
        var icon = '';
        if (type === 'success') {
            icon = '<div class="icon" style="font-size: 48px; margin-bottom: 15px; color: #28a745;">✓</div>';
        } else if (type === 'error') {
            icon = '<div class="icon" style="font-size: 48px; margin-bottom: 15px; color: #dc3545;">✗</div>';
        }
        
        // Set message with icon
        modalMessage.innerHTML = icon + '<p>' + message + '</p>';
        
        // Set type class
        modalBody.className = 'modal-body ' + type;
        
        // Show modal
        modal.classList.add('show');
        
        // Auto-close after 5 seconds
        setTimeout(function() {
            closeModal();
        }, 5000);
    }

    function closeModal() {
        var modal = document.getElementById('messageModal');
        modal.classList.remove('show');
    }

    // Close modal when clicking the X
    document.addEventListener('DOMContentLoaded', function() {
        var closeBtn = document.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        
        // Close modal when clicking outside
        var modal = document.getElementById('messageModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    });
</script>
