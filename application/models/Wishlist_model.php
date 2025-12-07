<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ============================================================================
 * WISHLIST MODEL
 * ============================================================================
 * 
 * This model handles all database operations for the wishlist feature.
 * It manages the relationship between customers, products, and customizations.
 * 
 * DATABASE TABLES:
 * - wishlist: Stores wishlist entries (Wishlist_ID, Customer_ID, Product_ID, CustomizationID, DateAdded)
 * - customization: Stores glass customization options (dimensions, shape, type, thickness, etc.)
 * - product: Product information (joined for display)
 * - cart: Shopping cart (used when moving items from wishlist to cart)
 * 
 * KEY METHODS:
 * - add_to_wishlist()      : Add item with duplicate prevention
 * - get_wishlist_items()   : Get all wishlist items for a customer
 * - remove_item()          : Remove single item (and its customization)
 * - clear_wishlist()       : Remove all items for a customer
 * - move_to_cart()         : Transfer item from wishlist to cart
 * - get_wishlist_count()   : Count items for header badge
 * - is_in_wishlist()       : Check if product already in wishlist
 * 
 * @author      Glassify Development Team
 * @version     1.0.0
 * @created     December 2025
 * ============================================================================
 */
class Wishlist_model extends CI_Model
{
    /** @var string Database table name */
    protected $table = "wishlist";

    /**
     * Constructor - Load database library
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ===================== ADD TO WISHLIST =====================
    public function add_to_wishlist($data)
    {
        // Check if item already exists in wishlist (same product and customization)
        $this->db->where('Customer_ID', $data['Customer_ID']);
        $this->db->where('Product_ID', $data['Product_ID']);
        
        if (!empty($data['CustomizationID'])) {
            $this->db->where('CustomizationID', $data['CustomizationID']);
        } else {
            $this->db->where('CustomizationID IS NULL', null, false);
        }
        
        $query = $this->db->get($this->table);

        if ($query->num_rows() > 0) {
            // Already exists
            return ['status' => 'exists', 'id' => $query->row()->Wishlist_ID];
        }

        $this->db->insert($this->table, $data);
        return ['status' => 'added', 'id' => $this->db->insert_id()];
    }

    // ===================== SAVE CUSTOMIZATION FOR WISHLIST =====================
    public function save_customization($data)
    {
        $this->db->insert('customization', $data);
        return $this->db->insert_id();
    }

    // ===================== GET WISHLIST ITEMS =====================
    public function get_wishlist_items($customer_id)
    {
        $this->db->select('
            w.Wishlist_ID,
            w.Product_ID,
            w.CustomizationID,
            w.DateAdded,
            p.ProductName,
            p.Price as BasePrice,
            p.ImageUrl,
            cu.DesignRef,
            cu.Dimensions,
            cu.GlassShape,
            cu.GlassType,
            cu.GlassThickness,
            cu.EdgeWork,
            cu.FrameType,
            cu.Engraving,
            cu.EstimatePrice,
            IF(cu.EstimatePrice IS NOT NULL AND cu.EstimatePrice > 0, cu.EstimatePrice, p.Price) as Price
        ');
        $this->db->from('wishlist w');
        $this->db->join('product p', 'p.Product_ID = w.Product_ID', 'left');
        $this->db->join('customization cu', 'cu.CustomizationID = w.CustomizationID', 'left');
        $this->db->where('w.Customer_ID', $customer_id);
        $this->db->order_by('w.DateAdded', 'DESC');

        return $this->db->get()->result();
    }

    // ===================== GET SINGLE WISHLIST ITEM =====================
    public function get_wishlist_item($wishlist_id)
    {
        $this->db->select('
            w.Wishlist_ID,
            w.Customer_ID,
            w.Product_ID,
            w.CustomizationID,
            w.DateAdded,
            p.ProductName,
            p.Price as BasePrice,
            p.ImageUrl,
            cu.DesignRef,
            cu.Dimensions,
            cu.GlassShape,
            cu.GlassType,
            cu.GlassThickness,
            cu.EdgeWork,
            cu.FrameType,
            cu.Engraving,
            cu.EstimatePrice,
            IF(cu.EstimatePrice IS NOT NULL AND cu.EstimatePrice > 0, cu.EstimatePrice, p.Price) as Price
        ');
        $this->db->from('wishlist w');
        $this->db->join('product p', 'p.Product_ID = w.Product_ID', 'left');
        $this->db->join('customization cu', 'cu.CustomizationID = w.CustomizationID', 'left');
        $this->db->where('w.Wishlist_ID', $wishlist_id);

        return $this->db->get()->row();
    }

    // ===================== REMOVE FROM WISHLIST =====================
    public function remove_item($wishlist_id)
    {
        // Get item first to check for customization
        $item = $this->db->where('Wishlist_ID', $wishlist_id)->get($this->table)->row();
        
        if ($item) {
            // Delete the wishlist entry
            $this->db->where('Wishlist_ID', $wishlist_id);
            $deleted = $this->db->delete($this->table);
            
            // If customization exists, delete it too
            if ($deleted && !empty($item->CustomizationID)) {
                $this->db->where('CustomizationID', $item->CustomizationID);
                $this->db->delete('customization');
            }
            
            return $deleted;
        }
        
        return false;
    }

    // ===================== CLEAR WISHLIST =====================
    public function clear_wishlist($customer_id)
    {
        // Get all customization IDs first
        $items = $this->db
            ->where('Customer_ID', $customer_id)
            ->get($this->table)
            ->result();
        
        $customization_ids = [];
        foreach ($items as $item) {
            if (!empty($item->CustomizationID)) {
                $customization_ids[] = $item->CustomizationID;
            }
        }
        
        // Delete wishlist items
        $this->db->where('Customer_ID', $customer_id);
        $deleted = $this->db->delete($this->table);
        
        // Delete customizations
        if ($deleted && !empty($customization_ids)) {
            $this->db->where_in('CustomizationID', $customization_ids);
            $this->db->delete('customization');
        }
        
        return $deleted;
    }

    // ===================== GET WISHLIST COUNT =====================
    public function get_wishlist_count($customer_id)
    {
        $this->db->where('Customer_ID', $customer_id);
        return $this->db->count_all_results($this->table);
    }

    // ===================== CHECK IF IN WISHLIST =====================
    public function is_in_wishlist($customer_id, $product_id, $customization_id = null)
    {
        $this->db->where('Customer_ID', $customer_id);
        $this->db->where('Product_ID', $product_id);
        
        if ($customization_id) {
            $this->db->where('CustomizationID', $customization_id);
        }
        
        return $this->db->count_all_results($this->table) > 0;
    }

    // ===================== MOVE TO CART =====================
    public function move_to_cart($wishlist_id, $customer_id)
    {
        $wishlist_item = $this->get_wishlist_item($wishlist_id);
        
        if (!$wishlist_item || $wishlist_item->Customer_ID != $customer_id) {
            return false;
        }

        // If the wishlist item has customization, duplicate it for the cart
        $new_customization_id = null;
        if (!empty($wishlist_item->CustomizationID)) {
            // Get the customization data
            $customization = $this->db
                ->where('CustomizationID', $wishlist_item->CustomizationID)
                ->get('customization')
                ->row();
            
            if ($customization) {
                // Create a new customization for the cart
                $custom_data = [
                    'Customer_ID' => $customer_id,
                    'Product_ID' => $wishlist_item->Product_ID,
                    'Dimensions' => $customization->Dimensions,
                    'GlassShape' => $customization->GlassShape,
                    'GlassType' => $customization->GlassType,
                    'GlassThickness' => $customization->GlassThickness,
                    'EdgeWork' => $customization->EdgeWork,
                    'FrameType' => $customization->FrameType,
                    'Engraving' => $customization->Engraving,
                    'DesignRef' => $customization->DesignRef,
                    'EstimatePrice' => $customization->EstimatePrice
                ];
                
                $this->db->insert('customization', $custom_data);
                $new_customization_id = $this->db->insert_id();
            }
        }

        // Add to cart
        $cart_data = [
            'Customer_ID' => $customer_id,
            'Product_ID' => $wishlist_item->Product_ID,
            'CustomizationID' => $new_customization_id,
            'Quantity' => 1
        ];

        // Check if same product already in cart
        $this->db->where('Customer_ID', $customer_id);
        $this->db->where('Product_ID', $wishlist_item->Product_ID);
        $this->db->where('CustomizationID', $new_customization_id);
        $existing = $this->db->get('cart');

        if ($existing->num_rows() > 0) {
            // Update quantity
            $row = $existing->row();
            $this->db->where('Cart_ID', $row->Cart_ID);
            $this->db->update('cart', ['Quantity' => $row->Quantity + 1]);
        } else {
            $this->db->insert('cart', $cart_data);
        }

        // Remove from wishlist (but keep the original customization for wishlist history)
        $this->db->where('Wishlist_ID', $wishlist_id);
        $this->db->delete($this->table);

        return true;
    }
}
