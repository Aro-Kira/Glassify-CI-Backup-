<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_model extends CI_Model
{
    private $last_error = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Customer_model');
    }

    // ===================== GET CUSTOMIZATION TABLE BY CATEGORY =====================
    /**
     * Returns the unified customization table name (optimized schema uses single table)
     * @param int $product_id Product ID (kept for backward compatibility)
     * @return string Table name
     */
    public function get_customization_table($product_id = null)
    {
        // Optimized schema uses unified customization table for all categories
        return 'customization';
    }
    
    // ===================== SAVE CUSTOMIZATION =====================
    /**
     * Saves customization to the appropriate table based on product category
     * @param array $data Customization data
     * @return int CustomizationID
     */
    public function save_customization($data)
    {
        // Store error message for debugging
        $this->last_error = null;
        
        try {
            // Validate required fields
            $customer_id = isset($data['Customer_ID']) ? intval($data['Customer_ID']) : null;
            $product_id = isset($data['Product_ID']) ? intval($data['Product_ID']) : null;
            
            if (!$customer_id || $customer_id <= 0) {
                $error_msg = 'Invalid or missing Customer_ID. Received: ' . var_export($data['Customer_ID'] ?? 'not set', true);
                log_message('error', 'Customization save error: ' . $error_msg);
                $this->last_error = $error_msg;
                return false;
            }
            
            if (!$product_id || $product_id <= 0) {
                $error_msg = 'Invalid or missing Product_ID. Received: ' . var_export($data['Product_ID'] ?? 'not set', true);
                log_message('error', 'Customization save error: ' . $error_msg);
                $this->last_error = $error_msg;
                return false;
            }
            
            // Verify Customer_ID exists in customer table, or create it if missing
            $customer_check = $this->db->where('Customer_ID', $customer_id)->get('customer')->row();
            $db_error = $this->db->error();
            if ($db_error['code'] != 0) {
                $error_msg = 'Database error checking Customer_ID: ' . $db_error['message'];
                log_message('error', 'Customization save error: ' . $error_msg);
                $this->last_error = $error_msg;
                return false;
            }
            if (!$customer_check) {
                // Customer_ID doesn't exist - try to find UserID and create customer record
                // Check if there's a user with UserID = customer_id that should be a customer
                $this->db->select('UserID, Role');
                $this->db->where('UserID', $customer_id);
                $user_check = $this->db->get('user')->row();
                
                if ($user_check && $user_check->Role === 'Customer') {
                    // Use Customer_model to ensure customer record exists
                    $new_customer_id = $this->Customer_model->ensure_customer_exists($user_check->UserID);
                    
                    if (!$new_customer_id) {
                        $error_msg = 'Failed to create customer record for UserID: ' . $user_check->UserID;
                        log_message('error', 'Customization save error: ' . $error_msg);
                        $this->last_error = $error_msg;
                        return false;
                    }
                    
                    // Use the newly created Customer_ID
                    $customer_id = $new_customer_id;
                } else {
                    // Customer_ID doesn't exist and can't be auto-created
                    $error_msg = 'Customer_ID ' . $customer_id . ' does not exist in customer table and no matching user found.';
                    log_message('error', 'Customization save error: ' . $error_msg);
                    $this->last_error = $error_msg;
                    return false;
                }
            }
            
            // Verify Product_ID exists in product table
            $product_check = $this->db->where('Product_ID', $product_id)->get('product')->row();
            $db_error = $this->db->error();
            if ($db_error['code'] != 0) {
                $error_msg = 'Database error checking Product_ID: ' . $db_error['message'];
                log_message('error', 'Customization save error: ' . $error_msg);
                $this->last_error = $error_msg;
                return false;
            }
            if (!$product_check) {
                // Get total products count (separate query)
                $this->db->select('COUNT(*) as total');
                $total_result = $this->db->get('product')->row();
                $total_products = $total_result ? $total_result->total : 0;
                $error_msg = 'Product_ID ' . $product_id . ' does not exist in product table. Total products in database: ' . $total_products;
                log_message('error', 'Customization save error: ' . $error_msg);
                $this->last_error = $error_msg;
                return false;
            }
            
            $table_name = $this->get_customization_table($product_id);
            
            // Prepare data based on table structure
            $table_data = $this->prepare_customization_data($table_name, $data);
            
            // Ensure Customer_ID and Product_ID are integers
            $table_data['Customer_ID'] = $customer_id;
            $table_data['Product_ID'] = $product_id;
            
            // Insert into appropriate table
            $this->db->insert($table_name, $table_data);
            
            // Check for database errors
            $db_error = $this->db->error();
            if ($db_error['code'] != 0) {
                $error_msg = 'Database error: ' . $db_error['message'];
                log_message('error', 'Customization save error in table ' . $table_name . ': ' . $db_error['message']);
                log_message('error', 'Attempted data: ' . print_r($table_data, true));
                $this->last_error = $error_msg;
                return false;
            }
            
            return $this->db->insert_id(); // returns CustomizationID
            
        } catch (Exception $e) {
            $error_msg = 'Exception: ' . $e->getMessage();
            log_message('error', 'Exception in save_customization: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->last_error = $error_msg;
            return false;
        }
    }
    
    /**
     * Get the last error message from save_customization
     */
    public function get_last_error()
    {
        return $this->last_error ?? 'Unknown error';
    }
    
    // ===================== PREPARE CUSTOMIZATION DATA =====================
    /**
     * Prepares customization data for unified customization table
     * @param string $table_name Table name (kept for backward compatibility, but always 'customization')
     * @param array $data Raw customization data
     * @return array Prepared data for customization table
     */
    private function prepare_customization_data($table_name, $data)
    {
        // Unified customization table supports all fields
        // Note: Customer_ID and Product_ID are handled separately in save_customization
        // to ensure they are integers and validated
        return [
            'Dimensions' => !empty($data['Dimensions']) ? $data['Dimensions'] : null,
            'GlassShape' => !empty($data['GlassShape']) ? $data['GlassShape'] : null,
            'GlassType' => !empty($data['GlassType']) ? $data['GlassType'] : null,
            'GlassThickness' => !empty($data['GlassThickness']) ? $data['GlassThickness'] : null,
            'EdgeWork' => !empty($data['EdgeWork']) ? $data['EdgeWork'] : null,
            'FrameType' => !empty($data['FrameType']) ? $data['FrameType'] : null,
            'Engraving' => !empty($data['Engraving']) ? $data['Engraving'] : null,
            'DesignRef' => !empty($data['DesignRef']) ? $data['DesignRef'] : null,
            'LEDBacklight' => !empty($data['LEDBacklight']) ? $data['LEDBacklight'] : null,
            'DoorOperation' => !empty($data['DoorOperation']) ? $data['DoorOperation'] : null,
            'Configuration' => !empty($data['Configuration']) ? $data['Configuration'] : null,
            'EstimatePrice' => isset($data['EstimatePrice']) ? floatval($data['EstimatePrice']) : 0.00,
            'PriceBreakdown' => isset($data['PriceBreakdown']) ? (is_string($data['PriceBreakdown']) ? $data['PriceBreakdown'] : json_encode($data['PriceBreakdown'])) : null,
            // Store all dynamic customization fields as JSON (synced with admin side)
            'Customization' => isset($data['Customization']) ? (is_string($data['Customization']) ? $data['Customization'] : json_encode($data['Customization'])) : null
        ];
    }

   // ===================== GET CART COUNT =====================
   /**
    * Get total number of items in cart for a customer
    * @param int $customer_id Customer ID
    * @return int Number of items in cart
    */
   public function get_cart_count($customer_id)
   {
       $this->db->where('Customer_ID', $customer_id);
       return $this->db->count_all_results('cart');
   }

   // ===================== GET CART ITEMS =====================
public function get_cart_items($customer_id)
{
    // Get cart items with product info (no customization join - we'll get it separately)
    $this->db->select('
        c.Cart_ID,
        c.Product_ID,
        c.CustomizationID,
        c.Quantity,
        p.ProductName,
        p.Price as BasePrice,
        p.ImageUrl,
        p.Category
    ');
    $this->db->from('cart c');
    $this->db->join('product p', 'p.Product_ID = c.Product_ID', 'left');
    $this->db->where('c.Customer_ID', $customer_id);
    $cart_items = $this->db->get()->result();
    
    // Load Customization_model to get customizations from appropriate tables
    $this->load->model('Customization_model');
    
    // For each cart item, get customization from appropriate table
    $result = [];
    foreach ($cart_items as $item) {
        $customization = null;
        $estimate_price = 0;
        
        // Default customization values
        $dimensions = null;
        $glass_shape = null;
        $glass_type = null;
        $glass_thickness = null;
        $edge_work = null;
        $frame_type = null;
        $engraving = null;
        $design_ref = null;
        
        // Get customization from appropriate table if CustomizationID exists
        if ($item->CustomizationID) {
            $customization = $this->Customization_model->get_customization(
                $item->CustomizationID, 
                $item->Product_ID
            );
            
            if ($customization) {
                $estimate_price = $customization->EstimatePrice ?? 0;
                $dimensions = $customization->Dimensions ?? null;
                $glass_shape = $customization->GlassShape ?? null;
                $glass_type = $customization->GlassType ?? null;
                $glass_thickness = $customization->GlassThickness ?? null;
                $edge_work = $customization->EdgeWork ?? null;
                $frame_type = $customization->FrameType ?? null;
                $engraving = $customization->Engraving ?? null;
                $design_ref = $customization->DesignRef ?? null;
            }
        }
        
        // Calculate price (use EstimatePrice if available, otherwise BasePrice)
        $price = ($estimate_price > 0) ? $estimate_price : $item->BasePrice;
        
        $result[] = (object)[
            'Cart_ID' => $item->Cart_ID,
            'Product_ID' => $item->Product_ID,
            'CustomizationID' => $item->CustomizationID,
            'Quantity' => $item->Quantity,
            'ProductName' => $item->ProductName,
            'BasePrice' => $item->BasePrice,
            'ImageUrl' => $item->ImageUrl,
            'Price' => $price,
            'DesignRef' => $design_ref,
            'Dimensions' => $dimensions,
            'GlassShape' => $glass_shape,
            'GlassType' => $glass_type,
            'GlassThickness' => $glass_thickness,
            'EdgeWork' => $edge_work,
            'FrameType' => $frame_type,
            'Engraving' => $engraving,
            'EstimatePrice' => $estimate_price
        ];
    }
    
    return $result;
}

// ===================== GET CART ITEMS WITH FULL DETAILS =====================
public function get_cart_items_with_details($customer_id)
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
    
    // For each cart item, get full customization from appropriate table
    $result = [];
    foreach ($cart_items as $item) {
        $customization = null;
        $estimate_price = 0;
        
        // Default customization values
        $dimensions = null;
        $glass_shape = null;
        $glass_type = null;
        $glass_thickness = null;
        $edge_work = null;
        $frame_type = null;
        $engraving = null;
        $design_ref = null;
        
        if ($item->CustomizationID) {
            $table_name = $this->get_customization_table($item->Product_ID);
            $this->db->where('CustomizationID', $item->CustomizationID);
            $customization = $this->db->get($table_name)->row();
            
            if ($customization) {
                $estimate_price = $customization->EstimatePrice ?? 0;
                $dimensions = $customization->Dimensions ?? null;
                $glass_shape = $customization->GlassShape ?? null;
                $glass_type = $customization->GlassType ?? null;
                $glass_thickness = $customization->GlassThickness ?? null;
                $edge_work = $customization->EdgeWork ?? null;
                $frame_type = $customization->FrameType ?? null;
                $engraving = $customization->Engraving ?? null;
                $design_ref = $customization->DesignRef ?? null;
            }
        }
        
        $result[] = (object)[
            'Cart_ID' => $item->Cart_ID,
            'Product_ID' => $item->Product_ID,
            'CustomizationID' => $item->CustomizationID,
            'Quantity' => $item->Quantity,
            'ProductName' => $item->ProductName,
            'Category' => $item->Category,
            'BasePrice' => $item->BasePrice,
            'ImageUrl' => $item->ImageUrl,
            'EstimatePrice' => $estimate_price,
            'Price' => ($estimate_price > 0) ? $estimate_price : $item->BasePrice,
            'Dimensions' => $dimensions,
            'GlassShape' => $glass_shape,
            'GlassType' => $glass_type,
            'GlassThickness' => $glass_thickness,
            'EdgeWork' => $edge_work,
            'FrameType' => $frame_type,
            'Engraving' => $engraving,
            'DesignRef' => $design_ref
        ];
    }
    
    return $result;
}




    // ===================== ADD TO CART =====================
    /**
     * Add to cart with transaction handling
     * Used in: CartCon->add_customized, AddtoCartCon->save (Add to Cart)
     * Sequence: Customer Product -> Add to Cart
     */
    public function add_to_cart($data)
    {
        $this->db->trans_start();
        
        // Check if product already in cart with same customization
        $this->db->where('Customer_ID', $data['Customer_ID']);
        $this->db->where('Product_ID', $data['Product_ID']);
        $this->db->where('CustomizationID', $data['CustomizationID'] ?? null);
        $query = $this->db->get('cart');

        if ($query->num_rows() > 0) {
            // Update quantity
            $row = $query->row();
            $this->db->where('Cart_ID', $row->Cart_ID);
            $this->db->update('cart', ['Quantity' => $row->Quantity + $data['Quantity']]);
            $cart_id = $row->Cart_ID;
        } else {
            $this->db->insert('cart', $data);
            $cart_id = $this->db->insert_id();
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return $cart_id;
    }

    // ===================== REMOVE ITEM =====================
    public function remove_item($cart_id)
    {
        log_message('debug', 'Cart_model::remove_item called with Cart_ID: ' . $cart_id);
        
        // Validate cart_id - must be greater than 0
        if (!$cart_id || $cart_id <= 0) {
            log_message('error', 'Attempted to delete cart item with invalid Cart_ID: ' . $cart_id);
            return false;
        }
        
        $this->db->where('Cart_ID', $cart_id);
        $result = $this->db->delete('cart');
        $affected_rows = $this->db->affected_rows();
        $db_error = $this->db->error();
        
        log_message('debug', 'Delete query executed - Result: ' . ($result ? 'true' : 'false') . ', Affected rows: ' . $affected_rows);
        
        if ($db_error['code'] != 0) {
            log_message('error', 'Delete cart item database error - Code: ' . $db_error['code'] . ', Message: ' . $db_error['message']);
            return false;
        }
        
        // Check if a row was actually deleted
        if ($affected_rows == 0) {
            log_message('error', 'No cart item found with Cart_ID: ' . $cart_id . ' (affected_rows = 0)');
            return false;
        }
        
        log_message('debug', 'Cart item successfully deleted - Cart_ID: ' . $cart_id);
        return $result;
    }

    // ===================== CLEAR CART =====================
    public function clear_cart($customer_id)
    {
        $this->db->where('Customer_ID', $customer_id);
        return $this->db->delete('cart');
    }
}
