<!--
============================================================================
WISHLIST PAGE VIEW
============================================================================

Displays the customer's saved wishlist items in a table format.

FEATURES:
- Table showing product image, name, customization details, and price
- "Add to Cart" button to move items to shopping cart
- "Remove" (X) button to delete individual items
- "Clear Wishlist" link to remove all items at once
- Custom design thumbnail with modal preview
- Empty state with "Browse Products" link

DATA PASSED FROM CONTROLLER:
- $wishlist_items: Array of wishlist items with product and customization data

JAVASCRIPT FILES:
- wishlist.js: Handles remove, move-to-cart, and clear actions

@author      Glassify Development Team
@version     1.0.0
@created     December 2025
============================================================================
-->

<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/wishlist_style.css'); ?>">

<!-- Set BASE_URL for JavaScript AJAX calls -->
<script>
    const BASE_URL = "<?= base_url(); ?>";
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ========================= WISHLIST SECTION ========================= -->
<section>
    <div class="container">
        <h2 class="wishlist-title">My Wishlist</h2>
        <hr class="divider">

        <div class="wishlist-table">
            <table>
                <thead>
                    <tr>
                        <th class="remove-col"> </th>
                        <th class="image-col">Image</th>
                        <th class="product-col">Product</th>
                        <th class="customization-col">Customization</th>
                        <th class="price-col">Price</th>
                        <th class="action-col"></th>
                    </tr>
                </thead>
                <tbody id="wishlist-body">
                    <?php if (!empty($wishlist_items)): ?>
                        <?php foreach ($wishlist_items as $item): ?>
                            <tr class="wishlist-row" data-id="<?= $item->Wishlist_ID ?>">
                                <td>
                                    <button class="remove-btn" data-id="<?= $item->Wishlist_ID ?>">X</button>
                                </td>
                                <td>
                                    <img src="<?= base_url('uploads/products/' . $item->ImageUrl) ?>" alt="<?= $item->ProductName ?>" class="wishlist-product-img">
                                </td>
                                <td class="product-name"><?= $item->ProductName ?></td>
                                <td class="customization-info">
                                    <?php if (!empty($item->CustomizationID)): ?>
                                        <div class="custom-layout">
                                            <?php if (!empty($item->DesignRef)): ?>
                                                <div class="design-thumbnail-wrapper">
                                                    <img src="<?= base_url($item->DesignRef) ?>" 
                                                         alt="Custom Design" 
                                                         class="design-thumbnail"
                                                         onclick="showDesignModal('<?= base_url($item->DesignRef) ?>')">
                                                    <span class="view-design-text">Click to view</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="custom-details">
                                                <?php if (!empty($item->Dimensions)): ?>
                                                    <span class="custom-tag">Size: <?= $item->Dimensions ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->GlassShape)): ?>
                                                    <span class="custom-tag">Shape: <?= ucfirst($item->GlassShape) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->GlassType)): ?>
                                                    <span class="custom-tag">Type: <?= ucfirst($item->GlassType) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->GlassThickness)): ?>
                                                    <span class="custom-tag">Thickness: <?= $item->GlassThickness ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->EdgeWork)): ?>
                                                    <span class="custom-tag">Edge: <?= ucfirst(str_replace('-', ' ', $item->EdgeWork)) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->FrameType)): ?>
                                                    <span class="custom-tag">Frame: <?= ucfirst($item->FrameType) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->Engraving) && $item->Engraving !== 'None'): ?>
                                                    <span class="custom-tag engraving-tag">Engraving: <?= $item->Engraving ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-custom">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td class="item-price">₱<?= number_format($item->Price, 2) ?></td>
                                <td class="actions">
                                    <button class="add-cart-btn" data-id="<?= $item->Wishlist_ID ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                        Add to Cart
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="empty-row">
                            <td colspan="6" class="empty-wishlist">
                                <div class="empty-message">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                    </svg>
                                    <p>Your wishlist is empty</p>
                                    <a href="<?= base_url('products') ?>" class="browse-btn">Browse Products</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($wishlist_items)): ?>
            <div class="wishlist-actions">
                <a href="#" class="clear-wishlist" id="clear-wishlist">Clear Wishlist</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Design Preview Modal -->
<div id="designModal" class="modal">
    <div class="modal-overlay" onclick="closeDesignModal()"></div>
    <div class="design-modal-content">
        <button class="modal-close" onclick="closeDesignModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="design-modal-header">
            <h3>Custom Design Layout</h3>
            <p>This design will be included in your order</p>
        </div>
        <div class="design-modal-body">
            <img id="designModalImage" src="" alt="Custom Design">
        </div>
        <div class="design-modal-footer">
            <button class="btn-primary" onclick="downloadDesignImage()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Download Design
            </button>
        </div>
    </div>
</div>

<script>
// Design Modal Functions
function showDesignModal(imageSrc) {
    document.getElementById('designModalImage').src = imageSrc;
    document.getElementById('designModal').classList.add('active');
}

function closeDesignModal() {
    document.getElementById('designModal').classList.remove('active');
}

function downloadDesignImage() {
    const img = document.getElementById('designModalImage');
    const link = document.createElement('a');
    link.href = img.src;
    link.download = 'custom-design-' + Date.now() + '.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDesignModal();
    }
});
</script>

<script src="<?= base_url('assets/js/wishlist.js'); ?>"></script>
