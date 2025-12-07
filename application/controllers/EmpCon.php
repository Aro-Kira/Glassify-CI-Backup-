<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmpCon extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->database();
    }

    public function index() {
        $data['title'] = "Employees";
        $this->load->helper('url'); // needed for base_url()
        $this->load->view('includes/header', $data);
        $this->load->view('employees/employees', $data);
        $this->load->view('includes/footer');
    }

    // Get all employees (Admin, Sales Representative, Inventory Officer)
    public function get_users() {
        header('Content-Type: application/json');
        
        $roles = ['Admin', 'Sales Representative', 'Inventory Officer'];
        $this->db->where_in('Role', $roles);
        $this->db->order_by('Date_Created', 'DESC');
        $users = $this->db->get('user')->result();
        
        $formatted = [];
        foreach ($users as $user) {
            $formatted[] = [
                'id' => (int)$user->UserID,
                'name' => trim($user->First_Name . ' ' . ($user->Middle_Name ? $user->Middle_Name . ' ' : '') . $user->Last_Name),
                'firstName' => $user->First_Name,
                'middleName' => $user->Middle_Name ? $user->Middle_Name : '',
                'lastName' => $user->Last_Name,
                'email' => $user->Email,
                'phone' => $user->PhoneNum,
                'role' => $user->Role,
                'status' => $user->Status
            ];
        }
        
        echo json_encode($formatted);
    }

    // Create new employee
    public function create_user() {
        header('Content-Type: application/json');
        
        $input = json_decode($this->input->raw_input_stream, true);
        
        if (!$input) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
            return;
        }
        
        // Validate required fields
        if (empty($input['firstName']) || empty($input['lastName']) || empty($input['email']) || empty($input['password']) || empty($input['role'])) {
            echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
            return;
        }
        
        // Check if email already exists
        if ($this->User_model->email_exists($input['email'])) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
            return;
        }
        
        // Validate role
        $validRoles = ['Admin', 'Sales Representative', 'Inventory Officer'];
        if (!in_array($input['role'], $validRoles)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($input['password'], PASSWORD_BCRYPT);
        
        // Prepare data
        $data = [
            'First_Name' => $input['firstName'],
            'Last_Name' => $input['lastName'],
            'Middle_Name' => isset($input['middleName']) ? $input['middleName'] : '',
            'Email' => $input['email'],
            'Password' => $hashedPassword,
            'PhoneNum' => isset($input['phone']) ? $input['phone'] : '',
            'Role' => $input['role'],
            'Status' => isset($input['status']) ? $input['status'] : 'Active'
        ];
        
        if ($this->User_model->register($data)) {
            echo json_encode(['status' => 'success', 'message' => 'Employee created successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create employee']);
        }
    }

    // Update employee
    public function update_user() {
        header('Content-Type: application/json');
        
        $input = json_decode($this->input->raw_input_stream, true);
        
        if (!$input || !isset($input['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
            return;
        }
        
        $user_id = $input['id'];
        
        // Check if user exists and is an employee
        $user = $this->User_model->get_by_id($user_id);
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            return;
        }
        
        $validRoles = ['Admin', 'Sales Representative', 'Inventory Officer'];
        if (!in_array($user->Role, $validRoles)) {
            echo json_encode(['status' => 'error', 'message' => 'User is not an employee']);
            return;
        }
        
        // Prepare update data
        $data = [];
        if (isset($input['firstName'])) $data['First_Name'] = $input['firstName'];
        if (isset($input['lastName'])) $data['Last_Name'] = $input['lastName'];
        if (isset($input['middleName'])) $data['Middle_Name'] = $input['middleName'];
        if (isset($input['email'])) {
            // Check if email is being changed and if new email already exists
            if ($input['email'] !== $user->Email && $this->User_model->email_exists($input['email'])) {
                echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
                return;
            }
            $data['Email'] = $input['email'];
        }
        if (isset($input['phone'])) $data['PhoneNum'] = $input['phone'];
        if (isset($input['role'])) {
            if (in_array($input['role'], $validRoles)) {
                $data['Role'] = $input['role'];
            }
        }
        if (isset($input['status'])) $data['Status'] = $input['status'];
        
        // Update password if provided
        if (!empty($input['password'])) {
            $data['Password'] = password_hash($input['password'], PASSWORD_BCRYPT);
        }
        
        if ($this->User_model->update_account($user_id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Employee updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update employee']);
        }
    }

    // Delete/Deactivate employee (soft delete)
    public function delete_user() {
        header('Content-Type: application/json');
        
        $input = json_decode($this->input->raw_input_stream, true);
        
        if (!$input || !isset($input['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
            return;
        }
        
        $user_id = $input['id'];
        
        // Check if user exists and is an employee
        $user = $this->User_model->get_by_id($user_id);
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            return;
        }
        
        $validRoles = ['Admin', 'Sales Representative', 'Inventory Officer'];
        if (!in_array($user->Role, $validRoles)) {
            echo json_encode(['status' => 'error', 'message' => 'User is not an employee']);
            return;
        }
        
        // Soft delete - set status to Inactive
        $data = ['Status' => 'Inactive'];
        
        if ($this->User_model->update_account($user_id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Employee deactivated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to deactivate employee']);
        }
    }
}
