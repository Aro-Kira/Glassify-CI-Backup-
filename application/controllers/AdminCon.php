<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminCon extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('User_model');
        
        // Check if user is logged in and has Admin role
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            $this->session->set_flashdata('error', 'Access denied. You must be logged in as an Admin.');
            redirect(base_url('Adlog'));
        }
    }
    
    // Dashboard
    public function admin_dashboard()
    {
        // Get dashboard statistics
        $data['stats'] = $this->get_dashboard_stats();
        
        $data['title'] = "Glassify - Dashboard";
        $data['active'] = 'dashboard';
        $data['content_view'] = 'admin_page/admin_dashboard';
        $data['page_css'] = 'admin_css/admin_dashboard.css';
        $this->load->view('admin_page/layout', $data);
    }
    
    /**
     * Get dashboard statistics
     * Returns: total orders this month, pending orders, weekly sales
     */
    private function get_dashboard_stats()
    {
        $stats = [];
        
        // 1. Total Orders (This Month Only)
        $this->db->where('MONTH(OrderDate)', date('m'));
        $this->db->where('YEAR(OrderDate)', date('Y'));
        $stats['total_orders_month'] = $this->db->count_all_results('order');
        
        // 2. Pending Orders (from order table where Status = 'Pending')
        $this->db->where('Status', 'Pending');
        $stats['pending_orders'] = $this->db->count_all_results('order');
        
        // 3. Weekly Sales (This Week - approved orders by sales rep)
        // Get start and end of current week (Monday to Sunday)
        // Calculate Monday of current week
        $current_date = date('Y-m-d');
        $day_of_week = date('N', strtotime($current_date)); // 1 = Monday, 7 = Sunday
        $days_to_monday = $day_of_week - 1; // Days to subtract to get Monday
        
        $week_start = date('Y-m-d', strtotime($current_date . ' -' . $days_to_monday . ' days')) . ' 00:00:00';
        $week_end = date('Y-m-d', strtotime($week_start . ' +6 days')) . ' 23:59:59';
        
        // Debug: Check what dates we're looking for
        $stats['debug_week_start'] = $week_start;
        $stats['debug_week_end'] = $week_end;
        
        // Sum TotalQuotation from approved_orders where Approved_Date (or Created_Date if Approved_Date is NULL) is within this week
        // Use COALESCE to handle NULL Approved_Date - fall back to Created_Date
        $query = $this->db->query("
            SELECT SUM(TotalQuotation) as TotalQuotation 
            FROM approved_orders 
            WHERE COALESCE(Approved_Date, Created_Date) >= ? 
            AND COALESCE(Approved_Date, Created_Date) <= ?
        ", [$week_start, $week_end]);
        
        $result = $query->row();
        $stats['weekly_sales'] = $result && $result->TotalQuotation ? floatval($result->TotalQuotation) : 0;
        
        return $stats;
    }

    // Orders
    public function admin_orders()
    {
        $data['title'] = "Glassify - Orders";
        $data['active'] = 'orders';
        $data['content_view'] = 'admin_page/admin_orders';
        $data['page_css'] = 'admin_css/admin_orders.css';
        $this->load->view('admin_page/layout', $data);
    }

    // Appointments
    public function admin_appointment()
    {
        $data['title'] = "Glassify - Appointments";
        $data['active'] = 'appointment';
        $data['content_view'] = 'admin_page/admin_appointment';
        $data['page_css'] = 'admin_css/admin_appointment.css';
        $this->load->view('admin_page/layout', $data);
    }
    
    /**
     * Get appointments from approved orders
     * Creates appointment records automatically for approved orders that don't have appointments yet
     */
    public function get_appointments_ajax()
    {
        header('Content-Type: application/json');
        
        $status_filter = $this->input->get('status');
        $service_filter = $this->input->get('service');
        $search = $this->input->get('search');
        $date_filter = $this->input->get('date');
        
        // First, ensure all approved orders have appointment records
        $this->sync_approved_orders_to_appointments();
        
        // Build query
        $this->db->select('a.*, ao.ProductName, ao.OrderID as ApprovedOrderID');
        $this->db->from('appointments a');
        $this->db->join('approved_orders ao', 'a.OrderID = ao.OrderID', 'left');
        
        // Apply status filter (In Progress, Complete, Cancelled)
        if ($status_filter && $status_filter !== 'all' && $status_filter !== 'All Statuses') {
            $this->db->where('a.Status', $status_filter);
        }
        
        // Apply service filter (Order Placed, Ocular Visit, In Fabrication, Installed, Completed)
        if ($service_filter && $service_filter !== 'all' && $service_filter !== 'All Services') {
            $this->db->where('a.Service', $service_filter);
        }
        
        // Apply search filter (searches client name, product name, or order ID)
        if ($search && trim($search) !== '') {
            $this->db->group_start();
            $this->db->like('a.ClientName', $search);
            $this->db->or_like('a.ProductName', $search);
            $this->db->or_like('a.OrderID', $search);
            $this->db->or_like('a.AssignedStaff', $search);
            $this->db->group_end();
        }
        
        // Apply date filter
        if ($date_filter && trim($date_filter) !== '') {
            $this->db->where('a.AppointmentDate', $date_filter);
        }
        
        $this->db->order_by('a.AppointmentDate', 'ASC');
        $this->db->order_by('a.AppointmentTime', 'ASC');
        
        $appointments = $this->db->get()->result();
        
        // Format response
        $formatted_appointments = [];
        foreach ($appointments as $index => $apt) {
            // Format date and time
            $date_time = 'N/A';
            if ($apt->AppointmentDate) {
                $date_str = date('m/d/Y', strtotime($apt->AppointmentDate));
                $time_str = $apt->AppointmentTime ? date('g:i A', strtotime($apt->AppointmentTime)) : '';
                $date_time = $date_str . ($time_str ? ' – ' . $time_str : '');
            }
            
            // Map service to tag class
            $service_class = $this->map_service_to_class($apt->Service);
            
            // Map status to display
            $status_display = $this->map_appointment_status_to_display($apt->Status);
            $status_class = $this->map_appointment_status_to_class($apt->Status);
            
            // Ensure appointment_date is in YYYY-MM-DD format for calendar
            $appointment_date_formatted = null;
            if ($apt->AppointmentDate) {
                // If it's already a date string, format it
                $appointment_date_formatted = date('Y-m-d', strtotime($apt->AppointmentDate));
            }
            
            $formatted_appointments[] = [
                'id' => $apt->AppointmentID,
                'order_id' => $apt->OrderID,
                'client' => $apt->ClientName ?: 'N/A',
                'product' => $apt->ProductName ?: 'N/A',
                'service' => $apt->Service,
                'service_class' => $service_class,
                'date_time' => $date_time,
                'appointment_date' => $appointment_date_formatted, // Ensure YYYY-MM-DD format
                'appointment_time' => $apt->AppointmentTime,
                'assigned_staff' => $apt->AssignedStaff ?: 'N/A',
                'status' => $status_display,
                'status_class' => $status_class,
                'notes' => $apt->Notes ?: ''
            ];
        }
        
        echo json_encode([
            'success' => true,
            'appointments' => $formatted_appointments,
            'total' => count($formatted_appointments)
        ]);
    }
    
    /**
     * Extract preferred installation date from SpecialInstructions
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
    
    /**
     * Sync approved orders to appointments table
     * Creates appointment records for approved orders that don't have appointments yet
     */
    private function sync_approved_orders_to_appointments()
    {
        // Get all approved orders
        $this->db->select('ao.OrderID, ao.Customer_ID, ao.ProductName, u.First_Name, u.Last_Name, u.Middle_Name');
        $this->db->from('approved_orders ao');
        $this->db->join('customer c', 'ao.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $approved_orders = $this->db->get()->result();
        
        foreach ($approved_orders as $order) {
            // Get SpecialInstructions from order table to extract preferred date
            $order_id_numeric = (int)str_replace(['GI', 'gi'], '', $order->OrderID);
            $this->db->select('SpecialInstructions');
            $this->db->where('OrderID', $order_id_numeric);
            $order_record = $this->db->get('order')->row();
            $special_instructions = $order_record ? ($order_record->SpecialInstructions ?? '') : '';
            
            // Extract preferred installation date from SpecialInstructions
            $preferred_date = $this->extract_preferred_installation_date($special_instructions);
            
            // Use preferred installation date if available, otherwise default to today
            $appointment_date = $preferred_date ?: date('Y-m-d');
            
            // Check if appointment already exists
            $this->db->where('OrderID', $order->OrderID);
            $existing = $this->db->get('appointments')->row();
            
            if (!$existing) {
                // Create appointment record with "Order Placed" status
                $client_name = trim(($order->First_Name ?? '') . ' ' . ($order->Middle_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
                
                $appointment_data = [
                    'OrderID' => $order->OrderID,
                    'Customer_ID' => $order->Customer_ID,
                    'ProductName' => $order->ProductName,
                    'ClientName' => $client_name,
                    'Service' => 'Order Placed',
                    'Status' => 'In Progress',
                    'AppointmentDate' => $appointment_date, // Use preferred installation date
                    'AppointmentTime' => '10:00:00' // Default time
                ];
                
                $this->db->insert('appointments', $appointment_data);
            } else {
                // Update existing appointment if it has NULL date or needs preferred date update
                if (empty($existing->AppointmentDate) || ($preferred_date && $existing->AppointmentDate !== $preferred_date)) {
                    $update_data = [];
                    if (empty($existing->AppointmentDate)) {
                        $update_data['AppointmentDate'] = $appointment_date;
                    }
                    if ($preferred_date && $existing->AppointmentDate !== $preferred_date) {
                        // Update to preferred date if it's different
                        $update_data['AppointmentDate'] = $preferred_date;
                    }
                    if (!empty($update_data)) {
                        $this->db->where('AppointmentID', $existing->AppointmentID);
                        $this->db->update('appointments', $update_data);
                    }
                }
            }
        }
    }
    
    /**
     * Get appointment details for edit modal
     */
    public function get_appointment_details_ajax()
    {
        header('Content-Type: application/json');
        
        $appointment_id = $this->input->get('appointment_id');
        if (!$appointment_id) {
            echo json_encode(['success' => false, 'message' => 'Appointment ID required']);
            return;
        }
        
        $this->db->where('AppointmentID', $appointment_id);
        $appointment = $this->db->get('appointments')->row();
        
        if (!$appointment) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }
        
        // Format date for input field (YYYY-MM-DD)
        $appointment_date = $appointment->AppointmentDate ? date('Y-m-d', strtotime($appointment->AppointmentDate)) : '';
        $appointment_time = $appointment->AppointmentTime ? date('H:i', strtotime($appointment->AppointmentTime)) : '';
        
        echo json_encode([
            'success' => true,
            'appointment' => [
                'id' => $appointment->AppointmentID,
                'order_id' => $appointment->OrderID,
                'product' => $appointment->ProductName ?: 'N/A',
                'client' => $appointment->ClientName ?: 'N/A',
                'service' => $appointment->Service,
                'date' => $appointment_date,
                'time' => $appointment_time,
                'assigned_staff' => $appointment->AssignedStaff ?: '',
                'status' => $appointment->Status,
                'notes' => $appointment->Notes ?: ''
            ]
        ]);
    }
    
    /**
     * Update appointment details
     */
    public function update_appointment_ajax()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $appointment_id = $this->input->post('appointment_id');
        $client_name = $this->input->post('client_name');
        $service = $this->input->post('service');
        $date = $this->input->post('date');
        $time = $this->input->post('time');
        $assigned_staff = $this->input->post('assigned_staff');
        $status = $this->input->post('status');
        $notes = $this->input->post('notes');
        
        if (!$appointment_id) {
            echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
            return;
        }
        
        // Validate service
        $valid_services = ['Order Placed', 'Ocular Visit', 'In Fabrication', 'Installed', 'Completed'];
        if (!in_array($service, $valid_services)) {
            echo json_encode(['success' => false, 'message' => 'Invalid service']);
            return;
        }
        
        // Validate status
        $valid_statuses = ['In Progress', 'Complete', 'Cancelled'];
        if (!in_array($status, $valid_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            return;
        }
        
        // Prepare update data
        $update_data = [
            'ClientName' => $client_name,
            'Service' => $service,
            'AppointmentDate' => $date ?: null,
            'AppointmentTime' => $time ?: null,
            'AssignedStaff' => $assigned_staff,
            'Status' => $status,
            'Notes' => $notes,
            'Updated_Date' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('AppointmentID', $appointment_id);
        if ($this->db->update('appointments', $update_data)) {
            echo json_encode([
                'success' => true,
                'message' => 'Appointment updated successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update appointment']);
        }
    }
    
    /**
     * Helper method to map service to CSS class
     */
    private function map_service_to_class($service)
    {
        $class_map = [
            'Order Placed' => 'blue',
            'Ocular Visit' => 'orange',
            'In Fabrication' => 'purple',
            'Installed' => 'teal',
            'Completed' => 'green'
        ];
        
        return $class_map[$service] ?? 'blue';
    }
    
    /**
     * Helper method to map appointment status to display format
     */
    private function map_appointment_status_to_display($status)
    {
        $status_map = [
            'In Progress' => 'In Progress',
            'Complete' => 'Complete',
            'Cancelled' => 'Cancelled'
        ];
        
        return $status_map[$status] ?? $status;
    }
    
    /**
     * Helper method to map appointment status to CSS class
     */
    private function map_appointment_status_to_class($status)
    {
        $class_map = [
            'In Progress' => 'yellow',
            'Complete' => 'green',
            'Cancelled' => 'red'
        ];
        
        return $class_map[$status] ?? 'yellow';
    }

    // Employees
    public function admin_employee()
    {
        $data['title'] = "Glassify - Employees";
        $data['active'] = 'employee';
        $data['content_view'] = 'admin_page/admin_employee';
        $data['page_css'] = 'admin_css/admin_employee.css';
        $this->load->view('admin_page/layout', $data);
    }

    // End Users
    public function admin_endUser()
    {
        $data['title'] = "Glassify - End Users";
        $data['active'] = 'endUser';
        $data['content_view'] = 'admin_page/admin_endUser';
        $data['page_css'] = 'admin_css/admin_endUser.css';
        $this->load->view('admin_page/layout', $data);
    }

    // Inventory
    public function admin_inventory()
    {
        $data['title'] = "Glassify - Inventory";
        $data['active'] = 'inventory';
        $data['content_view'] = 'admin_page/admin_inventory';
        $data['page_css'] = 'admin_css/admin_inventory.css';
        $this->load->view('admin_page/layout', $data);
    }

    // Products
   public function admin_product()
{
    $this->load->model('Product_model');
    
    $data['title'] = "Glassify - Products";
    $data['active'] = 'product';
    $data['content_view'] = 'admin_page/admin_product';
    $data['page_css'] = 'admin_css/admin_product.css';

    // Fetch products from the DB
    $data['products'] = $this->Product_model->get_products();

    $this->load->view('admin_page/layout', $data);
}


    // Payments
    public function admin_payments()
    {
        $data['title'] = "Glassify - Payments";
        $data['active'] = 'payments';
        $data['content_view'] = 'admin_page/admin_payments';
        $data['page_css'] = 'admin_css/admin_payments.css';
        $this->load->view('admin_page/layout', $data);
    }

    // Reports
    public function admin_reports()
    {
        $data['title'] = "Glassify - Reports";
        $data['active'] = 'reports';
        $data['content_view'] = 'admin_page/admin_reports';
        $data['page_css'] = 'admin_css/admin_reports.css';
        $this->load->view('admin_page/layout', $data);
    }

    // Account
    public function admin_account()
    {
        // Get logged-in Admin's UserID
        $user_id = $this->session->userdata('user_id');
        
        // Get Admin's information from database
        $admin = $this->User_model->get_by_id($user_id);
        
        if (!$admin) {
            $this->session->set_flashdata('error', 'User information not found.');
            redirect(base_url('admin-dashboard'));
        }
        
        // Pass Admin's information to view
        $data['admin'] = $admin;
        
        $data['title'] = "Glassify - Account Management";
        $data['active'] = 'account';
        $data['content_view'] = 'admin_page/admin_account';
        $data['page_css'] = 'admin_css/admin_accounts.css';
        $this->load->view('admin_page/layout', $data);
    }

    /**
     * Update admin account information via AJAX
     */
    public function update_account()
    {
        header('Content-Type: application/json');
        
        // Check if user is authenticated
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
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

        if (empty($field)) {
            echo json_encode(['success' => false, 'message' => 'Field name is required']);
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
            }
            
            $update_data[$field] = $value;
        }

        // Update account using User_model
        if ($this->User_model->update_account($user_id, $update_data)) {
            echo json_encode([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' updated successfully',
                'value' => $field === 'Password' ? '************' : $value
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update account. Please try again.']);
        }
    }

    // ======================
    // AJAX ENDPOINTS FOR ORDERS
    // ======================

    /**
     * Get all orders from database with filtering
     */
    public function get_orders_ajax()
    {
        header('Content-Type: application/json');
        
        $status_filter = $this->input->get('status');
        $search = $this->input->get('search');
        $month = $this->input->get('month'); // 0-11
        $year = $this->input->get('year');
        $page = $this->input->get('page') ?: 1;
        $limit = $this->input->get('limit') ?: 10;
        $offset = ($page - 1) * $limit;
        
        // Build query
        $this->db->select('o.OrderID, o.OrderDate, o.TotalAmount, o.Status, o.PaymentStatus, o.DeliveryAddress, c.Customer_ID, u.First_Name, u.Last_Name, u.Middle_Name');
        $this->db->from('order o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        
        // Apply status filter
        if ($status_filter && $status_filter !== 'all orders') {
            switch ($status_filter) {
                case 'completed':
                    $this->db->where('o.Status', 'Completed');
                    break;
                case 'pending':
                    $this->db->where('o.Status', 'Pending');
                    break;
                case 'cancel':
                    $this->db->where('o.Status', 'Cancelled');
                    break;
            }
        }
        
        // Apply search filter
        if ($search) {
            $this->db->group_start();
            $this->db->like('o.OrderID', $search);
            $this->db->or_like('o.DeliveryAddress', $search);
            $this->db->group_end();
        }
        
        // Apply date filter
        if ($month !== null && $month !== '' && $year) {
            $this->db->where('MONTH(o.OrderDate)', $month + 1); // JavaScript months are 0-11, MySQL is 1-12
            $this->db->where('YEAR(o.OrderDate)', $year);
        }
        
        // Get total count before pagination
        $total_count = $this->db->count_all_results('', false);
        
        // Apply pagination
        $this->db->limit($limit, $offset);
        $this->db->order_by('o.OrderDate', 'DESC');
        
        $orders = $this->db->get()->result();
        
        // Format response
        $formatted_orders = [];
        foreach ($orders as $order) {
            // Format OrderID - base order table uses integer, but we need to check order_page for formatted ID
            $order_id_formatted = '#' . $order->OrderID;
            
            // Try to get formatted OrderID from order_page (format: GI001)
            $this->db->select('OrderID, ProductName');
            // Convert integer OrderID to GI format for lookup
            $order_id_gi = 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT);
            $this->db->where('OrderID', $order_id_gi);
            $order_page = $this->db->get('order_page')->row();
            
            if ($order_page) {
                $order_id_formatted = '#' . $order_page->OrderID;
                $product_name = $order_page->ProductName;
            } else {
                // Fallback: try to get product name from customization
                $product_name = 'N/A';
                if ($order->Customer_ID) {
                    $this->db->select('p.ProductName');
                    $this->db->from('customization c');
                    $this->db->join('product p', 'c.Product_ID = p.Product_ID', 'left');
                    $this->db->where('c.Customer_ID', $order->Customer_ID);
                    $this->db->order_by('c.Created_Date', 'DESC');
                    $this->db->limit(1);
                    $custom = $this->db->get()->row();
                    if ($custom) {
                        $product_name = $custom->ProductName;
                    }
                }
            }
            
            // Format date
            $order_date = date('d/m/Y', strtotime($order->OrderDate));
            
            // Format address (truncate if long)
            $address = $order->DeliveryAddress ?: 'N/A';
            if (strlen($address) > 20) {
                $address = substr($address, 0, 17) . '...';
            }
            
            // Map status to display format
            $status_display = $this->map_status_to_display($order->Status);
            $status_class = $this->map_status_to_class($order->Status);
            
            $formatted_orders[] = [
                'order_id' => $order_id_formatted,
                'product_name' => $product_name,
                'address' => $address,
                'date' => $order_date,
                'price' => number_format($order->TotalAmount, 2, '.', ''),
                'status' => $status_display,
                'status_class' => $status_class,
                'full_address' => $order->DeliveryAddress,
                'customer_name' => trim(($order->First_Name ?? '') . ' ' . ($order->Middle_Name ?? '') . ' ' . ($order->Last_Name ?? ''))
            ];
        }
        
        echo json_encode([
            'orders' => $formatted_orders,
            'total' => $total_count,
            'page' => (int)$page,
            'limit' => (int)$limit,
            'total_pages' => ceil($total_count / $limit)
        ]);
    }

    /**
     * Get order details for popup
     */
    public function get_order_details_ajax()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->get('order_id');
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            return;
        }
        
        // Remove # prefix if present and handle GI format
        $order_id_clean = str_replace('#', '', $order_id);
        
        // Check if it's in GI format (e.g., GI001) or numeric
        $is_gi_format = preg_match('/^GI\d+$/i', $order_id_clean);
        
        if ($is_gi_format) {
            // Extract numeric part for base order table lookup
            $order_id_numeric = (int)str_replace('GI', '', $order_id_clean);
        } else {
            // Assume it's numeric
            $order_id_numeric = (int)$order_id_clean;
            $order_id_clean = 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
        }
        
        // Get order from order table (using numeric ID)
        $this->db->select('o.*, c.Customer_ID, u.First_Name, u.Last_Name, u.Middle_Name, u.Email, u.PhoneNum');
        $this->db->from('order o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->where('o.OrderID', $order_id_numeric);
        $order = $this->db->get()->row();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Get order details from order_page table
        $this->db->where('OrderID', $order_id_clean);
        $order_page = $this->db->get('order_page')->row();
        
        // Get customization details if available
        $customization = null;
        if ($order->Customer_ID) {
            $this->db->where('Customer_ID', $order->Customer_ID);
            $this->db->order_by('Created_Date', 'DESC');
            $this->db->limit(1);
            $customization = $this->db->get('customization')->row();
        }
        
        // Format response
        $response = [
            'success' => true,
            'order' => [
                'order_id' => '#' . $order_id_clean,
                'product_name' => $order_page ? $order_page->ProductName : 'N/A',
                'address' => $order->DeliveryAddress ?: 'N/A',
                'date' => date('d/m/Y', strtotime($order->OrderDate)),
                'status' => $this->map_status_to_display($order->Status),
                'total_quotation' => number_format($order->TotalAmount, 2, '.', ''),
                'customer_name' => trim(($order->First_Name ?? '') . ' ' . ($order->Middle_Name ?? '') . ' ' . ($order->Last_Name ?? '')),
                'customer_email' => $order->Email ?? 'N/A',
                'customer_phone' => $order->PhoneNum ?? 'N/A',
                'shape' => $order_page ? ($order_page->Shape ?: 'N/A') : ($customization ? ($customization->GlassShape ?: 'N/A') : 'N/A'),
                'dimension' => $order_page ? ($order_page->Dimension ?: 'N/A') : ($customization ? ($customization->Dimensions ?: 'N/A') : 'N/A'),
                'type' => $order_page ? ($order_page->Type ?: 'N/A') : ($customization ? ($customization->GlassType ?: 'N/A') : 'N/A'),
                'thickness' => $order_page ? ($order_page->Thickness ?: 'N/A') : ($customization ? ($customization->GlassThickness ?: 'N/A') : 'N/A'),
                'edge_work' => $order_page ? ($order_page->EdgeWork ?: 'N/A') : ($customization ? ($customization->EdgeWork ?: 'N/A') : 'N/A'),
                'frame_type' => $order_page ? ($order_page->FrameType ?: 'N/A') : ($customization ? ($customization->FrameType ?: 'N/A') : 'N/A'),
                'engraving' => $order_page ? ($order_page->Engraving ?: 'N/A') : ($customization ? ($customization->Engraving ?: 'N/A') : 'N/A'),
                'file_attached' => $order_page ? ($order_page->FileAttached ?: 'N/A') : 'N/A',
                'special_instructions' => $order->SpecialInstructions ?: 'N/A',
                'preferred_installation_date' => $this->extract_preferred_installation_date($order->SpecialInstructions ?? '') ?: 'N/A'
            ]
        ];
        
        echo json_encode($response);
    }

    /**
     * Get orders awaiting admin approval
     */
    public function get_awaiting_approval_orders()
    {
        header('Content-Type: application/json');
        
        $this->db->select('a.*, u.First_Name as SalesRep_First_Name, u.Last_Name as SalesRep_Last_Name, u.Middle_Name as SalesRep_Middle_Name, c.Customer_ID, cu.First_Name as Customer_First_Name, cu.Last_Name as Customer_Last_Name, cu.Middle_Name as Customer_Middle_Name');
        $this->db->from('awaiting_admin_orders a');
        $this->db->join('user u', 'a.RequestedBy_SalesRep_ID = u.UserID', 'left');
        $this->db->join('customer c', 'a.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user cu', 'c.UserID = cu.UserID', 'left');
        $this->db->order_by('a.Requested_Date', 'DESC');
        
        $orders = $this->db->get()->result();
        
        $formatted_orders = [];
        foreach ($orders as $order) {
            // Use Requested_Date as Scheduled Date for now
            $scheduled_date = $order->Requested_Date ? date('d/m/Y', strtotime($order->Requested_Date)) : 'N/A';
            
            $formatted_orders[] = [
                'id' => $order->AwaitingOrderID,
                'order_id' => '#' . $order->OrderID,
                'scheduled_date' => $scheduled_date,
                'price' => number_format($order->TotalQuotation, 2, '.', ''),
                'product_name' => $order->ProductName,
                'address' => $order->Address,
                'sales_rep_name' => trim(($order->SalesRep_First_Name ?? '') . ' ' . ($order->SalesRep_Middle_Name ?? '') . ' ' . ($order->SalesRep_Last_Name ?? '')),
                'customer_name' => trim(($order->Customer_First_Name ?? '') . ' ' . ($order->Customer_Middle_Name ?? '') . ' ' . ($order->Customer_Last_Name ?? ''))
            ];
        }
        
        echo json_encode($formatted_orders);
    }

    /**
     * Get order details for approval review popup
     */
    public function get_approval_order_details()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->get('order_id');
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            return;
        }
        
        // Remove # prefix if present
        $order_id_clean = str_replace('#', '', $order_id);
        
        // Get order from awaiting_admin_orders
        $this->db->where('OrderID', $order_id_clean);
        $order = $this->db->get('awaiting_admin_orders')->row();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Get sales rep info
        $sales_rep = $this->User_model->get_by_id($order->RequestedBy_SalesRep_ID);
        $sales_rep_name = $sales_rep ? trim($sales_rep->First_Name . ' ' . $sales_rep->Last_Name) : 'N/A';
        
        // Get customer info
        $customer_name = 'N/A';
        if ($order->Customer_ID) {
            $this->db->select('u.First_Name, u.Last_Name, u.Middle_Name');
            $this->db->from('customer c');
            $this->db->join('user u', 'c.UserID = u.UserID', 'left');
            $this->db->where('c.Customer_ID', $order->Customer_ID);
            $customer = $this->db->get()->row();
            if ($customer) {
                $customer_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Middle_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
            }
        }
        
        // Get SpecialInstructions from order table to extract preferred installation date
        $order_id_numeric = (int)str_replace('GI', '', $order_id_clean);
        $this->db->select('SpecialInstructions');
        $this->db->where('OrderID', $order_id_numeric);
        $order_record = $this->db->get('order')->row();
        $special_instructions = $order_record ? ($order_record->SpecialInstructions ?? '') : '';
        $preferred_installation_date = $this->extract_preferred_installation_date($special_instructions);
        
        $response = [
            'success' => true,
            'order' => [
                'order_id' => '#' . $order->OrderID,
                'product_name' => $order->ProductName ?: 'N/A',
                'address' => $order->Address ?: 'N/A',
                'date' => $order->OrderDate ? date('d/m/Y', strtotime($order->OrderDate)) : 'N/A',
                'scheduled_date' => $order->Requested_Date ? date('d/m/Y', strtotime($order->Requested_Date)) : 'N/A',
                'status' => 'Awaiting Admin',
                'total_quotation' => number_format($order->TotalQuotation, 2, '.', ''),
                'customer_name' => $customer_name,
                'sales_rep_name' => $sales_rep_name,
                'shape' => $order->Shape ?: 'N/A',
                'dimension' => $order->Dimension ?: 'N/A',
                'type' => $order->Type ?: 'N/A',
                'thickness' => $order->Thickness ?: 'N/A',
                'edge_work' => $order->EdgeWork ?: 'N/A',
                'frame_type' => $order->FrameType ?: 'N/A',
                'engraving' => $order->Engraving ?: 'N/A',
                'file_attached' => $order->FileAttached ?: 'N/A',
                'requested_date' => $order->Requested_Date ? date('d/m/Y H:i', strtotime($order->Requested_Date)) : 'N/A',
                'preferred_installation_date' => $preferred_installation_date ? date('F j, Y', strtotime($preferred_installation_date)) : 'N/A'
            ]
        ];
        
        echo json_encode($response);
    }

    /**
     * Admin approves order - moves from awaiting_admin_orders to ready_to_approve_orders
     */
    public function approve_order_admin()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->post('order_id');
        $admin_notes = $this->input->post('admin_notes') ?: '';
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Remove # prefix if present and handle GI format
        $order_id_clean = str_replace('#', '', $order_id);
        
        // Ensure it's in GI format (e.g., GI001)
        if (!preg_match('/^GI\d+$/i', $order_id_clean)) {
            // If numeric, convert to GI format
            $order_id_numeric = (int)$order_id_clean;
            $order_id_clean = 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
        }
        
        // Get admin ID
        $admin_id = $this->session->userdata('user_id');
        $admin = $this->User_model->get_by_id($admin_id);
        $admin_name = $admin ? trim($admin->First_Name . ' ' . $admin->Last_Name) : 'Admin';
        
        // Start transaction
        $this->db->trans_start();
        
        // Get order from awaiting_admin_orders
        $this->db->where('OrderID', $order_id_clean);
        $order = $this->db->get('awaiting_admin_orders')->row();
        
        if (!$order) {
            $this->db->trans_rollback();
            echo json_encode(['success' => false, 'message' => 'Order not found in awaiting approval']);
            return;
        }
        
        // Insert into ready_to_approve_orders
        $ready_data = [
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
            'AdminStatus' => 'Approved',
            'AdminNotes' => $admin_notes,
            'AdminReviewed_Date' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('ready_to_approve_orders', $ready_data);
        
        // Delete from awaiting_admin_orders
        $this->db->where('OrderID', $order_id_clean);
        $this->db->delete('awaiting_admin_orders');
        
        // Update order_page status if exists
        $this->db->where('OrderID', $order_id_clean);
        $this->db->update('order_page', ['Status' => 'Ready to Approve']);
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Failed to approve order']);
            return;
        }
        
        // Log activity
        $this->log_activity(
            'Order Approved by Admin',
            "Order {$order_id_clean} has been approved by {$admin_name}",
            'Admin',
            $admin_id,
            $admin_name,
            $order_id_clean,
            'Order'
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Order approved successfully. Sales representative will be notified.',
            'order_id' => $order_id_clean
        ]);
    }

    /**
     * Admin disapproves order - moves from awaiting_admin_orders to disapproved_orders
     */
    public function disapprove_order_admin()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->post('order_id');
        $disapproval_reason = $this->input->post('disapproval_reason') ?: 'Order disapproved by Admin';
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Remove # prefix if present and handle GI format
        $order_id_clean = str_replace('#', '', $order_id);
        
        // Ensure it's in GI format (e.g., GI001)
        if (!preg_match('/^GI\d+$/i', $order_id_clean)) {
            // If numeric, convert to GI format
            $order_id_numeric = (int)$order_id_clean;
            $order_id_clean = 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
        }
        
        // Get admin ID
        $admin_id = $this->session->userdata('user_id');
        $admin = $this->User_model->get_by_id($admin_id);
        $admin_name = $admin ? trim($admin->First_Name . ' ' . $admin->Last_Name) : 'Admin';
        
        // Start transaction
        $this->db->trans_start();
        
        // Get order from awaiting_admin_orders
        $this->db->where('OrderID', $order_id_clean);
        $order = $this->db->get('awaiting_admin_orders')->row();
        
        if (!$order) {
            $this->db->trans_rollback();
            echo json_encode(['success' => false, 'message' => 'Order not found in awaiting approval']);
            return;
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
            'DisapprovedBy' => 'Admin',
            'DisapprovedBy_ID' => $admin_id,
            'DisapprovalReason' => $disapproval_reason,
            'Disapproved_Date' => date('Y-m-d H:i:s'),
            'CustomerNotified' => 0
        ];
        
        $this->db->insert('disapproved_orders', $disapproved_data);
        
        // Delete from awaiting_admin_orders
        $this->db->where('OrderID', $order_id_clean);
        $this->db->delete('awaiting_admin_orders');
        
        // Update order_page status if exists
        $this->db->where('OrderID', $order_id_clean);
        $this->db->update('order_page', ['Status' => 'Disapproved']);
        
        // Update base order table status if exists (convert GI format to numeric)
        $order_id_numeric = (int)str_replace('GI', '', $order_id_clean);
        $this->db->where('OrderID', $order_id_numeric);
        $this->db->update('order', ['Status' => 'Cancelled']);
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Failed to disapprove order']);
            return;
        }
        
        // Log activity
        $this->log_activity(
            'Order Disapproved by Admin',
            "Order {$order_id_clean} has been disapproved by {$admin_name}. Reason: {$disapproval_reason}",
            'Admin',
            $admin_id,
            $admin_name,
            $order_id_clean,
            'Order'
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Order disapproved. Sales representative and customer will be notified.',
            'order_id' => $order_id_clean
        ]);
    }

    /**
     * Helper method to map database status to display format
     */
    private function map_status_to_display($status)
    {
        $status_map = [
            'Pending' => 'Pending',
            'Approved' => 'Confirmed',
            'Completed' => 'Completed',
            'Cancelled' => 'Canceled',
            'In Fabrication' => 'In Progress',
            'Ready for Installation' => 'In Progress',
            'Returned' => 'Returned'
        ];
        
        return $status_map[$status] ?? $status;
    }

    /**
     * Helper method to map database status to CSS class
     */
    private function map_status_to_class($status)
    {
        $class_map = [
            'Pending' => 'pending',
            'Approved' => 'completed',
            'Completed' => 'completed',
            'Cancelled' => 'canceled',
            'In Fabrication' => 'pending',
            'Ready for Installation' => 'pending',
            'Returned' => 'canceled'
        ];
        
        return $class_map[$status] ?? 'pending';
    }

    /**
     * Log activity to system_activity_log
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
    }

  
}
