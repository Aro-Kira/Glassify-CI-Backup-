// Function to update cart and wishlist counts
function updateHeaderCounts() {
  // Get BASE_URL from script tag or default
  const baseUrl = window.BASE_URL || (document.querySelector('script[data-base-url]')?.getAttribute('data-base-url')) || window.location.origin + '/';
  
  // Update cart count
  fetch(baseUrl + 'CartCon/get_cart_count_ajax')
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        const cartBadge = document.getElementById('cart-count');
        if (cartBadge) {
          if (data.count > 0) {
            cartBadge.textContent = data.count;
            cartBadge.style.display = 'flex';
          } else {
            cartBadge.style.display = 'none';
          }
        } else {
          // Create badge if it doesn't exist and count > 0
          const cartLink = document.querySelector('.cart-icon-link');
          if (cartLink && data.count > 0) {
            const wrapper = cartLink.querySelector('.icon-wrapper');
            if (wrapper) {
              let badge = wrapper.querySelector('.icon-badge');
              if (!badge) {
                badge = document.createElement('span');
                badge.className = 'icon-badge';
                badge.id = 'cart-count';
                wrapper.appendChild(badge);
              }
              badge.textContent = data.count;
              badge.style.display = 'flex';
            }
          }
        }
      }
    })
    .catch(error => console.error('Error updating cart count:', error));
  
  // Update wishlist count
  fetch(baseUrl + 'WishlistCon/get_count_ajax')
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        const wishlistBadge = document.getElementById('wishlist-count');
        if (wishlistBadge) {
          if (data.count > 0) {
            wishlistBadge.textContent = data.count;
            wishlistBadge.style.display = 'flex';
          } else {
            wishlistBadge.style.display = 'none';
          }
        } else {
          // Create badge if it doesn't exist and count > 0
          const wishlistLink = document.querySelector('.wishlist-icon-link');
          if (wishlistLink && data.count > 0) {
            const wrapper = wishlistLink.querySelector('.icon-wrapper');
            if (wrapper) {
              let badge = wrapper.querySelector('.icon-badge');
              if (!badge) {
                badge = document.createElement('span');
                badge.className = 'icon-badge';
                badge.id = 'wishlist-count';
                wrapper.appendChild(badge);
              }
              badge.textContent = data.count;
              badge.style.display = 'flex';
            }
          }
        }
      }
    })
    .catch(error => console.error('Error updating wishlist count:', error));
}

// Make function globally available for other scripts
window.updateHeaderCounts = updateHeaderCounts;

document.addEventListener("DOMContentLoaded", () => {
  // Update counts on page load (only if user is logged in)
  const isLoggedIn = document.querySelector('.cart-icon-link') && 
                     !document.querySelector('.cart-icon-link').href.includes('login');
  if (isLoggedIn) {
    updateHeaderCounts();
  }
  
  const currentPath = window.location.pathname.split("/").filter(Boolean);
  const currentFile = currentPath[currentPath.length - 1]; // e.g., "login" or "home"
  const currentRedirect = new URLSearchParams(window.location.search).get("redirect");
  const fullPath = window.location.pathname; // Full path for better matching

  const links = document.querySelectorAll(".menu a, .icons a");

  links.forEach(link => {
    const linkUrl = new URL(link.href, window.location.origin);
    const linkPath = linkUrl.pathname.split("/").filter(Boolean);
    const linkFile = linkPath[linkPath.length - 1]; // e.g., "about" or "login"
    const linkRedirect = linkUrl.searchParams.get("redirect");
    const linkFullPath = linkUrl.pathname;

    // Reset all links
    link.classList.remove("active");

    // Special handling for icon links (cart and wishlist)
    if (link.classList.contains("icon-link")) {
      // Check if we're on the cart page
      if (linkFullPath.includes('addtocart') || linkFullPath.includes('cart-page')) {
        if (fullPath.includes('addtocart') || fullPath.includes('cart-page')) {
          link.classList.add("active");
        }
      }
      // Check if we're on the wishlist page
      else if (linkFullPath.includes('wishlist')) {
        if (fullPath.includes('wishlist') && !fullPath.includes('wishlist/add') && 
            !fullPath.includes('wishlist/remove') && !fullPath.includes('wishlist/clear') &&
            !fullPath.includes('wishlist/move_to_cart') && !fullPath.includes('wishlist/count')) {
          link.classList.add("active");
        }
      }
      // If icon link redirects to login, don't mark as active
      else if (linkFile === "login" && linkRedirect) {
        link.classList.remove("active");
      }
    }
    // Normal active highlighting for menu links
    else {
      if (currentFile === linkFile && (!linkRedirect || currentRedirect === linkRedirect)) {
        link.classList.add("active");
      }
    }

    // Special case: if current page is login/register, only highlight login/register link
    if (link.id === "auth-link") {
      if (currentFile === "login" || currentFile === "register") {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    }
  });

  

});
