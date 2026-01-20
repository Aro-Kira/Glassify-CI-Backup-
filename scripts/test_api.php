<?php
// Test script for customization fields API

$url = 'http://localhost/Glassify-CI/customizationFields/get?category=Windows&subcategory=Sliding';

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json',
        'timeout' => 10
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "Error: Could not connect to API\n";
    exit(1);
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error: Invalid JSON response\n";
    echo $response . "\n";
    exit(1);
}

echo "API Response:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n";

echo "\nSummary:\n";
echo "Status: " . ($data['status'] ?? 'unknown') . "\n";
echo "Field Key: " . ($data['fieldKey'] ?? 'none') . "\n";
echo "Fields Count: " . (isset($data['fields']) ? count($data['fields']) : 0) . "\n";
echo "Is Default: " . (isset($data['isDefault']) ? 'yes' : 'no') . "\n";
echo "Step Names: " . (isset($data['stepNames']) ? 'present' : 'not present') . "\n";
?>