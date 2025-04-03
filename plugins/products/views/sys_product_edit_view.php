<?php
// Include any components needed
if (file_exists('plugins/admin/components/alerts.php')) {
    include_once 'plugins/admin/components/alerts.php';
}

// Enforce admin login if function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Check if we have a product to edit
if (empty($edit_product)) {
    // No product to edit, handle error
    $_SESSION['error_message'] = 'Product not found or invalid ID';
    header('Location: /index.php/admin/products');
    exit;
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Edit Product</h1>
        <p class="text-muted">Editing: <?php echo htmlspecialchars($edit_product['name']); ?></p>
    </div>

    <!-- Display alerts if available -->
    <?php if (function_exists('show_session_alerts')) show_session_alerts(); ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Edit Product Form -->
    <form action="/index.php/admin/products/edit/id/<?php echo $edit_product['id']; ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="edit_product">

        <div class="row">
            <div class="col-md-8">
                <!-- Main Product Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Product Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($edit_product['name']); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a product name.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sku" name="sku" 
                                   value="<?php echo htmlspecialchars($edit_product['sku']); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a unique SKU.
                            </div>
                            <div class="form-text">
                                A unique identifier for your product (Stock Keeping Unit)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="short_description" class="form-label">Short Description</label>
                            <textarea class="form-control" id="short_description" name="short_description" rows="2"><?php echo htmlspecialchars($edit_product['short_description'] ?? ''); ?></textarea>
                            <div class="form-text">
                                A brief summary displayed in product listings (maximum 500 characters)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Full Description</label>
                            <textarea class="form-control" id="description" name="description" rows="8"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                            <div class="form-text">
                                Detailed information about the product, features, and benefits
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Images -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Product Images</h5>
                    </div>
                    <div class="card-body">
                        <!-- Existing Images -->
                        <?php if (!empty($product_images)): ?>
                            <h6 class="mb-3">Current Images</h6>
                            <div id="existing-images" class="row mb-4">
                                <?php foreach ($product_images as $image): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 <?php echo $image['is_primary'] ? 'border-primary' : ''; ?>">
                                            <div class="card-header d-flex justify-content-between align-items-center <?php echo $image['is_primary'] ? 'bg-primary text-white' : ''; ?>">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="primary_image_id" 
                                                          value="<?php echo $image['id']; ?>" 
                                                          <?php echo $image['is_primary'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">Primary</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="delete_image[]" 
                                                          value="<?php echo $image['id']; ?>">
                                                    <label class="form-check-label text-danger">Delete</label>
                                                </div>
                                            </div>
                                            <?php if (file_exists($image['image_path'])): ?>
                                                <img src="/<?php echo htmlspecialchars($image['image_path']); ?>" class="card-img-top" 
                                                     alt="<?php echo htmlspecialchars($image['alt_text'] ?? ''); ?>" 
                                                     style="height: 150px; object-fit: contain; padding: 10px;">
                                            <?php else: ?>
                                                <div class="text-center p-3 bg-light">
                                                    <span class="text-muted">Image not found</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <div class="mb-2">
                                                    <label class="form-label small">Alt Text</label>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           name="image_alt[<?php echo $image['id']; ?>]" 
                                                           value="<?php echo htmlspecialchars($image['alt_text'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <p>No images attached to this product. Please add at least one image.</p>
                            </div>
                        <?php endif; ?>

                        <!-- Add New Images -->
                        <h6 class="mb-3">Add New Images</h6>
                        <div id="new-image-upload-container">
                            <div class="image-upload-item mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="file" class="form-control" name="new_images[]" accept="image/*">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="new_image_alt[]" placeholder="Alt Text">
                                    </div>
                                </div>
                                <div class="image-preview-container mt-2" style="display: none;">
                                    <div class="image-preview border rounded p-2">
                                        <img src="#" alt="Preview" style="max-width: 100%; max-height: 200px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-image" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-plus-circle"></i> Add Another Image
                        </button>
                        <div class="form-text mt-2">
                            Upload additional images for your product. Images should be high quality (at least 800x800 pixels).
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Product Status & Categories -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Status & Organization</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Product Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" <?php echo ($edit_product['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($edit_product['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                <option value="out_of_stock" <?php echo ($edit_product['status'] == 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                                <option value="discontinued" <?php echo ($edit_product['status'] == 'discontinued') ? 'selected' : ''; ?>>Discontinued</option>
                            </select>
                            <div class="form-text">
                                Controls visibility and availability of the product
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Featured Product</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                       <?php echo ($edit_product['is_featured']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">Mark as featured</label>
                            </div>
                            <div class="form-text">
                                Featured products appear in highlighted sections of your store
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Product</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_new" name="is_new" 
                                       <?php echo ($edit_product['is_new']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_new">Mark as new</label>
                            </div>
                            <div class="form-text">
                                New products can be filtered and highlighted with a "New" badge
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Categories</label>
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                <!-- Parent Categories -->
                                <?php foreach ($categories as $parent_category): ?>
                                    <div class="mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="categories[]" 
                                                   id="category-<?php echo $parent_category['id']; ?>" 
                                                   value="<?php echo $parent_category['id']; ?>"
                                                   <?php echo (in_array($parent_category['id'], $product_categories)) ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-semibold" for="category-<?php echo $parent_category['id']; ?>">
                                                <?php echo htmlspecialchars($parent_category['name']); ?>
                                            </label>
                                        </div>
                                        
                                        <!-- Child Categories -->
                                        <?php if (!empty($parent_category['children'])): ?>
                                            <div class="ms-4">
                                                <?php foreach ($parent_category['children'] as $child): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="categories[]" 
                                                               id="category-<?php echo $child['id']; ?>" 
                                                               value="<?php echo $child['id']; ?>"
                                                               <?php echo (in_array($child['id'], $product_categories)) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="category-<?php echo $child['id']; ?>">
                                                            <?php echo htmlspecialchars($child['name']); ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (empty($categories)): ?>
                                    <div class="text-center text-muted">
                                        <p>No categories found</p>
                                        <a href="/index.php/admin/categories/add" class="btn btn-sm btn-outline-primary">
                                            Add Category
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">
                                Assign your product to one or more categories
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Inventory -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Pricing & Inventory</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (€) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" 
                                       value="<?php echo number_format($edit_product['price'], 2, '.', ''); ?>" required>
                                <div class="invalid-feedback">
                                    Please provide a valid price.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="regular_price" class="form-label">Regular Price (€)</label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" id="regular_price" name="regular_price" step="0.01" min="0"
                                       value="<?php echo isset($edit_product['regular_price']) ? number_format($edit_product['regular_price'], 2, '.', '') : ''; ?>">
                            </div>
                            <div class="form-text">
                                Original price before discount (leave blank if not on sale)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sale_price" class="form-label">Sale Price (€)</label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0"
                                       value="<?php echo isset($edit_product['sale_price']) ? number_format($edit_product['sale_price'], 2, '.', '') : ''; ?>">
                            </div>
                            <div class="form-text">
                                Discounted price (leave blank if not on sale)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" 
                                   value="<?php echo (int)$edit_product['stock_quantity']; ?>">
                            <div class="form-text">
                                Number of units available for purchase
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="weight" name="weight" step="0.01" min="0"
                                       value="<?php echo isset($edit_product['weight']) ? number_format($edit_product['weight'], 2, '.', '') : ''; ?>">
                                <span class="input-group-text">kg</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dimensions (cm)</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group mb-2">
                                        <input type="number" class="form-control" id="dimensions_length" 
                                               name="dimensions_length" step="0.1" min="0" placeholder="Length"
                                               value="<?php echo isset($edit_product['dimensions_length']) ? 
                                                       number_format($edit_product['dimensions_length'], 1, '.', '') : ''; ?>">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group mb-2">
                                        <input type="number" class="form-control" id="dimensions_width" 
                                               name="dimensions_width" step="0.1" min="0" placeholder="Width"
                                               value="<?php echo isset($edit_product['dimensions_width']) ? 
                                                       number_format($edit_product['dimensions_width'], 1, '.', '') : ''; ?>">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group mb-2">
                                        <input type="number" class="form-control" id="dimensions_height" 
                                               name="dimensions_height" step="0.1" min="0" placeholder="Height"
                                               value="<?php echo isset($edit_product['dimensions_height']) ? 
                                                       number_format($edit_product['dimensions_height'], 1, '.', '') : ''; ?>">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Submit Buttons -->
        <div class="d-flex justify-content-between mb-4">
            <a href="/index.php/admin/products" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Products
            </a>
            <div>
                <button type="submit" name="save_draft" value="1" class="btn btn-outline-primary me-2">
                    <i class="fas fa-save me-2"></i> Save as Draft
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check-circle me-2"></i> Update Product
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.querySelector('.needs-validation');
        
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
        
        // Save as Draft button functionality
        const saveAsDraftBtn = document.querySelector('button[name="save_draft"]');
        if (saveAsDraftBtn) {
            saveAsDraftBtn.addEventListener('click', function() {
                document.getElementById('status').value = 'draft';
            });
        }
        
        // Product Images Preview
        const setupImagePreview = function(fileInput, previewContainer) {
            fileInput.addEventListener('change', function() {
                const preview = previewContainer.querySelector('img');
                
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        previewContainer.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                } else {
                    previewContainer.style.display = 'none';
                }
            });
        };
        
        // Setup initial image preview
        const firstFileInput = document.querySelector('input[name="new_images[]"]');
        const firstPreviewContainer = document.querySelector('.image-preview-container');
        if (firstFileInput && firstPreviewContainer) {
            setupImagePreview(firstFileInput, firstPreviewContainer);
        }
        
        // Add More Images Button
        let imageCounter = 1;
        const addImageBtn = document.getElementById('add-image');
        const imageContainer = document.getElementById('new-image-upload-container');
        
        if (addImageBtn && imageContainer) {
            addImageBtn.addEventListener('click', function() {
                const newItem = document.createElement('div');
                newItem.className = 'image-upload-item mb-3';
                newItem.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <input type="file" class="form-control" name="new_images[]" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="new_image_alt[]" placeholder="Alt Text">
                        </div>
                    </div>
                    <div class="image-preview-container mt-2" style="display: none;">
                        <div class="image-preview border rounded p-2">
                            <img src="#" alt="Preview" style="max-width: 100%; max-height: 200px;">
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm mt-2 remove-image">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                `;
                
                imageContainer.appendChild(newItem);
                
                // Setup preview for the new image
                const newFileInput = newItem.querySelector('input[type="file"]');
                const newPreviewContainer = newItem.querySelector('.image-preview-container');
                setupImagePreview(newFileInput, newPreviewContainer);
                
                // Setup remove button
                const removeBtn = newItem.querySelector('.remove-image');
                removeBtn.addEventListener('click', function() {
                    imageContainer.removeChild(newItem);
                });
                
                imageCounter++;
            });
        }
        
        // Confirm image deletion
        const confirmImageDeletion = function() {
            const deleteCheckboxes = document.querySelectorAll('input[name="delete_image[]"]');
            
            deleteCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        const imageCard = this.closest('.card');
                        if (imageCard) {
                            imageCard.classList.add('border-danger');
                            imageCard.style.opacity = '0.7';
                        }
                    } else {
                        const imageCard = this.closest('.card');
                        if (imageCard) {
                            imageCard.classList.remove('border-danger');
                            imageCard.style.opacity = '1';
                        }
                    }
                });
            });
        };
        
        // Initialize confirmation for image deletion
        confirmImageDeletion();
        
        // Highlight primary image card when radio is changed
        const primaryRadios = document.querySelectorAll('input[name="primary_image_id"]');
        
        primaryRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove highlighting from all cards
                document.querySelectorAll('#existing-images .card').forEach(card => {
                    card.classList.remove('border-primary');
                    card.querySelector('.card-header').classList.remove('bg-primary', 'text-white');
                });
                
                // Add highlighting to selected card
                if (this.checked) {
                    const card = this.closest('.card');
                    if (card) {
                        card.classList.add('border-primary');
                        card.querySelector('.card-header').classList.add('bg-primary', 'text-white');
                    }
                }
            });
        });
    });
</script>

<style>
    /* Custom styles for the form */
    .form-label {
        font-weight: 500;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        background-color: #f8f9fa;
    }
    
    .image-preview {
        background-color: #f8f9fa;
        text-align: center;
    }
    
    .remove-image {
        margin-bottom: 10px;
    }
    
    /* Hide number input spinners */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>