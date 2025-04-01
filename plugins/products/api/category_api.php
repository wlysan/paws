<?php
/**
 * Category API
 * 
 * Provides API endpoints for category functionality
 */

// Set headers for JSON response
header('Content-Type: application/json');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Process API request
switch ($action) {
    case 'get_categories':
        // Get categories for client-side display
        try {
            $pdo = getConnection();
            
            // Parameters
            $include_inactive = isset($_GET['include_inactive']) && $_GET['include_inactive'] === 'true';
            $parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;
            
            // Build query
            $sql = "SELECT id, name, slug, description, parent_id, image_path, status 
                    FROM product_categories 
                    WHERE is_deleted = 0";
            $params = [];
            
            if (!$include_inactive) {
                $sql .= " AND status = 'active'";
            }
            
            if ($parent_id !== null) {
                $sql .= " AND parent_id " . ($parent_id === 0 ? "IS NULL" : "= ?");
                if ($parent_id > 0) {
                    $params[] = $parent_id;
                }
            }
            
            $sql .= " ORDER BY display_order ASC, name ASC";
            
            // Execute query
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format response
            $response = [
                'status' => 'success',
                'data' => [
                    'categories' => $categories
                ]
            ];
            
            echo json_encode($response);
            
        } catch (PDOException $e) {
            // Log error
            error_log('API get_categories error: ' . $e->getMessage());
            
            // Return error response
            $response = [
                'status' => 'error',
                'message' => 'Failed to retrieve categories'
            ];
            
            http_response_code(500);
            echo json_encode($response);
        }
        break;
        
    case 'get_category':
        // Get single category by ID or slug
        try {
            $pdo = getConnection();
            
            $category_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $category_slug = isset($_GET['slug']) ? $_GET['slug'] : null;
            
            if ($category_id) {
                $stmt = $pdo->prepare("
                    SELECT id, name, slug, description, parent_id, image_path, status 
                    FROM product_categories 
                    WHERE id = ? AND is_deleted = 0
                ");
                $stmt->execute([$category_id]);
            } elseif ($category_slug) {
                $stmt = $pdo->prepare("
                    SELECT id, name, slug, description, parent_id, image_path, status 
                    FROM product_categories 
                    WHERE slug = ? AND is_deleted = 0
                ");
                $stmt->execute([$category_slug]);
            } else {
                throw new Exception('Either id or slug must be provided');
            }
            
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$category) {
                $response = [
                    'status' => 'error',
                    'message' => 'Category not found'
                ];
                
                http_response_code(404);
                echo json_encode($response);
                exit;
            }
            
            // Get parent category if exists
            if ($category['parent_id']) {
                $stmt = $pdo->prepare("
                    SELECT id, name, slug 
                    FROM product_categories 
                    WHERE id = ?
                ");
                $stmt->execute([$category['parent_id']]);
                $parent = $stmt->fetch(PDO::FETCH_ASSOC);
                $category['parent'] = $parent;
            }
            
            // Get subcategories
            $stmt = $pdo->prepare("
                SELECT id, name, slug, image_path 
                FROM product_categories 
                WHERE parent_id = ? AND status = 'active' AND is_deleted = 0
                ORDER BY display_order ASC, name ASC
            ");
            $stmt->execute([$category['id']]);
            $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $category['subcategories'] = $subcategories;
            
            // Format response
            $response = [
                'status' => 'success',
                'data' => [
                    'category' => $category
                ]
            ];
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            // Log error
            error_log('API get_category error: ' . $e->getMessage());
            
            // Return error response
            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            
            http_response_code(400);
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