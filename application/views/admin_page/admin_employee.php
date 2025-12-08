<script src="<?php echo base_url('assets/js/admin-js/employee.js'); ?>"></script>


<section class="employees-section">
    <h1 class="page-title">Employees</h1>

    <div class="controls-container">
        <div class="search-bar">
            <input type="text" placeholder="Search user..." class="search-input">
            <button class="search-button">Search</button>
        </div>
        <button class="add-user-button">+ Add User</button>
    </div>

    <div class="filter-tabs">
        <button class="tab-button active" data-filter="all">All Roles</button>
        <button class="tab-button" data-filter="Admin">Admin</button>
        <button class="tab-button" data-filter="Sales Representative">Sales Rep</button>
        <button class="tab-button" data-filter="Inventory Officer">Inventory Officer</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- JS will populate rows here -->
            </tbody>
        </table>
    </div>
</section>

<!-- Edit User Popup -->
<div class="overlay" id="editPopupOverlay">
    <div class="popup">
        <div class="popup-header">
            <h2>Edit Employee</h2>
            <p>Use this section to make changes to a user’s account information and system permissions.
                Administrators can update details, assign roles, or deactivate accounts when necessary.</p>
            <span class="close-btn">&times;</span>
        </div>
        <div class="popup-content">
            <h3>User Details</h3>
            <form>
                <label>First Name</label>
                <input type="text" id="edit-first-name">

                <label>Middle Name</label>
                <input type="text" id="edit-middle-name">

                <label>Last Name</label>
                <input type="text" id="edit-last-name">

                <label>Email Address</label>
                <input type="email" id="edit-email">

                <label>Role</label>
                <select>
                    <option>Sales Representative</option>
                    <option>Admin</option>
                    <option>Inventory Officer</option>
                </select>

                <label>Password</label>
                <input type="password" placeholder="Enter password">

                <label>Confirm Password</label>
                <input type="password" placeholder="Re-enter password">

                <div class="btn-group">
                    <button type="button" class="save-btn">Save Changes</button>
                    <button type="button" class="delete-btn">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add User Popup -->
<div class="overlay" id="addUserPopupOverlay">
    <div class="popup">
        <div class="popup-header">
            <h2>Add User</h2>
            <p>Use this section to create a new user account. Administrators can assign roles, set permissions, and
                provide login credentials for the new user.</p>
            <span class="close-btn">&times;</span>
        </div>
        <div class="popup-content">
            <h3>User Details</h3>
            <form>
                <label>Full Name</label>
                <input type="text" placeholder="Enter full name">

                <label>Email Address</label>
                <input type="email" placeholder="Enter email address">

                <label>Phone Number</label>
                <input type="text" placeholder="Enter phone number">

                <label>Role</label>
                <select>
                    <option selected disabled>Select Role</option>
                    <option>Sales Representative</option>
                    <option>Admin</option>
                    <option>Inventory Officer</option>
                </select>

                <label>Password</label>
                <input type="password" placeholder="Enter password">

                <label>Confirm Password</label>
                <input type="password" placeholder="Re-enter password">

                <div class="btn-group">
                    <button type="button" class="save-btn">Add User</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


