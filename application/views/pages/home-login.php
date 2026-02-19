<link rel="stylesheet" href="<?= base_url('assets/css/general-customer/pages/home_style.css'); ?>">

<style>
.pagination-btn:hover {
    background-color: #f3f4f6 !important;
    border-color: #9ca3af !important;
}

.pagination-btn.active:hover {
    background-color: #0d3d4d !important;
    border-color: #0d3d4d !important;
}
</style>

<?php
// Helper function to format time ago
function time_ago($datetime) {
    if (!$datetime) return 'No updates';
    
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

// Helper function to get status class
function get_status_class($status) {
    $status_lower = strtolower(trim($status));
    
    // Map specific statuses to their classes
    if ($status_lower === 'disapproved') {
        return 'disapproved';
    } elseif ($status_lower === 'ready to approve') {
        return 'ready-to-approve';
    } elseif ($status_lower === 'approved') {
        return 'approved';
    } elseif ($status_lower === 'completed') {
        return 'completed';
    } elseif ($status_lower === 'in fabrication') {
        return 'in-fabrication';
    } elseif ($status_lower === 'ready for installation') {
        return 'ready-for-installation';
    } elseif ($status_lower === 'pending review' || $status_lower === 'pending') {
        return 'pending';
    } elseif ($status_lower === 'awaiting admin') {
        return 'awaiting-admin';
    } elseif ($status_lower === 'cancelled') {
        return 'cancelled';
    } elseif ($status_lower === 'returned') {
        return 'returned';
    } elseif ($status_lower === 'confirmed') {
        return 'confirmed';
    }
    
    // Fallback for any other status
    return 'pending';
}

// Helper function to get activity message based on status
function get_activity_message($status) {
    $messages = [
        'Pending' => 'Your Order is Placed',
        'Approved' => 'Your Order is Approved',
        'In Fabrication' => 'Your Order is In Fabrication',
        'Ready for Installation' => 'Your Order is Ready for Installation',
        'Completed' => 'Your Order is Completed',
        'Cancelled' => 'Your Order was Cancelled'
    ];
    return isset($messages[$status]) ? $messages[$status] : 'Order Status Updated';
}

// Helper function to get activity description based on status
// Note: $order_id is escaped to prevent XSS attacks
function get_activity_description($status, $order_id) {
    // Escape order_id to prevent XSS vulnerability
    $safe_order_id = htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8');
    
    $descriptions = [
        'Pending' => "Your order <span class='order-id'>$safe_order_id</span> has been placed successfully.",
        'Approved' => "Your order <span class='order-id'>$safe_order_id</span> has been approved.",
        'In Fabrication' => "Your order <span class='order-id'>$safe_order_id</span> is currently in fabrication.",
        'Ready for Installation' => "Your order <span class='order-id'>$safe_order_id</span> is ready for installation.",
        'Completed' => "Your order <span class='order-id'>$safe_order_id</span> is completed.",
        'Cancelled' => "Your order <span class='order-id'>$safe_order_id</span> was cancelled."
    ];
    return isset($descriptions[$status]) ? $descriptions[$status] : "Your order <span class='order-id'>$safe_order_id</span> status has been updated.";
}

// Get user name
$user_name = isset($user) && $user ? htmlspecialchars($user->First_Name) : 'User';
?>

<!-- Welcome Section -->
<section class="dashboard-hero">

<style>
    /* Hero Section */
.dashboard-hero {
   background: 
    linear-gradient(rgba(10, 42, 58, 0.6), rgba(10, 42, 58, 0.6)),
      url("<?php echo base_url('assets/images/img-page/home_bg.png'); ?>");
  background-size: cover;
  background-position: center;
  position: relative; /* important */
  padding: 10%;
  color: #fff;
  text-align: center;
}

</style>
    <h1>Welcome, <span class="highlight"><?= $user_name ?>!</span></h1>
    <p class="subtle">What would you like to do today?</p>

    <div class="hero-cards">
        <div class="hero-card order-summary">
            <div class="card-header">
                <i class="fas fa-shopping-cart card-icon"></i>
                <span>Active Orders</span>
            </div>
            <div class="card-body">
                <div class="card-value">
                    <?php echo isset($orders_in_progress) ? $orders_in_progress : 0; ?>
                </div>
                <div class="card-subtitle">orders in progress</div>
            </div>
        </div>

        <div class="hero-card status-summary">
            <div class="card-header">
                <i class="fas fa-clock card-icon"></i>
                <span>Latest Status</span>
            </div>
            <div class="card-body">
                <?php if (isset($recent_activity) && $recent_activity): ?>
                    <div class="status-badge <?= get_status_class($recent_activity->Status) ?>">
                        <?= htmlspecialchars($recent_activity->Status) ?>
                    </div>
                    <div class="order-ref">Order #<?= htmlspecialchars($recent_activity->OrderNumber ?? 'GI' . str_pad($recent_activity->OrderID, 3, '0', STR_PAD_LEFT)) ?></div>
                <?php else: ?>
                    <div class="no-activity">No orders yet</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="hero-card appointment-summary">
            <div class="card-header">
                <i class="fas fa-calendar card-icon"></i>
                <span>Next Appointment</span>
            </div>
            <div class="card-body">
                <?php if (isset($next_appointment) && $next_appointment): ?>
                    <div class="appointment-date">
                        <?= date('M j, Y', strtotime($next_appointment->AppointmentDate)) ?>
                    </div>
                    <div class="appointment-time">
                        <?= date('g:i A', strtotime($next_appointment->AppointmentTime ?? '09:00')) ?>
                    </div>
                <?php else: ?>
                    <div class="no-appointment">No appointments scheduled</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <p class="last-update">Last Update: <?= isset($last_update) ? time_ago($last_update) : 'No updates yet' ?></p>
</section>

<!-- Order Progress Section -->
<section class="order-progress">
    <div class="section-header">
        <h2>Order Progress Overview</h2>
        <div class="dropdown">
            <button onclick="toggleDropdown('orderDropdown')" class="dropbtn">
                <span class="filter-icon">☰</span> Status <span class="dropdown-arrow">▾</span>
            </button>
            <div id="orderDropdown" class="dropdown-content">
                <div class="dropdown-header">
                    <span>Status</span>
                    <a href="#" onclick="clearOrderFilter(event)" class="clear-link">Clear</a>
                </div>
                <a href="#" onclick="filterOrders('all', this)" class="filter-option active">
                    <span class="option-text">All Orders</span>
                    <span class="checkmark">✓</span>
                </a>
                <a href="#" onclick="filterOrders('in-progress', this)" class="filter-option">
                    <span class="option-text">In Progress</span>
                    <span class="checkmark">✓</span>
                </a>
                <a href="#" onclick="filterOrders('completed', this)" class="filter-option">
                    <span class="option-text">Completed</span>
                    <span class="checkmark">✓</span>
                </a>
                <a href="#" onclick="filterOrders('cancelled', this)" class="filter-option">
                    <span class="option-text">Cancelled</span>
                    <span class="checkmark">✓</span>
                </a>
            </div>
        </div>
    </div>

    <table id="ordersTable">
        <thead>
            <tr>
                <th>Product</th>
                <th>Order ID</th>
                <th>Last Update</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($orders) && !empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <tr data-status="<?= get_status_class($order->Status) ?>">
                        <td class="product-cell">
                            <?php 
                            $image_raw = $order->ImageUrl ?? '';
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
                            <img src="<?= $product_img ?>" alt="<?= htmlspecialchars($order->ProductName ?? 'Product') ?>" class="product-thumb">
                            <span><?= htmlspecialchars($order->ProductName ?? 'Custom Order') ?></span>
                        </td>
                        <td><?= htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT)) ?></td>
                        <td><?= date('M j, Y', strtotime(isset($order->Updated_Date) && $order->Updated_Date ? $order->Updated_Date : $order->OrderDate)) ?></td>
                        <td><span class="status <?= get_status_class($order->Status ?? 'Pending Review') ?>"><?= htmlspecialchars($order->Status ?? 'Pending Review') ?></span></td>
                        <td><a href="<?= base_url('track_order?order=' . $order->OrderID) ?>" class="view-details">View details</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">No orders found. <a href="<?= base_url('products') ?>">Start shopping!</a></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if (isset($orders) && !empty($orders) && isset($total_order_pages) && $total_order_pages > 1): ?>
    <!-- Pagination -->
    <div class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding: 15px 0;">
        <div class="pagination-info" style="color: #6b7280; font-size: 14px;">
            Showing <?= (($current_order_page - 1) * $orders_per_page) + 1 ?> to <?= min($current_order_page * $orders_per_page, $total_orders) ?> of <?= $total_orders ?> orders
        </div>
        <div class="pagination-controls" style="display: flex; gap: 8px;">
            <?php if ($current_order_page > 1): ?>
                <a href="<?= base_url('home-login?page=' . ($current_order_page - 1)) ?>" 
                   class="pagination-btn" 
                   style="padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; text-decoration: none; background: #fff; transition: all 0.2s;">
                    &lsaquo; Prev
                </a>
            <?php endif; ?>
            
            <?php
            $start_page = max(1, $current_order_page - 2);
            $end_page = min($total_order_pages, $current_order_page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <a href="<?= base_url('home-login?page=' . $i) ?>" 
                   class="pagination-btn <?= $i === $current_order_page ? 'active' : '' ?>" 
                   style="padding: 6px 12px; border: 1px solid <?= $i === $current_order_page ? '#0d3d4d' : '#d1d5db' ?>; border-radius: 6px; color: <?= $i === $current_order_page ? '#fff' : '#374151' ?>; background: <?= $i === $current_order_page ? '#0d3d4d' : '#fff' ?>; text-decoration: none; font-weight: <?= $i === $current_order_page ? '600' : '400' ?>; transition: all 0.2s;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($current_order_page < $total_order_pages): ?>
                <a href="<?= base_url('home-login?page=' . ($current_order_page + 1)) ?>" 
                   class="pagination-btn" 
                   style="padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; text-decoration: none; background: #fff; transition: all 0.2s;">
                    Next &rsaquo;
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<!-- Activity Feed -->
<section class="activity-feed">
    <h2>Activity Feed</h2>
    <div class="activity-divider"></div>
    <div class="activity-list-container" id="activityListContainer">
        <ul id="activityList">
            <?php if (isset($activity_feed) && !empty($activity_feed)): ?>
                <?php $index = 0; foreach ($activity_feed as $activity): ?>
                    <li class="activity-item <?= $index >= 5 ? 'hidden-item' : '' ?>" data-index="<?= $index ?>">
                        <div class="activity-text">
                            <strong><?= get_activity_message($activity->Status) ?></strong>
                            <p><?= get_activity_description($activity->Status, $activity->OrderNumber ?? 'GI' . str_pad($activity->OrderID, 3, '0', STR_PAD_LEFT)) ?></p>
                        </div>
                        <div class="time-stamp">
                            <?php 
                            $display_date = isset($activity->Updated_Date) && $activity->Updated_Date ? $activity->Updated_Date : $activity->OrderDate;
                            ?>
                            <span><?= date('g:i A', strtotime($display_date)) ?></span><br>
                            <span><?= date('m/d/Y', strtotime($display_date)) ?></span>
                        </div>
                    </li>
                <?php $index++; endforeach; ?>
            <?php else: ?>
                <li class="activity-item">
                    <div class="activity-text">
                        <strong>No Activity Yet</strong>
                        <p>Your activity feed will appear here once you place an order.</p>
                    </div>
                    <div class="time-stamp" style="display: none;">
                        <!-- Timestamp hidden when no activity -->
                    </div>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <?php if (isset($activity_feed) && count($activity_feed) > 5): ?>
        <div class="read-all" id="readAllBtn" onclick="toggleActivityFeed()">
            <span class="read-all-text">Read all</span> <span class="arrow">▼</span>
        </div>
    <?php endif; ?>
</section>

<!-- Appointment Section -->
<section class="appointment">
    <div class="section-header">
        <h2>Appointment</h2>
        <div class="dropdown">
            <button onclick="toggleDropdown('appointmentDropdown')" class="dropbtn">
                <span class="filter-icon">☰</span> Status <span class="dropdown-arrow">▾</span>
            </button>
            <div id="appointmentDropdown" class="dropdown-content">
                <div class="dropdown-header">
                    <span>Status</span>
                    <a href="#" onclick="clearAppointmentFilter(event)" class="clear-link">Clear</a>
                </div>
                <a href="#" onclick="filterAppointments('all', this)" class="filter-option active">
                    <span class="option-text">All Status</span>
                    <span class="checkmark">✓</span>
                </a>
                <a href="#" onclick="filterAppointments('pending', this)" class="filter-option">
                    <span class="option-text">Pending</span>
                    <span class="checkmark">✓</span>
                </a>
                <a href="#" onclick="filterAppointments('confirmed', this)" class="filter-option">
                    <span class="option-text">Confirmed</span>
                    <span class="checkmark">✓</span>
                </a>
                <a href="#" onclick="filterAppointments('cancelled', this)" class="filter-option">
                    <span class="option-text">Cancelled</span>
                    <span class="checkmark">✓</span>
                </a>
            </div>
        </div>
    </div>

    <div class="appointment-table-wrapper" id="appointmentTableWrapper">
    <table id="appointmentsTable">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Service</th>
                <th>Date & Time</th>
                <th>Assigned Staff</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($appointments) && !empty($appointments)): ?>
                <?php 
                $appointment_count = 0;
                
                foreach ($appointments as $appointment): 
                        $appointment_count++;
                        
                        // Get actual appointment data from database
                        $order_id = htmlspecialchars($appointment->OrderNumber ?? 'GI' . str_pad($appointment->OrderID, 3, '0', STR_PAD_LEFT));
                        $service = htmlspecialchars($appointment->ServiceType ?? 'Consultation');
                        $appt_status = htmlspecialchars($appointment->AppointmentStatus ?? 'Pending');
                        $staff_name = htmlspecialchars($appointment->AssignedStaff ?? 'TBD');
                        
                        // Format appointment date and time
                        $appt_date = isset($appointment->AppointmentDate) ? $appointment->AppointmentDate : 'TBD';
                        $appt_time = isset($appointment->AppointmentTime) ? $appointment->AppointmentTime : '09:00';
                        
                        if ($appt_date !== 'TBD') {
                            $appointment_date = date('m/d/Y - g:i A', strtotime($appt_date . ' ' . $appt_time));
                        } else {
                            $appointment_date = 'TBD';
                        }
                        
                        // Determine status class - map database values to CSS classes
                        $status_class = strtolower(trim($appt_status));
                        if ($appt_status == 'In Progress') {
                            $status_class = 'in-progress';
                            $display_status = 'In Progress';
                        } elseif ($appt_status == 'Complete') {
                            $status_class = 'completed';
                            $display_status = 'Completed';
                        } elseif ($appt_status == 'Confirmed') {
                            $status_class = 'confirmed';
                            $display_status = 'Confirmed';
                        } elseif ($appt_status == 'Pending') {
                            $status_class = 'pending';
                            $display_status = 'Pending';
                        } elseif ($appt_status == 'Cancelled') {
                            $status_class = 'cancelled';
                            $display_status = 'Cancelled';
                        } else {
                            $status_class = 'pending';
                            $display_status = $appt_status;
                        }
                        
                        // Hide rows beyond 5 initially
                        $hidden_class = ($appointment_count > 5) ? 'hidden-row' : '';
                ?>
                        <tr data-status="<?= $status_class ?>" class="<?= $hidden_class ?>" data-index="<?= $appointment_count ?>">
                        <td><?= $order_id ?></td>
                        <td><?= $service ?></td>
                        <td><?= $appointment_date ?></td>
                        <td><?= $staff_name ?></td>
                        <td>
                            <!-- Display status for all appointment types, not just cancelled -->
                            <span class="status <?= $status_class ?>"><?= $display_status ?></span>
                        </td>
                    </tr>
                <?php 
                endforeach; 
                ?>
                <?php if ($appointment_count == 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">No upcoming appointments</td>
                    </tr>
                <?php endif; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">No appointments scheduled</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <?php if (isset($appointments) && count($appointments) > 5): ?>
    <div class="see-more-container">
        <a href="#" class="see-more-link" id="appointmentSeeMore">See more <span class="arrow">▼</span></a>
    </div>
    <?php endif; ?>
</section>

<!-- Recommendations -->
<section class="recommendations">
    <div class="section-header">
        <h2>You May Also Like</h2>
        <a href="<?= base_url('products') ?>" class="see-more">See more</a>
    </div>
    <div class="recommendation-grid">
        <?php if (isset($recommendations) && !empty($recommendations)): ?>
            <?php 
            // Limit to exactly 4 cards and randomize
            $recommendations_array = is_array($recommendations) ? $recommendations : (array)$recommendations;
            
            // Shuffle array to randomize (double check randomization)
            shuffle($recommendations_array);
            
            // Limit to 4 items
            $limited_recommendations = array_slice($recommendations_array, 0, 4);
            
            foreach ($limited_recommendations as $product): 
            ?>
                <div class="recommendation-card" style="text-align: left;">
                    <?php 
                    $image_raw = $product->ImageUrl ?? '';
                    $placeholder_svg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                    $image_url = $placeholder_svg;
                    
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
                                $image_url = $first_image;
                            } else if (strpos($first_image, 'assets/') === 0 || strpos($first_image, 'uploads/') === 0) {
                                $image_url = base_url($first_image);
                            } else {
                                $image_url = base_url('uploads/products/' . basename($first_image));
                            }
                        }
                    }
                    ?>
                    <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($product->ProductName) ?>">
                    <h3 style="font-weight: bold; color: white; text-align: center;"><?= htmlspecialchars($product->ProductName) ?></h3>
                    <!-- <p style="color: white; text-align: left !important; margin: 4px 0; font-size: 14px;">Type: <span style="font-weight: bold;"><?php 
                        // Determine order type based on category
                        $category = strtolower($product->Category ?? '');
                        if (in_array($category, ['shower enclosure', 'windows', 'railings', 'canopy'])) {
                            echo 'Site Assessment';
                        } else {
                            echo 'Direct Order';
                        }
                    ?></span></p> -->
                    <p style="color: white; text-align: left !important; margin: 4px 0; font-size: 14px;">Price: <span style="font-weight: bold;">₱<?= number_format($product->Price, 2) ?></span></p>
                    <button onclick="window.location.href='<?= base_url('2DModeling?id=' . $product->Product_ID) ?>'">Build and Buy</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback static recommendations -->
            <div class="recommendation-card">
                <img src="<?= base_url('assets/img/image 1.png'); ?>" alt="Product">
                <h3>Explore Our Products</h3>
                <button onclick="window.location.href='<?= base_url('shop') ?>'">View Shop</button>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function toggleDropdown(id) {
    // Close all other dropdowns first
    let dropdowns = document.getElementsByClassName("dropdown-content");
    for (let i = 0; i < dropdowns.length; i++) {
        if (dropdowns[i].id !== id) {
            dropdowns[i].classList.remove("show");
        }
    }
    document.getElementById(id).classList.toggle("show");
}

window.onclick = function (e) {
    if (!e.target.matches('.dropbtn') && !e.target.closest('.dropbtn')) {
        let dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            dropdowns[i].classList.remove("show");
        }
    }
}

document.querySelectorAll(".hero-card").forEach(card => {
    card.querySelector(".card-header").addEventListener("click", () => {
        card.classList.toggle("active");
    });
});

// Filter orders by status
function filterOrders(status, element) {
    event.preventDefault();
    
    const rows = document.querySelectorAll('#ordersTable tbody tr[data-status]');
    const inProgressStatuses = ['pending', 'approved', 'in-fabrication', 'ready-for-installation'];
    
    rows.forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else if (status === 'in-progress') {
            // Show rows that match any in-progress status
            row.style.display = inProgressStatuses.includes(row.dataset.status) ? '' : 'none';
        } else if (status === 'completed') {
            row.style.display = row.dataset.status === 'completed' ? '' : 'none';
        } else if (status === 'cancelled') {
            row.style.display = row.dataset.status === 'cancelled' ? '' : 'none';
        } else {
            row.style.display = row.dataset.status === status ? '' : 'none';
        }
    });
    
    // Update active state and checkmark
    const dropdown = document.getElementById('orderDropdown');
    dropdown.querySelectorAll('.filter-option').forEach(opt => {
        opt.classList.remove('active');
    });
    if (element) {
        element.classList.add('active');
    }
}

// Clear order filter
function clearOrderFilter(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropdown = document.getElementById('orderDropdown');
    const allOption = dropdown.querySelector('.filter-option');
    filterOrders('all', allOption);
}

// Filter appointments by status
function filterAppointments(status, element) {
    event.preventDefault();
    
    const rows = document.querySelectorAll('#appointmentsTable tbody tr[data-status]');
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update active state and checkmark
    const dropdown = document.getElementById('appointmentDropdown');
    dropdown.querySelectorAll('.filter-option').forEach(opt => {
        opt.classList.remove('active');
    });
    if (element) {
        element.classList.add('active');
    }
}

// Clear appointment filter
function clearAppointmentFilter(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropdown = document.getElementById('appointmentDropdown');
    const allOption = dropdown.querySelector('.filter-option');
    filterAppointments('all', allOption);
}

// Show/hide more appointments - progressive loading
let currentVisibleRows = 5; // Initially showing 5 rows
const rowsPerClick = 5; // Add 5 rows per click
const maxBeforeScroll = 10; // Make scrollable after 10 rows

document.getElementById('appointmentSeeMore')?.addEventListener('click', function(e) {
    e.preventDefault();
    const wrapper = document.getElementById('appointmentTableWrapper');
    const table = document.getElementById('appointmentsTable');
    const rows = table.querySelectorAll('tbody tr[data-index]');
    const totalRows = rows.length;
    
    // Check if all rows are already visible
    const allVisible = currentVisibleRows >= totalRows;
    
    if (allVisible) {
        // Collapse back to 5 rows
        currentVisibleRows = 5;
        rows.forEach((row) => {
            const index = parseInt(row.dataset.index);
            if (index > 5) {
                row.classList.add('hidden-row');
            }
        });
        
        // Remove scrollable
        wrapper.classList.remove('scrollable');
        
        this.innerHTML = 'See more <span class="arrow">▼</span>';
        
        // Scroll back to appointment section
        document.querySelector('.appointment').scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        // Show more rows (add rowsPerClick more)
        currentVisibleRows = Math.min(currentVisibleRows + rowsPerClick, totalRows);
        
        rows.forEach((row) => {
            const index = parseInt(row.dataset.index);
            if (index <= currentVisibleRows) {
                row.classList.remove('hidden-row');
            }
        });
        
        // If visible rows exceed maxBeforeScroll, make it scrollable
        if (currentVisibleRows > maxBeforeScroll) {
            wrapper.classList.add('scrollable');
        }
        
        // Update button text based on whether all rows are now visible
        if (currentVisibleRows >= totalRows) {
            this.innerHTML = 'Show less <span class="arrow">▲</span>';
        } else {
            this.innerHTML = 'See more <span class="arrow">▼</span>';
        }
    }
});

// Toggle activity feed - show more rows
let activityExpanded = false;
function toggleActivityFeed() {
    const container = document.getElementById('activityListContainer');
    const items = document.querySelectorAll('#activityList .activity-item');
    const readAllBtn = document.getElementById('readAllBtn');
    const totalItems = items.length;
    
    activityExpanded = !activityExpanded;
    
    if (activityExpanded) {
        // Show all items
        items.forEach(item => {
            item.classList.remove('hidden-item');
            item.classList.add('visible-item');
        });
        
        // If more than 10 items, make it scrollable
        if (totalItems > 10) {
            container.classList.add('scrollable');
        }
        
        readAllBtn.innerHTML = '<span class="read-all-text">Show less</span> <span class="arrow">▲</span>';
        readAllBtn.classList.add('expanded');
    } else {
        // Hide items after index 5
        items.forEach((item, index) => {
            if (index >= 5) {
                item.classList.add('hidden-item');
                item.classList.remove('visible-item');
            }
        });
        
        // Remove scrollable
        container.classList.remove('scrollable');
        
        readAllBtn.innerHTML = '<span class="read-all-text">Read all</span> <span class="arrow">▼</span>';
        readAllBtn.classList.remove('expanded');
        
        // Scroll back to top of activity feed
        document.querySelector('.activity-feed').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>
