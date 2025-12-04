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
          <th> </th>
            <th>Image</th>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
          
          </tr>
        </thead>
        <tbody id="cart-body">
          <?php if (!empty($cart_items)): ?>
            <?php foreach ($cart_items as $item): ?>
              <tr class="cart-row">
              <td>
                  <button class="remove-btn" data-id="<?= $item->Cart_ID ?>">X</button>
                </td>
                <td>
                  <img src="<?= base_url('uploads/products/' . $item->ImageUrl) ?>" alt="<?= $item->ProductName ?>"
                    class="cart-product-img">
                </td>
                <td><?= $item->ProductName ?></td>
                <td class="item-price" data-price="<?= $item->Price ?>">₱<?= number_format($item->Price, 2) ?></td>
                <td>
                  <input type="number" min="1" class="qty-input" data-id="<?= $item->Cart_ID ?>"
                    value="<?= $item->Quantity ?>">
                </td>
                <td class="item-total">₱<?= number_format($item->Price * $item->Quantity, 2) ?></td>
              

              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6">Your cart is empty.</td>
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
          <a href="<?php echo base_url('payment'); ?>">
            <button class="checkout-btn">Check Out</button>
          </a>
        </div>
      </div>

      <div class="quotation-content">
        <button class="generate-btn" id="openModal">Generate Quotation</button>
      </div> <!-- ✅ Added closing div -->
    </section>


</main>




<div id="quotationModal" class="modal">
  <div class="modal-content">
    <span class="modal-close" id="closeModal">&times;</span>

    <div class="modal-header">Quotation</div>
    <p class="quotation-date">Date: <span id="quotation-date"></span></p>

    <div class="section-title">Customer Information:</div>
    <div class="customer-info">
      <p><strong>Name:</strong> John Doe</p>
      <p><strong>Address:</strong> 123 Main Street, Anytown, USA</p>
      <p><strong>Email:</strong> john.doe@example.com</p>
      <p><strong>Phone:</strong> (123) 456-7890</p>
    </div>

    <div class="section-title">Quotation Details:</div>
    <table class="quotation-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Quantity</th>
          <th>Unit Price</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <!-- Rows will be dynamically generated -->
      </tbody>
    </table>

    <div class="quotation-total">
      <p><strong>Subtotal:</strong> <span id="quote-subtotal">₱0.00</span></p>
      <p><strong>Shipping Fee:</strong> <span id="quote-shipping">₱0.00</span></p>
      <p><strong>Handling Fee:</strong> <span id="quote-handling">₱0.00</span></p>
      <p><strong>Grand Total:</strong> <span id="quote-grandtotal">₱0.00</span></p>
    </div>
  </div>
</div>