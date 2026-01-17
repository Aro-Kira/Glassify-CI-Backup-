<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database(); // Make sure database is loaded
    }
public function get_products() {
    // Only show products that are available (In Stock or Low Stock)
    $this->db->group_start();
    $this->db->where('Status', 'In Stock');
    $this->db->or_where('Status', 'Low Stock');
    $this->db->group_end();
    return $this->db->order_by('DateAdded', 'DESC')->get('product')->result();
}

public function insert_product($data) {
    return $this->db->insert('product', $data);
}

public function delete_product($id) {
    return $this->db->where('Product_ID', $id)->delete('product');
}

public function update_product($id, $data) {
    return $this->db->where('Product_ID', $id)->update('product', $data);
}

public function get_product($id) {
    // Explicitly select all columns including Price, PriceMin, PriceMax to ensure they're retrieved
    $this->db->select('*');
    $this->db->where('Product_ID', $id);
    return $this->db->get('product')->row();
}

/**
 * Get recommended products for homepage
 * Returns a specified number of products (default: 4)
 * Prioritizes products that are in stock or low stock
 */
public function get_recommended_products($limit = 4) {
    // Get products that are in stock or low stock (available products)
    $this->db->group_start();
    $this->db->where('Status', 'In Stock');
    $this->db->or_where('Status', 'Low Stock');
    $this->db->group_end();
    $this->db->order_by('DateAdded', 'DESC');
    $this->db->limit($limit);
    $products = $this->db->get('product')->result();
    
    // If we don't have enough available products, get any products to fill the limit
    if (count($products) < $limit) {
        $product_ids = array_column($products, 'Product_ID');
        $needed = $limit - count($products);
        
        if (!empty($product_ids)) {
            $this->db->where_not_in('Product_ID', $product_ids);
        }
        $this->db->order_by('DateAdded', 'DESC');
        $this->db->limit($needed);
        $additional_products = $this->db->get('product')->result();
        
        // Merge the additional products
        $products = array_merge($products, $additional_products);
    }
    
    return $products;
}

}
