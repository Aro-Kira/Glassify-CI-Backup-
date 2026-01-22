// =====================================================
// PRODUCTS.JS
// =====================================================

// -------------------- TOAST NOTIFICATION --------------------
function showToast(message, type = 'info', duration = 3000) {
  const toast = document.createElement('div');
  toast.className = 'toast-notification';
  toast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === 'error' ? '#d9534f' : type === 'success' ? '#00B521' : '#005b82'};
    color: white;
    padding: 12px 20px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    z-index: 10000;
    animation: toastSlideIn 0.3s ease;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    max-width: 400px;
  `;
  toast.textContent = message;
  document.body.appendChild(toast);
  
  setTimeout(() => {
    toast.style.animation = 'toastFadeOut 0.3s ease forwards';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// Add toast animations if not already in styles
if (!document.getElementById('toast-animations')) {
  const style = document.createElement('style');
  style.id = 'toast-animations';
  style.textContent = `
    @keyframes toastSlideIn {
      from { transform: translateX(400px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes toastFadeOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(400px); opacity: 0; }
    }
  `;
  document.head.appendChild(style);
}

// -------------------- MULTIPLE IMAGE UPLOAD --------------------
let uploadedImages = {
  add: [], // Store File objects for add popup
  edit: []  // Store File objects for edit popup
};

/**
 * Setup multiple image upload with drag & drop
 */
function setupMultipleImageUpload(inputId, dropzoneId, previewGridId, countIndicatorId, mode = 'add') {
  const input = document.getElementById(inputId);
  const dropzone = document.getElementById(dropzoneId);
  const previewGrid = document.getElementById(previewGridId);
  const countIndicator = document.getElementById(countIndicatorId);

  if (!input || !dropzone || !previewGrid) return;

  // Reset images array
  uploadedImages[mode] = [];

  // Click on dropzone to trigger file input
  dropzone.addEventListener('click', () => input.click());

  // File input change handler
  input.addEventListener('change', (e) => {
    handleFiles(Array.from(e.target.files), previewGrid, countIndicator, mode);
  });

  // Drag & drop handlers
  dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('drag-over');
  });

  dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('drag-over');
  });

  dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('drag-over');
    const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
    handleFiles(files, previewGrid, countIndicator, mode);
  });

  // Update indicator on initialization
  updateImageCount(countIndicator, uploadedImages[mode].length);
}

/**
 * Handle file selection and create previews
 */
function handleFiles(files, previewGrid, countIndicator, mode) {
  const MAX_IMAGES = 10;
  
  files.forEach(file => {
    if (!file.type.startsWith('image/')) {
      showToast(`${file.name} is not an image file.`, 'error');
      return;
    }

    // Check if file already exists
    if (uploadedImages[mode].some(f => f.name === file.name && f.size === file.size)) {
      return; // Skip duplicates
    }

    // Check maximum limit before adding
    if (uploadedImages[mode].length >= MAX_IMAGES) {
      showToast(`Maximum ${MAX_IMAGES} images allowed per product.`, 'error');
      return;
    }

    uploadedImages[mode].push(file);

    // Create preview item
    const reader = new FileReader();
    reader.onload = (e) => {
      const previewItem = createPreviewItem(e.target.result, uploadedImages[mode].length - 1, mode);
      previewGrid.appendChild(previewItem);
      updateImageCount(countIndicator, uploadedImages[mode].length);
    };
    reader.readAsDataURL(file);
  });
}

/**
 * Create preview item element
 */
function createPreviewItem(imageSrc, index, mode) {
  const item = document.createElement('div');
  item.className = 'image-preview-item';
  item.dataset.index = index;

  const img = document.createElement('img');
  img.src = imageSrc;
  img.alt = `Preview ${index + 1}`;

  const numberBadge = document.createElement('div');
  numberBadge.className = 'image-number';
  numberBadge.textContent = `#${index + 1}`;

  const removeBtn = document.createElement('button');
  removeBtn.className = 'remove-image-btn';
  removeBtn.type = 'button';
  removeBtn.innerHTML = '<i class="fas fa-times"></i>';
  removeBtn.addEventListener('click', () => {
    removeImage(index, mode);
  });

  item.appendChild(img);
  item.appendChild(numberBadge);
  item.appendChild(removeBtn);

  return item;
}

/**
 * Remove image from preview and array
 */
function removeImage(index, mode) {
  uploadedImages[mode].splice(index, 1);
  refreshPreviews(mode);
}

/**
 * Refresh all previews after removal
 */
function refreshPreviews(mode) {
  const previewGridId = mode === 'add' ? 'imagePreviewGrid' : 'editImagePreviewGrid';
  const countIndicatorId = mode === 'add' ? 'imageCount' : 'editImageCount';
  
  const previewGrid = document.getElementById(previewGridId);
  const countIndicator = document.getElementById(countIndicatorId);

  if (!previewGrid) return;

  // Clear grid
  previewGrid.innerHTML = '';

  // Recreate all previews
  uploadedImages[mode].forEach((file, index) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const previewItem = createPreviewItem(e.target.result, index, mode);
      previewGrid.appendChild(previewItem);
    };
    reader.readAsDataURL(file);
  });

  updateImageCount(countIndicator, uploadedImages[mode].length);
}

/**
 * Update image count indicator
 */
function updateImageCount(countIndicator, count) {
  if (!countIndicator) return;
  
  const indicator = countIndicator.closest('.image-count-indicator');
  if (!indicator) return;

  const MAX_IMAGES = 10;
  // Show only the count, not "X / 10"
  countIndicator.textContent = count;

  // Update styling based on count (min 1, max 10)
  indicator.classList.remove('invalid', 'valid', 'warning');
  
  if (count === 0) {
    indicator.classList.add('invalid');
  } else if (count > MAX_IMAGES) {
    indicator.classList.add('invalid');
  } else if (count >= 1 && count <= MAX_IMAGES) {
    indicator.classList.add('valid');
  } else {
    indicator.classList.add('invalid');
  }
}

/**
 * Validate image count requirement (min 1, max 10)
 */
function validateImageCount(mode) {
  const MIN_IMAGES = 1;
  const MAX_IMAGES = 10;
  const count = uploadedImages[mode].length;
  
  if (count < MIN_IMAGES) {
    showToast(`Please upload at least ${MIN_IMAGES} image. Currently uploaded: ${count}`, 'error');
    return false;
  }
  
  if (count > MAX_IMAGES) {
    showToast(`Maximum ${MAX_IMAGES} images allowed per product. Currently uploaded: ${count}`, 'error');
    return false;
  }
  
  return true;
}

/**
 * Clear all uploaded images
 */
function clearImages(mode) {
  uploadedImages[mode] = [];
  const previewGridId = mode === 'add' ? 'imagePreviewGrid' : 'editImagePreviewGrid';
  const countIndicatorId = mode === 'add' ? 'imageCount' : 'editImageCount';
  
  const previewGrid = document.getElementById(previewGridId);
  const countIndicator = document.getElementById(countIndicatorId);
  
  if (previewGrid) previewGrid.innerHTML = '';
  updateImageCount(countIndicator, 0);
  
  // Reset file input
  const inputId = mode === 'add' ? 'productImageInput' : 'editProductImageInput';
  const input = document.getElementById(inputId);
  if (input) input.value = '';
}

// -------------------- FORMAT PRICE --------------------
function formatPrice(raw) {
  const num = parseFloat(raw);
  if (isNaN(num)) return "₱0.00";
  return `₱${num.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
}

function normalizePrice(priceText) {
  return priceText.replace(/[₱,\s]/g, "");
}

// -------------------- DELETE POPUP --------------------
let cardToDelete = null;
let deletePopupInitialized = false;
let deletePopup, deleteCloseBtn, deleteCancelBtn, deleteConfirmBtn, deleteMessage;

function initDeletePopup() {
  if (deletePopupInitialized) return;
  
  deletePopup = document.getElementById("popup-delete");
  deleteCloseBtn = document.querySelector(".popup-delete-close");
  deleteCancelBtn = document.querySelector(".popup-delete-cancel");
  deleteConfirmBtn = document.querySelector(".popup-delete-confirm");
  deleteMessage = document.getElementById("delete-message");
  
  deleteCloseBtn?.addEventListener("click", closeDeletePopup);
  deleteCancelBtn?.addEventListener("click", closeDeletePopup);

  deleteConfirmBtn?.addEventListener("click", () => {
    if (!cardToDelete) return;

    let id = cardToDelete.dataset.id;
    
    // Store original button text
    const originalText = deleteConfirmBtn.textContent;
    
    // Disable button to prevent multiple clicks
    deleteConfirmBtn.disabled = true;
    deleteConfirmBtn.textContent = "Deleting...";

    fetch(base_url + "ProductCon/delete_product/" + id)
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then(data => {
        if (data.status === "deleted") {
          const productName = cardToDelete.querySelector(".product-name")?.textContent || "Product";
          cardToDelete.remove();
          closeDeletePopup();
          showToast(`"${productName}" has been deleted successfully.`, 'success');
        } else {
          showToast("Failed to delete product.", 'error');
        }
      })
      .catch(error => {
        console.error('Error deleting product:', error);
        showToast("An error occurred while deleting the product. Please try again.", 'error');
      })
      .finally(() => {
        // Re-enable button and restore original text
        if (deleteConfirmBtn) {
          deleteConfirmBtn.disabled = false;
          deleteConfirmBtn.textContent = originalText;
        }
      });
  });
  
  deletePopupInitialized = true;
}

function openDeletePopup(card) {
  initDeletePopup();
  cardToDelete = card;
  const name = card.querySelector(".product-name")?.textContent || "Product";
  if (deleteMessage) deleteMessage.textContent = `Are you sure you want to delete "${name}"?`;
  if (deletePopup) deletePopup.style.display = "flex";
}

function closeDeletePopup() {
  if (deletePopup) deletePopup.style.display = "none";
  cardToDelete = null;
}

// -------------------- SEARCH & FILTER --------------------
function setupSearchFilter() {
  const searchInput = document.querySelector(".search-input");
  const searchButton = document.querySelector(".search-button");
  const categoryFilter = document.querySelector(".filter-category");
  const activeFiltersTags = document.getElementById("activeFiltersTags");
  const clearAllFilters = document.getElementById("clearAllFilters");
  
  if (!searchInput || !searchButton || !categoryFilter) {
    console.warn('Search/filter elements not found, retrying...');
    setTimeout(setupSearchFilter, 100);
    return;
  }

  // Update active filters display
  function updateActiveFilters() {
    if (!activeFiltersTags) return;
    
    const activeFilters = [];
    const searchTerm = (searchInput?.value || "").trim();
    const selectedCategory = (categoryFilter?.value || "").trim();
    
    if (searchTerm) {
      activeFilters.push({ type: 'search', value: searchTerm, label: `Search: "${searchTerm}"` });
    }
    
    if (selectedCategory) {
      activeFilters.push({ type: 'category', value: selectedCategory, label: `Category: ${selectedCategory}` });
    }
    
    // Clear existing tags
    activeFiltersTags.innerHTML = '';
    
    // Add active filter tags
    activeFilters.forEach(filter => {
      const tag = document.createElement('span');
      tag.className = 'active-filter-tag';
      tag.innerHTML = `
        ${filter.label}
        <span class="remove-filter" data-type="${filter.type}">&times;</span>
      `;
      
      tag.querySelector('.remove-filter').addEventListener('click', () => {
        if (filter.type === 'search') {
          searchInput.value = '';
        } else if (filter.type === 'category') {
          categoryFilter.value = '';
        }
        filterProducts();
      });
      
      activeFiltersTags.appendChild(tag);
    });
    
    // Show/hide clear all button
    if (clearAllFilters) {
      clearAllFilters.style.display = activeFilters.length > 0 ? 'block' : 'none';
    }
  }

  function filterProducts() {
    // Re-query product cards each time to handle dynamic content
    const productCards = document.querySelectorAll(".product-card");
    if (productCards.length === 0) {
      console.warn('No product cards found for filtering');
      return;
    }

    const searchTerm = (searchInput?.value || "").toLowerCase().trim();
    const selectedCategory = (categoryFilter?.value || "").toLowerCase().trim();

    let visibleCount = 0;
    productCards.forEach(card => {
      if (!card) return;
      
      const nameEl = card.querySelector(".product-name");
      const name = nameEl ? nameEl.textContent.toLowerCase() : "";
      const category = (card.dataset.category || "").toLowerCase();
      
      // Normalize category for comparison (handle different formats)
      const normalizedCategory = category.replace(/\s+/g, "-").toLowerCase();
      const normalizedSelectedCategory = selectedCategory.replace(/\s+/g, "-").toLowerCase();

      const matchesSearch = !searchTerm || name.includes(searchTerm);
      const matchesCategory = !selectedCategory || normalizedCategory === normalizedSelectedCategory || category.includes(selectedCategory);

      const show = matchesSearch && matchesCategory;
      
      card.style.display = show ? "" : "none";
      if (show) visibleCount++;
    });
    
    // Update active filters display
    updateActiveFilters();
    
    // Show message if no products match
    const productGrid = document.querySelector(".product-grid");
    let noResultsMsg = productGrid?.querySelector(".no-results-message");
    if (visibleCount === 0 && productCards.length > 0) {
      if (!noResultsMsg) {
        noResultsMsg = document.createElement("div");
        noResultsMsg.className = "no-results-message";
        noResultsMsg.style.cssText = "grid-column: 1 / -1; text-align: center; padding: 40px; color: #666; font-size: 16px;";
        noResultsMsg.textContent = "No products found matching your search criteria.";
        if (productGrid) productGrid.appendChild(noResultsMsg);
      }
      noResultsMsg.style.display = "block";
    } else if (noResultsMsg) {
      noResultsMsg.style.display = "none";
    }
  }
  
  // Clear all filters handler
  if (clearAllFilters) {
    clearAllFilters.addEventListener('click', (e) => {
      e.preventDefault();
      searchInput.value = '';
      categoryFilter.value = '';
      filterProducts();
    });
  }

  // Attach event listeners
  searchButton.addEventListener("click", (e) => {
    e.preventDefault();
    filterProducts();
  });
  
  searchInput.addEventListener("keyup", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      filterProducts();
    } else {
      // Real-time search as user types (optional)
      // filterProducts();
    }
  });
  
  categoryFilter.addEventListener("change", filterProducts);
  
  // Initial filter to ensure everything is visible
  filterProducts();
}

// -------------------- DYNAMIC CUSTOMIZATION FIELDS --------------------
/**
 * DYNAMIC FORM FIELD SYSTEM
 * 
 * This system implements a cascading dropdown system for product customization:
 * 1. Admin selects a main category (e.g., "Windows", "Doors")
 * 2. Subcategory dropdown appears with relevant options
 * 3. When subcategory is selected, customization fields are dynamically generated
 * 4. All fields use predefined options (dropdowns, checkboxes, color pickers, numeric inputs)
 * 5. No manual text input is allowed - all selections are from predefined lists
 * 
 * Field Types:
 * - tags: Clickable tag-style selection (can select multiple, add/remove tags)
 * - checkbox: Yes/No boolean selection
 * - color: Color picker for frame/hardware colors
 * - number: Numeric input with min/step validation
 * 
 * Data Flow:
 * - Category selection → Populate subcategories → Show subcategory dropdown
 * - Subcategory selection → Generate customization fields → Display in container
 * - Form submission → Collect all customization data → Send as JSON to server
 */

// Order Type to Category mapping
// Defines which categories are available for each order type
// Based on new reference:
// 🟢 Direct Order: Windows, Mirrors & Specialty Glass
// 🔵 Site Assessment: Doors, Glass Partitions & Enclosures, Commercial & Exterior
const orderTypeCategories = {
  "direct": [
    "Windows",                    // Sliding, Awning, Casement, Fixed Glass
    "Mirrors & Specialty Glass"   // Mirrors, Top Glass, Glass Board (Small-Scale Items)
  ],
  "site-assessment": [
    "Doors",                      // Sliding, Frameless (Large-Scale)
    "Glass Partitions & Enclosures", // Frameless Glass, Shower Enclosure (Large-Scale)
    "Commercial & Exterior"       // Storefront, Glass Balcony, Stair Railings (Large-Scale)
  ]
};

// Category to Subcategory mapping
// Maps each main category to its available subcategories
const categorySubcategories = {
  "Windows": ["Sliding", "Awning", "Casement", "Fixed Glass"],
  "Doors": ["Sliding", "Frameless"],
  "Glass Partitions & Enclosures": ["Frameless Glass", "Shower Enclosure", "Fixed Glass"],
  "Mirrors & Specialty Glass": ["Mirrors", "Top Glass", "Glass Board"],
  "Commercial & Exterior": ["Storefront", "Glass Balcony", "Stair Railings"]
};

// Order Type to Subcategory filtering
// For "Mirrors & Specialty Glass" category, filter subcategories based on order type
// Small-Scale (Direct): Mirrors, Top Glass, Glass Board
// Large-Scale (Site Assessment): All other subcategories if any
const orderTypeSubcategories = {
  "Mirrors & Specialty Glass": {
    "direct": ["Mirrors", "Top Glass", "Glass Board"]
  }
};

// Store editable customization fields (can be modified by admin)
// Load from localStorage if available, otherwise use defaults
let customizationFields = {};
const CUSTOMIZATION_FIELDS_STORAGE_KEY = 'glassify_customization_fields';

// Load customization fields with correct precedence:
// 1) Database (latest source of truth)
// 2) localStorage (offline cache; should not override DB)
// 3) JSON defaults (fill missing keys only)
/**
 * Fix field types for Windows_Casement if they're incorrect
 * Ensures Thickness is tags (not number) and Screen is tags (not checkbox)
 */
/**
 * Update Windows_Casement fields to new structure from JSON
 * This ensures the database is updated with the new field definitions
 */
async function updateWindowsCasementFields() {
  const fieldKey = 'Windows_Casement';
  
  // Load the new structure from JSON defaults
  const defaultFields = await getDefaultCustomizationFields();
  
  if (defaultFields[fieldKey] && Array.isArray(defaultFields[fieldKey]) && defaultFields[fieldKey].length > 0) {
    const newFields = JSON.parse(JSON.stringify(defaultFields[fieldKey])); // Deep copy
    
    // Check if current fields match the new structure
    const currentFields = customizationFields[fieldKey];
    let needsUpdate = false;
    
    // Expected new field IDs (in order): transomType, panelConfiguration, dimensions, frameColor, glassColor, glassType, thickness
    // Note: dimensions replaces the old width, height, h1 fields
    const expectedFieldIds = ['transomType', 'panelConfiguration', 'frameColor', 'glassColor', 'glassType', 'thickness'];
    const currentFieldIds = currentFields ? currentFields.map(f => f.id) : [];
    
    // Check if structure matches - must have all expected fields in correct order
    const hasAllFields = expectedFieldIds.every(id => currentFieldIds.includes(id));
    const hasCorrectOrder = JSON.stringify(currentFieldIds) === JSON.stringify(expectedFieldIds);
    
    if (!hasAllFields || !hasCorrectOrder) {
      needsUpdate = true;
      console.log('🔄 Windows_Casement structure mismatch detected. Updating...');
      console.log('Current field IDs:', currentFieldIds);
      console.log('Expected field IDs:', expectedFieldIds);
    } else {
      // Check if any field properties changed
      for (let i = 0; i < newFields.length; i++) {
        const newField = newFields[i];
        const currentField = currentFields ? currentFields.find(f => f.id === newField.id) : null;
        
        if (!currentField) {
          needsUpdate = true;
          break;
        }
        
        // Compare key properties
        if (currentField.label !== newField.label ||
            JSON.stringify(currentField.options || []) !== JSON.stringify(newField.options || []) ||
            currentField.stepNumber !== newField.stepNumber ||
            currentField.type !== newField.type) {
          needsUpdate = true;
          console.log(`🔄 Field ${newField.id} changed:`, { 
            label: { current: currentField.label, new: newField.label },
            options: { current: currentField.options, new: newField.options },
            stepNumber: { current: currentField.stepNumber, new: newField.stepNumber },
            type: { current: currentField.type, new: newField.type }
          });
          break;
        }
      }
    }
    
    if (needsUpdate) {
      console.log('📝 Updating Windows_Casement fields to new structure...');
      
      // Update in memory
      customizationFields[fieldKey] = newFields;
      
      // Update step names if available
      const stepNamesKey = `${fieldKey}_stepNames`;
      if (defaultFields[stepNamesKey]) {
        customizationFields[stepNamesKey] = defaultFields[stepNamesKey];
      }
      
      // Save to database
      const saved = await saveCustomizationFieldsToDatabase(fieldKey, newFields, 'Windows', 'Casement');
      if (saved) {
        console.log('✅ Windows_Casement fields updated and saved to database');
      } else {
        console.warn('⚠️ Windows_Casement fields updated in memory but database save may have failed');
      }
      
      // Clear localStorage cache to force reload
      try {
        localStorage.removeItem(CUSTOMIZATION_FIELDS_STORAGE_KEY);
        console.log('🗑️ Cleared localStorage cache');
      } catch (e) {
        console.warn('Could not clear localStorage:', e);
      }
      
      // Reload fields from database to ensure consistency
      setTimeout(async () => {
        try {
          const response = await fetch(base_url + 'customizationFields/getAll', { cache: 'no-cache' });
          const result = await response.json();
          if (result.status === 'success' && result.configs && result.configs[fieldKey]) {
            customizationFields[fieldKey] = result.configs[fieldKey].fields || newFields;
            console.log('🔄 Reloaded Windows_Casement fields from database');
          }
        } catch (e) {
          console.error('Error reloading fields:', e);
        }
      }, 500);
    } else {
      console.log('✅ Windows_Casement fields already up to date');
    }
  } else {
    console.warn('⚠️ Could not load Windows_Casement fields from JSON defaults');
  }
}

/**
 * Legacy function - kept for backward compatibility
 * Now calls updateWindowsCasementFields
 */
function fixWindowsCasementFieldTypes() {
  // This function is now replaced by updateWindowsCasementFields
  // But we'll call it to maintain compatibility
  updateWindowsCasementFields().catch(e => {
    console.error('Error updating Windows_Casement fields:', e);
  });
}

async function loadCustomizationFields() {
  // 1) Database (source of truth)
  if (typeof base_url !== 'undefined') {
    try {
      const response = await fetch(base_url + 'customizationFields/getAll', { cache: 'no-cache' });
      const result = await response.json();

      if (result.status === 'success' && result.configs) {
        for (const [fieldKey, config] of Object.entries(result.configs)) {
          // DB should override anything already in memory
          customizationFields[fieldKey] = config.fields || [];
          
          // Also load selectedTags if they exist in the config
          if (config.selectedTags) {
            customizationFields[`${fieldKey}_selectedTags`] = config.selectedTags;
          }
        }
        console.log('Customization fields loaded from database');
        
        // Update Windows_Casement fields to new structure if needed
        updateWindowsCasementFields().catch(e => {
          console.error('Error updating Windows_Casement fields:', e);
        });
      }
    } catch (e) {
      console.error("Error loading customization fields from database:", e);
    }
  }

  // 2) localStorage cache (fill missing only; never override DB)
  let saved = null;
  try {
    saved = localStorage.getItem(CUSTOMIZATION_FIELDS_STORAGE_KEY);
  } catch (e) {
    console.warn('localStorage access blocked by browser (Tracking Prevention):', e.message);
  }
  if (saved) {
    try {
      const savedFields = JSON.parse(saved);
      for (const [key, value] of Object.entries(savedFields)) {
        if (customizationFields[key] === undefined || customizationFields[key] === null || (Array.isArray(customizationFields[key]) && customizationFields[key].length === 0)) {
          customizationFields[key] = value;
        }
      }
      console.log('Customization fields loaded from localStorage (fill-missing)');
    } catch (e) {
      console.error('Error loading customization fields from localStorage:', e);
    }
  }

  // 3) JSON defaults (fill missing only)
  try {
    await initializeDefaultCustomizationFields();
    console.log('Customization fields defaults loaded from JSON (fill-missing)');
    
    // Update Windows_Casement fields to new structure after loading defaults (in case DB didn't have it)
    updateWindowsCasementFields().catch(e => {
      console.error('Error updating Windows_Casement fields:', e);
    });
  } catch (e) {
    console.error('Error loading customization fields from JSON defaults:', e);
  }

  // Persist merged view back to localStorage for caching/offline behavior
  try {
    localStorage.setItem(CUSTOMIZATION_FIELDS_STORAGE_KEY, JSON.stringify(customizationFields));
  } catch (e) {
    console.error('Error caching customization fields to localStorage:', e);
  }
  
  // Force update Windows_Casement fields to new structure (after all loading is done)
  // This ensures the database is updated even if it has old data
  updateWindowsCasementFields().catch(e => {
    console.error('Error updating Windows_Casement fields:', e);
  });
}

// Save customization fields to localStorage and database
function saveCustomizationFields() {
  try {
    localStorage.setItem(CUSTOMIZATION_FIELDS_STORAGE_KEY, JSON.stringify(customizationFields));
    
    // Also save to database via API
    saveCustomizationFieldsToDatabase();
  } catch (e) {
    console.error('Error saving customization fields:', e);
  }
}

// Save customization fields to database via API
// Can be called with specific field data or without parameters to save all fields
async function saveCustomizationFieldsToDatabase(fieldKeyToSave = null, fieldsToSave = null, categoryToSave = null, subcategoryToSave = null) {
  try {
    let fieldsToProcess = [];
    
    if (fieldKeyToSave && fieldsToSave) {
      // Save specific field configuration
      fieldsToProcess.push({
        fieldKey: fieldKeyToSave,
        fields: fieldsToSave,
        category: categoryToSave,
        subcategory: subcategoryToSave
      });
    } else {
      // Save all field configurations
      for (const [fieldKey, fields] of Object.entries(customizationFields)) {
        if (!fields || fields.length === 0) continue;
        
        // Extract category and subcategory from fieldKey
        const parts = fieldKey.split('_');
        let category = '';
        let subcategory = '';
        
        if (parts.length >= 2) {
        const prefixMap = {
            'Windows': 'Windows',
            'Doors': 'Doors',
            'Partitions': 'Glass Partitions & Enclosures',
            'Specialty': 'Mirrors & Specialty Glass',
            'Commercial': 'Commercial & Exterior'
          };
          
          const prefix = parts[0];
          category = prefixMap[prefix] || prefix;
          subcategory = parts.slice(1).join('_');
        }
        
        fieldsToProcess.push({
          fieldKey: fieldKey,
          fields: fields,
          category: category,
          subcategory: subcategory
        });
      }
    }
    
    // Save each field configuration
    for (const item of fieldsToProcess) {
      if (!item.fields || item.fields.length === 0) continue;
      
      // Use provided category/subcategory or extract from fieldKey
      let category = item.category;
      let subcategory = item.subcategory;
      
      if (!category || !subcategory) {
        const parts = item.fieldKey.split('_');
        if (parts.length >= 2) {
          const prefixMap = {
            'Windows': 'Windows',
            'Doors': 'Doors',
            'Partitions': 'Glass Partitions & Enclosures',
            'Specialty': 'Mirrors & Specialty Glass',
            'Commercial': 'Commercial & Exterior'
          };
          
          const prefix = parts[0];
          category = category || prefixMap[prefix] || prefix;
          subcategory = subcategory || parts.slice(1).join('_');
        }
      }
      
      // Get step names for this field key
      const stepNamesKey = item.fieldKey + '_stepNames';
      const stepNames = customizationFields[stepNamesKey];

      // Send as form data - CodeIgniter expects fields as array, so we'll send it as JSON string
      // and the controller should handle it (or we need to adjust controller, but for now this should work)
      const formData = new FormData();
      formData.append('fieldKey', item.fieldKey);
      formData.append('category', category);
      formData.append('subcategory', subcategory);
      formData.append('fields', JSON.stringify(item.fields));
      if (stepNames) {
        formData.append('stepNames', JSON.stringify(stepNames));
      }
      
      const response = await fetch(base_url + 'customizationFields/save', {
        method: 'POST',
        body: formData
      });
      
      // Check if response is OK
      if (!response.ok) {
        const text = await response.text();
        console.error(`HTTP error saving ${item.fieldKey}:`, response.status, text);
        return false;
      }
      
      // Check content type before parsing JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const text = await response.text();
        console.error(`Non-JSON response when saving ${item.fieldKey}:`, text.substring(0, 200));
        return false;
      }
      
      const result = await response.json();
      if (result.status === 'success') {
        console.log(`Saved field config for ${item.fieldKey}`);
      } else {
        console.error(`Error saving ${item.fieldKey}:`, result.message || 'Unknown error');
        return false; // Return false if any save fails
      }
    }
    
    return true; // Return true if all saves succeeded
  } catch (e) {
    console.error("Error saving customization fields to database:", e);
    return false;
  }
}

// Store cached default fields to avoid multiple fetches
let cachedDefaultFields = null;

/**
 * Load default customization fields from JSON file
 * Falls back to empty object if file fails to load
 */
async function getDefaultCustomizationFields() {
  // Return cached data if available
  if (cachedDefaultFields !== null) {
    return cachedDefaultFields;
  }

  try {
    // Ensure base_url has trailing slash and construct proper path
    const jsonPath = (base_url.endsWith('/') ? base_url : base_url + '/') + 'assets/data/default-customization-fields.json';
    const response = await fetch(jsonPath, {
      cache: 'no-cache', // Always fetch fresh data on page load
      headers: {
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    cachedDefaultFields = data;
    console.log('Successfully loaded customization fields from JSON file');
    return data;
  } catch (error) {
    console.error('Error loading default customization fields from JSON:', error);
    console.warn('Falling back to empty customization fields. Please ensure the JSON file exists at: assets/data/default-customization-fields.json');
    // Return empty object as fallback
    cachedDefaultFields = {};
    return {};
  }
}

// Initialize default customization fields
// Enhanced with comprehensive options from product catalog
// Organized into steps with 3-4 fields per step for better UX
async function initializeDefaultCustomizationFields() {
  // Initialize customizationFields if not already done
  if (!customizationFields) {
    customizationFields = {};
  }

  // Only set defaults for keys that don't already have data
  const defaultFields = await getDefaultCustomizationFields();
  for (const [key, value] of Object.entries(defaultFields)) {
    if (!customizationFields[key] || customizationFields[key].length === 0) {
      customizationFields[key] = value;
    }
  }
}

// Legacy function removed - data now loaded from JSON file
// The old hardcoded return statement has been moved to assets/data/default-customization-fields.json

// Initialize on load
// loadCustomizationFields will be called in DOMContentLoaded to ensure base_url is defined

/**
 * Populates category dropdown based on selected order type
 * @param {string} orderType - Selected order type ("direct" or "site-assessment")
 * @param {HTMLElement} categorySelect - Category select element
 */
function populateCategories(orderType, categorySelect) {
  // Clear existing options except the first placeholder
  categorySelect.innerHTML = '<option value="" disabled selected>Select category</option>';
  
  if (orderType && orderTypeCategories[orderType]) {
    orderTypeCategories[orderType].forEach(category => {
      const option = document.createElement("option");
      option.value = category;
      option.textContent = category;
      categorySelect.appendChild(option);
    });
  }
  
  // Clear subcategory when category changes
  const subcategorySelect = document.getElementById("productSubcategory");
  const subcategoryGroup = document.getElementById("subcategoryGroup");
  if (subcategorySelect) {
    subcategorySelect.innerHTML = '<option value="" disabled selected>Select subcategory</option>';
    subcategorySelect.value = "";
  }
  if (subcategoryGroup) {
    subcategoryGroup.style.display = "none";
  }
  
  // Clear customization fields
  const customizationContainer = document.getElementById("customizationFields");
  if (customizationContainer) {
    customizationContainer.innerHTML = "";
  }
}

/**
 * Subcategory to Series mapping
 * Maps each subcategory to its available series options
 * Works for all categories: Windows, Doors, Glass Partitions, Commercial, etc.
 */
const subcategorySeries = {
  "Casement": ["None", "YC-38 Series", "YC-50 Series", "60-DMX Series", "85 Series", "75 Series"],
  "Sliding": ["None", "798 Series", "900 Series"],
  "Awning": ["None", "798 Series", "900 Series"], // Add appropriate series if needed
  "Fixed Glass": [] // No series options for Fixed Glass
};

/**
 * Populates series dropdown based on selected subcategory
 * @param {string} subcategory - Selected subcategory
 * @param {HTMLElement} seriesSelect - Series select element
 */
function populateSeriesOptions(subcategory, seriesSelect) {
  if (!seriesSelect || !seriesSelect.tagName || seriesSelect.tagName !== 'SELECT') {
    console.warn('populateSeriesOptions: seriesSelect is null or invalid');
    return;
  }

  try {
    // Clear existing options and set placeholder
    seriesSelect.innerHTML = '<option value="" disabled selected>Select series</option>';
    
    if (subcategory && subcategorySeries[subcategory] && subcategorySeries[subcategory].length > 0) {
      subcategorySeries[subcategory].forEach(series => {
        const option = document.createElement("option");
        option.value = series;
        option.textContent = series;
        seriesSelect.appendChild(option);
      });
    }
    
    // Always add "None" option at the end if it's not already in the list
    if (subcategory && subcategorySeries[subcategory] && !subcategorySeries[subcategory].includes("None")) {
      const noneOption = document.createElement("option");
      noneOption.value = "None";
      noneOption.textContent = "None";
      seriesSelect.appendChild(noneOption);
    }
  } catch (error) {
    console.error('Error in populateSeriesOptions:', error, { subcategory });
  }
}

/**
 * Populates subcategory dropdown based on selected category and order type
 * @param {string} category - Selected category
 * @param {HTMLElement} subcategorySelect - Subcategory select element
 * @param {string} orderType - Optional order type for filtering
 */
function populateSubcategories(category, subcategorySelect, orderType = null) {
  if (!subcategorySelect || !subcategorySelect.tagName || subcategorySelect.tagName !== 'SELECT') {
    console.warn('populateSubcategories: subcategorySelect is null or invalid');
    return;
  }

  try {
    // Clear existing options and set placeholder with selected attribute
    subcategorySelect.innerHTML = '<option value="" disabled selected>Select subcategory</option>';
    
    if (category && categorySubcategories[category]) {
      let subcategories = categorySubcategories[category];
      
      // Filter subcategories based on order type if category has order-type-specific subcategories
      if (orderType && orderTypeSubcategories[category] && orderTypeSubcategories[category][orderType]) {
        subcategories = orderTypeSubcategories[category][orderType];
      }
      
      subcategories.forEach(subcat => {
        const option = document.createElement("option");
        option.value = subcat;
        option.textContent = subcat;
        subcategorySelect.appendChild(option);
      });
      
      // Show subcategory group if subcategories exist
      const subcategoryGroup = document.getElementById("subcategoryGroup");
      if (subcategoryGroup) {
        subcategoryGroup.style.display = "block";
      }
    } else {
      // Hide subcategory group if no subcategories
      const subcategoryGroup = document.getElementById("subcategoryGroup");
      if (subcategoryGroup) {
        subcategoryGroup.style.display = "none";
      }
    }
  } catch (error) {
    console.error('Error in populateSubcategories:', error, { category, orderType });
  }
}

// Category and subcategory mappings
// Note: Default customization fields data has been moved to assets/data/default-customization-fields.json
// The getDefaultCustomizationFields() function now loads from that JSON file

/**
 * Windows_Casement field definitions are now loaded from assets/data/default-customization-fields.json
 * Structure:
 * Step 1: Transom Type, Panel Configuration, Width, Height, h1 (conditional)
 * Step 2: Frame Color, Glass Color, Glass Type, Thickness
 * 
 * Series Presets are defined below for auto-filling when a series is selected
 */
  // Series Presets - Auto-fill configurations for each series
// Note: These presets are merged with the JSON file data when loaded
const Series_Presets = {
    "900 Series": {
      "numberOfPanels": ["2 Panels"],
      "transomType": ["None"],
      "trackSystem": ["2 Tracks"],
      "screenOption": ["Without Screen"],
      "panelConfiguration": ["S | S (Sliding | Sliding)"],
      "frameColor": ["White"],
      "glassType": ["Clear"],
      "glassThickness": ["6mm"],
      "lockType": ["Center Lok 904 Big"],
      "rollerType": ["Single Panel Roller"],
      "screen": ["Without Screen"]
    },
    "798 Series": {
      "numberOfPanels": ["2 Panels"],
      "transomType": ["None"],
      "trackSystem": ["2 Tracks"],
      "screenOption": ["Without Screen"],
      "panelConfiguration": ["S | S (Sliding | Sliding)"],
      "frameColor": ["White"],
      "glassType": ["Clear"],
      "glassThickness": ["6mm"],
      "lockType": ["Enter Lock 908"],
      "rollerType": ["Single Roller ORD"],
      "screen": ["Without Screen"]
    },
    // Casement Series Presets
    // Thickness options vary by series:
    // YC-38 Series: 6mm only
    // YC-50 Series: 6mm-8mm
    // 60-DMX/85/75 Series: 6mm, 8mm, 10mm, 12mm
    "YC-38 Series": {
      "transomType": ["None"],
      "panelConfiguration": ["1"],
      "frameColor": ["Hanalok"],
      "glassColor": ["Clear"],
      "glassType": ["Ordinary"],
      "thickness": ["6mm"]
    },
    "YC-50 Series": {
      "transomType": ["None"],
      "panelConfiguration": ["1"],
      "frameColor": ["Hanalok"],
      "glassColor": ["Clear"],
      "glassType": ["Ordinary"],
      "thickness": ["6mm", "8mm"]
    },
    "60-DMX Series": {
      "transomType": ["None"],
      "panelConfiguration": ["1"],
      "frameColor": ["Hanalok"],
      "glassColor": ["Clear"],
      "glassType": ["Ordinary"],
      "thickness": ["6mm", "8mm", "10mm", "12mm"]
    },
    "85 Series": {
      "transomType": ["None"],
      "panelConfiguration": ["1"],
      "frameColor": ["Hanalok"],
      "glassColor": ["Clear"],
      "glassType": ["Ordinary"],
      "thickness": ["6mm", "8mm", "10mm", "12mm"]
    },
    "75 Series": {
      "transomType": ["None"],
      "panelConfiguration": ["1"],
      "frameColor": ["Hanalok"],
      "glassColor": ["Clear"],
      "glassType": ["Ordinary"],
      "thickness": ["6mm", "8mm", "10mm", "12mm"]
    }
};

// Note: All field definitions (Windows_Casement, Doors_Sliding, etc.) are now loaded from assets/data/default-customization-fields.json
// The Series_Presets above are merged with the JSON data when customizationFields are loaded

/**
 * Populates subcategory dropdown based on selected category and order type
 * @param {string} category - Selected main category
 * @param {HTMLElement} subcategorySelect - Subcategory select element
 * @param {string} orderType - Selected order type (optional, for filtering)
 */
function populateSubcategories(category, subcategorySelect, orderType = null) {
  // Defensive check - ensure element exists and is valid
  if (!subcategorySelect || !subcategorySelect.nodeType || subcategorySelect.nodeType !== 1) {
    console.warn('populateSubcategories: subcategorySelect is null or invalid');
    return;
  }
  
  try {
    // Clear existing options and set placeholder with selected attribute
    subcategorySelect.innerHTML = '<option value="" disabled selected>Select subcategory</option>';
    
    if (category && categorySubcategories[category]) {
      let subcategories = categorySubcategories[category];
      
      // Filter subcategories based on order type if category has order-type-specific subcategories
      if (orderType && orderTypeSubcategories[category] && orderTypeSubcategories[category][orderType]) {
        subcategories = orderTypeSubcategories[category][orderType];
      }
      
      subcategories.forEach(subcat => {
        const option = document.createElement("option");
        option.value = subcat;
        option.textContent = subcat;
        subcategorySelect.appendChild(option);
      });
      
      // Show subcategory group if subcategories exist
      const subcategoryGroup = document.getElementById("subcategoryGroup");
      if (subcategoryGroup) {
        subcategoryGroup.style.display = "block";
      }
    } else {
      // Hide subcategory group if no subcategories
      const subcategoryGroup = document.getElementById("subcategoryGroup");
      if (subcategoryGroup) {
        subcategoryGroup.style.display = "none";
      }
    }
  } catch (error) {
    console.error('Error in populateSubcategories:', error, { category, orderType });
  }
}

/**
 * Show manage customization fields modal
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Ordinary", "Tempered", "Reflective"], stepNumber: 1 },
    { type: "tags", label: "Glass Color", id: "glassColor", options: ["Clear", "Bronze", "Frosted/Smoked"], stepNumber: 1 },
    { type: "tags", label: "Frame Color/Material", id: "frameColor", options: ["Powder Coated White", "Analok", "Matte Gray", "Matte Black", "Wood Finish"], stepNumber: 1 },
    // Step 2: Configuration & Details
    { type: "tags", label: "Thickness (mm)", id: "thickness", options: ["6mm", "8mm", "10mm", "12mm"], stepNumber: 2 }
  ],
  "Doors_Bi-fold Door_stepNames": {
    "1": "Basic Options",
    "2": "Configuration & Details"
  },
  "Doors_Frameless": [
    // Step 1: Basic Options
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Clear", "Tinted", "Frosted", "Laminated", "Laminated safety glass"], stepNumber: 1 },
    { type: "tags", label: "Door Type", id: "doorType", options: ["Single swing", "Double swing", "Single French door", "Double French doors"], stepNumber: 1 },
    { type: "tags", label: "Door Swing", id: "doorSwing", options: ["Left swing", "Right swing", "Left-hinged", "Right-hinged"], stepNumber: 1 },
    // Step 2: Panel Configuration
    { type: "tags", label: "Fixed Panels", id: "fixedPanels", options: ["With fixed side/transom panels", "Without fixed panels", "0 fixed panels", "1 fixed panel", "2 fixed panels", "More fixed panels", "With fixed side panel (left or right)", "With fixed transom", "Both"], stepNumber: 2 },
    { type: "tags", label: "Configuration", id: "configuration", options: ["With fixed side panel (left or right)", "With fixed transom", "Both", "Single swing door", "Double swing door"], stepNumber: 2 },
    // Step 3: Design & Hardware
    { type: "tags", label: "Handle Style", id: "handleType", options: ["Various pull handle designs", "Various pull handles", "Decorative handles"], stepNumber: 3 },
    { type: "tags", label: "Hardware Finish", id: "hardwareFinish", options: ["Polished Chrome/Stainless Steel", "Matte Black", "Brushed Nickel", "Gold", "Chrome/Stainless Steel"], stepNumber: 3 },
    { type: "tags", label: "Grid Pattern", id: "gridPattern", options: ["Internal grids", "External grids", "Colonial", "Prairie", "Custom grid designs", "French type grid"], stepNumber: 3 },
    // Step 4: Glass Treatment & Installation
    { type: "tags", label: "Glass Treatment", id: "glassTreatment", options: ["Frosted stripes (horizontal/vertical)", "Custom patterns", "Colors", "Frosted sticker (customizable patterns, opacity, colors)"], stepNumber: 4 },
    { type: "tags", label: "Installation", id: "installation", options: ["Patch fittings (minimalist hardware)", "Standard"], stepNumber: 4 },
    { type: "tags", label: "Hardware", id: "hardware", options: ["Push/pull handles", "Locks", "Closers", "Multi-point locks"], stepNumber: 4 },
    { type: "checkbox", label: "Soft-close", id: "softClose", stepNumber: 4 }
  ],
  "Doors_Frameless_stepNames": {
    "1": "Basic Options",
    "2": "Panel Configuration",
    "3": "Design & Hardware",
    "4": "Glass Treatment & Installation"
  },
  "Doors_Patch Fitting": [
    // Step 1: Basic Options
    { type: "tags", label: "Series", id: "series", options: ["Frameless Fixed Glass", "Frameless Door"], stepNumber: 1 },
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Tempered", "Reflective"], stepNumber: 1 },
    { type: "tags", label: "Glass Color", id: "glassColor", options: ["Clear", "Bronze", "Frosted/Smoked"], stepNumber: 1 },
    { type: "tags", label: "Frame Color/Material", id: "frameColor", options: ["Stainless Mirror Finish"], stepNumber: 1 },
    // Step 2: Configuration & Details
    { type: "tags", label: "Thickness (mm)", id: "thickness", options: ["10mm-12mm"], stepNumber: 2 }
  ],
  "Doors_Patch Fitting_stepNames": {
    "1": "Basic Options",
    "2": "Configuration & Details"
  },
  // Glass Partitions & Enclosures subcategories - Enhanced with catalog options
  "Partitions_Frameless Glass": [
    // Step 1: Basic Options
    { type: "tags", label: "Layout", id: "layout", options: ["L-shape", "Straight", "U-shape", "L-type", "Neo-angle", "Square", "Bay", "Other corner layouts"], stepNumber: 1 },
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Clear", "Frosted", "Tinted", "Frosted (full or partial)", "Clear with frosted sticker", "Fully frosted"], stepNumber: 1 },
    { type: "tags", label: "Finish", id: "finish", options: ["Clear", "Frosted", "Patterned"], stepNumber: 1 },
    // Step 2: Configuration & Hardware
    { type: "tags", label: "Configuration", id: "configuration", options: ["Single partition", "Multiple partitions", "2 fixed panels", "3 fixed panels", "Custom configurations"], stepNumber: 2 },
    { type: "tags", label: "Hardware Color", id: "hardwareColor", options: ["Black", "Silver", "Gold", "White", "Bronze", "Chrome/Stainless Steel", "Black Matte", "Brushed Nickel", "Stainless Steel"], stepNumber: 2 },
    { type: "tags", label: "Mounting Hardware", id: "mountingHardware", options: ["Stainless Fixed Bracket", "Gold U-Channel", "Analok U-Channel (anodized aluminum)", "Stainless U-Channel", "Other bracket types", "Standard mounting"], stepNumber: 2 },
    { type: "number", label: "Glass Thickness (mm)", id: "glassThickness", min: 1, step: 0.1, stepNumber: 2 }
  ],
  "Partitions_Frameless Glass_stepNames": {
    "1": "Basic Options",
    "2": "Configuration & Hardware"
  },
  "Partitions_Shower Enclosure": [
    // Step 1: Basic Options
    { type: "tags", label: "Series", id: "series", options: ["Arched Fixed Frameless Shower Partition", "Fixed Frameless with Curved Corner Shower Partition", "Fixed Frameless Shower Partition", "Fixed with Swing Shower Enclosure", "Fixed with Sliding Shower Enclosure", "Fixed Framed Shower Partition", "Swing Door Shower Enclosure", "Corner Swing Shower Enclosure", "Corner Sliding Shower Enclosure", "Corner Double Sliding Shower Enclosure", "Bay Swing Shower Enclosure", "2 Fixed and 1 Sliding Shower Enclosure", "2 Fixed and 1 Swing Shower Enclosure"], stepNumber: 1 },
    { type: "tags", label: "Layout", id: "layout", options: ["L-shape", "Straight", "U-shape", "L-type", "Neo-angle", "Square", "Bay", "Other corner layouts"], stepNumber: 1 },
    { type: "tags", label: "Configuration", id: "configuration", options: ["Fixed and swing", "Swing with small fixed glass", "Single sliding door", "Double sliding doors", "Sliding with fixed panels", "Single sliding", "Double sliding", "With fixed panels", "2 fixed panels", "3 fixed panels", "Custom configurations"], stepNumber: 1 },
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Tempered"], stepNumber: 1 },
    { type: "tags", label: "Glass Color", id: "glassColor", options: ["Clear", "Clear with Frosted Sticker (Middle Portion)", "10mm Frosted Tempered"], stepNumber: 1 },
    { type: "tags", label: "Hardware Finish", id: "hardwareFinish", options: ["Mirror/Stainless Hardware", "Matte Black Hardware"], stepNumber: 1 },
    // Step 2: Glass Treatment
    { type: "tags", label: "Glass Treatment", id: "glassTreatment", options: ["Frosted sticker (customizable patterns, opacity, colors)", "Clear", "Custom patterns", "Heights (top clear, bottom frosted)", "Colors"], stepNumber: 2 },
    { type: "tags", label: "Glass Thickness (mm)", id: "glassThickness", options: ["10mm"], stepNumber: 2 },
    // Step 3: Hardware & Installation
    { type: "tags", label: "Handle Style", id: "handleStyle", options: ["Various pull handle designs", "Various pull handles", "Knob handles", "Square handles", "Square matte black", "Round", "Bar-style"], stepNumber: 3 },
    { type: "tags", label: "Door Swing", id: "doorSwing", options: ["Left-hinged", "Right-hinged", "Left swing", "Right swing"], stepNumber: 3 },
    { type: "tags", label: "Mounting", id: "mounting", options: ["Standard mounting", "Custom mounting methods"], stepNumber: 3 }
  ],
  "Partitions_Shower Enclosure_stepNames": {
    "1": "Basic Options",
    "2": "Glass Treatment",
    "3": "Hardware & Installation"
  },
  "Partitions_Fixed Glass": [
    // Step 1: Basic Options
    { type: "tags", label: "Layout", id: "layout", options: ["L-shape", "Straight", "U-shape"], stepNumber: 1 },
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Clear", "Frosted", "Tinted"], stepNumber: 1 },
    { type: "tags", label: "Finish", id: "finish", options: ["Clear", "Frosted", "Patterned"], stepNumber: 1 },
    // Step 2: Configuration & Hardware
    { type: "tags", label: "Configuration", id: "configuration", options: ["Single partition", "Multiple partitions", "2 fixed panels", "3 fixed panels", "Custom configurations"], stepNumber: 2 },
    { type: "tags", label: "Mounting Hardware", id: "mountingHardware", options: ["Stainless Fixed Bracket", "Gold U-Channel", "Analok U-Channel (anodized aluminum)", "Stainless U-Channel", "Other bracket types", "Standard mounting"], stepNumber: 2 },
    { type: "tags", label: "Hardware Finish", id: "hardwareColor", options: ["Stainless Steel", "Black", "Gold", "Silver", "Bronze", "Analok (dark/bronze finish)", "Chrome/Stainless Steel"], stepNumber: 2 },
    { type: "number", label: "Glass Thickness (mm)", id: "glassThickness", min: 1, step: 0.1, stepNumber: 2 }
  ],
  "Partitions_Fixed Glass_stepNames": {
    "1": "Basic Options",
    "2": "Configuration & Hardware"
  },
  // Mirrors & Specialty Glass subcategories - Based on CUSTOMIZATION_REFERENCE.md
  "Specialty_Mirrors": [
    // Step 1: Basic Options
    { type: "tags", label: "Series", id: "series", options: ["Rectangle/Square Framed Mirror", "Rectangle/Square Frameless Mirror", "Oval Framed Mirror", "Oval Frameless Mirror", "Arched Framed Mirror", "Arched Frameless Mirror"], stepNumber: 1 },
    { type: "tags", label: "Shape", id: "shape", options: ["Round", "Rectangle", "Oval", "Circle", "Square", "Rectangular with rounded edges", "Rectangular with arched top", "Custom shapes"], stepNumber: 1 },
    { type: "number", label: "Corner Radius (in)", id: "cornerRadius", min: 0, step: 0.1, stepNumber: 1 },
    { type: "tags", label: "Frame Type", id: "frameType", options: ["Frameless", "Framed"], stepNumber: 1 },
    { type: "tags", label: "Frame Material/Color", id: "frameColor", options: ["White", "Black", "Gold", "Machine Polished Edges", "Beveled Edge"], stepNumber: 1 },
    // Step 2: Configuration & Details
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Copper Free and Lead Free Mirror"], stepNumber: 2 },
    { type: "tags", label: "Thickness (mm)", id: "thickness", options: ["6mm"], stepNumber: 2 },
    { type: "tags", label: "Tint/Finish", id: "tintFinish", options: ["Bronze tint/color", "Grey tint (smoked)", "Colored glass"], stepNumber: 2 },
    { type: "tags", label: "Orientation", id: "orientation", options: ["Vertical", "Horizontal", "Vertical/Full-body"], stepNumber: 2 },
    { type: "tags", label: "Style", id: "style", options: ["French Type (grid/paneled design)"], stepNumber: 2 },
    { type: "tags", label: "Lighting", id: "lighting", options: ["Integrated LED lighting", "Backlighting", "Front lighting", "Integrated LED options"], stepNumber: 2 },
    { type: "tags", label: "LED Color/Temperature", id: "ledColorTemperature", options: ["Warm white", "Cool white", "Tunable white", "RGB"], stepNumber: 2 },
    { type: "tags", label: "Grid Pattern", id: "gridPattern", options: ["French window style grid"], stepNumber: 2 },
    { type: "tags", label: "Quantity", id: "quantity", options: ["Available in sets (3 sets, or individually)"], stepNumber: 2 },
    { type: "tags", label: "Mounting Method", id: "mountingMethod", options: ["Wall-mounted", "Stand", "Adhesive", "Leaning", "Wall-mounted (often fixed above vanity)", "Fixed wall mount", "Integrated hanger", "Rope hanger", "Chain"], stepNumber: 2 },
    { type: "tags", label: "Control", id: "control", options: ["Touch sensor button", "Dimmer", "Defogger"], stepNumber: 2 },
    { type: "tags", label: "Additional Features", id: "additionalFeatures", options: ["Defogger", "Dimmer"], stepNumber: 2 },
    { type: "tags", label: "Arrangement", id: "arrangement", options: ["Can be displayed as triptych", "Individually"], stepNumber: 2 }
  ],
  "Specialty_Mirrors_stepNames": {
    "1": "Basic Options",
    "2": "Configuration & Details"
  },
  "Specialty_Top Glass": [
    // Step 1: Basic Options
    { type: "tags", label: "Shape", id: "shape", options: ["Round", "Rectangle", "Oval", "Square", "Custom shapes"], stepNumber: 1 },
    { type: "tags", label: "Edge Finish", id: "edgeFinish", options: ["Beveled", "Polished", "Raw", "Beveled edge", "Flat polished edge", "Pencil edge"], stepNumber: 1 },
    // Step 2: Details & Installation
    { type: "tags", label: "Mounting Method", id: "mountingMethod", options: ["Wall-mounted", "Stand", "Adhesive"], stepNumber: 2 }
  ],
  "Specialty_Top Glass_stepNames": {
    "1": "Basic Options",
    "2": "Details & Installation"
  },
  "Specialty_Glass Board": [
    // Step 1: Basic Options
    { type: "tags", label: "Shape", id: "shape", options: ["Round", "Rectangle", "Oval", "Square", "Custom shapes"], stepNumber: 1 },
    { type: "tags", label: "Edge Finish", id: "edgeFinish", options: ["Beveled", "Polished", "Raw", "Beveled edge", "Flat polished edge", "Pencil edge"], stepNumber: 1 },
    // Step 2: Details & Installation
    { type: "number", label: "Corner Radius (in)", id: "cornerRadius", min: 0, step: 0.1, stepNumber: 2 },
    { type: "tags", label: "Mounting Method", id: "mountingMethod", options: ["Wall-mounted", "Stand", "Adhesive"], stepNumber: 2 }
  ],
  "Specialty_Glass Board_stepNames": {
    "1": "Basic Options",
    "2": "Details & Installation"
  },
  // Commercial & Exterior subcategories - Enhanced with catalog options
  "Commercial_Storefront": [
    // Step 1: Basic Options
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Clear", "Tinted", "Frosted", "Laminated safety glass"], stepNumber: 1 },
    { type: "tags", label: "Safety Glass Type", id: "safetyGlassType", options: ["Tempered", "Laminated", "Bulletproof", "Laminated safety glass"], stepNumber: 1 },
    // Step 2: Hardware & Installation
    { type: "tags", label: "Handrail Type", id: "handrailType", options: ["Stainless steel", "Aluminum", "Glass"], stepNumber: 2 },
    { type: "tags", label: "Mounting System", id: "mountingSystem", options: ["Clamp", "Bolt", "Adhesive", "Patch fittings (minimalist hardware)"], stepNumber: 2 },
    { type: "tags", label: "Hardware Finish", id: "hardwareFinish", options: ["Polished Chrome/Stainless Steel", "Matte Black", "Brushed Nickel", "Gold"], stepNumber: 2 }
  ],
  "Commercial_Storefront_stepNames": {
    "1": "Basic Options",
    "2": "Hardware & Installation"
  },
  "Commercial_Glass Balcony": [
    // Step 1: Basic Options
    { type: "tags", label: "Safety Glass Type", id: "safetyGlassType", options: ["Tempered", "Laminated", "Bulletproof"], stepNumber: 1 },
    { type: "tags", label: "Handrail Type", id: "handrailType", options: ["Stainless steel", "Aluminum", "Glass"], stepNumber: 1 },
    { type: "tags", label: "Mounting System", id: "mountingSystem", options: ["Clamp", "Bolt", "Adhesive"], stepNumber: 1 }
  ],
  "Commercial_Glass Balcony_stepNames": {
    "1": "Basic Options"
  }
}

/**
 * Populates subcategory dropdown based on selected category and order type
 * @param {string} category - Selected main category
 * @param {HTMLElement} subcategorySelect - Subcategory select element
 * @param {string} orderType - Selected order type (optional, for filtering)
 */
function populateSubcategories(category, subcategorySelect, orderType = null) {
  // Defensive check - ensure element exists and is valid
  if (!subcategorySelect || !subcategorySelect.nodeType || subcategorySelect.nodeType !== 1) {
    console.warn('populateSubcategories: subcategorySelect is null or invalid');
    return;
  }
  
  try {
    // Clear existing options and set placeholder with selected attribute
    subcategorySelect.innerHTML = '<option value="" disabled selected>Select subcategory</option>';
    
    if (category && categorySubcategories[category]) {
      let subcategories = categorySubcategories[category];
      
      // Filter subcategories based on order type if category has order-type-specific subcategories
      if (orderType && orderTypeSubcategories[category] && orderTypeSubcategories[category][orderType]) {
        subcategories = orderTypeSubcategories[category][orderType];
      }
      
      subcategories.forEach(subcat => {
        const option = document.createElement("option");
        option.value = subcat;
        option.textContent = subcat;
        // Double-check element still exists before appending
        if (subcategorySelect && subcategorySelect.nodeType === 1) {
          subcategorySelect.appendChild(option);
        }
      });
    }
  } catch (error) {
    console.error('Error in populateSubcategories:', error, { category, orderType });
  }
}

/**
 * Shows modal to manage customization fields for a subcategory
 * @param {string} category - Selected category
 * @param {string} subcategory - Selected subcategory
 */
function showManageCustomizationFields(category, subcategory, productCustomization = {}, storedSeries = null) {
  console.log("=== MODAL OPENED ===");
  console.log("Category:", category);
  console.log("Subcategory:", subcategory);
  console.log("Stored series passed:", storedSeries);
  console.log("Product customization:", productCustomization);
  // Build field key
  let fieldKey;
  if (category === "Windows") {
    fieldKey = `Windows_${subcategory}`;
  } else if (category === "Doors") {
    fieldKey = `Doors_${subcategory}`;
  } else if (category === "Glass Partitions & Enclosures") {
    fieldKey = `Partitions_${subcategory}`;
  } else if (category === "Mirrors & Specialty Glass") {
    fieldKey = `Specialty_${subcategory}`;
  } else if (category === "Commercial & Exterior") {
    fieldKey = `Commercial_${subcategory}`;
  } else {
    fieldKey = subcategory;
  }
  
  // Get fields from saved data
  let fields = customizationFields[fieldKey] || [];
  
  // Get step names (stored separately)
  const stepNamesKey = `${fieldKey}_stepNames`;
  let stepNames = customizationFields[stepNamesKey] || {};
  
  // Get saved series selection for this subcategory
  const savedSeriesKey = `${fieldKey}_selectedSeries`;
  const savedSeries = customizationFields[savedSeriesKey] || null;
  
  // Remove any existing manage fields modal to prevent duplicates
  const existingModal = document.getElementById("manageCustomizationFieldsModal");
  if (existingModal) {
    existingModal.remove();
  }
  
  const modal = document.createElement("div");
  modal.className = "popup-overlay";
  modal.id = "manageCustomizationFieldsModal";
  modal.style.display = "flex";
  modal.innerHTML = `
    <div class="popup" style="width: 800px; max-height: 90vh; overflow-y: auto;">
      <span class="close-btn" id="closeManageFieldsModal">&times;</span>
      <h3>Manage Customization Fields</h3>
      <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
        Category: <strong>${category}</strong> | Subcategory: <strong>${subcategory}</strong>
      </p>
      <div id="minFieldsWarning" style="color: #d9534f; font-size: 12px; margin-bottom: 15px; padding: 8px; background: #ffe0e0; border-radius: 4px; display: none;">
        <i class="fas fa-info-circle"></i> <strong>Note:</strong> A minimum of 2 fields per step is required.
      </div>
      
      <!-- Series Selection Mode - Available for ALL categories and subcategories -->
      <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 6px; border: 1px solid #dee2e6;">
        <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #005b82;">
          <i class="fas fa-layer-group"></i> Series Configuration
        </h4>
        
        <div style="margin-bottom: 15px;">
          <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;">
            <input type="radio" name="seriesMode" id="useExistingSeries" value="existing" style="cursor: pointer;">
            <span style="font-weight: 600; color: #333;">Use Existing Series</span>
          </label>
          <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
            <input type="radio" name="seriesMode" id="createNewSeries" value="new" style="cursor: pointer;">
            <span style="font-weight: 600; color: #333;">Create New Series</span>
          </label>
        </div>
        
        <div id="seriesSelectorContainer" style="display: none; margin-top: 15px;">
          <label for="manageSeriesSelect" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Select Series:</label>
          <select id="manageSeriesSelect" class="input-text" style="width: 100%; padding: 8px;">
            <option value="" disabled selected>Select series</option>
          </select>
        </div>
        
        <div id="newSeriesContainer" style="display: none !important; margin-top: 15px;">
          <label for="newSeriesNameInput" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">New Series Name:</label>
          <input type="text" id="newSeriesNameInput" class="input-text" placeholder="e.g., YC-38 Series" style="width: 100%; padding: 8px;">
        </div>

        <div id="validationWarning" style="margin-top: 10px; padding: 8px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; font-size: 12px; color: #721c24; display: none;">
          <i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i>
          <strong>Required:</strong> <span id="validationMessage"></span>
        </div>
      </div>

      <div class="customization-fields-manager" id="fieldsManagerContainer" style="display: none;">
        ${fields.length === 0 ? '<p style="color: #999; text-align: center; padding: 20px;">No fields added yet. Click "Add Field" to start.</p>' : ''}
      </div>
      
      <div id="fieldActionsContainer" style="display: none; gap: 10px; margin-top: 15px;">
        <button type="button" class="add-series-btn" id="addCustomizationFieldBtn">
          <i class="fas fa-plus"></i> Add Field
        </button>
        <button type="button" class="add-series-btn" id="loadDefaultsBtn" style="background: #28a745; border-color: #28a745;">
          <i class="fas fa-magic"></i> Load Defaults
        </button>
      </div>
      
      <div class="popup-actions" style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; align-items: center; padding: 15px 0; border-top: 1px solid #e0e0e0;">
        <button class="save-btn" id="saveCustomizationFieldsBtn" style="padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 4px; cursor: pointer; border: none; background: #005b82; color: white; transition: background 0.3s;">
          Save Changes
        </button>
        <button type="button" class="cancel-btn" id="deleteSeriesBtn" style="background: #d9534f; border-color: #d9534f; color: white; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 4px; cursor: pointer; border: none; display: none; transition: background 0.3s;" title="Delete selected series">
          <i class="fas fa-trash"></i> Delete Series
        </button>
        <button class="cancel-btn" id="cancelManageFieldsBtn" style="padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 4px; cursor: pointer; border: 1px solid #005b82; background: white; color: #005b82; transition: all 0.3s;">
          Cancel
        </button>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  
  // Store current field key for reference
  modal.dataset.fieldKey = fieldKey;
  modal.dataset.category = category;
  modal.dataset.subcategory = subcategory;
  
  // Working copy of fields for editing
  let workingFields = JSON.parse(JSON.stringify(fields));
  window.currentWorkingFields = workingFields; // Store globally for edit function
  
  // Working copy of step names
  let workingStepNames = JSON.parse(JSON.stringify(stepNames));
  
  // Get series presets for this subcategory
  // Series are now segregated by subcategory: Series_Presets[subcategory][seriesName]
  if (!customizationFields["Series_Presets"]) {
    customizationFields["Series_Presets"] = {};
  }
  if (!customizationFields["Series_Presets"][subcategory]) {
    customizationFields["Series_Presets"][subcategory] = {};
  }
  const seriesPresets = customizationFields["Series_Presets"][subcategory] || {};
  
  // Get available series from predefined list OR from saved presets for this subcategory
  // First, check if subcategory has predefined series
  let availableSeries = subcategorySeries[subcategory] || [];
  
  // Also check for any series presets that exist for this specific subcategory
  Object.keys(seriesPresets).forEach(seriesName => {
    // Add series that exist in presets but not in predefined list
    if (!availableSeries.includes(seriesName) && seriesName !== "None") {
      availableSeries.push(seriesName);
    }
  });
  
  // Remove duplicates and filter out "None"
  availableSeries = [...new Set(availableSeries)].filter(s => s !== "None");
  
  let workingSeriesPresets = {};
  
  // Filter series presets for this subcategory only
  availableSeries.forEach(series => {
    if (seriesPresets[series]) {
      workingSeriesPresets[series] = JSON.parse(JSON.stringify(seriesPresets[series]));
    }
  });
  
  // Track dragged item index (shared across all items)
  let draggedIndex = null;
  
  // Maximum fields per step
  const MAX_FIELDS_PER_STEP = 4;
  
  // Helper function to determine drop position
  function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.field-manager-item:not(.dragging)')];
    
    return draggableElements.reduce((closest, child) => {
      const box = child.getBoundingClientRect();
      const offset = y - box.top - box.height / 2;
      
      if (offset < 0 && offset > closest.offset) {
        return { offset: offset, element: child };
      } else {
        return closest;
      }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
  }
  
  // Helper function to find which step container an element belongs to
  function getStepNumberFromElement(element) {
    // Traverse up to find the step container
    let current = element;
    while (current && current !== container) {
      if (current.classList && current.classList.contains('step-fields-container')) {
        return parseInt(current.dataset.stepNumber) || 1;
      }
      current = current.parentElement;
    }
    return null;
  }
  
  // Helper function to show confirmation dialog
  function showRemoveConfirmation(fieldLabel, onConfirm) {
    const modal = document.createElement('div');
    modal.className = 'confirmation-modal-overlay';
    modal.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10000;
    `;
    
    const dialog = document.createElement('div');
    dialog.className = 'confirmation-dialog';
    dialog.style.cssText = `
      background: white;
      padding: 24px;
      border-radius: 8px;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    `;
    
    dialog.innerHTML = `
      <div style="margin-bottom: 20px;">
        <h3 style="margin: 0 0 12px 0; color: #333; font-size: 18px; font-weight: 600;">
          <i class="fas fa-exclamation-triangle" style="color: #f0ad4e; margin-right: 8px;"></i>
          Confirm Removal
        </h3>
        <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.5;">
          Are you sure you want to remove the field <strong>"${fieldLabel}"</strong>? This action cannot be undone.
        </p>
      </div>
      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button class="cancel-btn" style="
          padding: 10px 20px;
          border: 1px solid #ddd;
          background: white;
          border-radius: 4px;
          cursor: pointer;
          font-size: 14px;
          color: #333;
        ">Cancel</button>
        <button class="confirm-btn" style="
          padding: 10px 20px;
          border: none;
          background: #d9534f;
          color: white;
          border-radius: 4px;
          cursor: pointer;
          font-size: 14px;
          font-weight: 600;
        ">Remove</button>
      </div>
    `;
    
    modal.appendChild(dialog);
    document.body.appendChild(modal);
    
    const cancelBtn = dialog.querySelector('.cancel-btn');
    const confirmBtn = dialog.querySelector('.confirm-btn');
    
    const closeModal = () => {
      document.body.removeChild(modal);
    };
    
    cancelBtn.onclick = closeModal;
    confirmBtn.onclick = () => {
      closeModal();
      onConfirm();
    };
    
    modal.onclick = (e) => {
      if (e.target === modal) {
        closeModal();
      }
    };
    
    // Close on Escape key
    const escapeHandler = (e) => {
      if (e.key === 'Escape') {
        closeModal();
        document.removeEventListener('keydown', escapeHandler);
      }
    };
    document.addEventListener('keydown', escapeHandler);
  }
  
  function checkMinimumFieldsRequirement() {
    const minFieldsWarning = document.getElementById("minFieldsWarning");

    if (!minFieldsWarning) return;

    // Group fields by step number
    const fieldsByStep = {};
    workingFields.forEach(field => {
      const step = field.stepNumber || 1;
      if (!fieldsByStep[step]) {
        fieldsByStep[step] = [];
      }
      fieldsByStep[step].push(field);
    });

    // Check if any step has fewer than 2 fields
    const hasInvalidStep = Object.values(fieldsByStep).some(fields => fields.length < 2);

    // Show/hide warning
    minFieldsWarning.style.display = hasInvalidStep ? "block" : "none";
  }

  function renderFieldsManager() {
    window.currentWorkingFields = workingFields; // Update global reference
    const container = document.getElementById("fieldsManagerContainer");

    // Check minimum fields per step requirement
    checkMinimumFieldsRequirement();
    if (!container) return;
    
    container.innerHTML = "";
    
    if (workingFields.length === 0) {
      container.innerHTML = '<p style="color: #999; text-align: center; padding: 20px;">No fields added yet. Click "Add Field" to start.</p>';
      return;
    }
    
    // Group fields by step
    const fieldsByStep = {};
    workingFields.forEach((field, index) => {
      const stepNum = field.stepNumber || 1;
      if (!fieldsByStep[stepNum]) {
        fieldsByStep[stepNum] = [];
      }
      fieldsByStep[stepNum].push({ field, originalIndex: index });
    });
    
    // Get all step numbers and sort them
    const stepNumbers = Object.keys(fieldsByStep).map(Number).sort((a, b) => a - b);
    
    // Render each step group
    stepNumbers.forEach(stepNum => {
      const stepFields = fieldsByStep[stepNum];
      const fieldCount = stepFields.length;
      const isOverLimit = fieldCount > MAX_FIELDS_PER_STEP;
      
      // Step header
      const stepHeader = document.createElement("div");
      stepHeader.className = "step-header";
      stepHeader.style.cssText = "background: #e8f4f8; padding: 12px; margin-bottom: 10px; border-radius: 6px; border-left: 4px solid #005b82;";
      
      const stepTitle = document.createElement("div");
      stepTitle.style.cssText = "display: flex; align-items: center; justify-content: space-between;";
      
      const stepTitleLeft = document.createElement("div");
      stepTitleLeft.style.cssText = "display: flex; align-items: center; gap: 10px;";
      
      const stepNumberBadge = document.createElement("span");
      stepNumberBadge.textContent = `Step ${stepNum}`;
      stepNumberBadge.style.cssText = "font-weight: 700; color: #005b82; font-size: 14px;";
      
      const stepNameInput = document.createElement("input");
      stepNameInput.type = "text";
      stepNameInput.className = "step-name-input";
      stepNameInput.placeholder = "Optional: Name this step (e.g., 'Basic Options', 'Advanced Settings')";
      stepNameInput.value = workingStepNames[stepNum] || "";
      stepNameInput.style.cssText = "flex: 1; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; max-width: 400px;";
      stepNameInput.addEventListener('change', (e) => {
        if (e.target.value.trim()) {
          workingStepNames[stepNum] = e.target.value.trim();
        } else {
          delete workingStepNames[stepNum];
        }
      });
      
      const fieldCountBadge = document.createElement("span");
      fieldCountBadge.textContent = `${fieldCount} field${fieldCount !== 1 ? 's' : ''}`;
      fieldCountBadge.style.cssText = `font-size: 12px; padding: 4px 8px; border-radius: 12px; font-weight: 600; ${
        isOverLimit 
          ? 'background: #ffe0e0; color: #d9534f;' 
          : fieldCount === MAX_FIELDS_PER_STEP
          ? 'background: #fff3cd; color: #856404;'
          : 'background: #d4edda; color: #155724;'
      }`;
      
      if (isOverLimit) {
        const warning = document.createElement("span");
        warning.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Over limit!';
        warning.style.cssText = "color: #d9534f; font-size: 12px; margin-left: 8px;";
        stepTitleLeft.appendChild(warning);
      }
      
      // Add remove step button (X) - icon on the right side
      const removeStepBtn = document.createElement("i");
      removeStepBtn.className = "fas fa-times";
      removeStepBtn.title = "Remove this step and all its fields";
      removeStepBtn.style.cssText = "cursor: pointer; font-size: 16px; color: #005b82; margin-left: 5px;";
      removeStepBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        showRemoveConfirmation(`Step ${stepNum}`, () => {
          // Remove all fields in this step
          workingFields = workingFields.filter(f => (f.stepNumber || 1) !== stepNum);
          // Remove step name
          delete workingStepNames[stepNum];
          // Re-number remaining steps to be sequential
          const remainingSteps = [...new Set(workingFields.map(f => f.stepNumber || 1))].sort((a, b) => a - b);
          remainingSteps.forEach((oldStepNum, index) => {
            const newStepNum = index + 1;
            if (oldStepNum !== newStepNum) {
              // Update fields in this step
              workingFields.forEach(f => {
                if ((f.stepNumber || 1) === oldStepNum) {
                  f.stepNumber = newStepNum;
                  f.step = newStepNum;
                }
              });
              // Update step names
              if (workingStepNames[oldStepNum]) {
                workingStepNames[newStepNum] = workingStepNames[oldStepNum];
                delete workingStepNames[oldStepNum];
              }
            }
          });
          renderFieldsManager();
        });
      });
      
      stepTitleLeft.appendChild(stepNumberBadge);
      stepTitleLeft.appendChild(stepNameInput);
      stepTitle.appendChild(stepTitleLeft);
      
      // Add field count badge and remove button on the right side
      const rightSide = document.createElement("div");
      rightSide.style.cssText = "display: flex; align-items: center; gap: 5px;";
      rightSide.appendChild(fieldCountBadge);
      rightSide.appendChild(removeStepBtn);
      stepTitle.appendChild(rightSide);
      stepHeader.appendChild(stepTitle);
      container.appendChild(stepHeader);
      
      // Create a container for fields in this step (for drag and drop between steps)
      const stepFieldsContainer = document.createElement("div");
      stepFieldsContainer.className = "step-fields-container";
      stepFieldsContainer.dataset.stepNumber = stepNum;
      stepFieldsContainer.style.cssText = "min-height: 20px; margin-bottom: 20px;";
      container.appendChild(stepFieldsContainer);
      
      // Add drag and drop handlers to the step container itself (for empty containers)
      stepFieldsContainer.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        if (draggedIndex !== null) {
          stepFieldsContainer.classList.add('drag-over-step');
        }
      });
      
      stepFieldsContainer.addEventListener('dragleave', (e) => {
        // Only remove highlight if we're not entering a child element
        if (!stepFieldsContainer.contains(e.relatedTarget)) {
          stepFieldsContainer.classList.remove('drag-over-step');
        }
      });
      
      stepFieldsContainer.addEventListener('drop', (e) => {
        e.preventDefault();
        stepFieldsContainer.classList.remove('drag-over-step');
        
        if (draggedIndex !== null) {
          const targetStepNumber = parseInt(stepFieldsContainer.dataset.stepNumber) || 1;
          const dragging = document.querySelector('.dragging');
          
          if (dragging) {
            // If the container is empty or we're dropping at the end
            stepFieldsContainer.appendChild(dragging);
            
            // Update the field's stepNumber
            const draggedField = workingFields[draggedIndex];
            if (draggedField) {
              draggedField.stepNumber = targetStepNumber;
              draggedField.step = targetStepNumber;
            }
            
            // Re-render to update the display
            renderFieldsManager();
          }
        }
      });
      
      // Render fields in this step
      stepFields.forEach(({ field, originalIndex }) => {
        const index = originalIndex;
      const item = document.createElement("div");
      item.className = "field-manager-item";
      item.dataset.index = index;
      item.dataset.fieldId = `field-${index}-${Date.now()}`; // Unique ID for tracking
      item.draggable = true;
      
      // Drag handle
      const dragHandle = document.createElement("div");
      dragHandle.className = "field-manager-drag-handle";
      dragHandle.innerHTML = '<i class="fas fa-grip-vertical"></i>';
      dragHandle.title = "Drag to reorder";
      
      const info = document.createElement("div");
      info.className = "field-manager-info";
      
      const label = document.createElement("div");
      label.className = "field-manager-label";
      label.textContent = field.label || `Field ${index + 1}`;
      
      const type = document.createElement("div");
      type.className = "field-manager-type";
      type.textContent = `${field.type}${field.options ? ` (${field.options.length} options)` : ''}`;
      
      // Remove step display since it's shown in header
      // const step = document.createElement("div");
      // step.className = "field-manager-step";
      // step.textContent = `Step ${field.stepNumber || 1}`;
      // step.style.cssText = "color: #005b82; font-weight: 600; font-size: 12px; margin-top: 4px;";
      
      info.appendChild(label);
      info.appendChild(type);
      // info.appendChild(step);
      
      const actions = document.createElement("div");
      actions.className = "field-manager-actions";
      
      const editBtn = document.createElement("button");
      editBtn.className = "field-manager-btn field-manager-edit";
      editBtn.type = "button";
      editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit';
      editBtn.onclick = () => {
        showAddCustomizationFieldModal(fieldKey, category, subcategory, (updatedField) => {
          workingFields[index] = updatedField;
          renderFieldsManager();
        }, field, index);
      };
      
      const removeBtn = document.createElement("button");
      removeBtn.className = "field-manager-btn field-manager-remove";
      removeBtn.type = "button";
      removeBtn.innerHTML = '<i class="fas fa-times"></i> Remove';
      removeBtn.onclick = () => {
        const fieldLabel = field.label || `Field ${index + 1}`;
        showRemoveConfirmation(fieldLabel, () => {
          workingFields.splice(index, 1);
          renderFieldsManager();
        });
      };
      
      const moveUpBtn = document.createElement("button");
      moveUpBtn.className = "field-manager-btn field-manager-move";
      moveUpBtn.type = "button";
      moveUpBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
      moveUpBtn.disabled = index === 0;
      moveUpBtn.title = "Move up";
      moveUpBtn.onclick = () => {
        if (index > 0) {
          [workingFields[index - 1], workingFields[index]] = [workingFields[index], workingFields[index - 1]];
          renderFieldsManager();
        }
      };
      
      const moveDownBtn = document.createElement("button");
      moveDownBtn.className = "field-manager-btn field-manager-move";
      moveDownBtn.type = "button";
      moveDownBtn.innerHTML = '<i class="fas fa-arrow-down"></i>';
      moveDownBtn.disabled = index === workingFields.length - 1;
      moveDownBtn.title = "Move down";
      moveDownBtn.onclick = () => {
        if (index < workingFields.length - 1) {
          [workingFields[index], workingFields[index + 1]] = [workingFields[index + 1], workingFields[index]];
          renderFieldsManager();
        }
      };
      
      // Drag and drop event handlers
      item.addEventListener('dragstart', (e) => {
        draggedIndex = index;
        item.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', index.toString());
        // Prevent text selection during drag
        e.dataTransfer.setDragImage(item, 0, 0);
        // Add a slight delay to allow the drag class to be applied
        setTimeout(() => {
          item.style.opacity = '0.5';
        }, 0);
      });
      
      item.addEventListener('dragend', (e) => {
        item.classList.remove('dragging');
        item.style.opacity = '';
        // Remove drag-over class from all items and step containers
        document.querySelectorAll('.field-manager-item').forEach(el => {
          el.classList.remove('drag-over');
        });
        document.querySelectorAll('.step-fields-container').forEach(step => {
          step.classList.remove('drag-over-step');
        });
        draggedIndex = null;
      });
      
      item.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        if (draggedIndex !== null) {
          // Find the step container this item belongs to
          const targetStepContainer = item.closest('.step-fields-container');
          if (!targetStepContainer) return;
          
          const afterElement = getDragAfterElement(targetStepContainer, e.clientY);
          const dragging = document.querySelector('.dragging');
          
          if (dragging) {
            // Only allow drop if it's in the same step container or different step
            if (afterElement == null) {
              targetStepContainer.appendChild(dragging);
            } else {
              targetStepContainer.insertBefore(dragging, afterElement);
            }
          }
          
          // Update visual feedback
          item.classList.add('drag-over');
          // Highlight the step container
          targetStepContainer.classList.add('drag-over-step');
        }
      });
      
      item.addEventListener('dragleave', (e) => {
        // Only remove if we're not entering a child element
        if (!item.contains(e.relatedTarget)) {
          item.classList.remove('drag-over');
        }
        // Remove step container highlight if leaving the step container
        const stepContainer = item.closest('.step-fields-container');
        if (stepContainer && !stepContainer.contains(e.relatedTarget)) {
          stepContainer.classList.remove('drag-over-step');
        }
      });
      
      item.addEventListener('drop', (e) => {
        e.preventDefault();
        item.classList.remove('drag-over');
        
        // Remove drag-over class from all step containers
        document.querySelectorAll('.step-fields-container').forEach(step => {
          step.classList.remove('drag-over-step');
        });
        
        if (draggedIndex !== null) {
          // Find which step container the item was dropped into
          const targetStepContainer = item.closest('.step-fields-container');
          if (!targetStepContainer) return;
          
          const targetStepNumber = parseInt(targetStepContainer.dataset.stepNumber) || 1;
          
          // Read the final order from DOM after dragover has rearranged elements
          // Process each step container separately
          const allStepContainers = Array.from(container.querySelectorAll('.step-fields-container'));
          const newOrder = [];
          
          allStepContainers.forEach(stepContainer => {
            const stepNum = parseInt(stepContainer.dataset.stepNumber) || 1;
            const stepItems = Array.from(stepContainer.querySelectorAll('.field-manager-item'));
            
            stepItems.forEach((domItem) => {
              const labelEl = domItem.querySelector('.field-manager-label');
              const typeEl = domItem.querySelector('.field-manager-type');
              
              if (labelEl && typeEl) {
                const label = labelEl.textContent;
                const typeText = typeEl.textContent;
                
                // Find the field that matches this DOM element
                const field = workingFields.find(f => {
                  const fieldTypeText = `${f.type}${f.options ? ` (${f.options.length} options)` : ''}`;
                  return f.label === label && fieldTypeText === typeText;
                });
                
                if (field) {
                  // Update the field's stepNumber to match the step it's in
                  field.stepNumber = stepNum;
                  field.step = stepNum; // Also update step property for consistency
                  newOrder.push(field);
                }
              }
            });
          });
          
          // Update workingFields with the new order and step assignments
          if (newOrder.length === workingFields.length) {
            workingFields.length = 0;
            workingFields.push(...newOrder);
            renderFieldsManager();
          }
        }
      });
      
      // Prevent drag on buttons and drag handle
      [editBtn, removeBtn, moveUpBtn, moveDownBtn].forEach(btn => {
        btn.addEventListener('mousedown', (e) => {
          e.stopPropagation();
        });
        // Prevent buttons from triggering drag
        btn.addEventListener('dragstart', (e) => {
          e.stopPropagation();
          e.preventDefault();
        });
      });
      
      // Make drag handle the drag trigger
      dragHandle.addEventListener('mousedown', (e) => {
        // Allow drag to start from handle
      });
      
      actions.appendChild(moveUpBtn);
      actions.appendChild(moveDownBtn);
      actions.appendChild(editBtn);
      actions.appendChild(removeBtn);
      
      item.appendChild(dragHandle);
      item.appendChild(info);
      item.appendChild(actions);
      stepFieldsContainer.appendChild(item);
      });
    });
  }
  
  
  // Series mode selection handlers - Available for ALL categories and subcategories
  // Works for all categories: Windows, Doors, Glass Partitions, Commercial, etc.
  // Even if no predefined series exist, admin can create new ones
  const useExistingRadio = document.getElementById("useExistingSeries");
  const createNewRadio = document.getElementById("createNewSeries");
  const seriesSelectorContainer = document.getElementById("seriesSelectorContainer");
  const newSeriesContainer = document.getElementById("newSeriesContainer");
  const manageSeriesSelect = document.getElementById("manageSeriesSelect");
  const newSeriesNameInput = document.getElementById("newSeriesNameInput");
  const fieldsContainer = document.getElementById("fieldsManagerContainer");
  const fieldActionsContainer = document.getElementById("fieldActionsContainer");
  
  // Populate series dropdown with available series
  availableSeries.forEach(series => {
    const option = document.createElement("option");
    option.value = series;
    option.textContent = series;
    manageSeriesSelect.appendChild(option);
  });
  
  // If no series exist yet, show message but still allow creating new ones
  if (availableSeries.length === 0) {
    const noSeriesOption = document.createElement("option");
    noSeriesOption.value = "";
    noSeriesOption.textContent = "No series yet - Create one below";
    noSeriesOption.disabled = true;
    manageSeriesSelect.appendChild(noSeriesOption);
  }
  
  // Track current mode and selected series
  let currentMode = null;
  let selectedSeriesName = savedSeries || null; // Initialize with saved series if available (for continuing edits)
  let filteredFields = [];
  
  // Determine which series to show: prioritize stored series from product, then current session selection
  let seriesToShow = storedSeries || selectedCustomizationSeries;

  console.log("=== SERIES SELECTION DEBUG ===");
  console.log("storedSeries:", storedSeries);
  console.log("selectedCustomizationSeries:", selectedCustomizationSeries);
  console.log("seriesToShow:", seriesToShow);
  console.log("availableSeries:", availableSeries);

  // Only pre-select a series if it was explicitly stored with this product or from current session
  if (seriesToShow && typeof seriesToShow === 'string' && seriesToShow.trim() !== '') {
    // Check case-insensitively in case of capitalization differences
    let matchingSeries = availableSeries.find(s => s.toLowerCase() === seriesToShow.toLowerCase());

    // If not found in available series, add it to the dropdown
    if (!matchingSeries) {
      const option = document.createElement("option");
      option.value = seriesToShow;
      option.textContent = seriesToShow;
      manageSeriesSelect.appendChild(option);
      availableSeries.push(seriesToShow);
      matchingSeries = seriesToShow;
    }

    // Set the series selection with a small delay to ensure DOM is ready
    setTimeout(() => {
      if (matchingSeries) {
        console.log(`✅ Setting dropdown value to: ${matchingSeries}`);
        console.log(`manageSeriesSelect exists:`, !!manageSeriesSelect);
        if (manageSeriesSelect) {
          console.log(`manageSeriesSelect options count:`, manageSeriesSelect.options.length);
          Array.from(manageSeriesSelect.options).forEach((opt, i) => {
            console.log(`Option ${i}: value="${opt.value}" text="${opt.text}"`);
          });
        }
        // Set the dropdown to the series
        manageSeriesSelect.value = matchingSeries;
        console.log(`Dropdown value after setting: ${manageSeriesSelect.value}`);

        // Auto-select "Use Existing Series" radio
        useExistingRadio.checked = true;
        currentMode = "existing";
        selectedSeriesName = matchingSeries; // Update selectedSeriesName
        // Show series selector container
        seriesSelectorContainer.style.display = "block";
        newSeriesContainer.style.display = "none";
        // Show delete button if series is selected
        const deleteSeriesBtn = document.getElementById("deleteSeriesBtn");
        if (deleteSeriesBtn) {
          deleteSeriesBtn.style.display = "block";
        }
        // Show fields for the selected series
        setTimeout(() => {
          showFieldsForMode();
        }, 100);
      }
    }, 50);
  } else {
    // No series to pre-select - leave unselected
    useExistingRadio.checked = false;
    seriesSelectorContainer.style.display = "none";
    newSeriesContainer.style.display = "none";
    fieldsContainer.style.display = "none";
    fieldActionsContainer.style.display = "none";
  }
  
  // Function to show fields based on mode
  const showFieldsForMode = () => {
      if (currentMode === "existing" && selectedSeriesName) {
        // Ensure workingFields has data - if empty, load from customizationFields
        if (!workingFields || workingFields.length === 0) {
          workingFields = JSON.parse(JSON.stringify(customizationFields[fieldKey] || []));
          window.currentWorkingFields = workingFields;
        }
        
        // Show ALL fields for the existing series (not filtered)
        // The preset values will be used to auto-fill, but all fields should be visible
        filteredFields = JSON.parse(JSON.stringify(workingFields));
        
        // Update thickness options based on selected series
        updateThicknessOptionsForSeries(selectedSeriesName);
        
        // Update thickness in fields
        const thicknessField = filteredFields.find(f => f.id === "thickness");
        if (thicknessField) {
          const seriesThicknessOptions = getThicknessOptionsForSeries(selectedSeriesName);
          if (seriesThicknessOptions.length > 0) {
            thicknessField.options = seriesThicknessOptions;
          }
        }
        
        // Temporarily replace workingFields for rendering
        const originalWorkingFields = workingFields;
        workingFields = filteredFields;
        
        renderFieldsManager();
        
        // After rendering, apply preset values to the fields
        setTimeout(() => {
          const seriesPreset = workingSeriesPresets[selectedSeriesName] || {};
          if (Object.keys(seriesPreset).length > 0) {
            // Apply preset values to tag fields
            Object.keys(seriesPreset).forEach(fieldId => {
              const presetValue = seriesPreset[fieldId];
              const hiddenInput = document.getElementById(fieldId);
              if (hiddenInput && Array.isArray(presetValue)) {
                hiddenInput.value = JSON.stringify(presetValue);
                // Trigger tag rendering update
                const tagContainer = document.querySelector(`#fieldsManagerContainer [data-field-id="${fieldId}"] .tag-container`);
                if (tagContainer) {
                  const field = filteredFields.find(f => f.id === fieldId);
                  if (field && field.options) {
                    renderTags(tagContainer, field.options, "", fieldId);
                  }
                }
              } else {
                // For number and other input types
                const input = document.getElementById(fieldId);
                if (input) {
                  if (typeof presetValue === 'number') {
                    input.value = presetValue;
                  } else if (typeof presetValue === 'boolean') {
                    input.checked = presetValue;
                  }
                }
              }
            });
          }
        }, 200);
        
        workingFields = originalWorkingFields; // Restore original for editing
      } else if (currentMode === "new") {
        // Show blank fields for new series creation (admin needs to create from scratch)
        filteredFields = [];
        workingFields = [];
        window.currentWorkingFields = [];
        renderFieldsManager();
      } else {
        // Hide fields
        fieldsContainer.style.display = "none";
        fieldActionsContainer.style.display = "none";
        return;
      }
      
      // Show fields and actions
      fieldsContainer.style.display = "block";
      fieldActionsContainer.style.cssText = "display: flex; gap: 10px; margin-top: 15px;";
    };
    
    // If saved series exists, show fields for it after showFieldsForMode is defined
    if (savedSeries && availableSeries.includes(savedSeries)) {
      setTimeout(() => {
        showFieldsForMode();
      }, 100);
    }
  
  // Use Existing Series handler
  useExistingRadio.addEventListener("change", () => {
      if (useExistingRadio.checked) {
        currentMode = "existing";
        seriesSelectorContainer.style.display = "block";
        newSeriesContainer.style.display = "none";
        manageSeriesSelect.disabled = false;
        newSeriesNameInput.value = "";

        // Hide validation warning when user makes a selection
        const validationWarning = document.getElementById("validationWarning");
        if (validationWarning) {
          validationWarning.style.display = "none";
        }

        // Hide delete button until a series is selected
        const deleteSeriesBtn = document.getElementById("deleteSeriesBtn");
        if (deleteSeriesBtn) {
          deleteSeriesBtn.style.display = "none";
        }
        
      // Hide fields until series is selected
      fieldsContainer.style.display = "none";
      fieldActionsContainer.style.display = "none";
    }
  });
  
  // Create New Series handler
  createNewRadio.addEventListener("change", () => {
      if (createNewRadio.checked) {
        currentMode = "new";
        seriesSelectorContainer.style.display = "none";
        manageSeriesSelect.disabled = true;
        manageSeriesSelect.value = "";
        selectedSeriesName = null;

        // Hide validation warning when user makes a selection
        const validationWarning = document.getElementById("validationWarning");
        if (validationWarning) {
          validationWarning.style.display = "none";
        }

        // Hide delete button when creating new series
        if (deleteSeriesBtn) {
          deleteSeriesBtn.style.display = "none";
        }
        
        // Hide new series input initially (will show when user types)
        newSeriesContainer.style.display = "none";
        newSeriesNameInput.value = "";
        
      // Show blank fields for new series creation
      showFieldsForMode();
    }
  });
  
  // Show/hide new series input based on value
  newSeriesNameInput.addEventListener("input", () => {
      // Hide validation warning when user types
      const validationWarning = document.getElementById("validationWarning");
      if (validationWarning) {
        validationWarning.style.display = "none";
      }

      if (newSeriesNameInput.value.trim()) {
        newSeriesContainer.style.display = "block";
      } else {
        newSeriesContainer.style.display = "none";
      }
  });
  
  // Also check on focus - show the container when user starts typing
  newSeriesNameInput.addEventListener("focus", () => {
      newSeriesContainer.style.display = "block";
    });
    
    // Hide on blur if empty
    newSeriesNameInput.addEventListener("blur", () => {
      if (!newSeriesNameInput.value.trim()) {
      newSeriesContainer.style.display = "none";
    }
  });
  
  // Ensure it's hidden on initial load
  setTimeout(() => {
    if (newSeriesContainer && !newSeriesNameInput.value.trim()) {
      newSeriesContainer.style.display = "none";
    }

    // Hide validation warning on modal open
    const validationWarning = document.getElementById("validationWarning");
    if (validationWarning) {
      validationWarning.style.display = "none";
    }
  }, 100);
  
  // Series selection handler
  const deleteSeriesBtn = document.getElementById("deleteSeriesBtn");
  manageSeriesSelect.addEventListener("change", () => {
      selectedSeriesName = manageSeriesSelect.value;

      // Hide validation warning when user makes a selection
      const validationWarning = document.getElementById("validationWarning");
      if (validationWarning) {
        validationWarning.style.display = "none";
      }

      if (selectedSeriesName) {
        // Show delete button when a series is selected
        if (deleteSeriesBtn) {
          deleteSeriesBtn.style.display = "block";
        }
        showFieldsForMode();
        // Update thickness options based on selected series
        updateThicknessOptionsForSeries(selectedSeriesName);
      } else {
        // Hide delete button when no series is selected
        if (deleteSeriesBtn) {
          deleteSeriesBtn.style.display = "none";
        }
      fieldsContainer.style.display = "none";
      fieldActionsContainer.style.display = "none";
    }
  });
  
  // Delete series button handler
  if (deleteSeriesBtn) {
    deleteSeriesBtn.addEventListener("click", () => {
        const seriesToDelete = manageSeriesSelect.value;
        if (!seriesToDelete) {
          showToast("Please select a series to delete.", 'error');
          return;
        }
        
        if (confirm(`Are you sure you want to delete the series "${seriesToDelete}"? This action cannot be undone.`)) {
          // Remove from workingSeriesPresets
          if (workingSeriesPresets[seriesToDelete]) {
            delete workingSeriesPresets[seriesToDelete];
          }
          
          // Remove from this subcategory's Series_Presets
          if (customizationFields["Series_Presets"] && 
              customizationFields["Series_Presets"][subcategory] && 
              customizationFields["Series_Presets"][subcategory][seriesToDelete]) {
            delete customizationFields["Series_Presets"][subcategory][seriesToDelete];
          }
          
          // Remove from dropdown
          const optionToRemove = Array.from(manageSeriesSelect.options).find(opt => opt.value === seriesToDelete);
          if (optionToRemove) {
            manageSeriesSelect.removeChild(optionToRemove);
          }
          
          // Reset selection
          manageSeriesSelect.value = "";
          selectedSeriesName = null;
          deleteSeriesBtn.style.display = "none";
          
          // Hide fields
          fieldsContainer.style.display = "none";
          fieldActionsContainer.style.display = "none";
          
          showToast(`Series "${seriesToDelete}" has been deleted.`, 'success');
          
      // Note: The deletion will be saved when the user clicks "Save Changes"
    }
  });
  }
  
  // Helper function to get thickness options for a series
  function getThicknessOptionsForSeries(seriesName) {
      if (seriesName === "YC-38 Series") {
        return ["6mm"];
      } else if (seriesName === "YC-50 Series") {
        return ["6mm", "8mm"];
      } else if (seriesName === "60-DMX Series" || seriesName === "85 Series" || seriesName === "75 Series") {
        return ["6mm", "8mm", "10mm", "12mm"];
      }
    return [];
  }
  
  // Function to update thickness field options based on series
  function updateThicknessOptionsForSeries(seriesName) {
    const thicknessOptions = getThicknessOptionsForSeries(seriesName);
    
    if (thicknessOptions.length === 0) return;
    
    // Find and update thickness field in workingFields
    const thicknessField = workingFields.find(f => f.id === "thickness");
    if (thicknessField) {
      thicknessField.options = thicknessOptions;
    }
  }
  
  // All subcategories now have series management available
  // Fields will be shown based on series mode selection above
  
  // Load defaults button - loads from JSON file
  document.getElementById("loadDefaultsBtn").onclick = async () => {
    if (workingFields.length > 0) {
      if (!confirm('Loading defaults will replace all current fields. Continue?')) {
        return;
      }
    }

    try {
      // Load from JSON file instead of database
      const defaultFields = await getDefaultCustomizationFields();
      
      if (defaultFields[fieldKey] && defaultFields[fieldKey].length > 0) {
        workingFields = JSON.parse(JSON.stringify(defaultFields[fieldKey]));
        window.currentWorkingFields = workingFields;

        // Also load step names if available
        const stepNamesKey = `${fieldKey}_stepNames`;
        if (defaultFields[stepNamesKey]) {
          workingStepNames = JSON.parse(JSON.stringify(defaultFields[stepNamesKey]));
        } else if (customizationFields[stepNamesKey]) {
          workingStepNames = JSON.parse(JSON.stringify(customizationFields[stepNamesKey]));
        }

        renderFieldsManager();
        showToast('Defaults loaded successfully from JSON file!', 'success');
      } else {
        showToast('No defaults available for this category/subcategory in JSON file.', 'info');
      }
    } catch (error) {
      console.error('Error loading defaults from JSON:', error);
      showToast('Error loading defaults. Please try again.', 'error');
    }
  };

  // Add field button
  document.getElementById("addCustomizationFieldBtn").onclick = () => {
    // Check which steps are available or need new fields
    const stepNumbers = [...new Set(workingFields.map(f => f.stepNumber || 1))].sort((a, b) => a - b);
    const lastStep = stepNumbers.length > 0 ? Math.max(...stepNumbers) : 0;
    
    // Check if last step is full
    const lastStepFields = workingFields.filter(f => (f.stepNumber || 1) === lastStep);
    const suggestedStep = lastStepFields.length >= MAX_FIELDS_PER_STEP ? lastStep + 1 : lastStep || 1;
    
    showAddCustomizationFieldModal(fieldKey, category, subcategory, (newField) => {
      // Validate step limit
      const targetStep = newField.stepNumber || 1;
      const stepFields = workingFields.filter(f => (f.stepNumber || 1) === targetStep);
      
      if (stepFields.length >= MAX_FIELDS_PER_STEP) {
        showToast(`Step ${targetStep} already has ${MAX_FIELDS_PER_STEP} fields. Maximum ${MAX_FIELDS_PER_STEP} fields per step recommended.`, 'error');
        // Still allow adding but show warning
      }
      
      workingFields.push(newField);
      renderFieldsManager();
    }, null, null, suggestedStep);
  };
  
  // Save button
  document.getElementById("saveCustomizationFieldsBtn").onclick = async () => {
    try {
      // Validation: Check if series is properly configured
      const useExistingRadio = document.getElementById("useExistingSeries");
      const createNewRadio = document.getElementById("createNewSeries");
      const manageSeriesSelect = document.getElementById("manageSeriesSelect");
      const newSeriesNameInput = document.getElementById("newSeriesNameInput");

      let hasValidSeriesSelection = false;

      if (useExistingRadio && useExistingRadio.checked) {
        // Check if existing series is selected
        if (manageSeriesSelect && manageSeriesSelect.value) {
          hasValidSeriesSelection = true;
        }
      } else if (createNewRadio && createNewRadio.checked) {
        // Check if new series name is provided
        if (newSeriesNameInput && newSeriesNameInput.value.trim()) {
          hasValidSeriesSelection = true;
        }
      }

      if (!hasValidSeriesSelection) {
        const validationWarning = document.getElementById("validationWarning");
        const validationMessage = document.getElementById("validationMessage");

        if (useExistingRadio && useExistingRadio.checked) {
          validationMessage.textContent = "Please select an existing series from the dropdown.";
        } else if (createNewRadio && createNewRadio.checked) {
          validationMessage.textContent = "Please enter a name for the new series.";
        } else {
          validationMessage.textContent = "Please select 'Use Existing Series' or 'Create New Series'.";
        }

        if (validationWarning) {
          validationWarning.style.display = "block";
        }
        return;
      } else {
        // Hide validation warning if series selection becomes valid
        const validationWarning = document.getElementById("validationWarning");
        if (validationWarning) {
          validationWarning.style.display = "none";
        }
      }

      // Save series presets if creating new series
      // Available for ALL categories and subcategories
      // Series are segregated by subcategory: Series_Presets[subcategory][seriesName]
      // createNewRadio and newSeriesNameInput already declared above for validation
      
      if (createNewRadio && createNewRadio.checked && newSeriesNameInput && newSeriesNameInput.value.trim()) {
        const newSeriesName = newSeriesNameInput.value.trim();
        if (!customizationFields["Series_Presets"]) {
          customizationFields["Series_Presets"] = {};
        }
        if (!customizationFields["Series_Presets"][subcategory]) {
          customizationFields["Series_Presets"][subcategory] = {};
        }
        
        // Create preset from current field selections
        const newPreset = {};
        const tagContainers = document.querySelectorAll('#fieldsManagerContainer .tag-container');
        tagContainers.forEach(container => {
          const fieldId = container.dataset.fieldId;
          if (fieldId) {
            const hiddenInput = document.getElementById(fieldId);
            if (hiddenInput) {
              const selectedValues = JSON.parse(hiddenInput.value || "[]");
              if (selectedValues.length > 0) {
                newPreset[fieldId] = selectedValues;
              }
            }
          }
        });
        
        // Also collect number and checkbox values
        document.querySelectorAll('#fieldsManagerContainer input[type="number"]').forEach(input => {
          if (input.value) {
            newPreset[input.name] = parseFloat(input.value) || 0;
          }
        });
        
        document.querySelectorAll('#fieldsManagerContainer input[type="checkbox"]').forEach(input => {
          newPreset[input.name] = input.checked;
        });
        
        // Store series under its subcategory
        customizationFields["Series_Presets"][subcategory][newSeriesName] = newPreset;
        console.log('New series preset created for subcategory:', subcategory, 'series:', newSeriesName, newPreset);
      }
      
      // Collect selected tags from all tag fields in the modal
      const selectedTagsKey = `${fieldKey}_selectedTags`;
      const selectedTags = {};
      
      // Find all tag containers in the fields manager
      const tagContainers = document.querySelectorAll('#fieldsManagerContainer .tag-container');
      tagContainers.forEach(container => {
        const fieldId = container.dataset.fieldId;
        if (fieldId) {
          // Find the hidden input for this field
          const hiddenInput = document.getElementById(fieldId);
          if (hiddenInput) {
            const selectedValues = JSON.parse(hiddenInput.value || "[]");
            if (selectedValues.length > 0) {
              selectedTags[fieldId] = selectedValues;
            }
          }
        }
      });
      
      // Update the customization fields with working copy
      customizationFields[fieldKey] = workingFields;
      
      // Save step names
      customizationFields[stepNamesKey] = workingStepNames;
      
      // Save selected tags
      customizationFields[selectedTagsKey] = selectedTags;
      
      // Save the selected series for this subcategory
      const savedSeriesKey = `${fieldKey}_selectedSeries`;

      // Determine which series was selected/created (use variables declared above)
      let selectedSeriesToSave = null;
      if (createNewRadio && createNewRadio.checked && newSeriesNameInput && newSeriesNameInput.value.trim()) {
        // New series was created
        selectedSeriesToSave = newSeriesNameInput.value.trim();
      } else if (manageSeriesSelect && manageSeriesSelect.value) {
        // Existing series was selected
        selectedSeriesToSave = manageSeriesSelect.value;
      }
      
      // Save the selected series
      if (selectedSeriesToSave) {
        customizationFields[savedSeriesKey] = selectedSeriesToSave;
        console.log('Saved selected series for', fieldKey, ':', selectedSeriesToSave);
      } else {
        // Clear saved series if none selected
        delete customizationFields[savedSeriesKey];
      }
      
      // Save series presets (including deletions) for ALL subcategories
      // Series management is available for all categories and subcategories
      // Series are segregated by subcategory: Series_Presets[subcategory][seriesName]
      if (!customizationFields["Series_Presets"]) {
        customizationFields["Series_Presets"] = {};
      }
      if (!customizationFields["Series_Presets"][subcategory]) {
        customizationFields["Series_Presets"][subcategory] = {};
      }
      // Update Series_Presets for this subcategory with workingSeriesPresets (deletions are already reflected)
      Object.keys(workingSeriesPresets).forEach(seriesName => {
        customizationFields["Series_Presets"][subcategory][seriesName] = workingSeriesPresets[seriesName];
      });
      // Also remove deleted series from this subcategory's Series_Presets
      const allSeriesInPresets = Object.keys(customizationFields["Series_Presets"][subcategory] || {});
      allSeriesInPresets.forEach(seriesName => {
        if (!workingSeriesPresets[seriesName] && availableSeries.includes(seriesName)) {
          delete customizationFields["Series_Presets"][subcategory][seriesName];
        }
      });
      
      // Save to localStorage first
      try {
        localStorage.setItem(CUSTOMIZATION_FIELDS_STORAGE_KEY, JSON.stringify(customizationFields));
      } catch (e) {
        console.warn('localStorage access blocked by browser (Tracking Prevention):', e.message);
      }
      
      // Save to database and wait for completion
      const saveSuccess = await saveCustomizationFieldsToDatabase(fieldKey, workingFields, category, subcategory);
      
      // Also save selectedTags to database as a separate entry
      if (Object.keys(selectedTags).length > 0) {
        await saveSelectedTagsToDatabase(fieldKey, selectedTags, category, subcategory);
      }
      
      if (!saveSuccess) {
        showToast("Failed to save customization fields to database. Please try again.", 'error');
        return;
      }
      
      // Regenerate fields in the form if the add popup is open
      const addSubcategorySelect = document.getElementById("productSubcategory");
      const addCategorySelect = document.getElementById("productCategory");
      const addCustomizationContainer = document.getElementById("customizationFields");
      
      if (addSubcategorySelect && addCategorySelect && addCustomizationContainer) {
        const selectedSubcategory = addSubcategorySelect.value;
        const selectedCategory = addCategorySelect.value;
        if (selectedSubcategory && selectedCategory && selectedSubcategory === subcategory && selectedCategory === category) {
          generateCustomizationFields(selectedSubcategory, addCustomizationContainer, "", selectedCategory);
          
          // No auto-application of series in Add New Product
          // Admin must manually select series every time
        }
      }
      
      // Regenerate fields in the "Add Direct Order Option" modal if open
      const standardOptionContainer = document.getElementById("standardOptionCustomizationFields");
      if (standardOptionContainer) {
        generateCustomizationFields(subcategory, standardOptionContainer, "standardOption_", category);
      }
      
      // Regenerate fields in the "Edit Direct Order Option" modal if open
      const editStandardOptionContainer = document.getElementById("editStandardOptionCustomizationFields");
      if (editStandardOptionContainer) {
        generateCustomizationFields(subcategory, editStandardOptionContainer, "editStandardOption_", category);
      }
      
      showToast("Customization fields saved successfully!", 'success');
      modal.remove();
    } catch (error) {
      console.error('Error saving customization fields:', error);
      showToast("An error occurred while saving. Please try again.", 'error');
    }
  };
  
  // Cancel button
  document.getElementById("cancelManageFieldsBtn").onclick = () => modal.remove();
  document.getElementById("closeManageFieldsModal").onclick = () => modal.remove();
  modal.onclick = (e) => {
    if (e.target === modal) modal.remove();
  };
}

/**
 * Shows modal to add/edit a customization field
 */
function showAddCustomizationFieldModal(fieldKey, category, subcategory, onSave, existingField = null, fieldIndex = null, suggestedStep = null) {
  const isEdit = existingField !== null;
  const MAX_FIELDS_PER_STEP = 4; // Maximum fields per step
  
  // Get current fields to count per step
  const currentFields = window.currentWorkingFields || [];
  const stepCounts = {};
  currentFields.forEach(f => {
    const step = f.stepNumber || 1;
    stepCounts[step] = (stepCounts[step] || 0) + 1;
  });
  
  // Generate step options with counts
  const maxSteps = 10;
  let stepOptions = '';
  for (let i = 1; i <= maxSteps; i++) {
    const count = stepCounts[i] || 0;
    const isSelected = existingField 
      ? (existingField.stepNumber || 1) === i
      : (suggestedStep === i || (!suggestedStep && i === 1));
    const warning = count >= MAX_FIELDS_PER_STEP ? ' ⚠️ (Full)' : count > 0 ? ` (${count} fields)` : '';
    stepOptions += `<option value="${i}" ${isSelected ? 'selected' : ''}>Step ${i}${warning}</option>`;
  }
  
  const modal = document.createElement("div");
  modal.className = "popup-overlay";
  modal.style.display = "flex";
  modal.innerHTML = `
    <div class="popup" style="width: 500px;">
      <span class="close-btn" id="closeFieldModal">&times;</span>
      <h3>${isEdit ? 'Edit' : 'Add'} Customization Field</h3>
      <div class="form-group">
        <label for="fieldLabelInput">Field Label</label>
        <input type="text" id="fieldLabelInput" class="text-input" placeholder="e.g., Glass Type" value="${existingField?.label || ''}">
      </div>
      <div class="form-group">
        <label for="fieldTypeSelect">Field Type</label>
        <select id="fieldTypeSelect" class="input-text">
          <option value="tags" ${existingField?.type === 'tags' ? 'selected' : ''}>Tags (Multiple Selection)</option>
          <option value="checkbox" ${existingField?.type === 'checkbox' ? 'selected' : ''}>Checkbox (Yes/No)</option>
          <option value="number" ${existingField?.type === 'number' ? 'selected' : ''}>Number Input</option>
          <option value="dimensions" ${existingField?.type === 'dimensions' ? 'selected' : ''}>Dimensions (Width, Height, h1)</option>
          <option value="color" ${existingField?.type === 'color' ? 'selected' : ''}>Color Picker</option>
        </select>
      </div>
      <div class="form-group" id="fieldOptionsGroup" style="display: ${!existingField || existingField?.type === 'tags' ? 'block' : 'none'};">
        <label>Options (for Tags)</label>
        <div id="fieldOptionsContainer" style="margin-top: 8px;">
          ${existingField?.options ? existingField.options.map(opt => `<div style="display: flex; gap: 8px; margin-bottom: 6px;"><input type="text" class="text-input option-input" value="${opt}" style="flex: 1;"><button type="button" class="remove-option-btn" style="background: #ffe0e0; color: #d9534f; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">×</button></div>`).join('') : '<div style="display: flex; gap: 8px; margin-bottom: 6px;"><input type="text" class="text-input option-input" placeholder="Enter option name" style="flex: 1;"><button type="button" class="remove-option-btn" style="background: #ffe0e0; color: #d9534f; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">×</button></div>'}
        </div>
        <button type="button" class="add-series-btn" id="addOptionBtn" style="margin-top: 8px;">
          <i class="fas fa-plus"></i> Add Option
        </button>
      </div>
      <div class="form-group" id="fieldNumberGroup" style="display: ${existingField?.type === 'number' ? 'block' : 'none'};">
        <label for="fieldMinInput">Minimum Value</label>
        <input type="number" id="fieldMinInput" class="input-text" value="${existingField?.min || 0}" step="0.1">
        <label for="fieldStepInput" style="margin-top: 8px;">Step</label>
        <input type="number" id="fieldStepInput" class="input-text" value="${existingField?.step || 1}" step="0.1">
      </div>
      <div class="form-group">
        <label for="fieldStepSelect">Step Number</label>
        <select id="fieldStepSelect" class="input-text">
          ${stepOptions}
        </select>
        <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">
          Select which step this field should appear in. Maximum ${MAX_FIELDS_PER_STEP} fields per step recommended.
        </small>
        <div id="stepWarning" style="display: none; color: #d9534f; font-size: 12px; margin-top: 4px; padding: 6px; background: #ffe0e0; border-radius: 4px;">
          <i class="fas fa-exclamation-triangle"></i> This step already has ${MAX_FIELDS_PER_STEP} fields. Consider using a new step.
        </div>
      </div>
      <div class="popup-actions">
        <button class="save-btn" id="confirmFieldBtn">${isEdit ? 'Update' : 'Add'} Field</button>
        <button class="cancel-btn" id="cancelFieldBtn">Cancel</button>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  
  const fieldTypeSelect = document.getElementById("fieldTypeSelect");
  const fieldOptionsGroup = document.getElementById("fieldOptionsGroup");
  const fieldNumberGroup = document.getElementById("fieldNumberGroup");
  const optionsContainer = document.getElementById("fieldOptionsContainer");
  
  // Show/hide options based on field type
  fieldTypeSelect.addEventListener("change", () => {
        const fieldType = fieldTypeSelect.value;
        fieldOptionsGroup.style.display = fieldType === "tags" ? "block" : "none";
        fieldNumberGroup.style.display = fieldType === "number" ? "block" : "none";
        
        // For dimensions type, show info message
        if (fieldType === "dimensions") {
          if (!document.getElementById("dimensionsInfo")) {
            const infoDiv = document.createElement("div");
            infoDiv.id = "dimensionsInfo";
            infoDiv.style.cssText = "margin-top: 10px; padding: 10px; background: #e8f4f8; border-radius: 4px; color: #005b82; font-size: 12px;";
            infoDiv.innerHTML = '<i class="fas fa-info-circle"></i> Dimensions field will display Width, Height, and h1 (conditional) input fields to customers in one row.';
            fieldTypeSelect.parentElement.appendChild(infoDiv);
          }
        } else {
          const infoDiv = document.getElementById("dimensionsInfo");
          if (infoDiv) infoDiv.remove();
        }
      });
      
      // Trigger change event to show/hide appropriate fields on load
      if (existingField?.type) {
        fieldTypeSelect.dispatchEvent(new Event('change'));
      }
  
  // Step selection warning
  const stepSelect = document.getElementById("fieldStepSelect");
  const stepWarning = document.getElementById("stepWarning");
  const updateStepWarning = () => {
    const selectedStep = parseInt(stepSelect.value) || 1;
    const count = stepCounts[selectedStep] || 0;
    // Don't count current field if editing
    const actualCount = isEdit && (existingField?.stepNumber || 1) === selectedStep ? count - 1 : count;
    const MAX_FIELDS_PER_STEP = 4;
    
    if (actualCount >= MAX_FIELDS_PER_STEP) {
      stepWarning.style.display = "block";
      stepWarning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> This step already has ${actualCount} fields (max ${MAX_FIELDS_PER_STEP} recommended). Consider using a new step.`;
    } else {
      stepWarning.style.display = "none";
    }
  };
  
  stepSelect.addEventListener("change", updateStepWarning);
  updateStepWarning(); // Initial check
  
  // Add option
  document.getElementById("addOptionBtn").onclick = () => {
    const optionDiv = document.createElement("div");
    optionDiv.style.cssText = "display: flex; gap: 8px; margin-bottom: 6px;";
    const input = document.createElement("input");
    input.type = "text";
    input.className = "text-input option-input";
    input.style.flex = "1";
    input.placeholder = "Enter option name";
    const removeBtn = document.createElement("button");
    removeBtn.type = "button";
    removeBtn.className = "remove-option-btn";
    removeBtn.style.cssText = "background: #ffe0e0; color: #d9534f; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;";
    removeBtn.textContent = "×";
    removeBtn.onclick = () => optionDiv.remove();
    optionDiv.appendChild(input);
    optionDiv.appendChild(removeBtn);
    optionsContainer.appendChild(optionDiv);
  };
  
  // Remove option handlers
  optionsContainer.querySelectorAll(".remove-option-btn").forEach(btn => {
    btn.onclick = () => btn.parentElement.remove();
  });
  
  // Save field
  document.getElementById("confirmFieldBtn").onclick = () => {
    const label = document.getElementById("fieldLabelInput").value.trim();
    const type = fieldTypeSelect.value;
    
    if (!label) {
      showToast("Please enter a field label.", 'error');
      return;
    }
    
    // Generate unique ID if editing or creating new
    let fieldId = existingField?.id;
    if (!fieldId) {
      // Create ID from label, ensure uniqueness
      fieldId = label.toLowerCase().replace(/\s+/g, '').replace(/[^a-z0-9]/g, '');
      // Add timestamp if needed for uniqueness
      const allFields = customizationFields[fieldKey] || [];
      const existingIds = allFields.map(f => f.id);
      let counter = 1;
      let uniqueId = fieldId;
      while (existingIds.includes(uniqueId)) {
        uniqueId = fieldId + counter;
        counter++;
      }
      fieldId = uniqueId;
    }
    
    const stepNumber = parseInt(document.getElementById("fieldStepSelect").value) || 1;
    const MAX_FIELDS_PER_STEP = 4;
    
    // Validate step limit (warn but allow)
    const count = stepCounts[stepNumber] || 0;
    const actualCount = isEdit && (existingField?.stepNumber || 1) === stepNumber ? count - 1 : count;
    if (actualCount >= MAX_FIELDS_PER_STEP && !isEdit) {
      const proceed = confirm(`Step ${stepNumber} already has ${actualCount} fields. Maximum ${MAX_FIELDS_PER_STEP} fields per step is recommended for better customer experience. Do you want to continue anyway?`);
      if (!proceed) {
        return;
      }
    }
    
    const field = {
      type: type,
      label: label,
      id: fieldId,
      stepNumber: stepNumber
    };
    
    if (type === "tags") {
      const optionInputs = optionsContainer.querySelectorAll(".option-input");
      const options = Array.from(optionInputs).map(input => input.value.trim()).filter(v => v);
      if (options.length === 0) {
        showToast("Please add at least one option for tags field.", 'error');
        return;
      }
      field.options = options;
    } else if (type === "number") {
      field.min = parseFloat(document.getElementById("fieldMinInput").value) || 0;
      field.step = parseFloat(document.getElementById("fieldStepInput").value) || 1;
    } else if (type === "dimensions") {
      // Dimensions field - special type that renders Width, Height, and h1 inputs
      field.fields = ["width", "height", "h1"];
      field.h1Conditional = existingField?.h1Conditional || {
        dependsOn: "",
        showWhen: []
      };
    } else if (type === "color") {
      field.default = existingField?.default || "#000000";
    }
    
    onSave(field, fieldIndex);
    modal.remove();
  };
  
  document.getElementById("cancelFieldBtn").onclick = () => modal.remove();
  document.getElementById("closeFieldModal").onclick = () => modal.remove();
  modal.onclick = (e) => {
    if (e.target === modal) modal.remove();
  };
}


/**
 * Generates dynamic customization fields based on selected subcategory
 * @param {string} subcategory - Selected subcategory
 * @param {HTMLElement} container - Container element to append fields to
 * @param {string} prefix - Prefix for field IDs (e.g., "add" or "edit")
 * @param {string} category - Selected main category (to resolve conflicts)
 */
function generateCustomizationFields(subcategory, container, prefix = "", category = "") {
  // Clear existing fields
  container.innerHTML = "";

  if (!subcategory) {
    return;
  }

  // Determine which field set to use based on category and subcategory
  // Build composite key from category and subcategory to handle conflicts
  let fieldKey;

  if (category === "Windows") {
    fieldKey = `Windows_${subcategory}`;
  } else if (category === "Doors") {
    fieldKey = `Doors_${subcategory}`;
  } else if (category === "Glass Partitions & Enclosures") {
    fieldKey = `Partitions_${subcategory}`;
  } else if (category === "Mirrors & Specialty Glass") {
    fieldKey = `Specialty_${subcategory}`;
  } else if (category === "Commercial & Exterior") {
    fieldKey = `Commercial_${subcategory}`;
  } else {
    fieldKey = subcategory; // Fallback to subcategory name
  }

  const fields = customizationFields[fieldKey];

  if (!fields) {
    // Show loading message and retry after a short delay
    container.innerHTML = '<p style="color: #666; font-style: italic;">Loading customization fields...</p>';
    setTimeout(() => {
      generateCustomizationFields(subcategory, container, prefix, category);
    }, 500);
    return;
  }
  
  // Get step names
  const stepNamesKey = `${fieldKey}_stepNames`;
  const stepNames = customizationFields[stepNamesKey] || {};
  
  // Get saved series for this subcategory (for Add New Product form)
  const savedSeriesKey = `${fieldKey}_selectedSeries`;
  const savedSeries = customizationFields[savedSeriesKey] || null;
  
  // Group fields by step
  const fieldsByStep = {};
  fields.forEach(field => {
    // Handle stepNumber 0 correctly (0 is falsy, so use nullish coalescing or explicit check)
    const stepNum = field.stepNumber !== undefined && field.stepNumber !== null ? field.stepNumber : 1;
    if (!fieldsByStep[stepNum]) {
      fieldsByStep[stepNum] = [];
    }
    fieldsByStep[stepNum].push(field);
  });
  
  // Get all step numbers and sort them
  const stepNumbers = Object.keys(fieldsByStep).map(Number).sort((a, b) => a - b);
  
  // Render each step group
  stepNumbers.forEach(stepNum => {
    const stepFields = fieldsByStep[stepNum];
    const stepName = stepNames[stepNum];
    
    // Create step header
    const stepHeader = document.createElement("div");
    stepHeader.className = "customization-step-header";
    stepHeader.style.cssText = "margin-top: 20px; margin-bottom: 15px; padding: 12px; background: #e8f4f8; border-left: 4px solid #005b82; border-radius: 4px;";
    
    const stepTitle = document.createElement("h4");
    stepTitle.style.cssText = "margin: 0; color: #005b82; font-size: 14px; font-weight: 600;";
    // Handle Step 0 display (show as "Series Selection" or "Step 0")
    if (stepNum === 0) {
      stepTitle.textContent = stepName || "Series Selection";
    } else {
      stepTitle.textContent = stepName ? `${stepName} (Step ${stepNum})` : `Step ${stepNum}`;
    }
    stepHeader.appendChild(stepTitle);
    container.appendChild(stepHeader);
    
    // Render fields in this step
    stepFields.forEach(field => {
    const formGroup = document.createElement("div");
    formGroup.className = "form-group";
    
    const label = document.createElement("label");
    label.textContent = field.label;
    label.setAttribute("for", `${prefix}${field.id}`);
    formGroup.appendChild(label);
    
    let input;
    
    switch (field.type) {
      case "tags":
        // Create tag container for tag-style selection
        const tagContainer = document.createElement("div");
        tagContainer.className = "tag-container";
        tagContainer.id = `${prefix}${field.id}Container`;
        tagContainer.dataset.fieldId = field.id;
        tagContainer.dataset.prefix = prefix;
        
        // Create wrapper for tags and add button
        const tagWrapper = document.createElement("div");
        tagWrapper.className = "tag-wrapper";
        
        // Create hidden input to store selected values as JSON
        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.id = `${prefix}${field.id}`;
        hiddenInput.name = field.id;
        
        // Store available options in data attribute (original + dynamically added)
        tagContainer.dataset.availableOptions = JSON.stringify(field.options || []);
        // Store original options separately to track which tags can be removed
        tagContainer.dataset.originalOptions = JSON.stringify(field.options || []);
        
        // Add: tags can be selected/unselected when series is chosen. Edit: use saved/product data, tags are clickable.
        const allOptions = field.options || [];
        if (prefix === "" || prefix === "standardOption_") {
          // ADD product: automatically select ALL available tags by default
          // Tags will be auto-selected, but can still be clicked to unselect
          hiddenInput.value = JSON.stringify(allOptions);
        } else {
          // EDIT product: use saved selections or product data (populateEditForm sets this)
          const selectedTagsKey = `${fieldKey}_selectedTags`;
          const selectedTags = customizationFields[selectedTagsKey] || {};
          const preSelected = selectedTags[field.id] || [];
          hiddenInput.value = preSelected.length > 0 ? JSON.stringify(preSelected) : "[]";
        }
        
        // Render tags (Add: all selected by default; Edit: selectable, click toggles)
        renderTags(tagContainer, field.options || [], prefix, field.id);
        
        tagWrapper.appendChild(tagContainer);
        tagWrapper.appendChild(hiddenInput);
        
        // Create "Add Tag" button
        const addTagBtn = document.createElement("button");
        addTagBtn.type = "button";
        addTagBtn.className = "add-tag-btn";
        addTagBtn.innerHTML = '<i class="fas fa-plus"></i> Add';
        addTagBtn.addEventListener("click", () => showAddTagDialog(tagContainer, prefix, field.id));
        
        const addTagWrapper = document.createElement("div");
        addTagWrapper.className = "add-tag-wrapper";
        addTagWrapper.appendChild(addTagBtn);
        
        formGroup.appendChild(tagWrapper);
        formGroup.appendChild(addTagWrapper);
        break;
        
      case "checkbox":
        input = document.createElement("input");
        input.type = "checkbox";
        input.id = `${prefix}${field.id}`;
        input.name = field.id;
        input.className = "custom-checkbox";
        // For checkboxes, put label after input
        label.setAttribute("for", `${prefix}${field.id}`);
        label.style.display = "inline";
        label.style.marginLeft = "8px";
        label.style.fontWeight = "normal";
        formGroup.insertBefore(input, label);
        // Add auto-save on change
        input.addEventListener("change", () => {
          autoSaveCustomizationFields(prefix);
        });
        break;
        
      case "color":
        // Note: Color picker kept as is, but can be converted to tags if needed
        input = document.createElement("input");
        input.type = "color";
        input.id = `${prefix}${field.id}`;
        input.name = field.id;
        input.value = field.default || "#000000";
        input.className = "color-picker";
        break;
        
      case "number":
        input = document.createElement("input");
        input.type = "number";
        input.id = `${prefix}${field.id}`;
        input.name = field.id;
        input.className = "input-text";
        input.min = field.min || 0;
        input.step = field.step || 1;
        input.placeholder = `Enter ${field.label.toLowerCase()}`;
        // Add auto-save on change
        input.addEventListener("change", () => {
          autoSaveCustomizationFields(prefix);
        });
        input.addEventListener("input", () => {
          // Debounce auto-save on input
          clearTimeout(input._autoSaveTimeout);
          input._autoSaveTimeout = setTimeout(() => {
            autoSaveCustomizationFields(prefix);
          }, 500);
        });
        break;
        
      case "dimensions":
        // Dimensions field - renders Width, Height, and h1 in one row
        const dimensionsContainer = document.createElement("div");
        dimensionsContainer.className = "dimensions-container";
        dimensionsContainer.style.cssText = "display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;";
        
        // Width input
        const widthGroup = document.createElement("div");
        widthGroup.style.cssText = "flex: 1; min-width: 120px;";
        const widthLabel = document.createElement("label");
        widthLabel.textContent = "Width";
        widthLabel.setAttribute("for", `${prefix}width`);
        widthLabel.style.cssText = "display: block; margin-bottom: 6px; font-size: 13px; color: #333;";
        const widthInput = document.createElement("input");
        widthInput.type = "number";
        widthInput.id = `${prefix}width`;
        widthInput.name = "width";
        widthInput.className = "input-text";
        widthInput.min = 0;
        widthInput.step = 0.1;
        widthInput.placeholder = "0";
        widthGroup.appendChild(widthLabel);
        widthGroup.appendChild(widthInput);
        
        // Height input
        const heightGroup = document.createElement("div");
        heightGroup.style.cssText = "flex: 1; min-width: 120px;";
        const heightLabel = document.createElement("label");
        heightLabel.textContent = "Height";
        heightLabel.setAttribute("for", `${prefix}height`);
        heightLabel.style.cssText = "display: block; margin-bottom: 6px; font-size: 13px; color: #333;";
        const heightInput = document.createElement("input");
        heightInput.type = "number";
        heightInput.id = `${prefix}height`;
        heightInput.name = "height";
        heightInput.className = "input-text";
        heightInput.min = 0;
        heightInput.step = 0.1;
        heightInput.placeholder = "0";
        heightGroup.appendChild(heightLabel);
        heightGroup.appendChild(heightInput);
        
        dimensionsContainer.appendChild(widthGroup);
        dimensionsContainer.appendChild(heightGroup);
        
        // h1 input (conditional)
        if (field.h1Conditional && field.h1Conditional.dependsOn) {
          const h1Group = document.createElement("div");
          h1Group.id = `${prefix}h1Group`;
          h1Group.className = "h1-dimension-group";
          h1Group.style.cssText = "flex: 1; min-width: 120px; display: none;"; // Hidden by default
          const h1Label = document.createElement("label");
          h1Label.textContent = "h1";
          h1Label.setAttribute("for", `${prefix}h1`);
          h1Label.style.cssText = "display: block; margin-bottom: 6px; font-size: 13px; color: #333;";
          const h1Input = document.createElement("input");
          h1Input.type = "number";
          h1Input.id = `${prefix}h1`;
          h1Input.name = "h1";
          h1Input.className = "input-text";
          h1Input.min = 0;
          h1Input.step = 0.1;
          h1Input.placeholder = "0";
          h1Group.appendChild(h1Label);
          h1Group.appendChild(h1Input);
          dimensionsContainer.appendChild(h1Group);
          
          // Add event listener to show/hide h1 based on dependency
          const dependsOnField = field.h1Conditional.dependsOn;
          const showWhen = field.h1Conditional.showWhen || [];
          
          // Function to check if h1 should be shown
          const checkH1Visibility = () => {
            const dependsOnInput = document.getElementById(`${prefix}${dependsOnField}`);
            if (!dependsOnInput) return;
            
            let shouldShow = false;
            if (dependsOnInput.type === "hidden") {
              // It's a tags field
              const selectedValues = JSON.parse(dependsOnInput.value || "[]");
              shouldShow = showWhen.some(val => selectedValues.includes(val));
            } else {
              // It's a regular input
              const value = dependsOnInput.value;
              shouldShow = showWhen.includes(value);
            }
            
            h1Group.style.display = shouldShow ? "block" : "none";
          };
          
          // Check on page load
          setTimeout(checkH1Visibility, 100);
          
          // Check when dependency changes
          const dependsOnInput = document.getElementById(`${prefix}${dependsOnField}`);
          if (dependsOnInput) {
            if (dependsOnInput.type === "hidden") {
              // For tags, listen to changes in the hidden input
              dependsOnInput.addEventListener("change", checkH1Visibility);
            } else {
              dependsOnInput.addEventListener("change", checkH1Visibility);
              dependsOnInput.addEventListener("input", checkH1Visibility);
            }
          }
        }
        
        formGroup.appendChild(dimensionsContainer);
        container.appendChild(formGroup);
        break;
    }
    
    // Append input/elements based on field type
    if (field.type === "tags") {
      // Tags are already appended in the case above
      container.appendChild(formGroup);
    } else if (field.type === "checkbox") {
      // For checkbox, input is already before label
      formGroup.appendChild(label);
      container.appendChild(formGroup);
    } else if (field.type === "dimensions") {
      // Dimensions are already appended in the case above
      // formGroup is already appended to container at line 3201
    } else {
      // For other types (color, number), append the input
      if (input) {
        formGroup.appendChild(input);
        container.appendChild(formGroup);
      } else {
        console.warn(`Input not created for field type: ${field.type}, field ID: ${field.id}`);
      }
    }
    });
  });
  
  // No auto-application of series in Add New Product form
  // Admin must manually select series every time they open Add New Product
  // This ensures no selections are saved unless admin explicitly saves the product
}

/**
 * Toggles tag selection state
 * @param {HTMLElement} tag - The tag element to toggle
 * @param {string} prefix - Field prefix
 * @param {string} fieldId - Field ID
 */
function toggleTagSelection(tag, prefix, fieldId) {
  const hiddenInput = document.getElementById(`${prefix}${fieldId}`);
  if (!hiddenInput) return;
  
  let selectedValues = JSON.parse(hiddenInput.value || "[]");
  const tagValue = tag.dataset.value;
  
  // Toggle selection
  if (tag.classList.contains("selected")) {
    // Deselect: remove from selectedValues
    selectedValues = selectedValues.filter(v => v !== tagValue);
    tag.classList.remove("selected");
  } else {
    // Select: add to selectedValues
    if (!selectedValues.includes(tagValue)) {
      selectedValues.push(tagValue);
    }
    tag.classList.add("selected");
  }
  
  // Update hidden input
  hiddenInput.value = JSON.stringify(selectedValues);
  
  // Save selected tags as defaults for future products
  saveSelectedTagsAsDefaults(prefix);
}

/**
 * Renders tag elements in a container
 * @param {HTMLElement} container - Container to render tags in
 * @param {Array} options - Available tag options
 * @param {string} prefix - Field prefix
 * @param {string} fieldId - Field ID
 */
function renderTags(container, options, prefix, fieldId) {
  container.innerHTML = ""; // Clear existing tags
  
  // Get selected values from hidden input
  const hiddenInput = document.getElementById(`${prefix}${fieldId}`);
  const selectedValues = hiddenInput ? JSON.parse(hiddenInput.value || "[]") : [];
  
  // Get all available options from global config
  const availableOptions = JSON.parse(container.dataset.availableOptions || "[]");
  
  // Merge with tags that have prices/images for this specific product
  const productSpecificTags = [];
  if (tagPrices && tagPrices[fieldId]) {
    Object.keys(tagPrices[fieldId]).forEach(tag => {
      if (!productSpecificTags.includes(tag)) productSpecificTags.push(tag);
    });
  }
  if (tagImages && tagImages[fieldId]) {
    Object.keys(tagImages[fieldId]).forEach(tag => {
      if (!productSpecificTags.includes(tag)) productSpecificTags.push(tag);
    });
  }

  // Final list: global options + product specific tags + previously selected values
  const allOptions = [...new Set([...availableOptions, ...productSpecificTags, ...selectedValues])]; 
  
  // Update available options
  container.dataset.availableOptions = JSON.stringify(allOptions);
  
  // Add (prefix ""): all tags selected, no click. Edit (prefix "edit"): selectable, click toggles blue.
  const isAddForm = (prefix === "" || prefix === "standardOption_");
  
  allOptions.forEach(option => {
    const tag = document.createElement("span");
    const isSelected = selectedValues.includes(option);
    tag.className = isSelected ? "tag selected" : "tag";
    tag.dataset.value = option;
    
    if (isAddForm) {
      tag.title = "Already confirmed for product - no need to click";
    }
    
    // Create tag content with image if available
    const tagContent = document.createElement("span");
    tagContent.className = "tag-content";
    
    // Add image if available
    if (tagImages[fieldId] && tagImages[fieldId][option]) {
      const tagImage = document.createElement("img");
      tagImage.className = "tag-image";
      
      // If it's a File object, create preview
      if (tagImages[fieldId][option] instanceof File) {
        const reader = new FileReader();
        reader.onload = (e) => {
          tagImage.src = e.target.result;
        };
        reader.readAsDataURL(tagImages[fieldId][option]);
      } else {
        // If it's already a URL (from database)
        tagImage.src = tagImages[fieldId][option];
      }
      tagImage.alt = option;
      tagContent.appendChild(tagImage);
    }
    
    // Add tag text
    const tagText = document.createTextNode(option);
    tagContent.appendChild(tagText);
    tag.appendChild(tagContent);
    
    // Add 2D preview indicator if visual config exists for this tag
    if (tagVisualConfigs[fieldId] && tagVisualConfigs[fieldId][option] && tagVisualConfigs[fieldId][option].enabled !== false) {
      const visualIndicator = document.createElement("span");
      visualIndicator.className = "tag-visual-indicator";
      visualIndicator.innerHTML = `<i class="fas fa-palette" style="font-size: 10px; color: #6c5ce7; margin-left: 4px;" title="Has 2D Preview Style"></i>`;
      tag.appendChild(visualIndicator);
    }
    
    // Add price display for all tags (always show price, even if 0)
    const priceSpan = document.createElement("span");
    priceSpan.className = "tag-price";
    const priceValue = (tagPrices[fieldId] && tagPrices[fieldId][option] !== undefined) 
      ? parseFloat(tagPrices[fieldId][option]) 
      : 0;
    priceSpan.textContent = `(₱${priceValue.toFixed(2)})`;
    tag.appendChild(priceSpan);
    
    // Add form: clickable when series is selected (can unselect). Edit form: click to select/unselect (toggle blue).
    // Both Add and Edit forms allow clicking to toggle selection
    tag.addEventListener("click", (e) => {
      if (e.target.closest('.tag-actions')) return;
      toggleTagSelection(tag, prefix, fieldId);
    });
    
    // Add edit and remove buttons for ALL tags - admin can edit/remove any tags (both preset and custom)
    const tagActions = document.createElement("span");
    tagActions.className = "tag-actions";
    
    // Edit button for ALL tags
    const editBtn = document.createElement("span");
    editBtn.className = "tag-edit";
    editBtn.innerHTML = " ✎";
    editBtn.title = "Edit tag details";
    editBtn.addEventListener("click", (e) => {
      e.stopPropagation(); // Prevent tag selection toggle
      showAddTagDialog(container, prefix, fieldId, true, option);
    });
    tagActions.appendChild(editBtn);
    
    // Remove button
    const removeBtn = document.createElement("span");
    removeBtn.className = "tag-remove";
    removeBtn.innerHTML = " ×";
    removeBtn.title = "Remove tag";
    removeBtn.addEventListener("click", (e) => {
      e.stopPropagation(); // Prevent tag selection toggle
      removeTag(option, container, prefix, fieldId);
    });
    tagActions.appendChild(removeBtn);
    
    tag.appendChild(tagActions);
    
    container.appendChild(tag);
  });
  
  // Update hidden input with selected values
  if (hiddenInput) {
    hiddenInput.value = JSON.stringify(selectedValues);
  }
}

/**
 * Auto-fills customization fields based on selected series preset
 * @param {string} seriesName - Name of the selected series (e.g., "900 Series")
 * @param {string} prefix - Field prefix
 * @param {string} subcategory - Subcategory name (e.g., "Casement", "Sliding")
 */
function autoFillSeriesPreset(seriesName, prefix, subcategory = null) {
  // Get subcategory from form if not provided
  if (!subcategory) {
    const subcategorySelect = prefix === "edit" 
      ? document.getElementById("editProductSubcategory")
      : document.getElementById("productSubcategory");
    if (subcategorySelect) {
      subcategory = subcategorySelect.value;
    }
  }
  
  if (!subcategory) {
    console.warn('autoFillSeriesPreset: subcategory not found');
    return;
  }
  
  // Series are now segregated by subcategory: Series_Presets[subcategory][seriesName]
  if (!customizationFields["Series_Presets"] || !customizationFields["Series_Presets"][subcategory]) {
    return;
  }
  
  const presets = customizationFields["Series_Presets"][subcategory];
  if (!presets || !presets[seriesName]) {
    return;
  }
  
  const preset = presets[seriesName];
  
  // Special handling for thickness field - update options dynamically based on series
  if (preset.thickness) {
    const thicknessOptions = Array.isArray(preset.thickness) ? preset.thickness : [preset.thickness];
    
    // Find the thickness field in the customization fields and update its options
    const fieldKey = prefix === "edit" ? getFieldKeyForEdit() : getFieldKeyForAdd();
    if (fieldKey && customizationFields[fieldKey]) {
      const thicknessField = customizationFields[fieldKey].find(f => f.id === "thickness");
      if (thicknessField && thicknessField.type === "tags") {
        // Update the field's options
        thicknessField.options = thicknessOptions;
        
        // Find the container and update it
        const container = document.getElementById(`${prefix}thicknessContainer`);
        if (container) {
          container.dataset.availableOptions = JSON.stringify(thicknessOptions);
          container.dataset.originalOptions = JSON.stringify(thicknessOptions);
          
          // Re-render the tags with new options
          renderTags(container, thicknessOptions, prefix, "thickness");
        }
      }
    }
  }
  
  // Fill each field in the preset
  Object.keys(preset).forEach(fieldId => {
    const values = preset[fieldId];
    const hiddenInput = document.getElementById(`${prefix}${fieldId}`);
    const container = document.getElementById(`${prefix}${fieldId}Container`);
    
    if (hiddenInput && container) {
      // Set the selected values
      hiddenInput.value = JSON.stringify(Array.isArray(values) ? values : [values]);
      
      // Update the tag display
      const availableOptions = JSON.parse(container.dataset.availableOptions || "[]");
      renderTags(container, availableOptions, prefix, fieldId);
    }
  });
  
  // Auto-save after filling
  autoSaveCustomizationFields(prefix);
}

// Helper function to get field key for add form
function getFieldKeyForAdd() {
  const categorySelect = document.getElementById("addProductCategory");
  const subcategorySelect = document.getElementById("addProductSubcategory");
  if (categorySelect && subcategorySelect && categorySelect.value && subcategorySelect.value) {
    return `${categorySelect.value}_${subcategorySelect.value}`;
  }
  return null;
}

// Helper function to get field key for edit form
function getFieldKeyForEdit() {
  const categorySelect = document.getElementById("editProductCategory");
  const subcategorySelect = document.getElementById("editProductSubcategory");
  if (categorySelect && subcategorySelect && categorySelect.value && subcategorySelect.value) {
    return `${categorySelect.value}_${subcategorySelect.value}`;
  }
  return null;
}

/**
 * Auto-saves customization fields to product (for add/edit forms)
 * Also saves selected tags as defaults for future products
 * @param {string} prefix - Field prefix ("add" or "edit")
 */
function autoSaveCustomizationFields(prefix) {
  // This function will be called automatically when fields change
  // The actual save happens when the form is submitted
  // But we can update the form data in real-time here if needed
  
  // Save selected tags as defaults when tags are selected
  saveSelectedTagsAsDefaults(prefix);
  
  // For now, just ensure the data is collected correctly
  // The actual save happens on form submission
  console.log(`[Auto-Save] Customization fields updated for ${prefix}`);
}

/**
 * Save selected tags as defaults for the current subcategory
 * @param {string} prefix - Field prefix ("add" or "edit")
 */
function saveSelectedTagsAsDefaults(prefix) {
  try {
    // Get current category and subcategory
    const categorySelect = prefix === "edit" 
      ? document.getElementById("editProductCategory")
      : document.getElementById("productCategory");
    const subcategorySelect = prefix === "edit"
      ? document.getElementById("editProductSubcategory")
      : document.getElementById("productSubcategory");
    
    if (!categorySelect || !subcategorySelect) return;
    
    const category = categorySelect.value;
    const subcategory = subcategorySelect.value;
    
    if (!category || !subcategory) return;
    
    // Build field key
    let fieldKey;
    if (category === "Windows") {
      fieldKey = `Windows_${subcategory}`;
    } else if (category === "Doors") {
      fieldKey = `Doors_${subcategory}`;
    } else if (category === "Glass Partitions & Enclosures") {
      fieldKey = `Partitions_${subcategory}`;
    } else if (category === "Mirrors & Specialty Glass") {
      fieldKey = `Specialty_${subcategory}`;
    } else if (category === "Commercial & Exterior") {
      fieldKey = `Commercial_${subcategory}`;
    } else {
      return;
    }
    
    // Collect selected tags from all tag fields
    const selectedTagsKey = `${fieldKey}_selectedTags`;
    const selectedTags = {};
    
    // Find all tag containers with the prefix
    const tagContainers = document.querySelectorAll(`[id$="Container"][data-prefix="${prefix}"]`);
    tagContainers.forEach(container => {
      const fieldId = container.dataset.fieldId;
      if (fieldId) {
        const hiddenInput = document.getElementById(`${prefix}${fieldId}`);
        if (hiddenInput) {
          const selectedValues = JSON.parse(hiddenInput.value || "[]");
          if (selectedValues.length > 0) {
            selectedTags[fieldId] = selectedValues;
          }
        }
      }
    });
    
    // Save to customizationFields
    if (Object.keys(selectedTags).length > 0) {
      customizationFields[selectedTagsKey] = selectedTags;
      // Save to localStorage
      try {
        localStorage.setItem(CUSTOMIZATION_FIELDS_STORAGE_KEY, JSON.stringify(customizationFields));
      } catch (e) {
        console.warn('localStorage access blocked by browser (Tracking Prevention):', e.message);
      }
    }
  } catch (e) {
    console.error('Error saving selected tags as defaults:', e);
  }
}

/**
 * Removes a tag from available options (including preset tags)
 * @param {string} tagValue - Value of tag to remove
 * @param {HTMLElement} container - Tag container
 * @param {string} prefix - Field prefix
 * @param {string} fieldId - Field ID
 */
function removeTag(tagValue, container, prefix, fieldId) {
  const hiddenInput = document.getElementById(`${prefix}${fieldId}`);
  let selectedValues = JSON.parse(hiddenInput.value || "[]");
  
  // Remove from selected values
  selectedValues = selectedValues.filter(v => v !== tagValue);
  hiddenInput.value = JSON.stringify(selectedValues);
  
  // Remove from available options
  const availableOptions = JSON.parse(container.dataset.availableOptions || "[]");
  const updatedOptions = availableOptions.filter(opt => opt !== tagValue);
  container.dataset.availableOptions = JSON.stringify(updatedOptions);
  
  // Also remove from original options if it was a preset tag
  // This ensures preset tags stay removed even if field is regenerated
  const originalOptions = JSON.parse(container.dataset.originalOptions || "[]");
  if (originalOptions.includes(tagValue)) {
    const updatedOriginalOptions = originalOptions.filter(opt => opt !== tagValue);
    container.dataset.originalOptions = JSON.stringify(updatedOriginalOptions);
  }
  
  // Remove from tag prices
  if (tagPrices[fieldId] && tagPrices[fieldId][tagValue] !== undefined) {
    delete tagPrices[fieldId][tagValue];
  }
  
  // Remove from tag images
  if (tagImages[fieldId] && tagImages[fieldId][tagValue] !== undefined) {
    delete tagImages[fieldId][tagValue];
  }
  
  // Re-render tags
  renderTags(container, updatedOptions, prefix, fieldId);
}

// Tag prices storage - stores price per tag
let tagPrices = {}; // Format: { fieldId: { tagName: price } }

// Tag images storage - stores image per tag
let tagImages = {}; // Format: { fieldId: { tagName: imageUrl } }

// Tag visual configs storage - stores Konva visual config per tag for 2D preview
// Format: { fieldId: { tagName: { fill: '#E0F2F1', opacity: 0.9, stroke: '#333333', strokeWidth: 4 } } }
let tagVisualConfigs = {};

// Mini Konva preview instance for tag modal
let tagPreviewStage = null;
let tagPreviewLayer = null;

/**
 * Shows modal to add a new tag with price
 * @param {HTMLElement} container - Tag container
 * @param {string} prefix - Field prefix
 * @param {string} fieldId - Field ID
 * @param {boolean} isEdit - Whether we are editing an existing tag
 * @param {string} oldTagValue - The original tag value if editing
 */
function showAddTagDialog(container, prefix, fieldId, isEdit = false, oldTagValue = null) {
  const modal = document.getElementById("addTagModal");
  const modalTitle = modal.querySelector('h3');
  const tagNameInput = document.getElementById("tagNameInput");
  const tagNameSelect = document.getElementById("tagNameSelect");
  const tagNameCustomInput = document.getElementById("tagNameCustomInput");
  const tagPriceInput = document.getElementById("tagPriceInput");
  const tagImageInput = document.getElementById("tagImageInput");
  const tagImageUploadBtn = document.getElementById("tagImageUploadBtn");
  const tagImagePreview = document.getElementById("tagImagePreview");
  const tagImagePreviewImg = document.getElementById("tagImagePreviewImg");
  const tagImageRemoveBtn = document.getElementById("tagImageRemoveBtn");
  const closeBtn = document.getElementById("closeTagModal");
  const cancelBtn = document.getElementById("cancelAddTag");
  const confirmBtn = document.getElementById("confirmAddTag");
  
  if (modalTitle) {
    modalTitle.textContent = isEdit ? "Edit Tag" : "Add New Tag";
  }
  if (confirmBtn) {
    confirmBtn.textContent = isEdit ? "Save Changes" : "Add Tag";
  }

  // Check if this is a shape field - show dropdown instead of text input
  const isShapeField = fieldId === "shape" || fieldId.toLowerCase().includes("shape");
  
  // Reset prices/images/visuals before populating
  resetKonvaVisualConfig();

  // Show/hide appropriate input based on field type
  if (isShapeField) {
    tagNameInput.style.display = "none";
    tagNameSelect.style.display = "block";
    tagNameCustomInput.style.display = "none";
    
    if (isEdit) {
      // Check if value is in dropdown options
      const options = Array.from(tagNameSelect.options).map(opt => opt.value);
      if (options.includes(oldTagValue)) {
        tagNameSelect.value = oldTagValue;
      } else {
        tagNameSelect.value = "Others";
        tagNameCustomInput.style.display = "block";
        tagNameCustomInput.value = oldTagValue;
      }
    } else {
      tagNameSelect.value = "";
      tagNameCustomInput.value = "";
    }
  } else {
    tagNameInput.style.display = "block";
    tagNameSelect.style.display = "none";
    tagNameInput.value = isEdit ? oldTagValue : "";
    tagNameCustomInput.style.display = "none";
    tagNameCustomInput.value = "";
  }
  
  // Populate price
  tagPriceInput.value = (isEdit && tagPrices[fieldId] && tagPrices[fieldId][oldTagValue] !== undefined) 
    ? tagPrices[fieldId][oldTagValue] 
    : "";
  
  // Populate image
  tagImageInput.value = "";
  if (isEdit && tagImages[fieldId] && tagImages[fieldId][oldTagValue]) {
    const imgData = tagImages[fieldId][oldTagValue];
    tagImagePreview.style.display = "block";
    if (imgData instanceof File) {
      const reader = new FileReader();
      reader.onload = (e) => tagImagePreviewImg.src = e.target.result;
      reader.readAsDataURL(imgData);
    } else {
      tagImagePreviewImg.src = imgData;
    }
    modal.dataset.tagImageFile = typeof imgData === 'string' ? imgData : imgData.name;
  } else {
    tagImagePreview.style.display = "none";
    tagImagePreviewImg.src = "";
    modal.dataset.tagImageFile = "";
  }

  // Populate Visual Config if editing
  if (isEdit && tagVisualConfigs[fieldId] && tagVisualConfigs[fieldId][oldTagValue]) {
    const config = tagVisualConfigs[fieldId][oldTagValue];
    applyVisualConfigToInputs(config);
  }
  
  // Handle dropdown change for "Others" option
  if (isShapeField) {
    tagNameSelect.onchange = () => {
      if (tagNameSelect.value === "Others") {
        tagNameCustomInput.style.display = "block";
        tagNameCustomInput.focus();
      } else {
        tagNameCustomInput.style.display = "none";
        tagNameCustomInput.value = ""; // Clear custom input when switching away from "Others"
        
        // Auto-apply preset for selected dropdown value
        if (typeof autoApplyVisualPreset === 'function') {
          autoApplyVisualPreset(fieldId, tagNameSelect.value);
        }
      }
    };
  }
  
  // Image upload button click handler
  tagImageUploadBtn.onclick = () => {
    tagImageInput.click();
  };
  
  // Image input change handler
  tagImageInput.onchange = (e) => {
    const file = e.target.files[0];
    if (file) {
      // Validate file type
      if (!file.type.startsWith('image/')) {
        showToast('Please select a valid image file.', 'error');
        return;
      }
      
      // Validate file size (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        showToast('Image size must be less than 5MB.', 'error');
        return;
      }
      
      // Create preview
      const reader = new FileReader();
      reader.onload = (event) => {
        tagImagePreviewImg.src = event.target.result;
        tagImagePreview.style.display = "block";
        modal.dataset.tagImageFile = file.name; // Store filename for reference
      };
      reader.readAsDataURL(file);
    }
  };
  
  // Remove image handler
  tagImageRemoveBtn.onclick = () => {
    tagImageInput.value = "";
    tagImagePreview.style.display = "none";
    tagImagePreviewImg.src = "";
    modal.dataset.tagImageFile = "";
    
    // If editing, also remove from global storage immediately or on confirm? 
    // Best to wait for confirm.
  };
  
  // ===== KONVA VISUAL CONFIG SETUP (FULLY DYNAMIC) =====
  // Show visual config for ALL tag fields - admin has full flexibility
  const konvaConfigGroup = document.getElementById("konvaVisualConfigGroup");
  const advancedKonvaOptions = document.getElementById("advancedKonvaOptions");
  const tagKonvaEffect = document.getElementById("tagKonvaEffect");
  const enableVisualPreviewToggle = document.getElementById("enableVisualPreview");
  const visualConfigContent = document.getElementById("visualConfigContent");
  
  // Always show visual config group (with toggle)
  if (konvaConfigGroup) {
    konvaConfigGroup.style.display = "block";
    
    // Check if editing and has existing visual config - enable toggle if so
    const hasExistingConfig = isEdit && tagVisualConfigs[fieldId] && tagVisualConfigs[fieldId][oldTagValue] && tagVisualConfigs[fieldId][oldTagValue].enabled !== false;
    
    if (enableVisualPreviewToggle) {
      // Set initial toggle state
      enableVisualPreviewToggle.checked = hasExistingConfig;
      
      // Show/hide visual config content based on toggle
      if (visualConfigContent) {
        visualConfigContent.style.display = hasExistingConfig ? "block" : "none";
      }
      
      // Toggle change handler - instant show/hide with preview initialization
      enableVisualPreviewToggle.onchange = () => {
        const isEnabled = enableVisualPreviewToggle.checked;
        if (visualConfigContent) {
          visualConfigContent.style.display = isEnabled ? "block" : "none";
          
          // Initialize preview instantly when enabled
          if (isEnabled) {
            const tagKonvaPreview = document.getElementById("tagKonvaPreview");
            if (tagKonvaPreview && typeof Konva !== 'undefined') {
              setTimeout(() => {
                initTagKonvaPreview();
                updateTagKonvaPreview();
              }, 50); // Small delay for DOM to update
            }
          }
        }
      };
    }
    
    // Show field category hint if presets are available
    if (typeof getFieldVisualCategory === 'function') {
      const category = getFieldVisualCategory(fieldId);
      const categoryHint = konvaConfigGroup.querySelector('.field-category-hint');
      if (categoryHint) {
        categoryHint.remove();
      }
      if (category && enableVisualPreviewToggle && enableVisualPreviewToggle.checked) {
        const hint = document.createElement('div');
        hint.className = 'field-category-hint';
        hint.style.cssText = 'background: #E3F2FD; padding: 8px 12px; border-radius: 4px; margin-bottom: 10px; font-size: 12px;';
        hint.innerHTML = `<i class="fas fa-lightbulb" style="color: #1976D2;"></i> <strong>Field Type: ${category.category.charAt(0).toUpperCase() + category.category.slice(1)}</strong> - ${category.description}`;
        visualConfigContent.insertBefore(hint, visualConfigContent.firstChild);
      }
    }
  }
  
  // Auto-apply visual presets when tag name changes (only when ADDING, not editing existing)
  if (!isEdit) {
    const tagNameInputForPreset = document.getElementById("tagNameInput");
    if (tagNameInputForPreset) {
      // Debounce function to avoid too many calls
      let presetTimeout;
      tagNameInputForPreset.addEventListener('input', () => {
        clearTimeout(presetTimeout);
        presetTimeout = setTimeout(() => {
          const tagValue = tagNameInputForPreset.value.trim();
          if (tagValue && typeof autoApplyVisualPreset === 'function') {
            autoApplyVisualPreset(fieldId, tagValue);
          }
        }, 500); // Wait 500ms after typing stops
      });
    }
  }
  
  // Visual config input elements - Basic
  const tagFillColor = document.getElementById("tagFillColor");
  const tagFillColorHex = document.getElementById("tagFillColorHex");
  const tagStrokeColor = document.getElementById("tagStrokeColor");
  const tagStrokeColorHex = document.getElementById("tagStrokeColorHex");
  const tagOpacity = document.getElementById("tagOpacity");
  const tagOpacityValue = document.getElementById("tagOpacityValue");
  const tagStrokeWidth = document.getElementById("tagStrokeWidth");
  const tagStrokeWidthValue = document.getElementById("tagStrokeWidthValue");
  const tagKonvaPreview = document.getElementById("tagKonvaPreview");
  
  // Advanced option elements
  const gradientOptions = document.getElementById("gradientOptions");
  const shadowOptions = document.getElementById("shadowOptions");
  const patternOptions = document.getElementById("patternOptions");
  const edgeOptions = document.getElementById("edgeOptions");
  
  // Effect type change handler - show/hide advanced options
  if (tagKonvaEffect) {
    tagKonvaEffect.onchange = () => {
      const effect = tagKonvaEffect.value;
      
      // Hide all advanced options first
      if (advancedKonvaOptions) advancedKonvaOptions.style.display = "none";
      if (gradientOptions) gradientOptions.style.display = "none";
      if (shadowOptions) shadowOptions.style.display = "none";
      if (patternOptions) patternOptions.style.display = "none";
      if (edgeOptions) edgeOptions.style.display = "none";
      
      // Show relevant options based on effect type
      if (effect === 'gradient') {
        if (advancedKonvaOptions) advancedKonvaOptions.style.display = "block";
        if (gradientOptions) gradientOptions.style.display = "block";
      } else if (effect === 'shadow') {
        if (advancedKonvaOptions) advancedKonvaOptions.style.display = "block";
        if (shadowOptions) shadowOptions.style.display = "block";
      } else if (effect === 'pattern') {
        if (advancedKonvaOptions) advancedKonvaOptions.style.display = "block";
        if (patternOptions) patternOptions.style.display = "block";
      } else if (effect === 'edge') {
        if (advancedKonvaOptions) advancedKonvaOptions.style.display = "block";
        if (edgeOptions) edgeOptions.style.display = "block";
      } else if (effect === 'custom') {
        // Show all advanced options for full customization
        if (advancedKonvaOptions) advancedKonvaOptions.style.display = "block";
        if (gradientOptions) gradientOptions.style.display = "block";
        if (shadowOptions) shadowOptions.style.display = "block";
        if (patternOptions) patternOptions.style.display = "block";
        if (edgeOptions) edgeOptions.style.display = "block";
      }
      
      updateTagKonvaPreview();
    };
    
    // Trigger initial display state
    tagKonvaEffect.dispatchEvent(new Event('change'));
  }
  
  // Initialize mini Konva preview
  if (tagKonvaPreview && typeof Konva !== 'undefined') {
    initTagKonvaPreview();
  }
  
  // Sync color pickers with hex inputs
  if (tagFillColor && tagFillColorHex) {
    tagFillColor.oninput = () => {
      tagFillColorHex.value = tagFillColor.value;
      updateTagKonvaPreview();
    };
    tagFillColorHex.oninput = () => {
      if (/^#[0-9A-Fa-f]{6}$/.test(tagFillColorHex.value)) {
        tagFillColor.value = tagFillColorHex.value;
        updateTagKonvaPreview();
      }
    };
  }
  
  if (tagStrokeColor && tagStrokeColorHex) {
    tagStrokeColor.oninput = () => {
      tagStrokeColorHex.value = tagStrokeColor.value;
      updateTagKonvaPreview();
    };
    tagStrokeColorHex.oninput = () => {
      if (/^#[0-9A-Fa-f]{6}$/.test(tagStrokeColorHex.value)) {
        tagStrokeColor.value = tagStrokeColorHex.value;
        updateTagKonvaPreview();
      }
    };
  }
  
  // Update opacity display and preview
  if (tagOpacity && tagOpacityValue) {
    tagOpacity.oninput = () => {
      tagOpacityValue.textContent = tagOpacity.value;
      updateTagKonvaPreview();
    };
  }
  
  // Update stroke width display and preview
  if (tagStrokeWidth && tagStrokeWidthValue) {
    tagStrokeWidth.oninput = () => {
      tagStrokeWidthValue.textContent = tagStrokeWidth.value;
      updateTagKonvaPreview();
    };
  }
  
  // Advanced option event listeners
  setupAdvancedKonvaEventListeners();
  // ===== END KONVA VISUAL CONFIG SETUP =====
  
  // Store current context for callback
  modal.dataset.containerId = container.id;
  modal.dataset.prefix = prefix;
  modal.dataset.fieldId = fieldId;
  modal.dataset.isShapeField = isShapeField;
  
  // Show modal with higher z-index to appear above other modals
  modal.style.display = "flex";
  modal.style.zIndex = "10001"; // Higher than other modals (which use 999-1000)
  
  // Focus appropriate input
  if (isShapeField) {
    tagNameSelect.focus();
  } else {
    tagNameInput.focus();
  }
  
  // Close handlers
  const closeModal = () => {
    modal.style.display = "none";
    modal.style.zIndex = ""; // Reset z-index when closed
    // Reset custom input when closing
    if (tagNameCustomInput) {
      tagNameCustomInput.style.display = "none";
      tagNameCustomInput.value = "";
    }
  };
  
  closeBtn.onclick = closeModal;
  cancelBtn.onclick = closeModal;
  
  // Confirm handler
  confirmBtn.onclick = () => {
    // Get tag value from appropriate input
    let tagValue = "";
    if (isShapeField) {
      if (tagNameSelect.value === "Others") {
        // Get value from custom input when "Others" is selected
        tagValue = tagNameCustomInput.value.trim();
        if (!tagValue) {
          showToast("Please enter a custom shape name.", 'error');
          return;
        }
      } else {
        tagValue = tagNameSelect.value.trim();
      }
    } else {
      tagValue = tagNameInput.value.trim();
    }
    
    const tagPrice = parseFloat(tagPriceInput.value) || 0;
    const tagImageFile = tagImageInput.files[0];
    
    if (!tagValue) {
      showToast(isShapeField ? "Please select a shape." : "Please enter a tag name.", 'error');
      return;
    }
    
    let availableOptions = JSON.parse(container.dataset.availableOptions || "[]");
    
    // If not editing, check if tag already exists
    if (!isEdit && availableOptions.includes(tagValue)) {
      showToast("This tag already exists!", 'error');
      return;
    }
    
    // If editing, and name changed, check if new name already exists
    if (isEdit && oldTagValue !== tagValue && availableOptions.includes(tagValue)) {
      showToast("This tag name already exists!", 'error');
      return;
    }

    // Handle tag renaming/replacement if editing
    if (isEdit && oldTagValue !== tagValue) {
      // Replace in options array
      const idx = availableOptions.indexOf(oldTagValue);
      if (idx !== -1) availableOptions[idx] = tagValue;
      
      // Migrate price
      if (tagPrices[fieldId] && tagPrices[fieldId][oldTagValue] !== undefined) {
        tagPrices[fieldId][tagValue] = tagPrice;
        delete tagPrices[fieldId][oldTagValue];
      }
      
      // Migrate image
      if (tagImages[fieldId] && tagImages[fieldId][oldTagValue]) {
        tagImages[fieldId][tagValue] = tagImages[fieldId][oldTagValue];
        delete tagImages[fieldId][oldTagValue];
      }
      
      // Migrate visual config
      if (tagVisualConfigs[fieldId] && tagVisualConfigs[fieldId][oldTagValue]) {
        tagVisualConfigs[fieldId][tagValue] = tagVisualConfigs[fieldId][oldTagValue];
        delete tagVisualConfigs[fieldId][oldTagValue];
      }
      
      // Migrate selected values if this tag was selected
      const hiddenInput = document.getElementById(`${prefix}${fieldId}`);
      if (hiddenInput) {
        let selectedValues = JSON.parse(hiddenInput.value || "[]");
        const sIdx = selectedValues.indexOf(oldTagValue);
        if (sIdx !== -1) {
          selectedValues[sIdx] = tagValue;
          hiddenInput.value = JSON.stringify(selectedValues);
        }
      }
    } else if (!isEdit) {
      // Add new tag
      availableOptions.push(tagValue);
      
      // Automatically select the newly added tag
      // Check if we're in the manage customization fields context OR Add product form
      const isInManageFields = container.closest('#fieldsManagerContainer') !== null;
      const isAddForm = (prefix === "" || prefix === "standardOption_");
      
      if (isInManageFields || isAddForm) {
        const hiddenInput = document.getElementById(`${prefix}${fieldId}`);
        if (hiddenInput) {
          let selectedValues = JSON.parse(hiddenInput.value || "[]");
          // Add the new tag to selected values if not already selected
          if (!selectedValues.includes(tagValue)) {
            selectedValues.push(tagValue);
            hiddenInput.value = JSON.stringify(selectedValues);
          }
        }
      }
    }
    
    container.dataset.availableOptions = JSON.stringify(availableOptions);
    
    // Store/Update tag price
    if (!tagPrices[fieldId]) {
      tagPrices[fieldId] = {};
    }
    tagPrices[fieldId][tagValue] = tagPrice;
    
    // Store/Update tag image if provided
    if (tagImageFile) {
      if (!tagImages[fieldId]) {
        tagImages[fieldId] = {};
      }
      // Store the file object - will be uploaded when product is saved
      tagImages[fieldId][tagValue] = tagImageFile;
    } else if (modal.dataset.tagImageFile === "" && tagImages[fieldId]) {
      // Image was removed
      delete tagImages[fieldId][tagValue];
    }
    
    // Store visual config - only if toggle is enabled
    const visualConfig = collectKonvaVisualConfig();
    
    // CRITICAL: Ensure tagVisualConfigs is an object, not an array
    // This can happen if PHP returns [] instead of {} for empty configs
    if (Array.isArray(tagVisualConfigs)) {
      console.warn('[Konva Visual Config] tagVisualConfigs was an array, converting to object');
      tagVisualConfigs = {};
    }
    
    if (visualConfig) {
      if (!tagVisualConfigs[fieldId]) {
        tagVisualConfigs[fieldId] = {};
      }
      tagVisualConfigs[fieldId][tagValue] = visualConfig;
      console.log(`[Konva Visual Config] ✅ Saved config for ${fieldId}/${tagValue}:`, tagVisualConfigs[fieldId][tagValue]);
      console.log(`[Konva Visual Config] Current tagVisualConfigs:`, JSON.stringify(tagVisualConfigs));
    } else {
      // If visual preview is disabled, remove any existing config
      if (tagVisualConfigs[fieldId] && tagVisualConfigs[fieldId][tagValue]) {
        delete tagVisualConfigs[fieldId][tagValue];
        console.log(`[Konva Visual Config] Removed config for ${fieldId}/${tagValue} (preview disabled)`);
      }
    }
    
    // Re-render tags with updated list (will show new tag as selected)
    renderTags(container, availableOptions, prefix, fieldId);
    
    closeModal();
  };
  
  // Close on overlay click
  modal.onclick = (e) => {
    if (e.target === modal) closeModal();
  };
  
  // Enter key to confirm (for text input)
  if (!isShapeField) {
    tagNameInput.onkeypress = (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        confirmBtn.click();
      }
    };
  }
  
  // Enter key for custom shape input
  tagNameCustomInput.onkeypress = (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      confirmBtn.click();
    }
  };
  
  tagPriceInput.onkeypress = (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      confirmBtn.click();
    }
  };
  
  // Enter key for dropdown (select)
  if (isShapeField) {
    tagNameSelect.onkeypress = (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        confirmBtn.click();
      }
    };
  }
}

/**
 * Initialize mini Konva preview in the tag modal
 */
function initTagKonvaPreview() {
  const container = document.getElementById("tagKonvaPreview");
  if (!container || typeof Konva === 'undefined') return;
  
  // Destroy existing stage if any
  if (tagPreviewStage) {
    tagPreviewStage.destroy();
  }
  
  // Clear container
  container.innerHTML = '';
  
  const width = container.offsetWidth || 200;
  const height = container.offsetHeight || 80;
  
  tagPreviewStage = new Konva.Stage({
    container: 'tagKonvaPreview',
    width: width,
    height: height
  });
  
  tagPreviewLayer = new Konva.Layer();
  tagPreviewStage.add(tagPreviewLayer);
  
  // Initial render
  updateTagKonvaPreview();
}

/**
 * Apply a visual configuration object to the modal inputs
 * @param {Object} config - The visual configuration object
 */
function applyVisualConfigToInputs(config) {
  if (!config) return;
  
  // Enable the visual preview toggle when applying config
  const enableToggle = document.getElementById('enableVisualPreview');
  const visualConfigContent = document.getElementById('visualConfigContent');
  if (enableToggle && config.enabled !== false) {
    enableToggle.checked = true;
    if (visualConfigContent) {
      visualConfigContent.style.display = 'block';
    }
  }
  
  const effectSelect = document.getElementById('tagKonvaEffect');
  const fillColor = document.getElementById('tagFillColor');
  const fillColorHex = document.getElementById('tagFillColorHex');
  const strokeColor = document.getElementById('tagStrokeColor');
  const strokeColorHex = document.getElementById('tagStrokeColorHex');
  const opacity = document.getElementById('tagOpacity');
  const opacityValue = document.getElementById('tagOpacityValue');
  const strokeWidth = document.getElementById('tagStrokeWidth');
  const strokeWidthValue = document.getElementById('tagStrokeWidthValue');
  
  if (effectSelect && config.effectType) effectSelect.value = config.effectType;
  if (fillColor && config.fill) {
    fillColor.value = config.fill;
    if (fillColorHex) fillColorHex.value = config.fill;
  }
  if (strokeColor && config.stroke) {
    strokeColor.value = config.stroke;
    if (strokeColorHex) strokeColorHex.value = config.stroke;
  }
  if (opacity && config.opacity !== undefined) {
    opacity.value = config.opacity;
    if (opacityValue) opacityValue.textContent = config.opacity;
  }
  if (strokeWidth && config.strokeWidth !== undefined) {
    strokeWidth.value = config.strokeWidth;
    if (strokeWidthValue) strokeWidthValue.textContent = config.strokeWidth;
  }
  
  // Advanced options
  if (config.gradientEnd) {
    const el = document.getElementById('tagGradientEnd');
    if (el) el.value = config.gradientEnd;
  }
  if (config.gradientDirection) {
    const el = document.getElementById('tagGradientDirection');
    if (el) el.value = config.gradientDirection;
  }
  if (config.shadowBlur !== undefined) {
    const el = document.getElementById('tagShadowBlur');
    const val = document.getElementById('tagShadowBlurValue');
    if (el) el.value = config.shadowBlur;
    if (val) val.textContent = config.shadowBlur;
  }
  if (config.shadowOffset !== undefined) {
    const el = document.getElementById('tagShadowOffset');
    const val = document.getElementById('tagShadowOffsetValue');
    if (el) el.value = config.shadowOffset;
    if (val) val.textContent = config.shadowOffset;
  }
  if (config.shadowColor) {
    const el = document.getElementById('tagShadowColor');
    if (el) el.value = config.shadowColor;
  }
  if (config.shadowOpacity !== undefined) {
    const el = document.getElementById('tagShadowOpacity');
    const val = document.getElementById('tagShadowOpacityValue');
    if (el) el.value = config.shadowOpacity;
    if (val) val.textContent = config.shadowOpacity;
  }
  if (config.patternType) {
    const el = document.getElementById('tagPatternType');
    if (el) el.value = config.patternType;
  }
  if (config.patternDensity !== undefined) {
    const el = document.getElementById('tagPatternDensity');
    const val = document.getElementById('tagPatternDensityValue');
    if (el) el.value = config.patternDensity;
    if (val) val.textContent = config.patternDensity;
  }
  if (config.edgeStyle) {
    const el = document.getElementById('tagEdgeStyle');
    if (el) el.value = config.edgeStyle;
  }
  
  // Restore individual corner radii
  const linkCheckbox = document.getElementById('linkCornerRadius');
  const cornerPreviewBox = document.getElementById('cornerPreviewBox');
  
  if (config.cornerRadiusTL !== undefined || config.cornerRadiusTR !== undefined || 
      config.cornerRadiusBL !== undefined || config.cornerRadiusBR !== undefined) {
    const tl = config.cornerRadiusTL ?? 0;
    const tr = config.cornerRadiusTR ?? 0;
    const br = config.cornerRadiusBR ?? 0;
    const bl = config.cornerRadiusBL ?? 0;
    
    const elTL = document.getElementById('tagCornerRadiusTL');
    const elTR = document.getElementById('tagCornerRadiusTR');
    const elBR = document.getElementById('tagCornerRadiusBR');
    const elBL = document.getElementById('tagCornerRadiusBL');
    const elSlider = document.getElementById('tagCornerRadius');
    const elSliderVal = document.getElementById('tagCornerRadiusValue');
    const allCornersSlider = document.getElementById('allCornersSlider');
    
    if (elTL) elTL.value = tl;
    if (elTR) elTR.value = tr;
    if (elBR) elBR.value = br;
    if (elBL) elBL.value = bl;
    
    // Check if all corners are the same
    const allSame = tl === tr && tr === br && br === bl;
    const isLinked = config.linkCorners !== undefined ? config.linkCorners : allSame;
    
    if (linkCheckbox) {
      linkCheckbox.checked = isLinked;
    }
    if (allCornersSlider) {
      allCornersSlider.style.display = isLinked ? 'block' : 'none';
    }
    
    // Set slider to first corner value
    if (elSlider) elSlider.value = tl;
    if (elSliderVal) elSliderVal.textContent = tl;
    
    // Update mini preview box
    if (cornerPreviewBox) {
      cornerPreviewBox.style.borderRadius = `${tl}px ${tr}px ${br}px ${bl}px`;
    }
  } else if (config.cornerRadius !== undefined) {
    // Legacy single cornerRadius support
    const val = config.cornerRadius;
    const el = document.getElementById('tagCornerRadius');
    const elVal = document.getElementById('tagCornerRadiusValue');
    const elTL = document.getElementById('tagCornerRadiusTL');
    const elTR = document.getElementById('tagCornerRadiusTR');
    const elBR = document.getElementById('tagCornerRadiusBR');
    const elBL = document.getElementById('tagCornerRadiusBL');
    
    if (el) el.value = val;
    if (elVal) elVal.textContent = val;
    if (elTL) elTL.value = val;
    if (elTR) elTR.value = val;
    if (elBR) elBR.value = val;
    if (elBL) elBL.value = val;
    
    if (linkCheckbox) linkCheckbox.checked = true;
    if (cornerPreviewBox) {
      cornerPreviewBox.style.borderRadius = `${val}px`;
    }
  }
}

/**
 * Update the mini Konva preview based on current visual config inputs
 * Supports all advanced visual effects
 */
function updateTagKonvaPreview() {
  if (!tagPreviewLayer) return;
  
  // Clear existing shapes
  tagPreviewLayer.destroyChildren();
  
  // Get current values
  const effectType = document.getElementById("tagKonvaEffect")?.value || 'fill';
  const fillColor = document.getElementById("tagFillColor")?.value || '#E0F2F1';
  const strokeColor = document.getElementById("tagStrokeColor")?.value || '#333333';
  const opacity = parseFloat(document.getElementById("tagOpacity")?.value) || 0.9;
  const strokeWidth = parseInt(document.getElementById("tagStrokeWidth")?.value) || 4;
  
  // Advanced options
  const gradientEnd = document.getElementById("tagGradientEnd")?.value || '#FFFFFF';
  const gradientDirection = document.getElementById("tagGradientDirection")?.value || 'vertical';
  const shadowBlur = parseInt(document.getElementById("tagShadowBlur")?.value) || 10;
  const shadowOffset = parseInt(document.getElementById("tagShadowOffset")?.value) || 5;
  const shadowColor = document.getElementById("tagShadowColor")?.value || '#000000';
  const shadowOpacity = parseFloat(document.getElementById("tagShadowOpacity")?.value) || 0.3;
  const patternType = document.getElementById("tagPatternType")?.value || 'none';
  const patternDensity = parseInt(document.getElementById("tagPatternDensity")?.value) || 5;
  const edgeStyle = document.getElementById("tagEdgeStyle")?.value || 'solid';
  
  // Get individual corner radii
  const cornerRadiusTL = parseInt(document.getElementById("tagCornerRadiusTL")?.value) || 0;
  const cornerRadiusTR = parseInt(document.getElementById("tagCornerRadiusTR")?.value) || 0;
  const cornerRadiusBR = parseInt(document.getElementById("tagCornerRadiusBR")?.value) || 0;
  const cornerRadiusBL = parseInt(document.getElementById("tagCornerRadiusBL")?.value) || 0;
  // Konva uses [topLeft, topRight, bottomRight, bottomLeft]
  const cornerRadius = [cornerRadiusTL, cornerRadiusTR, cornerRadiusBR, cornerRadiusBL];
  
  const width = tagPreviewStage.width();
  const height = tagPreviewStage.height();
  
  // Draw glass panel preview
  const padding = 15;
  const glassWidth = width - (padding * 2);
  const glassHeight = height - (padding * 2);
  
  // Build shape config based on effect type
  let shapeConfig = {
    x: padding,
    y: padding,
    width: glassWidth,
    height: glassHeight,
    cornerRadius: cornerRadius
  };
  
  // Apply fill based on effect type
  if (effectType === 'gradient') {
    // Calculate gradient points based on direction
    let fillLinearGradientStartPoint, fillLinearGradientEndPoint;
    if (gradientDirection === 'horizontal') {
      fillLinearGradientStartPoint = { x: 0, y: 0 };
      fillLinearGradientEndPoint = { x: glassWidth, y: 0 };
    } else if (gradientDirection === 'diagonal') {
      fillLinearGradientStartPoint = { x: 0, y: 0 };
      fillLinearGradientEndPoint = { x: glassWidth, y: glassHeight };
    } else {
      // vertical (default)
      fillLinearGradientStartPoint = { x: 0, y: 0 };
      fillLinearGradientEndPoint = { x: 0, y: glassHeight };
    }
    
    if (gradientDirection === 'radial') {
      shapeConfig.fillRadialGradientStartPoint = { x: glassWidth / 2, y: glassHeight / 2 };
      shapeConfig.fillRadialGradientStartRadius = 0;
      shapeConfig.fillRadialGradientEndPoint = { x: glassWidth / 2, y: glassHeight / 2 };
      shapeConfig.fillRadialGradientEndRadius = Math.max(glassWidth, glassHeight) / 2;
      shapeConfig.fillRadialGradientColorStops = [0, fillColor, 1, gradientEnd];
    } else {
      shapeConfig.fillLinearGradientStartPoint = fillLinearGradientStartPoint;
      shapeConfig.fillLinearGradientEndPoint = fillLinearGradientEndPoint;
      shapeConfig.fillLinearGradientColorStops = [0, fillColor, 1, gradientEnd];
    }
    shapeConfig.opacity = opacity;
  } else {
    shapeConfig.fill = fillColor;
    shapeConfig.opacity = opacity;
  }
  
  // Apply stroke based on edge style
  if (strokeWidth > 0) {
    shapeConfig.stroke = strokeColor;
    shapeConfig.strokeWidth = strokeWidth;
    
    if (edgeStyle === 'dashed') {
      shapeConfig.dash = [10, 5];
    } else if (edgeStyle === 'dotted') {
      shapeConfig.dash = [2, 4];
    } else if (edgeStyle === 'double') {
      // Will add another shape for double effect
    }
  }
  
  // Apply shadow
  if (effectType === 'shadow' || effectType === 'custom') {
    shapeConfig.shadowColor = shadowColor;
    shapeConfig.shadowBlur = shadowBlur;
    shapeConfig.shadowOffset = { x: shadowOffset, y: shadowOffset };
    shapeConfig.shadowOpacity = shadowOpacity;
  }
  
  // Create main glass shape
  const glassRect = new Konva.Rect(shapeConfig);
  tagPreviewLayer.add(glassRect);
  
  // Add double border if selected
  if (edgeStyle === 'double' && strokeWidth > 2) {
    const inset = strokeWidth + 2;
    const innerCornerRadius = cornerRadius.map(r => Math.max(0, r - inset));
    const innerBorder = new Konva.Rect({
      x: padding + inset,
      y: padding + inset,
      width: glassWidth - inset * 2,
      height: glassHeight - inset * 2,
      stroke: strokeColor,
      strokeWidth: Math.max(1, strokeWidth / 2),
      cornerRadius: innerCornerRadius
    });
    tagPreviewLayer.add(innerBorder);
  }
  
  // Add beveled effect if selected - properly follows corner radius
  if (edgeStyle === 'beveled') {
    const lightColor = '#FFFFFF';
    const darkColor = '#666666';
    const bevelWidth = 3;
    const bevelOpacity = 0.5;
    
    // Get corner radii for easier access
    const [rTL, rTR, rBR, rBL] = cornerRadius;
    const x = padding;
    const y = padding;
    const w = glassWidth;
    const h = glassHeight;
    
    // Create highlight path (top and left edges) using custom shape
    const highlightPath = new Konva.Shape({
      sceneFunc: function(context, shape) {
        context.beginPath();
        // Start from bottom-left, go up
        context.moveTo(x, y + h - rBL);
        // Left edge up to top-left corner
        context.lineTo(x, y + rTL);
        // Top-left corner arc (if radius)
        if (rTL > 0) {
          context.arcTo(x, y, x + rTL, y, rTL);
        } else {
          context.lineTo(x, y);
        }
        // Top edge to top-right corner
        context.lineTo(x + w - rTR, y);
        // Top-right corner arc (partial for highlight)
        if (rTR > 0) {
          context.arcTo(x + w, y, x + w, y + rTR, rTR);
        }
        context.strokeShape(shape);
      },
      stroke: lightColor,
      strokeWidth: bevelWidth,
      opacity: bevelOpacity,
      lineCap: 'round',
      lineJoin: 'round'
    });
    tagPreviewLayer.add(highlightPath);
    
    // Create shadow path (bottom and right edges)
    const shadowPath = new Konva.Shape({
      sceneFunc: function(context, shape) {
        context.beginPath();
        // Start from top-right, go down
        context.moveTo(x + w, y + rTR);
        // Right edge down to bottom-right corner
        context.lineTo(x + w, y + h - rBR);
        // Bottom-right corner arc
        if (rBR > 0) {
          context.arcTo(x + w, y + h, x + w - rBR, y + h, rBR);
        } else {
          context.lineTo(x + w, y + h);
        }
        // Bottom edge to bottom-left corner
        context.lineTo(x + rBL, y + h);
        // Bottom-left corner arc (partial for shadow)
        if (rBL > 0) {
          context.arcTo(x, y + h, x, y + h - rBL, rBL);
        }
        context.strokeShape(shape);
      },
      stroke: darkColor,
      strokeWidth: bevelWidth,
      opacity: bevelOpacity,
      lineCap: 'round',
      lineJoin: 'round'
    });
    tagPreviewLayer.add(shadowPath);
  }
  
  // Add pattern overlay if selected
  if ((effectType === 'pattern' || effectType === 'custom') && patternType !== 'none') {
    drawPatternOverlay(tagPreviewLayer, padding, padding, glassWidth, glassHeight, patternType, patternDensity, strokeColor);
  }
  
  // Add some grid lines to simulate window panes (unless pattern is very dense)
  if (patternType === 'none' || patternDensity < 10) {
    const midX = padding + glassWidth / 2;
    const midY = padding + glassHeight / 2;
    
    tagPreviewLayer.add(new Konva.Line({
      points: [midX, padding + 4, midX, padding + glassHeight - 4],
      stroke: strokeColor,
      strokeWidth: Math.max(1, strokeWidth - 2),
      opacity: 0.4
    }));
    
    tagPreviewLayer.add(new Konva.Line({
      points: [padding + 4, midY, padding + glassWidth - 4, midY],
      stroke: strokeColor,
      strokeWidth: Math.max(1, strokeWidth - 2),
      opacity: 0.4
    }));
  }
  
  tagPreviewLayer.draw();
}

/**
 * Draw pattern overlay on a layer
 */
function drawPatternOverlay(layer, x, y, width, height, patternType, density, color) {
  const spacing = Math.max(5, 30 / density);
  
  if (patternType === 'lines') {
    for (let i = spacing; i < width; i += spacing) {
      layer.add(new Konva.Line({
        points: [x + i, y, x + i, y + height],
        stroke: color,
        strokeWidth: 0.5,
        opacity: 0.3
      }));
    }
  } else if (patternType === 'grid') {
    for (let i = spacing; i < width; i += spacing) {
      layer.add(new Konva.Line({
        points: [x + i, y, x + i, y + height],
        stroke: color,
        strokeWidth: 0.5,
        opacity: 0.3
      }));
    }
    for (let i = spacing; i < height; i += spacing) {
      layer.add(new Konva.Line({
        points: [x, y + i, x + width, y + i],
        stroke: color,
        strokeWidth: 0.5,
        opacity: 0.3
      }));
    }
  } else if (patternType === 'dots') {
    for (let i = spacing; i < width; i += spacing) {
      for (let j = spacing; j < height; j += spacing) {
        layer.add(new Konva.Circle({
          x: x + i,
          y: y + j,
          radius: 1,
          fill: color,
          opacity: 0.4
        }));
      }
    }
  } else if (patternType === 'crosshatch') {
    for (let i = 0; i < width + height; i += spacing) {
      layer.add(new Konva.Line({
        points: [x + Math.max(0, i - height), y + Math.min(height, i), x + Math.min(width, i), y + Math.max(0, i - width)],
        stroke: color,
        strokeWidth: 0.5,
        opacity: 0.2
      }));
      layer.add(new Konva.Line({
        points: [x + Math.max(0, width - i), y + Math.max(0, i - width), x + Math.min(width, width - i + height), y + Math.min(height, i)],
        stroke: color,
        strokeWidth: 0.5,
        opacity: 0.2
      }));
    }
  } else if (patternType === 'frosted') {
    // Frosted glass effect - random small dots
    for (let i = 0; i < density * 20; i++) {
      const dotX = x + Math.random() * width;
      const dotY = y + Math.random() * height;
      layer.add(new Konva.Circle({
        x: dotX,
        y: dotY,
        radius: Math.random() * 2 + 0.5,
        fill: '#FFFFFF',
        opacity: Math.random() * 0.3 + 0.1
      }));
    }
  } else if (patternType === 'rain') {
    // Rain/water drops effect
    for (let i = 0; i < density * 10; i++) {
      const dropX = x + Math.random() * width;
      const dropY = y + Math.random() * height;
      const dropLen = Math.random() * 10 + 5;
      layer.add(new Konva.Ellipse({
        x: dropX,
        y: dropY,
        radiusX: 1,
        radiusY: dropLen / 4,
        fill: '#FFFFFF',
        opacity: Math.random() * 0.4 + 0.1
      }));
    }
  }
}

/**
 * Reset all Konva visual config inputs to defaults
 */
function resetKonvaVisualConfig() {
  // Reset toggle state
  const enableToggle = document.getElementById("enableVisualPreview");
  const visualConfigContent = document.getElementById("visualConfigContent");
  if (enableToggle) {
    enableToggle.checked = false;
  }
  if (visualConfigContent) {
    visualConfigContent.style.display = "none";
  }
  
  // Basic options
  const tagKonvaEffect = document.getElementById("tagKonvaEffect");
  const tagFillColor = document.getElementById("tagFillColor");
  const tagFillColorHex = document.getElementById("tagFillColorHex");
  const tagStrokeColor = document.getElementById("tagStrokeColor");
  const tagStrokeColorHex = document.getElementById("tagStrokeColorHex");
  const tagOpacity = document.getElementById("tagOpacity");
  const tagOpacityValue = document.getElementById("tagOpacityValue");
  const tagStrokeWidth = document.getElementById("tagStrokeWidth");
  const tagStrokeWidthValue = document.getElementById("tagStrokeWidthValue");
  
  // Reset basic
  if (tagKonvaEffect) tagKonvaEffect.value = "fill";
  if (tagFillColor) tagFillColor.value = "#E0F2F1";
  if (tagFillColorHex) tagFillColorHex.value = "#E0F2F1";
  if (tagStrokeColor) tagStrokeColor.value = "#333333";
  if (tagStrokeColorHex) tagStrokeColorHex.value = "#333333";
  if (tagOpacity) tagOpacity.value = "0.9";
  if (tagOpacityValue) tagOpacityValue.textContent = "0.9";
  if (tagStrokeWidth) tagStrokeWidth.value = "4";
  if (tagStrokeWidthValue) tagStrokeWidthValue.textContent = "4";
  
  // Reset advanced options
  const tagGradientEnd = document.getElementById("tagGradientEnd");
  const tagGradientDirection = document.getElementById("tagGradientDirection");
  const tagShadowBlur = document.getElementById("tagShadowBlur");
  const tagShadowBlurValue = document.getElementById("tagShadowBlurValue");
  const tagShadowOffset = document.getElementById("tagShadowOffset");
  const tagShadowOffsetValue = document.getElementById("tagShadowOffsetValue");
  const tagShadowColor = document.getElementById("tagShadowColor");
  const tagShadowOpacity = document.getElementById("tagShadowOpacity");
  const tagShadowOpacityValue = document.getElementById("tagShadowOpacityValue");
  const tagPatternType = document.getElementById("tagPatternType");
  const tagPatternDensity = document.getElementById("tagPatternDensity");
  const tagPatternDensityValue = document.getElementById("tagPatternDensityValue");
  const tagEdgeStyle = document.getElementById("tagEdgeStyle");
  const tagCornerRadius = document.getElementById("tagCornerRadius");
  const tagCornerRadiusValue = document.getElementById("tagCornerRadiusValue");
  
  if (tagGradientEnd) tagGradientEnd.value = "#FFFFFF";
  if (tagGradientDirection) tagGradientDirection.value = "vertical";
  if (tagShadowBlur) tagShadowBlur.value = "10";
  if (tagShadowBlurValue) tagShadowBlurValue.textContent = "10";
  if (tagShadowOffset) tagShadowOffset.value = "5";
  if (tagShadowOffsetValue) tagShadowOffsetValue.textContent = "5";
  if (tagShadowColor) tagShadowColor.value = "#000000";
  if (tagShadowOpacity) tagShadowOpacity.value = "0.3";
  if (tagShadowOpacityValue) tagShadowOpacityValue.textContent = "0.3";
  if (tagPatternType) tagPatternType.value = "none";
  if (tagPatternDensity) tagPatternDensity.value = "5";
  if (tagPatternDensityValue) tagPatternDensityValue.textContent = "5";
  if (tagEdgeStyle) tagEdgeStyle.value = "solid";
  if (tagCornerRadius) tagCornerRadius.value = "0";
  if (tagCornerRadiusValue) tagCornerRadiusValue.textContent = "0";
  
  // Reset individual corner radii
  const cornerRadiusTL = document.getElementById("tagCornerRadiusTL");
  const cornerRadiusTR = document.getElementById("tagCornerRadiusTR");
  const cornerRadiusBR = document.getElementById("tagCornerRadiusBR");
  const cornerRadiusBL = document.getElementById("tagCornerRadiusBL");
  const linkCornerRadius = document.getElementById("linkCornerRadius");
  const allCornersSlider = document.getElementById("allCornersSlider");
  const cornerPreviewBox = document.getElementById("cornerPreviewBox");
  
  if (cornerRadiusTL) cornerRadiusTL.value = "0";
  if (cornerRadiusTR) cornerRadiusTR.value = "0";
  if (cornerRadiusBR) cornerRadiusBR.value = "0";
  if (cornerRadiusBL) cornerRadiusBL.value = "0";
  if (linkCornerRadius) linkCornerRadius.checked = true;
  if (allCornersSlider) allCornersSlider.style.display = "block";
  if (cornerPreviewBox) cornerPreviewBox.style.borderRadius = "0px";
  
  // Hide all advanced options
  const advancedKonvaOptions = document.getElementById("advancedKonvaOptions");
  const gradientOptions = document.getElementById("gradientOptions");
  const shadowOptions = document.getElementById("shadowOptions");
  const patternOptions = document.getElementById("patternOptions");
  const edgeOptions = document.getElementById("edgeOptions");
  
  if (advancedKonvaOptions) advancedKonvaOptions.style.display = "none";
  if (gradientOptions) gradientOptions.style.display = "none";
  if (shadowOptions) shadowOptions.style.display = "none";
  if (patternOptions) patternOptions.style.display = "none";
  if (edgeOptions) edgeOptions.style.display = "none";
}

/**
 * Setup event listeners for advanced Konva options
 */
function setupAdvancedKonvaEventListeners() {
  // Gradient options
  const tagGradientEnd = document.getElementById("tagGradientEnd");
  const tagGradientDirection = document.getElementById("tagGradientDirection");
  if (tagGradientEnd) tagGradientEnd.oninput = updateTagKonvaPreview;
  if (tagGradientDirection) tagGradientDirection.onchange = updateTagKonvaPreview;
  
  // Shadow options
  const tagShadowBlur = document.getElementById("tagShadowBlur");
  const tagShadowBlurValue = document.getElementById("tagShadowBlurValue");
  const tagShadowOffset = document.getElementById("tagShadowOffset");
  const tagShadowOffsetValue = document.getElementById("tagShadowOffsetValue");
  const tagShadowColor = document.getElementById("tagShadowColor");
  const tagShadowOpacity = document.getElementById("tagShadowOpacity");
  const tagShadowOpacityValue = document.getElementById("tagShadowOpacityValue");
  
  if (tagShadowBlur) {
    tagShadowBlur.oninput = () => {
      if (tagShadowBlurValue) tagShadowBlurValue.textContent = tagShadowBlur.value;
      updateTagKonvaPreview();
    };
  }
  if (tagShadowOffset) {
    tagShadowOffset.oninput = () => {
      if (tagShadowOffsetValue) tagShadowOffsetValue.textContent = tagShadowOffset.value;
      updateTagKonvaPreview();
    };
  }
  if (tagShadowColor) tagShadowColor.oninput = updateTagKonvaPreview;
  if (tagShadowOpacity) {
    tagShadowOpacity.oninput = () => {
      if (tagShadowOpacityValue) tagShadowOpacityValue.textContent = tagShadowOpacity.value;
      updateTagKonvaPreview();
    };
  }
  
  // Pattern options
  const tagPatternType = document.getElementById("tagPatternType");
  const tagPatternDensity = document.getElementById("tagPatternDensity");
  const tagPatternDensityValue = document.getElementById("tagPatternDensityValue");
  
  if (tagPatternType) tagPatternType.onchange = updateTagKonvaPreview;
  if (tagPatternDensity) {
    tagPatternDensity.oninput = () => {
      if (tagPatternDensityValue) tagPatternDensityValue.textContent = tagPatternDensity.value;
      updateTagKonvaPreview();
    };
  }
  
  // Edge options
  const tagEdgeStyle = document.getElementById("tagEdgeStyle");
  const tagCornerRadius = document.getElementById("tagCornerRadius");
  const tagCornerRadiusValue = document.getElementById("tagCornerRadiusValue");
  const linkCornerRadius = document.getElementById("linkCornerRadius");
  const cornerRadiusTL = document.getElementById("tagCornerRadiusTL");
  const cornerRadiusTR = document.getElementById("tagCornerRadiusTR");
  const cornerRadiusBL = document.getElementById("tagCornerRadiusBL");
  const cornerRadiusBR = document.getElementById("tagCornerRadiusBR");
  const allCornersSlider = document.getElementById("allCornersSlider");
  const cornerPreviewBox = document.getElementById("cornerPreviewBox");
  
  if (tagEdgeStyle) tagEdgeStyle.onchange = updateTagKonvaPreview;
  
  // Function to update the mini corner preview box
  const updateCornerPreviewBox = () => {
    if (cornerPreviewBox) {
      const tl = parseInt(cornerRadiusTL?.value) || 0;
      const tr = parseInt(cornerRadiusTR?.value) || 0;
      const bl = parseInt(cornerRadiusBL?.value) || 0;
      const br = parseInt(cornerRadiusBR?.value) || 0;
      cornerPreviewBox.style.borderRadius = `${tl}px ${tr}px ${br}px ${bl}px`;
    }
  };
  
  // Link checkbox toggle - show/hide individual controls
  if (linkCornerRadius) {
    linkCornerRadius.onchange = () => {
      const isLinked = linkCornerRadius.checked;
      if (allCornersSlider) {
        allCornersSlider.style.display = isLinked ? 'block' : 'none';
      }
      // When re-linking, sync all corners to the slider value
      if (isLinked && tagCornerRadius) {
        const val = parseInt(tagCornerRadius.value) || 0;
        if (cornerRadiusTL) cornerRadiusTL.value = val;
        if (cornerRadiusTR) cornerRadiusTR.value = val;
        if (cornerRadiusBL) cornerRadiusBL.value = val;
        if (cornerRadiusBR) cornerRadiusBR.value = val;
        updateCornerPreviewBox();
        updateTagKonvaPreview();
      }
    };
  }
  
  // Master slider for all corners when linked
  if (tagCornerRadius) {
    tagCornerRadius.oninput = () => {
      const val = parseInt(tagCornerRadius.value) || 0;
      if (tagCornerRadiusValue) tagCornerRadiusValue.textContent = val;
      
      // Update all corners when linked
      if (linkCornerRadius?.checked) {
        if (cornerRadiusTL) cornerRadiusTL.value = val;
        if (cornerRadiusTR) cornerRadiusTR.value = val;
        if (cornerRadiusBL) cornerRadiusBL.value = val;
        if (cornerRadiusBR) cornerRadiusBR.value = val;
      }
      updateCornerPreviewBox();
      updateTagKonvaPreview();
    };
  }
  
  // Individual corner inputs
  const cornerInputHandler = (input) => {
    if (input) {
      input.oninput = () => {
        // If linked, sync all corners to this value
        if (linkCornerRadius?.checked) {
          const val = parseInt(input.value) || 0;
          if (cornerRadiusTL) cornerRadiusTL.value = val;
          if (cornerRadiusTR) cornerRadiusTR.value = val;
          if (cornerRadiusBL) cornerRadiusBL.value = val;
          if (cornerRadiusBR) cornerRadiusBR.value = val;
          if (tagCornerRadius) tagCornerRadius.value = val;
          if (tagCornerRadiusValue) tagCornerRadiusValue.textContent = val;
        }
        updateCornerPreviewBox();
        updateTagKonvaPreview();
      };
    }
  };
  
  cornerInputHandler(cornerRadiusTL);
  cornerInputHandler(cornerRadiusTR);
  cornerInputHandler(cornerRadiusBL);
  cornerInputHandler(cornerRadiusBR);
}

/**
 * Collect all Konva visual config values into an object
 */
function collectKonvaVisualConfig() {
  // Check if visual preview is enabled via toggle
  const enableToggle = document.getElementById("enableVisualPreview");
  const isEnabled = enableToggle?.checked || false;
  
  // If not enabled, return null (no visual config for this tag)
  if (!isEnabled) {
    return null;
  }
  
  const effectType = document.getElementById("tagKonvaEffect")?.value || 'fill';
  const fillColor = document.getElementById("tagFillColor")?.value || '#E0F2F1';
  const strokeColor = document.getElementById("tagStrokeColor")?.value || '#333333';
  const opacity = parseFloat(document.getElementById("tagOpacity")?.value) || 0.9;
  const strokeWidth = parseInt(document.getElementById("tagStrokeWidth")?.value) || 4;
  
  // Build config object with enabled flag
  const config = {
    enabled: true,
    effectType: effectType,
    fill: fillColor,
    opacity: opacity,
    stroke: strokeColor,
    strokeWidth: strokeWidth
  };
  
  // Add advanced options based on effect type
  if (effectType === 'gradient' || effectType === 'custom') {
    config.gradientEnd = document.getElementById("tagGradientEnd")?.value || '#FFFFFF';
    config.gradientDirection = document.getElementById("tagGradientDirection")?.value || 'vertical';
  }
  
  if (effectType === 'shadow' || effectType === 'custom') {
    config.shadowBlur = parseInt(document.getElementById("tagShadowBlur")?.value) || 10;
    config.shadowOffset = parseInt(document.getElementById("tagShadowOffset")?.value) || 5;
    config.shadowColor = document.getElementById("tagShadowColor")?.value || '#000000';
    config.shadowOpacity = parseFloat(document.getElementById("tagShadowOpacity")?.value) || 0.3;
  }
  
  if (effectType === 'pattern' || effectType === 'custom') {
    config.patternType = document.getElementById("tagPatternType")?.value || 'none';
    config.patternDensity = parseInt(document.getElementById("tagPatternDensity")?.value) || 5;
  }
  
  if (effectType === 'edge' || effectType === 'custom') {
    config.edgeStyle = document.getElementById("tagEdgeStyle")?.value || 'solid';
    // Save individual corner radii
    config.cornerRadiusTL = parseInt(document.getElementById("tagCornerRadiusTL")?.value) || 0;
    config.cornerRadiusTR = parseInt(document.getElementById("tagCornerRadiusTR")?.value) || 0;
    config.cornerRadiusBR = parseInt(document.getElementById("tagCornerRadiusBR")?.value) || 0;
    config.cornerRadiusBL = parseInt(document.getElementById("tagCornerRadiusBL")?.value) || 0;
    config.linkCorners = document.getElementById("linkCornerRadius")?.checked ?? true;
  }
  
  return config;
}

/**
 * Collects all customization field values from the form
 * @param {string} prefix - Prefix for field IDs (e.g., "" for add or "edit" for edit)
 * @returns {Object} Object containing all field values
 */
function collectCustomizationData(prefix = "") {
  const data = {};
  // Use correct container ID based on prefix
  let container;
  if (prefix === "edit") {
    container = document.getElementById("editCustomizationFields");
  } else if (prefix === "standardOption_") {
    container = document.getElementById("standardOptionCustomizationFields");
  } else if (prefix === "editStandardOption_") {
    container = document.getElementById("editStandardOptionCustomizationFields");
  } else {
    container = document.getElementById("customizationFields");
  }
  
  if (!container) return data;
  
  // Get all inputs, selects, and checkboxes
  const inputs = container.querySelectorAll("input, select");
  
  inputs.forEach(input => {
    if (input.type === "hidden") {
      // For tags, read directly from hidden input (source of truth)
      // The hidden input is updated by toggleTagSelection when tags are clicked
      const fieldId = input.name;
      try {
        const parsed = JSON.parse(input.value || "[]");
        data[fieldId] = Array.isArray(parsed) ? parsed : [];
      } catch (e) {
        // If parsing fails, try to get from selected tags in DOM as fallback
        const tagContainer = container.querySelector(`[data-field-id="${fieldId}"]`);
        if (tagContainer) {
          const selectedTags = Array.from(tagContainer.querySelectorAll(".tag.selected")).map(t => t.dataset.value);
          data[fieldId] = selectedTags;
        } else {
          data[fieldId] = [];
        }
      }
    } else if (input.type === "checkbox") {
      data[input.name] = input.checked;
    } else if (input.type === "color") {
      data[input.name] = input.value;
    } else {
      data[input.name] = input.value || "";
    }
  });
  
  return data;
}

/**
 * Populate customization fields with existing data
 * @param {string} prefix - The prefix used for field IDs (e.g., "editStandardOption_")
 * @param {object} data - The data object containing field values
 */
function populateCustomizationFields(prefix = "", data = {}) {
  // Use correct container ID based on prefix
  let container;
  if (prefix === "edit") {
    container = document.getElementById("editCustomizationFields");
  } else if (prefix === "standardOption_") {
    container = document.getElementById("standardOptionCustomizationFields");
  } else if (prefix === "editStandardOption_") {
    container = document.getElementById("editStandardOptionCustomizationFields");
  } else {
    container = document.getElementById("customizationFields");
  }
  
  if (!container) return;
  
  // Get all inputs, selects, and checkboxes
  const inputs = container.querySelectorAll("input, select");
  
  inputs.forEach(input => {
    const fieldName = input.name;
    if (!data.hasOwnProperty(fieldName)) return;
    
    const value = data[fieldName];
    
    if (input.type === "hidden") {
      // Hidden inputs store tag selections as JSON arrays
      if (Array.isArray(value)) {
        input.value = JSON.stringify(value);
        // Also update the visible tag display if it exists
        const tagDisplay = container.querySelector(`[data-field="${fieldName}"] .tag-display`);
        if (tagDisplay) {
          tagDisplay.textContent = value.length > 0 ? value.join(", ") : "None selected";
        }
      }
    } else if (input.type === "checkbox") {
      input.checked = Boolean(value);
    } else if (input.type === "color") {
      input.value = value || "#000000";
    } else if (input.tagName === "SELECT") {
      input.value = value || "";
    } else {
      input.value = value || "";
    }
  });
}

// -------------------- STANDARD SERIES MANAGEMENT --------------------
let standardSeries = []; // Store series with measurements: [{ id, name, measurements: [{ id, width, height, price }] }]

// Track selected series for product customization (from Manage Customization Fields modal)
let selectedCustomizationSeries = null;

/**
 * Add a new series
 */
function addSeries() {
  // Remove any existing add series modal
  const existingModal = document.getElementById("addSeriesModal");
  if (existingModal) existingModal.remove();
  
  const modal = document.createElement("div");
  modal.className = "popup-overlay";
  modal.id = "addSeriesModal";
  modal.style.display = "flex";
  modal.innerHTML = `
    <div class="popup" style="width: 400px;">
      <span class="close-btn" id="closeSeriesModal">&times;</span>
      <h3>Add New Series</h3>
      <div class="form-group">
        <label for="seriesNameInput">Series Name</label>
        <input type="text" id="seriesNameInput" class="text-input" placeholder="Enter series name">
      </div>
      <div class="popup-actions">
        <button class="save-btn" id="confirmAddSeries">Add Series</button>
        <button class="cancel-btn" id="cancelAddSeries">Cancel</button>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  
  const closeModal = () => {
    modal.remove();
  };
  
  modal.querySelector("#closeSeriesModal").onclick = closeModal;
  modal.querySelector("#cancelAddSeries").onclick = closeModal;
  
  modal.querySelector("#confirmAddSeries").onclick = () => {
    const seriesName = modal.querySelector("#seriesNameInput").value.trim();
    
    if (!seriesName) {
      showToast("Please enter a series name.", 'error');
      return;
    }
    
    // Check if series name already exists
    if (standardSeries.some(s => s.name.toLowerCase() === seriesName.toLowerCase())) {
      showToast("A series with this name already exists.", 'error');
      return;
    }
    
    const series = {
      id: Date.now(),
      name: seriesName,
      measurements: []
    };
    
    standardSeries.push(series);
    renderStandardSeries();
    closeModal();
  };
  
  modal.onclick = (e) => {
    if (e.target === modal) closeModal();
  };
  
  modal.querySelector("#seriesNameInput").focus();
}

/**
 * Remove a series
 */
function removeSeries(seriesId) {
  standardSeries = standardSeries.filter(s => s.id !== seriesId);
  renderStandardSeries();
}

/**
 * Edit a series name
 */
function editSeries(seriesId) {
  const series = standardSeries.find(s => s.id === seriesId);
  if (!series) return;
  
  // Remove any existing edit series modal
  const existingModal = document.getElementById("editSeriesModal");
  if (existingModal) existingModal.remove();
  
  const modal = document.createElement("div");
  modal.className = "popup-overlay";
  modal.id = "editSeriesModal";
  modal.style.display = "flex";
  modal.innerHTML = `
    <div class="popup" style="width: 400px;">
      <span class="close-btn" id="closeEditSeriesModal">&times;</span>
      <h3>Edit Series</h3>
      <div class="form-group">
        <label for="editSeriesNameInput">Series Name</label>
        <input type="text" id="editSeriesNameInput" class="text-input" placeholder="Enter series name" value="${series.name}">
      </div>
      <div class="popup-actions">
        <button class="save-btn" id="confirmEditSeries">Save Changes</button>
        <button class="cancel-btn" id="cancelEditSeries">Cancel</button>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  
  const closeModal = () => {
    modal.remove();
  };
  
  modal.querySelector("#closeEditSeriesModal").onclick = closeModal;
  modal.querySelector("#cancelEditSeries").onclick = closeModal;
  
  modal.querySelector("#confirmEditSeries").onclick = () => {
    const seriesName = modal.querySelector("#editSeriesNameInput").value.trim();
    
    if (!seriesName) {
      showToast("Please enter a series name.", 'error');
      return;
    }
    
    // Check if series name already exists (excluding current series)
    if (standardSeries.some(s => s.id !== seriesId && s.name.toLowerCase() === seriesName.toLowerCase())) {
      showToast("A series with this name already exists.", 'error');
      return;
    }
    
    // Update series name
    series.name = seriesName;
    renderStandardSeries();
    closeModal();
    showToast("Series updated successfully.", 'success');
  };
  
  modal.onclick = (e) => {
    if (e.target === modal) closeModal();
  };
  
  modal.querySelector("#editSeriesNameInput").focus();
  modal.querySelector("#editSeriesNameInput").select();
}

/**
 * Add a direct order option to a series (measurements, shape, and other options)
 */
function addDirectOrderOptionToSeries(seriesId) {
  // Get current category and subcategory from the form
  // Check which popup is open (add or edit)
  const editPopup = document.getElementById("editPopup");
  const isEditMode = editPopup && editPopup.style.display === "flex";
  
  const categorySelect = isEditMode 
    ? document.getElementById("editProductCategory")
    : document.getElementById("productCategory");
  const subcategorySelect = isEditMode
    ? document.getElementById("editProductSubcategory")
    : document.getElementById("productSubcategory");
  const category = categorySelect ? categorySelect.value : "";
  const subcategory = subcategorySelect ? subcategorySelect.value : "";
  
  // Remove any existing add option modal
  const existingModal = document.getElementById("addDirectOrderOptionModal");
  if (existingModal) existingModal.remove();
  
  const modal = document.createElement("div");
  modal.className = "popup-overlay";
  modal.id = "addDirectOrderOptionModal";
  modal.style.display = "flex";
  modal.innerHTML = `
    <div class="popup" style="width: 600px; max-height: 90vh; overflow-y: auto;">
      <span class="close-btn" id="closeDirectOrderOptionModal">&times;</span>
      <h3>Add Direct Order Option</h3>
      <div class="form-group">
        <label for="measurementWidthInput">Width</label>
        <div class="unit-wrapper">
          <div class="input-wrapper">
            <input type="number" id="measurementWidthInput" class="text-input" placeholder="Enter width" step="0.1" min="0.1">
          </div>
          <div class="unit-control">
            <button type="button" class="unit-select" id="btn-unit-width-measurement" data-current-unit="in">
              Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>
            </button>
            <div class="unit-dropdown" id="dropdown-width-measurement">
              <div class="unit-option" data-value="in">Inches</div>
              <div class="unit-option" data-value="cm">Centimeters</div>
              <div class="unit-option" data-value="mm">Millimeters</div>
            </div>
          </div>
        </div>
      </div>
      <!-- Lock/Unlock Button -->
      <div class="form-group" style="display: flex; justify-content: center; margin: 10px 0;">
        <button type="button" id="admin-dimension-lock-btn" class="dimension-lock-btn" title="Lock dimensions to keep height and width equal">
          <svg id="admin-lock-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          <svg id="admin-unlock-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
          </svg>
        </button>
      </div>
      <div class="form-group">
        <label for="measurementHeightInput">Height</label>
        <div class="unit-wrapper">
          <div class="input-wrapper">
            <input type="number" id="measurementHeightInput" class="text-input" placeholder="Enter height" step="0.1" min="0.1">
          </div>
          <div class="unit-control">
            <button type="button" class="unit-select" id="btn-unit-height-measurement" data-current-unit="in">
              Inches <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>
            </button>
            <div class="unit-dropdown" id="dropdown-height-measurement">
              <div class="unit-option" data-value="in">Inches</div>
              <div class="unit-option" data-value="cm">Centimeters</div>
              <div class="unit-option" data-value="mm">Millimeters</div>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="measurementPriceInput">Price (₱)</label>
        <div class="price-input">
          <span>₱</span>
          <input type="number" id="measurementPriceInput" class="input-text" placeholder="0.00" step="0.01" min="0">
        </div>
      </div>
      <!-- Customization Fields Container (same as Customize Build tab) -->
      <div class="form-group" style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
          <label style="font-weight: 600; margin: 0;">Customization Options</label>
          ${category && subcategory ? `<button type="button" class="manage-fields-btn" id="directOrderOptionManageFieldsBtn" style="padding: 6px 12px; font-size: 13px;">
            <i class="fas fa-cog"></i> Manage Customization Fields
          </button>` : ''}
        </div>
        <div id="standardOptionCustomizationFields" class="customization-fields-container">
          ${category && subcategory ? '<p style="color: #999; font-size: 12px;">Loading customization fields...</p>' : '<p style="color: #999; font-size: 12px;">Please select category and subcategory first to see customization options.</p>'}
        </div>
      </div>
      <div class="popup-actions">
        <button class="save-btn" id="confirmAddDirectOrderOption">Add Option</button>
        <button class="cancel-btn" id="cancelAddDirectOrderOption">Cancel</button>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  
  // Generate customization fields if category and subcategory are available
  const customizationContainer = modal.querySelector("#standardOptionCustomizationFields");
  if (category && subcategory && customizationContainer) {
    generateCustomizationFields(subcategory, customizationContainer, "standardOption_", category);
    
    // Setup manage customization fields button
    const manageFieldsBtn = modal.querySelector("#directOrderOptionManageFieldsBtn");
    if (manageFieldsBtn) {
      manageFieldsBtn.addEventListener("click", () => {
        showManageCustomizationFields(category, subcategory, {}, null);
      });
    }
  }
  
  // Setup unit dropdowns
  const unitMap = {
    'in': { name: 'Inches', toMm: 25.4 },
    'cm': { name: 'Centimeters', toMm: 10 },
    'mm': { name: 'Millimeters', toMm: 1 }
  };
  
  function setupMeasurementUnitDropdown(btnId, dropdownId, inputId) {
    const btn = modal.querySelector(`#${btnId}`);
    const dropdown = modal.querySelector(`#${dropdownId}`);
    const input = modal.querySelector(`#${inputId}`);
    
    if (!btn || !dropdown) return;
    
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      // Close other dropdowns
      modal.querySelectorAll('.unit-dropdown').forEach(d => {
        if (d !== dropdown) d.classList.remove('show');
      });
      dropdown.classList.toggle('show');
    });
    
    dropdown.querySelectorAll('.unit-option').forEach(opt => {
      opt.addEventListener('click', (e) => {
        e.stopPropagation();
        const newUnit = opt.dataset.value;
        const oldUnit = btn.dataset.currentUnit || 'in';
        
        // Convert the value when unit changes
        if (input && oldUnit !== newUnit) {
          const currentValue = parseFloat(input.value) || 0;
          if (currentValue > 0) {
            // Convert: oldUnit -> mm -> newUnit
            const valueInMm = currentValue * (unitMap[oldUnit]?.toMm || 25.4);
            const convertedValue = valueInMm / (unitMap[newUnit]?.toMm || 25.4);
            input.value = Math.round(convertedValue * 100) / 100;
          }
        }
        
        btn.innerHTML = `${unitMap[newUnit].name} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>`;
        btn.dataset.currentUnit = newUnit;
        dropdown.classList.remove('show');
      });
    });
  }
  
  setupMeasurementUnitDropdown('btn-unit-width-measurement', 'dropdown-width-measurement', 'measurementWidthInput');
  setupMeasurementUnitDropdown('btn-unit-height-measurement', 'dropdown-height-measurement', 'measurementHeightInput');
  
  // Lock/Unlock functionality for admin measurement modal
  let adminDimensionsLocked = false;
  const adminLockBtn = modal.querySelector('#admin-dimension-lock-btn');
  const adminLockIcon = modal.querySelector('#admin-lock-icon');
  const adminUnlockIcon = modal.querySelector('#admin-unlock-icon');
  const measurementWidthInput = modal.querySelector('#measurementWidthInput');
  const measurementHeightInput = modal.querySelector('#measurementHeightInput');
  const btnUnitWidthMeasurement = modal.querySelector('#btn-unit-width-measurement');
  const btnUnitHeightMeasurement = modal.querySelector('#btn-unit-height-measurement');
  
  function updateAdminMeasurementDimensions(type, value) {
    if (isNaN(value) || value <= 0) return;
    
    if (adminDimensionsLocked) {
      const otherInput = type === 'width' ? measurementHeightInput : measurementWidthInput;
      const otherBtn = type === 'width' ? btnUnitHeightMeasurement : btnUnitWidthMeasurement;
      const currentBtn = type === 'width' ? btnUnitWidthMeasurement : btnUnitHeightMeasurement;
      
      // Convert value to the other dimension's unit if needed
      let convertedValue = parseFloat(value);
      const currentUnit = currentBtn ? currentBtn.dataset.currentUnit : 'in';
      const otherUnit = otherBtn ? otherBtn.dataset.currentUnit : 'in';
      
      if (currentUnit !== otherUnit) {
        // Convert to millimeters first, then to target unit
        const valueInMm = convertedValue * (unitMap[currentUnit]?.toMm || 1);
        convertedValue = valueInMm / (unitMap[otherUnit]?.toMm || 1);
      }
      
      if (otherInput) {
        otherInput.value = Math.round(convertedValue * 100) / 100;
      }
    }
  }
  
  if (adminLockBtn) {
    adminLockBtn.addEventListener('click', () => {
      adminDimensionsLocked = !adminDimensionsLocked;
      
      if (adminDimensionsLocked) {
        adminLockIcon.style.display = 'none';
        adminUnlockIcon.style.display = 'block';
        adminLockBtn.classList.add('locked');
        adminLockBtn.title = 'Unlock dimensions to allow independent height and width';
        
        // When locking, sync the current values (make height equal to width)
        if (measurementWidthInput && measurementHeightInput) {
          const widthValue = parseFloat(measurementWidthInput.value) || 0;
          const widthUnit = btnUnitWidthMeasurement ? btnUnitWidthMeasurement.dataset.currentUnit : 'in';
          const heightUnit = btnUnitHeightMeasurement ? btnUnitHeightMeasurement.dataset.currentUnit : 'in';
          
          // Convert width to height's unit
          let convertedValue = widthValue;
          if (widthUnit !== heightUnit) {
            const valueInMm = widthValue * (unitMap[widthUnit]?.toMm || 1);
            convertedValue = valueInMm / (unitMap[heightUnit]?.toMm || 1);
          }
          
          measurementHeightInput.value = Math.round(convertedValue * 100) / 100;
        }
      } else {
        adminLockIcon.style.display = 'block';
        adminUnlockIcon.style.display = 'none';
        adminLockBtn.classList.remove('locked');
        adminLockBtn.title = 'Lock dimensions to keep height and width equal';
      }
    });
  }
  
  // Add input listeners for locked dimensions
  if (measurementWidthInput) {
    measurementWidthInput.addEventListener('input', (e) => {
      updateAdminMeasurementDimensions('width', e.target.value);
    });
  }
  
  if (measurementHeightInput) {
    measurementHeightInput.addEventListener('input', (e) => {
      updateAdminMeasurementDimensions('height', e.target.value);
    });
  }
  
  // Close dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.unit-control')) {
      modal.querySelectorAll('.unit-dropdown').forEach(d => d.classList.remove('show'));
    }
  });
  
  const closeModal = () => {
    modal.remove();
  };
  
  modal.querySelector("#closeDirectOrderOptionModal").onclick = closeModal;
  modal.querySelector("#cancelAddDirectOrderOption").onclick = closeModal;
  
  modal.querySelector("#confirmAddDirectOrderOption").onclick = () => {
    const width = parseFloat(modal.querySelector("#measurementWidthInput").value);
    const height = parseFloat(modal.querySelector("#measurementHeightInput").value);
    const price = parseFloat(modal.querySelector("#measurementPriceInput").value);
    const widthUnit = modal.querySelector("#btn-unit-width-measurement").dataset.currentUnit || 'in';
    const heightUnit = modal.querySelector("#btn-unit-height-measurement").dataset.currentUnit || 'in';
    
    if (!width || width <= 0) {
      showToast("Please enter a valid width.", 'error');
      return;
    }
    
    if (!height || height <= 0) {
      showToast("Please enter a valid height.", 'error');
      return;
    }
    
    if (!price || price < 0) {
      showToast("Please enter a valid price.", 'error');
      return;
    }
    
    // Collect customization data from the modal (same as Customize Build tab)
    const customizationData = collectCustomizationData("standardOption_");
    
    const series = standardSeries.find(s => s.id === seriesId);
    if (series) {
      const option = {
        id: Date.now(),
        width: width,
        height: height,
        widthUnit: widthUnit,
        heightUnit: heightUnit,
        price: price,
        customization: customizationData // Store all customization fields as JSON object
      };
      series.measurements.push(option);
      renderStandardSeries();
    }
    
    closeModal();
  };
  
  modal.onclick = (e) => {
    if (e.target === modal) closeModal();
  };
  
  modal.querySelector("#measurementWidthInput").focus();
}

/**
 * Remove a measurement from a series
 */
function removeMeasurementFromSeries(seriesId, measurementId) {
  const series = standardSeries.find(s => s.id === seriesId);
  if (series) {
    series.measurements = series.measurements.filter(m => m.id !== measurementId);
    renderStandardSeries();
  }
}

/**
 * Edit a measurement/option in a series
 */
function editMeasurement(seriesId, measurementId) {
  const series = standardSeries.find(s => s.id === seriesId);
  if (!series) return;
  
  const measurement = series.measurements.find(m => m.id === measurementId);
  if (!measurement) return;
  
  // Get current category and subcategory from the form
  // Check which popup is open (add or edit)
  const editPopup = document.getElementById("editPopup");
  const isEditMode = editPopup && editPopup.style.display === "flex";
  
  const categorySelect = isEditMode
    ? document.getElementById("editProductCategory")
    : document.getElementById("productCategory");
  const subcategorySelect = isEditMode
    ? document.getElementById("editProductSubcategory")
    : document.getElementById("productSubcategory");
  const category = categorySelect ? categorySelect.value : "";
  const subcategory = subcategorySelect ? subcategorySelect.value : "";
  
  // Remove any existing edit option modal
  const existingModal = document.getElementById("editDirectOrderOptionModal");
  if (existingModal) existingModal.remove();
  
  const modal = document.createElement("div");
  modal.className = "popup-overlay";
  modal.id = "editDirectOrderOptionModal";
  modal.style.display = "flex";
  modal.innerHTML = `
    <div class="popup" style="width: 600px; max-height: 90vh; overflow-y: auto;">
      <span class="close-btn" id="closeEditDirectOrderOptionModal">&times;</span>
      <h3>Edit Direct Order Option</h3>
      <div class="form-group">
        <label for="editMeasurementWidthInput">Width</label>
        <div class="unit-wrapper">
          <div class="input-wrapper">
            <input type="number" id="editMeasurementWidthInput" class="text-input" placeholder="Enter width" step="0.1" min="0.1" value="${measurement.width}">
          </div>
          <div class="unit-control">
            <button type="button" class="unit-select" id="btn-unit-edit-width-measurement" data-current-unit="${measurement.widthUnit || 'in'}">
              ${measurement.widthUnit === 'cm' ? 'Centimeters' : measurement.widthUnit === 'mm' ? 'Millimeters' : 'Inches'} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>
            </button>
            <div class="unit-dropdown" id="dropdown-edit-width-measurement">
              <div class="unit-option" data-value="in">Inches</div>
              <div class="unit-option" data-value="cm">Centimeters</div>
              <div class="unit-option" data-value="mm">Millimeters</div>
            </div>
          </div>
        </div>
      </div>
      <!-- Lock/Unlock Button -->
      <div class="form-group" style="display: flex; justify-content: center; margin: 10px 0;">
        <button type="button" id="admin-edit-dimension-lock-btn" class="dimension-lock-btn" title="Lock dimensions to keep height and width equal">
          <svg id="admin-edit-lock-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          <svg id="admin-edit-unlock-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
          </svg>
        </button>
      </div>
      <div class="form-group">
        <label for="editMeasurementHeightInput">Height</label>
        <div class="unit-wrapper">
          <div class="input-wrapper">
            <input type="number" id="editMeasurementHeightInput" class="text-input" placeholder="Enter height" step="0.1" min="0.1" value="${measurement.height}">
          </div>
          <div class="unit-control">
            <button type="button" class="unit-select" id="btn-unit-edit-height-measurement" data-current-unit="${measurement.heightUnit || 'in'}">
              ${measurement.heightUnit === 'cm' ? 'Centimeters' : measurement.heightUnit === 'mm' ? 'Millimeters' : 'Inches'} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>
            </button>
            <div class="unit-dropdown" id="dropdown-edit-height-measurement">
              <div class="unit-option" data-value="in">Inches</div>
              <div class="unit-option" data-value="cm">Centimeters</div>
              <div class="unit-option" data-value="mm">Millimeters</div>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="editMeasurementPriceInput">Price (₱)</label>
        <div class="price-input">
          <span>₱</span>
          <input type="number" id="editMeasurementPriceInput" class="input-text" placeholder="0.00" step="0.01" min="0" value="${measurement.price}">
        </div>
      </div>
      <!-- Customization Fields Container (same as Customize Build tab) -->
      <div class="form-group" style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
          <label style="font-weight: 600; margin: 0;">Customization Options</label>
          ${category && subcategory ? `<button type="button" class="manage-fields-btn" id="editDirectOrderOptionManageFieldsBtn" style="padding: 6px 12px; font-size: 13px;">
            <i class="fas fa-cog"></i> Manage Customization Fields
          </button>` : ''}
        </div>
        <div id="editStandardOptionCustomizationFields" class="customization-fields-container">
          ${category && subcategory ? '<p style="color: #999; font-size: 12px;">Loading customization fields...</p>' : '<p style="color: #999; font-size: 12px;">Please select category and subcategory first to see customization options.</p>'}
        </div>
      </div>
      <div class="popup-actions">
        <button class="save-btn" id="confirmEditDirectOrderOption">Save Changes</button>
        <button class="cancel-btn" id="cancelEditDirectOrderOption">Cancel</button>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  
  // Generate customization fields if category and subcategory are available
  const customizationContainer = modal.querySelector("#editStandardOptionCustomizationFields");
  if (category && subcategory && customizationContainer) {
    generateCustomizationFields(subcategory, customizationContainer, "editStandardOption_", category);
    
    // Populate existing customization data
    if (measurement.customization && Object.keys(measurement.customization).length > 0) {
      populateCustomizationFields("editStandardOption_", measurement.customization);
    }
    
    // Setup manage customization fields button
    const manageFieldsBtn = modal.querySelector("#editDirectOrderOptionManageFieldsBtn");
    if (manageFieldsBtn) {
      manageFieldsBtn.addEventListener("click", () => {
        showManageCustomizationFields(category, subcategory, {}, null);
      });
    }
  }
  
  // Setup unit dropdowns (similar to addDirectOrderOptionToSeries)
  const unitMap = {
    'in': { name: 'Inches', toMm: 25.4 },
    'cm': { name: 'Centimeters', toMm: 10 },
    'mm': { name: 'Millimeters', toMm: 1 }
  };
  
  function setupEditMeasurementUnitDropdown(btnId, dropdownId, inputId) {
    const btn = modal.querySelector(`#${btnId}`);
    const dropdown = modal.querySelector(`#${dropdownId}`);
    const input = modal.querySelector(`#${inputId}`);
    
    if (!btn || !dropdown) return;
    
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      // Close other dropdowns
      modal.querySelectorAll('.unit-dropdown').forEach(d => {
        if (d.id !== dropdownId) d.style.display = 'none';
      });
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });
    
    dropdown.querySelectorAll('.unit-option').forEach(option => {
      option.addEventListener('click', (e) => {
        e.stopPropagation();
        const newUnit = option.dataset.value;
        const oldUnit = btn.dataset.currentUnit || 'in';
        const unitName = unitMap[newUnit]?.name || newUnit;
        
        // Convert the value when unit changes
        if (input && oldUnit !== newUnit) {
          const currentValue = parseFloat(input.value) || 0;
          if (currentValue > 0) {
            // Convert: oldUnit -> mm -> newUnit
            const valueInMm = currentValue * (unitMap[oldUnit]?.toMm || 25.4);
            const convertedValue = valueInMm / (unitMap[newUnit]?.toMm || 25.4);
            input.value = Math.round(convertedValue * 100) / 100;
          }
        }
        
        // Update button with innerHTML (not textContent) to render SVG properly
        btn.innerHTML = `${unitName} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>`;
        btn.dataset.currentUnit = newUnit;
        dropdown.style.display = 'none';
      });
    });
  }
  
  setupEditMeasurementUnitDropdown('btn-unit-edit-width-measurement', 'dropdown-edit-width-measurement', 'editMeasurementWidthInput');
  setupEditMeasurementUnitDropdown('btn-unit-edit-height-measurement', 'dropdown-edit-height-measurement', 'editMeasurementHeightInput');
  
  // Setup dimension lock functionality
  let isLocked = false;
  const adminLockBtn = modal.querySelector("#admin-edit-dimension-lock-btn");
  const adminLockIcon = modal.querySelector("#admin-edit-lock-icon");
  const adminUnlockIcon = modal.querySelector("#admin-edit-unlock-icon");
  const measurementWidthInput = modal.querySelector("#editMeasurementWidthInput");
  const measurementHeightInput = modal.querySelector("#editMeasurementHeightInput");
  
  if (adminLockBtn) {
    adminLockBtn.addEventListener('click', () => {
      isLocked = !isLocked;
      if (isLocked) {
        adminLockIcon.style.display = 'none';
        adminUnlockIcon.style.display = 'block';
        adminLockBtn.classList.add('locked');
        adminLockBtn.title = 'Unlock dimensions to allow different height and width';
        // Sync height with width
        if (measurementWidthInput && measurementHeightInput) {
          const widthValue = parseFloat(measurementWidthInput.value) || 0;
          const widthUnit = modal.querySelector("#btn-unit-edit-width-measurement").dataset.currentUnit || 'in';
          const heightUnit = modal.querySelector("#btn-unit-edit-height-measurement").dataset.currentUnit || 'in';
          
          let convertedValue = widthValue;
          if (widthUnit !== heightUnit) {
            const valueInMm = widthValue * (unitMap[widthUnit]?.toMm || 1);
            convertedValue = valueInMm / (unitMap[heightUnit]?.toMm || 1);
          }
          
          measurementHeightInput.value = Math.round(convertedValue * 100) / 100;
        }
      } else {
        adminLockIcon.style.display = 'block';
        adminUnlockIcon.style.display = 'none';
        adminLockBtn.classList.remove('locked');
        adminLockBtn.title = 'Lock dimensions to keep height and width equal';
      }
    });
  }
  
  // Add input listeners for locked dimensions
  if (measurementWidthInput) {
    measurementWidthInput.addEventListener('input', (e) => {
      if (isLocked && measurementHeightInput) {
        const widthValue = parseFloat(e.target.value) || 0;
        const widthUnit = modal.querySelector("#btn-unit-edit-width-measurement").dataset.currentUnit || 'in';
        const heightUnit = modal.querySelector("#btn-unit-edit-height-measurement").dataset.currentUnit || 'in';
        
        let convertedValue = widthValue;
        if (widthUnit !== heightUnit) {
          const valueInMm = widthValue * (unitMap[widthUnit]?.toMm || 1);
          convertedValue = valueInMm / (unitMap[heightUnit]?.toMm || 1);
        }
        
        measurementHeightInput.value = Math.round(convertedValue * 100) / 100;
      }
    });
  }
  
  if (measurementHeightInput) {
    measurementHeightInput.addEventListener('input', (e) => {
      if (isLocked && measurementWidthInput) {
        const heightValue = parseFloat(e.target.value) || 0;
        const widthUnit = modal.querySelector("#btn-unit-edit-width-measurement").dataset.currentUnit || 'in';
        const heightUnit = modal.querySelector("#btn-unit-edit-height-measurement").dataset.currentUnit || 'in';
        
        let convertedValue = heightValue;
        if (heightUnit !== widthUnit) {
          const valueInMm = heightValue * (unitMap[heightUnit]?.toMm || 1);
          convertedValue = valueInMm / (unitMap[widthUnit]?.toMm || 1);
        }
        
        measurementWidthInput.value = Math.round(convertedValue * 100) / 100;
      }
    });
  }
  
  // Close dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (!modal.contains(e.target)) {
      modal.querySelectorAll('.unit-dropdown').forEach(d => d.style.display = 'none');
    }
  });
  
  const closeModal = () => {
    modal.remove();
  };
  
  modal.querySelector("#closeEditDirectOrderOptionModal").onclick = closeModal;
  modal.querySelector("#cancelEditDirectOrderOption").onclick = closeModal;
  
  modal.querySelector("#confirmEditDirectOrderOption").onclick = () => {
    const width = parseFloat(modal.querySelector("#editMeasurementWidthInput").value);
    const height = parseFloat(modal.querySelector("#editMeasurementHeightInput").value);
    const price = parseFloat(modal.querySelector("#editMeasurementPriceInput").value);
    const widthUnit = modal.querySelector("#btn-unit-edit-width-measurement").dataset.currentUnit || 'in';
    const heightUnit = modal.querySelector("#btn-unit-edit-height-measurement").dataset.currentUnit || 'in';
    
    if (!width || width <= 0) {
      showToast("Please enter a valid width.", 'error');
      return;
    }
    
    if (!height || height <= 0) {
      showToast("Please enter a valid height.", 'error');
      return;
    }
    
    if (!price || price < 0) {
      showToast("Please enter a valid price.", 'error');
      return;
    }
    
    // Collect customization data from the modal
    const customizationData = collectCustomizationData("editStandardOption_");
    
    // Update the measurement
    measurement.width = width;
    measurement.height = height;
    measurement.widthUnit = widthUnit;
    measurement.heightUnit = heightUnit;
    measurement.price = price;
    measurement.customization = customizationData;
    
    renderStandardSeries();
    closeModal();
    showToast("Measurement updated successfully.", 'success');
  };
  
  modal.onclick = (e) => {
    if (e.target === modal) closeModal();
  };
  
  modal.querySelector("#editMeasurementWidthInput").focus();
}

/**
 * Render all series with their measurements
 */
function renderStandardSeries() {
  // Check which popup is open and use appropriate container
  const editPopup = document.getElementById("editPopup");
  const addPopup = document.getElementById("productPopup");
  let container;
  
  if (editPopup && editPopup.style.display === "flex") {
    container = document.getElementById("editStandardSeriesContainer");
  } else {
    container = document.getElementById("standardSeriesContainer");
  }
  
  if (!container) return;
  
  container.innerHTML = "";
  
  if (standardSeries.length === 0) {
    const emptyMsg = document.createElement("p");
    emptyMsg.style.color = "#999";
    emptyMsg.style.fontSize = "13px";
    emptyMsg.style.textAlign = "center";
    emptyMsg.style.padding = "10px";
    emptyMsg.textContent = "No series added yet. Click \"Add Series\" to start.";
    container.appendChild(emptyMsg);
    return;
  }
  
  standardSeries.forEach(series => {
    const seriesItem = document.createElement("div");
    seriesItem.className = "series-item";
    
    // Series Header
    const seriesHeader = document.createElement("div");
    seriesHeader.className = "series-header";
    
    const seriesName = document.createElement("span");
    seriesName.className = "series-name";
    seriesName.textContent = series.name;
    
    const seriesActions = document.createElement("div");
    seriesActions.className = "series-actions";
    
    const addMeasurementBtn = document.createElement("button");
    addMeasurementBtn.className = "add-measurement-btn";
    addMeasurementBtn.type = "button";
    addMeasurementBtn.innerHTML = '<i class="fas fa-plus"></i> Add Option';
    addMeasurementBtn.onclick = () => addDirectOrderOptionToSeries(series.id);
    
    const editSeriesBtn = document.createElement("button");
    editSeriesBtn.className = "edit-series-btn";
    editSeriesBtn.type = "button";
    editSeriesBtn.innerHTML = '<i class="fas fa-edit"></i> Edit';
    editSeriesBtn.onclick = () => editSeries(series.id);
    
    const removeSeriesBtn = document.createElement("button");
    removeSeriesBtn.className = "remove-series-btn";
    removeSeriesBtn.type = "button";
    removeSeriesBtn.innerHTML = '<i class="fas fa-times"></i> Remove';
    removeSeriesBtn.onclick = () => removeSeries(series.id);
    
    seriesActions.appendChild(addMeasurementBtn);
    seriesActions.appendChild(editSeriesBtn);
    seriesActions.appendChild(removeSeriesBtn);
    
    seriesHeader.appendChild(seriesName);
    seriesHeader.appendChild(seriesActions);
    
    // Measurements Container
    const measurementsContainer = document.createElement("div");
    measurementsContainer.className = "series-measurements";
    
    if (series.measurements.length === 0) {
      const emptyMsg = document.createElement("p");
      emptyMsg.style.color = "#999";
      emptyMsg.style.fontSize = "12px";
      emptyMsg.style.textAlign = "center";
      emptyMsg.style.padding = "8px";
      emptyMsg.textContent = "No options yet. Click \"Add Option\" to add.";
      measurementsContainer.appendChild(emptyMsg);
    } else {
      series.measurements.forEach(measurement => {
        const measurementItem = document.createElement("div");
        measurementItem.className = "measurement-item";
        measurementItem.style.cursor = "pointer";
        measurementItem.title = "Click to preview this size";
        
        // Add click handler to update preview
        measurementItem.onclick = (e) => {
          // Don't trigger if clicking the remove or edit button
          if (e.target.closest('.remove-measurement-btn') || e.target.closest('.edit-measurement-btn')) return;
        };
        
        const measurementInfo = document.createElement("div");
        measurementInfo.className = "measurement-info";
        
        const dimensions = document.createElement("span");
        dimensions.className = "measurement-dimensions";
        const widthUnit = measurement.widthUnit || 'cm';
        const heightUnit = measurement.heightUnit || 'cm';
        const unitAbbr = { 'in': 'in', 'cm': 'cm', 'mm': 'mm' };
        dimensions.textContent = `${measurement.width}${unitAbbr[widthUnit]} × ${measurement.height}${unitAbbr[heightUnit]}`;
        
        const price = document.createElement("span");
        price.className = "measurement-price";
        // Ensure price is a number before calling toFixed
        const priceValue = parseFloat(measurement.price) || 0;
        price.textContent = `₱${priceValue.toFixed(2)}`;
        
        measurementInfo.appendChild(dimensions);
        measurementInfo.appendChild(price);
        
        // Add customization fields if they exist
        if (measurement.customization && Object.keys(measurement.customization).length > 0) {
          const detailsContainer = document.createElement("div");
          detailsContainer.className = "measurement-details";
          detailsContainer.style.cssText = "margin-top: 8px; font-size: 11px; color: #666; padding-top: 8px; border-top: 1px solid #eee;";
          
          Object.entries(measurement.customization).forEach(([key, value]) => {
            if (value !== null && value !== "" && value !== false && (Array.isArray(value) ? value.length > 0 : true)) {
              const detailItem = document.createElement("div");
              detailItem.style.cssText = "margin-bottom: 4px;";
              
              // Format field name (capitalize first letter, add spaces)
              const fieldName = key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase()).trim();
              
              let displayValue = value;
              if (Array.isArray(value)) {
                displayValue = value.join(", ");
              } else if (typeof value === "boolean") {
                displayValue = value ? "Yes" : "No";
              }
              
              detailItem.innerHTML = `<strong>${fieldName}:</strong> ${displayValue}`;
              detailsContainer.appendChild(detailItem);
            }
          });
          
          if (detailsContainer.children.length > 0) {
            measurementInfo.appendChild(detailsContainer);
          }
        }
        
        const measurementActions = document.createElement("div");
        measurementActions.className = "measurement-actions";
        measurementActions.style.cssText = "display: flex; gap: 5px; align-items: center;";
        
        const editBtn = document.createElement("button");
        editBtn.className = "edit-measurement-btn";
        editBtn.type = "button";
        editBtn.innerHTML = '<i class="fas fa-edit"></i>';
        editBtn.title = "Edit measurement";
        editBtn.style.cssText = "background: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;";
        editBtn.onclick = (e) => {
          e.stopPropagation(); // Prevent measurement item click
          editMeasurement(series.id, measurement.id);
        };
        
        const removeBtn = document.createElement("button");
        removeBtn.className = "remove-measurement-btn";
        removeBtn.type = "button";
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.onclick = (e) => {
          e.stopPropagation(); // Prevent measurement item click
          removeMeasurementFromSeries(series.id, measurement.id);
        };
        
        measurementActions.appendChild(editBtn);
        measurementActions.appendChild(removeBtn);
        
        measurementItem.appendChild(measurementInfo);
        measurementItem.appendChild(measurementActions);
        measurementsContainer.appendChild(measurementItem);
      });
    }
    
    seriesItem.appendChild(seriesHeader);
    seriesItem.appendChild(measurementsContainer);
    container.appendChild(seriesItem);
  });
}

// -------------------- POPUPS (ADD / EDIT) --------------------
// Backup storage for customizationFields when Add New Product popup opens
let customizationFieldsBackup = null;

function setupProductPopups() {
  const productGrid = document.querySelector(".product-grid");

  const addPopup = document.getElementById("productPopup");
  const addBtn = document.querySelector(".add-product-btn");
  const addCloseBtn = document.getElementById("closePopup");
  const addCancelBtn = document.getElementById("addProductCancelBtn");
  const addSaveBtn = document.getElementById("addProductSaveBtn");
  const addImageInput = document.getElementById("productImageInput");
  const addImagePreview = addPopup?.querySelector(".image-preview img");
  const addNameInput = document.getElementById("productName");
  const addDescriptionInput = document.getElementById("productDescription");
  const addPriceMinInput = document.getElementById("productPriceMin");
  const addPriceMaxInput = document.getElementById("productPriceMax");
  const addCategorySelect = document.getElementById("productCategory");
  const addSubcategorySelect = document.getElementById("productSubcategory");
  const addSubcategoryGroup = document.getElementById("subcategoryGroup");
  const addCustomizationContainer = document.getElementById("customizationFields");
  const placeholderImg = "https://cdn-icons-png.flaticon.com/512/4211/4211763.png";

  const editPopup = document.getElementById("editPopup");
  const editCloseBtn = document.getElementById("closeEditPopup");
  const editCancelBtn = document.getElementById("cancelEdit");
  const editSaveBtn = document.getElementById("editSaveBtn");
  const editImageInput = document.getElementById("editProductImageInput");
  const editNameInput = document.getElementById("editProductName");
  const editPriceInput = document.getElementById("editProductPrice");
  const editCategorySelect = document.getElementById("editProductCategory");
  const editSubcategorySelect = document.getElementById("editProductSubcategory");
  const editSubcategoryGroup = document.getElementById("editSubcategoryGroup");
  const editCustomizationContainer = document.getElementById("editCustomizationFields");

  // Setup multiple image upload for edit popup
  setupMultipleImageUpload('editProductImageInput', 'editImageUploadDropzone', 'editImagePreviewGrid', 'editImageCount', 'edit');

  let productBeingEdited = null;

  // Initialize categories based on default order type (direct)
  if (addCategorySelect) {
    populateCategories("direct", addCategorySelect);
  }

  // Get orderTypeInput early to avoid reference errors
  const orderTypeInput = document.getElementById("productOrderType");

  // ---------- ADD MODAL: Category Change Handler ----------
  addCategorySelect?.addEventListener("change", function() {
    const selectedCategory = this.value;
    if (selectedCategory) {
      // Get current order type
      const currentOrderType = orderTypeInput ? orderTypeInput.value : "direct";
      // Show subcategory dropdown and populate it (with order type filtering)
      if (addSubcategoryGroup) addSubcategoryGroup.style.display = "block";
      if (addSubcategorySelect) populateSubcategories(selectedCategory, addSubcategorySelect, currentOrderType);
      // Clear customization fields until subcategory is selected
      if (addCustomizationContainer) addCustomizationContainer.innerHTML = "";
    } else {
      if (addSubcategoryGroup) addSubcategoryGroup.style.display = "none";
      if (addCustomizationContainer) addCustomizationContainer.innerHTML = "";
    }
  });

  // ---------- ADD MODAL: Subcategory Change Handler ----------
  addSubcategorySelect?.addEventListener("change", function() {
    const selectedSubcategory = this.value;
    const selectedCategory = addCategorySelect ? addCategorySelect.value : "";
    const manageGroup = document.getElementById("manageCustomizationGroup");
    
    if (selectedSubcategory) {
      // Removed automatic call to generateCustomizationFields to keep it blank as per user request
      if (addCustomizationContainer) addCustomizationContainer.innerHTML = "";
      
      // Show manage button for customization fields
      if (manageGroup) manageGroup.style.display = "block";
    } else {
      if (addCustomizationContainer) addCustomizationContainer.innerHTML = "";
      // Hide manage button
      if (manageGroup) manageGroup.style.display = "none";
    }
  });
  
  // Setup manage customization fields button (Customize Build tab)
  const manageCustomizationBtn = document.getElementById("manageCustomizationBtn");
  manageCustomizationBtn?.addEventListener("click", () => {
    const selectedCategory = addCategorySelect ? addCategorySelect.value : "";
    const selectedSubcategory = addSubcategorySelect ? addSubcategorySelect.value : "";
    if (selectedCategory && selectedSubcategory) {
      showManageCustomizationFields(selectedCategory, selectedSubcategory, {}, selectedCustomizationSeries);
    }
  });
  

  // ---------- EDIT MODAL: Category Change Handler ----------
  editCategorySelect?.addEventListener("change", function() {
    const selectedCategory = this.value;
    if (selectedCategory) {
      // Show subcategory dropdown and populate it
      if (editSubcategoryGroup) editSubcategoryGroup.style.display = "block";
      if (editSubcategorySelect) populateSubcategories(selectedCategory, editSubcategorySelect);
      // Clear customization fields until subcategory is selected
      if (editCustomizationContainer) editCustomizationContainer.innerHTML = "";
    } else {
      if (editSubcategoryGroup) editSubcategoryGroup.style.display = "none";
      if (editCustomizationContainer) editCustomizationContainer.innerHTML = "";
    }
  });

  // ---------- EDIT MODAL: Subcategory Change Handler ----------
  editSubcategorySelect?.addEventListener("change", function() {
    const selectedSubcategory = this.value;
    const selectedCategory = editCategorySelect ? editCategorySelect.value : "";
    const editManageGroup = document.getElementById("editManageCustomizationGroup");
    
    if (selectedSubcategory) {
      generateCustomizationFields(selectedSubcategory, editCustomizationContainer, "edit", selectedCategory);
      // Show manage button for Customize Build tab
      if (editManageGroup) editManageGroup.style.display = "block";
    } else {
      editCustomizationContainer.innerHTML = "";
      // Hide manage button
      if (editManageGroup) editManageGroup.style.display = "none";
    }
  });
  
  // Setup manage customization fields button (Edit - Customize Build tab)
  const editManageCustomizationBtn = document.getElementById("editManageCustomizationBtn");
  editManageCustomizationBtn?.addEventListener("click", () => {
    const selectedCategory = editCategorySelect ? editCategorySelect.value : "";
    const selectedSubcategory = editSubcategorySelect ? editSubcategorySelect.value : "";
    if (selectedCategory && selectedSubcategory) {
      // Pass current product's stored series and customization data
      const productCustomization = window.currentEditingProduct?.Customization || {};
      const storedSeries = window.currentEditingProduct?.SelectedCustomizationSeries;
      showManageCustomizationFields(selectedCategory, selectedSubcategory, productCustomization, storedSeries);
    }
  });
  
  // Setup manage customization fields button (Edit - Standard tab)
  const editStandardManageCustomizationBtn = document.getElementById("editStandardManageCustomizationBtn");
  editStandardManageCustomizationBtn?.addEventListener("click", () => {
    const selectedCategory = editCategorySelect ? editCategorySelect.value : "";
    const selectedSubcategory = editSubcategorySelect ? editSubcategorySelect.value : "";
    if (selectedCategory && selectedSubcategory) {
      // Pass current product's stored series and customization data
      const productCustomization = window.currentEditingProduct?.Customization || {};
      const storedSeries = window.currentEditingProduct?.SelectedCustomizationSeries;
      showManageCustomizationFields(selectedCategory, selectedSubcategory, productCustomization, storedSeries);
    }
  });

  // ---------- ADD PRODUCT ----------
  if (addBtn && addPopup) {
    addBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      
      // Create backup of customizationFields before opening popup
      // This allows us to restore if popup is closed without saving
      customizationFieldsBackup = JSON.parse(JSON.stringify(customizationFields));
      console.log('Backup created for customizationFields before opening Add New Product popup');
      
      addPopup.style.display = "flex";
      
      // Full reset when opening
      if (addNameInput) addNameInput.value = "";
      if (addPriceMinInput) addPriceMinInput.value = "";
      if (addPriceMaxInput) addPriceMaxInput.value = "";
      clearImages('add');
      setOrderType("direct");
      if (addCategorySelect) addCategorySelect.value = "";
      if (addSubcategorySelect) addSubcategorySelect.value = "";
      if (addSubcategoryGroup) addSubcategoryGroup.style.display = "none";
      if (addCustomizationContainer) addCustomizationContainer.innerHTML = "";
      
      const manageGroup = document.getElementById("manageCustomizationGroup");
      const seriesGroup = document.getElementById("seriesGroup");
      const seriesSelect = document.getElementById("productSeries");
      if (manageGroup) manageGroup.style.display = "none";
      if (seriesGroup) seriesGroup.style.display = "none";
      if (seriesSelect) seriesSelect.value = "";

      // Reset standard series
      standardSeries = [];
      renderStandardSeries();
      
      // Reset tag prices
      tagPrices = {};
      tagImages = {};
      visualConfigs = {};
      standardFieldOptions = {};
      const previewContainer = document.getElementById('konvaPreviewContainer');
      if (previewContainer) previewContainer.innerHTML = '';
      
      // Setup add series button when popup opens (in case button wasn't found initially)
      const addSeriesBtn = document.getElementById("addSeriesBtn");
      if (addSeriesBtn) {
        // Remove any existing listeners
        const newBtn = addSeriesBtn.cloneNode(true);
        addSeriesBtn.parentNode.replaceChild(newBtn, addSeriesBtn);
        // Attach new listener
        document.getElementById("addSeriesBtn").addEventListener("click", addSeries);
      }
    });
  } else {
    console.warn('Add product button or popup not found');
  }
  
  // Setup add series button on initial load (if popup is already in DOM)
  const addSeriesBtn = document.getElementById("addSeriesBtn");
  if (addSeriesBtn) {
    addSeriesBtn.addEventListener("click", addSeries);
  }

  // Setup tab switching
  const customizeTab = document.getElementById("customizeTab");
  const customizeTabContent = document.getElementById("customizeTabContent");

  function switchTab(tabName) {
    // Remove active class from all tabs and contents
    [customizeTab].forEach(tab => tab?.classList.remove("active"));
    [customizeTabContent].forEach(content => content?.classList.remove("active"));

    // Add active class to selected tab and content
    if (tabName === "customize") {
      customizeTab?.classList.add("active");
      customizeTabContent?.classList.add("active");
    }
  }

  customizeTab?.addEventListener("click", () => switchTab("customize"));

  // Setup Order Type Buttons
  const directOrderBtn = document.getElementById("directOrderBtn");
  const siteAssessmentBtn = document.getElementById("siteAssessmentBtn");
  // orderTypeInput is already declared earlier in the function

  function setOrderType(orderType) {
    // Remove active class from both buttons
    [directOrderBtn, siteAssessmentBtn].forEach(btn => btn?.classList.remove("active"));
    
    // Add active class to selected button
    if (orderType === "direct") {
      directOrderBtn?.classList.add("active");
      if (orderTypeInput) orderTypeInput.value = "direct";
    } else if (orderType === "site-assessment") {
      siteAssessmentBtn?.classList.add("active");
      if (orderTypeInput) orderTypeInput.value = "site-assessment";
    }
    
    // Update categories based on order type
    const categorySelect = document.getElementById("productCategory");
    if (categorySelect) {
      populateCategories(orderType, categorySelect);
      
      // If a category was already selected, update subcategories based on new order type
      const selectedCategory = categorySelect.value;
      if (selectedCategory) {
        const subcategorySelect = document.getElementById("productSubcategory");
        const subcategoryGroup = document.getElementById("subcategoryGroup");
        if (subcategorySelect && subcategoryGroup && subcategoryGroup.style.display !== "none") {
          // Double-check subcategorySelect is still valid before calling
          if (subcategorySelect && subcategorySelect.nodeType === 1) {
            populateSubcategories(selectedCategory, subcategorySelect, orderType);
          }
        }
      }
    }
  }

  directOrderBtn?.addEventListener("click", () => setOrderType("direct"));
  siteAssessmentBtn?.addEventListener("click", () => setOrderType("site-assessment"));

  [addCloseBtn, addCancelBtn].forEach(btn =>
    btn?.addEventListener("click", () => {
      if (addPopup) addPopup.style.display = "none";
      if (addNameInput) addNameInput.value = "";
      if (addPriceMinInput) addPriceMinInput.value = "";
      if (addPriceMaxInput) addPriceMaxInput.value = "";
      clearImages('add');
      // Reset order type to direct
      setOrderType("direct");
      // Reset category and subcategory
      if (addCategorySelect) addCategorySelect.value = "";
      if (addSubcategorySelect) addSubcategorySelect.value = "";
      if (addSubcategoryGroup) addSubcategoryGroup.style.display = "none";
      // Clear selected customization series (forgotten when popup is cancelled)
      selectedCustomizationSeries = null;
      if (addCustomizationContainer) addCustomizationContainer.innerHTML = "";
      
      const manageGroup = document.getElementById("manageCustomizationGroup");
      const seriesGroup = document.getElementById("seriesGroup");
      const seriesSelect = document.getElementById("productSeries");
      if (manageGroup) manageGroup.style.display = "none";
      if (seriesGroup) seriesGroup.style.display = "none";
      if (seriesSelect) seriesSelect.value = "";
      
      // Reset standard series
      standardSeries = [];
      renderStandardSeries();
      // Reset tag prices
      tagPrices = {};
      tagImages = {};
      // Reset visual configs
      visualConfigs = {};
      // Reset standard field options
      standardFieldOptions = {};
      // Reset any previews
      const previewContainer = document.getElementById('konvaPreviewContainer');
      if (previewContainer) previewContainer.innerHTML = '';
      
      // Restore customizationFields from backup if popup is closed without saving
      // This reverts any changes made in "Manage Customization Fields" that weren't saved in "Add New Product"
      // The saved series will remain for "Manage Customization Fields" but won't auto-apply in "Add New Product" next time
      if (customizationFieldsBackup !== null) {
        // Get the current category and subcategory
        const currentCategory = addCategorySelect ? addCategorySelect.value : "";
        const currentSubcategory = addSubcategorySelect ? addSubcategorySelect.value : "";
        
        // Build field key
        let fieldKey = null;
        if (currentCategory && currentSubcategory) {
          if (currentCategory === "Windows") {
            fieldKey = `Windows_${currentSubcategory}`;
          } else if (currentCategory === "Doors") {
            fieldKey = `Doors_${currentSubcategory}`;
          } else if (currentCategory === "Glass Partitions & Enclosures") {
            fieldKey = `Partitions_${currentSubcategory}`;
          } else if (currentCategory === "Mirrors & Specialty Glass") {
            fieldKey = `Specialty_${currentSubcategory}`;
          } else if (currentCategory === "Commercial & Exterior") {
            fieldKey = `Commercial_${currentSubcategory}`;
          } else {
            fieldKey = currentSubcategory;
          }
        }
        
        // Preserve the saved series from backup (for "Manage Customization Fields")
        const savedSeriesKey = fieldKey ? `${fieldKey}_selectedSeries` : null;
        const preservedSeries = savedSeriesKey && customizationFieldsBackup[savedSeriesKey] ? customizationFieldsBackup[savedSeriesKey] : null;
        
        // Restore all customizationFields to the state before opening the popup
        // This ensures no selections made in Add New Product are saved if popup is closed without saving
        customizationFields = JSON.parse(JSON.stringify(customizationFieldsBackup));
        
        // Update localStorage to reflect the restored state
        try {
          localStorage.setItem(CUSTOMIZATION_FIELDS_STORAGE_KEY, JSON.stringify(customizationFields));
        } catch (e) {
          console.warn('localStorage access blocked by browser (Tracking Prevention):', e.message);
        }
        // Clear the backup
        customizationFieldsBackup = null;
        console.log('Restored customizationFields from backup because Add New Product popup was closed without saving. No selections were saved.');
      }
    })
  );

  // Setup multiple image upload for add popup
  setupMultipleImageUpload('productImageInput', 'imageUploadDropzone', 'imagePreviewGrid', 'imageCount', 'add');

  // Flag to prevent duplicate submissions
  let isSubmitting = false;

  addSaveBtn?.addEventListener("click", async (e) => {
    e.preventDefault(); // Prevent default button behavior
    
    // Prevent duplicate submissions
    if (isSubmitting) {
      return;
    }

    let name = addNameInput.value.trim();
    let description = addDescriptionInput ? addDescriptionInput.value.trim() : '';
    let categoryEl = document.getElementById("productCategory");
    let subcategoryEl = document.getElementById("productSubcategory");
    let orderTypeEl = document.getElementById("productOrderType");
    let priceMinEl = document.getElementById("productPriceMin");
    let priceMaxEl = document.getElementById("productPriceMax");
    let category = categoryEl ? categoryEl.value : '';
    let subcategory = subcategoryEl ? subcategoryEl.value : '';
    let orderType = orderTypeEl ? orderTypeEl.value : '';
    let priceMin = priceMinEl ? parseFloat(priceMinEl.value) : 0;
    let priceMax = priceMaxEl ? parseFloat(priceMaxEl.value) : 0;

    if (!name || !category || !orderType) {
      showToast("Please complete all required fields.", 'error');
      return;
    }

    // Check for duplicate product name
    try {
      const checkResponse = await fetch(base_url + "ProductCon/check_product_name?name=" + encodeURIComponent(name));
      const checkData = await checkResponse.json();
      if (checkData.exists) {
        showToast("A product with this name already exists. Product names must be unique.", 'error');
        return;
      }
    } catch (error) {
      console.error('Error checking product name:', error);
      // Continue with submission if check fails (backend will catch it)
    }

    if (!priceMin && !priceMax) {
      showToast("Please enter at least a minimum or maximum price.", 'error');
      return;
    }

    if (priceMin && priceMax && priceMin > priceMax) {
      showToast("Minimum price cannot be greater than maximum price.", 'error');
      return;
    }

    // Validate image count
    if (!validateImageCount('add'))
      return;

    // Set submitting flag and disable button
    isSubmitting = true;
    addSaveBtn.disabled = true;
    addSaveBtn.textContent = 'Saving...';

    // Collect data from BOTH tabs:
    // 1. Customize Build tab - customization fields (tags, checkboxes, numbers, etc.)
    const customizationData = collectCustomizationData("");
    
    // 2. Standard tab - standard series with measurements
    // (standardSeries is already stored in global variable)

    let formData = new FormData();
    formData.append("name", name);
    formData.append("description", description);
    formData.append("category", category);
    if (subcategory) formData.append("subcategory", subcategory);
    formData.append("orderType", orderType);
    formData.append("priceMin", priceMin);
    formData.append("priceMax", priceMax);
    
    // Save Customize Build data (customization fields)
    formData.append("customization", JSON.stringify(customizationData));
    // Save selected customization series
    if (selectedCustomizationSeries) {
      formData.append("selectedCustomizationSeries", selectedCustomizationSeries);
    }
    // Save tag prices (from Customize Build)
    formData.append("tagPrices", JSON.stringify(tagPrices));
    
    // Save tag visual configs for Konva.js 2D preview
    console.log("[Product Save] ========================================");
    console.log("[Product Save] tagPrices being saved:", tagPrices);
    console.log("[Product Save] tagVisualConfigs being saved:", tagVisualConfigs);
    console.log("[Product Save] tagVisualConfigs JSON:", JSON.stringify(tagVisualConfigs));
    
    // Check if frameColor has visual config
    if (tagVisualConfigs['frameColor']) {
        console.log("[Product Save] ✓ frameColor visual configs:", tagVisualConfigs['frameColor']);
    } else {
        console.log("[Product Save] ✗ No frameColor visual configs found!");
    }
    
    // Check if frameColor has prices (required for saving)
    if (tagPrices['frameColor']) {
        console.log("[Product Save] ✓ frameColor prices:", tagPrices['frameColor']);
    } else {
        console.log("[Product Save] ✗ No frameColor prices found - visual configs WON'T be saved!");
    }
    console.log("[Product Save] ========================================");
    
    formData.append("tagVisualConfigs", JSON.stringify(tagVisualConfigs));
    
    // Save tag images (from Customize Build) - append File objects
    Object.keys(tagImages).forEach(fieldId => {
      Object.keys(tagImages[fieldId]).forEach(tagName => {
        const imageFile = tagImages[fieldId][tagName];
        if (imageFile instanceof File) {
          formData.append(`tagImages[${fieldId}][${tagName}]`, imageFile);
        }
      });
    });
    
    // Save Standard data (series with measurements)
    if (standardSeries.length > 0) {
      formData.append("standardSeries", JSON.stringify(standardSeries));
    }
    
    // Append all images
    uploadedImages.add.forEach((file, index) => {
      formData.append(`productImages[]`, file);
    });

    fetch(base_url + "ProductCon/add_product", { method: "POST", body: formData })
      .then(async res => {
        // Check if response is JSON
        const contentType = res.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
          // Response is not JSON, likely an HTML error page
          const text = await res.text();
          console.error('Server returned non-JSON response:', text.substring(0, 500));
          throw new Error('Server returned an error page instead of JSON. Check server logs for details.');
        }
        
        if (!res.ok) {
          // Try to parse as JSON even if not OK
          try {
            const errorData = await res.json();
            throw new Error(errorData.msg || errorData.message || 'Network response was not ok');
          } catch (e) {
            throw new Error('Network response was not ok');
          }
        }
        return res.json();
      })
      .then(data => {
        if (data.status === "success") {
          // Product was saved successfully, discard the backup
          customizationFieldsBackup = null;
          // Clear selected series after successful save
          selectedCustomizationSeries = null;
          showToast("Product saved successfully!", 'success');
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          isSubmitting = false;
          addSaveBtn.disabled = false;
          addSaveBtn.textContent = 'Save';
          showToast(data.msg || "Error saving product.", 'error');
        }
      })
      .catch(error => {
        console.error('Error saving product:', error);
        isSubmitting = false;
        addSaveBtn.disabled = false;
        addSaveBtn.textContent = 'Save';
        showToast("Error saving product. Please try again.", 'error');
      });
  });
}

// ---------- POPULATE EDIT FORM ----------
function populateEditForm(product) {
  // Store product data globally for access by manage customization button
  window.currentEditingProduct = product;

  console.log("=== EDIT PRODUCT LOADED ===");
  console.log("Product:", product);
  console.log("SelectedCustomizationSeries:", product?.SelectedCustomizationSeries);
  console.log("Customization:", product?.Customization);
  
  // Clear previous data
  clearImages('edit');
  tagPrices = {};
  tagImages = {};
  standardSeries = [];
  
  // Populate basic fields
  const editNameInput = document.getElementById("editProductName");
  const editDescriptionInput = document.getElementById("editProductDescription");
  const editPriceMinInput = document.getElementById("editProductPriceMin");
  const editPriceMaxInput = document.getElementById("editProductPriceMax");
  const editCategoryEl = document.getElementById("editProductCategory");
  const editSubcategoryEl = document.getElementById("editProductSubcategory");
  const editOrderTypeInput = document.getElementById("editProductOrderType");
  const editDirectOrderBtn = document.getElementById("editDirectOrderBtn");
  const editSiteAssessmentBtn = document.getElementById("editSiteAssessmentBtn");
  const editCustomizeTab = document.getElementById("editCustomizeTab");
  const editStandardTab = document.getElementById("editStandardTab");
  const editCustomizeTabContent = document.getElementById("editCustomizeTabContent");
  const editStandardTabContent = document.getElementById("editStandardTabContent");
  const editSubcategoryGroup = document.getElementById("editSubcategoryGroup");
  const editCustomizationContainer = document.getElementById("editCustomizationFields");
  const editManageCustomizationGroup = document.getElementById("editManageCustomizationGroup");
  
  if (editNameInput) editNameInput.value = product.ProductName || '';
  if (editDescriptionInput) editDescriptionInput.value = product.Description || '';
  
  // Populate price range
  if (editPriceMinInput) editPriceMinInput.value = product.PriceMin || '';
  if (editPriceMaxInput) editPriceMaxInput.value = product.PriceMax || '';
  
  // Set order type
  const orderType = product.OrderType || 'direct';
  if (editOrderTypeInput) editOrderTypeInput.value = orderType;
  if (editDirectOrderBtn && editSiteAssessmentBtn) {
    [editDirectOrderBtn, editSiteAssessmentBtn].forEach(btn => btn.classList.remove("active"));
    if (orderType === "direct") {
      editDirectOrderBtn.classList.add("active");
    } else if (orderType === "site-assessment") {
      editSiteAssessmentBtn.classList.add("active");
    }
  }
  
  // Populate categories based on order type (read-only)
  if (editCategoryEl) {
    populateCategories(orderType, editCategoryEl);
    if (product.Category) {
      const categoryValue = String(product.Category).trim();
      
          // Use requestAnimationFrame to ensure DOM is updated after populateCategories
          requestAnimationFrame(() => {
            // Use setTimeout as additional safety
            setTimeout(() => {
              if (editCategoryEl) {
                // IMPORTANT: Temporarily enable the select to set the value
                // Some browsers don't allow setting value on disabled selects
                const wasDisabled = editCategoryEl.disabled;
                editCategoryEl.disabled = false;
                
                // Remove selected attribute from placeholder option FIRST
                const placeholderOption = editCategoryEl.querySelector('option[value=""]');
                if (placeholderOption) {
                  placeholderOption.removeAttribute('selected');
                  placeholderOption.selected = false;
                }
                
                // Log available options for debugging
                const availableOptions = Array.from(editCategoryEl.options).map(o => ({
                  value: o.value,
                  text: o.text,
                  index: o.index
                }));
                console.log('[Edit Form] Setting category:', categoryValue, 'Available options:', availableOptions);
                
                // Try to set the value directly
                editCategoryEl.value = categoryValue;
                
                // Verify it was set
                if (editCategoryEl.value !== categoryValue) {
                  // If direct value setting failed, try manual selection
                  const options = editCategoryEl.options;
                  let foundIndex = -1;
                  
                  for (let i = 0; i < options.length; i++) {
                    // Skip placeholder
                    if (options[i].value === "" || options[i].disabled) continue;
                    
                    const optValue = String(options[i].value).trim();
                    const optText = String(options[i].text).trim();
                    
                    if (optValue === categoryValue || 
                        optText === categoryValue ||
                        optValue.toLowerCase() === categoryValue.toLowerCase() ||
                        optText.toLowerCase() === categoryValue.toLowerCase()) {
                      foundIndex = i;
                      break;
                    }
                  }
                  
                  if (foundIndex >= 0) {
                    editCategoryEl.selectedIndex = foundIndex;
                    console.log('[Edit Form] Category set via selectedIndex:', foundIndex);
                  } else {
                    console.warn('[Edit Form] Category not found in options:', categoryValue);
                  }
                } else {
                  console.log('[Edit Form] Category set successfully via value property');
                }
                
                // Force a re-render by triggering change event (for some browsers)
                editCategoryEl.dispatchEvent(new Event('change', { bubbles: true }));
                
                // Re-disable the select (read-only)
                editCategoryEl.disabled = wasDisabled;
          
          // Populate subcategories after category is set
          // Use the actual category value that was set (or the product category as fallback)
          const actualCategory = editCategoryEl.value || categoryValue || product.Category;
          
          if (product.Subcategory && editSubcategoryEl && actualCategory) {
            // Populate subcategories based on the actual category
            populateSubcategories(actualCategory, editSubcategoryEl, orderType);
            
            // Wait for subcategories to be populated before setting value
            setTimeout(() => {
              if (editSubcategoryEl && editSubcategoryEl.nodeType === 1) {
                try {
                  const subcategoryValue = String(product.Subcategory).trim();
                  
                  // IMPORTANT: Temporarily enable the select to set the value
                  const wasSubDisabled = editSubcategoryEl.disabled;
                  editSubcategoryEl.disabled = false;
                  
                  // Remove selected attribute from placeholder option
                  const subPlaceholderOption = editSubcategoryEl.querySelector('option[value=""]');
                  if (subPlaceholderOption) {
                    subPlaceholderOption.removeAttribute('selected');
                    subPlaceholderOption.selected = false;
                  }
                  
                  // Try to set the value directly
                  editSubcategoryEl.value = subcategoryValue;
                  
                  // If value didn't set, try to find and select the option manually
                  if (editSubcategoryEl.value !== subcategoryValue) {
                    const options = editSubcategoryEl.options;
                    let found = false;
                    for (let i = 0; i < options.length; i++) {
                      // Skip placeholder option
                      if (options[i].value === "" || options[i].disabled) continue;
                      
                      const optValue = String(options[i].value).trim();
                      const optText = String(options[i].text).trim();
                      
                      if (optValue === subcategoryValue || 
                          optText === subcategoryValue ||
                          optValue.toLowerCase() === subcategoryValue.toLowerCase() ||
                          optText.toLowerCase() === subcategoryValue.toLowerCase()) {
                        editSubcategoryEl.selectedIndex = i;
                        found = true;
                        break;
                      }
                    }
                    
                    // If still not found, log for debugging
                    if (!found) {
                      console.warn('Subcategory not found:', subcategoryValue, 'Available options:', 
                        Array.from(editSubcategoryEl.options).map(o => ({value: o.value, text: o.text})));
                    }
                  }
                  
                  // Verify the value is set correctly
                  const finalSubValue = editSubcategoryEl.value || 
                    (editSubcategoryEl.selectedIndex > 0 ? editSubcategoryEl.options[editSubcategoryEl.selectedIndex].value : "");
                  
                  if (finalSubValue === subcategoryValue || editSubcategoryEl.selectedIndex > 0) {
                    // Re-disable the select (read-only)
                    editSubcategoryEl.disabled = wasSubDisabled;
                    
                    // Show subcategory group
                    if (editSubcategoryGroup) editSubcategoryGroup.style.display = "block";
                    
                    // Show Series dropdown for Windows subcategories
                    const editSeriesGroup = document.getElementById("editSeriesGroup");
                    const editSeriesSelect = document.getElementById("editProductSeries");
                    if (actualCategory === "Windows" && editSeriesGroup && editSeriesSelect) {
                      editSeriesGroup.style.display = "block";
                      // Populate series options based on selected subcategory
                      populateSeriesOptions(product.Subcategory, editSeriesSelect);
                      // Try to get series from customization fields if available
                      // For now, set to None - can be enhanced later to read from saved data
                      editSeriesSelect.value = "";
                    } else if (editSeriesGroup) {
                      editSeriesGroup.style.display = "none";
                    }
                    
                    // Generate customization fields
                    if (editCustomizationContainer) {
                      generateCustomizationFields(product.Subcategory, editCustomizationContainer, "edit", actualCategory);
                    }
                    
                    // Show manage customization button for Customize Build tab
                    if (editManageCustomizationGroup) editManageCustomizationGroup.style.display = "block";
                  } else {
                    console.warn('Subcategory value could not be set:', product.Subcategory, 'Category:', actualCategory);
                  }
                } catch (error) {
                  console.error('Error setting subcategory value:', error, { 
                    subcategory: product.Subcategory, 
                    category: actualCategory 
                  });
                }
              }
            }, 100); // Increased delay to ensure subcategories are fully populated
          } else if (product.Subcategory && !actualCategory) {
            console.warn('Cannot populate subcategories: category is missing', { 
              productCategory: product.Category, 
              categoryValue: categoryValue 
            });
          }
          }
        }, 50); // Delay to ensure categories are populated
      });
    }
  }
  
  // Populate images
  if (product.ImageUrl && Array.isArray(product.ImageUrl) && product.ImageUrl.length > 0) {
    const editPreviewGrid = document.getElementById("editImagePreviewGrid");
    const editImageCount = document.getElementById("editImageCount");
    
    product.ImageUrl.forEach((imageUrl, index) => {
      // Create image preview from URL
      const imageUrlFull = base_url + "uploads/products/" + imageUrl;
      const previewItem = createPreviewItemFromUrl(imageUrlFull, index, 'edit');
      if (editPreviewGrid) editPreviewGrid.appendChild(previewItem);
    });
    
    if (editImageCount) {
      updateImageCount(editImageCount, product.ImageUrl.length);
    }
  }
  
  // IMPORTANT: Load ALL tag data BEFORE rendering customization fields
  // This ensures palette icons show correctly for tags with visual configs
  
  // Load tag prices
  if (product.tagPrices) {
    tagPrices = product.tagPrices;
    console.log("[Edit Product] Loaded tagPrices:", tagPrices);
  } else {
    console.log("[Edit Product] No tagPrices found in product data");
  }
  
  // Load tag images
  if (product.tagImages) {
    tagImages = product.tagImages;
    console.log("[Edit Product] Loaded tagImages:", Object.keys(tagImages));
  }
  
  // Load tag visual configs (MUST be loaded BEFORE rendering tags)
  // IMPORTANT: Ensure tagVisualConfigs is always an OBJECT {}, never an ARRAY []
  // PHP json_encode returns [] for empty arrays, but we need {}
  if (product.tagVisualConfigs && typeof product.tagVisualConfigs === 'object' && !Array.isArray(product.tagVisualConfigs)) {
    tagVisualConfigs = product.tagVisualConfigs;
    console.log("[Edit Product] Loaded tagVisualConfigs:", tagVisualConfigs);
    // Log each field with visual configs
    Object.keys(tagVisualConfigs).forEach(fieldId => {
      const tagsWithConfig = Object.keys(tagVisualConfigs[fieldId]);
      console.log(`[Edit Product] Field "${fieldId}" has visual configs for: ${tagsWithConfig.join(', ')}`);
    });
  } else {
    // Reset to empty object if not valid or is an array
    tagVisualConfigs = {};
    console.log("[Edit Product] No tagVisualConfigs found or invalid format, initialized as empty object");
  }
  
  // IMPORTANT: Populate customization data BEFORE generating fields
  // This ensures hidden inputs are set before renderTags reads them
  const customizationDataToPopulate = {};
  if (product.Customization) {
    Object.keys(product.Customization).forEach(fieldId => {
      customizationDataToPopulate[fieldId] = product.Customization[fieldId];
    });
  }
  
  // NOW render customization fields (after all tag data is loaded)
  if (product.Subcategory && editCustomizationContainer) {
    console.log("[Edit Product] Generating customization fields with loaded tag data...");
    generateCustomizationFields(product.Subcategory, editCustomizationContainer, "edit", product.Category);
    
    // After fields are generated, populate the selected values
    // Use setTimeout to ensure DOM is ready
    setTimeout(() => {
      // Handle Customization field - it might be a JSON string or an object
      let customizationData = product.Customization;
      if (typeof customizationData === 'string') {
        try {
          customizationData = JSON.parse(customizationData);
        } catch (e) {
          console.error('[Edit Product] Failed to parse Customization JSON:', e);
          customizationData = null;
        }
      }
      
      if (customizationData && typeof customizationData === 'object') {
        console.log('[Edit Product] Populating customization fields with data:', customizationData);
        
        Object.keys(customizationData).forEach(fieldId => {
          const value = customizationData[fieldId];
          const fieldInput = document.getElementById(`edit${fieldId}`);
          const fieldContainer = document.getElementById(`edit${fieldId}Container`);
          
          console.log(`[Edit Product] Processing field "${fieldId}":`, value, 'Input exists:', !!fieldInput, 'Container exists:', !!fieldContainer);
          
          if (fieldInput) {
            if (Array.isArray(value)) {
              // Tags field - set hidden input value and re-render tags to show selection
              fieldInput.value = JSON.stringify(value);
              console.log(`[Edit Product] Set tag field "${fieldId}" to:`, value);
              
              if (fieldContainer) {
                // Get available options from container dataset (set during field generation)
                const availableOptions = JSON.parse(fieldContainer.dataset.availableOptions || "[]");
                console.log(`[Edit Product] Re-rendering tags for "${fieldId}" with selected values:`, value);
                // Re-render tags with the selected values - this will read from hidden input
                renderTags(fieldContainer, availableOptions, "edit", fieldId);
              } else {
                console.warn(`[Edit Product] Tag container not found for field "${fieldId}"`);
              }
            } else if (typeof value === 'boolean') {
              // Checkbox field
              fieldInput.checked = value;
              console.log(`[Edit Product] Set checkbox field "${fieldId}" to:`, value);
            } else {
              // Number or other field
              fieldInput.value = value;
              console.log(`[Edit Product] Set field "${fieldId}" to:`, value);
            }
          } else {
            console.warn(`[Edit Product] Field input not found for "${fieldId}"`);
          }
        });
      } else {
        console.log('[Edit Product] No customization data to populate');
      }
    }, 200); // Increased delay to ensure all DOM elements are ready
  }
  
  // Populate standard series
  if (product.standardSeries && Array.isArray(product.standardSeries)) {
    standardSeries = product.standardSeries.map(series => ({
      id: series.id || Date.now() + Math.random(),
      name: series.name,
      measurements: (series.measurements || []).map(m => ({
        id: m.id || Date.now() + Math.random(),
        width: m.width,
        height: m.height,
        price: m.price,
        widthUnit: m.widthUnit || 'cm',
        heightUnit: m.heightUnit || 'cm',
        customization: m.customization || {} // Include customization data
      }))
    }));
    
    // Render standard series (function will detect edit popup is open)
    renderStandardSeries();
  }
  
  // Set active tab based on which has data, but allow switching between tabs
  if (editCustomizeTab && editStandardTab && editCustomizeTabContent && editStandardTabContent) {
    // Check if Standard tab has data
    const hasStandardData = standardSeries.length > 0;
    // Check if Customize Build has data
    const hasCustomizeData = product.Customization && Object.keys(product.Customization).length > 0;
    
    if (hasStandardData && !hasCustomizeData) {
      // Switch to standard tab if it has data and customization is empty
      editStandardTab.classList.add("active");
      editCustomizeTab.classList.remove("active");
      editStandardTabContent.classList.add("active");
      editCustomizeTabContent.classList.remove("active");
    } else {
      // Default to customize tab, but Standard tab is still clickable
      editCustomizeTab.classList.add("active");
      editStandardTab.classList.remove("active");
      editCustomizeTabContent.classList.add("active");
      editStandardTabContent.classList.remove("active");
    }
    
    // Ensure both tabs are always clickable (remove any disabled state)
    editStandardTab.style.pointerEvents = "auto";
    editStandardTab.style.opacity = "1";
    editCustomizeTab.style.pointerEvents = "auto";
    editCustomizeTab.style.opacity = "1";
  }
}

/**
 * Create preview item from image URL (for existing images)
 */
function createPreviewItemFromUrl(imageSrc, index, mode) {
  const item = document.createElement("div");
  item.className = "image-preview-item";
  item.dataset.index = index;
  item.dataset.imageUrl = imageSrc;

  const img = document.createElement("img");
  img.src = imageSrc;
  img.alt = `Preview ${index + 1}`;

  const numberBadge = document.createElement("div");
  numberBadge.className = "image-number";
  numberBadge.textContent = `#${index + 1}`;

  const removeBtn = document.createElement("button");
  removeBtn.className = "remove-image-btn";
  removeBtn.type = "button";
  removeBtn.innerHTML = '<i class="fas fa-times"></i>';
  removeBtn.addEventListener("click", () => {
    // Remove from preview
    item.remove();
    const countIndicatorId = mode === 'add' ? 'imageCount' : 'editImageCount';
    const countIndicator = document.getElementById(countIndicatorId);
    const previewGrid = mode === 'add' ? document.getElementById('imagePreviewGrid') : document.getElementById('editImagePreviewGrid');
    if (previewGrid && countIndicator) {
      const remainingImages = previewGrid.querySelectorAll('.image-preview-item').length - 1;
      updateImageCount(countIndicator, remainingImages);
    }
  });

  item.appendChild(img);
  item.appendChild(numberBadge);
  item.appendChild(removeBtn);

  return item;
}

/**
 * Render standard series for edit popup
 */
function renderStandardSeriesForEdit(container) {
  if (!container) return;
  
  container.innerHTML = "";
  
  if (standardSeries.length === 0) {
    const emptyMsg = document.createElement("p");
    emptyMsg.style.color = "#999";
    emptyMsg.style.fontSize = "13px";
    emptyMsg.style.textAlign = "center";
    emptyMsg.style.padding = "10px";
    emptyMsg.textContent = "No series added yet. Click \"Add Series\" to start.";
    container.appendChild(emptyMsg);
    return;
  }
  
  standardSeries.forEach(series => {
    const seriesItem = document.createElement("div");
    seriesItem.className = "series-item";
    
    // Series Header
    const seriesHeader = document.createElement("div");
    seriesHeader.className = "series-header";
    
    const seriesName = document.createElement("span");
    seriesName.className = "series-name";
    seriesName.textContent = series.name;
    
    const seriesActions = document.createElement("div");
    seriesActions.className = "series-actions";
    
    const addMeasurementBtn = document.createElement("button");
    addMeasurementBtn.className = "add-measurement-btn";
    addMeasurementBtn.type = "button";
    addMeasurementBtn.innerHTML = '<i class="fas fa-plus"></i> Add Option';
    addMeasurementBtn.onclick = () => addDirectOrderOptionToSeries(series.id);
    
    const editSeriesBtn = document.createElement("button");
    editSeriesBtn.className = "edit-series-btn";
    editSeriesBtn.type = "button";
    editSeriesBtn.innerHTML = '<i class="fas fa-edit"></i> Edit';
    editSeriesBtn.onclick = () => editSeries(series.id);
    
    const removeSeriesBtn = document.createElement("button");
    removeSeriesBtn.className = "remove-series-btn";
    removeSeriesBtn.type = "button";
    removeSeriesBtn.innerHTML = '<i class="fas fa-times"></i> Remove';
    removeSeriesBtn.onclick = () => removeSeries(series.id);
    
    seriesActions.appendChild(addMeasurementBtn);
    seriesActions.appendChild(editSeriesBtn);
    seriesActions.appendChild(removeSeriesBtn);
    
    seriesHeader.appendChild(seriesName);
    seriesHeader.appendChild(seriesActions);
    
    // Measurements Container
    const measurementsContainer = document.createElement("div");
    measurementsContainer.className = "series-measurements";
    
    if (series.measurements.length === 0) {
      const emptyMsg = document.createElement("p");
      emptyMsg.style.color = "#999";
      emptyMsg.style.fontSize = "12px";
      emptyMsg.style.textAlign = "center";
      emptyMsg.style.padding = "8px";
      emptyMsg.textContent = "No options yet. Click \"Add Option\" to add.";
      measurementsContainer.appendChild(emptyMsg);
    } else {
      series.measurements.forEach(measurement => {
        const measurementItem = document.createElement("div");
        measurementItem.className = "measurement-item";
        
        const measurementInfo = document.createElement("div");
        measurementInfo.className = "measurement-info";
        
        const dimensions = document.createElement("span");
        dimensions.className = "measurement-dimensions";
        const widthUnit = measurement.widthUnit || 'cm';
        const heightUnit = measurement.heightUnit || 'cm';
        const unitAbbr = { 'in': 'in', 'cm': 'cm', 'mm': 'mm' };
        dimensions.textContent = `${measurement.width}${unitAbbr[widthUnit]} × ${measurement.height}${unitAbbr[heightUnit]}`;
        
        const price = document.createElement("span");
        price.className = "measurement-price";
        price.textContent = `₱${parseFloat(measurement.price).toFixed(2)}`;
        
        measurementInfo.appendChild(dimensions);
        measurementInfo.appendChild(price);
        
        // Add customization fields if they exist
        if (measurement.customization && Object.keys(measurement.customization).length > 0) {
          const detailsContainer = document.createElement("div");
          detailsContainer.className = "measurement-details";
          detailsContainer.style.cssText = "margin-top: 8px; font-size: 11px; color: #666; padding-top: 8px; border-top: 1px solid #eee;";
          
          Object.entries(measurement.customization).forEach(([key, value]) => {
            if (value !== null && value !== "" && value !== false && (Array.isArray(value) ? value.length > 0 : true)) {
              const detailItem = document.createElement("div");
              detailItem.style.cssText = "margin-bottom: 4px;";
              
              // Format field name (capitalize first letter, add spaces)
              const fieldName = key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase()).trim();
              
              let displayValue = value;
              if (Array.isArray(value)) {
                displayValue = value.join(", ");
              } else if (typeof value === "boolean") {
                displayValue = value ? "Yes" : "No";
              }
              
              detailItem.innerHTML = `<strong>${fieldName}:</strong> ${displayValue}`;
              detailsContainer.appendChild(detailItem);
            }
          });
          
          if (detailsContainer.children.length > 0) {
            measurementInfo.appendChild(detailsContainer);
          }
        }
        
        const measurementActions = document.createElement("div");
        measurementActions.className = "measurement-actions";
        measurementActions.style.cssText = "display: flex; gap: 5px; align-items: center;";
        
        const editBtn = document.createElement("button");
        editBtn.className = "edit-measurement-btn";
        editBtn.type = "button";
        editBtn.innerHTML = '<i class="fas fa-edit"></i>';
        editBtn.title = "Edit measurement";
        editBtn.style.cssText = "background: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;";
        editBtn.onclick = (e) => {
          e.stopPropagation();
          editMeasurement(series.id, measurement.id);
        };
        
        const removeBtn = document.createElement("button");
        removeBtn.className = "remove-measurement-btn";
        removeBtn.type = "button";
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.onclick = (e) => {
          e.stopPropagation();
          removeMeasurementFromSeries(series.id, measurement.id);
        };
        
        measurementActions.appendChild(editBtn);
        measurementActions.appendChild(removeBtn);
        
        measurementItem.appendChild(measurementInfo);
        measurementItem.appendChild(measurementActions);
        measurementsContainer.appendChild(measurementItem);
      });
    }
    
    seriesItem.appendChild(seriesHeader);
    seriesItem.appendChild(measurementsContainer);
    container.appendChild(seriesItem);
  });
}

// Setup Edit Popup Event Handlers
function setupEditPopupHandlers() {
  // ---------- EDIT PRODUCT / REMOVE PRODUCT CLICK HANDLERS ----------
  const productGridEl = document.querySelector(".product-grid");
  if (!productGridEl) {
    console.warn('Product grid not found, retrying in 100ms...');
    setTimeout(setupEditPopupHandlers, 100);
    return;
  }
  
  // Declare editPopup once at the top of the function
  const editPopup = document.getElementById("editPopup");
  
  productGridEl.addEventListener("click", e => {
    const editBtn = e.target.closest(".edit-btn") || e.target.closest(".product-edit-btn");
    if (editBtn) {
      e.stopPropagation();
      window.productBeingEdited = editBtn.closest(".product-card");
      const productId = window.productBeingEdited.dataset.id;
      const editPopupEl = document.getElementById("editPopup");

      // Fetch product data from database
      fetch(base_url + "ProductCon/get_product/" + productId)
        .then(res => res.json())
        .then(data => {
          if (data.status === "success" && data.product) {
            // Show popup BEFORE populating so renderStandardSeries uses correct container
            if (editPopupEl) editPopupEl.style.display = "flex";
            populateEditForm(data.product);
          } else {
            showToast("Failed to load product data.", 'error');
          }
        })
        .catch(error => {
          console.error('Error fetching product:', error);
          showToast("Error loading product data.", 'error');
        });
      return;
    }

    const removeBtn = e.target.closest(".remove-btn") || e.target.closest(".product-remove-btn");
    if (removeBtn) {
      e.stopPropagation();
      const productCard = removeBtn.closest(".product-card");
      if (productCard) {
        openDeletePopup(productCard);
      } else {
        console.error('Product card not found for remove button');
        showToast("Could not find product to delete.", 'error');
      }
    }
  });
  // Order Type Buttons for Edit
  const editDirectOrderBtn = document.getElementById("editDirectOrderBtn");
  const editSiteAssessmentBtn = document.getElementById("editSiteAssessmentBtn");
  const editOrderTypeInput = document.getElementById("editProductOrderType");
  const editCategorySelect = document.getElementById("editProductCategory");
  const editSubcategorySelect = document.getElementById("editProductSubcategory");
  const editSubcategoryGroup = document.getElementById("editSubcategoryGroup");
  const editCustomizationContainer = document.getElementById("editCustomizationFields");
  const editManageCustomizationGroup = document.getElementById("editManageCustomizationGroup");
  const editManageCustomizationBtn = document.getElementById("editManageCustomizationBtn");
  
    function setEditOrderType(orderType) {
    [editDirectOrderBtn, editSiteAssessmentBtn].forEach(btn => btn?.classList.remove("active"));
    
    if (orderType === "direct") {
      editDirectOrderBtn?.classList.add("active");
      if (editOrderTypeInput) editOrderTypeInput.value = "direct";
    } else if (orderType === "site-assessment") {
      editSiteAssessmentBtn?.classList.add("active");
      if (editOrderTypeInput) editOrderTypeInput.value = "site-assessment";
    }
    
    // Category and subcategory are read-only - don't update them when order type changes
    // They will remain as they were when the product was created
  }
  
  editDirectOrderBtn?.addEventListener("click", () => setEditOrderType("direct"));
  editSiteAssessmentBtn?.addEventListener("click", () => setEditOrderType("site-assessment"));
  
  // Category and subcategory are read-only in edit mode - no change handlers needed
  
  // Manage Customization Fields button for edit (Customize Build tab)
  // Use event delegation since button might not exist when this runs
  if (editPopup) {
    editPopup.addEventListener("click", (e) => {
      if (e.target.closest("#editManageCustomizationBtn")) {
        e.preventDefault();
        e.stopPropagation();
        // Get values from selects (even if disabled, value should still be accessible)
        let selectedCategory = editCategorySelect ? editCategorySelect.value : "";
        let selectedSubcategory = editSubcategorySelect ? editSubcategorySelect.value : "";
        
        // If values are empty (disabled selects sometimes don't return value), use stored product data
        if (!selectedCategory || !selectedSubcategory) {
          if (window.currentEditingProduct) {
            selectedCategory = selectedCategory || window.currentEditingProduct.Category || "";
            selectedSubcategory = selectedSubcategory || window.currentEditingProduct.Subcategory || "";
          }
        }
        
        // Fallback: try reading from selected option index
        if (!selectedCategory && editCategorySelect && editCategorySelect.selectedIndex >= 0) {
          const selectedOption = editCategorySelect.options[editCategorySelect.selectedIndex];
          if (selectedOption && selectedOption.value) {
            selectedCategory = selectedOption.value;
          }
        }
        if (!selectedSubcategory && editSubcategorySelect && editSubcategorySelect.selectedIndex >= 0) {
          const selectedOption = editSubcategorySelect.options[editSubcategorySelect.selectedIndex];
          if (selectedOption && selectedOption.value) {
            selectedSubcategory = selectedOption.value;
          }
        }
        
        if (selectedCategory && selectedSubcategory) {
          // Pass current product's stored series and customization data
          const productCustomization = window.currentEditingProduct?.Customization || {};
          const storedSeries = window.currentEditingProduct?.SelectedCustomizationSeries;
          showManageCustomizationFields(selectedCategory, selectedSubcategory, productCustomization, storedSeries);
        } else {
          showToast("Please ensure category and subcategory are set.", 'error');
        }
      }
    });
  }
  
  // Also attach directly if button exists (for immediate availability)
  editManageCustomizationBtn?.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    // Get values from selects (even if disabled, value should still be accessible)
    let selectedCategory = editCategorySelect ? editCategorySelect.value : "";
    let selectedSubcategory = editSubcategorySelect ? editSubcategorySelect.value : "";
    
    // If values are empty (disabled selects sometimes don't return value), use stored product data
    if (!selectedCategory || !selectedSubcategory) {
      if (window.currentEditingProduct) {
        selectedCategory = selectedCategory || window.currentEditingProduct.Category || "";
        selectedSubcategory = selectedSubcategory || window.currentEditingProduct.Subcategory || "";
      }
    }
    
    // Fallback: try reading from selected option index
    if (!selectedCategory && editCategorySelect && editCategorySelect.selectedIndex >= 0) {
      const selectedOption = editCategorySelect.options[editCategorySelect.selectedIndex];
      if (selectedOption && selectedOption.value) {
        selectedCategory = selectedOption.value;
      }
    }
    if (!selectedSubcategory && editSubcategorySelect && editSubcategorySelect.selectedIndex >= 0) {
      const selectedOption = editSubcategorySelect.options[editSubcategorySelect.selectedIndex];
      if (selectedOption && selectedOption.value) {
        selectedSubcategory = selectedOption.value;
      }
    }
    
    if (selectedCategory && selectedSubcategory) {
      // Pass current product's stored series and customization data
      const productCustomization = window.currentEditingProduct?.Customization || {};
      const storedSeries = window.currentEditingProduct?.SelectedCustomizationSeries;
      showManageCustomizationFields(selectedCategory, selectedSubcategory, productCustomization, storedSeries);
    } else {
      showToast("Please ensure category and subcategory are set.", 'error');
    }
  });
  
  
  // Tab switching for edit
  const editCustomizeTab = document.getElementById("editCustomizeTab");
  const editStandardTab = document.getElementById("editStandardTab");
  const editCustomizeTabContent = document.getElementById("editCustomizeTabContent");

  // Function to switch to Customize Build tab
  const switchToCustomizeTab = () => {
    if (editCustomizeTab) editCustomizeTab.classList.add("active");
    if (editCustomizeTabContent) editCustomizeTabContent.classList.add("active");
  };

  editCustomizeTab?.addEventListener("click", switchToCustomizeTab);

  // Also allow switching via data-tab attribute for consistency
  if (editCustomizeTab) {
    editCustomizeTab.setAttribute("data-tab", "customize");
  }
  
  // Setup Add Series button for edit
  const editAddSeriesBtn = document.getElementById("editAddSeriesBtn");
  if (editAddSeriesBtn) {
    editAddSeriesBtn.addEventListener("click", addSeries);
  }
  
  // Close popup handlers
  // editPopup already declared at the top of the function
  const editCloseBtn = document.getElementById("closeEditPopup");
  const editCancelBtn = document.getElementById("cancelEdit");
  const editSaveBtn = document.getElementById("editSaveBtn");
  
  const closeEditPopup = () => {
    if (editPopup) editPopup.style.display = "none";
    window.productBeingEdited = null;
    window.currentEditingProduct = null; // Clear stored product data
    clearImages('edit');
    tagPrices = {};
    standardSeries = [];
    
    // Reset category and subcategory (re-enable them for next edit)
    const editCatSelect = document.getElementById("editProductCategory");
    const editSubcatSelect = document.getElementById("editProductSubcategory");
    const editSubcatGroup = document.getElementById("editSubcategoryGroup");
    const editCustomContainer = document.getElementById("editCustomizationFields");
    const editManageGroup = document.getElementById("editManageCustomizationGroup");
    
    if (editCatSelect) {
      editCatSelect.value = "";
      editCatSelect.disabled = false;
    }
    if (editSubcatSelect) {
      editSubcatSelect.value = "";
      editSubcatSelect.innerHTML = '<option value="" disabled selected>Select subcategory</option>';
      editSubcatSelect.disabled = false;
    }
    if (editSubcatGroup) editSubcatGroup.style.display = "none";
    if (editCustomContainer) editCustomContainer.innerHTML = "";
    if (editManageGroup) editManageGroup.style.display = "none";
    
    // Reset order type
    const editOrderTypeInput = document.getElementById("editProductOrderType");
    const editDirectBtn = document.getElementById("editDirectOrderBtn");
    const editSiteAssessBtn = document.getElementById("editSiteAssessmentBtn");
    if (editOrderTypeInput) editOrderTypeInput.value = "direct";
    if (editDirectBtn) editDirectBtn.classList.add("active");
    if (editSiteAssessBtn) editSiteAssessBtn.classList.remove("active");
    
    // Reset tabs
    const editCustTab = document.getElementById("editCustomizeTab");
    const editCustTabContent = document.getElementById("editCustomizeTabContent");
    if (editCustTab) editCustTab.classList.add("active");
    if (editCustTabContent) editCustTabContent.classList.add("active");
  };
  
  editCloseBtn?.addEventListener("click", closeEditPopup);
  editCancelBtn?.addEventListener("click", closeEditPopup);

  // Save changes handler
  editSaveBtn?.addEventListener("click", async () => {
    if (!window.productBeingEdited) return;

    const id = window.productBeingEdited.dataset.id;
    const editNameInput = document.getElementById("editProductName");
    const editDescriptionInput = document.getElementById("editProductDescription");
    const editPriceMinInput = document.getElementById("editProductPriceMin");
    const editPriceMaxInput = document.getElementById("editProductPriceMax");
    const editCategoryEl = document.getElementById("editProductCategory");
    const editSubcategoryEl = document.getElementById("editProductSubcategory");
    const editOrderTypeInput = document.getElementById("editProductOrderType");

    let name = editNameInput ? editNameInput.value.trim() : '';
    let description = editDescriptionInput ? editDescriptionInput.value.trim() : '';
    let category = editCategoryEl ? editCategoryEl.value : '';
    let subcategory = editSubcategoryEl ? editSubcategoryEl.value : '';
    let orderType = editOrderTypeInput ? editOrderTypeInput.value : 'direct';
    let priceMin = editPriceMinInput ? parseFloat(editPriceMinInput.value) : 0;
    let priceMax = editPriceMaxInput ? parseFloat(editPriceMaxInput.value) : 0;

    // For editing, get existing product data if fields are empty (allows image-only updates)
    if (!name || !category || !orderType) {
      // Try to get values from the product being edited
      const productData = window.productBeingEdited.dataset;
      if (!name && productData.name) name = productData.name;
      if (!category && productData.category) category = productData.category;
      if (!orderType && productData.orderType) orderType = productData.orderType || 'direct';
      
      // If still missing required fields, show error
      if (!name || !category || !orderType) {
        showToast("Please complete all required fields.", 'error');
        return;
      }
    }

    // Check for duplicate product name (only if name changed)
    if (name && window.currentEditingProduct && name !== window.currentEditingProduct.ProductName) {
      try {
        const checkResponse = await fetch(base_url + "ProductCon/check_product_name?name=" + encodeURIComponent(name) + "&excludeId=" + id);
        const checkData = await checkResponse.json();
        if (checkData.exists) {
          showToast("A product with this name already exists. Product names must be unique.", 'error');
          return;
        }
      } catch (error) {
        console.error('Error checking product name:', error);
        // Continue with submission if check fails (backend will catch it)
      }
    }

    // Validate image count (check if new images uploaded or existing images present)
    const editPreviewGrid = document.getElementById("editImagePreviewGrid");
    const existingImageCount = editPreviewGrid ? editPreviewGrid.querySelectorAll('.image-preview-item').length : 0;
    const newImageCount = uploadedImages.edit.length;
    const totalImageCount = existingImageCount + newImageCount;
    const MIN_IMAGES = 1;
    const MAX_IMAGES = 10;
    
    // For editing, allow image-only updates without requiring prices
    // Only require prices if no images are being updated
    if (!priceMin && !priceMax && totalImageCount === 0) {
      showToast("Please enter at least a minimum or maximum price, or upload images.", 'error');
      return;
    }

    if (priceMin && priceMax && priceMin > priceMax) {
      showToast("Minimum price cannot be greater than maximum price.", 'error');
      return;
    }
    
    if (totalImageCount < MIN_IMAGES) {
      showToast(`Please ensure at least ${MIN_IMAGES} image is present. Currently: ${totalImageCount}`, 'error');
      return;
    }
    
    if (totalImageCount > MAX_IMAGES) {
      showToast(`Maximum ${MAX_IMAGES} images allowed per product. Currently: ${totalImageCount}`, 'error');
      return;
    }

    // Collect data from BOTH tabs:
    // 1. Customize Build tab - customization fields (tags, checkboxes, numbers, etc.)
    const customizationData = collectCustomizationData("edit");
    
    // 2. Standard tab - standard series with measurements
    // (standardSeries is already stored in global variable)

    let formData = new FormData();
    formData.append("name", name);
    formData.append("description", description);
    // Category and subcategory are read-only - don't send them (they won't be updated)
    // formData.append("category", category);
    // if (subcategory) formData.append("subcategory", subcategory);
    formData.append("orderType", orderType);
    formData.append("priceMin", priceMin);
    formData.append("priceMax", priceMax);
    
    // Save Customize Build data (customization fields)
    formData.append("customization", JSON.stringify(customizationData));
    // Save selected customization series
    if (selectedCustomizationSeries) {
      formData.append("selectedCustomizationSeries", selectedCustomizationSeries);
    }
    // Save tag prices (from Customize Build)
    formData.append("tagPrices", JSON.stringify(tagPrices));
    
    // Save tag visual configs for Konva.js 2D preview
    console.log("[Product Save] ========================================");
    console.log("[Product Save] tagPrices being saved:", tagPrices);
    console.log("[Product Save] tagVisualConfigs being saved:", tagVisualConfigs);
    console.log("[Product Save] tagVisualConfigs JSON:", JSON.stringify(tagVisualConfigs));
    
    // Check if frameColor has visual config
    if (tagVisualConfigs['frameColor']) {
        console.log("[Product Save] ✓ frameColor visual configs:", tagVisualConfigs['frameColor']);
    } else {
        console.log("[Product Save] ✗ No frameColor visual configs found!");
    }
    
    // Check if frameColor has prices (required for saving)
    if (tagPrices['frameColor']) {
        console.log("[Product Save] ✓ frameColor prices:", tagPrices['frameColor']);
    } else {
        console.log("[Product Save] ✗ No frameColor prices found - visual configs WON'T be saved!");
    }
    console.log("[Product Save] ========================================");
    
    formData.append("tagVisualConfigs", JSON.stringify(tagVisualConfigs));
    
    // Save tag images (from Customize Build) - append File objects
    Object.keys(tagImages).forEach(fieldId => {
      Object.keys(tagImages[fieldId]).forEach(tagName => {
        const imageFile = tagImages[fieldId][tagName];
        if (imageFile instanceof File) {
          formData.append(`tagImages[${fieldId}][${tagName}]`, imageFile);
        }
      });
    });
    
    // Save Standard data (series with measurements)
    if (standardSeries.length > 0) {
      formData.append("standardSeries", JSON.stringify(standardSeries));
    }
    
    // Add role parameter
    formData.append("user_role", "Admin");

    // Append all new images if any are uploaded
    uploadedImages.edit.forEach((file, index) => {
      formData.append(`productImages[]`, file);
    });
    
    // If no new images uploaded, we need to preserve existing images
    // The backend will handle this by checking if productImages is empty

    fetch(base_url + "ProductCon/update_product/" + id, {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === "updated") {
          // Clear selected series after successful save
          selectedCustomizationSeries = null;
          showToast("Product updated successfully!", 'success');
          setTimeout(() => location.reload(), 1000);
        } else {
          showToast(data.msg || "Failed to update product.", 'error');
        }
      })
      .catch(error => {
        console.error('Error updating product:', error);
        showToast("Error updating product.", 'error');
      });
  });
}
// -------------------- SORT --------------------
function setupProductSorting() {
  const sortSelect = document.getElementById("sortProducts");
  const productGrid = document.querySelector(".product-grid");

  if (!sortSelect || !productGrid) {
    console.warn('Sort elements not found, retrying...');
    setTimeout(setupProductSorting, 100);
    return;
  }

  sortSelect.addEventListener("change", () => {
    let cards = Array.from(productGrid.querySelectorAll(".product-card"));
    if (sortSelect.value === "recent") {
      // Sort by most recent (already in DOM order, just reverse)
      cards.reverse();
    } else if (sortSelect.value === "last") {
      // Sort by oldest first
      cards.reverse();
    }
    // Re-append cards in new order
    cards.forEach(card => productGrid.appendChild(card));
  });
}

// -------------------- INIT --------------------
document.addEventListener("DOMContentLoaded", () => {
  console.log('Products page DOM loaded, initializing...');

  // Load customization fields (DB → localStorage → JSON defaults)
  loadCustomizationFields().then(() => {
    // After loading, force update Windows_Casement if needed
    console.log('🔄 Checking Windows_Casement fields structure...');
    updateWindowsCasementFields().catch(e => {
      console.error('Error updating Windows_Casement fields:', e);
    });
  }).catch(e => console.error('Error loading customization fields:', e));
  
  // Setup functions with retry logic
  function initializeAll() {
    try {
      setupSearchFilter();
      setupProductPopups();
      setupProductSorting();
      setupEditPopupHandlers();
      console.log('All product page functions initialized successfully');
    } catch (error) {
      console.error('Error initializing product page:', error);
      // Retry after a short delay
      setTimeout(initializeAll, 200);
    }
  }
  
  // Small delay to ensure DOM is fully ready
  setTimeout(initializeAll, 50);
  
  // -------------------- PRODUCT IMAGE CAROUSEL --------------------
  function initializeProductCarousels() {
    const slideshows = document.querySelectorAll('.product-image-slideshow');
    
    slideshows.forEach(function(slideshow) {
      const slides = slideshow.querySelectorAll('.product-slide');
      const indicators = slideshow.querySelectorAll('.indicator-dot');
      
      if (slides.length <= 1) return; // No carousel needed for single image
      
      let currentSlide = 0;
      const totalSlides = slides.length;
      let carouselInterval;
      
      function showSlide(index) {
        // Remove active class from all slides and indicators
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        // Add active class to current slide and indicator
        if (slides[index]) {
          slides[index].classList.add('active');
        }
        if (indicators[index]) {
          indicators[index].classList.add('active');
        }
      }
      
      function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
      }
      
      function startCarousel() {
        if (carouselInterval) clearInterval(carouselInterval);
        carouselInterval = setInterval(nextSlide, 3000);
      }
      
      function stopCarousel() {
        if (carouselInterval) clearInterval(carouselInterval);
      }
      
      // Add click handlers to indicators
      indicators.forEach(function(indicator, index) {
        indicator.addEventListener('click', function() {
          currentSlide = index;
          showSlide(currentSlide);
          startCarousel(); // Restart carousel after manual navigation
        });
      });
      
      // Pause on hover
      slideshow.addEventListener('mouseenter', stopCarousel);
      slideshow.addEventListener('mouseleave', startCarousel);
      
      // Start carousel
      startCarousel();
    });
  }
  
  // Initialize carousels when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeProductCarousels);
  } else {
    initializeProductCarousels();
  }
});
