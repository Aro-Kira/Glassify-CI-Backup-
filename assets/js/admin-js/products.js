// =====================================================
// PRODUCTS.JS
// =====================================================

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
  files.forEach(file => {
    if (!file.type.startsWith('image/')) {
      alert(`${file.name} is not an image file.`);
      return;
    }

    // Check if file already exists
    if (uploadedImages[mode].some(f => f.name === file.name && f.size === file.size)) {
      return; // Skip duplicates
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

  countIndicator.textContent = count;

  // Update styling based on count
  indicator.classList.remove('invalid', 'valid');
  
  if (count === 0) {
    indicator.classList.add('invalid');
  } else if (count >= 3) {
    indicator.classList.add('valid');
  } else {
    indicator.classList.add('invalid');
  }
}

/**
 * Validate minimum image requirement
 */
function validateImageCount(mode) {
  const count = uploadedImages[mode].length;
  if (count < 3) {
    alert(`Please upload at least 3 images. Currently uploaded: ${count}`);
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
const deletePopup = document.getElementById("popup-delete");
const deleteCloseBtn = document.querySelector(".popup-delete-close");
const deleteCancelBtn = document.querySelector(".popup-delete-cancel");
const deleteConfirmBtn = document.querySelector(".popup-delete-confirm");
const deleteMessage = document.getElementById("delete-message");

function openDeletePopup(card) {
  cardToDelete = card;
  const name = card.querySelector(".product-name").textContent;
  deleteMessage.textContent = `Are you sure you want to delete "${name}"?`;
  deletePopup.style.display = "flex";
}

function closeDeletePopup() {
  deletePopup.style.display = "none";
  cardToDelete = null;
}

deleteCloseBtn?.addEventListener("click", closeDeletePopup);
deleteCancelBtn?.addEventListener("click", closeDeletePopup);

deleteConfirmBtn?.addEventListener("click", () => {
  if (!cardToDelete) return;

  let id = cardToDelete.dataset.id;

  fetch(base_url + "ProductCon/delete_product/" + id)
    .then(res => res.json())
    .then(data => {
      if (data.status === "deleted") {
        cardToDelete.remove();
        closeDeletePopup();
      } else {
        showToast("Failed to delete product.", 'error');
      }
    });
});

// -------------------- SEARCH & FILTER --------------------
function setupSearchFilter() {
  const searchInput = document.querySelector(".search-input");
  const searchButton = document.querySelector(".search-button");
  const categoryFilter = document.querySelector(".filter-category");
  const productCards = document.querySelectorAll(".product-card");

  function filterProducts() {
    const searchTerm = searchInput.value.toLowerCase();
    const selectedCategory = categoryFilter.value.toLowerCase();

    productCards.forEach(card => {
      const name = card.querySelector(".product-name").textContent.toLowerCase();
      const category = card.dataset.category.toLowerCase();

      const show =
        name.includes(searchTerm) &&
        (selectedCategory === "" || selectedCategory === category);

      card.style.display = show ? "" : "none";
    });
  }

  searchButton?.addEventListener("click", filterProducts);
  searchInput?.addEventListener("keyup", e => e.key === "Enter" && filterProducts());
  categoryFilter?.addEventListener("change", filterProducts);
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

// Category to Subcategory mapping
// Maps each main category to its available subcategories
const categorySubcategories = {
  "Windows": ["Sliding", "Awning", "Casement", "Fixed Glass"],
  "Doors": ["Sliding", "Frameless"],
  "Glass Partitions & Enclosures": ["Frameless Glass", "Shower Enclosure", "Fixed Glass"],
  "Mirrors & Specialty Glass": ["Mirrors", "Top Glass", "Glass Board"],
  "Cabinets & Furniture": ["Kitchen Cabinet", "Wardrobe Cabinet"],
  "Commercial & Exterior": ["Storefront", "Glass Balcony", "Stair Railings"]
};

// Customization field configurations for each subcategory
// Each field has: type (tags, checkbox, color, number), label, options/values
// Note: Some subcategory names appear in multiple categories, so we use composite keys
// Tags allow multiple selections and dynamic addition/removal
const customizationFields = {
  // Windows subcategories
  "Windows_Sliding": [
    { type: "number", label: "Width (cm)", id: "width", min: 1, step: 0.1 },
    { type: "number", label: "Height (cm)", id: "height", min: 1, step: 0.1 },
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Clear", "Tinted", "Laminated"] },
    { type: "tags", label: "Frame Color/Material", id: "frameColor", options: ["White", "Black", "Silver", "Bronze", "Wood", "Aluminum"] },
    { type: "number", label: "Thickness (mm)", id: "thickness", min: 1, step: 0.1 },
    { type: "checkbox", label: "Screen", id: "screen" }
  ],
  "Windows_Awning": [
    { type: "number", label: "Width (cm)", id: "width", min: 1, step: 0.1 },
    { type: "number", label: "Height (cm)", id: "height", min: 1, step: 0.1 },
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Clear", "Tinted", "Laminated"] },
    { type: "tags", label: "Frame Color/Material", id: "frameColor", options: ["White", "Black", "Silver", "Bronze", "Wood", "Aluminum"] },
    { type: "number", label: "Thickness (mm)", id: "thickness", min: 1, step: 0.1 },
    { type: "checkbox", label: "Screen", id: "screen" }
  ],
  "Windows_Casement": [
    { type: "number", label: "Width (cm)", id: "width", min: 1, step: 0.1 },
    { type: "number", label: "Height (cm)", id: "height", min: 1, step: 0.1 },
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Clear", "Tinted", "Laminated"] },
    { type: "tags", label: "Frame Color/Material", id: "frameColor", options: ["White", "Black", "Silver", "Bronze", "Wood", "Aluminum"] },
    { type: "number", label: "Thickness (mm)", id: "thickness", min: 1, step: 0.1 },
    { type: "checkbox", label: "Screen", id: "screen" }
  ],
  "Windows_Fixed Glass": [
    { type: "number", label: "Width (cm)", id: "width", min: 1, step: 0.1 },
    { type: "number", label: "Height (cm)", id: "height", min: 1, step: 0.1 },
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Clear", "Tinted", "Laminated"] },
    { type: "tags", label: "Frame Color/Material", id: "frameColor", options: ["White", "Black", "Silver", "Bronze", "Wood", "Aluminum"] },
    { type: "number", label: "Thickness (mm)", id: "thickness", min: 1, step: 0.1 },
    { type: "checkbox", label: "Screen", id: "screen" }
  ],
  // Doors subcategories
  "Doors_Sliding": [
    { type: "number", label: "Size (cm)", id: "size", min: 1, step: 0.1 },
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Clear", "Tinted", "Laminated"] },
    { type: "tags", label: "Handle Type", id: "handleType", options: ["Type A", "Type B", "Type C"] },
    { type: "tags", label: "Lock Type", id: "lockType", options: ["Type A", "Type B", "Type C"] },
    { type: "checkbox", label: "Soft-close", id: "softClose" }
  ],
  "Doors_Frameless": [
    { type: "number", label: "Size (cm)", id: "size", min: 1, step: 0.1 },
    { type: "tags", label: "Glass Type", id: "glassType", options: ["Clear", "Tinted", "Laminated"] },
    { type: "tags", label: "Handle Type", id: "handleType", options: ["Type A", "Type B", "Type C"] },
    { type: "tags", label: "Lock Type", id: "lockType", options: ["Type A", "Type B", "Type C"] },
    { type: "checkbox", label: "Soft-close", id: "softClose" }
  ],
  // Glass Partitions & Enclosures subcategories
  "Partitions_Frameless Glass": [
    { type: "tags", label: "Layout", id: "layout", options: ["L-shape", "Straight", "U-shape"] },
    { type: "number", label: "Glass Thickness (mm)", id: "glassThickness", min: 1, step: 0.1 },
    { type: "tags", label: "Finish", id: "finish", options: ["Clear", "Frosted", "Patterned"] },
    { type: "tags", label: "Hardware Color", id: "hardwareColor", options: ["Black", "Silver", "Gold", "White", "Bronze"] }
  ],
  "Partitions_Shower Enclosure": [
    { type: "tags", label: "Layout", id: "layout", options: ["L-shape", "Straight", "U-shape"] },
    { type: "number", label: "Glass Thickness (mm)", id: "glassThickness", min: 1, step: 0.1 },
    { type: "tags", label: "Finish", id: "finish", options: ["Clear", "Frosted", "Patterned"] },
    { type: "tags", label: "Hardware Color", id: "hardwareColor", options: ["Black", "Silver", "Gold", "White", "Bronze"] }
  ],
  "Partitions_Fixed Glass": [
    { type: "tags", label: "Layout", id: "layout", options: ["L-shape", "Straight", "U-shape"] },
    { type: "number", label: "Glass Thickness (mm)", id: "glassThickness", min: 1, step: 0.1 },
    { type: "tags", label: "Finish", id: "finish", options: ["Clear", "Frosted", "Patterned"] },
    { type: "tags", label: "Hardware Color", id: "hardwareColor", options: ["Black", "Silver", "Gold", "White", "Bronze"] }
  ],
  // Mirrors & Specialty Glass subcategories
  "Specialty_Mirrors": [
    { type: "tags", label: "Shape", id: "shape", options: ["Round", "Rectangle", "Oval"] },
    { type: "number", label: "Size (cm)", id: "size", min: 1, step: 0.1 },
    { type: "tags", label: "Edge Finish", id: "edgeFinish", options: ["Beveled", "Polished", "Raw"] },
    { type: "tags", label: "Mounting Method", id: "mountingMethod", options: ["Wall-mounted", "Stand", "Adhesive"] }
  ],
  "Specialty_Top Glass": [
    { type: "tags", label: "Shape", id: "shape", options: ["Round", "Rectangle", "Oval"] },
    { type: "number", label: "Size (cm)", id: "size", min: 1, step: 0.1 },
    { type: "tags", label: "Edge Finish", id: "edgeFinish", options: ["Beveled", "Polished", "Raw"] },
    { type: "tags", label: "Mounting Method", id: "mountingMethod", options: ["Wall-mounted", "Stand", "Adhesive"] }
  ],
  "Specialty_Glass Board": [
    { type: "tags", label: "Shape", id: "shape", options: ["Round", "Rectangle", "Oval"] },
    { type: "number", label: "Size (cm)", id: "size", min: 1, step: 0.1 },
    { type: "tags", label: "Edge Finish", id: "edgeFinish", options: ["Beveled", "Polished", "Raw"] },
    { type: "tags", label: "Mounting Method", id: "mountingMethod", options: ["Wall-mounted", "Stand", "Adhesive"] }
  ],
  // Cabinets & Furniture subcategories
  "Cabinets_Kitchen Cabinet": [
    { type: "tags", label: "Material", id: "material", options: ["Wood", "MDF", "Metal", "Glass"] },
    { type: "tags", label: "Finish", id: "finish", options: ["Matte", "Glossy", "Laminate"] },
    { type: "number", label: "Size (cm)", id: "size", min: 1, step: 0.1 },
    { type: "tags", label: "Door Type", id: "doorType", options: ["Glass", "Solid"] },
    { type: "tags", label: "Accessories", id: "accessories", options: ["Handles", "Locks", "Soft-close"] }
  ],
  "Cabinets_Wardrobe Cabinet": [
    { type: "tags", label: "Material", id: "material", options: ["Wood", "MDF", "Metal", "Glass"] },
    { type: "tags", label: "Finish", id: "finish", options: ["Matte", "Glossy", "Laminate"] },
    { type: "number", label: "Size (cm)", id: "size", min: 1, step: 0.1 },
    { type: "tags", label: "Door Type", id: "doorType", options: ["Glass", "Solid"] },
    { type: "tags", label: "Accessories", id: "accessories", options: ["Handles", "Locks", "Soft-close"] }
  ],
  // Commercial & Exterior subcategories
  "Commercial_Storefront": [
    { type: "tags", label: "Safety Glass Type", id: "safetyGlassType", options: ["Tempered", "Laminated", "Bulletproof"] },
    { type: "number", label: "Height (cm)", id: "height", min: 1, step: 0.1 },
    { type: "number", label: "Width (cm)", id: "width", min: 1, step: 0.1 },
    { type: "tags", label: "Handrail Type", id: "handrailType", options: ["Stainless steel", "Aluminum", "Glass"] },
    { type: "tags", label: "Mounting System", id: "mountingSystem", options: ["Clamp", "Bolt", "Adhesive"] }
  ],
  "Commercial_Glass Balcony": [
    { type: "tags", label: "Safety Glass Type", id: "safetyGlassType", options: ["Tempered", "Laminated", "Bulletproof"] },
    { type: "number", label: "Height (cm)", id: "height", min: 1, step: 0.1 },
    { type: "number", label: "Width (cm)", id: "width", min: 1, step: 0.1 },
    { type: "tags", label: "Handrail Type", id: "handrailType", options: ["Stainless steel", "Aluminum", "Glass"] },
    { type: "tags", label: "Mounting System", id: "mountingSystem", options: ["Clamp", "Bolt", "Adhesive"] }
  ],
  "Commercial_Stair Railings": [
    { type: "tags", label: "Safety Glass Type", id: "safetyGlassType", options: ["Tempered", "Laminated", "Bulletproof"] },
    { type: "number", label: "Height (cm)", id: "height", min: 1, step: 0.1 },
    { type: "number", label: "Width (cm)", id: "width", min: 1, step: 0.1 },
    { type: "tags", label: "Handrail Type", id: "handrailType", options: ["Stainless steel", "Aluminum", "Glass"] },
    { type: "tags", label: "Mounting System", id: "mountingSystem", options: ["Clamp", "Bolt", "Adhesive"] }
  ]
};

/**
 * Populates subcategory dropdown based on selected category
 * @param {string} category - Selected main category
 * @param {HTMLElement} subcategorySelect - Subcategory select element
 */
function populateSubcategories(category, subcategorySelect) {
  // Clear existing options except the first placeholder
  subcategorySelect.innerHTML = '<option value="" disabled selected>Select subcategory</option>';
  
  if (category && categorySubcategories[category]) {
    categorySubcategories[category].forEach(subcat => {
      const option = document.createElement("option");
      option.value = subcat;
      option.textContent = subcat;
      subcategorySelect.appendChild(option);
    });
  }
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
  } else if (category === "Cabinets & Furniture") {
    fieldKey = `Cabinets_${subcategory}`;
  } else if (category === "Commercial & Exterior") {
    fieldKey = `Commercial_${subcategory}`;
  } else {
    fieldKey = subcategory; // Fallback to subcategory name
  }
  
  const fields = customizationFields[fieldKey];
  
  if (!fields) {
    return;
  }
  
  fields.forEach(field => {
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
        hiddenInput.value = "[]"; // Initialize as empty array JSON
        
        // Store available options in data attribute (original + dynamically added)
        tagContainer.dataset.availableOptions = JSON.stringify(field.options || []);
        // Store original options separately to track which tags can be removed
        tagContainer.dataset.originalOptions = JSON.stringify(field.options || []);
        
        // Render initial tags
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
    } else {
      formGroup.appendChild(input);
      container.appendChild(formGroup);
    }
  });
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
  
  // Get all available options (including dynamically added ones)
  const availableOptions = JSON.parse(container.dataset.availableOptions || "[]");
  const originalOptions = JSON.parse(container.dataset.originalOptions || "[]");
  const allOptions = [...new Set([...availableOptions, ...selectedValues])]; // Merge and deduplicate
  
  // Update available options
  container.dataset.availableOptions = JSON.stringify(allOptions);
  
  // Render each tag
  allOptions.forEach(option => {
    const tag = document.createElement("span");
    tag.className = "tag";
    tag.dataset.value = option;
    tag.textContent = option;
    
    // Check if tag is selected
    if (selectedValues.includes(option)) {
      tag.classList.add("selected");
    }
    
    // Toggle selection on click
    tag.addEventListener("click", () => {
      toggleTagSelection(tag, prefix, fieldId);
    });
    
    // Add remove button (X icon) for dynamically added tags only
    // Compare against originalOptions to determine if tag was dynamically added
    if (!originalOptions.includes(option)) {
      const removeBtn = document.createElement("span");
      removeBtn.className = "tag-remove";
      removeBtn.innerHTML = " ×";
      removeBtn.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent tag selection toggle
        removeTag(option, container, prefix, fieldId);
      });
      tag.appendChild(removeBtn);
    }
    
    container.appendChild(tag);
  });
  
  // Update hidden input with selected values
  if (hiddenInput) {
    hiddenInput.value = JSON.stringify(selectedValues);
  }
}

/**
 * Toggles tag selection state
 * @param {HTMLElement} tag - Tag element clicked
 * @param {string} prefix - Field prefix
 * @param {string} fieldId - Field ID
 */
function toggleTagSelection(tag, prefix, fieldId) {
  const value = tag.dataset.value;
  const hiddenInput = document.getElementById(`${prefix}${fieldId}`);
  
  if (!hiddenInput) return;
  
  let selectedValues = JSON.parse(hiddenInput.value || "[]");
  
  if (tag.classList.contains("selected")) {
    // Deselect tag
    tag.classList.remove("selected");
    selectedValues = selectedValues.filter(v => v !== value);
  } else {
    // Select tag
    tag.classList.add("selected");
    if (!selectedValues.includes(value)) {
      selectedValues.push(value);
    }
  }
  
  // Update hidden input
  hiddenInput.value = JSON.stringify(selectedValues);
}

/**
 * Removes a tag from available options
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
  
  // Re-render tags
  renderTags(container, updatedOptions, prefix, fieldId);
}

/**
 * Shows dialog to add a new tag
 * @param {HTMLElement} container - Tag container
 * @param {string} prefix - Field prefix
 * @param {string} fieldId - Field ID
 */
function showAddTagDialog(container, prefix, fieldId) {
  const tagValue = prompt("Enter new tag name:");
  
  if (!tagValue || !tagValue.trim()) {
    return; // User cancelled or entered empty value
  }
  
  const trimmedValue = tagValue.trim();
  const availableOptions = JSON.parse(container.dataset.availableOptions || "[]");
  
  // Check if tag already exists
  if (availableOptions.includes(trimmedValue)) {
    alert("This tag already exists!");
    return;
  }
  
  // Add to available options
  availableOptions.push(trimmedValue);
  container.dataset.availableOptions = JSON.stringify(availableOptions);
  
  // Re-render tags with new option
  renderTags(container, availableOptions, prefix, fieldId);
  
  // Auto-select the newly added tag
  const newTag = container.querySelector(`[data-value="${trimmedValue}"]`);
  if (newTag && !newTag.classList.contains("selected")) {
    toggleTagSelection(newTag, prefix, fieldId);
  }
}

/**
 * Collects all customization field values from the form
 * @param {string} prefix - Prefix for field IDs (e.g., "" for add or "edit" for edit)
 * @returns {Object} Object containing all field values
 */
function collectCustomizationData(prefix = "") {
  const data = {};
  // Use correct container ID based on prefix
  const containerId = prefix === "edit" ? "editCustomizationFields" : "customizationFields";
  const container = document.getElementById(containerId);
  
  if (!container) return data;
  
  // Get all inputs, selects, and checkboxes
  const inputs = container.querySelectorAll("input, select");
  
  inputs.forEach(input => {
    if (input.type === "hidden") {
      // Hidden inputs store tag selections as JSON arrays
      try {
        const parsed = JSON.parse(input.value || "[]");
        data[input.name] = Array.isArray(parsed) ? parsed : [];
      } catch (e) {
        data[input.name] = [];
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

// -------------------- POPUPS (ADD / EDIT) --------------------
function setupProductPopups() {
  const productGrid = document.querySelector(".product-grid");

  const addPopup = document.getElementById("productPopup");
  const addBtn = document.querySelector(".add-product-btn");
  const addCloseBtn = document.getElementById("closePopup");
  const addCancelBtn = addPopup?.querySelector(".cancel-btn");
  const addSaveBtn = addPopup?.querySelector(".save-btn");
  const addImageInput = document.getElementById("productImageInput");
  const addImagePreview = addPopup?.querySelector(".image-preview img");
  const addNameInput = document.getElementById("productName");
  const addPriceInput = document.getElementById("productPrice");
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

  // ---------- ADD MODAL: Category Change Handler ----------
  addCategorySelect?.addEventListener("change", function() {
    const selectedCategory = this.value;
    if (selectedCategory) {
      // Show subcategory dropdown and populate it
      addSubcategoryGroup.style.display = "block";
      populateSubcategories(selectedCategory, addSubcategorySelect);
      // Clear customization fields until subcategory is selected
      addCustomizationContainer.innerHTML = "";
    } else {
      addSubcategoryGroup.style.display = "none";
      addCustomizationContainer.innerHTML = "";
    }
  });

  // ---------- ADD MODAL: Subcategory Change Handler ----------
  addSubcategorySelect?.addEventListener("change", function() {
    const selectedSubcategory = this.value;
    const selectedCategory = addCategorySelect.value;
    if (selectedSubcategory) {
      generateCustomizationFields(selectedSubcategory, addCustomizationContainer, "", selectedCategory);
    } else {
      addCustomizationContainer.innerHTML = "";
    }
  });

  // ---------- EDIT MODAL: Category Change Handler ----------
  editCategorySelect?.addEventListener("change", function() {
    const selectedCategory = this.value;
    if (selectedCategory) {
      // Show subcategory dropdown and populate it
      editSubcategoryGroup.style.display = "block";
      populateSubcategories(selectedCategory, editSubcategorySelect);
      // Clear customization fields until subcategory is selected
      editCustomizationContainer.innerHTML = "";
    } else {
      editSubcategoryGroup.style.display = "none";
      editCustomizationContainer.innerHTML = "";
    }
  });

  // ---------- EDIT MODAL: Subcategory Change Handler ----------
  editSubcategorySelect?.addEventListener("change", function() {
    const selectedSubcategory = this.value;
    const selectedCategory = editCategorySelect.value;
    if (selectedSubcategory) {
      generateCustomizationFields(selectedSubcategory, editCustomizationContainer, "edit", selectedCategory);
    } else {
      editCustomizationContainer.innerHTML = "";
    }
  });

  // ---------- ADD PRODUCT ----------
  addBtn?.addEventListener("click", () => (addPopup.style.display = "flex"));

  [addCloseBtn, addCancelBtn].forEach(btn =>
    btn?.addEventListener("click", () => {
      addPopup.style.display = "none";
      addNameInput.value = "";
      addPriceInput.value = "";
      clearImages('add');
      // Reset category and subcategory
      addCategorySelect.value = "";
      addSubcategorySelect.value = "";
      addSubcategoryGroup.style.display = "none";
      addCustomizationContainer.innerHTML = "";
    })
  );

  // Setup multiple image upload for add popup
  setupMultipleImageUpload('productImageInput', 'imageUploadDropzone', 'imagePreviewGrid', 'imageCount', 'add');

  addSaveBtn?.addEventListener("click", () => {
    let name = addNameInput.value.trim();
    let categoryEl = document.getElementById("productCategory");
    let subcategoryEl = document.getElementById("productSubcategory");
    let materialEl = document.getElementById("productMaterial");
    let category = categoryEl ? categoryEl.value : '';
    let subcategory = subcategoryEl ? subcategoryEl.value : '';
    let material = materialEl ? materialEl.value : '';
    let price = addPriceInput.value;

    if (!name || !category || !material || !price)
      return alert("Please complete all required fields.");

    // Validate image count
    if (!validateImageCount('add'))
      return;

    // Collect customization data
    const customizationData = collectCustomizationData("");

    let formData = new FormData();
    formData.append("name", name);
    formData.append("category", category);
    if (subcategory) formData.append("subcategory", subcategory);
    formData.append("material", material);
    formData.append("price", price);
    // Append customization data as JSON string
    formData.append("customization", JSON.stringify(customizationData));
    
    // Append all images
    uploadedImages.add.forEach((file, index) => {
      formData.append(`productImages[]`, file);
    });

    fetch(base_url + "ProductCon/add_product", { method: "POST", body: formData })
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          location.reload();
        } else {
          showToast("Error saving product.", 'error');
        }
      });
  });

  // ---------- EDIT PRODUCT ----------
productGrid.addEventListener("click", e => {
  const editBtn = e.target.closest(".edit-btn");
  if (editBtn) {
    productBeingEdited = editBtn.closest(".product-card");

    // Populate fields
    editNameInput.value = productBeingEdited.querySelector(".product-name").textContent;
    editPriceInput.value = normalizePrice(
      productBeingEdited.querySelector(".product-price").textContent
    );
    
    // Load existing images if available (for backward compatibility, we'll handle JSON array)
    // For now, we'll just clear the images and let admin re-upload if needed
    clearImages('edit');

    // Populate category and material
    const category = productBeingEdited.dataset.category;
    const material = productBeingEdited.dataset.material;

    const editCategoryEl = document.getElementById("editProductCategory");
    const editMaterialEl = document.getElementById("editProductMaterial");
    if (editCategoryEl) editCategoryEl.value = category || '';
    if (editMaterialEl) editMaterialEl.value = material || '';

    editPopup.style.display = "flex";
    return;
  }

  const removeBtn = e.target.closest(".remove-btn");
  if (removeBtn) {
    openDeletePopup(removeBtn.closest(".product-card"));
  }
});

// Close popup
[editCloseBtn, editCancelBtn].forEach(btn =>
  btn?.addEventListener("click", () => {
    editPopup.style.display = "none";
    productBeingEdited = null;
    clearImages('edit');
    // Reset category and subcategory
    editCategorySelect.value = "";
    editSubcategorySelect.value = "";
    editSubcategoryGroup.style.display = "none";
    editCustomizationContainer.innerHTML = "";
  })
);

// Save changes
editSaveBtn?.addEventListener("click", () => {
  if (!productBeingEdited) return;

  const id = productBeingEdited.dataset.id;

  // Collect customization data
  const customizationData = collectCustomizationData("edit");

  let formData = new FormData();
  formData.append("name", editNameInput.value);
  formData.append("price", editPriceInput.value);
  const editCategoryEl = document.getElementById("editProductCategory");
  const editSubcategoryEl = document.getElementById("editProductSubcategory");
  const editMaterialEl = document.getElementById("editProductMaterial");
  formData.append("category", editCategoryEl ? editCategoryEl.value : '');
  if (editSubcategoryEl && editSubcategoryEl.value) {
    formData.append("subcategory", editSubcategoryEl.value);
  }
  // Admin CANNOT edit materials - do not send material field
  // formData.append("material", editMaterialEl ? editMaterialEl.value : '');
  
  // Append customization data as JSON string
  formData.append("customization", JSON.stringify(customizationData));
  
  // Add role parameter
  formData.append("user_role", "Admin");

  // Append all images if any are uploaded
  uploadedImages.edit.forEach((file, index) => {
    formData.append(`productImages[]`, file);
  });

  fetch(base_url + "ProductCon/update_product/" + id, {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "updated") {
        location.reload();
      } else {
        showToast("Failed to update product.", 'error');
      }
    });
});
}
// -------------------- SORT --------------------
function setupProductSorting() {
  const sortSelect = document.getElementById("sortProducts");
  const productGrid = document.querySelector(".product-grid");

  sortSelect?.addEventListener("change", () => {
    let cards = Array.from(productGrid.querySelectorAll(".product-card"));
    if (sortSelect.value === "recent") cards.reverse();
    cards.forEach(card => productGrid.appendChild(card));
  });
}

// -------------------- INIT --------------------
document.addEventListener("DOMContentLoaded", () => {
  setupSearchFilter();
  setupProductPopups();
  setupProductSorting();
});
