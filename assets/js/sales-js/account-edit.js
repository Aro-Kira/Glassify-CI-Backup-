// Immediate execution to verify script is loaded
console.log('account-edit.js file is being parsed and executed');

document.addEventListener("DOMContentLoaded", () => {
  console.log('=== account-edit.js loaded ===');
  
  const popup = document.getElementById("editPopup");
  const closeBtn = document.getElementById("closePopup");
  const cancelBtn = document.getElementById("cancelPopup");
  const popupLabel = document.getElementById("popupLabel");
  const popupTitle = document.getElementById("popupTitle");
  const popupInput = document.getElementById("popupInput");
  const editForm = document.getElementById("editForm");
  const confirmPasswordGroup = document.getElementById("confirmPasswordGroup");
  const popupConfirmPassword = document.getElementById("popupConfirmPassword");

  console.log('Elements found:', {
    popup: !!popup,
    editForm: !!editForm,
    saveBtn: !!document.getElementById('saveBtn'),
    popupInput: !!popupInput
  });

  let activeInput = null; // store the input being edited
  let activeField = null; // store the field name being edited

  // Map label names to database field names
  const fieldMap = {
    'Email': 'Email', // Note: Email cannot be changed, but keeping for reference
    'Password': 'Password',
    'First Name': 'First_Name',
    'Middle Name': 'Middle_Name',
    'Last Name': 'Last_Name',
    'Title': 'Role', // Note: Role cannot be changed, but keeping for reference
    'Phone Number': 'PhoneNum'
  };

  // Attach click to all edit icons
  const editIcons = document.querySelectorAll(".form-group .fa-pen, .form-group .fas.fa-pen, .input-box .fa-pen, .input-box .fas.fa-pen");
  console.log('Found edit icons:', editIcons.length);
  
  editIcons.forEach((icon, index) => {
    console.log(`Setting up icon ${index + 1}:`, icon);
    icon.style.cursor = 'pointer'; // Make sure cursor shows it's clickable
    icon.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      console.log('Edit icon clicked!', icon);
      const input = icon.previousElementSibling; // the <input> before the icon
      const labelElement = icon.closest(".form-group").querySelector("label");
      const label = labelElement ? labelElement.textContent : '';
      console.log('Input found:', input);
      console.log('Label found:', label);
      
      // Skip if it's Email, Title, or Account Created (readonly attribute is just for display, we still allow editing)
      if (label === 'Email' || label === 'Title' || label === 'Account Created') {
        console.log('Field is protected, skipping:', label);
        return; // Don't allow editing of protected fields
      }

      // Check if field is in the field map (allowed to edit)
      if (!fieldMap[label] || fieldMap[label] === 'Email' || fieldMap[label] === 'Role') {
        console.log('Field not in field map or protected:', label);
        return; // Don't allow editing of Email or Role
      }

      console.log('Field is editable, proceeding...');
      activeInput = input;
      activeField = fieldMap[label] || null;
      console.log('Active field set to:', activeField);

      popupLabel.textContent = label;
      popupTitle.textContent = `Edit ${label}`;
      console.log('Popup label and title set');
      
      // Get the actual value (not the masked password)
      if (label.toLowerCase().includes("password")) {
        popupInput.value = ""; // Clear for password
        popupInput.type = "password";
        popupInput.placeholder = "Enter new password";
        popupConfirmPassword.value = ""; // Clear confirm password
        confirmPasswordGroup.style.display = "block"; // Show confirm password field
      } else {
        popupInput.value = input.value;
        popupInput.type = "text";
        popupInput.placeholder = "";
        confirmPasswordGroup.style.display = "none"; // Hide confirm password field
      }

      popup.style.display = "flex";
      console.log('=== Popup opened for field:', activeField, '===');
      console.log('Active input:', activeInput);
      console.log('Current value:', popupInput.value);
      console.log('Popup element:', popup);
      console.log('Popup display style:', window.getComputedStyle(popup).display);
    });
  });
  
  console.log('Edit icon listeners attached to', editIcons.length, 'icons');
  
  // Fallback: Use event delegation in case icons are added dynamically
  document.addEventListener('click', (e) => {
    if (e.target && (e.target.classList.contains('fa-pen') || e.target.classList.contains('fas') && e.target.classList.contains('fa-pen'))) {
      const icon = e.target;
      const formGroup = icon.closest('.form-group');
      if (formGroup) {
        const input = icon.previousElementSibling;
        const label = formGroup.querySelector("label");
        if (label && input) {
          const labelText = label.textContent;
          console.log('Edit icon clicked via delegation:', labelText);
          
          // Skip if field is readonly or if it's Email, Title, or Account Created
          if (input.hasAttribute('readonly') || labelText === 'Email' || labelText === 'Title' || labelText === 'Account Created') {
            console.log('Field is readonly or protected, skipping');
            return;
          }

          // Check if field is in the field map (allowed to edit)
          if (!fieldMap[labelText] || fieldMap[labelText] === 'Email' || fieldMap[labelText] === 'Role') {
            console.log('Field not in field map or protected, skipping');
            return;
          }

          activeInput = input;
          activeField = fieldMap[labelText] || null;

          popupLabel.textContent = labelText;
          popupTitle.textContent = `Edit ${labelText}`;
          
          // Get the actual value (not the masked password)
          if (labelText.toLowerCase().includes("password")) {
            popupInput.value = "";
            popupInput.type = "password";
            popupInput.placeholder = "Enter new password";
            popupConfirmPassword.value = "";
            confirmPasswordGroup.style.display = "block";
          } else {
            popupInput.value = input.value;
            popupInput.type = "text";
            popupInput.placeholder = "";
            confirmPasswordGroup.style.display = "none";
          }

          popup.style.display = "flex";
          console.log('Popup opened for field (via delegation):', activeField);
        }
      }
    }
  });
  console.log('Event delegation for edit icons attached');

  // Close popup
  [closeBtn, cancelBtn].forEach(btn => {
    btn.addEventListener("click", () => {
      popup.style.display = "none";
      activeInput = null;
      activeField = null;
      popupInput.value = "";
      popupConfirmPassword.value = "";
      confirmPasswordGroup.style.display = "none";
    });
  });

  // Function to handle save (extracted for reuse)
  function handleSave() {
    console.log('handleSave() called');
    console.log('Active input:', activeInput);
    console.log('Active field:', activeField);
    
    // Verify that Save button was clicked (not accidental submission)
    if (!activeInput || !activeField) {
      console.error('No field selected for editing');
      alert("No field selected for editing.");
      return;
    }

    let newValue = popupInput.value.trim();
    const confirmPassword = popupConfirmPassword.value.trim();
    
    // Check if value actually changed (client-side check)
    const currentValue = activeInput.value;
    if (activeField !== 'Password' && currentValue === newValue) {
      alert("No changes detected. The value is the same as the current value.");
      return;
    }

    // Validate based on field type
    if (activeField === 'Password') {
      if (newValue.length < 6) {
        alert("Password must be at least 6 characters long.");
        popupInput.focus();
        return;
      }
      if (newValue !== confirmPassword) {
        alert("Passwords do not match. Please try again.");
        popupConfirmPassword.focus();
        return;
      }
    } else if (activeField === 'PhoneNum') {
      // Validate phone number format (digits only, 10-13 characters)
      const phoneRegex = /^[0-9]{10,13}$/;
      if (!phoneRegex.test(newValue)) {
        alert("Phone number must be 10-13 digits only.");
        popupInput.focus();
        return;
      }
    } else if (activeField === 'First_Name' || activeField === 'Last_Name') {
      // Validate name (letters, spaces, hyphens, apostrophes only)
      const nameRegex = /^[a-zA-Z\s\-']+$/;
      if (!nameRegex.test(newValue)) {
        alert("Name can only contain letters, spaces, hyphens, and apostrophes.");
        popupInput.focus();
        return;
      }
      if (newValue.length < 2) {
        alert("Name must be at least 2 characters long.");
        popupInput.focus();
        return;
      }
      // Capitalize first letter of each word
      newValue = newValue.toLowerCase().split(' ').map(word => {
        if (word.length > 0) {
          return word.charAt(0).toUpperCase() + word.slice(1);
        }
        return word;
      }).join(' ');
    } else if (activeField === 'Middle_Name') {
      // Middle name is optional, but if provided, validate format
      if (newValue && newValue.length > 0) {
        const nameRegex = /^[a-zA-Z\s\-'.]+$/;
        if (!nameRegex.test(newValue)) {
          alert("Middle name can only contain letters, spaces, hyphens, apostrophes, and periods.");
          popupInput.focus();
          return;
        }
        // Capitalize first letter of each word
        newValue = newValue.toLowerCase().split(' ').map(word => {
          if (word.length > 0) {
            return word.charAt(0).toUpperCase() + word.slice(1);
          }
          return word;
        }).join(' ');
      }
    } else if (activeField !== 'Middle_Name' && !newValue) {
      alert("This field cannot be empty.");
      popupInput.focus();
      return;
    }

    // Show loading state
    const saveBtn = editForm.querySelector('.save-btn') || document.getElementById('saveBtn');
    if (!saveBtn) {
      console.error('Save button not found!');
      alert('Error: Save button not found. Please refresh the page.');
      return;
    }
    
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Save';
    saveBtn.disabled = true;

    console.log('Preparing to send update request...');
    console.log('Field:', activeField);
    console.log('Value:', activeField === 'Password' ? '***' : newValue);

    // Send AJAX request to update database
    const formData = new FormData();
    formData.append('field', activeField);
    formData.append('value', newValue);
    
    console.log('FormData created, field:', activeField, 'value length:', newValue.length);

    // Construct API URL (matching pattern used in other JS files)
    // Other files use: base_url + "Controller/method" (without index.php)
    const apiUrl = base_url + 'SalesCon/update_account';
    
    // Log the request being sent
    console.log('Base URL:', base_url);
    console.log('Full API URL:', apiUrl);
    console.log('Field:', activeField, 'Value:', activeField === 'Password' ? '***' : newValue);

    fetch(apiUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin' // Include cookies/session
    })
    .then(response => {
      console.log('Response status:', response.status);
      console.log('Response URL:', response.url);
      
      // If 404, try with index.php as fallback
      if (response.status === 404) {
        console.warn('Got 404, trying with index.php as fallback...');
        const apiUrlWithIndex = base_url + 'index.php/SalesCon/update_account';
        console.log('Trying fallback URL:', apiUrlWithIndex);
        
        return fetch(apiUrlWithIndex, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });
      }
      
      // Check if response is JSON
      const contentType = response.headers.get("content-type");
      if (contentType && contentType.includes("application/json")) {
        return response.json();
      } else {
        // If not JSON, get text to see what the error is
        return response.text().then(text => {
          console.error('Non-JSON response:', text);
          console.error('Response status:', response.status);
          if (response.status === 404) {
            throw new Error('404 Not Found: The endpoint SalesCon/update_account was not found. URL tried: ' + apiUrl);
          }
          throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
        });
      }
    })
    .then(data => {
      if (data.success) {
        // Only update UI after successful database save
        if (activeField === 'Password') {
          activeInput.value = "************"; // Mask password
        } else {
          activeInput.value = newValue;
        }
        
        // If name changed, reload page to update header
        if (activeField === 'First_Name' || activeField === 'Last_Name') {
          alert("Account updated successfully! Page will reload to reflect changes.");
          window.location.reload();
        } else {
          alert("Account updated successfully!");
          popup.style.display = "none";
          popupInput.value = "";
          popupConfirmPassword.value = "";
          confirmPasswordGroup.style.display = "none";
          activeInput = null;
          activeField = null;
        }
      } else {
        // Display error message from server
        const errorMsg = data.message || "Failed to update account";
        alert("Error: " + errorMsg);
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
      }
    })
    .catch(error => {
      console.error('Fetch error:', error);
      console.error('Error name:', error.name);
      console.error('Error message:', error.message);
      console.error('Error stack:', error.stack);
      alert("An error occurred while updating the account: " + error.message + "\n\nPlease check the browser console (F12) for more details.");
      const errorSaveBtn = editForm.querySelector('.save-btn') || document.getElementById('saveBtn');
      if (errorSaveBtn) {
        errorSaveBtn.textContent = originalText;
        errorSaveBtn.disabled = false;
      }
    });
  }

  // Save changes - triggered when Save button is clicked OR form is submitted
  console.log('Setting up save button handlers...');
  
  if (editForm) {
    console.log('editForm found, attaching submit listener');
    editForm.addEventListener("submit", (e) => {
      e.preventDefault(); // Prevent form from submitting normally
      e.stopPropagation();
      console.log('=== Form submit event triggered ===');
      handleSave();
    });
    console.log('✓ Form submit event listener attached');
  } else {
    console.error('✗ editForm not found!');
  }

  // Also add direct click handler for Save button (backup)
  const saveBtnElement = document.getElementById('saveBtn');
  if (saveBtnElement) {
    console.log('saveBtn found, attaching click listener');
    saveBtnElement.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      console.log('=== Save button clicked directly ===');
      handleSave();
    });
    console.log('✓ Save button click event listener attached');
    
    // Test: Try to trigger manually
    console.log('Save button element:', saveBtnElement);
    console.log('Save button type:', saveBtnElement.type);
    console.log('Save button parent form:', saveBtnElement.closest('form'));
  } else {
    console.warn('✗ Save button with id="saveBtn" not found on page load');
  }
  
  // Use event delegation as additional backup (works even if button is dynamically created)
  document.addEventListener('click', (e) => {
    if (e.target && (e.target.id === 'saveBtn' || e.target.classList.contains('save-btn'))) {
      console.log('=== Save button clicked via event delegation ===');
      e.preventDefault();
      e.stopPropagation();
      handleSave();
    }
  });
  console.log('✓ Event delegation listener attached for save button');
  
  console.log('=== Event listeners setup complete ===');
  
  // Close if clicked outside
  window.addEventListener("click", (e) => {
    if (e.target === popup) {
      popup.style.display = "none";
      activeInput = null;
      activeField = null;
      popupInput.value = "";
      popupConfirmPassword.value = "";
      confirmPasswordGroup.style.display = "none";
    }
  });
  
  console.log('=== All event listeners attached ===');
});
