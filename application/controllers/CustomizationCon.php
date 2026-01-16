<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CustomizationCon extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('Customization_model');
    }
public function save_customization() {
$customer_id = $this->session->userdata('customer_id');
    if (!$customer_id) {
        echo json_encode(['status'=>'error','message'=>'User not logged in']);
        log_message('error', 'SESSION DATA: ' . print_r($this->session->userdata(), true));

        return;
    }
    
    $data = [
        'Customer_ID'   => $customer_id,
        'Product_ID'    => $this->input->post('product_id'),
        'Dimensions'    => $this->input->post('dimensions') ?? '',
        'GlassShape'    => $this->input->post('shape') ?? '',
        'GlassType'     => $this->input->post('type') ?? '',
        'GlassThickness'=> $this->input->post('thickness') ?? '',
        'EdgeWork'      => $this->input->post('edge') ?? '',
        'FrameType'     => $this->input->post('frame') ?? '',
        'Engraving'     => $this->input->post('engraving') ?? '',
        'EstimatePrice' => $this->input->post('price') ?? 0,
        'DesignRef'     => $this->input->post('design_ref') ?? ''
    ];

    $customization_id = $this->Customization_model->add_customization($data);

    echo json_encode(['status' => 'success', 'customization_id' => $customization_id]);

    $post = $this->input->post();
    file_put_contents('debug.log', print_r($post, true));
}

public function remove_customization() {
    $customization_id = $this->input->post('customization_id');

    if (!$customization_id) {
        echo json_encode(['status' => 'error', 'message' => 'No customization ID provided']);
        return;
    }

    $result = $this->Customization_model->delete_customization($customization_id);

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
    }
}

public function upload_file() {
    header('Content-Type: application/json');
    
    $customer_id = $this->session->userdata('customer_id');
    if (!$customer_id) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        return;
    }

    // Check if file was uploaded
    if (empty($_FILES['file']['name'])) {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
        return;
    }

    // Configure upload
    $config['upload_path'] = FCPATH . 'uploads/issues/';
    $config['allowed_types'] = 'jpg|jpeg|png|pdf';
    $config['max_size'] = 25600; // 25MB in KB
    $config['encrypt_name'] = TRUE;

    // Create directory if it doesn't exist
    if (!is_dir($config['upload_path'])) {
        if (!mkdir($config['upload_path'], 0755, true)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create upload directory']);
            return;
        }
    }

    $this->load->library('upload', $config);

    if ($this->upload->do_upload('file')) {
        $upload_data = $this->upload->data();
        $file_path = 'uploads/issues/' . $upload_data['file_name'];
        
        echo json_encode([
            'status' => 'success',
            'file_path' => $file_path,
            'file_name' => $upload_data['original_name']
        ]);
    } else {
        $error = $this->upload->display_errors('', '');
        echo json_encode([
            'status' => 'error',
            'message' => $error ?: 'File upload failed'
        ]);
    }
}

}
