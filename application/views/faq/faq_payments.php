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
            <span class="current">Payments</span>
        </div>

        <!-- Page Title -->
        <h1 class="page-title">Payments</h1>
        <div class="title-underline"></div>

        <!-- FAQ Grid -->
        <div class="faq-grid">
            <div class="faq-card">
                <h3 class="faq-question">How can I pay for my order?</h3>
                <p class="faq-answer">For online transactions on our website, we exclusively accept GCash. This allows for a fast and secure checkout process. For in-person payments at our physical location, we accept GCash, bank transfers, and all major credit and debit cards.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">When will my payment be processed?</h3>
                <p class="faq-answer">GCash payments are processed instantly. Bank transfers are confirmed upon receipt.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">What if I have a payment issue?</h3>
                <p class="faq-answer">If your payment fails, please double-check your details. If the problem persists, contact our support team with your order number.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Do I need to create an account before ordering?</h3>
                <p class="faq-answer">Yes. Installment plans are available for in-person purchases at our location. You can choose from various terms depending on your bank and purchase amount. Please inquire with our staff for details.</p>
            </div>
        </div>
    </div>


