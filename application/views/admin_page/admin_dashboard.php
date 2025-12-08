
  
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
          </div>
        </section>

        <!-- INVENTORY & PROJECT PROGRESS -->
        <section class="inventory-progress">

          <!-- Inventory -->
          <div class="inventory-section">
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
          </div>

          <!-- Project Progress -->
          <div class="progress-section">
            <div class="dash-tabs">
              <h2>Project Progress</h2>
            </div>
            <div class="progress-box">
              <div class="progress-item">
                <span>Store Front A</span>
                <div class="progress-bar">
                  <div style="width:75%"></div>
                </div>
                <span class="percent">75%</span>
              </div>
              <div class="progress-item">
                <span>Residential Glasswork</span>
                <div class="progress-bar">
                  <div style="width:90%"></div>
                </div>
                <span class="percent">90%</span>
              </div>
              <div class="progress-item">
                <span>Office Partitioning</span>
                <div class="progress-bar">
                  <div style="width:45%"></div>
                </div>
                <span class="percent">45%</span>
              </div>
              <div class="progress-item">
                <span>SM North Facade</span>
                <div class="progress-bar">
                  <div style="width:30%"></div>
                </div>
                <span class="percent">30%</span>
              </div>
            </div>
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
              <?php if (!empty($today_appointments)): ?>
                <?php foreach ($today_appointments as $appointment): ?>
                  <?php
                  // Format time
                  $time = 'N/A';
                  if (!empty($appointment->AppointmentTime)) {
                      $time_obj = new DateTime($appointment->AppointmentTime);
                      $time = $time_obj->format('g:i A');
                  }
                  
                  // Map status to CSS class
                  $status_class = 'pending';
                  $status_text = 'Pending';
                  if ($appointment->Status === 'Complete') {
                      $status_class = 'confirmed';
                      $status_text = 'Confirmed';
                  } elseif ($appointment->Status === 'Cancelled') {
                      $status_class = 'canceled';
                      $status_text = 'Canceled';
                  } elseif ($appointment->Status === 'In Progress') {
                      $status_class = 'pending';
                      $status_text = 'In Progress';
                  }
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($time); ?></td>
                    <td><?php echo htmlspecialchars($appointment->Service ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($appointment->ClientName ?? 'N/A'); ?></td>
                    <td><span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" style="text-align: center; padding: 20px;">No appointments scheduled for today</td>
                </tr>
              <?php endif; ?>
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
              <?php if (!empty($recent_activities)): ?>
                <?php foreach ($recent_activities as $activity): ?>
                  <?php
                  // Map Action to badge class
                  $badge_class = 'info';
                  $badge_text = 'Info';
                  $action_lower = strtolower($activity->Action ?? '');
                  
                  if (strpos($action_lower, 'success') !== false || strpos($action_lower, 'approved') !== false || strpos($action_lower, 'completed') !== false) {
                      $badge_class = 'success';
                      $badge_text = 'Success';
                  } elseif (strpos($action_lower, 'error') !== false || strpos($action_lower, 'failed') !== false || strpos($action_lower, 'disapproved') !== false) {
                      $badge_class = 'error';
                      $badge_text = 'Error';
                  } elseif (strpos($action_lower, 'warning') !== false || strpos($action_lower, 'low') !== false || strpos($action_lower, 'pending') !== false) {
                      $badge_class = 'warning';
                      $badge_text = 'Warning';
                  } else {
                      $badge_class = 'info';
                      $badge_text = 'Info';
                  }
                  
                  // Format timestamp
                  $timestamp = 'N/A';
                  if (!empty($activity->Timestamp)) {
                      $date_obj = new DateTime($activity->Timestamp);
                      $timestamp = $date_obj->format('m/d/Y – g:i A');
                  }
                  
                  // Get user name
                  $user_name = $activity->UserName ?? 'System';
                  if (empty($user_name)) {
                      $user_name = 'System';
                  }
                  ?>
                  <tr>
                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span></td>
                    <td><?php echo htmlspecialchars($activity->Description ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($activity->Role ?? 'System'); ?></td>
                    <td><?php echo htmlspecialchars($user_name); ?></td>
                    <td><?php echo $timestamp; ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" style="text-align: center; padding: 20px;">No recent activities</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </section>

      </div>