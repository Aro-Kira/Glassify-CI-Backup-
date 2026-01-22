<?php
/**
 * Update Existing Products - Web Accessible Version
 *
 * Access this script via: http://localhost/Glassify-CI/scripts/update_existing_products_series_web.php
 */

echo "<h1>Update Existing Products - SelectedCustomizationSeries Field</h1>";
echo "<pre>";

// Include necessary files
require_once '../application/config/database.php';
require_once '../system/core/CodeIgniter.php';

// Initialize database connection
$CI =& get_instance();
$CI->load->database();

echo "Database connection established.\n\n";

// Get all products with customization data
$query = $CI->db->query("
    SELECT Product_ID, Category, Subcategory, Customization, SelectedCustomizationSeries
    FROM products
    WHERE Customization IS NOT NULL AND Customization != ''
    AND (SelectedCustomizationSeries IS NULL OR SelectedCustomizationSeries = '')
");

$products = $query->result();

echo "Found " . count($products) . " products to update.\n\n";

$updated = 0;
$skipped = 0;

foreach ($products as $product) {
    echo "Processing Product ID: {$product->Product_ID} - {$product->Category} > {$product->Subcategory}\n";

    // Skip if already has SelectedCustomizationSeries
    if (!empty($product->SelectedCustomizationSeries)) {
        echo "  ✓ Already has SelectedCustomizationSeries: {$product->SelectedCustomizationSeries}\n";
        $skipped++;
        continue;
    }

    // Parse customization data
    $customization = json_decode($product->Customization, true);
    if (!$customization || !is_array($customization)) {
        echo "  ✗ Invalid customization data\n";
        continue;
    }

    // Try to infer series from customization data
    $detectedSeries = null;

    // Check for thickness field which often indicates series
    if (isset($customization['thickness'])) {
        $thickness = $customization['thickness'];

        if (is_numeric($thickness)) {
            $thicknessNum = floatval($thickness);

            // YC series detection (common in glass industry)
            if ($thicknessNum >= 3 && $thicknessNum <= 12) {
                $detectedSeries = 'YC-' . $thicknessNum . 'mm Series';
            }
        }
    }

    // If we detected a series, update the product
    if ($detectedSeries) {
        $CI->db->where('Product_ID', $product->Product_ID);
        $CI->db->update('products', ['SelectedCustomizationSeries' => $detectedSeries]);

        echo "  ✓ Updated with detected series: {$detectedSeries}\n";
        $updated++;
    } else {
        echo "  ✗ Could not detect series from customization data\n";
        $skipped++;
    }

    echo "\n";
}

echo "</pre>";
echo "<h2>Summary:</h2>";
echo "<ul>";
echo "<li>Updated: {$updated} products</li>";
echo "<li>Skipped: {$skipped} products</li>";
echo "<li>Total processed: " . ($updated + $skipped) . " products</li>";
echo "</ul>";

echo "<p><strong>Script completed successfully!</strong></p>";
echo "<p><a href='../index.php'>← Back to Application</a></p>";
?>