<?php
// Quick check: does the 'Customization' column exist in the customization table?
$conn = new mysqli('localhost', 'root', '', 'latest_glassifydb');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "=== CUSTOMIZATION TABLE COLUMNS ===\n";
$result = $conn->query("SHOW COLUMNS FROM customization");
$has_customization_col = false;
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' | ' . $row['Type'] . "\n";
    if ($row['Field'] === 'Customization') {
        $has_customization_col = true;
    }
}

echo "\n=== Customization column exists: " . ($has_customization_col ? 'YES' : 'NO') . " ===\n";

if (!$has_customization_col) {
    echo "\nAdding Customization column...\n";
    $alter = $conn->query("ALTER TABLE customization ADD COLUMN `Customization` TEXT DEFAULT NULL COMMENT 'JSON string containing all dynamic customization fields' AFTER `PriceBreakdown`");
    if ($alter) {
        echo "SUCCESS: Customization column added!\n";
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
}

// Also check latest cart item
echo "\n=== LATEST CART ITEMS (last 3) ===\n";
$result = $conn->query("SELECT c.Cart_ID, cu.CustomizationID, cu.Customization, cu.Dimensions, cu.GlassType, cu.FrameType FROM cart c JOIN customization cu ON cu.CustomizationID = c.CustomizationID ORDER BY c.Cart_ID DESC LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Cart_ID: " . $row['Cart_ID'] . " | Customization JSON: " . ($row['Customization'] ?: 'NULL') . " | Dims: " . $row['Dimensions'] . " | Type: " . $row['GlassType'] . " | Frame: " . $row['FrameType'] . "\n";
    }
}

$conn->close();
echo "\nDone.\n";
