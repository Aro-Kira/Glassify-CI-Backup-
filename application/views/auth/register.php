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
            <?php endif; ?>

            <!-- Validation Errors -->
            <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

            <!-- Register Form -->
            <?php echo form_open('auth/process_register', ['class' => 'register-form']); ?>

                <div class="register-name-row">
                    <div class="register-input-group">
                        <label for="firstName">First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" id="firstName" placeholder="Enter your first name" value="<?= set_value('first_name'); ?>" required>
                    </div>

                    <div class="register-input-group">
                        <label for="middleInitial">Middle Name</label>
                        <input type="text" name="middle_initial" id="middleInitial" placeholder="Enter your middle name" value="<?= set_value('middle_initial'); ?>">
                    </div>

                    <div class="register-input-group">
                        <label for="surname">Surname <span class="required">*</span></label>
                        <input type="text" name="surname" id="surname" placeholder="Enter your surname" value="<?= set_value('surname'); ?>" required>
                    </div>
                </div>

                <div class="register-name-row">
                    <div class="register-input-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" id="email" placeholder="Enter your email" value="<?= set_value('email'); ?>" required>
                    </div>

                    <div class="register-input-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" name="phone" id="phone" placeholder="Enter your phone number" value="<?= set_value('phone'); ?>" required>
                    </div>
                </div>

                <div class="register-input-group password-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="register-input-row">
                        <input type="password" name="password" id="password" placeholder="Enter your password" required minlength="8">
                        <button type="button" class="toggle-password"><i class="fa fa-eye"></i></button>
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

                <div class="register-input-group password-group">
                    <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                    <div class="register-input-row">
                        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm your password" required>
                        <button type="button" class="toggle-password"><i class="fa fa-eye"></i></button>
                    </div>
                </div>

                <div class="register-options">
                    <label>
                        <input type="checkbox" required> I agree to Glassify's 
                        <a href="<?php echo base_url('terms'); ?>" target="_blank">Terms and Conditions</a>
                    </label>
                </div>

                <button type="submit" class="register-btn">Sign Up</button>

                <p class="register-login" style="text-align: center; margin-top: 1rem; font-size: 0.9rem;">
                    Have an account? <a href="<?php echo base_url('login'); ?>" class="register-login-link">Log in</a>
                </p>

            <?php echo form_close(); ?>
        </div>

    </div>
</section>

<!-- Registration Success Modal -->
<div id="registrationSuccessModal" class="registration-modal-overlay" style="display: none;">
    <div class="registration-modal-content">
        <div class="registration-modal-body success">
            <div class="modal-icon">✓</div>
            <p>Registration successful! Please check your email to confirm your account before logging in.</p>
            <button type="button" class="modal-ok-btn" onclick="closeRegistrationModal()">OK</button>
        </div>
    </div>
</div>

<style>
/* Registration Success Modal Styles */
.registration-modal-overlay {
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

.registration-modal-content {
    background-color: #fff;
    border-radius: 10px;
    width: 90%;
    max-width: 450px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    animation: slideDown 0.3s ease;
    position: relative;
}

.registration-modal-body {
    padding: 40px 30px 30px;
    text-align: center;
}

.registration-modal-body.success {
    color: #155724;
}

.registration-modal-body .modal-icon {
    font-size: 64px;
    margin-bottom: 20px;
    color: #28a745;
    font-weight: bold;
}

.registration-modal-body p {
    margin: 0 0 25px 0;
    font-size: 16px;
    line-height: 1.6;
    color: #155724;
}

.modal-ok-btn {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.modal-ok-btn:hover {
    background-color: #218838;
}

.modal-ok-btn:active {
    transform: scale(0.98);
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .registration-modal-content {
        width: 95%;
        max-width: 400px;
    }
    
    .registration-modal-body {
        padding: 30px 20px 25px;
    }
    
    .registration-modal-body .modal-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }
    
    .registration-modal-body p {
        font-size: 14px;
        margin-bottom: 20px;
    }
    
    .modal-ok-btn {
        padding: 10px 25px;
        font-size: 14px;
    }
}
</style>

<script>
// Show registration success modal if registration was successful
<?php if ($this->session->flashdata('registration_success')): ?>
document.addEventListener('DOMContentLoaded', function() {
    showRegistrationModal();
});
<?php endif; ?>

function showRegistrationModal() {
    const modal = document.getElementById('registrationSuccessModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeRegistrationModal() {
    const modal = document.getElementById('registrationSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        // Redirect to login page
        window.location.href = '<?= base_url("login") ?>';
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('registrationSuccessModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeRegistrationModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('registrationSuccessModal');
            if (modal && modal.style.display === 'flex') {
                closeRegistrationModal();
            }
        }
    });
});

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
        // Show password requirements when password field is focused
        passwordInput.addEventListener('focus', function() {
            const requirements = document.getElementById('passwordRequirements');
            if (requirements) {
                requirements.style.display = 'block';
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
