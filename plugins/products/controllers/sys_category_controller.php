<?php
/**
 * Admin Category Controller - Main
 * 
 * Handles the main listing of product categories in the admin interface
 */

// Require admin login if the function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Inicializar variáveis globais para a view
global $categories, $category_tree, $success_message, $error_message;

// Carregar mensagens da sessão se existirem
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Limpar mensagens da sessão após uso
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}

// Carregar categorias para exibição
$categories = [];
$category_tree = [];

try {
    $pdo = getConnection();
    
    // Obter todas as categorias
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COALESCE((SELECT COUNT(*) FROM product_category_relationships WHERE category_id = c.id), 0) as product_count,
               p.name as parent_name
        FROM product_categories c
        LEFT JOIN product_categories p ON c.parent_id = p.id
        WHERE c.is_deleted = 0
        ORDER BY c.parent_id ASC, c.display_order ASC, c.name ASC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Construir árvore de categorias para exibição
    $category_tree = build_category_tree($categories);
    
} catch (PDOException $e) {
    $error_message = 'Erro ao carregar categorias. Por favor, tente novamente mais tarde.';
    error_log('Erro ao carregar categorias: ' . $e->getMessage());
}