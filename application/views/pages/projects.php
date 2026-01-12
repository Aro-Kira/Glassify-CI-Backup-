<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/pages/projects_style.css'); ?>">



<!-- Projects Intro -->
<section class="projects-intro">
    <h1>Our Projects</h1>
    <p class="tagline">Innovating Spaces, Building Dreams.</p>
    <p class="description">
        Explore a selection of our completed glass and aluminum fabrication projects across Caloocan City and
        beyond.
    </p>
</section>
<style>
    /* Featured Projects Section */
    .featured-projects {
        background: #083c5d;
        color: white;
        padding: 5rem 1rem;
        text-align: center;
        max-width: 1400px;
        height: 14 00px;
        margin: 0 auto 1rem auto;
        -webkit-mask-image: url('<?php echo base_url('assets/images/img-page/jagged\ rectangle.svg'); ?>');
        mask-image: url('<?php echo base_url('assets/images/img-page/jagged\ rectangle.svg'); ?>');
        -webkit-mask-repeat: no-repeat;
        mask-repeat: no-repeat;
        -webkit-mask-size: 100% auto;
        mask-size: 100% auto;

    }
</style>

<!-- Featured Projects -->
<section class="featured-projects">
    <h2>Featured Projects</h2>
    <div class="projects-wrapper">
        <!-- Left Column -->
        <div class="projects-left">
            <div class="project-card">
                <img src="<?php echo base_url('assets/images/img-page/Gab.svg'); ?>" alt="GAB Chairman’s Office">
            </div>
            <h3>SM Caloocan City</h3>
            <p class="date">May 16, 2024</p>
            <div class="project-card">
                <img src="<?php echo base_url('assets/images/img-page/Residential.svg'); ?>" alt="Residential Project">
            </div>

        </div>

        <!-- Divider Line -->
        <div class="divider"></div>

        <!-- Right Column -->
        <div class="projects-right">
            <h3>GAB - Chairman’s Office</h3>
            <p class="date">September 06, 2024</p>
            <div class="project-card">
                <img src="<?php echo base_url('assets/images/img-page/sm-caloocan.svg'); ?>" alt="SM Caloocan City">
            </div>
            <h3>Residential Project</h3>
            <p class="date">October 19, 2024</p>
        </div>
    </div>
</section>


<!-- Before & After -->
<section class="before-after">
    <div class="before">
        <div class="border-box">
            <img src="<?php echo base_url('assets/images/img-page/mdi_pen.svg'); ?>" alt="topLeft-Dec"
                class="top-left-decor">
            <img src="<?php echo base_url('assets/images/img-page/before.svg'); ?>" alt="Before Design"
                class="img-panel">
            <img src="<?php echo base_url('assets/images/img-page/scroll.svg'); ?>" alt="topLeft-Dec"
                class="bottom-left-decor">
        </div>
        <p>Before</p>
    </div>

    <div class="after">
        <div class="border-box">
            <img src="<?php echo base_url('assets/images/img-page/fling-top.svg'); ?>" alt="topLeft-Dec"
                class="top-right-decor">
            <img src="<?php echo base_url('assets/images/img-page/after.svg'); ?>" alt="After Outcome" class="img-panel">
            <img src="<?php echo base_url('assets/images/img-page/fling-bottom.svg'); ?>" alt="topLeft-Dec"
                class="bottom-right-decor">
        </div>
        <p>After</p>
    </div>
</section>


<!-- Contact -->
<section id="contact" class="contact-section">
    <div class="contact-content">
        <!-- Left Side: Info -->
        <div class="contact-info">
            <h2>Contact Us!</h2>
            <p>
                We're eager to discuss your next glass or aluminum project.
                Our team is ready to assist you!
            </p>

            <div class="info-item">
                <span class="icon"><img src="<?php echo base_url('assets/images/img-page/gg_phone.svg'); ?>"
                        alt="phone_icon"></span>
                <div>
                    <p>0906 464 9709 / 0927 519 3800 <br> 0976 165 3506</p>
                </div>
            </div>

            <div class="info-item">
                <span class="icon"><img src="<?php echo base_url('assets/images/img-page/ic_outline-facebook.svg'); ?>"
                        alt="fb-icon"></span>
                <a href="https://www.facebook.com/glassworthbuilders" target="_blank" rel="noopener noreferrer">GlassWorth Builders</a>
            </div>

            <div class="info-item">
                <span class="icon"><img src="<?php echo base_url('assets/images/img-page/weui_email-filled.svg'); ?>"
                        alt="email-icon"></span>
                <p>glassworthbuilders@gmail.com</p>
            </div>
        </div>

        <!-- Right Side: Form -->
        <form class="contact-form" method="post" action="<?php echo base_url('quote-request'); ?>">
            <div class="form-group half">
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="first-name" required>
            </div>

            <div class="form-group half">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last-name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="needs">Briefly describe your needs</label>
                <input type="text" id="needs" name="needs" required>
            </div>

            <div class="form-group">
                <textarea id="message" name="message" placeholder="Message"></textarea>
            </div>

            <button type="submit">Inquire</button>
        </form>

    </div>
</section>

<!-- Modal Popup for Messages -->
<div id="messageModal" class="message-modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <div class="modal-body">
            <div id="modalMessage"></div>
        </div>
    </div>
</div>

<style>
    /* Modal Popup Styles */
    .message-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease;
    }

    .message-modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: #fff;
        margin: auto;
        padding: 0;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        animation: slideDown 0.3s ease;
        position: relative;
    }

    .modal-close {
        position: absolute;
        right: 15px;
        top: 15px;
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
        transition: color 0.3s;
    }

    .modal-close:hover,
    .modal-close:focus {
        color: #000;
    }

    .modal-body {
        padding: 40px 30px 30px;
        text-align: center;
    }

    .modal-body.success {
        color: #155724;
    }

    .modal-body.error {
        color: #721c24;
    }

    .modal-body .icon {
        font-size: 48px;
        margin-bottom: 20px;
    }

    .modal-body.success .icon {
        color: #28a745;
    }

    .modal-body.error .icon {
        color: #dc3545;
    }

    .modal-body p {
        margin: 0;
        font-size: 16px;
        line-height: 1.6;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<script>
    // Show modal if there's a flashdata message
    <?php if ($this->session->flashdata('success')): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showModal('<?php echo addslashes($this->session->flashdata('success')); ?>', 'success');
        });
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('error')): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showModal('<?php echo addslashes($this->session->flashdata('error')); ?>', 'error');
        });
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('email_debug')): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var debugMsg = 'Email Debug Info:<br><?php echo addslashes($this->session->flashdata('email_debug')); ?><br><small>Check <a href="<?php echo base_url('test_email'); ?>" target="_blank">test_email.php</a> for detailed testing.</small>';
            showModal(debugMsg, 'error');
        });
    <?php endif; ?>

    function showModal(message, type) {
        var modal = document.getElementById('messageModal');
        var modalMessage = document.getElementById('modalMessage');
        var modalBody = modal.querySelector('.modal-body');
        
        // Add icon based on type
        var icon = '';
        if (type === 'success') {
            icon = '<div class="icon" style="font-size: 48px; margin-bottom: 15px; color: #28a745;">✓</div>';
        } else if (type === 'error') {
            icon = '<div class="icon" style="font-size: 48px; margin-bottom: 15px; color: #dc3545;">✗</div>';
        }
        
        // Set message with icon
        modalMessage.innerHTML = icon + '<p>' + message + '</p>';
        
        // Set type class
        modalBody.className = 'modal-body ' + type;
        
        // Show modal
        modal.classList.add('show');
        
        // Auto-close after 5 seconds
        setTimeout(function() {
            closeModal();
        }, 5000);
    }

    function closeModal() {
        var modal = document.getElementById('messageModal');
        modal.classList.remove('show');
    }

    // Close modal when clicking the X
    document.addEventListener('DOMContentLoaded', function() {
        var closeBtn = document.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        
        // Close modal when clicking outside
        var modal = document.getElementById('messageModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    });
</script>