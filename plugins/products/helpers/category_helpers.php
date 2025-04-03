<?php
/**
 * Funções auxiliares para categorias de produtos
 */

/**
 * Get category by ID
 * 
 * @param int $category_id The category ID
 * @return array|null Category data or null if not found
 */
function get_category_by_id($category_id) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            SELECT * FROM product_categories 
            WHERE id = ? AND is_deleted = 0
        ");
        
        $stmt->execute([$category_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Get category error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get all categories
 * 
 * @param bool $include_inactive Whether to include inactive categories
 * @return array List of categories
 */
function get_all_categories($include_inactive = false) {
    try {
        $pdo = getConnection();
        
        $sql = "SELECT * FROM product_categories WHERE is_deleted = 0";
        if (!$include_inactive) {
            $sql .= " AND status = 'active'";
        }
        $sql .= " ORDER BY parent_id ASC, display_order ASC, name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Get all categories error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Create formatted category tree from flat list
 * 
 * @param array $categories Flat array of categories
 * @param int $parent_id Parent ID to start from (default: null for root)
 * @param int $level Current nesting level for indentation
 * @return array Categorized array
 */
function build_category_tree($categories, $parent_id = null, $level = 0) {
    $tree = [];
    
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id) {
            $category['level'] = $level;
            $category['children'] = build_category_tree($categories, $category['id'], $level + 1);
            $tree[] = $category;
        }
    }
    
    return $tree;
}

/**
 * Generate slug from string
 * 
 * @param string $string The string to create slug from
 * @return string Formatted slug
 */
function generate_slug($string) {
    // Debug logging
    error_log('Generating slug for: ' . $string);
    
    // Convert to lowercase
    $slug = strtolower($string);
    
    // Replace special characters with empty space
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    
    // Replace spaces with hyphens
    $slug = preg_replace('/\s+/', '-', $slug);
    
    // Remove multiple hyphens
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Trim hyphens from beginning and end
    $slug = trim($slug, '-');
    
    // If slug is empty, use a default value
    if (empty($slug)) {
        $slug = 'category-' . uniqid();
    }
    
    error_log('Generated slug: ' . $slug);
    return $slug;
}

/**
 * Check if a category slug exists
 * 
 * @param string $slug The slug to check
 * @param int $exclude_id Category ID to exclude from check (for updates)
 * @return bool True if exists, false otherwise
 */
function category_slug_exists($slug, $exclude_id = null) {
    try {
        $pdo = getConnection();
        
        // First check if the table exists
        $stmt = $pdo->prepare("
            SHOW TABLES LIKE 'product_categories'
        ");
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            // Table doesn't exist, so slug can't exist
            return false;
        }
        
        $sql = "SELECT COUNT(*) as count FROM product_categories WHERE slug = ?";
        $params = [$slug];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $exists = ($result['count'] > 0);
        error_log('Checking if slug exists: ' . $slug . ' - Result: ' . ($exists ? 'Yes' : 'No'));
        
        return $exists;
    } catch (PDOException $e) {
        error_log('Error checking slug: ' . $e->getMessage());
        return false;
    }
}