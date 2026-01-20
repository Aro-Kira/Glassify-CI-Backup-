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
        
        // Validate image count (min 1, max 10)
        if ($file_count < 1) {
            echo json_encode(['status' => 'error', 'msg' => 'Please upload at least 1 image.']);
            return;
        }
        if ($file_count > 10) {
            echo json_encode(['status' => 'error', 'msg' => 'Maximum 10 images allowed per product.']);
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

    // Validate image count (min 1, max 10)
    if (count($uploaded_images) < 1) {
        echo json_encode(['status' => 'error', 'msg' => 'Please upload at least 1 image.']);
        return;
    }
    if (count($uploaded_images) > 10) {
        echo json_encode(['status' => 'error', 'msg' => 'Maximum 10 images allowed per product.']);
        return;
    }

    // Store images as JSON array
    $image_json = json_encode($uploaded_images);

    // Get additional data
    $customization = $this->input->post('customization');
    $tagPrices = $this->input->post('tagPrices');
    $tagVisualConfigs = $this->input->post('tagVisualConfigs'); // Konva.js visual configs
    $priceMin = $this->input->post('priceMin');
    $priceMax = $this->input->post('priceMax');
    $standardSeries = $this->input->post('standardSeries');

    // Calculate average price for backward compatibility
    $avgPrice = 0;
    if ($priceMin && $priceMax) {
        $avgPrice = ($priceMin + $priceMax) / 2;
    } elseif ($priceMin) {
        $avgPrice = $priceMin;
    } elseif ($priceMax) {
        $avgPrice = $priceMax;
    }

    $orderType = $this->input->post('orderType', true);
    $subcategory = $this->input->post('subcategory', true);
    $productName = $this->input->post('name', true);
    $description = $this->input->post('description', true);
    
    // Check for duplicate product name
    if ($this->Product_model->product_name_exists($productName)) {
        echo json_encode(['status' => 'error', 'msg' => 'A product with this name already exists. Product names must be unique.']);
        return;
    }
    
    $data = [
        'ProductName' => $productName,
        'Description' => $description ? $description : null,
        'Category'    => $this->input->post('category', true),
        'Subcategory'  => $subcategory ? $subcategory : null, // Store subcategory
        'OrderType'   => $orderType ? $orderType : 'direct', // Store order type (direct or site-assessment)
        'Material'    => 'Glass', // Keep for backward compatibility, default to Glass
        'Price'       => $avgPrice, // Store average for backward compatibility
        'PriceMin'    => $priceMin ? $priceMin : null,
        'PriceMax'    => $priceMax ? $priceMax : null,
        'ImageUrl'    => $image_json,
        'DateAdded'   => date('Y-m-d H:i:s'),
        'Status'      => 'active' // default value
    ];

    // Add customization data if provided (from Customize Build tab)
    if ($customization) {
        $data['Customization'] = $customization;
    }

    // Insert product
    if ($this->Product_model->insert_product($data)) {
        $product_id = $this->db->insert_id();
        
        // Store tag prices if provided (from Customize Build tab)
        if ($tagPrices) {
            $tag_prices_data = json_decode($tagPrices, true);
            if (is_array($tag_prices_data)) {
                foreach ($tag_prices_data as $fieldId => $tags) {
                    foreach ($tags as $tagName => $price) {
                        // Check if there's an image for this tag
                        $imageUrl = null;
                        if (isset($_FILES["tagImages"]["name"][$fieldId][$tagName]) && 
                            !empty($_FILES["tagImages"]["name"][$fieldId][$tagName])) {
                            
                            // Create folder for tag images if it doesn't exist
                            $tag_upload_path = './uploads/tags/';
                            if (!is_dir($tag_upload_path)) {
                                mkdir($tag_upload_path, 0755, true);
                            }
                            
                            // Upload tag image
                            $tag_config['upload_path'] = $tag_upload_path;
                            $tag_config['allowed_types'] = 'jpg|jpeg|png|gif';
                            $tag_config['encrypt_name'] = TRUE;
                            $this->upload->initialize($tag_config);
                            
                            $_FILES['tagImage']['name'] = $_FILES["tagImages"]["name"][$fieldId][$tagName];
                            $_FILES['tagImage']['type'] = $_FILES["tagImages"]["type"][$fieldId][$tagName];
                            $_FILES['tagImage']['tmp_name'] = $_FILES["tagImages"]["tmp_name"][$fieldId][$tagName];
                            $_FILES['tagImage']['error'] = $_FILES["tagImages"]["error"][$fieldId][$tagName];
                            $_FILES['tagImage']['size'] = $_FILES["tagImages"]["size"][$fieldId][$tagName];
                            
                            if ($this->upload->do_upload('tagImage')) {
                                $imageUrl = $this->upload->data('file_name');
                            }
                        }
                        
                        // Get visual config for this tag if available
                        $visualConfig = null;
                        $visualConfigsData = $tagVisualConfigs ? json_decode($tagVisualConfigs, true) : [];
                        if (isset($visualConfigsData[$fieldId][$tagName])) {
                            $visualConfig = json_encode($visualConfigsData[$fieldId][$tagName]);
                        }
                        
                        $this->db->insert('product_tag_prices', [
                            'Product_ID' => $product_id,
                            'FieldID' => $fieldId,
                            'TagName' => $tagName,
                            'Price' => $price,
                            'ImageUrl' => $imageUrl,
                            'VisualConfig' => $visualConfig,
                            'Created_Date' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }
        
        // Store standard series if provided (from Standard tab)
        if ($standardSeries) {
            $series_data = json_decode($standardSeries, true);
            if (is_array($series_data)) {
                foreach ($series_data as $series) {
                    // Insert series
                    $this->db->insert('product_series', [
                        'Product_ID' => $product_id,
                        'SeriesName' => $series['name'],
                        'Created_Date' => date('Y-m-d H:i:s')
                    ]);
                    
                    $series_db_id = $this->db->insert_id();
                    
                    // Insert measurements for this series
                    if (isset($series['measurements']) && is_array($series['measurements'])) {
                        foreach ($series['measurements'] as $measurement) {
                            // Store original values and units (no conversion)
                            $width = $measurement['width'];
                            $height = $measurement['height'];
                            $widthUnit = isset($measurement['widthUnit']) ? $measurement['widthUnit'] : 'in';
                            $heightUnit = isset($measurement['heightUnit']) ? $measurement['heightUnit'] : 'in';
                            
                            // Store customization data as JSON
                            $customizationJson = null;
                            if (isset($measurement['customization']) && !empty($measurement['customization'])) {
                                $customizationJson = json_encode($measurement['customization']);
                            }
                            
                            $this->db->insert('product_standard_sizes', [
                                'Series_ID' => $series_db_id,
                                'Width' => $width,
                                'WidthUnit' => $widthUnit,
                                'Height' => $height,
                                'HeightUnit' => $heightUnit,
                                'Price' => $measurement['price'],
                                'OtherOptions' => $customizationJson,
                                'Created_Date' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }
            }
        }
        
        // Save field configurations to database if category/subcategory provided
        $category = $this->input->post('category', true);
        $subcategory = $this->input->post('subcategory', true);
        if ($category && $subcategory) {
            // Build field key
            $prefixMap = [
                'Windows' => 'Windows',
                'Doors' => 'Doors',
                'Glass Partitions & Enclosures' => 'Partitions',
                'Mirrors & Specialty Glass' => 'Specialty',
                'Commercial & Exterior' => 'Commercial'
            ];
            $prefix = $prefixMap[$category] ?? '';
            $fieldKey = $prefix ? "{$prefix}_{$subcategory}" : $subcategory;
            
            // Get fields from JavaScript (they should be saved via API, but we can also save here)
            // The fields are managed via the "Manage Customization Fields" modal and saved via API
            // So we don't need to save them here - they're already in the database
        }
        
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
        // Admin can edit: image, name, category, subcategory, order type, price range, customization, tag prices, standard series (NOT materials)
        $config['upload_path'] = './uploads/products/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['encrypt_name'] = TRUE;
        $this->upload->initialize($config);
        
        // Get existing product data
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
        
        // Handle multiple image uploads if provided
        if (!empty($_FILES['productImages']['name'][0])) {
            $files = $_FILES['productImages'];
            $file_count = count($files['name']);
            $uploaded_images = [];
            
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
                // Validate image count (min 1, max 10) if new uploads exist
                if (count($uploaded_images) < 1) {
                    echo json_encode(['status' => 'error', 'msg' => 'Please upload at least 1 image.']);
                    return;
                }
                if (count($uploaded_images) > 10) {
                    echo json_encode(['status' => 'error', 'msg' => 'Maximum 10 images allowed per product.']);
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
        } else {
            // No new images uploaded, keep existing images
            if (!empty($existing_images)) {
                $data['ImageUrl'] = json_encode($existing_images);
            }
        }
        
        // Get additional data
        $customization = $this->input->post('customization');
        $tagPrices = $this->input->post('tagPrices');
        $priceMin = $this->input->post('priceMin');
        $priceMax = $this->input->post('priceMax');
        $standardSeries = $this->input->post('standardSeries');
        $orderType = $this->input->post('orderType', true);
        $subcategory = $this->input->post('subcategory', true);
        
        // Calculate average price for backward compatibility
        $avgPrice = 0;
        if ($priceMin && $priceMax) {
            $avgPrice = ($priceMin + $priceMax) / 2;
        } elseif ($priceMin) {
            $avgPrice = $priceMin;
        } elseif ($priceMax) {
            $avgPrice = $priceMax;
        } elseif ($existing_product && $existing_product->Price) {
            $avgPrice = $existing_product->Price;
        }
        
        // Admin can update name, description, order type, price range (category and subcategory are read-only)
        if ($this->input->post('name')) {
            $newProductName = $this->input->post('name', true);
            // Check for duplicate product name (excluding current product)
            if ($this->Product_model->product_name_exists($newProductName, $id)) {
                echo json_encode(['status' => 'error', 'msg' => 'A product with this name already exists. Product names must be unique.']);
                return;
            }
            $data['ProductName'] = $newProductName;
        }
        if ($this->input->post('description') !== null) {
            $data['Description'] = $this->input->post('description', true) ?: null;
        }
        // Category and subcategory are read-only - don't update them
        // if ($this->input->post('category')) {
        //     $data['Category'] = $this->input->post('category', true);
        // }
        // if ($subcategory) {
        //     $data['Subcategory'] = $subcategory;
        // } else {
        //     $data['Subcategory'] = null;
        // }
        if ($orderType) {
            $data['OrderType'] = $orderType;
        }
        $data['Price'] = $avgPrice;
        if ($priceMin) {
            $data['PriceMin'] = $priceMin;
        } else {
            $data['PriceMin'] = null;
        }
        if ($priceMax) {
            $data['PriceMax'] = $priceMax;
        } else {
            $data['PriceMax'] = null;
        }
        
        // Add customization data if provided (from Customize Build tab)
        if ($customization) {
            $data['Customization'] = $customization;
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
        // Handle tag prices and standard series updates (only for Admin role)
        if ($user_role === 'Admin') {
            $tagPrices = $this->input->post('tagPrices');
            $tagVisualConfigs = $this->input->post('tagVisualConfigs'); // Konva.js visual configs
            $standardSeries = $this->input->post('standardSeries');
            
            // Update tag prices if provided (from Customize Build tab)
            if ($tagPrices) {
                // Get existing tag images and visual configs before deleting
                $existing_tag_images = [];
                $existing_visual_configs = [];
                $this->db->where('Product_ID', $id);
                $existing_tags = $this->db->get('product_tag_prices')->result();
                foreach ($existing_tags as $tag) {
                    $key = $tag->FieldID . '_' . $tag->TagName;
                    if (!empty($tag->ImageUrl)) {
                        $existing_tag_images[$key] = $tag->ImageUrl;
                    }
                    if (!empty($tag->VisualConfig)) {
                        $existing_visual_configs[$key] = $tag->VisualConfig;
                    }
                }
                
                // Delete existing tag prices for this product
                $this->db->where('Product_ID', $id);
                $this->db->delete('product_tag_prices');
                
                $tag_prices_data = json_decode($tagPrices, true);
                $visualConfigsData = $tagVisualConfigs ? json_decode($tagVisualConfigs, true) : [];
                
                // Debug: Log what we received
                log_message('debug', '[ProductCon] Received tagVisualConfigs: ' . $tagVisualConfigs);
                log_message('debug', '[ProductCon] Decoded visualConfigsData: ' . print_r($visualConfigsData, true));
                
                if (is_array($tag_prices_data)) {
                    foreach ($tag_prices_data as $fieldId => $tags) {
                        foreach ($tags as $tagName => $price) {
                            // Check if there's a new image for this tag
                            $imageUrl = null;
                            $tag_key = $fieldId . '_' . $tagName;
                            
                            // Check if new image uploaded
                            if (isset($_FILES["tagImages"]["name"][$fieldId][$tagName]) && 
                                !empty($_FILES["tagImages"]["name"][$fieldId][$tagName])) {
                                
                                // Create folder for tag images if it doesn't exist
                                $tag_upload_path = './uploads/tags/';
                                if (!is_dir($tag_upload_path)) {
                                    mkdir($tag_upload_path, 0755, true);
                                }
                                
                                // Upload tag image
                                $tag_config['upload_path'] = $tag_upload_path;
                                $tag_config['allowed_types'] = 'jpg|jpeg|png|gif';
                                $tag_config['encrypt_name'] = TRUE;
                                $this->upload->initialize($tag_config);
                                
                                $_FILES['tagImage']['name'] = $_FILES["tagImages"]["name"][$fieldId][$tagName];
                                $_FILES['tagImage']['type'] = $_FILES["tagImages"]["type"][$fieldId][$tagName];
                                $_FILES['tagImage']['tmp_name'] = $_FILES["tagImages"]["tmp_name"][$fieldId][$tagName];
                                $_FILES['tagImage']['error'] = $_FILES["tagImages"]["error"][$fieldId][$tagName];
                                $_FILES['tagImage']['size'] = $_FILES["tagImages"]["size"][$fieldId][$tagName];
                                
                                if ($this->upload->do_upload('tagImage')) {
                                    $imageUrl = $this->upload->data('file_name');
                                }
                            } elseif (isset($existing_tag_images[$tag_key])) {
                                // Keep existing image if no new one uploaded
                                $imageUrl = $existing_tag_images[$tag_key];
                            }
                            
                            // Get visual config for this tag
                            $visualConfig = null;
                            if (isset($visualConfigsData[$fieldId][$tagName])) {
                                $visualConfig = json_encode($visualConfigsData[$fieldId][$tagName]);
                            } elseif (isset($existing_visual_configs[$tag_key])) {
                                // Keep existing visual config if no new one provided
                                $visualConfig = $existing_visual_configs[$tag_key];
                            }
                            
                            $this->db->insert('product_tag_prices', [
                                'Product_ID' => $id,
                                'FieldID' => $fieldId,
                                'TagName' => $tagName,
                                'Price' => $price,
                                'ImageUrl' => $imageUrl,
                                'VisualConfig' => $visualConfig,
                                'Created_Date' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }
            }
            
            // Update standard series if provided (from Standard tab)
            if ($standardSeries) {
                // Delete existing series and measurements for this product
                $this->db->select('Series_ID');
                $this->db->where('Product_ID', $id);
                $existing_series = $this->db->get('product_series')->result();
                $series_ids = array_column($existing_series, 'Series_ID');
                
                if (!empty($series_ids)) {
                    $this->db->where_in('Series_ID', $series_ids);
                    $this->db->delete('product_standard_sizes');
                }
                
                $this->db->where('Product_ID', $id);
                $this->db->delete('product_series');
                
                // Insert new series
                $series_data = json_decode($standardSeries, true);
                if (is_array($series_data)) {
                    foreach ($series_data as $series) {
                        // Insert series
                        $this->db->insert('product_series', [
                            'Product_ID' => $id,
                            'SeriesName' => $series['name'],
                            'Created_Date' => date('Y-m-d H:i:s')
                        ]);
                        
                        $series_db_id = $this->db->insert_id();
                        
                        // Insert measurements for this series
                        if (isset($series['measurements']) && is_array($series['measurements'])) {
                            foreach ($series['measurements'] as $measurement) {
                                // Store original values and units (no conversion)
                                $width = $measurement['width'];
                                $height = $measurement['height'];
                                $widthUnit = isset($measurement['widthUnit']) ? $measurement['widthUnit'] : 'in';
                                $heightUnit = isset($measurement['heightUnit']) ? $measurement['heightUnit'] : 'in';
                                
                                // Store customization data as JSON
                                $customizationJson = null;
                                if (isset($measurement['customization']) && !empty($measurement['customization'])) {
                                    $customizationJson = json_encode($measurement['customization']);
                                }
                                
                                $this->db->insert('product_standard_sizes', [
                                    'Series_ID' => $series_db_id,
                                    'Width' => $width,
                                    'WidthUnit' => $widthUnit,
                                    'Height' => $height,
                                    'HeightUnit' => $heightUnit,
                                    'Price' => $measurement['price'],
                                    'OtherOptions' => $customizationJson,
                                    'Created_Date' => date('Y-m-d H:i:s')
                                ]);
                            }
                        }
                    }
                }
            }
        }
        
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
    
    // ---------------- GET PRODUCT (FULL DATA) ----------------
    public function get_product($id)
    {
        $product = $this->Product_model->get_product($id);
        
        if (!$product) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            return;
        }
        
        // Get tag prices, images, and visual configs
        $tag_prices = [];
        $tag_images = [];
        $tag_visual_configs = [];
        $this->db->where('Product_ID', $id);
        $tag_prices_result = $this->db->get('product_tag_prices')->result();
        foreach ($tag_prices_result as $tag) {
            if (!isset($tag_prices[$tag->FieldID])) {
                $tag_prices[$tag->FieldID] = [];
            }
            $tag_prices[$tag->FieldID][$tag->TagName] = $tag->Price;
            
            // Get tag images
            if (!empty($tag->ImageUrl)) {
                if (!isset($tag_images[$tag->FieldID])) {
                    $tag_images[$tag->FieldID] = [];
                }
                $tag_images[$tag->FieldID][$tag->TagName] = base_url('uploads/tags/' . $tag->ImageUrl);
            }
            
            // Get tag visual configs for Konva.js
            if (isset($tag->VisualConfig) && !empty($tag->VisualConfig)) {
                if (!isset($tag_visual_configs[$tag->FieldID])) {
                    $tag_visual_configs[$tag->FieldID] = [];
                }
                $decoded = json_decode($tag->VisualConfig, true);
                if ($decoded) {
                    $tag_visual_configs[$tag->FieldID][$tag->TagName] = $decoded;
                }
            }
        }
        
        // Get standard series
        $standard_series = [];
        $this->db->where('Product_ID', $id);
        $series_result = $this->db->get('product_series')->result();
        foreach ($series_result as $series) {
            $series_data = [
                'id' => $series->Series_ID,
                'name' => $series->SeriesName,
                'measurements' => []
            ];
            
            // Get measurements for this series
            $this->db->where('Series_ID', $series->Series_ID);
            $measurements = $this->db->get('product_standard_sizes')->result();
            foreach ($measurements as $measurement) {
                // Parse customization data from JSON
                $customization = null;
                if (isset($measurement->OtherOptions) && !empty($measurement->OtherOptions)) {
                    $decoded = json_decode($measurement->OtherOptions, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $customization = $decoded;
                    }
                }
                
                $series_data['measurements'][] = [
                    'id' => isset($measurement->SizeID) ? $measurement->SizeID : null,
                    'width' => $measurement->Width,
                    'height' => $measurement->Height,
                    'widthUnit' => isset($measurement->WidthUnit) ? $measurement->WidthUnit : 'in',
                    'heightUnit' => isset($measurement->HeightUnit) ? $measurement->HeightUnit : 'in',
                    'price' => $measurement->Price,
                    'customization' => $customization
                ];
            }
            
            $standard_series[] = $series_data;
        }
        
        // Parse images
        $images = [];
        if (!empty($product->ImageUrl)) {
            $decoded = json_decode($product->ImageUrl, true);
            if (is_array($decoded)) {
                $images = $decoded;
            } else {
                // Single image (backward compatibility)
                $images = [$product->ImageUrl];
            }
        }
        
        // Parse customization data
        $customization = null;
        if (!empty($product->Customization)) {
            $customization = json_decode($product->Customization, true);
        }
        
        // Ensure empty arrays are returned as objects {} not []
        // This is critical for JavaScript to handle them as objects with string keys
        echo json_encode([
            'status' => 'success',
            'product' => [
                'Product_ID' => $product->Product_ID,
                'ProductName' => $product->ProductName,
                'Category' => $product->Category,
                'Subcategory' => $product->Subcategory,
                'OrderType' => $product->OrderType ?? 'direct',
                'Material' => $product->Material,
                'Price' => $product->Price,
                'PriceMin' => $product->PriceMin,
                'PriceMax' => $product->PriceMax,
                'ImageUrl' => $images,
                'Customization' => $customization,
                'tagPrices' => empty($tag_prices) ? new stdClass() : $tag_prices,
                'tagImages' => empty($tag_images) ? new stdClass() : $tag_images,
                'tagVisualConfigs' => empty($tag_visual_configs) ? new stdClass() : $tag_visual_configs,
                'standardSeries' => $standard_series
            ]
        ]);
    }
    
    /**
     * Check if a product name already exists
     * Used for frontend validation before form submission
     */
    public function check_product_name() {
        $name = $this->input->get('name', true);
        $excludeId = $this->input->get('excludeId', true); // For updates, exclude current product
        
        if (empty($name)) {
            echo json_encode(['exists' => false]);
            return;
        }
        
        $exists = $this->Product_model->product_name_exists($name, $excludeId ? (int)$excludeId : null);
        echo json_encode(['exists' => $exists]);
    }
    
    /**
     * Convert measurement value to centimeters
     * @param float $value The measurement value
     * @param string $unit The unit (in, cm, mm)
     * @return float Value in centimeters
     */
    private function convertToCm($value, $unit = 'cm') {
        switch (strtolower($unit)) {
            case 'in':
            case 'inch':
            case 'inches':
                return $value * 2.54; // 1 inch = 2.54 cm
            case 'mm':
            case 'millimeter':
            case 'millimeters':
                return $value / 10; // 1 cm = 10 mm
            case 'cm':
            case 'centimeter':
            case 'centimeters':
            default:
                return $value; // Already in cm
        }
    }
}
