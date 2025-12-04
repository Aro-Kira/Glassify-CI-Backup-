<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/auth/login_style.css'); ?>">

<?php if ($this->session->flashdata('error')): ?>
  <p style="color: red; text-align:center; margin-bottom:10px;">
    <?php echo $this->session->flashdata('error'); ?>
  </p>
<?php endif; ?>

<?php if ($this->session->flashdata('success')): ?>
  <p style="color: green; text-align:center; margin-bottom:10px;">
    <?php echo $this->session->flashdata('success'); ?>
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
        <span class="highlight">Create</span> New Password.<br>
        Enter your new password<br>
        <span class="highlight">below</span>.
      </p>
      <div class="login-user-icon">
        <img src="<?php echo base_url('assets/images/img-page/mdi_shield-account.png'); ?>" alt="reset-icon" width="95" height="95">
      </div>
    </div>

    <!-- Right Panel -->
    <div class="login-right">
      <h3 class="login-title">Reset Password</h3>
      <p style="text-align: center; color: #666; margin-bottom: 20px;">
        Please enter your new password. It must be at least 6 characters long.
      </p>

      <form method="POST" action="<?= base_url('auth/process_reset_password/' . $role) ?>">
        <input type="hidden" name="token" value="<?= $token ?>">

        <div class="login-input-group">
          <label for="password">New Password</label>
          <div class="login-input-row">
            <img src="<?php echo base_url('assets/images/img-page/solar_password-outline.svg'); ?>" alt="Password Icon"
              class="login-input-icon">
            <input type="password" id="password" name="password" placeholder="Enter new password" required minlength="6">
          </div>
        </div>

        <div class="login-input-group">
          <label for="confirm_password">Confirm Password</label>
          <div class="login-input-row">
            <img src="<?php echo base_url('assets/images/img-page/solar_password-outline.svg'); ?>" alt="Password Icon"
              class="login-input-icon">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="6">
          </div>
        </div>

        <button type="submit" class="login-btn">Reset Password</button>

        <p class="login-register">
          <a href="<?php echo base_url('sales-login'); ?>">← Back to Login</a>
        </p>
      </form>

    </div>
  </div>
</section>

