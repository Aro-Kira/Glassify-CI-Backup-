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

    // Get all products first to update their status
    $allProducts = $this->Product_model->get_all_products();
    
    // Update product status based on materials for each product
    foreach ($allProducts as $product) {
        $this->Inventory_model->update_product_status_from_materials($product->Product_ID);
    }
    
    // Load products that customers can see (In Stock or Low Stock)
    // This matches what admin shows
    $products = $this->Product_model->get_products();
    
    // Fetch tags and series for each product
    foreach ($products as $product) {
        // Get tags for this product
        $product->tags = [];
        if ($this->db->table_exists('product_tag_prices')) {
            $this->db->distinct();
            $this->db->select('TagName');
            $this->db->where('Product_ID', $product->Product_ID);
            $tagsResult = $this->db->get('product_tag_prices')->result();
            foreach ($tagsResult as $tag) {
                $product->tags[] = $tag->TagName;
            }
        }
        
        // Get series for this product
        $product->series = [];
        if ($this->db->table_exists('product_series')) {
            $this->db->select('SeriesName');
            $this->db->where('Product_ID', $product->Product_ID);
            $seriesResult = $this->db->get('product_series')->result();
            foreach ($seriesResult as $series) {
                $product->series[] = $series->SeriesName;
            }
        }
    }
    
    $data['products'] = $products;

    $this->load->view('includes/header', $data);
    $this->load->view('shop/products', $data);  // now has $products available
    $this->load->view('includes/footer');
}



public function product_2d()
{
    $this->load->model('Product_model');
    
    // Ensure session is properly initialized - don't clear it on page load
    // The session library is already loaded in __construct()
    // Just ensure we're not doing anything that would destroy the session

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

    // Get customization field configurations based on product's category and subcategory
    // These are the field definitions (not customer selections)
    $customizationFields = [];
    
    // Load field configurations from JavaScript (we'll create an API endpoint for this)
    // For now, we'll need to load them based on category/subcategory
    // The field configurations are stored in localStorage on admin side, but we need them on server
    // We'll create a helper function or load from a config file
    
    // Build field key from category and subcategory
    $category = $product->Category ?? '';
    $subcategory = $product->Subcategory ?? '';
    
    if ($category && $subcategory) {
        // Map category to prefix
        $prefixMap = [
            'Windows' => 'Windows',
            'Doors' => 'Doors',
            'Glass Partitions & Enclosures' => 'Partitions',
            'Mirrors & Specialty Glass' => 'Specialty',
            'Commercial & Exterior' => 'Commercial'
        ];
        
        $prefix = $prefixMap[$category] ?? '';
        $fieldKey = $prefix ? "{$prefix}_{$subcategory}" : $subcategory;
        
        // Load customization fields from localStorage equivalent
        // For now, we'll pass the key to JavaScript and let it load from localStorage
        // Or we can create a database table to store field configurations
        $customizationFields = null; // Will be loaded by JavaScript from localStorage
    }

    // Get tag prices, images, and visual configs for this product
    $tagPrices = [];
    $tagImages = [];
    $tagVisualConfigs = [];
    if ($this->db->table_exists('product_tag_prices')) {
        $this->db->where('Product_ID', $product->Product_ID);
        $tagPricesResult = $this->db->get('product_tag_prices')->result();
        foreach ($tagPricesResult as $tagPrice) {
            if (!isset($tagPrices[$tagPrice->FieldID])) {
                $tagPrices[$tagPrice->FieldID] = [];
            }
            $tagPrices[$tagPrice->FieldID][$tagPrice->TagName] = floatval($tagPrice->Price);
            
            // Get tag images
            if (!empty($tagPrice->ImageUrl)) {
                if (!isset($tagImages[$tagPrice->FieldID])) {
                    $tagImages[$tagPrice->FieldID] = [];
                }
                $tagImages[$tagPrice->FieldID][$tagPrice->TagName] = base_url('uploads/tags/' . $tagPrice->ImageUrl);
            }
            
            // Get tag visual configs for Konva.js 2D preview
            if (isset($tagPrice->VisualConfig) && !empty($tagPrice->VisualConfig)) {
                if (!isset($tagVisualConfigs[$tagPrice->FieldID])) {
                    $tagVisualConfigs[$tagPrice->FieldID] = [];
                }
                $decoded = json_decode($tagPrice->VisualConfig, true);
                if ($decoded) {
                    $tagVisualConfigs[$tagPrice->FieldID][$tagPrice->TagName] = $decoded;
                }
            }
        }
    }

    // Get standard series and sizes
    $standardSeries = [];
    if ($this->db->table_exists('product_series') && $this->db->table_exists('product_standard_sizes')) {
        $this->db->where('Product_ID', $product->Product_ID);
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

    // Get product's selected customization options (only selected tags should show on customer side)
    $productSelectedOptions = [];
    if (isset($product->Customization) && !empty($product->Customization)) {
        $decoded = json_decode($product->Customization, true);
        if (is_array($decoded)) {
            $productSelectedOptions = $decoded;
        }
    }

    $data['title'] = "Glassify - 2D Modeling";
    $data['product'] = $product;
    $data['customizationFields'] = $customizationFields; // Will be loaded by JS
    $data['customizationFieldKey'] = isset($fieldKey) ? $fieldKey : null; // Pass key for JS to load
    $data['tagPrices'] = $tagPrices;
    $data['tagImages'] = $tagImages;
    $data['tagVisualConfigs'] = $tagVisualConfigs;
    $data['standardSeries'] = $standardSeries;
    $data['productSelectedOptions'] = $productSelectedOptions; // Selected tags for filtering
    
    // Get recommended products (same as products page - only In Stock or Low Stock)
    $data['recommendations'] = $this->Product_model->get_recommended_products(4);
    
    // Debug logging
    log_message('debug', 'Product 2D - Product ID: ' . $product->Product_ID);
    log_message('debug', 'Product 2D - Category: ' . ($product->Category ?? 'N/A'));
    log_message('debug', 'Product 2D - Subcategory: ' . ($product->Subcategory ?? 'N/A'));
    log_message('debug', 'Product 2D - Field Key: ' . (isset($fieldKey) ? $fieldKey : 'N/A'));
    log_message('debug', 'Product 2D - Tag Prices Count: ' . count($tagPrices));
    log_message('debug', 'Product 2D - Standard Series Count: ' . count($standardSeries));

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
        
        // Get all addresses for the dropdown selector
        $data['all_addresses'] = $all_addresses;
    } else {
        $data['all_addresses'] = [];
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

// Booking page for Site Assessment Orders (same as checkout but without payment forms)
public function booking()
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
    $data['addresses'] = ['Shipping' => null];

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
        
        // Get all addresses for the dropdown selector
        $data['all_addresses'] = $all_addresses;
    } else {
        $data['all_addresses'] = [];
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

    // fallback address
    if (!$data['addresses']['Shipping']) {
        $data['addresses']['Shipping'] = (object)[
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
    
    $data['title'] = "Glassify - Booking";
    $this->load->view('includes/header', $data);
    $this->load->view('shop/booking', $data);
    $this->load->view('includes/footer');
}




    public function ewallet()
    {
        // Check if user is logged in
        if (!$this->session->userdata('is_logged_in')) {
            redirect('login');
            return;
        }

        $data['title'] = "Glassify - Payment";
        
        // Initialize default summary
        $data['pending_summary'] = [
            'items' => 0,
            'subtotal' => 0,
            'shipping' => 0,
            'handling' => 0,
            'total' => 0
        ];
        $data['pending_cart_ids'] = '';
        
        $customer_id = $this->session->userdata('customer_id');
        
        if ($customer_id) {
            // Priority 1: Check if order was just placed (from checkout -> ewallet redirect)
            $last_order_id = $this->session->userdata('last_order_id');
            
            if ($last_order_id) {
                // Get order summary from the recently placed order
                $this->load->model('Order_model');
                $data['pending_summary'] = $this->Order_model->calculate_order_summary($last_order_id);
            } else {
                // Priority 2: Check for buy now customization ID (from session)
                $buy_now_customization_id = $this->session->userdata('buy_now_customization_id');
                
                if ($buy_now_customization_id) {
                // Handle buy now: get customization data and calculate summary
                $this->load->model('Cart_model');
                $this->load->model('Customization_model');
                
                // Get customization from database
                // Verify it belongs to the current customer for security
                $this->db->where('CustomizationID', $buy_now_customization_id);
                $this->db->where('Customer_ID', $customer_id);
                $customization = $this->db->get('customization')->row();
                
                if ($customization) {
                    // Get product info
                    $product_id = $customization->Product_ID ?? 0;
                    if ($product_id) {
                        $this->load->model('Product_model');
                        $product = $this->Product_model->get_product($product_id);
                        
                        if ($product) {
                            // Calculate summary for buy now (use quantity from session if available)
                            $quantity = $this->session->userdata('buy_now_quantity') ?: 1;
                            $unit_price = floatval($customization->EstimatePrice ?? $customization->TotalQuotation ?? $product->Price ?? 0);
                            $subtotal = $unit_price * $quantity;
                            $items_count = $quantity;
                            $shipping = $items_count * 25;
                            $handling = $items_count * 10;
                            $total = $subtotal + $shipping + $handling;
                            
                            $data['pending_summary'] = [
                                'items' => $items_count,
                                'subtotal' => $subtotal,
                                'shipping' => $shipping,
                                'handling' => $handling,
                                'total' => $total
                            ];
                        }
                    }
                }
                } else {
                    // Priority 3: Handle regular cart checkout: get selected cart IDs from URL or session
                $selected_ids_str = $this->input->get('selected') ?? '';
                
                if (!empty($selected_ids_str)) {
                    $data['pending_cart_ids'] = $selected_ids_str;
                    
                    // Get cart items for selected IDs
                    $this->load->model('Cart_model');
                    $cart_items = $this->Cart_model->get_cart_items_with_details($customer_id);
                    
                    // Parse selected IDs
                    $selected_ids = array_map('intval', explode(',', $selected_ids_str));
                    $selected_ids = array_filter($selected_ids);
                    
                    // Filter to only selected items
                    $selected_items = [];
                    foreach ($cart_items as $item) {
                        if (in_array($item->Cart_ID, $selected_ids)) {
                            $selected_items[] = $item;
                        }
                    }
                    
                    if (!empty($selected_items)) {
                        // Calculate summary using same logic as CartCon
                        $subtotal = 0;
                        $total_items = 0;
                        
                        foreach ($selected_items as $item) {
                            $price = $item->Price ?? $item->EstimatePrice ?? $item->BasePrice ?? 0;
                            $subtotal += $price * $item->Quantity;
                            $total_items += $item->Quantity;
                        }
                        
                        $shipping = $total_items * 25;
                        $handling = $total_items * 10;
                        $total = $subtotal + $shipping + $handling;
                        
                        $data['pending_summary'] = [
                            'items' => $total_items,
                            'subtotal' => $subtotal,
                            'shipping' => $shipping,
                            'handling' => $handling,
                            'total' => $total
                        ];
                    }
                }
                }
            }
        }
        
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
        
        // Check if this is a Direct Order (not Site Assessment)
        $order_type = strtolower(trim($order->OrderType ?? ''));
        $is_site_assessment = (
            $order_type === 'site-assessed' || 
            $order_type === 'site assessment' || 
            $order_type === 'site-assessed order'
        );
        $is_direct_order = !$is_site_assessment;
        
        // Get payment info for Direct Orders
        $payment_status = strtolower(trim($order->PaymentStatus ?? 'pending'));
        $payment = null;
        if ($is_direct_order) {
            $payment = $this->Order_model->get_order_payment($order_id);
        }
        
        // Get progress with order_id to check appointments table
        $progress = $this->Order_model->get_order_progress($order->Status, $order_id);
        
        // Calculate progress percentage based on order type
        if ($is_direct_order) {
            // Direct Order: 4 steps (Order Placed, Paid, In Fabrication, Completed)
            $total_steps = 4;
            $completed_steps = 0;
            if ($progress['order_placed'] === 'completed') $completed_steps++;
            if ($payment_status === 'paid' || $payment_status === 'partial') $completed_steps++;
            if ($progress['in_fabrication'] === 'completed') $completed_steps++;
            if ($progress['completed'] === 'completed') $completed_steps++;
        } else {
            // Site Assessment Order: 5 steps (Booking Submitted, Ocular Visit, In Fabrication, Installed, Completed)
            $total_steps = 5;
            $completed_steps = 0;
            foreach ($progress as $step => $status) {
                if ($status === 'completed') {
                    $completed_steps++;
                }
            }
        }
        
        $progress_percent = ($completed_steps / $total_steps) * 100;
        
        // Get dates from order
        $dates = [
            'order_date' => $order->OrderDate ?? null,
            'order_time' => $order->OrderDate ? date('g:i A', strtotime($order->OrderDate)) : null,
            'ocular_date' => $order->OcularDate ?? null,
            'payment_date' => ($payment && isset($payment->Payment_Date)) ? $payment->Payment_Date : (($payment && isset($payment->PaymentDate)) ? $payment->PaymentDate : null),
            'fabrication_date' => $order->FabricationDate ?? null,
            'installation_date' => $order->InstallationDate ?? null,
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
            'has_in_progress' => $has_in_progress,
            'payment_status' => $payment_status,
            'is_direct_order' => $is_direct_order,
            'is_site_assessment' => $is_site_assessment
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
                    $quantity = $this->session->userdata('buy_now_quantity') ?: 1;
                    $unit_price = floatval($custom->TotalQuotation ?? $custom->EstimatePrice ?? 0);
                    $total_amount = $unit_price * $quantity;
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
                'Status' => 'Pending',
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
        // Set JSON response header
        header('Content-Type: application/json');

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

        // Get selected cart IDs from POST
        $selected_ids_str = $this->input->post('selected_cart_ids');
        
        // Get cart items
        $all_cart_items = $this->Cart_model->get_cart_items_with_details($customer_id);
        $cart_items = [];
        
        if (!empty($selected_ids_str)) {
            $selected_ids = array_map('intval', explode(',', $selected_ids_str));
            $selected_ids = array_filter($selected_ids);
            
            foreach ($all_cart_items as $item) {
                if (in_array($item->Cart_ID, $selected_ids)) {
                    $cart_items[] = $item;
                }
            }
        } else {
            // Default to all items if none specified (fallback)
            $cart_items = $all_cart_items;
        }

        if (empty($cart_items)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Your cart is empty.'
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

        // Prepare order data - Set status to Pending Payment (will be updated after PayMongo verification)
        $order_data = [
            'Customer_ID' => $customer_id,
            'SalesRep_ID' => $sales_rep_id,
            'TotalAmount' => $total_amount,
            'Status' => 'Pending Payment',
            'PaymentStatus' => 'Pending',
            'DeliveryAddress' => $shipping_address,
            'SpecialInstructions' => $special_instructions_text,
            'PaymentMethod' => ucfirst($payment_method) // Store selected payment method
        ];

        // Create order (before payment)
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

        // DO NOT clear cart yet - wait until payment is successful
        // Cart will be cleared after payment verification in attach_payment_method() or payment_complete()
        // This allows users to refresh the payment page without losing their items

        // Return order_id to frontend - frontend will then create payment intent
        echo json_encode([
            'status' => 'success',
            'message' => 'Order created. Proceeding to payment...',
            'order_id' => $order_id,
            'payment_method' => $payment_method,
            'total_amount' => $total_amount,
            'next_step' => 'create_payment_intent' // Indicates frontend should create payment intent
        ]);
    }

    /**
     * Confirm Booking - AJAX endpoint for Site Assessment Orders
     * Creates an order with status "Pending Booking Confirmation"
     */
    public function confirm_booking()
    {
        // Set JSON response header
        header('Content-Type: application/json');

        // Check if user is logged in
        $customer_id = $this->session->userdata('customer_id');
        if (!$customer_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Please log in to confirm booking.'
            ]);
            return;
        }

        // Get POST data
        $terms_accepted = $this->input->post('terms_accepted');

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

        // Get selected cart IDs from POST
        $selected_ids_str = $this->input->post('selected_cart_ids');
        
        // Get cart items
        $all_cart_items = $this->Cart_model->get_cart_items_with_details($customer_id);
        $cart_items = [];
        
        if (!empty($selected_ids_str)) {
            $selected_ids = array_map('intval', explode(',', $selected_ids_str));
            $selected_ids = array_filter($selected_ids);
            
            foreach ($all_cart_items as $item) {
                if (in_array($item->Cart_ID, $selected_ids)) {
                    $cart_items[] = $item;
                }
            }
        } else {
            // Default to all items if none specified (fallback)
            $cart_items = $all_cart_items;
        }

        if (empty($cart_items)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Your cart is empty.'
            ]);
            return;
        }

        // Calculate totals (for Site Assessment, we show a price range)
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
            $special_instructions[] = 'Preferred Ocular Visit Date: ' . date('F j, Y', strtotime($preferred_installation_date));
        }
        $special_instructions_text = !empty($special_instructions) ? implode(' | ', $special_instructions) : null;

        // Get default sales rep
        $sales_rep_id = $this->Order_model->get_default_sales_rep();

        // Prepare order data with "Pending Booking Confirmation" status
        $order_data = [
            'Customer_ID' => $customer_id,
            'SalesRep_ID' => $sales_rep_id,
            'TotalAmount' => $total_amount,
            'Status' => 'Pending Booking Confirmation', // Site Assessment order status
            'PaymentStatus' => 'Pending',
            'DeliveryAddress' => $shipping_address,
            'SpecialInstructions' => $special_instructions_text,
            'OrderType' => 'Site-Assessed' // Mark as Site Assessment order
        ];

        // Create order
        $order_id = $this->Order_model->create_order($order_data);

        if (!$order_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create booking. Please try again.'
            ]);
            return;
        }

        // Save order customizations from cart items
        $this->Order_model->save_order_customizations($order_id, $cart_items);

        // Clear cart after successful booking
        $this->Cart_model->clear_cart($customer_id);

        // Redirect to track order page
        $redirect_url = base_url('track_order?order=' . $order_id);

        echo json_encode([
            'status' => 'success',
            'message' => 'Booking confirmed successfully!',
            'order_id' => $order_id,
            'redirect_url' => $redirect_url
        ]);
    }

    /**
     * Create PayMongo payment intent for Direct Order
     * STEP 2 - Backend Creates Payment Intent
     */
    public function create_payment_intent()
    {
        header('Content-Type: application/json');
        
        $customer_id = $this->session->userdata('customer_id');
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Please log in to proceed with payment.']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        $payment_method = $this->input->post('payment_method'); // card, gcash, maya
        
        if (!$order_id) {
            echo json_encode(['status' => 'error', 'message' => 'Order ID is required.']);
            return;
        }
        
        // Load PayMongo library
        $this->load->library('paymongo');
        
        // Get order details
        $this->load->model('Order_model');
        $order = $this->Order_model->get_order($order_id);
        
        if (!$order || $order->Customer_ID != $customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found or access denied.']);
            return;
        }
        
        // Only allow payment for Direct Orders (not Site Assessment)
        $order_type = strtolower(trim($order->OrderType ?? ''));
        if ($order_type === 'site-assessed' || $order_type === 'site assessment') {
            echo json_encode(['status' => 'error', 'message' => 'Site Assessment orders use a different payment flow.']);
            return;
        }
        
        // Map payment method
        $paymongo_method = 'card';
        if ($payment_method === 'gcash' || $payment_method === 'ewallet') {
            $paymongo_method = 'gcash';
        } elseif ($payment_method === 'maya') {
            $paymongo_method = 'maya';
        }
        
        // Create payment intent
        $order_number = $order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
        $description = 'Order #' . $order_number;
        
        $result = $this->paymongo->create_payment_intent(
            $order->TotalAmount,
            $paymongo_method,
            $description,
            ['order_id' => $order_id]
        );
        
        if (!$result['success']) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to initialize payment: ' . ($result['error'] ?? 'Unknown error')
            ]);
            return;
        }
        
        // Save payment intent to database (using Transaction_ID field to store PaymentIntentID)
        $this->load->database();
        $payment_data = [
            'OrderID' => $order_id,
            'PaymentMethod' => ucfirst($payment_method),
            'Amount' => $order->TotalAmount,
            'Status' => 'Pending',
            'Payment_Date' => date('Y-m-d H:i:s'),
            'Transaction_ID' => $result['payment_intent_id'] // Store PaymentIntentID in Transaction_ID field
        ];
        
        // Check if payment record exists, update or create
        $existing_payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
        if ($existing_payment) {
            $this->db->where('OrderID', $order_id)->update('payment', $payment_data);
        } else {
            $this->db->insert('payment', $payment_data);
        }
        
        echo json_encode([
            'status' => 'success',
            'payment_intent_id' => $result['payment_intent_id'],
            'client_key' => $result['client_key'],
            'public_key' => $this->paymongo->get_public_key()
        ]);
    }
    
    /**
     * Attach payment method to payment intent
     * STEP 4 - Backend Attaches Payment Method
     */
    public function attach_payment_method()
    {
        header('Content-Type: application/json');
        
        $customer_id = $this->session->userdata('customer_id');
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Please log in.']);
            return;
        }
        
        $payment_intent_id = $this->input->post('payment_intent_id');
        $payment_method_id = $this->input->post('payment_method_id');
        $order_id = $this->input->post('order_id');
        
        if (!$payment_intent_id || !$payment_method_id || !$order_id) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
            return;
        }
        
        // Verify order belongs to customer
        $this->load->model('Order_model');
        $order = $this->Order_model->get_order($order_id);
        
        if (!$order || $order->Customer_ID != $customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found or access denied.']);
            return;
        }
        
        // Load PayMongo library
        $this->load->library('paymongo');
        
        // Build return URL for e-wallet redirects
        $return_url = base_url('payment/complete?order_id=' . $order_id);
        
        // Attach payment method
        $result = $this->paymongo->attach_payment_method($payment_intent_id, $payment_method_id, $return_url);
        
        if (!$result['success']) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to process payment: ' . ($result['error'] ?? 'Unknown error')
            ]);
            return;
        }
        
        // Check payment status
        if ($result['status'] === 'succeeded') {
            // Payment succeeded immediately (card payment)
            $this->db->where('OrderID', $order_id)->update('payment', [
                'Status' => 'Paid',
                'Payment_Date' => date('Y-m-d H:i:s')
            ]);
            
            // Get order details to check if payment amount >= order total
            $this->load->model('Order_model');
            $order = $this->Order_model->get_order($order_id);
            $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
            
            // Check if payment amount meets or exceeds order total (for direct products)
            $payment_amount = $payment ? (float)($payment->Amount ?? 0) : 0;
            $order_total = $order ? (float)($order->TotalAmount ?? 0) : 0;
            $order_type = strtolower(trim($order->OrderType ?? ''));
            
            // For direct products: if payment amount >= order total, automatically mark as Paid
            $is_direct_product = ($order_type === 'direct' || empty($order_type));
            $is_fully_paid = $is_direct_product && $payment_amount >= $order_total;
            
            // Update order status
            $this->db->where('OrderID', $order_id)->update('`order`', [
                'PaymentStatus' => 'Paid',
                'Status' => $is_fully_paid ? 'Paid' : 'Pending Payment' // Auto-mark as Paid if full payment received
            ]);
            
            // Clear cart only after payment is successful
            $this->load->model('Cart_model');
            $this->Cart_model->clear_cart($customer_id);
            
            echo json_encode([
                'status' => 'success',
                'payment_status' => 'succeeded',
                'message' => 'Payment successful!',
                'redirect_url' => base_url('payment/complete?order_id=' . $order_id . '&payment_intent_id=' . $payment_intent_id)
            ]);
        } elseif ($result['status'] === 'awaiting_next_action' && !empty($result['next_action'])) {
            // E-wallet payment - redirect to PayMongo
            echo json_encode([
                'status' => 'success',
                'payment_status' => 'awaiting_next_action',
                'redirect_url' => $result['next_action']['redirect']['url'],
                'message' => 'Redirecting to payment...'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Payment processing failed. Please try again.'
            ]);
        }
    }
    
    /**
     * Handle payment completion (return from PayMongo)
     * STEP 6 - Return From PayMongo
     */
    public function payment_complete()
    {
        $order_id = $this->input->get('order_id');
        $payment_intent_id = $this->input->get('payment_intent_id');
        
        if (!$order_id) {
            show_error('Order ID is required.');
            return;
        }
        
        // Load PayMongo library
        $this->load->library('paymongo');
        
        // Get payment intent ID from URL or database
        if (!$payment_intent_id) {
            $this->load->database();
            $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
            if ($payment && !empty($payment->Transaction_ID)) {
                $payment_intent_id = $payment->Transaction_ID; // Transaction_ID stores PaymentIntentID
            }
        }
        
        if ($payment_intent_id) {
            // Verify payment intent status
            $result = $this->paymongo->retrieve_payment_intent($payment_intent_id);
            
            if ($result['success'] && $result['status'] === 'succeeded') {
                // Payment verified - update database
                $this->load->database();
                
                // Get order details to check if payment amount >= order total
                $this->load->model('Order_model');
                $order = $this->Order_model->get_order($order_id);
                $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
                
                // Check if payment amount meets or exceeds order total (for direct products)
                $payment_amount = $payment ? (float)($payment->Amount ?? 0) : 0;
                $order_total = $order ? (float)($order->TotalAmount ?? 0) : 0;
                $order_type = strtolower(trim($order->OrderType ?? ''));
                
                // For direct products: if payment amount >= order total, automatically mark as Paid
                $is_direct_product = ($order_type === 'direct' || empty($order_type));
                $is_fully_paid = $is_direct_product && $payment_amount >= $order_total;
                
                $this->db->where('OrderID', $order_id)->update('payment', [
                    'Status' => 'Paid',
                    'Payment_Date' => date('Y-m-d H:i:s')
                ]);
                
                // If fully paid, mark order status accordingly; otherwise wait for admin verification
                $this->db->where('OrderID', $order_id)->update('`order`', [
                    'PaymentStatus' => 'Paid',
                    'Status' => $is_fully_paid ? 'Paid' : 'Pending Payment' // Auto-mark as Paid if full payment received
                ]);
                
                // Clear cart only after payment is verified
                $customer_id = $this->session->userdata('customer_id');
                if ($customer_id) {
                    $this->load->model('Cart_model');
                    $this->Cart_model->clear_cart($customer_id);
                }
            }
        }
        
        // Load order details for success page
        $this->load->model('Order_model');
        $order = $this->Order_model->get_order($order_id);
        
        if (!$order) {
            show_error('Order not found.');
            return;
        }
        
        // Get payment details
        $this->load->database();
        $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
        
        $data['title'] = 'Payment Complete';
        $data['order'] = $order;
        $data['payment'] = $payment;
        $data['payment_status'] = $result['status'] ?? 'unknown';
        
        $this->load->view('includes/header', $data);
        $this->load->view('shop/payment_complete', $data);
        $this->load->view('includes/footer');
    }
    
    /**
     * Accept quotation for Site Assessment order
     * Changes status from "Quotation Available" to "Awaiting Payment"
     */
    public function accept_quotation()
    {
        header('Content-Type: application/json');

        $customer_id = $this->session->userdata('customer_id');
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Please log in to accept quotation.']);
            return;
        }

        $order_id = $this->input->post('order_id');
        if (!$order_id) {
            echo json_encode(['status' => 'error', 'message' => 'Order ID is required.']);
            return;
        }

        $this->load->model('Order_model');

        // Verify order belongs to customer and is a Site Assessment order
        $order = $this->Order_model->get_order($order_id);
        if (!$order) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
            return;
        }

        if ($order->Customer_ID != $customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
            return;
        }

        $order_type = strtolower(trim($order->OrderType ?? ''));
        $is_site_assessment = (
            $order_type === 'site-assessed' || 
            $order_type === 'site assessment' || 
            $order_type === 'site-assessed order'
        );

        if (!$is_site_assessment) {
            echo json_encode(['status' => 'error', 'message' => 'This order is not a Site Assessment order.']);
            return;
        }

        $status_lower = strtolower(trim($order->Status ?? ''));
        if ($status_lower !== 'quotation available' && $status_lower !== 'quotation ready' && $status_lower !== 'ready for quotation') {
            echo json_encode(['status' => 'error', 'message' => 'Quotation is not available for acceptance at this time.']);
            return;
        }

        // Update order status to "Awaiting Payment"
        $this->Order_model->update_order_status($order_id, 'Awaiting Payment');

        echo json_encode([
            'status' => 'success',
            'message' => 'Quotation accepted successfully!',
            'order_id' => $order_id
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

        // Ensure customer_id is an integer
        $customer_id = (int)$customer_id;

        // Get filter parameter (all, to_receive, completed, cancelled)
        $filter = $this->input->get('filter') ?: 'all';
        $valid_filters = ['all', 'to_receive', 'completed', 'cancelled'];
        if (!in_array($filter, $valid_filters)) {
            $filter = 'all';
        }
        $data['current_filter'] = $filter;

        // Load Order model
        $this->load->model('Order_model');

        // Get customer's order items (purchases) from database with filter
        $data['order_items'] = $this->Order_model->get_customer_order_items($customer_id, $filter);

        $this->load->view('includes/header', $data);
        $this->load->view('shop/list_product', $data);
        $this->load->view('includes/footer');
    }

    // ===================== CUSTOMER NOTIFICATIONS =====================
    public function notifications()
    {
        $data['title'] = "Glassify - Notifications";

        // Check if user is logged in and is a customer
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Customer') {
            redirect('login');
            return;
        }
        
        $customer_id = $this->session->userdata('customer_id');
        if (!$customer_id) {
            redirect('login');
            return;
        }
        
        // Set cache control headers for customer pages
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');

        // Ensure customer_id is an integer
        $customer_id = (int)$customer_id;

        // Check if customer_notifications table exists
        if ($this->db->table_exists('customer_notifications')) {
            // Get customer's notifications
            $this->db->where('Customer_ID', $customer_id);
            $this->db->order_by('Created_Date', 'DESC');
            $notifications = $this->db->get('customer_notifications')->result();
            
            // Format notifications for display
            $data['notifications'] = [];
            foreach ($notifications as $notif) {
                $data['notifications'][] = (object)[
                    'NotificationID' => $notif->NotificationID,
                    'Title' => $notif->Title ?? 'Notification',
                    'Message' => $notif->Message ?? '',
                    'Icon' => $notif->Icon ?? 'fa-info-circle',
                    'Type' => $notif->Type ?? 'General',
                    'Status' => strtolower($notif->Status ?? 'read'),
                    'Created_Date' => $notif->Created_Date ?? date('Y-m-d H:i:s'),
                    'RelatedID' => $notif->RelatedID ?? null,
                    'RelatedType' => $notif->RelatedType ?? null,
                    'ActionData' => isset($notif->ActionData) && !empty($notif->ActionData) ? $notif->ActionData : null
                ];
            }
        } else {
            // Table doesn't exist yet - show empty state
            $data['notifications'] = [];
        }

        $this->load->view('includes/header', $data);
        $this->load->view('shop/notifications', $data);
        $this->load->view('includes/footer');
    }

    // Get unread notification count (AJAX endpoint)
    public function get_notification_count_ajax()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Customer') {
            echo json_encode(['status' => 'error', 'count' => 0]);
            return;
        }
        
        $customer_id = (int)$this->session->userdata('customer_id');
        
        if ($this->db->table_exists('customer_notifications')) {
            $this->db->where('Customer_ID', $customer_id);
            $this->db->where('Status', 'Unread');
            $count = $this->db->count_all_results('customer_notifications');
            
            // Limit to 99, show 99+ if more
            if ($count > 99) {
                $display_count = '99+';
            } else {
                $display_count = $count;
            }
            
            echo json_encode(['status' => 'success', 'count' => $count, 'display' => $display_count]);
        } else {
            echo json_encode(['status' => 'error', 'count' => 0]);
        }
    }

    // Request installation date change
    public function request_installation_date_change()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Customer') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $order_id = $this->input->post('order_id');
        $new_date = $this->input->post('new_date');
        
        if (!$order_id || !$new_date) {
            echo json_encode(['success' => false, 'message' => 'Order ID and new date are required']);
            return;
        }
        
        // Get customer ID
        $customer_id = (int)$this->session->userdata('customer_id');
        
        // Verify order belongs to customer
        $this->db->where('OrderID', $order_id);
        $this->db->where('Customer_ID', $customer_id);
        $order = $this->db->get('`order`')->row();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found or does not belong to you']);
            return;
        }
        
        // Get installation appointment
        $this->db->where('OrderID', $order_id);
        $this->db->where('Service', 'Installed');
        $installation_appointment = $this->db->get('appointments')->row();
        
        if (!$installation_appointment) {
            echo json_encode(['success' => false, 'message' => 'Installation appointment not found']);
            return;
        }
        
        // Validate date is within 7 days of original date
        $original_date = $installation_appointment->AppointmentDate;
        if ($original_date) {
            $original_timestamp = strtotime($original_date);
            $new_timestamp = strtotime($new_date);
            $allowed_until = $original_timestamp + (7 * 24 * 60 * 60); // 7 days in seconds
            
            if ($new_timestamp > $allowed_until) {
                echo json_encode(['success' => false, 'message' => 'Date must be within 7 days of original installation date']);
                return;
            }
            
            if ($new_timestamp < time()) {
                echo json_encode(['success' => false, 'message' => 'New date cannot be in the past']);
                return;
            }
        }
        
        // Update installation appointment date
        $this->db->where('AppointmentID', $installation_appointment->AppointmentID);
        $update_result = $this->db->update('appointments', [
            'AppointmentDate' => $new_date,
            'Updated_Date' => date('Y-m-d H:i:s')
        ]);
        
        // Update order's InstallationDate if field exists
        if ($this->db->field_exists('InstallationDate', 'order')) {
            $this->db->where('OrderID', $order_id);
            $this->db->update('`order`', ['InstallationDate' => $new_date]);
        }
        
        if ($update_result) {
            // Send notification to admin about date change request
            $this->load->helper('notification');
            $order_number = $order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
            
            // Get customer name
            $this->db->select('u.First_Name, u.Last_Name');
            $this->db->from('customer c');
            $this->db->join('user u', 'u.UserID = c.UserID', 'left');
            $this->db->where('c.Customer_ID', $customer_id);
            $customer_user = $this->db->get()->row();
            $customer_name = trim(($customer_user->First_Name ?? '') . ' ' . ($customer_user->Last_Name ?? ''));
            
            // Note: This would typically notify admin, but for now we'll just log it
            log_message('info', "Customer {$customer_name} (ID: {$customer_id}) requested installation date change for order #{$order_number} to {$new_date}");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Date change request submitted successfully! We will contact you to confirm the new date.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update installation date']);
        }
    }

    // Mark all notifications as read
    public function mark_notifications_read()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Customer') {
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
            return;
        }
        
        $customer_id = (int)$this->session->userdata('customer_id');
        
        if ($this->db->table_exists('customer_notifications')) {
            // Mark all unread notifications as read
            $this->db->where('Customer_ID', $customer_id);
            $this->db->where('Status', 'Unread');
            $this->db->update('customer_notifications', [
                'Status' => 'Read',
                'Read_Date' => date('Y-m-d H:i:s')
            ]);
            
            echo json_encode(['status' => 'success', 'message' => 'Notifications marked as read']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Notifications table does not exist']);
        }
    }

    // ===================== GET BOOKED DATES =====================
    public function get_booked_dates()
    {
        header('Content-Type: application/json');
        
        $this->db->select('SpecialInstructions');
        $this->db->where('Status !=', 'Cancelled');
        $orders = $this->db->get('`order`')->result();
        
        $booked_dates = [];
        foreach ($orders as $order) {
            if (empty($order->SpecialInstructions)) continue;
            
            // Look for "Preferred Installation Date: [date]" pattern
            if (preg_match('/Preferred Installation Date:\s*([^|]+)/i', $order->SpecialInstructions, $matches)) {
                $date_str = trim($matches[1]);
                $parsed_date = date_parse($date_str);
                if ($parsed_date && $parsed_date['error_count'] == 0) {
                    $year = $parsed_date['year'];
                    $month = str_pad($parsed_date['month'], 2, '0', STR_PAD_LEFT);
                    $day = str_pad($parsed_date['day'], 2, '0', STR_PAD_LEFT);
                    $booked_dates[] = $year . '-' . $month . '-' . $day;
                }
            }
        }
        
        echo json_encode(['status' => 'success', 'booked_dates' => array_unique($booked_dates)]);
    }
    
}