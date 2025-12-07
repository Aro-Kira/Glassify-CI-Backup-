<!-- Reports Section -->
<section class="order-list-section">
  <div class="section-header">
    <h2>Inventory Reports</h2>
    <p>View and analyze inventory data and statistics</p>
  </div>

  <!-- Report Filters -->
  <div class="controls-container">
    <div class="filters">
      <label class="filter-label">Report Type:</label>
      <select id="reportType" class="filter-select">
        <option value="stock">Stock Status</option>
        <option value="transactions">Stock Transactions</option>
        <option value="activities">Activity Log</option>
        <option value="low-stock">Low Stock Items</option>
        <option value="out-of-stock">Out of Stock Items</option>
      </select>

      <label class="filter-label">Date Range:</label>
      <input type="date" id="startDate" class="filter-input">
      <span>to</span>
      <input type="date" id="endDate" class="filter-input">

      <button class="search-button" id="generateReport">Generate Report</button>
    </div>
  </div>

  <!-- Report Content -->
  <div class="report-content">
    <!-- Stock Status Report -->
    <div id="stockReport" class="report-section">
      <div class="order-tabs">
        <h2>Stock Status Report</h2>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Item ID</th>
              <th>Item Name</th>
              <th>Category</th>
              <th>Current Stock</th>
              <th>Min Threshold</th>
              <th>Status</th>
              <th>Unit</th>
            </tr>
          </thead>
          <tbody id="stockReportBody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 20px;">Click "Generate Report" to view stock status</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Stock Transactions Report -->
    <div id="transactionsReport" class="report-section" style="display: none;">
      <div class="order-tabs">
        <h2>Stock Transactions Report</h2>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Item</th>
              <th>Transaction Type</th>
              <th>Quantity</th>
              <th>Previous Stock</th>
              <th>New Stock</th>
              <th>Reason</th>
            </tr>
          </thead>
          <tbody id="transactionsReportBody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 20px;">Click "Generate Report" to view transactions</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Activity Log Report -->
    <div id="activitiesReport" class="report-section" style="display: none;">
      <div class="order-tabs">
        <h2>Activity Log Report</h2>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Timestamp</th>
              <th>Action</th>
              <th>Item</th>
              <th>Change Description</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody id="activitiesReportBody">
            <tr>
              <td colspan="5" style="text-align: center; padding: 20px;">Click "Generate Report" to view activities</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Low Stock Report -->
    <div id="lowStockReport" class="report-section" style="display: none;">
      <div class="order-tabs">
        <h2>Low Stock Items Report</h2>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Item ID</th>
              <th>Item Name</th>
              <th>Category</th>
              <th>Current Stock</th>
              <th>Min Threshold</th>
              <th>Difference</th>
              <th>Unit</th>
            </tr>
          </thead>
          <tbody id="lowStockReportBody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 20px;">Click "Generate Report" to view low stock items</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Out of Stock Report -->
    <div id="outOfStockReport" class="report-section" style="display: none;">
      <div class="order-tabs">
        <h2>Out of Stock Items Report</h2>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Item ID</th>
              <th>Item Name</th>
              <th>Category</th>
              <th>Last Stock</th>
              <th>Min Threshold</th>
              <th>Unit</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="outOfStockReportBody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 20px;">Click "Generate Report" to view out of stock items</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<script>
(function() {
    'use strict';
    
    const baseUrl = '<?php echo base_url(); ?>';
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Format date
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: '2-digit', 
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Load stock status report
    function loadStockReport() {
        fetch(baseUrl + 'api/inventory/get_items')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('stockReportBody');
                if (!tbody) return;
                
                if (data.success && data.data && data.data.length > 0) {
                    tbody.innerHTML = '';
                    
                    data.data.forEach(item => {
                        const stock = item.InStock || item.stock_quantity || 0;
                        const minThreshold = item.min_threshold || 10;
                        let status = 'In Stock';
                        let statusClass = 'status-in-stock';
                        
                        if (stock === 0) {
                            status = 'Out of Stock';
                            statusClass = 'status-out-stock';
                        } else if (stock < minThreshold) {
                            status = 'Low Stock';
                            statusClass = 'status-low-stock';
                        }
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${escapeHtml(item.ItemCode || item.item_code || 'N/A')}</td>
                            <td>${escapeHtml(item.ItemName || item.name || 'N/A')}</td>
                            <td>${escapeHtml(item.Category || item.category || 'N/A')}</td>
                            <td>${stock}</td>
                            <td>${minThreshold}</td>
                            <td><span class="${statusClass}">${status}</span></td>
                            <td>${escapeHtml(item.Unit || item.unit || 'N/A')}</td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No items found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading stock report:', error);
                const tbody = document.getElementById('stockReportBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #f44336;">Error loading report</td></tr>';
                }
            });
    }
    
    // Load activities report
    function loadActivitiesReport() {
        fetch(baseUrl + 'api/inventory/get_activities')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('activitiesReportBody');
                if (!tbody) return;
                
                if (data.success && data.data && data.data.length > 0) {
                    tbody.innerHTML = '';
                    
                    data.data.forEach(activity => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${formatDate(activity.timestamp)}</td>
                            <td>${escapeHtml(activity.action || '')}</td>
                            <td>${escapeHtml(activity.item_name || '')}</td>
                            <td>${escapeHtml(activity.change_description || '')}</td>
                            <td>${escapeHtml(activity.description || '')}</td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No activities found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading activities report:', error);
            });
    }
    
    // Load low stock report
    function loadLowStockReport() {
        fetch(baseUrl + 'api/inventory/get_items')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('lowStockReportBody');
                if (!tbody) return;
                
                if (data.success && data.data && data.data.length > 0) {
                    const lowStockItems = data.data.filter(item => {
                        const stock = item.InStock || item.stock_quantity || 0;
                        const minThreshold = item.min_threshold || 10;
                        return stock > 0 && stock < minThreshold;
                    });
                    
                    tbody.innerHTML = '';
                    
                    if (lowStockItems.length > 0) {
                        lowStockItems.forEach(item => {
                            const stock = item.InStock || item.stock_quantity || 0;
                            const minThreshold = item.min_threshold || 10;
                            const difference = minThreshold - stock;
                            
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${escapeHtml(item.ItemCode || item.item_code || 'N/A')}</td>
                                <td>${escapeHtml(item.ItemName || item.name || 'N/A')}</td>
                                <td>${escapeHtml(item.Category || item.category || 'N/A')}</td>
                                <td>${stock}</td>
                                <td>${minThreshold}</td>
                                <td><strong style="color: #f44336;">-${difference}</strong></td>
                                <td>${escapeHtml(item.Unit || item.unit || 'N/A')}</td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No low stock items found</td></tr>';
                    }
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No items found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading low stock report:', error);
            });
    }
    
    // Load out of stock report
    function loadOutOfStockReport() {
        fetch(baseUrl + 'api/inventory/get_items')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('outOfStockReportBody');
                if (!tbody) return;
                
                if (data.success && data.data && data.data.length > 0) {
                    const outOfStockItems = data.data.filter(item => {
                        const stock = item.InStock || item.stock_quantity || 0;
                        return stock === 0;
                    });
                    
                    tbody.innerHTML = '';
                    
                    if (outOfStockItems.length > 0) {
                        outOfStockItems.forEach(item => {
                            const minThreshold = item.min_threshold || 10;
                            
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${escapeHtml(item.ItemCode || item.item_code || 'N/A')}</td>
                                <td>${escapeHtml(item.ItemName || item.name || 'N/A')}</td>
                                <td>${escapeHtml(item.Category || item.category || 'N/A')}</td>
                                <td>0</td>
                                <td>${minThreshold}</td>
                                <td>${escapeHtml(item.Unit || item.unit || 'N/A')}</td>
                                <td><button class="btn-small" onclick="alert('Restock needed for ' + '${escapeHtml(item.ItemName || item.name)}')">Restock</button></td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No out of stock items found</td></tr>';
                    }
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No items found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading out of stock report:', error);
            });
    }
    
    // Show selected report
    function showReport(reportType) {
        // Hide all reports
        document.querySelectorAll('.report-section').forEach(section => {
            section.style.display = 'none';
        });
        
        // Show selected report
        const reportSection = document.getElementById(reportType + 'Report');
        if (reportSection) {
            reportSection.style.display = 'block';
        }
        
        // Load data based on report type
        switch(reportType) {
            case 'stock':
                loadStockReport();
                break;
            case 'activities':
                loadActivitiesReport();
                break;
            case 'low-stock':
                loadLowStockReport();
                break;
            case 'out-of-stock':
                loadOutOfStockReport();
                break;
            case 'transactions':
                // TODO: Implement transactions report when API is ready
                document.getElementById('transactionsReportBody').innerHTML = 
                    '<tr><td colspan="7" style="text-align: center; padding: 20px;">Transactions report coming soon</td></tr>';
                break;
        }
    }
    
    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Load default report
        showReport('stock');
        
        // Report type change
        document.getElementById('reportType').addEventListener('change', function() {
            const reportType = this.value;
            showReport(reportType);
        });
        
        // Generate report button
        document.getElementById('generateReport').addEventListener('click', function() {
            const reportType = document.getElementById('reportType').value;
            showReport(reportType);
        });
    });
})();
</script>

<style>
.report-section {
    margin-top: 20px;
}

.status-in-stock {
    color: #4caf50;
    font-weight: bold;
}

.status-low-stock {
    color: #ff9800;
    font-weight: bold;
}

.status-out-stock {
    color: #f44336;
    font-weight: bold;
}

.btn-small {
    padding: 5px 10px;
    background: #02455F;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.btn-small:hover {
    background: #036a8f;
}

.filter-input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin: 0 5px;
}

.search-button {
    padding: 8px 20px;
    background: #02455F;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-left: 10px;
}

.search-button:hover {
    background: #036a8f;
}
</style>


