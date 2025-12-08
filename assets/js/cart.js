$(document).ready(function () {

    // =============================
    // CALCULATE SELECTED ITEMS SUMMARY
    // =============================
    function calculateSelectedSummary() {
        let selectedItems = 0;
        let subtotal = 0;

        $('.item-checkbox:checked').each(function() {
            const row = $(this).closest('tr');
            const price = parseFloat($(this).data('price')) || 0;
            const quantity = parseInt(row.find('.qty-input').val()) || 0;
            
            selectedItems += quantity;
            subtotal += price * quantity;
        });

        // Calculate shipping and handling to match server-side calculation
        // Shipping: 25 per item, Handling: 10 per item
        const shipping = selectedItems * 25;
        const handling = selectedItems * 10;
        const total = subtotal + shipping + handling;

        // Update summary display
        $('#summary-items').text(selectedItems);
        $('#summary-subtotal').text(subtotal.toFixed(2));
        $('#summary-shipping').text(shipping.toFixed(2));
        $('#summary-handling').text(handling.toFixed(2));
        $('#summary-total').text(total.toFixed(2));

        // Update selected count in checkout button
        const selectedCount = $('.item-checkbox:checked').length;
        $('#selected-count').text(selectedCount);

        // Enable/disable checkout button based on selection
        if (selectedCount === 0) {
            $('#checkout-selected-btn').prop('disabled', true).addClass('disabled');
        } else {
            $('#checkout-selected-btn').prop('disabled', false).removeClass('disabled');
        }

        return { selectedItems, subtotal, shipping, handling, total };
    }

    // =============================
    // LOAD SUMMARY FROM SERVER
    // =============================
    function loadSummary() {
        $.ajax({
            url: BASE_URL + "CartCon/get_cart_ajax",
            method: "GET",
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    // Update each row total in cart table and checkbox data
                    res.items.forEach(item => {
                        const row = $(`.qty-input[data-id='${item.cart_id}']`).closest('tr');
                        row.find('.item-total').text(`₱${item.total.toFixed(2)}`);
                        row.find('.qty-input').val(item.quantity);
                        
                        // Update checkbox data attributes
                        const checkbox = row.find('.item-checkbox');
                        checkbox.data('quantity', item.quantity);
                    });

                    // Recalculate based on selected items
                    calculateSelectedSummary();
                }
            }
        });
    }

    // Initial load
    loadSummary();

    // =============================
    // SELECT ALL CHECKBOX
    // =============================
    $('#select-all-items').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.item-checkbox').prop('checked', isChecked);
        
        // Update row styling
        $('.cart-row').each(function() {
            if (isChecked) {
                $(this).removeClass('unselected');
            } else {
                $(this).addClass('unselected');
            }
        });

        calculateSelectedSummary();
    });

    // =============================
    // INDIVIDUAL ITEM CHECKBOX
    // =============================
    $(document).on('change', '.item-checkbox', function() {
        const row = $(this).closest('tr');
        
        if ($(this).prop('checked')) {
            row.removeClass('unselected');
        } else {
            row.addClass('unselected');
        }

        // Update "select all" checkbox state
        const totalItems = $('.item-checkbox').length;
        const checkedItems = $('.item-checkbox:checked').length;
        
        if (checkedItems === 0) {
            $('#select-all-items').prop('checked', false).prop('indeterminate', false);
        } else if (checkedItems === totalItems) {
            $('#select-all-items').prop('checked', true).prop('indeterminate', false);
        } else {
            $('#select-all-items').prop('checked', false).prop('indeterminate', true);
        }

        calculateSelectedSummary();
    });

    // =============================
    // CHECKOUT SELECTED ITEMS
    // =============================
    $('#checkout-selected-btn').on('click', function() {
        const selectedIds = [];
        $('.item-checkbox:checked').each(function() {
            selectedIds.push($(this).data('id'));
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one item to checkout.');
            return;
        }

        // Navigate to payment with selected cart IDs
        window.location.href = BASE_URL + 'payment?selected=' + selectedIds.join(',');
    });

    // =============================
    // DELETE ITEM (Real-time AJAX)
    // =============================
    $(document).on('click', '.remove-btn', function () {
        const btn = $(this);
        const row = btn.closest('tr');
        const cart_id = btn.data('id');
        const product_id = btn.data('product-id');
        const customization_id = btn.data('customization-id');
        const productName = row.find('td').eq(2).text();

        // Confirm before removing
        if (!confirm(`Remove "${productName}" from cart?`)) {
            return;
        }

        // Disable button to prevent double clicks
        btn.prop('disabled', true).css('opacity', '0.5');

        // Prepare data to send - always include product_id and customization_id as backup
        // This helps with the alternative deletion method when cart_id is 0 or invalid
        const ajaxData = {
            cart_id: cart_id || 0  // Send 0 if cart_id is null/undefined
        };
        
        // Always include product_id for fallback deletion method
        if (product_id) {
            ajaxData.product_id = product_id;
        }
        
        // Always include customization_id - send empty string if null/undefined (server handles empty as NULL)
        // jQuery .data() returns empty string for empty attributes, undefined if attribute missing
        // Convert undefined to empty string so server knows it's a non-customized item
        ajaxData.customization_id = (customization_id !== undefined && customization_id !== null && customization_id !== '') 
            ? customization_id 
            : '';

        console.log('Attempting to remove cart item:', {
            cart_id: cart_id,
            product_id: product_id,
            customization_id: customization_id,
            product_name: productName,
            url: BASE_URL + "CartCon/remove_ajax",
            data: ajaxData
        });

        $.ajax({
            url: BASE_URL + "CartCon/remove_ajax",
            method: "POST",
            data: ajaxData,
            dataType: "json",
            success: function (res) {
                console.log('Remove item response:', res);
                
                if (res.status === 'success') {
                    console.log('Item removed successfully');
                    // Animate row removal
                    row.css({
                        'transition': 'all 0.3s ease',
                        'opacity': '0',
                        'transform': 'translateX(-20px)'
                    });
                    
                    setTimeout(function() {
                        row.remove();
                        
                        // Check if cart is now empty
                        if ($('#cart-body tr.cart-row').length === 0) {
                            $('#cart-body').html('<tr><td colspan="8">Your cart is empty.</td></tr>');
                        }
                        
                        // Update summary and recalculate selected
                        calculateSelectedSummary();
                        
                        // Update select all checkbox state
                        const totalItems = $('.item-checkbox').length;
                        const checkedItems = $('.item-checkbox:checked').length;
                        if (totalItems === 0 || checkedItems === totalItems) {
                            $('#select-all-items').prop('indeterminate', false);
                        }
                    }, 300);
                } else {
                    console.error('Failed to remove item:', res);
                    const errorMsg = res.message || 'Failed to remove item. Please try again.';
                    console.error('Error message:', errorMsg);
                    alert(errorMsg);
                    btn.prop('disabled', false).css('opacity', '1');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error removing cart item:', {
                    status: status,
                    error: error,
                    statusCode: xhr.status,
                    responseText: xhr.responseText,
                    readyState: xhr.readyState
                });
                
                // Try to parse error response if it's JSON
                let errorMessage = 'An error occurred. Please try again.';
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                        console.error('Parsed error message:', errorMessage);
                    }
                } catch (e) {
                    console.error('Could not parse error response as JSON:', e);
                    console.error('Raw response:', xhr.responseText);
                }
                
                alert(errorMessage);
                btn.prop('disabled', false).css('opacity', '1');
            }
        });
    });

    // =============================
    // UPDATE QUANTITY
    // =============================
$(document).on('change', '.qty-input', function () { 
    const input = $(this);
    const cart_id = input.data('id');
    let quantity = parseInt(input.val());

    if (quantity < 1) input.val(1);

    $.post(BASE_URL + "CartCon/update_qty_ajax", 
        { cart_id: cart_id, quantity: input.val() }, 
        function (res) {

        if (res.status === 'success') {
            const row = input.closest('tr');

            // Get unit price from checkbox data attribute
            const checkbox = row.find('.item-checkbox');
            const unitPrice = parseFloat(checkbox.data('price')) || 0;
            const newTotal = (unitPrice * quantity).toFixed(2);

            row.find('.item-total').text('₱' + newTotal);
            
            // Update checkbox quantity data
            checkbox.data('quantity', quantity);

            // Recalculate selected summary
            calculateSelectedSummary();
        }
    }, 'json');
});


    // =============================
    // CLEAR CART (Real-time AJAX)
    // =============================
    $('#clear-cart').click(function () {
        const cartRows = $('tr.cart-row');
        
        if (cartRows.length === 0) {
            alert('Your cart is already empty.');
            return;
        }

        if (!confirm('Are you sure you want to clear your entire cart?')) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).text('Clearing...');

        $.ajax({
            url: BASE_URL + "CartCon/clear_ajax",
            method: "POST",
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    // Animate all rows removal
                    cartRows.css({
                        'transition': 'all 0.3s ease',
                        'opacity': '0',
                        'transform': 'translateX(-20px)'
                    });

                    setTimeout(function() {
                        cartRows.remove();
                        $('#cart-body').html('<tr><td colspan="8">Your cart is empty.</td></tr>');
                        calculateSelectedSummary();
                        btn.prop('disabled', false).text('Clear Shopping Cart');
                    }, 300);
                } else {
                    alert('Failed to clear cart. Please try again.');
                    btn.prop('disabled', false).text('Clear Shopping Cart');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
                btn.prop('disabled', false).text('Clear Shopping Cart');
            }
        });
    });

    // =============================
    // QUANTITY +/- BUTTONS
    // =============================
    $(document).on('click', '.qty-minus', function () {
        const cart_id = $(this).data('id');
        const input = $(`.qty-input[data-id='${cart_id}']`);
        let currentVal = parseInt(input.val());
        
        if (currentVal > 1) {
            input.val(currentVal - 1).trigger('change');
        }
    });

    $(document).on('click', '.qty-plus', function () {
        const cart_id = $(this).data('id');
        const input = $(`.qty-input[data-id='${cart_id}']`);
        let currentVal = parseInt(input.val());
        
        input.val(currentVal + 1).trigger('change');
    });

    // =============================
    // QUOTATION MODAL
    // =============================
    function openModal() {
        $('#quotationModal').addClass('show');
        $('body').css('overflow', 'hidden');
    }

    function closeModal() {
        $('#quotationModal').removeClass('show');
        $('body').css('overflow', '');
    }

    $('#openModal').click(function () {
        // Get selected cart IDs - convert to integers for consistent comparison
        const selectedIds = [];
        $('.item-checkbox:checked').each(function() {
            selectedIds.push(parseInt($(this).data('id')));
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one item to generate quotation.');
            return;
        }

        $.getJSON(BASE_URL + "CartCon/get_cart_ajax", function (res) {
            if (res.status === 'success') {
                const tbody = $('#quotation-items');
                const designsContainer = $('#quotation-designs');
                const designsSection = $('#designs-section');
                
                tbody.empty();
                designsContainer.empty();

                let subtotal = 0;
                let hasDesigns = false;
                let designIndex = 1;
                let itemCount = 0;

                // Only show selected items
                res.items.forEach((item, index) => {
                    // Check if this item is selected - convert cart_id to int for comparison
                    if (!selectedIds.includes(parseInt(item.cart_id))) {
                        return; // Skip unselected items
                    }

                    const unit_price = Number(item.unit_price) || 0;
                    const total = Number(item.total) || 0;
                    const customization = item.customization || 'Standard';

                    const row = `<tr style="animation-delay: ${itemCount * 0.05}s">
                        <td>${item.description}</td>
                        <td class="customization-cell">${customization}</td>
                        <td>${item.quantity}</td>
                        <td>₱${unit_price.toFixed(2)}</td>
                        <td>₱${total.toFixed(2)}</td>
                    </tr>`;
                    tbody.append(row);
                    subtotal += total;
                    itemCount++;

                    // Add design image if available
                    if (item.has_design && item.design_ref) {
                        hasDesigns = true;
                        const designCard = `
                            <div class="design-card">
                                <div class="design-card-header">
                                    <span class="design-number">Design #${designIndex}</span>
                                    <span class="design-product">${item.description}</span>
                                </div>
                                <div class="design-card-image">
                                    <img src="${item.design_ref}" alt="Custom Design ${designIndex}">
                                </div>
                                <div class="design-card-specs">
                                    ${customization}
                                </div>
                            </div>
                        `;
                        designsContainer.append(designCard);
                        designIndex++;
                    }
                });

                // Show/hide designs section based on whether there are designs
                if (hasDesigns) {
                    designsSection.show();
                } else {
                    designsSection.hide();
                }

                // Calculate fees based on selected items only (matching server-side calculation)
                // Shipping: 25 per item, Handling: 10 per item
                const shippingFee = itemCount * 25;
                const handlingFee = itemCount * 10;
                const grandTotal = subtotal + shippingFee + handlingFee;

                // Format date nicely
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                const formattedDate = new Date().toLocaleDateString('en-US', options);
                
                $('#quotation-date').text(formattedDate);
                $('#quote-subtotal').text(`₱${subtotal.toFixed(2)}`);
                $('#quote-shipping').text(`₱${shippingFee.toFixed(2)}`);
                $('#quote-handling').text(`₱${handlingFee.toFixed(2)}`);
                $('#quote-grandtotal').text(`₱${grandTotal.toFixed(2)}`);

                openModal();
            }
        });
    });

    // Close modal handlers
    $('#closeModal, #closeModalBtn').click(function () {
        closeModal();
    });

    // Close on overlay click
    $(document).on('click', '.modal-overlay', function () {
        closeModal();
    });

    // Close on ESC key
    $(document).keydown(function (e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    // Print quotation
    $('#printQuotation').click(function () {
        window.print();
    });
});
