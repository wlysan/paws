<?php
// View for product listing

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
            <form action="/index.php/admin/products" method="post" class="row g-3">
                <input type="hidden" name="action" value="filter_products">

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
                    <button type="submit" class="btn btn-primary w-100">
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
            <span class="badge bg-primary"><?php echo $total_products; ?> Total Products</span>
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    No products found. Create your first product to get started.
                </div>
                <div class="text-center mt-4">
                    <a href="/index.php/admin/products/add" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i> Create First Product
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Image</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Categories</th>
                                <th>Status</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['primary_image']) && file_exists($product['primary_image'])): ?>
                                            <img src="/<?php echo htmlspecialchars($product['primary_image']); ?>"
                                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                class="product-thumb" width="60" height="60">
                                        <?php else: ?>
                                            <div class="no-image">No image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($product['short_description'] ?? '', 0, 50) . (strlen($product['short_description'] ?? '') > 50 ? '...' : '')); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="fw-semibold">€<?php echo number_format((float)($product['price'] ?? 0), 2); ?></div>
                                        <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                            <small class="text-decoration-line-through text-muted">€<?php echo number_format((float)($product['regular_price'] ?? $product['price']), 2); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (($product['stock_quantity'] ?? 0) <= 0): ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php elseif (($product['stock_quantity'] ?? 0) <= 5): ?>
                                            <span class="badge bg-warning text-dark">Low: <?php echo (int)($product['stock_quantity'] ?? 0); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo (int)($product['stock_quantity'] ?? 0); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($product['category_names']) ? htmlspecialchars($product['category_names']) : '<span class="text-muted fst-italic">No categories</span>'; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status = $product['status'] ?? 'draft';
                                        switch ($status) {
                                            case 'published':
                                                $status_class = 'bg-success';
                                                break;
                                            case 'draft':
                                                $status_class = 'bg-secondary';
                                                break;
                                            case 'out_of_stock':
                                                $status_class = 'bg-danger';
                                                break;
                                            case 'discontinued':
                                                $status_class = 'bg-dark';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Product pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="/index.php/admin/products?page=<?php echo $current_page - 1; ?>">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="/index.php/admin/products?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="/index.php/admin/products?page=<?php echo $current_page + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .product-thumb {
        object-fit: cover;
        border-radius: 4px;
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
</style>