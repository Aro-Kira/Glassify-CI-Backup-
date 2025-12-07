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
        <div class="step completed">Cart</div>
        <div class="divider"></div>
        <div class="step active">Payment</div>
        <div class="divider"></div>
        <div class="step">Approval</div>
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
            <form id="profileForm" method="POST" action="<?= base_url('usercon/update_profile'); ?>">
                <!-- User Info -->
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstname" value="<?= htmlspecialchars($user->First_Name) ?>"
                            placeholder="Enter your first name" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lastname" value="<?= htmlspecialchars($user->Last_Name) ?>"
                            placeholder="Enter your last name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user->Email) ?>"
                            placeholder="Enter your email address" required>
                    </div>
                    <div class="form-group">
                        <label>Phone number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($user->PhoneNum) ?>" maxlength="11"
                            placeholder="Enter your phone number" required>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="info-title">
                    <h3>Shipping Address</h3>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Street Name/Number</label>
                        <input type="text" name="street" id="street"
                            value="<?= htmlspecialchars($addresses['Shipping']->AddressLine ?? '') ?>"
                            placeholder="Enter street name or number" required>
                    </div>
                    <div class="form-group">
                        <label>Barangay</label>
                        <input type="text" name="barangay" id="barangay"
                            value=""
                            placeholder="Enter barangay" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City/Municipality</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($addresses['Shipping']->City) ?>"
                            placeholder="Enter your city or municipality" required>
                    </div>
                    <div class="form-group">
                        <label>Province</label>
                        <input type="text" name="province"
                            value="<?= htmlspecialchars($addresses['Shipping']->Province) ?>"
                            placeholder="Enter your province" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country"
                            value="<?= htmlspecialchars($addresses['Shipping']->Country) ?>"
                            placeholder="Enter your country" required>
                    </div>
                    <div class="form-group">
                        <label>Zip code</label>
                        <input type="text" name="zipcode"
                            value="<?= htmlspecialchars($addresses['Shipping']->ZipCode) ?>"
                            placeholder="Enter your zip code" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Note</label>
                        <input type="text" name="note" value="<?= htmlspecialchars($addresses['Shipping']->Note) ?>"
                            placeholder="Add a note (optional)">
                    </div>
                </div>

                <!-- Preferred Date of Installation -->
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Preferred Date of Installation</label>
                        <input type="date" name="preferred_installation_date" id="preferred_installation_date"
                            placeholder="Select preferred installation date" required>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="terms"> <input type="checkbox" id="same-billing"> <label for="same-billing"> Make billing address same as shipping
                       
                    </label> </div>

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
                <input type="checkbox" id="accept-terms">
                <label for="accept-terms">
                    I have read and agree to Glassify's
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
            <p><strong>Name:</strong> <span id="quote-customer-name">-</span></p>
            <p><strong>Address:</strong> <span id="quote-customer-address">-</span></p>
            <p><strong>Email:</strong> <span id="quote-customer-email">-</span></p>
            <p><strong>Phone:</strong> <span id="quote-customer-phone">-</span></p>
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

    // Function to get customer info from form
    function getCustomerInfo() {
        const firstname = document.querySelector('input[name="firstname"]')?.value || '';
        const lastname = document.querySelector('input[name="lastname"]')?.value || '';
        const email = document.querySelector('input[name="email"]')?.value || '';
        const phone = document.querySelector('input[name="phone"]')?.value || '';
        const street = document.querySelector('input[name="street"]')?.value || '';
        const barangay = document.querySelector('input[name="barangay"]')?.value || '';
        const city = document.querySelector('input[name="city"]')?.value || '';
        const province = document.querySelector('input[name="province"]')?.value || '';
        const country = document.querySelector('input[name="country"]')?.value || '';
        const zipcode = document.querySelector('input[name="zipcode"]')?.value || '';
        
        // Combine street and barangay
        const addressLine = [street, barangay].filter(part => part.trim() !== '').join(', ');
        
        // Build full address
        const addressParts = [addressLine, city, province, country, zipcode].filter(part => part.trim() !== '');
        const fullAddress = addressParts.length > 0 ? addressParts.join(', ') : '-';
        
        return {
            name: (firstname + ' ' + lastname).trim() || '-',
            address: fullAddress,
            email: email || '-',
            phone: phone || '-'
        };
    }

    // Generate Quotation button - fetch data from database
    openBtn.onclick = function() {
        // Get customer info from form
        const customerInfo = getCustomerInfo();
        
        // Update customer info in modal
        document.getElementById('quote-customer-name').textContent = customerInfo.name;
        document.getElementById('quote-customer-address').textContent = customerInfo.address;
        document.getElementById('quote-customer-email').textContent = customerInfo.email;
        document.getElementById('quote-customer-phone').textContent = customerInfo.phone;
        
        // Set current date
        document.getElementById('quotation-date').textContent = new Date().toLocaleDateString();
        
        // Fetch cart data from database
        fetch(BASE_URL + "CartCon/get_cart_ajax")
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    const tbody = document.querySelector('#quotationModal tbody');
                    tbody.innerHTML = ''; // Clear existing rows
                    
                    let subtotal = 0;
                    
                    // Populate table with cart items
                    res.items.forEach(item => {
                        const unit_price = Number(item.unit_price) || 0;
                        const total = Number(item.total) || 0;
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.description || 'N/A'}</td>
                            <td>${item.quantity || 0}</td>
                            <td>₱${unit_price.toFixed(2)}</td>
                            <td>₱${total.toFixed(2)}</td>
                        `;
                        tbody.appendChild(row);
                        subtotal += total;
                    });
                    
                    // Update totals
                    const shippingFee = res.summary.shipping || 0;
                    const handlingFee = res.summary.handling || 0;
                    const grandTotal = subtotal + shippingFee + handlingFee;
                    
                    document.getElementById('quote-subtotal').textContent = `₱${subtotal.toFixed(2)}`;
                    document.getElementById('quote-shipping').textContent = `₱${shippingFee.toFixed(2)}`;
                    document.getElementById('quote-handling').textContent = `₱${handlingFee.toFixed(2)}`;
                    document.getElementById('quote-grandtotal').textContent = `₱${grandTotal.toFixed(2)}`;
                    
                    // Show modal
                    modal.style.display = "block";
                } else {
                    alert('Failed to load cart data. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error fetching cart data:', error);
                alert('Error loading quotation. Please try again.');
            });
    };
    
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

    // === Set minimum date for preferred installation date ===
    const preferredDateInput = document.getElementById("preferred_installation_date");
    if (preferredDateInput) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        preferredDateInput.setAttribute('min', today);
    }

    // === Place Order button logic ===
    document.getElementById("placeOrderBtn").addEventListener("click", function () {
        try {
            const btn = this;
            const ewallet = document.getElementById("ewallet-radio").checked;
            const cod = document.getElementById("COD-radio").checked;
            const termsCheckbox = document.getElementById('accept-terms');
            const termsAccepted = termsCheckbox ? termsCheckbox.checked : false;

            console.log('Place Order clicked - E-Wallet:', ewallet, 'COD:', cod, 'Terms:', termsAccepted);

            // Validate payment method
            if (!ewallet && !cod) {
                alert("Please select a payment method before placing order.");
                return;
            }

            // Validate terms acceptance
            if (!termsAccepted) {
                alert("Please accept the Terms and Conditions to proceed.");
                return;
            }

            // Get form data - using correct field names from the form
            const street = document.querySelector('input[name="street"]')?.value || '';
            const barangay = document.querySelector('input[name="barangay"]')?.value || '';
            // Combine street and barangay into address
            const address = [street, barangay].filter(part => part.trim() !== '').join(', ');
            
            const formData = {
                first_name: document.querySelector('input[name="firstname"]')?.value || '',
                last_name: document.querySelector('input[name="lastname"]')?.value || '',
                email: document.querySelector('input[name="email"]')?.value || '',
                phone: document.querySelector('input[name="phone"]')?.value || '',
                address: address, // Combined street and barangay
                street: street,
                barangay: barangay,
                city: document.querySelector('input[name="city"]')?.value || '',
                province: document.querySelector('input[name="province"]')?.value || '',
                country: document.querySelector('input[name="country"]')?.value || '',
                zipcode: document.querySelector('input[name="zipcode"]')?.value || '',
                note: document.querySelector('input[name="note"]')?.value || '',
                preferred_installation_date: document.querySelector('input[name="preferred_installation_date"]')?.value || '',
                payment_method: ewallet ? 'ewallet' : 'cod',
                total_amount: document.getElementById('summary-total')?.textContent.replace(/[₱,]/g, '') || '0'
            };

            console.log('Form data:', formData);

            // Validate required fields
            if (!formData.first_name || !formData.last_name || !formData.email || !formData.phone || 
                !street || !barangay || !formData.city || !formData.province || !formData.country || !formData.zipcode) {
                alert("Please fill in all required shipping information fields (including street name and barangay).");
                return;
            }

            // Validate preferred installation date
            if (!formData.preferred_installation_date) {
                alert("Please select a preferred date of installation.");
                return;
            }

            // Store form data in sessionStorage to pass to next page
            try {
                sessionStorage.setItem('checkout_data', JSON.stringify(formData));
            } catch (e) {
                console.error('Error saving to sessionStorage:', e);
            }

            console.log('Redirecting... E-Wallet:', ewallet, 'COD:', cod);

            if (ewallet) {
                const payingUrl = "<?php echo base_url('paying'); ?>";
                console.log('Redirecting to:', payingUrl);
                window.location.href = payingUrl; // redirect to e-wallet page
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
        } catch (error) {
            console.error('Error in Place Order:', error);
            alert('An error occurred. Please check the console (F12) for details and try again.');
        }
    });
    

</script>