<?php
/**
 * Customer Notification Helper
 * 
 * Helper functions for sending notifications to customers
 * 
 * Usage in controllers:
 * $this->load->helper('notification');
 * send_customer_notification($customer_id, $title, $message, $type, $icon, $related_id, $related_type);
 */

if (!function_exists('send_customer_notification')) {
    /**
     * Send a notification to a customer
     * 
     * @param int $customer_id Customer ID
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type Notification type (Order, Payment, Delivery, General, System)
     * @param string $icon Font Awesome icon class (default: fa-info-circle)
     * @param int|null $related_id Related ID (OrderID, PaymentID, etc.)
     * @param string|null $related_type Related type (Order, Payment, etc.)
     * @param int|null $created_by UserID of admin/staff who created the notification
     * @param string|null $action_data JSON string containing action data for buttons/interactions
     * @return bool|int Returns NotificationID on success, false on failure
     */
    function send_customer_notification($customer_id, $title, $message, $type = 'General', $icon = 'fa-info-circle', $related_id = null, $related_type = null, $created_by = null, $action_data = null)
    {
        $CI =& get_instance();
        
        // Check if table exists
        if (!$CI->db->table_exists('customer_notifications')) {
            log_message('error', 'customer_notifications table does not exist');
            return false;
        }
        
        // Get current user ID if not provided
        if ($created_by === null && $CI->session->userdata('user_id')) {
            $created_by = $CI->session->userdata('user_id');
        }
        
        // Prepare notification data
        $data = [
            'Customer_ID' => (int)$customer_id,
            'Icon' => $icon,
            'Type' => $type,
            'Title' => $title,
            'Message' => $message,
            'Status' => 'Unread',
            'Created_Date' => date('Y-m-d H:i:s'),
            'RelatedID' => $related_id,
            'RelatedType' => $related_type,
            'CreatedBy' => $created_by
        ];
        
        // Add ActionData if field exists and action_data is provided
        if ($action_data !== null && $CI->db->field_exists('ActionData', 'customer_notifications')) {
            $data['ActionData'] = $action_data;
        }
        
        // Insert notification
        if ($CI->db->insert('customer_notifications', $data)) {
            $notification_id = $CI->db->insert_id();
            log_message('info', "Customer notification sent: Customer ID {$customer_id}, Title: {$title}, Notification ID: {$notification_id}");
            return $notification_id;
        } else {
            $error = $CI->db->error();
            $error_message = is_array($error) && isset($error['message']) ? $error['message'] : (is_string($error) ? $error : 'Unknown database error');
            log_message('error', 'Failed to insert customer notification. Error: ' . $error_message . ' | Data: ' . json_encode($data));
            return false;
        }
    }
}

if (!function_exists('send_order_notification')) {
    /**
     * Send an order-related notification to a customer
     * 
     * @param int $customer_id Customer ID
     * @param int $order_id Order ID
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $icon Font Awesome icon class
     * @param int|null $created_by UserID of admin/staff who created the notification
     * @return bool|int Returns NotificationID on success, false on failure
     */
    function send_order_notification($customer_id, $order_id, $title, $message, $icon = 'fa-shopping-cart', $created_by = null)
    {
        return send_customer_notification($customer_id, $title, $message, 'Order', $icon, $order_id, 'Order', $created_by);
    }
}

if (!function_exists('send_payment_notification')) {
    /**
     * Send a payment-related notification to a customer
     * 
     * @param int $customer_id Customer ID
     * @param int $payment_id Payment ID
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $icon Font Awesome icon class
     * @param int|null $created_by UserID of admin/staff who created the notification
     * @return bool|int Returns NotificationID on success, false on failure
     */
    function send_payment_notification($customer_id, $payment_id, $title, $message, $icon = 'fa-credit-card', $created_by = null)
    {
        return send_customer_notification($customer_id, $title, $message, 'Payment', $icon, $payment_id, 'Payment', $created_by);
    }
}

if (!function_exists('send_delivery_notification')) {
    /**
     * Send a delivery-related notification to a customer
     * 
     * @param int $customer_id Customer ID
     * @param int $order_id Order ID
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $icon Font Awesome icon class
     * @param int|null $created_by UserID of admin/staff who created the notification
     * @return bool|int Returns NotificationID on success, false on failure
     */
    function send_delivery_notification($customer_id, $order_id, $title, $message, $icon = 'fa-truck', $created_by = null)
    {
        return send_customer_notification($customer_id, $title, $message, 'Delivery', $icon, $order_id, 'Order', $created_by);
    }
}
