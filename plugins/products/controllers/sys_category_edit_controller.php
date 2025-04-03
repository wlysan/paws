<?php
/**
 * Admin Category Controller - Edit
 * 
 * Handles editing existing product categories with dynamic attributes
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
    $_SESSION['error_message'] = 'Invalid category ID';
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
        $_SESSION['error_message'] = 'Category not found';
        header('Location: /index.php/admin/categories');
        exit;
    }
    
    // Deserializar atributos se existirem
    if (!empty($edit_category['attributes'])) {
        try {
            $attributes = unserialize($edit_category['attributes']);
            if ($attributes !== false) {
                $edit_category['attributes_array'] = $attributes;
            } else {
                $edit_category['attributes_array'] = [];
            }
        } catch (Exception $e) {
            error_log('Error unserializing attributes: ' . $e->getMessage());
            $edit_category['attributes_array'] = [];
        }
    } else {
        $edit_category['attributes_array'] = [];
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
    $_SESSION['error_message'] = 'Error loading category for editing';
    error_log('Error loading category for editing: ' . $e->getMessage());
    header('Location: /index.php/admin/categories');
    exit;
}

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicione um log para depuração
    error_log('Processing edit form for category ID: ' . $category_id);
    error_log('POST data: ' . print_r($_POST, true));
    
    // Extract form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : 'active';
    $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
    
    // Evitar que a categoria seja seu próprio pai
    if ($parent_id == $category_id) {
        $parent_id = null;
        error_log('Attempt to set category as its own parent, resetting to null');
    }
    
    // Processar atributos dinâmicos (se existirem)
    $attributes = [];
    if (isset($_POST['attr_key']) && isset($_POST['attr_value'])) {
        $keys = $_POST['attr_key'];
        $values = $_POST['attr_value'];
        
        for ($i = 0; $i < count($keys); $i++) {
            if (!empty($keys[$i])) {
                $attributes[$keys[$i]] = $values[$i] ?? '';
            }
        }
    }
    
    // Serializar atributos para armazenamento
    $serialized_attributes = !empty($attributes) ? serialize($attributes) : null;
    
    // Adicione mais logs para depuração
    error_log('Form data: Name=' . $name . ', Status=' . $status . ', Parent_ID=' . ($parent_id ?? 'NULL'));
    error_log('Attributes: ' . print_r($attributes, true));
    
    // Validar entrada
    if (empty($name)) {
        $error_message = 'Category name is required';
        error_log('Validation failed: Empty name');
    } else {
        try {
            error_log('Starting database update');
            $pdo = getConnection();
            
            // Verificar se a coluna attributes existe, adicioná-la se necessário
            $stmt = $pdo->prepare("
                SHOW COLUMNS FROM product_categories LIKE 'attributes'
            ");
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                error_log('Column attributes not found, adding it');
                
                $sql = "ALTER TABLE product_categories ADD COLUMN attributes TEXT AFTER display_order";
                $pdo->exec($sql);
                error_log('Column attributes added successfully');
            }
            
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
            
            // Atualizar categoria com suporte a atributos
            error_log('Executing UPDATE query with attributes');
            $stmt = $pdo->prepare("
                UPDATE product_categories 
                SET name = ?, 
                    slug = ?, 
                    description = ?, 
                    parent_id = ?, 
                    image_path = ?, 
                    display_order = ?, 
                    attributes = ?,
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
                $serialized_attributes,
                $status,
                $category_id
            ];
            
            error_log('UPDATE parameters: ' . print_r($params, true));
            $stmt->execute($params);
            
            // Verificar se a atualização foi bem-sucedida
            $affected_rows = $stmt->rowCount();
            error_log('Rows affected by UPDATE: ' . $affected_rows);
            
            // Mesmo que nenhuma linha seja afetada (porque os dados podem ser os mesmos),
            // consideramos como sucesso se não houve erros
            // Registrar atividade se a função existir
            if (function_exists('log_admin_activity')) {
                log_admin_activity('category_updated', 'Category updated: ' . $name);
            }
            
            // Definir mensagem de sucesso e redirecionar
            $_SESSION['success_message'] = 'Category updated successfully';
            error_log('Category updated successfully. Redirecting...');
            header('Location: /index.php/admin/categories');
            exit;
            
        } catch (PDOException $e) {
            $error_message = 'Database error. Please try again later.';
            error_log('Error updating category: ' . $e->getMessage());
        }
    }
    
    // Se chegou aqui, significa que houve algum erro
    // Atualiza os dados da categoria com os valores enviados para preencher o formulário novamente
    $edit_category = [
        'id' => $category_id,
        'name' => $name,
        'description' => $description,
        'parent_id' => $parent_id,
        'attributes' => $serialized_attributes,
        'attributes_array' => $attributes,
        'status' => $status,
        'display_order' => $display_order,
        'image_path' => $edit_category['image_path'] // Mantém o caminho da imagem original
    ];
}