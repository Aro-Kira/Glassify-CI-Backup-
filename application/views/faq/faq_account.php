

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
            <span class="current">Acount</span>
        </div>

        <!-- Page Title -->
        <h1 class="page-title">Account</h1>
        <div class="title-underline"></div>

        <!-- FAQ Grid -->
        <div class="faq-grid">
            <div class="faq-card">
                <h3 class="faq-question">Why should I create an account?</h3>
                <p class="faq-answer">An account lets you save designs, track orders, manage your information, and receive exclusive offers.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">How do I reset my password?</h3>
                <p class="faq-answer">Click "Forgot Password" on the login page and follow the instructions sent to your email.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">How do I update my personal information?</h3>
                <p class="faq-answer">Log in to your account and go to "Account Settings."</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Is my personal information secure?</h3>
                <p class="faq-answer">Yes. We use industry-standard security to protect your data and do not share it with third parties.</p>
            </div>
        </div>
    </div>

