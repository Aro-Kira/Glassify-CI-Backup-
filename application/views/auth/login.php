<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/auth/login_style.css'); ?>">

<?php if ($this->session->flashdata('error')): ?>
  <p style="color: red; text-align:center; margin-bottom:10px;">
    <?php echo $this->session->flashdata('error'); ?>
  </p>
<?php endif; ?>


<section class="login-section">
  <div class="login-container">

    <!-- Left Panel -->
    <div class="login-left">
      <div class="login-logo">
        <img src="<?php echo base_url('assets/images/img-page/logo.png'); ?>" alt="GlassWorth Logo">
      </div>
      <h2 class="login-brand">Glassify</h2>
      <p class="login-description">
        <span class="highlight">Design</span> Your Glass Project.
        Get <span class="highlight">Instant</span> Quotes.
        <span class="highlight">Order</span> Online.
      </p>
      <div class="login-user-icon">
        <img src="<?php echo base_url('assets/images/img-page/mdi_account-outline.svg'); ?>" alt="account-icon">
      </div>
    </div>

    <!-- Right Panel -->
    <div class="login-right">
      <h3 class="login-title">Sign In</h3>

      <!-- ✅ Only ONE form -->
      <form method="POST" action="<?= base_url('auth/process_role_login/Customer') ?>">

        <div class="login-input-group">
          <label for="email">Email Address</label>
          <div class="login-input-row">
            <img src="<?php echo base_url('assets/images/img-page/ic_outline-email.svg'); ?>" alt="Email Icon"
              class="login-input-icon">
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
          </div>
        </div>

        <div class="login-input-group">
          <label for="password">Password</label>
          <div class="login-input-row">
            <img src="<?php echo base_url('assets/images/img-page/solar_password-outline.svg'); ?>" alt="Password Icon"
              class="login-input-icon">
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
          </div>
        </div>

        <button type="submit" class="login-btn">Login</button>

        <div class="login-options">
          <label><input type="checkbox"> Remember Me</label>
          <a href="<?php echo base_url('forgot-password'); ?>">Forgot Password?</a>
        </div>

        <p class="login-register">
          Don’t have an account?
          <a href="<?php echo base_url('register'); ?>">Sign up</a>
        </p>
      </form>

    </div>
  </div>
</section>