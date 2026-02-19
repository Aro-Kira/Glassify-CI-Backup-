<?php
$c = new mysqli('localhost','root','','latest_glassifydb');
// Check all columns in product table
echo "=== All product columns ===\n";
$r = $c->query("SHOW COLUMNS FROM product");
while($row = $r->fetch_assoc()) {
    echo $row['Field'] . ' | ' . $row['Type'] . PHP_EOL;
}

// Check product 22 specifically
echo "\n=== Product 22 customization-related fields ===\n";
$r2 = $c->query("SELECT Product_ID, ProductName, Category, Subcategory, Customization, SelectedCustomizationSeries FROM product WHERE Product_ID = 22");
if ($r2) {
    $row = $r2->fetch_assoc();
    echo "Product_ID: " . $row['Product_ID'] . "\n";
    echo "ProductName: " . $row['ProductName'] . "\n";
    echo "Category: " . $row['Category'] . "\n";
    echo "Subcategory: " . $row['Subcategory'] . "\n";
    echo "Customization: " . substr($row['Customization'] ?? 'NULL', 0, 200) . "\n";
    echo "SelectedCustomizationSeries: " . ($row['SelectedCustomizationSeries'] ?? 'NULL') . "\n";
}

// Check customization_field_configs
echo "\n=== customization_field_configs columns ===\n";
$r3 = $c->query("SHOW COLUMNS FROM customization_field_configs");
while($row = $r3->fetch_assoc()) {
    echo $row['Field'] . ' | ' . $row['Type'] . PHP_EOL;
}

echo "\n=== customization_field_configs for Doors_Sliding ===\n";
$r4 = $c->query("SELECT * FROM customization_field_configs WHERE FieldKey = 'Doors_Sliding'");
if ($r4 && $r4->num_rows > 0) {
    $row = $r4->fetch_assoc();
    foreach($row as $k => $v) {
        echo "$k: " . substr($v ?? 'NULL', 0, 300) . "\n";
    }
} else {
    echo "No config found for Doors_Sliding\n";
    $r5 = $c->query("SELECT FieldKey FROM customization_field_configs");
    echo "Available FieldKeys:\n";
    while($row = $r5->fetch_assoc()) {
        echo "  - " . $row['FieldKey'] . "\n";
    }
}

$c->close();
