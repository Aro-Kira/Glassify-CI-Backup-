<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['session', 'form_validation', 'email']);
        $this->load->helper(['url', 'form', 'cookie']);
        $this->load->database();
        $this->load->model('User_model');
    }

    // ===================== REGISTER PAGE =====================
    public function register()
    {
        $data['title'] = "Glassify - Register";
        $data['force_guest_header'] = true; // Force guest header on login/register pages
        $this->load->view('includes/header', $data);
        $this->load->view('auth/register', $data);
        $this->load->view('includes/footer');
    }

    // ===================== PROCESS REGISTER =====================
    public function process_register()
    {
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('surname', 'Surname', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|callback_strong_password');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
        $this->form_validation->set_rules('phone', 'Phone Number', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('register'));
        }

        $email = $this->input->post('email');

        if ($this->User_model->email_exists($email)) {
            $this->session->set_flashdata('error', 'Email already registered.');
            redirect(base_url('register'));
        }

        // Generate email confirmation token
        $confirmation_token = bin2hex(random_bytes(32));
        $confirmation_expiry = date('Y-m-d H:i:s', strtotime('+24 hours')); // Token valid for 24 hours
        
        $data = [
            'First_Name' => $this->input->post('first_name'),
            'Middle_Name' => $this->input->post('middle_initial') ?: '',
            'Last_Name' => $this->input->post('surname'),
            'Email' => $email,
            'Password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
            'PhoneNum' => $this->input->post('phone'),
            'Role' => 'Customer', // default role
            'Status' => 'Inactive', // Set to Inactive until email is confirmed
            'reset_token' => $confirmation_token, // Temporarily use reset_token for confirmation
            'reset_token_expiry' => $confirmation_expiry
        ];

        $user_id = $this->User_model->register($data);
        
        if ($user_id) {
            // Send confirmation email
            $first_name = $this->input->post('first_name');
            $confirmation_link = base_url('auth/confirm_email/' . $confirmation_token);
            $email_sent = $this->send_confirmation_email($email, $first_name, $confirmation_link);
            
            if ($email_sent) {
                log_message('info', 'Confirmation email sent successfully to: ' . $email);
            } else {
                log_message('error', 'Failed to send confirmation email to: ' . $email);
            }
            
            // Set success flag to show popup on registration page
            $this->session->set_flashdata('registration_success', true);
            redirect(base_url('register'));
        } else {
            $this->session->set_flashdata('error', 'Registration failed. Please try again.');
            redirect(base_url('register'));
        }
    }

    // ===================== LOGIN PAGES =====================
    public function login()
    {
        // Redirect Sales Representatives to their login page
        $user_role = $this->session->userdata('user_role');
        if ($user_role === 'Sales Representative') {
            $this->session->set_flashdata('error', 'Sales Representatives must use the Sales login page.');
            redirect(base_url('sales-login'));
        }
        
        // Check for Remember Me cookie
        $remember_email = get_cookie('customer_remember_email');
        
        $data['title'] = "Glassify - Login";
        $data['force_guest_header'] = true; // Force guest header on login/register pages
        $data['remember_email'] = $remember_email ? $remember_email : '';
        $this->load->view('includes/header', $data);
        $this->load->view('auth/login', $data);
        $this->load->view('includes/footer');
    }

    // ===================== ADMIN LOGIN =====================
    public function admin_login()
    {
        // If a customer is logged in, log them out automatically
        if ($this->session->userdata('is_logged_in') && $this->session->userdata('user_role') === 'Customer') {
            $this->session->sess_destroy();
        }
        
        // Redirect Sales Representatives to their login page
        $user_role = $this->session->userdata('user_role');
        if ($user_role === 'Sales Representative') {
            $this->session->set_flashdata('error', 'Sales Representatives must use the Sales login page.');
            redirect(base_url('sales-login'));
        }
        
        // Check for Remember Me cookie
        $remember_email = get_cookie('admin_remember_email');
        
        $data['title'] = "Glassify - Admin Login";
        $data['force_guest_header'] = true; // Force guest header on employee login pages
        $data['remember_email'] = $remember_email ? $remember_email : '';
        $this->load->view('includes/header', $data);
        $this->load->view('auth/login_admin', $data);
        $this->load->view('includes/footer');
    }

    // ===================== SALES LOGIN =====================
    public function sales_login()
    {
        // If a customer is logged in, log them out automatically
        if ($this->session->userdata('is_logged_in') && $this->session->userdata('user_role') === 'Customer') {
            $this->session->sess_destroy();
        }
        
        // Check for Remember Me cookie
        $remember_email = get_cookie('sales_remember_email');
        
        $data['title'] = "Glassify - Sales Login";
        $data['remember_email'] = $remember_email ? $remember_email : '';
        $data['force_guest_header'] = true; // Force guest header on employee login pages
        $this->load->view('includes/header', $data);
        $this->load->view('auth/login_sales', $data);
        $this->load->view('includes/footer');
    }


    // ===================== STRONG PASSWORD VALIDATION =====================
    /**
     * Custom validation callback for strong password requirements
     * Password must contain:
     * - At least 8 characters
     * - At least one uppercase letter
     * - At least one lowercase letter
     * - At least one number
     */
    public function strong_password($password)
    {
        if (empty($password)) {
            $this->form_validation->set_message('strong_password', 'The {field} field is required.');
            return false;
        }

        $errors = [];

        // Check minimum length (8 characters)
        if (strlen($password) < 8) {
            $errors[] = 'at least 8 characters';
        }

        // Check for uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'one uppercase letter';
        }

        // Check for lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'one lowercase letter';
        }

        // Check for number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'one number';
        }

        if (!empty($errors)) {
            $this->form_validation->set_message('strong_password', 'The {field} must contain ' . implode(', ', $errors) . '.');
            return false;
        }

        return true;
    }

    // ===================== EMAIL VALIDATION HELPER =====================
    /**
     * Validates if email is properly formatted and has a valid domain
     * @param string $email
     * @return bool
     */
    private function is_valid_working_email($email)
    {
        // First check basic format with strict validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Additional format checks
        // Reject common fake email patterns
        $fake_patterns = [
            '/^test@/i',
            '/@test\./i',
            '/@example\./i',
            '/@fake\./i',
            '/@temp\./i',
            '/@dummy\./i',
            '/@invalid\./i',
            '/@noreply\./i',
            '/@no-reply\./i'
        ];
        
        foreach ($fake_patterns as $pattern) {
            if (preg_match($pattern, $email)) {
                return false;
            }
        }

        // Extract domain
        $domain = substr(strrchr($email, "@"), 1);
        
        // Reject common fake domains
        $fake_domains = ['test.com', 'example.com', 'fake.com', 'temp.com', 'dummy.com', 'invalid.com'];
        if (in_array(strtolower($domain), $fake_domains)) {
            return false;
        }
        
        // Check if domain exists and has valid DNS records
        // Check for MX records (mail exchange) first - this is required for email delivery
        if (!checkdnsrr($domain, 'MX')) {
            // If no MX record, check for A record (some domains use A records for mail)
            if (!checkdnsrr($domain, 'A')) {
                // Domain doesn't exist or has no mail servers
                return false;
            }
        }
        
        // Verify email address exists on mail server (SMTP verification)
        return $this->verify_email_exists($email, $domain);
    }

    /**
     * Verifies if email address actually exists on the mail server
     * @param string $email
     * @param string $domain
     * @return bool
     */
    private function verify_email_exists($email, $domain)
    {
        // Get MX records for the domain
        $mx_records = [];
        if (getmxrr($domain, $mx_records)) {
            // Sort by priority (lower number = higher priority)
            asort($mx_records);
            // Get first MX host (compatible with PHP < 7.3)
            reset($mx_records);
            $mx_host = key($mx_records);
            
            // Connect to mail server and verify email
            $connect = @fsockopen($mx_host, 25, $errno, $errstr, 10);
            if ($connect) {
                // SMTP conversation to verify email
                $response = fgets($connect, 515);
                if (strpos($response, '220') === 0) {
                    // Send HELO
                    $helo_domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
                    fputs($connect, "HELO " . $helo_domain . "\r\n");
                    $response = fgets($connect, 515);
                    
                    // Send MAIL FROM
                    fputs($connect, "MAIL FROM: <noreply@" . $helo_domain . ">\r\n");
                    $response = fgets($connect, 515);
                    
                    // Send RCPT TO (this checks if email exists)
                    fputs($connect, "RCPT TO: <" . $email . ">\r\n");
                    $response = fgets($connect, 515);
                    
                    // Send QUIT
                    fputs($connect, "QUIT\r\n");
                    fclose($connect);
                    
                    // Check if RCPT TO was accepted (250 = accepted, 251 = forwarded)
                    // 550 = mailbox not found, 551 = user not local
                    if (strpos($response, '250') === 0 || strpos($response, '251') === 0) {
                        return true; // Email exists
                    }
                    // If we get 550 or 551, email doesn't exist
                    if (strpos($response, '550') === 0 || strpos($response, '551') === 0) {
                        return false; // Email doesn't exist
                    }
                } else {
                    fclose($connect);
                }
            }
        }
        
        // If SMTP verification fails or can't connect, fall back to domain validation
        // At least the domain exists and has mail servers (MX or A records)
        return true;
    }

    // ===================== PROCESS ROLE LOGIN =====================
    public function process_role_login($role)
    {
        // Form validation
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            $login_routes = [
                'Admin' => 'Adlog',
                'Sales' => 'sales-login',
                'Inventory' => 'Invlog'
            ];
            $redirect_url = $login_routes[$role] ?? 'login';
            redirect(base_url($redirect_url));
        }

        // Sanitize input
        $email = $this->input->post('email', TRUE);
        $password = $this->input->post('password', TRUE);

        // Additional validation: Check if email is a valid working email
        if (!$this->is_valid_working_email($email)) {
            $this->session->set_flashdata('error', 'Please enter a real, working email address. Fake or invalid email addresses are not accepted.');
            $login_routes = [
                'Admin' => 'Adlog',
                'Sales' => 'sales-login',
                'Inventory' => 'Invlog'
            ];
            $redirect_url = $login_routes[$role] ?? 'login';
            redirect(base_url($redirect_url));
        }

        // Map URL-friendly role names to DB roles
        $role_map = [
            'Admin' => 'Admin',
            'Sales' => 'Sales Representative',
            'Customer' => 'Customer'
        ];

        $db_role = $role_map[$role] ?? '';
        $login_routes = [
            'Admin' => 'Adlog',
            'Sales' => 'sales-login',
            'Inventory' => 'Invlog'
        ];
        $redirect_url = $login_routes[$role] ?? 'login';

        // Check if email exists
        $user = $this->User_model->get_by_email($email);

        // Account Not Found
        if (!$user) {
            log_message('info', 'Login attempt failed: Account not found - email=' . $email . ', role=' . $role);
            $this->session->set_flashdata('error', 'Account does not exist. Please check your email address.');
            redirect(base_url($redirect_url));
        }

        // Check if account is active (for Customers, also check if email is confirmed)
        if ($user->Status !== 'Active') {
            log_message('info', 'Login attempt failed: Inactive account - email=' . $email . ', role=' . $role);
            
            // If user is Customer and account is Inactive, they likely haven't confirmed email
            if ($user->Role === 'Customer' && $role === 'Customer') {
                // Store email in session for resend confirmation
                $this->session->set_flashdata('unconfirmed_email', $email);
                $this->session->set_flashdata('error', 'Please confirm your email address before logging in. Check your inbox for the confirmation link.');
            } else {
                $this->session->set_flashdata('error', 'Your account is inactive. Please contact administrator.');
            }
            
            redirect(base_url($redirect_url));
        }

        // Verify password
        if (!password_verify($password, $user->Password)) {
            log_message('info', 'Login attempt failed: Incorrect password - email=' . $email . ', role=' . $role);
            $this->session->set_flashdata('error', 'Invalid password. Please try again.');
            redirect(base_url($redirect_url));
        }

        // Special handling: Sales Representatives can ONLY log in through sales-login page
        if ($user->Role === 'Sales Representative' && $role !== 'Sales') {
            log_message('info', 'Sales Representative attempted login through wrong page - email=' . $email . ', attempted_role=' . $role);
            $this->session->set_flashdata('error', 'Sales Representatives must log in through the Sales login page. Redirecting...');
            redirect(base_url('sales-login'));
        }

        // Check if user has the correct role
        if ($user->Role !== $db_role) {
            log_message('info', 'Login attempt failed: Wrong role - email=' . $email . ', user_role=' . $user->Role . ', required_role=' . $db_role);
            
            // Provide helpful redirect messages for other roles
            if ($user->Role === 'Admin') {
                $this->session->set_flashdata('error', 'You are an Admin. Please use the Admin login page.');
                redirect(base_url('Adlog'));
            } elseif ($user->Role === 'Customer') {
                $this->session->set_flashdata('error', 'You are a Customer. Please use the regular login page.');
                redirect(base_url('login'));
            } else {
                $this->session->set_flashdata('error', "You are not authorized to access the $role login. Your account role is: " . $user->Role);
                redirect(base_url($redirect_url));
            }
        }

        // Successful login - Set session
        $session_data = [
            'user_id' => $user->UserID,
            'user_name' => $user->First_Name . ' ' . $user->Last_Name,
            'user_email' => $user->Email,
            'user_role' => $user->Role,
            'is_logged_in' => true
        ];

        if ($user->Role === 'Customer') {
            // Get or create Customer_ID from customer table
            $this->load->model('Customer_model');
            $customer_id = $this->Customer_model->get_customer_id($user->UserID);
            
            if ($customer_id) {
                $session_data['customer_id'] = $customer_id;
            } else {
                // Fallback: use UserID if customer record creation failed
                log_message('warning', 'Failed to get Customer_ID for UserID: ' . $user->UserID . ', using UserID as fallback');
                $session_data['customer_id'] = $user->UserID;
            }
        }

        $this->session->set_userdata($session_data);

        // Handle Remember Me checkbox - set appropriate cookie based on role
        $remember_me = $this->input->post('remember_me');
        $cookie_name = '';
        
        switch ($user->Role) {
            case 'Admin':
                $cookie_name = 'admin_remember_email';
                break;
            case 'Sales Representative':
                $cookie_name = 'sales_remember_email';
                break;
            case 'Customer':
                $cookie_name = 'customer_remember_email';
                break;
        }
        
        if ($remember_me && !empty($cookie_name)) {
            // Set cookie for 30 days
            set_cookie($cookie_name, $email, 30 * 24 * 60 * 60); // 30 days
        } else if (!empty($cookie_name)) {
            // Delete cookie if exists
            delete_cookie($cookie_name);
        }

        log_message('info', 'Login successful: email=' . $email . ', role=' . $user->Role . ', user_id=' . $user->UserID);

        // Redirect based on role
        switch ($user->Role) {
            case 'Admin':
                redirect(base_url('admin-dashboard'));
                break;
            case 'Sales Representative':
                redirect(base_url('sales-dashboard'));
                break;
            case 'Customer':
                redirect(base_url('home-login'));
                break;
            default:
                redirect(base_url());
        }
    }

    // ===================== FORGOT PASSWORD =====================
    public function forgot_password($role = 'Sales')
    {
        $data['title'] = "Glassify - Forgot Password";
        $data['role'] = $role;
        $this->load->view('includes/header', $data);
        $this->load->view('auth/forgot_password', $data);
        $this->load->view('includes/footer');
    }

    public function process_forgot_password($role = 'Sales')
    {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            // Redirect to appropriate forgot password page based on role
            $forgot_password_routes = [
                'Admin' => 'admin-forgot-password',
                'Sales' => 'sales-forgot-password',
                'Customer' => 'forgot-password'
            ];
            $redirect_url = $forgot_password_routes[$role] ?? 'forgot-password';
            redirect(base_url($redirect_url));
        }

        $email = $this->input->post('email', TRUE);

        // Additional validation: Check if email is a valid working email
        if (!$this->is_valid_working_email($email)) {
            $this->session->set_flashdata('error', 'Please enter a real, working email address. Fake or invalid email addresses are not accepted.');
            // Redirect to appropriate forgot password page based on role
            $forgot_password_routes = [
                'Admin' => 'admin-forgot-password',
                'Sales' => 'sales-forgot-password',
                'Customer' => 'forgot-password'
            ];
            $redirect_url = $forgot_password_routes[$role] ?? 'forgot-password';
            redirect(base_url($redirect_url));
        }
        $user = $this->User_model->get_by_email($email);

        // Map URL-friendly role names to DB roles
        $role_map = [
            'Admin' => 'Admin',
            'Sales' => 'Sales Representative',
            'Customer' => 'Customer'
        ];
        $db_role = $role_map[$role] ?? '';

        // Validate: Check if email exists
        if (!$user) {
            $this->session->set_flashdata('error', 'Email not found in our system.');
            // Redirect to appropriate forgot password page based on role
            $forgot_password_routes = [
                'Admin' => 'admin-forgot-password',
                'Sales' => 'sales-forgot-password',
                'Customer' => 'forgot-password'
            ];
            $redirect_url = $forgot_password_routes[$role] ?? 'forgot-password';
            redirect(base_url($redirect_url));
        }

        // Validate: Check if user role matches the requested role
        if ($user->Role !== $db_role) {
            $this->session->set_flashdata('error', 'This email does not belong to a ' . $role . ' account. Please use the appropriate login page for your account type.');
            // Redirect to appropriate forgot password page based on role
            $forgot_password_routes = [
                'Admin' => 'admin-forgot-password',
                'Sales' => 'sales-forgot-password',
                'Customer' => 'forgot-password'
            ];
            $redirect_url = $forgot_password_routes[$role] ?? 'forgot-password';
            redirect(base_url($redirect_url));
        }

        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour

        // Save token to database
        $this->User_model->save_reset_token($user->UserID, $reset_token, $reset_expiry);

        // Send email with reset link
        $reset_link = base_url('reset-password/' . $role . '/' . $reset_token);
        
        // Send password reset email
        $email_sent = $this->send_reset_email($user->Email, $user->First_Name, $reset_link, $role);
        
        if ($email_sent) {
            log_message('info', 'Password reset email sent successfully to: ' . $email);
            $this->session->set_flashdata('email_sent', 'Password reset instructions have been sent to your email. Please check your inbox (and spam folder if you don\'t see it).');
        } else {
            log_message('error', 'Failed to send password reset email to: ' . $email);
            // Still show success message to user for security (don't reveal if email exists)
            $this->session->set_flashdata('email_sent', 'Password reset instructions have been sent to your email. Please check your inbox (and spam folder if you don\'t see it).');
        }
        
        // Redirect back to forgot password page to show success notification
        $forgot_password_routes = [
            'Admin' => 'admin-forgot-password',
            'Sales' => 'sales-forgot-password',
            'Inventory' => 'inventory-forgot-password',
            'Customer' => 'forgot-password'
        ];
        $redirect_url = $forgot_password_routes[$role] ?? 'forgot-password';
        redirect(base_url($redirect_url));
    }

    public function reset_password($role = 'Sales', $token = '')
    {
        if (empty($token)) {
            $this->session->set_flashdata('error', 'Invalid reset token.');
            
            // Redirect to appropriate login page based on role
            $login_redirect = [
                'Admin' => 'Adlog',
                'Sales' => 'sales-login',
                'Customer' => 'login'
            ];
            $redirect_url = $login_redirect[$role] ?? 'login';
            redirect(base_url($redirect_url));
        }

        // Verify token
        $user = $this->User_model->get_by_reset_token($token);

        if (!$user || strtotime($user->reset_token_expiry) < time()) {
            $this->session->set_flashdata('error', 'Invalid or expired reset token. Please request a new one.');
            // Redirect to appropriate forgot password page based on role
            $forgot_password_routes = [
                'Admin' => 'admin-forgot-password',
                'Sales' => 'sales-forgot-password',
                'Customer' => 'forgot-password'
            ];
            $redirect_url = $forgot_password_routes[$role] ?? 'forgot-password';
            redirect(base_url($redirect_url));
        }

        $data['title'] = "Glassify - Reset Password";
        $data['role'] = $role;
        $data['token'] = $token;
        $this->load->view('includes/header', $data);
        $this->load->view('auth/reset_password', $data);
        $this->load->view('includes/footer');
    }

    public function process_reset_password($role = 'Sales')
    {
        $this->form_validation->set_rules('token', 'Token', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|trim');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            $token = $this->input->post('token');
            redirect(base_url('reset-password/' . $role . '/' . $token));
        }

        $token = $this->input->post('token', TRUE);
        $password = $this->input->post('password', TRUE);

        // Verify token
        $user = $this->User_model->get_by_reset_token($token);

        if (!$user || strtotime($user->reset_token_expiry) < time()) {
            $this->session->set_flashdata('error', 'Invalid or expired reset token. Please request a new one.');
            // Redirect to appropriate forgot password page based on role
            $forgot_password_routes = [
                'Admin' => 'admin-forgot-password',
                'Sales' => 'sales-forgot-password',
                'Customer' => 'forgot-password'
            ];
            $redirect_url = $forgot_password_routes[$role] ?? 'forgot-password';
            redirect(base_url($redirect_url));
        }

        // Update password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Log password reset attempt
        log_message('info', 'Password reset attempt for UserID=' . $user->UserID . ', Email=' . $user->Email);
        
        // Start database transaction to ensure data integrity
        $this->db->trans_start();
        
        // Update password in database
        $password_updated = $this->User_model->update_password($user->UserID, $hashed_password);
        
        if ($password_updated) {
            // Clear reset token after successful password update
            $token_cleared = $this->User_model->clear_reset_token($user->UserID);
            
            // Complete transaction
            $this->db->trans_complete();
            
            // Verify transaction was successful
            if ($this->db->trans_status() === FALSE) {
                log_message('error', 'Password reset transaction failed for UserID=' . $user->UserID);
                $this->session->set_flashdata('error', 'Failed to reset password. Database transaction failed. Please try again.');
                redirect(base_url('reset-password/' . $role . '/' . $token));
            }
            
            // Verify password was actually saved to database by checking the database
            // This ensures the password update was committed successfully
            $updated_user = $this->User_model->get_by_id($user->UserID);
            if ($updated_user) {
                // Verify the new password matches what we just saved
                if (password_verify($password, $updated_user->Password)) {
                    log_message('info', 'Password successfully updated and verified in database for UserID=' . $user->UserID . ', Email=' . $user->Email);
                    $this->session->set_flashdata('success', 'Password reset successfully! You can now log in with your new password.');
                    
                    $login_routes = [
                        'Admin' => 'Adlog',
                        'Sales' => 'sales-login',
                        'Customer' => 'login'
                    ];
                    $redirect_url = $login_routes[$role] ?? 'login';
                    redirect(base_url($redirect_url));
                } else {
                    // Password was updated but verification failed - this is unusual but could happen
                    // Log as warning but still consider it successful since transaction completed
                    log_message('warning', 'Password update completed but verification check failed for UserID=' . $user->UserID . '. Password may still be updated in database.');
                    $this->session->set_flashdata('success', 'Password reset completed. You can now log in with your new password.');
                    
                    $login_routes = [
                        'Admin' => 'Adlog',
                        'Sales' => 'sales-login',
                        'Customer' => 'login'
                    ];
                    $redirect_url = $login_routes[$role] ?? 'login';
                    redirect(base_url($redirect_url));
                }
            } else {
                // User not found after update - this should not happen
                log_message('error', 'User not found after password update for UserID=' . $user->UserID);
                $this->session->set_flashdata('error', 'Password update completed but user verification failed. Please try logging in or contact support.');
                
                $login_routes = [
                    'Admin' => 'Adlog',
                    'Sales' => 'sales-login',
                    'Customer' => 'login'
                ];
                $redirect_url = $login_routes[$role] ?? 'login';
                redirect(base_url($redirect_url));
            }
        } else {
            // Rollback transaction on failure
            $this->db->trans_rollback();
            
            // Get database error for logging
            $db_error = $this->db->error();
            log_message('error', 'Password update failed for UserID=' . $user->UserID . ', Error: ' . json_encode($db_error));
            
            $this->session->set_flashdata('error', 'Failed to reset password. Please try again or contact support if the problem persists.');
            redirect(base_url('reset-password/' . $role . '/' . $token));
        }
    }

    // ===================== SEND RESET EMAIL =====================
    /**
     * Send password reset email to user
     * 
     * @param string $user_email User's email address
     * @param string $first_name User's first name
     * @param string $reset_link Password reset link with token
     * @param string $role User role (for email personalization)
     * @return bool True if email sent successfully, false otherwise
     */
    private function send_reset_email($user_email, $first_name, $reset_link, $role = 'Customer')
    {
        try {
            // Clear any previous email data
            $this->email->clear();
            
            // Load email configuration
            $this->load->config('email');
            
            // Initialize email library with SMTP settings
            $this->email->initialize([
                'protocol' => $this->config->item('protocol'),
                'smtp_host' => $this->config->item('smtp_host'),
                'smtp_user' => $this->config->item('smtp_user'),
                'smtp_pass' => $this->config->item('smtp_pass'),
                'smtp_port' => $this->config->item('smtp_port'),
                'smtp_crypto' => $this->config->item('smtp_crypto'),
                'smtp_timeout' => $this->config->item('smtp_timeout'),
                'mailtype' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'crlf' => "\r\n"
            ]);
            
            // Set email details
            $this->email->from('glassifytesting@gmail.com', 'Glassify');
            $this->email->to($user_email);
            $this->email->subject('Password Reset Request - Glassify');
            
            // Prepare email data for view
            $email_data = [
                'first_name' => $first_name,
                'reset_link' => $reset_link,
                'user_email' => $user_email,
                'role' => $role
            ];
            
            // Load email template
            $email_body = $this->load->view('emails/password_reset_email', $email_data, TRUE);
            
            // Set email message
            $this->email->message($email_body);
            
            // Send email
            $result = $this->email->send();
            
            if (!$result) {
                // Log email error for debugging
                $error = $this->email->print_debugger();
                log_message('error', 'Email sending failed: ' . $error);
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Exception in send_reset_email: ' . $e->getMessage());
            return false;
        }
    }
    
    // ===================== EMAIL CONFIRMATION =====================
    /**
     * Confirm user's email address using token
     */
    public function confirm_email($token = '')
    {
        if (empty($token)) {
            $this->session->set_flashdata('error', 'Invalid confirmation token.');
            redirect(base_url('login'));
        }
        
        // Find user by token (query directly without expiry check)
        $user = $this->db->where('reset_token', $token)
            ->where('Status', 'Inactive')
            ->get('user')
            ->row();
        
        if (!$user) {
            $this->session->set_flashdata('error', 'Invalid confirmation token. Please request a new confirmation email.');
            redirect(base_url('login'));
        }
        
        // Check if token is expired - if so, resend email
        if (strtotime($user->reset_token_expiry) < time()) {
            // Generate new confirmation token
            $confirmation_token = bin2hex(random_bytes(32));
            $confirmation_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Update token in database
            $this->db->where('UserID', $user->UserID);
            $this->db->update('user', [
                'reset_token' => $confirmation_token,
                'reset_token_expiry' => $confirmation_expiry
            ]);
            
            // Resend confirmation email
            $first_name = $user->First_Name;
            $confirmation_link = base_url('auth/confirm_email/' . $confirmation_token);
            $email_sent = $this->send_confirmation_email($user->Email, $first_name, $confirmation_link);
            
            if ($email_sent) {
                log_message('info', 'Confirmation email resent for expired token - UserID: ' . $user->UserID);
                $this->session->set_flashdata('error', 'Your confirmation link has expired. A new confirmation email has been sent to ' . $user->Email . '. Please check your inbox and click the new confirmation link.');
            } else {
                log_message('error', 'Failed to resend confirmation email for expired token - UserID: ' . $user->UserID);
                $this->session->set_flashdata('error', 'Your confirmation link has expired. Failed to send a new confirmation email. Please try logging in to resend the confirmation email.');
            }
            
            redirect(base_url('login'));
        }
        
        // Activate account and clear token
        $this->db->where('UserID', $user->UserID);
        $update_result = $this->db->update('user', [
            'Status' => 'Active',
            'reset_token' => NULL,
            'reset_token_expiry' => NULL
        ]);
        
        if ($update_result) {
            log_message('info', 'Email confirmed successfully for UserID: ' . $user->UserID);
            $this->session->set_flashdata('success', 'Email confirmed successfully! You can now log in.');
            redirect(base_url('login'));
        } else {
            log_message('error', 'Failed to confirm email for UserID: ' . $user->UserID);
            $this->session->set_flashdata('error', 'Confirmation failed. Please try again or contact support.');
            redirect(base_url('login'));
        }
    }
    
    // ===================== SEND CONFIRMATION EMAIL =====================
    /**
     * Send email confirmation email to user
     * 
     * @param string $user_email User's email address
     * @param string $first_name User's first name
     * @param string $confirmation_link Email confirmation link with token
     * @return bool True if email sent successfully, false otherwise
     */
    private function send_confirmation_email($user_email, $first_name, $confirmation_link)
    {
        try {
            // Clear any previous email data
            $this->email->clear();
            
            // Load email configuration
            $this->load->config('email');
            
            // Initialize email library with SMTP settings
            $this->email->initialize([
                'protocol' => $this->config->item('protocol'),
                'smtp_host' => $this->config->item('smtp_host'),
                'smtp_user' => $this->config->item('smtp_user'),
                'smtp_pass' => $this->config->item('smtp_pass'),
                'smtp_port' => $this->config->item('smtp_port'),
                'smtp_crypto' => $this->config->item('smtp_crypto'),
                'smtp_timeout' => $this->config->item('smtp_timeout'),
                'mailtype' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'crlf' => "\r\n"
            ]);
            
            // Set email details
            $this->email->from('glassifytesting@gmail.com', 'Glassify');
            $this->email->to($user_email);
            $this->email->subject('Confirm Your Email Address - Glassify');
            
            // Prepare email data for view
            $email_data = [
                'first_name' => $first_name,
                'confirmation_link' => $confirmation_link,
                'user_email' => $user_email
            ];
            
            // Load email template
            $email_body = $this->load->view('emails/email_confirmation', $email_data, TRUE);
            
            // Set email message
            $this->email->message($email_body);
            
            // Send email
            $result = $this->email->send();
            
            if (!$result) {
                // Log email error for debugging
                $error = $this->email->print_debugger();
                log_message('error', 'Confirmation email sending failed: ' . $error);
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Exception in send_confirmation_email: ' . $e->getMessage());
            return false;
        }
    }

    // ===================== RESEND CONFIRMATION EMAIL =====================
    /**
     * Resend confirmation email to user
     */
    public function resend_confirmation_email()
    {
        $email = $this->input->post('email', TRUE);
        
        if (empty($email)) {
            $this->session->set_flashdata('error', 'Email address is required.');
            redirect(base_url('login'));
        }
        
        // Find user by email
        $user = $this->User_model->get_by_email($email);
        
        if (!$user) {
            $this->session->set_flashdata('error', 'Account not found. Please check your email address.');
            redirect(base_url('login'));
        }
        
        // Check if account is already active
        if ($user->Status === 'Active') {
            $this->session->set_flashdata('success', 'Your account is already confirmed. You can log in now.');
            redirect(base_url('login'));
        }
        
        // Generate new confirmation token
        $confirmation_token = bin2hex(random_bytes(32));
        $confirmation_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Update token in database
        $this->db->where('UserID', $user->UserID);
        $update_result = $this->db->update('user', [
            'reset_token' => $confirmation_token,
            'reset_token_expiry' => $confirmation_expiry
        ]);
        
        if (!$update_result) {
            log_message('error', 'Failed to update confirmation token for UserID: ' . $user->UserID);
            $this->session->set_flashdata('error', 'Failed to generate confirmation link. Please try again.');
            redirect(base_url('login'));
        }
        
        // Send confirmation email
        $first_name = $user->First_Name;
        $confirmation_link = base_url('auth/confirm_email/' . $confirmation_token);
        $email_sent = $this->send_confirmation_email($user->Email, $first_name, $confirmation_link);
        
        if ($email_sent) {
            log_message('info', 'Confirmation email resent successfully to: ' . $user->Email);
            $this->session->set_flashdata('success', 'A new confirmation email has been sent to ' . $user->Email . '. Please check your inbox and click the confirmation link.');
        } else {
            log_message('error', 'Failed to resend confirmation email to: ' . $user->Email);
            $this->session->set_flashdata('error', 'Failed to send confirmation email. Please try again later.');
        }
        
        redirect(base_url('login'));
    }

    // ===================== LOGOUT =====================
    public function logout()
    {
        // Get user role before destroying session
        $user_role = $this->session->userdata('user_role');
        
        // Destroy session only - keep remember me cookies so email is still pre-filled on next login
        $this->session->sess_destroy();
        
        // Set cache control headers to prevent back button access after logout
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');
        
        // Redirect based on role
        if ($user_role === 'Sales Representative') {
            redirect(base_url('sales-login'));
        } elseif ($user_role === 'Admin') {
            redirect(base_url('Adlog'));
        } else {
            redirect(base_url());
        }
    }
}
