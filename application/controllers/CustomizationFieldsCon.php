<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customization Fields API Controller
 * Handles CRUD operations for customization field configurations
 */
class CustomizationFieldsCon extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
        header('Content-Type: application/json');
    }

    /**
     * Get field configurations for a category/subcategory
     * GET /customizationFields/get?category=Windows&subcategory=Sliding
     * or GET /customizationFields/get?fieldKey=Windows_Sliding
     */
    public function get()
    {
        $category = $this->input->get('category');
        $subcategory = $this->input->get('subcategory');
        $fieldKey = $this->input->get('fieldKey');

        // Build field key if not provided
        if (!$fieldKey && $category && $subcategory) {
            $prefixMap = [
                'Windows' => 'Windows',
                'Doors' => 'Doors',
                'Glass Partitions & Enclosures' => 'Partitions',
                'Mirrors & Specialty Glass' => 'Specialty',
                'Commercial & Exterior' => 'Commercial'
            ];
            $prefix = $prefixMap[$category] ?? '';
            $fieldKey = $prefix ? "{$prefix}_{$subcategory}" : $subcategory;
        }

        if (!$fieldKey) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing required parameters: fieldKey or (category and subcategory)'
            ]);
            return;
        }

        // Check if table exists
        if (!$this->db->table_exists('customization_field_configs')) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Table customization_field_configs does not exist. Please run the database migration script.',
                'fields' => []
            ]);
            return;
        }

        $this->db->where('FieldKey', $fieldKey);
        $config = $this->db->get('customization_field_configs')->row();

        if ($config && !empty($config->FieldConfig)) {
            $decoded = json_decode($config->FieldConfig, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid JSON in FieldConfig: ' . json_last_error_msg(),
                    'fields' => []
                ]);
                return;
            }

            // FieldConfig should be an array of field objects
            // Filter out any non-field entries (like stepNames keys)
            $fields = [];
            if (is_array($decoded)) {
                foreach ($decoded as $key => $value) {
                    // If key is numeric or value has 'type' property, it's a field
                    if (is_numeric($key) && is_array($value) && isset($value['type'])) {
                        $fields[] = $value;
                    } elseif (is_string($key) && strpos($key, '_stepNames') === false && is_array($value) && isset($value['type'])) {
                        // Handle case where it's an associative array but value is a field
                        $fields[] = $value;
                    }
                }

                // If no fields found but decoded is an array of arrays with 'type', use it directly
                if (empty($fields) && isset($decoded[0]) && is_array($decoded[0]) && isset($decoded[0]['type'])) {
                    $fields = $decoded;
                }
            }

            // Get step names if available
            $stepNamesKey = $fieldKey . '_stepNames';
            $stepNamesConfig = $this->db->where('FieldKey', $stepNamesKey)->get('customization_field_configs')->row();
            $stepNames = null;
            if ($stepNamesConfig && !empty($stepNamesConfig->FieldConfig)) {
                $stepNames = json_decode($stepNamesConfig->FieldConfig, true);
            }

            echo json_encode([
                'status' => 'success',
                'fieldKey' => $fieldKey,
                'fields' => $fields,
                'stepNames' => $stepNames
            ]);
        } else {
            // Return default fields if not found
            $defaultFields = $this->getDefaultFields($fieldKey);
            $stepNames = $this->getDefaultStepNames($fieldKey);
            echo json_encode([
                'status' => 'success',
                'fieldKey' => $fieldKey,
                'fields' => $defaultFields,
                'stepNames' => $stepNames,
                'isDefault' => true
            ]);
        }
    }

    /**
     * Save/Update field configurations
     * POST /customizationFields/save
     * Body: { fieldKey: "Windows_Sliding", fields: [...] }
     */
    public function save()
    {
        $fieldKey = $this->input->post('fieldKey');
        $category = $this->input->post('category');
        $subcategory = $this->input->post('subcategory');
        $fields = $this->input->post('fields');
        $stepNames = $this->input->post('stepNames');

        if (!$fieldKey) {
            if ($category && $subcategory) {
                $prefixMap = [
                    'Windows' => 'Windows',
                    'Doors' => 'Doors',
                    'Glass Partitions & Enclosures' => 'Partitions',
                    'Mirrors & Specialty Glass' => 'Specialty',
                    'Commercial & Exterior' => 'Commercial'
                ];
                $prefix = $prefixMap[$category] ?? '';
                $fieldKey = $prefix ? "{$prefix}_{$subcategory}" : $subcategory;
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Missing required parameter: fieldKey or (category and subcategory)'
                ]);
                return;
            }
        }

        // Handle both array and JSON string formats
        if (is_string($fields)) {
            $decoded = json_decode($fields, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $fields = $decoded;
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid JSON string in fields: ' . json_last_error_msg()
                ]);
                return;
            }
        }
        
        if (!$fields || !is_array($fields)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing or invalid fields array'
            ]);
            return;
        }

        // Check if table exists
        if (!$this->db->table_exists('customization_field_configs')) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Table customization_field_configs does not exist. Please run the database migration script: database/scripts/add_customization_fields_tables.sql'
            ]);
            return;
        }

        // Validate JSON
        $fieldsJson = json_encode($fields);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid JSON: ' . json_last_error_msg()
            ]);
            return;
        }

        // Check if config exists
        $this->db->where('FieldKey', $fieldKey);
        $existing = $this->db->get('customization_field_configs')->row();

        if ($existing) {
            // Update
            $this->db->where('FieldKey', $fieldKey);
            $this->db->update('customization_field_configs', [
                'FieldConfig' => $fieldsJson,
                'Updated_Date' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Insert
            $this->db->insert('customization_field_configs', [
                'Category' => $category ?? '',
                'Subcategory' => $subcategory ?? '',
                'FieldKey' => $fieldKey,
                'FieldConfig' => $fieldsJson
            ]);
        }

        // Save step names if provided
        if ($stepNames && is_array($stepNames)) {
            $stepNamesKey = $fieldKey . '_stepNames';
            $stepNamesJson = json_encode($stepNames);

            $this->db->where('FieldKey', $stepNamesKey);
            $existingStepNames = $this->db->get('customization_field_configs')->row();

            if ($existingStepNames) {
                $this->db->where('FieldKey', $stepNamesKey);
                $this->db->update('customization_field_configs', [
                    'FieldConfig' => $stepNamesJson,
                    'Updated_Date' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->insert('customization_field_configs', [
                    'Category' => $category ?? '',
                    'Subcategory' => $subcategory ?? '',
                    'FieldKey' => $stepNamesKey,
                    'FieldConfig' => $stepNamesJson
                ]);
            }
        }

        // Update the documentation file if it exists (don't let errors break the save)
        try {
            $this->updateDocumentation($fieldKey, $fields, $category, $subcategory, $stepNames);
        } catch (Exception $e) {
            // Log error but don't fail the save
            log_message('error', 'Error updating documentation: ' . $e->getMessage());
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Field configurations saved successfully',
            'fieldKey' => $fieldKey
        ]);
    }

    /**
     * Get all field configurations
     * GET /customizationFields/getAll
     */
    public function getAll()
    {
        // Check if table exists
        if (!$this->db->table_exists('customization_field_configs')) {
            echo json_encode([
                'status' => 'success',
                'configs' => [],
                'message' => 'Table customization_field_configs does not exist. Using defaults.'
            ]);
            return;
        }

        $configs = $this->db->get('customization_field_configs')->result();
        $result = [];

        foreach ($configs as $config) {
            $result[$config->FieldKey] = [
                'category' => $config->Category,
                'subcategory' => $config->Subcategory,
                'fields' => json_decode($config->FieldConfig, true) ?: []
            ];
        }

        echo json_encode([
            'status' => 'success',
            'configs' => $result
        ]);
    }

    /**
     * Seed or update database with correct field configurations
     * This ensures admin and customer pages are always in sync
     * Access via: /customizationFields/seed
     */
    public function seed()
    {
        $fields = [
            "Windows_Sliding" => [
                ["type" => "tags", "label" => "Panel", "id" => "numberOfPanels", "options" => ["2 Panels", "4 Panels"], "stepNumber" => 1],
                ["type" => "tags", "label" => "Transom Type", "id" => "transomType", "options" => ["Fixed Transom Head (fixed glass at top)", "Fixed Transom Sill (fixed glass at bottom)", "None"], "stepNumber" => 1],
                ["type" => "tags", "label" => "Track System (Sliding Rail Count)", "id" => "trackSystem", "options" => ["2 Tracks", "3 Tracks"], "stepNumber" => 2],
                ["type" => "tags", "label" => "Screen Option", "id" => "screenOption", "options" => ["With Screen", "Without Screen"], "stepNumber" => 2],
                ["type" => "tags", "label" => "Panel Configuration", "id" => "panelConfiguration", "options" => ["S | S (Sliding | Sliding)", "F | S (Fixed | Sliding)", "S | S | S | S (All Sliding)", "F | S | S | F (Fixed | Sliding | Sliding | Fixed)"], "stepNumber" => 2],
                ["type" => "tags", "label" => "Frame Color", "id" => "frameColor", "options" => ["Hanalok", "White", "Black", "Gray", "Wood Finish"], "stepNumber" => 3],
                ["type" => "tags", "label" => "Glass Type (6mm thickness only)", "id" => "glassType", "options" => ["Clear", "Ultra Clear", "Bronze", "Light Green", "Dark Gray", "Euro Gray", "Ford Blue", "Copperfree Mirror", "Reflective: Clear", "Reflective: Gray", "Reflective: Light Blue", "Reflective: Dark Blue", "Reflective: Light Green", "Reflective: Dark Green", "Reflective: Light Bronze", "Tempered: Clear", "Tempered: Bronze"], "stepNumber" => 3],
                ["type" => "tags", "label" => "Glass Thickness", "id" => "glassThickness", "options" => ["6mm"], "stepNumber" => 3],
                ["type" => "tags", "label" => "Lock Type", "id" => "lockType", "options" => ["Enter Lock 908", "Enter Lock 907", "Flushlock #12", "New Flushlock", "Center Lok 904 Big", "Flushlok #12", "Durable Flushlok", "New Auto Flushlock"], "stepNumber" => 4],
                ["type" => "tags", "label" => "Roller Type", "id" => "rollerType", "options" => ["Single Roller ORD", "Single Roller with Bearing", "Double Roller HD", "Blue Single Roller", "Blue Double Roller", "Single Panel Roller", "Blue Single Roller", "Blue Double Roller"], "stepNumber" => 4],
                ["type" => "tags", "label" => "Screen", "id" => "screen", "options" => ["With Screen", "Without Screen"], "stepNumber" => 4]
            ]
        ];

        $stepNames = [
            "Windows_Sliding_stepNames" => [
                "1" => "WINDOW TYPE",
                "2" => "SLIDING SYSTEM & SIZE",
                "3" => "FRAME & GLASS",
                "4" => "HARDWARE & ACCESSORIES"
            ]
        ];

        $count = 0;
        foreach ($fields as $fieldKey => $config) {
            $parts = explode('_', $fieldKey);
            $category = $parts[0];
            $subcategory = isset($parts[1]) ? $parts[1] : '';
            
            $prefixMap = [
                'Windows' => 'Windows',
                'Doors' => 'Doors',
                'Partitions' => 'Glass Partitions & Enclosures',
                'Specialty' => 'Mirrors & Specialty Glass',
                'Cabinets' => 'Cabinets & Furniture',
                'Commercial' => 'Commercial & Exterior'
            ];
            $category = $prefixMap[$category] ?? $category;

            $dataToSave = $config;
            if (isset($stepNames[$fieldKey . "_stepNames"])) {
                $dataToSave['stepNames'] = $stepNames[$fieldKey . "_stepNames"];
            }

            $this->db->where('FieldKey', $fieldKey);
            $existing = $this->db->get('customization_field_configs')->row();

            $saveData = [
                'FieldKey' => $fieldKey,
                'Category' => $category,
                'Subcategory' => $subcategory,
                'FieldConfig' => json_encode($dataToSave),
                'LastUpdated' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                $this->db->where('FieldKey', $fieldKey);
                $this->db->update('customization_field_configs', $saveData);
            } else {
                $this->db->insert('customization_field_configs', $saveData);
            }
            $count++;
        }

        echo json_encode(['status' => 'success', 'message' => "Seeded $count field configurations"]);
    }

    /**
     * Get default field configurations
     */
    private function getDefaultFields($fieldKey)
    {
        // Preferred: load defaults from the JSON file used across the app
        // This keeps admin + customer defaults in sync even when DB has no entry yet.
        $jsonDefaults = $this->loadJsonDefaults();
        if (is_array($jsonDefaults) && isset($jsonDefaults[$fieldKey]) && is_array($jsonDefaults[$fieldKey])) {
            return $jsonDefaults[$fieldKey];
        }

        // Load generated defaults from the config file (preferred method)
        $defaultsFile = APPPATH . 'config/customization_defaults.php';
        if (file_exists($defaultsFile)) {
            $generatedDefaults = require $defaultsFile;
            if (isset($generatedDefaults[$fieldKey])) {
                return $generatedDefaults[$fieldKey];
            }
        }

        // Fallback to hardcoded defaults if config file doesn't exist or field not found
        $defaultFields = [
            'Windows_Sliding' => [
                ['type' => 'tags', 'label' => 'Panel', 'id' => 'numberOfPanels', 'options' => ['2 Panels', '4 Panels'], 'stepNumber' => 1],
                ['type' => 'tags', 'label' => 'Transom Type', 'id' => 'transomType', 'options' => ['Fixed Transom Head (fixed glass at top)', 'Fixed Transom Sill (fixed glass at bottom)', 'None'], 'stepNumber' => 1],
                ['type' => 'tags', 'label' => 'Track System (Sliding Rail Count)', 'id' => 'trackSystem', 'options' => ['2 Tracks', '3 Tracks'], 'stepNumber' => 2],
                ['type' => 'tags', 'label' => 'Screen Option', 'id' => 'screenOption', 'options' => ['With Screen', 'Without Screen'], 'stepNumber' => 2],
                ['type' => 'tags', 'label' => 'Panel Configuration', 'id' => 'panelConfiguration', 'options' => ['S | S (Sliding | Sliding)', 'F | S (Fixed | Sliding)', 'S | S | S | S (All Sliding)', 'F | S | S | F (Fixed | Sliding | Sliding | Fixed)'], 'stepNumber' => 2],
                ['type' => 'tags', 'label' => 'Frame Color', 'id' => 'frameColor', 'options' => ['Hanalok', 'White', 'Black', 'Gray', 'Wood Finish'], 'stepNumber' => 3],
                ['type' => 'tags', 'label' => 'Glass Type (6mm thickness only)', 'id' => 'glassType', 'options' => ['Clear', 'Ultra Clear', 'Bronze', 'Light Green', 'Dark Gray', 'Euro Gray', 'Ford Blue', 'Copperfree Mirror', 'Reflective: Clear', 'Reflective: Gray', 'Reflective: Light Blue', 'Reflective: Dark Blue', 'Reflective: Light Green', 'Reflective: Dark Green', 'Reflective: Light Bronze', 'Tempered: Clear', 'Tempered: Bronze'], 'stepNumber' => 3],
                ['type' => 'tags', 'label' => 'Glass Thickness', 'id' => 'glassThickness', 'options' => ['6mm'], 'stepNumber' => 3],
                ['type' => 'tags', 'label' => 'Lock Type', 'id' => 'lockType', 'options' => ['Enter Lock 908', 'Enter Lock 907', 'Flushlock #12', 'New Flushlock', 'Center Lok 904 Big', 'Flushlok #12', 'Durable Flushlok', 'New Auto Flushlock'], 'stepNumber' => 4],
                ['type' => 'tags', 'label' => 'Roller Type', 'id' => 'rollerType', 'options' => ['Single Roller ORD', 'Single Roller with Bearing', 'Double Roller HD', 'Blue Single Roller', 'Blue Double Roller', 'Single Panel Roller', 'Blue Single Roller', 'Blue Double Roller'], 'stepNumber' => 4],
                ['type' => 'tags', 'label' => 'Screen', 'id' => 'screen', 'options' => ['With Screen', 'Without Screen'], 'stepNumber' => 4]
            ],
            'Windows_Awning' => [
                ['type' => 'tags', 'label' => 'Glass Type', 'id' => 'glassType', 'options' => ['Clear', 'Tinted', 'Laminated']],
                ['type' => 'tags', 'label' => 'Frame Color/Material', 'id' => 'frameColor', 'options' => ['White', 'Black', 'Silver', 'Bronze', 'Wood', 'Aluminum']],
                ['type' => 'number', 'label' => 'Thickness (mm)', 'id' => 'thickness', 'min' => 1, 'step' => 0.1],
                ['type' => 'checkbox', 'label' => 'Screen', 'id' => 'screen']
            ],
            'Windows_Casement' => [
                ['type' => 'tags', 'label' => 'Glass Type', 'id' => 'glassType', 'options' => ['Clear', 'Tinted', 'Laminated']],
                ['type' => 'tags', 'label' => 'Frame Color/Material', 'id' => 'frameColor', 'options' => ['White', 'Black', 'Silver', 'Bronze', 'Wood', 'Aluminum']],
                ['type' => 'number', 'label' => 'Thickness (mm)', 'id' => 'thickness', 'min' => 1, 'step' => 0.1],
                ['type' => 'checkbox', 'label' => 'Screen', 'id' => 'screen']
            ],
            'Windows_Fixed Glass' => [
                ['type' => 'tags', 'label' => 'Glass Type', 'id' => 'glassType', 'options' => ['Clear', 'Tinted', 'Laminated']],
                ['type' => 'tags', 'label' => 'Frame Color/Material', 'id' => 'frameColor', 'options' => ['White', 'Black', 'Silver', 'Bronze', 'Wood', 'Aluminum']],
                ['type' => 'number', 'label' => 'Thickness (mm)', 'id' => 'thickness', 'min' => 1, 'step' => 0.1],
                ['type' => 'checkbox', 'label' => 'Screen', 'id' => 'screen']
            ],
            'Doors_Sliding' => [
                ['type' => 'tags', 'label' => 'Glass Type', 'id' => 'glassType', 'options' => ['Clear', 'Tinted', 'Laminated']],
                ['type' => 'tags', 'label' => 'Handle Type', 'id' => 'handleType', 'options' => ['Type A', 'Type B', 'Type C']],
                ['type' => 'tags', 'label' => 'Lock Type', 'id' => 'lockType', 'options' => ['Type A', 'Type B', 'Type C']],
                ['type' => 'checkbox', 'label' => 'Soft-close', 'id' => 'softClose']
            ],
            'Doors_Frameless' => [
                ['type' => 'tags', 'label' => 'Glass Type', 'id' => 'glassType', 'options' => ['Clear', 'Tinted', 'Laminated']],
                ['type' => 'tags', 'label' => 'Handle Type', 'id' => 'handleType', 'options' => ['Type A', 'Type B', 'Type C']],
                ['type' => 'tags', 'label' => 'Lock Type', 'id' => 'lockType', 'options' => ['Type A', 'Type B', 'Type C']],
                ['type' => 'checkbox', 'label' => 'Soft-close', 'id' => 'softClose']
            ],
            'Partitions_Frameless Glass' => [
                ['type' => 'tags', 'label' => 'Layout', 'id' => 'layout', 'options' => ['L-shape', 'Straight', 'U-shape']],
                ['type' => 'number', 'label' => 'Glass Thickness (mm)', 'id' => 'glassThickness', 'min' => 1, 'step' => 0.1],
                ['type' => 'tags', 'label' => 'Finish', 'id' => 'finish', 'options' => ['Clear', 'Frosted', 'Patterned']],
                ['type' => 'tags', 'label' => 'Hardware Color', 'id' => 'hardwareColor', 'options' => ['Black', 'Silver', 'Gold', 'White', 'Bronze']]
            ],
            'Partitions_Shower Enclosure' => [
                ['type' => 'tags', 'label' => 'Layout', 'id' => 'layout', 'options' => ['L-shape', 'Straight', 'U-shape']],
                ['type' => 'number', 'label' => 'Glass Thickness (mm)', 'id' => 'glassThickness', 'min' => 1, 'step' => 0.1],
                ['type' => 'tags', 'label' => 'Finish', 'id' => 'finish', 'options' => ['Clear', 'Frosted', 'Patterned']],
                ['type' => 'tags', 'label' => 'Hardware Color', 'id' => 'hardwareColor', 'options' => ['Black', 'Silver', 'Gold', 'White', 'Bronze']]
            ],
            'Specialty_Mirrors' => [
                ['type' => 'tags', 'label' => 'Shape', 'id' => 'shape', 'options' => ['Round', 'Rectangle', 'Oval']],
                ['type' => 'tags', 'label' => 'Edge Finish', 'id' => 'edgeFinish', 'options' => ['Beveled', 'Polished', 'Raw']],
                ['type' => 'tags', 'label' => 'Mounting Method', 'id' => 'mountingMethod', 'options' => ['Wall-mounted', 'Stand', 'Adhesive']]
            ],
            'Specialty_Top Glass' => [
                ['type' => 'tags', 'label' => 'Shape', 'id' => 'shape', 'options' => ['Round', 'Rectangle', 'Oval', 'Square', 'Custom shapes'], 'stepNumber' => 1],
                ['type' => 'tags', 'label' => 'Edge Finish', 'id' => 'edgeFinish', 'options' => ['Beveled', 'Polished', 'Raw', 'Beveled edge', 'Flat polished edge', 'Pencil edge'], 'stepNumber' => 1],
                ['type' => 'tags', 'label' => 'Mounting Method', 'id' => 'mountingMethod', 'options' => ['Wall-mounted', 'Stand', 'Adhesive'], 'stepNumber' => 2]
            ],
            'Specialty_Glass Board' => [
                ['type' => 'tags', 'label' => 'Shape', 'id' => 'shape', 'options' => ['Round', 'Rectangle', 'Oval']],
                ['type' => 'tags', 'label' => 'Edge Finish', 'id' => 'edgeFinish', 'options' => ['Beveled', 'Polished', 'Raw']],
                ['type' => 'tags', 'label' => 'Mounting Method', 'id' => 'mountingMethod', 'options' => ['Wall-mounted', 'Stand', 'Adhesive']]
            ]
        ];

        return $defaultFields[$fieldKey] ?? [];
    }

    /**
     * Get default step names
     */
    private function getDefaultStepNames($fieldKey)
    {
        $stepNamesKey = $fieldKey . '_stepNames';

        // Preferred: load defaults from the JSON file used across the app
        $jsonDefaults = $this->loadJsonDefaults();
        if (is_array($jsonDefaults) && isset($jsonDefaults[$stepNamesKey]) && is_array($jsonDefaults[$stepNamesKey])) {
            return $jsonDefaults[$stepNamesKey];
        }

        // Load generated defaults from the config file
        $defaultsFile = APPPATH . 'config/customization_defaults.php';
        if (file_exists($defaultsFile)) {
            $generatedDefaults = require $defaultsFile;
            return $generatedDefaults[$stepNamesKey] ?? null;
        }

        return null;
    }

    /**
     * Load JSON defaults from assets/data/default-customization-fields.json
     * Returns associative array or null on failure.
     */
    private function loadJsonDefaults()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        // FCPATH points to the project root (where index.php lives)
        $jsonPath = FCPATH . 'assets/data/default-customization-fields.json';
        if (!file_exists($jsonPath)) {
            $cached = null;
            return $cached;
        }

        $raw = @file_get_contents($jsonPath);
        if ($raw === false || $raw === '') {
            $cached = null;
            return $cached;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $cached = null;
            return $cached;
        }

        $cached = $decoded;
        return $cached;
    }

    /**
     * Update the CUSTOMIZATION_REFERENCE.md file with new field configurations
     */
    private function updateDocumentation($fieldKey, $fields, $category, $subcategory, $stepNames = null)
    {
        $docFile = APPPATH . '../docs/CUSTOMIZATION_REFERENCE.md';

        if (!file_exists($docFile)) {
            log_message('error', 'CUSTOMIZATION_REFERENCE.md file not found at: ' . $docFile);
            return;
        }

        $content = file_get_contents($docFile);
        $lines = explode("\n", $content);

        // Find the category and subcategory sections
        $categoryLine = -1;
        $subcategoryLine = -1;
        $tableStart = -1;
        $tableEnd = -1;

        foreach ($lines as $i => $line) {
            // Find main category
            if (preg_match('/^## ' . preg_quote($category, '/') . '$/', trim($line))) {
                $categoryLine = $i;
            }

            // Find subcategory under this category
            if ($categoryLine !== -1 && preg_match('/^### ' . preg_quote($subcategory, '/') . '$/', trim($line))) {
                $subcategoryLine = $i;
                // Look for the step names line and table
                for ($j = $i + 1; $j < count($lines); $j++) {
                    if (strpos(trim($lines[$j]), '| Step | Field | Control | Options |') !== false) {
                        $tableStart = $j;
                        // Find table end
                        for ($k = $j + 1; $k < count($lines); $k++) {
                            if (trim($lines[$k]) === '' || preg_match('/^### /', trim($lines[$k])) || preg_match('/^## /', trim($lines[$k]))) {
                                $tableEnd = $k - 1;
                                break;
                            }
                        }
                        break;
                    }
                }
                break;
            }
        }

        if ($subcategoryLine === -1 || $tableStart === -1) {
            log_message('error', 'Could not find subcategory section for ' . $category . ' -> ' . $subcategory);
            return;
        }

        // Generate new table rows from fields
        $newTableRows = [];

        // Use provided step names or generate from fields
        if (!$stepNames) {
            $stepNames = $this->getStepNamesFromFields($fields);
        }

        // Add step names line
        if (!empty($stepNames)) {
            // Convert step names to array format if needed
            $stepNamesArray = [];
            if (is_array($stepNames)) {
                // If it's already an array, use it directly
                $stepNamesArray = array_values($stepNames);
            } elseif (is_object($stepNames)) {
                // If it's an object, convert to array
                $stepNamesArray = array_values((array)$stepNames);
            }
            
            if (!empty($stepNamesArray)) {
                $stepNamesLine = "**Step names:** " . implode(' · ', $stepNamesArray);
                // Find and replace the step names line
                for ($i = $subcategoryLine + 1; $i < $tableStart; $i++) {
                    if (preg_match('/^\*\*Step names:\*\*/', trim($lines[$i]))) {
                        $lines[$i] = $stepNamesLine;
                        break;
                    }
                }
            }
        }

        // Group fields by step
        $fieldsByStep = [];
        foreach ($fields as $field) {
            $step = $field['stepNumber'] ?? 1;
            if (!isset($fieldsByStep[$step])) {
                $fieldsByStep[$step] = [];
            }
            $fieldsByStep[$step][] = $field;
        }

        // Generate table rows
        foreach ($fieldsByStep as $stepNumber => $stepFields) {
            // Handle step names - they can be an array or object with numeric keys
            $stepName = "Step $stepNumber";
            if ($stepNames) {
                if (is_array($stepNames)) {
                    // If it's an array with numeric keys (0-indexed)
                    if (isset($stepNames[$stepNumber - 1])) {
                        $stepName = $stepNames[$stepNumber - 1];
                    } elseif (isset($stepNames[$stepNumber])) {
                        $stepName = $stepNames[$stepNumber];
                    }
                } elseif (is_object($stepNames)) {
                    // If it's an object with string keys like {"1": "Step 1"}
                    $stepKey = (string)$stepNumber;
                    if (isset($stepNames->$stepKey)) {
                        $stepName = $stepNames->$stepKey;
                    }
                }
            }
            
            foreach ($stepFields as $field) {
                $controlType = $field['type'];
                $fieldName = $field['label'];
                $options = $this->formatFieldOptions($field);

                $newTableRows[] = "| $stepName ($stepNumber) | $fieldName | `$controlType` | $options |";
            }
        }

        // Replace the table content
        $beforeTable = array_slice($lines, 0, $tableStart + 2); // Include header and separator
        $afterTable = array_slice($lines, $tableEnd + 1);

        $newContent = implode("\n", $beforeTable) . "\n" . implode("\n", $newTableRows) . "\n" . implode("\n", $afterTable);

        file_put_contents($docFile, $newContent);
    }

    /**
     * Extract step names from fields array
     */
    private function getStepNamesFromFields($fields)
    {
        $stepNames = [];
        $maxStep = 0;

        foreach ($fields as $field) {
            $step = $field['stepNumber'] ?? 1;
            $maxStep = max($maxStep, $step);
        }

        for ($i = 1; $i <= $maxStep; $i++) {
            $stepNames[] = "Step $i"; // Default step names
        }

        return $stepNames;
    }

    /**
     * Format field options for documentation
     */
    private function formatFieldOptions($field)
    {
        $type = $field['type'];

        if ($type === 'tags' && isset($field['options']) && is_array($field['options'])) {
            return implode(' \\| ', $field['options']);
        } elseif ($type === 'number') {
            $min = $field['min'] ?? '';
            $step = $field['step'] ?? '';
            $parts = [];
            if ($min !== '') $parts[] = "min $min";
            if ($step !== '') $parts[] = "step $step";
            return implode(' · ', $parts);
        } elseif ($type === 'checkbox') {
            return '';
        }

        return '';
    }

    /**
     * Save series presets to database
     * POST /customizationFields/saveSeriesPresets
     * Expects: seriesPresets (JSON string of series configurations organized by subcategory)
     */
    public function saveSeriesPresets()
    {
        $seriesPresetsJson = $this->input->post('seriesPresets');

        if (!$seriesPresetsJson) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing required parameter: seriesPresets'
            ]);
            return;
        }

        // Handle JSON string
        if (is_string($seriesPresetsJson)) {
            $seriesPresets = json_decode($seriesPresetsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid JSON in seriesPresets: ' . json_last_error_msg()
                ]);
                return;
            }
        } else {
            $seriesPresets = $seriesPresetsJson;
        }

        if (!is_array($seriesPresets) || empty($seriesPresets)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'seriesPresets must be a non-empty array'
            ]);
            return;
        }

        // Check if table exists
        if (!$this->db->table_exists('customization_field_configs')) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Table customization_field_configs does not exist'
            ]);
            return;
        }

        try {
            // Save each subcategory's series presets
            foreach ($seriesPresets as $subcategory => $seriesData) {
                $fieldKey = "Series_Presets_{$subcategory}";
                $presetsJson = json_encode($seriesData);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Invalid JSON for subcategory ' . htmlspecialchars($subcategory) . ': ' . json_last_error_msg()
                    ]);
                    return;
                }

                // Check if config exists
                $this->db->where('FieldKey', $fieldKey);
                $existing = $this->db->get('customization_field_configs')->row();

                if ($existing) {
                    // Update
                    $this->db->where('FieldKey', $fieldKey);
                    $this->db->update('customization_field_configs', [
                        'FieldConfig' => $presetsJson,
                        'Updated_Date' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    // Insert
                    $this->db->insert('customization_field_configs', [
                        'Category' => 'Series_Presets',
                        'Subcategory' => $subcategory,
                        'FieldKey' => $fieldKey,
                        'FieldConfig' => $presetsJson
                    ]);
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Series presets saved successfully'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error saving series presets: ' . $e->getMessage()
            ]);
        }
    }
}
