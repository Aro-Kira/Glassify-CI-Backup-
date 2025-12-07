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
        alert("Please fill all required fields!");
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
                alert("User updated successfully!");
                closePopup();
                loadUsers();
            } else {
                alert(res.message || "Failed to save user.");
            }
        })
        .catch(err => {
            console.error("Failed to update user:", err);
            alert("Failed to update user. Please try again.");
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
                alert("User deactivated successfully!");
                closeDeletePopup();
                loadUsers();
            } else {
                alert(res.message || "Failed to deactivate user.");
            }
        })
        .catch(err => {
            console.error("Failed to delete user:", err);
            alert("Failed to deactivate user. Please try again.");
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
