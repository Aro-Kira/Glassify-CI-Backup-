
 <link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/faq/report_issue.css'); ?>">


  <div class="container">
    <!-- Path -->
    <div class="path">
      <a href="<?php echo ($this->session->userdata('is_logged_in')) ? base_url('home-login') : base_url(); ?>" class="home" title="Go to Home">
        <svg class="home-icon" fill="currentColor" viewBox="0 0 20 20">
          <path
            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
        </svg>
        <span>Home</span>
      </a>
      <span class="separator">></span>
      <a href="<?php echo base_url('faq'); ?>" class="articles">FAQ</a>
      <span class="separator">></span>
      <span class="current">Report Issue</span>
    </div>

    <!-- Page Title -->
    <h1 class="page-title">Report an Issue</h1>
    <p class="page-desc">Please provide details of your issue so our team can assist you promptly.</p>
    <div class="title-underline"></div>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #f5c6cb;">
            <strong>Error:</strong> <?php echo $this->session->flashdata('error'); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('success')): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #c3e6cb;">
            <strong>Success:</strong> <?php echo $this->session->flashdata('success'); ?>
        </div>
    <?php endif; ?>
    
    <?php
    // Get form data from flashdata for repopulation
    $form_data = $this->session->flashdata('form_data');
    if ($form_data) {
        // Repopulate form values from flashdata
        $_POST['first-name'] = $form_data['first-name'] ?? '';
        $_POST['middle-name'] = $form_data['middle-name'] ?? '';
        $_POST['last-name'] = $form_data['last-name'] ?? '';
        $_POST['email'] = $form_data['email'] ?? '';
        $_POST['contact-number'] = $form_data['contact-number'] ?? '';
        $_POST['order-id'] = $form_data['order-id'] ?? '';
        $_POST['issue-category'] = $form_data['issue-category'] ?? '';
        $_POST['description'] = $form_data['description'] ?? '';
    }
    ?>

    <!-- Report Form -->
    <form class="report-form" action="<?php echo base_url('submit-issue'); ?>" method="POST" enctype="multipart/form-data">
      <h2>User Information</h2>
      <div class="form-row form-row-three">
        <div class="form-group">
          <label>First Name <span>*</span></label>
          <input type="text" name="first-name" required placeholder="Enter your first name" title="First Name" value="<?= set_value('first-name', isset($user) ? htmlspecialchars($user->First_Name ?? '') : ''); ?>">
        </div>
        <div class="form-group">
          <label>Middle Name</label>
          <input type="text" name="middle-name" placeholder="Enter your middle name" title="Middle Name" value="<?= set_value('middle-name', isset($user) ? htmlspecialchars($user->Middle_Name ?? '') : ''); ?>">
        </div>
        <div class="form-group">
          <label>Last Name <span>*</span></label>
          <input type="text" name="last-name" required placeholder="Enter your last name" title="Last Name" value="<?= set_value('last-name', isset($user) ? htmlspecialchars($user->Last_Name ?? '') : ''); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Email <span>*</span></label>
          <input type="email" name="email" required placeholder="Enter your email" title="Email" value="<?= set_value('email', isset($user) ? htmlspecialchars($user->Email ?? '') : ($this->session->userdata('user_email') ?? '')); ?>">
        </div>
        <div class="form-group">
          <label>Contact Number <span>*</span></label>
          <input type="text" name="contact-number" required placeholder="Enter your contact number" title="Contact Number" value="<?= set_value('contact-number', isset($user) ? htmlspecialchars($user->PhoneNum ?? '') : ''); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group full-width">
          <label>Order ID <span style="color: #999;">(Optional - leave blank if not applicable)</span></label>
          <input type="text" name="order-id" title="Order ID" placeholder="Enter your order ID (e.g., #G1001 or 1001) - Optional" value="<?= set_value('order-id'); ?>">
        </div>
      </div>

      <h2>Issue Details</h2>
      <div class="form-row">
        <div class="form-group full-width">
          <label>Issue Category <span>*</span></label>
          <select name="issue-category" required title="Issue Category">
            <option value="">Select Category</option>
            <option value="Order Issue" <?= set_select('issue-category', 'Order Issue'); ?>>Order Issue</option>
            <option value="Payment Issue" <?= set_select('issue-category', 'Payment Issue'); ?>>Payment Issue</option>
            <option value="Delivery Issue" <?= set_select('issue-category', 'Delivery Issue'); ?>>Delivery Issue</option>
            <option value="General Inquiry" <?= set_select('issue-category', 'General Inquiry'); ?>>General Inquiry</option>
            <option value="Installation Problems" <?= set_select('issue-category', 'Installation Problems'); ?>>Installation Problems</option>
            <option value="Product Defect/Damage" <?= set_select('issue-category', 'Product Defect/Damage'); ?>>Product Defect/Damage</option>
            <option value="Measurement/Design Problems" <?= set_select('issue-category', 'Measurement/Design Problems'); ?>>Measurement/Design Problems</option>
            <option value="Billing/Payment Questions" <?= set_select('issue-category', 'Billing/Payment Questions'); ?>>Billing/Payment Questions</option>
            <option value="Other" <?= set_select('issue-category', 'Other'); ?>>Other</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group full-width">
          <label>Description <span>*</span></label>
          <textarea name="description" placeholder="Please describe your issue in at least 2 sentences (20 to 50 words)" required><?= set_value('description'); ?></textarea>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group full-width">
          <label>Attachments (optional)</label>
          <div class="upload-btn-wrapper">
            <button type="button" class="upload-btn" id="upload-trigger-btn">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#423D3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="upload-icon">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                </svg>
                Upload Image or File
            </button>
            <input type="file" name="attachment" id="attachment-input" accept=".png,.pdf,.jpg,.jpeg" title="Attachment" placeholder="Upload Image or File" />
            <small style="display: block; margin-top: 5px; color: #666;">Accepted formats: PNG, PDF, JPG, JPEG</small>
          </div>
        </div>
      </div>

      <button type="submit" class="submit-btn">Submit Issue</button>
    </form>
  </div>

<script>
  // Make attachment button trigger file input
  document.addEventListener('DOMContentLoaded', function() {
    const uploadBtn = document.getElementById('upload-trigger-btn');
    const fileInput = document.getElementById('attachment-input');
    
    if (uploadBtn && fileInput) {
      uploadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        fileInput.click();
      });
      
      // Update button text when file is selected
      fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
          const fileName = this.files[0].name;
          uploadBtn.innerHTML = `
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#423D3D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="upload-icon">
              <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
            </svg>
            ${fileName}
          `;
        }
      });
    }
  });
</script>
