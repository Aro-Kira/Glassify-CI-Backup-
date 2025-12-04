document.addEventListener('DOMContentLoaded', function() {
        const currentPathname = window.location.pathname;
        
        // Explicitly target elements by their IDs
        const notificationLink = document.getElementById('notification-link');
        const userProfileLink = document.getElementById('user-profile-link');
        
        // Function to check and apply 'active' class
        function checkAndSetActive(linkElement) {
            if (!linkElement) return;

            const linkHref = linkElement.getAttribute('href');
            let targetPath = linkHref;

            // Handle the parent directory selector for comparison
            if (linkHref.startsWith('../')) {
                // If it starts with '../', we remove it for a simple path check
                targetPath = linkHref.substring(3); // Removes '../'
            }

            // Check if the current URL path contains the target path (case-insensitive includes check)
            if (currentPathname.toLowerCase().includes(targetPath.toLowerCase())) {
                linkElement.classList.add('active');
            }
        }
        
        // Apply the check to both specific elements
        checkAndSetActive(notificationLink);
        checkAndSetActive(userProfileLink);
    });