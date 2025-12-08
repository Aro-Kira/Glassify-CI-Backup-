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
        // Join with order table and get ProductName from order_items -> product
        $this->db->select('a.*, p.ProductName, o.OrderID, o.OrderNumber as ApprovedOrderID');
        $this->db->from('appointments a');
        $this->db->join('`order` o', 'a.OrderID = o.OrderID', 'left');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->group_by('a.AppointmentID'); // Group to avoid duplicates from multiple order_items
        
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
        
        // Build count query first (simple, no joins to avoid duplication)
        $this->db->from('`order` o');
        
        // Apply status filter
        if ($status_filter && $status_filter !== 'all orders') {
            switch ($status_filter) {
                case 'completed':
                    $this->db->where('o.Status', 'Completed');
                    break;
                case 'pending':
                    // Use 'Pending Review' status (legacy 'Pending' status is deprecated)
                    $this->db->where('o.Status', 'Pending Review');
                    break;
                case 'cancel':
                    $this->db->where('o.Status', 'Cancelled');
                    break;
            }
        }
        
        // Apply search filter for count
        if ($search) {
            $this->db->group_start();
            $this->db->like('o.OrderNumber', $search);
            $this->db->or_like('o.OrderID', $search);
            $this->db->or_like('o.DeliveryAddress', $search);
            // For product name search, use subquery
            $this->db->or_where("EXISTS (SELECT 1 FROM order_items oi2 JOIN product p2 ON oi2.Product_ID = p2.Product_ID WHERE oi2.OrderID = o.OrderID AND p2.ProductName LIKE '%" . $this->db->escape_like_str($search) . "%')", null, false);
            $this->db->group_end();
        }
        
        // Apply date filter
        if ($month !== null && $month !== '' && $year) {
            $this->db->where('MONTH(o.OrderDate)', $month + 1);
            $this->db->where('YEAR(o.OrderDate)', $year);
        }
        
        $total_count = $this->db->count_all_results();
        
        // Now build the data query with joins
        $this->db->select('
            o.OrderID, 
            o.OrderNumber,
            o.OrderDate, 
            o.TotalAmount, 
            o.Status, 
            o.PaymentStatus, 
            o.DeliveryAddress, 
            c.Customer_ID, 
            u.First_Name, 
            u.Last_Name, 
            u.Middle_Name,
            p.ProductName
        ');
        $this->db->from('`order` o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->group_by('o.OrderID');
        
        // Reapply filters for data query
        if ($status_filter && $status_filter !== 'all orders') {
            switch ($status_filter) {
                case 'completed':
                    $this->db->where('o.Status', 'Completed');
                    break;
                case 'pending':
                    // Use 'Pending Review' status (legacy 'Pending' status is deprecated)
                    $this->db->where('o.Status', 'Pending Review');
                    break;
                case 'cancel':
                    $this->db->where('o.Status', 'Cancelled');
                    break;
            }
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('o.OrderNumber', $search);
            $this->db->or_like('o.OrderID', $search);
            $this->db->or_like('o.DeliveryAddress', $search);
            $this->db->or_like('p.ProductName', $search);
            $this->db->group_end();
        }
        
        if ($month !== null && $month !== '' && $year) {
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
     * Uses Order_model->get_order_details_for_popup() for normalized data
     */
    public function get_order_details_ajax()
    {
        header('Content-Type: application/json');
        
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
        $order_id_formatted = '#' . ($order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT));
        
        // Format response
        $response = [
            'success' => true,
            'order' => [
                'order_id' => $order_id_formatted,
                'product_name' => $order->ProductName ?? 'N/A',
                'address' => $order->DeliveryAddress ?? 'N/A',
                'date' => $order->OrderDate ? date('d/m/Y', strtotime($order->OrderDate)) : 'N/A',
                'status' => $this->map_status_to_display($order->Status ?? 'Pending Review'),
                'total_quotation' => number_format($order->TotalAmount ?? 0, 2, '.', ''),
                'customer_name' => $customer_name,
                'customer_email' => $order->Email ?? 'N/A',
                'customer_phone' => $order->PhoneNum ?? 'N/A',
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
                'preferred_installation_date' => $preferred_installation_date
            ]
        ];
        
        echo json_encode($response);
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
            
            // Get sales rep name
            $sales_rep_name = 'N/A';
            if (isset($order->SalesRep_First_Name) && !empty($order->SalesRep_First_Name)) {
                $sales_rep_name = trim(($order->SalesRep_First_Name ?? '') . ' ' . ($order->SalesRep_Last_Name ?? ''));
                if (empty(trim($sales_rep_name))) {
                    $sales_rep_name = 'N/A';
                }
            } elseif (isset($order->SalesRep_ID) && !empty($order->SalesRep_ID)) {
                try {
                    $sales_rep = $this->User_model->get_by_id($order->SalesRep_ID);
                    $sales_rep_name = ($sales_rep && isset($sales_rep->First_Name)) 
                        ? trim($sales_rep->First_Name . ' ' . ($sales_rep->Last_Name ?? '')) 
                        : 'N/A';
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
                        'sales_rep_name' => $sales_rep_name,
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
        
        // Extract numeric OrderID for unified order table
        $order_id_numeric = (int)str_replace('GI', '', $order_id_clean);
        
        // Update order status using Order_model transaction function
        $this->load->model('Order_model');
        // Note: update_order_status doesn't handle disapproval reason, so we'll update manually
        $this->db->where('OrderID', $order_id_numeric);
        $this->db->update('order', [
            'Status' => 'Disapproved',
            'DisapprovedBy' => 'Admin',
            'DisapprovedBy_ID' => $admin_id,
            'DisapprovalReason' => $disapproval_reason,
            'Disapproved_Date' => date('Y-m-d H:i:s')
        ]);
        
        // Insert into disapproved_orders (legacy table for backward compatibility)
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

  
}
