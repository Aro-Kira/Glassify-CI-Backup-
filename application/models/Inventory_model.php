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
}

