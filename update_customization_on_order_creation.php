<?php
/**
 * Helper function to update customization record when order is created
 * 
 * This should be called AFTER an order is inserted into the order table
 * 
 * @param int $order_id The newly created OrderID
 * @param int $customer_id The Customer_ID
 * @param string $delivery_address The delivery address from order
 * @param object $db CodeIgniter database object
 * @return bool Success status
 */
function update_customization_on_order_creation($order_id, $customer_id, $delivery_address, $db) {
    // Find the customization record for this customer that doesn't have an OrderID yet
    $db->where('Customer_ID', $customer_id);
    $db->where('OrderID IS NULL', null, false);
    $db->order_by('Created_Date', 'DESC');
    $db->limit(1);
    $customization = $db->get('customization')->row();
    
    if ($customization) {
        // Update customization with OrderID, Address, and Date
        $update_data = [
            'OrderID' => $order_id,
            'DeliveryAddress' => $delivery_address,
            'OrderDate' => date('Y-m-d H:i:s')
        ];
        
        $db->where('CustomizationID', $customization->CustomizationID);
        return $db->update('customization', $update_data);
    }
    
    return false;
}

// Example usage in order creation code:
/*
// After inserting order:
$order_data = [
    'Customer_ID' => $customer_id,
    'SalesRep_ID' => $sales_rep_id,
    'TotalAmount' => $total_amount,
    'DeliveryAddress' => $delivery_address,
    // ... other fields
];
$this->db->insert('order', $order_data);
$order_id = $this->db->insert_id();

// Update customization record
update_customization_on_order_creation($order_id, $customer_id, $delivery_address, $this->db);
*/

