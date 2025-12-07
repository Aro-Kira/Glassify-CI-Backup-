
 <link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/faq/report_issue.css'); ?>">


  <div class="container">
    <!-- Path -->
    <div class="path">
      <a href="<?php echo base_url(); ?>" class="home" title="Go to Home">
        <svg class="home-icon" fill="currentColor" viewBox="0 0 20 20">
          <path
            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
        </svg>
      </a>
      <span class="separator">></span>
      <span class="articles" onclick="window.location.href='<?php echo base_url('faq'); ?>'">All articles</span>
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
    

    <!-- Report Form -->
    <form class="report-form" action="<?php echo base_url('submit-issue'); ?>" method="POST">
      <h2>User Information</h2>
      <div class="form-row">
        <div class="form-group">
          <label>First Name <span>*</span></label>
          <input type="text" name="first-name" required placeholder="Enter your first name" title="First Name">
        </div>
        <div class="form-group">
          <label>Last Name <span>*</span></label>
          <input type="text" name="last-name" required placeholder="Enter your last name" title="Last Name">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Email <span>*</span></label>
          <input type="email" name="email" required placeholder="Enter your email" title="Email" value="<?php echo $this->session->userdata('user_email') ?? ''; ?>">
        </div>
        <div class="form-group">
          <label>Contact Number <span>*</span></label>
          <input type="text" name="contact-number" required placeholder="Enter your contact number" title="Contact Number">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group full-width">
          <label>Order ID <span style="color: #999;">(Optional - leave blank if not applicable)</span></label>
          <input type="text" name="order-id" title="Order ID" placeholder="Enter your order ID (e.g., #G1001 or 1001) - Optional">
        </div>
      </div>

      <h2>Issue Details</h2>
      <div class="form-row">
        <div class="form-group full-width">
          <label>Issue Category <span>*</span></label>
          <select name="issue-category" required title="Issue Category">
            <option value="">Select Category</option>
            <option value="Order Issue">Order Issue</option>
            <option value="Payment Issue">Payment Issue</option>
            <option value="Delivery Issue">Delivery Issue</option>
            <option value="General Inquiry">General Inquiry</option>
            <option value="Installation Problems">Installation Problems</option>
            <option value="Product Defect/Damage">Product Defect/Damage</option>
            <option value="Measurement/Design Problems">Measurement/Design Problems</option>
            <option value="Billing/Payment Questions">Billing/Payment Questions</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group full-width">
          <label>Description <span>*</span></label>
          <textarea name="description" placeholder="Please describe your issue in at least 2 sentences (20 to 50 words)" required></textarea>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group full-width">
          <label>Attachments (optional)</label>
          <div class="upload-btn-wrapper">
            <button type="button" class="upload-btn">
                <img src="/Assets/img/attach.svg" alt="Attach" class="upload-icon">Upload Image or File
            </button>
            <input type="file" name="attachment" title="Attachment" placeholder="Upload Image or File" />
          </div>
        </div>
      </div>

      <button type="submit" class="submit-btn">Submit Issue</button>
    </form>
  </div>
