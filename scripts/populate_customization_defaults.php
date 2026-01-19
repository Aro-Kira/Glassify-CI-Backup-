<?php
/**
 * Script to populate database with default customization fields from config
 */

function populateDefaultsToDatabase() {
    // Include the CodeIgniter framework
    require_once __DIR__ . '/../index.php';

    $CI =& get_instance();
    $CI->load->database();

    // Load defaults from config file
    $defaultsFile = APPPATH . 'config/customization_defaults.php';
    if (!file_exists($defaultsFile)) {
        die("Error: Defaults file not found: $defaultsFile\n");
    }

    $defaults = require $defaultsFile;

    // Check if table exists
    if (!$CI->db->table_exists('customization_field_configs')) {
        die("Error: customization_field_configs table does not exist. Please run the database migration.\n");
    }

    // Process Windows defaults
    $windowsCategories = [
        'Windows_Sliding' => ['Category' => 'Windows', 'Subcategory' => 'Sliding'],
        'Windows_Awning' => ['Category' => 'Windows', 'Subcategory' => 'Awning'],
        'Windows_Casement' => ['Category' => 'Windows', 'Subcategory' => 'Casement'],
        'Windows_Fixed Glass' => ['Category' => 'Windows', 'Subcategory' => 'Fixed Glass']
    ];

    $inserted = 0;
    $updated = 0;

    foreach ($windowsCategories as $fieldKey => $categoryInfo) {
        if (!isset($defaults[$fieldKey])) {
            echo "Warning: No defaults found for $fieldKey\n";
            continue;
        }

        // Check if config already exists
        $CI->db->where('FieldKey', $fieldKey);
        $existing = $CI->db->get('customization_field_configs')->row();

        $fieldsJson = json_encode($defaults[$fieldKey]);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Error: Invalid JSON for $fieldKey: " . json_last_error_msg() . "\n";
            continue;
        }

        if ($existing) {
            // Update existing
            $CI->db->where('FieldKey', $fieldKey);
            $CI->db->update('customization_field_configs', [
                'FieldConfig' => $fieldsJson,
                'Updated_Date' => date('Y-m-d H:i:s')
            ]);
            $updated++;
            echo "Updated: $fieldKey\n";
        } else {
            // Insert new
            $CI->db->insert('customization_field_configs', [
                'Category' => $categoryInfo['Category'],
                'Subcategory' => $categoryInfo['Subcategory'],
                'FieldKey' => $fieldKey,
                'FieldConfig' => $fieldsJson
            ]);
            $inserted++;
            echo "Inserted: $fieldKey\n";
        }

        // Handle step names
        $stepNamesKey = $fieldKey . '_stepNames';
        if (isset($defaults[$stepNamesKey])) {
            $stepNamesJson = json_encode($defaults[$stepNamesKey]);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "Error: Invalid JSON for $stepNamesKey: " . json_last_error_msg() . "\n";
                continue;
            }

            $CI->db->where('FieldKey', $stepNamesKey);
            $existingStepNames = $CI->db->get('customization_field_configs')->row();

            if ($existingStepNames) {
                $CI->db->where('FieldKey', $stepNamesKey);
                $CI->db->update('customization_field_configs', [
                    'FieldConfig' => $stepNamesJson,
                    'Updated_Date' => date('Y-m-d H:i:s')
                ]);
                echo "Updated step names: $stepNamesKey\n";
            } else {
                $CI->db->insert('customization_field_configs', [
                    'Category' => $categoryInfo['Category'],
                    'Subcategory' => $categoryInfo['Subcategory'],
                    'FieldKey' => $stepNamesKey,
                    'FieldConfig' => $stepNamesJson
                ]);
                echo "Inserted step names: $stepNamesKey\n";
            }
        }
    }

    echo "\nSummary:\n";
    echo "Inserted: $inserted configurations\n";
    echo "Updated: $updated configurations\n";
    echo "Total Windows configurations processed: " . count($windowsCategories) . "\n";
}

echo "Populating database with Windows customization field defaults...\n";
populateDefaultsToDatabase();

// Now populate tag prices for products that don't have them
populateTagPricesForProducts();

echo "Done!\n";

function populateTagPricesForProducts() {
    // Include the CodeIgniter framework
    require_once __DIR__ . '/../index.php';

    $CI =& get_instance();
    $CI->load->database();

    // Get all products
    $products = $CI->db->get('product')->result();

    echo "\nPopulating tag prices for products...\n";

    $totalInserted = 0;

    foreach ($products as $product) {
        $product_id = $product->Product_ID;

        // Check if this product already has tag prices
        $CI->db->where('Product_ID', $product_id);
        $existingPrices = $CI->db->get('product_tag_prices')->result();

        if (count($existingPrices) > 0) {
            echo "Product $product_id ($product->Product_Name) already has tag prices, skipping...\n";
            continue;
        }

        // Get the category and subcategory for this product
        $category = $product->Category;
        $subcategory = $product->Subcategory;

        // Map category/subcategory to field key
        $fieldKey = getFieldKeyFromCategory($category, $subcategory);

        if (!$fieldKey) {
            echo "No field key mapping found for product $product_id ($category -> $subcategory), skipping...\n";
            continue;
        }

        // Get the customization fields for this field key
        $CI->db->where('FieldKey', $fieldKey);
        $config = $CI->db->get('customization_field_configs')->row();

        if (!$config) {
            echo "No customization config found for field key $fieldKey, skipping product $product_id...\n";
            continue;
        }

        $fields = json_decode($config->FieldConfig, true);
        if (!$fields) {
            echo "Invalid JSON in customization config for field key $fieldKey, skipping product $product_id...\n";
            continue;
        }

        // Generate tag prices from fields
        $tagPrices = generateTagPricesFromFields($fields, $product_id);

        // Insert tag prices
        foreach ($tagPrices as $tagPrice) {
            $CI->db->insert('product_tag_prices', $tagPrice);
            $totalInserted++;
        }

        echo "Inserted " . count($tagPrices) . " tag prices for product $product_id ($product->Product_Name)\n";
    }

    echo "Total tag prices inserted: $totalInserted\n";
}

function getFieldKeyFromCategory($category, $subcategory) {
    $mappings = [
        'Windows' => [
            'Sliding' => 'Windows_Sliding',
            'Awning' => 'Windows_Awning',
            'Casement' => 'Windows_Casement',
            'Fixed Glass' => 'Windows_Fixed Glass'
        ],
        'Mirrors & Specialty Glass' => [
            'Mirrors' => 'Specialty_Mirrors'
        ]
    ];

    return $mappings[$category][$subcategory] ?? null;
}

function generateTagPricesFromFields($fields, $productId) {
    $tagPrices = [];

    foreach ($fields as $field) {
        if (isset($field['options']) && is_array($field['options'])) {
            $fieldId = $field['id'];

            foreach ($field['options'] as $option) {
                // Generate default price (0 for most options, some specific ones get higher prices)
                $price = 0;

                // Add some pricing logic for common expensive options
                if (stripos($option, 'LED') !== false || stripos($option, 'lighting') !== false) {
                    $price = 500;
                } elseif (stripos($option, 'defogger') !== false || stripos($option, 'Defogger') !== false) {
                    $price = 300;
                } elseif (stripos($option, 'tinted') !== false || stripos($option, 'bronze') !== false) {
                    $price = 200;
                } elseif (stripos($option, 'gold') !== false || stripos($option, 'Gold') !== false) {
                    $price = 800;
                } elseif (stripos($option, 'laminated') !== false || stripos($option, 'Laminated') !== false) {
                    $price = 400;
                }

                $tagPrices[] = [
                    'Product_ID' => $productId,
                    'FieldID' => $fieldId,
                    'TagName' => $option,
                    'Price' => $price,
                    'Created_Date' => date('Y-m-d H:i:s')
                ];
            }
        }
    }

    return $tagPrices;
}
?>