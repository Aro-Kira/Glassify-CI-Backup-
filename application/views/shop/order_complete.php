<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/order_complete.css'); ?>">

<script>
    const BASE_URL = "<?= base_url(); ?>";
</script>

<div class="checkout-header">
    <!-- Back button -->
    <div class="back-btn">
        <a href="<?= base_url('products'); ?>">
            <img src="<?= base_url('assets/images/img-page/back_button.png');?>" alt="Back Icon">
            <span>Continue Shopping</span>
        </a>
    </div>

    <!-- Progress nav -->
    <div class="progress-nav">
        <div class="step completed">Cart</div>
        <div class="divider"></div>
        <div class="step completed">Payment</div>
        <div class="divider"></div>
        <div class="step active">Complete</div>
    </div>
</div>

<main>
    <!-- Confirmation -->
    <div class="confirmation">
        <div class="checkmark"><img src="<?= base_url('assets/images/img-page/checked-complete.png');?>" alt="check-icon"></div>
        <h2>Order is complete.</h2>
        <p>Thank you!</p>
    </div>

    <!-- Order Info -->
    <section class="order-info">
        <div class="info-box">
            <p><strong>Order ID:</strong> <?= isset($order) && $order ? str_pad($order->OrderID, 10, '0', STR_PAD_LEFT) : 'N/A' ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($payment_method ?? 'Cash on Delivery') ?></p>
            <p><strong>Transaction ID:</strong> TXN<?= isset($order) && $order ? date('Ymd', strtotime($order->OrderDate)) . str_pad($order->OrderID, 6, '0', STR_PAD_LEFT) : 'N/A' ?></p>
            <p><strong>Order Date:</strong> <?= isset($order) && $order ? date('F d, Y', strtotime($order->OrderDate)) : date('F d, Y') ?></p>
            <p><strong>Status:</strong> <span class="status-badge"><?= isset($order) && $order ? $order->Status : 'Pending' ?></span></p>
        </div>
    </section>

    <!-- Your Order -->
    <section class="your-order">
        <h3>Your Order</h3>

        <div class="order-content">
            <!-- Left Side -->
            <div class="left-col">
                <!-- Products -->
                <div class="products">
                    <div class="products-header">
                        <h4></h4>
                        <h4>Product Name</h4>
                        <h4>Details</h4>
                        <h4>Qty</h4>
                        <h4>Price</h4>
                    </div>
                    <table class="product-table">
                        <tbody>
                            <?php if (!empty($order_items)): ?>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?= base_url('uploads/products/' . ($item->ImageUrl ?? 'default.jpg')) ?>" alt="<?= htmlspecialchars($item->ProductName ?? 'Product') ?>">
                                    </td>
                                    <td><?= htmlspecialchars($item->ProductName ?? 'Product') ?></td>
                                    <td class="item-details">
                                        <?php if ($item->Dimensions): ?>
                                            <small>Size: <?= htmlspecialchars($item->Dimensions) ?></small><br>
                                        <?php endif; ?>
                                        <?php if ($item->GlassType): ?>
                                            <small>Type: <?= htmlspecialchars($item->GlassType) ?></small><br>
                                        <?php endif; ?>
                                        <?php if ($item->GlassThickness): ?>
                                            <small>Thickness: <?= htmlspecialchars($item->GlassThickness) ?></small><br>
                                        <?php endif; ?>
                                        <?php if ($item->FrameType): ?>
                                            <small>Frame: <?= htmlspecialchars($item->FrameType) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $item->Quantity ?></td>
                                    <td class="price">₱<?= number_format($item->EstimatePrice * $item->Quantity, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">No items found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Order Summary -->
                <div class="summary-box">
                    <h4>Order Summary</h4>
                    <p><span>Items:</span> <span id="summary-items"><?= $summary['items'] ?? 0 ?></span></p>
                    <p><span>Subtotal:</span> ₱<span id="summary-subtotal"><?= number_format($summary['subtotal'] ?? 0, 2) ?></span></p>
                    <p><span>Shipping Fee:</span> ₱<span id="summary-shipping"><?= number_format($summary['shipping'] ?? 0, 2) ?></span></p>
                    <p><span>Handling Fee:</span> ₱<span id="summary-handling"><?= number_format($summary['handling'] ?? 0, 2) ?></span></p>
                    <div class="divider"></div>
                    <p class="total"><span>Total:</span> ₱<span id="summary-total"><?= number_format($summary['total'] ?? 0, 2) ?></span></p>
                </div>
            </div>

            <!-- Right Side -->
            <div class="right-col">
                <!-- Shipping Address -->
                <div class="address-box">
                    <h4>Shipping Address</h4>
                    <?php if (isset($user) && $user): ?>
                        <p><strong><?= htmlspecialchars($user->First_Name . ' ' . $user->Last_Name) ?></strong></p>
                        <p><?= $user->PhoneNum ? '(+63) ' . htmlspecialchars($user->PhoneNum) : '' ?></p>
                    <?php endif; ?>
                    <?php if (isset($shipping_address) && $shipping_address): ?>
                        <p>
                            <?= htmlspecialchars($shipping_address->AddressLine ?? '') ?><br>
                            <?= htmlspecialchars($shipping_address->City ?? '') ?>, <?= htmlspecialchars($shipping_address->Province ?? '') ?><br>
                            <?= htmlspecialchars($shipping_address->Country ?? '') ?><br>
                            <?= htmlspecialchars($shipping_address->ZipCode ?? '') ?>
                        </p>
                    <?php elseif (isset($order) && $order && $order->DeliveryAddress): ?>
                        <p><?= nl2br(htmlspecialchars($order->DeliveryAddress)) ?></p>
                    <?php else: ?>
                        <p>No shipping address provided</p>
                    <?php endif; ?>
                </div>

                <!-- Billing Address -->
                <div class="address-box">
                    <h4>Billing Address</h4>
                    <?php if (isset($billing_address) && $billing_address && $billing_address->AddressLine): ?>
                        <p>
                            <?= htmlspecialchars($billing_address->AddressLine) ?><br>
                            <?= htmlspecialchars($billing_address->City ?? '') ?>, <?= htmlspecialchars($billing_address->Province ?? '') ?><br>
                            <?= htmlspecialchars($billing_address->Country ?? '') ?><br>
                            <?= htmlspecialchars($billing_address->ZipCode ?? '') ?>
                        </p>
                    <?php else: ?>
                        <p>Same as shipping address</p>
                    <?php endif; ?>
                </div>

                <!-- Invoice Button -->
                <div class="invoice-btn">
                    <button id="downloadInvoiceBtn">⬇ Download Invoice</button>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Import jsPDF & AutoTable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

<script>
// Order data from PHP
const orderData = {
    orderId: "<?= isset($order) && $order ? str_pad($order->OrderID, 10, '0', STR_PAD_LEFT) : 'N/A' ?>",
    transactionId: "TXN<?= isset($order) && $order ? date('Ymd', strtotime($order->OrderDate)) . str_pad($order->OrderID, 6, '0', STR_PAD_LEFT) : date('Ymd') . '000000' ?>",
    orderDate: "<?= isset($order) && $order ? date('F d, Y', strtotime($order->OrderDate)) : date('F d, Y') ?>",
    paymentMethod: "<?= htmlspecialchars($payment_method ?? 'Cash on Delivery') ?>",
    status: "<?= isset($order) && $order ? $order->Status : 'Pending' ?>",
    customer: {
        name: "<?= isset($user) ? htmlspecialchars($user->First_Name . ' ' . $user->Last_Name) : 'Customer' ?>",
        email: "<?= isset($user) ? htmlspecialchars($user->Email ?? '') : '' ?>",
        phone: "<?= isset($user) ? htmlspecialchars($user->PhoneNum ?? '') : '' ?>",
        address: "<?= isset($shipping_address) && $shipping_address ? htmlspecialchars($shipping_address->AddressLine . ', ' . $shipping_address->City . ', ' . $shipping_address->Province . ', ' . $shipping_address->Country . ' ' . $shipping_address->ZipCode) : (isset($order) && $order ? htmlspecialchars($order->DeliveryAddress) : '') ?>"
    },
    items: [
        <?php if (!empty($order_items)): ?>
            <?php foreach ($order_items as $index => $item): ?>
            {
                name: "<?= htmlspecialchars($item->ProductName ?? 'Product') ?>",
                dimensions: "<?= htmlspecialchars($item->Dimensions ?? '-') ?>",
                glassType: "<?= htmlspecialchars($item->GlassType ?? '-') ?>",
                thickness: "<?= htmlspecialchars($item->GlassThickness ?? '-') ?>",
                edgeWork: "<?= htmlspecialchars($item->EdgeWork ?? '-') ?>",
                frameType: "<?= htmlspecialchars($item->FrameType ?? '-') ?>",
                engraving: "<?= htmlspecialchars($item->Engraving ?? '-') ?>",
                quantity: <?= $item->Quantity ?>,
                unitPrice: <?= $item->EstimatePrice ?>,
                total: <?= $item->EstimatePrice * $item->Quantity ?>
            }<?= $index < count($order_items) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        <?php endif; ?>
    ],
    summary: {
        items: <?= $summary['items'] ?? 0 ?>,
        subtotal: <?= $summary['subtotal'] ?? 0 ?>,
        shipping: <?= $summary['shipping'] ?? 0 ?>,
        handling: <?= $summary['handling'] ?? 0 ?>,
        total: <?= $summary['total'] ?? 0 ?>
    }
};

// Generate Invoice PDF
document.getElementById('downloadInvoiceBtn').addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Colors
    const primaryColor = [46, 204, 113];
    const darkColor = [44, 62, 80];
    const lightGray = [236, 240, 241];
    
    // Header
    doc.setFillColor(...primaryColor);
    doc.rect(0, 0, 210, 40, 'F');
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(28);
    doc.setFont('helvetica', 'bold');
    doc.text('GLASSIFY', 20, 25);
    
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.text('INVOICE', 165, 20);
    doc.text('#' + orderData.transactionId, 150, 28);
    
    // Reset text color
    doc.setTextColor(...darkColor);
    
    // Invoice details
    doc.setFontSize(10);
    doc.text('Order ID: ' + orderData.orderId, 20, 50);
    doc.text('Date: ' + orderData.orderDate, 20, 56);
    doc.text('Payment Method: ' + orderData.paymentMethod, 120, 50);
    doc.text('Status: ' + orderData.status, 120, 56);
    
    // Customer Info Box
    doc.setFillColor(...lightGray);
    doc.roundedRect(20, 65, 170, 30, 3, 3, 'F');
    
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text('Bill To:', 25, 75);
    
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text(orderData.customer.name, 25, 83);
    doc.text(orderData.customer.email + ' | ' + orderData.customer.phone, 25, 90);
    
    // Items Table
    const tableData = orderData.items.map(item => [
        item.name,
        item.dimensions + ' | ' + item.glassType,
        item.quantity,
        '₱' + item.unitPrice.toLocaleString('en-PH', {minimumFractionDigits: 2}),
        '₱' + item.total.toLocaleString('en-PH', {minimumFractionDigits: 2})
    ]);
    
    doc.autoTable({
        startY: 105,
        head: [['Description', 'Specifications', 'Qty', 'Unit Price', 'Amount']],
        body: tableData,
        theme: 'striped',
        headStyles: {
            fillColor: primaryColor,
            textColor: 255,
            fontSize: 10,
            fontStyle: 'bold'
        },
        bodyStyles: {
            fontSize: 9,
            textColor: darkColor
        },
        columnStyles: {
            0: { cellWidth: 45 },
            1: { cellWidth: 55 },
            2: { cellWidth: 20, halign: 'center' },
            3: { cellWidth: 30, halign: 'right' },
            4: { cellWidth: 30, halign: 'right' }
        },
        margin: { left: 20, right: 20 }
    });
    
    // Summary
    const finalY = doc.lastAutoTable.finalY + 10;
    
    doc.setFillColor(...lightGray);
    doc.roundedRect(120, finalY, 70, 45, 3, 3, 'F');
    
    doc.setFontSize(10);
    doc.text('Subtotal:', 125, finalY + 10);
    doc.text('₱' + orderData.summary.subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2}), 180, finalY + 10, { align: 'right' });
    
    doc.text('Shipping Fee:', 125, finalY + 18);
    doc.text('₱' + orderData.summary.shipping.toLocaleString('en-PH', {minimumFractionDigits: 2}), 180, finalY + 18, { align: 'right' });
    
    doc.text('Handling Fee:', 125, finalY + 26);
    doc.text('₱' + orderData.summary.handling.toLocaleString('en-PH', {minimumFractionDigits: 2}), 180, finalY + 26, { align: 'right' });
    
    doc.setDrawColor(...primaryColor);
    doc.line(125, finalY + 32, 185, finalY + 32);
    
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text('TOTAL:', 125, finalY + 40);
    doc.text('₱' + orderData.summary.total.toLocaleString('en-PH', {minimumFractionDigits: 2}), 180, finalY + 40, { align: 'right' });
    
    // Payment Info
    const paymentY = finalY + 55;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.text('Payment Information:', 20, paymentY);
    
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9);
    doc.text('Payment Method: ' + orderData.paymentMethod, 20, paymentY + 8);
    doc.text('Transaction ID: ' + orderData.transactionId, 20, paymentY + 15);
    
    // Delivery Address
    doc.setFont('helvetica', 'bold');
    doc.text('Delivery Address:', 20, paymentY + 28);
    doc.setFont('helvetica', 'normal');
    const addressLines = orderData.customer.address.match(/.{1,60}/g) || [orderData.customer.address];
    addressLines.forEach((line, i) => {
        doc.text(line, 20, paymentY + 35 + (i * 5));
    });
    
    // Footer
    doc.setFillColor(...primaryColor);
    doc.rect(0, 280, 210, 17, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(9);
    doc.text('Thank you for your purchase! | www.glassify.com | contact@glassify.com', 105, 290, { align: 'center' });
    
    // Save
    doc.save('Glassify_Invoice_' + orderData.transactionId + '.pdf');
});
</script>
