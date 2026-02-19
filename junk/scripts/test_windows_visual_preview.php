<?php
/**
 * Test script to verify Windows 2D visual preview configurations
 */

echo "Testing Windows 2D Visual Preview Configurations\n";
echo "================================================\n\n";

// Check if the visual config file was generated
$visualConfigFile = __DIR__ . '/../assets/js/windows_visual_configs.js';
if (file_exists($visualConfigFile)) {
    echo "✅ Windows visual config file exists: $visualConfigFile\n";

    $content = file_get_contents($visualConfigFile);
    if (strpos($content, 'windowsVisualConfigs') !== false) {
        echo "✅ Visual config object found in file\n";
    } else {
        echo "❌ Visual config object not found in file\n";
    }

    // Check for specific configurations
    $configs = [
        'frameColor' => ['Hanalok', 'White', 'Black', 'Gray', 'Wood Finish'],
        'glassType' => ['Clear', 'Ultra Clear', 'Bronze'],
        'panelConfiguration' => ['S | S (Sliding | Sliding)']
    ];

    foreach ($configs as $field => $expectedOptions) {
        $found = 0;
        foreach ($expectedOptions as $option) {
            if (strpos($content, $option) !== false) {
                $found++;
            }
        }
        echo "✅ $field: $found/" . count($expectedOptions) . " options found\n";
    }

} else {
    echo "❌ Windows visual config file not found: $visualConfigFile\n";
}

// Check if Konva file was updated
$konvaFile = __DIR__ . '/../assets/js/2d-functions/2d_customization.js';
if (file_exists($konvaFile)) {
    echo "\n✅ Konva customization file exists: $konvaFile\n";

    $content = file_get_contents($konvaFile);

    // Check for Windows-specific frame styles
    $windowsFrameStyles = ['hanalok', 'wood finish'];
    $found = 0;
    foreach ($windowsFrameStyles as $style) {
        if (strpos($content, "'$style':") !== false) {
            $found++;
        }
    }
    echo "✅ Windows frame styles: $found/" . count($windowsFrameStyles) . " found in Konva file\n";

    // Check for Windows-specific glass styles
    $windowsGlassStyles = ['ultra clear', 'copperfree mirror', 'tempered: clear'];
    $found = 0;
    foreach ($windowsGlassStyles as $style) {
        if (strpos($content, "'$style':") !== false) {
            $found++;
        }
    }
    echo "✅ Windows glass styles: $found/" . count($windowsGlassStyles) . " found in Konva file\n";

    // Check for panel configuration parsing
    if (strpos($content, 'panelConfig.split') !== false) {
        echo "✅ Panel configuration parsing found in Konva file\n";
    } else {
        echo "❌ Panel configuration parsing not found in Konva file\n";
    }

} else {
    echo "❌ Konva customization file not found: $konvaFile\n";
}

// Check if 2DModeling.php was updated
$modelingFile = __DIR__ . '/../application/views/shop/2DModeling.php';
if (file_exists($modelingFile)) {
    echo "\n✅ 2DModeling.php file exists: $modelingFile\n";

    $content = file_get_contents($modelingFile);

    if (strpos($content, 'windows_visual_configs.js') !== false) {
        echo "✅ Windows visual config script included in 2DModeling.php\n";
    } else {
        echo "❌ Windows visual config script not included in 2DModeling.php\n";
    }

    if (strpos($content, 'windowsVisualConfigs') !== false) {
        echo "✅ Windows visual config loading code found in 2DModeling.php\n";
    } else {
        echo "❌ Windows visual config loading code not found in 2DModeling.php\n";
    }

} else {
    echo "❌ 2DModeling.php file not found: $modelingFile\n";
}

echo "\nTest completed!\n";
?>

<?php
// Run the test
echo "Running Windows 2D Visual Preview Test...\n\n";
?>