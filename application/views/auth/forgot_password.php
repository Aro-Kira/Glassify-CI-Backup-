<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/auth/login_style.css'); ?>">

<?php if ($this->session->flashdata('error')): ?>
  <p style="color: red; text-align:center; margin-bottom:10px;">
    <?php echo $this->session->flashdata('error'); ?>
  </p>
<?php endif; ?>

<?php 
$email_sent = $this->session->flashdata('email_sent');
if ($email_sent):
  // Get appropriate login page based on role
  $login_redirect = [
    'Admin' => 'Adlog',
    'Sales' => 'sales-login',
    'Inventory' => 'Invlog',
    'Customer' => 'login'
  ];
  $redirect_url = $login_redirect[$role] ?? 'login';
?>
<script>
  // =============================
  // TOAST NOTIFICATION SYSTEM
  // =============================
  function showToast(message, type = 'info', duration = 3000) {
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => {
      toast.classList.add('toast-fade-out');
      setTimeout(() => toast.remove(), 300);
    });

    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    const config = {
      success: { icon: '✓', bg: '#28a745', border: '#1e7e34' },
      error: { icon: '✕', bg: '#dc3545', border: '#c82333' },
      warning: { icon: '⚠', bg: '#ffc107', border: '#e0a800' },
      info: { icon: 'ℹ', bg: '#17a2b8', border: '#138496' }
    };
    
    const toastConfig = config[type] || config.info;
    
    toast.innerHTML = `
      <div class="toast-icon">${toastConfig.icon}</div>
      <div class="toast-message">${message}</div>
      <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    toast.style.cssText = `
      position: fixed;
      top: 80px;
      right: 20px;
      background: ${toastConfig.bg};
      color: white;
      padding: 16px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 10000;
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 300px;
      max-width: 500px;
      animation: toastSlideIn 0.3s ease;
      font-family: 'Montserrat', sans-serif;
      border-left: 4px solid ${toastConfig.border};
    `;
    
    if (!document.getElementById('toast-styles')) {
      const style = document.createElement('style');
      style.id = 'toast-styles';
      style.textContent = `
        @keyframes toastSlideIn {
          from { transform: translateX(400px); opacity: 0; }
          to { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastFadeOut {
          from { transform: translateX(0); opacity: 1; }
          to { transform: translateX(400px); opacity: 0; }
        }
        .toast-notification { transition: all 0.3s ease; }
        .toast-fade-out { animation: toastFadeOut 0.3s ease forwards; }
        .toast-icon { font-size: 20px; font-weight: bold; flex-shrink: 0; }
        .toast-message { flex: 1; font-size: 14px; line-height: 1.4; }
        .toast-close { background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; opacity: 0.8; transition: opacity 0.2s; flex-shrink: 0; }
        .toast-close:hover { opacity: 1; }
      `;
      document.head.appendChild(style);
    }
    
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('toast-fade-out');
      setTimeout(() => toast.remove(), 300);
    }, duration);
    
    return toast;
  }

  // Wait for page to fully load before showing toast
  window.addEventListener('load', function() {
    setTimeout(function() {
      showToast('<?php echo addslashes($email_sent); ?>', 'success', 4000);
      setTimeout(function() {
        window.location.href = '<?php echo base_url($redirect_url); ?>';
      }, 2000);
    }, 100);
  });
</script>
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
        <?php if ($role === 'Customer'): ?>
          <img src="<?php echo base_url('assets/images/img-page/mdi_account-outline.svg'); ?>" alt="account-icon">
        <?php else: ?>
          <img src="<?php echo base_url('assets/images/img-page/mdi_shield-account.png'); ?>" alt="reset-icon" width="95" height="95">
        <?php endif; ?>
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
          <?php
          // Redirect to appropriate login page based on role
          $login_redirect = [
            'Admin' => 'Adlog',
            'Sales' => 'sales-login',
            'Inventory' => 'Invlog',
            'Customer' => 'login'
          ];
          $redirect_url = $login_redirect[$role] ?? 'login';
          ?>
          <a href="<?php echo base_url($redirect_url); ?>">← Back to Login</a>
        </p>
      </form>

    </div>
  </div>
</section>

