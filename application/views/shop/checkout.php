<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/checkout_style.css'); ?>">

<script>
  const BASE_URL = "<?= base_url(); ?>";
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= base_url('assets/js/cart.js'); ?>"></script>


<div class="checkout-header">
    <!-- Back button -->
    <div class="back-btn">
        <a href="<?php echo base_url('addtocart'); ?>">
            <img src="<?php echo base_url('assets/images/img-page/back_button.png'); ?>" alt="Back Icon">
            <span>Back</span>
        </a>
    </div>

    <!-- Progress nav -->
    <div class="progress-nav">
        <div class="step">Cart</div>
        <div class="divider"></div>
        <div class="step active">Payment</div>
        <div class="divider"></div>
        <div class="step">Complete</div>
    </div>
</div>


<main>

    <!-- Title outside sections -->
    <div class="info-title">
        <h2>Shipping information</h2>
        <div class="title-divider"></div>
    </div>

    <!-- Content row -->
    <div class="info-container">
        <section class="info-section">
            <form>
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" title="First Name" placeholder="Enter your first name">
                    </div>
                    <div class="form-group">
                        <label>Last name</label>
                        <input type="text" name="last_name" title="Last Name" placeholder="Enter your last name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email address</label>
                        <input type="email" name="email" title="Email Address" placeholder="Enter your email address">
                    </div>
                    <div class="form-group">
                        <label>Phone number</label>
                        <input type="tel" name="phone" title="Phone Number" placeholder="Enter your phone number"
                            maxlength="11">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Address line</label>
                        <input type="text" name="address" title="Address Line" placeholder="Enter your address">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country" title="Country" placeholder="Enter your country">
                    </div>
                    <div class="form-group">
                        <label>Zip code</label>
                        <input type="text" name="zipcode" title="Zip code" placeholder="Enter your zip code">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Province</label>
                        <input type="text" name="province" title="Province" placeholder="Enter your province">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City/Municipality</label>
                        <input type="text" name="city" title="City or Municipality"
                            placeholder="Enter your city or municipality">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Note</label>
                        <input type="text" name="note" title="Note" placeholder="Add a note (optional)">
                    </div>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="same" name="same">
                    <label for="same">Make billing address same as shipping</label>
                </div>
            </form>
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
                    <button class="generate-btn" id="openModal">Generate Quotation</button>
                </div>

            </div>
            <div class="payment-section">
                <div class="payment-method-content">
                    <h3>Payment Methods</h3>
                    <p>
                        <img src="<?php echo base_url('assets/images/img-page/dollar.png'); ?>" alt="dollaricon">
                        <label for="ewallet-radio">E-Wallet</label>
                        <input type="radio" id="ewallet-radio" name="payment-method"
                            title="Select E-Wallet as payment method">
                    </p>
                    <p>
                        <img src="<?php echo base_url('assets/images/img-page/wallet.png'); ?>" alt="COD-icon">
                        <label for="COD-radio">Cash on Delivery</label>
                        <input type="radio" id="COD-radio" name="payment-method" title="Select COD as payment method">
                    </p>
                </div>

                <!-- Removed <a> and kept only button -->
                <button class="placeOrder-btn" id="placeOrderBtn">Place Order</button>
            </div>

            <div class="terms">
                <input type="checkbox" id="terms">
                <label for="terms">
                    I have read and agree to Glassify’s
                    <a href="<?php echo base_url('terms_order'); ?>">Terms and Conditions of Purchase</a>
                </label>
            </div>
        </section>
    </div>

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


<script>
    // === Modal logic ===
    const modal = document.getElementById("quotationModal");
    const openBtn = document.getElementById("openModal");
    const closeBtn = document.getElementById("closeModal");

    openBtn.onclick = () => modal.style.display = "block";
    closeBtn.onclick = () => modal.style.display = "none";
    window.onclick = (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };

    // === Phone number validation (digits only, max 11) ===
    const phoneInput = document.querySelector("input[name='phone']");
    if (phoneInput) {
        phoneInput.addEventListener("input", () => {
            phoneInput.value = phoneInput.value.replace(/\D/g, ""); // keep only digits
            if (phoneInput.value.length > 11) {
                phoneInput.value = phoneInput.value.slice(0, 11); // limit to 11
            }
        });
    }

    // === Auto add "@gmail.com" for email if missing ===
    const emailInput = document.querySelector("input[name='email']");
    if (emailInput) {
        emailInput.addEventListener("blur", () => {
            const val = emailInput.value.trim();
            if (val && !val.includes("@")) {
                emailInput.value = val + "@gmail.com";
            }
        });
    }

    // === Place Order button logic ===
    document.getElementById("placeOrderBtn").addEventListener("click", function () {
        const ewallet = document.getElementById("ewallet-radio").checked;
        const cod = document.getElementById("COD-radio").checked;

        if (!ewallet && !cod) {
            alert("Please select a payment method before placing order.");
            return;
        }

        // Get form data
        const formData = {
            first_name: document.querySelector('input[name="first_name"]').value,
            last_name: document.querySelector('input[name="last_name"]').value,
            email: document.querySelector('input[name="email"]').value,
            phone: document.querySelector('input[name="phone"]').value,
            address: document.querySelector('input[name="address"]').value,
            city: document.querySelector('input[name="city"]').value,
            province: document.querySelector('input[name="province"]').value,
            country: document.querySelector('input[name="country"]').value,
            zipcode: document.querySelector('input[name="zipcode"]').value,
            note: document.querySelector('input[name="note"]').value,
            payment_method: ewallet ? 'ewallet' : 'cod',
            total_amount: document.getElementById('summary-total').textContent.replace(/[₱,]/g, '')
        };

        // Store form data in sessionStorage to pass to next page
        sessionStorage.setItem('checkout_data', JSON.stringify(formData));

        if (ewallet) {
            window.location.href = "<?php echo base_url('paying'); ?>"; // redirect to e-wallet page
        } else if (cod) {
            // Submit form data to waiting_order
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "<?php echo base_url('waiting_order'); ?>";
            
            Object.keys(formData).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = formData[key];
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    });
</script>