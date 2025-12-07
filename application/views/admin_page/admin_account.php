<?php
// Get Admin data from controller
$admin = isset($admin) ? $admin : null;
$full_name = $admin ? trim($admin->First_Name . ' ' . ($admin->Middle_Name ? $admin->Middle_Name . ' ' : '') . $admin->Last_Name) : 'Admin';
$first_name = $admin ? $admin->First_Name : '';
$middle_name = $admin ? $admin->Middle_Name : '';
$last_name = $admin ? $admin->Last_Name : '';
$email = $admin ? $admin->Email : '';
$phone = $admin ? $admin->PhoneNum : '';
$role = $admin ? $admin->Role : 'Admin';
$status = $admin ? $admin->Status : 'Active';
$date_created = $admin ? date('F d, Y', strtotime($admin->Date_Created)) : '';
?>

<!-- Accounts -->
<section class="account-section">
  <div class="section-header">
    <h1 class="page-title">Account</h1>
    <i class="fas fa-user-circle"></i>
  </div>

  <div class="account-card">
    <!-- Profile Header -->
    <div class="profile-header">
      <div class="profile-icon">
        <img src="<?php echo base_url('assets/images/img-page/female-user.svg'); ?>" alt="Profile Icon">
      </div>
      <div class="profile-info">
        <h3><?= htmlspecialchars($full_name) ?></h3>
        <p><?= htmlspecialchars($role) ?></p>
      </div>
    </div>

    <hr />

    <!-- Account Details -->
    <div class="account-details">
      <div class="form-group">
        <label>Email</label>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($email) ?>" readonly>
          <!-- Email field is not editable - no edit icon -->
        </div>
      </div>

      <div class="form-group">
        <label>Password</label>
        <div class="input-box">
          <input type="password" value="************" readonly data-field="Password">
          <i class="fas fa-pen"></i>
        </div>
      </div>

      <div class="form-group">
        <label>First Name</label>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($first_name) ?>" readonly data-field="First_Name">
          <i class="fas fa-pen"></i>
        </div>
      </div>

      <div class="form-group">
        <label>Middle Initial</label>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($middle_name) ?>" placeholder="(optional)" readonly data-field="Middle_Name">
          <i class="fas fa-pen"></i>
        </div>
      </div>

      <div class="form-group">
        <label>Surname</label>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($last_name) ?>" readonly data-field="Last_Name">
          <i class="fas fa-pen"></i>
        </div>
      </div>

      <div class="form-group">
        <label>Title</label>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($role) ?>" readonly>
          <!-- Title field is not editable - no edit icon -->
        </div>
      </div>

      <div class="form-group">
        <label>Phone Number</label>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($phone) ?>" readonly data-field="PhoneNum">
          <i class="fas fa-pen"></i>
        </div>
      </div>

      <?php if ($date_created): ?>
      <div class="form-group">
        <label>Account Created</label>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($date_created) ?>" readonly>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="logout">
    <a href="<?php echo base_url('logout'); ?>">Log out?</a>
  </div>

  <!-- Popup Overlay -->
  <div class="popup-overlay" id="editPopup">
    <div class="popup">
      <span class="close-btn" id="closePopup">&times;</span>
      <h3 id="popupTitle">Edit Field</h3>

      <form id="editForm">
        <div class="form-group">
          <label id="popupLabel"></label>
          <input type="text" id="popupInput" class="input-text" autocomplete="off">
        </div>

        <!-- Confirm Password field (only shown when editing password) -->
        <div class="form-group" id="confirmPasswordGroup" style="display: none;">
          <label>Confirm Password</label>
          <input type="password" id="popupConfirmPassword" class="input-text" placeholder="Re-enter new password" autocomplete="off">
        </div>

        <div class="popup-actions">
          <button type="submit" class="save-btn" id="saveBtn">Save</button>
          <button type="button" class="cancel-btn" id="cancelPopup">Cancel</button>
        </div>
      </form>
    </div>
  </div>

</section>

<script src="<?php echo base_url('assets/js/admin-sidebar.js'); ?>"></script>
<script>
    // Make base_url and update URL available to JavaScript
    const base_url = "<?php echo base_url(); ?>";
    const updateAccountUrl = "<?php echo base_url('AdminCon/update_account'); ?>";
</script>
<script src="<?php echo base_url('assets/js/admin-js/account-edit.js'); ?>"></script>
