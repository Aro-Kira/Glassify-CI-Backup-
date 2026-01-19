<?php
/**
 * Script to generate visual configurations for Windows customization defaults
 * Creates tagVisualConfigs for frame colors, glass types, and other visual elements
 */

function generateWindowsVisualConfigs() {
    // Load defaults from config file
    $defaultsFile = __DIR__ . '/../application/config/customization_defaults.php';
    if (!file_exists($defaultsFile)) {
        die("Error: Defaults file not found: $defaultsFile\n");
    }

    $defaults = require $defaultsFile;

    // Extract Windows field configurations
    $windowsFields = [
        'Windows_Sliding' => $defaults['Windows_Sliding'] ?? [],
        'Windows_Awning' => $defaults['Windows_Awning'] ?? [],
        'Windows_Casement' => $defaults['Windows_Casement'] ?? [],
        'Windows_Fixed Glass' => $defaults['Windows_Fixed Glass'] ?? []
    ];

    // Initialize visual configs
    $visualConfigs = [];

    // Define color mappings for frame colors
    $frameColorMap = [
        'Hanalok' => ['color' => '#F5F5DC', 'width' => 4], // Beige/cream
        'White' => ['color' => '#FFFFFF', 'width' => 4],
        'Black' => ['color' => '#000000', 'width' => 4],
        'Gray' => ['color' => '#808080', 'width' => 4],
        'Wood Finish' => ['color' => '#8B4513', 'width' => 4] // Brown wood
    ];

    // Define color mappings for glass types
    $glassColorMap = [
        'Clear' => ['fill' => 'rgba(173, 216, 230, 0.3)', 'opacity' => 0.8], // Light blue tint
        'Ultra Clear' => ['fill' => 'rgba(255, 255, 255, 0.1)', 'opacity' => 0.9], // Very clear
        'Bronze' => ['fill' => 'rgba(205, 127, 50, 0.4)', 'opacity' => 0.7],
        'Light Green' => ['fill' => 'rgba(144, 238, 144, 0.4)', 'opacity' => 0.7],
        'Dark Gray' => ['fill' => 'rgba(105, 105, 105, 0.5)', 'opacity' => 0.6],
        'Copperfree Mirror' => ['fill' => 'rgba(192, 192, 192, 0.8)', 'opacity' => 0.9],
        'Euro Gray' => ['fill' => 'rgba(169, 169, 169, 0.5)', 'opacity' => 0.7],
        'Ford Blue' => ['fill' => 'rgba(70, 130, 180, 0.5)', 'opacity' => 0.7],
        'Reflective: Clear' => ['fill' => 'rgba(255, 255, 255, 0.6)', 'opacity' => 0.9],
        'Reflective: Gray' => ['fill' => 'rgba(169, 169, 169, 0.6)', 'opacity' => 0.8],
        'Reflective: Light Blue' => ['fill' => 'rgba(173, 216, 230, 0.6)', 'opacity' => 0.8],
        'Reflective: Dark Blue' => ['fill' => 'rgba(0, 0, 139, 0.6)', 'opacity' => 0.8],
        'Reflective: Light Green' => ['fill' => 'rgba(50, 205, 50, 0.6)', 'opacity' => 0.8],
        'Reflective: Dark Green' => ['fill' => 'rgba(0, 100, 0, 0.6)', 'opacity' => 0.8],
        'Reflective: Light Bronze' => ['fill' => 'rgba(205, 127, 50, 0.6)', 'opacity' => 0.8],
        'Tempered: Clear' => ['fill' => 'rgba(255, 255, 255, 0.2)', 'opacity' => 0.9],
        'Tempered: Bronze' => ['fill' => 'rgba(205, 127, 50, 0.3)', 'opacity' => 0.8]
    ];

    // Process each Windows configuration
    foreach ($windowsFields as $categoryKey => $fields) {
        foreach ($fields as $field) {
            $fieldId = $field['id'];

            // Handle frame color field
            if ($fieldId === 'frameColor') {
                $visualConfigs[$fieldId] = [];
                foreach ($field['options'] as $option) {
                    if (isset($frameColorMap[$option])) {
                        $visualConfigs[$fieldId][$option] = $frameColorMap[$option];
                    }
                }
            }

            // Handle glass type field
            if ($fieldId === 'glassType') {
                $visualConfigs[$fieldId] = [];
                foreach ($field['options'] as $option) {
                    if (isset($glassColorMap[$option])) {
                        $visualConfigs[$fieldId][$option] = $glassColorMap[$option];
                    }
                }
            }

            // Handle panel configuration (for visual indication of sliding panels)
            if ($fieldId === 'panelConfiguration') {
                $visualConfigs[$fieldId] = [];
                foreach ($field['options'] as $option) {
                    // Different panel configurations get different visual styles
                    $visualConfigs[$fieldId][$option] = [
                        'pattern' => 'sliding',
                        'panels' => extractPanelConfig($option)
                    ];
                }
            }
        }
    }

    // Add some default visual configs for fields that might not be in the defaults
    $visualConfigs['numberOfPanels'] = [
        '2 Panels' => ['panels' => 2],
        '4 Panels' => ['panels' => 4]
    ];

    $visualConfigs['operation'] = [
        'Awning (crank-out)' => ['operation' => 'awning', 'direction' => 'outward'],
        'Awning (push-out)' => ['operation' => 'awning', 'direction' => 'outward'],
        'Casement (hinge side configurable)' => ['operation' => 'casement', 'hinge' => 'configurable']
    ];

    return $visualConfigs;
}

function extractPanelConfig($configString) {
    // Parse panel configuration strings like "S | S (Sliding | Sliding)" or "F | S | S | F (Fixed | Sliding | Sliding | Fixed)"
    $parts = explode('|', $configString);
    $panels = [];

    foreach ($parts as $part) {
        $part = trim($part);
        if (strpos($part, 'S') !== false) {
            $panels[] = 'sliding';
        } elseif (strpos($part, 'F') !== false) {
            $panels[] = 'fixed';
        } else {
            $panels[] = 'unknown';
        }
    }

    return $panels;
}

function generateVisualConfigJs($visualConfigs, $outputPath) {
    $jsContent = "// Generated visual configurations for Windows customization\n";
    $jsContent .= "// Generated on: " . date('Y-m-d H:i:s') . "\n\n";
    $jsContent .= "const windowsVisualConfigs = " . json_encode($visualConfigs, JSON_PRETTY_PRINT) . ";\n\n";
    $jsContent .= "// Export for use in other scripts\n";
    $jsContent .= "if (typeof module !== 'undefined' && module.exports) {\n";
    $jsContent .= "    module.exports = windowsVisualConfigs;\n";
    $jsContent .= "} else if (typeof window !== 'undefined') {\n";
    $jsContent .= "    window.windowsVisualConfigs = windowsVisualConfigs;\n";
    $jsContent .= "}\n";

    file_put_contents($outputPath, $jsContent);
    echo "Generated Windows visual configs file: $outputPath\n";
}

function updateKonvaCustomization($visualConfigs) {
    $konvaFile = __DIR__ . '/../assets/js/2d-functions/2d_customization.js';

    if (!file_exists($konvaFile)) {
        echo "Warning: Konva customization file not found: $konvaFile\n";
        return;
    }

    $content = file_get_contents($konvaFile);

    // Find the frameStyles and glassStyles definitions
    $frameStylesPattern = '/let frameStyles = \{[^}]*\};/s';
    $glassStylesPattern = '/let glassStyles = \{[^}]*\};/s';

    // Generate new frame styles from visual configs
    $newFrameStyles = "let frameStyles = {\n";
    if (isset($visualConfigs['frameColor'])) {
        foreach ($visualConfigs['frameColor'] as $colorName => $style) {
            $normalizedName = strtolower(str_replace([' ', '(', ')'], ['', '', ''], $colorName));
            $newFrameStyles .= "    '{$normalizedName}': { color: '{$style['color']}', width: {$style['width']} },\n";
        }
    }
    $newFrameStyles .= "};";

    // Generate new glass styles from visual configs
    $newGlassStyles = "let glassStyles = {\n";
    if (isset($visualConfigs['glassType'])) {
        foreach ($visualConfigs['glassType'] as $glassName => $style) {
            $normalizedName = strtolower(str_replace([' ', '(', ')', ':', '/'], ['', '', '', '', ''], $glassName));
            $newGlassStyles .= "    '{$normalizedName}': { fill: '{$style['fill']}', opacity: {$style['opacity']} },\n";
        }
    }
    $newGlassStyles .= "};";

    // Update the content
    $content = preg_replace($frameStylesPattern, $newFrameStyles, $content);
    $content = preg_replace($glassStylesPattern, $newGlassStyles, $content);

    file_put_contents($konvaFile, $content);
    echo "Updated Konva customization file with Windows visual configs\n";
}

// Main execution
echo "Generating Windows visual configurations...\n";
$visualConfigs = generateWindowsVisualConfigs();

echo "Found visual configurations for " . count($visualConfigs) . " fields\n";

echo "Generating JavaScript file...\n";
$jsOutputFile = __DIR__ . '/../assets/js/windows_visual_configs.js';
generateVisualConfigJs($visualConfigs, $jsOutputFile);

echo "Updating Konva customization file...\n";
updateKonvaCustomization($visualConfigs);

echo "Done!\n";
echo "\nGenerated visual configs summary:\n";
foreach ($visualConfigs as $fieldId => $configs) {
    echo "- $fieldId: " . count($configs) . " options\n";
}
?>

<?php
// Run the generation
generateWindowsVisualConfigs();
?>