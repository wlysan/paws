/**
 * Category management JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    setupImagePreview();
    
    // Form validation
    setupFormValidation();
    
    // Auto-generate slug from name
    setupSlugGenerator();
    
    // Toggle functionality for subcategories in tree view
    setupCategoryTreeToggles();
});

/**
 * Set up image preview functionality
 */
function setupImagePreview() {
    const imageInput = document.getElementById('image');
    if (!imageInput) return;
    
    const imagePreviewContainer = document.querySelector('.image-preview-container');
    const imagePreview = document.getElementById('imagePreview');
    const removeImageCheckbox = document.getElementById('remove_image');
    
    // Show/hide current image based on remove checkbox
    if (removeImageCheckbox) {
        removeImageCheckbox.addEventListener('change', function() {
            const currentImage = document.querySelector('.current-image');
            if (currentImage) {
                currentImage.style.opacity = this.checked ? '0.5' : '1';
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
}

/**
 * Set up form validation
 */
function setupFormValidation() {
    const categoryForm = document.querySelector('form[action*="/admin/categories"]');
    if (!categoryForm) return;
    
    categoryForm.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Required field validation
        const nameInput = document.getElementById('name');
        if (!nameInput.value.trim()) {
            isValid = false;
            showError(nameInput, 'Category name is required');
        } else {
            clearError(nameInput);
        }
        
        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
        }
    });
}

/**
 * Show validation error for input
 */
function showError(input, message) {
    input.classList.add('is-invalid');
    
    let errorDiv = input.nextElementSibling;
    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
        errorDiv = document.createElement('div');
        errorDiv.classList.add('invalid-feedback');
        input.parentNode.insertBefore(errorDiv, input.nextSibling);
    }
    
    errorDiv.textContent = message;
}

/**
 * Clear validation error for input
 */
function clearError(input) {
    input.classList.remove('is-invalid');
    
    const errorDiv = input.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
        errorDiv.textContent = '';
    }
}

/**
 * Set up automatic slug generation from name
 */
function setupSlugGenerator() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    if (!nameInput || !slugInput) return;
    
    nameInput.addEventListener('input', function() {
        // Only generate slug if the field is empty or hasn't been manually edited
        if (!slugInput.dataset.manuallyEdited || slugInput.dataset.manuallyEdited !== 'true') {
            slugInput.value = generateSlug(this.value);
        }
    });
    
    // Mark slug as manually edited if user changes it
    slugInput.addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });
}

/**
 * Generate slug from text
 */
function generateSlug(text) {
    return text.toLowerCase()
        .replace(/[^\w ]+/g, '')
        .replace(/ +/g, '-');
}

/**
 * Set up category tree toggle functionality
 */
function setupCategoryTreeToggles() {
    const toggles = document.querySelectorAll('.category-tree-toggle');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const childrenContainer = document.querySelector(`.category-children[data-parent="${categoryId}"]`);
            
            if (childrenContainer) {
                const isExpanded = childrenContainer.style.display !== 'none';
                
                childrenContainer.style.display = isExpanded ? 'none' : 'block';
                
                // Update toggle icon
                const icon = this.querySelector('i');
                if (icon) {
                    if (isExpanded) {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-right');
                    } else {
                        icon.classList.remove('fa-chevron-right');
                        icon.classList.add('fa-chevron-down');
                    }
                }
            }
        });
    });
}

/**
 * Show confirmation modal
 */
function confirmDelete(categoryId, categoryName) {
    const modal = document.getElementById('deleteConfirmModal');
    if (!modal) return;
    
    const categoryNameElement = modal.querySelector('.category-name');
    if (categoryNameElement) {
        categoryNameElement.textContent = categoryName;
    }
    
    const confirmButton = modal.querySelector('.btn-danger');
    if (confirmButton) {
        confirmButton.onclick = function() {
            const form = document.getElementById('delete-form-' + categoryId);
            if (form) {
                form.submit();
            }
        };
    }
    
    // Show the modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

/**
 * Reorder categories via drag and drop
 */
function initCategoryReordering() {
    const categoryList = document.querySelector('.sortable-categories');
    if (!categoryList) return;
    
    // Initialize Sortable
    if (typeof Sortable !== 'undefined') {
        Sortable.create(categoryList, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                saveCategoryOrder();
            }
        });
    }
}

/**
 * Save category order after drag and drop
 */
function saveCategoryOrder() {
    const categoryItems = document.querySelectorAll('.category-item');
    const orderData = [];
    
    categoryItems.forEach((item, index) => {
        orderData.push({
            id: parseInt(item.dataset.categoryId),
            order: index
        });
    });
    
    // Send order data to server using fetch API
    fetch('/index.php/admin/categories', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'update_category_order',
            categories: orderData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Show success message
            const alertContainer = document.querySelector('.alert-container');
            if (alertContainer) {
                alertContainer.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Category order updated successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }
        } else {
            console.error('Failed to update category order:', data.message);
        }
    })
    .catch(error => {
        console.error('Error updating category order:', error);
    });
}