<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database(); // Make sure database is loaded
    }
public function get_products() {
    return $this->db->order_by('DateAdded', 'DESC')->get('product')->result();
}

/**
 * Insert product with transaction handling
 * Used in: AdminCon->admin_product (Admin Add Product)
 */
public function insert_product($data) {
    $this->db->trans_start();
    
    $result = $this->db->insert('product', $data);
    $product_id = $this->db->insert_id();
    
    // Log activity if system_activity_log exists
    if ($this->db->table_exists('system_activity_log') && $product_id) {
        $this->db->insert('system_activity_log', [
            'Action' => 'Product Added',
            'Description' => 'New product added: ' . ($data['ProductName'] ?? 'N/A'),
            'Role' => 'Admin',
            'Timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    $this->db->trans_complete();
    
    if ($this->db->trans_status() === FALSE) {
        return false;
    }
    
    return $product_id;
}

/**
 * Delete product with transaction handling
 */
public function delete_product($id) {
    $this->db->trans_start();
    
    // Get product name for logging
    $product = $this->get_product($id);
    $product_name = $product ? $product->ProductName : 'N/A';
    
    $result = $this->db->where('Product_ID', $id)->delete('product');
    
    // Log activity
    if ($this->db->table_exists('system_activity_log') && $result) {
        $this->db->insert('system_activity_log', [
            'Action' => 'Product Deleted',
            'Description' => 'Product deleted: ' . $product_name,
            'Role' => 'Admin',
            'Timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    $this->db->trans_complete();
    
    if ($this->db->trans_status() === FALSE) {
        return false;
    }
    
    return $result;
}

/**
 * Update product with transaction handling
 */
public function update_product($id, $data) {
    $this->db->trans_start();
    
    $result = $this->db->where('Product_ID', $id)->update('product', $data);
    
    // Log activity
    if ($this->db->table_exists('system_activity_log') && $result) {
        $this->db->insert('system_activity_log', [
            'Action' => 'Product Updated',
            'Description' => 'Product updated: ' . ($data['ProductName'] ?? 'Product ID ' . $id),
            'Role' => 'Admin',
            'Timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    $this->db->trans_complete();
    
    if ($this->db->trans_status() === FALSE) {
        return false;
    }
    
    return $result;
}

public function get_product($id) {
    return $this->db->where('Product_ID', $id)->get('product')->row();
}

/**
 * Get random products for recommendations
 */
public function get_recommended_products($limit = 4, $exclude_ids = [])
{
    if (!empty($exclude_ids)) {
        $this->db->where_not_in('Product_ID', $exclude_ids);
    }
    $this->db->order_by('RAND()');
    $this->db->limit($limit);
    return $this->db->get('product')->result();
}

/**
 * Get products by category
 */
public function get_products_by_category($category, $limit = 4)
{
    $this->db->where('Category', $category);
    $this->db->order_by('RAND()');
    $this->db->limit($limit);
    return $this->db->get('product')->result();
}

}
