document.addEventListener("DOMContentLoaded", () => {
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

    function showConfirmModal(message, onConfirm, onCancel = null) {
        const existingModal = document.getElementById('confirm-modal-overlay');
        if (existingModal) existingModal.remove();
        
        const overlay = document.createElement('div');
        overlay.id = 'confirm-modal-overlay';
        overlay.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); z-index: 10001;
            display: flex; align-items: center; justify-content: center;
            animation: fadeIn 0.2s ease;
        `;
        
        const modal = document.createElement('div');
        modal.style.cssText = `
            background: white; border-radius: 12px; padding: 30px;
            max-width: 450px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease;
        `;
        
        modal.innerHTML = `
            <h3 style="margin: 0 0 15px 0; font-size: 20px; color: #333; font-family: 'Montserrat', sans-serif;">Confirm Action</h3>
            <p style="margin: 0 0 25px 0; color: #666; font-size: 15px; line-height: 1.5;">${message}</p>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button id="confirm-cancel-btn" style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer; font-size: 14px; color: #666; transition: all 0.2s;">Cancel</button>
                <button id="confirm-ok-btn" style="padding: 10px 20px; border: none; background: #dc3545; color: white; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">Confirm</button>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        if (!document.getElementById('modal-styles')) {
            const style = document.createElement('style');
            style.id = 'modal-styles';
            style.textContent = `
                @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
                @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
                #confirm-cancel-btn:hover { background: #f5f5f5; }
                #confirm-ok-btn:hover { background: #c82333; }
            `;
            document.head.appendChild(style);
        }
        
        const cancelBtn = overlay.querySelector('#confirm-cancel-btn');
        const okBtn = overlay.querySelector('#confirm-ok-btn');
        
        cancelBtn.addEventListener('click', () => {
            overlay.style.animation = 'fadeIn 0.2s ease reverse';
            setTimeout(() => overlay.remove(), 200);
            if (onCancel) onCancel();
        });
        
        okBtn.addEventListener('click', () => {
            overlay.style.animation = 'fadeIn 0.2s ease reverse';
            setTimeout(() => overlay.remove(), 200);
            if (onConfirm) onConfirm();
        });
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.animation = 'fadeIn 0.2s ease reverse';
                setTimeout(() => overlay.remove(), 200);
                if (onCancel) onCancel();
            }
        });
        
        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                overlay.style.animation = 'fadeIn 0.2s ease reverse';
                setTimeout(() => overlay.remove(), 200);
                if (onCancel) onCancel();
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
    }

    // --- Elements ---
    const roleTabs = document.querySelectorAll(".filter-tabs .tab-button");
    const searchInput = document.querySelector(".search-input");
    const searchButton = document.querySelector(".search-button");

    const addUserBtn = document.querySelector(".add-user-button");
    const addUserPopup = document.getElementById("addUserPopupOverlay");
    const editPopup = document.getElementById("editPopupOverlay");
    const closeBtns = document.querySelectorAll(".close-btn");
    const cancelBtns = document.querySelectorAll(".cancel-btn");

    let currentFilter = "all"; // store the current tab filter
    let usersData = []; // users array
    let currentEditId = null; // store current editing user ID

    // Base URL
    const baseUrl = window.location.origin + '/Glassify-CI';

    // --- Load users from database ---
    async function loadUsers() {
        try {
            const res = await fetch(baseUrl + '/EmpCon/get_users');
            if (!res.ok) throw new Error('Failed to fetch users');
            usersData = await res.json();
            renderTable();
        } catch (err) {
            console.error("Error loading users:", err);
            showToast("Failed to load employees. Please refresh the page.", 'error');
        }
    }

    // --- Render Table ---
    function renderTable() {
        const tbody = document.querySelector(".table-container table tbody");
        tbody.innerHTML = "";
        
        if (usersData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No employees found</td></tr>';
            return;
        }
        
        usersData.forEach((user) => {
            const tr = document.createElement("tr");
            const fullName = `${user.firstName} ${user.middleName ? user.middleName + ' ' : ''}${user.lastName}`.trim();
            tr.innerHTML = `
                <td>${fullName}</td>
                <td>${user.role}</td>
                <td>${user.email}</td>
                <td><span class="status ${user.status.toLowerCase()}"></span>${user.status}</td>
                <td><i class="fas fa-edit edit-icon" data-id="${user.id}"></i></td>
            `;
            tbody.appendChild(tr);
        });

        // Bind edit icons
        document.querySelectorAll(".edit-icon").forEach((icon) => {
            const userId = parseInt(icon.getAttribute('data-id'));
            icon.addEventListener("click", () => openEditPopup(userId));
        });

        filterRows(); // Apply current filter & search
    }

    // --- Filter/Search ---
    function filterRows() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        document.querySelectorAll(".table-container table tbody tr").forEach((row) => {
            const userId = parseInt(row.querySelector('.edit-icon')?.getAttribute('data-id'));
            if (!userId) {
                row.style.display = "none";
                return;
            }
            
            const user = usersData.find(u => u.id === userId);
            if (!user) {
                row.style.display = "none";
                return;
            }
            
            const fullName = `${user.firstName} ${user.middleName ? user.middleName + ' ' : ''}${user.lastName}`.toLowerCase().trim();
            const matchesTab = currentFilter === "all" || user.role === currentFilter;
            const matchesSearch = fullName.includes(searchTerm) || user.email.toLowerCase().includes(searchTerm);
            row.style.display = (matchesTab && matchesSearch) ? "table-row" : "none";
        });
    }

    // --- Tab click ---
    roleTabs.forEach(tab => {
        tab.addEventListener("click", () => {
            roleTabs.forEach(btn => btn.classList.remove("active"));
            tab.classList.add("active");
            currentFilter = tab.getAttribute("data-filter");
            filterRows();
        });
    });

    // --- Search ---
    searchButton.addEventListener("click", filterRows);
    searchInput.addEventListener("keyup", e => {
        if (e.key === "Enter") filterRows();
    });

    // --- Popups ---
    addUserBtn.addEventListener("click", () => addUserPopup.style.display = "flex");
    closeBtns.forEach(btn => btn.addEventListener("click", closePopups));
    cancelBtns.forEach(btn => btn.addEventListener("click", closePopups));

    function closePopups() {
        addUserPopup.style.display = "none";
        editPopup.style.display = "none";
    }

    // --- Open Edit Popup ---
    function openEditPopup(userId) {
        const user = usersData.find(u => u.id === userId);
        if (!user) {
            showToast("User not found", 'error');
            return;
        }
        
        currentEditId = userId;
        
        // Show popup first
        editPopup.style.display = "flex";
        
        // Wait a tiny bit for DOM to be ready, then populate fields
        setTimeout(() => {
            // Find form inputs in edit popup - use querySelector within popup context
            const form = editPopup.querySelector('form');
            if (!form) {
                console.error('Form not found in edit popup');
                return;
            }
            
            // Set values for separate name fields - access from within popup context
            const firstNameInput = editPopup.querySelector('#edit-first-name');
            const middleNameInput = editPopup.querySelector('#edit-middle-name');
            const lastNameInput = editPopup.querySelector('#edit-last-name');
            const emailInput = editPopup.querySelector('#edit-email');
            const roleSelect = form.querySelector('select');
            
            // Clear all fields first
            if (firstNameInput) firstNameInput.value = '';
            if (middleNameInput) middleNameInput.value = '';
            if (lastNameInput) lastNameInput.value = '';
            if (emailInput) emailInput.value = '';
            
            // Now populate with correct data
            if (firstNameInput) firstNameInput.value = (user.firstName || '').trim();
            if (middleNameInput) middleNameInput.value = (user.middleName || '').trim();
            if (lastNameInput) lastNameInput.value = (user.lastName || '').trim();
            if (emailInput) emailInput.value = (user.email || '').trim();
            if (roleSelect) {
                roleSelect.value = user.role || '';
            }
            
            // Clear password fields
            const passwordInputs = form.querySelectorAll('input[type="password"]');
            passwordInputs.forEach(input => input.value = '');
        }, 10);
    }

    // --- Add User ---
    document.querySelector("#addUserPopupOverlay .save-btn").addEventListener("click", async () => {
        const popup = addUserPopup;
        const form = popup.querySelector('form');
        const inputs = form.querySelectorAll('input');
        const selects = form.querySelectorAll('select');
        
        // Get form values - assuming order: Full Name, Email, Phone, Role, Password, Confirm Password
        const fullName = inputs[0].value.trim();
        const email = inputs[1].value.trim();
        const phone = inputs[2] ? inputs[2].value.trim() : '';
        const role = selects[0].value;
        const password = inputs[inputs.length - 2] ? inputs[inputs.length - 2].value : '';
        const confirmPassword = inputs[inputs.length - 1] ? inputs[inputs.length - 1].value : '';

        if (!fullName || !email || !role || !password) {
            showToast("Please fill all required fields!", 'warning');
            return;
        }
        
        if (password !== confirmPassword) {
            showToast("Passwords do not match!", 'warning');
            return;
        }
        
        if (password.length < 6) {
            showToast("Password must be at least 6 characters long!", 'warning');
            return;
        }
        
        // Split full name into first, middle, last
        const nameParts = fullName.split(' ');
        const firstName = nameParts[0] || '';
        const lastName = nameParts[nameParts.length - 1] || '';
        const middleName = nameParts.length > 2 ? nameParts.slice(1, -1).join(' ') : '';

        const userData = {
            firstName: firstName,
            lastName: lastName,
            middleName: middleName,
            email: email,
            phone: phone,
            role: role,
            password: password,
            status: "Active"
        };

        try {
            const res = await fetch(baseUrl + '/EmpCon/create_user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });
            
            const result = await res.json();
            
            if (result.status === 'success') {
                showToast("Employee created successfully!", 'success');
                closePopups();
                await loadUsers(); // Reload users
            } else {
                showToast(result.message || "Failed to create employee", 'error');
            }
        } catch (err) {
            console.error("Error creating user:", err);
            showToast("Failed to create employee. Please try again.", 'error');
        }
    });

    // --- Save Edit ---
    document.querySelector("#editPopupOverlay .save-btn").addEventListener("click", async () => {
        if (!currentEditId) return;
        
        const user = usersData.find(u => u.id === currentEditId);
        if (!user) {
            showToast("User not found", 'error');
            return;
        }
        
        const popup = editPopup;
        const form = popup.querySelector('form');
        const selects = form.querySelectorAll('select');
        
        // Get form values from separate name fields
        const firstName = document.getElementById('edit-first-name')?.value.trim() || '';
        const middleName = document.getElementById('edit-middle-name')?.value.trim() || '';
        const lastName = document.getElementById('edit-last-name')?.value.trim() || '';
        const email = document.getElementById('edit-email')?.value.trim() || '';
        const role = selects[0].value;
        
        // Get password fields
        const passwordInputs = form.querySelectorAll('input[type="password"]');
        const password = passwordInputs[0] ? passwordInputs[0].value : '';
        const confirmPassword = passwordInputs[1] ? passwordInputs[1].value : '';
        
        if (!firstName || !lastName || !email || !role) {
            showToast("Please fill all required fields (First Name, Last Name, Email, and Role)!", 'warning');
            return;
        }
        
        if (password && password !== confirmPassword) {
            showToast("Passwords do not match!", 'warning');
            return;
        }
        
        if (password && password.length < 6) {
            showToast("Password must be at least 6 characters long!", 'warning');
            return;
        }
        
        const updateData = {
            id: currentEditId,
            firstName: firstName,
            lastName: lastName,
            middleName: middleName,
            email: email,
            role: role
        };
        
        if (password) {
            updateData.password = password;
        }
        
        try {
            const res = await fetch(baseUrl + '/EmpCon/update_user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updateData)
            });
            
            const result = await res.json();
            
            if (result.status === 'success') {
                showToast("Employee updated successfully!", 'success');
                closePopups();
                await loadUsers(); // Reload users
            } else {
                showToast(result.message || "Failed to update employee", 'error');
            }
        } catch (err) {
            console.error("Error updating user:", err);
            showToast("Failed to update employee. Please try again.", 'error');
        }
    });

    // --- Delete User ---
    document.querySelector("#editPopupOverlay .delete-btn").addEventListener("click", async () => {
        if (!currentEditId) return;
        
        const user = usersData.find(u => u.id === currentEditId);
        if (!user) {
            showToast("User not found", 'error');
            return;
        }
        
        showConfirmModal(`Are you sure you want to deactivate ${user.firstName} ${user.lastName}?`, async () => {
            try {
                const res = await fetch(baseUrl + '/EmpCon/delete_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: currentEditId })
                });
                
                const result = await res.json();
                
                if (result.status === 'success') {
                    showToast("Employee deactivated successfully!", 'success');
                    closePopups();
                    await loadUsers(); // Reload users
                } else {
                    showToast(result.message || "Failed to deactivate employee", 'error');
                }
            } catch (err) {
                console.error("Error deleting user:", err);
                showToast("Failed to deactivate employee. Please try again.", 'error');
            }
        });
    });

    // --- Initialize ---
    loadUsers();
});
