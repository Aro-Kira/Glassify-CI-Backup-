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

    /**
     * Helper function to convert order ID from various formats to numeric
     * Handles: GI001, #GI001, #1, 1, etc.
     * 
     * @param string|int $order_id Order ID in any format
     * @return array ['numeric' => int, 'formatted' => string] - Numeric ID and formatted string (GI001)
     */
    private function parse_order_id($order_id)
    {
        // Remove # prefix if present
        $order_id_clean = str_replace('#', '', (string)$order_id);
        $order_id_clean = str_replace('GI', '', $order_id_clean);
        $order_id_numeric_part = ltrim($order_id_clean, '0');
        if (empty($order_id_numeric_part)) {
            $order_id_numeric_part = '1';
        }
        $order_id_numeric = (int)$order_id_numeric_part;
        $order_id_formatted = 'GI' . str_pad($order_id_numeric_part, 3, '0', STR_PAD_LEFT);
        
        return [
            'numeric' => $order_id_numeric,
            'formatted' => $order_id_formatted
        ];
    }

    // Dashboard - Show statistics for this Sales Rep only
    public function sales_dashboard()
    {
        $sales_rep_id = $this->get_current_sales_rep_id();
        $sales_rep = $this->User_model->get_by_id($sales_rep_id);
        
        // Get total orders assigned today (from all order status tables)
        $today = date('Y-m-d');
        $total_orders_today = 0;
        
        // Count from unified order table with different statuses
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('DATE(OrderDate)', $today);
        $this->db->where('Status', 'Pending Review');
        $total_orders_today += $this->db->count_all_results('`order`');
        
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('DATE(OrderDate)', $today);
        $this->db->where('Status', 'Awaiting Admin');
        $total_orders_today += $this->db->count_all_results('`order`');
        
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('DATE(OrderDate)', $today);
        $this->db->where('Status', 'Ready to Approve');
        $total_orders_today += $this->db->count_all_results('`order`');
        
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('DATE(OrderDate)', $today);
        $this->db->where('Status', 'Approved');
        $total_orders_today += $this->db->count_all_results('`order`');
        
        // Get total orders needing approval (Status = 'Pending Review')
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('Status', 'Pending Review');
        $needs_approval_count = $this->db->count_all_results('`order`');
        
        // Get total payments with "Under Review" status
        // "Under Review" typically means payments with Status = 'Pending' that need verification
        // First, get all approved orders for this sales rep
        $this->db->select('OrderID');
        $this->db->from('`order`');
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('Status', 'Approved');
        $approved_order_ids = $this->db->get()->result();
        
        $under_review_count = 0;
        if (!empty($approved_order_ids)) {
            // Extract numeric OrderIDs (OrderID is already numeric in unified table)
            $numeric_order_ids = [];
            foreach ($approved_order_ids as $order) {
                $numeric_order_ids[] = (int)$order->OrderID;
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
        
        // Process activities to ensure UserName is not null/empty
        if (!empty($recent_activities)) {
            foreach ($recent_activities as $activity) {
                // If UserName is null or empty, try to get customer name
                if (empty($activity->UserName)) {
                    $customer_name = null;
                    
                    // If Role is Client or Customer, try to get customer name from order
                    if (($activity->Role === 'Client' || $activity->Role === 'Customer') && !empty($activity->RelatedID) && $activity->RelatedType === 'Order') {
                        // Get customer name from order
                        $this->db->select('u.First_Name, u.Last_Name');
                        $this->db->from('`order` o');
                        $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
                        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
                        $this->db->where('o.OrderID', $activity->RelatedID);
                        $order_customer = $this->db->get()->row();
                        
                        if ($order_customer) {
                            $first_name = trim($order_customer->First_Name ?? '');
                            $last_name = trim($order_customer->Last_Name ?? '');
                            if (!empty($last_name)) {
                                $customer_name = $last_name;
                                if (!empty($first_name)) {
                                    $customer_name = $first_name . ' ' . $last_name;
                                }
                            } elseif (!empty($first_name)) {
                                $customer_name = $first_name;
                            }
                        }
                    }
                    
                    // If still no customer name and UserID exists, try to get from user table
                    if (empty($customer_name) && !empty($activity->UserID)) {
                        // Check if user is a customer
                        $this->db->select('u.First_Name, u.Last_Name, u.Role');
                        $this->db->from('user u');
                        $this->db->where('u.UserID', $activity->UserID);
                        $user = $this->db->get()->row();
                        
                        if ($user) {
                            // If it's a customer, get name from customer table
                            if ($user->Role === 'Customer') {
                                $this->db->select('u.First_Name, u.Last_Name');
                                $this->db->from('customer c');
                                $this->db->join('user u', 'u.UserID = c.UserID', 'left');
                                $this->db->where('c.UserID', $activity->UserID);
                                $customer = $this->db->get()->row();
                                
                                if ($customer) {
                                    $first_name = trim($customer->First_Name ?? '');
                                    $last_name = trim($customer->Last_Name ?? '');
                                    if (!empty($last_name)) {
                                        $customer_name = $last_name;
                                        if (!empty($first_name)) {
                                            $customer_name = $first_name . ' ' . $last_name;
                                        }
                                    } elseif (!empty($first_name)) {
                                        $customer_name = $first_name;
                                    }
                                }
                            } else {
                                // For non-customers, use user table directly
                                $first_name = trim($user->First_Name ?? '');
                                $last_name = trim($user->Last_Name ?? '');
                                if (!empty($last_name)) {
                                    $customer_name = $last_name;
                                    if (!empty($first_name)) {
                                        $customer_name = $first_name . ' ' . $last_name;
                                    }
                                } elseif (!empty($first_name)) {
                                    $customer_name = $first_name;
                                }
                            }
                        }
                    }
                    
                    // Set the UserName based on what we found
                    if (!empty($customer_name)) {
                        $activity->UserName = $customer_name;
                    } elseif ($activity->Role === 'System') {
                        $activity->UserName = 'System';
                    } elseif ($activity->Role === 'Client' || $activity->Role === 'Customer') {
                        $activity->UserName = 'Customer';
                    } else {
                        $activity->UserName = 'User';
                    }
                }
            }
        } else {
            // If no activities in log, generate from existing data
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
        try {
            // Check if system_activity_log table exists before inserting
            if ($this->db->table_exists('system_activity_log')) {
                // Truncate action to 50 characters if needed (database constraint)
                $action = substr($action, 0, 50);
                
                // Verify UserID exists if provided (to avoid foreign key constraint errors)
                if ($user_id !== null) {
                    $this->db->select('UserID');
                    $this->db->where('UserID', $user_id);
                    $user_exists = $this->db->get('user')->row();
                    if (!$user_exists) {
                        log_message('warning', 'UserID ' . $user_id . ' does not exist in user table. Setting to NULL.');
                        $user_id = null;
                    }
                }
                
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
                
                // Check for insert errors
                if ($this->db->affected_rows() === 0) {
                    $error = $this->db->error();
                    log_message('error', 'Failed to insert into system_activity_log: ' . ($error['message'] ?? 'Unknown error'));
                }
            }
            
            // Also create notification in sales_notif table
            if ($this->db->table_exists('sales_notif')) {
                try {
                    $icon = $this->determine_notification_icon($action, $description);
                    $notification_description = $action . ': ' . $description;
                    $this->add_sales_notification($icon, $role, $notification_description, 'Unread', $related_id, $related_type);
                } catch (Exception $notif_error) {
                    // Log error but don't throw - notification failure shouldn't break the main operation
                    log_message('error', 'Failed to add sales notification: ' . $notif_error->getMessage());
                }
            }
        } catch (Exception $e) {
            // Log error but don't throw exception
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
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
        
        // Get recent orders from unified order table with customer info
        $this->db->select('o.OrderID, o.OrderNumber, o.OrderDate, o.Created_Date, o.Customer_ID, u.First_Name, u.Last_Name');
        $this->db->from('`order` o');
        $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
        $this->db->where('o.SalesRep_ID', $sales_rep_id);
        $this->db->where('o.Status', 'Pending Review');
        $this->db->order_by('o.Created_Date', 'DESC');
        $this->db->limit(3);
        $recent_orders = $this->db->get()->result();
        
        foreach ($recent_orders as $order) {
            // Build customer name with proper fallbacks
            $first_name = trim($order->First_Name ?? '');
            $last_name = trim($order->Last_Name ?? '');
            
            // Use last name if available, otherwise first name, otherwise 'Customer'
            if (!empty($last_name)) {
                $customer_name = $last_name;
                if (!empty($first_name)) {
                    $customer_name = $first_name . ' ' . $last_name;
                }
            } elseif (!empty($first_name)) {
                $customer_name = $first_name;
            } else {
                $customer_name = 'Customer';
            }
            
            // Format Order ID using OrderNumber if available
            $order_id_formatted = '#' . ($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT));
            
            // Use Created_Date for more accurate timestamp, fallback to OrderDate
            $timestamp = !empty($order->Created_Date) ? $order->Created_Date : $order->OrderDate;
            
            $activities[] = (object)[
                'Action' => 'Info',
                'Description' => "New order created ({$order_id_formatted})",
                'Role' => 'Client',
                'UserName' => $customer_name,
                'Timestamp' => $timestamp
            ];
        }
        
        // Get recent inventory warnings (low stock items)
        $this->db->select('Name, InStock, DateAdded, Updated_Date');
        $this->db->from('inventory_items');
        $this->db->where('InStock >', 0);
        $this->db->where('InStock <=', 10);
        $this->db->order_by('Updated_Date', 'DESC');
        $this->db->limit(2);
        $low_stock_items = $this->db->get()->result();
        
        foreach ($low_stock_items as $item) {
            // Use Updated_Date for more accurate timestamp, fallback to DateAdded
            $timestamp = !empty($item->Updated_Date) ? $item->Updated_Date : $item->DateAdded;
            
            $activities[] = (object)[
                'Action' => 'Warning',
                'Description' => "Stock running low: {$item->Name}",
                'Role' => 'System',
                'UserName' => 'System',
                'Timestamp' => $timestamp
            ];
        }
        
        // Get recent high priority issues
        $this->db->select('Category, Report_Date, Created_Date, First_Name, Last_Name');
        $this->db->from('issuereport');
        $this->db->where('Priority', 'High');
        $this->db->order_by('Created_Date', 'DESC');
        $this->db->limit(2);
        $recent_issues = $this->db->get()->result();
        
        foreach ($recent_issues as $issue) {
            // Build customer name with proper fallbacks
            $first_name = trim($issue->First_Name ?? '');
            $last_name = trim($issue->Last_Name ?? '');
            
            // Use last name if available, otherwise first name, otherwise 'Customer'
            if (!empty($last_name)) {
                $customer_name = $last_name;
                if (!empty($first_name)) {
                    $customer_name = $first_name . ' ' . $last_name;
                }
            } elseif (!empty($first_name)) {
                $customer_name = $first_name;
            } else {
                $customer_name = 'Customer';
            }
            
            // Use Created_Date for more accurate timestamp, fallback to Report_Date
            $timestamp = !empty($issue->Created_Date) ? $issue->Created_Date : $issue->Report_Date;
            
            $activities[] = (object)[
                'Action' => 'Error',
                'Description' => "High priority issue: {$issue->Category}",
                'Role' => 'Client',
                'UserName' => $customer_name,
                'Timestamp' => $timestamp
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
        // Note: payment table uses numeric OrderID, which matches order.OrderID in unified table
        $this->db->select('
            o.OrderID,
            o.OrderNumber,
            o.Customer_ID,
            o.SalesRep_ID,
            o.OrderDate,
            o.TotalAmount as TotalQuotation,
            o.Status,
            o.PaymentStatus,
            o.PaymentMethod,
            o.Approved_Date,
            o.DeliveryAddress as Address,
            user.First_Name,
            user.Last_Name,
            user.Email,
            product.ImageUrl as ProductImage,
            product.ProductName,
            payment.Payment_ID,
            payment.Amount as PaymentAmount,
            payment.Payment_Date,
            payment.Transaction_ID,
            payment.ReceiptPath,
            payment.Status as PaymentStatus,
            payment.CustomerName as PaymentCustomerName,
            payment.ProductName as PaymentProductName,
            payment.PaymentMethod as PaymentMethod
        ');
        $this->db->from('`order` o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user', 'user.UserID = c.UserID', 'left');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product', 'product.Product_ID = oi.Product_ID', 'left');
        $this->db->join('payment', 'payment.OrderID = o.OrderID', 'left');
        $this->db->where('o.SalesRep_ID', $sales_rep_id);
        // Show orders that are Approved OR have a payment record with receipt (E-Wallet orders)
        // This allows E-Wallet orders with receipts to show even if not yet approved
        $this->db->where("(o.Status = 'Approved' OR (payment.ReceiptPath IS NOT NULL AND payment.ReceiptPath != ''))", NULL, FALSE);
        // Order by Approved_Date if available, otherwise OrderDate
        $this->db->order_by("COALESCE(o.Approved_Date, o.OrderDate)", 'DESC', FALSE);
        $this->db->group_by('o.OrderID'); // Group to avoid duplicates from multiple order_items
        $orders = $this->db->get()->result();
        
        // Calculate weekly sales (last 7 days) - only from approved orders
        $week_start = date('Y-m-d', strtotime('-7 days'));
        $this->db->select_sum('TotalAmount');
        $this->db->from('`order`');
        $this->db->where('SalesRep_ID', $sales_rep_id);
        $this->db->where('Status', 'Approved');
        $this->db->where('Approved_Date >=', $week_start);
        $weekly_sales_result = $this->db->get()->row();
        $weekly_sales = $weekly_sales_result->TotalAmount ?? 0;
        
        // Count pending, under review, and overdue payments
        $pending_count = 0;
        $overdue_count = 0;
        foreach ($orders as $order) {
            // Get payment status from payment table if available, otherwise from order table
            $payment_status = $order->PaymentStatus ?? 'Pending';
            
            // Determine if status should be "Under Review" (has receipt but not paid)
            if ($payment_status === 'Pending' && !empty($order->ReceiptPath)) {
                $payment_status = 'Under Review';
            }
            
            // Count pending (excluding under review)
            if ($payment_status === 'Pending') {
                $pending_count++;
            }
            
            // Check if overdue (more than 7 days since approval and still pending/under review)
            if (($payment_status === 'Pending' || $payment_status === 'Under Review') && $order->Approved_Date) {
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
            // Sync customers from user table to enduser table (for new customers)
            $this->sync_customers_to_enduser();
            
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
        
        // Load Order_model
        $this->load->model('Order_model');
        
        // Get all orders for this sales rep (will filter to relevant statuses)
        $all_orders = $this->Order_model->get_sales_rep_orders($sales_rep_id);
        
        // Debug: Log if no orders found
        if (empty($all_orders)) {
            log_message('debug', 'Sales Orders: No orders found for SalesRep_ID: ' . $sales_rep_id);
        }
        
        // Define the three relevant statuses for this page
        $relevant_statuses = ['Pending Review', 'Awaiting Admin', 'Ready to Approve'];
        
        // Transform orders and filter to only relevant statuses
        $orders = [];
        foreach ($all_orders as $order) {
            $order_status = $order->Status ?? 'Pending Review';
            
            // Handle empty string status (treat as 'Pending Review')
            if (empty($order_status) || trim($order_status) === '') {
                $order_status = 'Pending Review';
            }
            
            // Normalize old 'Pending' status to 'Pending Review' for backward compatibility
            if ($order_status === 'Pending') {
                $order_status = 'Pending Review';
            }
            
            // Only include orders with the three relevant statuses
            if (!in_array($order_status, $relevant_statuses)) {
                continue;
            }
            
            $orders[] = (object)[
                'OrderID' => $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT),
                'ProductName' => $order->ProductName ?? 'N/A',
                'Address' => $order->DeliveryAddress ?? 'N/A',
                'OrderDate' => $order->OrderDate ?? date('Y-m-d H:i:s'),
                'Shape' => $order->GlassShape ?? '',
                'Dimension' => $order->Dimensions ?? '',
                'Type' => $order->GlassType ?? '',
                'Thickness' => $order->GlassThickness ?? '',
                'EdgeWork' => $order->EdgeWork ?? '',
                'FrameType' => $order->FrameType ?? '',
                'Engraving' => $order->Engraving ?? '',
                'FileAttached' => $order->DesignRef ?? null,
                'TotalQuotation' => $order->TotalAmount ?? 0,
                'Customer_ID' => $order->Customer_ID ?? 0,
                'SalesRep_ID' => $order->SalesRep_ID ?? $sales_rep_id,
                'Status' => $order_status
            ];
        }
        
        // For Ready to Approve orders, get AdminStatus from ready_to_approve_orders
        $ready_orders = $this->Order_model->get_ready_to_approve_orders($sales_rep_id);
        
        // Create a map for faster lookup by OrderNumber
        $ready_orders_map = [];
        foreach ($ready_orders as $ready_order) {
            $order_id_formatted = $ready_order->OrderNumber ?? 'GI' . str_pad($ready_order->OrderID, 3, '0', STR_PAD_LEFT);
            $ready_orders_map[$order_id_formatted] = $ready_order;
        }
        
        // Match orders and add AdminStatus
        foreach ($orders as &$order_item) {
            if (isset($ready_orders_map[$order_item->OrderID])) {
                $ready_order = $ready_orders_map[$order_item->OrderID];
                $order_item->AdminStatus = $ready_order->AdminStatus ?? null;
                $order_item->AdminNotes = $ready_order->AdminNotes ?? null;
            }
        }
        
        // Count orders by status (also handle legacy 'Pending' status)
        $pending_count = $this->Order_model->count_sales_rep_orders_by_status($sales_rep_id, 'Pending Review');
        // Also count old 'Pending' status for backward compatibility
        $pending_legacy = $this->db->where('SalesRep_ID', $sales_rep_id)
                                   ->where('Status', 'Pending')
                                   ->count_all_results('order');
        $pending_count += $pending_legacy;
        
        $awaiting_count = $this->Order_model->count_sales_rep_orders_by_status($sales_rep_id, 'Awaiting Admin');
        $ready_count = $this->Order_model->count_sales_rep_orders_by_status($sales_rep_id, 'Ready to Approve');
        
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
    // Uses Order_model->get_order_details_for_popup()
    public function get_order_details()
    {
        // Disable query caching to ensure fresh data
        $this->db->cache_off();
        
        // Set HTTP headers to prevent caching
        $this->output->set_header('Cache-Control: no-cache, no-store, must-revalidate');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');
        
        header('Content-Type: application/json');
        
        $sales_rep_id = $this->get_current_sales_rep_id();
        $order_id = $this->input->post('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Load Order_model
        $this->load->model('Order_model');
        
        // Parse order ID to get numeric version for logging
        $order_id_parsed = $this->parse_order_id($order_id);
        $order_id_numeric = $order_id_parsed['numeric'];
        
        // Pass the original order_id to the model - it can handle both numeric and OrderNumber format
        // The model will try both OrderID (numeric) and OrderNumber (GI001 format)
        $order = $this->Order_model->get_order_details_for_popup($order_id);
        
        if (!$order) {
            // Try to get basic order info to see if it exists at all
            // Try by numeric ID first
            $basic_order = $this->Order_model->get_order($order_id_numeric);
            // If not found, try by OrderNumber
            if (!$basic_order && is_string($order_id)) {
                $this->db->where('OrderNumber', $order_id);
                $basic_order = $this->db->get('`order`')->row();
                if ($basic_order) {
                    $order_id_numeric = $basic_order->OrderID;
                }
            }
            
            if ($basic_order) {
                log_message('error', 'SalesCon::get_order_details - Order exists but get_order_details_for_popup returned null. OrderID: ' . $order_id . ' (numeric: ' . $order_id_numeric . ')');
                // Order exists but query failed - might be missing order_items or customer data
                // Return basic order info
                $order = $basic_order;
                // Get order items separately
                $order_items = $this->Order_model->get_order_customizations($order_id_numeric);
                if (!empty($order_items)) {
                    $first_item = $order_items[0];
                    $order->ProductName = $first_item->ProductName ?? 'N/A';
                    $order->GlassShape = $first_item->GlassShape ?? '';
                    $order->Dimensions = $first_item->Dimensions ?? '';
                    $order->GlassType = $first_item->GlassType ?? '';
                    $order->GlassThickness = $first_item->GlassThickness ?? '';
                    $order->EdgeWork = $first_item->EdgeWork ?? '';
                    $order->FrameType = $first_item->FrameType ?? '';
                    $order->Engraving = $first_item->Engraving ?? '';
                    $order->DesignRef = $first_item->DesignRef ?? null;
                }
                // Get customer info
                $order_with_customer = $this->Order_model->get_order_with_customer($order_id_numeric);
                if ($order_with_customer) {
                    $order->First_Name = $order_with_customer->First_Name ?? '';
                    $order->Last_Name = $order_with_customer->Last_Name ?? '';
                    $order->Email = $order_with_customer->Email ?? '';
                    $order->PhoneNum = $order_with_customer->PhoneNum ?? '';
                }
            } else {
                // Order doesn't exist at all
                log_message('error', 'SalesCon::get_order_details - Order not found in database. OrderID: ' . $order_id . ' (numeric: ' . $order_id_numeric . ')');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Order not found',
                    'debug' => [
                        'input_order_id' => $order_id,
                        'parsed_numeric' => $order_id_numeric
                    ]
                ]);
                return;
            }
        }
        
        // Verify order belongs to this sales rep
        if ($order->SalesRep_ID != $sales_rep_id) {
            echo json_encode(['success' => false, 'message' => 'Order does not belong to this sales representative']);
            return;
        }
        
        // Format response to match view expectations (JavaScript expects specific field names)
        $order_date = $order->OrderDate ?? date('Y-m-d H:i:s');
        $formatted_date = date('d/m/Y', strtotime($order_date));
        
        $response = [
            'success' => true,
            'order' => [
                'OrderID' => $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT),
                'ProductName' => $order->ProductName ?? 'N/A',
                'Address' => $order->DeliveryAddress ?? 'N/A',
                'Date' => $formatted_date, // JavaScript expects 'Date' not 'OrderDate'
                'OrderDate' => $order_date, // Keep for backward compatibility
                'Shape' => $order->GlassShape ?? 'N/A', // JavaScript expects 'Shape' not 'GlassShape'
                'Dimension' => $order->Dimensions ?? 'N/A',
                'Dimensions' => $order->Dimensions ?? 'N/A', // Keep both for compatibility
                'Type' => $order->GlassType ?? 'N/A', // JavaScript expects 'Type' not 'GlassType'
                'Thickness' => $order->GlassThickness ?? 'N/A', // JavaScript expects 'Thickness' not 'GlassThickness'
                'EdgeWork' => $order->EdgeWork ?? 'N/A',
                'FrameType' => $order->FrameType ?? 'N/A',
                'Engraving' => $order->Engraving ?? 'N/A',
                'FileAttached' => $order->DesignRef ?? null,
                'FileUrl' => $order->DesignRef ? base_url($order->DesignRef) : null,
                'TotalQuotation' => $order->TotalAmount ?? 0,
                'TotalAmount' => number_format($order->TotalAmount ?? 0, 2), // JavaScript expects 'TotalAmount' formatted
                'Customer_ID' => $order->Customer_ID ?? 0,
                'SalesRep_ID' => $order->SalesRep_ID ?? $sales_rep_id,
                'Status' => $order->Status ?? 'Pending Review',
                'First_Name' => $order->First_Name ?? '',
                'Last_Name' => $order->Last_Name ?? '',
                'Email' => $order->Email ?? '',
                'PhoneNum' => $order->PhoneNum ?? '',
                'PreferredInstallationDate' => $this->extract_preferred_installation_date($order->SpecialInstructions ?? '') ?? 'N/A'
            ]
        ];
        
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
        
        $data['notifications'] = $all_notifications;
        $data['title'] = "Glassify - Notifications";
        $data['active'] = 'notif';
        $data['content_view'] = 'sales_page/sales_notif';
        $data['page_css'] = 'sales_css/sales_notif.css';
        $this->load->view('sales_page/layout', $data);
    }
    
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
        try {
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
            
            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                $error = $this->db->error();
                log_message('error', 'Failed to insert sales notification: ' . ($error['message'] ?? 'Unknown error'));
                return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Exception in add_sales_notification: ' . $e->getMessage());
            return false;
        }
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
     * Uses Order_model->sales_rep_final_approve()
     * Moves order from ready_to_approve_orders to approved_orders
     * Notifies customer and makes order available for payment
     */
    public function approve_order()
    {
        header('Content-Type: application/json');
        
        $sales_rep_id = $this->get_current_sales_rep_id();
        $order_id = $this->input->post('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Load Order_model
        $this->load->model('Order_model');
        
        // Parse order ID - remove # prefix
        $order_id_clean = str_replace('#', '', $order_id);
        
        // Look up the order by OrderNumber or OrderID to get the actual numeric OrderID
        $order = $this->Order_model->get_order($order_id_clean);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Use the actual numeric OrderID from the database
        $order_id_numeric = $order->OrderID;
        $order_id_formatted = $order->OrderNumber ?? 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
        
        // Use Order_model function
        $result = $this->Order_model->sales_rep_final_approve($order_id_numeric, $sales_rep_id);
        
        if ($result['success']) {
            $result['order_id'] = $order_id_formatted;
        }
        
        echo json_encode($result);
    }
    
    /**
     * Disapprove order (by Sales Rep at any stage)
     * Uses Order_model->sales_rep_final_disapprove()
     * Moves order to disapproved_orders, cancels it, and notifies customer
     */
    public function disapprove_order()
    {
        header('Content-Type: application/json');
        
        try {
            $sales_rep_id = $this->get_current_sales_rep_id();
            if (!$sales_rep_id) {
                echo json_encode(['success' => false, 'message' => 'Sales representative not authenticated']);
                return;
            }
            
            $order_id = $this->input->post('order_id');
            $reason = $this->input->post('reason') ?: '';
            
            if (!$order_id) {
                echo json_encode(['success' => false, 'message' => 'Order ID is required']);
                return;
            }
            
            // Load Order_model
            $this->load->model('Order_model');
            
            // Parse order ID - remove # prefix
            $order_id_clean = str_replace('#', '', $order_id);
            
            // Look up the order by OrderNumber or OrderID to get the actual numeric OrderID
            $order = $this->Order_model->get_order($order_id_clean);
            
            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                return;
            }
            
            // Use the actual numeric OrderID from the database
            $order_id_numeric = $order->OrderID;
            $order_id_formatted = $order->OrderNumber ?? 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
            
            // Use Order_model function
            $result = $this->Order_model->sales_rep_final_disapprove($order_id_numeric, $sales_rep_id, $reason);
            
            if ($result['success']) {
                $result['order_id'] = $order_id_formatted;
            }
            
            echo json_encode($result);
        } catch (Exception $e) {
            log_message('error', 'SalesCon::disapprove_order - Exception: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'An error occurred while disapproving the order: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Request approval - moves order from pending_review_orders to awaiting_admin_orders
     * Uses Order_model->request_admin_approval()
     */
    public function request_approval()
    {
        // Set JSON header
        header('Content-Type: application/json');
        
        $sales_rep_id = $this->get_current_sales_rep_id();
        $order_id = $this->input->post('order_id');
        $notes = $this->input->post('notes') ?: '';
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Load Order_model
        $this->load->model('Order_model');
        
        // Pass the original order_id to the model - it can handle both numeric and OrderNumber format
        // The model will try both OrderID (numeric) and OrderNumber (GI001 format)
        $result = $this->Order_model->request_admin_approval($order_id, $sales_rep_id, $notes);
        
        // Parse for response formatting
        $order_id_parsed = $this->parse_order_id($order_id);
        $order_id_formatted = $order_id_parsed['formatted'];
        
        if ($result['success']) {
            $result['order_id'] = $order_id_formatted;
        }
        
        echo json_encode($result);
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
                // If payment record doesn't exist, get from unified order table
                $this->db->select('o.*, p.ProductName');
                $this->db->from('`order` o');
                $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
                $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
                $this->db->where('o.OrderID', $order_id_numeric);
                $this->db->where('o.SalesRep_ID', $sales_rep_id);
                $this->db->where('o.Status', 'Approved');
                $this->db->group_by('o.OrderID');
                $order = $this->db->get()->row();
                
                if (!$order) {
                    echo json_encode(['success' => false, 'message' => 'Order not found']);
                    return;
                }
                
                // Get customer name
                $this->db->select('u.First_Name, u.Last_Name');
                $this->db->from('customer c');
                $this->db->join('user u', 'u.UserID = c.UserID', 'left');
                $this->db->where('c.Customer_ID', $order->Customer_ID);
                $customer = $this->db->get()->row();
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
                        'product_name' => $order->ProductName ?? 'N/A',
                        'product_image' => $product ? ($product->ImageUrl ?? '') : '',
                        'amount' => $order->TotalAmount,
                        'payment_method' => $order->PaymentMethod ?? 'Not Selected',
                        'receipt_path' => '' // No receipt if payment record doesn't exist
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
                    'payment_method' => $payment->PaymentMethod ?? 'Not Selected',
                    'receipt_path' => $payment->ReceiptPath ?? ''
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
        // Set JSON header first
        header('Content-Type: application/json');
        
        // Enable error reporting for debugging (only in development)
        if (ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 0); // Don't display, but log
        }
        
        // Set up error handler to catch any fatal errors
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                log_message('error', 'Fatal error in mark_payment_paid: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']);
                if (!headers_sent()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Fatal error: ' . $error['message'] . ' (Check logs for details)',
                        'error_file' => basename($error['file']),
                        'error_line' => $error['line']
                    ]);
                }
            }
        });
        
        try {
            // Check authentication
            if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Sales Representative') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
                return;
            }
            
            $sales_rep_id = $this->get_current_sales_rep_id();
            $order_id = $this->input->post('order_id');
            
            if (!$sales_rep_id) {
                echo json_encode(['success' => false, 'message' => 'Sales representative ID not found. Please log in again.']);
                return;
            }
            
            if (!$order_id) {
                echo json_encode(['success' => false, 'message' => 'Order ID is required']);
                return;
            }
            
            // Log the attempt
            log_message('info', 'mark_payment_paid called: order_id=' . $order_id . ', sales_rep_id=' . $sales_rep_id);
            // Remove # prefix and handle both numeric and GI format
            $order_id_clean = str_replace(['#GI', '#'], '', $order_id);
            $order_id_clean = trim($order_id_clean);
            
            // Try to find order by OrderNumber first (if it's in GI format)
            $order = null;
            $order_id_numeric = null;
            
            if (preg_match('/^GI\d+$/i', $order_id_clean)) {
                // It's in GI format, look up by OrderNumber
                $this->db->select('OrderID, Status, TotalAmount, PaymentMethod');
                $this->db->where('OrderNumber', $order_id_clean);
                $this->db->where('SalesRep_ID', $sales_rep_id);
                $order = $this->db->get('`order`')->row();
                if ($order) {
                    $order_id_numeric = $order->OrderID;
                }
            }
            
            // If not found by OrderNumber, try numeric lookup
            if (!$order) {
                $order_id_clean_numeric = str_replace('GI', '', $order_id_clean);
                $order_id_clean_numeric = ltrim($order_id_clean_numeric, '0');
                if (empty($order_id_clean_numeric)) {
                    $order_id_clean_numeric = 1;
                }
                $order_id_numeric = (int)$order_id_clean_numeric;
                
                // Verify order exists and belongs to this sales rep
                $this->db->select('OrderID, Status, TotalAmount, PaymentMethod');
                $this->db->where('OrderID', $order_id_numeric);
                $this->db->where('SalesRep_ID', $sales_rep_id);
                $order = $this->db->get('`order`')->row();
            }
            
            if (!$order) {
                throw new Exception('Order not found or does not belong to this sales representative');
            }
            
            // Ensure we have the numeric OrderID
            if (!$order_id_numeric) {
                $order_id_numeric = $order->OrderID;
            }
            
            // Generate order_id_string for logging (used later in the code)
            $order_id_string = 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
            
            // Update payment status using Order_model transaction function
            // Note: update_payment_status handles its own transaction
            $this->load->model('Order_model');
            
            log_message('info', 'Attempting to update payment status for OrderID: ' . $order_id_numeric);
            
            try {
                $update_result = $this->Order_model->update_payment_status($order_id_numeric, 'Paid');
                
                if (!$update_result) {
                    $error = $this->db->error();
                    $error_msg = $error['message'] ?? 'Unknown database error';
                    $error_code = $error['code'] ?? 0;
                    log_message('error', 'update_payment_status returned false. Error: ' . $error_msg . ' (Code: ' . $error_code . ')');
                    log_message('error', 'Order ID: ' . $order_id_numeric . ', Sales Rep ID: ' . $sales_rep_id);
                    
                    // Check if it's a database error
                    if ($this->db->trans_status() === FALSE) {
                        $trans_error = $this->db->error();
                        log_message('error', 'Transaction failed: ' . ($trans_error['message'] ?? 'Unknown transaction error'));
                    }
                    
                    throw new Exception('Failed to update payment status: ' . $error_msg);
                }
                
                log_message('info', 'Payment status updated successfully for OrderID: ' . $order_id_numeric);
            } catch (Exception $update_error) {
                log_message('error', 'Exception in update_payment_status: ' . $update_error->getMessage());
                log_message('error', 'Stack trace: ' . $update_error->getTraceAsString());
                log_message('error', 'File: ' . $update_error->getFile() . ', Line: ' . $update_error->getLine());
                throw $update_error;
            } catch (Error $update_error) {
                log_message('error', 'Fatal error in update_payment_status: ' . $update_error->getMessage());
                log_message('error', 'File: ' . $update_error->getFile() . ', Line: ' . $update_error->getLine());
                throw new Exception('Fatal error updating payment status: ' . $update_error->getMessage());
            }
            
            // Also update approved_orders table PaymentStatus if it exists and has the column
            // Note: This is a legacy table and may not have PaymentStatus column
            if ($this->db->table_exists('approved_orders')) {
                // Check if PaymentStatus column exists in approved_orders table
                $columns = $this->db->list_fields('approved_orders');
                if (in_array('PaymentStatus', $columns)) {
                    $this->db->where('OrderID', $order_id_string);
                    $this->db->where('SalesRep_ID', $sales_rep_id);
                    $update_approved = $this->db->update('approved_orders', [
                        'PaymentStatus' => 'Paid'
                    ]);
                    
                    if (!$update_approved) {
                        $error = $this->db->error();
                        // Log warning but don't fail if approved_orders doesn't have the record
                        log_message('warning', 'Failed to update approved_orders PaymentStatus for OrderID ' . $order_id_string . ': ' . $error['message']);
                    }
                } else {
                    // Column doesn't exist, skip update (this is fine - approved_orders is legacy)
                    log_message('debug', 'approved_orders table does not have PaymentStatus column - skipping update');
                }
            }
            
            // Deduct materials from inventory after payment
            // Get product ID from unified order table via order_items
            $product_id = null;
            $this->db->select('oi.Product_ID');
            $this->db->from('order_items oi');
            $this->db->where('oi.OrderID', $order_id_numeric);
            $this->db->limit(1);
            $order_item = $this->db->get()->row();
            
            if ($order_item && isset($order_item->Product_ID) && $order_item->Product_ID) {
                $product_id = $order_item->Product_ID;
            } else {
                // Fallback: Try to get from order_page if it exists
                if ($this->db->table_exists('order_page')) {
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
            }
            
            if ($product_id) {
                // Deduct materials for this product (wrap in try-catch to prevent failure)
                try {
                    $deduction_result = $this->Inventory_model->deduct_materials_for_order($order_id_numeric, $product_id, 1);
                    
                    if (!$deduction_result['success']) {
                        // Log warning if some materials couldn't be deducted
                        log_message('error', 'Some materials could not be deducted for order ' . $order_id_string . ': ' . json_encode($deduction_result['out_of_stock_items']));
                    } else {
                        log_message('info', 'Materials deducted successfully for order ' . $order_id_string);
                    }
                } catch (Exception $deduct_error) {
                    // Log the error but don't fail the payment update
                    log_message('error', 'Failed to deduct materials for order ' . $order_id_string . ': ' . $deduct_error->getMessage());
                }
            } else {
                log_message('error', 'Could not find product ID for order ' . $order_id_string . ' - materials not deducted');
            }
            
            // Get sales rep name for logging
            $sales_rep_name = 'Sales Representative';
            try {
                $sales_rep = $this->User_model->get_by_id($sales_rep_id);
                if ($sales_rep && isset($sales_rep->First_Name) && isset($sales_rep->Last_Name)) {
                    $sales_rep_name = trim($sales_rep->First_Name . ' ' . $sales_rep->Last_Name);
                } elseif ($sales_rep && isset($sales_rep->First_Name)) {
                    $sales_rep_name = trim($sales_rep->First_Name);
                } elseif ($sales_rep && isset($sales_rep->Last_Name)) {
                    $sales_rep_name = trim($sales_rep->Last_Name);
                }
            } catch (Exception $user_error) {
                log_message('error', 'Failed to get sales rep name: ' . $user_error->getMessage());
            }
            
            // Get payment amount
            $payment_amount = 0;
            try {
                $this->db->select('Amount');
                $this->db->where('OrderID', $order_id_numeric);
                $payment_info = $this->db->get('payment')->row();
                $payment_amount = $payment_info ? (float)($payment_info->Amount ?? 0) : 0;
            } catch (Exception $amount_error) {
                log_message('error', 'Failed to get payment amount: ' . $amount_error->getMessage());
                // Use order total as fallback
                $payment_amount = isset($order->TotalAmount) ? (float)$order->TotalAmount : 0;
            }
            
            // Log activity and create notification (wrap in try-catch to prevent failure)
            try {
                $this->log_activity(
                    'Payment Received',
                    "Payment for Order {$order_id_string} (Amount: ₱" . number_format($payment_amount, 2) . ") has been marked as paid by {$sales_rep_name}.",
                    'Sales Representative',
                    $sales_rep_id,
                    $sales_rep_name,
                    $order_id_numeric,
                    'Payment'
                );
            } catch (Exception $log_error) {
                // Log the error but don't fail the payment update
                log_message('error', 'Failed to log activity for payment: ' . $log_error->getMessage());
            }
            
            // Transaction is handled by update_payment_status, so we don't need to complete it here
            // But we should check if there were any errors
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment marked as paid successfully'
            ]);
        } catch (Exception $e) {
            // Log the error
            $error_message = $e->getMessage();
            $error_trace = $e->getTraceAsString();
            $error_file = $e->getFile();
            $error_line = $e->getLine();
            
            log_message('error', 'Error in mark_payment_paid: ' . $error_message);
            log_message('error', 'File: ' . $error_file . ', Line: ' . $error_line);
            log_message('error', 'Stack trace: ' . $error_trace);
            
            // Check if there's an active transaction and rollback if needed
            // Note: update_payment_status handles its own transaction, but we check just in case
            if ($this->db->trans_status() !== FALSE) {
                $this->db->trans_rollback();
                log_message('error', 'Transaction rolled back');
            }
            
            // Get database error if any
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                log_message('error', 'Database error: ' . $db_error['message'] . ' (Code: ' . ($db_error['code'] ?? 'N/A') . ')');
            }
            
            // Return detailed error in development, generic in production
            $message = (ENVIRONMENT === 'development') 
                ? 'Server error: ' . $error_message . ' (File: ' . basename($error_file) . ', Line: ' . $error_line . ')' 
                : 'An error occurred while processing your request. Please try again.';
            
            // Log the full error details
            log_message('error', 'mark_payment_paid error details: ' . json_encode([
                'error_message' => $error_message,
                'error_file' => $error_file,
                'error_line' => $error_line,
                'order_id' => $order_id ?? 'not set',
                'sales_rep_id' => $sales_rep_id ?? 'not set',
                'db_error' => $db_error,
                'trace' => $error_trace ?? 'not available'
            ]));
            
            // Return error with details for debugging
            $response = [
                'success' => false, 
                'message' => $message,
                'error_details' => (ENVIRONMENT === 'development') ? [
                    'error' => $error_message,
                    'file' => basename($error_file),
                    'line' => $error_line,
                    'db_error' => $db_error
                ] : null
            ];
            
            echo json_encode($response);
        } catch (Error $e) {
            // Catch PHP 7+ errors (fatal errors)
            $error_message = $e->getMessage();
            $error_trace = $e->getTraceAsString();
            log_message('error', 'Fatal error in mark_payment_paid: ' . $error_message);
            log_message('error', 'Stack trace: ' . $error_trace);
            
            // Check if there's an active transaction and rollback if needed
            if ($this->db->trans_status() !== FALSE) {
                $this->db->trans_rollback();
            }
            
            // Return error response
            $message = (ENVIRONMENT === 'development') 
                ? 'Fatal error: ' . $error_message 
                : 'An error occurred while processing your request. Please try again.';
            
            echo json_encode(['success' => false, 'message' => $message]);
            
            log_message('error', 'Fatal error in mark_payment_paid: ' . $error_message);
            log_message('error', 'Stack trace: ' . $error_trace);
            
            $message = (ENVIRONMENT === 'development') 
                ? 'Fatal error: ' . $error_message 
                : 'An error occurred while processing your request. Please try again.';
            
            echo json_encode(['success' => false, 'message' => $message]);
        }
    }
    
    /**
     * Sync customers from user table to enduser table
     * This ensures new customers appear in the End Users page
     */
    private function sync_customers_to_enduser()
    {
        // Get all customers from user table
        $this->db->where('Role', 'Customer');
        $customers = $this->db->get('user')->result();
        
        foreach ($customers as $customer) {
            // Check if customer already exists in enduser table
            $this->db->where('UserID', $customer->UserID);
            $existing = $this->db->get('enduser')->row();
            
            if ($existing) {
                // Update existing record
                $this->db->where('UserID', $customer->UserID);
                $this->db->update('enduser', [
                    'First_Name' => $customer->First_Name,
                    'Last_Name' => $customer->Last_Name,
                    'Middle_Name' => $customer->Middle_Name,
                    'Email' => $customer->Email,
                    'PhoneNum' => $customer->PhoneNum,
                    'Status' => $customer->Status,
                    'Date_Updated' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Insert new record
                $this->db->insert('enduser', [
                    'UserID' => $customer->UserID,
                    'First_Name' => $customer->First_Name,
                    'Last_Name' => $customer->Last_Name,
                    'Middle_Name' => $customer->Middle_Name,
                    'Email' => $customer->Email,
                    'PhoneNum' => $customer->PhoneNum,
                    'Status' => $customer->Status,
                    'Date_Created' => $customer->Date_Created,
                    'Date_Updated' => $customer->Date_Updated
                ]);
            }
        }
    }

    /**
     * Extract preferred installation date from special instructions
     * 
     * @param string $special_instructions Special instructions text
     * @return string|null Date in Y-m-d format or null
     */
    private function extract_preferred_installation_date($special_instructions)
    {
        if (empty($special_instructions)) {
            return null;
        }
        
        // Look for "Preferred Installation Date: [date]" pattern
        if (preg_match('/Preferred Installation Date:\s*([^|]+)/i', $special_instructions, $matches)) {
            $date_str = trim($matches[1]);
            // Try to parse the date (format: "F j, Y" like "January 15, 2025")
            $parsed_date = date_parse($date_str);
            if ($parsed_date && $parsed_date['error_count'] == 0) {
                // Reconstruct date in Y-m-d format
                $year = $parsed_date['year'];
                $month = str_pad($parsed_date['month'], 2, '0', STR_PAD_LEFT);
                $day = str_pad($parsed_date['day'], 2, '0', STR_PAD_LEFT);
                return $year . '-' . $month . '-' . $day;
            }
        }
        
        return null;
    }
}
