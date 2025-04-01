<?php
/**
 * Hooks relacionados à área administrativa
 */

// Hook para adicionar item no menu da área administrativa
add_hook('menu_lateral_items', function() {
    if (function_exists('is_admin_logged_in') && is_admin_logged_in()) {
        echo '<li>
                <a href="/index.php/admin/categories" class="item">
                    <div class="icon-box bg-primary">
                        <ion-icon name="pricetags-outline"></ion-icon>
                    </div>
                    <div class="in">
                        Product Categories
                    </div>
                </a>
            </li>';
    }
});

// Hook para adicionar assets na página administrativa
add_hook('page_assets', function() {
    // Verificar se estamos em uma página relacionada a produtos
    $rota = get_route();
    if (strpos($rota['route'], '/admin/categories') === 0 || 
        strpos($rota['route'], '/admin/products') === 0) {
        echo '<link rel="stylesheet" href="/plugins/products/css/sys_products.css">';
        echo '<script src="/plugins/products/js/sys_category.js"></script>';
    }
});

// Hook para adicionar breadcrumbs específicos
add_hook('breadcrumbs', function() {
    $rota = get_route();
    
    if ($rota['route'] === '/admin/categories') {
        echo '<li class="breadcrumb-item active" aria-current="page">Categories</li>';
    } 
    else if ($rota['route'] === '/admin/categories/add') {
        echo '<li class="breadcrumb-item"><a href="/index.php/admin/categories">Categories</a></li>';
        echo '<li class="breadcrumb-item active" aria-current="page">Add New</li>';
    }
    else if ($rota['route'] === '/admin/categories/edit') {
        echo '<li class="breadcrumb-item"><a href="/index.php/admin/categories">Categories</a></li>';
        echo '<li class="breadcrumb-item active" aria-current="page">Edit</li>';
    }
});