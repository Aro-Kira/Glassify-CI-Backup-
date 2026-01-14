<section class="user-section-main">
    <h1 class="page-title">Issue / Support</h1>
    <p class="issue-summary" id="issueSummary">Loading...</p>

    <div class="controls-container">
        <div class="search-bar">
            <input type="text" placeholder="Filter by name or category..." class="search-input">
            <button class="search-button">Search</button>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Ticket ID</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- JS will populate rows here -->
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <span id="paginationText">Loading...</span>
        <div class="pagination-controls" id="paginationControls">
            <!-- Pagination will be generated dynamically by JavaScript -->
        </div>
    </div>
</section>
</main>
</div>

<!-- Right Side Panel for Issue Details -->
<div class="popup-overlay" id="popupOverlay">
    <div class="popup ticket-popup">
        <div class="popup-header">
            <h2>Customer Contact Info</h2>
            <span class="close-btn" id="closePopupTicket">&times;</span>
        </div>

        <div class="customer-contact-info view-details">
            <div class="detail-row">
                <label>Name:</label>
                <span class="contact-name"></span>
            </div>
            <div class="detail-row">
                <label>Email:</label>
                <span class="contact-email"></span>
            </div>
            <div class="detail-row">
                <label>Phone:</label>
                <span class="contact-phone"></span>
            </div>
            <div class="detail-row">
                <label>Order ID:</label>
                <span class="contact-order-id"></span>
            </div>
        </div>

        <div class="issue-section">
            <h3 class="issue-section-title">Issue Category</h3>
            <div class="category-field">
                <input type="text" readonly class="category-input" id="issueCategory">
            </div>
        </div>

        <div class="priority-section">
            <h3 class="issue-section-title">Priority:</h3>
            <div class="priority-tag" id="priorityTag">
                <span class="priority-dot"></span>
                <span class="priority-text"></span>
            </div>
            <select id="prioritySelect" class="priority-select">
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>
        </div>

        <div class="description-section">
            <h3 class="issue-section-title">Description</h3>
            <div class="description-section-text">
                <textarea readonly class="description-textarea" id="issueDescription"></textarea>
            </div>
        </div>

        <div class="popup-actions ticket-actions">
            <button class="submit-btn resolved-btn" id="markResolvedBtn">Mark as Resolved</button>
            <button class="cancel-btn" id="cancelBtn">Cancel</button>
        </div>
    </div>
</div>

<!-- Action Dropdown Menu -->
<div class="action-dropdown" id="actionDropdown">
    <button class="dropdown-item" data-action="view">View</button>
    <button class="dropdown-item" data-action="resolve">Mark as Resolved</button>
    <button class="dropdown-item" data-action="cancel">Cancel</button>
</div>

<script>
    // Pass base URL from PHP to JavaScript
    var base_url = '<?php echo base_url(); ?>';
</script>
<script src="<?php echo base_url('assets/js/includes/sidebar.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/admin-js/admin-issues-pagination.js'); ?>"></script>
