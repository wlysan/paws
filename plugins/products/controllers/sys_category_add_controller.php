<?php
/**
 * Admin Category Controller - Add
 * 
 * Handles adding new product categories
 */

// Require admin login if the function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Inicializar variáveis globais para a view
global $categories, $success_message, $error_message;

$success_message = '';
$error_message = '';

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
    $error_message = 'Erro ao carregar categorias principais.';
    error_log('Erro ao carregar categorias principais: ' . $e->getMessage());
}

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log para depuração
    error_log('Formulário de adição de categoria recebido: ' . print_r($_POST, true));
    
    // Verificar se a ação do formulário está correta
    if (isset($_POST['action']) && $_POST['action'] === 'add_category') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : 'active';
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        
        // Log para depuração
        error_log('Dados extraídos: Nome=' . $name . ', Status=' . $status);
        
        // Validar entrada
        if (empty($name)) {
            $error_message = 'O nome da categoria é obrigatório';
            error_log('Validação falhou: Nome vazio');
        } else {
            try {
                error_log('Iniciando inserção no banco de dados');
                $pdo = getConnection();
                
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
                    error_log('Processando imagem carregada');
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
                        $image_path = $upload_path;
                        error_log('Imagem carregada com sucesso: ' . $image_path);
                    } else {
                        error_log('Falha ao mover arquivo carregado');
                    }
                }
                
                // Inserir categoria
                error_log('Executando INSERT no banco de dados');
                $stmt = $pdo->prepare("
                    INSERT INTO product_categories (
                        name, 
                        slug, 
                        description, 
                        parent_id, 
                        image_path, 
                        display_order, 
                        status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $params = [
                    $name,
                    $slug,
                    $description,
                    $parent_id,
                    $image_path,
                    $display_order,
                    $status
                ];
                
                error_log('Parâmetros do INSERT: ' . print_r($params, true));
                $stmt->execute($params);
                
                // Verificar se a inserção foi bem-sucedida
                $new_category_id = $pdo->lastInsertId();
                error_log('Nova categoria ID: ' . $new_category_id);
                
                if ($new_category_id) {
                    // Registrar atividade se a função existir
                    if (function_exists('log_admin_activity')) {
                        log_admin_activity('category_created', 'Categoria criada: ' . $name);
                    }
                    
                    // Definir mensagem de sucesso e redirecionar
                    $_SESSION['success_message'] = 'Categoria adicionada com sucesso';
                    error_log('Categoria adicionada com sucesso. Redirecionando...');
                    header('Location: /index.php/admin/categories');
                    exit;
                } else {
                    $error_message = 'Falha ao adicionar categoria.';
                    error_log('Nenhum ID retornado após a inserção');
                }
                
            } catch (PDOException $e) {
                $error_message = 'Erro no banco de dados. Por favor, tente novamente mais tarde.';
                error_log('Erro ao adicionar categoria: ' . $e->getMessage());
            }
        }
    } else {
        error_log('Ação do formulário não é "add_category": ' . ($_POST['action'] ?? 'não definida'));
    }
}