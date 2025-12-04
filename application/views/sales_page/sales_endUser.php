<section class="user-section-main">
  <h1 class="page-title">End Users</h1>
  <p>Easily update user details, list of registered customer, and their contact information.</p>

  <div class="controls-container">
    <div class="search-bar">
      <input type="text" placeholder="Filter by name or category..." class="search-input">
    </div>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th></th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone Number</th>
          <th>Joined Date</th>
          <th>Last Active</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $row_num = ($start ?? 1);
        if (!empty($customers)): 
          foreach ($customers as $customer): 
            $full_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Middle_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
            $full_name = trim($full_name);
            $joined_date = $customer->Date_Created ? date('d/m/Y', strtotime($customer->Date_Created)) : 'N/A';
            $last_active = $customer->Last_Active ? date('d/m/Y', strtotime($customer->Last_Active)) : ($customer->Date_Updated ? date('d/m/Y', strtotime($customer->Date_Updated)) : 'N/A');
            $status = $customer->Status ?? 'Active';
        ?>
        <tr data-user-id="<?php echo $customer->UserID; ?>">
          <td><?php echo $row_num++; ?></td>
          <td><?php echo htmlspecialchars($full_name); ?></td>
          <td><?php echo htmlspecialchars($customer->Email ?? 'N/A'); ?></td>
          <td><?php echo htmlspecialchars($customer->PhoneNum ?? 'N/A'); ?></td>
          <td><?php echo $joined_date; ?></td>
          <td><?php echo $last_active; ?></td>
          <td>
            <span class="status-badge <?php echo strtolower($status); ?>"><?php echo $status; ?></span>
          </td>
        </tr>
        <?php 
          endforeach; 
        else: 
        ?>
        <tr>
          <td colspan="7" style="text-align: center; padding: 20px;">No customers found</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <span>Showing <?php echo $start ?? 1; ?>-<?php echo $end ?? count($customers ?? []); ?> of <?php echo $total_customers ?? 0; ?> items</span>
    <div class="pagination-controls">
      <?php if (isset($current_page) && $current_page > 1): ?>
        <a href="?page=<?php echo $current_page - 1; ?>" class="page-btn prev">&lt;</a>
      <?php else: ?>
        <span class="page-btn prev disabled">&lt;</span>
      <?php endif; ?>
      
      <?php
      $total_pages = $total_pages ?? 1;
      $current_page = $current_page ?? 1;
      
      // Sliding window pagination: show 3-5 pages that move
      $window_size = 3; // Number of pages to show around current page
      
      if ($total_pages <= 5) {
        // If 5 or fewer pages, show all
        $start_page = 1;
        $end_page = $total_pages;
      } else {
        // Calculate sliding window
        $start_page = max(1, $current_page - 1);
        $end_page = min($total_pages, $current_page + 1);
        
        // Adjust if we're near the beginning
        if ($start_page == 1) {
          $end_page = min($total_pages, 3);
        }
        
        // Adjust if we're near the end
        if ($end_page == $total_pages) {
          $start_page = max(1, $total_pages - 2);
        }
      }
      
      // Show first page and ellipsis if needed
      if ($start_page > 1): ?>
        <a href="?page=1" class="page-number">1</a>
        <?php if ($start_page > 2): ?>
          <span class="dots">...</span>
        <?php endif; ?>
      <?php endif; ?>
      
      <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
        <?php if ($i == $current_page): ?>
          <span class="page-number active"><?php echo $i; ?></span>
        <?php else: ?>
          <a href="?page=<?php echo $i; ?>" class="page-number"><?php echo $i; ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      
      <?php if ($end_page < $total_pages): ?>
        <?php if ($end_page < $total_pages - 1): ?>
          <span class="dots">...</span>
        <?php endif; ?>
        <a href="?page=<?php echo $total_pages; ?>" class="page-number"><?php echo $total_pages; ?></a>
      <?php endif; ?>
      
      <?php if (isset($current_page) && $current_page < $total_pages): ?>
        <a href="?page=<?php echo $current_page + 1; ?>" class="page-btn next">&gt;</a>
      <?php else: ?>
        <span class="page-btn next disabled">&gt;</span>
      <?php endif; ?>
    </div>
  </div>
</section>
</main>
</div>

<!-- Overlay & Popup -->
<div class="overlay" id="popupOverlay">
  <div class="popup">
    <div class="popup-header">
      <h2>View User</h2>
      <p>Use this section to view a user’s account information. Details are read-only.</p>
      <span class="close-btn" id="closePopupBtn">&times;</span>
    </div>
    <div class="popup-content">
      <h3>User Details</h3>
      <div class="view-details">
        <div class="detail-row">
          <label>First Name:</label>
          <span class="view-first-name" id="popupFirstName">-</span>
        </div>
        <div class="detail-row">
          <label>Middle Initial (optional):</label>
          <span class="view-middle-initial" id="popupMiddleName">-</span>
        </div>
        <div class="detail-row">
          <label>Surname:</label>
          <span class="view-surname" id="popupLastName">-</span>
        </div>
        <div class="detail-row">
          <label>Email Address:</label>
          <span class="view-email" id="popupEmail">-</span>
        </div>
        <div class="detail-row">
          <label>Phone Number:</label>
          <span class="view-phone" id="popupPhone">-</span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const base_url = '<?php echo base_url(); ?>';
  
  function openUserPopup(userData) {
    document.getElementById('popupFirstName').textContent = userData.first_name || '-';
    document.getElementById('popupMiddleName').textContent = userData.middle_name || '-';
    document.getElementById('popupLastName').textContent = userData.last_name || '-';
    document.getElementById('popupEmail').textContent = userData.email || '-';
    document.getElementById('popupPhone').textContent = userData.phone || '-';
    document.getElementById('popupOverlay').style.display = 'flex';
  }
  
  function closePopup() {
    document.getElementById('popupOverlay').style.display = 'none';
  }
  
  // Search functionality
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-input');
    const searchButton = document.querySelector('.search-button');
    const tableRows = document.querySelectorAll('tbody tr[data-user-id]');
    
    function filterTable() {
      const query = searchInput.value.toLowerCase().trim();
      
      tableRows.forEach(row => {
        const fullName = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const phone = row.cells[3].textContent.toLowerCase();
        
        if (fullName.includes(query) || email.includes(query) || phone.includes(query)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
      
      // Update pagination count
      const visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none');
      const paginationSpan = document.querySelector('.pagination span');
      if (paginationSpan) {
        paginationSpan.textContent = `Showing 1-${visibleRows.length} of ${visibleRows.length} items`;
      }
    }
    
    if (searchButton) {
      searchButton.addEventListener('click', filterTable);
    }
    
    if (searchInput) {
      searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
          filterTable();
        } else {
          filterTable();
        }
      });
    }
    
    // Close popup when clicking outside or on close button
    const overlay = document.getElementById('popupOverlay');
    const closeBtn = document.getElementById('closePopupBtn');
    
    if (closeBtn) {
      closeBtn.addEventListener('click', closePopup);
    }
    
    if (overlay) {
      overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
          closePopup();
        }
      });
    }
  });
</script>
