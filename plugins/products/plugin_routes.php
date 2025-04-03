<?php
/**
 * Plugin Routes for Products
 * 
 * Defines routes specific to the products plugin
 */

 $plugin_route['products'] = [
    '/admin/categories' => [
        'view' => 'plugins/products/views/sys_category_view.php',
        'controller' => 'plugins/products/controllers/sys_category_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/categories/add' => [
        'view' => 'plugins/products/views/sys_category_add_view.php',
        'controller' => 'plugins/products/controllers/sys_category_add_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/categories/edit' => [  // Esta rota deve estar registrada exatamente assim
        'view' => 'plugins/products/views/sys_category_edit_view.php',
        'controller' => 'plugins/products/controllers/sys_category_edit_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/categories/delete' => [
        'view' => null, // Não precisa de view
        'controller' => 'plugins/products/controllers/sys_category_delete_controller.php',
        'structure' => null // Não precisa de structure, pois apenas processa e redireciona
    ],
    '/categories' => [
        'view' => 'plugins/products/views/categories_view.php',
        'controller' => 'plugins/products/controllers/categories_controller.php',
        'structure' => 'app/struct/mobile.php'
    ],
    '/category' => [
        'view' => 'plugins/products/views/category_view.php',
        'controller' => 'plugins/products/controllers/category_controller.php',
        'structure' => 'app/struct/relogio.php'
    ],

    '/admin/products' => [
        'view' => 'plugins/products/views/sys_product_view.php',
        'controller' => 'plugins/products/controllers/sys_product_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/products/add' => [
        'view' => 'plugins/products/views/sys_product_add_view.php',
        'controller' => 'plugins/products/controllers/sys_product_add_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/products/edit' => [
        'view' => 'plugins/products/views/sys_product_edit_view.php',
        'controller' => 'plugins/products/controllers/sys_product_edit_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/products/delete' => [
        'view' => null,
        'controller' => 'plugins/products/controllers/sys_product_delete_controller.php',
        'structure' => null
    ],
    '/products' => [
        'view' => 'plugins/products/views/products_view.php',
        'controller' => 'plugins/products/controllers/products_controller.php',
        'structure' => 'app/struct/relogio.php'
    ],
    '/product' => [
        'view' => 'plugins/products/views/product_view.php',
        'controller' => 'plugins/products/controllers/product_controller.php',
        'structure' => 'app/struct/relogio.php'
    ]
];