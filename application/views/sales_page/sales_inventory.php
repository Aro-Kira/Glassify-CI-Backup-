      <!-- Inventory -->
      <section class="order-list-section">
        <div class="section-header">
          <h2>Inventory</h2>
          
          <?php if (!empty($notifications) && $notification_count > 0): ?>
            <div class="inventory-alerts" style="background-color: #FFE6E6; border-left: 4px solid #DC143C; padding: 15px; margin: 15px 0; border-radius: 5px;">
              <h3 style="color: #DC143C; margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">
                <i class="fas fa-exclamation-triangle"></i> Out of Stock Alerts (<?php echo $notification_count; ?>)
              </h3>
              <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($notifications as $notif): ?>
                  <li style="margin: 5px 0; color: #333;">
                    <strong><?php echo htmlspecialchars($notif->ItemID); ?></strong> - 
                    <?php echo htmlspecialchars($notif->ItemName); ?>: 
                    <?php echo htmlspecialchars($notif->Message); ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="inventory-stats">
            <div class="stat-card">
              <p class="stat-title">Total Items</p>
              <p class="stat-value"><?php echo $total_items; ?></p>
            </div>
            <div class="stat-card">
              <p class="stat-title">Low Stocks Alerts</p>
              <p class="stat-value"><?php echo $low_stock_count; ?></p>
            </div>
            <div class="stat-card">
              <p class="stat-title">New Items</p>
              <p class="stat-value"><?php echo $new_items_count; ?></p>
            </div>
            <div class="stat-card">
              <p class="stat-title">Out of Stock</p>
              <p class="stat-value"><?php echo $out_of_stock_count; ?></p>
            </div>
          </div>
        </div>

        <div class="order-tabs">
          <h2>List of Items</h2>
        </div>
        <div class="controls-container">
          <div class="search-bar">
            <input type="text" placeholder="Filter by name or category..." class="search-input" id="inventorySearch">
          </div>
        </div>

        <!-- Table -->
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Item ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>In Stock</th>
                <th>Unit</th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <?php if (!empty($inventory_items)): ?>
                <?php $counter = 1; ?>
                <?php foreach ($inventory_items as $item): ?>
                  <?php
                    // Check if item is new (added within last 2 days)
                    $date_added = strtotime($item->DateAdded);
                    $two_days_ago = strtotime('-2 days');
                    $is_new = $date_added >= $two_days_ago;
                    
                    // Determine stock status - use actual InStock value from database
                    $stock_number = (int)$item->InStock; // Get stock directly from DB, never modify
                    $is_out_of_stock = $stock_number == 0;
                    $is_low_stock = $stock_number > 0 && $stock_number <= 10;
                  ?>
                  <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($item->ItemID); ?></td>
                    <td>
                      <?php echo htmlspecialchars($item->Name); ?>
                      <?php if ($is_new || $item->Status == 'New'): ?>
                        <span class="status-badge status-new">New</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item->Category); ?></td>
                    <td>
                      <?php echo number_format($stock_number, 0); ?>
                      <?php if ($is_out_of_stock): ?>
                        <span class="status-badge status-out-of-stock">Out of Stock</span>
                      <?php elseif ($is_low_stock): ?>
                        <span class="status-badge status-low-stock">Low Stock</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item->Unit); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" style="text-align: center; padding: 20px;">No inventory items found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <span id="paginationInfo">Showing 1-<?php echo count($inventory_items); ?> of <?php echo $total_items; ?> items</span>
          <div class="rows-per-page">
            <label for="rowsPerPageSelect">Rows per page:</label>
            <select id="rowsPerPageSelect">
              <option value="5" selected>5</option>
              <option value="10">10</option>
              <option value="25">25</option>
            </select>
          </div>
          <div class="pagination-controls">
            <button id="prevPage"><i class="fas fa-chevron-left"></i></button>
            <button class="active" id="page1">1</button>
            <button id="nextPage"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
      </section>

    </main>
  </div>
  
  <script>
    // Pass inventory data to JavaScript
    const inventoryData = <?php echo json_encode($inventory_items); ?>;
  </script>
  <script src="<?php echo base_url(); ?>assets/js/inventory-js/inventory-new-filter.js"></script>
