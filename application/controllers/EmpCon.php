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

    // Get all employees (Admin, Sales Representative)
    public function get_users() {
        header('Content-Type: application/json');
        
        $roles = ['Admin', 'Sales Representative'];
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
        $validRoles = ['Admin', 'Sales Representative'];
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
        
        $validRoles = ['Admin', 'Sales Representative'];
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

    // Delete/Archive employee - moves to employee_archive table
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
        
        $validRoles = ['Admin', 'Sales Representative'];
        if (!in_array($user->Role, $validRoles)) {
            echo json_encode(['status' => 'error', 'message' => 'User is not an employee']);
            return;
        }
        
        // Check for orders where user is SalesRep (RESTRICT constraint)
        $this->db->where('SalesRep_ID', $user_id);
        $order_count = $this->db->count_all_results('order');
        if ($order_count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete employee: Employee is assigned to ' . $order_count . ' order(s). Please reassign orders first.']);
            return;
        }
        
        // Check for projectschedule references (Admin_ID has RESTRICT)
        $this->db->where('Admin_ID', $user_id);
        $schedule_count = $this->db->count_all_results('projectschedule');
        if ($schedule_count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete employee: Employee is referenced in ' . $schedule_count . ' project schedule(s).']);
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
        $archive_insert_id = $this->db->insert('employee_archive', $archive_data);
        $archive_success = ($archive_insert_id !== FALSE && $this->db->affected_rows() > 0);
        
        // Check for archive insert errors
        if (!$archive_success) {
            $archive_error = $this->db->error();
            $this->db->trans_rollback();
            log_message('error', 'EmpCon->delete_user: Failed to insert into archive. User ID=' . $user_id . ' Error: ' . json_encode($archive_error));
            echo json_encode(['status' => 'error', 'message' => 'Failed to archive employee: ' . ($archive_error['message'] ?? 'Database error')]);
            return;
        }
        
        // Update references that can be set to NULL before deletion
        $this->db->where('ApprovedBy_SalesRep_ID', $user_id);
        $this->db->update('order', ['ApprovedBy_SalesRep_ID' => NULL]);
        
        $this->db->where('ApprovedBy_Admin_ID', $user_id);
        $this->db->update('order', ['ApprovedBy_Admin_ID' => NULL]);
        
        $this->db->where('DisapprovedBy_ID', $user_id);
        $this->db->update('order', ['DisapprovedBy_ID' => NULL]);
        
        // Update appointments table if AssignedStaff_ID column exists
        if ($this->db->field_exists('AssignedStaff_ID', 'appointments')) {
            $this->db->where('AssignedStaff_ID', $user_id);
            $this->db->update('appointments', ['AssignedStaff_ID' => NULL]);
        }
        
        // Update inventory_items table if UpdatedBy column exists
        if ($this->db->table_exists('inventory_items') && $this->db->field_exists('UpdatedBy', 'inventory_items')) {
            $this->db->where('UpdatedBy', $user_id);
            $this->db->update('inventory_items', ['UpdatedBy' => NULL]);
        }
        
        // Delete from user table
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
                $error_message = 'Failed to archive employee data';
            } elseif (!$delete_success) {
                $error_message = 'Failed to delete employee from user table. User may be referenced in other records.';
            }
            
            log_message('error', 'EmpCon->delete_user: Failed to archive and delete employee ID=' . $user_id . ' Error: ' . json_encode($error) . ' Archive success: ' . ($archive_success ? 'true' : 'false') . ' Delete success: ' . ($delete_success ? 'true' : 'false') . ' Transaction status: ' . ($this->db->trans_status() === FALSE ? 'FALSE' : 'TRUE'));
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete employee: ' . $error_message]);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Employee deleted and archived successfully']);
        }
    }
}
