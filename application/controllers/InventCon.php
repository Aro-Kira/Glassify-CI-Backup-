<?php
defined('BASEPATH') or exit('No direct script access allowed');

class InventCon extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->database();
        $this->load->model('User_model');
        
        // If a logged-in customer tries to access inventory pages, force logout and redirect
        if ($this->session->userdata('is_logged_in') && $this->session->userdata('user_role') === 'Customer') {
            // Set error message BEFORE clearing session data (flashdata needs active session)
            $this->session->set_flashdata('error', '⚠️ Access Denied: This page is restricted to Inventory Officer employees only. Customer accounts cannot access employee pages. You have been logged out for security reasons.');
            
            // Clear all user session data (but keep session alive for flashdata)
            $this->session->unset_userdata(['is_logged_in', 'user_id', 'user_name', 'user_email', 'user_role', 'customer_id']);
            
            // Set cache control headers to prevent back button access after force logout
            $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
            $this->output->set_header('Pragma: no-cache');
            $this->output->set_header('Expires: 0');
            
            redirect(base_url('login'));
        }
        
        // Check if user is logged in and has Inventory Officer role
        // Don't check auth for update_account (it handles its own auth check)
        $method = $this->router->method;
        if ($method !== 'update_account') {
            if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Inventory Officer') {
                $this->session->set_flashdata('error', 'Access denied. You must be logged in as an Inventory Officer.');
                redirect(base_url('InvLog'));
            }
        }
    }
    
    public function inventory_dashboard()
    {
        $this->load->model('Inventory_model');
        $this->load->model('Product_model');
        
        // Get statistics (uses same logic as inventory page)
        $stats = $this->Inventory_model->get_statistics();
        
        // Use the same new items count calculation as inventory page
        // (Status = 'New' OR DateAdded >= 2 days ago)
        $new_items_count = $stats['newItems'];
        
        // Get low stock count (same as statistics)
        $low_stock_count = $stats['lowStockAlerts'];
        
        // Get total products count
        $total_products = $this->db->count_all_results('product');
        
        // Get recent activities (last 10)
        $recent_activities = $this->Inventory_model->get_activities(10);
        
        $data['new_items_count'] = $new_items_count;
        $data['low_stock_count'] = $low_stock_count;
        $data['total_products'] = $total_products;
        $data['recent_activities'] = $recent_activities;
        $data['title'] = "Glassify - Inventory Dashboard";
        $data['active'] = 'dashboard';
        $data['content_view'] = 'inventory_page/inventory_dashboard';
        $data['page_css'] = 'inventory_css/inventory_dashboard.css';
        $this->load->view('inventory_page/layout', $data);
    }

    public function inventory_products()
    {
        $this->load->model('Product_model');
        $this->load->model('Inventory_model');
        
        // Fetch products from the DB
        $products = $this->Product_model->get_products();
        
        // Get product materials and update status for each product
        $products_with_materials = [];
        foreach ($products as $product) {
            $product_materials = $this->Inventory_model->get_product_materials($product->Product_ID);
            $product->current_material_id = !empty($product_materials) ? $product_materials[0]->InventoryItemID : '';
            
            // Update product status based on materials
            $this->Inventory_model->update_product_status_from_materials($product->Product_ID);
            
            // Reload product to get updated status
            $product = $this->Product_model->get_product($product->Product_ID);
            $products_with_materials[] = $product;
        }
        $data['products'] = $products_with_materials;
        
        // Fetch inventory items (raw materials) for material dropdown
        $data['inventory_items'] = $this->Inventory_model->get_all_items();
        
        // Get unique categories for filter dropdown
        $categories = [];
        foreach ($products as $product) {
            if (!empty($product->Category) && !in_array($product->Category, $categories)) {
                $categories[] = $product->Category;
            }
        }
        $data['categories'] = $categories;
        
        $data['title'] = "Glassify - Inventory Products";
        $data['active'] = 'product';
        $data['content_view'] = 'inventory_page/inventory_products';
        $data['page_css'] = 'admin_css/admin_product.css';
        $this->load->view('inventory_page/layout', $data);
    }

    public function inventory_inventory()
    {
        $this->load->model('Inventory_model');
        
        // Get all inventory items
        $inventory_items = $this->Inventory_model->get_all_items();
        
        // Get statistics
        $statistics = $this->Inventory_model->get_statistics();
        
        // Get recent activities
        $activities = $this->Inventory_model->get_activities(10);
        
        // Get unread notifications
        $notifications = $this->Inventory_model->get_unread_notifications();
        
        // Prepare data for view
        $data['inventory_items'] = $inventory_items;
        $data['statistics'] = $statistics;
        $data['activities'] = $activities;
        $data['notifications'] = $notifications;
        $data['total_items'] = $statistics['totalItems'];
        $data['low_stock_count'] = $statistics['lowStockAlerts'];
        $data['new_items_count'] = $statistics['newItems'];
        $data['out_of_stock_count'] = $statistics['outOfStock'];
        $data['recent_requests'] = $statistics['recentRequests'];
        
        $data['title'] = "Glassify - Inventory Management";
        $data['active'] = 'inventory';
        $data['content_view'] = 'inventory_page/inventory_inventory';
        $data['page_css'] = 'inventory_css/inventory_inventory.css';
        $this->load->view('inventory_page/layout', $data);
    }

    public function inventory_account()
    {
        // Get logged-in Inventory Officer's UserID
        $user_id = $this->session->userdata('user_id');
        
        // Get Inventory Officer's information from database
        $inventory_officer = $this->User_model->get_by_id($user_id);
        
        if (!$inventory_officer) {
            $this->session->set_flashdata('error', 'User information not found.');
            redirect(base_url('inventory-dashboard'));
        }
        
        // Pass Inventory Officer's information to view
        $data['inventory_officer'] = $inventory_officer;
        
        $data['title'] = "Glassify - Inventory Account";
        $data['active'] = 'account';
        $data['content_view'] = 'inventory_page/inventory_account';
        $data['page_css'] = 'admin_css/admin_accounts.css';
        $this->load->view('inventory_page/layout', $data);
    }

    public function update_account()
    {
        // Set JSON header first
        header('Content-Type: application/json');
        
        // Check if user is authenticated
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Inventory Officer') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized - Please log in again']);
            return;
        }

        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'User ID not found in session']);
            return;
        }

        // Get POST data
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        
        // Also try raw input in case POST isn't working
        if (empty($field)) {
            $raw_input = file_get_contents('php://input');
            parse_str($raw_input, $parsed);
            if (!empty($parsed['field'])) {
                $field = $parsed['field'];
                $value = $parsed['value'] ?? '';
            }
        }

        if (empty($field)) {
            echo json_encode(['success' => false, 'message' => 'Field name is required']);
            return;
        }

        // Validate field name (prevent SQL injection)
        $allowed_fields = ['First_Name', 'Last_Name', 'Middle_Name', 'PhoneNum', 'Password'];
        if (!in_array($field, $allowed_fields)) {
            echo json_encode(['success' => false, 'message' => 'Invalid field name']);
            return;
        }

        // Prepare update data
        $update_data = [];
        
        if ($field === 'Password') {
            // Hash password before storing
            if (empty($value)) {
                echo json_encode(['success' => false, 'message' => 'Password cannot be empty']);
                return;
            }
            $update_data['Password'] = password_hash($value, PASSWORD_DEFAULT);
        } else {
            $update_data[$field] = $value;
        }

        // Check if value actually changed (prevent unnecessary updates)
        $current_user = $this->User_model->get_by_id($user_id);
        if (!$current_user) {
            echo json_encode(['success' => false, 'message' => 'User account not found']);
            return;
        }

        // Check if the new value is different from current value
        $current_value = $current_user->$field ?? '';
        
        if ($field === 'Password') {
            // For password, we always update (can't compare hashes)
            // But verify the new password is different from old one by checking if it verifies
            if (!empty($current_value) && password_verify($value, $current_value)) {
                echo json_encode(['success' => false, 'message' => 'New password must be different from the current password.']);
                return;
            }
        } else {
            // For other fields, compare trimmed values
            $current_trimmed = trim($current_value);
            $new_trimmed = trim($value);
            
            if ($current_trimmed === $new_trimmed) {
                echo json_encode(['success' => false, 'message' => 'No changes detected. The value is the same as the current value.']);
                return;
            }
            
            log_message('debug', 'Value change detected: Current="' . $current_trimmed . '", New="' . $new_trimmed . '"');
        }

        // Log what we're about to update
        log_message('debug', 'Attempting to update: UserID=' . $user_id . ', Data=' . json_encode($update_data));

        // Update in database
        $result = $this->User_model->update_account($user_id, $update_data);
        
        // Get affected rows and error info from the database
        $affected_rows = $this->db->affected_rows();
        $db_error = $this->db->error();
        
        log_message('debug', 'Controller update_account: Model result=' . ($result ? 'true' : 'false') . ', Affected rows=' . $affected_rows . ', DB Error: ' . json_encode($db_error));
        
        if ($result && $affected_rows > 0) {
            // Update was successful - verify by fetching the user again
            $updated_user = $this->User_model->get_by_id($user_id);
            $verification_passed = false;
            
            if ($updated_user) {
                if ($field === 'Password') {
                    // For password, verify it was hashed (starts with $2y$)
                    $verification_passed = (strpos($updated_user->Password, '$2y$') === 0);
                    log_message('debug', 'Password verification: ' . ($verification_passed ? 'passed' : 'failed') . ' (hash starts with: ' . substr($updated_user->Password, 0, 4) . ')');
                } else {
                    // For other fields, check if value matches (trim for comparison)
                    $updated_value = trim($updated_user->$field ?? '');
                    $expected_value = trim($value);
                    $verification_passed = ($updated_value === $expected_value);
                    log_message('debug', 'Field verification: Updated="' . $updated_value . '", Expected="' . $expected_value . '", Match=' . ($verification_passed ? 'yes' : 'no'));
                }
            }
            
            if ($verification_passed) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Account updated successfully',
                    'field' => $field,
                    'value' => $field === 'Password' ? '***' : $updated_user->$field
                ]);
            } else {
                log_message('error', 'Update verification failed for UserID=' . $user_id . ', Field=' . $field);
                echo json_encode(['success' => false, 'message' => 'Update completed but verification failed. Please refresh the page.']);
            }
        } else {
            // Update failed
            $error_message = 'Failed to update account';
            if (!empty($db_error['message'])) {
                $error_message .= ': ' . $db_error['message'];
            }
            log_message('error', 'Update failed for UserID=' . $user_id . ', Field=' . $field . ', Error: ' . json_encode($db_error));
            echo json_encode(['success' => false, 'message' => $error_message]);
        }
    }

    public function inventory_reports()
    {
        $data['title'] = "Glassify - Inventory Reports";
        $data['active'] = 'reports';
        $data['content_view'] = 'inventory_page/inventory_reports';
        $data['page_css'] = 'inventory_css/inventory_reports.css';
        $this->load->view('inventory_page/layout', $data);
    }
    
    /**
     * Export stock status report to Excel
     */
    public function export_stock_report()
    {
        $this->load->model('Inventory_model');
        
        // Get all inventory items
        $items = $this->Inventory_model->get_all_items();
        
        // Set headers for Excel file
        $filename = 'Stock_Status_Report_' . date('Y-m-d_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Start output
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        
        // Create Excel content
        echo "<table border='1'>\n";
        
        // Header row
        echo "<tr style='background-color: #4CAF50; color: white; font-weight: bold;'>\n";
        echo "<th>Item ID</th>\n";
        echo "<th>Item Name</th>\n";
        echo "<th>Category</th>\n";
        echo "<th>Current Stock</th>\n";
        echo "<th>Min Threshold</th>\n";
        echo "<th>Status</th>\n";
        echo "<th>Unit</th>\n";
        echo "<th>Date Added</th>\n";
        echo "</tr>\n";
        
        // Data rows
        foreach ($items as $item) {
            $stock = $item->InStock ?? 0;
            $minThreshold = $item->min_threshold ?? 10;
            
            // Determine status
            $status = 'In Stock';
            if ($stock == 0) {
                $status = 'Out of Stock';
            } elseif ($stock < $minThreshold) {
                $status = 'Low Stock';
            }
            
            echo "<tr>\n";
            echo "<td>" . htmlspecialchars($item->ItemID ?? 'N/A') . "</td>\n";
            echo "<td>" . htmlspecialchars($item->Name ?? 'N/A') . "</td>\n";
            echo "<td>" . htmlspecialchars($item->Category ?? 'N/A') . "</td>\n";
            echo "<td>" . $stock . "</td>\n";
            echo "<td>" . $minThreshold . "</td>\n";
            echo "<td>" . htmlspecialchars($status) . "</td>\n";
            echo "<td>" . htmlspecialchars($item->Unit ?? 'N/A') . "</td>\n";
            echo "<td>" . ($item->DateAdded ? date('Y-m-d H:i:s', strtotime($item->DateAdded)) : 'N/A') . "</td>\n";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
        exit;
    }

    public function inventory_notif()
    {
        // Get ALL notifications from inventory_notifications table
        $this->db->order_by('Created_Date', 'DESC');
        $notifications = $this->db->get('inventory_notifications')->result();
        
        // Format notifications for display
        $all_notifications = [];
        foreach ($notifications as $notif) {
            // Format notifications similar to sales/admin
            $all_notifications[] = (object)[
                'Action' => 'Inventory Alert',
                'Description' => $notif->Message ?? '',
                'Icon' => 'fa-box-open',
                'Role' => 'System',
                'Timestamp' => $notif->Created_Date ?? date('Y-m-d H:i:s'),
                'Status' => strtolower($notif->Status ?? 'read') // 'unread' or 'read'
            ];
        }
        
        $data['notifications'] = $all_notifications;
        $data['title'] = "Glassify - Notifications";
        $data['active'] = 'notif';
        $data['content_view'] = 'inventory_page/inventory_notif';
        $data['page_css'] = 'sales_css/sales_notif.css';
        $this->load->view('inventory_page/layout', $data);
    }
    
    /**
     * Get unread notification count (AJAX endpoint)
     */
    public function get_notification_count_ajax()
    {
        header('Content-Type: application/json');
        
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Inventory Officer') {
            echo json_encode(['status' => 'error', 'count' => 0]);
            return;
        }
        
        $this->load->model('Inventory_model');
        $this->db->where('Status', 'Unread');
        $count = $this->db->count_all_results('inventory_notifications');
        
        // Limit to 99, show 99+ if more
        if ($count > 99) {
            $display_count = '99+';
        } else {
            $display_count = $count;
        }
        
        echo json_encode(['status' => 'success', 'count' => $count, 'display' => $display_count]);
    }
}
