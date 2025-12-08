<link rel="stylesheet" href="<?= base_url('assets/css/general-customer/pages/home_style.css'); ?>">

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
    $status_lower = strtolower($status);
    if (strpos($status_lower, 'progress') !== false || 
        strpos($status_lower, 'fabrication') !== false || 
        strpos($status_lower, 'pending') !== false ||
        strpos($status_lower, 'approved') !== false) {
        return 'in-progress';
    } elseif (strpos($status_lower, 'complete') !== false) {
        return 'completed';
    } elseif (strpos($status_lower, 'cancel') !== false) {
        return 'cancelled';
    } elseif (strpos($status_lower, 'confirm') !== false) {
        return 'confirmed';
    }
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
    <h1>Welcome back, <span class="highlight"><?= $user_name ?>!</span></h1>
    <p class="subtle">What would you like to do today?</p>

    <div class="hero-cards">
        <div class="hero-card" id="orderProgressCard">
            <div class="card-header">
                <span>Order Progress</span>
                <span class="arrow">▼</span>
            </div>
            <div class="card-body">
                <?php if (isset($orders_in_progress) && $orders_in_progress > 0): ?>
                    <p><?= $orders_in_progress ?> in progress</p>
                <?php else: ?>
                    <p>No orders in progress</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="hero-card">
            <div class="card-header">
                <span>Recent Activity</span>
                <span class="arrow">▼</span>
            </div>
            <div class="card-body">
                <?php if (isset($recent_activity) && $recent_activity): ?>
                    <?php 
                    // Format status for display
                    $status_display = strtolower($recent_activity->Status);
                    if ($status_display == 'in fabrication') {
                        $status_display = 'in fabrication';
                    } elseif ($status_display == 'ready for installation') {
                        $status_display = 'ready for installation';
                    } elseif ($status_display == 'approved') {
                        $status_display = 'approved';
                    } elseif ($status_display == 'pending') {
                        $status_display = 'pending approval';
                    }
                    ?>
                    <p>Order <?= htmlspecialchars($recent_activity->OrderNumber ?? 'GI' . str_pad($recent_activity->OrderID, 3, '0', STR_PAD_LEFT)) ?> <?= $status_display ?></p>
                <?php else: ?>
                    <p>No recent activity</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="hero-card">
            <div class="card-header">
                <span>Appointment</span>
                <span class="arrow">▼</span>
            </div>
            <div class="card-body">
                <?php if (isset($next_appointment) && $next_appointment): ?>
                    <p><?= date('m/d/Y', strtotime($next_appointment->AppointmentDate)) ?> - <?= date('g:i A', strtotime($next_appointment->AppointmentDate . ' +9 hours')) ?></p>
                <?php else: ?>
                    <p>No upcoming appointments</p>
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
                            $product_img = !empty($order->ImageUrl) 
                                ? base_url('uploads/products/' . $order->ImageUrl) 
                                : base_url('assets/img/placeholder.png');
                            ?>
                            <img src="<?= $product_img ?>" alt="<?= htmlspecialchars($order->ProductName ?? 'Product') ?>" class="product-thumb">
                            <span><?= htmlspecialchars($order->ProductName ?? 'Custom Order') ?></span>
                        </td>
                        <td><?= htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT)) ?></td>
                        <td><?= date('M j, Y', strtotime($order->OrderDate)) ?></td>
                        <td><span class="status <?= get_status_class($order->Status) ?>"><?= htmlspecialchars($order->Status) ?></span></td>
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
                            <p><?= get_activity_description($activity->Status, $activity->OrderID) ?></p>
                        </div>
                        <div class="time-stamp">
                            <span><?= date('g:i A', strtotime($activity->OrderDate)) ?></span><br>
                            <span><?= date('m/d/Y', strtotime($activity->OrderDate)) ?></span>
                        </div>
                    </li>
                <?php $index++; endforeach; ?>
            <?php else: ?>
                <li class="activity-item">
                    <div class="activity-text">
                        <strong>No Activity Yet</strong>
                        <p>Your activity feed will appear here once you place an order.</p>
                    </div>
                    <div class="time-stamp">
                        <span>--:-- --</span><br>
                        <span>--/--/----</span>
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
            <?php if (isset($orders) && !empty($orders)): ?>
                <?php 
                $appointment_count = 0;
                    $total_appointments = count($orders);
                // Staff names for display
                $staff_names = ['Joaquin Santos', 'Engr. Cruz', 'M. Lopez', 'R. Garcia', 'J. Reyes'];
                
                foreach ($orders as $order): 
                        $appointment_count++;
                        // Determine service type based on status
                        $service = 'Consultation';
                        $appt_status = 'Pending';
                        if ($order->Status == 'Ready for Installation') {
                            $service = 'Installation';
                            $appt_status = 'Confirmed';
                        } elseif ($order->Status == 'In Fabrication') {
                            $service = 'Ocular Visit';
                            $appt_status = 'Confirmed';
                        } elseif ($order->Status == 'Approved') {
                            $service = 'Ocular Visit';
                            $appt_status = 'Confirmed';
                        } elseif ($order->Status == 'Completed') {
                            $service = 'Installation';
                            $appt_status = 'Completed';
                        } elseif ($order->Status == 'Cancelled') {
                            $service = 'Consultation';
                            $appt_status = 'Cancelled';
                        }
                        
                        // Calculate estimated appointment date
                        $base_date = strtotime($order->OrderDate);
                        $appointment_date = date('m/d/Y - g:i A', strtotime('+14 days', $base_date));
                        
                        // Get staff name (cycle through for demo)
                        $staff_name = $staff_names[($appointment_count - 1) % count($staff_names)];
                        
                        // Determine status class
                        $status_class = strtolower($appt_status);
                        if ($appt_status == 'Confirmed') $status_class = 'confirmed';
                        elseif ($appt_status == 'Pending') $status_class = 'pending';
                        elseif ($appt_status == 'Cancelled') $status_class = 'cancelled';
                        elseif ($appt_status == 'Completed') $status_class = 'completed';
                        
                        // Hide rows beyond 5 initially
                        $hidden_class = ($appointment_count > 5) ? 'hidden-row' : '';
                ?>
                        <tr data-status="<?= $status_class ?>" class="<?= $hidden_class ?>" data-index="<?= $appointment_count ?>">
                        <td><?= htmlspecialchars($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT)) ?></td>
                        <td><?= $service ?></td>
                        <td><?= $appointment_date ?></td>
                        <td><?= $staff_name ?></td>
                        <td>
                            <!-- Display status for all appointment types, not just cancelled -->
                            <span class="status <?= $status_class ?>"><?= $appt_status ?></span>
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
    <?php if (isset($orders) && count($orders) > 5): ?>
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
                <div class="recommendation-card">
                    <?php 
                    $image_url = !empty($product->ImageUrl) 
                        ? base_url('uploads/products/' . $product->ImageUrl) 
                        : base_url('assets/img/placeholder.png');
                    ?>
                    <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($product->ProductName) ?>">
                    <h3><?= htmlspecialchars($product->ProductName) ?></h3>
                    <button onclick="window.location.href='<?= base_url('shop/customize/' . $product->Product_ID) ?>'">Build and Buy</button>
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
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
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
