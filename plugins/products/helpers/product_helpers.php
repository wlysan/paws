<?php
/**
 * Funções auxiliares para produtos
 */

/**
 * Get product by ID
 * 
 * @param int $product_id The product ID
 * @return array|null Product data or null if not found
 */
function get_product_by_id($product_id) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            SELECT * FROM products 
            WHERE id = ? AND is_deleted = 0
        ");
        
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Get product error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get products by category
 * 
 * @param int $category_id The category ID
 * @param int $limit Number of products to return
 * @param int $offset Offset for pagination
 * @return array List of products
 */
function get_products_by_category($category_id, $limit = 10, $offset = 0) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   (SELECT pi.image_path FROM product_images pi 
                    WHERE pi.product_id = p.id AND pi.is_primary = 1 
                    LIMIT 1) as primary_image
            FROM products p
            JOIN product_category_relationships pcr ON p.id = pcr.product_id
            WHERE pcr.category_id = ? 
            AND p.status = 'published' 
            AND p.is_deleted = 0
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$category_id, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Get products by category error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Count products in a category
 * 
 * @param int $category_id The category ID
 * @return int Number of products
 */
function count_category_products($category_id) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM products p
            JOIN product_category_relationships pcr ON p.id = pcr.product_id
            WHERE pcr.category_id = ? 
            AND p.status = 'published' 
            AND p.is_deleted = 0
        ");
        
        $stmt->execute([$category_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['total'];
    } catch (PDOException $e) {
        error_log('Count category products error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Upload and process product image
 * 
 * @param array $file The uploaded file from $_FILES
 * @param string $product_identifier Product name or ID for the filename
 * @return string|false Image path if successful, false on failure
 */
function process_product_image($file, $product_identifier) {
    // Check if file was uploaded properly
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = 'uploads/products/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $unique_name = generate_slug($product_identifier) . '-' . uniqid() . '.' . $file_ext;
    $upload_path = $upload_dir . $unique_name;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $upload_path;
    }
    
    return false;
}