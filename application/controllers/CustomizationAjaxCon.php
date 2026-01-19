<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AJAX Controller for Customization Operations
 * Handles saving/loading customization selections and price calculations
 */
class CustomizationAjaxCon extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
        $this->load->library('session');
        header('Content-Type: application/json');
    }

    /**
     * Save customization selections
     * POST /customizationAjax/save
     */
    public function save() {
        try {
            // Handle JSON input (php://input can only be read once)
            $raw = file_get_contents('php://input');
            $json_input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
            if (is_array($json_input)) {
                $product_id = $json_input['product_id'] ?? null;
                $selections = $json_input['selections'] ?? null;
                $timestamp = $json_input['timestamp'] ?? null;
            } else {
                $product_id = $this->input->post('product_id');
                $selections = $this->input->post('selections');
                $timestamp = $this->input->post('timestamp');
            }

            $customer_id = $this->session->userdata('customer_id');
            
            if (!$product_id || !is_array($selections)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid data provided'
                ]);
                return;
            }

            // If customer is logged in, save to database
            if ($customer_id) {
                // Check if table exists and create if needed
                try {
                    if (!$this->db->table_exists('customer_customizations')) {
                        $this->create_customizations_table();
                    }
                } catch (Throwable $e) {
                    log_message('error', 'Customization table check/create: ' . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'message' => 'Database configuration error. Customization saved locally only.'
                    ]);
                    return;
                }

                // Save or update customization
                $data = [
                    'customer_id' => $customer_id,
                    'product_id' => $product_id,
                    'selections' => json_encode($selections),
                    'timestamp' => $timestamp ? date('Y-m-d H:i:s', (int)($timestamp / 1000)) : date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                try {
                    $this->db->where('customer_id', $customer_id);
                    $this->db->where('product_id', $product_id);
                    $existing = $this->db->get('customer_customizations')->row();

                    if ($existing) {
                        $this->db->where('id', $existing->id);
                        $this->db->update('customer_customizations', $data);
                    } else {
                        $this->db->insert('customer_customizations', $data);
                    }
                } catch (Throwable $e) {
                    log_message('error', 'Customization DB save: ' . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to save to database. Customization saved locally only.'
                    ]);
                    return;
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Customization saved successfully'
                ]);
            } else {
                // Not logged in - still return success but don't save to DB
                echo json_encode([
                    'success' => true,
                    'message' => 'Customization saved locally (not logged in)'
                ]);
            }

        } catch (Throwable $e) {
            log_message('error', 'Customization save error: ' . $e->getMessage());
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save customization. Using local storage.'
            ]);
        }
    }

    /**
     * Load saved customization selections
     * GET /customizationAjax/load?product_id=X
     */
    public function load() {
        try {
            $customer_id = $this->session->userdata('customer_id');
            
            // If not logged in, return empty (frontend will use localStorage)
            if (!$customer_id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Not logged in - using local storage'
                ]);
                return;
            }

            $product_id = $this->input->get('product_id');
            if (!$product_id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Product ID required'
                ]);
                return;
            }

            // Check if table exists
            if (!$this->db->table_exists('customer_customizations')) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No saved customizations found'
                ]);
                return;
            }

            // Load customization
            $this->db->where('customer_id', $customer_id);
            $this->db->where('product_id', $product_id);
            $customization = $this->db->get('customer_customizations')->row();

            if ($customization) {
                $selections = json_decode($customization->selections, true);
                echo json_encode([
                    'success' => true,
                    'selections' => $selections,
                    'timestamp' => strtotime($customization->timestamp) * 1000
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No saved customization found'
                ]);
            }

        } catch (Exception $e) {
            log_message('error', 'Customization load error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to load customization: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get real-time price update
     * POST /customizationAjax/price
     */
    public function price() {
        try {
            // Handle JSON input
            $json_input = json_decode(file_get_contents('php://input'), true);
            if ($json_input) {
                $product_id = $json_input['product_id'] ?? null;
                $selections = $json_input['selections'] ?? null;
                $dimensions = $json_input['dimensions'] ?? null;
            } else {
                $product_id = $this->input->post('product_id');
                $selections = $this->input->post('selections');
                $dimensions = $this->input->post('dimensions');
            }

            if (!$product_id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Product ID required'
                ]);
                return;
            }

            // Get base product price
            $this->db->select('Price, PriceMin, PriceMax');
            $this->db->where('Product_ID', $product_id);
            $product = $this->db->get('product')->row();

            if (!$product) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Product not found'
                ]);
                return;
            }

            $base_price = $product->Price ?: $product->PriceMin ?: 0;

            // Calculate customization costs
            $customization_cost = $this->calculate_customization_cost($product_id, $selections, $dimensions);

            // Calculate area-based cost
            $area_cost = $this->calculate_area_cost($dimensions, $base_price);

            $total_price = $base_price + $customization_cost + $area_cost;

            // Create price breakdown
            $breakdown = [
                'area' => $area_cost,
                'customization' => $customization_cost,
                'base' => $base_price
            ];

            echo json_encode([
                'success' => true,
                'price' => $total_price,
                'breakdown' => $breakdown
            ]);

        } catch (Exception $e) {
            log_message('error', 'Price calculation error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to calculate price: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate customization cost based on selections
     */
    private function calculate_customization_cost($product_id, $selections, $dimensions) {
        $total_cost = 0;

        if (!is_array($selections)) {
            return $total_cost;
        }

        // Load tag prices for this product
        $this->db->where('Product_ID', $product_id);
        $tag_prices = $this->db->get('product_tag_prices')->result();

        $price_map = [];
        foreach ($tag_prices as $tag_price) {
            $price_map[$tag_price->FieldID][$tag_price->TagName] = $tag_price->Price;
        }

        // Calculate cost for each selection
        foreach ($selections as $field_id => $value) {
            if (isset($price_map[$field_id][$value])) {
                $total_cost += $price_map[$field_id][$value];
            }
        }

        return $total_cost;
    }

    /**
     * Calculate area-based cost
     */
    private function calculate_area_cost($dimensions, $base_price) {
        if (!is_array($dimensions) || !isset($dimensions['width']) || !isset($dimensions['height'])) {
            return 0;
        }

        $width = $dimensions['width'];
        $height = $dimensions['height'];
        $unit = $dimensions['unit'] ?? 'in';

        // Convert to inches if needed
        if ($unit === 'cm') {
            $width /= 2.54;
            $height /= 2.54;
        } elseif ($unit === 'mm') {
            $width /= 25.4;
            $height /= 25.4;
        }

        $area_sq_inches = $width * $height;

        // Calculate area cost (example: $0.50 per square inch above base)
        $base_area = 45 * 35; // Standard 45"x35" area
        $extra_area = max(0, $area_sq_inches - $base_area);

        return $extra_area * 0.50; // $0.50 per extra square inch
    }

    /**
     * Create customer customizations table if it doesn't exist.
     * Uses indexes only (no FK) to avoid failures when customer/product table or column names differ.
     */
    private function create_customizations_table() {
        $sql = "CREATE TABLE IF NOT EXISTS `customer_customizations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `selections` longtext NOT NULL,
            `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_customer_product` (`customer_id`,`product_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        if ($this->db->query($sql) === false) {
            $err = $this->db->error();
            log_message('error', 'create_customizations_table: ' . (isset($err['message']) ? $err['message'] : 'unknown'));
            throw new \RuntimeException('Could not create customer_customizations table');
        }
    }
}
?>