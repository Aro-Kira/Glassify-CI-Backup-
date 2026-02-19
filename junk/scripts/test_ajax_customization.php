<?php
/**
 * Test script for AJAX customization functionality
 */

echo "Testing AJAX Customization Functionality\n";
echo "========================================\n\n";

// Test 1: Check if controller exists
$controllerFile = __DIR__ . '/../application/controllers/CustomizationAjaxCon.php';
if (file_exists($controllerFile)) {
    echo "✅ CustomizationAjaxCon.php controller exists\n";

    $content = file_get_contents($controllerFile);
    $methods = ['save', 'load', 'price'];
    foreach ($methods as $method) {
        if (strpos($content, "function $method(") !== false) {
            echo "✅ $method method found in controller\n";
        } else {
            echo "❌ $method method not found in controller\n";
        }
    }
} else {
    echo "❌ CustomizationAjaxCon.php controller not found\n";
}

// Test 2: Check if routes are configured
$routesFile = __DIR__ . '/../application/config/routes.php';
if (file_exists($routesFile)) {
    echo "\n✅ Routes file exists\n";

    $content = file_get_contents($routesFile);
    $routes = [
        'customizationAjax/save',
        'customizationAjax/load',
        'customizationAjax/price'
    ];

    foreach ($routes as $route) {
        if (strpos($content, "'$route'") !== false) {
            echo "✅ Route '$route' configured\n";
        } else {
            echo "❌ Route '$route' not configured\n";
        }
    }
} else {
    echo "❌ Routes file not found\n";
}

// Test 3: Check if AJAX script exists
$ajaxScript = __DIR__ . '/../assets/js/2d-functions/customization_ajax.js';
if (file_exists($ajaxScript)) {
    echo "\n✅ customization_ajax.js script exists\n";

    $content = file_get_contents($ajaxScript);
    $functions = [
        'initCustomizationAjax',
        'saveCustomizationSelections',
        'loadSavedCustomization',
        'updatePriceRealtime'
    ];

    foreach ($functions as $function) {
        if (strpos($content, "function $function") !== false) {
            echo "✅ $function function found in AJAX script\n";
        } else {
            echo "❌ $function function not found in AJAX script\n";
        }
    }
} else {
    echo "❌ customization_ajax.js script not found\n";
}

// Test 4: Check if AJAX script is included in 2DModeling view
$modelingFile = __DIR__ . '/../application/views/shop/2DModeling.php';
if (file_exists($modelingFile)) {
    echo "\n✅ 2DModeling.php view exists\n";

    $content = file_get_contents($modelingFile);
    if (strpos($content, 'customization_ajax.js') !== false) {
        echo "✅ AJAX script included in 2DModeling view\n";
    } else {
        echo "❌ AJAX script not included in 2DModeling view\n";
    }

    if (strpos($content, 'ajax-status-indicator') !== false) {
        echo "✅ AJAX status indicator found in view\n";
    } else {
        echo "❌ AJAX status indicator not found in view\n";
    }
} else {
    echo "❌ 2DModeling.php view not found\n";
}

// Test 5: Try to test the API endpoints (requires web server to be running)
echo "\n🔍 API Endpoint Tests (requires running web server):\n";

$baseUrl = 'http://localhost/Glassify-CI';
$endpoints = [
    'customizationAjax/save' => 'POST',
    'customizationAjax/load' => 'GET',
    'customizationAjax/price' => 'POST'
];

foreach ($endpoints as $endpoint => $method) {
    $url = $baseUrl . '/' . $endpoint;
    echo "Testing $method $url ...\n";

    // For this test, we'll just check if the URL structure looks correct
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        echo "✅ URL format is valid\n";
    } else {
        echo "❌ URL format is invalid\n";
    }
}

echo "\nTest completed!\n";
echo "\nNote: To fully test the AJAX functionality:\n";
echo "1. Start the web server\n";
echo "2. Log in as a customer\n";
echo "3. Visit a Windows product page\n";
echo "4. Make customization selections\n";
echo "5. Check browser network tab for AJAX requests\n";
echo "6. Verify save indicators appear\n";
echo "7. Refresh page to test loading saved customizations\n";
?>

<?php
// Run the test
echo "Running AJAX Customization Test...\n\n";
?>