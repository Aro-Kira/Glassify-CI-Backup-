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
        
        // If a logged-in customer tries to access admin pages, redirect them (but DON'T log them out)
        if ($this->session->userdata('is_logged_in') && $this->session->userdata('user_role') === 'Customer') {
            // For AJAX requests, return JSON error
            if ($this->input->is_ajax_request() || 
                (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Access denied. Admin only.']);
                exit;
            }
            // For regular requests, redirect to customer dashboard without logging out
            $this->session->set_flashdata('error', '⚠️ Access Denied: This page is restricted to Admin employees only.');
            redirect(base_url('customer/dashboard'));
        }
        
        // Check if user is logged in and has Admin role
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            // If it's an AJAX request or API endpoint, return JSON unauthorized instead of redirecting
            $is_ajax = $this->input->is_ajax_request() || 
                       isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
                       (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
                       (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
            
            // Also check for known API method names
            $method = $this->uri->segment(2);
            $api_methods = ['process_installation_date_request', 'get_appointments_ajax', 'get_installation_date_change_requests', 
                           'update_appointment_status', 'get_order_details_ajax', 'update_order_status_ajax', 'get_orders_ajax'];
            
            if ($is_ajax || in_array($method, $api_methods)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
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
     * Get dashboard statistics from order table
     */
    private function get_dashboard_stats()
    {
        $stats = [];
        
        try {
            // Check if order table exists
            if (!$this->db->table_exists('order')) {
                return [
                    'total_orders' => 0,
                    'pending_orders' => 0,
                    'in_fabrication' => 0,
                    'ready_for_installation' => 0,
                    'completed_orders' => 0,
                    'total_orders_month' => 0
                ];
            }
            
            // Get total orders this month
            $this->db->where('MONTH(OrderDate)', date('n'));
            $this->db->where('YEAR(OrderDate)', date('Y'));
            $stats['total_orders_month'] = $this->db->count_all_results('`order`');
            
            // Get pending orders (Pending Review + Awaiting Admin)
            $this->db->reset_query();
            $this->db->where_in('Status', ['Pending Review', 'Awaiting Admin', 'Approved', 'Ocular Pending']);
            $stats['pending_orders'] = $this->db->count_all_results('`order`');
            
            // Get orders in fabrication
            $this->db->reset_query();
            $this->db->where('Status', 'In Fabrication');
            $stats['in_fabrication'] = $this->db->count_all_results('`order`');
            
            // Get orders ready for installation
            $this->db->reset_query();
            $this->db->where('Status', 'Ready for Installation');
            $stats['ready_for_installation'] = $this->db->count_all_results('`order`');
            
            // Get completed orders
            $this->db->reset_query();
            $this->db->where('Status', 'Completed');
            $stats['completed_orders'] = $this->db->count_all_results('`order`');
            
        }
        catch (Exception $e) {
            log_message('error', 'Error getting dashboard stats: ' . $e->getMessage());
            return [
                'total_orders' => 0,
                'pending_orders' => 0,
                'in_fabrication' => 0,
                'ready_for_installation' => 0,
                'completed_orders' => 0,
                'total_orders_month' => 0
            ];
        }
        
        return $stats;
    }

    /**
     * Safe fallback: today's appointments (empty list)
     */
    private function get_today_appointments()
    {
        return [];
    }

    /**
     * Safe fallback: recent activity log entries (empty list)
     */
    private function get_recent_activities()
    {
        return [];
    }

    // Get installation date change requests
    public function get_installation_date_change_requests()
    {
        header('Content-Type: application/json');

        try {
            if ($this->input->get('debug')) {
                echo json_encode(['success' => true, 'message' => 'get_installation_date_change_requests OK']);
                return;
            }

            if (!$this->db->table_exists('system_activity_log')) {
                echo json_encode(['success' => true, 'requests' => []]);
                return;
            }

            // Find recent installation date change request entries
            $this->db->select('*');
            $this->db->from('system_activity_log');
            $this->db->like('Action', 'Installation Date Change Request');
            $this->db->or_like('Description', 'installation date change');
            $this->db->order_by('Timestamp', 'DESC');
            $this->db->limit(200);
            $logs = $this->db->get()->result();

            $results = [];

            // Load Order_model if the order table exists
            if ($this->db->table_exists('order')) {
                $this->load->model('Order_model');
            }

            foreach ($logs as $log) {
                $description = $log->Description ?? '';
                $requested_date = null;
                $order_number = null;

                // Parse order number (e.g., GI001 or numeric)
                if (preg_match('/order\s+#?([A-Za-z0-9\-]+)/i', $description, $m)) {
                    $order_number = $m[1];
                }

                // Parse requested date (YYYY-MM-DD or MM/DD/YYYY)
                if (preg_match('/to\s+([0-9]{4}-[0-9]{2}-[0-9]{2})/', $description, $m2)) {
                    $requested_date = $m2[1];
                } elseif (preg_match('/to\s+([0-9]{2}\/[0-9]{2}\/[0-9]{4})/', $description, $m3)) {
                    $parts = explode('/', $m3[1]);
                    if (count($parts) == 3) $requested_date = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                }

                $order = null;
                $order_id = null;
                $order_status = null;
                $product_name = null;

                if ($order_number && isset($this->Order_model) && method_exists($this->Order_model, 'get_order')) {
                    $order = $this->Order_model->get_order($order_number);
                }

                // If not found by order number, try to extract numeric ID
                if (!$order && preg_match('/\b(\d{1,7})\b/', $description, $m4)) {
                    $maybe = (int)$m4[1];
                    $this->db->reset_query();
                    $this->db->where('OrderID', $maybe);
                    $order = $this->db->get('`order`')->row();
                }

                if ($order) {
                    $order_id = $order->OrderID ?? null;
                    $order_status = $order->Status ?? null;

                    // Try to get a product name from order_items/product
                    if ($this->db->table_exists('order_items') && $this->db->table_exists('product')) {
                        $this->db->reset_query();
                        $this->db->select('p.ProductName');
                        $this->db->from('order_items oi');
                        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
                        $this->db->where('oi.OrderID', $order_id);
                        $item = $this->db->get()->row();
                        if ($item) $product_name = $item->ProductName ?? null;
                    }
                }

                // Get current installation appointment date/status if exists
                $current_date = null;
                $appointment_status = null;
                if ($order_id && $this->db->table_exists('appointments')) {
                    $this->db->reset_query();
                    $this->db->where('OrderID', $order_id);
                    $this->db->where('Service', 'Installed');
                    $apt = $this->db->get('appointments')->row();
                    if ($apt) {
                        $current_date = $apt->AppointmentDate ?? null;
                        $appointment_status = $apt->Status ?? null;
                        if (!$product_name && property_exists($apt, 'ProductName')) {
                            $product_name = $apt->ProductName;
                        }
                    }
                }

                // Determine an ID value from common column names
                $logVars = get_object_vars($log);
                $possibleId = null;
                foreach (['id','ID','log_id','Log_ID','ActivityID','activity_id'] as $k) {
                    if (array_key_exists($k, $logVars)) { $possibleId = $logVars[$k]; break; }
                }

                $results[] = [
                    // JS expects `id` and `client_name`, `specs` fields
                    'id' => $possibleId,
                    'order_id' => $order_id,
                    'order_number' => $order_number ?? ($order->OrderNumber ?? null),
                    'client_name' => $log->UserName ?? ($order->CustomerName ?? null) ?? ($logVars['UserName'] ?? null),
                    'requested_date' => $requested_date,
                    'current_date' => $current_date,
                    'specs' => $product_name ?? 'N/A',
                    'assigned_staff' => null,
                    'order_status' => $order_status,
                    'appointment_status' => $appointment_status,
                    'description' => $description,
                    'timestamp' => $log->Timestamp ?? ($logVars['Timestamp'] ?? null)
                ];
            }

            echo json_encode(['success' => true, 'requests' => $results]);
            return;
        }
        catch (Exception $e) {
            log_message('error', 'Exception in get_installation_date_change_requests: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error']);
            return;
        }
    }

    /**
     * Process an installation date change request (approve/disapprove)
     * POST params: request_id, action ('approve'|'disapprove')
     */
    public function process_installation_date_request()
    {
        // Ensure AJAX consumers always get JSON and suppress direct error HTML
        header('Content-Type: application/json');
        error_reporting(E_ALL);
        ini_set('display_errors', 0);

        try {
            if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }

        $request_id = $this->input->post('request_id');
        $action = $this->input->post('action');

        if (!$request_id || !in_array($action, ['approve', 'disapprove'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }

        // Fetch the activity log entry
        $this->db->where('ActivityID', $request_id);
        $log = $this->db->get('system_activity_log')->row();
        if (!$log) {
            echo json_encode(['success' => false, 'message' => 'Request not found']);
            return;
        }

        // Parse order number and requested date from description (reuse same parsing as elsewhere)
        $order_number = null;
        $requested_date = null;
        if (!empty($log->Description)) {
            if (preg_match('/order\s+#?([A-Za-z0-9\-]+)/i', $log->Description, $m)) {
                $order_number = $m[1];
            }
            if (preg_match('/to\s+([0-9]{4}-[0-9]{2}-[0-9]{2})/', $log->Description, $m2)) {
                $requested_date = $m2[1];
            } elseif (preg_match('/to\s+([0-9]{2}\/[0-9]{2}\/[0-9]{4})/', $log->Description, $m3)) {
                $parts = explode('/', $m3[1]);
                if (count($parts) == 3) $requested_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
        }

        // Locate order by OrderNumber or attempt numeric
        $order = null;
        if ($order_number) {
            $this->load->model('Order_model');
            $order = $this->Order_model->get_order($order_number);
        }

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found for this request']);
            return;
        }

        $order_id = $order->OrderID ?? null;

        // Get admin name
        $admin_name = $this->session->userdata('user_name') ?: ($this->session->userdata('user_email') ?: 'Admin');

        if ($action === 'approve') {
            // Update installation appointment and order InstallationDate
            // Find Installed appointment
            $this->db->where('OrderID', $order_id);
            $this->db->where('Service', 'Installed');
            $apt = $this->db->get('appointments')->row();

            if ($apt && $requested_date) {
                $this->db->where('AppointmentID', $apt->AppointmentID);
                $this->db->update('appointments', ['AppointmentDate' => $requested_date, 'Updated_Date' => date('Y-m-d H:i:s')]);
            }

            if ($this->db->field_exists('InstallationDate', 'order') && $requested_date) {
                $this->db->where('OrderID', $order_id);
                // Use raw query to avoid issues with 'order' being a reserved keyword
                $this->db->query("UPDATE `order` SET InstallationDate = ? WHERE OrderID = ?", [$requested_date, $order_id]);
            }

            // Log approval
            $desc = "Admin {$admin_name} approved installation date change for order #{$order->OrderNumber} to {$requested_date}";
            if ($this->db->table_exists('system_activity_log')) {
                $this->db->insert('system_activity_log', [
                    'Action' => 'Installation Date Change Approved',
                    'Description' => $desc,
                    'Role' => 'Admin',
                    'UserName' => $admin_name,
                    'Timestamp' => date('Y-m-d H:i:s')
                ]);
            }

            // Notify customer by email if possible
            try {
                $this->db->reset_query();
                $this->db->select('u.Email, u.First_Name, u.Last_Name');
                $this->db->from('`order` o');
                $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
                $this->db->join('user u', 'u.UserID = c.UserID', 'left');
                $this->db->where('o.OrderID', $order_id);
                $cust = $this->db->get()->row();

                if ($cust && !empty($cust->Email)) {
                    $this->load->library('email');
                    $this->email->set_mailtype('html');
                    $from_email = config_item('smtp_user') ?: 'no-reply@localhost';
                    $from_name = config_item('site_name') ?: 'Glassify';
                    $this->email->from($from_email, $from_name);
                    $this->email->to($cust->Email);
                    $subject = "Installation Date Change Approved - Order {$order->OrderNumber}";
                    $body = '<p>' . htmlspecialchars($desc) . '</p>';
                    $this->email->subject($subject);
                    $this->email->message($body);
                    $this->email->send();
                }
            }
        catch (Exception $e) {
                log_message('error', 'Error sending approval email: ' . $e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => 'Request approved and installation date updated.']);
            return;
        }

        // Disapprove
        $reason = $this->input->post('reason');
        $reason_text = $reason ? " Reason: {$reason}" : '';
        $desc = "Admin {$admin_name} disapproved installation date change request for order #{$order->OrderNumber}.{$reason_text}";
        if ($this->db->table_exists('system_activity_log')) {
            $this->db->insert('system_activity_log', [
                'Action' => 'Installation Date Change Disapproved',
                'Description' => $desc,
                'Role' => 'Admin',
                'UserName' => $admin_name,
                'Timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        // Notify customer email
        try {
            $this->db->reset_query();
            $this->db->select('u.Email, u.First_Name, u.Last_Name');
            $this->db->from('`order` o');
            $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
            $this->db->join('user u', 'u.UserID = c.UserID', 'left');
            $this->db->where('o.OrderID', $order_id);
            $cust = $this->db->get()->row();

            if ($cust && !empty($cust->Email)) {
                $this->load->library('email');
                $this->email->set_mailtype('html');
                $from_email = config_item('smtp_user') ?: 'no-reply@localhost';
                $from_name = config_item('site_name') ?: 'Glassify';
                $this->email->from($from_email, $from_name);
                $this->email->to($cust->Email);
                $subject = "Installation Date Change Disapproved - Order {$order->OrderNumber}";
                $customer_name = trim(($cust->First_Name ?? '') . ' ' . ($cust->Last_Name ?? '')) ?: 'Valued Customer';
                $body = '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                        <h2 style="color: #dc3545;">Installation Date Change Request Disapproved</h2>
                        <p>Dear ' . htmlspecialchars($customer_name) . ',</p>
                        <p>We regret to inform you that your request to change the installation date for <strong>Order #' . htmlspecialchars($order->OrderNumber) . '</strong> has been disapproved.</p>
                        ' . ($reason ? '<div style="background: #f8f9fa; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;"><strong>Reason:</strong><br>' . nl2br(htmlspecialchars($reason)) . '</div>' : '') . '
                        <p>Your current installation date remains unchanged. If you have any questions or concerns, please contact us.</p>
                        <p>Thank you for your understanding.</p>
                        <p style="margin-top: 30px; color: #666;">Best regards,<br>The Glassify Team</p>
                    </div>
                ';
                $this->email->subject($subject);
                $this->email->message($body);
                $this->email->send();
            }
        }
        catch (Exception $e) {
            log_message('error', 'Error sending disapproval email: ' . $e->getMessage());
        }

        echo json_encode(['success' => true, 'message' => 'Request disapproved. Customer has been notified.']);
        return;
        }
        catch (Exception $e) {
            log_message('error', 'process_installation_date_request - Exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
            return;
        } catch (Error $e) {
            log_message('error', 'process_installation_date_request - Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Fatal server error: ' . $e->getMessage()]);
            return;
        }
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
        // Quick debug gate: visit with ?debug=1 to verify controller executes
        if ($this->input->get('debug')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'AdminCon::admin_appointment controller OK', 'type' => $type]);
            return;
        }
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
     * Get product customization data for 2D preview in appointment details
     * Returns tag prices, images, visual configs, field config, and selected options
     */
    public function get_product_customization_data()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->get('order_id');
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            return;
        }
        
        // Get the first order item and its product
        $order_item = null;
        $product = null;
        if ($this->db->table_exists('order_items') && $this->db->table_exists('product')) {
            // Build select fields based on what exists
            $select_fields = 'oi.*, p.Product_ID, p.ProductName, p.Category, p.Subcategory, p.ImageUrl, p.Material, p.Price';
            if ($this->db->field_exists('Customization', 'product')) {
                $select_fields .= ', p.Customization as ProductCustomization';
            }
            
            $this->db->select($select_fields);
            $this->db->from('order_items oi');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
            $this->db->where('oi.OrderID', $order_id);
            $order_item = $this->db->get()->row();
        }
        
        if (!$order_item) {
            echo json_encode(['success' => false, 'message' => 'No order item found']);
            return;
        }
        
        // Build field key from category and subcategory
        $category = $order_item->Category ?? '';
        $subcategory = $order_item->Subcategory ?? '';
        $fieldKey = '';
        if ($category && $subcategory) {
            $prefixMap = [
                'Windows' => 'Windows',
                'Doors' => 'Doors',
                'Glass Partitions & Enclosures' => 'Partitions',
                'Mirrors & Specialty Glass' => 'Specialty',
                'Commercial & Exterior' => 'Commercial'
            ];
            $prefix = $prefixMap[$category] ?? '';
            $fieldKey = $prefix ? "{$prefix}_{$subcategory}" : $subcategory;
        }
        
        // Get field configuration from customization_field_configs table
        $fieldConfig = [];
        $stepNames = null;
        if ($fieldKey && $this->db->table_exists('customization_field_configs')) {
            $this->db->where('FieldKey', $fieldKey);
            $config = $this->db->get('customization_field_configs')->row();
            if ($config && !empty($config->FieldConfig)) {
                $decoded = json_decode($config->FieldConfig, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $key => $value) {
                        if (is_numeric($key) && is_array($value) && isset($value['type'])) {
                            $fieldConfig[] = $value;
                        } elseif ($key === 'stepNames') {
                            $stepNames = $value;
                        }
                    }
                }
            }
        }
        
        // Get tag prices, images, and visual configs
        $tagPrices = [];
        $tagImages = [];
        $tagVisualConfigs = [];
        if ($this->db->table_exists('product_tag_prices')) {
            $this->db->where('Product_ID', $order_item->Product_ID);
            $tagPricesResult = $this->db->get('product_tag_prices')->result();
            foreach ($tagPricesResult as $tagPrice) {
                if (!isset($tagPrices[$tagPrice->FieldID])) {
                    $tagPrices[$tagPrice->FieldID] = [];
                }
                $tagPrices[$tagPrice->FieldID][$tagPrice->TagName] = floatval($tagPrice->Price);
                
                if (!empty($tagPrice->ImageUrl)) {
                    if (!isset($tagImages[$tagPrice->FieldID])) {
                        $tagImages[$tagPrice->FieldID] = [];
                    }
                    $tagImages[$tagPrice->FieldID][$tagPrice->TagName] = base_url('uploads/tags/' . $tagPrice->ImageUrl);
                }
                
                if (isset($tagPrice->VisualConfig) && !empty($tagPrice->VisualConfig)) {
                    if (!isset($tagVisualConfigs[$tagPrice->FieldID])) {
                        $tagVisualConfigs[$tagPrice->FieldID] = [];
                    }
                    $decoded_vc = json_decode($tagPrice->VisualConfig, true);
                    if ($decoded_vc) {
                        $tagVisualConfigs[$tagPrice->FieldID][$tagPrice->TagName] = $decoded_vc;
                    }
                }
            }
        }
        
        // Get product's selected customization options
        $productSelectedOptions = [];
        if (!empty($order_item->ProductCustomization)) {
            $decoded = json_decode($order_item->ProductCustomization, true);
            if (is_array($decoded)) {
                $productSelectedOptions = $decoded;
            }
        }
        
        // Get standard series and sizes
        $standardSeries = [];
        if ($this->db->table_exists('product_series') && $this->db->table_exists('product_standard_sizes')) {
            $this->db->where('Product_ID', $order_item->Product_ID);
            $seriesResult = $this->db->get('product_series')->result();
            foreach ($seriesResult as $series) {
                $this->db->where('Series_ID', $series->Series_ID);
                $measurementsResult = $this->db->get('product_standard_sizes')->result();
                $measurements = [];
                foreach ($measurementsResult as $measurement) {
                    $measurements[] = [
                        'width' => floatval($measurement->Width),
                        'height' => floatval($measurement->Height),
                        'price' => floatval($measurement->Price)
                    ];
                }
                $standardSeries[] = [
                    'id' => intval($series->Series_ID),
                    'name' => $series->SeriesName,
                    'measurements' => $measurements
                ];
            }
        }
        
        // Get customer's customization selections (from customization table)
        $customerSelections = [];
        if ($order_item->CustomizationID && $this->db->table_exists('customization')) {
            $this->db->where('CustomizationID', $order_item->CustomizationID);
            $cust = $this->db->get('customization')->row();
            if ($cust) {
                // Parse Customization JSON for saved selections
                if (!empty($cust->Customization)) {
                    $parsed = json_decode($cust->Customization, true);
                    if (is_array($parsed)) {
                        $customerSelections = $parsed;
                    }
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'product' => [
                'id' => $order_item->Product_ID,
                'name' => $order_item->ProductName ?? '',
                'category' => $category,
                'subcategory' => $subcategory,
                'material' => $order_item->Material ?? '',
                'image' => !empty($order_item->ImageUrl) ? base_url($order_item->ImageUrl) : null,
                'price' => floatval($order_item->Price ?? 0)
            ],
            'fieldKey' => $fieldKey,
            'fieldConfig' => $fieldConfig,
            'stepNames' => $stepNames,
            'tagPrices' => (object) $tagPrices,
            'tagImages' => (object) $tagImages,
            'tagVisualConfigs' => (object) $tagVisualConfigs,
            'selectedOptions' => (object) $productSelectedOptions,
            'standardSeries' => $standardSeries,
            'customerSelections' => (object) $customerSelections,
            'order_item' => [
                'dimensions' => $order_item->Dimensions ?? null,
                'quantity' => $order_item->Quantity ?? null,
                'unit_price' => $order_item->UnitPrice ?? null,
                'estimate_price' => $order_item->EstimatePrice ?? null
            ]
        ]);
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

            if (!$this->db->table_exists('appointments')) {
                echo json_encode([
                    'success' => true,
                    'appointments' => [],
                    'total' => 0,
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'total_pages' => 0
                ]);
                return;
            }

            $order_items_table_exists = $this->db->table_exists('order_items');
            $product_table_exists = $this->db->table_exists('product');
            $customer_table_exists = $this->db->table_exists('customer');
            $user_table_exists = $this->db->table_exists('user');
            $order_type_field_exists = $this->db->field_exists('OrderType', 'order');
            $order_number_field_exists = $this->db->field_exists('OrderNumber', 'order');
            $appointment_product_field_exists = $this->db->field_exists('ProductName', 'appointments');
            $appointment_assigned_staff_field_exists = $this->db->field_exists('AssignedStaff', 'appointments');
            $oi_dimensions_exists = $order_items_table_exists && $this->db->field_exists('Dimensions', 'order_items');
            $oi_quantity_exists = $order_items_table_exists && $this->db->field_exists('Quantity', 'order_items');
            $oi_unit_price_exists = $order_items_table_exists && $this->db->field_exists('UnitPrice', 'order_items');
            $oi_estimate_price_exists = $order_items_table_exists && $this->db->field_exists('EstimatePrice', 'order_items');
            $customization_table_exists = $this->db->table_exists('customization');
            $customer_user_join_exists = $customer_table_exists && $user_table_exists
                && $this->db->field_exists('Customer_ID', 'customer')
                && $this->db->field_exists('UserID', 'customer')
                && $this->db->field_exists('UserID', 'user')
                && $this->db->field_exists('First_Name', 'user')
                && $this->db->field_exists('Last_Name', 'user');
            
            // First, ensure all approved orders have appointment records
            // Reset query builder before sync to avoid conflicts
            $this->db->reset_query();
            try {
                $this->sync_approved_orders_to_appointments();
            }
        catch (Exception $e) {
                // Log error but don't fail the request
                log_message('error', 'AdminCon::get_appointments_ajax - Sync error: ' . $e->getMessage());
            }
            // Reset again after sync to ensure clean state
            $this->db->reset_query();
        
        // Build base query structure (from, joins) - without select or group_by yet
        $this->db->from('appointments a');
        $this->db->join('`order` o', 'a.OrderID = o.OrderID', 'left');
        if ($customer_user_join_exists) {
            $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
            $this->db->join('user u', 'u.UserID = c.UserID', 'left');
        }
        if ($order_items_table_exists) {
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        }
        if ($order_items_table_exists && $product_table_exists) {
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        }
        
        // Apply appointment type filter (ocular or installation)
        if ($appointment_type) {
            if ($appointment_type === 'ocular') {
                $this->db->where('a.Service', 'Ocular Visit');
                // Note: We'll filter out completed ocular appointments that have fabrication appointments
                // in the PHP code after fetching to avoid complex SQL queries
            } elseif ($appointment_type === 'installation') {
                $this->db->where('a.Service', 'Installed');
            }
        }

        // Only show appointments for approved or later orders
        if ($this->db->field_exists('Status', 'order')) {
            $this->db->where_in('o.Status', ['Ocular Pending', 'Approved', 'In Fabrication', 'Ready for Installation', 'Completed']);
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
            if ($appointment_product_field_exists) {
                $this->db->or_like('a.ProductName', $search);
            }
            // If search looks like an order number (GI001), search by OrderNumber
            if ($order_number_field_exists && preg_match('/^GI\d+$/i', $search)) {
                $this->db->or_like('o.OrderNumber', $search);
            } else {
                // Otherwise, try numeric OrderID
                if (is_numeric($search)) {
                    $this->db->or_where('a.OrderID', (int)$search);
                } else {
                    $this->db->or_like('a.OrderID', $search);
                }
            }
            if ($appointment_assigned_staff_field_exists) {
                $this->db->or_like('a.AssignedStaff', $search);
            }
            $this->db->group_end();
        }
        
        // Apply staff filter
        if ($staff_filter && $staff_filter !== 'all' && $appointment_assigned_staff_field_exists) {
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
        
        // Note: For ocular appointments, we filter in PHP after fetching, so count will be adjusted there
        
        // Reset query builder for data retrieval
        $this->db->reset_query();
        $select_fields = ['a.*', 'o.OrderID', 'o.OrderDate', 'o.DeliveryAddress'];
        if ($customer_user_join_exists) {
            $select_fields[] = 'CONCAT(u.First_Name, " ", u.Last_Name) as CustomerName';
            $select_fields[] = 'u.PhoneNum as CustomerPhone';
            $select_fields[] = 'u.Role as UserRole';
            // Also fetch the customer profile role (beginner|professional) when available
            if ($this->db->field_exists('role', 'customer')) {
                $select_fields[] = 'c.role as CustomerRole';
            }
        } else {
            $select_fields[] = 'a.ClientName as CustomerName';
        }
        if ($order_number_field_exists) {
            $select_fields[] = 'o.OrderNumber as ApprovedOrderID';
        }
        if ($order_type_field_exists) {
            $select_fields[] = 'o.OrderType';
        }
        if ($order_items_table_exists && $product_table_exists) {
            $select_fields[] = 'GROUP_CONCAT(DISTINCT p.ProductName SEPARATOR ", ") as ProductName';
        }
        if ($oi_dimensions_exists) {
            $select_fields[] = 'MIN(oi.Dimensions) as Dimensions';
        }
        if ($oi_quantity_exists) {
            $select_fields[] = 'MIN(oi.Quantity) as Quantity';
        }
        if ($oi_unit_price_exists) {
            $select_fields[] = 'MIN(oi.UnitPrice) as UnitPrice';
        }
        if ($oi_estimate_price_exists) {
            $select_fields[] = 'MIN(oi.EstimatePrice) as EstimatePrice';
        }
        // Add customization fields from order_items
        if ($order_items_table_exists) {
            if ($this->db->field_exists('GlassShape', 'order_items')) {
                $select_fields[] = 'MIN(oi.GlassShape) as OI_GlassShape';
            }
            if ($this->db->field_exists('GlassType', 'order_items')) {
                $select_fields[] = 'MIN(oi.GlassType) as OI_GlassType';
            }
            if ($this->db->field_exists('GlassThickness', 'order_items')) {
                $select_fields[] = 'MIN(oi.GlassThickness) as OI_GlassThickness';
            }
            if ($this->db->field_exists('EdgeWork', 'order_items')) {
                $select_fields[] = 'MIN(oi.EdgeWork) as OI_EdgeWork';
            }
            if ($this->db->field_exists('FrameType', 'order_items')) {
                $select_fields[] = 'MIN(oi.FrameType) as OI_FrameType';
            }
            if ($this->db->field_exists('Engraving', 'order_items')) {
                $select_fields[] = 'MIN(oi.Engraving) as OI_Engraving';
            }
        }
        // Also fetch from customization table as fallback
        if ($customization_table_exists) {
            $select_fields[] = 'MIN(cust.Dimensions) as Cust_Dimensions';
            $select_fields[] = 'MIN(cust.GlassShape) as Cust_GlassShape';
            $select_fields[] = 'MIN(cust.GlassType) as Cust_GlassType';
            $select_fields[] = 'MIN(cust.GlassThickness) as Cust_GlassThickness';
            $select_fields[] = 'MIN(cust.EdgeWork) as Cust_EdgeWork';
            $select_fields[] = 'MIN(cust.FrameType) as Cust_FrameType';
            $select_fields[] = 'MIN(cust.Engraving) as Cust_Engraving';
            $select_fields[] = 'MIN(cust.EstimatePrice) as Cust_EstimatePrice';
        }
        $this->db->select(implode(', ', $select_fields));
        $this->db->from('appointments a');
        $this->db->join('`order` o', 'a.OrderID = o.OrderID', 'left');
        if ($customer_user_join_exists) {
            $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
            $this->db->join('user u', 'u.UserID = c.UserID', 'left');
        }
        if ($order_items_table_exists) {
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        }
        if ($order_items_table_exists && $product_table_exists) {
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        }
        // Join customization table - prefer direct CustomizationID link if available
        if ($customization_table_exists) {
            // Check if order table has CustomizationID field
            if ($this->db->field_exists('CustomizationID', 'order')) {
                $this->db->join('customization cust', 'cust.CustomizationID = o.CustomizationID', 'left');
            } else {
                // Fallback to Customer_ID join
                $this->db->join('customization cust', 'cust.Customer_ID = o.Customer_ID', 'left');
            }
        }
        
        // Reapply appointment type filter (ocular, fabrication, or installation)
        if ($appointment_type) {
            if ($appointment_type === 'ocular') {
                $this->db->where('a.Service', 'Ocular Visit');
                // Note: Filtering is done in PHP after fetching
            } elseif ($appointment_type === 'fabrication') {
                $this->db->where('a.Service', 'In Fabrication');
            } elseif ($appointment_type === 'installation') {
                $this->db->where('a.Service', 'Installed');
            }
        }
        
        $this->db->group_by('a.AppointmentID');
        if ($this->db->field_exists('Status', 'order')) {
            $this->db->where_in('o.Status', ['Ocular Pending', 'Approved', 'In Fabrication', 'Ready for Installation', 'Completed']);
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
            if ($appointment_product_field_exists) {
                $this->db->or_like('a.ProductName', $search);
            }
            if ($order_number_field_exists && preg_match('/^GI\d+$/i', $search)) {
                $this->db->or_like('o.OrderNumber', $search);
            } else {
                if (is_numeric($search)) {
                    $this->db->or_where('a.OrderID', (int)$search);
                } else {
                    $this->db->or_like('a.OrderID', $search);
                }
            }
            if ($appointment_assigned_staff_field_exists) {
                $this->db->or_like('a.AssignedStaff', $search);
            }
            $this->db->group_end();
        }
        if ($staff_filter && $staff_filter !== 'all' && $appointment_assigned_staff_field_exists) {
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
        // Sort by Order Date DESC (newest orders first), then by Appointment Date/Time
        $this->db->order_by('o.OrderDate', 'DESC');
        $this->db->order_by('a.AppointmentDate', 'ASC');
        $this->db->order_by('a.AppointmentTime', 'ASC');
        
        $appointments = $this->db->get()->result();
        
        // Check for database errors in data query
        $db_error = $this->db->error();
        if (!empty($db_error['message'])) {
            log_message('error', 'AdminCon::get_appointments_ajax - Data query DB error: ' . $db_error['message']);
            throw new Exception('Database error: ' . $db_error['message']);
        }
        
        // For ocular appointments, filter out completed ones that have moved to fabrication
        if ($appointment_type === 'ocular' && $this->db->table_exists('appointments')) {
            // Get all order IDs from appointments
            $order_ids = array_map(function($apt) {
                return $apt->OrderID;
            }, $appointments);
            
            // Fetch all fabrication appointments for these orders in one query
            $fabrication_orders = [];
            if (!empty($order_ids)) {
                $this->db->reset_query();
                $fabrication_appts = $this->db->select('OrderID')
                                             ->where_in('OrderID', array_unique($order_ids))
                                             ->where('Service', 'In Fabrication')
                                             ->get('appointments')
                                             ->result();
                foreach ($fabrication_appts as $fab) {
                    $fabrication_orders[$fab->OrderID] = true;
                }
            }
            
            // Filter appointments: exclude completed ones that have fabrication appointments
            $filtered_appointments = [];
            foreach ($appointments as $apt) {
                // Include if status is not Complete
                if ($apt->Status !== 'Complete') {
                    $filtered_appointments[] = $apt;
                } else {
                    // If Complete, only include if no fabrication appointment exists
                    if (!isset($fabrication_orders[$apt->OrderID])) {
                        $filtered_appointments[] = $apt;
                    }
                }
            }
            $appointments = $filtered_appointments;
            // Recalculate total count after filtering
            $total_count = count($appointments);
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
            
            $client_name = $apt->ClientName ?: ($apt->CustomerName ?? 'N/A');

            // Prefer the customer profile role (beginner|professional) when available,
            // otherwise fall back to the user account role, and finally 'Customer'.
            $raw_role = $apt->CustomerRole ?? ($apt->UserRole ?? 'Customer');
            $role_display = is_string($raw_role) ? ucfirst(strtolower($raw_role)) : 'Customer';
            
            // Format order date
            $order_date_formatted = 'N/A';
            if (isset($apt->OrderDate) && $apt->OrderDate) {
                $order_date_formatted = date('M d, Y', strtotime($apt->OrderDate));
            }
            
            $formatted_appointments[] = [
                'id' => $apt->AppointmentID,
                'order_id' => $apt->OrderID,
                'order_number' => $apt->ApprovedOrderID ?? ($apt->OrderNumber ?? 'N/A'),
                'order_date' => $order_date_formatted,
                'client' => $client_name,
                'client_name' => $client_name,
                'role' => $role_display,
                'client_phone' => $apt->CustomerPhone ?? 'N/A',
                'client_address' => $apt->DeliveryAddress ?? 'N/A',
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
                'installation_notes' => $apt->InstallationNotes ?? '',
                'fabrication_notes' => $apt->FabricationNotes ?? '',
                'dimensions' => $apt->Dimensions ?? ($apt->Cust_Dimensions ?? null),
                'quantity' => $apt->Quantity ?? null,
                'unit_price' => $apt->UnitPrice ?? null,
                'estimate_price' => $apt->EstimatePrice ?? ($apt->Cust_EstimatePrice ?? null),
                // Customization fields - fallback from customization table if order_items is empty
                'glass_shape' => $apt->OI_GlassShape ?? ($apt->Cust_GlassShape ?? null),
                'glass_type' => $apt->OI_GlassType ?? ($apt->Cust_GlassType ?? null),
                'glass_thickness' => $apt->OI_GlassThickness ?? ($apt->Cust_GlassThickness ?? null),
                'edge_work' => $apt->OI_EdgeWork ?? ($apt->Cust_EdgeWork ?? null),
                'frame_type' => $apt->OI_FrameType ?? ($apt->Cust_FrameType ?? null),
                'engraving' => $apt->OI_Engraving ?? ($apt->Cust_Engraving ?? null)
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
        }
        catch (Exception $e) {
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
        if (!$this->db->table_exists('appointments')) {
            return;
        }

        $order_items_table_exists = $this->db->table_exists('order_items');
        $product_table_exists = $this->db->table_exists('product');
        $special_instructions_field_exists = $this->db->field_exists('SpecialInstructions', 'order');
        $preferred_date_field_exists = $this->db->field_exists('PreferredInstallationDate', 'order');

        // Get all approved orders from unified order table
        $select_fields = [
            'o.OrderID',
            'o.OrderNumber',
            'o.Customer_ID',
            'u.First_Name',
            'u.Last_Name',
            'u.Middle_Name'
        ];
        if ($preferred_date_field_exists) {
            $select_fields[] = 'o.PreferredInstallationDate';
        }
        if ($special_instructions_field_exists) {
            $select_fields[] = 'o.SpecialInstructions';
        }
        if ($order_items_table_exists && $product_table_exists) {
            $select_fields[] = 'p.ProductName';
        } else {
            $select_fields[] = 'NULL as ProductName';
        }
        $this->db->select(implode(', ', $select_fields));
        $this->db->from('`order` o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        if ($order_items_table_exists) {
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        }
        if ($order_items_table_exists && $product_table_exists) {
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        }
        // UNIFIED PROCESS: ALL orders go through ocular visit, no more OrderType filtering
        // Get orders that are Approved or Ocular Pending (after approval, orders go to Ocular Pending)
        $this->db->where_in('o.Status', ['Approved', 'Ocular Pending']);
        $this->db->group_by('o.OrderID'); // Group to avoid duplicates from multiple order_items
        $approved_orders = $this->db->get()->result();
        
        foreach ($approved_orders as $order) {
            // Get customer's preferred ocular visit date
            // Priority: 1. PreferredInstallationDate column, 2. SpecialInstructions JSON, 3. Fallback to +3 days
            $preferred_date = null;
            
            // Check PreferredInstallationDate column first
            if ($preferred_date_field_exists && !empty($order->PreferredInstallationDate)) {
                $preferred_date = date('Y-m-d', strtotime($order->PreferredInstallationDate));
            }
            
            // Fallback to SpecialInstructions JSON
            if (!$preferred_date && $special_instructions_field_exists && !empty($order->SpecialInstructions)) {
                $preferred_date = $this->extract_preferred_installation_date($order->SpecialInstructions);
            }
            
            // Use preferred date if available, otherwise default to 3 days from now
            $appointment_date = $preferred_date ?: date('Y-m-d', strtotime('+3 days'));
            
            // Check if Ocular Visit appointment already exists (only check for ocular, not fabrication)
            $this->db->reset_query();
            $this->db->where('OrderID', $order->OrderID);
            if ($this->db->field_exists('Service', 'appointments')) {
                $this->db->where('Service', 'Ocular Visit');
            } elseif ($this->db->field_exists('AppointmentType', 'appointments')) {
                $this->db->where('AppointmentType', 'Ocular');
            }
            $existing_ocular = $this->db->get('appointments')->row();
            
            if (!$existing_ocular) {
                // Create Ocular Visit appointment when order is approved
                $client_name = trim(($order->First_Name ?? '') . ' ' . ($order->Middle_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
                
                $appointment_data = [
                    'OrderID' => $order->OrderID
                ];
                
                if ($this->db->field_exists('Customer_ID', 'appointments')) {
                    $appointment_data['Customer_ID'] = $order->Customer_ID;
                }
                if ($this->db->field_exists('ProductName', 'appointments')) {
                    $appointment_data['ProductName'] = $order->ProductName;
                }
                if ($this->db->field_exists('ClientName', 'appointments')) {
                    $appointment_data['ClientName'] = $client_name;
                }
                if ($this->db->field_exists('Service', 'appointments')) {
                    $appointment_data['Service'] = 'Ocular Visit';
                }
                if ($this->db->field_exists('Status', 'appointments')) {
                    $appointment_data['Status'] = 'In Progress';
                }
                if ($this->db->field_exists('AppointmentType', 'appointments')) {
                    $appointment_data['AppointmentType'] = 'Ocular';
                }
                if ($this->db->field_exists('AppointmentDate', 'appointments')) {
                    $appointment_data['AppointmentDate'] = $appointment_date;
                }
                if ($this->db->field_exists('AppointmentTime', 'appointments')) {
                    $appointment_data['AppointmentTime'] = '10:00:00';
                }
                
                $this->db->reset_query();
                $this->db->insert('appointments', $appointment_data);
            } else {
                // Update existing ocular appointment if it has NULL date or needs preferred date update
                if (empty($existing_ocular->AppointmentDate) || ($preferred_date && $existing_ocular->AppointmentDate !== $preferred_date)) {
                    $update_data = [];
                    if (empty($existing_ocular->AppointmentDate)) {
                        $update_data['AppointmentDate'] = $appointment_date;
                    }
                    if ($preferred_date && $existing_ocular->AppointmentDate !== $preferred_date) {
                        // Update to preferred date if it's different
                        $update_data['AppointmentDate'] = $preferred_date;
                    }
                    if (!empty($update_data)) {
                        $this->db->reset_query();
                        $this->db->where('AppointmentID', $existing_ocular->AppointmentID);
                        $this->db->update('appointments', $update_data);
                    }
                }
            }
        }
    }
    
    /**
     * Create fabrication appointment/entry after ocular is complete
     */
    private function create_fabrication_appointment($order_id)
    {
        // Get order details
        $this->db->reset_query();
        $order = $this->db->where('OrderID', $order_id)->get('`order`')->row();
        
        if (!$order) {
            return false;
        }
        
        // Check if fabrication appointment already exists
        $this->db->reset_query();
        $existing_fabrication = $this->db->where('OrderID', $order_id)
                                         ->where('Service', 'In Fabrication')
                                         ->get('appointments')
                                         ->row();
        
        if ($existing_fabrication) {
            // Fabrication appointment already exists, don't create duplicate
            return true;
        }
        
        // Check if fabrication entry exists in projectschedule table
        if ($this->db->table_exists('projectschedule')) {
            $this->db->reset_query();
            $existing_project = $this->db->where('OrderID', $order_id)
                                         ->get('projectschedule')
                                         ->row();
            
            if ($existing_project) {
                // Fabrication entry already exists in projectschedule, don't create duplicate
                return true;
            }
        }
        
        // Create fabrication appointment in appointments table
        $client_name = '';
        if (!empty($order->Customer_ID)) {
            $this->db->reset_query();
            $this->db->select('u.First_Name, u.Last_Name, u.Middle_Name');
            $this->db->from('customer c');
            $this->db->join('user u', 'u.UserID = c.UserID', 'left');
            $this->db->where('c.Customer_ID', $order->Customer_ID);
            $customer_user = $this->db->get()->row();
            if ($customer_user) {
                $client_name = trim(($customer_user->First_Name ?? '') . ' ' . ($customer_user->Middle_Name ?? '') . ' ' . ($customer_user->Last_Name ?? ''));
            }

            // Build appointment data for fabrication
            $appointment_data = ['OrderID' => $order_id];
            if ($this->db->field_exists('Customer_ID', 'appointments')) {
                $appointment_data['Customer_ID'] = $order->Customer_ID ?? null;
            }
            if ($this->db->field_exists('Service', 'appointments')) {
                $appointment_data['Service'] = 'In Fabrication';
            }
            if ($this->db->field_exists('Status', 'appointments')) {
                $appointment_data['Status'] = 'In Progress';
            }
            if ($this->db->field_exists('AppointmentType', 'appointments')) {
                $appointment_data['AppointmentType'] = 'Fabrication';
            }
            if ($this->db->field_exists('ClientName', 'appointments')) {
                $appointment_data['ClientName'] = $client_name ?: 'N/A';
            }
            if ($this->db->field_exists('ProductName', 'appointments')) {
                $appointment_data['ProductName'] = $order->ProductName ?? null;
            }
            if ($this->db->field_exists('AppointmentDate', 'appointments')) {
                $appointment_data['AppointmentDate'] = date('Y-m-d');
            }
        if ($this->db->field_exists('AppointmentTime', 'appointments')) {
            $appointment_data['AppointmentTime'] = '10:00:00';
        }
        }
        
        $this->db->reset_query();
        $insert_result = $this->db->insert('appointments', $appointment_data);
        
        if (!$insert_result) {
            $db_error = $this->db->error();
            log_message('error', 'AdminCon::create_fabrication_appointment - Failed to insert fabrication appointment: ' . ($db_error['message'] ?? 'Unknown error'));
            return false;
        }
        
        // Also create entry in projectschedule table if it exists
        if ($this->db->table_exists('projectschedule')) {
            $this->db->reset_query();
            
            // Check if fields exist before inserting
            $schedule_data = ['OrderID' => $order_id];
            
            // Get the current admin user ID for the foreign key constraint
            $admin_id = $this->session->userdata('user_id');
            if ($admin_id && $this->db->field_exists('Admin_ID', 'projectschedule')) {
                $schedule_data['Admin_ID'] = $admin_id;
            }
            
            if ($this->db->field_exists('Status', 'projectschedule')) {
                $schedule_data['Status'] = 'In progress';
            }
            if ($this->db->field_exists('StartDate', 'projectschedule')) {
                $schedule_data['StartDate'] = date('Y-m-d');
            }
            if ($this->db->field_exists('CreatedDate', 'projectschedule')) {
                $schedule_data['CreatedDate'] = date('Y-m-d H:i:s');
            }
            
            $insert_result = $this->db->insert('projectschedule', $schedule_data);
            
            if (!$insert_result) {
                $db_error = $this->db->error();
                log_message('error', 'AdminCon::create_fabrication_appointment - Failed to insert into projectschedule: ' . ($db_error['message'] ?? 'Unknown error'));
                // Don't fail the whole operation if projectschedule insert fails
            }
        }
        
        // Log the creation for debugging
        log_message('info', "AdminCon::create_fabrication_appointment - Created fabrication appointment for OrderID: {$order_id}");
        
        return true;
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
        
        // Build select fields dynamically based on what columns exist
        $select_fields = 'a.*, CONCAT(u.First_Name, " ", u.Last_Name) as CustomerName, u.PhoneNum as CustomerPhone, o.DeliveryAddress, o.OrderDate';
        
        // Add installation payment fields from order table if they exist
        if ($this->db->field_exists('InstallationPaymentAmount', 'order')) {
            $select_fields .= ', o.InstallationPaymentAmount';
        }
        if ($this->db->field_exists('InstallationPaymentMethod', 'order')) {
            $select_fields .= ', o.InstallationPaymentMethod';
        }
        if ($this->db->field_exists('InstallationPaymentStatus', 'order')) {
            $select_fields .= ', o.InstallationPaymentStatus';
        }
        if ($this->db->field_exists('InstallationReceiptPath', 'order')) {
            $select_fields .= ', o.InstallationReceiptPath';
        }
        if ($this->db->field_exists('InstallationCompletedDate', 'order')) {
            $select_fields .= ', o.InstallationCompletedDate';
        }
        if ($this->db->field_exists('InstallationPaymentDueDate', 'order')) {
            $select_fields .= ', o.InstallationPaymentDueDate';
        }
        // Add new appointment-level payment workflow fields
        if ($this->db->field_exists('InstallationCompletedDate', 'appointments')) {
            $select_fields .= ', a.InstallationCompletedDate as appointment_installation_completed_date';
        }
        if ($this->db->field_exists('PaymentDueDate', 'appointments')) {
            $select_fields .= ', a.PaymentDueDate as appointment_payment_due_date';
        }
        if ($this->db->field_exists('PaymentGracePeriodDays', 'appointments')) {
            $select_fields .= ', a.PaymentGracePeriodDays as payment_grace_period_days';
        }
        
        $this->db->select($select_fields);
        $this->db->from('appointments a');
        $this->db->join('`order` o', 'a.OrderID = o.OrderID', 'left');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->where('a.AppointmentID', $appointment_id);
        $appointment = $this->db->get()->row();
        
        if (!$appointment) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }
        
        // Format date for input field (YYYY-MM-DD)
        $appointment_date = $appointment->AppointmentDate ? date('Y-m-d', strtotime($appointment->AppointmentDate)) : '';
        $appointment_time = $appointment->AppointmentTime ? date('H:i', strtotime($appointment->AppointmentTime)) : '';
        
        // Load order item specs (first item) with product info
        $order_item = null;
        $product_name = '';
        $product_image = '';
        if ($this->db->table_exists('order_items')) {
            $this->db->select('oi.*, p.ProductName, p.ImageUrl as ProductImage, p.Category as ProductCategory');
            $this->db->from('order_items oi');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
            $this->db->where('oi.OrderID', $appointment->OrderID);
            $order_item = $this->db->get()->row();
            if ($order_item) {
                $product_name = $order_item->ProductName ?? '';
                $product_image = $order_item->ProductImage ?? '';
            }
        }
        
        // Load customization data if CustomizationID exists
        $customization = null;
        $customization_json = null;
        $design_ref = null;
        if ($order_item && isset($order_item->CustomizationID) && $order_item->CustomizationID) {
            if ($this->db->table_exists('customization')) {
                $this->db->where('CustomizationID', $order_item->CustomizationID);
                $customization = $this->db->get('customization')->row();
                if ($customization) {
                    // Get Customization JSON if available
                    if (isset($customization->Customization)) {
                        $customization_json = $customization->Customization;
                    }
                    // Get DesignRef (2D preview image)
                    if (!empty($customization->DesignRef)) {
                        $design_ref = $customization->DesignRef;
                    }
                }
            }
        }
        // Fallback: DesignRef from order_items
        if (!$design_ref && $order_item && !empty($order_item->DesignRef)) {
            $design_ref = $order_item->DesignRef;
        }

        // Load payment receipt if available
        $receipt_url = null;
        $payment_method = null;
        $payment_status = null;
        $payment_amount = null;
        if ($this->db->table_exists('payment')) {
            $payment = $this->db->where('OrderID', $appointment->OrderID)->get('payment')->row();
            if ($payment) {
                if (!empty($payment->ReceiptPath)) {
                    $receipt_url = base_url($payment->ReceiptPath);
                }
                $payment_method = $payment->PaymentMethod ?? null;
                $payment_status = $payment->Status ?? 'Pending';
                $payment_amount = $payment->Amount ?? null;
            }
        }

        // Get ocular notes if field exists
        $ocular_notes = '';
        if ($this->db->field_exists('OcularNotes', 'appointments')) {
            $ocular_notes = $appointment->OcularNotes ?: '';
        }
        
        // Get fabrication notes if field exists
        $fabrication_notes = '';
        if ($this->db->field_exists('FabricationNotes', 'appointments')) {
            $fabrication_notes = $appointment->FabricationNotes ?: '';
        }
        
        // For installation appointments, get fabrication payment data from the fabrication appointment
        $fabrication_amount = null;
        $fabrication_method = null;
        $fabrication_status = 'Pending';
        $fabrication_receipt_url = null;
        
        // First check current appointment (for fabrication appointments)
        if ($this->db->field_exists('FabricationPaymentAmount', 'appointments')) {
            $fabrication_amount = $appointment->FabricationPaymentAmount ?? null;
            $fabrication_method = $appointment->FabricationPaymentMethod ?? null;
            $fabrication_status = $appointment->FabricationPaymentStatus ?? 'Pending';
            if (!empty($appointment->FabricationReceiptPath)) {
                $fabrication_receipt_url = base_url($appointment->FabricationReceiptPath);
            }
        }
        
        // If this is an installation appointment, look up the fabrication appointment for this order
        if ($appointment->Service === 'Installed' && $appointment->OrderID) {
            $this->db->reset_query();
            $this->db->where('OrderID', $appointment->OrderID);
            $this->db->where('Service', 'In Fabrication');
            $fab_appointment = $this->db->get('appointments')->row();
            
            if ($fab_appointment) {
                if ($this->db->field_exists('FabricationPaymentAmount', 'appointments') && isset($fab_appointment->FabricationPaymentAmount)) {
                    $fabrication_amount = $fab_appointment->FabricationPaymentAmount;
                }
                if ($this->db->field_exists('FabricationPaymentMethod', 'appointments') && isset($fab_appointment->FabricationPaymentMethod)) {
                    $fabrication_method = $fab_appointment->FabricationPaymentMethod;
                }
                if ($this->db->field_exists('FabricationPaymentStatus', 'appointments') && isset($fab_appointment->FabricationPaymentStatus)) {
                    $fabrication_status = $fab_appointment->FabricationPaymentStatus;
                }
                if ($this->db->field_exists('FabricationReceiptPath', 'appointments') && !empty($fab_appointment->FabricationReceiptPath)) {
                    $fabrication_receipt_url = base_url($fab_appointment->FabricationReceiptPath);
                }
            }
            
            // FALLBACK: If no fabrication payment found in appointments table, check order table
            if ($fabrication_amount === null && $appointment->OrderID) {
                $this->db->reset_query();
                $order_data = $this->db->get_where('`order`', ['OrderID' => $appointment->OrderID])->row();
                if ($order_data) {
                    if ($this->db->field_exists('FabricationPaymentAmount', 'order') && isset($order_data->FabricationPaymentAmount)) {
                        $fabrication_amount = $order_data->FabricationPaymentAmount;
                    }
                    if ($this->db->field_exists('FabricationPaymentMethod', 'order') && isset($order_data->FabricationPaymentMethod)) {
                        $fabrication_method = $order_data->FabricationPaymentMethod;
                    }
                    if ($this->db->field_exists('FabricationPaymentStatus', 'order') && isset($order_data->FabricationPaymentStatus)) {
                        $fabrication_status = $order_data->FabricationPaymentStatus;
                    }
                    if ($this->db->field_exists('FabricationReceiptPath', 'order') && !empty($order_data->FabricationReceiptPath)) {
                        $fabrication_receipt_url = base_url($order_data->FabricationReceiptPath);
                    }
                }
            }
        }
        
        // Format order date
        $order_date_formatted = '';
        if (isset($appointment->OrderDate) && $appointment->OrderDate) {
            $order_date_formatted = date('M d, Y', strtotime($appointment->OrderDate));
        }
        
        echo json_encode([
            'success' => true,
            'appointment' => [
                'id' => $appointment->AppointmentID,
                'order_id' => $appointment->OrderID,
                'order_date' => $order_date_formatted,
                'product' => $product_name ?: ($appointment->ProductName ?? 'N/A'),
                'product_name' => $product_name ?: ($appointment->ProductName ?? 'N/A'),
                'product_image' => $product_image ? base_url($product_image) : null,
                'product_category' => $order_item->ProductCategory ?? null,
                'client' => $appointment->ClientName ?: ($appointment->CustomerName ?? 'N/A'),
                'client_name' => $appointment->ClientName ?: ($appointment->CustomerName ?? 'N/A'),
                'client_phone' => $appointment->CustomerPhone ?? 'N/A',
                'client_address' => $appointment->DeliveryAddress ?? 'N/A',
                'service' => $appointment->Service,
                'date' => $appointment_date,
                'time' => $appointment_time,
                'assigned_staff' => $appointment->AssignedStaff ?: '',
                'assigned_staff_id' => $appointment->AssignedStaff_ID ?? null,
                'status' => $appointment->Status,
                'notes' => $appointment->Notes ?: '',
                'ocular_notes' => $ocular_notes,
                'order_item_id' => $order_item->OrderItemID ?? null,
                'dimensions' => $order_item->Dimensions ?? null,
                'quantity' => $order_item->Quantity ?? null,
                'unit_price' => $order_item->UnitPrice ?? null,
                'estimate_price' => $order_item->EstimatePrice ?? null,
                'receipt_url' => $receipt_url,
                // Customization data from order_items or customization table
                'glass_shape' => ($order_item->GlassShape ?? null) ?: ($customization->GlassShape ?? null),
                'glass_type' => ($order_item->GlassType ?? null) ?: ($customization->GlassType ?? null),
                'glass_thickness' => ($order_item->GlassThickness ?? null) ?: ($customization->GlassThickness ?? null),
                'edge_work' => ($order_item->EdgeWork ?? null) ?: ($customization->EdgeWork ?? null),
                'frame_type' => ($order_item->FrameType ?? null) ?: ($customization->FrameType ?? null),
                'engraving' => ($order_item->Engraving ?? null) ?: ($customization->Engraving ?? null),
                'design_ref' => $design_ref ? base_url($design_ref) : null,
                'customization_json' => $customization_json,
                // Include the dynamic customization data from order_items table
                'order_item_customization' => $order_item->Customization ?? null,
                // Payment breakdown data
                'payment_data' => [
                    'downpayment_amount' => $payment_amount,
                    'downpayment_method' => $payment_method,
                    'downpayment_status' => $payment_status,
                    'fabrication_amount' => $fabrication_amount,
                    'fabrication_method' => $fabrication_method,
                    'fabrication_status' => $fabrication_status,
                    'fabrication_receipt_url' => $fabrication_receipt_url,
                    // Installation payment data (10% final payment - from order table)
                    'installation_amount' => $appointment->InstallationPaymentAmount ?? null,
                    'installation_method' => $appointment->InstallationPaymentMethod ?? null,
                    'installation_status' => $appointment->InstallationPaymentStatus ?? 'Pending',
                    'installation_receipt_url' => !empty($appointment->InstallationReceiptPath) ? base_url($appointment->InstallationReceiptPath) : null,
                    'installation_completed_date' => $appointment->InstallationCompletedDate ?? null,
                    'installation_payment_due_date' => $appointment->InstallationPaymentDueDate ?? null
                ],
                // New payment workflow fields from appointments table
                'installation_completed_date' => $appointment->appointment_installation_completed_date ?? null,
                'payment_due_date' => $appointment->appointment_payment_due_date ?? null,
                'payment_grace_period_days' => $appointment->payment_grace_period_days ?? 5,
                'fabrication_notes' => $fabrication_notes
            ]
        ]);
    }
    
    /**
     * Update appointment details
     */
    public function update_appointment_ajax()
    {
        // Disable CodeIgniter profiler and set JSON output
        $this->output->enable_profiler(FALSE);
        $this->output->set_content_type('application/json');
        
        // Enable error logging
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        log_message('debug', '=== update_appointment_ajax START ===');
        log_message('debug', 'Request method: ' . $this->input->method());
        log_message('debug', 'Content-Type: ' . $this->input->server('CONTENT_TYPE'));
        
        try {
            // Check authentication
            if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
                log_message('warning', 'Unauthorized access attempt to update_appointment_ajax');
                $this->output->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
                return;
            }
            
            log_message('debug', 'Authentication passed');
            
            // Handle both JSON and form data
            $input_data = [];
            $content_type = $this->input->server('CONTENT_TYPE');
            if (empty($content_type)) {
                $content_type = '';
            }
            if (strpos($content_type, 'application/json') !== false) {
                $raw_input = file_get_contents('php://input');
                $input_data = json_decode($raw_input, true) ?: [];
            } else {
                $input_data = $this->input->post();
            }
            
            log_message('debug', 'update_appointment_ajax called with data: ' . json_encode($input_data));
            
            $appointment_id = isset($input_data['appointment_id']) ? $input_data['appointment_id'] : null;
            $client_name = isset($input_data['client_name']) ? $input_data['client_name'] : null;
            $service = isset($input_data['service']) ? $input_data['service'] : null;
            $date = isset($input_data['date']) ? $input_data['date'] : null;
            $time = isset($input_data['time']) ? $input_data['time'] : null;
            $assigned_staff = isset($input_data['assigned_staff']) ? $input_data['assigned_staff'] : null;
            $status = isset($input_data['status']) ? $input_data['status'] : null;
            $notes = isset($input_data['notes']) ? $input_data['notes'] : null;
            $ocular_notes = isset($input_data['ocular_notes']) ? $input_data['ocular_notes'] : null;
            $installation_notes = isset($input_data['installation_notes']) ? $input_data['installation_notes'] : null;
            $order_item_id = isset($input_data['order_item_id']) ? $input_data['order_item_id'] : null;
            $spec_width = isset($input_data['spec_width']) ? $input_data['spec_width'] : null;
            $spec_height = isset($input_data['spec_height']) ? $input_data['spec_height'] : null;
            $spec_unit = isset($input_data['spec_unit']) ? $input_data['spec_unit'] : 'in';
            $spec_price = isset($input_data['spec_price']) ? $input_data['spec_price'] : null;
            $spec_quantity = isset($input_data['spec_quantity']) ? $input_data['spec_quantity'] : null;
            $ocular_completed = isset($input_data['ocular_completed']) ? (bool)$input_data['ocular_completed'] : null;
            
            // Get customization specifications
            $spec_shape = isset($input_data['spec_shape']) ? $input_data['spec_shape'] : null;
            $spec_type = isset($input_data['spec_type']) ? $input_data['spec_type'] : null;
            $spec_thickness = isset($input_data['spec_thickness']) ? $input_data['spec_thickness'] : null;
            $spec_edge = isset($input_data['spec_edge']) ? $input_data['spec_edge'] : null;
            $spec_frame = isset($input_data['spec_frame']) ? $input_data['spec_frame'] : null;
            $spec_engraving = isset($input_data['spec_engraving']) ? $input_data['spec_engraving'] : null;
            $dynamic_customization = isset($input_data['dynamic_customization']) ? $input_data['dynamic_customization'] : null;
            
            // Get payment breakdown data
            $downpayment_amount = isset($input_data['downpayment_amount']) ? $input_data['downpayment_amount'] : null;
            $downpayment_method = isset($input_data['downpayment_method']) ? $input_data['downpayment_method'] : null;
            $downpayment_status = isset($input_data['downpayment_status']) ? $input_data['downpayment_status'] : 'Pending';
            
            // Get fabrication payment data
            $fabrication_amount = isset($input_data['fabrication_amount']) ? $input_data['fabrication_amount'] : null;
            $fabrication_method = isset($input_data['fabrication_method']) ? $input_data['fabrication_method'] : null;
            $fabrication_status = isset($input_data['fabrication_status']) ? $input_data['fabrication_status'] : 'Pending';
            $fabrication_notes = isset($input_data['fabrication_notes']) ? $input_data['fabrication_notes'] : null;
            
            // Get installation payment data (10% final payment)
            $installation_amount = isset($input_data['installation_amount']) ? $input_data['installation_amount'] : null;
            $installation_method = isset($input_data['installation_method']) ? $input_data['installation_method'] : null;
            $installation_status = isset($input_data['installation_status']) ? $input_data['installation_status'] : 'Pending';
            
            if (!$appointment_id) {
                $this->output->set_output(json_encode(['success' => false, 'message' => 'Appointment ID is required']));
                return;
            }
            
            // Get appointment to find OrderID and previous status
            $this->db->where('AppointmentID', $appointment_id);
            $appointment = $this->db->get('appointments')->row();
            
            if (!$appointment) {
                $this->output->set_output(json_encode(['success' => false, 'message' => 'Appointment not found']));
                return;
            }
            
            // Use existing service if not provided
            if (empty($service) && !empty($appointment->Service)) {
                $service = $appointment->Service;
            }
            
            // Validate service (only if provided or existing appointment has service)
            if (!empty($service)) {
                $valid_services = ['Order Placed', 'Ocular Visit', 'In Fabrication', 'Installed', 'Completed'];
                if (!in_array($service, $valid_services)) {
                    $this->output->set_output(json_encode(['success' => false, 'message' => 'Invalid service']));
                    return;
                }
            }
            
            // Validate status
            if (!empty($status)) {
                $valid_statuses = ['In Progress', 'Installed', 'Complete', 'Payment Overdue', 'Cancelled', 'Returned'];
                if (!in_array($status, $valid_statuses)) {
                    $this->output->set_output(json_encode(['success' => false, 'message' => 'Invalid status']));
                    return;
                }
            }
            
            $order_id = isset($appointment->OrderID) ? $appointment->OrderID : null;
            if (!$order_id) {
                log_message('error', 'Order ID not found for appointment: ' . $appointment_id);
                $this->output->set_output(json_encode(['success' => false, 'message' => 'Order ID not found in appointment']));
                return;
            }
            
            log_message('debug', 'Processing appointment: ' . $appointment_id . ', Order: ' . $order_id);
            
            $previous_status = isset($appointment->Status) ? $appointment->Status : null; // Store previous status to detect reversals
            
            // Start transaction
            log_message('debug', 'Starting database transaction');
            $this->db->trans_start();
            
            // Convert staff ID to staff name if needed
            $assigned_staff_name = null;
            $assigned_staff_id = null;
            if (!empty($assigned_staff) && is_numeric($assigned_staff)) {
                // If it's a numeric ID, get the staff name
                if ($this->db->table_exists('user')) {
                    $this->db->select('CONCAT(First_Name, " ", Last_Name) as StaffName', false);
                    $this->db->where('UserID', (int)$assigned_staff);
                    $staff = $this->db->get('user')->row();
                    if ($staff) {
                        $assigned_staff_name = $staff->StaffName;
                        $assigned_staff_id = (int)$assigned_staff;
                    }
                }
            }
            // If $assigned_staff is empty, null, or not numeric, both $assigned_staff_name and $assigned_staff_id remain null
            
            // Prepare update data - only update fields that are provided
            $update_data = [
                'Updated_Date' => date('Y-m-d H:i:s')
            ];
            
            // Only include fields that are provided (not null or empty string for optional fields)
            if ($client_name !== null) {
                $update_data['ClientName'] = $client_name;
            }
            if ($service !== null) {
                $update_data['Service'] = $service;
            }
            if ($date !== null) {
                $update_data['AppointmentDate'] = $date ?: null;
            }
            if ($time !== null) {
                $update_data['AppointmentTime'] = $time ?: null;
            }
            // Handle assigned staff - update if provided (empty string means unassign)
            if ($assigned_staff !== null) {
                $update_data['AssignedStaff'] = $assigned_staff_name;
                if ($this->db->field_exists('AssignedStaff_ID', 'appointments')) {
                    $update_data['AssignedStaff_ID'] = $assigned_staff_id;
                }
            }
            if ($status !== null) {
                $update_data['Status'] = $status;
                
                // Handle installation payment workflow - auto-set completion and due dates
                if ($status === 'Installed' && $this->db->field_exists('InstallationCompletedDate', 'appointments')) {
                    // Check if completion date is already set
                    $current_apt = $this->db->where('AppointmentID', $appointment_id)->get('appointments')->row();
                    if (!$current_apt || empty($current_apt->InstallationCompletedDate)) {
                        $update_data['InstallationCompletedDate'] = date('Y-m-d H:i:s');
                        
                        // Calculate payment due date (5 days from completion)
                        $grace_period = 5; // Default 5 days
                        if ($current_apt && !empty($current_apt->PaymentGracePeriodDays)) {
                            $grace_period = (int)$current_apt->PaymentGracePeriodDays;
                        }
                        $due_date = date('Y-m-d H:i:s', strtotime("+{$grace_period} days"));
                        $update_data['PaymentDueDate'] = $due_date;
                        
                        log_message('debug', "Set installation completion date and payment due date (+{$grace_period} days) for appointment {$appointment_id}");
                        
                        // Auto-unlock customer payment by setting InstallationPaymentStatus to Pending
                        if ($order_id && $this->db->field_exists('InstallationPaymentStatus', 'order')) {
                            $current_inst_status = null;
                            $order_check = $this->db->select('InstallationPaymentStatus, TotalAmount')->where('OrderID', $order_id)->get('`order`')->row();
                            if ($order_check) {
                                $current_inst_status = $order_check->InstallationPaymentStatus;
                                $total_amount = floatval($order_check->TotalAmount ?? 0);
                                
                                // Only set to Pending if not already set, and set the 10% amount
                                if (empty($current_inst_status) || $current_inst_status === null) {
                                    $unlock_payment_data = [
                                        'InstallationPaymentStatus' => 'Pending',
                                        'InstallationPaymentAmount' => round($total_amount * 0.1, 2)
                                    ];
                                    $this->db->where('OrderID', $order_id)->update('`order`', $unlock_payment_data);
                                    log_message('debug', "Unlocked installation payment for customer - OrderID: {$order_id}, Amount: " . round($total_amount * 0.1, 2));
                                }
                            }
                        }
                    }
                }
                
                // Clear payment due date when marked as Complete
                if ($status === 'Complete' && $this->db->field_exists('PaymentDueDate', 'appointments')) {
                    $update_data['PaymentDueDate'] = null;
                    
                    // Also mark installation payment as Paid if not already
                    if ($order_id && $this->db->field_exists('InstallationPaymentStatus', 'order')) {
                        $this->db->where('OrderID', $order_id)->update('`order`', ['InstallationPaymentStatus' => 'Paid']);
                        log_message('debug', "Marked installation payment as Paid for OrderID: {$order_id}");
                    }
                }
            }
            if ($notes !== null) {
                $update_data['Notes'] = $notes ?: null;
            }
            if ($ocular_notes !== null && $this->db->field_exists('OcularNotes', 'appointments')) {
                $update_data['OcularNotes'] = $ocular_notes ?: null;
            }
            if ($installation_notes !== null && $this->db->field_exists('InstallationNotes', 'appointments')) {
                $update_data['InstallationNotes'] = $installation_notes ?: null;
            }
            if ($fabrication_notes !== null && $this->db->field_exists('FabricationNotes', 'appointments')) {
                $update_data['FabricationNotes'] = $fabrication_notes ?: null;
            }
            
            $this->db->where('AppointmentID', $appointment_id);
            $this->db->update('appointments', $update_data);

            // Handle payment data if provided
            $total_amount = isset($input_data['total_amount']) ? $input_data['total_amount'] : null;
            $downpayment_amount = isset($input_data['downpayment_amount']) ? $input_data['downpayment_amount'] : null;
            $downpayment_method = isset($input_data['downpayment_method']) ? $input_data['downpayment_method'] : null;
            $downpayment_status = isset($input_data['downpayment_status']) ? $input_data['downpayment_status'] : null;
            
            // Save/update payment data
            if ($total_amount !== null || $downpayment_amount !== null) {
                log_message('debug', 'Processing payment data - Total: ' . ($total_amount ?: 'NULL') . ', Downpayment: ' . ($downpayment_amount ?: 'NULL'));
                
                // IMPORTANT: Also update order.TotalAmount so fabrication/installation pages can calculate payments
                if ($total_amount !== null && trim($total_amount) !== '' && $order_id) {
                    $this->db->where('OrderID', $order_id);
                    $this->db->update('order', ['TotalAmount' => (float)$total_amount]);
                    log_message('debug', 'Updated order.TotalAmount to ' . $total_amount . ' for OrderID: ' . $order_id);
                }
                
                // Check if appointment_payments table exists
                if ($this->db->table_exists('appointment_payments')) {
                    // Check if payment data already exists
                    $existing_payment = $this->db->where('appointment_id', $appointment_id)->get('appointment_payments')->row();
                    
                    $payment_data = [];
                    if ($total_amount !== null && trim($total_amount) !== '') {
                        $payment_data['total_amount'] = (float)$total_amount;
                    }
                    if ($downpayment_amount !== null && trim($downpayment_amount) !== '') {
                        $payment_data['downpayment_amount'] = (float)$downpayment_amount;
                    }
                    if ($downpayment_method !== null) {
                        $payment_data['downpayment_method'] = $downpayment_method;
                    }
                    if ($downpayment_status !== null) {
                        $payment_data['downpayment_status'] = $downpayment_status;
                    }
                    
                    if (!empty($payment_data)) {
                        if ($existing_payment) {
                            // Update existing payment record
                            $this->db->where('appointment_id', $appointment_id);
                            $this->db->update('appointment_payments', $payment_data);
                            log_message('debug', 'Updated appointment_payments for ID: ' . $appointment_id);
                        } else {
                            // Create new payment record
                            $payment_data['appointment_id'] = $appointment_id;
                            $this->db->insert('appointment_payments', $payment_data);
                            log_message('debug', 'Inserted new appointment_payments for ID: ' . $appointment_id);
                        }
                    }
                } else {
                    log_message('debug', 'appointment_payments table does not exist - skipping payment data save');
                }
            }
            
            // Handle order item specifications if provided
            // IMPORTANT: Only update customization for beginner accounts to prevent overwriting
            // customer's original customization with admin's ocular assessment specs
            if (!empty($spec_width) || !empty($spec_height) || !empty($spec_quantity) || !empty($spec_price) ||
                !empty($spec_shape) || !empty($spec_type) || !empty($spec_thickness) || !empty($spec_edge) || 
                !empty($spec_frame) || !empty($spec_engraving) || !empty($dynamic_customization)) {
                
                // Check if customer is a beginner before allowing customization updates
                // TEMPORARY: Set to true for testing dropdown saves
                $is_beginner = true; // TODO: Change back to proper check for production
                /*
                $is_beginner = false;
                $this->db->reset_query();
                $order_record = $this->db->select('Customer_ID')->where('OrderID', $order_id)->get('`order`')->row();
                if ($order_record && !empty($order_record->Customer_ID)) {
                    $this->db->reset_query();
                    $customer = $this->db->select('role')->where('Customer_ID', $order_record->Customer_ID)->get('customer')->row();
                    if ($customer && !empty($customer->role)) {
                        $is_beginner = (strcasecmp($customer->role, 'beginner') === 0);
                        log_message('debug', 'Customer role: ' . $customer->role . ' | Is Beginner: ' . ($is_beginner ? 'YES' : 'NO'));
                    }
                }
                */
                log_message('debug', 'TESTING MODE: Is Beginner forced to TRUE for testing');
                
                // Only proceed with customization updates if customer is a beginner
                if (!$is_beginner) {
                    log_message('info', 'Skipping customization update for non-beginner customer (Order: ' . $order_id . '). Admin specs are for internal reference only.');
                }
                
                $target_item_id = $order_item_id;
                if (empty($target_item_id)) {
                    $this->db->reset_query();
                    $this->db->where('OrderID', $order_id);
                    $item = $this->db->get('order_items')->row();
                    $target_item_id = $item->OrderItemID ?? null;
                }
                if (!empty($target_item_id) && $is_beginner) {
                    // Only update order_items for beginner accounts
                    log_message('info', 'Updating order_items customization for beginner account (Order: ' . $order_id . ', Item: ' . $target_item_id . ')');
                    $item_update = [];
                    if ($spec_width !== '' && $spec_height !== '' && $this->db->field_exists('Dimensions', 'order_items')) {
                        $item_update['Dimensions'] = trim($spec_width) . $spec_unit . ' x ' . trim($spec_height) . $spec_unit;
                    }
                    if ($spec_quantity !== '' && $this->db->field_exists('Quantity', 'order_items')) {
                        $item_update['Quantity'] = (int)$spec_quantity;
                    }
                    if ($spec_price !== '') {
                        if ($this->db->field_exists('UnitPrice', 'order_items')) {
                            $item_update['UnitPrice'] = (float)$spec_price;
                        }
                        if ($this->db->field_exists('EstimatePrice', 'order_items')) {
                            $item_update['EstimatePrice'] = (float)$spec_price;
                        }
                    }
                    
                    // Update customization fields if they exist in order_items table
                    log_message('debug', 'Processing static customization fields...');
                    log_message('debug', 'spec_shape: ' . ($spec_shape ?: 'NULL'));
                    log_message('debug', 'spec_type: ' . ($spec_type ?: 'NULL')); 
                    log_message('debug', 'spec_thickness: ' . ($spec_thickness ?: 'NULL'));
                    log_message('debug', 'spec_edge: ' . ($spec_edge ?: 'NULL'));
                    log_message('debug', 'spec_frame: ' . ($spec_frame ?: 'NULL'));
                    log_message('debug', 'spec_engraving: ' . ($spec_engraving ?: 'NULL'));
                    
                    if ($spec_shape !== null && $this->db->field_exists('GlassShape', 'order_items')) {
                        $item_update['GlassShape'] = $spec_shape;
                        log_message('debug', 'Updated GlassShape: ' . $spec_shape);
                    }
                    if ($spec_type !== null && $this->db->field_exists('GlassType', 'order_items')) {
                        $item_update['GlassType'] = $spec_type;
                        log_message('debug', 'Updated GlassType: ' . $spec_type);
                    }
                    if ($spec_thickness !== null && $this->db->field_exists('GlassThickness', 'order_items')) {
                        $item_update['GlassThickness'] = $spec_thickness;
                        log_message('debug', 'Updated GlassThickness: ' . $spec_thickness);
                    }
                    if ($spec_edge !== null && $this->db->field_exists('EdgeWork', 'order_items')) {
                        $item_update['EdgeWork'] = $spec_edge;
                        log_message('debug', 'Updated EdgeWork: ' . $spec_edge);
                    }
                    if ($spec_frame !== null && $this->db->field_exists('FrameType', 'order_items')) {
                        $item_update['FrameType'] = $spec_frame;
                        log_message('debug', 'Updated FrameType: ' . $spec_frame);
                    }
                    if ($spec_engraving !== null && $this->db->field_exists('Engraving', 'order_items')) {
                        $item_update['Engraving'] = $spec_engraving;
                        log_message('debug', 'Updated Engraving: ' . $spec_engraving);
                    }
                    
                    // Handle dynamic product-specific customization values
            log_message('debug', '=== DYNAMIC CUSTOMIZATION PROCESSING START ===');
            log_message('debug', 'Dynamic customization received (raw): ' . ($dynamic_customization ?: 'NULL'));
            log_message('debug', 'Is Beginner: ' . ($is_beginner ? 'YES' : 'NO'));
            log_message('debug', 'Target Item ID: ' . ($target_item_id ?: 'NULL'));
            
            if ($dynamic_customization) {
                $dynamic_specs = json_decode($dynamic_customization, true);
                log_message('debug', 'Decoded dynamic specs: ' . json_encode($dynamic_specs));
                log_message('debug', 'JSON decode error: ' . json_last_error_msg());
                        if (is_array($dynamic_specs) && !empty($dynamic_specs)) {
                            // Save to Customization JSON column if it exists
                            if ($this->db->field_exists('Customization', 'order_items')) {
                                // Merge with existing customization data
                                $existing_custom = '';
                                $this->db->reset_query();
                                $existing_row = $this->db->select('Customization')->where('OrderItemID', $target_item_id)->get('order_items')->row();
                                if ($existing_row && !empty($existing_row->Customization)) {
                                    $existing_data = json_decode($existing_row->Customization, true);
                                    if (is_array($existing_data)) {
                                        log_message('debug', 'Existing customization data: ' . json_encode($existing_data));
                                        $dynamic_specs = array_merge($existing_data, $dynamic_specs);
                                    }
                                }
                                $final_customization = json_encode($dynamic_specs);
                                $item_update['Customization'] = $final_customization;
                                log_message('debug', 'Final customization to save: ' . $final_customization);
                            } else {
                                log_message('debug', 'Customization column does not exist in order_items - skipping dynamic customization save');
                            }
                        }
                    }
                    
                    if (!empty($item_update)) {
                        log_message('debug', '=== UPDATING ORDER_ITEMS TABLE ===');
                        log_message('debug', 'OrderItemID: ' . $target_item_id);
                        log_message('debug', 'Update data: ' . json_encode($item_update));
                        
                        $this->db->reset_query();
                        $this->db->where('OrderItemID', $target_item_id);
                        $update_result = $this->db->update('order_items', $item_update);
                        
                        if ($update_result) {
                            log_message('debug', 'Successfully updated order_items for OrderItemID: ' . $target_item_id);
                            log_message('debug', 'Rows affected: ' . $this->db->affected_rows());
                        } else {
                            log_message('error', 'Failed to update order_items for OrderItemID: ' . $target_item_id);
                            log_message('error', 'DB Error: ' . json_encode($this->db->error()));
                            log_message('error', 'Last Query: ' . $this->db->last_query());
                        }
                        
                        // Add verification if Customization column exists
                        if ($this->db->field_exists('Customization', 'order_items')) {
                            $this->db->reset_query();
                            $verify_row = $this->db->select('Customization')->where('OrderItemID', $target_item_id)->get('order_items')->row();
                            if ($verify_row) {
                                log_message('debug', '=== VERIFICATION: Final saved customization ===');
                                log_message('debug', $verify_row->Customization ?: 'NULL');
                            }
                        }
                        log_message('debug', '=== END ORDER_ITEMS UPDATE ===');
                    } else {
                        log_message('debug', 'No item_update data to save (item_update is empty)');
                    }
                } else {
                    if (!$is_beginner) {
                        log_message('info', 'Skipped order_items update: Customer is not a beginner');
                    } else {
                        log_message('error', 'Skipped order_items update: target_item_id is empty');
                    }
                }
            }

            // Handle site photos upload (ocular appointments)
            if (isset($_FILES['site_photos']) && is_array($_FILES['site_photos']['name']) && !empty($_FILES['site_photos']['name'])) {
                $upload_path = './uploads/appointments/site_photos/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
                
                // Get existing site photos
                $existing_photos = [];
                if ($this->db->field_exists('SitePhotos', 'appointments')) {
                    $this->db->select('SitePhotos');
                    $this->db->where('AppointmentID', $appointment_id);
                    $apt = $this->db->get('appointments')->row();
                    if ($apt && !empty($apt->SitePhotos)) {
                        $existing_photos = json_decode($apt->SitePhotos, true) ?: [];
                    }
                }
                
                $uploaded_photos = [];
                foreach ($_FILES['site_photos']['name'] as $key => $filename) {
                    if (!empty($filename) && 
                        isset($_FILES['site_photos']['error'][$key]) && 
                        $_FILES['site_photos']['error'][$key] === UPLOAD_ERR_OK &&
                        isset($_FILES['site_photos']['tmp_name'][$key]) &&
                        isset($_FILES['site_photos']['type'][$key])) {
                        
                        $file_tmp = $_FILES['site_photos']['tmp_name'][$key];
                        $file_type = $_FILES['site_photos']['type'][$key];
                        
                        // Validate image type
                        if (strpos($file_type, 'image/') === 0) {
                            $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
                            $new_filename = 'site_' . $appointment_id . '_' . time() . '_' . $key . '.' . $file_ext;
                            $destination = $upload_path . $new_filename;
                            
                            if (move_uploaded_file($file_tmp, $destination)) {
                                $photo_path = 'uploads/appointments/site_photos/' . $new_filename;
                                $uploaded_photos[] = $photo_path;
                            }
                        }
                    }
                }
                
                // Merge with existing photos
                $all_photos = array_merge($existing_photos, $uploaded_photos);
                
                // Update SitePhotos field if it exists
                if ($this->db->field_exists('SitePhotos', 'appointments') && !empty($all_photos)) {
                    $this->db->where('AppointmentID', $appointment_id);
                    $this->db->update('appointments', [
                        'SitePhotos' => json_encode($all_photos)
                    ]);
                }
            }
            
            // Handle payment receipt upload (ocular payments)
            if ($this->db->table_exists('payment') && isset($_FILES['payment_receipt']) && !empty($_FILES['payment_receipt']['name'])) {
                try {
                    $upload_path = './uploads/payments/ocular/';
                    if (!is_dir($upload_path)) {
                        mkdir($upload_path, 0755, true);
                    }
                    $config = [
                        'upload_path' => $upload_path,
                        'allowed_types' => 'jpg|jpeg|png|pdf',
                        'max_size' => 5120
                    ];
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload('payment_receipt')) {
                    $upload_data = $this->upload->data();
                    $receipt_path = 'uploads/payments/ocular/' . $upload_data['file_name'];
                    $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
                    
                    // Prepare payment update data
                    $payment_update = [
                        'ReceiptPath' => $receipt_path,
                        'Payment_Date' => date('Y-m-d H:i:s')
                    ];
                    
                    // Add payment method if provided
                    if (!empty($downpayment_method)) {
                        $payment_update['PaymentMethod'] = $downpayment_method;
                    }
                    
                    // Add amount if provided
                    if (!empty($downpayment_amount)) {
                        $payment_update['Amount'] = (float)$downpayment_amount;
                    }
                    
                    // Update status based on downpayment status
                    if (!empty($downpayment_status)) {
                        $payment_update['Status'] = $downpayment_status;
                    }
                    
                    if ($payment) {
                        $this->db->where('OrderID', $order_id)->update('payment', $payment_update);
                    } else {
                        $payment_update['OrderID'] = $order_id;
                        if (!isset($payment_update['Status'])) {
                            $payment_update['Status'] = 'Paid';
                        }
                        $this->db->insert('payment', $payment_update);
                    }
                }
                } catch (Exception $e) {
                    log_message('error', 'Payment receipt upload error: ' . $e->getMessage());
                }
            }
            
            // Update payment data even without receipt upload (to save payment method and status)
            log_message('debug', 'Payment data - method: ' . var_export($downpayment_method, true) . 
                ', status: ' . var_export($downpayment_status, true) . 
                ', amount: ' . var_export($downpayment_amount, true) .
                ', order_id: ' . var_export($order_id, true));
            
            if ($this->db->table_exists('payment') && $order_id) {
                $this->db->reset_query();
                $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
                $payment_update = [];
                
                // Handle payment method - only update if value is provided and not empty
                if (!empty($downpayment_method)) {
                    $payment_update['PaymentMethod'] = $downpayment_method;
                }
                
                // Handle payment status - always update if provided (even if 'Pending')
                if (!empty($downpayment_status)) {
                    $payment_update['Status'] = $downpayment_status;
                }
                
                // Handle payment amount - update if provided and numeric
                if ($downpayment_amount !== null && $downpayment_amount !== '' && is_numeric($downpayment_amount)) {
                    $payment_update['Amount'] = (float)$downpayment_amount;
                }
                
                log_message('debug', 'Payment update data: ' . json_encode($payment_update) . 
                    ', payment exists: ' . ($payment ? 'yes' : 'no'));
                
                if (!empty($payment_update)) {
                    if ($payment) {
                        $this->db->reset_query();
                        $this->db->where('OrderID', $order_id);
                        $result = $this->db->update('payment', $payment_update);
                        log_message('debug', 'Payment update result: ' . ($result ? 'success' : 'failed') . 
                            ', affected rows: ' . $this->db->affected_rows());
                    } else {
                        // Create new payment record
                        $payment_update['OrderID'] = $order_id;
                        if (!isset($payment_update['Status'])) {
                            $payment_update['Status'] = $downpayment_status ?: 'Pending';
                        }
                        $this->db->reset_query();
                        $result = $this->db->insert('payment', $payment_update);
                        log_message('debug', 'Payment insert result: ' . ($result ? 'success' : 'failed') .
                            ', insert_id: ' . $this->db->insert_id());
                    }
                } else {
                    log_message('debug', 'Payment update skipped - no data to update');
                }
            } else {
                log_message('debug', 'Payment update skipped - table not exists or no order_id');
            }
            
            // Handle fabrication payment receipt upload
            if ($this->db->table_exists('payment') && isset($_FILES['fabrication_receipt']) && !empty($_FILES['fabrication_receipt']['name'])) {
                try {
                    $upload_path = './uploads/payments/fabrication/';
                    if (!is_dir($upload_path)) {
                        mkdir($upload_path, 0755, true);
                    }
                    $config = [
                        'upload_path' => $upload_path,
                        'allowed_types' => 'jpg|jpeg|png|pdf',
                        'max_size' => 5120
                    ];
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload('fabrication_receipt')) {
                        $upload_data = $this->upload->data();
                        $receipt_path = 'uploads/payments/fabrication/' . $upload_data['file_name'];
                        
                        // Store fabrication receipt in appointments table
                        if ($this->db->field_exists('FabricationReceiptPath', 'appointments')) {
                            $this->db->where('AppointmentID', $appointment_id);
                            $this->db->update('appointments', [
                                'FabricationReceiptPath' => $receipt_path
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    log_message('error', 'Fabrication receipt upload error: ' . $e->getMessage());
                }
            }
            
            // Update fabrication payment data
            if ($fabrication_method !== null || $fabrication_status !== null || $fabrication_amount !== null) {
                // Store fabrication payment data in appointments table fields
                $fab_payment_update = [];
                if ($fabrication_amount !== null && $fabrication_amount !== '' && $this->db->field_exists('FabricationPaymentAmount', 'appointments')) {
                    $fab_payment_update['FabricationPaymentAmount'] = (float)$fabrication_amount;
                }
                if ($fabrication_method !== null && $fabrication_method !== '' && $this->db->field_exists('FabricationPaymentMethod', 'appointments')) {
                    $fab_payment_update['FabricationPaymentMethod'] = $fabrication_method;
                }
                if ($fabrication_status !== null && $fabrication_status !== '' && $this->db->field_exists('FabricationPaymentStatus', 'appointments')) {
                    $fab_payment_update['FabricationPaymentStatus'] = $fabrication_status;
                }
                
                if (!empty($fab_payment_update)) {
                    $this->db->where('AppointmentID', $appointment_id);
                    $this->db->update('appointments', $fab_payment_update);
                }
            }
            
            // Handle installation payment receipt upload (store in order table)
            if ($order_id && isset($_FILES['installation_receipt']) && !empty($_FILES['installation_receipt']['name'])) {
                try {
                    $upload_path = './uploads/payments/installation/';
                    if (!is_dir($upload_path)) {
                        mkdir($upload_path, 0755, true);
                    }
                    $config = [
                        'upload_path' => $upload_path,
                        'allowed_types' => 'jpg|jpeg|png|pdf',
                        'max_size' => 5120
                    ];
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload('installation_receipt')) {
                        $upload_data = $this->upload->data();
                        $receipt_path = 'uploads/payments/installation/' . $upload_data['file_name'];
                        
                        // Store installation receipt in order table
                        if ($this->db->field_exists('InstallationReceiptPath', 'order')) {
                            $this->db->where('OrderID', $order_id);
                            $this->db->update('order', [
                                'InstallationReceiptPath' => $receipt_path
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    log_message('error', 'Installation receipt upload error: ' . $e->getMessage());
                }
            }
            
            // Update installation payment data (store in order table)
            if ($order_id && ($installation_method !== null || $installation_status !== null || $installation_amount !== null)) {
                $inst_payment_update = [];
                if ($installation_amount !== null && $installation_amount !== '' && $this->db->field_exists('InstallationPaymentAmount', 'order')) {
                    $inst_payment_update['InstallationPaymentAmount'] = (float)$installation_amount;
                }
                if ($installation_method !== null && $installation_method !== '' && $this->db->field_exists('InstallationPaymentMethod', 'order')) {
                    $inst_payment_update['InstallationPaymentMethod'] = $installation_method;
                }
                if ($installation_status !== null && $installation_status !== '' && $this->db->field_exists('InstallationPaymentStatus', 'order')) {
                    $inst_payment_update['InstallationPaymentStatus'] = $installation_status;
                }
                
                if (!empty($inst_payment_update)) {
                    $this->db->reset_query();
                    $this->db->where('OrderID', $order_id);
                    $this->db->update('order', $inst_payment_update);
                    log_message('debug', 'Installation payment updated for order ' . $order_id . ': ' . json_encode($inst_payment_update));
                }
            }
            
            // Get current order status
            $current_order = null;
            if ($order_id) {
                $this->db->where('OrderID', $order_id);
                $current_order = $this->db->get('`order`')->row();
            }
            
            // Sync appointment changes to order table
            $order_update = [];
            
            // Check if status is being reverted from Complete to something else
            $is_reverting = false;
            if (isset($previous_status) && isset($status)) {
                $is_reverting = ($previous_status === 'Complete' && $status !== 'Complete' && $status !== null);
            }
            
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
            if (isset($status) && $status === 'Complete' && isset($service) && !empty($service)) {
                // Moving forward: Complete the step
                switch ($service) {
                    case 'Order Placed':
                        // Order Placed complete - typically doesn't change order status
                        // Order status should remain as 'Approved' or whatever it was
                        break;
                    case 'Ocular Visit':
                        // Ocular visit complete - order can move to In Fabrication
                        // Create fabrication appointment/entry after ocular is complete
                        // This should happen regardless of current order status
                        try {
                            if (method_exists($this, 'create_fabrication_appointment')) {
                                $this->create_fabrication_appointment($order_id);
                            }
                        } catch (Exception $e) {
                            log_message('error', 'Failed to create fabrication appointment: ' . $e->getMessage());
                            // Don't fail the entire request if fabrication appointment creation fails
                        }
                        
                        // Update order status to In Fabrication if it's currently Approved or Ocular Pending
                        // Also check if status is NULL/empty (which might happen if 'Ocular Pending' isn't in enum)
                        $current_status = $current_order->Status ?? null;
                        if ($current_order && ($current_status === 'Approved' || $current_status === 'Ocular Pending' || empty($current_status))) {
                            $order_update['Status'] = 'In Fabrication';
                        }
                        
                        // Update OcularCompleted flag in order table
                        if ($this->db->field_exists('OcularCompleted', 'order')) {
                            $order_update['OcularCompleted'] = 1;
                            $order_update['OcularCompletedBy_ID'] = $this->session->userdata('user_id');
                            if (!$order_update['OcularDate']) {
                                $order_update['OcularDate'] = date('Y-m-d');
                            }
                        }
                        
                        // Send notification to customer about fabrication start
                        if ($current_order && $current_order->Customer_ID) {
                            try {
                                $this->load->helper('notification');
                                if (function_exists('send_order_notification')) {
                                    $order_number = $current_order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
                                    $admin_id = $this->session->userdata('user_id');
                                    
                                    send_order_notification(
                                        $current_order->Customer_ID,
                                        $order_id,
                                        'Order in Fabrication',
                                        "Great news! Your order #{$order_number} has completed the ocular visit and is now being fabricated. We'll notify you once fabrication is complete.",
                                        'fa-cog',
                                        $admin_id
                                    );
                                }
                            } catch (Exception $e) {
                                log_message('error', 'Failed to send notification when ocular marked complete: ' . $e->getMessage());
                            }
                        }
                        break;
                    case 'In Fabrication':
                        // Fabrication complete - order can move to Ready for Installation
                        $order_update['Status'] = 'Ready for Installation';
                        break;
                    case 'Installed':
                        // Installation complete - order can move to Completed
                        $order_update['Status'] = 'Completed';
                        
                        // Set installation completed date and payment due date (5 days from completion)
                        if ($this->db->field_exists('InstallationCompletedDate', 'order')) {
                            $order_update['InstallationCompletedDate'] = date('Y-m-d');
                        }
                        if ($this->db->field_exists('InstallationPaymentDueDate', 'order')) {
                            $order_update['InstallationPaymentDueDate'] = date('Y-m-d', strtotime('+5 days'));
                        }
                        
                        // Send notification to customer about installation completion and final payment due
                        if ($current_order && $current_order->Customer_ID) {
                            try {
                                $this->load->helper('notification');
                                if (function_exists('send_order_notification')) {
                                    $order_number = $current_order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
                                    $admin_id = $this->session->userdata('user_id');
                                    $due_date = date('F j, Y', strtotime('+5 days'));
                                    $total_amount = floatval($current_order->TotalAmount ?? 0);
                                    $final_payment = number_format($total_amount * 0.1, 2);
                                    
                                    send_order_notification(
                                        $current_order->Customer_ID,
                                        $order_id,
                                        'Installation Complete - Final Payment Due',
                                        "🎉 Your order #{$order_number} has been successfully installed! Please submit the final payment of ₱{$final_payment} (10%) by {$due_date}. You can pay online via GCash, Maya, or Credit/Debit Card in your Track Order page. Failure to pay within 5 days may result in product removal.",
                                        'fa-check-circle',
                                        $admin_id
                                    );
                                }
                            } catch (Exception $e) {
                                log_message('error', 'Failed to send installation completion notification: ' . $e->getMessage());
                            }
                            
                            // Send email notification with payment link
                            try {
                                $this->send_installation_payment_email($order_id);
                            } catch (Exception $e) {
                                log_message('error', 'Failed to send installation payment email: ' . $e->getMessage());
                            }
                        }
                        break;
                }
            } elseif ($is_reverting) {
                // Reverting from Complete: Cascade backwards step-by-step
                // When reverting a step, automatically revert all later steps that depend on it
                $services_order = ['Order Placed', 'Ocular Visit', 'In Fabrication', 'Installed', 'Completed'];
                $current_service_index = array_search($service, $services_order);
                
                // Get all appointments for this order to check dependencies
                $all_appointments = [];
                if ($order_id) {
                    try {
                        $this->db->where('OrderID', $order_id);
                        $all_appointments = $this->db->get('appointments')->result();
                        if (!$all_appointments) {
                            $all_appointments = [];
                        }
                    } catch (Exception $e) {
                        log_message('error', 'Failed to get appointments: ' . $e->getMessage());
                        $all_appointments = [];
                    }
                }
                
                // Step 1: Revert all later steps that are complete (cascade backwards)
                if ($current_service_index !== false && is_array($all_appointments)) {
                $later_services = array_slice($services_order, $current_service_index + 1);
                
                foreach ($later_services as $later_service) {
                    // Find appointments for this later service
                    if (is_array($all_appointments)) {
                        foreach ($all_appointments as $apt) {
                            if (isset($apt->Service) && isset($apt->Status) && isset($apt->AppointmentID) && 
                                $apt->Service === $later_service && $apt->Status === 'Complete') {
                                // Revert this later step to 'In Progress' (or 'Cancelled' if that's what was selected)
                                $revert_status = (isset($status) && $status === 'Cancelled') ? 'Cancelled' : 'In Progress';
                                try {
                                    $this->db->where('AppointmentID', $apt->AppointmentID)
                                             ->update('appointments', [
                                                 'Status' => $revert_status,
                                                 'Updated_Date' => date('Y-m-d H:i:s')
                                             ]);
                                } catch (Exception $e) {
                                    log_message('error', 'Failed to revert appointment: ' . $e->getMessage());
                                }
                            }
                        }
                    }
                    
                    // Also handle projectschedule table for "In Fabrication" service
                    if ($later_service === 'In Fabrication' && $this->db->table_exists('projectschedule')) {
                        $fabrication_project = $this->db->where('OrderID', $order_id)
                                                       ->get('projectschedule')
                                                       ->row();
                        if ($fabrication_project && $fabrication_project->Status === 'Completed') {
                            // Revert fabrication project status
                            $revert_project_status = (isset($status) && $status === 'Cancelled') ? 'Delayed' : 'In progress';
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
                    if (!$has_later_complete && $current_order) {
                        $current_status = $current_order->Status ?? null;
                        if ($current_status === 'In Fabrication') {
                            // Revert to Ocular Pending if it exists in enum, otherwise Approved
                            $order_update['Status'] = 'Ocular Pending'; // Will fail if not in enum, but we'll handle that
                        }
                        
                        // Clear OcularCompleted flag
                        if ($this->db->field_exists('OcularCompleted', 'order')) {
                            $order_update['OcularCompleted'] = 0;
                            $order_update['OcularCompletedBy_ID'] = null;
                        }
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
            } // Close elseif ($is_reverting) block
            
            // Update order table if there are changes
            $old_status = ($current_order && isset($current_order->Status)) ? $current_order->Status : null;
            $new_status = null;
            $installation_date_set = false;
            $scheduled_date_value = null;
            
            if (!empty($order_update) && $order_id) {
                try {
                    $this->db->where('OrderID', $order_id);
                    $update_result = $this->db->update('`order`', $order_update);
                    
                    // Check for database errors immediately
                    $db_error = $this->db->error();
                    if (!empty($db_error['code'])) {
                        $error_msg = $db_error['message'];
                        log_message('error', 'Database error updating order in update_appointment_ajax: ' . $error_msg);
                        
                        // If status enum error, try to handle gracefully
                        if (stripos($error_msg, 'enum') !== false || stripos($error_msg, 'data truncated') !== false) {
                            // Remove the problematic Status field and try again
                            if (isset($order_update['Status'])) {
                                $bad_status = $order_update['Status'];
                                unset($order_update['Status']);
                                log_message('warning', "Removed invalid status '{$bad_status}' from order update");
                                
                                // Try update again without the Status field
                                $this->db->where('OrderID', $order_id);
                                $update_result = $this->db->update('`order`', $order_update);
                                
                                // Log that status update was skipped
                                log_message('warning', "Order status update skipped - '{$bad_status}' not in enum. Please run migration script.");
                            }
                        }
                    }
                    
                    if (!$update_result && empty($db_error['code'])) {
                        log_message('error', 'Update returned false but no database error - possible no rows affected');
                    }
                    
                    // Track status changes and delivery date
                    if (isset($order_update['Status']) && !isset($db_error['code'])) {
                        $new_status = $order_update['Status'];
                    }
                    if (isset($order_update['InstallationDate'])) {
                        $installation_date_set = true;
                        $scheduled_date_value = $order_update['InstallationDate'];
                    } elseif (isset($order_update['EstimatedDelivery'])) {
                        $installation_date_set = true;
                        $scheduled_date_value = $order_update['EstimatedDelivery'];
                    }
                } catch (Exception $e) {
                    log_message('error', 'Exception updating order in update_appointment_ajax: ' . $e->getMessage());
                    log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                }
            }
            
            // Complete transaction
            $this->db->trans_complete();
            
            log_message('debug', 'Transaction completed, checking status');
            
            if ($this->db->trans_status() === FALSE) {
                $error = $this->db->error();
                log_message('error', 'Transaction failed in update_appointment_ajax: ' . json_encode($error));
                $this->output->set_output(json_encode([
                    'success' => false, 
                    'message' => 'Failed to update appointment: ' . ($error['message'] ?? 'Transaction failed')
                ]));
            } else {
                log_message('debug', 'Appointment updated successfully: ' . $appointment_id);
                $this->output->set_output(json_encode([
                    'success' => true,
                    'message' => 'Appointment updated successfully',
                    'order_updated' => !empty($order_update)
                ]));
            }
        } catch (Exception $e) {
            log_message('error', 'Exception in update_appointment_ajax: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ]));
        }
        
        log_message('debug', '=== update_appointment_ajax END ===');
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
     * Get site photos for an appointment
     */
    public function get_site_photos_ajax()
    {
        header('Content-Type: application/json');
        
        $appointment_id = $this->input->get('appointment_id');
        if (!$appointment_id) {
            echo json_encode(['success' => false, 'message' => 'Appointment ID required']);
            return;
        }
        
        if (!$this->db->field_exists('SitePhotos', 'appointments')) {
            echo json_encode(['success' => true, 'photos' => []]);
            return;
        }
        
        $this->db->select('SitePhotos');
        $this->db->where('AppointmentID', $appointment_id);
        $appointment = $this->db->get('appointments')->row();
        
        $photos = [];
        if ($appointment && !empty($appointment->SitePhotos)) {
            $photo_paths = json_decode($appointment->SitePhotos, true) ?: [];
            foreach ($photo_paths as $path) {
                if (file_exists('./' . $path)) {
                    $photos[] = [
                        'path' => $path,
                        'url' => base_url($path)
                    ];
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'photos' => $photos
        ]);
    }
    
    /**
     * Delete a site photo
     */
    public function delete_site_photo()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $appointment_id = $this->input->post('appointment_id');
        $photo_path = $this->input->post('photo_path');
        
        if (!$appointment_id || !$photo_path) {
            echo json_encode(['success' => false, 'message' => 'Appointment ID and photo path required']);
            return;
        }
        
        if (!$this->db->field_exists('SitePhotos', 'appointments')) {
            echo json_encode(['success' => false, 'message' => 'SitePhotos field does not exist']);
            return;
        }
        
        // Get current photos
        $this->db->select('SitePhotos');
        $this->db->where('AppointmentID', $appointment_id);
        $appointment = $this->db->get('appointments')->row();
        
        if (!$appointment) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }
        
        $photos = [];
        if (!empty($appointment->SitePhotos)) {
            $photos = json_decode($appointment->SitePhotos, true) ?: [];
        }
        
        // Remove photo from array
        $photos = array_filter($photos, function($p) use ($photo_path) {
            return $p !== $photo_path;
        });
        $photos = array_values($photos); // Re-index array
        
        // Delete file from filesystem
        $file_path = './' . $photo_path;
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        
        // Update database
        $this->db->where('AppointmentID', $appointment_id);
        $this->db->update('appointments', [
            'SitePhotos' => !empty($photos) ? json_encode($photos) : null
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Photo deleted successfully'
        ]);
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
    
    // For admin view, show ALL products (including unavailable) so admin can manage them
    $data['products'] = $allProducts;

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
            o.Status as OrderStatus,
            o.PaymentStatus,
            o.PaymentMethod as OrderPaymentMethod,
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
            payment.PaymentMethod as PaymentMethod,
            payment.payment_milestone
        ');
        $this->db->from('`order` o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user', 'user.UserID = c.UserID', 'left');
        
        // Join customer table for role info
        if ($this->db->field_exists('role', 'customer')) {
            $this->db->select('c.role as customer_role');
        }
        $this->db->select('user.Role as user_role');
        
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product', 'product.Product_ID = oi.Product_ID', 'left');
        $this->db->join('payment', 'payment.OrderID = o.OrderID', 'left');
        
        // Show all orders that have payment records OR are past the booking stage
        // In milestone payment system, orders can have payments at any stage
        // Include: All orders with payment records + orders that should have payments
        $this->db->where("(
            payment.Payment_ID IS NOT NULL 
            OR o.Status IN ('Approved', 'Ocular Pending', 'Ocular Visit', 'In Fabrication', 'Ready for Installation', 'Installation/Delivery', 'Completed')
        )", NULL, FALSE);
        
        // Exclude cancelled/rejected orders
        $this->db->where("o.Status NOT IN ('Cancelled', 'Rejected', 'Draft')", NULL, FALSE);
        
        // Order by most recent first (payment date if exists, otherwise order date)
        $this->db->order_by("COALESCE(payment.Payment_Date, o.Approved_Date, o.OrderDate)", 'DESC', FALSE);
        $this->db->group_by('o.OrderID'); // Group to avoid duplicates from multiple order_items
        $orders = $this->db->get()->result();
        
        // Calculate weekly sales (last 7 days) - using Payment_Date from payment table
        $week_start = date('Y-m-d', strtotime('-7 days'));
        $this->db->select_sum('Amount');
        $this->db->from('payment');
        $this->db->where('Payment_Date >=', $week_start);
        $this->db->where_in('Status', ['Paid', 'Verified']);
        $weekly_sales_result = $this->db->get()->row();
        $weekly_sales = $weekly_sales_result->Amount ?? 0;
        
        // Calculate monthly sales (last 30 days)
        $month_start = date('Y-m-d', strtotime('-30 days'));
        $this->db->select_sum('Amount');
        $this->db->from('payment');
        $this->db->where('Payment_Date >=', $month_start);
        $this->db->where_in('Status', ['Paid', 'Verified']);
        $monthly_sales_result = $this->db->get()->row();
        $monthly_sales = $monthly_sales_result->Amount ?? 0;
        
        // Get milestone breakdown for revenue report
        $this->db->select('payment_milestone, COUNT(*) as count, SUM(Amount) as total');
        $this->db->from('payment');
        $this->db->where('Payment_Date >=', $month_start);
        $this->db->where_in('Status', ['Paid', 'Verified']);
        $this->db->group_by('payment_milestone');
        $milestone_data = $this->db->get()->result();
        
        $milestone_breakdown = [
            'ocular_50' => 0,
            'fabrication_40' => 0,
            'installation_10' => 0
        ];
        foreach ($milestone_data as $item) {
            if ($item->payment_milestone) {
                $milestone_breakdown[$item->payment_milestone] = $item->total ?? 0;
            }
        }
        
        // Count pending and overdue payments
        $pending_count = 0;
        $overdue_count = 0;
        foreach ($orders as $order) {
            // Get payment status from payment table if available, otherwise from order table
            $payment_status = $order->PaymentStatus ?? 'Pending';
            
            // Count pending payments
            if ($payment_status === 'Pending') {
                $pending_count++;
            }
            
            // Check if overdue based on order status and milestone
            $is_overdue = false;
            $days_threshold = 7; // Default
            
            // Adjust threshold based on milestone
            if ($order->payment_milestone === 'fabrication_40') {
                $days_threshold = 3; // Fabrication payment overdue after 3 days
            } elseif ($order->payment_milestone === 'installation_10') {
                $days_threshold = 5; // Installation payment overdue after 5 days
            }
            
            // Check if overdue (more than threshold days and still pending/under review)
            if (($payment_status === 'Pending' || $payment_status === 'Under Review') && $order->Approved_Date) {
                $approved_date = strtotime($order->Approved_Date);
                $days_since = (time() - $approved_date) / (60 * 60 * 24);
                if ($days_since > $days_threshold) {
                    $overdue_count++;
                    $is_overdue = true;
                }
            }
            
            // Add overdue flag to order object
            $order->is_overdue = $is_overdue;
        }
        
        $data['orders'] = $orders;
        $data['weekly_sales'] = $weekly_sales;
        $data['monthly_sales'] = $monthly_sales;
        $data['milestone_breakdown'] = $milestone_breakdown;
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
        }
        catch (Exception $e) {
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
        }
        catch (Exception $e) {
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

    /**
     * Get payment timeline for an order
     * Shows all milestones: 50% Ocular, 40% Fabrication, 10% Installation
     */
    public function get_payment_timeline()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        try {
            // Get order details
            $this->db->select('o.*, u.First_Name, u.Last_Name, c.role as customer_role, u.Role as user_role');
            $this->db->from('`order` o');
            $this->db->join('customer c', 'c.Customer_ID = o.Customer_ID', 'left');
            $this->db->join('user u', 'u.UserID = c.UserID', 'left');
            $this->db->where('o.OrderID', $order_id);
            $order = $this->db->get()->row();
            
            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                return;
            }
            
            $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
            $customer_role = ucfirst($order->customer_role ?? $order->user_role ?? 'Customer');
            $total_amount = (float)$order->TotalAmount;
            
            // Calculate milestone amounts
            $ocular_amount = $total_amount * 0.50;
            $fabrication_amount = $total_amount * 0.40;
            $installation_amount = $total_amount * 0.10;
            
            // Get all payments for this order
            $this->db->where('OrderID', $order_id);
            $this->db->order_by('Payment_Date', 'ASC');
            $payments = $this->db->get('payment')->result();
            
            // Build timeline data structure
            $timeline = [
                'ocular_50' => [
                    'label' => '50% Ocular Visit Payment',
                    'amount' => $ocular_amount,
                    'status' => 'not_yet',
                    'payment' => null
                ],
                'fabrication_40' => [
                    'label' => '40% Fabrication Payment',
                    'amount' => $fabrication_amount,
                    'status' => 'not_yet',
                    'payment' => null
                ],
                'installation_10' => [
                    'label' => '10% Installation Payment',
                    'amount' => $installation_amount,
                    'status' => 'not_yet',
                    'payment' => null
                ]
            ];
            
            // Map payments to milestones
            // Also check appointments table for fabrication and installation payments
            $fabrication_apt = $this->db->where('OrderID', $order_id)
                                        ->where('Service', 'In Fabrication')
                                        ->get('appointments')->row();
            $installation_apt = $this->db->where('OrderID', $order_id)
                                         ->where('Service', 'Installed')
                                         ->get('appointments')->row();
            
            foreach ($payments as $payment) {
                $milestone = $payment->payment_milestone ?? 'ocular_50';
                if (isset($timeline[$milestone])) {
                    $status = 'pending';
                    if ($payment->Status === 'Paid' || $payment->Status === 'Verified') {
                        $status = 'verified';
                    } elseif ($payment->Status === 'Failed') {
                        $status = 'failed';
                    }
                    
                    $timeline[$milestone]['status'] = $status;
                    $timeline[$milestone]['payment'] = [
                        'payment_id' => $payment->Payment_ID,
                        'amount' => (float)$payment->Amount,
                        'date' => $payment->Payment_Date,
                        'method' => $payment->PaymentMethod,
                        'receipt_path' => $payment->ReceiptPath,
                        'transaction_id' => $payment->Transaction_ID,
                        'status' => $payment->Status
                    ];
                }
            }
            
            // Also check fabrication payment from appointments table
            if ($fabrication_apt && !empty($fabrication_apt->FabricationPaymentStatus) && $fabrication_apt->FabricationPaymentStatus !== 'Pending') {
                $fab_status = 'pending';
                if ($fabrication_apt->FabricationPaymentStatus === 'Paid' || $fabrication_apt->FabricationPaymentStatus === 'Verified') {
                    $fab_status = 'verified';
                } elseif ($fabrication_apt->FabricationPaymentStatus === 'Failed') {
                    $fab_status = 'failed';
                }
                
                // If fabrication payment exists in appointments but not in payment table, include it
                if (!isset($timeline['fabrication_40']['payment']) || is_null($timeline['fabrication_40']['payment'])) {
                    $timeline['fabrication_40']['status'] = $fab_status;
                    $timeline['fabrication_40']['payment'] = [
                        'payment_id' => 'apt_' . $fabrication_apt->AppointmentID,
                        'amount' => (float)($fabrication_apt->FabricationPaymentAmount ?? 0),
                        'date' => $fabrication_apt->Updated_Date ?? date('Y-m-d H:i:s'),
                        'method' => $fabrication_apt->FabricationPaymentMethod ?? '',
                        'receipt_path' => $fabrication_apt->FabricationReceiptPath ?? null,
                        'transaction_id' => $fabrication_apt->FabricationTransactionID ?? null,
                        'status' => $fabrication_apt->FabricationPaymentStatus
                    ];
                }
            }
            
            // Also check installation payment from appointments/order table
            if ($installation_apt && !empty($installation_apt->InstallationPaymentStatus) && $installation_apt->InstallationPaymentStatus !== 'Pending') {
                $inst_status = 'pending';
                if ($installation_apt->InstallationPaymentStatus === 'Paid' || $installation_apt->InstallationPaymentStatus === 'Verified') {
                    $inst_status = 'verified';
                } elseif ($installation_apt->InstallationPaymentStatus === 'Failed') {
                    $inst_status = 'failed';
                }
                
                // If installation payment exists in appointments but not in payment table, include it
                if (!isset($timeline['installation_10']['payment']) || is_null($timeline['installation_10']['payment'])) {
                    $timeline['installation_10']['status'] = $inst_status;
                    $timeline['installation_10']['payment'] = [
                        'payment_id' => 'apt_' . $installation_apt->AppointmentID,
                        'amount' => (float)($installation_apt->InstallationPaymentAmount ?? $installation_amount),
                        'date' => $installation_apt->Updated_Date ?? date('Y-m-d H:i:s'),
                        'method' => $installation_apt->InstallationPaymentMethod ?? '',
                        'receipt_path' => $installation_apt->InstallationReceiptPath ?? null,
                        'transaction_id' => $installation_apt->InstallationTransactionID ?? null,
                        'status' => $installation_apt->InstallationPaymentStatus
                    ];
                }
            }
            
            // Also check order table for installation payment (as backup)
            if (empty($timeline['installation_10']['payment']) || is_null($timeline['installation_10']['payment'])) {
                if (!empty($order->InstallationPaymentStatus) && $order->InstallationPaymentStatus !== 'Pending') {
                    $inst_status = 'pending';
                    if ($order->InstallationPaymentStatus === 'Paid' || $order->InstallationPaymentStatus === 'Verified') {
                        $inst_status = 'verified';
                    } elseif ($order->InstallationPaymentStatus === 'Failed') {
                        $inst_status = 'failed';
                    }
                    
                    $timeline['installation_10']['status'] = $inst_status;
                    $timeline['installation_10']['payment'] = [
                        'payment_id' => 'ord_' . $order->OrderID,
                        'amount' => (float)($order->InstallationPaymentAmount ?? $installation_amount),
                        'date' => $order->Modified_Date ?? date('Y-m-d H:i:s'),
                        'method' => $order->InstallationPaymentMethod ?? '',
                        'receipt_path' => $order->InstallationReceiptPath ?? null,
                        'transaction_id' => $order->InstallationTransactionID ?? null,
                        'status' => $order->InstallationPaymentStatus
                    ];
                }
            }
            
            // Calculate progress
            $paid_amount = 0;
            foreach ($timeline as $milestone) {
                if ($milestone['status'] === 'verified') {
                    $paid_amount += $milestone['amount'];
                }
            }
            $progress_percentage = $total_amount > 0 ? round(($paid_amount / $total_amount) * 100) : 0;
            
            echo json_encode([
                'success' => true,
                'order_number' => $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT),
                'customer_name' => $customer_name,
                'customer_role' => $customer_role,
                'total_amount' => $total_amount,
                'paid_amount' => $paid_amount,
                'remaining_amount' => $total_amount - $paid_amount,
                'progress_percentage' => $progress_percentage,
                'timeline' => $timeline
            ]);
        } catch (Exception $e) {
            log_message('error', 'Error in get_payment_timeline: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
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
        
        // Debug: Log that this method is being called
        log_message('debug', 'get_orders_ajax called - params: ' . json_encode($this->input->get()));
        log_message('debug', 'get_orders_ajax - Session data: ' . json_encode([
            'is_logged_in' => $this->session->userdata('is_logged_in'),
            'user_role' => $this->session->userdata('user_role')
        ]));
        
        // Check if database is accessible
        if (!$this->db->conn_id) {
            log_message('error', 'get_orders_ajax - Database connection failed');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection error',
                'orders' => [],
                'total' => 0,
                'page' => 1,
                'limit' => 10,
                'total_pages' => 0
            ]);
            return;
        }
        
        // Check if order table exists
        if (!$this->db->table_exists('order')) {
            log_message('error', 'get_orders_ajax - Order table does not exist');
            echo json_encode([
                'success' => false,
                'message' => 'Order table not found',
                'orders' => [],
                'total' => 0,
                'page' => 1,
                'limit' => 10,
                'total_pages' => 0
            ]);
            return;
        }
        
        log_message('debug', 'get_orders_ajax - Database connection OK, order table exists');
        
        try {
        $status_filter = $this->input->get('status');
        // order_type removed - all orders now unified
        $search = $this->input->get('search');
        $client_search = $this->input->get('client_search');
        $order_search = $this->input->get('order_search');
        $date_start = $this->input->get('date_start');
        $date_end = $this->input->get('date_end');
        $month_year = $this->input->get('month_year'); // Format: YYYY-MM
        $ocular_status = $this->input->get('ocular_status'); // For ocular status filtering
        $month = $this->input->get('month'); // 0-11 (legacy)
        $year = $this->input->get('year'); // (legacy)
        $page = $this->input->get('page') ?: 1;
        $limit = $this->input->get('limit') ?: 10;
        $offset = ($page - 1) * $limit;

        $ocular_completed_field_exists = $this->db->field_exists('OcularCompleted', 'order');
        $order_items_table_exists = $this->db->table_exists('order_items');
        $product_table_exists = $this->db->table_exists('product');
        
        // Build count query first (simple, no joins to avoid duplication)
        $this->db->reset_query();
        $this->db->from('`order` o');
        
        // Order type filtering removed - all orders are now unified
        
        // Apply status filter
        if ($status_filter && $status_filter !== 'all' && $status_filter !== 'all orders') {
            // Support a virtual "on_going" filter that maps to multiple active statuses
            if ($status_filter === 'on_going') {
                $ongoing_statuses = ['Pending Review', 'Awaiting Admin', 'Approved', 'Ocular Pending', 'In Fabrication', 'Ready for Installation'];
                $this->db->where_in('o.Status', $ongoing_statuses);
            } else {
                $this->db->where('o.Status', $status_filter);
            }
        }
        
        // Apply ocular status filter (all orders now go through ocular)
        if ($ocular_status && $ocular_status !== 'all') {
            if ($ocular_completed_field_exists) {
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
            // For product name search, use subquery only if required tables exist
            if ($order_items_table_exists && $product_table_exists) {
                $this->db->or_where("EXISTS (SELECT 1 FROM order_items oi2 JOIN product p2 ON oi2.Product_ID = p2.Product_ID WHERE oi2.OrderID = o.OrderID AND p2.ProductName LIKE '%" . $this->db->escape_like_str($search) . "%')", null, false);
            }
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
        $select_fields = [
            'o.OrderID',
            'o.OrderNumber',
            'o.OrderDate',
            'o.TotalAmount',
            'o.Status',
            'o.PaymentStatus',
            'o.DeliveryAddress'
        ];
        // OrderType field removed from selection - no longer needed
        $select_fields[] = 'c.Customer_ID';
        $select_fields[] = 'u.First_Name';
        $select_fields[] = 'u.Last_Name';
        $select_fields[] = 'u.Middle_Name';
        $select_fields[] = 'u.Email';
        $select_fields[] = 'u.PhoneNum';
        $select_fields[] = 'c.role AS customer_role';
        $select_fields[] = 'c.experience_data AS customer_experience_data';
        $select_fields[] = 'c.setup_status AS customer_setup_status';
        if ($order_items_table_exists && $product_table_exists) {
            $select_fields[] = 'p.ProductName';
        }
        $this->db->select(implode(', ', $select_fields));
        $this->db->from('`order` o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        if ($order_items_table_exists) {
            $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        }
        if ($order_items_table_exists && $product_table_exists) {
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        }
        $this->db->group_by('o.OrderID');
        
        // Reapply filters for data query (order type filtering removed - all orders unified)
        
        if ($status_filter && $status_filter !== 'all' && $status_filter !== 'all orders') {
            if ($status_filter === 'on_going') {
                $ongoing_statuses = ['Pending Review', 'Awaiting Admin', 'Approved', 'Ocular Pending', 'In Fabrication', 'Ready for Installation'];
                $this->db->where_in('o.Status', $ongoing_statuses);
            } else {
                $this->db->where('o.Status', $status_filter);
            }
        }
        
        // Apply ocular status filter (all orders now go through ocular)
        if ($ocular_status && $ocular_status !== 'all') {
            if ($ocular_completed_field_exists) {
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
            if ($order_items_table_exists && $product_table_exists) {
                $this->db->or_like('p.ProductName', $search);
            }
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
            
            // Determine customer role display
            $roleDisplay = '';
            if (isset($order->customer_setup_status) && $order->customer_setup_status === 'completed' && isset($order->customer_role)) {
                if ($order->customer_role === 'beginner') {
                    $roleDisplay = 'Beginner';
                } elseif ($order->customer_role === 'professional') {
                    $professionType = '';
                    if (isset($order->customer_experience_data)) {
                        $experienceData = json_decode($order->customer_experience_data, true);
                        if (isset($experienceData['profession_type'])) {
                            $professionType = ucfirst($experienceData['profession_type']);
                            if ($professionType === 'Other' && isset($experienceData['profession_type_other'])) {
                                $professionType = $experienceData['profession_type_other'];
                            }
                        }
                    }
                    $roleDisplay = $professionType ? 'Professional (' . $professionType . ')' : 'Professional';
                }
            }
            
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
                'customer_phone' => $order->PhoneNum ?? '',
                'customer_role' => $roleDisplay
            ];
            
            // Add ocular status for all orders (unified flow)
            $formatted_order['ocular_status'] = 'Pending'; // Default
            if ($ocular_completed_field_exists) {
                $formatted_order['ocular_status'] = (isset($order->OcularCompleted) && $order->OcularCompleted) ? 'Completed' : 'Pending';
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
        
        }
        catch (Exception $e) {
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
        // Suppress PHP errors from appearing in output (they break JSON)
        error_reporting(0);
        ini_set('display_errors', 0);
        
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
        
        // Get preferred ocular visit date - check direct column first, then SpecialInstructions
        $preferred_installation_date = 'N/A';
        if (isset($order->PreferredInstallationDate) && !empty($order->PreferredInstallationDate)) {
            $preferred_installation_date = date('F j, Y', strtotime($order->PreferredInstallationDate));
        } elseif (isset($order->SpecialInstructions) && !empty($order->SpecialInstructions)) {
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
        
        $ocular_staff_id = null;
        if ($ocular_appointment) {
            $ocular_notes = $ocular_appointment->OcularNotes ?? '';
            $ocular_date = $ocular_appointment->AppointmentDate ? date('F j, Y', strtotime($ocular_appointment->AppointmentDate)) : 'N/A';
            $ocular_completed = ($ocular_appointment->Status === 'Complete');
            
            if (!empty($ocular_appointment->AssignedStaff)) {
                $ocular_staff_name = $ocular_appointment->AssignedStaff;
            }
            
            // Get ocular staff ID
            if ($this->db->field_exists('AssignedStaff_ID', 'appointments')) {
                $ocular_staff_id = $ocular_appointment->AssignedStaff_ID ?? null;
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
        
        // Get payment receipt URL
        $receipt_url = null;
        if ($payment && !empty($payment->ReceiptPath)) {
            $receipt_url = base_url($payment->ReceiptPath);
        }
        
        // Build payment breakdown data (50-40-10 split)
        // Only populate downpayment_amount if actual payment exists - do not calculate fallback
        // The downpayment will be managed in the Ocular Visit appointment
        $total_amount = floatval($order->TotalAmount ?? 0);
        $payment_breakdown = [
            'downpayment_amount' => $payment && isset($payment->Amount) ? $payment->Amount : null,
            'downpayment_method' => $payment ? ($payment->PaymentMethod ?? null) : null,
            'downpayment_status' => $payment ? ($payment->Status ?? 'Pending') : 'Pending',
            'fabrication_amount' => null, // TODO: Will be populated when fabrication payment is added
            'fabrication_method' => null,
            'fabrication_status' => 'Pending',
            'fabrication_receipt_url' => null,
            'installation_amount' => null, // TODO: Will be populated when installation payment is added
            'installation_method' => null,
            'installation_status' => 'Pending',
            'installation_receipt_url' => null
        ];
        
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
                'estimate_price' => $order->TotalAmount ?? 0,
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
                'ocular_staff_id' => $ocular_staff_id,
                'ocular_completed' => $ocular_completed,
                'shape' => $order->GlassShape ?? 'N/A',
                'dimension' => $order->Dimensions ?? 'N/A',
                'type' => $order->GlassType ?? 'N/A',
                'thickness' => $order->GlassThickness ?? 'N/A',
                'edge_work' => $order->EdgeWork ?? 'N/A',
                'frame_type' => $order->FrameType ?? 'N/A',
                'engraving' => $order->Engraving ?? 'N/A',
                'file_attached' => (isset($order->DesignRef) ? $order->DesignRef : null) ?? 'N/A',
                'file_url' => (isset($order->DesignRef) && $order->DesignRef) ? base_url($order->DesignRef) : null,
                'special_instructions' => $order->SpecialInstructions ?? 'N/A',
                'preferred_installation_date' => $preferred_installation_date,
                'items' => $items,
                'payment_status' => $payment_status,
                'payment_method' => $payment_method,
                'payment_date' => $payment_date,
                'receipt_url' => $receipt_url,
                'payment_data' => $payment_breakdown
            ]
        ];
        
        echo json_encode($response);
        }
        catch (Exception $e) {
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
            }
        catch (Exception $e) {
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
                }
        catch (Exception $e) {
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
                }
        catch (Exception $e) {
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
                }
        catch (Exception $e) {
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
                }
        catch (Exception $e) {
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
                    }
        catch (Exception $e) {
                        $requested_date = 'N/A';
                    }
                }
                
                $total_amount = '0.00';
                if (isset($order->TotalAmount)) {
                    try {
                        $total_amount = number_format((float)$order->TotalAmount, 2, '.', '');
                    }
        catch (Exception $e) {
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
            }
        catch (Exception $e) {
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
            
        }
        catch (Exception $e) {
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
        // Log order type/status for debugging why direct orders may be routed to ocular
        $order_type_raw = isset($order->OrderType) ? trim($order->OrderType) : '';
        $order_status_raw = isset($order->Status) ? trim($order->Status) : '';
        log_message('info', "AdminCon::approve_order_admin - Order fetched: ID={$order_id_numeric}, OrderNumber={$order_id_clean}, OrderType='{$order_type_raw}', Status='{$order_status_raw}'");
        
        log_message('error', "AdminCon::approve_order_admin - [BEFORE CALL] About to call admin_approve_order. Order ID: {$order_id_numeric}, Admin ID: {$admin_id}");
        
        // Use Order_model function
        $result = $this->Order_model->admin_approve_order($order_id_numeric, $admin_id, $admin_notes);
        
        if ($result['success']) {
            $result['order_id'] = $order_id_clean;
            
            // Send notification to customer (guaranteed - call directly here as backup)
            if (!empty($order->Customer_ID)) {
                try {
                    $this->load->helper('notification');
                    if (function_exists('send_order_notification')) {
                        // Get ocular appointment date if available
                        $this->db->reset_query();
                        $this->db->where('OrderID', $order_id_numeric);
                        $this->db->where('Service', 'Ocular Visit');
                        $ocular_appointment = $this->db->get('appointments')->row();
                        
                        $ocular_date = 'TBD - We will contact you to schedule';
                        if ($ocular_appointment && !empty($ocular_appointment->AppointmentDate)) {
                            $formatted_date = date('F j, Y', strtotime($ocular_appointment->AppointmentDate));
                            $ocular_date = $formatted_date;
                        }
                        
                        send_order_notification(
                            $order->Customer_ID,
                            $order_id_numeric,
                            'Order Approved',
                            "Your order #{$order_id_clean} has been approved and will move to ocular visit! Ocular visit scheduled for {$ocular_date}. We'll contact you soon with more details.",
                            'fa-check-circle',
                            $admin_id
                        );
                    }
                }
        catch (Exception $e) {
                    log_message('error', 'AdminCon::approve_order_admin - Notification error: ' . $e->getMessage());
                }
            }
            
            // Immediately sync approved orders to create Ocular Visit appointment
            // This ensures the order goes to Ocular Visit appointments page (not fabrication queue yet)
            // Wrap in try-catch to prevent breaking approval if sync fails
            try {
                $this->db->reset_query();
                $this->sync_approved_orders_to_appointments();
            }
        catch (Exception $e) {
                log_message('error', 'AdminCon::approve_order_admin - Failed to sync appointments: ' . $e->getMessage());
                // Don't fail the approval if sync fails, but log the error
                // The sync will happen automatically when appointments page loads anyway
            } catch (Error $e) {
                log_message('error', 'AdminCon::approve_order_admin - Fatal error syncing appointments: ' . $e->getMessage());
                // Don't fail the approval if sync fails
            }
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
            'Pending Payment' => 'Pending Payment',
            'Payment Verified' => 'Payment Verified',
            'Paid' => 'Paid',
            'Approved' => 'Confirmed',
            'Ocular Pending' => 'Ocular Pending',
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
     * @param bool $show_as_notification - If false, this activity won't appear in notification feeds (default: false for internal admin actions)
     */
    private function log_activity($action, $description, $role, $user_id = null, $user_name = null, $related_id = null, $related_type = null, $show_as_notification = false)
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
        
        // Add flag to indicate if this should show as notification (for external events like customer actions)
        // Internal admin actions should not show as notifications
        try {
            if ($this->db->table_exists('system_activity_log') && $this->db->field_exists('ShowAsNotification', 'system_activity_log')) {
                $data['ShowAsNotification'] = $show_as_notification ? 1 : 0;
            }
        }
        catch (Exception $e) {
            // If field check fails, continue without the flag
            log_message('debug', 'Could not check ShowAsNotification field: ' . $e->getMessage());
        }
        
        try {
            $this->db->insert('system_activity_log', $data);
        }
        catch (Exception $e) {
            // Log the error but don't break execution
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }

    // Notifications
    public function admin_notif()
    {
        // Load pagination library
        $this->load->library('pagination');
        
        // Pagination settings
        $per_page = 15;
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $offset = ($page - 1) * $per_page;
        
        // Initialize notifications array
        $all_notifications = [];
        $total_notifications = 0;
        
        // Check if system_activity_log table exists and fetch notifications
        if ($this->db->table_exists('system_activity_log')) {
            try {
                // Get total count of notifications
                $this->db->reset_query();
                
                // Apply filters
                try {
                    if ($this->db->table_exists('system_activity_log') && $this->db->field_exists('ShowAsNotification', 'system_activity_log')) {
                        $this->db->where('ShowAsNotification', 1);
                    } else {
                        // Backward compatibility: filter out common internal admin actions
                        $this->db->where_not_in('Action', [
                            'Order Status Updated',
                            'Staff Assigned',
                            'Order Completed',
                            'Payment Status Updated'
                        ]);
                    }
                } catch (Exception $e) {
                    // If field check fails, use backward compatibility filter
                    log_message('debug', 'Could not check ShowAsNotification field: ' . $e->getMessage());
                    $this->db->where_not_in('Action', [
                        'Order Status Updated',
                        'Staff Assigned',
                        'Order Completed',
                        'Payment Status Updated'
                    ]);
                }
                
                $total_notifications = $this->db->count_all_results('system_activity_log');
                
                // Get paginated notifications
                $this->db->reset_query();
                
                // Apply same filters again for actual query
                try {
                    if ($this->db->table_exists('system_activity_log') && $this->db->field_exists('ShowAsNotification', 'system_activity_log')) {
                        $this->db->where('ShowAsNotification', 1);
                    } else {
                        $this->db->where_not_in('Action', [
                            'Order Status Updated',
                            'Staff Assigned',
                            'Order Completed',
                            'Payment Status Updated'
                        ]);
                    }
                } catch (Exception $e) {
                    log_message('debug', 'Could not check ShowAsNotification field: ' . $e->getMessage());
                    $this->db->where_not_in('Action', [
                        'Order Status Updated',
                        'Staff Assigned',
                        'Order Completed',
                        'Payment Status Updated'
                    ]);
                }
                
                $this->db->order_by('Timestamp', 'DESC');
                $this->db->limit($per_page, $offset);
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
        
        // Calculate pagination info
        $total_pages = ceil($total_notifications / $per_page);
        $pagination_data = [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_notifications,
            'per_page' => $per_page
        ];
        
        // Prepare data for view
        $data['notifications'] = $all_notifications;
        $data['pagination'] = $pagination_data;
        $data['title'] = "Glassify - Notifications";
        $data['active'] = 'notif';
        $data['content_view'] = 'admin_page/admin_notif';
        $data['page_css'] = 'sales_css/sales_notif.css';
        
        // Load view
        try {
            $this->load->view('admin_page/layout', $data);
        }
        catch (Exception $e) {
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
        // Set JSON header first to prevent HTML output
        header('Content-Type: application/json');
        
        try {
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
                
                // Filter out internal admin actions
                try {
                    if ($this->db->table_exists('system_activity_log') && $this->db->field_exists('ShowAsNotification', 'system_activity_log')) {
                        $this->db->where('ShowAsNotification', 1);
                    } else {
                        // Backward compatibility: filter out common internal admin actions
                        $this->db->where_not_in('Action', [
                            'Order Status Updated',
                            'Staff Assigned',
                            'Order Completed',
                            'Payment Status Updated'
                        ]);
                    }
                }
        catch (Exception $e) {
                    // If field check fails, use backward compatibility filter
                    log_message('debug', 'Could not check ShowAsNotification field: ' . $e->getMessage());
                    $this->db->where_not_in('Action', [
                        'Order Status Updated',
                        'Staff Assigned',
                        'Order Completed',
                        'Payment Status Updated'
                    ]);
                }
                
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
        catch (Exception $e) {
            // Ensure JSON is always returned, even on error
            log_message('error', 'Error in get_notification_count_ajax: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'count' => 0, 'message' => 'An error occurred']);
        }
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
        
        // UNIFIED PROCESS: All orders follow same flow
        // Orders should go to Ocular Pending after approval, then Fabrication
        // Flow: Approved -> Ocular Pending -> In Fabrication -> Ready for Installation -> Completed
        $current_status = $order->Status ?? 'Pending Review';
        $effective_status = $new_status;
        
        // Map Approved to Ocular Pending for ALL orders (unified process)
        if ($new_status === 'Approved') {
            $effective_status = 'Ocular Pending';
        }

        // Validate status transition
        $valid_transitions = $this->get_valid_status_transitions($current_status);
        
        // Loosen transitions to remove legacy sales-rep constraints
        if (in_array($current_status, ['Completed', 'Cancelled', 'Disapproved'], true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status transition']);
            return;
        }
        
        // Update order status
        $update_data = ['Status' => $effective_status];
        // Set Approved_Date when moving to Ocular Pending (after approval)
        if ($effective_status === 'Ocular Pending') {
            $admin_id = $this->session->userdata('user_id');
            if (!$order->Approved_Date) {
                $update_data['ApprovedBy_Admin_ID'] = $admin_id;
                $update_data['Approved_Date'] = date('Y-m-d H:i:s');
            }
        }
        
        // Log before update
        log_message('info', "Updating order {$order_id_clean} (OrderID: {$order->OrderID}) from '{$current_status}' to '{$effective_status}'");
        
        // Check if 'Ocular Pending' exists in the Status enum before trying to update
        // If it doesn't exist, we'll get a database error
        try {
            $this->db->where('OrderID', $order->OrderID);
            $result = $this->db->update('`order`', $update_data);
            
            // Check for database errors IMMEDIATELY after update
            $db_error = $this->db->error();
            if (!empty($db_error['code'])) {
                $error_msg = $db_error['message'];
                log_message('error', 'Database error updating order status: ' . $error_msg);
                
                // Check if it's an enum value error
                if (stripos($error_msg, 'enum') !== false || stripos($error_msg, 'ocular') !== false || stripos($error_msg, 'data truncated') !== false) {
                    log_message('error', "CRITICAL: Status value '{$effective_status}' is not in the database enum!");
                    echo json_encode([
                        'success' => false,
                        'message' => "Database Error: The status '{$effective_status}' is not allowed. Please run the database migration script: database/scripts/add_ocular_pending_status.sql"
                    ]);
                    return;
                }
                
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $error_msg]);
                return;
            }
            
            if (!$result) {
                log_message('error', 'Update returned false - no rows affected. OrderID: ' . $order->OrderID);
                echo json_encode(['success' => false, 'message' => 'Failed to update order status - no rows affected']);
                return;
            }
            
            log_message('info', "Order status update successful - {$result} row(s) affected");
        }
        catch (Exception $e) {
            log_message('error', 'Exception updating order status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error updating order status: ' . $e->getMessage()]);
            return;
        }
        
        // Log status change (internal admin action - not shown as notification)
        $this->log_activity(
            'Order Status Updated',
            "Order {$order_id_clean} status changed from {$current_status} to {$effective_status}",
            'Admin',
            $this->session->userdata('user_id'),
            $this->session->userdata('user_name'),
            $order->OrderID,
            'Order',
            false // Don't show as notification - internal admin action
        );
        
        // Send notifications to customer based on status change
        if ($order->Customer_ID && $current_status !== $effective_status) {
            $this->load->helper('notification');
            $order_number = $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT);
            $admin_id = $this->session->userdata('user_id');
            
            switch ($effective_status) {
                case 'Ocular Pending':
                    // Notify customer that booking is confirmed and ocular visit will be scheduled
                    try {
                        $this->db->reset_query();
                        $this->db->where('OrderID', $order->OrderID);
                        $this->db->where('Service', 'Ocular Visit');
                        $ocular_appointment = $this->db->get('appointments')->row();

                        $ocular_date = 'TBD - We will contact you to schedule';
                        if ($ocular_appointment && !empty($ocular_appointment->AppointmentDate)) {
                            $ocular_date = date('F j, Y', strtotime($ocular_appointment->AppointmentDate));
                        }

                        send_order_notification(
                            $order->Customer_ID,
                            $order->OrderID,
                            'Booking Confirmed',
                            "Your booking for order #{$order_number} is confirmed. We will schedule an ocular visit soon (estimated: {$ocular_date}).",
                            'fa-calendar-alt',
                            $admin_id
                        );
                    }
        catch (Exception $e) {
                        log_message('error', 'Error sending ocular pending notification: ' . $e->getMessage());
                    }
                    break;
                case 'In Fabrication':
                    send_order_notification(
                        $order->Customer_ID,
                        $order->OrderID,
                        'Order in Fabrication',
                        "Your order #{$order_number} is now being fabricated. We'll notify you once it's ready for installation.",
                        'fa-cog',
                        $admin_id
                    );
                    break;
                    
                case 'Ready for Installation':
                    send_order_notification(
                        $order->Customer_ID,
                        $order->OrderID,
                        'Order Ready for Installation',
                        "Your order #{$order_number} is ready for installation! We'll contact you soon to schedule the installation.",
                        'fa-check-circle',
                        $admin_id
                    );
                    break;
                    
                case 'Completed':
                    send_order_notification(
                        $order->Customer_ID,
                        $order->OrderID,
                        'Order Completed',
                        "Your order #{$order_number} has been completed and installed. Thank you for choosing Glassify!",
                        'fa-star',
                        $admin_id
                    );
                    break;
            }
        }
        
        // Save admin notes if provided
        if ($notes) {
            // Store notes in a notes table or order table if column exists
            if ($this->db->field_exists('AdminNotes', 'order')) {
                $this->db->where('OrderID', $order->OrderID)
                         ->update('`order`', ['AdminNotes' => $notes]);
            }
        }

        // If admin provided notes when cancelling, save them to DisapprovalReason and disapproved metadata
        if (strtolower($effective_status) === 'cancelled' && $notes) {
            $admin_id = $this->session->userdata('user_id');
            $update_cancel = [];
            if ($this->db->field_exists('DisapprovalReason', 'order')) {
                $update_cancel['DisapprovalReason'] = $notes;
            }
            if ($this->db->field_exists('DisapprovedBy', 'order')) {
                $update_cancel['DisapprovedBy'] = 'Admin';
            }
            if ($this->db->field_exists('DisapprovedBy_ID', 'order')) {
                $update_cancel['DisapprovedBy_ID'] = $admin_id;
            }
            if ($this->db->field_exists('Disapproved_Date', 'order')) {
                $update_cancel['Disapproved_Date'] = date('Y-m-d H:i:s');
            }
            if (!empty($update_cancel)) {
                $this->db->where('OrderID', $order->OrderID)->update('`order`', $update_cancel);
            }
        }
        
        // If moved to Ocular Pending stage, create ocular appointment if missing
        // IMPORTANT: Do NOT create fabrication appointment here - only after ocular is marked complete
        if ($effective_status === 'Ocular Pending' && $this->db->table_exists('appointments')) {
            $this->db->reset_query();
            $this->db->where('OrderID', $order->OrderID);
            if ($this->db->field_exists('Service', 'appointments')) {
                $this->db->where('Service', 'Ocular Visit');
            } elseif ($this->db->field_exists('AppointmentType', 'appointments')) {
                $this->db->where('AppointmentType', 'Ocular');
            }
            $existing = $this->db->get('appointments')->row();
            if (!$existing) {
                // Get client name
                $client_name = '';
                if (!empty($order->Customer_ID)) {
                    $this->db->reset_query();
                    $this->db->select('u.First_Name, u.Last_Name, u.Middle_Name');
                    $this->db->from('customer c');
                    $this->db->join('user u', 'u.UserID = c.UserID', 'left');
                    $this->db->where('c.Customer_ID', $order->Customer_ID);
                    $customer_user = $this->db->get()->row();
                    if ($customer_user) {
                        $client_name = trim(($customer_user->First_Name ?? '') . ' ' . ($customer_user->Middle_Name ?? '') . ' ' . ($customer_user->Last_Name ?? ''));
                    }
                }
                
                $appointment_data = ['OrderID' => $order->OrderID];
                if ($this->db->field_exists('Customer_ID', 'appointments')) {
                    $appointment_data['Customer_ID'] = $order->Customer_ID ?? null;
                }
                if ($this->db->field_exists('Service', 'appointments')) {
                    $appointment_data['Service'] = 'Ocular Visit';
                }
                if ($this->db->field_exists('Status', 'appointments')) {
                    $appointment_data['Status'] = 'In Progress';
                }
                if ($this->db->field_exists('AppointmentType', 'appointments')) {
                    $appointment_data['AppointmentType'] = 'Ocular';
                }
                if ($this->db->field_exists('ClientName', 'appointments')) {
                    $appointment_data['ClientName'] = $client_name ?: 'N/A';
                }
                if ($this->db->field_exists('ProductName', 'appointments')) {
                    $appointment_data['ProductName'] = $order->ProductName ?? null;
                }
                if ($this->db->field_exists('AppointmentDate', 'appointments')) {
                    $appointment_data['AppointmentDate'] = date('Y-m-d');
                }
                if ($this->db->field_exists('AppointmentTime', 'appointments')) {
                    $appointment_data['AppointmentTime'] = '10:00:00';
                }
                $this->db->reset_query();
                $this->db->insert('appointments', $appointment_data);
            }
        }
        
        // Do NOT create fabrication appointment here - it should only be created when ocular is marked complete

        // Verify the update actually happened
        $this->db->reset_query();
        $this->db->select('Status, OrderNumber');
        $updated_order = $this->db->where('OrderID', $order->OrderID)->get('`order`')->row();
        
        if (!$updated_order) {
            log_message('error', "Could not verify order update - order not found: OrderID {$order->OrderID}");
            echo json_encode([
                'success' => false,
                'message' => 'Order update may have failed - could not verify status change'
            ]);
            return;
        }
        
        $actual_new_status = $updated_order->Status ?? null;
        
        log_message('info', "Order {$order_id_clean} status update verified: {$current_status} -> {$effective_status} (actual in DB: " . ($actual_new_status ?? 'NULL/EMPTY') . ")");
        
        // Check if status actually changed
        if ($actual_new_status !== $effective_status) {
            log_message('error', "Status mismatch! Expected: {$effective_status}, Actual: " . ($actual_new_status ?? 'NULL/EMPTY'));
            
            // If status is NULL or empty, the enum value probably doesn't exist
            if (empty($actual_new_status) && $effective_status === 'Ocular Pending') {
                log_message('error', "CRITICAL: 'Ocular Pending' status is not in the database enum! Status field is empty.");
                echo json_encode([
                    'success' => false,
                    'message' => "Database Error: The status 'Ocular Pending' is not in the database enum. Please run: database/scripts/add_ocular_pending_status.sql",
                    'old_status' => $current_status ?: 'NULL',
                    'new_status' => $actual_new_status ?? 'NULL',
                    'expected_status' => $effective_status,
                    'order_id' => $order_id_clean
                ]);
                return;
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => $effective_status === 'Ocular Pending'
                ? 'Order approved and moved to ocular pending. Ocular visit appointment has been created.'
                : 'Order status updated successfully',
            'old_status' => $current_status ?: 'NULL',
            'new_status' => $actual_new_status ?? 'NULL',
            'expected_status' => $effective_status,
            'order_id' => $order_id_clean,
            'order_number' => $updated_order->OrderNumber ?? 'N/A'
        ]);
    }
    
    /**
     * Get valid status transitions for an order
     */
    private function get_valid_status_transitions($current_status)
    {
        $transitions = [
            'Pending Review' => ['Approved', 'Ocular Pending', 'Cancelled'],
            'Awaiting Admin' => ['Approved', 'Ocular Pending', 'Disapproved', 'Cancelled'],
            'Approved' => ['In Fabrication', 'Cancelled'],
            'Ocular Pending' => ['Approved', 'Cancelled'],
            'In Fabrication' => ['Ready for Installation', 'Cancelled'],
            'Ready for Installation' => ['Completed', 'Cancelled'],
            'Completed' => [], // Final state
            'Cancelled' => [], // Final state
            'Disapproved' => [] // Final state
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
        $staff_type = $this->input->post('staff_type'); // 'fabrication', 'installation', or 'ocular'
        $staff_id = $this->input->post('staff_id');
        $staff_name_post = $this->input->post('staff_name'); // optional external name from client
        
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
        if ($staff_type === 'ocular') {
            if (!$this->db->table_exists('appointments')) {
                echo json_encode(['success' => false, 'message' => 'Appointments table not found in database']);
                return;
            }

            // Resolve staff name
            $staff_name = 'Unassigned';
            // If a staff_name was provided (created via TomSelect), use it
            if (!empty($staff_name_post)) {
                $staff_name = $staff_name_post;
            } else if ($staff_id) {
                $staff = $this->db->where('UserID', $staff_id)->get('user')->row();
                if ($staff) {
                    $staff_name = trim(($staff->First_Name ?? '') . ' ' . ($staff->Last_Name ?? ''));
                }
            }

            // Find existing ocular appointment
            $this->db->reset_query();
            $this->db->where('OrderID', $order->OrderID);
            $this->db->group_start();
            $this->db->where('Service', 'Ocular Visit');
            if ($this->db->field_exists('AppointmentType', 'appointments')) {
                $this->db->or_where('AppointmentType', 'Ocular');
            }
            $this->db->group_end();
            $ocular_appointment = $this->db->get('appointments')->row();

            if ($ocular_appointment) {
                $update_data = [];
                if ($this->db->field_exists('AssignedStaff', 'appointments')) {
                    $update_data['AssignedStaff'] = $staff_id ? $staff_name : null;
                }
                if ($this->db->field_exists('AssignedStaff_ID', 'appointments')) {
                    $update_data['AssignedStaff_ID'] = $staff_id ?: null;
                }
                if (!empty($update_data)) {
                    $this->db->where('AppointmentID', $ocular_appointment->AppointmentID)
                             ->update('appointments', $update_data);
                }
            } else {
                // Create ocular appointment if missing
                $appointment_data = [
                    'OrderID' => $order->OrderID
                ];
                if ($this->db->field_exists('Customer_ID', 'appointments')) {
                    $appointment_data['Customer_ID'] = $order->Customer_ID ?? null;
                }
                if ($this->db->field_exists('Service', 'appointments')) {
                    $appointment_data['Service'] = 'Ocular Visit';
                }
                if ($this->db->field_exists('Status', 'appointments')) {
                    $appointment_data['Status'] = 'In Progress';
                }
                if ($this->db->field_exists('AppointmentType', 'appointments')) {
                    $appointment_data['AppointmentType'] = 'Ocular';
                }
                if ($this->db->field_exists('ClientName', 'appointments')) {
                    $client_name = '';
                    if (!empty($order->Customer_ID)) {
                        $this->db->reset_query();
                        $this->db->select('u.First_Name, u.Last_Name');
                        $this->db->from('customer c');
                        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
                        $this->db->where('c.Customer_ID', $order->Customer_ID);
                        $customer_user = $this->db->get()->row();
                        if ($customer_user) {
                            $client_name = trim(($customer_user->First_Name ?? '') . ' ' . ($customer_user->Last_Name ?? ''));
                        }
                    }
                    $appointment_data['ClientName'] = $client_name ?: 'N/A';
                }
                if ($this->db->field_exists('ProductName', 'appointments')) {
                    $appointment_data['ProductName'] = $order->ProductName ?? null;
                }
                if ($this->db->field_exists('AssignedStaff', 'appointments')) {
                    $appointment_data['AssignedStaff'] = $staff_id ? $staff_name : null;
                }
                if ($this->db->field_exists('AssignedStaff_ID', 'appointments')) {
                    $appointment_data['AssignedStaff_ID'] = $staff_id ?: null;
                }
                $this->db->insert('appointments', $appointment_data);
            }

            $this->log_activity(
                'Staff Assigned',
                "ocular staff assigned to order {$order_id_clean}: {$staff_name}",
                'Admin',
                $this->session->userdata('user_id'),
                $this->session->userdata('user_name'),
                $order->OrderID,
                'Order',
                false // Don't show as notification - internal admin action
            );

            echo json_encode(['success' => true, 'message' => 'Ocular staff assigned successfully']);
        } else {
            $field_name = $staff_type === 'fabrication' ? 'FabricationStaff_ID' : 'InstallationStaff_ID';
            
            if ($this->db->field_exists($field_name, 'order')) {
                // For fabrication/installation, external (free-text) names are not supported because
                // the database stores only *_Staff_ID as foreign keys. Reject external names.
                if (!empty($staff_name_post) && empty($staff_id)) {
                    echo json_encode(['success' => false, 'message' => 'External names are allowed only for Ocular assignments. Please create a user account before assigning for Fabrication/Installation.']);
                    return;
                }

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
                    'Order',
                    false // Don't show as notification - internal admin action
                );
                
                echo json_encode(['success' => true, 'message' => 'Staff assigned successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Staff assignment field not found in database']);
            }
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
        $format = $this->input->get('format') ?: 'pdf';

        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            return;
        }

        // Resolve order using existing model helper
        $this->load->model('Order_model');
        $order = $this->Order_model->get_order_details_for_popup($order_id);

        if (!$order) {
            // try fallback as in get_order_details_ajax
            $order = $this->Order_model->get_order($order_id);
        }

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }

        // For print/pdf - attempt server-side PDF generation when possible, otherwise fall back to printable page
        if (in_array(strtolower($format), ['pdf', 'print'])) {
            if (strtolower($format) === 'pdf' && class_exists('\Dompdf\\Dompdf')) {
                // Generate PDF using Dompdf
                try {
                    $data['order'] = $order;
                    $html = $this->load->view('admin_page/print_order', $data, true);

                    $dompdf = new \Dompdf\Dompdf();
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();

                    $output = $dompdf->output();

                    $exportsDir = FCPATH . 'writable/exports/';
                    if (!is_dir($exportsDir)) @mkdir($exportsDir, 0755, true);
                    $filename = 'order_export_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $order_id) . '_' . time() . '.pdf';
                    $filepath = $exportsDir . $filename;
                    file_put_contents($filepath, $output);

                    echo json_encode(['success' => true, 'file_path' => 'writable/exports/' . $filename]);
                    return;
                }
        catch (Exception $e) {
                    log_message('error', 'AdminCon::export_order - Dompdf PDF generation failed: ' . $e->getMessage());
                    // fallthrough to printable URL
                }
            }

            // Fallback: return printable HTML URL (browser can print to PDF)
            // Use site_url so this works when index_page is required (no URL rewrite)
            $download_url = site_url('AdminCon/print_order?order_id=' . urlencode($order_id));
            echo json_encode(['success' => true, 'download_url' => $download_url]);
            return;
        }

        // For excel - generate a CSV and return its path
        if (strtolower($format) === 'excel') {
            try {
                $exportsDir = FCPATH . 'writable/exports/';
                if (!is_dir($exportsDir)) @mkdir($exportsDir, 0755, true);

                $filename = 'order_export_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $order_id) . '_' . time() . '.csv';
                $filepath = $exportsDir . $filename;

                $fp = fopen($filepath, 'w');
                if (!$fp) throw new Exception('Unable to create export file');

                // Header
                fputcsv($fp, ['Order ID', 'Order Number', 'Client', 'Email', 'Phone', 'Product', 'Quantity', 'Unit Price', 'Subtotal', 'Total', 'Status', 'Order Date']);

                // Try to get items
                $items = [];
                if (method_exists($this->Order_model, 'get_order_customizations')) {
                    $items = $this->Order_model->get_order_customizations($order->OrderID ?? $order->order_id ?? 0);
                }

                if (!empty($items)) {
                    foreach ($items as $it) {
                        fputcsv($fp, [
                            $order->OrderID ?? '',
                            $order->OrderNumber ?? '',
                            $order->First_Name . ' ' . ($order->Last_Name ?? '') ?? '',
                            $order->Email ?? '',
                            $order->PhoneNum ?? '',
                            $it->ProductName ?? ($it->Product ?? ''),
                            $it->Quantity ?? ($it->Qty ?? 1),
                            $it->UnitPrice ?? ($it->Price ?? ''),
                            $it->Subtotal ?? '',
                            $order->TotalAmount ?? '',
                            $order->Status ?? '',
                            $order->OrderDate ?? ''
                        ]);
                    }
                } else {
                    // No items - write a single row
                    fputcsv($fp, [
                        $order->OrderID ?? '',
                        $order->OrderNumber ?? '',
                        trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? '')),
                        $order->Email ?? '',
                        $order->PhoneNum ?? '',
                        $order->ProductName ?? '',
                        $order->Quantity ?? '',
                        $order->UnitPrice ?? '',
                        $order->Subtotal ?? '',
                        $order->TotalAmount ?? '',
                        $order->Status ?? '',
                        $order->OrderDate ?? ''
                    ]);
                }

                fclose($fp);

                // Return relative file path for download
                $relPath = 'writable/exports/' . $filename;
                echo json_encode(['success' => true, 'file_path' => $relPath]);
                return;
            }
        catch (Exception $e) {
                log_message('error', 'AdminCon::export_order - Excel export failed: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Export failed']);
                return;
            }
        }

        // Default fallback
        echo json_encode(['success' => false, 'message' => 'Unsupported format']);
    }
    
        /**
         * Printable order view (used for Print/PDF fallback)
         */
        public function print_order()
        {
            $order_id = $this->input->get('order_id');
            if (!$order_id) {
                show_error('Order ID required', 400);
                return;
            }

            $this->load->model('Order_model');
            $order = $this->Order_model->get_order_details_for_popup($order_id);
            if (!$order) {
                // try numeric lookup
                $order = $this->Order_model->get_order($order_id);
            }

            if (!$order) {
                show_404();
                return;
            }

            $data['order'] = $order;
            $this->load->view('admin_page/print_order', $data);
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
            $order_number_exists = $this->db->field_exists('OrderNumber', 'order');
            $order_type_exists = $this->db->field_exists('OrderType', 'order');
            $order_total_exists = $this->db->field_exists('TotalAmount', 'order');
            $order_fabrication_start_exists = $this->db->field_exists('FabricationStartDate', 'order');
            $order_fabrication_end_exists = $this->db->field_exists('FabricationEndDate', 'order');
            $order_installation_exists = $this->db->field_exists('InstallationDate', 'order');
            $customer_table_exists = $this->db->table_exists('customer');
            $user_table_exists = $this->db->table_exists('user');
            $customer_user_join_exists = $customer_table_exists && $user_table_exists
                && $this->db->field_exists('Customer_ID', 'customer')
                && $this->db->field_exists('UserID', 'customer')
                && $this->db->field_exists('UserID', 'user')
                && $this->db->field_exists('First_Name', 'user')
                && $this->db->field_exists('Last_Name', 'user');

            $order_select_fields = ['o.OrderID', 'o.OrderDate', 'o.Status'];
            if ($order_number_exists) {
                $order_select_fields[] = 'o.OrderNumber';
            }
            if ($order_type_exists) {
                $order_select_fields[] = 'o.OrderType';
            }
            if ($order_total_exists) {
                $order_select_fields[] = 'o.TotalAmount';
            }
            if ($order_fabrication_start_exists) {
                $order_select_fields[] = 'o.FabricationStartDate';
            }
            if ($order_fabrication_end_exists) {
                $order_select_fields[] = 'o.FabricationEndDate';
            }
            if ($order_installation_exists) {
                $order_select_fields[] = 'o.InstallationDate';
            }
            if ($customer_user_join_exists) {
                $order_select_fields[] = 'u.First_Name';
                $order_select_fields[] = 'u.Last_Name';
            }

            $this->db->select(implode(', ', $order_select_fields));
            $this->db->from('`order` o');
            if ($customer_user_join_exists) {
                $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
                $this->db->join('user u', 'c.UserID = u.UserID', 'left');
            }
            
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
                }
        catch (Exception $e) {
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
            $appointment_select_fields = ['a.*'];
            if ($order_number_exists) {
                $appointment_select_fields[] = 'o.OrderNumber';
            }
            if ($order_type_exists) {
                $appointment_select_fields[] = 'o.OrderType';
            }
            if ($customer_user_join_exists) {
                $appointment_select_fields[] = 'u.First_Name';
                $appointment_select_fields[] = 'u.Last_Name';
            }

            $this->db->select(implode(', ', $appointment_select_fields));
            $this->db->from('appointments a');
            $this->db->join('`order` o', 'a.OrderID = o.OrderID', 'left');
            if ($customer_user_join_exists) {
                $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
                $this->db->join('user u', 'c.UserID = u.UserID', 'left');
            }
            
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
            
        }
        catch (Exception $e) {
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
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
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
        
        // IMPORTANT: Only show orders that have fabrication appointments or projectschedule entries
        // Do NOT show orders just because they have status "Approved"
        // Orders should only appear in fabrication queue AFTER ocular visit is marked complete
        
        // We'll filter orders in PHP after fetching to avoid complex SQL with conditional joins
        // First, get all orders that could potentially be in fabrication (approved or later)
        // IMPORTANT: Include "Ready for Installation" status to ensure ready orders are fetched
        $this->db->where_in('o.Status', ['Approved', 'In Fabrication', 'Ready for Installation', 'Completed', 'Installed']);
        
        // Also include orders with FabricationStatus='Ready' even if Status is different
        // This ensures ready orders are always fetched
        if ($this->db->field_exists('FabricationStatus', 'order')) {
            $this->db->or_where('o.FabricationStatus', 'Ready');
        }
        
        $this->db->group_by('o.OrderID');
        
        // Map status filter to actual statuses
        // Note: 'queued' status filter will be applied in PHP after fetching
        // Simple status filters that don't need JOINs can be applied here
        if ($status_filter && $status_filter !== 'all') {
            if ($status_filter === 'in-progress') {
                $this->db->where('o.Status', 'In Fabrication');
                if ($this->db->field_exists('FabricationStatus', 'order')) {
                    $this->db->where('(o.FabricationStatus IS NULL OR o.FabricationStatus = "In Progress")', null, false);
                }
            } elseif ($status_filter === 'quality-check') {
                // Quality check uses FabricationStatus field
                if ($this->db->field_exists('FabricationStatus', 'order')) {
                    $this->db->where('o.FabricationStatus', 'Quality Check');
                }
            } elseif ($status_filter === 'ready') {
                $this->db->group_start();
                $this->db->where('o.Status', 'Ready for Installation');
                if ($this->db->field_exists('FabricationStatus', 'order')) {
                    $this->db->or_where('o.FabricationStatus', 'Ready');
                }
                $this->db->group_end();
            } elseif ($status_filter === 'completed') {
                $this->db->where_in('o.Status', ['Completed', 'Installed']);
            } elseif ($status_filter !== 'queued') {
                // Apply other status filters, but skip 'queued' which will be done in PHP
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
        
        // Filter orders to only include those that have fabrication appointments or projectschedule entries
        // Or are already in fabrication status (for backward compatibility)
        $filtered_orders = [];
        $fabrication_order_ids = [];
        $schedule_order_ids = [];
        
        // Get order IDs that have fabrication appointments (including completed ones)
        // We need to include orders that have had fabrication appointments, even if they're now ready/completed
        if ($this->db->table_exists('appointments')) {
            $fab_apts = $this->db->select('OrderID')
                                  ->where('Service', 'In Fabrication')
                                  ->get('appointments')
                                  ->result();
            foreach ($fab_apts as $fab_apt) {
                $fabrication_order_ids[] = $fab_apt->OrderID;
            }
        }
        
        // Get order IDs that have projectschedule entries
        if ($this->db->table_exists('projectschedule')) {
            $schedules = $this->db->select('OrderID')
                                  ->get('projectschedule')
                                  ->result();
            foreach ($schedules as $schedule) {
                $schedule_order_ids[] = $schedule->OrderID;
            }
        }
        
        // Get fabrication appointment statuses for filtering
        $fabrication_statuses = [];
        $schedule_statuses = [];
        
        if ($this->db->table_exists('appointments') && !empty($fabrication_order_ids)) {
            $fab_statuses = $this->db->select('OrderID, Status')
                                      ->where('Service', 'In Fabrication')
                                      ->where_in('OrderID', $fabrication_order_ids)
                                      ->get('appointments')
                                      ->result();
            foreach ($fab_statuses as $fab_status) {
                $fabrication_statuses[$fab_status->OrderID] = $fab_status->Status;
            }
        }
        
        if ($this->db->table_exists('projectschedule') && !empty($schedule_order_ids)) {
            $sched_statuses = $this->db->select('OrderID, Status')
                                        ->where_in('OrderID', $schedule_order_ids)
                                        ->get('projectschedule')
                                        ->result();
            foreach ($sched_statuses as $sched_status) {
                $schedule_statuses[$sched_status->OrderID] = $sched_status->Status;
            }
        }
        
        // Filter orders
        foreach ($orders as $order) {
            $order_id = $order->OrderID;
            $has_fabrication = in_array($order_id, $fabrication_order_ids);
            $has_schedule = in_array($order_id, $schedule_order_ids);
            $in_fabrication_status = in_array($order->Status, ['In Fabrication', 'Ready for Installation', 'Completed', 'Installed']);
            // Also check if order has FabricationStatus set (even if no appointment exists)
            $has_fabrication_status = !empty($order->FabricationStatus);
            
            // Check if order is in "Ready" state
            $is_ready_state = ($order->Status === 'Ready for Installation') || 
                             (isset($order->FabricationStatus) && $order->FabricationStatus === 'Ready');
            
            // Include order if it has:
            // 1. Fabrication appointment (past or present)
            // 2. Schedule entry
            // 3. Is in fabrication-related status (In Fabrication, Ready for Installation, Completed, Installed)
            // 4. Has FabricationStatus set (indicates it's been in the fabrication queue)
            // 5. IMPORTANT: Orders in "Ready" state should ALWAYS be included (even without appointment/schedule)
            $should_include = $has_fabrication || $has_schedule || $in_fabrication_status || $has_fabrication_status || $is_ready_state;
            
            if ($should_include) {
                // Apply status filter if specified
                if ($status_filter && $status_filter !== 'all') {
                    $fab_status = $fabrication_statuses[$order_id] ?? null;
                    $sched_status = $schedule_statuses[$order_id] ?? null;
                    $order_fab_status = $order->FabricationStatus ?? null;
                    
                    if ($status_filter === 'queued') {
                        // Queued: Has fabrication appointment but status is still 'In Progress' or not started
                        $is_queued = false;
                        if ($fab_status === 'In Progress' || $fab_status === null) {
                            $is_queued = true;
                        } elseif ($sched_status === 'In progress' || $sched_status === 'Queued' || $sched_status === null) {
                            $is_queued = true;
                        } elseif ($order_fab_status === null || $order_fab_status === 'Queued') {
                            $is_queued = true;
                        }
                        if (!$is_queued) {
                            continue; // Skip this order
                        }
                    } elseif ($status_filter === 'ready') {
                        // Ready: Check if order is actually in ready status
                        $is_ready = false;
                        if ($order_fab_status === 'Ready') {
                            $is_ready = true;
                        } elseif ($order->Status === 'Ready for Installation') {
                            $is_ready = true;
                        } elseif ($fab_status === 'Ready' || $sched_status === 'Ready') {
                            $is_ready = true;
                        }
                        if (!$is_ready) {
                            continue; // Skip this order
                        }
                    }
                    // Other status filters were already applied in SQL
                }
                
                $filtered_orders[] = $order;
            }
        }
        
        $orders = $filtered_orders; // Use filtered orders
        
        $formatted_orders = [];
        foreach ($orders as $order) {
            $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
            $staff_name = trim(($order->Staff_First_Name ?? '') . ' ' . ($order->Staff_Last_Name ?? ''));
            
            // Determine queue status based on FabricationStatus if available, otherwise use Status
            $queue_status = $this->map_status_to_queue($order->Status, $order->FabricationStatus ?? null);
            
            // Debug: Log ready orders to help troubleshoot
            if ($queue_status === 'ready' || ($order->Status === 'Ready for Installation') || ($order->FabricationStatus ?? '') === 'Ready') {
                log_message('debug', 'Ready order found - OrderID: ' . $order->OrderID . ', Status: ' . $order->Status . ', FabricationStatus: ' . ($order->FabricationStatus ?? 'null') . ', queue_status: ' . $queue_status);
            }
            
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
            
            // Get payment data from payment table
            $payment = null;
            if ($this->db->table_exists('payment')) {
                $payment = $this->db->where('OrderID', $order->OrderID)->get('payment')->row();
                // Debug: Log payment data
                log_message('debug', 'OrderID: ' . $order->OrderID . ' - Payment data: ' . ($payment ? json_encode($payment) : 'NULL'));
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
                'total_amount' => $order->TotalAmount ?? 0,
                'total' => $order->TotalAmount ?? 0,
                'payment_data' => [
                    'downpayment_amount' => $payment && isset($payment->Amount) ? $payment->Amount : null,
                    'downpayment_method' => $payment ? ($payment->PaymentMethod ?? null) : null,
                    'downpayment_status' => $payment ? ($payment->Status ?? 'Pending') : 'Pending',
                    'fabrication_amount' => $this->db->field_exists('FabricationPaymentAmount', 'order') ? ($order->FabricationPaymentAmount ?? null) : null,
                    'fabrication_method' => $this->db->field_exists('FabricationPaymentMethod', 'order') ? ($order->FabricationPaymentMethod ?? null) : null,
                    'fabrication_status' => $this->db->field_exists('FabricationPaymentStatus', 'order') ? ($order->FabricationPaymentStatus ?? 'Pending') : 'Pending',
                    'fabrication_receipt_url' => $this->db->field_exists('FabricationReceiptPath', 'order') && !empty($order->FabricationReceiptPath) ? base_url($order->FabricationReceiptPath) : null
                ]
            ];
        }
        
        // Debug: Count ready orders
        $ready_count = 0;
        foreach ($formatted_orders as $order) {
            if (isset($order['queue_status']) && $order['queue_status'] === 'ready') {
                $ready_count++;
            }
        }
        log_message('debug', 'get_fabrication_queue - Total orders returned: ' . count($formatted_orders) . ', Ready orders: ' . $ready_count);
        
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
        $this->db->select('o.*, c.role as customer_role, u.First_Name, u.Last_Name, u.Email, u.PhoneNum,
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
        
        // Get order items with customization data
        $this->db->select('oi.*, p.ProductName, p.Product_ID');
        $this->db->from('order_items oi');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->where('oi.OrderID', $order_id);
        $order_items = $this->db->get()->result();
        
        // If no order_items, try to get from customization table
        $customization_data = null;
        if (empty($order_items) && $this->db->table_exists('customization')) {
            // Check if order has CustomizationID
            if ($this->db->field_exists('CustomizationID', 'order') && !empty($order->CustomizationID)) {
                $customization_data = $this->db->where('CustomizationID', $order->CustomizationID)->get('customization')->row();
            } else {
                // Fallback to Customer_ID
                $this->db->where('Customer_ID', $order->Customer_ID);
                $this->db->order_by('CustomizationID', 'DESC');
                $customization_data = $this->db->get('customization')->row();
            }
        }
        
        $customer_name = trim(($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? ''));
        $staff_name = trim(($order->Staff_First_Name ?? '') . ' ' . ($order->Staff_Last_Name ?? ''));
        
        // Get payment data from payment table
        $payment = null;
        if ($this->db->table_exists('payment')) {
            $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
            // Debug: Log payment data
            log_message('debug', 'get_production_order_details - OrderID: ' . $order_id . ' - Payment data: ' . ($payment ? json_encode($payment) : 'NULL'));
        }
        
        // Calculate total from order items if TotalAmount in order table is empty/null
        $total_amount = floatval($order->TotalAmount ?? 0);
        if ($total_amount <= 0 && !empty($order_items)) {
            $subtotal = 0;
            $total_items = 0;
            foreach ($order_items as $item) {
                $item_price = floatval($item->EstimatePrice ?? $item->UnitPrice ?? 0);
                $item_qty = intval($item->Quantity ?? 1);
                $subtotal += $item_price * $item_qty;
                $total_items += $item_qty;
            }
            // Add shipping (₱25/item) and handling (₱10/item) fees
            $shipping = $total_items * 25;
            $handling = $total_items * 10;
            $total_amount = $subtotal + $shipping + $handling;
        }
        
        // Calculate payment amounts based on 50-40-10 split
        $downpayment_amount = round($total_amount * 0.5, 2);
        $fabrication_amount = round($total_amount * 0.4, 2);
        $installation_amount = round($total_amount * 0.1, 2);
        
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
                    'phone' => $order->PhoneNum ?? '',
                    'role' => $order->customer_role ?? null
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
                'total_amount' => $total_amount,
                'total' => $total_amount,
                'role_at_order' => $order->CustomerRoleAtOrder ?? null,
                'payment_data' => [
                    'downpayment_amount' => $payment && isset($payment->Amount) && floatval($payment->Amount) > 0 ? floatval($payment->Amount) : $downpayment_amount,
                    'downpayment_method' => $payment ? ($payment->PaymentMethod ?? null) : null,
                    'downpayment_status' => $payment ? ($payment->Status ?? 'Pending') : 'Pending',
                    'fabrication_amount' => $fabrication_amount,
                    'fabrication_method' => $this->db->field_exists('FabricationPaymentMethod', 'order') ? ($order->FabricationPaymentMethod ?? null) : null,
                    'fabrication_status' => $this->db->field_exists('FabricationPaymentStatus', 'order') ? ($order->FabricationPaymentStatus ?? 'Pending') : 'Pending',
                    'fabrication_receipt_url' => $this->db->field_exists('FabricationReceiptPath', 'order') && !empty($order->FabricationReceiptPath) ? base_url($order->FabricationReceiptPath) : null,
                    'installation_amount' => $installation_amount
                ]
            ]
        ];
        
        foreach ($order_items as $item) {
            // Base item fields
            $dimensions = $item->Dimensions ?? null;
            $glass_shape = $item->GlassShape ?? null;
            $glass_type = $item->GlassType ?? null;
            $glass_thickness = $item->GlassThickness ?? null;
            $edge_work = $item->EdgeWork ?? null;
            $frame_type = $item->FrameType ?? null;
            $engraving = $item->Engraving ?? null;

            // If some specs are missing on the order_items row, try to fill from customization_data
            if ($customization_data) {
                if (empty($dimensions) && isset($customization_data->Dimensions)) {
                    $dimensions = $customization_data->Dimensions;
                }
                if (empty($glass_shape) && isset($customization_data->GlassShape)) {
                    $glass_shape = $customization_data->GlassShape;
                }
                if (empty($glass_type) && isset($customization_data->GlassType)) {
                    $glass_type = $customization_data->GlassType;
                }
                if (empty($glass_thickness) && isset($customization_data->GlassThickness)) {
                    $glass_thickness = $customization_data->GlassThickness;
                }
                if (empty($edge_work) && isset($customization_data->EdgeWork)) {
                    $edge_work = $customization_data->EdgeWork;
                }
                if (empty($frame_type) && isset($customization_data->FrameType)) {
                    $frame_type = $customization_data->FrameType;
                }
                if ((empty($engraving) || strtolower($engraving) === 'none') && isset($customization_data->Engraving)) {
                    $engraving = $customization_data->Engraving;
                }
            }

            $result['order']['items'][] = [
                'product_id' => $item->Product_ID,
                'product_name' => $item->ProductName ?? 'N/A',
                'quantity' => $item->Quantity ?? 1,
                'unit_price' => $item->UnitPrice ?? $item->EstimatePrice ?? 0,
                'subtotal' => ($item->Quantity ?? 1) * ($item->UnitPrice ?? $item->EstimatePrice ?? 0),
                'dimensions' => $dimensions,
                'glass_shape' => $glass_shape,
                'glass_type' => $glass_type,
                'glass_thickness' => $glass_thickness,
                'edge_work' => $edge_work,
                'frame_type' => $frame_type,
                'engraving' => $engraving,
                'customization' => isset($item->Customization) ? $item->Customization : (isset($item->customization) ? $item->customization : null)
            ];
        }
        
        // If no items from order_items, create item from customization data
        if (empty($result['order']['items']) && $customization_data) {
            // Get product name from customization
            $product_name = 'Custom Glass Product';
            if (!empty($customization_data->Product_ID) && $this->db->table_exists('product')) {
                $product = $this->db->where('Product_ID', $customization_data->Product_ID)->get('product')->row();
                if ($product) {
                    $product_name = $product->ProductName;
                }
            }
            
            $result['order']['items'][] = [
                'product_id' => $customization_data->Product_ID ?? null,
                'product_name' => $product_name,
                'quantity' => 1,
                'unit_price' => $customization_data->EstimatePrice ?? 0,
                'subtotal' => $customization_data->EstimatePrice ?? 0,
                'dimensions' => $customization_data->Dimensions ?? null,
                'glass_shape' => $customization_data->GlassShape ?? null,
                'glass_type' => $customization_data->GlassType ?? null,
                'glass_thickness' => $customization_data->GlassThickness ?? null,
                'edge_work' => $customization_data->EdgeWork ?? null,
                'frame_type' => $customization_data->FrameType ?? null,
                'engraving' => $customization_data->Engraving ?? null,
                'customization' => $customization_data
            ];
        }
        
        echo json_encode($result);
    }
    
    /**
     * Update fabrication progress
     */
    public function update_fabrication_progress()
    {
        // Set JSON header first to prevent HTML error pages
        header('Content-Type: application/json');
        
        // Enable error reporting for debugging (remove in production)
        error_reporting(E_ALL);
        ini_set('display_errors', 0); // Don't display errors, but log them
        
        try {
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
        $actual_end_date = $this->input->post('actual_end_date');
        $staff_id = $this->input->post('staff_id');
        $notes = $this->input->post('notes');
        $quality_check_notes = $this->input->post('quality_check_notes');
        $issues = $this->input->post('issues');
        
        // Get fabrication payment data
        $fab_payment_amount = $this->input->post('fabrication_payment_amount');
        $fab_payment_method = $this->input->post('fabrication_payment_method');
        $fab_payment_status = $this->input->post('fabrication_payment_status');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        $update_data = [];
        
        // Only update fields that exist in the database
        if ($progress !== null && $this->db->field_exists('FabricationProgress', 'order')) {
            $update_data['FabricationProgress'] = (int)$progress;
        }
        
        if ($status && $this->db->field_exists('Status', 'order')) {
            $update_data['Status'] = $status;
        }
        
        if ($fabrication_status !== null && $this->db->field_exists('FabricationStatus', 'order')) {
            // Server-side validation: prevent regressive changes from 'Ready' back to earlier stages
            $existing_order = $this->db->get_where('`order`', ['OrderID' => $order_id])->row();
            $existing_fab_status = $existing_order->FabricationStatus ?? null;
            $regressive = [
                'Queued' => ['In Progress', 'Quality Check', 'Ready', 'Completed'],
                'In Progress' => ['Queued'],
            ];
            // If existing status is 'Ready', reject updates setting it to earlier statuses
            $earlierStatuses = ['Queued', 'In Progress', 'Quality Check'];
            if ($existing_fab_status === 'Ready' && in_array($fabrication_status, $earlierStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Cannot move order back from Ready status.']);
                return;
            }

            $update_data['FabricationStatus'] = $fabrication_status;
            // Automatically set progress based on fabrication status
            $status_progress_map = [
                'Queued' => 0,
                'In Progress' => 25,
                'Quality Check' => 50,
                'Ready' => 75,
                'Completed' => 100
            ];
            if (isset($status_progress_map[$fabrication_status]) && $this->db->field_exists('FabricationProgress', 'order')) {
                $update_data['FabricationProgress'] = $status_progress_map[$fabrication_status];
            }
            
            // If fabrication status is "Completed", validate payment and create installation appointment
            // AND send notification to customer with installation date
            if ($fabrication_status === 'Completed') {
                // VALIDATION: Cannot complete unless fabrication payment is PAID
                if ($fab_payment_status !== 'Paid') {
                    // Also check the existing status in the database
                    $existing_order = $this->db->get_where('`order`', ['OrderID' => $order_id])->row();
                    $db_payment_status = $existing_order->FabricationPaymentStatus ?? null;
                    
                    if ($db_payment_status !== 'Paid') {
                        echo json_encode(['success' => false, 'message' => 'Cannot mark as Completed: Fabrication Payment must be PAID first.']);
                        return;
                    }
                }
                
                // Update order status to Ready for Installation
                if ($this->db->field_exists('Status', 'order')) {
                    $update_data['Status'] = 'Ready for Installation';
                }
                if ($this->db->field_exists('ActualFabricationEndDate', 'order')) {
                    $update_data['ActualFabricationEndDate'] = date('Y-m-d');
                }
                
                // Create installation appointment
                $this->create_installation_appointment($order_id);
                
                // Send notification to customer with installation date and date change option
                $this->db->reset_query();
                $order = $this->db->where('OrderID', $order_id)->get('`order`')->row();
                
                if ($order && $order->Customer_ID) {
                    try {
                        $this->load->helper('notification');
                        $order_number = $order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
                        $admin_id = $this->session->userdata('user_id');
                        
                        // Get installation appointment date
                        $this->db->reset_query();
                        $this->db->where('OrderID', $order_id);
                        $this->db->where('Service', 'Installed');
                        $installation_appointment = $this->db->get('appointments')->row();
                        
                        $installation_date = 'TBD';
                        $installation_time = '';
                        $installation_date_raw = null;
                        if ($installation_appointment) {
                            if (!empty($installation_appointment->AppointmentDate)) {
                                $installation_date_raw = $installation_appointment->AppointmentDate;
                                $installation_date = date('F j, Y', strtotime($installation_appointment->AppointmentDate));
                            }
                            if (!empty($installation_appointment->AppointmentTime)) {
                                $installation_time = date('g:i A', strtotime($installation_appointment->AppointmentTime));
                            }
                        }
                        
                        $date_time_text = $installation_date;
                        if ($installation_time) {
                            $date_time_text .= ' at ' . $installation_time;
                        }
                        
                        // Prepare action data for date change request
                        $action_data = [
                            'type' => 'installation_date_change',
                            'order_id' => $order_id,
                            'current_date' => $installation_date_raw,
                            'allowed_until' => date('Y-m-d', strtotime('+7 days'))
                        ];
                        
                        // Use send_customer_notification directly to include action data
                        if (function_exists('send_customer_notification')) {
                            send_customer_notification(
                                $order->Customer_ID,
                                'Installation Scheduled',
                                "Your order #{$order_number} fabrication is complete! Installation is scheduled for {$date_time_text}. You can request to change the date within the next 7 days if needed.",
                                'Delivery',
                                'fa-calendar-check',
                                $order_id,
                                'Order',
                                $admin_id,
                                json_encode($action_data)
                            );
                        }
                    }
        catch (Exception $e) {
                        log_message('error', 'Failed to send notification when fabrication completed: ' . $e->getMessage());
                    }
                }
            }
            
            // When moving to "Ready", send notification and email about 40% fabrication payment
            if ($fabrication_status === 'Ready') {
                $this->db->reset_query();
                $order = $this->db->where('OrderID', $order_id)->get('`order`')->row();
                
                if ($order && $order->Customer_ID) {
                    try {
                        $this->load->helper('notification');
                        $order_number = $order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
                        $admin_id = $this->session->userdata('user_id');
                        
                        // Calculate 40% payment amount (based on total)
                        $total_amount = $order->TotalAmount ?? 0;
                        $fabrication_payment = $total_amount * 0.4;
                        $formatted_amount = number_format($fabrication_payment, 2);
                        
                        // Send notification to customer about fabrication payment
                        if (function_exists('send_customer_notification')) {
                            send_customer_notification(
                                $order->Customer_ID,
                                'Fabrication Payment Required',
                                "Great news! Your order #{$order_number} is ready for final quality check and delivery. Please pay the remaining fabrication payment of ₱{$formatted_amount} (40% of total) to proceed with installation scheduling.",
                                'Payment',
                                'fa-money-bill-wave',
                                $order_id,
                                'Order',
                                $admin_id
                            );
                        }
                        
                        // Send email to customer about fabrication payment
                        $this->send_fabrication_payment_email($order, $fabrication_payment);
                        
                    }
        catch (Exception $e) {
                        log_message('error', 'Failed to send fabrication payment notification: ' . $e->getMessage());
                    }
                }
            }
            
            // When moving to In Progress, auto-set FabricationStartDate and FabricationEndDate
            if ($fabrication_status === 'In Progress') {
                $this->db->reset_query();
                $order_row = $this->db->get_where('`order`', ['OrderID' => $order_id])->row();

                // Only set FabricationStartDate if database field exists and there's no start date yet
                if ($this->db->field_exists('FabricationStartDate', 'order')) {
                    $existing_start = $order_row->FabricationStartDate ?? null;
                    if (empty($existing_start)) {
                        $update_data['FabricationStartDate'] = date('Y-m-d');
                    }
                }

                // Compute default expected end based on order type (Direct => +5 days)
                if ($this->db->field_exists('FabricationEndDate', 'order')) {
                    $existing_end = $order_row->FabricationEndDate ?? null;
                    if (empty($existing_end)) {
                        $days = 5; // default for Direct orders
                        $order_type = $order_row->OrderType ?? '';
                        if (!empty($order_type) && strtolower($order_type) === 'site-assessed') {
                            $days = 15; // site-assessed default (adjustable) — typically 15–30 days
                        }
                        $update_data['FabricationEndDate'] = date('Y-m-d', strtotime("+{$days} days"));
                    }
                }
            }
        }
        
        if ($start_date && $this->db->field_exists('FabricationStartDate', 'order')) {
            $update_data['FabricationStartDate'] = $start_date;
            // Set actual start date if not already set
            if ($this->db->field_exists('ActualFabricationStartDate', 'order')) {
                $order_check = $this->db->get_where('`order`', ['OrderID' => $order_id])->row();
                if ($order_check && empty($order_check->ActualFabricationStartDate)) {
                    $update_data['ActualFabricationStartDate'] = date('Y-m-d');
                }
            }
        }
        
        if ($end_date && $this->db->field_exists('FabricationEndDate', 'order')) {
            $update_data['FabricationEndDate'] = $end_date;
        }
        
        if ($actual_end_date && $this->db->field_exists('ActualFabricationEndDate', 'order')) {
            $update_data['ActualFabricationEndDate'] = $actual_end_date;
        }
        
        if ($staff_id !== null && $this->db->field_exists('FabricationStaff_ID', 'order')) {
            if ($staff_id !== '') {
                $update_data['FabricationStaff_ID'] = $staff_id ?: null;
            } else {
                // Explicitly unassign staff if empty string is sent
                $update_data['FabricationStaff_ID'] = null;
            }
        }
        
        // Handle notes - allow empty strings to clear the field
        if ($notes !== null && $this->db->field_exists('FabricationNotes', 'order')) {
            $update_data['FabricationNotes'] = $notes ?: null;
        }
        
        if ($quality_check_notes !== null && $this->db->field_exists('QualityCheckNotes', 'order')) {
            $update_data['QualityCheckNotes'] = $quality_check_notes ?: null;
        }
        
        // Check if FabricationIssues field exists before trying to update it
        if ($issues !== null && $this->db->field_exists('FabricationIssues', 'order')) {
            $update_data['FabricationIssues'] = $issues ?: null;
        } elseif ($issues !== null && $this->db->field_exists('FabricationNotes', 'order')) {
            // If field doesn't exist, store in FabricationNotes instead
            $existing_notes = isset($update_data['FabricationNotes']) ? $update_data['FabricationNotes'] : '';
            $update_data['FabricationNotes'] = ($existing_notes ? $existing_notes . "\n\n" : '') . 'Issues: ' . $issues;
        }
        
        // Handle fabrication payment receipt upload
        if (isset($_FILES['fabrication_payment_receipt']) && !empty($_FILES['fabrication_payment_receipt']['name'])) {
            $upload_path = './uploads/payments/fabrication/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            $config = [
                'upload_path' => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|pdf',
                'max_size' => 5120
            ];
            $this->load->library('upload', $config);
            if ($this->upload->do_upload('fabrication_payment_receipt')) {
                $upload_data = $this->upload->data();
                $receipt_path = 'uploads/payments/fabrication/' . $upload_data['file_name'];
                
                // Store receipt path in order table if field exists
                if ($this->db->field_exists('FabricationReceiptPath', 'order')) {
                    $update_data['FabricationReceiptPath'] = $receipt_path;
                }
            }
        }
        
        // Update fabrication payment data
        if ($fab_payment_amount !== null && $this->db->field_exists('FabricationPaymentAmount', 'order')) {
            $update_data['FabricationPaymentAmount'] = (float)$fab_payment_amount;
        }
        if ($fab_payment_method !== null && $this->db->field_exists('FabricationPaymentMethod', 'order')) {
            $update_data['FabricationPaymentMethod'] = $fab_payment_method;
        }
        if ($fab_payment_status !== null && $this->db->field_exists('FabricationPaymentStatus', 'order')) {
            $update_data['FabricationPaymentStatus'] = $fab_payment_status;
        }
        
        // Check if there's anything to update
        if (empty($update_data)) {
            echo json_encode(['success' => false, 'message' => 'No data to update']);
            return;
        }
        
        // Add updated timestamp
        if ($this->db->field_exists('Updated_Date', 'order')) {
            $update_data['Updated_Date'] = date('Y-m-d H:i:s');
        }
        
            // Verify order exists before updating
            $order_exists = $this->db->get_where('`order`', ['OrderID' => $order_id])->row();
            if (!$order_exists) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                return;
            }
            
            $this->db->where('OrderID', $order_id);
            $result = $this->db->update('`order`', $update_data);
            
            if ($result) {
                // ALSO update fabrication payment in the appointments table
                // This allows installation appointments to find the payment data
                if ($fab_payment_amount !== null || $fab_payment_method !== null || $fab_payment_status !== null) {
                    $this->db->reset_query();
                    $this->db->where('OrderID', $order_id);
                    $this->db->where('Service', 'In Fabrication');
                    $fab_appointment = $this->db->get('appointments')->row();
                    
                    if ($fab_appointment) {
                        $appointment_update = [];
                        if ($fab_payment_amount !== null && $this->db->field_exists('FabricationPaymentAmount', 'appointments')) {
                            $appointment_update['FabricationPaymentAmount'] = (float)$fab_payment_amount;
                        }
                        if ($fab_payment_method !== null && $this->db->field_exists('FabricationPaymentMethod', 'appointments')) {
                            $appointment_update['FabricationPaymentMethod'] = $fab_payment_method;
                        }
                        if ($fab_payment_status !== null && $this->db->field_exists('FabricationPaymentStatus', 'appointments')) {
                            $appointment_update['FabricationPaymentStatus'] = $fab_payment_status;
                        }
                        // Also save receipt path if uploaded
                        if (isset($receipt_path) && $this->db->field_exists('FabricationReceiptPath', 'appointments')) {
                            $appointment_update['FabricationReceiptPath'] = $receipt_path;
                        }
                        
                        if (!empty($appointment_update)) {
                            $this->db->reset_query();
                            $this->db->where('AppointmentID', $fab_appointment->AppointmentID);
                            $this->db->update('appointments', $appointment_update);
                            log_message('debug', 'Updated fabrication appointment payment: ' . json_encode($appointment_update));
                        }
                    }
                }
                
                // Check if any rows were actually affected
                if ($this->db->affected_rows() >= 0) {
                    echo json_encode(['success' => true, 'message' => 'Progress updated successfully']);
                } else {
                    // Even if no rows were affected, the update might have succeeded with same values
                    echo json_encode(['success' => true, 'message' => 'Update completed (no changes detected)']);
                }
            } else {
                // Get the last database error
                $error = $this->db->error();
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to update progress: ' . ($error['message'] ?? 'Database error')
                ]);
            }
        }
        catch (Exception $e) {
            // Catch any PHP errors and return JSON
            log_message('error', 'update_fabrication_progress error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Send fabrication payment email to customer
     * Called when fabrication status changes to "Ready"
     */
    private function send_fabrication_payment_email($order, $payment_amount)
    {
        try {
            // Get customer email
            $this->db->reset_query();
            $this->db->select('u.Email, u.First_Name, u.Last_Name');
            $this->db->from('customer c');
            $this->db->join('user u', 'u.UserID = c.UserID', 'left');
            $this->db->where('c.Customer_ID', $order->Customer_ID);
            $customer = $this->db->get()->row();
            
            if (!$customer || empty($customer->Email)) {
                log_message('error', 'send_fabrication_payment_email - Customer email not found for Customer_ID: ' . $order->Customer_ID);
                return false;
            }
            
            $order_number = $order->OrderNumber ?? 'GI' . str_pad($order->OrderID, 3, '0', STR_PAD_LEFT);
            $customer_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Last_Name ?? '')) ?: 'Valued Customer';
            $formatted_amount = number_format($payment_amount, 2);
            $total_amount = number_format($order->TotalAmount ?? 0, 2);
            
            $this->load->library('email');
            $this->email->set_mailtype('html');
            
            $from_email = config_item('smtp_user') ?: 'no-reply@glassify.com';
            $from_name = config_item('site_name') ?: 'Glassify';
            
            $this->email->from($from_email, $from_name);
            $this->email->to($customer->Email);
            $this->email->subject("Fabrication Payment Required - Order #{$order_number}");
            
            $body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <div style="background: linear-gradient(135deg, #02455F 0%, #0a5a7a 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Fabrication Payment Required</h1>
                    </div>
                    
                    <div style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none;">
                        <p style="font-size: 16px; color: #333;">Dear ' . htmlspecialchars($customer_name) . ',</p>
                        
                        <p style="font-size: 15px; color: #555; line-height: 1.6;">
                            Great news! Your custom glass order <strong>#' . htmlspecialchars($order_number) . '</strong> has completed fabrication 
                            and is ready for quality check and delivery preparation.
                        </p>
                        
                        <div style="background: #f8f9fa; border-left: 4px solid #02455F; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;">
                            <h3 style="margin: 0 0 15px 0; color: #02455F;">Payment Details</h3>
                            <table style="width: 100%; font-size: 14px; color: #555;">
                                <tr>
                                    <td style="padding: 8px 0;">Order Total:</td>
                                    <td style="text-align: right; font-weight: 600;">₱' . $total_amount . '</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;">Downpayment (50%) - Paid:</td>
                                    <td style="text-align: right; color: #28a745;">✓ Completed</td>
                                </tr>
                                <tr style="border-top: 1px solid #dee2e6;">
                                    <td style="padding: 12px 0; font-weight: 600; color: #02455F;">Fabrication Payment (40%) Due:</td>
                                    <td style="text-align: right; font-weight: 700; font-size: 18px; color: #dc3545;">₱' . $formatted_amount . '</td>
                                </tr>
                            </table>
                        </div>
                        
                        <p style="font-size: 15px; color: #555; line-height: 1.6;">
                            To proceed with installation scheduling, please complete the fabrication payment. 
                            You can pay via any of the following methods:
                        </p>
                        
                        <ul style="color: #555; line-height: 1.8;">
                            <li>E-Wallet (GCash, Maya, etc.)</li>
                            <li>Bank Transfer</li>
                            <li>Cash Payment at our office</li>
                            <li>Check Payment</li>
                        </ul>
                        
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="' . base_url('customer/orders') . '" style="display: inline-block; background: #02455F; color: #ffffff; text-decoration: none; padding: 14px 35px; border-radius: 6px; font-weight: 600; font-size: 15px;">View Order Details</a>
                        </div>
                        
                        <p style="font-size: 14px; color: #888; line-height: 1.6;">
                            If you have any questions about the payment or your order, please don\'t hesitate to contact us.
                        </p>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 0 0 10px 10px; text-align: center; border: 1px solid #e0e0e0; border-top: none;">
                        <p style="margin: 0; color: #666; font-size: 13px;">Thank you for choosing Glassify!</p>
                        <p style="margin: 10px 0 0 0; color: #888; font-size: 12px;">This is an automated message, please do not reply directly to this email.</p>
                    </div>
                </div>
            ';
            
            $this->email->message($body);
            
            if ($this->email->send()) {
                log_message('info', "Fabrication payment email sent to {$customer->Email} for Order #{$order_number}");
                return true;
            } else {
                log_message('error', 'Failed to send fabrication payment email: ' . $this->email->print_debugger(['headers']));
                return false;
            }
            
        }
        catch (Exception $e) {
            log_message('error', 'send_fabrication_payment_email exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email notification when installation is complete and final payment is due
     */
    private function send_installation_payment_email($order_id)
    {
        try {
            // Get order details
            $this->db->reset_query();
            $order = $this->db->get_where('`order`', ['OrderID' => $order_id])->row();
            
            if (!$order) {
                log_message('error', 'send_installation_payment_email - Order not found: ' . $order_id);
                return false;
            }
            
            // Get customer email
            $this->db->reset_query();
            $this->db->select('u.Email, u.First_Name, u.Last_Name');
            $this->db->from('customer c');
            $this->db->join('user u', 'u.UserID = c.UserID', 'left');
            $this->db->where('c.Customer_ID', $order->Customer_ID);
            $customer = $this->db->get()->row();
            
            if (!$customer || empty($customer->Email)) {
                log_message('error', 'send_installation_payment_email - Customer email not found for Customer_ID: ' . $order->Customer_ID);
                return false;
            }
            
            $order_number = $order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
            $customer_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Last_Name ?? '')) ?: 'Valued Customer';
            $total_amount = floatval($order->TotalAmount ?? 0);
            $final_payment = number_format($total_amount * 0.1, 2);
            $formatted_total = number_format($total_amount, 2);
            $due_date = date('F j, Y', strtotime('+5 days'));
            
            $this->load->library('email');
            $this->email->set_mailtype('html');
            
            $from_email = config_item('smtp_user') ?: 'no-reply@glassify.com';
            $from_name = config_item('site_name') ?: 'Glassify';
            
            $this->email->from($from_email, $from_name);
            $this->email->to($customer->Email);
            $this->email->subject("Installation Complete - Final Payment Due - Order #{$order_number}");
            
            $body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 24px;">✓ Installation Complete!</h1>
                    </div>
                    
                    <div style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none;">
                        <p style="font-size: 16px; color: #333;">Dear ' . htmlspecialchars($customer_name) . ',</p>
                        
                        <p style="font-size: 15px; color: #555; line-height: 1.6;">
                            Great news! Your custom glass order <strong>#' . htmlspecialchars($order_number) . '</strong> has been 
                            successfully installed. We hope you are satisfied with our work!
                        </p>
                        
                        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;">
                            <h3 style="margin: 0 0 10px 0; color: #856404;">⚠️ Final Payment Required</h3>
                            <p style="margin: 0; color: #856404; font-size: 14px;">
                                Please submit your final payment within <strong>5 days</strong> (by ' . $due_date . ') to complete your order.
                            </p>
                        </div>
                        
                        <div style="background: #f8f9fa; border-left: 4px solid #02455F; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;">
                            <h3 style="margin: 0 0 15px 0; color: #02455F;">Payment Summary</h3>
                            <table style="width: 100%; font-size: 14px; color: #555;">
                                <tr>
                                    <td style="padding: 8px 0;">Order Total:</td>
                                    <td style="text-align: right; font-weight: 600;">₱' . $formatted_total . '</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;">Downpayment (50%) - Paid:</td>
                                    <td style="text-align: right; color: #28a745;">✓ Completed</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;">Fabrication Payment (40%) - Paid:</td>
                                    <td style="text-align: right; color: #28a745;">✓ Completed</td>
                                </tr>
                                <tr style="border-top: 1px solid #dee2e6;">
                                    <td style="padding: 12px 0; font-weight: 600; color: #dc3545;">Final Payment (10%) Due:</td>
                                    <td style="text-align: right; font-weight: 700; font-size: 18px; color: #dc3545;">₱' . $final_payment . '</td>
                                </tr>
                            </table>
                        </div>
                        
                        <p style="font-size: 15px; color: #555; line-height: 1.6;">
                            Please complete your final payment via any of the following methods:
                        </p>
                        
                        <ul style="color: #555; line-height: 1.8;">
                            <li>E-Wallet (GCash, Maya, etc.)</li>
                            <li>Bank Transfer</li>
                            <li>Cash Payment at our office</li>
                            <li>Check Payment</li>
                        </ul>
                        
                        <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin: 20px 0;">
                            <p style="margin: 0; color: #721c24; font-size: 14px;">
                                <strong>Important:</strong> Failure to submit payment within 5 days may result in the removal of the installed product.
                            </p>
                        </div>
                        
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="' . base_url('customer/orders') . '" style="display: inline-block; background: #02455F; color: #ffffff; text-decoration: none; padding: 14px 35px; border-radius: 6px; font-weight: 600; font-size: 15px;">View Order & Pay</a>
                        </div>
                        
                        <p style="font-size: 14px; color: #888; line-height: 1.6;">
                            Thank you for choosing Glassify for your custom glass needs!
                        </p>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 0 0 10px 10px; text-align: center; border: 1px solid #e0e0e0; border-top: none;">
                        <p style="margin: 0; color: #666; font-size: 13px;">Thank you for choosing Glassify!</p>
                        <p style="margin: 10px 0 0 0; color: #888; font-size: 12px;">This is an automated message, please do not reply directly to this email.</p>
                    </div>
                </div>
            ';
            
            $this->email->message($body);
            
            if ($this->email->send()) {
                log_message('info', "Installation payment email sent to {$customer->Email} for Order #{$order_number}");
                return true;
            } else {
                log_message('error', 'Failed to send installation payment email: ' . $this->email->print_debugger(['headers']));
                return false;
            }
            
        }
        catch (Exception $e) {
            log_message('error', 'send_installation_payment_email exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create installation appointment for an order
     * Sets installation date to 2 days from current date/time
     */
    private function create_installation_appointment($order_id)
    {
        if (!$this->db->table_exists('appointments')) {
            log_message('error', 'AdminCon::create_installation_appointment - Appointments table does not exist');
            return false;
        }
        
        // Check if installation appointment already exists
        $this->db->reset_query();
        $existing_installation = $this->db->where('OrderID', $order_id)
                                         ->where('Service', 'Installed')
                                         ->get('appointments')
                                         ->row();
        
        if ($existing_installation) {
            log_message('info', "AdminCon::create_installation_appointment - Installation appointment already exists for OrderID: {$order_id}");
            return true; // Already exists, don't create duplicate
        }
        
        // Get order details
        $this->db->reset_query();
        $order = $this->db->get_where('`order`', ['OrderID' => $order_id])->row();
        
        if (!$order) {
            log_message('error', "AdminCon::create_installation_appointment - Order not found: OrderID {$order_id}");
            return false;
        }
        
        // Get customer details
        $client_name = 'N/A';
        $product_name = 'N/A';
        
        if ($order->Customer_ID) {
            $this->db->reset_query();
            $this->db->select('c.*, u.First_Name, u.Middle_Name, u.Last_Name');
            $this->db->from('customer c');
            $this->db->join('user u', 'u.UserID = c.UserID', 'left');
            $this->db->where('c.Customer_ID', $order->Customer_ID);
            $customer = $this->db->get()->row();
            
            if ($customer) {
                $client_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Middle_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
            }
        }
        
        // Get product name
        if ($this->db->table_exists('order_items') && $this->db->table_exists('product')) {
            $this->db->reset_query();
            $this->db->select('p.ProductName');
            $this->db->from('order_items oi');
            $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
            $this->db->where('oi.OrderID', $order_id);
            $this->db->limit(1);
            $product = $this->db->get()->row();
            if ($product && !empty($product->ProductName)) {
                $product_name = $product->ProductName;
            }
        }
        
        // Calculate installation date: 2 days from now
        $installation_date = date('Y-m-d', strtotime('+2 days'));
        $installation_time = date('H:i:s'); // Use current time
        
        // Prepare appointment data
        $appointment_data = [
            'OrderID' => $order_id
        ];
        
        if ($this->db->field_exists('Customer_ID', 'appointments')) {
            $appointment_data['Customer_ID'] = $order->Customer_ID ?? null;
        }
        if ($this->db->field_exists('Service', 'appointments')) {
            $appointment_data['Service'] = 'Installed';
        }
        if ($this->db->field_exists('Status', 'appointments')) {
            $appointment_data['Status'] = 'In Progress';
        }
        if ($this->db->field_exists('AppointmentType', 'appointments')) {
            $appointment_data['AppointmentType'] = 'Installation';
        }
        if ($this->db->field_exists('ClientName', 'appointments')) {
            $appointment_data['ClientName'] = $client_name ?: 'N/A';
        }
        if ($this->db->field_exists('ProductName', 'appointments')) {
            $appointment_data['ProductName'] = $product_name;
        }
        if ($this->db->field_exists('AppointmentDate', 'appointments')) {
            $appointment_data['AppointmentDate'] = $installation_date;
        }
        if ($this->db->field_exists('AppointmentTime', 'appointments')) {
            $appointment_data['AppointmentTime'] = $installation_time;
        }
        
        $this->db->reset_query();
        $insert_result = $this->db->insert('appointments', $appointment_data);
        
        if (!$insert_result) {
            $db_error = $this->db->error();
            log_message('error', 'AdminCon::create_installation_appointment - Failed to insert installation appointment: ' . ($db_error['message'] ?? 'Unknown error'));
            return false;
        }
        
        // Also update order's InstallationDate field if it exists
        if ($this->db->field_exists('InstallationDate', 'order')) {
            $this->db->reset_query();
            $this->db->where('OrderID', $order_id);
            $this->db->update('`order`', ['InstallationDate' => $installation_date]);
        }
        
        log_message('info', "AdminCon::create_installation_appointment - Created installation appointment for OrderID: {$order_id}, Date: {$installation_date}");
        return true;
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
        
        // Get order details for notification and payment validation
        $order = $this->db->where('OrderID', $order_id)->get('`order`')->row();
        
        // VALIDATION: Cannot complete unless fabrication payment is PAID
        if ($this->db->field_exists('FabricationPaymentStatus', 'order')) {
            $payment_status = $order->FabricationPaymentStatus ?? 'Pending';
            if ($payment_status !== 'Paid') {
                echo json_encode(['success' => false, 'message' => 'Cannot mark as Complete: Fabrication Payment (40%) must be PAID first.']);
                return;
            }
        }
        
        // Update order status
        $this->db->where('OrderID', $order_id);
        $this->db->update('`order`', [
            'Status' => 'Ready for Installation',
            'FabricationStatus' => 'Completed',
            'FabricationProgress' => 100,
            'ActualFabricationEndDate' => date('Y-m-d')
        ]);
        
        // Create installation appointment with date 2 days from now
        // Note: Notification is now sent in update_fabrication_progress() when FabricationStatus is set to "Completed"
        // This ensures notification is sent when admin moves card to step 5 complete in kanban
        $this->create_installation_appointment($order_id);
        
        echo json_encode(['success' => true, 'message' => 'Fabrication marked as complete. Installation appointment created.']);
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
    
    // Quotations admin page removed: functionality retained in backend where needed.

    // ======================
    // RETURN ORDERS MODULE
    // ======================
    
    public function admin_return_orders()
    {
        // Return Orders page removed — redirect to Orders list
        redirect(base_url('admin-orders'));
    }

    // ======================
    // QUOTATIONS AJAX METHODS
    // ======================
    
    /**
     * Create quotation from appointment (Ocular Visit)
     * Called from appointment details modal
     */
    public function create_quotation_from_appointment()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $appointment_id = $this->input->post('appointment_id');
        $total_amount = $this->input->post('total_amount');
        $notes = $this->input->post('notes') ?: '';
        $expiry_date = $this->input->post('expiry_date') ?: null;
        
        if (!$appointment_id || !$total_amount) {
            echo json_encode(['success' => false, 'message' => 'Appointment ID and Total Amount are required']);
            return;
        }
        
        // Get appointment and order details
        $this->db->where('AppointmentID', $appointment_id);
        $appointment = $this->db->get('appointments')->row();
        
        if (!$appointment || !$appointment->OrderID) {
            echo json_encode(['success' => false, 'message' => 'Appointment or Order not found']);
            return;
        }
        
        // Get order details
        $this->db->where('OrderID', $appointment->OrderID);
        $order = $this->db->get('order')->row();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Generate quotation number
        $this->db->select('MAX(CAST(SUBSTRING(QuotationNumber, 3) AS UNSIGNED)) as max_num');
        $this->db->from('quotation');
        $this->db->like('QuotationNumber', 'QT', 'after');
        $result = $this->db->get()->row();
        $next_num = ($result && $result->max_num) ? (int)$result->max_num + 1 : 1;
        $quotation_number = 'QT' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
        
        // Prepare quotation data
        $quotation_data = [
            'QuotationNumber' => $quotation_number,
            'Customer_ID' => $order->Customer_ID,
            'SalesRep_ID' => $order->SalesRep_ID ?? 1, // Default to admin if no sales rep
            'TotalAmount' => (float)$total_amount,
            'Notes' => $notes,
            'Status' => 'Pending',
            'CreatedDate' => date('Y-m-d H:i:s'),
            'Created_Date' => date('Y-m-d H:i:s')
        ];
        
        // Add fields that may exist in different table structures
        if ($this->db->field_exists('OrderID', 'quotation')) {
            $quotation_data['OrderID'] = $appointment->OrderID;
        }
        if ($this->db->field_exists('ConvertedToOrder_ID', 'quotation')) {
            $quotation_data['ConvertedToOrder_ID'] = $appointment->OrderID;
        }
        if ($this->db->field_exists('Total_amount', 'quotation')) {
            $quotation_data['Total_amount'] = (float)$total_amount;
        }
        if ($expiry_date && $this->db->field_exists('ExpiryDate', 'quotation')) {
            $quotation_data['ExpiryDate'] = $expiry_date;
        }
        if ($this->db->field_exists('Quotation_num', 'quotation')) {
            $quotation_data['Quotation_num'] = $quotation_number;
        }
        
        // Insert quotation
        if ($this->db->insert('quotation', $quotation_data)) {
            $quotation_id = $this->db->insert_id();
            
            // Update appointment to mark quotation created
            if ($this->db->field_exists('QuotationID', 'appointments')) {
                $this->db->where('AppointmentID', $appointment_id);
                $this->db->update('appointments', ['QuotationID' => $quotation_id]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Quotation created successfully',
                'quotation_id' => $quotation_id,
                'quotation_number' => $quotation_number
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create quotation: ' . $this->db->error()['message']]);
        }
    }
    
    /**
     * Generate quotation PDF and send via email
     */
    public function send_quotation_email()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $quotation_id = $this->input->post('quotation_id');
        $appointment_id = $this->input->post('appointment_id');
        
        if (!$quotation_id) {
            echo json_encode(['success' => false, 'message' => 'Quotation ID is required']);
            return;
        }
        
        // Get quotation details
        $this->db->where('QuotationID', $quotation_id);
        $quotation = $this->db->get('quotation')->row();
        
        if (!$quotation) {
            echo json_encode(['success' => false, 'message' => 'Quotation not found']);
            return;
        }
        
        // Get customer email
        $this->db->select('u.Email, u.First_Name, u.Last_Name');
        $this->db->from('customer c');
        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
        $this->db->where('c.Customer_ID', $quotation->Customer_ID);
        $customer = $this->db->get()->row();
        
        if (!$customer || !$customer->Email) {
            echo json_encode(['success' => false, 'message' => 'Customer email not found']);
            return;
        }
        
        // Generate PDF (we'll create a simple PDF generation)
        $pdf_path = $this->generate_quotation_pdf($quotation_id);
        
        if (!$pdf_path) {
            echo json_encode(['success' => false, 'message' => 'Failed to generate PDF']);
            return;
        }
        
        // Update quotation with PDF URL
        if ($this->db->field_exists('Pdf_url', 'quotation')) {
            $this->db->where('QuotationID', $quotation_id);
            $this->db->update('quotation', ['Pdf_url' => $pdf_path]);
        }
        
        // Send email with PDF attachment
        $this->load->config('email');
        $this->load->library('email');
        
        $this->email->initialize([
            'protocol' => $this->config->item('protocol'),
            'smtp_host' => $this->config->item('smtp_host'),
            'smtp_user' => $this->config->item('smtp_user'),
            'smtp_pass' => $this->config->item('smtp_pass'),
            'smtp_port' => $this->config->item('smtp_port'),
            'smtp_crypto' => $this->config->item('smtp_crypto'),
            'mailtype' => 'html',
            'charset' => 'utf-8'
        ]);
        
        $customer_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
        $quotation_number = $quotation->QuotationNumber ?? 'QT' . str_pad($quotation_id, 3, '0', STR_PAD_LEFT);
        $total_amount = $quotation->TotalAmount ?? $quotation->Total_amount ?? 0;
        
        $this->email->from('glassifytesting@gmail.com', 'Glassify - GlassWorth Builders');
        $this->email->to($customer->Email);
        $this->email->subject('Quotation #' . $quotation_number . ' - GlassWorth Builders');
        $this->email->message("
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <h2 style='color: #083c5d;'>Quotation #{$quotation_number}</h2>
                <p>Dear {$customer_name},</p>
                <p>Thank you for your interest in our services. Please find attached your quotation.</p>
                <p><strong>Quotation Number:</strong> {$quotation_number}</p>
                <p><strong>Total Amount:</strong> ₱" . number_format($total_amount, 2) . "</p>
                <p>Please review the attached quotation and contact us if you have any questions.</p>
                <p>Best regards,<br>GlassWorth Builders Team</p>
            </body>
            </html>
        ");
        $this->email->attach($pdf_path);
        
        if ($this->email->send()) {
            // Update quotation status
            $this->db->where('QuotationID', $quotation_id);
            $this->db->update('quotation', ['Status' => 'Approved']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Quotation sent successfully to ' . $customer->Email
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email: ' . $this->email->print_debugger()]);
        }
    }
    
    /**
     * Generate quotation PDF
     */
    private function generate_quotation_pdf($quotation_id)
    {
        // Get quotation with all details
        $this->db->where('q.QuotationID', $quotation_id);
        $this->db->select('q.*, o.OrderNumber, o.DeliveryAddress, 
                          CONCAT(u.First_Name, " ", u.Last_Name) as CustomerName,
                          u.Email as CustomerEmail, u.PhoneNum as CustomerPhone');
        $this->db->from('quotation q');
        $this->db->join('order o', 'o.OrderID = q.OrderID OR o.OrderID = q.ConvertedToOrder_ID', 'left');
        $this->db->join('customer c', 'c.Customer_ID = q.Customer_ID', 'left');
        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
        $quotation = $this->db->get()->row();
        
        if (!$quotation) {
            return false;
        }
        
        // Create PDF directory if it doesn't exist
        $pdf_dir = './uploads/quotations/';
        if (!is_dir($pdf_dir)) {
            mkdir($pdf_dir, 0755, true);
        }
        
        // For now, we'll create a simple HTML file that can be converted to PDF
        // In production, you'd use a library like TCPDF or DomPDF
        $quotation_number = $quotation->QuotationNumber ?? 'QT' . str_pad($quotation_id, 3, '0', STR_PAD_LEFT);
        $pdf_filename = 'quotation_' . $quotation_number . '_' . date('YmdHis') . '.html';
        $pdf_path = $pdf_dir . $pdf_filename;
        
        // Build a simple HTML fallback for the quotation PDF (previously used a view)
        $customer_name = htmlspecialchars($quotation->CustomerName ?? 'N/A');
        $customer_email = htmlspecialchars($quotation->CustomerEmail ?? '');
        $customer_phone = htmlspecialchars($quotation->CustomerPhone ?? '');
        $order_number = htmlspecialchars($quotation->OrderNumber ?? '');
        $delivery_address = htmlspecialchars($quotation->DeliveryAddress ?? '');
        $total_amount = number_format($quotation->TotalAmount ?? $quotation->Total_amount ?? 0, 2);
        $notes = !empty($quotation->Notes) ? nl2br(htmlspecialchars($quotation->Notes)) : '';

        $html_content = '<!DOCTYPE html>' . "\n";
        $html_content .= '<html><head><meta charset="UTF-8"><title>Quotation ' . $quotation_number . '</title>' . "\n";
        $html_content .= '<style>body{font-family:Arial,sans-serif;margin:40px;color:#333}.header{border-bottom:3px solid #083c5d;padding-bottom:20px;margin-bottom:30px}.company-name{font-size:24px;font-weight:bold;color:#083c5d;margin-bottom:5px}.quotation-title{font-size:18px;color:#666;margin-top:10px}.quotation-number{font-size:16px;font-weight:bold;color:#083c5d}.items-table{width:100%;border-collapse:collapse;margin:30px 0}.items-table th,.items-table td{border:1px solid #ddd;padding:12px;text-align:left}.items-table th{background-color:#083c5d;color:#fff}.total-section{text-align:right;margin-top:30px}.total-amount{font-size:20px;font-weight:bold;color:#083c5d;margin-top:10px}.footer{margin-top:50px;padding-top:20px;border-top:1px solid #ddd;font-size:12px;color:#666}</style>' . "\n";
        $html_content .= '</head><body>' . "\n";
        $html_content .= '<div class="header"><div class="company-name">GlassWorth Builders</div><div class="quotation-title">QUOTATION</div><div class="quotation-number">Quotation #: ' . $quotation_number . '</div><div style="margin-top:10px;">Date: ' . date('F d, Y', strtotime($quotation->CreatedDate ?? date('Y-m-d'))) . '</div></div>' . "\n";

        $html_content .= '<div class="info-section">';
        $html_content .= '<div style="margin:10px 0;"><strong>Customer:</strong> ' . $customer_name . '</div>';
        if (!empty($customer_email)) {
            $html_content .= '<div style="margin:10px 0;"><strong>Email:</strong> ' . $customer_email . '</div>';
        }
        if (!empty($customer_phone)) {
            $html_content .= '<div style="margin:10px 0;"><strong>Phone:</strong> ' . $customer_phone . '</div>';
        }
        if (!empty($order_number)) {
            $html_content .= '<div style="margin:10px 0;"><strong>Order Number:</strong> ' . $order_number . '</div>';
        }
        if (!empty($delivery_address)) {
            $html_content .= '<div style="margin:10px 0;"><strong>Delivery Address:</strong> ' . $delivery_address . '</div>';
        }
        $html_content .= '</div>' . "\n";

        $html_content .= '<table class="items-table"><thead><tr><th>Description</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr></thead><tbody>';
        $html_content .= '<tr><td>Glass Product</td><td>1</td><td>₱' . $total_amount . '</td><td>₱' . $total_amount . '</td></tr>';
        $html_content .= '</tbody></table>' . "\n";

        $html_content .= '<div class="total-section"><div class="total-amount">Total Amount: ₱' . $total_amount . '</div></div>' . "\n";

        if (!empty($notes)) {
            $html_content .= '<div class="info-section"><div><strong>Notes:</strong></div><div>' . $notes . '</div></div>' . "\n";
        }

        $html_content .= '<div class="footer"><p>Thank you for choosing GlassWorth Builders!</p><p>For inquiries, please contact us at:</p><p>Phone: 09275193300 / 09761653506</p><p>Email: glassworthbuilders@gmail.com</p></div>' . "\n";
        $html_content .= '</body></html>' . "\n";

        file_put_contents($pdf_path, $html_content);
        
        // Return path (in production, convert HTML to actual PDF)
        return $pdf_path;
    }
    
    /**
     * Proceed order to fabrication after quotation is sent
     */
    public function proceed_to_fabrication()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        $quotation_id = $this->input->post('quotation_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Update order status to "In Fabrication"
        $this->db->where('OrderID', $order_id);
        $update_data = ['Status' => 'In Fabrication'];
        
        // Update quotation status if provided
        if ($quotation_id) {
            $this->db->where('QuotationID', $quotation_id);
            $this->db->update('quotation', ['Status' => 'Converted to Order']);
        }
        
        if ($this->db->update('order', $update_data)) {
            // Log activity
            if ($this->db->table_exists('system_activity_log')) {
                $this->db->insert('system_activity_log', [
                    'Action' => 'Order Moved to Fabrication',
                    'Description' => "Order moved to fabrication after quotation sent. Order ID: {$order_id}",
                    'Role' => 'Admin',
                    'UserID' => $this->session->userdata('user_id'),
                    'RelatedID' => $order_id,
                    'RelatedType' => 'Order',
                    'Timestamp' => date('Y-m-d H:i:s')
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Order moved to fabrication successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
        }
    }
    
    public function get_quotations_ajax()
    {
        header('Content-Type: application/json');
        
        try {
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

            if (!$this->db->table_exists('quotation')) {
                echo json_encode([
                    'success' => true,
                    'quotations' => [],
                    'total' => 0,
                    'total_pages' => 0,
                    'current_page' => (int)$page
                ]);
                return;
            }

            $quotation_items_table_exists = $this->db->table_exists('quotation_items');
            $product_table_exists = $this->db->table_exists('product');
            $employee_table_exists = $this->db->table_exists('employee');
            $customer_table_exists = $this->db->table_exists('customer');
            $user_table_exists = $this->db->table_exists('user');
            $order_table_exists = $this->db->table_exists('order');

            $quotation_number_field = $this->db->field_exists('QuotationNumber', 'quotation') ? 'QuotationNumber' : ($this->db->field_exists('Quotation_num', 'quotation') ? 'Quotation_num' : null);
            $quotation_total_field = $this->db->field_exists('TotalAmount', 'quotation') ? 'TotalAmount' : ($this->db->field_exists('Total_amount', 'quotation') ? 'Total_amount' : null);
            $quotation_created_field = $this->db->field_exists('CreatedDate', 'quotation') ? 'CreatedDate' : ($this->db->field_exists('Created_date', 'quotation') ? 'Created_date' : ($this->db->field_exists('Created_Date', 'quotation') ? 'Created_Date' : null));
            $quotation_status_field = $this->db->field_exists('Status', 'quotation') ? 'Status' : null;
            $quotation_customer_field = $this->db->field_exists('Customer_ID', 'quotation') ? 'Customer_ID' : ($this->db->field_exists('CustomerID', 'quotation') ? 'CustomerID' : null);
            $quotation_salesrep_field = $this->db->field_exists('SalesRep_ID', 'quotation') ? 'SalesRep_ID' : ($this->db->field_exists('SalesRepID', 'quotation') ? 'SalesRepID' : null);
            $quotation_order_field = $this->db->field_exists('ConvertedToOrder_ID', 'quotation') ? 'ConvertedToOrder_ID' : ($this->db->field_exists('OrderID', 'quotation') ? 'OrderID' : null);

            $user_phone_field = $user_table_exists
                ? ($this->db->field_exists('Phone', 'user') ? 'Phone' : ($this->db->field_exists('PhoneNum', 'user') ? 'PhoneNum' : null))
                : null;

            $employee_id_field = $employee_table_exists
                ? ($this->db->field_exists('EmployeeID', 'employee') ? 'EmployeeID' : ($this->db->field_exists('Employee_ID', 'employee') ? 'Employee_ID' : null))
                : null;
            $employee_user_field = $employee_table_exists
                ? ($this->db->field_exists('UserID', 'employee') ? 'UserID' : ($this->db->field_exists('User_ID', 'employee') ? 'User_ID' : null))
                : null;
            $quotation_items_product_field = $quotation_items_table_exists
                ? ($this->db->field_exists('ProductID', 'quotation_items') ? 'ProductID' : ($this->db->field_exists('Product_ID', 'quotation_items') ? 'Product_ID' : null))
                : null;

            // Build query - resilient to missing columns
            $select_fields = ['q.QuotationID'];
            if ($quotation_number_field) {
                $select_fields[] = 'q.' . $quotation_number_field . ' as quotation_number';
            } else {
                $select_fields[] = 'NULL as quotation_number';
            }
            if ($quotation_total_field) {
                $select_fields[] = 'q.' . $quotation_total_field . ' as total_amount';
            } else {
                $select_fields[] = 'NULL as total_amount';
            }
            if ($quotation_created_field) {
                $select_fields[] = 'q.' . $quotation_created_field . ' as created_date';
            } else {
                $select_fields[] = 'NULL as created_date';
            }
            if ($quotation_status_field) {
                $select_fields[] = 'q.' . $quotation_status_field . ' as quotation_status';
            } else {
                $select_fields[] = 'NULL as quotation_status';
            }

            if ($order_table_exists && $quotation_order_field && $this->db->field_exists('OrderNumber', 'order')) {
                $select_fields[] = 'o.OrderNumber';
            }

            if ($user_table_exists) {
                $select_fields[] = 'CONCAT(u.First_Name, " ", u.Last_Name) as customer_name';
                $select_fields[] = 'u.Email as customer_email';
                if ($user_phone_field) {
                    $select_fields[] = 'u.' . $user_phone_field . ' as customer_phone';
                } else {
                    $select_fields[] = 'NULL as customer_phone';
                }
            } else {
                $select_fields[] = 'NULL as customer_name';
                $select_fields[] = 'NULL as customer_email';
                $select_fields[] = 'NULL as customer_phone';
            }

            if ($user_table_exists) {
                $select_fields[] = 'CONCAT(sr.First_Name, " ", sr.Last_Name) as sales_rep_name';
            } else {
                $select_fields[] = 'NULL as sales_rep_name';
            }

            if ($quotation_items_table_exists && $product_table_exists && $quotation_items_product_field) {
                $select_fields[] = 'p.ProductName as product_name';
            } else {
                $select_fields[] = 'NULL as product_name';
            }

            $this->db->select(implode(', ', $select_fields));
            $this->db->from('quotation q');
            if ($order_table_exists && $quotation_order_field) {
                $this->db->join('`order` o', 'q.' . $quotation_order_field . ' = o.OrderID', 'left');
            }
            if ($customer_table_exists && $quotation_customer_field) {
                $this->db->join('customer c', 'q.' . $quotation_customer_field . ' = c.Customer_ID', 'left');
            }
            if ($customer_table_exists && $user_table_exists) {
                $this->db->join('user u', 'c.UserID = u.UserID', 'left');
            }
            if ($quotation_items_table_exists) {
                $this->db->join('quotation_items qi', 'qi.QuotationID = q.QuotationID', 'left');
            }
            if ($quotation_items_table_exists && $product_table_exists && $quotation_items_product_field) {
                $this->db->join('product p', 'p.Product_ID = qi.' . $quotation_items_product_field, 'left');
            }
            if ($quotation_salesrep_field && $user_table_exists) {
                $this->db->join('user sr', 'q.' . $quotation_salesrep_field . ' = sr.UserID', 'left');
            } elseif ($quotation_salesrep_field && $employee_table_exists && $employee_id_field && $employee_user_field && $user_table_exists) {
                $this->db->join('employee e', 'q.' . $quotation_salesrep_field . ' = e.' . $employee_id_field, 'left');
                $this->db->join('user sr', 'e.' . $employee_user_field . ' = sr.UserID', 'left');
            }
            $this->db->group_by('q.QuotationID');

            // Apply filters
            if ($status_filter && $status_filter !== 'all' && $quotation_status_field) {
                $this->db->where('q.' . $quotation_status_field, $status_filter);
            }

            if ($date_start && $quotation_created_field) {
                $this->db->where('DATE(q.' . $quotation_created_field . ') >=', $date_start);
            }
            if ($date_end && $quotation_created_field) {
                $this->db->where('DATE(q.' . $quotation_created_field . ') <=', $date_end);
            }

            if ($client_search && $user_table_exists) {
                $this->db->group_start();
                $this->db->like('u.First_Name', $client_search);
                $this->db->or_like('u.Last_Name', $client_search);
                $this->db->or_like('u.Email', $client_search);
                if ($user_phone_field) {
                    $this->db->or_like('u.' . $user_phone_field, $client_search);
                }
                $this->db->group_end();
            }

            if ($sales_rep && $sales_rep !== 'all' && $quotation_salesrep_field) {
                $this->db->where('q.' . $quotation_salesrep_field, $sales_rep);
            }

            if ($amount_min && $quotation_total_field) {
                $this->db->where('q.' . $quotation_total_field . ' >=', $amount_min);
            }
            if ($amount_max && $quotation_total_field) {
                $this->db->where('q.' . $quotation_total_field . ' <=', $amount_max);
            }

            // Get total count
            $total = 0;
            $total_query = $this->db->get_compiled_select('', false);
            $count_row = $this->db->query("SELECT COUNT(*) as total FROM ($total_query) as count_query")->row();
            if ($count_row && isset($count_row->total)) {
                $total = (int)$count_row->total;
            }

            // Apply pagination
            $this->db->limit($limit, $offset);

            // Get results
            $quotations = $this->db->get()->result();

            // Format results
            $formatted_quotations = [];
            foreach ($quotations as $q) {
                $created_date = $q->created_date ? date('m/d/Y', strtotime($q->created_date)) : 'N/A';
                $formatted_quotations[] = [
                    'quotation_id' => $q->QuotationID,
                    'quotation_number' => $q->quotation_number ?? 'N/A',
                    'client_name' => $q->customer_name ?? 'N/A',
                    'sales_rep_name' => $q->sales_rep_name ?? 'N/A',
                    'product_name' => $q->product_name ?? 'N/A',
                    'total_amount' => $q->total_amount ?? 0,
                    'created_date' => $created_date,
                    'status' => $q->quotation_status ?? 'Pending'
                ];
            }

            echo json_encode([
                'success' => true,
                'quotations' => $formatted_quotations,
                'total' => $total,
                'total_pages' => $limit ? ceil($total / $limit) : 0,
                'current_page' => (int)$page
            ]);
        }
        catch (Exception $e) {
            log_message('error', 'AdminCon::get_quotations_ajax - Exception: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_quotations_ajax - Stack trace: ' . $e->getTraceAsString());
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                log_message('error', 'AdminCon::get_quotations_ajax - DB Error: ' . $db_error['message']);
            }
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading quotations: ' . $e->getMessage(),
                'quotations' => [],
                'total' => 0,
                'total_pages' => 0,
                'current_page' => 1
            ]);
        } catch (Error $e) {
            log_message('error', 'AdminCon::get_quotations_ajax - Fatal Error: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_quotations_ajax - Stack trace: ' . $e->getTraceAsString());
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                log_message('error', 'AdminCon::get_quotations_ajax - DB Error: ' . $db_error['message']);
            }
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Fatal error loading quotations: ' . $e->getMessage(),
                'quotations' => [],
                'total' => 0,
                'total_pages' => 0,
                'current_page' => 1
            ]);
        }
    }
    
    public function get_quotation_details_ajax()
    {
        header('Content-Type: application/json');
        
        try {
            $quotation_id = $this->input->get('quotation_id');
            if (!$quotation_id) {
                echo json_encode(['success' => false, 'message' => 'Quotation ID required']);
                return;
            }

            if (!$this->db->table_exists('quotation')) {
                echo json_encode(['success' => false, 'message' => 'Quotation table not found']);
                return;
            }

            $customer_table_exists = $this->db->table_exists('customer');
            $user_table_exists = $this->db->table_exists('user');
            $employee_table_exists = $this->db->table_exists('employee');
            $order_table_exists = $this->db->table_exists('order');
            $quotation_items_table_exists = $this->db->table_exists('quotation_items');
            $product_table_exists = $this->db->table_exists('product');

            $quotation_number_field = $this->db->field_exists('QuotationNumber', 'quotation') ? 'QuotationNumber' : ($this->db->field_exists('Quotation_num', 'quotation') ? 'Quotation_num' : null);
            $quotation_total_field = $this->db->field_exists('TotalAmount', 'quotation') ? 'TotalAmount' : ($this->db->field_exists('Total_amount', 'quotation') ? 'Total_amount' : null);
            $quotation_created_field = $this->db->field_exists('CreatedDate', 'quotation') ? 'CreatedDate' : ($this->db->field_exists('Created_date', 'quotation') ? 'Created_date' : ($this->db->field_exists('Created_Date', 'quotation') ? 'Created_Date' : null));
            $quotation_status_field = $this->db->field_exists('Status', 'quotation') ? 'Status' : null;
            $quotation_customer_field = $this->db->field_exists('Customer_ID', 'quotation') ? 'Customer_ID' : ($this->db->field_exists('CustomerID', 'quotation') ? 'CustomerID' : null);
            $quotation_salesrep_field = $this->db->field_exists('SalesRep_ID', 'quotation') ? 'SalesRep_ID' : ($this->db->field_exists('SalesRepID', 'quotation') ? 'SalesRepID' : null);
            $quotation_order_field = $this->db->field_exists('ConvertedToOrder_ID', 'quotation') ? 'ConvertedToOrder_ID' : ($this->db->field_exists('OrderID', 'quotation') ? 'OrderID' : null);

            $user_phone_field = $user_table_exists
                ? ($this->db->field_exists('Phone', 'user') ? 'Phone' : ($this->db->field_exists('PhoneNum', 'user') ? 'PhoneNum' : null))
                : null;

            $employee_id_field = $employee_table_exists
                ? ($this->db->field_exists('EmployeeID', 'employee') ? 'EmployeeID' : ($this->db->field_exists('Employee_ID', 'employee') ? 'Employee_ID' : null))
                : null;
            $employee_user_field = $employee_table_exists
                ? ($this->db->field_exists('UserID', 'employee') ? 'UserID' : ($this->db->field_exists('User_ID', 'employee') ? 'User_ID' : null))
                : null;
            $quotation_items_product_field = $quotation_items_table_exists
                ? ($this->db->field_exists('ProductID', 'quotation_items') ? 'ProductID' : ($this->db->field_exists('Product_ID', 'quotation_items') ? 'Product_ID' : null))
                : null;

            $select_fields = ['q.*'];
            if ($order_table_exists && $quotation_order_field && $this->db->field_exists('OrderNumber', 'order')) {
                $select_fields[] = 'o.OrderNumber';
            }
            if ($user_table_exists) {
                $select_fields[] = 'CONCAT(u.First_Name, " ", u.Last_Name) as customer_name';
                $select_fields[] = 'u.Email as customer_email';
                if ($user_phone_field) {
                    $select_fields[] = 'u.' . $user_phone_field . ' as customer_phone';
                } else {
                    $select_fields[] = 'NULL as customer_phone';
                }
            }
            if ($customer_table_exists && $this->db->field_exists('Address', 'customer')) {
                $select_fields[] = 'c.Address as customer_address';
            } else {
                $select_fields[] = 'NULL as customer_address';
            }
            if ($user_table_exists) {
                $select_fields[] = 'CONCAT(sr.First_Name, " ", sr.Last_Name) as sales_rep_name';
            } else {
                $select_fields[] = 'NULL as sales_rep_name';
            }

            $this->db->select(implode(', ', $select_fields));
            $this->db->from('quotation q');
            if ($order_table_exists && $quotation_order_field) {
                $this->db->join('`order` o', 'q.' . $quotation_order_field . ' = o.OrderID', 'left');
            }
            if ($customer_table_exists && $quotation_customer_field) {
                $this->db->join('customer c', 'q.' . $quotation_customer_field . ' = c.Customer_ID', 'left');
            }
            if ($customer_table_exists && $user_table_exists) {
                $this->db->join('user u', 'c.UserID = u.UserID', 'left');
            }
            if ($quotation_salesrep_field && $user_table_exists) {
                $this->db->join('user sr', 'q.' . $quotation_salesrep_field . ' = sr.UserID', 'left');
            } elseif ($quotation_salesrep_field && $employee_table_exists && $employee_id_field && $employee_user_field && $user_table_exists) {
                $this->db->join('employee e', 'q.' . $quotation_salesrep_field . ' = e.' . $employee_id_field, 'left');
                $this->db->join('user sr', 'e.' . $employee_user_field . ' = sr.UserID', 'left');
            }
            $this->db->where('q.QuotationID', $quotation_id);
            $quotation = $this->db->get()->row();
            
            if (!$quotation) {
                echo json_encode(['success' => false, 'message' => 'Quotation not found']);
                return;
            }

            // Get quotation items
            $items = [];
            if ($quotation_items_table_exists && $quotation_items_product_field) {
                $this->db->select('qi.*, p.ProductName');
                $this->db->from('quotation_items qi');
                if ($product_table_exists) {
                    $this->db->join('product p', 'p.Product_ID = qi.' . $quotation_items_product_field, 'left');
                }
                $this->db->where('qi.QuotationID', $quotation->QuotationID);
                $items = $this->db->get()->result();
            }
        
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
            if ($quotation_status_field && isset($quotation->$quotation_status_field) && $quotation->$quotation_status_field === 'Converted to Order') {
                if (isset($quotation->OrderNumber)) {
                    $linked_order_id = $quotation->OrderNumber;
                }
            }

            $created_date = ($quotation_created_field && isset($quotation->$quotation_created_field) && $quotation->$quotation_created_field)
                ? date('m/d/Y', strtotime($quotation->$quotation_created_field))
                : 'N/A';

            $total_amount = ($quotation_total_field && isset($quotation->$quotation_total_field))
                ? $quotation->$quotation_total_field
                : 0;

            $quotation_number = ($quotation_number_field && isset($quotation->$quotation_number_field))
                ? $quotation->$quotation_number_field
                : 'N/A';

            $status_value = ($quotation_status_field && isset($quotation->$quotation_status_field))
                ? $quotation->$quotation_status_field
                : 'Pending';

            echo json_encode([
                'success' => true,
                'quotation' => [
                    'quotation_id' => $quotation->QuotationID,
                    'quotation_number' => $quotation_number,
                    'created_date' => $created_date,
                    'expiry_date' => isset($quotation->ExpiryDate) ? date('m/d/Y', strtotime($quotation->ExpiryDate)) : 'N/A',
                    'status' => $status_value,
                    'customer_name' => $quotation->customer_name ?? 'N/A',
                    'customer_email' => $quotation->customer_email ?? 'N/A',
                    'customer_phone' => $quotation->customer_phone ?? 'N/A',
                    'customer_address' => $quotation->customer_address ?? 'N/A',
                    'sales_rep_name' => $quotation->sales_rep_name ?? 'N/A',
                    'total_amount' => $total_amount,
                    'items' => $formatted_items,
                    'linked_order_id' => $linked_order_id,
                    'admin_notes' => isset($quotation->AdminNotes) ? $quotation->AdminNotes : ''
                ]
            ]);
        }
        catch (Exception $e) {
            log_message('error', 'AdminCon::get_quotation_details_ajax - Exception: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_quotation_details_ajax - Stack trace: ' . $e->getTraceAsString());
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                log_message('error', 'AdminCon::get_quotation_details_ajax - DB Error: ' . $db_error['message']);
            }
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error loading quotation details: ' . $e->getMessage()]);
        } catch (Error $e) {
            log_message('error', 'AdminCon::get_quotation_details_ajax - Fatal Error: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_quotation_details_ajax - Stack trace: ' . $e->getTraceAsString());
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                log_message('error', 'AdminCon::get_quotation_details_ajax - DB Error: ' . $db_error['message']);
            }
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Fatal error loading quotation details: ' . $e->getMessage()]);
        }
    }
    
    public function get_sales_reps_ajax()
    {
        header('Content-Type: application/json');
        
        try {
            if (!$this->db->table_exists('employee') || !$this->db->table_exists('user')) {
                echo json_encode([
                    'success' => true,
                    'sales_reps' => []
                ]);
                return;
            }

            $employee_id_field = $this->db->field_exists('EmployeeID', 'employee') ? 'EmployeeID' : ($this->db->field_exists('Employee_ID', 'employee') ? 'Employee_ID' : null);
            $employee_user_field = $this->db->field_exists('UserID', 'employee') ? 'UserID' : ($this->db->field_exists('User_ID', 'employee') ? 'User_ID' : null);
            $employee_role_field = $this->db->field_exists('Role', 'employee') ? 'Role' : null;

            if (!$employee_id_field || !$employee_user_field) {
                echo json_encode([
                    'success' => true,
                    'sales_reps' => []
                ]);
                return;
            }

            $this->db->select('e.' . $employee_id_field . ' as user_id, CONCAT(u.First_Name, " ", u.Last_Name) as name, u.First_Name as first_name, u.Last_Name as last_name');
            $this->db->from('employee e');
            $this->db->join('user u', 'e.' . $employee_user_field . ' = u.UserID', 'left');
            if ($employee_role_field) {
                $this->db->where('e.' . $employee_role_field, 'Sales Representative');
            }
            $sales_reps = $this->db->get()->result();
            
            echo json_encode([
                'success' => true,
                'sales_reps' => $sales_reps
            ]);
        }
        catch (Exception $e) {
            log_message('error', 'AdminCon::get_sales_reps_ajax - Exception: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_sales_reps_ajax - Stack trace: ' . $e->getTraceAsString());
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                log_message('error', 'AdminCon::get_sales_reps_ajax - DB Error: ' . $db_error['message']);
            }
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading sales reps: ' . $e->getMessage(),
                'sales_reps' => []
            ]);
        } catch (Error $e) {
            log_message('error', 'AdminCon::get_sales_reps_ajax - Fatal Error: ' . $e->getMessage());
            log_message('error', 'AdminCon::get_sales_reps_ajax - Stack trace: ' . $e->getTraceAsString());
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                log_message('error', 'AdminCon::get_sales_reps_ajax - DB Error: ' . $db_error['message']);
            }
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Fatal error loading sales reps: ' . $e->getMessage(),
                'sales_reps' => []
            ]);
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

