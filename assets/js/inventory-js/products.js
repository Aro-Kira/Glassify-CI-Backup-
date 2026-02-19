// Deprecated: inventory products.js — Inventory Page removed
// (Kept for backward compatibility but no longer active)
// =====================================================
// INVENTORY PRODUCTS.JS - Inventory Officer Role
// =====================================================

// =============================
// TOAST NOTIFICATION SYSTEM
// =============================
function showToast(message, type = 'info', duration = 3000) {
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => {
        toast.classList.add('toast-fade-out');
        setTimeout(() => toast.remove(), 300);
    });

    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    const config = {
        success: { icon: '✓', bg: '#28a745', border: '#1e7e34' },
        error: { icon: '✕', bg: '#dc3545', border: '#c82333' },
        warning: { icon: '⚠', bg: '#ffc107', border: '#e0a800' },
        info: { icon: 'ℹ', bg: '#17a2b8', border: '#138496' }
    };
    
    const toastConfig = config[type] || config.info;
    
    toast.innerHTML = `
        <div class="toast-icon">${toastConfig.icon}</div>
        <div class="toast-message">${message}</div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    toast.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${toastConfig.bg};
        color: white;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        max-width: 500px;
        animation: toastSlideIn 0.3s ease;
        font-family: 'Montserrat', sans-serif;
        border-left: 4px solid ${toastConfig.border};
    `;
    
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            @keyframes toastSlideIn {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes toastFadeOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
            .toast-notification { transition: all 0.3s ease; }
            .toast-fade-out { animation: toastFadeOut 0.3s ease forwards; }
            .toast-icon { font-size: 20px; font-weight: bold; flex-shrink: 0; }
            .toast-message { flex: 1; font-size: 14px; line-height: 1.4; }
            .toast-close { background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; opacity: 0.8; transition: opacity 0.2s; flex-shrink: 0; }
            .toast-close:hover { opacity: 1; }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('toast-fade-out');
        setTimeout(() => toast.remove(), 300);
    }, duration);
    
    return toast;
}

// Get user role from global variable (set in view)
const userRole = typeof user_role !== 'undefined' ? user_role : 'Inventory Officer';

// -------------------- IMAGE PREVIEW --------------------
function setupImagePreview(inputElem, previewElem, placeholder) {
  if (!inputElem || inputElem.disabled) return;
  
  inputElem?.addEventListener("change", function () {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = () => (previewElem.src = reader.result);
      reader.readAsDataURL(file);
    } else {
      previewElem.src = placeholder;
    }
  });
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

// -------------------- EDIT PRODUCT (Inventory Officer) --------------------
function setupProductPopups() {
  const productGrid = document.querySelector(".product-grid");
  const editPopup = document.getElementById("editPopup");
  const editCloseBtn = document.getElementById("closeEditPopup");
  const editCancelBtn = document.getElementById("cancelEdit");
  const editSaveBtn = document.getElementById("editSaveBtn");
  const editImageInput = document.getElementById("editProductImageInput");
  const editImagePreview = document.getElementById("editProductImagePreview");
  const editNameInput = document.getElementById("editProductName");
  const editPriceInput = document.getElementById("editProductPrice");
  const editCategoryEl = document.getElementById("editProductCategory");
  const editMaterialEl = document.getElementById("editProductMaterial");
  const editImageLabel = document.getElementById("editProductImageLabel");
  const addMaterialBtn = document.getElementById("addMaterialBtn");
  const materialsList = document.getElementById("materialsList");
  const materialSelector = document.getElementById("materialSelector");
  const saveMaterialBtn = document.getElementById("saveMaterialBtn");
  const cancelMaterialBtn = document.getElementById("cancelMaterialBtn");
  
  const placeholderImg = "https://cdn-icons-png.flaticon.com/512/4211/4211763.png";
  let productBeingEdited = null;
  let productMaterials = []; // Array to store materials for current product

  // For Inventory Officer: Image, Name, Category, Price are READ-ONLY
  // Only Material is editable
  if (userRole === 'Inventory Officer') {
    // Disable image upload
    if (editImageInput) {
      editImageInput.disabled = true;
    }
    if (editImageLabel) {
      editImageLabel.style.opacity = '0.5';
      editImageLabel.style.cursor = 'not-allowed';
    }
    
    // Make name, category, price readonly
    if (editNameInput) editNameInput.readOnly = true;
    if (editPriceInput) editPriceInput.readOnly = true;
    if (editCategoryEl) editCategoryEl.disabled = true;
    
    // Material should be editable
    if (editMaterialEl) editMaterialEl.disabled = false;
  }

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
      editImagePreview.src = productBeingEdited.querySelector(".product-image img").src;

      // Populate category
      const category = productBeingEdited.dataset.category;
      if (editCategoryEl) editCategoryEl.value = category || '';
      
      // Load product materials from server
      const productId = productBeingEdited.dataset.id;
      loadProductMaterials(productId);

      editPopup.style.display = "flex";
      return;
    }

    const removeBtn = e.target.closest(".remove-btn");
    if (removeBtn) {
      openDeletePopup(removeBtn.closest(".product-card"));
    }
  });

  // Load product materials from server
  function loadProductMaterials(productId) {
    fetch(base_url + "ProductCon/get_product_materials/" + productId)
      .then(res => res.json())
      .then(data => {
        if (data.success && data.materials) {
          productMaterials = data.materials;
          renderMaterialsList();
        } else {
          productMaterials = [];
          renderMaterialsList();
        }
      })
      .catch(error => {
        console.error("Error loading materials:", error);
        productMaterials = [];
        renderMaterialsList();
      });
  }
  
  // Render materials list
  function renderMaterialsList() {
    if (!materialsList) return;
    
    if (productMaterials.length === 0) {
      materialsList.innerHTML = '<p style="color: #999; font-size: 14px; padding: 10px; text-align: center; background: #f5f5f5; border-radius: 4px;">No materials added yet. Click "Add New Material" to add.</p>';
      return;
    }
    
    materialsList.innerHTML = productMaterials.map((material, index) => {
      const stockStatus = material.AvailableStock !== undefined ? 
        `<small style="color: ${material.AvailableStock < 10 ? '#f44336' : '#666'}; display: block; margin-top: 4px;">
          Available: ${material.AvailableStock} ${material.Unit || ''}
        </small>` : '';
      
      return `
        <div class="material-item" style="display: flex; align-items: center; justify-content: space-between; padding: 10px; margin-bottom: 8px; background: #f9f9f9; border-radius: 4px; border: 1px solid #ddd;">
          <div style="flex: 1;">
            <strong>${material.ItemID} - ${material.ItemName}</strong>
            ${stockStatus}
          </div>
          <button type="button" class="remove-material-btn" data-index="${index}" style="background: #f44336; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      `;
    }).join('');
    
    // Attach remove handlers
    materialsList.querySelectorAll('.remove-material-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const index = parseInt(this.dataset.index);
        productMaterials.splice(index, 1);
        renderMaterialsList();
      });
    });
  }
  
  // Add Material Button
  addMaterialBtn?.addEventListener("click", () => {
    materialSelector.style.display = "block";
    addMaterialBtn.style.display = "none";
    if (editMaterialEl) editMaterialEl.value = "";
    const stockInfo = document.getElementById("stockInfo");
    if (stockInfo) stockInfo.textContent = "";
  });
  
  // Update stock info when material is selected
  editMaterialEl?.addEventListener("change", function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption && selectedOption.value) {
      const stock = parseInt(selectedOption.dataset.itemStock) || 0;
      const unit = selectedOption.dataset.itemUnit || '';
      
      const stockInfo = document.getElementById("stockInfo");
      if (stockInfo) {
        if (stock > 0) {
          stockInfo.textContent = `Available stock: ${stock} ${unit}`;
          stockInfo.style.color = stock < 10 ? "#f44336" : "#666";
        } else {
          stockInfo.textContent = "Out of stock!";
          stockInfo.style.color = "#f44336";
        }
      }
    } else {
      const stockInfo = document.getElementById("stockInfo");
      if (stockInfo) stockInfo.textContent = "";
    }
  });
  
  // Cancel Material Button
  cancelMaterialBtn?.addEventListener("click", () => {
    materialSelector.style.display = "none";
    addMaterialBtn.style.display = "inline-block";
    if (editMaterialEl) editMaterialEl.value = "";
    const stockInfo = document.getElementById("stockInfo");
    if (stockInfo) stockInfo.textContent = "";
  });
  
  // Save Material Button
  saveMaterialBtn?.addEventListener("click", () => {
    if (!editMaterialEl || !editMaterialEl.value) {
      showToast("Please select a raw material.", 'warning');
      return;
    }
    
    const selectedOption = editMaterialEl.options[editMaterialEl.selectedIndex];
    const availableStock = parseInt(selectedOption.dataset.itemStock) || 0;
    
    if (availableStock <= 0) {
      showToast("This material is out of stock!", 'warning');
      return;
    }
    
    // Default quantity is 1 per product unit (can be adjusted later if needed)
    const materialData = {
      InventoryItemID: editMaterialEl.value,
      ItemID: selectedOption.dataset.itemId,
      ItemName: selectedOption.dataset.itemName,
      QuantityRequired: 1, // Default to 1, will be deducted automatically when orders are placed
      Unit: selectedOption.dataset.itemUnit || '',
      AvailableStock: availableStock
    };
    
    // Check if material already exists
    const exists = productMaterials.some(m => m.InventoryItemID === materialData.InventoryItemID);
    if (exists) {
      showToast("This material is already added to the product.", 'warning');
      return;
    }
    
    productMaterials.push(materialData);
    renderMaterialsList();
    
    // Reset form
    materialSelector.style.display = "none";
    addMaterialBtn.style.display = "inline-block";
    editMaterialEl.value = "";
    const stockInfo = document.getElementById("stockInfo");
    if (stockInfo) stockInfo.textContent = "";
  });

  // Close popup
  [editCloseBtn, editCancelBtn].forEach(btn =>
    btn?.addEventListener("click", () => {
      editPopup.style.display = "none";
      productBeingEdited = null;
      productMaterials = [];
      materialSelector.style.display = "none";
      addMaterialBtn.style.display = "inline-block";
    })
  );

  // Save changes - For Inventory Officer, only send material
  editSaveBtn?.addEventListener("click", () => {
    if (!productBeingEdited) return;

    const id = productBeingEdited.dataset.id;

    let formData = new FormData();
    
    // For Inventory Officer: Only send materials, everything else is read-only
    if (userRole === 'Inventory Officer') {
      if (productMaterials.length === 0) {
        showToast("Please add at least one raw material to the product.", 'warning');
        return;
      }
      
      // Send all materials as JSON
      formData.append("materials", JSON.stringify(productMaterials));
      // Don't send image, name, category, or price
    } else {
      // For other roles (if needed in future)
      formData.append("name", editNameInput.value);
      formData.append("price", editPriceInput.value);
      formData.append("category", editCategoryEl ? editCategoryEl.value : '');
      formData.append("material", editMaterialEl ? editMaterialEl.value : '');
      
      if (editImageInput && editImageInput.files[0]) {
        formData.append("productImage", editImageInput.files[0]);
      }
    }

    // Add role parameter to identify who is updating
    formData.append("user_role", userRole);

    fetch(base_url + "ProductCon/update_product/" + id, {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === "updated") {
          location.reload();
        } else {
          showToast("Failed to update product: " + (data.message || "Unknown error"), 'error');
        }
      })
      .catch(error => {
        console.error("Error:", error);
        showToast("Error updating product. Please try again.", 'error');
      });
  });
}

// -------------------- INIT --------------------
document.addEventListener("DOMContentLoaded", () => {
  setupSearchFilter();
  setupProductPopups();
});

