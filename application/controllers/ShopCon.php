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
    $this->load->model('Inventory_model');

    // Load products from database
    $products = $this->Product_model->get_products();
    
    // Update product status based on materials for each product
    foreach ($products as $product) {
        $this->Inventory_model->update_product_status_from_materials($product->Product_ID);
    }
    
    // Reload products to get updated status
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
        
        // Get all addresses to find default or first one
        $all_addresses = $this->User_model->get_user_addresses($userID);
        
        // Find default address first, then shipping, then first available
        $default_address = null;
        $shipping_address = null;
        
        foreach ($all_addresses as $addr) {
            if ($addr->IsDefault == 1) {
                $default_address = $addr;
                break;
            }
            if ($addr->AddressType === 'Shipping' && !$shipping_address) {
                $shipping_address = $addr;
            }
        }
        
        // Use default if found, otherwise shipping, otherwise first address
        $selected_address = $default_address ?: $shipping_address ?: (!empty($all_addresses) ? $all_addresses[0] : null);
        
        if ($selected_address) {
            $data['addresses']['Shipping'] = $selected_address;
        }
        
        // Also get addresses by type for backward compatibility
        $data['addresses'] = array_merge($data['addresses'], $this->User_model->get_addresses($userID));
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
                'UnitHouseNumber' => '',
                'Street' => '',
                'Subdivision' => '',
                'Barangay' => '',
                'City' => '',
                'Province' => '',
                'Region' => '',
                'Country' => 'Philippines',
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
        // Get order summary from session (set when order was placed)
        $pending_summary = $this->session->userdata('last_order_summary');
        $pending_cart_ids = $this->session->userdata('last_selected_cart_ids');
        
        // If no summary in session, try to get from selected cart IDs
        if (!$pending_summary && $pending_cart_ids) {
            $this->load->model('Cart_model');
            $customer_id = $this->session->userdata('customer_id');
            
            if ($customer_id) {
                // Parse selected cart IDs
                $selected_ids = explode(',', $pending_cart_ids);
                $selected_ids = array_map('trim', $selected_ids);
                $selected_ids = array_filter($selected_ids);
                
                // Get cart items
                $cart_items = $this->Cart_model->get_cart_items($customer_id);
                
                // Filter by selected IDs
                $filtered_items = array_filter($cart_items, function($item) use ($selected_ids) {
                    return in_array($item->CartID, $selected_ids);
                });
                
                // Calculate summary
                $subtotal = 0;
                $total_items = 0;
                foreach ($filtered_items as $item) {
                    $price = $item->EstimatePrice ?? $item->Price ?? 0;
                    $subtotal += $price * $item->Quantity;
                    $total_items += $item->Quantity;
                }
                $shipping = $total_items * 25;
                $handling = $total_items * 10;
                $total_amount = $subtotal + $shipping + $handling;
                
                $pending_summary = [
                    'items' => $total_items,
                    'subtotal' => $subtotal,
                    'shipping' => $shipping,
                    'handling' => $handling,
                    'total' => $total_amount
                ];
            }
        }
        
        // Default values if no summary available
        if (!$pending_summary) {
            $pending_summary = [
                'items' => 0,
                'subtotal' => 0,
                'shipping' => 0,
                'handling' => 0,
                'total' => 0
            ];
        }
        
        $data['title'] = "Glassify - Payment";
        $data['pending_summary'] = $pending_summary;
        $data['pending_cart_ids'] = $pending_cart_ids ?: '';
        $this->load->view('includes/header', $data);
        $this->load->view('shop/ewallet', $data);
        $this->load->view('includes/footer');
    }
    
    /**
     * Handle e-wallet payment receipt submission
     */
    public function submit_ewallet_payment()
    {
        // Check if user is logged in
        if (!$this->session->userdata('is_logged_in')) {
            redirect('login');
            return;
        }
        
        // Get order ID from session
        $order_id = $this->session->userdata('last_order_id');
        if (!$order_id) {
            $this->session->set_flashdata('error', 'No order found. Please place an order first.');
            redirect('checkout');
            return;
        }
        
        // Load models
        $this->load->model('Order_model');
        $this->load->database();
        
        // Validate file upload
        if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'Please attach a payment receipt.');
            redirect(base_url('paying'));
            return;
        }
        
        // Validate terms acceptance - checkbox sends 'on' when checked
        $terms = $this->input->post('terms');
        if (!$terms || $terms !== 'on') {
            $this->session->set_flashdata('error', 'Please agree to the Terms and Conditions.');
            redirect(base_url('paying'));
            return;
        }
        
        // Configure file upload
        $upload_path = './uploads/payments/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'jpg|jpeg|png|pdf';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = TRUE;
        
        $this->load->library('upload', $config);
        
        // Upload receipt
        if (!$this->upload->do_upload('receipt')) {
            $error = $this->upload->display_errors();
            $this->session->set_flashdata('error', 'File upload failed: ' . $error);
            redirect(base_url('paying'));
            return;
        }
        
        $upload_data = $this->upload->data();
        $receipt_path = 'uploads/payments/' . $upload_data['file_name'];
        
        // Get order details
        $order = $this->Order_model->get_order_with_customer($order_id);
        if (!$order) {
            $this->session->set_flashdata('error', 'Order not found.');
            redirect(base_url('paying'));
            return;
        }
        
        // Save payment receipt using Order_model method
        $amount = $order->TotalAmount ?? 0;
        $this->Order_model->save_payment_receipt($order_id, $receipt_path, $amount);
        
        // Update order payment method
        $this->db->where('OrderID', $order_id);
        $this->db->update('`order`', [
            'PaymentMethod' => 'E-Wallet',
            'PaymentStatus' => 'Pending'
        ]);
        
        // Redirect to complete page
        redirect(base_url('complete'));
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
            
            // Get customer shipping address
            $customer_id = $this->session->userdata('customer_id');
            if ($customer_id) {
                $addresses = $this->User_model->get_addresses($customer_id);
                $data['shipping_address'] = $addresses['Shipping'] ?? null;
                $data['billing_address'] = $addresses['Billing'] ?? null;
                $data['user'] = $this->User_model->get_by_id($customer_id);
            }
        }
        
        $this->load->view('includes/header', $data);
        $this->load->view('shop/order_complete', $data);
        $this->load->view('includes/footer');
    }

    public function wishlist()
    {
        $data['title'] = "Glassify - Wishlist";
        $this->load->view('includes/header', $data);
        $this->load->view('shop/wishlist', $data);
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

                // Get progress steps based on status and order_id (to check appointments table)
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
     * AJAX endpoint for real-time order progress updates
     * Used by order tracking page to poll for appointment updates
     */
    public function get_order_progress_ajax()
    {
        $order_id = $this->input->get('order_id');
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            return;
        }
        
        $this->load->model('Order_model');
        
        // Get order details
        $order = $this->Order_model->get_order_with_customer($order_id);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Get progress with order_id to check appointments table
        $progress = $this->Order_model->get_order_progress($order->Status, $order_id);
        
        // Calculate progress percentage
        $completed_steps = 0;
        $total_steps = 5; // order_placed, ocular_visit, in_fabrication, installed, completed
        
        foreach ($progress as $step => $status) {
            if ($status === 'completed') {
                $completed_steps++;
            }
        }
        
        $progress_percent = ($completed_steps / $total_steps) * 100;
        
        // Get dates from order
        $dates = [
            'order_placed' => $order->OrderDate ?? null,
            'ocular_visit' => $order->OcularDate ?? null,
            'fabrication' => $order->FabricationDate ?? null,
            'installation' => $order->InstallationDate ?? null,
            'estimated_delivery' => $order->EstimatedDelivery ?? null
        ];
        
        // Check if any step is in progress
        $has_in_progress = false;
        foreach ($progress as $step => $status) {
            if ($status === 'in_progress') {
                $has_in_progress = true;
                break;
            }
        }
        
        echo json_encode([
            'success' => true,
            'progress' => $progress,
            'progress_percent' => $progress_percent,
            'dates' => $dates,
            'order_status' => $order->Status,
            'has_in_progress' => $has_in_progress
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
                // Get from category-specific customization tables (most recent for this customer)
                $this->load->model('Cart_model');
                $customization_tables = [
                    'mirror_customization',
                    'shower_enclosure_customization',
                    'aluminum_doors_customization',
                    'aluminum_bathroom_doors_customization'
                ];
                
                $custom = null;
                foreach ($customization_tables as $table) {
                    $this->db->select('EstimatePrice');
                    $this->db->from($table);
                    $this->db->where('Customer_ID', $customer_id);
                    $this->db->order_by('Created_Date', 'DESC');
                    $this->db->limit(1);
                    $result = $this->db->get()->row();
                    if ($result) {
                        $custom = $result;
                        break;
                    }
                }
                
                // Fallback to old customization table
                if (!$custom) {
                    $this->db->select('TotalQuotation, EstimatePrice');
                    $this->db->from('customization');
                    $this->db->where('Customer_ID', $customer_id);
                    $this->db->where('OrderID IS NULL', null, false);
                    $this->db->order_by('Created_Date', 'DESC');
                    $this->db->limit(1);
                    $custom = $this->db->get()->row();
                }
                
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
                $special_instructions[] = 'Note: ' . $this->input->post('note');
            }
            if ($preferred_installation_date) {
                $special_instructions[] = 'Preferred Installation Date: ' . date('F j, Y', strtotime($preferred_installation_date));
            }
            $special_instructions_text = !empty($special_instructions) ? implode(' | ', $special_instructions) : null;
            
            // Create order data
            $order_data = [
                'Customer_ID' => (int)$customer_id, // Ensure it's an integer
                'SalesRep_ID' => (int)$sales_rep_id,
                'TotalAmount' => $total_amount,
                'DeliveryAddress' => $delivery_address,
                'Status' => 'Pending Review', // Set to 'Pending Review' for new orders
                'PaymentStatus' => ($this->input->post('payment_method') === 'ewallet' && $this->input->post('receipt')) ? 'Pending' : 'Pending',
                'SpecialInstructions' => $special_instructions_text
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
        // Disable error display and set JSON header immediately
        @ini_set('display_errors', 0);
        header('Content-Type: application/json');
        
        // Prevent any output before JSON (only if output buffering is active)
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        try {
            // Check if user is logged in
            $customer_id = $this->session->userdata('customer_id');
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

            // Get selected cart IDs if provided
            $selected_cart_ids = $this->input->post('selected_cart_ids');
            
            // Get cart items
            $cart_items = $this->Cart_model->get_cart_items($customer_id);
            if (empty($cart_items)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Your cart is empty.'
                ]);
                return;
            }
            
            // Filter by selected cart IDs if provided
            if ($selected_cart_ids) {
                $selected_ids = explode(',', $selected_cart_ids);
                $selected_ids = array_map('trim', $selected_ids);
                $selected_ids = array_filter($selected_ids);
                $selected_ids = array_map('intval', $selected_ids); // Convert to integers for comparison
                
                if (!empty($selected_ids)) {
                    $cart_items_before = count($cart_items);
                    $cart_items = array_filter($cart_items, function($item) use ($selected_ids) {
                        // Cart_ID is the correct field name (with underscore)
                        return in_array((int)$item->Cart_ID, $selected_ids);
                    });
                    // Re-index array after filtering
                    $cart_items = array_values($cart_items);
                    
                    // Debug logging
                    log_message('debug', 'place_order: Filtered cart items. Before: ' . $cart_items_before . ', After: ' . count($cart_items) . ', Selected IDs: ' . implode(',', $selected_ids));
                }
            }
            
            if (empty($cart_items)) {
                $debug_info = [
                    'selected_cart_ids' => $selected_cart_ids,
                    'total_cart_items' => count($this->Cart_model->get_cart_items($customer_id)),
                    'selected_ids_parsed' => isset($selected_ids) ? $selected_ids : []
                ];
                
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No items selected for order. Please ensure items are selected in your cart.',
                    'debug' => ENVIRONMENT === 'development' ? $debug_info : null
                ]);
                return;
            }

            // Calculate totals
            $subtotal = 0;
            $total_items = 0;
            foreach ($cart_items as $item) {
                $price = $item->EstimatePrice ?? $item->Price ?? 0;
                $subtotal += $price * $item->Quantity;
                $total_items += $item->Quantity;
            }
            $shipping = $total_items * 25;
            $handling = $total_items * 10;
            $total_amount = $subtotal + $shipping + $handling;

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

            // Combine note and preferred installation date in SpecialInstructions
            $special_instructions = [];
            if ($note) {
                $special_instructions[] = 'Note: ' . $note;
            }
            if ($preferred_installation_date) {
                $special_instructions[] = 'Preferred Installation Date: ' . date('F j, Y', strtotime($preferred_installation_date));
            }
            $special_instructions_text = !empty($special_instructions) ? implode(' | ', $special_instructions) : null;

            // Get default sales rep
            $sales_rep_id = $this->Order_model->get_default_sales_rep();

            // Prepare order data
            $order_data = [
                'Customer_ID' => $customer_id,
                'SalesRep_ID' => $sales_rep_id,
                'TotalAmount' => $total_amount,
                'Status' => 'Pending Review', // Set to 'Pending Review' for new orders
                'PaymentStatus' => 'Pending',
                'DeliveryAddress' => $shipping_address,
                'SpecialInstructions' => $special_instructions_text
            ];

            // Create order
            $order_id = $this->Order_model->create_order($order_data);

            if (!$order_id) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create order. Please try again.'
                ]);
                return;
            }

            // Save order customizations from cart items
            $this->Order_model->save_order_customizations($order_id, $cart_items);

            // Calculate summary for ewallet page
            $order_summary = [
                'items' => $total_items,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'handling' => $handling,
                'total' => $total_amount
            ];
            
            // Store order info in session for payment/complete page
            $this->session->set_userdata([
                'last_order_id' => $order_id,
                'last_order_total' => $total_amount,
                'last_payment_method' => $payment_method,
                'last_order_summary' => $order_summary,
                'last_selected_cart_ids' => $this->input->post('selected_cart_ids') ?: ''
            ]);

            // Clear cart after successful order
            $this->Cart_model->clear_cart($customer_id);

            // Determine redirect URL based on payment method
            $redirect_url = ($payment_method === 'E-Wallet') 
                ? base_url('paying') 
                : base_url('complete');

            echo json_encode([
                'status' => 'success',
                'message' => 'Order placed successfully!',
                'order_id' => $order_id,
                'redirect_url' => $redirect_url
            ]);
        } catch (Exception $e) {
            log_message('error', 'ShopCon->place_order: Exception - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while placing your order. Please try again.',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : null
            ]);
        } catch (Error $e) {
            log_message('error', 'ShopCon->place_order: Fatal Error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'A fatal error occurred. Please try again.',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : null
            ]);
        }
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

        // Ensure customer_id is an integer
        $customer_id = (int)$customer_id;

        // Load Order model
        $this->load->model('Order_model');

        // Get customer's order items (purchases) from database
        $data['order_items'] = $this->Order_model->get_customer_order_items($customer_id);

        $this->load->view('includes/header', $data);
        $this->load->view('shop/list_product', $data);
        $this->load->view('includes/footer');
    }
    
}