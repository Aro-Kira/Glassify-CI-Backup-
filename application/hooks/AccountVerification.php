<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Account Verification Hook
 * 
 * This hook runs after every controller constructor to verify
 * that logged-in users' accounts still exist in the database.
 * If an admin deletes a user's account, this hook will automatically
 * log them out and redirect them to the homepage.
 */
class AccountVerification
{
    /**
     * Verify that the logged-in user's account still exists
     * 
     * @return void
     */
    public function verify_account()
    {
        $CI =& get_instance();
        
        // Check if session library is loaded
        if (!isset($CI->session)) {
            $CI->load->library('session');
        }
        
        // Get user_id from session
        $user_id = $CI->session->userdata('user_id');
        
        // If no user is logged in, nothing to verify
        if (!$user_id) {
            return;
        }
        
        // Load database if not loaded
        if (!isset($CI->db)) {
            $CI->load->database();
        }
        
        // Check if user exists in database
        $CI->db->where('UserID', $user_id);
        $user = $CI->db->get('user')->row();
        
        if (!$user) {
            // User account was deleted by admin
            // Log for debugging why session is being destroyed
            log_message('warning', 'AccountVerification: user not found in DB for session user_id=' . $user_id . '. Destroying session.');

            // Destroy the session
            $CI->session->sess_destroy();
            
            // Check if this is an AJAX request
            if ($CI->input->is_ajax_request()) {
                // Return JSON response for AJAX requests
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'account_deleted',
                    'message' => 'Your account has been deleted. Please refresh the page.',
                    'redirect' => base_url()
                ]);
                exit;
            }
            
            // For regular requests, redirect to homepage with message
            // Set a flash message (session is destroyed, so use a different approach)
            // Redirect with a URL parameter to show alert
            redirect(base_url() . '?account_deleted=1');
            exit;
        }
    }
}
