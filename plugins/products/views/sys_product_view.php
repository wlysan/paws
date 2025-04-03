<?php
// View for product listing

// Ensure variables are available
if (!isset($categories)) {
    $categories = $GLOBALS['categories'] ?? [];
}

if (!isset($error_message)) {
    $error_message = $GLOBALS['error_message'] ?? '';
}

if (!isset($success_message)) {
    $success_message = $GLOBALS['success_message'] ?? '';
}

// Check for messages
if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Products</h1>
            <p class="text-muted">Manage products for your pet boutique</p>
        </div>
        <div>
            <a href="/index.php/admin/products/add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Add New Product
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Filter Products</h5>
        </div>
        <div class="card-body">
            <form id="product-filter-form" class="row g-3">
                <div class="col-md-3">
                    <label for="filter_category" class="form-label">Category</label>
                    <select class="form-select" id="filter_category" name="filter_category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filter_status" class="form-label">Status</label>
                    <select class="form-select" id="filter_status" name="filter_status">
                        <option value="">All Statuses</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="out_of_stock">Out of Stock</option>
                        <option value="discontinued">Discontinued</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filter_search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="filter_search" name="filter_search" placeholder="Name, SKU, etc.">
                </div>

                <div class="col-md-2">
                    <label for="filter_price" class="form-label">Price Range</label>
                    <select class="form-select" id="filter_price" name="filter_price">
                        <option value="">All Prices</option>
                        <option value="0-10">€0 - €10</option>
                        <option value="10-25">€10 - €25</option>
                        <option value="25-50">€25 - €50</option>
                        <option value="50-100">€50 - €100</option>
                        <option value="100-">€100+</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="apply-filters" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Products</h5>
            <span class="badge bg-primary" id="total-products-count">0 Total Products</span>
        </div>
        <div class="card-body">
            <div id="products-container">
                <!-- Products will be loaded here via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading products...</p>
                </div>
            </div>
            
            <!-- Pagination Container -->
            <div id="pagination-container" class="mt-4">
                <!-- Pagination will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

<style>
    .product-thumb {
        object-fit: cover;
        border-radius: 4px;
        width: 60px;
        height: 60px;
    }

    .no-image {
        width: 60px;
        height: 60px;
        background-color: #f5f5f5;
        border: 1px dashed #ccc;
        border-radius: 4px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 10px;
        color: #999;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 0;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #adb5bd;
        margin-bottom: 1rem;
    }
    
    .empty-state h4 {
        margin-bottom: 1rem;
    }
</style>

<!-- Modal para confirmar exclusão - será preenchido via JS -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the product <strong id="delete-product-name"></strong>?</p>
                <p>This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="delete-product-form" action="/index.php/admin/products/delete" method="post">
                    <input type="hidden" id="delete-product-id" name="product_id" value="">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Carrega o JavaScript específico para produtos -->
<script src="/plugins/products/js/sys_product.js"></script>