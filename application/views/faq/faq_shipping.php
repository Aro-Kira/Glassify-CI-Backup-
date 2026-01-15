 <link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/faq/faqRoutes_style.css'); ?>">

    <div class="container">
        <!-- Path -->
        <div class="path">
            <a href="<?php echo ($this->session->userdata('is_logged_in')) ? base_url('home-login') : base_url(); ?>" class="home">
                <svg class="home-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <span>Home</span>
            </a>
            <span class="separator">></span>
            <a href="<?php echo base_url('faq'); ?>" class="articles">FAQ</a>
            <span class="separator">></span>
            <span class="current">Shipping & Installation</span>
        </div>

        <!-- Page Title -->
        <h1 class="page-title">Shipping & Installation</h1>
        <div class="title-underline"></div>

        <!-- FAQ Grid -->
        <div class="faq-grid">
            <div class="faq-card">
                <h3 class="faq-question">How long does shipping take?</h3>
                <p class="faq-answer">Shipping times vary based on your location and the complexity of your order. You will receive an estimated delivery date in your order confirmation email. We also offer expedited shipping options for an additional fee.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Can I track my order?</h3>
                <p class="faq-answer">You can track your order directly on our website.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Do you offer installation services?</h3>
                <p class="faq-answer">Yes. You can add installation during checkout. Our team will contact you to schedule it.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">What if my product is damaged upon arrival?</h3>
                <p class="faq-answer">Refuse the delivery and contact us immediately. If you've already accepted it, take photos and contact us within 48 hours to arrange a replacement.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Can I change my shipping address after placing an order?</h3>
                <p class="faq-answer">If you need to change your address, please contact us as soon as possible. We can update it as long as the order has not yet been shipped.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Do you ship internationally?</h3>
                <p class="faq-answer">Currently, we only ship within the Philippines. We are working on expanding our services in the future.</p>
            </div>
        </div>
    </div>

