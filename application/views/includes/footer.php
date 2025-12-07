

<link rel="stylesheet" href="<?php echo base_url('assets/css/include/footer_style.css'); ?>">

<?php 
// Check if we should force guest header (for employee login pages and customer login/register pages)
// Use the same logic as header to ensure consistency
$force_guest = isset($force_guest_header) && $force_guest_header;
$is_logged_in = $this->session->userdata('is_logged_in') && !$force_guest;
?>

<footer id="contact-footer" class="site-footer">
    <div class="footer-container">

        <!-- Logo & About -->
        <div class="footer-section about-footer">
            <img src="<?php echo base_url('assets/images/img-page/logo.png'); ?>" alt="GlassWorth Builders Logo" class="footer-logo">
            <p>
                We are glass and aluminum experts providing our clients with affordable
                and high-quality service for 27 years and counting
            </p>
            <div class="social-icons">
                <a href="https://www.facebook.com/glassworthbuilders"><img
                        src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/facebook.svg" alt="Facebook"></a>
                <a href="https://www.instagram.com/glassworthbuilders"><img
                        src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/instagram.svg" alt="Instagram"></a>
                <a href="https://www.tiktok.com/@glassworthbuilders"><img
                        src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/tiktok.svg" alt="TikTok"></a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <?php if ($is_logged_in): ?>
                    <!-- Logged-in customer links (matching header) -->
                    <li><a href="<?php echo base_url('home-login'); ?>">Home</a></li>
                    <li><a href="<?php echo base_url('products'); ?>">Products</a></li>
                    <li><a href="<?php echo base_url('faq'); ?>">FAQ</a></li>
                    <li><a href="<?php echo base_url('my_purchases'); ?>">Track Order</a></li>
                    <li><a href="<?php echo base_url('addtocart'); ?>">Cart</a></li>
                    <li><a href="<?php echo base_url('wishlist'); ?>">Wishlist</a></li>
                <?php else: ?>
                    <!-- Guest/public links -->
                    <li><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li><a href="<?php echo base_url('about'); ?>">About Us</a></li>
                    <li><a href="<?php echo base_url('projects'); ?>">Project Showcase</a></li>
                    <li><a href="<?php echo base_url('products'); ?>">Products & Services</a></li>
                    <li><a href="<?php echo base_url('faq'); ?>">FAQ</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Location -->
        <div class="footer-section">
            <h3>Location</h3>
            <p>
                Blk 5 Lot 33 Saranay Road, Malapitan Avenue, Bagumbong North<br>
                Caloocan City, Caloocan, Philippines
            </p>
            <h3>Working Hours</h3>
            <p>
                Monday - Saturday: 9:00 AM - 7:00 PM <br>
                Sunday: 9:00 AM - 5:00 PM
            </p>
        </div>

        <!-- Contact -->
        <div class="footer-section">
            <h3>Contact Information</h3>
            <p><img src="<?php echo base_url('assets/images/img-page/PHONE ICON.svg'); ?>" alt="Phone_icon" class="footer-icon"> 09275193300 /
                09761653506</p>
            <p><img src="<?php echo base_url('assets/images/img-page/EMAIL ICON.svg'); ?>" alt="Email_icon"
                    class="footer-icon">glassworthbuilders@gmail.com</p>
        </div>

    </div>
</footer>