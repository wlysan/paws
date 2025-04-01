<?php
/**
 * Products API
 * 
 * Provides API endpoints for product functionality
 */

// Set headers for JSON response
header('Content-Type: application/json');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Process API request
switch ($action) {
    case 'list_products':
        // Parameters
        $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';
        $sort_dir = isset($_GET['sort_dir']) ? strtoupper($_GET['sort_dir']) : 'ASC';
        
        try {
            $pdo = getConnection();
            
            // Build base query
            $sql_base = "
                FROM products p
                WHERE p.status = 'published' AND p.is_deleted = 0
            ";
            
            $params = [];
            
            // Filter by category if specified
            if ($category_id) {
                $sql_base .= "
                    AND EXISTS (
                        SELECT 1 FROM product_category_relationships pcr
                        WHERE pcr.product_id = p.id AND pcr.category_id = ?
                    )
                ";
                $params[] = $category_id;
            }
            
            // Count total products
            $count_sql = "SELECT COUNT(*) as total " . $sql_base;
            $stmt = $pdo->prepare($count_sql);
            $stmt->execute($params);
            $total_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $total_result['total'];
            
            // Validate sort parameters
            $allowed_sort_fields = ['name', 'price', 'created_at'];
            $allowed_sort_dirs = ['ASC', 'DESC'];
            
            if (!in_array($sort_by, $allowed_sort_fields)) {
                $sort_by = 'name';
            }
            
            if (!in_array($sort_dir, $allowed_sort_dirs)) {
                $sort_dir = 'ASC';
            }
            
            // Get products with pagination
            $sql = "
                SELECT p.*, 
                       (SELECT pi.image_path FROM product_images pi 
                        WHERE pi.product_id = p.id AND pi.is_primary = 1 
                        LIMIT 1) as primary_image
                " . $sql_base . "
                ORDER BY p." . $sort_by . " " . $sort_dir . "
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format response
            $response = [
                'status' => 'success',
                'data' => [
                    'products' => $products,
                    'pagination' => [
                        'total' => $total,
                        'per_page' => $limit,
                        'current_page' => $page,
                        'last_page' => ceil($total / $limit)
                    ]
                ]
            ];
            
            echo json_encode($response);
            
        } catch (PDOException $e) {
            error_log('API list_products error: ' . $e->getMessage());
            
            $response = [
                'status' => 'error',
                'message' => 'Failed to retrieve products'
            ];
            
            http_response_code(500);
            echo json_encode($response);
        }
        break;
        
    case 'product_details':
        // Get product ID or slug
        $product_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $product_slug = isset($_GET['slug']) ? $_GET['slug'] : null;
        
        if (!$product_id && !$product_slug) {
            $response = [
                'status' => 'error',
                'message' => 'Product ID or slug is required'
            ];
            
            http_response_code(400);
            echo json_encode($response);
            exit;
        }
        
        try {
            $pdo = getConnection();
            
            // Build query based on ID or slug
            if ($product_id) {
                $sql = "
                    SELECT p.* 
                    FROM products p
                    WHERE p.id = ? AND p.status = 'published' AND p.is_deleted = 0
                ";
                $params = [$product_id];
            } else {
                $sql = "
                    SELECT p.* 
                    FROM products p
                    WHERE p.slug = ? AND p.status = 'published' AND p.is_deleted = 0
                ";
                $params = [$product_slug];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                $response = [
                    'status' => 'error',
                    'message' => 'Product not found'
                ];
                
                http_response_code(404);
                echo json_encode($response);
                exit;
            }
            
            // Get product images
            $stmt = $pdo->prepare("
                SELECT * FROM product_images 
                WHERE product_id = ? 
                ORDER BY is_primary DESC, display_order ASC
            ");
            $stmt->execute([$product['id']]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $product['images'] = $images;
            
            // Get product categories
            $stmt = $pdo->prepare("
                SELECT c.id, c.name, c.slug 
                FROM product_categories c
                JOIN product_category_relationships pcr ON c.id = pcr.category_id
                WHERE pcr.product_id = ? AND c.status = 'active' AND c.is_deleted = 0
            ");
            $stmt->execute([$product['id']]);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $product['categories'] = $categories;
            
            // Format response
            $response = [
                'status' => 'success',
                'data' => [
                    'product' => $product
                ]
            ];
            
            echo json_encode($response);
            
        } catch (PDOException $e) {
            error_log('API product_details error: ' . $e->getMessage());
            
            $response = [
                'status' => 'error',
                'message' => 'Failed to retrieve product details'
            ];
            
            http_response_code(500);
            echo json_encode($response);
        }
        break;
        
    default:
        // Invalid action
        $response = [
            'status' => 'error',
            'message' => 'Invalid API action'
        ];
        
        http_response_code(400);
        echo json_encode($response);
        break;
}