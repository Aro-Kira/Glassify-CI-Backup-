// =====================================================
// DYNAMIC CUSTOMIZATION RENDERER
// Renders customization fields dynamically based on product data
// Connects to Konva.js for visualization
// =====================================================

// Store selected values for all fields
let selectedCustomizationValues = {};

// Expose to window for access from other scripts
if (typeof window !== 'undefined') {
    window.selectedCustomizationValues = selectedCustomizationValues;
}

// Get selectedProduct from window (set from 2DModeling.php)
function getSelectedProduct() {
  return window.selectedProduct || null;
}

/**
 * Renders customization fields dynamically based on product configuration
 * @param {Array} fields - Array of field configurations from product
 * @param {Object} tagPrices - Tag prices object { fieldId: { tagName: price } }
 * @param {HTMLElement} container - Container to render fields in
 * @param {Object} tagImages - Tag images object { fieldId: { tagName: imageUrl } }
 * @param {Object} stepNames - Step names object { "1": "Step Name", "2": "Step Name" }
 * @param {Object} selectedOptions - Admin-selected options per field { fieldId: ["option1", "option2"] } - only these will be shown
 */
function renderDynamicCustomizationFields(fields, tagPrices, container, tagImages = {}, stepNames = null, selectedOptions = null) {
  if (!fields || !Array.isArray(fields) || fields.length === 0) {
    console.warn('No customization fields found for this product');
    // Show a message to the user
    container.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">No customization options available for this product.</p>';
    return;
  }
  
  // Store selectedOptions globally for use in renderTagsField
  window.productSelectedOptions = selectedOptions || {};

  // Clear existing content
  container.innerHTML = '';

  // Group fields by step property (from admin configuration)
  const stepContainers = {};
  const stepNumbers = new Set();
  const renderedFieldIds = new Set(); // For global deduplication

  // First pass: identify all unique steps and force specific fields to Step 1
  fields.forEach((field) => {
    // FORCE Step 1 for specific fields to avoid "Step 2" confusion
    if (field.id === 'numberOfPanels' || field.id === 'transomType') {
      field.stepNumber = 1;
    }
    
    // Check stepNumber first (admin config), then step (legacy), default to 1
    const stepNum = field.stepNumber || field.step || 1;
    stepNumbers.add(stepNum);
  });

  // Create step containers
  const sortedSteps = Array.from(stepNumbers).sort((a, b) => a - b);
  sortedSteps.forEach((stepNum) => {
    let stepDiv = document.getElementById(`step-${stepNum}`);
    if (!stepDiv) {
      stepDiv = document.createElement('div');
      stepDiv.id = `step-${stepNum}`;
      stepDiv.className = 'step-container';
      container.appendChild(stepDiv);
    }
    stepDiv.className = stepNum === 1 ? 'step-container' : 'step-container hidden-step';
    stepDiv.dataset.stepNumber = stepNum;
    stepContainers[stepNum] = stepDiv;
  });

  // Second pass: render fields into their assigned steps with global deduplication
  fields.forEach((field) => {
    if (renderedFieldIds.has(field.id)) {
      console.log(`Skipping duplicate field rendering for "${field.id}"`);
      return;
    }
    renderedFieldIds.add(field.id);

    const stepNum = field.stepNumber || field.step || 1;
    const stepContainer = stepContainers[stepNum];
    
    if (!stepContainer) return;

    const fieldGroup = createFieldElement(field, tagPrices, tagImages);
    stepContainer.appendChild(fieldGroup);
  });

  // Third pass: Remove empty steps (steps where all fields are hidden)
  // This happens when admin didn't select any tags for options in that step
  const stepsToRemove = [];
  sortedSteps.forEach((stepNum) => {
    const stepContainer = stepContainers[stepNum];
    if (stepContainer) {
      // Check if step has any visible fields
      const allFields = stepContainer.children;
      let hasVisibleField = false;
      
      for (let i = 0; i < allFields.length; i++) {
        const field = allFields[i];
        // Check if field is visible (not hidden via display:none)
        if (field.style.display !== 'none') {
          hasVisibleField = true;
          break;
        }
      }
      
      if (!hasVisibleField) {
        console.log(`Step ${stepNum} has no visible fields, marking for removal`);
        stepsToRemove.push(stepNum);
      }
    }
  });
  
  // Remove empty steps from DOM and update step containers
  stepsToRemove.forEach((stepNum) => {
    const stepContainer = stepContainers[stepNum];
    if (stepContainer && stepContainer.parentNode) {
      stepContainer.parentNode.removeChild(stepContainer);
    }
    delete stepContainers[stepNum];
  });
  
  // Get remaining steps and re-number them
  const remainingSteps = sortedSteps.filter(s => !stepsToRemove.includes(s));
  console.log('Remaining steps after removing empty ones:', remainingSteps);
  
  // Re-number step containers to be sequential (1, 2, 3, ...)
  const updatedStepNames = {};
  remainingSteps.forEach((oldStepNum, index) => {
    const newStepNum = index + 1;
    const stepContainer = stepContainers[oldStepNum];
    
    if (stepContainer) {
      // Update step container ID and dataset
      stepContainer.id = `step-${newStepNum}`;
      stepContainer.dataset.stepNumber = newStepNum;
      
      // Show step 1, hide others
      if (newStepNum === 1) {
        stepContainer.classList.remove('hidden-step');
      } else {
        stepContainer.classList.add('hidden-step');
      }
      
      // Map old step names to new step numbers
      if (stepNames && stepNames[String(oldStepNum)]) {
        updatedStepNames[String(newStepNum)] = stepNames[String(oldStepNum)];
      }
    }
  });
  
  // Use updated step names if we had to re-number
  const finalStepNames = Object.keys(updatedStepNames).length > 0 ? updatedStepNames : stepNames;
  const finalTotalSteps = remainingSteps.length;
  
  console.log('Final total steps:', finalTotalSteps);
  console.log('Final step names:', finalStepNames);

  // Handle case where all steps are empty (no options selected by admin)
  if (finalTotalSteps === 0) {
    console.log('All steps are empty - no customization options available');
    // Don't show an empty message, just allow direct progression to summary
    // The dimensions container will still be shown for height/width input
    container.innerHTML = ''; // Clear the container
  }

  // Store step names globally for navigation
  if (finalStepNames && typeof window !== 'undefined') {
    window.customizationStepNames = finalStepNames;
  }
  
  // Update navigation and step indicators with actual remaining steps count
  updateStepNavigation(finalTotalSteps, finalStepNames);
  
  // Re-initialize step navigation if it exists
  if (typeof window !== 'undefined' && window.currentStep !== undefined) {
    window.currentStep = 1;
  }
  
  // Ensure only one option is active per field group
  enforceSingleSelection();
  
  // Check corner radius visibility after initial render
  setTimeout(() => {
    const cornerRadiusContainers = document.querySelectorAll('[data-conditional-field="true"][data-depends-on="shape"]');
    cornerRadiusContainers.forEach(container => {
      checkCornerRadiusVisibility(container);
    });
  }, 200);
  
  // Sync JavaScript state with active selections and re-render Konva
  // This ensures the first selected shape is applied to the canvas
  setTimeout(() => {
    syncStateFromActiveSelections();
    
    // Initialize conditional logic for Windows_Sliding fields after fields are rendered
    setTimeout(() => {
      // Check initial state of fields that affect conditionals
      const transomTypeContainer = document.querySelector('[data-field-id="transomType"]');
      const numberOfPanelsContainer = document.querySelector('[data-field-id="numberOfPanels"]');
      const trackSystemContainer = document.querySelector('[data-field-id="trackSystem"]');
      
      if (transomTypeContainer) {
        const activeTransom = transomTypeContainer.querySelector('.option-card.active');
        if (activeTransom) {
          const transomValue = activeTransom.dataset.value || activeTransom.textContent.trim();
          handleWindowsSlidingConditionals('transomType', transomValue);
        }
      }
      
      if (numberOfPanelsContainer) {
        const activePanels = numberOfPanelsContainer.querySelector('.option-card.active');
        if (activePanels) {
          const panelsValue = activePanels.dataset.value || activePanels.textContent.trim();
          handleWindowsSlidingConditionals('numberOfPanels', panelsValue);
        }
      }
      
      if (trackSystemContainer) {
        const activeTrack = trackSystemContainer.querySelector('.option-card.active');
        if (activeTrack) {
          const trackValue = activeTrack.dataset.value || activeTrack.textContent.trim();
          handleWindowsSlidingConditionals('trackSystem', trackValue);
        }
      }
    }, 350);
  }, 300);
}

/**
 * Ensures only one option is active per field group
 * This is a safety function to fix any cases where multiple options might be active
 */
function enforceSingleSelection() {
  // Find all field containers (both tag-container and grid-3-cols)
  const fieldContainers = document.querySelectorAll('[data-field-id]');
  const gridContainers = document.querySelectorAll('.grid-3-cols');
  
  // Process field containers
  fieldContainers.forEach(container => {
    const activeCards = container.querySelectorAll('.option-card.active');
    
    // If more than one is active, keep only the first one (or none if user prefers)
    // Here we just ensure we don't have multiple
    if (activeCards.length > 1) {
      for (let i = 1; i < activeCards.length; i++) {
        activeCards[i].classList.remove('active');
      }
    }
  });
  
  // Also check grid containers
  gridContainers.forEach(grid => {
    const activeCards = grid.querySelectorAll('.option-card.active');
    
    if (activeCards.length > 1) {
      for (let i = 1; i < activeCards.length; i++) {
        activeCards[i].classList.remove('active');
      }
    }
  });
  
  // Also check field sections
  const fieldSections = document.querySelectorAll('.type-section, .thickness-section, .edge-section, .frame-section, .field-section');
  fieldSections.forEach(section => {
    const activeCards = section.querySelectorAll('.option-card.active');
    
    if (activeCards.length > 1) {
      for (let i = 1; i < activeCards.length; i++) {
        activeCards[i].classList.remove('active');
      }
    }
  });
}

// Run enforcement only once on page load to fix initial state
// Don't run repeatedly to avoid interfering with user clicks
if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      // Run once after a delay to ensure fields are rendered
      setTimeout(() => {
        enforceSingleSelection();
        // After ensuring single selection, sync state and re-render Konva
        setTimeout(() => {
          syncStateFromActiveSelections();
        }, 100);
      }, 1000);
    });
  } else {
    // DOM already loaded - run once
    setTimeout(() => {
      enforceSingleSelection();
      // After ensuring single selection, sync state and re-render Konva
      setTimeout(() => {
        syncStateFromActiveSelections();
      }, 100);
    }, 1000);
  }
}

/**
 * Creates a field element based on field configuration
 */
function createFieldElement(field, tagPrices, tagImages = {}) {
  const fieldGroup = document.createElement('div');
  fieldGroup.className = getFieldGroupClass(field.type);
  
  const label = document.createElement('label');
  label.className = 'section-label';
  // Simplified labels as per user request
  let displayLabel = field.label;
  if (field.id === 'numberOfPanels') displayLabel = 'Panel';
  if (field.id === 'transomType') displayLabel = 'Transom Type';
  
  label.textContent = displayLabel;
  fieldGroup.appendChild(label);

  // Render field based on type
  switch (field.type) {
    case 'tags':
      renderTagsField(field, tagPrices, fieldGroup, tagImages);
      break;
    case 'checkbox':
      renderCheckboxField(field, fieldGroup);
      break;
    case 'number':
      renderNumberField(field, fieldGroup);
      break;
    case 'color':
      renderColorField(field, fieldGroup);
      break;
  }

  return fieldGroup;
}

/**
 * Renders a tags field
 */
function renderTagsField(field, tagPrices, container, tagImages = {}) {
  const tagContainer = document.createElement('div');
  tagContainer.className = 'tag-container';
  tagContainer.id = `${field.id}Container`;
  tagContainer.dataset.fieldId = field.id;

  let options = field.options || [];
  
  // Filter options based on admin-selected options (only show selected tags to customers)
  const selectedOptions = window.productSelectedOptions || {};
  if (selectedOptions && selectedOptions[field.id] && Array.isArray(selectedOptions[field.id]) && selectedOptions[field.id].length > 0) {
    // Only show options that are in the admin-selected list
    options = options.filter(opt => selectedOptions[field.id].includes(opt));
    console.log(`Field "${field.id}": Filtered options from ${field.options.length} to ${options.length} based on admin selection:`, selectedOptions[field.id]);
  } else if (selectedOptions && Object.keys(selectedOptions).length > 0) {
    // Admin has configured selections but this field has no selections - hide entirely
    console.log(`Field "${field.id}": No options selected by admin, hiding field`);
    container.style.display = 'none';
    return;
  }
  
  // If no options left after filtering, hide the entire field
  if (options.length === 0) {
    console.log(`Field "${field.id}": No options available, hiding field`);
    container.style.display = 'none';
    return;
  }
  
  // Move "None" to the end for transomType field
  if (field.id === 'transomType') {
    const noneIndex = options.indexOf('None');
    if (noneIndex > -1) {
      options.splice(noneIndex, 1);
      options.push('None');
    }
  }

  // Render options directly into tagContainer
  options.forEach((option, index) => {
    const tag = document.createElement('div');
    tag.className = 'option-card';
    
    // User requested NO pre-selected options
    // Screen auto-selection is now handled exclusively by handleWindowsSlidingConditionals or user interaction
    
    // DO NOT add active to any other option
    tag.dataset.value = option;
    tag.dataset.fieldId = field.id;
    
    // Create tag content wrapper
    const tagContent = document.createElement('div');
    tagContent.className = 'tag-content-wrapper';
    
    // Add image if available
    if (tagImages && tagImages[field.id] && tagImages[field.id][option]) {
      const tagImage = document.createElement('img');
      tagImage.className = 'tag-option-image';
      tagImage.src = tagImages[field.id][option];
      tagImage.alt = option;
      tagImage.onerror = function() {
        this.style.display = 'none';
      };
      tagContent.appendChild(tagImage);
    }
    
    const tagText = document.createTextNode(option);
    tagContent.appendChild(tagText);
    tag.appendChild(tagContent);
    
    // Add price if available
    if (tagPrices && tagPrices[field.id] && tagPrices[field.id][option]) {
      const priceSpan = document.createElement('span');
      priceSpan.className = 'tag-price-display';
      priceSpan.textContent = ` (₱${parseFloat(tagPrices[field.id][option]).toFixed(2)})`;
      tag.appendChild(priceSpan);
    }

    // Function to handle selection
    const handleSelection = (isDeselect = false) => {
      const section = tag.closest('.type-section, .thickness-section, .edge-section, .frame-section, .field-section, div[class$="-section"]');
      
      if (section) {
        section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        if (!isDeselect) {
          tag.classList.add('active');
          selectedCustomizationValues[field.id] = option;
        } else {
          delete selectedCustomizationValues[field.id];
        }
      }

      if (typeof window !== 'undefined') {
        window.selectedCustomizationValues = selectedCustomizationValues;
      }

      // Update price and visualization
      updateKonvaFromField(field.id, isDeselect ? null : option, true);
      
      if (field.id === 'shape') {
        setTimeout(() => {
          const cornerRadiusContainers = document.querySelectorAll('[data-conditional-field="true"][data-depends-on="shape"]');
          cornerRadiusContainers.forEach(container => {
            checkCornerRadiusVisibility(container);
          });
        }, 50);
      }
      
      handleWindowsSlidingConditionals(field.id, isDeselect ? null : option);
      
      if (typeof window !== 'undefined' && typeof window.updateRealTimePriceDisplay === 'function') {
        window.updateRealTimePriceDisplay();
      }
    };

    tag.addEventListener('click', function() {
      handleSelection(false);
    });

    // Double click to deselect
    tag.addEventListener('dblclick', function(e) {
      e.preventDefault();
      handleSelection(true);
    });

    tagContainer.appendChild(tag);
  });

  container.appendChild(tagContainer);
  
  // For Screen field, check initial track system state after rendering
  if (field.id === 'screen') {
    setTimeout(() => {
      updateScreenAvailability();
    }, 150);
  }
  
  // Immediately ensure only one option is active in this field group
  setTimeout(() => {
    const allCards = tagContainer.querySelectorAll('.option-card');
    const activeCards = tagContainer.querySelectorAll('.option-card.active');
    
    // If more than one is active, remove active from all and keep none (per user request for no pre-selection)
    if (activeCards.length > 1) {
      allCards.forEach(card => card.classList.remove('active'));
    }
  }, 10);
}

/**
 * Updates price based on tag selection
 * Now uses the centralized calculateTotal() function for accurate pricing
 */
function updatePriceFromTagSelection(fieldId, tagName, isSelected) {
  // Trigger full price recalculation using the new database-based system
  if (typeof window !== 'undefined' && typeof window.updateRealTimePriceDisplay === 'function') {
    window.updateRealTimePriceDisplay();
  }
}

/**
 * Renders a checkbox field
 */
function renderCheckboxField(field, container) {
  const checkboxWrapper = document.createElement('div');
  checkboxWrapper.className = 'checkbox-wrapper';
  checkboxWrapper.id = `${field.id}Container`;
  checkboxWrapper.dataset.fieldId = field.id;
  
  const checkbox = document.createElement('input');
  checkbox.type = 'checkbox';
  checkbox.id = field.id;
  checkbox.name = field.id;
  
  const label = document.createElement('label');
  label.setAttribute('for', field.id);
  label.textContent = field.label;
  
  checkbox.addEventListener('change', () => {
    updateKonvaFromField(field.id, checkbox.checked, true);
  });

  checkboxWrapper.appendChild(checkbox);
  checkboxWrapper.appendChild(label);
  
  // For Screen field, check initial track system state
  if (field.id === 'screen') {
    setTimeout(() => {
      updateScreenAvailability();
    }, 100);
  }
  
  container.appendChild(checkboxWrapper);
}

/**
 * Renders a number field
 */
function renderNumberField(field, container) {
  const inputGroup = document.createElement('div');
  inputGroup.className = 'input-group';
  
  const input = document.createElement('input');
  input.type = 'number';
  input.id = field.id;
  input.name = field.id;
  input.min = field.min || 0;
  input.step = field.step || 1;
  input.placeholder = `Enter ${field.label.toLowerCase()}`;
  input.className = 'dimension-input';
  
  // For corner radius field, show/hide based on shape selection
  if (field.id === 'cornerRadius' || field.id === 'radius') {
    container.style.display = 'none'; // Hide by default
    container.dataset.conditionalField = 'true';
    container.dataset.dependsOn = 'shape';
    
    // Check initial shape selection
    checkCornerRadiusVisibility(container);
    
    // Listen for shape changes
    document.addEventListener('customizationFieldChanged', (e) => {
      if (e.detail.fieldId === 'shape') {
        checkCornerRadiusVisibility(container);
      }
    });
  }
  
  input.addEventListener('input', () => {
    updateKonvaFromField(field.id, parseFloat(input.value) || 0, true);
  });

  inputGroup.appendChild(input);
  container.appendChild(inputGroup);
}

/**
 * Check if corner radius field should be visible based on selected shape
 */
function checkCornerRadiusVisibility(container) {
  // Get selected shape value
  const shapeField = document.querySelector('[data-field-id="shape"]');
  if (!shapeField) {
    container.style.display = 'none';
    return;
  }
  
  const activeShapeCard = shapeField.querySelector('.option-card.active');
  if (!activeShapeCard) {
    container.style.display = 'none';
    return;
  }
  
  const selectedShape = (activeShapeCard.dataset.value || activeShapeCard.textContent.trim()).toLowerCase();
  const rectangleShapes = ['rectangle', 'rectangular', 'square'];
  
  // Show only if rectangle or square is selected
  if (rectangleShapes.includes(selectedShape)) {
    container.style.display = '';
  } else {
    container.style.display = 'none';
    // Reset value when hidden
    const input = container.querySelector('input[type="number"]');
    if (input) {
      input.value = '0';
      updateKonvaFromField('cornerRadius', 0, true);
    }
  }
}

/**
 * Handle conditional logic for Windows_Sliding fields
 */
function handleWindowsSlidingConditionals(changedFieldId, selectedValue) {
  // Rule 1: Track System depends on Transom Type
  if (changedFieldId === 'transomType') {
    const trackSystemContainer = document.querySelector('[data-field-id="trackSystem"]');
    if (trackSystemContainer) {
      const trackOptions = trackSystemContainer.querySelectorAll('.option-card');
      const isFixedTransomSill = selectedValue.includes('Fixed Transom Sill');
      
      trackOptions.forEach(option => {
        const optionValue = option.dataset.value || option.textContent.trim();
        if (isFixedTransomSill && optionValue === '3 Tracks') {
          // Disable 3 Tracks if Fixed Transom Sill is selected
          option.style.opacity = '0.5';
          option.style.pointerEvents = 'none';
          option.classList.add('disabled');
          // If 3 Tracks was selected, switch to 2 Tracks
          if (option.classList.contains('active')) {
            option.classList.remove('active');
            const twoTracksOption = Array.from(trackOptions).find(opt => opt.dataset.value === '2 Tracks');
            if (twoTracksOption) {
              twoTracksOption.classList.add('active');
              selectedCustomizationValues['trackSystem'] = '2 Tracks';
            }
          }
        } else {
          // Enable all options if None or Fixed Transom Head
          option.style.opacity = '';
          option.style.pointerEvents = '';
          option.classList.remove('disabled');
        }
      });
      
      // Update Screen checkbox based on track system
      updateScreenAvailability();
    }
  }
  
  // Rule 2: Track System changes affect Screen availability
  if (changedFieldId === 'trackSystem') {
    updateScreenAvailability();
    
    // Also sync Step 2 Screen Option with Step 4 Screen
    const screenOptionContainer = document.querySelector('[data-field-id="screenOption"]');
    const screenContainer = document.querySelector('[data-field-id="screen"]');
    
    if (screenOptionContainer && screenContainer) {
      const activeOption = screenOptionContainer.querySelector('.option-card.active');
      if (activeOption) {
        const value = activeOption.dataset.value || activeOption.textContent.trim();
        syncScreenFields('screenOption', value);
      }
    }
  }
  
  // Sync Step 2 Screen Option and Step 4 Screen
  if (changedFieldId === 'screenOption' || changedFieldId === 'screen') {
    syncScreenFields(changedFieldId, selectedValue);
  }
  
  // Rule 3: Panel Configuration depends on Number of Panels
  if (changedFieldId === 'numberOfPanels') {
    const panelConfigContainer = document.querySelector('[data-field-id="panelConfiguration"]');
    if (panelConfigContainer) {
      const configOptions = panelConfigContainer.querySelectorAll('.option-card');
      const isTwoPanels = selectedValue === '2 Panels';
      const isFourPanels = selectedValue === '4 Panels';
      
      configOptions.forEach(option => {
        const optionValue = option.dataset.value || option.textContent.trim();
        
        // Exact matches from user requirements
        const isTwoPanelOption = optionValue.startsWith('S | S') && !optionValue.includes('S | S | S | S') || 
                                 optionValue.startsWith('F | S') && !optionValue.includes('F | S | S | F');
        const isFourPanelOption = optionValue.includes('S | S | S | S') || optionValue.includes('F | S | S | F');
        
        if (isTwoPanels) {
          option.style.display = isTwoPanelOption ? '' : 'none';
        } else if (isFourPanels) {
          option.style.display = isFourPanelOption ? '' : 'none';
        }
        
        if (option.style.display === 'none' && option.classList.contains('active')) {
          option.classList.remove('active');
        }
      });
      
      /* 
      // Auto-select first visible option if none selected - REMOVED AS PER USER REQUEST
      setTimeout(() => {
        const activeConfig = panelConfigContainer.querySelector('.option-card.active');
        if (!activeConfig || activeConfig.style.display === 'none') {
          const visibleOptions = Array.from(panelConfigContainer.querySelectorAll('.option-card')).filter(opt => opt.style.display !== 'none');
          if (visibleOptions.length > 0) {
            visibleOptions[0].classList.add('active');
            const newValue = visibleOptions[0].dataset.value || visibleOptions[0].textContent.trim();
            selectedCustomizationValues['panelConfiguration'] = newValue;
            updateKonvaFromField('panelConfiguration', newValue, true);
          }
        }
      }, 50);
      */
    }
  }
  
  // Rule 4: Lock Type and Roller Type depend on Series
  const product = getSelectedProduct();
  if (product && product.series) {
    const series = product.series;
    
    // Lock Type filtering
    const lockTypeContainer = document.querySelector('[data-field-id="lockType"]');
    if (lockTypeContainer) {
      const lockOptions = lockTypeContainer.querySelectorAll('.option-card');
      const allowedLocks = series.includes('798') 
        ? ['Enter Lock 908', 'Enter Lock 907', 'Flushlock #12', 'New Flushlock']
        : ['Center Lok 904 Big', 'Flushlok #12', 'Durable Flushlok', 'New Auto Flushlock'];
        
      lockOptions.forEach(option => {
        const value = option.dataset.value || option.textContent.trim();
        option.style.display = allowedLocks.includes(value) ? '' : 'none';
        if (option.style.display === 'none' && option.classList.contains('active')) {
          option.classList.remove('active');
        }
      });
      
      // Auto-select if none selected
      if (!lockTypeContainer.querySelector('.option-card.active')) {
        const firstVisible = Array.from(lockOptions).find(opt => opt.style.display !== 'none');
        if (firstVisible) firstVisible.classList.add('active');
      }
    }
    
    // Roller Type filtering
    const rollerTypeContainer = document.querySelector('[data-field-id="rollerType"]');
    if (rollerTypeContainer) {
      const rollerOptions = rollerTypeContainer.querySelectorAll('.option-card');
      const allowedRollers = series.includes('798')
        ? ['Single Roller ORD', 'Single Roller with Bearing', 'Double Roller HD', 'Blue Single Roller', 'Blue Double Roller']
        : ['Single Panel Roller', 'Blue Single Roller', 'Blue Double Roller'];
        
      rollerOptions.forEach(option => {
        const value = option.dataset.value || option.textContent.trim();
        option.style.display = allowedRollers.includes(value) ? '' : 'none';
        if (option.style.display === 'none' && option.classList.contains('active')) {
          option.classList.remove('active');
        }
      });
      
      // Auto-select if none selected
      if (!rollerTypeContainer.querySelector('.option-card.active')) {
        const firstVisible = Array.from(rollerOptions).find(opt => opt.style.display !== 'none');
        if (firstVisible) firstVisible.classList.add('active');
      }
    }
  }
}

/**
 * Sync Screen fields between Step 2 and Step 4
 */
function syncScreenFields(changedFieldId, value) {
  const otherFieldId = changedFieldId === 'screenOption' ? 'screen' : 'screenOption';
  const otherContainer = document.querySelector(`[data-field-id="${otherFieldId}"]`);
  
  if (otherContainer) {
    const otherOptions = otherContainer.querySelectorAll('.option-card');
    otherOptions.forEach(option => {
      const optionValue = option.dataset.value || option.textContent.trim();
      if (optionValue === value) {
        option.classList.add('active');
        selectedCustomizationValues[otherFieldId] = value;
      } else {
        option.classList.remove('active');
      }
    });
  }
}

/**
 * Update Screen tags field availability based on Track System
 */
function updateScreenAvailability() {
  const trackSystemContainer = document.querySelector('[data-field-id="trackSystem"]');
  const screenOptionContainer = document.querySelector('[data-field-id="screenOption"]');
  const screenContainer = document.querySelector('[data-field-id="screen"]');
  
  if (!trackSystemContainer) return;
  
  const activeTrackOption = trackSystemContainer.querySelector('.option-card.active');
  if (!activeTrackOption) return;
  
  const selectedTrack = activeTrackOption.dataset.value || activeTrackOption.textContent.trim();
  
  [screenOptionContainer, screenContainer].forEach(container => {
    if (!container) return;
    
    const screenOptions = container.querySelectorAll('.option-card');
    if (selectedTrack === '3 Tracks') {
      // Disable "With Screen" option for 3 Tracks
      screenOptions.forEach(option => {
        const optionValue = option.dataset.value || option.textContent.trim();
        if (optionValue === 'With Screen') {
          option.style.opacity = '0.5';
          option.style.pointerEvents = 'none';
          option.classList.add('disabled');
          // If "With Screen" was selected, switch to "Without Screen"
          if (option.classList.contains('active')) {
            option.classList.remove('active');
            const withoutScreenOption = Array.from(screenOptions).find(opt => {
              const val = opt.dataset.value || opt.textContent.trim();
              return val === 'Without Screen';
            });
            if (withoutScreenOption) {
              withoutScreenOption.classList.add('active');
              selectedCustomizationValues[container.dataset.fieldId] = 'Without Screen';
            }
          }
        } else {
          option.style.opacity = '';
          option.style.pointerEvents = '';
          option.classList.remove('disabled');
          
          // AUTO-SELECT "Without Screen" for 3 Tracks if not selected
          if (optionValue === 'Without Screen' && !container.querySelector('.option-card.active')) {
            option.classList.add('active');
            selectedCustomizationValues[container.dataset.fieldId] = 'Without Screen';
          }
        }
      });
      
      // Show message
      let messageEl = container.parentNode.querySelector('.conditional-message');
      if (!messageEl) {
        messageEl = document.createElement('div');
        messageEl.className = 'conditional-message';
        messageEl.style.color = '#d9534f'; // Use alert red
        messageEl.style.fontSize = '12px';
        messageEl.style.marginTop = '8px';
        messageEl.style.width = '100%';
        messageEl.style.textAlign = 'left';
        container.parentNode.appendChild(messageEl);
      }
      messageEl.textContent = 'Screen not available for 3 Tracks';
    } else {
      // Enable all screen options for 2 Tracks
      screenOptions.forEach(option => {
        option.style.opacity = '';
        option.style.pointerEvents = '';
        option.classList.remove('disabled');
      });
      const messageEl = container.parentNode.querySelector('.conditional-message');
      if (messageEl) {
        messageEl.remove();
      }
    }
  });
}

/**
 * Renders a color field
 */
function renderColorField(field, container) {
  const colorWrapper = document.createElement('div');
  colorWrapper.className = 'color-wrapper';
  
  const colorInput = document.createElement('input');
  colorInput.type = 'color';
  colorInput.id = field.id;
  colorInput.name = field.id;
  colorInput.value = field.default || '#000000';
  
  colorInput.addEventListener('change', () => {
    updateKonvaFromField(field.id, colorInput.value, true);
  });

  colorWrapper.appendChild(colorInput);
  container.appendChild(colorWrapper);
}

/**
 * Gets CSS class for field group based on type
 */
function getFieldGroupClass(type) {
  const classMap = {
    'tags': 'type-section',
    'checkbox': 'checkbox-section',
    'number': 'dimensions-container',
    'color': 'color-section'
  };
  return classMap[type] || 'field-section';
}

/**
 * Updates Konva visualization based on field changes
 */
function updateKonvaFromField(fieldId, value, isActive) {
  // Map field IDs to Konva parameters
  const fieldMapping = {
    'glassType': 'glassType',
    'frameColor': 'frameType',
    'frameFinishColor': 'frameType', // Product catalog field name
    'thickness': 'thickness',
    'screen': 'screen',
    'size': 'dimensions',
    'handleType': 'handle',
    'lockType': 'lock',
    'softClose': 'softClose',
    'layout': 'layout',
    'glassThickness': 'thickness',
    'finish': 'finish',
    'hardwareColor': 'hardwareColor',
    'shape': 'shape',
    'edgeFinish': 'edgeWork',
    'mountingMethod': 'mounting',
    'material': 'material',
    'doorType': 'doorType',
    'accessories': 'accessories',
    'safetyGlassType': 'glassType',
    'handrailType': 'handrail',
    'mountingSystem': 'mounting',
    // Multi-panel fields (from product catalog JSON)
    'numberOfPanels': 'numberOfPanels',
    'NumberOfPanels': 'numberOfPanels',
    'panelCount': 'numberOfPanels',
    'PanelCount': 'numberOfPanels',
    'operation': 'operation',
    'Operation': 'operation',
    'configuration': 'configuration',
    'Configuration': 'configuration',
    'doorSwing': 'doorSwing',
    'DoorSwing': 'doorSwing',
    'hingeSide': 'hingeSide',
    'HingeSide': 'hingeSide',
    // Rounded corners for rectangle/square mirrors
    'cornerRadius': 'cornerRadius',
    'radius': 'cornerRadius'
  };

  const konvaParam = fieldMapping[fieldId];
  if (!konvaParam) return;

  // Store selected value
  // For tag fields (single-select), store as single value
  // For other fields that might support multi-select, use array
  const isTagField = document.querySelector(`[data-field-id="${fieldId}"]`) !== null;
  
  if (isTagField) {
    // Single-select: store only the selected value
    if (isActive) {
      selectedCustomizationValues[fieldId] = value;
    } else {
      // If deselecting and it's the current value, clear it
      if (selectedCustomizationValues[fieldId] === value) {
        delete selectedCustomizationValues[fieldId];
      }
    }
  } else {
    // Multi-select: use array (for checkboxes, etc.)
    if (!selectedCustomizationValues[fieldId]) {
      selectedCustomizationValues[fieldId] = [];
    }
    
    if (isActive) {
      if (!selectedCustomizationValues[fieldId].includes(value)) {
        selectedCustomizationValues[fieldId].push(value);
      }
    } else {
      selectedCustomizationValues[fieldId] = selectedCustomizationValues[fieldId].filter(v => v !== value);
    }
  }

  // Update global state (accessing variables from 2d_customization.js)
  // These variables are defined in 2d_customization.js
  if (typeof window !== 'undefined') {
    if (konvaParam === 'glassType') {
      // Update glass type for Konva (synced with presets: Clear, Tinted, Laminated)
      const normalizedValue = value.toLowerCase().replace(/\s+/g, '-');
      if (window.currentGlassType !== undefined) {
        window.currentGlassType = normalizedValue;
      }
      // Also try direct assignment
      try {
        if (typeof currentGlassType !== 'undefined') {
          currentGlassType = normalizedValue;
        }
      } catch(e) {}
    } else if (konvaParam === 'frameType') {
      // Map frame color/material to frame type (synced with presets)
      // Preset values: White, Black, Silver, Bronze, Wood, Aluminum
      const normalizedValue = value.toLowerCase().replace(/\s+/g, '-');
      // Use the preset value directly (no mapping needed - Konva now supports all preset colors)
      const mappedFrame = normalizedValue;
      if (window.currentFrameType !== undefined) {
        window.currentFrameType = mappedFrame;
      }
      try {
        if (typeof currentFrameType !== 'undefined') {
          currentFrameType = mappedFrame;
        }
      } catch(e) {}
    } else if (konvaParam === 'thickness') {
      const thicknessValue = value + 'mm';
      if (window.currentThickness !== undefined) {
        window.currentThickness = thicknessValue;
      }
      try {
        if (typeof currentThickness !== 'undefined') {
          currentThickness = thicknessValue;
        }
      } catch(e) {}
    } else if (konvaParam === 'edgeWork') {
      // Edge finish from presets: Beveled, Polished, Raw
      const edgeValue = value.toLowerCase().replace(/\s+/g, '-');
      if (window.currentEdgeWork !== undefined) {
        window.currentEdgeWork = edgeValue;
      }
      try {
        if (typeof currentEdgeWork !== 'undefined') {
          currentEdgeWork = edgeValue;
        }
      } catch(e) {}
    } else if (konvaParam === 'shape') {
      // Shape from presets: Round, Rectangle, Oval
      const shapeValue = value.toLowerCase().replace(/\s+/g, '-');
      if (window.currentShape !== undefined) {
        window.currentShape = shapeValue;
      }
      try {
        if (typeof currentShape !== 'undefined') {
          currentShape = shapeValue;
        }
      } catch(e) {}
      
      // Auto-lock dimensions for shapes that require equal dimensions
      setTimeout(() => {
        if (typeof window !== 'undefined') {
          // Shapes that require equal dimensions (width = height)
          const equalDimensionShapes = [
            'round', 'circle', 'star', 'pentagon', 'hexagon', 'octagon', 'square'
          ];
          
          const normalizedShapeValue = shapeValue.toLowerCase().replace(/\s+/g, '-');
          const requiresEqualDimensions = equalDimensionShapes.includes(normalizedShapeValue);
          
          // Check if shape requires equal dimensions using the helper function or direct check
          if (requiresEqualDimensions || 
              (typeof window.isRoundShape === 'function' && window.isRoundShape(shapeValue))) {
            // Lock dimensions for shapes that require equal dimensions
            if (typeof window.lockDimensionsForRoundShape === 'function') {
              window.lockDimensionsForRoundShape();
            }
          } else {
            // Unlock if switching away from shapes that require equal dimensions
            if (typeof window.unlockDimensionsIfNotRound === 'function') {
              window.unlockDimensionsIfNotRound();
            }
          }
        }
        
        // Check corner radius visibility when shape changes
        setTimeout(() => {
          const cornerRadiusContainers = document.querySelectorAll('[data-conditional-field="true"][data-depends-on="shape"]');
          cornerRadiusContainers.forEach(container => {
            checkCornerRadiusVisibility(container);
          });
        }, 150);
      }, 100);
    } else if (konvaParam === 'finish') {
      // Finish from presets: Clear, Frosted, Patterned (maps to glass type)
      const finishValue = value.toLowerCase().replace(/\s+/g, '-');
      // Map finish to glass type for visualization
      const finishToGlassMap = {
        'clear': 'clear',
        'frosted': 'frosted',
        'patterned': 'patterned'
      };
      const mappedGlassType = finishToGlassMap[finishValue] || 'clear';
      if (window.currentGlassType !== undefined) {
        window.currentGlassType = mappedGlassType;
      }
      try {
        if (typeof currentGlassType !== 'undefined') {
          currentGlassType = mappedGlassType;
        }
      } catch(e) {}
    }
    else if (konvaParam === 'cornerRadius') {
      // Corner radius for rectangle/square (in inches)
      const radiusIn = parseFloat(value) || 0;
      if (window.currentCornerRadius !== undefined) {
        window.currentCornerRadius = radiusIn;
      }
      try {
        if (typeof currentCornerRadius !== 'undefined') {
          currentCornerRadius = radiusIn;
        }
      } catch (e) {}
    } else if (konvaParam === 'numberOfPanels' || konvaParam === 'operation' || konvaParam === 'configuration') {
      // Multi-panel fields - store in selectedCustomizationValues for renderWindow to use
      // These will be automatically picked up by renderWindow when it checks shouldUseMultiPanelRendering
      // No need to update individual state variables, just ensure the value is stored
    }
  }

  // Re-render Konva - try multiple ways to access the render function
  // The renderCustomState function is defined in 2d_customization.js
  // It will automatically check for multi-panel configuration via shouldUseMultiPanelRendering
  setTimeout(() => {
    if (typeof renderCustomState === 'function') {
      renderCustomState();
    } else if (typeof window.renderCustomState === 'function') {
      window.renderCustomState();
    } else if (window.renderCustomState) {
      window.renderCustomState();
    }
  }, 50);
}

/**
 * Renders standard sizes dynamically
 */
function renderStandardSizes(standardSeries, container) {
  if (!standardSeries || !Array.isArray(standardSeries) || standardSeries.length === 0) {
    console.warn('No standard series found for this product');
    // Show message if no standard sizes
    container.innerHTML = '<p style="color: #999; text-align: center; padding: 20px;">No standard sizes available for this product.</p>';
    return;
  }

  container.innerHTML = '';

  standardSeries.forEach((series, seriesIndex) => {
    const seriesSection = document.createElement('div');
    seriesSection.className = 'standard-size-section';
    if (seriesIndex > 0) {
      seriesSection.style.marginTop = '30px';
    }
    
    const seriesTitle = document.createElement('label');
    seriesTitle.className = 'section-label';
    seriesTitle.textContent = series.name || 'Standard Sizes';
    seriesSection.appendChild(seriesTitle);

    const sizesGrid = document.createElement('div');
    sizesGrid.className = 'grid-3-cols';

    series.measurements.forEach((measurement, index) => {
      const sizeCard = document.createElement('div');
      sizeCard.className = 'option-card';
      if (seriesIndex === 0 && index === 0) sizeCard.classList.add('active');
      
      sizeCard.dataset.height = measurement.height;
      sizeCard.dataset.width = measurement.width;
      sizeCard.dataset.price = measurement.price;
      sizeCard.dataset.seriesId = series.id;
      
      // Display size (keep in cm as admin entered)
      const displayHeight = measurement.height;
      const displayWidth = measurement.width;
      sizeCard.textContent = `${displayWidth}cm × ${displayHeight}cm`;
      
      // Add price display
      const priceSpan = document.createElement('span');
      priceSpan.className = 'size-price-display';
      priceSpan.textContent = ` - ₱${parseFloat(measurement.price).toFixed(2)}`;
      sizeCard.appendChild(priceSpan);

      sizeCard.addEventListener('click', () => {
        // Remove active from all cards in all series
        container.querySelectorAll('.option-card').forEach(card => {
          card.classList.remove('active');
        });
        sizeCard.classList.add('active');
        
        // Update Konva with selected size (convert cm to inches for Konva)
        const widthIn = measurement.width / 2.54; // cm to inches
        const heightIn = measurement.height / 2.54; // cm to inches
        
        // Try multiple ways to call renderStandardState
        if (typeof renderStandardState === 'function') {
          renderStandardState(widthIn, heightIn);
        } else if (typeof window.renderStandardState === 'function') {
          window.renderStandardState(widthIn, heightIn);
        } else {
          // Fallback: try to call renderWindow directly
          if (typeof renderWindow === 'function') {
            renderWindow(widthIn, heightIn, 'in', 'rectangle', 'tempered', '5mm', 'flat-polish', 'vinyl');
          }
        }
        
        // Update price
        updatePriceForStandardSize(measurement.price);
      });

      sizesGrid.appendChild(sizeCard);
    });

    seriesSection.appendChild(sizesGrid);
    container.appendChild(seriesSection);
  });
}

/**
 * Updates price display for standard size selection
 */
function updatePriceForStandardSize(price) {
  const totalPriceEl = document.getElementById('total-price');
  if (totalPriceEl) {
    totalPriceEl.textContent = `₱${parseFloat(price).toFixed(2)}`;
  }
}

/**
 * Updates step navigation based on number of steps
 * @param {number} totalSteps - Total number of steps
 * @param {Object} stepNames - Step names object { "1": "Step Name", "2": "Step Name" }
 */
function updateStepNavigation(totalSteps, stepNames = null) {
  // Store total steps globally for navigation
  if (typeof window !== 'undefined') {
    window.totalCustomizationSteps = totalSteps;
    
    // Store step names if provided
    if (stepNames) {
      window.customizationStepNames = stepNames;
    }
    
    // Handle case where there are no steps (all options empty)
    if (totalSteps === 0) {
      // Hide back button group
      const backGroup = document.getElementById('back-group');
      if (backGroup) {
        backGroup.classList.add('hidden-step');
      }
      
      // Update next button to go directly to summary
      const nextBtn = document.getElementById('next-btn');
      const nextNote = document.getElementById('next-note');
      if (nextBtn) {
        nextBtn.innerHTML = `Finalize Design <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
      }
      if (nextNote) {
        nextNote.textContent = '';
      }
      
      // Set up direct-to-summary navigation
      setupDirectToSummaryNavigation();
      return;
    }
    
    // Update breadcrumbs dynamically
    updateDynamicBreadcrumbs(1, totalSteps, stepNames);
    
    // Update next/back button visibility
    const backGroup = document.getElementById('back-group');
    if (backGroup && totalSteps > 1) {
      backGroup.classList.remove('hidden-step');
    } else if (backGroup) {
      backGroup.classList.add('hidden-step');
    }
    
    // Update next note text with step name if available
    const nextNote = document.getElementById('next-note');
    if (nextNote && totalSteps > 1) {
      const stepName = stepNames && stepNames['2'] ? stepNames['2'] : 'Step 2';
      nextNote.textContent = stepName;
    }
    
    // Override navigation handlers if needed
    setupDynamicStepNavigation(totalSteps, stepNames);
  }
}

/**
 * Sets up navigation to go directly to summary when there are no customization steps
 * (only dimensions need to be entered)
 */
function setupDirectToSummaryNavigation() {
  const nextBtn = document.getElementById('next-btn');
  const backBtn = document.getElementById('back-btn');
  
  if (!nextBtn) return;
  
  // Remove existing listeners by cloning
  const newNextBtn = nextBtn.cloneNode(true);
  nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
  
  // Get fresh reference
  const freshNextBtn = document.getElementById('next-btn');
  
  // Update button text
  freshNextBtn.innerHTML = `Finalize Design <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
  
  // Direct-to-summary handler
  freshNextBtn.addEventListener('click', () => {
    console.log('[Nav] Direct-to-summary clicked');
    // VALIDATION: Check dimensions even if no customization steps
    const missingFields = validateStep(1); // Check Step 1 (dimensions)
    if (missingFields.length > 0) {
      showValidationWarning(missingFields);
      return;
    }

    console.log('[Nav] Calling showOrderSummary...');
    if (typeof window.showOrderSummary === 'function') {
      window.showOrderSummary();
    } else if (typeof showOrderSummary === 'function') {
      showOrderSummary();
    } else {
      console.error('[Nav] showOrderSummary function not found!');
    }
  });
  
  // Hide back button
  if (backBtn) {
    const backGroup = document.getElementById('back-group');
    if (backGroup) {
      backGroup.classList.add('hidden-step');
    }
  }
}

/**
 * Validates all fields in a specific step
 * @param {number} stepNum - Step number to validate
 * @returns {Array} List of missing field labels
 */
function validateStep(stepNum) {
  const stepEl = document.getElementById(`step-${stepNum}`);
  if (!stepEl) return [];
  
  // Find all field containers in this step
  const containers = stepEl.querySelectorAll('[data-field-id]');
  let missingFields = [];
  const addedToMissing = new Set();
  
  containers.forEach(container => {
    // Skip if it's an option-card itself
    if (container.classList.contains('option-card')) return;
    
    // Skip if hidden
    if (container.style.display === 'none' || container.closest('.hidden-step')) return;
    
    const fieldId = container.dataset.fieldId;
    if (!fieldId) return;

    // Special case: check if this is a container that actually holds options
    const options = container.querySelectorAll('.option-card');
    if (options.length === 0) return;

    // Check for active selection
    const activeCard = container.querySelector('.option-card.active');
    if (!activeCard) {
      let label = fieldId;
      if (typeof window.getFieldDisplayName === 'function') {
        label = window.getFieldDisplayName(fieldId).replace(':', '');
      }
        
      if (!addedToMissing.has(label)) {
        missingFields.push(label);
        addedToMissing.add(label);
      }
    }
  });

  // Also check dimensions if they are visible in this step
  const dimContainer = document.querySelector('.dimensions-container');
  if (dimContainer && !dimContainer.classList.contains('hidden-step')) {
    const heightInput = document.getElementById('input-height');
    const widthInput = document.getElementById('input-width');
    
    if (!heightInput?.value || !widthInput?.value || parseFloat(heightInput.value) <= 0 || parseFloat(widthInput.value) <= 0) {
      if (!addedToMissing.has('Dimensions')) {
        missingFields.push('Dimensions (Height & Width)');
        addedToMissing.add('Dimensions');
      }
    }
  }
  
  return missingFields;
}

/**
 * Shows validation warning message
 * @param {Array} missingFields - List of missing field labels
 */
function showValidationWarning(missingFields) {
  const warningEl = document.getElementById('validation-warning');
  if (warningEl) {
    warningEl.innerHTML = '<div style="display: flex; align-items: center; gap: 10px;">' +
                          '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>' +
                          '<span>Please complete the following specifications: <strong>' + missingFields.join(', ') + '</strong></span>' +
                          '</div>';
    warningEl.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } else {
    alert('Please complete: ' + missingFields.join(', '));
  }
}

/**
 * Sets up dynamic step navigation that works with any number of steps
 * @param {number} totalSteps - Total number of steps
 * @param {Object} stepNames - Step names object { "1": "Step Name", "2": "Step Name" }
 */
function setupDynamicStepNavigation(totalSteps, stepNames = null) {
  const nextBtn = document.getElementById('next-btn');
  const backBtn = document.getElementById('back-btn');
  
  if (!nextBtn || !backBtn) return;
  
  // Remove existing listeners by cloning
  const newNextBtn = nextBtn.cloneNode(true);
  const newBackBtn = backBtn.cloneNode(true);
  nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
  backBtn.parentNode.replaceChild(newBackBtn, backBtn);
  
  // Get fresh references
  const freshNextBtn = document.getElementById('next-btn');
  const freshBackBtn = document.getElementById('back-btn');
  const backNote = document.getElementById('back-note');
  const nextNote = document.getElementById('next-note');
  
  // Dynamic next button handler
  freshNextBtn.addEventListener('click', () => {
    const currentStepNum = window.currentStep || 1;
    
    // VALIDATION: Check if all fields in the current step have a selection
    const missingFields = validateStep(currentStepNum);
    if (missingFields.length > 0) {
      showValidationWarning(missingFields);
      return;
    } else {
      const warningEl = document.getElementById('validation-warning');
      if (warningEl) warningEl.style.display = 'none';
    }
    
    const totalSteps = window.totalCustomizationSteps || 1;
    console.log(`[Nav] Next clicked. Current step: ${currentStepNum}/${totalSteps}`);

    if (currentStepNum < totalSteps) {
      // Move to next step (STRICT FLOW: 1 -> 2 -> 3 -> 4)
      console.log(`[Nav] Moving to next step: ${currentStepNum + 1}`);
      goToDynamicStep(currentStepNum + 1);
    } else {
      // Final step reached (Step 4) - now show summary
      console.log(`[Nav] Step ${totalSteps} completed. Transitioning to Review Order...`);
      
      // Validation: Final check before summary
      const missingFields = validateStep(currentStepNum);
      if (missingFields.length > 0) {
        showValidationWarning(missingFields);
        return;
      }
      
      // Ensure the button shows loading state
      const originalHtml = freshNextBtn.innerHTML;
      freshNextBtn.disabled = true;
      freshNextBtn.innerHTML = 'Generating Review... <span class="spinner-border spinner-border-sm"></span>';
      
      setTimeout(() => {
        if (typeof window.showOrderSummary === 'function') {
          window.showOrderSummary();
        } else if (typeof showOrderSummary === 'function') {
          showOrderSummary();
        } else {
          console.error('[Nav] showOrderSummary function not found anywhere!');
          alert('Could not show order summary. Please contact support.');
        }
        
        // Restore button state
        freshNextBtn.disabled = false;
        freshNextBtn.innerHTML = originalHtml;
      }, 300);
    }
  });
  
  // Dynamic back button handler
  freshBackBtn.addEventListener('click', () => {
    const currentStep = window.currentStep || 1;
    
    if (currentStep > 1) {
      goToDynamicStep(currentStep - 1);
    }
  });
  
  // Initialize first step (this will show dimensions and set up breadcrumbs)
  goToDynamicStep(1);
}

/**
 * Navigates to a specific step dynamically
 */
function goToDynamicStep(targetStep) {
  const totalSteps = window.totalCustomizationSteps || 1;
  
  // STRICT NAVIGATION: Check if trying to skip ahead
  if (targetStep > (window.currentStep || 1) + 1 && targetStep <= totalSteps) {
    console.warn(`[Nav] Blocking jump from ${window.currentStep} to ${targetStep}`);
    return;
  }

  // VALIDATION: Check previous step before moving forward
  if (targetStep > (window.currentStep || 1)) {
    const missingFields = validateStep(window.currentStep || 1);
    if (missingFields.length > 0) {
      showValidationWarning(missingFields);
      return;
    }
  }
  
  // Hide all step containers
  for (let i = 1; i <= totalSteps; i++) {
    const stepEl = document.getElementById(`step-${i}`);
    if (stepEl) {
      stepEl.classList.add('hidden-step');
    }
  }
  
  // Show target step
  const targetStepEl = document.getElementById(`step-${targetStep}`);
  if (targetStepEl) {
    targetStepEl.classList.remove('hidden-step');
  }
  
  // Hide/Show dimensions container (Height/Width)
  const dimensionsContainer = document.querySelector('.dimensions-container');
  if (dimensionsContainer) {
    const product = getSelectedProduct();
    const isWindowsSliding = product && product.category === 'Windows' && product.subcategory === 'Sliding';
    
    // For Windows Sliding, show on step 2. For others, show on step 1.
    // MODIFIED: Also check if targetStep matches where dimensions should be
    const shouldShowDimensions = isWindowsSliding ? (targetStep === 2) : (targetStep === 1);
    
    if (shouldShowDimensions) {
      dimensionsContainer.classList.remove('hidden-step');
      dimensionsContainer.style.display = '';
    } else {
      // Don't hide it if we are on the summary page or if it's already shown
      // Actually, we want it hidden on other steps to focus on the current step's options
      dimensionsContainer.classList.add('hidden-step');
      dimensionsContainer.style.display = 'none';
    }
  }
  
  // Update current step
  if (typeof window !== 'undefined') {
    window.currentStep = targetStep;
  }
  
  // Update UI elements
  const backGroup = document.getElementById('back-group');
  const nextBtn = document.getElementById('next-btn');
  const backNote = document.getElementById('back-note');
  const nextNote = document.getElementById('next-note');
  
  // Update back button visibility
  if (backGroup) {
    if (targetStep > 1) {
      backGroup.classList.remove('hidden-step');
    } else {
      backGroup.classList.add('hidden-step');
    }
  }
  
  // Get step names if available
  const stepNames = window.customizationStepNames || null;
  
  // Update button text and notes with step names if available
  if (targetStep < totalSteps) {
    if (nextBtn) {
      nextBtn.innerHTML = `Next <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>`;
    }
    if (nextNote) {
      const nextStepName = stepNames && stepNames[String(targetStep + 1)] 
        ? stepNames[String(targetStep + 1)] 
        : `Step ${targetStep + 1}`;
      nextNote.textContent = nextStepName;
    }
  } else {
    if (nextBtn) {
      nextBtn.innerHTML = `Finalize Design <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
    }
    if (nextNote) {
      nextNote.textContent = '';
    }
  }
  
  if (backNote && targetStep > 1) {
    const prevStepName = stepNames && stepNames[String(targetStep - 1)]
      ? stepNames[String(targetStep - 1)]
      : `Step ${targetStep - 1}`;
    backNote.textContent = prevStepName;
  }
  
  // Update breadcrumbs dynamically
  updateDynamicBreadcrumbs(targetStep, totalSteps, stepNames);
}

/**
 * Updates breadcrumb navigation dynamically based on current step and step names
 * @param {number} currentStep - Current step number
 * @param {number} totalSteps - Total number of steps
 * @param {Object} stepNames - Step names object { "1": "Step Name", "2": "Step Name" }
 */
function updateDynamicBreadcrumbs(currentStep, totalSteps, stepNames = null) {
  const crumbMain = document.getElementById('crumb-main');
  const breadcrumbsContainer = document.getElementById('breadcrumbs-container');
  
  if (!crumbMain || !breadcrumbsContainer) return;
  
  // Remove all dynamic breadcrumbs (keep only "Products" and main crumb)
  const dynamicCrumbs = breadcrumbsContainer.querySelectorAll('[id^="crumb-step"], [id^="chevron-crumb-step"]');
  dynamicCrumbs.forEach(crumb => crumb.remove());
  
  // Reset main crumb
  if (currentStep === 1) {
    // Step 1 - show main crumb as active
    crumbMain.textContent = stepNames && stepNames['1'] ? stepNames['1'] : 'Glass Shape';
    crumbMain.classList.add('active');
  } else {
    // Step 2+ - show main crumb as inactive, add step breadcrumbs
    crumbMain.textContent = stepNames && stepNames['1'] ? stepNames['1'] : 'Glass Shape';
    crumbMain.classList.remove('active');
    
    // Add breadcrumbs for each step up to current step
    for (let step = 2; step <= currentStep; step++) {
      const stepName = stepNames && stepNames[String(step)] 
        ? stepNames[String(step)] 
        : `Step ${step}`;
      
      // Add chevron
      const chevron = document.createElement('span');
      chevron.className = 'chevron-right';
      chevron.id = `chevron-crumb-step${step}`;
      breadcrumbsContainer.appendChild(chevron);
      
      // Add breadcrumb
      const crumb = document.createElement('span');
      crumb.className = step === currentStep ? 'active' : '';
      crumb.id = `crumb-step${step}`;
      crumb.textContent = stepName;
      breadcrumbsContainer.appendChild(crumb);
    }
  }
}

/**
 * Preloads customization selections from window.preloadedCustomization
 */
function preloadSelections() {
  if (!window.preloadedCustomization) return;
  
  console.log('[Preload] Starting pre-selection process...');
  const customization = window.preloadedCustomization;
  
  // Mapping of database columns to field IDs
  const colToFieldMap = {
    'GlassShape': 'shape',
    'GlassType': 'glassType',
    'GlassThickness': 'glassThickness',
    'EdgeWork': 'edgeWork',
    'FrameType': 'frameColor', // Standard field ID for frame color
    'Engraving': 'engraving'
  };

  // Try to parse dynamic customization JSON if it exists
  let dynamicCustomization = {};
  if (customization.Customization) {
    try {
      dynamicCustomization = typeof customization.Customization === 'string' 
        ? JSON.parse(customization.Customization) 
        : customization.Customization;
      console.log('[Preload] Found dynamic customization JSON:', dynamicCustomization);
    } catch(e) {
      console.error('[Preload] Error parsing dynamic customization JSON:', e);
    }
  }

  // Combine standard columns and dynamic JSON
  const allSelections = { ...customization, ...dynamicCustomization };
  
  // Iterate through all possible field containers in DOM
  const fieldContainers = document.querySelectorAll('[data-field-id]');
  fieldContainers.forEach(container => {
    const fieldId = container.dataset.fieldId;
    let targetValue = allSelections[fieldId];
    
    // If not found by fieldId, try the column mapping
    if (!targetValue) {
      for (const [col, fid] of Object.entries(colToFieldMap)) {
        if (fid === fieldId) {
          targetValue = customization[col];
          break;
        }
      }
    }

    if (targetValue) {
      console.log(`[Preload] Attempting to select value "${targetValue}" for field "${fieldId}"`);
      
      const options = container.querySelectorAll('.option-card');
      options.forEach(option => {
        const val = option.dataset.value || option.textContent.trim();
        // Use loose comparison or normalization if needed
        if (val === targetValue || val.toLowerCase() === String(targetValue).toLowerCase()) {
          option.click(); // Trigger the click handler to update UI and Konva
          console.log(`[Preload] Selected "${val}" for "${fieldId}"`);
        }
      });
    }
  });

  // Preload dimensions
  if (window.preloadedDimensions) {
    const inputWidth = document.getElementById('input-width');
    const inputHeight = document.getElementById('input-height');
    const btnUnitWidth = document.getElementById('btn-unit-width');
    const btnUnitHeight = document.getElementById('btn-unit-height');

    if (inputWidth) inputWidth.value = window.preloadedDimensions.width.value;
    if (inputHeight) inputHeight.value = window.preloadedDimensions.height.value;
    
    // Units might need triggering change events or manual updates to labels
    if (btnUnitWidth) btnUnitWidth.dataset.currentUnit = window.preloadedDimensions.width.unit;
    if (btnUnitHeight) btnUnitHeight.dataset.currentUnit = window.preloadedDimensions.height.unit;
    
    // Trigger updateDimensions if available
    if (typeof window.updateDimensions === 'function') {
      window.updateDimensions('width', window.preloadedDimensions.width.value, window.preloadedDimensions.width.unit);
      window.updateDimensions('height', window.preloadedDimensions.height.value, window.preloadedDimensions.height.unit);
    }
  }
}

/**
 * Syncs JavaScript state variables with the active DOM selections
 * This ensures Konva renders the correct shape when the page loads
 * Must be called AFTER fields are rendered and enforceSingleSelection() runs
 * 
 * IMPORTANT: This function also ensures admin-configured visual styles are loaded
 * and applied to the 2D preview, syncing the admin's color/style settings to customer view.
 */
function syncStateFromActiveSelections() {
  console.log('[Sync] ========== SYNCING STATE FROM DOM ==========');
  
  // If we have preloaded data, run preloading first
  if (window.preloadedCustomization) {
    preloadSelections();
    // Clear it so we don't re-run it multiple times
    delete window.preloadedCustomization;
  }
  
  // First, ensure visual configs are loaded (for frame colors like Gold, Silver, etc.)
  // This is CRITICAL for syncing admin's 2D preview settings to customer side
  if (typeof window !== 'undefined' && window.selectedProduct && window.selectedProduct.tagVisualConfigs) {
    const configCount = Object.keys(window.selectedProduct.tagVisualConfigs).length;
    if (configCount > 0) {
      if (typeof window.loadDynamicVisualConfigs === 'function') {
        console.log(`[Sync] Loading ${configCount} visual config field(s) from admin...`);
        window.loadDynamicVisualConfigs(window.selectedProduct.tagVisualConfigs);
      } else {
        console.warn('[Sync] loadDynamicVisualConfigs not available');
      }
    }
  }
  
  // Also check for pending configs that might have been set earlier
  if (typeof window !== 'undefined' && window.pendingVisualConfigs) {
    if (typeof window.loadDynamicVisualConfigs === 'function') {
      console.log('[Sync] Loading pending visual configs...');
      window.loadDynamicVisualConfigs(window.pendingVisualConfigs);
      delete window.pendingVisualConfigs;
    }
  }
  
  // Sync shape
  const shapeContainer = document.querySelector('[data-field-id="shape"]');
  if (shapeContainer) {
    const activeShapeCard = shapeContainer.querySelector('.option-card.active');
    if (activeShapeCard) {
      const shapeValue = (activeShapeCard.dataset.value || activeShapeCard.textContent.trim()).toLowerCase().replace(/\s+/g, '-');
      console.log('[Sync] Found active shape:', shapeValue);
      
      // Update global state
      if (typeof window !== 'undefined') {
        window.currentShape = shapeValue;
      }
      try {
        if (typeof currentShape !== 'undefined') {
          currentShape = shapeValue;
        }
      } catch(e) {}
      
      // Update selectedCustomizationValues
      selectedCustomizationValues['shape'] = shapeValue;
      
      // Auto-lock dimensions for round shapes
      const equalDimensionShapes = ['round', 'circle', 'star', 'pentagon', 'hexagon', 'octagon', 'square'];
      if (equalDimensionShapes.includes(shapeValue)) {
        if (typeof window.lockDimensionsForRoundShape === 'function') {
          window.lockDimensionsForRoundShape();
        }
      }
    }
  }
  
  // Also sync from legacy shape cards (data-shape attribute)
  const legacyShapeCard = document.querySelector('.option-card[data-shape].active');
  if (legacyShapeCard && !shapeContainer) {
    const shapeValue = legacyShapeCard.dataset.shape.toLowerCase().replace(/\s+/g, '-');
    console.log('[Sync] Found active legacy shape:', shapeValue);
    
    if (typeof window !== 'undefined') {
      window.currentShape = shapeValue;
    }
    try {
      if (typeof currentShape !== 'undefined') {
        currentShape = shapeValue;
      }
    } catch(e) {}
    
    selectedCustomizationValues['shape'] = shapeValue;
  }
  
  // Sync glass type
  const glassTypeContainer = document.querySelector('[data-field-id="glassType"]');
  if (glassTypeContainer) {
    const activeCard = glassTypeContainer.querySelector('.option-card.active');
    if (activeCard) {
      const value = (activeCard.dataset.value || activeCard.textContent.trim()).toLowerCase().replace(/\s+/g, '-');
      console.log('[Sync] Found active glass type:', value);
      
      if (typeof window !== 'undefined') {
        window.currentGlassType = value;
      }
      try {
        if (typeof currentGlassType !== 'undefined') {
          currentGlassType = value;
        }
      } catch(e) {}
      
      selectedCustomizationValues['glassType'] = value;
    }
  }
  
  // Sync frame type/color - search for multiple possible field IDs
  const frameFieldIds = ['frameColor', 'frameType', 'frameFinishColor', 'frame'];
  let frameContainer = null;
  for (const fid of frameFieldIds) {
    frameContainer = document.querySelector(`[data-field-id="${fid}"]`);
    if (frameContainer) break;
  }
  
  if (frameContainer) {
    const activeCard = frameContainer.querySelector('.option-card.active');
    if (activeCard) {
      // Get both the raw value and normalized value
      const rawValue = activeCard.dataset.value || activeCard.textContent.trim();
      const normalizedValue = rawValue.toLowerCase().replace(/\s+/g, '-');
      console.log('[Sync] Found active frame:', rawValue, '-> normalized:', normalizedValue);
      
      // Update global state with normalized value
      if (typeof window !== 'undefined') {
        window.currentFrameType = normalizedValue;
        
        // Also check if this frame color has a visual config and log it
        if (window.frameStyles && window.frameStyles[normalizedValue]) {
          console.log('[Sync] Frame style exists for', normalizedValue, ':', window.frameStyles[normalizedValue]);
        } else {
          console.log('[Sync] No frame style found for', normalizedValue, '- will use fallback');
        }
      }
      try {
        if (typeof currentFrameType !== 'undefined') {
          currentFrameType = normalizedValue;
        }
      } catch(e) {}
      
      selectedCustomizationValues[frameContainer.dataset.fieldId || 'frameType'] = rawValue;
    }
  }
  
  // Sync thickness
  const thicknessContainer = document.querySelector('[data-field-id="thickness"], [data-field-id="glassThickness"]');
  if (thicknessContainer) {
    const activeCard = thicknessContainer.querySelector('.option-card.active');
    if (activeCard) {
      const value = activeCard.dataset.value || activeCard.textContent.trim();
      const thicknessValue = value.includes('mm') ? value : value + 'mm';
      console.log('[Sync] Found active thickness:', thicknessValue);
      
      if (typeof window !== 'undefined') {
        window.currentThickness = thicknessValue;
      }
      try {
        if (typeof currentThickness !== 'undefined') {
          currentThickness = thicknessValue;
        }
      } catch(e) {}
      
      selectedCustomizationValues[thicknessContainer.dataset.fieldId || 'thickness'] = value;
    }
  }
  
  // Sync edge work/finish
  const edgeContainer = document.querySelector('[data-field-id="edgeFinish"], [data-field-id="edgeWork"]');
  if (edgeContainer) {
    const activeCard = edgeContainer.querySelector('.option-card.active');
    if (activeCard) {
      const value = (activeCard.dataset.value || activeCard.textContent.trim()).toLowerCase().replace(/\s+/g, '-');
      console.log('[Sync] Found active edge:', value);
      
      if (typeof window !== 'undefined') {
        window.currentEdgeWork = value;
      }
      try {
        if (typeof currentEdgeWork !== 'undefined') {
          currentEdgeWork = value;
        }
      } catch(e) {}
      
      selectedCustomizationValues[edgeContainer.dataset.fieldId || 'edgeWork'] = value;
    }
  }
  
  // Update window.selectedCustomizationValues
  if (typeof window !== 'undefined') {
    window.selectedCustomizationValues = selectedCustomizationValues;
  }
  
  console.log('[Sync] State sync complete. Current values:', {
    shape: window.currentShape,
    glassType: window.currentGlassType,
    frameType: window.currentFrameType,
    thickness: window.currentThickness,
    edgeWork: window.currentEdgeWork
  });
  
  // Re-render Konva with synced state
  setTimeout(() => {
    if (typeof renderCustomState === 'function') {
      renderCustomState();
    } else if (typeof window.renderCustomState === 'function') {
      window.renderCustomState();
    }
    
    // CRITICAL: Update real-time price display after syncing state
    if (typeof window.updateRealTimePriceDisplay === 'function') {
      window.updateRealTimePriceDisplay();
    }
  }, 50);
}

// Export functions for use in other scripts
window.renderDynamicCustomizationFields = renderDynamicCustomizationFields;
window.renderStandardSizes = renderStandardSizes;
window.updateKonvaFromField = updateKonvaFromField;
window.updateStepNavigation = updateStepNavigation;
window.goToDynamicStep = goToDynamicStep;
window.enforceSingleSelection = enforceSingleSelection;
window.updateDynamicBreadcrumbs = updateDynamicBreadcrumbs;
window.setupDirectToSummaryNavigation = setupDirectToSummaryNavigation;
window.syncStateFromActiveSelections = syncStateFromActiveSelections;

// MutationObserver disabled to avoid interfering with user clicks
// The click handler itself ensures only one option is active
