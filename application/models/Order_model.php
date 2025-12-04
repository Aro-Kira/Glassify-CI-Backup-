<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Inventory_model');
    }

    /**
     * Check if order can be created (inventory available)
     * 
     * @param int $product_id Product ID
     * @param int $quantity Quantity
     * @return array ['can_create' => bool, 'missing_materials' => array]
     */
    public function can_create_order($product_id, $quantity = 1)
    {
        return $this->Inventory_model->can_manufacture_product($product_id, $quantity);
    }
    
    /**
     * Create a new order and update customization record
     * 
     * @param array $order_data Order data (Customer_ID, SalesRep_ID, TotalAmount, DeliveryAddress, etc.)
     * @return int|false OrderID on success, false on failure
     */
    public function create_order($order_data)
    {
        // Start transaction
        $this->db->trans_start();
        
        // Check inventory availability before creating order
        // Get product ID from customization
        $customer_id = $order_data['Customer_ID'];
        $customization_tables = [
            'mirror_customization',
            'shower_enclosure_customization',
            'aluminum_doors_customization',
            'aluminum_bathroom_doors_customization'
        ];
        
        $product_id = null;
        foreach ($customization_tables as $table) {
            $this->db->where('Customer_ID', $customer_id);
            $this->db->where('OrderID IS NULL', null, false);
            $this->db->order_by('Created_Date', 'DESC');
            $this->db->limit(1);
            $custom = $this->db->get($table)->row();
            
            if ($custom) {
                $product_id = $custom->Product_ID;
                break;
            }
        }
        
        // Note: Inventory check is done in ShopCon before calling create_order
        // This ensures orders are only created when materials are available
        
        // Insert order
        $this->db->insert('order', $order_data);
        $order_id = $this->db->insert_id();
        
        if (!$order_id) {
            $this->db->trans_rollback();
            return false;
        }
        
        // Update customization record with OrderID, Address, and Date
        // Check all category-specific customization tables
        $customer_id = $order_data['Customer_ID'];
        $delivery_address = $order_data['DeliveryAddress'] ?? null;
        
        $this->load->model('Cart_model');
        
        // List of category-specific customization tables
        $customization_tables = [
            'mirror_customization',
            'shower_enclosure_customization',
            'aluminum_doors_customization',
            'aluminum_bathroom_doors_customization'
        ];
        
        $customization = null;
        $customization_table = null;
        
        // Find customization record in any category table that doesn't have an OrderID yet
        foreach ($customization_tables as $table) {
            $this->db->where('Customer_ID', $customer_id);
            $this->db->where('OrderID IS NULL', null, false); // Only get records without OrderID
            $this->db->order_by('Created_Date', 'DESC');
            $this->db->limit(1);
            $custom = $this->db->get($table)->row();
            
            if ($custom) {
                $customization = $custom;
                $customization_table = $table;
                break;
            }
        }
        
        // If not found in category tables, check old customization table
        if (!$customization) {
            $this->db->where('Customer_ID', $customer_id);
            $this->db->where('OrderID IS NULL', null, false);
            $this->db->order_by('Created_Date', 'DESC');
            $this->db->limit(1);
            $customization = $this->db->get('customization')->row();
            $customization_table = 'customization';
        }
        
        if ($customization) {
            // Get product info to determine category and format order details
            $this->db->select('ProductName, Category');
            $this->db->where('Product_ID', $customization->Product_ID);
            $product = $this->db->get('product')->row();
            
            // Format OrderID (e.g., GI001)
            $order_id_formatted = 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
            
            // Update customization table with OrderID
            $this->db->where('CustomizationID', $customization->CustomizationID);
            $this->db->update($customization_table, ['OrderID' => $order_id_formatted]);
            
            // Prepare order_page data from customization
            $order_page_data = [
                'OrderID' => $order_id_formatted,
                'ProductName' => $product ? $product->ProductName : 'N/A',
                'Address' => $delivery_address ?: 'N/A',
                'OrderDate' => date('Y-m-d H:i:s'),
                'Customer_ID' => $customer_id,
                'SalesRep_ID' => $order_data['SalesRep_ID'],
                'Status' => 'Pending Review',
                'TotalQuotation' => $order_data['TotalAmount'],
                'Dimension' => $customization->Dimensions ?? 'N/A',
                'Shape' => isset($customization->GlassShape) ? ($customization->GlassShape ?? 'N/A') : 'N/A',
                'Type' => isset($customization->GlassType) ? ($customization->GlassType ?? 'N/A') : 'N/A',
                'Thickness' => isset($customization->GlassThickness) ? ($customization->GlassThickness ?? 'N/A') : 'N/A',
                'EdgeWork' => isset($customization->EdgeWork) ? ($customization->EdgeWork ?? 'N/A') : 'N/A',
                'FrameType' => isset($customization->FrameType) ? ($customization->FrameType ?? 'N/A') : 'N/A',
                'Engraving' => isset($customization->Engraving) ? ($customization->Engraving ?? 'N/A') : 'N/A',
                'LEDBacklight' => isset($customization->LEDBacklight) ? ($customization->LEDBacklight ?? 'N/A') : 'N/A',
                'DoorOperation' => isset($customization->DoorOperation) ? ($customization->DoorOperation ?? 'N/A') : 'N/A',
                'Configuration' => isset($customization->Configuration) ? ($customization->Configuration ?? 'N/A') : 'N/A',
                'FileAttached' => isset($customization->DesignRef) ? ($customization->DesignRef ?? 'N/A') : 'N/A',
                'Category' => $product ? $product->Category : 'N/A'
            ];
            
            // Insert into order_page
            $this->db->insert('order_page', $order_page_data);
            
            // Insert into pending_review_orders (same data)
            $this->db->insert('pending_review_orders', $order_page_data);
        }
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return $order_id;
    }

    /**
     * Get order by ID
     */
    public function get_order($order_id)
    {
        return $this->db->where('OrderID', $order_id)->get('order')->row();
    }
}

