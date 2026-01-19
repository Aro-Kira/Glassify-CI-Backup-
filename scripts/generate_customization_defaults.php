<?php
/**
 * Script to parse CUSTOMIZATION_REFERENCE.md and generate default customization fields
 */

function parseCustomizationReference($filePath) {
    if (!file_exists($filePath)) {
        die("Error: File $filePath not found\n");
    }

    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);

    $defaults = [];
    $currentCategory = '';
    $currentSubcategory = '';
    $stepNamesByCategory = []; // Store step names per category+subcategory
    $stepCounter = 0;

    foreach ($lines as $line) {
        $line = trim($line);

        // Main categories
        if (preg_match('/^## (.+)$/', $line, $matches)) {
            $currentCategory = $matches[1];
            continue;
        }

        // Subcategories
        if (preg_match('/^### (.+)$/', $line, $matches)) {
            $currentSubcategory = $matches[1];
            $stepCounter = 0;
            continue;
        }

        // Step names (extract from "**Step names:**" line)
        if (preg_match('/^\*\*Step names:\*\*\s*(.+)$/', $line, $matches)) {
            $stepNamesStr = $matches[1];
            $stepNames = explode('·', $stepNamesStr);
            $categoryKey = $currentCategory . '_' . $currentSubcategory;
            $stepNamesByCategory[$categoryKey] = [];
            foreach ($stepNames as $stepName) {
                $stepCounter++;
                $stepNamesByCategory[$categoryKey][$stepCounter] = trim($stepName);
            }
            continue;
        }

        // Table headers (skip)
        if (strpos($line, '| --- ') !== false) {
            continue;
        }

        // Table row with field data
        if (preg_match('/^\| (.+?) \| (.+?) \| `(.+?)` \| (.+?) \|$/', $line, $matches)) {
            $stepField = trim($matches[1]);
            $fieldName = trim($matches[2]);
            $controlType = trim($matches[3]);
            $optionsStr = trim($matches[4]);

            // Skip table headers and separators
            if ($stepField === 'Step' || $stepField === '---') {
                continue;
            }

            // Parse step number
            if (preg_match('/\((\d+)\)$/', $stepField, $stepMatch)) {
                $stepNumber = (int)$stepMatch[1];
            } else {
                echo "Skipping row - no step number found in '$stepField'\n";
                continue; // Skip if no step number
            }

            // Generate field key
            $prefixMap = [
                'Windows' => 'Windows',
                'Doors' => 'Doors',
                'Glass Partitions & Enclosures' => 'Partitions',
                'Mirrors & Specialty Glass' => 'Specialty',
                'Commercial & Exterior' => 'Commercial'
            ];
            $prefix = $prefixMap[$currentCategory] ?? '';
            $fieldKey = $prefix ? "{$prefix}_{$currentSubcategory}" : $currentSubcategory;

            if (!isset($defaults[$fieldKey])) {
                $defaults[$fieldKey] = [];
            }

            // Parse options
            $options = [];
            if ($controlType === 'tags') {
                // For complex options, manually parse them based on the pattern
                $cleanOptionsStr = trim($optionsStr);

                // Handle special case for Panel Configuration which has complex nested options
                if (strpos($cleanOptionsStr, 'S \\| S (Sliding \\| Sliding)') !== false) {
                    // Manually define the expected options for this complex case
                    $options = [
                        'S | S (Sliding | Sliding)',
                        'F | S (Fixed | Sliding)',
                        'S | S | S | S (All Sliding)',
                        'F | S | S | F (Fixed | Sliding | Sliding | Fixed)'
                    ];
                } else {
                    // For simpler cases, split on escaped pipes
                    $rawOptions = preg_split('/\\\\\\|/', $cleanOptionsStr);

                    foreach ($rawOptions as $option) {
                        $cleanOption = trim($option);
                        // Remove trailing backslashes and convert escaped pipes back to regular pipes
                        $cleanOption = rtrim($cleanOption, '\\');
                        $cleanOption = str_replace('\\|', '|', $cleanOption);
                        $cleanOption = trim($cleanOption);
                        if (!empty($cleanOption)) {
                            $options[] = $cleanOption;
                        }
                    }
                }
            } elseif ($controlType === 'number') {
                // Parse min and step from options like "min 1 · step 0.1"
                $params = [];
                if (preg_match('/min (\d+(?:\.\d+)?)/', $optionsStr, $minMatch)) {
                    $params['min'] = (float)$minMatch[1];
                }
                if (preg_match('/step (\d+(?:\.\d+)?)/', $optionsStr, $stepMatch)) {
                    $params['step'] = (float)$stepMatch[1];
                }
            }

            // Create field definition
            $field = [
                'type' => $controlType,
                'label' => $fieldName,
                'id' => generateFieldId($fieldName),
                'stepNumber' => $stepNumber
            ];

            if ($controlType === 'tags' && !empty($options)) {
                $field['options'] = $options;
            } elseif ($controlType === 'number' && !empty($params)) {
                $field = array_merge($field, $params);
            } elseif ($controlType === 'checkbox') {
                // Checkbox doesn't need additional options
            }

            $defaults[$fieldKey][] = $field;
        }
    }

    // Add step names
    foreach ($stepNamesByCategory as $categoryKey => $stepNames) {
        // Convert category key to field key format
        $parts = explode('_', $categoryKey, 2);
        if (count($parts) === 2) {
            $category = $parts[0];
            $subcategory = $parts[1];

            $prefixMap = [
                'Windows' => 'Windows',
                'Doors' => 'Doors',
                'Glass Partitions & Enclosures' => 'Partitions',
                'Mirrors & Specialty Glass' => 'Specialty',
                'Commercial & Exterior' => 'Commercial'
            ];
            $prefix = $prefixMap[$category] ?? '';
            $fieldKey = $prefix ? "{$prefix}_{$subcategory}" : $subcategory;
            $stepNamesKey = "{$fieldKey}_stepNames";

            if (isset($defaults[$fieldKey])) {
                $defaults[$stepNamesKey] = $stepNames;
            }
        }
    }

    return $defaults;
}

function generateFieldId($fieldName) {
    // Convert field name to camelCase id
    $id = preg_replace('/[^a-zA-Z0-9\s]/', '', $fieldName);
    $id = lcfirst(str_replace(' ', '', ucwords(strtolower($id))));
    return $id;
}

function generateDefaultsFile($defaults, $outputPath) {
    $phpContent = "<?php\n";
    $phpContent .= "/**\n";
    $phpContent .= " * Generated customization defaults from CUSTOMIZATION_REFERENCE.md\n";
    $phpContent .= " * Generated on: " . date('Y-m-d H:i:s') . "\n";
    $phpContent .= " */\n\n";
    $phpContent .= "return " . var_export($defaults, true) . ";\n";

    file_put_contents($outputPath, $phpContent);
    echo "Generated defaults file: $outputPath\n";
}

function generateJsDefaults($defaults, $outputPath) {
    $jsContent = "// Generated customization defaults from CUSTOMIZATION_REFERENCE.md\n";
    $jsContent .= "// Generated on: " . date('Y-m-d H:i:s') . "\n\n";
    $jsContent .= "const generatedCustomizationDefaults = " . json_encode($defaults, JSON_PRETTY_PRINT) . ";\n\n";
    $jsContent .= "export default generatedCustomizationDefaults;\n";

    file_put_contents($outputPath, $jsContent);
    echo "Generated JS defaults file: $outputPath\n";
}

// Main execution
$referenceFile = __DIR__ . '/../docs/CUSTOMIZATION_REFERENCE.md';
$phpOutputFile = __DIR__ . '/../application/config/customization_defaults.php';
$jsOutputFile = __DIR__ . '/../assets/js/customization_defaults.js';

echo "Parsing customization reference...\n";
$defaults = parseCustomizationReference($referenceFile);

echo "Found " . count(array_filter(array_keys($defaults), function($key) { return strpos($key, '_stepNames') === false; })) . " field configurations\n";

echo "Generating PHP defaults file...\n";
generateDefaultsFile($defaults, $phpOutputFile);

echo "Generating JavaScript defaults file...\n";
generateJsDefaults($defaults, $jsOutputFile);

echo "Done!\n";
?>