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

    public function checkout()
    {
        $data['title'] = "Glassify - Payment";
        $this->load->view('includes/header', $data);
        $this->load->view('shop/checkout', $data);
        $this->load->view('includes/footer');
    }



    public function ewallet()
    {
        $data['title'] = "Glassify - Payment";
        $this->load->view('includes/header', $data);
        $this->load->view('shop/ewallet', $data);
        $this->load->view('includes/footer');
    }
    public function complete()
    {
        $data['title'] = "Glassify - Order Complete";
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
        $this->load->view('includes/header', $data);
        $this->load->view('shop/order_tracking', $data);
        $this->load->view('includes/footer');
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
            
            $customer_id = $this->session->userdata('customer_id');
            if (!$customer_id) {
                redirect('login');
                return;
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
            
            // Create order data
            $order_data = [
                'Customer_ID' => $customer_id,
                'SalesRep_ID' => $sales_rep_id,
                'TotalAmount' => $total_amount,
                'DeliveryAddress' => $delivery_address,
                'Status' => 'Pending',
                'PaymentStatus' => ($this->input->post('payment_method') === 'ewallet' && $this->input->post('receipt')) ? 'Pending' : 'Pending',
                'SpecialInstructions' => $this->input->post('note') ?: null
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
            // Get product ID from customization
            $product_id = null;
            $customization_tables = [
                'mirror_customization',
                'shower_enclosure_customization',
                'aluminum_doors_customization',
                'aluminum_bathroom_doors_customization'
            ];
            
            foreach ($customization_tables as $table) {
                $this->db->select('Product_ID');
                $this->db->from($table);
                $this->db->where('Customer_ID', $customer_id);
                $this->db->where('OrderID IS NULL', null, false);
                $this->db->order_by('Created_Date', 'DESC');
                $this->db->limit(1);
                $result = $this->db->get()->row();
                if ($result) {
                    $product_id = $result->Product_ID;
                    break;
                }
            }
            
            // Check inventory if product ID found
            if ($product_id) {
                $this->load->model('Inventory_model');
                $inventory_check = $this->Inventory_model->can_manufacture_product($product_id, 1);
                
                if (!$inventory_check['can_manufacture']) {
                    // Materials are out of stock - prevent order creation
                    $missing_items = array_map(function($m) {
                        return $m['ItemName'];
                    }, $inventory_check['missing_materials']);
                    
                    $error_message = "Cannot place order: The following materials are out of stock: " . implode(', ', $missing_items) . ". Please contact sales for assistance.";
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
}