<?php
/**
 * Frontend Product Controller
 * 
 * Handles the display of individual product details on the client side
 */

// Initialize variables
$active_product = null;
$error_message = '';

// Get the product slug from the URL parameters
$params = get_parameters();
$product_slug = isset($params['slug']) ? trim($params['slug']) : '';

// Debug
error_log('Product Controller: Looking for product with slug: ' . $product_slug);

if (!empty($product_slug)) {
    try {
        $pdo = getConnection();
        
        // Get the product by slug
        $stmt = $pdo->prepare("
            SELECT * FROM products 
            WHERE slug = ? AND status = 'published' AND is_deleted = 0
        ");
        $stmt->execute([$product_slug]);
        $active_product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$active_product) {
            // Product not found or not published
            $error_message = 'Product not found or no longer available.';
            error_log('Product Controller: Product not found or not published: ' . $product_slug);
        } else {
            // Get product images
            $stmt = $pdo->prepare("
                SELECT * FROM product_images 
                WHERE product_id = ? 
                ORDER BY is_primary DESC, display_order ASC
            ");
            $stmt->execute([$active_product['id']]);
            $active_product['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get product categories
            $stmt = $pdo->prepare("
                SELECT c.id, c.name, c.slug 
                FROM product_categories c
                JOIN product_category_relationships pcr ON c.id = pcr.category_id
                WHERE pcr.product_id = ? AND c.status = 'active' AND c.is_deleted = 0
            ");
            $stmt->execute([$active_product['id']]);
            $active_product['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug
            error_log('Product Controller: Product found: ' . $active_product['name']);
            error_log('Product Controller: Product has ' . count($active_product['images']) . ' images');
            error_log('Product Controller: Product belongs to ' . count($active_product['categories']) . ' categories');
        }
    } catch (PDOException $e) {
        $error_message = 'Error loading product. Please try again later.';
        error_log('Product Controller - Error loading product: ' . $e->getMessage());
    }
} else {
    $error_message = 'No product specified.';
    error_log('Product Controller: No product slug provided');
}