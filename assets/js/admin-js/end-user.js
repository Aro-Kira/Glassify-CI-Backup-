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

let users = [];
let currentEditingRow = null;
let rowToDelete = null;
let originalValues = {}; // Store original values when opening edit popup
let currentPage = 1;
const itemsPerPage = 10; // Increased from 4 to 10 for better usability

// --- FETCH USERS ---
function loadUsers() {
    console.log('Loading users from:', getUsersUrl);
    return fetch(getUsersUrl) // URL from PHP
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            console.log(`Loaded ${data.length} users from database:`, data);
            users = data;
            currentPage = 1; // Reset to first page when loading new data
            renderTable();
        })
        .catch(err => {
            console.error("Failed to load users:", err);
            showToast("Failed to load users. Please refresh the page.", 'error');
        });
}

// --- RENDER TABLE ---
function renderTable() {
    const tbody = document.querySelector("table tbody");
    tbody.innerHTML = "";
    
    // Apply search filter if active
    const searchQuery = document.querySelector(".search-input")?.value.toLowerCase().trim() || '';
    let filteredUsers = users;
    
    if (searchQuery) {
        filteredUsers = users.filter(user => {
            const fullName = `${user.firstName} ${user.middleInitial ? user.middleInitial + ' ' : ''}${user.lastName}`.toLowerCase();
            return fullName.includes(searchQuery) || user.email.toLowerCase().includes(searchQuery);
        });
    }
    
    if (filteredUsers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No customers found</td></tr>';
        updatePaginationInfo(0, 0, 0);
        return;
    }

    // Calculate pagination
    const totalUsers = filteredUsers.length;
    const totalPages = Math.ceil(totalUsers / itemsPerPage);
    
    // Ensure currentPage is valid
    if (currentPage > totalPages) {
        currentPage = totalPages || 1;
    }
    
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalUsers);
    const paginatedUsers = filteredUsers.slice(startIndex, endIndex);
    
    // Render paginated users
    paginatedUsers.forEach((user, index) => {
        const tr = document.createElement("tr");
        tr.dataset.id = user.id;
        const fullName = `${user.firstName} ${user.middleInitial ? user.middleInitial + ' ' : ''}${user.lastName}`.trim();
        const roleDisplay = user.roleDisplay || '';
        tr.innerHTML = `
            <td>${startIndex + index + 1}</td>
            <td>${fullName}</td>
            <td>${roleDisplay}</td>
            <td>${user.email}</td>
            <td>${user.joinedDate}</td>
            <td>${user.lastActive}</td>
            <td>
            <button class="edit-btn" onclick="openEdit(${user.id})">
            <i class="fa fa-edit"></i> Edit
            </button>
            <button class="delete-btn" onclick="openDelete(${user.id})">
            <i class="fa fa-trash"></i> Delete
            </button>
            </td>

        `;
        tbody.appendChild(tr);
    });
    
    // Update pagination info
    updatePaginationInfo(startIndex + 1, endIndex, totalUsers);
    
    // Render pagination controls
    renderPaginationControls(currentPage, totalPages);
}

// --- RENDER PAGINATION CONTROLS ---
function renderPaginationControls(current, total) {
    const container = document.querySelector(".pagination-controls");
    if (!container) {
        console.warn('Pagination controls container not found in DOM');
        return;
    }
    
    container.innerHTML = '';
    
    console.log(`Pagination: page ${current} of ${total} (${users.length} total users, ${itemsPerPage} per page)`);
    
    if (total <= 1) {
        // No pagination needed for single page, but show a message for clarity
        console.log('Only one page of results - pagination controls hidden');
        return;
    }
    
    // Create a wrapper for better styling
    const wrapper = document.createElement('div');
    wrapper.style.display = 'flex';
    wrapper.style.alignItems = 'center';
    wrapper.style.gap = '8px';
    
    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.textContent = '← Previous';
    prevBtn.className = 'pagination-btn pagination-prev';
    prevBtn.disabled = current === 1;
    prevBtn.onclick = () => changePage(current - 1);
    if (current === 1) {
        prevBtn.style.opacity = '0.5';
        prevBtn.style.cursor = 'not-allowed';
    }
    wrapper.appendChild(prevBtn);
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, current - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(total, startPage + maxVisiblePages - 1);
    
    // Adjust start if we're near the end
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // First page + ellipsis
    if (startPage > 1) {
        const firstBtn = document.createElement('button');
        firstBtn.textContent = '1';
        firstBtn.className = 'pagination-btn';
        firstBtn.onclick = () => changePage(1);
        wrapper.appendChild(firstBtn);
        
        if (startPage > 2) {
            const ellipsis = document.createElement('span');
            ellipsis.textContent = '...';
            ellipsis.style.padding = '0 8px';
            ellipsis.style.color = '#666';
            wrapper.appendChild(ellipsis);
        }
    }
    
    // Page number buttons
    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        pageBtn.className = 'pagination-btn';
        if (i === current) {
            pageBtn.classList.add('active');
            pageBtn.style.backgroundColor = '#0f2b46';
            pageBtn.style.color = 'white';
        }
        pageBtn.onclick = () => changePage(i);
        wrapper.appendChild(pageBtn);
    }
    
    // Ellipsis + last page
    if (endPage < total) {
        if (endPage < total - 1) {
            const ellipsis = document.createElement('span');
            ellipsis.textContent = '...';
            ellipsis.style.padding = '0 8px';
            ellipsis.style.color = '#666';
            wrapper.appendChild(ellipsis);
        }
        
        const lastBtn = document.createElement('button');
        lastBtn.textContent = total;
        lastBtn.className = 'pagination-btn';
        lastBtn.onclick = () => changePage(total);
        wrapper.appendChild(lastBtn);
    }
    
    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Next →';
    nextBtn.className = 'pagination-btn pagination-next';
    nextBtn.disabled = current === total;
    nextBtn.onclick = () => changePage(current + 1);
    if (current === total) {
        nextBtn.style.opacity = '0.5';
        nextBtn.style.cursor = 'not-allowed';
    }
    wrapper.appendChild(nextBtn);
    
    // Append wrapper to container
    container.appendChild(wrapper);
}

// --- UPDATE PAGINATION INFO ---
function updatePaginationInfo(start, end, total) {
    const paginationSpan = document.querySelector(".pagination .showing-info");
    if (paginationSpan) {
        if (total === 0) {
            paginationSpan.textContent = "Showing 0 of 0 end users";
        } else {
            paginationSpan.textContent = `Showing ${start}-${end} of ${total} end users`;
        }
    }
}

// --- CHANGE PAGE ---
function changePage(page) {
    console.log(`changePage called: requesting page ${page}, current page is ${currentPage}`);
    
    const searchQuery = document.querySelector(".search-input")?.value.toLowerCase().trim() || '';
    let filteredUsers = users;
    
    if (searchQuery) {
        filteredUsers = users.filter(user => {
            const fullName = `${user.firstName} ${user.middleInitial ? user.middleInitial + ' ' : ''}${user.lastName}`.toLowerCase();
            return fullName.includes(searchQuery) || user.email.toLowerCase().includes(searchQuery);
        });
    }
    
    const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
    console.log(`Total pages: ${totalPages}, filtered users: ${filteredUsers.length}`);
    
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderTable();
        
        // Smooth scroll to top of table
        const tableContainer = document.querySelector('.table-container');
        if (tableContainer) {
            tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else {
        console.warn(`Invalid page number: ${page} (valid range: 1-${totalPages})`);
    }
}

// --- EDIT USER ---
function openEdit(id) {
    const user = users.find(u => u.id == id);
    if (!user) return;

    currentEditingRow = user;

    document.getElementById("edit-id").value = user.id;
    document.getElementById("edit-firstName").value = user.firstName || '';
    document.getElementById("edit-middleInitial").value = user.middleInitial || '';
    document.getElementById("edit-lastName").value = user.lastName || '';
    document.getElementById("edit-email").value = user.email || '';
    document.getElementById("edit-phone").value = user.phone || '';

    // Store original values
    originalValues = {
        firstName: user.firstName || '',
        middleInitial: user.middleInitial || '',
        lastName: user.lastName || '',
        email: user.email || '',
        phone: user.phone || ''
    };

    // Initially disable save button
    const saveBtn = document.querySelector("#editForm .save-btn");
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.style.opacity = '0.5';
        saveBtn.style.cursor = 'not-allowed';
    }
    
    document.getElementById("popupOverlay").style.display = "flex";
    
    // Check for changes and enable/disable save button
    checkForChanges();
    
    // Add event listeners to all input fields
    const inputs = document.querySelectorAll("#editForm input[type='text'], #editForm input[type='email']");
    inputs.forEach(input => {
        input.addEventListener('input', checkForChanges);
    });
}

// --- CHECK FOR CHANGES ---
function checkForChanges() {
    const saveBtn = document.querySelector("#editForm .save-btn");
    if (!saveBtn) return;
    
    const currentValues = {
        firstName: document.getElementById("edit-firstName").value.trim(),
        middleInitial: document.getElementById("edit-middleInitial").value.trim(),
        lastName: document.getElementById("edit-lastName").value.trim(),
        email: document.getElementById("edit-email").value.trim(),
        phone: document.getElementById("edit-phone").value.trim()
    };
    
    // Check if any value has changed
    const hasChanges = 
        currentValues.firstName !== originalValues.firstName ||
        currentValues.middleInitial !== originalValues.middleInitial ||
        currentValues.lastName !== originalValues.lastName ||
        currentValues.email !== originalValues.email ||
        currentValues.phone !== originalValues.phone;
    
    // Enable/disable save button
    saveBtn.disabled = !hasChanges;
    if (hasChanges) {
        saveBtn.style.opacity = '1';
        saveBtn.style.cursor = 'pointer';
    } else {
        saveBtn.style.opacity = '0.5';
        saveBtn.style.cursor = 'not-allowed';
    }
}

function closePopup() {
    currentEditingRow = null;
    originalValues = {};
    
    // Remove event listeners
    const inputs = document.querySelectorAll("#editForm input[type='text'], #editForm input[type='email']");
    inputs.forEach(input => {
        input.removeEventListener('input', checkForChanges);
    });
    
    document.getElementById("popupOverlay").style.display = "none";
}

function saveEdit() {
    if (!currentEditingRow) return;
    
    // Check if save button is disabled (no changes)
    const saveBtn = document.querySelector("#editForm .save-btn");
    if (saveBtn && saveBtn.disabled) {
        return;
    }

    const updatedUser = {
        id: currentEditingRow.id,
        firstName: document.getElementById("edit-firstName").value.trim(),
        middleInitial: document.getElementById("edit-middleInitial").value.trim(),
        lastName: document.getElementById("edit-lastName").value.trim(),
        email: document.getElementById("edit-email").value.trim(),
        phone: document.getElementById("edit-phone").value.trim()
    };
    
    // Validation
    if (!updatedUser.firstName || !updatedUser.lastName || !updatedUser.email) {
        showToast("Please fill all required fields!", 'warning');
        return;
    }

    fetch(updateUserUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(updatedUser)
    })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                showToast("User updated successfully!", 'success');
                closePopup();
                loadUsers();
            } else {
                showToast(res.message || "Failed to save user.", 'error');
            }
        })
        .catch(err => {
            console.error("Failed to update user:", err);
            showToast("Failed to update user. Please try again.", 'error');
        });
}

// --- DELETE USER ---
function openDelete(id) {
    rowToDelete = id;
    document.getElementById("popup-delete").style.display = "flex";
}

function closeDeletePopup() {
    rowToDelete = null;
    document.getElementById("popup-delete").style.display = "none";
}

document.querySelector(".popup-delete-confirm").addEventListener("click", () => {
    if (!rowToDelete) return;

    fetch(deleteUserUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: rowToDelete })
    })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                showToast("User deleted and archived successfully!", 'success');
                closeDeletePopup();
                loadUsers().then(() => {
                    renderTable();
                });
            } else {
                showToast(res.message || "Failed to delete user.", 'error');
            }
        })
        .catch(err => {
            console.error("Failed to delete user:", err);
            showToast("Failed to delete user. Please try again.", 'error');
        });
});

// --- DELETE FROM EDIT POPUP ---
function deleteEditUser() {
    if (!currentEditingRow) return;
    openDelete(currentEditingRow.id);
    closePopup();
}

// --- SEARCH FUNCTION ---
const searchButton = document.querySelector(".search-button");
const searchInput = document.querySelector(".search-input");

if (searchButton) {
    searchButton.addEventListener("click", () => {
        currentPage = 1; // Reset to first page on search
        renderTable();
    });
}

if (searchInput) {
    searchInput.addEventListener("keyup", (e) => {
        if (e.key === "Enter") {
            currentPage = 1; // Reset to first page on search
            renderTable();
        } else {
            // Real-time search (optional)
            currentPage = 1;
            renderTable();
        }
    });
}

// --- INITIAL LOAD ---
document.addEventListener("DOMContentLoaded", loadUsers);
