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
        
        $this->db->select('u.*, c.role, c.experience_data, c.setup_status');
        $this->db->from('user u');
        $this->db->join('customer c', 'c.UserID = u.UserID', 'left');
        // Include Customer, Professional, and Beginner as customer-equivalents
        $this->db->where_in('u.Role', ['Customer', 'Professional', 'Beginner']);
        $this->db->order_by('u.Date_Created', 'DESC');
        $users = $this->db->get()->result();
        
        $formatted = [];
        foreach ($users as $user) {
            // Format date for joined date
            $joinedDate = date('Y-m-d', strtotime($user->Date_Created));
            
            // For last active, we'll use Date_Updated for now (can be enhanced later)
            $lastActive = $user->Date_Updated ? date('Y-m-d', strtotime($user->Date_Updated)) : $joinedDate;
            
            // Determine role display
            // Priority: customer.role (from experience setup) > user.Role (account-level)
            $roleDisplay = '';
            
            // First try customer.role from experience setup
            if ($user->setup_status === 'completed' && $user->role) {
                if ($user->role === 'beginner') {
                    $roleDisplay = 'Beginner';
                } elseif ($user->role === 'professional') {
                    // Get profession type from experience_data
                    $professionType = '';
                    if ($user->experience_data) {
                        $experienceData = json_decode($user->experience_data, true);
                        if (isset($experienceData['profession_type'])) {
                            $professionType = ucfirst($experienceData['profession_type']);
                            if ($professionType === 'Other' && isset($experienceData['profession_type_other'])) {
                                $professionType = $experienceData['profession_type_other'];
                            }
                        }
                    }
                    $roleDisplay = $professionType ? 'Professional (' . $professionType . ')' : 'Professional';
                }
            }
            
            // Fallback to user.Role if no customer.role available
            if (!$roleDisplay && $user->Role) {
                if ($user->Role === 'Professional') {
                    $roleDisplay = 'Professional';
                } elseif ($user->Role === 'Beginner') {
                    $roleDisplay = 'Beginner';
                } elseif ($user->Role === 'Customer') {
                    $roleDisplay = 'Customer';
                }
            }
            
            $formatted[] = [
                'id' => (int)$user->UserID,
                'firstName' => $user->First_Name,
                'middleInitial' => $user->Middle_Name ? $user->Middle_Name : '',
                'lastName' => $user->Last_Name,
                'email' => $user->Email,
                'phone' => $user->PhoneNum,
                'joinedDate' => $joinedDate,
                'lastActive' => $lastActive,
                'status' => $user->Status,
                'roleDisplay' => $roleDisplay
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
        
        // Check if user exists and is a customer-equivalent (Customer, Professional, Beginner)
        $user = $this->User_model->get_by_id($user_id);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        $allowed_roles = ['Customer', 'Professional', 'Beginner'];
        if (!in_array($user->Role, $allowed_roles)) {
            echo json_encode(['success' => false, 'message' => 'User is not an end user']);
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

    // Delete/Archive end user - moves to enduser_archive table
    public function delete_user() {
        header('Content-Type: application/json');
        
        $req = json_decode($this->input->raw_input_stream, true);
        
        if (!$req || !isset($req['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }
        
        $user_id = $req['id'];
        
        // Check if user exists and is a customer-equivalent (Customer, Professional, Beginner)
        $user = $this->User_model->get_by_id($user_id);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        $allowed_roles = ['Customer', 'Professional', 'Beginner'];
        if (!in_array($user->Role, $allowed_roles)) {
            echo json_encode(['success' => false, 'message' => 'User is not an end user']);
            return;
        }
        
        // Check if customer has orders (which prevents deletion due to RESTRICT constraint)
        $this->db->select('customer.Customer_ID');
        $this->db->from('customer');
        $this->db->where('customer.UserID', $user_id);
        $customer = $this->db->get()->row();
        
        if ($customer) {
            $this->db->where('Customer_ID', $customer->Customer_ID);
            $order_count = $this->db->count_all_results('order');
            
            if ($order_count > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete user: User has ' . $order_count . ' order(s). Please delete or reassign orders first.']);
                return;
            }
        }
        
        // Check for projectschedule references (Admin_ID has RESTRICT)
        $this->db->where('Admin_ID', $user_id);
        $schedule_count = $this->db->count_all_results('projectschedule');
        if ($schedule_count > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete user: User is referenced in ' . $schedule_count . ' project schedule(s).']);
            return;
        }
        
        // Start transaction
        $this->db->trans_start();
        
        // Prepare archive data - handle date formatting
        $archive_data = [
            'UserID' => $user->UserID,
            'First_Name' => $user->First_Name,
            'Last_Name' => $user->Last_Name,
            'Middle_Name' => $user->Middle_Name ? $user->Middle_Name : NULL,
            'Email' => $user->Email,
            'Password' => $user->Password,
            'PhoneNum' => $user->PhoneNum,
            'ImageUrl' => $user->ImageUrl ? $user->ImageUrl : NULL,
            'Role' => $user->Role,
            'Status' => $user->Status ? $user->Status : 'Active',
            'Date_Created' => $user->Date_Created ? $user->Date_Created : NULL,
            'Date_Updated' => $user->Date_Updated ? $user->Date_Updated : NULL,
            'Last_Active' => $user->Last_Active ? $user->Last_Active : NULL,
            'ArchivedAt' => date('Y-m-d H:i:s')
        ];
        
        // Insert into archive table
        $archive_insert_id = $this->db->insert('enduser_archive', $archive_data);
        $archive_success = ($archive_insert_id !== FALSE && $this->db->affected_rows() > 0);
        
        // Check for archive insert errors
        if (!$archive_success) {
            $archive_error = $this->db->error();
            $this->db->trans_rollback();
            log_message('error', 'EndUserCon->delete_user: Failed to insert into archive. User ID=' . $user_id . ' Error: ' . json_encode($archive_error));
            echo json_encode(['success' => false, 'message' => 'Failed to archive user: ' . ($archive_error['message'] ?? 'Database error')]);
            return;
        }
        
        // Update references that can be set to NULL before deletion
        $this->db->where('SalesRep_ID', $user_id);
        $this->db->update('order', ['SalesRep_ID' => NULL]);
        
        // Delete from user table (will CASCADE to customer and user_address)
        $this->db->where('UserID', $user_id);
        $delete_result = $this->db->delete('user');
        $delete_success = ($delete_result !== FALSE && $this->db->affected_rows() > 0);
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE || !$archive_success || !$delete_success) {
            $error = $this->db->error();
            $error_message = 'Database error';
            
            if (!empty($error['message'])) {
                $error_message = $error['message'];
            } elseif (!$archive_success) {
                $error_message = 'Failed to archive user data';
            } elseif (!$delete_success) {
                $error_message = 'Failed to delete user from user table. User may be referenced in other records.';
            }
            
            log_message('error', 'EndUserCon->delete_user: Failed to archive and delete user ID=' . $user_id . ' Error: ' . json_encode($error) . ' Archive success: ' . ($archive_success ? 'true' : 'false') . ' Delete success: ' . ($delete_success ? 'true' : 'false') . ' Transaction status: ' . ($this->db->trans_status() === FALSE ? 'FALSE' : 'TRUE'));
            echo json_encode(['success' => false, 'message' => 'Failed to delete user: ' . $error_message]);
        } else {
            echo json_encode(['success' => true, 'message' => 'User deleted and archived successfully']);
        }
    }
}
