<!-- Orders -->
<section class="order-list-section">
  <div class="section-header">
    <h2>Inventory</h2>

    <div class="inventory-stats">
      <div class="stat-card">
        <p class="stat-title">Total Items</p>
        <p class="stat-value">6</p>
      </div>
      <div class="stat-card">
        <p class="stat-title">Low Stocks Alerts</p>
        <p class="stat-value">1</p>
      </div>
      <div class="stat-card">
        <p class="stat-title">New Items</p>
        <p class="stat-value">1</p>
      </div>
      <div class="stat-card">
        <p class="stat-title">Recent Requests</p>
        <p class="stat-value">4</p>
      </div>
    </div>
  </div>

  <div class="order-tabs">
    <h2>List of Items</h2>
  </div>
  <div class="controls-container">
    <div class="search-bar">
      <input type="text" placeholder="Filter by name or category..." class="search-input">
      <button class="search-button">Search</button>
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
        <!-- Rows will be injected here by JS -->
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="pagination">
    <span id="paginationInfo">Showing 1-10 of 6 items</span>
    <div class="rows-per-page">
      <label for="rowsPerPageSelect">Rows per page:</label>
      <select id="rowsPerPageSelect">
        <option value="5" selected>5</option>
        <option value="10">10</option>
        <option value="25">25</option>
      </select>
    </div>
    <div class="pagination-controls">
      <button><i class="fas fa-chevron-left"></i></button>
      <button class="active">1</button>
      <button><i class="fas fa-chevron-right"></i></button>
    </div>
  </div>
</section>

<!-- Scripts -->
<script src="/Glassify/assets/js/order-status.js"></script>
<script src="/Glassify/assets/js/order-action.js"></script>
</main>
</div>

<script src="/Glassify/assets/js/order-popup.js"></script>
<script src="/Glassify/assets/js/search-inventory.js"></script>
<script src="/Glassify/assets/js/inventory-action.js"></script>
<script src="/Glassify/assets/js/inventory-filter.js"></script>