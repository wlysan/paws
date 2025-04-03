<?php
/**
 * Admin Product Controller - Main
 * 
 * Handles the main listing of products in the admin interface
 */

// Require admin login if the function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Initialize global variables for the view
global $products, $categories, $success_message, $error_message, $total_products, $total_pages, $current_page;

// Load messages from session if they exist
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear session messages after use
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}

// Debug for investigation
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
        
        // Get products in two steps to avoid complex subqueries
        
        // Step 1: Get the basic product data
        $stmt = $pdo->prepare("
            SELECT * 
            FROM products 
            WHERE is_deleted = 0
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$items_per_page, $offset]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug information
        error_log('Basic product query successful, found ' . count($products) . ' products');
        
        // Step 2: Enhance products with additional data
        if (!empty($products)) {
            // Create array to store product IDs
            $product_ids = [];
            foreach ($products as $product) {
                $product_ids[] = $product['id'];
            }
            
            // Get primary images for all products at once
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $pdo->prepare("
                SELECT product_id, image_path 
                FROM product_images 
                WHERE product_id IN ($placeholders) AND is_primary = 1
            ");
            $stmt->execute($product_ids);
            $primary_images = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get category names
            $stmt = $pdo->prepare("
                SELECT pcr.product_id, GROUP_CONCAT(c.name SEPARATOR ', ') as category_names
                FROM product_category_relationships pcr
                JOIN product_categories c ON pcr.category_id = c.id
                WHERE pcr.product_id IN ($placeholders)
                GROUP BY pcr.product_id
            ");
            $stmt->execute($product_ids);
            $category_names = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get category counts
            $stmt = $pdo->prepare("
                SELECT product_id, COUNT(*) as count
                FROM product_category_relationships
                WHERE product_id IN ($placeholders)
                GROUP BY product_id
            ");
            $stmt->execute($product_ids);
            $category_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Add this data to our products array
            foreach ($products as &$product) {
                $product['primary_image'] = $primary_images[$product['id']] ?? null;
                $product['category_names'] = $category_names[$product['id']] ?? '';
                $product['category_count'] = (int)($category_counts[$product['id']] ?? 0);
            }
            
            // Debug - check enhanced products
            error_log('Products enhanced with images and categories');
        }
        
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
        error_log('Products found: ' . count($products));
    } else {
        // The table doesn't exist, create an informative message
        $error_message = 'The products table has not been created yet. Please add your first product.';
        error_log('Table products not found');
    }
    
} catch (PDOException $e) {
    $error_message = 'Error loading products. Please try again later.';
    error_log('Error loading products: ' . $e->getMessage());
    error_log('SQL Error Code: ' . $e->getCode());
    
    if (isset($e->errorInfo) && is_array($e->errorInfo)) {
        error_log('SQL State: ' . ($e->errorInfo[0] ?? 'Unknown'));
    }
}

// Add debug info at the top of the page if requested
if (isset($_GET['debug'])) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc; background: #f9f9f9;'>";
    echo "<h3>Debug Information</h3>";
    
    echo "<p>Total products: " . $total_products . "</p>";
    echo "<p>Number of products in array: " . count($products) . "</p>";
    echo "<p>Error message: " . ($error_message ?? 'None') . "</p>";
    
    if (!empty($products)) {
        echo "<h4>First Product:</h4>";
        echo "<pre>";
        print_r($products[0]);
        echo "</pre>";
    } else {
        echo "<p>Products array is empty!</p>";
    }
    
    echo "</div>";
}

// Set products to empty array if null to avoid template errors
if ($products === null) {
    $products = [];
    error_log('Products was null, setting to empty array to avoid template errors');
}

// Debug
error_log('Admin product controller completed processing');