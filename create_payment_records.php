<?php
/**
 * Script to create payment records for existing approved orders
 * This will backfill payment records for orders that were approved before payment creation was implemented
 */

// Load CodeIgniter
define('ENVIRONMENT', 'development');
require_once('index.php');

$ci =& get_instance();
$ci->load->database();

echo "Starting payment record creation for approved orders...\n\n";

// Get all approved orders
$ci->db->select('*');
$ci->db->from('approved_orders');
$approved_orders = $ci->db->get()->result();

echo "Found " . count($approved_orders) . " approved orders\n\n";

$created = 0;
$skipped = 0;
$errors = 0;

foreach ($approved_orders as $approved_order) {
    // Extract numeric OrderID (GI001 -> 1)
    $order_id_string = $approved_order->OrderID;
    $order_id_numeric = str_replace('GI', '', $order_id_string);
    $order_id_numeric = ltrim($order_id_numeric, '0');
    if (empty($order_id_numeric)) {
        $order_id_numeric = 1;
    }
    $order_id_numeric = (int)$order_id_numeric;
    
    echo "Processing Order: {$order_id_string} (numeric: {$order_id_numeric})\n";
    
    // Check if order exists in order table
    $ci->db->where('OrderID', $order_id_numeric);
    $existing_order = $ci->db->get('order')->row();
    
    if (!$existing_order) {
        // Create order record first
        echo "  - Creating order record...\n";
        $order_data = [
            'OrderID' => $order_id_numeric,
            'Customer_ID' => $approved_order->Customer_ID,
            'SalesRep_ID' => $approved_order->SalesRep_ID,
            'OrderDate' => $approved_order->OrderDate,
            'TotalAmount' => $approved_order->TotalQuotation,
            'Status' => 'Approved',
            'PaymentStatus' => 'Pending',
            'DeliveryAddress' => $approved_order->Address
        ];
        
        $insert_order = $ci->db->insert('order', $order_data);
        if (!$insert_order) {
            $error = $ci->db->error();
            echo "  - ERROR: Failed to create order record: " . json_encode($error) . "\n";
            $errors++;
            continue;
        }
        echo "  - Order record created successfully\n";
    } else {
        echo "  - Order record already exists\n";
    }
    
    // Check if payment record already exists
    $ci->db->where('OrderID', $order_id_numeric);
    $existing_payment = $ci->db->get('payment')->row();
    
    if ($existing_payment) {
        echo "  - Payment record already exists, skipping\n";
        $skipped++;
        continue;
    }
    
    // Get customer name
    $ci->db->select('First_Name, Last_Name');
    $ci->db->where('UserID', $approved_order->Customer_ID);
    $customer = $ci->db->get('user')->row();
    $customer_name = '';
    if ($customer) {
        $customer_name = trim(($customer->First_Name ?? '') . ' ' . ($customer->Last_Name ?? ''));
    }
    
    // Create payment record
    echo "  - Creating payment record...\n";
    $payment_data = [
        'OrderID' => $order_id_numeric,
        'CustomerName' => $customer_name,
        'ProductName' => $approved_order->ProductName,
        'PaymentMethod' => $approved_order->PaymentMethod ?? null,
        'Amount' => $approved_order->TotalQuotation,
        'Payment_Date' => $approved_order->Approved_Date ?: date('Y-m-d H:i:s'),
        'Transaction_ID' => null,
        'Status' => 'Pending'
    ];
    
    $insert_payment = $ci->db->insert('payment', $payment_data);
    if (!$insert_payment) {
        $error = $ci->db->error();
        echo "  - ERROR: Failed to create payment record: " . json_encode($error) . "\n";
        $errors++;
    } else {
        echo "  - Payment record created successfully (Amount: ₱" . number_format($approved_order->TotalQuotation, 2) . ")\n";
        $created++;
    }
    
    echo "\n";
}

echo "\n=== Summary ===\n";
echo "Created: {$created}\n";
echo "Skipped: {$skipped}\n";
echo "Errors: {$errors}\n";
echo "\nDone!\n";

