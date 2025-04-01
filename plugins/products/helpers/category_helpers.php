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
/**
 * Generate slug from string
 * 
 * @param string $string The string to create slug from
 * @return string Formatted slug
 */
function generate_slug($string) {
    // Log para depuração
    error_log('Gerando slug para: ' . $string);
    
    // Converter para minúsculas
    $slug = strtolower($string);
    
    // Remover caracteres especiais
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    
    // Substituir espaços por hifens
    $slug = preg_replace('/\s+/', '-', $slug);
    
    // Remover hifens múltiplos
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Remover hifens no início e fim
    $slug = trim($slug, '-');
    
    // Se o slug ficar vazio, usar um valor padrão
    if (empty($slug)) {
        $slug = 'category-' . uniqid();
    }
    
    error_log('Slug gerado: ' . $slug);
    return $slug;
}



/**
 * Check if a category slug exists
 * 
 * @param string $slug The slug to check
 * @param int $exclude_id Category ID to exclude from check (for updates)
 * @return bool True if exists, false otherwise
 */
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
        error_log('Verificando se slug existe: ' . $slug . ' - Resultado: ' . ($exists ? 'Sim' : 'Não'));
        
        return $exists;
    } catch (PDOException $e) {
        error_log('Erro ao verificar slug: ' . $e->getMessage());
        return false;
    }
}