<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/auth/login_style.css'); ?>">


<?php if ($this->session->flashdata('success')): ?>
  <div style="background-color: #d4edda; border: 2px solid #c3e6cb; border-radius: 8px; padding: 15px; margin: 15px auto; max-width: 600px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <p style="color: #155724; font-weight: bold; margin: 0; font-size: 16px;">
      <?php echo $this->session->flashdata('success'); ?>
    </p>
  </div>
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

      <!-- Flash Messages -->
      <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger">
          <span><?= htmlspecialchars($this->session->flashdata('error')); ?></span>
        </div>
      <?php endif; ?>

      <!-- Validation Errors -->
      <?php echo validation_errors('<div class="alert alert-danger"><span>', '</span></div>'); ?>

      <!-- ✅ Only ONE form -->
      <form method="POST" action="<?= base_url('auth/process_role_login/Customer') ?>">

        <?php
        $remember_email_value = isset($remember_email) ? $remember_email : '';
        ?>

        <div class="login-input-group">
          <label for="email">Email Address</label>
          <div class="login-input-row">
            <img src="<?php echo base_url('assets/images/img-page/ic_outline-email.svg'); ?>" alt="Email Icon"
              class="login-input-icon">
            <input type="email" id="email" name="email" placeholder="Enter your email"
              value="<?= $remember_email_value ?>" required>
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

        <button type="submit" class="login-btn">Login</button>

        <div class="login-options">
          <label><input type="checkbox" name="remember_me" value="1" <?= (!empty($remember_email)) ? 'checked' : '' ?>> Remember Me</label>
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

<!-- Resend Confirmation Email Modal -->
<?php if ($this->session->flashdata('unconfirmed_email')): ?>
<div id="resendConfirmationModal" class="resend-modal-overlay" style="display: flex;">
  <div class="resend-modal-content">
    <div class="resend-modal-header">
      <h3>Resend Confirmation Email</h3>
      <button type="button" class="resend-modal-close" onclick="closeResendModal()">&times;</button>
    </div>
    <div class="resend-modal-body">
      <p>Please confirm your email address before logging in. Check your inbox for the confirmation link.</p>
      <div class="resend-modal-email"><?php echo htmlspecialchars($this->session->flashdata('unconfirmed_email')); ?></div>
      <p style="margin-top: 16px; color: #666; font-size: 0.9rem;">We can send you a new confirmation email if you didn't receive it.</p>
    </div>
    <div class="resend-modal-footer">
      <form method="POST" action="<?= base_url('auth/resend_confirmation') ?>" style="margin: 0; display: flex; gap: 12px; justify-content: flex-end; width: 100%;">
        <input type="hidden" name="email" value="<?= htmlspecialchars($this->session->flashdata('unconfirmed_email')) ?>">
        <button type="button" class="resend-modal-btn-cancel" onclick="closeResendModal()">Cancel</button>
        <button type="submit" class="resend-modal-btn-confirm">Resend Confirmation Email</button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// Close resend confirmation modal
function closeResendModal() {
    const modal = document.getElementById('resendConfirmationModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside or pressing ESC
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('resendConfirmationModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeResendModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeResendModal();
            }
        });
    }

    // Password toggle functionality
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