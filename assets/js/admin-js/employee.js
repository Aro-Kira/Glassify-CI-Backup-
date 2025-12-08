document.addEventListener("DOMContentLoaded", () => {
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
            alert("Failed to load employees. Please refresh the page.");
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
            alert("User not found");
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
            alert("Please fill all required fields!");
            return;
        }
        
        if (password !== confirmPassword) {
            alert("Passwords do not match!");
            return;
        }
        
        if (password.length < 6) {
            alert("Password must be at least 6 characters long!");
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
                alert("Employee created successfully!");
                closePopups();
                await loadUsers(); // Reload users
            } else {
                alert(result.message || "Failed to create employee");
            }
        } catch (err) {
            console.error("Error creating user:", err);
            alert("Failed to create employee. Please try again.");
        }
    });

    // --- Save Edit ---
    document.querySelector("#editPopupOverlay .save-btn").addEventListener("click", async () => {
        if (!currentEditId) return;
        
        const user = usersData.find(u => u.id === currentEditId);
        if (!user) {
            alert("User not found");
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
            alert("Please fill all required fields (First Name, Last Name, Email, and Role)!");
            return;
        }
        
        if (password && password !== confirmPassword) {
            alert("Passwords do not match!");
            return;
        }
        
        if (password && password.length < 6) {
            alert("Password must be at least 6 characters long!");
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
                alert("Employee updated successfully!");
                closePopups();
                await loadUsers(); // Reload users
            } else {
                alert(result.message || "Failed to update employee");
            }
        } catch (err) {
            console.error("Error updating user:", err);
            alert("Failed to update employee. Please try again.");
        }
    });

    // --- Delete User ---
    document.querySelector("#editPopupOverlay .delete-btn").addEventListener("click", async () => {
        if (!currentEditId) return;
        
        const user = usersData.find(u => u.id === currentEditId);
        if (!user) {
            alert("User not found");
            return;
        }
        
        if (!confirm(`Are you sure you want to deactivate ${user.firstName} ${user.lastName}?`)) return;
        
        try {
            const res = await fetch(baseUrl + '/EmpCon/delete_user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: currentEditId })
            });
            
            const result = await res.json();
            
            if (result.status === 'success') {
                alert("Employee deactivated successfully!");
                closePopups();
                await loadUsers(); // Reload users
            } else {
                alert(result.message || "Failed to deactivate employee");
            }
        } catch (err) {
            console.error("Error deleting user:", err);
            alert("Failed to deactivate employee. Please try again.");
        }
    });

    // --- Initialize ---
    loadUsers();
});
