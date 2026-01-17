 <link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/faq/faq_style.css'); ?>">

  <!-- Hero Section -->
  <div class="hero">
    <h1>Frequently Asked <br> Questions</h1>
    <style>
      .hero {
        background:
          linear-gradient(rgba(10, 42, 58, 0.6), rgba(10, 42, 58, 0.6)),
          url("<?php echo base_url('assets/images/img-page/faq_hero.jpg'); ?>");
        background-size: cover;
        background-position: center;
        height: 50vh;
        width: 100%;
        display: flex;
        padding-top: 30px;
        /* space for fixed header if needed */
        flex-direction: column;
        justify-content: center;
        color: white;
        margin-bottom: 60px;
      }
    </style>

  </div>

  <!-- Report Issue (only show if logged in) -->
  <?php if ($this->session->userdata('is_logged_in') && $this->session->userdata('user_role') === 'Customer'): ?>
  <section class="faq-section">
    <div class="faq-buttons">
      <a href="<?php echo base_url('report-issue'); ?>">
        <div class="faq-btn blue">
          <img src="<?php echo base_url('assets/images/img-page/report.png') ?>" alt="Report Issue Icon">
          <span>Report Issue</span>
        </div>
      </a>
    </div>
  </section>
  <?php endif; ?>

    <!-- Get More Information -->
    <div class="info-title">Get More Information</div>
    <div class="info-grid">
      <a href="<?php echo base_url('faq-ordering'); ?>">
        <div class="info-card">Ordering & Product Customization</div>
      </a>
      <a href="<?php echo base_url('faq-payment'); ?>">
        <div class="info-card">Payments</div>
      </a>
        <a href="<?php echo base_url('faq-shipping'); ?>">
        <div class="info-card">Shipping & Installation</div>
      </a>
       <a href="<?php echo base_url('faq-warranty'); ?>">
        <div class="info-card">Warranty</div>
      </a>
      <a href="<?php echo base_url('faq-pricing'); ?>">
        <div class="info-card">Pricing & Quotation</div>
      </a>
        <a href="<?php echo base_url('faq-account'); ?>">
        <div class="info-card">Account</div>
      </a>
    </div>

    <!-- Get Support -->
    <div class="support-title">Get Support</div>
    <div class="support-boxes">
      <div class="support-card">
        <div class="card-header">
          <h3><img src="<?php echo base_url('assets/images/img-page/contact-icon.png') ?>" alt="Contact Icon"> Contact Us
          </h3>
        </div>
        <p><img src="<?php echo base_url('assets/images/img-page/faq-email.svg') ?>"
            alt="email">glassworthbuilders@gmail.com</p>
        <p><img src="<?php echo base_url('assets/images/img-page/faq-ig.svg') ?>" alt="instagram">glassworthbuilders</p>
        <p><img src="<?php echo base_url('assets/images/img-page/faq-call.svg') ?>" alt="phone">+63 927 519 3800</p>
        <p><img src="<?php echo base_url('assets/images/img-page/faq-fb.svg') ?>" alt="facebook">GlassWorth Builders -
          Glass & Aluminum Services</p>
      </div>
    </div>
  </section>
