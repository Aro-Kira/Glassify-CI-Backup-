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

    $uploaded_images = [];
    
    // Handle multiple image uploads
    if (!empty($_FILES['productImages']['name'][0])) {
        $files = $_FILES['productImages'];
        $file_count = count($files['name']);
        
        // Validate minimum 3 images
        if ($file_count < 3) {
            echo json_encode(['status' => 'error', 'msg' => 'Please upload at least 3 images.']);
            return;
        }
        
        for ($i = 0; $i < $file_count; $i++) {
            $_FILES['productImage']['name'] = $files['name'][$i];
            $_FILES['productImage']['type'] = $files['type'][$i];
            $_FILES['productImage']['tmp_name'] = $files['tmp_name'][$i];
            $_FILES['productImage']['error'] = $files['error'][$i];
            $_FILES['productImage']['size'] = $files['size'][$i];
            
            if ($this->upload->do_upload('productImage')) {
                $uploaded_images[] = $this->upload->data('file_name');
            } else {
                echo json_encode(['status' => 'error', 'msg' => $this->upload->display_errors()]);
                return;
            }
        }
    } else {
        // Fallback: check for single image upload (backward compatibility)
        if (!empty($_FILES['productImage']['name'])) {
            if ($this->upload->do_upload('productImage')) {
                $uploaded_images[] = $this->upload->data('file_name');
            } else {
                echo json_encode(['status' => 'error', 'msg' => $this->upload->display_errors()]);
                return;
            }
        }
    }

    // Validate minimum 3 images
    if (count($uploaded_images) < 3) {
        echo json_encode(['status' => 'error', 'msg' => 'Please upload at least 3 images.']);
        return;
    }

    // Store images as JSON array
    $image_json = json_encode($uploaded_images);

    $data = [
        'ProductName' => $this->input->post('name', true),
        'Category'    => $this->input->post('category', true),
        'Material'    => $this->input->post('material', true),
        'Price'       => $this->input->post('price', true),
        'ImageUrl'    => $image_json,
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
        
        // Handle multiple image uploads if provided
        if (!empty($_FILES['productImages']['name'][0])) {
            $files = $_FILES['productImages'];
            $file_count = count($files['name']);
            $uploaded_images = [];
            
            // Get existing images if any (for edit)
            $existing_product = $this->Product_model->get_product($id);
            $existing_images = [];
            if ($existing_product && !empty($existing_product->ImageUrl)) {
                $decoded = json_decode($existing_product->ImageUrl, true);
                if (is_array($decoded)) {
                    $existing_images = $decoded;
                } else {
                    // Single image (backward compatibility)
                    $existing_images = [$existing_product->ImageUrl];
                }
            }
            
            for ($i = 0; $i < $file_count; $i++) {
                $_FILES['productImage']['name'] = $files['name'][$i];
                $_FILES['productImage']['type'] = $files['type'][$i];
                $_FILES['productImage']['tmp_name'] = $files['tmp_name'][$i];
                $_FILES['productImage']['error'] = $files['error'][$i];
                $_FILES['productImage']['size'] = $files['size'][$i];
                
                if ($this->upload->do_upload('productImage')) {
                    $uploaded_images[] = $this->upload->data('file_name');
                } else {
                    echo json_encode(['status' => 'error', 'msg' => $this->upload->display_errors()]);
                    return;
                }
            }
            
            // If new images uploaded, use them; otherwise keep existing
            if (count($uploaded_images) > 0) {
                // Validate minimum 3 images if new uploads exist
                if (count($uploaded_images) < 3) {
                    echo json_encode(['status' => 'error', 'msg' => 'Please upload at least 3 images.']);
                    return;
                }
                $data['ImageUrl'] = json_encode($uploaded_images);
            }
        } else if (!empty($_FILES['productImage']['name'])) {
            // Fallback: single image upload (backward compatibility)
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
        // Admin can now edit materials (functionality transferred from Inventory Officer)
        // Handle multiple materials from JSON for Admin (optional - can update other fields without materials)
        $materials_json = $this->input->post('materials');
        
        if ($materials_json) {
            $materials = json_decode($materials_json, true);
            
            if (is_array($materials) && !empty($materials)) {
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
            }
        }
        // Admin can update materials, image, name, category, and price
        
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
