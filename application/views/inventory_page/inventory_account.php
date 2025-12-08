<?php
// Get Inventory Officer data from controller
$inventory_officer = isset($inventory_officer) ? $inventory_officer : null;
$full_name = $inventory_officer ? trim($inventory_officer->First_Name . ' ' . ($inventory_officer->Middle_Name ? $inventory_officer->Middle_Name . ' ' : '') . $inventory_officer->Last_Name) : 'Inventory Officer';
$first_name = $inventory_officer ? $inventory_officer->First_Name : '';
$middle_name = $inventory_officer ? $inventory_officer->Middle_Name : '';
$last_name = $inventory_officer ? $inventory_officer->Last_Name : '';
$email = $inventory_officer ? $inventory_officer->Email : '';
$phone = $inventory_officer ? $inventory_officer->PhoneNum : '';
$role = $inventory_officer ? $inventory_officer->Role : 'Inventory Officer';
$status = $inventory_officer ? $inventory_officer->Status : 'Active';
$date_created = $inventory_officer ? date('F d, Y', strtotime($inventory_officer->Date_Created)) : '';
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
          <input type="password" value="************" readonly>
          <i class="fas fa-pen"></i>
        </div>
      </div>

      <div class="form-group">
        <label>First Name</label>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($first_name) ?>" readonly>
          <i class="fas fa-pen"></i>
        </div>
      </div>

      <div class="form-group">
        <label>Middle Name</label>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($middle_name) ?>" placeholder="(optional)" readonly>
          <i class="fas fa-pen"></i>
        </div>
      </div>

      <div class="form-group">
        <label>Last Name</label>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($last_name) ?>" readonly>
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
          <input type="text" value="<?= htmlspecialchars($phone) ?>" readonly>
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

    <script>
        // Make base_url available to JavaScript
        const base_url = "<?php echo base_url(); ?>";
        console.log('base_url defined:', base_url);
    </script>
    <script>
        console.log('Loading account-edit.js...');
    </script>
    <script src="<?php echo base_url('assets/js/inventory-js/account-edit.js'); ?>" onerror="console.error('Failed to load account-edit.js')"></script>
    <script>
        console.log('account-edit.js script tag processed');
    </script>
    <script src="<?php echo base_url('assets/js/inventory-js/account-icon-active.js'); ?>"></script>



