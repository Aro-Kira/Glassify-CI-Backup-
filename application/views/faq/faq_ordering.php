 <link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/faq/faqRoutes_style.css'); ?>">

 
 <div class="container">
        <!-- Path -->
        <div class="path">
            <a href="<?php echo ($this->session->userdata('is_logged_in')) ? base_url('home-login') : base_url(); ?>" class="home">
                <svg class="home-icon" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <span>Home</span>
            </a>
            <span class="separator">></span>
            <a href="<?php echo base_url('faq'); ?>" class="articles">FAQ</a>
            <span class="separator">></span>
            <span class="current">Ordering & Customization</span>
        </div>

        <!-- Page Title -->
        <h1 class="page-title">Ordering & Customization</h1>
        <div class="title-underline"></div>

        <!-- FAQ Grid -->
        <div class="faq-grid">
            <div class="faq-card">
                <h3 class="faq-question">How do I place an order?</h3>
                <p class="faq-answer">To place an order, simply log in to your account, go to the Products & Services page, customize your item using the 2D tool, and click "Add to Order." Follow the checkout process to confirm.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Can I upload my own plan?</h3>
                <p class="faq-answer">Yes. During the ordering process, you will see an option to upload your blueprint or project file. This helps us process bulk or property-wide orders more accurately.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">What does "product specification" mean?</h3>
                <p class="faq-answer">Product specification refers to the set of details you define when customizing a product—such as dimensions, thickness, frame type, glass type, and color...</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Do I need to create an account before ordering?</h3>
                <p class="faq-answer">Yes, you must have an account to place an order. This allows us to save your design, send updates, and store your order history securely.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">What if I need to order multiple items with different specifications?</h3>
                <p class="faq-answer">You can add multiple customized items to your cart individually. Each one can have its own dimensions and specifications before finalizing your order.</p>
            </div>

            <div class="faq-card">
                <h3 class="faq-question">Are there limitations to the types of customization I can make?</h3>
                <p class="faq-answer">Yes. While you can adjust size, thickness, frame type, glass type, and color, advanced design features like engravings or curved shapes are not supported...</p>
            </div>

            <div class="faq-card single">
                <h3 class="faq-question">Can I review my design before placing the order?</h3>
                <p class="faq-answer">Yes. After customizing your product, the system will display a summary page showing your 2D design, selected specifications, and estimated cost...</p>
            </div>
        </div>
    </div>

