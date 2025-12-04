<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customization_model extends CI_Model
{
    protected $table = 'customization';

    public function __construct() {
        parent::__construct();
        $this->load->model('Cart_model');
    }

    /**
     * Get the appropriate customization table based on product category
     */
    private function get_table($product_id) {
        if (!$product_id) {
            return $this->table; // Fallback to old table
        }
        return $this->Cart_model->get_customization_table($product_id);
    }

    public function add_customization($data) {
        // Use Cart_model to save (it handles table selection)
        $this->load->model('Cart_model');
        return $this->Cart_model->save_customization($data);
    }

    public function delete_customization($customization_id, $product_id = null) {
        $table = $this->get_table($product_id);
        $this->db->where('CustomizationID', $customization_id);
        return $this->db->delete($table);
    }

    public function delete_multiple($ids = [], $product_id = null)
    {
        if (empty($ids)) return false;
        
        $table = $this->get_table($product_id);
        $this->db->where_in('CustomizationID', $ids);
        return $this->db->delete($table);
    }
    
    /**
     * Get customization by ID from appropriate table
     */
    public function get_customization($customization_id, $product_id) {
        $table = $this->get_table($product_id);
        $this->db->where('CustomizationID', $customization_id);
        return $this->db->get($table)->row();
    }
}
