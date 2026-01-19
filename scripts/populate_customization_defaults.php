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
echo "Done!\n";
?>