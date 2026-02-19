<?php
/**
 * Script to populate tag prices for product ID 16 (Black Framed Round Mirror)
 */

// Include the CodeIgniter framework
require_once __DIR__ . '/../index.php';

$CI =& get_instance();
$CI->load->database();

$product_id = 16; // Black Framed Round Mirror

// Check if this product already has tag prices
$CI->db->where('Product_ID', $product_id);
$existingPrices = $CI->db->get('product_tag_prices')->result();

if (count($existingPrices) > 0) {
    echo "Product $product_id already has tag prices\n";
    exit;
}

// Define tag prices for Specialty Mirrors
$tagPrices = [
    // Shape options
    ['Product_ID' => $product_id, 'FieldID' => 'shape', 'TagName' => 'Round', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'shape', 'TagName' => 'Square', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'shape', 'TagName' => 'Rectangle', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'shape', 'TagName' => 'Oval', 'Price' => 200],

    // Frame Type options
    ['Product_ID' => $product_id, 'FieldID' => 'frameType', 'TagName' => 'Frameless', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'frameType', 'TagName' => 'Framed', 'Price' => 300],

    // Frame Material/Color options
    ['Product_ID' => $product_id, 'FieldID' => 'frameColor', 'TagName' => 'Black frame', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'frameColor', 'TagName' => 'White frame', 'Price' => 100],
    ['Product_ID' => $product_id, 'FieldID' => 'frameColor', 'TagName' => 'Gold frame', 'Price' => 800],
    ['Product_ID' => $product_id, 'FieldID' => 'frameColor', 'TagName' => 'Silver frame', 'Price' => 600],
    ['Product_ID' => $product_id, 'FieldID' => 'frameColor', 'TagName' => 'Bronze frame', 'Price' => 500],

    // Edge Finish options
    ['Product_ID' => $product_id, 'FieldID' => 'edgeFinish', 'TagName' => 'Beveled', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'edgeFinish', 'TagName' => 'Polished', 'Price' => 150],
    ['Product_ID' => $product_id, 'FieldID' => 'edgeFinish', 'TagName' => 'Rounded', 'Price' => 100],

    // Tint/Finish options
    ['Product_ID' => $product_id, 'FieldID' => 'tintFinish', 'TagName' => 'Clear', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'tintFinish', 'TagName' => 'Bronze tint/color', 'Price' => 200],
    ['Product_ID' => $product_id, 'FieldID' => 'tintFinish', 'TagName' => 'Gray tint/color', 'Price' => 200],
    ['Product_ID' => $product_id, 'FieldID' => 'tintFinish', 'TagName' => 'Blue tint/color', 'Price' => 250],

    // Orientation options
    ['Product_ID' => $product_id, 'FieldID' => 'orientation', 'TagName' => 'Vertical', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'orientation', 'TagName' => 'Horizontal', 'Price' => 0],

    // Style options
    ['Product_ID' => $product_id, 'FieldID' => 'style', 'TagName' => 'Plain', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'style', 'TagName' => 'French Type (grid/paneled design)', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'style', 'TagName' => 'Beveled edges', 'Price' => 150],

    // Mounting Method options
    ['Product_ID' => $product_id, 'FieldID' => 'mountingMethod', 'TagName' => 'Wall-mounted', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'mountingMethod', 'TagName' => 'Ceiling-mounted', 'Price' => 200],

    // Control options
    ['Product_ID' => $product_id, 'FieldID' => 'control', 'TagName' => 'Touch sensor button', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'control', 'TagName' => 'Remote control', 'Price' => 300],
    ['Product_ID' => $product_id, 'FieldID' => 'control', 'TagName' => 'App control', 'Price' => 500],

    // Additional Features options
    ['Product_ID' => $product_id, 'FieldID' => 'additionalFeatures', 'TagName' => 'Defogger', 'Price' => 300],
    ['Product_ID' => $product_id, 'FieldID' => 'additionalFeatures', 'TagName' => 'Anti-fog coating', 'Price' => 200],
    ['Product_ID' => $product_id, 'FieldID' => 'additionalFeatures', 'TagName' => 'Heated mirror', 'Price' => 400],

    // Arrangement options
    ['Product_ID' => $product_id, 'FieldID' => 'arrangement', 'TagName' => 'Single', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'arrangement', 'TagName' => 'Can be displayed as triptych', 'Price' => 0],

    // Lighting options
    ['Product_ID' => $product_id, 'FieldID' => 'lighting', 'TagName' => 'No lighting', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'lighting', 'TagName' => 'Integrated LED lighting', 'Price' => 500],

    // LED Color/Temperature options
    ['Product_ID' => $product_id, 'FieldID' => 'ledColorTemperature', 'TagName' => 'Warm white', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'ledColorTemperature', 'TagName' => 'Cool white', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'ledColorTemperature', 'TagName' => 'Daylight', 'Price' => 50],

    // Grid Pattern options
    ['Product_ID' => $product_id, 'FieldID' => 'gridPattern', 'TagName' => 'No grid', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'gridPattern', 'TagName' => 'French window style grid', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'gridPattern', 'TagName' => 'Custom grid pattern', 'Price' => 200],

    // Quantity options
    ['Product_ID' => $product_id, 'FieldID' => 'quantity', 'TagName' => 'Single', 'Price' => 0],
    ['Product_ID' => $product_id, 'FieldID' => 'quantity', 'TagName' => 'Available in sets (3 sets, or individually)', 'Price' => 0],
];

$inserted = 0;
foreach ($tagPrices as $tagPrice) {
    $tagPrice['Created_Date'] = date('Y-m-d H:i:s');
    $CI->db->insert('product_tag_prices', $tagPrice);
    $inserted++;
}

echo "Inserted $inserted tag prices for product $product_id\n";
?>