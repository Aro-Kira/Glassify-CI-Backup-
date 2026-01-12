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

// --- FETCH USERS ---
function loadUsers() {
    fetch(getUsersUrl) // URL from PHP
        .then(res => res.json())
        .then(data => {
            users = data;
            renderTable();
        })
        .catch(err => console.error("Failed to load users:", err));
}

// --- RENDER TABLE ---
function renderTable() {
    const tbody = document.querySelector("table tbody");
    tbody.innerHTML = "";
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No customers found</td></tr>';
        return;
    }

    users.forEach(user => {
        const tr = document.createElement("tr");
        tr.dataset.id = user.id;
        const fullName = `${user.firstName} ${user.middleInitial ? user.middleInitial + ' ' : ''}${user.lastName}`.trim();
        tr.innerHTML = `
            <td></td>
            <td>${fullName}</td>
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
}

// --- EDIT USER ---
function openEdit(id) {
    const user = users.find(u => u.id == id);
    if (!user) return;

    currentEditingRow = user;

    document.getElementById("edit-id").value = user.id;
    document.getElementById("edit-firstName").value = user.firstName;
    document.getElementById("edit-middleInitial").value = user.middleInitial;
    document.getElementById("edit-lastName").value = user.lastName;
    document.getElementById("edit-email").value = user.email;
    document.getElementById("edit-phone").value = user.phone;

    document.getElementById("popupOverlay").style.display = "flex";
}

function closePopup() {
    currentEditingRow = null;
    document.getElementById("popupOverlay").style.display = "none";
}

function saveEdit() {
    if (!currentEditingRow) return;

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
                showToast("User deactivated successfully!", 'success');
                closeDeletePopup();
                loadUsers();
            } else {
                showToast(res.message || "Failed to deactivate user.", 'error');
            }
        })
        .catch(err => {
            console.error("Failed to delete user:", err);
            showToast("Failed to deactivate user. Please try again.", 'error');
        });
});

// --- DELETE FROM EDIT POPUP ---
function deleteEditUser() {
    if (!currentEditingRow) return;
    openDelete(currentEditingRow.id);
    closePopup();
}

// --- SEARCH FUNCTION ---
document.querySelector(".search-button").addEventListener("click", searchUsers);
document.querySelector(".search-input").addEventListener("keyup", (e) => {
    if (e.key === "Enter") searchUsers();
});

function searchUsers() {
    const query = document.querySelector(".search-input").value.toLowerCase();
    const filtered = users.filter(u =>
        `${u.firstName} ${u.middleInitial} ${u.lastName}`.toLowerCase().includes(query) ||
        u.email.toLowerCase().includes(query)
    );
    users = filtered;
    renderTable();
}

// --- INITIAL LOAD ---
document.addEventListener("DOMContentLoaded", loadUsers);
