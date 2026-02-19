<?php
// Quick debug script to inspect order_items and customization for an order
// Usage: php scripts/debug_get_order.php <order_id>

$order_id = $argv[1] ?? null;
if (!$order_id) {
    echo json_encode(['error' => 'order_id required']);
    exit(1);
}

// DB config (fallback hardcoded for local XAMPP)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'latest_glassifydb';

$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_errno) {
    echo json_encode(['error' => 'DB connect failed: ' . $mysqli->connect_error]);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

// Get order
$stmt = $mysqli->prepare('SELECT * FROM `order` WHERE OrderID = ?');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order_res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order_res) {
    echo json_encode(['error' => 'Order not found']);
    exit(1);
}

// Get items
$stmt = $mysqli->prepare('SELECT oi.*, p.ProductName FROM order_items oi LEFT JOIN product p ON p.Product_ID = oi.Product_ID WHERE oi.OrderID = ?');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$items_res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Try to get customization if items missing or for fallback
$customization = null;
if (empty($items_res)) {
    // If order has CustomizationID
    if (!empty($order_res['CustomizationID'])) {
        $cid = $order_res['CustomizationID'];
        $stmt = $mysqli->prepare('SELECT * FROM customization WHERE CustomizationID = ?');
        $stmt->bind_param('i', $cid);
        $stmt->execute();
        $customization = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        // fallback to latest customization for customer
        if (!empty($order_res['Customer_ID'])) {
            $cust = $order_res['Customer_ID'];
            $stmt = $mysqli->prepare('SELECT * FROM customization WHERE Customer_ID = ? ORDER BY CustomizationID DESC LIMIT 1');
            $stmt->bind_param('i', $cust);
            $stmt->execute();
            $customization = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }
} else {
    // Also try to fetch customization (useful for filling missing fields)
    if (!empty($order_res['CustomizationID'])) {
        $cid = $order_res['CustomizationID'];
        $stmt = $mysqli->prepare('SELECT * FROM customization WHERE CustomizationID = ?');
        $stmt->bind_param('i', $cid);
        $stmt->execute();
        $customization = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else if (!empty($order_res['Customer_ID'])) {
        $cust = $order_res['Customer_ID'];
        $stmt = $mysqli->prepare('SELECT * FROM customization WHERE Customer_ID = ? ORDER BY CustomizationID DESC LIMIT 1');
        $stmt->bind_param('i', $cust);
        $stmt->execute();
        $customization = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// Prepare merged items similar to controller logic
$merged = [];
if (!empty($items_res)) {
    foreach ($items_res as $item) {
        $dimensions = $item['Dimensions'] ?? null;
        $glass_shape = $item['GlassShape'] ?? null;
        $glass_type = $item['GlassType'] ?? null;
        $glass_thickness = $item['GlassThickness'] ?? null;
        $edge_work = $item['EdgeWork'] ?? null;
        $frame_type = $item['FrameType'] ?? null;
        $engraving = $item['Engraving'] ?? null;

        if ($customization) {
            if (empty($dimensions) && isset($customization['Dimensions'])) $dimensions = $customization['Dimensions'];
            if (empty($glass_shape) && isset($customization['GlassShape'])) $glass_shape = $customization['GlassShape'];
            if (empty($glass_type) && isset($customization['GlassType'])) $glass_type = $customization['GlassType'];
            if (empty($glass_thickness) && isset($customization['GlassThickness'])) $glass_thickness = $customization['GlassThickness'];
            if (empty($edge_work) && isset($customization['EdgeWork'])) $edge_work = $customization['EdgeWork'];
            if (empty($frame_type) && isset($customization['FrameType'])) $frame_type = $customization['FrameType'];
            if ((empty($engraving) || strtolower($engraving) === 'none') && isset($customization['Engraving'])) $engraving = $customization['Engraving'];
        }

        $merged[] = [
            'product_id' => $item['Product_ID'] ?? null,
            'product_name' => $item['ProductName'] ?? 'N/A',
            'quantity' => $item['Quantity'] ?? 1,
            'unit_price' => $item['UnitPrice'] ?? $item['EstimatePrice'] ?? 0,
            'dimensions' => $dimensions,
            'glass_shape' => $glass_shape,
            'glass_type' => $glass_type,
            'glass_thickness' => $glass_thickness,
            'edge_work' => $edge_work,
            'frame_type' => $frame_type,
            'engraving' => $engraving
        ];
    }
} else if ($customization) {
    $merged[] = [
        'product_id' => $customization['Product_ID'] ?? null,
        'product_name' => $customization['ProductName'] ?? 'Custom Glass Product',
        'quantity' => 1,
        'unit_price' => $customization['EstimatePrice'] ?? 0,
        'dimensions' => $customization['Dimensions'] ?? null,
        'glass_shape' => $customization['GlassShape'] ?? null,
        'glass_type' => $customization['GlassType'] ?? null,
        'glass_thickness' => $customization['GlassThickness'] ?? null,
        'edge_work' => $customization['EdgeWork'] ?? null,
        'frame_type' => $customization['FrameType'] ?? null,
        'engraving' => $customization['Engraving'] ?? null
    ];
}

echo json_encode(['order_id' => intval($order_id), 'items' => $merged, 'customization_found' => (bool)$customization], JSON_PRETTY_PRINT);

$mysqli->close();
