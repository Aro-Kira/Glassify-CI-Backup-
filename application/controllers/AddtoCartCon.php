<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AddtoCartCon extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('security');
        $this->load->model('Cart_model');
    }

    public function save() {
        // Check if user is logged in
        $customerID = $this->session->userdata('customer_id');
        if (!$customerID) {
            echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
            return;
        }

        // Receive data
        $productID = $this->input->post('product_id');
        $quantity = $this->input->post('quantity') ?: 1;

        // Customization data
        $dimensions = $this->input->post('dimensions');
        if (is_array($dimensions)) {
            $dimensions = json_encode($dimensions);
        }
        $shape = $this->input->post('shape');
        $glass_type = $this->input->post('glass_type');
        $thickness = $this->input->post('thickness');
        $edge_work = $this->input->post('edge_work');
        $frame_type = $this->input->post('frame_type');
        $engraving = $this->input->post('engraving');
        $design_ref = $this->input->post('design_ref');
        $estimate_price = $this->input->post('estimate_price');

        // Insert into appropriate customization table based on product category
        $customData = [
            'Product_ID' => $productID,
            'Customer_ID' => $customerID,
            'Dimensions' => $dimensions,
            'GlassShape' => $shape,
            'GlassType' => $glass_type,
            'GlassThickness' => $thickness,
            'EdgeWork' => $edge_work,
            'FrameType' => $frame_type,
            'Engraving' => $engraving,
            'DesignRef' => $design_ref,
            'EstimatePrice' => $estimate_price
        ];
        $customID = $this->Cart_model->save_customization($customData);

        if (!$customID) {
            echo json_encode(['success' => false, 'message' => 'Failed to save customization']);
            return;
        }

        // Insert into cart table using Cart_model to avoid duplicates
        $cartData = [
            'Customer_ID' => $customerID,
            'Product_ID' => $productID,
            'CustomizationID' => $customID,
            'Quantity' => $quantity
        ];

        $this->Cart_model->add_to_cart($cartData);

        echo json_encode([
            'success' => true,
            'custom_id' => $customID
        ]);
    }
    
}
