<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlassWorth BUILDERS - Reports</title>
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
                <a href="index.php" class="nav-item">
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
                <a href="reports.php" class="nav-item active">
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
            <h1 class="page-title">Reports</h1>

            <!-- Filter Bar -->
            <div class="reports-filter-bar">
                <div class="reports-filter-group">
                    <label for="reportType">Report Type</label>
                    <select class="reports-filter-select" id="reportType" onchange="filterReports()">
                        <option value="all">All Reports</option>
                        <option value="sales">Sales Report</option>
                        <option value="inventory">Inventory Report</option>
                        <option value="purchases">Purchases Report</option>
                        <option value="products">Products Report</option>
                    </select>
                </div>
                <div class="reports-filter-group">
                    <label for="dateFrom">From Date</label>
                    <input type="date" class="reports-date-input" id="dateFrom" onchange="filterReports()">
                </div>
                <div class="reports-filter-group">
                    <label for="dateTo">To Date</label>
                    <input type="date" class="reports-date-input" id="dateTo" onchange="filterReports()">
                </div>
                <button class="btn-export" onclick="exportReport()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Export
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="reports-summary-cards">
                <div class="report-card">
                    <div class="report-card-icon" style="background-color: #e3f2fd;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="#2196f3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="report-card-content">
                        <div class="report-card-label">Total Sales</div>
                        <div class="report-card-value" id="totalSales">₱245,680.00</div>
                    </div>
                </div>
                <div class="report-card">
                    <div class="report-card-icon" style="background-color: #f3e5f5;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="#9c27b0" stroke-width="2"/>
                            <path d="M9 9h6v6H9z" stroke="#9c27b0" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="report-card-content">
                        <div class="report-card-label">Items Sold</div>
                        <div class="report-card-value" id="itemsSold">1,245</div>
                    </div>
                </div>
                <div class="report-card">
                    <div class="report-card-icon" style="background-color: #e8f5e9;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="#4caf50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="8.5" cy="7" r="4" stroke="#4caf50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20 8v6M23 11h-6" stroke="#4caf50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="report-card-content">
                        <div class="report-card-label">New Customers</div>
                        <div class="report-card-value" id="newCustomers">48</div>
                    </div>
                </div>
                <div class="report-card">
                    <div class="report-card-icon" style="background-color: #fff3e0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke="#ff9800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96" stroke="#ff9800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="12" y1="22.08" x2="12" y2="12" stroke="#ff9800" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="report-card-content">
                        <div class="report-card-label">Orders</div>
                        <div class="report-card-value" id="totalOrders">156</div>
                    </div>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="reports-table-container">
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Report Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTableBody">
                        <!-- Reports will be generated here -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="products-pagination" id="reportsPagination">
                <button class="pagination-btn" id="prevReportPageBtn" onclick="goToPreviousReportPage()" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="pagination-pages" id="reportPaginationPages">
                    <!-- Page numbers will be generated here -->
                </div>
                <button class="pagination-btn" id="nextReportPageBtn" onclick="goToNextReportPage()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </main>
    </div>

    <!-- Report Detail Modal -->
    <div class="modal-overlay" id="reportDetailModal">
        <div class="modal-content report-detail-modal">
            <div class="report-detail-header">
                <h2 id="reportDetailTitle">Report Details</h2>
                <button class="modal-close" id="closeReportDetailModal">&times;</button>
            </div>
            <div class="report-detail-body">
                <div class="report-detail-info">
                    <div class="report-detail-row">
                        <span class="report-detail-label">Date:</span>
                        <span class="report-detail-value" id="reportDetailDate">-</span>
                    </div>
                    <div class="report-detail-row">
                        <span class="report-detail-label">Report Type:</span>
                        <span class="report-detail-value" id="reportDetailType">-</span>
                    </div>
                    <div class="report-detail-row">
                        <span class="report-detail-label">Description:</span>
                        <span class="report-detail-value" id="reportDetailDescription">-</span>
                    </div>
                    <div class="report-detail-row">
                        <span class="report-detail-label">Amount:</span>
                        <span class="report-detail-value" id="reportDetailAmount">-</span>
                    </div>
                    <div class="report-detail-row">
                        <span class="report-detail-label">Status:</span>
                        <span class="report-detail-value" id="reportDetailStatus">-</span>
                    </div>
                </div>
                <div class="report-detail-actions">
                    <button class="btn-view-detail" onclick="handleViewReportDetail()">View</button>
                    <button class="btn-download-detail" onclick="handleDownloadReportDetail()">Download</button>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Reports data loaded from database
        let reportsData = [];
        let filteredReports = [];
        let currentReportPage = 1;
        const reportsPerPage = 10;

        // Load reports from database
        function loadReportsFromDatabase() {
            fetch('api/get_reports.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        reportsData = data.data;
                        
                        // Re-apply current filters after loading
                        applyFiltersToReports();
                        
                        // Update summary cards
                        updateSummaryCards();
                        
                        // Render reports and update pagination
                        renderReports();
                        updateReportPagination();
                    } else {
                        console.error('Error loading reports:', data.message);
                        const tbody = document.getElementById('reportsTableBody');
                        if (tbody) {
                            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px; color: #f44336;">Error loading reports. Please check your connection.</td></tr>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching reports:', error);
                    const tbody = document.getElementById('reportsTableBody');
                    if (tbody) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px; color: #f44336;">Error loading reports. Please check your connection.</td></tr>';
                    }
                });
        }

        // Load report statistics from database
        function loadReportStatistics() {
            const reportType = document.getElementById('reportType');
            const dateFrom = document.getElementById('dateFrom');
            const dateTo = document.getElementById('dateTo');
            
            const typeValue = reportType ? reportType.value : 'all';
            const fromValue = dateFrom ? dateFrom.value : '';
            const toValue = dateTo ? dateTo.value : '';
            
            // Build query string
            let queryParams = [];
            if (typeValue !== 'all') {
                queryParams.push('type=' + encodeURIComponent(typeValue));
            }
            if (fromValue) {
                queryParams.push('dateFrom=' + encodeURIComponent(fromValue));
            }
            if (toValue) {
                queryParams.push('dateTo=' + encodeURIComponent(toValue));
            }
            
            const queryString = queryParams.length > 0 ? '?' + queryParams.join('&') : '';
            
            fetch('api/get_report_statistics.php' + queryString)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Update summary cards
                        const totalSalesEl = document.getElementById('totalSales');
                        const itemsSoldEl = document.getElementById('itemsSold');
                        const newCustomersEl = document.getElementById('newCustomers');
                        const totalOrdersEl = document.getElementById('totalOrders');
                        
                        if (totalSalesEl) {
                            totalSalesEl.textContent = `₱${data.data.totalSales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                        }
                        if (itemsSoldEl) {
                            itemsSoldEl.textContent = data.data.itemsSold.toLocaleString();
                        }
                        if (newCustomersEl) {
                            newCustomersEl.textContent = data.data.newCustomers;
                        }
                        if (totalOrdersEl) {
                            totalOrdersEl.textContent = data.data.totalOrders;
                        }
                    } else {
                        console.error('Error loading report statistics:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching report statistics:', error);
                });
        }

        // Apply filters to loaded reports
        function applyFiltersToReports() {
            const reportType = document.getElementById('reportType');
            const dateFrom = document.getElementById('dateFrom');
            const dateTo = document.getElementById('dateTo');

            const typeValue = reportType ? reportType.value : 'all';
            const fromValue = dateFrom ? dateFrom.value : '';
            const toValue = dateTo ? dateTo.value : '';

            filteredReports = reportsData.filter(report => {
                const matchesType = typeValue === 'all' || report.type === typeValue;
                
                // Only filter by date if dates are provided
                if (!fromValue && !toValue) {
                    return matchesType;
                }
                
                const reportDate = new Date(report.date);
                reportDate.setHours(0, 0, 0, 0); // Normalize to start of day
                
                let matchesDate = true;
                if (fromValue) {
                    const fromDate = new Date(fromValue);
                    fromDate.setHours(0, 0, 0, 0);
                    matchesDate = matchesDate && reportDate >= fromDate;
                }
                if (toValue) {
                    const toDate = new Date(toValue);
                    toDate.setHours(23, 59, 59, 999); // Include entire end date
                    matchesDate = matchesDate && reportDate <= toDate;
                }
                
                return matchesType && matchesDate;
            });
        }

        // Initialize reports display
        document.addEventListener('DOMContentLoaded', function() {
            // Set default dates (current month)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            const dateFromInput = document.getElementById('dateFrom');
            const dateToInput = document.getElementById('dateTo');
            
            if (dateFromInput) {
                dateFromInput.value = firstDay.toISOString().split('T')[0];
            }
            if (dateToInput) {
                dateToInput.value = lastDay.toISOString().split('T')[0];
            }
            
            // Ensure table is visible
            const tableContainer = document.querySelector('.reports-table-container');
            if (tableContainer) {
                tableContainer.style.display = 'block';
            }
            
            // Load reports from database first
            loadReportsFromDatabase();
            loadReportStatistics();
            
            // Auto-refresh reports every 30 seconds for real-time updates
            setInterval(function() {
                loadReportsFromDatabase();
                loadReportStatistics();
            }, 30000); // Refresh every 30 seconds
        });

        function renderReports() {
            const tbody = document.getElementById('reportsTableBody');
            if (!tbody) {
                console.error('Reports table body not found!');
                return;
            }

            // Ensure table container is visible
            const tableContainer = document.querySelector('.reports-table-container');
            if (tableContainer) {
                tableContainer.style.display = 'block';
            }

            const startIndex = (currentReportPage - 1) * reportsPerPage;
            const endIndex = startIndex + reportsPerPage;
            const reportsToShow = filteredReports.slice(startIndex, endIndex);

            tbody.innerHTML = '';

            if (reportsToShow.length === 0) {
                // Show message when no reports match filters
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                        No reports found matching the selected filters.
                    </td>
                `;
                tbody.appendChild(row);
                return;
            }

            reportsToShow.forEach(report => {
                const row = document.createElement('tr');
                
                const formattedDate = new Date(report.date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                const typeLabels = {
                    'sales': 'Sales Report',
                    'inventory': 'Inventory Report',
                    'purchases': 'Purchases Report',
                    'products': 'Products Report'
                };
                
                const statusClass = report.status === 'completed' ? 'status-completed' : 'status-pending';
                const statusText = report.status === 'completed' ? 'Completed' : 'Pending';
                
                const amountDisplay = report.amount > 0 ? `₱${report.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '-';
                
                row.innerHTML = `
                    <td>${formattedDate}</td>
                    <td>${typeLabels[report.type] || report.type}</td>
                    <td>${report.description}</td>
                    <td>${amountDisplay}</td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td>
                        <button class="btn-view-report" onclick="viewReport(${report.id})">View</button>
                        <button class="btn-download-report" onclick="downloadReport(${report.id})">Download</button>
                    </td>
                `;

                tbody.appendChild(row);
            });
        }

        function filterReports() {
            // Apply filters to current reports data
            applyFiltersToReports();
            
            // Update summary cards from database with current filters
            loadReportStatistics();

            // Reset to first page when filtering
            currentReportPage = 1;
            
            // Render reports and update pagination
            renderReports();
            updateReportPagination();
            
            // Scroll to top of table if there are results
            if (filteredReports.length > 0) {
                const tableContainer = document.querySelector('.reports-table-container');
                if (tableContainer) {
                    tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }

        function updateSummaryCards() {
            // This function is now handled by loadReportStatistics()
            // Keeping it for backward compatibility but it will be called from loadReportStatistics
            loadReportStatistics();
        }

        function updateReportPagination() {
            const totalPages = Math.ceil(filteredReports.length / reportsPerPage);
            const paginationPages = document.getElementById('reportPaginationPages');
            const prevBtn = document.getElementById('prevReportPageBtn');
            const nextBtn = document.getElementById('nextReportPageBtn');

            if (!paginationPages) return;

            paginationPages.innerHTML = '';

            // Smart pagination: show max 5 page buttons
            let startPage = Math.max(1, currentReportPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            
            // Adjust start page if we're near the end
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            // Show first page and ellipsis if needed
            if (startPage > 1) {
                const firstBtn = document.createElement('button');
                firstBtn.className = 'pagination-page-btn';
                firstBtn.textContent = '1';
                firstBtn.onclick = () => goToReportPage(1);
                paginationPages.appendChild(firstBtn);
                
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '...';
                    paginationPages.appendChild(ellipsis);
                }
            }

            // Show page number buttons
            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = 'pagination-page-btn';
                if (i === currentReportPage) {
                    pageBtn.className += ' active';
                }
                pageBtn.textContent = i;
                pageBtn.onclick = () => goToReportPage(i);
                paginationPages.appendChild(pageBtn);
            }

            // Show last page and ellipsis if needed
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '...';
                    paginationPages.appendChild(ellipsis);
                }
                
                const lastBtn = document.createElement('button');
                lastBtn.className = 'pagination-page-btn';
                lastBtn.textContent = totalPages;
                lastBtn.onclick = () => goToReportPage(totalPages);
                paginationPages.appendChild(lastBtn);
            }

            // Update prev/next buttons
            if (prevBtn) {
                prevBtn.disabled = currentReportPage === 1 || totalPages === 0;
            }
            if (nextBtn) {
                nextBtn.disabled = currentReportPage === totalPages || totalPages === 0;
            }
        }

        function goToReportPage(page) {
            currentReportPage = page;
            renderReports();
            updateReportPagination();
        }

        function goToPreviousReportPage() {
            if (currentReportPage > 1) {
                goToReportPage(currentReportPage - 1);
            }
        }

        function goToNextReportPage() {
            const totalPages = Math.ceil(filteredReports.length / reportsPerPage);
            if (currentReportPage < totalPages) {
                goToReportPage(currentReportPage + 1);
            }
        }

        // Store current report ID for modal
        let currentReportId = null;

        function viewReport(reportId) {
            currentReportId = reportId;
            const report = reportsData.find(r => r.id === reportId);
            
            if (!report) {
                alert('Report not found!');
                return;
            }

            // Open modal and populate details
            openReportDetailModal(report);
        }

        function openReportDetailModal(report) {
            const modal = document.getElementById('reportDetailModal');
            const title = document.getElementById('reportDetailTitle');
            const date = document.getElementById('reportDetailDate');
            const type = document.getElementById('reportDetailType');
            const description = document.getElementById('reportDetailDescription');
            const amount = document.getElementById('reportDetailAmount');
            const status = document.getElementById('reportDetailStatus');

            if (modal && title) {
                title.textContent = report.description;
                
                const formattedDate = new Date(report.date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                
                const typeLabels = {
                    'sales': 'Sales Report',
                    'inventory': 'Inventory Report',
                    'purchases': 'Purchases Report',
                    'products': 'Products Report'
                };
                
                const statusClass = report.status === 'completed' ? 'status-completed' : 'status-pending';
                const statusText = report.status === 'completed' ? 'Completed' : 'Pending';
                const amountDisplay = report.amount > 0 ? `₱${report.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '-';
                
                if (date) date.textContent = formattedDate;
                if (type) type.textContent = typeLabels[report.type] || report.type;
                if (description) description.textContent = report.description;
                if (amount) amount.textContent = amountDisplay;
                if (status) {
                    status.innerHTML = `<span class="status-badge ${statusClass}">${statusText}</span>`;
                }
                
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeReportDetailModal() {
            const modal = document.getElementById('reportDetailModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        function handleViewReportDetail() {
            if (!currentReportId) return;
            
            const report = reportsData.find(r => r.id === currentReportId);
            if (report) {
                // Generate a detailed view/preview of the report
                const reportPreview = `
Report: ${report.description}
Date: ${new Date(report.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
Type: ${report.type}
Amount: ${report.amount > 0 ? `₱${report.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : 'N/A'}
Status: ${report.status}

[Report content would be displayed here in a real application]
                `.trim();
                
                // Open in a new window or show preview
                const newWindow = window.open('', '_blank', 'width=800,height=600');
                newWindow.document.write(`
                    <html>
                        <head>
                            <title>${report.description}</title>
                            <style>
                                body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
                                h1 { color: #1a4d4d; border-bottom: 2px solid #ffeb3b; padding-bottom: 10px; }
                                .report-info { margin: 20px 0; }
                                .report-info div { margin: 10px 0; }
                                .label { font-weight: bold; color: #666; }
                            </style>
                        </head>
                        <body>
                            <h1>${report.description}</h1>
                            <div class="report-info">
                                <div><span class="label">Date:</span> ${new Date(report.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                                <div><span class="label">Type:</span> ${report.type}</div>
                                <div><span class="label">Amount:</span> ${report.amount > 0 ? `₱${report.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : 'N/A'}</div>
                                <div><span class="label">Status:</span> ${report.status}</div>
                            </div>
                            <p>Report details and data would be displayed here...</p>
                        </body>
                    </html>
                `);
                newWindow.document.close();
            }
        }

        function handleDownloadReportDetail() {
            if (!currentReportId) return;
            
            const report = reportsData.find(r => r.id === currentReportId);
            if (!report) return;

            // Create report content
            const reportContent = `Report Details\n` +
                `================\n\n` +
                `Description: ${report.description}\n` +
                `Date: ${new Date(report.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}\n` +
                `Type: ${report.type}\n` +
                `Amount: ${report.amount > 0 ? `₱${report.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : 'N/A'}\n` +
                `Status: ${report.status}\n\n` +
                `[Detailed report data would be included here]\n`;

            // Create and download file
            const blob = new Blob([reportContent], { type: 'text/plain;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            const filename = `report_${report.id}_${report.date.replace(/-/g, '')}.txt`;
            
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Close modal after download
            setTimeout(() => {
                closeReportDetailModal();
                showExportNotification(`Report "${report.description}" downloaded successfully!`);
            }, 300);
        }

        function downloadReport(reportId) {
            const report = reportsData.find(r => r.id === reportId);
            if (!report) return;

            // Same download logic as handleDownloadReportDetail
            const reportContent = `Report Details\n` +
                `================\n\n` +
                `Description: ${report.description}\n` +
                `Date: ${new Date(report.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}\n` +
                `Type: ${report.type}\n` +
                `Amount: ${report.amount > 0 ? `₱${report.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : 'N/A'}\n` +
                `Status: ${report.status}\n\n` +
                `[Detailed report data would be included here]\n`;

            const blob = new Blob([reportContent], { type: 'text/plain;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            const filename = `report_${report.id}_${report.date.replace(/-/g, '')}.txt`;
            
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showExportNotification(`Report "${report.description}" downloaded successfully!`);
        }

        function exportReport() {
            if (filteredReports.length === 0) {
                alert('No reports to export. Please adjust your filters.');
                return;
            }

            // Create CSV content
            let csvContent = 'Date,Report Type,Description,Amount,Status\n';
            
            filteredReports.forEach(report => {
                const formattedDate = new Date(report.date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                const typeLabels = {
                    'sales': 'Sales Report',
                    'inventory': 'Inventory Report',
                    'purchases': 'Purchases Report',
                    'products': 'Products Report'
                };
                
                const reportType = typeLabels[report.type] || report.type;
                const status = report.status === 'completed' ? 'Completed' : 'Pending';
                const amount = report.amount > 0 ? `₱${report.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '-';
                
                // Escape commas and quotes in description
                const description = report.description.replace(/"/g, '""');
                
                csvContent += `"${formattedDate}","${reportType}","${description}","${amount}","${status}"\n`;
            });

            // Create a blob and download
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            // Generate filename with date range
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const reportType = document.getElementById('reportType').value;
            
            let filename = 'reports_export';
            if (reportType !== 'all') {
                filename += '_' + reportType;
            }
            if (dateFrom && dateTo) {
                filename += '_' + dateFrom + '_to_' + dateTo;
            }
            filename += '_' + new Date().toISOString().split('T')[0] + '.csv';
            
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show success notification
            showExportNotification(`Exported ${filteredReports.length} report(s) successfully!`);
        }

        function showExportNotification(message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'export-notification';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            // Hide and remove notification after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Make functions globally accessible
        window.filterReports = filterReports;
        window.goToReportPage = goToReportPage;
        window.goToPreviousReportPage = goToPreviousReportPage;
        window.goToNextReportPage = goToNextReportPage;
        window.viewReport = viewReport;
        window.downloadReport = downloadReport;
        window.exportReport = exportReport;
        window.handleViewReportDetail = handleViewReportDetail;
        window.handleDownloadReportDetail = handleDownloadReportDetail;
        window.closeReportDetailModal = closeReportDetailModal;

        // Add event listeners for modal
        document.addEventListener('DOMContentLoaded', function() {
            const closeBtn = document.getElementById('closeReportDetailModal');
            const modal = document.getElementById('reportDetailModal');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', closeReportDetailModal);
            }
            
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeReportDetailModal();
                    }
                });
            }
        });
    </script>
</body>
</html>
