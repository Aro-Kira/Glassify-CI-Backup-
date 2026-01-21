<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quotation <?php echo $quotation_number; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        .header {
            border-bottom: 3px solid #083c5d;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #083c5d;
            margin-bottom: 5px;
        }
        .quotation-title {
            font-size: 18px;
            color: #666;
            margin-top: 10px;
        }
        .quotation-number {
            font-size: 16px;
            font-weight: bold;
            color: #083c5d;
        }
        .info-section {
            margin: 30px 0;
        }
        .info-row {
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .items-table th {
            background-color: #083c5d;
            color: white;
        }
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #083c5d;
            margin-top: 10px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">GlassWorth Builders</div>
        <div class="quotation-title">QUOTATION</div>
        <div class="quotation-number">Quotation #: <?php echo $quotation_number; ?></div>
        <div style="margin-top: 10px;">Date: <?php echo date('F d, Y', strtotime($quotation->CreatedDate ?? date('Y-m-d'))); ?></div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer:</span>
            <span><?php echo htmlspecialchars($quotation->CustomerName ?? 'N/A'); ?></span>
        </div>
        <?php if (!empty($quotation->CustomerEmail)): ?>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span><?php echo htmlspecialchars($quotation->CustomerEmail); ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($quotation->CustomerPhone)): ?>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span><?php echo htmlspecialchars($quotation->CustomerPhone); ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($quotation->OrderNumber)): ?>
        <div class="info-row">
            <span class="info-label">Order Number:</span>
            <span><?php echo htmlspecialchars($quotation->OrderNumber); ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($quotation->DeliveryAddress)): ?>
        <div class="info-row">
            <span class="info-label">Delivery Address:</span>
            <span><?php echo htmlspecialchars($quotation->DeliveryAddress); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Glass Product</td>
                <td>1</td>
                <td>₱<?php echo number_format($quotation->TotalAmount ?? $quotation->Total_amount ?? 0, 2); ?></td>
                <td>₱<?php echo number_format($quotation->TotalAmount ?? $quotation->Total_amount ?? 0, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-amount">
            Total Amount: ₱<?php echo number_format($quotation->TotalAmount ?? $quotation->Total_amount ?? 0, 2); ?>
        </div>
    </div>

    <?php if (!empty($quotation->Notes)): ?>
    <div class="info-section">
        <div class="info-label">Notes:</div>
        <div><?php echo nl2br(htmlspecialchars($quotation->Notes)); ?></div>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>Thank you for choosing GlassWorth Builders!</p>
        <p>For inquiries, please contact us at:</p>
        <p>Phone: 09275193300 / 09761653506</p>
        <p>Email: glassworthbuilders@gmail.com</p>
    </div>
</body>
</html>
