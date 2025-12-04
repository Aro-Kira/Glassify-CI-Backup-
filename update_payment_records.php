<?php
/**
 * Script to update existing payment records with CustomerName, ProductName, and PaymentMethod
 */

// Load CodeIgniter
define('ENVIRONMENT', 'development');
require_once('index.php');

$ci =& get_instance();
$ci->load->database();

echo "Updating payment records with customer name, product name, and payment method...\n\n";

// Get all payment records
$ci->db->select('*');
$ci->db->from('payment');
$payments = $ci->db->get()->result();

echo "Found " . count($payments) . " payment records\n\n";

$updated = 0;
$skipped = 0;
$errors = 0;

foreach ($payments as $payment) {
    $order_id_numeric = $payment->OrderID;
    $order_id_string = 'GI' . str_pad($order_id_numeric, 3, '0', STR_PAD_LEFT);
    
    echo "Processing Payment ID: {$payment->Payment_ID} (OrderID: {$order_id_string})\n";
    
    // Get order details from approved_orders
    $ci->db->where('OrderID', $order_id_string);
    $order = $ci->db->get('approved_orders')->row();
    
    if (!$order) {
        echo "  - WARNING: Order not found in approved_orders\n";
        $skipped++;
        continue;
    }
    
    // Get customer name
    $ci->db->select('First_Name, Last_Name');
    $ci->db->where('UserID', $order->Customer_ID);
    $customer = $ci->db->get('user')->row();
    $customer_name = '';
    if ($customer) {
        $customer_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
    }
    
    // Update payment record
    $update_data = [];
    if (empty($payment->CustomerName) || $payment->CustomerName !== $customer_name) {
        $update_data['CustomerName'] = $customer_name;
    }
    if (empty($payment->ProductName) || $payment->ProductName !== $order->ProductName) {
        $update_data['ProductName'] = $order->ProductName;
    }
    if (empty($payment->PaymentMethod) && !empty($order->PaymentMethod)) {
        $update_data['PaymentMethod'] = $order->PaymentMethod;
    }
    
    if (!empty($update_data)) {
        echo "  - Updating: " . json_encode($update_data) . "\n";
        $ci->db->where('Payment_ID', $payment->Payment_ID);
        $update_result = $ci->db->update('payment', $update_data);
        
        if ($update_result) {
            echo "  - Updated successfully\n";
            $updated++;
        } else {
            $error = $ci->db->error();
            echo "  - ERROR: Failed to update: " . json_encode($error) . "\n";
            $errors++;
        }
    } else {
        echo "  - No updates needed (all fields already set)\n";
        $skipped++;
    }
    
    echo "\n";
}

echo "\n=== Summary ===\n";
echo "Updated: {$updated}\n";
echo "Skipped: {$skipped}\n";
echo "Errors: {$errors}\n";
echo "\nDone!\n";

