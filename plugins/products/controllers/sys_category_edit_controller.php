<?php
/**
 * Admin Category Controller - Edit
 * 
 * Handles editing existing product categories
 */

// Require admin login if the function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Inicializar variáveis globais para a view
global $categories, $edit_category, $success_message, $error_message;

$success_message = '';
$error_message = '';

// Obter ID da categoria a ser editada
$params = get_parameters();
$category_id = isset($params['id']) ? (int)$params['id'] : 0;

if ($category_id <= 0) {
    $_SESSION['error_message'] = 'ID de categoria inválido';
    header('Location: /index.php/admin/categories');
    exit;
}

// Carregar dados da categoria
try {
    $pdo = getConnection();
    
    // Obter categoria
    $stmt = $pdo->prepare("
        SELECT * FROM product_categories WHERE id = ? AND is_deleted = 0
    ");
    $stmt->execute([$category_id]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$edit_category) {
        $_SESSION['error_message'] = 'Categoria não encontrada';
        header('Location: /index.php/admin/categories');
        exit;
    }
    
    // Carregar categorias principais para o formulário (excluindo a categoria atual e seus filhos)
    $stmt = $pdo->prepare("
        SELECT id, name FROM product_categories 
        WHERE parent_id IS NULL 
        AND id != ? 
        AND is_deleted = 0
        ORDER BY name ASC
    ");
    $stmt->execute([$category_id]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao carregar categoria para edição';
    header('Location: /index.php/admin/categories');
    exit;
}

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicione um log para depuração
    error_log('Processando formulário de edição para categoria ID: ' . $category_id);
    error_log('POST data: ' . print_r($_POST, true));
    
    // Verificar se a ação do formulário está correta
    if (isset($_POST['action']) && $_POST['action'] === 'edit_category') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : 'active';
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        $current_image = isset($_POST['current_image']) ? $_POST['current_image'] : '';
        
        // Adicione mais logs para depuração
        error_log('Dados do formulário: Nome=' . $name . ', Status=' . $status);
        
        // Validar entrada
        if (empty($name)) {
            $error_message = 'O nome da categoria é obrigatório';
            error_log('Erro: Nome de categoria vazio');
        } else if ($parent_id == $category_id) {
            $error_message = 'Uma categoria não pode ser sua própria categoria pai';
            error_log('Erro: Categoria não pode ser sua própria pai');
        } else {
            try {
                error_log('Iniciando atualização no banco de dados');
                $pdo = getConnection();
                
                // Gerar slug se o nome mudou
                $slug = $edit_category['slug'];
                if ($name != $edit_category['name']) {
                    $slug = generate_slug($name);
                    $original_slug = $slug;
                    
                    // Verificar se o slug existe e adicionar sufixo se necessário
                    $counter = 1;
                    while (category_slug_exists($slug, $category_id)) {
                        $slug = $original_slug . '-' . $counter;
                        $counter++;
                    }
                }
                
                // Processar imagem carregada, se presente
                $image_path = $edit_category['image_path'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/categories/';
                    
                    // Criar diretório se não existir
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_tmp = $_FILES['image']['tmp_name'];
                    $file_name = basename($_FILES['image']['name']);
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Gerar nome de arquivo único
                    $unique_filename = $slug . '-' . uniqid() . '.' . $file_ext;
                    $upload_path = $upload_dir . $unique_filename;
                    
                    // Mover arquivo carregado
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        // Excluir imagem antiga se existir
                        if (!empty($image_path) && file_exists($image_path)) {
                            unlink($image_path);
                        }
                        $image_path = $upload_path;
                    }
                } else if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
                    // Remover imagem se solicitado
                    if (!empty($image_path) && file_exists($image_path)) {
                        unlink($image_path);
                    }
                    $image_path = '';
                }
                
                // Atualizar categoria
                error_log('Executando UPDATE no banco de dados');
                $stmt = $pdo->prepare("
                    UPDATE product_categories 
                    SET name = ?, 
                        slug = ?, 
                        description = ?, 
                        parent_id = ?, 
                        image_path = ?, 
                        display_order = ?, 
                        status = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                
                $params = [
                    $name,
                    $slug,
                    $description,
                    $parent_id,
                    $image_path,
                    $display_order,
                    $status,
                    $category_id
                ];
                
                error_log('Parâmetros do UPDATE: ' . print_r($params, true));
                $stmt->execute($params);
                
                // Verificar se a atualização foi bem-sucedida
                $affected_rows = $stmt->rowCount();
                error_log('Linhas afetadas pelo UPDATE: ' . $affected_rows);
                
                if ($affected_rows > 0) {
                    // Registrar atividade se a função existir
                    if (function_exists('log_admin_activity')) {
                        log_admin_activity('category_updated', 'Categoria atualizada: ' . $name);
                    }
                    
                    // Definir mensagem de sucesso e redirecionar
                    $_SESSION['success_message'] = 'Categoria atualizada com sucesso';
                    error_log('Categoria atualizada com sucesso. Redirecionando...');
                    header('Location: /index.php/admin/categories');
                    exit;
                } else {
                    $error_message = 'Nenhuma alteração foi feita na categoria.';
                    error_log('Nenhuma linha foi atualizada no banco de dados');
                }
                
            } catch (PDOException $e) {
                $error_message = 'Erro no banco de dados. Por favor, tente novamente mais tarde.';
                error_log('Erro ao atualizar categoria: ' . $e->getMessage());
            }
        }
    } else {
        error_log('Ação do formulário não é "edit_category": ' . ($_POST['action'] ?? 'não definida'));
    }
}