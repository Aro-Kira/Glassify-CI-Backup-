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
            <span class="current">Pricing & Quotation</span>
        </div>

        <!-- Page Title -->
        <h1 class="page-title">Pricing & Quotation</h1>
        <div class="title-underline"></div>

        <!-- FAQ Grid -->
        <div class="faq-grid">
            <div class="faq-card">
                <h3 class="faq-question">How do I get a price for a custom order?</h3>
                <p class="faq-answer">Use our online design tool for a real-time price estimate. For complex projects, you can upload your file or blueprint.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question"> Are the prices final?</h3>
                <p class="faq-answer">Prices on the website are estimates. The final price is confirmed at checkout and may vary with customization.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Do you offer bulk order discounts?</h3>
                <p class="faq-answer">Yes. Contact our sales department to discuss special pricing for large orders.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question"> Are there any hidden fees?</h3>
                <p class="faq-answer">No. All charges, including taxes and shipping fees, are clearly itemized in your cart before you pay.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Is the quotation valid for a specific time?</h3>
                <p class="faq-answer">Yes. Our quotations are valid for 30 days from the date of issue. After this period, prices may be subject to change.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">What's the difference between a quote and an estimate?</h3>
                <p class="faq-answer">An estimate is an approximate cost shown on the website. A quote is a formal, fixed price for your specific project, provided by our sales team.</p>
            </div>
        </div>
    </div>


