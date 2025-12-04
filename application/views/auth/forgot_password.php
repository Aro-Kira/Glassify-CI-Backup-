<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/auth/login_style.css'); ?>">

<?php if ($this->session->flashdata('error')): ?>
  <p style="color: red; text-align:center; margin-bottom:10px;">
    <?php echo $this->session->flashdata('error'); ?>
  </p>
<?php endif; ?>

<?php if ($this->session->flashdata('info')): ?>
  <p style="color: #007bff; text-align:center; margin-bottom:10px;">
    <?php echo $this->session->flashdata('info'); ?>
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
        <span class="highlight">Reset</span> Your Password.<br>
        Enter your email to receive<br>
        <span class="highlight">reset instructions</span>.
      </p>
      <div class="login-user-icon">
        <img src="<?php echo base_url('assets/images/img-page/mdi_shield-account.png'); ?>" alt="reset-icon" width="95" height="95">
      </div>
    </div>

    <!-- Right Panel -->
    <div class="login-right">
      <h3 class="login-title">Forgot Password</h3>
      <p style="text-align: center; color: #666; margin-bottom: 20px;">
        Enter your email address and we'll send you instructions to reset your password.
      </p>

      <form method="POST" action="<?= base_url('auth/process_forgot_password/' . $role) ?>">

        <div class="login-input-group">
          <label for="email">Email Address</label>
          <div class="login-input-row">
            <img src="<?php echo base_url('assets/images/img-page/ic_outline-email.svg'); ?>" alt="Email Icon"
              class="login-input-icon">
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
          </div>
        </div>

        <button type="submit" class="login-btn">Send Reset Link</button>

        <p class="login-register">
          <a href="<?php echo base_url('sales-login'); ?>">← Back to Login</a>
        </p>
      </form>

    </div>
  </div>
</section>

