<?php
/**
 * Test script to verify Windows customization defaults are properly stored
 */

require_once __DIR__ . '/../index.php';

$CI =& get_instance();
$CI->load->database();

echo "Testing Windows customization field defaults...\n\n";

$windowsFields = [
    'Windows_Sliding',
    'Windows_Awning',
    'Windows_Casement',
    'Windows_Fixed Glass'
];

foreach ($windowsFields as $fieldKey) {
    echo "=== $fieldKey ===\n";

    $CI->db->where('FieldKey', $fieldKey);
    $config = $CI->db->get('customization_field_configs')->row();

    if ($config) {
        echo "✓ Found configuration\n";
        echo "  Category: {$config->Category}\n";
        echo "  Subcategory: {$config->Subcategory}\n";

        $fields = json_decode($config->FieldConfig, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "  ✓ Valid JSON configuration\n";
            echo "  Number of fields: " . count($fields) . "\n";

            // Show first field as example
            if (!empty($fields)) {
                $firstField = $fields[0];
                echo "  Example field: {$firstField['label']} (type: {$firstField['type']})\n";
            }
        } else {
            echo "  ✗ Invalid JSON: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "✗ Configuration not found\n";
    }

    // Check step names
    $stepNamesKey = $fieldKey . '_stepNames';
    $CI->db->where('FieldKey', $stepNamesKey);
    $stepConfig = $CI->db->get('customization_field_configs')->row();

    if ($stepConfig) {
        echo "✓ Found step names\n";
        $stepNames = json_decode($stepConfig->FieldConfig, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "  Step names: " . implode(' · ', $stepNames) . "\n";
        } else {
            echo "  ✗ Invalid step names JSON: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "✗ Step names not found\n";
    }

    echo "\n";
}

echo "Test completed!\n";
?>