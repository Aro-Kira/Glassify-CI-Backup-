<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlassWorth BUILDERS - Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="hamburger-menu" id="hamburgerMenu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="top-bar-right">
            <div class="icon-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="icon-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <div class="logo-container">
                    <svg class="logo-window" width="140" height="90" viewBox="0 0 140 90" xmlns="http://www.w3.org/2000/svg">
                        <!-- Window frame with 3D perspective (viewed from lower-left angle) -->
                        <!-- Top horizontal bar -->
                        <line x1="8" y1="18" x2="132" y2="22" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        <!-- Bottom horizontal bar -->
                        <line x1="12" y1="72" x2="136" y2="76" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        <!-- Left vertical bar -->
                        <line x1="8" y1="18" x2="12" y2="72" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        <!-- Right vertical bar -->
                        <line x1="132" y1="22" x2="136" y2="76" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        <!-- Middle vertical divider -->
                        <line x1="70" y1="20" x2="74" y2="74" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                    <div class="logo-text-overlay">
                        <div class="logo-main">GlassWorth</div>
                        <div class="logo-sub">BUILDERS</div>
                    </div>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="#" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <div class="nav-text-item">General</div>
                <a href="inventory.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                        <path d="M9 9h6v6H9z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Inventory</span>
                </a>
                <a href="products.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Products</span>
                </a>
                <a href="reports.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <line x1="3" y1="21" x2="3" y2="9" stroke="currentColor" stroke-width="2"/>
                        <line x1="9" y1="21" x2="9" y2="13" stroke="currentColor" stroke-width="2"/>
                        <line x1="15" y1="21" x2="15" y2="5" stroke="currentColor" stroke-width="2"/>
                        <line x1="21" y1="21" x2="21" y2="17" stroke="currentColor" stroke-width="2"/>
                        <line x1="3" y1="9" x2="21" y2="9" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Reports</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1 class="page-title">Dashboard</h1>
            
            <div class="section-header">
                <h2 class="section-title">My Tasks</h2>
                <span class="section-date">• Today is October 1, 2025</span>
            </div>

            <div class="dashboard-cards">
                <div class="card card-returned">
                    <div class="card-number" id="dashboardReturnedItems">0</div>
                    <div class="card-label">Returned Items</div>
                    <button class="card-button" onclick="viewReturnedItems()">Review</button>
                </div>
                <div class="card card-low-stock">
                    <div class="card-number" id="dashboardLowStock">0</div>
                    <div class="card-label">Low Stock Alert</div>
                    <button class="card-button" onclick="viewLowStockItems()">View Items</button>
                </div>
                <div class="card card-total-products">
                    <div class="card-number" id="dashboardTotalProducts">0</div>
                    <div class="card-label">Total Products</div>
                    <button class="card-button" onclick="viewAllProducts()">View Products</button>
                </div>
            </div>

            <div class="section-header">
                <h2 class="section-title">Recent Activities</h2>
            </div>

            <div class="activities-table">
                <table>
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Item</th>
                            <th>Change</th>
                            <th>Description</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="dashboardActivitiesTableBody">
                        <!-- Activities will be loaded from database -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
    <script>
        // Dashboard card button functions
        function viewReturnedItems() {
            // Navigate to inventory page with returned items filter
            window.location.href = 'inventory.php?filter=returned';
        }

        function viewLowStockItems() {
            // Navigate to inventory page with low stock filter
            window.location.href = 'inventory.php?filter=lowstock';
        }

        function viewAllProducts() {
            // Navigate to products page
            window.location.href = 'products.php';
        }

        // Make functions globally accessible
        window.viewReturnedItems = viewReturnedItems;
        window.viewLowStockItems = viewLowStockItems;
        window.viewAllProducts = viewAllProducts;

        // Load dashboard statistics from database
        function loadDashboardStatistics() {
            fetch('api/get_statistics.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Update Returned Items
                        const returnedItemsEl = document.getElementById('dashboardReturnedItems');
                        if (returnedItemsEl) {
                            returnedItemsEl.textContent = data.data.returnedItems || data.data.recentRequests || 0;
                        }
                        
                        // Update Low Stock Alert
                        const lowStockEl = document.getElementById('dashboardLowStock');
                        if (lowStockEl) {
                            lowStockEl.textContent = data.data.lowStockAlerts || 0;
                        }
                        
                        // Update Total Products
                        const totalProductsEl = document.getElementById('dashboardTotalProducts');
                        if (totalProductsEl) {
                            totalProductsEl.textContent = data.data.totalProducts || 0;
                        }
                    } else {
                        console.error('Error loading dashboard statistics:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching dashboard statistics:', error);
                });
        }

        // Load activities from database
        function loadDashboardActivities() {
            fetch('api/get_activities.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const activitiesTable = document.getElementById('dashboardActivitiesTableBody');
                        if (!activitiesTable) return;
                        
                        // Clear existing rows
                        activitiesTable.innerHTML = '';
                        
                        if (data.data.length === 0) {
                            activitiesTable.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #666;">No activities found</td></tr>';
                            return;
                        }
                        
                        // Populate activities
                        data.data.forEach(activity => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${escapeHtml(activity.action || '')}</td>
                                <td>${escapeHtml(activity.item_name || '')}</td>
                                <td>${escapeHtml(activity.change_description || '')}</td>
                                <td>${escapeHtml(activity.description || '')}</td>
                                <td>${escapeHtml(activity.formatted_timestamp || activity.timestamp || '')}</td>
                            `;
                            activitiesTable.appendChild(row);
                        });
                    } else {
                        console.error('Error loading dashboard activities:', data.message);
                        const activitiesTable = document.getElementById('dashboardActivitiesTableBody');
                        if (activitiesTable) {
                            activitiesTable.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #f44336;">Error loading activities</td></tr>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching dashboard activities:', error);
                    const activitiesTable = document.getElementById('dashboardActivitiesTableBody');
                    if (activitiesTable) {
                        activitiesTable.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #f44336;">Error loading activities. Please check your connection.</td></tr>';
                    }
                });
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Update date display
        function updateDateDisplay() {
            const dateElement = document.querySelector('.section-date');
            if (dateElement) {
                const today = new Date();
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                const formattedDate = today.toLocaleDateString('en-US', options);
                dateElement.textContent = '• Today is ' + formattedDate;
            }
        }

        // Initialize dashboard when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Update date display
            updateDateDisplay();
            
            // Load statistics and activities
            loadDashboardStatistics();
            loadDashboardActivities();
            
            // Auto-refresh statistics every 30 seconds for real-time updates
            setInterval(function() {
                loadDashboardStatistics();
            }, 30000); // Refresh every 30 seconds
            
            // Auto-refresh activities every 30 seconds for real-time updates
            setInterval(function() {
                loadDashboardActivities();
            }, 30000); // Refresh every 30 seconds
        });
    </script>
</body>
</html>

