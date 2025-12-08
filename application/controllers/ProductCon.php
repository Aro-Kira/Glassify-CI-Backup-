<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

defined('BASEPATH') OR exit('No direct script access allowed');

class ProductCon extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Inventory_model');
        $this->load->helper(array('url', 'form'));
    }


    // ---------------- ADD PRODUCT ----------------
    public function add_product()
{
    $this->load->library('upload');

    // Create folder if it doesn't exist
    $upload_path = './uploads/products/';
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }

    $config['upload_path']   = $upload_path;
    $config['allowed_types'] = 'jpg|jpeg|png|gif';
    $config['encrypt_name']  = TRUE;
    $this->upload->initialize($config);

    $image = null;
    if (!empty($_FILES['productImage']['name'])) {
        if ($this->upload->do_upload('productImage')) {
            $image = $this->upload->data('file_name');
        } else {
            echo json_encode(['status' => 'error', 'msg' => $this->upload->display_errors()]);
            return;
        }
    }

    $data = [
        'ProductName' => $this->input->post('name', true),
        'Category'    => $this->input->post('category', true),
        'Material'    => $this->input->post('material', true),
        'Price'       => $this->input->post('price', true),
        'ImageUrl'    => $image,
        'DateAdded'   => date('Y-m-d H:i:s'),
        'Status'      => 'active' // default value
    ];

    if ($this->Product_model->insert_product($data)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}




// ---------------- UPDATE PRODUCT ----------------
public function update_product($id)
{
    $this->load->library('upload');
    $this->load->library('session');
    
    // Get user role from session or POST
    $user_role = $this->input->post('user_role') ?: $this->session->userdata('user_role');
    
    $data = [];
    
    // Role-based field updates
    if ($user_role === 'Admin') {
        // Admin can edit: image, name, category, price (NOT materials)
        $config['upload_path'] = './uploads/products/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['encrypt_name'] = TRUE;
        $this->upload->initialize($config);
        
        // Handle image upload if provided
        if (!empty($_FILES['productImage']['name'])) {
            if ($this->upload->do_upload('productImage')) {
                $data['ImageUrl'] = $this->upload->data('file_name');
            } else {
                echo json_encode(['status' => 'error', 'msg' => $this->upload->display_errors()]);
                return;
            }
        }
        
        // Admin can update name, category, price
        if ($this->input->post('name')) {
            $data['ProductName'] = $this->input->post('name', true);
        }
        if ($this->input->post('category')) {
            $data['Category'] = $this->input->post('category', true);
        }
        if ($this->input->post('price')) {
            $data['Price'] = $this->input->post('price', true);
        }
        // Admin CANNOT update Material - do not include it
        
    } elseif ($user_role === 'Inventory Officer') {
        // Inventory Officer can ONLY edit materials (not image, name, category, price)
        // Handle multiple materials from JSON
        $materials_json = $this->input->post('materials');
        
        if ($materials_json) {
            $materials = json_decode($materials_json, true);
            
            if (!is_array($materials) || empty($materials)) {
                echo json_encode(['status' => 'error', 'message' => 'No materials provided']);
                return;
            }
            
            // Delete existing material relationships for this product
            $this->db->where('Product_ID', $id);
            $this->db->delete('product_materials');
            
            // Insert all new material relationships
            $material_enum = 'Glass'; // Default for backward compatibility
            foreach ($materials as $material) {
                $inventory_item_id = $material['InventoryItemID'];
                $quantity = isset($material['QuantityRequired']) ? $material['QuantityRequired'] : 1;
                
                // Get inventory item details
                $inventory_item = $this->Inventory_model->get_item($inventory_item_id);
                
                if ($inventory_item) {
                    $material_data = [
                        'Product_ID' => $id,
                        'InventoryItemID' => $inventory_item_id,
                        'QuantityRequired' => $quantity,
                        'Unit' => $material['Unit'] ?? $inventory_item->Unit,
                        'Created_Date' => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert('product_materials', $material_data);
                    
                    // Determine material enum for backward compatibility (use first material)
                    if ($material_enum === 'Glass' && 
                        (stripos($inventory_item->Category, 'Aluminum') !== false || 
                         stripos($inventory_item->Name, 'Aluminum') !== false)) {
                        $material_enum = 'Aluminum';
                    }
                }
            }
            
            // Update Material field in product table for backward compatibility
            $data['Material'] = $material_enum;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No materials provided']);
            return;
        }
        // Do not allow updates to image, name, category, or price
        
    } else {
        // Default behavior for other roles (backward compatibility)
        $config['upload_path'] = './uploads/products/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['encrypt_name'] = TRUE;
        $this->upload->initialize($config);
        
        $data = [
            'ProductName' => $this->input->post('name', true),
            'Price'       => $this->input->post('price', true),
            'Category'    => $this->input->post('category', true),
            'Material'    => $this->input->post('material', true)
        ];
        
        if (!empty($_FILES['productImage']['name'])) {
            if ($this->upload->do_upload('productImage')) {
                $data['ImageUrl'] = $this->upload->data('file_name');
            } else {
                echo json_encode(['status' => 'error', 'msg' => $this->upload->display_errors()]);
                return;
            }
        }
    }
    
    // Only update if there's data to update
    if (empty($data)) {
        echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        return;
    }

    if ($this->Product_model->update_product($id, $data)) {
        echo json_encode(['status' => 'updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update product']);
    }
}


    // ---------------- DELETE PRODUCT ----------------
    public function delete_product($id)
    {
        if ($this->Product_model->delete_product($id)) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    
    // ---------------- GET PRODUCT MATERIALS ----------------
    public function get_product_materials($product_id)
    {
        $materials = $this->Inventory_model->get_product_materials($product_id);
        
        $formatted_materials = [];
        foreach ($materials as $material) {
            $formatted_materials[] = [
                'InventoryItemID' => $material->InventoryItemID,
                'ItemID' => $material->ItemID,
                'ItemName' => $material->ItemName,
                'QuantityRequired' => $material->QuantityRequired,
                'Unit' => $material->Unit
            ];
        }
        
        echo json_encode([
            'success' => true,
            'materials' => $formatted_materials
        ]);
    }
}
