<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ShopCon extends CI_Controller
{


     public function __construct()
    {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->library('session');
        $this->load->helper('url');
    }

public function products()
{
    $data['title'] = "Glassify - Products";

    // Load products from database
    $data['products'] = $this->Product_model->get_products();

    $this->load->view('includes/header', $data);
    $this->load->view('shop/products', $data);  // now has $products available
    $this->load->view('includes/footer');
}



public function product_2d()
{
    $this->load->model('Product_model');

    // Get id from GET instead of method param
    $id = $this->input->get('id');

    if ($id) {
        $product = $this->Product_model->get_product($id);
    } else {
        // Get the latest product as default
        $product = $this->Product_model->get_products()[0] ?? null;
    }

    if (!$product) {
        show_404();
    }

    $data['title'] = "Glassify - 2D Modeling";
    $data['product'] = $product;

    $this->load->view('includes/header', $data);
    $this->load->view('shop/2DModeling', $data);
    $this->load->view('includes/footer');
}

// ShopCon.php
public function checkout()
{
    // Check if user is logged in and is a customer
    if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Customer') {
        // Set cache control headers to prevent back button access
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');
        redirect('login');
        return;
    }
    
    // Set cache control headers for customer pages
    $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
    $this->output->set_header('Pragma: no-cache');
    $this->output->set_header('Expires: 0');
    
    $userID = $this->session->userdata('user_id');
    $data['user'] = null;
    $data['addresses'] = ['Shipping' => null, 'Billing' => null];

    if ($userID) {
        $this->load->model('User_model');
        $data['user'] = $this->User_model->get_by_id($userID);
        $data['addresses'] = $this->User_model->get_addresses($userID);
    }

    // fallback if user not found
    if (!$data['user']) {
        $data['user'] = (object)[
            'First_Name' => '',
            'Middle_Name' => '',
            'Last_Name' => '',
            'Email' => '',
            'PhoneNum' => '',
            'ImageUrl' => ''
        ];
    }

    // fallback addresses
    foreach (['Shipping', 'Billing'] as $type) {
        if (!$data['addresses'][$type]) {
            $data['addresses'][$type] = (object)[
                'AddressLine' => '',
                'City' => '',
                'Province' => '',
                'Country' => '',
                'ZipCode' => '',
                'Note' => ''
            ];
        }
    }
        $data['title'] = "Glassify - Payment";
    $this->load->view('includes/header', $data);
    $this->load->view('shop/checkout', $data);
    $this->load->view('includes/footer');
}




    public function ewallet()
    {
        $data['title'] = "Glassify - Payment";
        
        // Get pending order data from session
        $data['pending_summary'] = $this->session->userdata('pending_order_summary');
        $data['pending_cart_ids'] = $this->session->userdata('pending_cart_ids');
        
        // Log session data for debugging
        log_message('debug', 'Ewallet Page - Session Data: ' . json_encode([
            'pending_summary' => $data['pending_summary'],
            'pending_cart_ids' => $data['pending_cart_ids'],
            'all_session_keys' => array_keys($this->session->all_userdata())
        ]));
        
        // Check if we have valid pending order data (with actual values)
        $has_valid_order = false;
        if (is_array($data['pending_summary']) && 
            isset($data['pending_summary']['total']) && 
            $data['pending_summary']['total'] > 0) {
            $has_valid_order = true;
        }
        
        log_message('debug', 'Ewallet Page - Has valid order: ' . ($has_valid_order ? 'YES' : 'NO'));
        
        // If no valid pending order, redirect back to checkout
        if (!$has_valid_order) {
            log_message('error', 'Ewallet Page - Redirecting: No valid pending order');
            $this->session->set_flashdata('error', 'No pending order found. Please place an order first.');
            redirect('payment');
            return;
        }
        
        $this->load->view('includes/header', $data);
        $this->load->view('shop/ewallet', $data);
        $this->load->view('includes/footer');
    }

    /**
     * Submit E-Wallet Payment - Create order after payment receipt is uploaded
     */
    public function submit_ewallet_payment()
    {
        // Check if user is logged in
        $customer_id = $this->session->userdata('customer_id');
        if (!$customer_id) {
            $this->session->set_flashdata('error', 'Please log in to complete payment.');
            redirect('login');
            return;
        }

        // Get pending order data from session
        $order_data = $this->session->userdata('pending_order_data');
        $selected_cart_ids = $this->session->userdata('pending_cart_ids');

        if (empty($order_data)) {
            $this->session->set_flashdata('error', 'No pending order found. Please try again.');
            redirect('payment');
            return;
        }

        // Handle file upload
        $config['upload_path'] = './uploads/receipts/';
        $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf';
        $config['max_size'] = 5120; // 5MB
        $config['file_name'] = 'receipt_' . $customer_id . '_' . time();

        // Create upload directory if not exists
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('receipt')) {
            $this->session->set_flashdata('error', 'Failed to upload receipt: ' . $this->upload->display_errors('', ''));
            redirect('paying');
            return;
        }

        $upload_data = $this->upload->data();
        $receipt_path = 'uploads/receipts/' . $upload_data['file_name'];

        // Load models
        $this->load->model('Cart_model');
        $this->load->model('Order_model');

        // Now create the order
        $order_id = $this->Order_model->create_order($order_data);

        if (!$order_id) {
            $this->session->set_flashdata('error', 'Failed to create order. Please try again.');
            redirect('paying');
            return;
        }

        // Get cart items for order customizations
        $cart_items = $this->Cart_model->get_cart_items($customer_id);
        
        // Filter to only selected items
        if (!empty($selected_cart_ids)) {
            $selected_ids = array_filter(array_map('intval', explode(',', $selected_cart_ids)));
            if (!empty($selected_ids)) {
                $cart_items = array_filter($cart_items, function($item) use ($selected_ids) {
                    return in_array($item->Cart_ID, $selected_ids);
                });
                $cart_items = array_values($cart_items);
            }
        }

        // Save order customizations
        $this->Order_model->save_order_customizations($order_id, $cart_items);

        // Save payment receipt reference
        $this->Order_model->save_payment_receipt($order_id, $receipt_path, $order_data['TotalAmount']);

        // Store order info in session for complete page
        $this->session->set_userdata([
            'last_order_id' => $order_id,
            'last_order_total' => $order_data['TotalAmount'],
            'last_payment_method' => 'E-Wallet'
        ]);

        // Remove selected items from cart
        if (!empty($selected_cart_ids)) {
            $selected_ids = array_filter(array_map('intval', explode(',', $selected_cart_ids)));
            foreach ($selected_ids as $cart_id) {
                $this->Cart_model->remove_item($cart_id);
            }
        }

        // Clear pending order data from session
        $this->session->unset_userdata(['pending_order_data', 'pending_cart_ids', 'pending_order_summary']);

        // Redirect to order complete page
        redirect('complete');
    }
    public function complete()
    {
        $data['title'] = "Glassify - Order Complete";
        
        // Get order ID from session
        $order_id = $this->session->userdata('last_order_id');
        $payment_method = $this->session->userdata('last_payment_method');
        
        // Load models
        $this->load->model('Order_model');
        $this->load->model('User_model');
        
        // Default values
        $data['order'] = null;
        $data['order_items'] = [];
        $data['summary'] = [
            'items' => 0,
            'subtotal' => 0,
            'shipping' => 0,
            'handling' => 0,
            'total' => 0
        ];
        $data['shipping_address'] = null;
        $data['payment_method'] = $payment_method ?? 'Cash on Delivery';
        
        if ($order_id) {
            // Get order with customer details
            $data['order'] = $this->Order_model->get_order_with_customer($order_id);
            
            // Get order items (customizations)
            $data['order_items'] = $this->Order_model->get_order_customizations($order_id);
            
            // Calculate summary
            $data['summary'] = $this->Order_model->calculate_order_summary($order_id);
            
            // Get customer shipping address and user data
            $customer_id = $this->session->userdata('customer_id');
            if ($customer_id) {
                $addresses = $this->User_model->get_addresses($customer_id);
                $data['shipping_address'] = $addresses['Shipping'] ?? null;
                $data['billing_address'] = $addresses['Billing'] ?? null;
                
                // Get user data - first get customer to find UserID
                $customer = $this->db->where('Customer_ID', $customer_id)->get('customer')->row();
                if ($customer && $customer->UserID) {
                    $data['user'] = $this->User_model->get_by_id($customer->UserID);
                } else {
                    // Fallback: use data from order if available
                    if ($data['order'] && isset($data['order']->Email)) {
                        $data['user'] = (object)[
                            'First_Name' => $data['order']->First_Name ?? '',
                            'Last_Name' => $data['order']->Last_Name ?? '',
                            'Email' => $data['order']->Email ?? '',
                            'PhoneNum' => $data['order']->PhoneNum ?? ''
                        ];
                    }
                }
            } else if ($data['order'] && isset($data['order']->Email)) {
                // Fallback: use data from order if customer_id not in session
                $data['user'] = (object)[
                    'First_Name' => $data['order']->First_Name ?? '',
                    'Last_Name' => $data['order']->Last_Name ?? '',
                    'Email' => $data['order']->Email ?? '',
                    'PhoneNum' => $data['order']->PhoneNum ?? ''
                ];
            }
        }
        
        $this->load->view('includes/header', $data);
        $this->load->view('shop/order_complete', $data);
        $this->load->view('includes/footer');
    }

    public function order_tracking()
    {
        $data['title'] = "Glassify - Order Tracking";

        // Get order ID from URL parameter
        $order_id = $this->input->get('order');

        // Load models
        $this->load->model('Order_model');
        $this->load->model('User_model');

        // Default values
        $data['order'] = null;
        $data['order_items'] = [];
        $data['summary'] = [
            'items' => 0,
            'subtotal' => 0,
            'shipping' => 0,
            'handling' => 0,
            'total' => 0
        ];
        $data['payment'] = null;
        $data['progress'] = [];
        $data['shipping_address'] = null;
        $data['billing_address'] = null;

        if ($order_id) {
            // Get order tracking details
            $data['order'] = $this->Order_model->get_order_tracking_details($order_id);

            if ($data['order']) {
                // Get order items
                $data['order_items'] = $this->Order_model->get_order_customizations($order_id);

                // Calculate summary
                $data['summary'] = $this->Order_model->calculate_order_summary($order_id);

                // Get payment info
                $data['payment'] = $this->Order_model->get_order_payment($order_id);

                // Get progress steps based on status (pass order_id to check appointments table)
                $data['progress'] = $this->Order_model->get_order_progress($data['order']->Status, $order_id);

                // Get customer addresses
                $customer_id = $data['order']->Customer_ID;
                if ($customer_id) {
                    $addresses = $this->User_model->get_addresses($customer_id);
                    $data['shipping_address'] = $addresses['Shipping'] ?? null;
                    $data['billing_address'] = $addresses['Billing'] ?? null;
                }
            }
        }

        $this->load->view('includes/header', $data);
        $this->load->view('shop/order_tracking', $data);
        $this->load->view('includes/footer');
    }

    /**
     * AJAX endpoint to get real-time order progress updates
     * Used for polling to sync appointment changes
     */
    public function get_order_progress_ajax()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->get('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            return;
        }
        
        // Load models
        $this->load->model('Order_model');
        
        // Get order details
        $order = $this->Order_model->get_order_tracking_details($order_id);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Get progress steps
        $progress = $this->Order_model->get_order_progress($order->Status, $order_id);
        
        // Calculate progress percentage (include in-progress steps so line connects)
        // The line should extend to the highest completed or in-progress step
        $progress_percent = 0;
        if ($progress['order_placed'] === 'completed' || $progress['order_placed'] === 'in_progress') {
            $progress_percent = 0;
        }
        // Extend line to in-progress or completed steps
        if ($progress['ocular_visit'] === 'completed' || $progress['ocular_visit'] === 'in_progress') {
            $progress_percent = 25;
        }
        if ($progress['in_fabrication'] === 'completed' || $progress['in_fabrication'] === 'in_progress') {
            $progress_percent = 50;
        }
        if ($progress['installed'] === 'completed' || $progress['installed'] === 'in_progress') {
            $progress_percent = 75;
        }
        if ($progress['completed'] === 'completed' || $progress['completed'] === 'in_progress') {
            $progress_percent = 100;
        }
        
        // Ensure line extends fully to in-progress steps by checking if previous steps are completed
        // If a step is in progress, all previous steps should be completed for proper line display
        if ($progress['installed'] === 'in_progress') {
            // If installed is in progress, fabrication and ocular should be completed
            if ($progress['in_fabrication'] !== 'completed') $progress['in_fabrication'] = 'completed';
            if ($progress['ocular_visit'] !== 'completed') $progress['ocular_visit'] = 'completed';
            $progress_percent = 75; // Ensure line extends to installed step
        }
        if ($progress['completed'] === 'in_progress') {
            // If completed is in progress, all previous should be completed
            if ($progress['installed'] !== 'completed') $progress['installed'] = 'completed';
            if ($progress['in_fabrication'] !== 'completed') $progress['in_fabrication'] = 'completed';
            if ($progress['ocular_visit'] !== 'completed') $progress['ocular_visit'] = 'completed';
            $progress_percent = 100; // Ensure line extends to completed step
        }
        if ($progress['in_fabrication'] === 'in_progress') {
            // If fabrication is in progress, ocular should be completed
            if ($progress['ocular_visit'] !== 'completed') $progress['ocular_visit'] = 'completed';
            $progress_percent = 50; // Ensure line extends to fabrication step
        }
        
        // Check if any step is in progress (for progress bar color)
        $has_in_progress = false;
        foreach ($progress as $step_status) {
            if ($step_status === 'in_progress') {
                $has_in_progress = true;
                break;
            }
        }
        
        // Format dates for display
        $formatted_dates = [
            'ocular_date' => $order->OcularDate ? date('M j, Y', strtotime($order->OcularDate)) : null,
            'fabrication_date' => $order->FabricationDate ? date('M j, Y', strtotime($order->FabricationDate)) : null,
            'installation_date' => $order->InstallationDate ? date('M j, Y', strtotime($order->InstallationDate)) : null,
            'estimated_delivery' => $order->EstimatedDelivery ? date('M j, Y', strtotime($order->EstimatedDelivery)) : null
        ];
        
        echo json_encode([
            'success' => true,
            'order_status' => $order->Status,
            'progress' => $progress,
            'progress_percent' => $progress_percent,
            'has_in_progress' => $has_in_progress,
            'dates' => $formatted_dates,
            'order_date' => $order->OrderDate ? date('M j, Y', strtotime($order->OrderDate)) : null,
            'order_time' => $order->OrderDate ? date('g:i A', strtotime($order->OrderDate)) : null
        ]);
    }

    public function terms_order()
    {
        $data['title'] = "Glassify - Terms of Ordering";
        $this->load->view('includes/header', $data);
        $this->load->view('shop/terms_order', $data);
        $this->load->view('includes/footer');
    }

    public function waiting_order()
    {
        // Check if this is a POST request (order creation from checkout or ewallet)
        if ($this->input->method() === 'post') {
            $this->load->model('Order_model');
            $this->load->database();
            
            // Get UserID from session (stored as 'customer_id')
            $user_id = $this->session->userdata('customer_id');
            if (!$user_id) {
                redirect('login');
                return;
            }
            
            // Get or create Customer_ID from customer table
            // The session stores UserID, but order table needs Customer_ID
            $this->db->select('Customer_ID');
            $this->db->from('customer');
            $this->db->where('UserID', $user_id);
            $customer = $this->db->get()->row();
            
            if ($customer) {
                $customer_id = (int)$customer->Customer_ID; // Ensure integer type
            } else {
                // Customer record doesn't exist - create it
                $customer_data = [
                    'UserID' => (int)$user_id
                ];
                $this->db->insert('customer', $customer_data);
                $customer_id = (int)$this->db->insert_id();
                
                if (!$customer_id || $customer_id <= 0) {
                    log_message('error', 'Failed to create customer record for UserID: ' . $user_id);
                    $this->session->set_flashdata('error', 'Failed to create customer record. Please contact support.');
                    redirect('checkout');
                    return;
                }
            }
            
            // Get form data (from checkout form or ewallet form)
            $address = $this->input->post('address') ?: '';
            $city = $this->input->post('city') ?: '';
            $province = $this->input->post('province') ?: '';
            
            // Build delivery address
            $delivery_address = trim($address);
            if ($city) $delivery_address .= ($delivery_address ? ', ' : '') . $city;
            if ($province) $delivery_address .= ($delivery_address ? ', ' : '') . $province;
            
            if (empty($delivery_address)) {
                $delivery_address = $this->input->post('delivery_address') ?: 'N/A';
            }
            
            // Get SalesRep_ID (assign to first available sales rep or default)
            $this->db->select('UserID');
            $this->db->from('user');
            $this->db->where('Role', 'Sales Representative');
            $this->db->limit(1);
            $sales_rep = $this->db->get()->row();
            $sales_rep_id = $sales_rep ? $sales_rep->UserID : 1; // Default to 1 if none found
            
            // Get total amount from form, session, or customization
            $total_amount = 0;
            if ($this->input->post('total_amount')) {
                $total_amount = floatval(str_replace(',', '', $this->input->post('total_amount')));
            } elseif ($this->session->userdata('order_total')) {
                $total_amount = floatval($this->session->userdata('order_total'));
            } else {
                // Get from unified customization table (most recent for this customer)
                $this->db->select('EstimatePrice');
                $this->db->from('customization');
                $this->db->where('Customer_ID', $customer_id);
                $this->db->order_by('CreatedAt', 'DESC');
                $this->db->limit(1);
                $custom = $this->db->get()->row();
                
                if ($custom) {
                    $total_amount = floatval($custom->TotalQuotation ?? $custom->EstimatePrice ?? 0);
                }
            }
            
            // Validate Customer_ID exists in customer table before creating order
            $this->db->select('Customer_ID');
            $this->db->from('customer');
            $this->db->where('Customer_ID', $customer_id);
            $valid_customer = $this->db->get()->row();
            
            if (!$valid_customer) {
                // Customer_ID doesn't exist - this shouldn't happen, but create it anyway
                log_message('error', 'Customer_ID ' . $customer_id . ' does not exist in customer table. Attempting to create...');
                $customer_data = [
                    'UserID' => $user_id
                ];
                $this->db->insert('customer', $customer_data);
                $customer_id = $this->db->insert_id();
                
                if (!$customer_id) {
                    $this->session->set_flashdata('error', 'Failed to create customer record. Please contact support.');
                    redirect('checkout');
                    return;
                }
            }
            
            // Get preferred installation date
            $preferred_installation_date = $this->input->post('preferred_installation_date') ?: null;
            
            // Combine note and preferred installation date in SpecialInstructions
            $special_instructions = [];
            if ($this->input->post('note')) {
                $special_instructions[] = trim($this->input->post('note'));
            }
            if ($preferred_installation_date) {
                $formatted_date = date('F j, Y', strtotime($preferred_installation_date));
                $special_instructions[] = 'Preferred Installation Date: ' . $formatted_date;
            }
            $special_instructions_text = !empty($special_instructions) ? implode("\n", $special_instructions) : null;
            
            // Create order data
            $order_data = [
                'Customer_ID' => (int)$customer_id, // Ensure it's an integer
                'SalesRep_ID' => (int)$sales_rep_id,
                'TotalAmount' => $total_amount,
                'DeliveryAddress' => $delivery_address,
                'Status' => 'Pending Review',
                'PaymentStatus' => ($this->input->post('payment_method') === 'ewallet' && $this->input->post('receipt')) ? 'Pending' : 'Pending',
                'SpecialInstructions' => $special_instructions_text,
                'PreferredInstallationDate' => !empty($preferred_installation_date) ? $preferred_installation_date : null
            ];
            
            // Handle file upload (payment receipt for ewallet)
            if ($this->input->post('payment_method') === 'ewallet' && isset($_FILES['receipt'])) {
                $upload_path = './uploads/payments/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
                
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'jpg|jpeg|png|pdf';
                $config['max_size'] = 5120; // 5MB
                
                $this->load->library('upload', $config);
                
                if ($this->upload->do_upload('receipt')) {
                    $upload_data = $this->upload->data();
                    $order_data['QuotationPDFUrl'] = 'uploads/payments/' . $upload_data['file_name'];
                }
            }
            
            // Check inventory availability before creating order
            // Get product IDs from cart items (since we're placing order from cart)
            $this->load->model('Cart_model');
            $cart_items = $this->Cart_model->get_cart_items($customer_id);
            
            // Check inventory for each product in cart
            if (!empty($cart_items)) {
                $this->load->model('Inventory_model');
                $all_missing = [];
                
                foreach ($cart_items as $item) {
                    if (!empty($item->Product_ID)) {
                        $inventory_check = $this->Inventory_model->can_manufacture_product($item->Product_ID, $item->Quantity);
                        
                        if (!$inventory_check['can_manufacture']) {
                            $missing_items = array_map(function($m) {
                                return $m['ItemName'];
                            }, $inventory_check['missing_materials']);
                            $all_missing = array_merge($all_missing, $missing_items);
                        }
                    }
                }
                
                if (!empty($all_missing)) {
                    // Materials are out of stock - prevent order creation
                    $unique_missing = array_unique($all_missing);
                    $error_message = "Cannot place order: The following materials are out of stock: " . implode(', ', $unique_missing) . ". Please contact sales for assistance.";
                    $this->session->set_flashdata('error', $error_message);
                    redirect('checkout');
                    return;
                }
            }
            
            // Create order (this will also update customization record with OrderID, Address, Date)
            $order_result = $this->Order_model->create_order($order_data);
            
            // Check if order creation returned an error
            if (is_array($order_result) && isset($order_result['error'])) {
                $this->session->set_flashdata('error', $order_result['error'] . ': ' . implode(', ', $order_result['missing_materials']));
                redirect('checkout');
                return;
            }
            
            if ($order_result && !is_array($order_result)) {
                // Clear session data
                $this->session->unset_userdata('order_total');
                $this->session->unset_userdata('buy_now_customization_id');
                
                // Redirect to waiting order page
                $data['title'] = "Glassify - Waiting for Order Approval";
                $data['order_id'] = $order_result;
                $this->load->view('includes/header', $data);
                $this->load->view('shop/WaitingOrder', $data);
                $this->load->view('includes/footer');
            } else {
                // Error creating order
                $this->session->set_flashdata('error', 'Failed to create order. Please try again.');
                redirect('checkout');
            }
        } else {
            // GET request - just show the page
            $data['title'] = "Glassify - Waiting for Order Approval";
            $this->load->view('includes/header', $data);
            $this->load->view('shop/WaitingOrder', $data);
            $this->load->view('includes/footer');
        }
    }

    /**
     * Place Order - AJAX endpoint
     */
    public function place_order()
    {
        // Set JSON response header
        header('Content-Type: application/json');

        // Initialize log array for debugging
        $debug_log = [];
        $debug_log['timestamp'] = date('Y-m-d H:i:s');

        // Check if user is logged in
        $customer_id = $this->session->userdata('customer_id');
        $debug_log['customer_id'] = $customer_id;
        
        if (!$customer_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Please log in to place an order.'
            ]);
            return;
        }

        // Get POST data
        $payment_method = $this->input->post('payment_method');
        $terms_accepted = $this->input->post('terms_accepted');
        $selected_cart_ids = $this->input->post('selected_cart_ids');
        
        $debug_log['payment_method'] = $payment_method;
        $debug_log['terms_accepted'] = $terms_accepted;
        $debug_log['selected_cart_ids'] = $selected_cart_ids;

        // Validate payment method
        if (empty($payment_method)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Please select a payment method.'
            ]);
            return;
        }

        // Validate terms acceptance
        if ($terms_accepted !== 'true' && $terms_accepted !== '1') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Please accept the Terms and Conditions.'
            ]);
            return;
        }

        // Load models
        $this->load->model('Cart_model');
        $this->load->model('Order_model');
        $this->load->model('User_model');

        // Get cart items
        $cart_items = $this->Cart_model->get_cart_items($customer_id);
        $debug_log['cart_items_count_before_filter'] = count($cart_items);
        $debug_log['cart_items_raw'] = array_map(function($item) {
            return [
                'Cart_ID' => $item->Cart_ID ?? 'N/A',
                'Product_ID' => $item->Product_ID ?? 'N/A',
                'Quantity' => $item->Quantity ?? 0,
                'EstimatePrice' => $item->EstimatePrice ?? 'N/A',
                'Price' => $item->Price ?? 'N/A'
            ];
        }, $cart_items);
        
        // Filter to only selected items if IDs provided
        if (!empty($selected_cart_ids)) {
            $selected_ids = array_filter(array_map('intval', explode(',', $selected_cart_ids)));
            $debug_log['selected_ids_parsed'] = $selected_ids;
            
            if (!empty($selected_ids)) {
                $cart_items = array_filter($cart_items, function($item) use ($selected_ids) {
                    return in_array($item->Cart_ID, $selected_ids);
                });
                // Re-index array
                $cart_items = array_values($cart_items);
            }
        }
        
        $debug_log['cart_items_count_after_filter'] = count($cart_items);

        if (empty($cart_items)) {
            $debug_log['error'] = 'No items after filter';
            log_message('error', 'Place Order Debug: ' . json_encode($debug_log));
            
            echo json_encode([
                'status' => 'error',
                'message' => 'No items selected for checkout.',
                'debug' => $debug_log
            ]);
            return;
        }

        // Calculate totals for selected items only
        $subtotal = 0;
        $total_items = 0;
        foreach ($cart_items as $item) {
            $price = $item->EstimatePrice ?? $item->Price ?? 0;
            $debug_log['item_prices'][] = [
                'Cart_ID' => $item->Cart_ID,
                'EstimatePrice' => $item->EstimatePrice ?? 'null',
                'Price' => $item->Price ?? 'null',
                'used_price' => $price,
                'Quantity' => $item->Quantity
            ];
            $subtotal += $price * $item->Quantity;
            $total_items += $item->Quantity;
        }
        $shipping = $total_items * 25;
        $handling = $total_items * 10;
        $total_amount = $subtotal + $shipping + $handling;
        
        $debug_log['calculated_totals'] = [
            'subtotal' => $subtotal,
            'total_items' => $total_items,
            'shipping' => $shipping,
            'handling' => $handling,
            'total_amount' => $total_amount
        ];

        // Get shipping address
        $addresses = $this->User_model->get_addresses($customer_id);
        $shipping_address = '';
        if (isset($addresses['Shipping']) && $addresses['Shipping']) {
            $addr = $addresses['Shipping'];
            $shipping_address = implode(', ', array_filter([
                $addr->AddressLine,
                $addr->City,
                $addr->Province,
                $addr->Country,
                $addr->ZipCode
            ]));
        }

        // Get form data for shipping info update (optional)
        $firstname = $this->input->post('firstname');
        $middlename = $this->input->post('middlename'); // Middle name from form
        $lastname = $this->input->post('lastname');
        $address = $this->input->post('address');
        $city = $this->input->post('city');
        $province = $this->input->post('province');
        $country = $this->input->post('country');
        $zipcode = $this->input->post('zipcode');
        $note = $this->input->post('note');
        $preferred_installation_date = $this->input->post('preferred_installation_date');

        // Build delivery address from form if provided
        if (!empty($address)) {
            $shipping_address = implode(', ', array_filter([
                $address, $city, $province, $country, $zipcode
            ]));
        }

        // Get default sales rep
        $sales_rep_id = $this->Order_model->get_default_sales_rep();

        // Prepare special instructions (combine note and preferred installation date)
        $special_instructions = [];
        if (!empty($note)) {
            $special_instructions[] = trim($note);
        }
        if (!empty($preferred_installation_date)) {
            $formatted_date = date('F j, Y', strtotime($preferred_installation_date));
            $special_instructions[] = 'Preferred Installation Date: ' . $formatted_date;
        }
        $special_instructions_text = !empty($special_instructions) ? implode("\n", $special_instructions) : null;

        // Prepare order data
        $order_data = [
            'Customer_ID' => $customer_id,
            'SalesRep_ID' => $sales_rep_id,
            'TotalAmount' => $total_amount,
            'Status' => 'Pending Review',
            'PaymentStatus' => 'Pending',
            'PaymentMethod' => $payment_method, // Explicitly set PaymentMethod
            'DeliveryAddress' => $shipping_address,
            'SpecialInstructions' => $special_instructions_text,
            'PreferredInstallationDate' => !empty($preferred_installation_date) ? $preferred_installation_date : null
        ];

        // For E-Wallet: Store order data in session and redirect to payment page
        // Don't create order yet - wait for payment submission
        if ($payment_method === 'E-Wallet') {
            // Validate we have actual items with value
            if ($total_items <= 0 || $total_amount <= 0) {
                $debug_log['error'] = 'Invalid order amount';
                log_message('error', 'Place Order Debug: ' . json_encode($debug_log));
                
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid order amount. Please try again.',
                    'debug' => $debug_log
                ]);
                return;
            }

            // Prepare summary data
            $summary_data = [
                'items' => $total_items,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'handling' => $handling,
                'total' => $total_amount
            ];
            
            $debug_log['summary_to_store'] = $summary_data;

            // Store pending order data in session (cart items remain intact)
            $this->session->set_userdata('pending_order_data', $order_data);
            $this->session->set_userdata('pending_cart_ids', $selected_cart_ids);
            $this->session->set_userdata('pending_order_summary', $summary_data);
            $this->session->set_userdata('last_payment_method', $payment_method);
            
            // Verify session was stored
            $stored_summary = $this->session->userdata('pending_order_summary');
            $debug_log['session_verification'] = [
                'stored_successfully' => !empty($stored_summary),
                'stored_data' => $stored_summary
            ];
            
            // Log to file
            log_message('debug', 'E-Wallet Order - Session stored: ' . json_encode($debug_log));

            echo json_encode([
                'status' => 'success',
                'message' => 'Redirecting to payment...',
                'redirect_url' => base_url('paying'),
                'debug' => $debug_log
            ]);
            return;
        }

        // For COD: Create order immediately
        $order_id = $this->Order_model->create_order($order_data);

        if (!$order_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create order. Please try again.'
            ]);
            return;
        }

        // Save order customizations from selected cart items only
        $this->Order_model->save_order_customizations($order_id, $cart_items);

        // Store order info in session for complete page
        $this->session->set_userdata([
            'last_order_id' => $order_id,
            'last_order_total' => $total_amount,
            'last_payment_method' => $payment_method
        ]);

        // Remove only the selected items from cart (not entire cart)
        if (!empty($selected_cart_ids)) {
            $selected_ids = array_filter(array_map('intval', explode(',', $selected_cart_ids)));
            foreach ($selected_ids as $cart_id) {
                $this->Cart_model->remove_item($cart_id);
            }
        } else {
            // If no selection specified, clear entire cart (fallback)
            $this->Cart_model->clear_cart($customer_id);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Order placed successfully!',
            'order_id' => $order_id,
            'redirect_url' => base_url('complete')
        ]);
    }

    public function list_products()
    {
        $data['title'] = "Glassify - My Purchases";

        // Check if user is logged in and is a customer
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Customer') {
            // Set cache control headers to prevent back button access
            $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
            $this->output->set_header('Pragma: no-cache');
            $this->output->set_header('Expires: 0');
            redirect('login');
            return;
        }
        
        $customer_id = $this->session->userdata('customer_id');
        
        // Set cache control headers for customer pages
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');

        // Load Order model
        $this->load->model('Order_model');

        // Get customer's order items (purchases) from database
        $data['order_items'] = $this->Order_model->get_customer_order_items($customer_id);

        $this->load->view('includes/header', $data);
        $this->load->view('shop/list_product', $data);
        $this->load->view('includes/footer');
    }
    
}