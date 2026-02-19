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
    
    // Check if user is logged in
    $customer_id = $this->session->userdata('customer_id');
    
    // Get customer data to check setup status and role
    $customer_role = null;
    $setup_status = 'pending';
    if ($customer_id) {
        $customer_data = $this->db->get_where('customer', ['Customer_ID' => $customer_id])->row();
        if ($customer_data) {
            $customer_role = $customer_data->role ?? null;
            $setup_status = $customer_data->setup_status ?? 'pending';
        }
    }
    
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
    
    // Pass setup status and role to view for access control
    $data['setup_status'] = $setup_status;
    $data['customer_role'] = $customer_role;
    
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
    
    // Track origin for navigation display (cart or review from buy now)
    $from = $this->input->get('from');
    if ($from === 'review') {
        $this->session->set_userdata('payment_origin', 'review');
    } else {
        $this->session->set_userdata('payment_origin', 'cart');
    }
    $data['payment_origin'] = $this->session->userdata('payment_origin') ?: 'cart';
    
    // Check if this is a stage payment from order tracking (Pay Now button)
    $stage_order_id = $this->input->get('order');
    $stage_name = $this->input->get('stage');
    $data['stage_payment'] = null;

    if ($stage_order_id && $stage_name) {
        $this->load->model('Order_model');
        $stage_order = $this->Order_model->get_order_with_customer($stage_order_id);
        
        if ($stage_order) {
            // Get order items for display (join customization table to get Customization JSON)
            $stage_items = $this->db->select('oi.*, p.ProductName, p.ImageUrl, p.Category, p.Subcategory, p.PriceMin, p.PriceMax, c.Customization')
                                    ->from('order_items oi')
                                    ->join('product p', 'p.Product_ID = oi.Product_ID', 'left')
                                    ->join('customization c', 'c.CustomizationID = oi.CustomizationID', 'left')
                                    ->where('oi.OrderID', $stage_order_id)
                                    ->get()->result();

            // Calculate total & stage amount
            // Priority: 1) derive from downpayment (most accurate), 2) order.TotalAmount, 3) calculate from items
            
            // Get downpayment first - this is the most reliable source
            $dp_payment = $this->db->where('OrderID', $stage_order_id)
                                   ->order_by('Payment_Date', 'DESC')
                                   ->get('payment')->row();
            
            $total_quotation = 0;
            
            // Best source: derive from actual downpayment (downpayment = 50% of total)
            if ($dp_payment && floatval($dp_payment->Amount ?? 0) > 0) {
                $total_quotation = floatval($dp_payment->Amount) / 0.5;
            }
            
            // Fallback to order.TotalAmount
            if ($total_quotation <= 0) {
                $total_quotation = floatval($stage_order->TotalAmount ?? 0);
            }
            
            // Final fallback: calculate from order items
            if ($total_quotation <= 0) {
                $this->load->model('Order_model');
                $summary = $this->Order_model->calculate_order_summary($stage_order_id);
                $total_quotation = floatval($summary['total'] ?? 0);
            }

            $stage_labels = [
                'downpayment'  => 'Downpayment (50%)',
                'fabrication'  => 'Fabrication Payment (40%)',
                'installation' => 'Installation Payment (10%)',
            ];

            // Default percentages (used only as fallback)
            $stage_percentages = [
                'downpayment'  => 0.5,
                'fabrication'  => 0.4,
                'installation' => 0.1,
            ];

            // Calculate payment amounts based on total
            $default_dp = round($total_quotation * 0.5, 2);
            $default_fab = round($total_quotation * 0.4, 2);
            $default_inst = round($total_quotation * 0.1, 2);

            // Downpayment amount - use actual payment or calculated
            $dp_amount = $dp_payment && floatval($dp_payment->Amount ?? 0) > 0 ? floatval($dp_payment->Amount) : $default_dp;

            // Fabrication amount - always calculate from total (40%)
            $fab_amount = $default_fab;

            // Installation amount - always calculate from total (10%)
            $inst_amount = $default_inst;

            // Choose stage amount based on selected stage
            switch ($stage_name) {
                case 'downpayment':
                    $stage_amount = round(floatval($dp_amount), 2);
                    break;
                case 'fabrication':
                    $stage_amount = round(floatval($fab_amount), 2);
                    break;
                case 'installation':
                    $stage_amount = round(floatval($inst_amount), 2);
                    break;
                default:
                    $stage_amount = round($total_quotation * ($stage_percentages[$stage_name] ?? 0), 2);
            }

            $data['stage_payment'] = [
                'order_id'    => $stage_order_id,
                'order'       => $stage_order,
                'items'       => $stage_items,
                'stage'       => $stage_name,
                'stage_label' => $stage_labels[$stage_name] ?? ucfirst($stage_name),
                'amount'      => $stage_amount,
                'total'       => $total_quotation,
                'order_number'=> $stage_order->OrderNumber ?? ('GI' . str_pad($stage_order->OrderID, 3, '0', STR_PAD_LEFT)),
                'customer'    => [
                    'first_name' => $stage_order->First_Name ?? '',
                    'last_name'  => $stage_order->Last_Name ?? '',
                    'email'      => $stage_order->Email ?? '',
                    'phone'      => $stage_order->PhoneNum ?? ''
                ]
            ];
            
            // Parse shipping address components from DeliveryAddress
            // Format: "Unit, Street, Subdivision, Barangay, City, Province, Philippines, Zipcode"
            $address_parts = [];
            if (!empty($stage_order->DeliveryAddress)) {
                $parts = array_map('trim', explode(',', $stage_order->DeliveryAddress));
                // Try to identify components by position (common format: unit, street, subdivision, barangay, city, province, country, zipcode)
                $count = count($parts);
                
                // Extract known components (Philippines and zipcode are usually at the end)
                $country = '';
                $zipcode = '';
                $province = '';
                $city = '';
                
                // Check last parts for known patterns
                if ($count > 0 && preg_match('/^\d{4}$/', $parts[$count - 1])) {
                    $zipcode = array_pop($parts);
                    $count--;
                }
                if ($count > 0 && stripos($parts[$count - 1], 'Philippines') !== false) {
                    $country = array_pop($parts);
                    $count--;
                }
                // Province is typically second to last (after removing country/zipcode)
                if ($count > 0) {
                    $province = array_pop($parts);
                    $count--;
                }
                // City is typically third to last
                if ($count > 0) {
                    $city = array_pop($parts);
                    $count--;
                }
                // Barangay is typically fourth to last (after city)
                $barangay = '';
                if ($count > 0) {
                    $barangay = array_pop($parts);
                    $count--;
                }
                
                $address_parts = [
                    'full_address' => $stage_order->DeliveryAddress,
                    'country' => $country ?: 'Philippines',
                    'province' => $province,
                    'city' => $city,
                    'barangay' => $barangay,
                    'zipcode' => $zipcode,
                    'remaining' => implode(', ', $parts) // unit, street, subdivision
                ];
            }
            $data['stage_payment']['address_parts'] = $address_parts;
            
            $data['payment_origin'] = 'stage_payment';
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
    
    // Persist booking origin if provided (e.g., from=review)
    $from = $this->input->get('from');
    if (!empty($from)) {
        $this->session->set_userdata('booking_origin', $from);
    }

    $userID = $this->session->userdata('user_id');
    $customer_id = $this->session->userdata('customer_id');
    $data['user'] = null;
    $data['addresses'] = ['Shipping' => null];

    // Get customer role and setup status for UI conditional logic
    $data['customer_role'] = null;
    $data['setup_status'] = 'pending';
    if ($customer_id) {
        $customer_data = $this->db->get_where('customer', ['Customer_ID' => $customer_id])->row();
        if ($customer_data) {
            $data['customer_role'] = $customer_data->role ?? null;
            $data['setup_status'] = $customer_data->setup_status ?? 'pending';
        }
    }
    
    // Check if this is a beginner booking from 2D modeling page
    $source = $this->input->get('source');
    $product_id = $this->input->get('product_id');
    $product_name = $this->input->get('product_name');
    
    // Pass beginner booking data to view
    $data['beginner_booking'] = false;
    $data['beginner_product_id'] = '';
    $data['beginner_product_name'] = '';
    
    if ($source === 'beginner_booking' && $product_id) {
        // Force beginner role for this booking flow
        $data['customer_role'] = 'beginner';
        $data['beginner_booking'] = true;
        $data['beginner_product_id'] = $product_id;
        $data['beginner_product_name'] = urldecode($product_name);
        log_message('debug', 'Beginner booking detected - Product ID: ' . $product_id . ', Name: ' . urldecode($product_name));
    }

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
    // Expose booking origin to the view (fall back to session)
    $data['booking_origin'] = $this->session->userdata('booking_origin') ?: null;
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
            'PaymentMethod' => 'GCash',
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

    /**
     * Booking complete page (for Site Assessment bookings)
     */
    public function complete_booking()
    {
        $data['title'] = "Glassify - Booking Complete";

        // Get order ID from query string
        $order_id = $this->input->get('order');

        // Load models
        $this->load->model('Order_model');
        $this->load->database();

        $data['order'] = null;
        $data['payment'] = null;
        $data['payment_status'] = 'pending';
        $data['shipping_address'] = null;
        $data['billing_address'] = null;
        $data['user'] = null;

        if ($order_id) {
            // Include customer information
            $data['order'] = $this->Order_model->get_order_with_customer($order_id);
            $data['payment'] = $this->db->where('OrderID', $order_id)->get('payment')->row();
            // Order summary for totals (items, subtotal, shipping, total)
            $data['summary'] = $this->Order_model->calculate_order_summary($order_id);
            
            // Get appointment time for ocular visit display
            // First try to get from appointments table (if already approved)
            $appointment = $this->db->where('OrderID', $order_id)->get('appointments')->row();
            $data['appointment_time'] = $appointment->AppointmentTime ?? null;
            
            // If no appointment yet, try to get preferred time from order's SpecialInstructions
            if (empty($data['appointment_time']) && $data['order'] && !empty($data['order']->SpecialInstructions)) {
                $special_json = json_decode($data['order']->SpecialInstructions, true);
                if (json_last_error() === JSON_ERROR_NONE && !empty($special_json['preferred_ocular_time'])) {
                    // Convert HH:MM to full time format
                    $time_value = $special_json['preferred_ocular_time'];
                    if (strlen($time_value) === 5) {
                        $data['appointment_time'] = $time_value . ':00';
                    } else {
                        $data['appointment_time'] = $time_value;
                    }
                }
            }

            $customer_id = $data['order']->Customer_ID ?? null;
            if ($customer_id) {
                $this->load->model('User_model');
                $addresses = $this->User_model->get_addresses($customer_id);
                $data['shipping_address'] = $addresses['Shipping'] ?? null;
                $data['billing_address'] = $addresses['Billing'] ?? null;
                $data['user'] = $this->User_model->get_by_id($customer_id);
            }
        }

        // Provide booking origin to the view so the progress nav can reflect source until completion
        $data['booking_origin'] = $this->session->userdata('booking_origin') ?: null;
        
        // Get customer role for conditional display (from customer table, not session)
        $data['customer_role'] = 'professional'; // default
        $customer_id_for_role = $data['order']->Customer_ID ?? $this->session->userdata('customer_id');
        if ($customer_id_for_role) {
            $customer_data = $this->db->get_where('customer', ['Customer_ID' => $customer_id_for_role])->row();
            if ($customer_data && !empty($customer_data->role)) {
                $data['customer_role'] = $customer_data->role;
            }
        }

        $this->load->view('includes/header', $data);
        $this->load->view('shop/booking_complete', $data);
        $this->load->view('includes/footer');

        // Clear booking origin after showing the complete page so it doesn't persist beyond flow
        $this->session->unset_userdata('booking_origin');
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
        $data['payment_breakdown'] = [];

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
                // Ensure ocular appointment time (if any) is attached to the order object
                $ocular_apt = $this->db->where('OrderID', $order_id)
                                       ->where('Service', 'Ocular Visit')
                                       ->get('appointments')
                                       ->row();

                // Get downpayment from payment table first (this is the authoritative source for total)
                $dp_payment = $this->db->where('OrderID', $order_id)
                                       ->order_by('Payment_Date', 'DESC')
                                       ->get('payment')->row();

                // Build payment breakdown data (50-40-10 split)
                // Priority: 1) derive from actual downpayment (most accurate), 2) appointment_payments (admin-entered), 3) Don't show until admin sets amount
                $total_amount = 0;
                $admin_has_set_amount = false; // Track if admin has manually entered the amount
                
                // Best source: derive from actual downpayment (downpayment = 50% of total)
                if ($dp_payment && floatval($dp_payment->Amount ?? 0) > 0) {
                    $total_amount = floatval($dp_payment->Amount) / 0.5;
                    $admin_has_set_amount = true;
                }
                
                // Second best: appointment_payments table (admin manually entered during ocular visit)
                if ($total_amount <= 0 && $ocular_apt && $this->db->table_exists('appointment_payments')) {
                    $apt_payment = $this->db->where('appointment_id', $ocular_apt->AppointmentID)
                                            ->get('appointment_payments')->row();
                    if ($apt_payment && floatval($apt_payment->total_amount ?? 0) > 0) {
                        $total_amount = floatval($apt_payment->total_amount);
                        $admin_has_set_amount = true;
                    }
                }
                
                // DO NOT fallback to order.TotalAmount or calculate from items
                // Only show amounts after admin has entered them during ocular visit
                
                // Calculate payment amounts based on total
                $downpayment_amount = round($total_amount * 0.5, 2);
                $fabrication_amount = round($total_amount * 0.4, 2);
                $installation_amount = round($total_amount * 0.1, 2);

                // Get fabrication payment from appointments table (or order table fallback)
                $fab_amount = null; $fab_method = null; $fab_status = 'Pending'; $fab_receipt = null;
                $fab_apt = $this->db->where('OrderID', $order_id)
                                    ->where('Service', 'In Fabrication')
                                    ->get('appointments')->row();
                if ($fab_apt) {
                    $fab_amount = $fab_apt->FabricationPaymentAmount ?? null;
                    $fab_method = $fab_apt->FabricationPaymentMethod ?? null;
                    $fab_status = $fab_apt->FabricationPaymentStatus ?? 'Pending';
                    $fab_receipt = $fab_apt->FabricationReceiptPath ?? null;
                }
                // Fallback to order table
                if (empty($fab_amount) && isset($data['order']->FabricationPaymentAmount)) {
                    $fab_amount = $data['order']->FabricationPaymentAmount;
                    $fab_method = $data['order']->FabricationPaymentMethod ?? $fab_method;
                    $fab_status = $data['order']->FabricationPaymentStatus ?? $fab_status;
                    $fab_receipt = $data['order']->FabricationReceiptPath ?? $fab_receipt;
                }

                // Get installation payment from order table
                $inst_amount = $data['order']->InstallationPaymentAmount ?? null;
                $inst_method = $data['order']->InstallationPaymentMethod ?? null;
                $inst_status = $data['order']->InstallationPaymentStatus ?? 'Pending';
                $inst_receipt = $data['order']->InstallationReceiptPath ?? null;
                $inst_txn_id = $data['order']->InstallationTransactionID ?? null;
                
                // Get installation payment due date from appointments table
                $inst_due_date = null;
                $inst_completed_date = null;
                if ($this->db->table_exists('appointments')) {
                    $this->db->reset_query();
                    $installation_apt = $this->db->select('PaymentDueDate, InstallationCompletedDate')
                                                  ->where('OrderID', $order_id)
                                                  ->where('Service', 'Installed')
                                                  ->get('appointments')->row();
                    if ($installation_apt) {
                        $inst_due_date = $installation_apt->PaymentDueDate ?? null;
                        $inst_completed_date = $installation_apt->InstallationCompletedDate ?? null;
                    }
                }

                // Get fabrication transaction ID
                $fab_txn_id = null;
                if ($fab_apt && property_exists($fab_apt, 'FabricationTransactionID')) {
                    $fab_txn_id = $fab_apt->FabricationTransactionID ?? null;
                }
                if (empty($fab_txn_id) && isset($data['order']->FabricationTransactionID)) {
                    $fab_txn_id = $data['order']->FabricationTransactionID;
                }

                $data['payment_breakdown'] = [
                    'total_amount' => $total_amount,
                    'admin_has_set_amount' => $admin_has_set_amount, // Flag to show/hide amounts to customer
                    'downpayment_amount' => $dp_payment && floatval($dp_payment->Amount ?? 0) > 0 ? floatval($dp_payment->Amount) : $downpayment_amount,
                    'downpayment_method' => $dp_payment ? ($dp_payment->PaymentMethod ?? null) : null,
                    'downpayment_status' => $dp_payment ? ($dp_payment->Status ?? 'Pending') : 'Pending',
                    'downpayment_receipt' => $dp_payment ? ($dp_payment->ReceiptPath ?? null) : null,
                    'downpayment_transaction_id' => $dp_payment ? ($dp_payment->Transaction_ID ?? null) : null,
                    'fabrication_amount' => $fabrication_amount,
                    'fabrication_method' => $fab_method,
                    'fabrication_status' => $fab_status,
                    'fabrication_receipt' => $fab_receipt,
                    'fabrication_transaction_id' => $fab_txn_id,
                    'installation_amount' => $installation_amount,
                    'installation_method' => $inst_method,
                    'installation_status' => $inst_status,
                    'installation_receipt' => $inst_receipt,
                    'installation_transaction_id' => $inst_txn_id,
                    'installation_payment_due_date' => $inst_due_date,
                    'installation_completed_date' => $inst_completed_date,
                ];
                if ($ocular_apt) {
                    if (!empty($ocular_apt->AppointmentTime)) {
                        $data['order']->OcularTime = $ocular_apt->AppointmentTime;
                        // also set AppointmentTime for backward compatibility in views
                        $data['order']->AppointmentTime = $ocular_apt->AppointmentTime;
                    } elseif (!empty($ocular_apt->AppointmentDate) && strpos($ocular_apt->AppointmentDate, ':') !== false) {
                        $data['order']->OcularTime = date('H:i:s', strtotime($ocular_apt->AppointmentDate));
                        $data['order']->AppointmentTime = $data['order']->OcularTime;
                    }
                } else {
                    // No appointment record yet — try to parse preferred ocular date/time from SpecialInstructions JSON
                    if (!empty($data['order']->SpecialInstructions)) {
                        $si = json_decode($data['order']->SpecialInstructions, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($si)) {
                            // Preferred date
                            if (!empty($si['preferred_ocular_date'])) {
                                // Ensure both OcularDate and PreferredInstallationDate are available to views
                                $data['order']->OcularDate = $si['preferred_ocular_date'];
                                $data['order']->PreferredInstallationDate = $si['preferred_ocular_date'];
                                $data['order']->AppointmentDate = $si['preferred_ocular_date'];
                            }
                            // Preferred time
                            if (!empty($si['preferred_ocular_time'])) {
                                $time_val = $si['preferred_ocular_time'];
                                if (strlen($time_val) === 5) $time_val = $time_val . ':00';
                                $data['order']->OcularTime = $time_val;
                                $data['order']->AppointmentTime = $time_val;
                            }
                        }
                    }
                }
            }
        }

        $this->load->view('includes/header', $data);
        $this->load->view('shop/order_tracking', $data);
        $this->load->view('includes/footer');
    }
    
    /**
     * Customer cancel order endpoint (only available before admin approval)
     * Customers can cancel their own orders if status is still "Pending Review" or "Awaiting Admin"
     */
    public function customer_cancel_order()
    {
        header('Content-Type: application/json');
        
        // Check if customer is logged in
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Customer') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in as a customer.']);
            return;
        }
        
        $customer_id = $this->session->userdata('customer_id');
        $order_id = $this->input->post('order_id');
        $reason = $this->input->post('reason') ?: 'Customer requested cancellation';
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }
        
        // Load order model
        $this->load->model('Order_model');
        
        // Get order and verify ownership
        $order = $this->db->where('OrderID', $order_id)
                         ->where('Customer_ID', $customer_id)
                         ->get('order')
                         ->row();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found or you do not have permission to cancel this order']);
            return;
        }
        
        // Check if order can be cancelled (only unapproved orders)
        $status_lower = strtolower(trim($order->Status));
        $can_cancel = in_array($status_lower, ['pending review', 'awaiting admin', 'ready to approve', 'pending booking confirmation']);
        
        if (!$can_cancel) {
            echo json_encode(['success' => false, 'message' => 'This order cannot be cancelled. Please contact support for assistance.']);
            return;
        }
        
        // Update order status to Cancelled
        $update_data = [
            'Status' => 'Cancelled',
            'DisapprovalReason' => $reason,
            'Updated_Date' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('OrderID', $order_id)->update('order', $update_data);
        
        // Log the cancellation
        log_message('info', "Customer #{$customer_id} cancelled order #{$order_id}. Reason: {$reason}");
        
        // Send notification email to admin (optional)
        // TODO: Add email notification if needed
        
        echo json_encode([
            'success' => true,
            'message' => 'Your order has been cancelled successfully'
        ]);
    }
    
    /**
     * AJAX endpoint for customer to submit a stage payment (downpayment, fabrication, installation)
     * Used by the track order page "Pay Now" button
     */
    public function submit_stage_payment()
    {
        header('Content-Type: application/json');
        
        $order_id = $this->input->post('order_id');
        $stage = $this->input->post('stage'); // downpayment, fabrication, installation
        $payment_method = $this->input->post('payment_method');
        
        if (!$order_id || !$stage || !$payment_method) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
            return;
        }

        $this->load->model('Order_model');
        $this->load->database();
        
        // Get order and calculate amounts
        $order = $this->db->where('OrderID', $order_id)->get('`order`')->row();
        if (!$order) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
            return;
        }

        $summary = $this->Order_model->calculate_order_summary($order_id);
        $total_amount = floatval($summary['total'] ?? 0);
        
        // Handle receipt upload
        $receipt_path = null;
        if (!empty($_FILES['receipt']['name'])) {
            $upload_dir = 'uploads/receipts/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $config['upload_path'] = $upload_dir;
            $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf';
            $config['max_size'] = 5120; // 5MB
            $config['file_name'] = 'receipt_' . $order_id . '_' . $stage . '_' . time();
            
            $this->load->library('upload', $config);
            if ($this->upload->do_upload('receipt')) {
                $upload_data = $this->upload->data();
                $receipt_path = $upload_dir . $upload_data['file_name'];
            }
        }

        $now = date('Y-m-d H:i:s');

        try {
            if ($stage === 'downpayment') {
                $amount = round($total_amount * 0.5, 2);
                
                // Check if already paid
                $existing = $this->db->where('OrderID', $order_id)->where('Status', 'Paid')->get('payment')->row();
                if ($existing) {
                    echo json_encode(['status' => 'error', 'message' => 'Downpayment already paid.']);
                    return;
                }
                
                // Map payment method to proper display name
                $display_method = $payment_method;
                switch (strtolower($payment_method)) {
                    case 'gcash':
                        $display_method = 'GCash';
                        break;
                    case 'maya':
                        $display_method = 'Maya';
                        break;
                    case 'card':
                        $display_method = 'Credit/Debit Card';
                        break;
                    default:
                        $display_method = ucfirst($payment_method);
                        break;
                }
                
                // Update existing payment record or insert new one
                $existing_payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
                $payment_data = [
                    'PaymentMethod' => $display_method,
                    'Amount' => $amount,
                    'Status' => 'Paid',
                    'Payment_Date' => $now,
                ];
                if ($receipt_path) $payment_data['ReceiptPath'] = $receipt_path;
                
                if ($existing_payment) {
                    $this->db->where('Payment_ID', $existing_payment->Payment_ID)->update('payment', $payment_data);
                } else {
                    $payment_data['OrderID'] = $order_id;
                    $payment_data['CustomerName'] = ($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? '');
                    $this->db->insert('payment', $payment_data);
                }
                
                // Update order PaymentStatus
                $this->db->where('OrderID', $order_id)->update('`order`', ['PaymentStatus' => 'Partial', 'PaymentMethod' => $display_method]);

            } elseif ($stage === 'fabrication') {
                $amount = round($total_amount * 0.4, 2);
                
                // Verify order is in correct status
                $valid_statuses = ['In Fabrication', 'Ready for Installation', 'Installed', 'Completed'];
                if (!in_array($order->Status, $valid_statuses)) {
                    echo json_encode(['status' => 'error', 'message' => 'Fabrication payment is not yet available.']);
                    return;
                }
                
                // Check if already paid
                $fab_apt = $this->db->where('OrderID', $order_id)->where('Service', 'In Fabrication')->get('appointments')->row();
                if ($fab_apt && ($fab_apt->FabricationPaymentStatus ?? '') === 'Paid') {
                    echo json_encode(['status' => 'error', 'message' => 'Fabrication payment already paid.']);
                    return;
                }
                
                // Map payment method to proper display name
                $display_method = $payment_method;
                switch (strtolower($payment_method)) {
                    case 'gcash':
                        $display_method = 'GCash';
                        break;
                    case 'maya':
                        $display_method = 'Maya';
                        break;
                    case 'card':
                        $display_method = 'Credit/Debit Card';
                        break;
                    default:
                        $display_method = ucfirst($payment_method);
                        break;
                }
                
                // Update in appointments table (fabrication appointment)
                $fab_data = [
                    'FabricationPaymentAmount' => $amount,
                    'FabricationPaymentMethod' => $display_method,
                    'FabricationPaymentStatus' => 'Paid',
                ];
                if ($receipt_path) $fab_data['FabricationReceiptPath'] = $receipt_path;
                
                if ($fab_apt) {
                    $this->db->where('AppointmentID', $fab_apt->AppointmentID)->update('appointments', $fab_data);
                }
                
                // Also update order table if columns exist
                if ($this->db->field_exists('FabricationPaymentAmount', 'order')) {
                    $order_fab_data = [
                        'FabricationPaymentAmount' => $amount,
                        'FabricationPaymentMethod' => $display_method,
                        'FabricationPaymentStatus' => 'Paid',
                    ];
                    if ($receipt_path) $order_fab_data['FabricationReceiptPath'] = $receipt_path;
                    $this->db->where('OrderID', $order_id)->update('`order`', $order_fab_data);
                }

            } elseif ($stage === 'installation') {
                $amount = round($total_amount * 0.1, 2);
                
                // Verify order is in correct status OR payment is actively pending
                $valid_statuses = ['Installed', 'Completed'];
                $payment_pending = ($order->InstallationPaymentStatus ?? '') === 'Pending';
                if (!in_array($order->Status, $valid_statuses) && !$payment_pending) {
                    echo json_encode(['status' => 'error', 'message' => 'Installation payment is not yet available.']);
                    return;
                }
                
                // Check if already paid
                if (($order->InstallationPaymentStatus ?? '') === 'Paid') {
                    echo json_encode(['status' => 'error', 'message' => 'Installation payment already paid.']);
                    return;
                }
                
                // Map payment method to proper display name
                $display_method = $payment_method;
                switch (strtolower($payment_method)) {
                    case 'gcash':
                        $display_method = 'GCash';
                        break;
                    case 'maya':
                        $display_method = 'Maya';
                        break;
                    case 'card':
                        $display_method = 'Credit/Debit Card';
                        break;
                    default:
                        $display_method = ucfirst($payment_method);
                        break;
                }
                
                // Update order table
                if ($this->db->field_exists('InstallationPaymentAmount', 'order')) {
                    $inst_data = [
                        'InstallationPaymentAmount' => $amount,
                        'InstallationPaymentMethod' => $display_method,
                        'InstallationPaymentStatus' => 'Paid',
                    ];
                    if ($receipt_path) $inst_data['InstallationReceiptPath'] = $receipt_path;
                    $this->db->where('OrderID', $order_id)->update('`order`', $inst_data);
                }
                
                // Check if all 3 stages are now paid → mark order as fully Paid
                $dp_paid = $this->db->where('OrderID', $order_id)->where('Status', 'Paid')->get('payment')->row();
                $fab_check = $this->db->where('OrderID', $order_id)->where('Service', 'In Fabrication')->get('appointments')->row();
                $fab_paid = ($fab_check && ($fab_check->FabricationPaymentStatus ?? '') === 'Paid');
                
                if ($dp_paid && $fab_paid) {
                    $this->db->where('OrderID', $order_id)->update('`order`', ['PaymentStatus' => 'Paid']);
                }

            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid payment stage.']);
                return;
            }

            echo json_encode(['status' => 'success', 'message' => ucfirst($stage) . ' payment submitted successfully.']);

        } catch (Exception $e) {
            log_message('error', 'Stage payment error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
        }
    }

    /**
     * Create a PayMongo payment intent for a stage payment (downpayment, fabrication, installation)
     * Called from the checkout page in stage-payment mode
     */
    public function create_stage_payment_intent()
    {
        header('Content-Type: application/json');

        $customer_id = $this->session->userdata('customer_id');
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Please log in to proceed with payment.']);
            return;
        }

        $order_id = $this->input->post('order_id');
        $stage = $this->input->post('stage'); // downpayment, fabrication, installation
        $payment_method = $this->input->post('payment_method'); // card, gcash, maya

        if (!$order_id || !$stage || !$payment_method) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
            return;
        }

        $this->load->model('Order_model');
        $this->load->library('paymongo');
        $this->load->database();

        $order = $this->db->where('OrderID', $order_id)->get('`order`')->row();
        if (!$order || ($order->Customer_ID ?? null) != $customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found or access denied.']);
            return;
        }

        // Calculate stage amount
        $summary = $this->Order_model->calculate_order_summary($order_id);
        $total_amount = floatval($summary['total'] ?? 0);

        $stage_percentages = [
            'downpayment'  => 0.5,
            'fabrication'  => 0.4,
            'installation' => 0.1,
        ];

        if (!isset($stage_percentages[$stage])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid payment stage.']);
            return;
        }

        // Use admin-set amounts if available, otherwise default percentage
        $amount = round($total_amount * $stage_percentages[$stage], 2);

        if ($stage === 'downpayment') {
            $dp_payment = $this->db->where('OrderID', $order_id)->order_by('Payment_Date', 'DESC')->get('payment')->row();
            if ($dp_payment && !empty($dp_payment->Amount) && floatval($dp_payment->Amount) > 0) {
                $amount = floatval($dp_payment->Amount);
            }
        } elseif ($stage === 'fabrication') {
            // Check appointments table (In Fabrication appointment)
            $fab_apt = $this->db->where('OrderID', $order_id)->where('Service', 'In Fabrication')->get('appointments')->row();
            if ($fab_apt && !empty($fab_apt->FabricationPaymentAmount)) {
                $amount = floatval($fab_apt->FabricationPaymentAmount);
            } elseif (!empty($order->FabricationPaymentAmount)) {
                $amount = floatval($order->FabricationPaymentAmount);
            }
        } elseif ($stage === 'installation') {
            if (!empty($order->InstallationPaymentAmount)) {
                $amount = floatval($order->InstallationPaymentAmount);
            }
        }

        if ($amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid payment amount.']);
            return;
        }

        // Validate stage availability
        $order_status = $order->Status ?? 'Pending Review';
        if ($stage === 'fabrication') {
            $valid = ['In Fabrication', 'Ready for Installation', 'Installed', 'Completed'];
            if (!in_array($order_status, $valid)) {
                echo json_encode(['status' => 'error', 'message' => 'Fabrication payment is not yet available.']);
                return;
            }
        } elseif ($stage === 'installation') {
            $valid = ['Installed', 'Completed'];
            $payment_pending = ($order->InstallationPaymentStatus ?? '') === 'Pending';
            if (!in_array($order_status, $valid) && !$payment_pending) {
                echo json_encode(['status' => 'error', 'message' => 'Installation payment is not yet available.']);
                return;
            }
        }

        // Map payment method for PayMongo
        $paymongo_method = 'card';
        if ($payment_method === 'gcash' || $payment_method === 'ewallet') {
            $paymongo_method = 'gcash';
        } elseif ($payment_method === 'maya') {
            $paymongo_method = 'maya';
        }

        try {
            $order_number = $order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
            $stage_labels = ['downpayment' => 'Downpayment', 'fabrication' => 'Fabrication', 'installation' => 'Installation'];
            $description = 'Order #' . $order_number . ' - ' . ($stage_labels[$stage] ?? ucfirst($stage)) . ' Payment';

            $metadata = [
                'order_id' => (string)$order_id,
                'stage' => $stage,
                'stage_amount' => (string)$amount
            ];

            $result = $this->paymongo->create_payment_intent($amount, $paymongo_method, $description, $metadata);

            if (!$result['success']) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to initialize payment: ' . ($result['error'] ?? 'Unknown error')
                ]);
                return;
            }

            // Store stage payment intent in session for verification on completion
            $this->session->set_userdata('stage_payment', [
                'payment_intent_id' => $result['payment_intent_id'],
                'order_id' => $order_id,
                'stage' => $stage,
                'amount' => $amount,
                'payment_method' => $payment_method
            ]);

            echo json_encode([
                'status' => 'success',
                'payment_intent_id' => $result['payment_intent_id'],
                'client_key' => $result['client_key'],
                'public_key' => $this->paymongo->get_public_key(),
                'amount' => $amount,
                'stage' => $stage
            ]);
        } catch (Exception $e) {
            log_message('error', 'create_stage_payment_intent exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
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
        
        // All orders now follow unified flow (site-assessment process)
        // No need to check order_type anymore
        $is_site_assessment = true; // All orders are treated as site-assessment
        $is_direct_order = false;
        
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

        // Debug: log current session state to help investigate unexpected logouts
        $sess_user_id = $this->session->userdata('user_id') ?? 'NULL';
        $sess_user_role = $this->session->userdata('user_role') ?? 'NULL';
        $sess_is_logged_in = $this->session->userdata('is_logged_in') ? '1' : '0';
        $sess_customer = $this->session->userdata('customer_id') ?? 'NULL';
        $cookie_name = $this->config->item('sess_cookie_name') ?: 'ci_session';
        $cookie_val = isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : 'NULL';
        $save_path = APPPATH . 'writable/session/';
        $session_file = $save_path . 'ci_session' . $cookie_val;
        $file_exists = is_string($cookie_val) && $cookie_val !== 'NULL' && file_exists($session_file) ? 'yes' : 'no';

        log_message('debug', 'Place Order - session userdata: user_id=' . $sess_user_id .
            ', user_role=' . $sess_user_role .
            ', is_logged_in=' . $sess_is_logged_in .
            ', customer_id=' . $sess_customer .
            ', cookie_name=' . $cookie_name .
            ', cookie_val=' . $cookie_val .
            ', session_file_exists=' . $file_exists .
            ', session_id_runtime=' . session_id());

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
        $middlename = $this->input->post('middlename');
        $lastname = $this->input->post('lastname');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $unit_house_number = $this->input->post('unit_house_number');
        $street = $this->input->post('street');
        $subdivision = $this->input->post('subdivision');
        $barangay = $this->input->post('barangay');
        $city = $this->input->post('city');
        $province = $this->input->post('province');
        $region = $this->input->post('region');
        $country = $this->input->post('country');
        $zipcode = $this->input->post('zipcode');
        $note = $this->input->post('note');
        $preferred_installation_date = $this->input->post('preferred_installation_date');

        // Build delivery address from form if provided
        if (!empty($unit_house_number) || !empty($city)) {
            $address_parts = array_filter([
                $unit_house_number,
                $street,
                $subdivision,
                $barangay,
                $city,
                $province,
                $zipcode,
                $country
            ]);
            $shipping_address = implode(', ', $address_parts);
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

        // Prepare order data - All orders now follow unified flow with ocular visit
        $order_data = [
            'Customer_ID' => $customer_id,
            'SalesRep_ID' => $sales_rep_id,
            'TotalAmount' => $total_amount,
            'Status' => 'Pending Review', // Unified flow: starts with admin review
            'PaymentStatus' => 'Pending',
            'DeliveryAddress' => $shipping_address,
            'SpecialInstructions' => $special_instructions_text,
            'CustomerNotes' => $note, // Store note separately in CustomerNotes
            'PaymentMethod' => ucfirst($payment_method), // Store selected payment method
            'PreferredInstallationDate' => $preferred_installation_date ? date('Y-m-d', strtotime($preferred_installation_date)) : null
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

        // Debug: log current session state to help investigate unexpected logouts (booking)
        $cb_sess_user_id = $this->session->userdata('user_id') ?? 'NULL';
        $cb_sess_user_role = $this->session->userdata('user_role') ?? 'NULL';
        $cb_sess_is_logged_in = $this->session->userdata('is_logged_in') ? '1' : '0';
        $cb_sess_customer = $this->session->userdata('customer_id') ?? 'NULL';
        $cb_cookie_name = $this->config->item('sess_cookie_name') ?: 'ci_session';
        $cb_cookie_val = isset($_COOKIE[$cb_cookie_name]) ? $_COOKIE[$cb_cookie_name] : 'NULL';
        $cb_save_path = APPPATH . 'writable/session/';
        $cb_session_file = $cb_save_path . 'ci_session' . $cb_cookie_val;
        $cb_file_exists = is_string($cb_cookie_val) && $cb_cookie_val !== 'NULL' && file_exists($cb_session_file) ? 'yes' : 'no';

        log_message('debug', 'Confirm Booking - session userdata: user_id=' . $cb_sess_user_id .
            ', user_role=' . $cb_sess_user_role .
            ', is_logged_in=' . $cb_sess_is_logged_in .
            ', customer_id=' . $cb_sess_customer .
            ', cookie_name=' . $cb_cookie_name .
            ', cookie_val=' . $cb_cookie_val .
            ', session_file_exists=' . $cb_file_exists .
            ', session_id_runtime=' . session_id());

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
        $this->load->model('Product_model');

        // Check if this is a beginner booking (product-based, no cart)
        $is_beginner_booking = $this->input->post('is_beginner_booking') === 'true';
        $beginner_product_id = $this->input->post('beginner_product_id');
        
        $cart_items = [];
        $subtotal = 0;
        $total_items = 0;
        
        if ($is_beginner_booking && $beginner_product_id) {
            // BEGINNER BOOKING: Create a virtual cart item from product
            log_message('debug', 'Beginner booking for product ID: ' . $beginner_product_id);
            
            $product = $this->Product_model->get_product($beginner_product_id);
            if (!$product) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Product not found.'
                ]);
                return;
            }
            
            // Create a virtual cart item object for the order
            $virtual_item = new stdClass();
            $virtual_item->Cart_ID = null; // No cart entry
            $virtual_item->Product_ID = $product->Product_ID;
            $virtual_item->ProductName = $product->ProductName;
            $virtual_item->Category = $product->Category ?? '';
            $virtual_item->Subcategory = $product->Subcategory ?? '';
            $virtual_item->Quantity = 1;
            $virtual_item->EstimatePrice = $product->Price ?? $product->PriceMin ?? 0;
            $virtual_item->Price = $product->Price ?? $product->PriceMin ?? 0;
            $virtual_item->PriceMin = $product->PriceMin ?? null;
            $virtual_item->PriceMax = $product->PriceMax ?? null;
            $virtual_item->Customization_ID = null; // No customization for beginners
            $virtual_item->ImageUrl = $product->ImageUrl ?? '';
            $virtual_item->is_beginner_booking = true;
            
            $cart_items[] = $virtual_item;
            $total_items = 1;
            $subtotal = floatval($virtual_item->EstimatePrice);
            
        } else {
            // STANDARD BOOKING: Get items from cart
            // Get selected cart IDs from POST
            $selected_ids_str = $this->input->post('selected_cart_ids');
            
            // Get cart items
            $all_cart_items = $this->Cart_model->get_cart_items_with_details($customer_id);
            
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
            foreach ($cart_items as $item) {
                $price = $item->EstimatePrice ?? $item->Price ?? 0;
                $subtotal += $price * $item->Quantity;
                $total_items += $item->Quantity;
            }
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
        $middlename = $this->input->post('middlename');
        $lastname = $this->input->post('lastname');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $address = $this->input->post('address');
        $city = $this->input->post('city');
        $province = $this->input->post('province');
        $country = $this->input->post('country');
        $zipcode = $this->input->post('zipcode');
        $note = $this->input->post('note');
        $preferred_installation_date = $this->input->post('preferred_installation_date');
        $preferred_time = $this->input->post('preferred_time');

        // Build full name from form fields
        $full_name = trim(implode(' ', array_filter([$firstname, $middlename, $lastname])));

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
            'SpecialInstructions' => json_encode([
                'contact_name' => $full_name,
                'contact_phone' => $phone,
                'contact_email' => $email,
                'note' => $note,
                'preferred_ocular_date' => $preferred_installation_date,
                'preferred_ocular_time' => $preferred_time
            ], JSON_UNESCAPED_UNICODE),
            'CustomerNotes' => $note, // Save note separately
            'PreferredInstallationDate' => $preferred_installation_date ? date('Y-m-d', strtotime($preferred_installation_date)) : null // Save date separately
            // OrderType removed - all orders now follow unified process
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

        // Clear cart after successful booking (only for non-beginner bookings)
        if (!$is_beginner_booking) {
            $this->Cart_model->clear_cart($customer_id);
        }

        // Redirect to booking-complete page
        $redirect_url = base_url('complete_booking?order=' . $order_id);

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
        
        // All orders now follow unified flow - payment is accepted for all
        
        // Map payment method
        $paymongo_method = 'card';
        if ($payment_method === 'gcash' || $payment_method === 'ewallet') {
            $paymongo_method = 'gcash';
        } elseif ($payment_method === 'maya') {
            $paymongo_method = 'maya';
        }
        
        try {
        // Create payment intent
        $order_number = $order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
        $description = 'Order #' . $order_number;

        // Collect billing info from POST (if provided) and pass as metadata
        $billing_meta = [
            'order_id' => $order_id,
            'billing_firstname' => $this->input->post('billing_firstname') ?: null,
            'billing_lastname' => $this->input->post('billing_lastname') ?: null,
            'billing_email' => $this->input->post('billing_email') ?: null,
            'billing_phone' => $this->input->post('billing_phone') ?: null,
            'billing_street' => $this->input->post('billing_street') ?: null,
            'billing_subdivision' => $this->input->post('billing_subdivision') ?: null,
            'billing_barangay' => $this->input->post('billing_barangay') ?: null,
            'billing_city' => $this->input->post('billing_city') ?: null,
            'billing_province' => $this->input->post('billing_province') ?: null,
            'billing_region' => $this->input->post('billing_region') ?: null,
            'billing_zipcode' => $this->input->post('billing_zipcode') ?: null,
            'billing_country' => $this->input->post('billing_country') ?: null
        ];

        // Server-side validation: ensure billing city and province are present (or available via fallbacks)
        $billing_city = trim((string)$this->input->post('billing_city'));
        $billing_province = trim((string)$this->input->post('billing_province'));

        // Try to use saved addresses or order values as fallback
        if (empty($billing_city) || empty($billing_province)) {
            $this->load->model('User_model');
            $addresses = $this->User_model->get_addresses((int)$customer_id);
            $savedBilling = $addresses['Billing'] ?? null;
            $savedShipping = $addresses['Shipping'] ?? null;

            if (empty($billing_city)) {
                $billing_city = trim($savedBilling->City ?? $savedShipping->City ?? $order->City ?? '');
            }
            if (empty($billing_province)) {
                $billing_province = trim($savedBilling->Province ?? $savedShipping->Province ?? $order->Province ?? '');
            }
        }

        if (empty($billing_city) || empty($billing_province)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Billing city and province are required. Please provide both before proceeding with payment.',
                'missing' => [
                    'billing_city' => empty($billing_city),
                    'billing_province' => empty($billing_province)
                ]
            ]);
            return;
        }

        // Persist billing address to user's Billing address record (if available)
        $customer_id_for_addr = $order->Customer_ID ?? $this->session->userdata('customer_id');
        if (!empty($customer_id_for_addr)) {
            $this->load->model('User_model');
            $billing_unit = $this->input->post('billing_unit_house_number') ?: '';
            $billing_street = $this->input->post('billing_street') ?: '';
            $billing_subdivision = $this->input->post('billing_subdivision') ?: '';
            $billing_barangay = $this->input->post('billing_barangay') ?: '';
            $billing_city = $this->input->post('billing_city') ?: '';
            $billing_province = $this->input->post('billing_province') ?: '';
            $billing_region = $this->input->post('billing_region') ?: '';
            $billing_zip = $this->input->post('billing_zipcode') ?: '';
            $billing_country = $this->input->post('billing_country') ?: '';

            $address_line = implode(', ', array_filter([$billing_unit, $billing_street, $billing_subdivision]));
            $address_line2 = implode(', ', array_filter([$billing_barangay, $billing_city, $billing_province, $billing_region]));

            $billing_address_data = [
                'AddressLine' => trim($address_line) ?: null,
                'City' => $billing_city ?: null,
                'Province' => $billing_province ?: null,
                'Region' => $billing_region ?: null,
                'Country' => $billing_country ?: null,
                'ZipCode' => $billing_zip ?: null,
                // store components for compatibility with views that expect them
                'UnitHouseNumber' => $billing_unit ?: null,
                'Street' => $billing_street ?: null,
                'Subdivision' => $billing_subdivision ?: null,
                'Barangay' => $billing_barangay ?: null,
                'Note' => null,
                'IsDefault' => 0
            ];

            // Use User_model->update_address to upsert Billing address for the customer
            try {
                $this->User_model->update_address((int)$customer_id_for_addr, 'Billing', $billing_address_data);
            } catch (Exception $e) {
                log_message('error', 'Failed to persist billing address for customer ' . $customer_id_for_addr . ': ' . $e->getMessage());
            }
        }

        $result = $this->paymongo->create_payment_intent(
            $order->TotalAmount,
            $paymongo_method,
            $description,
            $billing_meta
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
        
        // Now add shipping contact info from POST data (done after initial insert/update to preserve data)
        $shipping_firstname = trim((string)$this->input->post('firstname'));
        $shipping_middlename = trim((string)$this->input->post('middlename'));
        $shipping_lastname = trim((string)$this->input->post('lastname'));
        $shipping_email = trim((string)$this->input->post('email'));
        $shipping_phone = trim((string)$this->input->post('phone'));
        $shipping_unit = trim((string)$this->input->post('unit_house_number'));
        $shipping_street = trim((string)$this->input->post('street'));
        $shipping_subdivision = trim((string)$this->input->post('subdivision'));
        $shipping_barangay = trim((string)$this->input->post('barangay'));
        $shipping_city = trim((string)$this->input->post('city'));
        $shipping_province = trim((string)$this->input->post('province'));
        $shipping_region = trim((string)$this->input->post('region'));
        $shipping_zipcode = trim((string)$this->input->post('zipcode'));
        $shipping_country = trim((string)$this->input->post('country'));
        
        $shipping_fullname = trim(($shipping_firstname . ' ' . $shipping_middlename . ' ' . $shipping_lastname));
        
        $shipping_data = [
            'shipping_name' => $shipping_fullname ?: null,
            'shipping_email' => $shipping_email ?: null,
            'shipping_phone' => $shipping_phone ?: null,
            'shipping_unit' => $shipping_unit ?: null,
            'shipping_street' => $shipping_street ?: null,
            'shipping_subdivision' => $shipping_subdivision ?: null,
            'shipping_barangay' => $shipping_barangay ?: null,
            'shipping_city' => $shipping_city ?: null,
            'shipping_province' => $shipping_province ?: null,
            'shipping_region' => $shipping_region ?: null,
            'shipping_postal_code' => $shipping_zipcode ?: null,
            'shipping_country' => $shipping_country ?: null
        ];
        
        // Update only shipping fields (preserves billing and other fields)
        $this->db->where('OrderID', $order_id)->update('payment', $shipping_data);
        
        // Build server-side canonical billing payload (authoritative) and persist into payment
        $billing_components = $this->build_paymongo_billing($order_id);

        // If the Payment page submitted explicit billing fields, prefer those exact values
        $client_billing = [
            'firstname' => trim((string)$this->input->post('billing_firstname')),
            'middlename' => trim((string)$this->input->post('billing_middlename')),
            'lastname' => trim((string)$this->input->post('billing_lastname')),
            'email' => trim((string)$this->input->post('billing_email')),
            'phone' => trim((string)$this->input->post('billing_phone')),
            'unit' => trim((string)$this->input->post('billing_unit_house_number')),
            'street' => trim((string)$this->input->post('billing_street')),
            'subdivision' => trim((string)$this->input->post('billing_subdivision')),
            'barangay' => trim((string)$this->input->post('billing_barangay')),
            'city' => trim((string)$this->input->post('billing_city')),
            'province' => trim((string)$this->input->post('billing_province')),
            'region' => trim((string)$this->input->post('billing_region')),
            'postal_code' => trim((string)$this->input->post('billing_zipcode')),
            'country' => trim((string)$this->input->post('billing_country'))
        ];

        $use_client_billing = false;
        foreach ($client_billing as $v) { if (!empty($v)) { $use_client_billing = true; break; } }

        if ($billing_components && isset($billing_components['paymongo_billing'])) {
            // Start with server-built components, but override with client-submitted values when present
            $billing_update = [
                'billing_name' => $billing_components['name'] ?? null,
                'billing_email' => $billing_components['email'] ?? null,
                'billing_phone' => $billing_components['phone'] ?? null,
                'billing_unit' => $billing_components['unit'] ?? null,
                'billing_street' => $billing_components['street'] ?? null,
                'billing_subdivision' => $billing_components['subdivision'] ?? null,
                'billing_barangay' => $billing_components['barangay'] ?? null,
                'billing_city' => $billing_components['city'] ?? null,
                'billing_province' => $billing_components['province'] ?? null,
                'billing_region' => $billing_components['region'] ?? null,
                'billing_postal_code' => $billing_components['postal_code'] ?? null,
                'billing_country' => $billing_components['country_full'] ?? null,
                'billing_country_iso' => $billing_components['country_iso'] ?? null,
                'billing_payload_json' => json_encode($billing_components['paymongo_billing'])
            ];

            if ($use_client_billing) {
                $client_fullname = trim(($client_billing['firstname'] . ' ' . $client_billing['middlename'] . ' ' . $client_billing['lastname'])) ?: ($billing_update['billing_name'] ?? null);
                // Map client fields to billing_update, preserving server values when client omitted
                $billing_update['billing_name'] = $client_fullname ?: $billing_update['billing_name'];
                $billing_update['billing_email'] = $client_billing['email'] ?: $billing_update['billing_email'];
                $billing_update['billing_phone'] = $client_billing['phone'] ?: $billing_update['billing_phone'];
                $billing_update['billing_unit'] = $client_billing['unit'] ?: $billing_update['billing_unit'];
                $billing_update['billing_street'] = $client_billing['street'] ?: $billing_update['billing_street'];
                $billing_update['billing_subdivision'] = $client_billing['subdivision'] ?: $billing_update['billing_subdivision'];
                $billing_update['billing_barangay'] = $client_billing['barangay'] ?: $billing_update['billing_barangay'];
                $billing_update['billing_city'] = $client_billing['city'] ?: $billing_update['billing_city'];
                $billing_update['billing_province'] = $client_billing['province'] ?: $billing_update['billing_province'];
                $billing_update['billing_region'] = $client_billing['region'] ?: $billing_update['billing_region'];
                $billing_update['billing_postal_code'] = $client_billing['postal_code'] ?: $billing_update['billing_postal_code'];
                $billing_update['billing_country'] = $client_billing['country'] ?: $billing_update['billing_country'];

                // Also replace billing_payload_json with the client-provided structure to guarantee exact display
                $client_line1 = trim(implode(', ', array_filter([$client_billing['unit'], $client_billing['street'], $client_billing['subdivision']])));
                $client_paymongo = [
                    'name' => $client_fullname ?: ($billing_components['name'] ?? ''),
                    'email' => $client_billing['email'] ?: ($billing_components['email'] ?? ''),
                    'phone' => $client_billing['phone'] ?: ($billing_components['phone'] ?? ''),
                    'address' => [
                        'line1' => $client_line1 ?: ($billing_components['paymongo_billing']['address']['line1'] ?? ''),
                        'city' => $client_billing['city'] ?: ($billing_components['paymongo_billing']['address']['city'] ?? ''),
                        'state' => $client_billing['province'] ?: ($billing_components['paymongo_billing']['address']['state'] ?? ''),
                        'postal_code' => $client_billing['postal_code'] ?: ($billing_components['paymongo_billing']['address']['postal_code'] ?? ''),
                        'country' => strtoupper(substr($client_billing['country'], 0, 2)) ?: ($billing_components['paymongo_billing']['address']['country'] ?? '')
                    ]
                ];
                $billing_update['billing_payload_json'] = json_encode($client_paymongo);
            }

            $this->db->where('OrderID', $order_id)->update('payment', $billing_update);
        }

        // Return payment init response; prefer client-supplied paymongo billing when available
        $response_billing = null;
        if (!empty($use_client_billing)) {
            $response_billing = isset($client_paymongo) ? $client_paymongo : ($billing_components['paymongo_billing'] ?? null);
        } else {
            $response_billing = $billing_components['paymongo_billing'] ?? null;
        }

        echo json_encode([
            'status' => 'success',
            'payment_intent_id' => $result['payment_intent_id'],
            'client_key' => $result['client_key'],
            'public_key' => $this->paymongo->get_public_key(),
            'billing' => $response_billing
        ]);
        } catch (Exception $e) {
            // Log and return structured JSON error instead of a 500 HTML page
            log_message('error', 'create_payment_intent exception: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            return;
        }
    }

    /**
     * Build canonical PayMongo billing payload from server-side authoritative values
     * Returns normalized components and 'paymongo_billing' ready to send to PayMongo
     */
    public function build_paymongo_billing($order_id)
    {
        $this->load->model('Order_model');
        $this->load->model('User_model');

        $order = $this->Order_model->get_order($order_id);
        if (!$order) return false;

        $customer_id = $order->Customer_ID ?? $this->session->userdata('customer_id');
        if (!$customer_id) return false;

        $addresses = $this->User_model->get_addresses((int)$customer_id);
        $addr = $addresses['Billing'] ?? null;

        // If no billing address saved, try shipping or order fields
        if (!$addr) {
            $addr = $addresses['Shipping'] ?? null;
        }

        $unit = trim($addr->UnitHouseNumber ?? '');
        $street = trim($addr->Street ?? '');
        $subd = trim($addr->Subdivision ?? '');
        $brgy = trim($addr->Barangay ?? '');

        $line1 = trim(implode(', ', array_filter([$unit, $street, $subd, $brgy])));
        $city = trim($addr->City ?? $order->City ?? '');
        $province = trim($addr->Province ?? $order->Province ?? '');
        $region = trim($addr->Region ?? '');
        $postal = trim($addr->ZipCode ?? $order->ZipCode ?? '');
        $country_full = trim($addr->Country ?? $order->Country ?? 'Philippines');

        // Try to map to ISO using existing helper if available
        if (method_exists($this, 'toIsoCountry')) {
            $country_iso = $this->toIsoCountry($country_full);
        } else {
            // fallback common mapping
            $map = ['Philippines' => 'PH', 'United States' => 'US'];
            $country_iso = $map[$country_full] ?? strtoupper(substr($country_full, 0, 2));
        }

        $name = trim((($addr->FirstName ?? '') . ' ' . ($addr->LastName ?? '')) ?: ($order->CustomerName ?? ''));
        $email = trim($addr->Email ?? $order->Email ?? '');
        $phone = trim($addr->Phone ?? $order->Phone ?? '');

        $billing_components = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'unit' => $unit,
            'street' => $street,
            'subdivision' => $subd,
            'barangay' => $brgy,
            'city' => $city,
            'province' => $province,
            'region' => $region,
            'postal_code' => $postal,
            'country_full' => $country_full,
            'country_iso' => $country_iso,
            'paymongo_billing' => [
                'name' => $name ?: '',
                'email' => $email ?: '',
                'phone' => $phone ?: '',
                'address' => [
                    'line1' => $line1 ?: '',
                    'city' => $city ?: '',
                    'state' => $province ?: '',
                    'postal_code' => $postal ?: '',
                    'country' => $country_iso ?: ''
                ]
            ]
        ];

        return $billing_components;
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
        
        // Check if this is a stage payment
        $stage = $this->input->post('stage'); // downpayment, fabrication, installation (optional)
        
        // Build return URL for e-wallet redirects
        $return_url = base_url('payment/complete?order_id=' . $order_id);
        if ($stage) {
            $return_url .= '&stage=' . urlencode($stage);
        }
        
        // Attach payment method
        $result = $this->paymongo->attach_payment_method($payment_intent_id, $payment_method_id, $return_url);
        
        if (!$result['success']) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to process payment: ' . ($result['error'] ?? 'Unknown error')
            ]);
            return;
        }
        
        // Determine the concrete PayMongo payment id (pay_...)
        $paymongo_pay_id = !empty($result['payment_id']) ? $result['payment_id'] : $payment_intent_id;
        
        // Check payment status
        if ($result['status'] === 'succeeded') {
            if ($stage) {
                // Stage payment succeeded immediately (card)
                $this->_complete_stage_payment($order_id, $stage, $paymongo_pay_id);
                echo json_encode([
                    'status' => 'success',
                    'payment_status' => 'succeeded',
                    'message' => ucfirst($stage) . ' payment successful!',
                    'redirect_url' => base_url('order-tracking?order=' . $order_id)
                ]);
            } else {
            // Regular order payment succeeded immediately (card payment)
            $this->db->where('OrderID', $order_id)->update('payment', [
                'Status' => 'Paid',
                'Payment_Date' => date('Y-m-d H:i:s')
            ]);
            
            // If PayMongo returned a concrete payment id (pay_...), store it so views show pay_ id instead of pi_
            if (!empty($result['payment_id'])) {
                $this->db->where('OrderID', $order_id)->update('payment', [
                    'Transaction_ID' => $result['payment_id']
                ]);
            }

            // Get order details to check if payment amount >= order total
            $this->load->model('Order_model');
            $order = $this->Order_model->get_order($order_id);
            $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
            
            // Check if payment amount meets or exceeds order total
            $payment_amount = $payment ? (float)($payment->Amount ?? 0) : 0;
            $order_total = $order ? (float)($order->TotalAmount ?? 0) : 0;
            
            // All orders follow unified flow - mark as Pending Review after payment
            $is_fully_paid = $payment_amount >= $order_total;
            
            // Update order status
            $this->db->where('OrderID', $order_id)->update('`order`', [
                'PaymentStatus' => 'Paid',
                'Status' => $is_fully_paid ? 'Pending Review' : 'Pending Payment' // Unified flow: goes to admin review after payment
            ]);

            // Ensure the order row records the concrete payment provider (GCash/Maya/Card/etc.)
            if ($payment && !empty($payment->PaymentMethod)) {
                $this->db->where('OrderID', $order_id)->update('`order`', [
                    'PaymentMethod' => ucfirst($payment->PaymentMethod)
                ]);
            }
            
            // Clear cart only after payment is successful
            $this->load->model('Cart_model');
            $this->Cart_model->clear_cart($customer_id);
            
            echo json_encode([
                'status' => 'success',
                'payment_status' => 'succeeded',
                'message' => 'Payment successful!',
                'redirect_url' => base_url('payment/complete?order_id=' . $order_id . '&payment_intent_id=' . $payment_intent_id)
            ]);
            }
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
     * Internal helper: mark a stage payment as complete
     * @param int $order_id
     * @param string $stage downpayment|fabrication|installation
     * @param string $transaction_id PayMongo payment/intent ID
     */
    private function _complete_stage_payment($order_id, $stage, $transaction_id)
    {
        $this->load->database();
        $this->load->model('Order_model');
        $now = date('Y-m-d H:i:s');

        $summary = $this->Order_model->calculate_order_summary($order_id);
        $total_amount = floatval($summary['total'] ?? 0);

        // Get payment method from session stage data or (prefer) PayMongo payment resource
        $stage_session = $this->session->userdata('stage_payment');
        $payment_method = $stage_session['payment_method'] ?? 'Card';

        // Try to get concrete payment resource from PayMongo to determine actual method
        $this->load->library('paymongo');
        $actual_method = null;
        // First try treating transaction_id as a payment id
        $pay_res = $this->paymongo->retrieve_payment($transaction_id);
        if (empty($pay_res['success'])) {
            // If that failed, try to treat transaction_id as a payment_intent and get linked payment id
            $pi_res = $this->paymongo->retrieve_payment_intent($transaction_id);
            if (!empty($pi_res['success']) && !empty($pi_res['payment_id'])) {
                $pay_res = $this->paymongo->retrieve_payment($pi_res['payment_id']);
            }
        }

        if (!empty($pay_res['success']) && !empty($pay_res['payment'])) {
            $p = $pay_res['payment'];
            // Try common locations for payment method type
            if (!empty($p['attributes']['payment_method_details']['type'])) {
                $detected = $p['attributes']['payment_method_details']['type'];
            } elseif (!empty($p['attributes']['type'])) {
                $detected = $p['attributes']['type'];
            } else {
                $detected = null;
            }

            if (empty($detected)) {
                // Fallback: search JSON for known keywords
                $json = json_encode($p);
                if (stripos($json, 'gcash') !== false) $detected = 'gcash';
                elseif (stripos($json, 'paymaya') !== false || stripos($json, 'maya') !== false) $detected = 'paymaya';
                elseif (stripos($json, 'card') !== false) $detected = 'card';
            }

            if (!empty($detected)) {
                // Normalize to display values
                if (stripos($detected, 'gcash') !== false) $actual_method = 'GCash';
                elseif (stripos($detected, 'paymaya') !== false || stripos($detected, 'maya') !== false) $actual_method = 'Maya';
                elseif (stripos($detected, 'card') !== false) $actual_method = 'Card';
                else $actual_method = ucfirst($detected);
            }
        }

        // If PayMongo lookup succeeded, prefer it; otherwise use session value
        if (!empty($actual_method)) {
            $payment_method = $actual_method;
        }

        if ($stage === 'downpayment') {
            $amount = round($total_amount * 0.5, 2);
            // Use admin-set amount if available
            $dp = $this->db->where('OrderID', $order_id)->get('payment')->row();
            if ($dp && floatval($dp->Amount) > 0) {
                $amount = floatval($dp->Amount);
            }
            
            // Map payment method to proper display name
            $display_method = $payment_method;
            switch (strtolower($payment_method)) {
                case 'gcash':
                    $display_method = 'GCash';
                    break;
                case 'maya':
                    $display_method = 'Maya';
                    break;
                case 'card':
                    $display_method = 'Credit/Debit Card';
                    break;
                default:
                    $display_method = ucfirst($payment_method);
                    break;
            }

            $payment_data = [
                'PaymentMethod' => $display_method,
                'Amount' => $amount,
                'Status' => 'Paid',
                'Payment_Date' => $now,
                'Transaction_ID' => $transaction_id
            ];

            if ($dp) {
                $this->db->where('Payment_ID', $dp->Payment_ID)->update('payment', $payment_data);
            } else {
                $payment_data['OrderID'] = $order_id;
                $order = $this->db->where('OrderID', $order_id)->get('`order`')->row();
                $payment_data['CustomerName'] = ($order->First_Name ?? '') . ' ' . ($order->Last_Name ?? '');
                $this->db->insert('payment', $payment_data);
            }

            $this->db->where('OrderID', $order_id)->update('`order`', [
                'PaymentStatus' => 'Partial',
                'PaymentMethod' => $display_method
            ]);

        } elseif ($stage === 'fabrication') {
            $amount = round($total_amount * 0.4, 2);
            $fab_apt = $this->db->where('OrderID', $order_id)->where('Service', 'In Fabrication')->get('appointments')->row();
            if ($fab_apt && !empty($fab_apt->FabricationPaymentAmount)) {
                $amount = floatval($fab_apt->FabricationPaymentAmount);
            }
            
            // Map payment method to proper display name
            $display_method = $payment_method;
            switch (strtolower($payment_method)) {
                case 'gcash':
                    $display_method = 'GCash';
                    break;
                case 'maya':
                    $display_method = 'Maya';
                    break;
                case 'card':
                    $display_method = 'Credit/Debit Card';
                    break;
                default:
                    $display_method = ucfirst($payment_method);
                    break;
            }

            $fab_data = [
                'FabricationPaymentAmount' => $amount,
                'FabricationPaymentMethod' => $display_method,
                'FabricationPaymentStatus' => 'Paid',
            ];
            if ($fab_apt) {
                $this->db->where('AppointmentID', $fab_apt->AppointmentID)->update('appointments', $fab_data);
            }
            if ($this->db->field_exists('FabricationPaymentAmount', 'order')) {
                $order_fab = $fab_data;
                // Store transaction ID in order table if column exists
                if ($this->db->field_exists('FabricationTransactionID', 'order')) {
                    $order_fab['FabricationTransactionID'] = $transaction_id;
                }
                $this->db->where('OrderID', $order_id)->update('`order`', $order_fab);
            }

        } elseif ($stage === 'installation') {
            $amount = round($total_amount * 0.1, 2);
            $order_row = $this->db->where('OrderID', $order_id)->get('`order`')->row();
            if (!empty($order_row->InstallationPaymentAmount)) {
                $amount = floatval($order_row->InstallationPaymentAmount);
            }
            
            // Map payment method to proper display name
            $display_method = $payment_method;
            switch (strtolower($payment_method)) {
                case 'gcash':
                    $display_method = 'GCash';
                    break;
                case 'maya':
                    $display_method = 'Maya';
                    break;
                case 'card':
                    $display_method = 'Credit/Debit Card';
                    break;
                default:
                    $display_method = ucfirst($payment_method);
                    break;
            }

            if ($this->db->field_exists('InstallationPaymentAmount', 'order')) {
                $inst_data = [
                    'InstallationPaymentAmount' => $amount,
                    'InstallationPaymentMethod' => $display_method,
                    'InstallationPaymentStatus' => 'Paid',
                ];
                if ($this->db->field_exists('InstallationTransactionID', 'order')) {
                    $inst_data['InstallationTransactionID'] = $transaction_id;
                }
                $this->db->where('OrderID', $order_id)->update('`order`', $inst_data);
            }
            
            // Mark installation appointment as COMPLETE and clear payment due date
            $installation_apt = $this->db->where('OrderID', $order_id)
                                         ->where('Service', 'Installed')
                                         ->get('appointments')->row();
            if ($installation_apt) {
                $apt_update = [
                    'Status' => 'Complete',
                    'Updated_Date' => date('Y-m-d H:i:s')
                ];
                if ($this->db->field_exists('PaymentDueDate', 'appointments')) {
                    $apt_update['PaymentDueDate'] = null; // Clear deadline when paid
                }
                $this->db->where('AppointmentID', $installation_apt->AppointmentID)
                         ->update('appointments', $apt_update);
                         
                log_message('debug', "Installation payment completed - marked appointment {$installation_apt->AppointmentID} as Complete for order {$order_id}");
                
                // Send completion notification to customer
                try {
                    $this->load->helper('notification');
                    if (function_exists('send_order_notification') && $order_row && $order_row->Customer_ID) {
                        $order_number = $order_row->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
                        send_order_notification(
                            $order_row->Customer_ID,
                            $order_id,
                            'Payment Complete - Order Finished!',
                            "🎉 Thank you! Your final payment of ₱" . number_format($amount, 2) . " for order #{$order_number} has been received. Your order is now complete!",
                            'fa-check-circle',
                            null
                        );
                    }
                } catch (Exception $e) {
                    log_message('error', 'Failed to send installation payment completion notification: ' . $e->getMessage());
                }

                // Auto-update order status to Completed when installation appointment is marked Complete
                try {
                    $this->load->model('Order_model');
                    $updated = $this->Order_model->update_order_status($order_id, 'Completed');
                    if ($updated) {
                        log_message('info', "Order {$order_id} status auto-updated to Completed due to Installed appointment completion");
                    } else {
                        log_message('warning', "Failed to auto-update Order {$order_id} status to Completed");
                    }
                } catch (Exception $e) {
                    log_message('error', 'Exception while auto-updating order status after installation completion: ' . $e->getMessage());
                }
            }

            // Check if all 3 stages paid → mark fully Paid
            $dp_paid = $this->db->where('OrderID', $order_id)->where('Status', 'Paid')->get('payment')->row();
            $fab_check = $this->db->where('OrderID', $order_id)->where('Service', 'In Fabrication')->get('appointments')->row();
            $fab_paid = ($fab_check && ($fab_check->FabricationPaymentStatus ?? '') === 'Paid');
            if ($dp_paid && $fab_paid) {
                $this->db->where('OrderID', $order_id)->update('`order`', ['PaymentStatus' => 'Paid']);
            }
        }

        // Clear stage session data
        $this->session->unset_userdata('stage_payment');
    }

    /**
     * Handle payment completion (return from PayMongo)
     * STEP 6 - Return From PayMongo
     */
    public function payment_complete()
    {
        $order_id = $this->input->get('order_id');
        $payment_intent_id = $this->input->get('payment_intent_id');
        $stage = $this->input->get('stage'); // downpayment, fabrication, installation (for stage payments)
        
        if (!$order_id) {
            show_error('Order ID is required.');
            return;
        }
        
        // Load PayMongo library
        $this->load->library('paymongo');
        
        // For stage payments, get the payment intent from session if not in URL
        if ($stage && !$payment_intent_id) {
            $stage_session = $this->session->userdata('stage_payment');
            if ($stage_session && ($stage_session['order_id'] ?? null) == $order_id) {
                $payment_intent_id = $stage_session['payment_intent_id'] ?? null;
            }
        }
        
        // Get payment intent ID from URL or database
        if (!$payment_intent_id && !$stage) {
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
                $paymongo_pay_id = !empty($result['payment_id']) ? $result['payment_id'] : $payment_intent_id;
                
                if ($stage) {
                    // Stage payment e-wallet return — process stage completion
                    $this->_complete_stage_payment($order_id, $stage, $paymongo_pay_id);
                    // Redirect to order tracking page
                    redirect('order-tracking?order=' . $order_id);
                    return;
                }
                
                // Regular order payment verification
                $this->load->database();
                
                // Get order details to check if payment amount >= order total
                $this->load->model('Order_model');
                $order = $this->Order_model->get_order($order_id);
                $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
                
                // Check if payment amount meets or exceeds order total
                $payment_amount = $payment ? (float)($payment->Amount ?? 0) : 0;
                $order_total = $order ? (float)($order->TotalAmount ?? 0) : 0;
                
                // All orders follow unified flow - mark as Pending Review after payment
                $is_fully_paid = $payment_amount >= $order_total;
                
                $this->db->where('OrderID', $order_id)->update('payment', [
                    'Status' => 'Paid',
                    'Payment_Date' => date('Y-m-d H:i:s')
                ]);

                // If PayMongo returned a concrete payment id (pay_...), store it to Transaction_ID so UI shows pay_ id
                if (!empty($result['payment_id'])) {
                    $this->db->where('OrderID', $order_id)->update('payment', [
                        'Transaction_ID' => $result['payment_id']
                    ]);

                    // Try fetching the payment resource to determine actual provider (GCash/Card/Maya)
                    $pay_info = $this->paymongo->retrieve_payment($result['payment_id']);
                    if (!empty($pay_info['success']) && !empty($pay_info['payment'])) {
                        $p = $pay_info['payment'];
                        $detected = null;
                        if (!empty($p['attributes']['payment_method_details']['type'])) {
                            $detected = $p['attributes']['payment_method_details']['type'];
                        } elseif (!empty($p['attributes']['type'])) {
                            $detected = $p['attributes']['type'];
                        } else {
                            $json = json_encode($p);
                            if (stripos($json, 'gcash') !== false) $detected = 'gcash';
                            elseif (stripos($json, 'paymaya') !== false || stripos($json, 'maya') !== false) $detected = 'paymaya';
                            elseif (stripos($json, 'card') !== false) $detected = 'card';
                        }

                        if (!empty($detected)) {
                            if (stripos($detected, 'gcash') !== false) $pm = 'GCash';
                            elseif (stripos($detected, 'paymaya') !== false || stripos($detected, 'maya') !== false) $pm = 'Maya';
                            elseif (stripos($detected, 'card') !== false) $pm = 'Card';
                            else $pm = ucfirst($detected);
                            // Update payment table and order with concrete provider
                            $this->db->where('OrderID', $order_id)->update('payment', ['PaymentMethod' => $pm]);
                            $this->db->where('OrderID', $order_id)->update('`order`', ['PaymentMethod' => $pm]);
                        }
                    }
                }

                // If fully paid, mark order status as Pending Review (unified flow)
                $this->db->where('OrderID', $order_id)->update('`order`', [
                    'PaymentStatus' => 'Paid',
                    'Status' => $is_fully_paid ? 'Pending Review' : 'Pending Payment' // Unified flow: goes to admin review
                ]);

                // Ensure the order row records the concrete payment provider (GCash/Maya/Card/etc.)
                $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();
                if ($payment && !empty($payment->PaymentMethod)) {
                    $actual_method = ucfirst($payment->PaymentMethod);
                    $this->db->where('OrderID', $order_id)->update('`order`', [
                        'PaymentMethod' => $actual_method
                    ]);
                    // Also update fabrication and installation payment method if status is Paid
                    if ($order && $order->FabricationPaymentStatus === 'Paid') {
                        $this->db->where('OrderID', $order_id)->update('`order`', [
                            'FabricationPaymentMethod' => $actual_method
                        ]);
                        $fab_apt = $this->db->where('OrderID', $order_id)->where('Service', 'In Fabrication')->get('appointments')->row();
                        if ($fab_apt) {
                            $this->db->where('AppointmentID', $fab_apt->AppointmentID)->update('appointments', [
                                'FabricationPaymentMethod' => $actual_method
                            ]);
                        }
                    }
                    if ($order && $order->InstallationPaymentStatus === 'Paid') {
                        $this->db->where('OrderID', $order_id)->update('`order`', [
                            'InstallationPaymentMethod' => $actual_method
                        ]);
                        $inst_apt = $this->db->where('OrderID', $order_id)->where('Service', 'Installed')->get('appointments')->row();
                        if ($inst_apt) {
                            $this->db->where('AppointmentID', $inst_apt->AppointmentID)->update('appointments', [
                                'InstallationPaymentMethod' => $actual_method
                            ]);
                        }
                    }
                }
                
                // Clear cart only after payment is verified
                $customer_id = $this->session->userdata('customer_id');
                if ($customer_id) {
                    $this->load->model('Cart_model');
                    $this->Cart_model->clear_cart($customer_id);
                }
            }
        }
        
        // Load order details for success page and include customer info
        $this->load->model('Order_model');
        $order = $this->Order_model->get_order_with_customer($order_id);

        if (!$order) {
            show_error('Order not found.');
            return;
        }

        // Get payment details
        $this->load->database();
        $payment = $this->db->where('OrderID', $order_id)->get('payment')->row();

        // Populate addresses and user info for the view (same as in complete())
        $this->load->model('User_model');
        $data['shipping_address'] = null;
        $data['billing_address'] = null;
        $data['user'] = null;
        $customer_id = $order->Customer_ID ?? null;
        if ($customer_id) {
            $addresses = $this->User_model->get_addresses($customer_id);
            $data['shipping_address'] = $addresses['Shipping'] ?? null;
            $data['billing_address'] = $addresses['Billing'] ?? null;
            $data['user'] = $this->User_model->get_by_id($customer_id);
        }

        $data['title'] = 'Payment Complete';
        $data['order'] = $order;
        $data['payment'] = $payment;
        $data['payment_status'] = $result['status'] ?? 'unknown';
        // Order summary for totals (items, subtotal, shipping, total)
        $data['summary'] = $this->Order_model->calculate_order_summary($order_id);
        // Preserve payment origin for navigation
        $data['payment_origin'] = $this->session->userdata('payment_origin') ?: 'cart';
        
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

        // All orders now follow unified flow - no order type check needed

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

        // Get filter parameter (all, to_receive / ongoing, completed, cancelled)
        $filter = $this->input->get('filter') ?: 'all';
        // Accept 'ongoing' (friendly) but map to 'to_receive' which means ongoing in the model
        if ($filter === 'ongoing') {
            $filter = 'to_receive';
        }
        $valid_filters = ['all', 'to_receive', 'completed', 'cancelled'];
        if (!in_array($filter, $valid_filters)) {
            $filter = 'all';
        }
        // Preserve the original requested filter display value for the view
        $data['current_filter'] = $this->input->get('filter') ?: $filter;

        // Load Order model
        $this->load->model('Order_model');

        // Pagination setup
        $per_page = 10;
        $page = (int)($this->input->get('page') ?: 1);
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $per_page;

        // Get total count for pagination
        $total_items = $this->Order_model->get_customer_order_items_count($customer_id, $filter);
        
        // Get customer's order items (purchases) from database with filter and pagination
        $data['order_items'] = $this->Order_model->get_customer_order_items($customer_id, $filter, $per_page, $offset);

        // Ensure each item has a user-friendly OrderStatus for display.
        // Some orders (especially site-assessment flows) rely on appointments/projects
        // therefore Status on the order row may be empty. Derive a readable status
        // from the order progress when missing.
        if (!empty($data['order_items'])) {
            foreach ($data['order_items'] as $idx => $itm) {
                $current_status = trim((string)($itm->OrderStatus ?? ''));
                if ($current_status === '') {
                    $progress = $this->Order_model->get_order_progress('', $itm->OrderID ?? null);
                    $derived = '';

                    // Determine product-level order type if available
                    $prod_order_type = strtolower(trim($itm->ProductOrderType ?? ''));
                    $is_site_assessed = (strpos($prod_order_type, 'site') !== false) || (strpos($prod_order_type, 'assessment') !== false) || (strpos($prod_order_type, 'site-assessed') !== false) || (strpos($prod_order_type, 'site-assessment') !== false);

                    if (!empty($progress) && is_array($progress)) {
                        if ($is_site_assessed) {
                            // Site-Assessed Orders: Booking Requested, Ocular Visit, In Fabrication
                            if (isset($progress['ocular_visit']) && $progress['ocular_visit'] === 'pending') {
                                $derived = 'Booking Requested';
                            } elseif (isset($progress['ocular_visit']) && $progress['ocular_visit'] === 'in_progress') {
                                $derived = 'Ocular Visit';
                            } elseif (isset($progress['in_fabrication']) && $progress['in_fabrication'] === 'in_progress') {
                                $derived = 'In Fabrication';
                            } elseif (isset($progress['completed']) && $progress['completed'] === 'completed') {
                                $derived = 'Completed';
                            }
                        } else {
                            // Direct Orders: prefer Order Placed by default unless fabrication/completion detected
                            if (isset($progress['in_fabrication']) && $progress['in_fabrication'] === 'in_progress') {
                                $derived = 'In Fabrication';
                            } elseif (isset($progress['completed']) && $progress['completed'] === 'completed') {
                                $derived = 'Completed';
                            } else {
                                // Default to 'Order Placed' when no further progress found
                                $derived = 'Order Placed';
                            }
                        }
                    }

                    // If we could derive a status, attach it back to the item
                    if ($derived !== '') {
                        $data['order_items'][$idx]->OrderStatus = $derived;
                    }
                }
            }
        }
        
        // Pagination data
        $data['total_items'] = $total_items;
        $data['per_page'] = $per_page;
        $data['current_page'] = $page;
        $data['total_pages'] = ceil($total_items / $per_page);

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
        $new_time = $this->input->post('new_time');
        
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
        
        // Record the request in activity log and notify admins for approval
        $this->load->helper('notification');
        $order_number = $order->OrderNumber ?? 'GI' . str_pad($order_id, 3, '0', STR_PAD_LEFT);

        // Get customer name
        $this->db->select('u.First_Name, u.Last_Name');
        $this->db->from('customer c');
        $this->db->join('user u', 'u.UserID = c.UserID', 'left');
        $this->db->where('c.Customer_ID', $customer_id);
        $customer_user = $this->db->get()->row();
        $customer_name = trim(($customer_user->First_Name ?? '') . ' ' . ($customer_user->Last_Name ?? ''));

        $description = "Customer {$customer_name} (ID: {$customer_id}) requested installation date change for order #{$order_number} to {$new_date}";
        if (!empty($new_time)) {
            $description .= " at {$new_time}";
        }

        // Insert into system activity log if table exists
        if ($this->db->table_exists('system_activity_log')) {
            $this->db->insert('system_activity_log', [
                'Action' => 'Installation Date Change Request',
                'Description' => $description,
                'Role' => 'Customer',
                'UserName' => $customer_name,
                'Timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        // Notify admin: send emails to active admins
        try {
            $this->db->reset_query();
            $admins = $this->db->select('Email, First_Name, Last_Name')
                               ->from('user')
                               ->where('Role', 'Admin')
                               ->where('Status', 'Active')
                               ->get()
                               ->result();

            if (!empty($admins)) {
                $this->load->library('email');
                $this->email->set_mailtype('html');
                $from_email = config_item('smtp_user') ?: 'no-reply@localhost';
                $from_name = config_item('site_name') ?: 'Glassify';
                $this->email->from($from_email, $from_name);

                foreach ($admins as $admin) {
                    if (empty($admin->Email)) continue;
                    $to = $admin->Email;
                    $subject = "Installation Date Change Request - Order {$order_number}";
                    $body = '<p>' . htmlspecialchars($description) . '</p>';
                    $body .= '<p><a href="' . site_url('admin-orders?order_id=' . $order_id) . '">Open order in admin panel</a></p>';

                    $this->email->clear(TRUE);
                    $this->email->to($to);
                    $this->email->subject($subject);
                    $this->email->message($body);
                    if (!$this->email->send()) {
                        log_message('error', 'Failed to send admin notification email to: ' . $to . ' | ' . print_r($this->email->print_debugger(['headers','subject','body']), true));
                    } else {
                        log_message('info', 'Admin notification email sent to: ' . $to . ' for order ' . $order_number);
                    }
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Error sending admin notification emails: ' . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Date change request submitted successfully and is pending admin approval.'
        ]);
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