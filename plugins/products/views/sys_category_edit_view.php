<?php
if (isset($edit_category)): ?>
    <!-- Depuração -->
    <div>
        <?php
        echo "ID da categoria: " . ($edit_category['id'] ?? 'não definido');
        echo "Nome da categoria: " . ($edit_category['name'] ?? 'não definido');
        ?>
    </div>
<?php else: ?>
    <div class="alert alert-danger">
        Dados da categoria não estão disponíveis. Por favor, volte e tente novamente.
    </div>
<?php endif;
// Include any components needed
if (file_exists('plugins/admin/components/alerts.php')) {
    include_once 'plugins/admin/components/alerts.php';
}

// Enforce admin login if function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Check if we have a category to edit
if (empty($edit_category)) {
    // No category to edit, handle error
    $_SESSION['error_message'] = 'Category not found or invalid ID';
    header('Location: /index.php/admin/categories');
    exit;
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Edit Category</h1>
        <p class="text-muted">Editing category: <?php echo htmlspecialchars($edit_category['name']); ?></p>
    </div>

    <!-- Display alerts if available -->
    <?php if (function_exists('show_session_alerts')) show_session_alerts(); ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Edit Category Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Category Details</h5>
        </div>
        <div class="card-body">
            <form action="/index.php/admin/categories/edit/id/<?php echo $edit_category['id']; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_category">
                <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($edit_category['image_path']); ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?php echo htmlspecialchars($edit_category['name']); ?>" required>
                            <div class="form-text">The name will appear on your site</div>
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Category</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">None (Top Level Category)</option>
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                    // Skip the current category and its children to prevent circular references
                                    if ($category['id'] == $edit_category['id']) {
                                        continue;
                                    }

                                    // Only show top level categories
                                    if (empty($category['parent_id'])):
                                    ?>
                                        <option value="<?php echo $category['id']; ?>"
                                            <?php echo ($edit_category['parent_id'] == $category['id']) ? 'selected' : ''; ?>>
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
                                <option value="active" <?php echo ($edit_category['status'] == 'active') ? 'selected' : ''; ?>>
                                    Active
                                </option>
                                <option value="inactive" <?php echo ($edit_category['status'] == 'inactive') ? 'selected' : ''; ?>>
                                    Inactive
                                </option>
                                <option value="featured" <?php echo ($edit_category['status'] == 'featured') ? 'selected' : ''; ?>>
                                    Featured
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order"
                                value="<?php echo (int)$edit_category['display_order']; ?>" min="0">
                            <div class="form-text">Categories with lower numbers will appear first</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5"><?php
                                                                                                        echo htmlspecialchars($edit_category['description']);
                                                                                                        ?></textarea>
                            <div class="form-text">A brief description of the category (optional)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category Image</label>
                            <?php if (!empty($edit_category['image_path']) && file_exists($edit_category['image_path'])): ?>
                                <div class="current-image mb-2">
                                    <img src="/<?php echo htmlspecialchars($edit_category['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($edit_category['name']); ?>"
                                        style="max-width: 100%; max-height: 200px;">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                    <label class="form-check-label" for="remove_image">
                                        Remove current image
                                    </label>
                                </div>
                            <?php endif; ?>

                            <div class="input-group">
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <label class="input-group-text" for="image">Upload New</label>
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
                        <i class="fas fa-save me-2"></i> Update Category
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
        const removeImageCheckbox = document.getElementById('remove_image');

        // Show/hide current image based on remove checkbox
        if (removeImageCheckbox) {
            removeImageCheckbox.addEventListener('change', function() {
                const currentImage = document.querySelector('.current-image');
                if (currentImage) {
                    if (this.checked) {
                        currentImage.style.opacity = '0.5';
                    } else {
                        currentImage.style.opacity = '1';
                    }
                }
            });
        }

        // Show preview for new image
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.style.display = 'block';

                    // If remove checkbox exists, uncheck it when new image is selected
                    if (removeImageCheckbox) {
                        removeImageCheckbox.checked = false;
                    }
                }

                reader.readAsDataURL(this.files[0]);
            } else {
                imagePreviewContainer.style.display = 'none';
            }
        });
    });
</script>