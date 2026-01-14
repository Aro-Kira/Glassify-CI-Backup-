document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById("sidebar");
    const toggle = document.getElementById("menu-toggle");
    const container = document.getElementById("layout-container");

    console.log("DOM loaded");
    console.log("Sidebar element:", sidebar);
    console.log("Toggle button:", toggle);
    console.log("Container:", container);

    if (toggle && sidebar && container) {
        toggle.addEventListener("click", () => {
            console.log("Toggle clicked!");
            sidebar.classList.toggle("collapsed");
            container.classList.toggle("sidebar-collapsed");

            // Check if classes were added
            console.log("Sidebar classes:", sidebar.className);
            console.log("Container classes:", container.className);
        });
    } else {
        console.warn("Some elements are missing. Collapse will not work.");
    }

    // Submenu toggle functionality
    const submenuToggles = document.querySelectorAll('.submenu-toggle, .submenu-header');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const parentLi = this.closest('li');
            if (!parentLi) return;
            
            // Toggle expanded class on the parent li (not active - active is only for highlighting)
            if (parentLi.classList.contains('has-submenu')) {
                parentLi.classList.toggle('expanded');
            } else if (parentLi.classList.contains('submenu-item')) {
                parentLi.classList.toggle('expanded');
            }
        });
    });

    // Auto-expand parent submenus if they contain active items (but don't highlight them)
    const activeItem = sidebar.querySelector('.submenu-nested li.active, .submenu > li.active:not(.submenu-item)');
    if (activeItem) {
        // Expand Order Management if any child is active
        const hasSubmenu = sidebar.querySelector('.has-submenu');
        if (hasSubmenu) {
            hasSubmenu.classList.add('expanded');
        }
        // Expand nested submenu if a nested item is active
        const nestedActive = sidebar.querySelector('.submenu-nested li.active');
        if (nestedActive) {
            const parentSubmenuItem = nestedActive.closest('.submenu-item');
            if (parentSubmenuItem) {
                parentSubmenuItem.classList.add('expanded');
            }
        }
    }
});
