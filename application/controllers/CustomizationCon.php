<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CustomizationCon extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function save_customization() {
        $this->load->model('Customization_model');
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
        $this->load->model('Customization_model');
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
        try {
            $customer_id = $this->session->userdata('customer_id');
            if (!$customer_id) {
                echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
                return;
            }

            if (!isset($_FILES['file']['name']) || (string) $_FILES['file']['name'] === '') {
                echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
                return;
            }

            $err = isset($_FILES['file']['error']) ? (int) $_FILES['file']['error'] : UPLOAD_ERR_OK;
            if ($err !== UPLOAD_ERR_OK) {
                $messages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
                    UPLOAD_ERR_FORM_SIZE => 'File too large',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Server temporary folder missing',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
                    UPLOAD_ERR_EXTENSION => 'Upload blocked by server extension',
                ];
                $msg = isset($messages[$err]) ? $messages[$err] : 'Upload error (code ' . $err . ')';
                echo json_encode(['status' => 'error', 'message' => $msg]);
                return;
            }

            $upload_path = str_replace(['\\', '//'], ['/', '/'], FCPATH . 'uploads/issues/');
            $upload_path = rtrim($upload_path, '/') . '/';

            if (!is_dir($upload_path)) {
                if (!@mkdir($upload_path, 0755, true)) {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create upload directory']);
                    return;
                }
            }
            if (!is_writable($upload_path)) {
                echo json_encode(['status' => 'error', 'message' => 'Upload directory is not writable']);
                return;
            }

            $config = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|pdf',
                'max_size'      => 25600,
                'encrypt_name'  => true,
            ];
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
        } catch (Throwable $e) {
            log_message('error', 'CustomizationCon::upload_file: ' . $e->getMessage());
            if (!headers_sent()) header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Upload failed. Please try again.']);
        }
    }

}
