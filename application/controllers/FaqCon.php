<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FaqCon extends CI_Controller {

      /* 
======================================
=============FAQ Directory============
======================================
 */

 public function faq() {
        $data['title'] = "Glassify - FAQ";
        $this->load->view('includes/header', $data);
        $this->load->view('faq/faq', $data);
        $this->load->view('includes/footer');
    }

    public function faq_ordering() {
        $data['title'] = "Glassify - FAQ Ordering & Product Customization";

        $this->load->view('includes/header', $data);
        $this->load->view('faq/faq_ordering', $data);
        $this->load->view('includes/footer');
      
    }
      public function faq_payment() {
        $data['title'] = "Glassify - FAQ Payments";
        $this->load->view('includes/header', $data);
        $this->load->view('faq/faq_payments', $data);
        $this->load->view('includes/footer');
    }
    public function faq_shipping() {
        $data['title'] = "Glassify - FAQ Shipping & Installation";
        $this->load->view('includes/header', $data);
        $this->load->view('faq/faq_shipping', $data);
        $this->load->view('includes/footer');
    }
    public function faq_warranty() {
        $data['title'] = "Glassify - FAQ Warranty";
        $this->load->view('includes/header', $data);
        $this->load->view('faq/faq_warranty', $data);
        $this->load->view('includes/footer');
    }
     public function faq_pricing() {
        $data['title'] = "Glassify - FAQ Pricing & Quotations";
        $this->load->view('includes/header', $data);
        $this->load->view('faq/faq_pricing', $data);
        $this->load->view('includes/footer');
    }
    public function faq_account() {
        $data['title'] = "Glassify - FAQ Account";
        $this->load->view('includes/header', $data);
        $this->load->view('faq/faq_account', $data);
        $this->load->view('includes/footer');
    }

    public function faq_report() {
        $this->load->helper(['form', 'url']); // Load form helper for any form-related functions
        $this->load->model('User_model');
        
        $data['title'] = "Glassify - Report Issue";
        
        // Get user data if logged in
        if ($this->session->userdata('is_logged_in')) {
            $user_id = $this->session->userdata('user_id');
            $data['user'] = $this->User_model->get_by_id($user_id);
        } else {
            $data['user'] = null;
        }
        
        $this->load->view('includes/header', $data);
        $this->load->view('faq/report_issue', $data);
        $this->load->view('includes/footer');
    }

    /**
     * Process issue report submission
     */
    public function submit_issue() {
        $this->load->library(['form_validation', 'session', 'upload']);
        $this->load->helper(['url', 'form']);
        $this->load->database();
        $this->load->model('Issue_model');

        // Validation rules
        $this->form_validation->set_rules('first-name', 'First Name', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('middle-name', 'Middle Name', 'trim|max_length[50]');
        $this->form_validation->set_rules('last-name', 'Last Name', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|max_length[100]');
        $this->form_validation->set_rules('contact-number', 'Contact Number', 'required|trim|max_length[13]');
        $this->form_validation->set_rules('order-id', 'Order ID', 'trim'); // Made optional
        $this->form_validation->set_rules('issue-category', 'Issue Category', 'required|trim');
        $this->form_validation->set_rules('description', 'Description', 'required|trim|min_length[20]');

        if ($this->form_validation->run() == FALSE) {
            // Preserve form data in flashdata for repopulating
            $this->session->set_flashdata('form_data', [
                'first-name' => $this->input->post('first-name'),
                'middle-name' => $this->input->post('middle-name'),
                'last-name' => $this->input->post('last-name'),
                'email' => $this->input->post('email'),
                'contact-number' => $this->input->post('contact-number'),
                'order-id' => $this->input->post('order-id'),
                'issue-category' => $this->input->post('issue-category'),
                'description' => $this->input->post('description')
            ]);
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('report-issue'));
            return;
        }

        // Get form data
        $first_name = $this->input->post('first-name');
        $middle_name = $this->input->post('middle-name');
        $last_name = $this->input->post('last-name');
        $email = $this->input->post('email');
        $phone = $this->input->post('contact-number');
        $order_id_input = $this->input->post('order-id');
        $category = $this->input->post('issue-category');
        $description = $this->input->post('description');
        
        // Handle file upload
        $file_path = NULL;
        if (!empty($_FILES['attachment']['name'])) {
            $config['upload_path'] = './uploads/issues/';
            $config['allowed_types'] = 'png|pdf|jpg|jpeg';
            $config['max_size'] = 5120; // 5MB
            $config['encrypt_name'] = TRUE;
            
            // Create directory if it doesn't exist
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, TRUE);
            }
            
            $this->upload->initialize($config);
            
            if ($this->upload->do_upload('attachment')) {
                $upload_data = $this->upload->data();
                $file_path = 'uploads/issues/' . $upload_data['file_name'];
            } else {
                $upload_error = $this->upload->display_errors('', '');
                // Preserve form data
                $this->session->set_flashdata('form_data', [
                    'first-name' => $first_name,
                    'middle-name' => $middle_name,
                    'last-name' => $last_name,
                    'email' => $email,
                    'contact-number' => $phone,
                    'order-id' => $order_id_input,
                    'issue-category' => $category,
                    'description' => $description
                ]);
                $this->session->set_flashdata('error', 'File upload failed: ' . $upload_error);
                redirect(base_url('report-issue'));
                return;
            }
        }

        // Handle Order ID - remove #G prefix if present, convert to integer
        $order_id_clean = preg_replace('/[^0-9]/', '', $order_id_input);
        $order_id = (int)$order_id_clean;

        // Get Customer_ID if logged in
        $user_id = $this->session->userdata('user_id');
        $customer_id = NULL;
        
        if ($user_id) {
            // User is logged in - find their Customer_ID from customer table
            $this->db->select('Customer_ID');
            $this->db->from('customer');
            $this->db->where('UserID', $user_id);
            $customer = $this->db->get()->row();
            
            if ($customer) {
                $customer_id = $customer->Customer_ID;
            } else {
                // User exists but no customer record - try to create one or use NULL
                // For now, use NULL (guest submission)
                $customer_id = NULL;
            }
        } else {
            // Guest - try to find if email exists in customer table
            $this->db->select('customer.Customer_ID');
            $this->db->from('customer');
            $this->db->join('user', 'user.UserID = customer.UserID');
            $this->db->where('user.Email', $email);
            $customer = $this->db->get()->row();
            $customer_id = $customer ? $customer->Customer_ID : NULL;
        }

        // Verify Order ID exists (if provided)
        if ($order_id > 0) {
            $order_exists = $this->db->where('OrderID', $order_id)->get('order')->row();
            if (!$order_exists) {
                // Order doesn't exist, set to NULL for guest submission
                $order_id = NULL;
            }
        } else {
            $order_id = NULL; // No order ID provided
        }

        // Map form categories to database categories
        $category_map = [
            'Order Issue' => 'Order Issue',
            'Payment Issue' => 'Payment Issue',
            'Delivery Issue' => 'Delivery Issue',
            'General Inquiry' => 'General Inquiry',
            'Installation Problems' => 'Installation Problems',
            'Product Defect/Damage' => 'Product Defect/Damage',
            'Measurement/Design Problems' => 'Measurement/Design Problems',
            'Billing/Payment Questions' => 'Billing/Payment Questions',
            'Other' => 'Other'
        ];
        
        $db_category = isset($category_map[$category]) ? $category_map[$category] : 'Other';

        // Prepare issue data
        // Use NULL instead of 0 for guest submissions to avoid foreign key issues
        // Note: Middle_Name field is collected in form but not stored in database (column doesn't exist in issuereport table)
        $issue_data = [
            'First_Name' => $first_name,
            'Last_Name' => $last_name,
            'Email' => $email,
            'PhoneNum' => $phone,
            'Category' => $db_category,
            'Description' => $description,
            'FileAttached' => $file_path,
            'Status' => 'Open',
            'Priority' => 'Low',
            'Report_Date' => date('Y-m-d H:i:s')
        ];
        
        // Only set Customer_ID and Order_ID if they have valid values
        if ($customer_id !== NULL && $customer_id > 0) {
            $issue_data['Customer_ID'] = $customer_id;
        }
        
        if ($order_id !== NULL && $order_id > 0) {
            $issue_data['Order_ID'] = $order_id;
        }

        // Insert issue
        $issue_id = $this->Issue_model->create_issue($issue_data);

        if ($issue_id) {
            $this->session->set_flashdata('success', 'Your issue has been submitted successfully. Ticket ID: #TC-' . str_pad($issue_id, 2, '0', STR_PAD_LEFT));
            redirect(base_url('report-issue'));
        } else {
            // Get database error for debugging
            $db_error = $this->db->error();
            $error_message = 'Failed to submit issue. ';
            
            if (!empty($db_error['message'])) {
                $error_message .= 'Database Error: ' . $db_error['message'];
                log_message('error', 'Issue submission failed: ' . $db_error['message']);
                log_message('error', 'Issue data: ' . print_r($issue_data, true));
            } else {
                $error_message .= 'Please check all fields and try again.';
            }
            
            $this->session->set_flashdata('error', $error_message);
            redirect(base_url('report-issue'));
        }
    }









}
