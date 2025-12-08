<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WishlistCon - Controller for wishlist AJAX operations
 */
class WishlistCon extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('Wishlist_model');
    }

    // ===================== SHOW WISHLIST PAGE =====================
    public function index()
    {
        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            redirect('login');
            return;
        }

        $wishlist_items = $this->Wishlist_model->get_wishlist_items($customer_id);

        $data['title'] = "Glassify - MY WISHLIST";
        $data['wishlist_items'] = $wishlist_items;

        $this->load->view('includes/header', $data);
        $this->load->view('shop/wishlist', $data);
        $this->load->view('includes/footer');
    }

    /**
     * Add item to wishlist (AJAX)
     */
    public function add_ajax()
    {
        $customer_id = $this->session->userdata('customer_id');
        
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Please log in to add items to wishlist']);
            return;
        }

        $post = $this->input->post();
        $product_id = $post['product_id'] ?? null;

        if (!$product_id) {
            echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
            return;
        }

        $customization_id = null;

        // Check if there's customization data
        $has_customization = !empty($post['dimensions']) || !empty($post['shape']) || 
                            !empty($post['type']) || !empty($post['thickness']);

        if ($has_customization) {
            // Handle design image upload (base64 to file)
            $design_ref = null;
            if (!empty($post['design_image'])) {
                $design_ref = $this->save_design_image($post['design_image'], $customer_id);
            }

            // Save customization first
            $custom_data = [
                'Customer_ID' => $customer_id,
                'Product_ID' => $product_id,
                'Dimensions' => $post['dimensions'] ?? null,
                'GlassShape' => $post['shape'] ?? null,
                'GlassType' => $post['type'] ?? null,
                'GlassThickness' => $post['thickness'] ?? null,
                'EdgeWork' => $post['edge'] ?? null,
                'FrameType' => $post['frame'] ?? null,
                'Engraving' => $post['engraving'] ?? null,
                'DesignRef' => $design_ref,
                'EstimatePrice' => $post['price'] ?? 0
            ];

            $customization_id = $this->Wishlist_model->save_customization($custom_data);
        }

        // Add to wishlist
        $wishlist_data = [
            'Customer_ID' => $customer_id,
            'Product_ID' => $product_id,
            'CustomizationID' => $customization_id
        ];

        $result = $this->Wishlist_model->add_to_wishlist($wishlist_data);

        if ($result['status'] === 'exists') {
            echo json_encode([
                'status' => 'exists',
                'message' => 'Item is already in your wishlist',
                'wishlist_count' => $this->Wishlist_model->get_wishlist_count($customer_id)
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Item added to wishlist!',
                'wishlist_id' => $result['id'],
                'wishlist_count' => $this->Wishlist_model->get_wishlist_count($customer_id)
            ]);
        }
    }

    /**
     * Add simple product to wishlist
     */
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

        $wishlist_data = [
            'Customer_ID' => $customer_id,
            'Product_ID' => $product_id,
            'CustomizationID' => null
        ];

        $this->Wishlist_model->add_to_wishlist($wishlist_data);
        redirect('wishlist');
    }


    /**
     * Remove item from wishlist (AJAX)
     */
    public function remove_ajax()
    {
        $wishlist_id = $this->input->post('wishlist_id');
        $customer_id = $this->session->userdata('customer_id');

        if (!$wishlist_id || !$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }

        $deleted = $this->Wishlist_model->remove_item($wishlist_id);

        if ($deleted) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Item removed from wishlist',
                'wishlist_count' => $this->Wishlist_model->get_wishlist_count($customer_id)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove item']);
        }
    }

    /**
     * Clear all wishlist items (AJAX)
     */
    public function clear_ajax()
    {
        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            return;
        }

        $this->Wishlist_model->clear_wishlist($customer_id);

        echo json_encode([
            'status' => 'success',
            'message' => 'Wishlist cleared',
            'wishlist_count' => 0
        ]);
    }

    /**
     * Move item to cart (AJAX)
     */
    public function move_to_cart_ajax()
    {
        $wishlist_id = $this->input->post('wishlist_id');
        $customer_id = $this->session->userdata('customer_id');

        if (!$wishlist_id || !$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }

        $moved = $this->Wishlist_model->move_to_cart($wishlist_id, $customer_id);

        if ($moved) {
            $this->load->model('Cart_model');
            $cart_items = $this->Cart_model->get_cart_items($customer_id);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Item moved to cart!',
                'cart_count' => count($cart_items),
                'wishlist_count' => $this->Wishlist_model->get_wishlist_count($customer_id)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to move item to cart']);
        }
    }

    /**
     * Get wishlist count (AJAX)
     */
    public function get_count_ajax()
    {
        $customer_id = $this->session->userdata('customer_id');

        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'count' => 0]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'count' => $this->Wishlist_model->get_wishlist_count($customer_id)
        ]);
    }

    /**
     * Check if in wishlist (AJAX)
     */
    public function check_ajax()
    {
        $product_id = $this->input->post('product_id');
        $customer_id = $this->session->userdata('customer_id');

        if (!$product_id || !$customer_id) {
            echo json_encode(['status' => 'error', 'in_wishlist' => false]);
            return;
        }

        // Get wishlist entry to return wishlist_id if found
        $this->db->select('Wishlist_ID, CustomizationID');
        $this->db->from('wishlist');
        $this->db->where('Customer_ID', $customer_id);
        $this->db->where('Product_ID', $product_id);
        $this->db->limit(1);
        $wishlist_item = $this->db->get()->row();

        $in_wishlist = $wishlist_item !== null;

        echo json_encode([
            'status' => 'success',
            'in_wishlist' => $in_wishlist,
            'wishlist_id' => $in_wishlist ? $wishlist_item->Wishlist_ID : null
        ]);
    }

    /**
     * Remove item from wishlist by product_id (AJAX)
     * Used for 2D Modeling page toggle functionality
     */
    public function remove_by_product_ajax()
    {
        $product_id = $this->input->post('product_id');
        $customer_id = $this->session->userdata('customer_id');

        if (!$product_id || !$customer_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }

        // Find wishlist entry by product_id
        $this->db->select('Wishlist_ID, CustomizationID');
        $this->db->from('wishlist');
        $this->db->where('Customer_ID', $customer_id);
        $this->db->where('Product_ID', $product_id);
        $this->db->limit(1);
        $wishlist_item = $this->db->get()->row();

        if (!$wishlist_item) {
            echo json_encode(['status' => 'error', 'message' => 'Item not found in wishlist']);
            return;
        }

        $deleted = $this->Wishlist_model->remove_item($wishlist_item->Wishlist_ID);

        if ($deleted) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Item removed from wishlist',
                'wishlist_count' => $this->Wishlist_model->get_wishlist_count($customer_id)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove item']);
        }
    }

    /**
     * Save design image
     */
    private function save_design_image($base64_data, $customer_id)
    {
        // Create designs directory if it doesn't exist
        $upload_dir = FCPATH . 'uploads/designs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Remove data URL prefix if present
        if (strpos($base64_data, 'data:image') === 0) {
            $base64_data = preg_replace('/^data:image\/\w+;base64,/', '', $base64_data);
        }

        // Decode base64 data
        $image_data = base64_decode($base64_data);
        if ($image_data === false) {
            return null;
        }

        // Generate unique filename
        $filename = 'wishlist_design_' . $customer_id . '_' . time() . '_' . uniqid() . '.png';
        $filepath = $upload_dir . $filename;

        // Save image file
        if (file_put_contents($filepath, $image_data)) {
            return 'uploads/designs/' . $filename;
        }

        return null;
    }
}
