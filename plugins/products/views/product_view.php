<?php
// Product detail view for frontend clients

// Check if we have a valid product
if (empty($active_product)) {
    // Redirect to products page if no valid product
    header('Location: /index.php/products');
    exit;
}

// Get categories for breadcrumbs
$product_categories = [];
if (!empty($active_product['categories'])) {
    $product_categories = $active_product['categories'];
}

// Set primary image and additional images
$primary_image = null;
$additional_images = [];

if (!empty($active_product['images'])) {
    foreach ($active_product['images'] as $image) {
        if ($image['is_primary']) {
            $primary_image = $image;
        } else {
            $additional_images[] = $image;
        }
    }
    
    // If no primary image was set, use the first image
    if (empty($primary_image) && !empty($active_product['images'])) {
        $primary_image = $active_product['images'][0];
    }
}
?>

<div class="section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php/home">Home</a></li>
                <li class="breadcrumb-item"><a href="/index.php/products">Products</a></li>
                <?php if (!empty($product_categories)): ?>
                    <li class="breadcrumb-item">
                        <a href="/index.php/category/<?php echo htmlspecialchars($product_categories[0]['slug']); ?>">
                            <?php echo htmlspecialchars($product_categories[0]['name']); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($active_product['name']); ?>
                </li>
            </ol>
        </nav>
        
        <!-- Product Detail -->
        <div class="product-detail">
            <div class="row">
                <!-- Product Images -->
                <div class="col-md-6 mb-4">
                    <div class="product-images">
                        <!-- Primary Image -->
                        <div class="primary-image mb-3">
                            <?php if ($primary_image && file_exists($primary_image['image_path'])): ?>
                                <img src="/<?php echo htmlspecialchars($primary_image['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($primary_image['alt_text'] ?? $active_product['name']); ?>"
                                     class="img-fluid rounded main-product-image" id="main-product-image">
                            <?php else: ?>
                                <div class="no-image-placeholder rounded">
                                    <i class="fas fa-image"></i>
                                    <p>No image available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Thumbnail Gallery -->
                        <?php if (count($active_product['images']) > 1): ?>
                            <div class="thumbnail-gallery d-flex">
                                <?php foreach ($active_product['images'] as $index => $image): ?>
                                    <?php if (file_exists($image['image_path'])): ?>
                                        <div class="thumbnail-item me-2 mb-2" data-index="<?php echo $index; ?>">
                                            <img src="/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($image['alt_text'] ?? 'Thumbnail ' . ($index + 1)); ?>"
                                                 class="img-thumbnail <?php echo ($image['is_primary']) ? 'active' : ''; ?>"
                                                 onclick="changeMainImage('<?php echo htmlspecialchars($image['image_path']); ?>', <?php echo $index; ?>)">
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Product Information -->
                <div class="col-md-6">
                    <div class="product-info">
                        <!-- Product Title & Badges -->
                        <h1 class="product-title"><?php echo htmlspecialchars($active_product['name']); ?></h1>
                        
                        <div class="badges mb-3">
                            <?php if ($active_product['is_new']): ?>
                                <span class="badge bg-success me-2">New</span>
                            <?php endif; ?>
                            
                            <?php if ($active_product['is_featured']): ?>
                                <span class="badge bg-primary me-2">Featured</span>
                            <?php endif; ?>
                            
                            <?php if ($active_product['stock_quantity'] <= 0): ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php elseif ($active_product['stock_quantity'] <= 5): ?>
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Price -->
                        <div class="product-price mb-3">
                            <?php if (!empty($active_product['sale_price']) && $active_product['sale_price'] < $active_product['price']): ?>
                                <span class="current-price">€<?php echo number_format($active_product['sale_price'], 2); ?></span>
                                <span class="original-price">€<?php echo number_format($active_product['price'], 2); ?></span>
                                <?php 
                                    $discount_percent = round((($active_product['price'] - $active_product['sale_price']) / $active_product['price']) * 100);
                                ?>
                                <span class="discount-badge"><?php echo $discount_percent; ?>% OFF</span>
                            <?php else: ?>
                                <span class="current-price">€<?php echo number_format($active_product['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Short Description -->
                        <?php if (!empty($active_product['short_description'])): ?>
                            <div class="short-description mb-4">
                                <p><?php echo nl2br(htmlspecialchars($active_product['short_description'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- SKU & Stock Status -->
                        <div class="product-meta mb-4">
                            <p class="sku"><strong>SKU:</strong> <?php echo htmlspecialchars($active_product['sku']); ?></p>
                            
                            <p class="stock-status">
                                <strong>Availability:</strong> 
                                <?php if ($active_product['stock_quantity'] > 0): ?>
                                    <span class="text-success">In Stock</span>
                                    <span class="stock-quantity">(<?php echo (int)$active_product['stock_quantity']; ?> available)</span>
                                <?php else: ?>
                                    <span class="text-danger">Out of Stock</span>
                                <?php endif; ?>
                            </p>
                            
                            <?php if (!empty($product_categories)): ?>
                                <p class="categories">
                                    <strong>Categories:</strong> 
                                    <?php 
                                    $category_links = [];
                                    foreach ($product_categories as $category) {
                                        $category_links[] = '<a href="/index.php/category/' . htmlspecialchars($category['slug']) . '">' . 
                                                           htmlspecialchars($category['name']) . '</a>';
                                    }
                                    echo implode(', ', $category_links);
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Add to Cart Form -->
                        <div class="add-to-cart-form mb-4">
                            <form action="/index.php/cart/add" method="post">
                                <input type="hidden" name="product_id" value="<?php echo $active_product['id']; ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="quantity-input">
                                            <label for="quantity" class="form-label">Quantity</label>
                                            <div class="input-group">
                                                <button type="button" class="btn btn-outline-secondary quantity-down">-</button>
                                                <input type="number" class="form-control text-center" id="quantity" name="quantity" 
                                                       value="1" min="1" max="<?php echo (int)$active_product['stock_quantity']; ?>"
                                                       <?php echo ($active_product['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
                                                <button type="button" class="btn btn-outline-secondary quantity-up">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg add-to-cart-btn"
                                            <?php echo ($active_product['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-secondary add-to-wishlist-btn">
                                        <i class="fas fa-heart me-2"></i> Add to Wishlist
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Shipping & Returns -->
                        <div class="shipping-returns mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-truck me-2 text-primary"></i>
                                        <span>Free shipping on orders over €50</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exchange-alt me-2 text-primary"></i>
                                        <span>30-day return policy</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Description & Details -->
            <div class="product-details mt-5">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                                data-bs-target="#description" type="button" role="tab" 
                                aria-controls="description" aria-selected="true">Description</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-tab" data-bs-toggle="tab" 
                                data-bs-target="#details" type="button" role="tab" 
                                aria-controls="details" aria-selected="false">Additional Information</button>
                    </li>
                </ul>
                
                <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabsContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                        <?php if (!empty($active_product['description'])): ?>
                            <div class="product-description">
                                <?php echo nl2br(htmlspecialchars($active_product['description'])); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No detailed description available for this product.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Additional Information Tab -->
                    <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th scope="row">SKU</th>
                                    <td><?php echo htmlspecialchars($active_product['sku']); ?></td>
                                </tr>
                                
                                <?php if (!empty($active_product['weight'])): ?>
                                <tr>
                                    <th scope="row">Weight</th>
                                    <td><?php echo htmlspecialchars($active_product['weight']); ?> kg</td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($active_product['dimensions_length']) || 
                                        !empty($active_product['dimensions_width']) || 
                                        !empty($active_product['dimensions_height'])): ?>
                                <tr>
                                    <th scope="row">Dimensions</th>
                                    <td>
                                        <?php 
                                        $dimensions = [];
                                        if (!empty($active_product['dimensions_length'])) $dimensions[] = 'Length: ' . $active_product['dimensions_length'] . ' cm';
                                        if (!empty($active_product['dimensions_width'])) $dimensions[] = 'Width: ' . $active_product['dimensions_width'] . ' cm';
                                        if (!empty($active_product['dimensions_height'])) $dimensions[] = 'Height: ' . $active_product['dimensions_height'] . ' cm';
                                        echo implode(', ', $dimensions);
                                        ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <tr>
                                    <th scope="row">Categories</th>
                                    <td>
                                        <?php 
                                        if (!empty($product_categories)) {
                                            $category_names = array_column($product_categories, 'name');
                                            echo htmlspecialchars(implode(', ', $category_names));
                                        } else {
                                            echo '<span class="text-muted">Uncategorized</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript for product gallery functionality
    function changeMainImage(imagePath, index) {
        const mainImage = document.getElementById('main-product-image');
        if (mainImage) {
            mainImage.src = '/' + imagePath;
        }
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail-item img').forEach(thumb => {
            thumb.classList.remove('active');
        });
        
        const activeThumb = document.querySelector(`.thumbnail-item[data-index="${index}"] img`);
        if (activeThumb) {
            activeThumb.classList.add('active');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Quantity input functionality
        const quantityInput = document.getElementById('quantity');
        const quantityDown = document.querySelector('.quantity-down');
        const quantityUp = document.querySelector('.quantity-up');
        
        if (quantityInput && quantityDown && quantityUp) {
            const maxQuantity = parseInt(quantityInput.getAttribute('max')) || 100;
            
            quantityDown.addEventListener('click', function() {
                let currentVal = parseInt(quantityInput.value) || 1;
                currentVal = Math.max(1, currentVal - 1);
                quantityInput.value = currentVal;
            });
            
            quantityUp.addEventListener('click', function() {
                let currentVal = parseInt(quantityInput.value) || 1;
                currentVal = Math.min(maxQuantity, currentVal + 1);
                quantityInput.value = currentVal;
            });
            
            quantityInput.addEventListener('change', function() {
                let currentVal = parseInt(this.value) || 1;
                currentVal = Math.max(1, Math.min(maxQuantity, currentVal));
                this.value = currentVal;
            });
        }
        
        // Wishlist button functionality (placeholder)
        const wishlistBtn = document.querySelector('.add-to-wishlist-btn');
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', function() {
                alert('Wishlist feature coming soon!');
            });
        }
    });
</script>

<style>
    /* Product Detail Styles */
    .product-title {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    
    .badges {
        margin-bottom: 1rem;
    }
    
    .product-price {
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .current-price {
        font-weight: bold;
        color: #212529;
    }
    
    .original-price {
        text-decoration: line-through;
        color: #6c757d;
        font-size: 1.1rem;
        margin-left: 0.5rem;
    }
    
    .discount-badge {
        background-color: #dc3545;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.85rem;
        margin-left: 0.5rem;
    }
    
    .stock-quantity {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .main-product-image {
        width: 100%;
        height: auto;
        object-fit: contain;
        max-height: 400px;
    }
    
    .thumbnail-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .thumbnail-item img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.2s;
    }
    
    .thumbnail-item img.active {
        border-color: #0d6efd;
    }
    
    .thumbnail-item img:hover {
        border-color: #0d6efd;
    }
    
    .no-image-placeholder {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        height: 400px;
        color: #6c757d;
    }
    
    .no-image-placeholder i {
        font-size: 3rem;
        margin-bottom: 0.5rem;
    }
    
    .product-meta {
        font-size: 0.95rem;
    }
    
    .add-to-cart-btn {
        font-size: 1.1rem;
    }
    
    .shipping-returns {
        font-size: 0.9rem;
    }
    
    /* Media queries for responsive design */
    @media (max-width: 767px) {
        .main-product-image {
            max-height: 300px;
        }
        
        .product-title {
            font-size: 1.5rem;
        }
        
        .product-price {
            font-size: 1.3rem;
        }
    }
</style>