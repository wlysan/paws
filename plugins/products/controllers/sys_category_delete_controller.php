<?php
/**
 * Admin Category Controller - Delete
 * 
 * Handles deletion of product categories
 */

// Require admin login if the function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Processar exclusão apenas via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php/admin/categories');
    exit;
}

// Obter ID da categoria
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

if ($category_id <= 0) {
    $_SESSION['error_message'] = 'ID de categoria inválido';
    header('Location: /index.php/admin/categories');
    exit;
}

try {
    $pdo = getConnection();
    
    // Verificar se a categoria existe
    $stmt = $pdo->prepare("SELECT * FROM product_categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        $_SESSION['error_message'] = 'Categoria não encontrada';
        header('Location: /index.php/admin/categories');
        exit;
    }
    
    // Verificar se a categoria tem filhos
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM product_categories 
        WHERE parent_id = ? AND is_deleted = 0
    ");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        $_SESSION['error_message'] = 'Não é possível excluir uma categoria com subcategorias. Por favor, exclua ou reatribua as subcategorias primeiro.';
        header('Location: /index.php/admin/categories');
        exit;
    }
    
    // Verificar se a categoria é usada por produtos
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM product_category_relationships 
        WHERE category_id = ?
    ");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        // Realizar soft delete apenas
        $stmt = $pdo->prepare("
            UPDATE product_categories 
            SET is_deleted = 1, 
                deleted_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$category_id]);
        
        // Excluir relacionamentos categoria-produto
        $stmt = $pdo->prepare("
            DELETE FROM product_category_relationships 
            WHERE category_id = ?
        ");
        $stmt->execute([$category_id]);
        
        $_SESSION['success_message'] = 'Categoria excluída com sucesso (exclusão lógica - relacionamentos de produtos foram removidos)';
    } else {
        // Se a categoria não tem produtos, pode fazer hard delete
        // Excluir a imagem primeiro se existir
        if (!empty($category['image_path']) && file_exists($category['image_path'])) {
            unlink($category['image_path']);
        }
        
        // Excluir a categoria
        $stmt = $pdo->prepare("DELETE FROM product_categories WHERE id = ?");
        $stmt->execute([$category_id]);
        
        $_SESSION['success_message'] = 'Categoria excluída com sucesso (exclusão permanente)';
    }
    
    // Registrar atividade se a função existir
    if (function_exists('log_admin_activity')) {
        log_admin_activity('category_deleted', 'Categoria excluída: ' . $category['name']);
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro no banco de dados ao excluir categoria.';
    error_log('Erro ao excluir categoria: ' . $e->getMessage());
}

// Redirecionar de volta para a lista de categorias
header('Location: /index.php/admin/categories');
exit;