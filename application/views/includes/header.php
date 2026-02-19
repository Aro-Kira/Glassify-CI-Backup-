<!-- ========================= INCLUDE STYLES & SCRIPTS ========================= -->
<link rel="stylesheet" href="<?php echo base_url('assets/css/include/header_style.css'); ?>">
<script>
    // Set BASE_URL for JavaScript
    window.BASE_URL = "<?php echo base_url(); ?>";
    
    // Check if account was deleted (user was logged out by admin)
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('account_deleted') === '1') {
            // Create centered warning popup
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; display: flex; align-items: center; justify-content: center;';
            
            const popup = document.createElement('div');
            popup.style.cssText = 'background: #fff; border-radius: 12px; padding: 30px 40px; max-width: 450px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.3); animation: popupFadeIn 0.3s ease;';
            popup.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="margin: 0 auto;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h3 style="color: #1f2937; font-size: 1.25rem; margin: 0 0 12px 0; font-weight: 600;">Account Deleted</h3>
                <p style="color: #6b7280; font-size: 0.95rem; margin: 0 0 24px 0; line-height: 1.5;">Your account has been deleted by an administrator. You have been logged out.</p>
                <button id="accountDeletedOkBtn" style="background: #02455f; color: white; border: none; padding: 12px 32px; border-radius: 6px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s;">OK</button>
            `;
            
            overlay.appendChild(popup);
            document.body.appendChild(overlay);
            
            // Add animation keyframes
            const style = document.createElement('style');
            style.textContent = '@keyframes popupFadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }';
            document.head.appendChild(style);
            
            // Close popup on button click
            document.getElementById('accountDeletedOkBtn').addEventListener('click', function() {
                overlay.remove();
                style.remove();
            });
            
            // Clean up URL
            const cleanUrl = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        }
    });
</script>
<script src="<?php echo base_url('assets/js/includes/header.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/toast-notification.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/confirmation-dialog.js'); ?>"></script>
<script src="https://kit.fontawesome.com/fc5ceca38c.js" crossorigin="anonymous"></script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : "Glassify"; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/images/img-page/logo-with-bg.png'); ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url('assets/images/img-page/logo-with-bg.png'); ?>">
    <link rel="apple-touch-icon" href="<?php echo base_url('assets/images/img-page/logo-with-bg.png'); ?>">
</head>

<?php 
// HEADER LOGIC: General vs Customer Header
// General header: public pages + all login pages (customer, admin, sales, inventory)
// Customer header: only for logged-in customers on customer pages

// Note: Account existence verification is handled by the AccountVerification hook
// which runs before every controller and automatically logs out users whose accounts were deleted

// Check if we should force guest header (for employee login pages and customer login/register pages)
$force_guest = isset($force_guest_header) && $force_guest_header;

// Determine if user is a logged-in customer (not employee)
$is_logged_in = $this->session->userdata('is_logged_in') && !$force_guest;
$user_role = $this->session->userdata('user_role');
$is_customer = $is_logged_in && $user_role === 'Customer';

// Get cart, wishlist, and notification counts if customer is logged in
$cart_count = 0;
$wishlist_count = 0;
$notification_count = 0;
if ($is_customer) {
    $customer_id = $this->session->userdata('customer_id');
    if ($customer_id) {
        // Load models in view (CodeIgniter allows this)
        // Note: In views, $this refers to CI_Loader, models are loaded into CI instance
        $CI =& get_instance();
        $CI->load->model('Cart_model');
        $CI->load->model('Wishlist_model');
        
        // Access models through CI instance
        $cart_count = $CI->Cart_model->get_cart_count($customer_id);
        $wishlist_count = $CI->Wishlist_model->get_wishlist_count($customer_id);
        
        // Get unread notification count
        if ($CI->db->table_exists('customer_notifications')) {
            $CI->db->where('Customer_ID', $customer_id);
            $CI->db->where('Status', 'Unread');
            $notification_count = $CI->db->count_all_results('customer_notifications');
        }
    }
}
?>

<?php if (!empty($user_role) && strtolower($user_role) === 'admin'): ?>
<script>
    // Suppress native blocking dialogs on Admin pages to avoid "localhost says" popups.
    // Keep references to originals in case debugging is needed.
    (function(){
        try {
            window._nativeConfirm = window.confirm.bind(window);
            window._nativeAlert = window.alert.bind(window);
            window._nativePrompt = window.prompt.bind(window);
            // Replace with no-op / safe defaults for admin UI
            window.confirm = function(){ return true; };
            window.alert = function(msg){ console.info('Suppressed alert:', msg); };
            window.prompt = function(){ return null; };
        } catch (e) {
            // If binding fails, silently ignore to avoid breaking the header
            console.warn('Failed to override native dialogs:', e);
        }
    })();
</script>
<?php endif; ?>

<header class="navbar">
    <!-- ========================= MOBILE MENU TOGGLE ========================= -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- ========================= LOGO SECTION ========================= -->
    <div class="logo">
        <a href="<?php echo $is_customer ? base_url('home-login') : base_url(); ?>">
            <img src="<?php echo base_url('assets/images/img-page/logo.png'); ?>" alt="GlassWorth Logo">
        </a>
    </div>

    <!-- ========================= NAVIGATION LINKS ========================= -->
    <nav class="menu" id="mainMenu">
        <!-- ========== HOME LINK CHANGES BASED ON LOGIN STATUS ========== -->
        <?php if ($is_customer): ?>
            <!-- When customer is logged in, redirect Home to home-login page -->
            <a href="<?php echo base_url('home-login'); ?>" data-link>Home</a>
        <?php else: ?>
            <!-- When not logged in or on login pages, redirect Home to main landing page -->
            <a href="<?php echo base_url(); ?>" data-link>Home</a>
        <?php endif; ?>

        <?php if (!$is_customer): ?>
            <!-- ========== GENERAL/GUEST LINKS (shown on public pages and login pages) ========== -->
            <a href="<?php echo base_url('about'); ?>" data-link>About Us</a>
            <a href="<?php echo base_url('projects'); ?>" data-link>Projects</a>
        <?php endif; ?>

        <!-- ========== ALWAYS AVAILABLE LINKS ========== -->
        <a href="<?php echo base_url('products'); ?>" data-link>Products</a>
        <a href="<?php echo base_url('faq'); ?>" data-link>FAQ</a>
    </nav>

    <!-- ========================= ICON SECTION ========================= -->
    <div class="icons">
        <?php if ($is_customer): ?>
            <!-- ========== CUSTOMER-ONLY ICON (TRACK ORDER) ========== -->
            <a href="<?php echo base_url('my_purchases'); ?>">
                <img src="<?php echo base_url('assets/images/img-page/tracking.png'); ?>" alt="Tracking"
                    class="tracking-icon">
            </a>
        <?php endif; ?>

        <!-- CART ICON REMOVED - Now using booking-only workflow -->

        <!-- WISHLIST ICON (Requires Customer Login) -->
        <a href="<?= base_url($is_customer ? 'wishlist' : 'login?redirect=wishlist'); ?>"
            class="icon-link wishlist-icon-link">
            <div class="icon-wrapper">
                <img src="<?= base_url('assets/images/img-page/heart.png'); ?>" alt="Wishlist">
                <?php if ($is_customer): ?>
                    <span class="icon-badge" id="wishlist-count" style="display: <?= $wishlist_count > 0 ? 'flex' : 'none' ?>;"><?= $wishlist_count ?></span>
                <?php endif; ?>
            </div>
        </a>

        <?php if ($is_customer): ?>
            <!-- ========== NOTIFICATION ICON (CUSTOMER ONLY) - use image so hover tint works consistently ========== -->
            <a href="<?php echo base_url('notifications'); ?>" class="icon-link">
                <div class="icon-wrapper">
                    <img src="<?php echo base_url('assets/images/img-page/bell_notif.png'); ?>" alt="Notifications" class="notif-icon">
                    <span class="icon-badge" id="notification-count" style="display: <?= $notification_count > 0 ? 'flex' : 'none' ?>;">
                        <?= $notification_count > 99 ? '99+' : $notification_count ?>
                    </span>
                </div>
            </a>
        <?php endif; ?>

        <!-- ========== PROFILE / LOGIN ICON (ALWAYS LAST) ========== -->
        <div class="header-dropdown" style="display: inline-block; position: relative;">
            <?php if ($is_customer): ?>
                <!-- When customer is logged in: link directly to Profile page -->
                <a href="<?php echo base_url('Profile'); ?>" style="display: inline-block;">
                    <img src="<?php echo base_url('assets/images/img-page/user.png'); ?>" alt="Profile" style="cursor: pointer;">
                </a>
            <?php else: ?>
                <!-- When not logged in or on login pages: show login icon -->
                <a href="<?php echo base_url('login'); ?>">
                    <img src="<?php echo base_url('assets/images/img-page/user.png'); ?>" alt="Login">
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>