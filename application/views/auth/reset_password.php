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
        <?php if ($role === 'Customer'): ?>
          <img src="<?php echo base_url('assets/images/img-page/mdi_account-outline.svg'); ?>" alt="account-icon">
        <?php else: ?>
          <img src="<?php echo base_url('assets/images/img-page/mdi_shield-account.png'); ?>" alt="reset-icon" width="95" height="95">
        <?php endif; ?>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="login-right">
      <h3 class="login-title">Reset Password</h3>
      <p style="text-align: left; color: #666; margin-bottom: 20px;">
        Please enter your new password.
      </p>

      <form method="POST" action="<?= base_url('auth/process_reset_password/' . $role) ?>">
        <input type="hidden" name="token" value="<?= $token ?>">

        <div class="login-input-group">
          <label for="password">New Password</label>
          <div class="login-input-row">
            <img src="<?php echo base_url('assets/images/img-page/solar_password-outline.svg'); ?>" alt="Password Icon"
              class="login-input-icon">
            <input type="password" id="password" name="password" placeholder="Enter new password" required minlength="8">
            <button type="button" class="login-toggle-password" id="togglePassword">
              <i class="fa fa-eye"></i>
            </button>
          </div>
          <div class="password-requirements" id="passwordRequirements" style="display: none;">
            <p class="requirements-title">Password must contain:</p>
            <ul class="requirements-list">
              <li id="req-length"><span class="check-icon">✗</span> At least 8 characters</li>
              <li id="req-uppercase"><span class="check-icon">✗</span> One uppercase letter (A-Z)</li>
              <li id="req-lowercase"><span class="check-icon">✗</span> One lowercase letter (a-z)</li>
              <li id="req-number"><span class="check-icon">✗</span> One number (0-9)</li>
            </ul>
          </div>
        </div>

        <div class="login-input-group">
          <label for="confirm_password">Confirm Password</label>
          <div class="login-input-row">
            <img src="<?php echo base_url('assets/images/img-page/solar_password-outline.svg'); ?>" alt="Password Icon"
              class="login-input-icon">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="8">
            <button type="button" class="login-toggle-password" id="toggleConfirmPassword">
              <i class="fa fa-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="login-btn">Reset Password</button>

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

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Toggle password visibility
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

    // Toggle confirm password visibility
    const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
    const confirmPasswordInput = document.getElementById("confirm_password");
    
    if (toggleConfirmPassword && confirmPasswordInput) {
        toggleConfirmPassword.addEventListener("click", function() {
            const type = confirmPasswordInput.getAttribute("type") === "password" ? "text" : "password";
            confirmPasswordInput.setAttribute("type", type);
            
            // Toggle icon
            const icon = this.querySelector("i");
            if (icon) {
                icon.classList.toggle("fa-eye");
                icon.classList.toggle("fa-eye-slash");
            }
        });
    }

    // Show password requirements when password field is focused
    if (passwordInput) {
        passwordInput.addEventListener('focus', function() {
            const requirements = document.getElementById('passwordRequirements');
            if (requirements) {
                requirements.style.display = 'block';
            }
        });

        passwordInput.addEventListener('blur', function() {
            if (this.value === '') {
                const requirements = document.getElementById('passwordRequirements');
                if (requirements) {
                    requirements.style.display = 'none';
                }
            }
        });

        passwordInput.addEventListener('input', function() {
            validatePasswordStrength(this.value);
        });

        // Validate password match
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                validatePasswordMatch();
            });
        }
    }

    function validatePasswordStrength(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password)
        };

        // Update visual feedback for each requirement
        updateRequirement('req-length', requirements.length);
        updateRequirement('req-uppercase', requirements.uppercase);
        updateRequirement('req-lowercase', requirements.lowercase);
        updateRequirement('req-number', requirements.number);
    }

    function updateRequirement(elementId, isValid) {
        const element = document.getElementById(elementId);
        if (element) {
            const icon = element.querySelector('.check-icon');
            if (icon) {
                if (isValid) {
                    icon.textContent = '✓';
                    icon.style.color = '#28a745';
                    element.style.color = '#28a745';
                } else {
                    icon.textContent = '✗';
                    icon.style.color = '#dc3545';
                    element.style.color = '#666';
                }
            }
        }
    }

    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword && password !== confirmPassword) {
            confirmPasswordInput.setCustomValidity('Passwords do not match');
        } else if (confirmPassword) {
            confirmPasswordInput.setCustomValidity('');
        }
    }
});
</script>
