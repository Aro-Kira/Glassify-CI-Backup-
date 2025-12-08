document.addEventListener("DOMContentLoaded", () => {
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
