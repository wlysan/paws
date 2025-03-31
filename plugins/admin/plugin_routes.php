<?php
/**
 * Plugin Routes for Admin
 * 
 * Defines routes specific to the admin plugin
 */

$plugin_route['admin'] = [
    '/admin/login' => [
        'view' => 'plugins/admin/views/sys_admin_login_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_login_controller.php',
        'structure' => 'app/struct/blank.php'  // Using blank structure for login page
    ],
    '/admin/register' => [
        'view' => 'plugins/admin/views/sys_admin_register_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_register_controller.php',
        'structure' => 'app/struct/blank.php'  // Using blank structure for register page
    ],
    '/admin/dashboard' => [
        'view' => 'plugins/admin/views/sys_admin_dashboard_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_dashboard_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/products' => [
        'view' => 'plugins/admin/views/sys_admin_products_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_products_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/categories' => [
        'view' => 'plugins/admin/views/sys_admin_categories_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_categories_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/orders' => [
        'view' => 'plugins/admin/views/sys_admin_orders_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_orders_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/customers' => [
        'view' => 'plugins/admin/views/sys_admin_customers_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_customers_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/pets' => [
        'view' => 'plugins/admin/views/sys_admin_pets_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_pets_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/reports' => [
        'view' => 'plugins/admin/views/sys_admin_reports_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_reports_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/settings' => [
        'view' => 'plugins/admin/views/sys_admin_settings_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_settings_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/profile' => [
        'view' => 'plugins/admin/views/sys_admin_profile_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_profile_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ],
    '/admin/logout' => [
        'view' => 'plugins/admin/views/sys_admin_logout_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_logout_controller.php',
        'structure' => 'app/struct/blank.php'
    ],
    '/admin/forgot-password' => [
        'view' => 'plugins/admin/views/sys_admin_forgot_password_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_forgot_password_controller.php',
        'structure' => 'app/struct/blank.php'
    ],
    '/admin/reset-password' => [
        'view' => 'plugins/admin/views/sys_admin_reset_password_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_reset_password_controller.php',
        'structure' => 'app/struct/blank.php'
    ],
    '/admin/verify' => [
        'view' => 'plugins/admin/views/sys_admin_verify_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_verify_controller.php',
        'structure' => 'app/struct/blank.php'
    ]
];