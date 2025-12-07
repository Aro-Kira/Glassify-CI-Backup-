$(document).ready(function () {

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
                    const summary = res.summary;

                    // Update cart summary
                    $('#summary-items').text(summary.items);
                    $('#summary-subtotal').text(summary.subtotal.toFixed(2));
                    $('#summary-shipping').text(summary.shipping.toFixed(2));
                    $('#summary-handling').text(summary.handling.toFixed(2));
                    $('#summary-total').text(summary.total.toFixed(2));

                    // Update each row total in cart table
                    res.items.forEach(item => {
                        const row = $(`.qty-input[data-id='${item.cart_id}']`).closest('tr');
                        row.find('.item-total').text(`₱${item.total.toFixed(2)}`);
                        row.find('.qty-input').val(item.quantity);
                    });
                }
            }
        });
    }

    // Initial load
    loadSummary();

    // =============================
    // DELETE ITEM
    // =============================
    $(document).on('click', '.remove-btn', function () {
        const btn = $(this);
        const cart_id = btn.data('id');
        const product_id = btn.data('product-id');
        const customization_id = btn.data('customization-id');

        // Prepare data - include fallback identifiers for Cart_ID = 0
        const postData = { cart_id: cart_id };
        if (cart_id <= 0 || !cart_id) {
            // If Cart_ID is invalid, use alternative identifiers
            postData.product_id = product_id;
            if (customization_id) {
                postData.customization_id = customization_id;
            }
        }

        $.ajax({
            url: BASE_URL + "CartCon/remove_ajax",
            type: "POST",
            data: postData,
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    btn.closest('tr').remove();
                    loadSummary(); // always reload summary from server
                } else {
                    alert("Error: " + (res.message || 'Failed to remove item'));
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = "Server error. Try again.";
                if (xhr.responseText) {
                    try {
                        let errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMessage = "Error: " + errorResponse.message;
                        }
                    } catch (e) {
                        console.error("AJAX Error:", {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error
                        });
                    }
                }
                alert(errorMessage);
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

            loadSummary(); // refresh totals (items, subtotal, shipping)

            // ----------------------------------------------
            // ✅ Update item total (Price × Quantity)
            // ----------------------------------------------
            const row = input.closest('tr');

            // get unit price text from 4th TD (₱123.45)
            const unitPriceText = row.find('td').eq(3).text().replace('₱', '').replace(',', '').trim();

            const unitPrice = parseFloat(unitPriceText);
            const newTotal = (unitPrice * quantity).toFixed(2);

            row.find('.item-total').text('₱' + newTotal);
        }
    }, 'json');
});


    // =============================
    // CLEAR CART
    // =============================
    $('#clear-cart').click(function () {
        $.post(BASE_URL + "CartCon/clear_ajax", {}, function (res) {
            if (res.status === 'success') {
                $('tr.cart-row').remove();
                loadSummary();
            }
        }, 'json');
    });

    // =============================
    // QUOTATION MODAL
    // =============================
    $('#openModal').click(function () {
        $.getJSON(BASE_URL + "CartCon/get_cart_ajax", function (res) {
            if (res.status === 'success') {
                const tbody = $('#quotationModal tbody');
                tbody.empty();

                let subtotal = 0;
                res.items.forEach(item => {
                    const unit_price = Number(item.unit_price) || 0;
                    const total = Number(item.total) || 0;

                    const row = `<tr>
        <td>${item.description}</td>
        <td>${item.quantity}</td>
        <td>₱${unit_price.toFixed(2)}</td>
        <td>₱${total.toFixed(2)}</td>
    </tr>`;
                    tbody.append(row);
                    subtotal += total;
                });


                const shippingFee = res.summary.shipping;
                const handlingFee = res.summary.handling;
                const grandTotal = subtotal + shippingFee + handlingFee;

                $('#quotation-date').text(new Date().toLocaleDateString());
                $('#quote-subtotal').text(`₱${subtotal.toFixed(2)}`);
                $('#quote-shipping').text(shippingFee.toFixed(2));
                $('#quote-handling').text(handlingFee.toFixed(2));
                $('#quote-grandtotal').text(`₱${grandTotal.toFixed(2)}`);

                $('#quotationModal').show();
            }
        });
    });

    $('#closeModal').click(function () {
        $('#quotationModal').hide();
    });

    $(window).click(function (e) {
        if ($(e.target).is('#quotationModal')) {
            $('#quotationModal').hide();
        }
    });
});
