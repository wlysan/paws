<?php
/**
 * Frontend Products Controller
 * 
 * Handles the display of product listings on the client side
 */

// Initialize variables
global $products, $categories, $total_products, $total_pages, $current_page, $query_params, $selected_category;

$products = [];
$categories = [];
$total_products = 0;
$total_pages = 1;
$items_per_page = 12;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$query_params = '';
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Debug
error_log('Products Controller: Loading products list');

try {
    $pdo = getConnection();
    
    // Build query based on filters
    $where_conditions = ["p.status = 'published'", "p.is_deleted = 0"];
    $params = [];
    $extra_query_parts = [];
    
    // Category filter
    if (!empty($selected_category)) {
        $where_conditions[] = "EXISTS (
            SELECT 1 FROM product_category_relationships pcr
            WHERE pcr.product_id = p.id AND pcr.category_id = ?
        )";
        $params[] = $selected_category;
        $extra_query_parts[] = 'category=' . $selected_category;
    }
    
    // Price range filter
    if (isset($_GET['price']) && !empty($_GET['price'])) {
        $price_range = explode('-', $_GET['price']);
        
        if (count($price_range) == 2) {
            $min_price = (float)$price_range[0];
            $max_price = !empty($price_range[1]) ? (float)$price_range[1] : PHP_FLOAT_MAX;
            
            if ($min_price > 0) {
                $where_conditions[] = "p.price >= ?";
                $params[] = $min_price;
            }
            
            if ($max_price < PHP_FLOAT_MAX) {
                $where_conditions[] = "p.price <= ?";
                $params[] = $max_price;
            }
            
            $extra_query_parts[] = 'price=' . $_GET['price'];
        }
    }
    
    // Create WHERE clause
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count for pagination
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM products p
        WHERE $where_clause
    ";
    
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_products = (int)$result['total'];
    $total_pages = ceil($total_products / $items_per_page);
    
    // Ensure current page is within valid range
    $current_page = min($current_page, max(1, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    
    // Determine sort order
    $sort_field = 'created_at';
    $sort_direction = 'DESC';
    
    if (isset($_GET['sort']) && !empty($_GET['sort'])) {
        switch ($_GET['sort']) {
            case 'price-low':
                $sort_field = 'price';
                $sort_direction = 'ASC';
                break;
            case 'price-high':
                $sort_field = 'price';
                $sort_direction = 'DESC';
                break;
            case 'name-asc':
                $sort_field = 'name';
                $sort_direction = 'ASC';
                break;
            case 'name-desc':
                $sort_field = 'name';
                $sort_direction = 'DESC';
                break;
            default:
                $sort_field = 'created_at';
                $sort_direction = 'DESC';
        }
        
        $extra_query_parts[] = 'sort=' . $_GET['sort'];
    }
    
    // Build query string for pagination links
    if (!empty($extra_query_parts)) {
        $query_params = '&' . implode('&', $extra_query_parts);
    }
    
    // Get products with pagination and primary image
    $sql = "
        SELECT p.*, 
               (SELECT pi.image_path 
                FROM product_images pi 
                WHERE pi.product_id = p.id AND pi.is_primary = 1 
                LIMIT 1) as primary_image,
               (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
                FROM product_categories c 
                JOIN product_category_relationships pcr ON c.id = pcr.category_id 
                WHERE pcr.product_id = p.id 
                LIMIT 1) as category_name
        FROM products p
        WHERE $where_clause
        ORDER BY p.$sort_field $sort_direction
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $items_per_page;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all active categories for filters
    $stmt = $pdo->prepare("
        SELECT id, name
        FROM product_categories 
        WHERE status = 'active' AND is_deleted = 0
        ORDER BY name ASC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug
    error_log('Products Controller: Found ' . count($products) . ' products');
    error_log('Products Controller: Total pages: ' . $total_pages);
    
} catch (PDOException $e) {
    error_log('Products Controller - Error loading products: ' . $e->getMessage());
    
    // Set empty results on error
    $products = [];
    $total_products = 0;
    $total_pages = 1;
}