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
        <span class="highlight">Admin</span> Access Only.<br>
        Manage <span class="highlight">Users</span>, Orders, and Settings.
      </p>
      <div class="login-user-icon">
        <img src="<?php echo base_url('assets/images/img-page/mdi_shield-account.png'); ?>" alt="sales-icon" width="95" height="95">
      </div>
    </div>

    <!-- Right Panel -->
    <div class="login-right">
      <h3 class="login-title">Admin Sign In</h3>

      <!-- ✅ Admin Login Form -->
  <form method="POST" action="<?= base_url('auth/process_role_login/Admin') ?>">
    

      <?php
      $pending_email = $this->session->tempdata('pending_admin_email');
      $remember_email_value = isset($remember_email) ? $remember_email : '';
      $saved_email = $pending_email ?: ($remember_email ?? '');
      ?>

      <div class="login-input-group">
        <label for="email">Admin Email</label>
        <div class="login-input-row">
          <img src="<?php echo base_url('assets/images/img-page/ic_outline-email.svg'); ?>" alt="Email Icon"
            class="login-input-icon">
          <input type="email" id="email" name="email" placeholder="Enter your admin email"
            value="<?= $saved_email ?>" required>
        </div>
      </div>

      <div class="login-input-group">
        <label for="password">Password</label>
        <div class="login-input-row">
          <img src="<?php echo base_url('assets/images/img-page/solar_password-outline.svg'); ?>" alt="Password Icon"
            class="login-input-icon">
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
          <button type="button" class="login-toggle-password" id="togglePassword">
            <i class="fa fa-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="login-btn">Login as Admin</button>

      <div class="login-options">
        <label><input type="checkbox" name="remember_me" value="1" <?= (!empty($remember_email)) ? 'checked' : '' ?>> Remember Me</label>
        <a href="<?php echo base_url('admin-forgot-password'); ?>">Forgot Password?</a>
      </div>

      <p class="login-register">
        <a href="<?php echo base_url('login'); ?>">← Back to User Login</a>
      </p>
      </form>

    </div>
  </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener("click", function() {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            
            // Toggle icon
            const icon = this.querySelector("i");
            if (icon) {
                icon.classList.toggle("fa-eye");
                icon.classList.toggle("fa-eye-slash");
            }
        });
    }
});
</script>