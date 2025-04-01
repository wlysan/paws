<?php
// Category view for frontend clients
?>

<div class="section">
    <div class="container">
        <div class="section-title">
            <h2>Product Categories</h2>
        </div>
        
        <div class="row">
            <?php
            // Fetch categories
            $categories = get_all_categories();
            
            // Filter top-level categories only
            $top_categories = array_filter($categories, function($category) {
                return empty($category['parent_id']);
            });
            
            // Display each category
            foreach ($top_categories as $category):
                // Find subcategories
                $subcategories = array_filter($categories, function($sub) use ($category) {
                    return $sub['parent_id'] == $category['id'];
                });
            ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="category-card">
                        <a href="/index.php/category/<?php echo htmlspecialchars($category['slug']); ?>">
                            <div class="category-img">
                                <?php if (!empty($category['image_path']) && file_exists($category['image_path'])): ?>
                                    <img src="/<?php echo htmlspecialchars($category['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($category['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="category-info">
                                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                <?php if (count($subcategories) > 0): ?>
                                    <small><?php echo count($subcategories); ?> subcategories</small>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($top_categories)): ?>
            <div class="alert alert-info text-center">
                <p>No categories available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.category-card {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.category-card a {
    text-decoration: none;
    color: inherit;
}

.category-img {
    height: 180px;
    overflow: hidden;
    position: relative;
}

.category-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.category-card:hover .category-img img {
    transform: scale(1.05);
}

.category-img .no-image {
    width: 100%;
    height: 100%;
    background-color: #f5f5f5;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 3rem;
    color: #ddd;
}

.category-info {
    padding: 15px;
    background: white;
}

.category-info h3 {
    margin: 0 0 5px;
    font-size: 1.1rem;
    font-weight: 600;
}

.category-info small {
    color: #777;
}

@media (max-width: 767px) {
    .category-img {
        height: 140px;
    }
}
</style>