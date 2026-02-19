<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Print Order</title>
  <style>
    body { font-family: Arial, sans-serif; color: #222; margin: 20px; }
    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .header h1 { margin:0; font-size:20px }
    .meta { margin-bottom:10px }
    table { width:100%; border-collapse:collapse; margin-top:10px }
    th, td { border:1px solid #ddd; padding:8px; text-align:left }
    th { background:#f4f4f4 }
  </style>
</head>
<body>
  <div class="header">
    <div>
      <h1 style="margin-bottom:6px">Order <?php echo isset($order->OrderNumber) ? '#' . $order->OrderNumber : ('#' . ($order->OrderID ?? '')); ?></h1>
      <div style="font-size:13px; color:#666">Order Date: <?php echo htmlspecialchars($order->OrderDate ?? date('Y-m-d')); ?></div>
    </div>
    <div style="text-align:right">
      <div style="font-size:13px; color:#666">Generated: <?php echo date('F j, Y, g:i a'); ?></div>
      <div style="margin-top:8px; font-weight:600; color:#02455F">Status: <?php echo htmlspecialchars($order->Status ?? ''); ?></div>
    </div>
  </div>

  <div class="meta" style="display:flex; gap:24px; margin-bottom:8px;">
    <div style="flex:1">
      <strong>Client:</strong> <?php echo htmlspecialchars(trim(($order->First_Name ?? '') . ' ' . ($order->Middle_Name ?? '') . ' ' . ($order->Last_Name ?? ''))); ?><br>
      <strong>Email:</strong> <?php echo htmlspecialchars($order->Email ?? ''); ?><br>
      <strong>Phone:</strong> <?php echo htmlspecialchars($order->PhoneNum ?? ''); ?><br>
    </div>
    <div style="flex:1">
      <strong>Address:</strong><br>
      <?php
        $address = '';
        if (!empty($order->Address)) $address = $order->Address;
        if (empty($address)) {
          // Try common alternative fields
          $address = trim(($order->AddressLine1 ?? '') . ' ' . ($order->AddressLine2 ?? '') . ' ' . ($order->City ?? '') . ' ' . ($order->Province ?? ''));
        }
        echo nl2br(htmlspecialchars($address ?: 'N/A'));
      ?>
    </div>
  </div>

  <?php if (!empty($order->SpecialInstructions) || !empty($order->Notes)) : ?>
    <div style="margin-bottom:12px;">
      <strong>Special Instructions / Notes:</strong>
      <div style="margin-top:6px; color:#333; padding:8px; background:#fafafa; border:1px solid #eee;">
        <?php echo nl2br(htmlspecialchars($order->SpecialInstructions ?? $order->Notes ?? '')); ?>
      </div>
    </div>
  <?php endif; ?>

  <h3 style="margin-top:8px;">Items</h3>
  <table>
    <thead>
      <tr>
        <th>Product</th>
        <th>Specs</th>
        <th>Quantity</th>
        <th>Unit Price</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
    <?php
      $items = [];
      if (method_exists($this->Order_model, 'get_order_customizations')) {
        $items = $this->Order_model->get_order_customizations($order->OrderID ?? $order->order_id ?? 0);
      }
      if (!empty($items)) {
        foreach ($items as $it) {
          $specParts = [];
          if (!empty($it->Dimensions)) $specParts[] = $it->Dimensions;
          if (!empty($it->Width) || !empty($it->Height)) $specParts[] = trim(($it->Width ?? '') . 'x' . ($it->Height ?? '')) . ($it->Unit ? ' ' . $it->Unit : '');
          if (!empty($it->GlassType)) $specParts[] = $it->GlassType;
          if (!empty($it->GlassThickness)) $specParts[] = $it->GlassThickness;
          if (!empty($it->EdgeWork)) $specParts[] = 'Edge: ' . $it->EdgeWork;
          if (!empty($it->FrameType)) $specParts[] = 'Frame: ' . $it->FrameType;
          $specText = htmlspecialchars(implode(' • ', array_filter($specParts)));

          echo '<tr>';
          echo '<td>' . htmlspecialchars($it->ProductName ?? $it->Product ?? 'Unnamed') . '</td>';
          echo '<td>' . ($specText ?: '&#8212;') . '</td>';
          echo '<td>' . htmlspecialchars($it->Quantity ?? $it->Qty ?? 1) . '</td>';
          echo '<td>' . htmlspecialchars($it->UnitPrice ?? $it->Price ?? '') . '</td>';
          echo '<td>' . htmlspecialchars($it->Subtotal ?? '') . '</td>';
          echo '</tr>';
        }
      } else {
        echo '<tr><td colspan="5">No items found</td></tr>';
      }
    ?>
    </tbody>
  </table>

  <div style="text-align:right; margin-top:12px; font-size:16px;">
    <div><strong>Subtotal:</strong> <?php echo htmlspecialchars($order->SubTotal ?? $order->Subtotal ?? ''); ?></div>
    <div><strong>Shipping:</strong> <?php echo htmlspecialchars($order->ShippingAmount ?? '0.00'); ?></div>
    <div style="margin-top:6px; font-size:18px;"><strong>Total: <?php echo htmlspecialchars($order->TotalAmount ?? ''); ?></strong></div>
  </div>

  <script>
    // Auto-print when opened
    window.onload = function(){ window.print(); };
  </script>
</body>
</html>
