<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customization_model extends CI_Model
{
    protected $table = "customization";

    public function __construct() {
        parent::__construct();
        $this->load->model('Cart_model');
    }

    /**
     * Get the unified customization table (optimized schema uses single table)
     */
    private function get_table($product_id = null) {
        // Optimized schema uses unified customization table
        return $this->table;
    }

    public function add_customization($data) {
        // Use Cart_model to save (it handles table selection)
        $this->load->model('Cart_model');
        return $this->Cart_model->save_customization($data);
    }

    public function delete_customization($customization_id, $product_id = null) {
        if ($product_id) {
            // We know the product, so we know which table to use
            $table = $this->get_table($product_id);
            $this->db->where('CustomizationID', $customization_id);
            $result = $this->db->delete($table);
            
            // Check for errors
            if ($this->db->error()['code'] != 0) {
                log_message('error', 'Delete customization error: ' . $this->db->error()['message']);
                return false;
            }
            
            return $result;
        } else {
            // Product ID not provided - try to delete from all possible tables
            return $this->delete_customization_from_any_table($customization_id);
        }
    }
    
    /**
     * Delete customization from unified customization table
     * Used as fallback when product_id is not known
     */
    public function delete_customization_from_any_table($customization_id) {
        // Optimized schema uses unified customization table
        $this->db->where('CustomizationID', $customization_id);
        $this->db->delete($this->table);
        
        // Check if deletion was successful (affected_rows > 0)
        return $this->db->affected_rows() > 0;
    }

    public function delete_multiple($ids = [], $product_id = null)
    {
        if (empty($ids)) return false;
        
        // Optimized schema uses unified customization table
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
