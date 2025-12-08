<!-- Payments -->
<section class="order-list-section">
  <div class="section-header">
    <h2>Payments</h2>

    <div class="inventory-stats">
      <div class="stat-card stat-green">
        <div class="stat-value">₱67,704</div>
        <div class="stat-title">Weekly Sales</div>
        <div class="stat-percent">↑ 18% from last week</div>
      </div>

      <div class="stat-card stat-orange">
        <div class="stat-value">3</div>
        <div class="stat-title">Pending Payments</div>
      </div>

      <div class="stat-card stat-red">
        <div class="stat-value">1</div>
        <div class="stat-title">Overdue Payments</div>
      </div>
    </div>

  </div>

  <div class="order-tabs">
    <h2>Payment Tables</h2>
  </div>

  <div class="payment-filters">
    <span class="filter-tab active">All</span>
    <span class="filter-tab">Paid</span>
    <span class="filter-tab">Pending</span>
    <span class="filter-tab">Under Review</span>
    <span class="filter-tab">Overdue</span>
  </div>


  <div class="table-container">
    <table class="payment-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Method</th>
          <th>Status</th>
          <th>Receipt</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>#G1001</td>
          <td>Calista Flockhart</td>
          <td><span class="method-gcash">Gcash</span></td>
          <td><span class="status-badge pending">Pending</span></td>
          <td><button class="receipt-btn"><i class="fas fa-camera camera-icon"></i></button></td>
          <td>03/23/2025</td>
          <td class="action-cell">⋮</td>
        </tr>
        <tr>
          <td>2</td>
          <td>#G1002</td>
          <td>Jeremy Tan</td>
          <td>Cash</td>
          <td><span class="status-badge overdue">Overdue</span></td>
          <td><button class="receipt-btn"><i class="fas fa-camera camera-icon"></i></button></td>
          <td>03/23/2025</td>
          <td class="action-cell">⋮</td>
        </tr>
        <tr>
          <td>3</td>
          <td>#G1003</td>
          <td>David Discaya</td>
          <td><span class="method-gcash">Gcash</span></td>
          <td><span class="status-badge pending">Pending</span></td>
          <td><button class="receipt-btn"><i class="fas fa-camera camera-icon"></i></button></td>
          <td>03/23/2025</td>
          <td class="action-cell">⋮</td>
        </tr>
        <tr>
          <td>4</td>
          <td>#G1004</td>
          <td>Harold Sy</td>
          <td><span class="method-gcash">Gcash</span></td>
          <td><span class="status-badge review">Under Review</span></td>
          <td><button class="receipt-btn"><i class="fas fa-camera camera-icon"></i></button></td>
          <td>03/23/2025</td>
          <td class="action-cell">⋮</td>
        </tr>
        <tr>
          <td>5</td>
          <td>#G1005</td>
          <td>Krishanne Gravidez</td>
          <td><span class="method-gcash">Gcash</span></td>
          <td><span class="status-badge paid">Paid</span></td>
          <td><button class="receipt-btn"><i class="fas fa-camera camera-icon"></i></button></td>
          <td>03/23/2025</td>
          <td class="action-cell">⋮</td>
        </tr>
        <tr>
          <td>6</td>
          <td>#G1006</td>
          <td>Julianne Copiaza</td>
          <td>Cash</td>
          <td><span class="status-badge review">Under Review</span></td>
          <td><button class="receipt-btn"><i class="fas fa-camera camera-icon"></i></button></td>
          <td>03/23/2025</td>
          <td class="action-cell">⋮</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="pagination">
    <span>Showing 1-10 of 255 items</span>
    <div class="pagination-controls">
      <button><i class="fas fa-chevron-left"></i></button>
      <button class="active">1</button>
      <button><i class="fas fa-chevron-right"></i></button>
    </div>

    <div id="actionMenu" class="action-menu hidden">
      <ul>
        <li><a href="#">View Receipt</a></li>
        <li><a href="#">Mark as Paid</a></li>
        <li><a href="#">Cancel</a></li>
      </ul>
    </div>

    <!-- Popup Overlay -->
    <div class="popup-overlay" id="productPopup">
      <div class="popup">
        <span class="close-btn" id="closePopup">&times;</span>
        <h3>Order ID: <span id="popupOrderId">#</span></h3>

        <!-- Image Preview -->
        <div class="form-group">
          <div class="image-preview">
            <img src="https://cdn-icons-png.flaticon.com/512/4211/4211763.png" alt="Preview">
          </div>
        </div>

        <!-- Form Fields -->
        <div class="form-group">
          <label>Customer: <span id="popupCustomer"></span></label>
        </div>

        <div class="form-group">
          <label>Price:</label>
          <div class="price-input">
            <span>₱</span>
            <input type="number" id="popupPrice" class="input-text" readonly>
          </div>
        </div>

        <div class="form-group method-field">
          <label>Method:</label>
        </div>


        <!-- Action Buttons -->
        <div class="popup-actions">
          <button class="save-btn">Mark as Paid</button>
          <button class="cancel-btn">Cancel</button>
        </div>
      </div>
    </div>



</section>
<script src="/Glassify/assets/js/order-status.js"></script>


<script src="/Glassify/assets/js/payments-action.js"></script>
<script src="/Glassify/assets/js/payment-filter.js"></script>
<script src="/Glassify/assets/js/view-receipt-payments.js"></script>