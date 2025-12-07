<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EndUserCon extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->database();
    }

    public function index() {
        $this->load->view('users_view');
    }

    // Get all end users (customers)
    public function get_users() {
        header('Content-Type: application/json');
        
        $this->db->where('Role', 'Customer');
        $this->db->order_by('Date_Created', 'DESC');
        $users = $this->db->get('user')->result();
        
        $formatted = [];
        foreach ($users as $user) {
            // Format date for joined date
            $joinedDate = date('Y-m-d', strtotime($user->Date_Created));
            
            // For last active, we'll use Date_Updated for now (can be enhanced later)
            $lastActive = $user->Date_Updated ? date('Y-m-d', strtotime($user->Date_Updated)) : $joinedDate;
            
            $formatted[] = [
                'id' => (int)$user->UserID,
                'firstName' => $user->First_Name,
                'middleInitial' => $user->Middle_Name ? $user->Middle_Name : '',
                'lastName' => $user->Last_Name,
                'email' => $user->Email,
                'phone' => $user->PhoneNum,
                'joinedDate' => $joinedDate,
                'lastActive' => $lastActive,
                'status' => $user->Status
            ];
        }
        
        echo json_encode($formatted);
    }

    // Update end user
    public function update_user() {
        header('Content-Type: application/json');
        
        $userData = json_decode($this->input->raw_input_stream, true);
        
        if (!$userData || !isset($userData['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }
        
        $user_id = $userData['id'];
        
        // Check if user exists and is a customer
        $user = $this->User_model->get_by_id($user_id);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        if ($user->Role !== 'Customer') {
            echo json_encode(['success' => false, 'message' => 'User is not a customer']);
            return;
        }
        
        // Prepare update data
        $data = [];
        if (isset($userData['firstName'])) $data['First_Name'] = $userData['firstName'];
        if (isset($userData['lastName'])) $data['Last_Name'] = $userData['lastName'];
        if (isset($userData['middleInitial'])) $data['Middle_Name'] = $userData['middleInitial'];
        if (isset($userData['email'])) {
            // Check if email is being changed and if new email already exists
            if ($userData['email'] !== $user->Email && $this->User_model->email_exists($userData['email'])) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                return;
            }
            $data['Email'] = $userData['email'];
        }
        if (isset($userData['phone'])) $data['PhoneNum'] = $userData['phone'];
        
        if ($this->User_model->update_account($user_id, $data)) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user']);
        }
    }

    // Delete/Deactivate end user (soft delete)
    public function delete_user() {
        header('Content-Type: application/json');
        
        $req = json_decode($this->input->raw_input_stream, true);
        
        if (!$req || !isset($req['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }
        
        $user_id = $req['id'];
        
        // Check if user exists and is a customer
        $user = $this->User_model->get_by_id($user_id);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        if ($user->Role !== 'Customer') {
            echo json_encode(['success' => false, 'message' => 'User is not a customer']);
            return;
        }
        
        // Soft delete - set status to Inactive
        $data = ['Status' => 'Inactive'];
        
        if ($this->User_model->update_account($user_id, $data)) {
            echo json_encode(['success' => true, 'message' => 'User deactivated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to deactivate user']);
        }
    }
}
