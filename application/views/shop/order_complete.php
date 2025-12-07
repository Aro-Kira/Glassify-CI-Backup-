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
        <p>Order has been placed waiting for approval</p>
        <?php if (isset($payment_method) && $payment_method === 'E-Wallet'): ?>
            <p class="refund-notice">(If your order is disapproved you can have your refund)</p>
        <?php endif; ?>
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

    <!-- Products Table -->
    <section class="order-products">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Customization</th>
                    <th>Price</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($order_items)): ?>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <img src="<?= base_url('uploads/products/' . ($item->ImageUrl ?? 'default.jpg')) ?>" alt="<?= htmlspecialchars($item->ProductName ?? 'Product') ?>" class="cart-product-img">
                        </td>
                        <td><?= htmlspecialchars($item->ProductName ?? 'Product') ?></td>
                        <td class="customization-info">
                            <?php 
                            // Check if item has any customization data
                            $has_customization = !empty($item->Dimensions) || !empty($item->GlassType) || 
                                                !empty($item->GlassThickness) || !empty($item->GlassShape) || 
                                                !empty($item->EdgeWork) || !empty($item->FrameType) || 
                                                !empty($item->DesignRef);
                            ?>
                            <?php if ($has_customization): ?>
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
                                            <span class="custom-tag">Size: <?= htmlspecialchars($item->Dimensions) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item->GlassShape)): ?>
                                            <span class="custom-tag">Shape: <?= ucfirst(htmlspecialchars($item->GlassShape)) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item->GlassType)): ?>
                                            <span class="custom-tag">Type: <?= ucfirst(htmlspecialchars($item->GlassType)) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item->GlassThickness)): ?>
                                            <span class="custom-tag">Thickness: <?= htmlspecialchars($item->GlassThickness) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item->EdgeWork)): ?>
                                            <span class="custom-tag">Edge: <?= ucfirst(str_replace('-', ' ', htmlspecialchars($item->EdgeWork))) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item->FrameType)): ?>
                                            <span class="custom-tag">Frame: <?= ucfirst(htmlspecialchars($item->FrameType)) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item->Engraving) && $item->Engraving !== 'None'): ?>
                                            <span class="custom-tag engraving-tag">Engraving: <?= htmlspecialchars($item->Engraving) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="no-custom">Standard</span>
                            <?php endif; ?>
                        </td>
                        <td class="price">₱<?= number_format($item->EstimatePrice * $item->Quantity, 2) ?></td>
                        <td><?= $item->Quantity ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No items found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- Order Summary & Addresses -->
    <section class="order-summary">
        <!-- Left side - Order Summary -->
        <div class="summary-box">
            <h4>Order Summary</h4>
            <p><span>Items:</span> <span><?= $summary['items'] ?? 0 ?></span></p>
            <p><span>Subtotal:</span> <span>₱ <?= number_format($summary['subtotal'] ?? 0, 2) ?></span></p>
            <p><span>Shipping Fee:</span> <span>₱ <?= number_format($summary['shipping'] ?? 0, 2) ?></span></p>
            <p><span>Handling Fee:</span> <span>₱ <?= number_format($summary['handling'] ?? 0, 2) ?></span></p>
            <div class="summary-divider"></div>
            <h3><span>Total:</span> <span>₱ <?= number_format($summary['total'] ?? 0, 2) ?></span></h3>
        </div>

        <!-- Right side - Addresses -->
        <div class="addresses">
            <!-- Shipping Address -->
            <div class="address-box">
                <h4>Shipping Address</h4>
                <?php if (isset($user) && $user): ?>
                    <p><b><?= htmlspecialchars($user->First_Name . ' ' . $user->Last_Name) ?></b></p>
                    <p><?= $user->PhoneNum ? '(+63) ' . htmlspecialchars($user->PhoneNum) : '' ?></p>
                <?php endif; ?>
                <?php if (isset($shipping_address) && $shipping_address): ?>
                    <p><?= htmlspecialchars($shipping_address->AddressLine ?? '') ?></p>
                    <p><?= htmlspecialchars($shipping_address->City ?? '') ?>, <?= htmlspecialchars($shipping_address->Province ?? '') ?></p>
                    <p><?= htmlspecialchars($shipping_address->Country ?? '') ?></p>
                    <p><?= htmlspecialchars($shipping_address->ZipCode ?? '') ?></p>
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
                    <p><?= htmlspecialchars($billing_address->AddressLine) ?></p>
                    <p><?= htmlspecialchars($billing_address->City ?? '') ?>, <?= htmlspecialchars($billing_address->Province ?? '') ?></p>
                    <p><?= htmlspecialchars($billing_address->Country ?? '') ?></p>
                    <p><?= htmlspecialchars($billing_address->ZipCode ?? '') ?></p>
                <?php else: ?>
                    <p>Same as shipping address</p>
                <?php endif; ?>
            </div>

            <!-- Invoice Button -->
            <div class="invoice-btn">
                <button id="downloadInvoiceBtn">⬇ Download Invoice</button>
            </div>
        </div>
    </section>
</main>

<!-- Design Preview Modal -->
<div id="designModal" class="design-modal">
    <div class="design-modal-overlay" onclick="closeDesignModal()"></div>
    <div class="design-modal-content">
        <button class="design-modal-close" onclick="closeDesignModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="design-modal-header">
            <h3>Custom Design Layout</h3>
            <p>This design is included in your order</p>
        </div>
        <div class="design-modal-body">
            <img id="designModalImage" src="" alt="Custom Design">
        </div>
        <div class="design-modal-footer">
            <button class="btn-download-design" onclick="downloadDesignImage()">
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
                shape: "<?= htmlspecialchars($item->GlassShape ?? '-') ?>",
                edgeWork: "<?= htmlspecialchars($item->EdgeWork ?? '-') ?>",
                frameType: "<?= htmlspecialchars($item->FrameType ?? '-') ?>",
                engraving: "<?= htmlspecialchars($item->Engraving ?? '-') ?>",
                designRef: "<?= !empty($item->DesignRef) ? base_url($item->DesignRef) : '' ?>",
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

// Helper function to load image as base64
function loadImageAsBase64(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = function() {
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            resolve(canvas.toDataURL('image/png'));
        };
        img.onerror = reject;
        img.src = url;
    });
}

// Generate Invoice PDF
document.getElementById('downloadInvoiceBtn').addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Generating...';
    
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Colors
        const primaryColor = [15, 43, 70]; // Dark blue to match theme
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
        
        // Items Table with more customization details
        const tableData = orderData.items.map(item => [
            item.name,
            `Size: ${item.dimensions}\nShape: ${item.shape}\nType: ${item.glassType}\nThickness: ${item.thickness}\nEdge: ${item.edgeWork}\nFrame: ${item.frameType}`,
            item.quantity,
            '₱' + item.unitPrice.toLocaleString('en-PH', {minimumFractionDigits: 2}),
            '₱' + item.total.toLocaleString('en-PH', {minimumFractionDigits: 2})
        ]);
        
        doc.autoTable({
            startY: 105,
            head: [['Product', 'Customization Details', 'Qty', 'Unit Price', 'Amount']],
            body: tableData,
            theme: 'striped',
            headStyles: {
                fillColor: primaryColor,
                textColor: 255,
                fontSize: 10,
                fontStyle: 'bold'
            },
            bodyStyles: {
                fontSize: 8,
                textColor: darkColor,
                cellPadding: 4
            },
            columnStyles: {
                0: { cellWidth: 35 },
                1: { cellWidth: 65 },
                2: { cellWidth: 15, halign: 'center' },
                3: { cellWidth: 30, halign: 'right' },
                4: { cellWidth: 30, halign: 'right' }
            },
            margin: { left: 20, right: 20 }
        });
        
        let currentY = doc.lastAutoTable.finalY + 10;
        
        // Check if any items have design references
        const itemsWithDesigns = orderData.items.filter(item => item.designRef && item.designRef !== '');
        
        if (itemsWithDesigns.length > 0) {
            // Add Custom Designs Section
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(12);
            doc.setTextColor(...primaryColor);
            doc.text('Custom Design Layouts', 20, currentY);
            currentY += 8;
            
            // Load and add design images
            for (let i = 0; i < itemsWithDesigns.length; i++) {
                const item = itemsWithDesigns[i];
                
                try {
                    const imgData = await loadImageAsBase64(item.designRef);
                    
                    // Check if we need a new page
                    if (currentY > 200) {
                        doc.addPage();
                        currentY = 20;
                    }
                    
                    // Draw design box
                    doc.setFillColor(...lightGray);
                    doc.roundedRect(20, currentY, 170, 60, 3, 3, 'F');
                    
                    // Product name
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(10);
                    doc.setTextColor(...darkColor);
                    doc.text(item.name + ' - Custom Design', 25, currentY + 8);
                    
                    // Add image
                    doc.addImage(imgData, 'PNG', 25, currentY + 12, 45, 45);
                    
                    // Add specs next to image
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(9);
                    let specY = currentY + 15;
                    doc.text('Size: ' + item.dimensions, 80, specY);
                    doc.text('Shape: ' + item.shape, 80, specY + 6);
                    doc.text('Type: ' + item.glassType, 80, specY + 12);
                    doc.text('Thickness: ' + item.thickness, 80, specY + 18);
                    doc.text('Edge: ' + item.edgeWork, 80, specY + 24);
                    doc.text('Frame: ' + item.frameType, 80, specY + 30);
                    if (item.engraving && item.engraving !== '-' && item.engraving !== 'None') {
                        doc.text('Engraving: ' + item.engraving, 80, specY + 36);
                    }
                    
                    currentY += 65;
                } catch (err) {
                    console.warn('Could not load design image:', err);
                    // Continue without the image
                }
            }
            
            currentY += 5;
        }
        
        // Check if we need a new page for summary
        if (currentY > 200) {
            doc.addPage();
            currentY = 20;
        }
        
        // Summary
        const finalY = currentY;
        
        doc.setFillColor(...lightGray);
        doc.roundedRect(120, finalY, 70, 45, 3, 3, 'F');
        
        doc.setTextColor(...darkColor);
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
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
        
        // Footer on each page
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFillColor(...primaryColor);
            doc.rect(0, 280, 210, 17, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(9);
            doc.text('Thank you for your purchase! | www.glassify.com | contact@glassify.com', 105, 290, { align: 'center' });
        }
        
        // Save
        doc.save('Glassify_Invoice_' + orderData.transactionId + '.pdf');
        
    } catch (error) {
        console.error('Error generating invoice:', error);
        alert('Error generating invoice. Please try again.');
    } finally {
        btn.disabled = false;
        btn.textContent = '⬇ Download Invoice';
    }
});
</script>
