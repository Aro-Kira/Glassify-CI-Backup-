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
        <span class="highlight">Sales</span> Access Only.<br>
        Manage <span class="highlight">Users</span>, Orders, and Settings.
      </p>
      <div class="login-user-icon">
        <img src="<?php echo base_url('assets/images/img-page/mdi_shield-account.png'); ?>" alt="sales-icon" width="95" height="95">
      </div>
    </div>

    <!-- Right Panel -->
    <div class="login-right">
      <h3 class="login-title">Sales Sign In</h3>

      <!-- ✅ sales Login Form -->
      <form method="POST" action="<?= base_url('auth/process_role_login/Sales') ?>">

        <?php
        $pending_email = $this->session->tempdata('pending_sales_email');
        $remember_email_value = isset($remember_email) ? $remember_email : '';
        $saved_email = $pending_email ?: ($remember_email ?? '');
        ?>

        <div class="login-input-group">
          <label for="email">Sales Email</label>
          <div class="login-input-row">
            <img src="<?php echo base_url('assets/images/img-page/ic_outline-email.svg'); ?>" alt="Email Icon"
              class="login-input-icon">
            <input type="email" id="email" name="email" placeholder="Enter your sales email"
              value="<?= $saved_email ?>" required>
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

        <button type="submit" class="login-btn">Login as Sales</button>

        <div class="login-options">
          <label><input type="checkbox" name="remember_me" value="1" <?= (!empty($remember_email)) ? 'checked' : '' ?>> Remember Me</label>
          <a href="<?php echo base_url('forgot-password/Sales'); ?>">Forgot Password?</a>
        </div>

        <p class="login-register">
          <a href="<?php echo base_url('login'); ?>">← Back to User Login</a>
        </p>
      </form>

    </div>
  </div>
</section>
