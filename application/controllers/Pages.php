<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends CI_Controller {

    public function home() {
        $data['title'] = "Glassify - Home";
        $this->load->view('includes/header', $data);
        $this->load->view('home', $data);
        $this->load->view('includes/footer');
    }

    public function home_login() {
        // Check if user is logged in and is a customer
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Customer') {
            // Set cache control headers to prevent back button access
            $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
            $this->output->set_header('Pragma: no-cache');
            $this->output->set_header('Expires: 0');
            redirect(base_url('login'));
        }
        
        // Set cache control headers for customer pages
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');

        // Load models
        $this->load->model('Order_model');
        $this->load->model('User_model');
        $this->load->model('Product_model');
        $this->load->model('Customer_model');

        $user_id = $this->session->userdata('user_id');
        
        // Get Customer_ID from UserID (order table uses Customer_ID, not UserID)
        $customer_id = $this->Customer_model->get_customer_id($user_id);
        
        if (!$customer_id) {
            log_message('error', 'Customer dashboard: Failed to get Customer_ID for UserID: ' . $user_id);
            // Fallback: try to get from session if available
            $customer_id = $this->session->userdata('customer_id');
            if (!$customer_id) {
                $data['error'] = 'Unable to load customer data. Please contact support.';
                $data['user'] = $this->User_model->get_by_id($user_id);
                $data['orders_in_progress'] = 0;
                $data['recent_activity'] = null;
                $data['orders'] = [];
                $data['activity_feed'] = [];
                $data['last_update'] = null;
                $data['recommendations'] = $this->Product_model->get_recommended_products(4);
                $data['next_appointment'] = null;
                $data['title'] = "Glassify - Home";
                $this->load->view('includes/header', $data);
                $this->load->view('pages/home-login', $data);
                $this->load->view('includes/footer');
                return;
            }
        }
        
        // Get user data
        $data['user'] = $this->User_model->get_by_id($user_id);
        
        // Get order statistics (using Customer_ID, not UserID)
        $in_progress_statuses = ['Pending', 'Approved', 'In Fabrication', 'Ready for Installation'];
        $data['orders_in_progress'] = $this->Order_model->count_orders_by_status($customer_id, $in_progress_statuses);
        
        // Get recent activity (most recent non-completed order)
        $data['recent_activity'] = $this->Order_model->get_recent_order_activity($customer_id);
        
        // Get orders for the table
        $data['orders'] = $this->Order_model->get_customer_orders_with_products($customer_id, 10);
        
        // Get activity feed (fetch more for expandable list)
        $data['activity_feed'] = $this->Order_model->get_activity_feed($customer_id, 20);
        
        // Get last update time
        $data['last_update'] = $this->Order_model->get_last_update_time($customer_id);
        
        // Get recommended products
        $data['recommendations'] = $this->Product_model->get_recommended_products(4);
        
        // Get next appointment (placeholder - using order dates for now)
        $data['next_appointment'] = $this->get_next_appointment($customer_id);

        $data['title'] = "Glassify - Home";
        $this->load->view('includes/header', $data);
        $this->load->view('pages/home-login', $data);
        $this->load->view('includes/footer');
    }

    /**
     * Helper function to get next appointment
     * Uses order installation dates as appointments for now
     * @param int $customer_id Customer_ID (not UserID)
     */
    private function get_next_appointment($customer_id) {
        $this->load->database();
        
        // Get order with upcoming installation (estimated from order date)
        $this->db->select('
            o.OrderID,
            o.OrderDate,
            o.Status,
            DATE_ADD(o.OrderDate, INTERVAL 14 DAY) as AppointmentDate
        ');
        $this->db->from('`order` o');
        $this->db->where('o.Customer_ID', $customer_id);
        $this->db->where('o.Status !=', 'Completed');
        $this->db->where('o.Status !=', 'Cancelled');
        $this->db->order_by('o.OrderDate', 'DESC');
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }

 public function about() {
    $data['title'] = "Glassify - About Us";
    $this->load->view('includes/header', $data);
    $this->load->view('pages/about', $data); // this matches your file name
    $this->load->view('includes/footer');
}


/*     public function contact() {
        $data['title'] = "Glassify - Contact";
        $this->load->view('includes/header', $data);
        $this->load->view('contact', $data);
        $this->load->view('includes/footer');
    } */

  
   

    public function projects() {
        $data['title'] = "Glassify - Projects";
        $this->load->view('includes/header', $data);
        $this->load->view('pages/projects', $data);
        $this->load->view('includes/footer');
    }

    public function terms() {
        $data['title'] = "Glassify - Terms and Conditions";
        $data['force_guest_header'] = true; // Force guest header on terms page
        $this->load->view('includes/header', $data);
        $this->load->view('pages/terms', $data);
        $this->load->view('includes/footer');
    }

    public function process_quote_request() {
        // Load required libraries
        $this->load->library(['form_validation', 'email', 'session']);
        $this->load->database();
        
        // Form validation rules
        $this->form_validation->set_rules('first-name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last-name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email Address', 'required|valid_email|trim');
        $this->form_validation->set_rules('phone', 'Phone Number', 'required|trim');
        $this->form_validation->set_rules('needs', 'Project Needs', 'required|trim');
        $this->form_validation->set_rules('message', 'Message', 'trim');
        
        if ($this->form_validation->run() == FALSE) {
            // Validation failed
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('projects'));
            return;
        }
        
        // Get form data
        $first_name = $this->input->post('first-name');
        $last_name = $this->input->post('last-name');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $needs = $this->input->post('needs');
        $message = $this->input->post('message');
        
        // Check if customer email exists in user table
        $this->db->where('Email', $email);
        $existing_user = $this->db->get('user')->row();
        $account_status = $existing_user ? 'Existing Customer Account' : 'New Customer';
        
        // Get all active Sales Representatives
        $this->db->where('Role', 'Sales Representative');
        $this->db->where('Status', 'Active');
        $sales_reps = $this->db->get('user')->result();
        
        // Configure email - load config from email.php
        $this->load->config('email');
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
        
        // Send email to all active Sales Representatives
        $sales_rep_subject = "New Inquiry from " . $first_name . " " . $last_name;
        $sales_rep_body = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <h2 style='color: #083c5d;'>New Inquiry</h2>
                <p><strong>Customer Information:</strong></p>
                <ul>
                    <li><strong>Name:</strong> " . htmlspecialchars($first_name . " " . $last_name) . "</li>
                    <li><strong>Email:</strong> " . htmlspecialchars($email) . "</li>
                    <li><strong>Phone:</strong> " . htmlspecialchars($phone) . "</li>
                    <li><strong>Account Status:</strong> " . $account_status . "</li>
                </ul>
                <p><strong>Project Needs:</strong></p>
                <p>" . nl2br(htmlspecialchars($needs)) . "</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
                <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>
                <p style='color: #666; font-size: 12px;'>Please contact the customer to discuss their project requirements.</p>
            </body>
            </html>
        ";
        
        // Send to each sales rep individually
        $emails_sent = false;
        $email_errors = [];
        
        foreach ($sales_reps as $rep) {
            if (!empty($rep->Email)) {
                $this->email->clear();
                $this->email->from('glassifytesting@gmail.com', 'Glassify System');
                $this->email->to($rep->Email);
                $this->email->subject($sales_rep_subject);
                $this->email->message($sales_rep_body);
                if ($this->email->send()) {
                    $emails_sent = true;
                } else {
                    $email_errors[] = 'Sales rep email failed: ' . $this->email->print_debugger();
                }
            }
        }
        
        // Send confirmation email to customer
        $customer_subject = "Thank you for your inquiry - Glassify";
        $customer_body = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <h2 style='color: #083c5d;'>Thank You for Your Inquiry!</h2>
                <p>Dear " . htmlspecialchars($first_name) . ",</p>
                <p>We have received your inquiry and appreciate your interest in our services.</p>
                <p>Our sales team will review your project requirements and contact you soon at:</p>
                <ul>
                    <li><strong>Email:</strong> " . htmlspecialchars($email) . "</li>
                    <li><strong>Phone:</strong> " . htmlspecialchars($phone) . "</li>
                </ul>
                <p>In the meantime, if you have any urgent questions, please feel free to contact us:</p>
                <ul>
                    <li><strong>Phone:</strong> 0906 464 9709 / 0927 519 3800 / 0976 165 3506</li>
                    <li><strong>Email:</strong> glassworthbuilders@gmail.com</li>
                </ul>
                <p>We look forward to working with you!</p>
                <p>Best regards,<br>The Glassify Team</p>
            </body>
            </html>
        ";
        
        $this->email->clear();
        $this->email->from('glassifytesting@gmail.com', 'Glassify');
        $this->email->to($email);
        $this->email->subject($customer_subject);
        $this->email->message($customer_body);
        $customer_email_sent = $this->email->send();
        
        // Capture email debug info
        $email_debug = $this->email->print_debugger();
        
        // Log errors for debugging
        if (!empty($email_errors) || !$customer_email_sent) {
            log_message('error', 'Email sending failed. Debug: ' . $email_debug);
            if (!empty($email_errors)) {
                log_message('error', 'Sales rep email errors: ' . implode(' | ', $email_errors));
            }
        }
        
        // Set success message (even if email fails, form submission is successful)
        $success_msg = 'Thank you! Your inquiry has been submitted. We will contact you soon.';
        
        // In development, show email debug info if email failed
        if (ENVIRONMENT === 'development') {
            if (!$customer_email_sent) {
                $this->session->set_flashdata('email_debug', 'Customer email failed. Debug: ' . $email_debug);
            }
            if (!empty($email_errors)) {
                $this->session->set_flashdata('email_debug', 'Sales rep emails failed. ' . implode(' | ', $email_errors));
            }
        }
        
        $this->session->set_flashdata('success', $success_msg);
        redirect(base_url('projects'));
    }

    public function test_email() {
        // Test email functionality
        $this->load->library('email');
        $this->load->config('email');
        
        $test_email = $this->input->get('email') ?: 'glassifytesting@gmail.com';
        
        $data['title'] = "Email Test - Glassify";
        $data['test_email'] = $test_email;
        
        // Configure email
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
        
        $this->email->from('glassifytesting@gmail.com', 'Glassify Test');
        $this->email->to($test_email);
        $this->email->subject('Test Email from Glassify System');
        $this->email->message('<h1>Test Email</h1><p>If you receive this email, your SMTP configuration is working correctly!</p><p>Time: ' . date('Y-m-d H:i:s') . '</p>');
        
        $data['email_sent'] = $this->email->send();
        $data['email_debug'] = $this->email->print_debugger();
        $data['smtp_config'] = [
            'host' => $this->config->item('smtp_host'),
            'user' => $this->config->item('smtp_user'),
            'port' => $this->config->item('smtp_port'),
            'crypto' => $this->config->item('smtp_crypto'),
            'protocol' => $this->config->item('protocol')
        ];
        
        $this->load->view('includes/header', $data);
        $this->load->view('pages/test_email', $data);
        $this->load->view('includes/footer');
    }
    


  
}
