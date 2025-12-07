<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/addtocart_style.css'); ?>">
<script>
  const BASE_URL = "<?= base_url(); ?>";
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
            
            <th>Image</th>
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
                  <img src="<?= base_url('uploads/products/' . $item->ImageUrl) ?>" alt="<?= $item->ProductName ?>"
                    class="cart-product-img">
                </td>
                <td><?= $item->ProductName ?></td>
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
                <td class="item-price" data-price="<?= $item->Price ?>">₱<?= number_format($item->Price, 2) ?></td>
                <td>
                  <div class="qty-wrapper">
                    <button type="button" class="qty-btn qty-minus" data-id="<?= $item->Cart_ID ?>">−</button>
                    <input type="number" min="1" class="qty-input" data-id="<?= $item->Cart_ID ?>"
                      value="<?= $item->Quantity ?>">
                    <button type="button" class="qty-btn qty-plus" data-id="<?= $item->Cart_ID ?>">+</button>
                  </div>
                </td>
                <td class="item-total">₱<?= number_format($item->Price * $item->Quantity, 2) ?></td>
                <td>
                  <button class="remove-btn" data-id="<?= $item->Cart_ID ?>" data-product-id="<?= $item->Product_ID ?>" data-customization-id="<?= $item->CustomizationID ?>">X</button>
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
      <div class="order-summary-content">
        <h3>Order Summary</h3>
        <p><span>Items:</span> <span id="summary-items">0</span></p>
        <p><span>Subtotal:</span> ₱<span id="summary-subtotal">0.00</span></p>
        <p><span>Shipping Fee:</span> ₱<span id="summary-shipping">0.00</span></p>
        <p><span>Handling Fee:</span> ₱<span id="summary-handling">0.00</span></p>
        <div class="summary-divider"></div>
        <p class="total"><span>Total:</span> ₱<span id="summary-total">0.00</span></p>
        <div class="btn-container">
          <button class="checkout-btn" id="checkout-selected-btn">Check Out (<span id="selected-count">0</span> items)</button>
        </div>
      </div>

      <div class="quotation-content">
        <button class="generate-btn" id="openModal">Generate Quotation</button>
      </div>
    </section>
  </div> <!-- End cart-container -->

</main>




<!-- Simple Modern Quotation Modal -->
<div id="quotationModal" class="modal">
  <div class="modal-overlay"></div>
  <div class="modal-content">
    <button class="modal-close" id="closeModal">&times;</button>

    <div class="modal-header">
      <h2>Quotation</h2>
      <span class="quotation-date" id="quotation-date"></span>
    </div>

    <div class="modal-body">
      <!-- Customer Info - Inline Style -->
      <div class="customer-info-bar">
        <div class="customer-detail">
          <span class="label">Customer</span>
          <span class="value" id="quote-customer-name"><?php 
            if (isset($customer)) {
              $name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Middle_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
              echo $name ?: 'N/A';
            } else {
              echo 'N/A';
            }
          ?></span>
        </div>
        <div class="customer-detail">
          <span class="label">Email</span>
          <span class="value" id="quote-customer-email"><?= isset($customer->Email) ? $customer->Email : 'N/A' ?></span>
        </div>
        <div class="customer-detail">
          <span class="label">Phone</span>
          <span class="value" id="quote-customer-phone"><?= isset($customer->PhoneNum) ? $customer->PhoneNum : 'N/A' ?></span>
        </div>
        <div class="customer-detail full-width">
          <span class="label">Address</span>
          <span class="value" id="quote-customer-address"><?= isset($customer->Address) ? $customer->Address : 'N/A' ?></span>
        </div>
      </div>

      <!-- Items Table -->
      <div class="table-wrapper">
        <table class="quotation-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Customization</th>
              <th>Qty</th>
              <th>Unit Price</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody id="quotation-items">
            <!-- Rows will be dynamically generated -->
          </tbody>
        </table>
      </div>

      <!-- Custom Design Layouts Section -->
      <div class="designs-section" id="designs-section" style="display: none;">
        <h4 class="section-title">Custom Design Layouts</h4>
        <p class="designs-note">Included designs for reference</p>
        <div class="designs-grid" id="quotation-designs">
          <!-- Design images will be dynamically generated -->
        </div>
      </div>

      <!-- Totals -->
      <div class="totals-box">
        <div class="total-line">
          <span>Subtotal</span>
          <span id="quote-subtotal">₱0.00</span>
        </div>
        <div class="total-line">
          <span>Shipping Fee</span>
          <span id="quote-shipping">₱0.00</span>
        </div>
        <div class="total-line">
          <span>Handling Fee</span>
          <span id="quote-handling">₱0.00</span>
        </div>
        <div class="total-line grand">
          <span>Grand Total</span>
          <span id="quote-grandtotal">₱0.00</span>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn-close" id="closeModalBtn">Close</button>
      <button class="btn-print" id="printQuotation">Print Quotation</button>
    </div>
  </div>
</div>

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
  }
});
</script>