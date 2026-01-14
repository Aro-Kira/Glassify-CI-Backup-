<!-- ========================= ADMIN SIDEBAR ========================= -->

<aside class="sidebar" id="sidebar">
    <div class="menu-notch" id="menu-toggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- ========================= LOGO SECTION ========================= -->
    <div class="logo-container">
        <img src="<?php echo base_url('assets/images/img-page/logo.png'); ?>" alt="GlassWorth Builders Logo" class="logo">
    </div>

    <!-- ========================= NAVIGATION MENU ========================= -->
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo (isset($active) && $active == 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-dashboard'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/dashboard.svg'); ?>" alt="Dashboard">
                    <span>Dashboard</span>
                </a>
            </li>
            <?php
            // ============================================
            // PATH HIGHLIGHTING LOGIC
            // Only highlights the active path from root to leaf
            // Example: Order Management → Orders → Direct Orders
            // ============================================
            
            // Orders submenu path detection
            $is_direct_orders = isset($active) && $active == 'direct-orders';
            $is_site_assessed_orders = isset($active) && $active == 'site-assessed-orders';
            $is_orders_path = $is_direct_orders || $is_site_assessed_orders;
            
            // Appointments submenu path detection
            $is_ocular_appointment = isset($active) && $active == 'ocular-appointment';
            $is_installation_appointment = isset($active) && $active == 'installation-appointment';
            $is_appointments_path = $is_ocular_appointment || $is_installation_appointment;
            
            // Production submenu path detection
            $is_fabrication_queue = isset($active) && $active == 'fabrication-queue';
            $is_production_path = $is_fabrication_queue;
            
            // Direct submenu items (no nested children)
            $is_calendar = isset($active) && $active == 'calendar';
            $is_quotations = isset($active) && $active == 'quotations';
            $is_return_orders = isset($active) && $active == 'return-orders';
            
            // Order Management is highlighted ONLY when a child is active
            // This ensures only the active path is highlighted, not siblings
            $is_order_management_path = $is_orders_path || $is_appointments_path || $is_production_path || $is_quotations || $is_return_orders;
            
            // Expansion logic (for showing submenus, not for highlighting)
            $is_orders_expanded = isset($active) && in_array($active, ['orders', 'direct-orders', 'site-assessed-orders']);
            $is_appointments_expanded = isset($active) && in_array($active, ['appointment', 'ocular-appointment', 'installation-appointment']);
            $is_production_expanded = isset($active) && $active == 'fabrication-queue';
            ?>
            <li class="has-submenu <?php echo $is_order_management_path ? 'active expanded' : ''; ?>">
                <a href="#" class="submenu-toggle">
                    <img src="<?php echo base_url('assets/images/img_admin/orders.svg'); ?>" alt="Order Management">
                    <span>Order Management</span>
                    <i class="fas fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item <?php echo $is_orders_expanded ? ($is_orders_path ? 'active ' : '') . 'expanded' : ''; ?>">
                        <a href="#" class="submenu-header">
                            <span>Orders</span>
                            <i class="fas fa-chevron-down submenu-icon"></i>
                        </a>
                        <ul class="submenu-nested">
                            <li class="<?php echo (isset($active) && $active == 'direct-orders') ? 'active' : ''; ?>">
                                <a href="<?php echo base_url('admin-orders?type=direct'); ?>">
                                    <span>Direct Orders</span>
                                </a>
                            </li>
                            <li class="<?php echo (isset($active) && $active == 'site-assessed-orders') ? 'active' : ''; ?>">
                                <a href="<?php echo base_url('admin-orders?type=site-assessed'); ?>">
                                    <span>Site-Assessed Orders</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="submenu-item <?php echo $is_appointments_expanded ? ($is_appointments_path ? 'active ' : '') . 'expanded' : ''; ?>">
                        <a href="#" class="submenu-header">
                            <span>Appointments</span>
                            <i class="fas fa-chevron-down submenu-icon"></i>
                        </a>
                        <ul class="submenu-nested">
                            <li class="<?php echo (isset($active) && $active == 'ocular-appointment') ? 'active' : ''; ?>">
                                <a href="<?php echo base_url('admin-appointment?type=ocular'); ?>">
                                    <span>Ocular / Site Assessment</span>
                                </a>
                            </li>
                            <li class="<?php echo (isset($active) && $active == 'installation-appointment') ? 'active' : ''; ?>">
                                <a href="<?php echo base_url('admin-appointment?type=installation'); ?>">
                                    <span>Installation</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="submenu-item <?php echo $is_production_expanded ? ($is_production_path ? 'active ' : '') . 'expanded' : ''; ?>">
                        <a href="#" class="submenu-header">
                            <span>Production</span>
                            <i class="fas fa-chevron-down submenu-icon"></i>
                        </a>
                        <ul class="submenu-nested">
                            <li class="<?php echo (isset($active) && $active == 'fabrication-queue') ? 'active' : ''; ?>">
                                <a href="<?php echo base_url('admin-production'); ?>">
                                    <span>Fabrication Queue</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="submenu-item <?php echo $is_quotations ? 'active' : ''; ?>">
                        <a href="<?php echo base_url('admin-quotations'); ?>">
                            <span>Quotations</span>
                        </a>
                    </li>
                    <li class="submenu-item <?php echo $is_return_orders ? 'active' : ''; ?>">
                        <a href="<?php echo base_url('admin-return-orders'); ?>">
                            <span>Return Orders</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="<?php echo (isset($active) && $active == 'payments') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-payments'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/payments.svg'); ?>" alt="Payments">
                    <span>Payments</span>
                </a>
            </li>
            <li class="<?php echo (isset($active) && $active == 'calendar') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-calendar'); ?>">
                    <i class="fas fa-calendar-alt" style="margin-right: 8px;"></i>
                    <span>Calendar / Project Timeline</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- ========================= USERS SECTION ========================= -->
    <div class="user-section">
        <span class="section-title">Users</span>
        <ul>
            <li class="<?php echo (isset($active) && $active == 'employee') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-employee'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/employees.svg'); ?>" alt="Employees">
                    <span>Employees</span>
                </a>
            </li>
            <li class="<?php echo (isset($active) && $active == 'endUser') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-endUser'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/end-user.svg'); ?>" alt="End User">
                    <span>End User</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- ========================= GENERAL SECTION ========================= -->
    <div class="general-section">
        <span class="section-title">General</span>
        <ul>
            <li class="<?php echo (isset($active) && $active == 'reports') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-reports'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/reports.svg'); ?>" alt="Reports">
                    <span>Reports</span>
                </a>
            </li>
            <li class="<?php echo (isset($active) && $active == 'product') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-product'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/products.svg'); ?>" alt="Products">
                    <span>Products</span>
                </a>
            </li>
            <li class="<?php echo (isset($active) && $active == 'inventory') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-inventory'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/inventory.svg'); ?>" alt="Inventory">
                    <span>Inventory</span>
                </a>
            </li>
          
            <li class="<?php echo (isset($active) && $active == 'issues') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-issues'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/support.svg'); ?>" alt="Issues/Support">
                    <span>Issues/Support</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
