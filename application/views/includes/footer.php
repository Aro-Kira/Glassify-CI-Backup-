

<link rel="stylesheet" href="<?php echo base_url('assets/css/include/footer_style.css'); ?>">

<?php 
// FOOTER LOGIC: General vs Customer Footer
// General footer: public pages + all login pages (customer, admin, sales, inventory)
// Customer footer: only for logged-in customers on customer pages

// Check if we should force guest header (for employee login pages and customer login/register pages)
// Use the same logic as header to ensure consistency
$force_guest = isset($force_guest_header) && $force_guest_header;

// Determine if user is a logged-in customer (not employee)
$is_logged_in = $this->session->userdata('is_logged_in') && !$force_guest;
$user_role = $this->session->userdata('user_role');
$is_customer = $is_logged_in && $user_role === 'Customer';
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
                <?php
                $social_links = [
                    'facebook' => ['url' => 'https://www.facebook.com/glassworthbuilders', 'icon' => 'assets/images/img-page/faq-fb.svg'],
                    'instagram' => ['url' => 'https://www.instagram.com/glassworthbuilders', 'icon' => 'assets/images/img-page/faq-ig.svg'],
                    'tiktok' => ['url' => 'https://www.tiktok.com/@glassworthbuilders', 'icon' => 'assets/images/icons/tik-tok.png'],
                ];

                foreach ($social_links as $name => $data) {
                    echo '<a href="' . $data['url'] . '"><img src="' . base_url($data['icon']) . '" alt="' . ucfirst($name) . '" style="filter: brightness(0) invert(1);"></a>';
                }
                ?>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <?php if ($is_customer): ?>
                    <!-- Logged-in customer links (matching header) -->
                    <li><a href="<?php echo base_url('home-login'); ?>">Home</a></li>
                    <li><a href="<?php echo base_url('products'); ?>">Products</a></li>
                    <li><a href="<?php echo base_url('faq'); ?>">FAQ</a></li>
                    <li><a href="<?php echo base_url('my_purchases'); ?>">Track Order</a></li>
                    <li><a href="<?php echo base_url('addtocart'); ?>">Cart</a></li>
                    <li><a href="<?php echo base_url('wishlist'); ?>">Wishlist</a></li>
                <?php else: ?>
                    <!-- General/public links (shown on public pages and login pages) -->
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