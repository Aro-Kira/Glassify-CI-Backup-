// =====================================================
// PRODUCTS.JS
// =====================================================

// -------------------- IMAGE PREVIEW --------------------
function setupImagePreview(inputElem, previewElem, placeholder) {
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
        alert("Failed to delete product.");
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
  const placeholderImg = "https://cdn-icons-png.flaticon.com/512/4211/4211763.png";

  const editPopup = document.getElementById("editPopup");
  const editCloseBtn = document.getElementById("closeEditPopup");
  const editCancelBtn = document.getElementById("cancelEdit");
  const editSaveBtn = document.getElementById("editSaveBtn");
  const editImageInput = document.getElementById("editProductImageInput");
  const editImagePreview = editPopup?.querySelector(".image-preview img");
  const editNameInput = document.getElementById("editProductName");
  const editPriceInput = document.getElementById("editProductPrice");

  let productBeingEdited = null;

  // ---------- ADD PRODUCT ----------
  addBtn?.addEventListener("click", () => (addPopup.style.display = "flex"));

  [addCloseBtn, addCancelBtn].forEach(btn =>
    btn?.addEventListener("click", () => {
      addPopup.style.display = "none";
      addNameInput.value = "";
      addPriceInput.value = "";
      addImageInput.value = "";
      addImagePreview.src = placeholderImg;
    })
  );

  setupImagePreview(addImageInput, addImagePreview, placeholderImg);

  addSaveBtn?.addEventListener("click", () => {
    let name = addNameInput.value.trim();
    let categoryEl = document.getElementById("productCategory");
    let materialEl = document.getElementById("productMaterial");
    let category = categoryEl ? categoryEl.value : '';
    let material = materialEl ? materialEl.value : '';
    let price = addPriceInput.value;
    let img = addImageInput.files[0];

    if (!name || !category || !material || !price)
      return alert("Please complete all fields.");

    let formData = new FormData();
    formData.append("name", name);
    formData.append("category", category);
    formData.append("material", material);
    formData.append("price", price);
    if (img) formData.append("productImage", img);

    fetch(base_url + "ProductCon/add_product", { method: "POST", body: formData })
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          location.reload();
        } else {
          alert("Error saving product.");
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
    editImagePreview.src = productBeingEdited.querySelector(".product-image img").src;

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
  })
);

setupImagePreview(editImageInput, editImagePreview, placeholderImg);

// Save changes
editSaveBtn?.addEventListener("click", () => {
  if (!productBeingEdited) return;

  const id = productBeingEdited.dataset.id;

  let formData = new FormData();
  formData.append("name", editNameInput.value);
  formData.append("price", editPriceInput.value);
  const editCategoryEl = document.getElementById("editProductCategory");
  const editMaterialEl = document.getElementById("editProductMaterial");
  formData.append("category", editCategoryEl ? editCategoryEl.value : '');
  // Admin CANNOT edit materials - do not send material field
  // formData.append("material", editMaterialEl ? editMaterialEl.value : '');
  
  // Add role parameter
  formData.append("user_role", "Admin");

  if (editImageInput.files[0]) {
    formData.append("productImage", editImageInput.files[0]);
  }

  fetch(base_url + "ProductCon/update_product/" + id, {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "updated") {
        location.reload();
      } else {
        alert("Failed to update product.");
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
