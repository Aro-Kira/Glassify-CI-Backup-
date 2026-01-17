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
            <span class="current">Warranty</span>
        </div>

        <!-- Page Title -->
        <h1 class="page-title">Warranty</h1>
        <div class="title-underline"></div>

        <!-- FAQ Grid -->
        <div class="faq-grid">
            <div class="faq-card">
                <h3 class="faq-question">What does the warranty cover?</h3>
                <p class="faq-answer">Our one-year warranty covers defects in materials and workmanship.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">How do I make a warranty claim?</h3>
                <p class="faq-answer">Contact our support team with your proof of purchase, order number, and a description of the issue.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">What voids my warranty?</h3>
                <p class="faq-answer">The warranty is voided by damage from misuse, improper installation, accidental damage, or unauthorized repairs.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Is the warranty transferable if I sell the product?</h3>
                <p class="faq-answer">The warranty is non-transferable and only applies to the original purchaser of the product.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Does the warranty cover normal wear and tear?</h3>
                <p class="faq-answer">No, the warranty does not cover normal wear and tear, cosmetic changes, or damage from improper maintenance.</p>
            </div>
        </div>
    </div>


