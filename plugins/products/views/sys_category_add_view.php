<?php
// Include any components needed
if (file_exists('plugins/admin/components/alerts.php')) {
    include_once 'plugins/admin/components/alerts.php';
}

// Enforce admin login if function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}
?>

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
            <form action="/index.php/admin/categories/add" method="post" enctype="multipart/form-data">
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

                <!-- Dynamic Attributes Section -->
                <div class="mt-4">
                    <h5 class="card-title">Additional Attributes</h5>
                    <p class="text-muted">Add key-value pairs for extra category properties (like sizes, colors, etc.)</p>

                    <div id="attributes-container" class="mb-3">
                        <!-- Attributes will be added here dynamically -->
                    </div>

                    <button type="button" id="add-attribute-btn" class="btn btn-secondary mb-3">
                        <i class="fas fa-plus-circle me-2"></i> Add Attribute
                    </button>

                    <!-- Example attributes for quick addition -->
                    <!-- Example attributes for quick addition -->
                    <div class="example-attributes mb-3">
                        <span class="form-text mb-2">Quick add common attributes:</span>
                        <!-- Tamanhos com descrições individuais -->
                        <button type="button" class="btn btn-outline-secondary btn-sm example-item"
                            data-keys="S,M,L,XL"
                            data-values="Small,Medium,Large,Extra Large">Sizes</button>

                        <!-- Cores com descrições individuais -->
                        <button type="button" class="btn btn-outline-secondary btn-sm example-item"
                            data-keys="RED,BLU,GRN,BLK,WHT"
                            data-values="Red,Blue,Green,Black,White">Colors</button>

                        <!-- Materiais com descrições individuais -->
                        <button type="button" class="btn btn-outline-secondary btn-sm example-item"
                            data-keys="CTN,PLY,LTH,NYL"
                            data-values="Cotton,Polyester,Leather,Nylon">Materials</button>

                        <!-- Faixa etária com descrições individuais -->
                        <button type="button" class="btn btn-outline-secondary btn-sm example-item"
                            data-keys="PUP,ADT,SNR"
                            data-values="Puppy,Adult,Senior">Age Groups</button>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
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
    document.addEventListener('DOMContentLoaded', function() {
        // Setup image preview
        const imageInput = document.getElementById('image');
        const imagePreviewContainer = document.querySelector('.image-preview-container');
        const imagePreview = document.getElementById('imagePreview');

        if (imageInput) {
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
        }
        // Setup dynamic attributes
        const addAttributeBtn = document.getElementById('add-attribute-btn');
        const attributesContainer = document.getElementById('attributes-container');

        if (addAttributeBtn) {
            addAttributeBtn.addEventListener('click', function() {
                addAttributeRow();
            });
        }

        // Example attributes quick add buttons
        // Example attributes quick add buttons
        const exampleItems = document.querySelectorAll('.example-item');

        if (exampleItems.length > 0) {
            exampleItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Agora trabalhamos com arrays de chaves e valores
                    const keys = this.dataset.keys.split(',');
                    const values = this.dataset.values.split(',');

                    // Adicionamos um par para cada chave/valor
                    for (let i = 0; i < keys.length; i++) {
                        if (keys[i] && values[i]) {
                            addAttributeRow(keys[i], values[i]);
                        }
                    }
                });
            });
        }

        /**
         * Add a new attribute row with optional key/value
         */
        function addAttributeRow(key = '', value = '') {
            if (!attributesContainer) return;

            const row = document.createElement('div');
            row.className = 'attribute-row row mb-2';

            row.innerHTML = `
            <div class="col-md-5">
                <input type="text" class="form-control" name="attr_key[]" value="${escapeHtml(key)}" placeholder="Key (e.g. sizes)">
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control" name="attr_value[]" value="${escapeHtml(value)}" placeholder="Value (e.g. XS,S,M,L,XL)">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-attribute-btn">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
        `;

            attributesContainer.appendChild(row);

            // Add event to remove button
            const removeBtn = row.querySelector('.remove-attribute-btn');
            removeBtn.addEventListener('click', function() {
                attributesContainer.removeChild(row);
            });
        }

        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(unsafe) {
            if (typeof unsafe !== 'string') return '';
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>

<style>
    /* Custom styles for attributes section */
    .example-attributes {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        align-items: center;
    }

    .example-item {
        margin-left: 5px;
        font-size: 0.8rem;
    }

    .attribute-row {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .remove-attribute-btn {
        padding: 0.375rem 0.75rem;
    }

    @media (max-width: 767px) {
        .attribute-row {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .attribute-row>div {
            width: 100%;
        }
    }
</style>