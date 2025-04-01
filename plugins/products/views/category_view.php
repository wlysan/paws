<?php
// Check if we have a valid category
if (empty($active_category)) {
    // Redirect to categories page if no valid category
    header('Location: /index.php/categories');
    exit;
}

// Get subcategories
$subcategories = array_filter($categories, function($sub) use ($active_category) {
    return $sub['parent_id'] == $active_category['id'];
});

// Get parent category if this is a subcategory
$parent_category = null;
if (!empty($active_category['parent_id'])) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $active_category['parent_id']) {
            $parent_category = $cat;
            break;
        }
    }
}
?>

<div class="section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php/home">Home</a></li>
                <li class="breadcrumb-item"><a href="/index.php/categories">Categories</a></li>
                <?php if ($parent_category): ?>
                    <li class="breadcrumb-item">
                        <a href="/index.php/category/<?php echo htmlspecialchars($parent_category['slug']); ?>">
                            <?php echo htmlspecialchars($parent_category['name']); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($active_category['name']); ?>
                </li>
            </ol>
        </nav>
        
        <!-- Category Header -->
        <div class="category-header">
            <?php if (!empty($active_category['image_path']) && file_exists($active_category['image_path'])): ?>
                <div class="category-banner">
                    <img src="/<?php echo htmlspecialchars($active_category['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($active_category['name']); ?>">
                </div>
            <?php endif; ?>
            
            <h1 class="category-title"><?php echo htmlspecialchars($active_category['name']); ?></h1>
            
            <?php if (!empty($active_category['description'])): ?>
                <div class="category-description">
                    <p><?php echo nl2br(htmlspecialchars($active_category['description'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Subcategories Section -->
        <?php if (!empty($subcategories)): ?>
        <div class="subcategories-section">
            <h3>Subcategories</h3>
            <div class="row">
                <?php foreach ($subcategories as $subcategory): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="subcategory-card">
                            <a href="/index.php/category/<?php echo htmlspecialchars($subcategory['slug']); ?>">
                                <div class="subcategory-img">
                                    <?php if (!empty($subcategory['image_path']) && file_exists($subcategory['image_path'])): ?>
                                        <img src="/<?php echo htmlspecialchars($subcategory['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($subcategory['name']); ?>">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="subcategory-info">
                                    <h4><?php echo htmlspecialchars($subcategory['name']); ?></h4>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Products Section -->
        <div class="products-section">
            <h3>Products in <?php echo htmlspecialchars($active_category['name']); ?></h3>
            
            <div class="row" id="product-list">
                <!-- Products will be loaded here -->
                <div class="col-12 text-center p-5">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading products...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript to load products by category
document.addEventListener('DOMContentLoaded', function() {
    loadCategoryProducts(<?php echo $active_category['id']; ?>);
});

function loadCategoryProducts(categoryId) {
    const productList = document.getElementById('product-list');
    
    // Fetch products for this category
    fetch(`/index.php/api/products?category_id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data.products.length > 0) {
                // Render products
                productList.innerHTML = data.data.products.map(product => `
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card">
                            <a href="/index.php/product/${product.slug}">
                                <div class="product-img">
                                    ${product.primary_image ? 
                                        `<img src="/${product.primary_image}" alt="${product.name}">` : 
                                        `<div class="no-image"><i class="fas fa-box"></i></div>`
                                    }
                                </div>
                                <div class="product-info">
                                    <h4>${product.name}</h4>
                                    <div class="product-price">€${parseFloat(product.price).toFixed(2)}</div>
                                </div>
                            </a>
                        </div>
                    </div>
                `).join('');
            } else {
                // No products found
                productList.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <p>No products found in this category.</p>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
            productList.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger text-center">
                        <p>Error loading products. Please try again later.</p>
                    </div>
                </div>
            `;
        });
}
</script>

<style>
.category-header {
    margin-bottom: 30px;
}

.category-banner {
    width: 100%;
    height: 250px;
    overflow: hidden;
    border-radius: 10px;
    margin-bottom: 20px;
}

.category-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-title {
    margin-bottom: 15px;
    font-weight: 600;
}

.category-description {
    margin-bottom: 30px;
    color: #555;
}

.subcategories-section,
.products-section {
    margin-bottom: 40px;
}

.subcategory-card,
.product-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 25px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.subcategory-card:hover,
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
}

.subcategory-card a,
.product-card a {
    text-decoration: none;
    color: inherit;
}

.subcategory-img,
.product-img {
    height: 160px;
    overflow: hidden;
}

.subcategory-img img,
.product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.subcategory-card:hover .subcategory-img img,
.product-card:hover .product-img img {
    transform: scale(1.05);
}

.subcategory-img .no-image,
.product-img .no-image {
    width: 100%;
    height: 100%;
    background-color: #f5f5f5;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 2rem;
    color: #ddd;
}

.subcategory-info,
.product-info {
    padding: 12px;
    background: white;
}

.subcategory-info h4,
.product-info h4 {
    margin: 0 0 5px;
    font-size: 1rem;
    font-weight: 500;
}

.product-price {
    font-weight: 600;
    color: var(--primary-color);
}

@media (max-width: 767px) {
    .category-banner {
        height: 150px;
    }
    
    .subcategory-img,
    .product-img {
        height: 120px;
    }
}
</style>