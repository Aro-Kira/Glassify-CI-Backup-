<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<script>
  const baseUrl = "<?php echo base_url(); ?>";
  const getFabricationQueueUrl = "<?php echo base_url('AdminCon/get_fabrication_queue'); ?>";
</script>

<!-- Production / Fabrication Queue Section (production.js loaded via layout page_js) -->
<section class="production-section">
  <div class="section-header">
    <h2>Production / Fabrication Queue <span class="found-text">Loading...</span></h2>
  </div>

  <!-- View Toggle -->
  <div class="view-toggle">
    <button class="toggle-btn active" data-view="kanban">Taskboard</button>
    <button class="toggle-btn" data-view="list">List View</button>
  </div>

  <!-- Filters -->
  <div class="filters-container">
    <div class="filter-group">
      <label for="status-filter">Status:</label>
      <select id="status-filter" class="filter-select">
        <option value="all">All</option>
        <option value="queued">Queued</option>
        <option value="in-progress">In Progress</option>
        <option value="quality-check">Quality Check</option>
        <option value="ready">Ready</option>
        <option value="completed">Completed</option>
      </select>
    </div>
    <div class="filter-group">
      <label for="order-type-filter">Order Type:</label>
      <select id="order-type-filter" class="filter-select">
        <option value="all">All</option>
        <option value="direct">Direct</option>
        <option value="site-assessed">Site-Assessed</option>
      </select>
    </div>
    <div class="filter-group">
      <label for="staff-filter">Assigned Staff:</label>
      <select id="staff-filter" class="filter-select">
        <option value="all">All Staff</option>
        <!-- Options will be loaded via AJAX -->
      </select>
    </div>
    <div class="filter-group">
      <label for="date-start-filter">Start Date:</label>
      <input type="date" id="date-start-filter" class="filter-input">
    </div>
    <div class="filter-group">
      <label for="date-end-filter">End Date:</label>
      <input type="date" id="date-end-filter" class="filter-input">
    </div>
    <div class="filter-group">
      <label for="search-filter">Search:</label>
      <input type="text" id="search-filter" class="filter-input" placeholder="Order number, client name">
    </div>
    <button class="filter-btn" id="apply-filters">Apply Filters</button>
  </div>

  <!-- Kanban Board View -->
  <div id="kanban-view" class="kanban-container">
    <div class="kanban-column" data-status="queued">
      <div class="column-header">
        <h3>Queued</h3>
        <span class="column-count">0</span>
      </div>
      <div class="kanban-cards" id="queued-cards">
        <!-- Cards will be loaded here -->
      </div>
    </div>
    
    <div class="kanban-column" data-status="in-progress">
      <div class="column-header">
        <h3>In Progress</h3>
        <span class="column-count">0</span>
      </div>
      <div class="kanban-cards" id="in-progress-cards">
        <!-- Cards will be loaded here -->
      </div>
    </div>
    
    <div class="kanban-column" data-status="quality-check">
      <div class="column-header">
        <h3>Quality Check</h3>
        <span class="column-count">0</span>
      </div>
      <div class="kanban-cards" id="quality-check-cards">
        <!-- Cards will be loaded here -->
      </div>
    </div>
    
    <div class="kanban-column" data-status="ready">
      <div class="column-header">
        <h3>Ready</h3>
        <span class="column-count">0</span>
      </div>
      <div class="kanban-cards" id="ready-cards">
        <!-- Cards will be loaded here -->
      </div>
    </div>
    
    <div class="kanban-column" data-status="completed">
      <div class="column-header">
        <h3>Completed</h3>
        <span class="column-count">0</span>
      </div>
      <div class="kanban-cards" id="completed-cards">
        <!-- Cards will be loaded here -->
      </div>
    </div>
  </div>

  <!-- List View -->
  <div id="list-view" class="list-container" style="display: none;">
    <table class="production-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Order ID</th>
          <th>Client</th>
          <th>Product</th>
          <th>Quantity</th>
          <th>Order Type</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Assigned Staff</th>
          <th>Progress</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="production-table-body">
        <!-- Rows will be loaded here -->
      </tbody>
    </table>
  </div>

  <!-- Order Details Modal -->
  <div class="order-details-modal" id="order-details-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Order Details</h3>
        <button class="close-modal">&times;</button>
      </div>
      <div class="modal-body">
        <!-- Content will be loaded dynamically -->
      </div>
    </div>
  </div>
</section>
