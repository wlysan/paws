<?php
/**
 * Admin Category Controller - Add
 * 
 * Handles adding new product categories with support for dynamic attributes
 */

// Require admin login if the function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Inicializar variáveis globais para a view
global $categories, $success_message, $error_message;

$success_message = '';
$error_message = '';

// Add detailed logging for debugging
error_log('Category Add Controller loaded');
error_log('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
error_log('POST data: ' . print_r($_POST, true));

// Carregar categorias principais para o formulário
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT id, name FROM product_categories 
        WHERE parent_id IS NULL AND is_deleted = 0
        ORDER BY name ASC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Error loading parent categories.';
    error_log('Error loading parent categories: ' . $e->getMessage());
}

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log para depuração
    error_log('Processing category add form submission');
    
    // Extract form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : 'active';
    $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
    
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
    
    // Log para depuração
    error_log('Form data: Name=' . $name . ', Status=' . $status . ', Parent_ID=' . ($parent_id ?? 'NULL'));
    error_log('Attributes: ' . print_r($attributes, true));
    
    // Validar entrada
    if (empty($name)) {
        $error_message = 'Category name is required';
        error_log('Validation failed: Empty name');
    } else {
        try {
            error_log('Starting database insertion');
            $pdo = getConnection();
            
            // Verificar se a tabela existe, criá-la se necessário
            $stmt = $pdo->prepare("
                SHOW TABLES LIKE 'product_categories'
            ");
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                error_log('Table product_categories not found, creating automatically');
                
                $sql = "
                CREATE TABLE product_categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    slug VARCHAR(100) NOT NULL UNIQUE,
                    description TEXT,
                    parent_id INT NULL,
                    image_path VARCHAR(255),
                    display_order INT DEFAULT 0,
                    attributes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                    status ENUM('active', 'inactive', 'featured') NOT NULL DEFAULT 'active',
                    is_deleted BOOLEAN DEFAULT FALSE,
                    deleted_at TIMESTAMP NULL,
                    FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ";
                
                $pdo->exec($sql);
                error_log('Table product_categories created successfully');
            }
            
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
            
            // Gerar slug
            $slug = generate_slug($name);
            $original_slug = $slug;
            
            // Verificar se o slug existe e adicionar sufixo se necessário
            $counter = 1;
            while (category_slug_exists($slug)) {
                $slug = $original_slug . '-' . $counter;
                $counter++;
            }
            
            // Processar imagem carregada, se presente
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                error_log('Processing uploaded image');
                $upload_dir = 'uploads/categories/';
                
                // Criar diretório se não existir
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                    error_log('Created upload directory: ' . $upload_dir);
                }
                
                $file_tmp = $_FILES['image']['tmp_name'];
                $file_name = basename($_FILES['image']['name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Gerar nome de arquivo único
                $unique_filename = $slug . '-' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $unique_filename;
                
                // Mover arquivo carregado
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image_path = $upload_path;
                    error_log('Image uploaded successfully: ' . $image_path);
                } else {
                    error_log('Failed to move uploaded file from ' . $file_tmp . ' to ' . $upload_path);
                    error_log('Upload error code: ' . $_FILES['image']['error']);
                }
            }
            
            // Inserir categoria com suporte a atributos
            error_log('Executing INSERT query with attributes');
            $stmt = $pdo->prepare("
                INSERT INTO product_categories (
                    name, 
                    slug, 
                    description, 
                    parent_id, 
                    image_path, 
                    display_order,
                    attributes,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $params = [
                $name,
                $slug,
                $description,
                $parent_id,
                $image_path,
                $display_order,
                $serialized_attributes,
                $status
            ];
            
            error_log('INSERT parameters: ' . print_r($params, true));
            $stmt->execute($params);
            
            // Verificar se a inserção foi bem-sucedida
            $new_category_id = $pdo->lastInsertId();
            error_log('New category ID: ' . $new_category_id);
            
            if ($new_category_id) {
                // Registrar atividade se a função existir
                if (function_exists('log_admin_activity')) {
                    log_admin_activity('category_created', 'Category created: ' . $name);
                }
                
                // Definir mensagem de sucesso e redirecionar
                $_SESSION['success_message'] = 'Category added successfully';
                error_log('Category added successfully. Redirecting...');
                header('Location: /index.php/admin/categories');
                exit;
            } else {
                $error_message = 'Failed to add category.';
                error_log('No ID returned after insertion');
            }
            
        } catch (PDOException $e) {
            $error_message = 'Database error. Please try again later.';
            error_log('Error adding category: ' . $e->getMessage());
        }
    }
}