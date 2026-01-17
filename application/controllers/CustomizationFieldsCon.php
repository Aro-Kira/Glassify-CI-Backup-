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
                'Cabinets & Furniture' => 'Cabinets',
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
            
            echo json_encode([
                'status' => 'success',
                'fieldKey' => $fieldKey,
                'fields' => $fields
            ]);
        } else {
            // Return default fields if not found
            $defaultFields = $this->getDefaultFields($fieldKey);
            echo json_encode([
                'status' => 'success',
                'fieldKey' => $fieldKey,
                'fields' => $defaultFields,
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

        if (!$fieldKey) {
            if ($category && $subcategory) {
                $prefixMap = [
                    'Windows' => 'Windows',
                    'Doors' => 'Doors',
                    'Glass Partitions & Enclosures' => 'Partitions',
                    'Mirrors & Specialty Glass' => 'Specialty',
                    'Cabinets & Furniture' => 'Cabinets',
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
     * Get default field configurations
     */
    private function getDefaultFields($fieldKey)
    {
        $defaultFields = [
            'Windows_Sliding' => [
                ['type' => 'tags', 'label' => 'Glass Type', 'id' => 'glassType', 'options' => ['Clear', 'Tinted', 'Laminated']],
                ['type' => 'tags', 'label' => 'Frame Color/Material', 'id' => 'frameColor', 'options' => ['White', 'Black', 'Silver', 'Bronze', 'Wood', 'Aluminum']],
                ['type' => 'number', 'label' => 'Thickness (mm)', 'id' => 'thickness', 'min' => 1, 'step' => 0.1],
                ['type' => 'checkbox', 'label' => 'Screen', 'id' => 'screen']
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
                ['type' => 'tags', 'label' => 'Shape', 'id' => 'shape', 'options' => ['Round', 'Rectangle', 'Oval']],
                ['type' => 'tags', 'label' => 'Edge Finish', 'id' => 'edgeFinish', 'options' => ['Beveled', 'Polished', 'Raw']],
                ['type' => 'tags', 'label' => 'Mounting Method', 'id' => 'mountingMethod', 'options' => ['Wall-mounted', 'Stand', 'Adhesive']]
            ],
            'Specialty_Glass Board' => [
                ['type' => 'tags', 'label' => 'Shape', 'id' => 'shape', 'options' => ['Round', 'Rectangle', 'Oval']],
                ['type' => 'tags', 'label' => 'Edge Finish', 'id' => 'edgeFinish', 'options' => ['Beveled', 'Polished', 'Raw']],
                ['type' => 'tags', 'label' => 'Mounting Method', 'id' => 'mountingMethod', 'options' => ['Wall-mounted', 'Stand', 'Adhesive']]
            ]
        ];

        return $defaultFields[$fieldKey] ?? [];
    }
}
