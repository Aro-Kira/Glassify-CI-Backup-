<section class="order-list-section">
  <div class="section-header">
    <h2>Dashboard</h2>
  </div>

  <div class="box">

        <div class="dash-tabs">
          <h2>My Tasks</h2>
        </div>
    <!-- Key Stats -->
    <section class="key-stats">
      <div class="stat-card stat-blue">
        <div class="stat-value"><?= isset($new_items_count) ? $new_items_count : 0; ?></div>
        <div class="stat-title">New Items</div>
        <button class="review-btn" onclick="window.location.href='<?= base_url('InventCon/inventory_inventory'); ?>'">View Items</button>
      </div>
      <div class="stat-card stat-orange">
        <div class="stat-value"><?= isset($low_stock_count) ? $low_stock_count : 0; ?></div>
        <div class="stat-title">Low Stock Alert</div>
        <button class="review-btn" onclick="window.location.href='<?= base_url('InventCon/inventory_inventory'); ?>'">Review</button>
      </div>
      <div class="stat-card stat-green">
        <div class="stat-value"><?= isset($total_products) ? $total_products : 0; ?></div>
        <div class="stat-title">Total Products</div>
        <button class="review-btn" onclick="window.location.href='<?= base_url('InventCon/inventory_products'); ?>'">View Product</button>
      </div>
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
            <th>Item</th>
            <th>Change</th>
            <th>Description</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recent_activities)): ?>
            <?php foreach ($recent_activities as $activity): ?>
              <tr>
                <td><?= htmlspecialchars($activity->action ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($activity->item_name ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($activity->change_description ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($activity->description ?? 'N/A'); ?></td>
                <td>
                  <?php 
                    if (isset($activity->formatted_timestamp)) {
                      echo htmlspecialchars($activity->formatted_timestamp);
                    } elseif (isset($activity->timestamp)) {
                      $timestamp = strtotime($activity->timestamp);
                      echo date('m/d/Y – h:i A', $timestamp);
                    } else {
                      echo 'N/A';
                    }
                  ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align: center; padding: 20px; color: #999;">No recent activities</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

  </div>
</section>

<script src="/Glassify/assets/js/dashboard-chart.js"></script>


