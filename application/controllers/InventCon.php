<?php
defined('BASEPATH') or exit('No direct script access allowed');

class InventCon extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Check if user is logged in and has Inventory Officer role
        if (!$this->session->userdata('is_logged_in') || $this->session->userdata('user_role') !== 'Inventory Officer') {
            $this->session->set_flashdata('error', 'Access denied. You must be logged in as an Inventory Officer.');
            redirect(base_url('InvLog'));
        }
    }
    
    public function inventory_dashboard()
    {
        $data['title'] = "Glassify - Inventory Dashboard";
        $data['active'] = 'dashboard';
        $data['content_view'] = 'inventory_page/inventory_dashboard';
        $data['page_css'] = 'inventory_css/inventory_dashboard.css';
        $this->load->view('inventory_page/layout', $data);
    }

    public function inventory_products()
    {
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
        $data['title'] = "Glassify - Inventory Account";
        $data['active'] = 'account';
        $data['content_view'] = 'inventory_page/inventory_account';
        $data['page_css'] = 'admin_css/admin_accounts.css';
        $this->load->view('inventory_page/layout', $data);
    }

    public function inventory_reports()
    {
        $data['title'] = "Glassify - Inventory Reports";
        $data['active'] = 'reports';
        $data['content_view'] = 'inventory_page/inventory_reports';
        $data['page_css'] = 'inventory_css/inventory_reports.css';
        $this->load->view('inventory_page/layout', $data);
    }

    public function inventory_notif()
    {
        $data['title'] = "Glassify - Inventory Notifications";
        $data['active'] = 'notif';
        $data['content_view'] = 'inventory_page/inventory_notif';
        $data['page_css'] = 'admin_css/admin_notif.css';
        $this->load->view('inventory_page/layout', $data);
    }
}
