<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all inventory items
     */
    public function get_all_items()
    {
        return $this->db->get('inventory_items')->result();
    }
    
    /**
     * Get inventory item by ID
     */
    public function get_item($inventory_item_id)
    {
        $this->db->where('InventoryItemID', $inventory_item_id);
        return $this->db->get('inventory_items')->row();
    }
    
    /**
     * Get materials required for a product
     */
    public function get_product_materials($product_id)
    {
        $this->db->select('pm.*, ii.ItemID, ii.Name as ItemName, ii.InStock, ii.Unit');
        $this->db->from('product_materials pm');
        $this->db->join('inventory_items ii', 'ii.InventoryItemID = pm.InventoryItemID');
        $this->db->where('pm.Product_ID', $product_id);
        return $this->db->get()->result();
    }
    
    /**
     * Check if product can be manufactured (all materials available)
     */
    public function can_manufacture_product($product_id, $quantity = 1)
    {
        $materials = $this->get_product_materials($product_id);
        $missing_materials = [];
        
        foreach ($materials as $material) {
            $required = $material->QuantityRequired * $quantity;
            if ($material->InStock < $required) {
                $missing_materials[] = [
                    'ItemID' => $material->ItemID,
                    'ItemName' => $material->ItemName,
                    'Required' => $required,
                    'Available' => $material->InStock,
                    'Shortage' => $required - $material->InStock
                ];
            }
        }
        
        return [
            'can_manufacture' => empty($missing_materials),
            'missing_materials' => $missing_materials
        ];
    }
    
    /**
     * Deduct materials from inventory when order is paid
     */
    public function deduct_materials_for_order($order_id, $product_id, $quantity = 1)
    {
        $this->db->trans_start();
        
        $materials = $this->get_product_materials($product_id);
        $deductions = [];
        $out_of_stock_items = [];
        
        foreach ($materials as $material) {
            $required = $material->QuantityRequired * $quantity;
            $current_stock = $material->InStock;
            
            if ($current_stock < $required) {
                // Not enough stock - record but don't deduct
                $out_of_stock_items[] = [
                    'ItemID' => $material->ItemID,
                    'ItemName' => $material->ItemName,
                    'Required' => $required,
                    'Available' => $current_stock
                ];
                continue;
            }
            
            // Deduct from inventory
            $new_stock = $current_stock - $required;
            $this->db->where('InventoryItemID', $material->InventoryItemID);
            $this->db->update('inventory_items', ['InStock' => $new_stock]);
            
            $deductions[] = [
                'ItemID' => $material->ItemID,
                'ItemName' => $material->ItemName,
                'Deducted' => $required,
                'Remaining' => $new_stock
            ];
            
            // Update status based on new stock level
            if ($new_stock == 0) {
                $this->db->where('InventoryItemID', $material->InventoryItemID);
                $this->db->update('inventory_items', ['Status' => 'Out of Stock']);
                
                // Create notification for sales
                $this->create_out_of_stock_notification($material->InventoryItemID, $material->ItemID, $material->ItemName);
            } elseif ($new_stock > 0 && $new_stock < 10) {
                $this->db->where('InventoryItemID', $material->InventoryItemID);
                $this->db->update('inventory_items', ['Status' => 'Low Stock']);
            }
        }
        
        $this->db->trans_complete();
        
        return [
            'success' => empty($out_of_stock_items),
            'deductions' => $deductions,
            'out_of_stock_items' => $out_of_stock_items
        ];
    }
    
    /**
     * Create notification for out of stock items
     */
    private function create_out_of_stock_notification($inventory_item_id, $item_id, $item_name)
    {
        // Check if notification already exists (unread)
        $this->db->where('InventoryItemID', $inventory_item_id);
        $this->db->where('Status', 'Unread');
        $existing = $this->db->get('inventory_notifications')->row();
        
        $message = "Item {$item_id} ({$item_name}) is now out of stock. Please restock immediately.";
        
        if (!$existing) {
            // Insert into inventory_notifications (keep for backward compatibility)
            $this->db->insert('inventory_notifications', [
                'InventoryItemID' => $inventory_item_id,
                'ItemID' => $item_id,
                'ItemName' => $item_name,
                'Message' => $message,
                'Status' => 'Unread'
            ]);
            
            // Also insert into sales_notif table
            $this->db->insert('sales_notif', [
                'Icon' => 'fa-box-open',
                'Role' => 'System',
                'Description' => 'Inventory Alert: ' . $message,
                'Status' => 'Unread',
                'RelatedID' => $inventory_item_id,
                'RelatedType' => 'Inventory',
                'Created_Date' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Get unread inventory notifications
     */
    public function get_unread_notifications()
    {
        $this->db->where('Status', 'Unread');
        $this->db->order_by('Created_Date', 'DESC');
        return $this->db->get('inventory_notifications')->result();
    }
    
    /**
     * Mark notification as read
     */
    public function mark_notification_read($notification_id)
    {
        $this->db->where('NotificationID', $notification_id);
        $this->db->update('inventory_notifications', ['Status' => 'Read']);
    }
    
    // =====================================================
    // NEW METHODS FROM CODES FOLDER INTEGRATION
    // =====================================================
    
    /**
     * Add new inventory item
     */
    public function add_item($data)
    {
        $this->db->trans_start();
        
        // Generate ItemID if not provided
        if (empty($data['ItemID'])) {
            // Get last item number
            $this->db->select('ItemID');
            $this->db->from('inventory_items');
            $this->db->order_by('InventoryItemID', 'DESC');
            $this->db->limit(1);
            $last_item = $this->db->get()->row();
            
            if ($last_item) {
                // Extract number from ItemID (e.g., GL-001 -> 001)
                preg_match('/(\d+)$/', $last_item->ItemID, $matches);
                $next_num = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
                
                // Determine prefix based on category
                $prefix = $this->get_category_prefix($data['Category']);
                $data['ItemID'] = $prefix . '-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
            } else {
                $prefix = $this->get_category_prefix($data['Category']);
                $data['ItemID'] = $prefix . '-001';
            }
        }
        
        // Set default values
        $insert_data = [
            'ItemID' => $data['ItemID'],
            'Name' => $data['Name'],
            'Category' => $data['Category'],
            'InStock' => isset($data['InStock']) ? intval($data['InStock']) : 0,
            'Unit' => $data['Unit'],
            'min_threshold' => isset($data['min_threshold']) ? intval($data['min_threshold']) : 10,
            'Status' => isset($data['Status']) ? $data['Status'] : 'In Stock',
            'DateAdded' => date('Y-m-d H:i:s')
        ];
        
        // Determine status based on stock
        if ($insert_data['InStock'] == 0) {
            $insert_data['Status'] = 'Out of Stock';
        } elseif ($insert_data['InStock'] < $insert_data['min_threshold']) {
            $insert_data['Status'] = 'Low Stock';
        } elseif (isset($data['is_new_item']) && $data['is_new_item']) {
            $insert_data['Status'] = 'New';
        }
        
        $this->db->insert('inventory_items', $insert_data);
        $item_id = $this->db->insert_id();
        
        // Log activity
        $this->log_activity('Item created', $data['Name'], $insert_data['InStock'] . ' ' . strtolower($data['Unit']) . ' initial', 'System', $item_id);
        
        // Create stock transaction
        if ($insert_data['InStock'] > 0) {
            $this->create_stock_transaction($item_id, 'add', $insert_data['InStock'], 'Initial stock', 0, $insert_data['InStock']);
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Failed to add item'];
        }
        
        return ['success' => true, 'item_id' => $item_id, 'ItemID' => $insert_data['ItemID']];
    }
    
    /**
     * Update inventory item
     */
    public function update_item($item_id, $data)
    {
        $this->db->trans_start();
        
        // Get current item data
        $current_item = $this->get_item($item_id);
        if (!$current_item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $update_data = [];
        if (isset($data['Name'])) $update_data['Name'] = $data['Name'];
        if (isset($data['Category'])) $update_data['Category'] = $data['Category'];
        if (isset($data['Unit'])) $update_data['Unit'] = $data['Unit'];
        if (isset($data['min_threshold'])) $update_data['min_threshold'] = intval($data['min_threshold']);
        if (isset($data['InStock'])) {
            $update_data['InStock'] = intval($data['InStock']);
            // Update status based on new stock
            if ($update_data['InStock'] == 0) {
                $update_data['Status'] = 'Out of Stock';
            } elseif ($update_data['InStock'] < $update_data['min_threshold']) {
                $update_data['Status'] = 'Low Stock';
            } else {
                $update_data['Status'] = 'In Stock';
            }
        }
        if (isset($data['Status'])) $update_data['Status'] = $data['Status'];
        
        $this->db->where('InventoryItemID', $item_id);
        $this->db->update('inventory_items', $update_data);
        
        // Log activity
        $changes = [];
        foreach ($update_data as $key => $value) {
            if ($key != 'Status' && isset($current_item->$key) && $current_item->$key != $value) {
                $changes[] = ucfirst($key) . ': ' . $current_item->$key . ' → ' . $value;
            }
        }
        if (!empty($changes)) {
            $this->log_activity('Item updated', $current_item->Name, implode(', ', $changes), 'System', $item_id);
        }
        
        $this->db->trans_complete();
        
        return ['success' => $this->db->trans_status() !== FALSE];
    }
    
    /**
     * Delete inventory item
     */
    public function delete_item($item_id)
    {
        $item = $this->get_item($item_id);
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $this->db->trans_start();
        
        // Log activity before deletion
        $this->log_activity('Item deleted', $item->Name, 'Item removed from inventory', 'System', $item_id);
        
        // Delete item (cascade will handle related records)
        $this->db->where('InventoryItemID', $item_id);
        $this->db->delete('inventory_items');
        
        $this->db->trans_complete();
        
        return ['success' => $this->db->trans_status() !== FALSE];
    }
    
    /**
     * Manage stock (add or remove)
     */
    public function manage_stock($item_id, $add_quantity = 0, $remove_quantity = 0, $reason = '', $user_id = null)
    {
        $item = $this->get_item($item_id);
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $this->db->trans_start();
        
        $previous_stock = $item->InStock;
        $new_stock = $previous_stock + $add_quantity - $remove_quantity;
        if ($new_stock < 0) $new_stock = 0;
        
        // Update stock
        $update_data = ['InStock' => $new_stock];
        
        // Update status
        $min_threshold = isset($item->min_threshold) ? $item->min_threshold : 10;
        if ($new_stock == 0) {
            $update_data['Status'] = 'Out of Stock';
        } elseif ($new_stock < $min_threshold) {
            $update_data['Status'] = 'Low Stock';
        } else {
            $update_data['Status'] = 'In Stock';
        }
        
        $this->db->where('InventoryItemID', $item_id);
        $this->db->update('inventory_items', $update_data);
        
        // Log activity
        $change_desc = '';
        if ($add_quantity > 0) $change_desc .= '+' . $add_quantity . ' ' . strtolower($item->Unit);
        if ($remove_quantity > 0) {
            if ($change_desc) $change_desc .= ', ';
            $change_desc .= '-' . $remove_quantity . ' ' . strtolower($item->Unit);
        }
        
        $action = $add_quantity > 0 ? 'Stock added' : ($remove_quantity > 0 ? 'Stock reduced' : 'Stock adjusted');
        $this->log_activity($action, $item->Name, $change_desc, $reason ?: 'Stock management', $item_id, $user_id);
        
        // Create stock transaction
        $transaction_type = $add_quantity > 0 ? 'add' : ($remove_quantity > 0 ? 'remove' : 'adjust');
        $quantity = $add_quantity > 0 ? $add_quantity : $remove_quantity;
        $this->create_stock_transaction($item_id, $transaction_type, $quantity, $reason, $previous_stock, $new_stock, $user_id);
        
        $this->db->trans_complete();
        
        return [
            'success' => $this->db->trans_status() !== FALSE,
            'previous_stock' => $previous_stock,
            'new_stock' => $new_stock
        ];
    }
    
    /**
     * Get statistics
     */
    public function get_statistics()
    {
        // Total items
        $total_items = $this->db->count_all_results('inventory_items');
        
        // Low stock alerts (stock < min_threshold and > 0)
        $this->db->where('InStock >', 0);
        $this->db->where('InStock <', 'min_threshold', FALSE);
        $low_stock_count = $this->db->count_all_results('inventory_items');
        
        // New items (Status = 'New' or added within 2 days)
        $this->db->where('Status', 'New');
        $this->db->or_where('DateAdded >=', date('Y-m-d H:i:s', strtotime('-2 days')));
        $new_items_count = $this->db->count_all_results('inventory_items');
        
        // Out of stock
        $this->db->where('InStock', 0);
        $out_of_stock_count = $this->db->count_all_results('inventory_items');
        
        // Recent requests (from activities in last 7 days)
        $this->db->where('timestamp >=', date('Y-m-d H:i:s', strtotime('-7 days')));
        $recent_requests = $this->db->count_all_results('activities');
        
        return [
            'totalItems' => $total_items,
            'lowStockAlerts' => $low_stock_count,
            'newItems' => $new_items_count,
            'outOfStock' => $out_of_stock_count,
            'recentRequests' => $recent_requests
        ];
    }
    
    /**
     * Get activities log
     */
    public function get_activities($limit = 50)
    {
        $this->db->select('a.*, DATE_FORMAT(a.timestamp, "%m/%d/%Y – %h:%i %p") as formatted_timestamp');
        $this->db->from('activities a');
        $this->db->order_by('a.timestamp', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
    
    /**
     * Log activity
     */
    public function log_activity($action, $item_name = null, $change_description = null, $description = null, $item_id = null, $user_id = null)
    {
        $data = [
            'action' => $action,
            'item_name' => $item_name,
            'change_description' => $change_description,
            'description' => $description,
            'InventoryItemID' => $item_id,
            'user_id' => $user_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('activities', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Create stock transaction record
     */
    public function create_stock_transaction($item_id, $transaction_type, $quantity, $reason = null, $previous_stock = null, $new_stock = null, $user_id = null)
    {
        $data = [
            'InventoryItemID' => $item_id,
            'transaction_type' => $transaction_type,
            'quantity' => $quantity,
            'reason' => $reason,
            'previous_stock' => $previous_stock,
            'new_stock' => $new_stock,
            'user_id' => $user_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('stock_transactions', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Get category prefix for ItemID generation
     */
    private function get_category_prefix($category)
    {
        $prefixes = [
            'Glass' => 'GL',
            'Aluminum' => 'AL',
            'Hardware' => 'HD',
            'Accessories' => 'AC',
            'Building Materials' => 'BM',
            'Finishing' => 'FN',
            'Lumber' => 'LB'
        ];
        
        return isset($prefixes[$category]) ? $prefixes[$category] : 'ITM';
    }
}

