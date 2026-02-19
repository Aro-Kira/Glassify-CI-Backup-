<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_requests extends CI_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Role_request_model');
        $this->load->model('User_model');
        $this->load->helper('url');
    }

    public function create()
    {
        header('Content-Type: application/json');

        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Read payload early so we can allow a test bypass of the cooldown
        $raw = $this->input->raw_input_stream;
        $payload = json_decode($raw, true);
        if (!is_array($payload)) $payload = [];

        // Allow bypass during development or when explicitly provided for testing.
        $bypass_cooldown = false;
        if (!empty($payload['bypass_cooldown'])) {
            $bypass_cooldown = true;
        }
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            $bypass_cooldown = true;
        }

        // Cooldown check (skip if bypass requested)
        if (!$bypass_cooldown && $this->Role_request_model->last_request_within_days($user_id, 90)) {
            echo json_encode(['success' => false, 'message' => 'A role change was requested recently. Please wait 90 days.']);
            return;
        }

        $answers = isset($payload['answers']) ? $payload['answers'] : [];
        // Log incoming payload for debugging
        log_message('info', 'Role_requests:create - payload=' . json_encode($payload) . ' user_id=' . $user_id);
        $confirmation = !empty($payload['confirmation']) ? 1 : 0;
        $requested_role_raw = isset($payload['requested_role']) ? trim($payload['requested_role']) : '';
        // Normalize requested role (accept case-insensitive inputs and common synonyms)
        $role_map = [
            'professional' => 'Professional',
            'skilled installer' => 'Professional',
            'beginner' => 'Beginner',
            'customer' => 'Customer'
        ];
        $requested_role = '';
        if ($requested_role_raw !== '') {
            $key = strtolower($requested_role_raw);
            if (isset($role_map[$key])) {
                $requested_role = $role_map[$key];
            }
        }
        // Validate requested role against allowed values to avoid empty/invalid roles
        $allowed_roles = ['Professional', 'Beginner', 'Customer'];
        if ($requested_role === '') {
            // fallback to Professional if nothing provided
            $requested_role = 'Professional';
        }
        if (!in_array($requested_role, $allowed_roles)) {
            log_message('warning', 'Role_requests:create - invalid requested_role: ' . $requested_role_raw . ' normalized=' . $requested_role . ' by user_id=' . $user_id);
            echo json_encode(['success' => false, 'message' => 'Invalid requested role']);
            return;
        }
        $comment = isset($payload['comment']) ? trim($payload['comment']) : '';
        if (strlen($comment) > 40) {
            $comment = substr($comment, 0, 40);
        }

        if (!$confirmation) {
            echo json_encode(['success' => false, 'message' => 'You must confirm the accuracy of your answers.']);
            return;
        }

        // Auto-approve all valid requests (no admin intervention required)
        $status = 'auto_approved';

        $insert = [
            'user_id' => $user_id,
            'requested_role' => $requested_role,
            'answers' => json_encode($answers),
            'confirmation' => $confirmation,
            'status' => $status,
            'comment' => $comment
        ];

        $id = $this->Role_request_model->create($insert);
        if (!$id) {
            $dberr = $this->db->error();
            log_message('error', 'Role_requests:create - failed to insert role_requests: ' . json_encode($dberr));
            echo json_encode(['success' => false, 'message' => 'Database error inserting request']);
            return;
        }

        if ($status === 'auto_approved') {
            // Update user role and log the change
            $user = $this->User_model->get_by_id($user_id);
            log_message('info', 'Role_requests:create - current DB role for UserID=' . $user_id . ' is ' . ($user->Role ?? 'NULL'));
            $old_role = $user ? ($user->Role ?? null) : null;

            // Update user role directly (User_model does not expose a direct role setter)

            $updateData = ['Role' => $requested_role];
            if ($this->db->field_exists('last_role_changed', 'user')) {
                $updateData['last_role_changed'] = date('Y-m-d H:i:s');
            } else {
                // fallback: update Date_Updated if available
                if ($this->db->field_exists('Date_Updated', 'user')) {
                    $updateData['Date_Updated'] = date('Y-m-d H:i:s');
                }
            }

            $this->db->where('UserID', $user_id);
            $update_ok = $this->db->update('user', $updateData);

            // Check update result and DB errors
            $dbErr = $this->db->error();
            $affected = $this->db->affected_rows();

            if ($update_ok === false || (!empty($dbErr['message']) && $dbErr['code'] != 0)) {
                log_message('error', 'Role_requests:create - failed to update user role for UserID=' . $user_id . ' Error=' . json_encode($dbErr) . ' Query=' . $this->db->last_query());
                echo json_encode(['success' => false, 'message' => 'Failed to update user role in database.']);
                return;
            }

            // If no rows affected, warn and fail if the DB still shows the old role
            if ($affected === 0) {
                $fresh = $this->User_model->get_by_id($user_id);
                $currentRole = $fresh ? ($fresh->Role ?? '') : '';
                if ($currentRole !== $requested_role) {
                    log_message('error', 'Role_requests:create - update executed but no rows affected and role unchanged. UserID=' . $user_id . ' currentRole=' . $currentRole . ' requested=' . $requested_role . ' Query=' . $this->db->last_query());
                    echo json_encode(['success' => false, 'message' => 'Role update did not persist.']);
                    return;
                }
            }

            // Log success and update session so the UI immediately reflects the new role. Also refresh basic user fields.
            log_message('info', 'Role_requests:create - update OK for UserID=' . $user_id . ' requested=' . $requested_role . ' affected=' . $affected);
            if ($affected === 0) {
                log_message('info', 'Role_requests:create - no rows affected but DB row matches requested role, proceeding to refresh session for UserID=' . $user_id);
            }

            // Also update customer.role (lowercase) if customer record exists
            // customer.role is ENUM('beginner', 'professional') so we need lowercase and exclude 'Customer'
            $this->load->model('Customer_model');
            $customer_id = $this->Customer_model->get_customer_id($user_id);
            if ($customer_id && in_array($requested_role, ['Professional', 'Beginner'])) {
                $customer_role_lowercase = strtolower($requested_role);
                $this->db->where('Customer_ID', $customer_id);
                $customer_update_ok = $this->db->update('customer', ['role' => $customer_role_lowercase]);
                if ($customer_update_ok) {
                    log_message('info', 'Role_requests:create - updated customer.role to ' . $customer_role_lowercase . ' for Customer_ID=' . $customer_id);
                } else {
                    $custErr = $this->db->error();
                    log_message('warning', 'Role_requests:create - failed to update customer.role: ' . json_encode($custErr));
                }
            }

            // Update session so the UI immediately reflects the new role. Also refresh basic user fields.
            $session_update = ['user_role' => $requested_role];
            $freshUser = $this->User_model->get_by_id($user_id);
            if ($freshUser) {
                $session_update['user_name'] = ($freshUser->First_Name ?? '') . ' ' . ($freshUser->Last_Name ?? '');
                $session_update['user_email'] = $freshUser->Email ?? '';
            }
            $this->session->set_userdata($session_update);

            // Insert audit log (best-effort)
            $this->db->insert('role_change_log', [
                'user_id' => $user_id,
                'old_role' => $old_role,
                'new_role' => $requested_role,
                'changed_by' => null,
                'reason' => $comment
            ]);
            $logErr = $this->db->error();
            if (!empty($logErr['message'])) {
                log_message('error', 'Role_requests:create - failed to insert role_change_log: ' . json_encode($logErr));
            }

            echo json_encode(['success' => true, 'id' => $id, 'status' => 'auto_approved']);
            return;
        }

        echo json_encode(['success' => true, 'id' => $id, 'status' => $status]);
    }
}
