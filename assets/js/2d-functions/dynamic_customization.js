// =====================================================
// DYNAMIC CUSTOMIZATION RENDERER
// Renders customization fields dynamically based on product data
// Connects to Konva.js for visualization
// =====================================================

// IMPORTANT: Always use window.selectedCustomizationValues directly to preserve Proxy
// The Proxy is created in customization_ajax.js to track changes
if (typeof window !== 'undefined' && !window.selectedCustomizationValues) {
    window.selectedCustomizationValues = {};
    console.log('🔧 Initialized window.selectedCustomizationValues');
}

// DEBUG: Monitor changes to selectedCustomizationValues
function logCustomizationChange(fieldId, value, source) {
    console.log(`📝 Customization changed:`, {
        field: fieldId,
        value: value,
        source: source,
        allValues: {...window.selectedCustomizationValues}
    });
}

// Get selectedProduct from window (set from 2DModeling.php)
function getSelectedProduct() {
  return window.selectedProduct || null;
}

// ============================================
// HELPER FUNCTIONS FOR DOOR DIMENSIONS
// These are defined globally so they can be accessed from multiple scopes
// ============================================

/**
 * Gets total width and height from the main dimension inputs
 */
function getDoorTotalDimensions() {
  const heightInput = document.getElementById('height');
  const widthInput = document.getElementById('width');
  return {
    totalHeight: heightInput ? parseFloat(heightInput.value) || 0 : 0,
    totalWidth: widthInput ? parseFloat(widthInput.value) || 0 : 0
  };
}

/**
 * Gets current Fixed Panels option from DOM
 */
function getDoorFixedPanelsOption() {
  const fixedPanelsContainer = document.querySelector('[data-field-id="fixedPanels"]');
  if (!fixedPanelsContainer) return 'None';
  const activeCard = fixedPanelsContainer.querySelector('.option-card.active');
  return activeCard ? activeCard.textContent.trim() : 'None';
}

/**
 * Auto-calculate h1 + h2 = totalHeight for Transom/Both options
 */
function autoDoorCalculateHeights() {
  const fixedPanels = getDoorFixedPanelsOption();
  const { totalHeight } = getDoorTotalDimensions();
  
  const h1Input = document.getElementById('input-h1');
  const h2Input = document.getElementById('input-h2');
  
  if (!h1Input || !h2Input) {
    console.log('🚪 [Auto-Height] Input fields not found');
    return;
  }
  
  if (!totalHeight || totalHeight <= 0) {
    console.log('🚪 [Auto-Height] Skipping - no total height set');
    return;
  }
  
  const hasTransom = fixedPanels === 'Transom Only' || fixedPanels === 'Both';
  if (!hasTransom) {
    console.log(`🚪 [Auto-Height] Skipping - current fixedPanels: "${fixedPanels}"`);
    return;
  }
  
  const h1 = parseFloat(h1Input.value) || 0;
  const h2 = parseFloat(h2Input.value) || 0;
  
  console.log(`🚪 [Auto-Height] fixedPanels="${fixedPanels}", totalHeight=${totalHeight}, h1=${h1}, h2=${h2}`);
  
  // If user is actively editing h1
  if (h1Input === document.activeElement && h1 > 0) {
    const calculatedH2 = totalHeight - h1;
    if (calculatedH2 > 0) {
      h2Input.value = calculatedH2.toFixed(2);
      console.log(`🚪 [Auto-Height] h2 = ${totalHeight} - ${h1} = ${calculatedH2.toFixed(2)}`);
    }
  }
  // If user is actively editing h2
  else if (h2Input === document.activeElement && h2 > 0) {
    const calculatedH1 = totalHeight - h2;
    if (calculatedH1 > 0) {
      h1Input.value = calculatedH1.toFixed(2);
      console.log(`🚪 [Auto-Height] h1 = ${totalHeight} - ${h2} = ${calculatedH1.toFixed(2)}`);
    }
  }
  // If no field is active and neither has value, auto-populate
  else if (!h1 && !h2) {
    const h1Val = totalHeight * 0.7;
    const h2Val = totalHeight * 0.3;
    h1Input.value = h1Val.toFixed(2);
    h2Input.value = h2Val.toFixed(2);
    console.log(`🚪 [Auto-Height] h1 & h2 (default 70%/30%) = ${h1Val.toFixed(2)} & ${h2Val.toFixed(2)}`);
  }
}

/**
 * Auto-calculate w1 + w2 + w3 = totalWidth for various Fixed Panels options
 */
function autoDoorCalculateWidths() {
  const fixedPanels = getDoorFixedPanelsOption();
  const { totalWidth } = getDoorTotalDimensions();
  
  const w1Input = document.getElementById('input-w1');
  const w2Input = document.getElementById('input-w2');
  const w3Input = document.getElementById('input-w3');
  
  if (!w1Input || !w2Input || !w3Input) {
    console.log('🚪 [Auto-Width] Input fields not found');
    return;
  }
  
  if (!totalWidth || totalWidth <= 0) {
    console.log('🚪 [Auto-Width] Skipping - no total width set');
    return;
  }
  
  console.log(`🚪 [Auto-Width] fixedPanels="${fixedPanels}", totalWidth=${totalWidth}`);
  
  const w1 = parseFloat(w1Input.value) || 0;
  const w2 = parseFloat(w2Input.value) || 0;
  const w3 = parseFloat(w3Input.value) || 0;
  
  // Handle "None" case
  if (fixedPanels === 'None') {
    if (!w1) {
      w1Input.value = totalWidth.toFixed(2);
      w2Input.value = '0';
      w3Input.value = '0';
      console.log(`🚪 [Auto-Width] w1 (None) = totalWidth = ${totalWidth.toFixed(2)}, w2=0, w3=0`);
    }
    return;
  }
  
  const hasWidthOptions = fixedPanels === '2 Panels' || fixedPanels === 'Both' || 
                         fixedPanels === 'Fixed Side (Left)' || fixedPanels === 'Fixed Side (Right)';
  if (!hasWidthOptions) {
    console.log(`🚪 [Auto-Width] Skipping - current fixedPanels: "${fixedPanels}"`);
    return;
  }
  
  // If user is editing w1
  if (w1Input === document.activeElement && w1 > 0) {
    const remaining = totalWidth - w1;
    if (remaining >= 0) {
      if (fixedPanels === 'Both') {
        const halfRemaining = remaining / 2;
        w2Input.value = halfRemaining.toFixed(2);
        w3Input.value = halfRemaining.toFixed(2);
        console.log(`🚪 [Auto-Width] w2 & w3 (Both) = ${halfRemaining.toFixed(2)}`);
      } else if (fixedPanels === 'Fixed Side (Left)') {
        w2Input.value = remaining.toFixed(2);
        w3Input.value = '0';
        console.log(`🚪 [Auto-Width] w2 (Left) = ${remaining.toFixed(2)}`);
      } else if (fixedPanels === 'Fixed Side (Right)') {
        w2Input.value = '0';
        w3Input.value = remaining.toFixed(2);
        console.log(`🚪 [Auto-Width] w3 (Right) = ${remaining.toFixed(2)}`);
      } else if (fixedPanels === '2 Panels') {
        const halfRemaining = remaining / 2;
        w2Input.value = halfRemaining.toFixed(2);
        w3Input.value = halfRemaining.toFixed(2);
        console.log(`🚪 [Auto-Width] w2 & w3 (2 Panels) = ${halfRemaining.toFixed(2)}`);
      }
    }
  }
  // If user is editing w2
  else if (w2Input === document.activeElement && w2 > 0) {
    const remaining = totalWidth - w1 - w2;
    if (remaining >= 0) {
      w3Input.value = remaining.toFixed(2);
      console.log(`🚪 [Auto-Width] w3 = ${totalWidth} - ${w1} - ${w2} = ${remaining.toFixed(2)}`);
    }
  }
  // If user is editing w3
  else if (w3Input === document.activeElement && w3 > 0) {
    const remaining = totalWidth - w1 - w3;
    if (remaining >= 0) {
      w2Input.value = remaining.toFixed(2);
      console.log(`🚪 [Auto-Width] w2 = ${totalWidth} - ${w1} - ${w3} = ${remaining.toFixed(2)}`);
    }
  }
  // If no field is active and none have values, auto-populate
  else if (!w1 && !w2 && !w3) {
    console.log(`🚪 [Auto-Width] No values set - auto-populating for "${fixedPanels}"`);
    
    if (fixedPanels === 'Both') {
      const w2Val = totalWidth * 0.25;
      const w1Val = totalWidth * 0.50;
      const w3Val = totalWidth * 0.25;
      w1Input.value = w1Val.toFixed(2);
      w2Input.value = w2Val.toFixed(2);
      w3Input.value = w3Val.toFixed(2);
      console.log(`🚪 [Auto-Width] w1, w2, w3 (Both: 50%/25%/25%) = ${w1Val.toFixed(2)}, ${w2Val.toFixed(2)}, ${w3Val.toFixed(2)}`);
    } 
    else if (fixedPanels === 'Fixed Side (Left)') {
      const w1Val = totalWidth * 0.70;
      const w2Val = totalWidth * 0.30;
      w1Input.value = w1Val.toFixed(2);
      w2Input.value = w2Val.toFixed(2);
      w3Input.value = '0';
      console.log(`🚪 [Auto-Width] w1, w2 (Left: 70%/30%) = ${w1Val.toFixed(2)}, ${w2Val.toFixed(2)}`);
    } 
    else if (fixedPanels === 'Fixed Side (Right)') {
      const w1Val = totalWidth * 0.70;
      const w3Val = totalWidth * 0.30;
      w1Input.value = w1Val.toFixed(2);
      w2Input.value = '0';
      w3Input.value = w3Val.toFixed(2);
      console.log(`🚪 [Auto-Width] w1, w3 (Right: 70%/30%) = ${w1Val.toFixed(2)}, ${w3Val.toFixed(2)}`);
    }
    else if (fixedPanels === '2 Panels') {
      const halfWidth = totalWidth / 2;
      w1Input.value = halfWidth.toFixed(2);
      w2Input.value = halfWidth.toFixed(2);
      w3Input.value = '0';
      console.log(`🚪 [Auto-Width] w1, w2 (2 Panels: 50%/50%) = ${halfWidth.toFixed(2)}, ${halfWidth.toFixed(2)}`);
    }
  }
}

/**
 * Creates h1, h2, w1, w2, w3 sub-dimension input fields for door products
 * These are always created for door products regardless of whether a dimensions field exists
 */
function createDoorSubDimensionInputs(container) {
  console.log('🚪 [Door Inputs] createDoorSubDimensionInputs called');
  
  // Check if this is a door product
  const product = getSelectedProduct();
  console.log('🚪 [Door Inputs] Selected product:', product);
  
  const isDoorProduct = product?.category === 'Doors' || 
                       (product?.Subcategory && 
                        ['Frameless', 'Swing Door', 'Sliding', 'Bi-fold Door', 'Patch Fitting'].includes(product.Subcategory));
  
  console.log('🚪 [Door Inputs] Is door product:', isDoorProduct);
  
  if (!isDoorProduct) {
    console.log('🚪 [Door Inputs] ❌ Not a door product, skipping');
    return; // Not a door product, skip
  }
  
  // Check if inputs already exist
  if (document.getElementById('input-group-h1')) {
    console.log('🚪 [Door Inputs] ⚠️ Inputs already exist, skipping creation');
    return; // Already created
  }
  
  console.log('🚪 [Door Inputs] Creating inputs...');
  
  // Create the dimensions container if it doesn't exist
  let subDimensionsContainer = document.querySelector('.sub-dimensions-container');
  if (!subDimensionsContainer) {
    subDimensionsContainer = document.createElement('div');
    subDimensionsContainer.className = 'dimensions-container sub-dimensions-container';
    subDimensionsContainer.style.cssText = 'width: 100%;';
    
    // Append to container (at the end)
    container.appendChild(subDimensionsContainer);
  }
  
  // Create h1 input (Door Height)
  const h1InputGroup = document.createElement('div');
  h1InputGroup.id = 'input-group-h1';
  h1InputGroup.className = 'hidden-step';
  const h1Label = document.createElement('label');
  h1Label.textContent = 'Door Height (h1)';
  const h1InputField = document.createElement('input');
  h1InputField.type = 'number';
  h1InputField.id = 'input-h1';
  h1InputField.className = 'dimension-input';
  h1InputField.min = 0;
  h1InputField.step = 0.1;
  h1InputField.placeholder = '0.0';
  const h1Unit = document.createElement('span');
  h1Unit.textContent = 'Inches';
  h1InputGroup.appendChild(h1Label);
  h1InputGroup.appendChild(h1InputField);
  h1InputGroup.appendChild(h1Unit);
  
  // Create h2 input (Transom Height)
  const h2InputGroup = document.createElement('div');
  h2InputGroup.id = 'input-group-h2';
  h2InputGroup.className = 'hidden-step';
  const h2Label = document.createElement('label');
  h2Label.textContent = 'Transom Height (h2)';
  const h2InputField = document.createElement('input');
  h2InputField.type = 'number';
  h2InputField.id = 'input-h2';
  h2InputField.className = 'dimension-input';
  h2InputField.min = 0;
  h2InputField.step = 0.1;
  h2InputField.placeholder = '0.0';
  const h2Unit = document.createElement('span');
  h2Unit.textContent = 'Inches';
  h2InputGroup.appendChild(h2Label);
  h2InputGroup.appendChild(h2InputField);
  h2InputGroup.appendChild(h2Unit);
  
  // Create w1 input (Door Width)
  const w1InputGroup = document.createElement('div');
  w1InputGroup.id = 'input-group-w1';
  w1InputGroup.className = 'hidden-step';
  const w1Label = document.createElement('label');
  w1Label.textContent = 'Door Width (w1)';
  const w1InputField = document.createElement('input');
  w1InputField.type = 'number';
  w1InputField.id = 'input-w1';
  w1InputField.className = 'dimension-input';
  w1InputField.min = 0;
  w1InputField.step = 0.1;
  w1InputField.placeholder = '0.0';
  const w1Unit = document.createElement('span');
  w1Unit.textContent = 'Inches';
  w1InputGroup.appendChild(w1Label);
  w1InputGroup.appendChild(w1InputField);
  w1InputGroup.appendChild(w1Unit);
  
  // Create w2 input (Left Panel Width)
  const w2InputGroup = document.createElement('div');
  w2InputGroup.id = 'input-group-w2';
  w2InputGroup.className = 'hidden-step';
  const w2Label = document.createElement('label');
  w2Label.textContent = 'Left Panel Width (w2)';
  const w2InputField = document.createElement('input');
  w2InputField.type = 'number';
  w2InputField.id = 'input-w2';
  w2InputField.className = 'dimension-input';
  w2InputField.min = 0;
  w2InputField.step = 0.1;
  w2InputField.placeholder = '0.0';
  const w2Unit = document.createElement('span');
  w2Unit.textContent = 'Inches';
  w2InputGroup.appendChild(w2Label);
  w2InputGroup.appendChild(w2InputField);
  w2InputGroup.appendChild(w2Unit);
  
  // Create w3 input (Right Panel Width)
  const w3InputGroup = document.createElement('div');
  w3InputGroup.id = 'input-group-w3';
  w3InputGroup.className = 'hidden-step';
  const w3Label = document.createElement('label');
  w3Label.textContent = 'Right Panel Width (w3)';
  const w3InputField = document.createElement('input');
  w3InputField.type = 'number';
  w3InputField.id = 'input-w3';
  w3InputField.className = 'dimension-input';
  w3InputField.min = 0;
  w3InputField.step = 0.1;
  w3InputField.placeholder = '0.0';
  const w3Unit = document.createElement('span');
  w3Unit.textContent = 'Inches';
  w3InputGroup.appendChild(w3Label);
  w3InputGroup.appendChild(w3InputField);
  w3InputGroup.appendChild(w3Unit);
  
  // Add all to sub-dimensions container
  subDimensionsContainer.appendChild(h1InputGroup);
  subDimensionsContainer.appendChild(h2InputGroup);
  subDimensionsContainer.appendChild(w1InputGroup);
  subDimensionsContainer.appendChild(w2InputGroup);
  subDimensionsContainer.appendChild(w3InputGroup);
  
  console.log('🚪 [Door Inputs] All input groups appended to container');
  
  // Add input event listeners for all sub-dimensions with auto-calculation
  [h1InputField, h2InputField, w1InputField, w2InputField, w3InputField].forEach(input => {
    input.addEventListener('input', () => {
      console.log(`🚪 [Door Inputs] ${input.id} changed to:`, input.value);
      
      // Auto-calculate related values using the global functions
      if (input.id === 'input-h1' || input.id === 'input-h2') {
        autoDoorCalculateHeights();
      } else if (input.id === 'input-w1' || input.id === 'input-w2' || input.id === 'input-w3') {
        autoDoorCalculateWidths();
      }
      
      // Update Konva preview
      if (typeof renderCustomState === 'function') {
        setTimeout(() => renderCustomState(), 100);
      }
    });
  });
  
  console.log('✅ [Door Inputs] Door sub-dimension inputs (h1, h2, w1, w2, w3) created successfully');
  console.log('🚪 [Door Inputs] Verifying elements in DOM:');
  console.log('🚪 [Door Inputs] h1 input-group found:', !!document.getElementById('input-group-h1'));
  console.log('🚪 [Door Inputs] h2 input-group found:', !!document.getElementById('input-group-h2'));
  console.log('🚪 [Door Inputs] w1 input-group found:', !!document.getElementById('input-group-w1'));
  console.log('🚪 [Door Inputs] w2 input-group found:', !!document.getElementById('input-group-w2'));
  console.log('🚪 [Door Inputs] w3 input-group found:', !!document.getElementById('input-group-w3'));
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
  
  // Create h1, h2, w1, w2, w3 sub-dimension inputs for door products if they don't exist yet
  createDoorSubDimensionInputs(container);
  
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
      // Check for transom type field (try both possible field IDs)
      const transomTypeContainer = document.querySelector('[data-field-id="transomType"]') || 
                                    document.querySelector('[data-field-id="transomTypeTopBottomFixedPanel"]');
      const numberOfPanelsContainer = document.querySelector('[data-field-id="numberOfPanels"]');
      const trackSystemContainer = document.querySelector('[data-field-id="trackSystem"]');
      
      if (transomTypeContainer) {
        const activeTransom = transomTypeContainer.querySelector('.option-card.active');
        if (activeTransom) {
          const transomValue = activeTransom.dataset.value || activeTransom.textContent.trim();
          // Get the actual field ID from the container
          const fieldId = transomTypeContainer.getAttribute('data-field-id');
          handleWindowsSlidingConditionals(fieldId, transomValue);
          // Also toggle h1 input visibility on initial load
          toggleH1InputVisibility(transomValue);
        }
      } else {
        // If no transom container found, make sure h1 is hidden
        toggleH1InputVisibility('None');
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
      
      // Initialize conditional logic for Awning windows (rows/columns visibility)
      const sizeConfigContainer = document.querySelector('[data-field-id="sizeConfiguration"]');
      if (sizeConfigContainer) {
        const activeSizeConfig = sizeConfigContainer.querySelector('.option-card.active');
        if (activeSizeConfig) {
          const sizeConfigValue = activeSizeConfig.dataset.value || activeSizeConfig.textContent.trim();
          handleAwningConditionals('sizeConfiguration', sizeConfigValue);
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
    
    // No spec tags selected by default - do not auto-select first card
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
    
    // No spec tags selected by default - do not auto-select first card
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
    
    // No spec tags selected by default - do not auto-select first card
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
  fieldGroup.dataset.fieldId = field.id; // Set data-field-id for all field types
  
  // Handle conditional fields (for mirrors: Frame Color/Edge Finish, and awning: rows/columns)
  if (field.conditional && field.dependsOn) {
    fieldGroup.dataset.conditionalField = 'true';
    fieldGroup.dataset.dependsOn = field.dependsOn;
    fieldGroup.dataset.showWhen = field.showWhen || '';
    // Initially hide conditional fields - they'll be shown by conditional handlers
    if (field.id === 'frameColor' || field.id === 'edgeFinish' || field.id === 'panelRows' || field.id === 'panelColumns') {
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
    case 'dimensions':
      renderDimensionsField(field, fieldGroup);
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
      // Find the section (fieldGroup) - matches pattern: this.closest('.type-section')
      const section = this.closest('.type-section, .thickness-section, .edge-section, .frame-section, .field-section, div[class$="-section"]');

      // Toggle behavior: if already active, remove it; otherwise, make it active and remove others
      const isCurrentlyActive = this.classList.contains('active');

      if (section) {
        if (isCurrentlyActive) {
          // Remove active from this card (unselect)
          this.classList.remove('active');
        } else {
          // Remove active from all siblings in this section and add to clicked card
          section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
          this.classList.add('active');
        }
      }

      // Update selected value in global object (preserve Proxy by updating properties directly)
      if (isCurrentlyActive) {
        // Remove from selection
        delete window.selectedCustomizationValues[field.id];
      } else {
        // Add to selection
        window.selectedCustomizationValues[field.id] = option;
        logCustomizationChange(field.id, option, 'option-card click');
      }

      console.log(`Tag clicked: field="${field.id}", option="${option}", selected=${!isCurrentlyActive}`);

      // Update price if needed
      if (tagPrices && tagPrices[field.id]) {
        if (isCurrentlyActive) {
          // Remove price for unselected tag
          if (tagPrices[field.id][option]) {
            updatePriceFromTagSelection(field.id, option, false);
          }
        } else {
          // Find previously active tag for price update (should be none since we clear all)
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
      }

      // Update visualization
      console.log(`Updating Konva visualization: field="${field.id}", option="${option}", selected=${!isCurrentlyActive}`);
      updateKonvaFromField(field.id, option, !isCurrentlyActive);
      
      // Handle Fixed Panels input visibility for renderDoorsFrameless
      if (field.id === 'fixedPanels' && typeof window.updateInputVisibility === 'function') {
        console.log('🚪 [Tag Click] Calling updateInputVisibility with option:', option);
        window.updateInputVisibility(option);
        
        // Also trigger auto-calculation and re-render to update the 2D preview
        if (typeof window !== 'undefined') {
          setTimeout(() => {
            // Trigger auto-calculation for the new Fixed Panels option
            console.log('🚪 [Tag Click] Triggering auto-calculation after visibility change');
            
            // Directly call auto-calculation functions
            if (typeof window.autoDoorCalculateHeights === 'function') {
              console.log('🚪 [Tag Click] Calling autoDoorCalculateHeights()');
              window.autoDoorCalculateHeights();
            }
            if (typeof window.autoDoorCalculateWidths === 'function') {
              console.log('🚪 [Tag Click] Calling autoDoorCalculateWidths()');
              window.autoDoorCalculateWidths();
            }
            
            // Then render to update the 2D preview
            if (typeof window.renderCustomState === 'function') {
              window.renderCustomState();
            }
          }, 50);
        }
      } else if (field.id === 'fixedPanels') {
        console.log('🚪 [Tag Click] updateInputVisibility NOT available on window object!');
      }
      
      // If shape field changed, check corner radius visibility immediately
      if (field.id === 'shape') {
        // Check immediately (no delay) for instant visibility update
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
      }
      
      // Handle Windows_Sliding conditional logic
      handleWindowsSlidingConditionals(field.id, option);
      
      // Handle Mirrors conditional logic (Frame Color/Edge Finish based on Frame Type)
      handleMirrorsConditionals(field.id, option);
      
      // Handle Awning windows conditional logic (rows/columns visibility)
      handleAwningConditionals(field.id, option);
      
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
        
        // Immediately check visibility for initially selected shape
        const injectedCornerRadius = document.getElementById('injected-corner-radius-controls');
        if (injectedCornerRadius) {
          const activeShapeCard = tagContainer.querySelector('.option-card.active');
          if (activeShapeCard) {
            const selectedShape = (activeShapeCard.dataset.value || activeShapeCard.textContent.trim()).toLowerCase();
            checkInjectedCornerRadiusVisibility(injectedCornerRadius, selectedShape);
          }
        }
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
    
    // If more than one is active, keep only the first one active
    if (activeCards.length > 1) {
      allCards.forEach(card => card.classList.remove('active'));
      if (allCards.length > 0) {
        allCards[0].classList.add('active');
      }
    } else if (activeCards.length === 1) {
      // Ensure only this one is active - remove from all others
      allCards.forEach(card => {
        if (card !== activeCards[0]) {
          card.classList.remove('active');
        }
      });
    }
    
    // CRITICAL FIX: Trigger Konva update for initially active option
    // This ensures the first selected button appears in Konva on initial page load
    const finalActiveCard = tagContainer.querySelector('.option-card.active');
    if (finalActiveCard) {
      const activeOption = finalActiveCard.dataset.value || finalActiveCard.textContent.trim();
      // Update selected value in global object (preserve Proxy)
      window.selectedCustomizationValues[field.id] = activeOption;
      logCustomizationChange(field.id, activeOption, 'initial active card');
      
      // If this is the shape field, check corner radius visibility immediately
      if (field.id === 'shape') {
        // Check for dynamically injected corner radius controls
        setTimeout(() => {
          const injectedCornerRadius = document.getElementById('injected-corner-radius-controls');
          if (injectedCornerRadius) {
            checkInjectedCornerRadiusVisibility(injectedCornerRadius, activeOption);
          }
          
          // Also check for regular corner radius containers
          const cornerRadiusContainers = document.querySelectorAll('[data-conditional-field="true"][data-depends-on="shape"]');
          cornerRadiusContainers.forEach(container => {
            checkCornerRadiusVisibility(container);
          });
        }, 150); // Slightly longer delay to ensure controls are injected
      }
      
      // If this is the frameType field, check mirrors conditionals immediately
      if (field.id === 'frameType') {
        setTimeout(() => {
          handleMirrorsConditionals('frameType', activeOption);
        }, 50);
      }
      
      // Trigger Konva update for the initially active option
      // Use a small delay to ensure Konva stage is initialized
      setTimeout(() => {
        updateKonvaFromField(field.id, activeOption, true);
      }, 100);
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
  
  // For panel rows/columns fields, show/hide based on size configuration
  if (field.id === 'panelRows' || field.id === 'panelColumns') {
    container.style.display = 'none'; // Hide by default
    container.dataset.conditionalField = 'true';
    container.dataset.dependsOn = 'sizeConfiguration';
    container.dataset.showWhen = 'Multiple panels';
    
    // Set default value
    input.value = field.id === 'panelRows' ? '1' : '1';
    window.selectedCustomizationValues[field.id] = 1;
    
    // Check initial size configuration selection
    const sizeConfigContainer = document.querySelector('[data-field-id="sizeConfiguration"]');
    if (sizeConfigContainer) {
      const activeSizeConfig = sizeConfigContainer.querySelector('.option-card.active');
      if (activeSizeConfig) {
        const sizeConfigValue = activeSizeConfig.dataset.value || activeSizeConfig.textContent.trim();
        handleAwningConditionals('sizeConfiguration', sizeConfigValue);
      }
    }
  }
  
  input.addEventListener('input', () => {
    const value = parseFloat(input.value) || (field.id === 'panelRows' || field.id === 'panelColumns' ? 1 : 0);
    window.selectedCustomizationValues[field.id] = value;
    logCustomizationChange(field.id, value, 'number input');
    updateKonvaFromField(field.id, value, true);
  });

  inputGroup.appendChild(input);
  container.appendChild(inputGroup);
}

/**
 * Renders a dimensions field (Width, Height, h1 in one row)
 */
function renderDimensionsField(field, container) {
  const dimensionsContainer = document.createElement('div');
  dimensionsContainer.className = 'dimensions-field-container';
  dimensionsContainer.style.cssText = 'display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 15px;';
  
  // Width input
  const widthGroup = document.createElement('div');
  widthGroup.style.cssText = 'flex: 1; min-width: 120px;';
  const widthLabel = document.createElement('label');
  widthLabel.textContent = 'Width';
  widthLabel.setAttribute('for', 'width');
  widthLabel.style.cssText = 'display: block; margin-bottom: 6px; font-size: 13px; color: #333; font-weight: 500;';
  const widthInput = document.createElement('input');
  widthInput.type = 'number';
  widthInput.id = 'width';
  widthInput.name = 'width';
  widthInput.className = 'dimension-input';
  widthInput.min = 0;
  widthInput.step = 0.1;
  widthInput.placeholder = '0';
  widthInput.style.cssText = 'width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;';
  widthGroup.appendChild(widthLabel);
  widthGroup.appendChild(widthInput);
  
  // Height input
  const heightGroup = document.createElement('div');
  heightGroup.style.cssText = 'flex: 1; min-width: 120px;';
  const heightLabel = document.createElement('label');
  heightLabel.textContent = 'Height';
  heightLabel.setAttribute('for', 'height');
  heightLabel.style.cssText = 'display: block; margin-bottom: 6px; font-size: 13px; color: #333; font-weight: 500;';
  const heightInput = document.createElement('input');
  heightInput.type = 'number';
  heightInput.id = 'height';
  heightInput.name = 'height';
  heightInput.className = 'dimension-input';
  heightInput.min = 0;
  heightInput.step = 0.1;
  heightInput.placeholder = '0';
  heightInput.style.cssText = 'width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;';
  heightGroup.appendChild(heightLabel);
  heightGroup.appendChild(heightInput);
  
  dimensionsContainer.appendChild(widthGroup);
  dimensionsContainer.appendChild(heightGroup);
  
  // h1 input (conditional)
  if (field.h1Conditional && field.h1Conditional.dependsOn) {
    const h1Group = document.createElement('div');
    h1Group.id = 'h1Group';
    h1Group.className = 'h1-dimension-group';
    h1Group.style.cssText = 'flex: 1; min-width: 120px; display: none;'; // Hidden by default
    const h1Label = document.createElement('label');
    h1Label.textContent = 'h1';
    h1Label.setAttribute('for', 'h1');
    h1Label.style.cssText = 'display: block; margin-bottom: 6px; font-size: 13px; color: #333; font-weight: 500;';
    const h1Input = document.createElement('input');
    h1Input.type = 'number';
    h1Input.id = 'h1';
    h1Input.name = 'h1';
    h1Input.className = 'dimension-input';
    h1Input.min = 0;
    h1Input.step = 0.1;
    h1Input.placeholder = '0';
    h1Input.style.cssText = 'width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;';
    h1Group.appendChild(h1Label);
    h1Group.appendChild(h1Input);
    dimensionsContainer.appendChild(h1Group);
    
    // Function to check if h1 should be shown
    const checkH1Visibility = () => {
      const dependsOnField = field.h1Conditional.dependsOn;
      const showWhen = field.h1Conditional.showWhen || [];
      
      // Find the dependency field (could be tags or other input)
      const dependsOnContainer = document.querySelector(`[data-field-id="${dependsOnField}"]`);
      if (!dependsOnContainer) return;
      
      let shouldShow = false;
      
      // Check if it's a tags field
      const activeTags = dependsOnContainer.querySelectorAll('.option-card.active');
      if (activeTags.length > 0) {
        activeTags.forEach(tag => {
          const tagValue = tag.dataset.value || tag.textContent.trim();
          if (showWhen.includes(tagValue)) {
            shouldShow = true;
          }
        });
      }
      
      h1Group.style.display = shouldShow ? 'block' : 'none';
    };
    
    // Check on page load
    setTimeout(checkH1Visibility, 100);
    
    // Listen for changes in the dependency field
    const dependsOnContainer = document.querySelector(`[data-field-id="${field.h1Conditional.dependsOn}"]`);
    if (dependsOnContainer) {
      dependsOnContainer.addEventListener('click', (e) => {
        if (e.target.closest('.option-card')) {
          setTimeout(checkH1Visibility, 50);
        }
      });
    }
    
    // Also listen to customization field change events
    document.addEventListener('customizationFieldChanged', (e) => {
      if (e.detail.fieldId === field.h1Conditional.dependsOn) {
        checkH1Visibility();
      }
    });
  }
  
  // For door products (Frameless, Swing, etc.), create additional sub-dimension inputs
  // These are for h1, h2, w1, w2, w3 customization
  const isDoorProduct = getSelectedProduct()?.category === 'Doors' || 
                       (getSelectedProduct()?.Subcategory && 
                        ['Frameless', 'Swing Door', 'Sliding', 'Bi-fold Door', 'Patch Fitting'].includes(getSelectedProduct().Subcategory));
  
  if (isDoorProduct && field.id === 'dimensions') {
    // Create a container for sub-dimensions (h1, h2, w1, w2, w3)
    const subDimensionsContainer = document.createElement('div');
    subDimensionsContainer.className = 'dimensions-container sub-dimensions-container';
    subDimensionsContainer.style.cssText = 'display: flex; gap: 15px; flex-wrap: wrap; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;';
    
    // Create h1 input (Door Height)
    const h1InputGroup = document.createElement('div');
    h1InputGroup.id = 'input-group-h1';
    h1InputGroup.className = 'hidden-step';
    h1InputGroup.style.cssText = 'flex: 1; min-width: 120px;';
    const h1Label = document.createElement('label');
    h1Label.textContent = 'Door Height (h1)';
    h1Label.style.cssText = 'display: block; margin-bottom: 6px; font-size: 13px; color: #333; font-weight: 500;';
    const h1InputField = document.createElement('input');
    h1InputField.type = 'number';
    h1InputField.id = 'input-h1';
    h1InputField.className = 'dimension-input';
    h1InputField.min = 0;
    h1InputField.step = 0.1;
    h1InputField.placeholder = '0.0';
    h1InputField.style.cssText = 'width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;';
    const h1Unit = document.createElement('span');
    h1Unit.textContent = 'Inches';
    h1Unit.style.cssText = 'display: block; margin-top: 4px; font-size: 12px; color: #666;';
    h1InputGroup.appendChild(h1Label);
    h1InputGroup.appendChild(h1InputField);
    h1InputGroup.appendChild(h1Unit);
    
    // Create h2 input (Transom Height)
    const h2InputGroup = document.createElement('div');
    h2InputGroup.id = 'input-group-h2';
    h2InputGroup.className = 'hidden-step';
    h2InputGroup.style.cssText = 'flex: 1; min-width: 120px;';
    const h2Label = document.createElement('label');
    h2Label.textContent = 'Transom Height (h2)';
    h2Label.style.cssText = 'display: block; margin-bottom: 6px; font-size: 13px; color: #333; font-weight: 500;';
    const h2InputField = document.createElement('input');
    h2InputField.type = 'number';
    h2InputField.id = 'input-h2';
    h2InputField.className = 'dimension-input';
    h2InputField.min = 0;
    h2InputField.step = 0.1;
    h2InputField.placeholder = '0.0';
    h2InputField.style.cssText = 'width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;';
    const h2Unit = document.createElement('span');
    h2Unit.textContent = 'Inches';
    h2Unit.style.cssText = 'display: block; margin-top: 4px; font-size: 12px; color: #666;';
    h2InputGroup.appendChild(h2Label);
    h2InputGroup.appendChild(h2InputField);
    h2InputGroup.appendChild(h2Unit);
    
    // Create w1 input (Door Width)
    const w1InputGroup = document.createElement('div');
    w1InputGroup.id = 'input-group-w1';
    w1InputGroup.className = 'hidden-step';
    w1InputGroup.style.cssText = 'flex: 1; min-width: 120px;';
    const w1Label = document.createElement('label');
    w1Label.textContent = 'Door Width (w1)';
    w1Label.style.cssText = 'display: block; margin-bottom: 6px; font-size: 13px; color: #333; font-weight: 500;';
    const w1InputField = document.createElement('input');
    w1InputField.type = 'number';
    w1InputField.id = 'input-w1';
    w1InputField.className = 'dimension-input';
    w1InputField.min = 0;
    w1InputField.step = 0.1;
    w1InputField.placeholder = '0.0';
    w1InputField.style.cssText = 'width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;';
    const w1Unit = document.createElement('span');
    w1Unit.textContent = 'Inches';
    w1Unit.style.cssText = 'display: block; margin-top: 4px; font-size: 12px; color: #666;';
    w1InputGroup.appendChild(w1Label);
    w1InputGroup.appendChild(w1InputField);
    w1InputGroup.appendChild(w1Unit);
    
    // Create w2 input (Left Panel Width)
    const w2InputGroup = document.createElement('div');
    w2InputGroup.id = 'input-group-w2';
    w2InputGroup.className = 'hidden-step';
    w2InputGroup.style.cssText = 'flex: 1; min-width: 120px;';
    const w2Label = document.createElement('label');
    w2Label.textContent = 'Left Panel Width (w2)';
    w2Label.style.cssText = 'display: block; margin-bottom: 6px; font-size: 13px; color: #333; font-weight: 500;';
    const w2InputField = document.createElement('input');
    w2InputField.type = 'number';
    w2InputField.id = 'input-w2';
    w2InputField.className = 'dimension-input';
    w2InputField.min = 0;
    w2InputField.step = 0.1;
    w2InputField.placeholder = '0.0';
    w2InputField.style.cssText = 'width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;';
    const w2Unit = document.createElement('span');
    w2Unit.textContent = 'Inches';
    w2Unit.style.cssText = 'display: block; margin-top: 4px; font-size: 12px; color: #666;';
    w2InputGroup.appendChild(w2Label);
    w2InputGroup.appendChild(w2InputField);
    w2InputGroup.appendChild(w2Unit);
    
    // Create w3 input (Right Panel Width)
    const w3InputGroup = document.createElement('div');
    w3InputGroup.id = 'input-group-w3';
    w3InputGroup.className = 'hidden-step';
    w3InputGroup.style.cssText = 'flex: 1; min-width: 120px;';
    const w3Label = document.createElement('label');
    w3Label.textContent = 'Right Panel Width (w3)';
    w3Label.style.cssText = 'display: block; margin-bottom: 6px; font-size: 13px; color: #333; font-weight: 500;';
    const w3InputField = document.createElement('input');
    w3InputField.type = 'number';
    w3InputField.id = 'input-w3';
    w3InputField.className = 'dimension-input';
    w3InputField.min = 0;
    w3InputField.step = 0.1;
    w3InputField.placeholder = '0.0';
    w3InputField.style.cssText = 'width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;';
    const w3Unit = document.createElement('span');
    w3Unit.textContent = 'Inches';
    w3Unit.style.cssText = 'display: block; margin-top: 4px; font-size: 12px; color: #666;';
    w3InputGroup.appendChild(w3Label);
    w3InputGroup.appendChild(w3InputField);
    w3InputGroup.appendChild(w3Unit);
    
    // Add all sub-dimension inputs to container
    subDimensionsContainer.appendChild(h1InputGroup);
    subDimensionsContainer.appendChild(h2InputGroup);
    subDimensionsContainer.appendChild(w1InputGroup);
    subDimensionsContainer.appendChild(w2InputGroup);
    subDimensionsContainer.appendChild(w3InputGroup);
    
    dimensionsContainer.appendChild(subDimensionsContainer);
    
    // Add input event listeners for sub-dimensions
    [h1InputField, h2InputField, w1InputField, w2InputField, w3InputField].forEach(input => {
      input.addEventListener('input', () => {
        // Update Konva preview
        if (typeof renderCustomState === 'function') {
          setTimeout(() => renderCustomState(), 100);
        }
      });
    });
  }
  
  // Add event listeners to update dimensions and trigger price recalculation
  [widthInput, heightInput].forEach(input => {
    input.addEventListener('input', () => {
      console.log(`🚪 [Dimensions] ${input.id} changed to:`, input.value);
      
      // Update global dimensions
      if (typeof window.currentDimensions !== 'undefined') {
        const fieldName = input.name;
        if (window.currentDimensions[fieldName]) {
          window.currentDimensions[fieldName].value = parseFloat(input.value) || 0;
        } else {
          window.currentDimensions[fieldName] = { value: parseFloat(input.value) || 0, unit: 'in' };
        }
      }
      
      // Trigger auto-calculation for door dimensions (w1, w2, w3, h1, h2)
      if (typeof window.autoDoorCalculateHeights === 'function') {
        console.log('🚪 [Dimensions] Triggering autoDoorCalculateHeights');
        window.autoDoorCalculateHeights();
      }
      if (typeof window.autoDoorCalculateWidths === 'function') {
        console.log('🚪 [Dimensions] Triggering autoDoorCalculateWidths');
        window.autoDoorCalculateWidths();
      }
      
      // Trigger price recalculation
      if (typeof updatePriceDisplay === 'function') {
        updatePriceDisplay();
      }
      
      // Trigger 2D preview update
      if (typeof renderCustomState === 'function') {
        setTimeout(() => renderCustomState(), 100);
      }
    });
  });
  
  // Also listen to h1 input if it exists
  const h1Input = dimensionsContainer.querySelector('#h1');
  if (h1Input) {
    h1Input.addEventListener('input', () => {
      if (typeof window.currentDimensions !== 'undefined') {
        window.currentDimensions.h1 = { value: parseFloat(h1Input.value) || 0, unit: 'in' };
      }
      
      if (typeof updatePriceDisplay === 'function') {
        updatePriceDisplay();
      }
      
      if (typeof renderCustomState === 'function') {
        setTimeout(() => renderCustomState(), 100);
      }
    });
  }
  
  container.appendChild(dimensionsContainer);
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
  // Hide by default since "Link All Corners" is checked by default
  individualWrapper.style.display = 'none';
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
    window.selectedCustomizationValues[fieldId] = valueInInches;
    window.selectedCustomizationValues[`${fieldId}_unit`] = currentUnit; // Store unit for reference
    logCustomizationChange(fieldId, valueInInches, 'dimension value');
    logCustomizationChange(`${fieldId}_unit`, currentUnit, 'dimension unit');
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
    
    window.selectedCustomizationValues[fieldId] = cornerValues;
    window.selectedCustomizationValues[`${fieldId}_unit`] = currentUnit; // Store unit for reference
    logCustomizationChange(fieldId, cornerValues, 'corner radius');
    logCustomizationChange(`${fieldId}_unit`, currentUnit, 'corner unit');
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
  // Show for rectangle and square only (per CUSTOMIZATION_REFERENCE.md)
  // Note: Arched shape does NOT show corner radius controls
  const rectangleShapes = ['rectangle', 'rectangular', 'square', 'rectangle/square', 'rectangle-square'];
  const isRectangleShape = rectangleShapes.includes(shape) || 
                           shape.includes('rectangle') || 
                           shape.includes('square');
  
  if (isRectangleShape) {
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
  
  // Show only if rectangle or square is selected (per CUSTOMIZATION_REFERENCE.md)
  // Note: Arched shape does NOT show corner radius controls
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
 * Frame Color field shows different options based on Frame Type:
 * - Framed: White, Black, Gold
 * - Frameless: Machine Polished Edges, Beveled Edge
 */
function handleMirrorsConditionals(changedFieldId, selectedValue) {
  if (changedFieldId === 'frameType') {
    const frameColorContainer = document.querySelector('[data-field-id="frameColor"]');
    const edgeFinishContainer = document.querySelector('[data-field-id="edgeFinish"]');
    
    // Check for "Standard Frame" (the actual option value) - per CUSTOMIZATION_REFERENCE.md
    const normalizedValue = (selectedValue || '').toLowerCase().trim();
    const isStandardFrame = normalizedValue === 'standard frame' || 
                           normalizedValue === 'standard-frame' || 
                           normalizedValue === 'framed' ||
                           (selectedValue && selectedValue.toLowerCase().includes('standard') && selectedValue.toLowerCase().includes('frame'));
    const isFrameless = normalizedValue === 'frameless';
    
    // Filter frameColor options based on Frame Type
    // If frameless: Only show "Machine Polished Edges" and "Beveled Edge"
    // If framed: Only show "White", "Black", "Gold"
    if (frameColorContainer) {
      const tagContainer = frameColorContainer.querySelector('.tag-container, [id$="Container"]');
      if (tagContainer) {
        const allOptionCards = tagContainer.querySelectorAll('.option-card');
        const frameOptions = ['white', 'black', 'gold'];
        const edgeOptions = ['machine polished edges', 'beveled edge'];
        
        allOptionCards.forEach(card => {
          const optionValue = (card.dataset.value || card.textContent || '').trim();
          const normalizedOption = optionValue.toLowerCase().replace(/\s+/g, ' ');
          
          if (isFrameless) {
            // Show only edge options, hide frame options
            const isEdgeOption = edgeOptions.some(opt => {
              const normalizedOpt = opt.toLowerCase();
              return normalizedOption === normalizedOpt || 
                     normalizedOption.includes('polished') || 
                     normalizedOption.includes('beveled');
            });
            card.style.display = isEdgeOption ? '' : 'none';
            
            // Clear selection if it's a frame option
            if (!isEdgeOption && card.classList.contains('active')) {
              card.classList.remove('active');
              delete window.selectedCustomizationValues['frameColor'];
            }
          } else if (isStandardFrame) {
            // Show only frame options, hide edge options
            const isFrameOption = frameOptions.some(opt => {
              const normalizedOpt = opt.toLowerCase();
              return normalizedOption === normalizedOpt || 
                     normalizedOption.startsWith(normalizedOpt);
            });
            card.style.display = isFrameOption ? '' : 'none';
            
            // Clear selection if it's an edge option
            if (!isFrameOption && card.classList.contains('active')) {
              card.classList.remove('active');
              delete window.selectedCustomizationValues['frameColor'];
            }
          } else {
            // If frameType is not set, show all options (default state)
            card.style.display = '';
          }
        });
      }
      
      // Show the field section (don't hide it, just filter options)
      let fieldSection = frameColorContainer.closest('.field-section, .type-section, .frame-section');
      if (!fieldSection) {
        fieldSection = frameColorContainer.parentElement;
      }
      if (fieldSection) {
        fieldSection.style.display = '';
      }
    }
    
    // Show/hide Edge Finish based on Frame Type (if it exists as separate field)
    // Edge Finish only appears when Frame Type = "Frameless"
    if (edgeFinishContainer) {
      // Try multiple selectors to find the parent section
      let fieldSection = edgeFinishContainer.closest('.field-section, .edge-section');
      if (!fieldSection) {
        // If not found, try parent element
        fieldSection = edgeFinishContainer.parentElement;
      }
      
      if (fieldSection) {
        if (isFrameless) {
          fieldSection.style.display = '';
        } else {
          fieldSection.style.display = 'none';
          // Clear selection when hidden
          const activeCard = edgeFinishContainer.querySelector('.option-card.active');
          if (activeCard) {
            activeCard.classList.remove('active');
            delete window.selectedCustomizationValues['edgeFinish'];
          }
        }
      } else {
        // Fallback: hide the container directly
        edgeFinishContainer.style.display = isFrameless ? '' : 'none';
      }
    }
  }
}

/**
 * Handle conditional logic for Windows_Sliding fields
 */
/**
 * Toggle visibility of h1 (inner height) input field based on transom selection
 * @param {string} transomValue - The selected transom type value
 */
function toggleH1InputVisibility(transomValue) {
  const h1InputGroup = document.getElementById('input-group-h1');
  const h1Input = document.getElementById('input-h1');
  const btnUnitH1 = document.getElementById('btn-unit-h1');
  const h2InputGroup = document.getElementById('input-group-h2');
  const h2Input = document.getElementById('input-h2');
  const btnUnitH2 = document.getElementById('btn-unit-h2');
  
  if (!h1InputGroup || !h1Input || !h2InputGroup || !h2Input) {
    console.warn('[Transom Inputs] Elements not found:', { 
      h1InputGroup: !!h1InputGroup, 
      h1Input: !!h1Input,
      h2InputGroup: !!h2InputGroup,
      h2Input: !!h2Input
    });
    return;
  }
  
  const hasTransom = transomValue && transomValue.toLowerCase() !== 'none';
  console.log('[Transom Inputs] Toggling visibility:', { transomValue, hasTransom });
  
  if (hasTransom) {
    // Show both h1 and h2 inputs
    h1InputGroup.classList.remove('hidden-step');
    h1InputGroup.style.display = '';
    h2InputGroup.classList.remove('hidden-step');
    h2InputGroup.style.display = '';
    
    // Get total height
    const inputHeight = document.getElementById('input-height');
    const btnUnitHeight = document.getElementById('btn-unit-height');
    const heightUnit = btnUnitHeight ? btnUnitHeight.getAttribute('data-current-unit') || 'in' : 'in';
    const totalHeight = inputHeight ? parseFloat(inputHeight.value) || 45 : 45;
    
    // Calculate default values if inputs are empty
    if (!h1Input.value || h1Input.value === '') {
      const h1Value = totalHeight * 0.7; // 70% of total height (sliding section)
      h1Input.value = h1Value.toFixed(1);
    }
    
    if (!h2Input.value || h2Input.value === '') {
      const h2Value = totalHeight * 0.3; // 30% of total height (fixed transom)
      h2Input.value = h2Value.toFixed(1);
    }
    
    // Sync units with height unit and use full unit names
    const unitMapLocal = {
      'in': 'Inches',
      'cm': 'Centimeters',
      'mm': 'Millimeters'
    };
    const unitName = unitMapLocal[heightUnit] || 'Inches';
    
    if (btnUnitH1) {
      btnUnitH1.setAttribute('data-current-unit', heightUnit);
      btnUnitH1.innerHTML = `${unitName} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>`;
    }
    
    if (btnUnitH2) {
      btnUnitH2.setAttribute('data-current-unit', heightUnit);
      btnUnitH2.innerHTML = `${unitName} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>`;
    }
    
    // Auto-adjust h2 based on h1 and total height
    adjustTransomHeights();
    
    console.log('[Transom Inputs] ✅ Shown with values:', { h1: h1Input.value, h2: h2Input.value });
  } else {
    // Hide both inputs
    h1InputGroup.classList.add('hidden-step');
    h1InputGroup.style.display = 'none';
    h1Input.value = '';
    h2InputGroup.classList.add('hidden-step');
    h2InputGroup.style.display = 'none';
    h2Input.value = '';
    console.log('[Transom Inputs] ❌ Hidden');
  }
}

/**
 * Auto-adjust transom heights: h1 + h2 = totalHeight
 * When one changes, adjust the other to maintain total height
 */
function adjustTransomHeights() {
  const inputHeight = document.getElementById('input-height');
  const btnUnitHeight = document.getElementById('btn-unit-height');
  const h1Input = document.getElementById('input-h1');
  const btnUnitH1 = document.getElementById('btn-unit-h1');
  const h2Input = document.getElementById('input-h2');
  const btnUnitH2 = document.getElementById('btn-unit-h2');
  
  if (!inputHeight || !h1Input || !h2Input) return;
  
  const totalHeight = parseFloat(inputHeight.value) || 0;
  if (totalHeight <= 0) return; // Don't adjust if total height is invalid
  
  const heightUnit = btnUnitHeight ? btnUnitHeight.getAttribute('data-current-unit') || 'in' : 'in';
  const h1Value = parseFloat(h1Input.value) || 0;
  const h2Value = parseFloat(h2Input.value) || 0;
  const h1Unit = btnUnitH1 ? btnUnitH1.getAttribute('data-current-unit') || heightUnit : heightUnit;
  const h2Unit = btnUnitH2 ? btnUnitH2.getAttribute('data-current-unit') || heightUnit : heightUnit;
  
  // Helper function to convert to mm (if not available globally)
  const convertToMmLocal = (value, unit) => {
    const unitMapLocal = {
      'in': { toMm: 25.4 },
      'cm': { toMm: 10 },
      'mm': { toMm: 1 }
    };
    const unitInfo = unitMapLocal[unit?.toLowerCase()] || unitMapLocal['in'];
    return value * unitInfo.toMm;
  };
  
  // Convert all to same unit (mm) for calculation
  const convertToMmFn = typeof convertToMm === 'function' ? convertToMm : convertToMmLocal;
  const totalHeightMm = convertToMmFn(totalHeight, heightUnit);
  const h1Mm = h1Value > 0 ? convertToMmFn(h1Value, h1Unit) : 0;
  const h2Mm = h2Value > 0 ? convertToMmFn(h2Value, h2Unit) : 0;
  
  // Unit map for conversion back
  const unitMapLocal = {
    'in': { toMm: 25.4 },
    'cm': { toMm: 10 },
    'mm': { toMm: 1 }
  };
  const getUnitToMm = (unit) => unitMapLocal[unit?.toLowerCase()]?.toMm || 25.4;
  
  // Calculate sum and difference
  const currentSum = h1Mm + h2Mm;
  const difference = totalHeightMm - currentSum;
  
  // Determine which input was last changed
  const h1LastModified = parseFloat(h1Input.dataset.lastModified) || 0;
  const h2LastModified = parseFloat(h2Input.dataset.lastModified) || 0;
  const heightLastModified = parseFloat(inputHeight.dataset.lastModified) || 0;
  
  // If total height was changed, maintain the ratio of h1 and h2
  if (heightLastModified > Math.max(h1LastModified, h2LastModified)) {
    // Total height changed - maintain ratio
    if (h1Mm > 0 && h2Mm > 0) {
      // Both have values, maintain ratio
      const h1Ratio = h1Mm / currentSum;
      const h2Ratio = h2Mm / currentSum;
      const newH1Mm = totalHeightMm * h1Ratio;
      const newH2Mm = totalHeightMm * h2Ratio;
      h1Input.value = Math.max(0.1, (newH1Mm / getUnitToMm(h1Unit)).toFixed(1));
      h2Input.value = Math.max(0.1, (newH2Mm / getUnitToMm(h2Unit)).toFixed(1));
    } else if (h1Mm > 0) {
      // Only h1 has value, calculate h2
      const newH2Mm = totalHeightMm - h1Mm;
      h2Input.value = Math.max(0.1, (newH2Mm / getUnitToMm(h2Unit)).toFixed(1));
    } else if (h2Mm > 0) {
      // Only h2 has value, calculate h1
      const newH1Mm = totalHeightMm - h2Mm;
      h1Input.value = Math.max(0.1, (newH1Mm / getUnitToMm(h1Unit)).toFixed(1));
    } else {
      // Neither has value, use default ratios
      const newH1Mm = totalHeightMm * 0.7;
      const newH2Mm = totalHeightMm * 0.3;
      h1Input.value = Math.max(0.1, (newH1Mm / getUnitToMm(h1Unit)).toFixed(1));
      h2Input.value = Math.max(0.1, (newH2Mm / getUnitToMm(h2Unit)).toFixed(1));
    }
  } else if (Math.abs(difference) > 1) {
    // One of h1 or h2 was changed, adjust the other
    if (h1LastModified > h2LastModified && h1Mm > 0) {
      // h1 was changed, adjust h2
      const newH2Mm = totalHeightMm - h1Mm;
      if (newH2Mm > 0) {
        h2Input.value = (newH2Mm / getUnitToMm(h2Unit)).toFixed(1);
      }
    } else if (h2LastModified > h1LastModified && h2Mm > 0) {
      // h2 was changed, adjust h1
      const newH1Mm = totalHeightMm - h2Mm;
      if (newH1Mm > 0) {
        h1Input.value = (newH1Mm / getUnitToMm(h1Unit)).toFixed(1);
      }
    } else if (h1Mm > 0 && h2Mm === 0) {
      // Only h1 has value, calculate h2
      const newH2Mm = totalHeightMm - h1Mm;
      if (newH2Mm > 0) {
        h2Input.value = (newH2Mm / getUnitToMm(h2Unit)).toFixed(1);
      }
    } else if (h2Mm > 0 && h1Mm === 0) {
      // Only h2 has value, calculate h1
      const newH1Mm = totalHeightMm - h2Mm;
      if (newH1Mm > 0) {
        h1Input.value = (newH1Mm / getUnitToMm(h1Unit)).toFixed(1);
      }
    }
  }
}

function handleWindowsSlidingConditionals(changedFieldId, selectedValue) {
  // Rule 1: Track System depends on Transom Type
  // Handle both field ID variations: 'transomType' and 'transomTypeTopBottomFixedPanel'
  const isTransomField = changedFieldId === 'transomType' || changedFieldId === 'transomTypeTopBottomFixedPanel';
  
  if (isTransomField) {
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
              window.selectedCustomizationValues['trackSystem'] = '2 Tracks';
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
    
    // Show/hide h1 input field based on transom selection
    toggleH1InputVisibility(selectedValue);
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
      
      // No spec tags selected by default - do not auto-select
    }
  }
}

/**
 * Handle conditional logic for Awning windows fields
 * Rows and Columns fields show when Size Configuration = "Multiple panels"
 */
function handleAwningConditionals(changedFieldId, selectedValue) {
  if (changedFieldId === 'sizeConfiguration') {
    const rowsContainer = document.querySelector('[data-field-id="panelRows"]');
    const columnsContainer = document.querySelector('[data-field-id="panelColumns"]');
    
    const isMultiplePanels = (selectedValue || '').toLowerCase().includes('multiple');
    
    // Show/hide rows field - rowsContainer is the fieldGroup (field-section) itself
    if (rowsContainer) {
      if (isMultiplePanels) {
        rowsContainer.style.display = '';
      } else {
        rowsContainer.style.display = 'none';
        // Reset value when hidden
        const input = rowsContainer.querySelector('input[type="number"]');
        if (input) {
          input.value = '1';
          window.selectedCustomizationValues['panelRows'] = 1;
        }
      }
    }
    
    // Show/hide columns field - columnsContainer is the fieldGroup (field-section) itself
    if (columnsContainer) {
      if (isMultiplePanels) {
        columnsContainer.style.display = '';
      } else {
        columnsContainer.style.display = 'none';
        // Reset value when hidden
        const input = columnsContainer.querySelector('input[type="number"]');
        if (input) {
          input.value = '1';
          window.selectedCustomizationValues['panelColumns'] = 1;
        }
      }
    }
    
    // Update Konva visualization
    if (typeof window !== 'undefined' && typeof window.updateKonvaFromField === 'function') {
      const rows = isMultiplePanels ? (window.selectedCustomizationValues['panelRows'] || 1) : 1;
      const cols = isMultiplePanels ? (window.selectedCustomizationValues['panelColumns'] || 1) : 1;
      updateKonvaFromField('panelRows', rows, true);
      updateKonvaFromField('panelColumns', cols, true);
    }
  }
}

/**
 * Update Screen tags field availability based on Track System
 * Note: Both 2-track and 3-track systems support screens.
 * 3-track systems are specifically designed with a dedicated track for the screen.
 */
function updateScreenAvailability() {
  const trackSystemContainer = document.querySelector('[data-field-id="trackSystem"]');
  const screenContainer = document.querySelector('[data-field-id="screen"]');
  
  if (!trackSystemContainer || !screenContainer) return;
  
  const screenOptions = screenContainer.querySelectorAll('.option-card');
  
  if (screenOptions.length > 0) {
    // Enable all screen options for both 2-track and 3-track systems
    // 3-track systems have a dedicated third track for the screen
    screenOptions.forEach(option => {
      option.style.opacity = '';
      option.style.pointerEvents = '';
      option.classList.remove('disabled');
    });
    
    // Remove any conditional message if it exists
    const messageEl = screenContainer.querySelector('.conditional-message');
    if (messageEl) {
      messageEl.remove();
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
  console.log(`updateKonvaFromField called: fieldId="${fieldId}", value="${value}", isActive=${isActive}`);

  // Map field IDs to Konva parameters
  const fieldMapping = {
    'glassType': 'glassType',
    'glassColor': 'glassColor',
    // Frame color/material should remain available to the comprehensive renderer as `frameColor`.
    // We still update the legacy `currentFrameType` so the old renderer path remains compatible.
    'frameColor': 'frameColor',
    'frameFinishColor': 'frameColor', // Product catalog field name
    'thickness': 'thickness',
    'glassThickness': 'thickness',
    'screen': 'screen',
    'size': 'dimensions',
    'handleType': 'handle',
    'lockType': 'lock',
    'softClose': 'softClose',
    'layout': 'layout',
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
    'panelRows': 'panelRows',
    'PanelRows': 'panelRows',
    'panelColumns': 'panelColumns',
    'PanelColumns': 'panelColumns',
    'sizeConfiguration': 'sizeConfiguration',
    'SizeConfiguration': 'sizeConfiguration',
    'operation': 'operation',
    'Operation': 'operation',
    'configuration': 'configuration',
    'Configuration': 'configuration',
    'doorSwing': 'doorSwing',
    'DoorSwing': 'doorSwing',
    'hingeSide': 'hingeSide',
    'HingeSide': 'hingeSide',
    // Door sub-dimension inputs (h1, h2, w1, w2, w3)
    'input-h1': 'h1',
    'input-h2': 'h2',
    'input-w1': 'w1',
    'input-w2': 'w2',
    'input-w3': 'w3',
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
  
  // Always store the value in selectedCustomizationValues, even if not in mapping
  // This ensures all fields are available for the comprehensive renderer
  if (isActive) {
    window.selectedCustomizationValues[fieldId] = value;
    logCustomizationChange(fieldId, value, 'updateKonvaFromField');
  } else {
    if (window.selectedCustomizationValues[fieldId] === value) {
      delete window.selectedCustomizationValues[fieldId];
      logCustomizationChange(fieldId, null, 'updateKonvaFromField (deleted)');
    }
  }
  
  // Special handling for awning window panel configuration fields
  // These fields need to trigger re-render to update the panel grid visualization
  if (fieldId === 'panelRows' || fieldId === 'panelColumns' || fieldId === 'sizeConfiguration') {
    // Always trigger re-render for these fields to update the panel grid
    if (typeof window !== 'undefined' && typeof window.renderCustomState === 'function') {
      setTimeout(() => {
        window.renderCustomState();
      }, 50);
    }
    // Continue to also process through normal flow if there's a mapping
    if (!konvaParam) {
      return;
    }
  }
  
  // If no konvaParam mapping, still trigger re-render for comprehensive renderer
  if (!konvaParam) {
    // Trigger re-render to update Konva with new customization values
    if (typeof window !== 'undefined' && typeof window.renderCustomState === 'function') {
      setTimeout(() => {
        window.renderCustomState();
      }, 50);
    }
    return;
  }

  // Store selected value
  // For tag fields (single-select), store as single value
  // For number fields, store as single value
  // For other fields that might support multi-select, use array
  const fieldElement = document.querySelector(`[data-field-id="${fieldId}"]`);
  const isTagField = fieldElement !== null;
  const isNumberField = fieldElement && fieldElement.querySelector('input[type="number"]') !== null;
  const isCheckboxField = fieldElement && fieldElement.querySelector('input[type="checkbox"]') !== null;
  
  if (isTagField || isNumberField) {
    // Single-select (tags) or number fields: store as single value
    if (isActive) {
      window.selectedCustomizationValues[fieldId] = value;
    } else {
      // If deselecting and it's the current value, clear it
      if (window.selectedCustomizationValues[fieldId] === value) {
        delete window.selectedCustomizationValues[fieldId];
      }
    }
  } else if (isCheckboxField) {
    // Multi-select: use array (for checkboxes, etc.)
    if (!window.selectedCustomizationValues[fieldId]) {
      window.selectedCustomizationValues[fieldId] = [];
    }
    
    if (isActive) {
      if (!window.selectedCustomizationValues[fieldId].includes(value)) {
        window.selectedCustomizationValues[fieldId].push(value);
      }
    } else {
      window.selectedCustomizationValues[fieldId] = window.selectedCustomizationValues[fieldId].filter(v => v !== value);
    }
  } else {
    // Fallback: store as single value for unknown field types
    if (isActive) {
      window.selectedCustomizationValues[fieldId] = value;
    } else {
      if (window.selectedCustomizationValues[fieldId] === value) {
        delete window.selectedCustomizationValues[fieldId];
      }
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
    } else if (konvaParam === 'glassColor') {
      // Store glass color for comprehensive renderer
      // The comprehensive renderer uses glassColor along with glassType to determine visual style
      // No need to update a global variable, just ensure it's in selectedCustomizationValues
      // The renderCustomState function will pass it to the comprehensive renderer
    } else if (konvaParam === 'frameColor') {
      // Frame Color/Material: keep the human-readable string for the comprehensive renderer,
      // but also update legacy `currentFrameType` (used by the older renderer paths).
      const normalizedValue = (value || '').toString().toLowerCase().trim().replace(/\s+/g, '-');
      if (window.currentFrameType !== undefined) {
        window.currentFrameType = normalizedValue;
      }
    } else if (konvaParam === 'thickness') {
      // Handle both thickness and glassThickness fields
      let thicknessValue = value;
      // If value doesn't already include 'mm', add it
      if (!thicknessValue.includes('mm') && !thicknessValue.includes('cm') && !thicknessValue.includes('in')) {
        thicknessValue = thicknessValue + 'mm';
      }
      // Update both window.currentThickness and selectedCustomizationValues
      if (window.currentThickness !== undefined) {
        window.currentThickness = thicknessValue;
      }
      // Ensure selectedCustomizationValues also has the normalized value
      if (isActive) {
        window.selectedCustomizationValues[fieldId] = thicknessValue;
        logCustomizationChange(fieldId, thicknessValue, 'thickness handler');
      }
    } else if (konvaParam === 'screen') {
      // Screen field - store in selectedCustomizationValues for comprehensive renderer
      // The comprehensive renderer will use this to draw screen pattern overlay
      // No need to update a global variable, just ensure it's in selectedCustomizationValues
    } else if (konvaParam === 'edgeWork') {
      const edgeValue = value.toLowerCase().replace(/\s+/g, '-');
      if (window.currentEdgeWork !== undefined) {
        window.currentEdgeWork = edgeValue;
      }
    } else if (konvaParam === 'shape') {
      const shapeValue = value.toLowerCase().replace(/\s+/g, '-');
      if (window.currentShape !== undefined) {
        window.currentShape = shapeValue;
      }
      
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
    }
    else if (konvaParam === 'cornerRadius') {
      // Corner radius for rectangle/square (in inches)
      if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
        if (window.currentCornerRadius !== undefined) {
          window.currentCornerRadius = value;
        }
        if (window.cornerRadiusLinked !== undefined) {
          window.cornerRadiusLinked = false;
        }
      } else {
        const radiusIn = parseFloat(value) || 0;
        if (window.currentCornerRadius !== undefined) {
          window.currentCornerRadius = radiusIn;
        }
        if (window.cornerRadiusLinked !== undefined) {
          window.cornerRadiusLinked = true;
        }
      }
    } else if (konvaParam === 'numberOfPanels' || konvaParam === 'operation' || konvaParam === 'configuration') {
      // Multi-panel fields - store in selectedCustomizationValues for renderWindow to use
      // These will be automatically picked up by renderWindow when it checks shouldUseMultiPanelRendering
      // No need to update individual state variables, just ensure the value is stored
    }
  }

  // Note: window.selectedCustomizationValues already updated directly above (Proxy preserved)

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
      // No spec tags selected by default - do not auto-select first standard size
      
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
 * DISABLED: Price is now static and shows the product's price range from database
 */
function updatePriceForStandardSize(price) {
  // Price display is now static - do not update
  // const totalPriceEl = document.getElementById('total-price');
  // if (totalPriceEl) {
  //   totalPriceEl.textContent = `₱${parseFloat(price).toFixed(2)}`;
  // }
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
// Helper function to ensure there's always a chevron before Review Order
function ensureChevronBeforeReviewOrder() {
  const crumbReview = document.getElementById('crumb-review');
  const breadcrumbsContainer = document.getElementById('breadcrumbs-container');

  if (!crumbReview || !breadcrumbsContainer) return;

  // Remove any existing chevron before Review Order
  const existingChevron = document.getElementById('chevron-crumb-review');
  if (existingChevron) {
    existingChevron.remove();
  }

  // Add a new chevron before Review Order
  const finalChevron = document.createElement('span');
  finalChevron.className = 'chevron-right';
  finalChevron.id = 'chevron-crumb-review';
  breadcrumbsContainer.insertBefore(finalChevron, crumbReview);
}

function updateDynamicBreadcrumbs(currentStep, totalSteps, stepNames = null) {
  const crumbStep1 = document.getElementById('crumb-step1');
  const crumbStep2 = document.getElementById('crumb-step2');
  const crumbReview = document.getElementById('crumb-review');
  const breadcrumbsContainer = document.getElementById('breadcrumbs-container');

  // Get all chevron elements
  const chevronStep1 = crumbStep1 ? crumbStep1.nextElementSibling : null;
  const chevronStep2 = crumbStep2 ? crumbStep2.nextElementSibling : null;

  if (!crumbStep1 || !crumbStep2 || !crumbReview || !breadcrumbsContainer) return;

  // All orders now follow unified flow (site-assessment process)
  // Order type check removed - all products treated the same

  // Clear all dynamic steps between Step 2 and Review Order
  const dynamicCrumbs = breadcrumbsContainer.querySelectorAll('[id^="crumb-step"]:not(#crumb-step1):not(#crumb-step2), [id^="chevron-crumb-step"]');
  dynamicCrumbs.forEach(crumb => crumb.remove());

  // Set Step 1 breadcrumb
  const step1Name = stepNames && stepNames['1'] ? stepNames['1'] : 'Step 1';
  crumbStep1.textContent = step1Name;

  // Set Step 2 breadcrumb
  const step2Name = stepNames && stepNames['2'] ? stepNames['2'] : 'Step 2';
  crumbStep2.textContent = step2Name;

  // Reset visibility for Step 1 and Step 2
  crumbStep1.style.display = '';
  crumbStep2.style.display = '';
  if (chevronStep1) chevronStep1.style.display = '';

  // Handle navigation states - Review Order is always the final step
  if (currentStep === 1) {
    // Step 1: Products → Step 1
    crumbStep1.classList.add('active');
    crumbStep2.classList.remove('active');
    crumbReview.classList.remove('active');

    // Hide Step 2 and Review Order
    crumbStep2.style.display = 'none';
    crumbReview.style.display = 'none';
    if (chevronStep1) chevronStep1.style.display = 'none';
    if (chevronStep2) chevronStep2.style.display = 'none';

  } else if (currentStep === 2) {
    // Step 2: Products → Step 1 → Step 2
    crumbStep1.classList.remove('active');
    crumbStep2.classList.add('active');
    crumbReview.classList.remove('active');

    // Hide Review Order
    crumbReview.style.display = 'none';
    if (chevronStep2) chevronStep2.style.display = 'none';

  } else if (currentStep === totalSteps) {
    // Final Step: Show all customization steps, but Review Order depends on order type
    crumbStep1.classList.remove('active');
    crumbStep2.classList.remove('active');

    // Add all intermediate steps (Step 3, Step 4, etc.) between Step 2 and Review Order
    // Hide the static chevron and rebuild the connection
    if (chevronStep2) chevronStep2.style.display = 'none';

    // Start inserting after Step 2
    let insertAfter = crumbStep2;

    for (let step = 3; step <= currentStep; step++) {
      const stepName = stepNames && stepNames[String(step)]
        ? stepNames[String(step)]
        : `Step ${step}`;

      // Create chevron for this step
      const chevron = document.createElement('span');
      chevron.className = 'chevron-right';
      chevron.id = `chevron-crumb-step${step}`;

      // Create crumb
      const crumb = document.createElement('span');
      crumb.id = `crumb-step${step}`;
      crumb.textContent = stepName;
      // Only highlight the current active step
      if (step === currentStep) {
        crumb.classList.add('active');
      }

      // Insert chevron and crumb after the previous element
      insertAfter.insertAdjacentElement('afterend', chevron);
      chevron.insertAdjacentElement('afterend', crumb);

      // Next insertion point is after this crumb
      insertAfter = crumb;
    }

    // Manage Review Order visibility and chevron
    const isSiteOrderMode = typeof isSiteOrder !== 'undefined' ? isSiteOrder : false;
    if (!isSiteOrderMode) {
      // Direct orders: Show Review Order
      crumbReview.classList.add('active');
      crumbReview.style.display = '';

      // Always ensure there's a chevron connecting to Review Order
      ensureChevronBeforeReviewOrder();
    } else {
      // Site orders: Hide Review Order
      crumbReview.classList.remove('active');
      crumbReview.style.display = 'none';
    }

  } else {
    // Additional Steps: Products → Step 1 → Step 2 → Step 3 → ...
    crumbStep1.classList.remove('active');
    crumbStep2.classList.remove('active');
    crumbReview.classList.remove('active');

    // Hide Review Order
    crumbReview.style.display = 'none';
    if (chevronStep2) chevronStep2.style.display = 'none';

    // Add breadcrumbs for steps up to current step
    // Hide the static chevron and rebuild the connection
    if (chevronStep2) chevronStep2.style.display = 'none';

    // Start inserting after Step 2
    let insertAfter = crumbStep2;

    for (let step = 3; step <= currentStep; step++) {
      const stepName = stepNames && stepNames[String(step)]
        ? stepNames[String(step)]
        : `Step ${step}`;

      // Create chevron
      const chevron = document.createElement('span');
      chevron.className = 'chevron-right';
      chevron.id = `chevron-crumb-step${step}`;

      // Create crumb
      const crumb = document.createElement('span');
      crumb.id = `crumb-step${step}`;
      crumb.textContent = stepName;
      if (step === currentStep) {
        crumb.classList.add('active');
      }

      // Insert chevron and crumb after the previous element
      insertAfter.insertAdjacentElement('afterend', chevron);
      chevron.insertAdjacentElement('afterend', crumb);

      // Next insertion point is after this crumb
      insertAfter = crumb;
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
  
  // Sync glass color
  const glassColorContainer = document.querySelector('[data-field-id="glassColor"]');
  if (glassColorContainer) {
    const activeCard = glassColorContainer.querySelector('.option-card.active');
    if (activeCard) {
      const value = activeCard.dataset.value || activeCard.textContent.trim();
      console.log('[Sync] Found active glass color:', value);
      selectedCustomizationValues['glassColor'] = value;
    }
  }
  
  // Sync screen
  const screenContainer = document.querySelector('[data-field-id="screen"]');
  if (screenContainer) {
    const activeCard = screenContainer.querySelector('.option-card.active');
    if (activeCard) {
      const value = activeCard.dataset.value || activeCard.textContent.trim();
      console.log('[Sync] Found active screen:', value);
      selectedCustomizationValues['screen'] = value;
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
  
  // Note: window.selectedCustomizationValues already updated (Proxy preserved)
  console.log('[Sync] State sync complete. Current values:', {
    shape: window.currentShape,
    glassType: window.currentGlassType,
    glassColor: selectedCustomizationValues['glassColor'],
    glassThickness: selectedCustomizationValues['glassThickness'] || selectedCustomizationValues['thickness'],
    screen: selectedCustomizationValues['screen'],
    frameType: window.currentFrameType,
    thickness: window.currentThickness,
    edgeWork: window.currentEdgeWork
  });
  
  // Trigger updateKonvaFromField for all active options to ensure Konva reflects initial state
  // This fixes the bug where first selected button doesn't appear in Konva on initial load
  const allFieldContainers = document.querySelectorAll('[data-field-id]');
  allFieldContainers.forEach(fieldContainer => {
    const fieldId = fieldContainer.dataset.fieldId;
    const activeCard = fieldContainer.querySelector('.option-card.active');
    if (activeCard && fieldId) {
      const activeValue = activeCard.dataset.value || activeCard.textContent.trim();
      // Update selectedCustomizationValues
      selectedCustomizationValues[fieldId] = activeValue;
      // Trigger Konva update for this field
      setTimeout(() => {
        updateKonvaFromField(fieldId, activeValue, true);
      }, 10);
    }
  });
  
  // Re-render Konva with synced state
  setTimeout(() => {
    if (typeof renderCustomState === 'function') {
      renderCustomState();
    } else if (typeof window.renderCustomState === 'function') {
      window.renderCustomState();
    }
  }, 100);
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
window.adjustTransomHeights = adjustTransomHeights;
window.toggleH1InputVisibility = toggleH1InputVisibility;
window.autoDoorCalculateHeights = autoDoorCalculateHeights;
window.autoDoorCalculateWidths = autoDoorCalculateWidths;
window.getDoorTotalDimensions = getDoorTotalDimensions;
window.getDoorFixedPanelsOption = getDoorFixedPanelsOption;

// MutationObserver disabled to avoid interfering with user clicks
// The click handler itself ensures only one option is active
