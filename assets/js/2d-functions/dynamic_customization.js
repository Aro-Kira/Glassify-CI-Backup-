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

  // First pass: identify all unique steps
  fields.forEach((field) => {
    // Check stepNumber first (admin config), then step (legacy), default to 1
    const stepNum = field.stepNumber || field.step || 1;
    stepNumbers.add(stepNum);
    console.log(`Field "${field.label || field.id}" assigned to step ${stepNum} (stepNumber: ${field.stepNumber}, step: ${field.step})`);
  });

  // Create step containers
  const sortedSteps = Array.from(stepNumbers).sort((a, b) => a - b);
  console.log('Creating step containers for steps:', sortedSteps);
  sortedSteps.forEach((stepNum) => {
    const stepDiv = document.createElement('div');
    stepDiv.id = `step-${stepNum}`;
    stepDiv.className = stepNum === 1 ? '' : 'hidden-step';
    stepDiv.dataset.stepNumber = stepNum;
    container.appendChild(stepDiv);
    stepContainers[stepNum] = stepDiv;
    console.log(`Created step container: step-${stepNum}`);
  });

  // Second pass: render fields into their assigned steps
  fields.forEach((field) => {
    // Check stepNumber first (admin config), then step (legacy), default to 1
    const stepNum = field.stepNumber || field.step || 1;
    const stepContainer = stepContainers[stepNum];
    
    if (!stepContainer) {
      console.warn(`Step container for step ${stepNum} not found for field "${field.label || field.id}". Available steps:`, Object.keys(stepContainers));
      if (stepContainers[1]) {
        stepContainers[1].appendChild(createFieldElement(field, tagPrices, tagImages));
      } else {
        console.error('Step 1 container also not found!');
      }
      return;
    }

    const fieldGroup = createFieldElement(field, tagPrices, tagImages);
    stepContainer.appendChild(fieldGroup);
    console.log(`Rendered field "${field.label || field.id}" into step ${stepNum} container`);
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
      
      // Initialize conditional logic for Mirrors fields
      const frameTypeContainer = document.querySelector('[data-field-id="frameType"]');
      if (frameTypeContainer) {
        const activeFrameType = frameTypeContainer.querySelector('.option-card.active');
        if (activeFrameType) {
          const frameTypeValue = activeFrameType.dataset.value || activeFrameType.textContent.trim();
          handleMirrorsConditionals('frameType', frameTypeValue);
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
    
    // If more than one is active, keep only the first one
    if (activeCards.length > 1) {
      // Remove active from all except the first
      for (let i = 1; i < activeCards.length; i++) {
        activeCards[i].classList.remove('active');
      }
    }
    
    // If none are active, make the first one active
    if (activeCards.length === 0) {
      const firstCard = container.querySelector('.option-card');
      if (firstCard) {
        firstCard.classList.add('active');
      }
    }
  });
  
  // Also check grid containers (for fields that use grid-3-cols directly)
  gridContainers.forEach(grid => {
    const activeCards = grid.querySelectorAll('.option-card.active');
    
    // If more than one is active, keep only the first one
    if (activeCards.length > 1) {
      for (let i = 1; i < activeCards.length; i++) {
        activeCards[i].classList.remove('active');
      }
    }
    
    // If none are active, make the first one active
    if (activeCards.length === 0) {
      const firstCard = grid.querySelector('.option-card');
      if (firstCard) {
        firstCard.classList.add('active');
      }
    }
  });
  
  // Also check field sections (type-section, thickness-section, etc.)
  const fieldSections = document.querySelectorAll('.type-section, .thickness-section, .edge-section, .frame-section, .field-section');
  fieldSections.forEach(section => {
    const activeCards = section.querySelectorAll('.option-card.active');
    
    if (activeCards.length > 1) {
      for (let i = 1; i < activeCards.length; i++) {
        activeCards[i].classList.remove('active');
      }
    }
    
    if (activeCards.length === 0) {
      const firstCard = section.querySelector('.option-card');
      if (firstCard) {
        firstCard.classList.add('active');
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
  fieldGroup.className = getFieldGroupClass(field.type, field.id);
  
  // Handle conditional fields (for mirrors: Frame Color/Edge Finish)
  if (field.conditional && field.dependsOn) {
    fieldGroup.dataset.conditionalField = 'true';
    fieldGroup.dataset.dependsOn = field.dependsOn;
    fieldGroup.dataset.showWhen = field.showWhen || '';
    // Initially hide conditional fields - they'll be shown by handleMirrorsConditionals
    if (field.id === 'frameColor' || field.id === 'edgeFinish') {
      fieldGroup.style.display = 'none';
    }
  }
  
  const label = document.createElement('label');
  label.className = 'section-label';
  label.textContent = field.label;
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
  
  // Create grid wrapper for tags
  const tagsGrid = document.createElement('div');
  tagsGrid.className = 'grid-3-cols';
  
  options.forEach((option, index) => {
    const tag = document.createElement('div');
    tag.className = 'option-card';
    // CRITICAL: Only the first option should be active
    if (index === 0) {
      tag.classList.add('active');
    }
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

    tag.addEventListener('click', function() {
      // EXACT SAME LOGIC AS 2d_customization.js
      // Find the section (fieldGroup) - matches pattern: this.closest('.type-section')
      const section = this.closest('.type-section, .thickness-section, .edge-section, .frame-section, .field-section, div[class$="-section"]');
      
      if (section) {
        // Remove active from all siblings in this section (EXACT pattern from 2d_customization.js)
        section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        // Add active to clicked card
        this.classList.add('active');
      }
      
      // Update selected value in global object
      selectedCustomizationValues[field.id] = option;
      if (typeof window !== 'undefined') {
        window.selectedCustomizationValues = selectedCustomizationValues;
      }
      
      // Update price if needed
      if (tagPrices && tagPrices[field.id]) {
        // Find previously active tag for price update
        const fieldContainer = this.closest(`[data-field-id="${field.id}"]`);
        if (fieldContainer) {
          const previouslyActive = fieldContainer.querySelector('.option-card.active:not([data-value="' + option + '"])');
          if (previouslyActive) {
            const prevOption = previouslyActive.dataset.value;
            if (prevOption && tagPrices[field.id][prevOption]) {
              updatePriceFromTagSelection(field.id, prevOption, false);
            }
          }
        }
        // Add price for newly selected tag
        if (tagPrices[field.id][option]) {
          updatePriceFromTagSelection(field.id, option, true);
        }
      }
      
      // Update visualization
      updateKonvaFromField(field.id, option, true);
      
      // If shape field changed, check corner radius visibility
      if (field.id === 'shape') {
        setTimeout(() => {
          // Check for dynamically injected corner radius controls
          const injectedCornerRadius = document.getElementById('injected-corner-radius-controls');
          if (injectedCornerRadius) {
            checkInjectedCornerRadiusVisibility(injectedCornerRadius, option);
          }
          
          // Also check for regular corner radius containers (if they exist)
          const cornerRadiusContainers = document.querySelectorAll('[data-conditional-field="true"][data-depends-on="shape"]');
          cornerRadiusContainers.forEach(container => {
            checkCornerRadiusVisibility(container);
          });
        }, 50);
      }
      
      // Handle Windows_Sliding conditional logic
      handleWindowsSlidingConditionals(field.id, option);
      
      // Handle Mirrors conditional logic (Frame Color/Edge Finish based on Frame Type)
      handleMirrorsConditionals(field.id, option);
      
      // Trigger price recalculation
      if (typeof window !== 'undefined' && typeof window.updateRealTimePriceDisplay === 'function') {
        window.updateRealTimePriceDisplay();
      }
    });

    tagsGrid.appendChild(tag);
  });

  tagContainer.appendChild(tagsGrid);
  container.appendChild(tagContainer);
  
  // For shape field on specialty products, inject corner radius controls
  if (field.id === 'shape') {
    const product = getSelectedProduct();
    const isSpecialtyProduct = product && (
      product.subcategory === 'Mirrors' || 
      product.subcategory === 'Top Glass' || 
      product.subcategory === 'Glass Board'
    );
    
    if (isSpecialtyProduct) {
      // Inject corner radius controls after shape field
      setTimeout(() => {
        injectCornerRadiusControls(container);
      }, 100);
    }
  }
  
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
    
    // If more than one is active, remove active from all and add to first only
    if (activeCards.length > 1) {
      allCards.forEach(card => card.classList.remove('active'));
      if (allCards.length > 0) {
        allCards[0].classList.add('active');
      }
    } else if (activeCards.length === 0 && allCards.length > 0) {
      // If none are active, make the first one active
      allCards[0].classList.add('active');
    } else if (activeCards.length === 1) {
      // Ensure only this one is active - remove from all others
      allCards.forEach(card => {
        if (card !== activeCards[0]) {
          card.classList.remove('active');
        }
      });
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
  // Check if this is a corner radius field for mirrors/top glass/glass board
  // Handle both 'cornerRadius' and 'cornerRadiusIn' field IDs
  const isCornerRadius = field.id === 'cornerRadius' || field.id === 'cornerRadiusIn' || field.id === 'radius';
  const product = getSelectedProduct();
  const isSpecialtyProduct = product && (
    product.subcategory === 'Mirrors' || 
    product.subcategory === 'Top Glass' || 
    product.subcategory === 'Glass Board'
  );
  
  // For corner radius on specialty products, hide the field since it's injected automatically
  // The corner radius controls are injected dynamically when shape field is rendered
  if (isCornerRadius && isSpecialtyProduct) {
    container.style.display = 'none';
    return;
  }
  
  // Standard number field rendering
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
  if (isCornerRadius) {
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
 * Renders individual corner radius controls (for mirrors, top glass, glass board)
 * Supports both 'cornerRadius' and 'cornerRadiusIn' field IDs
 */
function renderIndividualCornerRadius(field, container) {
  // Normalize field ID - use 'cornerRadius' internally for consistency
  const fieldId = field.id === 'cornerRadiusIn' ? 'cornerRadius' : field.id;
  // Hide by default until shape is selected
  container.style.display = 'none';
  container.dataset.conditionalField = 'true';
  container.dataset.dependsOn = 'shape';
  
  // Create wrapper for corner radius controls
  const cornerRadiusWrapper = document.createElement('div');
  cornerRadiusWrapper.className = 'corner-radius-controls';
  cornerRadiusWrapper.style.marginTop = '10px';
  
  // Label and Unit Selector Row
  const headerRow = document.createElement('div');
  headerRow.style.display = 'flex';
  headerRow.style.justifyContent = 'space-between';
  headerRow.style.alignItems = 'center';
  headerRow.style.marginBottom = '15px';
  
  const label = document.createElement('label');
  label.textContent = field.label || 'Corner Radius';
  label.style.fontWeight = '500';
  label.style.color = '#333';
  label.style.fontSize = '16px';
  headerRow.appendChild(label);
  
  // Unit selector
  const unitSelectorWrapper = document.createElement('div');
  unitSelectorWrapper.style.display = 'flex';
  unitSelectorWrapper.style.alignItems = 'center';
  unitSelectorWrapper.style.gap = '8px';
  
  const unitLabel = document.createElement('label');
  unitLabel.textContent = 'Unit:';
  unitLabel.style.fontSize = '13px';
  unitLabel.style.color = '#666';
  unitSelectorWrapper.appendChild(unitLabel);
  
  const unitSelect = document.createElement('select');
  unitSelect.id = `${fieldId}_unit`;
  unitSelect.style.padding = '4px 8px';
  unitSelect.style.border = '1px solid #ddd';
  unitSelect.style.borderRadius = '4px';
  unitSelect.style.fontSize = '13px';
  unitSelect.style.cursor = 'pointer';
  unitSelect.value = 'in'; // Default to inches
  
  const unitOptions = [
    { value: 'in', label: 'in' },
    { value: 'cm', label: 'cm' },
    { value: 'mm', label: 'mm' }
  ];
  
  unitOptions.forEach(opt => {
    const option = document.createElement('option');
    option.value = opt.value;
    option.textContent = opt.label;
    unitSelect.appendChild(option);
  });
  
  unitSelectorWrapper.appendChild(unitSelect);
  headerRow.appendChild(unitSelectorWrapper);
  cornerRadiusWrapper.appendChild(headerRow);
  
  // Link All checkbox
  const linkWrapper = document.createElement('div');
  linkWrapper.style.marginBottom = '15px';
  linkWrapper.style.display = 'flex';
  linkWrapper.style.alignItems = 'center';
  linkWrapper.style.gap = '8px';
  
  const linkCheckbox = document.createElement('input');
  linkCheckbox.type = 'checkbox';
  linkCheckbox.id = `${fieldId}_linkAll`;
  linkCheckbox.checked = true; // Default to linked
  linkCheckbox.style.cursor = 'pointer';
  
  const linkLabel = document.createElement('label');
  linkLabel.htmlFor = linkCheckbox.id;
  linkLabel.textContent = 'Link All Corners';
  linkLabel.style.cursor = 'pointer';
  linkLabel.style.fontSize = '14px';
  linkLabel.style.color = '#666';
  
  linkWrapper.appendChild(linkCheckbox);
  linkWrapper.appendChild(linkLabel);
  cornerRadiusWrapper.appendChild(linkWrapper);
  
  // Master slider (shown when linked)
  const masterSliderWrapper = document.createElement('div');
  masterSliderWrapper.id = `${fieldId}_masterWrapper`;
  masterSliderWrapper.style.marginBottom = '15px';
  
  const masterLabel = document.createElement('label');
  masterLabel.textContent = 'All Corners';
  masterLabel.style.display = 'block';
  masterLabel.style.marginBottom = '5px';
  masterLabel.style.fontSize = '13px';
  masterLabel.style.color = '#555';
  
  const masterInputGroup = document.createElement('div');
  masterInputGroup.style.display = 'flex';
  masterInputGroup.style.flexDirection = 'column';
  masterInputGroup.style.gap = '8px';
  
  // Text input and slider row
  const masterControlRow = document.createElement('div');
  masterControlRow.style.display = 'flex';
  masterControlRow.style.alignItems = 'center';
  masterControlRow.style.gap = '10px';
  
  // Text input box
  const masterTextInput = document.createElement('input');
  masterTextInput.type = 'number';
  masterTextInput.id = `${fieldId}_masterText`;
  masterTextInput.min = 0;
  masterTextInput.step = 0.1;
  masterTextInput.value = 0;
  masterTextInput.style.width = '80px';
  masterTextInput.style.padding = '6px 8px';
  masterTextInput.style.border = '1px solid #ddd';
  masterTextInput.style.borderRadius = '4px';
  masterTextInput.style.fontSize = '14px';
  
  // Unit display (will update based on unit selector)
  const masterUnitDisplay = document.createElement('span');
  masterUnitDisplay.id = `${fieldId}_masterUnitDisplay`;
  masterUnitDisplay.textContent = 'in';
  masterUnitDisplay.style.fontSize = '14px';
  masterUnitDisplay.style.color = '#666';
  masterUnitDisplay.style.minWidth = '25px';
  
  // Slider
  const masterSlider = document.createElement('input');
  masterSlider.type = 'range';
  masterSlider.id = `${fieldId}_master`;
  masterSlider.min = 0;
  masterSlider.max = 20; // Max 20 inches (will be converted based on unit)
  masterSlider.step = 0.1;
  masterSlider.value = 0;
  masterSlider.style.flex = '1';
  masterSlider.style.cursor = 'pointer';
  
  masterControlRow.appendChild(masterTextInput);
  masterControlRow.appendChild(masterUnitDisplay);
  masterControlRow.appendChild(masterSlider);
  
  masterInputGroup.appendChild(masterControlRow);
  
  masterSliderWrapper.appendChild(masterLabel);
  masterSliderWrapper.appendChild(masterInputGroup);
  cornerRadiusWrapper.appendChild(masterSliderWrapper);
  
  // Individual corner controls (hidden when linked)
  const individualWrapper = document.createElement('div');
  individualWrapper.id = `${fieldId}_individualWrapper`;
  individualWrapper.style.display = 'none';
  individualWrapper.style.display = 'grid';
  individualWrapper.style.gridTemplateColumns = '1fr 1fr';
  individualWrapper.style.gap = '15px';
  
  const corners = [
    { id: 'TL', label: 'Top Left', key: 'topLeft' },
    { id: 'TR', label: 'Top Right', key: 'topRight' },
    { id: 'BL', label: 'Bottom Left', key: 'bottomLeft' },
    { id: 'BR', label: 'Bottom Right', key: 'bottomRight' }
  ];
  
  corners.forEach(corner => {
    const cornerGroup = document.createElement('div');
    cornerGroup.style.display = 'flex';
    cornerGroup.style.flexDirection = 'column';
    cornerGroup.style.gap = '5px';
    
    const cornerLabel = document.createElement('label');
    cornerLabel.textContent = corner.label;
    cornerLabel.style.fontSize = '13px';
    cornerLabel.style.color = '#555';
    
    const cornerInputGroup = document.createElement('div');
    cornerInputGroup.style.display = 'flex';
    cornerInputGroup.style.flexDirection = 'column';
    cornerInputGroup.style.gap = '5px';
    
    // Text input and slider row
    const cornerControlRow = document.createElement('div');
    cornerControlRow.style.display = 'flex';
    cornerControlRow.style.alignItems = 'center';
    cornerControlRow.style.gap = '8px';
    
    // Text input box
    const cornerTextInput = document.createElement('input');
    cornerTextInput.type = 'number';
    cornerTextInput.id = `${fieldId}_${corner.id}_text`;
    cornerTextInput.dataset.cornerKey = corner.key;
    cornerTextInput.min = 0;
    cornerTextInput.step = 0.1;
    cornerTextInput.value = 0;
    cornerTextInput.style.width = '70px';
    cornerTextInput.style.padding = '5px 6px';
    cornerTextInput.style.border = '1px solid #ddd';
    cornerTextInput.style.borderRadius = '4px';
    cornerTextInput.style.fontSize = '13px';
    
    // Unit display
    const cornerUnitDisplay = document.createElement('span');
    cornerUnitDisplay.id = `${fieldId}_${corner.id}_unit`;
    cornerUnitDisplay.textContent = 'in';
    cornerUnitDisplay.style.fontSize = '13px';
    cornerUnitDisplay.style.color = '#666';
    cornerUnitDisplay.style.minWidth = '20px';
    
    // Slider
    const cornerSlider = document.createElement('input');
    cornerSlider.type = 'range';
    cornerSlider.id = `${fieldId}_${corner.id}`;
    cornerSlider.dataset.cornerKey = corner.key;
    cornerSlider.min = 0;
    cornerSlider.max = 20;
    cornerSlider.step = 0.1;
    cornerSlider.value = 0;
    cornerSlider.style.flex = '1';
    cornerSlider.style.cursor = 'pointer';
    
    cornerControlRow.appendChild(cornerTextInput);
    cornerControlRow.appendChild(cornerUnitDisplay);
    cornerControlRow.appendChild(cornerSlider);
    
    cornerInputGroup.appendChild(cornerControlRow);
    
    cornerGroup.appendChild(cornerLabel);
    cornerGroup.appendChild(cornerInputGroup);
    individualWrapper.appendChild(cornerGroup);
    
    // Update text input when slider changes
    cornerSlider.addEventListener('input', () => {
      const value = parseFloat(cornerSlider.value) || 0;
      cornerTextInput.value = value.toFixed(1);
      updateCornerRadiusValues(fieldId);
    });
    
    // Update slider when text input changes
    cornerTextInput.addEventListener('input', () => {
      const value = parseFloat(cornerTextInput.value) || 0;
      const maxValue = getMaxValueForUnit(fieldId);
      const clampedValue = Math.min(Math.max(0, value), maxValue);
      cornerSlider.value = clampedValue;
      cornerTextInput.value = clampedValue.toFixed(1);
      updateCornerRadiusValues(fieldId);
    });
  });
  
  cornerRadiusWrapper.appendChild(individualWrapper);
  container.appendChild(cornerRadiusWrapper);
  
  // Unit conversion functions
  function convertToInches(value, unit) {
    switch(unit) {
      case 'cm': return value / 2.54;
      case 'mm': return value / 25.4;
      default: return value; // inches
    }
  }
  
  function convertFromInches(value, unit) {
    switch(unit) {
      case 'cm': return value * 2.54;
      case 'mm': return value * 25.4;
      default: return value; // inches
    }
  }
  
  function getMaxValueForUnit(fieldId) {
    const unit = document.getElementById(`${fieldId}_unit`)?.value || 'in';
    switch(unit) {
      case 'cm': return 50.8; // 20 inches = 50.8 cm
      case 'mm': return 508; // 20 inches = 508 mm
      default: return 20; // inches
    }
  }
  
  function updateUnitDisplay(fieldId, unit) {
    // Update master unit display
    const masterUnitDisplay = document.getElementById(`${fieldId}_masterUnitDisplay`);
    if (masterUnitDisplay) masterUnitDisplay.textContent = unit;
    
    // Update individual corner unit displays
    corners.forEach(corner => {
      const cornerUnitDisplay = document.getElementById(`${fieldId}_${corner.id}_unit`);
      if (cornerUnitDisplay) cornerUnitDisplay.textContent = unit;
    });
    
    // Update slider max values
    const maxValue = getMaxValueForUnit(fieldId);
    masterSlider.max = maxValue;
    corners.forEach(corner => {
      const slider = document.getElementById(`${fieldId}_${corner.id}`);
      if (slider) slider.max = maxValue;
    });
    
    // Convert current values to new unit
    const currentUnit = masterSlider.dataset.currentUnit || 'in';
    if (currentUnit !== unit) {
      // Get current value in inches
      const currentValueIn = parseFloat(masterSlider.value) || 0;
      const actualValueIn = convertToInches(currentValueIn, currentUnit);
      
      // Convert to new unit
      const newValue = convertFromInches(actualValueIn, unit);
      
      // Update master controls
      masterSlider.value = newValue;
      masterTextInput.value = newValue.toFixed(1);
      
      // Update individual corners if unlinked
      if (!linkCheckbox.checked) {
        corners.forEach(corner => {
          const slider = document.getElementById(`${fieldId}_${corner.id}`);
          const textInput = document.getElementById(`${fieldId}_${corner.id}_text`);
          if (slider && textInput) {
            const cornerValueIn = convertToInches(parseFloat(slider.value) || 0, currentUnit);
            const cornerNewValue = convertFromInches(cornerValueIn, unit);
            slider.value = cornerNewValue;
            textInput.value = cornerNewValue.toFixed(1);
          }
        });
      }
    }
    
    masterSlider.dataset.currentUnit = unit;
  }
  
  // Unit selector change handler
  unitSelect.addEventListener('change', () => {
    const newUnit = unitSelect.value;
    updateUnitDisplay(fieldId, newUnit);
    updateCornerRadiusValues(fieldId);
  });
  
  // Initialize unit
  masterSlider.dataset.currentUnit = 'in';
  
  // Link checkbox toggle
  linkCheckbox.addEventListener('change', () => {
    const isLinked = linkCheckbox.checked;
    masterSliderWrapper.style.display = isLinked ? 'block' : 'none';
    individualWrapper.style.display = isLinked ? 'none' : 'grid';
    
    if (isLinked) {
      // Sync all corners to master value
      const masterVal = parseFloat(masterSlider.value) || 0;
      corners.forEach(corner => {
        const slider = document.getElementById(`${fieldId}_${corner.id}`);
        const textInput = document.getElementById(`${fieldId}_${corner.id}_text`);
        if (slider) slider.value = masterVal;
        if (textInput) textInput.value = masterVal.toFixed(1);
      });
      updateCornerRadiusValues(fieldId);
    }
  });
  
  // Master slider handler
  masterSlider.addEventListener('input', () => {
    const value = parseFloat(masterSlider.value) || 0;
    masterTextInput.value = value.toFixed(1);
    
    if (linkCheckbox.checked) {
      // Update all corners
      corners.forEach(corner => {
        const slider = document.getElementById(`${fieldId}_${corner.id}`);
        const textInput = document.getElementById(`${fieldId}_${corner.id}_text`);
        if (slider) slider.value = value;
        if (textInput) textInput.value = value.toFixed(1);
      });
      updateCornerRadiusValues(fieldId);
    }
  });
  
  // Master text input handler
  masterTextInput.addEventListener('input', () => {
    const value = parseFloat(masterTextInput.value) || 0;
    const maxValue = getMaxValueForUnit(fieldId);
    const clampedValue = Math.min(Math.max(0, value), maxValue);
    masterSlider.value = clampedValue;
    masterTextInput.value = clampedValue.toFixed(1);
    
    if (linkCheckbox.checked) {
      // Update all corners
      corners.forEach(corner => {
        const slider = document.getElementById(`${fieldId}_${corner.id}`);
        const textInput = document.getElementById(`${fieldId}_${corner.id}_text`);
        if (slider) slider.value = clampedValue;
        if (textInput) textInput.value = clampedValue.toFixed(1);
      });
      updateCornerRadiusValues(fieldId);
    }
  });
  
  // Check initial shape selection
  checkCornerRadiusVisibility(container);
  
  // Listen for shape changes
  document.addEventListener('customizationFieldChanged', (e) => {
    if (e.detail.fieldId === 'shape') {
      checkCornerRadiusVisibility(container);
    }
  });
}

/**
 * Updates corner radius values in selectedCustomizationValues
 * Converts values to inches for Konva (which expects inches)
 */
function updateCornerRadiusValues(fieldId) {
  const linkCheckbox = document.getElementById(`${fieldId}_linkAll`);
  const isLinked = linkCheckbox ? linkCheckbox.checked : true;
  const unitSelect = document.getElementById(`${fieldId}_unit`);
  const currentUnit = unitSelect?.value || 'in';
  
  // Conversion function to inches
  function convertToInches(value, unit) {
    switch(unit) {
      case 'cm': return value / 2.54;
      case 'mm': return value / 25.4;
      default: return value; // inches
    }
  }
  
  if (isLinked) {
    // Single value mode - get from master slider/text input
    const masterSlider = document.getElementById(`${fieldId}_master`);
    const masterTextInput = document.getElementById(`${fieldId}_masterText`);
    const value = parseFloat(masterTextInput?.value || masterSlider?.value) || 0;
    
    // Convert to inches for storage (Konva expects inches)
    const valueInInches = convertToInches(value, currentUnit);
    selectedCustomizationValues[fieldId] = valueInInches;
    selectedCustomizationValues[`${fieldId}_unit`] = currentUnit; // Store unit for reference
    if (typeof window !== 'undefined') {
      window.selectedCustomizationValues = selectedCustomizationValues;
    }
    updateKonvaFromField(fieldId, valueInInches, true);
  } else {
    // Individual corner mode
    const corners = ['TL', 'TR', 'BL', 'BR'];
    const cornerKeys = ['topLeft', 'topRight', 'bottomLeft', 'bottomRight'];
    const cornerValues = {};
    
    corners.forEach((cornerId, index) => {
      const textInput = document.getElementById(`${fieldId}_${cornerId}_text`);
      const slider = document.getElementById(`${fieldId}_${cornerId}`);
      const value = parseFloat(textInput?.value || slider?.value) || 0;
      
      // Convert to inches for storage
      cornerValues[cornerKeys[index]] = convertToInches(value, currentUnit);
    });
    
    selectedCustomizationValues[fieldId] = cornerValues;
    selectedCustomizationValues[`${fieldId}_unit`] = currentUnit; // Store unit for reference
    if (typeof window !== 'undefined') {
      window.selectedCustomizationValues = selectedCustomizationValues;
    }
    updateKonvaFromField(fieldId, cornerValues, true);
  }
}

/**
 * Injects corner radius controls dynamically after shape field for specialty products
 */
function injectCornerRadiusControls(shapeContainer) {
  // Check if already injected
  if (document.getElementById('injected-corner-radius-controls')) {
    return;
  }
  
  // Create corner radius container
  const cornerRadiusContainer = document.createElement('div');
  cornerRadiusContainer.id = 'injected-corner-radius-controls';
  cornerRadiusContainer.className = 'field-section';
  cornerRadiusContainer.style.display = 'none'; // Hidden by default
  cornerRadiusContainer.style.marginTop = '20px';
  
  // Create field group wrapper
  const fieldGroup = document.createElement('div');
  fieldGroup.className = 'field-group';
  
  // Create field label
  const fieldLabel = document.createElement('h3');
  fieldLabel.textContent = 'Corner Radius';
  fieldLabel.style.marginBottom = '15px';
  fieldLabel.style.fontSize = '16px';
  fieldLabel.style.fontWeight = '600';
  fieldLabel.style.color = '#333';
  fieldGroup.appendChild(fieldLabel);
  
  // Create corner radius controls using the existing function
  const field = {
    id: 'cornerRadius',
    label: 'Corner Radius (in)',
    type: 'number',
    min: 0,
    step: 0.1
  };
  
  renderIndividualCornerRadius(field, cornerRadiusContainer);
  
  // Insert after shape container
  shapeContainer.parentNode.insertBefore(cornerRadiusContainer, shapeContainer.nextSibling);
  
  // Check initial visibility
  setTimeout(() => {
    const shapeField = document.querySelector('[data-field-id="shape"]');
    if (shapeField) {
      const activeShapeCard = shapeField.querySelector('.option-card.active');
      if (activeShapeCard) {
        const selectedShape = (activeShapeCard.dataset.value || activeShapeCard.textContent.trim()).toLowerCase();
        checkInjectedCornerRadiusVisibility(cornerRadiusContainer, selectedShape);
      }
    }
  }, 150);
}

/**
 * Check if injected corner radius controls should be visible based on selected shape
 */
function checkInjectedCornerRadiusVisibility(container, selectedShape) {
  if (!container) return;
  
  const shape = (selectedShape || '').toLowerCase();
  // Show for rectangle, square, and any shape with "round" or "edge" in the name
  const rectangleShapes = ['rectangle', 'rectangular', 'square', 'rectangle/square', 'rectangle-square'];
  const hasRoundEdge = shape.includes('round') || shape.includes('edge') || shape.includes('rounded');
  const isRectangleShape = rectangleShapes.includes(shape) || 
                           shape.includes('rectangle') || 
                           shape.includes('square');
  
  if (isRectangleShape || hasRoundEdge) {
    container.style.display = '';
  } else {
    container.style.display = 'none';
    // Reset values when hidden
    const masterSlider = container.querySelector('[id$="_master"]');
    const masterTextInput = container.querySelector('[id$="_masterText"]');
    if (masterSlider) masterSlider.value = '0';
    if (masterTextInput) masterTextInput.value = '0';
    
    const cornerSliders = container.querySelectorAll('[id$="_TL"], [id$="_TR"], [id$="_BL"], [id$="_BR"]');
    cornerSliders.forEach(slider => {
      slider.value = '0';
      const textInputId = slider.id.replace('_TL', '_TL_text').replace('_TR', '_TR_text').replace('_BL', '_BL_text').replace('_BR', '_BR_text');
      const textInput = document.getElementById(textInputId);
      if (textInput) textInput.value = '0';
    });
    updateCornerRadiusValues('cornerRadius');
  }
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
  const rectangleShapes = ['rectangle', 'rectangular', 'square', 'rectangle/square', 'rectangle-square'];
  
  // Show only if rectangle or square is selected
  const isRectangleShape = rectangleShapes.includes(selectedShape) || 
                           selectedShape.includes('rectangle') || 
                           selectedShape.includes('square');
  
  if (isRectangleShape) {
    container.style.display = '';
  } else {
    container.style.display = 'none';
    // Reset value when hidden
    const input = container.querySelector('input[type="number"]');
    if (input) {
      input.value = '0';
      updateKonvaFromField('cornerRadius', 0, true);
    }
    // Also reset individual corner controls
    const cornerControls = container.querySelector('.corner-radius-controls');
    if (cornerControls) {
      const masterSlider = container.querySelector('[id$="_master"]');
      if (masterSlider) masterSlider.value = '0';
      const cornerSliders = container.querySelectorAll('[id$="_TL"], [id$="_TR"], [id$="_BL"], [id$="_BR"]');
      cornerSliders.forEach(slider => {
        slider.value = '0';
        const valueSpan = document.getElementById(slider.id + '_value');
        if (valueSpan) valueSpan.textContent = '0';
      });
      updateCornerRadiusValues('cornerRadius');
    }
  }
}

/**
 * Handle conditional logic for Mirrors fields
 * Frame Color shows when Frame Type = "Framed"
 * Edge Finish shows when Frame Type = "Frameless"
 */
function handleMirrorsConditionals(changedFieldId, selectedValue) {
  if (changedFieldId === 'frameType') {
    const frameColorContainer = document.querySelector('[data-field-id="frameColor"]');
    const edgeFinishContainer = document.querySelector('[data-field-id="edgeFinish"]');
    
    const isFramed = selectedValue && selectedValue.toLowerCase() === 'framed';
    const isFrameless = selectedValue && selectedValue.toLowerCase() === 'frameless';
    
    // Show/hide Frame Color based on Frame Type
    if (frameColorContainer) {
      const fieldSection = frameColorContainer.closest('.field-section, .type-section, .frame-section');
      if (fieldSection) {
        if (isFramed) {
          fieldSection.style.display = '';
        } else {
          fieldSection.style.display = 'none';
          // Clear selection when hidden
          const activeCard = frameColorContainer.querySelector('.option-card.active');
          if (activeCard) {
            activeCard.classList.remove('active');
            delete selectedCustomizationValues['frameColor'];
          }
        }
      }
    }
    
    // Show/hide Edge Finish based on Frame Type
    if (edgeFinishContainer) {
      const fieldSection = edgeFinishContainer.closest('.field-section, .edge-section');
      if (fieldSection) {
        if (isFrameless) {
          fieldSection.style.display = '';
        } else {
          fieldSection.style.display = 'none';
          // Clear selection when hidden
          const activeCard = edgeFinishContainer.querySelector('.option-card.active');
          if (activeCard) {
            activeCard.classList.remove('active');
            delete selectedCustomizationValues['edgeFinish'];
          }
        }
      }
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
        // Check if it's a 2-panel option (S | S or F | S, but not S | S | S | S)
        const isTwoPanelOption = (optionValue.includes('S | S') && !optionValue.includes('S | S | S | S')) || 
                                  (optionValue.includes('F | S') && !optionValue.includes('F | S | S | F'));
        // Check if it's a 4-panel option
        const isFourPanelOption = optionValue.includes('S | S | S | S') || optionValue.includes('F | S | S | F');
        
        if (isTwoPanels) {
          // Show only 2-panel options, hide 4-panel options
          if (isTwoPanelOption) {
            option.style.display = '';
          } else {
            option.style.display = 'none';
            if (option.classList.contains('active')) {
              option.classList.remove('active');
            }
          }
        } else if (isFourPanels) {
          // Show only 4-panel options, hide 2-panel options
          if (isFourPanelOption) {
            option.style.display = '';
          } else {
            option.style.display = 'none';
            if (option.classList.contains('active')) {
              option.classList.remove('active');
            }
          }
        }
      });
      
      // Auto-select first visible option if none selected
      setTimeout(() => {
        const activeConfig = panelConfigContainer.querySelector('.option-card.active');
        if (!activeConfig || activeConfig.style.display === 'none') {
          const visibleOptions = Array.from(panelConfigContainer.querySelectorAll('.option-card')).filter(opt => opt.style.display !== 'none');
          if (visibleOptions.length > 0) {
            visibleOptions[0].classList.add('active');
            selectedCustomizationValues['panelConfiguration'] = visibleOptions[0].dataset.value || visibleOptions[0].textContent.trim();
          }
        }
      }, 50);
    }
  }
}

/**
 * Update Screen tags field availability based on Track System
 */
function updateScreenAvailability() {
  const trackSystemContainer = document.querySelector('[data-field-id="trackSystem"]');
  const screenContainer = document.querySelector('[data-field-id="screen"]');
  
  if (!trackSystemContainer || !screenContainer) return;
  
  const activeTrackOption = trackSystemContainer.querySelector('.option-card.active');
  if (!activeTrackOption) return;
  
  const selectedTrack = activeTrackOption.dataset.value || activeTrackOption.textContent.trim();
  const screenOptions = screenContainer.querySelectorAll('.option-card');
  
  if (screenOptions.length > 0) {
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
              selectedCustomizationValues['screen'] = 'Without Screen';
            }
          }
        } else {
          // Enable "Without Screen" option
          option.style.opacity = '';
          option.style.pointerEvents = '';
          option.classList.remove('disabled');
        }
      });
      
      // Show message
      let messageEl = screenContainer.querySelector('.conditional-message');
      if (!messageEl) {
        messageEl = document.createElement('div');
        messageEl.className = 'conditional-message';
        messageEl.style.color = '#999';
        messageEl.style.fontSize = '12px';
        messageEl.style.marginTop = '5px';
        messageEl.style.textAlign = 'center';
        screenContainer.appendChild(messageEl);
      }
      messageEl.textContent = 'Screen not available for 3 Tracks';
    } else {
      // Enable all screen options for 2 Tracks
      screenOptions.forEach(option => {
        option.style.opacity = '';
        option.style.pointerEvents = '';
        option.classList.remove('disabled');
      });
      const messageEl = screenContainer.querySelector('.conditional-message');
      if (messageEl) {
        messageEl.remove();
      }
    }
  }
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
function getFieldGroupClass(type, fieldId) {
  const classMap = {
    'tags': 'type-section',
    'checkbox': 'checkbox-section',
    'number': 'field-section',
    'color': 'color-section'
  };

  let baseClass = classMap[type] || 'field-section';

  // Give thickness-related number fields a dedicated section class
  if (fieldId === 'thickness' || fieldId === 'glassThickness') {
    baseClass = `${baseClass} thickness-section`;
  }

  return baseClass.trim();
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
    'radius': 'cornerRadius',
    // Mirror-specific fields
    'tint': 'glassType',
    'Tint': 'glassType',
    'orientation': 'orientation',
    'Orientation': 'orientation',
    'lighting': 'lighting',
    'Lighting': 'lighting',
    'ledColor': 'ledColor',
    'LEDColor': 'ledColor',
    'smartFeatures': 'smartFeatures',
    'SmartFeatures': 'smartFeatures',
    'frameType': 'frameType',
    'FrameType': 'frameType'
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
      // Can be a single number (linked mode) or an object with individual corners
      if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
        // Individual corner values
        if (window.currentCornerRadius !== undefined) {
          window.currentCornerRadius = value;
        }
        if (window.cornerRadiusLinked !== undefined) {
          window.cornerRadiusLinked = false;
        }
        try {
          if (typeof currentCornerRadius !== 'undefined') {
            currentCornerRadius = value;
          }
          if (typeof cornerRadiusLinked !== 'undefined') {
            cornerRadiusLinked = false;
          }
        } catch (e) {}
      } else {
        // Single value (linked mode)
        const radiusIn = parseFloat(value) || 0;
        if (window.currentCornerRadius !== undefined) {
          window.currentCornerRadius = radiusIn;
        }
        if (window.cornerRadiusLinked !== undefined) {
          window.cornerRadiusLinked = true;
        }
        try {
          if (typeof currentCornerRadius !== 'undefined') {
            currentCornerRadius = radiusIn;
          }
          if (typeof cornerRadiusLinked !== 'undefined') {
            cornerRadiusLinked = true;
          }
        } catch (e) {}
      }
    } else if (konvaParam === 'numberOfPanels' || konvaParam === 'operation' || konvaParam === 'configuration') {
      // Multi-panel fields - store in selectedCustomizationValues for renderWindow to use
      // These will be automatically picked up by renderWindow when it checks shouldUseMultiPanelRendering
      // No need to update individual state variables, just ensure the value is stored
    }
  }

  // Ensure global reference is updated (for AJAX proxy detection)
  if (typeof window !== 'undefined') {
    window.selectedCustomizationValues = selectedCustomizationValues;
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

    // Trigger AJAX update if available (for price/auto-save)
    if (typeof window.customizationAjax !== 'undefined' && typeof window.customizationAjax.updatePrice === 'function') {
      window.customizationAjax.updatePrice();
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
        nextBtn.innerHTML = `Finalize Order <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
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
  freshNextBtn.innerHTML = `Finalize Order <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
  
  // Direct-to-summary handler
  freshNextBtn.addEventListener('click', () => {
    if (typeof window.showOrderSummary === 'function') {
      window.showOrderSummary();
    } else {
      console.log('Finalizing Custom Order...');
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
    const currentStep = window.currentStep || 1;
    
    if (currentStep < totalSteps) {
      // Move to next step
      goToDynamicStep(currentStep + 1);
    } else {
      // Final step - show summary
      if (typeof window.showOrderSummary === 'function') {
        window.showOrderSummary();
      } else {
        console.log('Finalizing Custom Order...');
      }
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
  
  // Hide/Show dimensions container (Height/Width) - only show on step 1
  const dimensionsContainer = document.querySelector('.dimensions-container');
  if (dimensionsContainer) {
    if (targetStep === 1) {
      dimensionsContainer.classList.remove('hidden-step');
    } else {
      dimensionsContainer.classList.add('hidden-step');
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
      nextBtn.innerHTML = `Finalize Order <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
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
 * Syncs JavaScript state variables with the active DOM selections
 * This ensures Konva renders the correct shape when the page loads
 * Must be called AFTER fields are rendered and enforceSingleSelection() runs
 * 
 * IMPORTANT: This function also ensures admin-configured visual styles are loaded
 * and applied to the 2D preview, syncing the admin's color/style settings to customer view.
 */
function syncStateFromActiveSelections() {
  console.log('[Sync] ========== SYNCING STATE FROM DOM ==========');
  
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
