
<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/order_tracking.css'); ?>">

<div class="order-status-page">
    <?php if ($order): ?>
        <?php 
        // All orders now follow the unified site-assessment process (ocular visit required)
        // Order type distinction has been removed - all orders are treated the same
        $is_site_assessment = true;
        $is_direct_order = false;
        // Determine if current viewer/customer is a Beginner role. Use available context first,
        // then fall back to session user_role if present.
        $is_beginner = false;
        if (isset($customer_role) && strcasecmp($customer_role, 'beginner') === 0) {
            $is_beginner = true;
        } elseif ($this->session->userdata && strcasecmp($this->session->userdata('user_role') ?? '', 'beginner') === 0) {
            $is_beginner = true;
        } else {
            // Try to load the customer record (if order has Customer_ID) and check `role` column
            $cust_id = $order->Customer_ID ?? $this->session->userdata('customer_id');
            if (!empty($cust_id)) {
                $this->db->where('Customer_ID', $cust_id);
                $cust_row = $this->db->get('customer')->row();
                if ($cust_row && property_exists($cust_row, 'role') && strcasecmp($cust_row->role ?? '', 'beginner') === 0) {
                    $is_beginner = true;
                }
            }
        }
        
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
                        case 'pending review':
                        case 'awaiting admin':
                        case 'ready to approve':
                            $site_assessment_status_map = [
                                'label' => 'Booking Submitted',
                                'message' => 'Your site assessment booking has been submitted and is awaiting confirmation.',
                                'show_payment' => false
                            ];
                            break;
                case 'ocular pending':
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
                                'message' => 'Your site assessment booking has been submitted and is awaiting confirmation.',
                                'show_payment' => false
                            ];
            }
        }
        ?>
        <!-- Title -->
        <section class="order-header">
            <div class="back-btn">
                <a href="javascript:history.back()">
                    <img src="<?= base_url('assets/images/img-page/back_button.png'); ?>" alt="Back Icon">
                    <span>Back to My Purchases</span>
                </a>
            </div>
            <h2>Order Status</h2>
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
                        <p style="margin: 0; color: #2d3748; line-height: 1.5;">
                            <?= htmlspecialchars(isset($order->DisapprovalReason) && $order->DisapprovalReason ? $order->DisapprovalReason : 'No reason provided by the administrator.') ?>
                        </p>
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
                    <?php 
                        // Only show installation date change option AFTER:
                        // 1. Order has been moved to Complete column in Fabrication (status = 'ready for installation')
                        // 2. AND the order has been sent to Installation page (has 'Installed' appointment = progress['installed'] is 'in_progress')
                        // This ensures the button only appears when the admin has finished fabrication and scheduled the installation
                        $can_request_date_change = false;
                        $has_installation_appointment = ($progress['installed'] ?? 'pending') === 'in_progress';
                        if (!empty($order->InstallationDate) 
                            && $status_lower === 'ready for installation'
                            && $has_installation_appointment
                            && (($progress['installed'] ?? 'pending') !== 'completed')) {
                            $can_request_date_change = true;
                        }
                    ?>
                    <?php if ($can_request_date_change): ?>
                        <?php
                            // Determine if customer may request a date change (within 7 days of the current installation date)
                            $show_request_button = false;
                            if ($this->session->userdata('is_logged_in') && $this->session->userdata('user_role') === 'Customer') {
                                $session_customer = (int)$this->session->userdata('customer_id');
                                if (!empty($order->Customer_ID) && (int)$order->Customer_ID === $session_customer && !empty($order->InstallationDate)) {
                                    $installation_ts = strtotime($order->InstallationDate);
                                    $allowed_until_ts = $installation_ts + (7 * 24 * 60 * 60);
                                    if (time() <= $allowed_until_ts) {
                                        $show_request_button = true;
                                        $allowed_until = date('Y-m-d', $allowed_until_ts);
                                    }
                                }
                            }
                        ?>

                        <div class="status-card ongoing" style="background: #ebf8ff; border-left: 5px solid #3182ce; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                            <h3 style="color: #3182ce; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-sync fa-spin"></i> <?= htmlspecialchars($site_assessment_status_map['label']) ?>
                            </h3>
                            <p style="color: #2d3748; margin: 0 0 15px 0;"><?= htmlspecialchars($site_assessment_status_map['message']) ?></p>
                            
                            <div style="background: #fff; border-radius:8px; padding:16px; margin-top:16px; border:1px solid #e6edf6;">
                                <div style="font-size:13px; color:#6b7280; margin-bottom:8px;">Installation Date</div>
                                <div style="font-size:22px; font-weight:700; color:#0f172a; margin-bottom:12px;">
                                    <?= !empty($order->InstallationDate) ? date('M j, Y', strtotime($order->InstallationDate)) : 'TBD' ?>
                                </div>
                                <?php if (!empty($show_request_button)): ?>
                                    <button id="track-request-date-change" class="btn-request-date-change" style="padding:10px 16px; border-radius:6px; background:#003049; color:#fff; border:none; cursor:pointer; width:100%; font-size:14px;">Request Installation Date Change</button>
                                <?php else: ?>
                                    <button class="btn-request-date-change" disabled style="padding:10px 16px; border-radius:6px; background:#cbd5e1; color:#fff; border:none; cursor:default; width:100%; font-size:14px;">Request Date (Unavailable)</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="status-card ongoing" style="background: #ebf8ff; border-left: 5px solid #3182ce; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                            <h3 style="color: #3182ce; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-sync fa-spin"></i> <?= htmlspecialchars($site_assessment_status_map['label']) ?>
                            </h3>
                            <p style="color: #2d3748; margin: 0 0 15px 0;"><?= htmlspecialchars($site_assessment_status_map['message']) ?></p>
                        
                            <?php
                            // Show cancel button only for unapproved orders (customer can cancel before admin approval)
                            $can_customer_cancel = in_array($status_lower, ['pending review', 'awaiting admin', 'ready to approve', 'pending booking confirmation']);
                            ?>
                            
                            <?php if ($can_customer_cancel): ?>
                                <button id="customer-cancel-order-btn" 
                                        onclick="cancelCustomerOrder(<?= $order->OrderID ?>)"
                                        style="background: #dc3545; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: all 0.2s; margin-right: 10px;"
                                        onmouseover="this.style.background='#c82333'" 
                                        onmouseout="this.style.background='#dc3545'">
                                    <i class="fas fa-times-circle" style="margin-right: 6px;"></i>Cancel Order
                                </button>
                            <?php endif; ?>
                            
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
                    <?php endif; ?>
                <!-- Removed direct order fallback - all orders now use unified site-assessment flow -->
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Order Info -->
        <section class="order-info">
            <?php
            // Display Order ID first, then Order Type determined from the order record (not product)
            $display_order_id = htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT));
            ?>
            <div>
                <h4>Order ID:</h4>
                <p><?= $display_order_id ?></p>
            </div>
            <!-- Order Type removed - all orders now follow unified process -->
            <!-- Payment Method and Payment ID are shown later in the order process after quotation acceptance -->
            <div>
                <h4>Booking Date:</h4>
                <p><?= date('F j, Y', strtotime($order->OrderDate)) ?></p>
            </div>
            <?php if ($is_site_assessment && !empty($order->PreferredInstallationDate)): ?>
            <div>
                <h4>Ocular Visit Date:</h4>
                <?php
                    $ocular_display = '';
                    if (!empty($order->PreferredInstallationDate)) {
                        $date_part = date('F j, Y', strtotime($order->PreferredInstallationDate));

                        // Determine time: prefer ocular appointment time (if synced), then AppointmentTime,
                        // then PreferredInstallationTime, otherwise infer time from the PreferredInstallationDate string if it contains a time.
                        $time_part = '';
                        if (!empty($order->OcularTime)) {
                            $time_part = date('g:i A', strtotime($order->OcularTime));
                        } elseif (!empty($order->AppointmentTime)) {
                            $time_part = date('g:i A', strtotime($order->AppointmentTime));
                        } elseif (!empty($order->PreferredInstallationTime)) {
                            $time_part = date('g:i A', strtotime($order->PreferredInstallationTime));
                        } elseif (!empty($order->PreferredInstallationDate) && strpos($order->PreferredInstallationDate, ':') !== false) {
                            $time_part = date('g:i A', strtotime($order->PreferredInstallationDate));
                        }

                        $ocular_display = $date_part . ($time_part ? ' - ' . $time_part : '');
                    }
                ?>
                <p><?= htmlspecialchars($ocular_display ?: 'TBD') ?></p>
            </div>
            <?php endif; ?>
        </section>

        <!-- Order Progress -->
        <?php if (!$is_cancelled): ?>
        <?php
        // All orders now follow unified site-assessment process (ocular visit required)
        // No separate direct order flow
        
        // Calculate payment status
        $payment_status = strtolower(trim($order->PaymentStatus ?? 'pending'));
        $is_paid = ($payment_status === 'paid' || $payment_status === 'partial');
        $payment_date = null;
        if (isset($payment) && $payment->Payment_Date) {
            $payment_date = $payment->Payment_Date;
        } elseif (isset($payment) && $payment->PaymentDate) {
            $payment_date = $payment->PaymentDate;
        }
        
        // All orders follow unified process: Booking Submitted → Ocular Visit → In Fabrication → Installation/Delivery → Completed (5 steps)
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
        // Determine display status for installation step.
        // Show Installation/Delivery as active only when the installed progress
        // is explicitly in-progress or completed. Do NOT consider merely having
        // an InstallationDate enough to make the step active.
        $display_installed = ($progress['installed'] === 'in_progress' || $progress['installed'] === 'completed')
            ? $progress['installed']
            : 'pending';
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
            
            <!-- All orders: Step 2 - Ocular Visit -->
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
                
                <!-- All orders: Step 3 - In Fabrication -->
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
                
                <!-- All orders: Step 4 - Installation/Delivery -->
                <div class="step <?= get_step_class($display_installed) ?>">
                    <img src="<?php echo base_url('assets/images/img-page/window_track.svg'); ?>" class="order-icon" alt="Installation">
                    <p>Installation/Delivery</p>
                    <?php if ($display_installed === 'completed'): ?>
                        <span class="icon"><img src="<?php echo base_url('assets/images/img-page/check-track.png'); ?>" alt="check"></span>
                        <small><?= date('M j, Y', strtotime($order->InstallationDate)) ?><br>Completed</small>
                    <?php elseif ($display_installed === 'in_progress'): ?>
                        <span class="icon"></span>
                        <small><?= date('M j, Y', strtotime($order->InstallationDate)) ?><br>In Progress</small>
                    <?php else: ?>
                        <span class="icon"></span>
                        <small>Expected<br><?= date('M j, Y', strtotime($order->InstallationDate)) ?></small>
                    <?php endif; ?>
                </div>
                
                <!-- All orders: Step 5 - Completed -->
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
        </section>
        <?php endif; ?>

        <!-- Products Table -->
        <section class="order-products">
            <?php if (!empty($is_beginner)): ?>
                <style>
                    /* Ensure beginner table headers and cells are left-aligned */
                    .cart-table.beginner-align th,
                    .cart-table.beginner-align td {
                        text-align: left !important;
                        padding-left: 18px !important;
                    }
                    /* keep Qty column default alignment */
                    .cart-table.beginner-align th.qty-col,
                    .cart-table.beginner-align td.qty-col {
                        text-align: center !important;
                        padding-left: 0 !important;
                    }
                    .cart-table.beginner-align td.price-cell { text-align: left !important; }
                </style>
            <?php endif; ?>
            <?php $beginner_class = !empty($is_beginner) ? ' beginner-align' : ''; ?>
            <table class="cart-table<?= $beginner_class ?>">
                <thead>
                    <tr>
                        <th style="text-align: left; padding-left: 18px;">Product</th>
                        <th style="text-align: left;">Customization</th>
                        <th class="qty-col" style="width: 90px; text-align: left;">Qty</th>
                        <th style="width: 160px; text-align: left;">Price Range</th>
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
                                        <div style="display:flex; flex-direction:column;">
                                            <span style="font-weight: 600; color: #0f2b46;"><?= htmlspecialchars($item->ProductName ?? 'Unknown Product') ?></span>
                                            <?php if (!empty($item->Category) || !empty($item->Subcategory)): ?>
                                                <span style="font-size: 0.85rem; color: #6b7280; margin-top:4px;">
                                                    <?= htmlspecialchars($item->Category ?? '') ?><?php if (!empty($item->Category) && !empty($item->Subcategory)): ?> - <?php endif; ?><?= htmlspecialchars($item->Subcategory ?? '') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="customization-info">
                                    <?php 
                                    // Build customization breakdown from 2D JSON or legacy fields
                                    $breakdown_fields = [];
                                    
                                    // Try to parse Customization JSON first (from 2D customization)
                                    if (!empty($item->Customization)) {
                                        $parsed = json_decode($item->Customization, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                                            // Add Dimension first if available
                                            if (!empty($item->Dimensions)) {
                                                $breakdown_fields[] = ['label' => 'Size', 'value' => $item->Dimensions];
                                            }
                                            
                                            // Skip internal/dimension fields, show specification fields
                                            $skip_keys = ['Dimension', 'Dimensions', 'width', 'height', 'unit', '_width', '_height', '_unit'];
                                            
                                            // Add other fields from JSON
                                            foreach ($parsed as $key => $value) {
                                                // Skip empty values, internal fields (starting with _), and dimension-related fields
                                                if (empty($value) || substr($key, 0, 1) === '_' || in_array($key, $skip_keys)) {
                                                    continue;
                                                }
                                                
                                                // Convert camelCase keys to proper labels (e.g. glassType -> Glass Type)
                                                $label = ucwords(preg_replace('/(?<!^)[A-Z]/', ' $0', str_replace('_', ' ', $key)));
                                                $breakdown_fields[] = ['label' => $label, 'value' => $value];
                                            }
                                        }
                                    }
                                    
                                    // Fallback to legacy fields if no JSON
                                    if (empty($breakdown_fields)) {
                                        if (!empty($item->Dimensions)) $breakdown_fields[] = ['label' => 'Size', 'value' => $item->Dimensions];
                                        if (!empty($item->GlassShape)) $breakdown_fields[] = ['label' => 'Shape', 'value' => ucfirst($item->GlassShape)];
                                        if (!empty($item->GlassType)) $breakdown_fields[] = ['label' => 'Type', 'value' => ucfirst($item->GlassType)];
                                        if (!empty($item->GlassThickness)) $breakdown_fields[] = ['label' => 'Thickness', 'value' => $item->GlassThickness];
                                        if (!empty($item->EdgeWork)) $breakdown_fields[] = ['label' => 'Edge', 'value' => ucfirst(str_replace('-', ' ', $item->EdgeWork))];
                                        if (!empty($item->FrameType)) $breakdown_fields[] = ['label' => 'Frame', 'value' => ucfirst($item->FrameType)];
                                        if (!empty($item->Engraving) && $item->Engraving !== 'None') $breakdown_fields[] = ['label' => 'Engraving', 'value' => $item->Engraving];
                                    }
                                    
                                    $has_breakdown = count($breakdown_fields) > 0;
                                    $has_customization = $has_breakdown;
                                    ?>
                                    <?php if ($has_breakdown): ?>
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
                                            <?php
                                            // Show first 2 specs as button
                                            $display_parts = array_slice($breakdown_fields, 0, 2);
                                            $remaining_count = count($breakdown_fields) - 2;
                                            $display_text = implode(' • ', array_map(function($f) { return $f['label'] . ': ' . $f['value']; }, $display_parts));
                                            $breakdown_json = htmlspecialchars(json_encode($breakdown_fields), ENT_QUOTES);
                                            ?>
                                            <div style="display:flex; gap:12px; align-items:center;">
                                                <button type="button" class="view-breakdown-btn" data-breakdown="<?= $breakdown_json ?>" style="flex:1; text-align:left; padding:10px 14px; border-radius:6px; border:2px solid #3b82f6; background:#eff6ff; color:#1e40af; cursor:pointer; font-size:13px; line-height:1.6; max-width:100%; word-wrap:break-word; white-space:normal; transition:all 0.2s ease; font-weight:600; box-shadow:0 2px 4px rgba(59,130,246,0.1);" onmouseover="this.style.backgroundColor='#dbeafe'; this.style.borderColor='#2563eb'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 6px rgba(59,130,246,0.2)';" onmouseout="this.style.backgroundColor='#eff6ff'; this.style.borderColor='#3b82f6'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(59,130,246,0.1)';">
                                                    <?= $display_text ?>
                                                    <?php if ($remaining_count > 0): ?>
                                                        <br><span style="font-size:12px; color:#4b5563;">and <?= $remaining_count ?> more</span>
                                                    <?php endif; ?>
                                                    <br><span style="font-size:11px; opacity:0.7;">▼ Click to expand</span>
                                                </button>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php if (!empty($is_beginner)): ?>
                                            <span class="no-custom" style="color:#6b7280; font-style:italic;">Awaiting admin 2D customization</span>
                                        <?php else: ?>
                                            <span class="no-custom">Standard</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="qty-col"><?= $has_customization ? ($item->Quantity ?? 1) : '' ?></td>
                                <td class="price-cell" style="text-align: right; font-weight:600; color:#0f2b46;">
                                    <?php
                                        // Always attempt to show an item-level price range, falling back to order summary
                                        $price_min = $item->PriceMin ?? $item->Price ?? $item->EstimatePrice ?? null;
                                        $price_max = $item->PriceMax ?? null;
                                        if ((empty($price_min) || empty($price_max)) && !empty($summary['price_range_min'])) {
                                            $price_min = $price_min ?? $summary['price_range_min'];
                                            $price_max = $price_max ?? $summary['price_range_max'] ?? null;
                                        }

                                        if ($price_min && $price_max) {
                                            echo '₱' . number_format((float)$price_min) . ' - ₱' . number_format((float)$price_max);
                                        } elseif ($price_min) {
                                            echo 'Starting at ₱' . number_format((float)$price_min);
                                        } else {
                                            echo 'Price TBD after assessment';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <?php if ($is_site_assessment): ?>
        <!-- Site Address and Special Instructions for Site Assessment Orders -->
        <section class="order-products">
            <?php
                // Parse contact info from SpecialInstructions if JSON
                $contact_name = '';
                $contact_phone = '';
                if (isset($order) && !empty($order->SpecialInstructions)) {
                    $special_data = json_decode($order->SpecialInstructions, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($special_data)) {
                        $contact_name = $special_data['contact_name'] ?? '';
                        $contact_phone = $special_data['contact_phone'] ?? '';
                    }
                }
                
                // Fallback to user data if not in SpecialInstructions
                if (empty($contact_name) && isset($user) && $user) {
                    $contact_name = htmlspecialchars($user->First_Name . ' ' . $user->Last_Name);
                }
                if (empty($contact_phone) && isset($user) && $user && !empty($user->PhoneNum)) {
                    $contact_phone = htmlspecialchars($user->PhoneNum);
                }

                // Determine site address (prefer DeliveryAddress, then billing variants)
                $site_addr = 'Site address not provided';
                if (isset($order) && $order) {
                    if (!empty($order->DeliveryAddress)) {
                        $site_addr = $order->DeliveryAddress;
                    } elseif (!empty($order->BillingAddress)) {
                        $site_addr = $order->BillingAddress;
                    } elseif (!empty($order->billing_address)) {
                        $site_addr = $order->billing_address;
                    }
                }

                // Determine notes (prefer Notes, then other variants)
                $order_notes = '';
                if (isset($order) && $order) {
                    if (!empty($order->Notes)) $order_notes = $order->Notes;
                    elseif (!empty($order->CustomerNotes)) $order_notes = $order->CustomerNotes;
                    elseif (!empty($order->Note)) $order_notes = $order->Note;
                }
            ?>

            <div class="addresses" style="display:flex !important; flex-direction:row !important; gap:20px; align-items:stretch; width:100%;">
                <div class="address-box" style="flex:1 1 50%; min-width:0; max-width:50%;">
                    <h4>Site Address</h4>
                    <?php if (!empty($contact_name)): ?>
                        <p><b><?= $contact_name ?></b></p>
                    <?php endif; ?>
                    <?php if (!empty($contact_phone)): ?>
                        <p>(+63) <?= $contact_phone ?></p>
                    <?php endif; ?>
                    <p><?= nl2br(htmlspecialchars($site_addr)) ?></p>
                </div>

                <div class="address-box" style="flex:1 1 50%; min-width:0; max-width:50%;">
                    <h4>Special Instructions / Note</h4>
                    <div style="padding: 15px 20px;">
                        <?php if (!empty($order_notes)): ?>
                            <?= nl2br(htmlspecialchars($order_notes)) ?>
                        <?php else: ?>
                            <p style="color: #999; font-style: italic; margin:0; text-align:left;">No notes or special instructions</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!$is_site_assessment): ?>
        <section class="order-products">
            <div class="addresses" style="display:flex !important; flex-direction:row !important; gap:20px; align-items:stretch; width:100%;">
                <div class="address-box" style="flex:1 1 50%; min-width:0; max-width:50%;">
                    <h4>Shipping Address</h4>
                    <?php $this->load->helper('address'); ?>
                    <?php
                        // Get shipping info from payment record ONLY (reflects Payment page input)
                        $ship_name = '';
                        $ship_email = '';
                        $ship_phone = '';
                        $ship_addr = null;
                        
                        if (isset($payment) && $payment) {
                            // Always use payment record data if available
                            $ship_name = $payment->shipping_name ?? '';
                            $ship_email = $payment->shipping_email ?? '';
                            $ship_phone = $payment->shipping_phone ?? '';
                            
                            // Build address array from payment shipping fields
                            $ship_addr = [
                                'UnitHouseNumber' => $payment->shipping_unit ?? '',
                                'Street' => $payment->shipping_street ?? '',
                                'Subdivision' => $payment->shipping_subdivision ?? '',
                                'Barangay' => $payment->shipping_barangay ?? '',
                                'City' => $payment->shipping_city ?? '',
                                'Province' => $payment->shipping_province ?? '',
                                'Region' => $payment->shipping_region ?? '',
                                'ZipCode' => $payment->shipping_postal_code ?? '',
                                'Country' => $payment->shipping_country ?? ''
                            ];
                            
                            // Check if address is completely empty
                            $has_address = false;
                            foreach ($ship_addr as $field) {
                                if (!empty($field)) {
                                    $has_address = true;
                                    break;
                                }
                            }
                            
                            // If address is empty, set to null
                            if (!$has_address) {
                                $ship_addr = null;
                            }
                        }
                    ?>
                    <?php if ($ship_name): ?><p><b><?= htmlspecialchars($ship_name) ?></b></p><?php endif; ?>
                    <?php if ($ship_email): ?><p><?= htmlspecialchars($ship_email) ?></p><?php endif; ?>
                    <?php if ($ship_phone): ?><p><?= format_display_phone($ship_phone) ?></p><?php endif; ?>

                    <?php if (!is_null($ship_addr)): ?>
                        <p><?= format_address_three_html($ship_addr) ?></p>
                    <?php else: ?>
                        <p>No shipping address captured from payment form.</p>
                    <?php endif; ?>
                </div>

                <div class="address-box" style="flex:1 1 50%; min-width:0; max-width:50%;">
                    <h4>Billing Address</h4>
                    <?php
                    // Prefer persisted billing fields on the payment record
                    $has_payment_billing = isset($payment) && ($payment->billing_name ?? $payment->billing_firstname ?? $payment->billing_street ?? false);
                    if ($has_payment_billing):
                        // Build from payment columns
                        $b_name = trim(($payment->billing_name ?? trim(($payment->billing_firstname ?? '') . ' ' . ($payment->billing_lastname ?? ''))));
                        $b_email = $payment->billing_email ?? '';
                        $b_phone = $payment->billing_phone ?? '';
                        $b_addr = [
                            'UnitHouseNumber' => $payment->billing_unit ?? $payment->billing_unit_house_number ?? '',
                            'Street' => $payment->billing_street ?? '',
                            'Subdivision' => $payment->billing_subdivision ?? '',
                            'Barangay' => $payment->billing_barangay ?? '',
                            'City' => $payment->billing_city ?? '',
                            'Province' => $payment->billing_province ?? '',
                            'Region' => $payment->billing_region ?? '',
                            'ZipCode' => $payment->billing_postal_code ?? $payment->billing_zipcode ?? '',
                            'Country' => $payment->billing_country ?? ''
                        ];
                    ?>
                        <?php if ($b_name): ?><p><b><?= htmlspecialchars($b_name) ?></b></p><?php endif; ?>
                        <?php if ($b_email): ?><p><?= htmlspecialchars($b_email) ?></p><?php endif; ?>
                        <?php if ($b_phone): ?><p><?= format_display_phone($b_phone) ?></p><?php endif; ?>
                        <p><?= format_address_three_html($b_addr) ?></p>
                    <?php elseif (isset($billing_address) && $billing_address): ?>
                        <p><?= format_address_three_html($billing_address) ?></p>
                    <?php elseif (isset($shipping_address) && $shipping_address): ?>
                        <p><?= format_address_three_html($shipping_address) ?></p>
                    <?php else: ?>
                        <p>No billing address provided.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="addresses" style="display:flex !important; flex-direction:row !important; gap:20px; align-items:stretch; width:100%; margin-top:20px;">
                <div class="address-box" style="flex:1 1 100%; min-width:0; max-width:100%;">
                    <h4>Special Instructions / Note</h4>
                    <div style="padding: 15px 20px;">
                        <?php
                            $order_notes = '';
                            if (isset($order) && $order) {
                                if (!empty($order->Notes)) $order_notes = $order->Notes;
                                elseif (!empty($order->CustomerNotes)) $order_notes = $order->CustomerNotes;
                                elseif (!empty($order->Note)) $order_notes = $order->Note;
                            }
                        ?>
                        <?php if (!empty($order_notes)): ?>
                            <?= nl2br(htmlspecialchars($order_notes)) ?>
                        <?php else: ?>
                            <p style="color: #999; font-style: italic; margin:0; text-align:left;">No notes or special instructions</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Payment Breakdown Section -->
        <section id="payment-breakdown-section" style="background: #f3f6f8; border: 1px solid #ddd; border-radius: 8px; padding: 24px 28px; margin-top: 20px; margin-bottom: 24px;">
            <?php
                $pb = $payment_breakdown ?? [];
                $order_status = $order->Status ?? 'Pending Review';
                $total_amount = floatval($pb['total_amount'] ?? 0);
                $admin_has_set_amount = $pb['admin_has_set_amount'] ?? false; // Check if admin entered amount

                // Payment data (must be defined before unlocked status checks)
                $dp_amount = floatval($pb['downpayment_amount'] ?? ($total_amount * 0.5));
                $dp_method = $pb['downpayment_method'] ?? null;
                $dp_status = $pb['downpayment_status'] ?? 'Pending';
                $dp_receipt = $pb['downpayment_receipt'] ?? null;
                $dp_txn_id = $pb['downpayment_transaction_id'] ?? null;

                $fab_amount = floatval($pb['fabrication_amount'] ?? ($total_amount * 0.4));
                $fab_method = $pb['fabrication_method'] ?? null;
                $fab_status = $pb['fabrication_status'] ?? 'Pending';
                $fab_receipt = $pb['fabrication_receipt'] ?? null;
                $fab_txn_id = $pb['fabrication_transaction_id'] ?? null;

                $inst_amount = floatval($pb['installation_amount'] ?? ($total_amount * 0.1));
                $inst_method = $pb['installation_method'] ?? null;
                $inst_status = $pb['installation_status'] ?? 'Pending';
                $inst_receipt = $pb['installation_receipt'] ?? null;
                $inst_txn_id = $pb['installation_transaction_id'] ?? null;

                // Determine which stages are unlocked based on order status
                // Synced exactly with admin panel (order-management.js populatePaymentBreakdown)
                // Downpayment: unlocked once ocular visit stage is reached (Approved/Booking Confirmed or later) OR if already paid
                $dp_unlocked_statuses = ['Ocular Pending', 'Approved', 'Booking Confirmed', 'Quotation Available', 'Awaiting Payment', 'In Fabrication', 'Ready for Installation', 'Installed', 'Completed'];
                $dp_unlocked = in_array($order_status, $dp_unlocked_statuses) || ($dp_status === 'Paid');
                // Fabrication: unlocked when In Fabrication, Ready for Installation, Installed, Completed OR if already paid
                $fab_unlocked_statuses = ['In Fabrication', 'Ready for Installation', 'Installed', 'Completed'];
                $fab_unlocked = in_array($order_status, $fab_unlocked_statuses) || ($fab_status === 'Paid');
                
                // Installation: unlocked when order reaches Ready for Installation phase or later
                // Stages: Ready for Installation → Installation/Delivery → Installed → Completed
                $inst_unlocked_statuses = ['Ready for Installation', 'Installation/Delivery', 'Installed', 'Completed'];
                $inst_unlocked = in_array($order_status, $inst_unlocked_statuses) || ($inst_status === 'Paid');

                // Determine active payment stage for "Pay Now"
                // Admin controls when customer can pay — only the current unlocked & unpaid stage is active
                $active_stage = null;
                if ($dp_unlocked && $dp_status !== 'Paid') {
                    $active_stage = 'downpayment';
                } elseif ($fab_unlocked && $fab_status !== 'Paid') {
                    $active_stage = 'fabrication';
                } elseif ($inst_unlocked && $inst_status !== 'Paid') {
                    $active_stage = 'installation';
                }

                // Build payment page URL for Pay Now redirect
                $payment_base_url = base_url('payment?order=' . $order->OrderID);
            ?>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0 0 4px 0; color: #0f2b46; font-size: 1.2rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#02455F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Payment Breakdown
                </h3>
            </div>

            <!-- Payment Schedule Info -->
            <div style="margin-bottom: 20px; padding: 12px 16px; background: #f0f4f8; border-left: 4px solid #02455F; border-radius: 4px;">
                <span style="color: #495057; font-size: 0.88rem;">
                    <strong>&#9432; Payment Schedule:</strong> 50% downpayment at ocular visit, 40% after fabrication complete, 10% after installation complete.
                </span>
            </div>

            <?php if (!$admin_has_set_amount): ?>
            <!-- Notice: Amounts pending ocular visit -->
            <div style="margin-bottom: 20px; padding: 16px 20px; background: #fff9e6; border: 1px solid #ffc107; border-radius: 6px; text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 8px;">⏳</div>
                <h4 style="margin: 0 0 8px 0; color: #856404; font-size: 1.05rem;">Payment Amounts Pending</h4>
                <p style="margin: 0; color: #856404; font-size: 0.9rem;">The exact payment amounts will be determined by our team during your scheduled ocular visit. We'll assess your project requirements and provide you with the final pricing breakdown.</p>
            </div>
            <?php endif; ?>

            <!-- Stage 1: Downpayment (50%) -->
            <div style="border: 2px solid <?= $dp_status === 'Paid' ? '#22c55e' : ($dp_unlocked ? '#02455F' : '#dee2e6') ?>; border-radius: 8px; padding: 18px 20px; margin-bottom: 16px; background: <?= $dp_unlocked ? '#ffffff' : '#f8f9fa' ?>; <?= !$dp_unlocked ? 'opacity: 0.6;' : '' ?>">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h4 style="margin: 0; color: <?= $dp_unlocked ? '#02455F' : '#6c757d' ?>; font-size: 1rem; font-weight: 700;">
                        <?php if ($dp_status === 'Paid'): ?>&#9989;<?php elseif (!$dp_unlocked): ?>&#128274;<?php else: ?>&#128179;<?php endif; ?> Downpayment (50%)
                    </h4>
                    <span style="display:inline-block; padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;
                        <?php if ($dp_status === 'Paid'): ?>
                            background: #dcfce7; color: #166534;
                        <?php elseif (!$dp_unlocked): ?>
                            background: #e5e7eb; color: #6b7280;
                        <?php else: ?>
                            background: #fef3c7; color: #92400e;
                        <?php endif; ?>
                    "><?= $dp_unlocked ? $dp_status : 'Locked' ?></span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Amount (&#8369;)</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827; font-weight:500;">
                            <?php if ($admin_has_set_amount && $dp_unlocked): ?>
                                <?= $dp_amount > 0 ? number_format($dp_amount, 2) : '—' ?>
                            <?php else: ?>
                                To be determined at ocular visit
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Payment Method</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827;">
                            <?= ($dp_unlocked && !empty($dp_method)) ? htmlspecialchars($dp_method) : '—' ?>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Payment ID</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827; font-size:0.8rem; word-break:break-all;">
                            <?= ($dp_unlocked && !empty($dp_txn_id)) ? htmlspecialchars($dp_txn_id) : '—' ?>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Status</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827;">
                            <?= $dp_unlocked ? $dp_status : 'Pending' ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($dp_receipt) && $dp_unlocked): ?>
                <div style="margin-top: 10px;">
                    <a href="<?= base_url($dp_receipt) ?>" target="_blank" style="color: #02455F; text-decoration: underline; font-size: 0.85rem;">
                        View Receipt
                    </a>
                </div>
                <?php endif; ?>
                <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
                    &#9432; <?= $dp_unlocked ? 'Downpayment is managed in the Ocular Visit appointment.' : 'This payment stage will be available after your booking is confirmed.' ?>
                </small>
                <?php if ($active_stage === 'downpayment' && $admin_has_set_amount): ?>
                <div style="margin-top: 14px; text-align: right;">
                    <a href="<?= $payment_base_url . '&stage=downpayment' ?>" style="display: inline-block; padding: 10px 28px; background: #02455F; color: #fff; border: none; border-radius: 6px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; text-decoration: none;" onmouseover="this.style.background='#023047'" onmouseout="this.style.background='#02455F'">
                        Pay Now — &#8369;<?= number_format($dp_amount, 2) ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Stage 2: Fabrication Payment (40%) -->
            <div style="border: 2px solid <?= $fab_status === 'Paid' ? '#22c55e' : ($fab_unlocked ? '#02455F' : '#dee2e6') ?>; border-radius: 8px; padding: 18px 20px; margin-bottom: 16px; background: <?= $fab_unlocked ? '#ffffff' : '#f8f9fa' ?>; <?= !$fab_unlocked ? 'opacity: 0.6;' : '' ?>">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h4 style="margin: 0; color: <?= $fab_unlocked ? '#02455F' : '#6c757d' ?>; font-size: 1rem; font-weight: 700;">
                        <?php if ($fab_status === 'Paid'): ?>&#9989;<?php elseif (!$fab_unlocked): ?>&#128274;<?php else: ?>&#128179;<?php endif; ?> Fabrication Payment (40%)
                    </h4>
                    <span style="display:inline-block; padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;
                        <?php if ($fab_status === 'Paid'): ?>
                            background: #dcfce7; color: #166534;
                        <?php elseif (!$fab_unlocked): ?>
                            background: #e5e7eb; color: #6b7280;
                        <?php else: ?>
                            background: #fef3c7; color: #92400e;
                        <?php endif; ?>
                    "><?= $fab_unlocked ? $fab_status : 'Locked' ?></span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Amount (&#8369;)</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827; font-weight:500;">
                            <?php if ($admin_has_set_amount): ?>
                                <?= $fab_amount > 0 ? number_format($fab_amount, 2) : '—' ?>
                            <?php else: ?>
                                To be determined at ocular visit
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Payment Method</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827;">
                            <?= (!empty($fab_method)) ? htmlspecialchars($fab_method) : 'Online Payment' ?>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Payment ID</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827; font-size:0.8rem; word-break:break-all;">
                            <?= ($fab_unlocked && !empty($fab_txn_id)) ? htmlspecialchars($fab_txn_id) : '—' ?>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Status</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827;">
                            <?= $fab_unlocked ? $fab_status : 'Pending' ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($fab_receipt) && $fab_unlocked): ?>
                <div style="margin-top: 10px;">
                    <a href="<?= base_url($fab_receipt) ?>" target="_blank" style="color: #02455F; text-decoration: underline; font-size: 0.85rem;">
                        View Receipt
                    </a>
                </div>
                <?php endif; ?>
                <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
                    &#9432; This payment stage will be available when order status is "In Fabrication" or later.
                </small>
                <?php if ($active_stage === 'fabrication' && $admin_has_set_amount): ?>
                <div style="margin-top: 14px; text-align: right;">
                    <a href="<?= $payment_base_url . '&stage=fabrication' ?>" style="display: inline-block; padding: 10px 28px; background: #02455F; color: #fff; border: none; border-radius: 6px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; text-decoration: none;" onmouseover="this.style.background='#023047'" onmouseout="this.style.background='#02455F'">
                        Pay Now — &#8369;<?= number_format($fab_amount, 2) ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Stage 3: Installation Payment (10%) -->
            <div style="border: 2px solid <?= $inst_status === 'Paid' ? '#22c55e' : ($inst_unlocked ? '#02455F' : '#dee2e6') ?>; border-radius: 8px; padding: 18px 20px; margin-bottom: 16px; background: <?= $inst_unlocked ? '#ffffff' : '#f8f9fa' ?>; <?= !$inst_unlocked ? 'opacity: 0.6;' : '' ?>">
                
                <!-- Payment Deadline Banner for Installation -->
                <?php if ($inst_unlocked && $inst_status !== 'Paid' && !empty($pb['installation_payment_due_date'])): ?>
                    <?php 
                        $due_date = new DateTime($pb['installation_payment_due_date']);
                        $now = new DateTime();
                        $diff = $now->diff($due_date);
                        $is_overdue = $now > $due_date;
                        $days_remaining = $is_overdue ? 0 : $diff->days;
                        $hours_remaining = $is_overdue ? 0 : $diff->h;
                    ?>
                    
                    <?php if (!$is_overdue): ?>
                        <!-- Payment Due Banner -->
                        <div style="background: #fff8e1; border: 1px solid #ffc107; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <strong style="color: #856404;">⏰ Payment Due in: <span id="payment-countdown"><?= $days_remaining ?> days, <?= $hours_remaining ?> hours</span></strong>
                                    <div style="font-size: 0.85rem; color: #6c5b1a; margin-top: 4px;">
                                        Due by: <?= $due_date->format('F j, Y \at g:i A') ?>
                                    </div>
                                </div>
                                <div>
                                    <button onclick="scrollToPayButton()" style="padding: 6px 16px; background: #ffc107; color: #000; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e0a800'" onmouseout="this.style.background='#ffc107'">
                                        Pay Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Payment Overdue Banner -->
                        <div style="background: #fee; border: 1px solid #dc3545; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <strong style="color: #721c24;">⚠️ Payment Overdue: <?= abs($diff->days) ?> days past due</strong>
                                    <div style="font-size: 0.85rem; color: #721c24; margin-top: 4px;">
                                        Product may be removed due to non-payment. Please contact us immediately.
                                    </div>
                                </div>
                                <div>
                                    <a href="tel:+639123456789" style="padding: 6px 16px; background: #dc3545; color: #fff; border: none; border-radius: 4px; font-weight: 600; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#c82333'" onmouseout="this.style.background='#dc3545'">
                                        Call Us
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <script>
                        // Real-time countdown for installation payment
                        function updatePaymentCountdown() {
                            const dueDate = new Date('<?= $due_date->format('Y-m-d H:i:s') ?>');
                            const now = new Date();
                            const diff = dueDate - now;
                            
                            if (diff <= 0) {
                                document.getElementById('payment-countdown').textContent = 'Payment Overdue!';
                                return;
                            }
                            
                            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            
                            document.getElementById('payment-countdown').textContent = `${days} days, ${hours} hours, ${minutes} minutes`;
                        }
                        
                        // Update countdown every minute
                        updatePaymentCountdown();
                        setInterval(updatePaymentCountdown, 60000);
                        
                        function scrollToPayButton() {
                            document.querySelector('[data-pay-now="installation"]').scrollIntoView({ behavior: 'smooth' });
                        }
                    </script>
                <?php endif; ?>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h4 style="margin: 0; color: <?= $inst_unlocked ? '#02455F' : '#6c757d' ?>; font-size: 1rem; font-weight: 700;">
                        <?php if ($inst_status === 'Paid'): ?>✅<?php elseif (!$inst_unlocked): ?>🔒<?php elseif ($inst_unlocked && !empty($pb['installation_payment_due_date'])): ?>⏰<?php else: ?>💳<?php endif; ?> Installation Payment (10%)
                    </h4>
                    <span style="display:inline-block; padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;
                        <?php if ($inst_status === 'Paid'): ?>
                            background: #dcfce7; color: #166534;
                        <?php elseif (!$inst_unlocked): ?>
                            background: #e5e7eb; color: #6b7280;
                        <?php else: ?>
                            background: #fef3c7; color: #92400e;
                        <?php endif; ?>
                    "><?= $inst_unlocked ? $inst_status : 'Locked' ?></span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Amount (₱)</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827; font-weight:500;">
                            <?php if ($admin_has_set_amount): ?>
                                <?= $inst_amount > 0 ? number_format($inst_amount, 2) : '—' ?>
                            <?php else: ?>
                                To be determined at ocular visit
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Payment Method</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827;">
                            <?= ($inst_unlocked && !empty($inst_method)) ? htmlspecialchars($inst_method) : ($inst_unlocked ? 'GCash / Maya / Card' : 'Cash / Check (On-site)') ?>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Payment ID</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827; font-size:0.8rem; word-break:break-all;">
                            <?= ($inst_unlocked && !empty($inst_txn_id)) ? htmlspecialchars($inst_txn_id) : '—' ?>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Status</label>
                        <div style="padding:10px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; color:#111827;">
                            <?= $inst_unlocked ? $inst_status : 'Pending' ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($inst_receipt) && $inst_unlocked): ?>
                <div style="margin-top: 10px;">
                    <a href="<?= base_url($inst_receipt) ?>" target="_blank" style="color: #02455F; text-decoration: underline; font-size: 0.85rem;">
                        📄 View Receipt
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if ($inst_unlocked && $inst_status === 'Pending'): ?>
                <div style="margin-top: 14px; padding: 12px; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px;">
                    <div style="color: #0f172a; font-size: 0.85rem; margin-bottom: 8px;">
                        <strong>💡 Pay online now via:</strong> GCash, Maya, or Credit/Debit Card
                    </div>
                    <div style="color: #475569; font-size: 0.8rem;">
                        Secure payment through PayMongo • No additional fees • Instant confirmation
                    </div>
                </div>
                <?php else: ?>
                <small style="color: #6c757d; font-style: italic; margin-top: 10px; display: block;">
                    ℹ️ This payment stage will be available when order status is "Installed" or later.
                </small>
                <?php endif; ?>
                
                <?php if ($active_stage === 'installation' && $admin_has_set_amount): ?>
                <div style="margin-top: 14px; text-align: right;">
                    <a href="<?= $payment_base_url . '&stage=installation' ?>" data-pay-now="installation" style="display: inline-block; padding: 12px 32px; background: #02455F; color: #fff; border: none; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.2s; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.background='#023047'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#02455F'; this.style.transform='translateY(0)'">
                        🚀 Pay Final Amount — ₱<?= number_format($inst_amount, 2) ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Note -->
            <div style="padding: 12px 16px; background: #fff8e1; border-left: 4px solid #ffc107; border-radius: 4px;">
                <span style="color: #856404; font-size: 0.85rem;">
                    <strong>&#9888; Note:</strong> Payment stages unlock based on order status. Fabrication payment becomes available during "In Fabrication" stage, and installation payment becomes available during "Installation/Delivery" stage.
                </span>
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
        <div class="design-modal-header" style="text-align:center;">
            <h3>2D Design Preview</h3>
            <p>This design is included in your order.</p>
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

<!-- Specs Modal -->
<div id="specsModal" class="design-modal">
    <div class="design-modal-overlay" onclick="closeSpecsModal()"></div>
    <div class="design-modal-content">
        <button class="design-modal-close" onclick="closeSpecsModal()">✕</button>
        <div class="design-modal-header">
            <h3>Product Specifications</h3>
        </div>
        <div class="design-modal-body" id="specsModalBody">
        </div>
        <div class="design-modal-footer">
            <button class="btn-download-design" onclick="closeSpecsModal()">Close</button>
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

// Specs Modal Functions
function openSpecsFromId(elementId) {
    var source = document.getElementById(elementId);
    if (!source) return;
    var body = document.getElementById('specsModalBody');
    body.innerHTML = source.innerHTML;
    document.getElementById('specsModal').classList.add('active');
}

function closeSpecsModal() {
    document.getElementById('specsModal').classList.remove('active');
}

// Customization breakdown modal handler
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.view-breakdown-btn');
    if (!btn) return;
    e.preventDefault();
    var breakdownData = btn.getAttribute('data-breakdown');
    if (!breakdownData) return;
    
    var breakdownFields = [];
    try {
        breakdownFields = JSON.parse(breakdownData);
    } catch (err) {
        console.error('Failed to parse breakdown data:', err);
        return;
    }
    
    var contentHtml = '<div class="breakdown-list" style="padding:0;">';
    breakdownFields.forEach(function(field) {
        var label = field.label || '';
        var value = field.value || field.val || '';
        if (!value || value === '' || value === 'None') {
            contentHtml += '<div style="margin-bottom:16px; padding:12px; background:#f9fafb; border-left:4px solid #d1d5db; border-radius:4px;"><strong style="display:block;color:#1f2937; margin-bottom:6px; font-size:14px;">' + label + '</strong><div style="color:#9ca3af; font-style:italic; font-size:13px;">Not specified</div></div>';
        } else {
            contentHtml += '<div style="margin-bottom:16px; padding:12px; background:#f0f9ff; border-left:4px solid #3b82f6; border-radius:4px;"><strong style="display:block;color:#1e40af; margin-bottom:6px; font-size:14px;">' + label + '</strong><div style="color:#1f2937; font-size:14px; font-weight:500;">' + value + '</div></div>';
        }
    });
    contentHtml += '</div>';
    
    // Create or update modal
    var modal = document.getElementById('breakdownModal');
    if (!modal) {
        var modalHtml = '<div id="breakdownModal" class="modal-backdrop" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:10000;"><div class="modal-content" style="max-width:720px;width:90%;max-height:85vh;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.3);"><div class="modal-header" style="background:#1e3a8a;color:#fff;padding:16px 20px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;"><h3 style="margin:0;font-size:20px;font-weight:700;">2D Customization Breakdown</h3><button class="modal-close" id="breakdownModalClose" style="background:rgba(255,255,255,0.2);border:none;color:#fff;font-size:28px;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background=\'rgba(255,255,255,0.3)\';" onmouseout="this.style.background=\'rgba(255,255,255,0.2)\';">×</button></div><div class="modal-body" id="breakdownModalBody" style="padding:24px;background:#fff;border-radius:0 0 12px 12px;"></div></div></div>';
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('breakdownModal');
        document.getElementById('breakdownModalClose').addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        });
        modal.addEventListener('click', function(ev) {
            if (ev.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
    
    document.getElementById('breakdownModalBody').innerHTML = contentHtml;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDesignModal();
        closeSpecsModal();
        var modal = document.getElementById('breakdownModal');
        if (modal && modal.style.display === 'flex') {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
});

// Pay Now buttons now redirect to payment page (no popup modal needed)

// Real-time order progress polling
(function() {
    const orderId = <?= $order ? $order->OrderID : 'null' ?>;
    // All orders now follow unified process (no direct order distinction)
    const isDirectOrder = false;
    const isSiteAssessment = true;
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
        
        // Check if payment is completed
        const isPaid = payment_status === 'paid' || payment_status === 'partial';
        
        // All orders follow unified process: Booking Submitted → Ocular Visit → In Fabrication → Installation/Delivery → Completed
        // Ensure previous steps are marked as completed when a later step is in progress
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
        
        // Calculate progress percentage: 5 steps (0%, 25%, 50%, 75%, 100%)
        let calculatedProgress = 0;
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
        
        // Update each step: Booking Submitted (0), Ocular Visit (1), In Fabrication (2), Installation/Delivery (3), Completed (4)
        updateStep('order_placed', progress.order_placed, data.order_date, data.order_time, 0);
        updateStep('ocular_visit', progress.ocular_visit, dates.ocular_date, null, 1);
        updateStep('in_fabrication', progress.in_fabrication, dates.fabrication_date, null, 2);
        updateStep('installed', progress.installed, dates.installation_date, null, 3);
        updateStep('completed', progress.completed, dates.estimated_delivery, null, 4);
    }
    
    function updateStep(stepName, status, date, time = null, stepIndex = null) {
        // If stepIndex is provided, use it directly; otherwise, map step names to indices
        if (stepIndex === null) {
            // Map step names to indices for unified order flow
            const stepMap = {
                'order_placed': 0,
                'ocular_visit': 1,
                'in_fabrication': 2,
                'installed': 3,
                'completed': 4
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

<!-- Date Change Request Modal (Track Order) -->
<div id="trackDateChangeModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.2); position: relative;">
        <span class="close-track-modal" style="position: absolute; right: 15px; top: 15px; font-size: 28px; font-weight: bold; cursor: pointer; color: #aaa;">&times;</span>
        <h2 style="color: #003049; margin-bottom: 20px;">Request Installation Date Change</h2>
        <p style="color: #6b7280; margin-bottom: 20px;">Please select a new installation date within 7 days of the original date.</p>
        <form id="trackDateChangeForm">
            <input type="hidden" id="track-modal-order-id" name="order_id" value="<?= $order->OrderID ?>">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500;">Current Date:</label>
                <input type="text" id="track-modal-current-date" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9fafb;" value="<?= !empty($order->InstallationDate) ? date('F j, Y', strtotime($order->InstallationDate)) : 'Not set' ?>">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 500;">New Date:</label>
                <input type="date" id="track-modal-new-date" name="new_date" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                <small style="color: #6b7280; display: block; margin-top: 5px;">Must be within 7 days of original date</small>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="close-track-modal" style="padding: 10px 20px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 20px; background: #003049; color: white; border: none; border-radius: 6px; cursor: pointer;">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trackBtn = document.getElementById('track-request-date-change');
    if (trackBtn) {
        trackBtn.addEventListener('click', function() {
            const modal = document.getElementById('trackDateChangeModal');
            const newDateInput = document.getElementById('track-modal-new-date');
            const allowedUntil = '<?= isset($allowed_until) ? $allowed_until : '' ?>';
            const today = new Date();
            const maxDate = allowedUntil ? new Date(allowedUntil) : new Date(today.getTime() + 7*24*60*60*1000);
            newDateInput.min = today.toISOString().split('T')[0];
            newDateInput.max = maxDate.toISOString().split('T')[0];
            modal.style.display = 'flex';
        });
    }

    document.querySelectorAll('.close-track-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('trackDateChangeModal').style.display = 'none';
        });
    });

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('trackDateChangeModal');
        if (event.target === modal) modal.style.display = 'none';
    });

    const form = document.getElementById('trackDateChangeForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            fetch('<?= base_url('ShopCon/request_installation_date_change') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Date change request submitted successfully', 'success');
                    document.getElementById('trackDateChangeModal').style.display = 'none';
                    location.reload();
                } else {
                    showToast('Error: ' + (data.message || 'Failed to submit request'), 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('An error occurred. Please try again.', 'error');
            });
        });
    }
});
</script>

<script src="<?php echo base_url('assets/js/order-status.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/konva.min.js'); ?>"></script>

<!-- Konva previews modal -->
<div id="konvaPreviewModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:1200;">
    <div style="width:90%; max-width:900px; background:#fff; border-radius:8px; padding:16px; position:relative;">
        <button id="konvaModalClose" style="position:absolute; right:12px; top:12px; background:#e53e3e; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer;">Close</button>
        <div id="konva-modal-container" style="width:100%; height:520px;"></div>
        <div style="margin-top:10px; display:flex; gap:12px; justify-content:space-between; align-items:center;">
            <div id="konva-modal-info" style="color:#374151;"></div>
            <div>
                <button id="konvaModalZoom" style="background:#036; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">Zoom</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Load Konva if available
    if (typeof Konva === 'undefined') return;

    function normalizeCustomization(raw) {
        var parsed = {};
        try { parsed = (typeof raw === 'string') ? JSON.parse(raw) : raw; } catch(e) { parsed = {}; }

        // Determine unit (prefer explicit _unit or unit field)
        var unit = parsed._unit || parsed.unit || parsed.Unit || 'mm';

        // Parse numeric width/height from common fields
        var w = null, h = null;
        if (parsed._width && parsed._height) { w = Number(parsed._width); h = Number(parsed._height); }
        else if (parsed.width && parsed.height) { w = Number(parsed.width); h = Number(parsed.height); }
        else if (parsed.Dimensions) {
            var m = (''+parsed.Dimensions).match(/(\d+(?:\.\d+)?)/g);
            if (m && m.length >= 2) { w = Number(m[0]); h = Number(m[1]); }
        }

        // Normalize to millimeters for consistent ratio calculations
        function toMM(value, unit) {
            if (!value) return null;
            var u = (unit || '').toLowerCase();
            var v = Number(value);
            if (isNaN(v)) return null;
            if (u === 'cm') return v * 10;
            if (u === 'm') return v * 1000;
            if (u === 'in' || u === 'inch') return v * 25.4;
            return v; // assume mm
        }

        var widthMM = toMM(w, unit) || 100;
        var heightMM = toMM(h, unit) || 100;

        return {
            dimensions: { width: { value: widthMM, unit: 'mm' }, height: { value: heightMM, unit: 'mm' } },
            rawUnit: unit,
            shape: (parsed.shape || parsed.GlassShape || 'rectangle').toString().toLowerCase(),
            glassType: (parsed.glassType || parsed.GlassType || 'tempered').toString().toLowerCase(),
            thickness: parsed.thicknessmm || parsed.thickness || parsed.GlassThickness || parsed.thicknessmm || parsed['thicknessmm'] || null,
            edgeFinish: parsed.edgeFinish || parsed.EdgeWork || parsed.edgeWork || '',
            frameType: (parsed.frameType || parsed.FrameType || 'vinyl').toString().toLowerCase(),
            price: parsed.EstimatePrice || parsed.Price || 0
        };
    }

    function renderKonvaInto(container, data) {
        // Destroy previous stage if present
        if (container._konvaStage) {
            try { container._konvaStage.destroy(); } catch (e) {}
            container._konvaStage = null;
        }

        // Clear container
        container.innerHTML = '';
        var sizeW = container.clientWidth || 120;
        var sizeH = container.clientHeight || 90;
        var stage = new Konva.Stage({ container: container, width: sizeW, height: sizeH });
        var layer = new Konva.Layer(); stage.add(layer);
        container._konvaStage = stage;

        var STAGE_SIZE = Math.min(sizeW, sizeH);
        var PADDING = 12;
        var DRAWING_SIZE = STAGE_SIZE - PADDING * 2;

        var widthMM = Number(data.dimensions.width.value) || 100;
        var heightMM = Number(data.dimensions.height.value) || 100;
        var actualRatio = widthMM / heightMM;

        var windowWidth, windowHeight;
        if (actualRatio > 1) { windowWidth = DRAWING_SIZE; windowHeight = DRAWING_SIZE / actualRatio; }
        else { windowHeight = DRAWING_SIZE; windowWidth = DRAWING_SIZE * actualRatio; }
        var offsetX = (sizeW - windowWidth) / 2;
        var offsetY = (sizeH - windowHeight) / 2;

        // Visual styles
        var glassFill = '#E0F2F1';
        var frameColor = '#333'; var frameWidth = Math.max(2, Math.round((Number(data.thickness) || 6) / 4));
        if (data.glassType === 'tinted') { glassFill = '#546E7A'; }
        if (data.frameType === 'wood') { frameColor = '#795548'; frameWidth = Math.max(3, frameWidth+2); }

        // Edge finish mapping (simple visual hint)
        var strokeDash = [];
        if (data.edgeFinish && data.edgeFinish.toLowerCase().indexOf('bevel') !== -1) {
            strokeDash = [6,4];
        }

        // Draw shape: support round/circle and rectangle
        var shapeLower = (data.shape || '').toString().toLowerCase();
        if (shapeLower.indexOf('round') !== -1 || shapeLower.indexOf('circle') !== -1) {
            var cx = offsetX + windowWidth/2;
            var cy = offsetY + windowHeight/2;
            var rx = windowWidth/2;
            var ry = windowHeight/2;
            var ellipse = new Konva.Ellipse({ x: cx, y: cy, radius: { x: rx, y: ry }, fill: glassFill, stroke: frameColor, strokeWidth: frameWidth, dash: strokeDash });
            layer.add(ellipse);
        } else {
            var frame = new Konva.Rect({ x: offsetX, y: offsetY, width: windowWidth, height: windowHeight, fill: glassFill, stroke: frameColor, strokeWidth: frameWidth, cornerRadius: 2, dash: strokeDash });
            layer.add(frame);
        }

        layer.draw();
        return { stage: stage, layer: layer };
    }

    // Render inline previews
    document.querySelectorAll('.konva-preview').forEach(function(el){
        var raw = el.getAttribute('data-customization') || '{}';
        var data = normalizeCustomization(raw);
        renderKonvaInto(el, data);
        el.addEventListener('click', function(){
            var modal = document.getElementById('konvaPreviewModal');
            var container = document.getElementById('konva-modal-container');
            var info = document.getElementById('konva-modal-info');
            modal.style.display = 'flex';
            var largeData = normalizeCustomization(raw);
            renderKonvaInto(container, largeData);
            info.textContent = (largeData.shape || '') + ' • ' + (largeData.dimensions.width.value + ' x ' + largeData.dimensions.height.value + (largeData.dimensions.width.unit||''));
        });
    });

    var modalClose = document.getElementById('konvaModalClose');
    if (modalClose) modalClose.addEventListener('click', function(){ document.getElementById('konvaPreviewModal').style.display='none'; document.getElementById('konva-modal-container').innerHTML=''; });
    var modal = document.getElementById('konvaPreviewModal');
    window.addEventListener('click', function(ev){ if (ev.target === modal) { modal.style.display='none'; document.getElementById('konva-modal-container').innerHTML=''; } });

});
</script>

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
                showToast('Quotation accepted successfully! Redirecting to payment page...', 'success');
                window.location.href = '<?= base_url('payment') ?>?order=' + orderId;
            } else {
                showToast('Error: ' + (data.message || 'Failed to accept quotation. Please try again.'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
    }
    
    // Customer cancel order function (available only before admin approval)
    function cancelCustomerOrder(orderId) {
        if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
            return;
        }
        
        const reason = prompt('Please provide a reason for cancellation (optional):');
        
        // AJAX call to cancel order
        fetch('<?= base_url('ShopCon/customer_cancel_order') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + orderId + '&reason=' + encodeURIComponent(reason || '')
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Order cancelled successfully', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('Error: ' + (data.message || 'Failed to cancel order'), 'error');
            }
        })
        .catch(err => {
            console.error('Error cancelling order:', err);
            showToast('An error occurred while cancelling the order. Please try again.', 'error');
        });
    }
</script>
