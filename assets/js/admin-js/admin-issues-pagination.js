/**
 * Admin Issues Management
 * Handles issue table, search, pagination, and detail panel
 */

(function() {
    'use strict';

    // Global variables
    let allIssues = [];
    let filteredIssues = [];
    let currentPage = 1;
    const itemsPerPage = 10;
    let currentIssueId = null;
    let currentActionDropdown = null;

    // API base URL - use base_url from PHP if available, otherwise default
    const API_BASE = (typeof base_url !== 'undefined') ? base_url : '/Glassify-CI/';

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializePage();
    });

    /**
     * Initialize the page
     */
    function initializePage() {
        loadIssues();
        setupEventListeners();
        updateIssueSummary();
    }

    /**
     * Setup all event listeners
     */
    function setupEventListeners() {
        // Search
        const searchInput = document.querySelector('.search-input');
        const searchButton = document.querySelector('.search-button');
        
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        }
        
        if (searchButton) {
            searchButton.addEventListener('click', performSearch);
        }

        // Close panel
        const closeBtn = document.getElementById('closePopupTicket');
        if (closeBtn) {
            closeBtn.addEventListener('click', closePanel);
        }

        // Panel overlay click
        const overlay = document.getElementById('popupOverlay');
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    closePanel();
                }
            });
        }

        // Mark as resolved button
        const markResolvedBtn = document.getElementById('markResolvedBtn');
        if (markResolvedBtn) {
            markResolvedBtn.addEventListener('click', handleMarkResolved);
        }

        // Cancel button
        const cancelBtn = document.getElementById('cancelBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closePanel);
        }

        // Priority selector
        const prioritySelect = document.getElementById('prioritySelect');
        if (prioritySelect) {
            prioritySelect.addEventListener('change', handlePriorityChange);
        }

        // Pagination buttons are now generated dynamically in updatePagination()

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (currentActionDropdown && !currentActionDropdown.contains(e.target)) {
                closeActionDropdown();
            }
        });
    }

    /**
     * Load issues from API
     */
    function loadIssues() {
        fetch(API_BASE + 'admin-get-issues')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allIssues = data.issues;
                    filteredIssues = [...allIssues];
                    renderTable();
                    updateIssueSummary();
                } else {
                    console.error('Failed to load issues:', data.message);
                    showError('Failed to load issues. Please refresh the page.');
                }
            })
            .catch(error => {
                console.error('Error loading issues:', error);
                showError('Error loading issues. Please check your connection.');
            });
    }

    /**
     * Update issue summary
     */
    function updateIssueSummary() {
        const totalOpen = allIssues.filter(i => i.status === 'Open').length;
        const highPriority = allIssues.filter(i => i.status === 'Open' && i.priority === 'High').length;
        
        const summaryEl = document.getElementById('issueSummary');
        if (summaryEl) {
            summaryEl.textContent = `${totalOpen} open issues | ${highPriority} High Priority`;
        }
    }

    /**
     * Perform search
     */
    function performSearch() {
        const searchInput = document.querySelector('.search-input');
        const query = searchInput.value.toLowerCase().trim();
        
        if (query === '') {
            filteredIssues = [...allIssues];
        } else {
            filteredIssues = allIssues.filter(issue => {
                return (
                    issue.ticket_id.toLowerCase().includes(query) ||
                    issue.category.toLowerCase().includes(query) ||
                    issue.email.toLowerCase().includes(query) ||
                    `${issue.first_name} ${issue.last_name}`.toLowerCase().includes(query)
                );
            });
        }
        
        currentPage = 1;
        renderTable();
    }

    /**
     * Render the issues table
     */
    function renderTable() {
        const tbody = document.querySelector('tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (filteredIssues.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px;">No issues found</td></tr>';
            updatePagination();
            return;
        }

        // Get issues for current page
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageIssues = filteredIssues.slice(startIndex, endIndex);

        pageIssues.forEach(issue => {
            const row = createTableRow(issue);
            tbody.appendChild(row);
        });

        updatePagination();
    }

    /**
     * Create a table row for an issue
     */
    function createTableRow(issue) {
        const tr = document.createElement('tr');
        tr.dataset.issueId = issue.issue_id;

        // Checkbox
        const tdCheckbox = document.createElement('td');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'issue-checkbox';
        tdCheckbox.appendChild(checkbox);
        tr.appendChild(tdCheckbox);

        // Ticket ID
        const tdTicket = document.createElement('td');
        tdTicket.textContent = issue.ticket_id;
        tr.appendChild(tdTicket);

        // Category
        const tdCategory = document.createElement('td');
        tdCategory.textContent = issue.category;
        tr.appendChild(tdCategory);

        // Priority
        const tdPriority = document.createElement('td');
        const priorityTag = document.createElement('div');
        priorityTag.className = `priority-tag-table ${issue.priority.toLowerCase()}`;
        priorityTag.innerHTML = `
            <span class="priority-dot-table"></span>
            ${issue.priority}
        `;
        tdPriority.appendChild(priorityTag);
        tr.appendChild(tdPriority);

        // Email
        const tdEmail = document.createElement('td');
        tdEmail.textContent = issue.email;
        tr.appendChild(tdEmail);

        // Action
        const tdAction = document.createElement('td');
        if (issue.status === 'Resolved') {
            const resolvedBadge = document.createElement('span');
            resolvedBadge.className = 'resolved-badge';
            resolvedBadge.textContent = 'Issue Resolved';
            resolvedBadge.style.cssText = 'color: #45A927; font-weight: bold;';
            tdAction.appendChild(resolvedBadge);
        } else {
            const actionBtn = document.createElement('button');
            actionBtn.className = 'kebab-btn';
            actionBtn.innerHTML = '&#8942;'; // Three dots
            actionBtn.onclick = (e) => {
                e.stopPropagation();
                showActionDropdown(e.target, issue.issue_id);
            };
            tdAction.appendChild(actionBtn);
        }
        tr.appendChild(tdAction);

        return tr;
    }

    /**
     * Show action dropdown
     */
    function showActionDropdown(button, issueId) {
        closeActionDropdown();

        const dropdown = document.getElementById('actionDropdown');
        if (!dropdown) return;

        currentIssueId = issueId;
        
        // Position dropdown
        const rect = button.getBoundingClientRect();
        dropdown.style.display = 'block';
        dropdown.style.position = 'fixed';
        dropdown.style.top = (rect.bottom + 5) + 'px';
        dropdown.style.left = (rect.left - 100) + 'px';
        dropdown.style.zIndex = '10000';

        // Setup dropdown actions
        setupDropdownActions(dropdown, issueId);
        
        currentActionDropdown = dropdown;
    }

    /**
     * Setup dropdown menu actions
     */
    function setupDropdownActions(dropdown, issueId) {
        const items = dropdown.querySelectorAll('.dropdown-item');
        
        items.forEach(item => {
            item.onclick = function(e) {
                e.stopPropagation();
                const action = this.dataset.action;
                
                if (action === 'view') {
                    viewIssue(issueId);
                } else if (action === 'resolve') {
                    markIssueResolved(issueId);
                } else if (action === 'cancel') {
                    closeActionDropdown();
                }
                
                closeActionDropdown();
            };
        });
    }

    /**
     * Close action dropdown
     */
    function closeActionDropdown() {
        const dropdown = document.getElementById('actionDropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
        currentActionDropdown = null;
        currentIssueId = null;
    }

    /**
     * View issue details
     */
    function viewIssue(issueId) {
        fetch(API_BASE + 'admin-get-issue-details/' + issueId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayIssueDetails(data.issue);
                    openPanel();
                } else {
                    showError('Failed to load issue details.');
                }
            })
            .catch(error => {
                console.error('Error loading issue details:', error);
                showError('Error loading issue details.');
            });
    }

    /**
     * Display issue details in panel
     */
    function displayIssueDetails(issue) {
        currentIssueId = issue.issue_id;

        // Customer info
        document.querySelector('.contact-name').textContent = `${issue.first_name} ${issue.last_name}`;
        document.querySelector('.contact-email').textContent = issue.email;
        document.querySelector('.contact-phone').textContent = issue.phone || 'N/A';
        document.querySelector('.contact-order-id').textContent = issue.order_id || 'N/A';

        // Category
        document.getElementById('issueCategory').value = issue.category || 'N/A';

        // Priority
        updatePriorityDisplay(issue.priority);
        const prioritySelect = document.getElementById('prioritySelect');
        if (prioritySelect) {
            prioritySelect.value = issue.priority || 'Low';
        }

        // Description
        document.getElementById('issueDescription').value = issue.description || 'No description provided.';
        
        // Handle attachment display
        const attachmentSection = document.getElementById('attachmentSection');
        const attachmentThumbnail = document.getElementById('attachmentThumbnail');
        const attachmentLink = document.getElementById('attachmentLink');
        const attachmentText = document.getElementById('attachmentText');
        
        if (issue.file_attached && issue.file_attached !== 'N/A' && issue.file_url) {
            attachmentSection.style.display = 'block';
            const fileName = issue.file_attached.includes('/') ? issue.file_attached.split('/').pop() : issue.file_attached;
            const fileExtension = fileName.split('.').pop().toLowerCase();
            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            const isImage = imageExtensions.includes(fileExtension);
            
            if (isImage) {
                attachmentThumbnail.src = issue.file_url;
                attachmentThumbnail.alt = fileName;
                attachmentThumbnail.style.display = 'block';
                attachmentLink.href = issue.file_url;
                attachmentLink.textContent = fileName;
                attachmentLink.style.display = 'inline';
                attachmentText.style.display = 'none';
            } else {
                attachmentThumbnail.style.display = 'none';
                attachmentLink.href = issue.file_url;
                attachmentLink.textContent = fileName;
                attachmentLink.style.display = 'inline';
                attachmentText.style.display = 'none';
            }
        } else {
            attachmentSection.style.display = 'none';
            attachmentThumbnail.style.display = 'none';
            attachmentLink.style.display = 'none';
            attachmentText.style.display = 'inline';
            attachmentText.textContent = 'N/A';
        }
    }

    /**
     * Update priority display
     */
    function updatePriorityDisplay(priority) {
        const priorityTag = document.getElementById('priorityTag');
        const priorityText = priorityTag.querySelector('.priority-text');
        
        if (priorityTag && priorityText) {
            priorityTag.className = `priority-tag ${priority.toLowerCase()}`;
            priorityText.textContent = priority;
        }
    }

    /**
     * Handle priority change
     */
    function handlePriorityChange() {
        const prioritySelect = document.getElementById('prioritySelect');
        const newPriority = prioritySelect.value;

        if (!currentIssueId) return;

        fetch(API_BASE + 'admin-update-priority', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `issue_id=${currentIssueId}&priority=${encodeURIComponent(newPriority)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePriorityDisplay(newPriority);
                loadIssues(); // Reload to update table
                showSuccess('Priority updated successfully');
            } else {
                showError('Failed to update priority.');
            }
        })
        .catch(error => {
            console.error('Error updating priority:', error);
            showError('Error updating priority.');
        });
    }

    /**
     * Open right panel
     */
    function openPanel() {
        const overlay = document.getElementById('popupOverlay');
        if (overlay) {
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Close right panel
     */
    function closePanel() {
        const overlay = document.getElementById('popupOverlay');
        if (overlay) {
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
        currentIssueId = null;
    }

    /**
     * Handle mark as resolved from panel
     */
    function handleMarkResolved() {
        if (currentIssueId) {
            markIssueResolved(currentIssueId);
        }
    }

    /**
     * Mark issue as resolved
     */
    async function markIssueResolved(issueId) {
        const confirmed = await showConfirmationAsync('Are you sure you want to mark this issue as resolved?');
        if (!confirmed) {
            return;
        }

        fetch(API_BASE + 'admin-mark-resolved', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `issue_id=${issueId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Issue marked as resolved');
                closePanel();
                loadIssues(); // Reload to update table
            } else {
                showError('Failed to mark issue as resolved.');
            }
        })
        .catch(error => {
            console.error('Error marking issue as resolved:', error);
            showError('Error marking issue as resolved.');
        });
    }

    /**
     * Change page
     */
    function changePage(page) {
        const totalPages = Math.ceil(filteredIssues.length / itemsPerPage);
        if (page < 1 || page > totalPages) return;
        
        currentPage = page;
        renderTable();
    }

    /**
     * Update pagination controls
     */
    function updatePagination() {
        const totalItems = filteredIssues.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1; // At least 1 page
        const startItem = totalItems === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
        const endItem = Math.min(currentPage * itemsPerPage, totalItems);

        // Update text
        const paginationText = document.getElementById('paginationText');
        if (paginationText) {
            paginationText.textContent = `Showing ${startItem}-${endItem} of ${totalItems} items`;
        }

        // Update pagination controls - dynamically generate page numbers
        const paginationControls = document.getElementById('paginationControls');
        if (!paginationControls) return;

        // Clear existing pagination
        paginationControls.innerHTML = '';

        // If only 1 page or no items, don't show pagination numbers
        if (totalPages <= 1) {
            return;
        }

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'page-btn prev';
        prevBtn.innerHTML = '&lt;';
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                changePage(currentPage - 1);
            }
        });
        paginationControls.appendChild(prevBtn);

        // Page numbers - show all pages if 5 or fewer, otherwise show with ellipsis
        if (totalPages <= 5) {
            // Show all page numbers if 5 or fewer pages
            for (let i = 1; i <= totalPages; i++) {
                const pageNum = document.createElement('span');
                pageNum.className = 'page-number';
                if (i === currentPage) {
                    pageNum.classList.add('active');
                }
                pageNum.textContent = i.toString();
                pageNum.addEventListener('click', () => changePage(i));
                paginationControls.appendChild(pageNum);
            }
        } else {
            // Show page numbers with ellipsis for many pages
            // Always show first page
            const firstPage = document.createElement('span');
            firstPage.className = 'page-number';
            if (1 === currentPage) {
                firstPage.classList.add('active');
            }
            firstPage.textContent = '1';
            firstPage.addEventListener('click', () => changePage(1));
            paginationControls.appendChild(firstPage);

            // Show ellipsis and pages around current page
            if (currentPage > 3) {
                const dots = document.createElement('span');
                dots.className = 'dots';
                dots.textContent = '...';
                paginationControls.appendChild(dots);
            }

            // Show pages around current page
            const startPage = Math.max(2, currentPage - 1);
            const endPage = Math.min(totalPages - 1, currentPage + 1);

            for (let i = startPage; i <= endPage; i++) {
                if (i === 1 || i === totalPages) continue; // Skip if already shown or will be shown
                
                const pageNum = document.createElement('span');
                pageNum.className = 'page-number';
                if (i === currentPage) {
                    pageNum.classList.add('active');
                }
                pageNum.textContent = i.toString();
                pageNum.addEventListener('click', () => changePage(i));
                paginationControls.appendChild(pageNum);
            }

            // Show ellipsis before last page if needed
            if (currentPage < totalPages - 2) {
                const dots = document.createElement('span');
                dots.className = 'dots';
                dots.textContent = '...';
                paginationControls.appendChild(dots);
            }

            // Always show last page
            if (totalPages > 1) {
                const lastPage = document.createElement('span');
                lastPage.className = 'page-number';
                if (totalPages === currentPage) {
                    lastPage.classList.add('active');
                }
                lastPage.textContent = totalPages.toString();
                lastPage.addEventListener('click', () => changePage(totalPages));
                paginationControls.appendChild(lastPage);
            }
        }

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'page-btn next';
        nextBtn.innerHTML = '&gt;';
        nextBtn.disabled = currentPage >= totalPages;
        nextBtn.addEventListener('click', () => {
            if (currentPage < totalPages) {
                changePage(currentPage + 1);
            }
        });
        paginationControls.appendChild(nextBtn);
    }

    /**
     * Show error message
     */
    function showError(message) {
        if (typeof showToast === 'function') {
            showToast(message, 'error');
        } else {
            console.error('Error:', message);
        }
    }

    /**
     * Show success message
     */
    function showSuccess(message) {
        if (typeof showToast === 'function') {
            showToast(message, 'success');
        } else {
            console.log('Success:', message);
        }
    }

})();
