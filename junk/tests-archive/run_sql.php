<?php
// Simple script to populate tag prices for product 16
require_once 'index.php';

$CI =& get_instance();
$CI->load->database();

// Check if product 16 already has tag prices
$CI->db->where('Product_ID', 16);
$existing = $CI->db->get('product_tag_prices')->result();

if (count($existing) > 0) {
    echo "Product 16 already has " . count($existing) . " tag prices\n";
    exit;
}

// Tag prices for product 16
$tagPrices = [
    ['Product_ID' => 16, 'FieldID' => 'shape', 'TagName' => 'Round', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'shape', 'TagName' => 'Square', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'shape', 'TagName' => 'Rectangle', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'shape', 'TagName' => 'Oval', 'Price' => 200],
    ['Product_ID' => 16, 'FieldID' => 'frameType', 'TagName' => 'Frameless', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'frameType', 'TagName' => 'Framed', 'Price' => 300],
    ['Product_ID' => 16, 'FieldID' => 'frameColor', 'TagName' => 'Black frame', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'frameColor', 'TagName' => 'White frame', 'Price' => 100],
    ['Product_ID' => 16, 'FieldID' => 'frameColor', 'TagName' => 'Gold frame', 'Price' => 800],
    ['Product_ID' => 16, 'FieldID' => 'frameColor', 'TagName' => 'Silver frame', 'Price' => 600],
    ['Product_ID' => 16, 'FieldID' => 'frameColor', 'TagName' => 'Bronze frame', 'Price' => 500],
    ['Product_ID' => 16, 'FieldID' => 'edgeFinish', 'TagName' => 'Beveled', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'edgeFinish', 'TagName' => 'Polished', 'Price' => 150],
    ['Product_ID' => 16, 'FieldID' => 'edgeFinish', 'TagName' => 'Rounded', 'Price' => 100],
    ['Product_ID' => 16, 'FieldID' => 'tintFinish', 'TagName' => 'Clear', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'tintFinish', 'TagName' => 'Bronze tint/color', 'Price' => 200],
    ['Product_ID' => 16, 'FieldID' => 'tintFinish', 'TagName' => 'Gray tint/color', 'Price' => 200],
    ['Product_ID' => 16, 'FieldID' => 'tintFinish', 'TagName' => 'Blue tint/color', 'Price' => 250],
    ['Product_ID' => 16, 'FieldID' => 'orientation', 'TagName' => 'Vertical', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'orientation', 'TagName' => 'Horizontal', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'style', 'TagName' => 'Plain', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'style', 'TagName' => 'French Type (grid/paneled design)', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'style', 'TagName' => 'Beveled edges', 'Price' => 150],
    ['Product_ID' => 16, 'FieldID' => 'mountingMethod', 'TagName' => 'Wall-mounted', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'mountingMethod', 'TagName' => 'Ceiling-mounted', 'Price' => 200],
    ['Product_ID' => 16, 'FieldID' => 'control', 'TagName' => 'Touch sensor button', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'control', 'TagName' => 'Remote control', 'Price' => 300],
    ['Product_ID' => 16, 'FieldID' => 'control', 'TagName' => 'App control', 'Price' => 500],
    ['Product_ID' => 16, 'FieldID' => 'additionalFeatures', 'TagName' => 'Defogger', 'Price' => 300],
    ['Product_ID' => 16, 'FieldID' => 'additionalFeatures', 'TagName' => 'Anti-fog coating', 'Price' => 200],
    ['Product_ID' => 16, 'FieldID' => 'additionalFeatures', 'TagName' => 'Heated mirror', 'Price' => 400],
    ['Product_ID' => 16, 'FieldID' => 'arrangement', 'TagName' => 'Single', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'arrangement', 'TagName' => 'Can be displayed as triptych', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'lighting', 'TagName' => 'No lighting', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'lighting', 'TagName' => 'Integrated LED lighting', 'Price' => 500],
    ['Product_ID' => 16, 'FieldID' => 'ledColorTemperature', 'TagName' => 'Warm white', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'ledColorTemperature', 'TagName' => 'Cool white', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'ledColorTemperature', 'TagName' => 'Daylight', 'Price' => 50],
    ['Product_ID' => 16, 'FieldID' => 'gridPattern', 'TagName' => 'No grid', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'gridPattern', 'TagName' => 'French window style grid', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'gridPattern', 'TagName' => 'Custom grid pattern', 'Price' => 200],
    ['Product_ID' => 16, 'FieldID' => 'quantity', 'TagName' => 'Single', 'Price' => 0],
    ['Product_ID' => 16, 'FieldID' => 'quantity', 'TagName' => 'Available in sets (3 sets, or individually)', 'Price' => 0],
];

$inserted = 0;
foreach ($tagPrices as $tagPrice) {
    $tagPrice['Created_Date'] = date('Y-m-d H:i:s');
    $CI->db->insert('product_tag_prices', $tagPrice);
    $inserted++;
}

echo "Inserted $inserted tag prices for product 16\n";
?>