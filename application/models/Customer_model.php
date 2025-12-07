<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customer Model
 * Handles customer record operations
 * Ensures customer records exist for users with Role 'Customer'
 */
class Customer_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get Customer_ID from UserID
     * Auto-creates customer record if missing
     * 
     * @param int $user_id UserID from user table
     * @return int|false Customer_ID on success, false on failure
     */
    public function get_customer_id($user_id)
    {
        if (!$user_id || $user_id <= 0) {
            return false;
        }

        // Check if customer record exists
        $this->db->where('UserID', $user_id);
        $customer = $this->db->get('customer')->row();

        if ($customer) {
            return (int)$customer->Customer_ID;
        }

        // Customer record doesn't exist - verify user exists and is a Customer
        $this->db->where('UserID', $user_id);
        $this->db->where('Role', 'Customer');
        $user = $this->db->get('user')->row();

        if (!$user) {
            log_message('error', 'Cannot create customer record: UserID ' . $user_id . ' does not exist or is not a Customer');
            return false;
        }

        // Create customer record
        $this->db->trans_start();
        $this->db->insert('customer', ['UserID' => $user_id]);
        $customer_id = $this->db->insert_id();
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE || !$customer_id) {
            log_message('error', 'Failed to create customer record for UserID: ' . $user_id);
            return false;
        }

        log_message('info', 'Auto-created customer record: Customer_ID ' . $customer_id . ' for UserID ' . $user_id);
        return (int)$customer_id;
    }

    /**
     * Ensure customer record exists for a UserID
     * Creates it if missing
     * 
     * @param int $user_id UserID from user table
     * @return int|false Customer_ID on success, false on failure
     */
    public function ensure_customer_exists($user_id)
    {
        return $this->get_customer_id($user_id);
    }

    /**
     * Get customer by Customer_ID
     * 
     * @param int $customer_id Customer_ID
     * @return object|false Customer record on success, false on failure
     */
    public function get_customer($customer_id)
    {
        $this->db->where('Customer_ID', $customer_id);
        return $this->db->get('customer')->row();
    }

    /**
     * Get customer by UserID
     * 
     * @param int $user_id UserID
     * @return object|false Customer record on success, false on failure
     */
    public function get_customer_by_user_id($user_id)
    {
        $this->db->where('UserID', $user_id);
        return $this->db->get('customer')->row();
    }
}
