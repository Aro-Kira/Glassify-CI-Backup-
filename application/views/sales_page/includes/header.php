<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<header class="header">
  <div class="header-right">
    <a href="<?php echo base_url('sales-notif'); ?>" 
       class="notification-link <?php echo (isset($active) && $active === 'notif') ? 'active' : ''; ?>">
      <i class="fas fa-bell"></i>
    </a>
    <a href="<?php echo base_url('sales-account'); ?>" 
       class="user-link <?php echo (isset($active) && $active === 'account') ? 'active' : ''; ?>">
      <i class="fas fa-user-circle user-icon"></i>
    </a>
  </div>
</header>

