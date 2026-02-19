<?php
// Backfill missing item specs for a given order_id
// Usage: php backfill_order_items.php <order_id>
$order_id = $argv[1] ?? null;
if (!$order_id) {
    echo "Usage: php backfill_order_items.php <order_id>\n";
    exit(1);
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'latest_glassifydb';

$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_errno) {
    echo "DB connect failed: " . $mysqli->connect_error . "\n";
    exit(1);
}
$mysqli->set_charset('utf8mb4');

// Fetch order
$stmt = $mysqli->prepare('SELECT * FROM `order` WHERE OrderID = ?');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) { echo "Order not found\n"; exit(1); }

// Fetch order_items
$stmt = $mysqli->prepare('SELECT * FROM order_items WHERE OrderID = ?');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch customization (if any)
$customization = null;
if (!empty($order['CustomizationID'])) {
    $cid = $order['CustomizationID'];
    $stmt = $mysqli->prepare('SELECT * FROM customization WHERE CustomizationID = ?');
    $stmt->bind_param('i', $cid);
    $stmt->execute();
    $customization = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else if (!empty($order['Customer_ID'])) {
    $cust = $order['Customer_ID'];
    $stmt = $mysqli->prepare('SELECT * FROM customization WHERE Customer_ID = ? ORDER BY CustomizationID DESC LIMIT 1');
    $stmt->bind_param('i', $cust);
    $stmt->execute();
    $customization = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Prepare backup file
$backupFile = __DIR__ . "/backfill_order_{$order_id}_backup.sql";
$fh = fopen($backupFile, 'w');
if (!$fh) { echo "Failed to open backup file\n"; exit(1); }

fwrite($fh, "-- Backup of order_items for OrderID={$order_id} on " . date('c') . "\n\n");

$updates = [];
foreach ($items as $item) {
    // Write INSERT backup
    $cols = array_keys($item);
    $vals = array_map(function($v) use ($mysqli){ return isset($v) ? "'" . $mysqli->real_escape_string($v) . "'" : 'NULL'; }, array_values($item));
    fwrite($fh, "INSERT INTO `order_items` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ");\n");

    // Gather possible fill sources
    $product = null;
    if (!empty($item['Product_ID'])) {
        $pid = $item['Product_ID'];
        $stmt = $mysqli->prepare('SELECT * FROM product WHERE Product_ID = ?');
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    $new = [];
    $fields = ['Dimensions','GlassShape','GlassType','GlassThickness','EdgeWork','FrameType','Engraving'];
    foreach ($fields as $f) {
        $current = $item[$f] ?? null;
        $filled = $current;
        if (empty($filled)) {
            if ($customization && isset($customization[$f]) && $customization[$f] !== '') {
                $filled = $customization[$f];
            } else if ($product && isset($product[$f]) && $product[$f] !== '') {
                $filled = $product[$f];
            }
        }
        $new[$f] = $filled;
    }

    // If any changed, queue update
    $changed = [];
    foreach ($fields as $f) {
        $orig = $item[$f] ?? null;
        $val = $new[$f];
        if (($orig === null && $val !== null) || ($orig !== null && $orig !== $val)) {
            $changed[$f] = $val;
        }
    }

    if (!empty($changed)) {
        $setSqlParts = [];
        foreach ($changed as $k => $v) {
            if ($v === null) $setSqlParts[] = "`$k` = NULL";
            else $setSqlParts[] = "`$k` = '" . $mysqli->real_escape_string($v) . "'";
        }
        $whereId = $item['OrderItemID'];
        $updateSql = "UPDATE `order_items` SET " . implode(', ', $setSqlParts) . " WHERE OrderItemID = " . intval($whereId) . ";";
        // Execute update
        if ($mysqli->query($updateSql)) {
            $updates[] = $updateSql;
        } else {
            fwrite($fh, "-- FAILED UPDATE: " . $updateSql . " -- Error: " . $mysqli->error . "\n");
        }
    }
}

fclose($fh);

// Report
echo "Backup written to: {$backupFile}\n";
if (empty($updates)) {
    echo "No updates needed; no spec fields were filled.\n";
} else {
    echo "Applied updates:\n";
    foreach ($updates as $u) echo $u . "\n";
}

$mysqli->close();
