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
        $customer_id = $this->session->userdata('customer_id'); // use customer_id consistently
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            return;
        }

        $post = $this->input->post();

        // 1️⃣ Prepare customization data
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

        // 2️⃣ Save customization
        $this->load->model('Cart_model');
        $customization_id = $this->Cart_model->save_customization($custom_data);

        if (!$customization_id) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save customization']);
            return;
        }

        // 3️⃣ Add to cart
        $cart_data = [
            'Customer_ID' => $customer_id,
            'Product_ID' => $post['product_id'] ?? null,
            'CustomizationID' => $customization_id,
            'Quantity' => $post['quantity'] ?? 1
        ];

        $this->Cart_model->add_to_cart($cart_data);

        // 4️⃣ Return updated cart info
        $cart_items = $this->Cart_model->get_cart_items($customer_id);
        $cart_count = count($cart_items);

        echo json_encode([
            'status' => 'success',
            'message' => 'Customized item added to cart',
            'customization_id' => $customization_id,
            'cart_count' => $cart_count
        ]);
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

        // 1. Clear existing customization data for this customer from all category tables (before order is created)
        $category_tables = [
            'mirror_customization',
            'shower_enclosure_customization',
            'aluminum_doors_customization',
            'aluminum_bathroom_doors_customization'
        ];
        
        foreach ($category_tables as $table) {
            $this->db->where('Customer_ID', $customer_id);
            $this->db->delete($table);
        }

        // 2. Prepare complete order details
        $custom_data = [
            'Customer_ID' => $customer_id,
            'Product_ID' => $post['product_id'] ?? null,
            'ProductName' => $post['product_name'] ?? null, // Store product name
            'Dimensions' => $post['dimensions'] ?? null, // JSON format
            'GlassShape' => $post['shape'] ?? null,
            'GlassType' => $post['type'] ?? null,
            'GlassThickness' => $post['thickness'] ?? null,
            'EdgeWork' => $post['edge_work'] ?? null,
            'FrameType' => $post['frame_type'] ?? null,
            'Engraving' => $post['engraving'] ?? null,
            'DesignRef' => $post['file_attached'] ?? null,
            'EstimatePrice' => $post['total_quotation'] ?? 0,
            'TotalQuotation' => $post['total_quotation'] ?? 0, // Store total quotation
            'OrderID' => null, // Will be set when order is created
            'DeliveryAddress' => null, // Will be set when order is created
            'OrderDate' => null // Will be set when order is created
        ];

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
        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            redirect('login');
            return;
        }

        $cart_items = $this->Cart_model->get_cart_items($customer_id);

        $data['title'] = "Glassify - MY CART";
        $data['cart_items'] = $cart_items;
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
    $cart_id = $this->input->post('cart_id');
    $customer_id = $this->session->userdata('customer_id');

    if (!$cart_id || !$customer_id) {
        echo json_encode(['status' => 'error']);
        return;
    }

    // 1. Get cart row first (to retrieve CustomizationID)
    $cart_item = $this->db->where('Cart_ID', $cart_id)->get('cart')->row();

    if (!$cart_item) {
        echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
        return;
    }

    // 2. Remove the cart item
    $this->Cart_model->remove_item($cart_id);

    // 3. Remove customization if exists
    if (!empty($cart_item->CustomizationID)) {
        $this->load->model('Customization_model');
        $this->Customization_model->delete_customization($cart_item->CustomizationID);
    }

    // 4. Refresh updated cart list
    $cart_items = $this->Cart_model->get_cart_items($customer_id);
    $summary = $this->calculate_summary($cart_items);

    echo json_encode([
        'status'  => 'success',
        'summary' => $summary
    ]);
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

    // 4. Delete all customization entries
    if (!empty($customization_ids)) {
        $this->load->model('Customization_model');
        $this->Customization_model->delete_multiple($customization_ids);
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
        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            echo json_encode(['status' => 'error']);
            return;
        }

        $cart_items = $this->Cart_model->get_cart_items($customer_id);
        $summary = $this->calculate_summary($cart_items);

        $items = [];
        foreach ($cart_items as $item) {
            $price = $item->Price ?? 0;
            $items[] = [
                'description' => $item->ProductName,
                'quantity' => $item->Quantity,
                'unit_price' => $price,
                'total' => $price * $item->Quantity
            ];
        }

        echo json_encode(['status' => 'success', 'items' => $items, 'summary' => $summary]);

    }

    // ===================== HELPER =====================
    private function calculate_summary($cart_items)
    {
        $subtotal = 0;
        $total_items = 0;



        foreach ($cart_items as $item) {
            $price = $item->EstimatePrice ?? 100;
            $subtotal += $price * $item->Quantity;
            $total_items += $item->Quantity;
 
        }
 
      ;

        $shipping =  $total_items * 25;
        $handling =  $total_items * 10;
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
