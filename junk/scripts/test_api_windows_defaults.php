<?php
/**
 * Test script to verify the API returns Windows customization defaults
 */

require_once __DIR__ . '/../index.php';

$CI =& get_instance();
$CI->load->database();

// Test the getAll endpoint
echo "Testing customizationFields/getAll API endpoint...\n\n";

// Simulate the API call by calling the controller method directly
$CI->load->controller('CustomizationFieldsCon');
$CI->CustomizationFieldsCon->getAll();

echo "\n\nAPI test completed!\n";

// Also test individual Windows fields
echo "\nTesting individual Windows field retrievals:\n\n";

$windowsFields = [
    'Windows_Sliding',
    'Windows_Awning',
    'Windows_Casement',
    'Windows_Fixed Glass'
];

foreach ($windowsFields as $fieldKey) {
    echo "=== Testing $fieldKey ===\n";

    // Simulate API call by setting GET parameters and calling the method
    $_GET['category'] = 'Windows';
    $_GET['subcategory'] = str_replace('Windows_', '', $fieldKey);

    ob_start();
    $CI->CustomizationFieldsCon->get();
    $output = ob_get_clean();

    // Parse the JSON response
    $response = json_decode($output, true);

    if ($response && $response['status'] === 'success') {
        echo "✓ API call successful\n";
        echo "  Field key: {$response['fieldKey']}\n";
        echo "  Number of fields: " . count($response['fields']) . "\n";
        echo "  Has step names: " . (isset($response['stepNames']) ? 'Yes' : 'No') . "\n";
        if (isset($response['stepNames'])) {
            echo "  Step names: " . implode(' · ', $response['stepNames']) . "\n";
        }
    } else {
        echo "✗ API call failed\n";
        if ($response && isset($response['message'])) {
            echo "  Error: {$response['message']}\n";
        }
    }

    echo "\n";
}
?>