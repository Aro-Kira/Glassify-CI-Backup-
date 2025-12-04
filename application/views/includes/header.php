<!-- ========================= INCLUDE STYLES & SCRIPTS ========================= -->
<link rel="stylesheet" href="<?php echo base_url('assets/css/include/header_style.css'); ?>">
<script src="<?php echo base_url('assets/js/includes/header.js'); ?>"></script>
<script src="https://kit.fontawesome.com/fc5ceca38c.js" crossorigin="anonymous"></script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : "Glassify"; ?></title>
</head>

<header class="navbar">
    <!-- ========================= LOGO SECTION ========================= -->
    <div class="logo">
        <a href="<?php echo base_url(); ?>">
            <img src="<?php echo base_url('assets/images/img-page/logo.png'); ?>" alt="GlassWorth Logo">
        </a>
    </div>

    <!-- ========================= NAVIGATION LINKS ========================= -->
    <nav class="menu">
        <!-- ========== HOME LINK CHANGES BASED ON LOGIN STATUS ========== -->
        <?php if ($this->session->userdata('is_logged_in')): ?>
            <!-- When logged in, redirect Home to home-login page -->
            <a href="<?php echo base_url('home-login'); ?>" data-link>Home</a>
        <?php else: ?>
            <!-- When not logged in, redirect Home to main landing page -->
            <a href="<?php echo base_url(); ?>" data-link>Home</a>
        <?php endif; ?>

        <?php if (!$this->session->userdata('is_logged_in')): ?>
            <!-- ========== GUEST-ONLY LINKS ========== -->
            <a href="<?php echo base_url('about'); ?>" data-link>About Us</a>
            <a href="<?php echo base_url('projects'); ?>" data-link>Projects</a>
        <?php endif; ?>

        <!-- ========== ALWAYS AVAILABLE LINKS ========== -->
        <a href="<?php echo base_url('products'); ?>" data-link>Products</a>
        <a href="<?php echo base_url('faq'); ?>" data-link>FAQ</a>
    </nav>

    <!-- ========================= ICON SECTION ========================= -->
    <div class="icons">

        <?php if ($this->session->userdata('is_logged_in')): ?>
            <!-- ========== USER-ONLY ICON (TRACK ORDER) ========== -->
            <a href="<?php echo base_url('my_purchases'); ?>">
                <img src="<?php echo base_url('assets/images/img-page/tracking.png'); ?>" alt="Tracking"
                    class="tracking-icon">
            </a>
        <?php endif; ?>

        <!-- CART ICON (Requires Login) -->
        <a href="<?= base_url($this->session->userdata('is_logged_in') ? 'addtocart' : 'login?redirect=addtocart'); ?>"
            class="icon-link">
            <img src="<?= base_url('assets/images/img-page/shopping-cart.png'); ?>" alt="Shopping_cart">
        </a>


        <!-- WISHLIST ICON (Requires Login) -->
        <a href="<?= base_url($this->session->userdata('is_logged_in') ? 'wishlist' : 'login?redirect=wishlist'); ?>"
            class="icon-link">
            <img src="<?= base_url('assets/images/img-page/heart.png'); ?>" alt="Wishlist">
        </a>



        <!-- ========== PROFILE / LOGIN ICON (ALWAYS LAST) ========== -->
        <div class="header-dropdown" style="display: inline-block; position: relative;">
            <?php if ($this->session->userdata('is_logged_in')): ?>
                <!-- When logged in: show dropdown with Profile + Logout -->
                <button class="header-dropbtn" style="background: none; border: none;">
                    <img src="<?php echo base_url('assets/images/img-page/user.png'); ?>" alt="Profile">
                </button>
                <div class="dropdown-content"
                    style="display: none; position: absolute; right: 0; background: white; border: 1px solid #ddd; border-radius: 5px; padding: 10px;">
                    <a href="<?php echo base_url('Profile'); ?>">Profile</a><br>
                    <a href="<?php echo base_url('logout'); ?>">Logout</a>
                </div>

                <!-- ========== DROPDOWN TOGGLE SCRIPT ========== -->
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const btn = document.querySelector('.header-dropbtn');
                        const dropdown = document.querySelector('.dropdown-content');
                        btn.addEventListener('click', () => {
                            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                        });
                        // Hide dropdown when clicking outside
                        document.addEventListener('click', (e) => {
                            if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                                dropdown.style.display = 'none';
                            }
                        });
                    });
                </script>
            <?php else: ?>
                <!-- When not logged in: show login icon -->
                <a href="<?php echo base_url('login'); ?>">
                    <img src="<?php echo base_url('assets/images/img-page/user.png'); ?>" alt="Login">
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>