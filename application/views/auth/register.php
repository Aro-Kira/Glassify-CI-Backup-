<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/auth/register_style.css'); ?>">

<section class="register-section">
    <div class="register-container">

        <!-- Left Panel -->
        <div class="register-left">
            <div class="register-logo">
                <img src="<?php echo base_url('assets/images/img-page/logo.png'); ?>" alt="Glassify Logo">
            </div>
            <h2 class="register-brand">Glassify</h2>
            <p class="register-description">
                <span class="highlight">Design</span> Your Glass Project.
                Get <span class="highlight">Instant</span> Quotes.
                <span class="highlight">Order</span> Online.
            </p>
            <div class="login-user-icon">
                <img src="<?php echo base_url('assets/images/img-page/mdi_account-outline.svg'); ?>" alt="account-icon">
            </div>
        </div>

        <!-- Right Panel -->
        <div class="register-right">
            <h3 class="register-title">Create an Account</h3>

            <!-- Flash Messages -->
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
            <?php elseif ($this->session->flashdata('success')): ?>
                <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
            <?php endif; ?>

            <!-- Validation Errors -->
            <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

            <!-- Register Form -->
            <?php echo form_open('auth/process_register', ['class' => 'register-form']); ?>

                <div class="register-input-group">
                    <label for="firstName">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" id="firstName" placeholder="Enter your first name" value="<?= set_value('first_name'); ?>" required>
                </div>

                <div class="register-input-group">
                    <label for="middleInitial">Middle Initial (optional)</label>
                    <input type="text" name="middle_initial" id="middleInitial" placeholder="Enter your middle initial" value="<?= set_value('middle_initial'); ?>">
                </div>

                <div class="register-input-group">
                    <label for="surname">Surname <span class="required">*</span></label>
                    <input type="text" name="surname" id="surname" placeholder="Enter your surname" value="<?= set_value('surname'); ?>" required>
                </div>

                <div class="register-input-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" name="email" id="email" placeholder="Enter your email" value="<?= set_value('email'); ?>" required>
                </div>

                <div class="register-input-group password-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="register-input-row">
                        <input type="password" name="password" id="password" placeholder="Enter your password" required minlength="8">
                        <button type="button" class="toggle-password"><i class="fa fa-eye"></i></button>
                    </div>
                    <div class="password-requirements" id="passwordRequirements">
                        <p class="requirements-title">Password must contain:</p>
                        <ul class="requirements-list">
                            <li id="req-length"><span class="check-icon">✗</span> At least 8 characters</li>
                            <li id="req-uppercase"><span class="check-icon">✗</span> One uppercase letter (A-Z)</li>
                            <li id="req-lowercase"><span class="check-icon">✗</span> One lowercase letter (a-z)</li>
                            <li id="req-number"><span class="check-icon">✗</span> One number (0-9)</li>
                        </ul>
                    </div>
                </div>

                <div class="register-input-group password-group">
                    <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                    <div class="register-input-row">
                        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm your password" required>
                        <button type="button" class="toggle-password"><i class="fa fa-eye"></i></button>
                    </div>
                </div>

                <div class="register-input-group">
                    <label for="phone">Phone Number <span class="required">*</span></label>
                    <input type="tel" name="phone" id="phone" placeholder="Enter your phone number" value="<?= set_value('phone'); ?>" required>
                </div>

                <div class="register-options">
                    <label>
                        <input type="checkbox" required> I agree to Glassify’s 
                        <a href="<?php echo base_url('terms'); ?>">Terms and Conditions</a>
                    </label>
                    <a href="#">Need Help?</a>
                </div>

                <button type="submit" class="register-btn">Sign Up</button>

            <?php echo form_close(); ?>
        </div>

    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggles = document.querySelectorAll(".toggle-password");

    toggles.forEach(toggle => {
        toggle.addEventListener("click", function() {
            const input = this.previousElementSibling;
            if (!input) return;
            input.type = input.type === "password" ? "text" : "password";
            this.innerHTML = input.type === "password" ? '<i class="fa fa-eye"></i>' : '<i class="fa fa-eye-slash"></i>';
        });
    });

    // Strong Password Validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePasswordStrength(this.value);
        });

        // Password requirements are always visible

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

        // Check if all requirements are met
        const allMet = Object.values(requirements).every(req => req === true);
        
        if (allMet) {
            passwordInput.setCustomValidity('');
            passwordInput.classList.remove('invalid');
            passwordInput.classList.add('valid');
        } else {
            passwordInput.setCustomValidity('Password does not meet all requirements');
            passwordInput.classList.remove('valid');
            passwordInput.classList.add('invalid');
        }
    }

    function updateRequirement(elementId, isValid) {
        const element = document.getElementById(elementId);
        if (element) {
            const icon = element.querySelector('.check-icon');
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

    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword && password !== confirmPassword) {
            confirmPasswordInput.setCustomValidity('Passwords do not match');
            confirmPasswordInput.classList.remove('valid');
            confirmPasswordInput.classList.add('invalid');
        } else if (confirmPassword) {
            confirmPasswordInput.setCustomValidity('');
            confirmPasswordInput.classList.remove('invalid');
            confirmPasswordInput.classList.add('valid');
        }
    }
});
</script>
