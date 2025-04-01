<?php

// Include any components needed
if (file_exists('plugins/admin/components/alerts.php')) {
    include_once 'plugins/admin/components/alerts.php';
}

// Enforce admin login if function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Verificar se a tabela product_categories existe
try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SHOW TABLES LIKE 'product_categories'
    ");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // A tabela não existe, criar automaticamente
        error_log('Tabela product_categories não encontrada, criando automaticamente');
        
        $sql = "
        CREATE TABLE product_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            parent_id INT NULL,
            image_path VARCHAR(255),
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            status ENUM('active', 'inactive', 'featured') NOT NULL DEFAULT 'active',
            is_deleted BOOLEAN DEFAULT FALSE,
            deleted_at TIMESTAMP NULL,
            FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($sql);
        error_log('Tabela product_categories criada com sucesso');
    } else {
        error_log('Tabela product_categories já existe');
    }
} catch (PDOException $e) {
    error_log('Erro ao verificar/criar tabela product_categories: ' . $e->getMessage());
}

print_r(get_route());
print_r(get_parameters());

?>
<!-- Depuração do formulário -->
<div>
    <p>URL do formulário: <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></p>
    <p>Método do formulário: POST</p>
    <p>Contém enctype: multipart/form-data</p>
</div>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Add New Category</h1>
        <p class="text-muted">Create a new product category</p>
    </div>
    
    <!-- Display alerts if available -->
    <?php if (function_exists('show_session_alerts')) show_session_alerts(); ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Add Category Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Category Details</h5>
        </div>
        <div class="card-body">
            <form action="/index.php/admin/categories/add" method="post" enctype="multipart/form-data
                <input type="hidden" name="action" value="add_category">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="form-text">The name will appear on your site</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Category</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">None (Top Level Category)</option>
                                <?php foreach ($categories as $category): ?>
                                    <?php if (empty($category['parent_id'])): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Categories can be nested for better organization</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="featured">Featured</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="0" min="0">
                            <div class="form-text">Categories with lower numbers will appear first</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                            <div class="form-text">A brief description of the category (optional)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Category Image</label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <label class="input-group-text" for="image">Upload</label>
                            </div>
                            <div class="form-text">Recommended size: 500x500 pixels</div>
                        </div>
                        
                        <div class="image-preview-container mb-3" style="display:none;">
                            <label class="form-label">Image Preview</label>
                            <div class="image-preview">
                                <img id="imagePreview" src="#" alt="Preview" style="max-width: 100%; max-height: 200px;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="/index.php/admin/categories" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Categories
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript for image preview
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image');
    const imagePreviewContainer = document.querySelector('.image-preview-container');
    const imagePreview = document.getElementById('imagePreview');
    
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.style.display = 'block';
            }
            
            reader.readAsDataURL(this.files[0]);
        } else {
            imagePreviewContainer.style.display = 'none';
        }
    });
});
</script>