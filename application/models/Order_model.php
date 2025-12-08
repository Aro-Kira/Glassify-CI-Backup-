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
     * Create a new order with comprehensive transaction handling
     * Used in: ShopCon->place_order, ShopCon->waiting_order, ShopCon->submit_ewallet_payment (Checkout -> Payment)
     * Sequence: Cart -> Checkout -> Payment -> Complete
     * 
     * @param array $order_data Order data (Customer_ID, SalesRep_ID, TotalAmount, DeliveryAddress, etc.)
     * @return int|false OrderID on success, false on failure
     */
    public function create_order($order_data)
    {
        // Start transaction
        $this->db->trans_start();
        
        // Generate OrderNumber (GI001, GI002, etc.)
        $order_number = $this->generate_order_number();
        $order_data['OrderNumber'] = $order_number;
        
        // Set default status if not provided
        if (!isset($order_data['Status'])) {
            $order_data['Status'] = 'Pending Review';
        }
        
        // Set default payment status if not provided
        if (!isset($order_data['PaymentStatus'])) {
            $order_data['PaymentStatus'] = 'Pending';
        }
        
        // Insert order
        $this->db->insert('order', $order_data);
        $order_id = $this->db->insert_id();
        
        if (!$order_id) {
            $this->db->trans_rollback();
            log_message('error', 'Order creation failed: Could not insert order record');
            return false;
        }
        
        // Log activity
        if ($this->db->table_exists('system_activity_log')) {
            $this->db->insert('system_activity_log', [
                'Action' => 'Order Created',
                'Description' => "New order created: {$order_number} (Customer ID: {$order_data['Customer_ID']})",
                'Role' => 'Customer',
                'RelatedID' => $order_id,
                'RelatedType' => 'Order',
                'Timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Order creation transaction failed for Customer ID: ' . $order_data['Customer_ID']);
            return false;
        }
        
        return $order_id;
    }
    
    /**
     * Generate unique order number (GI001, GI002, etc.)
     */
    private function generate_order_number()
    {
        // Get the last order number
        $this->db->select('OrderNumber');
        $this->db->from('order');
        $this->db->order_by('OrderID', 'DESC');
        $this->db->limit(1);
        $last_order = $this->db->get()->row();
        
        if ($last_order && preg_match('/GI(\d+)/', $last_order->OrderNumber, $matches)) {
            $next_num = intval($matches[1]) + 1;
        } else {
            $next_num = 1;
        }
        
        return 'GI' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get order by ID (handles both numeric OrderID and OrderNumber format like GI001)
     */
    public function get_order($order_id)
    {
        // Try numeric OrderID first
        $order = $this->db->where('OrderID', $order_id)->get('`order`')->row();
        
        // If not found and it's a string (could be OrderNumber format), try by OrderNumber
        if (!$order && is_string($order_id)) {
            $order = $this->db->where('OrderNumber', $order_id)->get('`order`')->row();
        }
        
        return $order;
    }

    /**
     * Get order with customer details
     */
    public function get_order_with_customer($order_id)
    {
        $this->db->select('o.*, u.First_Name, u.Last_Name, u.Email, u.PhoneNum');
        $this->db->from('`order` o');
        $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
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
     * Update order status with transaction handling
     * Used in: SalesCon->approve_order, SalesCon->disapprove_order, AdminCon->approve_order_admin
     * Sequence: Sales Rep (Order Confirmation) -> Admin (Order Confirmation)
     */
    public function update_order_status($order_id, $status, $approved_by = null, $approved_by_id = null)
    {
        $this->db->trans_start();
        
        $update_data = ['Status' => $status];
        
        // Add approval information if provided
        if ($approved_by === 'Sales Rep' && $approved_by_id) {
            $update_data['ApprovedBy_SalesRep_ID'] = $approved_by_id;
            $update_data['Approved_Date'] = date('Y-m-d H:i:s');
        } elseif ($approved_by === 'Admin' && $approved_by_id) {
            $update_data['ApprovedBy_Admin_ID'] = $approved_by_id;
            $update_data['Approved_Date'] = date('Y-m-d H:i:s');
        }
        
        $result = $this->db->where('OrderID', $order_id)
                        ->update('order', $update_data);
        
        // Log activity
        if ($this->db->table_exists('system_activity_log') && $result) {
            $order = $this->get_order($order_id);
            $order_number = $order ? $order->OrderNumber : 'N/A';
            
            $this->db->insert('system_activity_log', [
                'Action' => 'Order Status Updated',
                'Description' => "Order {$order_number} status updated to: {$status}",
                'Role' => $approved_by ?? 'System',
                'UserID' => $approved_by_id,
                'RelatedID' => $order_id,
                'RelatedType' => 'Order',
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
     * Update payment status with transaction handling
     * Used in: SalesCon->mark_payment_paid (Payment Processing)
     * Sequence: Payment -> Complete
     */
    public function update_payment_status($order_id, $status)
    {
        // Validate status value
        $valid_statuses = ['Pending', 'Paid', 'Failed', 'Refunded'];
        if (!in_array($status, $valid_statuses)) {
            log_message('error', 'Invalid payment status: ' . $status . ' for OrderID: ' . $order_id);
            return false;
        }
        
        // Make sure no transaction is already active
        if ($this->db->trans_status() !== FALSE) {
            $this->db->trans_rollback();
        }
        
        $this->db->trans_start();
        
        try {
            // First, verify the order exists
            $this->db->where('OrderID', $order_id);
            $order_exists = $this->db->get('`order`')->row();
            
            if (!$order_exists) {
                log_message('error', 'Order not found: OrderID ' . $order_id);
                $this->db->trans_rollback();
                return false;
            }
            
            // Update order table
            $this->db->where('OrderID', $order_id);
            $result = $this->db->update('`order`', ['PaymentStatus' => $status]);
            
            if (!$result) {
                $error = $this->db->error();
                log_message('error', 'Failed to update order PaymentStatus: ' . ($error['message'] ?? 'Unknown error'));
                $this->db->trans_rollback();
                return false;
            }
            
            // Check if payment record exists
            $this->db->where('OrderID', $order_id);
            $existing_payment = $this->db->get('payment')->row();
            
            if ($existing_payment) {
                // Update existing payment record
                $this->db->where('OrderID', $order_id);
                $payment_update = $this->db->update('payment', [
                    'Status' => $status,
                    'Payment_Date' => date('Y-m-d H:i:s')
                ]);
                
                if (!$payment_update) {
                    $error = $this->db->error();
                    log_message('error', 'Failed to update payment Status: ' . ($error['message'] ?? 'Unknown error'));
                    $this->db->trans_rollback();
                    return false;
                }
            } else {
                // Create payment record if it doesn't exist
                // Get order details to populate payment record
                $order = $this->get_order_with_customer($order_id);
                if ($order) {
                    // Get customer name
                    $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
                    
                    // Get product name from order items
                    $order_items = $this->get_order_customizations($order_id);
                    $product_name = '';
                    if (!empty($order_items)) {
                        $product_name = $order_items[0]->ProductName ?? '';
                    }
                    
                    // Create payment record
                    $payment_data = [
                        'OrderID' => $order_id,
                        'Amount' => $order->TotalAmount ?? 0,
                        'Status' => $status,
                        'Payment_Date' => date('Y-m-d H:i:s'),
                        'CustomerName' => $customer_name,
                        'ProductName' => $product_name,
                        'PaymentMethod' => $order->PaymentMethod ?? 'E-Wallet'
                    ];
                    
                    $payment_insert = $this->db->insert('payment', $payment_data);
                    
                    if (!$payment_insert) {
                        $error = $this->db->error();
                        log_message('error', 'Failed to insert payment record: ' . ($error['message'] ?? 'Unknown error'));
                        $this->db->trans_rollback();
                        return false;
                    }
                } else {
                    log_message('warning', 'Could not get order details for OrderID: ' . $order_id . ' - payment record not created');
                    // Don't fail the transaction if we can't create payment record, but log it
                }
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                $error = $this->db->error();
                log_message('error', 'Transaction failed: ' . ($error['message'] ?? 'Unknown error'));
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            log_message('error', 'Exception in update_payment_status: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->db->trans_rollback();
            return false;
        }
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
     * Uses actual appointment dates when available, otherwise calculates from OrderDate
     */
    public function get_order_tracking_details($order_id)
    {
        $this->db->select('
            o.*,
            u.First_Name,
            u.Last_Name,
            u.Email,
            u.PhoneNum,
            COALESCE(o.OcularDate, DATE_ADD(o.OrderDate, INTERVAL 3 DAY)) as OcularDate,
            COALESCE(o.FabricationDate, DATE_ADD(o.OrderDate, INTERVAL 7 DAY)) as FabricationDate,
            COALESCE(o.InstallationDate, DATE_ADD(o.OrderDate, INTERVAL 14 DAY)) as InstallationDate,
            COALESCE(o.EstimatedDelivery, DATE_ADD(o.OrderDate, INTERVAL 21 DAY)) as EstimatedDelivery
        ');
        $this->db->from('`order` o');
        $this->db->join('user u', 'u.UserID = o.Customer_ID', 'left');
        $this->db->where('o.OrderID', $order_id);
        
        $order = $this->db->get()->row();
        
        // Override with actual appointment dates if they exist
        if ($order) {
            // Get Ocular Visit appointment date
            $ocular_apt = $this->db->where('OrderID', $order_id)
                                   ->where('Service', 'Ocular Visit')
                                   ->get('appointments')
                                   ->row();
            if ($ocular_apt && $ocular_apt->AppointmentDate) {
                $order->OcularDate = $ocular_apt->AppointmentDate;
            }
            
            // Get In Fabrication appointment date
            $fabrication_apt = $this->db->where('OrderID', $order_id)
                                       ->where('Service', 'In Fabrication')
                                       ->get('appointments')
                                       ->row();
            if ($fabrication_apt && $fabrication_apt->AppointmentDate) {
                $order->FabricationDate = $fabrication_apt->AppointmentDate;
            }
            
            // Get Installed appointment date
            $installed_apt = $this->db->where('OrderID', $order_id)
                                     ->where('Service', 'Installed')
                                     ->get('appointments')
                                     ->row();
            if ($installed_apt && $installed_apt->AppointmentDate) {
                $order->InstallationDate = $installed_apt->AppointmentDate;
            }
            
            // Get Completed appointment date
            $completed_apt = $this->db->where('OrderID', $order_id)
                                     ->where('Service', 'Completed')
                                     ->get('appointments')
                                     ->row();
            if ($completed_apt && $completed_apt->AppointmentDate) {
                $order->EstimatedDelivery = $completed_apt->AppointmentDate;
            }
        }
        
        return $order;
    }

    /**
     * Get payment info for an order
     */
    public function get_order_payment($order_id)
    {
        return $this->db->where('OrderID', $order_id)->get('payment')->row();
    }

    /**
     * Get order progress steps based on status and appointments
     * Checks appointments table for actual completion status in real-time
     * @param string $status Order status
     * @param int|null $order_id Optional order ID to check appointments table for actual completion status
     */
    public function get_order_progress($status, $order_id = null)
    {
        // Return status: 'pending', 'in_progress', or 'completed'
        $steps = [
            'order_placed' => 'pending', // Will be checked from appointments table
            'ocular_visit' => 'pending',
            'in_fabrication' => 'pending',
            'installed' => 'pending',
            'completed' => 'pending'
        ];

        // If order_id is provided, check appointments and projectschedule tables for real-time status
        if ($order_id) {
            // Check Order Placed appointment
            $order_placed_apt = $this->db->where('OrderID', $order_id)
                                         ->where('Service', 'Order Placed')
                                         ->get('appointments')
                                         ->row();
            if ($order_placed_apt) {
                if ($order_placed_apt->Status === 'Complete') {
                    $steps['order_placed'] = 'completed';
                } elseif ($order_placed_apt->Status === 'In Progress') {
                    $steps['order_placed'] = 'in_progress';
                } elseif ($order_placed_apt->Status === 'Cancelled') {
                    // If cancelled, treat as pending
                    $steps['order_placed'] = 'pending';
                }
            } else {
                // If no Order Placed appointment exists, default to completed (order exists)
                $steps['order_placed'] = 'completed';
            }
            
            // Check Ocular Visit appointment
            $ocular_apt = $this->db->where('OrderID', $order_id)
                                   ->where('Service', 'Ocular Visit')
                                   ->get('appointments')
                                   ->row();
            if ($ocular_apt) {
                if ($ocular_apt->Status === 'Complete') {
                    $steps['ocular_visit'] = 'completed';
                } elseif ($ocular_apt->Status === 'In Progress') {
                    $steps['ocular_visit'] = 'in_progress';
                } elseif ($ocular_apt->Status === 'Cancelled') {
                    // If cancelled, treat as pending
                    $steps['ocular_visit'] = 'pending';
                }
            }
            
            // Check In Fabrication - check both appointments table (if used) and projectschedule table
            // First check appointments table (some systems may use this)
            $fabrication_apt = $this->db->where('OrderID', $order_id)
                                       ->where('Service', 'In Fabrication')
                                       ->get('appointments')
                                       ->row();
            if ($fabrication_apt) {
                if ($fabrication_apt->Status === 'Complete') {
                    $steps['in_fabrication'] = 'completed';
                } elseif ($fabrication_apt->Status === 'In Progress') {
                    $steps['in_fabrication'] = 'in_progress';
                } elseif ($fabrication_apt->Status === 'Cancelled') {
                    // If cancelled, treat as pending
                    $steps['in_fabrication'] = 'pending';
                }
            } else {
                // Check projectschedule table (primary source for fabrication status)
                if ($this->db->table_exists('projectschedule')) {
                    $fabrication_project = $this->db->where('OrderID', $order_id)
                                                   ->get('projectschedule')
                                                   ->row();
                    if ($fabrication_project) {
                        if ($fabrication_project->Status === 'Completed') {
                            $steps['in_fabrication'] = 'completed';
                        } elseif ($fabrication_project->Status === 'In progress') {
                            $steps['in_fabrication'] = 'in_progress';
                        } elseif ($fabrication_project->Status === 'Delayed') {
                            // If delayed, treat as in progress
                            $steps['in_fabrication'] = 'in_progress';
                        }
                    }
                }
            }
            
            // Check Installed appointment
            $installed_apt = $this->db->where('OrderID', $order_id)
                                     ->where('Service', 'Installed')
                                     ->get('appointments')
                                     ->row();
            if ($installed_apt) {
                if ($installed_apt->Status === 'Complete') {
                    $steps['installed'] = 'completed';
                    // If installed is complete, previous steps must also be complete
                    $steps['in_fabrication'] = 'completed';
                    $steps['ocular_visit'] = 'completed';
                } elseif ($installed_apt->Status === 'In Progress') {
                    $steps['installed'] = 'in_progress';
                    // If installed is in progress, previous steps must be completed
                    $steps['in_fabrication'] = 'completed';
                    $steps['ocular_visit'] = 'completed';
                } elseif ($installed_apt->Status === 'Cancelled') {
                    // If cancelled, treat as pending
                    $steps['installed'] = 'pending';
                }
            }
            
            // Check Completed appointment
            $completed_apt = $this->db->where('OrderID', $order_id)
                                     ->where('Service', 'Completed')
                                     ->get('appointments')
                                     ->row();
            if ($completed_apt) {
                if ($completed_apt->Status === 'Complete') {
                    $steps['completed'] = 'completed';
                    // If completed, all previous steps must also be complete
                    $steps['installed'] = 'completed';
                    $steps['in_fabrication'] = 'completed';
                    $steps['ocular_visit'] = 'completed';
                } elseif ($completed_apt->Status === 'In Progress') {
                    $steps['completed'] = 'in_progress';
                    // If completed is in progress, all previous steps must be completed
                    $steps['installed'] = 'completed';
                    $steps['in_fabrication'] = 'completed';
                    $steps['ocular_visit'] = 'completed';
                } elseif ($completed_apt->Status === 'Cancelled') {
                    // If cancelled, treat as pending
                    $steps['completed'] = 'pending';
                }
            }
            
            // If fabrication is in progress, ocular visit must be completed
            if ($steps['in_fabrication'] === 'in_progress' && $steps['ocular_visit'] === 'pending') {
                $steps['ocular_visit'] = 'completed';
            }
        }

        // Fallback to status-based logic if appointments not checked or not complete
        // Note: Order status indicates readiness, not completion. Only check appointments for actual completion.
        if ($steps['completed'] !== 'completed' && $status === 'Completed') {
            $steps['completed'] = 'completed';
            $steps['installed'] = 'completed';
            $steps['in_fabrication'] = 'completed';
            $steps['ocular_visit'] = 'completed';
        } elseif ($steps['installed'] === 'pending' && $status === 'Ready for Installation') {
            // Ready for Installation means fabrication is complete
            $steps['in_fabrication'] = 'completed';
            $steps['ocular_visit'] = 'completed';
            // Don't mark installed as completed - wait for actual installation appointment completion
        } elseif ($status === 'In Fabrication') {
            // In Fabrication status means ocular visit is complete and order is ready for fabrication
            // But fabrication itself is NOT complete yet - only mark ocular visit as complete
            if ($steps['ocular_visit'] === 'pending') {
                $steps['ocular_visit'] = 'completed';
            }
            // Do NOT mark in_fabrication as completed - wait for actual fabrication appointment completion
        } elseif ($status === 'Approved' && $steps['ocular_visit'] === 'pending') {
            // If status is still Approved, ocular visit hasn't been completed yet
            // This is just a fallback - appointments table should be the source of truth
        }

        return $steps;
    }

    /**
     * Get all order items (purchases) for a customer
     * Joins order, order_items, and product tables
     */
    public function get_customer_order_items($customer_id)
    {
        $this->db->select('
            oi.OrderItemID,
            oi.OrderID,
            oi.Product_ID,
            oi.Quantity,
            oi.EstimatePrice,
            oi.UnitPrice,
            oi.Dimensions,
            oi.GlassShape,
            oi.GlassType,
            oi.GlassThickness,
            oi.EdgeWork,
            oi.FrameType,
            oi.Engraving,
            p.ProductName,
            p.ImageUrl,
            p.Category,
            o.OrderDate,
            o.Status as OrderStatus,
            o.PaymentStatus,
            o.DeliveryAddress,
            COALESCE(
                o.PreferredInstallationDate,
                o.EstimatedDelivery,
                o.InstallationDate,
                DATE_ADD(o.OrderDate, INTERVAL 7 DAY)
            ) as DeliveryDate
        ');
        $this->db->from('order_items oi');
        $this->db->join('`order` o', 'o.OrderID = oi.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->where('o.Customer_ID', $customer_id);
        $this->db->order_by('o.OrderDate', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get order items by order ID (replaces get_order_customizations)
     */
    public function get_order_customizations($order_id)
    {
        $this->db->select('oi.*, p.ProductName, p.ImageUrl');
        $this->db->from('order_items oi');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->where('oi.OrderID', $order_id);
        return $this->db->get()->result();
    }

    /**
     * Calculate order summary
     */
    public function calculate_order_summary($order_id)
    {
        $items = $this->get_order_customizations($order_id);
        
        $subtotal = 0;
        $total_items = 0;
        
        foreach ($items as $item) {
            $subtotal += $item->EstimatePrice * $item->Quantity;
            $total_items += $item->Quantity;
        }
        
        $shipping = $total_items * 25;
        $handling = $total_items * 10;
        $total = $subtotal + $shipping + $handling;
        
        return [
            'items' => $total_items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'handling' => $handling,
            'total' => $total
        ];
    }

    /**
     * Save order items from cart items with transaction handling
     * Used in: ShopCon->place_order, ShopCon->submit_ewallet_payment (Checkout -> Payment)
     * Sequence: Cart -> Checkout -> Payment
     */
    public function save_order_customizations($order_id, $cart_items)
    {
        $this->db->trans_start();
        
        foreach ($cart_items as $item) {
            $base_price = $item->BasePrice ?? 0;
            $estimate_price = $item->EstimatePrice ?? $item->Price ?? $base_price;
            
            $order_item_data = [
                'OrderID' => $order_id,
                'Product_ID' => $item->Product_ID,
                'CustomizationID' => $item->CustomizationID ?? null,
                'Quantity' => $item->Quantity ?? 1,
                'UnitPrice' => $base_price,
                'EstimatePrice' => $estimate_price,
                'Dimensions' => $item->Dimensions ?? null,
                'GlassShape' => $item->GlassShape ?? null,
                'GlassType' => $item->GlassType ?? null,
                'GlassThickness' => $item->GlassThickness ?? null,
                'EdgeWork' => $item->EdgeWork ?? null,
                'FrameType' => $item->FrameType ?? null,
                'Engraving' => $item->Engraving ?? null,
                'DesignRef' => $item->DesignRef ?? null
            ];
            
            $result = $this->db->insert('order_items', $order_item_data);
            
            if (!$result) {
                $this->db->trans_rollback();
                log_message('error', 'Failed to insert order item for OrderID: ' . $order_id);
                return false;
            }
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Save order customizations transaction failed for OrderID: ' . $order_id);
            return false;
        }
        
        return true;
    }

    // ===================== HOME-LOGIN PAGE METHODS =====================

    /**
     * Count orders by status for a customer
     */
    public function count_orders_by_status($customer_id, $status = null)
    {
        $this->db->where('Customer_ID', $customer_id);
        if ($status) {
            if (is_array($status)) {
                $this->db->where_in('Status', $status);
            } else {
                $this->db->where('Status', $status);
            }
        }
        return $this->db->count_all_results('order');
    }

    /**
     * Get the most recent order activity for a customer
     */
    public function get_recent_order_activity($customer_id)
    {
        $this->db->select('
            o.OrderID,
            o.OrderNumber,
            o.Status,
            o.OrderDate,
            p.ProductName
        ');
        $this->db->from('`order` o');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->where('o.Customer_ID', $customer_id);
        $this->db->where('o.Status !=', 'Completed');
        $this->db->order_by('o.OrderDate', 'DESC');
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }

    /**
     * Get orders for dashboard display with product names
     * Limits to recent orders for performance
     */
    public function get_customer_orders_with_products($customer_id, $limit = 10)
    {
        $this->db->select('
            o.OrderID,
            o.OrderNumber,
            o.OrderDate,
            o.Status,
            o.PaymentStatus,
            oi.Product_ID,
            p.ProductName,
            p.ImageUrl
        ');
        $this->db->from('`order` o');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->where('o.Customer_ID', $customer_id);
        $this->db->order_by('o.OrderDate', 'DESC');
        $this->db->group_by('o.OrderID');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }

    /**
     * Get activity feed for dashboard
     * Returns recent order status changes with timestamps
     */
    public function get_activity_feed($customer_id, $limit = 5)
    {
        $this->db->select('
            o.OrderID,
            o.OrderNumber,
            o.Status,
            o.OrderDate,
            p.ProductName
        ');
        $this->db->from('`order` o');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->where('o.Customer_ID', $customer_id);
        $this->db->order_by('o.OrderDate', 'DESC');
        $this->db->group_by('o.OrderID');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }

    /**
     * Get the time since last update for dashboard
     */
    public function get_last_update_time($customer_id)
    {
        $this->db->select('OrderDate');
        $this->db->where('Customer_ID', $customer_id);
        $this->db->order_by('OrderDate', 'DESC');
        $this->db->limit(1);
        $result = $this->db->get('order')->row();
        
        return $result ? $result->OrderDate : null;
    }

    /**
     * Save payment receipt for E-Wallet orders with transaction handling
     * Used in: ShopCon->submit_ewallet_payment (Payment)
     * Sequence: Checkout -> Payment -> Complete
     */
    public function save_payment_receipt($order_id, $receipt_path, $amount = 0)
    {
        $this->db->trans_start();
        
        // Get order details for payment record
        $order = $this->get_order_with_customer($order_id);
        if ($order && $amount <= 0) {
            $amount = $order->TotalAmount;
        }

        // Get customer and product info
        $customer_name = '';
        $product_name = '';
        if ($order) {
            $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
            // Get product name from order items
            $order_items = $this->get_order_customizations($order_id);
            if (!empty($order_items)) {
                $product_name = $order_items[0]->ProductName ?? '';
            }
        }

        // Check if payment record exists
        $existing = $this->db->where('OrderID', $order_id)->get('payment')->row();
        
        if ($existing) {
            // Update existing payment record
            $result = $this->db->where('OrderID', $order_id)
                            ->update('payment', [
                                'ReceiptPath' => $receipt_path,
                                'Amount' => $amount,
                                'Status' => 'Pending',
                                'CustomerName' => $customer_name,
                                'ProductName' => $product_name
                            ]);
        } else {
            // Create new payment record
            $result = $this->db->insert('payment', [
                'OrderID' => $order_id,
                'Amount' => $amount,
                'ReceiptPath' => $receipt_path,
                'Status' => 'Pending',
                'CustomerName' => $customer_name,
                'ProductName' => $product_name,
                'PaymentMethod' => 'E-Wallet'
            ]);
        }
        
        // Update order payment method
        $this->update_payment_method($order_id, 'E-Wallet');
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Save payment receipt transaction failed for OrderID: ' . $order_id);
            return false;
        }
        
        return $result;
    }

    /**
     * Update order payment method
     */
    public function update_payment_method($order_id, $payment_method)
    {
        // Check if order table has PaymentMethod column, otherwise skip
        return $this->db->where('OrderID', $order_id)
                        ->update('order', ['PaymentMethod' => $payment_method]);
    }

    // ===================== ORDER FLOW FUNCTIONS (Based on ORDER_FLOW_DOCUMENTATION.md) =====================

    /**
     * Stage 3: Request Admin Approval
     * Sales Rep requests admin approval for a pending order
     * Status: 'Pending Review' → 'Awaiting Admin'
     * 
     * @param int $order_id Order ID
     * @param int $sales_rep_id Sales Representative ID
     * @param string $notes Optional notes from sales rep
     * @return array ['success' => bool, 'message' => string]
     */
    public function request_admin_approval($order_id, $sales_rep_id, $notes = '')
    {
        $this->db->trans_start();

        // Handle both numeric OrderID and OrderNumber format
        // If it's a string that looks like OrderNumber, try to find by OrderNumber first
        $order = null;
        if (is_string($order_id) && (preg_match('/^GI\d+$/i', $order_id) || preg_match('/^#?GI\d+$/i', $order_id))) {
            // Clean up the order ID (remove # if present)
            $order_number = str_replace('#', '', $order_id);
            $order = $this->db->where('OrderNumber', $order_number)->get('`order`')->row();
            if ($order) {
                $order_id = $order->OrderID; // Use numeric ID for rest of function
            }
        }
        
        // If not found by OrderNumber, try by numeric OrderID
        if (!$order) {
            $order = $this->get_order($order_id);
        }
        
        if (!$order) {
            $this->db->trans_rollback();
            log_message('error', 'Order_model::request_admin_approval - Order not found. Input: ' . $order_id);
            return ['success' => false, 'message' => 'Order not found'];
        }

        // Normalize status - handle empty string, NULL, or old 'Pending' status
        $order_status = $order->Status ?? '';
        if (empty($order_status) || trim($order_status) === '' || $order_status === 'Pending') {
            $order_status = 'Pending Review';
            // Update the order status if it was empty
            $this->db->where('OrderID', $order->OrderID)->update('`order`', ['Status' => 'Pending Review']);
        }

        if ($order_status !== 'Pending Review') {
            $this->db->trans_rollback();
            log_message('error', 'Order_model::request_admin_approval - Order status invalid. OrderID: ' . $order->OrderID . ', Status: ' . $order_status);
            return ['success' => false, 'message' => 'Order is not in Pending Review status. Current status: ' . $order_status];
        }

        if ($order->SalesRep_ID != $sales_rep_id) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Order does not belong to this sales representative'];
        }

        // Update order status
        $result = $this->update_order_status($order_id, 'Awaiting Admin');

        if (!$result) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Failed to update order status'];
        }

        // Insert into awaiting_admin_orders (legacy table for backward compatibility)
        if ($this->db->table_exists('awaiting_admin_orders')) {
            // Get order details from legacy pending_review_orders or build from order table
            $this->db->select('o.*, oi.*, p.ProductName');
            $this->db->from('`order` o');
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
            $this->db->where('o.OrderID', $order_id);
            $this->db->limit(1);
            $order_details = $this->db->get()->row();

            if ($order_details) {
                $awaiting_data = [
                    'OrderID' => $order->OrderID, // Numeric OrderID (int)
                    'OrderNumber' => $order->OrderNumber, // OrderNumber (varchar)
                    'ProductName' => $order_details->ProductName ?? 'N/A',
                    'Address' => $order->DeliveryAddress ?? '',
                    'OrderDate' => $order->OrderDate,
                    'TotalQuotation' => $order->TotalAmount,
                    'Customer_ID' => $order->Customer_ID,
                    'SalesRep_ID' => $order->SalesRep_ID,
                    'SalesRepNotes' => $notes // Notes from sales rep when requesting approval
                ];

                $this->db->insert('awaiting_admin_orders', $awaiting_data);
            }
        }

        // Log activity
        if ($this->db->table_exists('system_activity_log')) {
            $this->db->insert('system_activity_log', [
                'Action' => 'Approval Requested',
                'Description' => "Order {$order->OrderNumber} approval requested by Sales Rep. Notes: " . ($notes ?: 'None'),
                'Role' => 'Sales Representative',
                'UserID' => $sales_rep_id,
                'RelatedID' => $order_id,
                'RelatedType' => 'Order',
                'Timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Transaction failed'];
        }

        return ['success' => true, 'message' => 'Approval requested successfully. Order is now awaiting admin review.'];
    }

    /**
     * Stage 4: Admin Approve Order
     * Admin approves an order awaiting review
     * Status: 'Awaiting Admin' → 'Ready to Approve' (with AdminStatus = 'Approved')
     * 
     * @param int $order_id Order ID
     * @param int $admin_id Admin ID
     * @param string $admin_notes Optional admin notes
     * @return array ['success' => bool, 'message' => string]
     */
    public function admin_approve_order($order_id, $admin_id, $admin_notes = '')
    {
        $this->db->trans_start();

        // Validate order exists and is in correct status
        $order = $this->get_order($order_id);
        if (!$order) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Order not found'];
        }

        if ($order->Status !== 'Awaiting Admin') {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Order is not awaiting admin approval'];
        }

        // Update order status
        $update_data = [
            'Status' => 'Ready to Approve',
            'ApprovedBy_Admin_ID' => $admin_id,
            'Approved_Date' => date('Y-m-d H:i:s')
        ];
        $result = $this->db->where('OrderID', $order_id)->update('order', $update_data);

        if (!$result) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Failed to update order status'];
        }

        // Insert into ready_to_approve_orders (legacy table)
        if ($this->db->table_exists('ready_to_approve_orders')) {
            // Get order details
            $this->db->select('o.*, oi.*, p.ProductName');
            $this->db->from('`order` o');
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
            $this->db->where('o.OrderID', $order_id);
            $this->db->limit(1);
            $order_details = $this->db->get()->row();

            if ($order_details) {
                $ready_data = [
                    'OrderID' => $order->OrderNumber,
                    'ProductName' => $order_details->ProductName ?? 'N/A',
                    'Address' => $order->DeliveryAddress ?? '',
                    'OrderDate' => $order->OrderDate,
                    'Shape' => $order_details->GlassShape ?? '',
                    'Dimension' => $order_details->Dimensions ?? '',
                    'Type' => $order_details->GlassType ?? '',
                    'Thickness' => $order_details->GlassThickness ?? '',
                    'EdgeWork' => $order_details->EdgeWork ?? '',
                    'FrameType' => $order_details->FrameType ?? '',
                    'Engraving' => $order_details->Engraving ?? '',
                    'FileAttached' => $order_details->DesignRef ?? null,
                    'TotalQuotation' => $order->TotalAmount,
                    'Customer_ID' => $order->Customer_ID,
                    'SalesRep_ID' => $order->SalesRep_ID,
                    'AdminStatus' => 'Approved',
                    'AdminNotes' => $admin_notes,
                    'AdminReviewed_Date' => date('Y-m-d H:i:s')
                ];

                $this->db->insert('ready_to_approve_orders', $ready_data);
            }
        }

        // Delete from awaiting_admin_orders (legacy table)
        if ($this->db->table_exists('awaiting_admin_orders')) {
            $this->db->where('OrderID', $order->OrderID); // Use numeric OrderID
            $this->db->or_where('OrderNumber', $order->OrderNumber); // Also check OrderNumber for safety
            $this->db->delete('awaiting_admin_orders');
        }

        // Log activity
        if ($this->db->table_exists('system_activity_log')) {
            $this->db->insert('system_activity_log', [
                'Action' => 'Order Approved by Admin',
                'Description' => "Order {$order->OrderNumber} approved by Admin. Notes: " . ($admin_notes ?: 'None'),
                'Role' => 'Admin',
                'UserID' => $admin_id,
                'RelatedID' => $order_id,
                'RelatedType' => 'Order',
                'Timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Transaction failed'];
        }

        return ['success' => true, 'message' => 'Order approved successfully. Ready for sales rep final approval.'];
    }

    /**
     * Stage 4: Admin Disapprove Order
     * Admin disapproves an order awaiting review
     * Status: 'Awaiting Admin' → 'Ready to Approve' (with AdminStatus = 'Disapproved')
     * 
     * @param int $order_id Order ID
     * @param int $admin_id Admin ID
     * @param string $disapproval_reason Required reason for disapproval
     * @return array ['success' => bool, 'message' => string]
     */
    public function admin_disapprove_order($order_id, $admin_id, $disapproval_reason)
    {
        if (empty($disapproval_reason)) {
            return ['success' => false, 'message' => 'Disapproval reason is required'];
        }

        $this->db->trans_start();

        // Validate order exists and is in correct status
        $order = $this->get_order($order_id);
        if (!$order) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Order not found'];
        }

        if ($order->Status !== 'Awaiting Admin') {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Order is not awaiting admin approval'];
        }

        // Update order status
        $update_data = [
            'Status' => 'Ready to Approve',
            'DisapprovedBy' => 'Admin',
            'DisapprovedBy_ID' => $admin_id,
            'DisapprovalReason' => $disapproval_reason,
            'Disapproved_Date' => date('Y-m-d H:i:s')
        ];
        $result = $this->db->where('OrderID', $order_id)->update('order', $update_data);

        if (!$result) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Failed to update order status'];
        }

        // Insert into ready_to_approve_orders (legacy table)
        if ($this->db->table_exists('ready_to_approve_orders')) {
            // Get order details
            $this->db->select('o.*, oi.*, p.ProductName');
            $this->db->from('`order` o');
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
            $this->db->where('o.OrderID', $order_id);
            $this->db->limit(1);
            $order_details = $this->db->get()->row();

            if ($order_details) {
                $ready_data = [
                    'OrderID' => $order->OrderNumber,
                    'ProductName' => $order_details->ProductName ?? 'N/A',
                    'Address' => $order->DeliveryAddress ?? '',
                    'OrderDate' => $order->OrderDate,
                    'Shape' => $order_details->GlassShape ?? '',
                    'Dimension' => $order_details->Dimensions ?? '',
                    'Type' => $order_details->GlassType ?? '',
                    'Thickness' => $order_details->GlassThickness ?? '',
                    'EdgeWork' => $order_details->EdgeWork ?? '',
                    'FrameType' => $order_details->FrameType ?? '',
                    'Engraving' => $order_details->Engraving ?? '',
                    'FileAttached' => $order_details->DesignRef ?? null,
                    'TotalQuotation' => $order->TotalAmount,
                    'Customer_ID' => $order->Customer_ID,
                    'SalesRep_ID' => $order->SalesRep_ID,
                    'AdminStatus' => 'Disapproved',
                    'AdminNotes' => $disapproval_reason,
                    'AdminReviewed_Date' => date('Y-m-d H:i:s')
                ];

                $this->db->insert('ready_to_approve_orders', $ready_data);
            }
        }

        // Delete from awaiting_admin_orders (legacy table)
        if ($this->db->table_exists('awaiting_admin_orders')) {
            $this->db->where('OrderID', $order->OrderID); // Use numeric OrderID
            $this->db->or_where('OrderNumber', $order->OrderNumber); // Also check OrderNumber for safety
            $this->db->delete('awaiting_admin_orders');
        }

        // Log activity
        if ($this->db->table_exists('system_activity_log')) {
            $this->db->insert('system_activity_log', [
                'Action' => 'Order Disapproved by Admin',
                'Description' => "Order {$order->OrderNumber} disapproved by Admin. Reason: {$disapproval_reason}",
                'Role' => 'Admin',
                'UserID' => $admin_id,
                'RelatedID' => $order_id,
                'RelatedType' => 'Order',
                'Timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Transaction failed'];
        }

        return ['success' => true, 'message' => 'Order disapproved. Sales rep will be notified.'];
    }

    /**
     * Stage 5: Sales Rep Final Approve Order
     * Sales Rep final approval after admin review
     * Status: 'Ready to Approve' → 'Approved'
     * Creates payment record and notifies customer
     * 
     * @param int $order_id Order ID
     * @param int $sales_rep_id Sales Representative ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function sales_rep_final_approve($order_id, $sales_rep_id)
    {
        $this->db->trans_start();

        // Validate order exists and is in correct status
        $order = $this->get_order($order_id);
        if (!$order) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Order not found'];
        }

        if ($order->Status !== 'Ready to Approve') {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Order is not ready to approve'];
        }

        if ($order->SalesRep_ID != $sales_rep_id) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Order does not belong to this sales representative'];
        }

        // Check AdminStatus from ready_to_approve_orders
        $admin_status = null;
        if ($this->db->table_exists('ready_to_approve_orders')) {
            $this->db->where('OrderID', $order->OrderNumber);
            $ready_order = $this->db->get('ready_to_approve_orders')->row();
            if ($ready_order) {
                $admin_status = $ready_order->AdminStatus ?? null;
            }
        }

        if ($admin_status !== 'Approved') {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Order has not been approved by admin'];
        }

        // Update order status
        $update_data = [
            'Status' => 'Approved',
            'ApprovedBy_SalesRep_ID' => $sales_rep_id,
            'Approved_Date' => date('Y-m-d H:i:s')
        ];
        $result = $this->db->where('OrderID', $order_id)->update('order', $update_data);

        if (!$result) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Failed to update order status'];
        }

        // Create payment record if not exists
        $existing_payment = $this->get_order_payment($order_id);
        if (!$existing_payment) {
            // Get customer and product info
            $order_with_customer = $this->get_order_with_customer($order_id);
            $order_items = $this->get_order_customizations($order_id);
            
            $customer_name = '';
            $product_name = '';
            if ($order_with_customer) {
                $customer_name = trim(($order_with_customer->First_Name ?? '') . ' ' . ($order_with_customer->Last_Name ?? ''));
            }
            if (!empty($order_items)) {
                $product_name = $order_items[0]->ProductName ?? 'N/A';
            }

            $payment_data = [
                'OrderID' => $order_id,
                'CustomerName' => $customer_name,
                'ProductName' => $product_name,
                'PaymentMethod' => $order->PaymentMethod ?? 'E-Wallet',
                'Amount' => $order->TotalAmount,
                'Status' => 'Pending',
                'Payment_Date' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('payment', $payment_data);
        }

        // Insert into approved_orders (legacy table)
        if ($this->db->table_exists('approved_orders')) {
            $this->db->select('o.*, oi.*, p.ProductName');
            $this->db->from('`order` o');
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
            $this->db->where('o.OrderID', $order_id);
            $this->db->limit(1);
            $order_details = $this->db->get()->row();

            if ($order_details) {
                $approved_data = [
                    'OrderID' => $order->OrderID, // Use numeric OrderID
                    'OrderNumber' => $order->OrderNumber,
                    'ProductName' => $order_details->ProductName ?? 'N/A',
                    'Address' => $order->DeliveryAddress ?? '',
                    'OrderDate' => $order->OrderDate,
                    'TotalQuotation' => $order->TotalAmount,
                    'Customer_ID' => $order->Customer_ID,
                    'SalesRep_ID' => $order->SalesRep_ID,
                    'ApprovedBy_SalesRep_ID' => $sales_rep_id,
                    'Approved_Date' => date('Y-m-d H:i:s'),
                    'CustomerNotified' => 0,
                    'CustomerNotified_Date' => null
                ];

                $this->db->insert('approved_orders', $approved_data);
            }
        }

        // Delete from ready_to_approve_orders (legacy table)
        if ($this->db->table_exists('ready_to_approve_orders')) {
            $this->db->where('OrderID', $order->OrderNumber);
            $this->db->delete('ready_to_approve_orders');
        }

        // Log activity
        if ($this->db->table_exists('system_activity_log')) {
            $this->db->insert('system_activity_log', [
                'Action' => 'Order Approved',
                'Description' => "Order {$order->OrderNumber} has been approved by Sales Rep. Customer can now proceed with payment.",
                'Role' => 'Sales Representative',
                'UserID' => $sales_rep_id,
                'RelatedID' => $order_id,
                'RelatedType' => 'Order',
                'Timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Transaction failed'];
        }

        // Notify customer (async - can be implemented later)
        $this->notify_customer($order->Customer_ID, $order_id, 'approved', $order->TotalAmount);

        return ['success' => true, 'message' => 'Order approved successfully. Customer has been notified.'];
    }

    /**
     * Stage 5: Sales Rep Final Disapprove Order
     * Sales Rep final disapproval after admin review
     * Status: 'Ready to Approve' → 'Disapproved'
     * 
     * @param int $order_id Order ID
     * @param int $sales_rep_id Sales Representative ID
     * @param string $reason Optional reason (combines with admin reason if exists)
     * @return array ['success' => bool, 'message' => string]
     */
    public function sales_rep_final_disapprove($order_id, $sales_rep_id, $reason = '')
    {
        try {
            $this->db->trans_start();

            // Validate order exists and is in correct status
            $order = $this->get_order($order_id);
            if (!$order) {
                $this->db->trans_rollback();
                log_message('error', 'Order_model::sales_rep_final_disapprove - Order not found: ' . $order_id);
                return ['success' => false, 'message' => 'Order not found'];
            }

            if ($order->Status !== 'Ready to Approve') {
                $this->db->trans_rollback();
                log_message('error', 'Order_model::sales_rep_final_disapprove - Order status invalid. Expected: Ready to Approve, Got: ' . ($order->Status ?? 'null'));
                return ['success' => false, 'message' => 'Order is not ready to approve'];
            }

            if ($order->SalesRep_ID != $sales_rep_id) {
                $this->db->trans_rollback();
                log_message('error', 'Order_model::sales_rep_final_disapprove - Sales rep mismatch. Order SalesRep_ID: ' . ($order->SalesRep_ID ?? 'null') . ', Provided: ' . $sales_rep_id);
                return ['success' => false, 'message' => 'Order does not belong to this sales representative'];
            }

        // Get admin disapproval reason if exists
        $admin_notes = '';
        if ($this->db->table_exists('ready_to_approve_orders')) {
            $this->db->where('OrderID', $order->OrderNumber);
            $ready_order = $this->db->get('ready_to_approve_orders')->row();
            if ($ready_order && isset($ready_order->AdminNotes)) {
                $admin_notes = $ready_order->AdminNotes;
            }
        }

        // Combine admin reason with sales rep reason
        $final_reason = $reason;
        if ($admin_notes && $reason) {
            $final_reason = 'Admin Reason: ' . $admin_notes . ' | Sales Rep Finalization: ' . $reason;
        } elseif ($admin_notes) {
            $final_reason = 'Admin Reason: ' . $admin_notes . ' | Finalized by Sales Representative';
        } elseif (!$reason) {
            $final_reason = 'Order disapproved by Sales Representative';
        }

        // Update order status
        $update_data = [
            'Status' => 'Disapproved',
            'DisapprovedBy' => 'Sales Rep',
            'DisapprovedBy_ID' => $sales_rep_id,
            'DisapprovalReason' => $final_reason,
            'Disapproved_Date' => date('Y-m-d H:i:s')
        ];
        $result = $this->db->where('OrderID', $order_id)->update('order', $update_data);

        if (!$result) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Failed to update order status'];
        }

        // Insert into disapproved_orders (legacy table)
        if ($this->db->table_exists('disapproved_orders')) {
            try {
                $this->db->select('o.*, oi.*, p.ProductName');
                $this->db->from('`order` o');
                $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
                $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
                $this->db->where('o.OrderID', $order_id);
                $this->db->limit(1);
                $order_details = $this->db->get()->row();

                if ($order_details) {
                    // Build data array with only fields that exist in the table
                    $disapproved_data = [
                        'OrderID' => (int)$order_id, // Required field
                        'OrderNumber' => $order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT),
                        'ProductName' => $order_details->ProductName ?? 'N/A',
                        'Address' => $order->DeliveryAddress ?? '',
                        'OrderDate' => $order->OrderDate ?? date('Y-m-d H:i:s'),
                        'TotalQuotation' => $order->TotalAmount ?? 0.00,
                        'Customer_ID' => $order->Customer_ID ?? null,
                        'SalesRep_ID' => $order->SalesRep_ID ?? null,
                        'DisapprovedBy' => 'Sales Rep',
                        'DisapprovedBy_ID' => $sales_rep_id,
                        'DisapprovalReason' => $final_reason,
                        'Disapproved_Date' => date('Y-m-d H:i:s')
                    ];
                    
                    // Only add fields that exist in the table (check if columns exist)
                    $table_fields = $this->db->list_fields('disapproved_orders');
                    $fields_to_add = [
                        'Shape' => $order_details->GlassShape ?? '',
                        'Dimension' => $order_details->Dimensions ?? '',
                        'Type' => $order_details->GlassType ?? '',
                        'Thickness' => $order_details->GlassThickness ?? '',
                        'EdgeWork' => $order_details->EdgeWork ?? '',
                        'FrameType' => $order_details->FrameType ?? '',
                        'Engraving' => $order_details->Engraving ?? '',
                        'FileAttached' => $order_details->DesignRef ?? null
                    ];
                    
                    foreach ($fields_to_add as $field => $value) {
                        if (in_array($field, $table_fields)) {
                            $disapproved_data[$field] = $value;
                        }
                    }

                    $insert_result = $this->db->insert('disapproved_orders', $disapproved_data);
                    if (!$insert_result) {
                        $error = $this->db->error();
                        log_message('error', 'Order_model::sales_rep_final_disapprove - Failed to insert into disapproved_orders: ' . json_encode($error) . ' | Data: ' . json_encode($disapproved_data));
                        // Don't fail the transaction for legacy table insert failure
                    }
                } else {
                    log_message('debug', 'Order_model::sales_rep_final_disapprove - No order_details found for order_id: ' . $order_id);
                }
            } catch (Exception $e) {
                log_message('error', 'Order_model::sales_rep_final_disapprove - Exception inserting into disapproved_orders: ' . $e->getMessage());
                // Don't fail the transaction for legacy table insert failure
            }
        }

        // Delete from ready_to_approve_orders (legacy table)
        if ($this->db->table_exists('ready_to_approve_orders')) {
            $this->db->where('OrderID', $order->OrderNumber);
            $this->db->delete('ready_to_approve_orders');
        }

        // Log activity
        if ($this->db->table_exists('system_activity_log')) {
            $this->db->insert('system_activity_log', [
                'Action' => 'Order Disapproved',
                'Description' => "Order {$order->OrderNumber} disapproved by Sales Rep. Reason: {$final_reason}",
                'Role' => 'Sales Representative',
                'UserID' => $sales_rep_id,
                'RelatedID' => $order_id,
                'RelatedType' => 'Order',
                'Timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $error = $this->db->error();
            log_message('error', 'Order_model::sales_rep_final_disapprove - Transaction failed: ' . json_encode($error));
            return ['success' => false, 'message' => 'Transaction failed: ' . ($error['message'] ?? 'Unknown error')];
        }

        return ['success' => true, 'message' => 'Order disapproved successfully.'];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Order_model::sales_rep_final_disapprove - Exception: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }

    /**
     * Get orders by status for Sales Rep
     * 
     * @param int $sales_rep_id Sales Representative ID
     * @param string $status Order status ('Pending Review', 'Awaiting Admin', 'Ready to Approve')
     * @return array Array of order objects
     */
    public function get_sales_rep_orders($sales_rep_id, $status = null)
    {
        $this->db->select('
            o.*,
            oi.Product_ID,
            oi.Dimensions,
            oi.GlassShape,
            oi.GlassType,
            oi.GlassThickness,
            oi.EdgeWork,
            oi.FrameType,
            oi.Engraving,
            oi.DesignRef,
            p.ProductName,
            u.First_Name,
            u.Last_Name,
            u.PhoneNum
        ');
        $this->db->from('`order` o');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
        $this->db->where('o.SalesRep_ID', $sales_rep_id);

        if ($status) {
            $this->db->where('o.Status', $status);
        }

        $this->db->group_by('o.OrderID');
        $this->db->order_by('o.OrderDate', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Get orders awaiting admin approval
     * 
     * @return array Array of order objects
     */
    public function get_awaiting_admin_orders()
    {
        $this->db->select('
            o.*,
            oi.Product_ID,
            oi.Dimensions,
            oi.GlassShape,
            oi.GlassType,
            oi.GlassThickness,
            oi.EdgeWork,
            oi.FrameType,
            oi.Engraving,
            oi.DesignRef,
            p.ProductName,
            u.First_Name as Customer_First_Name,
            u.Last_Name as Customer_Last_Name,
            u.Email as Customer_Email,
            u.PhoneNum as Customer_Phone,
            sr.First_Name as SalesRep_First_Name,
            sr.Last_Name as SalesRep_Last_Name
        ');
        $this->db->from('`order` o');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
        $this->db->join('user sr', 'sr.UserID = o.SalesRep_ID', 'left');
        $this->db->where('o.Status', 'Awaiting Admin');
        $this->db->group_by('o.OrderID');
        $this->db->order_by('o.OrderDate', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Get orders ready to approve (with AdminStatus)
     * 
     * @param int $sales_rep_id Sales Representative ID (optional)
     * @return array Array of order objects with AdminStatus
     */
    public function get_ready_to_approve_orders($sales_rep_id = null)
    {
        // Check if ready_to_approve_orders table exists
        $table_exists = $this->db->table_exists('ready_to_approve_orders');
        
        if ($table_exists) {
            // Table exists - use full query with JOIN
            $this->db->select('
                o.*,
                oi.Product_ID,
                oi.Dimensions,
                oi.GlassShape,
                oi.GlassType,
                oi.GlassThickness,
                oi.EdgeWork,
                oi.FrameType,
                oi.Engraving,
                oi.DesignRef,
                p.ProductName,
                rto.AdminStatus,
                rto.AdminNotes,
                rto.AdminReviewed_Date
            ');
            $this->db->from('`order` o');
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
            $this->db->join('ready_to_approve_orders rto', 'rto.OrderID = o.OrderNumber', 'left');
        } else {
            // Table doesn't exist - return orders without admin status fields
            $this->db->select('
                o.*,
                oi.Product_ID,
                oi.Dimensions,
                oi.GlassShape,
                oi.GlassType,
                oi.GlassThickness,
                oi.EdgeWork,
                oi.FrameType,
                oi.Engraving,
                oi.DesignRef,
                p.ProductName,
                NULL as AdminStatus,
                NULL as AdminNotes,
                NULL as AdminReviewed_Date
            ');
            $this->db->from('`order` o');
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        }
        
        $this->db->where('o.Status', 'Ready to Approve');

        if ($sales_rep_id) {
            $this->db->where('o.SalesRep_ID', $sales_rep_id);
        }

        $this->db->group_by('o.OrderID');
        $this->db->order_by('o.OrderDate', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Validate status transition
     * Ensures order status transitions follow the defined flow
     * 
     * @param string $current_status Current order status
     * @param string $new_status Desired new status
     * @param string $role User role performing the transition
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validate_status_transition($current_status, $new_status, $role = 'System')
    {
        $valid_transitions = [
            'Pending Review' => ['Awaiting Admin', 'Disapproved'],
            'Awaiting Admin' => ['Ready to Approve', 'Disapproved'],
            'Ready to Approve' => ['Approved', 'Disapproved'],
            'Approved' => ['In Fabrication', 'Cancelled'],
            'In Fabrication' => ['Ready for Installation', 'Cancelled'],
            'Ready for Installation' => ['Completed', 'Cancelled'],
            'Disapproved' => [], // Terminal state
            'Completed' => [], // Terminal state
            'Cancelled' => [], // Terminal state
            'Returned' => [] // Terminal state
        ];

        // Check if transition is valid
        if (!isset($valid_transitions[$current_status])) {
            return ['valid' => false, 'message' => "Invalid current status: {$current_status}"];
        }

        if (!in_array($new_status, $valid_transitions[$current_status])) {
            return [
                'valid' => false,
                'message' => "Cannot transition from '{$current_status}' to '{$new_status}'. Valid transitions: " . implode(', ', $valid_transitions[$current_status])
            ];
        }

        // Role-based validation
        $role_restrictions = [
            'Sales Representative' => [
                'Pending Review' => ['Awaiting Admin'],
                'Ready to Approve' => ['Approved', 'Disapproved']
            ],
            'Admin' => [
                'Awaiting Admin' => ['Ready to Approve']
            ]
        ];

        if (isset($role_restrictions[$role])) {
            if (isset($role_restrictions[$role][$current_status])) {
                if (!in_array($new_status, $role_restrictions[$role][$current_status])) {
                    return [
                        'valid' => false,
                        'message' => "Role '{$role}' cannot transition from '{$current_status}' to '{$new_status}'"
                    ];
                }
            }
        }

        return ['valid' => true, 'message' => 'Transition is valid'];
    }

    /**
     * Notify customer of order status change
     * 
     * @param int $customer_id Customer ID
     * @param int $order_id Order ID
     * @param string $status_type Status type ('approved', 'disapproved', etc.)
     * @param float $total_amount Order total amount
     * @return bool Success status
     */
    private function notify_customer($customer_id, $order_id, $status_type, $total_amount = 0)
    {
        // Get customer email
        $this->db->select('u.Email, u.First_Name, u.Last_Name');
        $this->db->from('customer c');
        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
        $this->db->where('c.Customer_ID', $customer_id);
        $customer = $this->db->get()->row();

        if (!$customer || !$customer->Email) {
            log_message('error', "Cannot notify customer: Customer ID {$customer_id} not found or has no email");
            return false;
        }

        // Update notification status in order table
        $this->db->where('OrderID', $order_id);
        $this->db->update('order', [
            'CustomerNotified' => 1,
            'CustomerNotified_Date' => date('Y-m-d H:i:s')
        ]);

        // Update approved_orders if exists
        if ($this->db->table_exists('approved_orders')) {
            $order = $this->get_order($order_id);
            if ($order) {
                $this->db->where('OrderID', $order->OrderNumber);
                $this->db->update('approved_orders', [
                    'CustomerNotified' => 1,
                    'CustomerNotified_Date' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Log notification (actual email/SMS sending can be implemented here)
        $message = "Order #{$order_id} has been {$status_type}. ";
        if ($status_type === 'approved') {
            $message .= "Total: ₱" . number_format($total_amount, 2) . ". Customer can proceed with payment (E-Wallet or Cash on Delivery).";
        }

        log_message('info', "Customer notification: {$customer->Email} - {$message}");

        // TODO: Implement actual email sending
        // $this->load->library('email');
        // $this->email->from('noreply@glassify.com', 'Glassify');
        // $this->email->to($customer->Email);
        // $this->email->subject('Order ' . ucfirst($status_type) . ' - Ready for Payment');
        // $this->email->message($message);
        // $this->email->send();

        return true;
    }

    /**
     * Create payment record for approved order
     * 
     * @param int $order_id Order ID
     * @return bool Success status
     */
    public function create_payment_record($order_id)
    {
        $order = $this->get_order_with_customer($order_id);
        if (!$order) {
            return false;
        }

        // Check if payment already exists
        $existing = $this->get_order_payment($order_id);
        if ($existing) {
            return true; // Payment record already exists
        }

        // Get product name from order items
        $order_items = $this->get_order_customizations($order_id);
        $product_name = 'N/A';
        if (!empty($order_items)) {
            $product_name = $order_items[0]->ProductName ?? 'N/A';
        }

        $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? ''));

        $payment_data = [
            'OrderID' => $order_id,
            'CustomerName' => $customer_name,
            'ProductName' => $product_name,
            'PaymentMethod' => $order->PaymentMethod ?? 'E-Wallet',
            'Amount' => $order->TotalAmount,
            'Status' => 'Pending',
            'Payment_Date' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('payment', $payment_data);
    }

    /**
     * Get order count by status for Sales Rep
     * 
     * @param int $sales_rep_id Sales Representative ID
     * @param string $status Order status
     * @return int Count
     */
    public function count_sales_rep_orders_by_status($sales_rep_id, $status)
    {
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('Status', $status);
        return $this->db->count_all_results('order');
    }

    /**
     * Get order details for popup display (Sales Rep)
     * 
     * @param int $order_id Order ID
     * @return object|null Order object with all details
     */
    public function get_order_details_for_popup($order_id)
    {
        // Handle both numeric ID and OrderNumber format
        // First, check if it looks like an OrderNumber format (GI001, etc.)
        $order_exists = null;
        $order_id_clean = str_replace('#', '', (string)$order_id);
        
        // If it looks like OrderNumber format (GI001), try that first
        if (preg_match('/^GI\d+$/i', $order_id_clean)) {
            $order_exists = $this->db->where('OrderNumber', $order_id_clean)->get('`order`')->row();
            if ($order_exists) {
                $order_id = $order_exists->OrderID; // Use numeric ID for rest of query
            }
        }
        
        // If not found and it's numeric, try by numeric OrderID
        if (!$order_exists && is_numeric($order_id_clean)) {
            $order_exists = $this->db->where('OrderID', (int)$order_id_clean)->get('`order`')->row();
            if ($order_exists) {
                $order_id = $order_exists->OrderID;
            }
        }
        
        // If still not found, try as OrderNumber (for any string format)
        if (!$order_exists) {
            $order_exists = $this->db->where('OrderNumber', $order_id_clean)->get('`order`')->row();
            if ($order_exists) {
                $order_id = $order_exists->OrderID; // Use numeric ID for rest of query
            }
        }
        
        if (!$order_exists) {
            log_message('error', 'Order_model::get_order_details_for_popup - Order not found in database. Input: ' . $order_id_clean);
            return null;
        }

        // Get order with all related data
        // Use COALESCE to handle NULL values from LEFT JOINs
        $this->db->select('
            o.*,
            COALESCE(oi.Product_ID, NULL) as Product_ID,
            COALESCE(oi.Dimensions, NULL) as Dimensions,
            COALESCE(oi.GlassShape, NULL) as GlassShape,
            COALESCE(oi.GlassType, NULL) as GlassType,
            COALESCE(oi.GlassThickness, NULL) as GlassThickness,
            COALESCE(oi.EdgeWork, NULL) as EdgeWork,
            COALESCE(oi.FrameType, NULL) as FrameType,
            COALESCE(oi.Engraving, NULL) as Engraving,
            COALESCE(oi.DesignRef, NULL) as DesignRef,
            COALESCE(oi.Quantity, 0) as Quantity,
            COALESCE(oi.UnitPrice, 0) as UnitPrice,
            COALESCE(oi.EstimatePrice, 0) as EstimatePrice,
            COALESCE(p.ProductName, NULL) as ProductName,
            COALESCE(p.ImageUrl, NULL) as ProductImage,
            COALESCE(p.Category, NULL) as ProductCategory,
            COALESCE(u.First_Name, NULL) as First_Name,
            COALESCE(u.Middle_Name, NULL) as Middle_Name,
            COALESCE(u.Last_Name, NULL) as Last_Name,
            COALESCE(u.Email, NULL) as Email,
            COALESCE(u.PhoneNum, NULL) as PhoneNum,
            COALESCE(c.Customer_ID, NULL) as Customer_ID
        ');
        $this->db->from('`order` o');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
        $this->db->where('o.OrderID', $order_id);
        // Group by OrderID only - MySQL will pick the first matching row for other fields
        // This works because we're using LEFT JOINs and only need one result
        $this->db->group_by('o.OrderID');
        $this->db->limit(1);

        $result = $this->db->get()->row();
        
        // If query failed or returned no results, try a simpler query without GROUP BY
        if (!$result) {
            log_message('error', 'Order_model::get_order_details_for_popup - Query returned no results. OrderID: ' . $order_id . '. Trying simpler query...');
            
            // Try simpler query - just get the order and first order_item separately
            $simple_order = $this->db->where('OrderID', $order_id)->get('`order`')->row();
            if ($simple_order) {
                // Get first order item if exists
                $first_item = $this->db->where('OrderID', $order_id)->limit(1)->get('order_items')->row();
                if ($first_item) {
                    // Get product info
                    $product = $this->db->where('Product_ID', $first_item->Product_ID)->get('product')->row();
                    if ($product) {
                        $simple_order->ProductName = $product->ProductName;
                        $simple_order->ProductImage = $product->ImageUrl;
                        $simple_order->ProductCategory = $product->Category;
                    }
                    // Copy order item fields
                    $simple_order->Dimensions = $first_item->Dimensions;
                    $simple_order->GlassShape = $first_item->GlassShape;
                    $simple_order->GlassType = $first_item->GlassType;
                    $simple_order->GlassThickness = $first_item->GlassThickness;
                    $simple_order->EdgeWork = $first_item->EdgeWork;
                    $simple_order->FrameType = $first_item->FrameType;
                    $simple_order->Engraving = $first_item->Engraving;
                    $simple_order->DesignRef = $first_item->DesignRef;
                }
                
                // Get customer info
                $customer = $this->db->where('Customer_ID', $simple_order->Customer_ID)->get('customer')->row();
                if ($customer) {
                    $user = $this->db->where('UserID', $customer->UserID)->get('user')->row();
                    if ($user) {
                        $simple_order->First_Name = $user->First_Name;
                        $simple_order->Middle_Name = $user->Middle_Name;
                        $simple_order->Last_Name = $user->Last_Name;
                        $simple_order->Email = $user->Email;
                        $simple_order->PhoneNum = $user->PhoneNum;
                    }
                }
                
                return $simple_order;
            }
            
            log_message('error', 'Order_model::get_order_details_for_popup - Order not found even with simple query. OrderID: ' . $order_id);
        }
        
        return $result;
    }

    /**
     * Get order details for admin approval popup
     * Returns order details with customer and sales rep information for admin review
     * 
     * @param int|string $order_id Order ID (numeric or GI format)
     * @return object|null Order object with all details including customer and sales rep info
     */
    public function get_approval_order_details($order_id)
    {
        // Handle both numeric and GI format order IDs
        $order_id_numeric = null;
        $order_number = null;
        
        // Clean up order ID (ensure URL decoding and remove # if present)
        // Handle both # and %23 (URL-encoded #)
        $order_id = urldecode((string)$order_id);
        $order_id = str_replace('%23', '#', $order_id); // Handle case where %23 wasn't decoded
        $order_id_clean = str_replace('#', '', $order_id);
        
        // Check if it's in GI format (e.g., GI001)
        if (preg_match('/^GI\d+$/i', $order_id_clean)) {
            $order_number = $order_id_clean;
            $order_id_numeric = (int)str_replace('GI', '', $order_id_clean);
        } elseif (is_numeric($order_id_clean)) {
            $order_id_numeric = (int)$order_id_clean;
        } else {
            // Try to find by OrderNumber first
            $order_number = $order_id_clean;
        }
        
        // First, try to find the order
        $order_exists = null;
        if ($order_id_numeric) {
            try {
                $order_exists = $this->db->where('OrderID', $order_id_numeric)->get('`order`')->row();
                if ($order_exists) {
                    $order_id_numeric = $order_exists->OrderID;
                }
            } catch (Exception $e) {
                log_message('error', 'Order_model::get_approval_order_details - Error querying by numeric ID: ' . $e->getMessage());
                $order_exists = null;
            }
        }
        
        // If not found by numeric ID, try by OrderNumber
        if (!$order_exists && $order_number) {
            try {
                $order_exists = $this->db->where('OrderNumber', $order_number)->get('`order`')->row();
                if ($order_exists) {
                    $order_id_numeric = $order_exists->OrderID;
                }
            } catch (Exception $e) {
                log_message('error', 'Order_model::get_approval_order_details - Error querying by OrderNumber: ' . $e->getMessage());
                $order_exists = null;
            }
        }
        
        if (!$order_exists) {
            log_message('error', 'Order_model::get_approval_order_details - Order not found. Input: ' . $order_id . ', Clean: ' . $order_id_clean . ', Numeric: ' . ($order_id_numeric ?? 'null') . ', Number: ' . ($order_number ?? 'null'));
            return null;
        }
        
        // Ensure we have a valid numeric order ID
        if (!$order_id_numeric || !is_numeric($order_id_numeric)) {
            log_message('error', 'Order_model::get_approval_order_details - Invalid order_id_numeric: ' . var_export($order_id_numeric, true));
            return null;
        }

        // Now build the full query with all joins
        try {
            $this->db->select('
                o.*,
                COALESCE(oi.Product_ID, NULL) as Product_ID,
                COALESCE(oi.Dimensions, NULL) as Dimensions,
                COALESCE(oi.GlassShape, NULL) as GlassShape,
                COALESCE(oi.GlassType, NULL) as GlassType,
                COALESCE(oi.GlassThickness, NULL) as GlassThickness,
                COALESCE(oi.EdgeWork, NULL) as EdgeWork,
                COALESCE(oi.FrameType, NULL) as FrameType,
                COALESCE(oi.Engraving, NULL) as Engraving,
                COALESCE(oi.DesignRef, NULL) as DesignRef,
                COALESCE(p.ProductName, NULL) as ProductName,
                COALESCE(p.ImageUrl, NULL) as ProductImage,
                COALESCE(u.First_Name, NULL) as Customer_First_Name,
                COALESCE(u.Last_Name, NULL) as Customer_Last_Name,
                COALESCE(u.Middle_Name, NULL) as Customer_Middle_Name,
                COALESCE(u.Email, NULL) as Customer_Email,
                COALESCE(u.PhoneNum, NULL) as Customer_Phone,
                COALESCE(sr.First_Name, NULL) as SalesRep_First_Name,
                COALESCE(sr.Last_Name, NULL) as SalesRep_Last_Name,
                COALESCE(sr.Email, NULL) as SalesRep_Email,
                COALESCE(sr.PhoneNum, NULL) as SalesRep_Phone,
                COALESCE(c.Customer_ID, NULL) as Customer_ID,
                COALESCE(aao.Created_Date, NULL) as Requested_Date
            ');
            $this->db->from('`order` o');
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
            $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
            $this->db->join('user u', 'u.UserID = c.UserID', 'left');
            $this->db->join('user sr', 'sr.UserID = o.SalesRep_ID', 'left');
            
            // Only join awaiting_admin_orders if table exists
            if ($this->db->table_exists('awaiting_admin_orders')) {
                $this->db->join('awaiting_admin_orders aao', 'aao.OrderID = o.OrderID', 'left'); // Join on numeric OrderID
            }
            
            $this->db->where('o.OrderID', $order_id_numeric);
            // Don't restrict by status - allow any status for admin review
            // If multiple order_items exist, LIMIT 1 will get the first one
            // This avoids GROUP BY issues with strict SQL mode
            $this->db->limit(1);

            $query = $this->db->get();
            
            // Check for database errors
            $db_error = $this->db->error();
            if ($db_error['code'] != 0) {
                log_message('error', 'Order_model::get_approval_order_details - Database error: ' . $db_error['message']);
                log_message('error', 'Order_model::get_approval_order_details - Error code: ' . $db_error['code']);
                log_message('error', 'Order_model::get_approval_order_details - SQL: ' . $this->db->last_query());
                log_message('error', 'Order_model::get_approval_order_details - OrderID: ' . $order_id_numeric);
                // Try fallback approach
                return $this->get_approval_order_details_fallback($order_id_numeric, $order_exists);
            }
            
            $result = $query->row();
            
            // Check if result is valid (has at least OrderID)
            if (!$result || !isset($result->OrderID)) {
                log_message('error', 'Order_model::get_approval_order_details - Query returned no results. OrderID: ' . $order_id_numeric . '. Trying fallback...');
                // Try fallback approach
                return $this->get_approval_order_details_fallback($order_id_numeric, $order_exists);
            }
            
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Order_model::get_approval_order_details - Exception: ' . $e->getMessage());
            log_message('error', 'Order_model::get_approval_order_details - Stack trace: ' . $e->getTraceAsString());
            // Try fallback approach
            return $this->get_approval_order_details_fallback($order_id_numeric, $order_exists);
        } catch (Error $e) {
            log_message('error', 'Order_model::get_approval_order_details - Fatal Error: ' . $e->getMessage());
            log_message('error', 'Order_model::get_approval_order_details - Stack trace: ' . $e->getTraceAsString());
            // Try fallback approach
            return $this->get_approval_order_details_fallback($order_id_numeric, $order_exists);
        }
    }
    
    /**
     * Fallback method to get approval order details by fetching data separately
     */
    private function get_approval_order_details_fallback($order_id_numeric, $order_exists)
    {
        if (!$order_exists) {
            return null;
        }
        
        $order = $order_exists;
        
        // Initialize default values for order items
        $order->ProductName = null;
        $order->ProductImage = null;
        $order->Dimensions = null;
        $order->GlassShape = null;
        $order->GlassType = null;
        $order->GlassThickness = null;
        $order->EdgeWork = null;
        $order->FrameType = null;
        $order->Engraving = null;
        $order->DesignRef = null;
        
        // Get first order item if exists
        $first_item = $this->db->where('OrderID', $order_id_numeric)->limit(1)->get('order_items')->row();
        if ($first_item) {
            // Get product info
            $product = $this->db->where('Product_ID', $first_item->Product_ID)->get('product')->row();
            if ($product) {
                $order->ProductName = $product->ProductName;
                $order->ProductImage = $product->ImageUrl;
            }
            // Copy order item fields
            $order->Dimensions = $first_item->Dimensions;
            $order->GlassShape = $first_item->GlassShape;
            $order->GlassType = $first_item->GlassType;
            $order->GlassThickness = $first_item->GlassThickness;
            $order->EdgeWork = $first_item->EdgeWork;
            $order->FrameType = $first_item->FrameType;
            $order->Engraving = $first_item->Engraving;
            $order->DesignRef = $first_item->DesignRef;
        }
        
        // Initialize customer fields
        $order->Customer_First_Name = null;
        $order->Customer_Middle_Name = null;
        $order->Customer_Last_Name = null;
        $order->Customer_Email = null;
        $order->Customer_Phone = null;
        
        // Get customer info
        if ($order->Customer_ID) {
            $customer = $this->db->where('Customer_ID', $order->Customer_ID)->get('customer')->row();
            if ($customer) {
                $user = $this->db->where('UserID', $customer->UserID)->get('user')->row();
                if ($user) {
                    $order->Customer_First_Name = $user->First_Name;
                    $order->Customer_Middle_Name = $user->Middle_Name;
                    $order->Customer_Last_Name = $user->Last_Name;
                    $order->Customer_Email = $user->Email;
                    $order->Customer_Phone = $user->PhoneNum;
                }
            }
        }
        
        // Initialize sales rep fields
        $order->SalesRep_First_Name = null;
        $order->SalesRep_Last_Name = null;
        $order->SalesRep_Email = null;
        $order->SalesRep_Phone = null;
        
        // Get sales rep info
        if ($order->SalesRep_ID) {
            $sales_rep = $this->db->where('UserID', $order->SalesRep_ID)->get('user')->row();
            if ($sales_rep) {
                $order->SalesRep_First_Name = $sales_rep->First_Name;
                $order->SalesRep_Last_Name = $sales_rep->Last_Name;
                $order->SalesRep_Email = $sales_rep->Email;
                $order->SalesRep_Phone = $sales_rep->PhoneNum;
            }
        }
        
        // Get created date from awaiting_admin_orders if table exists (used as requested date)
        $order->Requested_Date = null;
        if ($this->db->table_exists('awaiting_admin_orders')) {
            $aao = $this->db->where('OrderID', $order->OrderID)->get('awaiting_admin_orders')->row();
            if ($aao && isset($aao->Created_Date)) {
                $order->Requested_Date = $aao->Created_Date; // Use Created_Date as requested date
            }
        }
        
        return $order;
    }
}

