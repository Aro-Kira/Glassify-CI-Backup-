<!-- ========================= INCLUDE STYLES & SCRIPTS ========================= -->
<link rel="stylesheet" href="<?php echo base_url('assets/css/include/header_style.css'); ?>">
<script>
    // Set BASE_URL for JavaScript
    window.BASE_URL = "<?php echo base_url(); ?>";
</script>
<script src="<?php echo base_url('assets/js/includes/header.js'); ?>"></script>
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

// Check if we should force guest header (for employee login pages and customer login/register pages)
$force_guest = isset($force_guest_header) && $force_guest_header;

// Determine if user is a logged-in customer (not employee)
$is_logged_in = $this->session->userdata('is_logged_in') && !$force_guest;
$user_role = $this->session->userdata('user_role');
$is_customer = $is_logged_in && $user_role === 'Customer';

// Get cart and wishlist counts if customer is logged in
$cart_count = 0;
$wishlist_count = 0;
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
    }
}
?>

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

        <!-- CART ICON (Requires Customer Login) -->
        <a href="<?= base_url($is_customer ? 'addtocart' : 'login?redirect=addtocart'); ?>"
            class="icon-link cart-icon-link">
            <div class="icon-wrapper">
                <img src="<?= base_url('assets/images/img-page/shopping-cart.png'); ?>" alt="Shopping_cart">
                <?php if ($is_customer): ?>
                    <span class="icon-badge" id="cart-count" style="display: <?= $cart_count > 0 ? 'flex' : 'none' ?>;"><?= $cart_count ?></span>
                <?php endif; ?>
            </div>
        </a>


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