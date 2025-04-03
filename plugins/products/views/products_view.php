<?php
// Products listing view for frontend clients
?>

<div class="section">
    <div class="container">
        <div class="section-title">
            <h1>Pet Products</h1>
            <p class="text-muted">Discover our exclusive collection of pet clothing and accessories</p>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-bar mb-4">
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex flex-wrap">
                        <div class="filter-item me-2 mb-2">
                            <select class="form-select form-select-sm" id="category-filter">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                        <?php echo (!empty($selected_category) && $selected_category == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-item me-2 mb-2">
                            <select class="form-select form-select-sm" id="price-filter">
                                <option value="">Price Range</option>
                                <option value="0-10">€0 - €10</option>
                                <option value="10-25">€10 - €25</option>
                                <option value="25-50">€25 - €50</option>
                                <option value="50-100">€50 - €100</option>
                                <option value="100-">€100+</option>
                            </select>
                        </div>
                        
                        <div class="filter-item me-2 mb-2">
                            <select class="form-select form-select-sm" id="sort-filter">
                                <option value="newest">Newest First</option>
                                <option value="price-low">Price: Low to High</option>
                                <option value="price-high">Price: High to Low</option>
                                <option value="name-asc">Name: A to Z</option>
                                <option value="name-desc">Name: Z to A</option>
                            </select>
                        </div>
                        
                        <div class="filter-item mb-2">
                            <button type="button" class="btn btn-primary btn-sm" id="apply-filters">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 text-md-end">
                    <div class="view-options">
                        <button type="button" class="btn btn-outline-secondary btn-sm active" id="grid-view" title="Grid View">
                            <i class="fas fa-th"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="list-view" title="List View">
                            <i class="fas fa-list"></i>
                        </button>
                        
                        <span class="text-muted small ms-2">
                            <?php echo $total_products; ?> product<?php echo ($total_products !== 1) ? 's' : ''; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="products-container" id="products-container">
            <?php if (!empty($products)): ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="col">
                            <div class="card product-card h-100">
                                <!-- Product badges -->
                                <div class="product-badges">
                                    <?php if ($product['is_new']): ?>
                                        <span class="badge bg-success">New</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                        <?php 
                                            $discount_percent = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                                        ?>
                                        <span class="badge bg-danger">-<?php echo $discount_percent; ?>%</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['stock_quantity'] <= 0): ?>
                                        <span class="badge bg-secondary">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Product image -->
                                <a href="/index.php/product/<?php echo htmlspecialchars($product['slug']); ?>" class="product-image-link">
                                    <?php if (!empty($product['primary_image']) && file_exists($product['primary_image'])): ?>
                                        <img src="/<?php echo htmlspecialchars($product['primary_image']); ?>" 
                                            class="card-img-top product-image" 
                                            alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                
                                <div class="card-body product-info">
                                    <!-- Product category -->
                                    <?php if (!empty($product['category_name'])): ?>
                                        <div class="product-category small text-muted mb-1">
                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Product name -->
                                    <h3 class="card-title product-name">
                                        <a href="/index.php/product/<?php echo htmlspecialchars($product['slug']); ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h3>
                                    
                                    <!-- Product price -->
                                    <div class="product-price mb-2">
                                        <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                            <span class="current-price">€<?php echo number_format($product['sale_price'], 2); ?></span>
                                            <span class="original-price">€<?php echo number_format($product['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="current-price">€<?php echo number_format($product['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Product short description (for list view) -->
                                    <div class="product-description d-none">
                                        <p><?php echo htmlspecialchars(substr($product['short_description'] ?? '', 0, 100) . (strlen($product['short_description'] ?? '') > 100 ? '...' : '')); ?></p>
                                    </div>
                                </div>
                                
                                <div class="card-footer product-actions">
                                    <div class="d-flex justify-content-between">
                                        <a href="/index.php/product/<?php echo htmlspecialchars($product['slug']); ?>" class="btn btn-sm btn-outline-primary">
                                            View Details
                                        </a>
                                        
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <button type="button" class="btn btn-sm btn-primary quick-add-to-cart"
                                                    data-product-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-cart-plus"></i> Add
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                Out of Stock
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Products pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="/index.php/products?page=<?php echo $current_page - 1; ?><?php echo $query_params; ?>">Previous</a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="/index.php/products?page=<?php echo $i; ?><?php echo $query_params; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="/index.php/products?page=<?php echo $current_page + 1; ?><?php echo $query_params; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-products-found">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-search mb-3" style="font-size: 3rem;"></i>
                        <h4>No products found</h4>
                        <p>We couldn't find any products matching your criteria. Try adjusting your filters or browse our categories.</p>
                        
                        <div class="mt-3">
                            <a href="/index.php/products" class="btn btn-primary">View All Products</a>
                            <a href="/index.php/categories" class="btn btn-outline-primary ms-2">Browse Categories</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // View toggle (Grid/List)
        const gridViewBtn = document.getElementById('grid-view');
        const listViewBtn = document.getElementById('list-view');
        const productsContainer = document.getElementById('products-container');
        
        if (gridViewBtn && listViewBtn && productsContainer) {
            gridViewBtn.addEventListener('click', function() {
                productsContainer.classList.remove('list-view');
                
                // Update active button state
                gridViewBtn.classList.add('active');
                listViewBtn.classList.remove('active');
                
                // Save preference
                localStorage.setItem('productViewMode', 'grid');
            });
            
            listViewBtn.addEventListener('click', function() {
                productsContainer.classList.add('list-view');
                
                // Update active button state
                listViewBtn.classList.add('active');
                gridViewBtn.classList.remove('active');
                
                // Save preference
                localStorage.setItem('productViewMode', 'list');
            });
            
            // Load saved preference
            const savedViewMode = localStorage.getItem('productViewMode');
            if (savedViewMode === 'list') {
                listViewBtn.click();
            }
        }
        
        // Filter functionality
        const applyFiltersBtn = document.getElementById('apply-filters');
        
        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', function() {
                const categoryFilter = document.getElementById('category-filter').value;
                const priceFilter = document.getElementById('price-filter').value;
                const sortFilter = document.getElementById('sort-filter').value;
                
                let queryParams = [];
                
                if (categoryFilter) {
                    queryParams.push('category=' + categoryFilter);
                }
                
                if (priceFilter) {
                    queryParams.push('price=' + priceFilter);
                }
                
                if (sortFilter) {
                    queryParams.push('sort=' + sortFilter);
                }
                
                // Redirect with filters
                window.location.href = '/index.php/products?' + queryParams.join('&');
            });
        }
        
        // Quick add to cart functionality
        const quickAddButtons = document.querySelectorAll('.quick-add-to-cart');
        
        quickAddButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                
                // Show loading state
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                this.disabled = true;
                
                // Simulate adding to cart (replace with actual AJAX call)
                setTimeout(() => {
                    // Reset button and show success
                    this.innerHTML = '<i class="fas fa-check"></i> Added';
                    
                    // Revert after 2 seconds
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-cart-plus"></i> Add';
                        this.disabled = false;
                    }, 2000);
                    
                    // Show cart notification (replace with your cart system)
                    alert('Product added to cart!');
                }, 800);
            });
        });
    });
</script>

<style>
    /* Products Grid & List View Styles */
    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .product-image-link {
        display: block;
        position: relative;
        padding-top: 100%; /* 1:1 Aspect Ratio */
        overflow: hidden;
    }
    
    .product-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .product-card:hover .product-image {
        transform: scale(1.05);
    }
    
    .product-badges {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 10;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .product-name {
        font-size: 1rem;
        margin-bottom: 0.5rem;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .product-name a {
        color: #212529;
        text-decoration: none;
    }
    
    .product-name a:hover {
        color: #0d6efd;
    }
    
    .product-price {
        font-weight: bold;
    }
    
    .current-price {
        color: #212529;
    }
    
    .original-price {
        text-decoration: line-through;
        color: #6c757d;
        font-size: 0.9rem;
        margin-left: 0.5rem;
    }
    
    .no-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f8f9fa;
        color: #adb5bd;
        font-size: 2rem;
    }
    
    /* List View Styles */
    .list-view .row {
        flex-direction: column;
    }
    
    .list-view .col {
        width: 100%;
        max-width: 100%;
    }
    
    .list-view .product-card {
        flex-direction: row;
        height: auto;
    }
    
    .list-view .product-image-link {
        width: 200px;
        padding-top: 200px; /* Keep 1:1 aspect ratio */
        flex-shrink: 0;
    }
    
    .list-view .product-info {
        display: flex;
        flex-direction: column;
    }
    
    .list-view .product-description {
        display: block !important;
        margin-top: auto;
    }
    
    .list-view .product-actions {
        width: auto;
        margin-top: auto;
    }
    
    /* Filter Bar Styles */
    .filter-bar {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .section-title {
        margin-bottom: 30px;
        text-align: center;
    }
    
    /* Responsive adjustments */
    @media (max-width: 991px) {
        .list-view .product-card {
            flex-direction: column;
        }
        
        .list-view .product-image-link {
            width: 100%;
        }
    }
    
    @media (max-width: 767px) {
        .view-options {
            margin-top: 15px;
            text-align: left !important;
        }
    }
</style>