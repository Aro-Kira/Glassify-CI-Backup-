document.addEventListener('DOMContentLoaded', function() {
    // Select all tab links using the common class
    const tabLinks = document.querySelectorAll('.tab-link');
    // Select all content sections using the common class
    const sections = document.querySelectorAll('.order-section');

    function switchTab(link) {
        // 1. Deactivate all links
        tabLinks.forEach(l => l.classList.remove('active'));

        // 2. Activate the clicked link
        link.classList.add('active');

        // 3. Determine the target section ID from the data-tab attribute
        const targetTab = link.getAttribute('data-tab'); // e.g., 'ready'
        const targetSectionId = 'tab-' + targetTab;      // e.g., 'tab-ready'

        // 4. Hide all content sections
        sections.forEach(section => section.classList.remove('active'));

        // 5. Show the target content section
        const targetSection = document.getElementById(targetSectionId);
        if (targetSection) {
            targetSection.classList.add('active');
            console.log('Switched to tab:', targetTab, 'Section:', targetSectionId);
        } else {
            console.error('Target section not found:', targetSectionId);
        }
        
        // Reset pagination when switching tabs
        if (typeof currentPage !== 'undefined') {
            currentPage = 1;
            if (typeof updatePagination === 'function') {
                setTimeout(() => updatePagination(), 100);
            }
        }
    }

    // Attach click listeners to all tab links
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            switchTab(this);
        });
    });
    
    // Also use event delegation as a fallback
    document.addEventListener('click', function(e) {
        const clickedLink = e.target.closest('.tab-link');
        if (clickedLink && tabLinks.length > 0) {
            e.preventDefault();
            e.stopPropagation();
            switchTab(clickedLink);
        }
    });
    
    // Check if we need to switch to a specific tab after page load (e.g., after order action)
    const switchToTab = sessionStorage.getItem('switchToTab');
    if (switchToTab) {
        // Find the tab link with matching data-tab attribute
        const targetLink = Array.from(tabLinks).find(link => link.getAttribute('data-tab') === switchToTab);
        if (targetLink) {
            // Small delay to ensure DOM is fully ready
            setTimeout(() => {
                switchTab(targetLink);
                // Clear the sessionStorage after switching
                sessionStorage.removeItem('switchToTab');
            }, 100);
        }
    }
});