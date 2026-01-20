<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/addtocart_style.css'); ?>">
<script>
  const BASE_URL = "<?= base_url(); ?>";
  const PAYMENT_URL = "<?= rtrim(base_url(), '/') . '/payment'; ?>";
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= base_url('assets/js/cart.js'); ?>"></script>

<!-- Progress Navigation -->
<div class="progress-nav">
  <div class="step active">Cart</div>
  <div class="divider"></div>
  <div class="step">Payment</div>
  <div class="divider"></div>
  <div class="step">Complete</div>
</div>

<main>

  <!-- Title outside sections -->
  <div class="cart-title">
    <h2>My Cart</h2>
    <div class="title-divider"></div>
  </div>

  <!-- Content row -->
  <div class="cart-container">
    <!-- Cart Section -->
    <section class="cart-section">
      <table class="cart-table">
        <thead>
          <tr>
            <th class="checkbox-col">
              <label class="custom-checkbox">
                <input type="checkbox" id="select-all-items" checked>
                <span class="checkmark"></span>
              </label>
            </th>
            
            <th>Product</th>
            <th>Customization</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th> </th>
          </tr>
        </thead>
        <tbody id="cart-body">
          <?php if (!empty($cart_items)): ?>
            <?php foreach ($cart_items as $item): ?>
              <tr class="cart-row" data-cart-id="<?= $item->Cart_ID ?>">
                <td class="checkbox-col">
                  <label class="custom-checkbox">
                    <input type="checkbox" class="item-checkbox" data-id="<?= $item->Cart_ID ?>" 
                           data-price="<?= $item->Price ?>" data-quantity="<?= $item->Quantity ?>" checked>
                    <span class="checkmark"></span>
                  </label>
                </td>
               
                <td>
                  <?php 
                  // Get product image (not 2D customization image)
                  $placeholder_svg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
                  $product_img = $placeholder_svg;
                  
                  // Use product image (ImageUrl), not DesignRef (2D customization image)
                  $image_raw = $item->ImageUrl ?? '';
                  if (!empty($image_raw)) {
                      $decoded = json_decode($image_raw, true);
                      $first_image = '';
                      if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                          $first_image = $decoded[0];
                      } else {
                          $first_image = $image_raw;
                      }
                      
                      if (!empty($first_image) && strpos($first_image, 'broken-image-icon') === false) {
                          if (strpos($first_image, 'http') === 0) {
                              $product_img = $first_image;
                          } else if (strpos($first_image, 'assets/') === 0 || strpos($first_image, 'uploads/') === 0) {
                              $product_img = base_url($first_image);
                          } else {
                              $product_img = base_url('uploads/products/' . basename($first_image));
                          }
                      }
                  }
                  ?>
                  <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="<?= $product_img ?>" alt="<?= $item->ProductName ?>"
                      class="cart-product-img"
                      style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; flex-shrink: 0;"
                      onerror="this.onerror=null;this.alt='Image unavailable';this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2280%22%3E%3Crect fill=%22%23eee%22 width=%2280%22 height=%2280%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2210%22%3EN/A%3C/text%3E%3C/svg%3E';">
                    <span style="font-weight: 600; color: #0f2b46;"><?= $item->ProductName ?></span>
                  </div>
                </td>
                <td class="customization-info">
                  <?php if (!empty($item->CustomizationID)): ?>
                    <div class="custom-layout">
                      <?php if (!empty($item->DesignRef)): ?>
                        <div class="design-thumbnail-wrapper">
                          <img src="<?= base_url($item->DesignRef) ?>" 
                               alt="Custom Design" 
                               class="design-thumbnail"
                               onclick="showDesignModal('<?= base_url($item->DesignRef) ?>')"
                               onerror="this.onerror=null;this.alt='Image unavailable';this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2280%22%3E%3Crect fill=%22%23eee%22 width=%2280%22 height=%2280%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2210%22%3EN/A%3C/text%3E%3C/svg%3E';">
                          <span class="view-design-text">Click to view</span>
                        </div>
                      <?php endif; ?>
                      <div class="custom-details" id="details-<?= $item->Cart_ID ?>">
                        <?php 
                        $specs = [];
                        
                        // Size first
                        if (!empty($item->Dimensions)) {
                            $specs[] = ['label' => 'Size', 'value' => $item->Dimensions];
                        }
                        
                        // Shape second
                        if (!empty($item->GlassShape)) {
                            $specs[] = ['label' => 'Shape', 'value' => ucfirst($item->GlassShape)];
                        }
                        
                        // Others from PriceBreakdown in order
                        if (!empty($item->PriceBreakdown)) {
                            $breakdown = json_decode($item->PriceBreakdown, true);
                            if (isset($breakdown['fieldPrices'])) {
                                // Define the same order as in 2D Modeling
                                $preferredOrder = [
                                    'numberOfPanels' => 'Panel',
                                    'transomType' => 'Transom Type',
                                    'trackSystem' => 'Track System',
                                    'panelConfiguration' => 'Panel Configuration',
                                    'frameColor' => 'Frame Color',
                                    'frameType' => 'Frame Color',
                                    'glassType' => 'Glass Type',
                                    'glassThickness' => 'Thickness',
                                    'thickness' => 'Thickness',
                                    'edgeWork' => 'Edge Work',
                                    'edgeFinish' => 'Edge Finish',
                                    'lockType' => 'Lock Type',
                                    'rollerType' => 'Roller Type',
                                    'screen' => 'Screen',
                                    'screenOption' => 'Screen'
                                ];

                                foreach ($preferredOrder as $fieldId => $label) {
                                    if (isset($breakdown['fieldPrices'][$fieldId])) {
                                        $specs[] = [
                                            'label' => $label, 
                                            'value' => $breakdown['fieldPrices'][$fieldId]['option']
                                        ];
                                    }
                                }
                            }
                        } else {
                            // Fallback to standard columns
                            if (!empty($item->GlassType)) $specs[] = ['label' => 'Type', 'value' => ucfirst($item->GlassType)];
                            if (!empty($item->GlassThickness)) $specs[] = ['label' => 'Thickness', 'value' => $item->GlassThickness];
                            if (!empty($item->EdgeWork)) $specs[] = ['label' => 'Edge', 'value' => ucfirst(str_replace('-', ' ', $item->EdgeWork))];
                            if (!empty($item->FrameType)) $specs[] = ['label' => 'Frame', 'value' => ucfirst($item->FrameType)];
                        }

                        if (!empty($item->Engraving) && $item->Engraving !== 'None') {
                            $specs[] = ['label' => 'Engraving', 'value' => $item->Engraving];
                        }

                        // Display up to 3 specs
                        $displaySpecs = array_slice($specs, 0, 3);
                        foreach ($displaySpecs as $spec) {
                            echo '<span class="custom-tag">' . $spec['label'] . ': ' . $spec['value'] . '</span>';
                        }

                        // "View All" if more than 3
                        if (count($specs) > 3): ?>
                            <button class="view-all-specs" onclick="showAllSpecs(<?= htmlspecialchars(json_encode($specs)) ?>, '<?= $item->ProductName ?>')" style="background: none; border: none; color: #006494; cursor: pointer; padding: 5px 0; font-size: 13px; font-weight: 600; display: block; text-decoration: underline;">View All (<?= count($specs) ?>)</button>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php else: ?>
                    <span class="no-custom">Standard</span>
                  <?php endif; ?>
                </td>
                <td class="item-price" data-price="<?= $item->Price ?>">
                  <?php 
                    $basePrice = $item->BasePrice ?? $item->Price;
                    $currentPrice = $item->Price;
                    if ($basePrice > $currentPrice): 
                  ?>
                    <div class="price-container">
                      <span class="original-price price-val">₱<?= number_format($basePrice, 2) ?></span>
                      <span class="current-price price-val">₱<?= number_format($currentPrice, 2) ?></span>
                    </div>
                  <?php else: ?>
                    <span class="current-price price-val">₱<?= number_format($currentPrice, 2) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="qty-wrapper">
                    <button type="button" class="qty-btn qty-minus" data-id="<?= $item->Cart_ID ?>">−</button>
                    <input type="number" min="1" class="qty-input" data-id="<?= $item->Cart_ID ?>"
                      value="<?= $item->Quantity ?>">
                    <button type="button" class="qty-btn qty-plus" data-id="<?= $item->Cart_ID ?>">+</button>
                  </div>
                </td>
                <td class="item-total"><span class="price-val">₱<?= number_format($item->Price * $item->Quantity, 2) ?></span></td>
                <td>
                  <button class="edit-btn" data-id="<?= $item->Cart_ID ?>" data-product-id="<?= $item->Product_ID ?>" data-customization-id="<?= $item->CustomizationID ?>" title="Edit Customization">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                  </button>
                  <button class="remove-btn" data-id="<?= $item->Cart_ID ?>" data-product-id="<?= $item->Product_ID ?>" data-customization-id="<?= $item->CustomizationID ?>" title="Remove from Cart">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <line x1="18" y1="6" x2="6" y2="18"></line>
                      <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8">Your cart is empty.</td>
            </tr>
          <?php endif; ?>
        </tbody>


      </table>

      <button id="clear-cart" class="clear-btn">Clear Shopping Cart</button>
    </section>

    <!-- Order Summary Section -->
    <section class="order-summary">
      <!-- Desktop Order Summary -->
      <div class="order-summary-content">
        <h3>Order Summary</h3>
        <p><span>Items:</span> <span id="summary-items">0</span></p>
        <p><span>Subtotal:</span> <span class="price-val">₱<span id="summary-subtotal">0.00</span></span></p>
        <p><span>Shipping Fee:</span> <span class="price-val">₱<span id="summary-shipping">0.00</span></span></p>
        <p><span>Handling Fee:</span> <span class="price-val">₱<span id="summary-handling">0.00</span></span></p>
        <div class="summary-divider"></div>
        <p class="total"><span>Total:</span> <span class="price-val">₱<span id="summary-total">0.00</span></span></p>
        <div class="btn-container">
          <button class="checkout-btn" id="checkout-selected-btn">Check Out (<span id="selected-count">0</span> items)</button>
        </div>
      </div>


      <!-- Mobile Order Summary Bar (Shopee style) -->
      <div class="order-summary-mobile">
        <div class="order-summary-mobile-left">
          <label class="custom-checkbox">
            <input type="checkbox" id="select-all-items-mobile" checked>
            <span class="checkmark"></span>
          </label>
          <div class="order-summary-mobile-info">
            <div class="order-summary-mobile-items">
              <span id="summary-items-mobile">0</span> item(s)
            </div>
            <div class="order-summary-mobile-fees">
              <span class="fee-item">Shipping: <span class="price-val">₱<span id="summary-shipping-mobile">0.00</span></span></span>
              <span class="fee-item">Handling: <span class="price-val">₱<span id="summary-handling-mobile">0.00</span></span></span>
            </div>
            <div class="order-summary-mobile-total">
              Total: <span class="price-val">₱<span id="summary-total-mobile">0.00</span></span>
            </div>
          </div>
        </div>
        <div class="order-summary-mobile-right">
          <button class="checkout-btn" id="checkout-selected-btn-mobile">Check Out (<span id="selected-count-mobile">0</span>)</button>
        </div>
      </div>
    </section>
  </div> <!-- End cart-container -->

</main>





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
      <p>This design will be included in your invoice and quotation</p>
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
    closeSpecsModal();
  }
});

// Full Specifications Modal Functions
function showAllSpecs(specs, productName) {
    const modal = document.getElementById('specsModal');
    const title = document.getElementById('specsModalTitle');
    const body = document.getElementById('specsModalBody');
    
    title.textContent = `Specifications - ${productName}`;
    body.innerHTML = '';
    
    specs.forEach(spec => {
        const row = document.createElement('div');
        row.className = 'spec-modal-row';
        row.style.display = 'flex';
        row.style.justifyContent = 'space-between';
        row.style.padding = '10px 0';
        row.style.borderBottom = '1px solid #eee';
        
        row.innerHTML = `
            <span style="color: #666;">${spec.label}:</span>
            <span style="font-weight: 600; color: #0f2b46;">${spec.value}</span>
        `;
        body.appendChild(row);
    });
    
    modal.classList.add('active');
}

function closeSpecsModal() {
    document.getElementById('specsModal').classList.remove('active');
}
</script>

<!-- Full Specifications Modal -->
<div id="specsModal" class="modal">
  <div class="modal-overlay" onclick="closeSpecsModal()"></div>
  <div class="design-modal-content" style="max-width: 400px;">
    <button class="modal-close" onclick="closeSpecsModal()">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
    <div class="design-modal-header">
      <h3 id="specsModalTitle">Full Specifications</h3>
    </div>
    <div class="design-modal-body" id="specsModalBody" style="padding: 10px 20px;">
      <!-- Specs rows will be injected here -->
    </div>
    <div class="design-modal-footer">
      <button class="btn-primary" onclick="closeSpecsModal()">Close</button>
    </div>
  </div>
</div>
