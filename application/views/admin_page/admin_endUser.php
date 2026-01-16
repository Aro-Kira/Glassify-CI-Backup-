<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


<section class="user-section-main">
  <h1 class="page-title">End Users</h1>
  <p>Easily update user details, list of registered customer, and their contact information.</p>

  <div class="controls-container">
    <div class="search-bar">
      <input type="text" placeholder="Filter by name or category..." class="search-input">
      <button class="search-button">Search</button>
    </div>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th></th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Joined Date</th>
          <th>Last Active</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- JS will populate rows here -->
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <span class="showing-info">Showing 1-4 of 255 items</span>
    <div class="pagination-controls"></div>
  </div>
</section>

<!-- Edit User Overlay -->
<div class="overlay" id="popupOverlay">
  <div class="popup">
    <div class="popup-header">
      <h2>Edit User</h2>
      <span class="close-btn" onclick="closePopup()">&times;</span>
    </div>
    <div class="popup-content">
      <form id="editForm">
        <input type="hidden" id="edit-id">
        <h3>User Details</h3>
        <label>First Name <span class="required">*</span></label>
        <input type="text" id="edit-firstName">
        <label>Middle Initial</label>
        <input type="text" id="edit-middleInitial">
        <label>Surname <span class="required">*</span></label>
        <input type="text" id="edit-lastName">
        <label>Email Address <span class="required">*</span></label>
        <input type="email" id="edit-email">
        <label>Phone Number <span class="required">*</span></label>
        <input type="text" id="edit-phone">
        <div class="btn-group">
          <button type="button" class="save-btn" onclick="saveEdit()">Save Changes</button>
          <button type="button" class="delete-btn" onclick="deleteEditUser()">Delete Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Overlay -->
<div class="popup-delete-overlay" id="popup-delete">
  <div class="popup-delete-box">
    <div class="popup-delete-header">
      Delete User?
      <span class="popup-delete-close" onclick="closeDeletePopup()">&times;</span>
    </div>
    <p>Are you sure you want to delete this user? This will archive the user.</p>
    <div class="popup-delete-actions">
      <button class="popup-delete-cancel" onclick="closeDeletePopup()">Cancel</button>
      <button class="popup-delete-confirm">Delete</button>
    </div>
  </div>
</div>

<script>
    const getUsersUrl = "<?= site_url('EndUserCon/get_users') ?>";
    const updateUserUrl = "<?= site_url('EndUserCon/update_user') ?>";
    const deleteUserUrl = "<?= site_url('EndUserCon/delete_user') ?>";
</script>
<script src="<?= base_url('assets/js/admin-js/end-user.js') ?>"></script>
