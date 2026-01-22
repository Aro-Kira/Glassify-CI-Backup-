<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CartCon extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('Cart_model');
    }

    // ===================== ADD TO CART =====================
    public function add($product_id = null)
    {
        if ($product_id === null) {
            show_404();
            return;
        }

        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            $this->session->set_flashdata('redirect_back', current_url());
            redirect('login');
            return;
        }

        $data = [
            'Customer_ID' => $customer_id,
            'Product_ID' => $product_id,
            'CustomizationID' => null,
            'Quantity' => 1
        ];

        $this->Cart_model->add_to_cart($data);
        redirect('cart-page');
    }

    // ===================== ADD CUSTOMIZED ITEM =====================
    public function add_customized()
    {
        // 1️⃣ Get customer_id from session
        $customer_id = $this->session->userdata('customer_id'); // use customer_id everywhere
        if (!$customer_id) {
            $this->session->set_flashdata('error', 'Please log in to add items.');
            redirect('login');
            return;
        }

        // 2️⃣ Get POST data
        $post = $this->input->post();

        // Optional: log POST data for debugging
        file_put_contents('debug.log', "POST DATA:\n" . print_r($post, true), FILE_APPEND);

        // 3️⃣ Prepare customization data
        $custom_data = [
            'Customer_ID' => $customer_id,
            'Product_ID' => $post['product_id'] ?? null,
            'Dimensions' => $post['dimensions'] ?? null,
            'GlassShape' => $post['shape'] ?? null,
            'GlassType' => $post['type'] ?? null,
            'GlassThickness' => $post['thickness'] ?? null,
            'EdgeWork' => $post['edge'] ?? null,
            'FrameType' => $post['frame'] ?? null,
            'Engraving' => $post['engraving'] ?? null,
            'DesignRef' => $post['design_ref'] ?? null,
            'EstimatePrice' => $post['price'] ?? 0
        ];

        // 4️⃣ Save customization
        $customization_id = $this->Cart_model->save_customization($custom_data);

        if (!$customization_id) {
            $this->session->set_flashdata('error', 'Failed to save customization.');
            redirect('product/' . $post['product_id']);
            return;
        }

        // 5️⃣ Prepare cart data
        $cart_data = [
            'Customer_ID' => $customer_id,
            'Product_ID' => $post['product_id'] ?? null,
            'CustomizationID' => $customization_id,
            'Quantity' => $post['quantity'] ?? 1
        ];

        // Optional: log cart data for debugging
        file_put_contents('debug.log', "CART DATA:\n" . print_r($cart_data, true), FILE_APPEND);

        // 6️⃣ Add to cart
        $this->Cart_model->add_to_cart($cart_data);

        // 7️⃣ Redirect to cart page
        $this->session->set_flashdata('success', 'Customized item added to cart!');
        redirect('cart-page');
    }

    public function add_customized_ajax()
    {
        // Set JSON header
        header('Content-Type: application/json');
        
        try {
            $customer_id = $this->session->userdata('customer_id');
            if (!$customer_id) {
                log_message('error', 'add_customized_ajax: No customer_id in session');
                echo json_encode(['status' => 'error', 'message' => 'User not logged in. Please log in and try again.']);
                return;
            }

            $post = $this->input->post();
            
            // Debug: Log received data
            log_message('debug', 'add_customized_ajax received POST data: ' . print_r($post, true));
            log_message('debug', 'add_customized_ajax customer_id: ' . $customer_id);
            
            // Validate required fields
            if (empty($post['product_id'])) {
                log_message('error', 'add_customized_ajax: Missing product_id in POST data');
                echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
                return;
            }
            
            $design_ref = null;

            // 1️⃣ Handle design image upload (base64 to file)
            if (!empty($post['design_image'])) {
                $design_ref = $this->save_design_image($post['design_image'], $customer_id);
            }

            // 2️⃣ Prepare customization data
            // Parse customization JSON if provided (contains all dynamic fields from admin)
            $customization_json = null;
            if (!empty($post['customization'])) {
                // If it's already a string, use it; otherwise encode it
                $customization_json = is_string($post['customization']) ? $post['customization'] : json_encode($post['customization']);
            }
            
            $custom_data = [
                'Customer_ID' => $customer_id,
                'Product_ID' => intval($post['product_id']),
                'Dimensions' => $post['dimensions'] ?? null,
                'GlassShape' => $post['shape'] ?? null,
                'GlassType' => $post['type'] ?? null,
                'GlassThickness' => $post['thickness'] ?? null,
                'EdgeWork' => $post['edge'] ?? null,
                'FrameType' => $post['frame'] ?? null,
                'Engraving' => $post['engraving'] ?? null,
                'DesignRef' => $design_ref,
                'EstimatePrice' => $this->clean_price($post['price'] ?? 0),
                'PriceBreakdown' => $post['price_breakdown'] ?? null,
                // Store all dynamic customization fields as JSON (synced with admin side)
                'Customization' => $customization_json
            ];

            // 3️⃣ Save customization
            $this->load->model('Cart_model');
            $customization_id = $this->Cart_model->save_customization($custom_data);

            if (!$customization_id) {
                // Get detailed error message from model
                $model_error = $this->Cart_model->get_last_error();
                $db_error = $this->db->error();
                
                $error_msg = 'Failed to save customization';
                if ($model_error) {
                    $error_msg .= ': ' . $model_error;
                } elseif ($db_error['code'] != 0) {
                    $error_msg .= ': ' . $db_error['message'];
                }
                
                // Log detailed information for debugging
                log_message('error', 'Customization save failed. Customer_ID: ' . $customer_id . ', Product_ID: ' . ($post['product_id'] ?? 'not set'));
                log_message('error', 'Custom data: ' . print_r($custom_data, true));
                
                echo json_encode(['status' => 'error', 'message' => $error_msg]);
                return;
            }

            // 4️⃣ Add to cart
            $cart_data = [
                'Customer_ID' => $customer_id,
                'Product_ID' => intval($post['product_id']),
                'CustomizationID' => $customization_id,
                'Quantity' => intval($post['quantity'] ?? 1)
            ];

            $cart_id = $this->Cart_model->add_to_cart($cart_data);
            
            if (!$cart_id) {
                $db_error = $this->db->error();
                $error_msg = 'Failed to add item to cart';
                if ($db_error['code'] != 0) {
                    $error_msg .= ': ' . $db_error['message'];
                    log_message('error', 'Add to cart error: ' . $db_error['message']);
                }
                echo json_encode(['status' => 'error', 'message' => $error_msg]);
                return;
            }

            // 5️⃣ Return updated cart info
            $cart_items = $this->Cart_model->get_cart_items($customer_id);
            $cart_count = count($cart_items);

            echo json_encode([
                'status' => 'success',
                'message' => 'Customized item added to cart',
                'customization_id' => $customization_id,
                'cart_id' => $cart_id,
                'cart_count' => $cart_count,
                'design_ref' => $design_ref
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Exception in add_customized_ajax: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            echo json_encode([
                'status' => 'error', 
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save base64 design image to file
     * Preserves the original image format (png, jpeg, gif, webp) from the data URL
     */
    private function save_design_image($base64_data, $customer_id)
    {
        try {
            // Create designs directory if it doesn't exist
            $upload_dir = FCPATH . 'uploads/designs/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    log_message('error', 'Failed to create uploads/designs directory');
                    return null;
                }
            }

            // Check if directory is writable
            if (!is_writable($upload_dir)) {
                log_message('error', 'Uploads/designs directory is not writable');
                return null;
            }

            // Extract image format from data URL (default to png if not found)
            $extension = 'png';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_data, $matches)) {
                $format = strtolower($matches[1]);
                // Map common formats to file extensions
                $format_map = [
                    'jpeg' => 'jpg',
                    'jpg' => 'jpg',
                    'png' => 'png',
                    'gif' => 'gif',
                    'webp' => 'webp',
                    'svg+xml' => 'svg'
                ];
                $extension = isset($format_map[$format]) ? $format_map[$format] : 'png';
            }

            // Remove data URL prefix if present
            if (strpos($base64_data, 'data:image') === 0) {
                $base64_data = preg_replace('/^data:image\/[\w+]+;base64,/', '', $base64_data);
            }

            // Decode base64 data
            $image_data = base64_decode($base64_data);
            if ($image_data === false) {
                log_message('error', 'Failed to decode base64 image data');
                return null;
            }

            // Generate unique filename with correct extension
            $filename = 'design_' . $customer_id . '_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $upload_dir . $filename;

            // Save image file
            if (file_put_contents($filepath, $image_data) === false) {
                log_message('error', 'Failed to save design image to: ' . $filepath);
                return null;
            }

            return 'uploads/designs/' . $filename;
        } catch (Exception $e) {
            log_message('error', 'Exception in save_design_image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean price string to decimal
     */
    private function clean_price($price)
    {
        if (is_numeric($price)) {
            return floatval($price);
        }
        // Remove currency symbols and commas
        $cleaned = preg_replace('/[^0-9.]/', '', $price);
        return floatval($cleaned);
    }

    // ===================== SAVE BUY NOW CUSTOMIZATION =====================
    // This method clears existing customization for customer and saves complete order details
    public function save_buy_now_customization()
    {
        $customer_id = $this->session->userdata('customer_id');
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            return;
        }

        $post = $this->input->post();

        // 1. Clear existing customization data for this customer from unified customization table (before order is created)
        // Note: In optimized schema, we typically don't delete customizations on buy now
        // They remain linked to orders via order_items. This deletion step may not be needed.
        // Keeping for backward compatibility but can be removed if causing issues.
        // $this->db->where('Customer_ID', $customer_id);
        // $this->db->delete('customization');

        // 2. Prepare complete order details
        // Handle file paths - can be single path or JSON array
        $file_attached = null;
        if (!empty($post['file_paths'])) {
            $file_paths = json_decode($post['file_paths'], true);
            if (is_array($file_paths) && !empty($file_paths)) {
                // Store as JSON if multiple files, or single path if one file
                $file_attached = count($file_paths) > 1 ? json_encode($file_paths) : $file_paths[0];
            } else {
                $file_attached = $post['file_attached'] ?? null;
            }
        } else {
            $file_attached = $post['file_attached'] ?? null;
        }
        
        $custom_data = [
            'Customer_ID' => $customer_id,
            'Product_ID' => intval($post['product_id'] ?? 0),
            'ProductName' => $post['product_name'] ?? null, // Store product name
            'Dimensions' => $post['dimensions'] ?? null, // JSON format
            'GlassShape' => $post['shape'] ?? null,
            'GlassType' => $post['type'] ?? null,
            'GlassThickness' => $post['thickness'] ?? null,
            'EdgeWork' => $post['edge_work'] ?? null,
            'FrameType' => $post['frame_type'] ?? null,
            'Engraving' => $post['engraving'] ?? null,
            'DesignRef' => $file_attached, // Store file path(s) in DesignRef
            'EstimatePrice' => floatval($post['total_quotation'] ?? 0),
            'TotalQuotation' => floatval($post['total_quotation'] ?? 0), // Store total quotation
            'OrderID' => null, // Will be set when order is created
            'DeliveryAddress' => null, // Will be set when order is created
            'OrderDate' => null, // Will be set when order is created
            'PriceBreakdown' => $post['price_breakdown'] ?? null,
            'Customization' => $post['customization'] ?? null
        ];
        
        // Store quantity in session for later use if needed (customization table might not have it)
        $this->session->set_userdata('buy_now_quantity', intval($post['quantity'] ?? 1));
        
        // If FileAttached field exists, also store there
        if ($this->db->field_exists('FileAttached', 'customization')) {
            $custom_data['FileAttached'] = $file_attached;
        }

        // 3. Save new customization record
        $customization_id = $this->Cart_model->save_customization($custom_data);

        if (!$customization_id) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save order details']);
            return;
        }

        // Store customization ID in session for order creation
        $this->session->set_userdata('buy_now_customization_id', $customization_id);

        echo json_encode([
            'status' => 'success',
            'message' => 'Order details saved',
            'customization_id' => $customization_id
        ]);
    }




    // ===================== SHOW CART PAGE =====================
    public function cart_page()
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
        
        $customer_id = $this->session->userdata('customer_id');
        
        // Set cache control headers for customer pages
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');

        $cart_items = $this->Cart_model->get_cart_items($customer_id);
        
        // Fetch user data for quotation (customer table only has Customer_ID and UserID,
        // but we need First_Name, Email, PhoneNum, Address from the user table)
        $user_id = $this->session->userdata('user_id');
        $this->load->model('User_model');
        $customer = $this->User_model->get_by_id($user_id);
        
        // Fallback if user not found
        if (!$customer) {
            $customer = (object)[
                'First_Name' => '',
                'Middle_Name' => '',
                'Last_Name' => '',
                'Email' => '',
                'PhoneNum' => '',
                'Address' => ''
            ];
        } else {
            // Fetch address from user_address table (get Shipping address by default)
            $addresses = $this->User_model->get_user_addresses($user_id);
            $full_address = '';
            
            if (!empty($addresses)) {
                // Find shipping address first, fallback to any address
                $addr = null;
                foreach ($addresses as $a) {
                    if ($a->AddressType === 'Shipping') {
                        $addr = $a;
                        break;
                    }
                }
                // If no shipping address, use first available
                if (!$addr && count($addresses) > 0) {
                    $addr = $addresses[0];
                }
                
                if ($addr) {
                    $address_parts = array_filter([
                        $addr->AddressLine ?? '',
                        $addr->City ?? '',
                        $addr->Province ?? '',
                        $addr->Country ?? '',
                        $addr->ZipCode ?? ''
                    ]);
                    $full_address = implode(', ', $address_parts);
                }
            }
            
            // Add the formatted address to the customer object
            $customer->Address = $full_address;
        }

        $data['title'] = "Glassify - MY CART";
        $data['cart_items'] = $cart_items;
        $data['customer'] = $customer;
        $data['summary'] = $this->calculate_summary($cart_items);

        $this->load->view('includes/header', $data);
        $this->load->view('shop/addtocart', $data);
        $this->load->view('includes/footer');
    }

    // ===================== REMOVE ITEM =====================
    public function remove($cart_id = null)
    {
        if ($cart_id === null) {
            show_404();
            return;
        }

        $this->Cart_model->remove_item($cart_id);
        redirect('cart-page');
    }

    // ===================== CLEAR CART =====================
    public function clear()
    {
        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            redirect('login');
            return;
        }

        $this->Cart_model->clear_cart($customer_id);
        redirect('cart-page');
    }

    // ===================== UPDATE QUANTITY =====================
    public function update_qty()
    {
        $cart_id = $this->input->post('cart_id');
        $qty = (int) $this->input->post('quantity');

        if (!$cart_id || !$qty) {
            echo json_encode(['status' => 'error']);
            return;
        }

        $this->db->where('Cart_ID', $cart_id);
        $this->db->update('cart', ['Quantity' => $qty]);
        echo json_encode(['status' => 'success']);
    }

    public function update_qty_ajax()
    {
        $cart_id = $this->input->post('cart_id');
        $qty = (int) $this->input->post('quantity');
        $customer_id = $this->session->userdata('customer_id');


        if (!$cart_id || !$customer_id) {
            echo json_encode(['status' => 'error']);
            return;
        }

        $this->db->where('Cart_ID', $cart_id);
        $this->db->update('cart', ['Quantity' => $qty]);

        $cart_items = $this->Cart_model->get_cart_items($customer_id);
        $summary = $this->calculate_summary($cart_items);

        echo json_encode(['status' => 'success', 'summary' => $summary]);
    }

    // ===================== REMOVE ITEM AJAX =====================
   public function remove_ajax()
{
    header('Content-Type: application/json');
    
    try {
        $cart_id = $this->input->post('cart_id');
        $customer_id = $this->session->userdata('customer_id');

        log_message('debug', 'Remove cart item - Cart_ID: ' . $cart_id . ', Customer_ID: ' . $customer_id);

        if (!$customer_id) {
            log_message('error', 'Remove cart item failed: User not logged in');
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            return;
        }

        // Handle Cart_ID = 0 by using alternative deletion method
        if (!$cart_id || $cart_id <= 0) {
            log_message('debug', 'Cart_ID is invalid or 0, using alternative deletion method');
            // Get Product_ID and CustomizationID from POST data as fallback
            $product_id = $this->input->post('product_id');
            $customization_id = $this->input->post('customization_id');
            
            log_message('debug', 'Alternative deletion - Product_ID: ' . ($product_id ? $product_id : 'NULL') . ', CustomizationID: ' . ($customization_id ? $customization_id : 'NULL'));
            
            if (!$product_id) {
                log_message('error', 'Remove cart item failed: Invalid cart item - no Product_ID provided. Cart_ID: ' . $cart_id);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Invalid cart item. Please refresh the page and try again.',
                    'debug' => 'Cart_ID is ' . $cart_id . ' but Product_ID is missing from request'
                ]);
                return;
            }
            
            // Delete by Customer_ID + Product_ID + CustomizationID combination
            $this->db->where('Customer_ID', $customer_id);
            $this->db->where('Product_ID', $product_id);
            if ($customization_id) {
                $this->db->where('CustomizationID', $customization_id);
            } else {
                $this->db->where('CustomizationID IS NULL', null, false);
            }
            $cart_item = $this->db->get('cart')->row();
            
            if (!$cart_item) {
                log_message('error', 'Remove cart item failed: Cart item not found with Product_ID: ' . $product_id . ', CustomizationID: ' . $customization_id);
                echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
                return;
            }
            
            log_message('debug', 'Found cart item - Cart_ID: ' . $cart_item->Cart_ID . ', CustomizationID: ' . $cart_item->CustomizationID);
            
            // Delete customization if exists
            if (!empty($cart_item->CustomizationID)) {
                log_message('debug', 'Deleting customization - CustomizationID: ' . $cart_item->CustomizationID . ', Product_ID: ' . $cart_item->Product_ID);
                $this->load->model('Customization_model');
                $customization_deleted = $this->Customization_model->delete_customization(
                    $cart_item->CustomizationID, 
                    $cart_item->Product_ID
                );
                log_message('debug', 'Customization deletion result: ' . ($customization_deleted ? 'success' : 'failed'));
            }
            
            // Delete cart item by combination
            $this->db->where('Customer_ID', $customer_id);
            $this->db->where('Product_ID', $product_id);
            if ($customization_id) {
                $this->db->where('CustomizationID', $customization_id);
            } else {
                $this->db->where('CustomizationID IS NULL', null, false);
            }
            $delete_result = $this->db->delete('cart');
            $affected_rows = $this->db->affected_rows();
            
            log_message('debug', 'Cart deletion - Result: ' . ($delete_result ? 'true' : 'false') . ', Affected rows: ' . $affected_rows);
            
            // Check if deletion was successful
            if ($this->db->error()['code'] != 0) {
                $error_msg = $this->db->error()['message'];
                log_message('error', 'Delete cart item database error: ' . $error_msg);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Failed to remove item. Please try again.',
                    'debug' => 'Database error: ' . $error_msg
                ]);
                return;
            }
            
            if (!$delete_result || $affected_rows == 0) {
                log_message('error', 'Remove cart item failed: No rows affected. Delete result: ' . ($delete_result ? 'true' : 'false') . ', Affected rows: ' . $affected_rows);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Cart item could not be removed. Please try again.',
                    'debug' => 'No rows were deleted from the database'
                ]);
                return;
            }
            
            log_message('debug', 'Cart item deleted successfully via alternative method');
        } else {
            log_message('debug', 'Using standard deletion method with Cart_ID: ' . $cart_id);
            // 1. Get cart row first (to retrieve CustomizationID and Product_ID)
            // Also verify it belongs to the logged-in customer
            $this->db->where('Cart_ID', $cart_id);
            $this->db->where('Customer_ID', $customer_id);
            $cart_item = $this->db->get('cart')->row();

            if (!$cart_item) {
                log_message('error', 'Remove cart item failed: Cart item not found or does not belong to customer. Cart_ID: ' . $cart_id . ', Customer_ID: ' . $customer_id);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Cart item not found or does not belong to you',
                    'debug' => 'Cart_ID: ' . $cart_id . ', Customer_ID: ' . $customer_id
                ]);
                return;
            }

            log_message('debug', 'Found cart item - Product_ID: ' . $cart_item->Product_ID . ', CustomizationID: ' . $cart_item->CustomizationID);

            // 2. Remove customization if exists (BEFORE removing cart item)
            if (!empty($cart_item->CustomizationID)) {
                log_message('debug', 'Deleting customization - CustomizationID: ' . $cart_item->CustomizationID . ', Product_ID: ' . $cart_item->Product_ID);
                $this->load->model('Customization_model');
                // Pass Product_ID so it knows which table to delete from
                $customization_deleted = $this->Customization_model->delete_customization(
                    $cart_item->CustomizationID, 
                    $cart_item->Product_ID
                );
                log_message('debug', 'Customization deletion result: ' . ($customization_deleted ? 'success' : 'failed'));
            }

            // 3. Remove the cart item
            log_message('debug', 'Calling remove_item with Cart_ID: ' . $cart_id);
            $remove_result = $this->Cart_model->remove_item($cart_id);
            
            // Check if removal was successful
            if ($remove_result === false) {
                log_message('error', 'Failed to remove cart item with Cart_ID: ' . $cart_id . '. remove_item() returned false.');
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Failed to remove item. Please try again.',
                    'debug' => 'remove_item() returned false for Cart_ID: ' . $cart_id
                ]);
                return;
            }
            
            log_message('debug', 'Cart item removed successfully via standard method');
        }

        // 4. Refresh updated cart list
        log_message('debug', 'Refreshing cart items for Customer_ID: ' . $customer_id);
        $cart_items = $this->Cart_model->get_cart_items($customer_id);
        $summary = $this->calculate_summary($cart_items);

        log_message('debug', 'Cart removal successful. Remaining items: ' . count($cart_items));
        echo json_encode([
            'status'  => 'success',
            'summary' => $summary
        ]);
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        $error_trace = $e->getTraceAsString();
        log_message('error', 'Remove cart item exception: ' . $error_message);
        log_message('error', 'Exception trace: ' . $error_trace);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to remove item. Please try again.',
            'debug' => 'Exception: ' . $error_message
        ]);
    }
}


    // ===================== CLEAR CART AJAX =====================
   public function clear_ajax()
{
    $customer_id = $this->session->userdata('customer_id');

    if (!$customer_id) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        return;
    }

    // 1. Get all cart items for the user (to extract CustomizationID)
    $cart_items = $this->db
        ->where('Customer_ID', $customer_id)
        ->get('cart')
        ->result();

    // 2. Collect customization IDs
    $customization_ids = [];
    foreach ($cart_items as $item) {
        if (!empty($item->CustomizationID)) {
            $customization_ids[] = $item->CustomizationID;
        }
    }

    // 3. Delete all cart items
    $this->db->where('Customer_ID', $customer_id)->delete('cart');

    // 4. Delete all customization entries from all tables
    if (!empty($customization_ids)) {
        $this->load->model('Customization_model');
        // Delete each customization individually with its product_id
        foreach ($cart_items as $item) {
            if (!empty($item->CustomizationID)) {
                $this->Customization_model->delete_customization(
                    $item->CustomizationID,
                    $item->Product_ID
                );
            }
        }
    }

    echo json_encode([
        'status'  => 'success',
        'summary' => [
            'items' => 0,
            'subtotal' => 0,
            'shipping' => 0,
            'handling' => 0,
            'total' => 0
        ]
    ]);
}


    // ===================== GET CART DATA FOR QUOTATION =====================
    public function get_cart_ajax()
    {
        header('Content-Type: application/json');
        
        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
            return;
        }

        $cart_items = $this->Cart_model->get_cart_items_with_details($customer_id);
        $summary = $this->calculate_summary($cart_items);

        $items = [];
        foreach ($cart_items as $item) {
            $price = $item->Price ?? $item->EstimatePrice ?? $item->BasePrice ?? 0;
            $total = $price * $item->Quantity;
            
            // Build customization string
            $customization_parts = [];
            
            // 1. Try to parse dynamic customization JSON (highest priority for accurate details)
            if (!empty($item->Customization)) {
                $dynamic_customs = json_decode($item->Customization, true);
                if (is_array($dynamic_customs)) {
                    foreach ($dynamic_customs as $key => $val) {
                        // Skip internal fields
                        if (in_array($key, ['product_id', 'product_name', 'total_quotation', 'quantity', 'price_breakdown', 'customization'])) continue;
                        if (empty($val) || $val === 'None') continue;
                        
                        // Convert key to readable label
                        $label = ucfirst(preg_replace('/(?<!^)[A-Z]/', ' $0', str_replace('_', ' ', $key)));
                        $customization_parts[] = "$label: $val";
                    }
                }
            }
            
            // 2. Fallback to standard fields if dynamic parts are empty
            if (empty($customization_parts)) {
                if (!empty($item->Dimensions)) $customization_parts[] = "Size: " . $item->Dimensions;
                if (!empty($item->GlassShape)) $customization_parts[] = "Shape: " . ucfirst($item->GlassShape);
                if (!empty($item->GlassType)) $customization_parts[] = "Type: " . ucfirst($item->GlassType);
                if (!empty($item->GlassThickness)) $customization_parts[] = "Thickness: " . $item->GlassThickness;
                if (!empty($item->EdgeWork)) $customization_parts[] = "Edge: " . ucfirst(str_replace('-', ' ', $item->EdgeWork));
                if (!empty($item->FrameType)) $customization_parts[] = "Frame: " . ucfirst($item->FrameType);
                if (!empty($item->Engraving) && $item->Engraving !== 'None') $customization_parts[] = "Engraving: " . $item->Engraving;
            }
            
            $customization = !empty($customization_parts) ? implode(' | ', $customization_parts) : 'Standard';
            
            // Handle image - can be JSON or string
            $image_raw = $item->ImageUrl ?? 'default.jpg';
            $placeholder_svg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
            $image_url = $placeholder_svg;
            
            if (!empty($image_raw)) {
                $decoded = json_decode($image_raw, true);
                $first_image = '';
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                    $first_image = $decoded[0];
                } else {
                    $first_image = $image_raw;
                }
                
                if (!empty($first_image) && strpos($first_image, 'broken-image-icon') === false) {
                    if (strpos($first_image, 'http') === 0) {
                        // External URL - can't check existence
                        $image_url = $first_image;
                    } else if (strpos($first_image, 'assets/') === 0 || strpos($first_image, 'uploads/') === 0) {
                        // Check if file exists
                        $file_path = FCPATH . $first_image;
                        if (file_exists($file_path)) {
                            $image_url = base_url($first_image);
                        } else {
                            // File doesn't exist, use placeholder
                            $image_url = $placeholder_svg;
                        }
                    } else {
                        // Treat as filename in uploads/products/
                        $filename = basename($first_image);
                        $file_path = FCPATH . 'uploads/products/' . $filename;
                        if (file_exists($file_path)) {
                            $image_url = base_url('uploads/products/' . $filename);
                        } else {
                            // File doesn't exist, use placeholder
                            $image_url = $placeholder_svg;
                        }
                    }
                }
            }

            $items[] = [
                'cart_id' => $item->Cart_ID,
                'product_id' => $item->Product_ID,
                'customization_id' => $item->CustomizationID,
                'description' => $item->ProductName,
                'quantity' => $item->Quantity,
                'unit_price' => $price,
                'total' => $total,
                'customization' => $customization,
                'image' => $image_url,
                'has_design' => !empty($item->DesignRef),
                'design_ref' => !empty($item->DesignRef) ? base_url($item->DesignRef) : null
            ];
        }

        echo json_encode(['status' => 'success', 'items' => $items, 'summary' => $summary]);
    }

    // ===================== GET ITEM CUSTOMIZATION FOR EDIT =====================
    public function get_item_customization_ajax()
    {
        header('Content-Type: application/json');
        
        $customer_id = $this->session->userdata('customer_id');
        $cart_id = $this->input->get('cart_id');

        if (!$customer_id || !$cart_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }

        // Get cart row to verify ownership and get CustomizationID
        $this->db->where('Cart_ID', $cart_id);
        $this->db->where('Customer_ID', $customer_id);
        $cart_item = $this->db->get('cart')->row();

        if (!$cart_item) {
            echo json_encode(['status' => 'error', 'message' => 'Item not found']);
            return;
        }

        if (empty($cart_item->CustomizationID)) {
            echo json_encode(['status' => 'error', 'message' => 'No customization found']);
            return;
        }

        // Get customization details
        $this->load->model('Customization_model');
        $customization = $this->Customization_model->get_customization($cart_item->CustomizationID, $cart_item->Product_ID);

        if (!$customization) {
            echo json_encode(['status' => 'error', 'message' => 'Customization data missing']);
            return;
        }

        echo json_encode(['status' => 'success', 'customization' => $customization]);
    }

    // ===================== GET SELECTED CART ITEMS FOR CHECKOUT =====================
    public function get_selected_cart_ajax()
    {
        header('Content-Type: application/json');
        
        $customer_id = $this->session->userdata('customer_id');
        
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            return;
        }
        
        // Get selected cart IDs from query parameter
        $selected_ids_str = $this->input->get('selected');
        
        if (empty($selected_ids_str)) {
            echo json_encode(['status' => 'error', 'message' => 'No items selected']);
            return;
        }
        
        // Parse selected IDs
        $selected_ids = array_map('intval', explode(',', $selected_ids_str));
        $selected_ids = array_filter($selected_ids); // Remove zeros
        
        if (empty($selected_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid selection']);
            return;
        }
        
        // Get cart items with full customization details
        $cart_items = $this->Cart_model->get_cart_items_with_details($customer_id);
        
        // Filter to only selected items
        $selected_items = [];
        foreach ($cart_items as $item) {
            if (in_array($item->Cart_ID, $selected_ids)) {
                $selected_items[] = $item;
            }
        }
        
        if (empty($selected_items)) {
            echo json_encode(['status' => 'error', 'message' => 'No valid items found']);
            return;
        }
        
        // Calculate summary for selected items
        $summary = $this->calculate_summary($selected_items);
        
        // Format items for response
        $items = [];
        foreach ($selected_items as $item) {
            $price = $item->Price ?? $item->EstimatePrice ?? $item->BasePrice ?? 0;
            $total = $price * $item->Quantity;
            
            // Build customization string
            $customization_parts = [];
            
            // 1. Try to parse dynamic customization JSON (highest priority for accurate details)
            if (!empty($item->Customization)) {
                $dynamic_customs = json_decode($item->Customization, true);
                if (is_array($dynamic_customs)) {
                    foreach ($dynamic_customs as $key => $val) {
                        // Skip internal fields
                        if (in_array($key, ['product_id', 'product_name', 'total_quotation', 'quantity', 'price_breakdown', 'customization'])) continue;
                        if (empty($val) || $val === 'None') continue;
                        
                        // Convert key to readable label
                        $label = ucfirst(preg_replace('/(?<!^)[A-Z]/', ' $0', str_replace('_', ' ', $key)));
                        $customization_parts[] = "$label: $val";
                    }
                }
            }
            
            // 2. Fallback to standard fields if dynamic parts are empty
            if (empty($customization_parts)) {
                if (!empty($item->Dimensions)) $customization_parts[] = "Size: " . $item->Dimensions;
                if (!empty($item->GlassShape)) $customization_parts[] = "Shape: " . ucfirst($item->GlassShape);
                if (!empty($item->GlassType)) $customization_parts[] = "Type: " . ucfirst($item->GlassType);
                if (!empty($item->GlassThickness)) $customization_parts[] = "Thickness: " . $item->GlassThickness;
                if (!empty($item->EdgeWork)) $customization_parts[] = "Edge: " . ucfirst(str_replace('-', ' ', $item->EdgeWork));
                if (!empty($item->FrameType)) $customization_parts[] = "Frame: " . ucfirst($item->FrameType);
                if (!empty($item->Engraving) && $item->Engraving !== 'None') $customization_parts[] = "Engraving: " . $item->Engraving;
            }
            
            $customization = !empty($customization_parts) ? implode(' | ', $customization_parts) : 'Standard';
            
            // Handle image - can be JSON or string
            $image_raw = $item->ImageUrl ?? 'default.jpg';
            $placeholder_svg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
            $image_url = $placeholder_svg;
            
            if (!empty($image_raw)) {
                $decoded = json_decode($image_raw, true);
                $first_image = '';
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                    $first_image = $decoded[0];
                } else {
                    $first_image = $image_raw;
                }
                
                if (!empty($first_image) && strpos($first_image, 'broken-image-icon') === false) {
                    if (strpos($first_image, 'http') === 0) {
                        // External URL - can't check existence
                        $image_url = $first_image;
                    } else if (strpos($first_image, 'assets/') === 0 || strpos($first_image, 'uploads/') === 0) {
                        // Check if file exists
                        $file_path = FCPATH . $first_image;
                        if (file_exists($file_path)) {
                            $image_url = base_url($first_image);
                        } else {
                            // File doesn't exist, use placeholder
                            $image_url = $placeholder_svg;
                        }
                    } else {
                        // Treat as filename in uploads/products/
                        $filename = basename($first_image);
                        $file_path = FCPATH . 'uploads/products/' . $filename;
                        if (file_exists($file_path)) {
                            $image_url = base_url('uploads/products/' . $filename);
                        } else {
                            // File doesn't exist, use placeholder
                            $image_url = $placeholder_svg;
                        }
                    }
                }
            }
            
            $items[] = [
                'cart_id' => $item->Cart_ID,
                'product_id' => $item->Product_ID,
                'description' => $item->ProductName,
                'quantity' => $item->Quantity,
                'unit_price' => $price,
                'total' => $total,
                'customization' => $customization,
                'image' => $image_url,
                'has_design' => !empty($item->DesignRef),
                'design_ref' => !empty($item->DesignRef) ? base_url($item->DesignRef) : null
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'items' => $items,
            'summary' => $summary
        ]);
    }

    // ===================== GET CART COUNT (AJAX) =====================
    /**
     * Get cart count for header badge
     */
    public function get_cart_count_ajax()
    {
        header('Content-Type: application/json');
        
        $customer_id = $this->session->userdata('customer_id');
        
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'count' => 0]);
            return;
        }
        
        $count = $this->Cart_model->get_cart_count($customer_id);
        
        echo json_encode([
            'status' => 'success',
            'count' => $count
        ]);
    }

    // ===================== HELPER =====================
    private function calculate_summary($cart_items)
    {
        $subtotal = 0;
        $total_items = 0;

        foreach ($cart_items as $item) {
            // Use Price (which includes EstimatePrice or BasePrice) as calculated in get_cart_items_with_details
            // Fallback chain: Price -> EstimatePrice -> BasePrice -> 0
            $price = $item->Price ?? $item->EstimatePrice ?? $item->BasePrice ?? 0;
            $subtotal += $price * $item->Quantity;
            $total_items += $item->Quantity;
        }

        $shipping = $total_items * 25;
        $handling = $total_items * 10;
        $total = $subtotal + $shipping + $handling;

        return [
            'items' => $total_items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'handling' => $handling,
            'total' => $total
        ];
    }

}
