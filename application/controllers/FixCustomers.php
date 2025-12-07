<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fix Customers Controller
 * One-time script to create customer records for existing users with Role 'Customer'
 * 
 * Access: http://your-domain/FixCustomers/fix
 * 
 * This should be run once to fix existing data, then can be deleted or protected
 */
class FixCustomers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Customer_model');
    }

    /**
     * Fix missing customer records
     * Creates customer records for all users with Role 'Customer' who don't have a customer record
     */
    public function fix()
    {
        // Security: Only allow in development or with admin authentication
        // Uncomment the following lines in production:
        /*
        if (ENVIRONMENT === 'production') {
            show_404();
            return;
        }
        */

        header('Content-Type: application/json');
        
        $results = [
            'total_users' => 0,
            'existing_customers' => 0,
            'created_customers' => 0,
            'errors' => []
        ];

        try {
            // Get all users with Role 'Customer'
            $this->db->where('Role', 'Customer');
            $users = $this->db->get('user')->result();
            $results['total_users'] = count($users);

            foreach ($users as $user) {
                // Check if customer record exists
                $this->db->where('UserID', $user->UserID);
                $existing = $this->db->get('customer')->row();

                if ($existing) {
                    $results['existing_customers']++;
                } else {
                    // Create customer record
                    $customer_id = $this->Customer_model->get_customer_id($user->UserID);
                    
                    if ($customer_id) {
                        $results['created_customers']++;
                        log_message('info', 'Created customer record: Customer_ID ' . $customer_id . ' for UserID ' . $user->UserID);
                    } else {
                        $results['errors'][] = 'Failed to create customer record for UserID: ' . $user->UserID;
                        log_message('error', 'Failed to create customer record for UserID: ' . $user->UserID);
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Customer records fixed successfully',
                'results' => $results
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'results' => $results
            ]);
        }
    }
}
