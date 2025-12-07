<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Inventory_model');
        $this->load->library('session');
        
        // Check authentication for inventory officer
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Inventory Officer') {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
            exit;
        }
    }
    
    /**
     * Get all inventory items
     */
    public function get_items()
    {
        $items = $this->Inventory_model->get_all_items();
        
        // Format items for frontend
        $formatted_items = [];
        foreach ($items as $item) {
            $formatted_items[] = [
                'item_id' => $item->InventoryItemID,
                'item_code' => $item->ItemID,
                'name' => $item->Name,
                'category' => $item->Category,
                'stock_quantity' => intval($item->InStock),
                'unit' => $item->Unit,
                'min_threshold' => isset($item->min_threshold) ? intval($item->min_threshold) : 10,
                'is_new_item' => ($item->Status === 'New'),
                'status' => $item->Status
            ];
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $formatted_items
            ]));
    }
    
    /**
     * Get statistics
     */
    public function get_statistics()
    {
        $stats = $this->Inventory_model->get_statistics();
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $stats
            ]));
    }
    
    /**
     * Add new item
     */
    public function add_item()
    {
        if ($this->input->method() !== 'post') {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(405)
                ->set_output(json_encode(['success' => false, 'message' => 'Method not allowed']));
            return;
        }
        
        $input = json_decode($this->input->raw_input_stream, true);
        if (!$input) {
            $input = $this->input->post();
        }
        
        // Validate required fields
        $required = ['itemName', 'itemCategory', 'initialStock', 'itemUnit'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['success' => false, 'message' => "Field '$field' is required"]));
                return;
            }
        }
        
        $data = [
            'Name' => trim($input['itemName']),
            'Category' => trim($input['itemCategory']),
            'InStock' => intval($input['initialStock']),
            'Unit' => trim($input['itemUnit']),
            'min_threshold' => isset($input['minThreshold']) ? intval($input['minThreshold']) : 10,
            'is_new_item' => isset($input['isNewItem']) && $input['isNewItem']
        ];
        
        $result = $this->Inventory_model->add_item($data);
        
        if ($result['success']) {
            $item = $this->Inventory_model->get_item($result['item_id']);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Item added successfully',
                    'data' => [
                        'item_id' => $result['item_id'],
                        'item_code' => $result['ItemID'],
                        'name' => $item->Name,
                        'category' => $item->Category,
                        'stock_quantity' => intval($item->InStock),
                        'unit' => $item->Unit
                    ]
                ]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(['success' => false, 'message' => $result['message']]));
        }
    }
    
    /**
     * Update item
     */
    public function update_item($item_id)
    {
        if ($this->input->method() !== 'post' && $this->input->method() !== 'put') {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(405)
                ->set_output(json_encode(['success' => false, 'message' => 'Method not allowed']));
            return;
        }
        
        $input = json_decode($this->input->raw_input_stream, true);
        if (!$input) {
            $input = $this->input->post();
        }
        
        $data = [];
        if (isset($input['itemName'])) $data['Name'] = trim($input['itemName']);
        if (isset($input['itemCategory'])) $data['Category'] = trim($input['itemCategory']);
        if (isset($input['stockQuantity'])) $data['InStock'] = intval($input['stockQuantity']);
        if (isset($input['itemUnit'])) $data['Unit'] = trim($input['itemUnit']);
        if (isset($input['minThreshold'])) $data['min_threshold'] = intval($input['minThreshold']);
        if (isset($input['isNewItem'])) $data['Status'] = $input['isNewItem'] ? 'New' : 'In Stock';
        
        $result = $this->Inventory_model->update_item($item_id, $data);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }
    
    /**
     * Delete item
     */
    public function delete_item($item_id)
    {
        if ($this->input->method() !== 'post' && $this->input->method() !== 'delete') {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(405)
                ->set_output(json_encode(['success' => false, 'message' => 'Method not allowed']));
            return;
        }
        
        $result = $this->Inventory_model->delete_item($item_id);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }
    
    /**
     * Manage stock (add/remove)
     */
    public function manage_stock($item_id)
    {
        if ($this->input->method() !== 'post') {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(405)
                ->set_output(json_encode(['success' => false, 'message' => 'Method not allowed']));
            return;
        }
        
        $input = json_decode($this->input->raw_input_stream, true);
        if (!$input) {
            $input = $this->input->post();
        }
        
        $add_quantity = isset($input['addStock']) ? intval($input['addStock']) : 0;
        $remove_quantity = isset($input['removeStock']) ? intval($input['removeStock']) : 0;
        $reason = isset($input['reason']) ? trim($input['reason']) : '';
        $user_id = $this->session->userdata('user_id');
        
        $result = $this->Inventory_model->manage_stock($item_id, $add_quantity, $remove_quantity, $reason, $user_id);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }
    
    /**
     * Get activities
     */
    public function get_activities()
    {
        $limit = $this->input->get('limit') ? intval($this->input->get('limit')) : 50;
        $activities = $this->Inventory_model->get_activities($limit);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $activities
            ]));
    }
}


