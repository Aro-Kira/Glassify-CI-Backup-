
<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/order_tracking.css'); ?>">

<div class="order-status-page">
    <?php if ($order): ?>
        <?php 
        // Check if this is a Site Assessment order
        $order_type = strtolower(trim($order->OrderType ?? ''));
        $is_site_assessment = (
            $order_type === 'site-assessed' || 
            $order_type === 'site assessment' || 
            $order_type === 'site-assessed order'
        );
        
        $status_lower = strtolower(trim($order->Status));
        $is_cancelled = ($status_lower === 'cancelled' || $status_lower === 'returned');
        $is_completed = ($status_lower === 'completed' || $status_lower === 'delivered');
        $is_ongoing = !$is_cancelled && !$is_completed;
        
        // Map Site Assessment status to display labels and messages
        $site_assessment_status_map = [];
        if ($is_site_assessment) {
            // Get payment status for checking balance due
            $payment_status = strtolower(trim($order->PaymentStatus ?? ''));
            $has_balance = ($payment_status === 'partial' || $payment_status === 'pending');
            
            // Check if ocular visit is completed
            $ocular_completed = ($progress['ocular_visit'] ?? 'pending') === 'completed';
            
            // Check if quotation file exists
            $has_quotation = !empty($order->QuotationPDFUrl ?? '');
            
            switch ($status_lower) {
                case 'pending booking confirmation':
                    $site_assessment_status_map = [
                        'label' => 'Booking Submitted',
                        'message' => 'Your site assessment booking has been submitted and is awaiting admin confirmation. Payment is not available yet.',
                        'show_payment' => false
                    ];
                    break;
                case 'approved':
                case 'booking confirmed':
                    // Check if quotation is available (has QuotationPDFUrl)
                    if ($has_quotation) {
                        $site_assessment_status_map = [
                            'label' => 'Quotation Available',
                            'message' => 'Your quotation is ready for review. Please accept it to proceed.',
                            'show_payment' => false,
                            'show_accept_quotation' => true
                        ];
                    } elseif ($ocular_completed) {
                        $site_assessment_status_map = [
                            'label' => 'Ocular Visit Completed – Preparing Quotation',
                            'message' => 'The ocular visit has been completed. We are preparing your quotation.',
                            'show_payment' => false
                        ];
                    } else {
                        $site_assessment_status_map = [
                            'label' => 'Booking Confirmed – Waiting for Ocular Visit',
                            'message' => 'Your booking has been confirmed. We will schedule an ocular visit soon.',
                            'show_payment' => false
                        ];
                    }
                    break;
                case 'quotation available':
                case 'quotation ready':
                case 'ready for quotation':
                    $site_assessment_status_map = [
                        'label' => 'Quotation Available',
                        'message' => 'Your quotation is ready for review. Please accept it to proceed.',
                        'show_payment' => false,
                        'show_accept_quotation' => true
                    ];
                    break;
                case 'awaiting payment':
                case 'pending payment':
                    $site_assessment_status_map = [
                        'label' => 'Awaiting Payment',
                        'message' => 'Please proceed with payment to continue with your order.',
                        'show_payment' => true,
                        'show_pay_now' => true
                    ];
                    break;
                case 'in fabrication':
                    $site_assessment_status_map = [
                        'label' => 'Payment Received – In Fabrication',
                        'message' => 'Payment has been received. Your order is now in fabrication.',
                        'show_payment' => false
                    ];
                    break;
                case 'ready for installation':
                    // Check if installation is completed
                    $installation_completed = ($progress['installed'] ?? 'pending') === 'completed';
                    if ($installation_completed && $has_balance) {
                        $site_assessment_status_map = [
                            'label' => 'Installation Completed – Balance Due',
                            'message' => 'Installation has been completed. Please pay the final payment.',
                            'show_payment' => true,
                            'show_pay_final' => true
                        ];
                    } else {
                        $site_assessment_status_map = [
                            'label' => 'Ready for Installation',
                            'message' => 'Your order is ready for installation.',
                            'show_payment' => false
                        ];
                    }
                    break;
                case 'installation completed':
                    if ($has_balance) {
                        $site_assessment_status_map = [
                            'label' => 'Installation Completed – Balance Due',
                            'message' => 'Installation has been completed. Please pay the final payment.',
                            'show_payment' => true,
                            'show_pay_final' => true
                        ];
                    } else {
                        $site_assessment_status_map = [
                            'label' => 'Installation Completed',
                            'message' => 'Installation has been completed.',
                            'show_payment' => false
                        ];
                    }
                    break;
                default:
                    // For other statuses, use generic booking message
                    $site_assessment_status_map = [
                        'label' => ucfirst(str_replace('-', ' ', $status_lower)),
                        'message' => 'Your site assessment order is being processed.',
                        'show_payment' => false
                    ];
            }
        }
        ?>
        <!-- Title -->
        <section class="order-header">
            <h2>Order Status</h2>
            <p>Order ID: <span><?= htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT)) ?></span></p>
            <div class="divider"></div>
        </section>

        <!-- Status Message Section -->
        <div class="status-message-container" style="margin-bottom: 30px;">
            <?php if ($is_cancelled): ?>
                <div class="status-card cancelled" style="background: #fff5f5; border-left: 5px solid #d9534f; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="color: #d9534f; margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-times-circle"></i> Order Cancelled
                    </h3>
                    <div style="background: white; padding: 15px; border-radius: 6px; border: 1px solid #fed7d7;">
                        <p style="margin: 0 0 5px 0; font-weight: 600; color: #4a5568;">Reason for Cancellation:</p>
                        <p style="margin: 0; color: #2d3748; line-height: 1.5;"><?= htmlspecialchars($order->DisapprovedReason ?: 'No reason provided by the administrator.') ?></p>
                    </div>
                    <p style="margin: 15px 0 0 0; color: #718096; font-size: 0.9em;">If you believe this is an error, please contact our support team at glassify@support.com</p>
                </div>
            <?php elseif ($is_completed): ?>
                <div class="status-card completed" style="background: #f0fff4; border-left: 5px solid #38a169; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="color: #38a169; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle"></i> Order Successfully Completed
                    </h3>
                    <p style="color: #2d3748; margin: 0 0 15px 0;">Your order has been fully processed, delivered, and installed. We hope you are satisfied with our service!</p>
                    <div style="display: flex; gap: 20px; font-size: 0.9em; color: #4a5568;">
                        <span><strong>Completed On:</strong> <?= date('F j, Y', strtotime($order->Updated_Date ?? $order->OrderDate)) ?></span>
                        <span><strong>Payment Status:</strong> <span style="color: #38a169; font-weight: 600;">Paid</span></span>
                    </div>
                </div>
            <?php else: ?>
                <?php if ($is_site_assessment && !empty($site_assessment_status_map)): ?>
                    <div class="status-card ongoing" style="background: #ebf8ff; border-left: 5px solid #3182ce; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h3 style="color: #3182ce; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-sync fa-spin"></i> <?= htmlspecialchars($site_assessment_status_map['label']) ?>
                        </h3>
                        <p style="color: #2d3748; margin: 0 0 15px 0;"><?= htmlspecialchars($site_assessment_status_map['message']) ?></p>
                        
                        <?php if (!empty($site_assessment_status_map['show_accept_quotation'])): ?>
                            <button onclick="acceptQuotation(<?= $order->OrderID ?>)" 
                                    style="background: #3182ce; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: all 0.2s;"
                                    onmouseover="this.style.background='#2c5282'" 
                                    onmouseout="this.style.background='#3182ce'">
                                Accept Quotation
                            </button>
                        <?php elseif (!empty($site_assessment_status_map['show_pay_now'])): ?>
                            <button onclick="window.location.href='<?= base_url('payment?order=' . $order->OrderID) ?>'" 
                                    style="background: #3182ce; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: all 0.2s;"
                                    onmouseover="this.style.background='#2c5282'" 
                                    onmouseout="this.style.background='#3182ce'">
                                Pay Now
                            </button>
                        <?php elseif (!empty($site_assessment_status_map['show_pay_final'])): ?>
                            <button onclick="window.location.href='<?= base_url('payment?order=' . $order->OrderID . '&type=final') ?>'" 
                                    style="background: #3182ce; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: all 0.2s;"
                                    onmouseover="this.style.background='#2c5282'" 
                                    onmouseout="this.style.background='#3182ce'">
                                Pay Final Payment
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="status-card ongoing" style="background: #ebf8ff; border-left: 5px solid #3182ce; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h3 style="color: #3182ce; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-sync fa-spin"></i> Your Order is In Progress
                        </h3>
                        <p style="color: #2d3748; margin: 0 0 15px 0;">We are working on your order. You can view the real-time status updates in the timeline below.</p>
                        <div style="background: rgba(255,255,255,0.5); padding: 12px 15px; border-radius: 6px; border: 1px dashed #bee3f8;">
                            <span style="color: #2b6cb0;"><strong>Current Step:</strong> 
                                <?php
                                if ($progress['completed'] === 'in_progress') echo "Final Delivery & Completion";
                                elseif ($progress['installed'] === 'in_progress') echo "Installation in progress";
                                elseif ($progress['in_fabrication'] === 'in_progress') echo "Fabricating your custom products";
                                elseif ($progress['ocular_visit'] === 'in_progress') echo "Preparing for ocular visit";
                                else echo "Order validation & payment verification";
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Order Info -->
        <section class="order-info">
            <div>
                <h4>Order ID:</h4>
                <p><?= htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT)) ?></p>
            </div>
            <div>
                <h4>Payment Method:</h4>
                <?php 
                // Determine payment method: if ReceiptPath exists, it's E-Wallet
                $detected_payment_method = 'Cash on Delivery';
                if (isset($payment) && !empty($payment->ReceiptPath)) {
                    $detected_payment_method = 'E-Wallet';
                }
                ?>
                <p><?= $detected_payment_method ?></p>
            </div>
            <div>
                <h4>Transaction ID:</h4>
                <p>TXN<?= date('Ymd', strtotime($order->OrderDate)) . str_pad($order->OrderID, 6, '0', STR_PAD_LEFT) ?></p>
            </div>
            <div>
                <h4>Estimated Date:</h4>
                <p><?= date('F j, Y', strtotime($order->EstimatedDelivery)) ?></p>
            </div>
        </section>

        <!-- Order Progress -->
        <?php if (!$is_cancelled): ?>
        <?php
        // Determine if this is a Direct Order (not Site Assessment)
        $is_direct_order = !$is_site_assessment;
        
        // For Direct Orders: Calculate payment status
        $payment_status = strtolower(trim($order->PaymentStatus ?? 'pending'));
        $is_paid = ($payment_status === 'paid' || $payment_status === 'partial');
        $payment_date = null;
        if (isset($payment) && $payment->Payment_Date) {
            $payment_date = $payment->Payment_Date;
        } elseif (isset($payment) && $payment->PaymentDate) {
            $payment_date = $payment->PaymentDate;
        }
        
        if ($is_direct_order) {
            // Direct Order: Order Placed → Paid → In Fabrication → Completed (4 steps)
            $progress_percent = 0;
            if ($progress['order_placed'] === 'completed' || $progress['order_placed'] === 'in_progress') {
                $progress_percent = 0;
            }
            // Paid step (33%)
            if ($is_paid) {
                $progress_percent = 33;
            }
            // In Fabrication step (66%)
            if ($progress['in_fabrication'] === 'completed' || $progress['in_fabrication'] === 'in_progress') {
                $progress_percent = 66;
            }
            // Completed step (100%)
            if ($progress['completed'] === 'completed' || $progress['completed'] === 'in_progress') {
                $progress_percent = 100;
            }
            
            // Ensure line extends fully to in-progress steps
            if ($progress['completed'] === 'in_progress') {
                if ($progress['in_fabrication'] !== 'completed') $progress['in_fabrication'] = 'completed';
                $progress_percent = 100;
            }
            if ($progress['in_fabrication'] === 'in_progress') {
                if (!$is_paid) $is_paid = true; // Mark as paid if fabrication started
                $progress_percent = 66;
            }
        } else {
            // Site Assessment Order: Booking Submitted → Ocular Visit → In Fabrication → Installed → Completed (5 steps)
            $progress_percent = 0;
            if ($progress['order_placed'] === 'completed' || $progress['order_placed'] === 'in_progress') {
                $progress_percent = 0;
            }
            if ($progress['ocular_visit'] === 'completed' || $progress['ocular_visit'] === 'in_progress') {
                $progress_percent = 25;
            }
            if ($progress['in_fabrication'] === 'completed' || $progress['in_fabrication'] === 'in_progress') {
                $progress_percent = 50;
            }
            if ($progress['installed'] === 'completed' || $progress['installed'] === 'in_progress') {
                $progress_percent = 75;
            }
            if ($progress['completed'] === 'completed' || $progress['completed'] === 'in_progress') {
                $progress_percent = 100;
            }
            
            // Ensure line extends fully to in-progress steps
            if ($progress['installed'] === 'in_progress') {
                if ($progress['in_fabrication'] !== 'completed') $progress['in_fabrication'] = 'completed';
                if ($progress['ocular_visit'] !== 'completed') $progress['ocular_visit'] = 'completed';
                $progress_percent = 75;
            }
            if ($progress['completed'] === 'in_progress') {
                if ($progress['installed'] !== 'completed') $progress['installed'] = 'completed';
                if ($progress['in_fabrication'] !== 'completed') $progress['in_fabrication'] = 'completed';
                if ($progress['ocular_visit'] !== 'completed') $progress['ocular_visit'] = 'completed';
                $progress_percent = 100;
            }
            if ($progress['in_fabrication'] === 'in_progress') {
                if ($progress['ocular_visit'] !== 'completed') $progress['ocular_visit'] = 'completed';
                $progress_percent = 50;
            }
        }
        
        // Check if any step is in progress (for progress bar color)
        $has_in_progress = false;
        foreach ($progress as $step_status) {
            if ($step_status === 'in_progress') {
                $has_in_progress = true;
                break;
            }
        }
        
        // Helper function to get step status class
        if (!function_exists('get_step_class')) {
            function get_step_class($status) {
                if ($status === 'completed') return 'completed';
                if ($status === 'in_progress') return 'in-progress';
                return 'pending';
            }
        }
        ?>
        <section class="order-progress <?= $has_in_progress ? 'has-in-progress' : '' ?>" style="--progress-width: <?= $progress_percent ?>%;">
            <!-- Step 1: Order Placed / Booking Submitted -->
            <div class="step <?= get_step_class($progress['order_placed']) ?>">
                <img src="<?php echo base_url('assets/images/img-page/checkout_track.svg'); ?>" class="order-icon" alt="checkout">
                <p><?= $is_site_assessment ? 'Booking Submitted' : 'Order Placed' ?></p>
                <?php if ($progress['order_placed'] === 'completed'): ?>
                    <span class="icon"><img src="<?php echo base_url('assets/images/img-page/check-track.png'); ?>" alt="check"></span>
                    <small><?= date('M j, Y', strtotime($order->OrderDate)) ?><br><?= date('g:i A', strtotime($order->OrderDate)) ?></small>
                <?php elseif ($progress['order_placed'] === 'in_progress'): ?>
                    <span class="icon"></span>
                    <small><?= date('M j, Y', strtotime($order->OrderDate)) ?><br>In Progress</small>
                <?php else: ?>
                    <span class="icon"></span>
                    <small>Pending</small>
                <?php endif; ?>
            </div>
            
            <?php if ($is_direct_order): ?>
                <!-- Direct Order: Step 2 - Paid -->
                <div class="step <?= $is_paid ? 'completed' : 'pending' ?>">
                    <img src="<?php echo base_url('assets/images/img-page/payment_track.svg'); ?>" class="order-icon" alt="payment" onerror="this.src='<?php echo base_url('assets/images/img-page/checkout_track.svg'); ?>'">
                    <p>Paid</p>
                    <?php if ($is_paid): ?>
                        <span class="icon"><img src="<?php echo base_url('assets/images/img-page/check-track.png'); ?>" alt="check"></span>
                        <?php if ($payment_date): ?>
                            <small><?= date('M j, Y', strtotime($payment_date)) ?><br>Completed</small>
                        <?php else: ?>
                            <small>Completed</small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="icon"></span>
                        <small>Pending</small>
                    <?php endif; ?>
                </div>
                
                <!-- Direct Order: Step 3 - In Fabrication -->
                <div class="step <?= get_step_class($progress['in_fabrication']) ?>">
                    <img src="<?php echo base_url('assets/images/img-page/package_track.svg'); ?>" class="order-icon" alt="fabrication">
                    <p>In Fabrication</p>
                    <?php if ($progress['in_fabrication'] === 'completed'): ?>
                        <span class="icon"><img src="<?php echo base_url('assets/images/img-page/check-track.png'); ?>" alt="check"></span>
                        <small><?= date('M j, Y', strtotime($order->FabricationDate)) ?><br>Completed</small>
                    <?php elseif ($progress['in_fabrication'] === 'in_progress'): ?>
                        <span class="icon"></span>
                        <small><?= date('M j, Y', strtotime($order->FabricationDate)) ?><br>In Progress</small>
                    <?php else: ?>
                        <span class="icon"></span>
                        <small>Expected<br><?= date('M j, Y', strtotime($order->FabricationDate)) ?></small>
                    <?php endif; ?>
                </div>
                
                <!-- Direct Order: Step 4 - Completed -->
                <div class="step <?= get_step_class($progress['completed']) ?>">
                    <img src="<?php echo base_url('assets/images/img-page/delivered_track.svg'); ?>" class="order-icon" alt="delivery">
                    <p>Completed</p>
                    <?php if ($progress['completed'] === 'completed'): ?>
                        <span class="icon"><img src="<?php echo base_url('assets/images/img-page/check-track.png'); ?>" alt="check"></span>
                        <small><?= date('M j, Y', strtotime($order->EstimatedDelivery)) ?><br>Completed</small>
                    <?php elseif ($progress['completed'] === 'in_progress'): ?>
                        <span class="icon"></span>
                        <small><?= date('M j, Y', strtotime($order->EstimatedDelivery)) ?><br>In Progress</small>
                    <?php else: ?>
                        <span class="icon"></span>
                        <small>Expected<br><?= date('M j, Y', strtotime($order->EstimatedDelivery)) ?></small>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Site Assessment Order: Step 2 - Ocular Visit -->
                <div class="step <?= get_step_class($progress['ocular_visit']) ?>">
                    <img src="<?php echo base_url('assets/images/img-page/ocular_track.svg'); ?>" class="order-icon" alt="ocular visit">
                    <p>Ocular Visit</p>
                    <?php if ($progress['ocular_visit'] === 'completed'): ?>
                        <span class="icon"><img src="<?php echo base_url('assets/images/img-page/check-track.png'); ?>" alt="check"></span>
                        <small><?= date('M j, Y', strtotime($order->OcularDate)) ?><br>Completed</small>
                    <?php elseif ($progress['ocular_visit'] === 'in_progress'): ?>
                        <span class="icon"></span>
                        <small><?= date('M j, Y', strtotime($order->OcularDate)) ?><br>In Progress</small>
                    <?php else: ?>
                        <span class="icon"></span>
                        <small>Expected<br><?= date('M j, Y', strtotime($order->OcularDate)) ?></small>
                    <?php endif; ?>
                </div>
                
                <!-- Site Assessment Order: Step 3 - In Fabrication -->
                <div class="step <?= get_step_class($progress['in_fabrication']) ?>">
                    <img src="<?php echo base_url('assets/images/img-page/package_track.svg'); ?>" class="order-icon" alt="fabrication">
                    <p>In Fabrication</p>
                    <?php if ($progress['in_fabrication'] === 'completed'): ?>
                        <span class="icon"><img src="<?php echo base_url('assets/images/img-page/check-track.png'); ?>" alt="check"></span>
                        <small><?= date('M j, Y', strtotime($order->FabricationDate)) ?><br>Completed</small>
                    <?php elseif ($progress['in_fabrication'] === 'in_progress'): ?>
                        <span class="icon"></span>
                        <small><?= date('M j, Y', strtotime($order->FabricationDate)) ?><br>In Progress</small>
                    <?php else: ?>
                        <span class="icon"></span>
                        <small>Expected<br><?= date('M j, Y', strtotime($order->FabricationDate)) ?></small>
                    <?php endif; ?>
                </div>
                
                <!-- Site Assessment Order: Step 4 - Installed -->
                <div class="step <?= get_step_class($progress['installed']) ?>">
                    <img src="<?php echo base_url('assets/images/img-page/window_track.svg'); ?>" class="order-icon" alt="Installation">
                    <p>Installed</p>
                    <?php if ($progress['installed'] === 'completed'): ?>
                        <span class="icon"><img src="<?php echo base_url('assets/images/img-page/check-track.png'); ?>" alt="check"></span>
                        <small><?= date('M j, Y', strtotime($order->InstallationDate)) ?><br>Completed</small>
                    <?php elseif ($progress['installed'] === 'in_progress'): ?>
                        <span class="icon"></span>
                        <small><?= date('M j, Y', strtotime($order->InstallationDate)) ?><br>In Progress</small>
                    <?php else: ?>
                        <span class="icon"></span>
                        <small>Expected<br><?= date('M j, Y', strtotime($order->InstallationDate)) ?></small>
                    <?php endif; ?>
                </div>
                
                <!-- Site Assessment Order: Step 5 - Completed -->
                <div class="step <?= get_step_class($progress['completed']) ?>">
                    <img src="<?php echo base_url('assets/images/img-page/delivered_track.svg'); ?>" class="order-icon" alt="delivery">
                    <p>Completed</p>
                    <?php if ($progress['completed'] === 'completed'): ?>
                        <span class="icon"><img src="<?php echo base_url('assets/images/img-page/check-track.png'); ?>" alt="check"></span>
                        <small><?= date('M j, Y', strtotime($order->EstimatedDelivery)) ?><br>Completed</small>
                    <?php elseif ($progress['completed'] === 'in_progress'): ?>
                        <span class="icon"></span>
                        <small><?= date('M j, Y', strtotime($order->EstimatedDelivery)) ?><br>In Progress</small>
                    <?php else: ?>
                        <span class="icon"></span>
                        <small>Expected<br><?= date('M j, Y', strtotime($order->EstimatedDelivery)) ?></small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <!-- Products Table -->
        <section class="order-products">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Customization</th>
                        <th>Price</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($order_items)): ?>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <?php 
                                        // Get product image (not 2D customization image)
                                        $image_raw = $item->ImageUrl ?? '';
                                        $placeholder_svg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                                        $product_img = $placeholder_svg;
                                        
                                        if (!empty($image_raw)) {
                                            $decoded = json_decode($image_raw, true);
                                            $first_image = '';
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                                                $first_image = $decoded[0];
                                            } else {
                                                $first_image = $image_raw;
                                            }
                                            
                                            if (!empty($first_image) && strpos($first_image, 'broken-image-icon') === false) {
                                                if (strpos($first_image, 'http') === 0) {
                                                    $product_img = $first_image;
                                                } else if (strpos($first_image, 'assets/') === 0 || strpos($first_image, 'uploads/') === 0) {
                                                    $product_img = base_url($first_image);
                                                } else {
                                                    $product_img = base_url('uploads/products/' . basename($first_image));
                                                }
                                            }
                                        }
                                        ?>
                                        <img src="<?= $product_img ?>" alt="<?= htmlspecialchars($item->ProductName ?? 'Product') ?>" class="cart-product-img" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; flex-shrink: 0;">
                                        <span style="font-weight: 600; color: #0f2b46;"><?= htmlspecialchars($item->ProductName ?? 'Unknown Product') ?></span>
                                    </div>
                                </td>
                                <td class="customization-info">
                                    <?php 
                                    // Check if item has any customization data
                                    $has_customization = !empty($item->Dimensions) || !empty($item->GlassType) || 
                                                        !empty($item->GlassThickness) || !empty($item->GlassShape) || 
                                                        !empty($item->EdgeWork) || !empty($item->FrameType) || 
                                                        !empty($item->DesignRef);
                                    ?>
                                    <?php if ($has_customization): ?>
                                        <div class="custom-layout">
                                            <?php if (!empty($item->DesignRef)): ?>
                                                <div class="design-thumbnail-wrapper">
                                                    <img src="<?= base_url($item->DesignRef) ?>" 
                                                         alt="Custom Design" 
                                                         class="design-thumbnail"
                                                         onclick="showDesignModal('<?= base_url($item->DesignRef) ?>')"
                                                         onerror="this.style.display='none'; this.parentElement.querySelector('.view-design-text').textContent='Image not found';">
                                                    <span class="view-design-text">Click to view</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="custom-details">
                                                <?php if (!empty($item->Dimensions)): ?>
                                                    <span class="custom-tag">Size: <?= htmlspecialchars($item->Dimensions) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->GlassShape)): ?>
                                                    <span class="custom-tag">Shape: <?= ucfirst(htmlspecialchars($item->GlassShape)) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->GlassType)): ?>
                                                    <span class="custom-tag">Type: <?= ucfirst(htmlspecialchars($item->GlassType)) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->GlassThickness)): ?>
                                                    <span class="custom-tag">Thickness: <?= htmlspecialchars($item->GlassThickness) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->EdgeWork)): ?>
                                                    <span class="custom-tag">Edge: <?= ucfirst(str_replace('-', ' ', htmlspecialchars($item->EdgeWork))) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->FrameType)): ?>
                                                    <span class="custom-tag">Frame: <?= ucfirst(htmlspecialchars($item->FrameType)) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->Engraving) && $item->Engraving !== 'None'): ?>
                                                    <span class="custom-tag engraving-tag">Engraving: <?= htmlspecialchars($item->Engraving) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-custom">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td class="price">₱<?= number_format(($item->EstimatePrice ?? 0) * ($item->Quantity ?? 1), 2) ?></td>
                                <td><?= $item->Quantity ?? 1 ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="order-summary">
            <!-- Left side (big box) -->
            <div class="summary-box">
                <h4>Order Summary</h4>
                <p>Items: <span><?= $summary['items'] ?></span></p>
                <p>Subtotal: <span>₱<?= number_format($summary['subtotal'], 2) ?></span></p>
                <p>Shipping Fee: <span>₱<?= number_format($summary['shipping'], 2) ?></span></p>
                <p>Handling Fee: <span>₱<?= number_format($summary['handling'], 2) ?></span></p>
                <h3>Total: <span>₱<?= number_format($summary['total'], 2) ?></span></h3>
            </div>

            <!-- Right side (stacked addresses) -->
            <div class="addresses">
                <div class="address-box">
                    <h4>Shipping Address</h4>
                    <?php if ($shipping_address): ?>
                        <p><b><?= htmlspecialchars($order->First_Name . ' ' . $order->Last_Name) ?></b></p>
                        <p>(+63) <?= htmlspecialchars($order->PhoneNum ?? '') ?></p>
                        <p><?= htmlspecialchars($shipping_address->AddressLine ?? '') ?>,
                            <?= htmlspecialchars($shipping_address->City ?? '') ?>,<br>
                            <?= htmlspecialchars($shipping_address->Province ?? '') ?>,
                            <?= htmlspecialchars($shipping_address->Country ?? 'Philippines') ?>
                            <?= htmlspecialchars($shipping_address->ZipCode ?? '') ?></p>
                    <?php elseif ($order->DeliveryAddress): ?>
                        <p><b><?= htmlspecialchars($order->First_Name . ' ' . $order->Last_Name) ?></b></p>
                        <p>(+63) <?= htmlspecialchars($order->PhoneNum ?? '') ?></p>
                        <p><?= htmlspecialchars($order->DeliveryAddress) ?></p>
                    <?php else: ?>
                        <p>No shipping address provided.</p>
                    <?php endif; ?>
                </div>

                <div class="address-box">
                    <h4>Billing Address</h4>
                    <?php if ($billing_address): ?>
                        <p><?= htmlspecialchars($billing_address->AddressLine ?? '') ?>,
                            <?= htmlspecialchars($billing_address->City ?? '') ?>,
                            <?= htmlspecialchars($billing_address->Province ?? '') ?></p>
                    <?php else: ?>
                        <p>Same as shipping address</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    <?php else: ?>
        <!-- No Order Found -->
        <section class="order-header">
            <h2>Order Not Found</h2>
            <p>The order you are looking for does not exist or you don't have permission to view it.</p>
            <div class="divider"></div>
            <a href="<?= base_url('list_products') ?>" class="btn-back">← Back to My Purchases</a>
        </section>
    <?php endif; ?>
</div>

<!-- Design Preview Modal -->
<div id="designModal" class="design-modal">
    <div class="design-modal-overlay" onclick="closeDesignModal()"></div>
    <div class="design-modal-content">
        <button class="design-modal-close" onclick="closeDesignModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="design-modal-header">
            <h3>Custom Design Layout</h3>
            <p>This design is included in your order</p>
        </div>
        <div class="design-modal-body">
            <img id="designModalImage" src="" alt="Custom Design">
        </div>
        <div class="design-modal-footer">
            <button class="btn-download-design" onclick="downloadDesignImage()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Download Design
            </button>
        </div>
    </div>
</div>

<script>
// Design Modal Functions
function showDesignModal(imageSrc) {
    document.getElementById('designModalImage').src = imageSrc;
    document.getElementById('designModal').classList.add('active');
}

function closeDesignModal() {
    document.getElementById('designModal').classList.remove('active');
}

function downloadDesignImage() {
    const img = document.getElementById('designModalImage');
    const link = document.createElement('a');
    link.href = img.src;
    link.download = 'custom-design-' + Date.now() + '.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDesignModal();
    }
});

// Real-time order progress polling
(function() {
    const orderId = <?= $order ? $order->OrderID : 'null' ?>;
    const isDirectOrder = <?= isset($is_direct_order) && $is_direct_order ? 'true' : 'false' ?>;
    const isSiteAssessment = <?= isset($is_site_assessment) && $is_site_assessment ? 'true' : 'false' ?>;
    if (!orderId) return;
    
    let lastUpdateTime = Date.now();
    let pollingInterval = null;
    const POLL_INTERVAL = 10000; // Poll every 10 seconds
    
    function updateOrderProgress() {
        fetch(`<?php echo base_url('shopcon/get_order_progress_ajax'); ?>?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateProgressUI(data);
                    lastUpdateTime = Date.now();
                }
            })
            .catch(error => {
                console.error('Error fetching order progress:', error);
            });
    }
    
    function updateProgressUI(data) {
        const { progress, progress_percent, dates, order_status, has_in_progress, payment_status } = data;
        
        // Check if payment is completed for Direct Orders
        const isPaid = payment_status === 'paid' || payment_status === 'partial';
        
        // Ensure previous steps are marked as completed when a later step is in progress
        // This ensures the line connects properly
        if (isDirectOrder) {
            // Direct Order: Order Placed → Paid → In Fabrication → Completed
            if (progress.completed === 'in_progress') {
                if (progress.in_fabrication !== 'completed') progress.in_fabrication = 'completed';
            }
            if (progress.in_fabrication === 'in_progress') {
                // If fabrication is in progress, payment should be completed
                // This is handled by payment_status check
            }
        } else {
            // Site Assessment Order: Booking Submitted → Ocular Visit → In Fabrication → Installed → Completed
            if (progress.installed === 'in_progress') {
                if (progress.in_fabrication !== 'completed') progress.in_fabrication = 'completed';
                if (progress.ocular_visit !== 'completed') progress.ocular_visit = 'completed';
            }
            if (progress.completed === 'in_progress') {
                if (progress.installed !== 'completed') progress.installed = 'completed';
                if (progress.in_fabrication !== 'completed') progress.in_fabrication = 'completed';
                if (progress.ocular_visit !== 'completed') progress.ocular_visit = 'completed';
            }
            if (progress.in_fabrication === 'in_progress') {
                if (progress.ocular_visit !== 'completed') progress.ocular_visit = 'completed';
            }
        }
        
        // Recalculate progress percentage to include in-progress steps (so line connects)
        let calculatedProgress = 0;
        if (isDirectOrder) {
            // Direct Order: 4 steps (0%, 33%, 66%, 100%)
            if (progress.order_placed === 'completed' || progress.order_placed === 'in_progress') {
                calculatedProgress = 0;
            }
            if (isPaid) {
                calculatedProgress = 33;
            }
            if (progress.in_fabrication === 'completed' || progress.in_fabrication === 'in_progress') {
                calculatedProgress = 66;
            }
            if (progress.completed === 'completed' || progress.completed === 'in_progress') {
                calculatedProgress = 100;
            }
        } else {
            // Site Assessment Order: 5 steps (0%, 25%, 50%, 75%, 100%)
            if (progress.order_placed === 'completed' || progress.order_placed === 'in_progress') {
                calculatedProgress = 0;
            }
            if (progress.ocular_visit === 'completed' || progress.ocular_visit === 'in_progress') {
                calculatedProgress = 25;
            }
            if (progress.in_fabrication === 'completed' || progress.in_fabrication === 'in_progress') {
                calculatedProgress = 50;
            }
            if (progress.installed === 'completed' || progress.installed === 'in_progress') {
                calculatedProgress = 75;
            }
            if (progress.completed === 'completed' || progress.completed === 'in_progress') {
                calculatedProgress = 100;
            }
        }
        
        // Update progress bar width
        const progressSection = document.querySelector('.order-progress');
        if (progressSection) {
            progressSection.style.setProperty('--progress-width', calculatedProgress + '%');
            // Add class for in-progress styling if any step is in progress
            if (has_in_progress) {
                progressSection.classList.add('has-in-progress');
            } else {
                progressSection.classList.remove('has-in-progress');
            }
        }
        
        // Update each step (pass status string: 'pending', 'in_progress', or 'completed')
        if (isDirectOrder) {
            // Direct Order: Order Placed (0), Paid (1), In Fabrication (2), Completed (3)
            updateStep('order_placed', progress.order_placed, data.order_date, data.order_time, 0);
            updateStep('paid', isPaid ? 'completed' : 'pending', dates.payment_date, null, 1);
            updateStep('in_fabrication', progress.in_fabrication, dates.fabrication_date, null, 2);
            updateStep('completed', progress.completed, dates.estimated_delivery, null, 3);
        } else {
            // Site Assessment Order: Booking Submitted (0), Ocular Visit (1), In Fabrication (2), Installed (3), Completed (4)
            updateStep('order_placed', progress.order_placed, data.order_date, data.order_time, 0);
            updateStep('ocular_visit', progress.ocular_visit, dates.ocular_date, null, 1);
            updateStep('in_fabrication', progress.in_fabrication, dates.fabrication_date, null, 2);
            updateStep('installed', progress.installed, dates.installation_date, null, 3);
            updateStep('completed', progress.completed, dates.estimated_delivery, null, 4);
        }
    }
    
    function updateStep(stepName, status, date, time = null, stepIndex = null) {
        // If stepIndex is provided, use it directly; otherwise, map step names to indices
        if (stepIndex === null) {
            // Legacy mapping for backward compatibility (for Site Assessment Orders)
            const stepMap = {
                'order_placed': 0,
                'ocular_visit': 1,
                'in_fabrication': 2,
                'installed': 3,
                'completed': 4,
                'paid': 1 // For Direct Orders
            };
            stepIndex = stepMap[stepName];
        }
        
        if (stepIndex === undefined || stepIndex === null) return;
        
        const allSteps = document.querySelectorAll('.order-progress .step');
        const stepElement = allSteps[stepIndex];
        if (!stepElement) return;
        
        // Remove all status classes
        stepElement.classList.remove('completed', 'in-progress', 'pending');
        
        // Add appropriate status class
        if (status === 'completed') {
            stepElement.classList.add('completed');
        } else if (status === 'in_progress') {
            stepElement.classList.add('in-progress');
        } else {
            stepElement.classList.add('pending');
        }
        
        // Update icon
        const iconSpan = stepElement.querySelector('.icon');
        if (iconSpan) {
            if (status === 'completed') {
                iconSpan.innerHTML = '<img src="<?php echo base_url('assets/images/img-page/check-track.png'); ?>" alt="check">';
            } else {
                iconSpan.innerHTML = '';
            }
        }
        
        // Update date text
        const smallElement = stepElement.querySelector('small');
        if (smallElement) {
            if (status === 'completed' && date) {
                smallElement.innerHTML = date + (time ? '<br>' + time : '<br>Completed');
            } else if (status === 'in_progress' && date) {
                smallElement.innerHTML = date + '<br>In Progress';
            } else if (date) {
                smallElement.innerHTML = 'Expected<br>' + date;
            } else {
                smallElement.innerHTML = 'Pending';
            }
        }
    }
    
    // Start polling when page is visible
    function startPolling() {
        if (pollingInterval) return;
        pollingInterval = setInterval(updateOrderProgress, POLL_INTERVAL);
        updateOrderProgress(); // Initial update
    }
    
    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }
    
    // Use Page Visibility API to pause polling when tab is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
        }
    });
    
    // Start polling on page load
    if (document.visibilityState === 'visible') {
        startPolling();
    }
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', stopPolling);
})();
</script>

<script src="<?php echo base_url('js/order-status.js'); ?>"></script>

<script>
    // Accept Quotation function for Site Assessment orders
    function acceptQuotation(orderId) {
        if (!confirm('Are you sure you want to accept this quotation? You will be redirected to the payment page.')) {
            return;
        }
        
        // AJAX call to accept quotation
        fetch('<?= base_url('accept-quotation') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + orderId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Quotation accepted successfully! Redirecting to payment page...');
                window.location.href = '<?= base_url('payment') ?>?order=' + orderId;
            } else {
                alert('Error: ' + (data.message || 'Failed to accept quotation. Please try again.'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
</script>
