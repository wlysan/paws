<?php
/**
 * Frontend Categories Controller
 * 
 * Handles the display of categories on the client side
 */

// Get the active category if provided
$category_slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$active_category = null;

if (!empty($category_slug)) {
    try {
        $pdo = getConnection();
        
        // Get the category by slug
        $stmt = $pdo->prepare("
            SELECT * FROM product_categories 
            WHERE slug = ? AND status = 'active' AND is_deleted = 0
        ");
        $stmt->execute([$category_slug]);
        $active_category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$active_category) {
            // Category not found or not active
            $error_message = 'Category not found or no longer available.';
        }
        
    } catch (PDOException $e) {
        $error_message = 'Error loading category. Please try again later.';
        error_log('Load category error: ' . $e->getMessage());
    }
}

// Load all categories for display
$categories = [];
try {
    $pdo = getConnection();
    
    // Get active categories
    $stmt = $pdo->prepare("
        SELECT * FROM product_categories 
        WHERE status = 'active' AND is_deleted = 0
        ORDER BY display_order ASC, name ASC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = 'Error loading categories. Please try again later.';
    error_log('Load categories error: ' . $e->getMessage());
}

// Build the category tree for nested display
$category_tree = build_category_tree($categories);

// Get featured categories
$featured_categories = array_filter($categories, function($category) {
    return $category['status'] === 'featured';
});