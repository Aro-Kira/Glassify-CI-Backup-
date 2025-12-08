
  
    <section class="order-list-section">
      <div class="section-header">
        <h2>Dashboard</h2>
      </div>

      <div class="box">

        <div class="dash-tabs">
          <h2>Key Stats</h2>
        </div>
        <!-- Key Stats -->
        <section class="key-stats">
          <div class="stat-card stat-blue">
            <div class="stat-value"><?php echo isset($stats['total_orders_month']) ? number_format($stats['total_orders_month']) : '0'; ?></div>
            <div class="stat-title">Total Order</div>
          </div>
          <div class="stat-card stat-orange">
            <div class="stat-value"><?php echo isset($stats['pending_orders']) ? number_format($stats['pending_orders']) : '0'; ?></div>
            <div class="stat-title">Pending Orders</div>
          </div>
          <div class="stat-card stat-green">
            <div class="stat-value">₱<?php echo isset($stats['weekly_sales']) ? number_format($stats['weekly_sales'], 2) : '0.00'; ?></div>
            <div class="stat-title">Weekly Sales</div>
            <?php if (isset($stats['debug_week_start']) && isset($stats['debug_week_end'])): ?>
              <div style="font-size: 12px; margin-top: 5px; opacity: 0.8;">
                Week: <?php echo $stats['debug_week_start']; ?> to <?php echo $stats['debug_week_end']; ?>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <!-- INVENTORY -->
        <section class="inventory-section-full">
          <div class="dash-tabs">
            <h2>Inventory</h2>
          </div>
          <div class="inventory-box">
            <table>
              <thead>
                <tr>
                  <th>Items</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>6mm Glass Sheets</td>
                  <td><span class="status-badge out">Out Of Stock</span></td>
                </tr>
                <tr>
                  <td>Aluminum Brackets</td>
                  <td><span class="status-badge low">Low Stock</span></td>
                </tr>
                <tr>
                  <td>Rubber Gasket</td>
                  <td><span class="status-badge in">In Stock</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>


        <div class="dash-tabs">
          <h2>Today's Appointment</h2>
        </div>
        <!-- Today's Appointments -->
        <section class="appointments">
          <table>
            <thead>
              <tr>
                <th>Time</th>
                <th>Type</th>
                <th>Client</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>9:00 AM</td>
                <td>Ocular Visit</td>
                <td>Client A</td>
                <td><span class="status confirmed">Confirmed</span></td>
              </tr>
              <tr>
                <td>11:30 AM</td>
                <td>Measurement</td>
                <td>Client B</td>
                <td><span class="status pending">Pending</span></td>
              </tr>
              <tr>
                <td>3:00 PM</td>
                <td>Consultation</td>
                <td>Client C</td>
                <td><span class="status canceled">Canceled</span></td>
              </tr>
            </tbody>
          </table>
        </section>
        <div class="dash-tabs">
          <h2>Recent Activities</h2>
        </div>
        <!-- Recent Activities -->
        <section class="recent-activities">
          <table>
            <thead>
              <tr>
                <th>Action</th>
                <th>Description</th>
                <th>Role</th>
                <th>User</th>
                <th>Timestamp</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="badge info">Info</span></td>
                <td>New order created (Order #1024)</td>
                <td>Client</td>
                <td>Client A</td>
                <td>5/28/2025 – 09:45 AM</td>
              </tr>
              <tr>
                <td><span class="badge success">Success</span></td>
                <td>Quotation sent to Client B</td>
                <td>Staff</td>
                <td>M. Lopez</td>
                <td>5/28/2025 – 08:30 AM</td>
              </tr>
              <tr>
                <td><span class="badge error">Error</span></td>
                <td>Inventory update failed (Glass Panel)</td>
                <td>Admin</td>
                <td>L. Doria</td>
                <td>5/27/2025 – 05:12 PM</td>
              </tr>
              <tr>
                <td><span class="badge warning">Warning</span></td>
                <td>Stock running low: Aluminum Brackets</td>
                <td>System</td>
                <td>System</td>
                <td>5/27/2025 – 02:15 PM</td>
              </tr>
            </tbody>
          </table>
        </section>

      </div>