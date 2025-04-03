<?php

// Debug para investigar o problema
    error_log('Debugging produto: Starting product loading');

    // Load products for display with pagination
    $products = [];
    $categories = [];
    $total_products = 0;
    $total_pages = 1;
    $current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $items_per_page = 10;

    try {
        $pdo = getConnection();
        
        // Check if the table exists
        $stmt = $pdo->prepare("
            SHOW TABLES LIKE 'products'
        ");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Get total count for pagination
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total 
                FROM products 
                WHERE is_deleted = 0
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_products = (int)$result['total'];
            $total_pages = ceil($total_products / $items_per_page);
            
            // Ensure current page is within valid range
            $current_page = min($current_page, max(1, $total_pages));
            $offset = ($current_page - 1) * $items_per_page;
            
            // Get products with pagination, primary image, and category count
            // Simplified query to debug potential issues
            $stmt = $pdo->prepare("
                SELECT p.*
                FROM products p
                WHERE p.is_deleted = 0
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$items_per_page, $offset]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Now, let's add image and category information separately for each product
            foreach($products as &$product) {
                // Get primary image
                $img_stmt = $pdo->prepare("
                    SELECT image_path 
                    FROM product_images 
                    WHERE product_id = ? AND is_primary = 1 
                    LIMIT 1
                ");
                $img_stmt->execute([$product['id']]);
                $image = $img_stmt->fetch(PDO::FETCH_ASSOC);
                $product['primary_image'] = $image ? $image['image_path'] : null;
                
                // Get category count
                $cat_count_stmt = $pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM product_category_relationships 
                    WHERE product_id = ?
                ");
                $cat_count_stmt->execute([$product['id']]);
                $count = $cat_count_stmt->fetch(PDO::FETCH_ASSOC);
                $product['category_count'] = (int)$count['count'];
                
                // Get category names
                $cat_names_stmt = $pdo->prepare("
                    SELECT GROUP_CONCAT(c.name SEPARATOR ', ') as category_names
                    FROM product_categories c 
                    JOIN product_category_relationships pcr ON c.id = pcr.category_id 
                    WHERE pcr.product_id = ?
                ");
                $cat_names_stmt->execute([$product['id']]);
                $names = $cat_names_stmt->fetch(PDO::FETCH_ASSOC);
                $product['category_names'] = $names ? $names['category_names'] : '';
            }
            
            // Get all active categories for filters and forms
            $stmt = $pdo->prepare("
                SELECT id, name 
                FROM product_categories 
                WHERE status = 'active' AND is_deleted = 0
                ORDER BY name ASC
            ");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug
            error_log('Products found: ' . count($products));
        } else {
            // The table doesn't exist, create an informative message
            $error_message = 'The products table has not been created yet. Please add your first product.';
            error_log('Table products not found');
        }
        
    } catch (PDOException $e) {
        $error_message = 'Error loading products. Please try again later.';
        error_log('Error loading products: ' . $e->getMessage());
        
        // Log detailed error information
        error_log('SQL Error: ' . $e->getCode() . ' - ' . $e->getMessage());
        error_log('Error trace: ' . $e->getTraceAsString());
    }

    // Show debug information if requested
    if (isset($_GET['debug_products']) && $_GET['debug_products'] == '1') {
        // Include the debug script
        include_once 'plugins/products/debug-products.php';
        exit;
    }