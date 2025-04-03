<?php
/**
 * Admin Product Controller - Add
 * 
 * Handles adding new products with support for multiple images and category relationships
 */

// Require admin login if the function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Initialize global variables for the view
global $categories, $success_message, $error_message;

$success_message = '';
$error_message = '';

// Add detailed logging for debugging
error_log('Product Add Controller loaded');
error_log('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
error_log('POST data: ' . print_r($_POST, true));

// Load active categories for the form
try {
    $pdo = getConnection();
    
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
    $error_message = 'Error loading categories.';
    error_log('Error loading categories: ' . $e->getMessage());
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log for debugging
    error_log('Processing product add form submission');
    
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
            error_log('Starting database insertion');
            $pdo = getConnection();
            
            // Check if the table exists, create it if necessary
            $stmt = $pdo->prepare("
                SHOW TABLES LIKE 'products'
            ");
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                error_log('Table products not found, creating automatically');
                
                // Create products table
                $sql = "
                CREATE TABLE products (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    sku VARCHAR(50) NOT NULL UNIQUE,
                    name VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) NOT NULL UNIQUE,
                    description TEXT,
                    short_description VARCHAR(500),
                    price DECIMAL(10,2) NOT NULL,
                    regular_price DECIMAL(10,2) NULL,
                    sale_price DECIMAL(10,2) NULL,
                    stock_quantity INT NOT NULL DEFAULT 0,
                    weight DECIMAL(8,2) NULL,
                    dimensions_length DECIMAL(8,2) NULL,
                    dimensions_width DECIMAL(8,2) NULL,
                    dimensions_height DECIMAL(8,2) NULL,
                    is_featured BOOLEAN DEFAULT FALSE,
                    is_new BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                    status ENUM('draft', 'published', 'out_of_stock', 'discontinued') NOT NULL DEFAULT 'draft',
                    is_deleted BOOLEAN DEFAULT FALSE,
                    deleted_at TIMESTAMP NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ";
                
                $pdo->exec($sql);
                error_log('Table products created successfully');
                
                // Create product_category_relationships table
                $sql = "
                CREATE TABLE IF NOT EXISTS product_category_relationships (
                    product_id INT NOT NULL,
                    category_id INT NOT NULL,
                    PRIMARY KEY (product_id, category_id),
                    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ";
                
                $pdo->exec($sql);
                error_log('Table product_category_relationships created successfully');
                
                // Create product_images table
                $sql = "
                CREATE TABLE IF NOT EXISTS product_images (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    product_id INT NOT NULL,
                    image_path VARCHAR(255) NOT NULL,
                    alt_text VARCHAR(255),
                    is_primary BOOLEAN DEFAULT FALSE,
                    display_order INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ";
                
                $pdo->exec($sql);
                error_log('Table product_images created successfully');
            }
            
            // Check if SKU already exists
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM products 
                WHERE sku = ? AND is_deleted = 0
            ");
            $stmt->execute([$sku]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $error_message = 'This SKU already exists. Please use a unique SKU.';
                error_log('Duplicate SKU: ' . $sku);
            } else {
                // Generate slug
                $slug = generate_slug($name);
                $original_slug = $slug;
                
                // Check if slug exists and add suffix if necessary
                $counter = 1;
                while (true) {
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as count 
                        FROM products 
                        WHERE slug = ? AND is_deleted = 0
                    ");
                    $stmt->execute([$slug]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($result['count'] == 0) {
                        break;
                    }
                    
                    $slug = $original_slug . '-' . $counter;
                    $counter++;
                }
                
                // Begin transaction
                $pdo->beginTransaction();
                
                // Insert product
                $stmt = $pdo->prepare("
                    INSERT INTO products (
                        sku, 
                        name, 
                        slug, 
                        description, 
                        short_description, 
                        price, 
                        regular_price, 
                        sale_price, 
                        stock_quantity, 
                        weight, 
                        dimensions_length, 
                        dimensions_width, 
                        dimensions_height, 
                        is_featured, 
                        is_new, 
                        status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                    $status
                ];
                
                error_log('INSERT parameters: ' . print_r($params, true));
                $stmt->execute($params);
                
                // Get the new product ID
                $product_id = $pdo->lastInsertId();
                error_log('New product ID: ' . $product_id);
                
                if ($product_id) {
                    // Process category relationships
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
                    
                    // Process product images
                    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                        $upload_dir = 'uploads/products/';
                        
                        // Create directory if it doesn't exist
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                            error_log('Created upload directory: ' . $upload_dir);
                        }
                        
                        // Determine which image is primary
                        $primary_image_index = isset($_POST['primary_image']) ? (int)$_POST['primary_image'] : 0;
                        
                        // Process each uploaded image
                        $image_count = count($_FILES['images']['name']);
                        for ($i = 0; $i < $image_count; $i++) {
                            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                                $file_tmp = $_FILES['images']['tmp_name'][$i];
                                $file_name = basename($_FILES['images']['name'][$i]);
                                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                
                                // Generate unique filename
                                $unique_filename = $slug . '-' . uniqid() . '.' . $file_ext;
                                $upload_path = $upload_dir . $unique_filename;
                                
                                // Move uploaded file
                                if (move_uploaded_file($file_tmp, $upload_path)) {
                                    // Insert image record
                                    $is_primary = ($i === $primary_image_index) ? 1 : 0;
                                    $alt_text = !empty($_POST['image_alt'][$i]) ? $_POST['image_alt'][$i] : $name;
                                    
                                    $stmt = $pdo->prepare("
                                        INSERT INTO product_images (
                                            product_id,
                                            image_path,
                                            alt_text,
                                            is_primary,
                                            display_order
                                        ) VALUES (?, ?, ?, ?, ?)
                                    ");
                                    
                                    $stmt->execute([
                                        $product_id,
                                        $upload_path,
                                        $alt_text,
                                        $is_primary,
                                        $i
                                    ]);
                                    
                                    error_log('Image uploaded: ' . $upload_path);
                                } else {
                                    error_log('Failed to move uploaded file: ' . $file_tmp);
                                }
                            } else if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                                error_log('Image upload error: ' . $_FILES['images']['error'][$i]);
                            }
                        }
                    }
                    
                    // Commit transaction
                    $pdo->commit();
                    
                    // Register activity if the function exists
                    if (function_exists('log_admin_activity')) {
                        log_admin_activity('product_created', 'Product created: ' . $name);
                    }
                    
                    // Set success message and redirect
                    $_SESSION['success_message'] = 'Product added successfully';
                    error_log('Product added successfully. Redirecting...');
                    header('Location: /index.php/admin/products');
                    exit;
                } else {
                    // Rollback transaction on failure
                    $pdo->rollBack();
                    $error_message = 'Failed to add product.';
                    error_log('No ID returned after insertion');
                }
            }
        } catch (PDOException $e) {
            // Rollback transaction on exception
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            $error_message = 'Database error. Please try again later.';
            error_log('Error adding product: ' . $e->getMessage());
        }
    } else {
        // Display all validation errors
        $error_message = implode(', ', $errors);
        error_log('Validation errors: ' . $error_message);
    }
}