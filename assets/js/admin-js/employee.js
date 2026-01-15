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
    let originalEditValues = {}; // Store original values when opening edit popup
    let currentPage = 1; // Current page for pagination
    const itemsPerPage = 4; // Items per page

    // Base URL
    const baseUrl = window.location.origin + '/Glassify-CI';

    // --- Load users from database ---
    async function loadUsers() {
        try {
            const res = await fetch(baseUrl + '/EmpCon/get_users');
            if (!res.ok) throw new Error('Failed to fetch users');
            usersData = await res.json();
            currentPage = 1; // Reset to first page when loading new data
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
        
        // Apply filters first
        const searchTerm = searchInput.value.toLowerCase().trim();
        let filteredUsers = usersData.filter((user) => {
            const fullName = `${user.firstName} ${user.middleName ? user.middleName + ' ' : ''}${user.lastName}`.toLowerCase().trim();
            const matchesTab = currentFilter === "all" || user.role === currentFilter;
            const matchesSearch = fullName.includes(searchTerm) || user.email.toLowerCase().includes(searchTerm);
            return matchesTab && matchesSearch;
        });
        
        if (filteredUsers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No employees found</td></tr>';
            updatePaginationInfo(0, 0, 0);
            renderPaginationControls(0);
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

        // Update pagination info
        updatePaginationInfo(startIndex + 1, endIndex, totalUsers);
        renderPaginationControls(totalPages);
    }

    // --- Update Pagination Info ---
    function updatePaginationInfo(start, end, total) {
        const paginationSpan = document.querySelector(".pagination .showing-info");
        if (paginationSpan) {
            if (total === 0) {
                paginationSpan.textContent = "Showing 0 of 0 employees";
            } else {
                paginationSpan.textContent = `Showing ${start}-${end} of ${total} employees`;
            }
        }
    }

    // --- Render Pagination Controls ---
    function renderPaginationControls(totalPages) {
        const controlsContainer = document.querySelector(".pagination-controls");
        if (!controlsContainer) return;
        
        controlsContainer.innerHTML = "";
        
        if (totalPages <= 1) {
            return; // No pagination needed if 1 page or less
        }
        
        // Previous button
        const prevBtn = document.createElement("button");
        prevBtn.className = "page-btn";
        prevBtn.innerHTML = "‹";
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => changePage(currentPage - 1);
        controlsContainer.appendChild(prevBtn);
        
        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        if (startPage > 1) {
            const firstPage = document.createElement("span");
            firstPage.className = "page-number";
            firstPage.textContent = "1";
            firstPage.onclick = () => changePage(1);
            controlsContainer.appendChild(firstPage);
            
            if (startPage > 2) {
                const dots = document.createElement("span");
                dots.className = "dots";
                dots.textContent = "...";
                controlsContainer.appendChild(dots);
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const pageNum = document.createElement("span");
            pageNum.className = `page-number ${i === currentPage ? 'active' : ''}`;
            pageNum.textContent = i;
            pageNum.onclick = () => changePage(i);
            controlsContainer.appendChild(pageNum);
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const dots = document.createElement("span");
                dots.className = "dots";
                dots.textContent = "...";
                controlsContainer.appendChild(dots);
            }
            
            const lastPage = document.createElement("span");
            lastPage.className = "page-number";
            lastPage.textContent = totalPages;
            lastPage.onclick = () => changePage(totalPages);
            controlsContainer.appendChild(lastPage);
        }
        
        // Next button
        const nextBtn = document.createElement("button");
        nextBtn.className = "page-btn";
        nextBtn.innerHTML = "›";
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => changePage(currentPage + 1);
        controlsContainer.appendChild(nextBtn);
    }

    // --- Change Page ---
    function changePage(page) {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let filteredUsers = usersData.filter((user) => {
            const fullName = `${user.firstName} ${user.middleName ? user.middleName + ' ' : ''}${user.lastName}`.toLowerCase().trim();
            const matchesTab = currentFilter === "all" || user.role === currentFilter;
            const matchesSearch = fullName.includes(searchTerm) || user.email.toLowerCase().includes(searchTerm);
            return matchesTab && matchesSearch;
        });
        
        const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
        
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderTable();
        }
    }

    // --- Tab click ---
    roleTabs.forEach(tab => {
        tab.addEventListener("click", () => {
            roleTabs.forEach(btn => btn.classList.remove("active"));
            tab.classList.add("active");
            currentFilter = tab.getAttribute("data-filter");
            currentPage = 1; // Reset to first page on filter change
            renderTable();
        });
    });

    // --- Search ---
    searchButton.addEventListener("click", () => {
        currentPage = 1; // Reset to first page on search
        renderTable();
    });
    searchInput.addEventListener("keyup", e => {
        if (e.key === "Enter") {
            currentPage = 1; // Reset to first page on search
            renderTable();
        }
    });

    // --- Popups ---
    addUserBtn.addEventListener("click", () => addUserPopup.style.display = "flex");
    closeBtns.forEach(btn => btn.addEventListener("click", closePopups));
    cancelBtns.forEach(btn => btn.addEventListener("click", closePopups));

    function closePopups() {
        addUserPopup.style.display = "none";
        editPopup.style.display = "none";
        originalEditValues = {};
        currentEditId = null;
        
        // Remove event listeners from edit popup
        const form = editPopup.querySelector('form');
        if (form) {
            const inputs = form.querySelectorAll('input[type="text"], input[type="email"], select');
            inputs.forEach(input => {
                input.removeEventListener('input', checkEditChanges);
                input.removeEventListener('change', checkEditChanges);
            });
        }
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
            const firstName = (user.firstName || '').trim();
            const middleName = (user.middleName || '').trim();
            const lastName = (user.lastName || '').trim();
            const email = (user.email || '').trim();
            const role = user.role || '';
            
            if (firstNameInput) firstNameInput.value = firstName;
            if (middleNameInput) middleNameInput.value = middleName;
            if (lastNameInput) lastNameInput.value = lastName;
            if (emailInput) emailInput.value = email;
            if (roleSelect) {
                roleSelect.value = role;
            }
            
            // Store original values
            originalEditValues = {
                firstName: firstName,
                middleName: middleName,
                lastName: lastName,
                email: email,
                role: role
            };
            
            // Clear password fields
            const passwordInputs = form.querySelectorAll('input[type="password"]');
            passwordInputs.forEach(input => input.value = '');
            
            // Initially disable save button
            const saveBtn = form.querySelector('.save-btn');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.style.opacity = '0.5';
                saveBtn.style.cursor = 'not-allowed';
            }
            
            // Check for changes and enable/disable save button
            checkEditChanges();
            
            // Add event listeners to all input fields
            const inputs = form.querySelectorAll('input[type="text"], input[type="email"], select');
            inputs.forEach(input => {
                input.addEventListener('input', checkEditChanges);
                input.addEventListener('change', checkEditChanges);
            });
        }, 10);
    }

    // --- CHECK FOR CHANGES IN EDIT POPUP ---
    function checkEditChanges() {
        const saveBtn = editPopup.querySelector('.save-btn');
        if (!saveBtn) return;
        
        const firstNameInput = editPopup.querySelector('#edit-first-name');
        const middleNameInput = editPopup.querySelector('#edit-middle-name');
        const lastNameInput = editPopup.querySelector('#edit-last-name');
        const emailInput = editPopup.querySelector('#edit-email');
        const roleSelect = editPopup.querySelector('form select');
        
        if (!firstNameInput || !lastNameInput || !emailInput || !roleSelect) return;
        
        const currentValues = {
            firstName: firstNameInput.value.trim(),
            middleName: middleNameInput.value.trim(),
            lastName: lastNameInput.value.trim(),
            email: emailInput.value.trim(),
            role: roleSelect.value
        };
        
        // Check if any value has changed
        const hasChanges = 
            currentValues.firstName !== originalEditValues.firstName ||
            currentValues.middleName !== originalEditValues.middleName ||
            currentValues.lastName !== originalEditValues.lastName ||
            currentValues.email !== originalEditValues.email ||
            currentValues.role !== originalEditValues.role;
        
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
        
        // Check if save button is disabled (no changes)
        const saveBtn = editPopup.querySelector('.save-btn');
        if (saveBtn && saveBtn.disabled) {
            return;
        }
        
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
        
        showConfirmModal(`Are you sure you want to delete ${user.firstName} ${user.lastName}? This will archive the employee.`, async () => {
            try {
                const res = await fetch(baseUrl + '/EmpCon/delete_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: currentEditId })
                });
                
                const result = await res.json();
                
                if (result.status === 'success') {
                    showToast("Employee deleted and archived successfully!", 'success');
                    closePopups();
                    await loadUsers(); // Reload users
                } else {
                    showToast(result.message || "Failed to delete employee", 'error');
                }
            } catch (err) {
                console.error("Error deleting user:", err);
                showToast("Failed to delete employee. Please try again.", 'error');
            }
        });
    });

    // --- Initialize ---
    loadUsers();
});
