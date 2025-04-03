<?php
/**
 * Admin Product Controller - Edit
 * 
 * Handles editing existing products with support for multiple images and category relationships
 */

// Require admin login if the function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Initialize global variables for the view
global $categories, $edit_product, $product_images, $product_categories, $success_message, $error_message;

$success_message = '';
$error_message = '';

// Get product ID from the URL parameters
$params = get_parameters();
$product_id = isset($params['id']) ? (int)$params['id'] : 0;

if ($product_id <= 0) {
    $_SESSION['error_message'] = 'Invalid product ID';
    header('Location: /index.php/admin/products');
    exit;
}

// Add detailed logging for debugging
error_log('Product Edit Controller loaded for product ID: ' . $product_id);
error_log('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);

// Load product data
try {
    $pdo = getConnection();
    
    // Get the product
    $stmt = $pdo->prepare("
        SELECT * FROM products WHERE id = ? AND is_deleted = 0
    ");
    $stmt->execute([$product_id]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$edit_product) {
        $_SESSION['error_message'] = 'Product not found';
        header('Location: /index.php/admin/products');
        exit;
    }
    
    // Get product images
    $stmt = $pdo->prepare("
        SELECT * FROM product_images 
        WHERE product_id = ? 
        ORDER BY is_primary DESC, display_order ASC
    ");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product category relationships
    $stmt = $pdo->prepare("
        SELECT category_id FROM product_category_relationships 
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    $product_categories = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'category_id');
    
    // Load all categories for the form
    // Get all active categories
    $stmt = $pdo->prepare("
        SELECT id, name, parent_id 
        FROM product_categories 
        WHERE status = 'active' AND is_deleted = 0
        ORDER BY parent_id ASC, name ASC
    ");
    $stmt->execute();
    $all_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create a hierarchical array for the dropdown
    $categories = [];
    $parent_categories = [];
    
    // First, find all parent categories
    foreach ($all_categories as $category) {
        if (empty($category['parent_id'])) {
            $parent_categories[$category['id']] = $category;
            $parent_categories[$category['id']]['children'] = [];
        }
    }
    
    // Then, assign child categories to their parents
    foreach ($all_categories as $category) {
        if (!empty($category['parent_id']) && isset($parent_categories[$category['parent_id']])) {
            $parent_categories[$category['parent_id']]['children'][] = $category;
        }
    }
    
    // Set the categories array for the view
    $categories = $parent_categories;
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error loading product data';
    error_log('Error loading product data: ' . $e->getMessage());
    header('Location: /index.php/admin/products');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log for debugging
    error_log('Processing product edit form submission');
    error_log('POST data: ' . print_r($_POST, true));
    
    // Extract form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $sku = isset($_POST['sku']) ? trim($_POST['sku']) : '';
    $short_description = isset($_POST['short_description']) ? trim($_POST['short_description']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $regular_price = isset($_POST['regular_price']) ? (float)$_POST['regular_price'] : null;
    $sale_price = isset($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $stock_quantity = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 0;
    $weight = isset($_POST['weight']) ? (float)$_POST['weight'] : null;
    $dimensions_length = isset($_POST['dimensions_length']) ? (float)$_POST['dimensions_length'] : null;
    $dimensions_width = isset($_POST['dimensions_width']) ? (float)$_POST['dimensions_width'] : null;
    $dimensions_height = isset($_POST['dimensions_height']) ? (float)$_POST['dimensions_height'] : null;
    $is_featured = isset($_POST['is_featured']) ? (int)($_POST['is_featured'] == 'on' || $_POST['is_featured'] == '1') : 0;
    $is_new = isset($_POST['is_new']) ? (int)($_POST['is_new'] == 'on' || $_POST['is_new'] == '1') : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 'draft';
    $selected_categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $deleted_images = isset($_POST['delete_image']) ? $_POST['delete_image'] : [];
    $primary_image_id = isset($_POST['primary_image_id']) ? (int)$_POST['primary_image_id'] : 0;
    
    // Log form data for debugging
    error_log('Form data: Name=' . $name . ', SKU=' . $sku . ', Price=' . $price);
    error_log('Selected categories: ' . print_r($selected_categories, true));
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Product name is required';
    }
    
    if (empty($sku)) {
        $errors[] = 'SKU is required';
    }
    
    if ($price <= 0) {
        $errors[] = 'Price must be greater than zero';
    }
    
    if (empty($errors)) {
        try {
            error_log('Starting database update');
            $pdo = getConnection();
            
            // Begin transaction
            $pdo->beginTransaction();
            
            // Check if SKU already exists for other products
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM products 
                WHERE sku = ? AND id != ? AND is_deleted = 0
            ");
            $stmt->execute([$sku, $product_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $error_message = 'This SKU already exists. Please use a unique SKU.';
                error_log('Duplicate SKU: ' . $sku);
                
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            } else {
                // Generate slug if name has changed
                $slug = $edit_product['slug'];
                if ($name != $edit_product['name']) {
                    $slug = generate_slug($name);
                    $original_slug = $slug;
                    
                    // Check if slug exists and add suffix if necessary
                    $counter = 1;
                    while (true) {
                        $stmt = $pdo->prepare("
                            SELECT COUNT(*) as count 
                            FROM products 
                            WHERE slug = ? AND id != ? AND is_deleted = 0
                        ");
                        $stmt->execute([$slug, $product_id]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($result['count'] == 0) {
                            break;
                        }
                        
                        $slug = $original_slug . '-' . $counter;
                        $counter++;
                    }
                }
                
                // Update product
                $stmt = $pdo->prepare("
                    UPDATE products SET
                        sku = ?, 
                        name = ?, 
                        slug = ?, 
                        description = ?, 
                        short_description = ?, 
                        price = ?, 
                        regular_price = ?, 
                        sale_price = ?, 
                        stock_quantity = ?, 
                        weight = ?, 
                        dimensions_length = ?, 
                        dimensions_width = ?, 
                        dimensions_height = ?, 
                        is_featured = ?, 
                        is_new = ?, 
                        status = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                
                $params = [
                    $sku,
                    $name,
                    $slug,
                    $description,
                    $short_description,
                    $price,
                    $regular_price,
                    $sale_price,
                    $stock_quantity,
                    $weight,
                    $dimensions_length,
                    $dimensions_width,
                    $dimensions_height,
                    $is_featured,
                    $is_new,
                    $status,
                    $product_id
                ];
                
                error_log('UPDATE parameters: ' . print_r($params, true));
                $stmt->execute($params);
                
                // Update category relationships
                // First, delete existing relationships
                $stmt = $pdo->prepare("
                    DELETE FROM product_category_relationships WHERE product_id = ?
                ");
                $stmt->execute([$product_id]);
                
                // Then, insert new relationships
                if (!empty($selected_categories)) {
                    $insert_values = [];
                    $insert_params = [];
                    
                    foreach ($selected_categories as $category_id) {
                        $insert_values[] = "(?, ?)";
                        $insert_params[] = $product_id;
                        $insert_params[] = $category_id;
                    }
                    
                    $sql = "INSERT INTO product_category_relationships (product_id, category_id) VALUES " . implode(', ', $insert_values);
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($insert_params);
                    
                    error_log('Categories linked to product: ' . count($selected_categories));
                }
                
                // Handle image deletions
                if (!empty($deleted_images)) {
                    foreach ($deleted_images as $image_id) {
                        // Get image path before deletion to delete the file
                        $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ? AND product_id = ?");
                        $stmt->execute([$image_id, $product_id]);
                        $image_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($image_data && !empty($image_data['image_path']) && file_exists($image_data['image_path'])) {
                            unlink($image_data['image_path']);
                            error_log('Deleted image file: ' . $image_data['image_path']);
                        }
                        
                        // Delete database record
                        $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
                        $stmt->execute([$image_id, $product_id]);
                        error_log('Deleted image record ID: ' . $image_id);
                    }
                }
                
                // Set primary image
                if ($primary_image_id > 0) {
                    // First, reset all images to non-primary
                    $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
                    $stmt->execute([$product_id]);
                    
                    // Then, set the selected image as primary
                    $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?");
                    $stmt->execute([$primary_image_id, $product_id]);
                    error_log('Set primary image ID: ' . $primary_image_id);
                }
                
                // Process new product images
                if (isset($_FILES['new_images']) && is_array($_FILES['new_images']['name'])) {
                    $upload_dir = 'uploads/products/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                        error_log('Created upload directory: ' . $upload_dir);
                    }
                    
                    // Process each uploaded image
                    $image_count = count($_FILES['new_images']['name']);
                    for ($i = 0; $i < $image_count; $i++) {
                        if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
                            $file_tmp = $_FILES['new_images']['tmp_name'][$i];
                            $file_name = basename($_FILES['new_images']['name'][$i]);
                            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                            
                            // Generate unique filename
                            $unique_filename = $slug . '-' . uniqid() . '.' . $file_ext;
                            $upload_path = $upload_dir . $unique_filename;
                            
                            // Move uploaded file
                            if (move_uploaded_file($file_tmp, $upload_path)) {
                                // Insert image record
                                $alt_text = !empty($_POST['new_image_alt'][$i]) ? $_POST['new_image_alt'][$i] : $name;
                                $is_primary = 0; // New images are not primary by default
                                
                                $stmt = $pdo->prepare("
                                    INSERT INTO product_images (
                                        product_id,
                                        image_path,
                                        alt_text,
                                        is_primary,
                                        display_order
                                    ) VALUES (?, ?, ?, ?, ?)
                                ");
                                
                                // Get highest display order
                                $stmt_order = $pdo->prepare("
                                    SELECT COALESCE(MAX(display_order), -1) as max_order 
                                    FROM product_images 
                                    WHERE product_id = ?
                                ");
                                $stmt_order->execute([$product_id]);
                                $max_order = $stmt_order->fetch(PDO::FETCH_ASSOC)['max_order'];
                                $new_order = $max_order + 1;
                                
                                $stmt->execute([
                                    $product_id,
                                    $upload_path,
                                    $alt_text,
                                    $is_primary,
                                    $new_order
                                ]);
                                
                                error_log('New image uploaded: ' . $upload_path);
                            } else {
                                error_log('Failed to move uploaded file: ' . $file_tmp);
                            }
                        } else if ($_FILES['new_images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                            error_log('Image upload error: ' . $_FILES['new_images']['error'][$i]);
                        }
                    }
                }
                
                // If there are no images and at least one image is required, set an error
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM product_images WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $image_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($image_count == 0 && $status == 'published') {
                    // Product is published but has no images
                    error_log('Warning: Published product has no images');
                    // This is just a warning, not stopping the update
                }
                
                // Update image alt texts if provided
                if (isset($_POST['image_alt']) && is_array($_POST['image_alt'])) {
                    foreach ($_POST['image_alt'] as $image_id => $alt_text) {
                        $stmt = $pdo->prepare("
                            UPDATE product_images 
                            SET alt_text = ? 
                            WHERE id = ? AND product_id = ?
                        ");
                        $stmt->execute([$alt_text, $image_id, $product_id]);
                    }
                }
                
                // Commit transaction
                $pdo->commit();
                
                // Register activity if the function exists
                if (function_exists('log_admin_activity')) {
                    log_admin_activity('product_updated', 'Product updated: ' . $name);
                }
                
                // Set success message and redirect
                $_SESSION['success_message'] = 'Product updated successfully';
                error_log('Product updated successfully. Redirecting...');
                header('Location: /index.php/admin/products');
                exit;
            }
        } catch (PDOException $e) {
            // Rollback transaction on exception
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            $error_message = 'Database error. Please try again later.';
            error_log('Error updating product: ' . $e->getMessage());
        }
    } else {
        // Display all validation errors
        $error_message = implode(', ', $errors);
        error_log('Validation errors: ' . $error_message);
    }
}