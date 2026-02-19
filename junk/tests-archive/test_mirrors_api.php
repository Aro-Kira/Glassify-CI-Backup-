<?php
// Test mirrors customization fields API
$ch = curl_init('http://localhost/customizationFields/get?category=Mirrors%20%26%20Specialty%20Glass&subcategory=Mirrors');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo 'Status: ' . ($data['status'] ?? 'unknown') . PHP_EOL;
echo 'Fields count: ' . (isset($data['fields']) ? count($data['fields']) : 0) . PHP_EOL;
if (isset($data['fields']) && count($data['fields']) > 0) {
    echo 'First field: ' . ($data['fields'][0]['label'] ?? 'unknown') . PHP_EOL;
    echo 'Field key: ' . ($data['fieldKey'] ?? 'unknown') . PHP_EOL;
}
echo 'Response: ' . $response . PHP_EOL;
?>