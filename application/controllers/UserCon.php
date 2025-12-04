<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserCon extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library(['session', 'upload', 'form_validation', 'image_lib']);
        $this->load->helper(['url', 'form']);
    }

    // =============================
    // LOAD PROFILE PAGE
    // =============================
    public function profile()
    {
        $userID = $this->session->userdata('user_id');

        if (!$userID) {
            show_error('No active user session', 403);
            return;
        }

        $data['title'] = "Glassify - User Profile";
        $data['user'] = $this->User_model->get_by_id($userID);
        $data['addresses'] = $this->User_model->get_addresses($userID);

        // Fallback if user not found
        if (!$data['user']) {
            $data['user'] = (object) [
                'First_Name' => '',
                'Middle_Name' => '',
                'Last_Name' => '',
                'Email' => '',
                'PhoneNum' => '',
                'ImageUrl' => ''
            ];
        }

        // Fallback if addresses not found
        foreach (['Shipping', 'Billing'] as $type) {
            if (!isset($data['addresses'][$type]) || !$data['addresses'][$type]) {
                $data['addresses'][$type] = (object)[
                    'AddressLine' => '',
                    'City' => '',
                    'Province' => '',
                    'Country' => '',
                    'ZipCode' => '',
                    'Note' => ''
                ];
            }
        }

        $this->load->view('includes/header', $data);
        $this->load->view('user/profile', $data);
        $this->load->view('includes/footer');
    }

    // =============================
    // ADD NEW ADDRESS (AJAX)
    // =============================
    public function add_address()
{
    // Require login
   $userID = $this->session->userdata('user_id');

if (!$userID) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    return;
}


    $data = [
        'UserID'      => $userID,
        'AddressLine' => $this->input->post('AddressLine', true),
        'City'        => $this->input->post('City', true),
        'Province'    => $this->input->post('Province', true),
        'Country'     => $this->input->post('Country', true),
        'ZipCode'     => $this->input->post('ZipCode', true),
        'AddressType' => 'Shipping' // default
    ];

$this->load->model('User_model');


    $insert_id = $this->User_model->add_address($data);

    if ($insert_id) {
        $full = $data['AddressLine'] . ", " . $data['City'] . ", " . $data['Province'] . ", " . $data['Country'] . ", " . $data['ZipCode'];

        echo json_encode([
            'success' => true,
            'address_id' => $insert_id,
            'full_address' => $full
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save address'
        ]);
    }
}

    // =============================
    // GET USER ADDRESSES (AJAX)
    // =============================
public function get_addresses()
{
    $user_id = $this->session->userdata('user_id');

    $this->load->model('User_model');
    $addresses = $this->User_model->get_user_addresses($user_id);

    echo json_encode([
        'success' => true,
        'data' => $addresses
    ]);
}



    // =============================
    // UPDATE PROFILE + ADDRESS
    // =============================
    public function update_profile()
    {
        $userID = $this->session->userdata('user_id');
        if (!$userID) {
            return $this->send_response('error', 'No active user session', 403);
        }

        // Validate user info
        $this->form_validation->set_rules('firstname', 'First Name', 'required|trim');
        $this->form_validation->set_rules('lastname', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('phone', 'Phone Number', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            return $this->send_response('error', validation_errors(), 400);
        }

        // Prepare user data
        $updateData = [
            'First_Name' => $this->input->post('firstname', TRUE),
            'Middle_Name' => $this->input->post('middlename', TRUE),
            'Last_Name' => $this->input->post('lastname', TRUE),
            'Email' => $this->input->post('email', TRUE),
            'PhoneNum' => $this->input->post('phone', TRUE)
        ];

        $password = $this->input->post('password', TRUE);
        if (!empty($password)) {
            $updateData['Password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = $this->handle_upload('image', $userID, 'user_');
            if ($uploadResult['status'] === 'error') {
                return $this->send_response('error', $uploadResult['message'], 400);
            }
            $updateData['ImageUrl'] = $uploadResult['file_path'];
        }

        if (!$this->User_model->update_user($userID, $updateData)) {
            return $this->send_response('error', 'Failed to update profile', 500);
        }

        // Handle addresses if provided (optional, based on modal selection)
        $address = $this->input->post('address', TRUE);
        if ($address) {
            $shippingData = [
                'AddressLine' => $this->input->post('address', TRUE),
                'City' => $this->input->post('city', TRUE),
                'Province' => $this->input->post('province', TRUE),
                'Country' => $this->input->post('country', TRUE),
                'ZipCode' => $this->input->post('zipcode', TRUE),
                'Note' => $this->input->post('note', TRUE)
            ];

            $this->User_model->update_address($userID, 'Shipping', $shippingData);

            if ($this->input->post('same')) {
                $this->User_model->update_address($userID, 'Billing', $shippingData);
            }
        }

        $this->send_response('success', 'Profile updated successfully');
    }

    // =============================
    // UPLOAD PROFILE PHOTO
    // =============================
    public function upload_photo()
    {
        $userID = $this->session->userdata('user_id');
        if (!$userID) {
            return $this->send_response('error', 'No active user session', 403);
        }

        $uploadResult = $this->handle_upload('photo', $userID, 'profile_');
        if ($uploadResult['status'] === 'error') {
            return $this->send_response('error', $uploadResult['message'], 400);
        }

        if (!$this->User_model->update_user($userID, ['ImageUrl' => $uploadResult['file_path']])) {
            return $this->send_response('error', 'Failed to save profile photo', 500);
        }

        $this->send_response('success', 'Photo uploaded successfully', 200, [
            'image' => base_url($uploadResult['file_path'])
        ]);
    }

    // =============================
    // HANDLE IMAGE UPLOAD + CROP
    // =============================
    private function handle_upload($field, $userID, $prefix)
    {
        $config['upload_path'] = FCPATH . 'uploads/profile/';
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size'] = 2048;
        $config['file_name'] = $prefix . $userID;
        $config['overwrite'] = TRUE;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload($field)) {
            return ['status' => 'error', 'message' => $this->upload->display_errors('', '')];
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];

        // Crop to 1:1 square
        list($width, $height) = getimagesize($filePath);
        $size = min($width, $height);
        $x = ($width - $size) / 2;
        $y = ($height - $size) / 2;

        $cropConfig = [
            'image_library' => 'gd2',
            'source_image' => $filePath,
            'maintain_ratio' => FALSE,
            'width' => $size,
            'height' => $size,
            'x_axis' => $x,
            'y_axis' => $y,
        ];

        $this->image_lib->initialize($cropConfig);
        if (!$this->image_lib->crop()) {
            return ['status' => 'error', 'message' => $this->image_lib->display_errors('', '')];
        }
        $this->image_lib->clear();

        return ['status' => 'success', 'file_path' => 'uploads/profile/' . $fileData['file_name']];
    }

    // =============================
    // JSON RESPONSE HELPER
    // =============================
    private function send_response($status, $message, $httpCode = 200, $extra = [])
    {
        $response = array_merge(['status' => $status, 'message' => $message], $extra);
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($httpCode)
            ->set_output(json_encode($response));
    }
}
