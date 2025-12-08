<section class="order-list-section">
  <div class="section-header">
    <h2>Reports</h2>

  </div>

  <div class="box">

    <div class="dash-tabs">
      <h2>Sales Reports</h2>
    </div>
    <div class="inventory-stats">
      <div class="stat-card">
        <p class="stat-value">₱<?php echo isset($report_stats['total_sales']) ? number_format($report_stats['total_sales'], 2) : '0.00'; ?></p>
        <p class="stat-title">Total Sales</p>
      </div>
      <div class="stat-card">
        <p class="stat-value"><?php echo isset($report_stats['total_orders']) ? number_format($report_stats['total_orders']) : '0'; ?></p>
        <p class="stat-title">Orders</p>
      </div>
      <div class="stat-card">
        <p class="stat-value">₱<?php echo isset($report_stats['avg_order_value']) ? number_format($report_stats['avg_order_value'], 2) : '0.00'; ?></p>
        <p class="stat-title">Avg. Order Value</p>
      </div>
      <div class="stat-card">
        <p class="stat-value"><?php echo isset($report_stats['refunds']) ? number_format($report_stats['refunds']) : '0'; ?></p>
        <p class="stat-title">Refunds</p>
      </div>
    </div>

  </div>
</section>



</main>
</div>

</body>

</html>