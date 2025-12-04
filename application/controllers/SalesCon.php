<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SalesCon extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->database();
        $this->load->model('Issue_model');
        $this->load->model('User_model');
        $this->load->model('Inventory_model');
        
        // Don't check auth for update_account, get_payment_details, and mark_payment_paid (they handle their own auth check)
        $method = $this->router->method;
        if ($method !== 'update_account' && $method !== 'get_payment_details' && $method !== 'mark_payment_paid') {
            $this->check_auth();
        }
    }

    // Check if user is authenticated and is a Sales Representative
    private function check_auth()
    {
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Sales Representative') {
            $this->session->set_flashdata('error', 'You must be logged in as a Sales Representative to access this page.');
            redirect(base_url('sales-login'));
        }
    }
    
    // Get current Sales Rep's UserID
    private function get_current_sales_rep_id()
    {
        return $this->session->userdata('user_id');
    }

    // Dashboard - Show statistics for this Sales Rep only
    public function sales_dashboard()
    {
        $sales_rep_id = $this->get_current_sales_rep_id();
        $sales_rep = $this->User_model->get_by_id($sales_rep_id);
        
        // Get total orders assigned today (from all order status tables)
        $today = date('Y-m-d');
        $total_orders_today = 0;
        
        // Count from pending_review_orders
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('DATE(OrderDate)', $today);
        $total_orders_today += $this->db->count_all_results('pending_review_orders');
        
        // Count from awaiting_admin_orders
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('DATE(OrderDate)', $today);
        $total_orders_today += $this->db->count_all_results('awaiting_admin_orders');
        
        // Count from ready_to_approve_orders
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('DATE(OrderDate)', $today);
        $total_orders_today += $this->db->count_all_results('ready_to_approve_orders');
        
        // Count from approved_orders
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('DATE(OrderDate)', $today);
        $total_orders_today += $this->db->count_all_results('approved_orders');
        
        // Get total orders needing approval (from pending_review_orders)
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $needs_approval_count = $this->db->count_all_results('pending_review_orders');
        
        // Get total payments with "Under Review" status
        // "Under Review" typically means payments with Status = 'Pending' that need verification
        // First, get all approved orders for this sales rep
        $this->db->select('OrderID');
        $this->db->from('approved_orders');
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $approved_order_ids = $this->db->get()->result();
        
        $under_review_count = 0;
        if (!empty($approved_order_ids)) {
            // Extract numeric OrderIDs
            $numeric_order_ids = [];
            foreach ($approved_order_ids as $order) {
                $order_id_clean = str_replace(['#GI', '#', 'GI'], '', $order->OrderID);
                $order_id_clean = ltrim($order_id_clean, '0');
                if (empty($order_id_clean)) {
                    $order_id_clean = '1';
                }
                $numeric_order_ids[] = (int)$order_id_clean;
            }
            
            if (!empty($numeric_order_ids)) {
                // Count payments with "Under Review" or "Pending" status (Under Review = Pending payments awaiting verification)
                $this->db->where_in('OrderID', $numeric_order_ids);
                // Check for "Under Review" first, then fallback to "Pending"
                $this->db->group_start();
                $this->db->where('Status', 'Under Review');
                $this->db->or_where('Status', 'Pending');
                $this->db->group_end();
                $under_review_count = $this->db->count_all_results('payment');
            }
        }
        
        // Get high priority issues count and category
        $this->db->where('Priority', 'High');
        $high_priority_issues = $this->db->get('issuereport')->result();
        $high_priority_count = count($high_priority_issues);
        
        // Get the most common category for high priority issues
        $issue_category = 'No Issues';
        if ($high_priority_count > 0) {
            $category_counts = [];
            foreach ($high_priority_issues as $issue) {
                $cat = $issue->Category ?? 'Unknown';
                $category_counts[$cat] = ($category_counts[$cat] ?? 0) + 1;
            }
            // Get the category with the highest count
            arsort($category_counts);
            $issue_category = key($category_counts);
        }
        
        // Get recent activities from system_activity_log
        $this->db->select('*');
        $this->db->from('system_activity_log');
        $this->db->order_by('Timestamp', 'DESC');
        $this->db->limit(10); // Get last 10 activities
        $recent_activities = $this->db->get()->result();
        
        // If no activities in log, generate from existing data
        if (empty($recent_activities)) {
            $recent_activities = $this->generate_recent_activities($sales_rep_id);
        }
        
        // Get statistics for this Sales Rep only
        $data['sales_rep'] = $sales_rep;
        $data['total_orders_today'] = $total_orders_today;
        $data['needs_approval_count'] = $needs_approval_count;
        $data['under_review_count'] = $under_review_count;
        $data['high_priority_count'] = $high_priority_count;
        $data['issue_category'] = $issue_category;
        $data['recent_activities'] = $recent_activities;
        $data['total_revenue'] = $this->get_sales_rep_revenue($sales_rep_id);
        $data['sales_rep_id'] = $sales_rep_id;
        
        $data['title'] = "Glassify - Dashboard";
        $data['active'] = 'dashboard';
        $data['content_view'] = 'sales_page/sales_dashboard';
        $data['page_css'] = 'sales_css/sales_dashboard.css';
        $this->load->view('sales_page/layout', $data);
    }
    
    /**
     * Log system activity
     */
    private function log_activity($action, $description, $role, $user_id = null, $user_name = null, $related_id = null, $related_type = null)
    {
        $data = [
            'Action' => $action,
            'Description' => $description,
            'Role' => $role,
            'UserID' => $user_id,
            'UserName' => $user_name,
            'RelatedID' => $related_id,
            'RelatedType' => $related_type,
            'Timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('system_activity_log', $data);
        
        // Also create notification in sales_notif table
        $icon = $this->determine_notification_icon($action, $description);
        $notification_description = $action . ': ' . $description;
        $this->add_sales_notification($icon, $role, $notification_description, 'Unread', $related_id, $related_type);
    }
    
    /**
     * Determine notification icon based on action and description
     * 
     * @param string $action Action type
     * @param string $description Description text
     * @return string Font Awesome icon class
     */
    private function determine_notification_icon($action, $description)
    {
        $action_lower = strtolower($action ?? '');
        $desc_lower = strtolower($description ?? '');
        
        // Inventory/Stock alerts
        if (strpos($desc_lower, 'inventory') !== false || strpos($desc_lower, 'stock') !== false || $action_lower === 'warning' || strpos($action_lower, 'low stock') !== false) {
            return 'fa-box-open';
        }
        // Employee/User related (requests, logout, etc.)
        elseif (strpos($desc_lower, 'employee') !== false || strpos($desc_lower, 'request') !== false || strpos($desc_lower, 'logout') !== false || strpos($desc_lower, 'logged') !== false) {
            return 'fa-user-tie';
        }
        // Order related
        elseif (strpos($desc_lower, 'order') !== false || strpos($action_lower, 'order') !== false) {
            return 'fa-shopping-cart';
        }
        // Payment related
        elseif (strpos($desc_lower, 'payment') !== false || strpos($action_lower, 'payment') !== false) {
            return 'fa-money-bill-wave';
        }
        // Issue related
        elseif (strpos($desc_lower, 'issue') !== false || strpos($action_lower, 'issue') !== false) {
            return 'fa-exclamation-circle';
        }
        // Default
        else {
            return 'fa-info-circle';
        }
    }
    
    /**
     * Generate recent activities from existing data if activity log is empty
     */
    private function generate_recent_activities($sales_rep_id)
    {
        $activities = [];
        
        // Get recent orders
        $this->db->select('OrderID, OrderDate, Customer_ID');
        $this->db->from('pending_review_orders');
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->order_by('OrderDate', 'DESC');
        $this->db->limit(3);
        $recent_orders = $this->db->get()->result();
        
        foreach ($recent_orders as $order) {
            // Get customer name
            $this->db->select('First_Name, Last_Name');
            $this->db->where('UserID', $order->Customer_ID);
            $customer = $this->db->get('user')->row();
            $customer_name = ($customer ? trim($customer->First_Name . ' ' . $customer->Last_Name) : 'Customer') ?: 'Customer';
            
            $order_id_formatted = '#' . $order->OrderID;
            $activities[] = (object)[
                'Action' => 'Info',
                'Description' => "New order created ({$order_id_formatted})",
                'Role' => 'Client',
                'UserName' => $customer_name,
                'Timestamp' => $order->OrderDate
            ];
        }
        
        // Get recent inventory warnings (low stock items)
        $this->db->select('Name, InStock, DateAdded');
        $this->db->from('inventory_items');
        $this->db->where('InStock >', 0);
        $this->db->where('InStock <=', 10);
        $this->db->order_by('DateAdded', 'DESC');
        $this->db->limit(2);
        $low_stock_items = $this->db->get()->result();
        
        foreach ($low_stock_items as $item) {
            $activities[] = (object)[
                'Action' => 'Warning',
                'Description' => "Stock running low: {$item->Name}",
                'Role' => 'System',
                'UserName' => 'System',
                'Timestamp' => $item->DateAdded
            ];
        }
        
        // Get recent high priority issues
        $this->db->select('Category, Report_Date, First_Name, Last_Name');
        $this->db->from('issuereport');
        $this->db->where('Priority', 'High');
        $this->db->order_by('Report_Date', 'DESC');
        $this->db->limit(2);
        $recent_issues = $this->db->get()->result();
        
        foreach ($recent_issues as $issue) {
            $customer_name = trim($issue->First_Name . ' ' . $issue->Last_Name);
            $activities[] = (object)[
                'Action' => 'Error',
                'Description' => "High priority issue: {$issue->Category}",
                'Role' => 'Client',
                'UserName' => $customer_name ?: 'Customer',
                'Timestamp' => $issue->Report_Date
            ];
        }
        
        // Sort by timestamp descending
        usort($activities, function($a, $b) {
            return strtotime($b->Timestamp) - strtotime($a->Timestamp);
        });
        
        return array_slice($activities, 0, 10); // Return top 10
    }

    // Payments - Show approved orders ready for payment
    public function sales_payments()
    {
        $sales_rep_id = $this->get_current_sales_rep_id();
        
        // Get approved orders for this sales rep with payment data
        // Note: payment table uses numeric OrderID, so we need to extract numeric part from approved_orders.OrderID
        $this->db->select('
            approved_orders.*,
            user.First_Name,
            user.Last_Name,
            user.Email,
            product.ImageUrl as ProductImage,
            payment.Payment_ID,
            payment.Amount as PaymentAmount,
            payment.Payment_Date,
            payment.Transaction_ID,
            payment.Status as PaymentStatus,
            payment.CustomerName as PaymentCustomerName,
            payment.ProductName as PaymentProductName,
            payment.PaymentMethod as PaymentMethod
        ');
        $this->db->from('approved_orders');
        $this->db->join('user', 'user.UserID = approved_orders.Customer_ID', 'left');
        $this->db->join('product', 'product.ProductName = approved_orders.ProductName', 'left');
        // Join payment: extract numeric part from OrderID (GI001 -> 1)
        $this->db->join('payment', "payment.OrderID = CAST(SUBSTRING(approved_orders.OrderID, 3) AS UNSIGNED)", 'left', false);
        $this->db->where('approved_orders.SalesRep_ID', $sales_rep_id);
        $this->db->order_by('approved_orders.Approved_Date', 'DESC');
        $orders = $this->db->get()->result();
        
        // Calculate weekly sales (last 7 days)
        $week_start = date('Y-m-d', strtotime('-7 days'));
        $this->db->select_sum('TotalQuotation');
        $this->db->from('approved_orders');
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('Approved_Date >=', $week_start);
        $weekly_sales_result = $this->db->get()->row();
        $weekly_sales = $weekly_sales_result->TotalQuotation ?? 0;
        
        // Count pending and overdue payments
        $pending_count = 0;
        $overdue_count = 0;
        foreach ($orders as $order) {
            if ($order->PaymentStatus === 'Pending') {
                $pending_count++;
            }
            // Check if overdue (more than 7 days since approval and still pending)
            if ($order->PaymentStatus === 'Pending' && $order->Approved_Date) {
                $approved_date = strtotime($order->Approved_Date);
                $days_since = (time() - $approved_date) / (60 * 60 * 24);
                if ($days_since > 7) {
                    $overdue_count++;
                }
            }
        }
        
        $data['orders'] = $orders;
        $data['weekly_sales'] = $weekly_sales;
        $data['pending_count'] = $pending_count;
        $data['overdue_count'] = $overdue_count;
        $data['title'] = "Glassify - Payments";
        $data['active'] = 'payments';
        $data['content_view'] = 'sales_page/sales_payments';
        $data['page_css'] = 'sales_css/sales_payment.css';
        $this->load->view('sales_page/layout', $data);
    }

    // End Users
    public function sales_endUser()
    {
        $sales_rep_id = $this->get_current_sales_rep_id();
        
        // Pagination settings
        $per_page = 10;
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $offset = ($page - 1) * $per_page;
        
        // Check if enduser table exists
        $enduser_exists = $this->db->query("SHOW TABLES LIKE 'enduser'")->num_rows() > 0;
        
        if ($enduser_exists) {
            // Get total count from enduser table
            $total_customers = $this->db->count_all_results('enduser');
            
            // Get paginated customers from enduser table
            $this->db->select('
                enduser.EndUser_ID,
                enduser.UserID,
                enduser.First_Name,
                enduser.Last_Name,
                enduser.Middle_Name,
                enduser.Email,
                enduser.PhoneNum,
                enduser.Status,
                enduser.Date_Created,
                enduser.Date_Updated,
                enduser.Last_Active
            ');
            $this->db->from('enduser');
            $this->db->order_by('enduser.Date_Created', 'DESC');
            $this->db->limit($per_page, $offset);
            $customers = $this->db->get()->result();
        } else {
            // Fallback to user table
            $this->db->where('user.Role', 'Customer');
            $total_customers = $this->db->count_all_results('user');
            
            $this->db->select('
                user.UserID as EndUser_ID,
                user.UserID,
                user.First_Name,
                user.Last_Name,
                user.Middle_Name,
                user.Email,
                user.PhoneNum,
                user.Status,
                user.Date_Created,
                user.Date_Updated,
                user.Date_Updated as Last_Active
            ');
            $this->db->from('user');
            $this->db->where('user.Role', 'Customer');
            $this->db->order_by('user.Date_Created', 'DESC');
            $this->db->limit($per_page, $offset);
            $customers = $this->db->get()->result();
        }
        
        $total_pages = ceil($total_customers / $per_page);
        
        $data['customers'] = $customers;
        $data['total_customers'] = $total_customers;
        $data['current_page'] = $page;
        $data['total_pages'] = $total_pages;
        $data['per_page'] = $per_page;
        $data['start'] = $offset + 1;
        $data['end'] = min($offset + $per_page, $total_customers);
        $data['title'] = "Glassify - End Users";
        $data['active'] = 'endUser';
        $data['content_view'] = 'sales_page/sales_endUser';
        $data['page_css'] = 'sales_css/sales_endUser.css';
        $this->load->view('sales_page/layout', $data);
    }

    // Orders - Fetch from status-specific tables
    public function sales_orders()
    {
        $sales_rep_id = $this->get_current_sales_rep_id();
        
        // Get orders from each status table
        // Pending Review orders
        $this->db->select('
            OrderID,
            ProductName,
            Address,
            OrderDate,
            Shape,
            Dimension,
            Type,
            Thickness,
            EdgeWork,
            FrameType,
            Engraving,
            FileAttached,
            TotalQuotation,
            Customer_ID,
            SalesRep_ID
        ');
        $this->db->from('pending_review_orders');
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->order_by('OrderDate', 'DESC');
        $pending_orders = $this->db->get()->result();
        foreach ($pending_orders as $order) {
            $order->Status = 'Pending Review';
        }
        
        // Awaiting Admin orders
        $this->db->select('
            OrderID,
            ProductName,
            Address,
            OrderDate,
            Shape,
            Dimension,
            Type,
            Thickness,
            EdgeWork,
            FrameType,
            Engraving,
            FileAttached,
            TotalQuotation,
            Customer_ID,
            SalesRep_ID
        ');
        $this->db->from('awaiting_admin_orders');
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->order_by('OrderDate', 'DESC');
        $awaiting_orders = $this->db->get()->result();
        foreach ($awaiting_orders as $order) {
            $order->Status = 'Awaiting Admin';
        }
        
        // Ready to Approve orders
        $this->db->select('
            OrderID,
            ProductName,
            Address,
            OrderDate,
            Shape,
            Dimension,
            Type,
            Thickness,
            EdgeWork,
            FrameType,
            Engraving,
            FileAttached,
            TotalQuotation,
            Customer_ID,
            SalesRep_ID,
            AdminStatus
        ');
        $this->db->from('ready_to_approve_orders');
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->order_by('OrderDate', 'DESC');
        $ready_orders = $this->db->get()->result();
        foreach ($ready_orders as $order) {
            $order->Status = 'Ready to Approve';
        }
        
        // Combine all orders
        $orders = array_merge($pending_orders, $awaiting_orders, $ready_orders);
        
        // Count orders by status
        $pending_count = count(array_filter($orders, function($o) { return $o->Status === 'Pending Review'; }));
        $awaiting_count = count(array_filter($orders, function($o) { return $o->Status === 'Awaiting Admin'; }));
        $ready_count = count(array_filter($orders, function($o) { return $o->Status === 'Ready to Approve'; }));
        
        $data['orders'] = $orders;
        $data['total_orders'] = count($orders);
        $data['pending_count'] = $pending_count;
        $data['awaiting_count'] = $awaiting_count;
        $data['ready_count'] = $ready_count;
        $data['sales_rep_id'] = $sales_rep_id;
        
        $data['title'] = "Glassify - Orders";
        $data['active'] = 'orders';
        $data['content_view'] = 'sales_page/sales_orders';
        $data['page_css'] = 'sales_css/sales_orders.css';
        $this->load->view('sales_page/layout', $data);
    }
    
    // AJAX endpoint to get order details for popup
    // This method ALWAYS fetches fresh data from the database - no caching
    public function get_order_details()
    {
        // Disable query caching to ensure fresh data
        $this->db->cache_off();
        
        // Set HTTP headers to prevent caching
        $this->output->set_header('Cache-Control: no-cache, no-store, must-revalidate');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');
        
        $sales_rep_id = $this->get_current_sales_rep_id();
        $order_id = $this->input->post('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Clean and format OrderID
        // OrderID from database is in format: GI006, GI001, etc.
        // Button passes: GI006 (from data-order-id attribute)
        $order_id_clean = trim($order_id);
        
        // Remove # prefix if present
        $order_id_clean = str_replace('#', '', $order_id_clean);
        
        // If it doesn't start with GI, add it
        if (strpos($order_id_clean, 'GI') !== 0) {
            // Extract numeric part if it's just a number
            $numeric_part = preg_replace('/[^0-9]/', '', $order_id_clean);
            if ($numeric_part) {
                $order_id_clean = 'GI' . str_pad($numeric_part, 3, '0', STR_PAD_LEFT);
            } else {
                $order_id_clean = 'GI' . str_pad($order_id_clean, 3, '0', STR_PAD_LEFT);
            }
        } else {
            // Ensure it's in the correct format (GI + 3 digits)
            $numeric_part = preg_replace('/[^0-9]/', '', $order_id_clean);
            if ($numeric_part) {
                $order_id_clean = 'GI' . str_pad($numeric_part, 3, '0', STR_PAD_LEFT);
            }
        }
        
        // Try to find order in status-specific tables
        $order = null;
        
        // Check pending_review_orders
        $this->db->select('*');
        $this->db->from('pending_review_orders');
        $this->db->where('OrderID', $order_id_clean);
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $order = $this->db->get()->row();
        
        // Check awaiting_admin_orders
        if (!$order) {
            $this->db->select('*');
            $this->db->from('awaiting_admin_orders');
            $this->db->where('OrderID', $order_id_clean);
            $this->db->where('SalesRep_ID', $sales_rep_id);
            $order = $this->db->get()->row();
        }
        
        // Check ready_to_approve_orders
        if (!$order) {
            $this->db->select('*');
            $this->db->from('ready_to_approve_orders');
            $this->db->where('OrderID', $order_id_clean);
            $this->db->where('SalesRep_ID', $sales_rep_id);
            $order = $this->db->get()->row();
        }
        
        // Get AdminNotes if order is from ready_to_approve_orders
        $admin_notes = '';
        if ($order && isset($order->AdminNotes)) {
            $admin_notes = $order->AdminNotes;
        }
        
        // Fallback to order_page if not found in status tables
        if (!$order) {
            $this->db->select('*');
            $this->db->from('order_page');
            $this->db->where('OrderID', $order_id_clean);
            $this->db->where('SalesRep_ID', $sales_rep_id);
            $order = $this->db->get()->row();
        }
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Format date
        $date_formatted = 'N/A';
        if ($order->OrderDate) {
            $date_formatted = date('d/m/Y', strtotime($order->OrderDate));
        }
        
        // Build file URL if file exists - check multiple possible locations
        $file_url = null;
        if ($order->FileAttached && $order->FileAttached !== 'N/A') {
            // Try different possible file paths
            $possible_paths = [
                'uploads/' . $order->FileAttached,
                'uploads/payments/' . $order->FileAttached,
                'assets/files/' . $order->FileAttached,
                $order->FileAttached // If it's already a full path
            ];
            
            // Check if file exists in any of these locations
            foreach ($possible_paths as $path) {
                $full_path = FCPATH . $path;
                if (file_exists($full_path)) {
                    $file_url = base_url($path);
                    break;
                }
            }
            
            // If no file found, still provide a URL for clicking (might be external or not yet uploaded)
            if (!$file_url) {
                $file_url = base_url('uploads/' . $order->FileAttached);
            }
        }
        
        // Get product category from product table
        $product_category = 'N/A';
        if (isset($order->ProductName) && !empty($order->ProductName)) {
            $this->db->select('Category');
            $this->db->where('ProductName', $order->ProductName);
            $product = $this->db->get('product')->row();
            if ($product) {
                $product_category = $product->Category;
            }
        }
        
        // Build response with ALL data from order_page table (mirrors customization table)
        // Include all fields exactly as stored in database - no modifications
        $response = [
            'success' => true,
            'order' => [
                'OrderID' => '#' . $order->OrderID,
                'ProductName' => $order->ProductName ?: 'N/A',
                'ProductCategory' => $product_category,
                'Address' => $order->Address ?: 'N/A',
                'Date' => $date_formatted,
                'Shape' => isset($order->Shape) ? ($order->Shape ?: 'N/A') : 'N/A',
                'Dimensions' => isset($order->Dimension) ? ($order->Dimension ?: 'N/A') : 'N/A',
                'Type' => isset($order->Type) ? ($order->Type ?: 'N/A') : 'N/A',
                'Thickness' => isset($order->Thickness) ? ($order->Thickness ?: 'N/A') : 'N/A',
                'EdgeWork' => isset($order->EdgeWork) ? ($order->EdgeWork ?: 'N/A') : 'N/A',
                'FrameType' => isset($order->FrameType) ? ($order->FrameType ?: 'N/A') : 'N/A',
                'Engraving' => isset($order->Engraving) ? ($order->Engraving ?: 'N/A') : 'N/A',
                'LEDBacklight' => isset($order->LEDBacklight) ? ($order->LEDBacklight ?: 'N/A') : 'N/A',
                'DoorOperation' => isset($order->DoorOperation) ? ($order->DoorOperation ?: 'N/A') : 'N/A',
                'Configuration' => isset($order->Configuration) ? ($order->Configuration ?: 'N/A') : 'N/A',
                'FileAttached' => $order->FileAttached ?: 'N/A',
                'TotalAmount' => number_format($order->TotalQuotation, 2),
                'FileUrl' => $file_url
            ]
        ];
        
        // Include AdminNotes if available (for disapproved orders from ready_to_approve_orders)
        if (isset($order->AdminNotes) && !empty($order->AdminNotes)) {
            $response['order']['AdminNotes'] = $order->AdminNotes;
        }
        
        // Include AdminStatus if available (for ready_to_approve_orders)
        if (isset($order->AdminStatus)) {
            $response['order']['AdminStatus'] = $order->AdminStatus;
        }
        
        echo json_encode($response);
    }
    
    // AJAX endpoint to filter orders by date
    public function filter_orders_by_date()
    {
        $sales_rep_id = $this->get_current_sales_rep_id();
        $date = $this->input->post('date');
        $status = $this->input->post('status'); // pending, awaiting, ready
        
        if (!$date) {
            echo json_encode(['success' => false, 'message' => 'Date is required']);
            return;
        }
        
        // Convert date to Y-m-d format
        $date_obj = DateTime::createFromFormat('Y-m-d', $date);
        if (!$date_obj) {
            $date_obj = DateTime::createFromFormat('d/m/Y', $date);
        }
        if (!$date_obj) {
            echo json_encode(['success' => false, 'message' => 'Invalid date format']);
            return;
        }
        $date_str = $date_obj->format('Y-m-d');
        
        // Build query
        $this->db->select('
            `order`.OrderID,
            `order`.OrderDate,
            `order`.TotalAmount,
            `order`.Status,
            product.ProductName,
            `order`.DeliveryAddress
        ');
        $this->db->from('order');
        $this->db->join('customization', 'customization.Customer_ID = order.Customer_ID', 'left');
        $this->db->join('product', 'product.Product_ID = customization.Product_ID', 'left');
        $this->db->where('order.SalesRep_ID', $sales_rep_id);
        $this->db->where('DATE(order.OrderDate)', $date_str);
        
        // Filter by status based on tab
        if ($status === 'pending') {
            $this->db->where('order.Status', 'Pending');
        } elseif ($status === 'awaiting') {
            $this->db->where('order.Status', 'Approved');
        } elseif ($status === 'ready') {
            $this->db->where_in('order.Status', ['Approved', 'In Fabrication', 'Ready for Installation']);
        }
        
        $this->db->group_by('order.OrderID');
        $this->db->order_by('order.OrderDate', 'DESC');
        $orders = $this->db->get()->result();
        
        $orders_data = [];
        foreach ($orders as $order) {
            $order_id_num = $order->OrderID;
            $orders_data[] = [
                'OrderID' => $order_id_num, // Store numeric ID for data-order-id attribute
                'OrderIDFormatted' => '#GI' . str_pad($order_id_num, 3, '0', STR_PAD_LEFT), // Formatted for display
                'ProductName' => $order->ProductName ?: 'N/A',
                'Address' => $order->DeliveryAddress ? (strlen($order->DeliveryAddress) > 20 ? substr($order->DeliveryAddress, 0, 20) . '...' : $order->DeliveryAddress) : 'N/A',
                'Date' => date('d/m/Y', strtotime($order->OrderDate)),
                'Price' => '₱' . number_format($order->TotalAmount, 2),
                'Status' => $order->Status
            ];
        }
        
        echo json_encode([
            'success' => true,
            'orders' => $orders_data,
            'count' => count($orders_data)
        ]);
    }
    
    // Inventory
    public function sales_inventory()
    {
        $sales_rep_id = $this->get_current_sales_rep_id();
        
        // Get all inventory items from database
        $this->db->select('InventoryItemID, ItemID, Name, Category, InStock, Unit, Status, DateAdded');
        $this->db->from('inventory_items');
        $this->db->order_by('InventoryItemID', 'ASC');
        $inventory_items = $this->db->get()->result();
        
        // Get unread inventory notifications
        $notifications = $this->Inventory_model->get_unread_notifications();
        
        // Calculate statistics
        $total_items = count($inventory_items);
        $low_stock_count = 0;
        $out_of_stock_count = 0;
        $new_items_count = 0;
        
        foreach ($inventory_items as $item) {
            if ($item->InStock == 0) {
                $out_of_stock_count++;
            } elseif ($item->InStock > 0 && $item->InStock < 10) {
                $low_stock_count++;
            }
            
            // Check if item is new (added within last 2 days)
            $date_added = strtotime($item->DateAdded);
            $two_days_ago = strtotime('-2 days');
            if ($date_added >= $two_days_ago) {
                $new_items_count++;
            }
        }
        
        // Update status in database based on stock levels (but preserve 'New' status if within 2 days)
        // Only update if status needs to change (to avoid unnecessary database writes)
        $this->db->query("UPDATE inventory_items SET Status = 'Out of Stock' WHERE InStock = 0 AND Status != 'Out of Stock' AND (Status != 'New' OR DateAdded < DATE_SUB(NOW(), INTERVAL 2 DAY))");
        $this->db->query("UPDATE inventory_items SET Status = 'Low Stock' WHERE InStock > 0 AND InStock < 10 AND Status != 'Low Stock' AND (Status != 'New' OR DateAdded < DATE_SUB(NOW(), INTERVAL 2 DAY))");
        $this->db->query("UPDATE inventory_items SET Status = 'In Stock' WHERE InStock >= 10 AND Status != 'In Stock' AND (Status != 'New' OR DateAdded < DATE_SUB(NOW(), INTERVAL 2 DAY))");
        
        // Mark items as 'New' if added within last 2 days (only if not already marked)
        $this->db->query("UPDATE inventory_items SET Status = 'New' WHERE DateAdded >= DATE_SUB(NOW(), INTERVAL 2 DAY) AND Status != 'New'");
        
        // No need to re-fetch - we already have the data with current status
        
        $data['inventory_items'] = $inventory_items;
        $data['total_items'] = $total_items;
        $data['low_stock_count'] = $low_stock_count;
        $data['out_of_stock_count'] = $out_of_stock_count;
        $data['new_items_count'] = $new_items_count;
        $data['notifications'] = $notifications;
        $data['notification_count'] = count($notifications);
        $data['title'] = "Glassify - Inventory";
        $data['active'] = 'inventory';
        $data['content_view'] = 'sales_page/sales_inventory';
        $data['page_css'] = 'sales_css/sales_inventory.css';
        $this->load->view('sales_page/layout', $data);
    }

    // Products
    public function sales_products()
    {
        $sales_rep_id = $this->get_current_sales_rep_id();
        
        // Get all products from database
        $this->db->select('Product_ID, ProductName, Category, Material, Price, ImageUrl, Status, DateAdded');
        $this->db->from('product');
        $this->db->order_by('DateAdded', 'DESC');
        $products = $this->db->get()->result();
        
        // Get unique categories for filter dropdown
        $this->db->distinct();
        $this->db->select('Category');
        $this->db->from('product');
        $this->db->order_by('Category', 'ASC');
        $categories_result = $this->db->get()->result();
        $categories = [];
        foreach ($categories_result as $cat) {
            $categories[] = $cat->Category;
        }
        
        $data['products'] = $products;
        $data['categories'] = $categories;
        $data['total_products'] = count($products);
        $data['title'] = "Glassify - Products";
        $data['active'] = 'products';
        $data['content_view'] = 'sales_page/sales_products';
        $data['page_css'] = 'sales_css/sales_products.css';
        $this->load->view('sales_page/layout', $data);
    }
    
    // Issues/Support
    public function sales_issues()
    {
        $data['title'] = "Glassify - Issues/Support";
        $data['active'] = 'issues';
        $data['content_view'] = 'sales_page/sales_issues';
        $data['page_css'] = 'sales_css/sales_issues.css';
        $this->load->view('sales_page/layout', $data);
    }

    public function sales_account()
    {
        // Get logged-in Sales Rep's UserID
        $user_id = $this->session->userdata('user_id');
        
        // Get Sales Rep's information from database
        $sales_rep = $this->User_model->get_by_id($user_id);
        
        if (!$sales_rep) {
            $this->session->set_flashdata('error', 'User information not found.');
            redirect(base_url('sales-dashboard'));
        }
        
        // Pass Sales Rep's information to view
        $data['sales_rep'] = $sales_rep;
        
        $data['title'] = "Glassify - Account Settings";
        $data['active'] = 'account';
        $data['content_view'] = 'sales_page/sales_account';
        $data['page_css'] = 'sales_css/sales_account.css';
        $this->load->view('sales_page/layout', $data);
    }

    // ===================== ISSUE/SUPPORT API ENDPOINTS =====================

    /**
     * Get all issues (AJAX endpoint)
     */
    public function get_issues_ajax()
    {
        // Don't filter by salesrep_id - show ALL issues to all sales reps
        // Sales reps can see all customer issues, including guest submissions
        $filters = [
            'status' => $this->input->get('status') ?: 'Open',
            'priority' => $this->input->get('priority'),
            'category' => $this->input->get('category'),
            'search' => $this->input->get('search')
            // Removed salesrep_id filter to show all issues
        ];
        
        $issues = $this->Issue_model->get_all_issues($filters);
        
        // Format issues for frontend
        $formatted_issues = [];
        foreach ($issues as $issue) {
            $formatted_issues[] = [
                'issue_id' => $issue->Issue_ID,
                'ticket_id' => '#TC-' . str_pad($issue->Issue_ID, 2, '0', STR_PAD_LEFT),
                'category' => $issue->Category,
                'priority' => $issue->Priority,
                'email' => $issue->Email,
                'first_name' => $issue->First_Name,
                'last_name' => $issue->Last_Name,
                'status' => $issue->Status,
                'report_date' => $issue->Report_Date
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'issues' => $formatted_issues,
            'count' => count($formatted_issues)
        ]);
    }

    /**
     * Get issue details by ID (AJAX endpoint)
     */
    public function get_issue_details_ajax($issue_id)
    {
        $issue = $this->Issue_model->get_issue_by_id($issue_id);
        
        if (!$issue) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Issue not found'
            ]);
            return;
        }
        
        // Format order ID
        $order_id_display = $issue->Order_ID > 0 ? '#G' . str_pad($issue->Order_ID, 4, '0', STR_PAD_LEFT) : 'N/A';
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'issue' => [
                'issue_id' => $issue->Issue_ID,
                'ticket_id' => '#TC-' . str_pad($issue->Issue_ID, 2, '0', STR_PAD_LEFT),
                'first_name' => $issue->First_Name,
                'last_name' => $issue->Last_Name,
                'email' => $issue->Email,
                'phone' => $issue->PhoneNum,
                'order_id' => $order_id_display,
                'order_id_raw' => $issue->Order_ID,
                'category' => $issue->Category,
                'priority' => $issue->Priority,
                'description' => $issue->Description,
                'status' => $issue->Status,
                'report_date' => $issue->Report_Date
            ]
        ]);
    }

    /**
     * Mark issue as resolved (AJAX endpoint)
     */
    public function mark_resolved_ajax()
    {
        $issue_id = $this->input->post('issue_id');
        
        if (!$issue_id) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Issue ID is required'
            ]);
            return;
        }
        
        $result = $this->Issue_model->mark_as_resolved($issue_id);
        
        if ($result) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Issue marked as resolved'
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update issue'
            ]);
        }
    }

    /**
     * Update issue priority (AJAX endpoint)
     */
    public function update_priority_ajax()
    {
        $issue_id = $this->input->post('issue_id');
        $priority = $this->input->post('priority');
        
        if (!$issue_id || !in_array($priority, ['Low', 'Medium', 'High'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request'
            ]);
            return;
        }
        
        $result = $this->Issue_model->update_priority($issue_id, $priority);
        
        if ($result) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Priority updated successfully'
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update priority'
            ]);
        }
    }

    /**
     * Get issue statistics (AJAX endpoint)
     */
    public function get_issue_stats_ajax()
    {
        $salesrep_id = $this->session->userdata('user_id');
        $stats = $this->Issue_model->get_issue_statistics($salesrep_id);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
    }

    // Notifications
    public function sales_notif()
    {
        // Get ALL notifications from sales_notif table
        $this->db->order_by('Created_Date', 'DESC');
        $notifications = $this->db->get('sales_notif')->result();
        
        // Format notifications for display
        $all_notifications = [];
        foreach ($notifications as $notif) {
            // Parse Action and Description from the stored Description field
            // Format: "Action: Description" or just "Description"
            $description = $notif->Description;
            $action = '';
            
            if (strpos($description, ': ') !== false) {
                $parts = explode(': ', $description, 2);
                $action = $parts[0];
                $description = $parts[1];
            } else {
                // If no action prefix, extract from description
                if (stripos($description, 'inventory') !== false || stripos($description, 'stock') !== false) {
                    $action = 'Inventory Alert';
                } elseif (stripos($description, 'employee') !== false && stripos($description, 'request') !== false) {
                    $action = 'Pending Request';
                } elseif (stripos($description, 'logout') !== false || stripos($description, 'logged out') !== false) {
                    $action = 'Logout Notice';
                } else {
                    $action = 'Notification';
                }
            }
            
            $all_notifications[] = (object)[
                'Action' => $action,
                'Description' => $description,
                'Icon' => $notif->Icon,
                'Role' => $notif->Role,
                'Timestamp' => $notif->Created_Date,
                'Status' => strtolower($notif->Status) // 'unread' or 'read'
            ];
        }
        
        // Count unread notifications
        $this->db->where('Status', 'Unread');
        $unread_count = $this->db->count_all_results('sales_notif');
        
        $data['notifications'] = $all_notifications;
        $data['unread_count'] = $unread_count;
        $data['title'] = "Glassify - Notifications";
        $data['active'] = 'notif';
        $data['content_view'] = 'sales_page/sales_notif';
        $data['page_css'] = 'sales_css/sales_notif.css';
        $this->load->view('sales_page/layout', $data);
    }
>>>>>>> origin/sales-branch
    
    /**
     * Add notification to sales_notif table
     * 
     * @param string $icon Font Awesome icon class (e.g., 'fa-box-open', 'fa-user-tie')
     * @param string $role Role: 'System', 'Client/Customer', 'Admin', 'Inventory Officer', 'Sales Representative'
     * @param string $description Notification message/description
     * @param string $status Status: 'Unread' or 'Read' (default: 'Unread')
     * @param int|null $related_id Related ID (OrderID, IssueID, etc.)
     * @param string|null $related_type Related type ('Order', 'Issue', 'Inventory', 'Payment', etc.)
     * @return int NotificationID of the created notification
     */
    public function add_sales_notification($icon, $role, $description, $status = 'Unread', $related_id = null, $related_type = null)
    {
        $data = [
            'Icon' => $icon,
            'Role' => $role,
            'Description' => $description,
            'Status' => $status,
            'RelatedID' => $related_id,
            'RelatedType' => $related_type,
            'Created_Date' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('sales_notif', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $notification_id NotificationID to mark as read
     */
    public function mark_notification_read($notification_id)
    {
        $this->db->where('NotificationID', $notification_id);
        $this->db->update('sales_notif', [
            'Status' => 'Read',
            'Read_Date' => date('Y-m-d H:i:s')
        ]);
    }

    // Update account information via AJAX
    public function update_account()
    {
        // Set JSON header first
        header('Content-Type: application/json');
        
        // Check if user is authenticated
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Sales Representative') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized - Please log in again']);
            return;
        }

        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'User ID not found in session']);
            return;
        }

        // Get POST data
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        
        // Also try raw input in case POST isn't working
        if (empty($field)) {
            $raw_input = file_get_contents('php://input');
            parse_str($raw_input, $parsed);
            if (!empty($parsed['field'])) {
                $field = $parsed['field'];
                $value = $parsed['value'] ?? '';
            }
        }

        // Debug logging
        log_message('debug', 'Update account request received');
        log_message('debug', 'POST data: ' . json_encode($_POST));
        log_message('debug', 'UserID=' . $user_id . ', Field=' . $field . ', Value length=' . strlen($value ?? ''));

        if (empty($field)) {
            echo json_encode(['success' => false, 'message' => 'Field name is required. Received: ' . json_encode($_POST)]);
            return;
        }
        
        if ($value === null || $value === '') {
            // Allow empty value only for Middle_Name
            if ($field !== 'Middle_Name') {
                echo json_encode(['success' => false, 'message' => 'Value is required for field: ' . $field]);
                return;
            }
        }

        // Validate field name
        $allowed_fields = ['First_Name', 'Middle_Name', 'Last_Name', 'PhoneNum', 'Password'];
        if (!in_array($field, $allowed_fields)) {
            echo json_encode(['success' => false, 'message' => 'Invalid field: ' . $field]);
            return;
        }

        // Prepare update data
        $update_data = [];

        // Handle password separately (needs hashing)
        if ($field === 'Password') {
            if (empty($value) || strlen($value) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
                return;
            }
            $update_data['Password'] = password_hash($value, PASSWORD_BCRYPT);
        } else {
            // Trim and validate other fields
            $value = trim($value);
            
            // Validate based on field type
            if ($field === 'PhoneNum') {
                // Validate phone number (10-13 digits)
                if (!preg_match('/^[0-9]{10,13}$/', $value)) {
                    echo json_encode(['success' => false, 'message' => 'Phone number must be 10-13 digits only']);
                    return;
                }
            } elseif ($field === 'First_Name' || $field === 'Last_Name') {
                // Validate name (letters, spaces, hyphens, apostrophes)
                if (!preg_match('/^[a-zA-Z\s\-\']+$/', $value)) {
                    echo json_encode(['success' => false, 'message' => 'Name can only contain letters, spaces, hyphens, and apostrophes']);
                    return;
                }
                if (strlen($value) < 2) {
                    echo json_encode(['success' => false, 'message' => 'Name must be at least 2 characters long']);
                    return;
                }
                // Capitalize first letter of each word
                $value = ucwords(strtolower($value));
            } elseif ($field === 'Middle_Name') {
                // Middle name is optional, but validate if provided
                if (!empty($value) && !preg_match('/^[a-zA-Z\s\-\'.]+$/', $value)) {
                    echo json_encode(['success' => false, 'message' => 'Middle name can only contain letters, spaces, hyphens, apostrophes, and periods']);
                    return;
                }
                // Capitalize first letter of each word if provided
                if (!empty($value)) {
                    $value = ucwords(strtolower($value));
                }
            } elseif (empty($value) && $field !== 'Middle_Name') {
                echo json_encode(['success' => false, 'message' => 'Field cannot be empty']);
                return;
            }
            
            // Additional validation: Check for duplicate phone numbers (if phone is being updated)
            if ($field === 'PhoneNum') {
                $this->db->where('PhoneNum', $value);
                $this->db->where('UserID !=', $user_id);
                $duplicate = $this->db->get('user')->row();
                if ($duplicate) {
                    echo json_encode(['success' => false, 'message' => 'This phone number is already in use by another account']);
                    return;
                }
            }
            
            $update_data[$field] = $value;
        }

        // Check if value actually changed (prevent unnecessary updates)
        $current_user = $this->User_model->get_by_id($user_id);
        if (!$current_user) {
            echo json_encode(['success' => false, 'message' => 'User account not found']);
            return;
        }

        // Check if the new value is different from current value
        $current_value = $current_user->$field ?? '';
        
        if ($field === 'Password') {
            // For password, we always update (can't compare hashes)
            // But verify the new password is different from old one by checking if it verifies
            if (!empty($current_value) && password_verify($value, $current_value)) {
                echo json_encode(['success' => false, 'message' => 'New password must be different from the current password.']);
                return;
            }
        } else {
            // For other fields, compare trimmed values
            $current_trimmed = trim($current_value);
            $new_trimmed = trim($value);
            
            if ($current_trimmed === $new_trimmed) {
                echo json_encode(['success' => false, 'message' => 'No changes detected. The value is the same as the current value.']);
                return;
            }
            
            log_message('debug', 'Value change detected: Current="' . $current_trimmed . '", New="' . $new_trimmed . '"');
        }

        // Log what we're about to update
        log_message('debug', 'Attempting to update: UserID=' . $user_id . ', Data=' . json_encode($update_data));

        // Update in database
        $result = $this->User_model->update_account($user_id, $update_data);
        
        // Get affected rows and error info from the database
        $affected_rows = $this->db->affected_rows();
        $db_error = $this->db->error();
        
        log_message('debug', 'Controller update_account: Model result=' . ($result ? 'true' : 'false') . ', Affected rows=' . $affected_rows . ', DB Error: ' . json_encode($db_error));
        
        if ($result && $affected_rows > 0) {
            // Update was successful - verify by fetching the user again
            $updated_user = $this->User_model->get_by_id($user_id);
            $verification_passed = false;
            
            if ($updated_user) {
                if ($field === 'Password') {
                    // For password, verify it was hashed (starts with $2y$)
                    $verification_passed = (strpos($updated_user->Password, '$2y$') === 0);
                    log_message('debug', 'Password verification: ' . ($verification_passed ? 'passed' : 'failed') . ' (hash starts with: ' . substr($updated_user->Password, 0, 4) . ')');
                } else {
                    // For other fields, check if value matches (trim for comparison)
                    $updated_value = trim($updated_user->$field ?? '');
                    $expected_value = trim($value);
                    $verification_passed = ($updated_value === $expected_value);
                    log_message('debug', 'Field verification: Updated="' . $updated_value . '", Expected="' . $expected_value . '", Match=' . ($verification_passed ? 'yes' : 'no'));
                }
            } else {
                log_message('error', 'Could not fetch updated user for verification');
            }
            
            if ($verification_passed) {
                // Update session if name changed
                if ($field === 'First_Name' || $field === 'Last_Name') {
                    $this->session->set_userdata('user_name', $updated_user->First_Name . ' ' . $updated_user->Last_Name);
                }

                // Log successful update
                log_message('info', 'Sales Rep account updated successfully: UserID=' . $user_id . ', Field=' . $field . ', Affected rows=' . $affected_rows);
                
                echo json_encode(['success' => true, 'message' => 'Account updated successfully in database']);
            } else {
                // Update query ran but verification failed
                log_message('error', 'Update verification failed: UserID=' . $user_id . ', Field=' . $field . ', Expected=' . $value);
                echo json_encode(['success' => false, 'message' => 'Update was saved but verification failed. Please refresh the page to see the changes.']);
            }
        } else {
            // Get database error if available
            $db_error = $this->db->error();
            $error_msg = 'Failed to update account in database';
            
            if (!empty($db_error['message'])) {
                // Check for unique constraint violations
                if (strpos($db_error['message'], 'Duplicate entry') !== false || strpos($db_error['message'], 'UNIQUE constraint') !== false) {
                    $error_msg = 'This value already exists in the database. Please use a different value.';
                } else {
                    $error_msg .= ': ' . $db_error['message'];
                }
            } elseif ($affected_rows === 0) {
                $error_msg = 'No changes were made. The value may be the same as the current value, or the update failed silently.';
            }
            
            // Log error with full details
            log_message('error', 'Sales Rep account update failed: UserID=' . $user_id . ', Field=' . $field . ', Error=' . $error_msg . ', DB Error=' . json_encode($db_error) . ', Affected rows=' . $affected_rows);
            
            echo json_encode(['success' => false, 'message' => $error_msg, 'debug' => ['affected_rows' => $affected_rows, 'db_error' => $db_error]]);
        }
    }
    
    // Get count of orders assigned to this Sales Rep
    private function get_sales_rep_orders_count($sales_rep_id, $status = null)
    {
        $this->db->where('SalesRep_ID', $sales_rep_id);
        if ($status) {
            $this->db->where('Status', $status);
        }
        return $this->db->count_all_results('order');
    }
    
    // Get total revenue for this Sales Rep
    private function get_sales_rep_revenue($sales_rep_id)
    {
        $this->db->select_sum('TotalAmount');
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('PaymentStatus', 'Paid');
        $result = $this->db->get('order')->row();
        return $result->TotalAmount ? number_format($result->TotalAmount, 2) : '0.00';
    }
    
    /**
     * Approve order (final approval by Sales Rep)
     * Moves order from ready_to_approve_orders to approved_orders
     * Notifies customer and makes order available for payment
     */
    public function approve_order()
    {
        $sales_rep_id = $this->get_current_sales_rep_id();
        $order_id = $this->input->post('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Remove # prefix if present and extract numeric part
        $order_id_clean = str_replace('#GI', '', $order_id);
        $order_id_clean = str_replace('#', '', $order_id_clean);
        $order_id_clean = str_replace('GI', '', $order_id_clean);
        $order_id_numeric_part = ltrim($order_id_clean, '0');
        if (empty($order_id_numeric_part)) {
            $order_id_numeric_part = '1';
        }
        $order_id_clean = 'GI' . str_pad($order_id_numeric_part, 3, '0', STR_PAD_LEFT);
        $order_id_numeric = (int)$order_id_numeric_part;
        
        // Start transaction
        $this->db->trans_start();
        
        // Get order from ready_to_approve_orders
        $this->db->where('OrderID', $order_id_clean);
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $order = $this->db->get('ready_to_approve_orders')->row();
        
        if (!$order) {
            $this->db->trans_rollback();
            echo json_encode(['success' => false, 'message' => 'Order not found in ready to approve']);
            return;
        }
        
        // Insert into approved_orders
        $approved_data = [
            'OrderID' => $order->OrderID,
            'ProductName' => $order->ProductName,
            'Address' => $order->Address,
            'OrderDate' => $order->OrderDate,
            'Shape' => $order->Shape,
            'Dimension' => $order->Dimension,
            'Type' => $order->Type,
            'Thickness' => $order->Thickness,
            'EdgeWork' => $order->EdgeWork,
            'FrameType' => $order->FrameType,
            'Engraving' => $order->Engraving,
            'FileAttached' => $order->FileAttached,
            'TotalQuotation' => $order->TotalQuotation,
            'Customer_ID' => $order->Customer_ID,
            'SalesRep_ID' => $order->SalesRep_ID,
            'ApprovedBy_SalesRep_ID' => $sales_rep_id,
            'Approved_Date' => date('Y-m-d H:i:s'),
            'CustomerNotified' => 0, // Will be set to 1 after notification is sent
            'PaymentStatus' => 'Pending'
        ];
        
        $this->db->insert('approved_orders', $approved_data);
        
        // Create payment record in payment table
        // Use the already extracted numeric OrderID
        
        // Check if order exists in order table, if not create it
        $this->db->where('OrderID', $order_id_numeric);
        $existing_order = $this->db->get('order')->row();
        
        if (!$existing_order) {
            // Create order record
            $order_data = [
                'OrderID' => $order_id_numeric,
                'Customer_ID' => $order->Customer_ID,
                'SalesRep_ID' => $order->SalesRep_ID,
                'OrderDate' => $order->OrderDate,
                'TotalAmount' => $order->TotalQuotation,
                'Status' => 'Approved',
                'PaymentStatus' => 'Pending',
                'DeliveryAddress' => $order->Address
            ];
            $insert_order = $this->db->insert('order', $order_data);
            if (!$insert_order) {
                $error = $this->db->error();
                log_message('error', 'Failed to create order record. OrderID: ' . $order_id_numeric . ', Error: ' . json_encode($error));
                $this->db->trans_rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to create order record: ' . $error['message']]);
                return;
            }
        }
        
        // Get customer name for payment record
        $this->db->select('First_Name, Last_Name');
        $this->db->where('UserID', $order->Customer_ID);
        $customer = $this->db->get('user')->row();
        $customer_name = '';
        if ($customer) {
            $customer_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
        }
        
        // Check if payment record already exists for this order
        $this->db->where('OrderID', $order_id_numeric);
        $existing_payment = $this->db->get('payment')->row();
        
        if (!$existing_payment) {
            // Create payment record with customer name, product name, and payment method
            $payment_data = [
                'OrderID' => $order_id_numeric,
                'CustomerName' => $customer_name,
                'ProductName' => $order->ProductName,
                'PaymentMethod' => $order->PaymentMethod ?? null,
                'Amount' => $order->TotalQuotation,
                'Payment_Date' => date('Y-m-d H:i:s'),
                'Transaction_ID' => null,
                'Status' => 'Pending'
            ];
            $insert_payment = $this->db->insert('payment', $payment_data);
            if (!$insert_payment) {
                $error = $this->db->error();
                log_message('error', 'Failed to create payment record. OrderID: ' . $order_id_numeric . ', Error: ' . json_encode($error));
                $this->db->trans_rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to create payment record: ' . $error['message']]);
                return;
            } else {
                log_message('info', 'Payment record created successfully. OrderID: ' . $order_id_numeric . ', Amount: ' . $order->TotalQuotation);
            }
        } else {
            // Update existing payment record with customer name, product, and method if missing
            $update_data = [];
            if (empty($existing_payment->CustomerName)) {
                $update_data['CustomerName'] = $customer_name;
            }
            if (empty($existing_payment->ProductName)) {
                $update_data['ProductName'] = $order->ProductName;
            }
            if (empty($existing_payment->PaymentMethod) && !empty($order->PaymentMethod)) {
                $update_data['PaymentMethod'] = $order->PaymentMethod;
            }
            if (!empty($update_data)) {
                $this->db->where('OrderID', $order_id_numeric);
                $this->db->update('payment', $update_data);
            }
            log_message('info', 'Payment record already exists for OrderID: ' . $order_id_numeric);
        }
        
        // Delete from ready_to_approve_orders
        $this->db->where('OrderID', $order_id_clean);
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->delete('ready_to_approve_orders');
        
        // Also remove from order_page if exists
        $this->db->where('OrderID', $order_id_clean);
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->delete('order_page');
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Failed to approve order']);
            return;
        }
        
        // Send notification to customer
        $this->notify_customer_approved($order->Customer_ID, $order_id_clean, $order->TotalQuotation);
        
        // Get sales rep name for logging
        $sales_rep = $this->User_model->get_by_id($sales_rep_id);
        $sales_rep_name = $sales_rep ? trim($sales_rep->First_Name . ' ' . $sales_rep->Last_Name) : 'Sales Representative';
        
        // Log activity and create notification
        $this->log_activity(
            'Order Approved',
            "Order {$order_id_clean} has been approved by {$sales_rep_name}. Customer can now proceed with payment.",
            'Sales Representative',
            $sales_rep_id,
            $sales_rep_name,
            $order_id_numeric,
            'Order'
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Order approved successfully. Customer has been notified and can proceed with payment.',
            'order_id' => $order_id_clean
        ]);
    }
    
    /**
     * Disapprove order (by Sales Rep at any stage)
     * Moves order to disapproved_orders, cancels it, and notifies customer
     */
    public function disapprove_order()
    {
        $sales_rep_id = $this->get_current_sales_rep_id();
        $order_id = $this->input->post('order_id');
        $reason = $this->input->post('reason') ?: 'Order disapproved by Sales Representative';
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Remove # prefix if present
        $order_id_clean = str_replace('#GI', '', $order_id);
        $order_id_clean = str_replace('#', '', $order_id_clean);
        $order_id_clean = ltrim($order_id_clean, '0');
        $order_id_clean = 'GI' . str_pad($order_id_clean, 3, '0', STR_PAD_LEFT);
        
        // Start transaction
        $this->db->trans_start();
        
        // Try to find order in any status table
        $order = null;
        $source_table = null;
        
        // Check pending_review_orders
        $this->db->where('OrderID', $order_id_clean);
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $order = $this->db->get('pending_review_orders')->row();
        if ($order) {
            $source_table = 'pending_review_orders';
        }
        
        // Check awaiting_admin_orders
        if (!$order) {
            $this->db->where('OrderID', $order_id_clean);
            $this->db->where('SalesRep_ID', $sales_rep_id);
            $order = $this->db->get('awaiting_admin_orders')->row();
            if ($order) {
                $source_table = 'awaiting_admin_orders';
            }
        }
        
        // Check ready_to_approve_orders
        if (!$order) {
            $this->db->where('OrderID', $order_id_clean);
            $this->db->where('SalesRep_ID', $sales_rep_id);
            $order = $this->db->get('ready_to_approve_orders')->row();
            if ($order) {
                $source_table = 'ready_to_approve_orders';
            }
        }
        
        if (!$order) {
            $this->db->trans_rollback();
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Check if this was already disapproved by Admin
        $was_admin_disapproved = false;
        $admin_notes = '';
        if (isset($order->AdminStatus) && $order->AdminStatus === 'Disapproved') {
            $was_admin_disapproved = true;
            $admin_notes = isset($order->AdminNotes) ? $order->AdminNotes : '';
            // Combine admin reason with sales rep's reason if provided
            if ($admin_notes && $reason !== 'Order disapproved by Sales Representative') {
                $reason = 'Admin Reason: ' . $admin_notes . ' | Sales Rep Finalization: ' . $reason;
            } elseif ($admin_notes) {
                $reason = 'Admin Reason: ' . $admin_notes . ' | Finalized by Sales Representative';
            }
        }
        
        // Insert into disapproved_orders
        $disapproved_data = [
            'OrderID' => $order->OrderID,
            'ProductName' => $order->ProductName,
            'Address' => $order->Address,
            'OrderDate' => $order->OrderDate,
            'Shape' => $order->Shape,
            'Dimension' => $order->Dimension,
            'Type' => $order->Type,
            'Thickness' => $order->Thickness,
            'EdgeWork' => $order->EdgeWork,
            'FrameType' => $order->FrameType,
            'Engraving' => $order->Engraving,
            'FileAttached' => $order->FileAttached,
            'TotalQuotation' => $order->TotalQuotation,
            'Customer_ID' => $order->Customer_ID,
            'SalesRep_ID' => $order->SalesRep_ID,
            'DisapprovedBy' => $was_admin_disapproved ? 'Admin' : 'Sales Rep',
            'DisapprovedBy_ID' => $was_admin_disapproved ? null : $sales_rep_id,
            'DisapprovalReason' => $reason,
            'Disapproved_Date' => date('Y-m-d H:i:s'),
            'CustomerNotified' => 0, // Will be set to 1 after notification is sent
        ];
        
        $this->db->insert('disapproved_orders', $disapproved_data);
        
        // Delete from source table
        $this->db->where('OrderID', $order_id_clean);
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->delete($source_table);
        
        // Also remove from order_page if exists
        $this->db->where('OrderID', $order_id_clean);
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->delete('order_page');
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Failed to disapprove order']);
            return;
        }
        
        // Send notification to customer
        $this->notify_customer_disapproved($order->Customer_ID, $order_id_clean, $reason);
        
        // Update notification status in disapproved_orders
        $this->db->where('OrderID', $order_id_clean);
        $this->db->update('disapproved_orders', [
            'CustomerNotified' => 1,
            'CustomerNotified_Date' => date('Y-m-d H:i:s')
        ]);
        
        // Get sales rep name for logging
        $sales_rep = $this->User_model->get_by_id($sales_rep_id);
        $sales_rep_name = $sales_rep ? trim($sales_rep->First_Name . ' ' . $sales_rep->Last_Name) : 'Sales Representative';
        
        // Log activity and create notification
        $this->log_activity(
            'Order Disapproved',
            "Order {$order_id_clean} has been disapproved by {$sales_rep_name}. Reason: {$reason}",
            'Sales Representative',
            $sales_rep_id,
            $sales_rep_name,
            $order_id_numeric,
            'Order'
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Order disapproved and cancelled. Customer has been notified immediately.',
            'order_id' => $order_id_clean
        ]);
    }
    
    /**
     * Request approval - moves order from pending_review_orders to awaiting_admin_orders
     */
    public function request_approval()
    {
        // Set JSON header
        header('Content-Type: application/json');
        
        $sales_rep_id = $this->get_current_sales_rep_id();
        $order_id = $this->input->post('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Remove # prefix if present and extract numeric part
        $order_id_clean = str_replace('#GI', '', $order_id);
        $order_id_clean = str_replace('#', '', $order_id_clean);
        $order_id_clean = str_replace('GI', '', $order_id_clean);
        $order_id_numeric_part = ltrim($order_id_clean, '0');
        if (empty($order_id_numeric_part)) {
            $order_id_numeric_part = '1';
        }
        $order_id_clean = 'GI' . str_pad($order_id_numeric_part, 3, '0', STR_PAD_LEFT);
        $order_id_numeric = (int)$order_id_numeric_part;
        
        // Start transaction
        $this->db->trans_start();
        
        // Get order from pending_review_orders
        $this->db->where('OrderID', $order_id_clean);
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $order = $this->db->get('pending_review_orders')->row();
        
        if (!$order) {
            $this->db->trans_rollback();
            echo json_encode(['success' => false, 'message' => 'Order not found in pending review']);
            return;
        }
        
        // Insert into awaiting_admin_orders
        $awaiting_data = [
            'OrderID' => $order->OrderID,
            'ProductName' => $order->ProductName,
            'Address' => $order->Address,
            'OrderDate' => $order->OrderDate,
            'Shape' => $order->Shape,
            'Dimension' => $order->Dimension,
            'Type' => $order->Type,
            'Thickness' => $order->Thickness,
            'EdgeWork' => $order->EdgeWork,
            'FrameType' => $order->FrameType,
            'Engraving' => $order->Engraving,
            'FileAttached' => $order->FileAttached,
            'TotalQuotation' => $order->TotalQuotation,
            'Customer_ID' => $order->Customer_ID,
            'SalesRep_ID' => $order->SalesRep_ID,
            'RequestedBy_SalesRep_ID' => $sales_rep_id,
            'Requested_Date' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('awaiting_admin_orders', $awaiting_data);
        
        // Delete from pending_review_orders
        $this->db->where('OrderID', $order_id_clean);
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->delete('pending_review_orders');
        
        // Update order_page status
        $this->db->where('OrderID', $order_id_clean);
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->update('order_page', ['Status' => 'Awaiting Admin']);
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Failed to request approval']);
            return;
        }
        
        // Get sales rep name for logging
        $sales_rep = $this->User_model->get_by_id($sales_rep_id);
        $sales_rep_name = $sales_rep ? trim($sales_rep->First_Name . ' ' . $sales_rep->Last_Name) : 'Sales Representative';
        
        // Log activity and create notification (wrap in try-catch to prevent breaking the request)
        try {
            $this->log_activity(
                'Approval Requested',
                "Order {$order_id_clean} approval has been requested by {$sales_rep_name}. Order is now awaiting admin review.",
                'Sales Representative',
                $sales_rep_id,
                $sales_rep_name,
                $order_id_numeric,
                'Order'
            );
        } catch (Exception $e) {
            // Log error but don't break the request
            log_message('error', 'Failed to log activity for request_approval: ' . $e->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Approval requested. Order is now awaiting admin review.',
            'order_id' => $order_id_clean
        ]);
    }
    
    /**
     * Notify customer that their order has been approved
     * Customer can now proceed with payment (E-Wallet or Cash on Delivery)
     */
    private function notify_customer_approved($customer_id, $order_id, $total_amount)
    {
        // Get customer email
        $this->db->where('UserID', $customer_id);
        $customer = $this->db->get('user')->row();
        
        if (!$customer || !$customer->Email) {
            log_message('error', "Cannot notify customer: Customer ID {$customer_id} not found or has no email");
            return false;
        }
        
        // Update notification status in approved_orders
        $this->db->where('OrderID', $order_id);
        $this->db->update('approved_orders', [
            'CustomerNotified' => 1,
            'CustomerNotified_Date' => date('Y-m-d H:i:s')
        ]);
        
        // TODO: Implement actual email/SMS notification
        // For now, log the notification
        $message = "Order {$order_id} has been approved. Total: ₱" . number_format($total_amount, 2) . ". Customer can proceed with payment (E-Wallet or Cash on Delivery).";
        log_message('info', "Customer notification sent: {$customer->Email} - {$message}");
        
        // In production, implement email sending here:
        // $this->load->library('email');
        // $this->email->from('noreply@glassify.com', 'Glassify');
        // $this->email->to($customer->Email);
        // $this->email->subject('Order Approved - Ready for Payment');
        // $this->email->message($message);
        // $this->email->send();
        
        return true;
    }
    
    /**
     * Notify customer that their order has been disapproved/cancelled
     */
    private function notify_customer_disapproved($customer_id, $order_id, $reason)
    {
        // Get customer email
        $this->db->where('UserID', $customer_id);
        $customer = $this->db->get('user')->row();
        
        if (!$customer || !$customer->Email) {
            log_message('error', "Cannot notify customer: Customer ID {$customer_id} not found or has no email");
            return false;
        }
        
        // TODO: Implement actual email/SMS notification
        // For now, log the notification
        $message = "Order {$order_id} has been rejected and cancelled. Reason: {$reason}";
        log_message('info', "Customer notification sent: {$customer->Email} - {$message}");
        
        // In production, implement email sending here:
        // $this->load->library('email');
        // $this->email->from('noreply@glassify.com', 'Glassify');
        // $this->email->to($customer->Email);
        // $this->email->subject('Order Rejected');
        // $this->email->message($message);
        // $this->email->send();
        
        return true;
    }
    
    /**
     * Get payment details for popup display
     * Fetches up-to-date data from payment table
     */
    public function get_payment_details()
    {
        // Set JSON header
        header('Content-Type: application/json');
        
        // Check authentication
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Sales Representative') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        $sales_rep_id = $this->get_current_sales_rep_id();
        $order_id = $this->input->post('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Remove # prefix and extract numeric part
        $order_id_clean = str_replace(['#GI', '#'], '', $order_id);
        $order_id_clean = str_replace('GI', '', $order_id_clean);
        $order_id_numeric = ltrim($order_id_clean, '0');
        if (empty($order_id_numeric)) {
            $order_id_numeric = 1;
        }
        $order_id_numeric = (int)$order_id_numeric;
        
        try {
            // Get payment record from database
            $this->db->where('OrderID', $order_id_numeric);
            $payment = $this->db->get('payment')->row();
            
            if (!$payment) {
                // If payment record doesn't exist, get from approved_orders
                $order_id_string = 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
                $this->db->where('OrderID', $order_id_string);
                $this->db->where('SalesRep_ID', $sales_rep_id);
                $order = $this->db->get('approved_orders')->row();
                
                if (!$order) {
                    echo json_encode(['success' => false, 'message' => 'Order not found']);
                    return;
                }
                
                // Get customer name
                $this->db->select('First_Name, Last_Name');
                $this->db->where('UserID', $order->Customer_ID);
                $customer = $this->db->get('user')->row();
                $customer_name = '';
                if ($customer) {
                    $customer_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
                }
                
                // Get product image
                $this->db->select('ImageUrl');
                $this->db->where('ProductName', $order->ProductName);
                $product = $this->db->get('product')->row();
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'customer_name' => $customer_name,
                        'product_name' => $order->ProductName,
                        'product_image' => $product ? ($product->ImageUrl ?? '') : '',
                        'amount' => $order->TotalQuotation,
                        'payment_method' => $order->PaymentMethod ?? 'Not Selected'
                    ]
                ]);
                return;
            }
            
            // Get product image
            $this->db->select('ImageUrl');
            $this->db->where('ProductName', $payment->ProductName);
            $product = $this->db->get('product')->row();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'customer_name' => $payment->CustomerName ?? '',
                    'product_name' => $payment->ProductName ?? '',
                    'product_image' => $product ? ($product->ImageUrl ?? '') : '',
                    'amount' => $payment->Amount,
                    'payment_method' => $payment->PaymentMethod ?? 'Not Selected'
                ]
            ]);
        } catch (Exception $e) {
            log_message('error', 'Error in get_payment_details: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Mark payment as paid
     * Updates payment status in payment table and order table
     */
    public function mark_payment_paid()
    {
        // Set JSON header
        header('Content-Type: application/json');
        
        // Check authentication
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Sales Representative') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        $sales_rep_id = $this->get_current_sales_rep_id();
        $order_id = $this->input->post('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        try {
            // Remove # prefix and extract numeric part
            $order_id_clean = str_replace(['#GI', '#'], '', $order_id);
            $order_id_clean = str_replace('GI', '', $order_id_clean);
            $order_id_numeric = ltrim($order_id_clean, '0');
            if (empty($order_id_numeric)) {
                $order_id_numeric = 1;
            }
            $order_id_numeric = (int)$order_id_numeric;
            
            // Start transaction
            $this->db->trans_start();
            
            // Update payment table
            $this->db->where('OrderID', $order_id_numeric);
            $this->db->update('payment', [
                'Status' => 'Paid',
                'Payment_Date' => date('Y-m-d H:i:s')
            ]);
            
            // Update order table PaymentStatus
            $this->db->where('OrderID', $order_id_numeric);
            $this->db->update('order', [
                'PaymentStatus' => 'Paid'
            ]);
            
            // Also update approved_orders table PaymentStatus if it exists
            $order_id_string = 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
            $this->db->where('OrderID', $order_id_string);
            $this->db->where('SalesRep_ID', $sales_rep_id);
            $this->db->update('approved_orders', [
                'PaymentStatus' => 'Paid'
            ]);
            
            // Deduct materials from inventory after payment
            // Get product ID from order or approved_orders
            $product_id = null;
            $this->db->select('Product_ID, ProductName');
            $this->db->where('OrderID', $order_id_string);
            $order_info = $this->db->get('approved_orders')->row();
            
            if ($order_info && isset($order_info->Product_ID) && $order_info->Product_ID) {
                $product_id = $order_info->Product_ID;
            } else {
                // Try to get from order_page
                $this->db->select('ProductName');
                $this->db->where('OrderID', $order_id_string);
                $order_page_info = $this->db->get('order_page')->row();
                
                if ($order_page_info && $order_page_info->ProductName) {
                    // Get product ID from product name
                    $this->db->select('Product_ID');
                    $this->db->where('ProductName', $order_page_info->ProductName);
                    $product = $this->db->get('product')->row();
                    if ($product) {
                        $product_id = $product->Product_ID;
                    }
                }
            }
            
            if ($product_id) {
                // Deduct materials for this product
                $deduction_result = $this->Inventory_model->deduct_materials_for_order($order_id_numeric, $product_id, 1);
                
                if (!$deduction_result['success']) {
                    // Log warning if some materials couldn't be deducted
                    log_message('warning', 'Some materials could not be deducted for order ' . $order_id_string . ': ' . json_encode($deduction_result['out_of_stock_items']));
                } else {
                    log_message('info', 'Materials deducted successfully for order ' . $order_id_string);
                }
            } else {
                log_message('warning', 'Could not find product ID for order ' . $order_id_string . ' - materials not deducted');
            }
            
            // Get sales rep name for logging
            $sales_rep = $this->User_model->get_by_id($sales_rep_id);
            $sales_rep_name = $sales_rep ? trim($sales_rep->First_Name . ' ' . $sales_rep->Last_Name) : 'Sales Representative';
            
            // Get payment amount
            $this->db->select('Amount');
            $this->db->where('OrderID', $order_id_numeric);
            $payment_info = $this->db->get('payment')->row();
            $payment_amount = $payment_info ? $payment_info->Amount : 0;
            
            // Log activity and create notification
            $this->log_activity(
                'Payment Received',
                "Payment for Order {$order_id_string} (Amount: ₱" . number_format($payment_amount, 2) . ") has been marked as paid by {$sales_rep_name}.",
                'Sales Representative',
                $sales_rep_id,
                $sales_rep_name,
                $order_id_numeric,
                'Payment'
            );
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                $error = $this->db->error();
                log_message('error', 'Failed to mark payment as paid. OrderID: ' . $order_id_numeric . ', Error: ' . json_encode($error));
                echo json_encode(['success' => false, 'message' => 'Failed to update payment status: ' . $error['message']]);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment marked as paid successfully'
            ]);
        } catch (Exception $e) {
            log_message('error', 'Error in mark_payment_paid: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
