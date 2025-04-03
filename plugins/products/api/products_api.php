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
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        $sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

        try {
            $pdo = getConnection();

            // Build query conditions
            $conditions = ["p.is_deleted = 0"];
            $params = [];

            if ($category_id) {
                $conditions[] = "EXISTS (
                    SELECT 1 FROM product_category_relationships pcr
                    WHERE pcr.product_id = p.id AND pcr.category_id = ?
                )";
                $params[] = $category_id;
            }

            if ($status) {
                $conditions[] = "p.status = ?";
                $params[] = $status;
            }

            if ($search) {
                $conditions[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
                $search_param = '%' . $search . '%';
                $params[] = $search_param;
                $params[] = $search_param;
                $params[] = $search_param;
            }

            $where_clause = implode(' AND ', $conditions);

            // Determine sort field and direction
            $order_by = "p.created_at DESC"; // Default (newest)

            switch ($sort_by) {
                case 'price-low':
                    $order_by = "p.price ASC";
                    break;
                case 'price-high':
                    $order_by = "p.price DESC";
                    break;
                case 'name-asc':
                    $order_by = "p.name ASC";
                    break;
                case 'name-desc':
                    $order_by = "p.name DESC";
                    break;
            }

            // Count total
            $count_sql = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
            $stmt = $pdo->prepare($count_sql);
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get products
            $sql = "
                SELECT p.*,
                    (SELECT pi.image_path 
                     FROM product_images pi 
                     WHERE pi.product_id = p.id AND pi.is_primary = 1 
                     LIMIT 1) as primary_image,
                    (SELECT GROUP_CONCAT(c.name SEPARATOR ', ')
                     FROM product_categories c
                     JOIN product_category_relationships pcr ON c.id = pcr.category_id
                     WHERE pcr.product_id = p.id) as category_names
                FROM products p
                WHERE $where_clause
                ORDER BY $order_by
                LIMIT ? OFFSET ?
            ";

            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'status' => 'success',
                'data' => [
                    'products' => $products,
                    'pagination' => [
                        'total' => (int)$total,
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
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
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
