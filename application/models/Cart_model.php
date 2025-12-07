<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ===================== GET CUSTOMIZATION TABLE BY CATEGORY =====================
    /**
     * Returns the appropriate customization table name based on product category
     * @param int $product_id Product ID
     * @return string Table name
     */
    public function get_customization_table($product_id)
    {
        // Get product category
        $this->db->select('Category');
        $this->db->where('Product_ID', $product_id);
        $product = $this->db->get('product')->row();
        
        if (!$product) {
            // Fallback to old customization table if product not found
            return 'customization';
        }
        
        $category = trim($product->Category);
        
        // Normalize category to handle case variations
        $category_lower = strtolower($category);
        
        // Map category to table name (case-insensitive)
        switch ($category_lower) {
            case 'mirrors':
                return 'mirror_customization';
            case 'shower enclosure / partition':
                return 'shower_enclosure_customization';
            case 'aluminum doors':
                return 'aluminum_doors_customization';
            case 'aluminum and bathroom doors':
                return 'aluminum_bathroom_doors_customization';
            default:
                // Log unknown category for debugging
                log_message('debug', 'Unknown product category: ' . $category . ' (Product ID: ' . $product_id . ')');
                // Fallback to old customization table for unknown categories
                return 'customization';
        }
    }
    
    // ===================== SAVE CUSTOMIZATION =====================
    /**
     * Saves customization to the appropriate table based on product category
     * @param array $data Customization data
     * @return int CustomizationID
     */
    public function save_customization($data)
    {
        try {
            // Get the appropriate table based on product category
            $product_id = $data['Product_ID'] ?? null;
            if (!$product_id) {
                // Fallback to old table if no product ID
                $this->db->insert('customization', $data);
                if ($this->db->error()['code'] != 0) {
                    log_message('error', 'Customization save error: ' . $this->db->error()['message']);
                    return false;
                }
                return $this->db->insert_id();
            }
            
            $table_name = $this->get_customization_table($product_id);
            
            // Prepare data based on table structure
            $table_data = $this->prepare_customization_data($table_name, $data);
            
            // Insert into appropriate table
            $this->db->insert($table_name, $table_data);
            
            // Check for database errors
            if ($this->db->error()['code'] != 0) {
                log_message('error', 'Customization save error in table ' . $table_name . ': ' . $this->db->error()['message']);
                return false;
            }
            
            return $this->db->insert_id(); // returns CustomizationID
            
        } catch (Exception $e) {
            log_message('error', 'Exception in save_customization: ' . $e->getMessage());
            return false;
        }
    }
    
    // ===================== PREPARE CUSTOMIZATION DATA =====================
    /**
     * Prepares customization data based on table structure
     * @param string $table_name Table name
     * @param array $data Raw customization data
     * @return array Prepared data for specific table
     */
    private function prepare_customization_data($table_name, $data)
    {
        // Base fields common to all tables
        $base_data = [
            'Customer_ID' => $data['Customer_ID'] ?? null,
            'Product_ID' => $data['Product_ID'] ?? null,
            'Dimensions' => $data['Dimensions'] ?? null,
            'EstimatePrice' => $data['EstimatePrice'] ?? 0
        ];
        
        // Add category-specific fields
        switch ($table_name) {
            case 'mirror_customization':
                return array_merge($base_data, [
                    'EdgeWork' => $data['EdgeWork'] ?? null,
                    'GlassShape' => $data['GlassShape'] ?? null,
                    'LEDBacklight' => $data['LEDBacklight'] ?? null,
                    'Engraving' => $data['Engraving'] ?? null
                ]);
                
            case 'shower_enclosure_customization':
                return array_merge($base_data, [
                    'GlassType' => $data['GlassType'] ?? null,
                    'GlassThickness' => $data['GlassThickness'] ?? null,
                    'FrameType' => $data['FrameType'] ?? null,
                    'Engraving' => $data['Engraving'] ?? null,
                    'DoorOperation' => $data['DoorOperation'] ?? null
                ]);
                
            case 'aluminum_doors_customization':
                return array_merge($base_data, [
                    'GlassType' => $data['GlassType'] ?? null,
                    'GlassThickness' => $data['GlassThickness'] ?? null,
                    'Configuration' => $data['Configuration'] ?? null
                ]);
                
            case 'aluminum_bathroom_doors_customization':
                return array_merge($base_data, [
                    'FrameType' => $data['FrameType'] ?? null
                ]);
                
            default:
                // Fallback: Only include fields that exist in old customization table
                // Old table has: Dimensions, GlassShape, GlassType, GlassThickness, EdgeWork, FrameType, Engraving, DesignRef, EstimatePrice
                // Does NOT have: LEDBacklight, DoorOperation, Configuration
                return array_merge($base_data, [
                    'GlassShape' => $data['GlassShape'] ?? null,
                    'GlassType' => $data['GlassType'] ?? null,
                    'GlassThickness' => $data['GlassThickness'] ?? null,
                    'EdgeWork' => $data['EdgeWork'] ?? null,
                    'FrameType' => $data['FrameType'] ?? null,
                    'Engraving' => $data['Engraving'] ?? null,
                    'DesignRef' => $data['DesignRef'] ?? null
                    // Excluded: LEDBacklight, DoorOperation, Configuration (not in old table)
                ]);
        }
    }

   // ===================== GET CART ITEMS =====================
public function get_cart_items($customer_id)
{
    // Get cart items with product info
    $this->db->select('
        c.Cart_ID,
        c.Product_ID,
        c.CustomizationID,
        c.Quantity,
        p.ProductName,
        p.Category,
        p.Price as BasePrice,
        p.ImageUrl
    ');
    $this->db->from('cart c');
    $this->db->join('product p', 'p.Product_ID = c.Product_ID', 'left');
    $this->db->where('c.Customer_ID', $customer_id);
    $cart_items = $this->db->get()->result();
    
    // For each cart item, get customization from appropriate table
    $result = [];
    foreach ($cart_items as $item) {
        $customization = null;
        $estimate_price = 0;
        
        if ($item->CustomizationID) {
            $table_name = $this->get_customization_table($item->Product_ID);
            $this->db->select('EstimatePrice');
            $this->db->where('CustomizationID', $item->CustomizationID);
            $customization = $this->db->get($table_name)->row();
            
            if ($customization) {
                $estimate_price = $customization->EstimatePrice;
            }
        }
        
        $result[] = (object)[
            'Cart_ID' => $item->Cart_ID,
            'Product_ID' => $item->Product_ID,
            'CustomizationID' => $item->CustomizationID,
            'Quantity' => $item->Quantity,
            'ProductName' => $item->ProductName,
            'BasePrice' => $item->BasePrice,
            'ImageUrl' => $item->ImageUrl,
            'EstimatePrice' => $estimate_price,
            'Price' => ($estimate_price > 0) ? $estimate_price : $item->BasePrice
        ];
    }
    
    return $result;
}




    // ===================== ADD TO CART =====================
    public function add_to_cart($data)
    {
        // Check if product already in cart
        $this->db->where('Customer_ID', $data['Customer_ID']);
        $this->db->where('Product_ID', $data['Product_ID']);
        $this->db->where('CustomizationID', $data['CustomizationID']);
        $query = $this->db->get('cart');

        if ($query->num_rows() > 0) {
            // Update quantity
            $row = $query->row();
            $this->db->where('Cart_ID', $row->Cart_ID);
            $this->db->update('cart', ['Quantity' => $row->Quantity + $data['Quantity']]);
        } else {
            $this->db->insert('cart', $data);
        }
        return true;
    }

    // ===================== REMOVE ITEM =====================
    public function remove_item($cart_id)
    {
        // Validate cart_id - must be greater than 0
        if (!$cart_id || $cart_id <= 0) {
            log_message('error', 'Attempted to delete cart item with invalid Cart_ID: ' . $cart_id);
            return false;
        }
        
        $this->db->where('Cart_ID', $cart_id);
        $result = $this->db->delete('cart');
        
        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Delete cart item error: ' . $this->db->error()['message']);
            return false;
        }
        
        return $result;
    }

    // ===================== CLEAR CART =====================
    public function clear_cart($customer_id)
    {
        $this->db->where('Customer_ID', $customer_id);
        return $this->db->delete('cart');
    }
}
