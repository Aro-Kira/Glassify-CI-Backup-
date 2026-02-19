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
        // Check if user is logged in and is a customer (allow Professional/Beginner as customer-equivalents)
        $allowed_customer_roles = ['Customer', 'Professional', 'Beginner'];
        $session_role = $this->session->userdata('user_role') ?: '';
        if (!$this->session->userdata('is_logged_in') || !in_array($session_role, $allowed_customer_roles)) {
            // Set cache control headers to prevent back button access
            $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
            $this->output->set_header('Pragma: no-cache');
            $this->output->set_header('Expires: 0');
            redirect(base_url('login'));
            return;
        }
        
        $userID = $this->session->userdata('user_id');
        
        // Set cache control headers for customer pages
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');

        $data['title'] = "Glassify - User Profile";
        $data['user'] = $this->User_model->get_by_id($userID);
        
        // Get Customer_ID
        $this->load->model('Customer_model');
        $customer_id = $this->Customer_model->get_customer_id($userID);
        
        // Get customer orders
        $this->load->model('Order_model');
        $data['orders'] = $customer_id ? $this->Order_model->get_customer_orders($customer_id) : [];
        
        // Get customer orders with products for display
        $data['orders_with_products'] = $customer_id ? $this->Order_model->get_customer_orders_with_products($customer_id, 50) : [];
        
        // Get all user addresses (not just Shipping/Billing)
        $data['all_addresses'] = $this->User_model->get_user_addresses($userID);
        
        // Get default address or first available
        $default_address = $this->User_model->get_default_address($userID);
        $data['addresses'] = $this->User_model->get_addresses($userID);
        
        // If we have a default address, use it for Shipping
        if ($default_address) {
            $data['addresses']['Shipping'] = $default_address;
        }
        
        // Load downloads
        $this->load->model('Download_model');
        $data['downloads'] = $customer_id ? $this->Download_model->get_customer_downloads($customer_id) : [];

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

        // Load experience data for User Experience tab
        if ($customer_id) {
            $customer_data = $this->db->get_where('customer', ['Customer_ID' => $customer_id])->row();
            if ($customer_data) {
                $data['customer_role'] = $customer_data->role ?? null;
                $data['setup_status'] = $customer_data->setup_status ?? 'pending';
                if (!empty($customer_data->experience_data)) {
                    $data['experience_data'] = json_decode($customer_data->experience_data, true);
                } else {
                    $data['experience_data'] = [];
                }
            } else {
                $data['customer_role'] = null;
                $data['setup_status'] = 'pending';
                $data['experience_data'] = [];
            }
        } else {
            $data['customer_role'] = null;
            $data['setup_status'] = 'pending';
            $data['experience_data'] = [];
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
        // Set JSON header
        header('Content-Type: application/json');
        
        try {
            // Require login
            $userID = $this->session->userdata('user_id');

            if (!$userID) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                return;
            }

            // Validate required fields
            $this->form_validation->set_rules('Barangay', 'Barangay', 'required|trim');
            $this->form_validation->set_rules('City', 'City/Municipality', 'required|trim');
            $this->form_validation->set_rules('Province', 'Province', 'required|trim');
            $this->form_validation->set_rules('Region', 'Region', 'required|trim');
            $this->form_validation->set_rules('ZipCode', 'Zip Code', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                echo json_encode([
                    'success' => false,
                    'message' => validation_errors()
                ]);
                return;
            }

            $data = [
                'UserID'          => $userID,
                'UnitHouseNumber' => $this->input->post('UnitHouseNumber', true),
                'Street'          => $this->input->post('Street', true),
                'Subdivision'     => $this->input->post('Subdivision', true),
                'Barangay'        => $this->input->post('Barangay', true),
                'City'            => $this->input->post('City', true),
                'Province'        => $this->input->post('Province', true),
                'Region'          => $this->input->post('Region', true),
                'Country'         => $this->input->post('Country', true) ?: 'Philippines',
                'ZipCode'         => $this->input->post('ZipCode', true),
                'AddressType'     => 'Shipping', // default
                'IsDefault'       => $this->input->post('IsDefault') ? 1 : 0
            ];
            
            // If this is set as default, unset other defaults for this user
            if ($data['IsDefault'] == 1) {
                $this->db->where('UserID', $userID);
                $this->db->update('user_address', ['IsDefault' => 0]);
            }

            // Build AddressLine from components for backward compatibility
            $addressParts = array_filter([
                $data['UnitHouseNumber'],
                $data['Street'],
                $data['Subdivision']
            ]);
            $data['AddressLine'] = !empty($addressParts) ? implode(', ', $addressParts) : null;

            $this->load->model('User_model');

            $insert_id = $this->User_model->add_address($data);

            if ($insert_id) {
                $full = trim(implode(', ', array_filter([
                    $data['UnitHouseNumber'],
                    $data['Street'],
                    $data['Subdivision'],
                    $data['Barangay'],
                    $data['City'],
                    $data['Province'],
                    $data['Region'],
                    $data['Country'],
                    $data['ZipCode']
                ])));

                echo json_encode([
                    'success' => true,
                    'address_id' => $insert_id,
                    'full_address' => $full
                ]);
            } else {
                // Get database error
                $db_error = $this->db->error();
                $error_message = 'Failed to save address';
                
                if (!empty($db_error['message'])) {
                    $error_message .= ': ' . $db_error['message'];
                    log_message('error', 'UserCon->add_address: Database error - ' . $db_error['message'] . ' | Code: ' . $db_error['code']);
                } else {
                    $error_message .= '. Please check if the database table has all required columns (UnitHouseNumber, Street, Subdivision, Barangay, Region).';
                }
                
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $error_message,
                    'db_error' => $db_error
                ]);
            }
        } catch (Exception $e) {
            log_message('error', 'UserCon->add_address: Exception - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        } catch (Error $e) {
            log_message('error', 'UserCon->add_address: Fatal Error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Fatal Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
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
    // GET SINGLE ADDRESS (AJAX)
    // =============================
    public function get_address()
    {
        $user_id = $this->session->userdata('user_id');
        $address_id = $this->input->get('address_id');

        if (!$address_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Address ID required'
            ]);
            return;
        }

        $this->load->model('User_model');
        $address = $this->User_model->get_address_by_id($address_id, $user_id);

        if ($address) {
            echo json_encode([
                'success' => true,
                'data' => $address
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Address not found'
            ]);
        }
    }

    // =============================
    // DELETE ADDRESS (AJAX)
    // =============================
    public function delete_address()
    {
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Customer') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            return;
        }

        $userID = $this->session->userdata('user_id');
        $addressID = $this->input->post('address_id');

        if (!$addressID) {
            echo json_encode(['success' => false, 'message' => 'Address ID is required.']);
            return;
        }

        $this->load->model('User_model');
        if ($this->User_model->delete_address_by_id($addressID, $userID)) {
            echo json_encode(['success' => true, 'message' => 'Address archived successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to archive address or address not found.']);
        }
    }

    // =============================
    // SET DEFAULT ADDRESS (AJAX)
    // =============================
    public function set_default_address()
    {
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Customer') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            return;
        }

        $userID = $this->session->userdata('user_id');
        $addressID = $this->input->post('address_id');

        if (!$addressID) {
            echo json_encode(['success' => false, 'message' => 'Address ID is required.']);
            return;
        }

        $this->load->model('User_model');
        
        // First, unset all default addresses for this user
        $this->User_model->unset_default_address($userID);
        
        // Then set the selected address as default
        if ($this->User_model->update_address_by_id($addressID, $userID, ['IsDefault' => 1])) {
            echo json_encode(['success' => true, 'message' => 'Address set as default successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to set default address or address not found.']);
        }
    }

    // =============================
    // UPDATE ADDRESS (AJAX)
    // =============================
    public function update_address()
    {
        // Require login
        $userID = $this->session->userdata('user_id');

        if (!$userID) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $address_id = $this->input->post('AddressID');

        if (!$address_id) {
            echo json_encode(['success' => false, 'message' => 'Address ID required']);
            return;
        }

        // Validate required fields
        $this->form_validation->set_rules('Barangay', 'Barangay', 'required|trim');
        $this->form_validation->set_rules('City', 'City/Municipality', 'required|trim');
        $this->form_validation->set_rules('Province', 'Province', 'required|trim');
        $this->form_validation->set_rules('Region', 'Region', 'required|trim');
        $this->form_validation->set_rules('ZipCode', 'Zip Code', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode([
                'success' => false,
                'message' => validation_errors()
            ]);
            return;
        }

        $data = [
            'UnitHouseNumber' => $this->input->post('UnitHouseNumber', true),
            'Street'          => $this->input->post('Street', true),
            'Subdivision'     => $this->input->post('Subdivision', true),
            'Barangay'        => $this->input->post('Barangay', true),
            'City'            => $this->input->post('City', true),
            'Province'        => $this->input->post('Province', true),
            'Region'          => $this->input->post('Region', true),
            'Country'         => $this->input->post('Country', true) ?: 'Philippines',
            'ZipCode'         => $this->input->post('ZipCode', true),
            'IsDefault'       => $this->input->post('IsDefault') ? 1 : 0
        ];
        
        // If this is set as default, unset other defaults for this user
        if ($data['IsDefault'] == 1) {
            $this->db->where('UserID', $userID);
            $this->db->where('AddressID !=', $address_id);
            $this->db->update('user_address', ['IsDefault' => 0]);
        }

        // Build AddressLine from components for backward compatibility
        $addressParts = array_filter([
            $data['UnitHouseNumber'],
            $data['Street'],
            $data['Subdivision']
        ]);
        $data['AddressLine'] = !empty($addressParts) ? implode(', ', $addressParts) : null;

        $this->load->model('User_model');
        $result = $this->User_model->update_address_by_id($address_id, $userID, $data);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Address updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update address'
            ]);
        }
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

        // Handle password change with security validation
        $currentPassword = $this->input->post('current_password', TRUE);
        $newPassword = $this->input->post('new_password', TRUE);
        $confirmPassword = $this->input->post('confirm_password', TRUE);

        // If any password field is provided, validate all
        if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
            // All password fields must be provided
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                return $this->send_response('error', 'All password fields are required to change password.', 400);
            }

            // Verify new password and confirm password match
            if ($newPassword !== $confirmPassword) {
                return $this->send_response('error', 'New password and confirm password do not match.', 400);
            }

            // Check minimum password length
            if (strlen($newPassword) < 6) {
                return $this->send_response('error', 'New password must be at least 6 characters long.', 400);
            }

            // Get current user to verify current password
            $currentUser = $this->User_model->get_by_id($userID);
            if (!$currentUser) {
                return $this->send_response('error', 'User not found.', 404);
            }

            // Verify current password
            if (!password_verify($currentPassword, $currentUser->Password)) {
                return $this->send_response('error', 'Current password is incorrect.', 400);
            }

            // Check if new password is different from current password
            if (password_verify($newPassword, $currentUser->Password)) {
                return $this->send_response('error', 'New password must be different from your current password.', 400);
            }

            // Hash and set new password
            $updateData['Password'] = password_hash($newPassword, PASSWORD_DEFAULT);
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
    // UPDATE USER EXPERIENCE
    // =============================
    public function update_experience()
    {
        $userID = $this->session->userdata('user_id');
        if (!$userID) {
            return $this->send_response('error', 'No active user session', 403);
        }

        // Get customer record
        $this->load->model('Customer_model');
        $customer_id = $this->Customer_model->get_customer_id($userID);
        
        if (!$customer_id) {
            return $this->send_response('error', 'Customer not found', 404);
        }

        // Get customer data
        $customer = $this->db->get_where('customer', ['Customer_ID' => $customer_id])->row();
        if (!$customer) {
            return $this->send_response('error', 'Customer record not found', 404);
        }

        // Validate role
        $role = $this->input->post('role', TRUE);
        if (!in_array($role, ['beginner', 'professional'])) {
            return $this->send_response('error', 'Invalid role selected', 400);
        }

        // Get existing experience data to preserve professional_type
        $existing_data = [];
        if (!empty($customer->experience_data)) {
            $existing_data = json_decode($customer->experience_data, true);
        }

        // Prepare experience data
        $experience_data = [
            'role' => $role
        ];

        // Preserve professional_type if role is professional
        if ($role === 'professional' && isset($existing_data['professional_type'])) {
            $experience_data['professional_type'] = $existing_data['professional_type'];
        }

        if ($role === 'beginner') {
            $experience_data['experience'] = $this->input->post('beginner_experience', TRUE);
            $experience_data['specifications_knowledge'] = $this->input->post('beginner_specifications', TRUE);
            $experience_data['customization_handling'] = $this->input->post('beginner_customization_handling', TRUE);
        } elseif ($role === 'professional') {
            $experience_data['previous_experience'] = $this->input->post('professional_prev_experience', TRUE);
            $experience_data['specification_preparation'] = $this->input->post('professional_spec_prep', TRUE);
            $experience_data['2d_tool_comfort'] = $this->input->post('professional_2d_comfort', TRUE);
        }

        // Update customer record
        $update_data = [
            'role' => $role,
            'experience_data' => json_encode($experience_data),
            'setup_status' => 'completed' // Ensure setup is marked as completed when updating
        ];

        if ($this->db->update('customer', $update_data, ['Customer_ID' => $customer_id])) {
            // Also sync user.Role with capitalized version to keep both tables consistent
            $user_role_capitalized = ucfirst($role); // 'beginner' -> 'Beginner', 'professional' -> 'Professional'
            $this->db->where('UserID', $userID);
            $this->db->update('user', ['Role' => $user_role_capitalized]);
            
            // Update session to reflect the new role
            $this->session->set_userdata('user_role', $user_role_capitalized);
            
            return $this->send_response('success', 'Your experience settings have been updated successfully.');
        } else {
            return $this->send_response('error', 'Failed to update experience settings', 500);
        }
    }

    // =============================
    // UPLOAD PROFILE PHOTO
    // =============================
    public function upload_photo()
    {
        try {
            $userID = $this->session->userdata('user_id');
            if (!$userID) {
                return $this->send_response('error', 'No active user session', 403);
            }

            // Check if file was uploaded
            if (empty($_FILES['photo']['name'])) {
                return $this->send_response('error', 'No file uploaded', 400);
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
        } catch (Exception $e) {
            return $this->send_response('error', 'Upload failed: ' . $e->getMessage(), 500);
        }
    }

    // =============================
    // DELETE PROFILE PHOTO
    // =============================
    public function delete_photo()
    {
        try {
            $userID = $this->session->userdata('user_id');
            if (!$userID) {
                return $this->send_response('error', 'No active user session', 403);
            }

            // Get current user to find image path
            $user = $this->User_model->get_by_id($userID);
            if ($user && !empty($user->ImageUrl)) {
                $imagePath = FCPATH . $user->ImageUrl;
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }

            // Clear ImageUrl in database
            if (!$this->User_model->update_user($userID, ['ImageUrl' => ''])) {
                return $this->send_response('error', 'Failed to delete profile photo', 500);
            }

            $this->send_response('success', 'Photo deleted successfully', 200, [
                'image' => base_url('assets/images/img-page/pfp.png')
            ]);
        } catch (Exception $e) {
            return $this->send_response('error', 'Delete failed: ' . $e->getMessage(), 500);
        }
    }

    // =============================
    // HANDLE IMAGE UPLOAD + CROP
    // =============================
    private function handle_upload($field, $userID, $prefix)
    {
        $uploadPath = FCPATH . 'uploads/profile/';
        
        // Ensure upload directory exists
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                return ['status' => 'error', 'message' => 'Failed to create upload directory'];
            }
        }

        // Check if directory is writable
        if (!is_writable($uploadPath)) {
            return ['status' => 'error', 'message' => 'Upload directory is not writable'];
        }

        $config['upload_path'] = $uploadPath;
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size'] = 2048;
        $config['file_name'] = $prefix . $userID;
        $config['overwrite'] = TRUE;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload($field)) {
            $error = $this->upload->display_errors('', '');
            return ['status' => 'error', 'message' => $error ? $error : 'File upload failed'];
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];

        // Verify file was uploaded
        if (!file_exists($filePath)) {
            return ['status' => 'error', 'message' => 'Uploaded file not found'];
        }

        // Crop to 1:1 square
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo === false) {
            @unlink($filePath);
            return ['status' => 'error', 'message' => 'Invalid image file'];
        }

        list($width, $height) = $imageInfo;
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
            $error = $this->image_lib->display_errors('', '');
            @unlink($filePath);
            $this->image_lib->clear();
            return ['status' => 'error', 'message' => $error ? $error : 'Image cropping failed'];
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
