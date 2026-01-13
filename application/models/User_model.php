<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    private $table = 'user';
    private $addressTable = 'user_address';

    public function __construct()
    {
        parent::__construct();
    }

    // =============================
    // USER FUNCTIONS
    // =============================
    /**
     * Register new user with transaction handling
     * Auto-creates customer record if Role is 'Customer'
     */
    public function register($data)
    {
        $this->db->trans_start();
        
        // Insert user
        $result = $this->db->insert($this->table, $data);
        $user_id = $this->db->insert_id();
        
        // If user is a Customer, create corresponding customer record
        if ($result && $user_id && isset($data['Role']) && $data['Role'] === 'Customer') {
            // Check if customer record already exists
            $this->db->where('UserID', $user_id);
            $existing_customer = $this->db->get('customer')->row();
            
            if (!$existing_customer) {
                // Create customer record
                $this->db->insert('customer', ['UserID' => $user_id]);
                log_message('info', 'Auto-created customer record for UserID: ' . $user_id);
            }
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return $user_id;
    }

    public function email_exists($email)
    {
        return $this->db->where('Email', $email)->count_all_results($this->table) > 0;
    }

    public function get_by_email($email)
    {
        return $this->db->get_where($this->table, ['Email' => $email])->row();
    }

    public function login($email)
    {
        return $this->get_by_email($email);
    }

    public function get_by_id($id)
    {
        return $this->db->where('UserID', $id)->get($this->table)->row();
    }

    // Update user (alias for backward compatibility)
    public function update_user($id, $data)
    {
        return $this->update_account($id, $data);
    }

    // Save password reset token
    public function save_reset_token($user_id, $token, $expiry)
    {
        return $this->db->where('UserID', $user_id)
            ->update($this->table, [
                'reset_token' => $token,
                'reset_token_expiry' => $expiry
            ]);
    }

    // Get user by reset token
    public function get_by_reset_token($token)
    {
        return $this->db->where('reset_token', $token)
            ->where('reset_token_expiry >', date('Y-m-d H:i:s'))
            ->get($this->table)
            ->row();
    }

    // Update password
    public function update_password($user_id, $hashed_password)
    {
        // Log the update attempt
        log_message('debug', 'User_model->update_password: Attempting to update password for UserID=' . $user_id);
        
        // Perform the update
        $this->db->where('UserID', $user_id);
        $result = $this->db->update($this->table, [
            'Password' => $hashed_password
        ]);
        
        // Get affected rows and error info
        $affected_rows = $this->db->affected_rows();
        $db_error = $this->db->error();
        
        // Log the result
        log_message('debug', 'User_model->update_password: Result=' . ($result ? 'true' : 'false') . ', Affected rows=' . $affected_rows);
        
        if (!empty($db_error['message'])) {
            log_message('error', 'User_model->update_password: Database error - ' . $db_error['message']);
        }
        
        // Return true only if update was successful AND at least one row was affected
        return $result && $affected_rows > 0;
    }

    // Clear reset token
    public function clear_reset_token($user_id)
    {
        log_message('debug', 'User_model->clear_reset_token: Clearing reset token for UserID=' . $user_id);
        
        $this->db->where('UserID', $user_id);
        $result = $this->db->update($this->table, [
            'reset_token' => NULL,
            'reset_token_expiry' => NULL
        ]);
        
        $affected_rows = $this->db->affected_rows();
        $db_error = $this->db->error();
        
        log_message('debug', 'User_model->clear_reset_token: Result=' . ($result ? 'true' : 'false') . ', Affected rows=' . $affected_rows);
        
        if (!empty($db_error['message'])) {
            log_message('error', 'User_model->clear_reset_token: Database error - ' . $db_error['message']);
        }
        
        return $result && $affected_rows > 0;
    }

    // Update user account information
    public function update_account($user_id, $data)
    {
        // Remove any fields that shouldn't be updated
        unset($data['UserID']);
        unset($data['Email']); // Email should not be changed via account page
        unset($data['Role']); // Role should not be changed via account page
        unset($data['Date_Created']); // Date_Created should not be changed
        
        // If no data to update, return false
        if (empty($data)) {
            log_message('error', 'User_model->update_account: No data to update');
            return false;
        }
        
        // Add Date_Updated timestamp
        $data['Date_Updated'] = date('Y-m-d H:i:s');
        
        // Log the update attempt
        log_message('debug', 'User_model->update_account: UserID=' . $user_id . ', Data=' . json_encode($data));
        
        // Build the WHERE clause and log what we're updating
        $this->db->where('UserID', $user_id);
        log_message('debug', 'User_model->update_account: Updating UserID=' . $user_id . ' with data: ' . json_encode($data));
        
        // Perform update
        $result = $this->db->update($this->table, $data);
        
        // Get affected rows and error info
        $affected_rows = $this->db->affected_rows();
        $db_error = $this->db->error();
        
        log_message('debug', 'User_model->update_account: Result=' . ($result ? 'true' : 'false') . ', Affected rows=' . $affected_rows . ', Error=' . json_encode($db_error));
        
        // Check if query execution failed
        if ($result === false) {
            log_message('error', 'User_model->update_account: Query execution failed');
            return false;
        }
        
        // Check for database errors
        if (!empty($db_error['message']) && $db_error['code'] != 0) {
            log_message('error', 'User_model->update_account: Database error - ' . $db_error['message']);
            return false;
        }
        
        // CRITICAL: Only return true if rows were actually affected
        // If affected_rows is 0, the update didn't change anything in the database
        if ($affected_rows > 0) {
            log_message('info', 'User_model->update_account: Successfully updated ' . $affected_rows . ' row(s) for UserID=' . $user_id);
            return true;
        } else {
            log_message('error', 'User_model->update_account: Update query executed but no rows were affected. UserID=' . $user_id . ', Data=' . json_encode($data));
            return false;
        }
    }

    // =============================
    // ADDRESS FUNCTIONS
    // =============================
    public function get_addresses($userID)
    {
        $this->db->where('UserID', $userID);
        $query = $this->db->get($this->addressTable);
        $addresses = $query->result();

        $result = [
            'Shipping' => null,
            'Billing' => null
        ];

        foreach ($addresses as $addr) {
            $result[$addr->AddressType] = $addr;
        }

        return $result;
    }

    public function update_address($userID, $addressType, $data)
    {
        $this->db->where(['UserID' => $userID, 'AddressType' => $addressType]);
        $exists = $this->db->count_all_results($this->addressTable, FALSE);

        if ($exists > 0) {
            return $this->db->update($this->addressTable, $data);
        } else {
            $data['UserID'] = $userID;
            $data['AddressType'] = $addressType;
            return $this->db->insert($this->addressTable, $data);
        }
    }

    // ====================================
    // ADD NEW ADDRESS (for multiple saved)
    // ====================================
    public function add_address($data)
    {
        $result = $this->db->insert($this->addressTable, $data);
        
        if (!$result) {
            $error = $this->db->error();
            log_message('error', 'User_model->add_address: Database insert failed - ' . json_encode($error));
            return false;
        }
        
        return $this->db->insert_id();
    }

    // ====================================
    // GET USER ADDRESSES (for multiple saved)
    // ====================================
    public function get_user_addresses($userID)
    {
        return $this->db
            ->where('UserID', $userID)
            ->get($this->addressTable)
            ->result();
    }

    // ====================================
    // GET ADDRESS BY ID (for editing)
    // ====================================
    public function get_address_by_id($addressID, $userID)
    {
        return $this->db
            ->where('AddressID', $addressID)
            ->where('UserID', $userID)
            ->get($this->addressTable)
            ->row();
    }

    // ====================================
    // UPDATE ADDRESS BY ID
    // ====================================
    public function update_address_by_id($addressID, $userID, $data)
    {
        $this->db->where('AddressID', $addressID);
        $this->db->where('UserID', $userID);
        return $this->db->update($this->addressTable, $data);
    }

    // ====================================
    // GET DEFAULT ADDRESS
    // ====================================
    public function get_default_address($userID)
    {
        $this->db->where('UserID', $userID);
        $this->db->where('IsDefault', 1);
        $result = $this->db->get($this->addressTable)->row();
        
        // If no default, get first shipping address
        if (!$result) {
            $this->db->where('UserID', $userID);
            $this->db->where('AddressType', 'Shipping');
            $result = $this->db->get($this->addressTable)->row();
        }
        
        // If still no result, get first available address
        if (!$result) {
            $this->db->where('UserID', $userID);
            $this->db->limit(1);
            $result = $this->db->get($this->addressTable)->row();
        }
        
        return $result;
    }
}
