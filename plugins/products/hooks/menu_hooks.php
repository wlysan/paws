<?php
/**
 * Hooks relacionados aos menus do frontend
 */

// Hook para adicionar categorias ao menu principal
add_hook('main_menu', function() {
    // Obter categorias ativas
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT id, name, slug 
            FROM product_categories 
            WHERE parent_id IS NULL AND status = 'active' AND is_deleted = 0
            ORDER BY display_order ASC, name ASC
            LIMIT 10
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($categories)) {
            echo '<li class="nav-item dropdown">';
            echo '<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Categories</a>';
            echo '<ul class="dropdown-menu">';
            
            foreach ($categories as $category) {
                echo '<li><a class="dropdown-item" href="/index.php/category/' . 
                     htmlspecialchars($category['slug']) . '">' . 
                     htmlspecialchars($category['name']) . '</a></li>';
            }
            
            echo '<li><hr class="dropdown-divider"></li>';
            echo '<li><a class="dropdown-item" href="/index.php/categories">All Categories</a></li>';
            echo '</ul>';
            echo '</li>';
        }
    } catch (PDOException $e) {
        error_log('Error loading categories for menu: ' . $e->getMessage());
    }
    
    // Add Products menu item
    echo '<li class="nav-item">';
    echo '<a class="nav-link" href="/index.php/products">Shop</a>';
    echo '</li>';
});

// Hook para adicionar categorias ao menu mobile
add_hook('mobile_menu', function() {
    // Obter categorias ativas
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT id, name, slug 
            FROM product_categories 
            WHERE parent_id IS NULL AND status = 'active' AND is_deleted = 0
            ORDER BY display_order ASC, name ASC
            LIMIT 10
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($categories)) {
            echo '<li class="accordion-item">';
            echo '<h2 class="accordion-header">';
            echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#categoriesAccordion">';
            echo 'Categories';
            echo '</button>';
            echo '</h2>';
            echo '<div id="categoriesAccordion" class="accordion-collapse collapse">';
            echo '<div class="accordion-body">';
            echo '<ul class="list-group list-group-flush">';
            
            foreach ($categories as $category) {
                echo '<li class="list-group-item"><a href="/index.php/category/' . 
                     htmlspecialchars($category['slug']) . '">' . 
                     htmlspecialchars($category['name']) . '</a></li>';
            }
            
            echo '<li class="list-group-item"><a href="/index.php/categories">All Categories</a></li>';
            echo '</ul>';
            echo '</div>';
            echo '</div>';
            echo '</li>';
        }
    } catch (PDOException $e) {
        error_log('Error loading categories for mobile menu: ' . $e->getMessage());
    }
    
    // Add Products menu item
    echo '<li class="list-group-item">';
    echo '<a href="/index.php/products">Shop All Products</a>';
    echo '</li>';
});

// Hook para adicionar links rápidos de produtos no rodapé
add_hook('footer_links', function() {
    echo '<div class="col-12 col-md-3">';
    echo '<h5 class="footer-title">Shop</h5>';
    echo '<ul class="footer-links">';
    echo '<li><a href="/index.php/products">All Products</a></li>';
    echo '<li><a href="/index.php/products?sort=newest">New Arrivals</a></li>';
    echo '<li><a href="/index.php/categories">Browse Categories</a></li>';
    
    // Mostrar algumas categorias em destaque
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT name, slug 
            FROM product_categories 
            WHERE status = 'featured' AND is_deleted = 0
            ORDER BY display_order ASC, name ASC
            LIMIT 3
        ");
        $stmt->execute();
        $featured_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($featured_categories as $category) {
            echo '<li><a href="/index.php/category/' . 
                 htmlspecialchars($category['slug']) . '">' . 
                 htmlspecialchars($category['name']) . '</a></li>';
        }
    } catch (PDOException $e) {
        error_log('Error loading featured categories for footer: ' . $e->getMessage());
    }
    
    echo '</ul>';
    echo '</div>';
});