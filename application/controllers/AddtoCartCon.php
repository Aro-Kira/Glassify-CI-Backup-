<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AddtoCartCon extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
        $this->load->helper('security');
        $this->load->model('Cart_model');
    }

    public function save() {
        // Receive data
        $productID = $this->input->post('product_id');
        $quantity = $this->input->post('quantity');

        // Customization data
        $dimensions = json_encode($this->input->post('dimensions'));
        $shape = $this->input->post('shape');
        $glass_type = $this->input->post('glass_type');
        $thickness = $this->input->post('thickness');
        $edge_work = $this->input->post('edge_work');
        $frame_type = $this->input->post('frame_type');
        $engraving = $this->input->post('engraving');
        $design_ref = $this->input->post('design_ref');
        $estimate_price = $this->input->post('estimate_price');

        // For now, static customer ID (replace later)
        $customerID = 1;

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

        if (!$insert_custom || !$customID) {
            echo json_encode(['success' => false]);
            return;
        }

        // Insert into cart table
        $cartData = [
            'Product_ID' => $productID,
            'CustomizationID' => $customID,
            'Quantity' => $quantity
        ];

        $insert_cart = $this->db->insert('cart', $cartData);
        $this->db->insert('cart', $cartData);

        echo json_encode([
            'success' => true,
            'custom_id' => $customID
        ]);
        log_message('error', 'Received product_id: ' . $this->input->post('product_id'));

    }
    
}
