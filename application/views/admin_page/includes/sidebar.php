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
            <li class="<?php echo (isset($active) && $active == 'orders') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-orders'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/orders.svg'); ?>" alt="Orders">
                    <span>Orders</span>
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
            <li class="<?php echo (isset($active) && $active == 'appointment') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-appointment'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/appointments.svg'); ?>" alt="Appointments">
                    <span>Appointments</span>
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
            <li class="<?php echo (isset($active) && $active == 'payments') ? 'active' : ''; ?>">
                <a href="<?php echo base_url('admin-payments'); ?>">
                    <img src="<?php echo base_url('assets/images/img_admin/payments.svg'); ?>" alt="Payments">
                    <span>Payments</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
