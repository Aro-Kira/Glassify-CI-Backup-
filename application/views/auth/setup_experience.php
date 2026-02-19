<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/auth/login_style.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/auth/setup_experience.css'); ?>">

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
      <h3 class="login-title">Welcome! Let's set up your experience.</h3>
      <p class="setup-subtitle">Tell us a bit about your experience so we can guide you properly.</p>
      
      <div class="setup-progress">
        <span class="progress-text">Step <span id="current-step">1</span> of <span id="total-steps">1</span></span>
      </div>

      <form id="setup-form" method="POST" action="<?= base_url('auth/save_experience_setup') ?>">
        
        <!-- Step 1: Role Selection (Beginner or Professional only) -->
        <div class="step-container active" data-step="1">
          <div class="setup-question" id="question-role">
          <label class="setup-question-label">What best describes your role? <span class="required">*</span></label>
          <div class="setup-options">
            <label class="setup-option">
              <input type="radio" name="role" value="beginner" required>
              <span class="option-content">
                <span class="option-title">Beginner</span>
                <span class="option-desc">No technical background in measurements or specifications</span>
              </span>
            </label>
            <label class="setup-option">
              <input type="radio" name="role" value="professional" required>
              <span class="option-content">
                <span class="option-title">Professional</span>
                <span class="option-desc">I work with technical specifications</span>
              </span>
            </label>
          </div>
          </div>
        </div>

        <!-- Professional Step 1.5: Type of Profession (only for professionals) -->
        <div class="step-container professional-step" data-step="1.5" style="display: none;">
          <div class="setup-question" id="question-profession-type">
            <label class="setup-question-label">What is your type of profession? <span class="required">*</span></label>
            <div class="setup-options">
              <label class="setup-option">
                <input type="radio" name="profession_type" value="architect">
                <span class="option-content">
                  <span class="option-title">Architect</span>
                  <span class="option-desc"></span>
                </span>
              </label>
              <label class="setup-option">
                <input type="radio" name="profession_type" value="engineer">
                <span class="option-content">
                  <span class="option-title">Engineer</span>
                  <span class="option-desc"></span>
                </span>
              </label>
              <label class="setup-option">
                <input type="radio" name="profession_type" value="contractor">
                <span class="option-content">
                  <span class="option-title">Contractor</span>
                  <span class="option-desc"></span>
                </span>
              </label>
              <label class="setup-option">
                <input type="radio" name="profession_type" value="other">
                <span class="option-content">
                  <span class="option-title">Other</span>
                  <span class="option-desc"></span>
                </span>
              </label>
            </div>
            <input type="text" name="profession_type_other_text" id="profession-type-other-text" class="setup-text-input" placeholder="Please specify your profession" style="display: none; margin-top: 10px;">
          </div>
        </div>

        <!-- Beginner Step 2: Experience -->
        <div class="step-container beginner-step" data-step="2" style="display: none;">
          <div class="setup-question">
            <label class="setup-question-label">Have you ever ordered a product that required specifications?</label>
            <div class="setup-options">
              <label class="setup-option-small">
                <input type="radio" name="beginner_experience" value="first_time">
                <span>No, this is my first time</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="beginner_experience" value="once_twice">
                <span>Yes, once or twice</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="beginner_experience" value="several_times">
                <span>Yes, several times</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Beginner Step 3: Specifications Knowledge -->
        <div class="step-container beginner-step" data-step="3" style="display: none;">
          <div class="setup-question">
            <label class="setup-question-label">Are you familiar with reading or providing product specifications (sizes, profiles, materials)?</label>
            <div class="setup-options">
              <label class="setup-option-small">
                <input type="radio" name="beginner_specifications" value="not_at_all">
                <span>Not at all</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="beginner_specifications" value="a_little">
                <span>A little</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="beginner_specifications" value="yes_need_guidance">
                <span>Yes, but I still need guidance</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Beginner Step 4: Customization Handling -->
        <div class="step-container beginner-step" data-step="4" style="display: none;">
          <div class="setup-question">
            <label class="setup-question-label">How would you like your product customization to be handled after the ocular visit?</label>
            <p class="setup-note" style="font-size: 0.9em; color: #666; margin-top: 5px;">Note: Beginner users cannot create customization themselves. This affects review/approval flow only.</p>
            <div class="setup-options">
              <label class="setup-option-small">
                <input type="radio" name="beginner_customization_handling" value="prepare_for_me">
                <span>I prefer GlassWorth Builders to prepare the customization for me</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="beginner_customization_handling" value="review_and_approve">
                <span>I want to review and approve the customization prepared for me</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Professional Step 2: Previous Experience with Specifications -->
        <div class="step-container professional-step" data-step="2" style="display: none;">
          <div class="setup-question">
            <label class="setup-question-label">Have you previously worked with products that required detailed specifications?</label>
            <div class="setup-options">
              <label class="setup-option-small">
                <input type="radio" name="professional_prev_experience" value="yes_regularly">
                <span>Yes, regularly</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="professional_prev_experience" value="yes_occasionally">
                <span>Yes, occasionally</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="professional_prev_experience" value="no_understand_drawings">
                <span>No, but I understand technical drawings</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Professional Step 3: Specification Preparation -->
        <div class="step-container professional-step" data-step="3" style="display: none;">
          <div class="setup-question">
            <label class="setup-question-label">How do you usually prepare product specifications?</label>
            <div class="setup-options">
              <label class="setup-option-small">
                <input type="radio" name="professional_spec_prep" value="prepare_myself">
                <span>I prepare measurements and specifications myself</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="professional_spec_prep" value="collaborate_after_assessment">
                <span>I collaborate after a site assessment</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="professional_spec_prep" value="adjust_supplier_specs">
                <span>I adjust specifications provided by suppliers</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Professional Step 4: 2D Tool Comfort -->
        <div class="step-container professional-step" data-step="4" style="display: none;">
          <div class="setup-question">
            <label class="setup-question-label">How comfortable are you with customizing products using a 2D configuration tool?</label>
            <div class="setup-options">
              <label class="setup-option-small">
                <input type="radio" name="professional_2d_comfort" value="very_comfortable">
                <span>Very comfortable</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="professional_2d_comfort" value="somewhat_comfortable">
                <span>Somewhat comfortable</span>
              </label>
              <label class="setup-option-small">
                <input type="radio" name="professional_2d_comfort" value="prefer_minimal">
                <span>I prefer minimal adjustments</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="setup-navigation">
          <button type="button" class="nav-btn" id="back-btn" style="display: none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back
          </button>
          <div style="flex: 1;"></div>
          <button type="button" class="nav-btn primary" id="next-btn" disabled>
            Next
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </button>
          <button type="submit" class="nav-btn primary" id="complete-btn" style="display: none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 6L9 17l-5-5"/>
            </svg>
            Complete Setup
          </button>
        </div>

      </form>

    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let currentStep = 1;
  let selectedRole = null;
  
  const roleInputs = document.querySelectorAll('input[name="role"]');
  const professionTypeInputs = document.querySelectorAll('input[name="profession_type"]');
  const professionTypeOtherText = document.getElementById('profession-type-other-text');
  const backBtn = document.getElementById('back-btn');
  const nextBtn = document.getElementById('next-btn');
  const completeBtn = document.getElementById('complete-btn');
  const currentStepEl = document.getElementById('current-step');
  const totalStepsEl = document.getElementById('total-steps');
  
  // Calculate total steps based on role
  // Beginner: 4 steps (1 role + 3 questions)
  // Professional: 5 steps (1 role + 1 profession type + 3 questions)
  function getTotalSteps() {
    if (selectedRole === 'beginner') return 4;
    if (selectedRole === 'professional') return 5;
    return 4; // Default
  }
  
  // Update total steps display
  function updateStepDisplay() {
    const total = getTotalSteps();
    totalStepsEl.textContent = total;
    
    // For professionals, adjust step numbers to show correctly
    if (selectedRole === 'professional') {
      if (currentStep === 1.5) {
        currentStepEl.textContent = '2';
      } else if (currentStep > 1.5) {
        currentStepEl.textContent = currentStep === 2 ? '3' : currentStep === 3 ? '4' : '5';
      } else {
        currentStepEl.textContent = currentStep;
      }
    } else {
      currentStepEl.textContent = currentStep;
    }
  }
  
  // Initialize
  updateNavigationButtons();
  updateStepDisplay();
  
  // Role selection handler
  roleInputs.forEach(input => {
    input.addEventListener('change', function() {
      selectedRole = this.value;
      saveProgress();
      updateNavigationButtons();
      updateStepDisplay();
    });
  });
  
  // Profession type selection handler (for professionals)
  professionTypeInputs.forEach(input => {
    input.addEventListener('change', function() {
      // Show/hide "other" text input
      if (this.value === 'other') {
        professionTypeOtherText.style.display = 'block';
        professionTypeOtherText.required = true;
      } else {
        professionTypeOtherText.style.display = 'none';
        professionTypeOtherText.required = false;
        professionTypeOtherText.value = '';
      }
      
      saveProgress();
      updateNavigationButtons();
    });
  });
  
  // Profession type other text input handler
  if (professionTypeOtherText) {
    professionTypeOtherText.addEventListener('input', function() {
      saveProgress();
      updateNavigationButtons();
    });
  }
  
  // Back button
  backBtn.addEventListener('click', function() {
    if (currentStep > 1) {
      // For professionals, go back through profession type step
      if (selectedRole === 'professional') {
        if (currentStep === 1.5) {
          goToStep(1);
        } else if (currentStep === 2) {
          goToStep(1.5);
        } else {
          goToStep(currentStep - 1);
        }
      } else {
        goToStep(currentStep - 1);
      }
    }
  });
  
  // Next button
  nextBtn.addEventListener('click', function() {
    if (isCurrentStepValid()) {
      // For professionals, include profession type step after step 1
      if (selectedRole === 'professional') {
        if (currentStep === 1) {
          goToStep(1.5);
        } else if (currentStep === 1.5) {
          goToStep(2);
        } else {
          goToStep(currentStep + 1);
        }
      } else {
        // For beginners, skip profession type step
        goToStep(currentStep + 1);
      }
    }
  });
  
  // Add change listeners to all radio inputs
  document.querySelectorAll('input[type="radio"]').forEach(input => {
    input.addEventListener('change', function() {
      saveProgress();
      updateNavigationButtons();
    });
  });
  
  function goToStep(stepNumber) {
    // Hide all steps
    document.querySelectorAll('.step-container').forEach(step => {
      step.classList.remove('active');
      step.style.display = 'none';
    });
    
    // Show target step based on role and step number
    if (stepNumber === 1) {
      // Always show role selection
      document.querySelector('[data-step="1"]').classList.add('active');
      document.querySelector('[data-step="1"]').style.display = 'block';
    } else if (stepNumber === 1.5) {
      // Show profession type for professionals
      const professionStep = document.querySelector('.professional-step[data-step="1.5"]');
      if (professionStep) {
        professionStep.classList.add('active');
        professionStep.style.display = 'block';
      }
    } else {
      // Show beginner or professional steps (2, 3, 4)
      const stepClass = selectedRole === 'beginner' ? 'beginner-step' : 'professional-step';
      const targetStep = document.querySelector(`.${stepClass}[data-step="${stepNumber}"]`);
      if (targetStep) {
        targetStep.classList.add('active');
        targetStep.style.display = 'block';
      }
    }
    
    currentStep = stepNumber;
    saveProgress();
    updateNavigationButtons();
    updateStepDisplay();
  }
  
  function isCurrentStepValid() {
    const currentStepContainer = document.querySelector('.step-container.active');
    if (!currentStepContainer) return false;
    
    // Check if current step has a radio selection
    const radioInputs = currentStepContainer.querySelectorAll('input[type="radio"]');
    if (radioInputs.length > 0) {
      const isSelected = Array.from(radioInputs).some(input => input.checked);
      
      // If profession_type "other" is selected, check text input
      if (isSelected && currentStep === 1.5) {
        const otherRadio = currentStepContainer.querySelector('input[name="profession_type"][value="other"]:checked');
        if (otherRadio && professionTypeOtherText) {
          return professionTypeOtherText.value.trim() !== '';
        }
      }
      
      return isSelected;
    }
    
    return true;
  }
  
  function isFinalStep() {
    const totalSteps = getTotalSteps();
    if (selectedRole === 'beginner') {
      return currentStep === 4;
    } else if (selectedRole === 'professional') {
      return currentStep === 4; // Professional steps go 1 -> 1.5 -> 2 -> 3 -> 4
    }
    return false;
  }
  
  function updateNavigationButtons() {
    // Back button visibility
    backBtn.style.display = currentStep > 1 ? 'inline-flex' : 'none';
    
    // Enable/disable next button based on validation
    const isValid = isCurrentStepValid();
    nextBtn.disabled = !isValid;
    
    // Show Next or Complete button - only on final step AND when role is selected
    if (isFinalStep() && selectedRole) {
      nextBtn.style.display = 'none';
      completeBtn.style.display = 'inline-flex';
      completeBtn.disabled = !isValid;
    } else {
      nextBtn.style.display = 'inline-flex';
      completeBtn.style.display = 'none';
    }
  }
  
  // Save current progress to sessionStorage
  function saveProgress() {
    const formData = {};
    
    // Save all form inputs
    document.querySelectorAll('#setup-form input[type="radio"]:checked').forEach(input => {
      formData[input.name] = input.value;
    });
    
    // Save profession type other text if present
    if (professionTypeOtherText && professionTypeOtherText.value) {
      formData['profession_type_other_text'] = professionTypeOtherText.value;
    }
    
    // Save current state
    const state = {
      currentStep: currentStep,
      selectedRole: selectedRole,
      formData: formData
    };
    
    sessionStorage.setItem('glassify_setup_progress', JSON.stringify(state));
  }
  
  // Restore progress from sessionStorage
  function restoreProgress() {
    const saved = sessionStorage.getItem('glassify_setup_progress');
    if (!saved) return;
    
    try {
      const state = JSON.parse(saved);
      
      // Restore form data
      Object.keys(state.formData).forEach(name => {
        const input = document.querySelector(`input[name="${name}"][value="${state.formData[name]}"]`);
        if (input) {
          input.checked = true;
          
          // Trigger change event for profession_type "other"
          if (name === 'profession_type' && state.formData[name] === 'other') {
            if (professionTypeOtherText) {
              professionTypeOtherText.style.display = 'block';
              professionTypeOtherText.required = true;
              if (state.formData['profession_type_other_text']) {
                professionTypeOtherText.value = state.formData['profession_type_other_text'];
              }
            }
          }
        }
      });
      
      // Restore role
      if (state.selectedRole) {
        selectedRole = state.selectedRole;
      }
      
      // Resume from saved step
      if (state.currentStep > 1) {
        goToStep(state.currentStep);
      }
      
      updateStepDisplay();
    } catch (e) {
      console.error('Error restoring progress:', e);
    }
  }
  
  // DON'T auto-restore progress - user must manually choose options
  // restoreProgress();
  
  // Clear session storage on successful submission
  document.getElementById('setup-form').addEventListener('submit', function() {
    sessionStorage.removeItem('glassify_setup_progress');
  });
});
</script>
