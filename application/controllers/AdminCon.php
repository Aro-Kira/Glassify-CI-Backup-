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
        
        // If a logged-in customer tries to access admin pages, force logout and redirect
        if ($this->session->userdata('is_logged_in') && $this->session->userdata('user_role') === 'Customer') {
            // Set error message BEFORE clearing session data (flashdata needs active session)
            $this->session->set_flashdata('error', '⚠️ Access Denied: This page is restricted to Admin employees only. Customer accounts cannot access employee pages. You have been logged out for security reasons.');
            
            // Clear all user session data (but keep session alive for flashdata)
            $this->session->unset_userdata(['is_logged_in', 'user_id', 'user_name', 'user_email', 'user_role', 'customer_id']);
            
            // Set cache control headers to prevent back button access after force logout
            $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
            $this->output->set_header('Pragma: no-cache');
            $this->output->set_header('Expires: 0');
            
            redirect(base_url('login'));
        }
        
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
        
        // Get today's appointments
        $data['today_appointments'] = $this->get_today_appointments();
        
        // Get recent activities
        $data['recent_activities'] = $this->get_recent_activities();
        
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
        
        // 2. Pending Orders (from order table where Status = 'Pending Review')
        $this->db->where('Status', 'Pending Review');
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
        
        // Sum TotalAmount from order table where Status='Approved' and Approved_Date (or Created_Date if Approved_Date is NULL) is within this week
        // Use COALESCE to handle NULL Approved_Date - fall back to Created_Date
        $query = $this->db->query("
            SELECT SUM(TotalAmount) as TotalQuotation 
            FROM `order` 
            WHERE Status = 'Approved'
            AND COALESCE(Approved_Date, Created_Date) >= ? 
            AND COALESCE(Approved_Date, Created_Date) <= ?
        ", [$week_start, $week_end]);
        
        $result = $query->row();
        $stats['weekly_sales'] = $result && $result->TotalQuotation ? floatval($result->TotalQuotation) : 0;
        
        return $stats;
    }
    
    /**
     * Get today's appointments from appointments table
     * Returns: array of appointments scheduled for today
     */
    private function get_today_appointments()
    {
        $today = date('Y-m-d');
        
        $this->db->select('a.AppointmentID, a.AppointmentTime, a.Service, a.ClientName, a.Status, a.AssignedStaff');
        $this->db->from('appointments a');
        $this->db->where('a.AppointmentDate', $today);
        $this->db->where('a.Status !=', 'Cancelled');
        $this->db->order_by('a.AppointmentTime', 'ASC');
        $this->db->limit(10); // Limit to 10 appointments
        
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * Get recent activities from system_activity_log
     * Returns: array of recent activities (last 10)
     */
    private function get_recent_activities()
    {
        $this->db->select('Action, Description, Role, UserName, Timestamp');
        $this->db->from('system_activity_log');
        $this->db->order_by('Timestamp', 'DESC');
        $this->db->limit(10); // Get last 10 activities
        
        $query = $this->db->get();
        return $query->result();
    }

    // Orders
    public function admin_orders()
    {
        // Determine active state based on type parameter
        $type = $this->input->get('type');
        if ($type == 'direct') {
            $data['active'] = 'direct-orders';
        } elseif ($type == 'site-assessed') {
            $data['active'] = 'site-assessed-orders';
        } else {
            $data['active'] = 'orders'; // Default fallback
        }
        
        $data['title'] = "Glassify - Orders";
        $data['content_view'] = 'admin_page/admin_orders';
        $data['page_css'] = 'admin_css/admin_orders.css';
        $this->load->view('admin_page/layout', $data);
    }

    // Appointments
    public function admin_appointment()
    {
        // Determine active state based on type parameter
        $type = $this->input->get('type');
        if ($type == 'ocular') {
            $data['active'] = 'ocular-appointment';
        } elseif ($type == 'installation') {
            $data['active'] = 'installation-appointment';
        } else {
            $data['active'] = 'appointment'; // Default fallback
        }
        
        $data['title'] = "Glassify - Appointments";
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
        
        try {
            $status_filter = $this->input->get('status');
            $service_filter = $this->input->get('service');
            $appointment_type = $this->input->get('appointment_type'); // 'ocular' or 'installation'
            $search = $this->input->get('search');
            $client_search = $this->input->get('client_search');
            $date_filter = $this->input->get('date');
            $staff_filter = $this->input->get('staff');
            $ocular_completed = $this->input->get('ocular_completed'); // 'yes' or 'no'
            $page = $this->input->get('page') ?: 1;
            $limit = $this->input->get('limit') ?: 10;
            $offset = ($page - 1) * $limit;
            
            // First, ensure all approved orders have appointment records
            // Reset query builder before sync to avoid conflicts
            $this->db->reset_query();
            try {
                $this->sync_approved_orders_to_appointments();
            } catch (Exception $e) {
                // Log error but don't fail the request
                log_message('error', 'AdminCon::get_appointments_ajax - Sync error: ' . $e->getMessage());
            }
            // Reset again after sync to ensure clean state
            $this->db->reset_query();
        
        // Build base query structure (from, joins) - without select or group_by yet
        $this->db->from('appointments a');
        $this->db->join('`order` o', 'a.OrderID = o.OrderID', 'left');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        
        // Apply appointment type filter (ocular or installation)
        if ($appointment_type) {
            if ($appointment_type === 'ocular') {
                $this->db->where('a.Service', 'Ocular Visit');
            } elseif ($appointment_type === 'installation') {
                $this->db->where('a.Service', 'Installed');
            }
        }
        
        // Apply status filter (In Progress, Complete, Cancelled)
        if ($status_filter && $status_filter !== 'all' && $status_filter !== 'All Statuses') {
            $this->db->where('a.Status', $status_filter);
        }
        
        // Apply service filter (Order Placed, Ocular Visit, In Fabrication, Installed, Completed)
        if ($service_filter && $service_filter !== 'all' && $service_filter !== 'All Services') {
            $this->db->where('a.Service', $service_filter);
        }
        
        // Apply client search filter
        if ($client_search && trim($client_search) !== '') {
            $this->db->like('a.ClientName', $client_search);
        }
        
        // Apply search filter (searches client name, product name, order ID, or order number) - legacy support
        if ($search && trim($search) !== '' && !$client_search) {
            $this->db->group_start();
            $this->db->like('a.ClientName', $search);
            $this->db->or_like('a.ProductName', $search);
            // If search looks like an order number (GI001), search by OrderNumber
            if (preg_match('/^GI\d+$/i', $search)) {
                $this->db->or_like('o.OrderNumber', $search);
            } else {
                // Otherwise, try numeric OrderID
                if (is_numeric($search)) {
                    $this->db->or_where('a.OrderID', (int)$search);
                } else {
                    $this->db->or_like('a.OrderID', $search);
                }
            }
            $this->db->or_like('a.AssignedStaff', $search);
            $this->db->group_end();
        }
        
        // Apply staff filter
        if ($staff_filter && $staff_filter !== 'all') {
            $this->db->like('a.AssignedStaff', $staff_filter);
        }
        
        // Apply date filter
        if ($date_filter && trim($date_filter) !== '') {
            $this->db->where('a.AppointmentDate', $date_filter);
        }
        
        // Apply ocular completed filter (for ocular appointments)
        if ($appointment_type === 'ocular' && $ocular_completed && $ocular_completed !== 'all') {
            // Check if order has OcularCompleted flag or if appointment status is Complete
            if ($ocular_completed === 'yes') {
                $this->db->where('a.Status', 'Complete');
            } elseif ($ocular_completed === 'no') {
                $this->db->where('a.Status !=', 'Complete');
            }
        }
        
        // Get total count - use a simpler approach to avoid query builder conflicts
        // Build count query separately
        $this->db->select('COUNT(DISTINCT a.AppointmentID) as total', false);
        $count_result = $this->db->get()->row();
        $total_count = $count_result ? (int)$count_result->total : 0;
        
        // Check for database errors in count query
        $db_error = $this->db->error();
        if (!empty($db_error['message'])) {
            log_message('error', 'AdminCon::get_appointments_ajax - Count query DB error: ' . $db_error['message']);
            $total_count = 0; // Will use result count as fallback
        }
        
        // Reset query builder for data retrieval
        $this->db->reset_query();
        $this->db->select('a.*, p.ProductName, o.OrderID, o.OrderNumber as ApprovedOrderID, o.OrderType');
        $this->db->from('appointments a');
        $this->db->join('`order` o', 'a.OrderID = o.OrderID', 'left');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->group_by('a.AppointmentID');
        
        // Reapply all filters
        if ($appointment_type) {
            if ($appointment_type === 'ocular') {
                $this->db->where('a.Service', 'Ocular Visit');
            } elseif ($appointment_type === 'installation') {
                $this->db->where('a.Service', 'Installed');
            }
        }
        if ($status_filter && $status_filter !== 'all' && $status_filter !== 'All Statuses') {
            $this->db->where('a.Status', $status_filter);
        }
        if ($service_filter && $service_filter !== 'all' && $service_filter !== 'All Services') {
            $this->db->where('a.Service', $service_filter);
        }
        if ($client_search && trim($client_search) !== '') {
            $this->db->like('a.ClientName', $client_search);
        }
        if ($search && trim($search) !== '' && !$client_search) {
            $this->db->group_start();
            $this->db->like('a.ClientName', $search);
            $this->db->or_like('a.ProductName', $search);
            if (preg_match('/^GI\d+$/i', $search)) {
                $this->db->or_like('o.OrderNumber', $search);
            } else {
                if (is_numeric($search)) {
                    $this->db->or_where('a.OrderID', (int)$search);
                } else {
                    $this->db->or_like('a.OrderID', $search);
                }
            }
            $this->db->or_like('a.AssignedStaff', $search);
            $this->db->group_end();
        }
        if ($staff_filter && $staff_filter !== 'all') {
            $this->db->like('a.AssignedStaff', $staff_filter);
        }
        if ($date_filter && trim($date_filter) !== '') {
            $this->db->where('a.AppointmentDate', $date_filter);
        }
        if ($appointment_type === 'ocular' && $ocular_completed && $ocular_completed !== 'all') {
            if ($ocular_completed === 'yes') {
                $this->db->where('a.Status', 'Complete');
            } elseif ($ocular_completed === 'no') {
                $this->db->where('a.Status !=', 'Complete');
            }
        }
        
        // Apply pagination and ordering
        $this->db->limit($limit, $offset);
        $this->db->order_by('a.AppointmentDate', 'ASC');
        $this->db->order_by('a.AppointmentTime', 'ASC');
        
        $appointments = $this->db->get()->result();
        
        // Check for database errors in data query
        $db_error = $this->db->error();
        if (!empty($db_error['message'])) {
            log_message('error', 'AdminCon::get_appointments_ajax - Data query DB error: ' . $db_error['message']);
            throw new Exception('Database error: ' . $db_error['message']);
        }
        
        // Fallback: if count query failed, use result count
        if ($total_count === 0 && !empty($appointments)) {
            $total_count = count($appointments);
        }
        
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
                'order_number' => $apt->ApprovedOrderID ?? ($apt->OrderNumber ?? 'N/A'),
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
                'notes' => $apt->Notes ?: '',
                'ocular_notes' => $apt->OcularNotes ?? '',
                'installation_notes' => $apt->InstallationNotes ?? ''
            ];
        }
        
        echo json_encode([
            'success' => true,
            'appointments' => $formatted_appointments,
            'total' => $total_count ?? count($formatted_appointments),
            'page' => (int)($page ?? 1),
            'limit' => (int)($limit ?? 10),
            'total_pages' => isset($total_count) ? ceil($total_count / ($limit ?? 10)) : 1
        ]);
        } catch (Exception $e) {
            log_message('error', 'AdminCon::get_appointments_ajax - Exception: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_appointments_ajax - Stack trace: ' . $e->getTraceAsString());
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                log_message('error', 'AdminCon::get_appointments_ajax - DB Error: ' . $db_error['message']);
            }
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading appointments: ' . $e->getMessage(),
                'appointments' => [],
                'total' => 0
            ]);
        } catch (Error $e) {
            log_message('error', 'AdminCon::get_appointments_ajax - Fatal Error: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_appointments_ajax - Stack trace: ' . $e->getTraceAsString());
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                log_message('error', 'AdminCon::get_appointments_ajax - DB Error: ' . $db_error['message']);
            }
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Fatal error loading appointments: ' . $e->getMessage(),
                'appointments' => [],
                'total' => 0
            ]);
        }
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
        // Get all approved orders from unified order table
        $this->db->select('o.OrderID, o.OrderNumber, o.Customer_ID, o.SpecialInstructions, u.First_Name, u.Last_Name, u.Middle_Name, p.ProductName');
        $this->db->from('`order` o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->where('o.Status', 'Approved');
        $this->db->group_by('o.OrderID'); // Group to avoid duplicates from multiple order_items
        $approved_orders = $this->db->get()->result();
        
        foreach ($approved_orders as $order) {
            // Get SpecialInstructions from order record (already selected above)
            $special_instructions = $order->SpecialInstructions ?? '';
            
            // Extract preferred installation date from SpecialInstructions
            $preferred_date = $this->extract_preferred_installation_date($special_instructions);
            
            // Use preferred installation date if available, otherwise default to today
            $appointment_date = $preferred_date ?: date('Y-m-d');
            
            // Check if appointment already exists (use OrderID integer)
            $this->db->reset_query();
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
                
                $this->db->reset_query();
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
                        $this->db->reset_query();
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
        
        // Get appointment to find OrderID and previous status
        $this->db->where('AppointmentID', $appointment_id);
        $appointment = $this->db->get('appointments')->row();
        
        if (!$appointment) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }
        
        $order_id = $appointment->OrderID;
        $previous_status = $appointment->Status; // Store previous status to detect reversals
        
        // Start transaction
        $this->db->trans_start();
        
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
        $this->db->update('appointments', $update_data);
        
        // Get current order status
        $this->db->where('OrderID', $order_id);
        $current_order = $this->db->get('`order`')->row();
        
        // Sync appointment changes to order table
        $order_update = [];
        
        // Check if status is being reverted from Complete to something else
        $is_reverting = ($previous_status === 'Complete' && $status !== 'Complete');
        
        // Update order dates based on appointment service and status
        if ($service === 'Ocular Visit' && $date) {
            $order_update['OcularDate'] = $date;
        } elseif ($service === 'In Fabrication' && $date) {
            $order_update['FabricationDate'] = $date;
        } elseif ($service === 'Installed' && $date) {
            $order_update['InstallationDate'] = $date;
        } elseif ($service === 'Completed' && $status === 'Complete') {
            // When appointment is marked as Completed and status is Complete, update order status
            $order_update['Status'] = 'Completed';
            if ($date) {
                $order_update['EstimatedDelivery'] = $date;
            }
        }
        
        // Handle status changes (both forward and reverse)
        if ($status === 'Complete') {
            // Moving forward: Complete the step
            switch ($service) {
                case 'Order Placed':
                    // Order Placed complete - typically doesn't change order status
                    // Order status should remain as 'Approved' or whatever it was
                    break;
                case 'Ocular Visit':
                    // Ocular visit complete - order can move to In Fabrication
                    if ($current_order && $current_order->Status === 'Approved') {
                        $order_update['Status'] = 'In Fabrication';
                    }
                    break;
                case 'In Fabrication':
                    // Fabrication complete - order can move to Ready for Installation
                    $order_update['Status'] = 'Ready for Installation';
                    break;
                case 'Installed':
                    // Installation complete - order can move to Completed
                    $order_update['Status'] = 'Completed';
                    break;
            }
        } elseif ($is_reverting) {
            // Reverting from Complete: Cascade backwards step-by-step
            // When reverting a step, automatically revert all later steps that depend on it
            $services_order = ['Order Placed', 'Ocular Visit', 'In Fabrication', 'Installed', 'Completed'];
            $current_service_index = array_search($service, $services_order);
            
            // Get all appointments for this order to check dependencies
            $all_appointments = $this->db->where('OrderID', $order_id)
                                         ->get('appointments')
                                         ->result();
            
            // Step 1: Revert all later steps that are complete (cascade backwards)
            if ($current_service_index !== false) {
                $later_services = array_slice($services_order, $current_service_index + 1);
                
                foreach ($later_services as $later_service) {
                    // Find appointments for this later service
                    foreach ($all_appointments as $apt) {
                        if ($apt->Service === $later_service && $apt->Status === 'Complete') {
                            // Revert this later step to 'In Progress' (or 'Cancelled' if that's what was selected)
                            $revert_status = ($status === 'Cancelled') ? 'Cancelled' : 'In Progress';
                            $this->db->where('AppointmentID', $apt->AppointmentID)
                                     ->update('appointments', [
                                         'Status' => $revert_status,
                                         'Updated_Date' => date('Y-m-d H:i:s')
                                     ]);
                        }
                    }
                    
                    // Also handle projectschedule table for "In Fabrication" service
                    if ($later_service === 'In Fabrication' && $this->db->table_exists('projectschedule')) {
                        $fabrication_project = $this->db->where('OrderID', $order_id)
                                                       ->get('projectschedule')
                                                       ->row();
                        if ($fabrication_project && $fabrication_project->Status === 'Completed') {
                            // Revert fabrication project status
                            $revert_project_status = ($status === 'Cancelled') ? 'Delayed' : 'In progress';
                            $this->db->where('Schedule_ID', $fabrication_project->Schedule_ID)
                                     ->update('projectschedule', [
                                         'Status' => $revert_project_status
                                     ]);
                        }
                    }
                }
            }
            
            // Step 2: Rollback order status based on the reverted step
            switch ($service) {
                case 'Order Placed':
                    // Order Placed completion doesn't typically change order status from 'Approved'
                    // So reverting it shouldn't change order status either
                    break;
                case 'Ocular Visit':
                    // If reverting ocular visit, check if any later steps are still complete
                    $has_later_complete = $this->db->where('OrderID', $order_id)
                                                   ->where('AppointmentID !=', $appointment_id)
                                                   ->where_in('Service', ['In Fabrication', 'Installed', 'Completed'])
                                                   ->where('Status', 'Complete')
                                                   ->get('appointments')
                                                   ->num_rows() > 0;
                    if (!$has_later_complete && $current_order && $current_order->Status === 'In Fabrication') {
                        $order_update['Status'] = 'Approved';
                    }
                    break;
                case 'In Fabrication':
                    // If reverting fabrication, also revert projectschedule if it exists
                    if ($this->db->table_exists('projectschedule')) {
                        $fabrication_project = $this->db->where('OrderID', $order_id)
                                                       ->get('projectschedule')
                                                       ->row();
                        if ($fabrication_project && $fabrication_project->Status === 'Completed') {
                            // Revert fabrication project status
                            $revert_project_status = ($status === 'Cancelled') ? 'Delayed' : 'In progress';
                            $this->db->where('Schedule_ID', $fabrication_project->Schedule_ID)
                                     ->update('projectschedule', [
                                         'Status' => $revert_project_status
                                     ]);
                        }
                    }
                    
                    // If reverting fabrication, check if installation is still complete
                    $installed_complete = $this->db->where('OrderID', $order_id)
                                                  ->where('AppointmentID !=', $appointment_id)
                                                  ->where('Service', 'Installed')
                                                  ->where('Status', 'Complete')
                                                  ->get('appointments')
                                                  ->num_rows() > 0;
                    if (!$installed_complete && $current_order && $current_order->Status === 'Ready for Installation') {
                        // Revert to In Fabrication status (since ocular should be complete)
                        $order_update['Status'] = 'In Fabrication';
                    }
                    break;
                case 'Installed':
                    // If reverting installation, check if completed is still done
                    $completed_done = $this->db->where('OrderID', $order_id)
                                              ->where('AppointmentID !=', $appointment_id)
                                              ->where('Service', 'Completed')
                                              ->where('Status', 'Complete')
                                              ->get('appointments')
                                              ->num_rows() > 0;
                    if (!$completed_done && $current_order && $current_order->Status === 'Completed') {
                        $order_update['Status'] = 'Ready for Installation';
                    }
                    break;
                case 'Completed':
                    // If reverting completed status, order should go back to Ready for Installation
                    $other_completed = $this->db->where('OrderID', $order_id)
                                               ->where('AppointmentID !=', $appointment_id)
                                               ->where('Service', 'Completed')
                                               ->where('Status', 'Complete')
                                               ->get('appointments')
                                               ->num_rows() > 0;
                    if (!$other_completed && $current_order && $current_order->Status === 'Completed') {
                        $order_update['Status'] = 'Ready for Installation';
                    }
                    break;
            }
        }
        
        // Update order table if there are changes
        if (!empty($order_update)) {
            $this->db->where('OrderID', $order_id);
            $this->db->update('`order`', $order_update);
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Failed to update appointment']);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Appointment updated successfully',
                'order_updated' => !empty($order_update)
            ]);
        }
    }
    
    /**
     * Delete appointment
     */
    public function delete_appointment_ajax()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $appointment_id = $this->input->post('appointment_id');
        
        if (!$appointment_id) {
            echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
            return;
        }
        
        // Get appointment to verify it exists
        $this->db->where('AppointmentID', $appointment_id);
        $appointment = $this->db->get('appointments')->row();
        
        if (!$appointment) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }
        
        // Start transaction
        $this->db->trans_start();
        
        // Delete the appointment
        $this->db->where('AppointmentID', $appointment_id);
        $this->db->delete('appointments');
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete appointment']);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Appointment deleted successfully'
            ]);
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
        // Load Inventory_model
        $this->load->model('Inventory_model');
        
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
        $this->db->query("UPDATE inventory_items SET Status = 'Out of Stock' WHERE InStock = 0 AND Status != 'Out of Stock' AND (Status != 'New' OR DateAdded < DATE_SUB(NOW(), INTERVAL 2 DAY))");
        $this->db->query("UPDATE inventory_items SET Status = 'Low Stock' WHERE InStock > 0 AND InStock < 10 AND Status != 'Low Stock' AND (Status != 'New' OR DateAdded < DATE_SUB(NOW(), INTERVAL 2 DAY))");
        $this->db->query("UPDATE inventory_items SET Status = 'In Stock' WHERE InStock >= 10 AND Status != 'In Stock' AND (Status != 'New' OR DateAdded < DATE_SUB(NOW(), INTERVAL 2 DAY))");
        
        // Mark items as 'New' if added within last 2 days (only if not already marked)
        $this->db->query("UPDATE inventory_items SET Status = 'New' WHERE DateAdded >= DATE_SUB(NOW(), INTERVAL 2 DAY) AND Status != 'New'");
        
        $data['inventory_items'] = $inventory_items;
        $data['total_items'] = $total_items;
        $data['low_stock_count'] = $low_stock_count;
        $data['out_of_stock_count'] = $out_of_stock_count;
        $data['new_items_count'] = $new_items_count;
        $data['notifications'] = $notifications;
        $data['notification_count'] = count($notifications);
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
    $this->load->model('Inventory_model');
    
    $data['title'] = "Glassify - Products";
    $data['active'] = 'product';
    $data['content_view'] = 'admin_page/admin_product';
    $data['page_css'] = 'admin_css/admin_product.css';

    // Fetch ALL products first (admin needs to see all for management)
    $allProducts = $this->Product_model->get_all_products();
    
    // Update product status based on materials for each product
    foreach ($allProducts as $product) {
        $this->Inventory_model->update_product_status_from_materials($product->Product_ID);
    }
    
    // Reload products - get products that customers can see (In Stock or Low Stock)
    // This ensures admin sees the same products that customers see
    $data['products'] = $this->Product_model->get_products();
    
    // Ensure we have products to display - if filtering removed all products,
    // show all products so admin can manage them
    if (empty($data['products']) && !empty($allProducts)) {
        $data['products'] = $allProducts;
    }

    $this->load->view('admin_page/layout', $data);
}


    // Payments - Show approved orders ready for payment
    public function admin_payments()
    {
        // Get approved orders for all sales reps with payment data
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
        $data['content_view'] = 'admin_page/admin_payments';
        $data['page_css'] = 'admin_css/admin_payments.css';
        $this->load->view('admin_page/layout', $data);
    }

    /**
     * Get payment details for admin
     * Similar to SalesCon but without SalesRep_ID filtering
     */
    public function get_payment_details()
    {
        // Set JSON header
        header('Content-Type: application/json');
        
        // Check authentication
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
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
                $this->db->where("(o.Status = 'Approved' OR o.Status = 'Pending Review')", NULL, FALSE);
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
     * Mark payment as paid (Admin version)
     * Updates payment status in payment table and order table
     * Admin can mark any order as paid (no SalesRep_ID restriction)
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
            if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
                return;
            }
            
            $order_id = $this->input->post('order_id');
            
            if (!$order_id) {
                echo json_encode(['success' => false, 'message' => 'Order ID is required']);
                return;
            }
            
            // Log the attempt
            log_message('info', 'mark_payment_paid (Admin) called: order_id=' . $order_id);
            
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
                
                // Verify order exists (no SalesRep_ID restriction for admin)
                $this->db->select('OrderID, Status, TotalAmount, PaymentMethod');
                $this->db->where('OrderID', $order_id_numeric);
                $order = $this->db->get('`order`')->row();
            }
            
            if (!$order) {
                throw new Exception('Order not found');
            }
            
            // Ensure we have the numeric OrderID
            if (!$order_id_numeric) {
                $order_id_numeric = $order->OrderID;
            }
            
            // Generate order_id_string for logging
            $order_id_string = 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
            
            // Update payment status using Order_model transaction function
            $this->load->model('Order_model');
            
            log_message('info', 'Attempting to update payment status for OrderID: ' . $order_id_numeric);
            
            try {
                $update_result = $this->Order_model->update_payment_status($order_id_numeric, 'Paid');
                
                if (!$update_result) {
                    $error = $this->db->error();
                    $error_msg = $error['message'] ?? 'Unknown database error';
                    $error_code = $error['code'] ?? 0;
                    log_message('error', 'update_payment_status returned false. Error: ' . $error_msg . ' (Code: ' . $error_code . ')');
                    log_message('error', 'Order ID: ' . $order_id_numeric);
                    
                    if ($this->db->trans_status() === FALSE) {
                        $trans_error = $this->db->error();
                        log_message('error', 'Transaction failed: ' . ($trans_error['message'] ?? 'Unknown transaction error'));
                    }
                    
                    throw new Exception('Failed to update payment status: ' . $error_msg);
                }
                
                log_message('info', 'Payment status updated successfully for OrderID: ' . $order_id_numeric);
            } catch (Exception $update_error) {
                log_message('error', 'Exception in update_payment_status: ' . $update_error->getMessage());
                throw $update_error;
            }
            
            // Deduct materials from inventory after payment
            $this->load->model('Inventory_model');
            $product_id = null;
            $this->db->select('oi.Product_ID');
            $this->db->from('order_items oi');
            $this->db->where('oi.OrderID', $order_id_numeric);
            $this->db->limit(1);
            $order_item = $this->db->get()->row();
            
            if ($order_item && isset($order_item->Product_ID) && $order_item->Product_ID) {
                $product_id = $order_item->Product_ID;
            }
            
            if ($product_id) {
                try {
                    $deduction_result = $this->Inventory_model->deduct_materials_for_order($order_id_numeric, $product_id, 1);
                    
                    if (!$deduction_result['success']) {
                        log_message('error', 'Some materials could not be deducted for order ' . $order_id_string . ': ' . json_encode($deduction_result['out_of_stock_items']));
                    } else {
                        log_message('info', 'Materials deducted successfully for order ' . $order_id_string);
                    }
                } catch (Exception $deduct_error) {
                    log_message('error', 'Failed to deduct materials for order ' . $order_id_string . ': ' . $deduct_error->getMessage());
                }
            }
            
            // Get admin name for logging
            $admin_id = $this->session->userdata('user_id');
            $admin_name = 'Admin';
            try {
                $admin = $this->User_model->get_by_id($admin_id);
                if ($admin && isset($admin->First_Name) && isset($admin->Last_Name)) {
                    $admin_name = trim($admin->First_Name . ' ' . $admin->Last_Name);
                }
            } catch (Exception $user_error) {
                log_message('error', 'Failed to get admin name: ' . $user_error->getMessage());
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
                $payment_amount = isset($order->TotalAmount) ? (float)$order->TotalAmount : 0;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment marked as paid successfully'
            ]);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            log_message('error', 'Error in mark_payment_paid (Admin): ' . $error_message);
            
            if ($this->db->trans_status() !== FALSE) {
                $this->db->trans_rollback();
            }
            
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                log_message('error', 'Database error: ' . $db_error['message']);
            }
            
            $message = (ENVIRONMENT === 'development') 
                ? 'Server error: ' . $error_message 
                : 'An error occurred while processing your request. Please try again.';
            
            echo json_encode([
                'success' => false, 
                'message' => $message
            ]);
        }
    }

    // Reports
    public function admin_reports()
    {
        // Get report statistics from database
        $data['report_stats'] = $this->get_report_statistics();
        
        $data['title'] = "Glassify - Reports";
        $data['active'] = 'reports';
        $data['content_view'] = 'admin_page/admin_reports';
        $data['page_css'] = 'admin_css/admin_reports.css';
        $this->load->view('admin_page/layout', $data);
    }
    
    /**
     * Get report statistics from database
     * Returns: total sales, total orders, average order value, refunds
     */
    private function get_report_statistics()
    {
        $stats = [];
        
        // 1. Total Sales - Sum of TotalAmount from orders where PaymentStatus = 'Paid'
        $this->db->select_sum('TotalAmount');
        $this->db->where('PaymentStatus', 'Paid');
        $query = $this->db->get('order');
        $result = $query->row();
        $stats['total_sales'] = $result && $result->TotalAmount ? floatval($result->TotalAmount) : 0;
        
        // 2. Total Orders - Count of all orders
        // Reset query builder first
        $this->db->reset_query();
        $stats['total_orders'] = $this->db->count_all_results('order');
        
        // 3. Average Order Value - Total Sales / Number of Paid Orders
        // Count paid orders for accurate average
        $this->db->reset_query();
        $this->db->where('PaymentStatus', 'Paid');
        $paid_orders_count = $this->db->count_all_results('order');
        
        if ($paid_orders_count > 0) {
            $stats['avg_order_value'] = $stats['total_sales'] / $paid_orders_count;
        } else {
            $stats['avg_order_value'] = 0;
        }
        
        // 4. Refunds - Count of orders where PaymentStatus = 'Refunded' OR Status = 'Cancelled'
        $this->db->group_start();
        $this->db->where('PaymentStatus', 'Refunded');
        $this->db->or_where('Status', 'Cancelled');
        $this->db->group_end();
        $stats['refunds'] = $this->db->count_all_results('order');
        
        return $stats;
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

    // Issues/Support
    public function admin_issues()
    {
        $this->load->model('Issue_model');
        $data['title'] = "Glassify - Issues/Support";
        $data['active'] = 'issues';
        $data['content_view'] = 'admin_page/admin_issues';
        $data['page_css'] = 'admin_css/admin_issues.css';
        $this->load->view('admin_page/layout', $data);
    }

    // ===================== ISSUE/SUPPORT API ENDPOINTS =====================

    /**
     * Get all issues (AJAX endpoint)
     */
    public function get_issues_ajax()
    {
        $this->load->model('Issue_model');
        
        $filters = [
            'status' => $this->input->get('status') ?: 'Open',
            'priority' => $this->input->get('priority'),
            'category' => $this->input->get('category'),
            'search' => $this->input->get('search')
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
        $this->load->model('Issue_model');
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
        
        // Build file URL if attachment exists
        $file_url = null;
        $file_attached = $issue->FileAttached ?? null;
        if ($file_attached && $file_attached !== 'N/A') {
            if (strpos($file_attached, 'uploads/') === 0) {
                $file_url = base_url($file_attached);
            } else {
                $file_url = base_url('uploads/' . $file_attached);
            }
        }
        
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
                'report_date' => $issue->Report_Date,
                'file_attached' => $file_attached,
                'file_url' => $file_url
            ]
        ]);
    }

    /**
     * Mark issue as resolved (AJAX endpoint)
     */
    public function mark_resolved_ajax()
    {
        $this->load->model('Issue_model');
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
        $this->load->model('Issue_model');
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
        
        try {
            $status_filter = $this->input->get('status');
        $order_type = $this->input->get('order_type'); // 'direct' or 'site-assessed'
        $search = $this->input->get('search');
        $client_search = $this->input->get('client_search');
        $order_search = $this->input->get('order_search');
        $date_start = $this->input->get('date_start');
        $date_end = $this->input->get('date_end');
        $month_year = $this->input->get('month_year'); // Format: YYYY-MM
        $ocular_status = $this->input->get('ocular_status'); // For site-assessed orders
        $month = $this->input->get('month'); // 0-11 (legacy)
        $year = $this->input->get('year'); // (legacy)
        $page = $this->input->get('page') ?: 1;
        $limit = $this->input->get('limit') ?: 10;
        $offset = ($page - 1) * $limit;
        
        // Build count query first (simple, no joins to avoid duplication)
        $this->db->reset_query();
        $this->db->from('`order` o');
        
        // Apply order type filter (if OrderType column exists)
        if ($order_type) {
            if ($this->db->field_exists('OrderType', 'order')) {
                if ($order_type === 'direct') {
                    $this->db->where('(o.OrderType = "Direct" OR o.OrderType IS NULL)');
                } elseif ($order_type === 'site-assessed') {
                    $this->db->where('o.OrderType', 'Site-Assessed');
                }
            } else {
                // Fallback: If OrderType doesn't exist, we can't filter by type
                // This allows the system to work even if the column hasn't been added yet
            }
        }
        
        // Apply status filter
        if ($status_filter && $status_filter !== 'all' && $status_filter !== 'all orders') {
            $this->db->where('o.Status', $status_filter);
        }
        
        // Apply ocular status filter for site-assessed orders
        if ($order_type === 'site-assessed' && $ocular_status && $ocular_status !== 'all') {
            // This would require checking appointments table for ocular completion
            // For now, we'll implement a basic check if OcularCompleted field exists
            if ($this->db->field_exists('OcularCompleted', 'order')) {
                if ($ocular_status === 'completed') {
                    $this->db->where('o.OcularCompleted', 1);
                } elseif ($ocular_status === 'pending') {
                    $this->db->where('(o.OcularCompleted = 0 OR o.OcularCompleted IS NULL)');
                }
            }
        }
        
        // Apply date range filter
        if ($date_start) {
            $this->db->where('o.OrderDate >=', $date_start . ' 00:00:00');
        }
        if ($date_end) {
            $this->db->where('o.OrderDate <=', $date_end . ' 23:59:59');
        }
        
        // Apply month/year filter (new format)
        if ($month_year) {
            $parts = explode('-', $month_year);
            if (count($parts) == 2) {
                $this->db->where('MONTH(o.OrderDate)', (int)$parts[1]);
                $this->db->where('YEAR(o.OrderDate)', (int)$parts[0]);
            }
        }
        
        // Apply client search (name, email, phone)
        // For count query, we'll skip client search filter to avoid join complexity
        // The main query will handle it properly with joins
        // This is acceptable since client search is typically used with the main query anyway
        
        // Apply order number search
        if ($order_search) {
            $this->db->group_start();
            $this->db->like('o.OrderNumber', $order_search);
            $this->db->or_like('o.OrderID', $order_search);
            $this->db->group_end();
        }
        
        // Apply general search filter for count (legacy support)
        if ($search && !$client_search && !$order_search) {
            $this->db->group_start();
            $this->db->like('o.OrderNumber', $search);
            $this->db->or_like('o.OrderID', $search);
            $this->db->or_like('o.DeliveryAddress', $search);
            // For product name search, use subquery
            $this->db->or_where("EXISTS (SELECT 1 FROM order_items oi2 JOIN product p2 ON oi2.Product_ID = p2.Product_ID WHERE oi2.OrderID = o.OrderID AND p2.ProductName LIKE '%" . $this->db->escape_like_str($search) . "%')", null, false);
            $this->db->group_end();
        }
        
        // Apply legacy date filter
        if ($month !== null && $month !== '' && $year && !$month_year) {
            $this->db->where('MONTH(o.OrderDate)', $month + 1);
            $this->db->where('YEAR(o.OrderDate)', $year);
        }
        
        // Get total count
        // Use get_compiled_select to debug if needed
        $total_count = $this->db->count_all_results();
        
        // Check for database errors
        $db_error = $this->db->error();
        if (!empty($db_error['code'])) {
            log_message('error', 'AdminCon::get_orders_ajax - Count query error: ' . $db_error['message']);
            log_message('error', 'AdminCon::get_orders_ajax - Count query SQL: ' . $this->db->last_query());
            throw new Exception('Database error in count query: ' . $db_error['message']);
        }
        
        // Reset query for data retrieval
        $this->db->reset_query();
        
        // Now build the data query with joins
        $this->db->select('
            o.OrderID, 
            o.OrderNumber,
            o.OrderDate, 
            o.TotalAmount, 
            o.Status, 
            o.PaymentStatus, 
            o.DeliveryAddress,
            o.OrderType,
            c.Customer_ID, 
            u.First_Name, 
            u.Last_Name, 
            u.Middle_Name,
            u.Email,
            u.PhoneNum,
            p.ProductName
        ');
        $this->db->from('`order` o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->group_by('o.OrderID');
        
        // Reapply filters for data query
        if ($order_type) {
            if ($this->db->field_exists('OrderType', 'order')) {
                if ($order_type === 'direct') {
                    $this->db->where('(o.OrderType = "Direct" OR o.OrderType IS NULL)');
                } elseif ($order_type === 'site-assessed') {
                    $this->db->where('o.OrderType', 'Site-Assessed');
                }
            }
        }
        
        if ($status_filter && $status_filter !== 'all' && $status_filter !== 'all orders') {
            $this->db->where('o.Status', $status_filter);
        }
        
        if ($order_type === 'site-assessed' && $ocular_status && $ocular_status !== 'all') {
            if ($this->db->field_exists('OcularCompleted', 'order')) {
                if ($ocular_status === 'completed') {
                    $this->db->where('o.OcularCompleted', 1);
                } elseif ($ocular_status === 'pending') {
                    $this->db->where('(o.OcularCompleted = 0 OR o.OcularCompleted IS NULL)');
                }
            }
        }
        
        if ($date_start) {
            $this->db->where('o.OrderDate >=', $date_start . ' 00:00:00');
        }
        if ($date_end) {
            $this->db->where('o.OrderDate <=', $date_end . ' 23:59:59');
        }
        
        if ($month_year) {
            $parts = explode('-', $month_year);
            if (count($parts) == 2) {
                $this->db->where('MONTH(o.OrderDate)', (int)$parts[1]);
                $this->db->where('YEAR(o.OrderDate)', (int)$parts[0]);
            }
        }
        
        if ($client_search) {
            $this->db->group_start();
            $this->db->like('u.First_Name', $client_search);
            $this->db->or_like('u.Last_Name', $client_search);
            $this->db->or_like('u.Email', $client_search);
            $this->db->or_like('u.PhoneNum', $client_search);
            $this->db->group_end();
        }
        
        if ($order_search) {
            $this->db->group_start();
            $this->db->like('o.OrderNumber', $order_search);
            $this->db->or_like('o.OrderID', $order_search);
            $this->db->group_end();
        }
        
        if ($search && !$client_search && !$order_search) {
            $this->db->group_start();
            $this->db->like('o.OrderNumber', $search);
            $this->db->or_like('o.OrderID', $search);
            $this->db->or_like('o.DeliveryAddress', $search);
            $this->db->or_like('p.ProductName', $search);
            $this->db->group_end();
        }
        
        if ($month !== null && $month !== '' && $year && !$month_year) {
            $this->db->where('MONTH(o.OrderDate)', $month + 1);
            $this->db->where('YEAR(o.OrderDate)', $year);
        }
        
        // Apply pagination
        $this->db->limit($limit, $offset);
        $this->db->order_by('o.OrderDate', 'DESC');
        
        $orders = $this->db->get()->result();
        
        // Format response
        $formatted_orders = [];
        foreach ($orders as $order) {
            // Format OrderID using OrderNumber if available, otherwise format from OrderID
            $order_id_formatted = '#' . ($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT));
            $product_name = $order->ProductName ?? 'N/A';
            
            // Format date
            $order_date = date('d/m/Y', strtotime($order->OrderDate));
            
            // Format address (truncate if long)
            $address = $order->DeliveryAddress ?: 'N/A';
            if (strlen($address) > 20) {
                $address = substr($address, 0, 17) . '...';
            }
            
            // Map status to display format
            // Handle null or empty status
            $order_status = $order->Status ?? 'Pending Review';
            if (empty($order_status) || trim($order_status) === '') {
                $order_status = 'Pending Review';
            }
            $status_display = $this->map_status_to_display($order_status);
            $status_class = $this->map_status_to_class($order_status);
            
            $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Middle_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
            if (empty($customer_name)) {
                $customer_name = 'N/A';
            }
            
            $formatted_order = [
                'order_id' => $order_id_formatted,
                'order_id_raw' => $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT),
                'product_name' => $product_name,
                'address' => $address,
                'date' => $order_date,
                'price' => number_format($order->TotalAmount, 2, '.', ''),
                'status' => $status_display,
                'status_class' => $status_class,
                'status_raw' => $order_status,
                'full_address' => $order->DeliveryAddress,
                'customer_name' => $customer_name,
                'customer_email' => $order->Email ?? '',
                'customer_phone' => $order->PhoneNum ?? ''
            ];
            
            // Add ocular status for site-assessed orders
            if ($order_type === 'site-assessed') {
                $formatted_order['ocular_status'] = 'Pending'; // Default, will be updated if field exists
                if ($this->db->field_exists('OcularCompleted', 'order')) {
                    $formatted_order['ocular_status'] = (isset($order->OcularCompleted) && $order->OcularCompleted) ? 'Completed' : 'Pending';
                }
            }
            
            $formatted_orders[] = $formatted_order;
        }
        
        echo json_encode([
            'orders' => $formatted_orders,
            'total' => $total_count,
            'page' => (int)$page,
            'limit' => (int)$limit,
            'total_pages' => ceil($total_count / $limit)
        ]);
        
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                $error_message .= ' | DB Error: ' . $db_error['message'];
            }
            log_message('error', 'AdminCon::get_orders_ajax - Error: ' . $error_message);
            log_message('error', 'AdminCon::get_orders_ajax - Stack trace: ' . $e->getTraceAsString());
            log_message('error', 'AdminCon::get_orders_ajax - Last query: ' . $this->db->last_query());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading orders: ' . $error_message,
                'orders' => [],
                'total' => 0,
                'page' => 1,
                'limit' => 10,
                'total_pages' => 0
            ]);
        } catch (Error $e) {
            $error_message = $e->getMessage();
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                $error_message .= ' | DB Error: ' . $db_error['message'];
            }
            log_message('error', 'AdminCon::get_orders_ajax - Fatal error: ' . $error_message);
            log_message('error', 'AdminCon::get_orders_ajax - Stack trace: ' . $e->getTraceAsString());
            log_message('error', 'AdminCon::get_orders_ajax - Last query: ' . $this->db->last_query());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Fatal error loading orders: ' . $error_message,
                'orders' => [],
                'total' => 0,
                'page' => 1,
                'limit' => 10,
                'total_pages' => 0
            ]);
        }
    }

    /**
     * Get order details for popup
     * Uses Order_model->get_order_details_for_popup() for normalized data
     */
    public function get_order_details_ajax()
    {
        header('Content-Type: application/json');
        
        try {
            $order_id = $this->input->get('order_id');
            if (!$order_id) {
                echo json_encode(['success' => false, 'message' => 'Order ID required']);
                return;
            }
            
            // Remove # prefix if present
            $order_id_clean = str_replace('#', '', $order_id);
            
            // Load Order_model
            $this->load->model('Order_model');
            
            // Pass the order_id directly to the model - it handles both numeric ID and OrderNumber format
            // The model will look up by OrderNumber (GI001) first if it's a string, then by numeric ID
            $order = $this->Order_model->get_order_details_for_popup($order_id_clean);
        
        if (!$order) {
            // Fallback: Try to get basic order info to see if it exists at all
            $basic_order = null;
            $order_id_numeric = null;
            
            // Try by OrderNumber first (for GI001 format)
            if (preg_match('/^GI\d+$/i', $order_id_clean)) {
                $basic_order = $this->db->where('OrderNumber', $order_id_clean)->get('`order`')->row();
                if ($basic_order) {
                    $order_id_numeric = $basic_order->OrderID;
                }
            }
            
            // If not found, try by numeric ID
            if (!$basic_order && is_numeric($order_id_clean)) {
                $order_id_numeric = (int)$order_id_clean;
                $basic_order = $this->Order_model->get_order($order_id_numeric);
            }
            
            // If still not found, try OrderNumber lookup for any string
            if (!$basic_order) {
                $basic_order = $this->db->where('OrderNumber', $order_id_clean)->get('`order`')->row();
                if ($basic_order) {
                    $order_id_numeric = $basic_order->OrderID;
                }
            }
            
            if ($basic_order) {
                // Order exists but query failed - might be missing order_items or customer data
                log_message('error', 'AdminCon::get_order_details_ajax - Order exists but get_order_details_for_popup returned null. OrderID: ' . $order_id_clean . ' (numeric: ' . $order_id_numeric . ')');
                
                // Get basic order info
                $order = $basic_order;
                if (!$order_id_numeric) {
                $order_id_numeric = $order->OrderID;
                }
                
                // Try to get order items separately
                $order_items = $this->Order_model->get_order_customizations($order_id_numeric);
                if (!empty($order_items)) {
                    $first_item = $order_items[0];
                    $order->ProductName = $first_item->ProductName ?? 'N/A';
                    $order->GlassShape = $first_item->GlassShape ?? 'N/A';
                    $order->Dimensions = $first_item->Dimensions ?? 'N/A';
                    $order->GlassType = $first_item->GlassType ?? 'N/A';
                    $order->GlassThickness = $first_item->GlassThickness ?? 'N/A';
                    $order->EdgeWork = $first_item->EdgeWork ?? 'N/A';
                    $order->FrameType = $first_item->FrameType ?? 'N/A';
                    $order->Engraving = $first_item->Engraving ?? 'N/A';
                    $order->DesignRef = $first_item->DesignRef ?? null;
                } else {
                    // No order items found - set defaults
                    $order->ProductName = 'N/A';
                    $order->GlassShape = 'N/A';
                    $order->Dimensions = 'N/A';
                    $order->GlassType = 'N/A';
                    $order->GlassThickness = 'N/A';
                    $order->EdgeWork = 'N/A';
                    $order->FrameType = 'N/A';
                    $order->Engraving = 'N/A';
                    $order->DesignRef = null;
                }
                
                // Get customer info separately
                $order_with_customer = $this->Order_model->get_order_with_customer($order_id_numeric);
                if ($order_with_customer) {
                    $order->First_Name = $order_with_customer->First_Name ?? '';
                    $order->Middle_Name = $order_with_customer->Middle_Name ?? '';
                    $order->Last_Name = $order_with_customer->Last_Name ?? '';
                    $order->Email = $order_with_customer->Email ?? '';
                    $order->PhoneNum = $order_with_customer->PhoneNum ?? '';
                } else {
                    // No customer info found - set defaults
                    $order->First_Name = '';
                    $order->Middle_Name = '';
                    $order->Last_Name = '';
                    $order->Email = 'N/A';
                    $order->PhoneNum = 'N/A';
                }
            } else {
                // Order doesn't exist at all
                log_message('error', 'AdminCon::get_order_details_ajax - Order not found in database. OrderID: ' . $order_id_clean);
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                return;
            }
        }
        
        // Get customer name
        $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Middle_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
        if (empty($customer_name)) {
            $customer_name = 'N/A';
        }
        
        // Get preferred installation date from SpecialInstructions
        $preferred_installation_date = 'N/A';
        if (isset($order->SpecialInstructions) && !empty($order->SpecialInstructions)) {
            $preferred_date = $this->extract_preferred_installation_date($order->SpecialInstructions);
            if ($preferred_date) {
                $preferred_installation_date = date('F j, Y', strtotime($preferred_date));
            }
        }
        
        // Format order ID
        if (!isset($order->OrderID)) {
            log_message('error', 'AdminCon::get_order_details_ajax - Order object missing OrderID property');
            echo json_encode(['success' => false, 'message' => 'Invalid order data: missing OrderID']);
            return;
        }
        
        $order_id_formatted = '#' . ($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT));
        $order_id_numeric = $order->OrderID;
        
        // Get staff assignments
        $fabrication_staff_name = 'Unassigned';
        $installation_staff_name = 'Unassigned';
        
        if ($this->db->field_exists('FabricationStaff_ID', 'order') && !empty($order->FabricationStaff_ID)) {
            $fab_staff = $this->db->where('UserID', $order->FabricationStaff_ID)->get('user')->row();
            if ($fab_staff) {
                $fabrication_staff_name = trim(($fab_staff->First_Name ?? '') . ' ' . ($fab_staff->Last_Name ?? ''));
            }
        }
        
        if ($this->db->field_exists('InstallationStaff_ID', 'order') && !empty($order->InstallationStaff_ID)) {
            $inst_staff = $this->db->where('UserID', $order->InstallationStaff_ID)->get('user')->row();
            if ($inst_staff) {
                $installation_staff_name = trim(($inst_staff->First_Name ?? '') . ' ' . ($inst_staff->Last_Name ?? ''));
            }
        }
        
        // Get ocular notes and related info (for site-assessed orders)
        $ocular_notes = '';
        $ocular_date = 'N/A';
        $ocular_staff_name = 'N/A';
        $ocular_completed = false;
        
        $ocular_appointment = $this->db->where('OrderID', $order_id_numeric)
                                       ->where('Service', 'Ocular Visit')
                                       ->get('appointments')
                                       ->row();
        
        if ($ocular_appointment) {
            $ocular_notes = $ocular_appointment->OcularNotes ?? '';
            $ocular_date = $ocular_appointment->AppointmentDate ? date('F j, Y', strtotime($ocular_appointment->AppointmentDate)) : 'N/A';
            $ocular_completed = ($ocular_appointment->Status === 'Complete');
            
            if (!empty($ocular_appointment->AssignedStaff)) {
                $ocular_staff_name = $ocular_appointment->AssignedStaff;
            }
        }
        
        // Get order items for the items table
        $order_items = $this->Order_model->get_order_customizations($order_id_numeric);
        $items = [];
        foreach ($order_items as $item) {
            $items[] = [
                'product_name' => $item->ProductName ?? 'N/A',
                'quantity' => $item->Quantity ?? 0,
                'unit_price' => number_format($item->UnitPrice ?? 0, 2, '.', ''),
                'shape' => $item->GlassShape ?? 'N/A',
                'dimension' => $item->Dimensions ?? 'N/A',
                'type' => $item->GlassType ?? 'N/A',
                'thickness' => $item->GlassThickness ?? 'N/A',
                'edge_work' => $item->EdgeWork ?? 'N/A',
                'frame_type' => $item->FrameType ?? 'N/A',
                'engraving' => $item->Engraving ?? 'N/A',
                'design_file' => $item->DesignRef ? base_url($item->DesignRef) : null
            ];
        }
        
        // Get sales rep info
        $sales_rep_name = 'N/A';
        $sales_rep_email = 'N/A';
        $sales_rep_phone = 'N/A';
        $sales_rep_status = 'N/A';
        
        if (!empty($order->SalesRep_ID)) {
            $sales_rep = $this->db->where('UserID', $order->SalesRep_ID)->get('user')->row();
            if ($sales_rep) {
                $sales_rep_name = trim(($sales_rep->First_Name ?? '') . ' ' . ($sales_rep->Last_Name ?? ''));
                $sales_rep_email = $sales_rep->Email ?? 'N/A';
                $sales_rep_phone = $sales_rep->PhoneNum ?? 'N/A';
                $sales_rep_status = $sales_rep->Status ?? 'N/A';
            }
        }
        
        // Get payment info
        $payment = $this->db->where('OrderID', $order_id_numeric)->order_by('Payment_Date', 'DESC')->get('payment')->row();
        $payment_status = $payment ? ($payment->Status ?? 'N/A') : 'N/A';
        $payment_method = $payment ? ($payment->PaymentMethod ?? 'N/A') : 'N/A';
        $payment_date = $payment && $payment->Payment_Date ? date('F j, Y', strtotime($payment->Payment_Date)) : 'N/A';
        
        // Format response
        $response = [
            'success' => true,
            'order' => [
                'order_id' => $order_id_formatted,
                'order_id_raw' => $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT),
                'product_name' => $order->ProductName ?? 'N/A',
                'address' => $order->DeliveryAddress ?? 'N/A',
                'full_address' => $order->DeliveryAddress ?? 'N/A',
                'date' => $order->OrderDate ? date('d/m/Y', strtotime($order->OrderDate)) : 'N/A',
                'status' => $this->map_status_to_display($order->Status ?? 'Pending Review'),
                'status_raw' => $order->Status ?? 'Pending Review',
                'total_quotation' => number_format($order->TotalAmount ?? 0, 2, '.', ''),
                'subtotal' => number_format($order->TotalAmount ?? 0, 2, '.', ''),
                'tax' => '0.00',
                'customer_name' => $customer_name,
                'customer_email' => $order->Email ?? 'N/A',
                'customer_phone' => $order->PhoneNum ?? 'N/A',
                'sales_rep_name' => $sales_rep_name,
                'sales_rep_email' => $sales_rep_email,
                'sales_rep_phone' => $sales_rep_phone,
                'sales_rep_status' => $sales_rep_status,
                'fabrication_staff_name' => $fabrication_staff_name,
                'installation_staff_name' => $installation_staff_name,
                'ocular_notes' => $ocular_notes,
                'ocular_date' => $ocular_date,
                'ocular_staff_name' => $ocular_staff_name,
                'ocular_completed' => $ocular_completed,
                'shape' => $order->GlassShape ?? 'N/A',
                'dimension' => $order->Dimensions ?? 'N/A',
                'type' => $order->GlassType ?? 'N/A',
                'thickness' => $order->GlassThickness ?? 'N/A',
                'edge_work' => $order->EdgeWork ?? 'N/A',
                'frame_type' => $order->FrameType ?? 'N/A',
                'engraving' => $order->Engraving ?? 'N/A',
                'file_attached' => $order->DesignRef ?? 'N/A',
                'file_url' => $order->DesignRef ? base_url($order->DesignRef) : null,
                'special_instructions' => $order->SpecialInstructions ?? 'N/A',
                'preferred_installation_date' => $preferred_installation_date,
                'items' => $items,
                'payment_status' => $payment_status,
                'payment_method' => $payment_method,
                'payment_date' => $payment_date
            ]
        ];
        
        echo json_encode($response);
        } catch (Exception $e) {
            log_message('error', 'AdminCon::get_order_details_ajax - Exception: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_order_details_ajax - Stack trace: ' . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Error loading order details: ' . $e->getMessage()
            ]);
        } catch (Error $e) {
            log_message('error', 'AdminCon::get_order_details_ajax - Fatal Error: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_order_details_ajax - Stack trace: ' . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Fatal error loading order details: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get orders awaiting admin approval
     */
    /**
     * Get awaiting approval orders
     * Uses Order_model->get_awaiting_admin_orders()
     */
    public function get_awaiting_approval_orders()
    {
        header('Content-Type: application/json');
        
        $this->load->model('Order_model');
        $orders = $this->Order_model->get_awaiting_admin_orders();
        
        $formatted_orders = [];
        foreach ($orders as $order) {
            $order_id_formatted = $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT);
            $scheduled_date = $order->OrderDate ? date('d/m/Y', strtotime($order->OrderDate)) : 'N/A';
            
            $formatted_orders[] = [
                'id' => $order->OrderID,
                'order_id' => '#' . $order_id_formatted,
                'scheduled_date' => $scheduled_date,
                'price' => number_format($order->TotalAmount ?? 0, 2, '.', ''),
                'product_name' => $order->ProductName ?? 'N/A',
                'address' => $order->DeliveryAddress ?? 'N/A',
                'sales_rep_name' => trim(($order->SalesRep_First_Name ?? '') . ' ' . ($order->SalesRep_Last_Name ?? '')),
                'customer_name' => trim(($order->Customer_First_Name ?? '') . ' ' . ($order->Customer_Last_Name ?? ''))
            ];
        }
        
        echo json_encode($formatted_orders);
    }

    /**
     * Get order details for approval review popup
     * Uses Order_model->get_approval_order_details() for normalized data
     */
    public function get_approval_order_details()
    {
        header('Content-Type: application/json');
        
        try {
            $order_id = $this->input->get('order_id');
            if (!$order_id) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Order ID required']);
                return;
            }
            
            // Ensure proper URL decoding and remove # prefix if present
            // CodeIgniter's input->get() should decode automatically, but we'll ensure it
            // Handle both # and %23 (URL-encoded #)
            $order_id = urldecode($order_id);
            $order_id = str_replace('%23', '#', $order_id); // Handle case where %23 wasn't decoded
            // Remove # prefix if present
            $order_id_clean = str_replace('#', '', $order_id);
            
            // Load Order_model
            $this->load->model('Order_model');
            
            // Use Order_model function which handles both OrderID and OrderNumber formats
            try {
                $order = $this->Order_model->get_approval_order_details($order_id_clean);
            } catch (Exception $e) {
                log_message('error', 'AdminCon::get_approval_order_details - Error calling model: ' . $e->getMessage());
                log_message('error', 'AdminCon::get_approval_order_details - Stack trace: ' . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error fetching order from database: ' . $e->getMessage()]);
                return;
            } catch (Error $e) {
                log_message('error', 'AdminCon::get_approval_order_details - Fatal error calling model: ' . $e->getMessage());
                log_message('error', 'AdminCon::get_approval_order_details - Stack trace: ' . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Fatal error fetching order from database: ' . $e->getMessage()]);
                return;
            }
            
            // Debug: Log what we got
            if (!$order) {
                log_message('debug', 'AdminCon::get_approval_order_details - Model returned null for: ' . $order_id_clean);
            } else {
                log_message('debug', 'AdminCon::get_approval_order_details - Model returned order with OrderID: ' . (isset($order->OrderID) ? $order->OrderID : 'N/A'));
            }
            
            if (!$order) {
                // Fallback: try to get from awaiting_admin_orders (legacy) if table exists
                if ($this->db->table_exists('awaiting_admin_orders')) {
                    $this->db->where('OrderID', $order_id_clean);
                    $legacy_order = $this->db->get('awaiting_admin_orders')->row();
                    
                    if ($legacy_order) {
                        // Convert legacy format to new format
                        $order_id_numeric = (int)str_replace('GI', '', $order_id_clean);
                        $order = $this->Order_model->get_order_details_for_popup($order_id_numeric);
                    }
                }
                
                // If still not found, try direct order lookup
                if (!$order) {
                    // Try by OrderNumber first
                    if (preg_match('/^GI\d+$/i', $order_id_clean)) {
                        $order = $this->db->where('OrderNumber', $order_id_clean)->get('`order`')->row();
                    }
                    
                    // Try by numeric ID
                    if (!$order && is_numeric($order_id_clean)) {
                        $order = $this->Order_model->get_order((int)$order_id_clean);
                    }
                }
                
                if (!$order) {
                    log_message('error', 'AdminCon::get_approval_order_details - Order not found. OrderID: ' . $order_id_clean);
                    http_response_code(404); // Not Found
                    echo json_encode(['success' => false, 'message' => 'Order not found']);
                    return;
                }
            }
            
            // Validate that order has at least OrderID
            if (!isset($order->OrderID)) {
                log_message('error', 'AdminCon::get_approval_order_details - Order object missing OrderID. OrderID input: ' . $order_id_clean);
                http_response_code(500); // Internal Server Error
                echo json_encode(['success' => false, 'message' => 'Invalid order data returned']);
                return;
            }
            
            // Get sales rep information
            $sales_rep_name = 'N/A';
            $sales_rep_status = 'N/A';
            $sales_rep_email = 'N/A';
            $sales_rep_phone = 'N/A';
            
            if (isset($order->SalesRep_First_Name) && !empty($order->SalesRep_First_Name)) {
                $sales_rep_name = trim(($order->SalesRep_First_Name ?? '') . ' ' . ($order->SalesRep_Last_Name ?? ''));
                if (empty(trim($sales_rep_name))) {
                    $sales_rep_name = 'N/A';
                }
                // Get status from order object if available
                if (isset($order->SalesRep_Status)) {
                    $sales_rep_status = $order->SalesRep_Status;
                }
                if (isset($order->SalesRep_Email)) {
                    $sales_rep_email = $order->SalesRep_Email;
                }
                if (isset($order->SalesRep_Phone)) {
                    $sales_rep_phone = $order->SalesRep_Phone;
                }
            } elseif (isset($order->SalesRep_ID) && !empty($order->SalesRep_ID)) {
                try {
                    $sales_rep = $this->User_model->get_by_id($order->SalesRep_ID);
                    if ($sales_rep) {
                        $sales_rep_name = isset($sales_rep->First_Name) 
                            ? trim($sales_rep->First_Name . ' ' . ($sales_rep->Last_Name ?? '')) 
                            : 'N/A';
                        $sales_rep_status = isset($sales_rep->Status) ? $sales_rep->Status : 'N/A';
                        $sales_rep_email = isset($sales_rep->Email) ? $sales_rep->Email : 'N/A';
                        $sales_rep_phone = isset($sales_rep->PhoneNum) ? $sales_rep->PhoneNum : 'N/A';
                    }
                } catch (Exception $e) {
                    log_message('error', 'AdminCon::get_approval_order_details - Error fetching sales rep: ' . $e->getMessage());
                    $sales_rep_name = 'N/A';
                }
            }
            
            // Get customer name
            $customer_name = 'N/A';
            if (isset($order->Customer_First_Name) && !empty($order->Customer_First_Name)) {
                $customer_name = trim(($order->Customer_First_Name ?? '') . ' ' . ($order->Customer_Middle_Name ?? '') . ' ' . ($order->Customer_Last_Name ?? ''));
                if (empty(trim($customer_name))) {
                    $customer_name = 'N/A';
                }
            } elseif (isset($order->Customer_ID) && !empty($order->Customer_ID)) {
                try {
                    $this->db->select('u.First_Name, u.Last_Name, u.Middle_Name');
                    $this->db->from('customer c');
                    $this->db->join('user u', 'c.UserID = u.UserID', 'left');
                    $this->db->where('c.Customer_ID', $order->Customer_ID);
                    $customer = $this->db->get()->row();
                    if ($customer && isset($customer->First_Name)) {
                        $customer_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Middle_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
                        if (empty(trim($customer_name))) {
                            $customer_name = 'N/A';
                        }
                    }
                } catch (Exception $e) {
                    log_message('error', 'AdminCon::get_approval_order_details - Error fetching customer: ' . $e->getMessage());
                    $customer_name = 'N/A';
                }
            }
            
            // Get preferred installation date from SpecialInstructions
            $preferred_installation_date = 'N/A';
            if (isset($order->SpecialInstructions) && !empty($order->SpecialInstructions)) {
                try {
                    $preferred_date = $this->extract_preferred_installation_date($order->SpecialInstructions);
                    if ($preferred_date) {
                        $preferred_installation_date = date('F j, Y', strtotime($preferred_date));
                    }
                } catch (Exception $e) {
                    log_message('error', 'AdminCon::get_approval_order_details - Error parsing preferred date: ' . $e->getMessage());
                }
            }
            
            // Notes are not stored in awaiting_admin_orders table (column doesn't exist)
            // If notes are needed in the future, they should be stored in the order table or a separate notes table
            $notes = '';
            
            // Format order ID
            $order_id_formatted = '#';
            if (isset($order->OrderNumber) && !empty($order->OrderNumber)) {
                $order_id_formatted .= $order->OrderNumber;
            } elseif (isset($order->OrderID)) {
                $order_id_formatted .= 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT);
            } else {
                $order_id_formatted .= 'N/A';
            }
            
            // Format date safely
            $order_date_formatted = 'N/A';
            if (isset($order->OrderDate) && !empty($order->OrderDate)) {
                try {
                    $order_date_formatted = date('d/m/Y', strtotime($order->OrderDate));
                } catch (Exception $e) {
                    log_message('error', 'AdminCon::get_approval_order_details - Error formatting order date: ' . $e->getMessage());
                    $order_date_formatted = 'N/A';
                }
            }
            
            // Build response array safely
            try {
                $requested_date = 'N/A';
                if (isset($order->OrderDate) && !empty($order->OrderDate)) {
                    try {
                        $requested_date = date('d/m/Y H:i', strtotime($order->OrderDate));
                    } catch (Exception $e) {
                        $requested_date = 'N/A';
                    }
                }
                
                $total_amount = '0.00';
                if (isset($order->TotalAmount)) {
                    try {
                        $total_amount = number_format((float)$order->TotalAmount, 2, '.', '');
                    } catch (Exception $e) {
                        $total_amount = '0.00';
                    }
                }
                
                $response = [
                    'success' => true,
                    'order' => [
                        'order_id' => $order_id_formatted,
                        'product_name' => isset($order->ProductName) ? (string)$order->ProductName : 'N/A',
                        'address' => isset($order->DeliveryAddress) ? (string)$order->DeliveryAddress : 'N/A',
                        'date' => $order_date_formatted,
                        'scheduled_date' => $order_date_formatted,
                        'status' => isset($order->Status) ? (string)$order->Status : 'Awaiting Admin',
                        'total_quotation' => $total_amount,
                        'customer_name' => $customer_name,
                        'customer_email' => isset($order->Customer_Email) ? (string)$order->Customer_Email : 'N/A',
                        'customer_phone' => isset($order->Customer_Phone) ? (string)$order->Customer_Phone : 'N/A',
                        'sales_rep_name' => $sales_rep_name,
                        'sales_rep_status' => $sales_rep_status,
                        'sales_rep_email' => $sales_rep_email,
                        'sales_rep_phone' => $sales_rep_phone,
                        'shape' => isset($order->GlassShape) ? (string)$order->GlassShape : 'N/A',
                        'dimension' => isset($order->Dimensions) ? (string)$order->Dimensions : 'N/A',
                        'type' => isset($order->GlassType) ? (string)$order->GlassType : 'N/A',
                        'thickness' => isset($order->GlassThickness) ? (string)$order->GlassThickness : 'N/A',
                        'edge_work' => isset($order->EdgeWork) ? (string)$order->EdgeWork : 'N/A',
                        'frame_type' => isset($order->FrameType) ? (string)$order->FrameType : 'N/A',
                        'engraving' => isset($order->Engraving) ? (string)$order->Engraving : 'N/A',
                        'file_attached' => isset($order->DesignRef) ? (string)$order->DesignRef : 'N/A',
                        'file_url' => (isset($order->DesignRef) && !empty($order->DesignRef)) ? base_url($order->DesignRef) : null,
                        'requested_date' => $requested_date,
                        'preferred_installation_date' => $preferred_installation_date,
                        'special_instructions' => isset($order->SpecialInstructions) ? (string)$order->SpecialInstructions : '',
                        'notes' => $notes
                    ]
                ];
                
                // Success - return 200 OK
                http_response_code(200);
                echo json_encode($response);
            } catch (Exception $e) {
                log_message('error', 'AdminCon::get_approval_order_details - Error building response: ' . $e->getMessage());
                log_message('error', 'AdminCon::get_approval_order_details - Stack trace: ' . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error building response: ' . $e->getMessage()]);
            } catch (Error $e) {
                log_message('error', 'AdminCon::get_approval_order_details - Fatal error building response: ' . $e->getMessage());
                log_message('error', 'AdminCon::get_approval_order_details - Stack trace: ' . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Fatal error building response: ' . $e->getMessage()]);
            }
            
        } catch (Exception $e) {
            log_message('error', 'AdminCon::get_approval_order_details - Exception: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_approval_order_details - Stack trace: ' . $e->getTraceAsString());
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Error loading order details: ' . $e->getMessage()]);
        } catch (Error $e) {
            log_message('error', 'AdminCon::get_approval_order_details - Fatal Error: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_approval_order_details - Stack trace: ' . $e->getTraceAsString());
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Fatal error loading order details: ' . $e->getMessage()]);
        }
    }

    /**
     * Admin approves order
     * Uses Order_model->admin_approve_order()
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
        $order_id_clean = $order->OrderNumber ?? 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
        
        // Get admin ID
        $admin_id = $this->session->userdata('user_id');
        
        // Use Order_model function
        $result = $this->Order_model->admin_approve_order($order_id_numeric, $admin_id, $admin_notes);
        
        if ($result['success']) {
            $result['order_id'] = $order_id_clean;
        }
        
        echo json_encode($result);
    }

    /**
     * Admin disapproves order
     * Uses Order_model->admin_disapprove_order()
     */
    public function disapprove_order_admin()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->post('order_id');
        $disapproval_reason = $this->input->post('disapproval_reason') ?: '';
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        if (empty($disapproval_reason)) {
            echo json_encode(['success' => false, 'message' => 'Disapproval reason is required']);
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
        $order_id_clean = $order->OrderNumber ?? 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
        
        // Get admin ID
        $admin_id = $this->session->userdata('user_id');
        
        // Use Order_model function
        $result = $this->Order_model->admin_disapprove_order($order_id_numeric, $admin_id, $disapproval_reason);
        
        if ($result['success']) {
            $result['order_id'] = $order_id_clean;
        }
        
        echo json_encode($result);
    }

    /**
     * Helper method to map database status to display format
     */
    private function map_status_to_display($status)
    {
        // Handle null or empty status
        if (empty($status) || trim($status) === '') {
            return 'Pending Review';
        }
        
        $status_map = [
            'Pending' => 'Pending',
            'Pending Review' => 'Ready to Approve',
            'Approved' => 'Confirmed',
            'Completed' => 'Completed',
            'Cancelled' => 'Canceled',
            'Disapproved' => 'Disapproved',
            'In Fabrication' => 'In Progress',
            'Ready for Installation' => 'In Progress',
            'Awaiting Admin' => 'Ready to Approve',
            'Returned' => 'Returned'
        ];
        
        return $status_map[$status] ?? $status;
    }

    /**
     * Helper method to map database status to CSS class
     */
    private function map_status_to_class($status)
    {
        // Handle null or empty status
        if (empty($status) || trim($status) === '') {
            return 'pending';
        }
        
        $class_map = [
            'Pending' => 'pending',
            'Pending Review' => 'pending',
            'Approved' => 'completed',
            'Completed' => 'completed',
            'Cancelled' => 'canceled',
            'Disapproved' => 'canceled',
            'In Fabrication' => 'pending',
            'Ready for Installation' => 'pending',
            'Awaiting Admin' => 'pending',
            'Returned' => 'canceled'
        ];
        
        return $class_map[$status] ?? 'pending';
    }

    /**
     * Admin completes order
     * Updates order status to 'Completed'
     */
    public function complete_order()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->post('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Remove # prefix if present
        $order_id_clean = str_replace('#', '', $order_id);
        
        // Get admin ID
        $admin_id = $this->session->userdata('user_id');
        
        // Load Order_model
        $this->load->model('Order_model');
        
        // Try to find the order using multiple methods (similar to get_order_details_ajax)
        $order = null;
        $order_id_numeric = null;
        
        // Try by OrderNumber first (for GI001 format)
        if (preg_match('/^GI\d+$/i', $order_id_clean)) {
            $order = $this->db->where('OrderNumber', $order_id_clean)->get('`order`')->row();
            if ($order) {
                $order_id_numeric = $order->OrderID;
            }
        }
        
        // If not found, try by numeric ID
        if (!$order && is_numeric($order_id_clean)) {
            $order = $this->Order_model->get_order((int)$order_id_clean);
            if ($order) {
                $order_id_numeric = $order->OrderID;
            }
        }
        
        // If still not found, try OrderNumber lookup for any string
        if (!$order) {
            $order = $this->db->where('OrderNumber', $order_id_clean)->get('`order`')->row();
            if ($order) {
                $order_id_numeric = $order->OrderID;
            }
        }
        
        // If still not found, try using get_order with the cleaned ID (it handles both formats)
        if (!$order) {
            $order = $this->Order_model->get_order($order_id_clean);
            if ($order) {
                $order_id_numeric = $order->OrderID;
            }
        }
        
        if (!$order) {
            echo json_encode([
                'success' => false, 
                'message' => 'Order not found. Please check the order ID and try again.'
            ]);
            return;
        }
        
        // Ensure we have the numeric ID
        if (!$order_id_numeric) {
            $order_id_numeric = $order->OrderID;
        }
        
        // Get the OrderNumber for display
        $order_number = $order->OrderNumber ?? 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
        
        // Validate that order can be completed (should be in a valid status)
        $valid_statuses_for_completion = ['Approved', 'In Fabrication', 'Ready for Installation'];
        if (!in_array($order->Status, $valid_statuses_for_completion)) {
            $message = "Order cannot be completed. Current status: {$order->Status}. ";
            if ($order->Status === 'Awaiting Admin') {
                $message .= "Please approve this order in the 'Order Schedule Approval' section below first.";
            } else {
                $message .= "Order must be in 'Approved', 'In Fabrication', or 'Ready for Installation' status.";
            }
            echo json_encode([
                'success' => false, 
                'message' => $message
            ]);
            return;
        }
        
        // Update order status to 'Completed' using Order_model
        $result = $this->Order_model->update_order_status($order_id_numeric, 'Completed', 'Admin', $admin_id);
        
        if ($result) {
            // Log activity
            $admin = $this->User_model->get_by_id($admin_id);
            $admin_name = $admin ? trim($admin->First_Name . ' ' . $admin->Last_Name) : 'Admin';
            
            $this->log_activity(
                'Order Completed',
                "Order {$order_number} has been marked as completed by {$admin_name}",
                'Admin',
                $admin_id,
                $admin_name,
                $order_id_numeric,
                'Order'
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Order marked as completed successfully',
                'order_id' => $order_number
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to complete order']);
        }
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

    // Notifications
    public function admin_notif()
    {
        // Initialize notifications array
        $all_notifications = [];
        
        // Check if system_activity_log table exists and fetch notifications
        if ($this->db->table_exists('system_activity_log')) {
            try {
                $this->db->order_by('Timestamp', 'DESC');
                $notifications = $this->db->get('system_activity_log')->result();
                
                // Format notifications for display
                if ($notifications) {
                    foreach ($notifications as $notif) {
                        // Determine icon based on action
                        $icon = $this->determine_notification_icon(
                            isset($notif->Action) ? $notif->Action : '', 
                            isset($notif->Description) ? $notif->Description : ''
                        );
                        
                        // Format title and message
                        $action = isset($notif->Action) ? $notif->Action : 'Notification';
                        $description = isset($notif->Description) ? $notif->Description : '';
                        
                        $all_notifications[] = (object)[
                            'Action' => $action,
                            'Description' => $description,
                            'Icon' => $icon,
                            'Role' => isset($notif->Role) ? $notif->Role : 'System',
                            'Timestamp' => isset($notif->Timestamp) ? $notif->Timestamp : date('Y-m-d H:i:s'),
                            'Status' => 'read'
                        ];
                    }
                }
            } catch (Exception $e) {
                log_message('error', 'Error fetching notifications: ' . $e->getMessage());
                // Continue with empty array
            } catch (Error $e) {
                log_message('error', 'Fatal error fetching notifications: ' . $e->getMessage());
                // Continue with empty array
            }
        }
        
        // Prepare data for view
        $data['notifications'] = $all_notifications;
        $data['title'] = "Glassify - Notifications";
        $data['active'] = 'notif';
        $data['content_view'] = 'admin_page/admin_notif';
        $data['page_css'] = 'sales_css/sales_notif.css';
        
        // Load view
        try {
            $this->load->view('admin_page/layout', $data);
        } catch (Exception $e) {
            log_message('error', 'Error loading admin_notif view: ' . $e->getMessage());
            show_error('Error loading notifications page: ' . $e->getMessage(), 500);
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
        
        // Order-related icons
        if (stripos($action_lower, 'order') !== false || stripos($desc_lower, 'order') !== false) {
            if (stripos($action_lower, 'approval') !== false || stripos($action_lower, 'requested') !== false) {
                return 'fa-user-tie';
            } elseif (stripos($action_lower, 'approved') !== false) {
                return 'fa-shopping-cart';
            } elseif (stripos($action_lower, 'disapproved') !== false || stripos($action_lower, 'rejected') !== false) {
                return 'fa-times-circle';
            } elseif (stripos($action_lower, 'completed') !== false) {
                return 'fa-check-circle';
            }
            return 'fa-shopping-cart';
        }
        
        // Product-related
        if (stripos($action_lower, 'product') !== false) {
            return 'fa-box';
        }
        
        // Inventory-related
        if (stripos($action_lower, 'inventory') !== false || stripos($desc_lower, 'inventory') !== false || 
            stripos($desc_lower, 'stock') !== false) {
            return 'fa-box-open';
        }
        
        // Payment-related
        if (stripos($action_lower, 'payment') !== false || stripos($desc_lower, 'payment') !== false) {
            return 'fa-money-bill-wave';
        }
        
        // User/Employee-related
        if (stripos($action_lower, 'employee') !== false || stripos($action_lower, 'user') !== false) {
            return 'fa-user-tie';
        }
        
        // Default icon
        return 'fa-info-circle';
    }
    
    /**
     * Mark all notifications as viewed (AJAX endpoint)
     * Stores the current timestamp in session to track when admin last viewed notifications
     */
    public function mark_all_notifications_viewed()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        // Store current timestamp as last viewed time
        $this->session->set_userdata('admin_last_viewed_notifications', date('Y-m-d H:i:s'));
        
        echo json_encode(['status' => 'success', 'message' => 'All notifications marked as viewed']);
    }
    
    /**
     * Get notification count (AJAX endpoint)
     * Admin uses system_activity_log - count only unviewed recent notifications
     */
    public function get_notification_count_ajax()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['status' => 'error', 'count' => 0]);
            return;
        }
        
        // Count unviewed notifications from system_activity_log
        if ($this->db->table_exists('system_activity_log')) {
            // Get last viewed timestamp from session
            $last_viewed = $this->session->userdata('admin_last_viewed_notifications');
            
            // Count notifications from last 30 days
            $this->db->where('Timestamp >=', date('Y-m-d H:i:s', strtotime('-30 days')));
            
            // If admin has viewed notifications before, only count newer ones
            if ($last_viewed) {
                $this->db->where('Timestamp >', $last_viewed);
            }
            
            $count = $this->db->count_all_results('system_activity_log');
        } else {
            $count = 0;
        }
        
        // Limit to 99, show 99+ if more
        if ($count > 99) {
            $display_count = '99+';
        } else {
            $display_count = $count;
        }
        
        echo json_encode(['status' => 'success', 'count' => $count, 'display' => $display_count]);
    }

    // ======================
    // ORDER MANAGEMENT - NEW METHODS
    // ======================

    /**
     * Update order status
     */
    public function update_order_status()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        $new_status = $this->input->post('status');
        $notes = $this->input->post('notes');
        
        if (!$order_id || !$new_status) {
            echo json_encode(['success' => false, 'message' => 'Order ID and status are required']);
            return;
        }
        
        // Remove # prefix if present
        $order_id_clean = str_replace('#', '', $order_id);
        
        // Get current order
        $order = $this->db->where('OrderNumber', $order_id_clean)
                          ->or_where('OrderID', $order_id_clean)
                          ->get('`order`')->row();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Validate status transition
        $current_status = $order->Status ?? 'Pending Review';
        $valid_transitions = $this->get_valid_status_transitions($current_status);
        
        if (!in_array($new_status, $valid_transitions)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status transition']);
            return;
        }
        
        // Update order status
        $update_data = ['Status' => $new_status];
        if ($new_status === 'Approved' && !$order->Approved_Date) {
            $update_data['Approved_Date'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where('OrderID', $order->OrderID)->update('`order`', $update_data);
        
        // Log status change
        $this->log_activity(
            'Order Status Updated',
            "Order {$order_id_clean} status changed from {$current_status} to {$new_status}",
            'Admin',
            $this->session->userdata('user_id'),
            $this->session->userdata('user_name'),
            $order->OrderID,
            'Order'
        );
        
        // Save admin notes if provided
        if ($notes) {
            // Store notes in a notes table or order table if column exists
            if ($this->db->field_exists('AdminNotes', 'order')) {
                $this->db->where('OrderID', $order->OrderID)
                         ->update('`order`', ['AdminNotes' => $notes]);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
    }
    
    /**
     * Get valid status transitions for an order
     */
    private function get_valid_status_transitions($current_status)
    {
        $transitions = [
            'Pending Review' => ['Approved', 'Cancelled'],
            'Approved' => ['In Fabrication', 'Cancelled'],
            'Ocular Pending' => ['Approved', 'Cancelled'],
            'In Fabrication' => ['Ready for Installation', 'Cancelled'],
            'Ready for Installation' => ['Completed', 'Cancelled'],
            'Completed' => [], // Final state
            'Cancelled' => [] // Final state
        ];
        
        return $transitions[$current_status] ?? [];
    }
    
    /**
     * Assign staff to order
     */
    public function assign_staff()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        $staff_type = $this->input->post('staff_type'); // 'fabrication' or 'installation'
        $staff_id = $this->input->post('staff_id');
        
        if (!$order_id || !$staff_type) {
            echo json_encode(['success' => false, 'message' => 'Order ID and staff type are required']);
            return;
        }
        
        // Remove # prefix if present
        $order_id_clean = str_replace('#', '', $order_id);
        
        // Get order
        $order = $this->db->where('OrderNumber', $order_id_clean)
                          ->or_where('OrderID', $order_id_clean)
                          ->get('`order`')->row();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Update staff assignment
        $field_name = $staff_type === 'fabrication' ? 'FabricationStaff_ID' : 'InstallationStaff_ID';
        
        if ($this->db->field_exists($field_name, 'order')) {
            $update_data = [$field_name => $staff_id ?: null];
            $this->db->where('OrderID', $order->OrderID)->update('`order`', $update_data);
            
            // Get staff name for logging
            $staff_name = 'Unassigned';
            if ($staff_id) {
                $staff = $this->db->where('UserID', $staff_id)->get('user')->row();
                if ($staff) {
                    $staff_name = ($staff->First_Name ?? '') . ' ' . ($staff->Last_Name ?? '');
                }
            }
            
            $this->log_activity(
                'Staff Assigned',
                "{$staff_type} staff assigned to order {$order_id_clean}: {$staff_name}",
                'Admin',
                $this->session->userdata('user_id'),
                $this->session->userdata('user_name'),
                $order->OrderID,
                'Order'
            );
            
            echo json_encode(['success' => true, 'message' => 'Staff assigned successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Staff assignment field not found in database']);
        }
    }
    
    /**
     * Get staff list for dropdown
     */
    public function get_staff_list()
    {
        header('Content-Type: application/json');
        
        $role = $this->input->get('role'); // 'Fabrication' or 'Installation'
        
        $this->db->select('UserID, First_Name, Last_Name, Middle_Name, Role');
        $this->db->from('user');
        
        // Filter by role if provided (for Fabrication/Installation, use Admin or Sales Representative)
        // Since there's no specific Fabrication/Installation role, we'll get all active staff
        // The frontend can filter as needed
        if ($role) {
            // For now, get Admin and Sales Representative roles as they can be assigned
            $this->db->where_in('Role', ['Admin', 'Sales Representative']);
        } else {
            // Get all active staff (Admin, Sales Rep, Inventory Officer)
            $this->db->where_in('Role', ['Admin', 'Sales Representative']);
        }
        
        $this->db->where('Status', 'Active');
        $this->db->order_by('First_Name', 'ASC');
        
        $staff = $this->db->get()->result();
        
        $formatted_staff = [];
        foreach ($staff as $s) {
            $formatted_staff[] = [
                'id' => $s->UserID,
                'name' => trim(($s->First_Name ?? '') . ' ' . ($s->Middle_Name ?? '') . ' ' . ($s->Last_Name ?? ''))
            ];
        }
        
        echo json_encode(['success' => true, 'staff' => $formatted_staff]);
    }
    
    /**
     * Update ocular notes for an order
     */
    public function update_ocular_notes()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        $ocular_notes = $this->input->post('ocular_notes');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Remove # prefix if present
        $order_id_clean = str_replace('#', '', $order_id);
        
        // Get order to find OrderID
        $order = $this->db->where('OrderNumber', $order_id_clean)
                          ->or_where('OrderID', $order_id_clean)
                          ->get('`order`')->row();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        $order_id_numeric = $order->OrderID;
        
        // Find or create ocular appointment
        $ocular_appointment = $this->db->where('OrderID', $order_id_numeric)
                                       ->where('Service', 'Ocular Visit')
                                       ->get('appointments')
                                       ->row();
        
        if ($ocular_appointment) {
            // Update existing appointment
            $this->db->where('AppointmentID', $ocular_appointment->AppointmentID)
                     ->update('appointments', [
                         'OcularNotes' => $ocular_notes,
                         'Updated_Date' => date('Y-m-d H:i:s')
                     ]);
        } else {
            // Create new ocular appointment if it doesn't exist
            $appointment_data = [
                'OrderID' => $order_id_numeric,
                'Customer_ID' => $order->Customer_ID,
                'Service' => 'Ocular Visit',
                'Status' => 'In Progress',
                'OcularNotes' => $ocular_notes,
                'AppointmentDate' => date('Y-m-d'),
                'AppointmentTime' => '10:00:00'
            ];
            
            // Get customer name
            $customer = $this->db->where('Customer_ID', $order->Customer_ID)
                                 ->join('user u', 'u.UserID = customer.UserID', 'left')
                                 ->get('customer')
                                 ->row();
            
            if ($customer) {
                $appointment_data['ClientName'] = trim(($customer->First_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
            }
            
            $this->db->insert('appointments', $appointment_data);
        }
        
        // Log activity
        $this->log_activity(
            'Ocular Notes Updated',
            "Ocular notes updated for order {$order_id_clean}",
            'Admin',
            $this->session->userdata('user_id'),
            $this->session->userdata('user_name'),
            $order_id_numeric,
            'Order'
        );
        
        echo json_encode(['success' => true, 'message' => 'Ocular notes updated successfully']);
    }
    
    /**
     * Export order (placeholder - can be enhanced for PDF/Excel generation)
     */
    public function export_order()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->get('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            return;
        }
        
        // This is a placeholder - implement actual export logic
        echo json_encode(['success' => true, 'message' => 'Export functionality to be implemented']);
    }
    
    // ======================
    // CALENDAR MODULE
    // ======================
    
    public function admin_calendar()
    {
        $data['title'] = "Glassify - Calendar / Project Timeline";
        $data['active'] = 'calendar';
        $data['content_view'] = 'admin_page/admin_calendar';
        $data['page_css'] = 'admin_css/admin_calendar.css';
        $data['page_js'] = 'admin-js/calendar.js';
        $this->load->view('admin_page/layout', $data);
    }
    
    /**
     * Get calendar events (orders and appointments)
     */
    public function get_calendar_events()
    {
        header('Content-Type: application/json');
        
        try {
            $start_date = $this->input->get('start');
            $end_date = $this->input->get('end');
            $order_type = $this->input->get('order_type'); // 'direct', 'site-assessed', or 'all'
            $status_filter = $this->input->get('status'); // Status filter
            $search = $this->input->get('search'); // Search by order number or client name
            
            $events = [];
            
            // Get orders with additional fields for timeline
            $this->db->select('o.OrderID, o.OrderNumber, o.OrderDate, o.Status, o.OrderType, o.TotalAmount, 
                              u.First_Name, u.Last_Name, o.FabricationStartDate, o.FabricationEndDate,
                              o.InstallationDate');
            $this->db->from('`order` o');
            $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
            $this->db->join('user u', 'c.UserID = u.UserID', 'left');
            
            if ($start_date) {
                $this->db->where('o.OrderDate >=', $start_date);
            }
            if ($end_date) {
                $this->db->where('o.OrderDate <=', $end_date);
            }
            
            if ($order_type && $order_type !== 'all') {
                // Check if OrderType column exists by trying to query it
                try {
                    if ($order_type === 'direct') {
                        $this->db->where('(o.OrderType = "Direct" OR o.OrderType IS NULL)');
                    } elseif ($order_type === 'site-assessed') {
                        $this->db->where('o.OrderType', 'Site-Assessed');
                    }
                } catch (Exception $e) {
                    // If OrderType doesn't exist, skip this filter
                    log_message('debug', 'OrderType field may not exist: ' . $e->getMessage());
                }
            }
            
            if ($status_filter && $status_filter !== 'all') {
                $this->db->where('o.Status', $status_filter);
            }
            
            if ($search) {
                $this->db->group_start();
                $this->db->like('o.OrderNumber', $search);
                $this->db->or_like('u.First_Name', $search);
                $this->db->or_like('u.Last_Name', $search);
                $this->db->group_end();
            }
            
            $orders_query = $this->db->get();
            $db_error = $this->db->error();
            if ($db_error['code'] != 0) {
                throw new Exception('Orders query failed: ' . $db_error['message']);
            }
            $orders = $orders_query->result();
            
            foreach ($orders as $order) {
                $first_name = isset($order->First_Name) ? $order->First_Name : '';
                $last_name = isset($order->Last_Name) ? $order->Last_Name : '';
                $customer_name = trim($first_name . ' ' . $last_name);
                $order_type_value = isset($order->OrderType) ? $order->OrderType : 'Direct';
                $progress = $this->calculate_order_progress($order->Status, $order_type_value);
                
                $order_number = isset($order->OrderNumber) && $order->OrderNumber ? 
                    $order->OrderNumber : 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT);
                
                $events[] = [
                    'id' => 'order_' . $order->OrderID,
                    'title' => 'Order: ' . $order_number,
                    'start' => $order->OrderDate,
                    'type' => 'order',
                    'order_id' => $order->OrderID,
                    'order_number' => $order_number,
                    'status' => $order->Status,
                    'customer' => $customer_name,
                    'order_type' => $order_type_value,
                    'color' => $this->get_order_calendar_color($order->Status, $order_type_value),
                    'progress' => $progress,
                    'fabrication_start' => isset($order->FabricationStartDate) ? $order->FabricationStartDate : null,
                    'fabrication_end' => isset($order->FabricationEndDate) ? $order->FabricationEndDate : null,
                    'installation_date' => isset($order->InstallationDate) ? $order->InstallationDate : null,
                    'total_amount' => isset($order->TotalAmount) ? $order->TotalAmount : 0
                ];
            }
        
            // Get appointments with additional details
            $this->db->select('a.*, o.OrderNumber, o.OrderType, u.First_Name, u.Last_Name');
            $this->db->from('appointments a');
            $this->db->join('`order` o', 'a.OrderID = o.OrderID', 'left');
            $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
            $this->db->join('user u', 'c.UserID = u.UserID', 'left');
            
            if ($start_date) {
                $this->db->where('a.AppointmentDate >=', $start_date);
            }
            if ($end_date) {
                $this->db->where('a.AppointmentDate <=', $end_date);
            }
            
            if ($search) {
                $this->db->group_start();
                $this->db->like('o.OrderNumber', $search);
                $this->db->or_like('a.ClientName', $search);
                $this->db->or_like('u.First_Name', $search);
                $this->db->or_like('u.Last_Name', $search);
                $this->db->group_end();
            }
            
            $appointments_query = $this->db->get();
            $db_error = $this->db->error();
            if ($db_error['code'] != 0) {
                throw new Exception('Appointments query failed: ' . $db_error['message']);
            }
            $appointments = $appointments_query->result();
            
            foreach ($appointments as $apt) {
                $appointment_time = isset($apt->AppointmentTime) && $apt->AppointmentTime ? $apt->AppointmentTime : '00:00:00';
                $start_datetime = $apt->AppointmentDate . ' ' . $appointment_time;
                
                $client_name = isset($apt->ClientName) && $apt->ClientName ? $apt->ClientName : '';
                $apt_first_name = isset($apt->First_Name) ? $apt->First_Name : '';
                $apt_last_name = isset($apt->Last_Name) ? $apt->Last_Name : '';
                $customer_name = !empty($client_name) ? $client_name : trim($apt_first_name . ' ' . $apt_last_name);
                
                $events[] = [
                    'id' => 'appt_' . $apt->AppointmentID,
                    'title' => $apt->Service . ' - ' . (!empty($customer_name) ? $customer_name : 'N/A'),
                    'start' => $start_datetime,
                    'type' => 'appointment',
                    'appointment_id' => $apt->AppointmentID,
                    'order_id' => isset($apt->OrderID) ? $apt->OrderID : null,
                    'order_number' => isset($apt->OrderNumber) && $apt->OrderNumber ? $apt->OrderNumber : 'N/A',
                    'service' => $apt->Service,
                    'client_name' => $customer_name,
                    'assigned_staff' => isset($apt->AssignedStaff) && $apt->AssignedStaff ? $apt->AssignedStaff : 'N/A',
                    'status' => isset($apt->Status) && $apt->Status ? $apt->Status : 'Scheduled',
                    'color' => $this->get_appointment_calendar_color($apt->Service, isset($apt->Status) ? $apt->Status : 'Scheduled'),
                    'notes' => isset($apt->Notes) ? $apt->Notes : ''
                ];
            }
            
            echo json_encode($events);
            
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            $error_trace = $e->getTraceAsString();
            log_message('error', 'get_calendar_events error: ' . $error_message);
            log_message('error', 'get_calendar_events trace: ' . substr($error_trace, 0, 500));
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Failed to load calendar events',
                'message' => $error_message
            ]);
            exit;
        } catch (Error $e) {
            $error_message = $e->getMessage();
            log_message('error', 'get_calendar_events fatal error: ' . $error_message);
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Failed to load calendar events',
                'message' => $error_message
            ]);
            exit;
        }
    }
    
    /**
     * Calculate order progress percentage based on status and order type
     */
    private function calculate_order_progress($status, $order_type)
    {
        if ($order_type === 'Site-Assessed') {
            $progress_map = [
                'Pending Review' => 0,
                'Ocular Pending' => 0,
                'Ocular Completed' => 20,
                'Approved' => 35,
                'In Fabrication' => 55,
                'Ready for Installation' => 80,
                'Installed' => 95,
                'Completed' => 100,
                'Cancelled' => 0
            ];
        } else {
            // Direct Orders
            $progress_map = [
                'Pending Review' => 0,
                'Approved' => 25,
                'In Fabrication' => 50,
                'Ready for Installation' => 75,
                'Installed' => 90,
                'Completed' => 100,
                'Cancelled' => 0
            ];
        }
        
        return $progress_map[$status] ?? 0;
    }
    
    private function get_order_calendar_color($status, $order_type)
    {
        // Color coding as per documentation
        if ($status === 'Cancelled') {
            return '#f44336'; // Red
        }
        if ($status === 'Completed') {
            return '#2e7d32'; // Dark Green
        }
        if ($order_type === 'Site-Assessed') {
            return '#FF9800'; // Orange
        }
        return '#2196F3'; // Blue for Direct
    }
    
    private function get_appointment_calendar_color($service, $status)
    {
        if (stripos($service, 'Ocular') !== false || stripos($service, 'Site Assessment') !== false) {
            return '#FFC107'; // Light Orange
        }
        if (stripos($service, 'Installation') !== false) {
            return '#4CAF50'; // Green
        }
        return '#9C27B0'; // Purple (Fabrication)
    }
    
    /**
     * Get day details (orders and appointments for a specific date)
     */
    public function get_day_details()
    {
        header('Content-Type: application/json');
        
        $date = $this->input->get('date');
        if (!$date) {
            echo json_encode(['success' => false, 'message' => 'Date is required']);
            return;
        }
        
        $events = [];
        
        // Get orders for this date
        $this->db->select('o.OrderID, o.OrderNumber, o.OrderDate, o.Status, o.OrderType, o.TotalAmount, u.First_Name, u.Last_Name');
        $this->db->from('`order` o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->where('DATE(o.OrderDate)', $date);
        
        $orders = $this->db->get()->result();
        
        foreach ($orders as $order) {
            $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
            $events[] = [
                'id' => 'order_' . $order->OrderID,
                'type' => 'order',
                'order_id' => $order->OrderID,
                'order_number' => $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT),
                'status' => $order->Status,
                'customer' => $customer_name,
                'order_type' => $order->OrderType ?? 'Direct',
                'color' => $this->get_order_calendar_color($order->Status, $order->OrderType ?? 'Direct'),
                'time' => null,
                'total_amount' => $order->TotalAmount ?? 0
            ];
        }
        
        // Get appointments for this date
        $this->db->select('a.*, o.OrderNumber');
        $this->db->from('appointments a');
        $this->db->join('`order` o', 'a.OrderID = o.OrderID', 'left');
        $this->db->where('DATE(a.AppointmentDate)', $date);
        $this->db->order_by('a.AppointmentTime', 'ASC');
        
        $appointments = $this->db->get()->result();
        
        foreach ($appointments as $apt) {
            $events[] = [
                'id' => 'appt_' . $apt->AppointmentID,
                'type' => 'appointment',
                'appointment_id' => $apt->AppointmentID,
                'order_id' => $apt->OrderID,
                'order_number' => $apt->OrderNumber ?? 'N/A',
                'service' => $apt->Service,
                'client_name' => $apt->ClientName ?? 'N/A',
                'status' => $apt->Status ?? 'Scheduled',
                'color' => $this->get_appointment_calendar_color($apt->Service, $apt->Status ?? 'Scheduled'),
                'time' => $apt->AppointmentTime ?? '00:00:00',
                'assigned_staff' => $apt->AssignedStaff ?? 'N/A'
            ];
        }
        
        echo json_encode(['success' => true, 'events' => $events]);
    }
    
    // ======================
    // PRODUCTION / FABRICATION QUEUE MODULE
    // ======================
    
    public function admin_production()
    {
        $data['title'] = "Glassify - Production / Fabrication Queue";
        $data['active'] = 'fabrication-queue';
        $data['content_view'] = 'admin_page/admin_production';
        $data['page_css'] = 'admin_css/admin_production.css';
        $data['page_js'] = 'admin-js/production.js';
        $this->load->view('admin_page/layout', $data);
    }
    
    /**
     * Get fabrication queue orders
     */
    public function get_fabrication_queue()
    {
        header('Content-Type: application/json');
        
        $status_filter = $this->input->get('status');
        $order_type = $this->input->get('order_type');
        $staff_filter = $this->input->get('staff');
        $date_start = $this->input->get('date_start');
        $date_end = $this->input->get('date_end');
        $search = $this->input->get('search');
        
        // Get orders that are approved or in fabrication
        $this->db->select('o.*, u.First_Name, u.Last_Name, p.ProductName, oi.Quantity,
                          staff.First_Name as Staff_First_Name, staff.Last_Name as Staff_Last_Name');
        $this->db->from('`order` o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->join('user staff', 'staff.UserID = o.FabricationStaff_ID', 'left');
        $this->db->group_by('o.OrderID');
        
        $this->db->where_in('o.Status', ['Approved', 'In Fabrication', 'Ready for Installation', 'Completed', 'Installed']);
        
        // Map status filter to actual statuses
        if ($status_filter && $status_filter !== 'all') {
            if ($status_filter === 'queued') {
                $this->db->where('(o.Status = "Approved" AND (o.FabricationStatus IS NULL OR o.FabricationStatus = "Queued"))');
            } elseif ($status_filter === 'in-progress') {
                $this->db->where('o.Status', 'In Fabrication');
                $this->db->where('(o.FabricationStatus IS NULL OR o.FabricationStatus = "In Progress")');
            } elseif ($status_filter === 'quality-check') {
                // Quality check uses FabricationStatus field
                $this->db->where('o.FabricationStatus', 'Quality Check');
            } elseif ($status_filter === 'ready') {
                $this->db->where('(o.Status = "Ready for Installation" OR o.FabricationStatus = "Ready")');
            } elseif ($status_filter === 'completed') {
                $this->db->where_in('o.Status', ['Completed', 'Installed']);
            } else {
                $this->db->where('o.Status', $status_filter);
            }
        }
        
        if ($order_type && $order_type !== 'all') {
            if ($this->db->field_exists('OrderType', 'order')) {
                if ($order_type === 'direct') {
                    $this->db->where('(o.OrderType = "Direct" OR o.OrderType IS NULL)');
                } elseif ($order_type === 'site-assessed') {
                    $this->db->where('o.OrderType', 'Site-Assessed');
                }
            }
        }
        
        if ($staff_filter && $staff_filter !== 'all') {
            $this->db->where('o.FabricationStaff_ID', $staff_filter);
        }
        
        if ($date_start) {
            $this->db->where('o.FabricationStartDate >=', $date_start);
        }
        if ($date_end) {
            $this->db->where('o.FabricationEndDate <=', $date_end);
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('o.OrderNumber', $search);
            $this->db->or_like('u.First_Name', $search);
            $this->db->or_like('u.Last_Name', $search);
            $this->db->or_like('p.ProductName', $search);
            $this->db->group_end();
        }
        
        $orders = $this->db->get()->result();
        
        $formatted_orders = [];
        foreach ($orders as $order) {
            $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
            $staff_name = trim(($order->Staff_First_Name ?? '') . ' ' . ($order->Staff_Last_Name ?? ''));
            
            // Determine queue status based on FabricationStatus if available, otherwise use Status
            $queue_status = $this->map_status_to_queue($order->Status, $order->FabricationStatus ?? null);
            
            // Get progress - use FabricationProgress if set, otherwise calculate based on status
            $fabrication_status = $order->FabricationStatus ?? null;
            $progress = $order->FabricationProgress ?? null;
            
            // If no progress set, use status-based defaults
            if ($progress === null && $fabrication_status) {
                $status_progress_map = [
                    'Queued' => 0,
                    'In Progress' => 25,
                    'Quality Check' => 50,
                    'Ready' => 75,
                    'Completed' => 100
                ];
                $progress = $status_progress_map[$fabrication_status] ?? $this->calculate_fabrication_progress($order->Status, $order->FabricationStartDate, $order->FabricationEndDate);
            } elseif ($progress === null) {
                $progress = $this->calculate_fabrication_progress($order->Status, $order->FabricationStartDate, $order->FabricationEndDate);
            }
            
            $formatted_orders[] = [
                'order_id' => $order->OrderID,
                'order_number' => $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT),
                'customer_name' => $customer_name,
                'product_name' => $order->ProductName ?? 'N/A',
                'quantity' => $order->Quantity ?? 1,
                'status' => $order->Status,
                'queue_status' => $queue_status,
                'order_type' => $order->OrderType ?? 'Direct',
                'fabrication_start' => $order->FabricationStartDate ?? null,
                'fabrication_end' => $order->FabricationEndDate ?? null,
                'fabrication_staff_id' => $order->FabricationStaff_ID ?? null,
                'fabrication_staff_name' => $staff_name ?: 'Unassigned',
                'progress' => $progress,
                'total_amount' => $order->TotalAmount ?? 0
            ];
        }
        
        echo json_encode(['success' => true, 'orders' => $formatted_orders]);
    }
    
    /**
     * Map order status to queue status
     * Uses FabricationStatus if available, otherwise falls back to Status
     */
    private function map_status_to_queue($status, $fabrication_status = null)
    {
        // If FabricationStatus is set, use it to determine queue status
        if ($fabrication_status) {
            $fabrication_map = [
                'Queued' => 'queued',
                'In Progress' => 'in-progress',
                'Quality Check' => 'quality-check',
                'Ready' => 'ready',
                'Completed' => 'completed'
            ];
            
            if (isset($fabrication_map[$fabrication_status])) {
                return $fabrication_map[$fabrication_status];
            }
        }
        
        // Fallback to Status field
        $status_map = [
            'Approved' => 'queued',
            'In Fabrication' => 'in-progress',
            'Ready for Installation' => 'ready',
            'Completed' => 'completed',
            'Installed' => 'completed'
        ];
        
        return $status_map[$status] ?? 'queued';
    }
    
    /**
     * Get detailed order information for production queue
     */
    public function get_production_order_details()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->get('order_id');
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Get order details
        $this->db->select('o.*, u.First_Name, u.Last_Name, u.Email, u.PhoneNum,
                          staff.First_Name as Staff_First_Name, staff.Last_Name as Staff_Last_Name,
                          staff.UserID as Staff_UserID');
        $this->db->from('`order` o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->join('user staff', 'staff.UserID = o.FabricationStaff_ID', 'left');
        $this->db->where('o.OrderID', $order_id);
        
        $order = $this->db->get()->row();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Get order items
        $this->db->select('oi.*, p.ProductName, p.Product_ID');
        $this->db->from('order_items oi');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->where('oi.OrderID', $order_id);
        $order_items = $this->db->get()->result();
        
        $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
        $staff_name = trim(($order->Staff_First_Name ?? '') . ' ' . ($order->Staff_Last_Name ?? ''));
        
        $result = [
            'success' => true,
            'order' => [
                'order_id' => $order->OrderID,
                'order_number' => $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT),
                'order_type' => $order->OrderType ?? 'Direct',
                'status' => $order->Status,
                'order_date' => $order->OrderDate,
                'customer' => [
                    'name' => $customer_name,
                    'email' => $order->Email ?? '',
                    'phone' => $order->PhoneNum ?? ''
                ],
                'fabrication' => [
                    'start_date' => $order->FabricationStartDate ?? null,
                    'end_date' => $order->FabricationEndDate ?? null,
                    'actual_start_date' => $order->ActualFabricationStartDate ?? null,
                    'actual_end_date' => $order->ActualFabricationEndDate ?? null,
                    'staff_id' => $order->FabricationStaff_ID ?? null,
                    'staff_name' => $staff_name ?: 'Unassigned',
                    'progress' => $this->get_fabrication_progress($order->FabricationStatus ?? null, $order->FabricationProgress ?? null, $order->Status, $order->FabricationStartDate ?? null, $order->FabricationEndDate ?? null),
                    'status' => $order->FabricationStatus ?? null,
                    'notes' => $order->FabricationNotes ?? '',
                    'quality_check_notes' => $order->QualityCheckNotes ?? '',
                    'issues' => $order->FabricationIssues ?? ''
                ],
                'items' => [],
                'total_amount' => $order->TotalAmount ?? 0
            ]
        ];
        
        foreach ($order_items as $item) {
            $result['order']['items'][] = [
                'product_id' => $item->Product_ID,
                'product_name' => $item->ProductName ?? 'N/A',
                'quantity' => $item->Quantity ?? 1,
                'unit_price' => $item->UnitPrice ?? 0,
                'subtotal' => ($item->Quantity ?? 1) * ($item->UnitPrice ?? 0)
            ];
        }
        
        echo json_encode($result);
    }
    
    /**
     * Update fabrication progress
     */
    public function update_fabrication_progress()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        $progress = $this->input->post('progress');
        $status = $this->input->post('status');
        $fabrication_status = $this->input->post('fabrication_status');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $staff_id = $this->input->post('staff_id');
        $notes = $this->input->post('notes');
        $quality_check_notes = $this->input->post('quality_check_notes');
        $issues = $this->input->post('issues');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        $update_data = [];
        
        if ($progress !== null) {
            $update_data['FabricationProgress'] = (int)$progress;
        }
        
        if ($status) {
            $update_data['Status'] = $status;
        }
        
        if ($fabrication_status !== null) {
            $update_data['FabricationStatus'] = $fabrication_status;
            // Automatically set progress based on fabrication status
            $status_progress_map = [
                'Queued' => 0,
                'In Progress' => 25,
                'Quality Check' => 50,
                'Ready' => 75,
                'Completed' => 100
            ];
            if (isset($status_progress_map[$fabrication_status])) {
                $update_data['FabricationProgress'] = $status_progress_map[$fabrication_status];
            }
        }
        
        if ($start_date) {
            $update_data['FabricationStartDate'] = $start_date;
            if (!$this->db->get_where('`order`', ['OrderID' => $order_id])->row()->ActualFabricationStartDate) {
                $update_data['ActualFabricationStartDate'] = date('Y-m-d');
            }
        }
        
        if ($end_date) {
            $update_data['FabricationEndDate'] = $end_date;
        }
        
        if ($staff_id !== null) {
            $update_data['FabricationStaff_ID'] = $staff_id ?: null;
        }
        
        if ($notes !== null) {
            $update_data['FabricationNotes'] = $notes;
        }
        
        if ($quality_check_notes !== null) {
            $update_data['QualityCheckNotes'] = $quality_check_notes;
        }
        
        if ($issues !== null) {
            $update_data['FabricationIssues'] = $issues;
        }
        
        $this->db->where('OrderID', $order_id);
        $this->db->update('`order`', $update_data);
        
        if ($this->db->affected_rows() >= 0) {
            echo json_encode(['success' => true, 'message' => 'Progress updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update progress']);
        }
    }
    
    /**
     * Get staff members for assignment
     */
    public function get_fabrication_staff()
    {
        header('Content-Type: application/json');
        
        // Get employees (Admin, Sales, Inventory - anyone who can be assigned)
        $this->db->select('UserID, First_Name, Last_Name, Middle_Name, Role');
        $this->db->from('user');
        $this->db->where_in('Role', ['Admin', 'Sales Representative', 'Inventory Officer']);
        $this->db->where('Status', 'Active');
        $this->db->order_by('First_Name', 'ASC');
        
        $staff = $this->db->get()->result();
        
        $formatted_staff = [];
        foreach ($staff as $member) {
            $formatted_staff[] = [
                'id' => $member->UserID,
                'name' => trim($member->First_Name . ' ' . ($member->Middle_Name ? $member->Middle_Name . ' ' : '') . $member->Last_Name),
                'role' => $member->Role
            ];
        }
        
        echo json_encode(['success' => true, 'staff' => $formatted_staff]);
    }
    
    /**
     * Move order to quality check
     */
    public function move_to_quality_check()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Update FabricationStatus to Quality Check
        $this->db->where('OrderID', $order_id);
        $this->db->update('`order`', [
            'Status' => 'In Fabrication',
            'FabricationStatus' => 'Quality Check',
            'FabricationProgress' => 50
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Order moved to quality check']);
    }
    
    /**
     * Mark fabrication as complete
     */
    public function mark_fabrication_complete()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        $this->db->where('OrderID', $order_id);
        $this->db->update('`order`', [
            'Status' => 'Ready for Installation',
            'FabricationStatus' => 'Completed',
            'FabricationProgress' => 100,
            'ActualFabricationEndDate' => date('Y-m-d')
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Fabrication marked as complete']);
    }
    
    /**
     * Get fabrication progress based on FabricationStatus or calculate from Status
     */
    private function get_fabrication_progress($fabrication_status = null, $fabrication_progress = null, $order_status = null, $start_date = null, $end_date = null)
    {
        // If progress is explicitly set, use it
        if ($fabrication_progress !== null) {
            return (int)$fabrication_progress;
        }
        
        // If FabricationStatus is set, use status-based defaults
        if ($fabrication_status) {
            $status_progress_map = [
                'Queued' => 0,
                'In Progress' => 25,
                'Quality Check' => 50,
                'Ready' => 75,
                'Completed' => 100
            ];
            if (isset($status_progress_map[$fabrication_status])) {
                return $status_progress_map[$fabrication_status];
            }
        }
        
        // Fallback to calculating from order status
        return $this->calculate_fabrication_progress($order_status, $start_date, $end_date);
    }
    
    private function calculate_fabrication_progress($status, $start_date = null, $end_date = null)
    {
        // Base progress from status
        $status_map = [
            'Approved' => 0,
            'In Fabrication' => 50,
            'Ready for Installation' => 75,
            'Installed' => 90,
            'Completed' => 100
        ];
        
        $base_progress = $status_map[$status] ?? 0;
        
        // If we have dates, calculate time-based progress
        if ($start_date && $end_date) {
            $start = strtotime($start_date);
            $end = strtotime($end_date);
            $now = time();
            
            if ($end > $start) {
                $total_days = ($end - $start) / (60 * 60 * 24);
                $elapsed_days = ($now - $start) / (60 * 60 * 24);
                
                if ($elapsed_days > 0 && $total_days > 0) {
                    $time_progress = min(100, ($elapsed_days / $total_days) * 100);
                    // Combine status and time progress
                    $base_progress = max($base_progress, min(100, ($base_progress + $time_progress) / 2));
                }
            }
        }
        
        return (int)$base_progress;
    }
    
    // ======================
    // QUOTATIONS MODULE
    // ======================
    
    public function admin_quotations()
    {
        $data['title'] = "Glassify - Quotations";
        $data['active'] = 'quotations';
        $data['content_view'] = 'admin_page/admin_quotations';
        $data['page_css'] = ['admin_css/admin_orders.css', 'admin_css/admin_quotations.css'];
        $data['page_js'] = 'admin-js/quotations.js';
        $this->load->view('admin_page/layout', $data);
    }
    
    // ======================
    // RETURN ORDERS MODULE
    // ======================
    
    public function admin_return_orders()
    {
        $data['title'] = "Glassify - Return Orders";
        $data['active'] = 'return-orders';
        $data['content_view'] = 'admin_page/admin_return_orders';
        $data['page_css'] = ['admin_css/admin_orders.css', 'admin_css/admin_return_orders.css'];
        $data['page_js'] = 'admin-js/return-orders.js';
        $this->load->view('admin_page/layout', $data);
    }

    // ======================
    // QUOTATIONS AJAX METHODS
    // ======================
    
    public function get_quotations_ajax()
    {
        header('Content-Type: application/json');
        try {
            if (!$this->db->table_exists('quotation')) {
                echo json_encode(['success' => false, 'message' => 'Quotation table not found.', 'quotations' => [], 'total' => 0, 'total_pages' => 0, 'current_page' => 1]);
                return;
            }
            if (!$this->db->table_exists('employee')) {
                echo json_encode(['success' => false, 'message' => 'Employee table not found. Quotations require the employee table.', 'quotations' => [], 'total' => 0, 'total_pages' => 0, 'current_page' => 1]);
                return;
            }
            $status_filter = $this->input->get('status');
            $date_start = $this->input->get('date_start');
            $date_end = $this->input->get('date_end');
            $client_search = $this->input->get('client_search');
            $sales_rep = $this->input->get('sales_rep');
            $amount_min = $this->input->get('amount_min');
            $amount_max = $this->input->get('amount_max');
            $page = $this->input->get('page') ?: 1;
            $limit = $this->input->get('limit') ?: 10;
            $offset = ($page - 1) * $limit;
            
            // Build query - Note: Adjust table/column names based on your actual database schema
            $this->db->select('q.*, o.OrderID, o.OrderNumber, o.Customer_ID, o.TotalAmount, o.CreatedDate, 
                              CONCAT(u.First_Name, " ", u.Last_Name) as customer_name,
                              u.Email as customer_email, COALESCE(u.PhoneNum, u.Phone) as customer_phone,
                              CONCAT(sr.First_Name, " ", sr.Last_Name) as sales_rep_name,
                              p.ProductName as product_name');
            $this->db->from('quotation q');
            $this->db->join('`order` o', 'q.OrderID = o.OrderID', 'left');
            $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
            $this->db->join('user u', 'c.UserID = u.UserID', 'left');
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
            $this->db->join('employee e', 'o.SalesRep_ID = e.EmployeeID', 'left');
            $this->db->join('user sr', 'e.UserID = sr.UserID', 'left');
            $this->db->group_by('q.QuotationID');
            
            // Apply filters
            if ($status_filter && $status_filter !== 'all') {
                if ($this->db->field_exists('Status', 'quotation')) {
                    $this->db->where('q.Status', $status_filter);
                }
            }
            if ($date_start) {
                $this->db->where('DATE(q.Created_date) >=', $date_start);
            }
            if ($date_end) {
                $this->db->where('DATE(q.Created_date) <=', $date_end);
            }
            if ($client_search) {
                $this->db->group_start();
                $this->db->like('u.First_Name', $client_search);
                $this->db->or_like('u.Last_Name', $client_search);
                $this->db->or_like('u.Email', $client_search);
                $this->db->or_like('u.PhoneNum', $client_search);
                $this->db->group_end();
            }
            if ($sales_rep && $sales_rep !== 'all') {
                $this->db->where('e.EmployeeID', $sales_rep);
            }
            if ($amount_min) {
                $this->db->where('q.Total_amount >=', $amount_min);
            }
            if ($amount_max) {
                $this->db->where('q.Total_amount <=', $amount_max);
            }
            
            // Get total count
            $total_query = $this->db->get_compiled_select('', false);
            $total = $this->db->query("SELECT COUNT(*) as total FROM ($total_query) as count_query")->row()->total;
            
            // Apply pagination and get results
            $this->db->limit($limit, $offset);
            $quotations = $this->db->get()->result();
            
            $formatted_quotations = [];
            foreach ($quotations as $q) {
                $formatted_quotations[] = [
                    'quotation_id' => $q->QuotationID,
                    'quotation_number' => $q->Quotation_num,
                    'client_name' => $q->customer_name,
                    'sales_rep_name' => $q->sales_rep_name,
                    'product_name' => $q->product_name,
                    'total_amount' => $q->Total_amount,
                    'created_date' => $q->Created_date ? date('m/d/Y', strtotime($q->Created_date)) : 'N/A',
                    'status' => isset($q->Status) ? $q->Status : 'Pending'
                ];
            }
            
            echo json_encode([
                'success' => true,
                'quotations' => $formatted_quotations,
                'total' => (int) $total,
                'total_pages' => (int) ceil($total / $limit),
                'current_page' => (int) $page
            ]);
        } catch (Throwable $e) {
            log_message('error', 'get_quotations_ajax: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to load quotations. Check that the quotation table and related tables exist.', 'quotations' => [], 'total' => 0, 'total_pages' => 0, 'current_page' => 1]);
        }
    }
    
    public function get_quotation_details_ajax()
    {
        header('Content-Type: application/json');
        
        $quotation_id = $this->input->get('quotation_id');
        if (!$quotation_id) {
            echo json_encode(['success' => false, 'message' => 'Quotation ID required']);
            return;
        }
        
        // Get quotation details with related data
        $this->db->select('q.*, o.OrderID, o.OrderNumber, o.Customer_ID, o.TotalAmount, o.CreatedDate,
                          CONCAT(u.First_Name, " ", u.Last_Name) as customer_name,
                          u.Email as customer_email, u.Phone as customer_phone,
                          CONCAT(sr.First_Name, " ", sr.Last_Name) as sales_rep_name,
                          c.Address as customer_address');
        $this->db->from('quotation q');
        $this->db->join('`order` o', 'q.OrderID = o.OrderID', 'left');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->join('employee e', 'o.SalesRep_ID = e.EmployeeID', 'left');
        $this->db->join('user sr', 'e.UserID = sr.UserID', 'left');
        $this->db->where('q.QuotationID', $quotation_id);
        $quotation = $this->db->get()->row();
        
        if (!$quotation) {
            echo json_encode(['success' => false, 'message' => 'Quotation not found']);
            return;
        }
        
        // Get quotation items
        $this->db->select('oi.*, p.ProductName');
        $this->db->from('order_items oi');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->where('oi.OrderID', $quotation->OrderID);
        $items = $this->db->get()->result();
        
        $formatted_items = [];
        foreach ($items as $item) {
            $formatted_items[] = [
                'product_name' => $item->ProductName,
                'specifications' => $item->Specifications ?? 'N/A',
                'quantity' => $item->Quantity ?? 1,
                'unit_price' => $item->UnitPrice ?? 0,
                'subtotal' => ($item->UnitPrice ?? 0) * ($item->Quantity ?? 1)
            ];
        }
        
        // Check if converted to order
        $linked_order_id = null;
        if (isset($quotation->Status) && $quotation->Status === 'Converted to Order') {
            $linked_order_id = $quotation->OrderNumber;
        }
        
        echo json_encode([
            'success' => true,
            'quotation' => [
                'quotation_id' => $quotation->QuotationID,
                'quotation_number' => $quotation->Quotation_num,
                'created_date' => $quotation->Created_date ? date('m/d/Y', strtotime($quotation->Created_date)) : 'N/A',
                'expiry_date' => isset($quotation->ExpiryDate) ? date('m/d/Y', strtotime($quotation->ExpiryDate)) : 'N/A',
                'status' => isset($quotation->Status) ? $quotation->Status : 'Pending',
                'customer_name' => $quotation->customer_name,
                'customer_email' => $quotation->customer_email,
                'customer_phone' => $quotation->customer_phone,
                'customer_address' => $quotation->customer_address,
                'sales_rep_name' => $quotation->sales_rep_name,
                'total_amount' => $quotation->Total_amount,
                'items' => $formatted_items,
                'linked_order_id' => $linked_order_id,
                'admin_notes' => isset($quotation->AdminNotes) ? $quotation->AdminNotes : ''
            ]
        ]);
    }
    
    public function get_sales_reps_ajax()
    {
        header('Content-Type: application/json');
        try {
            if (!$this->db->table_exists('employee')) {
                echo json_encode(['success' => false, 'message' => 'Employee table not found.', 'sales_reps' => []]);
                return;
            }
            $this->db->select('e.EmployeeID as user_id, CONCAT(u.First_Name, " ", u.Last_Name) as name, u.First_Name as first_name, u.Last_Name as last_name');
            $this->db->from('employee e');
            $this->db->join('user u', 'e.UserID = u.UserID', 'left');
            $this->db->where('e.Role', 'Sales Representative');
            $sales_reps = $this->db->get()->result();
            
            echo json_encode([
                'success' => true,
                'sales_reps' => $sales_reps
            ]);
        } catch (Throwable $e) {
            log_message('error', 'get_sales_reps_ajax: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to load sales reps. Check that the employee table and Role column exist.', 'sales_reps' => []]);
        }
    }
    
    public function approve_quotation()
    {
        header('Content-Type: application/json');
        
        $quotation_id = $this->input->post('quotation_id');
        if (!$quotation_id) {
            echo json_encode(['success' => false, 'message' => 'Quotation ID required']);
            return;
        }
        
        // Update quotation status
        if ($this->db->field_exists('Status', 'quotation')) {
            $this->db->where('QuotationID', $quotation_id);
            $this->db->update('quotation', ['Status' => 'Approved']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Quotation approved successfully']);
    }
    
    public function reject_quotation()
    {
        header('Content-Type: application/json');
        
        $quotation_id = $this->input->post('quotation_id');
        $reason = $this->input->post('reason');
        
        if (!$quotation_id) {
            echo json_encode(['success' => false, 'message' => 'Quotation ID required']);
            return;
        }
        
        // Update quotation status
        $update_data = [];
        if ($this->db->field_exists('Status', 'quotation')) {
            $update_data['Status'] = 'Rejected';
        }
        if ($this->db->field_exists('RejectionReason', 'quotation')) {
            $update_data['RejectionReason'] = $reason;
        }
        
        if (!empty($update_data)) {
            $this->db->where('QuotationID', $quotation_id);
            $this->db->update('quotation', $update_data);
        }
        
        echo json_encode(['success' => true, 'message' => 'Quotation rejected successfully']);
    }
    
    public function convert_quotation_to_order()
    {
        header('Content-Type: application/json');
        
        $quotation_id = $this->input->post('quotation_id');
        if (!$quotation_id) {
            echo json_encode(['success' => false, 'message' => 'Quotation ID required']);
            return;
        }
        
        // Get quotation
        $this->db->where('QuotationID', $quotation_id);
        $quotation = $this->db->get('quotation')->row();
        
        if (!$quotation) {
            echo json_encode(['success' => false, 'message' => 'Quotation not found']);
            return;
        }
        
        // Update quotation status
        if ($this->db->field_exists('Status', 'quotation')) {
            $this->db->where('QuotationID', $quotation_id);
            $this->db->update('quotation', ['Status' => 'Converted to Order']);
        }
        
        // The order already exists (quotation is linked to an order)
        // Just update the order status if needed
        $this->db->where('OrderID', $quotation->OrderID);
        $this->db->update('`order`', ['Status' => 'Pending Review']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Quotation converted to order successfully',
            'order_id' => $quotation->OrderNumber
        ]);
    }
    
    public function save_quotation_notes()
    {
        header('Content-Type: application/json');
        
        $quotation_id = $this->input->post('quotation_id');
        $notes = $this->input->post('notes');
        
        if (!$quotation_id) {
            echo json_encode(['success' => false, 'message' => 'Quotation ID required']);
            return;
        }
        
        // Update admin notes if column exists
        if ($this->db->field_exists('AdminNotes', 'quotation')) {
            $this->db->where('QuotationID', $quotation_id);
            $this->db->update('quotation', ['AdminNotes' => $notes]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Notes saved successfully']);
    }
    
    // ======================
    // RETURN ORDERS AJAX METHODS
    // ======================
    
    public function get_return_orders_ajax()
    {
        header('Content-Type: application/json');
        
        $status_filter = $this->input->get('status');
        $date_start = $this->input->get('date_start');
        $date_end = $this->input->get('date_end');
        $client_search = $this->input->get('client_search');
        $order_search = $this->input->get('order_search');
        $return_type = $this->input->get('return_type');
        $page = $this->input->get('page') ?: 1;
        $limit = $this->input->get('limit') ?: 10;
        $offset = ($page - 1) * $limit;
        
        // Note: Adjust table/column names based on your actual database schema
        // Assuming a return_orders table exists, adjust as needed
        if (!$this->db->table_exists('return_orders')) {
            echo json_encode([
                'success' => true,
                'return_orders' => [],
                'total' => 0,
                'total_pages' => 0,
                'current_page' => $page
            ]);
            return;
        }
        
        $this->db->select('r.*, o.OrderNumber, o.CreatedDate as order_date,
                          CONCAT(u.First_Name, " ", u.Last_Name) as client_name,
                          p.ProductName as product_name');
        $this->db->from('return_orders r');
        $this->db->join('`order` o', 'r.OrderID = o.OrderID', 'left');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->join('product p', 'r.Product_ID = p.Product_ID', 'left');
        
        // Apply filters
        if ($status_filter && $status_filter !== 'all') {
            if ($this->db->field_exists('ReturnStatus', 'return_orders')) {
                $this->db->where('r.ReturnStatus', $status_filter);
            } elseif ($this->db->field_exists('Status', 'return_orders')) {
                $this->db->where('r.Status', $status_filter);
            }
        }
        
        if ($date_start) {
            if ($this->db->field_exists('ReturnDate', 'return_orders')) {
                $this->db->where('DATE(r.ReturnDate) >=', $date_start);
            }
        }
        if ($date_end) {
            if ($this->db->field_exists('ReturnDate', 'return_orders')) {
                $this->db->where('DATE(r.ReturnDate) <=', $date_end);
            }
        }
        
        if ($client_search) {
            $this->db->group_start();
            $this->db->like('u.First_Name', $client_search);
            $this->db->or_like('u.Last_Name', $client_search);
            $this->db->or_like('u.Email', $client_search);
            $this->db->or_like('u.Phone', $client_search);
            $this->db->group_end();
        }
        
        if ($order_search) {
            $this->db->like('o.OrderNumber', $order_search);
        }
        
        if ($return_type && $return_type !== 'all') {
            if ($this->db->field_exists('ReturnType', 'return_orders')) {
                $this->db->where('r.ReturnType', $return_type);
            } elseif ($this->db->field_exists('ReturnReason', 'return_orders')) {
                $this->db->like('r.ReturnReason', $return_type);
            }
        }
        
        // Get total count
        $total_query = $this->db->get_compiled_select('', false);
        $total = $this->db->query("SELECT COUNT(*) as total FROM ($total_query) as count_query")->row()->total;
        
        // Apply pagination
        $this->db->limit($limit, $offset);
        
        // Get results
        $return_orders = $this->db->get()->result();
        
        // Format results
        $formatted_returns = [];
        foreach ($return_orders as $r) {
            $status_field = $this->db->field_exists('ReturnStatus', 'return_orders') ? 'ReturnStatus' : 'Status';
            $return_date_field = $this->db->field_exists('ReturnDate', 'return_orders') ? 'ReturnDate' : 'CreatedDate';
            $reason_field = $this->db->field_exists('ReturnReason', 'return_orders') ? 'ReturnReason' : 'Reason';
            
            $formatted_returns[] = [
                'return_id' => $r->ReturnID ?? $r->ID,
                'return_number' => isset($r->ReturnNumber) ? $r->ReturnNumber : ($r->ReturnID ?? $r->ID),
                'original_order_id' => $r->OrderNumber,
                'client_name' => $r->client_name,
                'product_name' => $r->product_name,
                'return_date' => isset($r->$return_date_field) ? date('m/d/Y', strtotime($r->$return_date_field)) : 'N/A',
                'return_reason' => isset($r->$reason_field) ? $r->$reason_field : 'N/A',
                'status' => isset($r->$status_field) ? $r->$status_field : 'Pending'
            ];
        }
        
        echo json_encode([
            'success' => true,
            'return_orders' => $formatted_returns,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'current_page' => $page
        ]);
    }
    
    public function get_return_details_ajax()
    {
        header('Content-Type: application/json');
        
        $return_id = $this->input->get('return_id');
        if (!$return_id) {
            echo json_encode(['success' => false, 'message' => 'Return ID required']);
            return;
        }
        
        if (!$this->db->table_exists('return_orders')) {
            echo json_encode(['success' => false, 'message' => 'Return orders table not found']);
            return;
        }
        
        // Get return order details
        $this->db->select('r.*, o.OrderNumber, o.CreatedDate as order_date, o.TotalAmount as original_amount,
                          CONCAT(u.First_Name, " ", u.Last_Name) as client_name,
                          p.ProductName as product_name');
        $this->db->from('return_orders r');
        $this->db->join('`order` o', 'r.OrderID = o.OrderID', 'left');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->join('product p', 'r.Product_ID = p.Product_ID', 'left');
        $this->db->where('r.ReturnID', $return_id);
        $return_order = $this->db->get()->row();
        
        if (!$return_order) {
            echo json_encode(['success' => false, 'message' => 'Return order not found']);
            return;
        }
        
        // Get photos if field exists
        $photos = [];
        if ($this->db->field_exists('Photos', 'return_orders') && !empty($return_order->Photos)) {
            $photos = json_decode($return_order->Photos, true) ?: [];
        }
        
        $status_field = $this->db->field_exists('ReturnStatus', 'return_orders') ? 'ReturnStatus' : 'Status';
        $return_date_field = $this->db->field_exists('ReturnDate', 'return_orders') ? 'ReturnDate' : 'CreatedDate';
        $reason_field = $this->db->field_exists('ReturnReason', 'return_orders') ? 'ReturnReason' : 'Reason';
        $type_field = $this->db->field_exists('ReturnType', 'return_orders') ? 'ReturnType' : 'Type';
        
        echo json_encode([
            'success' => true,
            'return_order' => [
                'return_id' => $return_order->ReturnID ?? $return_order->ID,
                'return_number' => isset($return_order->ReturnNumber) ? $return_order->ReturnNumber : ($return_order->ReturnID ?? $return_order->ID),
                'return_date' => isset($return_order->$return_date_field) ? date('m/d/Y', strtotime($return_order->$return_date_field)) : 'N/A',
                'return_type' => isset($return_order->$type_field) ? $return_order->$type_field : 'N/A',
                'status' => isset($return_order->$status_field) ? $return_order->$status_field : 'Pending',
                'original_order_id' => $return_order->OrderNumber,
                'original_order_date' => $return_order->order_date ? date('m/d/Y', strtotime($return_order->order_date)) : 'N/A',
                'original_product_name' => $return_order->product_name,
                'original_amount' => $return_order->original_amount,
                'product_name' => $return_order->product_name,
                'quantity_returned' => isset($return_order->Quantity) ? $return_order->Quantity : 1,
                'return_reason' => isset($return_order->$reason_field) ? $return_order->$reason_field : 'N/A',
                'return_description' => isset($return_order->ReturnDescription) ? $return_order->ReturnDescription : 'N/A',
                'photos' => $photos,
                'replacement_required' => isset($return_order->ReplacementRequired) ? (bool)$return_order->ReplacementRequired : false,
                'replacement_product_name' => isset($return_order->ReplacementProductName) ? $return_order->ReplacementProductName : 'N/A',
                'replacement_order_id' => isset($return_order->ReplacementOrderID) ? $return_order->ReplacementOrderID : null,
                'replacement_appointment_id' => isset($return_order->ReplacementAppointmentID) ? $return_order->ReplacementAppointmentID : null,
                'refund_amount' => isset($return_order->RefundAmount) ? $return_order->RefundAmount : '',
                'refund_method' => isset($return_order->RefundMethod) ? $return_order->RefundMethod : '',
                'refund_status' => isset($return_order->RefundStatus) ? $return_order->RefundStatus : 'Pending',
                'refund_date' => isset($return_order->RefundDate) ? date('m/d/Y', strtotime($return_order->RefundDate)) : 'N/A',
                'admin_notes' => isset($return_order->AdminNotes) ? $return_order->AdminNotes : ''
            ]
        ]);
    }
    
    public function approve_return()
    {
        header('Content-Type: application/json');
        
        $return_id = $this->input->post('return_id');
        if (!$return_id) {
            echo json_encode(['success' => false, 'message' => 'Return ID required']);
            return;
        }
        
        if (!$this->db->table_exists('return_orders')) {
            echo json_encode(['success' => false, 'message' => 'Return orders table not found']);
            return;
        }
        
        $status_field = $this->db->field_exists('ReturnStatus', 'return_orders') ? 'ReturnStatus' : 'Status';
        $this->db->where('ReturnID', $return_id);
        $this->db->update('return_orders', [$status_field => 'Approved']);
        
        echo json_encode(['success' => true, 'message' => 'Return approved successfully']);
    }
    
    public function reject_return()
    {
        header('Content-Type: application/json');
        
        $return_id = $this->input->post('return_id');
        $reason = $this->input->post('reason');
        
        if (!$return_id) {
            echo json_encode(['success' => false, 'message' => 'Return ID required']);
            return;
        }
        
        if (!$this->db->table_exists('return_orders')) {
            echo json_encode(['success' => false, 'message' => 'Return orders table not found']);
            return;
        }
        
        $status_field = $this->db->field_exists('ReturnStatus', 'return_orders') ? 'ReturnStatus' : 'Status';
        $update_data = [$status_field => 'Rejected'];
        
        if ($this->db->field_exists('RejectionReason', 'return_orders')) {
            $update_data['RejectionReason'] = $reason;
        }
        
        $this->db->where('ReturnID', $return_id);
        $this->db->update('return_orders', $update_data);
        
        echo json_encode(['success' => true, 'message' => 'Return rejected successfully']);
    }
    
    public function process_refund()
    {
        header('Content-Type: application/json');
        
        $return_id = $this->input->post('return_id');
        $refund_amount = $this->input->post('refund_amount');
        $refund_method = $this->input->post('refund_method');
        
        if (!$return_id || !$refund_amount || !$refund_method) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }
        
        if (!$this->db->table_exists('return_orders')) {
            echo json_encode(['success' => false, 'message' => 'Return orders table not found']);
            return;
        }
        
        $update_data = [];
        if ($this->db->field_exists('RefundAmount', 'return_orders')) {
            $update_data['RefundAmount'] = $refund_amount;
        }
        if ($this->db->field_exists('RefundMethod', 'return_orders')) {
            $update_data['RefundMethod'] = $refund_method;
        }
        if ($this->db->field_exists('RefundStatus', 'return_orders')) {
            $update_data['RefundStatus'] = 'Processed';
        }
        if ($this->db->field_exists('RefundDate', 'return_orders')) {
            $update_data['RefundDate'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where('ReturnID', $return_id);
        $this->db->update('return_orders', $update_data);
        
        echo json_encode(['success' => true, 'message' => 'Refund processed successfully']);
    }
    
    public function create_replacement_order()
    {
        header('Content-Type: application/json');
        
        $return_id = $this->input->post('return_id');
        if (!$return_id) {
            echo json_encode(['success' => false, 'message' => 'Return ID required']);
            return;
        }
        
        if (!$this->db->table_exists('return_orders')) {
            echo json_encode(['success' => false, 'message' => 'Return orders table not found']);
            return;
        }
        
        // Get return order details
        $this->db->where('ReturnID', $return_id);
        $return_order = $this->db->get('return_orders')->row();
        
        if (!$return_order) {
            echo json_encode(['success' => false, 'message' => 'Return order not found']);
            return;
        }
        
        // Get original order
        $this->db->where('OrderID', $return_order->OrderID);
        $original_order = $this->db->get('`order`')->row();
        
        if (!$original_order) {
            echo json_encode(['success' => false, 'message' => 'Original order not found']);
            return;
        }
        
        // Create new order based on original order
        $new_order_data = [
            'Customer_ID' => $original_order->Customer_ID,
            'SalesRep_ID' => $original_order->SalesRep_ID,
            'OrderType' => $original_order->OrderType ?? 'Direct',
            'Status' => 'Pending Review',
            'TotalAmount' => $original_order->TotalAmount,
            'CreatedDate' => date('Y-m-d H:i:s'),
            'SpecialInstructions' => 'Replacement order for Return ID: ' . $return_id
        ];
        
        $this->db->insert('`order`', $new_order_data);
        $new_order_id = $this->db->insert_id();
        
        // Generate order number
        $order_number = 'ORD-' . str_pad($new_order_id, 6, '0', STR_PAD_LEFT);
        $this->db->where('OrderID', $new_order_id);
        $this->db->update('`order`', ['OrderNumber' => $order_number]);
        
        // Copy order items
        $this->db->where('OrderID', $original_order->OrderID);
        $order_items = $this->db->get('order_items')->result();
        
        foreach ($order_items as $item) {
            $new_item = [
                'OrderID' => $new_order_id,
                'Product_ID' => $item->Product_ID,
                'Quantity' => $item->Quantity,
                'UnitPrice' => $item->UnitPrice,
                'Specifications' => $item->Specifications
            ];
            $this->db->insert('order_items', $new_item);
        }
        
        // Update return order with replacement order ID
        if ($this->db->field_exists('ReplacementOrderID', 'return_orders')) {
            $this->db->where('ReturnID', $return_id);
            $this->db->update('return_orders', ['ReplacementOrderID' => $order_number]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Replacement order created successfully',
            'order_id' => $order_number
        ]);
    }
    
    public function schedule_replacement_appointment()
    {
        header('Content-Type: application/json');
        
        $return_id = $this->input->post('return_id');
        if (!$return_id) {
            echo json_encode(['success' => false, 'message' => 'Return ID required']);
            return;
        }
        
        if (!$this->db->table_exists('return_orders')) {
            echo json_encode(['success' => false, 'message' => 'Return orders table not found']);
            return;
        }
        
        // Get return order
        $this->db->where('ReturnID', $return_id);
        $return_order = $this->db->get('return_orders')->row();
        
        if (!$return_order) {
            echo json_encode(['success' => false, 'message' => 'Return order not found']);
            return;
        }
        
        // Get replacement order ID
        $replacement_order_id = null;
        if ($this->db->field_exists('ReplacementOrderID', 'return_orders') && !empty($return_order->ReplacementOrderID)) {
            $replacement_order_id = $return_order->ReplacementOrderID;
        }
        
        if (!$replacement_order_id) {
            echo json_encode(['success' => false, 'message' => 'Replacement order must be created first']);
            return;
        }
        
        // Get order details
        $this->db->where('OrderNumber', $replacement_order_id);
        $order = $this->db->get('`order`')->row();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Replacement order not found']);
            return;
        }
        
        // Create appointment
        $appointment_data = [
            'OrderID' => $order->OrderID,
            'Customer_ID' => $order->Customer_ID,
            'Service' => 'Installation',
            'Status' => 'Scheduled',
            'AppointmentDate' => date('Y-m-d', strtotime('+7 days')),
            'AppointmentTime' => '10:00:00'
        ];
        
        $this->db->insert('appointments', $appointment_data);
        $appointment_id = $this->db->insert_id();
        
        // Update return order with appointment ID
        if ($this->db->field_exists('ReplacementAppointmentID', 'return_orders')) {
            $this->db->where('ReturnID', $return_id);
            $this->db->update('return_orders', ['ReplacementAppointmentID' => $appointment_id]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Replacement appointment scheduled successfully',
            'appointment_id' => $appointment_id
        ]);
    }
    
    public function update_return_status()
    {
        header('Content-Type: application/json');
        
        $return_id = $this->input->post('return_id');
        $status = $this->input->post('status');
        
        if (!$return_id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Return ID and status required']);
            return;
        }
        
        if (!$this->db->table_exists('return_orders')) {
            echo json_encode(['success' => false, 'message' => 'Return orders table not found']);
            return;
        }
        
        $status_field = $this->db->field_exists('ReturnStatus', 'return_orders') ? 'ReturnStatus' : 'Status';
        $this->db->where('ReturnID', $return_id);
        $this->db->update('return_orders', [$status_field => $status]);
        
        echo json_encode(['success' => true, 'message' => 'Return status updated successfully']);
    }
    
    public function save_return_notes()
    {
        header('Content-Type: application/json');
        
        $return_id = $this->input->post('return_id');
        $notes = $this->input->post('notes');
        
        if (!$return_id) {
            echo json_encode(['success' => false, 'message' => 'Return ID required']);
            return;
        }
        
        if (!$this->db->table_exists('return_orders')) {
            echo json_encode(['success' => false, 'message' => 'Return orders table not found']);
            return;
        }
        
        if ($this->db->field_exists('AdminNotes', 'return_orders')) {
            $this->db->where('ReturnID', $return_id);
            $this->db->update('return_orders', ['AdminNotes' => $notes]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Notes saved successfully']);
    }

    // ===================== INVENTORY PRODUCTS MANAGEMENT =====================
    // Transferred from InventCon - now part of Admin functionality
    public function admin_inventory_products()
    {
        $this->load->model('Product_model');
        $this->load->model('Inventory_model');
        
        // Fetch products from the DB
        $products = $this->Product_model->get_products();
        
        // Get product materials and update status for each product
        $products_with_materials = [];
        foreach ($products as $product) {
            $product_materials = $this->Inventory_model->get_product_materials($product->Product_ID);
            $product->current_material_id = !empty($product_materials) ? $product_materials[0]->InventoryItemID : '';
            
            // Update product status based on materials
            $this->Inventory_model->update_product_status_from_materials($product->Product_ID);
            
            // Reload product to get updated status
            $product = $this->Product_model->get_product($product->Product_ID);
            $products_with_materials[] = $product;
        }
        $data['products'] = $products_with_materials;
        
        // Fetch inventory items (raw materials) for material dropdown
        $data['inventory_items'] = $this->Inventory_model->get_all_items();
        
        // Get unique categories for filter dropdown
        $categories = [];
        foreach ($products as $product) {
            if (!empty($product->Category) && !in_array($product->Category, $categories)) {
                $categories[] = $product->Category;
            }
        }
        $data['categories'] = $categories;
        
        $data['title'] = "Glassify - Inventory Products";
        $data['active'] = 'inventory_products';
        $data['content_view'] = 'inventory_page/inventory_products';
        $data['page_css'] = 'admin_css/admin_product.css';
        $this->load->view('admin_page/layout', $data);
    }

    // ===================== INVENTORY REPORTS =====================
    public function admin_inventory_reports()
    {
        $data['title'] = "Glassify - Inventory Reports";
        $data['active'] = 'inventory_reports';
        $data['content_view'] = 'inventory_page/inventory_reports';
        $data['page_css'] = 'inventory_css/inventory_reports.css';
        $this->load->view('admin_page/layout', $data);
    }
    
    /**
     * Export stock status report to Excel
     */
    public function admin_export_stock_report()
    {
        $this->load->model('Inventory_model');
        
        // Get all inventory items
        $items = $this->Inventory_model->get_all_items();
        
        // Set headers for Excel file
        $filename = 'Stock_Status_Report_' . date('Y-m-d_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Start output
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        
        // Create Excel content
        echo "<table border='1'>\n";
        
        // Header row
        echo "<tr style='background-color: #4CAF50; color: white; font-weight: bold;'>\n";
        echo "<th>Item ID</th>\n";
        echo "<th>Item Name</th>\n";
        echo "<th>Category</th>\n";
        echo "<th>Current Stock</th>\n";
        echo "<th>Min Threshold</th>\n";
        echo "<th>Status</th>\n";
        echo "<th>Unit</th>\n";
        echo "<th>Date Added</th>\n";
        echo "</tr>\n";
        
        // Data rows
        foreach ($items as $item) {
            $stock = $item->InStock ?? 0;
            $minThreshold = $item->min_threshold ?? 10;
            
            // Determine status
            $status = 'In Stock';
            if ($stock == 0) {
                $status = 'Out of Stock';
            } elseif ($stock < $minThreshold) {
                $status = 'Low Stock';
            }
            
            echo "<tr>\n";
            echo "<td>" . htmlspecialchars($item->ItemID ?? 'N/A') . "</td>\n";
            echo "<td>" . htmlspecialchars($item->Name ?? 'N/A') . "</td>\n";
            echo "<td>" . htmlspecialchars($item->Category ?? 'N/A') . "</td>\n";
            echo "<td>" . $stock . "</td>\n";
            echo "<td>" . $minThreshold . "</td>\n";
            echo "<td>" . htmlspecialchars($status) . "</td>\n";
            echo "<td>" . htmlspecialchars($item->Unit ?? 'N/A') . "</td>\n";
            echo "<td>" . ($item->DateAdded ? date('Y-m-d H:i:s', strtotime($item->DateAdded)) : 'N/A') . "</td>\n";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
        exit;
    }

    /**
     * Get unread inventory notification count (AJAX endpoint)
     */
    public function admin_get_inventory_notification_count_ajax()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['status' => 'error', 'count' => 0]);
            return;
        }
        
        $this->load->model('Inventory_model');
        $this->db->where('Status', 'Unread');
        $count = $this->db->count_all_results('inventory_notifications');
        
        // Limit to 99, show 99+ if more
        if ($count > 99) {
            $display_count = '99+';
        } else {
            $display_count = $count;
        }
        
        echo json_encode(['status' => 'success', 'count' => $count, 'display' => $display_count]);
    }

  
}
