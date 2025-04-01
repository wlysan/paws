<?php
/**
 * Hooks relacionados aos menus do frontend
 */

// Hook para adicionar categorias ao menu principal
add_hook('main_menu', function() {
    $categories = get_all_categories();
    $top_categories = array_filter($categories, function($category) {
        return empty($category['parent_id']) && $category['status'] === 'active';
    });
    
    echo '<li class="nav-item dropdown">';
    echo '<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Categories</a>';
    echo '<ul class="dropdown-menu">';
    
    foreach ($top_categories as $category) {
        echo '<li><a class="dropdown-item" href="/index.php/category/' . 
             htmlspecialchars($category['slug']) . '">' . 
             htmlspecialchars($category['name']) . '</a></li>';
    }
    
    echo '<li><hr class="dropdown-divider"></li>';
    echo '<li><a class="dropdown-item" href="/index.php/categories">All Categories</a></li>';
    echo '</ul>';
    echo '</li>';
});

// Hook para adicionar categorias ao menu mobile
add_hook('mobile_menu', function() {
    $categories = get_all_categories();
    $top_categories = array_filter($categories, function($category) {
        return empty($category['parent_id']) && $category['status'] === 'active';
    });
    
    echo '<li class="accordion-item">';
    echo '<h2 class="accordion-header">';
    echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#categoriesAccordion">';
    echo 'Categories';
    echo '</button>';
    echo '</h2>';
    echo '<div id="categoriesAccordion" class="accordion-collapse collapse">';
    echo '<div class="accordion-body">';
    echo '<ul class="list-group list-group-flush">';
    
    foreach ($top_categories as $category) {
        echo '<li class="list-group-item"><a href="/index.php/category/' . 
             htmlspecialchars($category['slug']) . '">' . 
             htmlspecialchars($category['name']) . '</a></li>';
    }
    
    echo '<li class="list-group-item"><a href="/index.php/categories">All Categories</a></li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';
    echo '</li>';
});