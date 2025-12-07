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
        
        // Skip product_id check - inventory is checked in ShopCon before calling create_order
        $product_id = null;
        
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
        
        // Find customization records from cart items
        // Get cart items to find which customizations belong to this order
        $cart_items = [];
        if (method_exists($this->Cart_model, 'get_cart_items')) {
            $cart_items = $this->Cart_model->get_cart_items($customer_id);
        }
        
        // Find customization records that match cart items
        // Use cart items to find the exact customizations
        if (!empty($cart_items)) {
            foreach ($cart_items as $cart_item) {
                if (!empty($cart_item->CustomizationID)) {
                    // Find customization by CustomizationID in the appropriate table
                    $table_name = $this->Cart_model->get_customization_table($cart_item->Product_ID);
                    
                    $this->db->where('CustomizationID', $cart_item->CustomizationID);
                    $this->db->where('Customer_ID', $customer_id);
                    $custom = $this->db->get($table_name)->row();
                    
                    if ($custom) {
                        $customization = $custom;
                        $customization_table = $table_name;
                        break; // Use first matching customization
                    }
                }
            }
        }
        
        // If not found via cart items, get most recent customization for customer
        if (!$customization) {
            foreach ($customization_tables as $table) {
                $this->db->where('Customer_ID', $customer_id);
                $this->db->order_by('Created_Date', 'DESC');
                $this->db->limit(1);
                $custom = $this->db->get($table)->row();
                
                if ($custom) {
                    $customization = $custom;
                    $customization_table = $table;
                    break;
                }
            }
        }
        
        // If not found in category tables, check old customization table (skip OrderID filter)
        if (!$customization) {
            $this->db->where('Customer_ID', $customer_id);
            $this->db->order_by('Created_Date', 'DESC');
            $this->db->limit(1);
            $customization = $this->db->get('customization')->row();
            if ($customization) {
                $customization_table = 'customization';
            }
        }
        
        if ($customization) {
            // Get product info to determine category and format order details
            $this->db->select('ProductName, Category');
            $this->db->where('Product_ID', $customization->Product_ID);
            $product = $this->db->get('product')->row();
            
            // Format OrderID (e.g., GI001)
            $order_id_formatted = 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
            
            // Update customization table with OrderID (only if column exists)
            try {
                // Check if OrderID column exists by trying to select it
                $this->db->select('OrderID');
                $this->db->from($customization_table);
                $this->db->limit(1);
                $test = $this->db->get()->row();
                
                // If we got here, column exists - update it
                $this->db->where('CustomizationID', $customization->CustomizationID);
                $this->db->update($customization_table, ['OrderID' => $order_id_formatted]);
            } catch (Exception $e) {
                // OrderID column doesn't exist - skip update
                log_message('debug', 'OrderID column does not exist in ' . $customization_table . ' - skipping OrderID update');
            }
            
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
                'FileAttached' => isset($customization->DesignRef) ? ($customization->DesignRef ?? 'N/A') : 'N/A'
            ];
            
            // Insert into order_page
            $this->db->insert('order_page', $order_page_data);
            
            // Insert into pending_review_orders
            // Only include columns that exist in pending_review_orders table
            // pending_review_orders doesn't have: Status, LEDBacklight, DoorOperation, Configuration
            $pending_review_data = [
                'OrderID' => $order_page_data['OrderID'],
                'ProductName' => $order_page_data['ProductName'],
                'Address' => $order_page_data['Address'],
                'OrderDate' => $order_page_data['OrderDate'],
                'Customer_ID' => $order_page_data['Customer_ID'],
                'SalesRep_ID' => $order_page_data['SalesRep_ID'],
                'TotalQuotation' => $order_page_data['TotalQuotation'],
                'Dimension' => $order_page_data['Dimension'],
                'Shape' => $order_page_data['Shape'],
                'Type' => $order_page_data['Type'],
                'Thickness' => $order_page_data['Thickness'],
                'EdgeWork' => $order_page_data['EdgeWork'],
                'FrameType' => $order_page_data['FrameType'],
                'Engraving' => $order_page_data['Engraving'],
                'FileAttached' => $order_page_data['FileAttached']
            ];
            $this->db->insert('pending_review_orders', $pending_review_data);
        } else {
            // If no customization found, still insert basic order info into pending_review_orders
            // This ensures orders always appear in Sales Rep's pending review
            $order_id_formatted = 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
            
            // Get product info from cart items
            $product_name = 'N/A';
            if (!empty($cart_items)) {
                $first_item = $cart_items[0];
                $product_name = $first_item->ProductName ?? 'N/A';
            }
            
            // Insert minimal order info into pending_review_orders
            $pending_review_data = [
                'OrderID' => $order_id_formatted,
                'ProductName' => $product_name,
                'Address' => $delivery_address ?: 'N/A',
                'OrderDate' => date('Y-m-d H:i:s'),
                'Customer_ID' => $customer_id,
                'SalesRep_ID' => $order_data['SalesRep_ID'],
                'TotalQuotation' => $order_data['TotalAmount'],
                'Dimension' => 'N/A',
                'Shape' => 'N/A',
                'Type' => 'N/A',
                'Thickness' => 'N/A',
                'EdgeWork' => 'N/A',
                'FrameType' => 'N/A',
                'Engraving' => 'N/A',
                'FileAttached' => 'N/A'
            ];
            
            $this->db->insert('pending_review_orders', $pending_review_data);
            
            // Also insert into order_page
            $order_page_data = [
                'OrderID' => $order_id_formatted,
                'ProductName' => $product_name,
                'Address' => $delivery_address ?: 'N/A',
                'OrderDate' => date('Y-m-d H:i:s'),
                'Customer_ID' => $customer_id,
                'SalesRep_ID' => $order_data['SalesRep_ID'],
                'Status' => 'Pending Review',
                'TotalQuotation' => $order_data['TotalAmount'],
                'Dimension' => 'N/A',
                'Shape' => 'N/A',
                'Type' => 'N/A',
                'Thickness' => 'N/A',
                'EdgeWork' => 'N/A',
                'FrameType' => 'N/A',
                'Engraving' => 'N/A',
                'FileAttached' => 'N/A'
            ];
            
            $this->db->insert('order_page', $order_page_data);
            
            // Log info that customization was not found (using 'info' as 'warning' is not a valid CodeIgniter log level)
            log_message('info', 'Order ' . $order_id_formatted . ' created without customization. Customer_ID: ' . $customer_id);
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

    /**
     * Get order with customer details
     */
    public function get_order_with_customer($order_id)
    {
        $this->db->select('o.*, u.First_Name, u.Last_Name, u.Email, u.PhoneNum');
        $this->db->from('order o');
        $this->db->join('user u', 'u.UserID = o.Customer_ID', 'left');
        $this->db->where('o.OrderID', $order_id);
        return $this->db->get()->row();
    }

    /**
     * Get orders by customer ID
     */
    public function get_customer_orders($customer_id)
    {
        return $this->db->where('Customer_ID', $customer_id)
                        ->order_by('OrderDate', 'DESC')
                        ->get('order')
                        ->result();
    }

    /**
     * Update order status
     */
    public function update_order_status($order_id, $status)
    {
        return $this->db->where('OrderID', $order_id)
                        ->update('order', ['Status' => $status]);
    }

    /**
     * Update payment status
     */
    public function update_payment_status($order_id, $status)
    {
        return $this->db->where('OrderID', $order_id)
                        ->update('order', ['PaymentStatus' => $status]);
    }

    /**
     * Get default sales rep ID (first available)
     */
    public function get_default_sales_rep()
    {
        $result = $this->db->select('UserID')
                           ->where('Role', 'Sales Representative')
                           ->limit(1)
                           ->get('user')
                           ->row();
        return $result ? $result->UserID : 2; // Default to 2 if none found
    }

    /**
     * Get full order details for tracking page
     */
    public function get_order_tracking_details($order_id)
    {
        $this->db->select('
            o.*,
            u.First_Name,
            u.Last_Name,
            u.Email,
            u.PhoneNum,
            DATE_ADD(o.OrderDate, INTERVAL 3 DAY) as OcularDate,
            DATE_ADD(o.OrderDate, INTERVAL 7 DAY) as FabricationDate,
            DATE_ADD(o.OrderDate, INTERVAL 14 DAY) as InstallationDate,
            DATE_ADD(o.OrderDate, INTERVAL 21 DAY) as EstimatedDelivery
        ');
        $this->db->from('`order` o');
        $this->db->join('user u', 'u.UserID = o.Customer_ID', 'left');
        $this->db->where('o.OrderID', $order_id);
        
        return $this->db->get()->row();
    }

    /**
     * Get payment info for an order
     */
    public function get_order_payment($order_id)
    {
        return $this->db->where('OrderID', $order_id)->get('payment')->row();
    }

    /**
     * Get order progress steps based on status
     * Each status marks all previous steps as completed
     */
    public function get_order_progress($status)
    {
        $steps = [
            'order_placed' => false,
            'ocular_visit' => false,
            'in_fabrication' => false,
            'installed' => false,
            'completed' => false
        ];

        switch ($status) {
            case 'Completed':
                $steps['completed'] = true;
                $steps['installed'] = true;
                $steps['in_fabrication'] = true;
                $steps['ocular_visit'] = true;
                $steps['order_placed'] = true;
                break;
            case 'Ready for Installation':
                $steps['installed'] = true;
                $steps['in_fabrication'] = true;
                $steps['ocular_visit'] = true;
                $steps['order_placed'] = true;
                break;
            case 'In Fabrication':
                $steps['in_fabrication'] = true;
                $steps['ocular_visit'] = true;
                $steps['order_placed'] = true;
                break;
            case 'Approved':
                $steps['ocular_visit'] = true;
                $steps['order_placed'] = true;
                break;
            case 'Pending':
                $steps['order_placed'] = true;
                break;
            case 'Cancelled':
            case 'Returned':
                $steps['order_placed'] = true;
                break;
        }

        return $steps;
    }

    /**
     * Get customer order items for "My Purchases" page
     * Returns all orders with product details for a customer
     * 
     * @param int $customer_id Customer ID
     * @return array Array of order items with product details
     */
    public function get_customer_order_items($customer_id)
    {
        // Get orders from order_page table (which contains order details)
        $this->db->select('
            op.OrderID,
            op.ProductName,
            op.TotalQuotation as EstimatePrice,
            op.OrderDate,
            op.Customer_ID
        ');
        $this->db->from('order_page op');
        $this->db->where('op.Customer_ID', $customer_id);
        $this->db->order_by('op.OrderDate', 'DESC');
        
        $results = $this->db->get()->result();
        
        // Format results to match view expectations
        $order_items = [];
        foreach ($results as $row) {
            // Get product ImageUrl by ProductName
            $image_url = 'default.jpg';
            $this->db->select('ImageUrl');
            $this->db->where('ProductName', $row->ProductName);
            $product = $this->db->get('product')->row();
            if ($product && !empty($product->ImageUrl)) {
                $image_url = $product->ImageUrl;
            }
            
            // Quantity defaults to 1 (not stored in order_page)
            $quantity = 1;
            
            $order_items[] = (object)[
                'OrderID' => $row->OrderID,
                'ProductName' => $row->ProductName,
                'ImageUrl' => $image_url,
                'EstimatePrice' => floatval($row->EstimatePrice ?? 0),
                'Quantity' => $quantity,
                'OrderDate' => $row->OrderDate,
                'DeliveryDate' => $row->OrderDate, // Use OrderDate as DeliveryDate for now
                'Status' => 'Pending' // Default status
            ];
        }
        
        return $order_items;
    }
}

