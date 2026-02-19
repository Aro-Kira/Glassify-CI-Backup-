<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<script>
  const baseUrl = "<?php echo base_url(); ?>";
  const getCalendarEventsUrl = "<?php echo base_url('AdminCon/get_calendar_events'); ?>";
  const getDayDetailsUrl = "<?php echo base_url('AdminCon/get_day_details'); ?>";
</script>
<!-- calendar.js loaded via layout page_js -->

<!-- Calendar Section -->
<section class="calendar-section">
  <div class="section-header">
    <h2>Calendar / Project Timeline <span class="found-text">Loading...</span></h2>
  </div>

  <!-- View Options -->
  <div class="view-options">
    <button class="view-btn active" data-view="monthly">Monthly</button>
    <button class="view-btn" data-view="weekly">Weekly</button>
    <button class="view-btn" data-view="daily">Daily</button>
    <button class="view-btn" data-view="timeline">Timeline</button>
  </div>

  <!-- Filters -->
  <div class="calendar-filters">
    <select id="order-type-filter" class="filter-select">
      <option value="all">All Order Types</option>
      <option value="direct">Direct Orders</option>
      <option value="site-assessed">Site-Assessed Orders</option>
    </select>
    <select id="status-filter" class="filter-select">
      <option value="all">All Statuses</option>
      <option value="Pending Review">Pending Review</option>
      <option value="Approved">Approved</option>
      <option value="Ocular Pending">Ocular Pending</option>
      <option value="Ocular Completed">Ocular Completed</option>
      <option value="In Fabrication">In Fabrication</option>
      <option value="Ready for Installation">Ready for Installation</option>
      <option value="Installed">Installation/Delivery</option>
      <option value="Completed">Completed</option>
      <option value="Cancelled">Cancelled</option>
    </select>
    <input type="date" id="date-start-filter" class="filter-input" placeholder="Start Date">
    <input type="date" id="date-end-filter" class="filter-input" placeholder="End Date">
    <input type="text" id="search-filter" class="filter-input" placeholder="Search by order number or client name">
    <button class="filter-btn" id="apply-calendar-filters">Apply</button>
    <button class="filter-btn" id="clear-calendar-filters" style="background: #999;">Clear</button>
  </div>

  <!-- Calendar Container -->
  <div class="calendar-container">
    <div id="calendar-view-monthly" class="calendar-view active">
      <!-- Monthly calendar will be rendered here -->
      <div id="monthly-calendar"></div>
    </div>
    
    <div id="calendar-view-weekly" class="calendar-view">
      <!-- Weekly calendar will be rendered here -->
      <div id="weekly-calendar"></div>
    </div>
    
    <div id="calendar-view-daily" class="calendar-view">
      <!-- Daily calendar will be rendered here -->
      <div id="daily-calendar"></div>
    </div>
    
    <div id="calendar-view-timeline" class="calendar-view">
      <!-- Timeline/Gantt chart will be rendered here -->
      <div id="timeline-calendar"></div>
    </div>
  </div>

  <!-- Day Details Sidebar -->
  <div class="day-details-sidebar" id="day-details-sidebar">
    <div class="sidebar-header">
      <h3 id="sidebar-date">Select a date</h3>
      <button class="close-sidebar" id="close-sidebar">&times;</button>
    </div>
    <div class="sidebar-content" id="sidebar-content">
      <!-- Day events will be loaded here -->
    </div>
  </div>
</section>
